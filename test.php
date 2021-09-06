<?php
include_once("./inc/autoload.php");
if ($_SESSION['logon'] != true) {
	header("Location: logon.php");
	exit;
}

use ZammadAPIClient\Client;
use ZammadAPIClient\ResourceType;

$ticketID = '1713';
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

<div class="modal-body">
	<?php
	foreach ($ticket_articles AS $ticket_article) {
		$article_content = $ticket_article->getValues();
		if ($article_content['sender'] != "System" ) {
			//printArray($article_content);
			$output  = "";
			$output .= $article_content['body'];
			$output .= "<hr />";
			
			echo $output;
		}
		
		
		
		
		
		
	}
	?>
	
	<div class="mb-3">
		<label for="exampleFormControlTextarea1" class="form-label">Your Update</label>
		<textarea class="form-control" id="exampleFormControlTextarea1" rows="3"></textarea>
	</div>
</div>

<div class="modal-footer">
	<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
	<div class="btn-group">
		<button type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">Action</button>
		<ul class="dropdown-menu">
			<li><a class="dropdown-item" href="#">Update</a></li>
			<li><a class="dropdown-item" href="#">Update and Close</a></li>
		</ul>
	</div>
</div>