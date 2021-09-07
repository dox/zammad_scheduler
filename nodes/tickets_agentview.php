<?php
use Zendesk\API\HttpClient as ZendeskAPI;
use ZammadAPIClient\Client;
use ZammadAPIClient\ResourceType;

$agentsClass = new agents();

$currentTickets = $client->resource( ResourceType::TICKET )->search("owner_id:" . $_SESSION['user_id'] . " AND (state_id:2 OR state_id:3 OR state_id:5)");
?>

<div class="container">
	<?php
	$title = "<svg width=\"1em\" height=\"1em\"><use xlink:href=\"inc/icons.svg#agentview\"/></svg> Agent View";
	
	echo makeTitle($title);
	?>
	
	<ul class="nav nav-tabs mb-3" id="myTab" role="tablist">
		<li class="nav-item">
			<a class="nav-link active" aria-current="page" href="#">My Tickets (<?php echo count($currentTickets); ?>)</a>
		</li>
		<!--<li class="nav-item">
			<a class="nav-link" aria-current="page" href="#">Inactive</a>
		</li>
		<li class="nav-item">
			<a class="nav-link" aria-current="page" href="#">Inactive</a>
		</li>-->
	</ul>
	
	<div class="tab-content" id="myTabContent">
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