<?php
include_once("../inc/autoload.php");

$tickets = new tickets();

$ticket = $tickets->getTicket($_POST['ticketUID']);

$ticket_data = [
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

$tickets->ticketCreateInZammad($ticket_data);
?>
