<?php /* @var $block \Gzero\Cms\ViewModels\BlockViewModel */ ?>
<div class="{{ $block->theme('col-sm-12') }}">
    <div id="slider-{{$block->id()}}" class="slider">
        <div class="jumbotron">
            @if($block->hasTitle())
                <h2>{{ $block->title() }}</h2>
            @endif
            @if($block->hasBody())
                <p>{!! $block->body() !!}</p>
            @endif
            <p>
                <a class="btn btn-lg btn-success" href="{{ route('register') }}" role="button">
                    @lang('gzero-core::common.get_started_today')
                </a>
            </p>
        </div>
    </div>
</div>
