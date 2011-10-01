<html>
<head>
<link href="tln.css" type="text/css" rel="stylesheet">
</head>
<body>
<div id="wordSelector">
<form action="index.php">
	<ul><li><input type="text" name="search"></input>
	<INPUT type="submit" value="search"></input>
<?php
require_once('tln-config.php');
require_once('TlnData.php');

$db = new mysqli(TLNDBHOST, TLNDBUSER, TLNDBPASS, TLNDBNAME);
if (mysqli_connect_errno()) {
	die('Database connection error: ' . mysqli_connect_error() . '  Please check \'tfx-config.php\'');
}

function get_wordlist($db, $word) {
	$result = '<ul>';
	$tln = new TlnData($db);
	$sql = 'select word
			from tln_word
			where word like \'' . strtolower($word) . '%\' and word regexp \'^[[:alnum:]]+$\'
			union
			select word
			from tln_word
			where word like \'' . strtolower($word) . '%\' 
			union
			select word
			from tln_word
			where word like \'%' . strtolower($word) . '%\' and word regexp \'^[[:alnum:]]+$\'
			union
			select word
			from tln_word
			where word like \'%' . strtolower($word) . '%\'
			limit 10';
	if ($stmt = $db->prepare($sql)) {
		$stmt->execute();
		$stmt->store_result();
		$stmt->bind_result($suggestion);
		while ($stmt->fetch()) {
			$my_params = array('view' => 'detail', 'word' => $suggestion);
			$result .= '<li><a href="index.php' . $tln->h2q($my_params) . '">' . $suggestion . "</a></li>\n";	
		}
		$result .= '</ul>';
		return $result;
	}
	return false;
}

if (array_key_exists('search', $_GET)) {
	print get_wordlist($db, $_GET['search']);
}
?>
</li></ul>
</form>
</div><!-- end srcSelector -->
</body></html>