
<?php
  if (isset($_GET['days']))
    $days = $_GET['days'];
  else 
    $days = 30;

  if (isset($_GET['model']))
    $model = $_GET['model'];
  else 
    $model = "time";

  $streamdataFilepath = "data/streamdata_" . $model . "_" . $days . ".csv";
  $networkdataFilepath = "data/networkdata_" . $model . "_" . $days . ".json";


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
  
  <div id="continent-visitors" class="visitor-label">YO YO YO</div>
  <div id="timeline-date" class="visitor-label"></div>
  <div id="guestbook-visitors" class="visitor-label"><span id="guestbook-visitor-count"></span> Guestbook Visitors</div>
  <div id="museum-visitors" class="visitor-label"><span id="museum-visitor-count"></span> US Pavilion Visitors</div>
  <div id="online-visitors"class="visitor-label"><span id="online-visitor-count"></span> Online Visitors</div>
  <h4 id="time-space"><a href="?model=space" id="space-button" role="button" data-toggle="modal">space</a>
    <a href="?model=time" role="button"  id="time-button" data-toggle="modal" class="selected">time</a></h4>
  <!-- Button to trigger modal -->
  <h4 id="about-button"><a href="#about-modal" role="button" data-toggle="modal">about</a></h4>
   
  <!-- Modal -->
  <div id="about-modal" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
      <h3 style="font-family: 'Codystar', cursive;">The Sixth Room</h3>
    </div>
    <div class="modal-body">
      <p>Bloomberg is supporting "The Sixth Room," a digital tool that visually represents the network of virtual and physical audiences visiting <a href="http://www.sarahszevenice2013.com">Sarah Sze’s Triple Point</a>, both in Venice at the United States Pavilion and online over the course of la Biennale di Venezia.  Constantly updated, "The Sixth Room" data represents an ever-changing visual record of public engagement with Triple Point. Coordinated by Nell Breyer, previously a research affiliate at MIT’s Center for Advanced Visual Studies and designed by <a href="http://www.kanarinka.com">Catherine D’Ignazio</a> from MIT's Media Lab, "The Sixth Room" is accessible online and on mobile devices.</p>
    </div>
    <div class="modal-footer">
      <button class="btn btn-info" data-dismiss="modal" aria-hidden="true">x</button>
    </div>
  </div>
</body>
<script type="text/javascript">
    var streamdataFilepath = "<?= $streamdataFilepath ?>";
    var networkdataFilepath = "<?= $networkdataFilepath ?>";
    var model = "<?= $model ?>";
    
</script>
<script src="js/thesixthroom.js"></script>

</html>