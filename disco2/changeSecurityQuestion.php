<!-- This page allows the user or the admin to change their secuirty question
     The page should be accessed from setting.php and should submit its data to submitChangeSecurityQuestion.php
     The page displays a form where the user can select a question and submit an answer to the question -->

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

	$("#sqForm").on('submit',(function(e) {
		e.preventDefault();
		$.ajax({
			url: "submitChangeSecurityQuestion.php", // Url to which the request is send
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
					$("#sqForm")[0].reset(); 
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
	    echo '<a class="navbar-brand" href="#">DISCO2</a>';
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


		//a table containing the questions the user can select and a textbox for the answer
		//the indexing of the questions is constant throughout the page so that confussion when verifying data doesn't arise
		//the index of each question will be stored in the data base after it is submitted to submitChangeSecurityQuestion.php
		//if the selection is 0, then the question is Unselected and the the next file will indicate so
		//table with the things the user has to input

		echo '<div class="card card-container">';
    	echo '<h2 class="login_title text-center">Change Security Question</h2>';
   	 	echo '<hr/>';

   	 	echo '<form class="form-signin" method="POST" id="sqForm" action="">';
		
	    echo '<p class="input_title">Security Question</p>';
		echo '<select name = "security_question" class="login_box" id = "security_question" >';

		//security questions. It is important that they are consistent through out the files
		$question = array();
		$question[0] = "Choose your security question";
		$question[1] = "In what city did you meet your spouse/significant other?";
		$question[2] = "Which phone number do you remember most from your childhood?";
		$question[3] = "What was your favorite place to visit as a child?"; 
		$question[4] = "What is the name of your favorite childhood friend?";
		$question[5] = "What was your childhood nickname?"; 
		$question[6] = "What is the name of your favorite pet?";
		$question[7] = "In what city or town did your mother and father meet?";
		$question[8] = "Where were you when you had your first kiss?"; 
		$question[9] = "In what city does your nearest sibling live?";
		$question[10] = "In what city or town was your first job?";
		$question[11] = "What is your mother's maiden name?"; 
		$question[12] = "What street did you grow up on?";
		$question[13] = "What was the make of your first car?";
		$question[14] = "What is the name of the place your wedding reception was held?";
		$question[15] = "What is the name of a college you applied to but didn't attend?";
		$question[16] = "What is your father's middle name?";
		$question[17] = "What is the name of your first grade teacher?";
		$question[18] = "What was your high school mascot?";
		$question[19] = "What was the name of your first stuffed animal?";

		//determine which selection is selected
		for($j=0;$j<count($question);$j++) {
				echo '<option value="'.$j.'"';
				if( array_key_exists('security_question', $_POST) && intval($_POST['security_question']) == $j ) {
					echo 'selected="selected" ';
				}
				echo '>'.$question[$j].'</option>';
		}

		echo '</select>';

		echo '<p class="input_title">Answer</p>';
	    echo '<input type="text" class="login_box" name = "security_question_answer" placeholder = "Enter the Security Question Answer" pattern=".{2,36}" size = "40"  maxlength = "36" required autofocus>';
		
	    
	    echo '<div id="message" class="error-text"></div>';
    	echo '<button class="btn btn-lg btn-primary btn-block" type="submit">Submit</button>';
	    echo '</form>';

		echo '<a href="settings.php" class="btn btn-lg btn-primary btn-block" role="button">Back</a>';	
	    
	    echo '</div>'; //card-container
	    echo '</div>'; // disco2-main

				
	}
	//if the user is not logged in, tell him to do so
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
