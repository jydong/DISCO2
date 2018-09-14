<!-- This page allows an user or an admin to recover his password in case he forgot he forgot it
     The page is accessible from index.php and submits the data to submitRecoverPassword.php
     The page displays a table with the data that has to be entered in order to recover the password, all this data will be submitted to the next file -->

<!-- Header -->
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
<title>DISCO2</title>

<!-- Latest compiled and minified CSS -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">

<!-- jQuery library -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>

<!-- Latest compiled JavaScript -->
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>

<!-- jquery redirect plug-in -->
<script src="jquery.redirect.js"></script>

<link href="https://fonts.googleapis.com/css?family=Montserrat" rel="stylesheet">
<link rel="stylesheet" type="text/css" href="disco2.css" title="Default" />


<script type="text/javascript">
$(document).ready(function (e) {

	$("#recoverForm").on('submit',(function(e) {
		e.preventDefault();
		$.ajax({
			url: "submitRecoverPassword.php", // Url to which the request is send
			type: "POST",             // Type of request to be send, called as method
			data: new FormData(this), // Data sent to server, a set of key/value pairs (i.e. form fields and values)
			contentType: false,       // The content type used when sending data to the server.
			cache: false,             // To unable request pages to be cached
			processData:false,        // To send DOMDocument or non processed data file it is set to false
			dataType: "json",
			success: function(data){   // A function to be called if request succeeds	
				if(data["success"]){						
					$.redirect("recoverPasswordSQ.php",{username: data['username'], email: data['email'], fullname: data['fullname']}); 
				}
				else{
					$("#recoverForm")[0].reset(); 
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


<!-- This is where the body starts -->
<body>

<!-- Page Header -->

<?php
//this where the php starts

//include the utilities file
include 'utilities.php';

//if the minimum php requirements are passed
if(performMinimumRequirementsCheck()){
	
	//start the session
	startSession();

	//if the user is logged in, we go to the home page
	if(array_key_exists('user_is_logged_in',$_SESSION) && $_SESSION['user_is_logged_in']){
		

	    echo '<nav class="navbar navbar-default">';
	    echo '<div class="container-fluid">';

	    echo '<div class="navbar-header">';
	    echo '<a class="navbar-brand" href="index.php">DISCO2</a>';
	    echo '</div>';

	    // Collect the nav links, forms, and other content for toggling
	    echo '<div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">';
	      echo '<ul class="nav navbar-nav">';
	        echo '<li class="active"><a href="index.php">Home<span class="sr-only">(current)</span></a></li>';
	        echo '<li><a href="visualization.php">Visualization</a></li>';
	        echo '<li><a href="analysis.php">Analysis</a></li>';
	      echo '</ul>';

	      echo '<ul class="nav navbar-nav navbar-right">';
	        echo '<li><a href="settings.php">Settings</a></li>';
	        if($_SESSION['user_admin_flag']){
	          echo '<li><a href="users.php">Users</a></li>';
	        }
	        echo '<li><button type="submit" class="btn btn-default">Logout</button></li>';
	      echo '</ul>';
	    echo '</div>'; //navbar-collapse 

	    echo '</div>'; //container-fluid 
	    echo '</nav>';

	}



	//if the user is not logged in then we can display the form for recovering the password
	if(!array_key_exists('user_is_logged_in',$_SESSION) || !$_SESSION['user_is_logged_in']){
		
		echo '<nav class="navbar navbar-default">';
	    echo '<div class="container-fluid">';

	    echo '<div class="navbar-header">';
	    echo '<a class="navbar-brand" href="#">DISCO2</a>';
	    echo '</div>';

	    echo '</div>';
	    echo '</nav>';

	    echo '<br/>';

		echo '<div id="disco2-main">';
			
    	echo '<div class="card card-container">';
    	echo '<h2 class="login_title text-center">Password Recovery</h2>';
   	 	echo '<hr>';
		
		echo '<form class="form-signin" method="POST" id="recoverForm" action="">';

	    echo '<span id="reauth-email" class="reauth-email"></span>';
	    echo '<p class="input_title">Username</p>';
	    echo '<input type="text" id="username" class="login_box" size = "40" pattern="[a-zA-Z0-9]{2,36}" title="Your username must be between 2 to 36 characters.Username must contain only a-z, A-Z, or 0-9." maxlength = "36" name="username" required autofocus>';

	    echo '<p class="input_title">Fullname</p>';
	    echo '<input type="text" id="fullname" class="login_box" size = "40" pattern="[a-zA-Z\s]{2,36}" title="Your fullname must be between 2 to 36 characters. Letters only."  maxlength = "36" name="fullname" required autofocus>';

	    echo '<p class="input_title">Email</p>';
	    echo '<input type="email" id="email" class="login_box" size = "40" name="email" required autofocus>';

	    echo '<div id="message" class="error-text"></div>';

		echo '<button class="btn btn-lg btn-primary" type="submit" name="recover">Recover Password</button>';
		echo '</form>';

		//add a button back to home
	    echo '<form class="form-signin" method="POST" action="index.php">';
		echo '<button class="btn btn-lg btn-primary" type="submit" >Back</button>';
		echo '</form>';

		echo '</div>';
		echo '</div>'; // disco2-main

	}
	//if the user is already logged in, tell him so
	else{
		echo '<nav class="navbar navbar-default">';
	    echo '<div class="container-fluid">';

	    echo '<div class="navbar-header">';
	    echo '<a class="navbar-brand" href="index.php">DISCO2</a>';
	    echo '</div>';
	    echo '</div>';
	    echo '</nav>';
	    echo '<br/>';
		echo '<form action = "index.php">';
		echo '<input type = "submit" value = "You already logged in! You can change the password by going to settings!">';
		echo '</form>';
	}

	//end displaying the page
	echo '<hr/>';
	
	echo '<div id="disco2-tail">';
	echo '<p>Site developed by Colby College Computer Science Department.</p>';
	echo '</div>';

}
	

//else, show that the php requirements are not met
else{
	echo '<p>Failed minimum requirements check</p>';
}

?>

</body>
</html>

