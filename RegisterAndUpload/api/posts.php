<?php
    use Psr\Http\Message\ServerRequestInterface as Request;
    use Psr\Http\Message\ResponseInterface as Response;

    require '../vendor/autoload.php';
    require '../includes/DbOperations.php';
    require '../includes/Constants.php';

    $app = new \Slim\App([ 'settings'=>[ 'displayErrorDetails'=>true]]);

    $app->add(new Tuupola\Middleware\HttpBasicAuthentication([
        "secure" => USING_HTTPS,
        "users" => [API_USER => API_PASS]
    ]));

    $app->post('/feed',function(Request $request, Response $response){

        $message['error'] = true;
        $message['type'] = "UNKNOWN";
        $message['message'] = "Unknown error has occurred.";
        $message['posts'] = [];
    
    	$request_data = $request->getParsedBody();
    
    	$user_key = $request_data['user_key'];
    	$offset = $request_data['offset'];
        
		$db = new DbOperations();
				
        $result = $db->getFeed($user_key, $offset);
        
        $message['error'] = $result['error'];
        $message['isLoggedIn'] = $result['isLoggedIn'];
        $message['user'] = $result['user'];
        $message['type'] = $result['type'];
        $message['message'] = $result['message'];
        $message['posts'] = $result['posts'];

        $response->write(json_encode($message));
    	return $response
    			->withHeader('Content-type','application/json');
    });

    $app->post('/create_post',function(Request $request, Response $response){

        $message['error'] = true;
        $message['message'] = "Unknown error has occurred.";

        $request_data = $request->getParsedBody();
        
    	$user_key = $request_data['user_key'];
    	$image = $request_data['image'];
        $description = $request_data['description'];
        
        $img_name = "";
        if(!empty($image)){
            $secure = true;
            $rand_string = bin2hex(openssl_random_pseudo_bytes(14, $secure));
            $curTime = round(microtime(true) * 1000);
    
            $img_name = $rand_string . $curTime . '.png';

            $location = '../images/' . $img_name;
            file_put_contents($location,base64_decode($photo));
        }

        $db = new DbOperations();
        
        $result = $db->createPost($user_key, $img_name, $description);
        
        $message['error'] = $result['error'];
        $message['message'] = $result['message'];

        $response->write(json_encode($message));
    	return $response
    			->withHeader('Content-type','application/json');
    });

    $app->post('/delete_post',function(Request $request, Response $response){

        $message['error'] = true;
        $message['message'] = "Unknown error has occurred.";
    
    	$request_data = $request->getParsedBody();
    
    	$user_key = $request_data['user_key'];
    	$post_id = $request_data['post_id'];
        
        $db = new DbOperations();
        
        $result = $db->deletePost($user_key, $post_id);
        
        $message['error'] = $result['error'];
        $message['message'] = $result['message'];

        $response->write(json_encode($message));
    	return $response
    			->withHeader('Content-type','application/json');
    });


    $app->run();

?>