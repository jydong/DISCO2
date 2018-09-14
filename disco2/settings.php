<!-- This page displays the settings menu for an user or an admin
     The page should be accessed from index.php
     The page containg links to the things an user or an admin can manage about his account. For now: the security question and the password -->

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
	        echo '<li><a href="logout.php" class="btn btn-default" role="button">Logout</a></li>';
	      echo '</ul>';
	    echo '</div>'; //navbar-collapse 
	    echo '</div>'; //container-fluid 
	    echo '</nav>';


		echo '<br/>';
		echo '<div id="disco2-main">';

		echo '<div class="container">';

	    echo '<div class="card card-container">';
	    echo '<h2 class="login_title text-center">Settings</h2>';
	    echo '<hr>';

	    echo '<form action="changePassword.php">';
    	echo '<button class="btn btn-lg btn-primary btn-block" type="submit">Change Password</button>';
    	echo '</form>'; 

    	echo '<form action="changeSecurityQuestion.php">';
    	echo '<button class="btn btn-lg btn-primary btn-block" type="submit">Change Security Question</button>';
    	echo '</form>'; 

		echo '<form action="index.php">';
    	echo '<button class="btn btn-lg btn-primary btn-block" type="submit">Back</button>';
    	echo '</form>'; 

    
    	echo '</div>'; //card-container
    
    	echo '</div>'; //container
    	echo '</div>'; // disco2-main			
		
	}

	//otherwise, tell the user to log in
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
		echo '<input type="submit" value="You are not logged in! Click to login.">';
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
