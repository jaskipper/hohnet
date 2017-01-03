<div class="container">
  <div class="row">
    <!-- Column 1 -->
    <div id="news" class="col-xs-12 col-md-6">
      <h3 class="border-bottom">From the Blog</h3>
        {{ start_short() }}
        [loop type="post" count="3"]
        <div class="container skippost mt-1 mb-1">
          <div class="row blogentry rounded">
            <div class="col-xs-12 col-xl-5 postimg-wrap pr-0 pl-0">
              <div class="postimg" style='background-image: url([field image-url])'></div>
            </div>
            <div class="col-xs-12 col-xl-7 pt-1 blogtext">
              <h4>[link][field title][/link]</h4>
              <p class="mb-0 text-muted"><em><small>[field date] - [field author]</small></em></p>
              <p class="mb-0">[field excerpt]...</p>
              <p class="text-xs-right mb-1"><em>[link]Continue Reading...[/link]</em></p>
            </div>
          </div>
        </div>
        [/loop]
        {{ end_short() }}
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
