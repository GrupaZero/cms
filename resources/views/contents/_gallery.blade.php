@if($images->isNotEmpty())
    <div class="row image-gallery">
        @foreach($images as $image)
            <div class="col-sm-6 col-md-3 image text-center mb-4">
                <a href="{{croppaUrl($image->uploadPath(), config('gzero.image.max_width'), config('gzero.image.max_height'),
                ['resize'])}}" class="thumbnail colorbox"
                   rel="gallery" title="{{$image->title()}}">
                    <img class="img-fluid"
                         title="{{$image->title() . ' ' . $image->description()}}"
                         src="{{croppaUrl($image->uploadPath(), 280, 180, ['resize'])}}"
                         alt="{{$image->title()}}">
                </a>
            </div>
        @endforeach
    </div>
@endif