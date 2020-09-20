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
    $sw = new Smartwaiver($SMART_WAIVER_API_KEY);
    $waiver_id = getWaiverId($sw, $SMART_WATIVER_TEMPLATE_ID, $first_name, $last_name, $email);

    if ($waiver_id) {
        logActivity('TODO: Waiver found.  Save to custom field and activate new badge: ' . $new_badge_id);
    } else {
        logActivity("TODO: No waiver Found.  Email Waiver to $email");
    }
});

function getWaiverId($sw, $templateId, $first_name, $last_name, $email) {
    logActivity("&getWaiverId($templateId, $first_name, $last_name, $email", 0);
    $searchResults = $sw->search($templateId, '', '', $first_name, $last_name);

    if ($searchResults->count == 0 ) {
        logActivity("No Signed Waiver for $first_name $last_name found");
        return;
    }
    logActivity("Found {$searchResults->count} results");
    for ($i = 0; $i < $searchResults->count; $i++) {
        $waivers = $sw->searchResult($searchResults, $i);
        foreach ($waivers as $waiver) {
            logActivity("Processing Waiver $i:" . json_encode($waiver));
            $waiver_id = $waiver->waiverId;

            // If the request worked and we have something in the email field and the email matches
            if (strtolower($waiver->email) == strtolower($email)) {
                logActivity("Found a waiver: first_name='{$first_name}' last_name='{$last_name}' email='{$email}' waiver_email='{$waiver->email}' waiver_id={$waiver_id}");
                return $waiver_id;
            } else {
                logActivity("Found waiver for same name ($first_name, $last_name), but not email ($email != {$waiver->email})\n");
            }
        }
    }
}
