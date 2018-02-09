@if(!empty($images) && $images->isNotEmpty())
    <div class="row image-gallery">
        @foreach($images as $image)
            <div class="col-xs-6 col-md-3 image mb-4">
                <a href="{{croppaUrl($image->uploadPath())}}" class="colorbox thumbnail"
                   rel="gallery" title="{{($image->title($language->code))? $image->title($language->code) : ''}}">
                    <img class="img-fluid"
                         title="{{($image->title($language->code))? $image->title($language->code) : ''}}"
                         src="{{croppaUrl($image->uploadPath(), 180, 180)}}"
                         alt="{{($image->title($language->code))? $image->title($language->code) : ''}}">
                </a>
            </div>
        @endforeach
    </div>
@endif