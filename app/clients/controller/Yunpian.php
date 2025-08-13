<?php
/**
 *@作者:MissZhang
 *@邮箱:<787727147@qq.com>
 *@创建时间:2021/7/21 下午9:35
 *@说明:发送短信控制器
 */
namespace app\mobile\controller;

require_once root_path('vendor') . "aliyun-dysms-php-sdk-lite/SignatureHelper.php";
use Aliyun\DySDKLite\SignatureHelper;
use think\App;
use think\facade\Db;

class Yunpian extends Base {
    private $sms=[];//短信配置

    protected function initialize()
    {
        parent::initialize();
        $this->sms=getSysConfig('sms');
    }
    public function index()
    {
        // 短信配置信息
        $ab               = $this->randstring();//获取随机数字
        $mobile           = input('mobile');//接收手机号码
        $type             = input('type');//发送类型
        $scene            = input('scene');//发送场景,1:用户注册,2:找回密码,3:修改密码
        header("Content-Type:text/html;charset=utf-8");
        // 验证
        if(!preg_match("/^1[3456789]{1}\d{9}$/",$mobile)){
            exit(json_encode(array('code' => 0, 'msg' => '请输入正确的手机号码')));
        }
        if (isset($type) && $type=='forget'){
            $user=Db::name('users')->where('mobile',$mobile)->find();
            if (empty($user)){
                exit(json_encode(array('code' => 0, 'msg' => '手机号码未注册')));
            }
            if ($user['is_lock']==1){
                exit(json_encode(array('code' => 0, 'msg' => '账户异常无法找回')));
            }
        }
        if (isset($type) && $type=='reg'){
            $user=Db::name('users')->where('mobile',$mobile)->find();
            if (!empty($user)){
                exit(json_encode(array('code' => 0, 'msg' => '手机号码已注册')));
            }
        }
        $data['scene']    = $scene;
        $data['code']     = $ab;
        $data['mobile']   = $mobile;
        $data['add_time'] = time();
        $res              = Db::name('sms_log')->where('mobile',$mobile)->find();
        if($res) {
            $interval_time    = time()-$res['add_time'];
            if($interval_time<$this->sms['sms_time_out']) {
                exit(json_encode(array('code' => 0, 'msg' => '发送太频繁')));
            } else {
                $log=Db::name('sms_log')->where('mobile',$mobile)->update($data);
            }
        } else {
            $log=Db::name('sms_log')->insert($data);
        }
        $sms=$this->sendAliSms($ab,$mobile);
//        $array = json_decode($sms,true);
        if ($log && $sms->Code=='OK'){
            exit(json_encode(array('code' => 1, 'msg' => '发送成功')));
        }else{
            exit(json_encode(array('code' => 0, 'msg' => '发送失败')));
        }
    }
    /**
     * 阿里云发送短信
     * @param string $code 验证码
     * @param string $mobile 手机号码
     */
    private function sendAliSms($code,$mobile){
        // *** 需用户填写部分 ***
        // 必填：是否启用https
        $security = false;
        //必填: 请参阅 https://ak-console.aliyun.com/ 取得您的AK信息
        $accessKeyId = $this->sms['access_key'];
        $accessKeySecret = $this->sms['secret'];
        //必填: 短信接收号码
        $params["PhoneNumbers"] = $mobile;
        //必填: 短信签名，应严格按"签名名称"填写，请参考: https://dysms.console.aliyun.com/dysms.htm#/develop/sign
        $params["SignName"] = $this->sms['sign_name'];
        //必填: 短信模板Code，应严格按"模板CODE"填写, 请参考: https://dysms.console.aliyun.com/dysms.htm#/develop/template
        $params["TemplateCode"] = $this->sms['sms_template'];
        //可选: 设置模板参数, 假如模板中存在变量需要替换则为必填项
        $params['TemplateParam'] = Array (
            "code" => $code
        );
        //可选: 设置发送短信流水号
        $params['OutId'] = "12345";
        //可选: 上行短信扩展码, 扩展码字段控制在7位或以下，无特殊需求用户请忽略此字段
        $params['SmsUpExtendCode'] = "1234567";
        // *** 需用户填写部分结束, 以下代码若无必要无需更改 ***
        if(!empty($params["TemplateParam"]) && is_array($params["TemplateParam"])) {
            $params["TemplateParam"] = json_encode($params["TemplateParam"], JSON_UNESCAPED_UNICODE);
        }
        // 初始化SignatureHelper实例用于设置参数，签名以及发送请求
        $helper = new SignatureHelper();
        // 此处可能会抛出异常，注意catch
        $content = $helper->request(
            $accessKeyId,
            $accessKeySecret,
            "dysmsapi.aliyuncs.com",
            array_merge($params, array(
                "RegionId" => "cn-hangzhou",
                "Action" => "SendSms",
                "Version" => "2017-05-25",
            )),
            $security
        );
        return $content;
    }
    /**
    单条发送短信的function，适用于注册/找回密码/认证/操作提醒等单个用户单条短信的发送场景
     * @param $appid        应用ID
     * @param $mobile       接收短信的手机号码
     * @param $templateid   短信模板，可在后台短信产品→选择接入的应用→短信模板-模板ID，查看该模板ID
     * @param null $param   变量参数，多个参数使用英文逗号隔开（如：param=“a,b,c”）
     * @param $uid			用于贵司标识短信的参数，按需选填。
     * @return mixed|string
     * @throws Exception
     */
    private function SendSms($param,$mobile){
        $url = 'https://open.ucpaas.com/ol/sms/sendsms';
        $body_json = array(
            'sid'=>$this->sms['sms_sid'],
            'token'=>$this->sms['sms_token'],
            'appid'=>$this->sms['sms_appid'],
            'templateid'=>$this->sms['sms_template'],
            'param'=>$param,
            'mobile'=>$mobile,
            'uid'=>'',
        );
        $body = json_encode($body_json);
        $data = $this->getResult($url, $body,'post');
        return $data;
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
            $opts = array();
            $opts['http'] = array();
            $headers = array(
                "method" => strtoupper($method),
            );
            $headers[]= 'Accept:application/json';
            $headers['header'] = array();
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
    /**
     * 获取随机位数数字
     * @param  integer $len 长度
     * @return string
     */
    protected static function randString($len = 6)
    {
        $chars = str_repeat('0123456789', $len);
        $chars = str_shuffle($chars);
        $str   = substr($chars, 0, $len);
        return $str;
    }
}