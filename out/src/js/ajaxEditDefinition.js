var shopUrl = document.getElementById('shopUrl').value;
var shopId  = document.getElementById('shopId').value;
var translations = [];
var xhr = new XMLHttpRequest();

function getInput(label, name, value, id, type, required)
{
    var html = '';
    html += '<div id="' + id + '" class="input-row">';
    html += '<div class="input-label">' + label;
    if (typeof required === 'boolean' && required === true) {
        html += ' *';
    }
    html += '</div>';
    html += '<div><input '

    if (typeof name === 'string' && name !== '') {
        html += 'name="' + name + '" ';
    }

    if (typeof value === 'string' && value !== '') {
        html += 'value="' + value + '" ';
    }

    if (typeof type === 'string' && type !== '') {
        html += 'type="' + type + '" ';
    }

    if (typeof required === 'boolean' && required === true) {
        html += ' required ';
    }

    html += '/></div></div>';
    return html;
}

function getDropdown(label, name, id, list, selected, actionOnChange, required)
{
    var html = '';
    html += '<div id="' + id + '" class="input-row">';
    html += '<div class="input-label">' + label;
    if (typeof required === 'boolean' && required === true) {
        html += ' *';
    }
    html += '</div>';
    html += '<div><select '

    if (typeof name === 'string' && name !== '') {
        html += 'name="' + name + '" ';
    }

    if (typeof actionOnChange === 'string' && actionOnChange !== '') {
        html += 'onChange="' + actionOnChange + '"';
        html += 'id="' + id + '_select"';
    }

    if (typeof required === 'boolean' && required === true) {
        html += ' required ';
    }

    html += '>';

    if (typeof list !== 'undefined' && list.length > 0) {
        html += '<option value="">';
        if (typeof required === 'boolean' && required === true) {
            html += 'bitte wählen';
        } else {
            html += 'Keine Auswahl / bitte wählen';
        }

        html += '</option>';
        list.forEach(function (row) {
            html += '<option value="' + row.value + '"';
            if (row.value === selected) {
                html += ' selected="selected" ';
            }
            html += '>' + row.title + '</option>';
        });
    }
    html += '</select></div></div>';
    return html;
}

function closeModal()
{
    var dialog = document.querySelector('dialog');
    dialog.close();
    document.getElementById('modalContent').innerHTML = '';
    document.getElementById('bodyDiv').classList = '';
    document.getElementById('content').classList = '';
}

function reorganizeTableList(tableList)
{
    var list = [];
    var fields = [];

    tableList.forEach(function (row) {
        var item = [];
        item.title = row.table;
        item.value = row.table;
        list.push(item);
        fields[row.table] = row.fields;
    });

    var result = [];
    result.fields = fields;
    result.list = list;

    return result;
}

function getTableDropdown(
    objectId,
    tabellist,
    label,
    name,
    selectedTable,
    fieldLabel,
    fieldName,
    selectedField,
    idLabel,
    idName,
    selectedIdField,
    actionOnChange
)
{
    var html = '';

    if (typeof tabellist !== 'undefined' && tabellist.length > 0) {
        var reorganizedList = reorganizeTableList(tabellist);
        var list = reorganizedList.list;
        var fields = reorganizedList.fields;

        html += '<div id="' + objectId + '" class="table-div">';
        html += getDropdown(label, name, name, list, selectedTable, actionOnChange);
        html += getDropdown(fieldLabel, fieldName, fieldName, fields[selectedTable], selectedField);
        html += getDropdown(idLabel, idName, idName, fields[selectedTable], selectedIdField);
        html += '</div>';
    }

    return html;
}

function createForm(response)
{
    var html = '';

    html += getInput(
        response.translations.label_fieldTitle,
        'fieldTitle',
        response.fieldSelectionRow.fieldTitle,
        'fieldTitle',
        'text',
        true
    );

    html += getDropdown(
        response.translations.label_attributeGroup,
        'attributeGroupId',
        'attributeGroupId',
        response.attributeGroupList,
        response.fieldSelectionRow.attributeGroupId,
        '',
        true
    );

    html += getDropdown(
        response.translations.label_type,
        'type',
        'type',
        response.validTypeList,
        response.dataRessource.type,
        'toggleFieldsOnType();',
        true
    );

    html += getDropdown(
        response.translations.label_attributeValue,
        'attributeValue',
        'attributeValue',
        response.attributeLabelList,
        response.dataRessource.attributeValue
    );

    html += getTableDropdown(
        'primaryValues',
        response.primaryTableList,
        response.translations.label_primaryTable,
        'primaryTable',
        response.dataRessource.primaryTable,
        response.translations.label_primaryTableField,
        'primaryField',
        response.dataRessource.primaryField,
        response.translations.label_primaryTableIdField,
        'primaryId',
        response.dataRessource.primaryId,
        'togglePrimaryTableFields();'
    );

    html += getTableDropdown(
        'relationValues',
        response.relationTableList,
        response.translations.label_relationTable,
        'relationTable',
        response.dataRessource.relationTable,
        response.translations.label_relationTableField,
        'relationField',
        response.dataRessource.relationField,
        response.translations.label_relationTableIdField,
        'relationId',
        response.dataRessource.relationId,
        'toggleRelationTableFields();'
    );

    html += getDropdown(
        response.translations.label_generatedField,
        'generated',
        'generated',
        response.generatedFieldList,
        response.dataRessource.generated
    );

    html += getDropdown(
        response.translations.label_converter,
        'converter',
        'converter',
        response.converterList,
        response.dataRessource.converter
    );

    return html;
}

function setVisibility(typeValue)
{
    if (typeValue === 'field') {
        document.getElementById('primaryValues').style.display = 'grid';
        document.getElementById('relationValues').style.display = 'none';
        document.getElementById('generated').style.display = 'none';
        document.getElementById('attributeValue').style.display = 'none';
    } else if (typeValue === 'attribute') {
        document.getElementById('primaryValues').style.display = 'none';
        document.getElementById('relationValues').style.display = 'none';
        document.getElementById('generated').style.display = 'none';
        document.getElementById('attributeValue').style.display = 'grid';
    } else if (typeValue === 'relationfield') {
        document.getElementById('primaryValues').style.display = 'grid';
        document.getElementById('relationValues').style.display = 'grid';
        document.getElementById('generated').style.display = 'none';
        document.getElementById('attributeValue').style.display = 'none';
    } else if (typeValue === 'generatedfield') {
        document.getElementById('primaryValues').style.display = 'none';
        document.getElementById('relationValues').style.display = 'none';
        document.getElementById('generated').style.display = 'grid';
        document.getElementById('attributeValue').style.display = 'none';
    } else {
        document.getElementById('primaryValues').style.display = 'none';
        document.getElementById('relationValues').style.display = 'none';
        document.getElementById('generated').style.display = 'none';
        document.getElementById('attributeValue').style.display = 'none';
    }
}

function openModal(objectId)
{
    document.getElementById('bodyDiv').classList.add('modalBackground');
    document.getElementById('content').classList.add('content-inactive');
    getModalContent(objectId);
    var dialog = document.querySelector('dialog');
    dialog.show();
}

function getFormHeader(objectId)
{
    var html = '';
    html += '<div class="form-header"><div class="header">' + translations.formHeader + '</div></div>';
    html += '<form id="modalDataForm" method="POST" onSubmit="saveDefinition();">';
    if (typeof objectId === 'string' && objectId !== '') {
        html += '<input type="hidden" name="objectId" value="' + objectId + '" />';
        html += '<input type="hidden" name="shopId" value="' + shopId + '" />';
    }
    return html;
}

function getFormFooter()
{
    var html = '';
    html += '<div class="form-footer">';
    html += '<div class="submit footer-button">';
    html += '<input type="submit" class="save-button" value="' + translations.saveForm + '" />';
    html += '</div>';
    html += '</form>';
    return html;
}

function getModalContent(objectId)
{
    $.ajax({
        method: "POST",
        url: shopUrl + "index.php?cl=SoluteAjaxEditDefinition&fnc=run",
        data: {
            objectId: objectId
        }
    })
        .done(function (jsonResponse) {
            var response = JSON.parse(jsonResponse);
            translations = response.translations;
            var html = '';
            html += getFormHeader(objectId);
            html += createForm(response);
            html += getFormFooter();
            document.getElementById('modalContent').innerHTML = html;
            setVisibility(response.dataRessource.type);
        });
}

function toggleFieldsOnType()
{
    var typeValue = document.getElementById('type_select');
    setVisibility(typeValue.value);
}

function togglePrimaryTableFields()
{
    var table = document.getElementById('primaryTable_select').value;

    $.ajax({
        method: "POST",
        url: shopUrl + "index.php?cl=SoluteAjaxTableFieldList&fnc=run",
        data: {
            table: table
        }
    })
        .done(function (jsonResponse) {
            var response = JSON.parse(jsonResponse);
            var reorganizedList = reorganizeTableList(response.tableList);
            var fields = reorganizedList.fields;

            var htmlPrimaryField = getDropdown(
                translations.label_primaryTableField,
                'primaryField',
                'primaryField',
                fields[table],
                ''
            );
            $('#primaryField').replaceWith(htmlPrimaryField);

            var htmlPrimaryIdField = getDropdown(
                translations.label_primaryTableIdField,
                'primaryIdField',
                'primaryId',
                fields[table],
                ''
            );
            $('#primaryId').replaceWith(htmlPrimaryIdField);
        });
}

function toggleRelationTableFields()
{
    var table = document.getElementById('relationTable_select').value;

    $.ajax({
        method: "POST",
        url: shopUrl + "index.php?cl=SoluteAjaxTableFieldList&fnc=run",
        data: {
            table: table
        }
    })
        .done(function (jsonResponse) {
            var response = JSON.parse(jsonResponse);
            var reorganizedList = reorganizeTableList(response.tableList);
            var fields = reorganizedList.fields;

            var htmlRelationField = getDropdown(
                translations.label_relationTableField,
                'relationField',
                'relationField',
                fields[table],
                ''
            );
            $('#relationField').replaceWith(htmlRelationField);

            var htmlRelationIdField = getDropdown(
                translations.label_relationTableIdField,
                'relationIdField',
                'relationId',
                fields[table],
                ''
            );
            $('#relationId').replaceWith(htmlRelationIdField);
        });
}

function saveDefinition()
{
    var formData = new FormData(document.getElementById("modalDataForm"));
    var targetUrl = shopUrl + 'index.php?cl=SoluteAjaxSaveDefinition&fnc=run';

    xhr.open("POST", targetUrl, true);
    xhr.send(formData);
    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4 && xhr.status === 200) {
            var response = JSON.parse(xhr.responseText);
            if (response.result === false) {
                console.log(response);
            }
        }
        if (xhr.status !== 200) {
            console.log(xhr.status);
        }
    }
}

function deleteDefinition(objectId)
{
    $.ajax({
        method: "POST",
        url: shopUrl + "index.php?cl=SoluteAjaxDeleteDefinition&fnc=run",
        data: {
            objectId: objectId
        }
    })
        .done(function () {
            document.getElementById('definition_' + objectId).remove();
        });
}
