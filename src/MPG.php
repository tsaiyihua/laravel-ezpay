<?php
namespace TsaiYiHua\EZPay;

use TsaiYiHua\EZPay\Collections\MpgPostCollection;
use TsaiYiHua\EZPay\Exceptions\EZPayException;
use TsaiYiHua\EZPay\Services\StringService;
use TsaiYiHua\EZPay\Validations\MpgValidation;

class MPG
{
    use EZPayTrait;

    protected $apiUrl;
    protected $mpgPostCollection;
    protected $postData;

    public function __construct(MpgPostCollection $mpgPostCollection)
    {
        if (config('app.env') == 'production') {
            $this->apiUrl = 'https://payment.ezpay.com.tw/MPG/mpg_gateway';
        } else {
            $this->apiUrl = 'https://cpayment.ezpay.com.tw/MPG/mpg_gateway';
        }
        $this->mpgPostCollection = $mpgPostCollection;
    }

    /**
     * @param $attributes
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @throws EZPayException
     */
    public function createOrder($attributes)
    {
        $attributes['orderId'] = $attributes['orderId'] ?? StringService::identifyNumberGenerator('O');
        $attributes['NotifyURL'] = $attributes['NotifyURL'] ?? route('ezpay.notify');
        $attributes['ReturnURL'] = $attributes['ReturnURL'] ?? route('ezpay.return');
        $attributes['CustomerURL'] = $attributes['CustomerURL'] ?? route('ezpay.customer');
        $validator = MpgValidation::validator($attributes);
        if ($validator->fails()) {
            throw new EZPayException($validator->getMessageBag());
        }
        $this->mpgPostCollection->setBasicInfo()->setOptionalInfo($attributes)->setOrder($attributes);
        $this->mpgPostCollection->setTradeInfo()->setTradeSha();
        return $this->composeMpgPostData()->send();
    }

    /**
     * Set Post Data for EZPay API
     * @return $this
     */
    public function composeMpgPostData()
    {
        $this->postData = [
            'MerchantID' => $this->mpgPostCollection->merchantId,
            'Version'    => $this->mpgPostCollection->version,
            'TradeInfo'  => $this->mpgPostCollection->tradeInfo,
            'TradeSha'   => $this->mpgPostCollection->tradeSha
        ];
        return $this;
    }
}