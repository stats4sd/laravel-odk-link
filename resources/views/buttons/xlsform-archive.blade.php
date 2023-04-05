<button
    onclick="archiveForm(this)"
    data-is-active="{{ $entry->is_active }}"
    {{ $entry->is_active ? '' : 'disabled="disabled"' }}
    data-route="{{ url($crud->route.'/'.$entry->getKey().'/archive') }}"
    class="btn btn-sm btn-warning"
>
    <i class="la la-"></i>Archive Form
</button>

{{-- Button Javascript --}}
{{-- - used right away in AJAX operations (ex: List) --}}
{{-- - pushed to the end of the page, after jQuery is loaded, for non-AJAX operations (ex: Show) --}}
@push('after_scripts')
    @if (request()->ajax())
        @endpush
    @endif
    <script>

        if (typeof archiveForm != 'function') {

            function archiveForm(button) {
                // ask for confirmation before deleting an item
                // e.preventDefault();
                var button = $(button);
                var route = button.attr('data-route');
                var row = $("#crudTable a[data-route='" + route + "']").closest('tr');


                button.attr('disabled', 'disabled');
                button.html("<i class='la la-spinner'></i> Archive Form")

                swal({
                    title: "Are you sure?",
                    text: "This will disable data collection for the form and prevent anyone from submitting records.",
                    icon: "info",
                    buttons: {
                        cancel: {
                            text: "{!! trans('backpack::crud.cancel') !!}",
                            value: null,
                            visible: true,
                            className: "bg-secondary",
                            closeModal: true,
                        },
                        update: {
                            text: "Yes - Archive the form",
                            value: true,
                            visible: true,
                            className: "bg-success",
                        }
                    },
                }).then((value) => {
                    if (value) {
                        $.ajax({
                            url: route,
                            type: 'POST',
                            success: function (result) {
                                console.log(result);
                                new Noty({
                                    type: "info",
                                    text: "Form successfully archived"
                                }).show();
                            },
                            error: function (result) {
                                // Show an alert with the result
                                swal({
                                    title: "Error",
                                    text: "Something went wrong with the update - please try again or contact the site admin",
                                    icon: "error",
                                    timer: 4000,
                                    buttons: false,
                                });
                            },
                            complete: () => {
                                button.removeAttr('disabled');
                                button.html("<i class='la la-wp-form'></i> Archive Form")

                                // get list of currently open details rows
                                var detailShown = crud.table.rows('.dt-hasChild.shown')

                                // reload table data and re-open current details rows
                                crud.table.ajax.reload(() => {
                                    detailShown.every(function (rowIdx, tableLoop, rowLoop) {
                                        var tr = crud.table.row(rowIdx).node()
                                        tr.getElementsByClassName("details-control")[0].click()

                                    })
                                }, true)
                            }
                        });
                    }
                });

            }
        }

        // make it so that the function above is run after each DataTable draw event
        // crud.addFunctionToDataTablesDrawEventQueue('deleteEntry');
    </script>
    @if (!request()->ajax())
        @endpush
    @endif
