// ============================
// Delivery Receipt Data Population
// ============================
// Centralized runtime state (globals retained for parity; consider module pattern later)
// Future improvement: Wrap in IIFE / module pattern to minimize global leakage.

// it is use to set the date range of the data
var start;
var end;

// it is use to populate the table and searchable options for products and clients
var clients = [];
var products = [];
var delivery_receipt = [];

// it is use for table of sales invoices
var receipt_list_table = [];

// it is use to count the number of input fields for discount
var input_counter = 0;

// it is use to summarize the item details
var item_summary = [];

// it is use to store summary table data
var item_table_list = null;

$(document).ready(function () {
    start = moment().subtract(30, 'days');
    end = moment();
    $('#dr_date_range').daterangepicker({
        startDate: start,
        endDate: end,
        locale: {
            format: 'MM/DD/YYYY'
        }
    });

    // Trigger handleDateRangeApply when the apply button is clicked
    $('#dr_date_range').on('apply.daterangepicker', function (ev, picker) {
        // handleDateRangeApply(picker.startDate, picker.endDate);
        start = picker.startDate;
        end = picker.endDate;
        get_products_clients_dr(start, end);
    });

    get_products_clients_dr(start, end);

    $('#item_remove_discount').hide();
    $('#item_add_discount').hide();

    $('#client_date_details').val(new Date().toISOString().split('T')[0]);
});

/**
 * Fetch products, clients and sales invoices for given date range then refresh UI tables & selects.
 * @param {moment.Moment} start
 * @param {moment.Moment} end
 */
function get_products_clients_dr(start, end) {
    showLoader();
    $.ajax({
        url: base_url + '/delivery_receipt/get_products_clients_dr',
        type: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({
            start: start.format('YYYY-MM-DD'),
            end: end.format('YYYY-MM-DD')
        }),
        success: function (response) {
            $('#loader').hide();
            var data = JSON.parse(response);

            products = (data.products || []).map(function (product) {
                return {
                    id: product.id,
                    product_name: product.product_name,
                    product_item: product.product_item,
                    product_unit: product.product_unit,
                    product_weight: product.product_weight,
                    product_price: product.product_price,
                    product_name_item: product.product_name + ' ( ' + product.product_item + ' )',
                    product_id: product.product_id
                };
            });

            clients = (data.clients || []).map(function (client) {
                return {
                    id: client.id,
                    client_name: client.client_name,
                    client_tin: client.client_tin,
                    client_business_name: client.client_business_name,
                    client_term: client.client_term,
                    client_address: client.client_address,
                    client_id: client.client_id
                };
            });

            delivery_receipt = (data.delivery_receipt || []).map(function (dr) {
                return {
                    dr_id: dr.id,
                    client_name: dr.client_name,
                    client_term: dr.client_term,
                    dr_status: dr.dr_status,
                    dr_date: dr.dr_date,
                    updated_at: dr.updated_at,
                    client_id: dr.client_id
                };
            });

            populateSelect('#clients_details', clients, 'client_name');
            populateSelect('#products_details', products, 'product_name_item');
            delivery_receipt_table();

            hideLoader();
        },
        error: function (xhr) {
            if (xhr.status === 500) {
                var response = JSON.parse(xhr.responseText);
                alert(response.error);
            } else {
                alert('Call a system admin.');
            }
        }
    });
}

/**
 * Populate a select2 (or standard) select element with items using a given property for text.
 * Adds a blank option first.
 * @param {string} selector - jQuery selector for select element
 * @param {Array<object>} items - items to inject
 * @param {string} textProperty - property to use for option label
 */
function populateSelect(selector, items, textProperty) {
    var select = $(selector);
    select.empty();
    select.append($('<option></option>').attr('value', '').text(''));
    items.forEach(function (item) {
        var option = $('<option></option>').attr('value', item.id).text(item[textProperty]);
        select.append(option);
    });
}

/**
 * Initialize / refresh delivery receipt DataTable.
 */
function delivery_receipt_table() {
    receipt_list_table = $('#receipt_list_table').DataTable({
        destroy: true,
        data: delivery_receipt,
        order: [0, 'desc'],
        columns: [
            { data: 'updated_at', visible: false },
            { data: 'dr_id' },
            { data: 'client_name' },
            {
                data: function (row) {
                    return terms_set(row.client_term);
                }
            },
            { data: 'dr_status' },
            { 
                data: 'dr_date', render: function (data) {
                    let date = new Date(data);
                    let options = { year: 'numeric', month: 'long', day: 'numeric' };
                    return date.toLocaleDateString('en-US', options);
                }
            },
            {
                data: function (row) {
                    let view_button = '<button type="button" class="btn btn-success mx-1 view_dr_btn"><i class="fa fa-eye"></i></button>';
                    let edit_button = '<button type="button" class="btn btn-warning mx-1 edit_dr_btn"><i class="fa fa-pencil"></i></button>';
                    let print_button = '<button type="button" class="btn btn-primary mx-1 print_dr_btn"><i class="fa fa-print"></i></button>';
                    if (row.dr_status === 'cancelled') {
                        return view_button; // No buttons for canceled status
                    }

                    return row.dr_status === 'printed' ? (view_button + print_button) : (view_button + edit_button + print_button);
                }
            }
        ],
        columnDefs: [
            { targets: '_all', className: 'content_center' },
            { targets: [6], className: 'd-flex justify-content-center' }
        ],
        drawCallback: function () {
            initDeliveryReceiptButton();
        }
    });
}

/**
 * Bind action buttons (edit / print / view) for invoice list rows.
 */
function initDeliveryReceiptButton() {
    $('.edit_dr_btn').off('click');
    $('.edit_dr_btn').on('click', function () {
        let data = receipt_list_table.row($(this).parents('tr')).data();
        if (data.dr_status === 'printed') {
            alert('Cannot edit a printed delivery receipt.');
            return;
        }
        showUniversalModal('Edit Confirmation', 'Are you sure you want to edit this draft?', function () {
            $.ajax({
                url: base_url + '/delivery_receipt/get_delivery_receipt_by_id',
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify(data.dr_id),
                success: function (response) {
                    let drData = JSON.parse(response);
                    clear_item_fields();
                    clear_customer_fields();
                    if(item_table_list != null) {
                        clear_table_summary();
                    }
                    populate_invoice_module_draft(drData.header, drData.items);
                    $('#update_draft_btn').show(); // Show the update draft button
                    $('#cancel_update_draft_btn').show();
                    $('#draft_btn').hide(); // Hide the draft button
                    $('#print_btn').hide();

                    // Remove the matching dr_id from delivery_receipt array
                    delivery_receipt = delivery_receipt.filter(function (item) {
                        return item.dr_id !== data.dr_id;
                    });
                    // Reinitialize the table
                    delivery_receipt_table();
                },
                error: function (xhr) {
                    if (xhr.status === 500) {
                        var response = JSON.parse(xhr.responseText);
                        alert(response.error);
                    } else {
                        alert('Call a system admin');
                    }
                }
            });
        });
    });

    $('.print_dr_btn').off('click');
    $('.print_dr_btn').on('click', function () {
        let data = receipt_list_table.row($(this).parents('tr')).data();
        showUniversalModal('Print Confirmation', 'Are you sure you want to print this delivery receipt?', function () {
            print_dr(data);
        });
    });

    $('.view_dr_btn').off('click');
    $('.view_dr_btn').on('click', function () {
        let data = receipt_list_table.row($(this).parents('tr')).data();
        $.ajax({
            url: base_url + '/delivery_receipt/get_dr_receipt_by_id',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                dr_id: data.dr_id,
                status: data.dr_status
            }),
            success: function (response) {
                let response_data = JSON.parse(response);

                // Validation: if items are null/empty, show a professional alert and do not attempt to render the view
                if (response_data.items == null) {
                    alert('No items were found for this delivery receipt. This may be due to a system error — the issue has been logged and handled. Please contact support if the problem persists.');
                    return;
                }

                delivery_receipt_view_receipt(response_data.header, response_data.items, data.dr_status, true);
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
}

/**
 * Populate UI with existing draft invoice for editing.
 * @param {object} header
 * @param {Array<object>} data
 */
function populate_invoice_module_draft(header, data) {
    // Populate customer details
    $('.clients_details_container').hide();
    $('.clients_details_name_container').show();

    $('#clients_details_name').text(header.client_name).attr('edit-dr-id', header.dr_id);
    $('#client_tin_details').text(header.client_tin);
    $('#client_address_details').text(header.client_address);
    $('#client_company_details').text(header.client_business_name);
    $('#client_term_details').val(header.client_term).change();
    $('#client_date_details').val(header.dr_date);

    $('#item_freight_details').val(header.freight_cost);

    // Reset item summary before populating
    item_summary = [];

    (data || []).forEach(function (item) {
        // Normalize discounts; support {value,label} and legacy {discount,label}
        var discountsArr = Array.isArray(item.discounts) && item.discounts.length > 0
            ? item.discounts.map(function (discount) {
                var val = (discount && (discount.value ?? discount.discount)) || '';
                var lab = (discount && discount.label) || '';
                return { value: val, label: lab };
            })
            : [{ value: '', label: '' }];

        item_summary.push({
            unique_id: Number(item.unique_id || item.dr_unique_id || 0),
            product_id: String(item.product_id || item.dr_product_id || ''),
            price: String(item.price || item.unit_price || 0),
            qty: String(item.qty || item.dr_item_qty || 0),
            discounts: discountsArr
        });
    });
    item_list_table();
}

/**
 * Handle printing logic based on invoice status.
 * @param {object} data - row object from DataTable
 */
function print_dr(data) {
    let id = data.dr_id;
    let status = data.dr_status;

    if (status === 'draft') {
        $.ajax({
            url: base_url + '/delivery_receipt/print_dr_receipt',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(data),
            success: function (response) {
                var data = JSON.parse(response);
                if (data.status === 'success') {
                    window.open('/delivery_receipt_view/' + id + '/' + status, '_blank');
                }
                else {
                    alert('Failed to print delivery receipt');
                }
            },
            error: function (xhr) {
                if (xhr.status === 400) {
                    var response = JSON.parse(xhr.responseText);
                    alert(response.error);
                } else {
                    alert('Failed to print delivery receipt');
                }
            }
        });
    }
    else if (status === 'printed') {
        window.open('/delivery_receipt_view/' + id + '/' + status, '_blank');
    }
    else {
        alert('contact system admin');
    }

    cancel_update_delivery_receipt();
}