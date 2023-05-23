<div class="card">
    <div class="card-body">
        <h3>Setup Device for Live Data Collection</h3>
        <div class="my-4 mx-3">
            <p>To setup a device for live data collection, please follow the steps below. Note - this will give
                access to every live form owned by this team.
            <ol>
                <li>Open ODK Collect on your device.</li>
                <li>Tap the icon in the top-right to open the list of projects, then tap "Add Project" at
                    the bottom of the list.
                </li>
                <li>Scan the QR code below:</li>
            </ol>
            <div class="my-4 mx-3 d-flex justify-content-left">
                {!! QrCode::size(200)->generate($entry->odkProject->appUsers()->where('can_access_all_forms', 1)->first()->qr_code_string) !!}
            </div>
            </p>
        </div>
    </div>
</div>