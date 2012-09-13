Set wshshell = CreateObject("WScript.Shell")
Set oFS = CreateObject("Scripting.FileSystemObject")
Set oOutput = CreateObject("Msxml2.DOMDocument.3.0")
iMaxLines = 2000
iLineNo = 0

If Wscript.Arguments.Count = 2 Then
	Set oInput = oFS.GetFile(Wscript.Arguments(0))
	Set fInput = oInput.OpenAsTextStream(1)
	strURL = Wscript.Arguments(1)
	
	Set oEle = oOutput.createComment("Reading " & oInput.Path)
	Set oEle = oOutput.appendChild(oEle)
		
	Set oHT = oOutput.createElement("Timeline")
	Set oHT = oOutput.appendChild(oHT)
	
	Set oEle = oOutput.createElement("Filename")
	Set oEle = oHT.appendChild(oEle)
	Set oEle = oEle.appendChild(oOutput.createTextNode(oInput.Path))
	
	Set oDat = oOutput.createElement("Data")
	Set oDat = oHT.appendChild(oDat)
	
	Do While Not fInput.AtEndOfStream 
		text = fInput.readLine()
		oDat.appendChild(oOutput.createCDataSection(text & vbLf))
		iLineNo = iLineNo + 1
		If iLineNo >= iMaxLines then
			iLineNo = 0		
			Dispatch oOutput, strURL
			oHt.removeChild(oDat)
			Set oDat = oOutput.createElement("Data")
			Set oDat = oHT.appendChild(oDat)
		End If	
	Loop
	Dispatch oOutput, strURL
Else 
	WScript.Echo("Usage:  " & WScript.ScriptName & " <http_file> http://<server>[[:<port>]/<path>]/TfxHttp.php")
End If

Function Dispatch(oOutput, strUrl)
	if Not oOutput is Nothing then
		WScript.Echo "[" & FormatDateTime(now(), 2) & " " & FormatDateTime(now(), 3) & "] sending to " & strUrl
		Set oReq = CreateObject("Microsoft.XMLHTTP")
		oReq.open "POST",strURL,False
		oReq.send oOutput
		if oReq.readyState = 4 then
			WScript.Echo "[" & FormatDateTime(now(), 2) & " " & FormatDateTime(now(), 3) & "] " & oReq.statusText & vbCrLf & oReq.responsetext
		end if
	end if
End Function
