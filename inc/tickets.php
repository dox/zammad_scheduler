<?php
use ZammadAPIClient\Client;
use ZammadAPIClient\ResourceType;

class tickets {
	protected static $table_name = "tickets";

	public static function getTicket($uid = null) {
		global $database;

		$sql  = "SELECT * FROM " . self::$table_name . " ";
		$sql .= "WHERE uid = '" . $uid . "';";

		$ticket = $database->getRow($sql);

		return $ticket;
	}
	
	public function showTicketsTable($tickets = null) {
		//printArray($tickets);
		$output  = "<table class=\"table\">";
		$output .= "<thead>";
		$output .= "<tr>";
		$output .= "<th scope=\"col\">Frequency</th>";
		$output .= "<th scope=\"col\">Subject</th>";
		$output .= "<th scope=\"col\">Assign To</th>";
		$output .= "<th scope=\"col\"></th>";
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
		
		$class = "";
		if ($ticket->status == "Disabled") {
			$class = "table-secondary";
		}
		$output  = "<tr class=\"" . $class . "\">";
		$output .= "<th scope=\"row\">" . $ticket->frequency ."</th>";
		$output .= "<td>" . $ticket->subject . "</td>";
		$output .= "<td>" . $agent['firstname'] . "</td>";
		$output .= "<td><a href=\"index.php?n=ticket_edit&job=" . $ticket->uid . "\">Edit</a></td>";
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
			$sql .= " WHERE frequency = '" . $filter . "'";
		}

		$tickets = $database->getRows($sql);

		return $tickets;
	}

	public static function getTicketsByGroup($groupID = null, $filter = "all") {
		global $database;
	
		$sql  = "SELECT * FROM " . self::$table_name;
		$sql .= " WHERE zammad_group = '" . $groupID . "'";
		if ($filter != "all") {
			$sql .= " AND frequency = '" . $filter . "'";
		}
	
		$tickets = $database->getRows($sql);
	
		return $tickets;
	}
	
	public static function getTicketsByAgent($ownerID = null) {
		global $database;
	
		$sql  = "SELECT * FROM " . self::$table_name;
		$sql .= " WHERE zammad_agent = '" . $ownerID . "'";
	
		$tickets = $database->getRows($sql);
	
		return $tickets;
	}
	
	public static function getTicketsByCustomer($ownerID = null) {
		global $database;
	
		$sql  = "SELECT * FROM " . self::$table_name;
		$sql .= " WHERE zammad_customer = '" . $ownerID . "'";
	
		$tickets = $database->getRows($sql);
	
		return $tickets;
	}
	
	public static function getTicketsByAgentOrCustomer($ownerID = null) {
		global $database;
	
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
					$value = str_replace("'", "\'", $value);
					$sqlUpdate[] = $updateItem ." = '" . $value . "' ";
				}
			}
		}

		$sql .= " SET " . implode(", ", $sqlUpdate);
		$sql .= " WHERE uid = '" . $array['uid'] . "' ";

		// check if the database entry was successful (by attempting it)
		if ($database->exec($sql)) {
			$logRecord = new logs();
			$logRecord->description = "New " . $this->frequency . " task created: '" . $this->subject . "'";
			$logRecord->type = "admin";
			$logRecord->log_record();
		} else {
			$logRecord = new logs();
			$logRecord->description = "Error creating task: " . $this->subject . "'";
			$logRecord->type = "error";
			$logRecord->log_record();
		}

		return $update;
	  }

	  public function delete($ticketUID = null) {
		global $database;

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
			$value = str_replace("'", "\'", $value);
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
