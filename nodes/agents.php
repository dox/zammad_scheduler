<?php
$agentsClass = new agents();
$agents = $agentsClass->getZammadAgents();

$totalAgents = count($agents);
$groupNames = array();
$scheduledInvolvement = 0;

foreach ($agents as $agent) {
	if (isset($agent['groups']) && is_array($agent['groups'])) {
		foreach (array_keys($agent['groups']) as $groupName) {
			$groupNames[$groupName] = true;
		}
	}

	$assignedCount = count(tickets::getTicketsByAgent($agent['id']));
	$customerCount = count(tickets::getTicketsByCustomer($agent['id']));

	if (($assignedCount + $customerCount) > 0) {
		$scheduledInvolvement++;
	}
}

$summaryCards = array(
	array(
		'label' => 'Users',
		'value' => $totalAgents,
		'helper' => 'Active Zammad users available in this directory',
		'class' => 'text-primary',
	),
	array(
		'label' => 'Groups',
		'value' => count($groupNames),
		'helper' => 'Distinct Zammad groups represented across those agents',
		'class' => 'text-success',
	),
	array(
		'label' => 'In Use',
		'value' => $scheduledInvolvement,
		'helper' => 'Users currently linked to at least one scheduled ticket',
		'class' => 'text-secondary',
	),
);
?>

<div class="container">
	<?php
	$title = "<i class=\"bi bi-people\"></i> Active Zammad Users";
	$subtitle = "Active users imported from Zammad, including people linked to scheduled tickets.";

	echo makeTitle($title, $subtitle, $icons);
	?>

	<div class="agents-page-shell">
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
				<div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
					<div>
						<h3 class="h5 mb-1">User Directory</h3>
						<p class="text-muted mb-0">Browse active Zammad users, see where they sit in Zammad, and jump into their scheduled-ticket view.</p>
					</div>
					<input type="search" class="form-control agent-search-input" placeholder="Search users by name, group, login or email" aria-label="Search users">
				</div>

				<?php if (!empty($agents)): ?>
					<div class="table-responsive">
						<table class="table align-middle agent-table mb-0" id="agentDirectoryTable">
							<thead>
								<tr>
									<th scope="col">Agent</th>
									<th scope="col">Groups</th>
									<th scope="col">Scheduled Tickets</th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ($agents as $agent): ?>
									<?php
									$agentURL = "index.php?n=agent&agentUID=" . $agent['id'];
									$assignedCount = count(tickets::getTicketsByAgent($agent['id']));
									$customerCount = count(tickets::getTicketsByCustomer($agent['id']));
									$totalLinked = $assignedCount + $customerCount;

									$displayName = trim(($agent['firstname'] ?? '') . " " . ($agent['lastname'] ?? ''));
									if ($displayName === "") {
										$displayName = $agent['login'] ?? ("Agent " . $agent['id']);
									}

									$groups = array();
									if (isset($agent['groups']) && is_array($agent['groups'])) {
										$groups = array_keys($agent['groups']);
										sort($groups);
									}

									$groupText = !empty($groups) ? implode(", ", $groups) : "No groups returned";
									$email = $agent['email'] ?? '';
									$login = $agent['login'] ?? '';
									$imageUrl = null;
									if (!empty($agent['image'])) {
										$imageUrl = rtrim(zammad_url, '/') . "/api/v1/users/image/" . rawurlencode($agent['image']);
									}
									$initials = strtoupper(substr((string) ($agent['firstname'] ?? ''), 0, 1) . substr((string) ($agent['lastname'] ?? ''), 0, 1));
									if ($initials === '') {
										$initials = strtoupper(substr((string) $displayName, 0, 2));
									}
									?>
									<tr>
										<td>
											<div class="d-flex align-items-center gap-3">
												<?php if ($imageUrl): ?>
													<img src="<?php echo htmlspecialchars($imageUrl, ENT_QUOTES); ?>" class="agent-list-avatar agent-list-avatar-image" alt="<?php echo htmlspecialchars($displayName, ENT_QUOTES); ?>">
												<?php else: ?>
													<div class="agent-list-avatar"><?php echo htmlspecialchars($initials, ENT_QUOTES); ?></div>
												<?php endif; ?>
												<div>
													<div class="fw-semibold"><a class="agent-row-link" href="<?php echo htmlspecialchars($agentURL, ENT_QUOTES); ?>"><?php echo htmlspecialchars($displayName, ENT_QUOTES); ?></a></div>
													<div class="small text-muted"><?php echo htmlspecialchars($login, ENT_QUOTES); ?><?php if ($email): ?> · <?php echo htmlspecialchars($email, ENT_QUOTES); ?><?php endif; ?></div>
													<div class="small text-muted">Agent ID <?php echo htmlspecialchars((string) $agent['id'], ENT_QUOTES); ?></div>
												</div>
											</div>
										</td>
										<td>
											<div class="agent-groups-cell">
												<?php if (!empty($groups)): ?>
													<?php foreach ($groups as $groupName): ?>
														<span class="badge rounded-pill section-badge me-2 mb-2"><?php echo htmlspecialchars($groupName, ENT_QUOTES); ?></span>
													<?php endforeach; ?>
												<?php else: ?>
													<span class="text-muted small">No groups returned</span>
												<?php endif; ?>
											</div>
										</td>
										<td>
											<div class="small text-muted mb-1"><?php echo $totalLinked; ?> linked ticket<?php echo $totalLinked === 1 ? '' : 's'; ?></div>
											<div class="d-flex flex-wrap gap-2">
												<span class="badge bg-primary-subtle text-primary-emphasis">Assigned: <?php echo $assignedCount; ?></span>
												<span class="badge bg-success-subtle text-success-emphasis">Logged By: <?php echo $customerCount; ?></span>
											</div>
											<div class="agent-search-text d-none"><?php echo htmlspecialchars($displayName . ' ' . $login . ' ' . $email . ' ' . $groupText, ENT_QUOTES); ?></div>
										</td>
									</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					</div>
					<p class="text-muted small mb-0 d-none mt-3" id="agentSearchEmpty">No agents match this search.</p>
				<?php else: ?>
					<div class="alert alert-light border mb-0" role="alert">No active users were returned from Zammad.</div>
				<?php endif; ?>
			</div>
		</div>
	</div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
	var searchInput = document.querySelector('.agent-search-input');
	var table = document.getElementById('agentDirectoryTable');
	var emptyState = document.getElementById('agentSearchEmpty');

	if (!searchInput || !table || !emptyState) {
		return;
	}

	searchInput.addEventListener('input', function () {
		var term = searchInput.value.toLowerCase().trim();
		var visibleRows = 0;

		table.querySelectorAll('tbody tr').forEach(function (row) {
			var rowText = row.textContent.toLowerCase();
			var matches = rowText.indexOf(term) !== -1;

			row.style.display = matches ? '' : 'none';

			if (matches) {
				visibleRows += 1;
			}
		});

		emptyState.classList.toggle('d-none', visibleRows !== 0);
	});
});
</script>

<style>
.agents-page-shell .card {
	border-radius: 1rem;
	background-color: var(--bs-tertiary-bg);
	border: 1px solid var(--bs-border-color-translucent) !important;
	box-shadow: var(--bs-box-shadow-sm) !important;
}

.agent-search-input {
	max-width: 28rem;
}

.agent-table thead th {
	color: var(--bs-secondary-color);
	font-size: 0.8rem;
	font-weight: 700;
	letter-spacing: 0.04em;
	text-transform: uppercase;
	border-bottom-width: 1px;
}

.agent-list-avatar {
	width: 2.75rem;
	height: 2.75rem;
	border-radius: 0.9rem;
	display: flex;
	align-items: center;
	justify-content: center;
	font-weight: 700;
	color: #0d6efd;
	background: linear-gradient(145deg, #d9ecff, #f3f7fb);
	border: 1px solid rgba(13, 110, 253, 0.12);
	flex: 0 0 auto;
}

.agent-list-avatar-image {
	object-fit: cover;
}

.agent-groups-cell {
	min-width: 14rem;
}

.section-badge {
	background-color: var(--bs-secondary-bg-subtle);
	color: var(--bs-emphasis-color);
	border: 1px solid var(--bs-border-color-translucent);
}

.agent-row-link {
	color: inherit;
	text-decoration: none;
}

.agent-row-link:hover,
.agent-row-link:focus {
	color: #0d6efd;
	text-decoration: underline;
}
</style>
