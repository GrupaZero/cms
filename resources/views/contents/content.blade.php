<?php /* @var $content \Gzero\Cms\Presenters\ContentPresenter */ ?>
@extends('gzero-core::layouts.withRegions')
@section('bodyClass', $content->getTheme())

@section('metaData')
    @if(isProviderLoaded('Gzero\Social\ServiceProvider') && function_exists('fbOgTags'))
        {!! fbOgTags($content->getUrl(), $content->translation) !!}
    @endif
@stop

@section('title', $content->getSeoTitle())
@section('seoDescription', $content->getSeoDescription())
@section('head')
    @parent
    @include('gzero-cms::contents._canonical')
    @include('gzero-cms::contents._alternateLinks', ['content' => $content])
    @if(method_exists($content, 'stDataMarkup'))
        {!! $content->stDataMarkup($language->code) !!}
    @endif
@stop

@section('breadcrumbs')
    {!! Breadcrumbs::render('content') !!}
@stop

@section('content')
    @include('gzero-cms::contents._notPublishedContentMsg')
    <h1 class="content-title">
        {{ $content->getTitle() }}
    </h1>
    <div class="row justify-content-md-between content-meta">
        <div class="col-12 col-md-auto">
            <p class="content-author text-muted">
                <i>@lang('gzero-core::common.posted_by') {{ $content->getAuthor()->displayName() }}</i>
                <i>@lang('gzero-core::common.posted_on') {{ $content->getPublishDate() }}</i>
            </p>
        </div>
        @if(isProviderLoaded('Gzero\Social\ServiceProvider') && function_exists('shareButtons'))
            <div class="col-12 col-md-auto">
                <div class="social-buttons">
                    {!! shareButtons($content->getUrl(), $content->translation) !!}
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
            {!! $content->getTeaser() !!}
        </p>
    @endif
    {!! $content->getBody() !!}
    {{--@include('gzero-cms::contents._gallery', ['images' => $images, 'thumb' => $content->thumb])--}}
    @if(config('gzero-cms.disqus.enabled') && $content->isCommentAllowed())
        <div class="row">
            <div class="col">
                <div class="text-center">
                    @include('gzero-cms::contents._disqus', ['contentId' => $content->getId(), 'url' => $content->getUrl()])
                </div>
            </div>
        </div>
    @endif
    <hr>
    @if(isProviderLoaded('Gzero\Social\ServiceProvider') && function_exists('likeButtons'))
        <div class="row mb-2">
            <div class="col">
                <div class="social-buttons">
                    {!! likeButtons($content->getUrl(), $content->translation) !!}
                </div>
            </div>
        </div>
    @endif
@stop
