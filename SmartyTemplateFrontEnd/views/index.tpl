Hello

<br />


<table>
{foreach from=$people key=k item=p}
    <tr style="background: {cycle values="silver, gray"};">
        <td>{$k}</td>
        <td>{$p}</td>
    </tr>
{/foreach}
</table>

<html>
<head>
    <title>Info</title>
</head>
<body>
<pre>
    User Information:
    Name: {$name}
    Address: {$address}
</pre>
</body>
</html>

{html_options name="Dang" options=$myOptions selected=$mySelect}