<?php

    include '../includes/DbOperations.php';

    $offset = isset($_POST['offset']) ? $_POST['offset'] : 0;

    $db = new DbOperations();

    $result = $db->getFeed("",$offset);

    $response['error'] = $result['error'];
    $response['type'] = $result['type'];
    $response['content'] = "<div class='card-view center-text card-view-bg'><h5><b>Unknown error occurred.</b></h5></div>";

    if(!$result['error']) {
        $content = "";
        foreach($result['posts'] as $curPost) {
            $showDeleteBtn = $curPost['did_user_create_post']; //$user_id == $curPost['created_by_uid'];
            
            $content .= 
                '<div class="card-view card-view-bg card-view-post" id="card-view-post-' . $curPost['post_id'] . '">
                    <div class="row post-title-row">
                        <h5><b>' . $curPost['username'] . ' - ' . $curPost['creation_time'] . '</b></h5>'
                        . ( $showDeleteBtn ?
                        '<button class="btn btn-sm btn-danger delete-post-btn" data-post-id="' . $curPost['post_id'] . '">
                            <i class="fa fa-trash"></i>
                        </button>' : ''
                        ) .
                    '</div>
                    <span id="alert-span-' . $curPost['post_id'] . '"></span>
                    <p style="white-space: pre-line; word-wrap: break-word;">' . $curPost['description'] . '</p>
                    <center><img class="img-post" src="' . $curPost['image'] . '"></center>
                </div>';
        }
        $response['content'] = $content;
    } else if ($result['error']) {
        $response['content'] = "<div class='card-view center-text card-view-bg'><h5><b>" . $result['message'] . "</b></h5></div>";
    }

    exit(json_encode($response));

?>