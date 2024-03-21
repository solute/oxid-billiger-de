[{assign var="moduleUrl"          value=$oViewConf->getModuleUrl('oxid_solute')}]
[{assign var="fieldSelectionList" value=$oView->getFieldSelection()}]
[{assign var="shopUrl"            value=$oViewConf->getShopUrl()}]
[{assign var="imagePath"          value=$moduleUrl|cat:'out/src/img/icon/'}]

<!doctype html>
<html lang="de">
    <head>
        <link rel="stylesheet" type="text/css" href="[{$moduleUrl}]out/src/css/ajaxCore.css" />
        <link rel="stylesheet" type="text/css" href="[{$moduleUrl}]out/src/css/ajaxArticleList.css" />
        <link rel="stylesheet" type="text/css" href="[{$moduleUrl}]out/src/css/ajaxFieldDefinition.css" />
    </head>

    <body>
        <input id="moduleUrl" type="hidden" value="[{$moduleUrl}]" />
        <input id="shopUrl" type="hidden" value="[{$shopUrl}]" />
        <input id="shopId" type="hidden" value="[{$oView->getShopId()}]" />

        <dialog id="modalWindow">
            <div>
                <input id="translationSubmitButtonLabel" type="hidden" value="[{oxmultilang ident="UMSOLUTE_BUTTON_SUBMIT"}]" />
                <a onclick="closeModal();" title="[{oxmultilang ident="UMSOLUTE_MODAL_CLOSE"}]" class="close">X</a>
                <div id="modalContent"></div>
            </div>
        </dialog>

        <div id="bodyDiv"></div>

        <div id="content">
            <h1>[{oxmultilang ident="UMSOLUTE_TITLE_EDIT_FIELD_DEFINITION"}]</h1>

            <div>

                <div class="list-row">
                    <div></div>
                    <div></div>
                    <div class="listHeader">[{oxmultilang ident="UMSOLUTE_COL_LABEL_ATTRIBUTE_GROUP"}]</div>
                    <div class="listHeader">[{oxmultilang ident="UMSOLUTE_COL_LABEL_FIELD_NAME"}]</div>
                    <div></div>
                </div>

                <div class="list-row">
                    <div class="listValidateButton"></div>
                    <div class="listValidateButton" onclick="openModal();">
                        <img src="[{$imagePath}]plus-solid.svg" class="icon-button" />
                    </div>
                    <div class="listItem"></div>
                    <div class="listItem"></div>
                    <div></div>
                </div>

                [{foreach from=$fieldSelectionList item=row}]
                    <div class="list-row" id="definition_[{$row.OXID}]">
                        <div class="listValidateButton" onclick="deleteDefinition('[{$row.OXID}]')">
                            <img src="[{$imagePath}]trash-can.svg" class="icon-button" />
                        </div>
                        <div class="listValidateButton" onclick="openModal('[{$row.OXID}]');">
                            <img src="[{$imagePath}]pencil.svg" class="icon-button" />
                        </div>
                        <div class="listItem">
                            [{$row.UM_TITLE}]
                        </div>
                        <div class="listItem">
                            [{$row.UM_FIELD_TITLE}]
                        </div>
                        <div></div>
                    </div>
                [{/foreach}]

            </div>
        </div>

    </body>

    <script src="[{$moduleUrl}]out/src/js/jquery.min.js"></script>
    <script src="[{$moduleUrl}]out/src/js/ajaxEditDefinition.js"></script>
</html>