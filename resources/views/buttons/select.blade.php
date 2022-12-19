@if ($crud->hasAccess('update'))
<a href="{{ url($crud->route.'/'.$entry->getKey().'/select') }} " class="btn btn-xs btn-default"><i class="la la-ban"></i> Select</a>
@endif