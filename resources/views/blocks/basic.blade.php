<div class="block {{isset($block->theme) ? $block->theme : 'col-sm-12'}}">
    @if(isset($translation))
        <div class="block-title">
            <h4>{{ $translation->title }}</h4>
        </div>
        <div class="block-body">
            {!! $translation->body !!}
        </div>
    @endif
</div>
