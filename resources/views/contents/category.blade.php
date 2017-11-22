@extends('gzero-core::layouts.master')
@section('bodyClass', $content->theme)

@section('title'){{ $translation->seoTitle() }}@stop
@section('seoDescription'){{ $translation->seoDescription() }}@stop
@section('head')
    @parent
    @include('gzero-cms::includes.canonical', ['paginator' => $children])
    @include('gzero-cms::includes.alternateLinks', ['content' => $content])
    @if(method_exists($content, 'stDataMarkup'))
        {!! $content->stDataMarkup($language->code) !!}
    @endif
@stop
@section('mainContent')
    <div class="utility-container">
        <div class="container text-center-xs">
            {!! Breadcrumbs::render('category') !!}
        </div>
    </div>
    @parent
@stop
@section('content')
    @include('gzero-cms::includes.notPublishedContentMsg')
    <h1 class="content-title page-header">
        {{ $translation->title }}
    </h1>
    {!! $translation->body !!}
    @if($children)
        @foreach($children as $index => $child)
            <?php $childTranslation = $child->translation($language->code); ?>
            @if($childTranslation)
                <?php $childUrl = $child->routeUrl($language->code); ?>
                <div class="media">
                    <h2 class="page-header" title="{{ $childTranslation->title }}">
                        <a href="{{ $childUrl }}">
                            {{ $childTranslation->title }}
                        </a>
                    </h2>
                    <div class="media-body">
                        <div class="row article-meta">
                            <div class="col-xs-8">
                                <p class="text-muted">
                                    <small> @lang('gzero-core::common.posted_by') {{ $child->authorName() }}</small>
                                    <small>@lang('gzero-core::common.posted_on') {{ $child->publishDate() }}</small>
                                </p>
                            </div>
                            @if(config('disqus.enabled') && $child->is_comment_allowed)
                                <div class="col-xs-4 text-right">
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
                            <div class="thumb mb20">
                                <img class="img-responsive"
                                     title="{{($thumbTranslation)? $thumbTranslation->title : ''}}"
                                     src="{{croppaUrl($child->thumb->getFullPath(),
                                    config('gzero.image.thumb.width'), config('gzero.image.thumb.height'), ['resize'])}}"
                                     alt="{{($thumbTranslation)? $thumbTranslation->title : ''}}">
                            </div>
                        @endif
                        {!! $childTranslation->teaser !!}
                    </div>
                    <div class="row">
                        <div class="col-sm-4">
                            <a href="{{ $childUrl }}" class="btn btn-default read-more">
                                @lang('gzero-core::common.read_more')
                            </a>
                        </div>
                        <div class="col-sm-8 text-right text-left-xs mt20-xs">
                            <ul class="list-inline text-muted">
                                <li>
                                    @lang('gzero-core::common.rating') {!! $child->ratingStars() !!}
                                </li>
                                <li>
                                    @lang('gzero-core::common.number_of_views') {{ $child->visits }}
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                @if($index < sizeof($children) -1)
                    <hr/>
                @endif
            @endif
        @endforeach
        {!! $children->render() !!}
    @endif
@stop
@section('footerScripts')
    @if(config('disqus.enabled') && config('disqus.domain'))
        <script id="dsq-count-scr" src="//{{config('disqus.domain')}}.disqus.com/count.js" async></script>
    @endif
@stop
