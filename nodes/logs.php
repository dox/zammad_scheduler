<?php
$logsClass = new logs();
$logsClass->purgeLogs();
?>

<div class="container">
	<?php
	$title = "<i class=\"bi bi-clock-history\"></i> Logs";
	$subtitle = "Logs for cron tasks, ticket creation and agent changes.";
	//$icons[] = array("class" => "btn-primary", "name" => "<svg width=\"1em\" height=\"1em\"><use xlink:href=\"inc/icons.svg#zendesk\"/></svg> View Zendesk", "value" => "onclick=\"location.href='" . zd_url . "'\"");

	echo makeTitle($title, $subtitle, $icons);
	?>
	
	<div class="mb-3">
		<p>Example <b>crontab -e</b>:</p>

		<code>
			<ul class="list-unstyled">
				<li># Run the combined scheduler once a day:</li>
				<li>0 0 * * * cd <?php echo($_SERVER['DOCUMENT_ROOT']); ?>/; php -q cron/run.php</li>
				<li># This single job checks daily, weekly, monthly, and yearly tickets automatically.</li>
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
