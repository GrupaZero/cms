<?php /* @var $block \Gzero\Cms\ViewModels\BlockViewModel */ ?>
<div class="slider-block {{ $block->theme('col-sm-12') }}">
    @if($images->isEmpty())
        <div id="slider-{{$block->id()}}" class="slider">
            <div class="jumbotron">
                @if($block->hasTitle())
                    <h2>{{ $block->title() }}</h2>
                @endif
                @if($block->hasBody())
                    <p>{!! $block->body() !!}</p>
                @endif
            </div>
        </div>
    @elseif($images->count() > 1)
        <div id="slider-{{$block->id()}}" class="carousel slide carousel-fade" data-ride="carousel" data-interval="8000">
            <ol class="carousel-indicators">
                @foreach($images as $image)
                    <li data-target="#slider-{{$block->id()}}" data-slide-to="{{$image->id()}}"
                        class="{{($loop->first) ? 'active': ''}}"></li>
                @endforeach
            </ol>
            <div class="carousel-inner">
                @foreach($images as $image)
                    <div class="carousel-item{{($loop->first) ? ' active': ''}}"
                         style="background-image: url('{{croppaUrl($image->uploadPath(), 1110, 530)}}');">
                        <div class="carousel-caption">
                            <h5 class="display-3 font-weight-bold">{{$image->title()}}</h5>
                            <p class="lead">{{$image->description()}}</p>
                        </div>
                    </div>
                @endforeach
            </div>
            <a class="carousel-control-prev" href="#slider-{{$block->id()}}" role="button" data-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="sr-only">@lang('gzero-core::common.previous')</span>
            </a>
            <a class="carousel-control-next" href="#slider-{{$block->id()}}" role="button" data-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="sr-only">@lang('gzero-core::common.next')</span>
            </a>
        </div>
    @else
        <div class="card bg-dark text-white">
            @foreach($images as $image)
                <img class="card-img" src="{{croppaUrl($image->uploadPath(), 1110, 530)}}" alt="{{$image->title()}}">
                <div class="card-img-overlay">
                    @if($block->hasTitle())
                        <h5 class="card-title display-3 font-weight-bold">{{ $block->title() }}</h5>
                    @endif
                    @if($block->hasBody())
                        <p class="card-text lead">{!! $block->body() !!}</p>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</div>
