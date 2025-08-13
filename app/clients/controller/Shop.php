<?php
/**
*@作者:MissZhang
*@邮箱:<787727147@qq.com>
*@创建时间:2021/7/27 下午2:47
*@说明:商城控制器
*/
namespace app\mobile\controller;


use app\common\model\Ad;
use app\common\model\GoodsCategory;
use app\common\model\JfGoodsCategory;
use app\common\model\JifenGoods;
use app\common\model\JifenOrder;
use app\common\model\UserAddress;
use think\facade\Db;
use think\facade\View;

class Shop extends Base
{
    //首页
    public function index()
    {
        $ad=Ad::where('open=1 and ad_type=2')->order('sort asc,ad_id desc')->column('ad_link,image');
        $cate_list=JfGoodsCategory::where('is_show=1')->order('sort asc,id desc')->column('id,name');
        View::assign('ad',$ad);
        View::assign('cate_list',$cate_list);
        return view();
    }
    //ajax请求商品
    public function ajax_index(){
        $cate_id=input('cate_id',0);
        $keyword=input('keyword');
        if ($cate_id){
            $where[]=['cat_id_1','=',$cate_id];
        }
        if ($keyword){
            $where[]=['goods_name','like',"%$keyword%"];
        }
        $where[]=['is_on_sale','=',1];
        $count=Db::name('jifen_goods')->where($where)->count();
        $pages=ceil($count/10);
        $lists=Db::name('jifen_goods')->where($where)
            ->field('goods_id,goods_img,goods_price,goods_name,retail_price')
            ->order('sort asc,goods_id desc')
            ->select();
        $data=[];
        if ($lists){
            foreach ($lists as $key => $val){
                $img=$val['goods_img'] ? $val['goods_img'] : '/static/images/not_adv.png';
                $a='<a href="'.url('Shop/jifen_goods_info',['goods_id'=>$val['goods_id']]).'" class="goods-item">';
                $a.='<img class="goods-img" src="'.$img.'">';
                $a.='<div class="goods-info">
                    <div class="goods-name">'.$val['goods_name'].'</div>
                    <div class="goods-bot">
                      <div class="left">¥'.$val['goods_price'].'</div>
                      <span style="text-decoration:line-through;color:#888;font-size:12px;">零售价：'.$val['retail_price'].'</span>
                    </div>
                  </div>';
                $a.='</a>';
                $data[]=$a;
            }
        }
        return json(['pages'=>$pages,'data'=>$data]);
    }
    //积分商品详情
    public function jifen_goods_info(){
        $goods_id = input("goods_id/d");
        if (empty($goods_id)) {
            $this->error('提交参数有误');
        }
        $goodsModel = new JifenGoods();
        $goods = $goodsModel::find($goods_id);
        if (empty($goods) || $goods['is_on_sale'] == 0) {
            $this->error('此商品不存在或者已下架');
        }
        View::assign('goods', $goods);
        return view();
    }
    //购买积分商品
    public function jifen_goods_buy(){
        $goods_id = input("goods_id/d");
        $goods_num=input('goods_num',1);
        $address_id=input('address_id');
        if (empty($goods_id)) {
            $this->error('提交参数有误');
        }
        $goodsModel = new JifenGoods();
        $goods = $goodsModel::find($goods_id);
        if (empty($goods) || $goods['is_on_sale'] == 0) {
            $this->error('此商品不存在或者已下架');
        }
        if ($address_id){
            $add=Db::name('user_address')->where('address_id',$address_id)->find();
        }else{
            $add=Db::name('user_address')->where('user_id='.$this->user_id.' and is_default=1')->find();
        }
        $ship_price=$goodsModel->get_ship_price($goods_id,$add['address_id'],$goods_num);
        $total_amount=$goods['goods_price']*$goods_num;
        $order_amount=$total_amount;
        $pay_list=[];
        if ($ship_price>0){
            $payment_where[]=['status','=',1];
            if(strstr($_SERVER['HTTP_USER_AGENT'],'MicroMessenger')){
                //微信浏览器
                $payment_where[] = ['code','=','weixin'];
            }else{
                $payment_where[] =['code','in',['alipayMobile','weixinH5']];
            }
            $pay_list=Db::name('plugin')->where($payment_where)->field('name,code')->select()->toArray();
        }
        if (input('act')=='submit_order'){
            if (empty($address_id)){
                return json(['code'=>0,'msg'=>'请选择收货地址']);
            }
            $user_address=new UserAddress();
            $add=$user_address->where(['user_id'=>$this->user_id,'address_id'=>$address_id])->find();
            if (empty($add)){
                return json(['code'=>0,'msg'=>'收货地址不存在']);
            }
            if ($this->user['jifen']<$order_amount){
                return json(['code'=>0,'msg'=>"积分不足,可用{$this->user['jifen']}!"]);
            }
            $pay_code=input('pay_code');
            if ($ship_price>0){
                if (empty($pay_code) || !in_array($pay_code,['alipayMobile','weixin','weixinH5'])){
                    return json(['code'=>0,'msg'=>"支付方式有误"]);
                }
                $jf_order_insert['pay_name']    =   Db::name('plugin')->where('code',$pay_code)->value('name');
            }else{
                $jf_order_insert['pay_name']    =   '积分';
                $jf_order_insert['pay_time']    =   time();
                $pay_code='jifen';
                $jf_order_insert['order_status']=   1;
            }
            $check=$this->request->checkToken('__token__');
            if (false===$check){
                return json(['code'=>0,'msg'=>"非法提交"]);
            }
            $jf_order_insert['pay_code']            =   $pay_code;
            $jf_order_insert['type']                =   2;
            $jf_order_insert['order_sn']            =   getOrderSn();//订单编号
            $jf_order_insert['user_id']             =   $this->user_id;//用户id
            $jf_order_insert['consignee']           =   $add['consignee'];//收货人
            $jf_order_insert['mobile']              =   $add['mobile'];//手机
            $jf_order_insert['province']            =   $add['province'];//省份
            $jf_order_insert['city']                =   $add['city'];//城市
            $jf_order_insert['district']            =   $add['district'];//县区
            $jf_order_insert['address']             =   $add['address'];//详细地址
            $jf_order_insert['pid']                 =   $add['pid'];//省份id
            $jf_order_insert['cid']                 =   $add['cid'];//城市id
            $jf_order_insert['did']                 =   $add['did'];//区/县id
            $jf_order_insert['user_note']           =   input('user_note','');//用户备注
            $jf_order_insert['total_amount']        =   $total_amount;//订单总价
            $jf_order_insert['order_amount']        =   $order_amount;//应付款金额
            $jf_order=new \app\common\model\Order();
            $res=$jf_order->save($jf_order_insert);
            $log1=false;
            if ($res){
                $order_goods['order_id']        =   $jf_order->order_id;
                $order_goods['goods_id']        =   $goods['goods_id'];
                $order_goods['goods_name']      =   $goods['goods_name'];
                $order_goods['goods_img']       =   $goods['goods_img'];
                $order_goods['goods_num']       =   $goods_num;
                $order_goods['goods_price']     =   $goods['goods_price'];
                $order_goods['is_zhifu']        =   1;
                $order_goods['pay_time']        =   time();
                //插入订单商品表
                $log1=Db::name('order_goods')->insert($order_goods);
            }
            $log=accountLog($this->user_id,-$order_amount,"兑换商品",2,0,$jf_order->order_id,$jf_order->order_sn);
            if ($ship_price>0){
                $url=url('Payment/get_code',['pay_code'=>$pay_code,'order_sn'=>$jf_order_insert['order_sn']])->build();
                $msg='跳转支付中~';
            }else{
                $url=url('Order/order_list')->build();
                $msg='兑换成功';
            }
            if ($res && $log && $log1){
                return json(['code'=>1,'msg'=>$msg,'url'=>$url]);
            }else{
                return json(['code'=>0,'msg'=>'兑换失败']);
            }
        }
        View::assign('total_amount',$total_amount);//订单总额
        View::assign('ship_price',$ship_price);//运费
        View::assign('order_amount',$order_amount);//应付
        View::assign('add',$add);
        View::assign('goods', $goods);
        View::assign('goods_num', $goods_num);
        View::assign('pay_list', $pay_list);
        return view();
    }
    public function jifen_goods_buy1(){
        $goods_id = input("goods_id/d");
        $goods_num=input('goods_num',1);
        $address_id=input('address_id');
        if (empty($goods_id)) {
            $this->error('提交参数有误');
        }
        $goodsModel = new JifenGoods();
        $goods = $goodsModel::find($goods_id);
        if (empty($goods) || $goods['is_on_sale'] == 0) {
            $this->error('此商品不存在或者已下架');
        }
        if ($address_id){
            $add=Db::name('user_address')->where('address_id',$address_id)->find();
        }else{
            $add=Db::name('user_address')->where('user_id='.$this->user_id.' and is_default=1')->find();
        }
        $ship_price=$goodsModel->get_ship_price($goods_id,$add['address_id'],$goods_num);
        $total_amount=$goods['goods_price']*$goods_num;
        $order_amount=$total_amount;
        $pay_list=[];
        if ($ship_price>0){
            $payment_where[]=['status','=',1];
            if(strstr($_SERVER['HTTP_USER_AGENT'],'MicroMessenger')){
                //微信浏览器
                $payment_where[] = ['code','=','weixin'];
            }else{
                $payment_where[] =['code','in',['alipayMobile','weixinH5']];
            }
            $pay_list=Db::name('plugin')->where($payment_where)->field('name,code')->select()->toArray();
        }
        if (input('act')=='submit_order'){
            if (empty($address_id)){
                return json(['code'=>0,'msg'=>'请选择收货地址']);
            }
            $user_address=new UserAddress();
            $add=$user_address->where(['user_id'=>$this->user_id,'address_id'=>$address_id])->find();
            if (empty($add)){
                return json(['code'=>0,'msg'=>'收货地址不存在']);
            }
            if ($this->user['jifen']<$order_amount){
                return json(['code'=>0,'msg'=>"积分不足,可用{$this->user['jifen']}!"]);
            }
            $pay_code=input('pay_code');
            if ($ship_price>0){
                if (empty($pay_code) || !in_array($pay_code,['alipayMobile','weixin','weixinH5'])){
                    return json(['code'=>0,'msg'=>"支付方式有误"]);
                }
                $jf_order_insert['pay_name']    =   Db::name('plugin')->where('code',$pay_code)->value('name');
            }else{
                $jf_order_insert['pay_name']    =   '积分';
                $jf_order_insert['pay_time']    =   time();
                $pay_code='jifen';
                $jf_order_insert['order_status']=   1;
            }
            $check=$this->request->checkToken('__token__');
            if (false===$check){
                return json(['code'=>0,'msg'=>"非法提交"]);
            }
            $jf_order_insert['order_sn']    =   getOrderSn('jifen_order');
            $jf_order_insert['type']        =   2;
            $jf_order_insert['user_id']     =   $this->user_id;
            $jf_order_insert['goods_id']    =   $goods['goods_id'];
            $jf_order_insert['goods_name']  =   $goods['goods_name'];
            $jf_order_insert['goods_img']   =   $goods['goods_img'];
            $jf_order_insert['goods_price'] =   $goods['goods_price'];
            $jf_order_insert['goods_num']   =   $goods_num;
            $jf_order_insert['total_amount']=   $total_amount;
            $jf_order_insert['ship_price']  =   $ship_price;
            $jf_order_insert['order_amount']=   $order_amount;
            $jf_order_insert['consignee']   =   $add['consignee'];
            $jf_order_insert['mobile']      =   $add['mobile'];
            $jf_order_insert['province']    =   $add['province'];
            $jf_order_insert['city']        =   $add['city'];
            $jf_order_insert['district']    =   $add['district'];
            $jf_order_insert['address']     =   $add['address'];
            $jf_order_insert['pid']         =   $add['pid'];//省份id
            $jf_order_insert['cid']         =   $add['cid'];//城市id
            $jf_order_insert['did']         =   $add['did'];//区/县id
            $jf_order_insert['user_note']   =   input('user_note','');
            $jf_order_insert['pay_code']    =   $pay_code;
            $jf_order=new JifenOrder();
            $res=$jf_order->save($jf_order_insert);
            $log=accountLog($this->user_id,-$order_amount,"兑换商品",2,0,$jf_order->order_id,$jf_order->order_sn);
            if ($ship_price>0){
                $url=url('Payment/get_code',['pay_code'=>$pay_code,'order_sn'=>$jf_order_insert['order_sn']])->build();
                $msg='跳转支付中~';
            }else{
                $url=url('Order/jifen_order_list')->build();
                $msg='兑换成功';
            }
            if ($res && $log){
                return json(['code'=>1,'msg'=>$msg,'url'=>$url]);
            }else{
                return json(['code'=>0,'msg'=>'兑换失败']);
            }
        }
        View::assign('total_amount',$total_amount);//订单总额
        View::assign('ship_price',$ship_price);//运费
        View::assign('order_amount',$order_amount);//应付
        View::assign('add',$add);
        View::assign('goods', $goods);
        View::assign('goods_num', $goods_num);
        View::assign('pay_list', $pay_list);
        return view();
    }
}
