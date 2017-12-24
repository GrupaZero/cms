<?php /* @var $child \Gzero\Cms\Presenters\ContentPresenter */ ?>
<article class="{{ $child->isSticky() ? 'is-sticky' : '' }}{{ $child->isPromoted() ? ' is-promoted' : '' }}">
    <h2 class="article-title">
        <a href="{{ $child->getUrl() }}">
            {{ $child->getTitle() }}
        </a>
    </h2>
    <div class="row justify-content-between article-meta">
        <div class="col col-md-auto">
            <p class="text-muted">
                <small>@lang('gzero-core::common.posted_by') {{ $child->getAuthorName() }}</small>
                <small>@lang('gzero-core::common.posted_on') {{ $child->getPublishDate() }}</small>
            </p>
        </div>
        @if(config('gzero-cms.disqus.enabled') && $child->isCommentAllowed())
            <div class="col-auto">
                <a href="{{ $child->getUrl() }}#disqus_thread"
                   data-disqus-identifier="{{ $child->getId() }}"
                   class="disqus-comment-count">
                    0 @lang('gzero-core::common.comments')
                </a>
            </div>
        @endif
    </div>
    @if($child->hasThumbnail())
        <?php $thumbTranslation = $child->thumb->translation($language->code); ?>
        <div class="row mb-2">
            <div class="col">
                <img class="img-fluid img-thumbnail"
                     title="{{($thumbTranslation)? $thumbTranslation->title : ''}}"
                     src="{{croppaUrl($child->thumb->getFullPath(),
                                config('gzero.image.thumb.width'), config('gzero.image.thumb.height'), ['resize'])}}"
                     alt="{{($thumbTranslation)? $thumbTranslation->title : ''}}">
            </div>
        </div>
    @endif
    {!! $child->getTeaser() !!}
    <div class="row justify-content-md-between">
        <div class="col-12 col-md-auto mb-3 mb-md-0">
            <a href="{{ $child->getUrl() }}" class="btn btn-outline-primary">
                @lang('gzero-core::common.read_more')
            </a>
        </div>
    </div>
</article>
@if($loop->remaining)
    <hr class="my-4"/>
@endif