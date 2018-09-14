<!-- This page allows the admins to view the information about a selected user
     The page is accessible from users.php.
     The file displays data about a user in a table after running a query through the users data base that retrieves the username obtained from users.php -->

<!-- Header -->
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
<title>DISCO2-USERS</title>

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
	        echo '<li><a href="settings.php">Settings</a></li>';
	        if($_SESSION['user_admin_flag']){
	          echo '<li class="active"><a href="users.php">Users<span class="sr-only">(current)</span></a></li>';
	        }
	        echo '<li><a href="logout.php" class="btn btn-info" role="button">Logout</button></a></li>';
	      echo '</ul>';
	    echo '</div>'; //navbar-collapse 
	    echo '</div>'; //container-fluid 
	    echo '</nav>';


		$result = 1;
		$button_string ='';

		//unless we don't have the right keys in the $_GET array or if the username doesn't have the right format
		if(array_key_exists('username',$_GET) && strlen($_GET['username'])>36 && strlen($_GET['username'])<4 ){
			$button_string ='The username format is not valid!';
			$result = 0;
		}
			
		//initialize connection variables
		$db_connection = null;

		//if we can connect to the users database
		if($result && createDatabaseConnection($db_connection,$feedback)){
		
			//select information about the user whose username cooresponds to the one offered from the previous window
			$sql = 'SELECT * FROM users WHERE user_name = "'.$_GET['username'].'"' ;

			//execute the query				
			$query = $db_connection->query($sql);
			$user = $query->fetchObject();

			//if no user was found, then something went wrong
			//we display this and set the result to 0
			if(!$user){
				echo '<form action="users.php">';
				echo '<input type="submit" value="The username is wrong or you are not allowed to see this user! Go Back!">';
				echo '</form>';
			}
			//if we find an user with this name we will display his data
			else if($result && $user){
				if(isset($_POST['type_changed_to_admin'])){
					$sql = 'UPDATE users SET user_admin_flag = 1,user_regular_flag = 0
				            WHERE user_name = "'.$_GET['username'].'"' ;			
					$query = $db_connection->query($sql);
				}
				else if(isset($_POST['type_changed_to_regular'])){
					$sql = 'UPDATE users SET user_admin_flag = 0,user_regular_flag = 1
				            WHERE user_name = "'.$_GET['username'].'"' ;			
					$query = $db_connection->query($sql);
				}

				if(isset($_POST['account_activated'])){
					$sql = 'UPDATE users SET user_active_account_flag = 1
				            WHERE user_name = "'.$_GET['username'].'"' ;			
					$query = $db_connection->query($sql);
				}
				else if(isset($_POST['account_disabled'])){
					$sql = 'UPDATE users SET user_active_account_flag = 0
				            WHERE user_name = "'.$_GET['username'].'"' ;			
					$query = $db_connection->query($sql);
				}

				// get the user again in case user type or account status has been modified 
				$sql = 'SELECT * FROM users WHERE user_name = "'.$_GET['username'].'"' ;		
				$query = $db_connection->query($sql);
				$user = $query->fetchObject();

				echo '<div id="disco2-main">';
				echo '<div id = "disco2-report">';

				echo '<table class="table table-hover">';
				echo '<caption><h3>'.$user->user_name."'s Information</h3></caption>";

				//echo '<thead><tr><th colspan = "2">'.$user->user_name."'s Data</th></tr></thead>";
				
				echo '<tr>';
				echo '<td width="30%">Username</td>';
				echo '<td width="70%">'.$user->user_name.'</td>';
				echo '</tr>';
				
				echo '<tr>';
				echo '<td>Full name</td>';
				echo '<td>'.$user->user_fullname.'</td>';
				echo '</tr>';

				echo '<tr>';
				echo '<td>Email</td>';
				echo '<td>'.$user->user_email.'</td>';
				echo '</tr>';

				
				
				echo '<tr>';
				echo '<td>User Type</td>';
				if($user->user_admin_flag == 1)
					echo '<td>Administrator</td>';
				else if($user->user_regular_flag == 1)
					echo '<td>Regular User</td>';
				echo '</tr>';

				
				echo '<tr>';
				echo '<td>Account Status</td>';
				if($user->user_active_account_flag == 1)
					echo '<td>Active</td>';
				else
					echo '<td>Inactive</td>';
				echo '</tr>';	

				echo '<tr>';
				echo '<td>Project Description</td>';
				echo '<td>'.$user->project_description.'</td>';
				echo '</tr>';

				echo '</table>';


				echo '<form method = "POST" action = "viewUser.php?username='.$user->user_name.'">';

				if($user->user_active_account_flag == 0){
					echo '<input type="submit" class="btn btn-info" value = "Activate Account" name = "account_activated">';
					
				}
				else{
					echo '<input type="submit" class="btn btn-info" value = "Disable Account" name = "account_disabled">';
					
				}

				if($user->user_admin_flag == 1){
					echo '<input type="submit" class="btn btn-info" value = "Change User Type to Regular" name = "type_changed_to_regular">';
				}
				else if($user->user_regular_flag == 1){
					echo '<input type="submit" class="btn btn-info" value = "Change User Type to Administrator" name = "type_changed_to_admin">';
				}

				echo '</form>';
				

				echo '<form action = "users.php">';
				echo '<input type = "submit" class="btn btn-primary" value = "Back">';
				echo '</form>';

				echo '</div>';
				echo '</div>';

			}
			//we already posted want went wrong, now we add some control buttons
			else{
				echo '<form action="users.php">';
				echo '<input type="submit" value="'.$button_string.' Go back!"/>';
				echo '</form>';
			}

			//clean up
			$query = null;
			$db_connection = null;	
			
		}
		//if the connection to the database is unsuccessful
		else{
			echo '<form action="users.php">';
			echo '<input type="submit" value="An error occured while connecting to the database! Try again!">';
			echo '</form>';
		}
	}
	//if the user is not logged in with a right account
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
		echo '<input type="submit" value="You are not allowed to access the page! Go Home!">';
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


</div>
</body>
</html>