<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<?php
$tickets = new tickets();
$ticket = $tickets->getTicket($_GET['job']);
$previousZammadTicket = $tickets->ticketValuesGetFromZammad($ticket->last_id);
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
	$previousZammadTicket = $tickets->ticketValuesGetFromZammad($ticket->last_id);
}

$groups = $agentsClass->groups();
$agentsByVisibleGroups = $agentsClass->getAgentsGroupedByVisibleGroups();
$assignedAgent = $agentsClass->getZammadAgent($ticket->zammad_agent);
$customerAgent = $agentsClass->getZammadAgent($ticket->zammad_customer);

$priorityLabels = array(
	"1" => "1 Low",
	"2" => "2 Normal",
	"3" => "3 High",
);

$statusClass = $ticket->status === "Enabled" ? "bg-success-subtle text-success-emphasis" : "bg-secondary-subtle text-secondary-emphasis";
$priorityText = $priorityLabels[$ticket->zammad_priority] ?? $ticket->zammad_priority;
$groupName = $groups[$ticket->zammad_group] ?? ("Group " . $ticket->zammad_group);
$assignedName = is_array($assignedAgent) ? trim(($assignedAgent['firstname'] ?? '') . " " . ($assignedAgent['lastname'] ?? '')) : "";
$customerName = is_array($customerAgent) ? trim(($customerAgent['firstname'] ?? '') . " " . ($customerAgent['lastname'] ?? '')) : "";
$assignedAgentUrl = "index.php?n=agent&agentUID=" . urlencode((string) $ticket->zammad_agent);
$customerAgentUrl = "index.php?n=agent&agentUID=" . urlencode((string) $ticket->zammad_customer);

if ($assignedName === "") {
	$assignedName = "Agent " . $ticket->zammad_agent;
}

if ($customerName === "") {
	$customerName = "Customer " . $ticket->zammad_customer;
}

$frequencySummary = $ticket->frequency;
if ($ticket->frequency === "Yearly" && !empty($ticket->frequency2)) {
	$frequencySummary .= " on " . strtoupper($ticket->frequency2);
}

$lastRunIsOpen = isset($previousZammadTicket['id']) && $previousZammadTicket['state'] != 'closed';
$lastRunUrl = $lastRunIsOpen ? zammad_url . "/#ticket/zoom/" . $previousZammadTicket['id'] : null;
?>

<div class="container">
	<?php
	$title = "<i class=\"bi bi-stickies\"></i> Ticket ID: " . $ticket->uid;
	$subtitle = htmlspecialchars($ticket->subject, ENT_QUOTES);
	$runNowLabel = "<span class=\"run-now-content\"><span class=\"run-now-icon-wrap\"><i class=\"bi bi-arrow-repeat run-now-icon\"></i></span><span class=\"run-now-label\">Run Now</span></span>";
	$icons[] = array("class" => "btn-warning js-run-now-button", "name" => $runNowLabel, "value" => "onclick=\"zammadTicketCreate(this.id, event);\" id=\"" . $ticket->uid . "\" data-running-label=\"Running...\" data-idle-label=\"Run Now\"");
	$icons[] = array("class" => "btn-danger", "name" => "<i class=\"bi bi-trash\"></i> Delete Ticket", "value" => "data-bs-toggle=\"modal\" data-bs-target=\"#ticketDeleteModal\"");

	echo makeTitle($title, $subtitle, $icons);
	?>

	<div id="runNowFeedback" class="alert d-none" role="alert"></div>

	<div class="ticket-edit-shell">
		<div class="row g-4">
			<div class="col-xl-4">
				<div class="card shadow-sm border-0 h-100">
					<div class="card-body p-4">
						<div class="d-flex justify-content-between align-items-start gap-3 mb-4">
							<div>
								<div class="ticket-meta-label">Current Status</div>
								<h2 class="h4 mb-1"><?php echo htmlspecialchars($ticket->subject, ENT_QUOTES); ?></h2>
								<div class="text-muted">Ticket ID <?php echo htmlspecialchars((string) $ticket->uid, ENT_QUOTES); ?></div>
							</div>
							<span class="badge <?php echo $statusClass; ?>"><?php echo htmlspecialchars($ticket->status, ENT_QUOTES); ?></span>
						</div>

						<div class="ticket-meta-list">
							<div class="ticket-meta-item">
								<div class="ticket-meta-label">Frequency</div>
								<div class="ticket-meta-value"><?php echo htmlspecialchars($frequencySummary, ENT_QUOTES); ?></div>
							</div>
							<div class="ticket-meta-item">
								<div class="ticket-meta-label">Priority</div>
								<div class="ticket-meta-value"><?php echo htmlspecialchars($priorityText, ENT_QUOTES); ?></div>
							</div>
							<div class="ticket-meta-item">
								<div class="ticket-meta-label">Group</div>
								<div class="ticket-meta-value"><?php echo htmlspecialchars($groupName, ENT_QUOTES); ?></div>
							</div>
							<div class="ticket-meta-item">
								<div class="ticket-meta-label">Assigned Agent</div>
								<div class="ticket-meta-value"><a class="ticket-meta-link" href="<?php echo htmlspecialchars($assignedAgentUrl, ENT_QUOTES); ?>"><?php echo htmlspecialchars($assignedName, ENT_QUOTES); ?></a></div>
							</div>
							<div class="ticket-meta-item">
								<div class="ticket-meta-label">Customer</div>
								<div class="ticket-meta-value"><a class="ticket-meta-link" href="<?php echo htmlspecialchars($customerAgentUrl, ENT_QUOTES); ?>"><?php echo htmlspecialchars($customerName, ENT_QUOTES); ?></a></div>
							</div>
							<div class="ticket-meta-item">
								<div class="ticket-meta-label">Tags</div>
								<div class="ticket-meta-value"><?php echo $ticket->tags ? htmlspecialchars($ticket->tags, ENT_QUOTES) : '<span class="text-muted">No tags set</span>'; ?></div>
							</div>
						</div>

						<div class="mt-4">
							<div class="ticket-meta-label mb-2">Last Run</div>
							<?php if ($lastRunIsOpen): ?>
								<div class="alert alert-primary mb-0" role="alert">
									<div class="fw-semibold mb-1">Previous run still open</div>
									<div class="small mb-2">Zammad ticket <?php echo htmlspecialchars((string) $previousZammadTicket['id'], ENT_QUOTES); ?> created on <?php echo htmlspecialchars(dateDisplay($previousZammadTicket['created_at']), ENT_QUOTES); ?></div>
									<a href="<?php echo htmlspecialchars($lastRunUrl, ENT_QUOTES); ?>"><?php echo htmlspecialchars($lastRunUrl, ENT_QUOTES); ?></a>
								</div>
							<?php elseif (!empty($ticket->last_id)): ?>
								<div class="alert alert-light border mb-0" role="alert">
									Last created Zammad ticket: <?php echo htmlspecialchars((string) $ticket->last_id, ENT_QUOTES); ?>
								</div>
							<?php else: ?>
								<div class="alert alert-light border mb-0" role="alert">
									This scheduled ticket has not been run yet.
								</div>
							<?php endif; ?>
						</div>
					</div>
				</div>
			</div>

			<div class="col-xl-8">
				<form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post">
					<div class="card shadow-sm border-0 mb-4">
						<div class="card-body p-4">
							<div class="section-heading">
								<h3 class="h5 mb-1">Ticket Content</h3>
								<p class="text-muted mb-0">Define what gets created when this scheduled job runs.</p>
							</div>

							<div class="mb-3">
								<label for="inputSubject" class="form-label">Ticket Subject</label>
								<input type="text" class="form-control" id="inputSubject" name="inputSubject" value="<?php echo htmlspecialchars($ticket->subject, ENT_QUOTES); ?>">
							</div>

							<div class="mb-0">
								<label for="inputBody" class="form-label">Ticket Body</label>
								<textarea class="form-control ticket-body-input" rows="6" id="inputBody" name="inputBody"><?php echo htmlspecialchars($ticket->body, ENT_QUOTES); ?></textarea>
							</div>
						</div>
					</div>

					<div class="card shadow-sm border-0 mb-4">
						<div class="card-body p-4">
							<div class="section-heading">
								<h3 class="h5 mb-1">Routing</h3>
								<p class="text-muted mb-0">Choose where the ticket lands and who should be attached to it.</p>
							</div>

							<div class="row g-3">
								<div class="col-md-6">
									<label for="inputGroup" class="form-label">Ticket Group</label>
									<select class="form-select" id="inputGroup" name="inputGroup">
										<?php foreach ($groups AS $groupID => $groupName): ?>
											<option value="<?php echo htmlspecialchars((string) $groupID, ENT_QUOTES); ?>"<?php if ($groupID == $ticket->zammad_group) { echo " selected"; } ?>><?php echo htmlspecialchars($groupName, ENT_QUOTES); ?></option>
										<?php endforeach; ?>
									</select>
								</div>
								<div class="col-md-6">
									<label for="inputPriority" class="form-label">Ticket Priority</label>
									<select class="form-select" id="inputPriority" name="inputPriority">
										<option value="1"<?php if ($ticket->zammad_priority == "1") { echo " selected"; } ?>>1 Low</option>
										<option value="2"<?php if ($ticket->zammad_priority == "2") { echo " selected"; } ?>>2 Normal</option>
										<option value="3"<?php if ($ticket->zammad_priority == "3") { echo " selected"; } ?>>3 High</option>
									</select>
								</div>
								<div class="col-md-6">
									<label for="inputLoggedBy" class="form-label">Zammad Customer</label>
									<select class="form-select" id="inputLoggedBy" name="inputLoggedBy">
										<?php foreach ($agentsByVisibleGroups AS $groupName => $groupAgents): ?>
											<optgroup label="<?php echo htmlspecialchars($groupName, ENT_QUOTES); ?>">
												<?php foreach ($groupAgents AS $agent): ?>
													<?php
													$agentName = trim(($agent['firstname'] ?? '') . " " . ($agent['lastname'] ?? ''));
													if ($agentName === '') {
														$agentName = $agent['login'] ?? ('Agent ' . $agent['id']);
													}
													?>
													<option value="<?php echo htmlspecialchars((string) $agent['id'], ENT_QUOTES); ?>"<?php if ($ticket->zammad_customer == $agent['id']) { echo " selected"; } ?>><?php echo htmlspecialchars($agentName, ENT_QUOTES); ?></option>
												<?php endforeach; ?>
											</optgroup>
										<?php endforeach; ?>
									</select>
								</div>
								<div class="col-md-6">
									<label for="inputAssignTo" class="form-label">Zammad Agent</label>
									<select class="form-select" id="inputAssignTo" name="inputAssignTo">
										<?php foreach ($agentsByVisibleGroups AS $groupName => $groupAgents): ?>
											<optgroup label="<?php echo htmlspecialchars($groupName, ENT_QUOTES); ?>">
												<?php foreach ($groupAgents AS $agent): ?>
													<?php
													$agentName = trim(($agent['firstname'] ?? '') . " " . ($agent['lastname'] ?? ''));
													if ($agentName === '') {
														$agentName = $agent['login'] ?? ('Agent ' . $agent['id']);
													}
													?>
													<option value="<?php echo htmlspecialchars((string) $agent['id'], ENT_QUOTES); ?>"<?php if ($ticket->zammad_agent == $agent['id']) { echo " selected"; } ?>><?php echo htmlspecialchars($agentName, ENT_QUOTES); ?></option>
												<?php endforeach; ?>
											</optgroup>
										<?php endforeach; ?>
									</select>
								</div>
								<div class="col-12">
									<label for="inputCC" class="form-label">Ticket CC</label>
									<input type="text" class="form-control" id="inputCC" name="inputCC" aria-describedby="inputCCHelp" value="<?php echo htmlspecialchars((string) $ticket->cc, ENT_QUOTES); ?>">
									<div id="inputCCHelp" class="form-text">Comma-separated list of email addresses to CC into this ticket.</div>
								</div>
								<div class="col-12">
									<label for="inputTags" class="form-label">Ticket Tags</label>
									<input type="text" class="form-control" id="inputTags" name="inputTags" aria-describedby="inputTagsHelp" value="<?php echo htmlspecialchars((string) $ticket->tags, ENT_QUOTES); ?>">
									<div id="inputTagsHelp" class="form-text">Comma-separated list of tags to include into this ticket.</div>
								</div>
							</div>
						</div>
					</div>

					<div class="card shadow-sm border-0 mb-4">
						<div class="card-body p-4">
							<div class="section-heading">
								<h3 class="h5 mb-1">Schedule</h3>
								<p class="text-muted mb-0">Set how often the ticket runs and whether it is currently active.</p>
							</div>

							<div class="row g-3">
								<div class="col-md-6">
									<label for="inputFrequency" class="form-label">Ticket Frequency</label>
									<select class="form-select" id="inputFrequency" name="inputFrequency" onchange="toggleFrequency2()">
										<option value="Daily"<?php if ($ticket->frequency == "Daily") { echo " selected"; } ?>>Daily</option>
										<option value="Weekly"<?php if ($ticket->frequency == "Weekly") { echo " selected"; } ?>>Weekly</option>
										<option value="Monthly"<?php if ($ticket->frequency == "Monthly") { echo " selected"; } ?>>Monthly</option>
										<option value="Yearly"<?php if ($ticket->frequency == "Yearly") { echo " selected"; } ?>>Yearly</option>
									</select>
								</div>
								<div class="col-md-6">
									<label for="inputStatus" class="form-label">Ticket Status</label>
									<select class="form-select" id="inputStatus" name="inputStatus">
										<option value="Enabled"<?php if ($ticket->status == "Enabled") { echo " selected"; } ?>>Enabled</option>
										<option value="Disabled"<?php if ($ticket->status == "Disabled") { echo " selected"; } ?>>Disabled</option>
									</select>
								</div>
								<div class="col-12" id="inputFrequency2Div" <?php if ($ticket->frequency <> 'Yearly') { echo 'hidden'; } ?>>
									<label for="inputFrequency2" class="form-label">Yearly Frequency</label>
									<input type="text" class="form-control" id="inputFrequency2" name="inputFrequency2" aria-describedby="inputFrequency2Help" value="<?php echo htmlspecialchars(strtoupper((string) $ticket->frequency2), ENT_QUOTES); ?>">
									<div id="inputFrequency2Help" class="form-text">Pick one or more dates for yearly runs. Dates are stored as `MON-01` style values.</div>
								</div>
							</div>
						</div>
					</div>

					<div class="d-flex justify-content-end">
						<button type="submit" class="btn btn-primary btn-lg px-4">Save Changes</button>
					</div>
				</form>
			</div>
		</div>
	</div>
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
				<p>Are you sure you want to delete this ticket from the Task Scheduler? This will not delete any existing tickets on Zammad.</p>
				<p class="text-danger"><strong>WARNING!</strong> This action cannot be undone.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-link text-muted" data-bs-dismiss="modal">Close</button>
				<button type="submit" class="btn btn-danger"><i class="bi bi-trash"></i> Delete Ticket</button>
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
	const month = new Date(`${monthStr} 1, 2000`).getMonth();
	const day = parseInt(dayStr, 10);
	const today = new Date();
	return new Date(today.getFullYear(), month, day);
  });
}

if (input) {
	flatpickr(input, {
	  mode: "multiple",
	  defaultDate: parseCustomDates(input.value),
	  dateFormat: "M-d",
	  formatDate: (date, format, locale) => {
		const month = date.toLocaleString('en-US', { month: 'short' }).toUpperCase();
		const day = String(date.getDate()).padStart(2, '0');
		return `${month}-${day}`;
	  },
	  onChange: function(selectedDates, dateStr, instance) {
		input.value = selectedDates.map(date => {
		  const month = date.toLocaleString('en-GB', { month: 'short' }).toUpperCase();
		  const day = String(date.getDate()).padStart(2, '0');
		  return `${month}-${day}`;
		}).join(",");
	  }
	});
}
</script>

<style>
.ticket-edit-shell .card {
	border-radius: 1rem;
	background-color: var(--bs-tertiary-bg);
	border: 1px solid var(--bs-border-color-translucent) !important;
	box-shadow: var(--bs-box-shadow-sm) !important;
}

.section-heading {
	margin-bottom: 1.25rem;
}

.ticket-meta-list {
	display: grid;
	gap: 1rem;
}

.ticket-meta-item {
	padding-bottom: 0.75rem;
	border-bottom: 1px solid rgba(33, 37, 41, 0.08);
}

.ticket-meta-item:last-child {
	padding-bottom: 0;
	border-bottom: 0;
}

.ticket-meta-label {
	font-size: 0.75rem;
	font-weight: 700;
	letter-spacing: 0.08em;
	text-transform: uppercase;
	color: var(--bs-secondary-color);
	margin-bottom: 0.25rem;
}

.ticket-meta-value {
	color: var(--bs-body-color);
	word-break: break-word;
}

.ticket-meta-link {
	color: inherit;
	text-decoration: none;
}

.ticket-meta-link:hover,
.ticket-meta-link:focus {
	color: #0d6efd;
	text-decoration: underline;
}

.ticket-body-input {
	min-height: 12rem;
}

.run-now-content {
	display: inline-flex;
	align-items: center;
	gap: 0.4rem;
}

.run-now-icon-wrap {
	display: inline-flex;
	align-items: center;
	justify-content: center;
}

.js-run-now-button.is-running .run-now-icon {
	animation: runNowSpin 0.9s linear infinite;
}

@keyframes runNowSpin {
	from {
		transform: rotate(0deg);
	}
	to {
		transform: rotate(360deg);
	}
}
</style>
