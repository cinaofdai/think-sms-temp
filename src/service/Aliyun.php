<?php
/**
 * Created by dh2y.
 * Date: 2018/7/31 12:11
 * for:
 */

namespace dh2y\sms\service;


/** .-----------------------------配置说明---------------------------------
 * |    只需要配置 account(阿里大鱼accessKeyId )和  password(阿里大鱼accessKeySecret)
 * |------------------------------配置方法---------------------------------
 * |   'SMS_SDK' => array(
 * |        'class' => 'Aliyun',
 * |        'account' => 'demo',
 * |        'password'=> '12345',
 * |        'signature' => 'XXXX'   //签名
 * |   )
 * |   new Sms(config('SMS_SDK'))
 * '-------------------------------------------------------------------*/


class Aliyun extends TemplateInterface
{

    protected $baseUrl = 'dysmsapi.aliyuncs.com';

    /**
     * 获取短信请求地址
     * @param $api
     * @return mixed
     */
    public function getRequestUrl($api)
    {
        // TODO: Implement getRequestUrl() method.
    }

    /**
     * 发送短信
     * @param $phone
     * @param string $code 短信模板Code
     * @param array $param ["code" => "12345","product" => "ddd"];
     * @return mixed
     */
    public function sendSms($phone, $code,$param=[])
    {
        $params = array ();

        $accessKeyId = $this->account;
        $accessKeySecret = $this->password;

        // fixme 必填: 短信接收号码
        $params["PhoneNumbers"] = $phone;

        // fixme 必填: 短信签名，应严格按"签名名称"填写，请参考: https://dysms.console.aliyun.com/dysms.htm#/develop/sign
        $params["SignName"] =  $this->signature;

        // fixme 必填: 短信模板Code，应严格按"模板CODE"填写, 请参考: https://dysms.console.aliyun.com/dysms.htm#/develop/template
        $params["TemplateCode"] = $code;

        // fixme 可选: 设置模板参数, 假如模板中存在变量需要替换则为必填项
        $params['TemplateParam'] = $param;

        // fixme 可选: 设置发送短信流水号
        $params['OutId'] = time();



        // *** 需用户填写部分结束, 以下代码若无必要无需更改 ***
        if(!empty($params["TemplateParam"]) && is_array($params["TemplateParam"])) {
            $params["TemplateParam"] = json_encode($params["TemplateParam"], JSON_UNESCAPED_UNICODE);
        }


        // 此处可能会抛出异常，注意catch
        $content = $this->request(
            $accessKeyId,
            $accessKeySecret,
            $this->baseUrl,
            array_merge($params, array(
                "RegionId" => "cn-hangzhou",
                "Action" => "SendSms",
                "Version" => "2017-05-25",
            ))
        // fixme 选填: 启用https
        // ,true
        );

        if($content->Code=='OK'&&$content->Message=='OK'){
            return true;
        }else{
            return false;
        }
    }

    /**
     * 查询发送短信内容
     * @param $phone
     * @return mixed
     */
    public function querySend($phone)
    {
        $params = array ();

        // *** 需用户填写部分 ***

        // fixme 必填: 请参阅 https://ak-console.aliyun.com/ 取得您的AK信息
        $accessKeyId = $this->account;
        $accessKeySecret = $this->password;

        // fixme 必填: 短信接收号码
        $params["PhoneNumber"] = $phone;

        // fixme 必填: 短信发送日期，格式Ymd，支持近30天记录查询
        $params["SendDate"] = date('Ymd');

        // fixme 必填: 分页大小
        $params["PageSize"] = 1;

        // fixme 必填: 当前页码
        $params["CurrentPage"] = 1;

        // fixme 可选: 设置发送短信流水号
        $params["BizId"] = time();

        // *** 需用户填写部分结束, 以下代码若无必要无需更改 ***


        // 此处可能会抛出异常，注意catch
        $content = $this->request(
            $accessKeyId,
            $accessKeySecret,
            $this->baseUrl,
            array_merge($params, array(
                "RegionId" => "cn-hangzhou",
                "Action" => "QuerySendDetails",
                "Version" => "2017-05-25",
            ))
        // fixme 选填: 启用https
        // ,true
        );

        return $content;
    }



    /**
     * 生成签名并发起请求
     *
     * @param $accessKeyId string AccessKeyId (https://ak-console.aliyun.com/)
     * @param $accessKeySecret string AccessKeySecret
     * @param $domain string API接口所在域名
     * @param $params array API具体参数
     * @param $security boolean 使用https
     * @return bool|\stdClass 返回API接口调用结果，当发生错误时返回false
     */
    public function request($accessKeyId, $accessKeySecret, $domain, $params, $security=false) {
        $apiParams = array_merge(array (
            "SignatureMethod" => "HMAC-SHA1",
            "SignatureNonce" => uniqid(mt_rand(0,0xffff), true),
            "SignatureVersion" => "1.0",
            "AccessKeyId" => $accessKeyId,
            "Timestamp" => gmdate("Y-m-d\TH:i:s\Z"),
            "Format" => "JSON",
        ), $params);
        ksort($apiParams);

        $sortedQueryStringTmp = "";
        foreach ($apiParams as $key => $value) {
            $sortedQueryStringTmp .= "&" . $this->encode($key) . "=" . $this->encode($value);
        }

        $stringToSign = "GET&%2F&" . $this->encode(substr($sortedQueryStringTmp, 1));

        $sign = base64_encode(hash_hmac("sha1", $stringToSign, $accessKeySecret . "&",true));

        $signature = $this->encode($sign);

        $url = ($security ? 'https' : 'http')."://{$domain}/?Signature={$signature}{$sortedQueryStringTmp}";

        try {
            $content = $this->fetchContent($url);
            return json_decode($content);
        } catch( \Exception $e) {
            return false;
        }
    }

    private function encode($str)
    {
        $res = urlencode($str);
        $res = preg_replace("/\+/", "%20", $res);
        $res = preg_replace("/\*/", "%2A", $res);
        $res = preg_replace("/%7E/", "~", $res);
        return $res;
    }

    private function fetchContent($url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "x-sdk-client" => "php/2.0.0"
        ));

        if(substr($url, 0,5) == 'https') {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        }

        $rtn = curl_exec($ch);

        if($rtn === false) {
            trigger_error("[CURL_" . curl_errno($ch) . "]: " . curl_error($ch), E_USER_ERROR);
        }
        curl_close($ch);

        return $rtn;
    }

}