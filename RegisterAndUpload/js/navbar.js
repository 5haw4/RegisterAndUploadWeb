

    $(window).ready(function(){
        var feedNav = $("#feed-li");
        var createPostNav = $("#create-post-li");
        var signInOutNav = $("#sign-in-out-li");
        var loggedInAsNav = $("#logged-in-as-li");

        feedNav.show();

        signInOutNav.on("click", function(){
            if(signInOutNav.children("a").html() == "Login") {
                window.location = 'index.php';
            } else {
                $.ajax({
                    url: 'php/delete_web_login_token.php',
                    method: 'GET',
                    complete: function(){
                        window.location = 'feed.php';
                    }
                });
            }
        });

        $.ajax({
            url: 'php/get_user_info.php',
            method: 'GET',
            success: function(response){
                response = JSON.parse(response);
                signInOutNav.show();
                if(response['isLoggedIn']) {
                    var user = response['user'];
                    loggedInAsNav.children("a").html("Welcome, " + user['username']);
                    loggedInAsNav.show();
                    signInOutNav.children("a").html("Sign Out");
                    createPostNav.show();
                } else {
                    loggedInAsNav.hide();
                    signInOutNav.children("a").html("Login");
                }
            }
        });



    });