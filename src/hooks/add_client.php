<?php

require_once __DIR__ . '/vendor/autoload.php';

use Smartwaiver\Smartwaiver;
require_once  __DIR__ . '/config.php';

# https://developers.whmcs.com/hooks-reference/client/
add_hook('ClientAdd', 1, function($vars) {
    logActivity('Client Add: ' . json_encode($vars), 0);
    $client_id = $vars['client_id'];
    $first_name = $vars['firstname'];
    $last_name = $vars['lastname'];
    $email = $vars['email'];

    # API Key + Template ID ingested from config.php
    $waiver_id = getWaiverId($sw, $SMART_WATIVER_TEMPLATE_ID, $first_name, $last_name, $email);

    if ($waiver_id) {
        logActivity('TODO: Waiver found.  Save to custom field and activate new badge: ' . $new_badge_id);
    } else {
        logActivity("TODO: No waiver Found.  Email Waiver to $email");
    }
});
