<?php /* @var $content \Gzero\Cms\Presenters\ContentPresenter */ ?>
<script type="application/ld+json">
    {
        "@context": "http://schema.org",
        "@type": "Article",
        "publisher": {
            "@type": "Organization",
            "url": "{{ routeMl('home') }}",
            "name": "{{ config('app.name') }}",
            "logo": {
                "@type": "ImageObject",
                "url": "{{ asset('/images/logo.png') }}"
            }
        },
        "mainEntityOfPage": {
            "@type": "WebPage",
            "@id": "{{ routeMl('home') }}"
        },
        @if(isset($content))
            "headline": "{{ $content->getTitle()}}",
            "author": {
                "@type": "Person",
                "name": "{{ $content->getAuthor()->displayName() }}"
            },
            "datePublished": "{{ $content->published_at }}",
            "dateModified": "{{ $content->updated_at }}",
            "url": "{{ $content->getUrl() }}",
            @if($content->hasAncestors())
                "articleSection": "{{ implode(',', $content->getAncestorsNames()) }}",
            @endif
            "image": {
                "@type": "ImageObject",
                @if($content->hasThumbnail())
                    "url": "{{ asset(croppaUrl($content->thumb->getFullPath())) }}",
                    "width": "{{ isset($width) ? $width : config('gzero-cms.image.thumb.width') }}",
                    "height": "{{ isset($height) ? $height : 'auto' }}"
                @else
                    "url": "{{ $content->getFirstImageUrl($content->getTeaser(), asset('images/share-logo.png')) }}",
                    "width": "{{ config('gzero-cms.image.thumb.width') }}",
                    "height": "{{ 'auto' }}"
                @endif
            }
        @endif
    }
</script>
