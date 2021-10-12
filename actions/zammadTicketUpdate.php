<?php
include_once("../inc/autoload.php");




use Zendesk\API\HttpClient as ZendeskAPI;
use ZammadAPIClient\Client;
use ZammadAPIClient\ResourceType;

$tickets = new tickets();
$ticketExisting = $client->resource( ZammadAPIClient\ResourceType::TICKET )->get($_POST['ticketID']);
$ticketValues = $ticketExisting->getValues();

$agentsClass = new agents();

$agentBeingAssigned = $agentsClass->getAgent($_POST['ticketOwner']);
printArray($agentBeingAssigned);

if ($_POST['ticketBody'] <> "") {
	$ticket_data = [
		'ticket_id'		=> $ticketValues['id'],
		'group_id'		=> $agentBeingAssigned['group_id'],
		'owner_id'	=> $_POST['ticketOwner'],
		'content_type' => 'text/html',
		'body' => $_POST['ticketBody'],
		'internal' => 'false'
	];
	
	$ticketUpdate = $client->resource( ZammadAPIClient\ResourceType::TICKET_ARTICLE );
	$ticketUpdate->setValues($ticket_data);
	$ticketUpdate->save();
	exitOnError($ticketUpdate);
	
}

$ticket_data = [
	'id'		=> $ticketValues['id'],
	'group_id'	=> $agentBeingAssigned['group_id'],
	'owner_id'	=> $_POST['ticketOwner'],
	'state' 	=> $_POST['ticketState']
];

$ticketExisting->setValues($ticket_data);
$ticketExisting->save();
exitOnError($ticketExisting);

$logRecord = new logs();
$logRecord->description = "Ticket " . $ticketValues['id'] . " updated.  Owner: " . $_POST['ticketOwner'] . " - Group: " . $agentBeingAssigned['group_id'];
$logRecord->type = "ticket";
$logRecord->log_record();
?>