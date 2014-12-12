<?php
include_once "../login.php";
$usr=$_SESSION['USER_NAME'];
$action = $_REQUEST["action"];
$prms = $_REQUEST["params"];
$result = Array("success" =>-1,"data"=>Array(),"message"=>"");
$db = appUtils::getDB();
switch($action){
    case "search":
        $tipo=$prms["tipo"];
        list($anno_in,$numero_in)=explode("/",preg_replace("/[^0-9\/]/", "", $prms["numero_in"]));
        list($anno_fi,$numero_fi)=explode("/",preg_replace("/[^0-9\/]/", "", $prms["numero_fi"]));
        $sql = <<<EOT
SELECT
    pratica,numero as numero_pratica,data_presentazione,oggetto,
    regexp_replace(split_part(numero,'/',1),'[^0-9]+','')::integer as anno,regexp_replace(split_part(numero,'/',2),'[^0-9]+','')::integer as numero 
FROM
    pe.avvioproc 
WHERE 
    tipo = %s 
AND
(
    (regexp_replace(split_part(numero,'/',1),'[^0-9]+','')::integer = %s AND regexp_replace(split_part(numero,'/',2),'[^0-9]+','')::integer >= %s ) 
OR 
    (regexp_replace(split_part(numero,'/',1),'[^0-9]+','')::integer = %s AND regexp_replace(split_part(numero,'/',2),'[^0-9]+','')::integer <= %s )
) 
ORDER by 5,6
EOT;
        $sql = sprintf($sql,$tipo,$anno_in,$numero_in,$anno_fi,$numero_fi);
        $res=$db->fetchAll($sql);
        $result["data"]=$res;
        $result["success"]=1;
        break;
    case "print":
        $id_modello = $_REQUEST["model"];
        foreach($prms as $idpratica){
            $r=$db->fetchAssoc("SELECT count(*) as duplicated,coalesce(Y.multiple,0) as multiple FROM stp.stampe X INNER JOIN stp.e_modelli Y ON(X.modello=Y.id) WHERE X.pratica=? and modello=? group by 2",Array($idpratica,$id_modello),0);
            list($duplicated,$multiple)=  array_values($r);
            if(!$duplicated || $multiple){
                include_once "../lib/stampe.word.class.php";
                $doc=new wordDoc($id_modello,$idpratica);
                $doc->createDoc();
                $data=Array(
                    'pratica'=>$idpratica,
                    'modello'=>$id_modello,
                    'file_doc'=>$doc->docName,
                    'file_pdf'=>$doc->docName,
                    'form'=>$form,
                    'utente_doc'=>$usr,
                    'utente_pdf'=>$usr,
                    'data_creazione_doc'=>'NOW',
                    'data_creazione_pdf'=>'NOW'
                );
                $db->insert('stp.stampe',$data);
                $lastid=appUtils::getLastId($db,'stp.stampe');
                $edit="<img src=\"images/word.gif\" border=0 >&nbsp;&nbsp;<a target=\"documenti\" href=\"./openDocument.php?id=$lastid&pratica=$idpratica\" >$doc->basename</a>";
                $view="Creato il Documento ".$doc->basename;
                $data=Array(
                    'pratica'=>$idpratica,
                    'data'=>'NOW',
                    'utente'=>$usr,
                    'nota'=>$view,
                    'uidins'=>$_SESSION["USER_ID"],
                    'tmsins'=>time(),
                    'nota_edit'=>$edit,
                    'stampe'=>$lastid,
                    'immagine'=>'word.png'
                );
                $db->insert('pe.iter',$data);
                
                $result["data"][]=Array("id"=>$lastid,"pratica"=>$idpratica,"name"=>$doc->basename,"success"=>1);
            }
            else if($duplicated){
                $r=$db->fetchAssoc("SELECT id,data_creazione_doc FROM stp.stampe pratica=? and modello=? order by tmsins DESC LIMIT 1",Array($idpratica,$id_modello),0);
                list($id,$data)=  array_values($r);
                $result["data"][]=Array("id"=>$id,"pratica"=>$idpratica,"data"=>$data,"success"=>0);
            }
            $result["success"]=1;
        }
        break;
}
header('Content-Type: application/json');
print json_encode($result);
?>