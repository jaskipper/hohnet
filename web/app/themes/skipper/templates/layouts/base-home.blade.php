<!doctype html>
<html @php(language_attributes())>
  @include('partials.head')
  <body @php(body_class())>
    <!--[if IE]>
      <div class="alert alert-warning">
        {!! __('You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> to improve your experience.', 'sage') !!}
      </div>
    <![endif]-->
    @php(do_action('get_header'))
    @include('partials.header')

      <section id="landing" class="jarallax fullvh" data-jarallax='{"speed": 0.4}' style='background-image: url(/app/uploads/2017/01/sucrestreet.jpg)'>
        <div class="overlay fullvh flex-middle">
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

      <section id="links" class="skipper-box-shadow bg-white">
          @include('home.links')
      </section>

      <section id="about" style='background-image: url(/app/uploads/2016/12/grungemap2-2-1-1.jpg)'>
        @include('home.aboutskippers')
      </section>

      <section id="whatsup" class="skipper-box-shadow bg-white">
        @include('home.whatsup')
      </section>

      <section id="churches" class="jarallax" data-jarallax='{"speed": 0.2}' style='background-image: url(/app/uploads/2017/01/Walking-Heads.jpg)'>
        @include('home.churches')
      </section>

      <section id="mailing" class="skipper-box-shadow bg-white">
        @include('home.mailing')
      </section>

    @php(do_action('get_footer'))
    @include('partials.footer')
    @php(wp_footer())
  </body>
</html>
