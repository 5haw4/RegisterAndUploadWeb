<?php
	
    require '../includes/DbOperations.php';

    $response['error'] = true;
    $response['message'] = "Unknown error occurred.";

    $db = new DbOperations();

    $img_name = "";
	if(isset($_FILES["file"]) && $_FILES["file"]["name"] != ''){
        $test = explode('.', $_FILES["file"]["name"]);
        $ext = end($test);
        
        $secure = true;
        $rand_string = bin2hex(openssl_random_pseudo_bytes(14, $secure));
        $curTime = round(microtime(true) * 1000);

		$img_name = $rand_string . $curTime . '.' . $ext;
		$location = '../images/' . $img_name;
        move_uploaded_file($_FILES["file"]["tmp_name"], $location);
    }

    $description = isset($_POST['description']) ? $_POST['description'] : "";
    $result = $db->createPost("", $img_name, $description);
    $response['error'] = $result['error'];
    $response['message'] = $result['message'];
    
    if(!$result['error'])
        $response['message'] = $response['message'] . '<br>Redirecting...';

    exit(json_encode($response));

?>