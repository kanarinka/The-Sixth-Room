<?php
  include 'continents.php';
  include 'config.php'; 

  $con=mysqli_connect($DB_HOST,$DB_USER,$DB_PWD,$DB_NAME);

  if (mysqli_connect_errno($con))
  {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
  }
  
  if(isset($_POST['name'])){

    $continent_abbreviation = $countries_to_continent_abbreviations[$_POST['country_abbreviation']];
    $continent = $continent_abbreviations_to_continents[$continent_abbreviation];
    $visited_us_pavilion = isset($_POST['visited_us_pavilion']) &&  $_POST['visited_us_pavilion'] == "1";
    $visited_online_tour = isset($_POST['visited_online_tour']) &&  $_POST['visited_online_tour'] == "1";
    $sql="INSERT INTO individual_visitors (visit_date, name, city, country_abbreviation, country, continent_abbreviation, continent, venue, visited_us_pavilion, visited_online_tour)
          VALUES
          ('" . date('Y-m-d H:i:s', strtotime('now')) . "' ,'" . mysqli_real_escape_string($con, $_POST['name']) . "','$_POST[city]','$_POST[country_abbreviation]','$_POST[country]','$continent_abbreviation','$continent', 'GUESTBOOK','$visited_us_pavilion','$visited_online_tour')";

    if (!mysqli_query($con,$sql))
    {
      die('Error: ' . mysqli_error($con));
    } 

  }
?>

<div id="guestbook-form-div">

  <form id="guestbook-form" class="form-horizontal" style="margin-top:40px">
    <div class="control-group" id="name-group">
      <label class="control-label" for="name">Name:</label>
      <div class="controls">
        <input type="text" id="name" name="name" placeholder="Your name">
        <span class="help-inline" style="display:none">Please fill out your name.</span>
      </div>
    </div>
    <div class="control-group" id="location-group">
      <label class="control-label" for="city">Where are you from?</label>
      <div class="controls">
         <input type="text" id="city" name="city" placeholder="City" style="margin-bottom:5px">
         <?php 
            include 'countries.php'; 
          ?>
          <span class="help-inline" style="display:none">Please fill out city and country fields.</span>
      </div>
    </div>
    <div class="control-group">
      <div class="controls">
        <label class="checkbox">
          <input type="checkbox" name="visited_us_pavilion" value="1"> Did you visit the US Pavilion?
        </label>
        <label class="checkbox">
          <input type="checkbox" name="visited_online_tour" value="1"> Did you visit the <a target="_blank" href="http://www.sarahszevenice2013.com/the-exhibition/virtual-tour">Online Tour</a>?
        </label>
        
      </div>
    </div>
    <div class="control-group">
      <div class="controls"> 
        <button id="sign-the-guestbook-submit" type="button" class="btn btn-warning">Sign the guestbook</button>
      </div>
    </div>
    
  </form>

  <div id="guestbook-result" style="display:none">Thanks for your info. You have now entered the Sixth Room network.</div>

</div>
<script type="text/javascript">
    $('#sign-the-guestbook-submit').click(function(){
        var errors = false;
        if ($('#name').val().length == 0 ){
          $('#name-group').addClass('warning');
          $('#name-group').find('span.help-inline').show();
          errors=true;
        } else{
          $('#name-group').removeClass('warning');
          $('#name-group').find('span.help-inline').hide();
        }
        if ($('#city').val().length == 0 || $('#country_abbreviation').val().length != 2){
          $('#location-group').addClass('warning');
          $('#location-group').find('span.help-inline').show();
          errors =true;
        } else{
          $('#location-group').removeClass('warning');
          $('#location-group').find('span.help-inline').hide();
        }
        if(!errors){
          $.ajax({
             url: 'http://thesixthroom.org/includes/guestbook_form.php' , 
             type: 'POST',
             data: $("#guestbook-form").serialize()+ "&country=" + $('#country_abbreviation').find(":selected").text(),
             success: function(result){     
               $('#guestbook-form').hide();
               $('#result').show();
               $('#guestbook-modal').modal('hide');
             
             }
          });   
        }
        return false;     
     });
    
</script>