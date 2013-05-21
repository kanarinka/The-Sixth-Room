<?php
require_once(dirname(__FILE__) . "/google-api-php-client/src/Google_Client.php");
require_once(dirname(__FILE__) . "/google-api-php-client/src/contrib/Google_AnalyticsService.php");

session_start();

$client = new Google_Client();
$client->setApplicationName('Hello Analytics API Sample');
$oauthInfo = '{"access_token":"ya29.AHES6ZTMaXBqJMs61A8nxTjIll4iqqnh292Fd2TxKrx0GWy50Anz","token_type":"Bearer","expires_in":3600,"refresh_token":"1\/eaA8r7wzbODtU0O8JPE3mzlqWGisSgt67zf_fTTDp-A","created":1368815690}';
// Visit //code.google.com/apis/console?api=analytics to generate your
// client id, client secret, and to register your redirect uri.
$client->setClientId('197314413399.apps.googleusercontent.com');
$client->setClientSecret('wBosmPys0Ce9jleh1OAp-g4k');
$client->setRedirectUri('http://www.thesixthroom.org/oauth2callback');
//$client->setDeveloperKey($oauthInfo);
$client->setAccessToken($oauthInfo);
$client->setScopes(array('https://www.googleapis.com/auth/analytics.readonly'));

// Magic. Returns objects from the Analytics Service instead of associative arrays.
$client->setUseObjects(true);

if (isset($_GET['code'])) {
  $client->authenticate();
  $_SESSION['token'] = $client->getAccessToken();
  $redirect = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
  header('Location: ' . filter_var($redirect, FILTER_SANITIZE_URL));
}

if (isset($_SESSION['token'])) {
  $client->setAccessToken($_SESSION['token']);
}

if (!$client->getAccessToken()) {
  $authUrl = $client->createAuthUrl();
  print "<a class='login' href='$authUrl'>Connect Me!</a>";

} else {
  $analytics = new Google_AnalyticsService($client);
  runMainDemo($analytics);
}

// function runMainDemo continued in next section.
function runMainDemo(&$analytics) {
  try {

    // Step 2. Get the user's first profile ID.
    $profileId = '72356214';
  	//$profileId = getFirstProfileId($analytics);
  	
    if (isset($profileId)) {

      // Step 3. Query the Core Reporting API.
      $results = getResults($analytics, $profileId);

      // Step 4. Output the results.
      printResults($results);
    }

  } catch (apiServiceException $e) {
    // Error from the API.
    print 'There was an API error : ' . $e->getCode() . ' : ' . $e->getMessage();

  } catch (Exception $e) {
    print 'There wan a general error : ' . $e->getMessage();
  }
}
function getFirstprofileId(&$analytics) {
  $accounts = $analytics->management_accounts->listManagementAccounts();

  if (count($accounts->getItems()) > 0) {
    $items = $accounts->getItems();
    $firstAccountId = $items[0]->getId();

    $webproperties = $analytics->management_webproperties
        ->listManagementWebproperties($firstAccountId);

    if (count($webproperties->getItems()) > 0) {
      $items = $webproperties->getItems();
      $firstWebpropertyId = $items[0]->getId();

      $profiles = $analytics->management_profiles
          ->listManagementProfiles($firstAccountId, $firstWebpropertyId);

      if (count($profiles->getItems()) > 0) {
        $items = $profiles->getItems();
        return $items[0]->getId();

      } else {
        throw new Exception('No profiles found for this user.');
      }
    } else {
      throw new Exception('No webproperties found for this user.');
    }
  } else {
    throw new Exception('No accounts found for this user.');
  }
}
function getResults(&$analytics, $profileId) {
	$ids = 'ga:' . $profileId;
	$start_date = "2013-05-01";
	$end_date = "2013-05-17";
	$metrics = "ga:visits";
	$dimensions = "ga:country";
	$optParams = array('dimensions' => $dimensions);
	return $analytics->data_ga->get($ids,$start_date,$end_date,$metrics,$optParams);

   /*return $analytics->data_ga->get(
       'ga:' . $profileId,
       '2013-05-03',
       '2013-05-17',
       'ga:visits');*/
}
function printResults(&$results) {
	echo "<pre>"; 
print_r($results);
echo "</pre>";

/*  if (count($results->getRows()) > 0) {
    $profileName = $results->getProfileInfo()->getProfileName();
    $rows = $results->getRows();
    $visits = $rows[0][0];
    print $results;
    print "<p>First profile found: $profileName</p>";
    print "<p>Total visits: $visits</p>";

  } else {
    print '<p>No results found.</p>';
  }*/
}
?>