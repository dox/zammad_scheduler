<?php
class logs {
	protected static $table_name = "logs";
	
	public $uid;
	public $date_added;
	public $description;
	public $type;
	
	public function getLogs() {
		global $database;
		
		$sql  = "SELECT * FROM " . self::$table_name;
		$sql .= " ORDER BY date_added DESC";
		
		$logs = $database->getRows($sql);
	
		return $logs;
	}
	
	public function displayLogs() {
		$output  = "<table class=\"table table-striped\">";
		$output .= "<thead>";
		$output .= "<tr>";
		$output .= "<th width=\"200px\">Date</th>";
		$output .= "<th>Description</th>";
		$output .= "</tr>";
		$output .= "</thead>";
		
		$successTypes = array("logon_success");
		$primaryTypes = array("admin");
		$warningTypes = array("logon_fail", "defunt_access");
		$dangerTypes = array("error");
		$infoTypes = array("cron", "info");
		
		$output .= "<tbody>";
		foreach ($this->getLogs() AS $log) {
			if (in_array($log->type, $successTypes)) {
				$typeClass = "bg-success";
				$alertClass = "table-success";
			} elseif (in_array($log->type, $primaryTypes)) {
				$typeClass = "bg-primary";
				$alertClass = "table-primary";
			} elseif (in_array($log->type, $warningTypes)) {
				$typeClass = "bg-warning";
				$alertClass = "table-warning";
			} elseif (in_array($log->type, $dangerTypes)) {
				$typeClass = "bg-danger";
				$alertClass = "table-danger";
			} elseif (in_array($log->type, $infoTypes)) {
				$typeClass = "bg-info";
				$alertClass = "table-info";
			} else {
				$typeClass = "bg-dark";
				$alertClass = "table-dark";
			}
			
			$output .= "<tr class=\"" . $alertClass . "\">";
			$output .= "<td>" . date('Y-m-d H:i:s',strtotime($log->date_added)) . "</td>";
			$output .= "<td>" . $log->description . "<span class=\"badge rounded-pill float-end " . $typeClass  . "\">" . $this->type . "</span></td>";
			$output .= "</tr>";
		}
		$output .= "</tbody>";
		$output .= "</table>";
	
		return $output;
	}
	
	public function purgeLogs() {
		global $database;
		
		if (defined("log_retention")) {
			$logAge = log_retention;
		} else {
			$logAge = "180";
		}
		
		$sql  = "DELETE FROM " . self::$table_name . " ";
		$sql .= "WHERE date_added < now() - interval '" . $logAge . " days'";
		
		$deleteLogs = $database->exec($sql);
	
		return true;
	}
	
	public function log_record() {
		global $database;
	
		$sql  = "INSERT INTO " . self::$table_name . " (";
		$sql .= "description, type";
		$sql .= ") VALUES ('" . $this->description . "', '" . $this->type . "')";
	
		// check if the database entry was successful (by attempting it)
		if ($database->exec($sql)) {
			return true;
		} else {
			return false;
		}
	}




public static function member($uid = null) {
	global $database;

	$sql  = "SELECT * FROM " . self::$table_name . " ";
	$sql .= "WHERE uid = '" . $uid . "';";

	$results = self::find_by_sql($sql);

	//return $results;
	return !empty($results) ? array_shift($results) : false;
}











}
?>
