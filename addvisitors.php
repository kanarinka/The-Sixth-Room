<?php
  include 'includes/config.php'; 
  date_default_timezone_set('EST');
  $con=mysqli_connect($DB_HOST,$DB_USER,$DB_PWD,$DB_NAME);

  $duplicate_error = false;
  if (mysqli_connect_errno($con))
  {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
  }
  
  if(isset($_POST['num_visitors'])){
    $formattedDate = date('Y-m-d H:i:s', strtotime($_POST['visit_date']));
    $halfDate = substr($formattedDate, 0, 10);
    $duplicate = mysqli_query($con, "SELECT COUNT(*) from individual_visitors WHERE venue = 'MUSEUM' AND visit_date LIKE '". $halfDate . "%'")  or die("SQL error");
    $count = mysqli_fetch_array($duplicate)[0];
    if ($count > 0){
      $duplicate_error = true;
    } else {
      $num_visitors = $_POST['num_visitors'];
      for ($i=0;$i<$num_visitors;$i++){
        $sql="INSERT INTO individual_visitors (visit_date, venue)
              VALUES
              ('" . $formattedDate . "' ,'MUSEUM')";

        if (!mysqli_query($con,$sql))
        {
          die('Error: ' . mysqli_error($con));
        }
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
                  <option value="today">Today</option>
                  <option value="yesterday">Yesterday</option>
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
  $result = mysqli_query($con,"SELECT COUNT(*) AS num_visitors, visit_date, venue FROM individual_visitors WHERE venue = 'MUSEUM' GROUP BY visit_date ORDER BY visit_date DESC ");
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