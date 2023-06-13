<?php
use ZammadAPIClient\Client;
use ZammadAPIClient\ResourceType;

$agentsClass = new agents();

$currentTickets = $client->resource( ResourceType::TICKET )->search("owner_id:" . $_SESSION['user_id'] . " AND (state_id:1 OR state_id:2 OR state_id:3 OR state_id:5)");
$unassignedTickets = $client->resource( ResourceType::TICKET )->search("owner_id:1 AND (state_id:1 OR state_id:2)");

?>

<div class="container">
	<div class="alert alert-danger" role="alert">
	  <h1>This application is being retired in <span id="demo"></span><br />
	  Please use <a href="https://help.seh.ox.ac.uk">https://help.seh.ox.ac.uk</a></h1>
	</div>
	
	<ul class="nav nav-tabs mb-3" id="myTab" role="tablist">
		<li class="nav-item">
			<button class="nav-link active" id="my-tab" data-bs-toggle="tab" data-bs-target="#myTickets" type="button" role="tab" aria-controls="unassigned" aria-selected="true">My Tickets (<?php echo count($currentTickets); ?>)</button>
		</li>
		<li class="nav-item">
			<button class="nav-link" id="unassigned-tab" data-bs-toggle="tab" data-bs-target="#unassignedTickets" type="button" role="tab" aria-controls="unassigned" aria-selected="true">Unassigned (<?php echo count($unassignedTickets); ?>)</button>
		</li>
	</ul>
	
	<div class="tab-content">
		<div class="tab-pane fade show active" id="myTickets" role="tabpanel" aria-labelledby="my-tab">
			<?php
			$output  = "<div class=\"row\">";
			$output .= "<div class=\"col\">";
			
			foreach ($currentTickets AS $ticket) {
				if (!is_array($ticket)) {
					$ticket = $ticket->getValues();
					
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
		<div class="tab-pane fade" id="unassignedTickets" role="tabpanel" aria-labelledby="unassigned-tab">
			<?php
			$output  = "<div class=\"row\">";
			$output .= "<div class=\"col\">";
			
			foreach ($unassignedTickets AS $ticket) {
				if (!is_array($ticket)) {
					$ticket = $ticket->getValues();
					
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
	</div>
</div>

<div class="modal fade" id="menuModal" tabindex="-1" aria-labelledby="menuModal" aria-hidden="true">
  <div class="modal-dialog  ">
	<div class="modal-content">
	  <div id="menuContentDiv"></div>
	</div>
  </div>
</div>

<script>
  function displayMenu(this_id) {
	var ticketID = this_id.replace("ticketID-", "");
	var request = new XMLHttpRequest();
  
	request.open('GET', '/test.php?ticketID=' + ticketID, true);
	//request.open('GET', '/test.php', true);
  
	request.onload = function() {
	  if (request.status >= 200 && request.status < 400) {
		var resp = request.responseText;
  
		menuContentDiv.innerHTML = resp;
	  }
	};
  
	request.send();
  }
</script>



<script>
// Set the date we're counting down to
var countDownDate = new Date("Jun 31, 2023 12:00:00").getTime();

// Update the count down every 1 second
var x = setInterval(function() {

  // Get today's date and time
  var now = new Date().getTime();

  // Find the distance between now and the count down date
  var distance = countDownDate - now;

  // Time calculations for days, hours, minutes and seconds
  var days = Math.floor(distance / (1000 * 60 * 60 * 24));
  var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
  var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
  var seconds = Math.floor((distance % (1000 * 60)) / 1000);

  // Display the result in the element with id="demo"
  document.getElementById("demo").innerHTML = days + "d " + hours + "h "
  + minutes + "m " + seconds + "s ";

  // If the count down is finished, write some text 
  if (distance < 0) {
	clearInterval(x);
	document.getElementById("demo").innerHTML = "EXPIRED";
  }
}, 1000);
</script>