<!-- This file allows the user or the admin to change his password
     The file should be accessed from settings.php and it should then submit data to submitChangePassword.php
     The page displays a form with the required data to change the password -->


<!-- Header -->
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
<title>DISCO2-SETTINGS</title>

<!-- Latest compiled and minified CSS -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">

<!-- jQuery library -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>

<!-- Latest compiled JavaScript -->
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
<!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
<!--[if lt IE 9]>
  <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
  <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
<![endif]-->

<link href="https://fonts.googleapis.com/css?family=Montserrat" rel="stylesheet">
<link rel="stylesheet" type="text/css" href="disco2.css" title="Default" />

<script type="text/javascript">
$(document).ready(function (e) {

	$("#passwordForm").on('submit',(function(e) {
		e.preventDefault();
		$.ajax({
			url: "submitChangePassword.php", // Url to which the request is send
			type: "POST",             // Type of request to be send, called as method
			data: new FormData(this), // Data sent to server, a set of key/value pairs (i.e. form fields and values)
			contentType: false,       // The content type used when sending data to the server.
			cache: false,             // To unable request pages to be cached
			processData:false,        // To send DOMDocument or non processed data file it is set to false
			dataType: "json",
			success: function(data){   // A function to be called if request succeeds	
				if(data["success"]){						
					alert(data['message']);
					location.href = "index.php";	
				}
				else{
					$("#passwordForm")[0].reset(); 
					$('#message').html('<h5>'+data['message']+'</h5>');  
				}	
			},
  			error: function(xhr, textStatus, error){
      			console.log(xhr.statusText);
      			console.log(textStatus);
      			console.log(error);
  			}
		});
		return false;
	}))
});
</script>
</head>


<!-- The body starts here -->
<body>


<?php
//this where the php starts

//include the utilities file
include 'utilities.php';

//if the minimum php requirements are passed
if(performMinimumRequirementsCheck()){
	
	//start the session
	startSession();

	//if the user is logged in
	if(array_key_exists('user_is_logged_in',$_SESSION) && $_SESSION['user_is_logged_in']){
		
		echo '<nav class="navbar navbar-default">';
	    echo '<div class="container-fluid">';

	    echo '<div class="navbar-header">';
	    echo '<a class="navbar-brand" href="index.php">DISCO2</a>';
	    echo '</div>';

	    // Collect the nav links, forms, and other content for toggling
	    echo '<div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">';
	      echo '<ul class="nav navbar-nav">';
	        echo '<li><a href="index.php">Home<span class="sr-only">(current)</span></a></li>';
	        echo '<li><a href="visualization.php">Visualization</a></li>';
	        echo '<li><a href="analysis.php">Analysis</a></li>';
	      echo '</ul>';

	      echo '<ul class="nav navbar-nav navbar-right">';
	        echo '<li class="active"><a href="settings.php">Settings<span class="sr-only">(current)</span></a></li>';
	        if($_SESSION['user_admin_flag']){
	          echo '<li><a href="users.php">Users</a></li>';
	        }
	        echo '<li><a href="logout.php" class="btn btn-info" role="button">Logout</a></li>';
	      echo '</ul>';
	    echo '</div>'; //navbar-collapse 
	    echo '</div>'; //container-fluid 
	    echo '</nav>';
	    echo '<br/>';

	    echo '<div id="disco2-main">';
		
		//display a table where the user can input his old password and his new password choice
		
		echo '<div class="card card-container">';
	    echo '<h2 class="login_title text-center">Change Password</h2>';
	    echo '<hr/>';
    	
	    echo '<form class="form-signin" method="POST" id="passwordForm" name="passwordForm" action="">';
	    echo '</br><h4>Passwords must have between 6 and 32 characters.</h4>';
	    echo '<span id="reauth-email" class="reauth-email"></span>';


		if ($_SESSION['user_force_password_reset'] == 0){
			echo '<p class="input_title">Old Password</p>';
	    	echo '<input type="password" id="old_password" class="login_box" maxlength="32" pattern = ".{6,32}" name="old_password" required>';
		}
		
		echo '<p class="input_title">New Password</p>';
	    echo '<input type="password" id="new_password" class="login_box" maxlength="32" pattern = ".{6,32}" name="new_password" required>';

	    echo '<p class="input_title">Confirm New Password</p>';
	    echo '<input type="password" id="new_password_repeat" class="login_box" maxlength="32" pattern = ".{6,32}" name="new_password_repeat" required>';


		//buttons for either submitting the form or for going back
		//the button that submits the form to submitChangePassword.php has a name which will be checked in that file
		echo '<div id="message" class="error-text"></div>';
	    echo '<button class="btn btn-lg btn-primary btn-block" type="submit">Submit</button>';
	    
	    echo '</form>';


		if ($_SESSION['user_force_password_reset'] == 0){
			echo '<a href="settings.php" class="btn btn-lg btn-primary btn-block" role="button">Back</a>';
		}
	    
	    echo '</div>'; //card-container
	    echo '</div>'; // disco2-main
				
	}
	//if the user is not logged in, send him out to the main page
	else{

	    echo '<nav class="navbar navbar-default">';
	    echo '<div class="container-fluid">';

	    echo '<div class="navbar-header">';
	    echo '<a class="navbar-brand" href="index.php">DISCO2</a>';
	    echo '</div>';
	    echo '</div>';
	    echo '</nav>';
	    echo '<br/>';

		echo '<form action="index.php">';
		echo '<input type="submit" value="You are not logged in. Click to login.">';
		echo '</form>';
	}

	//end displaying the page
	echo '<hr/>';
	
	echo '<div id="disco2-tail">';
	echo '<p>Site developed by Colby College Computer Science Department.</p>';
	echo '</div>';
	
}
//if the requirements are not met, just display something
else{
	echo '<p>Failed minimum requirements check</p>';
}

?>

</body>
</html>


