@extends('gzero-core::layouts.master')
@section('bodyClass', $content->theme)
<?php $url = $content->routeUrl($language->code); ?>

@section('metaData')
    @if(isProviderLoaded('Gzero\Social\ServiceProvider') && function_exists('fbOgTags'))
        {!! fbOgTags($url, $translation) !!}
    @endif
@stop

@section('title'){{ $translation->seoTitle() }}@stop
@section('seoDescription'){{ $translation->seoDescription() }}@stop
@section('head')
    @parent
    @include('gzero-cms::includes.canonical')
    @include('gzero-cms::includes.alternateLinks', ['content' => $content])
    @if(method_exists($content, 'stDataMarkup'))
        {!! $content->stDataMarkup($language->code) !!}
    @endif
@stop
@section('mainContent')
    <div class="utility-container">
        <div class="container text-center-xs">
            {!! Breadcrumbs::render('content') !!}
        </div>
    </div>
    @parent
@stop
@section('content')
    @include('gzero-cms::includes.notPublishedContentMsg')
    <h1 class="content-title page-header">
        {{ $translation->title }}
    </h1>
    <div class="row content-meta">
        <div class="col-sm-7">
            <p class="content-author text-muted">
                <i>@lang('gzero-core::common.posted_by') {{ $content->authorName() }}</i>
                <i>@lang('gzero-core::common.posted_on') {{ $content->publishDate() }}</i>
            </p>
        </div>
        <div class="col-sm-5 text-right text-left-sm text-left-xs">
            @if(isProviderLoaded('Gzero\Social\ServiceProvider') && function_exists('shareButtons'))
                <div class="social-buttons mb15">
                    {!! shareButtons($url, $translation) !!}
                </div>
            @endif
        </div>
    </div>
    @if($content->thumb)
        <?php $thumbTranslation = $content->thumb->translation($language->code); ?>
        <div class="thumb mb20">
            <img class="img-responsive"
                 title="{{($thumbTranslation)? $thumbTranslation->title : ''}}"
                 src="{{croppaUrl($content->thumb->getFullPath(),
                  config('gzero.image.thumb.width'), config('gzero.image.thumb.height'), ['resize'])}}"
                 alt="{{($thumbTranslation)? $thumbTranslation->title : ''}}">
        </div>
    @endif
    {!! $translation->body !!}
    @include('gzero-cms::includes.gallery', ['images' => $images, 'thumb' => $content->thumb])
    @if(config('disqus.enabled') && $content->is_comment_allowed)
        <div class="text-center">
            @include('gzero-cms::includes.disqus.disqus', ['contentId' => $content->id, 'url' => $url])
        </div>
    @endif
    <hr>
    @if(isProviderLoaded('Gzero\Social\ServiceProvider') && function_exists('likeButtons'))
        <div class="social-buttons mb15">
            {!! likeButtons($url, $translation) !!}
        </div>
    @endif
    <div class="text-muted text-right">
        @lang('gzero-core::common.rating') {!! $content->ratingStars() !!}
    </div>
@stop
