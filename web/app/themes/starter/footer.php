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
                    <p class="fplastlttext"><a href="https://hohnet.com">The Hand of Hur, Inc.</a>    |     All Rights Reserved.</p>
                </div>
                <div class="col-sm-4">
                    <h2>Pages</h2>
                    <?php if(has_nav_menu('secondary')): ?>
                        <?php wp_nav_menu( array( 'theme_location' => 'secondary', 'container'  => false, 'menu_class' => 'footer-menu','depth' => 1 ) ); ?>
                    <?php endif; ?>

                </div>
                <div class="col-sm-4">
                    <div class="pull-right footer-sm">
                        <h2>Find Us on Social Media!</h2>
                        <ul>
                            <li><a href="http://facebook.com/jasonaskipper" data-title="Jason's Facebook" data-content="Follow Jason & keep up to date on the latest news!" target="blank" title="Jason's Facebook"><i class="fa fa-facebook jason"></i></a></li>
                            <li><a href="http://facebook.com/norahskipper" data-title="Norah's Facebook" data-content="Follow Norah & keep up to date on the latest news!"  target="blank" title="Norah's Facebook"><i class="fa fa-facebook norah"></i></a></li>
                            <li><a href="http://twitter.com/jasonaskipper" data-title="Jason's Twitter" data-content="Follow Jason & keep up to date on the latest news!" target="blank" title="Jason's Twitter"><i class="fa fa-twitter jason"></i></a></li>
                            <li><a href="http://twitter.com/norahskipper" data-title="Norah's Twitter" data-content="Follow Norah & keep up to date on the latest news!" target="blank" title="Norah's Twitter"><i class="fa fa-twitter norah"></i></a></li>
                            <li><a href="https://www.linkedin.com/in/jasonaskipper" data-title="Jason's LinkedIn Profile" data-content="Follow Jason & see all that he is doing in his ministry & professional world!" target="blank" title="Jason's LinkedIn"><i class="fa fa-linkedin"></i></a></li>
                            <li><a href="http://pinterest.com/norahskipper" data-title="Norah's Pinterest" data-content="Follow Norah & see what she is doing on Pinterest!" target="blank" title="Norah's Pinterest"><i class="fa fa-pinterest"></i></a></li>
                            <li><a href="http://instagram.com/jasonaskipper" data-title="Jason's Instagram" data-content="Follow Jason & see what he's doing on Instagram!" target="blank" title="Instagram"><i class="fa fa-instagram"></i></a></li>
                            <li><a href="http://youtube.com/jasonaskipper" data-title="Jason's Youtube" data-content="Follow Jason & see his latest personal and ministry videos on Youtube!" target="blank" title="Jason's Youtube"><i class="fa fa-youtube"></i></a></li>
                            <li><a href="https://www.youtube.com/user/eebeeproductions/feed" data-title="Erynn & Bella's Youtube" data-content="This is Erynn & Bella's (Alea) Youtube channel. Subscribe to their channel and see the incredible talent that God has given them!" target="blank" title="Erynn's Youtube"><i class="fa fa-youtube norah"></i></a></li>
                            <li><a href="https://www.youtube.com/channel/UCubhwe8_rhKrDu_PjSHBXAQ" data-title="Jordan's Youtube" data-content="This is Jordan's Youtube channel. Subscribe and see his little videos with gameplay and more. ;-)" target="blank" title="Jordan's Youtube"><i class="fa fa-youtube jordan"></i></a></li>

                        </ul>
                        <div class="skipperinnovations">
                            <a target="_blank" data-content="This is Jason's personal Web-Design | Audio/Visual Solutions Business. If you are in need of website design or Audio/Visual/Lighting consulting, setup and training, contact us and we'll see what we can do for you!<br>Click on the image to visit our website." href="http://www.skipperinnovations.com/" title="Skipper Innovations"><img class="wp-post-image" style="width: 100%; max-width: 480px;" src="/app/uploads/2015/02/skipinlogored1.png" alt="client5"></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <a id="gototop" class="gototop" href="#"><i class="icon-chevron-up"></i></a><!--#gototop-->
    </footer><!--/#footer-->
</div>
<?php if(isset($themeum['before_body']))  echo $themeum['before_body']; ?>
<?php if(isset($smof_data['google_analytics'])) echo $smof_data['google_analytics'];?>

    <?php if(isset($smof_data['custom_css'])): ?>
        <?php if(!empty($smof_data['custom_css'])): ?>
            <style>
                <?php echo $smof_data['custom_css']; ?>
            </style>
        <?php endif; ?>
    <?php endif; ?>
<?php wp_footer(); ?>
<script>window.twttr = (function(d, s, id) {
  var js, fjs = d.getElementsByTagName(s)[0],
    t = window.twttr || {};
  if (d.getElementById(id)) return;
  js = d.createElement(s);
  js.id = id;
  js.src = "https://platform.twitter.com/widgets.js";
  fjs.parentNode.insertBefore(js, fjs);

  t._e = [];
  t.ready = function(f) {
    t._e.push(f);
  };

  return t;
}
(document, "script", "twitter-wjs"));</script>

</body>
</html>
