<?php
namespace TsaiYiHua\EZPay\Collections;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use TsaiYiHua\EZPay\Exceptions\EZPayException;
use TsaiYiHua\EZPay\Services\StringService;

class MpgPostCollection extends Collection
{
    public $merchantId;
    public $version;
    public $tradeInfo;
    public $tradeSha;

    public function __construct()
    {
        parent::__construct();
        $this->merchantId = config('ezpay.mpg.MerchantId');
        $this->version = '1.0';
    }

    public function setBasicInfo()
    {
        $this->put('MerchantID' , $this->merchantId);
        $this->put('TimeStamp', Carbon::now()->timestamp);
        $this->put('Version' , $this->version);
        return $this;
    }

    public function setOrder($attributes)
    {
        $this->put('MerchantOrderNo', $attributes['orderId']);
        $this->put('Amt', $attributes['amount']);
        $this->put('ItemDesc', $attributes['itemName']);
        return $this;
    }

    public function setOptionalInfo($attributes)
    {
        $optionalParams = [
            'LangType', 'TradeLimit', 'CustomerURL', 'ClientBackURL', 'P2GEACC', 'ACCLINK',
            'CREDIT', 'InstFlag', 'CreditRed', 'WEBATM', 'VACC', 'CVS',
            'ExpireDate', 'ExpireTime', 'NotifyURL', 'ReturnURL'
        ];
        foreach($optionalParams as $param) {
            if (isset($attributes[$param])) {
                $this->put($param, $attributes[$param]);
            }
        }
        return $this;
    }

    /**
     * @return $this
     * @throws EZPayException
     */
    public function setTradeInfo()
    {
        $this->tradeInfo = StringService::createAesEncrypt($this);
        return $this;
    }

    /**
     * @return $this
     * @throws EZPayException
     */
    public function setTradeSha()
    {
        $this->tradeSha = StringService::createShaEncrypt($this->tradeInfo);
        return $this;
    }
}