<?php
$email_address = "andrew.breakspear@seh.ox.ac.uk";

use ZammadAPIClient\Client;
use ZammadAPIClient\ResourceType;

class agents {
	public function getZammadAgents() {
		global $client;
		
		$agents = $client->resource( ResourceType::USER )->search("role_ids:2 AND active:true");
			
		$agentsArray = array();
		foreach ($agents AS $agentObject) {
			if (!is_array($agentObject)) {
				$agentObject = $agentObject->getValues();
				
				$agentsArray[$agentObject['id']] = $agentObject;
			}
		}
		
		return $agentsArray;
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
		global $database, $client;
		
		$sql  = "SELECT * FROM agents";
		$sql .= " WHERE agent_id = '" . $id . "' ";
		
		$agent = $database->getRows($sql);
		
		$agentObject = $client->resource(ZammadAPIClient\ResourceType::USER)->get($id);
		$agentArray = $agentObject->getValues();	
		
		return $agentArray;
	}
	
	public function create($array = null) {
		global $database;
	
		foreach ($array AS $updateItem => $value) {
			$value = str_replace("'", "\'", $value);
			$sqlUpdate[] = $updateItem ." = '" . $value . "' ";
		}
	
		$sql  = "INSERT INTO agents";
		$sql .= " SET " . implode(", ", $sqlUpdate);
	
		$create = $database->query($sql);
	
		// log this!
		$logRecord = new logs();
		$logRecord->description = "Agent created: " . $array['id'];
		$logRecord->type = "admin";
		$logRecord->log_record();
	
		return $create;
	}
	
	public function ticketsInvolvedWith($agentID = null) {
		global $database;

		$sql  = "SELECT * FROM tickets ";
		$sql .= "WHERE zammad_customer = '" . $agentID . "' ";
		$sql .= "OR zammad_agent = '" . $agentID . "'";

		$tickets = $database->getRows($sql);

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
		
		// remove 'Users' from the group list
		if (($key = array_search("Users", $groupArray)) !== false) {
			unset($groupArray[$key]);
		}
		
		$groupArray = array_unique($groupArray);

		return $groupArray;
	}


}
?>
