var base_url = $('#base_url').val();
var selectedClients = [];
var editSelectedClients = [];
var client_list_table;
var edit_client_list_table;
var filter_name_table;
var clients_data = [];

function showLoader() {
    $('#loader').show();
}

function hideLoader() {
    $('#loader').hide();
}

$(document).ready(function () {
    showLoader();
    // Set initial state of Save Filter button
    update_save_filter_button();
    $.ajax({
        url: base_url + '/dynamic_filter_client/get_clients',
        type: 'GET',
        contentType: 'application/json',
        success: function (response) {
            let data = JSON.parse(response);
            clients_data = data.clients;
            populate_select(clients_data);
            populate_edit_select(clients_data);
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

    get_client_filters();

    // When a client is chosen, add it to the array, then reset select to blank
    $('#clients_details').on('change', function () {
        const $select = $(this);
        const id = $select.val();

        // Skip if blank/invalid selection
        if (!id) return;

        const $opt = $select.find('option:selected');
        const name = $opt.text();
        const address = $opt.data('address') || '';

        // Prevent duplicate clients by id (normalize to string for safety)
        const isDuplicate = selectedClients.some(c => String(c.id) === String(id));
        if (isDuplicate) {
            alert('Client already in the list.');
            $select.val('');
            update_save_filter_button();
            return;
        }

        const selectedItem = { id: id, name: name, address: address };

        // Push into the array (duplicate handling can be added later if needed)
        selectedClients.push(selectedItem);

        // Reset select to blank
        $select.val('');

        populate_table();
        update_save_filter_button();
    });

    // Save filter: validate and send via AJAX
    $('#save_filter').on('click', function () {
        const filterName = ($('#filter_name').val() || '').trim();

        if (!filterName) {
            alert('Please enter a filter name.');
            return;
        }
        if (selectedClients.length === 0) {
            alert('Please add at least one client.');
            return;
        }

        const payload = {
            filter_name: filterName,
            clients: selectedClients
        };

        const $btn = $(this);
        $btn.prop('disabled', true);

        showLoader();
        $.ajax({
            url: base_url + '/dynamic_filter_client/save_filter',
            type: 'POST',
            data: JSON.stringify(payload),
            contentType: 'application/json',
            success: function (res) {
                let data = {};
                try { data = typeof res === 'string' ? JSON.parse(res) : res; } catch (e) {}
                alert(data?.message || 'Filter saved successfully.');
                
                // Reset state
                selectedClients = [];
                // Clear both add and edit inputs in case either was used
                $('#dy_add_filter_box #filter_name, #dy_edit_filter_box #edit_filter_name').val('');
                populate_table();
                update_save_filter_button();
                get_client_filters();
                // Return to add mode after save
                enterAddMode();
            },
            error: function (xhr) {
                let message = 'Failed to save filter. Please contact the system admin.';
                try {
                    const err = JSON.parse(xhr.responseText);
                    if (err?.error) message = err.error;
                } catch (e) {}
                alert(message);
            },
            complete: function () {
                hideLoader();
                $btn.prop('disabled', false);
            }
        });
    });

    // Cancel edit: clear edit box and switch back to add box
    $('#cancel_edit').off('click').on('click', function () {
        // Clear edit state data
        editSelectedClients = [];
        $('#edit_filter_name').val('').removeAttr('data-filter-id');
        $('#edit_clients_details').val('');

        // Re-render (empties the edit table)
        edit_populate_table();

        // Switch back to add mode
        enterAddMode();

        // Ensure save button reflects current add-mode selection
        update_save_filter_button();
    });

    // When a client is chosen in EDIT select, add to editSelectedClients and reset select
    $('#edit_clients_details').off('change').on('change', function () {
        const $select = $(this);
        const id = $select.val();
        if (!id) return;

        const $opt = $select.find('option:selected');
        const name = $opt.text();
        const address = $opt.data('address') || '';

        // Prevent duplicates based on id
        const isDuplicate = Array.isArray(editSelectedClients) && editSelectedClients.some(c => String(c.id) === String(id));
        if (isDuplicate) {
            alert('Client already in the list.');
            $select.val('');
            return;
        }

        // Add and reset
        editSelectedClients.push({ id: id, name: name, address: address });
        $select.val('');

        // Re-render edit table
        edit_populate_table();
    });

    // Edit filter: validate and send update via AJAX
    $('#edit_filter').off('click').on('click', function () {
        const $btn = $(this);
        const filterId = $('#edit_filter_name').attr('data-filter-id');
        const filterName = ($('#edit_filter_name').val() || '').trim();

        if (!filterId) {
            alert('Missing filter ID. Please select a filter to edit from the right table.');
            return;
        }
        if (!filterName) {
            alert('Please enter a filter name.');
            return;
        }
        if (!Array.isArray(editSelectedClients) || editSelectedClients.length === 0) {
            alert('Please add at least one client to the filter.');
            return;
        }

        const payload = {
            client_filter_id: filterId,
            filter_name: filterName,
            clients: editSelectedClients
        };

        $btn.prop('disabled', true);
        showLoader();
        $.ajax({
            url: base_url + '/dynamic_filter_client/edit_client_filter',
            type: 'POST',
            data: JSON.stringify(payload),
            contentType: 'application/json',
            success: function (res) {
                let data = {};
                try { data = typeof res === 'string' ? JSON.parse(res) : res; } catch (e) {}

                // If backend returns a message, show it; otherwise use a generic success
                alert(data?.message || 'Filter updated successfully.');

                // Refresh list on the right
                get_client_filters();

                // Clear edit state and switch back to add mode
                editSelectedClients = [];
                $('#edit_filter_name').val('').removeAttr('data-filter-id');
                $('#edit_clients_details').val('');
                edit_populate_table();
                enterAddMode();
            },
            error: function (xhr) {
                let message = 'Failed to update filter. Please contact the system admin.';
                try {
                    const err = JSON.parse(xhr.responseText);
                    if (err?.error) message = err.error;
                } catch (e) {}
                alert(message);
            },
            complete: function () {
                hideLoader();
                $btn.prop('disabled', false);
            }
        });
    });
});

function populate_select(items) {
    let select = $('#clients_details'); // Replace with your actual select element ID
    select.empty();
    select.append($('<option></option>').attr('value', '').text('')); // Add blank option
    items.forEach(function (item) {
        let option = $('<option></option>')
            .attr('value', item.client_id)
            .attr('data-address', item.client_address || '')
            .text(item.client_name);
        select.append(option);
    });
}

function populate_edit_select(items) {
    let select = $('#edit_clients_details'); // Replace with your actual select element ID
    select.empty();
    select.append($('<option></option>').attr('value', '').text('')); // Add blank option
    items.forEach(function (item) {
        let option = $('<option></option>')
            .attr('value', item.client_id)
            .attr('data-address', item.client_address || '')
            .text(item.client_name);
        select.append(option);
    });
}

function populate_table() {
    client_list_table = $('#client_filter_list_table').DataTable({
        destroy: true,
        data: selectedClients,
        order: [0, 'desc'],
        columns: [
            { data: 'id', visible: false },
            { data: 'name' },
            { data: 'address' },
            {   
                data: function (data) {
                    let remove_button = '<button type="button" class="btn btn-danger mx-1 remove_si_btn"><i class="fa fa-trash"></i></button>';
                    
                    return remove_button;
                }
            }
        ],
        columnDefs: [
            { targets: '_all', className: 'content_center' },
        ],
        drawCallback: function () {
            // Left table: remove selected client
            $('.remove_si_btn').off('click').on('click', function () {
                let row = $(this).closest('tr');
                let rowData = client_list_table.row(row).data();
                let clientIdToRemove = rowData.id;
                selectedClients = selectedClients.filter(function (client) { return String(client.id) !== String(clientIdToRemove); });
                populate_table();
                update_save_filter_button();
            });
        }
    });

    // Ensure the button visibility reflects current data
    update_save_filter_button();
}

function edit_populate_table() {
    edit_client_list_table = $('#edit_client_filter_list_table').DataTable({
        destroy: true,
        data: editSelectedClients,
        order: [0, 'desc'],
        columns: [
            { data: 'id', visible: false },
            { data: 'name' },
            { data: 'address' },
            {   
                data: function (data) {
                    let remove_button = '<button type="button" class="btn btn-danger mx-1 edit_remove_client_btn"><i class="fa fa-trash"></i></button>';
                    
                    return remove_button;
                }
            }
        ],
        columnDefs: [
            { targets: '_all', className: 'content_center' },
        ],
        drawCallback: function () {
             // Remove client from selectedClients array and update table
            $('.edit_remove_client_btn').off('click').on('click', function () {
                let row = $(this).closest('tr');
                let rowData = edit_client_list_table.row(row).data();
                let clientIdToRemove = rowData.id;
                editSelectedClients = editSelectedClients.filter(client => String(client.id) !== String(clientIdToRemove));
                edit_populate_table();
                update_save_filter_button();
            });
        }
    });

    // Ensure the button visibility reflects current data
    update_save_filter_button();
}

// Hide/show Save Filter button based on selections
function update_save_filter_button() {
    if (selectedClients.length === 0) {
        $('#save_filter').hide();
    } else {
        $('#save_filter').show();
    }
}

function get_client_filters() {
    showLoader();
    $.ajax({
        url: base_url + '/dynamic_filter_client/get_client_filters',
        type: 'POST',
        contentType: 'application/json',
        success: function (res) {
            let data = {};
            try { data = typeof res === 'string' ? JSON.parse(res) : res; } catch (e) {}
            populate_dynamic_filter_client_table((data && data.filters) ? data.filters : []);
        },
        error: function (xhr) {
            let message = 'Failed to retrieve client filters. Please contact the system admin.';
            try {
                const err = JSON.parse(xhr.responseText);
                if (err?.error) message = err.error;
            } catch (e) {}
            alert(message);
        },
        complete: function () {
            hideLoader();
        }
    });
}

function populate_dynamic_filter_client_table(data) {
    filter_name_table = $('#filter_name_table').DataTable({
        destroy: true,
        data: data,
        order: [0, 'desc'],
        columns: [
            { data: 'id', visible: false },
            { data: 'filter_name' },
            {   
                data: function () {
                    let view_button = '<button type="button" class="btn btn-success mx-1 view_client_filter_btn" title="View"><i class="fa fa-eye"></i></button>';
                    let edit_button = '<button type="button" class="btn btn-warning mx-1 edit_client_filter_btn" title="Edit"><i class="fa fa-edit"></i></button>';
                    let delete_button = '<button type="button" class="btn btn-danger mx-1 delete_client_filter_btn" title="Delete"><i class="fa fa-trash"></i></button>';
                    return view_button + edit_button + delete_button;
                }
            }
        ],
        columnDefs: [
            { targets: '_all', className: 'content_center' }
        ],
        drawCallback: function () {
            initClientFilterButtons();
        }
    });
}

function initClientFilterButtons() {
    // View
    $('.view_client_filter_btn').off('click').on('click', function () {
        const rowData = filter_name_table.row($(this).parents('tr')).data();
        $.ajax({
            url: base_url + '/dynamic_filter_client/view_client_filter',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({ client_filter_id: rowData.id }),
            success: function (response) {
                let response_data = null;
                try {
                    response_data = (typeof response === 'string') ? JSON.parse(response) : response;
                } catch (e) {
                    alert('Invalid server response.');
                    return;
                }
                processViewClientFilterSuccess(response_data);
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

    // Edit: populate left form
    $('.edit_client_filter_btn').off('click').on('click', function () {
        const rowData = filter_name_table.row($(this).parents('tr')).data();
        if (!rowData || !rowData.id) {
            alert('Invalid selection.');
            return;
        }
        showLoader();
        $.ajax({
            url: base_url + '/dynamic_filter_client/view_client_filter',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({ client_filter_id: rowData.id }),
            success: function (response) {
                let response_data = null;
                try {
                    response_data = (typeof response === 'string') ? JSON.parse(response) : response;
                } catch (e) {
                    alert('Invalid server response.');
                    return;
                }
                processEditClientFilterSuccess(response_data, rowData);
            },
            error: function (xhr) {
                if (xhr.status === 500) {
                    let response = JSON.parse(xhr.responseText);
                    alert(response.error);
                } else {
                    alert('Call a system admin');
                }
            },
            complete: function () {
                hideLoader();
            }
        });
    });

    // Delete (stub)
    $('.delete_client_filter_btn').off('click').on('click', function () {
        const rowData = filter_name_table.row($(this).parents('tr')).data();
        if (!rowData || !rowData.id) {
            alert('Invalid selection.');
            return;
        }
        const name = rowData.filter_name || '';
        if (!confirm('Are you sure you want to delete filter: ' + name + '?')) {
            return;
        }

        showLoader();
        console.log(rowData);
        
        $.ajax({
            url: base_url + '/dynamic_filter_client/delete_client_filter',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({ client_filter_id: rowData.id }),
            success: function (res) {
                let data = {};
                try { data = typeof res === 'string' ? JSON.parse(res) : res; } catch (e) {}
                alert(data?.message || 'Filter deleted successfully.');
                // Refresh the filters list
                get_client_filters();

                // If currently editing this same filter, clear edit state and go back to add mode
                const currentEditId = $('#edit_filter_name').attr('data-filter-id');
                if (currentEditId && String(currentEditId) === String(rowData.id)) {
                    editSelectedClients = [];
                    $('#edit_filter_name').val('').removeAttr('data-filter-id');
                    $('#edit_clients_details').val('');
                    edit_populate_table();
                    enterAddMode();
                }
            },
            error: function (xhr) {
                let message = 'Failed to delete filter. Please contact the system admin.';
                try {
                    const err = JSON.parse(xhr.responseText);
                    if (err?.error) message = err.error;
                } catch (e) {}
                alert(message);
            },
            complete: function () {
                hideLoader();
            }
        });
    });
}

// Handle and validate the response for viewing a client filter
function processViewClientFilterSuccess(response_data) {
    // Validate presence of clients array with at least one item
    if (!response_data || !Array.isArray(response_data.clients) || response_data.clients.length === 0) {
        alert('No client filter data found.');
        return;
    }

    // Set the filter name from the first client's filter_name
    $('#client_filter_name').text(response_data.clients[0].filter_name || '');

    // Populate the client names
    const container = $('#client_filter_items_container');
    container.empty();
    response_data.clients.forEach(function(item) {
        const name = item.client_name || item.name || '';
        container.append('<p>- ' + name + '</p>');
    });

    // Show the modal only after successful validation and rendering
    $('#dynamic_filter_view_modal').modal('show');
}

// Handle and validate the response for editing a client filter
function processEditClientFilterSuccess(response_data, rowData) {
    // Validate presence of clients array with at least one item
    if (!response_data || !Array.isArray(response_data.clients) || response_data.clients.length === 0) {
        alert('No client filter data found for editing.');
        return;
    }

    // Set the filter name from the first client's filter_name (fallback to row's name)
    const filterName = rowData.filter_name || '';
    // Set the filter name in the edit box
    $('#edit_filter_name').val(filterName);
    // Attach the filter id as an attribute for reference during editing
    const editFilterId = (rowData && rowData.id) || (response_data.clients[0] && response_data.clients[0].filter_id) || response_data.filter_id || '';
    if (editFilterId) {
        $('#edit_filter_name').attr('data-filter-id', editFilterId);
    } else {
        $('#edit_filter_name').removeAttr('data-filter-id');
    }

    // Show edit box and hide add box
    enterEditMode();

    // Map clients into the left table's shape
    editSelectedClients = response_data.clients.map(function (item) {
        return {
            id: item.client_id || '',
            name: item.client_name || '',
            address: item.client_address || ''
        };
    });
   
    // Re-render left table with selected clients
    edit_populate_table();
}

// Switch UI to Edit mode: hide add box, show edit box
function enterEditMode() {
    $('#dy_add_filter_box').hide();
    $('#dy_edit_filter_box').show();
}

// Switch UI to Add mode: show add box, hide edit box and enable input
function enterAddMode() {
    $('#dy_edit_filter_box').hide();
    $('#dy_add_filter_box').show();
}
