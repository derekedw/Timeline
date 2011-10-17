<?php 
require_once 'functions.php';
require_once('tln-config.php');
require_once('TlnData.php');

include 'header.php';
$db = new mysqli(TLNDBHOST, TLNDBUSER, TLNDBPASS, TLNDBNAME);
if (mysqli_connect_errno()) {
	die('Database connection error: ' . mysqli_connect_error() . '  Please check \'tfx-config.php\'');
}

$dates = array();
$disableDates = false;
if (array_key_exists('selected', $_GET)) {
	if (validate_list($_GET['selected'])) {
		$tln = new TlnData($db);
		$dates = $tln->get_selection_properties($_GET['selected']);
		if ($dates['count'] > 0) {
			$disableDates = true;
		}
	}
} 
?>
<form action="group.php">
<div id="groupform">
<div id="groupinput">
<input type="hidden" id="entries" name="entries" value="<?php print $_GET['selected']; ?>">
<p>Group name:</p>
<input type="text" id="name" name="name" size="25" maxlength="25" ><br>
<p>Group description (optional):</p>
<textarea rows="8" cols="32" id="description" name="description"></textarea><br>
<p>Start date:</p>
<input id="startDate" name="startDate" size="20" maxlength="20" type="text" 
<?php
if ($disableDates) {
	print 'value="' . $dates['min'] . '" disabled="disabled"';
}
?> 
>
<img src="calendar.gif" onclick="showChooser(this, 'startDate', 'chooserSpan', 1970, 2020, 'Y-m-d', false);"> 
<div id="chooserSpan" class="dateChooser select-free" style="display: none; visibility: hidden; width: 160px;">
</div>

<p>End date:</p>
<input id="endDate" name="endDate" size="20" maxlength="20" type="text" 
<?php
if ($disableDates) {
	print 'value="' . $dates['max'] . '" disabled="disabled"';
}
?> 
>
<img src="calendar.gif" onclick="showChooser(this, 'endDate', 'chooserSpan2', 1970, 2020, 'Y-m-d', false);"> 
<div id="chooserSpan2" class="dateChooser select-free" style="display: none; visibility: hidden; width: 160px;">
</div>

</div>
<div id="colorchooser">
<p>Highlight color:</p>
<table style="width:0%;">
<tbody>
<?php
for ($i = 0; $i < 12; $i++) {
	print '<tr id="group' . $i . '" onclick="selectRecord(this,' . $i . ',true)"><td class="group' . $i . '"><img src="1x1.png" width="30" height="20"></td></tr>';	
}
?>
</tbody>
</table> 
</div>
<div id="buttons">
<input type="button" value="ok" onclick="javascript:saveGroup(<?php
$my_params=$_GET;
unset($my_params['selected']); 
unset($my_params['view']); 
print ((string) $disableDates . ', \'' . h2q($my_params) . '\''); 
?>);"><input type="reset" value="cancel"><br />
</div>
</div>
</form>
<?php 
include 'footer.php';
?>