<?php

  include 'includes/config.php'; 
  
  $con=mysqli_connect($DB_HOST,$DB_USER,$DB_PWD,$DB_NAME);


  if (mysqli_connect_errno($con))
  {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
  }
  
?>
<!DOCTYPE html>
<html>
    <head>
        <title>The Sixth Room: Guestbook</title>
        
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
        <meta charset="utf-8">
            
            
        <script type="text/javascript" charset="utf-8" src="https://ajax.googleapis.com/ajax/libs/jquery/1.8.1/jquery.min.js"></script>
        <script src="bootstrap/js/bootstrap.min.js"></script>
        <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
       
        <link href="bootstrap/css/bootstrap-responsive.min.css" rel="stylesheet">
        <link href="css/thesixthroom.css" rel="stylesheet">
        <link href='http://fonts.googleapis.com/css?family=Codystar' rel='stylesheet' type='text/css'>
        
    </head>
    <body>
      <div class="container-fluid">
        <div id="main">
          <div class="row-fluid">
          <h1 style="font-family: 'Codystar', cursive;">The Sixth Room Guestbook Admin</h1>
          
          <table class="table table-striped table-bordered table-hover table-condensed">
            <thead>
              <tr>
                <th>Name</th>
                <th>Date</th>
                <th>Location</th>
                <!--<th>Comments</th>-->
              </tr>
            </thead>
            <tbody>
<?php
  $result = mysqli_query($con,"SELECT * FROM individual_visitors ORDER BY visit_date DESC");
  while($row = mysqli_fetch_array($result))
  {

    ?>
              <tr>
                <td><?= $row['name'] ?></td>
                <td><?= date("D M j Y", strtotime( $row['visit_date']) )?> </td>
                <td><?= $row['city'] ?>, <?= $row['state'] ?>, <?= $row['country'] ?></td>
                <!--<td><?= $row['comments'] ?></td>-->
              </tr>
<?php } ?>
            </tbody>
          </table>
      </div>
    </div>
  </div>
    </body>          
    
</html>