<?php
namespace TsaiYiHua\EZPay;

use GuzzleHttp\Client;

trait EZPayTrait
{
    /**
     * Send Post Data to EZPay API
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function send()
    {
        $data = [
            'apiUrl' => $this->apiUrl,
            'postData'  => $this->postData
        ];
        return view('ezpay::send', $data);
    }

    public function query()
    {
        $client = new Client();
        $response = $client->post($this->apiUrl, ['form_params'=>$this->postData]);
        return json_decode((string)$response->getBody());
    }
}