<?php
/**
 * Created by PhpStorm.
 * User: yihua
 * Date: 2018/12/5
 * Time: 上午 10:08
 */

namespace TsaiYiHua\EZPay;


use TsaiYiHua\EZPay\Collections\InvoicePostCollection;
use TsaiYiHua\EZPay\Collections\InvoiceTouchIssuePostCollection;
use TsaiYiHua\EZPay\Exceptions\EZPayException;
use TsaiYiHua\EZPay\Validations\InvoiceValidation;

class Invoice
{
    use EZPayTrait;

    protected $apiUrl;
    protected $touchIssueApiUrl;
    protected $postData;
    protected $invPostCollection;
    protected $invTouchIssuePostCollection;

    public function __construct(
        InvoicePostCollection $invPostCollection,
        InvoiceTouchIssuePostCollection $invTouchIssuePostCollection
    )
    {
        if (config('app.env') == 'production') {
            $this->apiUrl = 'https://inv.ezpay.com.tw/Api/invoice_issue';
            $this->touchIssueApiUrl = 'https://inv.ezpay.com.tw/Api/invoice_touch_issue';
        } else {
            $this->apiUrl = 'https://cinv.ezpay.com.tw/Api/invoice_issue';
            $this->touchIssueApiUrl = 'https://cinv.ezpay.com.tw/Api/invoice_touch_issue';
        }
        $this->invPostCollection = $invPostCollection;
        $this->invTouchIssuePostCollection = $invTouchIssuePostCollection;
    }

    /**
     * @param $attributes
     * @throws EZPayException
     */
    public function issueInvoice($attributes)
    {
        $validator = InvoiceValidation::validator($attributes);
        if ($validator->fails()) {
            throw new EZPayException($validator->getMessageBag());
        }
        $this->invPostCollection->setBasicInfo($attributes)->setPurchaseInfo()
            ->setInvoiceInfo()->setTaxInfo()->setOptionalInfo();
        $this->invPostCollection->setPostData();
        $this->composePostData();
        return $this;
    }

    /**
     * @param $attributes
     * @return $this
     * @throws EZPayException
     */
    public function touchIssueInvoice($attributes)
    {
        $validator = InvoiceValidation::touchInssueValidator($attributes);
        if ($validator->fails()) {
            throw new EZPayException($validator->getMessageBag());
        }
        $this->apiUrl = $this->touchIssueApiUrl;
        $this->invTouchIssuePostCollection->setBasicInfo($attributes)->setInvoiceInfo()->setPostData();
        $this->composePostData();
        return $this;

    }
    /**
     * Set Post Data for EZPay API
     */
    public function composePostData()
    {
        $this->postData = [
            'MerchantID_' => $this->invPostCollection->merchantId,
            'PostData_'    => $this->invPostCollection->postData
        ];
    }
}