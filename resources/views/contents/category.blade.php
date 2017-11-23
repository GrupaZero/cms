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
    <div class="float-left">
        {!! Breadcrumbs::render('category') !!}
    </div>
    @parent
@stop
@section('content')
    <div class="row justify-content-md-center">
        <div class="col col-md-auto">
            @include('gzero-cms::includes.notPublishedContentMsg')
        </div>
    </div>
    <h1 class="content-title">
        {{ $translation->title }}
    </h1>
    {!! $translation->body !!}
    @if($children)
        @foreach($children as $index => $child)
            <?php $childTranslation = $child->translation($language->code); ?>
            @if($childTranslation)
                <?php $childUrl = $child->routeUrl($language->code); ?>
                <h2 title="{{ $childTranslation->title }}">
                    <a href="{{ $childUrl }}">
                        {{ $childTranslation->title }}
                    </a>
                </h2>
                <div class="row justify-content-between article-meta">
                    <div class="col col-md-auto">
                        <p class="text-muted">
                            <small> @lang('gzero-core::common.posted_by') {{ $child->authorName() }}</small>
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
                    <div class="col col-md-auto">
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
                @if($index < sizeof($children) -1)
                    <hr class="my-4"/>
                @endif
            @endif
        @endforeach
        {!! $children->render() !!}
    @endif
    <div class="w-100 my-4"></div>
@stop
@section('footerScripts')
    @if(config('disqus.enabled') && config('disqus.domain'))
        <script id="dsq-count-scr" src="//{{config('disqus.domain')}}.disqus.com/count.js" async></script>
    @endif
@stop
