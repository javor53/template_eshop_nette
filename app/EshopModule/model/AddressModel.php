<?php

namespace App\EshopModule\Model;

/**
 * database:address
 * id
 * city
 * street
 * street_number
 * zip
 * phone
 */

use Nette;


class AddressModel extends \App\EshopModule\Model\ManagerModel{
    
    use Nette\SmartObject;
    
    function __construct(\Nette\Database\Context $database) {
        parent::__construct($database);
        
    }
    
    /**
     * 
     * @param type $city
     * @param type $street
     * @param type $num
     * @param type $zip
     * @param type $phone
     */
    function createAddress($city,$street,$num,$zip,$phone) {
        try{
            $this->database->table('address')->insert([
                'city' => $city,
                'street' => $street,
                'street_number' => $num,
                'zip' => $zip,
                'phone' => $phone
             ]);
            $id = $this->database->query('SELECT id FROM address ORDER BY id DESC LIMIT 1')->fetch();
            return $id->id;
        }
        catch(\Exception $e)
        {

        }
    }
    
    /**
     * 
     * @param type $id
     * @return type
     * @throws \Exception
     */
    function getAddress($id) {
        $p = $this->database->query('SELECT * FROM address WHERE id=?',$id)->fetch();
        if(!$p){
            throw new \Exception('Address not found');
        }
        return $p;
    }
    
}
