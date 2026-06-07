<?php
class ControllerUserRights {
    static public function ctrUserLogin() {
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_POST["loginEmail"]) || !isset($_POST["loginPass"])) {
            return null;
        }

        $email = trim($_POST["loginEmail"]);
        $password = $_POST["loginPass"];

        $table = 'userrights';
        $item = 'email';
        $value = $email;
        $answer = ModelUserRights::mdlGetUserCredentials($table, $item, $value);

        if (empty($answer) || ($answer["email"] ?? '') !== $email) {
            return "Incorrect email or password.";
        }

        $passwordMatches = false;
        // Prefer hashed password verification, fall back to plain comparison for migration
        if (!empty($answer["password"]) && password_verify($password, $answer["password"])) {
            $passwordMatches = true;
        } elseif (($answer["password"] ?? '') === $password) {
            $passwordMatches = true;
            // Re-hash the plain password into the DB for better security
            try {
                ModelUserRights::mdlUpdatePassword($answer["userid"], $password, $answer["email"]);
            } catch (Exception $e) {
                // non-fatal
            }
        }

        if (!$passwordMatches) {
            return "Incorrect email or password.";
        }

        $displayName = $answer["email"];
        $userType = strtolower($answer["Type"] ?? $answer["type"] ?? '');
        
        if ($userType === 'lgu') {
            $lguDetails = ModelUserRights::mdlGetUserCredentials('lgu_users', 'lgu_id', $answer['userid']);
            if (empty($lguDetails)) {
                $lguDetails = ModelUserRights::mdlGetUserCredentials('lgu_users', 'office_email_address', $answer['email']);
            }
            if (!empty($lguDetails)) {
                $displayName = trim(($lguDetails['first_name'] ?? '') . ' ' . ($lguDetails['last_name'] ?? '')) ?: $displayName;
                $_SESSION['firstname'] = $lguDetails['first_name'] ?? '';
                $_SESSION['lastname'] = $lguDetails['last_name'] ?? '';
            }
        } elseif ($userType === 'public') {
            $publicDetails = ModelUserRights::mdlGetUserCredentials('personal_users', 'user_id', $answer['userid']);
            if (!empty($publicDetails)) {
                $displayName = trim(($publicDetails['first_name'] ?? '') . ' ' . ($publicDetails['last_name'] ?? '')) ?: $displayName;
                $_SESSION['firstname'] = $publicDetails['first_name'] ?? '';
                $_SESSION['lastname'] = $publicDetails['last_name'] ?? '';
            }
        }

        // Successful login: set session values
        $_SESSION["loggedIn"] = "ok";
        $_SESSION["userid"]   = $answer["userid"];
        $_SESSION["user_type"] = $userType;  // ADD THIS LINE - stores 'lgu' or 'public'
        $_SESSION["email"] = $answer["email"];
        $_SESSION["username"] = $displayName;

        // Record current login timestamp (UTC) in DB and session
        $now = (new DateTime('now', new DateTimeZone('UTC')))->format('Y-m-d H:i:s');
        try {
            ModelUserRights::mdlUpdateLastLogin($answer["userid"], $now);
            $_SESSION['last_login'] = $now;
        } catch (Exception $e) {
            // non-fatal: continue without blocking login if update fails
        }

        // Redirect based on user type
        if ($userType === 'lgu') {
            header("Location: ?route=home");
        } else {
            header("Location: ?route=map");
        }
        exit();
    }

    // ... rest of your existing code remains the same ...
}