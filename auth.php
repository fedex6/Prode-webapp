<?php
session_start();

function isLoggedIn(): bool {
    return isset($_SESSION['user_id']);
}

function isAdmin(): bool {
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;
}

function requireLogin(): void {
    if (!isLoggedIn()) {
        header('Location: index.php');
        exit;
    }
}

function requireAdmin(): void {
    requireLogin();
    if (!isAdmin()) {
        header('Location: dashboard.php');
        exit;
    }
}

function currentUser(): array {
    return [
        'id'           => $_SESSION['user_id'] ?? 0,
        'username'     => $_SESSION['username'] ?? '',
        'display_name' => $_SESSION['display_name'] ?? '',
        'is_admin'     => $_SESSION['is_admin'] ?? 0,
        'avatar'       => $_SESSION['avatar'] ?? '👤',
    ];
}
