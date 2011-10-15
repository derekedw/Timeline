<?php include 'header.php'; ?>
<form action="group.php">
<div id="groupform">
<div id="groupinput">
<p>Group name:</p><input type="text" id="name" name="name"><br>
<p>Group description:</p>
<textarea rows="8" cols="32"></textarea><br>
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
<div style="clear: both; text-align:right;">
<input type="submit" value="ok"><input type="reset" value="cancel"><br />
</div>
</div>
</form>
<?php include 'footer.php'; ?>