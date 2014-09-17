<?
/*
form per il calcolo automatico oneri di urbanizzazione e costo di costruzione Regione Liguria
in modalitÃ  edit permette il calcolo automatico o la modifica di un calcolo esistente 
in modalitÃ  view elenca in ordine cronologico inverso i calcoli fatti


*/
include_once("login.php");
$self=$_SERVER["PHP_SELF"];
$step=$_POST["step"];
$idpratica=$_REQUEST["pratica"];
$modo=(isset($_REQUEST["mode"]))?($_REQUEST["mode"]):('view');$calcolo=$_POST["calcolo"];
$id=$_REQUEST["id"];
unset($_SESSION["ADD_NEW"]);
$titolo=$_SESSION["TITOLO_$idpratica"];
$tabpath="oneri";

include "./lib/tabella_v.class.php";
if (($modo=="new") or ($modo=="edit")){
//########### MODALITA NUOVO CALCOLO O EDITA CALCOLO ESISTENTE ################
	$tabella=new tabella_v("$tabpath/calcolati",$modo);
	$tabella->set_tabella_elenco("e_oneri");	
	if ($modo=="edit"){//sto editando un calcolo esistente sul db
		if ($calcolo)
			$dati=$_POST;
		else
		$dati="id=$id";
	}
       else if ($modo=="new") $dati="pratica=$idpratica";
?>
<html>
<head>
<title>Calcolo Oneri - <?=$titolo?></title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<SCRIPT language="javascript" src="js/LoadLibs.js" type="text/javascript"></SCRIPT>
<SCRIPT language="javascript" src="src/rpc.oneri_calcolati.js" type="text/javascript"></SCRIPT>
<SCRIPT language="javascript" src="src/http_request.js" type="text/javascript"></SCRIPT>

<script language=javascript>

function set_perc(){
	if ((document.oneri.intervento.value>0) || document.oneri.intervento.value=='')
		document.oneri.perc.style.display = 'none';
	else
		document.oneri.perc.style.display = '';

}

function confirmSubmit()
{	
	return confirm('Sicuro di voler eliminare definitivamente il calcolo?');
}
function checkdati(){
	for(i=0;i<document.oneri.elements.length;i++){
		var obj=document.oneri.elements[i];
		if ((obj.type=='select-one') && (obj.value=='')){
			alert('Per effettuare il calcolo automatico Ãš necessario selezionare il campo '+obj.name);
			return false;
		}
	}
	return true;
}

</script>

</head>

<body  background="" onload="javascript:init(<?="$idpratica,'$modo'"?>);">

<?include "./inc/inc.page_header.php";	?>
		<!-- <<<<<<<<<<<<<<<<<<<<<   MODALITA' FORM IN EDITING  >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>--->
		<TABLE cellPadding=0  cellspacing=0 border=0 class="stiletabella" width="99%" align="center">		
		<FORM id="oneri" name="oneri" method="post" action="praticaweb.php">		  
		  <tr> 
			<td> 
			<!-- intestazione-->
				<H2 class="blueBanner">Calcolo Oneri</H2>
			<!-- fine intestazione-->
			</td>
		  </tr>
		  <tr> 
			<td> 
				<!-- contenuto-->
			<?$tabella->set_dati($dati);
				$tabella->edita();?>
				<!-- fine contenuto-->
			</td>
		  </tr>
		  <tr> 
				<!-- riga finale -->
				<td align="left"><img src="images/gray_light.gif" height="2" width="90%"></td></td>
		   </tr>  
		</TABLE>
		<table>
			<tr>
				<td><input name="active_form" type="hidden" value="oneri.calcolati.php">
				<input name="pratica" type="hidden" value="<?=$idpratica?>">
				<input name="calcolo" type="hidden" value="1">
				<input name="id" type="hidden" value="<?=$id?>">
				<input name="mode" type="hidden" value="<?=$modo?>">
				</td>
				<td valign="bottom"><input name="azione" type="submit" class="hexfield" tabindex="14" value="Salva" onclick="return checkdati()"></td>
				<td valign="bottom"><input name="azione" type="submit" class="hexfield" tabindex="14" value="Annulla"></td>
				<?if($id){?>
				<td valign="bottom"><input name="azione" type="submit" class="hexfield" tabindex="14" value="Elimina" onclick="confirmSubmit()"></td><?}?>
			</tr>
		</FORM>		
		</table>	

		</body>
</html>
<?}
else{
// MODALITA VISTA DATI
//echo "id=$id";

?>
<html>
<head>
<title>Oneri</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<SCRIPT language="javascript" src="js/LoadLibs.js" type="text/javascript"></SCRIPT>
</head>
<body  background="">
		
		<!-- <<<<<<<<<<<<<<<<<<<<<   MODALITA' FORM IN VISTA DATI  >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>--->
		<H2 class="blueBanner">Calcolo Oneri in dettaglio</H2>
		<TABLE cellPadding=0  cellspacing=0 border=0 class="stiletabella" width="100%">		
		<TR>
			<TD>
				<?
				$tabella=new tabella_v("$tabpath/calcolati");
				$nrec=$tabella->set_dati("pratica=$idpratica");
				if($nrec>0){
					$tabella->set_tabella_elenco("oneri.e_tariffe");	
					$tabella->set_titolo("calcolo","modifica",array("id"=>"","pratica"=>""));
					$tabella->elenco();
				}
				else{
					echo "<p><b>Nessun calcolo</b></p>";
				}				
					
					?>
			</TD>
		</TR>
<?php if ($tabella->editable){ ?>		
		<tr>
		  <td>
				&nbsp;<input  name=""  id="" class="hexfield1" style="width:70px" type="button" value="chiudi" onClick="javascript:window.location='oneri.importi.php?pratica=<?=$_REQUEST["pratica"]?>'" >
		  </td>
		</tr>
<?php } ?>		

</body>
</html>

<?}?>

