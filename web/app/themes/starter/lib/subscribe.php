<?php

require_once 'MailChimp.php';
use \DrewM\MailChimp\MailChimp;

// Email address verification
function isEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

    $fullname = $_POST['fullname'];

    $parts = explode(" ", $fullname);
    $firstname = array_shift($parts);
    $lastname = implode(" ", $parts);

    $merge_vars = array('FNAME'=>$firstname, 'LNAME'=>$lastname);

if($_POST) {

    $mailchimp_api_key = $MAILCHIMP_API_KEY; // enter your MailChimp API Key
    // ****
    $mailchimp_list_id = $MAILCHIMP_LIST_ID; // enter your MailChimp List ID
    // ****

    $subscriber_email = addslashes( trim( $_POST['email'] ) );

    if( !isEmail($subscriber_email) ) {
        $array = array();
        $array['valid'] = 0;
        $array['message'] = 'Insert a valid email address!';
        echo json_encode($array);
    }
    else {
        $array = array();

        $MailChimp = new MailChimp($mailchimp_api_key);

        $result = $MailChimp->post("lists/$mailchimp_list_id/members", [
                'email_address' => $subscriber_email,
                'merge_fields' => $merge_vars,
                'status'        => 'pending',
        ]);

        if($result == false) {
            $array['valid'] = 0;
            $array['message'] = '<i class="fa fa-times" aria-hidden="true"></i> An error occurred! Please try again later.';
        }
        else {
            $array['valid'] = 1;
            $array['message'] = '<i class="fa fa-check" aria-hidden="true"></i> Thanks for your subscription! We sent you a confirmation email. Please confirm your email address to complete your subscription.';
        }

            echo json_encode($array);

    }

}

?>
