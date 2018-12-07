<?php
/**
 * Created by PhpStorm.
 * User: yihua
 * Date: 2018/12/5
 * Time: 上午 11:16
 */

namespace TsaiYiHua\EZPay\Validations;


use Illuminate\Support\Facades\Validator;

class InvoiceValidation
{
    static public function validator($data)
    {
        $validator = Validator::make($data, [
            'orderId' => 'required|alpha_num|max:20',
            'items' => 'required|array',
            'TransNum' => 'max:20',
            'Status' => 'in:0,1,3',
            'CreateStatusTime' => 'required_if:Status,3|date_format:YYYY-MM-DD',
            'Category' => 'in:B2C,B2B',
            'BuyerName' => 'required|max:50',
            'BuyerUBN' => 'required_if:Category,B2B',
            'BuyerAddress' => 'max:100',
            'BuyerEmail' => 'required_if:CarrierType,2|email',
            'CarrierType' => 'max:2',
            'CarrierNum' => 'alpha_num|max:50',
            'LoveCode' => 'integer|min:100|max:9999999',
            'PrintFlag' => 'in:Y,N',
            'TaxType' => 'in:1,2,3,9',
            'TaxRate' => 'numeric',
            'CustomsClearance' => 'in:1,2',
            'Amt' => 'integer',
            'AmtSales' => 'integer',
            'AmtZero' => 'integer',
            'AmtFree' => 'integer',
            'TaxAmt' => 'integer',
            'Comment' => 'max:71'
        ]);
        return $validator;
    }

    static public function touchInssueValidator($data)
    {
        $validator = Validator::make($data, [
            'orderId' => 'required|alpha_num|max:20',
            'TransNum' => 'max:20',
            'amount' => 'required|integer',
            'invoiceTransNo' => 'required|max:20'
        ]);
        return $validator;
    }

    static public function queryValidator($data)
    {
        $validator = Validator::make($data, [
            'orderId' => 'required|alpha_num|max:20',
            'amount' => 'required|integer',
            'SearchType' => 'in:0,1',
            'invoiceNum' => 'required|max:10',
            'randomNum' => 'required|max:4',
            'DisplayFlag' => 'max:1'
        ]);
        return $validator;
    }

    static public function invalidValidator($data)
    {
        $validator = Validator::make($data, [
            'invoiceNum' => 'required|max:10',
            'reason' => 'required|max:20',
        ]);
        return $validator;
    }
}