<?php

if (!function_exists('logActivity')) {
    function logActivity($message) {
        print($message . PHP_EOL);
    }
}

function open_ticket($subject, $message, $client_id) {
    logActivity("&open_ticket($subject, $client_id)");
    $command = 'OpenTicket';
    $values = array(
        'deptid' => '1', # General 
        'subject' => $subject,
        'message' => $message,
        'priority' => 'Medium',
        'clientid' => $client_id,
    );

    $adminuser = 'cto';

    // Call the localAPI function
    $results = localAPI($command, $values, $adminuser);
    if ($results['result'] == 'success') {
        logActivity('Ticket created successsfully');
    } else {
        logActivity("An Error Occurred opening a ticket");
        logActivity(json_encode($results));
    }
}

#open_ticket('test', 'foobar');
