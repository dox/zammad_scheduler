<?php
$tickets = new tickets();
$previewDateInput = $_GET['previewDate'] ?? date('Y-m-d');
$messages = [];

try {
	$previewDate = new DateTimeImmutable($previewDateInput);
} catch (Exception $e) {
	$previewDate = new DateTimeImmutable('today');
	$previewDateInput = $previewDate->format('Y-m-d');
	$messages[] = "<div class=\"alert alert-warning\" role=\"alert\">Invalid preview date supplied, so today is being used.</div>";
}

$dueTickets = $tickets->getDueTicketsForDate($previewDate);
$allTickets = $tickets->getTickets();
$appTimezone = defined("app_timezone") ? app_timezone : date_default_timezone_get();
$dueTicketUIDs = array_map(function ($ticket) {
	return (string) $ticket->uid;
}, $dueTickets);

$frequencyGroups = [
	'Daily' => [],
	'Weekly' => [],
	'Monthly' => [],
	'Yearly' => [],
];

foreach ($dueTickets as $ticket) {
	$frequency = ucfirst(strtolower(trim((string) ($ticket->frequency ?? ''))));
	if (!array_key_exists($frequency, $frequencyGroups)) {
		continue;
	}

	$frequencyGroups[$frequency][] = $ticket;
}

$enabledCount = 0;
$disabledCount = 0;
foreach ($allTickets as $ticket) {
	if (ucfirst(strtolower(trim((string) ($ticket->status ?? '')))) === 'Enabled') {
		$enabledCount++;
	} else {
		$disabledCount++;
	}
}
?>

<div class="container">
	<?php
	$title = "<i class=\"bi bi-calendar2-week\"></i> Scheduler Preview";
	$subtitle = "Choose a date and see which scheduled tickets would run without creating anything in Zammad.";

	echo makeTitle($title, $subtitle);
	?>

	<?php
	foreach ($messages as $message) {
		echo $message;
	}
	?>

	<div class="scheduler-preview-shell">
		<form class="card border-0 shadow-sm mb-4" method="get" action="index.php">
			<div class="card-body p-4">
				<input type="hidden" name="n" value="scheduler_preview">
				<div class="row g-3 align-items-end">
					<div class="col-md-5">
						<label for="previewDate" class="form-label">Preview Date</label>
						<input type="date" class="form-control" id="previewDate" name="previewDate" value="<?php echo htmlspecialchars($previewDate->format('Y-m-d'), ENT_QUOTES); ?>">
					</div>
					<div class="col-md-4">
						<div class="ticket-meta-label">Timezone</div>
						<div class="fw-semibold"><?php echo htmlspecialchars($appTimezone, ENT_QUOTES); ?></div>
					</div>
					<div class="col-md-3 text-md-end">
						<button type="submit" class="btn btn-primary"><i class="bi bi-search"></i> Preview</button>
					</div>
				</div>
			</div>
		</form>

		<div class="row g-3 mb-4">
			<div class="col-md-3">
				<div class="card border-0 shadow-sm h-100">
					<div class="card-body">
						<div class="ticket-meta-label">Evaluated Date</div>
						<div class="h4 mb-0"><?php echo htmlspecialchars($previewDate->format('Y-m-d'), ENT_QUOTES); ?></div>
					</div>
				</div>
			</div>
			<div class="col-md-3">
				<div class="card border-0 shadow-sm h-100">
					<div class="card-body">
						<div class="ticket-meta-label">Due Tickets</div>
						<div class="h4 mb-0 text-success"><?php echo htmlspecialchars((string) count($dueTickets), ENT_QUOTES); ?></div>
					</div>
				</div>
			</div>
			<div class="col-md-3">
				<div class="card border-0 shadow-sm h-100">
					<div class="card-body">
						<div class="ticket-meta-label">Enabled</div>
						<div class="h4 mb-0 text-primary"><?php echo htmlspecialchars((string) $enabledCount, ENT_QUOTES); ?></div>
					</div>
				</div>
			</div>
			<div class="col-md-3">
				<div class="card border-0 shadow-sm h-100">
					<div class="card-body">
						<div class="ticket-meta-label">Excluded Disabled</div>
						<div class="h4 mb-0 text-secondary"><?php echo htmlspecialchars((string) $disabledCount, ENT_QUOTES); ?></div>
					</div>
				</div>
			</div>
		</div>

		<div id="runAllFeedback" class="alert d-none" role="alert"></div>

		<div class="d-flex justify-content-end mb-4">
			<button type="button"
				class="btn btn-warning"
				id="runAllPreviewTickets"
				data-ticket-uids="<?php echo htmlspecialchars(json_encode($dueTicketUIDs), ENT_QUOTES); ?>"
				data-preview-date="<?php echo htmlspecialchars($previewDate->format('Y-m-d'), ENT_QUOTES); ?>"
				onclick="zammadTicketsCreateFromPreview(this, event);"
				<?php if (count($dueTickets) === 0) { echo "disabled"; } ?>>
				<i class="bi bi-arrow-repeat"></i> Run All Due Tickets
			</button>
		</div>

		<?php foreach ($frequencyGroups as $frequency => $frequencyTickets): ?>
			<div class="card border-0 shadow-sm mb-4">
				<div class="card-body p-4">
					<div class="d-flex justify-content-between align-items-center gap-3 mb-3">
						<div>
							<div class="ticket-meta-label">Frequency</div>
							<h2 class="h5 mb-0"><?php echo htmlspecialchars($frequency, ENT_QUOTES); ?></h2>
						</div>
						<span class="badge rounded-pill text-bg-light border"><?php echo htmlspecialchars((string) count($frequencyTickets), ENT_QUOTES); ?></span>
					</div>

					<?php if (count($frequencyTickets) > 0): ?>
						<div class="table-responsive">
							<?php echo $tickets->showTicketsTable($frequencyTickets); ?>
						</div>
					<?php else: ?>
						<div class="alert alert-light border mb-0" role="alert">
							No <?php echo htmlspecialchars(strtolower($frequency), ENT_QUOTES); ?> tickets would run on this date.
						</div>
					<?php endif; ?>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
</div>

<style>
.scheduler-preview-shell .card {
	border-radius: .75rem;
}

.scheduler-preview-shell .form-control {
	min-height: 44px;
}

.scheduler-preview-shell .ticket-meta-label {
	color: var(--bs-secondary-color);
	font-size: .75rem;
	font-weight: 700;
	letter-spacing: .08em;
	text-transform: uppercase;
}
</style>
