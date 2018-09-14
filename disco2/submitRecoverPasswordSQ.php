<?php
//this where the php starts

//include the utilities file
include 'utilities.php';
		
$result = 1;//we set result to 1
$button_string = "";

//we check if we have the right keys in the $_POST array
//because we will repost data in changePassword.php if something went wrong, we set 
//result to 2 for this case because if we are missing varaibles, reposting the data might throw errors
if( !array_key_exists('username', $_POST) ||
    !array_key_exists('security_question_answer', $_POST)||
    !array_key_exists('fullname', $_POST) ||
    !array_key_exists('email', $_POST)) {
	$button_string = "The form was not submitted correctly!";
	$result = 2;
}

//now we are going to check if the format of the varaibles is correct and if something is wrong
        //we display what went wrong and set result 0 
elseif( empty($_POST['username']) ) {
	$button_string = "Username field is empty!";
	$result = 0;
}
elseif( strlen($_POST['username']) < 2 || strlen($_POST['username']) > 36 ) {
	$button_string = "Username must be between 2 and 36 characters!";
	$result = 0;
}
elseif( !preg_match('/^[a-z\d]{4,36}$/i', $_POST['username']) ) {
	$button_string = "Username must contain only a-z, A-Z, or 0-9!";
	$result = 0;
}
elseif( !array_key_exists('security_question_answer', $_POST)){
	$button_string = "The form was not submitted correctly!";
	$result = 2;
}

elseif( empty($_POST['security_question_answer']) ) {
	$button_string = "The answer field is empty!";
	$result = 0;
}
elseif( strlen($_POST['security_question_answer']) > 32 ) {
	$button_string = "The answer can be no longer than 32 characters!";
	$result = 0;
}

//if the form was submitted correctly and the format of the data is correct
if($result == 1){
	
	//initialize connection variables
	$db_connection = null;
	
	//connect to the database and if the connection is successful
	if( createDatabaseConnection($db_connection, $feedback ) ) {
		
		//sql query that selects certain data about the user where the username is equal to the one submitted
		$sql = 'SELECT user_security_question_answer FROM users 
			WHERE user_name = "'.$_POST['username'].'"';
		
		//run the query and get the user that has the name submited from recoverPassword.php
		$query = $db_connection->prepare($sql);
		$query->execute();
		$user = $query->fetchObject();
		
		//if we can't find an user, then the user name doesn't exist, so we set the result to 0 and display the problem
		if(!$user){
			$button_string = "The username does not exist!";
			$result = 0;
		}

		//if the security question answers don't match, we set the result to 0 and display the problem
		if(strcmp($user->user_security_question_answer,$_POST['security_question_answer'])){
			$button_string = "Your security question answer is wrong!";
			$result = 0;
		}
		
		//clean up
		$query = null;
	}
	//in case the connection was not successful, we display this and set the result to 0
	else{
		$button_string = "An error occurred while connecting to the data base!";
		$result = 0;
	}
}
		
//if everything went alright until this point, send an email to the user with his new password
if($result == 1){	
	//create a new password
	$temp_password = randomString(6);
	$temp_password_hash = password_hash($temp_password,PASSWORD_DEFAULT);

	//update the password in the database 
	$sql = 'UPDATE users SET user_password_hash = :temp_password
		WHERE user_name = :user_name LIMIT 1';
	$query = $db_connection->prepare($sql);
	$query->bindValue(':user_name', $_POST['username']);
	$query->bindValue(':temp_password', $temp_password_hash);
	$query->execute();

	//change user_force_password_reset to 1
	$sql = 'UPDATE users SET user_force_password_reset = 1 WHERE user_name = :user_name LIMIT 1';
	$query = $db_connection->prepare($sql);
	$query->bindValue(':user_name', $_POST['username']);
	$query->execute();

	$query = null;
	$db_connection = null;

	//we send an email to the user with his new password
	sendResetPasswordEmail($_POST['email'],$temp_password,$_POST['fullname']);	
	
	$button_string = "Your new password has been emailed to you!";
	$success = true;

}
else{
	$success = false;
}

echo json_encode(array("message" => $button_string, "success" => $success));
?>


