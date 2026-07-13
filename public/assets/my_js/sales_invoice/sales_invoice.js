// ============================
// Sales Invoice Item Helpers
// ============================
// NOTE: Keep existing function & variable names referenced directly in HTML (onclick attributes)
// to avoid breaking the current view. Internal helper functions prefixed with _.

const VAT_RATE = 0.12; // Central VAT rate definition

/**
 * Populate customer detail labels when a client is selected.
 */
$('#clients_details').change(function () {
    const selectedItem = clients.find(client => client.id == $(this).val());
    if (!selectedItem) return;

    $('#client_tin_details').text(selectedItem.client_tin);
    $('#client_address_details').text(selectedItem.client_address);
    $('#client_company_details').text(selectedItem.client_business_name);
    $('#client_term_details').val(selectedItem.client_term).change();
    // Store both internal id and original client_id for backend payload compatibility
    $('#clients_details')
        .attr('data-client-id', selectedItem.id)
        .attr('data-client-client-id', selectedItem.client_id);
});

$('#products_details').change(function () {
    const selectedItem = products.find(product => product.id == $(this).val());
    if (!selectedItem) return;
    $('#item_price_details').val(selectedItem.product_price);
    if (input_counter === 0) {
        add_discount_input();
    }
    calculate_sales_invoice();
});

/**
 * Calculate current line item amounts (amount, discounts, VAT, totals) and update UI.
 * Uses global vat_switch & discount inputs. Safe if fields empty.
 */
function calculate_sales_invoice() {
    const price = parseFloat($('#item_price_details').val()) || 0;
    const qty = parseFloat($('#item_qty_details').val()) || 0;

    // Aggregate discount values
    let totalDiscountPerUnit = 0;
    $('.item_discounts_value').each(function () {
        totalDiscountPerUnit += parseFloat($(this).val()) || 0;
    });

    const amount = price * qty; // Raw amount before discount
    const discountedUnitPrice = Math.max(price - totalDiscountPerUnit, 0); // Avoid negative
    const totalAmount = discountedUnitPrice * qty;

    // VAT calculations only if vat_switch enabled
    let vatableSales = '';
    let vatAmount = '';
    if (vat_switch) {
        const divisor = 1 + VAT_RATE;
        vatableSales = totalAmount / divisor;
        vatAmount = totalAmount - vatableSales;
    }

    $('#item_amount_details').text(formatPrice(amount)).attr('data-amount', amount);
    $('#item_total_details').text(formatPrice(totalAmount)).attr('data-total-amount', totalAmount);

    if (vat_switch) {
        $('#item_vatsales_details').text(formatPrice(vatableSales)).attr('data-vat-sales', vatableSales);
        $('#item_vat_details').text(formatPrice(vatAmount)).attr('data-vat', vatAmount);
    } else {
        $('#item_vatsales_details').text('').attr('data-vat-sales', '');
        $('#item_vat_details').text('').attr('data-vat', '');
    }
}

$('#item_switch_details').change(function () {
    if ($(this).is(':checked')) {
        $('#item_switch_label_detail').text('Vatable');
        vat_switch = true;
        calculate_sales_invoice();
    } else {
        $('#item_switch_label_detail').text('Not Vatable');
        vat_switch = false;
        calculate_sales_invoice();
    }
});

$('#item_qty_details').on('input', function () {
    calculate_sales_invoice();
});

$('#item_price_details').on('input', function () {
    calculate_sales_invoice();
});

/**
 * Collect discounts from current dynamic inputs.
 * @returns {Array<{value:string,label:string}>}
 */
function _collectDiscounts() {
    const parentElement = $('#add_input_discount');
    const discountsData = [];
    parentElement.find('.item_discounts_value').each(function () {
        const discountValueInput = $(this);
        const discountValue = discountValueInput.val();
        const idParts = discountValueInput.attr('id').split('_');
        const inputCounter = idParts[idParts.length - 1];
        const discountLabelInput = parentElement.find('#item_discount_label_' + inputCounter);
        const discountLabel = discountLabelInput.val();
        discountsData.push({ value: discountValue, label: discountLabel });
    });
    return discountsData;
}

/**
 * Add current item details to summary (invoked by HTML button). Performs validation.
 */
function add_item_details() {
    const unique_id = Date.now();
    const product_id = $('#products_details').val();
    const price = $('#item_price_details').val();
    const qty = $('#item_qty_details').val();

    const missingFields = [];
    if (!product_id) missingFields.push('Product');
    if (!price) missingFields.push('Price');
    if (!qty) missingFields.push('Quantity');
    if (missingFields.length) {
        alert('Invalid data. Please fill in the following fields: ' + missingFields.join(', '));
        return;
    }

    const discountsData = _collectDiscounts();

    item_summary.push({
        unique_id,
        product_id,
        price,
        qty,
        vat_switch,
        discounts: discountsData
    });

    item_list_table();
    clear_item_fields();
}

function clear_item_fields() {
    $('#products_details').empty();
    $('#item_price_details').val('');
    $('#item_qty_details').val('');
    $('#item_switch_details').prop('checked', false);
    $('#item_vatsales_details').text('').attr('data-vat-sales', ''); // fixed attribute consistency
    $('#item_vat_details').text('').attr('data-vat', '');
    $('#item_amount_details').text('').attr('data-amount', '');
    $('#item_total_details').text('').attr('data-total-amount', '');
    $('#add_input_discount').empty();
    $('#item_remove_discount').hide();
    $('#item_add_discount').hide();
    input_counter = 0;
    vat_switch = false;
    populateSelect('#products_details', products, 'product_name_item');
}

function clear_customer_fields() {
    $('.clients_details_container').show();
    $('.clients_details_name_container').hide();

    $('#clients_details').empty();
    $('#clients_details_name').empty();
    $('#client_tin_details').text('');
    $('#client_address_details').text('');
    $('#client_company_details').text('');
    $('#client_term_details').val('cod').change();
    $('#clients_details').attr('data-client-id', ''); // Add this line
    $('#clients_details').attr('data-client-client-id', '');
    $('#clients_details_name').attr('edit-si-id', '');
    $('#client_date_details').val(new Date().toISOString().split('T')[0]);

    populateSelect('#clients_details', clients, 'client_name');
}

function clear_table_summary() {
    item_summary = [];
    $('#summary_total_amount').text('').attr('data-total-amount', '');
    $('#item_freight_details').val('');
    $('#discount_summary').empty();
    $('#summary_vatable_sales').text('').attr('data-vatable-sales', '');
    $('#summary_vat_exempt_sales').text('').attr('data-vat-exempt-sales', '');
    $('#summary_zero_rated').text(''); 
    $('#summary_vat_amount').text('').attr('data-vat-amount', '');
    $('#summary_total_amount_due').text('').attr('data-total-amount-due', '');

    item_table_list.clear().draw();
    item_table_list = null;
}

function add_discount_input() {
    input_counter++;
    const discount_input = `<div class="col-5 mb-2">
            <div class="d-flex align-items-center">
                <p>Discount:&nbsp;</p>
                <input type="number" class="form-control item_discounts_value" id="item_discount_value_${input_counter}">
            </div>
        </div>`;
    const discount_label = `<div class="col-7 mb-2">
            <div class="d-flex align-items-center">
                <p>Discount&nbsp;Label:&nbsp;</p>
                <input type="text" class="form-control item_discounts_label" id="item_discount_label_${input_counter}">
            </div>
        </div>`;
    const showRemove = input_counter > 1;
    $('#item_remove_discount').toggle(showRemove);
    $('#item_add_discount').show();
    $('#add_input_discount').append(discount_input + discount_label);
}

function remove_discount_input() {
    input_counter--;
    const container = $('#add_input_discount');
    const showRemove = input_counter > 1;
    $('#item_remove_discount').toggle(showRemove);
    $('#item_add_discount').show();
    // Remove last pair (value + label)
    container.children().last().remove();
    container.children().last().remove();
    calculate_sales_invoice();
}

$('#add_input_discount').on('input', '.item_discounts_value, .item_discounts_label', function() {
    calculate_sales_invoice();
});