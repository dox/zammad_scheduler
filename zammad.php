<?php
include_once("./inc/autoload.php");

if ($_SESSION['logon'] != true) {
  header("Location: logon.php");
  exit;
}
?>
<!DOCTYPE html>
<html lang="en" class="h-100">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="">
  <meta name="author" content="Andrew Breakspear">
  <title>Task Scheduler</title>
  
  <!-- Bootstrap core CSS/JS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-KyZXEAg3QhqLMpG8r+8fhAXLRk2vvoC2f3B09zVXn8CA5QIVfZOJ3BCsw2P0p/We" crossorigin="anonymous">
  <!-- JavaScript Bundle with Popper -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-U1DAWAznBHeqEIlVSCgzq+c9gqGAJn5c/t99JyeKa9xxaYpSvHU5awsuZVVFIhvj" crossorigin="anonymous"></script>
  
  <script src="js/app.js"></script>
</head>

<body class="bg-light">
  <?php
  use Zendesk\API\HttpClient as ZendeskAPI;
  use ZammadAPIClient\Client;
  use ZammadAPIClient\ResourceType;
  
  
  $agentID = '1713';

  
  
  $currentTickets = $client->resource( ResourceType::TICKET )->search("owner_id:" . $agentID . " AND state_id:2");
  

  
  
   
  ?>
  
  <div class="container">
      <?php
      $title = "<svg width=\"1em\" height=\"1em\"><use xlink:href=\"inc/icons.svg#tickets\"/></svg> Tickets";
      $subtitle = "Daily, weekly, monthly and yearly tickets that are auto-scheduled to appear on Zendesk.";
  
      echo makeTitle($title, $subtitle);
      ?>
  
      <ul class="nav nav-tabs" id="myTab" role="tablist">
          <li class="nav-item">
            <a class="nav-link active" aria-current="page" href="#">My Tickets (<?php echo count($currentTickets); ?>)</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" aria-current="page" href="#">Inactive</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" aria-current="page" href="#">Inactive</a>
          </li>
      </ul>
  
      <div class="tab-content" id="myTabContent">
        <?php
        $output  = "<div class=\"row\">";
        $output .= "<div class=\"col\">";
        
        foreach ($currentTickets AS $ticket) {
           if (!is_array($ticket)) {
             $ticket = $ticket->getValues();
             
             
             $output .= "<div class=\"card mb-3\" id=\"ticketID-" . $ticket['id'] . "\" data-bs-toggle=\"modal\"  data-bs-target=\"#menuModal\" onclick=\"displayMenu(this.id)\">";
               $output .= "<div class=\"card-body\">";
                 $output .= "<h5 class=\"card-title\">" . $ticket['title'] . "</h5>";
                 $output .= "<h6 class=\"card-subtitle mb-2 text-muted\">Created by " . $ticket['customer_id'] . " on " . $ticket['created_at'] . "</h6>";
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
</body>
</html>

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