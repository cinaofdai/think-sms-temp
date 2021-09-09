<?php


namespace dh2y\sms\service;

/** .-----------------------------配置说明---------------------------------
 * |    只需要配置 account(华为云短信APP_Key )和  password(华为云短信信APP_Secret)  app_id(华为云短信sender通道）
 * |------------------------------配置方法---------------------------------
 * |   'SMS_SDK' => array(
 * |        'class' => 'Huawei',
 * |        'account' => 'demo',
 * |        'password'=> '12345',
 * |        'signature' => 'XXXX'   //签名
 * |   )
 * |   new Sms(config('SMS_SDK'))
 * '-------------------------------------------------------------------*/
class Huawei extends TemplateInterface
{

    protected $baseUrl = 'https://rtcsms.cn-north-1.myhuaweicloud.com:10743/sms/batchSendSms/v1';

    /**
     * 发送短信
     * @param $phone
     * @param string $code 短信模板Code
     * @param array $param ["code" => "12345","product" => "ddd"];
     * @return mixed
     */
    public function sendSms($phone, $code, $param)
    {

        $APP_KEY = $this->account; //APP_Key
        $APP_SECRET = $this->password; //APP_Secret
        $sender = $this->app_id; //国内短信签名通道号或国际/港澳台短信通道号
        $TEMPLATE_ID = $code; //模板ID

        //条件必填,国内短信关注,当templateId指定的模板类型为通用模板时生效且必填,必须是已审核通过的,与模板类型一致的签名名称
        //国际/港澳台短信不用关注该参数
        $signature = $this->signature; //签名名称

        //必填,全局号码格式(包含国家码),示例:+86151****6789,多个号码之间用英文逗号分隔
        $receiver = '+86'.$phone; //短信接收人号码

        //选填,短信状态报告接收地址,推荐使用域名,为空或者不填表示不接收状态报告
        $statusCallback = '';

        /**
         * 选填,使用无变量模板时请赋空值 $TEMPLATE_PARAS = '';
         * 单变量模板示例:模板内容为"您的验证码是${1}"时,$TEMPLATE_PARAS可填写为'["369751"]'
         * 双变量模板示例:模板内容为"您有${1}件快递请到${2}领取"时,$TEMPLATE_PARAS可填写为'["3","人民公园正门"]'
         * 模板中的每个变量都必须赋值，且取值不能为空
         * 查看更多模板和变量规范:产品介绍>模板和变量规范
         * @var string $TEMPLATE_PARAS
         */
        $TEMPLATE = [];
        foreach ($param as $item){
            array_push($TEMPLATE,$item);
        }
        $TEMPLATE_PARAS = json_encode($TEMPLATE, JSON_UNESCAPED_UNICODE);

        //请求Headers
        $headers = [
            'Content-Type: application/x-www-form-urlencoded',
            'Authorization: WSSE realm="SDP",profile="UsernameToken",type="Appkey"',
            'X-WSSE: ' . $this->buildWsseHeader($APP_KEY, $APP_SECRET)
        ];
        //请求Body
        $data = http_build_query([
            'from' => $sender,
            'to' => $receiver,
            'templateId' => $TEMPLATE_ID,
            'templateParas' => $TEMPLATE_PARAS,
            'statusCallback' => $statusCallback,
            'signature' => $signature //使用国内短信通用模板时,必须填写签名名称
        ]);

        $context_options = [
            'http' => ['method' => 'POST', 'header'=> $headers, 'content' => $data, 'ignore_errors' => true],
            'ssl' => ['verify_peer' => false, 'verify_peer_name' => false] //为防止因HTTPS证书认证失败造成API调用失败，需要先忽略证书信任问题
        ];

        $response = file_get_contents($this->baseUrl, false, stream_context_create($context_options));
        $res =  json_encode($response,1);
        if ($res['code']=='000000'){
            return true;
        }else{
            $this->setError($res['description']);
            return false;
        }
    }


    /**
     * 构造X-WSSE参数值
     * @param string $appKey
     * @param string $appSecret
     * @return string
     */
    function buildWsseHeader($appKey, $appSecret){
        date_default_timezone_set('Asia/Shanghai');
        $now = date('Y-m-d\TH:i:s\Z'); //Created
        $nonce = uniqid(); //Nonce
        $base64 = base64_encode(hash('sha256', ($nonce . $now . $appSecret))); //PasswordDigest
        return sprintf("UsernameToken Username=\"%s\",PasswordDigest=\"%s\",Nonce=\"%s\",Created=\"%s\"",
            $appKey, $base64, $nonce, $now);
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
