<?php

    include '../includes/DbOperations.php';

    $db = new DbOperations();

    $validate_response = $db->validateUserKey("");
		
    if($validate_response['isLoggedIn']) {
        $db->deleteWebLoginToken($validate_response['user']['user_id']);
    } else {
        setcookie('rememberme', '', time() - 3600, '/');
    }

?>