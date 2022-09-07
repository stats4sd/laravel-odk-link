<h2 class="card-title">{{ $entry->title }}</h2>

<div class="row">
    <div class="col-md-12 col-xl-4">
        <div class="card">
            <div class="card-header">
                <h3>Admin Actions</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 col-lg-4">
                        @include('odk-link::buttons.xlsform-update-xlsfile')
                    </div>
                    <div class="col-md-6 col-lg-8 text-wrap">
                        Replace the XLSFile with the latest template file.
                    </div>
                </div>
                <hr/>
                <div class="row">
                    <div class="col-md-6 col-lg-4">
                        @include('odk-link::buttons.xlsform-deploy-draft')
                    </div>
                    <div class="col-md-6 col-lg-8 text-wrap">
                        Push the latest file and data to the 'draft' deployment.
                    </div>
                </div>
                <hr/>
                <div class="row">
                    <div class="col-md-6 col-lg-4">
                        @include('odk-link::buttons.xlsform-deploy-live')
                    </div>
                    <div class="col-md-6 col-lg-8 text-wrap">
                        Publish the current draft version to 'live'.
                    </div>
                </div>
                <hr/>
                <div class="row">
                    <div class="col-md-6 col-lg-4">
                        @include('odk-link::buttons.xlsform-archive')
                    </div>
                    <div class="col-md-6 col-lg-8 text-wrap">
                        Archive the form to prevent further data collection.
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-4">
        <div class="card">
            <div class="card-header">
                <h3>Draft Deployment</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    @if($entry->has_draft)
                        <div class="col-md-4">
                            {!! QrCode::size(100)->generate($entry->draft_qr_code_string) !!}
                        </div>
                        <div class="col-md-8 text-wrap my-auto">
                            To test the draft form, open ODK Collect and tap the icon in the top-right. Tap "Add
                            project" and scan the QR code. Your device will be linked to this draft.
                        </div>
                    @else
                        <div class="mx-auto align-self-center">~ No draft version is currently active ~</div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-xl-4">
        <div class="card">
            <div class="card-header">
                <h3>Live Deployment</h3>
            </div>
            <div class="card-body">
                @if($entry->is_active)
                    <div class="text-wrap">
                        Form is live!! Use either your main data collector QR code, or create unique enumerator accounts
                        (who get their own QR codes...) to collect data using all your live forms.
                    </div>
                @else
                    <div class="mx-auto align-self-center">
                        ~Form is not yet live~
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
