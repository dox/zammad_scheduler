<?php
$agentsClass = new agents();
$agent = $agentsClass->getAgent($_GET['agentUID']);

$ticketsClass = new tickets();
?>

<div class="container">
	<?php
	$title = "<svg width=\"1em\" height=\"1em\"><use xlink:href=\"inc/icons.svg#agents\"/></svg> " . $agent['firstname'] . " " . $agent['lastname'];
	$subtitle = "Agent ID: " . $agent['agent_id'];

	echo makeTitle($title, $subtitle, $icons);
	?>

	<div class="row">
		<div class="col-lg-6 mb-3">
			<p>First Name: <?php echo $agent['firstname']; ?></p>
			<p>Last Name: <?php echo $agent['lastname']; ?></p>
			<p>Login: <?php echo $agent['ldap']; ?></p>
			<p>Zammad Agent ID: <?php echo $agent['agent_id']; ?></p>
			<p>Zammad Group: <?php echo $agentsClass->groups()[$agent['group_id']]; ?> (<?php echo $agent['group_id']; ?>)</p>

		</div>

		<div class="col-lg-6">
			<h4>Jobs assigned to/logged by:</h4>
			<?php
			foreach(tickets::getTicketsByAgentOrCustomer($agent['agent_id']) AS $ticket) {
					echo $ticketsClass->ticketDisplay($ticket['uid']);
			}
			?>
		</div>
	</div>
</div>