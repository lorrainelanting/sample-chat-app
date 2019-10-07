<?php
	session_start();

	if ($_SERVER["REQUEST_METHOD"] != "GET") {
		http_response_code(405); // invalid method
		return;
	}

	if (!isLogIn()) {

		http_response_code(401); // not authorized
		return;
	} 

	// if (!isset($_POST["message"]) || $_POST["message"] == "") {

	// 	http_response_code(400); // bad request
	// 	return;
	// }

	if (!connectToDB()) {

		http_response_code(500); // server error
		return;
	} 

	if (isset($_GET["startId"])) {
		fetchMessages($_GET["startId"]);
	} else {
		fetchMessages();
	}

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

	function fetchMessages($startId = -1) {
		$limit = $startId > -1 ? 1 : 10; // getLimit($startId) return 1 or 10
		$select_sql = "";

		if ($startId > -1 && isset($_SESSION["username"])) {
			$select_sql = "SELECT users.name as 'sender', messages.id, users.username, messages.sender_id, messages.message, messages.date_created as 'date_sent' FROM `messages`, `users` WHERE messages.sender_id=users.id AND users.username!='" . $_SESSION["username"] . "' AND  messages.id>" . $startId . " ORDER BY messages.id DESC LIMIT " . $limit;
			// echo $select_sql;
		} else {
			$select_sql = "SELECT users.name as 'sender', messages.id, users.username, messages.sender_id, messages.message, messages.date_created as 'date_sent' FROM `messages`, `users` WHERE messages.sender_id=users.id AND messages.id>" . $startId . " ORDER BY messages.id DESC LIMIT " . $limit; 
		}

		$result = $GLOBALS["connection"]->query($select_sql);
		
		$response = '{"messages": [';
		if ($result->num_rows > 0) {
			while ($message = $result->fetch_assoc()) {
				$row = '{'.
				'"sender": {' .
					'"id": ' . $message["sender_id"] . ',' .
					'"name": "' . $message["sender"] . '"'
				. '},' .
				'"chat": {' . 
					'"id": "' . $message["id"] . '",' .
					'"message": "' . $message["message"] . '",' .
					'"date_sent": "' . $message["date_sent"] . '"'
				. "}"
			. "},";
			$response = $response . $row;
			}

			$response = substr($response, 0, -1);
		}

		$response = $response . ']}'; //'{"messages": [] }'
		echo preg_replace("/[\n\r]/", "", $response);
	}




	function disconnectFromDB() {
			$GLOBALS['connection']->close();
		}

	function isLogIn() {
		return isset($_SESSION["username"]) && isset($_SESSION["name"]);
	}

	disconnectFromDB();
?>