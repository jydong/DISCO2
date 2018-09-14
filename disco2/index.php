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

	$("#loginForm").on('submit',(function(e) {
		e.preventDefault();
		$.ajax({
			url: "login.php", // Url to which the request is send
			type: "POST",             // Type of request to be send, called as method
			data: new FormData(this), // Data sent to server, a set of key/value pairs (i.e. form fields and values)
			contentType: false,       // The content type used when sending data to the server.
			cache: false,             // To unable request pages to be cached
			processData:false,        // To send DOMDocument or non processed data file it is set to false
			dataType: "json",
			success: function(data){   // A function to be called if request succeeds	
				if(!data["success"]){	
					$('#loginMessage').html('<h5>'+data['message']+'</h5>');  	
				}
				else{
					if(data["firstTime"]){
						location.href = "changePassword.php";
					}
					else{
						location.reload();
					}
				}	
			}
		});
		return false;
	}))

	$("#uploadCSV").on('submit',(function(e) {
		e.preventDefault();
		$.ajax({
			url: "uploadFile.php", // Url to which the request is send
			type: "POST",             // Type of request to be send, called as method
			data: new FormData(this), // Data sent to server, a set of key/value pairs (i.e. form fields and values)
			contentType: false,       // The content type used when sending data to the server.
			cache: false,             // To unable request pages to be cached
			processData:false,        // To send DOMDocument or non processed data file it is set to false
			dataType: "json",
			success: function(data){   // A function to be called if request succeeds		
				if(data["success"]){
					$('#uploadedFiles').append($("<option/>", {value:data["filename"], text:data["filename"]}));
					$('#uploadMessage').html('<h5>'+data['message']+'</h5>');  	
				}
				else{
					$('#uploadMessage').html('<h5>'+data['message']+'</h5>');  	
				}
			}
		});
		return false;
	}))




	$("#selectFile").submit(function(e){
		$("#header-table").fadeOut();
		$("#cancelTypeSelection").fadeOut();
        $("#confirmTypeSelection").fadeOut();

		e.preventDefault();
	   	$.ajax({
	    	type: "POST",
      		url: "updateCurrentFilename.php",
      		data: $(this).serialize(), 
	     	success: function(){
	     		location.reload();  		
	    	}
	   	});
		return false;
	})

	$("#showHeaderTable").click(function(e){
		e.preventDefault();
        $("#header-table").fadeToggle();
        $("#cancelTypeSelection").fadeToggle();
        $("#confirmTypeSelection").fadeToggle();			
    })


	$("#cancelTypeSelection").click(function(){
        $("#header-table").fadeOut();
        $("#cancelTypeSelection").fadeOut();
        $("#confirmTypeSelection").fadeOut();
    })

    $("#confirmTypeSelection").click(function(e){
    	e.preventDefault();
        $("#header-table").fadeOut();
        $("#cancelTypeSelection").fadeOut();
        $("#confirmTypeSelection").fadeOut();
        
       	$.ajax({
	    	type: "POST",
      		url: "selectHeaderType.php",
      		data: $("#selectType").serialize(),
      		cache: false,  
	     	success: function(){
	     		alert("Header types have been updated!");

	    	}
	   	});
		return false;
    });



});



</script>
	
</head>


<body>


<?php

// disable all warning messages 
// there is a warning regarding session (ajax form submitting) !!!!
error_reporting(E_ERROR | E_PARSE);

//include the utilities file
include 'utilities.php';



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
	        echo '<li><a href="logout.php" class="btn btn-info" role="button">Logout</a></li>';
	
	    echo '</div>'; //container-fluid 
	    echo '</nav>';
	
		echo '<br/>';
		
		echo '<div class="disco2-main">';
		echo '<div class="card card-left-container">';

		echo '<h3>Current File:</h3>';
		echo '<div id="currentFile">';
		if(isset($_SESSION['current_file'])){
			echo '<h3>'.$_SESSION['current_file'].'</h3>';
		}
		else{
			echo '<h3>'.'Not Selected'.'</h3>';
		}
		echo "<hr/>";
		echo '</div>';


		// display all the csv file names from the user folder in a dropdown menu list 
		// the "Use this File" button submits the selection to index.php
		// the selected file is stored as $_SESSION['current_file']
		echo '<form id="selectFile" method = "POST" action = "">';	
		echo '<h4>Select a CSV file</h4>';
		echo '<select class="login_box" name = "uploadedFiles" id = "uploadedFiles">';
		$dir = "users/".$_SESSION['user_name']."/";
		// $subfolders = array_map("htmlspecialchars", scandir($dir));
		$subfolders = scandir($dir, SCANDIR_SORT_NONE);
		$subfolders = array_slice($subfolders,2); //drop the first two: current and parent of current
		foreach ($subfolders as $sub){
			if( is_dir($dir.$sub) ){
				echo "<option value='$sub.csv'>$sub.csv</option>";
			}
		}
		echo '</select>';
		echo '<button class="btn btn-lg btn-info btn-control" type="submit" name = "change_file">OK</button>';
		echo '</form>';
		echo "<hr/>";



		// allow the user to select a file and upload it, submit the selection to uploadFile.php
		echo '<form id="uploadCSV" action="" method="post" enctype="multipart/form-data">';
		echo '<input type="file" name="file" id="file" required />';
		echo '<input type="hidden" name = "user_name" value = "'.$_SESSION['user_name'].'"/>';
		echo '<br/>';
		echo '<div id="uploadMessage" class="error-text"></div>';
		echo '<button class="btn btn-lg btn-info btn-control" type="submit">Upload</button>';	
		echo '</form>';
		echo "<hr/>";

		// show buttons associated with header table if current_file exists in $_SESSION
		if(isset($_SESSION["current_file"])){
			echo '<button id="showHeaderTable" class="btn btn-lg btn-info btn-control" type="submit">Select Header Types</button>';

			echo '<br/>';	
			
			echo '<button id="cancelTypeSelection" class="btn btn-warning btn-toShow-left" type="submit" name="Cancel">Cancel</button>';	
			echo '<button id="confirmTypeSelection" class="btn btn-success btn-toShow-right" type="submit" name="OK">OK</button>';
		}


		echo '</div>'; // end of card-left-container 


		echo '<div class="card card-right-container" id="header-table">';

		if(isset($_SESSION["current_file"])){
			
			echo '<table class="table table-hover">';
			echo '<caption><h3>Select Header Types<h3></caption>';

			echo '<thead>';
			echo '<tr>';
			echo '<th>Header</th>';
			echo '<th>Type</th>';
			echo '</tr>';

			$typeFilename = basename($_SESSION['current_file'],'.csv').'_type.csv';
	   		$subfolder = "users/".$_SESSION['user_name']."/".basename($_SESSION['current_file'],'.csv')."/";
			$typeFile = fopen($subfolder.$typeFilename,"r");
			fgetcsv($typeFile); //skip the first line of headers
			$dataTypes = fgetcsv($typeFile); //get an array of types from the file
			fclose($typeFile);


			echo '<form name="selectType" id ="selectType">';
			for ($x = 0; $x < count($_SESSION["headers"]); $x++) {
				echo '<tr>';
				echo '<td>'.$_SESSION["headers"][$x].'</td>';
				echo '<td>';
				echo '<select name="'.$_SESSION["headers"][$x].'"_type">';
				$allTypes = array("number", "string", "date", "enum");

				foreach ($allTypes as $type){
					if($type == $dataTypes[$x]){
		    			echo "<option value='$type' selected='selected'>$type</option>";
		    		}
		    		else{
		    			echo "<option value='$type'>$type</option>";
		    		}
				}
				echo '</select>';
				echo '</td>';
				echo '</tr>';
			}; 
			echo '</form>';


			echo '</thead>';
			echo '</table>';

		}

	   
		echo '</div>'; // end of card card-right-container
		

		// add a div to clear floating
		echo '<div id="clear">'; 
		echo '</div>';

		echo '</div>'; // disco2-main

		
	}

	//else, if the user is not logged in, show the login from
	else{
		
	    echo '<nav class="navbar navbar-default">';
	    echo '<div class="container-fluid">';

	    echo '<div class="navbar-header">';
	    echo '<a class="navbar-brand" href="indx.php">DISCO2</a>';
	    echo '</div>';
	    echo '<div id="disco2-menu">';
	    echo '</div>';
	    echo '</div>';
	    echo '</nav>';

		echo '<div id="disco2-main">';
	    echo '<h1 class="welcome text-center">Welcome to <br> DISCO2</h1>';
	    echo '<div class="card card-container">';
	    echo '<h2 class="login_title text-center">Login</h2>';
	    echo '<hr>';

	    
	    echo '<form class="form-signin" method="POST" id="loginForm" action="">';
	    echo '<span id="reauth-email" class="reauth-email"></span>';
	    echo '<p class="input_title">Username</p>';
	    echo '<input type="text" id="inputEmail" class="login_box" maxlength="32" pattern = "[a-zA-z0-9]{4,36}" name="user_name" required autofocus>';
	    echo '<p class="input_title">Password</p>';
	    echo '<input type="password" id="inputPassword" class="login_box" maxlength="32" patern = ".{6,36}" name="user_password" required>';
	    
	    // echo '<div id="remember" class="checkbox">';
	    // echo '<input type="checkbox" tabindex="3" class="" name="remember" id="remember">';
	    // echo '<label for="remember"> Remember Me</label>';
	    // echo '</div>';

	    echo '<a href="recoverPassword.php" name="recover" class="forgot-password">Forgot Password?</a>';
	    echo '<hr/>';

	    echo '<div id="loginMessage" class="error-text"></div>';
	    
	    echo '<button class="btn btn-lg btn-primary" type="submit" name="login">Login</button>';
	    echo '</form>';

	    echo '<form class="form-signin" method="POST" name="register" action="register.php">';
	    echo '<button class="btn btn-lg btn-primary" type="submit" name="register">Sign Up</button>';
	    echo '</form>';
	    
	    echo '</div>'; //card-container
	    echo '</div>'; // disco2-main
	    


	}


	//end the page
	echo '<hr/>';

	echo '<div id="disco2-tail">';
	echo '<p>Site developed by Colby College Computer Science Department.</p>';
	echo '</div>';

}

//else, if the minimum requirements are failed, show that this has happened
else{
	echo '<p>Failed minimum requirements check</p>';
}


?>

</body>
</html>



