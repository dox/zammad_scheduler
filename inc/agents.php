<?php
$email_address = "andrew.breakspear@seh.ox.ac.uk";

use ZammadAPIClient\Client;
use ZammadAPIClient\ResourceType;

class agents {
	private const AGENT_CACHE_VERSION = 6;

	public function getAgents() {
		return $this->getZammadAgents();
	}

	public function getZammadAgents() {
		$this->invalidateAgentCacheIfNeeded();

		$hasFullAgentCache = !empty($_SESSION['zammad_agents_loaded_all']);

		if (
			!$hasFullAgentCache ||
			!isset($_SESSION['zammad_agents']) ||
			!is_array($_SESSION['zammad_agents'])
		) {
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
			$_SESSION['zammad_agents_loaded_all'] = true;
		}

		return $_SESSION['zammad_agents'];
	}
	
	public function getZammadAgent($id = null) {
		$this->invalidateAgentCacheIfNeeded();

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

	public function getAgentsGroupedByVisibleGroups() {
		$visibleGroups = $this->groups();
		$groupedAgents = array();
		$otherAgents = array();

		foreach ($visibleGroups as $groupId => $groupName) {
			$groupedAgents[$groupName] = array();
		}

		foreach ($this->getZammadAgents() as $agent) {
			$agentGroupIds = $this->getAgentGroupIds($agent);
			$matchedVisibleGroup = false;

			foreach ($agentGroupIds as $groupId) {
				$groupId = (int) $groupId;

				if (!isset($visibleGroups[$groupId])) {
					continue;
				}

				$groupedAgents[$visibleGroups[$groupId]][$agent['id']] = $agent;
				$matchedVisibleGroup = true;
			}

			if (!$matchedVisibleGroup) {
				$otherAgents[$agent['id']] = $agent;
			}
		}

		$groupedAgents = array_filter($groupedAgents, function ($agents) {
			return !empty($agents);
		});

		if (!empty($otherAgents)) {
			$groupedAgents['Other Agents'] = $otherAgents;
		}

		return $groupedAgents;
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

	private function invalidateAgentCacheIfNeeded() {
		$cacheVersion = (int) ($_SESSION['zammad_agents_cache_version'] ?? 0);

		if ($cacheVersion === self::AGENT_CACHE_VERSION) {
			return;
		}

		unset(
			$_SESSION['zammad_agents'],
			$_SESSION['zammad_agents_loaded_all'],
			$_SESSION['zammad_roles'],
			$_SESSION['zammad_group_user_ids'],
			$_SESSION['zammad_group_memberships']
		);
		$_SESSION['zammad_agents_cache_version'] = self::AGENT_CACHE_VERSION;
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

		return $this->enrichAgentGroups($compact);
	}

	private function enrichAgentGroups($agent = null) {
		if (!is_array($agent) || empty($agent['id'])) {
			return $agent;
		}

		$groupMemberships = $this->fetchGroupMembershipsByUserId();
		$derivedMembership = $groupMemberships[(int) $agent['id']] ?? null;

		if (
			(empty($agent['group_ids']) || !is_array($agent['group_ids'])) &&
			is_array($derivedMembership) &&
			isset($derivedMembership['group_ids']) &&
			is_array($derivedMembership['group_ids'])
		) {
			$agent['group_ids'] = $derivedMembership['group_ids'];
		}

		if (
			(empty($agent['groups']) || !is_array($agent['groups'])) &&
			is_array($derivedMembership) &&
			isset($derivedMembership['groups']) &&
			is_array($derivedMembership['groups'])
		) {
			$agent['groups'] = $derivedMembership['groups'];
		}

		if ((empty($agent['groups']) || !is_array($agent['groups'])) && !empty($agent['group_ids'])) {
			$groupNames = $this->groups();
			$agent['groups'] = array();

			foreach ($agent['group_ids'] as $groupId) {
				$groupId = (int) $groupId;
				if (isset($groupNames[$groupId])) {
					$agent['groups'][$groupNames[$groupId]] = true;
				}
			}
		}

		return $agent;
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

	private function fetchAllZammadRoles() {
		if (isset($_SESSION['zammad_roles']) && is_array($_SESSION['zammad_roles'])) {
			return $_SESSION['zammad_roles'];
		}

		$roles = $this->fetchAllZammadRolesViaHttp();
		$rolesById = array();

		foreach ($roles as $roleObject) {
			$role = $this->normaliseAgent($roleObject);

			if (!is_array($role) || empty($role['id'])) {
				continue;
			}

			$rolesById[(int) $role['id']] = $role;
		}

		$_SESSION['zammad_roles'] = $rolesById;

		return $rolesById;
	}

	private function fetchAgentUserIdsFromGroups() {
		if (isset($_SESSION['zammad_group_user_ids']) && is_array($_SESSION['zammad_group_user_ids'])) {
			return $_SESSION['zammad_group_user_ids'];
		}

		$groups = $this->fetchAllZammadGroups();
		$userIds = array();

		foreach ($groups as $groupObject) {
			$group = $this->normaliseAgent($groupObject);

			if (!is_array($group) || !isset($group['user_ids']) || !is_array($group['user_ids'])) {
				continue;
			}

			foreach ($group['user_ids'] as $userId) {
				$userIds[(int) $userId] = true;
			}
		}

		$_SESSION['zammad_group_user_ids'] = $userIds;

		return $userIds;
	}

	private function fetchGroupMembershipsByUserId() {
		if (isset($_SESSION['zammad_group_memberships']) && is_array($_SESSION['zammad_group_memberships'])) {
			return $_SESSION['zammad_group_memberships'];
		}

		$groups = $this->fetchAllZammadGroups();
		$memberships = array();

		foreach ($groups as $groupObject) {
			$group = $this->normaliseAgent($groupObject);

			if (
				!is_array($group) ||
				empty($group['id']) ||
				empty($group['name']) ||
				!isset($group['user_ids']) ||
				!is_array($group['user_ids'])
			) {
				continue;
			}

			$groupId = (int) $group['id'];
			$groupName = (string) $group['name'];

			foreach ($group['user_ids'] as $userId) {
				$userId = (int) $userId;

				if (!isset($memberships[$userId])) {
					$memberships[$userId] = array(
						'group_ids' => array(),
						'groups' => array(),
					);
				}

				$memberships[$userId]['group_ids'][$groupId] = $groupId;
				$memberships[$userId]['groups'][$groupName] = true;
			}
		}

		foreach ($memberships as $userId => $membership) {
			$memberships[$userId]['group_ids'] = array_values($membership['group_ids']);
			ksort($memberships[$userId]['groups'], SORT_NATURAL | SORT_FLAG_CASE);
		}

		$_SESSION['zammad_group_memberships'] = $memberships;

		return $memberships;
	}

	private function fetchAllZammadUsersViaHttp() {
		if (!function_exists('curl_init')) {
			return array();
		}

		$allUsers = array();
		$page = 1;
		$perPage = 100;
		$maxPages = 100;

		while ($page <= $maxPages) {
			$url = rtrim(zammad_url, '/') . '/api/v1/users?page=' . $page . '&per_page=' . $perPage;
			$response = $this->zammadApiGet($url);

			if (!is_array($response) || empty($response)) {
				break;
			}

			$userCountBeforePage = count($allUsers);

			foreach ($response as $user) {
				if (is_array($user) && isset($user['id'])) {
					$allUsers[$user['id']] = $user;
				}
			}

			// Some Zammad setups cap page size server-side, so rely on new IDs appearing
			// rather than assuming "fewer than requested" means we reached the end.
			if (count($allUsers) === $userCountBeforePage) {
				break;
			}

			if (count($response) < $perPage) {
				break;
			}

			$page++;
		}

		return array_values($allUsers);
	}

	private function fetchAllZammadRolesViaHttp() {
		if (!function_exists('curl_init')) {
			return array();
		}

		$url = rtrim(zammad_url, '/') . '/api/v1/roles';
		$response = $this->zammadApiGet($url);

		return is_array($response) ? $response : array();
	}

	private function fetchAllZammadGroups() {
		$groups = $this->fetchAllZammadGroupsViaHttp();

		if (is_array($groups) && !empty($groups)) {
			return $groups;
		}

		global $client;

		try {
			$groups = $client->resource(ResourceType::GROUP)->all();
			if (is_array($groups)) {
				return $groups;
			}
		} catch (\Throwable $e) {
			return array();
		}

		return array();
	}

	private function fetchAllZammadGroupsViaHttp() {
		if (!function_exists('curl_init')) {
			return array();
		}

		$url = rtrim(zammad_url, '/') . '/api/v1/groups';
		$response = $this->zammadApiGet($url);

		return is_array($response) ? $response : array();
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

		$groupIds = array();

		if (isset($agent['group_ids']) && is_array($agent['group_ids'])) {
			foreach ($agent['group_ids'] as $groupId) {
				$groupId = (int) $groupId;

				if ($groupId > 0) {
					$groupIds[$groupId] = $groupId;
				}
			}
		}

		if (isset($agent['groups']) && is_array($agent['groups'])) {
			$groupIdsByName = array_flip($this->groups());

			foreach (array_keys($agent['groups']) as $groupName) {
				if (isset($groupIdsByName[$groupName])) {
					$groupId = (int) $groupIdsByName[$groupName];
					$groupIds[$groupId] = $groupId;
				}
			}
		}

		return array_values($groupIds);
	}

	private function getPrimaryGroupId($agent = null) {
		$groupIds = $this->getAgentGroupIds($agent);
		$visibleGroups = $this->groups();

		if (empty($groupIds)) {
			return null;
		}

		foreach ($groupIds as $groupId) {
			$groupId = (int) $groupId;

			if (isset($visibleGroups[$groupId])) {
				return $groupId;
			}
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

		return $this->hasAgentRole($agent) || $this->isAgentInAnyGroup($agent);
	}

	private function hasAgentRole($agent = null) {
		if (!is_array($agent) || !isset($agent['role_ids']) || !is_array($agent['role_ids'])) {
			return !empty($this->getAgentGroupIds($agent));
		}

		$roleIds = array_map('intval', $agent['role_ids']);
		$rolesById = $this->fetchAllZammadRoles();

		if (!empty($rolesById)) {
			foreach ($roleIds as $roleId) {
				if (isset($rolesById[$roleId]) && $this->isAgentCapableRole($rolesById[$roleId])) {
					return true;
				}
			}
		}

		if (!empty($this->getAgentGroupIds($agent))) {
			return true;
		}

		return in_array(2, $roleIds, true);
	}

	private function isAgentInAnyGroup($agent = null) {
		if (!is_array($agent) || empty($agent['id'])) {
			return false;
		}

		if (!empty($this->getAgentGroupIds($agent))) {
			return true;
		}

		$groupUserIds = $this->fetchAgentUserIdsFromGroups();

		return isset($groupUserIds[(int) $agent['id']]);
	}

	private function isAgentCapableRole($role = null) {
		if (!is_array($role)) {
			return false;
		}

		$roleName = strtolower(trim((string) ($role['name'] ?? '')));
		if ($roleName !== '' && (strpos($roleName, 'agent') !== false || strpos($roleName, 'admin') !== false)) {
			return true;
		}

		if (!isset($role['permission_names']) || !is_array($role['permission_names'])) {
			return false;
		}

		foreach ($role['permission_names'] as $permissionName) {
			$permissionName = strtolower((string) $permissionName);

			if ($permissionName === 'admin' || strpos($permissionName, 'admin.') === 0) {
				return true;
			}

			if ($permissionName === 'ticket.agent' || strpos($permissionName, 'ticket.agent.') === 0) {
				return true;
			}
		}

		return false;
	}

	private function sortAgentsByDisplayName($agentA = null, $agentB = null) {
		return strcasecmp($this->getAgentDisplayName($agentA), $this->getAgentDisplayName($agentB));
	}

}
?>
