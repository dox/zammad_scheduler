<?php
use ZammadAPIClient\Client;
use ZammadAPIClient\ResourceType;

$agentsClass = new agents();

$ticketID = $_POST['ticketID'];

$tickets = $client->resource( ResourceType::TICKET )->search("id:" . $ticketID);
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
			<div id="emailHelp" class="form-text">This is the database ID - not the ticket number!  Click on the ticket URL in Zammad to find the ID</div>
		<button type="submit" class="btn btn-primary">Submit</button>
	</div>
		  
	<?php
	$output  = "<div class=\"row\">";
	$output .= "<div class=\"col\">";
	
	foreach ($tickets AS $ticket) {
		if (!is_array($ticket)) {
			$ticket = $ticket->getValues();
			
			printArray($ticket);
			
			$customer = $agentsClass->getAgent($ticket['customer_id']);
			
			$output .= "<div class=\"card mb-3\" id=\"ticketID-" . $ticket['id'] . "\" data-bs-toggle=\"modal\"  data-bs-target=\"#menuModal\" onclick=\"displayMenu(this.id)\">";
			$output .= "<div class=\"card-body\">";
			$output .= "<h5 class=\"card-title\">" . $ticket['title'] . "</h5>";
			$output .= "<h6 class=\"card-subtitle mb-2 text-muted\">" . $customer['firstname'] . " " . $customer['lastname'] . " on " . dateDisplay($ticket['created_at']) . "</h6>";
			//$output .= " This is: " . $customer['firstname'] . " " . $customer['lastname'];
			//$output .= "<p class=\"card-text\">With supporting text below as a natural lead-in to additional content.</p>";
			$output .= "</div>"; //card-body
			$output .= "</div>"; //card
			
			//printArray($ticket);
		}
	}
	 
	$output .= "</div>"; //col
	$output .= "</div>"; //row
	 
	echo $output;
	?>
</div>