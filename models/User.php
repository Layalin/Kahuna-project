<?php
class User {
    // Find a user by their username
    public static function findByUsername($pdo, $username) {
        $stmt = $pdo->prepare("SELECT * FROM USERS WHERE username = ?");
        $stmt->execute([$username]);
        return $stmt->fetch();
    }

    // Create a new user
    public static function create($pdo, $username, $password, $role) {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO USERS (username, password_hash, role) VALUES (?, ?, ?)");
        return $stmt->execute([$username, $password_hash, $role]);
    }

    // Save a session token for a user
    public static function saveToken($pdo, $user_id, $token) {
        $stmt = $pdo->prepare("UPDATE USERS SET session_token = ? WHERE id = ?");
        return $stmt->execute([$token, $user_id]);
    }
}
?>