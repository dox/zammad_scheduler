<?php
$agentsClass = new agents();



?>

<div class="container">
	<?php
	$title = "<svg width=\"1em\" height=\"1em\"><use xlink:href=\"inc/icons.svg#agents\"/></svg> Agents";
	$subtitle = "Agents imported from Zammad that can be assigned scheduled tickets.";
	//$icons[] = array("class" => "btn-warning", "name" => "<svg width=\"1em\" height=\"1em\"><use xlink:href=\"inc/icons.svg#zendesk\"/></svg> Sync Zendesk Agents", "value" => "onclick=\"location.href='index.php?n=agents&import=true'\"");

	echo makeTitle($title, $subtitle, $icons);
	
	$output  = "<table class=\"table table-striped\">";
	$output .= "<thead>";
	$output .= "<tr>";
	$output .= "<th scope=\"col\">Agent ID</th>";
	$output .= "<th>Name</th>";
	$output .= "<th>Groups</th>";
	$output .= "<th><span class=\"float-end\">Jobs Logged/Assigned</span></th>";
	$output .= "</tr>";
	$output .= "</thead>";
	
	$output .= "<tbody>";
	
	foreach ($agentsClass->getZammadAgents() AS $agent) {
		$agentURL = "index.php?n=agent&agentUID=" . $agent['id'];
		$jobsLogged = tickets::getTicketsByAgent($agent['id']);
		$jobsAssigned = tickets::getTicketsByCustomer($agent['id']);
		
		$output .= "<tr>";
		$output .= "<td>" . $id . "</td>";
		$output .= "<td><a href=\"" . $agentURL . "\">" . $agent['firstname'] . " " . $agent['lastname'] . "</a></td>";
		$output .= "<td>" . implode(", ", array_keys($agent['groups'])) . "</td>";
		$output .= "<td><span class=\"float-end\"><span class=\"badge bg-primary\">" . count($jobsLogged) . "</span> / <span class=\"badge bg-success\">" . count($jobsAssigned) . "<span></span></td>";
		$output .= "</tr>";
	}
	
	$output .= "</tbody>";
	$output .= "</table>";
	
	echo $output;
	?>
</div>