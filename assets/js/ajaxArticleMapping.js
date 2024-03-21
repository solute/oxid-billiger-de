var shopUrl     = document.getElementById('shopUrl').value;
var shopId      = document.getElementById('shopId').value;
var articleId   = document.getElementById('articleId').value;

function getMapping()
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
            var html = displayList(response);
            var mapping = document.getElementById('Mapping');
            mapping.innerHTML = html;
            mapping.style.display = 'block';
        });
}

getMapping();
