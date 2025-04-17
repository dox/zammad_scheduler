<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<?php
$tickets = new tickets();
$agentsClass = new agents();

if (isset($_GET['ticketDelete'])) {
	$tickets->delete($_GET['ticketDelete']);
}

if (isset($_POST['inputSubject'])) {
	$ticket_create['subject'] = $_POST['inputSubject'];
	$ticket_create['body'] = $_POST['inputBody'];
	$ticket_create['zammad_priority'] = $_POST['inputPriority'];
	$ticket_create['zammad_group'] = $_POST['inputGroup'];
	$ticket_create['tags'] = $_POST['inputTags'];
	$ticket_create['frequency'] = $_POST['inputFrequency'];
	$ticket_create['frequency2'] = str_replace(' ', '', strtoupper($_POST['inputFrequency2']));
	$ticket_create['zammad_agent'] = $_POST['inputAssignTo'];
	$ticket_create['zammad_customer'] = $_POST['inputLoggedBy'];
	$ticket_create['cc'] = $_POST['inputCC'];
	$ticket_create['status'] = "Enabled";

	$tickets->create($ticket_create);
}
?>

<div class="container">
	<?php
	$title = "<svg width=\"1em\" height=\"1em\"><use xlink:href=\"inc/icons.svg#tickets\"/></svg> Tickets";
	$subtitle = "Daily, weekly, monthly and yearly tickets that are auto-scheduled to appear on Zammad.";
	$icons[] = array("class" => "btn-primary", "name" => "<svg width=\"1em\" height=\"1em\"><use xlink:href=\"inc/icons.svg#tickets\"/></svg> Add Ticket", "value" => "data-bs-toggle=\"modal\" data-bs-target=\"#ticketAddModal\"");

	echo makeTitle($title, $subtitle, $icons);
	?>

	<ul class="nav nav-tabs nav-fill" id="myTab" role="tablist">
		<?php
		$groups = $agentsClass->groups();
		
		$i = 0;
		foreach($groups AS $groupID => $groupName) {
			if ($i == 0) {
				$active = " active";
			} else {
				$active = "";
			}
			
			$output  = "<li class=\"nav-item\" role=\"presentation\">";
			$output .= "<button class=\"nav-link" . $active . "\" id=\"tab-" . $groupID . "\" data-bs-toggle=\"tab\" data-bs-target=\"#content-" . $groupID . "\" type=\"button\" role=\"tab\" aria-controls=\"" . $groupName . "\" aria-selected=\"false\">" . $groupName . "</button>";
			$output .= "</li>";
			
			echo $output;
			
			$i++;
		}
		?>
	</ul>

	<div class="tab-content" id="myTabContent">
		<?php
		
		$i = 0;
		foreach($groups AS $groupID => $groupName) {
			if ($i == 0) {
				$active = " show active";
			} else {
				$active = "";
			}
			
			$output  = "<div class=\"tab-pane fade" . $active . "\" id=\"content-" . $groupID . "\" role=\"tabpanel\" aria-labelledby=\"tab-" . $groupID . "\">";

			$output .= "<br />";
			
			$output .= $tickets->showTicketsTable($tickets->getTicketsByGroup($groupID));
			

			$output .= "</div>";

			echo $output;
			
			$i++;
		}
		?>
	</div>
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
					<label for="inputGroup" class="form-label">Ticket Group</label>
					<select class="form-select" id="inputGroup" name="inputGroup">
						<?php
						foreach ($agentsClass->groups() AS $groupID => $groupName) {
							$output = "<option value=\"" . $groupID . "\">" . $groupName . "</option>";
	
							echo $output;
						}
						?>
					</select>
				</div>
				<div class="mb-3">
					<label for="inputPriority" class="form-label">Ticket Priority</label>
					<select class="form-select" id="inputPriority" name="inputPriority">
						<option value="1">1 Low</option>
						<option value="2">2 Normal</option>
						<option value="3">3 High</option>
					</select>
				</div>
				<div class="mb-3">
					<label for="inputLoggedBy" class="form-label">Ticket Logged By</label>
					<select class="form-select" id="inputLoggedBy" name="inputLoggedBy">
						<?php
						foreach ($agentsClass->getZammadAgents() AS $agent) {
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
						foreach ($agentsClass->getZammadAgents() AS $agent) {
							$output  = "<option value=\"" . $agent['id'] . "\">" . $agent['firstname'] . " " . $agent['lastname'] . "</option>";

							echo $output;
						}
						?>
					</select>
				</div>
				<div class="mb-3">
					<label for="inputCC" class="form-label">Ticket CC</label>
					<input type="text" class="form-control" id="inputCC" name="inputCC" aria-describedby="inputCCHelp">
					<div id="inputCCHelp" class="form-text">Comma-separated list of email addresses to CC into this ticket.</div>
				</div>
				<div class="mb-3">
					<label for="inputTags" class="form-label">Ticket Tags</label>
					<input type="text" class="form-control" id="inputTags" name="inputTags" aria-describedby="inputTagsHelp">
					<div id="inputTagsHelp" class="form-text">Comma-separated list of tags to include into this ticket.</div>
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
					<div id="inputFrequency2Help" class="form-text">Single and multiple dates allowed</div>
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


<script>
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