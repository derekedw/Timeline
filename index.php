<?php
require_once('tln-config.php');
require_once('TlnData.php');

$db = new mysqli(TLNDBHOST, TLNDBUSER, TLNDBPASS, TLNDBNAME);
if (mysqli_connect_errno()) {
	die('Database connection error: ' . mysqli_connect_error() . '  Please check \'tfx-config.php\'');
}
$xmlDoc = new DOMDocument();
$xmlDoc->loadXML(@file_get_contents('php://input'));
$nodes = $xmlDoc->getElementsByTagName("Data");
if ($nodes->length > 0) {
	set_time_limit(120);
	$tln = new TlnData($db);
	$tln->import($nodes->item(0)->textContent);
}

$db->close();
?>