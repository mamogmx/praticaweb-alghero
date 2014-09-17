<?
include_once "./login.php";

$usr=$_SESSION['USER_NAME'];
$idpratica=$_REQUEST["pratica"];
$form=$_POST["form"];
$modello=$_POST["modello"];
$file=$_POST["file"];
$azione=$_POST["azione"];
$procedimento=$_POST["procedimento"];
$id_modello=$_POST["id"];
list($schema_iter,$nomeform)=explode(".",$form);
if ($schema_iter=="oneri" || $schema_iter=="vigi") $schema_iter="pe";	//Redirigo gli schemi collegati a PE
$db = new sql_db(DB_HOST,DB_USER,DB_PWD,DB_NAME, false);
if(!$db->db_connect_id)  die( "Impossibile connettersi al database ".DB_NAME);
$db=appUtils::getDB();

if ($_POST["azione"]==="Crea Documento") {	//	Creo un nuovo documento
    $r=$db->fetchAssoc("SELECT count(*) as duplicated,coalesce(Y.multiple,0) as multiple FROM stp.stampe X INNER JOIN stp.e_modelli Y ON(X.modello=Y.id) WHERE X.pratica=? and modello=? group by 2",Array($idpratica,$id_modello),0);
    
    list($duplicated,$multiple)=  array_values($r);
    if(!$duplicated || $multiple){
        include_once "lib/stampe.word.class.php";
        list($sc,$f)=explode(".",$form);
        $schema="stp";
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
        /*sql="INSERT INTO stp.stampe(pratica,modello,file_doc,file_pdf,form,utente_doc,utente_pdf,data_creazione_doc,data_creazione_pdf) VALUES($idpratica,$id_modello,$doc->docName,$doc->docName,'$form','$usr','$usr',now(),now())";
        if(!$db->sql_query($sql)) print_debug($sql);
        $lastid=$db->sql_nextid();*/
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
        $db->insert($schema_iter.'.iter',$data);
        /*$sql="INSERT INTO $schema_iter.iter(pratica,data,utente,nota,nota_edit,uidins,tmsins,stampe,immagine) VALUES($idpratica,'$today','$usr','$testoview','$testoedit',".$_SESSION["USER_ID"].",".time().",$lastid,'laserjet.gif');";
        $db->sql_query($sql);*/
        //Azioni da eseguire sulla stampa dei documenti
        $arrAct=explode(',',$doc->actions);
        $info=appUtils::getInfoPratica($idpratica);
        for($i=0;$i<count($arrAct);$i++){
            switch($arrAct[$i]){
                case "fd":
                    appUtils::addTransition($idpratica,Array("codice"=>$arrAct[$i],"utente_fi"=>$info['dirigente'],"utente_in"=>$info['resp_proc'],"data"=>"now()","note"=>$doc->basename));
                default:
                    break;
            }

        }
    }
}
$active_form=($is_cdu)?("cdu.iter?pratica=$idpratica"):("pe.iter?pratica=$idpratica&tipo=pratica");

?>
