<?php
$_SERVER['DOCUMENT_ROOT'] = "/var/www/tasks/html";
include_once($_SERVER['DOCUMENT_ROOT'] . "/inc/autoload.php");

$tickets = new tickets();

foreach($tickets->getTickets('Weekly') AS $ticket) {
	$ticket_data = [
		'uid'         => $ticket->uid,
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
	
	if ($ticket->status == "Enabled") {
		$tickets->ticketCreateInZammad($ticket_data);
	}
}
?>
