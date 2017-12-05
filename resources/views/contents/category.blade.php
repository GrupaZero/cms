@extends('gzero-core::layouts.master')
@section('bodyClass', $content->theme)

@section('title'){{ $translation->seoTitle() }}@stop
@section('seoDescription'){{ $translation->seoDescription() }}@stop
@section('head')
    @parent
    @include('gzero-cms::contents._canonical', ['paginator' => $children])
    @include('gzero-cms::contents._alternateLinks', ['content' => $content])
    @if(method_exists($content, 'stDataMarkup'))
        {!! $content->stDataMarkup($language->code) !!}
    @endif
@stop
@section('mainContent')
    {!! Breadcrumbs::render('category') !!}
    @parent
@stop
@section('content')
    <div class="row justify-content-md-center">
        <div class="col col-md-auto">
            @include('gzero-cms::contents._notPublishedContentMsg')
        </div>
    </div>
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
    @if(config('disqus.enabled') && config('disqus.domain'))
        <script id="dsq-count-scr" src="//{{config('disqus.domain')}}.disqus.com/count.js" async></script>
    @endif
@stop
