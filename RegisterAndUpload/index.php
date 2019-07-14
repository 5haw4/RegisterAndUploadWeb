
	<?php 
		include 'includes/DbOperations.php';
		
		$db = new DbOperations();
		$validate_response = $db->validateUserKey("");
		
		if($validate_response['isLoggedIn']) {
			header('Location: feed.php');
		}

		include 'snippets/header.php'; 
	?>

	<body>
        <div class="card-view" id="main-content">
            <h3 class="title-bg" id="title">Login</h3>
			<div class="form-group" id="username-form">
				<label for="username">Username:</label>
				<input type="text" id="username" name="username" class="form-control" 
				value="" placeholder="Choose a username" />
			</div>
			<div class="form-group">
				<label for="email">Email:</label>
				<input type="email" id="email" name="email" class="form-control" 
				value="" placeholder="Enter your email" />
			</div>
			<div class="form-group">
				<label for="password">Password:</label>
				<input type="password" id="password" name="password" class="form-control"
				value="" placeholder="Enter a password" />
            </div>
            <b><span id="alert-span"></span></b>
			<div class="form-group">
				<center>
                    <button class="btn btn-md btn-primary" id="action-btn">Login</button>
                    <br>
                    <label> OR </label>
                    <br>
				    <button class="btn btn-md btn-primary" id="switch-btn">Register</button>
                    <br>
				    <button class="btn btn-md btn-link" onclick="window.location = 'feed.php'">Skip</button>
				</center>
			</div>
        <div>
    </body>
    
    <script src="js/register_login.js"></script>

</html>
