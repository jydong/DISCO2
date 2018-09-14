
<?php
	session_start();

	// an array of selected types is submitted by confirmTypeSelection button in index.php
	// store this array in $_SESSION["types"]

	if(!isset($_SESSION["current_file"])){
		echo "Please select a file first!";
		return;
	}


	if(!empty($_POST)){
		$_SESSION["types"] = $_POST;
	}


	// update the associated type file 
	$typeFilename = basename($_SESSION['current_file'],'.csv').'_type.csv';
   	$subfolder = "users/".$_SESSION['user_name']."/".basename($_SESSION['current_file'],'.csv')."/";
	$typeFile = fopen($subfolder.$typeFilename,"w");
	fputcsv($typeFile, $_SESSION['headers']); //write headers
	fputcsv($typeFile, $_SESSION["types"]); //write types 
	fclose($typeFile);


	



	




?>