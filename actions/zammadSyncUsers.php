<?php
include_once("../inc/autoload.php");

$agentsClass = new agents();

$agentsObject = $client->resource(ZammadAPIClient\ResourceType::USER)->search("role_ids:2 AND active:true");

foreach ($agentsObject AS $agentObject) {
	if (!is_array($agentObject)) {
		$agentObject = $agentObject->getValues();
		//printArray($agentObject);
		
		$localAgent = $agentsClass->getAgent($agentObject['id']);
		
		if (empty($localAgent)) {
			//create the Zammad agent in the local database
			$agentArray['agent_id'] = $agentObject['id'];
			$agentArray['ldap'] = $agentObject['login'];
			$agentArray['firstname'] = $agentObject['firstname'];
			$agentArray['lastname'] = $agentObject['lastname'];
			$agentArray['group_id'] = "1";
			
			$agentsClass->create($agentArray);
		}
	}
}
?>