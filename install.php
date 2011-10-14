<html>
<body>
<?php
require_once('tln-config.php');
require_once('TlnData.php');
require_once('Job.php');

$db = new mysqli(TLNDBHOST, TLNDBUSER, TLNDBPASS, TLNDBNAME);

if (mysqli_connect_errno()) {
	die('Connect error: ' . mysqli_connect_error());
}

if (Job::create($db)) {
	$job = Job::get_new();
	$tln = new TlnData($db);
	if (!$tln->create_db($job)) {
		print $tln->h1('Install unsuccessful');
		$db->close();
	}
}				
?>
</body>
</html>