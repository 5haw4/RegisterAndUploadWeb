

    $(window).ready(function(){
        document.title = "Create New Post";

        var descriptionInput = $('#description');
        var imagePreview = $('#image-preview');
        var fileInput = document.getElementById("file");
        var alertSpan = $('#alert-span');
        var postBtn = $('#post-btn');
        

        function showAlert(type, message){
            alertSpan.html('<div class="alert alert-' + type + '">' + message + '</div>');
        }

        var updatePreviewImage = function () {
            var reader = new FileReader();
        
            reader.onload = function (e) {
                // get loaded data and render thumbnail.
                imagePreview.attr("src",e.target.result);
            };
        
            // read the image file as a data URL.
            reader.readAsDataURL(this.files[0]);
        };

        var createPost = function(){
            var form_data = new FormData();
		    if(fileInput.files[0] != null) {
		    	var name = fileInput.files[0].name;
		    	var ext = name.split('.').pop().toLowerCase();
		    	if(jQuery.inArray(ext, ['png','jpg','jpeg']) == -1) {
                    showAlert("danger", "Invalid format, only 'png', 'jpg' and 'jpeg' formats are supported!");
                    return;
		    	}
		    	var oFReader = new FileReader();
		    	oFReader.readAsDataURL(document.getElementById("file").files[0]);
		    	var f = document.getElementById("file").files[0];
		    	var fsize = f.size||f.fileSize;
		    	if(fsize > 2000000) {
                    showAlert("danger", "Profile picture file size is too big!");
                    return;
		    	} else {
		    		form_data.append("file", document.getElementById('file').files[0]);
		    	}
		    }
            form_data.append("description", descriptionInput.val());

            
            postBtn.prop("disabled",true);
            postBtn.children('i').removeClass("fa-check");
			postBtn.children('i').addClass("fa-circle-o-notch");
			postBtn.children('i').addClass("fa-spin");
		    $.ajax({
		    	url: 'php/create_post.php',
		    	method: 'POST',
		    	dataType: 'text',
		    	processData: false,
		    	contentType: false,
		    	data: form_data,
		    	success: function(response){
                    response = JSON.parse(response);
                    showAlert(!response['error'] ? "success" : "danger", response['message']);
                    if(!response['error']) {
                        setTimeout(function() {
                            window.location='feed.php';
                        }, 1500);
                    } else {
                        postBtn.prop("disabled",false);
                    }
                }, error: function(){
                    showAlert("danger", 'Unknown error has occurred while creating your post.');
                    postBtn.prop("disabled",false);
                }, complete: function(){
                    postBtn.children('i').addClass("fa-check");
                    postBtn.children('i').removeClass("fa-circle-o-notch");
                    postBtn.children('i').removeClass("fa-spin");
                }
            });
        };
        
        $("#file").on('change',updatePreviewImage);
        postBtn.on("click", createPost);
	});
