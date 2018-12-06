<?php
namespace TsaiYiHua\EZPay\Validations;

use Illuminate\Support\Facades\Validator;

class MpgValidation
{
    static public function validator($data)
    {
        $availableInstallment = ['3', '6', '12', '18', '24'];
        $validator = Validator::make($data, [
            'orderId' => 'alpha_num|max:20',
            'itemName' => 'required|max:50',
            'amount' => 'required|integer',
            'LangType' => 'in:en,zh-tw',
            'TradeLimit' => 'integer|max:900',
            'CustomerURL' => 'url|max:50',
            'ClientBackURL' => 'url|max:50',
            'P2GEACC' => 'integer|in:0,1',
            'ACCLINK' => 'integer|in:0,1',
            'CREDIT' => 'integer|in:0,1',
            'InstFlag' => ['in:0,1', function ($attribute, $value, $fail) use ($availableInstallment) {
                $buf = explode(',', $value);
                $ok = true;
                for($i=0; $i<sizeof($buf); $i++) {
                    if (!in_array($buf[$i], $availableInstallment)) {
                        $ok = false;
                    }
                }
                if ($ok === false) {
                    $fail($attribute.' value must be composed by 3, 6, 12, 18, 24');
                }
            }],
            'CreditRed' => 'integer|in:0,1',
            'WEBATM' => 'integer|in:0,1',
            'VACC' => 'integer|in:0,1',
            'CVS' => 'integer|in:0,1',
            'ExpireDate' => 'date_format:Ymd',
            'ExpireTime' => 'date_format:His',
            'NotifyURL' => 'url',
            'ReturnURL' => 'url'
        ]);
        return $validator;
    }
}