# Cockpit CMS Push Notification
Push Notification addon for Cockpit CMS

### User required fields 
``` JSON
{
  "deviceType": "iOS/Android"
  "deviceToken": "Android or iOS device token"
}
```

`config.yml`
```YAML
pushNotifications:
  cert:
    dev: 'cert-dev.pem'
    live: 'cert-live.pem'
  keys:
    ios: 'P@ssw0rd'
    android: 'AAAA_XXXXXXXXXXXXXXXXXXXX'
```

## Usage:
currently there is no UI available but you can use it as follows:

```PHP
$message = [
  'mtitle' => 'This is push notification title',
  'mdesc' => 'This is the content'
]
$this->app->module('pushnotification')->push($message);
```
