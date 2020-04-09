<?php
$command = escapeshellcmd('E:/Learning Coding/PHP/ReadPythonFromPHP/main.py');
//or
//$command = escapeshellcmd("C:/Users/prnsoft/AppData/Local/Programs/Python/Python36/python.exe E:/Learning_Coding/PHP/ReadPythonFromPHP/main.py");
//or
//$command = escapeshellcmd("E:/Learning_Coding/PHP/ReadPythonFromPHP/dist/main/main.exe");
$output = exec($command, $out, $status);
echo $output;
if($status == 0)
{
    echo "succeed";
}
elseif($status == 1)
{
    echo "Not Succeed";
}
?>