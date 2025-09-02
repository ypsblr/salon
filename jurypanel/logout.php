<?php
session_unset();
session_destroy();
header("Location: index.php");
printf("<script>location.href='index.php'</script>");
?>
