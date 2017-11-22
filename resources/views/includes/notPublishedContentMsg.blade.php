@if(!$content->is_active)
    <div class="alert alert-warning" role="alert">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
        @lang('gzero-core::common.content_not_published')
    </div>
@endif