<?php

if (!function_exists('logActivity')) {
    function logActivity($message) {
        print($message . PHP_EOL);
    }
}

function notify_discord_add_client($name) {
    logActivity('notify_discord_add_client: ' . $name);
    $curl = curl_init();

    curl_setopt_array($curl, array(
    CURLOPT_URL => $DISCORD_WEBHOOK_ADD_CLIENT_URL,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS =>'{
        "embeds": [
            {
                "title": "New member!",
                "description": "A new member has been added!",
                "fields": [
                    {
                        "name": "Name",
                        "value": "' . $name . '",
                        "inline": true
                    }
                ]
            }
        ]
    }'
    ));

    $response = curl_exec($curl);

    curl_close($curl);
}