var shopUrl     = document.getElementById('shopUrl').value;
var shopId      = document.getElementById('shopId').value;
var categoryId  = document.getElementById('categoryId').value;

function getMapping()
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
            var html = displayList(response);
            var mapping = document.getElementById('Mapping');
            mapping.innerHTML = html;
            mapping.style.display = 'block';
        });
}

getMapping();
