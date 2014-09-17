<?php

/**
 * Description of pratica
 *
 * @author marco carbone
 */
use Doctrine\Common\ClassLoader;
require_once APPS_DIR.'plugins/Doctrine/Common/ClassLoader.php';
class pratica {
    var $pratica;
	var $tipopratica=null;
    var $info=Array();
    var $allegati;
    var $url_allegati;
    var $documenti;
    var $url_documenti;
    var $user;
	var $cm_mq=37.7; //Valore Corrispettivo monetario €/mq
	var $next;
	var $prev;
    var $db;
    
    function __construct($id,$type=0){
		
		$this->pratica=$id;
		$db = new sql_db(DB_HOST,DB_USER,DB_PWD,DB_NAME, false);
		if(!$db->db_connect_id)  die( "Impossibile connettersi al database ".DB_NAME);
		$this->db=$db;
		$this->db1=$this->setDB();
		switch($type){
			case 1:
				$this->initCdu();
				break;
			default:
				$this->initPratica();
				break;
		}
		
    }
    function __destruct(){
        $this->db1->close();
    }
	
	private function initPratica(){
		$db=$this->db1;
		if ($this->pratica && is_numeric($this->pratica)){
			//INFORMAZIONI SULLA PRATICA
			$sql="SELECT numero,tipo,resp_proc,resp_it,resp_ia,date_part('year',data_presentazione) as anno,data_presentazione,data_prot FROM pe.avvioproc  WHERE pratica=?";
			$r=$db->fetchAssoc($sql, Array($this->pratica));
			$this->info=$r;
			if($this->info['tipo'] < 10000 || in_array($this->info['tipo'],Array(14000,15000))){
				$this->tipopratica='pratica';
			}
			elseif($this->info['tipo'] < 13000){
				$this->tipopratica='dia';
			}
			else{
				$this->tipopratica='ambientale';
			}

			$numero=appUtils::normalizeNumero($this->info['numero']);
			$tmp=explode('-',$numero);
			if (count($tmp)==2 && preg_match("|([A-z0-9]+)|",$tmp[0])){
				$tmp[0]=(preg_match("|^[89]|",$tmp[0]))?("19".$tmp[0]):($tmp[0]);
				$numero=implode('-',$tmp);
			}
			$anno=($r['anno'])?($r['anno']):($tmp[0]);

			//Struttura delle directory
			$arrDir=Array(DATA_DIR,'praticaweb','documenti',$anno);
			$this->annodir=implode(DIRECTORY_SEPARATOR,$arrDir).DIRECTORY_SEPARATOR;
			$arrDir[]=$numero;
			$this->documenti=implode(DIRECTORY_SEPARATOR,$arrDir).DIRECTORY_SEPARATOR;
			$arrDir[]="allegati";
			$this->allegati=implode(DIRECTORY_SEPARATOR,$arrDir).DIRECTORY_SEPARATOR;
			$arrDir[]="tmb";
			$this->allegati_tmb=implode(DIRECTORY_SEPARATOR,$arrDir).DIRECTORY_SEPARATOR;

			$this->url_documenti="/documenti/$anno/$numero/";
			$this->url_allegati="/documenti/$anno/$numero/allegati/";
			$this->smb_documenti=SMB_PATH."$anno/$numero/";
            
			$this->createStructure();
			//INFO PRATICA PREC E SUCC
			$sql="SELECT max(pratica) as pratica FROM pe.avvioproc WHERE pratica < ?";
			$this->prev=$db->fetchColumn($sql,Array($this->pratica));
			$sql="SELECT min(pratica) as pratica FROM pe.avvioproc WHERE pratica > ?";
			$this->next=$db->fetchColumn($sql,Array($this->pratica));
		}

		//ESTRAGGO INFORMAZIONI SUL DIRIGENTE
		$sql="SELECT userid as dirigente FROM admin.users WHERE attivato=1 and '13' = ANY(string_to_array(coalesce(gruppi,''),','));";
		$dirig=$db->fetchColumn($sql);
		$this->info['dirigente']=$dirig;
		//ESTRAGGO INFORMAZIONI SUL RESPONSABILE DEL SERVIZIO
		$sql="SELECT userid as rds FROM admin.users WHERE attivato=1 and '15' = ANY(string_to_array(coalesce(gruppi,''),','));";
		$rds=$db->fetchColumn($sql);
		$this->info['rds']=$rds;
		//INFO UTENTE (ID-GRUPPI-NOME)
		$this->userid=$_SESSION['USER_ID'];
		$this->usergroups=$_SESSION['GROUPS'];
		$sql="SELECT username FROM admin.users WHERE userid=?";
		$this->user=$db->fetchColumn($sql,Array($this->userid));
				
	}
	
	private function initCdu(){
		$db=$this->db1;
		$this->tipopratica='cdu';
		if($this->pratica){
			$sql="select protocollo,date_part('year',data) as anno FROM cdu.richiesta WHERE pratica=?";
			$r=$db->fetchAssoc($sql,Array($this->pratica));
			$this->info=$r;
			extract($r);
			$arrDir=Array(DATA_DIR,'praticaweb','documenti','cdu',$anno);
			$this->annodir=implode(DIRECTORY_SEPARATOR,$arrDir).DIRECTORY_SEPARATOR;
			$arrDir[]=$protocollo;
			$this->documenti=implode(DIRECTORY_SEPARATOR,$arrDir).DIRECTORY_SEPARATOR;
			$this->url_documenti="/documenti/cdu/$anno/$protocollo/";
		}
	}
	
	
		function createStructure(){
		if($this->pratica){
			if(!file_exists($this->annodir)) {
				mkdir($this->annodir);
				chmod($this->annodir,0777);
				print (!file_exists($this->annodir))?("Errore nella creazione della cartella $this->annodir\n"):("Cartella $this->annodir creata con successo\n");
			}
			if(!file_exists($this->documenti)) {
				mkdir($this->documenti);
				chmod($this->documenti,0777);
				//print (!file_exists($this->documenti))?("Errore nella creazione della cartella $this->documenti\n"):("Cartella $this->documenti creata con successo\n");
			}
			if($this->allegati && !file_exists($this->allegati)) {
				mkdir($this->allegati);
				chmod($this->allegati,0777);
				//print (!file_exists($this->allegati))?("Errore nella creazione della cartella $this->allegati\n"):("Cartella $this->allegati creata con successo\n");
			}
			if($this->allegati_tmb && !file_exists($this->allegati_tmb)){
				mkdir($this->allegati_tmb);
				chmod($this->allegati_tmb,0777);
				//print (!file_exists($this->allegati_tmb))?("Errore nella creazione della cartella $this->allegati_tmb\n"):("Cartella $this->allegati_tmb creata con successo\n");

			}
		}
	}
	
	//Cancellazione della Pratica
    static function delete($id){
        $db=pratica::setDB();
        //$sql="DELETE FROM pe.avvioproc WHERE pratica=$id;";
        if($db->delete('pe.avvioproc',array($id))){
            system("rm -rf ".$this->documenti); 
        }
    }
	
	function removeStructure(){
		rmdir($this->allegati_tmb);
		rmdir($this->allegati);
		rmdir($this->documenti);
	}
	
	function nuovaPratica($arrInfo){
		//Creazione Struttura nuova Pratica
		$this->createStructure();
		if(in_array($this->tipopratica,Array("ambientale","dia","pratica"))){
			$this->setAllegati();
			//Array('codice'=>null,'utente_in'=>$this->userid,'utente_fi'=>null,'data'=>"now",'stato_in'=>null,'stato_fi'=>null,'note'=>null,'tmsins'=>time(),'uidins'=>$this->userid);
			$this->addTransition(Array('codice'=>'ardp',"utente_fi"=>$this->info["resp_proc"],"data"=>$arrInfo["data_resp"]));
			$this->addTransition(Array('codice'=>'aipre',"utente_fi"=>$this->userid));
			if ($this->info["resp_it"]) $this->addTransition(Array('codice'=>'aitec',"utente_fi"=>$this->info["resp_it"],"data"=>$arrInfo["data_resp_it"]));
			if ($this->info["resp_ia"]) $this->addTransition(Array('codice'=>'aiamm',"utente_fi"=>$this->info["resp_ia"],"data"=>$arrInfo["data_resp_ia"]));
		}
		
	}
	private function setAllegati($list=Array()){
		if(!$list){
			$db=$this->db1;
			$ris=$db->fetchAll("select $this->pratica as pratica,id as documento,1 as allegato,$this->userid as uidins,".time()." as tmsins from pe.e_documenti where default_ins=1");
			for ($i=0;$i<count($ris);$i++) $db->insert("pe.allegati",$ris[$i]);
		}
	}
	function addRecenti(){
		if (!is_numeric($this->pratica)) return;
		$db=$this->db1;
		$pr=$db->fetchColumn("select coalesce(pratica,0) from pe.recenti where utente=? and pratica=?",Array($this->userid,$this->pratica));
		if($pr){
			$db->update("pe.recenti",Array("data"=>time()),Array("utente"=>$this->userid,"pratica"=>$this->pratica));
		}
		else{
			$tot=$db->fetchColumn("select count(*) from pe.recenti where utente=?",Array($this->userid));
			if((int)$tot > 10){
				$d=$db->fetchColumn("SELECT min(data) FROM pe.recenti WHERE utente=?",Array($this->userid));
				$db->delete("pe.recenti",Array("utente"=>$this->userid,"data"=>$d));
			}
			$db->insert("pe.recenti",Array("pratica"=>$this->pratica,"data"=>time(),"utente"=>$this->userid));
		}
	}
	
	//Aggiunge Un record all'iter
    function addIter($testoview,$testoedit){
        $db=$this->db;
        $usr=$_SESSION['USER_NAME'];
        
        $today=date('j-m-y'); 
		$sql="INSERT INTO pe.iter(pratica,data,utente,nota,nota_edit,uidins,tmsins,stampe,immagine) VALUES($this->pratica,'$today','$usr','$testoview','$testoedit',$this->userid,".time().",null,'laserjet.gif');";
		$db->sql_query($sql);
    }
	
	function setDateLavori($data){
		$db=$this->db;	
		$sql="select id from pe.lavori where pratica=$this->pratica";
		$db->sql_query($sql);
		$res=$db->sql_fetchrow();
		// se ho giÃƒÂ  il record esco
		
		if(!$res){
			$sql="SELECT tipo FROM pe.avvioproc WHERE pratica=$this->pratica;";
			$db->sql_query($sql);
			$tipo=$db->sql_fetchfield('tipo');
			switch($tipo){
				case "2000":
				case "2050":
                case "2070":
				case "2100":
				case "2150":
				case "2170":
                case "2180":
                case "2190":
					$sql="insert into pe.lavori (pratica,scade_il,scade_fl,uidins,tmsins) values ($this->pratica,'$data'::date + INTERVAL '1 year', '$data'::date + INTERVAL '3 year',".$_SESSION["USER_ID"].",".time().");";
		
					$db->sql_query($sql);
					//INSERIMENTO SCADENZE RATE ONERI URBANIZZAZIONE E CORRISPETTIVO MONETARIO
					//$db->sql_query($sql);
					break;
				case "10000":
				case "10100":
					$sql="insert into pe.lavori (pratica,scade_il,scade_fl,uidins,tmsins) values ($this->pratica,('$data'::date + INTERVAL '1 year 30 day')::date, ('$data'::date + INTERVAL '3 year 30 day')::date,".$_SESSION["USER_ID"].",".time().");";
					$db->sql_query($sql);
					//INSERIMENTO SCADENZE RATE ONERI URBANIZZAZIONE E CORRISPETTIVO MONETARIO
					//$this->setDateRateCM($data);
					//$this->setDateRateOC($data);
					break;
				default:
					break;
			}
			//INSERIMENTO SCADENZE DATE INIZIO E FINE LAVORI
			
			
			
		}
	}
	
/*********************************************************************************************************/	
/*------------------------------------     TITOLO         -----------------------------------------------*/
/*********************************************************************************************************/
	
	function nuovoTitolo($data){
        return;
		$db=$this->db;
		$sql="SELECT tipo FROM pe.avvioproc WHERE pratica=$this->pratica;";
		$db->sql_query($sql);
		$tipo=$db->sql_fetchfield('tipo');
		switch($tipo){
			case "2000":
			case "2050":
				$tipiPratica="(2000,2050)";
				break;
			case "2100":
			case "2150":
			case "2170":
            case "2180":
            case "2190":
				$tipiPratica="(2100,2150,2170,2180,2190)";
				break;
//Modifica 21/06/2012			
			case "13000":
			case "13100":
				$tipiPratica="(13000,13100)";
				break;
//Fine Modifica			
			default:
				return;
				break;
		}
		$sql="select 
coalesce(max(a.numero),0)+1 as numero,date_part('year','$data'::date) as anno
from pe.titolo a inner join pe.avvioproc b using(pratica) 
where 
tipo in $tipiPratica and date_part('year',data_rilascio)=date_part('year','$data'::date);";
		if($db->sql_query($sql)){
			$anno=$db->sql_fetchfield('anno');
			$num=$db->sql_fetchfield('numero');
			$sql="UPDATE pe.titolo SET numero=$num,titolo='$num/$anno' WHERE pratica=$this->pratica;";
			$db->sql_query($sql);
		}
	}
	function removeTitolo(){
		$db=$this->db1;
		$db->delete("pe.lavori",Array("pratica"=>$this->pratica));
		$db->update("oneri.rate",Array("data_scadenza"=>null),Array("pratica"=>$this->pratica));
	}
	
/*********************************************************************************************************/	
/*------------------------------------     ONERI          -----------------------------------------------*/
/*********************************************************************************************************/
	
	//Calcolo Corrispettivo Monetario
	function setCM(){
		$db=$this->db;
		$sql="UPDATE oneri.c_monetario SET totale_noscomputo = round(coalesce(sup_cessione*$this->cm_mq,0),2),totale = round(coalesce(sup_cessione*$this->cm_mq,0),2)-coalesce(scomputo,0) WHERE pratica=$this->pratica;";

		$db->sql_query($sql);
	}
	
	//Calcolo rate Corrispettivo Monetario
	function setRateCM(){
		$db=$this->db;
        $t=time();
		$sql="DELETE FROM oneri.rate WHERE pratica=$this->pratica and rata in (5,6);
INSERT INTO oneri.rate(pratica,rata,totale,uidins,tmsins) (
(SELECT $this->pratica as pratica,5 as rata,(totale*0.5),$this->userid,$t FROM oneri.c_monetario WHERE pratica=$this->pratica)
UNION
(SELECT $this->pratica as pratica,6 as rata,(totale*0.5),$this->userid,$t FROM oneri.c_monetario WHERE pratica=$this->pratica));";
		$db->sql_query($sql);
		
		
		
		$menu=new Menu('pratica','pe');
		$menu->add_menu($this->pratica,'120');
        $menu->add_menu($this->pratica,'130');
		
	}
	//Calcolo date scadenza rate CM
	function setDateRateCM($data){
		if($data){
			$db=$this->db;
			$sql="UPDATE oneri.rate SET data_scadenza='$data'::date WHERE pratica=$this->pratica  and rata=5;";
			$sql.="UPDATE oneri.rate SET data_scadenza='$data'::date + INTERVAL '1 year' WHERE pratica=$this->pratica  and rata=6;";
			$db->sql_query($sql);
		}
	}
	//Calcolo della Fideiussione CM
	function setFidiCM(){
		$db=$this->db;
		$sql="UPDATE oneri.c_monetario SET fideiussione=(SELECT totale-coalesce(versato,0) from oneri.rate where pratica=$this->pratica and rata=6) WHERE pratica=$this->pratica;";
		//echo $sql;        
		$db->sql_query($sql);
	}
	//Calcolo Totale Oneri Costruzione
	function setOC(){
		$db=$this->db;
		$sql="UPDATE oneri.oneri_concessori SET totale = coalesce(oneri_urbanizzazione,0) + coalesce(oneri_costruzione,0)-(coalesce(scomputo_urb,0)+coalesce(scomputo_costr,0)) WHERE pratica=$this->pratica;";
		$db->sql_query($sql);
	}
	
	//Calcolo Rate Oneri Costruzione
    function setRateOC($rateizzato=1){
		$this->setOC();
        $db=$this->db;
        $t=time();
		if($rateizzato==1)	// <---- MODIFICA DEL 21/06/2012
			$sql="DELETE FROM oneri.rate WHERE pratica=$this->pratica and rata in (1,2,3,4);
INSERT INTO oneri.rate(pratica,rata,totale,uidins,tmsins) (
(SELECT $this->pratica as pratica,1 as rata,((coalesce(oneri_urbanizzazione,0)-coalesce(scomputo_urb,0))*0.5+(coalesce(oneri_costruzione,0)-coalesce(scomputo_costr,0))*0.3),$this->userid,$t FROM oneri.oneri_concessori WHERE pratica=$this->pratica)
UNION
(SELECT $this->pratica as pratica,2 as rata,((coalesce(oneri_urbanizzazione,0)-coalesce(scomputo_urb,0))*0.25+(coalesce(oneri_costruzione,0)-coalesce(scomputo_costr,0))*0.3),$this->userid,$t FROM oneri.oneri_concessori WHERE pratica=$this->pratica)
UNION
(SELECT $this->pratica as pratica,3 as rata,((coalesce(oneri_urbanizzazione,0)-coalesce(scomputo_urb,0))*0.25+(coalesce(oneri_costruzione,0)-coalesce(scomputo_costr,0))*0.4),$this->userid,$t FROM oneri.oneri_concessori WHERE pratica=$this->pratica)
);";
		else
			$sql="DELETE FROM oneri.rate WHERE pratica=$this->pratica and rata in (1,2,3,4);
INSERT INTO oneri.rate(pratica,rata,totale,uidins,tmsins) (SELECT $this->pratica as pratica,4 as rata,((coalesce(oneri_urbanizzazione,0)-coalesce(scomputo_urb,0))+(coalesce(oneri_costruzione,0)-coalesce(scomputo_costr,0))),$this->userid,$t FROM oneri.oneri_concessori WHERE pratica=$this->pratica);";
        $db->sql_query($sql);
		
		$menu=new Menu('pratica','pe');
		$menu->add_menu($this->pratica,'120');
        $menu->add_menu($this->pratica,'130');
    }
	//Calcolo date scadenza rate OC
	function setDateRateOC($data){
		$db=$this->db;
		if($data){
			$sql="UPDATE oneri.rate SET data_scadenza='$data'::date WHERE pratica=$this->pratica and rata=1;";
			$sql.="UPDATE oneri.rate SET data_scadenza='$data'::date + INTERVAL '1 year' WHERE pratica=$this->pratica and rata=2;";
			$sql.="UPDATE oneri.rate SET data_scadenza='$data'::date + INTERVAL '3 year' WHERE pratica=$this->pratica and rata=3;";
			$db->sql_query($sql);
		}
	}
	//Calcolo della Fideiussione OC
	function setFidiOC(){
		$db=$this->db;
		$sql="UPDATE oneri.oneri_concessori SET fideiussione=coalesce((SELECT sum(totale-coalesce(versato,0)) FROM oneri.rate WHERE rata in (2,3) and pratica=$this->pratica),0) WHERE pratica=$this->pratica;";
        $db->sql_query($sql);
	}
	
/*********************************************************************************************************/	
/*------------------------------------     WORKFLOW       -----------------------------------------------*/
/*********************************************************************************************************/	
	
	function setMansione($m,$usr,$d,$fr = NULL,$note = ''){
		$from=($fr)?($fr):($_SESSION['USER_ID']);
		$d=($d)?(($d=='CURRENT_DATE')?($d):("'$d'::date")):("null");
		$db=$this->db;	
		$sql="INSERT INTO pe.movimenti_pratiche(pratica,da_utente,a_utente,data,motivo,note,uidins,tmsins) VALUES($this->pratica,$from,$usr,$d,(select id from pe.e_statipratica where codice='$m'),'$note',$from,".time().");";
		$db->sql_query($sql);
	}
	function removeMansione($m){
		if($m){
			$db=$this->db;	
			$sql="DELETE FROM pe.movimenti_pratiche WHERE pratica=$this->pratica and motivo = (select id from pe.e_statipratica where codice='$m')";
			$db->sql_query($sql);
		}
	}
	
	/* WORKFLOW Da Mettere*/
	
	function addRole($role,$usr,$d){
		$t=time();
		$db=$this->db1;
		$data=($d)?(($d=='CURRENT_DATE')?('now'):($d)):('now');
		$arrDati=Array(
			'pratica'=>$this->pratica,
			'role'=>$role,
			'utente'=>$usr,
			'data'=>$data,
			'tmsins'=>$t,
			'uidins'=>$this->userid
		);
		$db->insert('pe.wf_roles', $arrDati);
	}
	function delRole($role){
		$db=$this->db1;
		$db->delete('pe.wf_roles',Array('pratica'=>$this->pratica,'role'=>$role));
	}
	function addTransition($prms){
		$db=$this->db1;
		$initVal=Array("pratica"=>$this->pratica,'codice'=>null,'utente_in'=>$this->userid,'utente_fi'=>null,'data'=>"now",'stato_in'=>null,'stato_fi'=>null,'note'=>null,'tmsins'=>time(),'uidins'=>$this->userid);
		foreach($initVal as $key=>$val) $params[$key]=(in_array($key,array_keys($prms)) && $prms[$key])?($prms[$key]):($val);
		$params['note']=($params['note'])?($db->quote($params['note'])):($params['note']);
		$cod=$params['codice'];
		
		if($db->insert("pe.wf_transizioni",$params)){
			switch($cod){
				case "ardp":
				case "aitec":
				case "aiamm":
				case "aipre":
				case "aiagi":
				case "ailav":
					$this->addRole(substr($cod,1),$params['utente_fi'],$params['data']);
					break;
				case "rardp":
				case "raitec":
				case "raiamm":
					$this->delRole(substr($cod,2));
					$this->addRole(substr($cod,2),$params['utente_fi'],$params['data']);
					break;
				default:
					break;
			}
		}
		
	}
	function delTransition($id=null,$cod=null){
		$db=$this->db1;
		$filter=($id)?(Array('pratica'=>$this->pratica,'id'=>$id)):(Array('pratica'=>$this->pratica,'cod_transizione'=>$cod));
		$db->delete('pe.wf_transizioni',$filter);
	}
	
	static function setDB(){
		$classLoader = new ClassLoader('Doctrine', APPS_DIR.'plugins/');
		$classLoader->register();
		$config = new \Doctrine\DBAL\Configuration();
		$connectionParams = array(
			'dbname' => DB_NAME,
			'user' => DB_USER,
			'password' => DB_PWD,
			'host' => DB_HOST,
			'port' => DB_PORT,
			'driver' => DB_DRIVER,
		);
		$conn = \Doctrine\DBAL\DriverManager::getConnection($connectionParams, $config);
		return $conn;
	}
	
	
	
	
	
	function getLastId($tab){
		list($sk,$tb)=explode('.',$tab);
		$db=$this->db1;
		$sql="select array_to_string(regexp_matches(column_default, 'nextval[(][''](.+)['']::regclass[)]'),'') as sequence from information_schema.columns where table_schema=? and table_name=? and column_default ilike 'nextval%'";
		$sequence=$db->fetchColumn($sql,Array($sk,$tb));
		return $db->fetchColumn("select currval('$sequence')");
	}
	
	/*-----------------------------------------------------------------------------------------*/
	
	static function getStato($id){
		$db=pratica::setDB();
		$sql="SELECT codice,data,descrizione FROM pe.elenco_transizioni_pratiche WHERE pratica=? order by data DESC,tmsins DESC LIMIT 1;";
		$ris=$db->fetchAssoc($sql,Array($id));
		return $ris;
	}
	
	
}


class appUtils {
   static function getDB(){
		$classLoader = new ClassLoader('Doctrine', APPS_DIR.'plugins/');
		$classLoader->register();
		$config = new \Doctrine\DBAL\Configuration();
		$connectionParams = array(
			'dbname' => DB_NAME,
			'user' => DB_USER,
			'password' => DB_PWD,
			'host' => DB_HOST,
			'port' => DB_PORT,
			'driver' => DB_DRIVER,
		);
		$conn = \Doctrine\DBAL\DriverManager::getConnection($connectionParams, $config);
		return $conn;
	}
    static function getLastId($db,$tab,$sk=null,$tb=null){
		if(!$sk || !$tb) list($sk,$tb)=explode('.',$tab);
		//$db=self::getDB();
		$sql="select array_to_string(regexp_matches(column_default, 'nextval[(][''](.+)['']::regclass[)]'),'') as sequence from information_schema.columns where table_schema=? and table_name=? and column_default ilike 'nextval%'";
		$sequence=$db->fetchColumn($sql,Array($sk,$tb));
		return $db->fetchColumn("select currval('$sequence')");
	}
    
    static function isNumeric($v){
        try{
            $value=self::toNumber($v);
            return (int)(is_numeric($value));
        }
        catch(Exception $e){
            return 0;
        }
    }
    static function toNumber($v){
        return strlen(trim(str_replace(",",".",$v)))?trim(str_replace(",",".",$v)):'0';
    }
    
    static function getUserId(){
        return $_SESSION["USER_ID"];
    }
    
    static function getUserName(){
        return $_SESSION["USER_NAME"];
    }
/*-------------------------------------------------------------------------------*/    
    static function normalizeNumero($numero){
        return preg_replace("|([^A-z0-9\-]+)|",'',str_replace('/','-',str_replace('\\','-',$numero)));
    }
    static function getAnno($numero){
        $numero=self::normalizeNumero($numero);
    }

    static function unzip($dir,$file){
        $zip=zip_open(realpath($dir)."/".$file);
        if(!$zip) {return("Unable to proccess file '{$file}'");}
        $zfile='word/document.xml';

        while($zip_entry=zip_read($zip)) {
           $zdir=dirname(zip_entry_name($zip_entry));
           $zname=zip_entry_name($zip_entry);

           if(!zip_entry_open($zip,$zip_entry,"r")) {$e.="Unable to proccess file '{$zname}'";continue;} 
           if(!is_dir($zdir)) mkdirr($zdir,0777);

           $zip_fs=zip_entry_filesize($zip_entry);
           if(empty($zip_fs)) continue;

           $zz=zip_entry_read($zip_entry,$zip_fs);
           if ($zname==$zfile){
            return $zz;
           }
           //$z=fopen($zname,"w");
           //fwrite($z,$zz);
           //fclose($z);
           zip_entry_close($zip_entry);

        }
        zip_close($zip);

        return($e);
    }

    static function getDocxErrors($text){
        preg_match_all("|[$](.+)[}]|Umi",$text,$res,PREG_SET_ORDER);
        for($i=0;$i<count($res);$i++){
            $s=$res[$i][0];
            if(!preg_match("|^[$][{]([A-z0-9_]+)\.([[A-z0-9_]+)[}]$|Umi",$s,$r))
                $errors[$s]=htmlspecialchars($s);
        }
        return $errors;
    }
    
/*-------------------------------------------------------------------------------------------*/
    static function getInfoPratica($pratica){
        $db=self::getDb();
        $sql="SELECT numero,tipo,resp_proc,resp_it,resp_ia,date_part('year',data_presentazione) as anno,data_presentazione,data_prot FROM pe.avvioproc  WHERE pratica=?";
		$r=$db->fetchAssoc($sql, Array($pratica));
        //ESTRAGGO INFORMAZIONI SUL DIRIGENTE
		$sql="SELECT userid as dirigente FROM admin.users WHERE attivato=1 and '13' = ANY(string_to_array(coalesce(gruppi,''),','));";
		$dirig=$db->fetchColumn($sql);
		$r['dirigente']=$dirig;
		//ESTRAGGO INFORMAZIONI SUL RESPONSABILE DEL SERVIZIO
		$sql="SELECT userid as rds FROM admin.users WHERE attivato=1 and '15' = ANY(string_to_array(coalesce(gruppi,''),','));";
		$rds=$db->fetchColumn($sql);
		$r['rds']=$rds;
		return $r;
    }
    
    static function getStato($id){
		$db=pratica::setDB();
		$sql="SELECT codice,data,descrizione FROM pe.elenco_transizioni_pratiche WHERE pratica=? order by data DESC,tmsins DESC LIMIT 1;";
		$ris=$db->fetchAssoc($sql,Array($id));
		return $ris;
	}
    
    static function getIdTrans($m){
        $db=self::getDb();
        $id=$db->fetchColumn("SELECT id FROM pe.e_transizioni WHERE codice=?",Array($m),0);
        return $id;
    }
/*--------------------------------------------------------------------------------------------*/  
    static function getPraticaRole($cfg,$pratica){
        $db=self::getDB();
		//Recupero il responsabile del procedimento
		$rdp=$db->fetchColumn("SELECT resp_proc FROM pe.avvioproc WHERE pratica=?",Array($pratica),0);
		
		//Verifico il dirigente
		$idDiri=$db->fetchColumn("SELECT userid FROM admin.users WHERE (SELECT DISTINCT id::varchar FROM admin.groups WHERE nome='dirigenza')=ANY(string_to_array(coalesce(gruppi,''),','))",Array(),0);
		/*$db->sql_query($sql);
		$idDiri=$db->sql_fetchfield('userid');*/
		
		//Verifico il responsabile del Servizio
		$idRds=$db->fetchColumn("SELECT userid FROM admin.users WHERE (SELECT DISTINCT id::varchar FROM admin.groups WHERE nome='rds')=ANY(string_to_array(coalesce(gruppi,''),','))",Array(),0);
		/*$db->sql_query($sql);
		$idRds=$db->sql_fetchfield('userid');*/
        
        //Verifico gli archivisti
		$sql="SELECT userid FROM admin.users WHERE (SELECT DISTINCT id::varchar FROM admin.groups WHERE nome='archivio')=ANY(string_to_array(coalesce(gruppi,''),','));";
		$r=$db->fetchAll($sql);
        for($i=0;$i<count($r);$i++){
            $idArch[]=$r[$i];
            $roles[$r[$i]]="archivio";
            $ris[]=$r[$i];
        }
		//Array con tutti i ruoli
        $supRoles=Array($rdp,$idRds,$idDiri);
		$ris=Array($rdp,$idRds,$idDiri);
		
		$sql="SELECT role,utente FROM pe.wf_roles WHERE pratica=?";
        $res=$db->fetchAll($sql,Array($pratica));
        $roles[$idDiri]=Array('dir');
		$roles[$idRds]=Array('rds');
        
        for($i=0;$i<count($res);$i++){
				$r=$res[$i];
				$roles[$r['utente']][]=$r['role'];
				$ris[]=$r['utente'];
			}
		if(count($res)){
			if (in_array($_SESSION["USER_ID"],$ris) or $_SESSION["PERMESSI"]<2)
				$owner=1;
			else
				$owner=2;
		}
		else
			$owner=3;
        
        return Array("roles"=>$roles,"owner"=>$owner,"ris"=>$ris,"editor"=>$supRoles);
    }
/*---------------------------------------------------------------------------------------------*/    
    static function addRole($pratica,$role,$usr,$d){
		$t=time();
		$db=self::getDB();
		$data=($d)?(($d=='CURRENT_DATE')?('now'):($d)):('now');
		$arrDati=Array(
			'pratica'=>$pratica,
			'role'=>$role,
			'utente'=>$usr,
			'data'=>$data,
			'tmsins'=>$t,
			'uidins'=>self::getUserId()
		);
		$db->insert('pe.wf_roles', $arrDati);
	}
	static function delRole($pratica,$role){
		$db=self::getDB();
		$db->delete('pe.wf_roles',Array('pratica'=>$pratica,'role'=>$role));
	}
    
    
    static function addTransition($pratica,$prms){
        $db=self::getDb();
        $userid=appUtils::getUserId();
		$initVal=Array("pratica"=>$pratica,'codice'=>null,'utente_in'=>$userid,'utente_fi'=>null,'data'=>"now",'stato_in'=>null,'stato_fi'=>null,'note'=>null,'tmsins'=>time(),'uidins'=>$userid);
		foreach($initVal as $key=>$val) $params[$key]=(in_array($key,array_keys($prms)) && $prms[$key])?($prms[$key]):($val);
		$params['note']=($params['note'])?($db->quote($params['note'])):($params['note']);
		$cod=$params['codice'];
		
		if($db->insert("pe.wf_transizioni",$params)){
			switch($cod){
				case "ardp":
				case "aitec":
				case "aiamm":
				case "aipre":
				case "aiagi":
				case "ailav":
					self::addRole($pratica,substr($cod,1),$params['utente_fi'],$params['data']);
					break;
				case "rardp":
				case "raitec":
				case "raiamm":
					self::delRole($pratica,substr($cod,2));
					self::addRole($pratica,substr($cod,2),$params['utente_fi'],$params['data']);
					break;
				default:
					break;
			}
		}
		
	}
	static function delTransition($pratica,$id=null){
		$db=self::getDb();
        $isCodice=(is_numeric($id))?(0):(1);
		$filter=($isCodice)?(Array('pratica'=>$pratica,'id'=>$id)):(Array('pratica'=>$pratica,'codice'=>$id));
		$db->delete('pe.wf_transizioni',$filter);
	}
    
    static function addIter($pratica,$prms){
        $db=self::getDb();
        $usr=self::getUserName();
        $initVal=Array("pratica"=>$pratica,'data'=>'now()','utente'=>$usr,'nota'=>null,'nota_edit'=>null,'stampe'=>null,'immagine'=>'laserjet.gif','tmsins'=>time(),'uidins'=>$userid);
        foreach($initVal as $key=>$val) $params[$key]=(in_array($key,array_keys($prms)) && $prms[$key])?($prms[$key]):($val);
		//$params['nota']=($params['nota'])?($db->quote($params['nota'])):($params['nota']);
        //$params['nota_edit']=($params['nota_edit'])?($db->quote($params['nota_edit'])):($params['nota_edit']);
		$db->insert("pe.iter",$params);
    }
    
    static function getVincoli($pratica){
        $db=self::getDB();
        $sql="SELECT vincolo,tavola,zona FROM pe.vincoli WHERE pratica=?";
        $ris=$db->fetchAll($sql,Array($pratica));
        $result=Array();
        for($i=0;$i<count($ris);$i++) $result[strtolower($ris[$i]["vincolo"])][strtolower($ris[$i]["tavola"])][]=strtolower($ris[$i]["zona"]);
        return $result;
        
    }
    static function setPrmProgCalcolati($pratica,$data){
        $db=self::getDB();
        $table="pe.parametri_prog";
        $sql="select distinct id,codice from pe.e_parametri order by 2;";
        $res=$db->fetchAll($sql);
        for($i=0;$i<count($res);$i++) $e_prm[$res[$i]["codice"]]=$res[$i]["id"];
        $sql="select distinct B.id,A.codice from pe.e_parametri A inner join pe.parametri_prog B on(A.id=B.parametro) order by 2;";
        $res=$db->fetchAll($sql);
        for($i=0;$i<count($res);$i++) $prms[$res[$i]["codice"]]=$res[$i]["id"];
        $params=array_keys($data);
        
        //Volume Totale
        if (self::isNumeric($data[$prms["ve"]]) && self::isNumeric($data[$prms["vp"]]) && self::isNumeric($data[$prms["vd"]])){
        //if (self::isNumeric($data[$prms["vp"]])){
            $v=(double)self::toNumber($data[$prms["ve"]])+(double)self::toNumber($data[$prms["vp"]])-(double)self::toNumber($data[$prms["vd"]]);
            try{
                $db->insert($table,Array("pratica"=>$pratica,"parametro"=>$e_prm["v"],"valore"=>$v));
                $lastid=self::getLastId($db,$table);
                $prms["v"]=$lastid;
                $data[$lastid]=$v;
            }
            catch(Exception $e){}
        }
        //Indice di Fabbricabilità
        if (self::isNumeric($data[$prms["v"]]) && self::isNumeric($data[$prms["slot"]])){
            $v=(double)self::toNumber($data[$prms["v"]])/(double)self::toNumber($data[$prms["slot"]]);
            try{
                $db->insert($table,Array("pratica"=>$pratica,"parametro"=>$e_prm["iif"],"valore"=>$v));
                $lastid=self::getLastId($db,$table);
                $prms["iif"]=$lastid;
                $data[$lastid]=$v;
            }
            catch(Exception $e){}
        }
        //Superficie Coperta Totale
        if (self::isNumeric($data[$prms["sce"]]) && self::isNumeric($data[$prms["scp"]]) && self::isNumeric($data[$prms["scd"]])){
            $v=(double)self::toNumber($data[$prms["sce"]])+(double)self::toNumber($data[$prms["scp"]])-(double)self::toNumber($data[$prms["scd"]]);
            try{
                $db->insert($table,Array("pratica"=>$pratica,"parametro"=>$e_prm["sc"],"valore"=>$v));
                $lastid=self::getLastId($db,$table);
                $prms["sc"]=$lastid;
                $data[$lastid]=$v;
            }
            catch(Exception $e){}
        }
        //Indice di copertura
        if (self::isNumeric($data[$prms["sc"]]) && self::isNumeric($data[$prms["slot"]])){
            $v=((double)self::toNumber($data[$prms["sc"]])/(double)self::toNumber($data[$prms["slot"]]))*100;
            try{
                $db->insert($table,Array("pratica"=>$pratica,"parametro"=>$e_prm["ic"],"valore"=>$v));
                $lastid=self::getLastId($db,$table);
                $prms["ic"]=$lastid;
                $data[$lastid]=$v;
            }
            catch(Exception $e){}
        }
        //Superficie Utile Totale
        if (self::isNumeric($data[$prms["sue"]]) && self::isNumeric($data[$prms["sup"]]) && self::isNumeric($data[$prms["sud"]])){
            $v=(double)self::toNumber($data[$prms["sue"]])+(double)self::toNumber($data[$prms["sup"]])-(double)self::toNumber($data[$prms["sud"]]);
            try{
                $db->insert($table,Array("pratica"=>$pratica,"parametro"=>$e_prm["su"],"valore"=>$v));
                $lastid=self::getLastId($db,$table);
                $prms["su"]=$lastid;
                $data[$lastid]=$v;
            }
            catch(Exception $e){}
        }
        
        //Indice di utilizzo fondiario
        if (self::isNumeric($data[$prms["su"]]) && self::isNumeric($data[$prms["sf"]])){
            $v=(double)self::toNumber($data[$prms["su"]])/(double)self::toNumber($data[$prms["sf"]]);
            try{
                $db->insert($table,Array("pratica"=>$pratica,"parametro"=>$e_prm["uf"],"valore"=>$v));
                $lastid=self::getLastId($db,$table);
                $prms["uf"]=$lastid;
                $data[$lastid]=$v;
            }
            catch(Exception $e){}
        }
        //Indice di copertura esistente
        if (self::isNumeric($data[$prms["sce"]]) && self::isNumeric($data[$prms["slot"]])){
            $v=(double)self::toNumber($data[$prms["sce"]])/(double)self::toNumber($data[$prms["slot"]]);
            try{
                $db->insert($table,Array("pratica"=>$pratica,"parametro"=>$e_prm["ice"],"valore"=>$v));
                $lastid=self::getLastId($db,$table);
                $prms["ice"]=$lastid;
                $data[$lastid]=$v;
            }
            catch(Exception $e){}
        }
        //Volume con indice 3/1
        if (self::isNumeric($data[$prms["slot"]])){
            $v=(double)3*(double)self::toNumber($data[$prms["slot"]]);
            try{
                $db->insert($table,Array("pratica"=>$pratica,"parametro"=>$e_prm["v3_1"],"valore"=>$v));
                $lastid=self::getLastId($db,$table);
                $prms["v3_1"]=$lastid;
                $data[$lastid]=$v;
            }
            catch(Exception $e){}
        }
        //Volume Zone E
        if (self::isNumeric($data[$prms["slot"]])){
            $vincoli=self::getVincoli($pratica);
            $isZonaE=0;
            for($i=0;$i<count($vincoli["prg"]["zonizzazione"]);$i++)
                if (preg_match('/^e(.*)/',$vincoli["prg"]["zonizzazione"][$i])) 
                        $isZonaE=1;
            if($isZonaE){
                $prmZonaE=Array(3,1.5,0.75);
                $sup1=(double)self::toNumber($data[$prms["slot"]]);
                if ($sup1>10000) {
                    $v1=round(10000*$prmZonaE[0],2);
                    $sup1=$sup1-10000;
                    if ($sup1>10000) {
                        $v2=round(10000*$prmZonaE[1],2);
                        $sup1=$sup1-10000;
                        if ($sup1>0) {
                            $v3=round($sup1*$prmZonaE[2],2);
                        }
                    }
                    else { 
                        $v2=round($sup1*$prmZonaE[1],2);
                        $v3=0;
                     }			

                }
                else { 
                    $v1=round($sup1*$prmZonaE[0],2);
                    $v2=0;
                    $v3=0;
                }
                $v=$v1+$v2+$v3;
                try{
                    $db->insert($table,Array("pratica"=>$pratica,"parametro"=>$e_prm["v_ze"],"valore"=>$v));
                    $lastid=self::getLastId($db,$table);
                    $prms["v_ze"]=$lastid;
                    $data[$lastid]=$v;
                }
                catch(Exception $e){}
                
            }
        }
        //Volume Esistente - Volume da demolire
        if (self::isNumeric($data[$prms["ve"]]) && self::isNumeric($data[$prms["vd"]])){
            $v=(double)self::toNumber($data[$prms["ve"]])-(double)self::toNumber($data[$prms["vd"]]);
            try{
                $db->insert($table,Array("pratica"=>$pratica,"parametro"=>$e_prm["ve_vd"],"valore"=>$v));
                $lastid=self::getLastId($db,$table);
                $prms["ve_vd"]=$lastid;
                $data[$lastid]=$v;
            }
            catch(Exception $e){}
        }
        //Volume Progetto - Volume da demolire
        if (self::isNumeric($data[$prms["vp"]]) && self::isNumeric($data[$prms["vd"]])){
            $v=(double)self::toNumber($data[$prms["vp"]])-(double)self::toNumber($data[$prms["vd"]]);
            try{
                $db->insert($table,Array("pratica"=>$pratica,"parametro"=>$e_prm["vp_vd"],"valore"=>$v));
                $lastid=self::getLastId($db,$table);
                $prms["vp_vd"]=$lastid;
                $data[$lastid]=$v;
            }
            catch(Exception $e){}
        }
    }
    
     /*static function unzip($dir,$file){
       $zip=zip_open(realpath($dir)."/".$file);
        if(!$zip) {return("Unable to proccess file '{$file}'");}
        $zfile='word/document.xml';

        while($zip_entry=zip_read($zip)) {
           $zdir=dirname(zip_entry_name($zip_entry));
           $zname=zip_entry_name($zip_entry);

           if(!zip_entry_open($zip,$zip_entry,"r")) {$e.="Unable to proccess file '{$zname}'";continue;} 
           if(!is_dir($zdir)) mkdirr($zdir,0777);

           $zip_fs=zip_entry_filesize($zip_entry);
           if(empty($zip_fs)) continue;

           $zz=zip_entry_read($zip_entry,$zip_fs);
           if ($zname==$zfile){
            return $zz;
           }
           //$z=fopen($zname,"w");
           //fwrite($z,$zz);
           //fclose($z);
           zip_entry_close($zip_entry);

        }
        zip_close($zip);

        return($e);
    }

    static function getDocxErrors($text){
        $errors=Array();
        preg_match_all("|[$](.+)[}]|Umi",$text,$res,PREG_SET_ORDER);
        for($i=0;$i<count($res);$i++){
            $s=$res[$i][0];
            if(!preg_match("|^[$][{]([A-z0-9_]+)\.([[A-z0-9_]+)[}]$|Umi",$s,$r))
                $errors[$s]=htmlspecialchars($s);
        }
        return $errors;
    }*/
}
?>
