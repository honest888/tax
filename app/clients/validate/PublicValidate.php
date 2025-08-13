<?php
/**
*@作者:MissZhang
*@邮箱:<787727147@qq.com>
*@创建时间:2021/7/7 下午6:21
*@说明:前台公共验证器
*/
namespace app\mobile\validate;

use app\common\model\Users;
use think\facade\Db;
use think\Validate;

class PublicValidate extends Validate
{
    protected $regex  = [
        'idcard'        =>      '/^(\d{15}|\d{18}|\d{17}x)$/i',
        'bank_card'     =>      '/^(\d{16}|\d{17}|\d{19})$/',
    ];
    //验证规则
    protected $rule = [
        'account'      =>'require',
        /*'mobile'            =>'require|checkMobile|unique:users',
        'login_mobile'      =>'require',
        'realname'          =>'require|chs',
        'nickname'          =>'require',
        'birthday'          =>'require',
        'idcard'            =>'require|regex:idcard',
        'consignee'         =>'require',
        'province'          =>'require|checkProvince',
        'address'           =>'require',
        'bank_name'         =>'require|chs',
        'bank_card'         =>'require|regex:bank_card',
        'oldpassword'       =>'requireIf:sms,0|checkPass:password',
        'oldpaypwd'         =>'requireIf|checkPass:paypwd',
        'password'          =>'require|min:6',
        'conpassword'       =>'require|confirm:password',
        'rekey'             =>'require|checkReid',
        'node'              =>'require|checkNode',
        'zhifubao'          =>'require|checkZfb',
        'code'              =>'requireIf:sms,1',
        '__token__'         =>'require|token',*/
    ];
    //错误信息
    protected $message  = [
        'mobile.require'            => '请输入账号',
        'mobile.checkMobile'        => '手机号码格式有误',
        'mobile.unique'             => '手机号码已注册',
        'login_mobile.require'      => '请输入账号/手机号',
        'nickname.require'          => '请输入昵称',
        'birthday.require'          => '请选择生日',
        'oldpassword.requireIf'     => '请输入原密码',
        'oldpaypwd.require'         => '请输入原密码',
        'password.require'          => '请输入密码',
        'password.min'              => '密码至少6位',
        'conpassword.require'       => '请输入确认密码',
        'conpassword.confirm'       => '两次密码输入不一致',
        'code.requireIf'            => '请输入验证码',
        'code.checkCode'            => '请输入验证码',
        'rekey.require'             => '请输入推荐人',
        'node.require'              => '请输入接点人',
        'realname.require'          => '请输入真实姓名',
        'realname.chs'              => '真实姓名只能为中文',
        'idcard.require'            => '请输入身份证号',
        'idcard.regex'              => '身份证号格式有误',
        'idcard.unique'             => '身份证号已注册',
        'consignee.require'         => '请输入收货人',
        'address.require'           => '请输入详细地址',
        'province.require'          => '请选择地区',
        'province.checkProvince'    => '请选择地区',
        'zhifubao.require'          => '请输入支付宝',
        'zhifubao.checkZfb'         => '支付宝格式有误',
        'bank_name.require'         => '请输入开户银行',
        'bank_name.chs'             => '开户银行只能为中文',
        'bank_card.require'         => '请输入银行账号',
        'bank_card.regex'           => '银行账号格式有误',
        '__token__.require'         => '非法提交',
        '__token__.token'           => '请刷新重试',
    ];
    public function sceneLogin(){
        return $this->only(['login_mobile','password','__token__'])
            ->append('password','checkUser');
    }
    //注册验证场景
    public function sceneReg(){
        return $this->only(['mobile','code','realname','password','conpassword','rekey'])
            ->append('code','checkCode:regis_sms_enable');
    }
    public function sceneForget(){
        return $this->only(['mobile','code','password','conpassword','__token__'])
            ->remove('mobile','checkMobile|unique')
            ->append('mobile','checkReg')
            ->append('code','checkCode');
    }
    public function sceneRealname(){
        return $this->only(['realname','__token__']);
    }
    public function sceneNickname(){
        return $this->only(['nickname','__token__']);
    }
    public function sceneZhifubao(){
        return $this->only(['realname','zhifubao','code','__token__'])
            ->append('code','checkCode:user_info_sms_enable');
    }
    public function sceneBank(){
        return $this->only(['realname','bank_name','bank_card','code','__token__'])
            ->append('code','checkCode:user_info_sms_enable');
    }
    public function sceneInfo(){
        return $this->only(['realname','bank_name','bank_card','code','__token__'])
            ->append('code','checkCode:user_info_sms_enable');
    }
    public function scenePass(){
        return $this->only(['oldpassword','code','password','conpassword','__token__'])
            ->append('code','checkCode:edit_pwd_sms_enable');
    }
    public function sceneNoPaypwd(){
        return $this->only(['code','password','conpassword','__token__'])
            ->append('code','checkCode:edit_paypwd_sms_enable');
    }
    public function scenePaypwd(){
        return $this->only(['oldpaypwd','code','password','conpassword','__token__'])
            ->append('code','checkCode:edit_paypwd_sms_enable');
    }
    //检查手机号是否注册、是否被锁定
    protected function checkUser($value, $rule, $data){
        $user_where['mobile|account']=$data['account'];
        $user=Users::where($user_where)->find();
        if (empty($user) || encrypt($value)!=$user['password']){
            return '账号或密码错误';
        }
        if ($user['is_lock']==1){
            return '账号异常已被锁定';
        }
        return true;
    }
    //检查手机号是否注册
    protected function checkReg($value, $rule, $data){
        $user=get_user_info($value,1);
        if (empty($user)){
            return '手机号码未注册';
        }
        return true;
    }
    //检查手机号
    protected function checkMobile($value, $rule, $data)
    {
        return check_mobile($value);
    }
    //检查旧密码
    protected function checkPass($value, $rule, $data)
    {
        $user=get_user_info($data['mobile'],1);
        if ($user[$rule]!=encrypt($value)){
            return '原密码不正确';
        }
        return true;
    }
    //检查支付宝账号
    protected function checkZfb($value, $rule, $data)
    {
        if(!preg_match('/^0?(13|14|15|16|17|18|19)[0-9]{9}$/',$value) && !preg_match("/^([0-9A-Za-z\\-_\\.]+)@([0-9a-z]+\\.[a-z]{2,3}(\\.[a-z]{2})?)$/i",$value)){
            return false;
        }
        return true;
    }
    //检查地区
    protected function checkProvince($value, $rule, $data)
    {
        if ($value=='请选择'){
            return false;
        }
        return true;
    }
    //检查推荐人
    protected function checkReid($value, $rule, $data)
    {
        $user_where['mobile|account']=$value;
        $user=Users::where($user_where)->find();
        if (empty($user)){
            return '推荐人不存在';
        }
        if ($user['is_lock']==1){
            return '推荐人状态异常';
        }
        if ($user['mobile']==$data['mobile']){
            return '推荐人不能为自己';
        }
        return true;
    }
    //检查接点人
    protected function checkNode($value, $rule, $data)
    {
        //先判断推荐人是否已填写
        if (empty($data['rekey'])){
            return '请输入推荐人';
        }
        $reuser=Users::where('mobile',$data['rekey'])->find();
        if (empty($reuser)){
            return '推荐人不存在';
        }
        $user=Users::where('mobile',$value)->find();
        if (empty($user)){
            return '接点人不存在';
        }
        if ($user['is_lock']==1){
            return '接点人状态异常';
        }
        if ($user['mobile']==$data['mobile']){
            return '接点人不能为自己';
        }
        //判断接点人是否为推荐人伞下的
        if ($reuser['user_id']!=$user['user_id']){
            $ids=get_node_ids($reuser['user_id']);
            if (!in_array($user['user_id'],$ids)){
                return '接点人必须为推荐人伞下的会员';
            }
        }
        return true;
    }
    //检查身份证号
    protected function checkIdcard($value, $rule, $data)
    {
        if ($data['user_id']){
            $count=Db::name('users')->whereLike('idcard','%'.$value.'%')->where('user_id!='.$data['user_id'])->count();
        }else{
            $count=Db::name('users')->whereLike('idcard','%'.$value.'%')->count();
        }
        if ($count==0){
            return true;
        }else{
            return false;
        }
    }
    //检查验证码
    protected function checkCode($value, $rule, $data)
    {
        if (empty($rule)){
            $sms_enable=1;
        }else{
            $sms_enable=getSysConfig('sms.'.$rule);
        }
        if ($sms_enable){
            $code=yz_sms_code($data['mobile'],$value);
            if ($code['code']==1){
                return true;
            }else{
                return $code['msg'];
            }
        }else{
            return true;
        }
    }
}