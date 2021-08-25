<?php
$agentsClass = new agents();
$agent = $agentsClass->getAgent($_GET['agentUID']);

$ticketsClass = new tickets();
?>

<div class="container">
	<?php
	$title = "<svg width=\"1em\" height=\"1em\"><use xlink:href=\"inc/icons.svg#agents\"/></svg> " . $agent['firstname'] . " " . $agent['lastname'];
	$subtitle = "Agent ID: " . $agent['id'];

	echo makeTitle($title, $subtitle, $icons);
	?>

	<div class="row">
		<div class="col-lg-6 mb-3">
			<p>Zammad ID: <?php echo $agent['id']; ?></p>
			<p>First Name: <?php echo $agent['firstname']; ?></p>
			<p>Last Name: <?php echo $agent['lastname']; ?></p>
			<p>Email: <?php echo $agent['email']; ?></p>
			<p>Mobile: <?php echo $agent['mobile']; ?></p>
			<p>Login: <?php echo $agent['login']; ?></p>
			<p>Active: <?php echo $agent['active']; ?></p>
			<p>Last Login: <?php echo $agent['last_login']; ?></p>

		</div>

		<div class="col-lg-6">
			<h4>Jobs assigned to/logged by:</h4>
			<?php
			foreach($agentsClass->ticketsInvolvedWith($agent['id']) AS $ticket) {
					echo $ticketsClass->ticketDisplay($ticket['uid']);
			}
			?>
		</div>
	</div>
</div>


<?php
printArray($agent);

?>