<div id="disqus_thread"></div>
@section('footerScripts')
    @parent
    @if(config('gzero-cms.disqus.domain') && config('gzero-cms.disqus.api_key'))
        <script>
            var disqus_config = function() {
                this.page.url = "{{$url}}"; // page's canonical url
                this.page.identifier = {{$contentId}}; // page's unique identifier
                // The generated payload which authenticates users with Disqus
                this.page.remote_auth_s3 = '{{$remoteAuthS3}}';
                this.page.api_key = '{{config('gzero-cms.disqus.api_key')}}';

                // This adds the custom login/logout functionality
                this.sso = {
                    name: "{{ config('app.name') }}",
                    url: "{{ route('login') }}",
                    logout: "{{ route('logout') }}"
                };
            };
            (function() { // DON'T EDIT BELOW THIS LINE
                var d = document, s = d.createElement('script');
                s.src = '//{{config('gzero-cms.disqus.domain')}}.disqus.com/embed.js';
                s.setAttribute('data-timestamp', +new Date());
                (d.head || d.body).appendChild(s);
            })();
        </script>
    @endif
@stop
<noscript>
    <div class="alert alert-info" role="alert">
        @lang('gzero-core::common.comments_no_js_message')
    </div>
    <a href="https://disqus.com/?ref_noscript" rel="nofollow"></a>
</noscript>
