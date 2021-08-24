<?php
session_start();

require_once('./inc/config.php');

require_once('./vendor/autoload.php');

require_once('./inc/database.php');
require_once('./inc/jobs.php');
require_once('./inc/agents.php');
require_once('./inc/logs.php');

$jobs = new jobs();
$jobs_weekly = $jobs->jobs_weekly();

foreach($jobs_weekly AS $job) {
	if ($job->status == "Enabled") {
		$job->create_zendesk_ticket();
	}
	else {
		$logRecord = new logs();
		$logRecord->description = "Didn't create job: " . $job->subject . " (" . $job->uid . ") because it was disabled.";
		$logRecord->type = "info";
		$logRecord->log_record();
	}
}
?>
