<div class="{{isset($block->theme) ? $block->theme : 'col-sm-12'}}">
    <div id="slider-{{$block->id}}" class="slider">
        <div class="jumbotron">
            @if(isset($translation))
                <h1>{{ $translation->title }}</h1>
                <p>{!! $translation->body !!}</p>
            @endif
            <p>
                <a class="btn btn-lg btn-success" href="{{ route('register') }}" role="button">
                    @lang('gzero-core::common.get_started_today')
                </a>
            </p>
        </div>
    </div>
</div>
