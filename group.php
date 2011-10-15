<?php include 'header.php'; ?>
<form action="group.php">
<div id="groupform">
<div id="groupinput">
<p>Group name:</p>
<input type="text" id="name" name="name"><br>
<p>Group description:</p>
<textarea rows="8" cols="32"></textarea><br>
<p>Start date:</p>
<input id="startDate" name="startDate" size="10" maxlength="10" type="text">
<img src="calendar.gif" onclick="showChooser(this, 'startDate', 'chooserSpan', 1970, 2020, 'Y-m-d', false);"> 
<div id="chooserSpan" class="dateChooser select-free" style="display: none; visibility: hidden; width: 160px;">
</div>

<p>End date:</p>
<input id="endDate" name="endDate" size="10" maxlength="10" type="text">
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
	print '<tr><td class="group' . $i . '"><img src="1x1.png" width="30" height="20"></td></tr>';	
}
?>
</tbody>
</table>
</div>
<div id="buttons">
<input type="submit" value="ok"><input type="reset" value="cancel"><br />
</div>
</div>
</form>
<?php include 'footer.php'; ?>