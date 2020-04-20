
{include file="inc_head.tpl" openHead="yes"}
{foreach from=$gui->tableSet key=idx item=matrix name="initializer"}
    {assign var=tableID value=$matrix->tableID}
    {if $smarty.foreach.initializer.first}
        {$matrix->renderCommonGlobals()}
        {if $matrix instanceof tlExtTable}
            {include file="inc_ext_js.tpl" bResetEXTCss=1}
            {include file="inc_ext_table.tpl"}
        {/if}
    {/if}
    {$matrix->renderHeadSection()}
{/foreach}

</head>

<script>
    jQuery( document ).ready(function() {
        jQuery(".chosen-select").chosen({ width: "100%" });
    });


    function showtr() {
        jQuery('.link4build').hide();
        var selectVal = jQuery("#build_id option:selected").val();
        jQuery("#link_" + selectVal).show();
    }

</script>

<body>
{if $gui->printDate == ''}
    <form name="resultsTC" id="resultsTC" METHOD="POST"
          target="avoidPageRefreshWhenSendindMail"
          action="lib/results/cus_resultSuite.php?format=3&do_action=result&tplan_id={$gui->tplan_id}&tproject_id={$gui->tproject_id}&buildListForExcel={$gui->buildListForExcel}&build_id={$build_id}">
        <h1 class="title">{$gui->title|escape}</h1>

        <table>
            <tr>
                <td><label for="build"> {$labels.build}</label></td>
                <td style="width:100px">
                    <select class="chosen-select" name="build_id" id="build_id"
                            data-placeholder="{$labels.builds}" onchange="showtr();">
                        {foreach key=build_id item=buildObj from=$gui->buildInfoSet}
                            <option value="{$build_id}">{$buildObj.name|escape}</option>
                        {/foreach}
                    </select>
                </td>
            </tr>
        </table>


        {if $gui->apikey != ''}
            <input type="hidden" name="apikey" id="apikey" value="{$gui->apikey}">
        {/if}
        &nbsp;&nbsp;
        <input type="image" name="exportSpreadSheet" id="exportSpreadSheet"
               src="{$tlImages.export_excel}" title="{$labels.export_as_spreadsheet}">
    </form>

{else} print data to excel
    <table style="font-size: larger;font-weight: bold;">
        <tr><td>{$labels.title}</td><td>{$gui->title|escape}</td><tr>
        <tr><td>{$labels.date}</td><td>{$gui->printDate|escape}</td><tr>
        <tr><td>{$labels.printed_by}</td><td>{$user|escape}</td><tr>
    </table>
{/if}
</body>
</html>