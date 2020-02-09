<?php

namespace App\EshopModule\Model;

use Nette;
use Nette\Mail\Message;
use Nette\Mail\SendmailMailer;

class OrderEmailModel extends \App\EshopModule\Model\ManagerModel {
    
    public function __construct(Nette\Database\Context $database) {
        parent::__construct($database);
    }
    
    /**
     * 
     * @return type
     * @throws \Exception
     */
    public function getEmails() {
        $p = $this->database->query('SELECT * FROM order_email')->fetchAll();
        if(!$p){
            throw new \Exception('Order Emails not found');
        }
        return $p;
    }
    
    /**
     * 
     * @param type $id
     * @return type
     * @throws \Exception
     */
    public function getEmail($id) {
        $p = $this->database->query('SELECT * FROM order_email WHERE id=?',$id)->fetch();
        if(!$p){
            throw new \Exception('Order Emails not found');
        }
        return $p;
    }
    
    /**
     * 
     * @param type $type
     * @return type
     * @throws \Exception
     */
    public function getEmailByType($type) {
        $p = $this->database->query('SELECT * FROM order_email WHERE type=?',$type)->fetch();
        if(!$p){
            return $p;
        }
        return $p;
    }
    
    /**
     * 
     * @param type $id
     * @return type
     * @throws \Exception
     */
    public function getOrderSentEmails($id) {
        $p = $this->database->query('SELECT * FROM order_email_sent WHERE id=?',$id)->fetch();
        if(!$p){
            throw new \Exception('Order Emails not found');
        }
        return $p;
    }
    
    public function getEmailSetting() {
        $p = $this->database->query('SELECT * FROM order_email_setting WHERE id=1')->fetch();
        if(!$p){
            throw new \Exception('Order email setting not found');
        }
        return $p;
    }
    
    /**
     * 
     * @return int
     * @throws \Exception
     */
    function getEmailCount() : int{
        $p = $this->database->table('order_email')->count('*');
          
        if(!$p){
            throw new \Exception('Email not found');
        }
        return $p;    
    }
    
    /**
     * 
     * @param type $subject
     * @param type $content
     * @param type $type
     * @return type
     */
    public function createEmail($subject,$content,$type) {
         try{           
            $this->database->table('order_email')->insert([
                'subject' => $subject,
                'content' => $content,
                'type' => $type,   
             ]);
            $id = $this->database->query('SELECT id FROM order_email ORDER BY id DESC LIMIT 1')->fetch();
            
            $this->database->query('ALTER TABLE order_email_sent ADD COLUMN sent_? TinyInt(1) DEFAULT 0',$id->id);
            
            return $id->id;
        }
        catch(\Exception $e)
        {

        }
    }
    
    /**
     * 
     * @param type $id
     * @param type $subject
     * @param type $content
     * @param type $type
     */
    public function updateEmail($id,$subject,$content,$type) {
        try{
            $this->database->table('order_email')->where('id',$id)->update([
                'subject' => $subject,
                'content' => $content,
                'type' => $type,
             ]);
        }
        catch(\Exception $e)
        {

        }
    }
    
    
    public function updateEmailSetting($id,$host,$username,$password,$secure,$name){
        try{
            $this->database->table('order_email_setting')->where('id',$id)->update([
                'host' => $host,
                'username' => $username,
                'password' => $password,
                'secure' => $secure,
                'name' => $name,
             ]);
        }
        catch(\Exception $e)
        {

        }
    }
    
    /**
     * 
     * @param type $ids
     * @param type $sends
     */
    public function updateEmailSending($ids,$sends) {
        $i=0;
        foreach ($ids as $id) {
            $this->database->query('UPDATE order_email SET '
                    . 'sending=? WHERE id=?',$sends[$i],$ids[$i]);
            $i++;
        }
    }
    
    /**
     * 
     * @param type $id
     * @return type
     */
    public function deleteEmail($id){
        try{           
            if($this->getEmailCount() == 1){
                return -1;
            }
            $this->database->query('ALTER TABLE order_email_sent DROP COLUMN sent_?',(int)$id);
            $this->database->query('DELETE FROM order_email WHERE id=?',(int)$id);
            
            
            
        }
        catch(\Exception $e)
        {

        }
    }
    
   
    /**
     * 
     * @param type $orderData
     * @param type $type
     * @return type
     */
    public function replaceEmailData($orderData,$type) {
        
        $sentId = $this->database->query('SELECT order_email_sent_id FROM orders WHERE id=?',$orderData->id)->fetch();
        $orderData->order_email_sent_id = $sentId->order_email_sent_id;
        
        $message = $this->getEmailByType($type);
        if(!$message){
            return NULL;
        }
        
        if(!$this->isEmailSendable($orderData, $message->id)){
            return NULL;
        }
        
        $items = "<table><tr><th>Název položky&emsp;</th>"
                . "<th>Množství&emsp;</th>"
                . "<th>Cena/kus&emsp;</th></tr>";
                
        foreach($orderData->data as $item => $value){
            $items .= "<tr><td>" . $value['title']. "</td>" 
                    . "<td>" . $value['quantity'] . "</td>"
                    . "<td>" .$value['price']. "</td></tr>"; 
        }
        $items .= "</table>";
        
        $message->subject = str_replace('[order_id]',$orderData->id,$message->subject);
        $message->content = str_replace('[items]',$items,$message->content);
        $message->content = str_replace('[order_id]',$orderData->id,$message->content);
        $message->content = str_replace('[price]',$orderData->total_price,$message->content);
        $message->to = $orderData->email;
        $message->sentId = $orderData->order_email_sent_id;
        $this->setEmailSent($message->sentId, $message->id);
        return $message;
    }
    
    public function sendEmail($message) {
        try{
            //$this->setEmailSent($message->sentId, $message->id);
            $setting = $this->getEmailSetting();
            $mail = new Message;
            $mail->setFrom( $setting->name . '<' . $setting->username . '>')
                    ->addTo($message->to)
                    ->setSubject($message->subject)
                    ->setBody($message->content);

            $mailer = new \Nette\Mail\SmtpMailer;
            $mailer->send($mail);
            
        }
        catch(\Exception $e){
            
        }
    }
    
    public function isEmailSendable($order,$emailId) : bool{
        $sentEmails = $this->database->query('SELECT sent_? FROM order_email_sent WHERE id=?',(int)$emailId,(int)$order->order_email_sent_id)->fetch();
        $email = $this->database->query('SELECT sending FROM order_email WHERE id=?',$emailId)->fetch();
        $sent = "sent_" . $emailId;

        if($sentEmails->{$sent} == 0 && $email->sending == 1){
            return true;
        }else{
            return false;
        }
    }
    
    private function setEmailSent($sentId, $emailId) {
        try{
            $this->database->table('order_email_sent')->where('id',$sentId)->update([
                'sent_' . $emailId => 1,
             ]);
        }
        catch(\Exception $e)
        {

        }
    }
    
}
