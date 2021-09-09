<?php


namespace dh2y\sms\service;

/** .-----------------------------配置说明---------------------------------
 * |    只需要配置 account(云片 apikey)和  password(云片  apikey)
 * |------------------------------配置方法---------------------------------
 * |   'SMS_SDK' => array(
 * |        'class' => 'Yunpian',
 * |        'account' => 'demo',
 * |        'password'=> '12345',
 * |        'signature' => 'XXXX'   //签名
 * |   )
 * |   new Sms(config('SMS_SDK'))
 * '-------------------------------------------------------------------*/
class Yunpian extends TemplateInterface
{

    protected $baseUrl = 'https://sms.yunpian.com/v2/sms/';


    /**
     * 发送短信
     * @param $phone
     * @param string $code 短信模板Code
     * @param array $param ["code" => "12345","product" => "ddd"];
     * @return mixed
     */
    public function sendSms($phone, $code, $param)
    {
        $url = $this->getRequestUrl('tpl_single_send.json');
        //多个参数tpl_value构建
        $tpl_value = '';
        foreach ($param as $key => $item){
            $temp =$tpl_value==''?'#'.$key.'#'.'='.urldecode($item):'&'.urldecode('#'.$key.'#').'='.urlencode($item);
            $tpl_value .= $temp;
        }

        // 需要对value进行编码
        $data = [
            'tpl_id' => $code,
            'tpl_value' =>$tpl_value ,
            'apikey' => $this->account,
            'mobile' => $phone
        ];
        $result = $this->curl_post($url,$data);
        return $result;
    }

    /**
     * @inheritDoc
     */
    public function querySend($phone)
    {
        // TODO: Implement querySend() method.
    }

    /**
     * @inheritDoc
     */
    public function getRequestUrl($api)
    {
        return $this->baseUrl.$api;
    }



    public function curl_post($url,$data){
        $ch = curl_init();
        /* 设置验证方式 */
        curl_setopt($ch, CURLOPT_HTTPHEADER,
            [
                'Accept:text/plain;charset=utf-8',
                'Content-Type:application/x-www-form-urlencoded',
                'charset=utf-8'
            ]);
        /* 设置返回结果为流 */
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        /* 设置超时时间*/
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        /* 设置通信方式 */
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        $result = curl_exec($ch);
        $error = curl_error($ch);
        if($result === false){
            $this->setError( $error);
            return false;
        }
        return json_decode($result,true);;
    }
}