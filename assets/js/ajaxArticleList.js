var shopUrl = document.getElementById('shopUrl').value;
var shopId  = document.getElementById('shopId').value;
var languageId = 1;
var listStart = 0;
var listLimit = 20;
var listCountAll = 0;
var articleListView = [];
var articleListChecked = [];
var xhr = new XMLHttpRequest();
var iconPath = shopUrl + 'out/modules/oxid_solute/img/icon/';

function getArticleList()
{
    $.ajax({
        method: "POST",
        url: shopUrl + "index.php?cl=SoluteAjaxArticleList&fnc=run",
        data: {
            shopId: shopId,
            start: listStart,
            limit: listLimit
        }
    })
        .done(function ( jsonResponse ) {
            var response = JSON.parse(jsonResponse);
            var articleList = document.getElementById('ArticleList');
            listCountAll = response['max'];
            var headline = response['headline'];
            document.getElementById('CheckboxControl').style.display = 'flex';
            articleList.innerHTML = renderTableData(response);
            articleList.style.display = 'block';
        });
}

function renderTableData(response)
{
    var html = getTableHead(response.translation.headline);
    response.data.forEach(function (row) {
        var articleId = row['OXID'];
        html += getTableGrid();
            html += '<div><input class="soluteCheckBox" type="checkbox" name="" id="checkbox_' + articleId + '" onclick="toggleOne(' + "'" + articleId + "'" + ', ' + "'" + row['OXCATNID'] + "'" + ')"></div>';
            html += '<div id="validateButton_' + articleId + '" class="listValidateButton" onclick="validateArticle(' + "'" + articleId + "'" + ', ' + "'" + row['OXCATNID'] + "'" + ')" title="' + document.getElementById('translationButtonTitleValidate').value + '"><img src="' + iconPath + 'check.svg" class="icon-button"/></div>';
            html += '<div id="checkButton_' + articleId + '" class="listValidateButton" onclick="checkSingleArticleOnApi(' + "'" + articleId + "'" + ');" title="' + document.getElementById('translationButtonTitleCheck').value + '"><img src="' + iconPath + 'magnifying-glass.svg" class="icon-button"/></div>';
            html += '<div id="deleteButton_' + articleId + '" class="listValidateButton" onclick="deleteSingleArticleOnApi(' + "'" + articleId + "'" + ');" title="' + document.getElementById('translationButtonTitleDelete').value + '"><img src="' + iconPath + 'trash-can.svg" class="icon-button"/></div>';
        if (row['UM_LOG'] === '') {
            html += '<div id="infoField_' + articleId + '" class="listItem"></div>';
        } else {
            html += '<div id="infoField_' + articleId + '" class="listValidateButton ';
            if (row['state'] === true) {
                html += 'listValidateButtonValid';
            } else {
                html += 'listValidateButtonError';
            }
            html += '" onclick="toggleInfoItem(' + "'" + articleId + "'" + ');" title="' + document.getElementById('translationButtonTitleInfo').value + '"><img src="' + iconPath + 'info.svg" class="icon-button"/></div>';
        }
            html += '<div class="listItem pointer" onclick="openModal(' + "'article'" + ', ' + "'" + articleId + "'" + ');"><img src="' + iconPath + 'pencil.svg" class="icon-pencil"/>' + row['OXARTNUM'] + '</div>';
            html += '<div class="listItem pointer" onclick="openModal(' + "'category'" + ', ' + "'" + row['OXCATNID'] + "'" + ');"><img src="' + iconPath + 'pencil.svg" class="icon-pencil"/>' + row['OXCATTITLE'] + '</div>';
            html += '<div class="listItem">' + row['OXTITLE'] + '</div>';
            html += '<div class="listItem">' + row['OXSHORTDESC'] + '</div>';
        html += '</div>';

        html += '<div id="infoLine_' + articleId + '" class="infoLine" style="display: none;">';
            html += getInfoLine(articleId, row['state'], row['UM_LOG']);
        html += '</div>';

        articleListView.push(getViewData(articleId, row['OXCATNID']));
    });

    html += getArticleInfo();
    return html;
}

function openModal(content, objectId)
{
    document.getElementById('bodyDiv').classList.add('modalBackground');
    document.getElementById('content').classList.add('content-inactive');
    getModal(content, objectId);
    var dialog = document.querySelector('dialog');
    dialog.show();
}

function closeModal()
{
    var dialog = document.querySelector('dialog');
    dialog.close();
    document.getElementById('modalContent').innerHTML = '';
    document.getElementById('bodyDiv').classList = '';
    document.getElementById('content').classList = '';
}

function getModal(content, objectId)
{
    if (content === 'article') {
        getArticleModalContent(objectId);
    } else if (content === 'category') {
        getCategoryModalContent(objectId);
    }
}

function getArticleModalContent(articleId)
{
    $.ajax({
        method: "POST",
        url: shopUrl + "index.php?cl=SoluteAjaxArticleMapping&fnc=run",
        data: {
            shopId: shopId,
            articleId: articleId
        }
    })
        .done(function ( jsonResponse ) {
            var response = JSON.parse(jsonResponse);
            var html = '';

            html += '<form id="modalDataForm" method="POST" onsubmit="saveMapping()">';
                html += '<input type="hidden" name="SoluteModuleShopId" id="shopId"     value="' + shopId + '" />';
                html += '<input type="hidden" name="SoluteArticleId"    id="articleId"  value="' + articleId + '" />';
                html += '<input type="hidden" name="SoluteObject"                       value="article" />';

                html += displayList(response);
                var labelSubmit = document.getElementById('translationSubmitButtonLabel').value;
                html += '<div class="submit"><input type="submit" value="' + labelSubmit + '" /></div>';
            html += '</form>';
            document.getElementById('modalContent').innerHTML = html;
        });
}

function getCategoryModalContent(categoryId)
{
    $.ajax({
        method: "POST",
        url: shopUrl + "index.php?cl=SoluteAjaxCategoryMapping&fnc=run",
        data: {
            shopId: shopId,
            categoryId: categoryId
        }
    })
        .done(function ( jsonResponse ) {
            var response = JSON.parse(jsonResponse);
            var html = '';

            html += '<form id="modalDataForm" method="POST" onsubmit="saveMapping()">';
            html += '<input type="hidden" name="SoluteModuleShopId" id="shopId"     value="' + shopId + '" />';
            html += '<input type="hidden" name="SoluteCategoryId"   id="categoryId" value="' + categoryId + '" />';
            html += '<input type="hidden" name="SoluteObject"                       value="category" />';

            html += displayList(response);

            html += '<div class="submit"><input type="submit" value="Speichern" /></div>';
            html += '</form>';
            document.getElementById('modalContent').innerHTML = html;
        });
}

function saveMapping()
{
    var formData = new FormData(document.getElementById("modalDataForm"));
    var targetUrl = shopUrl + 'index.php?cl=SoluteAjaxSaveMapping&fnc=run';

    xhr.open("POST", targetUrl, true);
    xhr.send(formData);
    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4 && xhr.status === 200) {
            var response = JSON.parse(xhr.responseText);
            if (response.resulte === false) {
                console.log(response);
            }
        }
        if (xhr.status !== 200) {
            console.log(xhr.status);
        }
    }
}

function getInfoLine(articleId, state, log)
{
    var html = '';
    html += '<div></div>';
    html += '<div id="infoItem_' + articleId + '" class="infoItem ';
    if (state === true) {
        html += 'listValidateButtonValid';
    }
    html += '">';
    html += log;
    html += '</div>';

    return html;
}

function getTableHead(headline)
{
    var html = getTableGrid();
    html += '<div class="listHeader"><input id="masterCheckbox" type="checkbox" style="margin-left: -1px;" onclick="toggleAllCheckboxes();" /></div>';
    html += '<div class="listHeader"></div>';
    html += '<div class="listHeader"></div>';
    html += '<div class="listHeader"></div>';
    html += '<div class="listHeader"></div>';
    html += '<div class="listHeader">' + headline['articlenumber'] + '</div>';
    html += '<div class="listHeader">' + headline['category'] + '</div>';
    html += '<div class="listHeader">' + headline['title'] + '</div>';
    html += '<div class="listHeader">' + headline['shortdescription'] + '</div>';
    html += '</div>';

    return html;
}

function getTableGrid()
{
    return '<div class="listBody">';
}

function getArticleInfo()
{
    var start = listStart + 1;
    var end = listStart + listLimit;
    if (end > listCountAll) {
        end = listCountAll;
    }

    var html = '<div class="listFooter">';
    if (listStart >= listLimit) {
        html += '<div class="pagingButton" onclick="previousPage();"><</div>';
    }
    html += '<div class="listFooterText">Artikel ' + start + ' bis ' + end  + ' von ' + listCountAll + ' Artikeln.</div>';
    if (end < listCountAll) {
        html += '<div class="pagingButton" onclick="nextPage();">></div>';
    }
    html += '</div>';
    return html;
}

function nextPage()
{
    listStart += listLimit;
    articleListView = [];
    articleListChecked = [];
    getArticleList();
}

function previousPage()
{
    listStart -= listLimit;
    if (listStart < 0) {
        listStart = 0;
    }
    articleListView = [];
    articleListChecked = [];
    getArticleList();
}

function validateArticle(articleId, categoryId, send = false)
{
    document.getElementById('validateButton_' + articleId).classList = 'listValidateButton';

    var infoLineField = document.getElementById('infoLine_' + articleId);
    infoLineField.style.display = 'none';

    var infoField = document.getElementById('infoField_' + articleId);
    infoField.innerHTML = '';
    infoField.className = 'listItem';
    var result = false;

    $.ajax({
        method: "POST",
        url: shopUrl + "index.php?cl=SoluteAjaxValidateArticle&fnc=run",
        data: {
            shopId: shopId,
            languageId: languageId,
            articleId: articleId,
            categoryId: categoryId
        }
    })
        .done(function ( jsonResponse ) {
            var resultList = JSON.parse(jsonResponse);
            var result = resultList.result;
            var infoLineContent = getInfoLine(articleId, result, resultList['log']);

            infoField.classList = 'listValidateButton';
            infoLineField.innerHTML = infoLineContent;

            if (result === false) {
                infoField.innerHTML = '<img src="' + iconPath + 'info.svg" class="icon-button"/>';
                infoField.classList.add('listValidateButtonError');
                infoField.setAttribute('onclick', 'toggleInfoItem(' + "'" + articleId + "'" + ')');
                uncheckOne(articleId);
            } else {
                infoField.innerHTML = '<img src="' + iconPath + 'upload.svg" class="icon-button" />';
                infoField.setAttribute('title', document.getElementById('translationButtonTitleUpload').value);
                infoField.classList.add('listValidateButtonValid');
                infoField.setAttribute('onclick', 'sendSingleArticleToApi(' + "'" + articleId + "'" + ',' + "'" + categoryId + "'" + ',' + "'" + resultList.valueHash + "'" + ')');
                if (send === true) {
                    sendSingleArticleToApi(articleId, categoryId, resultList.valueHash);
                }
            }
        });
    return result;
}

function validateMultipleArticle()
{
    articleListChecked.forEach(function (viewData) {
        validateArticle(viewData['articleId'], viewData['categoryId']);
    });
    document.getElementById('masterCheckbox').checked = false;
}

function sendMultipleArticleToApi()
{
    articleListChecked.forEach(function (viewData) {
        validateArticle(viewData['articleId'], viewData['categoryId'], true);
    });
    document.getElementById('masterCheckbox').checked = false;
}

function checkMultipleArticleOnApi()
{
    articleListChecked.forEach(function (viewData) {
        checkSingleArticleOnApi(viewData['articleId']);
    });
    document.getElementById('masterCheckbox').checked = false;
}

function deleteMultipleArticleOnApi()
{
    articleListChecked.forEach(function (viewData) {
        deleteSingleArticleOnApi(viewData['articleId']);
    });
    document.getElementById('masterCheckbox').checked = false;
}

function toggleInfoItem(articleId)
{
    var infoLine = document.getElementById('infoLine_' + articleId);
    if (infoLine.style.display === 'none') {
        infoLine.style.display = 'grid';
    } else {
        infoLine.style.display = 'none';
    }
}

function toggleAllCheckboxes()
{
    var checkbox = document.getElementById('masterCheckbox');
    if (checkbox.checked === true) {
        checkAll();
    } else {
        uncheckAll();
    }
}

function toggleOne(articleId, categoryId)
{
    var checkbox = document.getElementById('checkbox_' + articleId);
    if (checkbox.checked === true) {
        checkOne(articleId, categoryId);
    } else {
        uncheckOne(articleId);
    }
}

function checkAll()
{
    articleListView.forEach(function (viewData) {
        checkOne(viewData['articleId'], viewData['categoryId']);
    });
}

function uncheckAll()
{
    articleListView.forEach(function (viewData) {
        uncheckOne(viewData['articleId']);
    });
}

function checkOne(articleId, categoryId)
{
    var id = 'checkbox_' + articleId;
    document.getElementById(id).checked = true;
    articleListChecked.push(getViewData(articleId, categoryId));
}

function uncheckOne(articleId)
{
    var id = 'checkbox_' + articleId;
    document.getElementById(id).checked = false;

    articleListChecked.forEach(function (item, key) {
        if (item['articleId'] === articleId) {
            articleListChecked.splice(key,1);
        }
    });
}

function getViewData(articleId, categoryId)
{
    var viewData = [];
    viewData['articleId'] = articleId;
    viewData['categoryId'] = categoryId;

    return viewData;
}

function convertResponse(errors)
{
    var html = '';
    errors.forEach(function (item) {
        html += item['key'] + ': ' + item['text'] + '<br>';
    });
    return html;
}

function sendSingleArticleToApi(articleId, categoryId, feedHash)
{
    var articleList = {};
    articleList.data = [];
    var data = {};
    data.categoryId = categoryId;
    data.articleId = articleId;
    data.feedHash = feedHash;
    var infoField = document.getElementById('infoField_' + articleId);
    infoField.removeAttribute('onclick');

    articleList.data.push(data);
    var articleListJson = JSON.stringify(articleList);
    $.ajax({
        method: "POST",
        url: shopUrl + "index.php?cl=SoluteAjaxSendToApi&fnc=run",
        data: {
            shopId: shopId,
            languageId: languageId,
            articleList: articleListJson
        }
    })
        .done(function ( jsonResponse ) {
            var resultList = JSON.parse(jsonResponse);

            var result = resultList[articleId]['result'];

            var infoLineContent = getInfoLine(articleId, result, resultList[articleId]['log']);
            var infoLineField = document.getElementById('infoLine_' + articleId);
            infoLineField.innerHTML = infoLineContent;

            infoField.setAttribute('onclick', 'toggleInfoItem(' + "'" + articleId + "'" + ')');

            if (result === true) {
                // OK
                infoField.classList = 'listValidateButton listValidateButtonValid';
                infoField.innerHTML = '<img src="' + iconPath + 'thumbs-up.svg" class="icon-button"/>';
                infoField.setAttribute('title', document.getElementById('translationButtonTitleSuccess').value);
            } else {
                // Fehler
                infoField.classList = 'listValidateButton listValidateButtonError';
                infoField.innerHTML = '<img src="' + iconPath + 'info.svg" class="icon-button"/>';
            }
            uncheckOne(articleId);
        });
}

function checkSingleArticleOnApi(articleId)
{
    var articleList = {};
    articleList.data = [];
    var data = {};
    data.articleId = articleId;

    articleList.data.push(data);
    var articleListJson = JSON.stringify(articleList);

    $.ajax({
        method: "POST",
        url: shopUrl + "index.php?cl=SoluteAjaxCheckArticleOnApi&fnc=run",
        data: {
            articleList: articleListJson
        }
    })
        .done(function ( jsonResponse ) {
            var resultList = JSON.parse(jsonResponse);
            var result = resultList[articleId]['result'];

            var infoLineContent = getInfoLine(articleId, result, resultList[articleId]['log']);
            var infoLineField = document.getElementById('infoLine_' + articleId);
            infoLineField.innerHTML = infoLineContent;

            setInfoField(articleId, result);
            uncheckOne(articleId);
        });
}

function deleteSingleArticleOnApi(articleId)
{
    var articleList = {};
    articleList.data = [];
    var data = {};
    data.articleId = articleId;

    articleList.data.push(data);
    var articleListJson = JSON.stringify(articleList);

    $.ajax({
        method: "POST",
        url: shopUrl + "index.php?cl=SoluteAjaxDeleteArticleOnApi&fnc=run",
        data: {
            articleList: articleListJson
        }
    })
        .done(function ( jsonResponse ) {
            var resultList = JSON.parse(jsonResponse);
            var result = resultList[articleId]['result'];

            var infoLineContent = getInfoLine(articleId, result, resultList[articleId]['log']);
            var infoLineField = document.getElementById('infoLine_' + articleId);
            infoLineField.innerHTML = infoLineContent;

            setInfoField(articleId, result);
            document.getElementById('checkbox_' + articleId).checked = false;
            uncheckOne(articleId);
        });
}

function setInfoField(articleId, result)
{
    var infoField = document.getElementById('infoField_' + articleId);
    infoField.innerHTML = '<img src="' + iconPath + 'info.svg" class="icon-button"/>'
    infoField.setAttribute('onclick', 'toggleInfoItem(' + "'" + articleId + "'" + ')');

    if (result === true) {
        infoField.classList = 'listValidateButton listValidateButtonValid';
    } else {
        infoField.classList = 'listValidateButton listValidateButtonError';
    }
}

getArticleList();
