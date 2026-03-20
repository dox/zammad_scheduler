<?php
$email_address = "andrew.breakspear@seh.ox.ac.uk";

use ZammadAPIClient\Client;
use ZammadAPIClient\ResourceType;

class agents {
	public function getAgents() {
		return $this->getZammadAgents();
	}

	public function getZammadAgents() {
		global $client;
		
		if (!isset($_SESSION['zammad_agents'])) {
			$zammad_agents = $client->resource( ResourceType::USER )->search("role_ids:2 AND active:true");
			
			$agentsArray = array();
			foreach ($zammad_agents AS $agentObject) {
				if (!is_array($agentObject)) {
					$agentObject = $agentObject->getValues();
					
					$agentsArray[$agentObject['id']] = $agentObject;
				}
			}
			
			$_SESSION['zammad_agents'] = $agentsArray;
		}
		
		return $_SESSION['zammad_agents'];
	}
	
	public function getZammadAgent($id = null) {
		if (!isset($_SESSION['zammad_agents'][$id])) {
			global $client;
			$zammad_agent = $client->resource(ResourceType::USER)->search("id:" . (int) $id);

			if (isset($zammad_agent[0]) && !is_array($zammad_agent[0])) {
				$_SESSION['zammad_agents'][$id] = $zammad_agent[0]->getValues();
			} else {
				$_SESSION['zammad_agents'][$id] = null;
			}
		}
		
		return $_SESSION['zammad_agents'][$id];
	}

	public function getAgent($id = null) {
		$agent = $this->getZammadAgent($id);

		if (is_array($agent) && !isset($agent['group_id']) && isset($agent['groups']) && is_array($agent['groups'])) {
			$groupIds = array_keys($agent['groups']);
			if (!empty($groupIds)) {
				$agent['group_id'] = $groupIds[0];
			}
		}

		return $agent;
	}
	
	public function getAgentsByGroup($groupID = null) {
		global $database;
		$groupID = $database->escape($groupID);
		
		$sql  = "SELECT * FROM agents";
		$sql .= " WHERE group_id = '" . $groupID . "' ";
		$sql .= " ORDER BY lastname DESC";
		
		$agents = $database->getRows($sql);
	
		return $agents;
	}
	
	public function create($array = null) {
		global $database;
	
		foreach ($array AS $updateItem => $value) {
			$value = $database->escape($value);
			$sqlColumns[] = $updateItem;
			$sqlValues[] = "'" . $value . "'";
		}
	
		$sql  = "INSERT INTO agents";
		$sql .= " (" . implode(", ", $sqlColumns) . ")";
		$sql .= " VALUES (" . implode(", ", $sqlValues) . ")";
	
		$create = $database->exec($sql);
	
		// log this!
		$logRecord = new logs();
		$logRecord->description = "Agent created: " . $array['id'];
		$logRecord->type = "admin";
		$logRecord->log_record();
	
		return $create;
	}
	
	public function ticketsInvolvedWith($agentID = null) {
		global $database;
		$agentID = $database->escape($agentID);

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
		
		if (!isset($_SESSION['zammad_groups'])) {
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
			
			$_SESSION['zammad_groups'] = $groupArray;
		}
		
		return $_SESSION['zammad_groups'];
	}

	public function localAgentsTableExists() {
		global $database;

		$sql = "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = 'public' AND table_name = 'agents'";

		return ((int) $database->getCol($sql)) > 0;
	}


}
?>
