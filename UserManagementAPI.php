<?php

namespace UWMadison\UserManagementAPI;

use ExternalModules\AbstractExternalModule;
use Logging;

class UserManagementAPI extends AbstractExternalModule
{
    private $requesting_user;
    private $default_page_size = 100;
    private $action_list = ["add", "remove", "suspend", "reset", "modify", "read", "dump"];
    private $term_map = [
        "id" => "ui_id", // no changes
        "user" => "username", // no changes
        "first_name" => "user_firstname",
        "last_name" => "user_lastname",
        "email" => "user_email", // email
        // Optional
        "email2" => "user_email2", // email
        "email3" => "user_email3", // email
        "phone" => "user_phone", // phone
        "mobile" => "user_phone_sms", // phone
        "institution_id" => "user_inst_id",
        "sponsor" => "user_sponsor", // username
        "expiration" => "user_expiration", // datetime
        "comments" => "user_comments",
        "display_email" => "display_on_email_users", // boolean
        "allow_creation" => "allow_create_db", // boolean
        // Times
        "creation" => "user_creation", // no changes
        "activity" => "user_lastactivity", // no changes
        "login" => "user_lastlogin", // no changes
        "suspended" => "user_suspended_time", // no changes
        // Admin
        "super_user" => "super_user", // no changes
        // "admin" flag if any admin setting is on
    ];

    public function process()
    {
        $result = [
            "status" => "failure",
            "message" => "Invalid token",
        ];

        $token = $this->sanitizeAPIToken($_POST["token"]);
        $action = lower($_POST["action"]);
        $user = $_POST["user"];
        $payload = $_POST;
        unset($payload["token"]);
        unset($payload["action"]);
        unset($payload["user"]);

        if (strlen($token) !== 64) {
            return $result;
        }

        $q = $this->query("
        SELECT username, super_user
        FROM redcap_user_information
        WHERE api_token = ?
        AND user_suspended_time IS NULL
        LIMIT 1", $token);

        if (!($q && $q !== false && db_num_rows($q) == 1)) {
            return $result;
        }

        $this->requesting_user = db_fetch_assoc($q)["username"];

        if (empty($action) || (empty($user) && $action !== "dump")) {
            $result["message"] = "Missing user or action";
            return $result;
        }

        if (!in_array($action, $this->action_list)) {
            $result["message"] = "Invalid action";
            return $result;
        }

        return $this->$action($user, $payload);
    }

    private function systemLog($sql, $username, $msg)
    {
        // TODO not sure if we need this
    }

    private function package($status, $msg, $data)
    {
        $result = [
            "status" => $status,
            "message" => $msg,
            "data" => []
        ];
        if (empty($data))
            $result;
        foreach ($this->term_map as $nice => $orig)
            $result["data"][$nice] = $data[$orig];
        $result["data"]["admin"] = false;
        $admin_flags = ["super_user", "account_manager", "access_system_config", "access_system_upgrade", "access_external_module_install", "admin_rights", "access_admin_dashboards"];
        foreach ($admin_flags as $flag) {
            if ($data[$flag]) {
                $result["data"]["admin"] = true;
                break;
            }
        }
        return $result;
    }

    private function add($user, $payload)
    {
        // TODO (Table based user only)
        // What data needs to be defaulted?
    }

    private function remove($user, $payload)
    {
        // TODO
    }

    private function suspend($user, $payload)
    {
        // TODO
    }

    private function reset($user, $payload)
    {
        // TODO (Table based user only)
    }

    private function modify($user, $payload)
    {
        // TODO
    }

    private function read($user, $payload)
    {
        $q = $this->query("SELECT * FROM redcap_user_information WHERE username = ?", $user);
        if (!($q && $q !== false && db_num_rows($q) == 1))
            return $this->package("failure", "User not found", []);
        return $this->package("success", "User found, data returned.", db_fetch_assoc($q));
    }

    private function dump($user, $payload)
    {
        $page = $payload["page"] ?? 0;
        $size = $payload["page_size"] ?? $this->default_page_size;
        $q = $this->query("SELECT * FROM redcap_user_information LIMIT ? OFFSET ?", $size, $page * $size);
        $result = [
            "status" => "success",
            "message" => "Dumping users, page $page",
            "data" => []
        ];
        $i = $page * $size;
        while ($row = db_fetch_assoc($q)) {
            $result["data"][$i] = $this->package("", "", $row)["data"];
            $i++;
        }
    }
}
