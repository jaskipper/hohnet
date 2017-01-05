<article @php(post_class('mb-2'))>
  <div class="row">
    <div class="col-xs-12 col-lg-5 col-xl-4 img-wrap">
        @php( the_post_thumbnail( 'medium') )
    </div>
    <div class="col-xs-12 col-lg-7 col-xl-8">
      <header>
        <h2 class="entry-title mb-0"><a href="{{ get_permalink() }}">{{ get_the_title() }}</a></h2>
        @include('partials/entry-meta')
      </header>
      <div class="entry-summary">
        @php(the_excerpt())
      </div>
    </div>
</article>
