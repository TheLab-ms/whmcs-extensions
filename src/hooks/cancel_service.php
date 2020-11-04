<?php

require_once  __DIR__ . '/whmcs_controller.php';

# https://developers.whmcs.com/hooks-reference/client/
# For a quick test, change to "ClientEdit" and you can edit the client instead of creating a new one.  Parameters are the same.
#add_hook('ClientEdit', 1, function($vars) {
add_hook('CancellationRequest', 1, function($vars) {
    require __DIR__ . '/config.php';
    logActivity('Cancellation Request: ' . json_encode($vars), 0);
    $client_id  = $vars['userid'];
    $service_id = $vars['relid'];
    $reason     = $vars['reason'];
    $type       = $vars['type'];

    $subject = "We are sorry to see you leave TheLab.ms.";
    $body = "We will now deactivate your badge.";
    $ticket_id = open_ticket($subject, $body, $client_id);

    $message = "Deactivate badge for $first_name $last_name ($client_id)\n\n"
        . "Copy the Badge #: https://{$_SERVER['HTTP_HOST']}/admin/clientsprofile.php?userid=$client_id\n"
        . "Log into $BADGE_ACS_LOGIN_ENDPOINT\n"
        . "Click Users\n"
        . "Search for Badge # from previous page\n"
        . "Click Delete\n"
        . "Click OK";
    add_ticket_note($ticket_id, $message);
});