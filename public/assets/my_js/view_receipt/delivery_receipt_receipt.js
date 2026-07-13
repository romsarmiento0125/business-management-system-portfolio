function delivery_receipt_view_receipt(header, items, status, show_button) {
    const modal_structure = `
    <div class="modal fade" id="drViewModal" tabindex="-1" aria-labelledby="drViewModalTitle" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5">Delivery Receipt Details</h1>
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
                                        <h3 style="font-size: 18px; margin: 0;">NUMBER 1 FEEDS CORPORATION</h3>
                                        <h5 style="font-size: 14px; margin: 0;">Villarama Highway, Poblacion, Norzagaray, Bulacan</h5>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <div class="d-flex justify-content-start align-items-center mb-3">
                                    <h4 style="font-size: 16px;">DELIVERY RECEIPT</h4>
                                </div>
                            </div>
                        </div>
                        <div class="fw-medium mb-3">
                            <div class="row">
                                <div class="col-8 d-flex">
                                    <p class="mr-1">Delivered&nbsp;to:</p>
                                    <p class="text-decoration-underline">${header.client_name}</p>
                                </div>
                                <div class="col-4 d-flex">
                                    <p class="mr-1">Date:</p>
                                    <p class="text-decoration-underline">${header.dr_date}</p>
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
                                <div class="col-12 d-flex">
                                    <p class="mr-1">Business Style:</p>
                                    <p class="text-decoration-underline">${header.client_business_name}</p>
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
                        <div id="dr_item_lists"></div>
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
                                <p id="dr_modal_item_tot_amount"></p>
                            </div>
                        </div>
                        <div class="row fw-semibold dr_modal_discount_title">
                            <div class="col-1 text-center text-center border-right border-left border-secondary"></div>
                            <div class="col-2 text-center"></div>
                            <div class="col-5 text-right border-right border-left border-secondary">
                                <p>Discount</p>
                            </div>
                            <div class="col-2 text-left "></div>
                            <div class="col-2 text-left border-right border-left border-secondary"></div>
                        </div>
                        <div class="fw-semibold" id="dr_item_discounts"></div>
                        <div class="row fw-semibold dr_modal_discount_freight">
                            <div class="col-1 text-center text-center border-right border-left border-secondary"></div>
                            <div class="col-2 text-center"></div>
                            <div class="col-5 text-right border-right border-left border-secondary">
                                <p>TPA</p>
                            </div>
                            <div class="col-2 text-left "></div>
                            <div class="col-2 text-left border-right border-left border-secondary">
                                <p id="dr_modal_freight"></p>
                            </div>
                        </div>
                        <div class="row fw-semibold border-bottom border-secondary">
                            <div class="col-1 text-center text-center border-right border-left border-secondary"></div>
                            <div class="col-2 text-center"></div>
                            <div class="col-5 text-right border-right border-left border-secondary"></div>
                            <div class="col-2 text-left"></div>
                            <div class="col-2 text-left border-right border-left border-secondary">
                                <p id="dr_modal_total_amount"></p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="d-flex justify-content-between w-100">
                        <div class="">
                            <button type="button" class="btn btn-warning" id="dr_draft_dr" onclick="dr_handleDraftButtonClick(${header.dr_id}, '${header.c_c_id}')" style="display: none;">Return to Draft</button>
                            <button type="button" class="btn btn-danger" id="dr_cancel_dr" onclick="dr_handleCancelButtonClick(${header.dr_id})" style="display: none;">Cancel DR</button>
                            <button type="button" class="btn btn-primary" id="dr_print_dr_modal" onclick="dr_handlePrintButtonClick(${header.dr_id}, ${header.c_id})" style="display: none;">Print Delivery Receipt <i class="fa fa-print"></i></button>
                        </div>
                        <div class="">
                            <button type="button" class="btn btn-primary" id="close_modal" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>`;

    $('#view_dr_receipt_modal').html(modal_structure);
    // Show buttons based on status
    if (show_button) {
        if (status === "printed") {
            $('#dr_draft_dr').show();
            $('#dr_cancel_dr').show();
        } else if (status === "draft") {
            $('#dr_print_dr_modal').show();
        } else {
            // Hide all action buttons
            $('#dr_draft_dr').hide();
            $('#dr_cancel_dr').hide();
            $('#dr_print_dr_modal').hide();
        }
    } else {
        // Hide all action buttons
        $('#dr_draft_dr').hide();
        $('#dr_cancel_dr').hide();
        $('#dr_print_dr_modal').hide();
    }

    if (!header.freight_cost || header.freight_cost == 0) {
        $(".dr_modal_discount_freight").hide();
    }

    dr_item_receipt_list_table(items, header);

    let hasDiscount = false;
    items.forEach(function(item) {
        if (item.discounts && item.discounts.length > 0) {
            hasDiscount = true;
        }
    });

    if (!hasDiscount) {
        $('.dr_modal_discount_title').hide();
    }

    $('#drViewModal').modal('show');
}

function dr_item_receipt_list_table(item_summary, header) {
    item_summary.forEach(function(item) {
        let item_row = `<div class="row"><div class="col-1 text-center border-right border-left border-secondary"><p>${item.qty}</p></div>` +
            `<div class="col-2 text-center"><p>${item.product_unit}</p></div>` +
            `<div class="col-5 text-left border-right border-left border-secondary"><p>${item.product_name} (${item.product_code})</p></div>` +
            `<div class="col-2 text-center"><p>${formatPrice(item.price)}</p></div>` +
            `<div class="col-2 text-center border-right border-left border-secondary"><p>${formatPrice(item.price * item.qty)}</p></div></div>`;
        $("#dr_item_lists").append(item_row);
    });

    dr_compute_receipt_totals(item_summary, header);
}

function dr_compute_receipt_totals(data, header) {
    $('#dr_item_discounts').empty();
    let sum_tot_amnt = 0;
    let total_discounts = 0;
    let sum_tot_amnt_due = 0;

    data.forEach(function (item) {
        const lineTotal = (parseFloat(item.price) || 0) * (parseFloat(item.qty) || 0);
        sum_tot_amnt += lineTotal;
    const lineDiscount = dr_show_receipt_discount_summary(item.discounts || [], parseFloat(item.qty) || 0);
        total_discounts += lineDiscount;
        sum_tot_amnt_due += (lineTotal - lineDiscount);
    });

    const freight = (!header.freight_cost || header.freight_cost === '') ? 0 : parseFloat(header.freight_cost);
    sum_tot_amnt_due = parseFloat(sum_tot_amnt_due) + freight;

    $("#dr_modal_item_tot_amount").text(formatPrice(sum_tot_amnt || 0));
    $("#dr_modal_freight").text(formatPrice(freight || 0));
    $("#dr_modal_total_amount").text(formatPrice(sum_tot_amnt_due || 0));
}

function dr_show_receipt_discount_summary(dis_data, qty) {
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
            $('#dr_item_discounts').append(discount_row);
        }
        disc_value += (dis.value * qty);
    });

        return disc_value;
    }

function dr_handleDraftButtonClick(dr_id, client_id) {
    const auth_modal_structure = `
    <div class="modal fade" id="drAuthModal" tabindex="-1" aria-labelledby="drAuthModalTitle" aria-hidden="true">
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

    $('#dr_auth_modal').html(auth_modal_structure);

    $('#drViewModal').modal('hide');
    $('#drAuthModal').modal('show');

    $('#auth_submit_btn').off('click').on('click', function () {
        const username = $('#auth_username').val();
        const password = $('#auth_password').val();

        if (!username || !password) {
            alert('Please enter username and password.');
            return;
        }

        // Authenticate via AJAX
        $.ajax({
            url: base_url + '/delivery_receipt/authenticate_user',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                username: username,
                password: password
            }),
            success: function (response) {
                let data = JSON.parse(response);
                if (data.status === 'success') {
                    $('#drAuthModal').modal('hide');
                    // Proceed with draft DR logic
                    $.ajax({
                        url: base_url + '/delivery_receipt/draft_dr_receipt',
                        type: 'POST',
                        contentType: 'application/json',
                        data: JSON.stringify({
                            dr_id: dr_id,
                            client_id: client_id
                        }),
                        success: function (response) {
                            let data = JSON.parse(response);
                            if (data.status === 'success') {
                                alert('Delivery receipt drafted successfully');
                            } else {
                                alert('Failed to draft delivery receipt');
                            }
                            cancel_update_delivery_receipt();
                        },
                        error: function (xhr) {
                            if (xhr.status === 400) {
                                let response = JSON.parse(xhr.responseText);
                                alert(response.error);
                            } else {
                                alert('Failed to draft delivery receipt');
                            }
                        }
                    });
                } else {
                    alert('Cannot proceed to draft the DR due to wrong credentials.');
                }
            },
            error: function () {
                alert('Authentication error.');
            }
        });
    });
}

function dr_handleCancelButtonClick(dr_id) {
    const auth_modal_structure = `
    <div class="modal fade" id="drAuthModal" tabindex="-1" aria-labelledby="drAuthModalTitle" aria-hidden="true">
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

    $('#dr_auth_modal').html(auth_modal_structure);

    $('#drViewModal').modal('hide');
    $('#drAuthModal').modal('show');

    $('#auth_submit_btn').off('click').on('click', function () {
        const username = $('#auth_username').val();
        const password = $('#auth_password').val();

        if (!username || !password) {
            alert('Please enter username and password.');
            return;
        }

        // Authenticate via AJAX
        $.ajax({
            url: base_url + '/delivery_receipt/authenticate_user',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                username: username,
                password: password
            }),
            success: function (response) {
                let data = JSON.parse(response);
                if (data.status === 'success') {
                    $('#drAuthModal').modal('hide');
                    // Proceed with cancel DR logic
                    $.ajax({
                        url: base_url + '/delivery_receipt/cancel_dr_receipt',
                        type: 'POST',
                        contentType: 'application/json',
                        data: JSON.stringify({
                            dr_id: dr_id,
                        }),
                        success: function (response) {
                            let data = JSON.parse(response);
                            if (data.status === 'success') {
                                alert('Delivery receipt cancelled successfully');
                            } else {
                                alert('Failed to cancel delivery receipt');
                            }
                            cancel_update_delivery_receipt();
                        },
                        error: function (xhr) {
                            if (xhr.status === 400) {
                                let response = JSON.parse(xhr.responseText);
                                alert(response.error);
                            } else {
                                alert('Failed to cancel delivery receipt');
                            }
                        }
                    });
                } else {
                    alert('Cannot proceed to cancel the DR due to wrong credentials.');
                }
            },
            error: function () {
                alert('Authentication error.');
            }
        });
    });
}

function dr_handlePrintButtonClick(dr_id, client_id) {
    $.ajax({
        url: base_url + '/delivery_receipt/print_dr_receipt',
        type: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({
            dr_id: dr_id,
            client_id: client_id
        }),
        success: function (response) {
            let data = JSON.parse(response);
            if (data.status === 'success') {
                window.open("/delivery_receipt_view/" + dr_id + "/" + "draft", "_blank");
            }
            else {
                alert('Failed to print delivery receipt');
            }
            $('#drViewModal').modal('hide');
        },
        error: function (xhr) {
            if (xhr.status === 400) {
                let response = JSON.parse(xhr.responseText);
                alert(response.error);
            } else {
                alert('Failed to print delivery receipt');
            }
        }
    });

    cancel_update_delivery_receipt()
}

