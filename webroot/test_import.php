﻿<html>
<body>
<?php
require_once('tln-config.php');
require_once('TlnData.php');
require_once('Job.php');

$db = new mysqli(TLNDBHOST, TLNDBUSER, TLNDBPASS, TLNDBNAME);
 
if (mysqli_connect_errno()) {
	die('Connect error: ' . mysqli_connect_error());
}

$n = 1;
$tln = new TlnData($db);
$text = file('c:\Users\Derek\git\Timeline\bin\time.csv');
$input = array();
while ($text) {
	for ($i = 0; $i < 1000; $i++) 
		$input[] = array_shift($text);
	$tln->import(implode("", $input));
	$input = array();
	$n++;
}
$db->close();
print 'All done!<br />';

?>
</body>
</html>