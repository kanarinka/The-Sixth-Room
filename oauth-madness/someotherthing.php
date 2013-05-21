<?php
require_once(dirname(__FILE__) . "/google-api-php-client/src/Google_Client.php");


$clientId = "197314413399.apps.googleusercontent.com";
$clientSecret = "wBosmPys0Ce9jleh1OAp-g4k";
#$redirectUrl = 'http://www.thesixthroom.org/oauth2callback';
 
$analyticsWriteScope = "https://www.googleapis.com/auth/analytics";
 
$client = new Google_Client();
$client->setClientId($clientId);
$client->setClientSecret($clientSecret);
#$client->setRedirectUri($redirectUrl);
$client->setScopes(array($analyticsWriteScope));
 
$authUrl = $client->createAuthUrl();
 
print "$authUrl\n";
?>