<?php
include_once("inc/autoload.php");

if (isset($_GET['logout'])) {
	unset($_SESSION['zammad_agents'], $_SESSION['zammad_agents_loaded_all'], $_SESSION['zammad_groups'], $_SESSION['zammad_roles'], $_SESSION['zammad_group_user_ids'], $_SESSION['zammad_agents_cache_version']);
	$_SESSION = array();
	session_unset();
	session_destroy();
	if (ini_get('session.use_cookies')) {
		$sessionParams = session_get_cookie_params();
		setcookie(session_name(), '', time() - 3600, $sessionParams['path'], $sessionParams['domain'], $sessionParams['secure'], $sessionParams['httponly']);
	}
	setcookie("logon", "", time() - 3600, "/");
	setcookie("username", "", time() - 3600, "/");
	setcookie("admin", "", time() - 3600, "/");
	setcookie("user_id", "", time() - 3600, "/");
	unset($_COOKIE['logon']);
	unset($_COOKIE['username']);
	unset($_COOKIE['admin']);
	unset($_COOKIE['user_id']);
	header("Location: index.php");
	exit;
}

if (isset($_POST['inputUsername']) && isset($_POST['inputPassword'])) {
	if ($ldap_connection->auth()->attempt($_POST['inputUsername'] . LDAP_ACCOUNT_SUFFIX, $_POST['inputPassword'], $stayAuthenticated = true)) {
		// Successfully authenticated user.
		unset($_SESSION['zammad_agents'], $_SESSION['zammad_agents_loaded_all'], $_SESSION['zammad_groups'], $_SESSION['zammad_roles'], $_SESSION['zammad_group_user_ids'], $_SESSION['zammad_agents_cache_version']);
		session_regenerate_id(true);
		$_SESSION['logon'] = true;
		$_SESSION['username'] = strtoupper($_POST['inputUsername']);
		unset($_SESSION['logon_error']);
		
		$users = $client->resource( ZammadAPIClient\ResourceType::USER )->search("login:" . $_SESSION['username']);
		
		$user = $users[0]->getValues();
		$_SESSION['user_id'] = $user['id'];
		$_SESSION['group_ids'] = $user['group_ids'];
		
			if (isset($_POST['remember']) && $_POST['remember'] == "true") {
			$cookieTime = time() + (86400 * 30); // 86400 = 1 day
			
			setcookie("logon", $_SESSION['logon'], $cookieTime, "/");
			setcookie("username", $_SESSION['username'], $cookieTime, "/");
			setcookie("admin", $_SESSION['admin'], $cookieTime, "/");
			setcookie("user_id", $_SESSION['user_id'], $cookieTime, "/");
		}

		$logRecord = new logs();
		$logRecord->description = $_SESSION['username'] . " logon succesful";
		$logRecord->type = "logon_success";
		$logRecord->log_record();

		header("Location: index.php?n=tickets");
		exit;
	} else {
		// Username or password is incorrect.
		$_SESSION['logon'] = false;
		$_SESSION['username'] = null;
		$_SESSION['admin'] = false;
		$_SESSION['logon_error'] = "Incorrect username/password";

		$logRecord = new logs();
		$logRecord->description = $_POST['inputUsername'] . " logon failed";
		$logRecord->type = "logon_fail";
		$logRecord->log_record();
	}
}
?>
<!DOCTYPE html>
<html lang="en" class="h-100">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="description" content="">
	<meta name="author" content="Andrew Breakspear">
	<title>Task Scheduler</title>
	
	<!-- Bootstrap core CSS/JS -->
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
	<script src="js/app.js"></script>
	<script src="js/colour-modes.js"></script>
</head>

<body>
	<?php
	if (!empty($_SESSION['logon'])) {
		include_once("views/navbar.php");
	}
	
	if ($_SESSION['logon'] == true) {
		if (isset($_GET['n'])) {
			$node = "nodes/" . $_GET['n'] . ".php";
		
			if (!file_exists($node)) {
				$node = "nodes/404.php";
			}
		} else {
			$node = "nodes/tickets.php";
		}
	} else {
		$node = "nodes/logon.php";
	}
	
	include_once($node);
	
	if (!empty($_SESSION['logon'])) {
		include_once("views/footer.php");
	}
	?>
</body>
</html>
