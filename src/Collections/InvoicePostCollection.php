<?php
/**
 * Created by PhpStorm.
 * User: yihua
 * Date: 2018/12/5
 * Time: 上午 10:24
 */

namespace TsaiYiHua\EZPay\Collections;


use Carbon\Carbon;
use Illuminate\Support\Collection;
use TsaiYiHua\EZPay\Exceptions\EZPayException;
use TsaiYiHua\EZPay\Services\StringService;

class InvoicePostCollection extends Collection
{
    public $merchantId;
    public $postData;
    public $sendData;

    public function __construct()
    {
        parent::__construct();
        $this->merchantId = config('ezpay.invoice.MerchantId');
    }

    public function setBasicInfo($attributes)
    {
        $this->put('RespondType' , 'JSON');
        $this->put('Version', '1.4');
        $this->put('TimeStamp' , Carbon::now()->timestamp);
        $this->sendData = $attributes;
        return $this;
    }

    public function setPurchaseInfo()
    {
        $items = $this->sendData['items'];
        $amount = 0;
        foreach($items as $item) {
            $itemName[] = $item['name'];
            $itemQty[] = $item['qty'];
            $itemUnit[] = $item['unit'];
            $itemPrice[] = $item['price'];
            $itemAmount[] = $item['price']*$item['qty'];
            $amount += $item['price']*$item['qty'];
        }
        $itemNameInv = implode('|', $itemName);
        $itemCountInv = implode('|', $itemQty);
        $itemUnitInv = implode('|', $itemUnit);
        $itemPriceInv = implode('|', $itemPrice);
        $itemAmountInv = implode('|', $itemAmount);

        $this->put('MerchantOrderNo', $this->sendData['orderId']);
        $this->put('TotalAmt', $amount);
        $this->put('ItemName', $itemNameInv);
        $this->put('ItemCount', $itemCountInv);
        $this->put('ItemUnit', $itemUnitInv);
        $this->put('ItemPrice', $itemPriceInv);
        $this->put('ItemAmt', $itemAmountInv);
        return $this;
    }

    public function setInvoiceInfo()
    {
        $this->put('Status', $this->sendData['Status'] ?? 1);
        $this->put('Category', $this->sendData['Category'] ?? 'B2C');
        $this->put('BuyerName', $this->sendData['BuyerName']);
        $this->put('PrintFlag', $this->sendData['PrintFlag'] ?? 'N');
        $this->put('LoveCode', null);
        $this->put('CarrierType', null);
        if ($this->get('Category') == 'B2B') {
            $this->put('PrintFlag', $this->sendData['PrintFlag'] ?? 'Y');
            $this->put('BuyerUBN', $this->sendData['BuyerUBN']);
        } else {
            $this->put('CarrierType', $this->sendData['CarrierType'] ?? null);
            $this->put('LoveCode', $this->sendData['LoveCode'] ?? null);
        }
        if ($this->get('LoveCode') !== null) {
            $this->put('CarrierType', null);
        }
        if ($this->get('CarrierType') !== null) {
            $this->put('CarrierNum', rawurlencode(trim($this->sendData['CarrierNum'])));
        }
        if ($this->get('CarrierType') === null && $this->get('LoveCode') === null) {
            $this->put('PrintFlag', 'Y');
        }
        return $this;
    }

    /**
     * @return $this
     * @throws EZPayException
     */
    public function setTaxInfo()
    {
        $this->put('TaxType', $this->sendData['TaxType'] ?? 1);
        if ($this->get('TaxType') == 1) {
            $this->put('TaxRate', 5);
        } elseif ($this->get('TaxType') == 2) {
            $this->put('TaxRate', 0);
        } else {
            $this->put('TaxRate', $this->sendData['TaxRate']);
        }
        $this->put('Amt', $this->priceBeforeTax($this->get('TotalAmt'), $this->get('TaxRate')));
        $this->put('TaxAmt', $this->calcTax($this->get('TotalAmt'), $this->get('TaxRate')));
        if ($this->get('TaxType') == 9) {
            $this->setMixTaxInfo();
        }
        return $this;
    }

    /**
     * @throws EZPayException
     */
    private function setMixTaxInfo()
    {
        if ($this->get('Category') == 'B2B') {
            throw new EZPayException('限發票種類為B2C,Category=B2C 時使用');
        }
        $items = $this->sendData['items'];
        foreach($items as $item) {
            if (!isset($item['tax'])) {
                throw new EZPayException('當 TaxType =9 混合應稅與免稅或零稅率時, 每個產品的資訊必須加入 tax 類別, ex: $item[\'tax\']');
            }
            $itemTaxType[] = $item['tax'];
        }
        $itemNameTaxType = implode('|', $itemTaxType);
        $this->put('AmtZero', $this->sendData['AmtZero'] ?? 0);
        $this->put('AmtFree', $this->sendData['AmtFree'] ?? 0);
        $this->put('Amt', $this->get('AmtSales')+$this->get('AmtZero')+$this->get('AmtFree'));
        $this->put('ItemTaxType', $itemNameTaxType);
    }

    public function setOptionalInfo()
    {
        $optionalParams = [
            'TransNum', 'CreateStatusTime', 'BuyerAddress', 'BuyerEmail', 'CustomsClearance',
            'AmtSales', 'AmtZero', 'AmtFree', 'ItemTaxType', 'Comment'
            ];
        foreach($optionalParams as $param) {
            if (isset($this->sendData[$param])) {
                $this->put($param, $this->sendData[$param]);
            }
        }
        return $this;
    }


    /**
     * @return $this
     * @throws EZPayException
     */
    public function setPostData()
    {
        $this->postData = StringService::createAesEncrypt($this,
            config('ezpay.invoice.HashKey'),
            config('ezpay.invoice.HashIV'));
        return $this;
    }

    private function priceBeforeTax($price, $tax)
    {
        return $price - $this->calcTax($price, $tax);
    }

    private function calcTax($price, $tax)
    {
        $taxRate = $tax / 100;

        return $price - round($price / (1 + $taxRate));
    }
}