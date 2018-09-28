# Cockpit CMS Push Notification
Push Notification addon for Cockpit CMS

Download the repo and put it content in `addons/pushNotification`

### User required fields 
``` JSON
{
  "deviceType": "iOS/Android",
  "deviceToken": "Android or iOS device token"
}
```

### Changes in config.yml
```YAML
pushNotifications:
  cert:
    dev: 'cert-dev.pem'
    live: 'cert-live.pem'
  keys:
    ios: 'P@ssw0rd passphrase'
    android: 'AAAA_XXXXXXXXXXXXXXXXXXXX'
```

Make sure to add the iOS certificates in the same directory and make sure to change the `config.yml` cert file names

## Usage:
currently there is no UI available but you can use it as follows:

```PHP
$message = [
  'mtitle' => 'This is push notification title',
  'mdesc' => 'This is the content'
]
$this->app->module('pushnotification')->push($message);
```
