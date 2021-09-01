<?php
require_once('../inc/autoload.php');

$tickets = new tickets();

foreach($tickets->getTickets('Daily') AS $ticket) {
	// fetch the days this ticket is supposed to run
	$freqArray = explode(",", strtoupper($ticket['frequency2']));
	
	// check each of the scheduled days, and check if today is one of them
	foreach ($freqArray AS $dateToRun) {
		if ($dateToRun == strtoupper(date('M-d'))) {
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
				$tickets->ticketCreateInZammad($ticket_data);
			}
		}
	}
}
?>
