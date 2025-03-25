<?php
$tickets = new tickets();
$ticket = $tickets->getTicket(207);

$ticket_data = [
	'uid'		  => $ticket->uid,
	'group_id'    => $ticket->zammad_group,
	'owner_id'    => $ticket->zammad_agent,
	'priority_id' => $ticket->zammad_priority,
	'state_id'    => 1,
	'title'       => $ticket->subject,
	'customer_id' => $ticket->zammad_customer,
	'article'     => [
		'subject' => $ticket->subject,
		'body'    => $ticket->body,
	],
];

//$existingTicket = $tickets->ticketValuesGetFromZammad('26155');
$result = $tickets->ticketCreateInZammad2($ticket_data);
printArray($result);
?>

<div class="container">
	<?php
	$title = "<svg width=\"1em\" height=\"1em\"><use xlink:href=\"inc/icons.svg#tickets\"/></svg> Ticket ID: " . $ticket->uid;
	$subtitle = $ticket->subject;
	$icons[] = array("class" => "btn-warning", "name" => "<svg width=\"1em\" height=\"1em\"><use xlink:href=\"inc/icons.svg#run-now\"/></svg> Run Now", "value" => "onclick=\"zammadTicketCreate(this.id);\" id=\"" . $ticket->uid . "\"");
	
	echo makeTitle($title, $subtitle, $icons);
	?>
	
	<?php
	printArray($ticket);
	?>
</div>


<script>
function runJob() {
	if (window.confirm("Are you sure you want to run this job now?")) {
			location.href = 'index.php?n=tickets&jobRun=<?php echo $ticket->uid; ?>';
	}
}


</script>
