<?php
session_start();

require_once($_SERVER['DOCUMENT_ROOT'] . '/inc/config.php');

if (debug == true) {
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(1);
} else {
	ini_set('display_errors', 0);
	ini_set('display_startup_errors', 0);
	error_reporting(0);
}

require ($_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php');

use LdapRecord\Connection;

// Create a new connection:
$ldap_connection = new Connection([
    'hosts' => [LDAP_SERVER],
    'port' => LDAP_PORT,
    'base_dn' => LDAP_BASE_DN,
    'username' => LDAP_BIND_DN,
		'password' => LDAP_BIND_PASSWORD,
		'use_tls' => LDAP_STARTTLS,
]);
try {
    $ldap_connection->connect();
} catch (\LdapRecord\Auth\BindException $e) {
    $error = $e->getDetailedError();

    echo $error->getErrorCode();
    echo $error->getErrorMessage();
    echo $error->getDiagnosticMessage();
}

//use Zendesk\API\HttpClient as ZendeskAPI;
use ZammadAPIClient\Client;
use ZammadAPIClient\ResourceType;

$zammad_api_client_config = [
    'url' => zammad_url,
    'debug'         => zammad_debug,
    'http_token' => zammad_token
];

$client = new Client($zammad_api_client_config);

require_once($_SERVER['DOCUMENT_ROOT'] . '/inc/globalfunctions.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/inc/database.php');
$database = new PgSql();

require_once($_SERVER['DOCUMENT_ROOT'] . '/inc/tickets.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/inc/agents.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/inc/logs.php');

?>
