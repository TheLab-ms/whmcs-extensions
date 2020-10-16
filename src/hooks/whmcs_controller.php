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
        return $results['id'];
    } else {
        logActivity("An Error Occurred opening a ticket");
        logActivity(json_encode($results));
    }
}

function add_ticket_note($ticket_id, $message) {
    logActivity("&add_ticket_note($ticket_id, $message)");
    $command = 'AddTicketNote';
    $values = array(
        'ticketid' => $ticket_id,
        'message' => $message,
    );
    $adminuser = 'cto';

    // Call the localAPI function
    $results = localAPI($command, $values, $adminuser);
    if ($results['result'] == 'success') {
        logActivity('Ticket Note created successsfully');
    } else {
        logActivity("An Error Occurred adding a ticket note");
        logActivity(json_encode($results));
    }
}

#open_ticket('test', 'foobar');
