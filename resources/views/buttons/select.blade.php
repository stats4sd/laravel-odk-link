@if ($crud->hasAccess('update'))
<a href="{{ url($crud->route.'/'.$entry->getKey().'/select') }} " class="btn btn-sm btn-link"><i class="la la-tasks"></i> Select</a>
@endif