<?php
/**
 * Created by PhpStorm.
 * User: yihua
 * Date: 2018/12/4
 * Time: 下午 1:58
 */

namespace TsaiYiHua\EZPay\Collections;


use Illuminate\Support\Collection;
use TsaiYiHua\EZPay\Exceptions\EZPayException;
use TsaiYiHua\EZPay\Services\StringService;

class MpgResponseCollection extends Collection
{
    protected $status;
    protected $message;
    protected $merchantId;
    protected $tradeInfo;
    protected $tradeSha;

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
        $this->tradeInfo = $response->TradeInfo;
        $this->tradeSha = $response->TradeSha;
        $res = StringService::parseResponse($response->TradeInfo);
        $this->message = $res->Message;
        $allParams = collect(array_merge(
            self::resultBasicParams(),
            self::accountLinkParams(),
            self::creditParams(),
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
            'MerchantID', 'Amt', 'TradeNo', 'MerchantOrderNo', 'PaymentType',
            'PayTime', 'IP', 'EscrowBank', 'ExpireDate', 'ExpireTime',
            'BuyerAmt', 'BuyerFee', 'IP'
        ];
    }

    static public function accountLinkParams()
    {
        return [
            'AccLinkBank', 'AccLinkNo'
        ];
    }

    static public function creditParams()
    {
        return [
            'RespondCode', 'Auth', 'AuthDate', 'AuthTime', 'AuthBank', 'Card6No', 'Card4No',
            'Exp', 'Inst', 'InstFirst', 'InstEach', 'ECI', 'RedAmt'
        ];
    }

    static public function ATMParams()
    {
        return [
            'BankCode', 'CodeNo', 'PayBankCode', 'PayerAccount5Code'
        ];
    }

    static public function CVSParams()
    {
        //PayStore 的值
        //IBON = 7-11
        //FAMILY = 全家
        //OK = OK 超商
        //HILIFE = 萊爾富超商
        return [
            'PayStore', 'CodeNo'
        ];
    }
}