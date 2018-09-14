<?php

/**
 * This is the installation file for the 0-one-file version of the php-login script.
 * It simply creates a new and empty database.
 */

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

performMinimumRequirementsCheck();

// error reporting config
error_reporting(E_ALL);

// config data
$server = "mysql:dbname=disco2;host=localhost";
$username = "root";
// $password = "EgDRMwiYm9Tb";
$password = "";

// create new database file / connection (the file will be automaticly created the first time a connection is made up)
$db_connection = new PDO($server,$username,$password);

//we will first delete the users database if it exists
$sql = 'DROP TABLE IF EXISTS `users`';
echo '<p>'.$sql.'<p>';
$query = $db_connection->prepare($sql);
if($query == NULL) {
	echo '<p>invalid query</p>';
	return;
}
if($query->execute()){
	echo '<p>The users database was successfully deleted!</p>';
}
else{
	echo '<p>An error occurred while deleteing the users database!</p>';
}

$query = null;

// create new empty users table inside the database (if table does not already exist)
$sql = 'CREATE TABLE IF NOT EXISTS `users` (
        `user_id` INT PRIMARY KEY AUTO_INCREMENT,
        `user_fullname` varchar(64),
        `user_name` varchar(64),
        `user_password_hash` varchar(255),
        `user_email` varchar(64),
        `user_security_question` int,
		`user_security_question_answer` varchar(64),
        `user_force_password_reset` int,
	    `user_active_account_flag` int,
	    `user_admin_flag` int,
        `user_regular_flag` int,
        `project_description` text
	);
        CREATE UNIQUE INDEX `user_id_UNIQUE` ON `users` (`user_id` ASC);';

// execute the above query
echo '<p>'.$sql.'<p>';
$query = $db_connection->prepare($sql);
if($query == NULL) {
	echo '<p>invalid query</p>';
	return;
}
if($query->execute()){
	echo '<p>The users database was successfully created!</p>';
}
else{
	echo '<p>An error occurred while creating the users database!</p>';
}

$query = null;

// create the admin users, maxwell
$hashedpw = password_hash("bruce", PASSWORD_DEFAULT);
$sql = sprintf('INSERT INTO users VALUES(1, "Bruce Maxwell", "maxwell", "%s", "bmaxwell@colby.edu",0,"",0,1,1,0,"")', $hashedpw);
$query = $db_connection->prepare($sql);
$add_success = $query->execute();
if($add_success) {
	echo "<p>User maxwell added.</p>\n";
}

$query = null;

// create the admin users, Jingyan
// $hashedpw = password_hash("jingyan", PASSWORD_DEFAULT);
// $sql = sprintf('INSERT INTO users VALUES(2, "Jingyan Dong", "dong", "%s", "jdong@colby.edu",0,"",0,1,1,0,"");', $hashedpw);
// $query = $db_connection->prepare($sql);
// $add_success = $query->execute();
// if($add_success) {
// 	echo "<p>User dong added.</p>\n";
// }
// $query = null;

?>
