<?
include_once ("login.php");
$titolo=$_SESSION["TITOLO_$idpratica"];
$idpratica=$_REQUEST["pratica"];
$modo=(isset($_REQUEST["mode"]))?($_REQUEST["mode"]):('view');
$curr_part=$_REQUEST["part"];
$ar_particella=explode(",",$curr_part); 
if($ar_particella[0])
	$sql_update_mappale="sezione='".$ar_particella[0]. "',";
$sql_update_mappale="foglio='" . $ar_particella[1] . "' , mappale='" . $ar_particella[2] . "'";

$tabpath=cdu;
$azione=$_POST["azione"]; 
if ($_POST["azione"]){ 
	$idrow=$_POST["idriga"];
	$active_form=$_REQUEST["active_form"]; 
	if($_SESSION["ADD_NEW"]!==$_POST){
			unset($_SESSION["ADD_NEW"]);//serve per non inserire piÃ¹ record con f5
		if (isset($array_dati["errors"])) //sono al ritorno errore
			$Errors=$array_dati["errors"];
		else 
			include_once "./db/db.cdu.vincoli.php";
		if ($modo=="new"){ 
			$newid=$_SESSION["ADD_NEW"];
			$sql="update cdu.mappali set $sql_update_mappale where id=$newid";
			//echo "<p>$sql</p>";
			$db->sql_query($sql);
		}
		$_SESSION["ADD_NEW"]=$_POST;	
	}
}

?>
<html>
<head>

<title>Vincoli - <?=$titolo?></title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<SCRIPT language="javascript" src="js/LoadLibs.js" type="text/javascript"></SCRIPT>
<SCRIPT language="javascript" src="src/http_request.js" type="text/javascript"></SCRIPT>
<SCRIPT language="javascript" src="src/x_core.js" type="text/javascript"></SCRIPT>
<script language=javascript>

function confirmSubmit()
{
	document.getElementById("azione").value="Salva";
	return true ;
}

function elimina(id){
	var agree=confirm('Sicuro di voler eliminare definitivamente la riga selezionata?');
	if (agree){
		$("#btn_azione").val("Elimina");
		$("#idriga").val(id);
		$('#vincoli').submit();
	}
}
function link(id){
	window.location="pe.scheda_normativa.php?id="+id;
}
/*
function is_array(obj){
		if(typeof(obj)=='object' && typeof(obj.length)=='number')
			return true;
		else
			return false;
	}
	
	function getVincoli(obj,id,schema){ 
		if (obj.name=='vincolo')  {
			var prm=new Array('action=elenco_vinc','obj=tavola');
			var val=obj.options[obj.selectedIndex].value;
			prm.push('id='+val); 
			var param=prm.join('&'); 
		}
		else if(obj.name=='tavola'){
			var prm=new Array('action=elenco_vinc','obj=zona');
			var o=xGetElementById('vincolo');
			var val=o.options[o.selectedIndex].value;
			val=val+obj.options[obj.selectedIndex].value;
			prm.push('id='+val);
			var param=prm.join('&'); 
		}
		
		makeRequest('rpc_vincoli_cdu.php',param,'setVincoli','POST');
	}
	function setVincoli(obj) {
		var o=xGetElementById(obj.id);	
		for(j=o.length-1;j>=0;j--){
			o.remove(j);	//Rimuovo tutte le opzioni dal select
		}
		var arr=obj.values;  
		if (is_array(arr)){
			if (arr.length==0){	
				o.options[0]=new Option('Seleziona ====>','');
			}
			else{
				o.options[0]=new Option('Seleziona ====>','');			//Aggiungo il Primo elemento
				for(j=1;j<=arr.length;j++) o.options[j]=new Option(arr[j-1].name,arr[j-1].id);	//Aggiungo le altre opzioni
			}
		}
	}*/
</script>
</head>
<body  background="" leftMargin="0" topMargin="0" marginheight="0" marginwidth="0">
<?
if (($modo=="edit") or ($modo=="new")){
	include_once "./lib/tabella_h.class.php";
	include_once "./lib/tabella_v.class.php";
	$tabellav=new tabella_v("$tabpath/zone_mappale",'new');
	$tabellah=new tabella_h("$tabpath/zone_mappale",'edit');
	$tabellav->set_errors($errors);
	if($ar_particella[0]){
		$sparticella="Sezione " . $ar_particella[0]. " ";
		$sql="sezione='".$ar_particella[0]. "' and ";
	}
	$sparticella.="Foglio ".  $ar_particella[1]. " Mappale " .  $ar_particella[2];
	$sql="foglio='" . $ar_particella[1] . "' and mappale='" . $ar_particella[2] . "'";
	include "./inc/inc.page_header.php";?>
<form method=post name="vincoli" id="vincoli" action="cdu.vincoli.php">
	<TABLE cellPadding=0  cellspacing=0 border=0 class="stiletabella" width="99%" align="center">		
		<TR> 
			<td> 
			<!-- intestazione-->
				<H2 class="blueBanner">Modifica elenco vincoli associati alla particella <?=$sparticella?></H2>
			<!-- fine intestazione-->
			</td>
		  </TR>
		  <TR> 
			<td> 
				<!-- contenuto-->

				<input type="hidden" name="idriga" id="idriga" value="0">
				<input type="hidden" name="part" id="part" value="<?=$curr_part?>">				
				<input name="active_form" type="hidden" value="cdu.vincoli.php">
				<input name="cdu" type="hidden" value="1"></td>
				<input type="hidden" name="mode" value="new">

				<?
				if($Errors){
					$tabellav->set_errors($Errors);
					$tabellav->set_dati($_POST);
				}
				  $tabellav->edita();?>
				
				<!-- fine contenuto-->			
			</td>
		  </TR>
	</table>
<p>&nbsp;</p>
	<TABLE cellPadding=0  cellspacing=0 border=0 class="stiletabella" width="99%" align="center">	
		
	<?$db=$tabellah->get_db();
		$db->sql_query ("select * from vincoli.vincolo order by ordine;");
		
		$elenco_vincoli = $db->sql_fetchrowset(); 
		foreach($elenco_vincoli as $row){
			$vincolo=$row["nome_vincolo"];
			$nome_vincolo=$row["descrizione"];	
			$num_zone=$tabellah->set_dati("pratica=$idpratica and vincolo='$vincolo' and $sql");
		?>
		  <TR> 
			<td> 
			<?
				if ($num_zone) {
					print ("<b>$nome_vincolo</b>");
					$tabellah->elenco();
				}?>
			</td>
		  </TR>
<?}// end for?>
	</TABLE>
</form>		
		<?include "./inc/inc.window.php"; // contiene la gesione della finestra popup
	}//end if

else{
	
//########## MODALITA' VISTA DATI #####################
$db = new sql_db(DB_HOST,DB_USER,DB_PWD,DB_NAME, false);
if(!$db->db_connect_id)  die( "Impossibile connettersi al database");

//SCHEMA DB VECCHIO
//elenco dei piani
$sql_piani="select nome,descrizione from pe.e_vincoli where cdu=1 order by ordine;";
print_debug($sql_piani,"tabella");
//verifico l'esitenza dei vincoli per la pratica corrente
$sql_vincoli="select (coalesce(cdu.mappali.sezione,'') || ','::text || cdu.mappali.foglio || ','::text || cdu.mappali.mappale) as particella,mappali.vincolo,mappali.zona,mappali.perc_area,e_vincoli.descrizione from pe.e_vincoli, cdu.mappali where
mappali.vincolo=e_vincoli.nome and pe.e_vincoli.cdu=1 and pratica=$idpratica order by cdu.mappali.perc_area desc, cdu.mappali.sezione,cdu.mappali.foglio,cdu.mappali.mappale;";
print_debug("Vincoli\n".$sql_vincoli);
//aggiungo i mappali che non risultano legati a vincoli
$sql_mappali="select (coalesce(cdu.mappali.sezione,'') || ','::text || cdu.mappali.foglio || ','::text || cdu.mappali.mappale)  as particella from cdu.mappali where pratica=$idpratica and vincolo is null;";
print_debug($sql_mappali,"tabella");

//SCHEMA DB NUOVO
//elenco dei piani
$sql_piani2="select nome_vincolo,nome_tavola,descrizione from vincoli.tavola where cdu=1 order by ordine;";
print_debug($sql_piani2,"tabella");
//verifico l'esitenza dei vincoli per la pratica corrente
$sql_vincoli2="select distinct(coalesce(cdu.mappali.sezione,'') || ','::text || cdu.mappali.foglio || ','::text || cdu.mappali.mappale) as particella,mappali.vincolo,mappali.zona,mappali.tavola,mappali.perc_area,
case when coalesce(zona.sigla,'')<>'' then zona.sigla else zona.descrizione end as descrizione from cdu.mappali left join vincoli.zona on (mappali.zona=zona.nome_zona and mappali.vincolo=zona.nome_vincolo) 
left join vincoli.tavola on (mappali.tavola=zona.nome_tavola) where tavola.cdu=1 and  pratica=$idpratica";
print_debug("Vincoli\n".$sql_vincoli2);

//echo "<p>$sql_vincoli2</p>";

	$db->sql_query ($sql_piani2); 
	$piani=$db->sql_fetchrowset();
	$npiani=$db->sql_numrows();
	$db->sql_query ($sql_vincoli2);
	$vincoli=$db->sql_fetchrowset();
	$nvincoli=$db->sql_numrows();


$db->sql_query ($sql_mappali);
$mappali=$db->sql_fetchrowset();
$nmappali=$db->sql_numrows();

$array_mappali=array();
$array_zone=array();

//verifico se esiste il vincolo nelle tavole
	for ($r=0; $r < $nvincoli; $r++){
	$idparticella=$vincoli[$r]["particella"];
	$piano=$vincoli[$r]["tavola"];  
	$zona=$array_zone[$idparticella][$piano];
	if ($zona)
		$zona.="<br>".$vincoli[$r]["zona"]." (".$vincoli[$r]["perc_area"]." %)";
	else
		$zona=$vincoli[$r]["zona"]." (".$vincoli[$r]["perc_area"]." %)";
	$array_zone[$idparticella][$piano]=$zona; 
	}



$array_mappali=array_keys ($array_zone);

$req="mode=edit&pratica=$idpratica";

?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<SCRIPT language="javascript" src="js/LoadLibs.js" type="text/javascript"></SCRIPT>
<title>Raffronto<?=$titolo?></title>
</head>
<body>

<?//include "./inc/page_header.inc";	?>
<H2 class="blueBanner">Tabella dei vincoli per mappale:</H2>

<table cellPadding=2  cellspacing=2 width="100%" class="stiletabella" border=1>
<FORM name="cdu" method="post" action="praticaweb.php">
<tr bgColor=#728bb8>
    <td colspan=2 width="140" rowspan="2"  valign="middle" align="center">
		<font face="Verdana" color="#ffffff" size="1"><b>Particella</b></font>
	</td>
	<td colspan="<?=$npiani?>" align="center"><font face="Verdana" color="#ffffff" size="1"><b>Vincoli</b></font></td>		
</tr>
<tr bgColor=#728bb8>
	<?for($i=0;$i<$npiani;$i++){?>	
		<td height="15"  align="center">
			<font face="Verdana" color="#ffffff" size="1"><b><?=$piani[$i]["descrizione"]?></b></font>
		</td>
		<?}?>
</tr>

<?php

for($i=0;$i<count($array_mappali);$i++){ 
	$idparticella=$array_mappali[$i]; 
	$ar_particella=explode(',',$idparticella);
	if ($ar_particella[0]) 
		$particella='Sez. '. $ar_particella[0].' F. '. $ar_particella[1].' M. '. $ar_particella[2];
	else
		$particella=' F. '. $ar_particella[1].' M. '. $ar_particella[2];
	$url="cdu.vincoli.php?pratica=$idpratica&mode=edit&part=$idparticella";?>
<tr>
	<td><a href="<?=$url?>" target="_parent"><img src="images/propri.gif" border=0></a></td>
	<td height="15"  align="center">
		<font face="Verdana"  size="1"><b><?=$particella?></b></font>
	</td>
	<?for($j=0;$j<$npiani;$j++){
	
		$piano=$piani[$j]["nome_tavola"]; 
		$zona=$array_zone[$idparticella][$piano]; 
	
			if(!$zona) $zona="---";
		?>	
		<td height="15"  align="left">
			<font face="Verdana" size="1"><b><?=$zona?></b></font>
	</td>
	<?}?>
</tr>
<?}?>

<?//Aggiungo i mappali senza vincoli
	for($i=0;$i<count($mappali);$i++){
		$idparticella=$mappali[$i]['particella'];
		$ar_particella=explode(',',$mappali[$i]["particella"]); 
		if ($ar_particella[0]) 
			$particella='Sez. '. $ar_particella[0].' F. '. $ar_particella[1].' M. '. $ar_particella[2];
		else
			$particella=' F. '. $ar_particella[1].' M. '. $ar_particella[2];
		$url="cdu.vincoli.php?pratica=$idpratica&mode=edit&part=$idparticella"; 
		?>
		<tr>
	<td><a href="<?=$url?>" target="_parent"><img src="images/propri.gif" border=0></a></td>
	<td height="15"  align="center">
		<font face="Verdana"  size="1"><b><?=$particella?></b></font>
		</b></font>
	</td>
	<?for($j=0;$j<$npiani;$j++){?>
		<td height="15"  align="left">
			<font face="Verdana" size="1"><b>---</b></font>
	</td>
	<?}?>
</tr>
<?}?>
</table>
<br>
		</FORM>		
			
<?
	include_once "./lib/tabella_v.class.php";
	$tabellav=new tabella_v("$tabpath/stampa.tab");
	$tabellav->set_dati("id>0");
	//$tabellav->edita();
	print($tabellav->elenco_stampe("cdu.vincoli"));
}?>
	</body>
	</html>
