<?php
$command = escapeshellcmd('E:/Learning Coding/PHP/ReadPythonFromPHP/main.py');
exec($command, $out, $status);

if($status == 0)
{
    echo "succeed";
}
elseif($status == 1)
{
    echo "Not Succeed";
}
?>