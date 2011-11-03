<?php

function summary_view($tln, $params) {
	$run_once = false;
	if (count($result = $tln->get_view($params)) <= 0)
		return false;
	print "<div id=\"contentArea\"><div id=\"report\"><table><tbody> 
		<thead>
	    <tr>
	      <th>Date Time</th>
	      <th>Host</th>
	      <th>Log Source Type</th>
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
		$i=0;
		foreach ($result as $daterow) {
			$my_params=$daterow[2][0];
			if ( ! $run_once) {
				print "<tbody>\n";
				$my_params['go'] = 'forward';
				$my_params['date'] = $daterow[0][0];
				$my_params['time'] = $daterow[0][1];
				print '<tr><td colspan="11"><a href="' . h2q($my_params) . '" >continue</a></tr>';
				$run_once = true;
			}
			print '<tr class="d' . $i%2 . '"><td rowspan="' . count($daterow[1]) . '" >' . $daterow[0][0] . ' ' . $daterow[0][1] .
				' <a href="' . h2q($daterow[2][1]) . '" >[+]</a> ' .
				'<a href="' . h2q($daterow[2][2]) . '" >[-]</a> ' .
				'<a href="' . h2q($daterow[2][3]) . '" >[details]</a></td>';
			$firstrow=true;
			foreach ($daterow[1] as $sourcerow) {
				if (!$firstrow)
					print '<tr class="d' . $i%2 . '">';
				foreach ($sourcerow[1] as $column) {
					print '<td><a href="' . h2q($column[1]) . '" >' . $column[0] . '</a></td>';
				}
				foreach ($sourcerow[0] as $column) {
					print '<td style="text-align:right;"><a href="' . h2q($column[1]) . '" >' . $column[0] . '</a></td>';
				} 
				print "</tr>\n";
				$firstrow=false;
			}
			$i++;
		}
		$my_params=$daterow[2][0];
		$my_params['go'] = 'backward';
		$my_params['date'] = $daterow[0][0];
		$my_params['time'] = $daterow[0][1];
		print '<tr><td colspan="11"><a href="' . h2q($my_params) . '" >continue</a> ';
		print "</tbody></table>\n";
	} else {
		print $tln->h1("No data");
	}
	print "</div></div>\n";
}

function detail_view($tln, $params) {
	$result = $tln->get_detail_view($params);
	$run_once = false;
	if (count($result) > 0) {
		print "<div id=\"report\"><table><thead>
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
		$i=0;
		foreach ($result as $row) {
			if ( ! $run_once) {
				print "<tbody>\n";
				$my_params=$params;
				$my_params['go'] = 'forward';
				$my_params['date'] = $row[1][1];
				$my_params['time'] = $row[1][2];
				print '<tr><td colspan="17"><a href="' . h2q($my_params) . '" >continue</a>';
				print '						<a href="javascript:addSelected(\'' . h2q($params) . '\');">add to a group</a>';
				$my_params = $params;
				$my_params['view'] = 'report';
				print '                     <a href="' . h2q($my_params) . '">report</a>';
				$run_once = true;
			}
			$macb = $row[0];
			print '<tr class="';
			if ($row[4] != null)
				print 'group' . $row[4];
			else 
				print 'd' . $i%2;
			print '" onclick="selectRecord(this,' . $row[3] . ',false);"><td>' . implode('</td><td>', $row[1]) . "</td>\n";
			print '<td>' . $tln->get_macb($macb) . '</td>';
			print '<td>' . implode('</td><td>', $row[2]) . "</td></tr>\n";		
			$i++;		
		}		
		$my_params['go'] = 'backward';
		$my_params['date'] = $row[1][1];
		$my_params['time'] = $row[1][2];
		print '<tr><td colspan="17"><a href="' . h2q($my_params) . '" >continue</a>';
		unset($my_params['go']);
		unset($my_params['date']);
		unset($my_params['time']);
		print '						<a href="javascript:addSelected(\'' . h2q($params) . '\');">add to a group</a>';
		$my_params = $params;
		$my_params['view'] = 'report';
		print '                     <a href="' . h2q($my_params) . '">report</a>';
		print "</tbody></table>\n";
	} else {
		print $tln->h1("No data");
	}
	print "</div>\n";
}
function report_view($tln) {
	$result = $tln->get_report_view();
	print '<div id="report"><table><thead><tr>
		  <th>Count</th>
		  <th>Date</th>
		  <th>Time</th>
		  <th>MACB</th>
		  <th>Host</th>
		  <th>Source</th>
		  <th>Sourcetype</th>
		  <th>Type</th>
		  <th>User</th>
		  <th>Description</th> 
		  <th>Inode</th>
		  <th>Notes</th>
		  <th>Extra</th>
		</tr></thead><tbody>';
	$oldgrpname  = '';
	$oldgrpdesc  = '';
	foreach ($result as $row) {
		if ($oldgrpname != $row[0]) {
			print '<tr class="group' . $row[5] . '"><td colspan="4">' . $row[0] . '</td><td colspan="9">' . $row[1] . "</td></tr>\n";
			$oldgrpname  = $row[0];
			$oldgrpdesc  = $row[1];
		}
		print '<tr class="group' . $row[5] . '"><td>' . implode('</td><td>', $row[3]) . "</td>\n";
		print '<td>' . implode('', $row[2]) . "</td>\n";
		print '<td>' . implode('</td><td>', $row[4]) . "</td><tr>\n";
	}
	print '</tbody></table></div>';
}

function canvas_start() {
	print "<div id=\"canvas\">\n";
}

function canvas_end() {
	print "</div>\n";
}

require_once('tln-config.php');
require_once('TlnData.php');

$db = new mysqli(TLNDBHOST, TLNDBUSER, TLNDBPASS, TLNDBNAME);
if (mysqli_connect_errno()) {
	die('Database connection error: ' . mysqli_connect_error() . '  Please check \'tfx-config.php\'');
}

$tln = new TlnData($db);
$input = file_get_contents('php://input');
if ($input) { # If information was posted, we are importing data. Output is in plain text.
	$xmlDoc = new DOMDocument();
	$xmlDoc->loadXML(utf8_decode(@$input));
	$nodes = $xmlDoc->getElementsByTagName("Data");
	if ($nodes->length > 0) {
		set_time_limit(300);
		$tln->import($nodes->item(0)->textContent);
	}
} else { # If information was NOT posted, output is in HTML.
	include 'header.php';
	include 'words.php';
	canvas_start();

	if (array_key_exists('view', $_GET)) {
		if ($_GET['view'] == 'detail') {
			detail_view($tln, $_GET);
		} else if ($_GET['view'] == 'report') {
			report_view($tln, $_GET);
		}
	} else if (array_key_exists('entries', $_GET)) {
		if (validate_list($_GET['entries']) && validate_int($_GET['color'], 0, 11)) {
			$tln->add_group($_GET['name'], $_GET['description'], $_GET['color'], $_GET['entries']);
			// ?color=1&name=USB%20mount&description=USB%20drive%20E%20mounted&entries=33640&color=1&name=USB%20mount&description=USB%20drive%20E%20mounted&entries=11309,13001,24745,33639&datezoom=0&date=2009-08&time=.
			$my_params = $_GET;
			unset($my_params['name']);
			unset($my_params['description']);
			unset($my_params['color']);
			unset($my_params['entries']);
			detail_view($tln, $my_params);
		}
	} else 
		summary_view($tln, $_GET);
	canvas_end();
	include 'footer.php';
}

$db->close();
?>