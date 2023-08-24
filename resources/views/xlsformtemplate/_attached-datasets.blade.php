<h2>Attached Datasets</h2>

@if($xlsformTemplate->has('requiredDataMedia'))
    <div class="bd-callout border-info mb-4">
        Based on your xls file, your form requires {{ $xlsformTemplate->requiredDataMedia->count() }} dataset{{ $xlsformTemplate->requiredDataMedia->count() > 1 ? 's' : '' }} to be attached to the form. On this page, you can either upload a static file containing the correct data, or link the form to a dataset within this platform.
    </div>
@else
    <div class="bd-callout border-success mb-4">
        Based on your xls file, your form does not reference any dataset media files (e.g. csv files).
    </div>
@endif

    @foreach($xlsformTemplate->requiredDataMedia as $requiredMedia)

        <livewire:odk-link::required-data-media wire:key="{{ $requiredMedia->id }}" :required-media="$requiredMedia"/>

    @endforeach
