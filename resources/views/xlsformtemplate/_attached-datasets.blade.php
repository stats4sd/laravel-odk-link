<h2>Attached Datasets</h2>

@if($xlsformTemplate->has('requiredDataMedia'))
    <div class="bd-callout border-info mb-4">
        Based on your xls file, your form requires {{ $xlsformTemplate->requiredDataMedia->count() }} dataset{{ $xlsformTemplate->requiredDataMedia->count() > 1 ? 's' : '' }} to be attached to the form. <br/><br/>
        For each dataset listed, you can either:
        <ul>
            <li>upload a static file containing the data to be used by <b>every</b> user on the platform. This is exactly the same as uploading a csv file directly to your ODK Aggregate Server.</li>
            <li>Define a "dataset" that will be used. You should give the dataset a name and specify what variables are required by the form. Individual users or teams will be asked to add their own data that conforms to the structure you define.</li>
    </div>
@else
    <div class="bd-callout border-success mb-4">
        Based on your xls file, your form does not reference any dataset media files (e.g. csv files). You do not need to do anything on this page.
    </div>
@endif

    @foreach($xlsformTemplate->requiredDataMedia as $requiredMedia)

        <livewire:odk-link::required-data-media wire:key="{{ $requiredMedia->id }}" :required-media="$requiredMedia"/>

    @endforeach
