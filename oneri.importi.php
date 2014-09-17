<?
/*se il record corrente da editare Ãš stato generato con il calcolo automatico rendo editabile solo i valori di scomputo e nascondo il pulsante 
Elimina in quanto l'eliminazione degli importi calcolati va fatta dai singoli calcoli 
se invece il record Ãš stato inserito manualmente posso editare tutto ed eliminare il record*/

include_once("login.php");
include "./lib/tabella_v.class.php";
include "./lib/tabella_h.class.php";

$modo=(isset($_REQUEST["mode"]))?($_REQUEST["mode"]):('view');
$idpratica=$_REQUEST["pratica"];
$tab=(isset($_POST["tabella"]))?($_POST["tabella"]):(null);
$titpag=$_SESSION["TITOLO_$idpratica"];
$Errors=$array_dati["errors"];
$tabpath="oneri";

if ($tab=="oneri" || !$tab){
				$file_conf_oneri="$tabpath/oneri_concessori";
				$file_config_cmonet="$tabpath/corrispettivo_monetario";		
}
elseif($tab=='concessori'){
				$titolo="Costo di Costruzione e Oneri di Urbanizzazione";
				$file_conf="$tabpath/oneri_concessori";
}
elseif($tab=='monetario'){
				$titolo="Corrispettivo Monetario";
				$file_conf="$tabpath/corrispettivo_monetario";	
}
elseif ($tab=="monetizzazione"){	
				$titolo="Monetizzazione aree verdi e parcheggi";
				$file_conf="$tabpath/monetizzazione.tab";
}
	
//	print_array($_REQUEST);
?>
<html>
<head>
<title><?=$titolo." - ".$titpag?></title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<SCRIPT language="javascript" src="js/LoadLibs.js" type="text/javascript"></SCRIPT>
</SCRIPT>
</head>
<script language=javascript>
function link(id){
	window.location="oneri.calcolati.php?pratica=<?=$idpratica?>";
}
</script>
<body  background="">

<?php
				if (($modo=="edit") or ($modo=="new")) {
				$tabella=new tabella_v($file_conf,$modo);
	unset($_SESSION["ADD_NEW"]);	
	include "./inc/inc.page_header.php";	?>
		<!-- <<<<<<<<<<<<<<<<<<<<<   MODALITA' FORM IN EDITING  >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>--->
<FORM id="" name="" method="post" action="praticaweb.php">
	<TABLE cellPadding=0  cellspacing=0 border=0 class="stiletabella" width="99%" align="center">		
		<tr>
			<!-- intestazione-->
			<td> <H2 class="blueBanner"><?=$titolo?></H2></td>
		</tr>
		<tr> 
			<td> 
			<!-- contenuto-->
			<?php
				if($Errors){
					$tabella->set_errors($Errors);
					$tabella->set_dati($_POST);
				}
				elseif ($modo=="edit"){	
					$tabella->set_dati("pratica=$idpratica");
				}
				$tabella->edita();
			?>	
			</td>
		</tr>
	</TABLE>

	<input name="active_form" type="hidden" value="oneri.importi.php">
	<input name="mode" type="hidden" value="<?=$modo?>">
	<input name="tabella" type="hidden" value="<?=$tab?>">
</FORM>	
<?
}else{
//           <<<<<<<<<<<<<<<<<<<<<   MODALITA' FORM IN VISTA DATI  >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>--->
?>


	<h2 class="blueBanner">Tabella degli oneri</h2>
	<TABLE cellPadding=0  cellspacing=0 border=0 class="stiletabella" width="99%" align="center">			
					<tr> 
									<td>

<!--  tabella oneri e costo-->
<?php //se non ci sono record visualizzo comunque il pulsante per il calcolo automatico
	$tabella_oneri=new Tabella_v($file_conf_oneri,'view');//tabella verticale con totali ed estremi di pagamento
	$tabella_cmonet=new Tabella_v('oneri/corrispettivo_monetario','view');
	$tabella_monetizz=new Tabella_v('oneri/monetizzazione','view');
	
	$numrows=$tabella_oneri->set_dati("pratica=$idpratica");//vedo se c'è un record nella tabella dei totali
	if ($numrows){
		$tabella_oneri->set_titolo("Costo di Costruzione e Oneri di Urbanizzazione","modifica",array("tabella"=>"concessori"));
		$tabella_oneri->get_titolo();
		$tabella_oneri->tabella();
	}
	else{
		$tabella_oneri->set_titolo("Costo di Costruzione e Oneri di Urbanizzazione","nuovo",array("tabella"=>"concessori"));
		$tabella_oneri->get_titolo();
		print "<p><b>Nessun dato inserito</b></p>";
        print "<hr>";
        if ($tabella_oneri->editable) echo "
        <form method=\"post\" target=\"_parent\" action=\"oneri.calcolo.urbanizzazione.php\">	
            <input type=\"hidden\" name=\"mode\" value=\"new\">	
			<input type=\"hidden\" name=\"pratica\" value=\"$idpratica\">	
			<INPUT class=\"printhide\" name=\"modifica\"  TYPE=\"image\" SRC=\"images/calcolapicc.gif\" >				
		</form>	";
	}

	$numrows=$tabella_cmonet->set_dati("pratica=$idpratica");//vedo se c'Ãš un record nella tabella dei totali
	if ($numrows){
		$tabella_cmonet->set_titolo("Corrispettivo Monetario","modifica",array("tabella"=>"monetario"));
		$tabella_cmonet->get_titolo();
		$tabella_cmonet->tabella();
	}
	else{
		$tabella_cmonet->set_titolo("Corrispettivo Monetario","nuovo",array("tabella"=>"monetario"));
		$tabella_cmonet->get_titolo();
		print "<p><b>Nessun dato inserito</b></p>";
        print "<hr>";
        if ($tabella_oneri->editable) echo "
        <form method=\"post\" target=\"_parent\" action=\"oneri.calcolo.c_monetario.php\">	
            <input type=\"hidden\" name=\"mode\" value=\"new\">	
			<input type=\"hidden\" name=\"pratica\" value=\"$idpratica\">	
			<INPUT class=\"printhide\" name=\"modifica\"  TYPE=\"image\" SRC=\"images/calcolapicc.gif\" >				
		</form>	";
	}
	/*
	$numrows=$tabella_monetizz->set_dati("pratica=$idpratica");//vedo se c'Ãš un record nella tabella dei totali
	if ($numrows){
		$tabella_monetizz->set_titolo("Monetizzazione aree verdi e parcheggi","modifica",array("tabella"=>"monetizzazione"));
		$tabella_monetizz->get_titolo();
		$tabella_monetizz->tabella();
	}
	else{
		$tabella_monetizz->set_titolo("Monetizzazione aree verdi e parcheggi","nuovo",array("tabella"=>"monetizzazione"));
		$tabella_monetizz->get_titolo();
		print "<p><b>Nessun dato inserito</b></p>";
	}
	*/
	?>
			</td>
		</tr>
		<tr>
			<td>
				<?php
					if ($tabella_oneri->editable) print($tabella_oneri->elenco_stampe("oneri.importi"));
				?>
			</td>
		</tr>
	</table>
			
<?}?>
</body>
</html>
