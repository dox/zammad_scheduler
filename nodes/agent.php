<?php
$agentsClass = new agents();
$agent = $agentsClass->getZammadAgent($_GET['agentUID']);

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
			<img src="<?php echo "http://help.seh.ox.ac.uk/api/v1/users/image/" . $agent['image']; ?>" class="img-thumbnail" alt="...">
			<p>First Name: <?php echo $agent['firstname']; ?></p>
			<p>Last Name: <?php echo $agent['lastname']; ?></p>
			<p>Login: <?php echo $agent['login']; ?></p>
			<p>Email: <?php echo $agent['email']; ?></p>
			<p>Zammad Groups: <?php echo implode(", ", array_keys($agent['groups'])); ?></p>

		</div>

		<div class="col-lg-6">
			<h4>Jobs assigned to/logged by:</h4>
			<?php
			echo $ticketsClass->showTicketsTable($ticketsClass->getTicketsByAgentOrCustomer($agent['id']));
			?>
		</div>
	</div>
	<div class="divider"></div>
	<div class="row">
		<?php
		printArray($agent);
		?>
	</div>
</div>