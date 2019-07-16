


    $(window).ready(function(){
        document.title  = "Feed";
        
        var mainContent = $('#main-content');
        var loadingDiv = $('#loading-div');
        var offset = 0;
        var limit = 10;
        var noMoreItems = false;
        var isLoading = false;
    
        $(window).on('scroll', function onScroll(){ 
            if($(window).scrollTop() == 
                $(document).height() - $(window).height()) { 
                loadFeed();
            }
        });

        function loadFeed(){
            if(isLoading || noMoreItems) {
                if(noMoreItems)
                    loadingDiv.hide();
                return; 
            }
            isLoading = true;
            loadingDiv.show();
            $.ajax({
                url: 'php/get_feed.php',
                method: 'POST',
                data: {
                    offset: offset
                },
                success: function(response){
                    response = JSON.parse(response);
                    if(!response['error'] || mainContent.children().length <= 0) 
                        mainContent.append(response['content']);

                    if(response['type'] == "NO_POSTS")
                        noMoreItems = true;
                    else 
                        offset += limit;
                    
                    setDeleteBtnListeners();
                }, error: function(){
                    if(mainContent.children().length <= 0)
                        mainContent.html("<div class='card-view center-text card-view-bg'><h5><b>Unknown error occurred.</b></h5></div>");
                }, complete: function(){
                    isLoading = false;
                    loadingDiv.hide();
                }
            });
        }
        loadFeed();

        function setDeleteBtnListeners(){
            $(".delete-post-btn").on("click",function(event){
                var modalParent = $("#modal-parent");
                var modalDeleteBtn = $("#modal-delete-btn");
                var dltBtn = $(this);

                modalDeleteBtn.on("click", ()=>deletePost(dltBtn));
                modalParent.modal();
            });
        }

        function deletePost(dltBtn) {
            var postId = dltBtn.attr("data-post-id");
            var alertSpan = $("#alert-span-" + postId);
            var cardViewPost = $("#card-view-post-" + postId);
            var isRemoving = false;
        
            dltBtn.prop("disabled",true);
            dltBtn.children('i').removeClass("fa-trash");
            dltBtn.children('i').addClass("fa-circle-o-notch");
            dltBtn.children('i').addClass("fa-spin");
            loadingDiv.show();
            $.ajax({
                url: 'php/delete_post.php',
                method: 'POST',
                data: {
                    post_id: postId
                },
                success: function(response){
                    response = JSON.parse(response);
                    if(!response['error']) {
                        offset--;
                        isRemoving = true;
                        cardViewPost.html(
                            '<div class="row post-title-row" style="margin-top:0;"> \
                                <h6 style="margin:0; padding:0;"><b>' + response['message'] + '</b></h6> \
                                <button class="btn btn-sm btn-link style="padding:0; margin:0; border:0;" \
                                    onclick="$(\'#card-view-post-' + postId + '\').fadeToggle(()=>$(\'#card-view-post-' + postId + '\').remove());">\
                                    <b>Hide</b>\
                                </button>\
                            </div>');
                    } else {
                        showAlert("danger",response['message']);
                    }
                }, error: function(){
                    isRemoving = false;
                    showAlert("danger","Unknown error occurred.");
                }, complete: function(){
                    dltBtn.prop("disabled",isRemoving);
                    dltBtn.children('i').addClass("fa-trash");
                    dltBtn.children('i').removeClass("fa-circle-o-notch");
                    dltBtn.children('i').removeClass("fa-spin");
                }
            });

            function showAlert(type, message){
                alertSpan.html('<div style="margin:15px;" class="alert alert-' + type + '">' + message + '</div>');
            }
        }

    });
    