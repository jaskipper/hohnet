<?php
/*
Plugin Name:  Skipper Exit Modal
Plugin URI:   https://skipperinnovations.com
Description:  An exit modal using Bootstrap 4
Version:      1.0.0
Author:       Jason Skipper
Author URI:   https://skipperinnovations.com
License:      MIT License
*/

// register jquery and style on initialization
add_action('init', 'register_exitmodal');
function register_exitmodal() {
    wp_register_script( 'exitmodaljs', plugins_url('exitmodal.js', __FILE__), array('jquery') );

    wp_register_style( 'exitmodalcss', plugins_url('exitmodal.css', __FILE__), false, '1.0.0', 'all');
}

// use the registered jquery and style above
add_action('wp_enqueue_scripts', 'enqueue_exitmodal');

function enqueue_exitmodal(){
   wp_enqueue_script('exitmodaljs');

   wp_enqueue_style( 'exitmodalcss' );
}

function add_modaldiv() {
ob_start(); ?>

    <div id="skipper-exitmodal" class="modal fade">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-body">
            <div class="container-fluid">
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
              <div class="row pt-1">
                <div class="col-xs-12 col-sm-2">
                  <i class="fa fa-hand-paper-o fa-4x" aria-hidden="true"></i>
                </div>
                <div class="col-xs-12 col-sm-10">
                  <h2>Wait!</h2>
                  <p>Have you signed up for our Mailing List? Be the first to know what's happening with the Skippers and their ministry around the world!</p>
                  <form class="subscribe" role="form" action="/app/themes/skipper/src/lib/Mailchimp/subscribe.php" method="post">
                      <div class="form-group">
                          <label class="sr-only" for="subscribe-name">Enter Name...</label>
                          <input type="text" name="fullname" placeholder="Enter your name..." class="subscribe-fullname form-control" id="subscribe-fullname">
                      </div>
                      <div class="form-group">
                          <label class="sr-only" for="subscribe-email">Enter Email...</label>
                          <input type="text" name="email" placeholder="Enter your email..." class="subscribe-email form-control">
                      </div>
                      <div class="text-xs-right flex">
                          <button id="setexitcookie" type="button" class="btn btn-secondary bg-faded float-xs-left text-muted">Already Subscribed</button>
                          <button type="submit" class="btn btn-primary" data-dismiss="modal" aria-label="Close">Sign Up Now!</button>
                      </div>
                  </form>
                  <p class="success-message bg-success"></p>
                  <p class="error-message bg-danger"></p>
                </div>
              </div>
            </div>
          </div>
          <!-- <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
          </div> -->
        </div>
      </div>
    </div>

    <?php
    $exitmodal_content = ob_get_clean();

    echo $exitmodal_content;
}
add_action( 'wp_footer', 'add_modaldiv', 100 );

?>
