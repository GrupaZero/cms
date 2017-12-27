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
    @include('gzero-cms::contents._canonical', ['paginator' => $children])
    @include('gzero-cms::contents._alternateLinks', ['content' => $content])
    @include('gzero-cms::contents._stDataMarkup', ['content' => $content])
@stop
@section('breadcrumbs')
    {!! Breadcrumbs::render('category') !!}
@stop
@section('content')
    @include('gzero-cms::contents._notPublishedContentMsg')
    <h1 class="content-title">
        {{ $content->getTitle() }}
    </h1>
    {!! $content->getBody() !!}
    @if($children)
        @foreach($children as $index => $child)
            @include('gzero-cms::contents._article', ['child' => $child])
        @endforeach
        {!! $children->render() !!}
    @endif
    <div class="w-100 my-4"></div>
@stop
@section('footerScripts')
    @if(config('gzero-cms.disqus.enabled') && config('gzero-cms.disqus.domain'))
        <script id="dsq-count-scr" src="//{{config('gzero-cms.disqus.domain')}}.disqus.com/count.js" async></script>
    @endif
@stop
