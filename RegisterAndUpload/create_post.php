
    <?php 
        include 'includes/DbOperations.php';

        $db = new DbOperations();
        $validate_response = $db->validateUserKey("");

        if(!$validate_response['isLoggedIn']) {
            header('Location: feed.php');
        }

        include 'snippets/header.php'; 
    ?>
    
    <body>
        <?php include 'snippets/navbar.php'; ?>
        <div id="main-content">
            <div class="card-view card-view-bg">
                <h3 class="title-bg" id="title">Create Post:</h3>
                <p style="margin-left:15px; margin-right: 15px;"><b>Please note:</b><br>All your posts and images are publicly visible to all visitors.</p>
			    <div class="form-group">
			    	<label for="description">Description:</label>
			    	<textarea type="text" id="description" name="description" class="form-control" 
			    	    value="" placeholder="Enter a description"></textarea>
			    </div>
            
                <div style="margin-top:30px;">
			    	<center>
                        <img class="img-post" id="image-preview" src="" alt="">
			    		<br>
			    		<label><b>Choose an Image:</b></label>
			    		<br>
			    		<Input class="btn btn-link" style="margin-bottom:10px; margin-top:-10px;" 
			    			type="file" id="file" name="file">
			    	</center>
                </div>

                <b><span id="alert-span"></span></b>
			    <div class="form-group">
			    	<center>
                        <button class="btn btn-md btn-primary" id="post-btn"><i class="fa fa-check"></i> Create Post</button>
			    	</center>
                </div>
            </div>
        </div>
    </body>
    
    <script src="js/create_post.js"></script>

</html>
