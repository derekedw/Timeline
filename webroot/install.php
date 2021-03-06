<?php
include 'header.php';
require_once('tln-config.php');
require_once('TlnData.php');
require_once('Job.php');

$db = new mysqli(TLNDBHOST, TLNDBUSER, TLNDBPASS, TLNDBNAME);

if (mysqli_connect_errno()) {
	die('Connect error: ' . mysqli_connect_error());
}

?>
<div id="groupform">
<?php 

$tln = new TlnData($db);
if ($tln->has_tables(TLNDBNAME)) {
	if ($tln->has_upgrade()) {
		if (array_key_exists('choice', $_POST) && $_POST['choice'] == "Yes") {
			$tln->do_upgrade();
		} else {
			?>
			<h1>The code base was updated to version <?php print $tln->get_code_version(); ?>, but the database is at version <?php print $tln->get_db_version(); ?></h1>
			<p>Would you like to upgrade?</p>
			<?php $tln->get_upgrade_info(); ?>
			<form method="post" action="install.php">
				<input type="submit" name="choice" VALUE="Yes">
				<input type="submit" name="choice" VALUE="No">
			 </form>
			<?php 
		}
	} else {
		print $tln->h1("This database is already up to date with the installed Tapestry code");
		print $tln->p("Check periodically for updated code at <a href=\"http://github.com/derekedw/Timeline\">http://github.com/derekedw/Timeline</a>");
	}
} else {
	if (Job::create($db)) {
		$job = Job::get_new();
		if (!$tln->create_db($job)) 
			print $tln->h1('Install unsuccessful');
	}
}
$db->close();
?>
</div>
<?php 
include 'footer.php';	
?>