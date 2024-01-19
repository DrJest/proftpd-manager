<div class="container">
  <form autocomplete="off" id="install" class="row">
    <div class="col-12">
      <h1 class="h3 mb-3">Installation</h1>
    </div>
    <div class="col-4 text-center">
      <h3>Database</h3>
      <br />
      <div class="form-floating">
        <input type="text" class="form-control" id="databaseHost" name="databaseHost" value="localhost">
        <label for="databaseHost">Database Host</label>
      </div>
      <div class="form-floating">
        <input type="text" class="form-control" id="databasePort" name="databasePort" value="3306">
        <label for="databasePort">Database Port</label>
      </div>
      <div class="form-floating">
        <input type="text" class="form-control" id="databaseUser" name="databaseUser" value="proftpd">
        <label for="databaseUser">Database User</label>
      </div>
      <div class="form-floating">
        <input type="text" class="form-control password" id="databasePass" name="databasePass">
        <label for="databasePass">Database Password</label>
      </div>
      <div class="form-floating">
        <input type="text" class="form-control" id="databaseName" name="databaseName" value="proftpd">
        <label for="databaseName">Database Name</label>
      </div>
    </div>
    <div class="col-4 text-center">
      <h3>ProFTPd Mysql Settings</h3>
      <br />
      <div class="form-floating">
        <input type="text" class="form-control" id="groupsTable" name="groupsTable" value="ftpgroups">
        <label for="groupsTable">Groups Table Name</label>
      </div>
      <div class="form-floating">
        <input type="text" class="form-control" id="groupsTableEditableFields" name="groupsTableEditableFields" value="groupname,gid,members">
        <label for="groupsTableEditableFields">Groups Table Editable Fields (comma separated)</label>
      </div>
      <br />
      <div class="form-floating">
        <input type="text" class="form-control" id="usersTable" name="usersTable" value="ftpusers">
        <label for="usersTable">Users Table Name</label>
      </div>
      <div class="form-floating">
        <input type="text" class="form-control" id="usersTableEditableFields" name="usersTableEditableFields" value="userid,uid,gid,homedir,shell">
        <label for="usersTableEditableFields">Users Table Editable Fields (comma separated)</label>
      </div>
      <div class="form-floating">
        <input type="text" class="form-control" id="usersTableIdField" name="usersTableIdField" value="id">
        <label for="usersTableIdField">Users Table ID Field</label>
      </div>
      <div class="form-floating">
        <input type="text" class="form-control" id="usersTablePasswdField" name="usersTablePasswdField" value="passwd">
        <label for="usersTablePasswdField">Users Table Password Field</label>
      </div>
      <div class="form-floating">
        <input type="text" class="form-control" id="usersTableModifiedField" name="usersTableModifiedField" value="modified">
        <label for="usersTableModifiedField">Users Table Modified Field</label>
      </div>
    </div>
    <div class="col-4 text-center">
      <h3>Manager Settings</h3>
      <br />
      <div class="form-floating">
        <input type="text" class="form-control password" id="appPassword" name="appPassword">
        <label for="appPassword">Manager Password</label>
      </div>
      <div class="form-floating">
        <input type="text" class="form-control password" id="appPasswordConfirm" name="appPasswordConfirm">
        <label for="appPasswordConfirm">Manager Password (Confirm)</label>
      </div>
    </div>
    <div class="col-4 m-auto">
      <br />
      <br />
      <button class="w-100 btn btn-lg btn-primary" type="submit">Install Now</button>
    </div>
  </form>
</div>

<script>
  document.addEventListener('DOMContentLoaded', () => {
    let redirecting = false;
    $('#install').submit(function(e) {
      e.preventDefault();
      if (redirecting) return;
      if (!/^\d+$/.test($(this).find('[name="databasePort"]').val())) {
        return showToast({
          class: 'error',
          title: 'Error',
          text: 'Invalid Port!'
        });
      }
      $.post('./api/install', $(this).serialize())
        .then(() => {
          showToast({
            class: 'success',
            title: 'Installation Succedeed',
            text: 'Redirecting to login...'
          })
          redirecting = true;
          setTimeout(() => {
            location.reload();
          }, 1500);
        });
    })
  });
</script>