<?php

    include '../includes/DbOperations.php';

    $response['error'] = true;
    $response['message'] = "Unknown error occurred.";

    if(!isset($_POST['post_id']) || $_POST['post_id'] == "")
        exit(json_encode($response));

    $post_id =$_POST['post_id'];

    $db = new DbOperations();

    $result = $db->deletePost("", $post_id);

    $response['error'] = $result['error'];
    $response['message'] = $result['message'];

    exit(json_encode($response));

?>