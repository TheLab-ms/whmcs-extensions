<?php

/*
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config.php';

use Smartwaiver\Smartwaiver;

class WaiverController
{
    private $sw;

    public function __construct($api_key) {
        $this->sw = new Smartwaiver($api_key);
    }

    function findWaiver($templateId, $first_name, $last_name, $email) {
        logActivity("&getWaiverId($templateId, $first_name, $last_name, $email", 0);

        $searchResults = $this->sw->search($templateId, '', '', $first_name, $last_name);

        if ($searchResults->count == 0 ) {
            logActivity("No Signed Waiver for $first_name $last_name found");
            return;
        }
        logActivity("Found {$searchResults->count} results");
        for ($i = 0; $i < $searchResults->count; $i++) {
            $waivers = $this->sw->searchResult($searchResults, $i);
            foreach ($waivers as $waiver) {
                logActivity("Processing Waiver $i:" . json_encode($waiver));
                $waiver_id = $waiver->waiverId;

                // If the request worked and we have something in the email field and the email matches
                if (strtolower($waiver->email) == strtolower($email)) {
                    logActivity("Found a waiver: first_name='{$first_name}' last_name='{$last_name}' email='{$email}' waiver_email='{$waiver->email}' waiver_id={$waiver_id}");
                    return $waiver;
                } else {
                    logActivity("Found waiver for same name ($first_name, $last_name), but not email ($email != {$waiver->email})\n");
                    return null;
                }
            }
        }
    }
}

if (!function_exists('logActivity')) {
    function logActivity($message) {
        print($message . PHP_EOL);
    }
}

#$sw = new WaiverController($SMART_WAIVER_API_KEY);
#$waiver = $sw->findWaiver($SMART_WATIVER_TEMPLATE_ID, 'Client 1', 'Maker', 'tommy+client+1@lastcoolnameleft.com');
#if ($waiver) { print("Found Wavier ID {$waiver->waiverId}\n"); }
*/