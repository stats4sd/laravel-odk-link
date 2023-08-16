<h2>Attached Media Files</h2>

@if($xlsformTemplate->has('requiredFixedMedia'))
    <div class="bd-callout border-info mb-4">
        Based on your xls file, your form requires {{ $xlsformTemplate->requiredFixedMedia->count() }} media file{{ $xlsformTemplate->requiredFixedMedia->count() > 1 ? 's' : '' }}. Please upload all the required files below. They will be included in all instances of this form that are published by platform members.
    </div>
@else
    <div class="bd-callout border-success mb-4">
        Based on your xls file, your form does not reference any fixed media files (e.g. images, videos or audio). If you know this is incorrect, for example if you reference media file names through variables in your form, you may still upload media files below.
    </div>
@endif

    @foreach($xlsformTemplate->requiredFixedMedia as $requiredMedia)

        <div class="card mb-2">
            <div class="card-header">
                <h3 class="mb-0">{{ $requiredMedia->name }} ( {{ $requiredMedia->type }} file ) </h3>
            </div>

            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 col-12 d-flex justify-content-center">
                        @if($requiredMedia->has('attachment'))
                            @if($requiredMedia->type === "image")
                                <img src="{{ $requiredMedia->attachment->getUrl() }}" width="200"/>
                            @elseif($requiredMedia->type === "audio")
                                ~~ AUDIO PREVIEW ~~
                            @elseif($requiredMedia->type === "video")
                                ~~ VIDEO PREVIEW ~~
                            @endif
                        @else
                            ~~ Preview will appear here after upload ~~
                        @endif
                    </div>
                    <div class="col-md-6 col-12 d-flex align-items-center">
                        <div class="form-input-group">
                            <label class="form-label" for="required_media__{{$requiredMedia->id}}">Add or replace file:</label>
                            <input class="form-input" type="file" name="required_media__{{$requiredMedia->id}}"/>
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Save</button>
            </div>
        </div>

    @endforeach
