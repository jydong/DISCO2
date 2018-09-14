<?php
include 'utilities.php';
startSession();

$button_string = "";

//we first check if the form was submitted correctly(all the varaibles in the right format and if the form
//was submitted using the right button from changePassword.php 
//if the form is complete and it was submitted correctly put the result to 1
//otherwise get different values for different errors
if( !empty($_POST['old_password']) && 
    !empty($_POST['new_password']) && 
    !empty($_POST['new_password_repeat']) &&
    strlen($_POST['old_password']) <=32 &&
    strlen($_POST['new_password']) <=32 &&
    strlen($_POST['new_password_repeat']) <=32 &&
    strlen($_POST['old_password']) >=6 &&
    strlen($_POST['new_password']) >=6 &&
    strlen($_POST['new_password_repeat']) >=6 &&
    $_POST['new_password'] == $_POST['new_password_repeat'] ){
	$result = 1;
}
//if something went wrong, determine what, set the result to 0 and display the error

else if( empty($_POST['old_password'])){
	if ($_SESSION['user_force_password_reset'] == 0){
		$button_string = "You did not enter the old password!";
		$result = 0;
	}
	else{
		$result = 1;
	}
}
else if( empty($_POST['new_password']) ){
	$button_string = "You did not enter a new password!";
	$result = 0;
}
else if( empty($_POST['new_password_repeat']) ){
	$button_string = "You did not repeat the new password!";
	$result = 0;
}
else if( strlen($_POST['old_password'])>32 || strlen($_POST['old_password'])<6 ){
	if ($_SESSION['user_force_password_reset'] == 0){
		$button_string = "The old password has to have between 6 and 32 characters!";
		$result = 0;
	}
	else{
		$result = 1;
	}
}
else if( strlen($_POST['new_password'])>32 || strlen($_POST['new_password'])<6){
	$button_string = "The new password has to have between 6 and 32 characters!";
	$result = 0;
}
else if( strlen($_POST['new_password_repeat'])>32 || strlen($_POST['new_password_repeat'])<6){
	$button_string = "The new retyped password has to have between 6 and 32 characters!";
	$result = 0;
}
else if( strcmp($_POST['new_password'],$_POST['new_password_repeat'])){
	$button_string = "The new password does not match with the retyped password!";
	$result = 0;
}
else{
	$button_string = "An unknown error occurred while submitting your data!";
	$result = 0;
}


//if the form is completed correctly
if($result == 1){

	//initialize the connection variables
	$db_connection = null;

	//connect to the data base and if the connection is successful
	if(createDatabaseConnection($db_connection,$feedback)){
		
		//run a query that retrives the hashed password of the current user
		$sql = 'SELECT user_password_hash FROM users 
			WHERE user_name = "'.$_SESSION['user_name'].'"';
		
		$query = $db_connection->prepare($sql);
		$query->execute();

		//get the password
		$password = $query->fetchObject();
		
		//if we actually retrived a hash
		if($password){
			if ($_SESSION['user_force_password_reset'] == 0){
				//if the retrieved hash doesn't match the stored password
				//store a different value for result
				if (!password_verify($_POST['old_password'], $password->user_password_hash)){
					$button_string = "The old password is wrong!";				
					$result = 0;
				}
			}
		}
		//if the query is empty, then the user name had to be wrong so ,we set the result to 0 and display what went wrong
		else{
			$button_string = "The username used is wrong!";
			$result = 0;
		}

		//clean up
		$query = null;	
	}
	//if the connection was not successful, change result to 0 and display the wrong doings
	else{
		$button_string = "An error occurred while connecting to the data base!";
		$result = 0;
	}
}
		

//if everything is right until this point(including password verification) we can go ahead and change the password	
if($result == 1){
	
	//run a query that updates the password
	$sql = 'UPDATE users 
		SET user_password_hash = "'.password_hash($_POST['new_password'],PASSWORD_DEFAULT).'" 
		WHERE user_name = :user_name LIMIT 1';

	$query = $db_connection->prepare($sql);
	$query->bindValue(':user_name', $_SESSION['user_name']);
	$result = $query->execute();

	//run a query to get the value of user_force_password_reset 
	$sql = 'SELECT user_force_password_reset FROM users WHERE user_name = :user_name LIMIT 1';
	$query = $db_connection->prepare($sql);
	$query->bindValue(':user_name', $_SESSION['user_name']);
	$query->execute();
	$result_row = $query->fetchObject();

	if($result){
		// change user_force_password_reset to 0 if this is the first time the user logs in
		if(intval($result_row->user_force_password_reset) == 1){
			$sql = 'UPDATE users SET user_force_password_reset = 0 WHERE user_name = :user_name';
			$query = $db_connection->prepare($sql);
			$query->bindValue(':user_name', $_SESSION['user_name']);
			$query->execute();
			$_SESSION['user_force_password_reset'] = 0;
		}
	
		//display the success
		$button_string = "Your password has been successfully changed";

	}
	else{


		$button_string = "An error occurred while changing the password! Try again!";
	}

	//clean up
	$db_connection = null;
	$query = null;
	
}



if($result == 1){
	$success = true;
}
else{
	$success = false;
}

echo json_encode(array("message" => $button_string, "success" => $success));



?>