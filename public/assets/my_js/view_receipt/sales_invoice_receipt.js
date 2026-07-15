function sales_invoice_view_receipt(header, items, status, show_button) {
    const modal_structure = `
    <div class="modal fade" id="siViewModal" tabindex="-1" aria-labelledby="siViewModalTitle" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5">Sales Invoice Details</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" style="overflow-x: auto;">
                    <div class="modal_box mx-5" style="min-width: 750px; font-size: 14px;">
                        <div class="row">
                            <div class="col-12">
                                <div class="d-flex justify-content-center align-items-center mb-3">
                                    <div class="n1_logo">
                                        <img src="/assets/logo.png" alt="Logo" style="max-height: 60px; width: auto;">
                                    </div>
                                    <div class="text-center">
                                        <h3 style="font-size: 18px; margin: 0;">ROM PAULO SARMIENTO</h3>
                                        <h5 style="font-size: 14px; margin: 0;">Poblacion, Norzagaray, Bulacan</h5>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <div class="d-flex justify-content-start align-items-center mb-3">
                                    <h4 style="font-size: 16px;">SALES INVOICE</h4>
                                </div>
                            </div>
                        </div>
                        <div class="fw-medium mb-3">
                            <div class="row">
                                <div class="col-8 d-flex">
                                    <p class="mr-1">SOLD&nbsp;TO:</p>
                                    <p class="text-decoration-underline">${header.client_name}</p>
                                </div>
                                <div class="col-4 d-flex">
                                    <p class="mr-1">Date:</p>
                                    <p class="text-decoration-underline">${header.si_date}</p>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-8 d-flex">
                                    <p class="mr-1">TIN:</p>
                                    <p class="text-decoration-underline">${header.client_tin}</p>
                                </div>
                                <div class="col-4 d-flex">
                                    <p class="mr-1">Terms:</p>
                                    <p class="text-decoration-underline">${terms_set(header.client_term)}</p>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12 d-flex">
                                    <p class="mr-1">Address:</p>
                                    <p class="text-decoration-underline">${header.client_address}</p>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-8 d-flex">
                                    <p class="mr-1">Business Style:</p>
                                    <p class="text-decoration-underline">${header.client_business_name}</p>
                                </div>
                                <div class="col-4">
                                    <p class="mr-1">P.O. No:</p>
                                    <p class="text-decoration-underline"></p>
                                </div>
                            </div>
                        </div>
                        <div class="row fw-semibold">
                            <div class="col-1 text-center border border-secondary">
                                <p>QTY</p>
                            </div>
                            <div class="col-2 text-center border-top border-bottom border-secondary">
                                <p>UNIT</p>
                            </div>
                            <div class="col-5 text-center border border-secondary">
                                <p>ARTICLES</p>
                            </div>
                            <div class="col-2 text-center border-top border-bottom border-secondary">
                                <p>U.PRICE</p>
                            </div>
                            <div class="col-2 text-center border border-secondary">
                                <p>AMOUNT</p>
                            </div>
                        </div>
                        <div class="" id="si_item_lists">
                        </div>

                        <div class="row fw-semibold ">
                            <div class="col-1 text-center text-center border-right border-left border-secondary py-5"></div>
                            <div class="col-2 text-center"></div>
                            <div class="col-5 text-right border-right border-left border-secondary"></div>
                            <div class="col-2 text-center "></div>
                            <div class="col-2 text-center border-right border-left border-secondary"></div>
                        </div>

                        <div class="row fw-semibold">
                            <div class="col-1 text-center text-center border-right border-left border-secondary"></div>
                            <div class="col-2 text-center"></div>
                            <div class="col-5 text-right border-right border-left border-secondary"></div>
                            <div class="col-2 text-left"></div>
                            <div class="col-2 text-left border-right border-left border-secondary">
                                <p id="si_modal_item_tot_amount"></p>
                            </div>
                        </div>

                        <div class="row fw-semibold si_modal_discount_title">
                            <div class="col-1 text-center text-center border-right border-left border-secondary"></div>
                            <div class="col-2 text-center"></div>
                            <div class="col-5 text-right border-right border-left border-secondary">
                                <p>Discount</p>
                            </div>
                            <div class="col-2 text-left "></div>
                            <div class="col-2 text-left border-right border-left border-secondary"></div>
                        </div>

                        <div class="fw-semibold" id="si_item_discounts"></div>

                        <div class="row fw-semibold si_modal_discount_freight">
                            <div class="col-1 text-center text-center border-right border-left border-secondary"></div>
                            <div class="col-2 text-center"></div>
                            <div class="col-5 text-right border-right border-left border-secondary">
                                <p>TPA</p>
                            </div>
                            <div class="col-2 text-left "></div>
                            <div class="col-2 text-left border-right border-left border-secondary">
                                <p id="si_modal_freight">${header.freight_cost}</p>
                            </div>
                        </div>

                        <div class="row fw-semibold ">
                            <div class="col-1 text-center text-center border-right border-left border-secondary"></div>
                            <div class="col-2 text-center"></div>
                            <div class="col-5 text-right border-right border-left border-secondary">
                                <p>VATable Sales</p>
                            </div>
                            <div class="col-2 text-left "></div>
                            <div class="col-2 text-left border-right border-left border-secondary">
                                <p id="si_modal_vatable_sales"></p>
                            </div>
                        </div>
                        <div class="row fw-semibold">
                            <div class="col-1 text-center text-center border-right border-left border-secondary"></div>
                            <div class="col-2 text-center"></div>
                            <div class="col-5 text-right border-right border-left border-secondary">
                                <p>VAT-Exempt Sales</p>
                            </div>
                            <div class="col-2 text-left"></div>
                            <div class="col-2 text-left border-right border-left border-secondary">
                                <p id="si_modal_vat_exempt_sales"></p>
                            </div>
                        </div>
                        <div class="row fw-semibold">
                            <div class="col-1 text-center text-center border-right border-left border-secondary"></div>
                            <div class="col-2 text-center"></div>
                            <div class="col-5 text-right border-right border-left border-secondary">
                                <p>VAT-Zero Rated Sales</p>
                            </div>
                            <div class="col-2 text-left"></div>
                            <div class="col-2 text-left border-right border-left border-secondary">
                                <p id="si_modal_vat_zero_rated_sales">₱0</p>
                            </div>
                        </div>
                        <div class="row fw-semibold">
                            <div class="col-1 text-center text-center border-right border-left border-secondary"></div>
                            <div class="col-2 text-center"></div>
                            <div class="col-5 text-right border-right border-left border-secondary">
                                <p>VAT Amount</p>
                            </div>
                            <div class="col-2 text-left"></div>
                            <div class="col-2 text-left border-right border-left border-secondary">
                                <p id="si_modal_vat_amount"></p>
                            </div>
                        </div>
                        <div class="row fw-semibold border-bottom border-secondary">
                            <div class="col-1 text-center text-center border-right border-left border-secondary"></div>
                            <div class="col-2 text-center"></div>
                            <div class="col-5 text-right border-right border-left border-secondary">
                                <p>TOTAL AMOUNT DUE</p>
                            </div>
                            <div class="col-2 text-left"></div>
                            <div class="col-2 text-left border-right border-left border-secondary">
                                <p id="si_modal_total_amount_due"></p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="d-flex justify-content-between w-100">
                        <div class="">
                            <button type="button" class="btn btn-warning" id='draft_si' onclick="si_handleDraftButtonClick(${header.si_id}, '${header.c_c_id}')" style="display: none;">Return to Draft</button>
                            <button type="button" class="btn btn-danger" id='cancel_si' onclick="si_handleCancelButtonClick(${header.si_id})" style="display: none;">Cancell SI</button>
                            <button type="button" class="btn btn-primary" id='print_si_modal' onclick="si_handlePrintButtonClick(${header.si_id}, ${header.c_id})" style="display: none;">Print Sales Invoice <i class="fa fa-print"></i></button>
                        </div>
                        <div class="">
                            <button type="button" class="btn btn-primary" id='close_modal' data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>`;

    $('#view_si_receipt_modal').html(modal_structure);
    // Show or hide action buttons. If show_button is true we force-hide all action buttons.
    if (show_button) {
        // Default behavior: show/hide based on status
        if (status === "printed") {
            $('#draft_si').show();
            $('#cancel_si').show();
        } else if (status === "draft") {
            $('#print_si_modal').show();
        } else{
            // Hide all action buttons
            $('#draft_si').hide();
            $('#cancel_si').hide();
            $('#print_si_modal').hide();
        }
    } else {
        // When show_button is true the caller requested buttons to be hidden.
        $('#draft_si').hide();
        $('#cancel_si').hide();
        $('#print_si_modal').hide();
    }

    if (!header.freight_cost || header.freight_cost == 0) {
        $(".si_modal_discount_freight").hide();
    }

    si_item_receipt_list_table(items, header);

    let hasDiscount = false;
    items.forEach(function(item) {
        if (item.discounts && item.discounts.length > 0) {
            hasDiscount = true;
        }
    });

    if (!hasDiscount) {
        $('.si_modal_discount_title').hide();
    }
    
    $('#siViewModal').modal('show');
}

function si_item_receipt_list_table(item_summary, header) {
    item_summary.forEach(function(item) {
        let item_row = `<div class="row"><div class="col-1 text-center border-right border-left border-secondary"><p>${item.qty}</p></div>` +
            `<div class="col-2 text-center"><p>${item.product_unit}</p></div>` +
            `<div class="col-5 text-left border-right border-left border-secondary"><p>${item.product_name} (${item.product_code})</p></div>` +
            `<div class="col-2 text-center"><p>${formatPrice(item.price)}</p></div>` +
            `<div class="col-2 text-center border-right border-left border-secondary"><p>${formatPrice(item.price * item.qty)}</p></div></div>`;
        $("#si_item_lists").append(item_row);
    });

    si_compute_receipt_vatables(item_summary, header);
}

function si_compute_receipt_vatables(data, header) {
    $('#si_item_discounts').empty();
    let sum_tot_amnt = 0;
    let sum_disc = 0;
    let sum_vat_sales = 0;
    let sum_exempt = 0;
    let sum_vat = 0;
    let sum_tot_amnt_due = 0;

    data.forEach(function (item) {
        sum_tot_amnt = sum_tot_amnt + (parseFloat(item.price) * parseFloat(item.qty));
    sum_disc = si_show_receipt_discount_summary(item.discounts, parseFloat(item.qty));
        if(item.vat_switch) {
            sum_vat_sales = sum_vat_sales + (((parseFloat(item.price) * parseFloat(item.qty)) - sum_disc) / 1.12);
            sum_vat = sum_vat + (((parseFloat(item.price) * parseFloat(item.qty)) - sum_disc) - (((parseFloat(item.price) * parseFloat(item.qty)) - sum_disc) / 1.12));
        }
        else{
            sum_exempt = sum_exempt + ((parseFloat(item.price) * parseFloat(item.qty)) - sum_disc);
        }
        sum_tot_amnt_due = sum_tot_amnt_due + ((parseFloat(item.price) * parseFloat(item.qty)) - sum_disc);
    });

    sum_tot_amnt_due = parseFloat(sum_tot_amnt_due) + (header.freight_cost === '' ? 0 : parseFloat(header.freight_cost));

    $("#si_modal_item_tot_amount").text(sum_tot_amnt == 0 ? formatPrice(0) : formatPrice(sum_tot_amnt));
    $("#si_modal_vatable_sales").text(sum_vat_sales == "" ? formatPrice(0) : formatPrice(sum_vat_sales));
    $("#si_modal_vat_exempt_sales").text(sum_exempt == "" ? formatPrice(0) : formatPrice(sum_exempt));
    $("#si_modal_vat_amount").text(sum_vat == "" ? formatPrice(0) : formatPrice(sum_vat));
    $("#si_modal_total_amount_due").text(sum_tot_amnt_due == "" ? formatPrice(0) : formatPrice(sum_tot_amnt_due));
}

function si_show_receipt_discount_summary(dis_data, qty) {
    let discount_row = '';
    let disc_value = 0;
    dis_data.forEach(function (dis) {
        if (dis.value == '') {
            disc_value += 0;
        }
        else {
            discount_row = '<div class="row"><div class="col-1 text-center border-right border-left border-secondary"></div>' +
                '<div class="col-2 text-center"></div>' +
                '<div class="col-5 text-right border-right border-left border-secondary"><p>'+ dis.label + '&nbsp;' + dis.value + ' x ' + qty + '</p></div>' +
                '<div class="col-2 text-left"></div>' +
                '<div class="col-2 text-left border-right border-left border-secondary"><p>' + formatPrice(dis.value * qty) + '</p></div></div>';
            $('#si_item_discounts').append(discount_row);
        }
        disc_value += (dis.value * qty);
    });

    return disc_value;
}

function si_handleDraftButtonClick(si_id, client_id) {
    const auth_modal_structure = `
    <div class="modal fade" id="siAuthModal" tabindex="-1" aria-labelledby="siAuthModalTitle" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5">Authentication Required</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="" autocomplete="off" autofill="off" id="auth_form">
                    <div class="modal-body mx-auto">
                        <div class="d-flex align-items-center mb-2">
                            <p>Username:&nbsp;</p>
                            <input type="text" class="form-control" id="auth_username" name="auth_username">
                        </div>
                        <div class="d-flex align-items-center mb-2">
                            <p>Password:&nbsp;</p>
                            <input type="password" id="auth_password" name="auth_password" class="form-control">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" id="auth_submit_btn">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    `;

    $('#si_auth_modal').html(auth_modal_structure);

    $('#siViewModal').modal('hide');
    $('#siAuthModal').modal('show');

    $('#auth_submit_btn').off('click').on('click', function () {
        const username = $('#auth_username').val();
        const password = $('#auth_password').val();

        if (!username || !password) {
            alert('Please enter username and password.');
            return;
        }

        // Authenticate via AJAX
        $.ajax({
            url: base_url + '/sales_invoice/authenticate_user',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                username: username,
                password: password
            }),
            success: function (response) {
                let data = JSON.parse(response);
                if (data.status === 'success') {
                    $('#siAuthModal').modal('hide');
                    // Proceed with draft SI logic
                    $.ajax({
                        url: base_url + '/sales_invoice/draft_si_receipt',
                        type: 'POST',
                        contentType: 'application/json',
                        data: JSON.stringify({
                            si_id: si_id,
                            client_id: client_id
                        }),
                        success: function (response) {
                            let data = JSON.parse(response);
                            if (data.status === 'success') {
                                alert('Sales invoice drafted successfully');
                            } else {
                                alert('Failed to draft sales invoice');
                            }
                            cancel_update_sales_invoice();
                        },
                        error: function (xhr) {
                            if (xhr.status === 400) {
                                let response = JSON.parse(xhr.responseText);
                                alert(response.error);
                            } else {
                                alert('Failed to draft sales invoice');
                            }
                        }
                    });
                } else {
                    alert('Cannot proceed to draft the SI due to wrong credentials.');
                }
            },
            error: function () {
                alert('Authentication error.');
            }
        });
    });
}

function si_handleCancelButtonClick(si_id) {
    const auth_modal_structure = `
    <div class="modal fade" id="siAuthModal" tabindex="-1" aria-labelledby="siAuthModalTitle" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5">Authentication Required</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="" autocomplete="off" autofill="off" id="auth_form">
                    <div class="modal-body mx-auto">
                        <div class="d-flex align-items-center mb-2">
                            <p>Username:&nbsp;</p>
                            <input type="text" class="form-control" id="auth_username" name="auth_username">
                        </div>
                        <div class="d-flex align-items-center mb-2">
                            <p>Password:&nbsp;</p>
                            <input type="password" id="auth_password" name="auth_password" class="form-control">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" id="auth_submit_btn">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    `;

    $('#si_auth_modal').html(auth_modal_structure);

    $('#siViewModal').modal('hide');
    $('#siAuthModal').modal('show');

    $('#auth_submit_btn').off('click').on('click', function () {
        const username = $('#auth_username').val();
        const password = $('#auth_password').val();

        if (!username || !password) {
            alert('Please enter username and password.');
            return;
        }

        // Authenticate via AJAX
        $.ajax({
            url: base_url + '/sales_invoice/authenticate_user',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                username: username,
                password: password
            }),
            success: function (response) {
                let data = JSON.parse(response);
                if (data.status === 'success') {
                    $('#siAuthModal').modal('hide');
                    // Proceed with cancel SI logic
                    $.ajax({
                        url: base_url + '/sales_invoice/cancel_si_receipt',
                        type: 'POST',
                        contentType: 'application/json',
                        data: JSON.stringify({
                            si_id: si_id,
                        }),
                        success: function (response) {
                            let data = JSON.parse(response);
                            if (data.status === 'success') {
                                alert('Sales invoice cancelled successfully');
                            } else {
                                alert('Failed to cancel sales invoice');
                            }
                            cancel_update_sales_invoice();
                        },
                        error: function (xhr) {
                            if (xhr.status === 400) {
                                let response = JSON.parse(xhr.responseText);
                                alert(response.error);
                            } else {
                                alert('Failed to cancel sales invoice');
                            }
                        }
                    });
                } else {
                    alert('Cannot proceed to cancel the SI due to wrong credentials.');
                }
            },
            error: function () {
                alert('Authentication error.');
            }
        });
    });
}

function si_handlePrintButtonClick(si_id, client_id) {
    $.ajax({
        url: base_url + '/sales_invoice/print_si_receipt',
        type: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({
            si_id: si_id,
            client_id: client_id
        }),
        success: function (response) {
            let data = JSON.parse(response);
            if (data.status === 'success') {
                window.open("/sales_invoice_view/" + si_id + "/" + "draft", "_blank");
            }
            else {
                alert('Failed to print sales invoice');
            }
            $('#siViewModal').modal('hide');
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

    cancel_update_sales_invoice()
}
