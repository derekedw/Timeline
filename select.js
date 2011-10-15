/**
 * 
 */
var selected = new Array();
function selectRecord(tableRow,factId) {
	if (tableRow.className.match("depresse") == null) {
		tableRow.className = 'depresse' + tableRow.className;
	} else { 
		tableRow.className = tableRow.className.replace("depresse","");
	}
	selected.push(factId);
}