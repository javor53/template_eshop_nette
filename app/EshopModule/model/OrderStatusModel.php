<?php

namespace App\EshopModule\Model;

use Nette;


abstract class OrderStatusModel{
    
    use Nette\SmartObject;
    
    const NewOrder = 1;
    const Processing = 2;
    const Shipping = 3;
    const Delivered = 4;
    const Canceled = 5;
    
    
}
