<?php
use ZammadAPIClient\Client;
use ZammadAPIClient\ResourceType;

$client = new Client($zammad_api_client_config);

$email_address = "andrew.breakspear@seh.ox.ac.uk";


class agents {

	public function getAgents() {
		global $client;

		$agents = $client->resource( ResourceType::USER )->search("role_ids:2 AND active:true");

		if ( !is_array($agents) ) {
			exitOnError($agents);
		}

		return $agents;
	}

	public static function getAgent($id = null) {
		global $client;

		if (array_key_exists($id, $_SESSION['cached_agents'])) {
			$agent = $_SESSION['cached_agents'][$id];
		} else {


			$agent = $client->resource( ResourceType::USER )->get($id);
			$agent = $agent->getValues();

			$_SESSION['cached_agents'][$agent['id']] = $agent;
		}



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

		$currentTickets = $client->resource( ResourceType::TICKET )->search($searchText);

		return $currentTickets;

	}

	public function groups() {
		//global $client;

		//$groupsObject = $client->resource(ResourceType::GROUP)->all();
		//$groups = $groupsObject[2]->getValues();

		/*
		printArray($groups);

		foreach ($groups AS $group) {
			$groupArray[$group['id']] = $group['name'];
		}
		*/

		$groupArray[2] = "IT Support";
		$groupArray[3] = "Maintenance";
		$groupArray[4] = "Housekeeping";
		$groupArray[5] = "Gardens";

		return $groupArray;
	}


}
?>
