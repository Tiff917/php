<?php
declare(strict_types=1);

require_once __DIR__ . '/helpers.php';

if (is_logged_in() || empty($_COOKIE[REMEMBER_COOKIE])) {
    return;
}

$parts = explode(':', (string) $_COOKIE[REMEMBER_COOKIE], 2);
if (count($parts) !== 2) {
    clear_remember_cookie();
    return;
}

[$selector, $token] = $parts;

$stmt = db()->prepare(
    'SELECT rt.*, u.id AS user_id, u.username, u.display_name, u.email, u.role
     FROM remember_tokens rt
     INNER JOIN users u ON u.id = rt.user_id
     WHERE rt.selector = :selector AND rt.expires_at > NOW()
     LIMIT 1'
);
$stmt->execute(['selector' => $selector]);
$record = $stmt->fetch();

if (!$record || !hash_equals($record['token_hash'], hash('sha256', $token))) {
    clear_remember_cookie();
    if ($record) {
        db()->prepare('DELETE FROM remember_tokens WHERE id = :id')->execute(['id' => $record['id']]);
    }
    return;
}

login_user([
    'id' => $record['user_id'],
    'username' => $record['username'],
    'display_name' => $record['display_name'],
    'email' => $record['email'],
    'role' => $record['role'],
]);

db()->prepare('DELETE FROM remember_tokens WHERE id = :id')->execute(['id' => $record['id']]);
issue_remember_token((int) $record['user_id']);
