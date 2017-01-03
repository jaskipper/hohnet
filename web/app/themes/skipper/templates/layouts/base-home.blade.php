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
    <div class="parallax">
      <!--<div class="parallax__group">
        <div class="parallax__layer parallax__layer--back">
          <div class="title">Back layer</div>
        </div>
        <div class="parallax__layer parallax__layer--base">
          <div class="title">Base Layer</div>
        </div>
      </div>-->

      <section id="landing" class="parallax__group">
        <div class="parallax__layer parallax__layer--back"></div>
        <div class="parallax__layer parallax__layer--base">
          <div class="overlaycontent skipper-text-shadow">
            <h1>The Hand of Hur, Inc.</h1>
            <h2>Loving the World and Connecting it to God's Infinite Grace & Power</h2>
          </div>
        </div>
      </section>

      <section id="links" class="skipper-box-shadow">
          @include('home.links')
      </section>

      <section id="about">
        @include('home.aboutskippers')
      </section>

      <section id="whatsup" class="skipper-box-shadow">
        @include('home.whatsup')
      </section>

      <section id="churches" class="parallax__group">
        <div class="parallax__layer parallax__layer--back"></div>
        @include('home.churches')
      </section>

      <section id="mailing" class="parallax__group skipper-box-shadow">
        <div class="parallax_layer parallax__layer--back"></div>
        @include('home.mailing')
      </section>

    @php(do_action('get_footer'))
    @include('partials.footer')
    </div> <!-- Parallax -->
    @php(wp_footer())
  </body>
</html>
