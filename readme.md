# Laravel EZPay
Laravel EZPay 為串接EZPay的非官方套件

## 系統需求
 - PHP >= 7
 - Laravel >= 5.7
 - guzzlehttp >= 6.2

## 安裝
```composer require tsaiyihua/laravel-ezpay```

## 環境設定
```php artisan vendor:publish --tag=ezpay```  
### .env 裡加入
```
EZPAY_MERCHANT_ID=
EZPAY_HASH_KEY=
EZPAY_HASH_IV=
```
 - 金流測試用的參數值請參考介接文件 API_E_wallet_ezPay_1.0.2.pdf 第17頁。
 - 查詢訂單的參數請請參考介接文件 API_Trans_ezPay_1.0.0.pdf 第10頁。

## 用法
### 建立訂單
  - 產品資料單筆時可簡單只傳送 itemName 及 amount
```php
use TsaiYiHua\EZPay\MPG;

class MpgController extends Controller 
{
    public function __construct(MPG $mpg)
    {
        $this->mpg = $mpg;
    }
...

    public function sendOrder()
    {
        $data = [
            'itemName' => 'Donate',
            'amount' => 50
        ];
        return $this->mpg->createOrder($data);
    }
```
### 查詢訂單
```php
use TsaiYiHua\EZPay\Collections\QueryResponseCollection;
use TsaiYiHua\EZPay\Query;

class QueryController extends Controller 
{
    protected $query;
    protected $queryResponse;
    
    public function __construct(Query $query, QueryResponseCollection $queryResponse)
    {
        $this->query = $query;
        $this->>queryResponse = $queryResponse;
    }
    ...
    public function queryInfo()
    {
        $res = $this->query->queryInfo('18120414321996244');
        return $this->queryResponse->collectResponse($res);
    }
```
### 開立發票
```php
use TsaiYiHua\EZPay\Collections\InvoiceResponseCollection;
use TsaiYiHua\EZPay\Invoice;

class InvoiceController extends Controller
{
    ...
    public function __construct(Invoice $invoice, InvoiceResponseCollection $invResponse)
    {
        $this->invoice = $invoice;
        $this->invResponse = $invResponse;
    }
    ...
    
    public function issueInvoice()
    {
        $itemData[] = [
            'name' => 'Donate',
            'qty' => 1,
            'unit' => '次',
            'price' => 500
        ];
        $invData = [
            'orderId' => StringService::identifyNumberGenerator('O'),
            'items' => $itemData,
            'BuyerName' => 'Buyer Name',
            'BuyerEmail' => 'eamil@address.com',
            'LoveCode' => 919
        ];
        return $this->invResponse->collectResponse($this->invoice->issueInvoice($invData)->query());
    }
```
 - 已知問題
   - NotifyURL 及 ReturnURL 的參數無效，必須直接到管理平台設定才有設。
   - 境外轉帳可用支付寶，但也要企業帳號才能測試。

## 參考資料
 - ezPay 簡單付電子支付平台技術串接手冊 (2017-9-11)
   - 文件編號 ezPay_1.0.2
   - 文件位置 documents/API_E_wallet_ezPay_1.0.2.pdf
 - ezPay 簡單付電子支付平台交易狀態查詢 技術串接手冊 (2017-03-23)
   - 文件編號 ezPay_1.0.0
   - 文件位置 documents/API_Trans_ezPay_1.0.0.pdf
 - <a href='https://www.ezpay.com.tw/info/Service_intro/api_document/member'>線上API文件區</a>
 - https://github.com/s950329/laravel-spgateway
 - 捐贈碼清冊
   - documents/20181205141501_受捐贈機關或團體捐贈碼清冊.pdf
   - <a href='https://www.einvoice.nat.gov.tw/APMEMBERVAN/XcaOrgPreserveCodeQuery/XcaOrgPreserveCodeQuery' target='_blank'>捐贈碼查詢平台</a>

