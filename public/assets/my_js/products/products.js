var base_url = $('#base_url').val();
var user_role = $('#user_role').val();
var product_table_data;

function showLoader() {
    $('#loader').show();
}

function hideLoader() {
    $('#loader').hide();
}

function formatPrice(price) {
    return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'PHP' }).format(price);
}

$(document).ready(function () {
    get_table_products();
});

function get_table_products() {
    showLoader();
    $.ajax({
        url: base_url + '/products/get_table_products',
        type: 'POST',
        beforeSend: function () {
            $('#loader').show();
        },
        success: function (response) {
            let data = JSON.parse(response);
            product_table(data);
            hideLoader();
        },
        error: function () {
            hideLoader();
        }
    });
}

function product_table(data) {
    product_table_data = $('#product_table').DataTable({
        destroy: true,
        data: data,
        scrollX: true,
        order: [[0, 'desc']], // Order by the 'id' column in ascending order
        columns: [
            { data: 'id' },
            { data: 'product_name' },
            { data: 'product_item' },
            { data: 'product_unit' },
            { data: 'product_weight' },
            {
                data: function (data) {
                    return formatPrice(data.product_price);
                }
            },
            {
                data: function (data) {
                    return data.product_cost_value ? formatPrice(data.product_cost_value) : '<span class="text-muted">N/A</span>';
                },
                visible: user_role === '1' || user_role === '2'
            },
            {
                data: function (data) {
                    if (data.product_tag === 'na' || data.product_tag === null) {
                        return '<span class="text-muted">N/A</span>';
                    }

                    let tagLabelMap = {
                        'a': 'Product A',
                        'b': 'Product B',
                        'c': 'Product C'
                    };

                    let normalizedTag = String(data.product_tag).toLowerCase();
                    let displayTag = tagLabelMap[normalizedTag] || data.product_tag;

                    return '<span class="">' + displayTag + '</span>';
                }
            },
            { data: 'is_active', render: function (data) { return data === '0' ? '<span>Active</span>' : '<span>Inactive</span>'; } },
            {
                data: function (data) {
                    let editButton = '<button type="button" class="btn btn-warning mx-1 edit_product" product_id="' + data.product_id + '" ' + (data.is_active === '0' ? '' : 'style=\"display:none;\"') + '><i class="fa fa-pencil"></i></button>';
                    let costButton = '';
                    if (user_role === '1' || user_role === '2') {
                        costButton = '<button type="button" class="btn btn-info mx-1 set_cost" product_id="' + data.product_id + '" data-current-cost="' + (data.product_cost_value || '') + '" ' + (data.is_active === '0' ? '' : 'style=\"display:none;\"') + ' title="Set Cost"><i class="fa fa-money"></i></button>';
                    }
                    let makeInactiveButton = '<button type="button" class="btn btn-danger mx-1 make_inactive" product_id="' + data.product_id + '" ' + (data.is_active === '0' ? '' : 'style=\"display:none;\"') + '><i class="fa fa-times"></i></button>';
                    let makeActiveButton = '<button type="button" class="btn btn-success mx-1 make_active" product_id="' + data.product_id + '" ' + (data.is_active === '1' ? '' : 'style=\"display:none;\"') + '><i class="fa fa-check"></i></button>';
                    return '<div class="d-flex justify-content-center">' + editButton + costButton + makeInactiveButton + makeActiveButton + '</div>';
                },
                visible: user_role === '1' || user_role === '2' || user_role === '3'
            }
        ],
        columnDefs: [
            { targets: '_all', className: 'content_center' },
            { targets: [0], className: 'text-center' }
        ],
        drawCallback: function () {
            initButton();
        }
    });
}

function initButton() {
    $('.edit_product').off('click');
    $('.edit_product').on('click', function () {
        let data = product_table_data.row($(this).parents('tr')).data();
        $('#edit_product_name').val(data.product_name);
        $('#edit_product_name').attr('data-id', data.id);
        $('#edit_product_name').attr('data-product-id', data.product_id); // Add product_id attribute
        $('#edit_product_unit').val(data.product_unit);
        $('#edit_product_item').val(data.product_item);
        $('#edit_product_weight').val(data.product_weight);
        $('#edit_product_price').val(data.product_price);
        $('#edit_product_tag').val(data.product_tag).trigger('change');
        $('#editProductModal').modal('show');
    });

    $('.set_cost').off('click');
    $('.set_cost').on('click', function () {
        let product_id = $(this).attr('product_id');
        let currentCost = $(this).attr('data-current-cost');
        $('#cost_product_id').val(product_id);
        $('#product_cost_input').val(currentCost || '');
        $('#productCostModal').modal('show');
    });

    $('.make_inactive').off('click');
    $('.make_inactive').on('click', function () {
        let product_id = $(this).attr('product_id');
        if (confirm('Are you sure you want to deactivate this product?')) {
            showLoader();
            $.ajax({
                url: base_url + '/products/active_inactive',
                type: 'POST',
                data: { product_id: product_id, is_active: '1' },
                success: function (response) {
                    let data = JSON.parse(response);
                    if (data.status === 'success') {
                        alert(data.message);
                        get_table_products();
                    } else {
                        alert('Error: ' + data.message);
                    }
                    hideLoader();
                },
                error: function () {
                    hideLoader();
                }
            });
        }
    });

    $('.make_active').off('click');
    $('.make_active').on('click', function () {
        let product_id = $(this).attr('product_id');
        if (confirm('Are you sure you want to activate this product?')) {
            showLoader();
            $.ajax({
                url: base_url + '/products/active_inactive',
                type: 'POST',
                data: { product_id: product_id, is_active: '0' },
                success: function (response) {
                    let data = JSON.parse(response);
                    if (data.status === 'success') {
                        alert(data.message);
                        get_table_products();
                    } else {
                        alert('Error: ' + data.message);
                    }
                    hideLoader();
                },
                error: function () {
                    hideLoader();
                }
            });
        }
    });
}

$('#save_product').click(function () {
    let product_data = {
        product_name: $('#product_name').val(),
        product_item: $('#product_item').val(),
        product_unit: $('#product_unit').val(),
        product_weight: $('#product_weight').val(),
        product_price: parseFloat($('#product_price').val()).toFixed(2),
        product_tag: $('#product_tag').val()
    }

    if (product_data.product_name === '' || 
        product_data.product_item === '' || 
        product_data.product_unit === '' || 
        product_data.product_weight === '' || 
        product_data.product_price === '' || 
        product_data.product_tag === '') {
        alert('All fields are required');
        return;
    }

    if (isProductNameExists(product_data.product_name)) {
        alert('Product name already exists');
        return;
    }

    if (isProductItemExists(product_data.product_item)) {
        alert('Product item code already exists');
        return;
    }

    showLoader();
    $.ajax({
        url: base_url + '/products/save_product',
        type: 'POST',
        data: JSON.stringify(product_data),
        dataType: 'json',
        success: function (response) {
            if (response.status === 'success') {
                alert(response.message);
                get_table_products();
                clear_modal_fields();
                $('#addProductModal').modal('hide');
            } else if (response.status === 'exists') {
                alert(response.message);
            } else {
                alert('Error: ' + response.message);
            }
            hideLoader();
        },
        error: function () {
            hideLoader();
        }
    });
});

$('#save_edit_product').click(function () {
    let product_data = {
        product_name: $('#edit_product_name').val(),
        product_name_attr: $('#edit_product_name').attr('data-id'),
        product_id: $('#edit_product_name').attr('data-product-id'), // Retrieve product_id attribute
        product_unit: $('#edit_product_unit').val(),
        product_item: $('#edit_product_item').val(),
        product_weight: $('#edit_product_weight').val(),
        product_price: parseFloat($('#edit_product_price').val()).toFixed(2),
        product_tag: $('#edit_product_tag').val()
    }

    if (product_data.product_name === '' || 
        product_data.product_unit === '' || 
        product_data.product_item === '' || 
        product_data.product_weight === '' || 
        product_data.product_price === '' || 
        product_data.product_tag === '') {
        alert('All fields are required');
        return;
    }

    showLoader();
    $.ajax({
        url: base_url + '/products/edit_product',
        type: 'POST',
        data: JSON.stringify(product_data),
        success: function (response) {
            let data = JSON.parse(response);
            if (data.status === 'success') {
                alert(data.message);
                get_table_products();
                $('#editProductModal').modal('hide');
            } else {
                alert('Error: ' + data.message);
            }
            hideLoader();
        },
        error: function () {
            hideLoader();
        }
    });
});

function isProductNameExists(product_name) {
    let exists = false;
    product_table_data.rows().every(function () {
        let data = this.data();
        if (data.product_name === product_name) {
            exists = true;
            return false;
        }
    });
    return exists;
}

function isProductItemExists(product_item) {
    let exists = false;
    product_table_data.rows().every(function () {
        let data = this.data();
        if (data.product_item === product_item) {
            exists = true;
            return false;
        }
    });
    return exists;
}

function clear_modal_fields() {
    $('#product_name').val('');
    $('#product_item').val('');
    $('#product_weight').val('');
    $('#product_price').val('');
}

$('#save_product_cost').click(function () {
    let product_id = $('#cost_product_id').val();
    let cost = $('#product_cost_input').val();

    if (cost === '' || cost === null) {
        alert('Please enter a cost value');
        return;
    }

    let cost_data = {
        product_id: product_id,
        cost: parseFloat(cost).toFixed(2)
    };

    showLoader();
    $.ajax({
        url: base_url + '/products/save_product_cost',
        type: 'POST',
        contentType: 'application/json',
        data: JSON.stringify(cost_data),
        success: function (response) {
            let data = JSON.parse(response);
            if (data.status === 'success') {
                alert(data.message);
                get_table_products();
                $('#productCostModal').modal('hide');
                $('#product_cost_input').val('');
            } else {
                alert('Error: ' + data.message);
            }
            hideLoader();
        },
        error: function () {
            alert('An error occurred while saving the product cost');
            hideLoader();
        }
    });
});