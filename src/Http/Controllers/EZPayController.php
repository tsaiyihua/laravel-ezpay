<?php
namespace TsaiYiHua\EZPay\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use TsaiYiHua\EZPay\Collections\MpgResponseCollection;
use TsaiYiHua\EZPay\Services\StringService;

class EZPayController extends Controller
{
    protected $mpgResCollection;

    public function __construct(MpgResponseCollection $mpgResCollection)
    {
        $this->mpgResCollection = $mpgResCollection;
    }

    /**
     * @param Request $request
     * @return MpgResponseCollection
     * @throws \TsaiYiHua\EZPay\Exceptions\EZPayException
     */
    public function notifyUrl(Request $request)
    {
        $this->mpgResCollection->collectResponse($request);
        return $this->mpgResCollection;
    }

    /**
     * @param Request $request
     * @throws \TsaiYiHua\EZPay\Exceptions\EZPayException
     */
    public function returnUrl(Request $request)
    {
        $this->mpgResCollection->collectResponse($request);
        dd($this->mpgResCollection);
    }

    /**
     * @param Request $request
     * @throws \TsaiYiHua\EZPay\Exceptions\EZPayException
     */
    public function customUrl(Request $request)
    {
        $this->mpgResCollection->collectResponse($request);
        dd($this->mpgResCollection);
    }
}