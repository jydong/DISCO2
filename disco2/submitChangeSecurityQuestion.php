 <?php
include 'utilities.php';
startSession();

$button_string = "";
//if the form was submitted correctly, if the question was selected and if the answer has the right format and exists,
//we set the result to 1, otherwise we store result = 0 and display the problem
if(!empty( $_POST['security_question_answer'] ) &&
	strlen( $_POST['security_question_answer'] ) <= 64 &&
	strlen( $_POST['security_question_answer'] ) >= 2 &&
	intval($_POST['security_question'])){			
	$result = 1;
}

else if(empty( $_POST['security_question_answer'] )){
	$button_string = "You did not submit an answer!";
	$result = 0;
}
else if(strlen( $_POST['security_question_answer'] ) > 64){
	$button_string = "The answer has to have at most 64 characters!";
	$result = 0;
}
else if(strlen( $_POST['security_question_answer'] ) < 2){
	$button_string = "The answer has to have at least 2 characters!";
	$result = 0;
}
else if(!intval($_POST['security_question'])){
	$button_string = "You did not select a question!";
	$result = 0;
}
else{
	$button_string = "An unknown error occured while submitting your changes!";
	$result = 0;
}

//if we have the right data we run the query that updates the question and the answer
if($result == 1){

	//initialize connection variables
	$db_connection = null;

	//connect to the data base and run the query
    if(createDatabaseConnection($db_connection,$feedback)){
		//the query updates the security question and the answer where the username corresponds to the one stored in $_SESSION
		$sql = 'UPDATE users SET user_security_question = '.$_POST['security_question'].',user_security_question_answer = "'.$_POST['security_question_answer'].'"
		                     WHERE user_name = "'.$_SESSION['user_name'].'"';
		$query = $db_connection->prepare($sql);
		$result = $query->execute();
		if(!$result){
			$button_string = "The username does not exist!";
			$result = 0;
		}
		//clean up
		$query = null;
		$db_connection = null;	
	}
	//if we cannot connect store a different result value
	else{
		$button_string = "An error occurred while connecting to the data base!";
		$result = 0;
	}
}

//if everything went well
if($result == 1){
	//display the success and a table with buttons
	$button_string = "Your security question has been successfully changed!";
	$success = true;

}
//otherwise
else{
	$success = false;
}

echo json_encode(array("message" => $button_string, "success" => $success));


?>



