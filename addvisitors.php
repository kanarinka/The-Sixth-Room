<?php
  include 'includes/config.php'; 
  date_default_timezone_set('EST');
  $con=mysqli_connect($DB_HOST,$DB_USER,$DB_PWD,$DB_NAME);

  $duplicate_error = false;

  $today = date('m-d-Y', strtotime('today'));
  $yesterday = date('m-d-Y', strtotime('yesterday'));
  $twodays = date('m-d-Y', strtotime('2 days ago'));
  $threedays = date('m-d-Y', strtotime('3 days ago'));
  $fourdays = date('m-d-Y', strtotime('4 days ago'));
  $fivedays = date('m-d-Y', strtotime('5 days ago'));
  $sixdays = date('m-d-Y', strtotime('6 days ago'));
  $sevendays = date('m-d-Y', strtotime('7 days ago'));
  $eightdays = date('m-d-Y', strtotime('8 days ago'));

  if (mysqli_connect_errno($con))
  {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
  }
  
  if(isset($_POST['num_visitors'])){
    $formattedDate = date('Y-m-d H:i:s', strtotime($_POST['visit_date']));

    $halfDate = substr($formattedDate, 0, 10);
    $duplicate = mysqli_query($con, "SELECT COUNT(*) from individual_visitors WHERE venue = 'MUSEUM' AND visit_date LIKE '". $halfDate . "%'") or die("SQL error");
    $count = mysqli_fetch_array($duplicate);
    if ($count[0] > 0){
      $duplicate_error = true;
    } else {

      $num_visitors = $_POST['num_visitors'];
      
      $visit_date = new DateTime();
      $visit_date->setTimestamp(strtotime($_POST['visit_date']));
      $name = "US Pavilion visitor";
      /*
        Say exhibition hours are 10am - 6pm
      */
      $begin_hour = 10-2;
      $end_hour = 18-2;

      $visit_date->setTime($begin_hour, 0,0);
      
      $total_seconds = ($end_hour - $begin_hour) * 60 * 60;
      $second_interval = $total_seconds/$num_visitors;

      for ($i=0;$i<$num_visitors;$i++){
        $visit_date->modify("+". strval(round($second_interval)) . " seconds");
        //$visit_date->modify("+". strval(rand(0,59)) . " seconds");
        $sql="INSERT INTO individual_visitors (name, visit_date, venue, city, country, country_abbreviation, continent, continent_abbreviation, visited_us_pavilion)
              VALUES
              ('" . $name . "', '" . $visit_date->format('Y-m-d H:i:s') . "' ,'MUSEUM', 'Venice', 'Italy', 'IT', 'Europe', 'EU', 1)";

        if (!mysqli_query($con,$sql))
        {
          die('Error: ' . mysqli_error($con));
        }
      }
      if ($i > 0){
        //run python script to generate new json data files
        exec("python " . $SERVER_PATH ."python/makedatafiles.py", $output);
        
      }
    }
    
  }
?>
<!DOCTYPE html>
<html>
    <head>
        <title>The Sixth Room: Add Visitor Counts</title>
        
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
        <meta charset="utf-8">
            
            
        <script type="text/javascript" charset="utf-8" src="https://ajax.googleapis.com/ajax/libs/jquery/1.8.1/jquery.min.js"></script>
        <script src="bootstrap/js/bootstrap.min.js"></script>
        <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
        
        <link href="bootstrap/css/bootstrap-responsive.min.css" rel="stylesheet">
        <link href="css/thesixthroom.css" rel="stylesheet">
        
    </head>
    <body>
      <div class="container-fluid">
        <div id="main">
          <div class="row-fluid">

          <h1>The Sixth Room</h1>
          <h3>Enter visitors to the U.S. pavilion</h3>
          <?php
            if ($duplicate_error){
              echo '<h4 style="color:red">Sorry, that date already has visitor data.</h4>';
            }
            if (isset($output)){
              echo '<h4 style="color:red"> UPDATE RESULTS (If it says "all done, maestro" then 
                everything updated properly. Otherwise, copy and paste message and send to Catherine):</h4><br/><pre>';
              echo var_dump($output);
              echo "</pre>";
              
            }
          ?>
          <form style="margin-top:40px" method="post" action="addvisitors.php">
            <div class="control-group">
              <label class="control-label" for="inputEmail">Visitor Count:</label>
              <div class="controls">
                <input type="text" id="num_visitors" name="num_visitors" placeholder="# visitors">
              </div>
            </div>
            <div class="control-group">
              <label class="control-label" for="inputPassword">For Date:</label>
              <div class="controls">
                <select name="visit_date">
                  <option value="today">Today - <?= $today ?></option>
                  <option value="yesterday">Yesterday - <?= $yesterday ?></option>
                  <option value="2 days ago">Two Days Ago - <?= $twodays ?></option>
                  <option value="3 days ago">Three Days Ago - <?= $threedays ?></option>
                  <option value="4 days ago">Four Days Ago - <?= $fourdays ?></option>
                 <option value="5 days ago">Five Days Ago - <?= $fivedays ?></option>
                  <option value="6 days ago">Six Days Ago - <?= $sixdays ?></option>
                   <option value="7 days ago">Seven Days Ago - <?= $sevendays ?></option>
                    <option value="8 days ago">Eight Days Ago - <?= $eightdays ?></option>
                </select>
              </div>
            </div>
            <div class="control-group">
              <div class="controls">
                
                <button type="submit" class="btn">Submit</button>
              </div>
            </div>
            <span class="help-block" style="margin-top:40px">Problems? Email <a href="mailto:dignazio@mit.edu">Catherine</a></span>
          </form>
          <table class="table table-striped table-bordered table-hover table-condensed">
            <thead>
              <tr>
                <th>Date</th>
                <th>US Pavilion Visitors</th>
              </tr>
            </thead>
            <tbody>
<?php
  $result = mysqli_query($con,"SELECT COUNT(date(visit_date)) AS num_visitors, visit_date FROM individual_visitors WHERE venue = 'MUSEUM' GROUP BY date(visit_date), venue ORDER BY visit_date DESC");
  while($row = mysqli_fetch_array($result))
  {

    ?>
              <tr>
                <td><?= date("D M j Y", strtotime( $row['visit_date']) )?> </td>
                <td><?= $row['num_visitors'] ?></td>
              </tr>
<?php } ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
    </body>          
    
</html>