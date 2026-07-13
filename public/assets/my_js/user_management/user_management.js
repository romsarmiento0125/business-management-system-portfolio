var base_url = $('#base_url').val();
var user_table_data;
var user_role;

function showLoader() {
    $('#loader').show();
}

function hideLoader() {
    $('#loader').hide();
}

$(document).ready(function () {
    get_user_role();
    get_table_user();
});

function get_user_role() {
    showLoader();
    $.ajax({
        url: base_url + '/user/get_user_role',
        type: 'POST',
        beforeSend: function () {
            $('#loader').show();
        },
        success: function (response) {
            var data = JSON.parse(response);
            user_role = data;

            // Populate the #user_role select element
            var roleSelect = $('#user_role');
            roleSelect.empty(); // Clear existing options
            user_role.forEach(function (role) {
                roleSelect.append('<option value="' + role.id + '">' + role.user_role + '</option>');
            });

            var editRoleSelect = $('#edit_user_role');
            editRoleSelect.empty(); // Clear existing options
            user_role.forEach(function (role) {
                editRoleSelect.append('<option value="' + role.id + '">' + role.user_role + '</option>');
            });

            hideLoader();
        },
        error: function () {
            hideLoader();
        }
    });
}

function get_table_user() {
    showLoader();
    $.ajax({
        url: base_url + '/user/get_table_user',
        type: 'POST',
        beforeSend: function () {
            $('#loader').show();
        },
        success: function (response) {
            var data = JSON.parse(response);
            user_table(data);
            hideLoader();
        },
        error: function () {
            hideLoader();
        }
    });
}

function user_table(data) {
    user_table_data = $('#user_table').DataTable({
        destroy: true,
        data: data,
        columns: [
            { data: 'id', visible: false },
            { 
                data: 'username',
                render: function (data) {
                    return data.toUpperCase(); // Format username to uppercase
                }
            },
            { data: 'user_role' },
            { 
                data: 'archive',
                render: function (data) {
                    return data === 0 || data === '0' ? 'Active' : 'Inactive'; // Ensure both number and string types are handled
                }
            },
            {
                data: function (data) {
                    var editButton = '<button type="button" class="btn btn-warning edit_user mx-1"><i class="fa fa-pencil"></i></button>';
                    var archiveButton = data.archive === 0 || data.archive === '0' ? '<button type="button" class="btn btn-danger archive_user mx-1"><i class="fa fa-archive"></i></button>' : '';
                    var activateButton = data.archive === 1 || data.archive === '1' ? '<button type="button" class="btn btn-success activate_user mx-1"><i class="fa fa-check"></i></button>' : '';
                    return editButton + archiveButton + activateButton;
                }
            }
        ],
        columnDefs: [
            { targets: '_all', className: 'content_center' },
            { targets: [4], className: 'text-center' }
        ],
        drawCallback: function () {
            initButton();
        }
    });
}

function initButton() {
    $('.edit_user').off('click');
    $('.edit_user').on('click', function () {
        var data = user_table_data.row($(this).parents('tr')).data();
        $('#edit_user_username').text(data.username).attr('data-id', data.id);
        $('#edit_user_firstname').val(data.first_name);
        $('#edit_user_lastname').val(data.last_name);
        $('#edit_user_role').val(data.role_id).change(); // Ensure the value is set and triggers any change event
        $('#editUsertModal').modal('show');
    });

    $('.archive_user').off('click');
    $('.archive_user').on('click', function () {
        var data = user_table_data.row($(this).parents('tr')).data();
        if (confirm('Are you sure you want to archive this account?')) {
            showLoader();
            $.ajax({
                url: base_url + '/user/archive_user',
                type: 'POST',
                data: JSON.stringify({ id: data.id }),
                success: function (response) {
                    var result = JSON.parse(response);
                    if (result.status === 'success') {
                        alert(result.message);
                        get_table_user();
                    } else {
                        alert('Error: ' + result.message);
                    }
                    hideLoader();
                },
                error: function () {
                    alert('An error occurred while archiving the account.');
                    hideLoader();
                }
            });
        }
    });

    $('.activate_user').off('click');
    $('.activate_user').on('click', function () {
        var data = user_table_data.row($(this).parents('tr')).data();
        if (confirm('Are you sure you want to activate this account?')) {
            showLoader();
            $.ajax({
                url: base_url + '/user/activate_user',
                type: 'POST',
                data: JSON.stringify({ id: data.id }),
                success: function (response) {
                    var result = JSON.parse(response);
                    if (result.status === 'success') {
                        alert(result.message);
                        get_table_user();
                    } else {
                        alert('Error: ' + result.message);
                    }
                    hideLoader();
                },
                error: function () {
                    alert('An error occurred while activating the account.');
                    hideLoader();
                }
            });
        }
    });
}

$('#save_user').click(function () {
    var user_data = {
        username: $('#user_username').val(),
        firstname: $('#user_firstname').val(),
        lastname: $('#user_lastname').val(),
        password: $('#user_password').val(),
        role: $('#user_role').val()
    };

    if (user_data.username === '' || user_data.firstname === '' || user_data.lastname === '' || user_data.password === '' || user_data.role === '') {
        alert('All fields are required');
        return;
    }

    if (isUserNameExists(user_data.username)) {
        alert('Username already exists');
        return;
    }

    $.ajax({  
        url: base_url + '/user/save_user',
        type: 'POST',
        data: JSON.stringify(user_data),
        success: function (response) {
            var data = JSON.parse(response);
            if (data.status === 'success') {
                alert(data.message);
                get_table_user();
                clear_modal_fields();
                $('#addUserModal').modal('hide');
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

$('#save_edit_user').click(function () {
    var user_data = {
        id: $('#edit_user_username').attr('data-id'), // Get the user ID
        firstname: $('#edit_user_firstname').val(),
        lastname: $('#edit_user_lastname').val(),
        password: $('#edit_user_password').val(), // Optional password
        role: $('#edit_user_role').val()
    };

    if (user_data.firstname === '' || user_data.lastname === '' || user_data.role === '') {
        alert('All fields except password are required');
        return;
    }

    showLoader();
    $.ajax({
        url: base_url + '/user/edit_user',
        type: 'POST',
        data: JSON.stringify(user_data),
        success: function (response) {
            var data = JSON.parse(response);
            if (data.status === 'success') {
                alert(data.message);
                get_table_user();
                $('#editUsertModal').modal('hide');
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

function isUserNameExists(user_name) {
    var exists = false;
    user_table_data.rows().every(function () {
        var data = this.data();
        if (data.username === user_name) {
            exists = true;
            return false;
        }
    });
    return exists;
}

function clear_modal_fields() {
    $('#user_username').val('');
    $('#user_firstname').val('');
    $('#user_lastname').val('');
    $('#user_password').val('');
}