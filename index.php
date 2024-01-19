<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Slim\Views\PhpRenderer;

define('PROFTPDMANAGER', 1);


function loadConfig()
{
  if (!file_exists('config.php')) {
    return [
      'error' => 'Missing Configuration',
      'code' => 400
    ];
  }
  $session = new \SlimSession\Helper();
  if (!$session->exists('user_logged_in')) {
    return [
      'error' => 'Not logged in',
      'code' => 401
    ];
  }
  $config = require('config.php');
  foreach ($config as $k => &$v) {
    if ($k !== 'appPassword')
      $v = base64_decode($v);
  }
  extract($config);
  try {
    $db = new \PDO("mysql:host=$databaseHost;port=$databasePort;dbname=$databaseName", $databaseUser, $databasePass);
  } catch (PDOException $e) {
    return [
      'error' => 'Unable to connect to database',
      'code' => 500
    ];
  }
  $config['groupsTableEditableFields'] = explode(',', $groupsTableEditableFields);
  $config['usersTableEditableFields'] = explode(',', $usersTableEditableFields);
  $config['db'] = $db;
  return $config;
}

function sendJson($response, $data, $code = 200)
{
  $response->getBody()->write(json_encode($data));
  return $response->withHeader('Content-Type', 'application/json')->withStatus($code);
}

require __DIR__ . '/vendor/autoload.php';

$app = AppFactory::create();

$base_path = substr(str_replace('\\', '/', realpath(dirname(__FILE__))), strlen(str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT']))));

$app->setBasePath($base_path);

$app->add(
  new \Slim\Middleware\Session([
    'name' => 'slim_session',
    'autorefresh' => true,
    'lifetime' => '1 hour',
  ])
);

$app->get('/', function (Request $request, Response $response, $args) {
  $renderer = new PhpRenderer('templates');
  $renderer->setLayout('layout.php');
  if (!file_exists('config.php')) {
    return $renderer->render($response, "install.php");
  }
  $session = new \SlimSession\Helper();
  if (!$session->exists('user_logged_in')) {
    return $renderer->render($response, "login.php");
  }
  $config = require('config.php');
  foreach ($config as $k => &$v) {
    if ($k !== 'appPassword')
      $v = base64_decode($v);
  }
  extract($config);
  try {
    $db = new \PDO("mysql:host=$databaseHost;port=$databasePort;dbname=$databaseName", $databaseUser, $databasePass);
  } catch (PDOException $e) {
    var_dump($e);
    return $renderer->render($response, "error.php", [
      'toast' => [
        'title' => 'Error',
        'text' => 'Unable to connect to database'
      ]
    ]);
  }
  $users = $db->query("SELECT COUNT(id) AS count FROM {$usersTable}");
  $groups = $db->query("SELECT COUNT(id) AS count FROM {$usersTable}");
  return $renderer->render($response, "dashboard.php", [
    'users' => ceil($users->fetch()['count'] / 20),
    'groups' => ceil($groups->fetch()['count'] / 20),
    'logged_in' => true,
    'config' => $config
  ]);
});

$app->get('/logout', function (Request $request, Response $response, $args) use ($base_path) {
  $session = new \SlimSession\Helper();
  $session->destroy();
  return $response
    ->withHeader('Location', $base_path)
    ->withStatus(302);
});

$app->post('/api/install', function (Request $request, Response $response, $args) {
  if (file_exists('config.php')) {
    return sendJson($response, [
      'status' => 'error',
      'message' => 'Already Installed'
    ], 400);
  }
  $databaseHost = $_POST['databaseHost'];
  $databasePort = $_POST['databasePort'];
  $databaseName = $_POST['databaseName'];
  $databaseUser = $_POST['databaseUser'];
  $databasePass = $_POST['databasePass'];
  $groupsTable = $_POST['groupsTable'];
  $usersTable = $_POST['usersTable'];
  $appPassword = $_POST['appPassword'];
  $groupsTableEditableFields = $_POST['groupsTableEditableFields'];
  $usersTableEditableFields = $_POST['usersTableEditableFields'];
  $usersTableIdField = $_POST['usersTableIdField'];
  $usersTablePasswdField = $_POST['usersTablePasswdField'];

  try {
    $db = new \PDO("mysql:host=$databaseHost;port=$databasePort;dbname=$databaseName", $databaseUser, $databasePass);
  } catch (PDOException $e) {
    return sendJson($response, [
      'status' => 'error',
      'message' => 'Unable to connect to database'
    ], 400);
  }
  $tables = array_map(function ($e) {
    return strtolower($e[0]);
  }, $db->query('SHOW TABLES')->fetchAll(PDO::FETCH_BOTH));

  if (!in_array(strtolower($groupsTable), $tables)) {
    return sendJson($response, [
      'status' => 'error',
      'message' => 'Invalid Groups Table Name'
    ], 400);
  }

  if (!in_array(strtolower($usersTable), $tables)) {
    return sendJson($response, [
      'status' => 'error',
      'message' => 'Invalid Users Table Name'
    ], 400);
  }

  if (strlen($appPassword) < 8) {
    return sendJson($response, [
      'status' => 'error',
      'message' => 'Password must be at least 8 characters long'
    ], 400);
  }

  $template = "<?php
  return array(
    'databaseHost' => '%s',
    'databasePort' => '%s',
    'databaseName' => '%s',
    'databaseUser' => '%s',
    'databasePass' => '%s',
    'groupsTable' => '%s',
    'groupsTableEditableFields' => '%s',
    'usersTable' => '%s',
    'usersTableEditableFields' => '%s',
    'usersTableIdField' => '%s',
    'usersTablePasswdField' => '%s',
    'appPassword' => '%s',
  );
  ";

  $config = sprintf(
    $template,
    base64_encode($databaseHost),
    base64_encode($databasePort),
    base64_encode($databaseName),
    base64_encode($databaseUser),
    base64_encode($databasePass),
    base64_encode($groupsTable),
    base64_encode($groupsTableEditableFields),
    base64_encode($usersTable),
    base64_encode($usersTableEditableFields),
    base64_encode($usersTableIdField),
    base64_encode($usersTablePasswdField),
    password_hash($appPassword, PASSWORD_DEFAULT)
  );
  if (!file_put_contents('config.php', $config)) {
    return sendJson($response, [
      'status' => 'error',
      'message' => 'Unable to write config'
    ], 500);
  }
  return sendJson($response, [
    'status' => 'OK'
  ]);
});

$app->post('/api/login', function (Request $request, Response $response, $args) {
  if (!file_exists('config.php')) {
    return sendJson($response, [
      'status' => 'error',
      'message' => 'Not Installed'
    ], 500);
  }
  $session = new \SlimSession\Helper();
  if ($session->exists('user_logged_in')) {
    return sendJson($response, [
      'status' => 'error',
      'message' => 'Already Logged In'
    ], 500);
  }
  $config = require('config.php');
  $hash = $config['appPassword'];
  if (!password_verify($_POST['appPassword'], $hash)) {
    return sendJson($response, [
      'status' => 'error',
      'message' => 'Wrong Password'
    ], 401);
  }
  $session->set('user_logged_in', md5($hash));
  return sendJson($response, [
    'status' => 'OK'
  ]);
});

$app->get('/api/users/', function (Request $request, Response $response, $args) {
  $config = loadConfig();
  if (isset($config['error'])) {
    return sendJson($response, [
      'status' => 'error',
      'message' => $config['error']
    ], $config['code']);
  }
  extract($config);
  $page = isset($_GET['page']) && $_GET['page'] ? intval($_GET['page']) : 1;
  $perPage = 20;
  $offset = ($page - 1) * $perPage;
  $users = $db->query("SELECT * FROM {$config['usersTable']} LIMIT {$perPage} OFFSET {$offset}");
  return sendJson($response, [
    'status' => 'OK',
    'data' => $users->fetchAll(PDO::FETCH_ASSOC)
  ]);
});

$app->post('/api/users/', function (Request $request, Response $response, $args) {
  $config = loadConfig();
  if (isset($config['error'])) {
    return sendJson($response, [
      'status' => 'error',
      'message' => $config['error']
    ], $config['code']);
  }
  extract($config);
  extract($config);
  if (!$_POST['passwd'] || !$_POST['passwd_confirm'] || $_POST['passwd'] !== $_POST['passwd_confirm']) {
    return sendJson($response, [
      'status' => 'error',
      'message' => 'Passwords mismatch'
    ], 400);
  }
  $passwd_bin = openssl_digest($_POST['passwd'], 'MD5', true);
  $passwd = '{md5}' . base64_encode($passwd_bin);
  $fields = ["`{$usersTablePasswdField}`"];
  $names = [':passwd'];
  $values = ['passwd' => $passwd];

  foreach ($usersTableEditableFields as $f) {
    $fields[] = "`{$f}`";
    $names[] = ":{$f}";
    $values[$f] = $_POST[$f];
  }

  $sql = "INSERT INTO {$usersTable} (" . join(',', $fields) . ") VALUES (" . join(',', $names) . ")";
  $result = $db->prepare($sql)->execute($values);
  if (!$result) {
    return sendJson($response, [
      'status' => 'error'
    ], 500);
  }
  return sendJson($response, [
    'status' => 'OK'
  ]);
});

$app->post('/api/users/{id}', function (Request $request, Response $response, $args) {
  $config = loadConfig();
  if (isset($config['error'])) {
    return sendJson($response, [
      'status' => 'error',
      'message' => $config['error']
    ], $config['code']);
  }
  extract($config);
  $fields = [];
  $values = [];
  foreach ($usersTableEditableFields as $f) {
    $fields[] = "`{$f}` = :{$f}";
    $values[$f] = $_POST[$f];
  }
  $values['id'] = $args['id'];
  $sql = "UPDATE {$usersTable} SET " . join(',', $fields) . " WHERE {$usersTableIdField} = :id";

  $result = $db->prepare($sql)->execute($values);
  if (!$result) {
    return sendJson($response, [
      'status' => 'error',
      'message' => 'Unable to save User'
    ], 500);
  }
  if ($_POST['passwd'] && $_POST['passwd_confirm']) {
    if ($_POST['passwd'] !== $_POST['passwd_confirm']) {
      return sendJson($response, [
        'status' => 'error',
        'message' => 'Passwords mismatch'
      ], 500);
    }
    $sql = "UPDATE {$usersTable} SET {$usersTablePasswdField} = :passwd WHERE {$usersTableIdField} = :id";
    $passwd_bin = openssl_digest($_POST['passwd'], 'MD5', true);
    $passwd = base64_encode($passwd_bin);
    $result = $db->prepare($sql)->execute([
      'passwd' => '{MD5}' . $passwd,
      'id' => $id
    ]);
    if (!$result) {
      return sendJson($response, [
        'status' => 'error',
        'message' => 'Unable to update password'
      ], 500);
    }
  }
  return sendJson($response, [
    'status' => 'OK'
  ]);
});

$app->delete('/api/users/{id}', function (Request $request, Response $response, $args) {
  $config = loadConfig();
  if (isset($config['error'])) {
    return sendJson($response, [
      'status' => 'error',
      'message' => $config['error']
    ], $config['code']);
  }
  extract($config);
  $sql = "DELETE FROM {$usersTable} WHERE {$usersTableIdField} = :id";
  $result = $db->prepare($sql)->execute([
    'id' => $args['id']
  ]);
  if (!$result) {
    return sendJson($response, [
      'status' => 'error',
      'message' => 'Unable to delete'
    ], 500);
  }
  return sendJson($response, [
    'status' => 'OK'
  ]);
});

$app->run();
