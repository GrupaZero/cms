<?php /* @var $block \Gzero\Cms\Presenters\BlockPresenter */ ?>
<div class="{{ $block->getTheme('col-sm-12') }}">
    <div id="slider-{{$block->getId()}}" class="slider">
        <div class="jumbotron">
            @if($block->hasTitle())
                <h2>{{ $block->getTitle() }}</h2>
            @endif
            @if($block->hasBody())
                <p>{!! $block->getBody() !!}</p>
            @endif
            <p>
                <a class="btn btn-lg btn-success" href="{{ route('register') }}" role="button">
                    @lang('gzero-core::common.get_started_today')
                </a>
            </p>
        </div>
    </div>
</div>
