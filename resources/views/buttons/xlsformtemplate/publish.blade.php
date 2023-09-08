@if ($crud->hasAccess('update'))
<a href="{{ url($crud->route.'/'.$entry->getKey().'/publish') }} " class="btn btn-sm btn-link"><i class="la la-tasks"></i> Publish</a>
@endif
