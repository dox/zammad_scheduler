<?php
$appRoot = dirname(__DIR__);
$_SERVER['DOCUMENT_ROOT'] = $appRoot;
chdir($appRoot);

include_once($_SERVER['DOCUMENT_ROOT'] . "/inc/autoload.php");

$tickets = new tickets();
$tickets->runScheduledTickets();
?>
