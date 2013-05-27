<?php
	//run python script to generate new files
	echo exec('whoami');
	echo "<br/>";
	echo exec('pwd');
	echo "<br/>";
	exec("python ../python/makedatafiles.py 2>&1", $output);
	echo '<pre>';
	print_r($output);
	echo '</pre>';
 ?>