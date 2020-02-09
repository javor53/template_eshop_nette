<?php

namespace App\EshopModule\Model;

/**
 * database:customers
 * id
 * full_name
 * email
 * address_id
 */


use Nette;


class CustomerModel extends \App\EshopModule\Model\ManagerModel{
    
    use Nette\SmartObject;
    
    private $cartModel;
    private $orderManagerModel;
    private $addressModel;
    private $invoiceModel;
    
    function __construct(
        Nette\Database\Context $database,   
        CartModel $cartModel,
        OrderManagerModel $orderManagerModel,
        AddressModel $addressModel,
        InvoiceModel $invoiceModel) {
        
        parent::__construct($database);
        $this->addressModel = $addressModel;
        $this->cartModel = $cartModel;
        $this->orderManagerModel = $orderManagerModel;
        $this->invoiceModel = $invoiceModel;
    }

    /**
     * 
     * @return type
     * @throws \Exception
     */
    function getCustomers() {
        $p = $this->database->query('SELECT * FROM w_customers');
        if(!$p){
            throw new \Exception('Customers not found');
        }
        return $p;
    }
    
    /**
     * 
     * @param type $id
     * @return type
     * @throws \Exception
     */
    function getCustomerDetail($id) {
        $p = $this->database->query('SELECT * FROM w_customer_detail WHERE id=?',$id)->fetch();
          
        if(!$p){
            throw new \Exception('Customer Detail not found');
        }
        
        return $p;
    }
        
    /**
     * 
     * @param type $name
     * @param type $email
     * @param type $address_id
     * @return type
     */
    function createCustomer($name,$email,$address_id,$dAddress_id,$comp_id) {
        try{
            //if(!$this->customerExists($email)){
                $this->database->table('customers')->insert([
                    'full_name' => $name,
                    'email' => $email,
                    'address_id' => $address_id,
                    'delivery_address_id' => $dAddress_id,
                    'customer_comp_id' => $comp_id,
                 ]);
                $id = $this->database->query('SELECT id FROM customers ORDER BY id DESC LIMIT 1')->fetch();
                return $id->id;
            /*}else{
                $customer = $this->database->query('SELECT id FROM customers WHERE email=?',$email)->fetch();
                return $customer->id;
            }*/
        }
        catch(\Exception $e)
        {

        }
        
    }
    
    /**
     * 
     * @param type $name
     * @param type $i_num
     * @param type $vat
     * @return type
     */
    function createCustomerComp($name,$i_num,$vat) {
        try{
            $this->database->table('customer_comp')->insert([
                'name' => $name,
                'i_number' => $i_num,
                'vat' => $vat
             ]);
            $id = $this->database->query('SELECT id FROM customer_comp ORDER BY id DESC LIMIT 1')->fetch();
            return $id->id;
        }
        catch(\Exception $e)
        {

        }
        
    }
    
    /**
     * 
     * @param type $values
     * @return type
     */
    function purchase($values){//pokud má objednávka stejný customer email vytvor objednávku pro již existujícího uživatele(stejné customer_id)
        //zákazník může mít více adres
        $addressId = $this->addressModel->createAddress(
                $values->city,
                $values->street, 
                $values->street_number, 
                $values->zip,
                $values->phone);
        
        if($values->isDelivery){
           $dAddressId = $this->addressModel->createAddress(
                $values->d_city,
                $values->d_street, 
                $values->d_street_number, 
                $values->d_zip,
                   0); 
        }else{
            $dAddressId = NULL;
        }
        
        if($values->isComp){
           $compId = $this->createCustomerComp(
                $values->comp_name, 
                $values->i_number, 
                $values->vat);
        }else{
            $compId = NULL;
        }
        
        $customerId = $this->createCustomer(
                $values->full_name, 
                $values->email, 
                $addressId,
                $dAddressId,
                $compId);

        $data = $this->cartModel->getData();
        $totalPrice = $this->cartModel->getTotalPrice();
         
        $orderId =  $this->orderManagerModel->createOrder(
                 $data,
                 $totalPrice,
                 $values->payment,
                 $values->transport,
                 $values->note, 
                 $customerId);
        
        
        
        $invoice = $this->invoiceModel->createInvoiceByOrderID($orderId);
        $this->invoiceModel->saveInvoice($invoice);
        
        $this->cartModel->emptyCart();        
        return $orderId; 
    }
    
    function emptyCart(){
        $this->cartModel->emptyCart();
    }
    
    /**
     * 
     * @param type $email
     * @return boolean
     */
    function customerExists($email) : bool{
        $p = $this->database->query('SELECT id FROM customers WHERE email=?',$email)->fetch();
        if(!$p){
            return false;
        }else{
            return true;
        }
    }
}
