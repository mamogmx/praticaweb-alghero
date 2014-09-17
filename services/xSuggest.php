<?php
include_once "../login.php";
error_reporting(E_ERROR);
$db=$dbconn;
//$db=new sql_db(DB_HOST,DB_USER,DB_PWD,DB_NAME, false);
//if(!$db->db_connect_id)  die( "Impossibile connettersi al database");
$result=Array();
$field=$_REQUEST['field'];
$value=addslashes($_REQUEST['term']);
switch($field) {
	case 'tavola':
		$sql="SELECT nome_tavola as id,coalesce(descrizione,nome_tavola) as opzione FROM vincoli.tavola WHERE nome_vincolo='$value' or nome_vincolo is null order by ordine,2;";
		if($db->sql_query($sql)){
			$result=Array(Array("id"=>null,"opzione"=>'Seleziona =====>'));
			$res=$db->sql_fetchrowset();//print_array($res);
            for($i=0;$i<count($res);$i++){
				$result[]=Array("id"=>$res[$i]["id"],"opzione"=>$res[$i]["opzione"]);
			}
			$exec=1;
		}
		else
            $result[]=Array(
                "id"=>'',
                "value"=>'',
                "label"=>"Si è verificato un errore nell' esecuzione dell'interrogazione $sql"
                
            );
		break;
	case 'zona':
		$vincolo=addslashes($_REQUEST['vincolo']);

		$sql="SELECT nome_zona as id,coalesce(sigla || ' - ','') || coalesce(descrizione,nome_zona) as opzione FROM vincoli.zona WHERE nome_tavola='$value' and nome_vincolo='$vincolo' order by ordine,2;";
		if($db->sql_query($sql)){
			$res=$db->sql_fetchrowset();//print_array($res);
			$result=Array(Array("id"=>null,"opzione"=>'Seleziona =====>'));
            for($i=0;$i<count($res);$i++){
				$result[]=Array("id"=>$res[$i]["id"],"opzione"=>$res[$i]["opzione"]);
			}
			$exec=1;
		}
		else
            $result[]=Array(
                "id"=>'',
                "value"=>'',
                "label"=>"Si è verificato un errore nell' esecuzione dell'interrogazione $sql"
                
            );
		break;
	case 'motivo':
		$motivo=addslashes($_REQUEST['motivo']);
		switch($motivo){
			case "2":
				$sql="select * from admin.elenco_istruttori_tecnici where id=(SELECT resp_it FROM pe.avvioproc where pratica=); ";
				break;
			default:
				$sql="SELECT * FROM admin.elenco_istruttori;";
				
				break;
		}
		if($db->sql_query($sql)){
			$res=$db->sql_fetchrowset();//print_array($res);
			$result=Array(Array("id"=>null,"opzione"=>'Seleziona =====>'));
            for($i=0;$i<count($res);$i++){
				$result[]=Array("id"=>$res[$i]["id"],"opzione"=>$res[$i]["opzione"]);
			}
			$exec=1;
		}
		else
            $result[]=Array(
                "id"=>'',
                "value"=>'',
                "label"=>"Si è verificato un errore nell' esecuzione dell'interrogazione $sql"
                
            );
		break;
    case 'codfis':
        require_once("../calcolacodicefiscale.php");
		$cognome=$_REQUEST['cognome'];
		$nome=$_REQUEST['nome'];
		$sesso=$_REQUEST['sesso'];
		$comune=addslashes($_REQUEST['comunato']);
		$datanascita=$_REQUEST['datanato'];
        $sql="SELECT codice FROM pe.e_comuni WHERE nome ilike '$comune' order by nome";
        if($db->sql_query($sql)){
            //$comune=$db->sql_fetchfield('codice');
            $codfis='';
            $r=new risultato;
            $r=calcolacodicefiscale($cognome,$nome,$sesso,$comune,$datanascita);
            if (sizeof($r->errori)){
                $errors= "Si sono verificati i seguenti errori:";
                reset ($r->errori);
                while (list ($key, $val) = each ($r->errori)) {
                    $errors.= ($key+1)." - $val;\n";
                }
            } 
            else{
                $codfis= $r->codicefiscale;
            }
            $result=Array('value'=>$codfis,'error'=>$errors);
        }
        break;
    case 'comune':
    case 'comuned':
    case 'citta':
    case 'comunato':
        $sql="SELECT * FROM pe.e_comuni WHERE nome ilike '$value%' order by nome";
        $child=Array(
            'comune'=>Array('cap'=>'cap','prov'=>'sigla_prov'),
            'comunato'=>Array('provnato'=>'sigla_prov'),
            'comuned'=>Array('capd'=>'cap','provd'=>'sigla_prov'),
            'citta'=>Array('cap'=>'cap','sigla_prov'=>'prov')
        );
        if($db->sql_query($sql)){
            
            $res=$db->sql_fetchrowset();//print_array($res);
            for($i=0;$i<count($res);$i++){
                $r=Array();
                foreach($child[$field] as $k=>$v) $r[$k]=$res[$i][$v];
                $result[]=Array(
                    "id"=>$res[$i]["codice"],
                    "value"=>$res[$i]["nome"],
                    "label"=>$res[$i]["nome"]." (".$res[$i]["sigla_prov"].") - ".$res[$i]["cap"],
                    "child"=>$r
                );
                
            }
            $exec=1;
        }
        else
            $result[]=Array(
                "id"=>'',
                "value"=>'',
                "label"=>"Si è verificato un errore nell' esecuzione dell'interrogazione"
                
            );
        break;
    case "foglio":
        $sql="SELECT DISTINCT foglio as valore FROM nct.particelle WHERE foglio ilike '%$value%' order by 1";
        if($db->sql_query($sql)){
            $res=$db->sql_fetchrowset();
            for($i=0;$i<count($res);$i++){
                $result[]=Array(
                    "id"=>$res[$i]["valore"],
                    "value"=>$res[$i]["valore"],
                    "label"=>$res[$i]["valore"]
                );
                
            }
            $exec=1;
        }
        break;
    case 'mappale':
        $fg=(isset($_REQUEST['foglio']))?(addslashes($_REQUEST['foglio'])):('%');
        $sql="SELECT DISTINCT mappale as valore,substring(mappale from '\\\\d+') FROM nct.particelle WHERE mappale ilike '$value%' and foglio ilike '$fg' order by substring(mappale from '\\\\d+');";

        if($db->sql_query($sql)){
            $res=$db->sql_fetchrowset();
            for($i=0;$i<count($res);$i++){
                $result[]=Array(
                    "id"=>$res[$i]["valore"],
                    "value"=>$res[$i]["valore"],
                    "label"=>$res[$i]["valore"]
                );
            }
            $exec=1;
        }
        else
            $result[]=Array(
                "id"=>'',
                "value"=>'',
                "label"=>"Si è verificato un errore nell' esecuzione dell'interrogazione $sql;"
                
            );
        break;
    case 'via':
        $sql="SELECT DISTINCT nomestrada as valore FROM dbt_topociv.v_geocivico WHERE nomestrada ilike '%$value%' order by 1";
        if($db->sql_query($sql)){
            $res=$db->sql_fetchrowset();
            for($i=0;$i<count($res);$i++){
                $result[]=Array(
                    "id"=>$res[$i]["valore"],
                    "value"=>$res[$i]["valore"],
                    "label"=>$res[$i]["valore"]
                );
                
            }
            $exec=1;
        }
        else
            $result[]=Array(
                "id"=>'',
                "value"=>'',
                "label"=>"Si è verificato un errore nell' esecuzione dell'interrogazione"
                
            );
        break;
    case 'civico':
        $strada=(isset($_REQUEST['via']))?(addslashes($_REQUEST['via'])):('%');
        $sql="SELECT DISTINCT etichetta as valore FROM dbt_topociv.v_geocivico WHERE etichetta ilike '$value%' and nomestrada ilike '$strada' order by 1";
        if($db->sql_query($sql)){
            $res=$db->sql_fetchrowset();
            for($i=0;$i<count($res);$i++){
                $result[]=Array(
                    "id"=>$res[$i]["valore"],
                    "value"=>$res[$i]["valore"],
                    "label"=>$res[$i]["valore"]
                );
            }
            $exec=1;
        }
        else
            $result[]=Array(
                "id"=>'',
                "value"=>'',
                "label"=>"Si è verificato un errore nell' esecuzione dell'interrogazione $sql;"
                
            );
        break;
    case "titolo":
    case "titolod":
        $tabella='pe.soggetti';
        break;
    case 'parere':
            $tabella="pe.pareri";
            break;
    case 'notaio':
            $tabella="pe.asservimenti";
            break;
    case 'destuso1':
    case 'destuso2':
            $field="destuso";
            $tabella='pe.e_destuso';
            break;
    case 'motivo':
            $tabella="pe.sopralluoghi";
            break;
    case 'motivo_v':
            $tabella="vigi.sopralluoghi";
            break;
    case 'origine':
            $tabella="vigi.esposti";
            break;
    case 'intervento':
            $tabella="pe.e_intervento";
            break;	
    case 'nota':
            $tabella="pe.e_voci_iter";
            break;			
    case 'sede1':
            $tabella="ce.commissione";
            break;
}
if (!$result){
    $sql="select distinct $field as valore from $tabella where $field ilike '$value%' order by 1;";
    if($db->sql_query($sql)){
        $res=$db->sql_fetchrowset();
        for($i=0;$i<count($res);$i++){
            $result[]=Array(
                "id"=>$res[$i]["valore"],
                "value"=>$res[$i]["valore"],
                "label"=>$res[$i]["valore"]
            );
            
        }
    }
    elseif(isset($exec))
        passthru;
    else
        $result[]=Array(
            "id"=>'',
            "value"=>'',
            "label"=>"Si è verificato un errore nell' esecuzione dell'interrogazione"
            
        );
}

print json_encode($result);
return;
?>
