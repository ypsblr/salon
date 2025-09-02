
	<!-- plugin scripts -->
	<script src="plugin/jquery/dist/jquery.min.js"></script>
	<script src="plugin/jquery-ui/jquery-ui.min.js"></script>
	<script src="plugin/slimScroll/jquery.slimscroll.min.js"></script>
	<script src="plugin/bootstrap/dist/js/bootstrap.min.js"></script>
	<script src="plugin/metisMenu/dist/metisMenu.min.js"></script>
	<script src="plugin/iCheck/icheck.min.js"></script>
	<script src="plugin/sparkline/index.js"></script>
	<script src="plugin/swal/js/sweetalert.min.js"></script>

	<!-- App scripts -->
	<script src="custom/js/homer.js"></script>

<?php
	//Show error message set in $_SESSION['err_msg'];
	if (isset($_SESSION['err_msg']) && $_SESSION['err_msg'] != "") {
?>
	<script>
		$(document).ready(function() {
			swal({
				title: 'Error',
				text:  '<?php echo $_SESSION['err_msg']; ?>',
				icon: "error",
				button: 'Dismiss'
			});
		});
	</script>
<?php
	}
	unset($_SESSION['err_msg']);
?>

<?php
	//Show error message set in $_SESSION['err_msg'];
	if (isset($_SESSION['success_msg']) && $_SESSION['success_msg'] != "") {
?>
	<script>
		$(document).ready(function() {
			swal({
				title: 'Success',
				text:  '<?php echo $_SESSION['success_msg']; ?>',
				icon: "success",
				button: 'Great'
			});
		});
	</script>
<?php
	}
	unset($_SESSION['success_msg']);
?>

<?php
	//Show error message set in $_SESSION['err_msg'];
	if (isset($_SESSION['info_msg']) && $_SESSION['info_msg'] != "") {
?>
	<script>
		$(document).ready(function() {
			swal({
				title: 'Information',
				text:  '<?php echo $_SESSION['info_msg']; ?>',
				icon: "info",
				button: 'Thanks'
			});
		});
	</script>
<?php
	}
	unset($_SESSION['info_msg']);
?>

<!-- Keep Session Alive -->
<?php
	if ( isset($_SESSION['jury_id']) && isset($_SESSION['jury_type']) && isset($_SESSION['jury_yearmonth']) ) {
?>
	<script>
		var session_timer;

		function validate_session_status() {
			$.ajax({
				url: "ajax/keep_session_alive.php",
				type: "POST",
				data: {
					jury_id : "<?= $_SESSION['jury_id'];?>",
					jury_type : "<?= $_SESSION['jury_type'];?>",
					jury_yearmonth : '<?= $_SESSION['jury_yearmonth'];?>',
					session : '<?= json_encode($_SESSION);?>',
				},
				cache: false,
				success: function(response) {
					data = JSON.parse(response);
					if (! data.success) {
						console.log(data.msg);
					}
				},
			});
		}

		// Start Keep Alive process
		$(document).ready(function() {
			validate_session_status();			// Run once immediately
			session_timer = setInterval(validate_session_status, 5*60*1000);	// Run every 5 minutes to keep session variables alive
		});
	</script>
<?php
	}
?>
