<h2>Attached Media Files</h2>

@if($xlsformTemplate->has('requiredFixedMedia'))
    <div class="bd-callout border-info mb-4">
        Based on your xls file, your form requires {{ $xlsformTemplate->requiredFixedMedia->count() }} media file{{ $xlsformTemplate->requiredFixedMedia->count() > 1 ? 's' : '' }}. Please upload all the required files below. They will be included in all instances of this form that are published by platform members.<br/><br/>

    </div>


@else
    <div class="bd-callout border-success mb-4">
        Based on your xls file, your form does not reference any fixed media files (e.g. images, videos or audio). You do not need to do anything on this page.
    </div>
@endif


<required-media-list :required-fixed-media="{{ $xlsformTemplate->requiredFixedMedia->toJson() }}"/>
