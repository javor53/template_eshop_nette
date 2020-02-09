<?php

namespace App\EshopModule\Components\Mail;

use Nette;
use Nette\Neon\Neon;
use Nette\Mail\SmtpMailer;

class Mailer
{
    /** @var Nette\Database\Context */
    protected $db;
    
    protected $settings;
    
    protected $m;

    public function __construct(Nette\Database\Context $db)
    {
        $this->db = $db;
        $this->settings =  $this->db->query('SELECT * FROM order_email_setting LIMIT 1')->fetch();


        $this->m = new SmtpMailer([
            'smtp' => true,
            'host' => $this->settings->host,
            'username' => $this->settings->username,
            'password' => $this->settings->password,
            'secure' => $this->settings->secure,
            'port' => 587
        ]);
        
    }
    
    public function send($message) {
        $this->m->send($message);
    }
    /**
     * @return bool|Nette\Database\IRow|Nette\Database\Row
     */
    public function getSettings()
    {
        return $this->settings;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->settings->name;
    }

    /**
     * @return string
     */
    public function getFrom()
    {
        return $this->settings->username;
    }

    /**
     * @param array $to
     * @param string $subject
     * @param string $body
     * @return Nette\Mail\Message
     */
    public function createMessage($to = [], $subject = '', $body = '')
    {
        $message = new Nette\Mail\Message();
        $message->setFrom($this->getFrom(), $this->getName());

        if (!empty($to))
        {
            if (is_array($to))
            {
                foreach ($to as $email)
                    $message->addTo($email);
            }
            else if (is_string($to))
                $message->addTo($to);
        }

        if (!empty($subject))
            $message->setSubject($subject);

        if (!empty($body))
            $message->setBody($body);

        return $message;
    }

}