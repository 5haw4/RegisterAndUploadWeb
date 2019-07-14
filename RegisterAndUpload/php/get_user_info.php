<?php

    include_once '../includes/DbOperations.php';

    $db = new DbOperations();

    $validate_response = $db->validateUserKey("");
    $user = $validate_response['isLoggedIn'] ? $validate_response['user'] : []; 

    $response['isLoggedIn'] = $validate_response['isLoggedIn'];
    $response['user'] = $user;

    exit(json_encode($response));

?>