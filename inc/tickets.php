<?php
use ZammadAPIClient\Client;
use ZammadAPIClient\ResourceType;

class tickets {
	protected static $table_name = "tickets";

	public static function getTicket($uid = null) {
		global $database;
		$uid = $database->escape($uid);

		$sql  = "SELECT * FROM " . self::$table_name . " ";
		$sql .= "WHERE uid = '" . $uid . "';";

		$ticket = $database->getRow($sql);

		return $ticket;
	}
	
	public function showTicketsTable($tickets = null) {
		$output  = "<table class=\"table align-middle ticket-table mb-0\">";
		$output .= "<thead>";
		$output .= "<tr>";
		$output .= "<th scope=\"col\">Frequency</th>";
		$output .= "<th scope=\"col\">Subject</th>";
		$output .= "<th scope=\"col\">Assign To</th>";
		$output .= "</tr>";
		$output .= "</thead>";
		$output .= "<tbody>";
		
		foreach ($tickets AS $ticket) {
			$output .= $this->showTicketRow($ticket);	
		}
		
		$output .= "</tbody>";
		$output .= "</table>";
		
		return $output;
	}
	
	private function showTicketRow($ticket = null) {
		$agentsClass = new agents();
		
		$agent = $agentsClass->getZammadAgent($ticket->zammad_agent);
		$agentName = is_array($agent) ? trim(($agent['firstname'] ?? '') . " " . ($agent['lastname'] ?? '')) : "";
		if ($agentName === "") {
			$agentName = "Agent " . $ticket->zammad_agent;
		}
		
		$class = "";
		if ($ticket->status == "Disabled") {
			$class = "table-secondary";
		}
		$output  = "<tr class=\"" . $class . "\">";
		$output .= "<th scope=\"row\"><span class=\"badge rounded-pill text-bg-light border\">" . htmlspecialchars($ticket->frequency, ENT_QUOTES) . "</span></th>";
		$output .= "<td>";
		$output .= "<div class=\"fw-semibold\"><a class=\"ticket-row-link\" href=\"index.php?n=ticket_edit&job=" . $ticket->uid . "\">" . htmlspecialchars($ticket->subject, ENT_QUOTES) . "</a></div>";
		if ($ticket->status == "Disabled") {
			$output .= "<div class=\"small text-muted\">Disabled</div>";
		}
		$output .= "</td>";
		$output .= "<td>" . htmlspecialchars($agentName, ENT_QUOTES) . "</td>";
		$output .= "</tr>";
		
		return $output;
	}
	
	public function ticketDisplay($uid = null) {
		$ticket = $this->getTicket($uid);
		
		$agentsClass = new agents();
		$agent = $agentsClass->getZammadAgent($ticket->zammad_agent);
		
		if ($ticket->status == "Enabled") {
			$class = "";
			$subjectTitle = $ticket->subject;
		} else {
			$class = "list-group-item-dark";
			$subjectTitle = $ticket->subject . " [DISABLED]";
		}

		$output  = "<a href=\"index.php?n=ticket_edit&job=" . $ticket->uid . "\" class=\"list-group-item list-group-item-action " . $class . "\">";
		$output .= "<div class=\"d-flex w-100 justify-content-between\">";
		$output .= "<h5 class=\"mb-1\">" . $subjectTitle . "</h5>";
		$output .= "</div>";
		$output .= "<p class=\"mb-1\">" . $ticket->body . "</p>";
		$output .= "<span class=\"badge bg-primary rounded-pill float-end\">" . $ticket->type . "</span>" . "<small>Assign To: " . $agent['firstname'] . " " . $agent['lastname'] . "</small>";

		if ($ticket->frequency == "Yearly") {
			$output .= " on <small>" . strtoupper($ticket->frequency2) . "</small>";
		}
		$output .= "</a>";

		return $output;
	}

	public static function getTickets($filter = "all") {
		global $database;

		$sql  = "SELECT * FROM " . self::$table_name;

		if ($filter != "all") {
			$filter = $database->escape($filter);
			$sql .= " WHERE frequency = '" . $filter . "'";
		}

		$tickets = $database->getRows($sql);

		return $tickets;
	}

	public function getDueTicketsForDate($date = null) {
		if ($date === null) {
			$date = new DateTimeImmutable('today');
		} elseif (!$date instanceof DateTimeInterface) {
			$date = new DateTimeImmutable((string) $date);
		}

		$allTickets = $this->getTickets();
		$dueTickets = [];

		foreach ($allTickets as $ticket) {
			if ($this->normalizeTicketValue($ticket->status ?? '') !== 'Enabled') {
				continue;
			}

			if ($this->isTicketDueOnDate($ticket, $date)) {
				$dueTickets[] = $ticket;
			}
		}

		return $dueTickets;
	}

	public function isTicketDueOnDate($ticket = null, DateTimeInterface $date = null) {
		if ($ticket === null) {
			return false;
		}

		if ($date === null) {
			$date = new DateTimeImmutable('today');
		}

		switch ($this->normalizeTicketValue($ticket->frequency ?? '')) {
			case 'Daily':
				return true;

			case 'Weekly':
				return $date->format('N') === '1';

			case 'Monthly':
				return $date->format('j') === '1';

			case 'Yearly':
				return $this->isYearlyTicketDueOnDate($ticket, $date);

			default:
				return false;
		}
	}

	public function getNextDueDate($ticket = null, DateTimeInterface $date = null) {
		if ($ticket === null || $this->normalizeTicketValue($ticket->status ?? '') !== 'Enabled') {
			return null;
		}

		if ($date === null) {
			$date = new DateTimeImmutable('today');
		}

		$date = new DateTimeImmutable($date->format('Y-m-d'));

		switch ($this->normalizeTicketValue($ticket->frequency ?? '')) {
			case 'Daily':
				return $date;

			case 'Weekly':
				if ($date->format('N') === '1') {
					return $date;
				}

				return $date->modify('next monday');

			case 'Monthly':
				if ($date->format('j') === '1') {
					return $date;
				}

				return $date->modify('first day of next month');

			case 'Yearly':
				return $this->getNextYearlyDueDate($ticket, $date);

			default:
				return null;
		}
	}

	public function buildTicketPayload($ticket = null) {
		if ($ticket === null) {
			return null;
		}

		return [
			'uid'         => $ticket->uid,
			'group_id'    => $ticket->zammad_group,
			'owner_id'    => $ticket->zammad_agent,
			'priority_id' => $ticket->zammad_priority,
			'state_id'    => 1,
			'title'       => $ticket->subject,
			'customer_id' => $ticket->zammad_customer,
			'article'     => [
				'subject' => $ticket->subject,
				'body'    => $ticket->body,
			],
		];
	}

	public function runScheduledTickets($date = null) {
		$dueTickets = $this->getDueTicketsForDate($date);
		$date = $date instanceof DateTimeInterface ? $date : new DateTimeImmutable($date === null ? 'today' : (string) $date);

		$this->logScheduledTicketSummary($dueTickets, $date);

		foreach ($dueTickets as $ticket) {
			$this->ticketCreateInZammad($this->buildTicketPayload($ticket));
		}

		return count($dueTickets);
	}

	private function normalizeTicketValue($value = null) {
		return ucfirst(strtolower(trim((string) $value)));
	}

	private function getNextYearlyDueDate($ticket = null, DateTimeInterface $date = null) {
		if ($ticket === null || empty($ticket->frequency2)) {
			return null;
		}

		$date = new DateTimeImmutable($date->format('Y-m-d'));
		$datesToRun = array_filter(array_map('trim', explode(',', strtoupper((string) $ticket->frequency2))));
		$nextDueDate = null;

		foreach ($datesToRun as $dateToRun) {
			$dateParts = $this->parseYearlyDateToken($dateToRun);
			if ($dateParts === null) {
				continue;
			}

			for ($year = (int) $date->format('Y'); $year <= (int) $date->format('Y') + 1; $year++) {
				if (!checkdate($dateParts['month'], $dateParts['day'], $year)) {
					continue;
				}

				$candidate = $date->setDate($year, $dateParts['month'], $dateParts['day']);
				if ($candidate < $date) {
					continue;
				}

				if ($nextDueDate === null || $candidate < $nextDueDate) {
					$nextDueDate = $candidate;
				}
			}
		}

		return $nextDueDate;
	}

	private function parseYearlyDateToken($dateToken = null) {
		$monthNumbers = [
			'JAN' => 1,
			'FEB' => 2,
			'MAR' => 3,
			'APR' => 4,
			'MAY' => 5,
			'JUN' => 6,
			'JUL' => 7,
			'AUG' => 8,
			'SEP' => 9,
			'OCT' => 10,
			'NOV' => 11,
			'DEC' => 12,
		];

		if (!preg_match('/^([A-Z]{3})-(\d{1,2})$/', trim((string) $dateToken), $matches)) {
			return null;
		}

		if (!isset($monthNumbers[$matches[1]])) {
			return null;
		}

		return [
			'month' => $monthNumbers[$matches[1]],
			'day' => (int) $matches[2],
		];
	}

	private function logScheduledTicketSummary($dueTickets = [], DateTimeInterface $date = null) {
		$counts = [
			'Daily' => 0,
			'Weekly' => 0,
			'Monthly' => 0,
			'Yearly' => 0,
		];

		foreach ($dueTickets as $ticket) {
			$frequency = $this->normalizeTicketValue($ticket->frequency ?? '');
			if (!array_key_exists($frequency, $counts)) {
				continue;
			}

			$counts[$frequency]++;
		}

		$logRecord = new logs();
		$logRecord->description = "Cron evaluated " . $date->format('Y-m-d') . ": " . count($dueTickets) . " due tickets (Daily: " . $counts['Daily'] . ", Weekly: " . $counts['Weekly'] . ", Monthly: " . $counts['Monthly'] . ", Yearly: " . $counts['Yearly'] . ")";
		$logRecord->type = "cron";
		$logRecord->log_record();
	}

	private function isYearlyTicketDueOnDate($ticket = null, DateTimeInterface $date = null) {
		if ($ticket === null || empty($ticket->frequency2)) {
			return false;
		}

		if ($date === null) {
			$date = new DateTimeImmutable('today');
		}

		$datesToRun = array_filter(array_map('trim', explode(',', strtoupper((string) $ticket->frequency2))));
		$todayToken = strtoupper($date->format('M-d'));

		return in_array($todayToken, $datesToRun, true);
	}
	
	public static function getTicketsByGroup($groupID = null, $filter = "all") {
		global $database;
		$groupID = $database->escape($groupID);
	
		$sql  = "SELECT * FROM " . self::$table_name;
		$sql .= " WHERE zammad_group = '" . $groupID . "'";
		if ($filter != "all") {
			$filter = $database->escape($filter);
			$sql .= " AND frequency = '" . $filter . "'";
		}
	
		$tickets = $database->getRows($sql);
	
		return $tickets;
	}
	
	public static function getTicketsByAgent($ownerID = null) {
		global $database;
		$ownerID = $database->escape($ownerID);
	
		$sql  = "SELECT * FROM " . self::$table_name;
		$sql .= " WHERE zammad_agent = '" . $ownerID . "'";
	
		$tickets = $database->getRows($sql);
	
		return $tickets;
	}
	
	public static function getTicketsByCustomer($ownerID = null) {
		global $database;
		$ownerID = $database->escape($ownerID);
	
		$sql  = "SELECT * FROM " . self::$table_name;
		$sql .= " WHERE zammad_customer = '" . $ownerID . "'";
	
		$tickets = $database->getRows($sql);
	
		return $tickets;
	}
	
	public static function getTicketsByAgentOrCustomer($ownerID = null) {
		global $database;
		$ownerID = $database->escape($ownerID);
	
		$sql  = "SELECT * FROM " . self::$table_name;
		$sql .= " WHERE zammad_customer = '" . $ownerID . "'";
		$sql .= " OR zammad_agent = '" . $ownerID . "'";
	
		$tickets = $database->getRows($sql);
	
		return $tickets;
	}
	
	public function ticketObjectGetFromZammad($ticketID = null) {
		global $client;
		
		$ticketObject = $client->resource(ZammadAPIClient\ResourceType::TICKET )->get($ticketID);
		exitOnError($ticketObject);
		
		return $ticketObject;
	}
	
	public function ticketValuesGetFromZammad($ticketID = null) {	
		if (is_null($ticketID)) {
			return false;
		}	
		$ticketObject = $this->ticketObjectGetFromZammad($ticketID);
		$ticketValues = $ticketObject->getValues();
		
		return $ticketValues;
	}
	
	public function ticketArticlesGetFromZammad($ticketID = null) {		
		$ticketObject = $this->ticketObjectGetFromZammad($ticketID);
		$ticketArticles = $ticketObject->getTicketArticles();
		
		return $ticketArticles;
	}
	
	public function ticketCreateInZammad($ticket = null) {
		global $client;
	
		$ticket_data = [
			'group_id'    => $ticket['group_id'],
			'owner_id'    => $ticket['owner_id'],
			'priority_id' => $ticket['priority_id'],
			'state_id'    => 1,
			'title'       => $ticket['title'],
			'customer_id' => $ticket['customer_id'],
			'article'     => [
				'subject' => $ticket['article']['subject'],
				'body'    => $ticket['article']['body'],
			],
		];
	
		$zammad_ticket = $client->resource( ResourceType::TICKET );
		$zammad_ticket->setValues($ticket_data);
		$zammad_ticket->save();
		exitOnError($zammad_ticket);
		
		$zammad_ticket_id = $zammad_ticket->getID(); // same as getValue('id')
		
		$ticket_update['uid'] = $ticket['uid'];
		$ticket_update['last_id'] = $zammad_ticket_id;
		$this->update($ticket_update);
	
		
	
		$logRecord = new logs();
		$logRecord->description = "API submission: " . $ticket['title'] . " created ticket ID: " . $zammad_ticket_id;
		$logRecord->type = "cron";
		$logRecord->log_record();
	
		return true;
	}

	public function update($array = null) {
		global $database;

		$sql  = "UPDATE " . self::$table_name;

		foreach ($array AS $updateItem => $value) {
			if ($updateItem != 'uid') {
				if ($value == '') {
					$sqlUpdate[] = $updateItem ." = NULL ";
				} else {
					$value = $database->escape($value);
					$sqlUpdate[] = $updateItem ." = '" . $value . "' ";
				}
			}
		}

		$sql .= " SET " . implode(", ", $sqlUpdate);
		$sql .= " WHERE uid = '" . $database->escape($array['uid']) . "' ";

		// check if the database entry was successful (by attempting it)
		$update = $database->exec($sql);
		if ($update) {
			$logRecord = new logs();
			$logRecord->description = "Task updated: " . $array['uid'];
			$logRecord->type = "admin";
			$logRecord->log_record();
		} else {
			$logRecord = new logs();
			$logRecord->description = "Error updating task: " . $array['uid'];
			$logRecord->type = "error";
			$logRecord->log_record();
		}

		return $update;
	  }

	  public function delete($ticketUID = null) {
		global $database;
		$ticketUID = $database->escape($ticketUID);

		$sql  = "DELETE FROM " . self::$table_name . " ";
		$sql .= "WHERE uid = '" . $ticketUID . "' ";

		$delete = $database->exec($sql);

		// log this!
		$logRecord = new logs();
		$logRecord->description = "Task deleted: UID " . $ticketUID;
		$logRecord->type = "error";
		$logRecord->log_record();
	}

	public function create($array = null) {
		global $database;

		foreach ($array AS $updateItem => $value) {
			$value = $database->escape($value);
			$sqlUpdate[$updateItem] = "'" . $value . "'";
		}

		$sql  = "INSERT INTO " . self::$table_name;
		$sql .= " (" . implode(", ", array_keys($sqlUpdate)) . ") ";
		$sql .= "VALUES (" . implode(", ", $sqlUpdate) . ")";
		
		$create = $database->exec($sql);

		// log this!
		$logRecord = new logs();
		$logRecord->description = "Task created: " . $array['subject'];
		$logRecord->type = "admin";
		$logRecord->log_record();

		return $create;
	}










public function tagsArray() {
	$tags = $this->tags;
	$tags = str_replace(" ", "", $tags); // remove spaces
	$tagsArray = explode(",", $this->tags);

	return $tagsArray;
}

}
?>
