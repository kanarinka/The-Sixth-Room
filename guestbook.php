
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
          <h1 style="font-family: 'Codystar', cursive;">The Sixth Room Guestbook</h1>
          <p style="font-size:1.4em">Signing the guestbook includes your name and location as part of the Sixth Room network visualization.</p>
          
          <?php 
            include 'includes/guestbook_form.php'; 
          ?>
          
          
          <hr/>
          <a href="guestbook.php" class="btn btn-info" id="guestbook-start-over-button">Start Over</a>
      </div>
    </div>
  </div>
    </body>          
    
</html>