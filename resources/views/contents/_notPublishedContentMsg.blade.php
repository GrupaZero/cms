@if(!$content->canBeShown())
    <div class="row justify-content-md-center">
        <div class="col-12 col-md-auto">
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                @lang('gzero-core::common.content_not_published')
            </div>
        </div>
    </div>
@endif