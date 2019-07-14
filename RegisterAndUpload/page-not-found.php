    
    <?php include 'snippets/header.php'; ?>

    <body>
        <?php include 'snippets/navbar.php'; ?>
        <div id="main-content">
            <center>
                <h5 style="text-align:center; display: inline-block; font-size:45px; font-weight:bold; margin-top: 5vh;">
                    <?php echo isset($_GET['error']) ? htmlspecialchars($_GET['error'], ENT_QUOTES, 'UTF-8') : "Unknown"; ?> Error
                </h5>
                <br>
                <h5 style="text-align:center; display: inline-block; font-size:30px;">
                    Are you lost?
                </h5>
                <br>
                <button onclick="window.location='feed.php'" 
                    class="btn btn-bg btn-warning" 
                    style="margin-top:15px;"><i class="fa fa-home"></i> Go Home</button>
            </center>
        </div>
    </body>

</html>

