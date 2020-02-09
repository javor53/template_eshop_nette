<?php

namespace App\EshopModule\Model;

use Nette;
use Nette\Application\UI\Form;
use App\EshopModule\Model;
use Nette\Utils\Json;

class InvoiceModel extends ManagerModel
{
    /** @var Model\OrderManagerModel @inject */
    public $orderManagerModel;
    
    /** @var Model\ShippingInfoModel @inject */
    public $shippingInfoModel;

    public function __construct(Nette\Database\Context $database, OrderManagerModel $orderManagerModel, ShippingInfoModel $shippingInfoModel)
    {
        parent::__construct($database);
        $this->orderManagerModel = $orderManagerModel;
        $this->shippingInfoModel = $shippingInfoModel;
    }

    /**
     * @return bool|Nette\Database\IRow|Nette\Database\Row
     * @throws \Exception
     */
    public function getSupplier()
    {
        $supplier = $this->database->query('
            SELECT
                id, full_name AS fullName, city, street_and_number AS streetAndNumber, zip, i_number
            FROM invoice_supplier
            LIMIT 1
        ')->fetch();

        if (!$supplier)
            throw new \Exception('supplier not found');

        return $supplier;
    }

    public function changeSupplier($fullName, $streetAndNumber, $city, $zip, $in)
    {
        $this->database->query('
            UPDATE invoice_supplier
            SET full_name = ?, city = ?, street_and_number = ?, zip = ?, i_number = ?
        ', $fullName, $city, $streetAndNumber, $zip, $in);
    }

    /**
     * @return bool|Nette\Database\IRow|Nette\Database\Row
     * @throws \Exception
     */
    public function getBank()
    {
        $bank = $this->database->query('SELECT id, name AS bankName, account_number AS accountNumber FROM invoice_bank LIMIT 1')->fetch();

        if (!$bank)
            throw new \Exception('bank not found');

        return $bank;
    }

    public function changeBank($bankName, $accountNumber)
    {
        $this->database->query('
            UPDATE invoice_bank
            SET name = ?, account_number = ?
        ', $bankName, $accountNumber);
    }

    public function deleteByOrderID($id)
    {
        $this->database->query('DELETE FROM invoice WHERE order_id = ?', $id);
    }

    public function saveInvoice($invoice)
    {
        $this->database->table('invoice')->insert([
            'order_id' => $invoice->orderID,
            'number' => $invoice->number,
            'supplier' => Json::encode($invoice->supplier),
            'subscriber' => Json::encode($invoice->subscriber),
            'created_at' => $invoice->createdAt,
            'due_date' => $invoice->dueDate,
            'payment' => Json::encode($invoice->payment),
            'total_price' => $invoice->totalPrice,
            'employee' => Json::encode($invoice->employee),
            'products' => Json::encode($invoice->products)
        ]);
    }

    public function createInvoiceByOrderID($id)
    {
        try
        {

            $order = $this->orderManagerModel->getOrder($id);
            $customer = $this->orderManagerModel->getCustomer($order->customers_id);
            $bank = $this->getBank();
            $supplier = $this->getSupplier();
            $invoice = new \stdClass();

            $invoice->orderID = $order->id;
            $invoice->number = $order->id;

            $now = new Nette\Utils\DateTime();
            $invoice->createdAt = $now->format('d.m.Y');
            $invoice->dueDate = $now->add(new \DateInterval('P14D'))->format('d.m.Y');

            $invoice->supplier = (object) [
                'name' => $supplier->fullName,
                'city' => $supplier->city,
                'streetAndNumber' => $supplier->streetAndNumber,
                'zip' => $supplier->zip,
                'in' => $supplier->i_number
            ];

            $invoice->subscriber = (object) [
                'name' => $order->full_name,
                'city' => $customer->city,
                'streetAndNumber' => $customer->street . ' ' . $customer->street_number,
                'zip' => $customer->zip,
                //'hasCompany' => $order->companyID != null && $order->companyName != null && $order->companyIN != null && $order->companyTIN != null
                'hasCompany' => FALSE
            ];

            
            if ($invoice->subscriber->hasCompany)
            {
                $invoice->subscriber->company = (object) [
                    'name' => $order->companyName,
                    'in' => $order->companyIN,
                    'tin' => $order->companyTIN
                ];
            }

            $invoice->payment = (object) [
                'type' => $this->shippingInfoModel->configPayment[$order->payment],
                'isAccountTransfer' => $order->payment == 'transfer-to-account'
            ];

            if ($invoice->payment->isAccountTransfer)
            {
                $invoice->payment->bank = (object) [
                    'name' => $bank->bankName,
                    'accountNumber' => $bank->accountNumber,
                    'variableSymbol' => $invoice->number
                ];
            }

            $invoice->totalPrice = $order->total_price;
            $invoice->products = [];

            foreach ($order->data as $product => $value)
            {
                $invoice->products[] = (object) [
                    'name' => $value['title'],
                    'price' => $value['price'],
                    'count' => $value['quantity']
                ];
            }

            $invoice->employee = (object) [
                'name' => 'Jmeno vystavovatele',
                'email' => 'email',
                'phone' => '45454545'
            ];

            return $invoice;
        }
        catch (\Exception $e)
        {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * @param $id
     * @return bool|Nette\Database\IRow|Nette\Database\Row
     * @throws Nette\Utils\JsonException
     * @throws \Exception
     */
    public function getByOrderID($id)
    {
        $invoice = $this->database->query('
            SELECT
                id, order_id AS orderID, `number`, supplier, subscriber,
                created_at AS createdAt, due_date AS dueDate, payment,
                products, total_price AS totalPrice, employee
            FROM invoice
            WHERE order_id = ?
        ', $id)->fetch();

        if (!$invoice)
            throw new \Exception('invoice not found');

        $invoice->supplier = Json::decode($invoice->supplier);
        $invoice->subscriber = Json::decode($invoice->subscriber);
        $invoice->payment = Json::decode($invoice->payment);
        $invoice->products = Json::decode($invoice->products);
        $invoice->employee = Json::decode($invoice->employee);

        return $invoice;
    }

    /**
     * @param $id
     * @return bool|Nette\Database\IRow|Nette\Database\Row
     * @throws \Exception
     */
    public function getByID($id)
    {
        $order = $this->database->query('SELECT order_id FROM invoice WHERE id = ?', $id)->fetch();

        if (!$order)
            throw new \Exception('order-not-found');

        return $this->getByOrderID($order->order_id);
    }
    
    /**
     * 
     * @param type $id
     * @return boolean
     * 
     */
    public function saveInvoicePdf($id)
    {
        $success = true;

        try
        {
            
            require_once __DIR__ . '\..\..\..\vendor\autoload.php';

            $latte = new Latte\Engine();
            
            $invoice = $this->invoiceModel->getByOrderID($id);
            //$filePath = '\..\..\..\www\pdf\tmp_invoices\Faktura-' . $invoice->number . '.pdf';
            $fileName = 'Faktura-' . $invoice->number . '.pdf';
            //$this->error($filePath);
            //$this->tmpFileModel->insert($filePath);
            
            $mpdf = new \Mpdf\Mpdf();
            //$mpdf->WriteHTML('<h1>Hello world!</h1>');
            //$mpdf->Output();
            $mpdf->WriteHTML($latte->renderToString(__DIR__ .'\..\templates\invoice.latte', ['invoice' => $invoice]));
            $mpdf->Output($fileName, 'D');
            
        }
        catch (\Exception $e)
        {
            $success = false;
        }

        return $success;
    }
    
    
    
}