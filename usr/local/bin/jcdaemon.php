<?php

//Open an openlog instance
openlog("bsi2r", LOG_PID | LOG_PERROR , LOG_DAEMON);

// Read and initialize variables from the configuration file
$ini_config = parse_ini_file("/etc/bsi2r/bsi2r.ini", true);
$WebSrvInfo=$ini_config['WebSiteInfo'];
$SrvInfo=$ini_config['SrvInfo'];

if (!empty($SrvInfo['LCTIME']))
{
	$LCTIME=$SrvInfo['LCTIME'];
}
else
{
	$LCTIME='fr_FR.UTF8';
}

// Set the local time
setlocale (LC_TIME, $LCTIME); 


// Test that the configuration fil contain the requirement
// ApiKey to access to the Url api
foreach($WebSrvInfo as $WebValue){
	if (empty($WebValue)){
		syslog(LOG_ERR, "UrlPath or ApiKey not found. Please check your configuration file. Values required");
	}
}

// Test the Server values and put the default if blank
if (!empty($SrvInfo['RedisHost']))
{
	$RedisHost=$SrvInfo['RedisHost'];
}
else
{
	$RedisHost='localhost';
}

if (!empty($SrvInfo['RedisPort']))
{
	$RedisPort=$SrvInfo['RedisPort'];
}
else
{
	$RedisPort='6379';
}
	
if (!empty($SrvInfo['Refresh']))
{
	$Refresh=$SrvInfo['Refresh'];
}
else
{
	$Refresh='2';
}

// One only argument required. The country
if (!isset($argv[1]))
{
	syslog(LOG_ERR, "No country selected. Please run script with \"help\" for argument.\n") ;
	exit;
}
elseif ($argv[1]=="help")
{
	syslog(LOG_ERR, "Please use this script with a country. All country supported are available on https://developer.jcdecaux.com \n");
	exit;
}
else
{
	$Country=$argv[1];
}


// Define the Redis environment
$redis=new Redis() or die(syslog(LOG_ERR, "Can't load redis module. Please the that the redis module is installeda"));
$redis->connect($RedisHost,$RedisPort) or die(syslog(LOG_ERR, $RedisHost." : Redis server is unreachable on port ".$RedisPort.". \n"));
$i=0;

// Daemon starting
while (true) {

// Url of country construction
#$UrlCountry=$WebSrvInfo['UrlPath']."?contract=".$Country."&apiKey=".$WebSrvInfo['ApiKey'];
// Dev url
$UrlCountry="/var/www/".$Country;

#$UrlCountry="/home/croquette/paris-test.json";

//Read and decode the Json file. Check the url with 1s of timeout.
$TimeOut = stream_context_create(array('http' => array('timeout'=>0.1))); 
$GetCountryContent=file_get_contents($UrlCountry, 0, $TimeOut);

if ($GetCountryContent==false)
{
	syslog(LOG_ERR, "$UrlCountry is unreachable or not available. \n For more detail please go to the https://developer.jcdecaux.com");
	exit;
}

$DecodeCountryContent=json_decode($GetCountryContent);

if ($DecodeCountryContent==NULL)
{
	syslog(LOG_ERR, $UrlCountry." : is not a json valid format. \n");
	exit;
}

foreach($DecodeCountryContent as $IdStation ){

// Put the json into the redis server
	$Update=strftime("%A %d %B %T");
	$AvBike=$IdStation->available_bikes;
	$AvBikeStd=$IdStation->available_bike_stands;
	$Addr=$IdStation->address;
	// Delete the city code from the address format
	$AddrRpl=preg_replace('/ - [0-9][0-9[0-9][0-9][0-9].+$/','',$Addr);
	$AddrLow=strtolower($AddrRpl);
	$City=$IdStation->contract_name;
	$CityLow=strtolower($City);
	$Status=$IdStation->status;
	$NewJson='{"available_bikes":'.$AvBike.',"available_bike_stands":'.$AvBikeStd.',"LastUpdate":"'.$Update.'","Status":"'.$Status.'","Address":"'.$AddrLow.'","City":"'.$CityLow.'"}';
        $redis->set("$Country:".$IdStation->number,$NewJson);
}

// Sleep this daemon for X secondes
sleep($Refresh);
}

$redis->close();

closelog();

?>


