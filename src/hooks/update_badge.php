<?php

use WHMCS\Database\Capsule;

# https://developers.whmcs.com/hooks-reference/everything-else/
add_hook('CustomFieldSave', 1, function($vars) {
    #logActivity('UPDATE BADGE: ' . json_encode($vars));
    $field_id = $vars['fieldid'];
    $new_badge_id = $vars['value'];

    $field_name = Capsule::table('tblcustomfields')->where('id', $field_id)->value('fieldname');
    #logActivity('FIELD NAME: ' . $field_name);
    if ($field_name == 'Badge Number') {
        $old_value = Capsule::table('tblcustomfieldsvalues')->where('fieldid', $field_id)->where('relid', $vars['relid'])->value('value');
        #logActivity('OLD VALUE: ' . $old_value);

        if ($old_value == $new_badge_id) {
            logActivity('Badge id HAS NOT changed.  No action');
            return;
        }

        if ($old_value != '') {
            logActivity('TODO: Badge id HAS changed.  Deactivate old badge id: ' . $old_value);
        }

        if ($new_badge_id) {
            // Check if valid Waiver has been provided.
            logActivity('TODO: Activate new badge: ' . $new_badge_id);
        }
    }
});
