<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<?php
$tickets = new tickets();
$ticket = $tickets->getTicket($_GET['job']);
$previousZammadTicket = $tickets->ticketValuesGetFromZammad($ticket->last_id);

//printArray($ticket);

$agentsClass = new agents();

if (!empty($_POST)) {
	$ticket_update['uid'] = $ticket->uid;
	$ticket_update['subject'] = $_POST['inputSubject'];
	$ticket_update['body'] = $_POST['inputBody'];
	$ticket_update['zammad_priority'] = $_POST['inputPriority'];
	$ticket_update['zammad_group'] = $_POST['inputGroup'];
	$ticket_update['tags'] = $_POST['inputTags'];
	$ticket_update['frequency'] = $_POST['inputFrequency'];
	$ticket_update['frequency2'] = str_replace(' ', '', strtoupper($_POST['inputFrequency2']));
	$ticket_update['zammad_agent'] = $_POST['inputAssignTo'];
	$ticket_update['zammad_customer'] = $_POST['inputLoggedBy'];
	$ticket_update['cc'] = $_POST['inputCC'];
	$ticket_update['status'] = $_POST['inputStatus'];
	
	if ($tickets->update($ticket_update)) {
		$messages[] = "<div class=\"alert alert-success\" role=\"alert\">Job Updated!</div>";
	} else {
		$messages[] = "<div class=\"alert alert-danger\" role=\"alert\">Something went wrong, please contact IT Support</div>";
	}
	
	$ticket = $tickets->getTicket($_GET['job']);
}
?>

<div class="container">
	<?php
	$title = "<svg width=\"1em\" height=\"1em\"><use xlink:href=\"inc/icons.svg#tickets\"/></svg> Ticket ID: " . $ticket->uid;
	$subtitle = $ticket->subject;
	$icons[] = array("class" => "btn-warning", "name" => "<svg width=\"1em\" height=\"1em\"><use xlink:href=\"inc/icons.svg#run-now\"/></svg> Run Now", "value" => "onclick=\"zammadTicketCreate(this.id);\" id=\"" . $ticket->uid . "\"");
	$icons[] = array("class" => "btn-danger", "name" => "<svg width=\"1em\" height=\"1em\"><use xlink:href=\"inc/icons.svg#delete\"/></svg> Delete Ticket", "value" => "data-bs-toggle=\"modal\" data-bs-target=\"#ticketDeleteModal\"");

	echo makeTitle($title, $subtitle, $icons);
	
	if (isset($previousZammadTicket['id']) && $previousZammadTicket['state'] != 'closed') {
		$url = zammad_url . "/#ticket/zoom/" . $previousZammadTicket['id'];
		
		echo "<div class=\"alert alert-primary\" role=\"alert\">";
		echo "The previous run of this ticket has not yet been closed<br />";
		echo "ID: " . $previousZammadTicket['id'] . " created on " . $previousZammadTicket['created_at'] . "<br />";
		echo "<a href=\"" . $url . "\">" . $url . "</a>";
		echo "</div>";
		//printArray($previousZammadTicket);
	}
	?>

	<form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post">
	<div class="mb-3">
		<label for="inputSubject" class="form-label">Ticket Subject</label>
		<input type="text" class="form-control" id="inputSubject" name="inputSubject" value="<?php echo $ticket->subject; ?>">
	</div>
	<div class="mb-3">
		<label for="inputBody" class="form-label">Ticket Body</label>
		<textarea class="form-control" rows="3" id="inputBody" name="inputBody"><?php echo $ticket->body; ?></textarea>
	</div>
	<div class="row g-12">
		<div class="col-md-4 mb-3">
			<label for="inputGroup" class="form-label">Ticket Group</label>
			<select class="form-select" id="inputGroup" name="inputGroup">
				<?php
				foreach ($agentsClass->groups() AS $groupID => $groupName) {
					$output  = "<option value=\"" . $groupID . "\"";
					if ($groupID == $ticket->zammad_group) {
						$output .= " selected ";
					}
					$output .= ">" . $groupName . "</option>";

					echo $output;
				}
				?>
			</select>
		</div>
		<div class="col-md-4 mb-3">
			<label for="inputPriority" class="form-label">Ticket Priority</label>
			<select class="form-select" id="inputPriority" name="inputPriority">
				<option value="1" <?php if ($ticket->zammad_priority == "1") { echo " selected";}?>>1 Low</option>
				<option value="2" <?php if ($ticket->zammad_priority == "2") { echo " selected";}?>>2 Normal</option>
				<option value="3" <?php if ($ticket->zammad_priority == "3") { echo " selected";}?>>3 High</option>
			</select>
		</div>
	</div>
	<div class="mb-3">
		<label for="inputLoggedBy" class="form-label">Zammad Customer</label>
		<select class="form-select" id="inputLoggedBy" name="inputLoggedBy">
			<?php
			foreach ($agentsClass->getZammadAgents() AS $agent) {
				$output  = "<option value=\"" . $agent['id'] . "\"";
				if ($ticket->zammad_customer == $agent['id']) {
					$output .= " selected";
				}
				$output .= ">" . $agent['firstname'] . " " . $agent['lastname'] . "</option>";

				echo $output;
			}
			?>
		</select>
	</div>
	<div class="mb-3">
		<label for="inputAssignTo" class="form-label">Zammad Agent</label>
		<select class="form-select" id="inputAssignTo" name="inputAssignTo">
			<?php
			foreach ($agentsClass->getZammadAgents() AS $agent) {
				$output  = "<option value=\"" . $agent['id'] . "\"";
				if ($ticket->zammad_agent == $agent['id']) {
					$output .= " selected";
				}
				$output .= ">" . $agent['firstname'] . " " . $agent['lastname'] . "</option>";

				echo $output;
			}
			?>
		</select>
	</div>
	<div class="mb-3">
		<label for="inputCC" class="form-label">Ticket CC</label>
		<input type="text" class="form-control" id="inputCC" name="inputCC" aria-describedby="inputCCHelp" value="<?php echo $ticket->cc ?>">
		<div id="inputCCHelp" class="form-text">Comma-seperated list of email addresses to CC into this ticket.</div>
	</div>
	<div class="mb-3">
		<label for="inputTags" class="form-label">Ticket Tags</label>
		<input type="text" class="form-control" id="inputTags" name="inputTags" aria-describedby="inputTagsHelp" value="<?php echo $ticket->tags; ?>">
		<div id="inputTagsHelp" class="form-text">Comma-seperated list of tags to include into this ticket.</div>
	</div>
	<div class="mb-3">
		<label for="inputFrequency" class="form-label">Ticket Frequency</label>
		<select class="form-select" id="inputFrequency" name="inputFrequency" onchange="toggleFrequency2()">
			<option value="Daily" <?php if ($ticket->frequency == "Daily") { echo " selected";}?>>Daily</option>
			<option value="Weekly" <?php if ($ticket->frequency == "Weekly") { echo " selected";}?>>Weekly</option>
			<option value="Monthly" <?php if ($ticket->frequency == "Monthly") { echo " selected";}?>>Monthly</option>
			<option value="Yearly" <?php if ($ticket->frequency == "Yearly") { echo " selected";}?>>Yearly</option>
		</select>
	</div>
	<div class="mb-3" id="inputFrequency2Div" <?php if ($ticket->frequency <> 'Yearly') { echo 'hidden'; } ?>>
		<label for="inputFrequency2" class="form-label">Yearly Frequency</label>
		<input type="text" class="form-control" id="inputFrequency2" name="inputFrequency2" aria-describedby="inputFrequency2Help" value="<?php echo strtoupper($ticket->frequency2); ?>">
		<div id="inputFrequency2Help" class="form-text">Single and multiple dates allowed</div>
	</div>
	<div class="mb-3">
		<label for="inputStatus" class="form-label">Ticket Status</label>
		<select class="form-select" id="inputStatus" name="inputStatus">
			<option value="Enabled" <?php if ($ticket->status == "Enabled") { echo " selected";}?>>Enabled</option>
			<option value="Disabled" <?php if ($ticket->status == "Disabled") { echo " selected";}?>>Disabled</option>
		</select>
	</div>
	<div class="d-grid gap-2">
		<button type="submit" class="btn btn-primary">Save</button>
	</div>
	</form>
</div>


<!-- Modal -->
<div class="modal fade" id="ticketDeleteModal" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form action="index.php?n=tickets&ticketDelete=<?php echo $ticket->uid; ?>" method="post">
			<div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="staticBackdropLabel">Delete Ticket</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
				<p>Are you sure you want to delete this ticket from the Task Scheduler?  This will not delete any existing tickets on Zammad.</p>
				<p class="text-danger"><strong>WARNING!</strong> This action cannot be undone!</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-link text-muted" data-bs-dismiss="modal">Close</button>
				<button type="submit" class="btn btn-danger"><svg width="1em" height="1em"><use xlink:href="inc/icons.svg#delete"/></svg> Delete Ticket</button>
      </div>
    </div>
		</form>
  </div>
</div>


<script>
function runJob() {
	if (window.confirm("Are you sure you want to run this job now?")) {
			location.href = 'index.php?n=tickets&jobRun=<?php echo $ticket->uid; ?>';
	}
}

const input = document.querySelector("#inputFrequency2");

function parseCustomDates(str) {
  if (!str) return [];
  return str.split(",").map(dateStr => {
	const [monthStr, dayStr] = dateStr.trim().split("-");
	const month = new Date(`${monthStr} 1, 2000`).getMonth(); // Dummy year
	const day = parseInt(dayStr, 10);
	const today = new Date();
	return new Date(today.getFullYear(), month, day);
  });
}

flatpickr(input, {
  mode: "multiple",
  defaultDate: parseCustomDates(input.value),
  dateFormat: "M-d", // still required but overridden below
  formatDate: (date, format, locale) => {
	const month = date.toLocaleString('en-US', { month: 'short' }).toUpperCase();
	const day = String(date.getDate()).padStart(2, '0');
	return `${month}-${day}`;
  },
  onChange: function(selectedDates, dateStr, instance) {
	// Update the input field manually to show the correct custom format
	input.value = selectedDates.map(date => {
	  const month = date.toLocaleString('en-GB', { month: 'short' }).toUpperCase();
	  const day = String(date.getDate()).padStart(2, '0');
	  return `${month}-${day}`;
	}).join(",");
  }
});
</script>