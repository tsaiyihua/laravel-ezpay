<?php
/**
 * Created by PhpStorm.
 * User: yihua
 * Date: 2018/12/7
 * Time: 下午 3:19
 */

namespace TsaiYiHua\EZPay\Collections;


use Carbon\Carbon;
use Illuminate\Support\Collection;
use TsaiYiHua\EZPay\Exceptions\EZPayException;
use TsaiYiHua\EZPay\Services\StringService;

class InvoiceQueryPostCollection extends Collection
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
        $this->put('Version', '1.1');
        $this->put('TimeStamp' , Carbon::now()->timestamp);
        $this->put('SearchType', $attributes['SearchType'] ?? 0);
        $this->sendData = $attributes;
        return $this;
    }

    public function setOrderInfo()
    {
        $this->put('MerchantOrderNo', $this->sendData['orderId']);
        $this->put('TotalAmt', $this->sendData['amount']);
        $this->put('InvoiceNumber', $this->sendData['invoiceNum']);
        $this->put('RandomNum', $this->sendData['randomNum']);
        return $this;
    }

    public function displayOnEZPay()
    {
        $this->put('DisplayFlag', 1);
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

    public function isDisplayOnEZPay()
    {
        if ($this->get('DisplayFlag') == 1) {
            return true;
        } else {
            return false;
        }

    }
}