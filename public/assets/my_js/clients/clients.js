var base_url = $('#base_url').val();
var user_role = $('#user_role').val();
var client_table_data;

function showLoader() {
    $('#loader').show();
}

function hideLoader() {
    $('#loader').hide();
}

$(document).ready(function () {
    // Hide Add Client button for user role 4
    if (user_role === '4') {
        $('#addClientBtn').hide();
    }
    
    get_table_clients();
    $('#addClientModal').on('shown.bs.modal', function () {
        $('.select2').select2({
            dropdownParent: $('#addClientModal')
        });
    });
    $('#editClientModal').on('shown.bs.modal', function () {
        $('.select2').select2({
            dropdownParent: $('#editClientModal')
        });
    });

    // Volume modal: init daterangepicker on open and reset result
    $('#volumeModal').on('shown.bs.modal', function () {
        var defaultStart = moment().subtract(30, 'days');
        var defaultEnd   = moment();
        $('#volume_daterange').daterangepicker({
            startDate: defaultStart,
            endDate: defaultEnd,
            locale: {
                cancelLabel: 'Clear',
                format: 'YYYY-MM-DD'
            }
        });
        $('#volume_daterange').val(defaultStart.format('YYYY-MM-DD') + ' - ' + defaultEnd.format('YYYY-MM-DD'));
        $('#volume_daterange').on('apply.daterangepicker', function (ev, picker) {
            $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD'));
        });
        $('#volume_daterange').on('cancel.daterangepicker', function () {
            $(this).val('');
        });

        // Populate product filter
        var productSelect = $('#product_filter');
        $.ajax({
            url: base_url + '/products/get_custom_filters',
            method: 'POST',
            dataType: 'json',
            success: function (res) {
                productSelect.empty();
                productSelect.append($('<option></option>').val('').text('All Products'));
                var filters = (res && res.filters) ? res.filters : [];
                $.each(filters, function (i, f) {
                    productSelect.append($('<option></option>').val(f.id).text(f.filter_name));
                });
                productSelect.select2({ dropdownParent: $('#volumeModal') });
            },
            error: function () {
                productSelect.empty();
                productSelect.append($('<option></option>').val('').text('All Products'));
                productSelect.select2({ dropdownParent: $('#volumeModal') });
            }
        });
    });

    $('#volumeModal').on('hidden.bs.modal', function () {
        $('#volume_daterange').val('');
        if ($('#volume_daterange').data('daterangepicker')) {
            $('#volume_daterange').data('daterangepicker').remove();
        }
        $('#volume_result').text('');
        $('#volume_result_label').text('');
        $('#volume_result_card').hide();
        $('#volume_modal_client_name').text('');
        $('#product_filter').val('').trigger('change.select2');
    });

    // Volume type buttons
    $('#volume_si_btn, #volume_dr_btn, #volume_sidr_btn').on('click', function () {
        var type_map  = { 'volume_si_btn': 'si',  'volume_dr_btn': 'dr',   'volume_sidr_btn': 'sidr' };
        var label_map = { 'volume_si_btn': 'SI',  'volume_dr_btn': 'DR',   'volume_sidr_btn': 'SI and DR' };
        var color_map = { 'volume_si_btn': 'result-si', 'volume_dr_btn': 'result-dr', 'volume_sidr_btn': 'result-sidr' };
        var type  = type_map[this.id];
        var label = label_map[this.id];
        var colorClass = color_map[this.id];
        var daterange = $('#volume_daterange').val();
        var client_id = $('#volume_modal_client_id').val();
        var product_filter_id = $('#product_filter').val() || '';

        if (!daterange) {
            alert('Please select a date range first.');
            return;
        }

        var dates = daterange.split(' - ');
        var date_from = dates[0];
        var date_to = dates[1];

        showLoader();
        $.ajax({
            url: base_url + '/clients/get_client_volume',
            type: 'POST',
            data: { client_id: client_id, date_from: date_from, date_to: date_to, type: type, product_filter_id: product_filter_id },
            success: function (data) {
                if (data.status === 'success') {
                    $('#volume_result_label').text(label + ' Volume');
                    $('#volume_result').text(Number(data.total_qty).toLocaleString(undefined, { minimumFractionDigits: 0, maximumFractionDigits: 2 }));
                    $('#volume_result_card')
                        .removeClass('result-si result-sidr result-dr')
                        .addClass(colorClass)
                        .show();
                } else {
                    alert('Error: ' + data.message);
                }
                hideLoader();
            },
            error: function () {
                hideLoader();
            }
        });
    });

    // Open product filter management in a new tab
    $('#add_custom_product_filter').on('click', function () {
        window.open(base_url + '/dynamic_filter_product', '_blank');
    });
});

function get_table_clients() {
    showLoader();
    $.ajax({
        url: base_url + '/clients/get_table_clients',
        type: 'POST',
        success: function (data) {
            client_table(data);
            hideLoader();
        },
        error: function () {
            hideLoader();
        }
    });
}

function client_table(data) {
    client_table_data = $('#client_table').DataTable({
        destroy: true,
        data: data,
        columns: [
            { data: 'id', visible: false },
            { data: 'client_name' },
            { data: 'client_tin', render: function (data) { return '<span class="col-truncate" title="' + (data || '') + '">' + (data || '') + '</span>'; } },
            { data: 'client_address', render: function (data) { return '<span class="col-truncate" title="' + (data || '') + '">' + (data || '') + '</span>'; } },
            { data: 'client_business_name', render: function (data) { return '<span class="col-truncate" title="' + (data || '') + '">' + (data || '') + '</span>'; } },
            {
                data: function (data) {
                    let term = terms_set(data.client_term);
                    return term;
                }
            },
            { data: 'is_active', render: function (data) { return data === '0' ? '<span>Active</span>' : '<span>Inactive</span>'; } },
            {
                data: function (data) {
                    let editButton = '<button type="button" class="btn btn-warning mx-1 edit_client" data-id="' + data.client_id + '" ' + (data.is_active === '0' ? '' : 'style="display:none;"') + '><i class="fa fa-pencil-square-o"></i></button>';
                    let makeInactiveButton = '<button type="button" class="btn btn-danger mx-1 make_inactive" data-id="' + data.client_id + '" ' + (data.is_active === '0' ? '' : 'style="display:none;"') + '><i class="fa fa-times"></i></button>';
                    let makeActiveButton = '<button type="button" class="btn btn-success mx-1 make_active" data-id="' + data.client_id + '" ' + (data.is_active === '1' ? '' : 'style="display:none;"') + '><i class="fa fa-check"></i></button>';
                    let showVolumeButton = '<button type="button" class="btn btn-primary mx-1 show_volume" data-id="' + data.client_id + '" ' + (data.is_active === '0' ? '' : 'style="display:none;"') + '><i class="fa fa-bar-chart"></i></button>';
                    
                    return editButton + makeInactiveButton + makeActiveButton + showVolumeButton;
                },
                visible: user_role !== '4' && user_role !== '6'  // Hide actions column if user role is 4 or 6
            }
        ],
        columnDefs: [
            { targets: '_all', className: 'content_center' },
            { targets: [2], width: '10%' },
            { targets: [3], width: '18%' },
            { targets: [4], width: '14%' },
            { targets: [5], width: '7%' },
            { targets: [6], width: '8%' },
            { targets: [7], width: '18%', className: 'text-center', createdCell: function (td) { $(td).css('display', 'flex'); } }
        ],
        order: [[0, 'desc']], // Add order by id column in ascending order
        drawCallback: function () {
            initButton();
            initMakeInactiveButton(); // Initialize the inactive button
            initMakeActiveButton();   // Initialize the active button
            initShowVolumeButton();   // Initialize the show volume button
        }
    });
}

function initButton() {
    $('.edit_client').off('click');
    $('.edit_client').on('click', function () {
        var data = client_table_data.row($(this).parents('tr')).data();
        $('#edit_client_name').val(data.client_name);
        $('#edit_client_name').attr('data-id', data.id);
        $('#edit_client_name').attr('data-client-id', data.client_id);
        $('#edit_client_tin').val(data.client_tin);
        $('#edit_client_business_name').val(data.client_business_name);
        $('#edit_client_term').val(data.client_term).trigger('change');
        $('#edit_client_address').val(data.client_address);
        $('#editClientModal').modal('show');
    });
}

function initMakeInactiveButton() {
    $('.make_inactive').off('click');
    $('.make_inactive').on('click', function () {
        var client_id = $(this).data('id');
        if (confirm('Are you sure you want to make this client inactive?')) {
            showLoader();
            $.ajax({
                url: base_url + '/clients/active_inactive',
                type: 'POST',
                data: { client_id: client_id, is_active: 1 },
                success: function (data) {
                    // var data = JSON.parse(response);
                    if (data.status === 'success') {
                        alert(data.message);
                        get_table_clients();
                    } else {
                        alert('Error: ' + data.message);
                    }
                    hideLoader();
                },
                error: function () {
                    hideLoader();
                }
            });
        }
    });
}

function initMakeActiveButton() {
    $('.make_active').off('click');
    $('.make_active').on('click', function () {
        var client_id = $(this).data('id');
        if (confirm('Are you sure you want to make this client active?')) {
            showLoader();
            $.ajax({
                url: base_url + '/clients/active_inactive',
                type: 'POST',
                data: { client_id: client_id, is_active: 0 },
                success: function (data) {
                    // var data = JSON.parse(response);
                    if (data.status === 'success') {
                        alert(data.message);
                        get_table_clients();
                    } else {
                        alert('Error: ' + data.message);
                    }
                    hideLoader();
                },
                error: function () {
                    hideLoader();
                }
            });
        }
    });
}

function initShowVolumeButton() {
    $('.show_volume').off('click');
    $('.show_volume').on('click', function () {
        var rowData = client_table_data.row($(this).parents('tr')).data();
        var client_id = $(this).data('id');
        $('#volume_modal_client_id').val(client_id);
        $('#volume_modal_client_name').text(rowData ? rowData.client_name : '');
        $('#volume_result_card').hide();
        $('#volume_result').text('');
        $('#volume_result_label').text('');
        $('#volumeModal').modal('show');
    });
}

$('#save_client').click(function () {
    var client_name = $('#client_name').val();
    var client_tin = $('#client_tin').val();
    var client_business_name = $('#client_business_name').val();
    var client_term = $('#client_term').val();
    var client_address = $('#client_address').val();

    if (client_name === '' || client_term === '') {
        alert('Client name and term are required');
        return;
    }

    if (isClientNameExists(client_name)) {
        alert('Client name already exists');
        return;
    }

    showLoader();
    $.ajax({
        url: base_url + '/clients/save_client',
        type: 'POST',
        data: {
            client_name: client_name,
            client_tin: client_tin,
            client_business_name: client_business_name,
            client_term: client_term,
            client_address: client_address
        },
        success: function (data) {
            // var data = JSON.parse(response);
            if (data.status === 'success') {
                alert(data.message);
                get_table_clients();
                clear_modal_fields();
                $('#addClientModal').modal('hide');
            } else if (data.status === 'exists') {
                alert(data.message);
            } else {
                alert('Error: ' + data.message);
            }
            hideLoader();
        },
        error: function () {
            hideLoader();
        }
    });
});

$('#save_edit_client').click(function () {
    var client_name = $('#edit_client_name').val();
    var client_name_attr = $('#edit_client_name').attr('data-id');
    var client_id = $('#edit_client_name').attr('data-client-id'); // Get the client_id attribute
    var client_tin = $('#edit_client_tin').val();
    var client_business_name = $('#edit_client_business_name').val();
    var client_term = $('#edit_client_term').val();
    var client_address = $('#edit_client_address').val();

    // Correctly retrieve the original data from the DataTable
    var original_data = client_table_data.row(function (idx, data, node) {
        return data.client_id == client_id;
    }).data();

    if (!original_data) {
        console.error('Original data not found for client_id:', client_id);
        return;
    }

    if (
        client_name === original_data.client_name &&
        client_tin === original_data.client_tin &&
        client_business_name === original_data.client_business_name &&
        client_term === original_data.client_term &&
        client_address === original_data.client_address
    ) {
        $('#editClientModal').modal('hide'); // Close the modal if no changes
        return;
    }

    if (client_name === '' || client_term === '') {
        alert('Client name and term are required');
        return;
    }

    showLoader();
    $.ajax({
        url: base_url + '/clients/edit_client',
        type: 'POST',
        data: {
            client_name: client_name,
            client_name_attr: client_name_attr,
            client_id: client_id, // Include client_id in the data
            client_tin: client_tin,
            client_business_name: client_business_name,
            client_term: client_term,
            client_address: client_address
        },
        success: function (data) {
            // var data = JSON.parse(response);
            if (data.status === 'success') {
                alert(data.message);
                get_table_clients();
                $('#editClientModal').modal('hide');
            } else {
                alert('Error: ' + data.message);
            }
            hideLoader();
        },
        error: function () {
            hideLoader();
        }
    });
});

function isClientNameExists(client_name) {
    var exists = false;
    client_table_data.rows().every(function () {
        var data = this.data();
        if (data.client_name === client_name) {
            exists = true;
            return false;
        }
    });
    return exists;
}

function clear_modal_fields() {
    $('#client_name').val('');
    $('#client_tin').val('');
    $('#client_business_name').val('');
    $('#client_address').val('');
}