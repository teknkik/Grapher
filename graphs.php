<?php
echo "Authors Olli Pasananen & Riku Karvonen 2014\n";
echo "A Q&D command line script to generate graphs for the weather station system\n";
error_reporting(E_ALL ^ E_NOTICE);
require_once("jpgraph/src/jpgraph.php");
require_once("jpgraph/src/jpgraph_line.php");
require_once("jpgraph/src/jpgraph_bar.php");
require_once("jpgraph/src/jpgraph_date.php");

$link = mysqli_connect("localhost","pi","rasbian","pi") or die(mysqli_error($link));

function fixTime($time) {
	$e = explode(":",$time);
	$h = intval($e[0]);
	$m = intval($e[1]);

	if($h < 10) { $h = "0".$h; }
	if($m < 10) { $m = "0".$m; }

	return $h.":".$m;
}

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
	$fixedTime = fixTime($hour.":".$rounded);
	if(!$timehours[$fixedTime])	{
		$timehours[$fixedTime] = array();
	}
	array_push($timehours[$fixedTime],$row['1']);
	echo $fixedTime.", ";
}

$newdata = array();
$newtime = array();
foreach($timehours as $hour=>$tbl) {
	$count = count($tbl);
	$average = array_sum($tbl)/$count;
	//echo "Average for ".$hour.": ".$average."\n";
	$newdata[] = $average;
	$newtime[] = $hour;
}

//var_dump($timehours);

$graph = new Graph($x,$y);
$graph->SetScale("textlin");
$theme_class = new UniversalTheme;
$graph->SetTheme($theme_class);
$graph->img->SetAntiAliasing(false);
$graph->title->Set("Temperature (C) in the past 24 hours with ".$minutes." minute interval");
$graph->img->SetAntiAliasing();

$graph->yaxis->HideZeroLabel();
$graph->yaxis->HideLine(false);
$graph->yaxis->HideTicks(false,false);
$graph->yaxis->scale->SetAutoMin(min($newdata)-10);
$graph->yaxis->scale->SetAutoMax(max($newdata)+5);

$graph->xgrid->Show();
$graph->xgrid->SetLineStyle("solid");
$graph->xgrid->SetColor('#000000');
//$graph->xaxis->Hide();
$graph->xaxis->SetPos('min');
$graph->xaxis->title->Set("");
$graph->yaxis->title->Set("Temp: ");
$graph->xaxis->SetLabelAngle(90);

$graph->xaxis->SetTickLabels($newtime);

$lineplot = new LinePlot($newdata);
$graph->Add($lineplot);
$lineplot->SetLegend("Temperature");
$lineplot->SetWeight(5);
//$lineplot->SetBackgroundGradient('#FFFFFF','#F0F8FF',GRAD_HOR,BGRAD_PLOT);

$graph->legend->SetFrameWeight(1);
//$graph->legend->SetPos(0.5,0.98,'center','bottom');
$graph->Stroke("/home/pi/public_html/".$filename.".png");

}

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
	$fixedTime = fixTime($hour.":".$rounded);
	if(!$timehours[$fixedTime])	{
		$timehours[$fixedTime] = array();
	}
	array_push($timehours[$fixedTime],$row['2']);
}

$newdata = array();
$newtime = array();
foreach($timehours as $hour=>$tbl) {
	$count = count($tbl);
	$average = array_sum($tbl)/$count;
	$newdata[] = $average;
	$newtime[] = $hour;
	echo "Average humidity for ".$hour.": ".$average."%\n";
}

//var_dump($timehours);

$graph = new Graph($x,$y);
$graph->SetScale("datlin", 0, 100);
$theme_class = new UniversalTheme;
$graph->SetTheme($theme_class);
$graph->img->SetAntiAliasing(false);
$graph->title->Set("Relative humidity % in the past 24 hours with ".$minutes." minute interval");

$graph->img->SetAntiAliasing();
$graph->xaxis->SetLabelAngle(90);
$graph->yaxis->HideZeroLabel();
$graph->yaxis->HideLine(false);
$graph->yaxis->HideTicks(false,false);
//$graph->yaxis->scale->SetAutoMin(min($data)-10);
//$graph->yaxis->scale->SetAutoMax(max($data)+5);

$graph->xgrid->Show();
$graph->xgrid->SetLineStyle("solid");
$graph->xaxis->SetTickLabels($newtime);
$graph->xgrid->SetColor('#000000');
//$graph->xaxis->Hide();
$graph->xaxis->SetPos('min');
//$graph->xaxis->title->Set("Time: ");
//$graph->yaxis->title->Set("Relative humidity %");

//$lineplot = new LinePlot($newdata);
//graph->Add($lineplot);
//$lineplot->SetLegend("Temperature");
//$lineplot->SetWeight(5);
//$lineplot->SetBackgroundGradient('#FFFFFF','#F0F8FF',GRAD_HOR,BGRAD_PLOT);

$b1 = new BarPlot($newdata);
$graph->Add($b1);
$b1->SetColor("white");
$b1->SetFillColor("#11cccc");
$b1->SetWidth(0.9);
//$b1->value->SetFormat("%01.1f%");

$graph->legend->SetFrameWeight(1);

//$graph->legend->SetPos(0.5,0.98,'center','bottom');
$graph->Stroke("/home/pi/public_html/".$filename.".png");

}

?>

