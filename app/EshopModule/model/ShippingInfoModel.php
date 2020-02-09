<?php

namespace App\EshopModule\Model;

use Nette;


class ShippingInfoModel{
    
    use Nette\SmartObject;
    
    public $configStatus = [
            '1' => 'Nová',
            '2' => 'Zpracovává se',
            '3' => 'Expedovaná',
            '4' => 'Doručená',
            '5' => 'Zrušená'
            ];

    public $configPayment = [
            'transfer-to-account' => 'Převodem na účet',
            'cash-on-delivery' => 'Dobírka',
        ];
    public $configTransport = [
            'ppl' => 'PPL',
            'cz-post' => 'Česká pošta',
        ];
    public $configPaymentStatus = [
            'paid' => 'Zaplaceno',
            'unpaid' => 'Nezaplaceno',
        ];
    
}
