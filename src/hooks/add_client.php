<?php

require_once  __DIR__ . '/whmcs_controller.php';

# https://developers.whmcs.com/hooks-reference/client/
# For a quick test, change to "ClientEdit" and you can edit the client instead of creating a new one.  Parameters are the same.
add_hook('ClientAdd', 1, function($vars) {
    require __DIR__ . '/config.php';
    logActivity('Client Add: ' . json_encode($vars), 0);
    $client_id = $vars['userid'];
    $first_name = $vars['firstname'];
    $last_name = $vars['lastname'];
    $email = $vars['email'];

    $subject = "Validate waiver for $first_name $last_name ($client_id)";
    $body = "Go to https://app.smartwaiver.com/console, search for $first_name $last_name $email\n"
        . "Click Preview\n"
        . "Open in new tab\n"
        . "Copy the unique ID from the URL in to the user profile located: https://{$_SERVER['HTTP_HOST']}/admin/clientssummary.php?userid=$client_id";
    open_ticket($subject, $body);
});