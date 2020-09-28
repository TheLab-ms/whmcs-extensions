<?php

/*
require_once  __DIR__ . '/waiver_controller.php';

use Smartwaiver\Smartwaiver;
use WHMCS\Database\Capsule;

# https://developers.whmcs.com/hooks-reference/client/
add_hook('ClientEdit', 1, function($vars) {
    echo('CLIENT EDIT');
    require __DIR__ . '/config.php';
    logActivity("SMART_WAIVER_API_KEY=$SMART_WAIVER_API_KEY");
    logActivity('Client Add: ' . json_encode($vars), 0);
    $client_id = $vars['userid'];
    $first_name = $vars['firstname'];
    $last_name = $vars['lastname'];
    $email = $vars['email'];

    $sw = new WaiverController($SMART_WAIVER_API_KEY);
    $waiver = $sw->findWaiver($SMART_WATIVER_TEMPLATE_ID, $first_name, $last_name, $email);
    $waiver_id = $waiver->waiverId;

    if (!$waiver_id) {
        logActivity("TODO: No waiver Found.  Email Waiver to $email");
    } elseif ($waiver->verified == false) {
        logActivity('Waiver found, but not verified.  Ignoring');
    } else {
        $values = [ 
            'fieldid' => 3, # Hardcoded, I know.
            'relid' => $client_id,
            'value' => $waiver_id];
        logActivity("Waiver Found.  Updating Waiver Custom Field" . json_encode($values));
        $result = Capsule::table('tblcustomfieldsvalues')->insert($values);
        logActivity($result);
        $value = Capsule::table('tblcustomfieldsvalues')->where('fieldid', 3)->where('relid', $client_id)->value('value');
        logActivity($value);

    }
});
*/