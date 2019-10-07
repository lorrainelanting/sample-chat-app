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

	if (!isset($_POST["message"]) || $_POST["message"] == "") {

		http_response_code(400); // bad request
		return;
	}

	if (!connectToDB()) {

		http_response_code(500); // server error
		return;
	}

	sendMessage($_POST["message"]);

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

	function sendMessage($msg) {
		$select_sql = "SELECT `id` FROM `users` WHERE users.username='" . $_SESSION["username"] . "'";
		
		$result = $GLOBALS["connection"]->query($select_sql);
		if ($result->num_rows > 0) {
			$user = $result->fetch_assoc();
			
			$insert_sql = "INSERT INTO `messages`(`message`, `sender_id`) VALUES ('" . $msg . "','" . $user["id"] . "')";

			if ($GLOBALS['connection']->query($insert_sql)) {
				fetchMessage($GLOBALS['connection']->insert_id);
			} else {
				http_response_code(400); // bad request
			}

		} else {
			http_response_code(401); // not authorized
		}
	}

	function fetchMessage($id) {
		$select_sql = "SELECT users.name as 'sender', messages.id, users.username, messages.sender_id, messages.message, messages.date_created as 'date_sent' FROM `messages`, `users` WHERE messages.sender_id=users.id AND messages.id=" . $id . " ORDER BY messages.id DESC LIMIT 1"; 

		$result = $GLOBALS["connection"]->query($select_sql);
		
		if ($result->num_rows > 0) {
			$message = $result->fetch_assoc();
			
			$response = '{'.
				'"sender": {' .
					'"id": ' . $message["sender_id"] . ',' .
					'"name": "' . $message["sender"] . '"'
				. '},' .
				'"chat": {' . 
					'"id": "' . $message["id"] . '",' .
					'"message": "' . $message["message"] . '",' .
					'"date_sent": "' . $message["date_sent"] . '"'
				. "}"
			. "}";
			echo preg_replace("/[\n\r]/", "", $response);
		} else {
			http_response_code(410); // wala
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
