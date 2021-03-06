<?php
require_once(dirname(__FILE__) . "/google-api-php-client/src/Google_Client.php");
require_once(dirname(__FILE__) . "/google-api-php-client/src/contrib/Google_AnalyticsService.php");

session_start();

include '../includes/config.php'; 
include '../includes/continents.php'; 

$con=mysqli_connect($DB_HOST,$DB_USER,$DB_PWD,$DB_NAME);

if (mysqli_connect_errno($con))
{
  echo "Failed to connect to MySQL: " . mysqli_connect_error();
}

$client = new Google_Client();
$client->setApplicationName('Hello Analytics API Sample');
$oauthInfo = '{"access_token":"ya29.AHES6ZTMaXBqJMs61A8nxTjIll4iqqnh292Fd2TxKrx0GWy50Anz","token_type":"Bearer","expires_in":3600,"refresh_token":"1\/eaA8r7wzbODtU0O8JPE3mzlqWGisSgt67zf_fTTDp-A","created":1368815690}';
// Visit //code.google.com/apis/console?api=analytics to generate your
// client id, client secret, and to register your redirect uri.
$client->setClientId($GA_CLIENT_ID);
$client->setClientSecret($GA_CLIENT_SECRET);
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

      // Step 4. Store the results.
      //storeResults($results);

      
      printResults($results);
    }

  } catch (apiServiceException $e) {
    // Error from the API.
    print 'There was an API error : ' . $e->getCode() . ' : ' . $e->getMessage();

  } catch (Exception $e) {
    print 'There wan a general error : ' . $e->getMessage();
  }
}

function getResults(&$analytics, $profileId) {
	$ids = 'ga:' . $profileId;
	$start_date = "2013-05-23";
	$end_date = "2013-05-23";
	$metrics = "ga:visits";
	$dimensions = "ga:city,ga:region,ga:country,ga:continent,ga:hour";
	$optParams = array('dimensions' => $dimensions);
	return $analytics->data_ga->get($ids,$start_date,$end_date,$metrics,$optParams);
}
function printResults(&$results) {
	echo "<pre>"; 
  print_r($results);
  echo "</pre>";
}
function storeResults(&$results){
  global $countries_to_country_codes;
  global $countries_to_continent_abbreviations;
  global $continent_abbreviations_to_continents;
  global $con;

  foreach ($results->rows as $row){

    //if field contains "not set" then make it blank
    $city = strpos($row[0] , "not set") ? "" : $row[0];
    $state = strpos($row[1] , "not set") ? "" : $row[1];
    $country = strpos($row[2] , "not set") ? "" : $row[2];
    $continent = strpos($row[3] , "not set") ? "" : $row[3];
    $num_visitors = $row[4];
    $matches = preg_grep_keys ('/'.$country.'/i', $countries_to_country_codes);
    
    $country_abbreviation = $matches[$country];
    $continent_abbreviation = $countries_to_continent_abbreviations[$country_abbreviation];
    $continent = $continent_abbreviations_to_continents[$continent_abbreviation];
    
    $i =0;
    while($i < $num_visitors){
      $sql="INSERT INTO individual_visitors (visit_date, name, city, state, country_abbreviation, country, continent_abbreviation, continent, venue)
          VALUES
          ('" . date('Y-m-d H:i:s', strtotime('yesterday')) . "' ,'" . mysqli_real_escape_string($con, 'anonymous') . "','$city','$state','$country_abbreviation','$country','$continent_abbreviation','$continent', 'ONLINE')";

      if (!mysqli_query($con,$sql))
      {
        die('Error: ' . mysqli_error($con));
      } 
      echo "stored one";
      $i++;
    }
     
  }
}
function preg_grep_keys( $pattern, $input, $flags = 0 )
{
    $keys = preg_grep( $pattern, array_keys( $input ), $flags );
    $vals = array();
    foreach ( $keys as $key )
    {
        $vals[$key] = $input[$key];
    }
    return $vals;
}
?>