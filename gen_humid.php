#!/usr/bin/php5
<?php #Script for generating humidity graphs with graphs.php
require_once("graphs.php");
genHumidGraph(1600, 800, 5, "humid5", $link);
genHumidGraph(1600, 800, 15, "humid15", $link);
?>
