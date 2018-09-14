<?php
//this where the php starts

//include the utilities file
include 'utilities.php';

		
$result = 1;//we set result to 1
$button_string = "";
$success = false;

//we are checking if all the $_POST variables were submited and if it was submitted using the right button from register.php
//for this case we store result = 2 because we might get errors if we try to repost data register.php
if( !array_key_exists('username', $_POST) ||
    !array_key_exists('fullname', $_POST) ||
    !array_key_exists('email', $_POST) ||
    !array_key_exists('security_question', $_POST) ||
    !array_key_exists('security_question_answer', $_POST) ) {
	$button_string = "The form was not submitted correctly!";
	$result = 2;
}

//we also check for cases when the format of the submitted data is wrong
//for these cases we display the problem and set the result to 0
else if( !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL) ) {
		$button_string = "Invalid email format"; 
		$result = 0;
}
else if( empty($_POST['security_question_answer']) ) {
	$button_string = "The answer field is empty!";
	$result = 0;
}
else if( strlen($_POST['security_question_answer']) > 64 ) {
	$button_string = "The answer can be no longer than 64 characters!";
	$result = 0;
}
else if( !is_numeric($_POST['security_question']) || intval($_POST['security_question']) < 0 || intval($_POST['security_question']) > 19) {
	$button_string = "The security question was not submitted correctly!";
	$result = 0;
}
else if( intval($_POST['security_question']) == 0  ){
	$button_string = "Please select a security question!";
	$result = 0;
}


//if the form is complete we go on and first run a query to check the username to see if it already exists
if($result == 1){
	
	//initialize connection variables
	$db_connection = null;
	
	//connect to the database and if the connection is successful
	if( createDatabaseConnection( $db_connection, $feedback ) ) {
		
		//run a sql query that selects the password hash for users with the posted registration code and for which the account was not activated
		$sql = 'SELECT user_name FROM users WHERE user_name = "'.$_POST['username'].'"';
		$query = $db_connection->prepare($sql);
		$query->execute();
		$username_exists = $query->fetchObject();
		$query = null;
		
		//if the username already exists, set the result to 0, let the user choose another username
		if($username_exists){
			$button_string = "This username already exists. Please choose another one.";
			$result = 0;
		}

		else{
			$temp_password = randomString(6);
			$hashedpw = password_hash($temp_password, PASSWORD_DEFAULT);
			if(array_key_exists('project_description', $_POST)){
				$description = $_POST['project_description'];
			}
			else{
				$description = '';
			}

			$sql = "INSERT INTO users VALUES(NULL, '".$_POST['fullname']."','".$_POST['username']."', 
				'".$hashedpw."', '".$_POST['email']."','".$_POST['security_question']."',
				'".$_POST['security_question_answer']."',1,0,0,1,'".$description."')";


			$query = $db_connection->prepare($sql);
			$query->execute();

			$sql = 'SELECT user_name FROM users WHERE user_name = "'.$_POST['username'].'"';
			$query = $db_connection->prepare($sql);
			$query->execute();
			$username_exists = $query->fetchObject();
			$query = null;
				

			if(!$username_exists){
				$button_string = "An error occurred while adding the new user to the database.";
				$result = 0;
			}
		}
	}

	//if an error occurred while connecting to the database, we display this and set the result to 0
	else{
		$button_string = "An error occurred while connecting to the data base!";
		$result = 0;

	}
	//clean up
	$db_connection = null;
	$query = null;

}
	
//if everything is right until this point, we run a query that adds the new user information to the database and send a temporary password to the user by email.
if($result == 1){
	//send an email to the user with his password
	sendRegistrationEmail($_POST['email'],$_POST['username'],$temp_password,$_POST['fullname']);

	$success = true;
	$button_string = "Your account has been successfully created! Your password has been emailed to you."

}
else{
	$success = false;
}	


echo json_encode(array("message" => $button_string, "success" => $success));


?>



