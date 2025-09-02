<?php
?>
<!-- MODAL Forms -->
<!-- Edit Contest Rules -->
<div class="modal" id="edit_blob_modal" tabindex="-1" role="dialog">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header" style="padding-top: 15px; padding-bottom: 15px;">
				<div class="row form-group">
					<div class="col-sm-10">
						<h4 class="modal-title"><small>Editing <span id="blob_file_name">Blob</span></small></h4>
					</div>
					<div class="col-sm-2">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
				</div>
			</div>
			<div class="modal-body">
				<p id="blob-custom-tags"></p>
				<form role="form" method="post" id="edit_blob_form" name="edit_blob_form" action="#" enctype="multipart/form-data" >
					<input type="hidden" name="yearmonth" id="blob_yearmonth" value="">
					<input type="hidden" name="blob_type" id="blob_type" value="">
					<input type="hidden" name="blob_file" id="blob_file" value="">
					<input type="hidden" name="blob_input" id="blob_input" value="">
					<!-- Rules text area -->
					<div class="row form-group">
						<div class="col-sm-12">
							<textarea name="blob_content" class="form-control" id="blob_content" >Loading Content...</textarea>
						</div>
					</div>
					<br><br>
					<div class="row form-group">
						<div class="col-sm-9">
							<input class="btn btn-primary pull-right" type="submit" id="blob_save" name="blob-save" value="Save">
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>
<!-- END OF MODAL FORM -->
