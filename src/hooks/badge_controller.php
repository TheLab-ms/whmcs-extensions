<?php

require_once __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/config.php';

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleRetry\GuzzleRetryMiddleware;

if (!function_exists('logActivity')) {
    function logActivity($message) {
        print($message . PHP_EOL);
    }
}

class BadgeController
{
    public $guzzle_client;
    private $has_rebooted = FALSE;
    private $handler_stack;

    public function __construct() {
        $this->handler_stack = HandlerStack::create();
        $this->handler_stack->push(GuzzleRetryMiddleware::factory());

        $jar = new \GuzzleHttp\Cookie\CookieJar;

        $this->guzzle_client = new \GuzzleHttp\Client([
            'timeout'  => 10.0,
#            'handler' => $stack,
            'max_retry_attempts' => 5,
            'retry_on_timeout' => TRUE,
        #    'cookies' => $jar,
        ]);
    }

    # Apparently, the ACS doesn't care if you login.  That's scary.
    function login() {
        logActivity("&login()");
        # Weird.  Must add require here instead of at the top.  I think it's because of the hook.  require_once doesn't work either
        require __DIR__ . '/config.php';
        $response = $this->guzzle_client->request('POST', $BADGE_ACS_LOGIN_ENDPOINT, [
            'handler' => $this->handler_stack,
            'form_params' => [
                'username' => $BADGE_ACS_USERNAME,
                'pwd' => $BADGE_ACS_PASSWORD,
                'logId' => '20101222',
            ]
        ]);
        $status_code = $response->getStatusCode();
        #print("status_code=$status_code\n");
        $body = (string) $response->getBody();
        $regex_results = array();
        preg_match('/Web Controller/', $body, $regex_results);
        if ($regex_results) {
            return TRUE;
        } else {
            return FALSE;
        }
        #print("$body\n");
    }

    # NOTE: If you add a badge that already exists, it returns 200 and nothing changes in the system
    function add_badge($badge_id, $name) {
        #logActivity("&add_badge($name, $badge_id)");
        logActivity("&add_badge($badge_id, $name)");

        # Controller can reboot at any point.  Better to force the reboot before making a change
        if (!$this->has_rebooted) {
            $this->reboot();
        }

        # Weird.  Must add require here instead of at the top.  I think it's because of the hook.  require_once doesn't work either
        require __DIR__ . '/config.php';
        $response = $this->guzzle_client->request('POST', $BADGE_ACS_ADD_USER_ENDPOINT, [
            'form_params' => [
                'AD21' => $badge_id,
                'AD22' => $name,
            ]
        ]);

        # Validate Badge creation worked
        $acs_id = $this->get_acs_id_from_badge_id($badge_id);
        if ($acs_id) {
            logActivity("Add Badge ($badge_id) Success! Created ACS_ID: $acs_id");
        } else {
            logActivity("TODO:  Notify admin.  Unable to find added badge");
            $status_code = $response->getStatusCode();
            logActivity("status_code=$status_code");
            $body = (string) $response->getBody();
            logActivity($body);
        }
    }

    function delete_badge($badge_id) {
        logActivity("&delete_badge($badge_id)");

        # Controller can reboot at any point.  Better to force the reboot before making a change
        if (!$this->has_rebooted) {
            $this->reboot();
        }

        $acs_id = $this->get_acs_id_from_badge_id($badge_id);
        if (!$acs_id) {
            echo("Unable to find singular ACS ID.  Aborting\n");
            return;
        }
        logActivity("Going to delete $acs_id");
        # Can't just delete badge. Must submit and then delete.  Stoopid ACS system.
        $this->submit_delete_op($acs_id);
        $this->confirm_delete_op($acs_id);
    }

    # We can't delete via the badge ID.  We have to find the ID in the ACS and delete from that
    function get_acs_id_from_badge_id($badge_id) {
        logActivity("&get_acs_id_from_badge_id($badge_id)");
        # Weird.  Must add require here instead of at the top.  I think it's because of the hook.  require_once doesn't work either
        require __DIR__ . '/config.php';

        $result = 0;

        # Can't just Search for user.  Crappy software needs to prime search first
        $response = $this->guzzle_client->request('POST', $BADGE_ACS_HEADER_ENDPOINT, [
            'form_params' => [
                's2' => 'Users',
            ]
        ]);
        $status_code = $response->getStatusCode();
        #print("status_code=$status_code\n");
        $body = (string) $response->getBody();
        #logActivity($body);
        #echo $body . PHP_EOL;

        logActivity("BADGE_ACS_SEARCH_ENDPOINT=$BADGE_ACS_SEARCH_ENDPOINT");
        $response = $this->guzzle_client->request('POST', $BADGE_ACS_SEARCH_ENDPOINT, [
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
            logActivity("Found ACS_ID $result for Badge ID $badge_id");
        } else {
            logActivity("Unable to find ACS_ID for Badge ID $badge_id");
        }

        return $result;
    }

    function submit_delete_op($acs_id) {
        logActivity("&submit_delete_op($acs_id)");
        # Weird.  Must add require here instead of at the top.  I think it's because of the hook.  require_once doesn't work either
        require __DIR__ . '/config.php';
        $response = $this->guzzle_client->request('POST', $BADGE_ACS_DELETE_ENDPOINT, [
            'form_params' => [
                'D' . $acs_id => 'Delete'
            ]
        ]);
    }

    function reboot() {
        logActivity("&reboot()");
        require __DIR__ . '/config.php';

        # Gotta prime the reboot
        try {
            $response = $this->guzzle_client->request('POST', $BADGE_ACS_CONFIG_ENDPOINT, [
                'form_params' => [
                    'E16' => 'Reboot'
                ]
            ]);
        } catch (Exception $e) {
            logActivity("&reboot()::Prep Reboot operation timed out.");
        }
        $status_code = $response->getStatusCode();

        # Do the reboot!
        try {
            $response = $this->guzzle_client->request('POST', $BADGE_ACS_CONFIG_ENDPOINT, [
                'form_params' => [
                    'Reboot' => 'Reboot'
                ]
            ]);
        } catch (Exception $e) {
            logActivity("&reboot()::Reboot operation timed out.  Expected behavior.");
        }
        #$this->validate_reboot();

        $i = 0;
        while (!$this->login()) {
            $i++;
            $this->login();
            if ($i >= 5) {
                logActivity("Tried to login too many times");
                break;
            }
        }
        #$this->validate_login();
    }

    function validate_reboot() {
        logActivity("&validate_reboot()");
        require __DIR__ . '/config.php';
        print($this->handler_stack);
        $this->guzzle_client->request('GET', $BADGE_ACS_CONFIG_ENDPOINT, [
            'handler' => $this->handler_stack,
        ]);

    }

    function confirm_delete_op($acs_id) {
        logActivity("&confirm_delete_op($acs_id)" . PHP_EOL);
        # Weird.  Must add require here instead of at the top.  I think it's because of the hook.  require_once doesn't work either
        require __DIR__ . '/config.php';
        $response = $this->guzzle_client->request('POST', $BADGE_ACS_DELETE_ENDPOINT, [
            'form_params' => [
                'X' . $acs_id => 'Delete'
            ]
        ]);
        $status_code = $response->getStatusCode();
        #print("status_code=$status_code\n");
    }

    function validate_login() {
        logActivity("&validate_login()");
        # Weird.  Must add require here instead of at the top.  I think it's because of the hook.  require_once doesn't work either
        require __DIR__ . '/config.php';
        $response = $this->guzzle_client->request('POST', $BADGE_ACS_LOGIN_ENDPOINT, [
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
            logActivity("LOGIN FAILED!");
            logActivity($body);
            return;
        }
    }
}

#$badge_controller = new BadgeController();
#$badge_controller->add_badge(1111111, "DELETE ME 2");
#$badge_controller->delete_badge(1111111);
