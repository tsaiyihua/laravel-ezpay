<?php
/**
 * Created by PhpStorm.
 * User: yihua
 * Date: 2018/12/5
 * Time: 下午 5:21
 */

namespace TsaiYiHua\EZPay\Collections;


use Illuminate\Support\Collection;
use TsaiYiHua\EZPay\Exceptions\EZPayException;

class InvoiceResponseCollection extends Collection
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
            'MerchantID', 'InvoiceTransNo', 'MerchantOrderNo', 'TotalAmt', 'InvoiceNumber',
            'RandomNum', 'CreateTime', 'CheckCode', 'BarCode', 'QRcodeL', 'QRcodeR'
        ];
    }
}