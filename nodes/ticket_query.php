<?php
use ZammadAPIClient\Client;
use ZammadAPIClient\ResourceType;

$agentsClass = new agents();

$ticketID = $_POST['ticketID'];

$tickets = $client->resource( ResourceType::TICKET )->search("number:" . $ticketID);
?>

<div class="container">
	<?php
	$title = "<svg width=\"1em\" height=\"1em\"><use xlink:href=\"inc/icons.svg#tickets\"/></svg> Ticket Query";
	$subtitle = "Ticket ID: " . $ticketID;
		
	echo makeTitle($title, $subtitle);
	?>
	
	<form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post">
		<div class="mb-3">
			<label for="exampleInputEmail1" class="form-label">Ticket ID</label>
			<input type="number" class="form-control" id="ticketID" name="ticketID" value="<?php echo $ticketID; ?>" aria-describedby="emailHelp">
			
		<button type="submit" class="btn btn-primary">Submit</button>
	</div>
		  
	<?php
	$output  = "<div class=\"row\">";
	$output .= "<div class=\"col\">";
	
	foreach ($tickets AS $ticket) {
		if (!is_array($ticket)) {
			$ticket = $ticket->getValues();
			
			echo "<h2>Ticket: " . $ticket['number'] . "</h2>";
			printArray($ticket);
			
			$customer = $agentsClass->getZammadAgent($ticket['customer_id']);
			
			echo "<h2>Customer: " . $customer['firstname'] . " " . $customer['lastname'] . "</h2>";
			printArray($customer);
			
			$agent = $agentsClass->getZammadAgent($ticket['owner_id']);
			
			echo "<h2>Agent: " . $agent['firstname'] . " " . $agent['lastname'] . "</h2>";
			printArray($agent);
		}
	}
	 
	$output .= "</div>"; //col
	$output .= "</div>"; //row
	 
	echo $output;
	?>
</div>