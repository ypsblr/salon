<?php
// header("Location: viewer.html?file=/salons/" .$_REQUEST['id'] . "/files/" . $_REQUEST['catalog'] . "#magazineMode=true");
// printf("<script>location.href='viewer.html?file=/salons/" .$_REQUEST['id'] . "/files/" . $_REQUEST['catalog'] . "#magazineMode=true'</script>");
header("Location: viewer.html?file=/catalog/" . $_REQUEST['catalog'] . "#magazineMode=true");
printf("<script>location.href='viewer.html?file=/catalog/" . $_REQUEST['catalog'] . "#magazineMode=true'</script>");
?>
