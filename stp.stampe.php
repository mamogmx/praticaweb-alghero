<?//Nota conservo il tipo per poter verificere se Ãš cambiato
include_once("login.php");
include_once "./lib/tabella_h.class.php";
$tabpath="stp";
$titolo=$_SESSION["TITOLO_$idpratica"];
$usr=$_SESSION['USER_NAME'];
$idpratica=$_REQUEST["pratica"];
$tipopratica=$_POST["tipo_pratica"];
$form=$_POST["form"];
$tipo=$_POST["tipo"];
$modello=$_POST["modello"];
$file=$_POST["file"];
$azione=$_POST["azione"];
$procedimento=$_POST["procedimento"];
list($tipo,$pag)=explode(".",$form);
//print_r($_POST);
$condono=($tipo=="cn")?(1):(0);
$ce=($tipo=="ce")?(1):(0);
$cdu=($tipo=="cdu")?(1):(0);
$active_form=$form.".php";
$tab_err=array();
$hidden="hidden";
if($_POST["azione"])
	include("./db/db.stp.stampe.php");

if ($form=='ce.commissione_paesaggio') 
{
	$comm_paesaggio=1;
	$ce=0;
}

$bottone[0]="\n\t<TR>
		<TD align=\"left\"><span style=\"visibility:$hidden;margin-left:25px;\">Nome del file: </span></TD>
		<TD align=\"left\"><input type=\"text\" style=\"width:350px;visibility:$hidden;font-size:12px;\" name=\"file\" id=\"nuovo_nomefile\"value=\"$nome_file\"></TD>
	</TR>
	<TR>
		<TD align\"center\"><input type=\"button\" alt=\"Crea il documento\" value=\"Crea Documento\" class=\"hexfield1\" style=\"margin-top:10;margin-bottom:10;margin-left:20;visibility:$hidden;\" onclick=\"javascript:crea_rtf('')\"></TD>
		<TD align=\"center\" align=\"right\"><input type=\"button\" class=\"hexfield1\" style=\"width:150px;\" name=\"newmodel\" value=\"Carica nuovo modello\" onclick=\"javascript:win=window.open('stp.carica_modello.php?pratica=$idpratica&utente=$usr&procedimento=$procedimento&form=$form','Documento','Height=250,Width=530,toolbar=no,resizable=yes');win.focus();\"></TD>
	</TR>\n";
$bottone[1]="\n\t<tr>
		<td colspan=\"2\">&nbsp;</td>
	</tr>
	<tr>
		<form action=\"stp.stampe.php\" method=\"POST\" enctype=\"multipart/form-data\">
		<td>
			<input name=\"myfile\" type=\"file\" style=\"margin-left:20px;width:360px;FONT: 11px/1.3em\">			
		</td>
		<td align=\"center\">
			<input class=\"hexfield1\" type=\"submit\" name=\"azione\" value=\"Carica File\" style=\"width:100px;margin-left:53px\" onclick=\"javascript:if (myfile.value.length>0) return true;else {alert('Inserire un file da caricare');return false;}\">
			<input type=\"hidden\" name=\"pratica\" value=\"$idpratica\">
			<input type=\"hidden\" name=\"form\" value=\"$form\">
			<input type=\"hidden\" name=\"MAX_FILE_SIZE\" value=\"3000000\">
                     
                      
		</td>
		</form>
	</tr>";	


?>
<html>
<head>
<title>Gestione modelli e stampe - <?=$titolo?></title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<SCRIPT language="javascript" src="js/LoadLibs.js" type="text/javascript"></SCRIPT>

<SCRIPT language="javascript">

function home(){
	document.main.azione.value="Annulla";
	document.main.submit();
	
}
function check_radio(nome,act){
	var id=$("input[name='id']:checked").val();
	id=(!id>0)?(-1):(id);
	if(id==-1){
		alert("Seleziona un elemento.");
		return 0;
	}
	else{
		$("#id_mod").val(id);
		if (act=="elimina") return confirm('Sei sicuro di voler eliminare il modello ?');
		else
			return 1;
	}
}
function submit_form(nome,act){
	if (check_radio('id',act)==1) {
		//document.getElementById('azione').value=act;
		return true;
	}
	else {
		return false;
	}
}
</SCRIPT>
</head>	

<body onload="<?=$body_onload?>">

<?php
	include "./inc/inc.page_header.php";
    $pr=new pratica($idpratica);
    $filtro_tipopratica="(tipo_pratica='0' or '".floor((double)$pr->info['tipo']/100)."'=ANY(string_to_array(coalesce(tipo_pratica,''),',')) or '".$pr->info['tipo']."'=ANY(string_to_array(coalesce(tipo_pratica,''),',')))";
	if ($_SESSION["PERMESSI"]<=3) $array_file_tab=(!$condono)?(array("$tabpath/modelli","$tabpath/stampe_rtf","$tabpath/stampe_pdf")):(array("$tabpath/modelli_condono","$tabpath/stampehtml_condono","$tabpath/stampepdf_condono"));
	else
		$array_file_tab=(!$condono)?(array("$tabpath/modelli_usr","$tabpath/stampe_rtf","$tabpath/stampe_pdf")):(array("$tabpath/modelli_condono","$tabpath/stampehtml_condono","$tabpath/stampepdf_condono"));
	$array_titolo=array("Modelli","Documento Prodotto","Documento Finale");
	$array_param=array("modello","rtf","pdf");
	$array_filtro=array("form='$form' and $filtro_tipopratica and (proprietario='pubblico' or proprietario='$usr')","pratica=$idpratica and form='$form' and char_length(file_doc)>0","pratica=$idpratica and form='$form' and char_length(file_pdf)>0");
?>	
<H2 class="blueBanner">Gestione Stampe e Modelli</H2>

<!------------------------------------------------------------------------   MODELLI   ------------------------------------------------------------------------------------------------------------------------------------->


<TABLE cellPadding=0  cellspacing=0 border=0 class="stiletabella" width="800">
	<TR>
		<TD colspan="4">
		<?
		$tabella=new Tabella_h($array_file_tab[0],'list'); 
		$tabella->set_titolo($array_titolo[0]);
		$tabella->set_tag($array_param[0]);
			$filtro='';
			$campi=$tabella->elenco_campi;
			$campi=explode(',',$campi); 
			foreach ($campi as $i => $value) { 
				if ($value=='tipo_pratica') $filtro="tipo_pratica ilike '$tipopratica%' and ";
			}
		$numrows=$tabella->set_dati($filtro.$array_filtro[0]);

			$tabella->get_titolo();
		if ($numrows){
			$tabella->elenco();

		}
		else{
				foreach ($campi as $i => $value) { 
					if ($value=='tipo_pratica') 
						$numrows2=$tabella->set_dati($array_filtro[0]);
				} 
					if ($numrows2)
				$tabella->elenco();
					else
			print ("<p><b>Nessun documento prodotto</b></p>");        

			}

		?>
		</TD>
	</TR>
	<TR>
		<TD><hr>
			
		</TD>
	</TR>
</table>
<form method="POST" action="praticaweb.php" name="modelli" id="praticaFrm">
	<input type="hidden" name="pratica" value="<?=$idpratica?>">
	<input type="hidden" name="form" value="<?=$form?>">
	<input type="hidden" name="condono" value="<?=$condono?>">
	<input type="hidden" name="cdu" value="<?=$cdu?>">
	<input type="hidden" name="comm" value="<?=$ce?>">
	<input type="hidden" name="active_form" value="<?=$active_form?>">
	<input type="hidden" name="stampe" value="1">
	<input type="hidden" name="id" id="id_mod" value="">
	<input type="hidden" name="comm_paesaggio" value="<?=$comm_paesaggio?>">
	<input type="hidden" name="azione" id="azione" value="">
	<span id="back_btn"></span>
	<span id="print_btn"></span>
	<script>
		$('#back_btn').button({
			'label':'Indietro',
			'icons':{
				'primary':'ui-icon-circle-triangle-w'
			}
		}).click(function(){
			$("#azione").val('Annulla');
			$('#praticaFrm').submit();
		});
		$('#print_btn').button({
			'label':'Crea Documento',
			'icons':{
				'primary':'ui-icon-disk'
			}
		}).click(function(){
			$("#azione").val('Crea Documento');
			if (submit_form('id','crea')){
				$('#print_btn').unbind('click');
                $('#praticaFrm').submit();
            }
			else
				return false;
		});
	</script>
<!--	<input type="submit" name="azione" value="Crea Documento" class="hexfield1" onclick="return ">
<input type="submit" name="azione" value="Elimina Modello" class="hexfield1" onclick="return submit_form('id','elimina')">
	<input class="hexfield1" style="width:100px;margin-top:10px" type="submit" value="Annulla" >-->

</form>



</body>
</html>
