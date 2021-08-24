<?php
$agentsClass = new agents();
?>

<div class="container">
	<?php
	$title = "<svg width=\"1em\" height=\"1em\"><use xlink:href=\"inc/icons.svg#agents\"/></svg> Agents";
	$subtitle = "Agents impored from Zendesk that can be assigned scheduled tickets.";
	//$icons[] = array("class" => "btn-warning", "name" => "<svg width=\"1em\" height=\"1em\"><use xlink:href=\"inc/icons.svg#zendesk\"/></svg> Sync Zendesk Agents", "value" => "onclick=\"location.href='index.php?n=agents&import=true'\"");

	echo makeTitle($title, $subtitle, $icons);
	?>

	<?php echo $agentsClass->displayAgents(); ?>
</div>