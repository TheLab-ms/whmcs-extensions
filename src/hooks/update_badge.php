<?php

use WHMCS\Database\Capsule;

add_hook('CustomFieldSave', 1, function($vars) {
    #logActivity('UPDATE BADGE: ' . json_encode($vars), 0);
    $field_id = $vars['fieldid'];
    $new_badge_id = $vars['value'];

    $field_name = Capsule::table('tblcustomfields')->where('id', $field_id)->value('fieldname');
    #logActivity('FIELD NAME: ' . $field_name, 0);
    if ($field_name == 'Badge Number') {
        $old_value = Capsule::table('tblcustomfieldsvalues')->where('fieldid', $field_id)->where('relid', $vars['relid'])->value('value');
        #logActivity('OLD VALUE: ' . $old_value, 0);

        if ($old_value == $new_badge_id) {
            logActivity('TODO: Badge id HAS NOT changed.  No action', 0);
            return;
        }

        if ($old_value != '') {
            logActivity('TODO: Badge id HAS changed.  Deactivate old badge id: ' . $old_value, 0);
        }

        if ($new_badge_id) {
            logActivity('TODO: Activate new badge: ' . $new_badge_id, 0);
        }
    }
});
