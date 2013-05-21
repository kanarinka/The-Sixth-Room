<?php

  $con=mysqli_connect("localhost","webapp","1l0ves1x","thesixthroom");

  if (mysqli_connect_errno($con))
  {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
  }
  
  if(isset($_POST['name'])){

    $sql="INSERT INTO guestbook_visitor (visit_date, name, city, country, comments)
          VALUES
          ('" . date('Y-m-d H:i:s', strtotime('today')) . "' ,'" . mysqli_real_escape_string($con, $_POST['name']) . "','$_POST[city]','$_POST[country]','$_POST[comments]')";

    if (!mysqli_query($con,$sql))
    {
      die('Error: ' . mysqli_error($con));
    }
    
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
        
    </head>
    <body>
      <div class="container-fluid">
        <div id="main">
          <div class="row-fluid">
          <h1>The Sixth Room Guestbook</h1>
          <h3>Demo - will later be integrated into viz as modal</h3>
          <form class="form-horizontal" style="margin-top:40px" method="post" action="guestbook.php">
            <div class="control-group">
              <label class="control-label" for="name">Name:</label>
              <div class="controls">
                <input type="text" id="name" name="name" placeholder="Your name">
              </div>
            </div>
            <div class="control-group">
              <label class="control-label" for="city">City/Country:</label>
              <div class="controls">
                 <input type="text" id="city" name="city" placeholder="City">
                 <input type="text" id="country" name="country" placeholder="Country">
              </div>
            </div>
            <div class="control-group">
              <label class="control-label" for="comments">Your comments:</label>
              <div class="controls">
                 <textarea rows="3" name="comments"></textarea>
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
                <th>Name</th>
                <th>Date</th>
                <th>Location</th>
                <!--<th>Comments</th>-->
              </tr>
            </thead>
            <tbody>
<?php
  $result = mysqli_query($con,"SELECT * FROM guestbook_visitor");
  while($row = mysqli_fetch_array($result))
  {

    ?>
              <tr>
                <td><?= $row['name'] ?></td>
                <td><?= date("D M j Y", strtotime( $row['visit_date']) )?> </td>
                <td><?= $row['city'] ?>, <?= $row['country'] ?></td>
                <!---<td><?= $row['comments'] ?></td>--->
              </tr>
<?php } ?>
            </tbody>
          </table>
      </div>
    </div>
  </div>
    </body>          
    
</html>