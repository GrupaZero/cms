<?php $childTranslation = $child->translation($language->code); ?>
@if($childTranslation)
    <?php $childUrl = $child->routeUrl($language->code); ?>
    <article class="{{ $child->is_sticky ? 'is-sticky' : '' }}{{ $child->is_promoted ? ' is-promoted' : '' }}">
        <h2 class="article-title">
            <a href="{{ $childUrl }}">
                {{ $childTranslation->title }}
            </a>
        </h2>
        <div class="row justify-content-between article-meta">
            <div class="col col-md-auto">
                <p class="text-muted">
                    <small>@lang('gzero-core::common.posted_by') {{ $child->authorName() }}</small>
                    <small>@lang('gzero-core::common.posted_on') {{ $child->publishDate() }}</small>
                </p>
            </div>
            @if(config('disqus.enabled') && $child->is_comment_allowed)
                <div class="col-auto">
                    <a href="{{ $childUrl }}#disqus_thread"
                       data-disqus-identifier="{{ $child->id }}"
                       class="disqus-comment-count">
                        0 @lang('gzero-core::common.comments')
                    </a>
                </div>
            @endif
        </div>
        @if($child->thumb)
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
        {!! $childTranslation->teaser !!}
        <div class="row justify-content-md-between">
            <div class="col-12 col-md-auto mb-3 mb-md-0">
                <a href="{{ $childUrl }}" class="btn btn-outline-primary">
                    @lang('gzero-core::common.read_more')
                </a>
            </div>
            <div class="col col-md-auto">
                <ul class="list-inline text-muted">
                    <li class="list-inline-item">
                        @lang('gzero-core::common.rating') {!! $child->ratingStars() !!}
                    </li>
                    <li class="list-inline-item">
                        @lang('gzero-core::common.number_of_views') {{ $child->visits }}
                    </li>
                </ul>
            </div>
        </div>
    </article>
    @if($loop->remaining)
        <hr class="my-4"/>
    @endif
@endif