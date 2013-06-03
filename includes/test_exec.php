<?php
include 'config.php';
exec("python " . $SERVER_PATH ."python/makedatafiles.py 2>&1", $output);

	echo '<pre>';
	print_r($output);
	echo '</pre>';
 ?>