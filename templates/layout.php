<!doctype html>
<html lang="en">

<head>
  <!-- Required meta tags -->
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" integrity="sha512-Fo3rlrZj/k7ujTnHg4CGR2D7kSs0v4LLanw2qksYuRlEzO+tcaEPQogQ0KaoGN26/zrn20ImR1DfuLWnOo7aBA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <style>
    .no-results:not(:first-child:last-child) {
      display: none;
    }

    tr {
      vertical-align: middle;
    }

    #loader {
      display: none;
    }

    #loader>div {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      z-index: 9999;
      display: flex;
      align-items: center;
      text-align: center;
      background: rgba(0, 0, 0, 0.5);
    }

    .lds-roller {
      display: inline-block;
      position: relative;
      width: 80px;
      height: 80px;
      margin: auto;
    }

    .lds-roller div {
      animation: lds-roller 1.2s cubic-bezier(0.5, 0, 0.5, 1) infinite;
      transform-origin: 40px 40px;
    }

    .lds-roller div:after {
      content: " ";
      display: block;
      position: absolute;
      width: 7px;
      height: 7px;
      border-radius: 50%;
      background: #fff;
      margin: -4px 0 0 -4px;
    }

    .lds-roller div:nth-child(1) {
      animation-delay: -0.036s;
    }

    .lds-roller div:nth-child(1):after {
      top: 63px;
      left: 63px;
    }

    .lds-roller div:nth-child(2) {
      animation-delay: -0.072s;
    }

    .lds-roller div:nth-child(2):after {
      top: 68px;
      left: 56px;
    }

    .lds-roller div:nth-child(3) {
      animation-delay: -0.108s;
    }

    .lds-roller div:nth-child(3):after {
      top: 71px;
      left: 48px;
    }

    .lds-roller div:nth-child(4) {
      animation-delay: -0.144s;
    }

    .lds-roller div:nth-child(4):after {
      top: 72px;
      left: 40px;
    }

    .lds-roller div:nth-child(5) {
      animation-delay: -0.18s;
    }

    .lds-roller div:nth-child(5):after {
      top: 71px;
      left: 32px;
    }

    .lds-roller div:nth-child(6) {
      animation-delay: -0.216s;
    }

    .lds-roller div:nth-child(6):after {
      top: 68px;
      left: 24px;
    }

    .lds-roller div:nth-child(7) {
      animation-delay: -0.252s;
    }

    .lds-roller div:nth-child(7):after {
      top: 63px;
      left: 17px;
    }

    .lds-roller div:nth-child(8) {
      animation-delay: -0.288s;
    }

    .lds-roller div:nth-child(8):after {
      top: 56px;
      left: 12px;
    }

    @keyframes lds-roller {
      0% {
        transform: rotate(0deg);
      }

      100% {
        transform: rotate(360deg);
      }
    }

    @font-face {
      font-family: 'password';
      font-style: normal;
      font-weight: 400;
      src: url("assets/fonts/password.ttf");
    }

    input.password:not(:placeholder-shown):not(.shown) {
      font-family: 'password';
    }

    input.password:not(.shown)+.input-group-append .fa-eye-slash {
      display: none;
    }

    input.password.shown+.input-group-append .fa-eye {
      display: none;
    }

    #toasts {
      position: fixed;
      right: 10px;
      bottom: 10px;
      width: 300px;
      z-index: 999999;
    }

    .form-floating {
      margin-bottom: 6px;
    }
  </style>
  <title>ProFTPd Manager</title>
</head>

<body>
  <nav class="navbar navbar-light bg-light mb-4">
    <div class="container-fluid">
      <a class="navbar-brand" href="#">ProFTPd Manager</a>
      <?php if ($logged_in) { ?>
        <a class="btn btn-outline-success" href="./logout">Logout</a>
        <?php } ?>
    </div>
  </nav>
  <?= $content ?>

  <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
    <?php if (isset($toast)) { ?>
      <div id="liveToast" class="toast showing" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header">
          <strong class="me-auto"><?= $toast->title; ?></strong>
          <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body">
          <?= $toast->message; ?>
        </div>
      </div>
    <?php } ?>
  </div>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
  <script>
    function showToast(opts) {
      let div = $('<div class="toast" role="alert" aria-live="assertive" aria-atomic="true" data-delay="2500"></div>');
      let color = '#007aff';
      if (opts.class === 'error') color = '#dc3545';
      if (opts.class === 'warn') color = '#ffc107';
      if (opts.class === 'success') color = '#28a745';
      div.html(`
        <div class="toast-header">
          <svg class="rounded mr-2" width="20" height="20" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="xMidYMid slice" focusable="false" role="img">
            <rect width="100%" height="100%" fill="${color}"></rect>
          </svg>
          <strong class="ms-2 me-auto">${opts.title}</strong>
          <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body">
          ${opts.text}
        </div>
      `);
      $('#toasts').append(div);
      div.toast('show');
    }

    $.ajaxPrefilter((options, _, jqXHR) => {
      $('#loader').show();
      jqXHR.done(() => {
        $('#loader').hide();
      });
      jqXHR.fail((err) => {
        $('#loader').hide();
        showToast({
          class: 'error',
          title: 'Error',
          text: err.responseJSON ? err.responseJSON.message : err.statusText
        })
      })
    });
  </script>
  <div id="loader">
    <div>
      <div class="lds-roller">
        <div></div>
        <div></div>
        <div></div>
        <div></div>
        <div></div>
        <div></div>
        <div></div>
        <div></div>
      </div>
    </div>
  </div>
  <div id="toasts"></div>
</body>

</html>