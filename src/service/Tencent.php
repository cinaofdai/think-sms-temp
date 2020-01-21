<?php
/**
 * Created by dailinlin.
 * Date: 2020/1/9 15:01
 * for:
 */


namespace dh2y\sms\service;


/** .-----------------------------配置说明---------------------------------
 * |    只需要配置 account(腾讯云短信AppID )和  password(腾讯云短信App Key)
 * |------------------------------配置方法---------------------------------
 * |   'SMS_SDK' => array(
 * |        'class' => 'Tencent',
 * |        'account' => 'demo',
 * |        'password'=> '12345',
 * |        'signature' => 'XXXX'   //签名
 * |   )
 * |   new Sms(config('SMS_SDK'))
 * '-------------------------------------------------------------------*/


class Tencent extends TemplateInterface
{

    protected $baseUrl = 'https://yun.tim.qq.com/v5/tlssmssvr/sendsms';

    /**
     * 发送短信
     * @param $phone
     * @param string $code 短信模板Code
     * @param array $param ["code" => "12345","product" => "ddd"];
     * @return mixed
     */
    public function sendSms($phone, $code, $param)
    {
        $random = rand(100000, 999999);
        $curTime = time();
        $wholeUrl = $this->baseUrl . "?sdkappid=" . $this->account . "&random=" . $random;

        // 按照协议组织 post 包体
        $data = new \stdClass();
        $tel = new \stdClass();
        $tel->nationcode = ""."86";
        $tel->mobile = "".$phone;

        $data->tel = $tel;
        $data->sig = $this->sign($this->password, $random,$curTime, $phone);
        $data->tpl_id = $code;
        $data->params = array_values($param);
        $data->sign = $this->signature;
        $data->time = $curTime;
        $data->extend = "";
        $data->ext = "";

        $result =  $this->sendCurlPost($wholeUrl, $data);
        $rsp = json_decode($result);

        if($rsp->result==0&&$rsp->errmsg=='OK'){
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

    /**
     * 生成签名
     * @param string $appkey        sdkappid对应的appkey
     * @param string $random        随机正整数
     * @param string $curTime       当前时间
     * @param array  $phoneNumber   手机号码
     * @return string  签名结果
     */
    public function sign($appkey, $random, $curTime, $phoneNumber){
        $phoneNumbers = array($phoneNumber);

        $phoneNumbersString = $phoneNumbers[0];
        for ($i = 1; $i < count($phoneNumbers); $i++) {
            $phoneNumbersString .= ("," . $phoneNumbers[$i]);
        }

        return hash("sha256", "appkey=".$appkey."&random=".$random."&time=".$curTime."&mobile=".$phoneNumbersString);
    }

    /**
     * 发送请求
     * @param string $url      请求地址
     * @param array  $dataObj  请求内容
     * @return string 应答json字符串
     */
    public function sendCurlPost($url, $dataObj){
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 60);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($dataObj));
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);

        $ret = curl_exec($curl);
        if (false == $ret) {
            // curl_exec failed
            $result = "{ \"result\":" . -2 . ",\"errmsg\":\"" . curl_error($curl) . "\"}";
        } else {
            $rsp = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            if (200 != $rsp) {
                $result = "{ \"result\":" . -1 . ",\"errmsg\":\"". $rsp
                    . " " . curl_error($curl) ."\"}";
            } else {
                $result = $ret;
            }
        }

        curl_close($curl);

        return $result;
    }


}