var base_url = $('#base_url').val();
var start;
var end;

function showLoader() {
    $('#loader').show();
}

function hideLoader() {
    $('#loader').hide();
}

$(document).ready(function () {
    // Restore masked state from localStorage for each toggle
    $('.toggle-mask').each(function () {
        var targetId = $(this).data('target');
        if (localStorage.getItem('mask_' + targetId) === '1') {
            $(this).removeClass('fa-eye').addClass('fa-eye-slash');
            $('#' + targetId).attr('data-masked', '1').attr('data-real', $('#' + targetId).text()).text('******');
        }
    });

    // Toggle mask on click
    $(document).on('click', '.toggle-mask', function () {
        var targetId = $(this).data('target');
        var $number = $('#' + targetId);
        var isMasked = $number.attr('data-masked') === '1';
        if (isMasked) {
            // Reveal
            $number.text($number.attr('data-real')).removeAttr('data-masked');
            $(this).removeClass('fa-eye-slash').addClass('fa-eye');
            localStorage.setItem('mask_' + targetId, '0');
        } else {
            // Mask
            $number.attr('data-masked', '1').attr('data-real', $number.text()).text('******');
            $(this).removeClass('fa-eye').addClass('fa-eye-slash');
            localStorage.setItem('mask_' + targetId, '1');
        }
    });

    start = moment().subtract(30, 'days');
    end = moment();
    $('#reservation').daterangepicker({
        startDate: start,
        endDate: end,
        locale: {
            format: 'MM/DD/YYYY'
        }
    });
    // Trigger handleDateRangeApply when the apply button is clicked
    $('#reservation').on('apply.daterangepicker', function (ev, picker) {
        handleDateRangeApply(picker.startDate, picker.endDate);
    });

    get_dashboard_data(start, end);
});

function handleDateRangeApply(startDate, endDate) {
    start = startDate;
    end = endDate;
    get_dashboard_data(start, end);
}

function get_dashboard_data(start, end) {
    showLoader();
    $.ajax({
        url: base_url + '/dashboard/get_dashboard_data',
        type: 'POST',
        data: JSON.stringify({ start: start.format('YYYY-MM-DD'), end: end.format('YYYY-MM-DD') }),
        success: function (response) {
            let data = JSON.parse(response);
            let result = data.result;
            setInfoBoxValue('dr_total_amount', formatPrice(result.dr_total_amount));
            setInfoBoxValue('dr_total_freight', formatPrice(result.dr_total_freight));
            setInfoBoxValue('si_total_amount', formatPrice(result.si_total_amount));
            setInfoBoxValue('si_total_freight', formatPrice(result.si_total_freight));
            setInfoBoxValue('si_total_vatable_sales', formatPrice(result.si_total_vatable_sales));
            setInfoBoxValue('si_total_vat_exempt_sales', formatPrice(result.si_total_vat_exempt_sales));
            setInfoBoxValue('si_total_vat_amount', formatPrice(result.si_total_vat_amount));
            setInfoBoxValue('dr_gains', formatPrice(result.dr_gains));
            setInfoBoxValue('si_gains', formatPrice(result.si_gains));
            setInfoBoxValue('total_gains', formatPrice(parseFloat(result.dr_gains) + parseFloat(result.si_gains)));
            hideLoader();
        },
        error: function () {
            alert('An error occurred while getting the data.');
            hideLoader();
        }
    });
}

function formatPrice(price) {
    return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'PHP' }).format(price);
}

function setInfoBoxValue(id, value) {
    var $el = $('#' + id);
    $el.attr('data-real', value);
    if (localStorage.getItem('mask_' + id) === '1') {
        $el.attr('data-masked', '1').text('******');
    } else {
        $el.removeAttr('data-masked').text(value);
    }
}

$('#export_accounting_total').on('click', function () {
    showLoader();
    $.ajax({
        url: base_url + '/dashboard/export_accounting_total',
        type: 'POST',
        contentType: 'application/json',
        dataType: 'json',
        data: JSON.stringify({
            start:  start.format('YYYY-MM-DD'),
            end: end.format('YYYY-MM-DD'),
        }),
        success: function (res) {
            if (res.data) {
                buildAndExportTable(res.data || []);
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
});

function buildAndExportTable(data) {
    // Combine both arrays - just append one to the other, no sorting
    let combinedData = [];

    // Process SI data - group by si_id and separate vat/exempt amounts
    let siGrouped = {};
    (data.si_export_data || []).forEach(function(item) {
        let siId = item.si_id || '';
        
        if (!siGrouped[siId]) {
            siGrouped[siId] = {
                si_dr_date: item.si_dr_date,
                client_name: item.client_name,
                client_address: item.client_address,
                client_tin: item.client_tin,
                si_id: item.si_id,
                dr_id: item.dr_id,
                si_dr_status: item.si_dr_status,
                type: 'SI',
                document_id: siId,
                vat_amount: '',
                total_amount: ''
            };
        }
        
        // If vat_check is 1, it's VAT amount
        if (item.vat_check == '1' || item.vat_check == 1) {
            siGrouped[siId].vat_amount = item.total_amount;
        } else {
            // If vat_check is 0, it's exempt amount
            siGrouped[siId].total_amount = item.total_amount;
        }
    });
    
    // Add grouped SI data to combined array
    Object.values(siGrouped).forEach(function(item) {
        combinedData.push(item);
    });
    
    // Add DR data
    (data.dr_export_data || []).forEach(function(item) {
        combinedData.push({
            ...item,
            type: 'DR',
            document_id: item.dr_id || '',
            vat_amount: '',
            vat_check: ''  // Remove vat_check for DR
        });
    });

    // Create a hidden table structure
    const exportTable = `<div class="export_table_wrapper" style="display:none;">
                            <table id="export_accounting_table" class="table display nowrap" style="width:100%;">
                                <thead>
                                     <tr>
                                        <th colspan="6"></th>
                                        <th>Cash</th>
                                        <th>Checks</th>
                                        <th>Accounts</th>
                                        <th>Expanded</th>
                                        <th colspan="2" style="text-align: center;">Sales</th>
                                    </tr>
                                    <tr>
                                        <th colspan="6"></th>
                                        <th>on&nbsp;Hand</th>
                                        <th>on&nbsp;Hand</th>
                                        <th>Receivable</th>
                                        <th>Withholding&nbsp;Tax</th>
                                        <th>VAT</th>
                                        <th>Exempt</th>
                                    </tr>
                                    <tr>
                                        <th>Date</th>
                                        <th>Name&nbsp;of&nbsp;Customer</th>
                                        <th>Address</th>
                                        <th>Tin&nbsp;no</th>
                                        <th>SI&nbsp;no</th>
                                        <th>DR&nbsp;no</th>
                                        <th>Dr.</th>
                                        <th>Dr.</th>
                                        <th>Dr.</th>
                                        <th>Dr.</th>
                                        <th>Cr.</th>
                                        <th>Cr.</th>
                                    </tr>
                                </thead>
                            </table>
                         </div>`;
    $('.data_export_table').html(exportTable);

    // Initialize DataTable with the data
    let table = $('#export_accounting_table').DataTable({
        destroy: true,
        data: combinedData,
        ordering: false, // Disable sorting
        columns: [
            { data: 'si_dr_date' },
            { 
                data: 'client_name',
                render: function(data, type, row) {
                    if (row.si_dr_status && row.si_dr_status.toLowerCase() === 'cancelled') {
                        return data + ' (Cancelled)';
                    }
                    return data;
                }
            },
            { data: 'client_address' },
            { data: 'client_tin' },
            { data: 'si_id' },
            { data: 'dr_id' },
            { data: null, defaultContent: '' },
            { data: null, defaultContent: '' },
            { data: null, defaultContent: '' },
            { data: null, defaultContent: '' },
            { data: 'vat_amount' },
            { data: 'total_amount' }
        ],
        columnDefs: [
            { targets: '_all', className: 'content_center' },
        ],
        layout: {
            topStart: {
                buttons: [
                    {
                        extend: 'excelHtml5',
                        text: 'Excel',
                        filename: 'Accounting_Report_' + start.format('YYYY-MM-DD') + '_to_' + end.format('YYYY-MM-DD')
                    }
                ]
            }
        }
    });

    // Automatically trigger the Excel export
    table.button('.buttons-excel').trigger();
}