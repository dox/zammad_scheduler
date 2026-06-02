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

function zammadTicketCreateRequest(ticketUID) {
	return new Promise(function(resolve, reject) {
		var formData = new FormData();
		formData.append("ticketUID", ticketUID);

		var request = new XMLHttpRequest();
		request.open("POST", "../actions/zammadTicketCreate.php", true);
		request.send(formData);

		request.onload = function() {
			if (request.status == 200) {
				resolve(ticketUID);
			} else {
				reject(ticketUID);
			}
		};

		request.onerror = function() {
			reject(ticketUID);
		};
	});
}

async function zammadTicketsCreateFromPreview(button, evt) {
	if (evt && typeof evt.preventDefault === 'function') {
		evt.preventDefault();
	}

	var feedback = document.getElementById('runAllFeedback');
	var previewDate = button ? button.getAttribute('data-preview-date') : '';
	var ticketUIDs = [];

	try {
		ticketUIDs = JSON.parse(button.getAttribute('data-ticket-uids') || '[]');
	} catch (error) {
		ticketUIDs = [];
	}

	if (ticketUIDs.length === 0) {
		if (feedback) {
			feedback.className = 'alert alert-info';
			feedback.textContent = 'There are no due tickets to run for this preview date.';
		}
		return false;
	}

	if (!window.confirm('Create ' + ticketUIDs.length + ' Zammad ticket' + (ticketUIDs.length === 1 ? '' : 's') + ' for ' + previewDate + '?')) {
		return false;
	}

	if (button) {
		button.disabled = true;
		button.setAttribute('aria-busy', 'true');
	}

	var createdCount = 0;
	var failedUIDs = [];

	if (feedback) {
		feedback.className = 'alert alert-warning';
		feedback.textContent = 'Creating 0 of ' + ticketUIDs.length + ' due tickets...';
	}

	for (var i = 0; i < ticketUIDs.length; i++) {
		try {
			await zammadTicketCreateRequest(ticketUIDs[i]);
			createdCount++;
		} catch (ticketUID) {
			failedUIDs.push(ticketUID);
		}

		if (feedback) {
			feedback.className = 'alert alert-warning';
			feedback.textContent = 'Creating ' + createdCount + ' of ' + ticketUIDs.length + ' due tickets...';
		}
	}

	if (button) {
		button.disabled = false;
		button.removeAttribute('aria-busy');
	}

	if (feedback) {
		if (failedUIDs.length > 0) {
			feedback.className = 'alert alert-danger';
			feedback.textContent = 'Created ' + createdCount + ' of ' + ticketUIDs.length + ' tickets. Failed ticket UID' + (failedUIDs.length === 1 ? ': ' : 's: ') + failedUIDs.join(', ') + '.';
		} else {
			feedback.className = 'alert alert-success';
			feedback.textContent = 'Created all ' + createdCount + ' due tickets in Zammad.';
		}
	}

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

document.addEventListener('DOMContentLoaded', function() {
	var loginForm = document.forms.loginSubmit;

	if (!loginForm) {
		return;
	}

	loginForm.addEventListener('submit', function() {
		var submitButton = loginForm.querySelector('button[type="submit"]');
		var buttonLabel = submitButton ? submitButton.querySelector('.login-button-label') : null;
		var buttonSpinner = submitButton ? submitButton.querySelector('.login-button-spinner') : null;

		if (!submitButton) {
			return;
		}

		submitButton.disabled = true;
		submitButton.setAttribute('aria-busy', 'true');

		if (buttonSpinner) {
			buttonSpinner.classList.remove('d-none');
		}

		if (buttonLabel) {
			buttonLabel.textContent = submitButton.getAttribute('data-loading-label') || 'Signing In...';
		}
	});
});
