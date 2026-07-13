// ============================
// Sales Invoice Data Population
// ============================
// Centralized runtime state (globals retained to avoid broad refactor risk now).
// Future improvement: Wrap in IIFE / module pattern to minimize global leakage.

// it is use to set the date range of the data
var start;
var end;

// it is use to populate the table and searchable options for products and clients
var clients = [];
var products = [];
var sales_invoice = [];

// it is use for table of sales invoices
var invoice_list_table = [];

// it is use to count the number of input fields for discount
var input_counter = 0;

// it is use as a condition to check if the item is vatable or not
var vat_switch = false;

// it is use to summarize the item details
var item_summary = [];

// it is use to store summary table data
var item_table_list = null;

$(document).ready(function () {
    start = moment().subtract(30, 'days');
    end = moment();
    $('#si_date_range').daterangepicker({
        startDate: start,
        endDate: end,
        locale: {
            format: 'MM/DD/YYYY'
        }
    });

    // Trigger handleDateRangeApply when the apply button is clicked
    $('#si_date_range').on('apply.daterangepicker', function (ev, picker) {
        // handleDateRangeApply(picker.startDate, picker.endDate);
        start = picker.startDate;
        end = picker.endDate;
        get_products_clients_si(start, end);
    });

    get_products_clients_si(start, end);

    $('#item_remove_discount').hide();
    $('#item_add_discount').hide();

    $('#client_date_details').val(new Date().toISOString().split('T')[0]);

});

/**
 * Fetch products, clients and sales invoices for given date range then refresh UI tables & selects.
 * @param {moment.Moment} start
 * @param {moment.Moment} end
 */
function get_products_clients_si(start, end) {
    showLoader();
    $.ajax({
        url: base_url + '/sales_invoice/get_products_clients_si',
        type: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({
            start: start.format('YYYY-MM-DD'),
            end: end.format('YYYY-MM-DD')
        }),
        success: function (response) {
            $('#loader').hide();
            let data = JSON.parse(response);
            products = data.products.map(function (product) {
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
            clients = data.clients.map(function (client) {
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

            sales_invoice = data.sales_invoice.map(function (si) {
                return {
                    si_id: si.id,
                    client_name: si.client_name,
                    client_term: si.client_term,
                    si_status: si.si_status,
                    si_date: si.si_date,
                    updated_at: si.updated_at,
                    client_id: si.client_id
                };
            });

            populateSelect('#clients_details', clients, 'client_name');
            populateSelect('#products_details', products, 'product_name_item');
            sales_invoice_table();

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
    
}

/**
 * Populate a select2 (or standard) select element with items using a given property for text.
 * Adds a blank option first.
 * @param {string} selector - jQuery selector for select element
 * @param {Array<object>} items - items to inject
 * @param {string} textProperty - property to use for option label
 */
function populateSelect(selector, items, textProperty) {
    let select = $(selector);
    select.empty();
    select.append($('<option></option>').attr('value', '').text('')); // Add blank option
    items.forEach(function (item) {
        let option = $('<option></option>').attr('value', item.id).text(item[textProperty]);
        select.append(option);
    });
}

/**
 * Initialize / refresh sales invoice DataTable.
 */
function sales_invoice_table() {
    invoice_list_table = $('#invoice_list_table').DataTable({
        destroy: true,
        data: sales_invoice,
        order: [0, 'desc'],
        columns: [
            { data: 'updated_at', visible: false }, // Hide this column
            { data: 'si_id' },
            { data: 'client_name' },
            {
                data: function (data) {
                    return terms_set(data.client_term);
                }
            },
            { data: 'si_status' },
            {
                data: 'si_date', render: function (data) {
                    let date = new Date(data);
                    let options = { year: 'numeric', month: 'long', day: 'numeric' };
                    return date.toLocaleDateString('en-US', options);
                }
            },
            {   
                data: function (data) {
                    let view_button = '<button type="button" class="btn btn-success mx-1 view_si_btn"><i class="fa fa-eye"></i></button>';
                    let edit_button = '<button type="button" class="btn btn-warning mx-1 edit_si_btn"><i class="fa fa-pencil"></i></button>';
                    let print_button = '<button type="button" class="btn btn-primary mx-1 print_si_btn"><i class="fa fa-print"></i></button>';
                    if (data.si_status === 'cancelled') {
                        return view_button; // No buttons for canceled status
                    }
                    
                    return data.si_status === 'printed' ? (view_button + print_button) : (view_button + edit_button + print_button);
                }
            }
        ],
        columnDefs: [
            { targets: '_all', className: 'content_center' },
            { targets: [6], className: 'd-flex justify-content-center' }
        ],
        drawCallback: function () {
            initSalesInvoiceButton();
        }
    });
}

/**
 * Bind action buttons (edit / print / view) for invoice list rows.
 */
function initSalesInvoiceButton() {
    $('.edit_si_btn').off('click');
    $('.edit_si_btn').on('click', function () {
        let data = invoice_list_table.row($(this).parents('tr')).data();
        if (data.si_status === 'printed') {
            alert('Cannot edit a printed invoice.');
            return;
        }
        showUniversalModal("Edit Confirmation", "Are you sure you want to edit this draft?", function () {
            $.ajax({
                url: base_url + '/sales_invoice/get_sales_invoice_by_id',
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify(data.si_id),
                success: function (response) {
                    let invoiceData = JSON.parse(response);
                    clear_item_fields();
                    clear_customer_fields();
                    if(item_table_list != null) {
                        clear_table_summary();
                    }
                    populate_invoice_module_draft(invoiceData.header, invoiceData.items);
                    $('#update_draft_btn').show(); // Show the update draft button
                    $('#cancel_update_draft_btn').show();
                    $('#draft_btn').hide(); // Hide the draft button
                    $('#print_btn').hide();

                    // Remove the matching si_id from sales_invoice array
                    sales_invoice = sales_invoice.filter(function(item) {
                        return item.si_id !== data.si_id;
                    });
                    // Reinitialize the table
                    sales_invoice_table();
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
    });

    $('.print_si_btn').off('click');
    $('.print_si_btn').on('click', function () {
        let data = invoice_list_table.row($(this).parents('tr')).data();
        showUniversalModal("Print Confirmation", "Are you sure you want to print this sales invoice?", function () {
            print_si(data);
        });
    });

    $('.view_si_btn').off('click');
    $('.view_si_btn').on('click', function () {
        let data = invoice_list_table.row($(this).parents('tr')).data();
        $.ajax({
            url: base_url + '/sales_invoice/get_si_receipt_by_id',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                si_id: data.si_id,
                status: data.si_status
            }),
            success: function (response) {
                let response_data = JSON.parse(response);

                // Validation: if items are null/empty, show a professional alert and do not attempt to render the view
                if (response_data.items == null) {
                    alert('No items were found for this delivery receipt. This may be due to a system error — the issue has been logged and handled. Please contact support if the problem persists.');
                    return;
                }

                sales_invoice_view_receipt(response_data.header, response_data.items, data.si_status, true);
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

    $('#clients_details_name').text(header.client_name).attr('edit-si-id', header.si_id);
    $('#client_tin_details').text(header.client_tin);
    $('#client_address_details').text(header.client_address);
    $('#client_company_details').text(header.client_business_name);
    $('#client_term_details').val(header.client_term).change();
    $('#client_date_details').val(header.si_date);

    $('#item_freight_details').val(header.freight_cost);

    data.forEach(function (item) {
        let discountsArr = [];
        if (Array.isArray(item.discounts) && item.discounts.length > 0) {
            discountsArr = item.discounts.map(function(discount) {
                if (discount && discount.value) {
                    return {
                        value: discount.value,
                        label: discount.label
                    };
                } else {
                    return {
                        value: '',
                        label: ''
                    };
                }
            });
        } else {
            discountsArr = [{ value: '', label: '' }];
        }
        // Normalize vat_switch which may come as 0/1, '0'/'1', true/false, 'true'/'false'
        const vatSwitchNormalized = (item.vat_switch === 1 || item.vat_switch === '1' || item.vat_switch === true || item.vat_switch === 'true');

        item_summary.push({
            unique_id: Number(item.unique_id),
            product_id: String(item.product_id),
            price: String(item.price),
            qty: String(item.qty),
            vat_switch: vatSwitchNormalized,
            discounts: discountsArr
        });
    });
    item_list_table();
}

/**
 * Handle printing logic based on invoice status.
 * @param {object} data - row object from DataTable
 */
function print_si(data) {
    let id = data.si_id;
    let status = data.si_status;

    if (status === 'draft') {
        $.ajax({
            url: base_url + '/sales_invoice/print_si_receipt',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(data),
            success: function (response) {
                let data = JSON.parse(response);
                if (data.status === 'success') {
                    window.open("/sales_invoice_view/" + id + "/" + status, "_blank");
                }
                else {
                    alert('Failed to print sales invoice');
                }
            },
            error: function (xhr) {
                if (xhr.status === 400) {
                    let response = JSON.parse(xhr.responseText);
                    alert(response.error);
                } else {
                    alert('Failed to print sales invoice');
                }
            }
        });
    }
    else if (status === 'printed') {
        window.open("/sales_invoice_view/" + id + "/" + status, "_blank");
    }
    else {
        alert('contact system admin');
    }

    cancel_update_sales_invoice();
}