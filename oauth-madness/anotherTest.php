<?php

require_once(dirname(__FILE__) . "/google-api-php-client/src/Google_Client.php");
require_once(dirname(__FILE__) . "/google-api-php-client/src/contrib/Google_AnalyticsService.php");
 
$clientId = "197314413399.apps.googleusercontent.com";
$clientSecret = "wBosmPys0Ce9jleh1OAp-g4k";
$oauthInfo = '{"access_token":"ya29.AHES6ZTMaXBqJMs61A8nxTjIll4iqqnh292Fd2TxKrx0GWy50Anz","token_type":"Bearer","expires_in":3600,"refresh_token":"1\/eaA8r7wzbODtU0O8JPE3mzlqWGisSgt67zf_fTTDp-A","created":1368815690}';
 
$client = new Google_Client();
$client->setClientId($clientId);
$client->setClientSecret($clientSecret);
$client->setAccessToken($oauthInfo);
$client->setUseObjects(true);
$analytics = new Google_AnalyticsService($client);
 
$result = $analytics->management_accounts->listManagementAccounts();
$accounts = $result->items;
foreach ($accounts as $account) {
    print "Found an account with an ID of {$account->id} and a name of {$account->name}<br>"; 
}
 
$accountId = $accounts[1]->id;
$result = $analytics->management_webproperties->listManagementWebproperties($accountId);
$webProperties = $result->items;
 
foreach ($webProperties as $webProperty) {
    print "Found a web property for the site {$webProperty->websiteUrl}, with an ID of {$webProperty->id} and a name of {$webProperty->name}<br/>\n\n"; 
}
 
$webPropertyId = $webProperties[0]->id;
$result = $analytics->management_profiles->listManagementProfiles($accountId, $webPropertyId);
$profiles = $result->items;
 
foreach ($profiles as $profile) {
    print "Found a profile with an ID of {$profile->id} and a name of {$profile->name}<br/>"; 
}
 
$profileId = $profiles[0]->id;
print "$profileId\n";
?>