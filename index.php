<?php

function default_view($tln) {
	$result = $tln->get_view();
	print "<html><body><table><tbody> 
		<thead>
	    <tr>
	      <th>Date Time</th>
	      <th>Host</th>
	      <th>Log Source</th>
	      <th>L2T Format</th>
	      <th>L2T Version</th>
	      <th>M</th>
	      <th>A</th>
	      <th>C</th>
	      <th>B</th>
	      <th>Total</th>
	    </tr>
	  	</thead>\n";
	foreach ($result as $daterow) {
		print '<tr><td rowspan="' . count($daterow[1]) . '" ><a href="' . $tln->h2q($daterow[2]) . '" >' . $daterow[0] . '</a> ';
		$my_params = $daterow[2];
		$my_params['view'] = 'detail';
		print '<a href="' . $tln->h2q($my_params) . '" >(details)</a></td>';
		foreach ($daterow[1] as $sourcerow) {
			print '<td>' . $sourcerow[5] . '</td>';
			print '<td><a href="' . $tln->h2q($sourcerow[2]) . '" >' . $sourcerow[0] . '</a></td>';
			print '<td>' . $sourcerow[4] . '</td>';
			print '<td>' . $sourcerow[3] . '</td>';
			if (array_key_exists('M', $sourcerow[1])) {
				print '<td><a href="' . $tln->h2q($sourcerow[1]['M'][1]) . '" >' . $sourcerow[1]['M'][0] . '</a></td>';
			} else {
				print '<td>0</td>';
			}
			if (array_key_exists('A', $sourcerow[1])) {
				print '<td><a href="' . $tln->h2q($sourcerow[1]['A'][1]) . '" >' . $sourcerow[1]['A'][0] . '</a></td>';
			} else {
				print '<td>0</td>';
			}
			if (array_key_exists('C', $sourcerow[1])) {
				print '<td><a href="' . $tln->h2q($sourcerow[1]['C'][1]) . '" >' . $sourcerow[1]['C'][0] . '</a></td>';
			} else {
				print '<td>0</td>';
			}
			if (array_key_exists('B', $sourcerow[1])) {
				print '<td><a href="' . $tln->h2q($sourcerow[1]['B'][1]) . '" >' . $sourcerow[1]['B'][0] . '</a></td>';
			} else {
				print '<td>0</td>';
			}
			if (array_key_exists('total', $sourcerow[1])) {
				print '<td><a href="' . $tln->h2q($sourcerow[1]['total'][1]) . '" >' . $sourcerow[1]['total'][0] . '</a></td>';
			} else {
				print '<td>0</td>';
			}
			print "</tr>\n";
		}
	}
	print "</tbody></table></body></html>\n";
}

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
	if (array_key_exists('view', $_GET)) {
		if ($_GET['view'] == 'detail') {
			$result = $tln->get_detail_view($_GET);
			print "<html><body><table><thead>
	    <tr>
	      <th>Count</th>
	      <th>Date</th>
	      <th>Time</th>
	      <th>MACB</th>
	      <th>Source</th>
	      <th>Sourcetype</th>
	      <th>Type</th>
	      <th>User</th>
	      <th>Host</th>
	      <th>Short</th>
	      <th>Description</th>
	      <th>Version</th>
	      <th>Filename</th>
	      <th>Inode</th>
	      <th>Notes</th>
	      <th>Format</th>
	      <th>Extra</th>
	    </tr>
	  	</thead><tbody>\n";
			foreach ($result as $row) {
				/* $macb, $count, gmdate("%m/%d/%Y", strtotime($oldkeys[6])), $oldkeys[7], $source, $sourcetype,
						$user, $host, $short, $description, $version, $filename,
						$inode, $notes, $format, $extra */
				$macb = $row[0];
				print '<tr><td>' . implode('</td><td>', $row[1]) . "</td>\n";
				print '<td>' . $tln->get_macb($macb) . '</td>';
				print '<td>' . implode('</td><td>', $row[2]) . "</td></tr>\n";				
			}
			print "</tbody></table></body></html>\n";
		}
	} else 
	default_view($tln);
}

$db->close();
?>