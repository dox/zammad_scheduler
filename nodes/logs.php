<?php
$logsClass = new logs();
$logsClass->purgeLogs();
?>

<div class="container">
	<?php
	$title = "<svg width=\"1em\" height=\"1em\"><use xlink:href=\"inc/icons.svg#logs\"/></svg> Logs";
	$subtitle = "Logs for cron tasks, ticket creation and agent changes.";
	//$icons[] = array("class" => "btn-primary", "name" => "<svg width=\"1em\" height=\"1em\"><use xlink:href=\"inc/icons.svg#zendesk\"/></svg> View Zendesk", "value" => "onclick=\"location.href='" . zd_url . "'\"");

	echo makeTitle($title, $subtitle, $icons);
	?>
	
	<div class="mb-3">
		<p>Example <b>crontab -e</b>:</p>

		<code>
			<ul class="list-unstyled">
				<li># Run Zendesk daily tasks every week day morning:</li>
				<li>0 0 * * MON-FRI cd <?php echo($_SERVER['DOCUMENT_ROOT']); ?>/; php -q cron/daily.php</li>
				<li># Run Zendesk weekly tasks every Monday morning:</li>
				<li>0 0 * * MON cd <?php echo($_SERVER['DOCUMENT_ROOT']); ?>/; php -q cron/weekly.php</li>
				<li># Run Zendesk monthly tasks every 1st of the month:</li>
				<li>0 0 1 * * cd <?php echo($_SERVER['DOCUMENT_ROOT']); ?>/; php -q cron/monthly.php</li>
				<li># Run Zendesk yearly tasks (check every morning):</li>
				<li>0 0 * * * cd <?php echo($_SERVER['DOCUMENT_ROOT']); ?>/; php -q cron/yearly.php</li>
			</ul>
		</code>
	</div>

	<hr />

	<table class="table table-striped">
		<thead>
			<tr>
				<th width="200px">Date</th>
				<th>Description</th>
			</tr>
		</thead>
		<tbody>
			<?php
			echo $logsClass->displayLogs();
			?>
		</tbody>
	</table>
</div>
