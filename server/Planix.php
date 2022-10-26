<?php

class Planix
{
    protected $DB, $USER;

    function __construct()
    {
        $this->DB = new mysqli("mysql", "admin", "admin", "db");
        if ($this->DB->connect_error) {
            exit("Connection failed: " . $this->DB->connect_error);
        }
        $token = explode(" ", $_SERVER["HTTP_AUTHORIZATION"]);
        if (isset($token[1])) {
            $this->USER = $this->DB->query("SELECT users.*, clients.code, clients.name as client FROM users, clients
WHERE users.active=1 AND users.client_id=clients.id AND md5(users.id)='" . $token[1] . "'")
                ->fetch_assoc();
        }
    }

    public function login($email, $password)
    {
        if (empty($email) or empty($password)) {
            return false;
        }
        try {
            $email = $this->DB->real_escape_string($email);
            $password = md5($this->DB->real_escape_string($password));
            $user = $this->DB->query("SELECT * FROM users WHERE users.active=1 AND email='$email' AND password='$password'")->fetch_assoc();
            if (isset($user['id'])) {
                $user['token'] = md5($user['id']);
                return $user;
            } else {
                return ['error' => "Не верно указан логин или пароль!"];
            }
        } catch (Throwable $e) {
            return $e->getMessage();
        }
    }

    public function tickets($filters = [], $page = 0)
    {
        if (empty($this->USER)) {
            return false;
        }
        if ($this->USER['role'] == 1)
            $filter = "tasks.user_id=" . $this->USER['id'] . " AND ";
        elseif ($this->USER['role'] == 2)
            $filter = "tasks.user_id=" . $this->USER['id'] . " AND ";
        else
            $filter = "";
        foreach ($filters as $key => $value) {
            if ($key == "stack" and strlen($value)) $filter .= "tasks.stack_id=" . (int)$value . " AND ";
            if ($key == "location" and strlen($value)) $filter .= "tasks.location_id=" . (int)$value . " AND ";
            if ($key == "object" and strlen($value)) $filter .= "tasks.object_id=" . (int)$value . " AND ";
            if ($key == "user" and strlen($value)) $filter .= "tasks.user_id=" . (int)$value . " AND ";
            if ($key == "ppr" and strlen($value)) $filter .= "tasks.ppr=" . (int)$value . " AND ";
            if ($key == "status" and strlen($value)) $filter .= "tasks.status IN (" . join(",", [$value]) . ") AND ";
            if ($key == "date1" and strlen($value)) $filter .= "tasks.created_at >= '" . $value . "' AND ";
            if ($key == "date2" and strlen($value)) $filter .= "tasks.created_at <= '" . $value . " 23:59:59' AND ";
        }
        $limit = (!empty($page)) ? "LIMIT " . (int)$page * 50 . ",50" : "LIMIT 0,50";
        if ($page == "all") $limit = "LIMIT 0,5000";
        $query = "SELECT
tasks.id,
DATE_FORMAT(created_at,'%d.%m.%Y') as created_date,
DATE_FORMAT(created_at,'%H:%i') as created_time,
DATE_FORMAT(tasks.finish,'%d.%m.%Y %H:%i') as finish,
tasks.status,
tasks.ppr,
tasks.priority,
tasks.about,
tasks.about_original,
tasks.period_id as period,
stacks.name as stack,
users.name as user,
locations.name as location,
objects.name as object
FROM tasks
LEFT JOIN stacks ON stacks.id=tasks.stack_id
LEFT JOIN users ON users.id=tasks.user_id
LEFT JOIN objects ON objects.id=tasks.object_id
LEFT JOIN locations ON locations.id=tasks.location_id
WHERE " . $filter . " tasks.client_id=" . $this->USER['client_id'] . "
ORDER BY tasks.id DESC " . $limit;
        $tickets = $this->DB
            ->query($query)->fetch_all(MYSQLI_ASSOC);
        return $tickets;
    }

    public function users($filters = [])
    {
        if (empty($this->USER)) {
            return false;
        }
        $filter = "";
        $active = 1;
        foreach ($filters as $key => $value) {
            if ($key == "role" and strlen($value)) $filter .= "type IN (" . $value . ") AND ";
            if ($key == "active" and strlen($value)) $active = 0;
            if ($key == "q" and strlen($value)) $filter .= "(name LIKE '%" . $value . "%' OR email LIKE '%" . $value . "%') AND ";
        }
        $users = $this->DB
            ->query("SELECT id, name, jobtitle, email, type, company, mailing, active
FROM users
WHERE " . $filter . "active=" . $active . " AND client_id=" . $this->CLIENT['id'] . " ORDER BY name")
            ->fetch_all(MYSQLI_ASSOC);
        foreach ($users as $key => $value) {
            $users[$key]['role_name'] = $KSUTO['roles'][$value['type']];
        }
        return $users;
    }

    public function docs($filters = [])
    {
        if (empty($this->USER)) {
            return false;
        }
    }
}