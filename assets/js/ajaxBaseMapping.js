function displayList(response)
{
    var groupTitle = '';
    var html = '';
    response.data.forEach(function (row) {
        if (row['UM_TITLE'] !== groupTitle) {
            html += getGroupTitle(row['UM_TITLE']);
            html += getHeadline(response.translation.headline);
            groupTitle = row['UM_TITLE'];
        }
        var fieldSelectionGroup = response.fieldSelection[groupTitle];
        html += getAttributeLine(row, fieldSelectionGroup, response.mapping[row['OXID']]);
    });

    return html;
}

function getGroupTitle(title)
{
    var html = '';
    html += '<div class="attributeGroup">' + title + '</div>';

    return html;
}

function getHeadline(headline)
{
    var html = '';
    html += '<div class="tableRow">';
    html += '<div class="headline">' + headline['identifier']  + '</div>';
    html += '<div class="headline">' + headline['mappinglist'] + '</div>';
    html += '<div class="headline">' + headline['manualvalue'] + '</div>';
    html += '<div class="headline">' + headline['description'] + '</div>';
    html += '</div>';

    return html;
}

function getAttributeLine(row, fieldSelection, mapping)
{
    var html = '';
    var identifier = row['UM_NAME_PRIMARY'] + '_manualValue';
    var required = false;
    if (row['UM_REQUIRED'] === '1') {
        required = true;
    }

    html += '<div class="tableRow">';
    html += '<div class="identifier ';
    if (required) {
        html += 'required';
    }
    html += '">' + row['UM_NAME_PRIMARY'];
    if (required) {
        html += '*';
    }
    html += '</div>';
    html += '<div>' + getFieldSelectionList(fieldSelection, mapping, row['UM_NAME_PRIMARY']) + '</div>'; //
    html += '<div>';

    if (row['UM_VALID_VALUES'] !== '') {
        var manualValue = '';
        if (mapping && mapping.UM_MANUAL_VALUE) {
            manualValue = mapping.UM_MANUAL_VALUE;
        }
        html += getValueList(row['UM_VALID_VALUES'], identifier, manualValue);
    } else {
        html += '<input type="text" class="input" value="';
        if (mapping && mapping.UM_MANUAL_VALUE) {
            html += mapping.UM_MANUAL_VALUE;
        }
        html += '" ';
        html += 'name="' + identifier + '" />';
    }
    html += '</div>';
    html += '<div>' + row['UM_DESCRIPTION'] + '</div>';
    html += '</div>';

    return html;
}

function getFieldSelectionList(fieldSelection, mapping, identifier)
{
    var list = Object.values(fieldSelection);
    var selected = '';
    if (mapping && mapping.UM_DATA_RESSOURCE_ID) {
        selected = mapping.UM_DATA_RESSOURCE_ID;
    } else {
        selected = '';
    }
    var html = '';
    html += '<select class="dropdown" name="' + identifier + '">';
    html += '<option ';

    if (selected === '') {
        html += ' selected="selected" ';
    }
    html += 'value="">bitte wählen / keine Zuweisung</option>';
    list.forEach(function (row) {
        html += '<option ';
        if (row['OXID'] === selected) {
            html += ' selected="selected"';
        }
        html += ' value="' + row['OXID'] + '" ';
        html += '>' + row.UM_FIELD_TITLE;
        var data = getDataRessourceType(row['UM_DATA_RESSOURCE']);
        if (data !== '') {
            html += ' [' + data + ']';
        }
        html += '</option>';
    });
    html += '</select>';
    return html;
}

function getValueList(validValues, identifier, manualValue)
{
    var html = '';
    html += '<select class="dropdown" name="' + identifier + '">';
    html += '<option '
    if (manualValue === '') {
        html += 'selected="selected" ';
    }
    html += 'value="">bitte wählen</option>';
    validValues.forEach(function (row) {
        html += '<option value="' + row + '"';
        if (row == manualValue) {
            html += ' selected="selected" ';
        }
        html += '>' + row + '</option>';
    });
    html += '</select>';

    return html;
}

function getDataRessourceType(dataRessource)
{
    var data = '';
    if (dataRessource.field) {
        var field = dataRessource.field;
        data = field.primarytable + '(' + field.primaryid + ').' + field.primaryfield;
    } else if (dataRessource.attribute) {
        var attribute = dataRessource.attribute;
        data = 'attribute ' + attribute.label;
    } else if (dataRessource.relationfield) {
        var relationfield = dataRessource.relationfield;
        data = relationfield.primarytable + '.' + relationfield.primaryfield + ' => ';
        data += relationfield.relationtable + '(' + relationfield.relationid + ').';
        data += relationfield.relationfield;
    } else if (dataRessource.generatedfield) {
        data = 'generated field';
    }

    return data;
}
