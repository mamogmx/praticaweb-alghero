<?php // content="text/plain; charset=utf-8"
// Gantt example
require_once 'login.php';
 //ini_set('intl.default_locale', 'it_IT');
require_once (APPS_DIR.'plugins/jpgraph/jpgraph.php');
require_once (APPS_DIR.'plugins/jpgraph/jpgraph_gantt.php');

// 
// The data for the graphs
//
setlocale(LC_ALL, 'it_IT.utf8');
$dateLocale = new DateLocale(); 
// Use Swedish locale 
$dateLocale->Set('it_IT.utf8');
$data = array(
  array(0,ACTYPE_GROUP,     "Tempo Totale",	"2011-12-13","2012-02-12"),
  array(1,ACTYPE_NORMAL,    "Istruttoria Preliminare","2011-12-13","2011-12-30"),
  array(2,ACTYPE_NORMAL,    "Istruttoria Tecnica",      "2011-12-30","2012-01-18"),
  array(3,ACTYPE_NORMAL,    "Istruttoria Amministrativa", "2012-01-18","2012-01-30") ,
  array(4,ACTYPE_MILESTONE, "Rilascio Titolo", "2012-02-09",'RILASCIO TITOLO'));

// Create the basic graph
$graph = new GanttGraph();
$graph->title->Set("Scadenze Pratica n° 0125/2012");

// Setup scale
//$graph->ShowHeaders(GANTT_HYEAR | GANTT_HMONTH | GANTT_HDAY | GANTT_HWEEK);
$graph->scale->week->SetStyle(WEEKSTYLE_FIRSTDAY);

// Add the specified activities
$graph->CreateSimple($data);

// .. and stroke the graph
$graph->Stroke();
//phpinfo();

?>


