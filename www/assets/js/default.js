function getUserTable() {
    $.ajax(URL('admin/admin-user/user-table'), {
        method: 'GET'
    }).done(function(res) {
        $('#user-table-block').html(res);
    }).fail(function(res) {
        console.log(res);
    });
}

var NewForm = {
    baseIDS: [
        '#frm-newUserForm-username',
        '#frm-newUserForm-password',
        '#frm-newUserForm-passwordVerify',
        '#frm-newUserForm-firstName',
        '#frm-newUserForm-lastName',
        '#frm-newUserForm-email',
        '#frm-newUserForm-phone'
    ],

    changeRole: function(e) {
        $('#frm-newUserForm-role').val($('#user-role').val());
    },

    clear: function() {
        $('#user-role').val('supervisor');
        $('#frm-newUserForm-role').val('supervisor');

        for (var id in NewForm.baseIDS) {
            $(NewForm.baseIDS[id]).val('');
        }
    },

    openModal: function(e) {
        NewForm.clear();
        UIkit.modal('#new-user-modal').show();
    },

    closeModal: function(e) {
        UIkit.modal('#new-user-modal').hide();
    },

    save: function($form) {
        var error = false;

        for (id in NewForm.baseIDS) {
            if (id == '#frm-newUserForm-phone' && $(NewForm.baseIDS[id]).val() == '') {
                notify('Nebyly vyplněny všechny údaje', 'danger');
                error = true;
                break;
            }
        }

        if ($('#frm-newUserForm-password').val() != $('#frm-newUserForm-passwordVerify').val()) {
            notify('Hesla se neshodují', 'danger');
            error = true;
        }

        if (!error) {
            sendForm($form, function(res) {
                if (!res.success) {
                    notify(res.msg, 'danger');
                }
                else {
                    NewForm.closeModal();
                    notify('Nový uživatel vytvořen', 'success');
                }

                getUserTable();
            });
        }
    }
};

var EditForm = {
    open: function(id) {
        $.ajax(URL('admin/admin-user/edit-data/' + id), {
            method: 'GET'
        }).done(function(res) {
            if (!res.success) {
                notify(res.msg, 'danger');
            }
            else {
                $('#frm-editUserForm-id').val(res.user.id);
                $('#frm-editUserForm-username').val(res.user.username);
                $('#frm-editUserForm-firstName').val(res.user.firstName);
                $('#frm-editUserForm-lastName').val(res.user.lastName);
                $('#frm-editUserForm-email').val(res.user.email);
                $('#frm-editUserForm-role').val(res.user.role);
                $('#edit-user-role').val(res.user.role);
                $('#frm-editUserForm-phone').val(res.user.phone != null ? res.user.phone : '');

                UIkit.modal('#edit-user-modal').show();
            }
        }).fail(function(res) {
            notify('Nastala chyba', 'danger');
        });
    },

    changeRole: function(e) {
        $('#frm-editUserForm-role').val($('#edit-user-role').val());
    },

    switchPasswordType: function($check) {
        var value = $check.is(':checked') ? 1 : 0;
        $('#frm-editUserForm-originalPassword').val(value);

        if (value == 1) {
            $('.password-row').addClass('display-none');
        }
        else {
            $('.password-row').removeClass('display-none');
        }
    },

    save: function($form) {
        var error = false;

        if ($('#frm-editUserForm-originalPassword').val() == 0) {
            if ($('#frm-editUserForm-password').val() != $('#frm-editUserForm-passwordVerify').val()) {
                notify('Hesla se neshodují', 'danger');
                error = true;
            }
        }

        if (!error) {
            sendForm($form, function(res) {
                if (!res.success) {
                    notify(res.msg, 'danger');
                }
                else {
                    UIkit.modal('#edit-user-modal').hide();
                    notify('Změny uloženy', 'success');
                }

                getUserTable();
            });
        }
    }
};

function deleteUser(id) {
    modalConfirm('Opravdu chcete smazat tohoto uživatele?', function() {
        
        $.ajax(URL('admin/admin-user/delete/' + id), {
            method: 'GET'
        }).done(function(res) {
            getUserTable();

            if (res.success) {
                notify('Uživatel smazán', 'success');
            }
        }).fail(function(res) {
            getUserTable();
            notify('Nastala chyba', 'danger');
        })
    })
}

$(function() {

    getUserTable();
    NewForm.changeRole();
    EditForm.changeRole();

    $(document).on('click', '#open-new-user-modal', NewForm.openModal);
    $(document).on('change', '#user-role', NewForm.changeRole);
    $(document).on('submit', '#frm-newUserForm', function(e) {e.preventDefault(); NewForm.save($(this)); });

    $(document).on('click', '.edit-user-button', function(e) { var id = $(this).attr('data-user-id'); EditForm.open(id); });
    $(document).on('change', '#edit-user-role', EditForm.changeRole);
    $(document).on('change', '#original-password', function(e) { EditForm.switchPasswordType($(this)); });
    $(document).on('submit', '#frm-editUserForm', function(e) { e.preventDefault(); EditForm.save($(this)); });

    $(document).on('click', '.delete-user-button', function() { var id = $(this).attr('data-user-id'); deleteUser(id); });
});
