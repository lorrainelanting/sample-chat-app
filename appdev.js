document.addEventListener("DOMContentLoaded", function() {
	// VIEW BINDINGS
	var dataSource = {
		errorMessage : "",
		successMessage : "",
		messages : [], //all message in chat room
		message : "", //specific message in textbox
		userId : null,
		checkedMessages : [] // = onEvent
	}

	initializedViews();
	

	function updateTextBox() {
		document.getElementById("type_msg").value = dataSource.message;

	}

	function updateViews() {
		updateTextBox();
		updateErrorMessage();
		updateSuccessMessage();
		updateMessages();
	}

	function updateErrorMessage() {
		document.getElementById("error-msg").innerHTML = dataSource.errorMessage;
	}

	function updateSuccessMessage() {
		document.getElementById("success-msg").innerHTML = dataSource.successMessage;
	}

	function updateMessages() {
		
		document.getElementById("messages_table").innerHTML = "";
		
		for (var i = 0; i < dataSource.messages.length ; i++) {
			var message = dataSource.messages[i];
			var row = document.createElement("tr");
			var checkboxColumn = document.createElement("td");
			var userColumn = document.createElement("td");
			userColumn.innerHTML = message.sender.name;
			var messageColumn = document.createElement("td");
			messageColumn.innerHTML = message.chat.message;
			var dateCreatedColumn = document.createElement("td");
			dateCreatedColumn.innerHTML = message.chat.date_sent;
			
			row.appendChild(checkboxColumn);
			row.appendChild(userColumn);
			row.appendChild(messageColumn);
			row.appendChild(dateCreatedColumn);

			document.getElementById("messages_table").appendChild(row);
			console.log(dataSource.userId);
			
			if (message.sender.id == dataSource.userId) {
				var inputForCheckbox = document.createElement("input");
					inputForCheckbox.type = "checkbox";
					
				if (dataSource.checkedMessages.includes(message.chat.id)) {
					console.log(message.chat.id, "is checked")
					inputForCheckbox.checked = true;
					
				}
				let mid = message.chat.id
				inputForCheckbox.addEventListener("click", function() {
					if (event.srcElement.checked) {
						console.log(mid, "checked")
						addToCheckMessages(mid);
					} else {
						console.log(mid, "unchecked")
						removeFromCheckMessages(mid)
					}
				})


				checkboxColumn.appendChild(inputForCheckbox);
			}
		}
	}

	// INITIALIZER
	function initializedViews() {
		dataSource.userId = document.getElementById("userId").value;
		
		setInterval(function(){
			var latestMessage = getOtherUsersLatestMsg();
			if (latestMessage) {
				var startId = latestMessage.chat.id;
				fetchLatestMessages(startId);
			}
		}, 1000);

		document.getElementById("type_msg").addEventListener("keyup", function() {
			dataSource.message = document.getElementById("type_msg").value;
			

		});

		document.getElementById("send_btn").addEventListener("click", function() {
			sendMessage(dataSource.message);
			dataSource.message = "";
			updateTextBox();
		});

		document.getElementById("delete_msg").addEventListener("click", function() {
			let checkedMsgs = dataSource.checkedMessages;
			if (checkedMsgs.length == 0) {
				dataSource.errorMessage = "Error";
				updateErrorMessage();
				return;
			}

			deleteMessages();
		});

		updateViews();
		fetchMessages();	
	}

	// OPERATIONS
	function sendMessage(message) {
		var button = event.srcElement;
		button.innerHTML = "Sending";
		button.disabled = true;
		var xhttp = new XMLHttpRequest();
		xhttp.onreadystatechange = function() { 
			if (this.readyState == 4) {
				button.disabled = false;
				button.innerHTML = "Send";

				if (this.status == 200) {
					dataSource.successMessage = "Message sent.";
					console.log(this.responseText)
					var messageResponse = JSON.parse(this.responseText);
					dataSource.messages.push(messageResponse);

					if (dataSource.messages.length > 10) {
						dataSource.messages = dataSource.messages.slice(1, dataSource.messages.length)
					}
				} 
				updateViews();
				// TODO catch other status codes
			}
		};
		xhttp.open("POST", "api/send-message.php", true);
		xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded")
		xhttp.send("message=" + message);
	}

	function fetchMessages() {
		console.log("fetchMessages");
		var xhttp = new XMLHttpRequest();
		xhttp.onreadystatechange = function() { 
			if (this.readyState == 4) {

				if (this.status == 200) {
					dataSource.messages = [];
					var response = JSON.parse(this.responseText.replace("", ""));

					for (var i = response.messages.length - 1; i >= 0; i--) {
						var message = response.messages[i];
						dataSource.messages.push(message);
					}
				} 
				updateMessages();
			}
		};
		xhttp.open("GET", "api/get-messages.php", true);
		xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded")
		xhttp.send();
	}



	function fetchLatestMessages(startId) {
		var xhttp = new XMLHttpRequest();
		xhttp.onreadystatechange = function() { 
			if (this.readyState == 4) {

				if (this.status == 200) {
					
					var response = JSON.parse(this.responseText);
					
					if (response.messages.length > 0) {
						
						fetchMessages();
					}
					
				} 
			}
		};
		xhttp.open("GET", "api/get-messages.php?startId=" + startId, true);
		xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded")
		xhttp.send();
	}

	function getOtherUsersLatestMsg() {
		if (dataSource.messages.length == 0) {
			return null;
		} else {
			for (var i = dataSource.messages.length - 1; i >= 0; i--) {
				var message = dataSource.messages[i];
				if (dataSource.userId != message.sender.id) {
					return message;
				}
			}
		}
		return null;
	}


	
	function addToCheckMessages(messageId) {
		dataSource.checkedMessages.push(messageId);
	}

	function removeFromCheckMessages(messageId) {
		var indexValue = dataSource.checkedMessages.indexOf(messageId);
		dataSource.checkedMessages.splice(indexValue, 1)

	}

	function deleteMessages() {
		var deleteButton = event.srcElement;
		deleteButton.innerHTML = "Deleting";
		deleteButton.disabled = true;

		let checkedMessages = dataSource.checkedMessages;

		var xhttp = new XMLHttpRequest();
		xhttp.onreadystatechange = function() { 
			if (this.readyState == 4) {
				deleteButton.disabled = false;
				deleteButton.innerHTML = "Delete";

				if (this.status == 200) {
					var response = JSON.parse(this.responseText);
					dataSource.successMessage = response.message;
					dataSource.checkedMessages = [];
				} else {
					dataSource.errorMessage = "Failed to delete message."
				}
				fetchMessages();
				updateViews();
				// TODO catch other status codes
			}
		};
		xhttp.open("POST", "api/delete-messages.php", true);
		xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded")
		xhttp.send("messages=" + checkedMessages);
	}
	
});

