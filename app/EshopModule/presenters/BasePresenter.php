<?php

namespace EshopModule;

use Nette;
use App\Model;
use Nette\Application\UI\Presenter;
use App\EshopModule\Components\Mail;

abstract class BasePresenter extends Presenter
{
     /** @var Model\UserManager @inject */
    public $userManager;
    
    /** @var Mail\Mailer @inject */
    public $mailer;
    
    protected $admin;    
    
    public function beforeRender(){
        parent::beforeRender();
        $this->template->admin = $this->admin;
        
        $this->template->menuItems = [
            'Homepage' => ['Hlavní stránka' => ':Public:Homepage:default'],
            //'Moduly' => ['Blog' => ':Admin:Homepage:default', 'Eshop' => ':Eshop:Homepage:default'],
            'Produkty' => ['Přehled' => ':Eshop:Product:default', 'Ceny' => ':Eshop:Product:price','Sklad' => ':Eshop:Product:stock','Kategorie' => ':Eshop:Product:category'],
            'Objednávky' => ['Přehled' => ':Eshop:Order:default'],
            'Zákazníci' => ['Přehled' => ':Eshop:Customer:default'],
            'Nastavení' => ['Faktury' => ':Eshop:Invoice:settings','E-maily' => ':Eshop:OrderEmails:settings'],
        ]; 
        $this->template->menuItemsJournalist = [
            'Homepage' => ['Hlavní stránka' => ':Public:Homepage:default'],
            'Galerie' => ['Správa obrázků' => ':Admin:Images:upload','Správa kategorií' => ':Admin:Collections:adminShow'],
            'Blog' => ['Seznam článků' => ':Admin:Post:adminShow'  ,'Vytvořit nový článek' => ':Admin:Post:create'],
        ];
    }
    public function startup() {
        parent::startup();
        $this->admin = $this->userManager->getByID($this->user->getId());
    }
}
