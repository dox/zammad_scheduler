<?php
function printArray($array) {
echo ("<pre>");
print_r ($array);
echo ("</pre>");
}

function makeTitle($title = null, $subtitle = null, $iconsArray = null) {
	$output  = "<div class=\"px-3 py-3 pt-md-5 pb-md-4 text-center\">";
	$output .= "<h1 class=\"display-4\">" . $title . "</h1>";
	
	if ($subtitle != null) {
		$output .= "<p class=\"lead\">" . $subtitle . "</p>";
	}
	
	$output .= "</div>";
	
	$output .= "<div class=\"pb-3 text-end\">";
	foreach ($iconsArray AS $icon) {
		$output .= "<button type=\"button\" class=\"btn btn-sm ms-1 " . $icon['class'] . "\"" . $icon['value'] . ">";
		$output .= $icon['name'];
		$output .= "</button>";
	}
	$output .= "</div>";
	
	return $output;
}

function exitOnError($object) {
	if ( $object->hasError() ) {
		print $object->getError() . "\n";
		exit(1);
	}
}
?>