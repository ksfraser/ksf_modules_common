<?php

require_once '../vendor/autoload.php';

use Orangehrm\API\Client;
use Orangehrm\API\HTTPRequest;

//$client = new Client('https://orange.fraserhighlandshoppe.ca','admin','randomZaqwsx9');
//$client = new Client('https://orange.fraserhighlandshoppe.ca/symfony/web/index.php/','admin','randomZaqwsx9');
$client = new Client('https://orange.fraserhighlandshoppe.ca/symfony/web/index.php/','suitecrm','randomZaqwsx9@');

$request = new HTTPRequest('employee/search');
//$request = new HTTPRequest('employee/1');
$result = $client->get($request)->getResult();


echo "-----------------------------------\n";
echo " Employee Info \n";
echo "----------------------------------- \n";
foreach ($result['data'] as $item) {

    echo $item['firstName'].' '. $item['lastName']."\n";
}
