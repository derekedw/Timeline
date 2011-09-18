<html>
<body>
<?php
require_once('tln-config.php');
require_once('TlnData.php');

$db = new mysqli(TLNDBHOST, TLNDBUSER, TLNDBPASS, TLNDBNAME);

if (mysqli_connect_errno()) {
	die('Connect error: ' . mysqli_connect_error());
}

$tln = new TlnData($db);
$tln->create_date();
$tln->create_time();
$tln->create_version();

$tln->create_import();
$tln->create_source();
$tln->create_fact();

$tln->fill_date($db);
$tln->fill_time($db);
$tln->fill_version($db);

Job::create($db);

$db->close();
print 'All done!<br />';

?>
</body>
</html>