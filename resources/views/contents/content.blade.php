<?php /* @var $content \Gzero\Cms\ViewModels\ContentViewModel */ ?>
@extends('gzero-core::layouts.withRegions')
@section('bodyClass', $content->theme())

@section('metaData')
    @if(isProviderLoaded('Gzero\Social\ServiceProvider') && function_exists('fbOgTags'))
        {!! fbOgTags($content->url(), $content->translation) !!}
    @endif
@stop

@section('title', $content->seoTitle())
@section('seoDescription', $content->seoDescription())
@section('head')
    @parent
    @include('gzero-cms::contents._canonical')
    @include('gzero-cms::contents._alternateLinks', ['content' => $content])
    @include('gzero-cms::contents._stDataMarkup', ['content' => $content])
@stop

@section('breadcrumbs')
    {!! Breadcrumbs::render('content') !!}
@stop

@section('content')
    @include('gzero-cms::contents._notPublishedContentMsg')
    <h1 class="content-title">
        {{ $content->title() }}
    </h1>
    <div class="row justify-content-md-between content-meta">
        <div class="col-12 col-md-auto">
            <p class="content-author text-muted">
                <i>@lang('gzero-core::common.posted_by') {{ $content->author()->displayName() }}</i>
                <i>@lang('gzero-core::common.posted_on') {{ $content->publishedAt() }}</i>
            </p>
        </div>
        @if(isProviderLoaded('Gzero\Social\ServiceProvider') && function_exists('shareButtons'))
            <div class="col-12 col-md-auto">
                <div class="social-buttons">
                    {!! shareButtons($content->url(), $content->translation) !!}
                </div>
            </div>
        @endif
    </div>
    @if($content->hasThumbnail())
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
    @if($content->hasTeaser())
        <p class="lead">
            {!! $content->teaser() !!}
        </p>
    @endif
    {!! $content->body() !!}
    {{--@include('gzero-cms::contents._gallery', ['images' => $images, 'thumb' => $content->thumb])--}}
    @if(config('gzero-cms.disqus.enabled') && $content->isCommentAllowed())
        <div class="row">
            <div class="col">
                <div class="text-center">
                    @include('gzero-cms::contents._disqus', ['contentId' => $content->id(), 'url' => $content->url()])
                </div>
            </div>
        </div>
    @endif
    <hr>
    @if(isProviderLoaded('Gzero\Social\ServiceProvider') && function_exists('likeButtons'))
        <div class="row mb-2">
            <div class="col">
                <div class="social-buttons">
                    {!! likeButtons($content->url(), $content->translation) !!}
                </div>
            </div>
        </div>
    @endif
@stop
