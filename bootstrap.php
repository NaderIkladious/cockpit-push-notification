<?php
$this->module("pushnotification")->extend([
  'android' => function ($data, $reg_id) {
    $url = 'https://fcm.googleapis.com/fcm/send';
    $message = array(
      'title' => $data['mtitle'],
      'message' => $data['mdesc'],
      'subtitle' => '',
      'tickerText' => '',
      'msgcnt' => 1,
      'vibrate' => 1
    );
    $headers = array(
      'Authorization: key=' . $this->app->retrieve('pushNotifications/keys/android', null),
      'Content-Type: application/json'
    );
    $fields = array(
      'registration_ids' => array($reg_id),
      'data' => $message,
    );
    return $this->useCurl($url, $headers, json_encode($fields));
  },
  'iOS' => function ($data, $devicetoken) {
    $deviceToken = $devicetoken;
    $ctx = stream_context_create();
    $gateway = '';
    if (isset($_ENV['APPLE_CERT'])) {
      stream_context_set_option($ctx, 'ssl', 'local_cert', __DIR__ . '/' . $this->app->retrieve('config/pushNotifications/cert/live', null));
      $gateway = 'ssl://gateway.push.apple.com:2195';
    } else {
      stream_context_set_option($ctx, 'ssl', 'local_cert', __DIR__ . '/' . $this->app->retrieve('config/pushNotifications/cert/dev', null));
      $gateway = 'ssl://gateway.sandbox.push.apple.com:2195';
    }
    stream_context_set_option($ctx, 'ssl', 'passphrase', $this->app->retrieve('config/pushNotifications/keys/ios', null));
    // Open a connection to the APNS server
    $fp = stream_socket_client(
      $gateway, $err,
      $errstr, 60, STREAM_CLIENT_CONNECT | STREAM_CLIENT_PERSISTENT, $ctx);
    if (!$fp)
      exit("Failed to connect: $err $errstr" . PHP_EOL);
    // Create the payload body
    $body['aps'] = array(
      'alert' => array(
        'title' => $data['mtitle'],
        'body' => $data['mdesc'],
      ),
      'sound' => 'default'
    );
    // Encode the payload as JSON
    $payload = json_encode($body);
    // Build the binary notification
    $msg = chr(0) . pack('n', 32) . pack('H*', $deviceToken) . pack('n', strlen($payload)) . $payload;
    // Send it to the server
    $result = fwrite($fp, $msg, strlen($msg));
    // Close the connection to the server
    fclose($fp);
    if (!$result)
      return 'Message not delivered' . PHP_EOL;
    else
      return 'Message successfully delivered' . PHP_EOL;
  },
  'useCurl' => function ($url, $headers, $fields = null) {
    // Open connection
    $ch = curl_init();
    if ($url) {
      // Set the url, number of POST vars, POST data
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_POST, true);
      curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      // Disabling SSL Certificate support temporarly
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
      if ($fields) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
      }
      // Execute post
      $result = curl_exec($ch);
      if ($result === FALSE) {
        die('Curl failed: ' . curl_error($ch));
      }
      // Close connection
      curl_close($ch);
      return $result;
    }
  },
  '_pushAndroid' => function ($data) {
    $options = [
      'filter' => [
        'deviceType' => 'Android'
      ]
    ];
    $androidUsers = $this->app->storage->find("cockpit/accounts", $options)->toArray();
    foreach ($androidUsers as $user) {
      if (!empty($user['deviceToken'])) {
        $this->android($data, $user['deviceToken']);
      }
    }
    return;
  },
  '_pushiOS' => function ($data) {
    $options = [
      'filter' => [
        'deviceType' => 'iOS'
      ]
    ];
    $iOSUsers = $this->app->storage->find("cockpit/accounts", $options)->toArray();
    foreach ($iOSUsers as $user) {
      if (!empty($user['deviceToken'])) {
        $this->iOS($data, $user['deviceToken']);
      }
    }
    return;
  },
  'push' => function ($data) {
    $this->_pushAndroid($data);
    $this->_pushiOS($data);
    return;
  }
]);