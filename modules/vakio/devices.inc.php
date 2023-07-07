<?php
/*
* @version 0.1 (wizard)
*/
global $session;

if ($this->owner->name=='panel') {
    $out['CONTROLPANEL']=1;
}


/* 
inflow - Приток
inflow_max - Приток MAX
recuperator - Рекуперация лето
winter - Рекуперация зима
outflow - Вытяжка
outflow_max - Вытяжка MAX
night - Ночной 
*/
$device_types = array(
    0 => array(
        "NAME"=>"VAKIO Atmosphere",
        "IMG_NAME"=>"atmosphere.png",
        "IS_HAS_SENSORS"=>1,
        "IS_HAS_CONTROLS"=>0,
        "IS_HAS_CO2"=>1
    ),
    1 => array(
        "NAME"=>"VAKIO Base Smart",
        "IMG_NAME"=>"base_smart.png",
        "IS_HAS_SENSORS"=>0,
        "IS_HAS_CONTROLS"=>1
    ),
    2 => array(
        "NAME"=>"VAKIO KIV",
        "IMG_NAME"=>"kiv.png",
        "IS_HAS_SENSORS"=>0,
        "IS_HAS_CONTROLS"=>1
    ),
    3 => array(
        "NAME"=>"VAKIO Openair",
        "IMG_NAME"=>"openair.png",
        "IS_HAS_SENSORS"=>1,
        "IS_HAS_CONTROLS"=>1,
        "IS_HAS_CO2"=>0
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
    $device_state = array_change_key_case(json_decode($result[$i]['VAKIO_DEVICE_STATE'], true), CASE_UPPER);
    unset($result[$i]['VAKIO_DEVICE_STATE']);
    foreach ($device_state as $key => $value) {
        $result[$i][$key] = $value;
    }
    foreach ($device_type as $key => $value) {
        $result[$i][$key] = $value;
    }
}

$out["DEVICES"]=$result;
// $out["TEST"]=$test;