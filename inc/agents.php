<?php
$email_address = "andrew.breakspear@seh.ox.ac.uk";

use ZammadAPIClient\Client;
use ZammadAPIClient\ResourceType;

class agents {
	public function getAgents() {
		return $this->getZammadAgents();
	}

	public function getZammadAgents() {
		if (!isset($_SESSION['zammad_agents']) || !is_array($_SESSION['zammad_agents'])) {
			$zammad_agents = $this->fetchAllZammadUsers();
			$agentsArray = array();

			foreach ($zammad_agents as $agentObject) {
				$agentValues = $this->compactAgent($this->normaliseAgent($agentObject));

				if ($this->shouldIncludeAgent($agentValues)) {
					$agentsArray[$agentValues['id']] = $agentValues;
				}
			}

			uasort($agentsArray, array($this, 'sortAgentsByDisplayName'));
			$_SESSION['zammad_agents'] = $agentsArray;
		}

		return $_SESSION['zammad_agents'];
	}
	
	public function getZammadAgent($id = null) {
		$id = (int) $id;
		if ($id <= 0) {
			return null;
		}

		if (!isset($_SESSION['zammad_agents']) || !is_array($_SESSION['zammad_agents'])) {
			$_SESSION['zammad_agents'] = array();
		}

		if (!array_key_exists($id, $_SESSION['zammad_agents'])) {
			global $client;

			try {
				$zammadAgent = $client->resource(ResourceType::USER)->get($id);
				$_SESSION['zammad_agents'][$id] = $this->compactAgent($this->normaliseAgent($zammadAgent));
			} catch (\Throwable $e) {
				$_SESSION['zammad_agents'][$id] = null;
			}
		}
		
		return $_SESSION['zammad_agents'][$id];
	}

	public function getAgent($id = null) {
		$agent = $this->getZammadAgent((int) $id);

		if (is_array($agent)) {
			$agent['group_id'] = $this->getPrimaryGroupId($agent);
		}

		return $agent;
	}
	
	public function getAgentsByGroup($groupID = null) {
		$groupID = (int) $groupID;
		$agents = array();

		foreach ($this->getZammadAgents() as $agent) {
			if (in_array($groupID, $this->getAgentGroupIds($agent), true)) {
				$agents[$agent['id']] = $agent;
			}
		}

		return $agents;
	}
	
	public function create($array = null) {
		// Agents now come directly from Zammad and are no longer stored locally.
		return false;
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
		
		if (!isset($_SESSION['zammad_groups']) || !is_array($_SESSION['zammad_groups'])) {
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
		return false;
	}

	private function normaliseAgent($agentObject = null) {
		if (is_array($agentObject)) {
			return $agentObject;
		}

		if (is_object($agentObject) && method_exists($agentObject, 'getValues')) {
			return $agentObject->getValues();
		}

		return null;
	}

	private function compactAgent($agent = null) {
		if (!is_array($agent)) {
			return null;
		}

		$compact = array(
			'id' => $agent['id'] ?? null,
			'firstname' => $agent['firstname'] ?? '',
			'lastname' => $agent['lastname'] ?? '',
			'login' => $agent['login'] ?? '',
			'email' => $agent['email'] ?? '',
			'active' => $agent['active'] ?? false,
			'image' => $agent['image'] ?? null,
			'last_login' => $agent['last_login'] ?? null,
			'updated_at' => $agent['updated_at'] ?? null,
			'role_ids' => isset($agent['role_ids']) && is_array($agent['role_ids']) ? array_map('intval', $agent['role_ids']) : array(),
			'group_ids' => isset($agent['group_ids']) && is_array($agent['group_ids']) ? array_map('intval', $agent['group_ids']) : array(),
			'groups' => isset($agent['groups']) && is_array($agent['groups']) ? $agent['groups'] : array(),
		);

		if (empty($compact['id'])) {
			return null;
		}

		return $compact;
	}

	private function fetchAllZammadUsers() {
		$users = $this->fetchAllZammadUsersViaHttp();

		if (is_array($users) && !empty($users)) {
			return $users;
		}

		global $client;

		try {
			$users = $client->resource(ResourceType::USER)->all();
			if (is_array($users)) {
				return $users;
			}
		} catch (\Throwable $e) {
			return array();
		}

		return array();
	}

	private function fetchAllZammadUsersViaHttp() {
		if (!function_exists('curl_init')) {
			return array();
		}

		$allUsers = array();
		$page = 1;
		$perPage = 200;

		while (true) {
			$url = rtrim(zammad_url, '/') . '/api/v1/users?page=' . $page . '&per_page=' . $perPage;
			$response = $this->zammadApiGet($url);

			if (!is_array($response) || empty($response)) {
				break;
			}

			foreach ($response as $user) {
				if (is_array($user) && isset($user['id'])) {
					$allUsers[$user['id']] = $user;
				}
			}

			if (count($response) < $perPage) {
				break;
			}

			$page++;
		}

		return array_values($allUsers);
	}

	private function zammadApiGet($url = '') {
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'Accept: application/json',
			'Authorization: Token token=' . zammad_token,
		));

		$responseBody = curl_exec($ch);
		$statusCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);

		if ($responseBody === false || $statusCode < 200 || $statusCode >= 300) {
			return array();
		}

		$decoded = json_decode($responseBody, true);

		return is_array($decoded) ? $decoded : array();
	}

	private function getAgentGroupIds($agent = null) {
		if (!is_array($agent)) {
			return array();
		}

		if (isset($agent['group_ids']) && is_array($agent['group_ids'])) {
			return array_map('intval', $agent['group_ids']);
		}

		if (!isset($agent['groups']) || !is_array($agent['groups'])) {
			return array();
		}

		$groupIdsByName = array_flip($this->groups());
		$groupIds = array();

		foreach (array_keys($agent['groups']) as $groupName) {
			if (isset($groupIdsByName[$groupName])) {
				$groupIds[] = (int) $groupIdsByName[$groupName];
			}
		}

		return $groupIds;
	}

	private function getPrimaryGroupId($agent = null) {
		$groupIds = $this->getAgentGroupIds($agent);

		if (empty($groupIds)) {
			return null;
		}

		return (int) reset($groupIds);
	}

	private function getAgentDisplayName($agent = null) {
		if (!is_array($agent)) {
			return '';
		}

		$displayName = trim(($agent['firstname'] ?? '') . " " . ($agent['lastname'] ?? ''));

		if ($displayName === '') {
			$displayName = (string) ($agent['login'] ?? ('Agent ' . ($agent['id'] ?? '')));
		}

		return $displayName;
	}

	private function shouldIncludeAgent($agent = null) {
		if (!is_array($agent) || empty($agent['id']) || empty($agent['active'])) {
			return false;
		}

		return $this->hasAgentRole($agent);
	}

	private function hasAgentRole($agent = null) {
		if (!is_array($agent) || !isset($agent['role_ids']) || !is_array($agent['role_ids'])) {
			return false;
		}

		return in_array(2, array_map('intval', $agent['role_ids']), true);
	}

	private function sortAgentsByDisplayName($agentA = null, $agentB = null) {
		return strcasecmp($this->getAgentDisplayName($agentA), $this->getAgentDisplayName($agentB));
	}

}
?>
