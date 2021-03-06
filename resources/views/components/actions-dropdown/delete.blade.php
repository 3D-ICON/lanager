@can('delete', $item)
    @php
        $route = kebab_case(str_plural(class_basename($item)));
    @endphp
    <form action="{{ route( $route . '.destroy', $item->id) }}" method="POST" class="confirm-deletion">
        {{ method_field('DELETE') }}
        {{ csrf_field() }}
        <a class="dropdown-item" href="#" onclick="$(this).closest('form').submit();">@lang('title.delete')</a>
    </form>
@endcan