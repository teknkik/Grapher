#!/usr/bin/php5
<?php
include("graph.php");
jGraphPressure(2600, 1000, 15, "../public_html/pressure15.png", $db, 1, false );
jGraphPressure(2600, 1000, 5, "../public_html/pressure5.png", $db, 1, false );
?>
