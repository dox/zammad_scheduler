<div class="container">
	<?php
	$title = "Task Scheduler";
	$subtitle = "A simple web-based utility to create and manage reoccurring ticket creation in Zammad.";
	$icons[] = array("class" => "btn-primary", "name" => "<svg width=\"1em\" height=\"1em\"><use xlink:href=\"inc/icons.svg#zendesk\"/></svg> View Helpdesk", "value" => "onclick=\"location.href='" . zammad_url . "'\"");

	echo makeTitle($title, $subtitle, $icons);
	?>

	<div class="row row-cols-1 row-cols-md-3 mb-3 text-center">
    <div class="col">
      <div class="card mb-4 shadow-sm">
      <div class="card-header">
        <h4 class="my-0 fw-normal">Step 1</h4>
      </div>
      <div class="card-body">
        <h1 class="card-title pricing-card-title">Configure</h1>
        <ul class="list-unstyled mt-3 mb-4">
          <li>Follow the install from the README</li>
          <li>Setup the cron tasks on your server</li>
        </ul>
				<a href="https://www.github.com/dox/zammad_scheduler" role="button" class="btn btn-lg btn-block btn-outline-primary">README</a>
      </div>
    </div>
    </div>
    <div class="col">
      <div class="card mb-4 shadow-sm">
      <div class="card-header">
        <h4 class="my-0 fw-normal">Step 2</h4>
      </div>
      <div class="card-body">
        <h1 class="card-title pricing-card-title">Agents</h1>
        <ul class="list-unstyled mt-3 mb-4">
          <li>Locate each Agent's ID from Zendesk</li>
          <li>Setup the details here</li>
        </ul>
				<a href="index.php?n=agents" role="button" class="btn btn-lg btn-block btn-outline-primary">Agents</a>
      </div>
    </div>
    </div>
    <div class="col">
      <div class="card mb-4 shadow-sm">
      <div class="card-header">
        <h4 class="my-0 fw-normal">Step 3</h4>
      </div>
      <div class="card-body">
        <h1 class="card-title pricing-card-title">Tickets</h1>
        <ul class="list-unstyled mt-3 mb-4">
          <li>Create scheduled tickets</li>
          <li>Test tickets by running 'now'</li>
        </ul>
				<a href="index.php?n=tickets" role="button" class="btn btn-lg btn-block btn-outline-primary">Tickets</a>
      </div>
    </div>
    </div>
  </div>
</div>


<?php
$agentsClass = new agents();
?>

<div class="container">
  <?php
  foreach ($agentsClass->getAgents() AS $agent) {
    $agent = $agent->getValues();
    
    $currentTickets = $agentsClass->ticketsOwnedBy($agent['id']);
    
    $currentTicketsORIGINAL = $agent['preferences']['tickets_open'];
    
    if (count($currentTickets) > 0) {
      echo $agent['firstname'] . " " . $agent['lastname'] . ": Current Tickets = " . count($currentTickets) . "<br />";
    }
    
  }
  ?>
</div>