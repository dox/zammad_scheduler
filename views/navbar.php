<?php
$currentNode = $_GET['n'] ?? 'tickets';
?>
<nav class="navbar fixed-top navbar-expand-lg bg-body-tertiary border-bottom">
<div class="container">
	<a class="navbar-brand" href="index.php">
		<i class="bi bi-calendar2-check"></i> Task Scheduler</a>
	<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
		<span class="navbar-toggler-icon"></span>
	</button>

	<div class="collapse navbar-collapse" id="navbarSupportedContent">
		<ul class="navbar-nav mr-auto">
			<li class="nav-item <?php if($currentNode == "tickets" || $currentNode == "ticket_edit") { echo "active";}?>">
				<a class="nav-link" href="index.php?n=tickets">
					<i class="bi bi-stickies"></i> Tickets
				</a>
			</li>
			<li class="nav-item <?php if($currentNode == "agents" || $currentNode == "agent") { echo "active";}?>">
				<a class="nav-link" href="index.php?n=agents">
					<i class="bi bi-people"></i> Agents
				</a>
			</li>
			<li class="nav-item <?php if($currentNode == "logs") { echo "active";}?>">
				<a class="nav-link" href="index.php?n=logs">
					<i class="bi bi-clock-history"></i> Logs
				</a>
			</li>
		</ul>
		
		<div class="ms-auto d-flex align-items-center pt-3 pt-lg-0">
			<div class="dropdown">
				<button class="btn btn-sm btn-outline-secondary dropdown-toggle d-flex align-items-center gap-2"
					id="bd-theme"
					type="button"
					data-bs-toggle="dropdown"
					aria-expanded="false"
					aria-label="Toggle theme">
					<i class="bi bi-circle-half"></i>
					<span class="d-none d-sm-inline" id="bd-theme-text">Theme</span>
				</button>
		
				<ul class="dropdown-menu dropdown-menu-end shadow-sm" data-bs-popper="static">
					<li>
						<button type="button"
							class="dropdown-item d-flex align-items-center justify-content-between"
							data-bs-theme-value="light" aria-pressed="false">
							<span><i class="bi bi-sun me-2"></i>Light</span>
							<i class="bi bi-check2 d-none"></i>
						</button>
					</li>
					<li>
						<button type="button"
							class="dropdown-item d-flex align-items-center justify-content-between"
							data-bs-theme-value="dark" aria-pressed="false">
							<span><i class="bi bi-moon-stars-fill me-2"></i>Dark</span>
							<i class="bi bi-check2 d-none"></i>
						</button>
					</li>
					<li>
						<button type="button"
							class="dropdown-item d-flex align-items-center justify-content-between"
							data-bs-theme-value="auto" aria-pressed="false">
							<span><i class="bi bi-circle-half me-2"></i>Auto</span>
							<i class="bi bi-check2 d-none"></i>
						</button>
					</li>
				</ul>
			</div>
		</div>
	</div>
</div>
</nav>

<div class="container pb-4">
<?php
if (isset($messages) && is_array($messages)) {
	foreach ($messages AS $message) {
		echo $message;
	}
}
?>
</div>

<style>
	#bd-theme .bi {
		line-height: 1;
	}
</style>
