<?php /* @var $child \Gzero\Cms\ViewModels\ContentViewModel */ ?>
<article class="{{ $child->isSticky() ? 'is-sticky' : '' }}{{ $child->isPromoted() ? ' is-promoted' : '' }}">
    <h2 class="article-title">
        <a href="{{ $child->url() }}">
            {{ $child->title() }}
        </a>
    </h2>
    <div class="row justify-content-between article-meta">
        <div class="col col-md-auto">
            <p class="text-muted">
                <small>@lang('gzero-core::common.posted_by') {{ $child->author()->displayName() }}</small>
                <small>@lang('gzero-core::common.posted_on') {{ $child->publishedAt() }}</small>
            </p>
        </div>
        @if(config('gzero-cms.disqus.enabled') && $child->isCommentAllowed())
            <div class="col-auto">
                <a href="{{ $child->url() }}#disqus_thread"
                   data-disqus-identifier="{{ $child->id() }}"
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
    {!! $child->teaser() !!}
    <div class="row justify-content-md-between">
        <div class="col-12 col-md-auto mb-3 mb-md-0">
            <a href="{{ $child->url() }}" class="btn btn-outline-primary">
                @lang('gzero-core::common.read_more')
            </a>
        </div>
    </div>
</article>
@if($loop->remaining)
    <hr class="my-4"/>
@endif