

	<?php include 'snippets/header.php'; ?>

    <body>
        <?php include 'snippets/navbar.php'; ?>
        <div id="main-content">
        </div>
        <div id="loading-div" style="margin-bottom: 25px;">
			<center>
				<h5 style="text-align:center; display: inline-block;">
                    <i class="fa fa-circle-o-notch fa-spin" style="display: inline-block; font-size:24px"></i> Loading...
                </h5>
			</center>
        </div>


        <div class="modal fade" id="modal-parent" role="dialog" style="color:black;">
          <div class="modal-dialog">

            <!-- Modal content-->
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="modal-title"><b>Are you sure?</b></h5>
              </div>
              <div class="modal-body" id="modal-body" style="margin:0 auto;">
                <p><b>Deleting this post will delete any and all info related to it, such as its image and description.</b></p>
              </div>
              <div class="modal-footer" id="modal-footer">
                <button type="button" class="btn btn-danger" id="modal-delete-btn" data-dismiss="modal">Delete</button>
                <button type="button" class="btn btn-success" id="modal-cancel-btn" data-dismiss="modal">Cancel</button>
              </div>
            </div>

          </div>
        </div>
    </body>
    
    <script src="js/feed.js"></script>

</html>

