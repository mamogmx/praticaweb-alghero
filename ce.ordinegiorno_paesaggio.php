<?
//print_r($_REQUEST);
include_once("login.php");
$tabpath="ce";
$file_config="$tabpath/ordinegiorno_paes";
$modo=(isset($_REQUEST["mode"]))?($_REQUEST["mode"]):('view');
$idcomm=$_REQUEST["pratica"];
$ric=$_REQUEST["ricerca"];
$titolo=$_SESSION["TITOLO_$idcomm"];
if ($idcomm==0)	$modo="new";

include "./lib/tabella_h.class.php";
include_once "./lib/tabella_v.class.php";

?>
<html>
<head>
<title>Ordine del Giorno - <?=$titolo?></title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<SCRIPT language="javascript" src="js/LoadLibs.js" type="text/javascript"></SCRIPT>
<SCRIPT language="javascript" src="src/iframe.js" type="text/javascript"></SCRIPT>
<SCRIPT language=javascript>
function elimina(pratica){
	cancella.idelete.value=pratica;
	cancella.ricerca.value=0;
	if (confirm("sei sicuro di voler eliminare questa pratica?")) cancella.submit();
}

function show(id){
	d=document.getElementById(id);
	if (d.style.display=='none') d.style.display='';
	else
		d.style.display='none';
	
	in_resizeCaller();
}
</SCRIPT>
</head>
<body>
<?
	if ($modo=="edit"){
		include "./inc/inc.page_header.php";
		// Pagina dei risultati della ricerca
		if ($ric==1){
			include "ce.ricerca_pratiche.php";

		}
		//Pagina di ricerca delle pratiche
		else{
			$modo="edit";
			$ric=1;
			$idpratica=$_POST["idelete"];
			$tabella=new Tabella_v("$tabpath/ordinegiorno",'find');
			$tabella->set_titolo("Trova Pratiche");
			$tabella->get_titolo();?>
		<form name="ricerca" method="post" action="ce.ordinegiorno_paesaggio.php">
			<?$tabella->edita();?>
		<table>
			<tr>
				<td>
					<input name="active_form" type="hidden" value="ce.ordinegiorno_paesaggio.php">
					<input name="mode" type="hidden" value="<?=$modo?>">
					<input name="comm_paesaggio" type="hidden" value=1>
					<input name="tiporicerca" type="hidden" value="1">
					<input name="ricerca" type="hidden" value="<?=$ric?>">
					<input name="pratica" type="hidden" value="<?=$idcomm?>">
					<input name="data" type="hidden" value="avvioproc.data_presentazione">
				</td>
				<td valign="bottom"><input name="azione" type="submit" class="hexfield" tabindex="14" value="Trova"></td>
				<td valign="bottom"><input name="azione" type="button" class="hexfield" tabindex="14" value="Annulla" onclick="javascript:document.location='praticaweb.php?comm_paesaggio=1&pratica=<?=$idcomm?>&active_form=ce.ordinegiorno_paesaggio.php'"></td>
			</tr>
		</table>
	</form>
		
		<?	// Eseguo cancellazione della pratica dalla commissione
			if ($idpratica) {
				$db = new sql_db(DB_HOST,DB_USER,DB_PWD,DB_NAME, false);
				if(!$db->db_connect_id)  die( "Impossibile connettersi al database");	
				$sql="DELETE FROM pe.pareri WHERE ente=(SELECT tipo_comm FROM ce.commissione WHERE id=$idcomm) and data_rich=(SELECT data_convocazione FROM ce.commissione WHERE id=$idcomm) and pratica=$idpratica";
				if (!$db->sql_query($sql)) echo "ERRORE NELLA CANCELLAZIONE DELLA PRATICA <br>$sql<br>";
				print_debug($sql);
			}
			$tabella_h=new Tabella_h($file_config,$modo);
			$tabella_h->set_titolo("Elenco pratiche da discutere");
			$tabella_h->get_titolo();
			$tabella_h->set_dati("pratica > 0");?>
	<form name="cancella" method="post" action="ce.ordinegiorno_paesaggio.php">
			<?$tabella_h->elenco();?>
		<table>
			<tr>
				<td>
					<input name="active_form" type="hidden" value="ordinegiorno_paesaggio.php">
					<input name="mode" type="hidden" value="<?=$modo?>">
					<input name="comm_paesaggio" type="hidden" value=1>
					<input name="ricerca" type="hidden" value="<?=$ric?>">
					<input name="pratica" type="hidden" value="<?=$idcomm?>">
                                  <input name="tiporicerca" type="hidden" value="1">
					<input name="idelete" type="hidden">
				</td>
			</tr>
		</table>
	</form>
		
	<?}?>
		<img src="images/gray_light.gif" height="2" width="90%">
		
		
	<?
	
//include "./inc/inc.messaggi.php";
}
// ModalitÃ  view della pagina
else{ 
		
		unset($_SESSION["ADD_NEW"]);
		$tabella=new Tabella_v("$tabpath/convocazione_paes");
		$tabellah=new Tabella_h($file_config);
		//print_r($tabella->num_col)?>
		<!-- <<<<<<<<<<<<<<<<<<<<<   MODALITA' FORM IN VISTA DATI  >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>--->
	<H2 class="blueBanner">Ordine del giorno</H2>
		
			<!-- contenuto-->
<?
		//include "./inc/page_header.inc";

		$tabella->set_titolo("Dati della commissione");
		$tabella->set_dati("pratica=$idcomm");
		$tabella->get_titolo();
		$tabella->tabella();
		$tabellah->set_titolo("Pratiche discusse","modifica");
		$tabellah->set_dati("id>0");
		$tabellah->get_titolo();
		$tabellah->elenco();
	}
	
				//$tabella->elenco_stampe("commissione")?>
			<!-- fine contenuto-->
			 

</body>
</html>
