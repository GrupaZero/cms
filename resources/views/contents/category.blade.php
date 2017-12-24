@extends('gzero-core::layouts.withRegions')
@section('bodyClass', $content->theme)

@section('title', $translation->seoTitle())
@section('seoDescription', $translation->seoDescription())
@section('head')
    @parent
    @include('gzero-cms::contents._canonical', ['paginator' => $children])
    @include('gzero-cms::contents._alternateLinks', ['content' => $content])
    @if(method_exists($content, 'stDataMarkup'))
        {!! $content->stDataMarkup($language->code) !!}
    @endif
@stop
@section('breadcrumbs')
    {!! Breadcrumbs::render('category') !!}
@stop
@section('content')
    @include('gzero-cms::contents._notPublishedContentMsg')
    <h1 class="content-title">
        {{ $translation->title }}
    </h1>
    {!! $translation->body !!}
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
