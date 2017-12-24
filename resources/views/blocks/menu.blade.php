<div class="block {{isset($block->theme) ? $block->theme : 'col-sm-12'}}">
    @if(isset($translation))
        <div class="block-title">
            <h2>{{ $translation->title }}</h2>
        </div>
        <div class="block-body">
            {!! $translation->body !!}
        </div>
    @endif
</div>
