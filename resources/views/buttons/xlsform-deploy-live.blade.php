<button
    onclick="deployLiveEntry(this)"
    data-route="{{ url($crud->route.'/'.$entry->getKey().'/deploy-live') }}"
    class="btn btn-sm btn-success"
    data-button-type="action"
    data-is-active="{{ $entry->is_active }}"
    {{ $entry->has_draft ? '' : 'disabled="disabled"' }}
>
    <i class="la la-"></i>Publish to Live
</button>

{{-- Button Javascript --}}
{{-- - used right away in AJAX operations (ex: List) --}}
{{-- - pushed to the end of the page, after jQuery is loaded, for non-AJAX operations (ex: Show) --}}
@push('after_scripts')
    @if (request()->ajax())
        @endpush
    @endif
    <script>

        if (typeof deployLiveEntry != 'function') {

            function deployLiveEntry(button) {
                // ask for confirmation before deleting an item
                // e.preventDefault();
                var button = $(button);
                var route = button.attr('data-route');
                var row = $("#crudTable a[data-route='" + route + "']").closest('tr');


                button.attr('disabled', 'disabled');
                button.html("<i class='la la-spinner'></i> Publish Live Form")

                var text = button.attr('data-is-active') == 1 ? 'This will overwrite your existing live form with the current draft version. Are you sure you wish to do this?'
                    : 'This will enable this form for live data collection. Your account(s) will gain access to this form based on how you have setup your enumerator accounts.'

                swal({
                    title: "Are you sure?",
                    text: text,
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
                            text: "Yes - Publish this form",
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
                                    text: "Form successfully published"
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
                                button.html("<i class='la la-wp-form'></i> Publish Live Form")


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
