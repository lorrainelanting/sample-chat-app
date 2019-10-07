<?php

connectToDB();
session_start();

// SECURITY
redirectIfNotLoggedIn();

// FORM HANDLERS
handleLogOut();
handlePasswordUpdate();
handleProfileUpdateSubmission();
handleMessage();
handleDeleteMessage();
fetchMessages();

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

// SENDING MESSAGE
function isValidMessage() {
	if ($_POST["insert-msg"] != "") {
		return true;
	}
	return false;
}

function sendMessage() {
	$select_sql = "SELECT `id` FROM `users` WHERE users.username='" . $_SESSION["username"] . "'";
	
	$result = $GLOBALS["connection"]->query($select_sql);
	if ($result->num_rows > 0) {
		$user = $result->fetch_assoc();
		
		$insert_sql = "INSERT INTO `messages`(`message`, `sender_id`) VALUES ('" . $_POST["insert-msg"] . "','" . $user["id"] . "')";
		if ($GLOBALS['connection']->query($insert_sql)) {
			$GLOBALS["successMsg"] = "Message Sent";
		} else {
			$GLOBALS["errorMsg"] = "Message Not Sent";
		}

	} else {
		$GLOBALS["errorMsg"] = "Invalid Session";
	}
}

function isMessageSubmitted() {
	if (isset($_POST["send"])) {
		return true;
	}
	return false;
}

function handleMessage() {
	if (isMessageSubmitted()) {
		if (isValidMessage()) {
			sendMessage();
		} else {
			$GLOBALS["errorMsg"] = "Invalid Message";
		}
	}
}

function fetchMessages() {
	$select_sql = "SELECT users.name as 'sender', messages.id, users.username, messages.sender_id, messages.message, messages.date_created as 'date_sent' FROM `messages`, `users` WHERE messages.sender_id=users.id ORDER BY messages.id DESC LIMIT 10"; 

	$result = $GLOBALS["connection"]->query($select_sql);
	
	$GLOBALS["messages"] = [];
	if ($result->num_rows > 0) {
		while ($message = $result->fetch_assoc()) {
			array_push($GLOBALS["messages"], $message);
		}
	}
}

// DELETING MESSAGE

function isDeleteSubmitted() {
	if (isset($_POST["delete_msg"])) {
		return true;
	}
	return false;
}

function isValidToDelete(){
	if (isset($_POST["delete_markers"])) {
		return true;
	}
	return false;
}

function deleteMessages() {
	$ids = implode(",", $_POST["delete_markers"]);
	$delete_sql = "DELETE FROM `messages` WHERE id IN (" . $ids . ")";
		if ($GLOBALS["connection"]->query($delete_sql)) {
			$GLOBALS["successMsg"] = "Message deleted";
		} else {
			$GLOBALS["errorMsg"] = "Message not deleted";
		}
}

function handleDeleteMessage() {
	if (isDeleteSubmitted()) {
		if (isValidToDelete()) {
			deleteMessages();
		} else {
			$GLOBALS["errorMsg"] = "No Message Selected";
		}
	}
}
// UPDATE PROFILE
function updateProfile() {
	
	$name = $_POST["last_name"] . "," . " " . $_POST["first_name"] . " " . $_POST["m_i"];
	$sql = "UPDATE `users` SET `name`='". $name . "' WHERE users.username='" . $_SESSION["username"] . "'";
	if ($GLOBALS['connection']->query($sql)) {
		$_SESSION["name"] = $name;
	} else {
		$GLOBALS["errorMsg"] = "ERROR updating record: " . $$GLOBALS['connection']->error;
	}
}

function isValidProfileInput() {
	if ($_POST["last_name"] != "" || $_POST["first_name"] != "" || $_POST["m_i"] != "") {
		return true;
	} 
	return false;
}

function handleProfileUpdateSubmission() {
	if (isProfileUpdateSubmitted()) {
		if (isValidProfileInput()) {
			updateProfile();
		} else {
			$GLOBALS["errorMsg"] = "No any information to update";
		}
	}
}

function isProfileUpdateSubmitted() {
	if (isset($_POST["update"])) {
		return true;
	}
	return false;
}

function redirectToHomePage() {
	header("Location:index.php");
	exit;
}

function isLogIn() {
	return isset($_SESSION["username"]) && isset($_SESSION["name"]);
}

// TO UPDATE PASWWORD
function isValidPassword() {
	if ($_POST["current_pswd"] == "" || $_POST["new_pswd"] == "" || $_POST["confirm_pswd"] == "") {
		return false;
	}
	return true;
}

function isMatchPassword() {
	if ($_POST["confirm_pswd"] != $_POST["new_pswd"]) {
		$GLOBALS["errorMsg"] = "Password not Match";
		return false;
	} else {
		return true;
	}
}

function isUpdatePasswordSubmitted() {
	if (isset($_POST["update_pswd"])) {
		return true;
	}
	return false;
}

function updatePassword() {
	$sql = "UPDATE `users` SET `password`='" . $_POST["new_pswd"] . "' WHERE users.username='" . $_SESSION["username"] . "' AND users.password='" . $_POST["current_pswd"] . "'";
	
	$result = $GLOBALS['connection']->query($sql);
		
	if ($result) {
		$GLOBALS["successMsg"] = "Password updated";
	
	} else {
		$GLOBALS["errorMsg"] = "Failed to update password. Invalid input.";
	}
	
}

function handlePasswordUpdate() {
	if (isUpdatePasswordSubmitted()) {
		if (isValidPassword() && isMatchPassword()) {
			updatePassword();
		} else {
			$GLOBALS["errorMsg"] = "Invalid input";;
		}
	}
}

function redirectIfNotLoggedIn() {
	if (!isLogIn()) {
		redirectToHomePage();
	}
}

function handleLogOut() {
	if (isset($_POST["log_out"])) {
		session_destroy();
		redirectToHomePage();
	}
}

function showWelcome() {
	if (isset($_SESSION["name"])) {
		echo $_SESSION["name"];
	}
}

function showSuccessMsg() {
	if (isset($GLOBALS["successMsg"])) {

		echo $GLOBALS["successMsg"];
	}
}

function showError() {
	if (isset($GLOBALS["errorMsg"])) {

		echo $GLOBALS["errorMsg"];
	}
}

function getMessages() {
	if (isset($GLOBALS["messages"])) {
		return $GLOBALS["messages"];
	} else {
		return [];
	}
}

function getUserId() {
	$select_sql = "SELECT `id` FROM `users` WHERE users.username='" . $_SESSION["username"] . "'";
	
	$result = $GLOBALS["connection"]->query($select_sql);
	if ($result->num_rows > 0) {
		$user = $result->fetch_assoc();
		return $user["id"];
	}
	return -1;
}

?>

<!-- VIEW -->
<!DOCTYPE html>
<html>
<head>
	<title></title>
</head>
<body>

	<h2>Welcome 
		<?php 
			showWelcome();
		?>	
	</h2>
	<!-- INSERT MESSAGE FORM -->
	<h1 style="color: red" id="error-msg">
		
	</h1>

	<p style="color: green" id="success-msg">
		
	</p>

	<div>
		<div class="messages" id="chat_room">
			<table id="messages_table">
				
			</table>
		</div>
		<button type="button" id="delete_msg">Delete</button>

		<textarea name="insert-msg" id="type_msg"></textarea>
		<button type="button" id="send_btn">Send</button>
	</div>

	<br>
	<h4>Update Profile</h4>
	<form action="user-acct.php" method="POST">
		<input type="text" name="last_name" placeholder="Last Name" />
		<input type="text" name="first_name" placeholder="First Name" />
		<input type="text" name="m_i" placeholder="M.I" />
		<input type="submit" name="update" value="Update" />
		<input type="submit" name="log_out" value="Log Out"/>
	</form>
	<br>
	<!-- UPDATE PASSWORD FORM -->
	<form action="user-acct.php" method="POST">
		<h4>Update Password</h4>
		<input type="password" name="current_pswd" placeholder="Current Password" />
		<br>
		<input type="password" name="new_pswd" placeholder="Enter New Password" />
		<br>
		<input type="password" name="confirm_pswd" placeholder="Confirm New Password" />
		<br>
		<input type="submit" name="update_pswd" value="Update Password" />
	</form>
	<input type="hidden" id="userId" value="<?php echo getUserId(); ?>" />
	

	<script src="appdev.js">	
	
	</script>

</body>
</html>

<?php
	disconnectFromDB();
?>