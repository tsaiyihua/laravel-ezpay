<?php
namespace TsaiYiHua\EZPay\Collections;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use TsaiYiHua\EZPay\Exceptions\EZPayException;
use TsaiYiHua\EZPay\Services\StringService;

class QueryPostCollection extends Collection
{
    public $merchantId;
    public $version;
    public $queryInfo;
    public $querySha;

    public function __construct()
    {
        parent::__construct();
        $this->merchantId = config('ezpay.mpg.MerchantId');
        $this->version = '1.0';
    }

    public function setQueryParams($tradeNo)
    {
        $this->put('MerchantID' , $this->merchantId);
        $this->put('TimeStamp', Carbon::now()->timestamp);
        $this->put('Version' , $this->version);
        $this->put('TradeNo', $tradeNo);
        return $this;
    }

    /**
     * @return $this
     * @throws EZPayException
     */
    public function setQueryInfo()
    {
        $this->queryInfo = StringService::createAesEncrypt($this);
        return $this;
    }

    /**
     * @return $this
     * @throws EZPayException
     */
    public function setQuerySha()
    {
        $this->querySha = StringService::createShaEncrypt($this->queryInfo);
        return $this;
    }
}