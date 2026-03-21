' Abre el configurador de impresion sin mostrar ventana de cmd
' Doble clic en este archivo o crea un acceso directo en el escritorio.
Dim fso, dir, exe
Set fso = CreateObject("Scripting.FileSystemObject")
dir = fso.GetParentFolderName(WScript.ScriptFullName)
exe = dir & "\print-agent.exe"
CreateObject("WScript.Shell").Run Chr(34) & exe & Chr(34) & " --config", 0, False
