// ============================
// Delivery Receipt Summary Logic (refactored from Sales Invoice)
// ============================
// Constraint: Do NOT add/remove functions. Only adapt internals & naming usage for DR.
// Differences vs SI: No VAT calculation; simpler totals. Field names reused for compatibility.

$('#item_freight_details').on('input', function () {
    compute_summary_values(item_summary);
});

/**
 * Initialize or refresh the DataTable representing current item summary.
 */
function item_list_table() {
    // For DR we still use item_summary array (structure aligned earlier with SI: price, qty, discounts[])
    item_table_list = $('#item_list_table').DataTable({
        destroy: true,
        data: item_summary,
        columns: [
            { data: 'unique_id', visible: false },
            { 
                data: function (row) {
                    const p = products.find(function (pr) { return pr.id == row.product_id; });
                    return p ? (p.product_item || p.product_name || 'N/A') : 'N/A';
                }
            },
            { data: 'price' },
            { data: 'qty' },
            { 
                data: function (row) {
                    return formatPrice((parseFloat(row.price) || 0) * (parseFloat(row.qty) || 0));
                }
            },
            { 
                data: function (row) {
                    let d = 0; 
                    row.discounts.forEach(function (dis) { d += parseFloat(dis.value) || 0; });
                    return formatPrice(d * (parseFloat(row.qty) || 0));
                }
            },
            { 
                data: function (row) {
                    let d = 0; 
                    row.discounts.forEach(function (dis) { d += parseFloat(dis.value) || 0; });
                    const price = parseFloat(row.price) || 0; 
                    const qty = parseFloat(row.qty) || 0; 
                    return formatPrice((price - Math.min(d, price)) * qty);
                }
            },
            { 
                data: function () {
                    return '<button type="button" class="btn btn-warning mx-1 edit_summary_btn"><i class="fa fa-pencil"></i></button>' +
                           '<button type="button" class="btn btn-danger mx-1 remove_summary_btn"><i class="fa fa-trash"></i></button>';
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
            compute_summary_values(item_summary);
        }
    });
}

function initSummaryButton() {
    $('.edit_summary_btn').off('click');
    $('.edit_summary_btn').on('click', function () {
        const data = item_table_list.row($(this).parents('tr')).data();

        $('#products_details').val(data.product_id).trigger('change');
        $('#item_price_details').val(data.price);
        $('#item_qty_details').val(data.qty);
        // DR: no VAT toggle; retain field if template still has it
        if ($('#item_switch_details').length) {
            $('#item_switch_details').prop('checked', false);
        }

        input_counter = 0;
        $('#add_input_discount').empty();
        $('#item_remove_discount').hide();
        $('#item_add_discount').hide();

        data.discounts.forEach(function (discount) {
            input_counter++;
            const discount_input = `<div class="col-5 mb-2">
                <div class="d-flex align-items-center">
                    <p>Discount:&nbsp;</p>
                    <input type="number" class="form-control item_discounts_value" id="item_discount_value_${input_counter}" value="${discount.value}">
                </div>
            </div>`;
            const discount_label = `<div class="col-7 mb-2">
                <div class="d-flex align-items-center">
                    <p>Discount&nbsp;Label:&nbsp;</p>
                    <input type="text" class="form-control item_discounts_label" id="item_discount_label_${input_counter}" value="${discount.label}">
                </div>
            </div>`;
            $('#add_input_discount').append(discount_input + discount_label);
        });

        if (input_counter <= 1) {
            $('#item_remove_discount').hide();
        } else {
            $('#item_remove_discount').show();
        }
        $('#item_add_discount').show();

        calculate_delivery_receipt();

        item_summary = item_summary.filter(function (item) {
            return item.unique_id !== data.unique_id;
        });

        item_list_table();
    });

    $('.remove_summary_btn').off('click');
    $('.remove_summary_btn').on('click', function () {
        const data = item_table_list.row($(this).parents('tr')).data();
        showUniversalModal('Delete Item', 'Are you sure you want to delete this item?', function () {
            item_summary = item_summary.filter(function (item) {
                return item.unique_id !== data.unique_id;
            });

            item_list_table();
        });
    });
}

/**
 * Compute totals (amounts, discounts) and update summary fields.
 * @param {Array} data - current item_summary array
 */
function compute_summary_values(data) {
    // DR: Simplified computation (no VAT breakdown). We reuse existing DOM ids where possible; non-used VAT fields zeroed.
    $('#discount_summary').empty();
    let freight_cost = $('#item_freight_details').val();
    let sub_total = 0;
    let total_discount_accum = 0;

    data.forEach(function (item) {
        const price = parseFloat(item.price) || 0;
        const qty = parseFloat(item.qty) || 0;
        let perUnitDiscount = 0;
        item.discounts.forEach(function (dis) {
            perUnitDiscount += (parseFloat(dis.value) || 0);
        });
        perUnitDiscount = Math.min(perUnitDiscount, price); // cap
        sub_total += price * qty;
        total_discount_accum += perUnitDiscount * qty;
        show_discount_summary(item.discounts, qty);
    });

    const total_after_discount = sub_total - total_discount_accum;
    const final_total = total_after_discount + (freight_cost === '' ? 0 : parseFloat(freight_cost));

    // Map to existing fields (vat fields become zeros)
    $('#summary_sub_total').text(formatPrice(sub_total)).attr('data-sub-total', sub_total);
    $('#summary_total_amount').text(formatPrice(final_total)).attr('data-total-amount', final_total);
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
function save_delivery_receipt(type) {
    const actionWord = type === 'printed' ? 'print' : 'draft';
    showUniversalModal('Confirm Action', 'Are you sure you want to ' + actionWord + ' this delivery receipt?', function () {
        const customerDetail = {
            id: $('#clients_details').attr('data-client-id'),
            client_id: $('#clients_details').attr('data-client-client-id'),
            terms: $('#client_term_details').val(),
            date: $('#client_date_details').val()
        };

        const freight_cost = $('#item_freight_details').val() || 0;
        
        const items = item_summary.map(function (item) {
            return {
                product_id: item.product_id,
                unique_id: item.unique_id,
                unique_product_id: products.find(product => product.id == item.product_id)?.product_id || null,
                price: item.price,
                qty: item.qty,
                discounts: item.discounts
            };
        });

        const drData = {
            customer: customerDetail,
            items: items,
            freight_cost: freight_cost
        };

        // Validate data
        const missing = [];
        if (!customerDetail.id) missing.push('Customer Name');
        if (!customerDetail.terms) missing.push('Customer Terms');
        if (!customerDetail.date) missing.push('Customer Date');
        if (items.length === 0) missing.push('Items');

        if (missing.length) {
            alert('Invalid data. Please fill in: ' + missing.join(', '));
            return;
        }

        if (type === "draft") {
            $.ajax({
                url: base_url + '/delivery_receipt/save_draft',
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify(drData),
                beforeSend: function() {
                    $('#draft_btn').prop('disabled', true);
                },
                success: function (response) {
                    let data = JSON.parse(response);
                    if (data.result.status == "success") {
                        alert('Draft saved successfully');
                        clear_item_fields();
                        clear_customer_fields();
                        clear_table_summary();
                        get_products_clients_dr(start, end);
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
                url: base_url + '/delivery_receipt/print_delivery',
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify(drData),
                beforeSend: function() {
                    $('#draft_btn').prop('disabled', true);
                },
                success: function (response) {
                    let data = JSON.parse(response);
                    if (data.result.status == "success") {
                        clear_item_fields();
                        clear_customer_fields();
                        clear_table_summary();
                        get_products_clients_dr(start, end);
                        window.open("/delivery_receipt_view/" + data.result.receipt_id + "/" + 'draft', "_blank");
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
function update_delivery_receipt() {
    showUniversalModal('Confirm Action', 'Are you sure you want to update this delivery receipt?', function () {
        let customerDetail = {
            dr_id: $('#clients_details_name').attr('edit-dr-id'),
            terms: $('#client_term_details').val(),
            date: $('#client_date_details').val()
        };

        let freight_cost = $('#item_freight_details').val() || 0;

        let items = item_summary.map(function (item) {
            return {
                product_id: item.product_id,
                unique_id: item.unique_id,
                unique_product_id: products.find(product => product.id == item.product_id)?.product_id || null,
                price: item.price,
                qty: item.qty,
                discounts: item.discounts
            };
        });
        const drData = { customer: customerDetail, items: items, freight_cost: freight_cost };
        const missing = [];
        if (!customerDetail.terms) missing.push('Customer Terms');
        if (!customerDetail.date) missing.push('Customer Date');
        if (items.length === 0) missing.push('Items');
        if (missing.length) {
            alert('Invalid data. Please fill in: ' + missing.join(', '));
            return;
        }
        $.ajax({
            url: base_url + '/delivery_receipt/update_draft',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(drData),
            beforeSend: function () {
                $('#draft_btn').prop('disabled', true);
            },
            success: function (response) {
                let data; 
                try { data = JSON.parse(response); } catch (e) { alert('Unexpected response'); return; }
                if (data.result && data.result.status === 'success') {
                    alert('Update draft successfully');
                    clear_item_fields();
                    clear_customer_fields();
                    clear_table_summary();
                    if (typeof get_products_clients_dr === 'function') {
                        get_products_clients_dr(start, end);
                    }
                } else {
                    alert('Failed to update draft');
                }
                $('#update_draft_btn').hide();
                $('#cancel_update_draft_btn').hide();
                $('#draft_btn').show();
                $('#print_btn').show();
            },
            error: function (xhr) {
                try { const r = JSON.parse(xhr.responseText); alert(r.error || 'Failed to update'); } catch (e) { alert('Failed to update'); }
            },
            complete: function () {
                $('#draft_btn').prop('disabled', false);
            }
        });
    });
}

/**
 * Cancel edit mode and reset UI state.
 */
function cancel_update_delivery_receipt() {
    clear_item_fields();
    clear_customer_fields();
    if(item_table_list != null) {
        clear_table_summary();
    }
    get_products_clients_dr(start, end);

    $('#update_draft_btn').hide(); // Show the update draft button
    $('#cancel_update_draft_btn').hide();
    $('#draft_btn').show(); // Hide the draft button
    $('#print_btn').show();
}
