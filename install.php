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
	if ($tln->create_date()) {
		if ($tln->fill_date($db)) {
			if ($tln->create_time()) {
				if ($tln->fill_time($db)) {
					if ($tln->create_version()) {
						if ($tln->create_import()) {
							if ($tln->create_source()) {
								if ($tln->create_fact()) {
									if ($tln->fill_version($db)) { 
										print $tln->h1('All done in ' . gmdate("H:i:s", time() - $job->getId()));
										$db->close();
										exit(0);
									}
								}
							}
						}
					}
				}
			}
		}
	}
}				
print $tln->h1('Install unsuccessful');
$db->close();
?>
</body>
</html>