function zammadTicketCreate(this_id, evt) {	
	if (evt && typeof evt.preventDefault === 'function') {
		evt.preventDefault();
	}
	
	var ticketUID = this_id;
	var button = document.getElementById(ticketUID);
	var feedback = document.getElementById('runNowFeedback');
	var label = button ? button.querySelector('.run-now-label') : null;
	var idleLabel = button ? button.getAttribute('data-idle-label') : 'Run Now';
	var runningLabel = button ? button.getAttribute('data-running-label') : 'Running...';
	
	var formData = new FormData();
	
	formData.append("ticketUID", ticketUID);

	if (button) {
		button.disabled = true;
		button.classList.add('is-running');
		button.setAttribute('aria-busy', 'true');
	}

	if (label) {
		label.textContent = runningLabel;
	}

	if (feedback) {
		feedback.className = 'alert alert-warning';
		feedback.textContent = 'Creating ticket in Zammad...';
	}
	
	//https://javascript.info/xmlhttprequest GREAT documentation!
	var request = new XMLHttpRequest();
	
	request.open("POST", "../actions/zammadTicketCreate.php", true);
	request.send(formData);
	
	// 4. This will be called after the response is received
	request.onload = function() {
		if (button) {
			button.disabled = false;
			button.classList.remove('is-running');
			button.removeAttribute('aria-busy');
		}

		if (label) {
			label.textContent = idleLabel;
		}

		if (request.status != 200) { // analyze HTTP status of the response
			if (feedback) {
				feedback.className = 'alert alert-danger';
				feedback.textContent = "Something went wrong while running this ticket. Please try again.";
			}
		} else { // show the result
			if (feedback) {
				feedback.className = 'alert alert-success';
				feedback.textContent = 'Ticket created successfully in Zammad.';
			}
		}
	};
			
	request.onerror = function() {
		if (button) {
			button.disabled = false;
			button.classList.remove('is-running');
			button.removeAttribute('aria-busy');
		}

		if (label) {
			label.textContent = idleLabel;
		}

		if (feedback) {
			feedback.className = 'alert alert-danger';
			feedback.textContent = 'Request failed while contacting Zammad.';
		}
	};
		
	return false;
}

function zammadTicketUpdate(this_id, state, evt) {	
	if (evt && typeof evt.preventDefault === 'function') {
		evt.preventDefault();
	}
	
	var ticketID = this_id;
	var ticketBody = document.getElementById('ticketBody').value;
	var ticketOwner = document.getElementById('owner_id').value;
	
	var formData = new FormData();
	
	formData.append("ticketID", ticketID);
	formData.append("ticketBody", ticketBody);
	formData.append("ticketOwner", ticketOwner);
	formData.append("ticketState", state);
	
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
			//alert(`${request.status}: ${request.response}`); // e.g. 404: Not Found
			location.reload();
		}
	};
			
	request.onerror = function() {
		alert("Request failed");
	};
		
	return false;
}

function toggleFrequency2() {
	d = document.getElementById("inputFrequency").value;

	if (d == 'Yearly'){
		document.getElementById("inputFrequency2Div").removeAttribute("hidden");
	} else {
		document.getElementById("inputFrequency2Div").setAttribute("hidden", true);
	}
}
