<?php
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

$sql = "SELECT username, password, name FROM users";
$result = $GLOBALS['connection']->query($sql);

if ($result->num_rows > 0) {
	while ($row = $result->fetch_assoc()) {
		// echo "Username: " . " " . $row["username"] . " " . "Password: " . " " . $row["password"] . " " . "Name: " . " " . $row["name"] . "<br>";
	}
} else {
	// echo "No Result";
}

?>

<?php
session_start();
?>

<html>
<head>
	
</head>

<body>
<h4>WELCOME TO OFFICE CHATBOX</h4>

<!-- LOG-IN Form -->
<form action="index.php" method="POST">
	<h5>Log-in To Account</h5>
	<label>Username</label>
	<input type="text" name="username" />
	<br>
	<label>Password</label>
	<input type="password" name="password" />
	<br>
	<input type="submit" name="log-in" value="Log-in">
</form>

<!--REGISTER Form -->
<form action="index.php" method="POST">
	<h5>Create Account</h5>
	<label>Name</label>
	<input type="text" name="last_name" placeholder="Last Name" />
	<input type="text" name="first_name" placeholder="First Name" />
	<input type="text" name="middle_name" placeholder="Middle Name" />
	<br>
	<label>Username</label>
	<input type="text" name="username" required />
	<br>
	<label>Password</label>
	<input type="password" name="password" required>
	<br>
	<label>Confirm Password</label>
	<input type="password" name="confirm-passw" required>
	<br>
	<input type="submit" name="submit">
</form>

<!--PHP CODES-->
<!-- To Cofirm Password -->
<?php
// Connection
$GLOBALS['connection'] = new mysqli($servername, $username, $password, $dbname);

function isValidToLogIn() {
	if (!isset($_POST["username"]) || $_POST["username"] == "" || !isset($_POST["password"]) || $_POST["password"] == "") {
		return false;	
	}
	return true;
}

function isSubmitLogIn() {
	if (isset($_POST["log-in"])) {
		return true;
	}
	return false;
}

function logIn() {
	$usern = $_POST["username"];
	$sql = 'SELECT username, password, name FROM users WHERE users.username="' . $usern . '" LIMIT 1';
	$result = $GLOBALS['connection']->query($sql);
	if ($result->num_rows > 0) {
		$user = $result->fetch_assoc();   
		
		if ($_POST["password"] != $user["password"]) {
			echo "Invalid Password";
		} else {
			$_SESSION["username"] = $user["username"];
			$_SESSION["name"] = $user["name"];
			redirectToUsersAcct();
		}	
	} else {
		echo "User does not exist";
	}
}

if (isSubmitLogIn()) {
	if (isValidToLogIn()) {

		logIn();
	} else {
		echo "User and Password is Required";
	}
}

// TO REGISTER
function confirmPassword() {
	if ($_POST["confirm-passw"] != $_POST["password"]){
			return false;
		} else {
			return true;
		} 
}

function isValid() {
	if ($_POST["last_name"] == "" || $_POST["first_name"] == "" || $_POST["middle_name"] == "" || 
		$_POST["username"] == "" || $_POST["password"] == "" || $_POST["confirm-passw"] == "") {
		return false;
	}
	return true;
}

function isSubmit() {
	if (isset($_POST["submit"])) {
		return true;
	}
	return false;
}

function register() {
	$name = $_POST["last_name"] . ", " . $_POST["first_name"] . " " . $_POST["middle_name"];
	$sql = 'INSERT INTO users(`username`, `password`, `name`) VALUES ("' . $_POST["username"] . '", "' . $_POST["password"] . '", "' . $name . '")';

		if ($GLOBALS['connection']->query($sql)) {
			$userId = $GLOBALS['connection']->insert_id;
		
			$sql = 'SELECT username, password, name FROM users WHERE users.id=' . $userId;
		
			$result = $GLOBALS['connection']->query($sql);
			
			if ($result->num_rows > 0) {
				$user = $result->fetch_assoc();   
				
				$_SESSION["username"] = $user["username"];
				$_SESSION["name"] = $user["name"];
				return true;
			}
		} 
		else {
			// echo $GLOBALS['connection']->error;
		} return false;
}

function redirectToUsersAcct() {
	header("Location:user-acct.php");
		exit;
	}
if (isSubmit()) {
	if (isValid()) {
		// if (confirmPassword()) {
		// 	redirectToUsersAcct(); 
		// } else {
		// 	echo "Password not match";
		// }
		if (register()) {
			
			redirectToUsersAcct();
		} else {
			echo "FAILED TO REGISTER";
		}
	// } else {
	// 	echo "Invalid";
	}
}

if (isset($_SESSION["username"]) && isset($_SESSION["name"])) {
	redirectToUsersAcct();
} else {
	echo "Please Log In";
}
$GLOBALS['connection']->close();
?>

</body>

</html>