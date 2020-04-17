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