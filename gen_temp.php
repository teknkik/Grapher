#!/usr/bin/php5

<?php
include("graph.php");
jGraphTemp(2600, 1000, 15, "../public_html/temperature15.png", $db, 1, false);
jGraphTemp(2600, 1000,  5, "../public_html/temperature5.png", $db, 1, false);
?>


