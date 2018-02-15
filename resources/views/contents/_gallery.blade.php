@if($images->isNotEmpty())
    <div class="row image-gallery">
        @foreach($images as $image)
            <div class="col-sm-6 col-md-3 image text-center mb-4">
                <a href="{{croppaUrl($image->uploadPath())}}" class="thumbnail"
                   rel="gallery" title="{{$image->title()}}">
                    <img class="img-fluid colorbox"
                         title="{{$image->title() . ' ' . $image->description()}}"
                         src="{{croppaUrl($image->uploadPath(), 280, 180)}}"
                         alt="{{$image->title()}}">
                </a>
            </div>
        @endforeach
    </div>
@endif