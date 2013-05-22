<?php
  $showForm = true;
  $showResult = false;
  $con=mysqli_connect("localhost","webapp","1l0ves1x","thesixthroom");

  if (mysqli_connect_errno($con))
  {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
  }
  
  if(isset($_POST['name'])){

    $sql="INSERT INTO guestbook_visitor (visit_date, name, city, state, country, comments)
          VALUES
          ('" . date('Y-m-d H:i:s', strtotime('today')) . "' ,'" . mysqli_real_escape_string($con, $_POST['name']) . "','$_POST[city]','$_POST[state]','$_POST[country]','$_POST[comments]')";

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


<?php if ($showForm) { ?>
  <form id="guestbook-form" class="form-horizontal" style="margin-top:40px">
    <div class="control-group">
      <label class="control-label" for="name">Name:</label>
      <div class="controls">
        <input type="text" id="name" name="name" placeholder="Your name">
      </div>
    </div>
    <div class="control-group">
      <label class="control-label" for="city">Where are you from?</label>
      <div class="controls">
         <input type="text" id="city" name="city" placeholder="City" style="margin-bottom:5px">
         <input type="text" id="state" name="state" placeholder="State/Province" style="margin-bottom:5px">
         <?php 
            include 'countries.php'; 
          ?>
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
  <div>Thanks for your info. You have now entered the Sixth Room network.</div>
<?php } ?>
<div id="guestbook-form-div"></div>

<script type="text/javascript">
    $('#sign-the-guestbook-submit').click(function(){
        
        $.ajax({
           url: 'guestbook_form.php' , 
           type: 'POST',
           data: $("#guestbook-form").serialize(),
           success: function(result){     
             $('#guestbook-form-div').html('<p>' + result + '</p>')      
           }
        });   
        return false;     
     });
    
</script>