<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once  __DIR__ . '/config.php';

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleRetry\GuzzleRetryMiddleware;

$stack = HandlerStack::create();
$stack->push(GuzzleRetryMiddleware::factory());

// TODO:  Do I need to force a login after reboot?
$jar = new \GuzzleHttp\Cookie\CookieJar;

$client = new \GuzzleHttp\Client([
    'timeout'  => 10.0,
    'handler' => $stack,
    'max_retry_attempts' => 5,
    'retry_on_timeout' => TRUE,
#    'cookies' => $jar,
]);


# Apparently, the ACS doesn't care if you login.  That's scary.
function login() {
    #echo("&login()" . PHP_EOL);
    global $client, $BADGE_ACS_USERNAME, $BADGE_ACS_PASSWORD, $BADGE_ACS_LOGIN_ENDPOINT;
    $response = $client->request('POST', $BADGE_ACS_LOGIN_ENDPOINT, [
        'form_params' => [
            'username' => $BADGE_ACS_USERNAME,
            'pwd' => $BADGE_ACS_PASSWORD,
            'logId' => '20101222',
        ]
    ]);
    $status_code = $response->getStatusCode();
    #print("status_code=$status_code\n");
    $body = (string) $response->getBody();
    #print("$body\n");
}

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

    # Validate Badge creation worked
    $acs_id = get_acs_id_from_badge_id($badge_id);
    if ($acs_id) {
        print("Add Badge ($badge_id) Success! Created ACS_ID: $acs_id\n");
    } else {
        print("TODO:  Fix error.  Badge not added.");
        $status_code = $response->getStatusCode();
        print("status_code=$status_code\n");
        $body = (string) $response->getBody();
        print("body=$body\n");
    }
}

function delete_badge($badge_id) {
    echo("&delete_badge($badge_id)" . PHP_EOL);
    $acs_id = get_acs_id_from_badge_id($badge_id);
    if (!$acs_id) {
        echo("Unable to find singular ACS ID.  Aborting\n");
        return;
    }
    echo "Going to delete $acs_id\n";
    submit_delete_op($acs_id);
    confirm_delete_op($acs_id);
}

# We can't delete via the badge ID.  We have to find the ID in the ACS and delete from that
function get_acs_id_from_badge_id($badge_id) {
    echo("&get_acs_id_from_badge_id($badge_id)" . PHP_EOL);
    global $client, $BADGE_ACS_SEARCH_ENDPOINT, $BADGE_ACS_CONFIG_ENDPOINT;
    $result = 0;

    # Can't just Search for user.  Crappy software needs to prime search first
    $response = $client->request('POST', $BADGE_ACS_CONFIG_ENDPOINT, [
        'form_params' => [
            's2' => 'Users',
        ]
    ]);
    $status_code = $response->getStatusCode();
    #print("status_code=$status_code\n");
    $body = (string) $response->getBody();
    #echo $body . PHP_EOL;

    $response = $client->request('POST', $BADGE_ACS_SEARCH_ENDPOINT, [
        'form_params' => [
            'US21' => $badge_id,
            '22' => 0,
            '23' => '',
            '24' => 'Search'
        ]
    ]);
    $status_code = $response->getStatusCode();
    #print("status_code=$status_code\n");
    $body = (string) $response->getBody();
    #echo $body . PHP_EOL;
    $result_count = substr_count($body, 'Delete'); // Response doesn't show Count if ACS reboots. Count the # of deletes
    #echo "Count = $result_count\n";
    if ($result_count == 1) {
        $regex_results = array();
        preg_match('/<input type=submit name=D(\d+) value=\'Delete\'>/', $body, $regex_results);
        $result = $regex_results[1];
        echo "Found ACS_ID $result for Badge ID $badge_id\n";
    }

    return $result;
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
    #print("status_code=$status_code\n");
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
    #print("status_code=$status_code\n");
}

function validate_login() {
    global $client, $BADGE_ACS_CONFIG_ENDPOINT, $BADGE_ACS_DEVICE_NUMBER;
    $response = $client->request('POST', $BADGE_ACS_CONFIG_ENDPOINT, [
        'form_params' => [
            's5' => 'Configure'
        ]
    ]);
    $status_code = $response->getStatusCode();
    $body = (string) $response->getBody();
    #print("status_code=$status_code\n");
    $result_count = substr_count($body, $BADGE_ACS_DEVICE_NUMBER);
    #echo "Count = $result_count\n";
    if ($result_count != 1) {
        echo "LOGIN FAILED!\n";
        echo $body . PHP_EOL;
        return;
    }

    #echo "LOGIN SUCCESS!\n";
}

#login();
#validate_login();
#get_acs_id_from_badge_id(1111111);
add_badge("DELETEME", 1111111);
#delete_badge(1111111);
