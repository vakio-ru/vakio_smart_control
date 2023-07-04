<?php
/*
* @version 0.1 (wizard)
*/
  if ($this->owner->name=='panel') {
   $out['CONTROLPANEL']=1;
  }
  $table_name='vakio_devices';
  $rec=SQLSelectOne("SELECT * FROM $table_name WHERE ID='$id'");

  // Создаём список с наименованием типов устройств
  $out['VAKIO_TYPES'] = array(
    0 => array(
      "TITLE"=>"Atmosphere",
      "SELECTED"=>false,
      "ID"=>0
    ),
    1 => array(
      "TITLE"=>"Base Smart",
      "SELECTED"=>false,
      "ID"=>1
    ),
    2 => array(
      "TITLE"=>"Kiv Pro/New",
      "SELECTED"=>false,
      "ID"=>2
    ),
    3 => array(
      "TITLE"=>"Openair",
      "SELECTED"=>false,
      "ID"=>3
    ),
  );


  if ($this->mode=='update') {
    $ok=1;
    //updating '<%LANG_TITLE%>' (varchar, required)
    $rec['TITLE']=gr('title');
    $rec['VAKIO_DEVICE_TYPE']=(int) gr('vakio_device_type');
    $rec['VAKIO_DEVICE_MQTT_TOPIC']=gr('vakio_device_mqtt_topic');

    if ($rec['TITLE']=='') {
      $out['ERR_TITLE']=1;
      $ok=0;
    }
    if ($rec['VAKIO_DEVICE_TYPE'] > 3) {
      $out['ERR_VAKIO_DEVICE_TYPE']=1;
      $ok=0;
    }
    if ($rec['VAKIO_DEVICE_MQTT_TOPIC']=='') {
      $out['ERR_VAKIO_DEVICE_MQTT_TOPIC']=1;
      $ok=0;
    }

    $out["VAKIO_TYPES"][$rec["VAKIO_DEVICE_TYPE"]]["SELECTED"] = true;
    $rec['VAKIO_DEVICE_STATE']= "{}";
    
    //UPDATING RECORD
    if ($ok) {
      if ($rec['ID']) {
        SQLUpdate($table_name, $rec); // update
      } else {
        $new_rec=1;
        $rec['ID']=SQLInsert($table_name, $rec); // adding new record
      }
      $out['OK']=1;
    } else {
      $out['ERR']=1;
    }
  }
  if (is_array($rec)) {
   foreach($rec as $k=>$v) {
    if (!is_array($v)) {
     $rec[$k]=htmlspecialchars($v);
    }
   }
  }
  outHash($rec, $out);
