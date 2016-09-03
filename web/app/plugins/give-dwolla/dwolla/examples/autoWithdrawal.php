<?php
// Include the Dwolla REST Client
require '../lib/dwolla.php';

// Include any required keys
require '_keys.php';

// Instantiate a new Dwolla REST Client
$Dwolla = new DwollaRestClient();

// Seed a previously generated access token
$Dwolla->setToken($token);


/***
 * Example 1:
 *
 * Get auto withdrawal status for the account
 * associated with the current OAuth token.
 */

$status = $Dwolla->getAutoWithdrawalStatus();
if($status == NULL) { echo "Error: {$Dwolla->getError()} \n"; } // Check for errors
else { echo $status; } // Print autowithdrawal status

/***
 * Example 2:
 *
 * Enable auto withdrawal for funding ID '12345678' for
 * the account associated with the current OAuth token.
 */

$aw = $Dwolla->toggleAutoWithdrawalStatus(true, '12345678');
if($aw == NULL) { echo "Error: {$Dwolla->getError()} \n"; } // Check for errors
else { echo $aw; } // Print response