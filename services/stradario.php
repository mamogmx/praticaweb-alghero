<html>
	<head>
		<title>Stradario</title>
		<style>
			BODY { font-family:arial; }
			TABLE { border-collapse:collapse; }
			TD { border-top:2px solid black; border-bottom:2px solid black; border-left:1px solid black; border-right:1px solid black; padding:0 4 0 4px; }
			TABLE TR TD.sezione { border-color:red; }
		</style>
	</head>
	<body>
		<table style="font-size:9px; border:2px solid black; width:18cm">
			<caption style="text-align:center; padding-bottom:24px;"><div style="font-weight:bold; font-size:16pt">Stradario del Comune di Alghero</div><div style="color:gray; font-size:10pt; font-weight:normal">D.P.R. 30 maggio 1989, n. 223, art. 45</div></caption>
			<tr style="background-color:rgb(240,240,255)">
				<td rowspan="2" valign="center" align="center"><b>COD.</b></td>
				<td colspan="2" valign="center" style="text-align:center; height:1cm; border-bottom:1px solid black"><b>AREE DI CIRCOLAZIONE</b></td>
				<td colspan="3" valign="center" style="text-align:center; height:1cm; border-bottom:1px solid black"><b>NUMERI CIVICI</b></td>
				<td rowspan="2" valign="center" style="text-align:center; padding:0px"><b>NUMERI CIVICI DISTINTI SECONDO L'APPARTENENZA ALLE SINGOLE SEZIONI DI CENSIMENTO</b></td>
				<td colspan="2" valign="center" align="center"><b>UBICAZIONE DELL'AREA<br>DI CIRCOLAZIONE</b></td>
				<!--<td rowspan="2" valign="center" align="center">ANNOTAZIONI</b></td>-->
			</tr>
			<tr style="background-color:rgb(240,240,255)">
				<td style="height:17mm; border-top:1px solid black"><b>Specie</td>
				<td style="border-top:1px solid black"><b>Denominazione</td>
				<td style="border-top:1px solid black"><b>Estremi</td>
				<td style="border-top:1px solid black"><b>Ripetuti</td>
				<td style="border-top:1px solid black"><b>Mancanti</td>
				<td style="border-top:1px solid black"><b>Da</td>
				<td style="border-top:1px solid black"><b>A</td>
			</tr>
			<?
			$connessione=pg_connect("host=127.0.0.1 port=5432 dbname=sit_alghero user=postgres password=postgres");
			$sql_1="
			SELECT tpstrid, tpstrcod, initcap(specie) as specie,
				substring(
				tpstrnom,
				length(specie)+2,
				length(tpstrnom) - length(specie)
				) as denominazione, tpstrnom,
				da_via, a_via
				FROM dbt_topociv.dbt_tpstr
				WHERE tpstrid IN (SELECT tpstrid FROM dbt_topociv.dbt_civico) and stradario=1
				ORDER BY 4;";

			$query_1=pg_query($connessione,$sql_1);
			while($array_1=pg_fetch_assoc($query_1))
				{?>
				<tr>
					<td style=""><?=$array_1['tpstrcod']?></td>
					<td style=""><?=$array_1['specie']?></td>
					<td><?=$array_1['denominazione']?></td>
					<td align="center"><!-- ESTREMI -->
				<?
				// DISPARI PIU' BASSO E PIU' ALTO DI UNA STRADA
				$sql_dispari_di_una_strada=
					"SELECT civicoid, enuclasse, civicolatos, civiconum || coalesce(civicosub,'') as civico, tpstrid
					FROM dbt_topociv.dbt_civico
					where
					tpstrid=".$array_1['tpstrid']."
					and civiconum/2<>div(civiconum,2)
					order by civiconum, coalesce(civicosub,'')";
				$query_dispari_di_una_strada=pg_query($connessione,$sql_dispari_di_una_strada);
				if(pg_num_rows($query_dispari_di_una_strada)>0)
					{
					$sql_dispari_di_una_strada=pg_fetch_result($query_dispari_di_una_strada,0,civico);
					echo("<div class=\"estremi\">".$sql_dispari_di_una_strada."-");
					$ultimo_dispari='';
					while($array_dispari_di_una_strada=pg_fetch_assoc($query_dispari_di_una_strada))
						{
						$ultimo_dispari=$array_dispari_di_una_strada['civico'];
						};
					echo($ultimo_dispari."</div>");
					} else {
					echo("<div class=\"estremi\">-</div>");
					};
				
				// PARI PIU' BASSO E PIU' ALTO DI UNA STRADA
				$sql_pari_di_una_strada=
					"SELECT civicoid, enuclasse, civicolatos, civiconum || coalesce(civicosub,'') as civico, tpstrid
					FROM dbt_topociv.dbt_civico
					where
					tpstrid=".$array_1['tpstrid']."
					and civiconum/2=div(civiconum,2)
					order by civiconum, coalesce(civicosub,'')";
				$query_pari_di_una_strada=pg_query($connessione,$sql_pari_di_una_strada);
				if(pg_num_rows($query_pari_di_una_strada)>0)
					{
					$sql_pari_di_una_strada=pg_fetch_result($query_pari_di_una_strada,0,civico);
					echo("<div class=\"estremi\">".$sql_pari_di_una_strada."-");
					$ultimo_pari='';
					while($array_pari_di_una_strada=pg_fetch_assoc($query_pari_di_una_strada))
						{
						$ultimo_pari=$array_pari_di_una_strada['civico'];
						};
					echo($ultimo_pari."</div>");
					} else {
					echo("<div class=\"estremi\">-</div>");
					};
				?>
				</td>
				
				<td><!-- NUMERI RIPETUTI -->
				
				<?
				$sql3="select civiconum||coalesce(civicosub,'') as civico from
						(
						select tpstrid,civiconum,civicosub,count(*)
						from dbt_topociv.dbt_civico
						group by tpstrid,civiconum,civicosub
						) a
						where count>1 and div(civiconum,2)=civiconum/2 and tpstrid=".$array_1['tpstrid']."
						order by civiconum,coalesce(civicosub,'');";
				$query3=pg_query($connessione,$sql3);
				while($array3=pg_fetch_assoc($query3))
					{
					echo($array3['civico']." ");
					};
				echo("<br>");
				$sql4="select civiconum||coalesce(civicosub,'') as civico from
						(
						select tpstrid,civiconum,civicosub,count(*)
						from dbt_topociv.dbt_civico
						group by tpstrid,civiconum,civicosub
						) a
						where count>1 and div(civiconum,2)<>civiconum/2 and tpstrid=".$array_1['tpstrid']."
						order by civiconum,coalesce(civicosub,'');";
				$query4=pg_query($connessione,$sql4);
				while($array4=pg_fetch_assoc($query4))
					{
					echo($array4['civico']." ");
					};
				?>
				</td>
				
				<td><!-- NUMERI MANCANTI -->
				<?
				$dispari='';
				$pari='';
				$sql5="select min(civiconum),max(civiconum) from dbt_topociv.dbt_civico where div(civiconum,2)<>civiconum/2 and tpstrid=".$array_1['tpstrid'].";";
				$query5=pg_query($connessione,$sql5);
				while($array5=pg_fetch_assoc($query5))
					{
					for ($i = (int) $array5['min']; $i <= (int) $array5['max']; $i=$i+2)
						{
						$sql6="select coalesce(civiconum::text,'') as civico from dbt_topociv.dbt_civico where tpstrid=".$array_1['tpstrid']." and civiconum=".$i.";";
						$query6=pg_query($connessione,$sql6);
						if (pg_num_rows($query6)==0 and $i<>0)
							{
							$dispari.=$i." ";
							};
						};
					};
				$sql7="select min(civiconum),max(civiconum) from dbt_topociv.dbt_civico where div(civiconum,2)=civiconum/2 and tpstrid=".$array_1['tpstrid'].";";
				$query7=pg_query($connessione,$sql7);
				while($array7=pg_fetch_assoc($query7))
					{
					for ($i = (int) $array7['min']; $i <= (int) $array7['max']; $i=$i+2)
						{
						$sql8="select coalesce(civiconum::text,'') as civico from dbt_topociv.dbt_civico where tpstrid=".$array_1['tpstrid']." and civiconum=".$i.";";
						$query8=pg_query($connessione,$sql8);
						if (pg_num_rows($query8)==0 and $i<>0)
							{
							$pari.=$i." ";
							};
						};
					};
				echo($dispari."<br>".$pari);
				?>
				</td>
				
				
				
				
				
				
				
				
				
				<td valign="top" style="width:5cm; padding:0px">
				
				<table class="sezione" border="0" style="width:100%; border-collapse:collapse; border-width:0px">
				<?
					$sql9="
						SELECT distinct sezione_2001
						FROM civici_sezioni
						WHERE strada_id=".$array_1['tpstrid']." 
						ORDER BY sezione_2001;
					";
					$query9=pg_query($connessione,$sql9);
					while($array9=pg_fetch_assoc($query9))
						{
						?>
						
							<tr>
								<td style="border-color:rgb(192,192,192); width:34%; font-style:italic; font-size:8pt; border-width:0 0 1 0px; text-align:left"><i>Sez. <?=$array9['sezione_2001']?></i></td>
								<td style='border-color:rgb(192,192,192); width:33%; font-size:10pt; border-width:0 0 1 0px; text-align:center'>
						<?
						// PRIMO NUMERO DISPARI
						$sql10="SELECT civico_id, civico_numero, civico_esponente, strada_id, sezione_2001
								FROM civici_sezioni
								WHERE civico_numero/2::numeric<>div(civico_numero,2)::numeric AND strada_id=".$array_1['tpstrid']." AND sezione_2001=".$array9['sezione_2001']."
								ORDER by 2, 3
								LIMIT 1";
						$query10=pg_query($connessione,$sql10);
						while($array10=pg_fetch_assoc($query10))
							{
							echo($array10['civico_numero'].$array10['civico_esponente']);
							};
							
						// ULTIMO NUMERO DISPARI
						$sql11="SELECT civico_id, civico_numero, civico_esponente, strada_id, sezione_2001
								FROM civici_sezioni
								WHERE civico_numero/2::numeric<>div(civico_numero,2) AND strada_id=".$array_1['tpstrid']." AND sezione_2001=".$array9['sezione_2001']."
								ORDER by 2, 3";
						$query11=pg_query($connessione,$sql11);
						while($array11=pg_fetch_assoc($query11))
							{
							$ultimo_dispari=$array11['civico_numero'].$array11['civico_esponente'];
							};
						echo("-".$ultimo_dispari);
						$ultimo_dispari='';
						echo("</td><td style='border-color:rgb(192,192,192); width:33%; font-size:10pt; border-width:0 1 1 0px; text-align:center'> ");
						$seconda_riga="";
							
						// PRIMO NUMERO PARI
						$sql12="SELECT civico_id, civico_numero, civico_esponente, strada_id, sezione_2001
								FROM civici_sezioni
								WHERE civico_numero/2::numeric=div(civico_numero,2) AND strada_id=".$array_1['tpstrid']." AND sezione_2001=".$array9['sezione_2001']."
								ORDER by 2, 3
								LIMIT 1";
						$query12=pg_query($connessione,$sql12);
						while($array12=pg_fetch_assoc($query12))
							{
							echo($array12['civico_numero'].$array12['civico_esponente']);
							};
							
						// ULTIMO NUMERI PARI
						$sql13="SELECT civico_id, civico_numero, civico_esponente, strada_id, sezione_2001
								FROM civici_sezioni
								WHERE civico_numero/2::numeric=div(civico_numero,2) AND strada_id=".$array_1['tpstrid']." AND sezione_2001=".$array9['sezione_2001']."
								ORDER by 2, 3";
						$query13=pg_query($connessione,$sql13);
						while($array13=pg_fetch_assoc($query13))
							{
							$ultimo_pari=$array13['civico_numero'].$array13['civico_esponente'];
							};
						echo("-".$ultimo_pari);
						$ultimo_pari="";
						echo("</td></tr>");
						};
						
						
						
				?>
				</table>
				
				
				</td>
				<td valign="top">
				<? echo($array_1['da_via']) ?><br><br>
				<?/*
				$sql15="SELECT id, strada_id, strada_nome, da, a FROM inizio_fine_strade where strada_id='".$array_1['tpstrid']."';";
				$query15=pg_query($connessione,$sql15);
				while($array15=pg_fetch_assoc($query15))
							{
							echo("Da:");
							echo($array15['da']);
							echo("<br><br>");
							echo("A:");
							echo($array15['a']);
							
							};*/
				?>
				
				
				</td>
				<td valign="top"><? echo($array_1['a_via']) ?></td>
				
				
				
				
				
				
				</tr>
				<?};
			?>
		</table>
	</body>
</html>
