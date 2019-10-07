<?php
	
	session_start();

	if ($_SERVER["REQUEST_METHOD"] != "POST") {
		http_response_code(405); // invalid method
		return;
	}

	if (!isLogIn()) {

		http_response_code(401); // not authorized
		return;
	} 

	if (!isset($_POST["messages"]) || $_POST["messages"] == "") {

		http_response_code(400); // bad request
		return;
	}

	if (!connectToDB()) {

		http_response_code(500); // server error
		return;
	}

	deleteMessages($_POST["messages"]);

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
			return false;
		}
		return true;
	}

	function deleteMessages($msgsId) {
		$delete_sql = "DELETE FROM `messages` WHERE id IN (" . $msgsId . ")";

		if ($GLOBALS['connection']->query($delete_sql)) {
			echo '{"success": true, "message": "Deleted successfuly!"}';
		} else {
			http_response_code(400); // bad request
		}
	}

	function disconnectFromDB() {
		$GLOBALS['connection']->close();
	}

	function isLogIn() {
		return isset($_SESSION["username"]) && isset($_SESSION["name"]);
	}

	disconnectFromDB();
?>