[{assign var="moduleUrl" value=$oViewConf->getModuleUrl('oxid_solute')}]
[{assign var="shopUrl"   value=$oViewConf->getShopUrl()}]

<!doctype html>
<html lang="de">
    <head>
        <link rel="stylesheet" type="text/css" href="[{$moduleUrl}]out/src/css/ajaxCore.css" />
        <link rel="stylesheet" type="text/css" href="[{$moduleUrl}]out/src/css/ajaxArticleList.css" />
        <link rel="stylesheet" type="text/css" href="[{$moduleUrl}]out/src/css/ajaxMapping.css" />
    </head>

    <body>
        <dialog id="modalWindow">
            <div>
                <input id="translationSubmitButtonLabel" type="hidden" value="[{oxmultilang ident="UMSOLUTE_BUTTON_SUBMIT"}]" />
                <a onclick="closeModal();" title="[{oxmultilang ident="UMSOLUTE_MODAL_CLOSE"}]" class="close">X</a>
                <div id="modalContent"></div>
            </div>
        </dialog>

        <div id="bodyDiv"></div>

        <div id="content">
            <h1>[{oxmultilang ident="UMSOLUTE_TITLE_ARTICLE_LIST"}]</h1>
            <input id="moduleUrl" type="hidden" value="[{$moduleUrl}]" />
            <input id="shopUrl" type="hidden" value="[{$shopUrl}]" />
            <input id="shopId" type="hidden" value="[{$oView->getShopId()}]" />
            <input id="translationButtonTitleValidate" type="hidden" value="[{oxmultilang ident="UMSOLUTE_BUTTON_TITLE_VALIDATE"}]" />
            <input id="translationButtonTitleCheck" type="hidden" value="[{oxmultilang ident="UMSOLUTE_BUTTON_TITLE_CHECK"}]" />
            <input id="translationButtonTitleDelete" type="hidden" value="[{oxmultilang ident="UMSOLUTE_BUTTON_TITLE_DELETE"}]" />
            <input id="translationButtonTitleInfo" type="hidden" value="[{oxmultilang ident="UMSOLUTE_BUTTON_TITLE_INFO"}]" />
            <input id="translationButtonTitleUpload" type="hidden" value="[{oxmultilang ident="UMSOLUTE_BUTTON_TITLE_UPLOAD"}]" />
            <input id="translationButtonTitleSuccess" type="hidden" value="[{oxmultilang ident="UMSOLUTE_BUTTON_TITLE_SUCCESS"}]" />
            <div id="CheckboxControl" class="checkboxControlContainer" style="display:none;">
                <div id="buttonValidateAll" class="btnValidateAll" onclick="validateMultipleArticle();">[{oxmultilang ident="UMSOLUTE_BUTTON_VALIDATEALL"}]</div>
                <div id="buttonValidateAll" class="btnValidateAll" onclick="checkMultipleArticleOnApi();">[{oxmultilang ident="UMSOLUTE_BUTTON_CHECKALL"}]</div>
                <div id="buttonValidateAll" class="btnValidateAll" onclick="sendMultipleArticleToApi();">[{oxmultilang ident="UMSOLUTE_BUTTON_SENDTOAPIALL"}]</div>
                <div id="buttonValidateAll" class="btnValidateAll" onclick="deleteMultipleArticleOnApi();">[{oxmultilang ident="UMSOLUTE_BUTTON_DELETEALL"}]</div>
            </div>

            <div id="ArticleList" style="display: none;"></div>
        </div>
    </body>

    <script src="[{$moduleUrl}]out/src/js/jquery.min.js"></script>
    <script src="[{$moduleUrl}]out/src/js/ajaxBaseMapping.js"></script>
    <script src="[{$moduleUrl}]out/src/js/ajaxArticleList.js"></script>
</html>