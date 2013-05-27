<?php
  session_start();
  

  /*
    URL from on site will be http://www.thesixthroom.org/index.php?uspavilion=true
    This prevents guestbook from popping up
  */
  if (isset($_GET['uspavilion'])) {
    $uspavilion = $_GET['uspavilion'];
    $_SESSION['uspavilion'] = $uspavilion;
  }
  else if (isset($_SESSION['uspavilion']))
    $uspavilion = $_SESSION['uspavilion'];
  else
    $uspavilion = false;

  if(!isset($_SESSION['has_seen_guestbook']) && !$uspavilion){
      $showGuestbook = true;
      $_SESSION['has_seen_guestbook']=true;
  } else{
    $showGuestbook = false;
  }

  if (isset($_GET['days']))
    $days = $_GET['days'];
  else 
    $days = 30;


  if (isset($_GET['model']))
    $model = $_GET['model'];
  else 
    $model = "time";

  //$streamdataFilepath = "data/streamdata_" . $model . "_" . $days . ".csv";
  //$networkdataFilepath = "data/networkdata_" . $model . "_" . $days . ".json";
  date_default_timezone_set('EST');
  $yesterday = date("Y_m_d", time() - 60 * 60 * 24);
  $current_network_date = $yesterday;
  $streamdataFilepath = "data/streamgraph_" . $model . ".csv";
  $networkdataFilepath = "data/networkdata_" . $model . "_" . $current_network_date . ".json";

?>
<!DOCTYPE html>
<meta charset="utf-8">
<title>The Sixth Room: a visualization of the network of virtual and physical audiences visiting Sarah Sze’s Triple Point</title>
<script type="text/javascript" charset="utf-8" src="http://code.jquery.com/jquery-1.8.3.min.js"></script>
<script type="text/javascript" charset="utf-8" src="js/date.js"></script>
<script src="bootstrap/js/bootstrap.min.js"></script>
<link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
<link href="css/thesixthroom_visualization.css" rel="stylesheet">
<link href='http://fonts.googleapis.com/css?family=Codystar' rel='stylesheet' type='text/css'>
<script src="js/d3.js"></script>
<body>

  <div id="visitor-info"></div>
  <div id="date-info"></div>
  <div id="timeline-date" class="time-label"></div>
  
  <!-- Main Nav  -->

  <h4 id="time-space"><a href="?model=space" id="space-button" role="button" data-toggle="modal">space</a> | 
    <a href="?model=time" role="button"  id="time-button" data-toggle="modal" class="selected">time</a> | 
    <a href="#" role="button" id="world-button" data-toggle="modal">world</a></h4>
  
  <div id="person-entered" style="position: absolute; left: 50%;">
    <p>Joseph Lacryphious from Salinas, KS, USA, just entered the network</p>
  </div>

  <!-- Button to trigger About modal  -->
  <h4 id="about-button"><a href="#about-modal" role="button" data-toggle="modal">about</a> | <a href="#whereami-modal" role="button" id="search-button" data-toggle="modal">search</a> 
    </h4>
   
  <!-- About Modal -->
  <div id="about-modal" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-header">
      <button type="button" class="pull-right btn btn-info" data-dismiss="modal" aria-hidden="true">×</button>
      <h3 style="font-family: 'Codystar', cursive;">The Sixth Room</h3>
    </div>
    <div class="modal-body">
      <p>Bloomberg is supporting "The Sixth Room," a digital tool that visually represents the network of virtual and physical audiences visiting <a href="http://www.sarahszevenice2013.com">Sarah Sze’s Triple Point</a>, both in Venice at the United States Pavilion and online over the course of la Biennale di Venezia.  Constantly updated, "The Sixth Room" data represents an ever-changing visual record of public engagement with Triple Point. Coordinated by <a href="#">Nell Breyer</a>, previously a research affiliate at MIT’s Center for Advanced Visual Studies and designed by <a href="#">Catherine D’Ignazio</a> from MIT's Media Lab, "The Sixth Room" is accessible online and on mobile devices.</p>
      <p>Related sites:</p>
      <ul>
        <li><a href="http://www.sarahszevenice2013.com/the-exhibition/virtual-tour">Virtual Tour of Sarah Sze Triple Point</a></li>
        <li><a href="http://www.sarahszevenice2013.com">Sarah Sze Triple Point Official Website</a></li>
      </ul>
      <p>This project is made possible by Bloomberg.</p>

      <p><em>Thanks and acknowledgements:</em> Sarah Sze Studio, The Bronx Museum of the Arts, U.S. Pavilion Office, The Peggy Guggenheim Collection, Exhibit-E, MIT Media Lab.</p>
    </div>
    <div class="modal-footer">
     
      
        <button id="sign-the-guestbook-button" class="btn btn-warning pull-right" style="margin-left:20px" data-dismiss="modal" aria-hidden="true">Sign the guestbook</button> 
        <p>Signing the guestbook includes your name and location as part of the Sixth Room network visualization.</p>
      
    </div>
  </div>
  <!-- Guestbook Modal -->
  <div id="guestbook-modal" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-header">
      <button type="button" class="pull-right btn btn-info" data-dismiss="modal" aria-hidden="true">×</button>
      <h3 style="font-family: 'Codystar', cursive;">The Sixth Room Guestbook</h3>
    </div>
    <div class="modal-body">
      <p>Signing the guestbook includes your name and location as part of the Sixth Room network visualization.</p>
      
        <?php 
          
          include 'includes/guestbook_form.php'; 
        ?>
      
    </div>
    <div class="modal-footer">
      <button class="btn btn-info pull-left" id="guestbook-about-button">About the project</button> <button class="btn btn-info" data-dismiss="modal" aria-hidden="true">Visit the Sixth Room</button>
    </div>
  </div>
</body>
<script type="text/javascript">
var isMobile = {
    Android: function() {
        return navigator.userAgent.match(/Android/i);
    },
    BlackBerry: function() {
        return navigator.userAgent.match(/BlackBerry/i);
    },
    iOS: function() {
        return navigator.userAgent.match(/iPhone|iPad|iPod/i);
    },
    Opera: function() {
        return navigator.userAgent.match(/Opera Mini/i);
    },
    Windows: function() {
        return navigator.userAgent.match(/IEMobile/i);
    },
    any: function() {
        return (isMobile.Android() || isMobile.BlackBerry() || isMobile.iOS() || isMobile.Opera() || isMobile.Windows());
    }
};
</script>
<script type="text/javascript">
    if (!isMobile.any())
      window.onresize = function(){window.location.reload();}

    var streamdataFilepath = "<?= $streamdataFilepath ?>";
    var networkdataFilepath = "<?= $networkdataFilepath ?>";
    window.currentNetworkDate = "<?= $current_network_date ?>";

    var showGuestbook = "<?= $showGuestbook ?>";
    var lastTime = "<?= time() ?>";

    var model = "<?= $model ?>";

    if (showGuestbook){
      $('#guestbook-modal').modal();
    }
    $('#guestbook-modal').on('hidden', function () {
      $('#guestbook-result').hide();
      $('#guestbook-form').show();
      $('#guestbook-form').find("input[type=text], textarea, select").val("")
    });
    $('#sign-the-guestbook-button').click(function(){
        $('#about-modal').modal('hide');
        $('#guestbook-modal').modal();
    });
    $('#guestbook-about-button').click(function(){
        $('#guestbook-modal').modal('hide');
        $('#about-modal').modal();
    });
    $('#world-button, #search-button').click(function(){alert('coming soon')});

</script>
<script type="text/javascript" src="js/thesixthroom.js"></script>

</html>