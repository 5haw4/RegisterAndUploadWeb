

    $(window).ready(function(){
        document.title = "Register / Login";
        
        var title = $('#title');
        var usernameForm = $('#username-form');
        var usernameInput = $('#username');
        var emailInput = $('#email');
        var passwordInput = $('#password');
        var alertSpan = $('#alert-span');
        var actionBtn = $('#action-btn');
        var switchBtn = $('#switch-btn');


        actionBtn.on("click",function(){
            loginRegister();
        });

        switchBtn.on("click",function(){
            switchBtn.prop("disabled",true);
            usernameForm.fadeToggle(function(){
                title.html(isLoginIn() ? "Login" : "Register");
                actionBtn.html(isLoginIn() ? "Login" : "Register");
                switchBtn.html(!isLoginIn() ? "Login" : "Register");
                switchBtn.prop("disabled",false);
            });
        });

        function loginRegister(){
            actionBtn.prop("disabled",true);
            switchBtn.prop("disabled",true);
            $.ajax({
                url: 'php/register_login.php',
                method: 'POST',
                data: {
                    isLoginIn: isLoginIn(),
                    username: usernameInput.val(),
                    email: emailInput.val(),
                    password: passwordInput.val()
                },
                success: function(response){
                    response = JSON.parse(response);
                    if(!response['error']) {
                        showAlert("success",response['message']);
                        setTimeout(function() {
                            window.location='feed.php';
                        }, 1500);
                    } else {
                        showAlert("danger",response['message']);
                    }
                }, error: function(){
                    showAlert("danger","Something went wrong, please try again later.");
                }, complete: function(){
                    actionBtn.prop("disabled",false);
                    switchBtn.prop("disabled",false);
                }
            });
        }

        function showAlert(type, message){
            alertSpan.html('<div class="alert alert-' + type + '">' + message + '</div>');
        }

        function isLoginIn(){
            return !usernameForm.is(':visible');
        }

    });
