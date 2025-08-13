<?php
/**
 *@作者:MissZhang
 *@邮箱:<787727147@qq.com>
 *@创建时间:2021/7/15 下午3:35
 *@说明:支付控制器
 */

namespace app\mobile\controller;


use app\BaseController;
use think\App;
use think\facade\Db;
use think\facade\View;

class Payment extends BaseController
{
    private $payment; //  具体的支付类
    private $pay_code; //  具体的支付code
    public function __construct(App $app)
    {
        parent::__construct($app);
        $pay_code=input('pay_code');
        $this->pay_code=$pay_code;
        if (empty($this->pay_code)){
            exit('支付方式有误');
        }
        switch ($this->pay_code){
            case 'alipayMobile':
                require_once root_path('payment/alipay/wappay').'service/AlipayTradeService.php';
                require_once root_path('payment/alipay/wappay').'buildermodel/AlipayTradeWapPayContentBuilder.php';
                $this->payment = new \AlipayTradeService();
                break;
            case 'weixin':
                require_once root_path('payment/weixin').'weixin.class.php';
                $this->payment = new \weixin();
                break;
            case 'weixinH5':
                require_once root_path('payment/weixinH5').'weixinH5.class.php';
                $this->payment = new \weixinH5();
                break;
        }
    }
    //提交支付
    public function get_code(){
        $order_sn=input('order_sn');
        if (empty($order_sn)){
            exit('订单号有误');
        }
        if(stripos($order_sn,'r') !== false){
            $order=Db::name('recharge')->where('order_sn',$order_sn)->find();
            $order_amount=$order['order_amount'];
        }else{
            $order=Db::name('order')->where('order_sn',$order_sn)->find();
            $order_amount=$order['order_amount'];
        }
        if (empty($order)){
            exit('订单不存在');
        }
        if ($order['order_status']>0){
            $this->error('此订单已完成支付');
        }
        //不在微信浏览器 但支付方式是微信
        if ($this->pay_code == 'weixin' && !strstr($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger')){
            exit('请在微信浏览器完成支付');
        }
        //在微信浏览器 但支付方式不是微信
        if (strstr($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') && $this->pay_code != 'weixin'){
            exit('请在浏览器完成支付');
        }
        $order['order_amount']=$order_amount;
        switch ($this->pay_code){
            case 'alipayMobile':
                $where['code']='alipayMobile';
                $where['status']=1;
                $plugin=Db::name('plugin')->where($where)->find();
                if (empty($plugin)){
                    exit('支付宝支付暂未开放');
                }
                $code_str = $this->payment->get_code($order);
                break;
            case 'weixin':
                //微信JS支付
                $code_str = $this->payment->getJSAPI($order);
                exit($code_str);
                break;
            case 'weixinH5':
                $where['code']='weixinH5';
                $where['status']=1;
                $plugin=Db::name('plugin')->where($where)->find();
                if (empty($plugin)){
                    exit('微信支付暂未开放');
                }
                //微信H5支付
                $return = $this->payment->get_code($order);
                if ($return['code'] != 1) {
                    $this->error($return['msg']);
                }
                View::assign('deeplink', $return['result']);
                if(!isset($deeplink_flag)) $deeplink_flag = 1;
                View::assign('deeplink_flag', $deeplink_flag);
                break;
        }
        View::assign('order',$order);
        View::assign('code_str',$code_str);
        return view('payment');
    }
    //支付通知
    public function notify_url(){
        $this->payment->do_notify();
        exit();
    }
    //判断支付状态 从而返回不同界面
    public function return_url(){
        $arr=input('get.');
        unset($arr['pay_code']);
        $result=$this->payment->check($arr);
        $out_trade_no = htmlspecialchars($_GET['out_trade_no']);
        $is_cz=0;
        if(stripos($out_trade_no,'r') !== false){
            $order=Db::name('recharge')->where('order_sn',$out_trade_no)->find();
            $is_cz=1;
        }else{
            $order=Db::name('order')->where('order_sn',$out_trade_no)->find();
        }
        View::assign('order',$order);
        View::assign('is_cz',$is_cz);
        if ($result){//验签成功
            if ($order['order_status']==1){
                return view('success');
            }else{
                return view('error');
            }
        }else{//验签失败
            return view('error');
        }
    }
    //微信支付无法验签 所有另外写一个方法
    public function weixin_return(){
        $order_sn=input('order_sn');
        if (empty($order_sn)){
            exit("提交参数有误");
        }
        $is_cz=0;
        if(stripos($order_sn,'r') !== false){
            $order=Db::name('recharge')->where('order_sn',$order_sn)->find();
            $is_cz=1;
        }else{
            $order=Db::name('order')->where('order_sn',$order_sn)->find();
        }
        View::assign('order',$order);
        View::assign('is_cz',$is_cz);
        if ($order['order_status']==1){
            return view('success');
        }else{
            return view('error');
        }
    }
}