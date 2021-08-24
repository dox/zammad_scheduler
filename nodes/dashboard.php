<?php
use Zendesk\API\HttpClient as ZendeskAPI;

$team = new team();
$teamMembersEnabled = $team->team_all_enabled();

$subdomain = zd_subdomain;
$username  = zd_username;
$token     = zd_token;

$client = new ZendeskAPI($subdomain);
$client->setAuth('basic', ['username' => $username, 'token' => $token]);

?>

<div class="container">
	<div class="px-3 py-3 pt-md-5 pb-md-4 text-center">
		<h1 class="display-4">Dashboard</h1>
		<p class="lead">Logs for cron tasks, ticket creation and agent changes.</p>
	</div>

  <?php
  foreach ($teamMembersEnabled AS $teamMember) {
    //echo $teamMember->zendesk_id;
    $tickets = $client->users($teamMember->zendesk_id)->tickets()->requested();

    echo "<pre>";
    $tickets = $client->tickets()->findAll();
    //print_r($tickets);
    echo $teamMember->firstname;
    echo count($tickets->tickets);
    echo "<br />";
    echo "</pre>";

    //print_r($teamMember);
  }?>
</div>
