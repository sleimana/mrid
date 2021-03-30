' --- Desc: This macro will send The Reperlibilita data to the Mr. ID Robot
' --- Author: Sleiman A.
' --- SOC:

Private Function pvPostFile(sFileName As String, Optional ByVal bAsync As Boolean) As String
    Const STR_BOUNDARY  As String = "3fbd04f5-b1ed-4060-99b9-fca7ff59c113"
    Dim nFile           As Integer
    Dim baBuffer()      As Byte
    Dim sPostData       As String
 
    '--- read file
    nFile = FreeFile
    Open sFileName For Binary Access Read As nFile
    If LOF(nFile) > 0 Then
        ReDim baBuffer(0 To LOF(nFile) - 1) As Byte
        Get nFile, , baBuffer
        sPostData = StrConv(baBuffer, vbUnicode)
    End If
    Close nFile
    '--- prepare body
    sPostData = "--" & STR_BOUNDARY & vbCrLf & _
        "Content-Disposition: form-data; name=""file""; filename=""" & Mid$(sFileName, InStrRev(sFileName, "\") + 1) & """" & vbCrLf & _
        "Content-Type: application/octet-stream" & vbCrLf & vbCrLf & _
        sPostData & vbCrLf & _
        "--" & STR_BOUNDARY & "--"
        
    '--- prepare data
    Dim b
    With CreateObject("Microsoft.XMLDOM").createElement("b64")
        .DataType = "bin.base64": .Text = "aHR0cDovL3NsZWltYW4uZXUu..c2V0X3lvdXJfcHl0aG9uX2VuZHBvaW50X3VybF9oZXJl"
        b = .nodeTypedValue
        With CreateObject("ADODB.Stream")
            .Open: .Type = 1: .Write b: .Position = 0: .Type = 2: .Charset = "utf-8"
            sUrl = .ReadText
            .Close
        End With
    End With
    
    '--- post
    With CreateObject("Microsoft.XMLHTTP")
        .Open "POST", sUrl, bAsync
        .SetRequestHeader "Content-Type", "multipart/form-data; boundary=" & STR_BOUNDARY
        .Send pvToByteArray(sPostData)
        If Not bAsync Then
            pvPostFile = .ResponseText
        End If
    End With
End Function
 
Private Function pvToByteArray(sText As String) As Byte()
    pvToByteArray = StrConv(sText, vbFromUnicode)
End Function

Function pvFileExists(ByVal FileToTest As String) As Boolean
   FileExists = (Dir(FileToTest) <> "")
End Function

Sub pvDeleteFile(ByVal FileToDelete As String)
   If pvFileExists(FileToDelete) Then
      '--- remove readonly attribute, if set
      SetAttr FileToDelete, vbNormal
      Kill FileToDelete
   End If
End Sub

Sub Button2_Click()
    
    sFile = Application.ActiveWorkbook.Path & "\rep_sparkle.tmp"
    
    answer = MsgBox("Do you want to send data to the Robot?", vbQuestion + vbYesNo + vbDefaultButton2, "Confirmation!")
    If answer = vbYes Then
        
        '--- build file and send it
        Dim myFile As String, rng As Range, cellValue As Variant, i As Integer, j As Integer
        myFile = sFile
        Set rng = Selection
        Open myFile For Output As #1
        For i = 1 To rng.Rows.Count
            For j = 1 To rng.Columns.Count
                cellValue = rng.Cells(i, j).Text
                If j = rng.Columns.Count Then
                    Print #1, cellValue
                Else
                    Print #1, cellValue,
                End If
            Next j
        Next i
        Close #1
        
        MsgBox (pvPostFile(myFile))
        pvDeleteFile (myFile)
    Else
        MsgBox ("No changes to the Robot")
    End If
End Sub
