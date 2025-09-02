<?php
include("inc/connect.php");

if (empty($_REQUEST['id']) ||  empty($_REQUEST['admin']) || $_REQUEST['admin'] != 'sm' )
	die("Sorry ! Not Understood !");

$sql = "SELECT * FROM profile WHERE profile_id = '" . $_REQUEST['id'] . "' ";
$query = mysqli_query($DBCON, $sql) or die("Hmmm ! Something is not right !");
if (mysqli_num_rows($query) == 0)
	die("Sorry ! Cannot !");
?>

<!DOCTYPE html>
<html lang="en">

<head>
	<title>Jumper</title>
</head>

<body>
	<form name="validate" id="validate" method="post" >
		<input type="hidden" name="id" value="<?=$_REQUEST['id'];?>" >
		<input type="hidden" name="admin" value="<?=$_REQUEST['admin'];?>" >
		<label>?  </label>
		<input type="password" name="auth" >
		<br><br>
		<input type="submit" id="go_jump" name="go_jump" value="Go" >
	</form>
	<!-- Form that will be used to submit data -->
	<form id="submission_form" name="submission_form" action="op/jump_me.php" method="post">
		<input type="hidden" name="ypsd" id="ypsd" value="" >
	</form>
</body>

<!-- Javascripts -->
<script src="plugin/jquery/js/jquery.min.js"></script>
<!-- Crypto Functions -->
<script src="plugin/cryptojs/cryptojs-aes.min.js"></script>
<script src="plugin/cryptojs/cryptojs-aes-format.js"></script>
<script>
	// Login Form
	$("#go_jump").click(function(e){
		// Prevent the main form from getting submitted
		e.preventDefault();

		// Gather Data
		let data = {
			id : $("#validate").find("input[name='id']").val(),
			admin : $("#validate").find("input[name='admin']").val(),
			auth : $("#validate").find("input[name='auth']").val(),
		}
		// Submit the Form
		// Encrypt
		let ypsd = CryptoJS.AES.encrypt(JSON.stringify(data), "UN7U/yzk&%6GQwAA", { format: CryptoJSAesJson }).toString();
		$("#ypsd").val(ypsd);
		$("#submission_form").submit();
	});
</script>
</html>
