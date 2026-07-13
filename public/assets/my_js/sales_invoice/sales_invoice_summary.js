// ============================
// Sales Invoice Summary Logic
// ============================
// Note: Public functions (called from HTML onclick) retained.
// Adds light documentation & small safety checks; no behavioral change intended.

$('#item_freight_details').on('input', function () {
    compute_summary_vatables(item_summary);
});

/**
 * Initialize or refresh the DataTable representing current item summary.
 */
function item_list_table() {
    item_table_list = $('#item_list_table').DataTable({
        destroy: true,
        data: item_summary,
        columns: [
            { data: 'unique_id', visible: false },
            { 
                data: function (data) {
                    const matchedProduct = products.find(function(product) {
                        return product.id == data.product_id;
                    });

                    // If a product is found, return its name; otherwise, return an empty string or a placeholder
                    if (matchedProduct) {
                        return matchedProduct.product_item;
                    } else {
                        return 'N/A'; // Or some other placeholder if the product isn't found
                    }
                }
            },
            { data: 'price' },
            { data: 'qty' },
            {
                data: function (data) {
                    let amount = parseFloat(data.price) * parseFloat(data.qty);
                    return formatPrice(amount);
                }
            },
            {
                data: function (data) {
                    let discount = 0;
                    data.discounts.forEach(function (dis) {
                        discount += parseFloat(dis.value) || 0;
                    });
                    let total_discount = parseFloat(discount) * parseFloat(data.qty);
                    return formatPrice(total_discount);
                }
            },
            {
                data: function (data) {
                    let discount = 0;
                    data.discounts.forEach(function (dis) {
                        discount += parseFloat(dis.value) || 0;
                    });

                    let total_amount = (parseFloat(data.price) - parseFloat(discount)) * parseFloat(data.qty);
                    return formatPrice(total_amount);
                }
            },
            {
                data: function (data) {
                    let edit_button = '<button type="button" class="btn btn-warning mx-1 edit_summary_btn"><i class="fa fa-pencil"></i></button>';
                    let remove_button = '<button type="button" class="btn btn-danger mx-1 remove_summary_btn"><i class="fa fa-trash"></i></button>';
                    return edit_button + remove_button;
                }
            }
        ],
        columnDefs: [
            { targets: '_all', className: 'content_center' },
            { targets: [7], className: 'd-flex justify-content-center' }
        ],
        drawCallback: function () {
            initSummaryButton();
        },
        initComplete: function () {
            // This function is called when the DataTable has completed its initialisation
            compute_summary_vatables(item_summary);
        }
    });
}

function initSummaryButton() {
    $('.edit_summary_btn').off('click');
    $('.edit_summary_btn').on('click', function () {
        var data = item_table_list.row($(this).parents('tr')).data();

        $('#products_details').val(data.product_id).trigger('change');
        $('#item_price_details').val(data.price);
        $('#item_qty_details').val(data.qty);
        $('#item_switch_details').prop('checked', data.vat_switch);
        vat_switch = data.vat_switch;

        input_counter = 0;
        $('#add_input_discount').empty();
        $('#item_remove_discount').hide();
        $('#item_add_discount').hide();

        let discount_input = '';
        let discount_label = '';

        data.discounts.forEach(function (discount, index) {
            input_counter++;
            discount_input = `<div class="col-5 mb-2">
                <div class="d-flex align-items-center">
                    <p>Discount:&nbsp;</p>
                    <input type="number" class="form-control item_discounts_value" id="item_discount_value_${input_counter}" value="${discount.value}">
                </div>
            </div>`;

            discount_label = `<div class="col-7 mb-2">
                <div class="d-flex align-items-center">
                    <p>Discount&nbsp;Label:&nbsp;</p>
                    <input type="text" class="form-control item_discounts_label" id="item_discount_label_${input_counter}" value="${discount.label}">
                </div>
            </div>`;

            $('#add_input_discount').append(discount_input + discount_label);
        });

        if (input_counter <= 1) {
            $('#item_remove_discount').hide();
            $('#item_add_discount').show();
        }
        else {
            $('#item_remove_discount').show();
            $('#item_add_discount').show();
        }

        calculate_sales_invoice();

        item_summary = item_summary.filter(function (item) {
            return item.unique_id !== data.unique_id;
        });

        item_list_table();
    });

    $('.remove_summary_btn').off('click');
    $('.remove_summary_btn').on('click', function () {
        var data = item_table_list.row($(this).parents('tr')).data();
        showUniversalModal("Delete Item", "Are you sure you want to delete this item?", function () {
            item_summary = item_summary.filter(function (item) {
                return item.unique_id !== data.unique_id;
            });

            item_list_table();
        });
    });
}

/**
 * Compute totals (amounts, discounts, VAT) and update summary fields.
 * @param {Array} data - current item_summary array
 */
function compute_summary_vatables(data) {
    $('#discount_summary').empty();
    let freight_cost = $('#item_freight_details').val();
    let sum_tot_amnt = 0;
    let sum_disc = 0;
    let sum_vat_sales = 0;
    let sum_exempt = 0;
    let sum_vat = 0;
    let sum_tot_amnt_due = 0;
    // let freight = parseFloat(freight_cost) || 0;
    data.forEach(function (item) {
        sum_tot_amnt = sum_tot_amnt + (parseFloat(item.price) * parseFloat(item.qty));
        sum_disc = show_discount_summary(item.discounts, parseFloat(item.qty));
        if(item.vat_switch) {
            sum_vat_sales = sum_vat_sales + (((parseFloat(item.price) * parseFloat(item.qty)) - sum_disc) / 1.12);
            sum_vat = sum_vat + (((parseFloat(item.price) * parseFloat(item.qty)) - sum_disc) - (((parseFloat(item.price) * parseFloat(item.qty)) - sum_disc) / 1.12));
        }
        else{
            sum_exempt = sum_exempt + ((parseFloat(item.price) * parseFloat(item.qty)) - sum_disc);
        }
        sum_tot_amnt_due = sum_tot_amnt_due + ((parseFloat(item.price) * parseFloat(item.qty)) - sum_disc);
    });

    sum_tot_amnt_due = parseFloat(sum_tot_amnt_due) + (freight_cost === '' ? 0 : parseFloat(freight_cost));

    $('#summary_total_amount').text(formatPrice(sum_tot_amnt)).attr('data-total-amount', sum_tot_amnt);
    $('#summary_vatable_sales').text(formatPrice(sum_vat_sales)).attr('data-vatable-sales', sum_vat_sales);
    $('#summary_vat_exempt_sales').text(formatPrice(sum_exempt)).attr('data-vat-exempt-sales', sum_exempt);
    $('#summary_zero_rated').text('0'); 
    $('#summary_vat_amount').text(formatPrice(sum_vat)).attr('data-vat-amount', sum_vat);
    $('#summary_total_amount_due').text(formatPrice(sum_tot_amnt_due)).attr('data-total-amount-due', sum_tot_amnt_due);
}

function show_discount_summary(dis_data, qty) {
    let disc_sum = '';
    let disc_value = 0;
    dis_data.forEach(function (dis) {
        if (dis.value == '') {
            disc_value += 0;
        }
        else {
            disc_sum = '<div class="d-flex align-items-center mb-1">' +
                '<p class="fw-bold">' + dis.value + '</p>' +
                '<p class="mx-2">x</p>' +
                '<p class="fw-bold">' + qty + '</p>' +
                '<p class="mx-2">=</p>' +
                '<p class="fw-bold">' + (formatPrice((dis.value * qty))) + '</p>' +
                '<p class="fw-bold ms-2">' + dis.label + '</p>' +
                '</div>';
            $('#discount_summary').append(disc_sum);
        }
        disc_value += (dis.value * qty);
    });

    return disc_value;
}

/**
 * Persist sales invoice as draft or printed based on type param.
 * @param {('draft'|'printed')} type
 */
function save_sales_invoice(type) {
    let prompt = type === "printed" ? "print" : "draft";
    showUniversalModal("Confirm Action", "Are you sure you want to " + prompt + " this sales invoice?", function () {
        let customerDetail = {
            id: $('#clients_details').attr('data-client-id'),
            client_id: $('#clients_details').attr('data-client-client-id'),
            terms: $('#client_term_details').val(),
            date: $('#client_date_details').val()
        };
        
        let freight_cost = $('#item_freight_details').val() || 0;

        let items = item_summary.map(function(item) {
            return {
                product_id: item.product_id,
                unique_id: item.unique_id,
                unique_product_id: products.find(product => product.id == item.product_id)?.product_id || null,
                price: item.price,
                qty: item.qty,
                vat_switch: item.vat_switch,
                discounts: item.discounts
            };
        });

        let invoiceData = {
            customer: customerDetail,
            items: items,
            freight_cost: freight_cost,
        };

        // Validate data
        let missingFields = [];
        if (!customerDetail.id) missingFields.push('Customer Name');
        if (!customerDetail.terms) missingFields.push('Customer Terms');
        if (!customerDetail.date) missingFields.push('Customer Date');
        if (item_summary.length === 0) missingFields.push('Items');

        if (missingFields.length > 0) {
            alert('Invalid data. Please fill in the following fields: ' + missingFields.join(', '));
            return;
        }
        
        if (type === "draft") {
            $.ajax({
                url: base_url + '/sales_invoice/save_draft',
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify(invoiceData),
                beforeSend: function() {
                    $('#draft_btn').prop('disabled', true);
                },
                success: function (response) {
                    var data = JSON.parse(response);
                    if (data.result.status == "success") {
                        alert('Draft saved successfully');
                        clear_item_fields();
                        clear_customer_fields();
                        clear_table_summary();
                        get_products_clients_si(start, end);
                    }
                    else {
                        alert('Failed to save draft');
                    }
                },
                error: function (xhr) {
                    if (xhr.status === 400) {
                        var response = JSON.parse(xhr.responseText);
                        alert(response.error);
                    } else if (xhr.status === 500) {
                        var response = JSON.parse(xhr.responseText);
                        alert(response.error);
                    } else {
                        alert('Failed to save drafts');
                    }
                    $('#draft_btn').prop('disabled', false);
                },
                complete: function() {
                    $('#draft_btn').prop('disabled', false);
                }
            });
        } else if (type === "printed") {
            $.ajax({
                url: base_url + '/sales_invoice/print_invoice',
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify(invoiceData),
                beforeSend: function() {
                    $('#draft_btn').prop('disabled', true);
                },
                success: function (response) {
                    var data = JSON.parse(response);
                    if (data.result.status == "success") {
                        clear_item_fields();
                        clear_customer_fields();
                        clear_table_summary();
                        get_products_clients_si(start, end);
                        window.open("/sales_invoice_view/" + data.result.invoice_id + "/" + 'draft', "_blank");
                    }
                    else {
                        alert('Failed to save draft');
                    }
                },
                error: function (xhr) {
                    if (xhr.status === 400) {
                        var response = JSON.parse(xhr.responseText);
                        alert(response.error);
                    } else if (xhr.status === 500) {
                        var response = JSON.parse(xhr.responseText);
                        alert(response.error);
                    } else {
                        alert('Failed to save drafts');
                    }
                    $('#draft_btn').prop('disabled', false);
                },
                complete: function() {
                    $('#draft_btn').prop('disabled', false);
                }
            });
        }
    });
}

/**
 * Update an existing draft invoice currently loaded in edit mode.
 */
function update_sales_invoice() {
    showUniversalModal("Confirm Action", "Are you sure you want to update this sales invoice?", function () {
        let customerDetail = {
            si_id: $('#clients_details_name').attr('edit-si-id'),
            terms: $('#client_term_details').val(),
            date: $('#client_date_details').val()
        };
        
        let freight_cost = $('#item_freight_details').val() || 0;

        let items = item_summary.map(function(item) {
            return {
                product_id: item.product_id,
                unique_id: item.unique_id,
                unique_product_id: products.find(product => product.id == item.product_id)?.product_id || null,
                price: item.price,
                qty: item.qty,
                vat_switch: item.vat_switch,
                discounts: item.discounts
            };
        });

        let invoiceData = {
            customer: customerDetail,
            items: items,
            freight_cost: freight_cost,
        };

        // Validate data
        let missingFields = [];
        if (!customerDetail.terms) missingFields.push('Customer Terms');
        if (!customerDetail.date) missingFields.push('Customer Date');
        if (item_summary.length === 0) missingFields.push('Items');

        if (missingFields.length > 0) {
            alert('Invalid data. Please fill in the following fields: ' + missingFields.join(', '));
            return;
        }
        
        $.ajax({
            url: base_url + '/sales_invoice/update_draft',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(invoiceData),
            beforeSend: function() {
                $('#draft_btn').prop('disabled', true);
            },
            success: function (response) {
                var data = JSON.parse(response);
                if (data.result.status == "success") {
                    alert('Udate draft successfully');
                    clear_item_fields();
                    clear_customer_fields();
                    clear_table_summary();
                    get_products_clients_si(start, end);
                }
                else {
                    alert('Failed to update draft');
                }
                $('#update_draft_btn').hide(); // Show the update draft button
                $('#cancel_update_draft_btn').hide();
                $('#draft_btn').show(); // Hide the draft button
                $('#print_btn').show();
            },
            error: function (xhr) {
                if (xhr.status === 400) {
                    var response = JSON.parse(xhr.responseText);
                    alert(response.error);
                } else if (xhr.status === 500) {
                    var response = JSON.parse(xhr.responseText);
                    alert(response.error);
                } else {
                    alert('Failed to update drafts');
                }
                $('#draft_btn').prop('disabled', false);
            },
            complete: function() {
                $('#draft_btn').prop('disabled', false);
            }
        });
    });

}

/**
 * Cancel edit mode and reset UI state.
 */
function cancel_update_sales_invoice() {
    clear_item_fields();
    clear_customer_fields();
    if(item_table_list != null) {
        clear_table_summary();
    }
    get_products_clients_si(start, end);

    $('#update_draft_btn').hide(); // Show the update draft button
    $('#cancel_update_draft_btn').hide();
    $('#draft_btn').show(); // Hide the draft button
    $('#print_btn').show();
}
