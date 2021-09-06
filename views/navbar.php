<?php
$currentPage = basename($_SERVER["SCRIPT_FILENAME"], '.php');
?>
<nav class="navbar fixed-top navbar-expand-lg navbar-light bg-light">
<div class="container">
	<a class="navbar-brand" href="index.php">
		<svg width="1em" height="1em">
			<use xlink:href="inc/icons.svg#logo"/>
		</svg> Task Scheduler</a>
	<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
		<span class="navbar-toggler-icon"></span>
	</button>

	<div class="collapse navbar-collapse" id="navbarSupportedContent">
		<ul class="navbar-nav mr-auto">
			<li class="nav-item <?php if($currentPage == "index") { echo "active";}?>">
				<a class="nav-link" href="index.php">
					<svg width="1em" height="1em">
						<use xlink:href="inc/icons.svg#home"/>
					</svg> Home
				</a>
			</li>
			<li class="nav-item <?php if($currentPage == "jobs" || $currentPage == "job_edit") { echo "active";}?>">
				<a class="nav-link" href="index.php?n=tickets">
					<svg width="1em" height="1em">
						<use xlink:href="inc/icons.svg#tickets"/>
					</svg> Tickets
				</a>
			</li>
			<li class="nav-item <?php if($currentPage == "agents" || $currentPage == "agent_edit") { echo "active";}?>">
				<a class="nav-link" href="index.php?n=agents">
					<svg width="1em" height="1em">
						<use xlink:href="inc/icons.svg#agents"/>
					</svg> Agents
				</a>
			</li>
			<li class="nav-item <?php if($currentPage == "logs") { echo "active";}?>">
				<a class="nav-link" href="index.php?n=logs">
					<svg width="1em" height="1em">
						<use xlink:href="inc/icons.svg#logs"/>
					</svg> Logs
				</a>
			</li>
			<li class="nav-item <?php if($currentPage == "tickets_agentview") { echo "active";}?>">
				<a class="nav-link" href="index.php?n=tickets_agentview">
					<svg width="1em" height="1em">
						<use xlink:href="inc/icons.svg#agentview"/>
					</svg> Agent View
				</a>
			</li>
		</ul>
	</div>
</div>
</nav>

<div class="container">
<?php
foreach ($messages AS $message) {
	echo $message;
}
?>
</div>

<style>
	body {
    padding-top: 65px;
}
</style>
