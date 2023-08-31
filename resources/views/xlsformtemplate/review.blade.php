@extends('backpack.theme-tabler::blank')

@section('content')

    <div class="container">

        <h1>{{ $xlsformTemplate->title }}</h1>

        <div class="bd-callout border-info mb-4">
            <h2>Prepare your form for use</h2>
            <p>Now you have uploaded your XLSForm file, there are some extra steps required to ensure your form is ready for use by members of this platform. The tabs below guide you through these steps. Some parts may not be needed - for example, your form may not have any attached media.</p>
            <p>At any time, you may review a draft version of the form on the first tab. As you update the form, this draft will also update.</p>
            <p>Once your form is ready, you can make it available to platform members by checking the box at the bottom of the page.</p>
        </div>

        <div class="card">
            <div class="card-header">
                <ul class="nav nav-tabs card-header-tabs" id="myTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="review-tab" data-bs-toggle="tab" data-bs-target="#review-tab-pane" type="button" role="tab" aria-controls="review-tab-pane" aria-selected="true">
                            <h3 class="mb-0">REVIEW FORM</h3>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="fixed-media-tab" data-bs-toggle="tab" data-bs-target="#fixed-media-tab-pane" type="button" role="tab" aria-controls="fixed-media-tab-pane" aria-selected="false">
                            <h3 class="mb-0">ATTACHED MEDIA</h3>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="data-media-tab" data-bs-toggle="tab" data-bs-target="#data-media-tab-pane" type="button" role="tab" aria-controls="data-media-tab-pane" aria-selected="false">
                            <h3 class="mb-0">ATTACHED DATASETS</h3>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="structure-tab" data-bs-toggle="tab" data-bs-target="#structure-tab-pane" type="button" role="tab" aria-controls="structure-tab-pane" aria-selected="false" structure>
                            <h3 class="mb-0">FORM STRUCTURE</h3>
                        </button>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content" id="myTabContent">
                    <div class="tab-pane fade" id="review-tab-pane" role="tabpanel" aria-labelledby="review-tab" tabindex="0">
                        @include('odk-link::xlsformtemplate._review')
                    </div>
                    <div class="tab-pane fade" id="fixed-media-tab-pane" role="tabpanel" aria-labelledby="fixed-media-tab" tabindex="0">
                        @include('odk-link::xlsformtemplate._attached-media')
                    </div>
                    <div class="tab-pane fade show active" id="data-media-tab-pane" role="tabpanel" aria-labelledby="data-media-tab" tabindex="0">
                        @include('odk-link::xlsformtemplate._attached-datasets')
                    </div>
                    <div class="tab-pane fade" id="structure-tab-pane" role="tabpanel" aria-labelledby="structure-tab" tabindex="0">
                        @include('odk-link::xlsformtemplate._form-structure')
                    </div>
                </div>
            </div>
        </div>


    </div>
@endsection

@section('after_styles')
    <style>
        .bd-callout {
            background-color: #fff;
            padding: 1.25rem;
            border: 1px solid #eee;
            border-left-width: 0.25rem;
            border-top-color: #eee !important;
            border-right-color: #eee !important;
            border-bottom-color: #eee !important;
            border-radius: 0.25rem;
        }
    </style>

@endsection

@section('after_scripts')
    @vite('resources/assets/js/odk-link.js', 'vendor/stats4sd/laravel-odk-link')
@endsection
