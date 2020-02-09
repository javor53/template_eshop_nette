<?php

namespace App\EshopModule\Model;

use Nette;
use Nette\Utils\DateTime;

/**
 * database: orders
 * id
 * data
 * total_price
 * created_at
 * payment
 * transport
 * status
 * note
 * payment_status
 */

class OrderManagerModel extends \App\EshopModule\Model\ManagerModel{
    
    use Nette\SmartObject;
    
    private $shippingInfoModel;
    private $itemModel;
    private $addressModel;
    
    public  $orderId;
    protected $products;
            
    function __construct(
                Nette\Database\Context $database,
                ShippingInfoModel $shippingInfoModel, 
                ItemModel $itemModel,
                AddressModel $addressModel) {
        
        parent::__construct($database);
        $this->addressModel = $addressModel;
        $this->shippingInfoModel = $shippingInfoModel;
        $this->itemModel = $itemModel;   
    }
    
    /**
     * 
     * @return type
     * @throws \Exception
     */
    function getOrders() {
        $p = $this->database->query('SELECT * FROM w_orders')->fetchAll();
        if(!$p){
            throw new \Exception('Orders not found');
        }
        return $p;
    }
    
    /**
     * 
     * @param type $status
     * @return type
     * @throws \Exception
     */
    function getOrdersByStatus() {
        $p = $this->database->query('SELECT * FROM w_orders ORDER BY status ASC')->fetchAll();
        if(!$p){
            throw new \Exception('Orders not found');
        }
        return $p;
    }
    /**
     * 
     * @param type $id
     * @return type
     * @throws \Exception
     */
    function getOrder($id) {
        $p = $this->database->query('SELECT * FROM w_orders WHERE id=?',$id)->fetch();
          
        if(!$p){
            throw new \Exception('Order not found');
        }
        $p->data = unserialize($p->data);
        
        return $p;
    }
    /**
     * 
     * @param type $id
     * @return type
     * @throws \Exception
     */
    function getCustomer($id) {
        $p = $this->database->query('SELECT * FROM w_customers WHERE id=?',$id)->fetch();
          
        if(!$p){
            throw new \Exception('Customer not found');
        }
        
        return $p;
    }
    
    /**
     * 
     * @return type
     * @throws \Exception
     * 
     */
    function getOrdersCount() {
        $p = $this->database->table('orders')->count('*');
          
        if(!$p){
            throw new \Exception('Order not found');
        }
        
        return $p;    
    }

    /**
     * 
     * @param type $ids
     * @param type $status
     */
    function updateStatus($ids,$status) {
        $i=0;
        //$ids = explode(',', $ids);
        foreach ($ids as $id) {
            $this->database->query('UPDATE orders SET status=? WHERE id=?',$status[$i],$ids[$i]);
            $i++;
        }
    }
    
    /**
     * 
     * @param type $data
     * @param type $price
     * @param type $payment
     * @param type $transport
     * @param type $note
     * @param type $customers_id
     * @param type $status
     * @return type
     */
    function createOrder($data, $price, $payment, $transport, $note, $customers_id, $status = 1) {
                
        try{
            $this->database->table('order_email_sent')->insert(['id' => '']);
            $orderEmailId = $this->database->query('SELECT id FROM order_email_sent ORDER BY id DESC LIMIT 1')->fetch();
            
            $this->database->table('orders')->insert([
                'data' => serialize($data),
                'total_price' => $price,
                'created_at' => new \DateTime(),
                'payment' => $payment,
                'transport' => $transport,
                'status' => $status,
                'note' => $note,
                'customers_id' => $customers_id,
                'order_email_sent_id' => $orderEmailId->id    
             ]);
            $id = $this->database->query('SELECT id FROM orders ORDER BY id DESC LIMIT 1')->fetch();
            
            foreach($data as $item){
                $this->database->query('UPDATE w_products_stock SET actual_quantity = actual_quantity - ? WHERE id=?',$item['quantity'],$item['id']);
                
                $this->itemModel->productManagerModel->logProductStock($item['id'],"-" . $item['quantity'], "objednávka: " . $id->id);
            }

            return $id->id;
        }
        catch(\Exception $e)
        {

        }
    }
    /**
     * 
     * @param type $id
     * @throws \Exception
     */
    function setOrderPaid($id) {
        $p = $this->database->query('UPDATE orders SET payment_status=? WHERE id=?','paid',$id);  
        
        if(!$p){
            throw new \Exception('Order not found');
        }
        
    }
    
    /**
     * 
     * @param type $id
     * @throws \Exception
     */
    function setOrderCompleted($id,$value) {
        $p = $this->database->query('UPDATE orders SET completed=? WHERE id=?',$value,$id);  
        
        if(!$p){
            throw new \Exception('Order not found');
        }
        
    }
    
    /**
     * 
     * @param type $id
     * @return type
     */
    public function hasInvoice($id)
    {
        $invoice = $this->db->query('SELECT id FROM invoice WHERE order_id = ?', $id)->fetch();

        return !$invoice ? false : true;
    }
    
    /**
     * 
     * @param type $id
     */
    public function deleteOrder($id) {
        try{
            $oId = $this->database->query('SELECT order_email_sent_id FROM orders WHERE id = ?', $id)->fetch();
        
            $this->database->query('DELETE FROM order_email_sent WHERE id=?',$oId->order_email_sent_id);
            $this->database->query('DELETE FROM orders WHERE id=?',$id);
        }
        catch(\Exception $e)
        {
            
        }
        
    }
}
