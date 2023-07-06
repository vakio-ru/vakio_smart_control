<?php
/*
* @version 0.1 (wizard)
*/
global $session;
global $device;

if ($this->owner->name=='panel') {
    $out['CONTROLPANEL']=1;
}
// if (isset($device)) {
    
// }

$data = array(
    "id" => 2,
    "not_id" => 3
);

echo json_encode($data);