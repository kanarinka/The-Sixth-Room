<?php 
date_default_timezone_set('EST');
$yesterday = strtotime('yesterday');
echo $yesterday;

$start_date ="05-24-2013";
$start_date2 = substr($start_date, 6) . "-" . substr($start_date, 0,2) . "-" . substr($start_date, 3,2);
echo $start_date2;
$date = new DateTime($start_date2,new DateTimeZone('America/New_York'));
/*echo "<br/>".substr($start_date, 6) ."<br/>";
echo "<br/>".substr($start_date, 3,2) ."<br/>";
echo "<br/>".substr($start_date, 0,2) ."<br/>";
$year = substr($start_date, 6);
$month =substr($start_date, 3,2);
$day = substr($start_date, 0,2);
#$date -> setDate( $year, $month, $day );
*/
echo $date->format('l, F jS, Y, H:i:s');
/*
$date->setTimestamp(strtotime('yesterday'));

$begin_hour = 10-2;
$end_hour = 18-2;

$date->setTime($begin_hour, 0,0);
echo $date->format('l, F jS, Y, H:i:s');

$total_minutes = ($begin_hour - $end_hour) * 60;
$minute_interval = $total_minutes/10;

echo "minute interval is " . $minute_interval;

$date->modify("+". strval(round($minute_interval)) . " minutes");
echo $date->format('l, F jS, Y, H:i:s');
*/
?>
<br/>
<script type="text/javascript">
document.write(Date.now());
</script>