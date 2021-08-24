<?php
$agentsClass = new agents();
$agent = $agentsClass->getAgent($_GET['agentUID']);

$tickets = new tickets();
//$jobsAssigned = $tickets->jobs_involved_with($agent->zendesk_id);

?>

<div class="container">
	<?php
	$title = "<svg width=\"1em\" height=\"1em\"><use xlink:href=\"inc/icons.svg#agents\"/></svg> " . $agent['firstname'] . " " . $agent['lastname'];
	$subtitle = "Agent ID: " . $agent['id'];
	$icons[] = array("class" => "btn-danger", "name" => "<svg width=\"1em\" height=\"1em\"><use xlink:href=\"inc/icons.svg#delete\"/></svg> Delete Agent", "value" => "data-bs-toggle=\"modal\" data-bs-target=\"#agentDeleteModal\"");

	echo makeTitle($title, $subtitle, $icons);
	?>

	<form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post">
	<div class="row">
		<div class="col-lg-6 mb-3">
			<div class="mb-3">
				<label for="inputFirstname" class="form-label">First Name</label>
				<input type="text" class="form-control" name="inputFirstname" value="<?php echo $agent['firstname']; ?>">
			</div>
			<div class="mb-3">
				<label for="inputLastname" class="form-label">Last Name</label>
				<input type="text" class="form-control" name="inputLastname" value="<?php echo $agent->lastname; ?>">
			</div>
			<div class="mb-3">
				<label for="inputEmail" class="form-label">Email Address</label>
				<input type="email" class="form-control" name="inputEmail" value="<?php echo $agent->email; ?>">
			</div>
			<div class="mb-3">
				<label for="inputZendeskID" class="form-label">Zendesk ID</label>
				<input type="number" class="form-control" name="inputZendeskID" value="<?php echo $agent->zendesk_id; ?>">
			</div>
			<div class="mb-3">
				<?php
				if (count($jobsAssigned) == 0) {
					$disabled = "";
				} else {
					$disabled = " disabled";
				}
				?>
				<label for="inputEnabled" class="form-label">User Account Status</label>
				<select class="form-select" id="inputEnabled" name="inputEnabled" aria-label="Default select example" <?php echo $disabled; ?>>
					<option value="1" <?php if ($agent->enabled == "1") { echo " selected";}?>>Enabled</option>
					<option value="0" <?php if ($agent->enabled == "0") { echo " selected";}?>>Disabled</option>
				</select>
			</div>
			<div class="d-grid gap-2">
				<button type="submit" class="btn btn-primary">Modify</button>
			</div>
		</div>

		<div class="col-lg-6">
			<h4>Jobs assigned to/logged by:</h4>
			<?php
			foreach($jobsAssigned AS $job) {
				echo $job->job_display();
			}
			?>
		</div>
	</div>
	</form>
</div>

<!-- Modal -->
<div class="modal fade" id="agentDeleteModal" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form action="index.php?n=agents&agentDelete=<?php echo $agent->uid; ?>" method="post">
			<div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="staticBackdropLabel">Delete Agent</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
				<?php
				if (count($jobsAssigned) == 0) {
					$disabled = "";
					$output  = "<p>Are you sure you want to delete this agent from the Zendesk Scheduler?  This will not delete the agent from Zendesk.</p>";
					$output .= "<p class=\"text-danger\"><strong>WARNING!</strong> This action cannot be undone!</p>";
				} else {
					$disabled = " disabled ";
					$output  = "<p>* User cannot be deleted when there are jobs assigned to/logged by them</p>";
				}

				echo $output;
				?>

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-link text-muted" data-bs-dismiss="modal">Close</button>
				<button type="submit" class="btn btn-danger" <?php echo $disabled; ?>><svg width="1em" height="1em"><use xlink:href="inc/icons.svg#delete"/></svg> Delete Agent</button>
      </div>
    </div>
		</form>
  </div>
</div>

<?php
printArray($agent);

?>