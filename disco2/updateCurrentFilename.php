<?php
    session_start();
    $_SESSION["current_file"] = $_POST["uploadedFiles"]; 

   	$typeFilename = basename($_SESSION['current_file'],'.csv').'_type.csv';
   	$subfolder = "users/".$_SESSION['user_name']."/".basename($_SESSION['current_file'],'.csv')."/";
    $dataFile = fopen($subfolder.$_SESSION['current_file'],"r");
	
	if(!feof($dataFile)){ // update headers in $_SESSION["headers"]
		$_SESSION["headers"] = fgetcsv($dataFile);
	}
	fclose($dataFile);
	
	// if a type file does not exist, loop through each line of the data file to decide a type (number or string) for each header, and create a type file
   	if(!file_exists($subfolder.$typeFilename)){
	
		$types = array(); // create an array for header types
		for ($x = 0; $x < count($_SESSION["headers"]); $x++) {
	    	array_push($types,"number");
		}

		$dataFile = fopen($subfolder.$_SESSION['current_file'],"r");
		fgetcsv($dataFile); // skip the first line of headers
		while (($data = fgetcsv($dataFile, 1000, ",")) !== FALSE) { // decide header types
			for ($x = 0; $x < count($_SESSION["headers"]); $x++) {
	    		if($types[$x] == "number"){
	    			if(!is_numeric($data[$x])){
	    				$types[$x] = "string";
	    			}
	    		}
	    	}
		}
		fclose($dataFile);

		$_SESSION["types"] = $types; // update types in $_SESSION["types"]

		$typeFile = fopen($subfolder.$typeFilename,"w");
		fputcsv($typeFile, $_SESSION['headers']); //write headers
		fputcsv($typeFile, $_SESSION["types"]); //write types 
		fclose($typeFile);
	}

	else{ //else, read the types from the type file and update types in $_SESSION["types"]
		$typeFile = fopen($subfolder.$typeFilename,"r");
		fgetcsv($typeFile); // skip the first line of headers
		$_SESSION["types"] =  fgetcsv($typeFile);
		fclose($typeFile);
	}

?>