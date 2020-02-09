<?php

namespace EshopModule;

use Nette;
use App\EshopModule\Model;
use App\EshopModule\Components\Forms;
use Nette\Application\UI\Form;
use Nette\Utils\Json;
use Latte;

class InvoicePresenter extends BasePresenter
{
    /** @var Model\InvoiceModel @inject */
    public $invoiceModel;
    
    /** @var Forms\InvoiceSettingsForm @inject */
    public $invoiceSettingsForm;
    
    
    public function createComponentInvoiceSettingsForm() {
         if (!$this->getUser()->isLoggedIn() || $this->admin->role != 'supervisor') {
            $this->error('K akci je nutné přihlášení.');
        }        
        
        $form = $this->invoiceSettingsForm->create();
        
        $bank = $this->invoiceModel->getBank();
        $supplier = $this->invoiceModel->getSupplier();
        
        $form->setDefaults([
            'name' => $bank->bankName,
            'account_number' => $bank->accountNumber,
            'full_name' => $supplier->fullName,
            'city' => $supplier->city,
            'street_and_number' => $supplier->streetAndNumber,
            'zip' => $supplier->zip,
            'i_number' => $supplier->i_number,
        ]);
        
        $form->onSuccess[] = function(Form $form, $values) {
            try {
                $this->invoiceModel->changeBank($values->name, $values->account_number);
                $this->invoiceModel->changeSupplier(
                        $values->full_name, 
                        $values->street_and_number, 
                        $values->city, $values->zip, 
                        $values->i_number);
                
            } catch (\Exception $e) {

            }
        };
        
        return $form;            
    }
    
    public function actionSavePdf($id)
    {
        if (!$this->getUser()->isLoggedIn() || $this->admin->role != 'supervisor') {
            $this->error('K akci je nutné přihlášení.');
        }       
        
        $success = true;
        //$this->error(__DIR__ .'\..\templates\invoice.latte');
        try
        {
            
            require_once __DIR__ . '\..\..\..\vendor\autoload.php';

            $latte = new Latte\Engine();
            
            $invoice = $this->invoiceModel->getByOrderID($id);
            $filePath = 'Faktura-' . $invoice->number . '.pdf';
            //$this->error($filePath);
            //$this->tmpFileModel->insert($filePath);
            
            $mpdf = new \Mpdf\Mpdf();
            //$mpdf->WriteHTML('<h1>Hello world!</h1>');
            //$mpdf->Output();
            $mpdf->WriteHTML($latte->renderToString(__DIR__ .'\..\templates\invoice.latte', ['invoice' => $invoice]));
            $mpdf->Output();
           // $mpdf->Output();
        }
        catch (\Exception $e)
        {
            $success = false;
        }

        $this->sendJson(['success' => $success]);
    }
}