<?php

function default_view($tln, $params) {
	$run_once = false;
	if (count($result = $tln->get_view($params)) <= 0)
		return false;
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
	if (count($result) > 0) {
		foreach ($result as $daterow) {
			$my_params=$daterow[2];
			if ( ! $run_once) {
				print "<tbody>\n";
				print '<tr><td colspan="10"><a href="' . $tln->h2q($my_params[0]) . '" >continue</a></tr>';
				$run_once = true;
			}
			print '<tr><td rowspan="' . count($daterow[1]) . '" >' . $daterow[0] . 
				'<a href="' . $tln->h2q($my_params[1]) . '" >[+]</a> ' .
				'<a href="' . $tln->h2q($my_params[2]) . '" >[-]</a> ' .
				'<a href="' . $tln->h2q($my_params[3]) . '" >[details]</a></td>';
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
		$my_params=$daterow[2];
		print '<tr><td colspan="10"><a href="' . $tln->h2q($my_params[0]) . '" >continue</a> ';
		print "</tbody></table>\n";
	} else {
		print $tln->h1("No data");
	}
	print "</body></html>\n";
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
			$run_once = false;
			print "<html><body>\n";
			if (count($result) > 0) { 
				print "<table><thead>
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
		  	</thead>\n";
				foreach ($result as $row) {
					if ( ! $run_once) {
						print "<tbody>\n";
						$my_params=$_GET;
						$my_params['go'] = 'forward';
						$my_params['date'] = $row[1][1];
						$my_params['time'] = $row[1][2];
						print '<tr><td colspan="17"><a href="' . $tln->h2q($my_params) . '" >continue</a> ';
						$run_once = true;
					}
					/* $macb, $count, gmdate("%m/%d/%Y", strtotime($oldkeys[6])), $oldkeys[7], $source, $sourcetype,
							$user, $host, $short, $description, $version, $filename,
							$inode, $notes, $format, $extra */
					$macb = $row[0];
					print '<tr><td>' . implode('</td><td>', $row[1]) . "</td>\n";
					print '<td>' . $tln->get_macb($macb) . '</td>';
					print '<td>' . implode('</td><td>', $row[2]) . "</td></tr>\n";				
				}		
				$my_params['go'] = 'backward';
				$my_params['date'] = $row[1][1];
				$my_params['time'] = $row[1][2];
				print '<tr><td colspan="17"><a href="' . $tln->h2q($my_params) . '" >continue</a> ';
				print "</tbody></table>\n";
			} else {
				print $tln->h1("No data");
			}
			print "</body></html>\n";
		}
	} else 
		default_view($tln, $_GET);
}

$db->close();
?>