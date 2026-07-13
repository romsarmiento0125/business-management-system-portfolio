var base_url = $('#base_url').val();
var user_role = $('#user_role').val();
var si_table;
var dr_table;
var si_dr_date_start;
var si_dr_date_end;
// Column index trackers for filtering (set when tables are built)
var si_paid_col_index = 6;
var si_status_col_index = 5;
var dr_paid_col_index = 6;
var dr_status_col_index = 5;
var si_dr_type = 1; // 1 = SI, 2 = DR

function showLoader() {
    $('#loader').show();
}

function hideLoader() {
    $('#loader').hide();
}

// Map client terms to days (used for due-date calculations)
function getTermDays(term) {
    if (term === undefined || term === null || term === '') return null; // unknown, don't color
    const t = ('' + term).toLowerCase().trim();
    if (t === 'cod') return 0; // Cash on Delivery -> due today
    if (t === 'flex') return 120; // Custom business rule: FLEX = 120 days
    const asNum = parseInt(t, 10);
    return isNaN(asNum) ? null : asNum; // e.g., '7', '15', '30', etc.
}

// Determine unpaid row status relative to term: 'overdue' | 'within' | null (no highlight)
function evaluateRowTermState(rowData, type) {
    // type: 'si' | 'dr'
    const paid = type === 'si' ? Number(rowData.si_paid) === 1 : Number(rowData.dr_paid) === 1;
    const statusRaw = (type === 'si' ? (rowData.si_status || '') : (rowData.dr_status || '')) + '';
    const status = statusRaw.toLowerCase();

    // Do not color paid or cancelled rows
    if (paid) return null;
    if (status === 'cancelled' || status === 'canceled') return null;

    const termDays = getTermDays(rowData.client_term);
    if (termDays === null) return null; // cannot evaluate without a term
    const dateStr = type === 'si' ? rowData.si_date : rowData.dr_date;
    // Be robust to various formats, e.g., 'YYYY-MM-DD' or 'YYYY-MM-DD HH:mm:ss'
    let baseDate = moment(dateStr, [moment.ISO_8601, 'YYYY-MM-DD', 'YYYY-MM-DD HH:mm:ss'], true);
    if (!baseDate.isValid()) {
        // Fallback: try native Date
        const d = new Date(dateStr);
        if (!isNaN(d.getTime())) baseDate = moment(d);
    }
    if (!baseDate || !baseDate.isValid()) return null;

    const dueDate = baseDate.clone().startOf('day').add(termDays, 'days');
    const today = moment().startOf('day');

    if (today.isAfter(dueDate)) return 'overdue';
    return 'within';
}

$(document).ready(function () {
    // Toggle SI/DR containers on button click and update button states
    $('#btn_show_si').on('click', function () {
        $('#si_table_container').show();
        $('#dr_table_container').hide();
        $('#btn_show_si')
            .removeClass('btn-secondary')
            .addClass('btn-warning');
        $('#btn_show_dr')
            .removeClass('btn-success')
            .addClass('btn-secondary');
        get_si_paid_unpaid();
        si_dr_type = 1;
    });
    $('#btn_show_dr').on('click', function () {
        $('#si_table_container').hide();
        $('#dr_table_container').show();
        $('#btn_show_dr')
            .removeClass('btn-secondary')
            .addClass('btn-success');
        $('#btn_show_si')
            .removeClass('btn-warning')
            .addClass('btn-secondary');
        get_dr_paid_unpaid();
        si_dr_type = 2;
    });

    si_dr_date_start = moment().subtract(30, 'days');
    si_dr_date_end = moment();

    $('#si_dr_date_range').daterangepicker({
        startDate: si_dr_date_start,
        endDate: si_dr_date_end,
        locale: {
            format: 'MM/DD/YYYY'
        }
    });

    $('#si_dr_date_range').on('apply.daterangepicker', function(ev, picker) {
        $(this).val(picker.startDate.format('MM/DD/YYYY') + ' - ' + picker.endDate.format('MM/DD/YYYY'));
        fetchSiDrData();
        if (si_dr_type == 1) {
            get_si_paid_unpaid();
        } else {
            get_dr_paid_unpaid();
        }
    });

    // Bind payment status filter change
    $('#payment_status_filter').on('change', function () {
        applyPaymentFilter();
    });

    // Initial fetch with default date range
    fetchSiDrData();
    get_si_paid_unpaid();
});

function fetchSiDrData() {
    // Get date range from input
    let dateRange = $('#si_dr_date_range').val();
    let date_start, date_end;
    if (dateRange && dateRange.indexOf(' - ') > -1) {
        let parts = dateRange.split(' - ');
        date_start = moment(parts[0], 'MM/DD/YYYY').format('YYYY-MM-DD');
        date_end = moment(parts[1], 'MM/DD/YYYY').format('YYYY-MM-DD');
    } else {
        date_start = si_dr_date_start.format('YYYY-MM-DD');
        date_end = si_dr_date_end.format('YYYY-MM-DD');
    }

    $.ajax({
        url: base_url + '/sidrdashboard/get_si_dr',
        type: 'POST',
        data: {
            date_start: date_start,
            date_end: date_end
        },
        success: function (response) {
            let data = JSON.parse(response);
            si_dr_table(data);
        },
        error: function (xhr) {
            if (xhr.status === 400) {
                let response = JSON.parse(xhr.responseText);
                alert(response.error);
            } else {
                alert('Failed to get data.');
            }
        }
    });
}

function si_dr_table(data) {
    si_table = $('#sales_invoice_list_table').DataTable({
        destroy: true,
        // Disable DataTables' own odd/even striping classes
        stripeClasses: [],  
        data: data.sales_invoice,
        order: [0, 'desc'],
        scrollY: '60vh',
        scrollCollapse: true,
        scrollX: true,
        columns: [
            { data: 'updated_at', visible: false }, // Hide this column
            { data: 'si_id' },
            { data: 'client_name' },
            {
                data: 'si_date', render: function (data) {
                    var date = new Date(data);
                    var options = { year: 'numeric', month: 'long', day: 'numeric' };
                    return date.toLocaleDateString('en-US', options);
                }
            },
            { data: 'total_amount', render: function(data, type, row) {
                    var amount = parseFloat(data);
                    if (isNaN(amount)) amount = 0;
                    var formatted = '₱' + amount.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                    var cls = (row && row.si_status && row.si_status.toLowerCase() === 'cancelled') ? 'text-decoration-line-through' : '';
                    return '<span class="' + cls + '">' + formatted + '</span>';
                }
            },
            { // Terms
                data: 'client_term', render: function (term) {
                    return terms_set(term);
                }
            },
            { 
                data: 'si_status', render: function(data) {
                    return data.charAt(0).toUpperCase() + data.slice(1);
                }
            },
            { 
                data: 'si_paid',
                render: function(data, type, row, meta) {
                    // If the sales invoice is cancelled, show a disabled Cancelled button instead of Paid/Unpaid
                    if (row && row.si_status && row.si_status.toLowerCase() === 'cancelled') {
                        return '<button type="button" class="btn btn-secondary btn-sm" disabled>Cancelled</button>';
                    }
                    var btnText = data == 1 ? 'Paid' : 'Unpaid';
                    // For user roles 4 and 6, just show the text, not a button
                    if (user_role === '4' || user_role === '6') {
                        return btnText;
                    }
                    var btnClass = data == 1 ? 'btn-success' : 'btn-danger';
                    return '<button type="button" class="btn ' + btnClass + ' btn-sm si-paid-btn" data-row="' + meta.row + '" data-si-id="' + row.si_id + '">' + btnText + '</button>';
                }
            },
            { 
                data: function(data){
                    var view_button = '<button type="button" class="btn btn-success mx-1 view_si_btn"><i class="fa fa-eye"></i></button>';
                    return view_button;
                }
            }
        ],
        columnDefs: [
            { targets: '_all', className: 'content_center' },
            { targets: [1, 6, 7], className: 'text-center' }, // Center Status and Paid columns horizontally
        ],
        createdRow: function(row, rowData) {
            var state = evaluateRowTermState(rowData, 'si');
            // Use lighter custom contextual classes for coloring
            if (state === 'overdue') {
                $(row).addClass('table-danger-light'); // light red
            } else if (state === 'within') {
                $(row).addClass('table-warning-light'); // light orange
            }
        },
        rowCallback: function(row, rowData) {
            // Ensure classes persist across redraws and reflect latest data
            $(row).removeClass('table-warning-light table-danger-light');
            var state = evaluateRowTermState(rowData, 'si');
            if (state === 'overdue') {
                $(row).addClass('table-danger-light');
            } else if (state === 'within') {
                $(row).addClass('table-warning-light');
            }
        },
        drawCallback: function () {
            initSiPaidButton();
            init_view_si_button();
        }
    });
    // Ensure Bootstrap zebra striping is removed from the SI table if present in markup
    $('#sales_invoice_list_table').removeClass('table-striped');
    // Update SI column indexes after adding Terms column
    si_status_col_index = 6;
    si_paid_col_index = 7;

    dr_table = $('#delivery_receipt_list_table').DataTable({
        destroy: true,
        // Disable DataTables' own odd/even striping classes
        stripeClasses: [],
        data: data.delivery_receipts,
        order: [0, 'desc'],
        scrollY: '60vh',
        scrollCollapse: true,
        scrollX: true,
        columns: [
            { data: 'updated_at', visible: false }, // Hide this column
            { data: 'dr_id' },
            { data: 'client_name' },
            {
                data: 'dr_date', render: function (data) {
                    var date = new Date(data);
                    var options = { year: 'numeric', month: 'long', day: 'numeric' };
                    return date.toLocaleDateString('en-US', options);
                }
            },
            { data: 'total_amount', render: function(data, type, row) {
                    var amount = parseFloat(data);
                    if (isNaN(amount)) amount = 0;
                    var formatted = '₱' + amount.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                    var cls = (row && row.dr_status && row.dr_status.toLowerCase() === 'cancelled') ? 'text-decoration-line-through' : '';
                    return '<span class="' + cls + '">' + formatted + '</span>';
                }
            },
            { // Terms
                data: 'client_term', render: function (term) {
                    return terms_set(term);
                }
            },
            { 
                data: 'dr_status', render: function(data) {
                    return data.charAt(0).toUpperCase() + data.slice(1);
                }
            },
            { 
                data: 'dr_paid',
                render: function(data, type, row, meta) {
                    // If the delivery receipt is cancelled, show a disabled Cancelled button instead of Paid/Unpaid
                    if (row && row.dr_status && row.dr_status.toLowerCase() === 'cancelled') {
                        return '<button type="button" class="btn btn-secondary btn-sm" disabled>Cancelled</button>';
                    }
                    var btnText = data == 1 ? 'Paid' : 'Unpaid';
                    // For user roles 4 and 6, just show the text, not a button
                    if (user_role === '4' || user_role === '6') {
                        return btnText;
                    }
                    var btnClass = data == 1 ? 'btn-success' : 'btn-danger';
                    return '<button type="button" class="btn ' + btnClass + ' btn-sm dr-paid-btn" data-row="' + meta.row + '" data-dr-id="' + row.dr_id + '">' + btnText + '</button>';
                }
            },
            { 
                data: function(data){
                    var view_button = '<button type="button" class="btn btn-success mx-1 view_dr_btn"><i class="fa fa-eye"></i></button>';
                    return view_button;
                }
            }
        ],
        columnDefs: [
            { targets: '_all', className: 'content_center' },
            { targets: [1, 6, 7], className: 'text-center' }, // Center Status and Paid columns horizontally
        ],
        createdRow: function(row, rowData) {
            var state = evaluateRowTermState(rowData, 'dr');
            if (state === 'overdue') {
                $(row).addClass('table-danger-light'); // light red
            } else if (state === 'within') {
                $(row).addClass('table-warning-light'); // light orange
            }
        },
        rowCallback: function(row, rowData) {
            $(row).removeClass('table-warning-light table-danger-light');
            var state = evaluateRowTermState(rowData, 'dr');
            if (state === 'overdue') {
                $(row).addClass('table-danger-light');
            } else if (state === 'within') {
                $(row).addClass('table-warning-light');
            }
        },
        drawCallback: function () {
            initDrPaidButton();
            init_view_dr_button();
        }
    });
    // Ensure Bootstrap zebra striping is removed from the DR table if present in markup
    $('#delivery_receipt_list_table').removeClass('table-striped');
    // Update DR column indexes after adding Terms column
    dr_status_col_index = 6;
    dr_paid_col_index = 7;

    // Apply current payment status filter (if any) after tables are (re)built
    applyPaymentFilter();
}

function applyPaymentFilter() {
    var val = ($('#payment_status_filter').val() || '').toUpperCase();
    if (typeof si_table !== 'undefined' && si_table) {
        // Clear both related columns before applying specific filter
        si_table.column(si_paid_col_index).search('');
        si_table.column(si_status_col_index).search('');
        switch (val) {
            case 'PAID':
                si_table.column(si_paid_col_index).search('^Paid$', true, false).draw();
                break;
            case 'UNPAID':
                si_table.column(si_paid_col_index).search('^Unpaid$', true, false).draw();
                break;
            case 'CANCELED': // UI uses American spelling; table shows "Cancelled"
                si_table.column(si_status_col_index).search('^Cancelled$', true, false).draw();
                break;
            default:
                si_table.draw();
        }
    }

    if (typeof dr_table !== 'undefined' && dr_table) {
        dr_table.column(dr_paid_col_index).search('');
        dr_table.column(dr_status_col_index).search('');
        switch (val) {
            case 'PAID':
                dr_table.column(dr_paid_col_index).search('^Paid$', true, false).draw();
                break;
            case 'UNPAID':
                dr_table.column(dr_paid_col_index).search('^Unpaid$', true, false).draw();
                break;
            case 'CANCELED':
                dr_table.column(dr_status_col_index).search('^Cancelled$', true, false).draw();
                break;
            default:
                dr_table.draw();
        }
    }
}

function initSiPaidButton() {
    $('.si-paid-btn').off('click').on('click', function () {
        var rowIdx = $(this).data('row');
        var rowData = si_table.row(rowIdx).data();
        showPaymentAuthModal(rowData, 'si');
    });
}

function initDrPaidButton() {
    $('.dr-paid-btn').off('click').on('click', function () {
        var rowIdx = $(this).data('row');
        var rowData = dr_table.row(rowIdx).data();
        showPaymentAuthModal(rowData, 'dr');
    });
}

function init_view_si_button() {
    $('.view_si_btn').off('click').on('click', function () {
        let data = si_table.row($(this).parents('tr')).data();
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
                    alert('No items were found for this sales invoice. This may be due to a system error — the issue has been logged and handled. Please contact support if the problem persists.');
                    return;
                }

                sales_invoice_view_receipt(response_data.header, response_data.items, data.si_status, false);
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

function init_view_dr_button() {
    $('.view_dr_btn').off('click').on('click', function () {
        let data = dr_table.row($(this).parents('tr')).data();
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

                delivery_receipt_view_receipt(response_data.header, response_data.items, data.dr_status, false);
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

function showPaymentAuthModal(rowData, type) {
    // Clear previous values
    $('#auth_username').val('');
    $('#auth_password').val('');
    // Store SI/DR ID and type for later use
    $('#authModal').data('record-id', rowData.si_id || rowData.dr_id);
    $('#authModal').data('record-type', type);

    let modalEl = document.getElementById('authModal');
    let modal = bootstrap.Modal.getOrCreateInstance(modalEl);

    // Just show the modal normally, no extra focus/aria handling
    modal.show();

    $('#auth_submit_btn').off('click').on('click', function () {
        let username = $('#auth_username').val();
        let password = $('#auth_password').val();
        let record_id = $('#authModal').data('record-id');
        let record_type = $('#authModal').data('record-type');
        let row_data = {
            username: username,
            password: password,
            type: record_type,
            id: record_id
        };
        $.ajax({
            url: base_url + '/sidrdashboard/update_si_dr_payment',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(row_data),
            success: function (response) {
                var data = JSON.parse(response);
                if (data.status === 'error') {
                    alert(data.message);
                } else if (data.status === 'success') {
                    fetchSiDrData();
                }
                modal.hide();
            },
            error: function (xhr) {
                if (xhr.status === 500) {
                    var response = JSON.parse(xhr.responseText);
                    alert(response.error);
                } else {
                    alert('Call a system admin');
                }
                modal.hide();
            }
        });
    });
}

function get_si_paid_unpaid() {
    let dateRange = $('#si_dr_date_range').val();
    let date_start, date_end;
    if (dateRange && dateRange.indexOf(' - ') > -1) {
        let parts = dateRange.split(' - ');
        date_start = moment(parts[0], 'MM/DD/YYYY').format('YYYY-MM-DD');
        date_end = moment(parts[1], 'MM/DD/YYYY').format('YYYY-MM-DD');
    } else {
        date_start = si_dr_date_start.format('YYYY-MM-DD');
        date_end = si_dr_date_end.format('YYYY-MM-DD');
    }

    $.ajax({
        url: base_url + '/sidrdashboard/si_get_paid_unpaid',
        type: 'POST',
        dataType: 'json',
        data: {
            date_start:  date_start,
            date_end: date_end,
        },
        success: function (res) {
            if (res && res.success) {
                try {
                    var paidVal = (res.paid_data !== undefined) ? res.paid_data : (res.paid || '');
                    var unpaidVal = (res.unpaid_data !== undefined) ? res.unpaid_data : (res.unpaid || '');

                    // Fill the label and amounts in the info box
                    $('#account_type_paid').text('Sales Invoice');
                    $('#paid_amount').text(formatPrice(paidVal));
                    $('#account_type_unpaid').text('Sales Invoice');
                    $('#unpaid_amount').text(formatPrice(unpaidVal));
                } catch (e) {
                    console.error('Error updating paid/unpaid UI:', e);
                }
            } else {
                console.warn('Unexpected response for si_get_paid_unpaid:', res);
            }
        },
        error: function (xhr, status, err) {
            console.error('Accounting AJAX error:', status, err, xhr && xhr.responseText);
        }
    });
}

function get_dr_paid_unpaid() {
    let dateRange = $('#si_dr_date_range').val();
    let date_start, date_end;
    if (dateRange && dateRange.indexOf(' - ') > -1) {
        let parts = dateRange.split(' - ');
        date_start = moment(parts[0], 'MM/DD/YYYY').format('YYYY-MM-DD');
        date_end = moment(parts[1], 'MM/DD/YYYY').format('YYYY-MM-DD');
    } else {
        date_start = si_dr_date_start.format('YYYY-MM-DD');
        date_end = si_dr_date_end.format('YYYY-MM-DD');
    }

    $.ajax({
        url: base_url + '/sidrdashboard/dr_get_paid_unpaid',
        type: 'POST',
        dataType: 'json',
        data: {
            date_start:  date_start,
            date_end: date_end,
        },
        success: function (res) {
            if (res && res.success) {
                try {
                    var paidVal = (res.paid_data !== undefined) ? res.paid_data : (res.paid || '');
                    var unpaidVal = (res.unpaid_data !== undefined) ? res.unpaid_data : (res.unpaid || '');

                    // Fill the label and amounts in the info box
                    $('#account_type_paid').text('Delivery Receipt');
                    $('#paid_amount').text(formatPrice(paidVal));
                    $('#account_type_unpaid').text('Delivery Receipt');
                    $('#unpaid_amount').text(formatPrice(unpaidVal));
                } catch (e) {
                    console.error('Error updating paid/unpaid UI:', e);
                }
            } else {
                console.warn('Unexpected response for dr_get_paid_unpaid:', res);
            }
        },
        error: function (xhr, status, err) {
            console.error('Accounting AJAX error:', status, err, xhr && xhr.responseText);
        }
    });
}