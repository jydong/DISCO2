<?php
//this where the php starts

//include the utilities file
include 'utilities.php';

$result = 1;//we set result to 1
$button_string = '';
	
//we check if we have the right keys in the $_POST array
//because we will repost data in changePassword.php if something went wrong, we set 
//result to 2 for this case because if we are missing varaibles, reposting the data might throw errors
if( !array_key_exists('username', $_POST) ||
    !array_key_exists('fullname', $_POST) ||
    !array_key_exists('email', $_POST) ) {
	$button_string = 'The form was not submitted correctly!';
	$result = 2;
}

//now we are going to check if the format of the varaibles is correct and if something is wrong
        //we display what went wrong and set result 0 
elseif( empty($_POST['username']) ) {
	$button_string = 'Username field is empty!';
	$result = 0;
}
elseif( strlen($_POST['username']) < 4 || strlen($_POST['username']) > 36 ) {
	$button_string = 'Username must be between 2 and 36 characters!';
	$result = 0;
}
elseif( !preg_match('/^[a-z\d]{4,36}$/i', $_POST['username']) ) {
	$button_string = 'Username must contain only a-z, A-Z, or 0-9!';
	$result = 0;
}
elseif( empty($_POST['email']) ) {
	$button_string = 'Email field is empty!';
	$result = 0;
}
elseif( strlen($_POST['email']) > 64 ) {
	echo '<p>Email field cannot be longer than 64 characters!';
	$result = 0;
}
elseif( !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL) ) {
	$button_string = 'Email field is not in a valid email format!';
	$result = 0;
}
elseif( empty($_POST['fullname']) ) {
	$button_string = 'The fullname field is empty!';
	$result = 0;
}
elseif( strlen($_POST['fullname']) > 36 ) {
	$button_string = 'The fullname can be no longer than 36 characters!';
	$result = 0;
}

//if the form was submitted correctly and the format of the data is correct
if($result == 1){
	
	//initialize connection variables
	$db_connection = null;
	$feedback = '';
	
	//connect to the database and if the connection is successful
	if( createDatabaseConnection($db_connection, $feedback ) ) {
		
		//sql query that selects certain data about the user where the username is equal to the one submitted
		$sql = 'SELECT user_fullname,user_email,user_security_question, user_security_question_answer FROM users 
			WHERE user_name = "'.$_POST['username'].'"';
		
		//run the query and get the user that has the name submited from recoverPassword.php
		$query = $db_connection->prepare($sql);
		$query->execute();
		$user = $query->fetchObject();
		
		//if we can't find an user, then the user name doesn't exist, so we set the result to 0 and display the problem
		if(!$user){
			$button_string = 'Sorry, the username does not exist!';
			$result = 0;
		}
		//if the fullnames don't match, we set the result to 0 and display the problem		
		else if(strcmp($user->user_fullname,$_POST['fullname'])){
			$button_string = 'Sorry, your fullname is wrong!';
			$result = 0;
		}
		//if the emails don't match, we set the result to 0 and display the problem	
		else if(strcmp($user->user_email,$_POST['email'])){
			$button_string = 'Sorry, your email is wrong!';
			$result = 0;
		}
		
		//clean up
		$query = null;
	}
	//in case the connection was not successful, we display this and set the result to 0
	else{
		$button_string = 'An error occurred while connecting to the data base:' .$feedback.'!';
		$result = 0;
	}
}
		

if($result == 1){
	$success = true;
}
else{
	$success = false;
}

echo json_encode(array("message" => $button_string, "success" => $success, "username" => $_POST['username'],"email" => $_POST['email'], "fullname" => $_POST['fullname']));





?>

