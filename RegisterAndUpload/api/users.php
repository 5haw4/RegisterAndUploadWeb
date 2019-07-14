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

    $app->post('/register',function(Request $request, Response $response){

        $message['error'] = true;
        $message['message'] = "Unknown error has occurred.";
    
    	$request_data = $request->getParsedBody();
    
    	$username = $request_data['username'];
    	$email = $request_data['email'];
    	$password = $request_data['password'];
		
		$db = new DbOperations();
				
        $result = $db->register($username, $email, $password);
        
        $message['error'] = $result['error'];
        $message['message'] = $result['message'];

        if(!$message['error']) {
            $isTokenForWebLogin = false;
            $user_id = (isset($result['user_id']) && !empty($result['user_id'])) ? $result['user_id'] : "";
            $result_token = $db->generateLoginToken($user_id, $isTokenForWebLogin);
            $message['user_key'] = !$result_token['error'] ? $result_token['user_key'] : "";
        }

    	$response->write(json_encode($message));
    	return $response
    			->withHeader('Content-type','application/json');
    });

    $app->post('/login',function(Request $request, Response $response){

        $message['error'] = true;
        $message['message'] = "Unknown error has occurred.";
    
    	$request_data = $request->getParsedBody();
    
    	$email = $request_data['email'];
    	$password = $request_data['password'];				
        
		$db = new DbOperations();
				
        $result = $db->login($email, $password);
        
        $message['error'] = $result['error'];
        $message['message'] = $result['message'];

        if(!$message['error']) {
            $isTokenForWebLogin = false;
            $user_id = (isset($result['user_id']) && !empty($result['user_id'])) ? $result['user_id'] : "";
            $result_token = $db->generateLoginToken($user_id, $isTokenForWebLogin);
            $message['user_key'] = !$result_token['error'] ? $result_token['user_key'] : "";
        }

        $response->write(json_encode($message));
    	return $response
    			->withHeader('Content-type','application/json');
    });

    $app->post('/validate_user_key',function(Request $request, Response $response){
    
    	$request_data = $request->getParsedBody();
    
    	$user_key = $request_data['user_key'];
        
		$db = new DbOperations();
		
        $result = $db->validateUserKey($user_key);
        
        $message['isLoggedIn'] = $result['isLoggedIn'];
        $message['user'] = $result['user'];

        $response->write(json_encode($message));
    	return $response
    			->withHeader('Content-type','application/json');
    });

    $app->run();

?>