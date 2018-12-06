<?php
namespace TsaiYiHua\EZPay\Collections;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use TsaiYiHua\EZPay\Exceptions\EZPayException;
use TsaiYiHua\EZPay\Services\StringService;

class InvoiceTouchIssuePostCollection extends Collection
{
    public $merchantId;
    public $postData;
    public $sendData;

    public function __construct()
    {
        parent::__construct();
        $this->merchantId = config('ezpay.invoice.MerchantId');
    }

    public function setBasicInfo($attributes)
    {
        $this->put('RespondType' , 'JSON');
        $this->put('Version', '1.0');
        $this->put('TimeStamp' , Carbon::now()->timestamp);
        $this->sendData = $attributes;
        return $this;
    }

    public function setInvoiceInfo()
    {
        $this->put('TransNum', $this->sendData['TransNum'] ?? null);
        $this->put('InvoiceTransNo', $this->sendData['invoiceTransNo']);
        $this->put('MerchantOrderNo', $this->sendData['orderId']);
        $this->put('TotalAmt', $this->sendData['amount']);
        return $this;
    }

    /**
     * @return $this
     * @throws EZPayException
     */
    public function setPostData()
    {
        $this->postData = StringService::createAesEncrypt($this,
            config('ezpay.invoice.HashKey'),
            config('ezpay.invoice.HashIV'));
        return $this;
    }
}