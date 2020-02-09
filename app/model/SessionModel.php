<?php
namespace App\Model;

use Nette;


class SessionModel
{
    /** @var Nette\Http\Session */
    private $session;

    /** @var Nette\Http\SessionSection */
    public $sessionSection;

    public function __construct(Nette\Http\Session $session)
    {
        $this->session = $session;

        // a získáme přístup do sekce 'mySection':
        $this->sessionSection = $session->getSection('editModeSection');
    }
}