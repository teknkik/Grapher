<?php
error_reporting(E_ALL ^ E_NOTICE);
include("pChart/class/pData.class.php");
include("pChart/class/pDraw.class.php");
include("pChart/class/pImage.class.php");

$db = mysqli_connect("localhost","pi","rasbian","pi");

function fixTime($time) {
	$e = explode(":",$time);
	$h = intval($e[0]);
	$m = intval($e[1]);

	if($h < 10) { $h = "0".$h; }
	if($m < 10) { $m = "0".$m; }

	return $h.":".$m;
}

function jGraphTemp($x, $y, $minutes, $filename, $link, $days, $output) {

	$query = "SELECT timestamp,temp,humidity FROM lukemat WHERE timestamp > NOW() - interval ".$days." day";
	$res = mysqli_query($link,$query);

	$time = array();
	$temp = array();
	$humid = array();

	$data = new pData();

	while ($row = mysqli_fetch_array($res)) {
		$time[] = substr($row['0'],11,5);
		$hour = substr($row['0'],11,2);
		$minute = substr($row['0'],14,2);
		$id = substr($row['0'],0,10);
		$rounded = round(intval($minute)/$minutes)*$minutes;
		if($rounded == 60) { $rounded = 0; $hour += 1; }
		$fixedTime = fixTime($hour.":".$rounded);
		if($days > 1) {
			$fixedTime = $id." ".$fixedTime;
		}
		if(!$timehours[$fixedTime])	{
			$timehours[$fixedTime] = array();
		}
		array_push($timehours[$fixedTime],$row['1']);

		$randbg = rand(41,47);
		$randcolor = rand(30,37);

		if($randbg%10 == $randcolor%10) {
			$randcolor = 30;
		}

		if(!$output) echo chr(27)."[".$randbg.";".$randcolor."m".$fixedTime." ".$rounded;
	}

	$newdata = array();
	$newtime = array();
	$counter = 1;
	foreach($timehours as $hour=>$tbl) {
		$count = count($tbl);
		$average = array_sum($tbl)/$count;
		//echo "Average for ".$hour.": ".$average."\n";
		$newdata[] = $average;
		$newtime[] = ($counter%2 == 0) ? $hour : "";
		$counter++;
	}

	$data->addPoints($newtime,"Timestamp");
	$data->addPoints($newdata, "Temperature");

	$data->setAbscissa("Timestamp");
	$data->setXAxisName("Time");

	$data->setAxisName(0,"");
	$data->setAxisUnit(0, "C");

	$data->setSerieWeight("Temperature",2);

	$pic = new pImage($x,$y,$data);
	$pic->setGraphArea(40,15,$x-60,$y-35);

	$pic->setfontProperties(array("FontName"=>"/home/pi/cron/pChart/fonts/verdana.ttf","FontSize"=>11));

	$AxisBoundaries = array(0=>array("Min"=>min($newdata)-5,"Max"=>max($newdata)+5));
	$scaleSettings = array("LabelSkip"=>floor(count($newdata)/20),"GridR"=>200, "GridG"=>200, "GridB"=>200,"DrawSubTicks"=>TRUE, "CycleBackground"=>TRUE,"Mode"=>SCALE_MODE_MANUAL,"ManualScale"=>$AxisBoundaries);
	$pic->drawScale($scaleSettings);
	$pic->drawSplineChart();

	if($output == true) {
		$pic->Stroke();
	} else {
		$pic->Render($filename);
	}

}

function jGraphHumid($x, $y, $minutes, $filename, $link, $days, $output) {

	$query = "SELECT timestamp,temp,humidity FROM lukemat WHERE timestamp > NOW() - interval ".$days." day";
	$res = mysqli_query($link,$query);

	$time = array();
	$temp = array();
	$humid = array();

	$data = new pData();

	while ($row = mysqli_fetch_array($res)) {
		$time[] = substr($row['0'],11,5);
		$hour = substr($row['0'],11,2);
		$minute = substr($row['0'],14,2);
		$id = substr($row['0'],0,10);
		$rounded = round(intval($minute)/$minutes)*$minutes;
		if($rounded == 60) { $rounded = 0; $hour += 1; }
		$fixedTime = fixTime($hour.":".$rounded);
		if($days > 1) {
			$fixedTime = $id." ".$fixedTime;
		}
		if(!$timehours[$fixedTime])	{
			$timehours[$fixedTime] = array();
		}
		array_push($timehours[$fixedTime],$row['2']);

		$randbg = rand(41,47);
		$randcolor = rand(30,37);

		if($randbg%10 == $randcolor%10) {
			$randcolor = 30;
		}

		if(!$output) echo chr(27)."[".$randbg.";".$randcolor."m".$fixedTime." ".$rounded;
	}

	$newdata = array();
	$newtime = array();
	$counter = 1;
	foreach($timehours as $hour=>$tbl) {
		$count = count($tbl);
		$average = array_sum($tbl)/$count;
		//echo "Average for ".$hour.": ".$average."\n";
		$newdata[] = $average;
		$newtime[] = ($counter%2 == 0) ? $hour : "";
		$counter++;
	}

	$data->addPoints($newtime,"Timestamp");
	$data->addPoints($newdata, "Humidity");

	$data->setAbscissa("Timestamp");
	$data->setXAxisName("Time");

	$data->setAxisName(0,"");
	$data->setAxisUnit(0, "%");

	$data->setSerieWeight("Humidity",2);

	$pic = new pImage($x,$y,$data);
	$pic->setGraphArea(40,15,$x-60,$y-35);

	$pic->setfontProperties(array("FontName"=>"/home/pi/cron/pChart/fonts/verdana.ttf","FontSize"=>11));

	$AxisBoundaries = array(0=>array("Min"=>0,"Max"=>100));
	$scaleSettings = array("LabelSkip"=>floor(count($newdata)/20),"GridR"=>200, "GridG"=>200, "GridB"=>200,"DrawSubTicks"=>TRUE, "CycleBackground"=>TRUE,"Mode"=>SCALE_MODE_MANUAL,"ManualScale"=>$AxisBoundaries);
	$pic->drawScale($scaleSettings);
	$pic->drawSplineChart();
	if($output == true) {
		$pic->Stroke();
	} else {
		$pic->Render($filename);
	}
}

function jGraphPressure($x, $y, $minutes, $filename, $link, $days, $output) {

	$query = "SELECT timestamp,temp,humidity,pressure FROM lukemat WHERE timestamp > NOW() - interval ".$days." day";
	$res = mysqli_query($link,$query);

	$time = array();
	$temp = array();
	$humid = array();

	$data = new pData();

	while ($row = mysqli_fetch_array($res)) {
		$time[] = substr($row['0'],11,5);
		$hour = substr($row['0'],11,2);
		$minute = substr($row['0'],14,2);
		$id = substr($row['0'],0,10);
		$rounded = round(intval($minute)/$minutes)*$minutes;
		if($rounded == 60) { $rounded = 0; $hour += 1; }
		$fixedTime = fixTime($hour.":".$rounded);
		if($days > 1) {
			$fixedTime = $id." ".$fixedTime;
		}
		if(!$timehours[$fixedTime])	{
			$timehours[$fixedTime] = array();
		}
		array_push($timehours[$fixedTime],$row['3']);

		$randbg = rand(41,47);
		$randcolor = rand(30,37);

		if($randbg%10 == $randcolor%10) {
			$randcolor = 30;
		}

		if(!$output) echo chr(27)."[".$randbg.";".$randcolor."m".$fixedTime." ".$rounded;
	}

	$newdata = array();
	$newtime = array();
	$counter = 1;
	foreach($timehours as $hour=>$tbl) {
		$count = count($tbl);
		$average = array_sum($tbl)/$count;
		#echo "Average for ".$hour.": ".$average."\n";
		$newdata[] = $average;
		$newtime[] = ($counter%2 == 0) ? $hour : "";
		$counter++;
	}

	$data->addPoints($newtime,"Timestamp");
	$data->addPoints($newdata, "Pressure");

	$data->setAbscissa("Timestamp");
	$data->setXAxisName("Time");

	$data->setAxisName(0,"");
	$data->setAxisUnit(0, "hPa");

	$data->setSerieWeight("Pressure",2);
	$pic = new pImage($x,$y,$data);
	$pic->setGraphArea(150,15,$x-60,$y-85);

	$pic->setfontProperties(array("FontName"=>"/home/pi/cron/pChart/fonts/verdana.ttf","FontSize"=>11));

	$AxisBoundaries = array(0=>array("Min"=>min($newdata)-10,"Max"=>max($newdata)+10));
	$scaleSettings = array("LabelSkip"=>floor(count($newdata)/20),"GridR"=>200, "GridG"=>200, "GridB"=>200,"DrawSubTicks"=>TRUE, "CycleBackground"=>TRUE, "Mode"=>SCALE_MODE_MANUAL,"ManualScale"=>$AxisBoundaries);
	$pic->drawScale($scaleSettings);
	$pic->drawSplineChart();
	if($output == true) {
		$pic->Stroke();
	} else {
		$pic->Render($filename);
	}
}



?>
