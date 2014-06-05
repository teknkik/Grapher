#!/usr/bin/php5
<?php #A script for generating temperature grapsh with graphs.php
include("graphs.php");
genTempGraph(1600, 800, 15, "temperature15", $link);
genTempGraph(1600, 800, 5, "temperature5", $link);
?>
