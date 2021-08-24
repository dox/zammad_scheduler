<?php
use Zendesk\API\HttpClient as ZendeskAPI;
use ZammadAPIClient\Client;
use ZammadAPIClient\ResourceType;

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
	
	public function ticketCreateInZammad($ticket = null) {
		global $client;
		// = new Client($zammad_api_client_config);
		printArray($ticket);
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
		
		$ticket = $client->resource( ResourceType::TICKET );
		$ticket->setValues($ticket_data);
		$ticket->save();
		exitOnError($ticket);
			
		$ticket_id = $ticket->getID(); // same as getValue('id')
		
		return true;
	}







public static function jobs_assigned($zendesk_id = null) {
	global $database;

	$sql  = "SELECT * FROM " . self::$table_name . " ";
	$sql .= "WHERE assign_to = '" . $zendesk_id . "';";

	$results = self::find_by_sql($sql);

	return $results;
	//return !empty($results) ? array_shift($results) : false;
}

public static function jobs_logged($zendesk_id = null) {
	global $database;

	$sql  = "SELECT * FROM " . self::$table_name . " ";
	$sql .= "WHERE logged_by = '" . $zendesk_id . "';";

	$results = self::find_by_sql($sql);

	return $results;
	//return !empty($results) ? array_shift($results) : false;
}

public static function jobs_involved_with($zendesk_id = null) {
	global $database;

	$sql  = "SELECT * FROM " . self::$table_name . " ";
	$sql .= "WHERE assign_to = '" . $zendesk_id . "' ";
	$sql .= "OR logged_by = '" . $zendesk_id . "';";

	$results = self::find_by_sql($sql);

	return $results;
}



public function job_create() {
	global $database;

	$sql  = "INSERT INTO " . self::$table_name . " (";
	$sql .= "subject, body, type, priority, tags, frequency, frequency2, assign_to, cc, status, logged_by";
	$sql .= ") VALUES ('";
	$sql .= $database->escape_value($this->subject) . "', '";
	$sql .= $database->escape_value($this->body) . "', '";
	$sql .= $database->escape_value($this->type) . "', '";
	$sql .= $database->escape_value($this->priority) . "', '";
	$sql .= $database->escape_value($this->tags) . "', '";
	$sql .= $database->escape_value($this->frequency) . "', '";
	$sql .= $database->escape_value($this->frequency2) . "', '";
	$sql .= $database->escape_value($this->assign_to) . "', '";
	$sql .= $database->escape_value($this->cc) . "', '";
	$sql .= $database->escape_value($this->status) . "', '";
	$sql .= $database->escape_value($this->logged_by) . "')";

	// check if the database entry was successful (by attempting it)
	if ($database->query($sql)) {
		$logRecord = new logs();
		$logRecord->description = "New " . $this->frequency . " task created: '" . $this->subject . "'";
		$logRecord->type = "admin";
		$logRecord->log_record();

		return true;
	} else {
		$logRecord = new logs();
		$logRecord->description = "Error creating task: " . $this->subject . "'";
		$logRecord->type = "error";
		$logRecord->log_record();

		return false;
	}
}

public function job_delete() {
	global $database;

	$sql  = "DELETE FROM " . self::$table_name . " ";
	$sql .= "WHERE uid = '" . $this->uid . "' ";
	$sql .= "LIMIT 1;";

	// check if the database entry was successful (by attempting it)
	if ($database->query($sql)) {
		$logRecord = new logs();
		$logRecord->description = "Deleting task (" . $this->uid . ")";
		$logRecord->type = "admin";
		$logRecord->log_record();

		return true;
	} else {
		$logRecord = new logs();
		$logRecord->description = "Error deleting task (" . $this->uid . ")";
		$logRecord->type = "error";
		$logRecord->log_record();

		return false;
	}
}

public function job_update() {
	global $database;

	$sql  = "UPDATE " . self::$table_name . " ";
	$sql .= "SET subject = '" . $database->escape_value($this->subject) . "', ";
	$sql .= "body = '" . $database->escape_value($this->body) . "', ";
	$sql .= "type = '" . $database->escape_value($this->type) . "', ";
	$sql .= "priority = '" . $database->escape_value($this->priority) . "', ";
	$sql .= "tags = '" . $database->escape_value($this->tags) . "', ";
	$sql .= "frequency = '" . $database->escape_value($this->frequency) . "', ";
	$sql .= "frequency2 = '" . $database->escape_value($this->frequency2) . "', ";
	$sql .= "logged_by = '" . $database->escape_value($this->logged_by) . "', ";
	$sql .= "cc = '" . $database->escape_value($this->cc) . "', ";
	$sql .= "assign_to = '" . $database->escape_value($this->assign_to) . "', ";
	$sql .= "status = '" . $database->escape_value($this->status) . "' ";
	$sql .= "WHERE uid = '" . $this->uid . "' ";
	$sql .= "LIMIT 1;";

	if ($database->query($sql)) {
		$logRecord = new logs();
		$logRecord->description = "Updating task (" . $this->uid . ")";
		$logRecord->type = "admin";
		$logRecord->log_record();

		return true;
	} else {
		$logRecord = new logs();
		$logRecord->description = "Error updating task (" . $this->uid . ")";
		$logRecord->type = "error";
		$logRecord->log_record();

		return false;
	}
}

public function tagsArray() {
	$tags = $this->tags;
	$tags = str_replace(" ", "", $tags); // remove spaces
	$tagsArray = explode(",", $this->tags);

	return $tagsArray;
}

public function create_zendesk_ticket() {
	$subdomain = zd_subdomain;
	$username  = zd_username;
	$token     = zd_token;

	$client = new ZendeskAPI($subdomain);
	$client->setAuth('basic', ['username' => $username, 'token' => $token]);

	if ($this->assign_to == 0) {
		$this->assign_to = null;
	}

	try {
		// Create a new ticket wi
		$newTicket = $client->tickets()->create(array(
			'type' => strtolower($this->type),
			'tags'  => array( implode(",", $this->tagsArray()) ),
			'subject'  => $this->subject,
			'comment'  => array(
				'body' => $this->body
			),
			'priority' => strtolower($this->priority),
			'assignee_id' => $this->assign_to,
			'requester_id' => $this->logged_by,
			'collaborators' => $this->cc,
		));

		$logRecord = new logs();
		$logRecord->description = "Successfully ran " . strtolower($this->frequency) . " task  '" . $this->subject . "' (" . $this->uid . ")";
		$logRecord->type = "cron";
		$logRecord->log_record();

		// Show result
		//echo "running complete";
		//echo "<pre>";
		//print_r($newTicket);
		//echo "</pre>";
		return true;
	} catch (\Zendesk\API\Exceptions\ApiResponseException $e) {
		$logRecord = new logs();
		$logRecord->description = "Error running " . $this->frequency . "task  '" . $this->subject . "' (" . $this->uid . ") " . $e->getMessage();
		$logRecord->type = "error";
		$logRecord->log_record();

		return $e->getMessage().'</br>';
	}
}

}
?>
