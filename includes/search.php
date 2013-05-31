<?php
	include 'config.php'; 
	date_default_timezone_set('EST');
	$con=mysqli_connect($DB_HOST,$DB_USER,$DB_PWD,$DB_NAME);
	$searchtext = $_REQUEST['searchtext'];
	
	$result = array();
	if (isset($searchtext)){
	  	if (mysqli_connect_errno($con))
		{
			echo "Failed to connect to MySQL: " . mysqli_connect_error();
		}

		$sql="SELECT id, name, city, country FROM individual_visitors WHERE name LIKE '%" . mysqli_real_escape_string($con, $searchtext) . "%'";
	    $sql_result = mysqli_query($con,$sql);
	    
  		while($row = mysqli_fetch_array($sql_result)){
  			$person = array('name' => $row["name"], 'db_id'=> $row["id"], 'city' => $row["city"], 'country' => $row["country"]);
  			array_push($result, $person);
  		}
  		echo json_encode($result);
    }
?>