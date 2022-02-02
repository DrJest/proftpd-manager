<div class="container">
  <div class="row">
    <div class="col-md-12">
      <br />
      <br />
      <br />
      <h2>Users</h2>
      <table class="table table-striped" id="users">
        <thead>
          <tr>
          </tr>
        </thead>
        <tbody>
          <tr class="no-results text-center">
            <td colspan="10">No Users</td>
          </tr>
        </tbody>
      </table>
      <nav aria-label="Users navigation">
        <ul class="pagination justify-content-center">
          <li class="page-item disabled">
            <a class="page-link" href="#" tabindex="-1">Previous</a>
          </li>
          <?php for ($i = 1; $i <= $users; ++$i) { ?>
            <li class="page-item <?php if ($users == 1) echo 'disabled'; ?>" data-page="<?= $i; ?>">
              <a class="page-link" href="#">
                <?= $i; ?>
              </a>
            </li>
          <?php } ?>
          <li class="page-item <?php if ($users == 1) echo 'disabled'; ?>">
            <a class="page-link" href="#">Next</a>
          </li>
        </ul>
      </nav>
    </div>
  </div>
</div>
<div class="modal fade" id="userModal" tabindex="-1" aria-labelledby="userModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="userModalLabel">Add / Edit User</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form class="form-floating" autocomplete="off"></form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary save">Save changes</button>
      </div>
    </div>
  </div>
</div>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    let page = 1,
      modal,
      config = {
        passwdField: '<?php echo $config['usersTablePasswdField']; ?>',
        idField: '<?php echo $config['usersTableIdField']; ?>',
        editableFields: '<?php echo $config['usersTableEditableFields']; ?>'
      };

    const loadUsers = p => {
      $.get('./api/users/')
        .then(r => {
          let h = $('#users > thead > tr').html('');
          let b = $('#users > tbody');
          b.find('tr:not(.no-results)').remove();
          if (!r.data || !r.data.length) return;
          for (let i in r.data[0]) {
            if (i !== config.passwdField)
              h.append(`<th>${i}</th>`);
          }
          h.append('<th> <button class="btn btn-xs btn-primary add" title="Add User"> <i class="fa-solid fa-user-plus"></i> </button> </th>');
          for (let l of r.data) {
            let tr = $('<tr>').data('user', l);
            for (let i in l) {
              if (i !== config.passwdField)
                tr.append(`<td>${l[i]}</td>`);
            }
            tr.append(`
              <td>
                <button class="btn btn-xs btn-primary edit" title="Edit User"> <i class="fa-solid fa-pen-to-square"></i> </button>
                <button class="btn btn-xs btn-danger delete" title="Delete User"> <i class="fa-solid fa-trash"></i> </button>
              </td>
            `)
            tr.appendTo(b);
          }
        })
    };

    const editUser = u => {
      let form = $('#userModal form').html('').data('id', u[config.idField] || '');
      for (let i of config.editableFields.split(',')) {
        let field = `
            <div class="form-floating">
              <input type="text" class="form-control" id="${i}" name="${i}" value="${u[i] || ''}" required>
              <label for="${i}">${i}</label>
            </div>
          `;
        form.append(field)
      }
      form.append(`<br />
          <p>To set / change the password please compile below.</p>
          <div class="form-floating">
            <input type="text" class="form-control password" id="passwd" name="passwd" required>
            <label for="passwd">Password</label>
          </div>
          <div class="form-floating">
            <input type="text" class="form-control password" id="passwd_confirm" name="passwd_confirm" required>
            <label for="passwd_confirm">Confirm Password</label>
          </div>`);
      modal = new bootstrap.Modal(document.getElementById('userModal'), {
        keyboard: false
      });
      modal.show();
    };

    $('#users')
      .on('click', '.edit', function(e) {
        e.preventDefault();
        let u = $(this).parents('tr').eq(0).data('user');
        editUser(u);
      })
      .on('click', '.delete', function(e) {
        e.preventDefault();
        if (!confirm('This cannot be undone, are you sure?'))
          return;
        let u = $(this).parents('tr').eq(0).data('user');
        $.ajax({
          method: 'DELETE',
          url: './api/users/' + u.id,
          success: r => {
            if (r.status === 'OK') {
              $(this).parents('tr').eq(0).remove();
              showToast({
                class: 'success',
                title: 'Success',
                text: 'User deleted'
              })
            }
          }
        })
      })
      .on('click', '.add', function(e) {
        editUser({});
      })

    $('#userModal')
      .on('submit', 'form', function(e) {
        e.preventDefault();
        let data = $(this).serialize();
        if ($(this).find('[name="passwd"]').val() !== $(this).find('[name="passwd_confirm"]').val()) {
          showToast({
            class: 'error',
            title: 'Error',
            text: 'Passwords mismatch'
          })
          return;
        }
        $.post('./api/users/' + $(this).data('id'), data)
          .then(r => {
            if (r.status === 'OK') {
              showToast({
                class: 'success',
                title: 'Success',
                text: 'User Saved'
              });
              loadUsers(page);
            }
          });
      })
      .on('click', '.save', function(e) {
        $('#userModal form').submit();
      })

    loadUsers(page);
  });
</script>