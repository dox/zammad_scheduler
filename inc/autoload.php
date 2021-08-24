<?php
session_start();

set_include_path('/var/www/tasks.seh.ox.ac.uk/public_html/');

require_once('inc/config.php');

if (debug == true) {
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(1);
} else {
	ini_set('display_errors', 0);
	ini_set('display_startup_errors', 0);
	error_reporting(0);
}

require ('vendor/autoload.php');

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

$zammad_api_client_config = [
    'url' => zammad_url,

    // with username and password
    'username' => zammad_username,
    'password' => zammad_password,
    'debug'         => zammad_debug
    // or with HTTP token:
    // 'http_token' => '...',

    // or with OAuth2 token:
    //'oauth2_token' => '...',
];



require_once('inc/globalfunctions.php');
require_once('inc/database.php');
$database = new db(db_host, db_username, db_password, db_database);

require_once('inc/tickets.php');
require_once('inc/agents.php');
require_once('inc/logs.php');

?>
