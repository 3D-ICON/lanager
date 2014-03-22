<?php
/*
|--------------------------------------------------------------------------
| View Composers
|--------------------------------------------------------------------------
*/

View::composer('layouts.default.infopages', function($view)
{
	$infoPagesMenuItems = Zeropingheroes\Lanager\Models\InfoPage::whereNull('parent_id')->get();

	$view->with('infoPages', $infoPagesMenuItems);
});

View::composer('layouts.default.nav', function($view)
{
	// Steam OpenID Login URL - cached for 1 day due to request time
	$authUrl = Cache::remember('authUrl', 60*24, function()
	{
		$openId = new LightOpenID(Request::server('HTTP_HOST'));
		
		$openId->identity = 'http://steamcommunity.com/openid';
		$openId->returnUrl = URL::route('users.openIdLogin');
		return $openId->authUrl();
	});

	$view->with('authUrl', $authUrl);
});