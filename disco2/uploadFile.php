<?php
if(isset($_FILES["file"]["type"])){
	$validextensions = array("csv");
	$temporary = explode(".", $_FILES["file"]["name"]);
	$file_extension = end($temporary);
	$filename = "None";
	if (($_FILES["file"]["type"] == "text/csv") && ($_FILES["file"]["size"] < 52428800) && in_array($file_extension, $validextensions)) {
		if ($_FILES["file"]["error"] > 0){
			$message =  "Return Code: " . $_FILES["file"]["error"] . "<br/><br/>";
			$success = false;
		}
		else{
	        if (!file_exists($_POST['user_name'])) {
	            mkdir($_POST['user_name'], 0777, true);
	        }

	        $subfolder = basename($_FILES['file']['name'],'.csv')."/";
	        if (file_exists("users/".$_POST['user_name']."/".$subfolder)) {
	        	$message = $_FILES["file"]["name"] . " already exists.";
				$success = false;
	        }
			else{
				mkdir("users/".$_POST['user_name']."/".$subfolder, 0777, true);
				$sourcePath = $_FILES['file']['tmp_name']; // Storing source path of the file in a variable
				$targetPath = "users/".$_POST['user_name']."/".$subfolder.$_FILES['file']['name']; // Target path where file is to be stored

				if(move_uploaded_file($sourcePath,$targetPath)){
 					$message = "Success! File ".$_FILES["file"]["name"]." has been uploaded.";
 					$success = true;
 					$filename = $_FILES["file"]["name"];
 					
 				}
 				else{
 					$message = "Error uploading File!";
 					$success = false;
 				}
				
			}
		}
	}	
	else{
		$message = "Sorry, you must upload a CSV file and the maximum size is 50MB.";
		$success = false;
	}
	echo json_encode(array('message' => $message,'success' => $success,'filename' => $filename ));
	
}

?>