/**
 * 
 */
var selected = new Array();
function selectRecord(tableRow,factId,singleSelect) {
	var oldrow;
	if (tableRow.className.match("depresse") == null) {
		tableRow.className = 'depressed ' + tableRow.className;
		if (singleSelect) {
			if (selected.length>0) {
				oldrow = document.getElementById("group" + selected[0]);
				selectRecord(oldrow,selected[0],true);
			}
			selected = [factId];
		} else
			selected.push(factId);
	} else { 
		removeElement(selected,factId);
		tableRow.className = tableRow.className.replace("depressed ","");
	}
}

function addSelected(returnParams) {
	var result="";
	var i=0;
	if (selected.length>0) {
		for (i=0;i<selected.length;i++) {
			result=result+","+selected[i];
		}
		// Remove the leading comma
		window.location.href = "group.php?selected=" + result.replace(",", "") + returnParams.replace("?", "&");
	}
}

function removeElement(array,element) {
	var i=0;
	for (i=0;i<array.length;i++) {
		if (array[i] == element)
			array.splice(i,1);
	}
}

function saveGroup(disableDates,returnParams) {
	var result="";
	var i=0;
	if (selected.length>0) {
		result = "index.php?color=" + selected[0];
		result = result + "&name=" + document.getElementById('name').value;
		result = result + "&description=" + document.getElementById('description').value;
		result = result + "&entries=" + document.getElementById('entries').value;
		if (!disableDates) {
			result = result + "&startDate=" + document.getElementById('startDate').value;
			result = result + "&endDate=" + document.getElementById('endDate').value;
		}
		// Remove the leading comma
		window.location.href = result + returnParams.replace("?", "&");
	}
}
