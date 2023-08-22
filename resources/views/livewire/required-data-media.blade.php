<div class="card mb-2
        @if($requiredMedia->hasMedia()) border-success @else border-dark @endif
        "
>

    <div class="card-header d-flex justify-content-start align-items-center">
        <h3 class="mb-0 me-4">
            {{ $requiredMedia->name }} ( csv file )
            @if($requiredMedia->hasMedia())
                <i class="la la-check-circle"></i>
            @endif
        </h3>

        <div class="form-check form-switch d-flex align-items-center mb-0">
            <input class="form-check-input mb-0 me-2" type="checkbox" id="flexSwitchCheckDefault" wire:model="isStaticFile" wire:change="updateType">
            <label class="form-check-label mb-0" for="flexSwitchCheckDefault">Is a static file?</label>
        </div>
    </div>

    <div class="card-body">
        @if($requiredMedia->is_static)
            <livewire:odk-link::required-data-media-uploader :required-media="$requiredMedia"/>
        @else
            <h4>Define Dataset</h4>
            The {{ $requiredMedia->name }} file will contain the latest data from a dataset defined by individual form owners. When form owners deploy this form, they will be asked to add their data with the column headers you specify here.

            <hr/>
            <div class="row">
                <div class="col-12 col-lg-6 p-4">

                    <form wire:submit="pickDataset">
                        <div class="mb-3 me-4 pe-4">
                            <label for="exampleFormControlInput1" class="form-label">
                                Step 1. Select Dataset
                            </label>
                            <select class="form-select" aria-label="Select Dataset" wire:model="datasetId">
                                    <option value='' disabled selected>Select Dataset...</option>
                                @foreach($datasets as $dataset)
                                    <option wire:key="{{ $dataset->id }}" value="{{ $dataset->id }}">{{ $dataset->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <button class="btn btn-primary" type="submit">Save</button>
                    </form>
                </div>

                <div class="col-12 col-lg-6 p-4">
                    <h4>Step 2: Define Dataset Variables</h4>
                    @if($dataset)
                        @foreach($dataset->variables as $variable)
                            <livewire:odk-link::dataset-variable
                                wire:key="{{ $variable->id }}" :variable="$variable"></livewire:odk-link::dataset-variable>
                        @endforeach
                        <button class="btn btn-info" wire:click="addVariable">+ Add Variable</button>
                    @else
                        ~ Enter a dataset name and click save before adding variables ~
                    @endif


                </div>
                @endif
            </div>
    </div>

    @teleport('body')
    <!-- Include these scripts somewhere on the page: -->
    <script defer src="https://unpkg.com/@alpinejs/ui@3.12.3-beta.0/dist/cdn.min.js"></script>
    <script defer src="https://unpkg.com/@alpinejs/focus@3.12.3/dist/cdn.min.js"></script>
    <script defer src="https://unpkg.com/alpinejs@3.12.3/dist/cdn.min.js"></script>

    @endteleport
</div>
