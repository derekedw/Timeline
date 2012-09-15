<?php
function validate_int($num, $min = -1, $max = PHP_INT_MAX) {
	if (intval($num) != $num)
		return false;
	if (intval($num) > $max)
		return false;
	if (intval($num) < $min)
		return false;
	return true;
}

function validate_date($date) {
	$ymd = explode('-', $date);
	if (validate_int($ymd[1], 1, 12) && validate_int($ymd[2], 1, 31) && validate_int($ymd[0], 1970, 2070)) 
		return true;
	return false;
}

function validate_time($time) {
	$hms = explode('-', $time);
	if (validate_int($hms[0], 0, 23) && validate_int($hms[1], 0, 59) && validate_int($hms[2], 0, 59)) 
		return true;
	return false;
}

function validate_check($item) {
	if ($item !== 'on' && $item !== 'off')
		return false;
	return true;
}
function validate_ip($ip) {
	$octets = preg_split('/\./', $ip);
	if ((count($octets) == 4) && (validate_int($octets[0], 0, 255)) 
	    && (validate_int($octets[1], 0, 255))&& (validate_int($octets[2], 0, 255))
	    && (validate_int($octets[3], 0, 255)))
		return true;
	return false;
}

function validate_dns($name) {
	$labels = preg_split('/\./', $name);
	if ((strlen($name)< 253) && (array_size($labels) < 2)) {
		foreach ($labels as $lbl) {
			if (! preg_match('/^[-a-z0-9]{1-63}$/i',$lbl))
				return false;
		}
		return true;
	} else
		return false;
}
function validate_list($list) {
	foreach (split('/,/', $list) as $id) {
		if (!validate_int($id))
			return false;
	}
	return true;
}
function h2q($my_params) {
	foreach ($my_params as $k => $v) {
			$query[] = $k . '=' . $v;
	}
	return '?' . implode('&', $query);		
}

function canvas_start() {
	print "<div id=\"canvas\">\n";
}

function canvas_end() {
	print "</div>\n";
}
?>