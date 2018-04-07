<?php

namespace Zeropingheroes\Lanager\Http\Controllers;

use \InvalidArgumentException;
use Socialite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Zeropingheroes\Lanager\UserOAuthAccount;
use Zeropingheroes\Lanager\User;

/**
 * Class AuthController
 * @package Zeropingheroes\Lanager\Http\Controllers\Auth
 */
class AuthController extends Controller
{

    /**
     * Show the login page.
     *
     * @return \Illuminate\Http\Response
     */
    public function showLoginForm()
    {
        return view('pages.auth.login');
    }

    /**
     * Redirect the user to the external authentication provider.
     *
     * @param $OAuthProvider string
     * @return Response
     * @throws InvalidArgumentException
     */
    public function redirectToProvider($OAuthProvider)
    {
        if ($OAuthProvider == 'steam') {
            return Socialite::with('steam')->redirect();
        }
        throw new InvalidArgumentException(__('phrase.provider-not-supported', ['provider' => $OAuthProvider]));

    }

    /**
     * Obtain the user information from the external authentication provider.
     *
     * @param $OAuthProvider
     * @return Response
     * @throws InvalidArgumentException
     */
    public function handleProviderCallback($OAuthProvider)
    {
        if ($OAuthProvider == 'steam') {
            $OAuthUser = Socialite::with('steam')->user();

            $user = $this->findOrCreateUser($OAuthUser, 'steam');
            Auth::login($user, true);

            return redirect()->intended('/');
        }

        throw new InvalidArgumentException(__('phrase.provider-not-supported', ['provider' => $OAuthProvider]));
    }

    /**
     * Log the user out of the application.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function logout(Request $request)
    {
        $this->guard()->logout();

        $request->session()->invalidate();

        return redirect('/');
    }

    /**
     * Get the guard to be used during authentication.
     *
     * @return \Illuminate\Contracts\Auth\StatefulGuard
     */
    protected function guard()
    {
        return Auth::guard();
    }

    /**
     * If a user has registered before using OAuth, return the user
     * else, create a new user object.
     * @param  $OAuthUser Socialite user object
     * @param $OAuthProvider OAuth provider
     * @return  User
     */
    public function findOrCreateUser($OAuthUser, $OAuthProvider)
    {
        // Check if the given OAuth account exists
        $existingOAuthAccount = UserOAuthAccount::where(
            [
                'provider' => $OAuthProvider,
                'provider_id' => $OAuthUser->id
            ]
        )->first();

        // If the OAuth account exists
        // return the user who owns the account
        if ($existingOAuthAccount) {
            return $existingOAuthAccount->user()->first();
        }

        // Otherwise create a new user ...
        $user = User::create([
            'username' => $OAuthUser->nickname
        ]);

        // ... link the OAuth account ...
        $user->OAuthAccounts()
            ->create([
                'username' => $OAuthUser->nickname,
                'avatar' => $OAuthUser->avatar,
                'provider' => $OAuthProvider,
                'provider_id' => $OAuthUser->id
            ]);

        // ... and return the user
        return $user;
    }
}