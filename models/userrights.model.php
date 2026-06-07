<?php
require_once "connection.php";

class ModelUserRights {
    public static $lastError = '';
    static public function mdlGetUserCredentials($table, $item, $value) {
        $stmt = (new Connection)->connect()->prepare(
            "SELECT * FROM $table WHERE $item = :item"
        );
        $stmt->bindParam(":item", $value, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    static public function mdlGenerateUserId() {
        $db = (new Connection)->connect();
        $stmt = $db->prepare("SELECT MAX(CAST(userid AS UNSIGNED)) AS max_userid FROM userrights");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $nextId = isset($result['max_userid']) && is_numeric($result['max_userid']) ? ((int)$result['max_userid'] + 1) : 1;
        return str_pad($nextId, 5, '0', STR_PAD_LEFT);
    }

    static public function mdlGenerateLguId() {
        $db = (new Connection)->connect();
        $stmt = $db->prepare("SELECT MAX(id) AS max_id FROM lgu_users");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $nextId = isset($result['max_id']) && is_numeric($result['max_id']) ? ((int)$result['max_id'] + 1) : 1;
        return 'LGU' . str_pad($nextId, 5, '0', STR_PAD_LEFT);
    }

    // This method is redundant - you can remove it since mdlGetUserCredentials does the same thing
    static public function mdlGetUserLogin($username, $password){
        $encryptpass = $password;
        $stmt = (new Connection)->connect()->prepare("SELECT userid, username, password FROM userrights WHERE username = :username AND password = :password");
        $stmt->bindParam(":username", $username, PDO::PARAM_STR);
        $stmt->bindParam(":password", $encryptpass, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch();
    }

    static public function mdlCreateUser($data) {
        $stmt = (new Connection)->connect()->prepare(
            "INSERT INTO userrights (userid, email, password, `Type`) VALUES (:userid, :email, :password, :type)"
        );
        $stmt->bindParam(":userid", $data["userid"], PDO::PARAM_STR);
        $stmt->bindParam(":email", $data["email"], PDO::PARAM_STR);
        $stmt->bindParam(":password", $data["password"], PDO::PARAM_STR);
        $stmt->bindParam(":type", $data["type"], PDO::PARAM_STR);

        return $stmt->execute();
    }

    static protected function mdlTableHasColumn($table, $column) {
        static $columnCache = [];
        $key = "$table.$column";
        if (array_key_exists($key, $columnCache)) {
            return $columnCache[$key];
        }

        $db = (new Connection)->connect();
        $stmt = $db->prepare("SHOW COLUMNS FROM `$table` LIKE :column");
        $stmt->bindParam(":column", $column, PDO::PARAM_STR);
        $stmt->execute();
        $columnCache[$key] = (bool) $stmt->fetch(PDO::FETCH_ASSOC);
        return $columnCache[$key];
    }

    static public function mdlCreateLguRegistration($data) {
        $db = (new Connection)->connect();

        try {
            $db->beginTransaction();

            $stmtUser = $db->prepare(
                "INSERT INTO userrights (userid, email, password, `Type`) VALUES (:userid, :email, :password, :type)"
            );
            $stmtUser->bindParam(":userid", $data["userid"], PDO::PARAM_STR);
            $stmtUser->bindParam(":email", $data["email"], PDO::PARAM_STR);
            $stmtUser->bindParam(":password", $data["password"], PDO::PARAM_STR);
            $stmtUser->bindParam(":type", $data["type"], PDO::PARAM_STR);
            $stmtUser->execute();

            $lguHasContactColumn = self::mdlTableHasColumn('lgu_users', 'contact_number');
            if ($lguHasContactColumn) {
                $stmtLgu = $db->prepare(
                    "INSERT INTO lgu_users (lgu_id, lgu_office_name, office_email_address, office_type, province, region, position_role, first_name, last_name, office_number, contact_number, password) VALUES (:lgu_id, :lgu_office_name, :office_email_address, :office_type, :province, :region, :position_role, :first_name, :last_name, :office_number, :contact_number, :password)"
                );
            } else {
                $stmtLgu = $db->prepare(
                    "INSERT INTO lgu_users (lgu_id, lgu_office_name, office_email_address, office_type, province, region, position_role, first_name, last_name, office_number, password) VALUES (:lgu_id, :lgu_office_name, :office_email_address, :office_type, :province, :region, :position_role, :first_name, :last_name, :office_number, :password)"
                );
            }
            $contactNumber = $data["contact_number"] ?? null;
            $stmtLgu->bindParam(":lgu_id", $data["lgu_id"], PDO::PARAM_STR);
            $stmtLgu->bindParam(":lgu_office_name", $data["lgu_office_name"], PDO::PARAM_STR);
            $stmtLgu->bindParam(":office_email_address", $data["office_email_address"], PDO::PARAM_STR);
            $stmtLgu->bindParam(":office_type", $data["office_type"], PDO::PARAM_STR);
            $stmtLgu->bindParam(":province", $data["province"], PDO::PARAM_STR);
            $stmtLgu->bindParam(":region", $data["region"], PDO::PARAM_STR);
            $stmtLgu->bindParam(":position_role", $data["position_role"], PDO::PARAM_STR);
            $stmtLgu->bindParam(":first_name", $data["first_name"], PDO::PARAM_STR);
            $stmtLgu->bindParam(":last_name", $data["last_name"], PDO::PARAM_STR);
            $stmtLgu->bindParam(":office_number", $data["office_number"], PDO::PARAM_STR);
            if ($lguHasContactColumn) {
                $stmtLgu->bindParam(":contact_number", $contactNumber, PDO::PARAM_STR);
            }
            $stmtLgu->bindParam(":password", $data["password"], PDO::PARAM_STR);
            $stmtLgu->execute();

            $db->commit();
            return true;
        } catch (PDOException $e) {
            $db->rollBack();
            throw $e;
        }
    }

    static public function mdlCreatePublicRegistration($data, $personalData) {
        $db = (new Connection)->connect();

        try {
            $db->beginTransaction();

            $stmtUser = $db->prepare(
                "INSERT INTO userrights (userid, email, password, `Type`) VALUES (:userid, :email, :password, :type)"
            );
            $stmtUser->bindParam(":userid", $data["userid"], PDO::PARAM_STR);
            $stmtUser->bindParam(":email", $data["email"], PDO::PARAM_STR);
            $stmtUser->bindParam(":password", $data["password"], PDO::PARAM_STR);
            $stmtUser->bindParam(":type", $data["type"], PDO::PARAM_STR);
            $stmtUser->execute();

            $stmtPersonal = $db->prepare(
                "INSERT INTO personal_users (user_id, first_name, last_name, middle_initial, extension, date_of_birth, sex, email_address, phone_number, region, account_type, password, status) VALUES (:user_id, :first_name, :last_name, :middle_initial, :extension, :date_of_birth, :sex, :email_address, :phone_number, :region, :account_type, :password, :status)"
            );
            $stmtPersonal->bindParam(":user_id", $personalData["user_id"], PDO::PARAM_STR);
            $stmtPersonal->bindParam(":first_name", $personalData["first_name"], PDO::PARAM_STR);
            $stmtPersonal->bindParam(":last_name", $personalData["last_name"], PDO::PARAM_STR);
            $stmtPersonal->bindParam(":middle_initial", $personalData["middle_initial"], PDO::PARAM_STR);
            $stmtPersonal->bindParam(":extension", $personalData["extension"], PDO::PARAM_STR);
            $stmtPersonal->bindParam(":date_of_birth", $personalData["date_of_birth"], PDO::PARAM_STR);
            $stmtPersonal->bindParam(":sex", $personalData["sex"], PDO::PARAM_STR);
            $stmtPersonal->bindParam(":email_address", $personalData["email_address"], PDO::PARAM_STR);
            $stmtPersonal->bindParam(":phone_number", $personalData["phone_number"], PDO::PARAM_STR);
            $stmtPersonal->bindParam(":region", $personalData["region"], PDO::PARAM_STR);
            $stmtPersonal->bindParam(":account_type", $personalData["account_type"], PDO::PARAM_STR);
            $stmtPersonal->bindParam(":password", $personalData["password"], PDO::PARAM_STR);
            $stmtPersonal->bindParam(":status", $personalData["status"], PDO::PARAM_STR);
            $stmtPersonal->execute();

            $db->commit();
            return true;
        } catch (PDOException $e) {
            $db->rollBack();
            throw $e;
        }
    }

    static public function mdlUpdatePassword($userid, $password, $email) {
        $db = (new Connection)->connect();

        try {
            $db->beginTransaction();

            // Hash the password before saving
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $db->prepare("UPDATE userrights SET password = :password WHERE userid = :userid");
            $stmt->bindParam(":password", $hashed, PDO::PARAM_STR);
            $stmt->bindParam(":userid", $userid, PDO::PARAM_STR);
            $stmt->execute();

            $userData = self::mdlGetUserCredentials('userrights', 'userid', $userid);
            if (!empty($userData) && isset($userData['Type'])) {
                if ($userData['Type'] === 'lgu') {
                    $stmtLgu = $db->prepare("UPDATE lgu_users SET password = :password WHERE office_email_address = :email");
                    $stmtLgu->bindParam(":password", $hashed, PDO::PARAM_STR);
                    $stmtLgu->bindParam(":email", $email, PDO::PARAM_STR);
                    $stmtLgu->execute();
                } elseif ($userData['Type'] === 'public') {
                    $stmtPersonal = $db->prepare("UPDATE personal_users SET password = :password WHERE email_address = :email");
                    $stmtPersonal->bindParam(":password", $hashed, PDO::PARAM_STR);
                    $stmtPersonal->bindParam(":email", $email, PDO::PARAM_STR);
                    $stmtPersonal->execute();
                }
            }

            $db->commit();
            return true;
        } catch (PDOException $e) {
            $db->rollBack();
            error_log("mdlUpdatePassword error: " . $e->getMessage());
            return false;
        }
    }

    static public function mdlUpdateUserProfile($userid, $email, $firstName, $lastName, $contactNumber, $officeEmail = null, $officeNumber = null, $password = null) {
        $db = (new Connection)->connect();

        try {
            $db->beginTransaction();

            // Prepare hashed password if provided
            $hashed = null;
            if ($password !== null && $password !== '') {
                $hashed = password_hash($password, PASSWORD_DEFAULT);
            }

            // Update type-specific profile tables first to avoid FK/email constraint issues
            $userData = self::mdlGetUserCredentials('userrights', 'userid', $userid);
            if (!empty($userData) && isset($userData['Type'])) {
                if ($userData['Type'] === 'lgu') {
                    $lguHasContactColumn = self::mdlTableHasColumn('lgu_users', 'contact_number');
                    $updateSql = "UPDATE lgu_users SET first_name = :first_name, last_name = :last_name";

                    if ($lguHasContactColumn) {
                        $updateSql .= ", contact_number = :contact_number";
                    }

                    if ($lguHasContactColumn) {
                        if ($officeNumber !== null) {
                            $updateSql .= ", office_number = :office_number";
                        }
                    } else {
                        // Legacy schema only has one phone field.
                        $updateSql .= ", office_number = :office_number";
                    }

                    if (isset($officeEmail) && $officeEmail !== '') {
                        $updateSql .= ", office_email_address = :office_email";
                    }

                    $updateSql .= " WHERE lgu_id = :userid OR office_email_address = :email_check";
                    $stmtLgu = $db->prepare($updateSql);
                    $stmtLgu->bindParam(":first_name", $firstName, PDO::PARAM_STR);
                    $stmtLgu->bindParam(":last_name", $lastName, PDO::PARAM_STR);
                    if ($lguHasContactColumn) {
                        $stmtLgu->bindParam(":contact_number", $contactNumber, PDO::PARAM_STR);
                    }
                    if ($lguHasContactColumn) {
                        if ($officeNumber !== null) {
                            $stmtLgu->bindParam(":office_number", $officeNumber, PDO::PARAM_STR);
                        }
                    } else {
                        $phoneToStore = $officeNumber !== null ? $officeNumber : $contactNumber;
                        $stmtLgu->bindParam(":office_number", $phoneToStore, PDO::PARAM_STR);
                    }
                    if (isset($officeEmail) && $officeEmail !== '') {
                        $stmtLgu->bindParam(":office_email", $officeEmail, PDO::PARAM_STR);
                    }
                    $stmtLgu->bindParam(":userid", $userid, PDO::PARAM_STR);
                    $stmtLgu->bindParam(":email_check", $email, PDO::PARAM_STR);
                    $stmtLgu->execute();

                    // If password provided, update LGU password column too
                    if (!empty($hashed)) {
                        $stmtLguPwd = $db->prepare("UPDATE lgu_users SET password = :password WHERE lgu_id = :userid OR office_email_address = :email_check");
                        $stmtLguPwd->bindParam(":password", $hashed, PDO::PARAM_STR);
                        $stmtLguPwd->bindParam(":userid", $userid, PDO::PARAM_STR);
                        $stmtLguPwd->bindParam(":email_check", $email, PDO::PARAM_STR);
                        $stmtLguPwd->execute();
                    }
                } elseif ($userData['Type'] === 'public') {
                    // Update personal user record
                    $stmtPersonal = $db->prepare("UPDATE personal_users SET first_name = :first_name, last_name = :last_name, phone_number = :phone_number, email_address = :email WHERE user_id = :userid OR email_address = :email_check");
                    $stmtPersonal->bindParam(":first_name", $firstName, PDO::PARAM_STR);
                    $stmtPersonal->bindParam(":last_name", $lastName, PDO::PARAM_STR);
                    $stmtPersonal->bindParam(":phone_number", $contactNumber, PDO::PARAM_STR);
                    $stmtPersonal->bindParam(":email", $email, PDO::PARAM_STR);
                    $stmtPersonal->bindParam(":userid", $userid, PDO::PARAM_STR);
                    $stmtPersonal->bindParam(":email_check", $email, PDO::PARAM_STR);
                    $stmtPersonal->execute();

                    if (!empty($hashed)) {
                        $stmtPersonalPwd = $db->prepare("UPDATE personal_users SET password = :password WHERE user_id = :userid OR email_address = :email_check");
                        $stmtPersonalPwd->bindParam(":password", $hashed, PDO::PARAM_STR);
                        $stmtPersonalPwd->bindParam(":userid", $userid, PDO::PARAM_STR);
                        $stmtPersonalPwd->bindParam(":email_check", $email, PDO::PARAM_STR);
                        $stmtPersonalPwd->execute();
                    }
                }
            }

            // Finally update userrights email and optionally password
            if ($hashed !== null) {
                $stmt = $db->prepare("UPDATE userrights SET email = :email, password = :password WHERE userid = :userid");
                $stmt->bindParam(":password", $hashed, PDO::PARAM_STR);
            } else {
                $stmt = $db->prepare("UPDATE userrights SET email = :email WHERE userid = :userid");
            }
            $stmt->bindParam(":email", $email, PDO::PARAM_STR);
            $stmt->bindParam(":userid", $userid, PDO::PARAM_STR);
            $stmt->execute();

            $db->commit();
            return true;
        } catch (PDOException $e) {
            $db->rollBack();
            self::$lastError = $e->getMessage();
            error_log("mdlUpdateUserProfile error: " . $e->getMessage());
            return false;
        }
    }

    static public function mdlUpdateLastLogin($userid, $timestamp) {
        $db = (new Connection)->connect();
        // Ensure column exists (MySQL supports IF NOT EXISTS)
        try {
            $db->exec("ALTER TABLE userrights ADD COLUMN IF NOT EXISTS last_login DATETIME NULL");
        } catch (PDOException $e) {
            // ignore - some MySQL versions may not support IF NOT EXISTS, try safer fallback
            try {
                $db->exec("ALTER TABLE userrights ADD COLUMN last_login DATETIME NULL");
            } catch (PDOException $ex) {
                // ignore if still fails (column may already exist or insufficient privileges)
            }
        }

        $stmt = $db->prepare("UPDATE userrights SET last_login = :last_login WHERE userid = :userid");
        $stmt->bindParam(":last_login", $timestamp, PDO::PARAM_STR);
        $stmt->bindParam(":userid", $userid, PDO::PARAM_STR);
        return $stmt->execute();
    }

    // Ensure permissions table exists and create if not
    static protected function mdlEnsurePermissionsTable() {
        $db = (new Connection)->connect();
        try {
            $db->exec("CREATE TABLE IF NOT EXISTS user_permissions (
                userid VARCHAR(20) PRIMARY KEY,
                permissions TEXT NULL,
                updated_at DATETIME NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
        } catch (PDOException $e) {
            // ignore - creation may fail if insufficient privileges
        }
    }

    // Get permissions array for a user (route => level)
    static public function mdlGetPermissions($userid) {
        self::mdlEnsurePermissionsTable();
        $db = (new Connection)->connect();
        $stmt = $db->prepare("SELECT permissions FROM user_permissions WHERE userid = :userid");
        $stmt->bindParam(":userid", $userid, PDO::PARAM_STR);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row || empty($row['permissions'])) return [];
        $data = json_decode($row['permissions'], true);
        return is_array($data) ? $data : [];
    }

    static public function mdlIsRouteRestricted($userid, $route) {
        if (empty($userid) || empty($route)) {
            return false;
        }
        $permissions = self::mdlGetPermissions($userid);
        return isset($permissions[$route]) && strtolower($permissions[$route]) === 'restricted';
    }

    // Set permissions for a user (permissions is an associative array)
    static public function mdlSetPermissions($userid, $permissions) {
        self::mdlEnsurePermissionsTable();
        $db = (new Connection)->connect();
        // Normalize permission values to lowercase for consistent storage
        if (is_array($permissions)) {
            $permissions = array_map(function($v){ return is_string($v) ? strtolower($v) : $v; }, $permissions);
        }
        $json = json_encode($permissions);
        try {
            $stmt = $db->prepare("REPLACE INTO user_permissions (userid, permissions, updated_at) VALUES (:userid, :permissions, :updated_at)");
            $now = (new DateTime('now', new DateTimeZone('UTC')))->format('Y-m-d H:i:s');
            $stmt->bindParam(":userid", $userid, PDO::PARAM_STR);
            $stmt->bindParam(":permissions", $json, PDO::PARAM_STR);
            $stmt->bindParam(":updated_at", $now, PDO::PARAM_STR);
            return $stmt->execute();
        } catch (PDOException $e) {
            self::$lastError = $e->getMessage();
            return false;
        }
    }

    // Return list of users with optional name fields
    static public function mdlListUsers() {
        $db = (new Connection)->connect();
        $stmt = $db->prepare("SELECT userid, email, `Type` FROM userrights ORDER BY userid ASC");
        $stmt->execute();
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $out = [];
        foreach ($users as $u) {
            $entry = $u;
            $entry['first_name'] = '';
            $entry['last_name'] = '';
            if (strtolower($u['Type'] ?? $u['type'] ?? '') === 'lgu') {
                $extra = self::mdlGetUserCredentials('lgu_users', 'lgu_id', $u['userid']);
                if (!empty($extra)) {
                    $entry['first_name'] = $extra['first_name'] ?? '';
                    $entry['last_name'] = $extra['last_name'] ?? '';
                }
            } else {
                $extra = self::mdlGetUserCredentials('personal_users', 'user_id', $u['userid']);
                if (!empty($extra)) {
                    $entry['first_name'] = $extra['first_name'] ?? '';
                    $entry['last_name'] = $extra['last_name'] ?? '';
                }
            }
            $out[] = $entry;
        }
        return $out;
    }
}
