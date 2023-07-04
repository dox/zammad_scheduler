<?php
require_once('../inc/autoload.php');

$tickets = new tickets();

foreach($tickets->getTickets('Monthly') AS $ticket) {
	$ticket_data = [
		'group_id'    => $ticket['zammad_group'],
		'owner_id'    => $ticket['zammad_agent'],
		'priority_id' => $ticket['zammad_priority'],
		'state_id'    => 1,
		'title'       => $ticket['subject'],
		'customer_id' => $ticket['zammad_customer'],
		'article'     => [
			'subject' => $ticket['subject'],
			'body'    => $ticket['body'],
		],
	];
	
	if ($ticket['status'] == "Enabled") {
		printArray($ticket_data);
		//$tickets->ticketCreateInZammad($ticket_data);
	}
}
?>
