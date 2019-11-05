function get_oct_product_preorder(product_id) {
    masked('body', true);
    $(".modal-backdrop").remove();
    $.ajax({
        type: 'post',
        dataType: 'html',
        url: 'index.php?route=octemplates/module/oct_product_preorder',
        data: 'product_id=' + product_id,
        success: function(data) {
            masked('body', false);
            $(".modal-holder").html(data);
            $("#us-oct-product-preorder-modal").modal("show");
        }
    });
}