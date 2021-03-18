<?php

if (!function_exists('logActivity')) {
    function logActivity($message) {
        print($message . PHP_EOL);
    }
}

function notify_discord_add_client($name) {
    require __DIR__ . '/config.php';
    logActivity('notify_discord_add_client: ' . $name);
    logActivity('notify_discord_add_client: URL=' . $DISCORD_WEBHOOK_ADD_CLIENT_URL);
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
    }',
    CURLOPT_HTTPHEADER => array(
        'Content-Type: application/json'
      ),
    ));

    $response = curl_exec($curl);
    logActivity('Curl response: ' . json_encode($response), 0);
    $info = curl_getinfo($curl);
    logActivity('Curl info: ' . json_encode($info), 0);

    curl_close($curl);
    logActivity('notify_discord_add_client: COMPLETE');
}