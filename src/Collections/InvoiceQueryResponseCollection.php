<?php
namespace TsaiYiHua\EZPay\Collections;

use Illuminate\Support\Collection;
use TsaiYiHua\EZPay\Exceptions\EZPayException;

class InvoiceQueryResponseCollection extends Collection
{
    protected $status;
    protected $message;
    protected $merchantId;

    /**
     * @param $response
     * @return $this
     * @throws EZPayException
     */
    public function collectResponse($response)
    {
        if (!isset($response->Status)) {
            throw new EZPayException('Error Response type');
        }
        $this->status = $response->Status;
        $this->message = $response->Message;
        $res = json_decode($response->Result);
        $this->merchantId = $res->MerchantID;
        $allParams = collect(self::resultBasicParams())->unique();
        $allParams->each(function($param) use($res) {
            if (isset($res->$param)) {
                $this->put($param, $res->$param);
            }
        });
        return $this;
    }

    static public function resultBasicParams()
    {
        return [
            'MerchantID', 'InvoiceTransNo', 'MerchantOrderNo', 'InvoiceNumber',
            'RandomNum', 'BuyerName', 'BuyerUBN', 'BuyerAddress', 'BuyerPhone',
            'BuyerEmail', 'InvoiceType', 'Category', 'TaxType', 'TaxRate', 'Amt',
            'AmtSales', 'AmtZero', 'AmtFree', 'TaxAmt', 'TotalAmt', 'CarrierType',
            'CarrierNum', 'LoveCode', 'PrintFlag', 'ItemDetail', 'InvoiceStatus',
            'UploadStatus', 'CreateTime', 'CheckCode'
        ];
    }
}