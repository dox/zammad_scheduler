function zammadTicketCreate(this_id) {	
	event.preventDefault();
	
	var ticketUID = this_id;
	
	var formData = new FormData();
	
	formData.append("ticketUID", ticketUID);
	
	//https://javascript.info/xmlhttprequest GREAT documentation!
	var request = new XMLHttpRequest();
	
	request.open("POST", "../actions/zammadTicketCreate.php", true);
	request.send(formData);
	
	// 4. This will be called after the response is received
	request.onload = function() {
		if (request.status != 200) { // analyze HTTP status of the response
			alert("Something went wrong.  Please refresh this page and try again.");
			alert(`Error ${request.status}: ${request.statusText}`); // e.g. 404: Not Found
		} else { // show the result
			//alert(`${request.status}: ${request.response}`); // e.g. 404: Not Found
			//location.href = 'index.php?n=orders_all';
		}
	};
			
	request.onerror = function() {
		alert("Request failed");
	};
		
	return false;
}

function zammadTicketUpdate(this_id) {	
	event.preventDefault();
	
	var ticketID = this_id;
	var ticketBody = document.getElementById('ticketBody').value;
	
	var formData = new FormData();
	
	formData.append("ticketID", ticketID);
	formData.append("ticketBody", ticketBody);
	
	//https://javascript.info/xmlhttprequest GREAT documentation!
	var request = new XMLHttpRequest();
	
	request.open("POST", "../actions/zammadTicketUpdate.php", true);
	request.send(formData);
	
	// 4. This will be called after the response is received
	request.onload = function() {
		if (request.status != 200) { // analyze HTTP status of the response
			alert("Something went wrong.  Please refresh this page and try again.");
			alert(`Error ${request.status}: ${request.statusText}`); // e.g. 404: Not Found
		} else { // show the result
			alert(`${request.status}: ${request.response}`); // e.g. 404: Not Found
			//location.href = 'index.php?n=orders_all';
		}
	};
			
	request.onerror = function() {
		alert("Request failed");
	};
		
	return false;
}