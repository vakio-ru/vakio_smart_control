<?php
chdir(dirname(__FILE__) . '/../');

include_once("./config.php");
include_once("./lib/loader.php");
include_once("./lib/threads.php");

set_time_limit(0);
// connecting to database
$db = new mysql(DB_HOST, '', DB_USER, DB_PASSWORD, DB_NAME);

include_once("./load_settings.php");
include_once(DIR_MODULES . "control_modules/control_modules.class.php");

$ctl = new control_modules();

set_time_limit(0);

include_once(ROOT . "3rdparty/phpmqtt/phpMQTT.php");
include_once(DIR_MODULES . 'vakio/vakio.class.php');

$vakio_module = new vakio();
$vakio_module->getConfig();

$tmp = SQLSelectOne("SELECT `ID` FROM `vakio_devices` LIMIT 1");
if (!$tmp['ID'])
   exit; // no devices added -- no need to run this cycle

echo date("H:i:s") . " running " . basename(__FILE__) . PHP_EOL;
$latest_check=0;
$checkEvery=2; // poll every 5 seconds

if ($vakio_module->config["MQTT_CLIENT"]) {
   $client_name = $mqtt->config['MQTT_CLIENT'];
} else {
   $client_name = "majordomo-client-" . random_int(1, 100);
}

if ($mqtt->config['MQTT_AUTH']) {
   $username = $mqtt->config['MQTT_USERNAME'];
   $password = $mqtt->config['MQTT_PASSWORD'];
} else {
   $username = "";
   $password = "";
}

$host = 'localhost';
$host = 'test.mosquitto.org';

if ($mqtt->config['MQTT_HOST']) {
   $host = $mqtt->config['MQTT_HOST'];
}

if ($mqtt->config['MQTT_PORT']) {
   $port = $mqtt->config['MQTT_PORT'];
} else {
   $port = 1883;
}

$mqtt_client = new Bluerhinos\phpMQTT($host, $port, $client_name);

$connect = $mqtt_client->connect(true, NULL, $username, $password);

if (!$connect) {
   exit(1);
}

$query_list = array();
$rec = SQLSelect("SELECT `VAKIO_DEVICE_MQTT_TOPIC` FROM `vakio_devices` WHERE 1");
foreach($rec as $row) {
   $query_list[] = $row['VAKIO_DEVICE_MQTT_TOPIC'];
}
unset($row);

$total = count($query_list);
echo date('H:i:s') . " Topics to watch: $query_list (Total: $total)\n";

for ($i = 0; $i < $total; $i++) {
   $path = trim($query_list[$i]) . "/#";
   echo date('H:i:s') . " Path: $path\n";
   $topics[$path] = array("qos" => 0, "function" => "procmsg");
}

$mqtt_client->subscribe($topics, 0);
$previousMillis = 0;

while ($mqtt_client->proc())
{
   $operations = checkOperationsQueue('public');
   for ($i=0; $i<count($operations); $i++) {
      $topic = "vakio/" . $operations["DATANAME"];
      $value = $operations["DATAVALUE"];
      $mqtt_client->publish($topic, $value, 0, true);
      print_r($operations[DATANAME]);
      print_r('Publish to ' . $topic . ' value = ' . $value);
   }
   $currentMillis = round(microtime(true) * 10000);
   if ($currentMillis - $previousMillis > 10000) {
      $previousMillis = $currentMillis;
      setGlobal((str_replace('.php', '', basename(__FILE__))) . 'Run', time(), 1);

      if (file_exists('./reboot') || IsSet($_GET['onetime'])) {
         $mqtt_client->close();
         $db->Disconnect();
         exit;
      }
   }
}


$mqtt_client->close();

/**
 * По полученному топику определяет устройство, которому обновляет поле VAKIO_DEVICE_STATE.
 * @param mixed $topic Topic
 * @param mixed $msg Message
 * @return void
 */
function procmsg($topic, $msg) {
   if (!isset($topic) || !isset($msg)) return false;
   
   $topic_parts = explode("/", $topic);
   $topic_parts_count = count($topic_parts);
   $topic_db_format = $topic_parts[0];
   for ($i = 1; $i < $topic_parts_count - 1; $i++){
      $topic_db_format = $topic_db_format . "/" . $topic_parts[$i];
   }
   $endpoint = $topic_parts[$topic_parts_count - 1];
   $rec = SQLSelectOne("SELECT * FROM `vakio_devices` WHERE `VAKIO_DEVICE_MQTT_TOPIC`='$topic_db_format'");
   
   if(!$rec['ID']) {
      echo date("Y-m-d H:i:s") . " Ignore received from {$topic} : $msg\n";
      return false;
   }
   
   $state = json_decode($rec["VAKIO_DEVICE_STATE"], true);
   $state[$endpoint] = $msg;
   $rec["VAKIO_DEVICE_STATE"] = json_encode($state);
   
   SQLUpdate("vakio_devices", $rec);
}

$db->Disconnect(); // closing database connection
