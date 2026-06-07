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
                $lguDetails = ModelUserRights::mdlGetUserCredentials('lgu_users', 'lgu_id', 'LGU' . $answer['userid']);
            }
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
        // Persist assigned center in session if available
        if (!empty($answer['assigned_center_id'])) {
            $_SESSION['assigned_center_id'] = $answer['assigned_center_id'];
        } else {
            $_SESSION['assigned_center_id'] = null;
        }

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

    static public function ctrUserRegister() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $firstName = trim($_POST['firstName'] ?? '');
        $lastName = trim($_POST['lastName'] ?? '');
        $middleInitial = trim($_POST['middleInitial'] ?? '');
        $extension = trim($_POST['extension'] ?? '');
        $dateOfBirth = trim($_POST['dateOfBirth'] ?? '');
        $sex = trim($_POST['sex'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phoneNumber = trim($_POST['phoneNumber'] ?? '');
        $region = trim($_POST['region'] ?? '');
        $accountType = trim($_POST['accountType'] ?? 'public');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirmPassword'] ?? '';

        if (empty($firstName) || empty($lastName) || empty($dateOfBirth) || empty($sex) || empty($email)
            || empty($phoneNumber) || empty($region) || empty($password) || empty($confirmPassword)) {
            return 'Please fill in all required fields.';
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return 'Please enter a valid email address.';
        }

        if ($password !== $confirmPassword) {
            return 'Password and Confirm Password must match.';
        }

        $existingUser = ModelUserRights::mdlGetUserCredentials('userrights', 'email', $email);
        if (!empty($existingUser)) {
            return 'This email address is already registered.';
        }

        if ($accountType === 'lgu') {
            $_SESSION['lgu_registration'] = [
                'firstName' => $firstName,
                'lastName' => $lastName,
                'email' => $email,
                'password' => $password,
                'accountType' => 'lgu'
            ];

            header('Location: ?route=registration_lgu');
            exit;
        }

        $userId = ModelUserRights::mdlGenerateUserId();
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $registrationData = [
            'userid' => $userId,
            'email' => $email,
            'password' => $hashedPassword,
            'type' => 'public'
        ];

        $personalData = [
            'user_id' => $userId,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'middle_initial' => $middleInitial,
            'extension' => $extension,
            'date_of_birth' => $dateOfBirth,
            'sex' => $sex,
            'email_address' => $email,
            'phone_number' => $phoneNumber,
            'region' => $region,
            'account_type' => 'public',
            'password' => $hashedPassword,
            'status' => 'active'
        ];

        try {
            return ModelUserRights::mdlCreatePublicRegistration($registrationData, $personalData);
        } catch (PDOException $e) {
            return 'Failed to register user: ' . $e->getMessage();
        }
    }

    static public function ctrLguRegister() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $registrationData = $_SESSION['lgu_registration'] ?? [];
        if (empty($registrationData) || empty($registrationData['email']) || empty($registrationData['password'])) {
            return 'Please complete the first registration step before continuing.';
        }

        $lguOfficeName = trim($_POST['lguOfficeName'] ?? '');
        $lguOfficeEmail = trim($_POST['lguOfficeEmail'] ?? '');
        $lguOfficeNumber = trim($_POST['lguOfficeNumber'] ?? '');
        $lguContactNumber = trim($_POST['lguContactNumber'] ?? '');
        $lguOfficeType = trim($_POST['lguOfficeType'] ?? '');
        $lguDepartment = trim($_POST['lguDepartment'] ?? '');
        $lguRegion = trim($_POST['lguRegion'] ?? '');
        $lguProvince = trim($_POST['lguProvince'] ?? '');
        $lguPosition = trim($_POST['lguPosition'] ?? '');

        if (empty($lguOfficeName) || empty($lguOfficeEmail) || empty($lguOfficeNumber) || empty($lguContactNumber) || empty($lguOfficeType)
            || empty($lguDepartment) || empty($lguRegion) || empty($lguProvince) || empty($lguPosition)) {
            return 'Please fill in all required LGU fields.';
        }

        if (!filter_var($lguOfficeEmail, FILTER_VALIDATE_EMAIL)) {
            return 'Please enter a valid office email address.';
        }

        if (!empty(ModelUserRights::mdlGetUserCredentials('userrights', 'email', $registrationData['email']))) {
            return 'This email address is already registered.';
        }

        if (!empty(ModelUserRights::mdlGetUserCredentials('userrights', 'email', $lguOfficeEmail))) {
            return 'This office email address is already registered.';
        }

        $userId = ModelUserRights::mdlGenerateUserId();
        $hashedPassword = password_hash($registrationData['password'], PASSWORD_DEFAULT);

        $userData = [
            'userid' => $userId,
            'email' => $registrationData['email'],
            'password' => $hashedPassword,
            'type' => 'lgu'
        ];

        $lguUserData = [
            'lgu_id' => ModelUserRights::mdlGenerateLguId(),
            'lgu_office_name' => $lguOfficeName,
            'office_email_address' => $lguOfficeEmail,
            'office_type' => $lguOfficeType,
            'province' => $lguProvince,
            'region' => $lguRegion,
            'position_role' => $lguPosition,
            'first_name' => $registrationData['firstName'],
            'last_name' => $registrationData['lastName'],
            'office_number' => $lguOfficeNumber,
            'contact_number' => $lguContactNumber,
            'password' => $hashedPassword
        ];

        $lguRegistrationData = array_merge($userData, $lguUserData);

        try {
            return ModelUserRights::mdlCreateLguRegistration($lguRegistrationData);
        } catch (PDOException $e) {
            return 'Failed to register LGU user: ' . $e->getMessage();
        }
    }

}