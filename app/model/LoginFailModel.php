<?php

namespace App\Model;

use Nette;

class LoginFailModel extends ManagerModel
{
    const WAIT_TIME = 30;

    const ATTEMPTS = 3;

    public function deleteOld()
    {
        $olds = $this->database->query('SELECT id FROM w_login_fail WHERE difference > ?', self::WAIT_TIME)->fetchAll();

        foreach ($olds as $old)
            $this->database->query('DELETE FROM login_fail WHERE id = ?', $old->id);
    }

    public function getByIP()
    {
        return $this->database->query('SELECT * FROM w_login_fail WHERE ip LIKE ?', $this->getIP())->fetch();
    }

    public function increaseFailCountByIP()
    {
        $fail = $this->getByIP();

        if (!$fail)
        {
            $this->database->table('login_fail')->insert([
                'ip' => $this->getIP(),
                'count' => 1
            ]);
        }
        else
            $this->database->query('UPDATE login_fail SET `count` = ? WHERE id = ?', $fail->count + 1, $fail->id);
    }

    public function getIP()
    {
        $ipaddress = '';

        if (getenv('HTTP_CLIENT_IP'))
            $ipaddress = getenv('HTTP_CLIENT_IP');
        else if(getenv('HTTP_X_FORWARDED_FOR'))
            $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
        else if(getenv('HTTP_X_FORWARDED'))
            $ipaddress = getenv('HTTP_X_FORWARDED');
        else if(getenv('HTTP_FORWARDED_FOR'))
            $ipaddress = getenv('HTTP_FORWARDED_FOR');
        else if(getenv('HTTP_FORWARDED'))
            $ipaddress = getenv('HTTP_FORWARDED');
        else if(getenv('REMOTE_ADDR'))
            $ipaddress = getenv('REMOTE_ADDR');
        else
            $ipaddress = 'UNKNOWN';

        return $ipaddress;
    }
}