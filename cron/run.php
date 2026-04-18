<?php
$_SERVER['DOCUMENT_ROOT'] = "/var/www/tasks/html";
include_once($_SERVER['DOCUMENT_ROOT'] . "/inc/autoload.php");

$tickets = new tickets();
$tickets->runScheduledTickets();
?>
