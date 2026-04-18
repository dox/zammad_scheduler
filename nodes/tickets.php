<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<?php
$tickets = new tickets();
$agentsClass = new agents();
$groups = $agentsClass->groups();
$allTickets = $tickets->getTickets();

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

$enabledCount = 0;
$disabledCount = 0;
foreach ($allTickets as $scheduledTicket) {
	if (($scheduledTicket->status ?? '') === 'Disabled') {
		$disabledCount++;
	} else {
		$enabledCount++;
	}
}

$summaryCards = array(
	array(
		'label' => 'Scheduled Tickets',
		'value' => count($allTickets),
		'helper' => 'All jobs currently configured in this scheduler',
		'class' => 'text-primary',
	),
	array(
		'label' => 'Enabled',
		'value' => $enabledCount,
		'helper' => 'Jobs that can currently create tickets',
		'class' => 'text-success',
	),
	array(
		'label' => 'Disabled',
		'value' => $disabledCount,
		'helper' => 'Jobs kept for reference but not currently active',
		'class' => 'text-secondary',
	),
);
?>

<div class="container">
	<?php
	$title = "<i class=\"bi bi-stickies\"></i> Tickets";
	$subtitle = "Daily, weekly, monthly and yearly tickets that are auto-scheduled to appear on Zammad.";
	$icons[] = array("class" => "btn-primary", "name" => "<i class=\"bi bi-plus-circle\"></i> Add Ticket", "value" => "data-bs-toggle=\"modal\" data-bs-target=\"#ticketAddModal\"");

	echo makeTitle($title, $subtitle, $icons);
	?>

	<div class="tickets-page-shell">
		<div class="row g-3 mb-4">
			<?php foreach ($summaryCards as $card): ?>
				<div class="col-md-4">
					<div class="card border-0 shadow-sm h-100">
						<div class="card-body">
							<div class="text-muted small text-uppercase mb-2"><?php echo htmlspecialchars($card['label'], ENT_QUOTES); ?></div>
							<div class="display-6 fw-semibold <?php echo htmlspecialchars($card['class'], ENT_QUOTES); ?>"><?php echo htmlspecialchars((string) $card['value'], ENT_QUOTES); ?></div>
							<div class="small text-muted"><?php echo htmlspecialchars($card['helper'], ENT_QUOTES); ?></div>
						</div>
					</div>
				</div>
			<?php endforeach; ?>
		</div>

		<div class="card border-0 shadow-sm">
			<div class="card-body p-4">
				<div class="section-heading">
					<h3 class="h5 mb-1">Scheduled Ticket Library</h3>
					<p class="text-muted mb-0">Browse tickets by Zammad group and use the live filter to find jobs quickly.</p>
				</div>

				<ul class="nav nav-pills nav-fill ticket-group-tabs mb-4" id="myTab" role="tablist">
					<?php
					$i = 0;
					foreach($groups AS $groupID => $groupName) {
						$active = $i === 0 ? " active" : "";
						$groupTicketCount = count($tickets->getTicketsByGroup($groupID));
						
						$output  = "<li class=\"nav-item\" role=\"presentation\">";
						$output .= "<button class=\"nav-link" . $active . "\" id=\"tab-" . $groupID . "\" data-bs-toggle=\"tab\" data-bs-target=\"#content-" . $groupID . "\" type=\"button\" role=\"tab\" aria-controls=\"" . htmlspecialchars($groupName, ENT_QUOTES) . "\" aria-selected=\"" . ($i === 0 ? "true" : "false") . "\">";
						$output .= "<span>" . htmlspecialchars($groupName, ENT_QUOTES) . "</span>";
						$output .= "<span class=\"badge rounded-pill group-count-badge ms-2\">" . $groupTicketCount . "</span>";
						$output .= "</button>";
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
						$active = $i === 0 ? " show active" : "";
						$groupTickets = $tickets->getTicketsByGroup($groupID);
						
						$output  = "<div class=\"tab-pane fade" . $active . "\" id=\"content-" . $groupID . "\" role=\"tabpanel\" aria-labelledby=\"tab-" . $groupID . "\">";
						$output .= "<div class=\"d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-3\">";
						$output .= "<div>";
						$output .= "<div class=\"ticket-meta-label\">Group Overview</div>";
						$output .= "<div class=\"text-muted\">Manage scheduled tickets for " . htmlspecialchars($groupName, ENT_QUOTES) . ".</div>";
						$output .= "</div>";
						$output .= "<input type=\"search\" class=\"form-control ticket-search-input\" placeholder=\"Search tickets in " . htmlspecialchars($groupName, ENT_QUOTES) . "\" data-ticket-search-target=\"content-" . $groupID . "\">";
						$output .= "</div>";

						if (!empty($groupTickets)) {
							$output .= "<div class=\"table-responsive\">";
							$output .= $tickets->showTicketsTable($groupTickets);
							$output .= "</div>";
							$output .= "<p class=\"text-muted small mb-0 d-none ticket-search-empty\">No tickets match this search.</p>";
						} else {
							$output .= "<div class=\"alert alert-light border mb-0\" role=\"alert\">No scheduled tickets have been created for this group yet.</div>";
						}

						$output .= "</div>";

						echo $output;
						
						$i++;
					}
					?>
				</div>
			</div>
		</div>
	</div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
	function updateEmptyState(pane) {
		var emptyState = pane.querySelector('.ticket-search-empty');
		if (!emptyState) {
			return;
		}

		var visibleRows = 0;
		pane.querySelectorAll('tbody tr').forEach(function (row) {
			if (row.style.display !== 'none') {
				visibleRows += 1;
			}
		});

		emptyState.classList.toggle('d-none', visibleRows !== 0);
	}

	document.querySelectorAll('.ticket-search-input').forEach(function (input) {
		input.addEventListener('input', function () {
			var term = input.value.toLowerCase().trim();
			var pane = document.getElementById(input.getAttribute('data-ticket-search-target'));

			if (!pane) {
				return;
			}

			pane.querySelectorAll('tbody tr').forEach(function (row) {
				var text = row.textContent.toLowerCase();
				row.style.display = text.indexOf(term) === -1 ? 'none' : '';
			});

			updateEmptyState(pane);
		});
	});
});
</script>


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
						foreach ($agentsClass->getAgentsGroupedByVisibleGroups() AS $groupName => $groupAgents) {
							echo "<optgroup label=\"" . htmlspecialchars($groupName, ENT_QUOTES) . "\">";

							foreach ($groupAgents AS $agent) {
								$agentName = trim(($agent['firstname'] ?? '') . " " . ($agent['lastname'] ?? ''));
								if ($agentName === '') {
									$agentName = $agent['login'] ?? ('Agent ' . $agent['id']);
								}

								$output  = "<option value=\"" . htmlspecialchars((string) $agent['id'], ENT_QUOTES) . "\">";
								$output .= htmlspecialchars($agentName, ENT_QUOTES);
								$output .= "</option>";

								echo $output;
							}

							echo "</optgroup>";
						}
						?>
					</select>
				</div>
				<div class="mb-3">
					<label for="inputAssignTo" class="form-label">Auto-assign To Agent</label>
					<select class="form-select" id="inputAssignTo" name="inputAssignTo">
						<?php
						foreach ($agentsClass->getAgentsGroupedByVisibleGroups() AS $groupName => $groupAgents) {
							echo "<optgroup label=\"" . htmlspecialchars($groupName, ENT_QUOTES) . "\">";

							foreach ($groupAgents AS $agent) {
								$agentName = trim(($agent['firstname'] ?? '') . " " . ($agent['lastname'] ?? ''));
								if ($agentName === '') {
									$agentName = $agent['login'] ?? ('Agent ' . $agent['id']);
								}

								$output  = "<option value=\"" . htmlspecialchars((string) $agent['id'], ENT_QUOTES) . "\">";
								$output .= htmlspecialchars($agentName, ENT_QUOTES);
								$output .= "</option>";

								echo $output;
							}

							echo "</optgroup>";
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
				<button type="submit" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Add Ticket</button>
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
.tickets-page-shell .card {
	border-radius: 1rem;
	background-color: var(--bs-tertiary-bg);
	border: 1px solid var(--bs-border-color-translucent) !important;
	box-shadow: var(--bs-box-shadow-sm) !important;
}

.section-heading {
	margin-bottom: 1.25rem;
}

.ticket-group-tabs .nav-link {
	border-radius: 999px;
	font-weight: 600;
}

.ticket-group-tabs .nav-link.active {
	box-shadow: inset 0 0 0 1px rgba(13, 110, 253, 0.08);
}

.ticket-meta-label {
	font-size: 0.75rem;
	font-weight: 700;
	letter-spacing: 0.08em;
	text-transform: uppercase;
	color: var(--bs-secondary-color);
	margin-bottom: 0.25rem;
}

.ticket-search-input {
	max-width: 26rem;
}

.ticket-table thead th {
	color: var(--bs-secondary-color);
	font-size: 0.8rem;
	font-weight: 700;
	letter-spacing: 0.04em;
	text-transform: uppercase;
	border-bottom-width: 1px;
}

.group-count-badge {
	background-color: var(--bs-secondary-bg-subtle);
	color: var(--bs-emphasis-color);
	border: 1px solid var(--bs-border-color-translucent);
}

.ticket-row-link {
	color: inherit;
	text-decoration: none;
}

.ticket-row-link:hover,
.ticket-row-link:focus {
	color: #0d6efd;
	text-decoration: underline;
}
</style>
