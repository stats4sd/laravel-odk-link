<div>

    <h3>Main Survey</h3>

    <div class="bd-callout border-info mb-4">The following variables are present at the 'top' level of the form. You may add these to a dataset within the platform. When submissions are sent to this form, the data will be added to the linked dataset, and will be available for download by the platform members or for linking to other ODK forms.</div>

    <form wire:submit="pickDataset">
        <div class="mb-3 me-4 pe-4">
            <label for="exampleFormControlInput1" class="form-label">
                Step 1. What is the primary data subject?
            </label>
            <div class="d-flex justify-content-between align-items-center">

                <select class="form-select" aria-label="Select Dataset" wire:model="rootDatasetId">
                    <option value='' disabled selected>Select Dataset...</option>
                    @foreach($datasets as $dataset)
                        <option wire:key="{{ $dataset->id }}" value="{{ $dataset->id }}">{{ $dataset->name }}</option>
                    @endforeach
                </select>
                <button class="btn btn-info" wire:click='createRootDataset' data-bs-target="#datasetModal" data-bs-toggle="modal">+ Add Dataset / Data Subject</button>
            </div>
        </div>

        <button class="btn btn-primary" type="submit">Save</button>
    </form>

    <ul class="list-group">
        @foreach($xlsformTemplate->root_fields as $field)
            <li class="list-group-item">{{ $field['name'] }} ({{ $field['selectMultiple'] ? 'select multiple' : $field['type'] }})</li>
        @endforeach
    </ul>

    @teleport('body')
    <div class="modal fade" id="datasetModal" tabindex="-1" aria-labelledby="datasetModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="datasetModalLabel">Add Dataset for {{ $structure }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="exampleFormControlInput1" class="form-label">Dataset Name</label>
                        <input class="form-control" placeholder="dataset-name" wire:model="newDatasetName">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" wire:click="storeDataset" data-bs-dismiss="modal">Save changes</button>
                </div>
            </div>
        </div>
    </div>

    @endteleport

    @dump($xlsformTemplate->root_fields)

    @dump($xlsformTemplate->structure)
</div>
