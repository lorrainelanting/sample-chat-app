<?php
connectToDB();
// session_start();

function connectToDB() {
	$servername = "localhost";
	$username = "root";
	$password = "";
	$dbname = "office_chat";

	// Connection
	$GLOBALS['connection'] = new mysqli($servername, $username, $password, $dbname);

	// Check Connection
	if ($GLOBALS['connection']->connect_error) {
		die("Connection failed:" . $GLOBALS['connection']->connect_error);
	}
}

function disconnectFromDB() {
	$GLOBALS['connection']->close();
}



?>




<!DOCTYPE html>
<html>
<head>
	<title></title>
</head>
<body>

<form action="user-acct.php" method="POST">
	<input type="text" name="message" disabled>
	<input type="text" name="text-field" placeholder="Type Here..">
	<input type="submit" name="send" value="Send">
</form>

</body>
</html>

<?php
	disconnectFromDB();
?>