<?php
namespace TsaiYiHua\EZPay\Services;

use Illuminate\Support\Collection;
use TsaiYiHua\EZPay\Exceptions\EZPayException;

class StringService
{
    /**
     * Identify Number Generator
     * @return string
     * @throws EZPayException
     */
    static public function identifyNumberGenerator($prefix='A')
    {
        if (strlen($prefix) > 2) {
            throw new EZPayException('ID prefix character maximum is 2 characters');
        }
        $intMsConst = 1000000;
        try {
            list($ms, $timestamp) = explode(" ", microtime());
            $msString = (string) substr('000000'.($ms*$intMsConst), -6);
            return $prefix . $timestamp . $msString . substr('00'.random_int(0, 99),-2);
        } catch (\Exception $e) {
            return $prefix . $timestamp . $msString . '00';
        }
    }

    static public function parseResponse($info)
    {
        return json_decode(self::decryptAes256cbc($info));
    }

    /**
     * @param Collection $collection
     * @param null $key
     * @param null $iv
     * @return string
     * @throws EZPayException
     */
    static public function createAesEncrypt (Collection $collection, $key=null, $iv=null) {
        if ($collection->isNotEmpty()) {
            $return_str = http_build_query($collection->toArray());
        } else {
            throw new EZPayException('Post data can not be empty');
        }
        // 加密字串
        $tradeInfo = trim(bin2hex(openssl_encrypt(
            self::addPadding($return_str),
            'AES-256-CBC',
            $key ?? config('ezpay.mpg.HashKey'),
            OPENSSL_RAW_DATA | OPENSSL_NO_PADDING,
            $iv ?? config('ezpay.mpg.HashIV')
        )));
        return $tradeInfo;
    }

    /**
     * @param $aesTradeInfo
     * @return string
     * @throws EZPayException
     */
    static public function createShaEncrypt($aesTradeInfo)
    {
        if (empty($aesTradeInfo)) {
            throw new EZPayException('TradeInfo can not be empty');
        }
        $beHashString[] = 'HashKey='. config('ezpay.mpg.HashKey');
        $beHashString[] = $aesTradeInfo;
        $beHashString[] = 'HashIV='. config('ezpay.mpg.HashIV');
        return strtoupper(hash("sha256", join('&', $beHashString)));
    }

    static private function decryptAes256cbc($parameter = "")
    {
        return self::stripPadding(
                openssl_decrypt(hex2bin($parameter),
            'aes-256-cbc',
                config('ezpay.mpg.HashKey'),
                OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING,
                config('ezpay.mpg.HashIV')
            )
        );
    }

    static private function addPadding($string, $blockSize = 32) {
        $len = strlen($string);
        $pad = $blockSize - ($len % $blockSize);
        $string .= str_repeat(chr($pad), $pad);
        return $string;
    }

    static private function stripPadding($string) {
        $slast = ord(substr($string, -1));
        $slastc = chr($slast);
        if (preg_match("/$slastc{" . $slast . "}/", $string)) {
            $string = substr($string, 0, strlen($string) - $slast);
            return $string;
        } else {
            return false;
        }
    }
}