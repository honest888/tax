<?php
/**
 *@作者:MissZhang
 *@邮箱:<787727147@qq.com>
 *@创建时间:2021/7/8 上午9:24
 *@说明:购物车控制器
 */

namespace app\mobile\controller;


use app\common\model\JifenOrder;
use think\facade\Db;
use think\facade\View;

class Cart extends Base
{
    //购物车首页
    public function index(){
        $user_id=$this->user_id;
        if ($user_id==0){
            return $this->error('请先登录',url('User/login'));
        }
        $cartlist=Db::name('cart')->where('user_id',$user_id)->select()->toArray();
        $amount=0;
        if ($cartlist){
            foreach ($cartlist as $key => $cart){
                $good=Db::name('goods')->where('goods_id='.$cart['goods_id'].' and is_on_sale=1')->find();
                $item=1;
                if ($cart['item_id']){
                    $item=Db::name('spec_goods_price')->where('item_id='.$cart['item_id'])->find();
                }
                if (empty($good) || empty($item)){//清除被删除的商品
                    Db::name('cart')->where('id',$cart['id'])->delete();
                    unset($cartlist[$key]);
                }else{
                    if (is_array($item)){
                        $cartlist[$key]['max']=$item['store_count'];
                        $img=$item['spec_img'];
                    }else{
                        $cartlist[$key]['max']=$good['store_count'];
                    }
                    $cartlist[$key]['img']= $img ? $img : $good['goods_img'];
                    if ($cart['selected']==1){
                        //初始计算金额
                        $amount+=$cart['goods_price']*$cart['goods_num'];
                    }
                }
            }
        }
        View::assign('cartList', $cartlist);
        View::assign('amount', $amount);
        return view();
    }
    public function do_cart(){
        $carts=input('arr/a');
        if ($this->user_id==0){
            return json(['code'=>-100,'msg'=>'请先登录','url'=>url('User/login')->build()]);
        }
        if (empty($carts)){
            return json(['code'=>0,'msg'=>'请选择商品']);
        }
        $amount=0;
        foreach ($carts as $k => $v){
            $cart=Db::name('cart')->where('id='.$v['cart_id'].' and user_id='.$this->user_id)->find();
            if (empty($cart)){
                return json(['code'=>0,'msg'=>'非法操作']);
            }
            $good=Db::name('goods')->where('goods_id='.$cart['goods_id'].' and is_on_sale=1')->find();
            $item=1;
            if ($cart['item_id']){
                $item=Db::name('spec_goods_price')->where('item_id='.$cart['item_id'])->find();
            }
            if (empty($good) || empty($item)){
                Db::name('cart')->where('id',$v['cart_id'])->delete();
                return json(['code'=>-100,'msg'=>$cart['goods_name'].'商品不存在或已下架','url'=>url('Cart/index')->build()]);
            }
            $id=$v['cart_id'];
            unset($v['cart_id']);
            Db::name('cart')->where('id',$id)->update($v);
            if ($v['selected']==1){
                $amount+=$cart['goods_price']*$v['goods_num'];
            }
        }
        return json(['code'=>1,'amount'=>$amount]);
    }
    //删除购物车商品
    public function delete(){
        $cart_ids = input('ids/a',[]);
        $result = Db::name('cart')->where('id','in',implode(',',$cart_ids))->delete();
        if($result !== false){
            return json(['code'=>1,'msg'=>'删除成功','url'=>url('Cart/index')->build()]);
        }else{
            return json(['code'=>0,'msg'=>'删除失败']);
        }
    }
    //将商品加入购物车
    function cart_add()
    {
        $goods_id = input("goods_id/d"); // 商品id
        $item_id = input("item_id/d"); // 规格表id
        $goods_num = input("goods_num/d");// 商品数量
        if ($this->user_id==0){
            return json(['code'=>-100,'msg'=>'请先登录','url'=>url('User/login')->build()]);
        }
        if(empty($goods_id)){
            return json(['code'=>0,'msg'=>'请选择要购买的商品']);
        }
        if(empty($goods_num)){
            return json(['code'=>0,'msg'=>'购买商品数量不能为0']);
        }
        $good=Db::name('goods')->where('goods_id',$goods_id)->find();
        $map['user_id']=$this->user_id;
        $map['goods_id']=$goods_id;
        $map['goods_sn']=$good['goods_sn'];
        $map['goods_name']=$good['goods_name'];
        if ($item_id){
            $item=Db::name('spec_goods_price')->where('item_id',$item_id)->find();
            $map['item_id']=$item_id;
            $map['item_name']=$item['key_name'];
            $map['goods_price']=$item['price'];
        }else{
            $map['goods_price']=$good['goods_price'];
        }
        $map['goods_num']=$goods_num;
        $map['add_time']=time();
        $where['user_id']=$this->user_id;
        $where['goods_id']=$goods_id;
        $item_id && $where['item_id']=$item_id;
        $cart=Db::name('cart')->where($where)->find();
        if ($cart){
            $res=Db::name('cart')->where($where)->inc('goods_num',$goods_num)->update();
        }else{
            $res=Db::name('cart')->insert($map);
        }
        if ($res){
            return json(['code'=>1,'msg'=>'加入购物车成功']);
        }else{
            return json(['code'=>0,'msg'=>'加入购物车失败']);
        }
    }
    //购物车第二步确定页面
    public function cart(){
        $goods_id = input("goods_id/d"); // 商品id
        $goods_num = input("goods_num/d");// 商品数量
        $address_id=input('address_id');
        $action=input('action');
        if ($this->user_id == 0){
            $this->redirect(url('User/login'));
        }
        if ($address_id){
            $add=Db::name('user_address')->where('address_id',$address_id)->find();
        }else{
            $add=Db::name('user_address')->where('user_id='.$this->user_id.' and is_default=1')->find();
        }
        $user=get_user_info($this->user_id);
        $goods_model=new \app\common\model\Goods();
        $total_amount=0;
        if ($action=='buy_now'){
            $goods_where[]=['is_on_sale','=',1];
            $goods_where[]=['goods_id','=',$goods_id];
            $goods=$goods_model->where($goods_where)->find();
            if (empty($goods)){
                $this->error('商品不存在或已下架');
            }
            if ($goods_num>$goods['store_count']){
                $this->error('商品已售尽');
            }
            $level_price=$goods->getDengjiPrice($this->user['level']);
            $cartList[0]['user_id']=$this->user_id;
            $cartList[0]['goods_id']=$goods_id;
            $cartList[0]['item_id']=0;
            $cartList[0]['goods_sn']=$goods['goods_sn'];
            $cartList[0]['goods_name']=$goods['goods_name'];
            $cartList[0]['goods_price']=$level_price;
            $cartList[0]['goods_num']=$goods_num;
            $cartList[0]['img']=$goods['goods_img'];
            $total_amount=$cartList[0]['goods_price']*$goods_num;
        }else{
            $cartList=Db::name('cart')->where('user_id='.$this->user_id.' and selected=1')->select()->toArray();
            if (empty($cartList)){
                $this->error('购物车没有选中商品',url('Index/index'));
            }
            foreach ($cartList as $key => $v){
                $goods_where[]=['is_on_sale','=',1];
                $goods_where[]=['goods_id','=',$v['goods_id']];
                $goods=$goods_model->where($goods_where)->find();
                unset($goods_where);
                if (empty($goods)){
                    $this->error($goods['goods_name'].'商品不存在或已下架');
                }
                if ($v['goods_num']>$goods['store_count']){
                    $this->error('商品已售尽');
                }
                $level_price=$goods->getDengjiPrice($this->user['level']);
                $cartList[$key]['img']=$goods['goods_img'];
                $cartList[$key]['goods_price']=$level_price;
                $total_amount+=$level_price*$v['goods_num'];
            }
        }
        $order_amount=$total_amount;
        $pay_list[]=array(
            'code'  =>  'yue',
            'name'  =>  '余额(可用'.$user['user_money'].')',
        );
        $payment_where[]=['status','=',1];
        if(strstr($_SERVER['HTTP_USER_AGENT'],'MicroMessenger')){
            //微信浏览器
            $payment_where[] = ['code','=','weixin'];
        }else{
            $payment_where[] =['code','in',['alipayMobile','weixinH5']];
        }
        $payment=Db::name('plugin')->where($payment_where)->field('name,code')->select()->toArray();
        $payment && $pay_list=array_merge($pay_list,$payment);
        if (input('act')=='submit_order'){
            $data=input('post.');
            if ($this->user_id == 0){
                return json(['code'=>-100,'msg'=>'请先登录']);
            }
            $xd_uid=input('xd_uid');
            $user_id=$this->user_id;
            $xd_id=0;
            //如果当前会员为普通会员&$xd_uid有值
            if ($this->user['level']==1){
                if ($xd_uid){
                    return json(['code'=>0,'msg'=>'非法提交']);
                }
            }else{
                if ($xd_uid){
                    if ($xd_uid==$this->user['mobile']){
                        return json(['code'=>0,'msg'=>'购买会员不能为自己']);
                    }
                    //先看看用户存在不存在
                    $xd_user=get_user_info($xd_uid,1);
                    if (empty($xd_user)){
                        return json(['code'=>0,'msg'=>'购买会员不存在']);
                    }
                    //只能是普通会员
                    if ($xd_user['level']!=1){
                        return json(['code'=>0,'msg'=>'不允许为此会员购买']);
                    }
                    //判断是否在团队内
                    $td_ids=get_td_ids($this->user_id);
                    if (!in_array($xd_user['user_id'],$td_ids)){
                        return json(['code'=>0,'msg'=>'只能为自己团队的会员下单']);
                    }
                    $user_id=$xd_user['user_id'];
                    $xd_id=$this->user_id;
                }
            }
            //地址信息
            $address = Db::name('user_address')->where("address_id", $data['address_id'])->find();
            if (empty($address)){
                return json(['code'=>0,'msg'=>'请先选择收货地址']);
            }
            if (!in_array($data['pay_code'],['yue','weixin','alipayMobile','weixinH5'])){
                return json(['code'=>0,'msg'=>'支付方式有误']);
            }
            switch ($data['pay_code']){
                case 'yue':
                    if ($user['user_money']<$order_amount){
                        $str=sprintf('余额不足,可用%s!',$user['user_money']);
                        return json(['code'=>0,'msg'=>$str]);
                    }
                    $pay['pay_name']        =   '余额';
                    $pay['pay_time']        =   time();
                    $pay['order_status']    =   1;
                    break;
                default:
                    $pay_name=Db::name('plugin')->where('code',$data['pay_code'])->value('name');
                    $pay['pay_name']=$pay_name;
                    break;
            }
            $pay['pay_code']            =   $data['pay_code'];
            $pay['reduce']              =   getSysConfig('shopping.reduce');
            $pay['order_sn']            =   getOrderSn();//订单编号
            $pay['user_id']             =   $user_id;//用户id
            $pay['xd_uid']              =   $xd_id;//帮下单用户id
            $pay['consignee']           =   $address['consignee'];//收货人
            $pay['mobile']              =   $address['mobile'];//手机
            $pay['province']            =   $address['province'];//省份
            $pay['city']                =   $address['city'];//城市
            $pay['district']            =   $address['district'];//县区
            $pay['address']             =   $address['address'];//详细地址
            $pay['user_note']           =   $data['user_note'];//用户备注
            $pay['total_amount']        =   $total_amount;//订单总价
            $pay['order_amount']        =   $order_amount;//应付款金额
            $order=new \app\common\model\Order();
            $res=$order->save($pay);
            if ($res){
                foreach ($cartList as $k1 => $v1){
                    $goods2=Db::name('goods')->where('goods_id',$v1['goods_id'])->find();
                    $order_goods['order_id']        =   $order->order_id;
                    $order_goods['goods_id']        =   $goods2['goods_id'];
                    $order_goods['goods_name']      =   $goods2['goods_name'];
                    $order_goods['goods_img']       =   $v1['img'];
                    $order_goods['goods_sn']        =   $goods2['goods_sn'];
                    $order_goods['goods_num']       =   $v1['goods_num'];
                    $order_goods['goods_price']     =   $v1['goods_price'];
                    if (in_array($data['pay_code'],['yue'])){
                        $order_goods['is_zhifu']        =   1;
                        $order_goods['pay_time']        =   time();
                    }
                    if ($v1['id']){
                        //删除购物车
                        Db::name('cart')->where('id',$v1['id'])->delete();
                    }
                    //插入订单商品表
                    Db::name('order_goods')->insert($order_goods);
                }
                if ($pay['pay_code']=='yue'){//如果余额支付直接扣除库存
                    GoodsStoreCount($order->order_id);//减少库存增加销量
                    $url=url('Order/order_list')->build();
                    $msg='支付成功';
                    //扣除余额
                    accountLog($this->user_id,-$order_amount,'购物消费',1,0,$order->order_id,$pay['order_sn']);
                    //发放奖励
                    jl_base($order->order_id);
                }else{
                    if (getSysConfig('shopping.reduce')==1){
                        GoodsStoreCount($order->order_id);//减少库存增加销量
                    }
                    $url=url('Payment/get_code',['pay_code'=>$pay['pay_code'],'order_sn'=>$pay['order_sn']])->build();
                    $msg='跳转支付中~';
                }
                return json(['code'=>200,'msg'=>$msg,'url'=>$url]);
            }else{
                return json(['code'=>0,'msg'=>'订单创建失败']);
            }
        }
        View::assign('cartList',$cartList);
        View::assign('total_amount',$total_amount);//订单总额
        View::assign('order_amount',$order_amount);//应付
        View::assign('add',$add);
        View::assign('pay_list',$pay_list);
        return view();
    }
    //购物车第二步确定页面
    public function cart11(){
        $goods_id = input("goods_id/d"); // 商品id
        $goods_num = input("goods_num/d");// 商品数量
        $item_id = input("item_id/d",0); // 规格表id
        $address_id=input('address_id');
        $action=input('action');
        if ($this->user_id == 0){
            $this->redirect(url('User/login'));
        }
        if ($address_id){
            $add=Db::name('user_address')->where('address_id',$address_id)->find();
        }else{
            $add=Db::name('user_address')->where('user_id='.$this->user_id.' and is_default=1')->find();
        }
        $user=get_user_info($this->user_id);
        $jifen=$zt_jl=$jt_jl=0;
        $total_amount=0;
        if ($action=='buy_now'){
            if ($item_id){
                $good=Db::name('spec_goods_price')->where('item_id='.$item_id)->find();
                $goods_name=Db::name('goods')->where('goods_id='.$goods_id.' and is_on_sale=1')->value('goods_name');
                if (!$good['spec_img']){
                    $good['spec_img']=Db::name('goods')->where('goods_id='.$goods_id.' and is_on_sale=1')->value('goods_img');
                }
                $good_sn=Db::name('goods')->where('goods_id='.$goods_id.' and is_on_sale=1')->value('goods_sn');
            }else{
                $good=Db::name('goods')->where('goods_id='.$goods_id.' and is_on_sale=1')->find();
            }
            if (empty($good)){
                $this->error('商品不存在或已下架');
            }
            if ($goods_num>$good['store_count']){
                $this->error('商品已售尽');
            }
            $cartList[0]['user_id']=$this->user_id;
            $cartList[0]['goods_id']=$goods_id;
            $cartList[0]['item_id']=$item_id;
            $cartList[0]['goods_sn']=$good_sn ? $good_sn : $good['goods_sn'];
            $cartList[0]['goods_name']=$goods_name? $goods_name : $good['goods_name'];
            if ($good['key_name']){
                $cartList[0]['item_name']=$good['key_name'];
            }
            $cartList[0]['goods_price']=$good['goods_price'] ? $good['goods_price'] : $good['price'];
            $cartList[0]['goods_num']=$goods_num;
            $cartList[0]['img']=$good['spec_img'] ? $good['spec_img'] : $good['goods_img'];
            $order_good=Db::name('goods')->where('goods_id='.$goods_id)->find();
            //计算赠送积分
            $jifen=$order_good['jifen']*$goods_num;
            //计算直推奖励
            $zt_jl=($order_good['goods_price']*($order_good['zt_jl_bili']/100))*$goods_num;
            //计算间推奖励
            $jt_jl=($order_good['goods_price']*($order_good['jt_jl_bili']/100))*$goods_num;
            $total_amount=$cartList[0]['goods_price']*$goods_num;
        }else{
            $cartList=Db::name('cart')->where('user_id='.$this->user_id.' and selected=1')->select()->toArray();
            if (empty($cartList)){
                $this->error('购物车没有选中商品',url('Index/index'));
            }
            foreach ($cartList as $key => $v){
                if ($v['item_id']){
                    $goods=Db::name('spec_goods_price')->where('item_id='.$v['item_id'])->find();
                }else{
                    $goods=Db::name('goods')->where('goods_id='.$v['goods_id'].' and is_on_sale=1')->find();
                }
                $goods1=Db::name('goods')->where('goods_id='.$v['goods_id'])->find();
                if (empty($goods)){
                    $this->error($goods1['goods_name'].'商品不存在或已下架');
                }
                if ($v['goods_num']>$goods['store_count']){
                    $this->error('商品已售尽');
                }
                $cartList[$key]['img']=$goods['spec_img']? $goods['spec_img'] :$goods['goods_img'];
                $total_amount+=$v['goods_price']*$v['goods_num'];
                //计算赠送积分
                $jifen+=$goods1['jifen']*$v['goods_num'];
                //计算直推奖励
                $zt_jl+=($goods1['goods_price']*($goods1['zt_jl_bili']/100))*$v['goods_num'];
                //计算间推奖励
                $jt_jl+=($goods1['goods_price']*($goods1['jt_jl_bili']/100))*$v['goods_num'];
            }
        }
        $order_amount=$total_amount;
        //计算推荐人赠送积分
        $reid_jifen=0;
        $shop_given_reid_jifen=getSysConfig('rate.shop_given_reid_jifen');
        if ($shop_given_reid_jifen>0){
            $shop_jifen=$total_amount*($shop_given_reid_jifen/100);
            $reid_jifen=$shop_jifen*$goods_num;
        }
        $pay_list[]=array(
            'code'  =>  'yue',
            'name'  =>  '余额(可用'.$user['user_money'].')',
        );
        $payment_where[]=['status','=',1];
        if(strstr($_SERVER['HTTP_USER_AGENT'],'MicroMessenger')){
            //微信浏览器
            $payment_where[] = ['code','=','weixin'];
        }else{
            $payment_where[] =['code','in',['alipayMobile','weixinH5']];
        }
        $payment=Db::name('plugin')->where($payment_where)->field('name,code')->select()->toArray();
        $payment && $pay_list=array_merge($pay_list,$payment);
        if (input('act')=='submit_order'){
            $data=input('post.');
            if ($this->user_id == 0){
                return json(['code'=>-100,'msg'=>'请先登录']);
            }
            //地址信息
            $address = Db::name('user_address')->where("address_id", $data['address_id'])->find();
            if (empty($address)){
                return json(['code'=>0,'msg'=>'请先选择收货地址']);
            }
            if (!in_array($data['pay_code'],['yue','weixin','alipayMobile','weixinH5'])){
                return json(['code'=>0,'msg'=>'支付方式有误']);
            }
            switch ($data['pay_code']){
                case 'yue':
                    if ($user['user_money']<$order_amount){
                        $str=sprintf('余额不足,可用%s!',$user['user_money']);
                        return json(['code'=>0,'msg'=>$str]);
                    }
                    $pay['pay_name']        =   '余额';
                    $pay['pay_time']        =   time();
                    $pay['order_status']    =   1;
                    break;
                default:
                    $pay_name=Db::name('plugin')->where('code',$data['pay_code'])->value('name');
                    $pay['pay_name']=$pay_name;
                    break;
            }
            $pay['pay_code']            =   $data['pay_code'];
            $pay['reduce']              =   getSysConfig('shopping.reduce');
            $pay['order_sn']            =   getOrderSn();//订单编号
            $pay['user_id']             =   $this->user_id;//用户id
            $pay['consignee']           =   $address['consignee'];//收货人
            $pay['mobile']              =   $address['mobile'];//手机
            $pay['province']            =   $address['province'];//省份
            $pay['city']                =   $address['city'];//城市
            $pay['district']            =   $address['district'];//县区
            $pay['address']             =   $address['address'];//详细地址
            $pay['user_note']           =   $data['user_note'];//用户备注
            $pay['total_amount']        =   $total_amount;//订单总价
            $pay['order_amount']        =   $order_amount;//应付款金额
            $pay['jifen']               =   $jifen;//赠送积分
            $pay['reid_jifen']          =   $reid_jifen;//推荐人赠送积分
            $pay['zt_jl']               =   $zt_jl;//直推奖励
            $pay['jt_jl']               =   $jt_jl;//间推奖励
            $pay['add_time']            =   time();//下单时间
            $res=Db::name('order')->insertGetId($pay);
            if ($res){
                foreach ($cartList as $k1 => $v1){
                    $goods2=Db::name('goods')->where('goods_id',$v1['goods_id'])->find();
                    $order_goods['order_id']        =   $res;
                    $order_goods['goods_id']        =   $goods2['goods_id'];
                    $order_goods['goods_name']      =   $goods2['goods_name'];
                    $order_goods['goods_img']       =   $v1['img'];
                    $order_goods['goods_sn']        =   $goods2['goods_sn'];
                    $order_goods['goods_num']       =   $v1['goods_num'];
                    $order_goods['goods_price']     =   $goods2['goods_price'];
                    $order_goods['jifen']           =   $goods2['jifen'];
                    $order_goods['zt_jl_bl']        =   $goods2['zt_jl_bili'];
                    $order_goods['zt_jl']           =   ($goods2['goods_price']*($goods2['zt_jl_bili']/100))*$v1['goods_num'];
                    $order_goods['jt_jl_bl']        =   $goods2['jt_jl_bili'];
                    $order_goods['jt_jl']           =   ($goods2['goods_price']*($goods2['jt_jl_bili']/100))*$v1['goods_num'];
                    if (in_array($data['pay_code'],['yue'])){
                        $order_goods['is_zhifu']        =   1;
                        $order_goods['pay_time']        =   time();
                    }
                    if ($v1['id']){
                        //删除购物车
                        Db::name('cart')->where('id',$v1['id'])->delete();
                    }
                    //插入订单商品表
                    Db::name('order_goods')->insert($order_goods);
                }
                if ($pay['pay_code']=='yue'){//如果余额支付直接扣除库存
                    GoodsStoreCount($res);//减少库存增加销量
                    $url=url('Order/order_list')->build();
                    $msg='支付成功';
                    //扣除余额
                    accountLog($this->user_id,-$order_amount,'购物消费',1,0,$res,$pay['order_sn']);
                    //发放奖励
                    jl_base($res);
                }else{
                    if (getSysConfig('shopping.reduce')==1){
                        GoodsStoreCount($res);//减少库存增加销量
                    }
                    $url=url('Payment/get_code',['pay_code'=>$pay['pay_code'],'order_sn'=>$pay['order_sn']])->build();
                    $msg='跳转支付中~';
                }
                return json(['code'=>1,'msg'=>$msg,'url'=>$url]);
            }else{
                return json(['code'=>0,'msg'=>'订单创建失败']);
            }
        }
        View::assign('cartList',$cartList);
        View::assign('total_amount',$total_amount);//订单总额
        View::assign('order_amount',$order_amount);//应付
        View::assign('add',$add);
        View::assign('pay_list',$pay_list);
        View::assign('jifen',$jifen);
        return view();
    }
}