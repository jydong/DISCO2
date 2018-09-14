<!-- This page allows the admins to see a table to user information
     The page is accessible from index.php and contains links to pages where information about each user can be seen
     The file runs a query that displays all the users in the database in alphabetical orders and links with their username posted to viewUser.php -->


<!-- Header -->

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



		//prepare stuff for connecting to the database
		$db_connection = null;
		$feedback = '';

		//create the connection and if the connection is successful
		if(createDatabaseConnection($db_connection,$feedback)){
			
			//sql query that retrieves users that are not admins and orders them by usernames

			$sql = 'SELECT * FROM users ORDER BY user_regular_flag,user_name';

			//execute the query and get the results
			$query = $db_connection->prepare($sql);
			$query->execute();
			$users = $query->fetchAll();		
			
			//get the size of the result array
			$size = count($users);

			echo '<div id="disco2-main">';
			echo '<div id = "disco2-report">';

			//if there are no users then display an empty table
			if(!$size){

				echo '<table class="table table-hover">';

				echo '<caption><h3>Users</h3></caption>';

				echo '<thead>';
				echo '<tr>';
				echo '<th>Username</th>';
				echo '<th>Full name</th>';
				echo '<th>User Type</th>';
				echo '<th>User Status</th>';
				echo '</tr>';
				echo '</thead>';

				echo '</table>';
			}
			//otherwise diplay a link for each user to a page where we can view the user's data
			//the link will place the username in a $_GET variable
			else{
				//start the table of users
				echo '<table class="table table-hover">';
				echo '<caption><h3>Users</h3></caption>';

				echo '<thead>';
				echo '<tr>';
				echo '<th>Username</th>';
				echo '<th>Full name</th>';
				echo '<th>User Type</th>';
				echo '<th>Account Status</th>';
				echo '</tr>';
				echo '</thead>';

				//and create links for each user
				for($i=0;$i<$size;$i++){

					$user = $users[$i];

					echo '<tr id="user" onclick="window.location.href = `viewUser.php?username='.$user['user_name'].'`;">';

					echo '<td>'.$user['user_name'].'</td>';
					echo '<td>'.$user['user_fullname'].'</td>';

					if($user['user_admin_flag'] == 1){
						echo '<td>Administrator</td>';
					}
					else if($user['user_regular_flag'] == 1){
						echo '<td>Regular user</td>';
					}

					if($user['user_active_account_flag'] == 1){
						echo '<td>Active</td>';
					}
					else{
						echo '<td>Inactive</td>';
					}
					echo '</tr>';
				}
				echo '</table>';
			}

			echo '</div>'; //disco2-report
			echo '</div>'; //disco2-main

		}
		//in case some error occured while connecting to the database
		else{
			echo '<form action="index.php">';
			echo '<input type="submit" value="An error occured while connecting to the database: '.$feedback.'! Try again!">';
			echo '</form>';
		}

	}
	//in case the user doesn't have the right account
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