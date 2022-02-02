<div class="container">
  <div class="row vh-100">
    <div class="col-4 m-auto text-center">
      <main class="form-signin">
        <form autocomplete="off" id="login">
          <h1 class="h3 mb-3 fw-normal">Login</h1>
          <div class="form-floating">
            <input type="password" class="form-control" id="appPassword" name="appPassword" required>
            <label for="appPassword">Manager Password</label>
          </div>
          <br />
          <button class="w-100 btn btn-lg btn-primary" type="submit">Login</button>
        </form>
      </main>
    </div>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', () => {
    let redirecting = false;
    $('#login').submit(function(e) {
      e.preventDefault();
      if (redirecting) return;
      $.post('./api/login', $(this).serialize())
        .then(() => {
          showToast({
            class: 'success',
            title: 'Login Succedeed',
            text: 'Redirecting to dashboard...'
          });
          redirecting = true;
          setTimeout(() => {
            location.reload();
          }, 1500);
        });
    })
  });
</script>