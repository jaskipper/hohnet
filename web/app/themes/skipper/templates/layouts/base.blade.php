<!doctype html>
<html @php(language_attributes())>
  @include('partials.head')
  <body @php(body_class())>
    <div id="fb-root"></div>
    <script>(function(d, s, id) {
      var js, fjs = d.getElementsByTagName(s)[0];
      if (d.getElementById(id)) return;
      js = d.createElement(s); js.id = id;
      js.src = "//connect.facebook.net/en_US/sdk.js#xfbml=1&version=v2.8&appId=311824144040";
      fjs.parentNode.insertBefore(js, fjs);
    }(document, 'script', 'facebook-jssdk'));</script>
    <!--[if IE]>
      <div class="alert alert-warning">
        {!! __('You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> to improve your experience.', 'sage') !!}
      </div>
    <![endif]-->
    @php(do_action('get_header'))
    @include('partials.header')
    <section id="landing" class="jarallax" data-jarallax='{"speed": 0.4}' style='background-image: url(/app/uploads/2017/01/sucrestreet.jpg); height: 320px;'>
      <div class="overlay flex-middle" style="height: 320px;">
        <div class="container skipper-text-shadow">
          <div class="row" data-aos="zoom-in">
            <div class="col-sm-12">
              <h1 class="hvr-grow">The Hand of Hur, Inc.</h1>
            </div>
          </div>
          <div class="row">
            <div class="col-sm-12">
              <p class="subtitle" data-aos="zoom-in" data-aos-delay="800">Loving the World and Connecting it to God's Infinite Grace & Power</p>
            </div>
          </div>
        </div>
      </div>
    </section>
    <section class="mainwrap">
      <div class="wrap container" role="document">
        <div class="content row">
          <main class="main">
            @yield('content')
          </main>
          @if (App\display_sidebar())
            <aside class="sidebar">
              @include('partials.sidebar')
            </aside>
          @endif
        </div>
      </div>
    </section>
    @php(do_action('get_footer'))
    @include('partials.footer')
    @php(wp_footer())
  </body>
</html>
