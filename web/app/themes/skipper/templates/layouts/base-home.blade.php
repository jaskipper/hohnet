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

      <section id="landing" class="p_group skipper-text-shadow">
        <div class="bgimage p_layer p_layer-1"></div>
        <div class="p_overlay p_layer p_layer-2 "></div>
        <div class="container flex-middle fullvh">
          <div class="row p_layer-4 pb-1">
            <div class="col-sm-12">
              <h1 class="">The Hand of Hur, Inc.</h1>
            </div>
          </div>
          <div class="row p_layer-3 pt-1">
            <div class="col-sm-12">
              <p class="subtitle">Loving the World and Connecting it to God's Infinite Grace & Power</p>
            </div>
          </div>
        </div>
      </section>

      <section id="links" class="skipper-box-shadow p_group">
          @include('home.links')
      </section>

      <section id="about" class="p_group">
        @include('home.aboutskippers')
      </section>

      <section id="whatsup" class="skipper-box-shadow" class="p_group">
        @include('home.whatsup')
      </section>

      <section id="churches" class="p_group">
        <div class="p_layer p_layer-back"></div>
        @include('home.churches')
      </section>

      <section id="mailing" class="rellax skipper-box-shadow" class="p_group">
        @include('home.mailing')
      </section>

    @php(do_action('get_footer'))
    @include('partials.footer')
    </div> <!-- Parallax -->
    @php(wp_footer())
  </body>
</html>
