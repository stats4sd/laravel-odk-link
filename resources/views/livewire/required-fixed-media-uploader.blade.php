<form wire:submit="save">
    <div class="card mb-2
        @if($requiredMedia->hasMedia()) border-success @else border-dark @endif
        "
    >
        <div class="card-header">
            <h3 class="mb-0">
                {{ $requiredMedia->name }} ( {{ $requiredMedia->type }} file )
                @if($requiredMedia->hasMedia())
                    <i class="la la-check-circle"></i>
                @endif
            </h3>
        </div>

        <div class="card-body">

            <div class="row">
                <div class="col-md-6 col-12 d-flex justify-content-center align-items-center">
                    @if($requiredMedia->hasMedia())
                        @if($requiredMedia->type === "image")
                            <img src="{{ $this->fileUrl }}" width="200"/>
                        @elseif($requiredMedia->type === "audio")
                            ~~ AUDIO PREVIEW ~~
                        @elseif($requiredMedia->type === "video")
                            ~~ VIDEO PREVIEW ~~
                        @endif
                    @else
                        ~~ Preview will appear here after upload ~~
                    @endif
                </div>
                <div class="col-md-6 col-12">
                    <div
                        class="form-input-group"
                        x-data="{ uploading: false, progress: 0 }"
                        x-on:livewire-upload-start="uploading = true"
                        x-on:livewire-upload-finish="uploading = false; $dispatch('save')"
                        x-on:livewire-upload-error="uploading = false"
                        x-on:livewire-upload-progress="progress = $event.detail.progress"
                    >
                        <label class="form-label font-weight-bold" for="required_media__{{$requiredMedia->id}}">Add or replace file:</label>

                        <input class="form-input"
                               type="file"
                               wire:model="attachment"
                               id="required_media__{{$requiredMedia->id}}"
                        />

                        <div x-show="uploading">
                            <progress max="100" x-bind:value="progress"></progress>
                        </div>

                        @error('attachment')
                        <div class="text-danger"> {{ $message }} </div>
                        @enderror

                        <div class="mt-2">After uploading, click save to store the uploaded file.</div>

                        <div class="d-flex justify-content-start">
                            <button class="btn btn-primary mt-4 me-4" type="submit">Save</button>
                            <button class="btn btn-secondary mt-4 me-4" wire:click.prevent="removeAndReset">Remove Uploaded File</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
