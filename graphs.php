<?php
echo "A Q&D command line script to generate graphs for the weather station system\n";
error_reporting(E_ALL ^ E_NOTICE);
require_once("jpgraph/src/jpgraph.php");
require_once("jpgraph/src/jpgraph_line.php");
require_once("jpgraph/src/jpgraph_bar.php");

$link = mysqli_connect("localhost","pi","rasbian","pi") or die(mysqli_error($link));
#Creating temperature line graphs
function genTempGraph($x, $y, $minutes, $filename, $link) {

$mdata = mysqli_query($link, "SELECT * from lukemat WHERE timestamp > now() - interval 1 day ORDER BY timestamp ASC");
$data = array();
$time = array();
$timehours = array();

while ($row = mysqli_fetch_array($mdata)) {
	$data[] = $row['1'];
	$time[] = substr($row['0'],11,5);
	$hour = substr($row['0'],11,2);
	$minute = substr($row['0'],14,2);
	$rounded = round(intval($minute)/$minutes)*$minutes;
	if($rounded == 60) { $rounded = 0; $hour += 1; }
	if(!$timehours[$hour.":".$rounded])	{
		$timehours[$hour.":".$rounded] = array();
	}
	array_push($timehours[$hour.":".$rounded],$row['1']);
}

$newdata = array();
$newtime = array();
foreach($timehours as $hour=>$tbl) {
	$avg = 0;
	$count = 0;
	foreach($tbl as $val) {
		$avg += intval($val);
		$count++;
	}
	$avg = $avg/$count;
	echo "Average: ".$avg."\n";
	$newdata[] = $avg;
	$newtime[] = $hour;
}

$graph = new Graph($x,$y);
$graph->SetScale("textlin");
$theme_class = new UniversalTheme;
$graph->SetTheme($theme_class);
$graph->img->SetAntiAliasing(false);

$graph->img->SetAntiAliasing();

$graph->yaxis->HideZeroLabel();
$graph->yaxis->HideLine(false);
$graph->yaxis->HideTicks(false,false);
$graph->yaxis->scale->SetAutoMin(min($data)-10);
$graph->yaxis->scale->SetAutoMax(max($data)+5);

$graph->xgrid->Show();
$graph->xgrid->SetLineStyle("solid");
$graph->xaxis->SetTickLabels($newtime);
$graph->xgrid->SetColor('#000000');
$graph->xaxis->SetPos('min');
$graph->xaxis->title->Set("Time: ");
$graph->yaxis->title->Set("Temp: ");

$lineplot = new LinePlot($newdata);
$graph->Add($lineplot);
$lineplot->SetLegend("Temperature");
$lineplot->SetWeight(5);
$graph->legend->SetFrameWeight(1);
$graph->Stroke("/home/pi/public_html/".$filename.".png");

}
#Creating humidity bar graphs
function genHumidGraph($x, $y, $minutes, $filename, $link) {

$mdata = mysqli_query($link, "SELECT * from lukemat WHERE timestamp > now() - interval 1 day ORDER BY timestamp ASC");
$data = array();
$time = array();
$timehours = array();

while ($row = mysqli_fetch_array($mdata)) {
	$data[] = $row['2'];
	$time[] = substr($row['0'],11,5);
	$hour = substr($row['0'],11,2);
	$minute = substr($row['0'],14,2);
	$rounded = round(intval($minute)/$minutes)*$minutes;
	if($rounded == 60) { $rounded = 0; $hour +=1 ;}
	if(!$timehours[$hour.":".$rounded])	{
		$timehours[$hour.":".$rounded] = array();
	}
	array_push($timehours[$hour.":".$rounded],$row['1']);
}

$newdata = array();
$newtime = array();
foreach($timehours as $hour=>$tbl) {
	$avg = 0;
	$count = 0;
	foreach($tbl as $val) {
		$avg += intval($val);
		$count++;
	}
	$avg = $avg/$count;
	echo "Average: ".$avg."\n";
	$newdata[] = $avg;
	$newtime[] = $hour;
}

$graph = new Graph($x,$y);
$graph->SetScale("textlin", 0, 100);
$theme_class = new UniversalTheme;
$graph->SetTheme($theme_class);
$graph->img->SetAntiAliasing(false);

$graph->img->SetAntiAliasing();

$graph->yaxis->HideZeroLabel();
$graph->yaxis->HideLine(false);
$graph->yaxis->HideTicks(false,false);

$graph->xgrid->Show();
$graph->xgrid->SetLineStyle("solid");
$graph->xaxis->SetTickLabels($newtime);
$graph->xgrid->SetColor('#000000');

$graph->xaxis->SetPos('min');
$graph->xaxis->title->Set("Time: ");
$graph->yaxis->title->Set("Relative humidity:");

$b1 = new BarPlot($newdata);
$graph->Add($b1);
$b1->SetColor("white");
$b1->SetFillColor("#11cccc");
$b1->SetWidth(0.9);
$graph->legend->SetFrameWeight(1);

$graph->Stroke("/home/pi/public_html/".$filename.".png");

}
?>

