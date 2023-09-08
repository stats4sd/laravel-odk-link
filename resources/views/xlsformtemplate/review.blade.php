@extends('backpack.theme-tabler::blank')

@section('content')

    <div class="container" id="xlsform-template-review">

        <div class="d-flex justify-content-between align-items-center">
            <h1>{{ $xlsformTemplate->title }}</h1>
            <a href="{{ route('xlsform-template.edit', $xlsformTemplate)  }}" class="btn btn-primary">Edit XlsForm</a>
        </div>

        <div class="bd-callout border-info mb-4">
            <h2>Prepare your form for use</h2>
            <p>Now you have uploaded your XLSForm file, there are some extra steps required to ensure your form is ready for use by members of this platform. The tabs below guide you through these steps. Some parts may not be needed - for example, your form may not have any attached media.</p>
            <p>At any time, you may review a draft version of the form on the first tab. As you update the form, this draft will also update.</p>
            <p>Once your form is ready, you can make it available to platform members by checking the box at the bottom of the page.</p>
        </div>

        <div class="card">
            <div class="card-header">
                <ul class="nav nav-tabs card-header-tabs" id="review-tabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link review-tab" id="fixed-media-tab" data-bs-toggle="tab" data-bs-target="#fixed-media-tab-pane" type="button" role="tab" aria-controls="fixed-media-tab-pane" aria-selected="false">
                            <h3 class="mb-0">ATTACHED MEDIA</h3>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link review-tab active" id="data-media-tab" data-bs-toggle="tab" data-bs-target="#data-media-tab-pane" type="button" role="tab" aria-controls="data-media-tab-pane" aria-selected="false">
                            <h3 class="mb-0">ATTACHED DATASETS</h3>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link review-tab" id="review-tab" data-bs-toggle="tab" data-bs-target="#review-tab-pane" type="button" role="tab" aria-controls="review-tab-pane" aria-selected="true">
                            <h3 class="mb-0">REVIEW FORM</h3>
                        </button>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content" id="review-tabs-content">
                    <div class="tab-pane fade " id="fixed-media-tab-pane" role="tabpanel" aria-labelledby="fixed-media-tab" tabindex="0">
                        @include('odk-link::xlsformtemplate._attached-media')
                    </div>
                    <div class="tab-pane fade show active" id="data-media-tab-pane" role="tabpanel" aria-labelledby="data-media-tab" tabindex="0">
                        @include('odk-link::xlsformtemplate._attached-datasets')
                    </div>
                    <div class="tab-pane fade" id="review-tab-pane" role="tabpanel" aria-labelledby="review-tab" tabindex="0">
                        @include('odk-link::xlsformtemplate._review')
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
    @vite(env('ODK_LINK_PACKAGE_PATH') . 'resources/assets/js/form-media.js')

    {{-- Keep Tab and url query param updated --}}
    <script>
        $(document).ready(() => {

            var triggerTabList = [].slice.call(document.querySelectorAll('#review-tabs button'))
            triggerTabList.forEach(function (triggerEl) {
                var tabTrigger = new bootstrap.Tab(triggerEl)

                triggerEl.addEventListener('click', function (event) {
                    event.preventDefault()
                    tabTrigger.show()
                })
            })


            let url = location.href.replace(/\/$/, "");

            if (location.hash) {
                const hash = url.split("#");
                let tabEl = document.querySelector('#review-tabs button#' + hash[1]) ?? null;
                console.log(tabEl)
                if (tabEl) {
                    bootstrap.Tab.getInstance(tabEl).show();
                    url = location.href.replace(/\/#/, "#");
                    history.replaceState(null, null, url);
                    setTimeout(() => {
                        $(window).scrollTop(0);
                    }, 400);
                }
            } else {

                // get set default
                let tabEl = document.querySelector('#review-tabs button#fixed-media-tab')
                bootstrap.Tab.getInstance(tabEl).show();
                url = location.href.replace(/\/#/, "#");
                history.replaceState(null, null, url);
                setTimeout(() => {
                    $(window).scrollTop(0);
                }, 400);
            }

            $('.review-tab').on("click", function () {
                let newUrl;
                const hash = this.id;
                if (hash == "fixed-media-tab") {
                    newUrl = url.split("#")[0];
                } else {
                    newUrl = url.split("#")[0] + "#" + hash;
                }

                console.log(newUrl)
                newUrl += "/";
                history.replaceState(null, null, newUrl);

            });
        });
    </script>
@endsection
