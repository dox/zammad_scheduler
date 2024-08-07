<?php
include_once("./inc/autoload.php");
if ($_SESSION['logon'] != true) {
	header("Location: logon.php");
	exit;
}

use ZammadAPIClient\Client;
use ZammadAPIClient\ResourceType;

$agentsClass = new agents();

$ticketID = $_GET['ticketID'];
  
$ticketObject = $client->resource( ResourceType::TICKET )->get($ticketID);
exitOnError($ticketObject);

$ticket = $ticketObject->getValues();
$ticket_articles = $ticketObject->getTicketArticles();
?>

<div class="modal-header">
	<h5 class="modal-title"><?php echo $ticket['title']; ?></h5>
	<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>

<form action="index.php?n=tickets_agentview" method="post">
<div class="modal-body">
	<?php
	foreach ($ticket_articles AS $ticket_article) {
		$article_content = $ticket_article->getValues();
		if ($article_content['sender'] != "System" ) {
			
			$niceOutput = str_replace("src=\"/api/v1/ticket_attachment/", "src=\"https://help.seh.ox.ac.uk/api/v1/ticket_attachment/", $article_content['body']);
			
			$output  = "";
			$output .= $niceOutput;
			$output .= "<hr />";
			
			echo $output;
		}
	}
	?>
	
	<div class="mb-3">
		<?php
		if ($ticket['owner_id'] == "1") {
			$textDisable = " disabled ";
			$textContent  = "Please assign the ticket before you update it";
		} else {
			$textDisable = "";
			$textContent = "";
		}
		$output  = "<label for=\"exampleFormControlTextarea1\" class=\"form-label\">Your Update</label>";
		$output .= "<textarea " . $textDisable . "class=\"form-control\" id=\"ticketBody\" name=\"ticketBody\" rows=\"3\"></textarea>";
		
		echo $output;
		echo $textContent;
		?>
	</div>
	<div class="mb-3">
		<label for="owner_id" class="form-label">Owner</label>
		<select class="form-select" id="owner_id" name="owner_id">
			<?php
			foreach ($agentsClass->getAgents() AS $agent) {				
				$output  = "<option value=\"" . $agent['agent_id'] . "\"";
				if ($ticket['owner_id'] == "1" && $agent['agent_id'] == $_SESSION['user_id']) {
					$output .= " selected";
				} else {
					if ($ticket['owner_id'] == $agent['agent_id']) {
						$output .= " selected";
					}
				}
				$output .= ">" . $agent['firstname'] . " " . $agent['lastname'] . "</option>";

				echo $output;
			}
			?>
		</select>
	</div>
</div>

<div class="modal-footer">
	<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
	<div class="btn-group">
		<div class="btn-group">
			<button type="submit" class="btn btn-primary" onclick="zammadTicketUpdate(<?php echo $ticket['id']; ?>, 'open')">Update</button>
			<button type="button" class="btn btn-primary dropdown-toggle dropdown-toggle-split" id="dropdownMenuReference" data-bs-toggle="dropdown" aria-expanded="false" data-bs-reference="parent">
			  <span class="visually-hidden">Toggle Dropdown</span>
			</button>
			<ul class="dropdown-menu" aria-labelledby="dropdownMenuReference">
				<li><a class="dropdown-item" href="#" onclick="zammadTicketUpdate(<?php echo $ticket['id']; ?>, 'open')">Update</a></li>
				<li><a class="dropdown-item" href="#" onclick="zammadTicketUpdate(<?php echo $ticket['id']; ?>, 'closed')">Update and Close</a></li>
			</ul>
		  </div>
	</div>
</div>
<br /><br />
</form>