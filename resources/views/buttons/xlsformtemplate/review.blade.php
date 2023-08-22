@if ($crud->hasAccess('update'))
<a href="{{ url($crud->route.'/'.$entry->getKey().'/review') }} " class="btn btn-sm btn-link"><i class="la la-tasks"></i> Review</a>
@endif
