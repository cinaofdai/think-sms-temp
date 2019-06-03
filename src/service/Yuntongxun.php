<?php
/**
 * Created by dailinlin.
 * Date: 2019/6/3 16:19
 * for:
 */


namespace dh2y\sms\service;


use dh2y\sms\service\yuntongxun\REST;

class Yuntongxun extends TemplateInterface
{

            //请求地址，格式如下，不需要写https://
        private $serverIP='app.cloopen.com';

            //请求端口
        private $serverPort='8883';

            //REST版本号
        private $softVersion='2013-12-26';

    /**
     * 发送短信
     * @param $phone 手机号码集合,用英文逗号分开
     * @param string $code 短信模板 模板Id
     * @param array $param 格式为数组 例如：$param ["code" => "12345","product" => "ddd"]，如不需替换请填 null
     * @return mixed
     */
    public function sendSms($phone, $code, $param)
    {
        // 初始化REST SDK
        $accountSid = $this->account;
        $accountToken = $this->password;
        $appId = $this->app_id;

        $rest = new REST($this->serverIP,$this->serverPort,$this->softVersion);
        $rest->setAccount($accountSid,$accountToken);
        $rest->setAppId($appId);


        $datas = [];
        foreach ($param as $key => $item){
            $datas[] = $param[$key];
        }
         // 发送模板短信  array('Marry','Alon')
        $result = $rest->sendTemplateSMS($phone,$datas,$code);
        if($result == NULL ) {
           $this->setError('result error!');
           return false;
        }
        if($result->statusCode!=0) {
            $this->setError($result->statusMsg);

            return false;
        }else{
            return true;
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
}