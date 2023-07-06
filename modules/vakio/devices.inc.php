<?php
/*
* @version 0.1 (wizard)
*/
global $session;

if ($this->owner->name=='panel') {
    $out['CONTROLPANEL']=1;
}

$device_types = array(
    0 => array(
        "NAME"=>"VAKIO Atmosphere",
        "IMG_NAME"=>"atmosphere.png",
        "IS_HAS_SENSORS"=>true,
        "IS_HAS_CONTROLS"=>false,
        "IS_HAS_CO2"=>false,
        "CONTROLS"=>array()
    ),
    1 => array(
        "NAME"=>"VAKIO Base Smart",
        "IMG_NAME"=>"base_smart.png",
        "IS_HAS_SENSORS"=>false,
        "IS_HAS_CONTROLS"=>true,
        "SENSORS"=>array(),
        "CONTROLS"=>array("state", "workmode", "speed")
    ),
    2 => array(
        "NAME"=>"VAKIO KIV",
        "IMG_NAME"=>"kiv.png",
        "IS_HAS_SENSORS"=>false,
        "IS_HAS_CONTROLS"=>true,
        "SENSORS"=>array(),
        "CONTROLS"=>array("state", "gate")
    ),
    3 => array(
        "NAME"=>"VAKIO Openair",
        "IMG_NAME"=>"openair.png",
        "IS_HAS_SENSORS"=>true,
        "IS_HAS_CONTROLS"=>true,
        "IS_HAS_CO2"=>false,
        "SENSORS"=>array(),
        "CONTROLS"=>array("state", "workmode", "speed", "gate")
    )
);

$result=SQLSelect("SELECT * FROM `vakio_devices`");

/* 
ID
TITLE
VAKIO_DEVICE_TYPE
VAKIO_DEVICE_MQTT_TOPIC
VAKIO_DEVICE_STATE
*/
for ($i=0; $i<count($result); $i++) {
    $device_type = $device_types[$result[$i]['VAKIO_DEVICE_TYPE']];
    $result[$i]['DEVICE_TYPE'] = $device_type;
    $result[$i]['VAKIO_DEVICE_STATE'] = array_change_key_case(json_decode($result[$i]['VAKIO_DEVICE_STATE'], true), CASE_UPPER);
    // $test = implode("\t", array_keys($result[$i]['VAKIO_DEVICE_STATE']));
    // $test = implode("\t", json_decode((string)$result[$i]['VAKIO_DEVICE_STATE'], true));
    $test = json_decode((string)$result[$i]['VAKIO_DEVICE_STATE'], true);
    // for ($j=0; $j<count($result[$i]['DEVICE_DEVICE_STATE']); $j++){
        
    // }
    $out["TEST"]=$test;
    echo $result;
}

$out["DEVICES"]=$result;
// $out["TEST"]=$test;