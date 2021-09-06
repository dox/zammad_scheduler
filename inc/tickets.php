<?php
class tickets {
	protected static $table_name = "tickets";

	public static function getTicket($uid = null) {
		global $database;

		$sql  = "SELECT * FROM " . self::$table_name . " ";
		$sql .= "WHERE uid = '" . $uid . "';";

		$ticket = $database->query($sql)->fetchArray();

		return $ticket;
	}

	public function ticketDisplay($uid = null) {
		$ticket = $this->getTicket($uid);

		$agentsClass = new agents();
		$agent = $agentsClass->getAgent($ticket['zammad_agent']);

		if ($ticket['status'] == "Enabled") {
			$class = "";
			$subjectTitle = $ticket['subject'];
		} else {
			$class = "list-group-item-dark";
			$subjectTitle = $ticket['subject'] . " [DISABLED]";
		}

		$output  = "<a href=\"index.php?n=ticket_edit&job=" . $ticket['uid'] . "\" class=\"list-group-item list-group-item-action " . $class . "\">";
		$output .= "<div class=\"d-flex w-100 justify-content-between\">";
		$output .= "<h5 class=\"mb-1\">" . $subjectTitle . "</h5>";
		$output .= "</div>";
		$output .= "<p class=\"mb-1\">" . $ticket['body'] . "</p>";
		$output .= "<span class=\"badge bg-primary rounded-pill float-end\">" . $ticket['type'] . "</span>" . "<small>Assign To: " . $agent['firstname'] . " " . $agent['lastname'] . "</small>";

		if ($ticket['frequency'] == "Yearly") {
			$output .= " on <small>" . strtoupper($ticket['frequency2']) . "</small>";
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

		$tickets = $database->query($sql)->fetchAll();

		return $tickets;
	}

	public static function getTicketsByGroup($groupID = null, $filter = "all") {
		global $database;

		$sql  = "SELECT * FROM " . self::$table_name;
		$sql .= " WHERE zammad_group = '" . $groupID . "'";
		if ($filter != "all") {
			$sql .= " AND frequency = '" . $filter . "'";
		}

		$tickets = $database->query($sql)->fetchAll();

		return $tickets;
	}
	
	public function ticketObjectGetFromZammad($ticketID = null) {
		global $client;
		
		$ticketObject = $client->resource(ZammadAPIClient\ResourceType::TICKET )->get($ticketID);
		exitOnError($ticketObject);
		
		return $ticketObject;
	}
	
	public function ticketValuesGetFromZammad($ticketID = null) {		
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

		$ticket = $client->resource(ZammadAPIClient\ResourceType::TICKET );
		$ticket->setValues($ticket_data);
		$ticket->save();
		exitOnError($ticket);

		$ticket_id = $ticket->getID(); // same as getValue('id')

		$logRecord = new logs();
		$logRecord->description = "API submission: " . $ticket_data['title'];
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
		$sql .= " LIMIT 1";

		// check if the database entry was successful (by attempting it)
		if ($database->query($sql)) {
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
		$sql .= "LIMIT 1";

		$delete = $database->query($sql);

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
			$sqlUpdate[] = $updateItem ." = '" . $value . "' ";
		}

		$sql  = "INSERT INTO " . self::$table_name;
		$sql .= " SET " . implode(", ", $sqlUpdate);

		$create = $database->query($sql);

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
