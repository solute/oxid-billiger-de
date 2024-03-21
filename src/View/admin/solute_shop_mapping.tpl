[{assign var="moduleUrl" value=$oViewConf->getModuleUrl('oxid_solute')}]
[{assign var="shopUrl"   value=$oViewConf->getShopUrl()}]

<!doctype html>
<html lang="de">
    <head>
        <link rel="stylesheet" type="text/css" href="[{$moduleUrl}]out/src/css/ajaxCore.css" />
        <link rel="stylesheet" type="text/css" href="[{$moduleUrl}]out/src/css/ajaxMapping.css" />
    </head>

    <body>
        <h1>[{oxmultilang ident="UMSOLUTE_TITLE_SHOP_MAPPING"}]</h1>
        <form method="post" action="[{$oView->getActionUrl()}]">
            [{$oViewConf->getHiddenSid()}]
            <input type="hidden" name="soluteModulUrl"      id="moduleUrl"  value="[{$moduleUrl}]" />
            <input type="hidden" name="shopUrl"             id="shopUrl"    value="[{$shopUrl}]" />
            <input type="hidden" name="soluteModulShopId"   id="shopId"     value="[{$oView->getShopId()}]" />
            <input type="hidden" name="saveMapping"                         value="1" />
            <input type="hidden" name="cl"                                  value="ShopMappingController" />
            <input type="hidden" name="fc"                                  value="" />

            [{assign var="result" value=$oView->getResultMessage()}]
            [{if $result}]
                <div class="result [{if $result.result}]success[{else}]error[{/if}]">[{$result.message}]</div>
            [{/if}]

            <div id="Mapping" style="display: none;"></div>

            <div class="submit"><input type="submit" value="[{oxmultilang ident="UMSOLUTE_BUTTON_SUBMIT"}]"/></div>
        </form>
    </body>

    <script src="[{$moduleUrl}]out/src/js/jquery.min.js"></script>
    <script src="[{$moduleUrl}]out/src/js/ajaxBaseMapping.js"></script>
    <script src="[{$moduleUrl}]out/src/js/ajaxShopMapping.js"></script>
</html>