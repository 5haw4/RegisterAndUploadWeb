<?php

	class DbOperations{
		private $con;

		function __construct(){
			require_once dirname(__FILE__) . '/DbConnect.php';

			$db = new DbConnect();
			$this->con = $db->connect();
		}

        //---Registering and login in---//
        
        public function register($username, $email, $password){
            $response['error'] = true;
            $response['message'] = "Unknown error occurred.";

            if(empty($username)){
                $response['message'] = "Please enter a valid username.";
                return $response;
            }
            if(strlen($username) < 5 || strlen($username) > 15){
                $response['message'] = "Please enter a username with 5 - 15 characters.";
                return $response;
            }
            if($this->doesUsernameExist($username)){
                $response['message'] = "Username is already in use.";
                return $response;
            }
            if(preg_match('/[^A-Za-z0-9]/', $username)) {
                $response['message'] = "You can only use english letters and numbers in your username.";
                return $response;
            }

            if(empty($password)){
                $response['message'] = "Please enter a valid password.";
                return $response;
            }
            if(strlen($password) < 5 || strlen($password) > 50){
                $response['message'] = "Please enter a password with 5 - 50 characters.";
                return $response;
            }

            if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
                $response['message'] = "Please enter a valid email address.";
                return $response;
            }
            if(strlen($email) > 30){
                $response['message'] = "Please enter an email address with less then 50 characters.";
                return $response;
            }
        
            $password = password_hash($password,PASSWORD_DEFAULT);

            if(!$this->doesEmailExist($email)){
                $stmt = $this->con->prepare("INSERT INTO users (username,email,password) VALUES(?,?,?)");
                $stmt->bind_param("sss",$username,$email,$password);
                if($stmt->execute()){
                    $response['error'] = false;
                    $response['message'] = "Successfully registered!";
                    $response['user_id'] = $this->con->insert_id;
                    return $response;
                } else {
                    $response['message'] = "Failed to register.";
                    return $response;
                }
            } else {
                $response['message'] = "Email already exist.";
            }
            return $response;
        }
        public function login($email, $password){
            $response['error'] = true;
            $response['message'] = "Unknown error occurred.";

			if($this->doesEmailExist($email)){
                $stmt = $this->con->prepare("SELECT password, user_id FROM users WHERE email = ?");
                $stmt->bind_param("s",$email);
                $stmt->execute();
                $stmt->bind_result($hash_password, $user_id);
                $stmt->fetch();
    
				if(password_verify($password,$hash_password)){
                    $response['error'] = false;
                    $response['message'] = "Logged in successfully!";
                    $response['user_id'] = $user_id;
				} else {
                    $response['message'] = "Wrong password.";
				}
			} else {
                $response['message'] = "User not found.";
            }
            return $response;
        }
        public function generateLoginToken($user_id, $isTokenForWebLogin){
            if($this->doesUidExist($user_id)) {
                $token = $this->generateRandomToken();
                
                //saving the token in the db
                $query = "UPDATE users SET " . 
                        ($isTokenForWebLogin ? "web_login_token" : "app_login_token") .
                        " = ? WHERE user_id = ?";
                $stmt = $this->con->prepare($query);
                $stmt->bind_param("ss",$token, $user_id);
                $stmt->execute();

                //creating a 'user key' that will be stored on the client side
                $user_key = $user_id . ':' . $token;
                $mac = hash_hmac('sha256', $user_key, SECRET_KEY);
                $user_key .= ':' . $mac;
                if($isTokenForWebLogin) {
                    //saving the 'user key' in a cookie
                    $daysTillExpiration = 180; //approx. 6 month
                    setcookie('rememberme', $user_key, time() + 60 * 60 * 24 * $daysTillExpiration, '/');
                    $response['error'] = false;
                } else {
                    //returning the 'user key' to be stored in the app
                    $response['error'] = false;
                    $response['user_key'] = $user_key;
                }
            } else {
                $response['error'] = true;
            }
            return $response;
        }
        public function validateUserKey($user_key) {
            /*
                - If 'user_key' is empty:
                        - The user is using the website
                        - Need to get the 'user_key' from its cookie
                
                - If 'user_key' is NOT empty:
                        - The user is using the app
                        - The 'user_key' already been retrieved off of the user's device and got 
                        passed through the Api.
            */

            $isUsingWeb = false;

            if(empty($user_key)) {
                $user_key = isset($_COOKIE['rememberme']) ? $_COOKIE['rememberme'] : '';
                $isUsingWeb = true;
            }

            $response['isLoggedIn'] = false;
            $response['user'] = [];

            if (!empty($user_key)) {
                list ($user_id, $token, $mac) = explode(':', $user_key);
                if (!hash_equals(hash_hmac('sha256', $user_id . ':' . $token, SECRET_KEY), $mac)) {
                    //user has tampered with the 'mac' value on the client side
                    $response['isLoggedIn'] = false;
                }
                $usertoken = $this->fetchTokenByUid($user_id, $isUsingWeb);
                if (hash_equals($usertoken, $token)) {
                    $response['isLoggedIn'] = true;
                    $response['user'] = $this->getUserDataByUid($user_id);
                }
            }
            return $response;
        }
        public function deleteWebLoginToken($user_id){
            setcookie('rememberme', '', time() - 3600, '/');
            $query = "UPDATE users SET web_login_token = '' WHERE user_id = ?";
            $stmt = $this->con->prepare($query);
            $stmt->bind_param("s", $user_id);
            if($stmt->execute())
                return true;
            else
                return false;
        }



        //---Feed and posts---//

        public function getFeed($user_key, $offset){
            $limit = 10;

            $query = 
                "SELECT posts.*, users.username 
                    FROM posts, users 
                    WHERE users.user_id = posts.created_by_uid
                    ORDER BY posts.creation_time DESC 
                    LIMIT ?,?";
            $stmt = $this->con->prepare($query);
            $stmt->bind_param("ss",$offset,$limit);
			$stmt->execute();
            $result = $stmt->get_result();
            
            $user_id = "";
            $user = [];
            $validate_response = $this->validateUserKey($user_key);
            if($validate_response['isLoggedIn']) {
                $user_id = $validate_response['user']['user_id'];
                $user = $validate_response['user'];
            }
            $response['isLoggedIn'] = $validate_response['isLoggedIn'];
            $response['user'] = $user;
            
            $i = 0;
            $posts = [];
            while($data = $result->fetch_array(MYSQLI_BOTH)){
                $post = [];
                $post['post_id'] = $data['post_id'];
                $post['username'] = $this->xssProofString('@' . $data['username']);
                $post['created_by_uid'] = $data['created_by_uid'];
                $post['image'] = empty($data['image']) ? "" : IMAGE_BASE_URL . $data['image'];
                $post['description'] = $this->xssProofString($data['description']);
                $post['creation_time'] = $this->getTimeAgo(($data['creation_time'] / 1000)); //for full date and time: date("d-m-Y h:i", ($data['creation_time'] / 1000));
                $post['did_user_create_post'] = ($user_id == $data['created_by_uid'] && $user_id != "");

                $posts[$i] = $post;
                $i++;
            }

            if(count($posts) <= 0) {
                $response['error'] = true;
                $response['type'] = "NO_POSTS";
                $response['message'] = "No posts found, be the first to post.";
                $response['posts'] = [];
            } else {
                $response['error'] = false;
                $response['type'] = "SUCCESS";
                $response['message'] = "";
                $response['posts'] = $posts;
            }

            return $response;
        }
        public function createPost($user_key, $image, $description){
            $response['error'] = true;
            $response['message'] = "Unknonw error occurred.";

            $validate_response = $this->validateUserKey($user_key);
            
            if($validate_response['isLoggedIn']) {
                $user_id = $validate_response['user']['user_id'];
            } else {
                if(!empty($image))
                    unlink('../images/' . $image);
                $response['message'] = "You are not logged in!";
                return $response;
            }
        
            if(empty($image) && empty($description)){
                $response['message'] = "You have to add a description and/or an image to your post!";
                return $response;
            }
            if(strlen($description) > 300 || substr_count($description,"\n") > 5) {
                $response['message'] = "The description is too long, please keep it 300 characters or less, and 5 lines or less.";
                return $response;
            }

            $curTime = $this->getCurTimeInMilli();
            $query = "INSERT INTO posts 
                        (created_by_uid, image, description, creation_time) 
                        VALUES(?,?,?,?)";
            $stmt = $this->con->prepare($query);
            $stmt->bind_param("ssss", $user_id, $image, $description, $curTime);
            if($stmt->execute()){
                $response['error'] = false;
                $response['message'] = "Post created successfully!";
            }
            return $response;
        }
        public function deletePost($user_key, $post_id){
            $response['error'] = true;
            $response['message'] = "Unknonw error occurred.";
            
            $validate_response = $this->validateUserKey($user_key);
            
            if($validate_response['isLoggedIn']) {
                $user_id = $validate_response['user']['user_id'];
            } else {
                $response['message'] = "You are not logged in!";
                return $response;
            }

            if($this->didUserCreatePost($user_id, $post_id)) {
                $image = $this->getImageOfPost($post_id);
                if(!empty($image))
                    unlink('../images/' . $image);

                $stmt = $this->con->prepare("DELETE FROM posts WHERE post_id = ?");
                $stmt->bind_param("s",$post_id);
                if($stmt->execute()) {
                    $response['error'] = false;
                    $response['message'] = "Post successfully deleted!";
                }
            } else {
                $response['message'] = "Unauthorized edit, you can only delete your own posts.";
            }
            
            return $response;
        }



        //---Utilities---//

        private function getUserDataByUid($user_id) {
			$stmt = $this->con->prepare("SELECT email, username FROM users WHERE user_id = ?");
			$stmt->bind_param("s",$user_id);
			$stmt->execute();
			$stmt->bind_result($email, $username);
			$stmt->fetch();
			$user['user_id'] = $user_id;
			$user['username'] = $username;
			$user['email'] = $email;
			return $user;
        }

		private function doesEmailExist($email){
			$stmt = $this->con->prepare("SELECT * FROM users WHERE email = ?");
			$stmt->bind_param("s",$email);
			$stmt->execute();
			$stmt->store_result();
			return $stmt->num_rows > 0;
        }
		private function doesUidExist($user_id){
			$stmt = $this->con->prepare("SELECT * FROM users WHERE user_id = ?");
			$stmt->bind_param("s",$user_id);
			$stmt->execute();
			$stmt->store_result();
			return $stmt->num_rows > 0;
        }
        private function doesUsernameExist($username){
            $stmt = $this->con->prepare("SELECT * FROM users WHERE username = ?");
			$stmt->bind_param("s",$username);
			$stmt->execute();
			$stmt->store_result();
			return $stmt->num_rows > 0;
        }

        private function generateRandomToken(){
            $secure = true;
            return bin2hex(openssl_random_pseudo_bytes(256, $yes));        
        }
        private function fetchTokenByUid($user_id, $getWebToken){
            $query = "SELECT " . ($getWebToken ? "web_login_token" : "app_login_token") . " FROM users WHERE user_id = ?";
            $stmt = $this->con->prepare($query);
            $stmt->bind_param("s",$user_id);
            $stmt->execute();
            $stmt->bind_result($token);
            $stmt->fetch();
            return $token;
        }

        private function getCurTimeInMilli(){
            return round(microtime(true) * 1000);
        }

        private function didUserCreatePost($user_id, $post_id){
            $query = "SELECT * FROM users, posts 
                        WHERE 
                            users.user_id = posts.created_by_uid 
                            AND users.user_id = ?
                            AND posts.post_id = ?";
            $stmt = $this->con->prepare($query);
			$stmt->bind_param("ss", $user_id, $post_id);
			$stmt->execute();
			$stmt->store_result();
			return $stmt->num_rows > 0;
        }
        private function getImageOfPost($post_id){
            $query = "SELECT image FROM posts WHERE post_id = ?";
            $stmt = $this->con->prepare($query);
            $stmt->bind_param("s",$post_id);
            $stmt->execute();
            $stmt->bind_result($image);
            $stmt->fetch();
            return $image;
        }
        
        private function getTimeAgo($time) {
            $time_difference = time() - $time;
            if($time_difference < 1 ) { return /*less than*/ '1 second ago'; }
            $condition = array( 12 * 30 * 24 * 60 * 60 =>  'year',
                        30 * 24 * 60 * 60       =>  'month',
                        24 * 60 * 60            =>  'day',
                        60 * 60                 =>  'hour',
                        60                      =>  'minute',
                        1                       =>  'second'
            );
            foreach( $condition as $secs => $str ) {
                $d = $time_difference / $secs;
                if( $d >= 1 ) {
                    $t = round( $d );
                    return /*'about ' .*/ $t . ' ' . $str . ( $t > 1 ? 's' : '' ) . ' ago';
                }
            }
        }
        
        private function xssProofString($string){
            return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
        }

	}

?>