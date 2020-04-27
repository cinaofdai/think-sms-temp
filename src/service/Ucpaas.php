<?php



namespace dh2y\sms\service;


/** .-----------------------------配置说明---------------------------------
 * |    只需要配置 account(云之讯accountSid )和  password(云之讯token)
 * |------------------------------配置方法---------------------------------
 * |   'SMS_SDK' => array(
 * |        'class' => 'Ucpaas',
 * |        'appid' => 'appidxxxxxxx',
 * |        'account' => 'demo',
 * |        'password'=> '12345',
 * |        'signature' => 'XXXX'   //签名
 * |   )
 * |   new Sms(config('SMS_SDK'))
 * '-------------------------------------------------------------------*/

class Ucpaas extends TemplateInterface
{

    //API请求地址
    protected $baseUrl = "https://open.ucpaas.com/ol/sms/";

    /**
     * 发送短信
     * @param $phone
     * @param string $code 短信模板Code
     * @param array $param ["code" => "12345","product" => "ddd"];
     * @return mixed
     */
    public function sendSms($phone, $code, $param)
    {
        $param_str = '';
        foreach ($param as $pa){
            $param_str .= $param_str==''?$pa:','.$pa;
        }

        $url =  $url = $this->getRequestUrl('sendsms');
        $body_json = array(
            'sid'=>$this->account,
            'token'=>$this->password,
            'appid'=>$this->appid,
            'templateid'=>$code,
            'param'=>$param_str,
            'mobile'=>$phone,
            'uid'=>'',
        );
        $body = json_encode($body_json);
        $data = $this->getResult($url, $body,'post');
        return $data;
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
        return $this->baseUrl.$api;
    }

    private function getResult($url, $body = null, $method)
    {
        $data = $this->connection($url,$body,$method);
        if (isset($data) && !empty($data)) {
            $result = $data;
        } else {
            $result = '没有返回数据';
        }
        return $result;
    }

    /**
     * @param $url    请求链接
     * @param $body   post数据
     * @param $method post或get
     * @return mixed|string
     */
    private function connection($url, $body,$method)
    {
        if (function_exists("curl_init")) {
            $header = array(
                'Accept:application/json',
                'Content-Type:application/json;charset=utf-8',
            );
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
            if($method == 'post'){
                curl_setopt($ch,CURLOPT_POST,1);
                curl_setopt($ch,CURLOPT_POSTFIELDS,$body);
            }
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            $result = curl_exec($ch);
            curl_close($ch);
        } else {
            $opts = [];
            $opts['http'] = [];
            $headers = array(
                "method" => strtoupper($method),
            );
            $headers[]= 'Accept:application/json';
            $headers['header'] = [];
            $headers['header'][]= 'Content-Type:application/json;charset=utf-8';

            if(!empty($body)) {
                $headers['header'][]= 'Content-Length:'.strlen($body);
                $headers['content']= $body;
            }

            $opts['http'] = $headers;
            $result = file_get_contents($url, false, stream_context_create($opts));
        }
        return $result;
    }



}