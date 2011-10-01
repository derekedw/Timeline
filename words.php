<html>
<body>
<form action="words.php">
	<input type="text" name="search"></input>
	<INPUT type="submit" value="ok"></input>
</form>
</body></html>
<?php
require_once('tln-config.php');
require_once('TlnData.php');

$db = new mysqli(TLNDBHOST, TLNDBUSER, TLNDBPASS, TLNDBNAME);
if (mysqli_connect_errno()) {
	die('Database connection error: ' . mysqli_connect_error() . '  Please check \'tfx-config.php\'');
}

function get_wordlist($db, $word) {
	$tln = new TlnData($db);
	$sql = 'select word
			from tln_word
			where word like \'' . $word . '%\' and word regexp \'^[[:alnum:]]+$\'
			union
			select word
			from tln_word
			where word like \'' . $word . '%\' 
			union
			select word
			from tln_word
			where word like \'%' . $word . '%\' and word regexp \'^[[:alnum:]]+$\'
			union
			select word
			from tln_word
			where word like \'%' . $word . '%\'
			limit 10';
	if ($stmt = $db->prepare($sql)) {
		$stmt->execute();
		$stmt->store_result();
		$stmt->bind_result($word);
		$words = array();
		while ($stmt->fetch()) {
			$my_params = array('view' => 'detail', 'word' => $word);
			$result .= '<a href="index.php' . $tln->h2q($my_params) . '">' . $word . "</a><br />\n";	
		}
		return $result;
	}
	return false;
}

if (array_key_exists('search', $_GET)) {
	print get_wordlist($db, $_GET['search']);
}
?>