<?php
##
##		Generates list of plugins (seperated by space) based on an API call to anchor.host containing list of plugins to preload
##
## 		Pass arguments from command line like this
##		php plugin-generate.php token=random customers=1294,1245 website=anchorhost
## 
##		assign command line arguments to varibles 
## 		userlist=~/Documents/Scripts/userlist.csv becomes $_GET['userlist']
##

if (isset($argv)) {
    parse_str(implode('&', array_slice($argv, 1)), $_GET);
}

$token = $_GET['token'];
$customers = explode(",", $_GET['customers'] );

$preloaded_users = array();

foreach ($customers as $customer) {

    $curl = curl_init( "https://anchor.host/wp-json/wp/v2/customer/$customer/?token=$token" );
    curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 1);
    $response = curl_exec( $curl );
    $json_results_website = json_decode($response, true);
    $plugins = $json_results_website["preloaded_plugins"];

    foreach ($plugins as $plugin) {
        $preloaded_plugins[] = $plugin;
    }  
}

$output = "";

foreach ($preloaded_plugins as $preloaded_plugin) {
	$plugin = $preloaded_plugin["plugin"];
    $output .= $plugin . " ";
}

echo trim($output);