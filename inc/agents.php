<?php
$email_address = "andrew.breakspear@seh.ox.ac.uk";

class agents {

	public function getAgents() {
		/*global $client;

		$agents = $client->resource(ZammadAPIClient\ResourceType::USER )->search("role_ids:2 AND active:true");

		if ( !is_array($agents) ) {
			exitOnError($agents);
		}

		return $agents;
		*/
		global $database;
		
		$sql  = "SELECT * FROM agents";
		$sql .= " ORDER BY lastname DESC";
		
		$agents = $database->query($sql)->fetchAll();
	
		return $agents;
	}
	
	public function getAgentsByGroup($groupID = null) {
		global $database;
		
		$sql  = "SELECT * FROM agents";
		$sql .= " WHERE group_id = '" . $groupID . "' ";
		$sql .= " ORDER BY lastname DESC";
		
		$agents = $database->query($sql)->fetchAll();
	
		return $agents;
	}

	public  function getAgent($id = null) {
		global $database;
		
		$sql  = "SELECT * FROM agents";
		$sql .= " WHERE agent_id = '" . $id . "' ";
		
		$agent = $database->query($sql)->fetchArray();
	
		return $agent;
	}

	public function displayAgents() {

		$output  = "<table class=\"table table-striped\">";
		$output .= "<thead>";
		$output .= "<tr>";
		$output .= "<th scope=\"col\">ID</th>";
		$output .= "<th>Name</th>";
		$output .= "<th>Email</th>";
		$output .= "<th><span class=\"float-end\">Jobs Logged/Assigned</span></th>";
		$output .= "</tr>";
		$output .= "</thead>";

		$output .= "<tbody>";

		foreach ($this->getAgents() AS $agent) {
			$agent = $agent->getValues();

			$agentURL = "index.php?n=agent&agentUID=" . $agent['id'];
			//$jobsLogged = jobs::jobs_logged($agent->zendesk_id);
			//$jobsAssigned = jobs::jobs_assigned($agent->zendesk_id);

			$output .= "<tr>";
			$output .= "<td>" . $agent['id'] . "</td>";
			$output .= "<td><a href=\"" . $agentURL . "\">" . $agent['firstname'] . " " . $agent['lastname'] . "</a></td>";
			$output .= "<td>" . $agent['email'] . "</td>";
			$output .= "<td><span class=\"float-end\"><span class=\"badge bg-primary\">" . count($jobsLogged) . "</span> / <span class=\"badge bg-success\">" . count($jobsAssigned) . "<span></span></td>";
			$output .= "</tr>";
		}

		$output .= "</tbody>";
		$output .= "</table>";

		return $output;
	}

	public function ticketsInvolvedWith($agentID = null) {
		global $database;

		$sql  = "SELECT * FROM tickets ";
		$sql .= "WHERE zammad_customer = '" . $agentID . "' ";
		$sql .= "OR zammad_agent = '" . $agentID . "'";

		$tickets = $database->query($sql)->fetchAll();

		return $tickets;

	}

	public function ticketsOwnedBy($agentID = null) {
		global $client;

		$searchText = "owner_id:" . $agentID . " AND state_id:2";

		$currentTickets = $client->resource(ZammadAPIClient\ResourceType::TICKET )->search($searchText);

		return $currentTickets;

	}

	public function groups() {
		global $client;

		$groupsObject = $client->resource(ZammadAPIClient\ResourceType::GROUP)->all();
			
			$groupArray = array();
		foreach ($groupsObject AS $groupObject) {
			if (!is_array($groupObject)) {
				$groupObject = $groupObject->getValues();
			
				$groupArray[$groupObject['id']] = $groupObject['name'];
			}
		}
		
		$groupArray = array_unique($groupArray);

		return $groupArray;
	}


}
?>
