<?php

    include '../includes/DbOperations.php';

    $response['error'] = true;
    $response['message'] = "Unknown error occurred.";

    if(!isset($_POST['isLoginIn']) || !isset($_POST['email']) || !isset($_POST['password']) || 
            (!$_POST['isLoginIn'] && !isset($_POST['username'])))
        exit(json_encode($response));

    $isLoginIn = $_POST['isLoginIn'] == 'true';
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    $db = new DbOperations();

    if($isLoginIn) {
        $result = $db->login($email, $password);
    } else {
        $result = $db->register($username, $email, $password);
    }

    if(!$result['error']) {
        $isTokenForWebLogin = true;

        $user_id = (isset($result['user_id']) && !empty($result['user_id'])) ? $result['user_id'] : "";    
        $db->generateLoginToken($user_id, $isTokenForWebLogin);

        $result['message'] .= "<br>Redirecting...";
    }

    $response['error'] = $result['error'];
    $response['message'] = $result['message'];

    exit(json_encode($response));

?>