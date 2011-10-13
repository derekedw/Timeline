/**
 * 
 */
function selectRecord(tableRow) {
	if (tableRow.className.match("depresse") == null) {
		tableRow.className = 'depresse' + tableRow.className;
	} else { 
		tableRow.className = tableRow.className.replace("depresse","");
	}
}