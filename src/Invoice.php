<?php
namespace TsaiYiHua\EZPay;

use TsaiYiHua\EZPay\Collections\InvoiceInvalidPostCollection;
use TsaiYiHua\EZPay\Collections\InvoicePostCollection;
use TsaiYiHua\EZPay\Collections\InvoiceQueryPostCollection;
use TsaiYiHua\EZPay\Collections\InvoiceTouchIssuePostCollection;
use TsaiYiHua\EZPay\Exceptions\EZPayException;
use TsaiYiHua\EZPay\Validations\InvoiceValidation;

class Invoice
{
    use EZPayTrait;

    const METHOD_INV_ISSUE = 1;
    const METHOD_INV_TOUCH_ISSUE = 2;
    const METHOD_INV_QUERY = 3;
    const METHOD_INV_INVALID = 4;

    protected $apiUrl;
    protected $touchIssueApiUrl;
    protected $queryInvoiceApiUrl;
    protected $invalidInvoiceApiUrl;

    protected $postData;
    protected $invPostCollection;
    protected $invTouchIssuePostCollection;
    protected $invQueryPostCollection;
    protected $invInvalidPostCollection;

    public function __construct(
        InvoicePostCollection $invPostCollection,
        InvoiceTouchIssuePostCollection $invTouchIssuePostCollection,
        InvoiceQueryPostCollection $invQueryPostCollection,
        InvoiceInvalidPostCollection $invInvalidPostCollection
    )
    {
        if (config('app.env') == 'production') {
            $this->apiUrl = 'https://inv.ezpay.com.tw/Api/invoice_issue';
            $this->touchIssueApiUrl = 'https://inv.ezpay.com.tw/Api/invoice_touch_issue';
            $this->queryInvoiceApiUrl = 'https://inv.ezpay.com.tw/Api/invoice_search';
            $this->invalidInvoiceApiUrl = 'https://inv.ezpay.com.tw/Api/invoice_invalid';
        } else {
            $this->apiUrl = 'https://cinv.ezpay.com.tw/Api/invoice_issue';
            $this->touchIssueApiUrl = 'https://cinv.ezpay.com.tw/Api/invoice_touch_issue';
            $this->queryInvoiceApiUrl = 'https://cinv.ezpay.com.tw/Api/invoice_search';
            $this->invalidInvoiceApiUrl = 'https://cinv.ezpay.com.tw/Api/invoice_invalid';
        }
        $this->invPostCollection = $invPostCollection;
        $this->invTouchIssuePostCollection = $invTouchIssuePostCollection;
        $this->invQueryPostCollection = $invQueryPostCollection;
        $this->invInvalidPostCollection = $invInvalidPostCollection;
    }

    /**
     * @param $attributes
     * @return $this
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
        $this->composePostData(self::METHOD_INV_TOUCH_ISSUE);
        return $this;
    }

    /**
     * @param $attributes
     * @return $this
     * @throws EZPayException
     */
    public function queryInvoice($attributes)
    {
        $validator = InvoiceValidation::queryValidator($attributes);
        if ($validator->fails()) {
            throw new EZPayException($validator->getMessageBag());
        }
        $this->apiUrl = $this->queryInvoiceApiUrl;
        $this->invQueryPostCollection->setBasicInfo($attributes)->setOrderInfo();
        if ($attributes['DisplayFlag'] == 1) {
            $this->invQueryPostCollection->displayOnEZPay();
        }
        $this->invQueryPostCollection->setPostData();
        $this->composePostData(self::METHOD_INV_QUERY);
        return $this;
    }

    /**
     * @param $attributes
     * @return $this
     * @throws EZPayException
     */
    public function invalidInvoice($attributes)
    {
        $validator = InvoiceValidation::invalidValidator($attributes);
        if ($validator->fails()) {
            throw new EZPayException($validator->getMessageBag());
        }
        $this->apiUrl = $this->queryInvoiceApiUrl;
        $this->invInvalidPostCollection->setBasicInfo($attributes)->setInvoiceInfo()->setPostData();
        $this->composePostData(self::METHOD_INV_INVALID);
        return $this;
    }

    /**
     * Set Post Data for EZPay API
     * @param int $type
     */
    public function composePostData($type=self::METHOD_INV_ISSUE)
    {
        if ($type == self::METHOD_INV_TOUCH_ISSUE) {
            $postData = $this->invTouchIssuePostCollection->postData;
        } else if ($type == self::METHOD_INV_QUERY) {
            $postData = $this->invQueryPostCollection->postData;
        } else if ($type == self::METHOD_INV_INVALID) {
            $postData = $this->invInvalidPostCollection->postData;
        } else {
            $postData = $this->invPostCollection->postData;
        }
        $this->postData = [
            'MerchantID_' => $this->invPostCollection->merchantId,
            'PostData_'    => $postData
        ];
    }
}