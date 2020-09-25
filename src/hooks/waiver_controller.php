<?php

require_once  __DIR__ . '/config.php';

function getWaiverId($sw, $templateId, $first_name, $last_name, $email) {
    logActivity("&getWaiverId($templateId, $first_name, $last_name, $email", 0);
    global $SMART_WAIVER_API_KEY;

    # API Key + Template ID ingested from config.php
    $sw = new Smartwaiver($SMART_WAIVER_API_KEY);
    $waiver_id = getWaiverId($sw, $templateId, $first_name, $last_name, $email);

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
