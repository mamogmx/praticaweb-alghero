<?php
include_once "../login.php";
error_reporting(E_ERROR);
$db=  appUtils::getDB();

$result=Array();
$action=(isset($_REQUEST["action"]) && $_REQUEST["action"])?($_REQUEST["action"]):("");
switch($action) {
	case "nuovi_oneri":
        $data_inizio=$_REQUEST["inizio"];
        $sqlData="SELECT code,zona,c_1,c_2,c_3,c_4,c_5,c_6,c_7,c_8,c_9,c_10,c_11,c_12,c_13,c_14,c_15,c_16,c_17,c_18,c_19,c_20,'$data_inizio'::date as inizio_validita FROM oneri.tabella_b WHERE inizio_validita = (SELECT max(inizio_validita) from oneri.tabella_b)";
        $data=$db->fetchAll($sqlData);
        foreach($data as $v) $db->insert("oneri.tabella_b",$v);
        
        break;
    case "checkModelli":
        $value=(isset($_REQUEST["id"]) && $_REQUEST["id"])?($_REQUEST["id"]):("%");
        $sql="SELECT id,nome FROM stp.e_modelli WHERE id::varchar ilike ?";
        $res=$db->fetchAll($sql,Array($value),0);
        for($i=0;$i<count($res);$i++){
            
            $nome=$res[$i]["nome"];
            $text=appUtils::unzip(MODELLI, $nome);
            $err=appUtils::getDocxErrors($text);
            $result[$res[$i]["id"]]=$err;
        }
        
        break;
    default:
        break;
}

print json_encode($result);
return;
?>