<?php


namespace dh2y\sms\service;

/** .-----------------------------配置说明---------------------------------
 * |    只需要配置 account(SendCloud apiUser )和  password(SendCloud apiKey)
 * |------------------------------配置方法---------------------------------
 * |   'SMS_SDK' => array(
 * |        'class' => 'SendCloud',
 * |        'account' => 'demo',
 * |        'password'=> '12345',
 * |        'signature' => 'XXXX'   //签名
 * |   )
 * |   new Sms(config('SMS_SDK'))
 * '-------------------------------------------------------------------*/
class SendCloud extends TemplateInterface
{
    protected $baseUrl = ' http://www.sendcloud.net/smsapi/send';

    /**
     * 发送短信
     * @param $phone
     * @param string $code 短信模板Code
     * @param array $param ["code" => "12345","product" => "ddd"];
     * @return mixed
     */
    public function sendSms($phone, $code, $param)
    {
        $apiUser = $this->account;
        $apiKey = $this->password;
        $params = [
            'smsUser' => $apiUser,
            'templateId' => $code,
            'msgType' => 0,
            'phone' => $phone,
            'vars' => json_encode($param, 1),
        ];
        $params['signature'] = sign($apiKey, $params);
        $result = $this->postDataCurl($params);
        return $result;
    }

    /**
     * 查询发送短信内容
     * @param $phone
     * @return mixed
     */
    public function querySend($phone)
    {
        // TODO: Implement querySend() method.
    }

    /**
     * 获取短信请求地址
     * @param $api
     * @return mixed
     */
    public function getRequestUrl($api)
    {
        // TODO: Implement getRequestUrl() method.
    }


    public function curl_post($params){
        $ch = curl_init();
        try {
            curl_setopt_array($ch, [
                CURLOPT_URL => $this->baseUrl,
                CURLOPT_POST => true,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CONNECTTIMEOUT => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_POSTFIELDS => http_build_query($params),
            ]);
            $result = curl_exec($ch);
            $error = curl_error($ch);
            if ($error) {
                $this->setError($error);
                return false;
            }
        } catch (\Exception $error) {
            $this->setError($error);
            return false;
        }
        curl_close($ch);
        return json_decode($result, true);
    }


    /**
     * 加密
     * @param $apiKey
     * @param $params
     * @return string
     */
    public function sign($apiKey, $params){
        ksort($params);
        $signParts = [$apiKey, $apiKey];
        foreach ($params as $key => $value) {
            array_splice($signParts, -1, 0, $key.'='.$value);
        }
        return md5(join('&', $signParts));
    }
}
