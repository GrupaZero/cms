<?php /* @var $content \Gzero\Cms\ViewModels\ContentViewModel */ ?>
<script type="application/ld+json">
    {
        "@context": "http://schema.org",
        "@type": "Article",
        "publisher": {
            "@type": "Organization",
            "url": "{{ route('home') }}",
            "name": "{{ config('app.name') }}",
            "logo": {
                "@type": "ImageObject",
                "url": "{{ asset('/images/logo.png') }}"
            }
        },
        "mainEntityOfPage": {
            "@type": "WebPage",
            "@id": "{{ route('home') }}"
        },
        @if(isset($content))
            "headline": "{{ $content->title()}}",
            "author": {
                "@type": "Person",
                "name": "{{ $content->author()->displayName() }}"
            },
            "datePublished": "{{ $content->publishedAt() }}",
            "dateModified": "{{ $content->updatedAt() }}",
            "url": "{{ $content->url() }}",
            @if($content->hasAncestors())
                "articleSection": "{{ implode(',', $content->ancestorsNames()) }}",
            @endif
            "image": {
                "@type": "ImageObject",
                @if($content->hasThumbnail())
                    "url": "{{ asset(croppaUrl($content->thumbnail()->uploadPath())) }}",
                    "width": "{{ isset($width) ? $width : config('gzero-cms.image.thumb.width') }}",
                    "height": "{{ isset($height) ? $height : 'auto' }}"
                @else
                    "url": "{{ $content->firstImageUrl($content->teaser(), asset('images/share-logo.png')) }}",
                    "width": "{{ config('gzero-cms.image.thumb.width') }}",
                    "height": "{{ 'auto' }}"
                @endif
            }
        @endif
    },
    @if(isset($children))
        "@type":"ItemList",
        "itemListElement":[@foreach ($children as $index => $child){
            "@type": "ListItem",
            "position": {{$index+1}},
            "url":"{{$child->url()}}"
        }{{($children->count() !== $index+1) ? ',': ''}}@endforeach]
    @endif
</script>
