<?php

/*
 * Login page for the MCMI Data Resource

 * The login code taken from/modified from:
 * @package php-login
 * @author Panique
 * @link https://github.com/panique/php-login/
 * @license http://opensource.org/licenses/MIT MIT License
 * 
 * Modified by Bruce A. Maxwell


Web site structure
- home page with login field
-- home page with user logged in
- HIT entries listing
- data entry page

Home page and login management (login and logout) should all be one file: index.php

HIT entries listing should be its own file: listing.php

HIT report should be its own file: report.php


Common functions:
- accessing the various DBs
- starting sessions

 */

performMinimumRequirementsCheck();

// Set of utility functions for managing sessions and handling logins

// requirements check
function performMinimumRequirementsCheck() {
	if (version_compare(PHP_VERSION, '5.3.7', '<')) {
		echo "Sorry, Simple PHP Login does not run on a PHP version older than 5.3.7 !";
	} elseif (version_compare(PHP_VERSION, '5.5.0', '<')) {
		require_once("libraries/password_compatibility_library.php");
		return true;
	} elseif (version_compare(PHP_VERSION, '5.5.0', '>=')) {
		return true;
	}
	return false;
}

// creates a database connection, returns true if successful, false if not
// arguments are references and are modified by the function
function createDatabaseConnection( &$db_connection, &$feedback ) {
	// config data
	$server = "mysql:dbname=disco2;host=localhost";
	$username = "root";
	$password = "";
	try {
		$db_connection = new PDO($server,$username,$password);
		return true;
	} catch (PDOException $e) {
		$feedback = "PDO database connection problem: " . $e->getMessage();
	} catch (Exception $e) {
		$feedback = "General problem: " . $e->getMessage();
	}
	return false;
}

//function that starts the php session and sets the path for writting things on a file on the server
function startSession() {
	session_save_path();
	session_start();
}



//function that generates a random string of a given size
function randomString($size){
	$password = '';
	$characters = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
	for($i=0;$i<$size;$i++){
		$password = $password.$characters[rand(0, strlen($characters)-1)];
	}
	return $password;
}

//function that sends a registration email to a given address containing registration details
function sendRegistrationEmail($to,$username,$temp_password,$name){
	$subject = 'Your New DISCO2 Account';
	
	$message = '<html><body>';
	$message .= '<table>';
	$message .= '<tr><td>Dear '. $name. ',</td></tr>';
	$message .= '<tr><td></td></tr>';
	$message .= '<tr><td>An account was created for you on DISCO2.</td></tr>';
	// $message .= '<tr><td>To activate your account and to select a password please click on the link below:</td></tr>';
	// $message .= '<tr><td>https://mcmi.colby.edu/hit/register.php?registration_code=' . $registration_code . '</td></tr>';
	$message .= '<tr><td>Your username is:</td></tr>';
	$message .= '<tr><td>' . $username . '</td></tr>';
	$message .= '<tr><td>Your temporary password is:</td></tr>';
	$message .= '<tr><td>' . $temp_password . '</td></tr>';
	$message .= '<tr><td></td></tr>';
	$message .= '<tr><td>Best Regards,</td></tr>';
	$message .= '<tr><td>The DISCO2 Team</td></tr>';	
	$message .= '</table>';
	$message .= '</body></html>';
	  
	$headers = "From: mcmicolby@gmail.com" . "\r\n";
	$headers .= "Content-Type: text/html; charset=ISO-8859-1";

	$from = "-f  bmaxwell@colby.edu";

	mail($to, $subject, $message, $headers, $from);
}

//function that sends an email to a given address with a new password
function sendResetPasswordEmail($to,$temp_password,$name){
	$subject = 'DISCO2 Password Reset';
	
	$message = '<html><body>';
	$message .= '<table>';
	$message .= '<tr><td>Dear '. $name. ',</td></tr>';
	$message .= '<tr><td></td></tr>';
	$message .= '<tr><td>Your password on DISCO2 has been reset.</td></tr>';
	$message .= '<tr><td>The temporary password can be modified when you log in into your account:</td></tr>';
	$message .= '<tr><td>Your temporary password is:</td></tr>';
	$message .= '<tr><td>' . $temp_password . '</td></tr>';
	$message .= '<tr><td></td></tr>';
	$message .= '<tr><td>Best Regards,</td></tr>';
	$message .= '<tr><td>The DISCO2 Team</td></tr>';	
	$message .= '</table>';
	$message .= '</body></html>';
	  
	$headers = "From: mcmicolby@gmail.com" . "\r\n";
	$headers .= "Content-Type: text/html; charset=ISO-8859-1";

	$from = "-f  bmaxwell@colby.edu";

	mail($to, $subject, $message, $headers, $from);
}



?>