<?php
namespace TsaiYiHua\EZPay\Collections;

use Illuminate\Support\Collection;
use TsaiYiHua\EZPay\Exceptions\EZPayException;
use TsaiYiHua\EZPay\Services\StringService;

class QueryResponseCollection extends Collection
{
    protected $status;
    protected $message;
    protected $merchantId;
    protected $queryInfo;
    protected $querySha;

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
        $this->merchantId = $response->MerchantID;
        $this->queryInfo = $response->QueryInfo;
        $this->querySha = $response->QuerySha;
        $res = StringService::parseResponse($response->QueryInfo);
        $this->message = $res->Message;
        $allParams = collect(array_merge(
            self::resultBasicParams(),
            self::creditParams(),
            self::webATMParams(),
            self::ATMParams(),
            self::CVSParams()
        ))->unique();
        $allParams->each(function($param) use($res) {
            if (isset($res->Result->$param)) {
                $this->put($param, $res->Result->$param);
            }
        });
        return $this;
    }

    static public function resultBasicParams()
    {
        return [
            'MerID', 'MerMemID', 'MerName', 'BuyerMemID', 'BuyerName',
            'BuyerAccNo', 'BuyerEmail', 'TradeNo', 'MerchantOrderNo', 'Amt',
            'PaymentType', 'PaymentStatus', 'AmtStatus', 'CreateDT', 'PayDT',
            'FundDT'
        ];
    }

    static public function creditParams()
    {
        return [
            'RespondCode', 'Auth', 'ECI', 'CloseStatus', 'CloseAmt',
            'BackStatus', 'BackBalance', 'Inst', 'InstFirst', 'InstEach'
        ];
    }

    static public function webATMParams()
    {
        return [
            'PayBankCode', 'PayerAccount5Code'
        ];
    }

    static public function ATMParams()
    {
        return [
            'PayBankCode', 'PayerAccount5Code', 'CodeNo', 'ExpireDT'
        ];
    }

    static public function CVSParams()
    {
        return [
            'PayStore', 'CodeNo', 'ExpireDT'
        ];
    }
}