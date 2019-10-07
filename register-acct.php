<?php
if ($_POST["confirm-passw"] != $_POST["password"]){
	echo "Password not match";
} else{
	echo "Password Match";
}
?>

<!-- NOTES
-every user has a unique USERNAME
	-connect it to database. -->