<?php

require_once  __DIR__ . '/badge_controller.php';
require_once  __DIR__ . '/whmcs_controller.php';

use WHMCS\Database\Capsule;
use WHMCS\User\Client;

# https://developers.whmcs.com/hooks-reference/everything-else/
# Using the CustomFieldSave because if we use ClientEdit, we wouldn't have the before/after values.
# I think this is the only hook that lets us capture both
add_hook('CustomFieldSave', 1, function($vars) {
    logActivity('CustomFieldSave: ' . json_encode($vars));
    $field_id = $vars['fieldid'];
    $new_badge_id = $vars['value'];
    $client_id = $vars['relid'];

    $field_name = Capsule::table('tblcustomfields')->where('id', $field_id)->value('fieldname');
#    logActivity('FIELD NAME: ' . $field_name);
    if ($field_name == 'Badge Number') {
        $old_value = Capsule::table('tblcustomfieldsvalues')->where('fieldid', $field_id)->where('relid', $client_id)->value('value');
#        logActivity('OLD VALUE: ' . $old_value);

        if ($old_value == $new_badge_id) {
            logActivity('Badge id HAS NOT changed.  No action');
            return;
        }

        $badge_controller = new BadgeController();
        if ($old_value != '') {
            logActivity('Badge id HAS changed.  Open Ticket to deactivate old badge id: ' . $old_value);
            open_ticket("DELETE Badge ID $old_value", "User $client_id updated badge from $old_value to $new_badge_id");
        }

        if ($new_badge_id) {
            // Check if valid Waiver has been provided.
            logActivity("Activate new badge: " . $new_badge_id . " for Client $client_id");
            try {
                $client = Client::findOrFail($client_id);
                $full_name = $client->firstName . ' ' . $client->lastName;
                $badge_controller->add_badge($new_badge_id, $full_name);
            } catch (Exception $e) {
                $err_msg = "update_badge.php: Couldn't find client $client_id. {$e->getMessage()}";
                logActivity($err_msg);
                echo $err_msg;
            }
        }
    }
});
