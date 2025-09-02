<?php
?>
<!-- MODAL Forms -->
<!-- Edit Contest Rules -->
<div class="modal" id="edit-posting-modal" tabindex="-1" role="dialog">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header" style="padding-top: 15px; padding-bottom: 15px;">
				<div class="row form-group">
					<div class="col-sm-10">
						<h4 class="modal-title"><small>Info on sending of <span id="posting-type-disp">Award</span> for <span id="posting-profile-name">Name</span></small></h4>
					</div>
					<div class="col-sm-2">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
				</div>
			</div>
			<div class="modal-body">
				<form role="form" method="post" id="edit-posting-form" name="edit_posting_form" action="#" enctype="multipart/form-data" >
					<input type="hidden" name="posting_yearmonth" id="posting-yearmonth" value="">
					<input type="hidden" name="posting_profile_id" id="posting-profile-id" value="">
					<input type="hidden" name="posting_type" id="posting-type" value="">
					<input type="hidden" name="posting_operation" id="posting-operation" value="">
					<input type="hidden" name="posting_currency" id="posting-currency" value="INR">
					<!-- Date of Posting -->
					<div class="row form-group">
						<div class="col-sm-12">
							<label for="posting-date">Date of Posting</label>
							<input type="date" name="posting_date" class="form-control" id="posting-date" >
						</div>
					</div>
					<!-- Bank Account Number -->
					<div class="row form-group" id="bank-account-disp">
						<div class="col-sm-12">
							<label for="bank_account">Bank Account Number</label>
							<input type="text" name="bank_account" class="form-control" id="bank-account" >
						</div>
					</div>
					<!-- Cash Award Amount -->
					<div class="row form-group" id="cash-award-disp">
						<div class="col-sm-12">
							<label for="cash_award">Cash Award Amount</label>
							<input type="number" name="cash_award" class="form-control" id="cash-award" >
						</div>
					</div>
					<!-- Tracking Number -->
					<div class="row form-group" id="tracking-no-disp">
						<div class="col-sm-12">
							<label for="tracking-no">Tracking No</label>
							<input type="text" name="tracking_no" class="form-control" id="tracking-no" >
						</div>
					</div>
					<!-- Tracking Website -->
					<div class="row form-group" id="tracking-website-disp">
						<div class="col-sm-12">
							<label for="tracking-website">Tracking Website</label>
							<input type="url" name="tracking_website" class="form-control" id="tracking-website" value="https://www.indiapost.gov.in" >
						</div>
					</div>
					<!-- Posting Operator -->
					<div class="row form-group">
						<div class="col-sm-12">
							<label for="tracking-website">Operator</label>
							<input type="text" name="post_operator" class="form-control" id="post-operator" value="India Posts" >
						</div>
					</div>
					<br><br>
					<div class="row form-group">
						<div class="col-sm-9">
							<input class="btn btn-primary pull-right" type="submit" id="posting-save" name="posting_save" value="Save">
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>
<!-- END OF MODAL FORM -->
