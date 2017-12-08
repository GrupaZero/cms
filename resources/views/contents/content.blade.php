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
    @include('gzero-cms::contents._canonical')
    @include('gzero-cms::contents._alternateLinks', ['content' => $content])
    @if(method_exists($content, 'stDataMarkup'))
        {!! $content->stDataMarkup($language->code) !!}
    @endif
@stop
@section('mainContent')
    {!! Breadcrumbs::render('content') !!}
    @parent
@stop
@section('content')
    <div class="row justify-content-md-center">
        <div class="col-12 col-md-auto">
            @include('gzero-cms::contents._notPublishedContentMsg')
        </div>
    </div>
    <h1 class="content-title">
        {{ $translation->title }}
    </h1>
    <div class="row justify-content-md-between content-meta">
        <div class="col-12 col-md-auto">
            <p class="content-author text-muted">
                <i>@lang('gzero-core::common.posted_by') {{ $content->authorName() }}</i>
                <i>@lang('gzero-core::common.posted_on') {{ $content->publishDate() }}</i>
            </p>
        </div>
        @if(isProviderLoaded('Gzero\Social\ServiceProvider') && function_exists('shareButtons'))
            <div class="col-12 col-md-auto">
                <div class="social-buttons">
                    {!! shareButtons($url, $translation) !!}
                </div>
            </div>
        @endif
    </div>
    @if($content->thumb)
        <?php $thumbTranslation = $content->thumb->translation($language->code); ?>
        <div class="row mb-2">
            <div class="col">
                <img class="img-fluid img-thumbnail"
                     title="{{($thumbTranslation)? $thumbTranslation->title : ''}}"
                     src="{{croppaUrl($content->thumb->getFullPath(),
                        config('gzero.image.thumb.width'), config('gzero.image.thumb.height'), ['resize'])}}"
                     alt="{{($thumbTranslation)? $thumbTranslation->title : ''}}">
            </div>
        </div>
    @endif
    @if(!empty($translation->teaser))
        <p class="lead">
            {!! $translation->teaser !!}
        </p>
    @endif
    {!! $translation->body !!}
    @include('gzero-cms::contents._gallery', ['images' => $images, 'thumb' => $content->thumb])
    @if(config('gzero-cms.disqus.enabled') && $content->is_comment_allowed)
        <div class="row">
            <div class="col">
                <div class="text-center">
                    @include('gzero-cms::contents._disqus', ['contentId' => $content->id, 'url' => $url])
                </div>
            </div>
        </div>
    @endif
    <hr>
    @if(isProviderLoaded('Gzero\Social\ServiceProvider') && function_exists('likeButtons'))
        <div class="row mb-2">
            <div class="col">
                <div class="social-buttons">
                    {!! likeButtons($url, $translation) !!}
                </div>
            </div>
        </div>
    @endif
    <div class="row justify-content-end mb-2">
        <div class="col-auto">
            <div class="text-muted">
                @lang('gzero-core::common.rating') {!! $content->ratingStars() !!}
            </div>
        </div>
    </div>
@stop
