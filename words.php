<?php
require_once('tln-config.php');
require_once('TlnData.php');

$db = new mysqli(TLNDBHOST, TLNDBUSER, TLNDBPASS, TLNDBNAME);
if (mysqli_connect_errno()) {
	die('Database connection error: ' . mysqli_connect_error() . '  Please check \'tfx-config.php\'');
}

$sql = 'select distinct short, description from tln_fact limit 2500';
if ($stmt = $db->prepare($sql)) {
	$stmt->execute();
	$stmt->store_result();
	$stmt->bind_result($short, $description);
	$words = array();
	while ($stmt->fetch()) {
		preg_match_all('/\w+([_.@:]\w+)*/', $short . ' ' . $description, $matches); 
		foreach ($matches[0] as $input) {
			$words[$input]++;
		}			
	}
	arsort($words);
	print '<html><body><p>' . count($words) . ' words found</p><table><tbody><tr>';
	$i = 1;
	foreach (array_keys($words) as $word) {
		print '<td>' . $word . "</td><td>" . $words[$word] . "</td>";
		$i++;
		if (($i % 10) == 0)
			print "</tr>\n<tr>";
	}
	print "</tr></tbody></table></body></html>\n";
}
?>