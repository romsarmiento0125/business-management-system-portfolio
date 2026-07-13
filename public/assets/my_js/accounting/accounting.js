var base_url = $('#base_url').val();
var date_start;
var date_end;
var client_filter_table;
var product_filter_table;
var pick_si_dr = 'si';
var si_volume_table;
var dr_volume_table;
var si_dr_volume_table;

function showLoader() {
    $('#loader').show();
}

function hideLoader() {
    $('#loader').hide();
}

$(document).ready(function () {
    date_start = moment().subtract(30, 'days');
    date_end = moment();
    $('#date_range').daterangepicker({
        startDate: date_start,
        endDate: date_end,
        locale: {
            format: 'MM/DD/YYYY'
        }
    });

    // Keep globals in sync when user changes the picker
    $('#date_range').on('apply.daterangepicker', function(ev, picker) {
        date_start = picker.startDate;
        date_end = picker.endDate;
        // refresh current view when daterange changes
        if (pick_si_dr == 'si') {
            setActiveAccountingButton('si');
        }
        else if (pick_si_dr == 'dr') {
            setActiveAccountingButton('dr');
        }
        openAccountingView();
    });

    populate_custom_si_filter();
    get_filters();
});

// Reusable function to fetch filters from server. Returns a Promise that resolves with the JSON response.
function get_filters() {
    return new Promise(function (resolve, reject) {
        $.ajax({
            url: base_url + '/accounting/get_filters',
            method: 'POST',
            dataType: 'json',
            success: function (res) {
                if (res && res.success) {
                    renderClientTable(res.clients || []);
                    renderProductTable(res.products || []);
                }
            },
            error: function (xhr, status, err) {
                reject({ xhr: xhr, status: status, err: err });
            }
        });
    });
}

// Render clients into #filter_clients table
function renderClientTable(clients) {
    // Initialize DataTable for clients
    if ($.fn.DataTable.isDataTable('#filter_clients')) {
        $('#filter_clients').DataTable().clear().destroy();
    }

    client_filter_table = $('#filter_clients').DataTable({
        destroy: true,
        data: clients,
        order: [[0, 'desc']],
        columns: [
            { data: 'id', visible: false },
            { data: 'client_name' },
        ],
        columnDefs: [
            { targets: '_all', className: 'content_center' },
        ],
    });
}

// Render products into #filter_products table
function renderProductTable(products) {
    // Initialize DataTable for products
    if ($.fn.DataTable.isDataTable('#filter_products')) {
        $('#filter_products').DataTable().clear().destroy();
    }

    product_filter_table = $('#filter_products').DataTable({
        destroy: true,
        data: products,
        order: [[0, 'desc']],
        columns: [
            { data: 'id', visible: false },
            { 
                data: function (row, type, set, meta) {
                    return row.product_name + (row.product_item ? ' (' + row.product_item + ')' : '');
                }
            },
            { data: function (row, type, set, meta) {
                return formatPrice(row.product_price);
            }},
        ],
        columnDefs: [
            { targets: '_all', className: 'content_center' },
            { targets: [1, 2], className: 'text-left' }
        ],
    });
}

function populate_custom_si_filter() {
    const client = $('#client_filter');
    const product = $('#product_filter');

    $.ajax({
        url: base_url + '/clients/get_custom_filters',
        method: 'POST',
        dataType: 'json',
        success: function (res) {
            // Reset and add default option for both selects
            client.empty();
            client.append($('<option></option>').attr('value', '').text(''));
            client.append($('<option></option>').attr('value', '0').text('All'));

            // Populate client filter options when available
            var filters = (res && res.filters) ? res.filters : [];
            $.each(filters, function (index, filter) {
                // filter has shape: { id, filter_name }
                client.append(
                    $('<option></option>')
                        .attr('value', filter.id)
                        .text(filter.filter_name)
                );
            });

            // If Select2 is in use, trigger change to refresh UI
            try { product.trigger('change.select2'); } catch (e) {}
        },
        error: function (xhr, status, err) {
            console.error('Custom filters AJAX error:', status, err, xhr && xhr.responseText);
            client.empty();
            client.append($('<option></option>').attr('value', 'check_all').text('All'));
        }
    });

    $.ajax({
        url: base_url + '/products/get_custom_filters',
        method: 'POST',
        dataType: 'json',
        success: function (res) {
            product.empty();
            product.append($('<option></option>').attr('value', '').text(''));
            product.append($('<option></option>').attr('value', '0').text('All'));

            // Populate client filter options when available
            var filters = (res && res.filters) ? res.filters : [];
            $.each(filters, function (index, filter) {
                // filter has shape: { id, filter_name }
                product.append(
                    $('<option></option>')
                        .attr('value', filter.id)
                        .text(filter.filter_name)
                );
            });

            // If Select2 is in use, trigger change to refresh UI
            try { product.trigger('change.select2'); } catch (e) {}
        },
        error: function (xhr, status, err) {
            console.error('Custom filters AJAX error:', status, err, xhr && xhr.responseText);
            product.empty();
            product.append($('<option></option>').attr('value', 'check_all').text('All'));
        }
    });
}

// Helper: format the currently selected date range (MM/DD/YYYY - MM/DD/YYYY)
function getSelectedDateRange() {
    const dr = $('#date_range').data('daterangepicker');
    if (!dr) return '';
    return dr.startDate.format('MM/DD/YYYY') + ' - ' + dr.endDate.format('MM/DD/YYYY');
}

function clearSiDrTable() {
    $('#si_dr_table').empty();
}

function sendAccountingRequest(dateStart, dateEnd) {
    // Show loader while fetching accounting data
    showLoader();

    $.ajax({
        url: base_url + '/accounting/get_' + pick_si_dr + '_data_items_accounting',
        type: 'POST',
        dataType: 'json',
        data: {
            date_start: dateStart,
            date_end: dateEnd,
        },
        success: function (res) {
            if (res && res.success) {
                if (pick_si_dr === 'si') {
                    generate_accounting_si(res.data || []);
                }
                else if (pick_si_dr === 'dr') {
                    generate_accounting_dr(res.data || []);
                }
                // After successfully receiving data, hide the accounting filters smoothly
                hideAccountingFilter();
            }
        },
        error: function (xhr, status, err) {
            console.error('Accounting AJAX error:', status, err, xhr && xhr.responseText);
        },
        complete: function () {
            // Always hide the loader when the AJAX call finishes (success or error)
            hideLoader();
        }
    });
}

// Ensure the accounting filter is hidden (smoothly). This differs from toggle: it only hides.
function hideAccountingFilter() {
    const $filter = $('#accounting_filter');
    if ($filter.length === 0) return;
    // If already hidden via d-none or currently animating to hidden, nothing to do
    if ($filter.hasClass('d-none') || $filter.is(':animated')) return;

    $filter.slideUp(300, function () {
        $filter.addClass('d-none').css('display', '');
    });
}

// Helper to set active button state for Sales Invoice (si) or Delivery Receipt (dr)
function setActiveAccountingButton(type) {
    if (type === 'si') {
        $('#sales_invoice_btn').removeClass('btn-secondary btn-success').addClass('btn-warning');
        $('#delivery_receipt_btn').removeClass('btn-warning btn-success').addClass('btn-secondary');
    } else if (type === 'dr') {
        $('#delivery_receipt_btn').removeClass('btn-secondary btn-warning').addClass('btn-success');
        $('#sales_invoice_btn').removeClass('btn-warning btn-success').addClass('btn-secondary');
    }
}

function openAccountingView() {
    const title = (pick_si_dr === 'dr') ? 'Delivery Receipt' : 'Sales Invoice';
    $('#accounting_title').text(title + ' (' + getSelectedDateRange() + ')');
    clearSiDrTable();
    $('#si_dr_table').append('<div class="p-3">Displaying ' + title + ' data for selected filters.</div>');
    $('#accounting_data').removeClass('d-none');

    sendAccountingRequest(date_start.format('YYYY-MM-DD'), date_end.format('YYYY-MM-DD'));
}

    // Aggregate raw product summary rows by product_id.
    // Input rows expected to have: id, is_old, product_id, product_item, product_name, product_unit, total_item_qty
    function aggregateProductSummary(rows) {
        var map = {}; // product_id -> aggregate
        rows.forEach(function (r) {
            var pid = r.product_id;
            var isPreferred = Number(r.is_old) === 0; // user prefers is_old === 0
            if (!pid && pid !== 0) return; // skip invalid

            if (!map[pid]) {
                map[pid] = {
                    // representative id (first seen)
                        id: r.id != null ? r.id : null,
                        // if this row is preferred (is_old === 0) we'll record preferred_id
                        preferred_id: (Number(r.is_old) === 0 && r.id != null) ? r.id : null,
                    product_id: pid,
                    product_name: r.product_name || '',
                    product_item: r.product_item || '',
                    product_unit: r.product_unit || '',
                    total_item_qty: Number(r.total_item_qty) || 0,
                    // prefer name when is_old === 0 (user preference)
                    has_preferred_name: (Number(r.is_old) === 0 && !!r.product_name)
                };
            } else {
                // sum qty
                map[pid].total_item_qty += Number(r.total_item_qty) || 0;
                // no longer tracking all ids; keep representative id and preferred_id only

                if (isPreferred) {
                    // override details from preferred rows (is_old === 0)
                    if (r.id != null) map[pid].preferred_id = r.id;
                    if (r.product_name) {
                        map[pid].product_name = r.product_name;
                        map[pid].has_preferred_name = true;
                    }
                    if (r.product_item) map[pid].product_item = r.product_item;
                    if (r.product_unit) map[pid].product_unit = r.product_unit;
                } else {
                    // if no preferred details yet, fill blanks
                    if (!map[pid].has_preferred_name && r.product_name) {
                        map[pid].product_name = r.product_name;
                    }
                    if (!map[pid].product_item && r.product_item) map[pid].product_item = r.product_item;
                    if (!map[pid].product_unit && r.product_unit) map[pid].product_unit = r.product_unit;
                }
            }
        });

        // Convert map to array and strip helper flags
        var out = Object.keys(map).map(function (k) {
            var v = map[k];
            return {
                // use preferred_id when available otherwise fallback to first seen id
                id: v.preferred_id != null ? v.preferred_id : v.id,
                product_id: v.product_id,
                product_name: v.product_name,
                product_item: v.product_item,
                product_unit: v.product_unit,
                total_item_qty: v.total_item_qty
            };
        });
        return out;
    }

// Click handlers for the export buttons
$(document).on('click', '#sales_invoice_btn', function () {
    pick_si_dr = 'si';
    openAccountingView();
    // Update button classes
    setActiveAccountingButton('si');
});

$(document).on('click', '#delivery_receipt_btn', function () {
    pick_si_dr = 'dr';
    openAccountingView();
    // Update button classes
    setActiveAccountingButton('dr');
});

function populate_export_table(data) {
    const title = (pick_si_dr === 'dr') ? 'Delivery Receipt' : 'Sales Invoice';
    const exportTable = `<div class="export_table_wrapper" style="display:none;">
                            <table id="export_qty_table" class="table display nowrap" style="width:100%;">
                                <thead>
                                    <tr>
                                        <th>Product&nbsp;name</th>
                                        <th>Product&nbsp;code</th>
                                        <th>Total&nbsp;qty</th>
                                        <th>Product&nbsp;unit</th>
                                    </tr>
                                </thead>
                            </table>
                         </div>`;
    $('.data_export_table').html(exportTable);

    let table = $('#export_qty_table').DataTable({
        destroy: true,
        data: data,
        order: [[0, 'asc']],
        columns: [
            { data: 'product_name' },
            { data: 'product_item' },
            { data: 'total_item_qty' },
            { data: 'product_unit' }
        ],
        columnDefs: [
            { targets: '_all', className: 'content_center' },
        ],
        layout: {
            topStart: {
                buttons: [
                    {
                        extend: 'excelHtml5',   // or 'excel'
                        text: 'Excel',
                        filename: 'Product sales report for ' +  title + ' ' + date_start.format('YYYY-MM-DD') + ' to ' + date_end.format('YYYY-MM-DD') // -> MyExportFile.xlsx
                    }
                ]
            }
        }
    });

    table.button('.buttons-excel').trigger();
}

// Smoothly toggle (hide/show) the accounting filter panel using slideUp/slideDown
function toggleAccountingFilter() {
    const $filter = $('#accounting_filter');
    if ($filter.length === 0) return; // nothing to do
    // Prevent double-trigger while animating
    if ($filter.is(':animated')) return;

    // If currently hidden via d-none, prepare it to slide down
    if ($filter.hasClass('d-none')) {
        // Remove d-none but keep it visually hidden, then slide down
        $filter.removeClass('d-none').css('display', 'none').slideDown(300);
    } else {
        // Slide up then add d-none and clear inline display so class controls visibility
        $filter.slideUp(300, function () {
            $filter.addClass('d-none').css('display', '');
        });
    }
}

// Wire the hide_filter button to toggle the accounting filter smoothly
$(document).on('click', '#hide_filter', function () {
    toggleAccountingFilter();
});

// Open dynamic filter view for custom client filter in a new tab
$(document).on('click', '#add_custom_client_filter', function () {
    window.open('/dynamic_filter_client', '_blank');
});

// Open dynamic filter view for custom product filter in a new tab
$(document).on('click', '#add_custom_product_filter', function () {
    window.open('/dynamic_filter_product', '_blank');
});

$(document).on('change', '#client_filter', function () {
    let val = $(this).val();

    // For a specific saved filter, fetch the client list and apply
    $.ajax({
        url: base_url + '/accounting/dynamic_change_client_show',
        method: 'POST',
        data: {
            filter_id:  val,
        },
        success: function (res) {
            if (res.success) {
                get_filters();
            }
            else {
                alert('Failed to apply client filter.');
            }
        },
        error: function (xhr, status, err) {
            console.error('Error loading client filter:', status, err, xhr && xhr.responseText);
        }
    });
});

$(document).on('change', '#product_filter', function () {
    let val = $(this).val();

    // For a specific saved filter, fetch the client list and apply
    $.ajax({
        url: base_url + '/accounting/dynamic_change_product_show',
        method: 'POST',
        data: {
            filter_id:  val,
        },
        success: function (res) {
            if (res.success) {
                get_filters();
            }
            else {
                alert('Failed to apply product filter.');
            }
        },
        error: function (xhr, status, err) {
            console.error('Error loading product filter:', status, err, xhr && xhr.responseText);
        }
    });
});

$(document).on('click', '#si_volume_view', function () {
    showLoader();
    $.ajax({
        url: base_url + '/accounting/get_si_volume',
        type: 'POST',
        dataType: 'json',
        data: {
            date_start:  date_start.format('YYYY-MM-DD'),
            date_end: date_end.format('YYYY-MM-DD'),
        },
        success: function (res) {
           populate_si_volume_table(res.data || []);
        },
        error: function (xhr, status, err) {
            console.error('Accounting AJAX error:', status, err, xhr && xhr.responseText);
        },
        complete: function () {
            // Always hide the loader when the AJAX call finishes (success or error)
            hideLoader();
        }
    });
    $('#si_volume_modal').modal('show');
});

$(document).on('click', '#si_volume_export', function () {
    if (si_volume_table) {
        si_volume_table.button('.buttons-excel').trigger();
    }
});

$(document).on('click', '#dr_volume_view', function () {
    showLoader();
    $.ajax({
        url: base_url + '/accounting/get_dr_volume',
        type: 'POST',
        dataType: 'json',
        data: {
            date_start:  date_start.format('YYYY-MM-DD'),
            date_end: date_end.format('YYYY-MM-DD'),
        },
        success: function (res) {
           populate_dr_volume_table(res.data || []);
        },
        error: function (xhr, status, err) {
            console.error('Accounting AJAX error:', status, err, xhr && xhr.responseText);
        },
        complete: function () {
            // Always hide the loader when the AJAX call finishes (success or error)
            hideLoader();
        }
    });
    $('#dr_volume_modal').modal('show');
});

$(document).on('click', '#dr_volume_export', function () {
    if (dr_volume_table) {
        dr_volume_table.button('.buttons-excel').trigger();
    }
});

$(document).on('click', '#total_volume_view', function () {
    showLoader();
    $.ajax({
        url: base_url + '/accounting/get_si_dr_volume',
        type: 'POST',
        dataType: 'json',
        data: {
            date_start:  date_start.format('YYYY-MM-DD'),
            date_end: date_end.format('YYYY-MM-DD'),
        },
        success: function (res) {
           populate_si_dr_volume_table(res.data || []);
        },
        error: function (xhr, status, err) {
            console.error('Accounting AJAX error:', status, err, xhr && xhr.responseText);
        },
        complete: function () {
            // Always hide the loader when the AJAX call finishes (success or error)
            hideLoader();
        }
    });
    $('#si_dr_volume_modal').modal('show');
});

$(document).on('click', '#si_dr_volume_export', function () {
    if (si_dr_volume_table) {
        si_dr_volume_table.button('.buttons-excel').trigger();
    }
});

function applyExcelRowColors(xlsx, dtTable) {
    var colors = ['FDECEA', 'D1E7DD', 'F8D7DA', 'CFE2FF'];

    // --- styles.xml: add fills + cellXfs ---
    var sSh = xlsx.xl['styles.xml'];
    var fills = $('fills', sSh);
    var fillStart = parseInt($('fills', sSh).attr('count'));
    colors.forEach(function(c) {
        fills.append(
            '<fill><patternFill patternType="solid">' +
            '<fgColor rgb="FF' + c + '"/>' +
            '<bgColor indexed="64"/>' +
            '</patternFill></fill>'
        );
    });
    $('fills', sSh).attr('count', fillStart + colors.length);

    var cellXfs = $('cellXfs', sSh);
    var xfStart = parseInt(cellXfs.attr('count'));
    for (var i = 0; i < colors.length; i++) {
        cellXfs.append(
            '<xf numFmtId="0" fontId="0" fillId="' + (fillStart + i) + '" borderId="0" xfId="0" applyFill="1"/>'
        );
    }
    cellXfs.attr('count', xfStart + colors.length);

    // --- sheet1.xml: colour each data row ---
    var exportedData = dtTable.rows({ search: 'applied', order: 'applied' }).data().toArray();
    var sheet = xlsx.xl.worksheets['sheet1.xml'];
    $('row', sheet).each(function(i) {
        if (i <= 1) return; // skip title row (i=0) and column header row (i=1)
        var rd = exportedData[i - 2];
        if (!rd) return;
        var net_profit = (Number(rd.gross_sales) || 0) - (Number(rd.total_cost) || 0);
        var styleIdx;
        if (net_profit <= 0) {
            styleIdx = xfStart;       // danger-light
        } else if (rd.product_tag === 'A') {
            styleIdx = xfStart + 1;   // success
        } else if (rd.product_tag === 'B') {
            styleIdx = xfStart + 2;   // danger
        } else if (rd.product_tag === 'C') {
            styleIdx = xfStart + 3;   // primary
        }
        if (styleIdx !== undefined) {
            $('c', this).attr('s', styleIdx);
        }
    });
}

function populate_si_volume_table(data) {

    // Fixed tag → Bootstrap color mapping
    var tagColorMap  = { 'A': 'table-success', 'B': 'table-danger', 'C': 'table-primary' };
    var tagBadgeMap  = { 'A': 'text-bg-success', 'B': 'text-bg-danger', 'C': 'text-bg-primary' };
    var uniqueTags = [];
    data.forEach(function(r) {
        if (r.product_tag && uniqueTags.indexOf(r.product_tag) === -1) {
            uniqueTags.push(r.product_tag);
        }
    });
    uniqueTags.sort();

    si_volume_table = $('#si_volume_table').DataTable({
        destroy: true,
        data: data,
        order: [[0, 'asc']],
        columns: [
            {
                data: null,
                render: function (data, type) {
                    let name = data.product_name || '';
                    let item = data.product_item || 'No code';
                    if (type === 'sort' || type === 'order') {
                        let sortTag = data.product_tag ? data.product_tag : 'zzz_no_tag';
                        return sortTag + '|||' + name;
                    }
                    if (type === 'display') {
                        let truncated = name.length > 15 ? name.substring(0, 15) + '...' : name;
                        return '<span title="' + name + '">' + truncated + '</span> (' + item + ')';
                    }
                    return name + ' (' + item + ')';
                },
            },
            { data: 'total_item_qty' },
            {
                data: null,
                render: function (data, type) {
                    let cost = data.cost || 0;
                    let formatted;
                    if (typeof cost === 'string' && cost.includes(',')) {
                        formatted = cost.split(',').map(function(v) { return formatPrice(Number(v.trim()) || 0); }).join(', ');
                    } else {
                        formatted = formatPrice(Number(cost) || 0);
                    }
                    if (type === 'display') {
                        let truncated = formatted.length > 15 ? formatted.substring(0, 15) + '...' : formatted;
                        return '<span title="' + formatted + '">' + truncated + '</span>';
                    }
                    if (type === 'export') return formatted.replace(/₱/g, '');
                    return formatted;
                }
            },
            {
                data: null,
                render: function (data, type) {
                    var formatted = formatPrice(data.total_cost || 0);
                    if (type === 'export') return formatted.replace(/₱/g, '');
                    return formatted;
                }
            },
            {
                data: null,
                render: function (data, type) {
                    let selling = data.selling_price || 0;
                    let formatted;
                    if (typeof selling === 'string' && selling.includes(',')) {
                        formatted = selling.split(',').map(function(v) { return formatPrice(Number(v.trim()) || 0); }).join(', ');
                    } else {
                        formatted = formatPrice(Number(selling) || 0);
                    }
                    if (type === 'display') {
                        let truncated = formatted.length > 15 ? formatted.substring(0, 15) + '...' : formatted;
                        return '<span title="' + formatted + '">' + truncated + '</span>';
                    }
                    if (type === 'export') return formatted.replace(/₱/g, '');
                    return formatted;
                }
            },
            {
                data: null,
                render: function (data, type) {
                    var formatted = formatPrice(data.gross_sales || 0);
                    if (type === 'export') return formatted.replace(/₱/g, '');
                    return formatted;
                }
            },
            {
                data: null,
                render: function (data, type) {
                    let net_profit = (Number(data.gross_sales) || 0) - (Number(data.total_cost) || 0);
                    let formatted = formatPrice(net_profit);
                    if (type === 'display') {
                        let truncated = formatted.length > 15 ? formatted.substring(0, 15) + '...' : formatted;
                        return '<span title="' + formatted + '">' + truncated + '</span>';
                    }
                    if (type === 'export') return formatted.replace(/₱/g, '');
                    return formatted;
                }
            },
        ],
        columnDefs: [
            { targets: '_all', className: 'content_center' },
        ],
        createdRow: function(row, rowData) {
            let net_profit = (Number(rowData.gross_sales) || 0) - (Number(rowData.total_cost) || 0);
            if (net_profit <= 0) {
                $(row).addClass('table-danger-light'); // light red — takes priority over tag color
            } else if (rowData.product_tag && tagColorMap[rowData.product_tag]) {
                $(row).addClass(tagColorMap[rowData.product_tag]);
            }
            // untagged rows: no class → plain white
        },
        buttons: [
            {
                extend: 'excelHtml5',
                text: 'Export to Excel',
                filename: 'SI_Volume_Export_' + moment().format('YYYYMMDD_HHmmss'),
                exportOptions: {
                    orthogonal: 'export'
                },
                customize: function(xlsx) {
                    applyExcelRowColors(xlsx, si_volume_table);
                }
            }
        ]
    });

    // Render color legend above the table
    var $legend = $('#si_volume_tag_legend');
    if ($legend.length) {
        if (uniqueTags.length > 0) {
            var legendHtml = '<div class="d-flex flex-wrap gap-2 align-items-center"><small class="text-muted me-1">Tags:</small>';
            uniqueTags.forEach(function(tag) {
                legendHtml += '<span class="badge ' + tagBadgeMap[tag] + '">' + tag + '</span>';
            });
            legendHtml += '</div>';
            $legend.html(legendHtml);
        } else {
            $legend.html('');
        }
    }
}

function populate_dr_volume_table(data) {
    var tagColorMap = { 'A': 'table-success', 'B': 'table-danger', 'C': 'table-primary' };
    var tagBadgeMap = { 'A': 'text-bg-success', 'B': 'text-bg-danger', 'C': 'text-bg-primary' };
    var uniqueTags = [];
    data.forEach(function(r) {
        if (r.product_tag && uniqueTags.indexOf(r.product_tag) === -1) {
            uniqueTags.push(r.product_tag);
        }
    });
    uniqueTags.sort();

    dr_volume_table = $('#dr_volume_table').DataTable({
        destroy: true,
        data: data,
        order: [[0, 'asc']],
        columns: [
            {
                data: null,
                render: function (data, type) {
                    let name = data.product_name || '';
                    let item = data.product_item || 'No code';
                    if (type === 'sort' || type === 'order') {
                        let sortTag = data.product_tag ? data.product_tag : 'zzz_no_tag';
                        return sortTag + '|||' + name;
                    }
                    if (type === 'display') {
                        let truncated = name.length > 15 ? name.substring(0, 15) + '...' : name;
                        return '<span title="' + name + '">' + truncated + '</span> (' + item + ')';
                    }
                    return name + ' (' + item + ')';
                },
            },
            { data: 'total_item_qty' },
            {
                data: null,
                render: function (data, type) {
                    let cost = data.cost || 0;
                    let formatted;
                    if (typeof cost === 'string' && cost.includes(',')) {
                        formatted = cost.split(',').map(function(v) { return formatPrice(Number(v.trim()) || 0); }).join(', ');
                    } else {
                        formatted = formatPrice(Number(cost) || 0);
                    }
                    if (type === 'display') {
                        let truncated = formatted.length > 15 ? formatted.substring(0, 15) + '...' : formatted;
                        return '<span title="' + formatted + '">' + truncated + '</span>';
                    }
                    if (type === 'export') return formatted.replace(/₱/g, '');
                    return formatted;
                }
            },
            {
                data: null,
                render: function (data, type) {
                    var formatted = formatPrice(data.total_cost || 0);
                    if (type === 'export') return formatted.replace(/₱/g, '');
                    return formatted;
                }
            },
            {
                data: null,
                render: function (data, type) {
                    let selling = data.selling_price || 0;
                    let formatted;
                    if (typeof selling === 'string' && selling.includes(',')) {
                        formatted = selling.split(',').map(function(v) { return formatPrice(Number(v.trim()) || 0); }).join(', ');
                    } else {
                        formatted = formatPrice(Number(selling) || 0);
                    }
                    if (type === 'display') {
                        let truncated = formatted.length > 15 ? formatted.substring(0, 15) + '...' : formatted;
                        return '<span title="' + formatted + '">' + truncated + '</span>';
                    }
                    if (type === 'export') return formatted.replace(/₱/g, '');
                    return formatted;
                }
            },
            {
                data: null,
                render: function (data, type) {
                    var formatted = formatPrice(data.gross_sales || 0);
                    if (type === 'export') return formatted.replace(/₱/g, '');
                    return formatted;
                }
            },
            {
                data: null,
                render: function (data, type) {
                    let net_profit = (Number(data.gross_sales) || 0) - (Number(data.total_cost) || 0);
                    let formatted = formatPrice(net_profit);
                    if (type === 'display') {
                        let truncated = formatted.length > 15 ? formatted.substring(0, 15) + '...' : formatted;
                        return '<span title="' + formatted + '">' + truncated + '</span>';
                    }
                    if (type === 'export') return formatted.replace(/₱/g, '');
                    return formatted;
                }
            },
        ],
        columnDefs: [
            { targets: '_all', className: 'content_center' },
        ],
        createdRow: function(row, rowData) {
            let net_profit = (Number(rowData.gross_sales) || 0) - (Number(rowData.total_cost) || 0);
            if (net_profit <= 0) {
                $(row).addClass('table-danger-light'); // light red
            } else if (rowData.product_tag && tagColorMap[rowData.product_tag]) {
                $(row).addClass(tagColorMap[rowData.product_tag]);
            }
        },
        buttons: [
            {
                extend: 'excelHtml5',
                text: 'Export to Excel',
                filename: 'DR_Volume_Export_' + moment().format('YYYYMMDD_HHmmss'),
                exportOptions: {
                    orthogonal: 'export'
                },
                customize: function(xlsx) {
                    applyExcelRowColors(xlsx, dr_volume_table);
                }
            }
        ]
    });

    var $drLegend = $('#dr_volume_tag_legend');
    if ($drLegend.length) {
        if (uniqueTags.length > 0) {
            var legendHtml = '<div class="d-flex flex-wrap gap-2 align-items-center"><small class="text-muted me-1">Tags:</small>';
            uniqueTags.forEach(function(tag) {
                legendHtml += '<span class="badge ' + (tagBadgeMap[tag] || 'text-bg-secondary') + '">' + tag + '</span>';
            });
            legendHtml += '</div>';
            $drLegend.html(legendHtml);
        } else {
            $drLegend.html('');
        }
    }
}

function populate_si_dr_volume_table(data) {
    var tagColorMap = { 'A': 'table-success', 'B': 'table-danger', 'C': 'table-primary' };
    var tagBadgeMap = { 'A': 'text-bg-success', 'B': 'text-bg-danger', 'C': 'text-bg-primary' };
    var uniqueTags = [];
    data.forEach(function(r) {
        if (r.product_tag && uniqueTags.indexOf(r.product_tag) === -1) {
            uniqueTags.push(r.product_tag);
        }
    });
    uniqueTags.sort();

    si_dr_volume_table = $('#si_dr_volume_table').DataTable({
        destroy: true,
        data: data,
        order: [[0, 'asc']],
        columns: [
            {
                data: null,
                render: function (data, type) {
                    let name = data.product_name || '';
                    let item = data.product_item || 'No code';
                    if (type === 'sort' || type === 'order') {
                        let sortTag = data.product_tag ? data.product_tag : 'zzz_no_tag';
                        return sortTag + '|||' + name;
                    }
                    if (type === 'display') {
                        let truncated = name.length > 15 ? name.substring(0, 15) + '...' : name;
                        return '<span title="' + name + '">' + truncated + '</span> (' + item + ')';
                    }
                    return name + ' (' + item + ')';
                },
            },
            { data: 'total_item_qty' },
            {
                data: null,
                render: function (data, type) {
                    let cost = data.cost || 0;
                    let formatted;
                    if (typeof cost === 'string' && cost.includes(',')) {
                        formatted = cost.split(',').map(function(v) { return formatPrice(Number(v.trim()) || 0); }).join(', ');
                    } else {
                        formatted = formatPrice(Number(cost) || 0);
                    }
                    if (type === 'display') {
                        let truncated = formatted.length > 15 ? formatted.substring(0, 15) + '...' : formatted;
                        return '<span title="' + formatted + '">' + truncated + '</span>';
                    }
                    if (type === 'export') return formatted.replace(/₱/g, '');
                    return formatted;
                }
            },
            {
                data: null,
                render: function (data, type) {
                    var formatted = formatPrice(data.total_cost || 0);
                    if (type === 'export') return formatted.replace(/₱/g, '');
                    return formatted;
                }
            },
            {
                data: null,
                render: function (data, type) {
                    let selling = data.selling_price || 0;
                    let formatted;
                    if (typeof selling === 'string' && selling.includes(',')) {
                        formatted = selling.split(',').map(function(v) { return formatPrice(Number(v.trim()) || 0); }).join(', ');
                    } else {
                        formatted = formatPrice(Number(selling) || 0);
                    }
                    if (type === 'display') {
                        let truncated = formatted.length > 15 ? formatted.substring(0, 15) + '...' : formatted;
                        return '<span title="' + formatted + '">' + truncated + '</span>';
                    }
                    if (type === 'export') return formatted.replace(/₱/g, '');
                    return formatted;
                }
            },
            {
                data: null,
                render: function (data, type) {
                    var formatted = formatPrice(data.gross_sales || 0);
                    if (type === 'export') return formatted.replace(/₱/g, '');
                    return formatted;
                }
            },
            {
                data: null,
                render: function (data, type) {
                    let net_profit = (Number(data.gross_sales) || 0) - (Number(data.total_cost) || 0);
                    let formatted = formatPrice(net_profit);
                    if (type === 'display') {
                        let truncated = formatted.length > 15 ? formatted.substring(0, 15) + '...' : formatted;
                        return '<span title="' + formatted + '">' + truncated + '</span>';
                    }
                    if (type === 'export') return formatted.replace(/₱/g, '');
                    return formatted;
                }
            },
        ],
        columnDefs: [
            { targets: '_all', className: 'content_center' },
        ],
        createdRow: function(row, rowData) {
            let net_profit = (Number(rowData.gross_sales) || 0) - (Number(rowData.total_cost) || 0);
            if (net_profit <= 0) {
                $(row).addClass('table-danger-light'); // light red
            } else if (rowData.product_tag && tagColorMap[rowData.product_tag]) {
                $(row).addClass(tagColorMap[rowData.product_tag]);
            }
        },
        buttons: [
            {
                extend: 'excelHtml5',
                text: 'Export to Excel',
                filename: 'SI_DR_Volume_Export_' + moment().format('YYYYMMDD_HHmmss'),
                exportOptions: {
                    orthogonal: 'export'
                },
                customize: function(xlsx) {
                    applyExcelRowColors(xlsx, si_dr_volume_table);
                }
            }
        ]
    });

    var $siDrLegend = $('#si_dr_volume_tag_legend');
    if ($siDrLegend.length) {
        if (uniqueTags.length > 0) {
            var legendHtml = '<div class="d-flex flex-wrap gap-2 align-items-center"><small class="text-muted me-1">Tags:</small>';
            uniqueTags.forEach(function(tag) {
                legendHtml += '<span class="badge ' + (tagBadgeMap[tag] || 'text-bg-secondary') + '">' + tag + '</span>';
            });
            legendHtml += '</div>';
            $siDrLegend.html(legendHtml);
        } else {
            $siDrLegend.html('');
        }
    }
}


