<?php
	include 'config.php'; 
	date_default_timezone_set('EST');
	$con=mysqli_connect($DB_HOST,$DB_USER,$DB_PWD,$DB_NAME);
	$after_date = $_REQUEST['after_date'];
	$new_time = time();
	$text = "";
	$result = array("new_time" => $new_time, "text" => $text);
	if (isset($after_date)){

		$after_date = date('Y-m-d H:i:s', $after_date);

	  	if (mysqli_connect_errno($con))
		{
			echo "Failed to connect to MySQL: " . mysqli_connect_error();
		}

		$sql="SELECT * FROM individual_visitors WHERE visit_date > '" . mysqli_real_escape_string($con, $after_date) . "'";
	    
	    $sql_result = mysqli_query($con,$sql);
	    $updated = false;
  		while($row = mysqli_fetch_array($sql_result)){

  			$text = $text . $row["name"] . " from " . $row["city"] . " " . $row["country"] . " just entered the network <br/>";
  			$updated = true;
  		}
  		if ($updated){
  			//run python script to generate new files
  			exec("python " . $SERVER_PATH ."python/makedatafiles.py", $output);
  		}
  		$result["text"] = $text;
  		$result["sql"] = $sql;
  		echo json_encode($result);
    }
?>