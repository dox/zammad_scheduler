<?php
$tickets = new tickets();
$agentsClass = new agents();

?>

<div class="container">
	<?php
	$title = "<svg width=\"1em\" height=\"1em\"><use xlink:href=\"inc/icons.svg#tickets\"/></svg> Tickets";
	$subtitle = "Daily, weekly, monthly and yearly tickets that are auto-scheduled to appear on Zendesk.";
	$icons[] = array("class" => "btn-primary", "name" => "<svg width=\"1em\" height=\"1em\"><use xlink:href=\"inc/icons.svg#tickets\"/></svg> Add Ticket", "value" => "data-bs-toggle=\"modal\" data-bs-target=\"#ticketAddModal\"");

	echo makeTitle($title, $subtitle, $icons);
	?>

	<h1>Daily</h1>
	<p>These tasks will appear on Zendesk at 00:00 every day (Monday - Friday).</p>
	<?php
	foreach($tickets->getTickets('Daily') AS $ticket) {
		echo $tickets->ticketDisplay($ticket['uid']);
	}
	?>

	<h1 class="mt-3">Weekly</h1>
	<p>These tasks will appear on Zendesk at 00:00 every Monday morning.</p>
	<?php
	foreach($tickets->getTickets('Weekly') AS $ticket) {
		echo $tickets->ticketDisplay($ticket['uid']);
	}
	?>

	<h1 class="mt-3">Monthly</h1>
	<p>These tasks will appear on Zendesk at 00:00 on the 1st of every month.</p>
	<?php
	foreach($tickets->getTickets('Monthly') AS $ticket) {
		echo $tickets->ticketDisplay($ticket['uid']);
	}
	?>

	<h1 class="mt-3">Yearly</h1>
	<p>These tasks will appear on Zendesk at 00:00 once every year on the date(s) specified.</p>
	<?php
	foreach($tickets->getTickets('Yearly') AS $ticket) {
		echo $tickets->ticketDisplay($ticket['uid']);
	}
	?>
</div>



<!-- Modal -->
<div class="modal fade" id="ticketAddModal" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post">
			<div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="staticBackdropLabel">Add New Scheduled Ticket</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
				<div class="mb-3">
					<label for="inputSubject" class="form-label">Ticket Subject</label>
					<input type="text" class="form-control" id="inputSubject" name="inputSubject">
				</div>
				<div class="mb-3">
					<label for="inputBody" class="form-label">Ticket Body</label>
					<textarea class="form-control" rows="3" id="inputBody" name="inputBody"></textarea>
				</div>
				<div class="mb-3">
					<label for="inputType" class="form-label">Ticket Type</label>
					<select class="form-select" id="inputType" name="inputType">
						<option value="Question">Question</option>
						<option value="Problem">Problem</option>
						<option value="Task">Task</option>
					</select>
				</div>
				<div class="mb-3">
					<label for="inputPriority" class="form-label">Ticket Priority</label>
					<select class="form-select" id="inputPriority" name="inputPriority">
						<option value="Low">Low</option>
						<option value="Normal">Normal</option>
						<option value="High">High</option>
						<option value="Urgent">Urgent</option>
					</select>
				</div>
				<div class="mb-3">
					<label for="inputLoggedBy" class="form-label">Ticket Logged By</label>
					<select class="form-select" id="inputLoggedBy" name="inputLoggedBy">
						<?php
						foreach ($agentsClass->getAgents() AS $agent) {
							$agent = $agent->getValues();
							
							$output  = "<option value=\"" . $agent['id'] . "\">" . $agent['firstname'] . " " . $agent['lastname'] . "</option>";

							echo $output;
						}
						?>
					</select>
				</div>
				<div class="mb-3">
					<label for="inputAssignTo" class="form-label">Auto-assign To Agent</label>
					<select class="form-select" id="inputAssignTo" name="inputAssignTo">
						<?php
						foreach ($agentsClass->getAgents() AS $agent) {
							$agent = $agent->getValues();

							$output  = "<option value=\"" . $agent['id'] . "\">" . $agent['firstname'] . " " . $agent['lastname'] . "</option>";

							echo $output;
						}
						?>
					</select>
				</div>
				<div class="mb-3">
					<label for="inputCC" class="form-label">Ticket CC</label>
					<input type="text" class="form-control" id="inputCC" name="inputCC" aria-describedby="inputCCHelp">
					<div id="inputCCHelp" class="form-text">Comma-seperated list of email addresses to CC into this ticket.</div>
				</div>
				<div class="mb-3">
					<label for="inputTags" class="form-label">Ticket Tags</label>
					<input type="text" class="form-control" id="inputTags" name="inputTags" aria-describedby="inputTagsHelp">
					<div id="inputTagsHelp" class="form-text">Comma-seperated list of tags to include into this ticket.</div>
				</div>
				<div class="mb-3">
					<label for="inputFrequency" class="form-label">Ticket Frequency</label>
					<select class="form-select" id="inputFrequency" name="inputFrequency" onchange="toggleFrequency2()">
						<option value="Daily">Daily</option>
						<option value="Weekly">Weekly</option>
						<option value="Monthly">Monthly</option>
						<option value="Yearly">Yearly</option>
					</select>
				</div>
				<div class="mb-3" id="inputFrequency2Div" hidden>
					<label for="inputFrequency2" class="form-label">Yearly Frequency</label>
					<input type="text" class="form-control" id="inputFrequency2" name="inputFrequency2" aria-describedby="inputFrequency2Help">
					<div id="inputFrequency2Help" class="form-text">The day of the year you want this task to run, written in the format '<?php echo strtoupper(date('M-d'));?>' (with leading zeros).<br />Specify multiple dates by using a comma to separate them (no spaces!) like: '<?php echo strtoupper(date('M-d')) ."," . strtoupper(date('M-d',strtotime(' +1 day')));?>'.</div>
				</div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-link text-muted" data-bs-dismiss="modal">Close</button>
				<button type="submit" class="btn btn-primary"><svg width="1em" height="1em"><use xlink:href="inc/icons.svg#tickets"/></svg> Add Ticket</button>
      </div>
    </div>
		</form>
  </div>
</div>


