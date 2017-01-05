<div class="container">
  <div class="row">
    <!-- Column 1 -->
    <div id="news" class="col-xs-12 col-md-6">
      <h3 class="border-bottom">From the Blog</h3>
      <!-- Get Loop - Need to learn to do this with Blade -->
      <?
        $args = array('post_type' => 'post','posts_per_page'=>'3');
        $loop = new WP_Query( $args );
        // Limit the Front Page excerpts to 30 words
        function custom_excerpt_length( $length ) {
  	        return 30;
        }
        add_filter( 'excerpt_length', 'custom_excerpt_length', 999 );
      ?>
      @while ($loop->have_posts()) @php($loop->the_post())
        @include('partials.content')
      @endwhile
      <!-- End Loop -->
      <div class="border-bottom"></div>
      <p class="mt-1 text-xs-center font-weight-bold lead"><a href="/blog">READ MORE FROM THE BLOG</a></p>
      <div class="spacer hidden-md-up pb-3"></div>
    </div>
    <!-- Column 2 -->
    <div id="skippertwitter" class="col-xs-12 col-md-6">
      <h3>Jason &amp; Norah's Twitter Feeds</h3>
      <a class="twitter-timeline" href="https://twitter.com/jasonaskipper/lists/jnskipper" data-widget-id="568089445166243840" data-chrome="noborder noheader">Tweets from https://twitter.com/jasonaskipper/lists/jnskipper</a>
      <script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+"://platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");
      </script>
    </div>
  <!-- row -->
  </div>
</div>
