    <section id="bottom" class="footer-wiget-area">
        <div class="container">
            <div class="row">
               <?php dynamic_sidebar('bottom'); ?>
            </div>
        </div>
    </section>
    <?php global $themeum; ?>
    <footer id="footer" class="midnight-blue">
        <div class="container">
            <div class="row">
                <div class="col-sm-4">
                    <h2>Contact Us!</h2>

                    <div class="fpfootertext">
                        <?php if(isset($themeum['copyright_text'])) echo $themeum['copyright_text']; ?>
                    </div>
                    <p>
                    Telephone: <?php echo do_shortcode( '[encrypted_email]615-900-0757[/encrypted_email]'); ?><br>
                    E-Mail: <?php echo do_shortcode( '[encrypted_email]jasonskipper@gmail.com[/encrypted_email]'); ?>
                    </p>
                    <p class="fplastlttext">&copy; <?php echo date("Y") ?> The Hand of Hur, Inc.  |  All Rights Reserved.</p>
                </div>
                <div class="col-sm-4 footer-links">
                    <h2>Links</h2>
                    <p>Below are some websites that are useful or meaningful to us in some way.</p>
                    <ul>
                      <li><a href="https://skipperinnovations.com" target="_blank" data-title="Skipper Innovations" data-content="Skipper Innovations - My Business Website" title="Skipper Innovations - My Business Website">Skipper Innovations</a></li>
                      <li><a href="https://www.ministrygenius.com" target="_blank" data-title="Ministry Genius" data-content="Ministry Genius - A new Business venture that I (Jason) am starting on... More to come soon" title="Ministry Genius - A new Business venture that I (Jason) am starting on... More to come soon">Ministry Genius</a></li>
                      <li><a href="https://www.climbcc.com" target="_blank" data-title="Climb Community Church" data-content="Climb Community Church - Our Dalton Church Plant. Website will be up ASAP" title="Climb Community Church - Our Dalton Church Plant. Website will be up ASAP">Climb Community Church</a></li>
                      <li><a href="http://www.skipperstrings.com" target="_blank" data-title="Skipper Strings" data-content="My Dad, Roger Skipper's Business website." title="My Dad, Roger Skipper's Business website.">Skipper Custom Instruments</a></li>
                      <li><a href="https://www.youtube.com/user/eebeeproductions/videos" target="_blank" data-title="Erynn's YouTube" data-content="Erynn's (my daughter) Youtube Channel" title="Erynn's (my daughter) Youtube Channel">Erynn & Bella's YouTube Channel</a></li>
                      <li><a href="https://www.youtube.com/channel/UCubhwe8_rhKrDu_PjSHBXAQ" target="_blank" data-title="Jordan's Youtube" data-content="Jordan's (my son) Youtube Channel" title="Jordan's (my son) Youtube Channel">Jordan's YouTube Channel</a></li>
                    </ul>

                </div>
                <div class="col-sm-4">
                    <div class="pull-right footer-sm">
                        <h2>Find Us on Social Media!</h2>
                        <ul>
                            <li><a href="http://facebook.com/jasonaskipper" data-title="Jason's Facebook" data-content="Follow Jason & keep up to date on the latest news!" target="blank" title="Jason's Facebook"><i class="fa fa-facebook jason"></i></a></li>
                            <li><a href="http://facebook.com/norahskipper" data-title="Norah's Facebook" data-content="Follow Norah & keep up to date on the latest news!"  target="blank" title="Norah's Facebook"><i class="fa fa-facebook norah"></i></a></li>
                            <li><a href="http://twitter.com/jasonaskipper" data-title="Jason's Twitter" data-content="Follow Jason & keep up to date on the latest news!" target="blank" title="Jason's Twitter"><i class="fa fa-twitter jason"></i></a></li>
                            <li><a href="https://www.linkedin.com/in/jasonaskipper" data-title="Jason's LinkedIn Profile" data-content="Follow Jason & see all that he is doing in his ministry & professional world!" target="blank" title="Jason's LinkedIn"><i class="fa fa-linkedin"></i></a></li>
                            <li><a href="http://instagram.com/jasonaskipper" data-title="Jason's Instagram" data-content="Follow Jason & see what he's doing on Instagram!" target="blank" title="Instagram"><i class="fa fa-instagram"></i></a></li>

                        </ul>
                        <div class="skipperinnovations">
                            <a target="_blank" data-content="This is Jason's personal Business." href="http://www.skipperinnovations.com/" title="Skipper Innovations"><img class="wp-post-image" style="width: 100%; max-width: 480px;" src="/app/uploads/2015/02/skipinlogored1-300x59.png" alt="client5"></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <a id="gototop" class="gototop" href="#"><i class="icon-chevron-up"></i></a><!--#gototop-->
    </footer><!--/#footer-->
</div>
<?php if(isset($themeum['before_body']))  echo $themeum['before_body']; ?>
  <?php if(isset($smof_data['custom_css'])): ?>
    <?php if(!empty($smof_data['custom_css'])): ?>
      <style>
        <?php echo $smof_data['custom_css']; ?>
      </style>
    <?php endif; ?>
  <?php endif; ?>
<?php wp_footer(); ?>
<!-- Piwik -->
<script type="text/javascript">
  var _paq = _paq || [];
  _paq.push(["setDocumentTitle", document.domain + "/" + document.title]);
  _paq.push(["setCookieDomain", "*.hohnet.com"]);
  _paq.push(['trackPageView']);
  _paq.push(['enableLinkTracking']);
  (function() {
    var u="//skipperinnovations.com/piwik/";
    _paq.push(['setTrackerUrl', u+'piwik.php']);
    _paq.push(['setSiteId', 2]);
    var d=document, g=d.createElement('script'), s=d.getElementsByTagName('script')[0];
    g.type='text/javascript'; g.async=true; g.defer=true; g.src=u+'piwik.js'; s.parentNode.insertBefore(g,s);
  })();
</script>
<noscript><p><img src="//skipperinnovations.com/piwik/piwik.php?idsite=2" style="border:0;" alt="" /></p></noscript>
<!-- End Piwik Code -->
</body>
</html>
