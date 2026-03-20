<?php
$agentsClass = new agents();
$agent = $agentsClass->getZammadAgent($_GET['agentUID']);
$ticketsClass = new tickets();

if (empty($agent)) {
	echo "<div class=\"container\"><div class=\"alert alert-danger\" role=\"alert\">Agent not found.</div></div>";
	return;
}

$displayName = trim(($agent['firstname'] ?? '') . " " . ($agent['lastname'] ?? ''));
if ($displayName === "") {
	$displayName = $agent['login'] ?? ("Agent " . $agent['id']);
}

$assignedTickets = $ticketsClass->getTicketsByAgent($agent['id']);
$customerTickets = $ticketsClass->getTicketsByCustomer($agent['id']);
$allTickets = $ticketsClass->getTicketsByAgentOrCustomer($agent['id']);

$groups = array();
if (isset($agent['groups']) && is_array($agent['groups'])) {
	$groups = array_keys($agent['groups']);
	sort($groups);
}

$groupText = !empty($groups) ? implode(", ", $groups) : "No groups returned by Zammad";
$email = $agent['email'] ?? null;
$login = $agent['login'] ?? null;
$isActive = !empty($agent['active']);
$lastLogin = $agent['last_login'] ?? null;
$updatedAt = $agent['updated_at'] ?? null;
$imageUrl = null;

if (!empty($agent['image'])) {
	$imageUrl = rtrim(zammad_url, '/') . "/api/v1/users/image/" . rawurlencode($agent['image']);
}

$initials = strtoupper(substr((string) ($agent['firstname'] ?? ''), 0, 1) . substr((string) ($agent['lastname'] ?? ''), 0, 1));
if ($initials === "") {
	$initials = strtoupper(substr((string) $displayName, 0, 2));
}

$summaryCards = array(
	array(
		'label' => 'Assigned',
		'value' => count($assignedTickets),
		'helper' => 'Scheduled tickets owned by this agent',
		'class' => 'text-primary',
	),
	array(
		'label' => 'Logged By',
		'value' => count($customerTickets),
		'helper' => 'Scheduled tickets raised on behalf of this agent',
		'class' => 'text-success',
	),
	array(
		'label' => 'Total Involved',
		'value' => count($allTickets),
		'helper' => 'Any scheduled ticket where this agent appears',
		'class' => 'text-dark',
	),
);
?>

<div class="container">
	<?php
	$title = "<i class=\"bi bi-people\"></i> " . htmlspecialchars($displayName, ENT_QUOTES);
	$subtitle = "Agent ID: " . htmlspecialchars((string) $agent['id'], ENT_QUOTES);

	echo makeTitle($title, $subtitle, $icons);
	?>

	<div class="agent-profile-shell">
		<div class="row g-4">
			<div class="col-xl-4">
				<div class="card shadow-sm border-0 h-100">
					<div class="card-body p-4">
						<div class="d-flex align-items-center gap-3 mb-4">
							<?php if ($imageUrl): ?>
								<img src="<?php echo htmlspecialchars($imageUrl, ENT_QUOTES); ?>" class="agent-avatar" alt="<?php echo htmlspecialchars($displayName, ENT_QUOTES); ?>">
							<?php else: ?>
								<div class="agent-avatar agent-avatar-fallback"><?php echo htmlspecialchars($initials, ENT_QUOTES); ?></div>
							<?php endif; ?>

							<div>
								<h2 class="h4 mb-1"><?php echo htmlspecialchars($displayName, ENT_QUOTES); ?></h2>
								<div class="d-flex flex-wrap gap-2 align-items-center">
									<span class="badge <?php echo $isActive ? 'bg-success-subtle text-success-emphasis' : 'bg-secondary-subtle text-secondary-emphasis'; ?>">
										<?php echo $isActive ? 'Active in Zammad' : 'Inactive in Zammad'; ?>
									</span>
									<?php if ($login): ?>
										<span class="text-muted small"><?php echo htmlspecialchars($login, ENT_QUOTES); ?></span>
									<?php endif; ?>
								</div>
							</div>
						</div>

						<div class="agent-detail-list">
							<div class="agent-detail-item">
								<div class="agent-detail-label">Email</div>
								<div class="agent-detail-value">
									<?php if ($email): ?>
										<a href="mailto:<?php echo htmlspecialchars($email, ENT_QUOTES); ?>"><?php echo htmlspecialchars($email, ENT_QUOTES); ?></a>
									<?php else: ?>
										<span class="text-muted">No email returned</span>
									<?php endif; ?>
								</div>
							</div>
							<div class="agent-detail-item">
								<div class="agent-detail-label">Last Login</div>
								<div class="agent-detail-value">
									<?php echo $lastLogin ? htmlspecialchars(dateDisplay($lastLogin), ENT_QUOTES) : '<span class="text-muted">Not available</span>'; ?>
								</div>
							</div>
							<div class="agent-detail-item">
								<div class="agent-detail-label">Profile Updated</div>
								<div class="agent-detail-value">
									<?php echo $updatedAt ? htmlspecialchars(dateDisplay($updatedAt), ENT_QUOTES) : '<span class="text-muted">Not available</span>'; ?>
								</div>
							</div>
						</div>

						<div class="agent-group-list mt-4">
							<div class="agent-detail-label mb-2">Group Membership</div>
							<?php if (!empty($groups)): ?>
								<?php foreach ($groups as $groupName): ?>
									<span class="badge rounded-pill section-badge me-2 mb-2"><?php echo htmlspecialchars($groupName, ENT_QUOTES); ?></span>
								<?php endforeach; ?>
							<?php else: ?>
								<p class="text-muted mb-0">No groups were returned for this agent.</p>
							<?php endif; ?>
						</div>
					</div>
				</div>
			</div>

			<div class="col-xl-8">
				<div class="row g-3 mb-3">
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
						<div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-2 mb-3">
							<div>
								<h3 class="h5 mb-1">Scheduled Tickets</h3>
								<p class="text-muted mb-0">Everything in this app that is assigned to or logged by this agent.</p>
							</div>
							<input type="search" class="form-control agent-ticket-search" placeholder="Filter this list" aria-label="Filter scheduled tickets">
						</div>

						<?php if (!empty($allTickets)): ?>
							<div class="table-responsive" id="agent-ticket-table">
								<?php echo $ticketsClass->showTicketsTable($allTickets); ?>
							</div>
							<p class="text-muted small mb-0 d-none" id="agent-ticket-empty">No scheduled tickets match your search.</p>
						<?php else: ?>
							<div class="alert alert-light border mb-0" role="alert">This agent is not currently linked to any scheduled tickets.</div>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<style>
.agent-profile-shell .card {
	border-radius: 1rem;
	background-color: var(--bs-tertiary-bg);
	border: 1px solid var(--bs-border-color-translucent) !important;
	box-shadow: var(--bs-box-shadow-sm) !important;
}

.agent-avatar {
	width: 88px;
	height: 88px;
	border-radius: 1.25rem;
	object-fit: cover;
	background: linear-gradient(145deg, #d9ecff, #f3f7fb);
	border: 1px solid rgba(13, 110, 253, 0.12);
}

.agent-avatar-fallback {
	display: flex;
	align-items: center;
	justify-content: center;
	font-size: 1.75rem;
	font-weight: 700;
	color: #0d6efd;
}

.agent-detail-list {
	display: grid;
	gap: 1rem;
}

.agent-detail-item {
	padding-bottom: 0.75rem;
	border-bottom: 1px solid rgba(33, 37, 41, 0.08);
}

.agent-detail-item:last-child {
	padding-bottom: 0;
	border-bottom: 0;
}

.agent-detail-label {
	font-size: 0.75rem;
	font-weight: 700;
	letter-spacing: 0.08em;
	text-transform: uppercase;
	color: var(--bs-secondary-color);
	margin-bottom: 0.25rem;
}

.agent-detail-value {
	color: var(--bs-body-color);
	word-break: break-word;
}

.section-badge {
	background-color: var(--bs-secondary-bg-subtle);
	color: var(--bs-emphasis-color);
	border: 1px solid var(--bs-border-color-translucent);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
	var searchInput = document.querySelector('.agent-ticket-search');
	var ticketTable = document.getElementById('agent-ticket-table');
	var emptyState = document.getElementById('agent-ticket-empty');

	if (!searchInput || !ticketTable || !emptyState) {
		return;
	}

	searchInput.addEventListener('input', function () {
		var term = searchInput.value.toLowerCase().trim();
		var visibleRows = 0;

		ticketTable.querySelectorAll('tbody tr').forEach(function (row) {
			var matches = row.textContent.toLowerCase().indexOf(term) !== -1;
			row.style.display = matches ? '' : 'none';

			if (matches) {
				visibleRows += 1;
			}
		});

		emptyState.classList.toggle('d-none', visibleRows !== 0);
	});
});
</script>
