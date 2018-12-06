<?php
namespace TsaiYiHua\EZPay;

use TsaiYiHua\EZPay\Collections\QueryPostCollection;

class Query
{
    use EZPayTrait;

    protected $apiUrl;
    protected $postData;
    protected $queryPostCollection;

    public function __construct(QueryPostCollection $queryPostCollection)
    {
        if (config('app.env') == 'production') {
            $this->apiUrl = 'https://payment.ezpay.com.tw/API/merchant_trade/query_trade_info';
        } else {
            $this->apiUrl = 'https://cpayment.ezpay.com.tw/API/merchant_trade/query_trade_info';
        }
        $this->queryPostCollection = $queryPostCollection;
    }

    /**
     * @param $tradeNo
     * @param $orderId
     * @return mixed
     * @throws Exceptions\EZPayException
     */
    public function queryInfo($tradeNo)
    {
        $this->queryPostCollection->setQueryParams($tradeNo)->setQueryInfo()->setQuerySha();
        return $this->composeQueryPostData()->query();
    }

    /**
     * Set Post Data for EZPay API
     * @return $this
     */
    public function composeQueryPostData()
    {
        $this->postData = [
            'MerchantID' => $this->queryPostCollection->merchantId,
            'Version'    => $this->queryPostCollection->version,
            'QueryInfo'  => $this->queryPostCollection->queryInfo,
            'QuerySha'   => $this->queryPostCollection->querySha
        ];
        return $this;
    }
}