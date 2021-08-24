<?php
require_once 'vendor/autoload.php';
echo zammad_username;


echo "test";
echo "<pre>";

require_once 'vendor/autoload.php';

$zammad_api_client_config = [
    'url' => 'https://help.seh.ox.ac.uk',

    // with username and password
    'username' => 'breakspear',
    'password' => 'P!ssport7',
    'debug'         => false
    // or with HTTP token:
    // 'http_token' => '...',

    // or with OAuth2 token:
    //'oauth2_token' => '...',
];

use ZammadAPIClient\Client;
use ZammadAPIClient\ResourceType;


$client = new Client($zammad_api_client_config);

$email_address = "andrew.breakspear@seh.ox.ac.uk";

$users = $client->resource( ResourceType::USER )->search("role_ids:2");
if ( !is_array($users) ) {
    exitOnError($users);
} else {
    print 'Found ' . count($users) . ' user(s) ' . "\n";

}

echo "<hr />";



$ticket_text = "owner.email:andrew.breakspear@seh.ox.ac.uk";
$tickets = $client->resource( ResourceType::TICKET )->search($ticket_text);
    if ( !is_array($tickets) ) {
        exitOnError($tickets);
    } else {
        print 'Found ' . count($tickets) . ' ticket(s) with text ' . $ticket_text . "\n";
    }
    
    
    





//
// Create a ticket
//
$ticket_text = 'API test ticket';

$ticket_data = [
    'group_id'    => 1,
    'priority_id' => 1,
    'state_id'    => 1,
    'title'       => $ticket_text,
    'customer_id' => 1,
    'article'     => [
        'subject' => $ticket_text,
        'body'    => $ticket_text,
    ],
];

$ticket = $client->resource( ResourceType::TICKET );
$ticket->setValues($ticket_data);
//$ticket->save();
exitOnError($ticket);

$ticket_id = $ticket->getID(); // same as getValue('id')

//
// Fetch ticket
//
$ticket = $client->resource( ResourceType::TICKET )->get($ticket_id);
exitOnError($ticket);
print_r( $ticket->getValues() );

//
// Fetch ticket articles
//
$ticket_articles = $ticket->getTicketArticles();
foreach ( $ticket_articles as $ticket_article ) {
    print_r($ticket_article);
}

//
// Search ticket
//
$tickets = $client->resource( ResourceType::TICKET )->search($ticket_text);
if ( !is_array($tickets) ) {
    exitOnError($tickets);
} else {
    print 'Found ' . count($tickets) . ' ticket(s) with text ' . $ticket_text . "\n";
}

//
// Delete created ticket
//
//$ticket->delete();
//exitOnError($ticket);

function exitOnError($object) {
    if ( $object->hasError() ) {
        print $object->getError() . "\n";
        exit(1);
    }
}



echo "</pre>";
?>
