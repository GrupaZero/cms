<?php /* @var $block \Gzero\Cms\Presenters\BlockPresenter */ ?>
<div class="block {{ $block->getTheme('col-sm-12') }}">
    @if($block->hasTitle())
        <div class="block-title">
            <h4>{{ $block->getTitle() }}</h4>
        </div>
    @endif
    @if($block->hasBody())
        <div class="block-body">
            {!! $block->getBody() !!}
        </div>
    @endif
</div>
