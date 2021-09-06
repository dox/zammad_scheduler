<?php
include_once("../inc/autoload.php");

use Zendesk\API\HttpClient as ZendeskAPI;
use ZammadAPIClient\Client;
use ZammadAPIClient\ResourceType;

$tickets = new tickets();
$ticketExisting = $client->resource( ResourceType::TICKET )->get($_POST['ticketID']);
$ticketValues = $ticketExisting->getValues();

$ticket_data = [
	'ticket_id'		=> $ticketValues['id'],
	'content_type' => 'text/html',
	'body' => $_POST['ticketBody'],
	'internal' => 'false'
];
printArray($ticket_data);

$ticketUpdate = $client->resource( ResourceType::TICKET_ARTICLE );
$ticketUpdate->setValues($ticket_data);
$ticketUpdate->save();
exitOnError($ticketUpdate);
?>