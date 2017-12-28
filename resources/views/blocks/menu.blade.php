<?php /* @var $block \Gzero\Cms\ViewModels\BlockViewModel */ ?>
<div class="block {{ $block->theme('col-sm-12') }}">
    @if($block->hasTitle())
        <div class="block-title">
            <h4>{{ $block->title() }}</h4>
        </div>
    @endif
    @if($block->hasBody())
        <div class="block-body">
            {!! $block->body() !!}
        </div>
    @endif
</div>
