<?php	
include 'utilities.php';
startSession();

$button_string = '';


//if the result is 1, then the form is complete, otherwise something went wrong	
if( !empty($_POST['user_name']) && !empty($_POST['user_password']) && 
    strlen($_POST['user_name']) >=4 && strlen($_POST['user_name']) <=36 &&
        strlen($_POST['user_name']) >=4 && strlen($_POST['user_name']) <=36 ){
	$result = 1;
}
// else if( !array_key_exists('login',$_POST) ){
// 	$button_string = 'The form was not submitted correctly!';
// 	$result = 0;
// }
else if( empty($_POST['user_name']) ){
	$button_string = 'Please insert a username!';
	$result = 0;
}
else if( empty($_POST['user_password']) ){
	$button_string = 'Please insert a password!';
	$result = 0;
}
elseif( strlen($_POST['user_name']) < 4 || strlen($_POST['user_name']) > 36 ) {
	$button_string = 'Username must be between 4 and 36 characters!';
	$result = 0;
}
elseif( strlen($_POST['user_password']) < 4 || strlen($_POST['user_password']) > 36 ) {
	$button_string = 'Password must be between 4 and 36 characters!';
	$result = 0;
}
//if the form is complete
if($result == 1){
	
	//connect to the database and if the connection is successful
	$db_connection = null;
	$feedback = '';
	if( createDatabaseConnection($db_connection, $feedback ) ) {
		
		//sql query that selects certain data about the user where the username is equal to the one submitted
		$sql = 'SELECT user_name, user_fullname, user_email, user_password_hash, user_admin_flag, user_regular_flag,user_active_account_flag,user_force_password_reset
			FROM users 
			WHERE user_name = :user_name LIMIT 1';
		//execute the query
		$query = $db_connection->prepare($sql);
		$query->bindValue(':user_name', $_POST['user_name']);
		$query->execute();

		//fetch the only row
		$result_row = $query->fetchObject();
		
		//if the we have a row(which means the user exists) and if the account is active
		if($result_row && intval($result_row->user_active_account_flag) == 1) {
			
			//we will first check if the passwords match and if they do
			if (password_verify($_POST['user_password'], $result_row->user_password_hash)) {				
				// write user data into PHP SESSION [a file on your server]
				
				//data about the user
				$_SESSION['user_name'] = $result_row->user_name;
				$_SESSION['user_fullname'] = $result_row->user_fullname;
				$_SESSION['user_email'] = $result_row->user_email;

				//these will be user to check if the user is logged in and if the user is an admin
				$_SESSION['user_is_logged_in'] = true;
				$_SESSION['user_admin_flag'] = $result_row->user_admin_flag;
				$_SESSION['user_regular_flag'] = $result_row->user_regular_flag;
				$_SESSION['user_force_password_reset'] = $result_row->user_force_password_reset;

				$success = true;

				// if this is the first time the user logins in, redirct to changePassword.php to let the user reset the password
				if(intval($result_row->user_force_password_reset) == 1){; 
					$firstTime = true;
				}
				else{
					$firstTime = false;
				}
    			

			}
			//if the password_verify function returns false, then the password is wrong				
			else{
				$button_string = 'Your password is wrong!';
				$result = 0;
			}
		}
		//if the first if statement fails then either we have no results for the query, in which case the user doesn't exist
		else if(!$result_row){
			$button_string = "The username doesn't exist!";
			$result = 0;
		}
		//or the active_flag is 0, in which case the account is not active
		else{
			$button_string = 'This account has not been activated! Please contact the administrator.';
			$result = 0;
		}

		$query = null;
		$dbconn = null;

	}
	//we will display this if there was an error while connecting to the data base
	else{
		$button_string = 'An error occurred while connecting to the data base:' .$feedback.'!';
		$result = 0;
	}

}

if($result == 0 ){
	$success = false;
    $firstTime = false;
}

echo json_encode(array('message' => $button_string, 'success' => $success, 'firstTime' => $firstTime));


?>