<button
    onclick="getSubmissions(this)"
    data-route="{{ url($crud->route.'/'.$entry->getKey().'/get-submissions') }}"
    class="btn btn-sm btn-success"
    data-button-type="action"
    data-is-active="{{ $entry->is_active }}"
    {{ $entry->is_active ? '' : 'disabled="disabled"' }}
>
    <i class="la la-"></i>Pull<br>Submissions
</button>

{{-- Button Javascript --}}
{{-- - used right away in AJAX operations (ex: List) --}}
{{-- - pushed to the end of the page, after jQuery is loaded, for non-AJAX operations (ex: Show) --}}
@push('after_scripts')
    @if (request()->ajax())
        @endpush
    @endif
    <script>

        if (typeof getSubmissions != 'function') {

            function getSubmissions(button) {
                var button = $(button);
                var route = button.attr('data-route');
                var row = $("#crudTable a[data-route='" + route + "']").closest('tr');

                $.ajax({
                url: route,
                type: 'POST',
                success: function(result) {
                    console.log(result);
                    new Noty({
                        type: "info",
                        text: "Pull Submissions Successful"
                    }).show();
                },
                error: function(result) {
                    // Show an alert with the result
                    swal({
                        title: "Error",
                        text: "Something went wrong while communicating with ODK Central - please try again or contact the site admin",
                        icon: "error",
                        timer: 4000,
                        buttons: false,
                    });
                }
            });

            }
        }
    </script>
    @if (!request()->ajax())
        @endpush
    @endif
