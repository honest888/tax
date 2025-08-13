<?php
/**
*@作者:MissZhang
*@邮箱:<787727147@qq.com>
*@创建时间:2021/8/9 上午9:47
*@说明:收益控制器
*/
namespace app\mobile\controller;


use app\common\model\AccountLog;
use app\common\model\JifenOrder;
use app\common\model\JlLog;
use app\common\model\Withdrawals;
use think\facade\Db;
use think\facade\View;

class Shouyi extends Base
{
    //首页
    public function index(){
        /*
          总收入：直推➕极差➕伯乐奖的所有收入
          本月收入：当月直推➕极差➕伯乐奖，每月初清零
          已提现就显示零钱的已提现
          待提现就显示当前零钱多少
         **/
        //本月收入 总收入
        $account_log=new AccountLog();
        $sr_where[]=['user_id','=',$this->user_id];
        $sr_where[]=['type','=',4];
        $sr_where[]=['desc','in',["直推奖励","团队奖励","伯乐奖励"]];
        $month_sr=$account_log->where($sr_where)->whereMonth('add_time')->sum('money');
        $zong_sr=$account_log->where($sr_where)->sum('money');
        //统计提现
        $with=new Withdrawals();
        $ytx=$with->whereRaw("user_id={$this->user_id} and status=1 and type=4")->sum('money');
        View::assign('month_sr',$month_sr);
        View::assign('zong_sr',$zong_sr);
        View::assign('ytx',$ytx);
        return view();
    }
    //收益账户明细
    public function hy_points(){
        //类型1今日2全部
        $type=input('type',1);
        View::assign('type',$type);
        return view();
    }
    //加载资金列表
    function ajax_hy_points(){
        //类型1今日2全部
        $type=input('type');
        $where[]=['user_id','=',$this->user_id];
        $where[]=['type','=',4];
        $where[]=['desc','in',['直推奖励','团队奖励','伯乐奖励']];
        $account_log=new AccountLog();
        $p=input('page');
        if ($type==1){
            $count=$account_log->where($where)->whereMonth('add_time')->count();
            $lists=$account_log
                ->where($where)
                ->whereMonth('add_time')
                ->order('log_id desc')
                ->page($p,20)
                ->field('money,add_time,desc')
                ->select();
        }else{
            $count=$account_log->where($where)->count();
            $lists=$account_log
                ->where($where)
                ->order('log_id desc')
                ->page($p,20)
                ->field('money,add_time,desc')
                ->select();
        }
        $pages=ceil($count/20);
        $data=[];
        if ($lists){
            foreach ($lists as $key => $val){
                if ($val['money']>0){
                    $money='+'.$val['money'];
                }else{
                    $money=$val['money'];
                }
                $a='<div class="money-item">
                        <div class="left">
                            <h5>'.$val['desc'].'</h5>
                            <p>'.$val['add_time'].'</p>
                        </div>
                        <div class="right">'.$money.'</div>
                    </div>';

                $data[]=$a;
            }
        }
        return json(['pages'=>$pages,'data'=>$data]);
    }
    //ajax请求商品
    public function ajax_index(){
        $cate_id=input('cate_id',0);
        if ($cate_id){
            $where[]=['cat_id_1','=',$cate_id];
        }
        $where[]=['is_on_sale','=',1];
        $count=Db::name('goods')->where($where)->count();
        $pages=ceil($count/10);
        $lists=Db::name('goods')->where($where)
            ->field('goods_id,goods_img,goods_price,goods_name')
            ->order('sort asc,goods_id desc')
            ->select();
        $data=[];
        if ($lists){
            foreach ($lists as $key => $val){
                $img=$val['goods_img'] ? $val['goods_img'] : '/public/images/not_adv.jpg';
                $a='<a href="'.url('Goods/goods_info',['id'=>$val['goods_id']]).'" class="list">';
                $a.='<dl>';
                $a.='<dt class="img"><img src="'.$img.'"></dt>';
                $a.='<dt class="goods_name">'.$val['goods_name'].'</dt>';
                $a.='<dd><span class="red">￥<span>'.$val['goods_price'].'</span></span></dd>';
                $a.='</dl>';
                $a.='</a>';
                $data[]=$a;
            }
        }
        return json(['pages'=>$pages,'data'=>$data]);
    }
    //区域代理
    public function qy_daili(){
        //统计订单
        $order=new JifenOrder();
        //今日订单数 包含待付款
        $day_order_where[]=['sid','=',$this->user_id];
        $day_order=$order->where($day_order_where)->whereDay('pay_time')->count();
        //待付款
        $dfk_where[]=['sid','=',$this->user_id];
        $dfk_where[]=['order_status','=',-1];
        $dfk=$order->where($dfk_where)->count();
        //待发货
        $dfh_where[]=['sid','=',$this->user_id];
        $dfh_where[]=['order_status','=',1];
        $dfh=$order->where($dfh_where)->count();
        //待收货
        $dsh_where[]=['sid','=',$this->user_id];
        $dsh_where[]=['order_status','=',2];
        $dsh=$order->where($dsh_where)->count();
        //已完成
        $ywc_where[]=['sid','=',$this->user_id];
        $ywc_where[]=['order_status','=',3];
        $ywc=$order->where($ywc_where)->count();
        //今日收入 总收入
        $account_log=new AccountLog();
        $sr_where[]=['user_id','=',$this->user_id];
        $sr_where[]=['type','=',3];
        $sr_where[]=['desc','like',["%代理奖励"]];
        $day_sr=$account_log->where($sr_where)->whereDay('add_time')->sum('money');
        $zong_sr=$account_log->where($sr_where)->sum('money');
        //计算省级 市级 县级奖励
        $jl_log=new JlLog();
        $sheng_where[]=['user_id','=',$this->user_id];
        $sheng_where[]=['level','=',1];
        $sheng_jl=$jl_log->where($sheng_where)->sum('money');
        $shi_where[]=['user_id','=',$this->user_id];
        $shi_where[]=['level','=',2];
        $shi_jl=$jl_log->where($shi_where)->sum('money');
        $xian_where[]=['user_id','=',$this->user_id];
        $xian_where[]=['level','=',3];
        $xian_jl=$jl_log->where($xian_where)->sum('money');
        View::assign('day_order',$day_order);
        View::assign('dfk',$dfk);
        View::assign('dfh',$dfh);
        View::assign('dsh',$dsh);
        View::assign('ywc',$ywc);
        View::assign('day_sr',$day_sr);
        View::assign('zong_sr',$zong_sr);
        View::assign('sheng_jl',$sheng_jl);
        View::assign('shi_jl',$shi_jl);
        View::assign('xian_jl',$xian_jl);
        return view();
    }
    //代理账户明细
    public function dl_points(){
        //类型1今日2全部
        $type=input('type',1);
        View::assign('type',$type);
        return view();
    }
    //加载资金列表
    function ajax_dl_points(){
        //类型1今日2全部
        $type=input('type');
        $where[]=['user_id','=',$this->user_id];
        $where[]=['type','=',3];
        $where[]=['desc','like',["%代理奖励"]];
        $account_log=new AccountLog();
        $p=input('page');
        if ($type==1){
            $count=$account_log->where($where)->whereDay('add_time')->count();
            $lists=$account_log
                ->where($where)
                ->whereDay('add_time')
                ->order('log_id desc')
                ->page($p,20)
                ->field('money,add_time,desc')
                ->select();
        }else{
            $count=$account_log->where($where)->count();
            $lists=$account_log
                ->where($where)
                ->order('log_id desc')
                ->page($p,20)
                ->field('money,add_time,desc')
                ->select();
        }
        $pages=ceil($count/20);
        $data=[];
        if ($lists){
            foreach ($lists as $key => $val){
                if ($val['money']>0){
                    $money='+'.$val['money'];
                }else{
                    $money=$val['money'];
                }
                $a='<div class="money-item">
                        <div class="left">
                            <h5>'.$val['desc'].'</h5>
                            <p>'.$val['add_time'].'</p>
                        </div>
                        <div class="right">'.$money.'</div>
                    </div>';

                $data[]=$a;
            }
        }
        return json(['pages'=>$pages,'data'=>$data]);
    }
    //ajax加载发货订单
    public function ajax_send_order(){
        $p=input('page');
        $status=input('status',-1);
        $keyword=input('keyword');
        $order=new JifenOrder();
        $where[]=['sid','=',$this->user_id];
        $where[]=['type','=',1];
        $where[]=['order_status','=',$status];
        if ($keyword){
            $where[]=['order_sn|mobile|shipping_code','like',"%$keyword%"];
        }
        $count=$order->where($where)->count();
        $pages=ceil($count/10);
        $lists=$order->where($where)
            ->page($p,10)
            ->order('order_id desc')
            ->field('order_id,user_id,order_sn,goods_img,goods_name,item_name,goods_price,goods_num,pay_name,total_amount,order_status,add_time')
            ->select();
        $data=[];
        if (!$lists->isEmpty()){
            foreach ($lists as $key => $val){
                $url=url('Shouyi/order_send',['order_id'=>$val['order_id']]);
                $a='<div class="list-item">
                        <div class="list-item-top">
                            <div class="left">
                                <img src="'.$val['user']['head_pic'].'">
                                <p>'.$val['user']['realname'].'</p>
                            </div>
                            <div class="right">'.$val['pay_name'].'</div>
                        </div>
                        <div class="list-goods">
                            <div class="list-goods-item">
                                <div class="left">
                                    <img src="'.$val['goods_img'].'">
                                </div>
                                <div class="right">
                                    <div class="right-item">
                                        <h5 class="goods-name">'.$val['goods_name'].'</h5>
                                        <p>'.$val['item_name'].'</p>
                                    </div>
                                    <div class="right-item">
                                        <h5 class="goods-price">¥'.$val['goods_price'].'</h5>
                                        <p>x'.$val['goods_num'].'</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="flex-item">
                            <div class="left">订单编号:'.$val['order_sn'].'</div>
                            <div class="right">成交日期:'.$val['time_text'].'</div>
                        </div>
                        <div class="flex-item">
                            <div class="left">共'.$val['goods_num'].'件商品</div>
                            <div class="right">总计:<span class="zong">¥'.$val['total_amount'].'</span></div>
                        </div>';
                        $a.='<div class="bot-item">';
                        if ($val['order_status']==1){
                            $a.='<a href="'.$url.'" class="bot-item-btn">确认发货</a>';
                        }
                        $a.='<a href="tel:'.$val['user']['mobile'].'" class="bot-item-btn">联系买家</a>
                        </div>
                    </div>';
                $data[]=$a;
            }
        }
        return json(['pages'=>$pages,'data'=>$data]);
    }
    //订单发货
    public function order_send(){
        $order_id = input('order_id/d', 0);
        $order=JifenOrder::where(['sid'=>$this->user_id,'order_id'=>$order_id])->find();
        if (empty($order)) {
            $this->error('订单不存在');
        }
        if (IS_POST){
            $shipping_code=input('shipping_code');//快递编码
            $shipping_name=input('shipping_name');//快递公司
            $shipping_number=input('shipping_number');//快递单号
            if (empty($shipping_code)){
                return json(['code'=>0,'msg'=>'请选择快递编码']);
            }
            if (empty($shipping_name)){
                return json(['code'=>0,'msg'=>'快递公司不能为空']);
            }
            if (empty($shipping_number)){
                return json(['code'=>0,'msg'=>'快递单号不能为空']);
            }
            $check=$this->request->checkToken('__token__');
            if (false===$check){
                return json(['code'=>0,'msg'=>"非法提交"]);
            }
            $update['shipping_code']=$shipping_code;
            $update['shipping_name']=$shipping_name;
            $update['shipping_number']=$shipping_number;
            if ($order->order_status==1){
                $update['shipping_time']=time();
                $update['order_status']=2;
            }
            $res=$order->save($update);
            if ($res){
                return json(['code'=>1,'msg'=>'发货成功','url'=>url('Shouyi/qy_daili')->build()]);
            }else{
                return json(['code'=>0,'msg'=>'发货失败']);
            }
        }
        $shipping=config('app.shipping');
        View::assign('shipping', $shipping);
        View::assign('order', $order);
        return view();
    }
    //订单管理
    public function td_order(){
        $status = input('status','all');
        View::assign('status', $status);
        return view();
    }
    //ajax加载订单管理
    public function ajax_td_order(){
        $p=input('page');
        $status=input('status');
        $order=new JifenOrder();
        //$ids=get_node_ids($this->user_id);
        $ids=Db::name('users')->where('reid',$this->user_id)->column('user_id');
        $where[]=['user_id','in',$ids];
        $where[]=['type','=',1];
        if ($status!='all'){
            $where[]=['order_status','=',$status];
        }
        $count=$order->where($where)->count();
        $pages=ceil($count/10);
        $lists=$order->where($where)
            ->page($p,10)
            ->order('order_id desc')
            ->field('order_id,user_id,order_sn,goods_img,goods_name,item_name,goods_price,goods_num,pay_name,total_amount,order_status,add_time')
            ->select();
        $data=[];
        if (!$lists->isEmpty()){
            foreach ($lists as $key => $val){
                $a='<div class="list-item">
                        <div class="list-item-top">
                            <div class="left">
                                <img src="'.$val['user']['head_pic'].'">
                                <p>'.$val['user']['realname'].'</p>
                            </div>
                            <div class="right">'.$val['pay_name'].'</div>
                        </div>
                        <div class="list-goods">
                            <div class="list-goods-item">
                                <div class="left">
                                    <img src="'.$val['goods_img'].'">
                                </div>
                                <div class="right">
                                    <div class="right-item">
                                        <h5 class="goods-name">'.$val['goods_name'].'</h5>
                                        <p>'.$val['item_name'].'</p>
                                    </div>
                                    <div class="right-item">
                                        <h5 class="goods-price">¥'.$val['goods_price'].'</h5>
                                        <p>x'.$val['goods_num'].'</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="flex-item">
                            <div class="left">订单编号:'.$val['order_sn'].'</div>
                            <div class="right">成交日期:'.$val['time_text'].'</div>
                        </div>
                        <div class="flex-item">
                            <div class="left">共'.$val['goods_num'].'件商品</div>
                            <div class="right">总计:<span class="zong">¥'.$val['total_amount'].'</span></div>
                        </div>';
                $a.='<div class="bot-item">';
                $a.='<a href="tel:'.$val['user']['mobile'].'" class="bot-item-btn">联系买家</a>
                        </div>
                    </div>';
                $data[]=$a;
            }
        }
        return json(['pages'=>$pages,'data'=>$data]);
    }
}
