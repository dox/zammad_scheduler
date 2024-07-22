<?php
include_once("inc/autoload.php");

if (isset($_GET['logout'])) {
	$_SESSION = array();
	unset($_COOKIE['logon']);
	unset($_COOKIE['username']);
	unset($_COOKIE['admin']);
	unset($_COOKIE['user_id']);
}

if (isset($_POST['inputUsername']) && isset($_POST['inputPassword'])) {
	if ($ldap_connection->auth()->attempt($_POST['inputUsername'] . LDAP_ACCOUNT_SUFFIX, $_POST['inputPassword'], $stayAuthenticated = true)) {
		// Successfully authenticated user.
		$_SESSION['logon'] = true;
		$_SESSION['username'] = strtoupper($_POST['inputUsername']);
		
		$users = $client->resource( ZammadAPIClient\ResourceType::USER )->search("login:" . $_SESSION['username']);
		
		$user = $users[0]->getValues();
		$_SESSION['user_id'] = $user['id'];
		$_SESSION['group_ids'] = $user['group_ids'];
		
		if ($_POST['remember'] == "true") {
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
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
	<script src="js/app.js"></script>
</head>

<body class="bg-light">
	<?php include_once("views/navbar.php");
	
	if ($_SESSION['logon'] == true) {
		if (isset($_GET['n'])) {
			$node = "nodes/" . $_GET['n'] . ".php";
		
			if (!file_exists($node)) {
				$node = "nodes/404.php";
			}
		} else {
			$node = "nodes/index.php";
		}
	} else {
		$node = "nodes/logon.php";
	}
	
	include_once($node);
	
	include_once("views/footer.php");
	?>
</body>
</html>
