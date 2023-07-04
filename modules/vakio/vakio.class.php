<?php
/**
* Vakio Smart Control 
* @package project
* @author Wizard <sergejey@gmail.com>
* @copyright http://majordomo.smartliving.ru/ (c)
* @version 0.1 (wizard, 06:07:32 [Jul 03, 2023])
*/
//
//
class vakio extends module {
/**
* vakio
*
* Module class constructor
*
* @access private
*/
function __construct() {
  $this->name="vakio";
  $this->title="Vakio Smart Control";
  $this->module_category="<#LANG_SECTION_DEVICES#>";
  $this->checkInstalled();
}
/**
* saveParams
*
* Saving module parameters
*
* @access public
*/
function saveParams($data=1) {
 $p=array();
 if (IsSet($this->id)) {
  $p["id"]=$this->id;
 }
 if (IsSet($this->view_mode)) {
  $p["view_mode"]=$this->view_mode;
 }
 if (IsSet($this->edit_mode)) {
  $p["edit_mode"]=$this->edit_mode;
 }
 if (IsSet($this->tab)) {
  $p["tab"]=$this->tab;
 }
 return parent::saveParams($p);
}
/**
* getParams
*
* Getting module parameters from query string
*
* @access public
*/
function getParams() {
  global $id;
  global $mode;
  global $view_mode;
  global $edit_mode;
  global $tab;
  if (isset($id)) {
   $this->id=$id;
  }
  if (isset($mode)) {
   $this->mode=$mode;
  }
  if (isset($view_mode)) {
   $this->view_mode=$view_mode;
  }
  if (isset($edit_mode)) {
   $this->edit_mode=$edit_mode;
  }
  if (isset($tab)) {
   $this->tab=$tab;
  }
}
/**
* Run
*
* Description
*
* @access public
*/
function run() {
 global $session;
  $out=array();
  if ($this->action=='admin') {
   $this->admin($out);
  } else {
   $this->usual($out);
  }
  if (IsSet($this->owner->action)) {
   $out['PARENT_ACTION']=$this->owner->action;
  }
  if (IsSet($this->owner->name)) {
   $out['PARENT_NAME']=$this->owner->name;
  }
  $out['VIEW_MODE']=$this->view_mode;
  $out['EDIT_MODE']=$this->edit_mode;
  $out['MODE']=$this->mode;
  $out['ACTION']=$this->action;
  $out['TAB']=$this->tab;
  $this->data=$out;
  $p=new parser(DIR_TEMPLATES.$this->name."/".$this->name.".html", $this->data, $this);
  $this->result=$p->result;
}
/**
* BackEnd
*
* Module backend
*
* @access public
*/
function admin(&$out) {
  $this->getConfig();
  $out['MQTT_CLIENT'] = $this->config['MQTT_CLIENT'];
  $out['MQTT_HOST'] = $this->config['MQTT_HOST'];
  $out['MQTT_PORT'] = $this->config['MQTT_PORT'];
  $out['MQTT_QUERY'] = $this->config['MQTT_QUERY'];

  if (!$out['MQTT_HOST']) {
    $out['MQTT_HOST'] = 'localhost';
  }
  if (!$out['MQTT_PORT']) {
    $out['MQTT_PORT'] = '1883';
  }
  if (!$out['MQTT_QUERY']) {
    $out['MQTT_QUERY'] = 'vakio';
  }

  $out['MQTT_USERNAME'] = $this->config['MQTT_USERNAME'];
  $out['MQTT_PASSWORD'] = $this->config['MQTT_PASSWORD'];
  $out['MQTT_AUTH'] = $this->config['MQTT_AUTH'];

  if ($this->view_mode=='update_settings') {
    global $mqtt_client;
    global $mqtt_host;
    global $mqtt_username;
    global $mqtt_password;
    global $mqtt_port;
    global $mqtt_auth;


    $this->config['MQTT_CLIENT'] = trim($mqtt_client);
    $this->config['MQTT_HOST'] = trim($mqtt_host);
    $this->config['MQTT_USERNAME'] = trim($mqtt_username);
    $this->config['MQTT_PASSWORD'] = trim($mqtt_password);
    $this->config['MQTT_AUTH'] = (int)$mqtt_auth;
    $this->config['MQTT_PORT'] = (int)$mqtt_port;

    $this->saveConfig();

    setGlobal('cycle_vakio', 'restart');

    $this->redirect("?");
  }
  if (isset($this->data_source) && !$_GET['data_source'] && !$_POST['data_source']) {
    $out['SET_DATASOURCE']=1;
  }
  if ($this->data_source=='vakio_devices' || $this->data_source=='') {
    if ($this->view_mode=='' || $this->view_mode=='search_vakio_devices') {
    $this->search_vakio_devices($out);
    }
    if ($this->view_mode=='edit_vakio_devices') {
    $this->edit_vakio_devices($out, $this->id);
    }
    if ($this->view_mode=='delete_vakio_devices') {
    $this->delete_vakio_devices($this->id);
    $this->redirect("?");
    }
  }
}
/**
* FrontEnd
*
* Module frontend
*
* @access public
*/
function usual(&$out) {
 $this->admin($out);
}
/**
* vakio_devices search
*
* @access public
*/
 function search_vakio_devices(&$out) {
  require(dirname(__FILE__).'/vakio_devices_search.inc.php');
 }
/**
* vakio_devices edit/add
*
* @access public
*/
 function edit_vakio_devices(&$out, $id) {
  require(dirname(__FILE__).'/vakio_devices_edit.inc.php');
 }
/**
* vakio_devices delete record
*
* @access public
*/
 function delete_vakio_devices($id) {
  $rec=SQLSelectOne("SELECT * FROM vakio_devices WHERE ID='$id'");
  // some action for related tables
  SQLExec("DELETE FROM vakio_devices WHERE ID='".$rec['ID']."'");
 }
 function processCycle() {
 $this->getConfig();
  //to-do
 }
/**
* Install
*
* Module installation routine
*
* @access private
*/
 function install($data='') {
  parent::install();
 }
/**
* Uninstall
*
* Module uninstall routine
*
* @access public
*/
 function uninstall() {
  SQLExec('DROP TABLE IF EXISTS vakio_devices');
  parent::uninstall();
 }
/**
* dbInstall
*
* Database installation routine
*
* @access private
*/
 function dbInstall($data) {
/*
vakio_devices - 
*/
  $data = <<<EOD
 vakio_devices: ID int(10) unsigned NOT NULL auto_increment
 vakio_devices: TITLE varchar(100) NOT NULL DEFAULT ''
 vakio_devices: VAKIO_DEVICE_TYPE int(10) NOT NULL DEFAULT ''
 vakio_devices: VAKIO_DEVICE_MQTT_TOPIC varchar(255) NOT NULL DEFAULT ''
 vakio_devices: VAKIO_DEVICE_STATE varchar(255) NOT NULL DEFAULT ''
EOD;
  parent::dbInstall($data);
 }
// --------------------------------------------------------------------
}
/*
*
* TW9kdWxlIGNyZWF0ZWQgSnVsIDAzLCAyMDIzIHVzaW5nIFNlcmdlIEouIHdpemFyZCAoQWN0aXZlVW5pdCBJbmMgd3d3LmFjdGl2ZXVuaXQuY29tKQ==
*
*/
