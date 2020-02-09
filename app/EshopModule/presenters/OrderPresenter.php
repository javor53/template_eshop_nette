<?php

namespace EshopModule;

use Nette;
use App\EshopModule\Model;
use Nette\Application\UI\Multiplier;
use App\EshopModule\Components\Forms;
use Nette\Application\UI\Form;
use Nette\Utils\Arrays;

class OrderPresenter extends BasePresenter
{
    
    /** @var Model\OrderManagerModel @inject */
    public $orderManagerModel;
    
    /** @var Model\OrderEmailModel @inject */
    public $orderEmailModel;
    
    /** @var Model\ShippingInfoModel @inject */
    public $shippingInfoModel;
    
    /** @var Model\CustomerModel @inject */
    public $customerModel;
    
    /** @var Model\ProductModels\ProductCategoryModel @inject */
    public $productCategoryModel;
    /*
    protected $configStatus = [
            '1' => 'Nová',
            '2' => 'Zpracovává se',
            '3' => 'Expedovaná',
            '4' => 'Doručená',
            '5' => 'Zrušená'
            ];

    protected $configPayment = [
            'transfer-to-account' => 'Převodem na účet',
            'cash-on-delivery' => 'Dobírka',
        ];
    protected $configTransport = [
            'ppl' => 'PPL',
            'cz-post' => 'Česká pošta',
        ];*/
    
    public function handleSaveOrderStatus(){
        $orders = $this->orderManagerModel->getOrders();
        $this->template->orders = $orders;
        foreach ($orders as $order) {
           // $this['orderStatus']->setDefaults(array('selStatus' => $order->status));
        }
        
        
        if ($this->isAjax()) {
            $this->redrawControl('orders');
        }else {
            $this->redirect('this');
        }
    }
    
    public function handleFilterStatus(){
        
        $orders = $this->orderManagerModel->getOrdersByStatus();
        $this->template->orders = $orders;
        $i = 0;
        foreach ($orders as $order) {
            $this["orderStatus"]['order'][$i]->setDefaults(array('selStatus' => $order->status)); 
            $i++;
        }
        
        $this->redrawControl('orders');
   
    }
    
    public function renderDefault($id){
        
        
        $this->template->configPayment = $this->shippingInfoModel->configPayment;
        $this->template->configTransport = $this->shippingInfoModel->configTransport;
        
        if(!isset($this->template->orderStatusFilter)){
            $this->template->orderStatusFilter = 0;
        }
        
        if(!isset($this->template->orders)){
            $orders = $this->orderManagerModel->getOrders();
            $this->template->orders = $orders;
            $i = 0;
            foreach ($orders as $order) {
                $this["orderStatus"]['order'][$i]->setDefaults(array('selStatus' => $order->status)); 
                $i++;
            }
        }

        
           
    } 
    
    public function renderDetail($id) {
        $order = $this->orderManagerModel->getOrder($id);
        $customer = $this->customerModel->getCustomerDetail($order->customers_id);
        
        $products = $order->data;
        $this->template->configPayment = $this->shippingInfoModel->configPayment;
        $this->template->configPaymentStatus = $this->shippingInfoModel->configPaymentStatus;
        $this->template->configStatus = $this->shippingInfoModel->configStatus;
        $this->template->configTransport = $this->shippingInfoModel->configTransport;
        $this->template->products = $products;
        $this->template->order = $order;    
        $this->template->customer = $customer; 
        
        
    }
    
    public function createComponentOrderStatus() {

        $numOfOrders = $this->orderManagerModel->getOrdersCount();
        
        $form = new Nette\Application\UI\Form;

        
        $form->addMultiplier('order', function (Nette\Forms\Container $container, Nette\Forms\Form $form) {
            $container->addSelect('selStatus','',$this->shippingInfoModel->configStatus);
            $container->addHidden('ids');
        },$numOfOrders);


        $form->addSubmit('submit', 'uložit');
        $form->onSuccess[] = function(Form $form, $values) {
            try
            {
                $values = $form->getValues();
                $i=0;
                foreach ($values['order'] as $value) {   
                    $status[$i] = $value['selStatus'];
                    $ids[$i] = $value['ids'];
                    $i++;
                }
                
                $this->orderManagerModel->updateStatus($ids,$status);
                $this->actionSendMail($ids);
                $this->handleSaveOrderStatus();
                
            }
            catch (Nette\Security\AuthenticationException $e) {
                $form->addError($e->getMessage());
            }
        };   
        
        return $form;
    }

    public function actionSetOrderPaid($id){
        $this->orderManagerModel->setOrderPaid($id);
        
        $this->flashMessage('Objednávka: ' . $id .' je zaplacena.', 'success');
        $this->redirect('Order:detail',array("id" => $id));
    }
    
    public function actionSetOrderCompleted($id,$value){
        
        $this->orderManagerModel->setOrderCompleted($id,$value);
        
        if($value == 1){
            $this->flashMessage('Objednávka: ' . $id .' je kompletní.', 'success');
            $this->redirect('Order:detail',array("id" => $id));
        }else{
            $this->flashMessage('Objednávka: ' . $id .' kompletace zrušena.', 'success');
            $this->redirect('Order:detail',array("id" => $id));
        }
    }
    
    public function actionSendMail($ids) {
        $i=0;
        $x=0;
        $unsend = "";
        foreach($ids as $id){
            $order = $this->orderManagerModel->getOrder($ids[$i]);

            $message = $this->orderEmailModel->replaceEmailData($order, $order->status);

            if($message == NULL){
                $x++;
                $unsend .= $ids[$i] . ",";
                $i++;
                continue;
            }
            //$mail = $this->mailer->createMessage([$order->email], $message->subject, $message->content);
            //$this->mailer->send($mail);
            //
            //$this->orderEmailModel->sendEmail($message);
            $i++;
        }
        if($x > 0){
            $this->flashMessage('Některé z emailů nebyly odeslány('. $unsend .')', 'success');
            $this->redirect('Order:');
        }elseif ($x == 0) {
            $this->flashMessage('Odeslání proběhlo v pořádku.', 'success');
            $this->redirect('Order:');
        }
    }
    
    public function actionDeleteOrder($id) {
        $order = $this->orderManagerModel->getOrder($id);
        if($order->status == 5){
            $this->orderManagerModel->deleteOrder($id);
            $this->flashMessage('Objednávka smazaná.', 'success');
            $this->redirect('Order:');
        }else{
            $this->flashMessage('Objednávku nelze smazat (lze mazat pouze zrušené objedn.).', 'fail');
            $this->redirect('Order:');
        }
    }
    
    
}
