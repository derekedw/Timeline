<?php

require_once('tln-config.php');
require_once('TlnData.php');

$db = new mysqli(TLNDBHOST, TLNDBUSER, TLNDBPASS, TLNDBNAME);
if (mysqli_connect_errno()) {
	die('Database connection error: ' . mysqli_connect_error() . '  Please check \'tfx-config.php\'');
}

$tln = new TlnData($db);
$input = file_get_contents('php://input');
if ($input) {
	$xmlDoc = new DOMDocument();
	$xmlDoc->loadXML(@$input);
	$nodes = $xmlDoc->getElementsByTagName("Data");
	if ($nodes->length > 0) {
		set_time_limit(120);
		$tln->import($nodes->item(0)->textContent);
	}
} else {
	$result = $tln->get();
	print "<html><body><table><tbody> 
		<thead>
	    <tr>
	      <th>Date Time</th>
	      <th>Log Source</th>
	      <th>M</th>
	      <th>A</th>
	      <th>C</th>
	      <th>B</th>
	      <th>Total</th>
	    </tr>
	  	</thead>\n";
	foreach ($result as $daterow) {
		print '<tr><td rowspan="' . count($daterow[1]) . '" ><a href="' . $tln->h2q($daterow[2]) . '" >' . $daterow[0] . '</a></td>';
		foreach ($daterow[1] as $sourcerow) {
			print '<td><a href="' . $tln->h2q($sourcerow[2]) . '" >' . $sourcerow[0] . '</a></td>';
			if (array_key_exists('M', $sourcerow[1])) {
				print '<td><a href="?' . $tln->h2q($sourcerow[1]['M'][1]) . '" >' . $sourcerow[1]['M'][0] . '</a></td>';
			} else {
				print '<td>0</td>';
			}
			if (array_key_exists('A', $sourcerow[1])) {
				print '<td><a href="?' . $tln->h2q($sourcerow[1]['A'][1]) . '" >' . $sourcerow[1]['A'][0] . '</a></td>';
			} else {
				print '<td>0</td>';
			}
			if (array_key_exists('C', $sourcerow[1])) {
				print '<td><a href="?' . $tln->h2q($sourcerow[1]['C'][1]) . '" >' . $sourcerow[1]['C'][0] . '</a></td>';
			} else {
				print '<td>0</td>';
			}
			if (array_key_exists('B', $sourcerow[1])) {
				print '<td><a href="?' . $tln->h2q($sourcerow[1]['B'][1]) . '" >' . $sourcerow[1]['B'][0] . '</a></td>';
			} else {
				print '<td>0</td>';
			}
			if (array_key_exists('total', $sourcerow[1])) {
				print '<td><a href="?' . $tln->h2q($sourcerow[1]['total'][1]) . '" >' . $sourcerow[1]['total'][0] . '</a></td>';
			} else {
				print '<td>0</td>';
			}
			print '</tr>';
		}
	}
	print "</tbody></table></body></html>\n";
}

$db->close();
?>