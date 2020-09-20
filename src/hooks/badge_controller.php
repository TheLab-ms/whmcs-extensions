<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once  __DIR__ . '/config.php';

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleRetry\GuzzleRetryMiddleware;

$stack = HandlerStack::create();
$stack->push(GuzzleRetryMiddleware::factory());

$client = new \GuzzleHttp\Client([
    'timeout'  => 10.0,
    'handler' => $stack,
    'max_retry_attempts' => 5,
]);

function add_badge($name, $badge_id) {
    #logActivity("&add_badge($name, $badge_id)");
    echo("&add_badge($name, $badge_id)" . PHP_EOL);
    global $BADGE_ACS_ADD_USER_ENDPOINT, $client;
    $response = $client->request('POST', $BADGE_ACS_ADD_USER_ENDPOINT, [
        'form_params' => [
            'AD21' => $badge_id,
            'AD22' => $name,
        ]
    ]);
    $status_code = $response->getStatusCode();
    print("status_code=$status_code\n");
}

function delete_badge($badge_id) {
    echo("&delete_badge($badge_id)" . PHP_EOL);
    $acs_id = get_acs_id_from_badge_id($badge_id);
    if (!$acs_id) {
        echo("Unable to find singular ACS ID.  Aborting");
    }
    submit_delete_op($acs_id);
    confirm_delete_op($acs_id);
}

function get_acs_id_from_badge_id($badge_id) {
    global $BADGE_ACS_SEARCH_ENDPOINT, $client;
    $response = $client->request('POST', $BADGE_ACS_SEARCH_ENDPOINT, [
        'form_params' => [
            'US21' => $badge_id,
            '22' => 0,
            '23' => $badge_id,
            '24' => 'Search'
        ]
    ]);
    $status_code = $response->getStatusCode();
    print("status_code=$status_code\n");
    $body = (string) $response->getBody();
    echo $body . PHP_EOL;
    $result_count = substr_count($body, 'Delete'); // Response doesn't show Count if ACS reboots. Count the # of deletes
    echo "Count = $result_count\n";
    if ($result_count != 1) {
        echo "TODO: Notify admin.  Found $result_count results.  Not going to delete badges\n";
        return;
    }

    $regex_results = array();
    preg_match('/<input type=submit name=D(\d+) value=\'Delete\'>/', $body, $regex_results);
    $acs_id = $regex_results[1];

    echo "Going to delete access ID $acs_id\n";

    echo "TODO: Found exactly one result.  Clear to delete $badge_id\n";
    return $acs_id;
}

function submit_delete_op($acs_id) {
    echo("&submit_delete_op($acs_id)" . PHP_EOL);
    global $client, $BADGE_ACS_DELETE_ENDPOINT;
    $response = $client->request('POST', $BADGE_ACS_DELETE_ENDPOINT, [
        'form_params' => [
            'D' . $acs_id => 'Delete'
        ]
    ]);
    $status_code = $response->getStatusCode();
    print("status_code=$status_code\n");
}

function confirm_delete_op($acs_id) {
    echo("&confirm_delete_op($acs_id)" . PHP_EOL);
    global $client, $BADGE_ACS_DELETE_ENDPOINT;
    $response = $client->request('POST', $BADGE_ACS_DELETE_ENDPOINT, [
        'form_params' => [
            'X' . $acs_id => 'Delete'
        ]
    ]);
    $status_code = $response->getStatusCode();
    print("status_code=$status_code\n");
}



#add_badge("DELETEME", 1111111);
delete_badge(1111111);
