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
	// debug_dump("SESSION", $_SESSION, __FILE__, __LINE__);
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
