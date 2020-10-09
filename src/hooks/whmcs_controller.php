<?php

if (!function_exists('logActivity')) {
    function logActivity($message) {
        print($message . PHP_EOL);
    }
}

function open_ticket($subject, $message) {
    logActivity("&open_ticket($message)");
    $command = 'OpenTicket';
    $values = array(
        'deptid' => '1', # General 
        'subject' => $subject,
        'message' => $message,
        'priority' => 'Medium',
        'responsetype' => 'json',
    );

    $adminuser = 'tommy';

    // Call the localAPI function
    $results = localAPI($command, $values, $adminuser);
    if ($results['result'] == 'success') {
        logActivity('Ticket created successsfully');
    } else {
        logActivity("An Error Occurred: " . $results['result']);
    }
}

#open_ticket('test', 'foobar');
