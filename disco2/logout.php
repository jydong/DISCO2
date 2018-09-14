<!-- This page allows the user and the admin to logout 
     The page is accessible from index.php
     The file destroys the $_SESSION variables and displays whether the logout was successful or not -->


<!-- Header -->
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<!-- Latest compiled and minified CSS -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">

<!-- jQuery library -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>

<!-- Latest compiled JavaScript -->
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>

<!-- jquery redirect plug-in -->
<script src="jquery.redirect.js"></script>


<head>
<title>Home</title>
<link rel="stylesheet" type="text/css" href="disco2.css" title="Default" />
</head>


<!-- This where the body starts -->
<body>

<!-- Page header --> 


<?php
//this where the php starts

//include the utilities file
include 'utilities.php';

//if the minimum php requirements are passed
if(performMinimumRequirementsCheck()){
	
	//start the session
	startSession();

	//display the menu and the body that will say the logout was successful


	echo '<div id="disco2-body">';

	// echo '<h1>Logout</h1>';

	//if then we destory the session varibles 
	if(array_key_exists('user_is_logged_in',$_SESSION) && $_SESSION['user_is_logged_in']){

		//destroy the session
		$_SESSION = array();
		session_destroy();
	
		// echo '<form action = "index.php">';
		// echo '<input type = "submit", value = "You have logged out! Click to go back."/>';
		// echo '</form>';
		echo '<script type="text/javascript">';
		echo 'window.location.href = "index.php"';
		echo '</script>'; 
	}
	//if the user is already logged out, tell him so
	else{
		echo '<nav class="navbar navbar-default">';
	    echo '<div class="container-fluid">';

	    echo '<div class="navbar-header">';
	    echo '<a class="navbar-brand" href="#">DISCO2</a>';
	    echo '</div>';
	    echo '<div id="disco2-menu">';
	    echo '</div>';
	    echo '</div>';
	    echo '</nav>';
		echo '<form action = "index.php">';
		echo '<input type = "submit" value = "You already logged out! Click to go back."/>';
		echo '</form>';
	}

	//end the page

	echo '<hr/>';


	echo '<div id="disco2-tail">';
	echo '<p>Site developed by Colby College Computer Science Department.</p>';
	echo '</div>';

	echo '</div>';

	
}
//else, show that the php requirements are not met
else{
	echo '<p>Failed minimum requirements check</p>';
}

?>

</body>
</html>