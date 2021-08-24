<?php
class logs {

protected static $table_name = "logs";

public $uid;
public $date_added;
public $description;
public $type;

private static function instantiate($record) {
	$object = new self;

	foreach ($record as $attribute=>$value) {
		if ($object->has_attribute($attribute)) {
			$object->$attribute = $value;
		}
	}

	return $object;
}

private function has_attribute($attribute) {
	// get_object_vars returns as associative array with all attributes
	// (incl. private ones!) as the keys and their current values as the value
	$object_vars = get_object_vars($this) ;

	// we don't care about the value, we just want to know if the key exists
	// will return true or false
	return array_key_exists($attribute, $object_vars);
}



// ****** //

public static function find_by_sql($sql="") {
	global $database;

	$result_set = $database->query($sql);
	$object_array = array();

	while ($row = $database->fetch_array($result_set)) {
		global $database;
		$object_array[] = self::instantiate($row);
	}

	return $object_array;
}


public static function member($uid = null) {
	global $database;

	$sql  = "SELECT * FROM " . self::$table_name . " ";
	$sql .= "WHERE uid = '" . $uid . "';";

	$results = self::find_by_sql($sql);

	//return $results;
	return !empty($results) ? array_shift($results) : false;
}

public static function find_all() {
	global $database;

	$sql  = "SELECT * FROM " . self::$table_name . " ";
	$sql .= "ORDER BY date_added DESC";

	$results = self::find_by_sql($sql);

	return $results;
	//return !empty($results) ? array_shift($results) : false;
}

public function log_record() {
	global $database;

	$sql  = "INSERT INTO " . self::$table_name . " (";
	$sql .= "description, type";
	$sql .= ") VALUES ('";
	$sql .= $database->escape_value(addslashes($this->description)) . "', '";
	$sql .= $database->escape_value(strtolower($this->type)) . "')";

	// check if the database entry was successful (by attempting it)
	if ($database->query($sql)) {
		return true;
	} else {
		return false;
	}
}

public function display_log() {
	$successTypes = array("logon_success");
	$primaryTypes = array("admin");
	$warningTypes = array("logon_fail");
	$dangerTypes = array("error");
	$infoTypes = array("cron", "info");

	if (in_array($this->type, $successTypes)) {
		$typeClass = "bg-success";
		$alertClass = "table-success";
	} elseif (in_array($this->type, $primaryTypes)) {
		$typeClass = "bg-primary";
		$alertClass = "table-primary";
	} elseif (in_array($this->type, $warningTypes)) {
		$typeClass = "bg-warning";
		$alertClass = "table-warning";
	} elseif (in_array($this->type, $dangerTypes)) {
		$typeClass = "bg-danger";
		$alertClass = "table-danger";
	} elseif (in_array($this->type, $infoTypes)) {
		$typeClass = "bg-info";
		$alertClass = "table-info";
	} else {
		$typeClass = "bg-dark";
		$alertClass = "table-dark";
	}

	$output  = "<tr class=\"" . $alertClass . "\">";
	$output .= "<td>" . date('Y-m-d H:i:s',strtotime($this->date_added)) . "</td>";
	$output .= "<td>" . $this->description . "<span class=\"badge rounded-pill float-end " . $typeClass  . "\">" . $this->type . "</span></td>";
	$output .= "</tr>";

	return $output;
}



public function delete_old_logs() {
	global $database;

	if (defined("log_retention")) {
		$logAge = log_retention;
	} else {
		$logAge = "180";
	}

	$sql  = "DELETE FROM " . self::$table_name . " ";
	$sql .= "WHERE DATEDIFF(NOW(), date_added) > " . $logAge;

	// check if the database entry was successful (by attempting it)
	if ($database->query($sql)) {
		return true;
	} else {
		return false;
	}
}

}
?>
