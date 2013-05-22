<?php
  include 'continents.php'; 
  $showForm = true;
  $showResult = false;
  $con=mysqli_connect("localhost","webapp","1l0ves1x","thesixthroom");

  if (mysqli_connect_errno($con))
  {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
  }
  
  if(isset($_POST['name'])){

    $continent_abbreviation = $countries_to_continent_abbreviations[$_POST['country_abbreviation']];
    $continent = $continent_abbreviations_to_continents[$continent_abbreviation];
    $sql="INSERT INTO individual_visitors (visit_date, name, city, state, country_abbreviation, country, continent_abbreviation, continent, comments, venue)
          VALUES
          ('" . date('Y-m-d H:i:s', strtotime('today')) . "' ,'" . mysqli_real_escape_string($con, $_POST['name']) . "','$_POST[city]','$_POST[state]','$_POST[country_abbreviation]','$_POST[country]','$continent_abbreviation','$continent','$_POST[comments]', 'GUESTBOOK')";

    if (!mysqli_query($con,$sql))
    {
      die('Error: ' . mysqli_error($con));
    } 
    else{
      $showForm = false;
      $showResult = true;
    }
  }
?>

<div id="guestbook-form-div">
<?php if ($showForm) { ?>
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
         <input type="text" id="state" name="state" placeholder="State/Province" style="margin-bottom:5px">
         <?php 
            include 'countries.php'; 
          ?>
          <span class="help-inline" style="display:none">Please fill out city, state and country fields.</span>
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
        <button id="sign-the-guestbook-submit" type="button" class="btn btn-warning">Sign the guestbook</button>
      </div>
    </div>
    <!--<span class="help-block" style="margin-top:40px">Problems? Email <a href="mailto:dignazio@mit.edu">Catherine</a></span>-->
  </form>
<?php } ?>
<?php if ($showResult) { ?>
  <div class="result">Thanks for your info. You have now entered the Sixth Room network.</div>
<?php } ?>
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
        if ($('#city').val().length == 0 || $('#state').val().length == 0 || $('#country_abbreviation').val().length != 2){
          $('#location-group').addClass('warning');
          $('#location-group').find('span.help-inline').show();
          errors =true;
        } else{
          $('#location-group').removeClass('warning');
          $('#location-group').find('span.help-inline').hide();
        }
        if(!errors){
          $.ajax({
             url: 'guestbook_form.php' , 
             type: 'POST',
             data: $("#guestbook-form").serialize()+ "&country=" + $('#country_abbreviation').find(":selected").text(),
             success: function(result){     
               //$('#guestbook-form').hide();
               $('#guestbook-form-div').html('<p>' + result + '</p>')      
             }
          });   
        }
        return false;     
     });
    
</script>