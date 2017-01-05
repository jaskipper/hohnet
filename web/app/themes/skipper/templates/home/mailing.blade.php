<div class="container">
  <h3>Don't forget to subscribe to our Mailing List...</h3>
  <div class="mysubscriptionform">
    <form class="subscribe" role="form" action="/app/themes/skipper/src/lib/Mailchimp/subscribe.php" method="post">
      <div class="row">
        <div class="col-sm-4 col-md-5">
          <div class="form-group">
            <label class="sr-only" for="subscribe-name">Enter Name...</label>
            <input type="text" name="fullname" placeholder="Enter your name..." class="subscribe-fullname form-control" id="subscribe-fullname">
          </div>
        </div>
        <div class="col-sm-4 col-md-5">
          <div class="form-group">
            <label class="sr-only" for="subscribe-email">Enter Email...</label>
            <input type="text" name="email" placeholder="Enter your email..." class="subscribe-email form-control">
          </div>
        </div>
        <div class="col-sm-4 col-md-2">
          <button type="submit" class="btn btn-warning">Sign Up Now!</button>
        </div>
      </div>
    </form>
    <p class="success-message bg-success"></p>
    <p class="error-message bg-danger"></p>
  </div>
</div>
