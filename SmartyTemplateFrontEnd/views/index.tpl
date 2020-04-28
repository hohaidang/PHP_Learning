Hello


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
{html_radios name='id' options=$cust_radios
selected=$customer_id separator='<br />'}

<html>
<body>
<!-- regular submit button -->
<form action="filecalled.php">
    <input type="submit" />
</form>
</html>

<html>
{*<form action="index.php" method="post">*}
{*    <p>*}
{*        <label class="input1" for="text1">Familiy name</label>*}
{*        <input id="text1" name="{$varname}" size="60" maxlength="60" class="required" />*}
{*    </p>*}
{*    <input type=submit />*}
{*</form>*}

{*Text Box and Summit Button*}
<form action = "filecalled.php" method="post">
    <p>
        <label class="input1" for="text1">Familiy name</label>
        <input id="text1" name="text1" value="{$varname}" size="60" maxlength="60" class="required" />
    </p>
    <input type=submit />
</form>
</html>