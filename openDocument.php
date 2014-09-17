<?php
require_once "login.php";
$id=$_REQUEST["id"];
$pratica=$_REQUEST["pratica"];
if ($pratica!="null" && $pratica){
    $db=appUtils::getDB();
    $sql="SELECT file_doc FROM stp.stampe WHERE id=?";
    $fName=$db->fetchColumn($sql, array($id));
    $pr=new pratica($pratica);
    $url=$pr->smb_documenti.$fName;
	print_debug($url."\n",NULL,"documento");
}
else{
    $db=appUtils::getDB();
    $sql="SELECT nome FROM stp.e_modelli WHERE id=?";
    $fName=$db->fetchColumn($sql, array($id));
    $url=SMB_MODELLI.$fName;
}
//if (file_exists(DATA_DIR.implode(DIRECTORY_SEPARATOR,Array("praticaweb",'documenti')).DIRECTORY_SEPARATOR)) {
//    echo $url;exit;
//}

header("Content-type: application/vnd.ms-word");
@header("Location: $url") ;

?>
