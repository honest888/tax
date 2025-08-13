<?php
/**
 *@作者:MissZhang
 *@邮箱:<787727147@qq.com>
 *@创建时间:2021/7/9 上午10:32
 *@说明:订单控制器
 */

namespace app\mobile\controller;
use app\common\model\JifenOrder;
use think\facade\Db;
use think\facade\View;

class Order extends Base
{
    //订单列表
    public function order_list(){
        $status = input('status','all');
        View::assign('status', $status);
        return view();
    }
    //ajax加载订单
    public function ajax_order(){
        $p=input('page');
        $status=input('status');
        $order=new \app\common\model\Order();
        $where[]=['user_id','=',$this->user_id];
        if ($status!='all'){
            if ($status==-1){
                $where[]=['xd_uid','=',0];
                $where[]=['order_status','=',$status];
            }else{
                $where[]=['order_status','=',$status];
            }
        }else{
            $where[]=['xd_uid','=',0];
        }
        $count=$order->where($where)->count();
        $pages=ceil($count/10);
        $lists=$order->where($where)
            ->page($p,10)
            ->order('order_id desc')
            ->field('order_id,order_sn,pay_code,total_amount,order_status')
            ->select();
        $data=[];
        if (!$lists->isEmpty()){
            foreach ($lists as $key => $val){
                $order_url=url('Order/order_detail',['order_id'=>$val['order_id']])->build();
                $a='<div class="order-list"><div class="title"><div class="left">订单编号:'.$val['order_sn'].'</div>';
                $a.='<div class="right">'.$val['order_status_text'].'</div></div>';
                $a.='<a href="'.$order_url.'" class="order-list-list">';
                foreach ($val['order_goods'] as $k => $v){
                    $a.='<div class="order-list-item">';
                    $a.='<div class="left"><img src="'.$v['goods_img'].'"></div><div class="right"><p>'.$v['goods_name'].'</p>';
                    if ($v['item_name']){
                        $a.='<p class="ccc">'.$v['item_name'].'</p>';
                    }
                    $a.='<p><span class="price">￥'.$v['goods_price'].'</span><span>×'.$v['goods_num'].'</span></p></div></div>';
                }
                $a.='</a>';
                $a.='<div class="order-bottom"><div class="left">总价￥'.$val['total_amount'].'</div>';
                if ($val['order_status']>0){
                    $a.='<div class="right">';
                    if ($val['order_status']==2){
                        $a.='<button onclick="orderConfirm('.$val['order_id'].',this)" type="button">确认收货</button>';
                    }
                    $a.='</div>';
                }
                if ($val['order_status']==-1){
                    $a.='<div class="right">';
                    $a.='<button onclick="cancel_order('.$val['order_id'].',this)" class="cancel" type="button">取消订单</button>';
                    $a.='</div>';
                }
                $a.='</div></div>';
                $data[]=$a;
            }
        }
        return json(['pages'=>$pages,'data'=>$data]);
    }
    //团队订单列表
    public function td_order_list(){
        $user_id = input('user_id');
        View::assign('user_id', $user_id);
        return view();
    }
    //ajax加载订单
    public function ajax_td_order_list(){
        $p=input('page');
        $status=input('status');
        $user_id=input('user_id');
        $order=new \app\common\model\Order();
        if ($user_id){
            //判断是否在团队内
            $td_ids=get_td_ids($this->user_id);
            if (in_array($user_id,$td_ids)){
                $where[]=['user_id','=',$user_id];
            }else{
                return json(['pages'=>1,'data'=>[]]);
            }
        }else{
            $where[]=['user_id','=',$this->user_id];
        }
        if ($status!='all'){
            if ($status==-1){
                $where[]=['xd_uid','=',0];
                $where[]=['order_status','=',$status];
            }else{
                $where[]=['order_status','=',$status];
            }
        }else{
            $where[]=['xd_uid','=',0];
        }
        $count=$order->where($where)->count();
        $pages=ceil($count/10);
        $lists=$order->where($where)
            ->page($p,10)
            ->order('order_id desc')
            ->field('order_id,order_sn,pay_code,total_amount,order_status')
            ->select();
        $data=[];
        if (!$lists->isEmpty()){
            foreach ($lists as $key => $val){
                $order_url="javascript:;";
                $a='<div class="order-list"><div class="title"><div class="left">订单编号:'.$val['order_sn'].'</div>';
                $a.='<div class="right">'.$val['order_status_text'].'</div></div>';
                $a.='<a href="'.$order_url.'" class="order-list-list">';
                foreach ($val['order_goods'] as $k => $v){
                    $a.='<div class="order-list-item">';
                    $a.='<div class="left"><img src="'.$v['goods_img'].'"></div><div class="right"><p>'.$v['goods_name'].'</p>';
                    if ($v['item_name']){
                        $a.='<p class="ccc">'.$v['item_name'].'</p>';
                    }
                    $a.='<p><span class="price">￥'.$v['goods_price'].'</span><span>×'.$v['goods_num'].'</span></p></div></div>';
                }
                $a.='</a>';
                $a.='<div class="order-bottom"><div class="left">总价￥'.$val['total_amount'].'</div>';
                $a.='</div></div>';
                $data[]=$a;
            }
        }
        return json(['pages'=>$pages,'data'=>$data]);
    }
    //确定收货
    public function order_confirm(){
        $id = input('id/d', 0);
        $order=new \app\common\model\Order();
        $order=$order->where(['user_id'=>$this->user_id,'order_id'=>$id])->find();
        if (empty($order)){
            return json(['code'=>0,'msg'=>'订单不存在']);
        }
        if ($order['order_status']!=2){
            return json(['code'=>0,'msg'=>'该订单不能确认收货']);
        }
        $res=Db::name('order')->where('order_id',$id)->update(['order_status'=>3,'confirm_time'=>time()]);
        if ($res){
            return json(['code'=>1,'msg'=>'收货成功','url'=>url('Order/order_list')->build()]);
        }else{
            return json(['code'=>0,'msg'=>'收货失败']);
        }
    }
    //取消订单
    public function cancel_order(){
        $order_id = input('id/d', 0);
        $order=new \app\common\model\Order();
        $order=$order->where(['user_id'=>$this->user_id,'order_id'=>$order_id])->find();
        if (empty($order)){
            return json(['code'=>0,'msg'=>'订单不存在']);
        }
        if ($order['order_status']!=-1){
            return json(['code'=>0,'msg'=>'订单状态不允许取消']);
        }
        if ($order['reduce']==1){
            GoodsStoreCount($order_id,2);//减少销量增加库存
        }
        $row = $order->delete();
        Db::name('order_goods')->where('order_id',$order_id)->delete();
        if ($row){
            return json(['code'=>1,'msg'=>'操作成功','url'=>url('Order/order_list')->build()]);
        }else{
            return json(['code'=>0,'msg'=>'操作失败']);
        }
    }
    //订单详情
    public function order_detail(){
        $order_id = input('order_id/d', 0);
        $order=\app\common\model\Order::where(['user_id'=>$this->user_id,'order_id'=>$order_id])->find();
        if (empty($order)) {
            $this->error('订单不存在');
        }
        View::assign('order', $order);
        return view();
    }
    //物流信息
    public function logistics(){
        $order_id = input('order_id/d', 0);
        $order=\app\common\model\Order::where(['user_id'=>$this->user_id,'order_id'=>$order_id])->find();
        if (empty($order)) {
            $this->error('订单不存在');
        }
        $kuaidi=new KuaiDi100($this->app);
        $result=$kuaidi->query($order['shipping_code'],$order['shipping_number'],$order['mobile']);
        $list=[];
        if ($result['code']==1){
            $list=$result['result'];
        }
        View::assign('order', $order);
        View::assign('list', $list);
        return view();
    }
}