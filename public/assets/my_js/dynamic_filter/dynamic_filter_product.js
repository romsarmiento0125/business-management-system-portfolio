var base_url = $('#base_url').val();
var selectedProducts = [];
var editSelectedProducts = [];
var product_list_table;
var edit_product_list_table;
var filter_name_table;
var products_data = [];

function showLoader() {
    $('#loader').show();
}

function hideLoader() {
    $('#loader').hide();
}

$(document).ready(function () {
    showLoader();
    // Set initial state of Save Filter button
    update_save_filter_button();
    $.ajax({
        url: base_url + '/dynamic_filter_product/get_products',
        type: 'GET',
        contentType: 'application/json',
        success: function (response) {
            let data = JSON.parse(response);
            products_data = data.products;
            populate_select(products_data);
            populate_edit_select(products_data);
            hideLoader();
        },
        error: function (xhr) {
            if (xhr.status === 500) {
                let response = JSON.parse(xhr.responseText);
                alert(response.error);
            } else {
                alert('Call a system admin.');
            }
        }
    });

    get_product_filters();

    // When a products is chosen, add it to the array, then reset select to blank
    $('#product_name').on('change', function () {
        const $select = $(this);
        const id = $select.val();

        // Skip if blank/invalid selection
        if (!id) return;

        const $opt = $select.find('option:selected');
        const name = $opt.text();
        const item_code = $opt.data('item_code') || '';

        // Prevent duplicate products by id (normalize to string for safety)
        const isDuplicate = selectedProducts.some(c => String(c.id) === String(id));
        if (isDuplicate) {
            alert('Product already in the list.');
            $select.val('');
            update_save_filter_button();
            return;
        }

        const selectedItem = { id: id, name: name, item_code: item_code };

        // Push into the array (duplicate handling can be added later if needed)
        selectedProducts.push(selectedItem);

        // Reset select to blank
        $select.val('');

        populate_table();
        update_save_filter_button();
    });

    // Save filter: validate and send via AJAX
    $('#save_product_filter').on('click', function () {
        const filterName = ($('#filter_name_product').val() || '').trim();

        if (!filterName) {
            alert('Please enter a filter name.');
            return;
        }
        if (selectedProducts.length === 0) {
            alert('Please add at least one product.');
            return;
        }

        const payload = {
            filter_name_product: filterName,
            products: selectedProducts
        };

        const $btn = $(this);
        $btn.prop('disabled', true);

        showLoader();
        $.ajax({
            url: base_url + '/dynamic_filter_product/save_product_filter',
            type: 'POST',
            data: JSON.stringify(payload),
            contentType: 'application/json',
            success: function (res) {
                let data = {};
                try { data = typeof res === 'string' ? JSON.parse(res) : res; } catch (e) {}
                alert(data?.message || 'Filter saved successfully.');
                
                // Reset state
                selectedProducts = [];
                // Clear both add and edit inputs in case either was used
                $('#dy_add_filter_box_product #filter_name_product, #dy_edit_filter_box_product #edit_filter_name_product').val('');
                populate_table();
                update_save_filter_button();
                get_product_filters();
                // Return to add mode after save
                enterAddMode();
            },
            error: function (xhr) {
                let message = 'Failed to save filter. Please contact the system admin.';
                try {
                    const err = JSON.parse(xhr.responseText);
                    if (err?.error) message = err.error;
                } catch (e) {}
                alert(message);
            },
            complete: function () {
                hideLoader();
                $btn.prop('disabled', false);
            }
        });
    });

    // Cancel edit: clear edit box and switch back to add box
    $('#cancel_edit_product').off('click').on('click', function () {
        // Clear edit state data
        editSelectedProducts = [];
        $('#edit_filter_name_product').val('').removeAttr('data-filter-id');
        $('#edit_products_details').val('');

        // Re-render (empties the edit table)
        edit_populate_table();

        // Switch back to add mode
        enterAddMode();

        // Ensure save button reflects current add-mode selection
        update_save_filter_button();
    });

    // When a product is chosen in EDIT select, add to editSelectedProducts and reset select
    $('#edit_products_details').off('change').on('change', function () {
        const $select = $(this);
        const id = $select.val();
        if (!id) return;

        const $opt = $select.find('option:selected');
        const name = $opt.text();
        const item_code = $opt.data('item_code') || '';

        // Prevent duplicates based on id
        const isDuplicate = Array.isArray(editSelectedProducts) && editSelectedProducts.some(c => String(c.id) === String(id));
        if (isDuplicate) {
            alert('Product already in the list.');
            $select.val('');
            return;
        }

        // Add and reset
        editSelectedProducts.push({ id: id, name: name, item_code: item_code });
        $select.val('');

        // Re-render edit table
        edit_populate_table();
    });

    // Edit filter: validate and send update via AJAX
    $('#edit_filter_product').off('click').on('click', function () {
        const $btn = $(this);
        const filterId = $('#edit_filter_name_product').attr('data-filter-id');
        const filterName = ($('#edit_filter_name_product').val() || '').trim();

        if (!filterId) {
            alert('Missing filter ID. Please select a filter to edit from the right table.');
            return;
        }
        if (!filterName) {
            alert('Please enter a filter name.');
            return;
        }
        if (!Array.isArray(editSelectedProducts) || editSelectedProducts.length === 0) {
            alert('Please add at least one product to the filter.');
            return;
        }

        const payload = {
            product_filter_id: filterId,
            filter_name: filterName,
            products: editSelectedProducts
        };

        $btn.prop('disabled', true);
        showLoader();
        $.ajax({
            url: base_url + '/dynamic_filter_product/edit_product_filter',
            type: 'POST',
            data: JSON.stringify(payload),
            contentType: 'application/json',
            success: function (res) {
                let data = {};
                try { data = typeof res === 'string' ? JSON.parse(res) : res; } catch (e) {}

                // If backend returns a message, show it; otherwise use a generic success
                alert(data?.message || 'Filter updated successfully.');

                // Refresh list on the right
                get_product_filters();

                // Clear edit state and switch back to add mode
                editSelectedProducts = [];
                $('#edit_filter_name_product').val('').removeAttr('data-filter-id');
                $('#edit_products_details').val('');
                edit_populate_table();
                enterAddMode();
            },
            error: function (xhr) {
                let message = 'Failed to update filter. Please contact the system admin.';
                try {
                    const err = JSON.parse(xhr.responseText);
                    if (err?.error) message = err.error;
                } catch (e) {}
                alert(message);
            },
            complete: function () {
                hideLoader();
                $btn.prop('disabled', false);
            }
        });
    });
});

function populate_select(items) {
    let select = $('#product_name'); // Replace with your actual select element ID
    select.empty();
    select.append($('<option></option>').attr('value', '').text('')); // Add blank option
    items.forEach(function (item) {
        let option = $('<option></option>')
            .attr('value', item.product_id)
            .attr('data-item_code', item.product_item || '')
            .text(item.product_name +  ' (' + item.product_item + ')');
        select.append(option);
    });
}

function populate_edit_select(items) {
    let select = $('#edit_products_details'); // Replace with your actual select element ID
    select.empty();
    select.append($('<option></option>').attr('value', '').text('')); // Add blank option
    items.forEach(function (item) {
        let option = $('<option></option>')
            .attr('value', item.product_id)
            .attr('data-item_code', item.product_item || '')
            .text(item.product_name);
        select.append(option);
    });
}

function populate_table() {
    product_list_table = $('#product_filter_list_table').DataTable({
        destroy: true,
        data: selectedProducts,
        order: [0, 'desc'],
        columns: [
            { data: 'id', visible: false },
            { data: 'name' },
            { data: 'item_code' },
            {   
                data: function (data) {
                    let remove_button = '<button type="button" class="btn btn-danger mx-1 remove_si_btn"><i class="fa fa-trash"></i></button>';
                    
                    return remove_button;
                }
            }
        ],
        columnDefs: [
            { targets: '_all', className: 'content_center' },
        ],
        drawCallback: function () {
            // Left table: remove selected client
            $('.remove_si_btn').off('click').on('click', function () {
                let row = $(this).closest('tr');
                let rowData = product_list_table.row(row).data();
                let productIdToRemove = rowData.id;
                selectedProducts = selectedProducts.filter(function (product) { return String(product.id) !== String(productIdToRemove); });
                populate_table();
                update_save_filter_button();
            });
        }
    });

    // Ensure the button visibility reflects current data
    update_save_filter_button();
}

function edit_populate_table() {
    edit_product_list_table = $('#edit_product_filter_list_table').DataTable({
        destroy: true,
        data: editSelectedProducts,
        order: [0, 'desc'],
        columns: [
            { data: 'id', visible: false },
            { data: 'name' },
            { data: 'item_code' },
            {   
                data: function (data) {
                    let remove_button = '<button type="button" class="btn btn-danger mx-1 edit_remove_product_btn"><i class="fa fa-trash"></i></button>';
                    
                    return remove_button;
                }
            }
        ],
        columnDefs: [
            { targets: '_all', className: 'content_center' },
        ],
        drawCallback: function () {
             // Remove product from editSelectedProducts array and update table
            $('.edit_remove_product_btn').off('click').on('click', function () {
                let row = $(this).closest('tr');
                let rowData = edit_product_list_table.row(row).data();
                let productIdToRemove = rowData.id;
                editSelectedProducts = editSelectedProducts.filter(product => String(product.id) !== String(productIdToRemove));
                edit_populate_table();
                update_save_filter_button();
            });
        }
    });

    // Ensure the button visibility reflects current data
    update_save_filter_button();
}

// Hide/show Save Filter button based on selections
function update_save_filter_button() {
    if (selectedProducts.length === 0) {
        $('#save_filter').hide();
    } else {
        $('#save_filter').show();
    }
}

function get_product_filters() {
    showLoader();
    $.ajax({
        url: base_url + '/dynamic_filter_product/get_product_filters',
        type: 'POST',
        contentType: 'application/json',
        success: function (res) {
            let data = {};
            try { data = typeof res === 'string' ? JSON.parse(res) : res; } catch (e) {}
            populate_dynamic_filter_product_table((data && data.filters) ? data.filters : []);
        },
        error: function (xhr) {
            let message = 'Failed to retrieve product filters. Please contact the system admin.';
            try {
                const err = JSON.parse(xhr.responseText);
                if (err?.error) message = err.error;
            } catch (e) {}
            alert(message);
        },
        complete: function () {
            hideLoader();
        }
    });
}

function populate_dynamic_filter_product_table(data) {
    product_filter_name_table = $('#filter_name_table_product').DataTable({
        destroy: true,
        data: data,
        order: [0, 'desc'],
        columns: [
            { data: 'id', visible: false },
            { data: 'filter_name' },
            {   
                data: function () {
                    let view_button = '<button type="button" class="btn btn-success mx-1 view_product_filter_btn" title="View"><i class="fa fa-eye"></i></button>';
                    let edit_button = '<button type="button" class="btn btn-warning mx-1 edit_product_filter_btn" title="Edit"><i class="fa fa-edit"></i></button>';
                    let delete_button = '<button type="button" class="btn btn-danger mx-1 delete_product_filter_btn" title="Delete"><i class="fa fa-trash"></i></button>';
                    return view_button + edit_button + delete_button;
                }
            }
        ],
        columnDefs: [
            { targets: '_all', className: 'content_center' }
        ],
        drawCallback: function () {
            initProductFilterButtons();
        }
    });
}
    
function initProductFilterButtons() {
    // View
    $('.view_product_filter_btn').off('click').on('click', function () {
        const rowData = product_filter_name_table.row($(this).parents('tr')).data();
        $.ajax({
            url: base_url + '/dynamic_filter_product/view_product_filter',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({ product_filter_id: rowData.id }),
            success: function (response) {
                let response_data = null;
                try {
                    response_data = (typeof response === 'string') ? JSON.parse(response) : response;
                } catch (e) {
                    alert('Invalid server response.');
                    return;
                }
                processViewProductFilterSuccess(response_data);
            },
            error: function (xhr) {
                if (xhr.status === 500) {
                    let response = JSON.parse(xhr.responseText);
                    alert(response.error);
                } else {
                    alert('Call a system admin');
                }
            }
        });
    });

    // Edit: populate left form
    $('.edit_product_filter_btn').off('click').on('click', function () {
        const rowData = product_filter_name_table.row($(this).parents('tr')).data();
        if (!rowData || !rowData.id) {
            alert('Invalid selection.');
            return;
        }
        showLoader();
        $.ajax({
            url: base_url + '/dynamic_filter_product/view_product_filter',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({ product_filter_id: rowData.id }),
            success: function (response) {
                let response_data = null;
                try {
                    response_data = (typeof response === 'string') ? JSON.parse(response) : response;
                } catch (e) {
                    alert('Invalid server response.');
                    return;
                }
                processEditProductFilterSuccess(response_data, rowData);
            },
            error: function (xhr) {
                if (xhr.status === 500) {
                    let response = JSON.parse(xhr.responseText);
                    alert(response.error);
                } else {
                    alert('Call a system admin');
                }
            },
            complete: function () {
                hideLoader();
            }
        });
    });

    // Delete (stub)
    $('.delete_product_filter_btn').off('click').on('click', function () {
        const rowData = product_filter_name_table.row($(this).parents('tr')).data();
        if (!rowData || !rowData.id) {
            alert('Invalid selection.');
            return;
        }
        const name = rowData.filter_name || '';
        if (!confirm('Are you sure you want to delete filter: ' + name + '?')) {
            return;
        }

        showLoader();
        $.ajax({
            url: base_url + '/dynamic_filter_product/delete_product_filter',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({ product_filter_id: rowData.id }),
            success: function (res) {
                let data = {};
                try { data = typeof res === 'string' ? JSON.parse(res) : res; } catch (e) {}
                alert(data?.message || 'Filter deleted successfully.');
                // Refresh the filters list
                get_product_filters();

                // If currently editing this same filter, clear edit state and go back to add mode
                const currentEditId = $('#edit_filter_name_product').attr('data-filter-id');
                if (currentEditId && String(currentEditId) === String(rowData.id)) {
                    editSelectedProducts = [];
                    $('#edit_filter_name_product').val('').removeAttr('data-filter-id');
                    $('#edit_products_details').val('');
                    edit_populate_table();
                    enterAddMode();
                }
            },
            error: function (xhr) {
                let message = 'Failed to delete filter. Please contact the system admin.';
                try {
                    const err = JSON.parse(xhr.responseText);
                    if (err?.error) message = err.error;
                } catch (e) {}
                alert(message);
            },
            complete: function () {
                hideLoader();
            }
        });
    });
}

// Handle and validate the response for viewing a product filter
function processViewProductFilterSuccess(response_data) {
    // Validate presence of products array with at least one item
    if (!response_data || !Array.isArray(response_data.products) || response_data.products.length === 0) {
        alert('No product filter data found.');
        return;
    }

    // Set the filter name from the first product's filter_name
    $('#product_filter_name').text(response_data.products[0].filter_name || '');

    // Populate the product names
    const container = $('#product_filter_items_container');
    container.empty();
    response_data.products.forEach(function(item) {
        const name = item.product_name || item.name || '';
        const code = item.product_item;
        container.append('<p>- ' + name + ' (' + code + ')</p>');
    });

    // Show the modal only after successful validation and rendering
    $('#dynamic_filter_view_modal').modal('show');
}

// Handle and validate the response for editing a client filter
function processEditProductFilterSuccess(response_data, rowData) {
    // Validate presence of products array with at least one item
    if (!response_data || !Array.isArray(response_data.products) || response_data.products.length === 0) {
        alert('No product filter data found for editing.');
        return;
    }

    // Set the filter name from the first product's filter_name (fallback to row's name)
    const filterName = rowData.filter_name || '';
    // Set the filter name in the edit box
    $('#edit_filter_name_product').val(filterName);
    // Attach the filter id as an attribute for reference during editing
    const editFilterId = (rowData && rowData.id) || (response_data.products[0] && response_data.products[0].filter_id) || response_data.filter_id || '';
    if (editFilterId) {
        $('#edit_filter_name_product').attr('data-filter-id', editFilterId);
    } else {
        $('#edit_filter_name_product').removeAttr('data-filter-id');
    }

    // Show edit box and hide add box
    enterEditMode();

    // Map products into the left table's shape
    editSelectedProducts = response_data.products.map(function (item) {
        return {
            id: item.product_id || '',
            name: item.product_name || '',
            item_code: item.product_item || ''
        };
    });
   
    // Re-render left table with selected products
    edit_populate_table();
}

// Switch UI to Edit mode: hide add box, show edit box
function enterEditMode() {
    $('#dy_add_filter_box_product').hide();
    $('#dy_edit_filter_box_product').show();
}

// Switch UI to Add mode: show add box, hide edit box and enable input
function enterAddMode() {
    $('#dy_edit_filter_box_product').hide();
    $('#dy_add_filter_box_product').show();
}
