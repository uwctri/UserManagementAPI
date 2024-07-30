<?php

namespace UWMadison\UserManagementAPI;

use ExternalModules\AbstractExternalModule;
use Logging;

class UserManagementAPI extends AbstractExternalModule
{
    private $requestingUser;
    private $action_list = ["add", "remove", "suspend", "reset", "modify", "read", "dump"];

    public function process()
    {
        $result = [
            'status' => 'failure',
            'message' => 'Invalid token',
        ];

        $token = $this->sanitizeAPIToken($_POST['token']);
        $action = lower($_POST['action']);
        $user = $_POST['user'];
        $payload = $_POST;
        unset($payload['token']);
        unset($payload['action']);
        unset($payload['user']);

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

        $this->requestingUser = db_fetch_assoc($q)["username"];

        if (empty($action) || empty($user)) {
            $result['message'] = 'Missing user or action';
            return $result;
        }

        if (!in_array($action, $this->action_list)) {
            $result['message'] = 'Invalid action';
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
        return [
            'status' => $status,
            'message' => $msg,
            'data' => $data
        ];
    }

    private function add($user, $payload)
    {
        // TODO (Table based user only)
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
        // TODO
    }

    private function dump($user, $payload)
    {
        // TODO
    }
}
