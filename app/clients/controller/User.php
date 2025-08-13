<?php
/**
 *@作者:MissZhang
 *@邮箱:<787727147@qq.com>
 *@创建时间:2021/7/7 下午6:07
 *@说明:用户控制器
 */
namespace app\clients\controller;


use app\common\model\AccountLog;
use app\common\model\Given;
use app\common\model\JlLog;
use app\common\model\Recharge;
use app\common\model\UserLevel;
use app\common\model\Users;
use app\common\model\UserSign;
use app\common\model\Withdrawals;
use app\clients\validate\PublicValidate;
use think\exception\ValidateException;
use think\facade\Db;
use think\facade\Filesystem;
use think\facade\View;
use think\Image;

class User extends Base
{
    protected function initialize()
    {
        //die();
        parent::initialize();
    }
    /*增加client 界面 1*/
    public function addclient(){
        $user = session('user');
        if (IS_POST) {
           $data=input('post.');
           $data['reg_time']=time();
           $lastYear = date('Y', strtotime("-1 year"));
           $data['taxyear']=$lastYear;
           
           $data['user_id']=$user['user_id'];
           //生成链接
           $key=getRandomString(28);
           $user_where22['link']=$key;
           $reuser22=DB::name('users_clients')->where($user_where22)->find();
           if($reuser22){
               while($reuser22){
                   $key=getRandomString(28);
                   $user_where22['link']=$key;
                   $reuser22=DB::name('users_clients')->where($user_where22)->find();
               }
           }
           $data['link']=$key;
           $res=Db::name('users_clients')->insertGetId($data);
         
          // echo Db::name('users_clients')->getLastSql();
           if($res){
              return json(['code'=>1,'msg'=>'Successfully added','url'=>url('User/center')->build()]); 
           }else{
              return json(['code'=>0,'msg'=>'Fail to add','url'=>url('User/center')->build()]);  
           } 
        }
        return view();
    }
    //个人中心
    public function index(){
        return view();
    }
    public function login(){
        return view();
    }
    //登录
    /*public function login(){
        if ($this->user_id > 0) {
            $this->redirect(url('User/index'));
        }
        $referurl = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : url("User/index");
        View::assign('referurl', $referurl);
        return view();
    }*/
    /*删除clicent*/
    public function delClient(){
        if (IS_POST) {
            $user = session('user');
            $data=input('post.');
            $map['id']=$data['id'];
            $map['user_id']=$user['user_id'];
            $check=DB::name('users_clients')->where($map)->find();
            $url=url('User/center')->build();
            if($check){
                DB::name('users_clients')->where($map)->delete();
               return json(['code'=>1,'msg'=>'Successfully deleted','url'=>$url]); 
            }else{
               return json(['code'=>0,'msg'=>'Unavaliable client','url'=>$url]); 
            }
        }
    }
    /*个人中心 列表client */
    public function center(){
         $user = session('user');
         $where11[]=['user_id','=',$user['user_id']];
        if (IS_POST) {
            $data=input('post.');
          
            if($data['keyword']){
                $keyword=$data['keyword'];
                $where11[]=['firstname|lastname','like','%'.$keyword.'%'];
            }
            if($data['status']!=-1){
                $status=$data['status'];
                $where11[]=['status','=',$status];
            }
            if($data['type']!=-1){
                $type=$data['type'];
                $where11[]=['type','=',$type];
            }
            if($data['taxyear']!=-1){
                $taxyear=$data['taxyear'];
                $where11[]=['taxyear','=',$taxyear];
            }
        }
        
        //获得所有client
        $this->page_size=10;
         $list=DB::name('users_clients')->where($where11)->paginate(['query'=>['status'=>$status,'keyword'=>$keyword,'taxyear'=>$taxyear,'type'=>$type],'list_rows'=>$this->page_size]);
        // echo DB::name('users_clients')->getLastSql();die();
         foreach($list as $key=>$val){
             $le=substr($val['firstname'],0,1);
             $le2=substr($val['lastname'],0,1);
             $val['first']=$le.$le2;
             if($val['updatetime']){
                 $customFormat = 'M j, Y, g:I A'; //显示样式  Jan 11, 2024, 10:0 AM  F为英文月份全称 jS为6th第几天
                 $val['utime']=date($customFormat,$val['updatetime']); //将当前时间按照自定义格式转换成字符串
             }else{
                 $val['utime']='';
             }
             $val['link']="http://".$_SERVER['HTTP_HOST'].'/republic/folders/'.$val['link'];
             $list[$key]=$val;
         }
         $page = $list->render();
         View::assign('list', $list);
         View::assign('page', $page);
         return view();
    }
    //登录
    public function do_login(){
        $username = trim(input('account'));
        $data=input('post.');
        try {
            validate(PublicValidate::class)->scene('login')->check($data);
        }catch (ValidateException $e){
            $error=$e->getError();
            return json(['code'=>0,'msg'=>$error]);
        }
        $user_where['mobile|account']=$username;
        $user=Users::where($user_where)->find();
        $url=url('User/center')->build();
        Db::name('users')->where("user_id", $user['user_id'])->update(['last_login'=>time()]);
      //  echo Db::name('users')->getLastSql();die();
        session('user',$user);
        return json(['code'=>1,'msg'=>'登录成功','url'=>$url]);
    }
    //注册
    public function reg(){
        if($this->user_id > 0) {
            $this->redirect(url('User/index'));
        }
        $regis_sms_enable=getSysConfig('sms.regis_sms_enable');
        $rekey=input('rekey');
        if (IS_POST) {
            $username = input('mobile');
            $password = input('password');
            $data=input('post.');
            $data['sms']=$regis_sms_enable;
            try {
                validate(PublicValidate::class)->scene('reg')->check($data);
            }catch (ValidateException $e){
                $error=$e->getError();
                return json(['code'=>0,'msg'=>$error]);
            }
            $user_where['mobile|account']=$rekey;
            $reuser=Users::where($user_where)->find();
            $re_user_id=$reuser['user_id'];
            $map['password']    =   encrypt($password);
            $map['head_pic']    =   '/static/mobile/img/default.png';
            $map['nickname']    =   $username;
            $map['mobile']      =   $username;
            $map['realname']    =   $data['realname'];
            $map['reid']        =   $re_user_id;
            $map['jt_id']       =   $reuser['reid'];
            $map['rekey']       =   getReKey();
            $map['account']     =   'Cs'.$map['rekey'];
            $map['reg_time']    =   time();
            $res=Db::name('users')->insertGetId($map);
            if ($res){
                //增加团队人数
                td_all_num($res);
                return json(['code'=>1,'msg'=>'注册成功','url'=>url('User/login')->build()]);
            }else{
                return json(['code'=>0,'msg'=>'注册失败']);
            }
        }
        // 推荐人
        View::assign('rekey', $rekey);
        View::assign('regis_sms_enable', $regis_sms_enable);
        return view();
    }
    //忘记密码
    public function forget_pwd(){
        if($this->user_id > 0) {
            $this->redirect(url('User/index'));
        }
        if (IS_POST){
            $data=input('post.');
            $data['sms']=1;
            try {
                validate(PublicValidate::class)->scene('forget')->check($data);
            }catch (ValidateException $e){
                $error=$e->getError();
                return json(['code'=>0,'msg'=>$error]);
            }
            $mobile=input('mobile');
            $user=get_user_info($mobile,1);
            $update['password'] = encrypt($data['password']);
            $res=Db::name('users')->where('user_id',$user['user_id'])->update($update);
            if ($res!==false){
                return json(['code'=>1,'msg'=>'密码重置成功','url'=>url('User/login')->build()]);
            }else{
                return json(['code'=>0,'msg'=>'密码重置失败']);
            }
        }
        return view();
    }
    //退出
    public function logout()
    {
        session('user',null);
        $url=url('Index/index')->build();
        return $this->redirect($url);
    }
    //签到界面
    public function sign(){
        $sign=Db::name('user_sign')
            ->where("user_id=$this->user_id")
            ->whereDay('add_time')
            ->find();
        $edit=1;
        if ($sign){
            $edit=0;
        }
        //7天前的时间戳
        $start_time=strtotime('-6 days');
        $start=strtotime(date('Y-m-d 00:00:00',$start_time));
        $end=strtotime(date('Y-m-d 23:59:59'));
        $arr=[];
        for ($i=$start;$i<$end;$i+=86400){
            $start1=date("Y-m-d H:i:s",$i);
            $end1=date("Y-m-d 23:59:59",$i);
            $day=date("m-d",$i);
            $map['start']=$start1;
            $map['end']=$end1;
            $map['day']=$day;
            $start_time1=strtotime($start1);
            $end_time1=strtotime($end1);
            $yet_where['user_id']=$this->user_id;
            $yet_sign=Db::name('user_sign')
                ->where($yet_where)
                ->whereBetweenTime('add_time',$start_time1,$end_time1)
                ->find();
            if ($yet_sign){
                $map['yet']=1;
            }else{
                $map['yet']=0;
            }
            $arr[]=$map;
        }
        //签到视频
        $wap_sign_video=getSysConfig('basic.wap_sign_video');
        View::assign('edit',$edit);
        View::assign('arr',$arr);
        View::assign('wap_sign_video',$wap_sign_video);
        return view();
    }
    //签到操作
    public function do_sign(){
        $sign=Db::name('user_sign')
            ->where("user_id=$this->user_id")
            ->whereDay('add_time')
            ->find();
        if ($sign){
            return json(['code'=>0,'msg'=>'今日已签到']);
        }
        $sign_jl=getSysConfig('rate.sign_jl');
        $arr=explode(',',$sign_jl);
        $money=$arr[$this->user['lj_sign']];
        if (empty($money)){
            $money=$arr[count($arr-1)];
        }
        //签到奖励
        if (empty($money)){
            return json(['code'=>0,'msg'=>'没有奖励可以领取']);
        }
        //插入一条签到记录
        $map['user_id']=$this->user_id;
        $map['day']=$this->user['lj_sign']+1;
        $map['money']=$money;
        $res=UserSign::create($map);
        $jifen_arr=config('app.jifen_arr');
        $log=accountLog($this->user_id,$money,$jifen_arr[3],2);
        if ($res && $log){
            //累积签到天数+1
            Db::name('users')->where('user_id',$this->user_id)->inc('lj_sign')->update();
            return json(['code'=>1,'msg'=>"签到成功,奖励{$money}积分!",'url'=>url('User/jifen')->build()]);
        }else{
            return json(['code'=>0,'msg'=>'签到失败']);
        }
    }
    //加载签到列表
    function ajax_get_sign(){
        $user_sign=new UserSign();
        $where[]=['user_id','=',$this->user_id];
        $p=input('page');
        $count=$user_sign->where($where)->count();
        $pages=ceil($count/20);
        $data=[];
        $lists=$user_sign
            ->where($where)
            ->order('id desc')
            ->page($p,20)
            ->field('bond,add_time')
            ->select();
        if ($lists){
            foreach ($lists as $key => $val){
                $a='<div class="money-item"><span>每日签到</span>';
                $a.='<span class="money">+'.$val['bond'].'</span>';
                $a.='<span>'.$val['add_time'].'</span>';
                $a.='</div>';
                $data[]=$a;
            }
        }
        return json(['pages'=>$pages,'data'=>$data]);
    }
    //等级界面
    public function level(){
        //看看是否还有下一等级
        $user_level=new UserLevel();
        //查询最大 最小会员等级
        $max_level_id=$user_level->max('level_id');
        $min_level_id=$user_level->min('level_id');
        //当前会员等级
        $current_level=$this->user['level'];
        //当前消费
        $lj_xf=$this->user['lj_xf'];
        $next_level_id=$current_level+1;
        $next_level=$user_level->where('level_id',$next_level_id)->find();
        //计算总进度
        if ($max_level_id==$current_level){
            $ids=[$current_level-2,$current_level-1,$current_level];
        }else if ($current_level==$min_level_id){
            $ids=[$current_level,$current_level+1,$current_level+2];
        }else{
            $ids=[$current_level-1,$current_level,$current_level+1];
        }
        $level_list=$user_level->whereIn('level_id',$ids)->select()->toArray();
        foreach ($level_list as $key => $val){
            if ($current_level>=$val['level_id']){
                $class='act';
            }else{
                $class='';
            }
            $next=$level_list[$key+1];
            if ($next){
                if ($lj_xf>=$next['lj_xiaofei']){
                    $width=100;
                }else{
                    $diff=$next['lj_xiaofei'];
                    $width=$diff>0 ? round(($lj_xf/$diff)*100,2) : 0;
                }
            }else{
                $width=0;
            }
            $level_list[$key]['class']=$class;
            $level_list[$key]['width']=$width;
        }
        //下一等级
        View::assign('next_level',$next_level);
        View::assign('level_list',$level_list);
        return view();
    }
    //我的代理
    public function team_index(){
        return view();
    }
    //我的团队
    public function team(){
        $user_id=input('user_id');
        View::assign('user_id',$user_id);
        return view();
    }
    //加载团队列表
    function ajax_team(){
        $p=input('page');
        $user_id=input('user_id');
        $td_ids=get_td_ids($this->user_id);
        if ($user_id){
            if (!in_array($user_id,$td_ids)){
                return json(['pages'=>1,'data'=>[]]);
            }
            $teams=get_td_ids($user_id);
        }else{
            $teams=get_td_ids($this->user_id);
        }
//        $where[]=['reid','=',$this->user_id];
        $user_model=new Users();
        $where[]=['user_id','in',$teams];
        $count=$user_model
            ->where($where)
            ->count();
        $pages=ceil($count/20);
        $data=[];
        $lists=$user_model->where($where)
            ->order('reg_time desc,user_id desc')
            ->page($p,20)
            ->field('user_id,account,realname,level,reg_time,mobile,head_pic,month_xf,valid_time')
            ->select();
        if ($lists){
            foreach ($lists as $key => $val){
                $url=url('Order/td_order_list',['user_id'=>$val['user_id']])->build();
                $url1=url('User/team',['user_id'=>$val['user_id']])->build();
                $a='<div class="team-item">
                        <div class="team-item-top">
                            <div class="left">
                                <img src="'.$val['head_pic'].'">
                            </div>
                            <div class="right">
                                <div class="zuo">
                                    <h5>'.$val['realname'].'</h5>
                                    <p>'.$val['account'].'</p>
                                </div>
                                <div class="you">
                                    <h6>
                                        <a href="'.$url1.'">
                                        <img src="/static/mobile/img/td-small.png">
                                        <i>团队</i>
                                        </a>
                                    </h6>
                                    <span>'.$val['level_name'].'</span>
                                </div>
                            </div>
                        </div>
                        <div class="team-item-bot">
                            <div class="progress">
                                <div class="progress-tit">
                                    <div class="left">
                                        <p>已完成金额</p>
                                        <h4>¥'.$val['month_xf_text'].'</h4>
                                    </div>
                                    <a class="right" href="'.$url.'">
                                        <span class="first">任务金额</span>
                                        <h4>¥'.$val['target_amount'].'</h4>
                                        <i class="iconfont iconjiantou"></i>
                                    </a>
                                </div>
                                <div class="jindu">
                                    <div class="jindu-info" style="width: '.$val['jd_bl'].'%"></div>
                                </div>
                            </div>
                        </div>
                    </div>';
                $data[]=$a;
            }
        }
        return json(['pages'=>$pages,'data'=>$data]);
    }
    //账户明细
    public function points(){
        $type=input('type');
        View::assign('type',$type);
        return view();
    }
    //加载资金列表
    function ajax_points(){
        $type=input('type');
        $where['user_id']=$this->user_id;
        if ($type){
            $where['type']=$type;
        }
        $account_log=new AccountLog();
        $p=input('page');
        $count=$account_log->where($where)->count();
        $pages=ceil($count/20);
        $data=[];
        $lists=$account_log
            ->where($where)
            ->order('log_id desc')
            ->page($p,20)
            ->field('money,add_time,desc')
            ->select();
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
    //消息界面
    public function message(){
        //将全部消息置为已读
        $where[]=['user_id','=',$this->user_id];
        $where[]=['status','=',-1];
        $account_log=new AccountLog();
        $account_log->where($where)->save(['status'=>1]);
        return view();
    }
    //加载消息列表
    function ajax_message(){
        $where[]=['user_id','=',$this->user_id];
        $where[]=['type','in',[3,4]];
        $where[]=['desc','in',['直推奖励','团队奖励','伯乐奖励','县级代理奖励','市级代理奖励','省级代理奖励']];
        $account_log=new AccountLog();
        $p=input('page');
        $count=$account_log->where($where)->count();
        $pages=ceil($count/20);
        $data=[];
        $lists=$account_log
            ->where($where)
            ->order('log_id desc')
            ->page($p,20)
            ->field('log_id,gid,money,add_time,desc')
            ->select();
        if ($lists){
            foreach ($lists as $key => $val){
                $url=url('User/message_detail',['id'=>$val['log_id']]);
                $a='<div class="message-item">
                        <div class="time">
                            <span>'.$val['time_text'].'</span>
                        </div>
                        <a href="'.$url.'" class="message-info">
                            <div class="tit">佣金收入提醒</div>
                            <div class="center">
                                <div class="money">
                                    <p>佣金收入</p>
                                    <h4>¥ '.$val['money'].'</h4>
                                </div>
                                <div class="row">
                                    <span>贡献者</span>
                                    <span>'.$val['guser']['realname'].'</span>
                                </div>
                                <div class="row">
                                    <span>佣金类别</span>
                                    <span>'.$val['desc'].'</span>
                                </div>
                            </div>
                            <div class="bot">
                                <span>查看详情</span>
                                <span class="iconfont iconjiantou"></span>
                            </div>
                        </a>
                    </div>';
                $data[]=$a;
            }
        }
        return json(['pages'=>$pages,'data'=>$data]);
    }
    //消息详情
    public function message_detail(){
        $id = input('id/d', 0);
        $order=AccountLog::where(['user_id'=>$this->user_id,'log_id'=>$id])->find();
        if (empty($order)) {
            $this->error('消息不存在');
        }
        View::assign('order', $order);
        return view();
    }
    //零钱界面
    public function lingqian(){
        return view();
    }
    //积分界面
    public function jifen(){
        $find=Db::name('user_sign')
            ->where("user_id=$this->user_id")
            ->whereDay('add_time')
            ->find();
        View::assign('find', $find);
        return view();
    }
    //资金充值
    public function recharge(){
        $recharge=getSysConfig('recharge');
        if ($recharge['cz_open']==0){
            $this->error("充值暂未开放",url('User/recharge_list'));
        }
        if (IS_POST){
            $money=input('money');
            $pay_code=input('pay_code');
            if (!in_array($pay_code,['weixin','alipayMobile','weixinH5'])){
                return json(['code'=>0,'msg'=>'支付方式有误']);
            }
            if ($money=='' || !is_numeric($money) || $money<=0){
                return json(['code'=>0,'msg'=>'请输入正确的充值金额']);
            }
            if ($recharge['cz_min']>0){
                if ($money<$recharge['cz_min']){
                    return json(['code'=>0,'msg'=>"最少充值金额为{$recharge['cz_min']}元!"]);
                }
            }
            if ($recharge['cz_xishu']>0){
                if (($money*10000)%($recharge['cz_xishu']*10000)!=0){
                    return json(['code'=>0,'msg'=>"充值金额为{$recharge['cz_xishu']}元的整数倍!"]);
                }
            }
            $insert['order_sn']     =   getOrderSn('recharge','r');
            $insert['type']         =   1;
            $insert['user_id']      =   $this->user_id;
            $insert['order_amount'] =   $money;
            $insert['pay_code']     =   $pay_code;
            $insert['pay_name']     =   Db::name('plugin')->where('code',$pay_code)->value('name');;
            $log=Recharge::create($insert);
            if ($log){
                $url=url('Payment/get_code',['pay_code'=>$pay_code,'order_sn'=>$insert['order_sn']])->build();
                $msg='跳转支付中~';
                return json(['code'=>1,'msg'=>$msg,'url'=>$url]);
            }else{
                return json(['code'=>0,'msg'=>'订单创建失败']);
            }
        }
        $payment_where[]=['status','=',1];
        if(strstr($_SERVER['HTTP_USER_AGENT'],'MicroMessenger')){
            //微信浏览器
            $payment_where[] = ['code','=','weixin'];
        }else{
            $payment_where[] =['code','in',['alipayMobile','weixinH5']];
        }
        $payment=Db::name('plugin')->where($payment_where)->field('name,code')->select()->toArray();
        View::assign('payment', $payment);
        View::assign('recharge', $recharge);
        return view();
    }
    //充值记录
    public function recharge_list(){
        return view();
    }
    public function ajax_recharge_list(){
        $p=input('page');
//        $order_status=input('order_status');
        $order=new Recharge();
        $where[]=['user_id','=',$this->user_id];
//        if ($order_status!='all'){
//            $where[]=['order_status','=',$order_status];
//        }
        $where[]=['order_status','>',0];
        $count=$order->where($where)->count();
        $pages=ceil($count/10);
        $lists=$order->where($where)
            ->page($p,10)
            ->field('order_sn,type,order_amount,pay_name,order_status,add_time')
            ->select();
        $data=[];
        if (!$lists->isEmpty()){
            foreach ($lists as $key => $val){
                $a='<div class="order-list">';
                $a.='<div class="title">
                        <div class="left">订单编号:'.$val['order_sn'].'</div>
                        <div class="right red">'.$val['status_text'].'</div>
                    </div>';
                $a.='<div class="order-info">
                        <div class="order-info-item">
                            <div class="tit">充值方式:</div>
                            <div class="money">'.$val['pay_name'].'</div>
                        </div>
                        <div class="order-info-item">
                            <div class="tit">充值金额:</div>
                            <div class="money">￥'.$val['order_amount'].'</div>
                        </div>
                        <div class="order-info-item">
                            <div class="tit">充值时间:</div>
                            <div class="money">'.$val['add_time'].'</div>
                        </div>
                    </div>';
                $a.='</div>';
                $data[]=$a;
            }
        }
        return json(['pages'=>$pages,'data'=>$data]);
    }
    //动态 静态收益明细
    public function jl_log(){
        //类型1今日静态明细2今日动态明细3累积明细
        $type=input('type',1);
        switch ($type){
            case 1:
                $title="今日收益";
                break;
            case 2:
                $title="动态收益";
                break;
            case 3:
                $title="累积收益";
                break;
            default:
                $this->error("提交参数有误");
        }
        View::assign('type',$type);
        View::assign('title',$title);
        return view();
    }
    //加载资金列表
    function ajax_jl_log(){
        //类型1今日静态明细2今日动态明细3累积明细
        $type=input('type',1);
        $day=date('Y-m-d');
        $time=strtotime($day);
        if ($type!=3){
            $where[]=['type','=',$type];
            $where[]=['add_time','>=',$time];
        }
        $where[]=['user_id','=',$this->user_id];
        $account_log=new JlLog();
        $p=input('page');
        $count=$account_log->where($where)->count();
        $pages=ceil($count/20);
        $data=[];
        $lists=$account_log
            ->where($where)
            ->order('add_time desc')
            ->page($p,20)
            ->field('amount,add_time,desc')
            ->select();
        if ($lists){
            foreach ($lists as $key => $val){
                $a='<div class="list"><span>'.$val['desc'].'</span>';
                $a.='<span class="red">+'.$val['amount'].'</span>';
                $a.='<span>'.$val['time_text'].'</span>';
                $a.='</div>';
                $data[]=$a;
            }
        }
        return json(['pages'=>$pages,'data'=>$data]);
    }
    //个人信息界面
    public function user_info(){
        return view();
    }
    //个人信息
    public function info()
    {
        $user_info_sms_enable=getSysConfig('sms.user_info_sms_enable');
        $return=$_SERVER['HTTP_REFERER'];
        if (IS_POST) {
            $data=input('post.');
            $return=$data['return'];
            $user=get_user_info($this->user_id);
            $data['sms']=$user_info_sms_enable;
            $data['mobile']=$user['mobile'];
            try {
                validate(PublicValidate::class)->scene('info')->check($data);
            }catch (ValidateException $e){
                $error=$e->getError();
                return json(['code'=>0,'msg'=>$error]);
            }
            $user_update['realname']=$data['realname'];
            $user_update['zhifubao']=$data['zhifubao'];
            $user_update['bank_name']=$data['bank_name'];
            $user_update['bank_card']=$data['bank_card'];
            if ($return){
                $url=$return;
            }else{
                $url=url('User/info')->build();
            }
            Db::name('users')->where('user_id',$user['user_id'])->update($user_update);
            return json(['code'=>1,'msg'=>'操作成功','url'=>$url]);
        }
        View::assign('return',$return);
        View::assign('user_info_sms_enable',$user_info_sms_enable);
        return view();
    }
    //修改真实姓名
    public function realname(){
        $user_id=$this->user_id;
        if (IS_POST){
            $data=input('post.');
            try {
                validate(PublicValidate::class)->scene('realname')->check($data);
            }catch (ValidateException $e){
                $error=$e->getError();
                return json(['code'=>0,'msg'=>$error]);
            }
            $realname=$data['realname'];
            Db::name('users')->where('user_id',$user_id)->update(['realname'=>$realname]);
            return json(['code'=>1,'msg'=>'修改成功','url'=>url('User/realname')->build()]);
        }
        return view();
    }
    //密码修改
    public function password()
    {
        $edit_pwd_sms_enable=getSysConfig('sms.edit_pwd_sms_enable');
        if ($this->user_id==0){
            $this->error('请先登录',url('User/login'));
        }
        if (IS_POST) {
            $data=input('post.');
            $user=get_user_info($this->user_id);
            $data['sms']=$edit_pwd_sms_enable;
            try {
                validate(PublicValidate::class)->scene('pass')->check($data);
            }catch (ValidateException $e){
                $error=$e->getError();
                return json(['code'=>0,'msg'=>$error]);
            }
            $map['password']=encrypt($data['password']);
            $res=Db::name('users')->where('user_id',$user['user_id'])->update($map);
            if ($res!==false){
                return json(['code'=>1,'msg'=>'修改成功','url'=>url('User/password')->build()]);
            }else{
                return json(['code'=>0,'msg'=>'修改失败']);
            }
        }
        View::assign('edit_pwd_sms_enable', $edit_pwd_sms_enable);
        return view();
    }
    //支付密码修改
    public function paypwd()
    {
        $edit_paypwd_sms_enable=getSysConfig('sms.edit_paypwd_sms_enable');
        $return=$_SERVER['HTTP_REFERER'];
        if (IS_POST) {
            $data=input('post.');
            $user=get_user_info($this->user_id);
            $data['sms']=$edit_paypwd_sms_enable;
            if (empty($this->user['paypwd'])){
                $scene='noPaypwd';
            }else{
                $scene='paypwd';
            }
            $return1=$data['return'];
            try {
                validate(PublicValidate::class)->scene($scene)->check($data);
            }catch (ValidateException $e){
                $error=$e->getError();
                return json(['code'=>0,'msg'=>$error]);
            }
            $map['paypwd']=encrypt($data['password']);
            Db::name('users')->where('user_id',$user['user_id'])->update($map);
            if ($return1){
                $url=$return1;
            }else{
                $url=url('User/paypwd')->build();
            }
            return json(['code'=>1,'msg'=>'修改成功','url'=>$url]);
        }
        View::assign('return',$return);
        View::assign('edit_paypwd_sms_enable', $edit_paypwd_sms_enable);
        return view();
    }
    //资金转赠
    public function given(){
        $given=getSysConfig('given');
        if ($given['given_open']==0){
            $this->error("转赠暂未开放",url('User/given_list'));
        }
        if (empty($this->user['paypwd'])){
            $this->error("请先设置支付密码",url('User/paypwd'));
        }
        if (IS_POST){
            $dfmobile=input('dfmobile');
            $money=input('money');
            $paypwd=input('paypwd');
            if (empty($dfmobile)){
                return json(['code'=>0,'msg'=>'请输入对方手机号码']);
            }
            $dfuser=get_user_info($dfmobile,1);
            if (empty($dfuser)){
                return json(['code'=>0,'msg'=>'会员不存在']);
            }
            if ($dfmobile==$this->user['mobile']){
                return json(['code'=>0,'msg'=>'转赠人不能为自己']);
            }
            if ($money=='' || !is_numeric($money) || $money<=0){
                return json(['code'=>0,'msg'=>'请输入正确的转账金额']);
            }
            if ($given['cz_min']>0){
                if ($money<$given['given_min']){
                    return json(['code'=>0,'msg'=>"最少转账金额为{$given['given_min']}元!"]);
                }
            }
            if ($given['given_xishu']>0){
                if (($money*10000)%($given['given_xishu']*10000)!=0){
                    return json(['code'=>0,'msg'=>"转账金额为{$given['given_xishu']}元的整数倍!"]);
                }
            }
            if ($given['given_daymax']>0){
                $today=strtotime(date('Ymd'));
                $where[]=['user_id','=',$this->user_id];
                $where[]=['add_time','>=',$today];
                $sum=Db::name('given')->where($where)->sum('money');
                if ($sum+$money>$given['given_daymax']){
                    return json(['code'=>0,'msg'=>"当日转账额度超出限制!"]);
                }
            }
            if ($money>$this->user['user_money']){
                return json(['code'=>0,'msg'=>"余额不足,可用{$this->user['user_money']}元!"]);
            }
            if (empty($paypwd)){
                return json(['code'=>0,'msg'=>'请输入支付密码']);
            }
            if (encrypt($paypwd)!=$this->user['paypwd']){
                return json(['code'=>0,'msg'=>'支付密码不正确']);
            }
            $check=$this->request->checkToken('__token__');
            if (false===$check){
                return json(['code'=>0,'msg'=>"非法提交"]);
            }
            $taxfee=$money*($given['given_sxf']/100);
            $true_money=$money-$taxfee;
            $desc="余额转账";
            $log=accountLog($this->user_id,-$money,$desc,1);
            if($log){
                $user=get_user_info($this->user_id);
                if($user['user_money']>=0){
                    $insert['user_id']      =   $this->user_id;
                    $insert['dfuid']        =   $dfuser['user_id'];
                    $insert['type']         =   1;
                    $insert['money']        =   $money;
                    $insert['taxfee']       =   $taxfee;
                    $insert['true_money']   =   $true_money;
                    $log1=Given::create($insert);
                    if($log1){
                        $log2=accountLog($dfuser['user_id'],$true_money,'会员转账',1);
                    }
                }
            }
            
            
            if ($log && $log1 && $log2){
                return json(['code'=>1,'msg'=>'转账成功','url'=>url('User/given_list')->build()]);
            }else{
                return json(['code'=>0,'msg'=>'转账失败']);
            }
        }
        View::assign('given', $given);
        return view();
    }
    //转赠记录列表
    public function given_list(){
        return view();
    }
    public function ajax_given_list(){
        $user_id=$this->user_id;
        $given=new Given();
        $p=input('page');
        $count=$given->where('user_id',$user_id)->count();
        $pages=ceil($count/20);
        $data=[];
        $lists=$given->where('user_id',$user_id)
            ->order('add_time desc')
            ->page($p,20)
            ->field('money,taxfee,true_money,add_time,dfuid')
            ->select();
        if ($lists){
            foreach ($lists as $key => $val){
                $mobile=substr_replace($val['dfuser']['mobile'],'****',3,4);
                $a='<div class="order-list">';
                $a.='<div class="order-info">
                        <div class="order-info-item">
                            <div class="tit">对方账户:</div>
                            <div class="money">'.$mobile.'</div>
                        </div>
                        <div class="order-info-item">
                            <div class="tit">转账金额:</div>
                            <div class="money">￥'.$val['money'].'</div>
                        </div>
                        <div class="order-info-item">
                            <div class="tit">手续费:</div>
                            <div class="money">￥'.$val['taxfee'].'</div>
                        </div>
                        <div class="order-info-item">
                            <div class="tit">实际到账:</div>
                            <div class="money">￥'.$val['true_money'].'</div>
                        </div>
                        <div class="order-info-item">
                            <div class="tit">转账时间:</div>
                            <div class="money">'.$val['add_time'].'</div>
                        </div>
                    </div>';
                $a.='</div>';
                $data[]=$a;
            }
        }
        return json(['pages'=>$pages,'data'=>$data]);
    }
    //申请提现
    public function withdrawals(){
        //余额提现开关
        $cash_open=getSysConfig('cash.cash_open');
        //判断是否为普通会员 不是有效会员不能提现
//        if ($this->user['level']==1 || $this->user['is_valid']==0){
//            $this->error('请升级后在进行提现');
//        }
        if($cash_open!=1){
            $this->error('提现暂未开放',url('User/withdrawals_list'));
        }
        if (empty($this->user['realname']) || empty($this->user['zhifubao']) || empty($this->user['bank_name']) || empty($this->user['bank_card'])){
            $this->error('请先完善个人资料',url('User/info'));
        }
        $user_with_sms_enable=getSysConfig('sms.user_with_sms_enable');
        if (IS_POST) {
            $data = input('post.');
            $money = input('money');
            $tx_type = input('tx_type');
            if (!in_array($tx_type,[1,2])){
                return json(['code'=>0,'msg'=>'提交参数有误']);
            }
            if ($money=='' || !is_numeric($money) || $money<=0){
                return json(['code'=>0,'msg'=>'请输入正确的提现金额']);
            }
            //余额提现
            $bj_tx_sxf         =    getSysConfig('cash.ye_tx_sxf');//余额提现手续费
            $bj_tx_min         =    getSysConfig('cash.ye_tx_min');//余额最少提现
            $bj_tx_xishu       =    getSysConfig('cash.ye_tx_xishu');//余额提现系数
            $bj_tx_num         =    getSysConfig('cash.ye_tx_num');//余额每日可提现次数
            if ($bj_tx_num>0){
                $count_where[]=['user_id','=',$this->user_id];
                $count_where[]=['type','=',1];
                $count=Db::name('withdrawals')
                    ->where($count_where)
                    ->whereDay('create_time')
                    ->count();
                if ($count>=$bj_tx_num){
                    $msg=sprintf('余额每日可提现%s次',$bj_tx_num);
                    return json(['code'=>0,'msg'=>$msg]);
                }
            }
            if ($money>$this->user['user_money']){
                $msg=sprintf('余额不足,可用%s元!',$this->user['user_money']);
                return json(['code'=>0,'msg'=>$msg]);
            }
            if ($bj_tx_min>0) {
                if ($money<$bj_tx_min){
                    $msg=sprintf('余额最少提现%s元起!',$bj_tx_min);
                    return json(['code'=>0,'msg'=>$msg]);
                }
            }
            if ($bj_tx_xishu>0) {
                if (($money*10000)%($bj_tx_xishu*10000)!=0){
                    $msg=sprintf('余额提现金额为%s元的整数倍!',$bj_tx_xishu);
                    return json(['code'=>0,'msg'=>$msg]);
                }
            }
            $taxfee=$money*($bj_tx_sxf/100);
            if ($user_with_sms_enable){
                $check=yz_sms_code($this->user['mobile'],$data['code']);
                if ($check['code']!=1){
                    return json($check);
                }
            }
            $check=$this->request->checkToken('__token__');
            if (false===$check){
                return json(['code'=>0,'msg'=>"非法提交"]);
            }
            $insert['user_id']          =       $this->user_id;
            $insert['tx_type']          =       $tx_type;
            $insert['money']            =       $money;
            $insert['taxfee']           =       $taxfee;
            $insert['true_money']       =       $money-$taxfee;
            $insert['realname']         =       $this->user['realname'];
            $insert['zhifubao']         =       $this->user['zhifubao'];
            $insert['bank_name']        =       $this->user['bank_name'];
            $insert['bank_card']        =       $this->user['bank_card'];
            $res=Withdrawals::create($insert);
            $log=accountLog($this->user['user_id'],-$money,'申请提现');
            $log1=true;
            if ($res && $log && $log1) {
                return json(['code'=>1,'msg'=>"已提交申请",'url'=>url('User/withdrawals_list')->build()]);
            } else {
                return json(['code'=>0,'msg'=>'提交失败']);
            }
        }
        View::assign('cash_config', getSysConfig('cash'));//提现配置项
        View::assign('user_with_sms_enable', $user_with_sms_enable);    //提现短信
        return view();
    }
    //申请记录列表
    public function withdrawals_list(){
        return view();
    }
    public function ajax_with(){
        $p=input('page');
        $with=new Withdrawals();
        $where[]=['user_id','=',$this->user_id];
        $where[]=['type','=',1];
        $count=$with->where($where)->count();
        $pages=ceil($count/20);
        $data=[];
        $lists=$with->where($where)
            ->order('create_time desc,id desc')
            ->page($p,20)
            ->withoutField('id')
            ->select();
        if ($lists){
            foreach ($lists as $key => $val){
                $a='<div class="order-list">';
                $a.='<div class="order-info">
                        <div class="order-info-item">
                            <div class="tit">提现金额:</div>
                            <div class="money">￥'.$val['money'].'</div>
                        </div>
                        <div class="order-info-item">
                            <div class="tit">手续费:</div>
                            <div class="money">￥'.$val['taxfee'].'</div>
                        </div>
                        <div class="order-info-item">
                            <div class="tit">实际到账:</div>
                            <div class="money">￥'.$val['true_money'].'</div>
                        </div>
                        <div class="order-info-item">
                            <div class="tit">提现状态:</div>
                            <div class="money">'.$val['status_text'].'</div>
                        </div>
                        <div class="order-info-item">
                            <div class="tit">提现信息:</div>
                            <div class="money">'.$val['zhang_hu'].'</div>
                        </div>
                        <div class="order-info-item">
                            <div class="tit">提现时间:</div>
                            <div class="money">'.$val['create_time'].'</div>
                        </div>
                    </div>';
                $a.='</div>';
                $data[]=$a;
            }
        }
        return json(['pages'=>$pages,'data'=>$data]);
    }
    //推广二维码
    public function qrcode(){
        if ($this->user['valid']){
            $this->error("没有推广权限");
        }
        require_once root_path('vendor')."phpqrcode/phpqrcode.php";
        $uid=$this->user_id;
        $userinfo = Db::name("users")->where('user_id',$uid)->find();
        $url='http://'.$_SERVER['HTTP_HOST'].url('user/reg',array('rekey'=>$userinfo['mobile']));
        $base_name = 'upload/qrcode/tgzm.png';
        $img_name=md5('zhang'.$this->user_id.'zhang');
        \QRcode::png($url,'upload/qrcode/'.$img_name.'.png',1,4,2);
        header('Content-Type: image/png');
        // Load
        $thumb = imagecreatefrompng($base_name);
        $e_p = imagecreatefrompng('upload/qrcode/'.$img_name.'.png');
        $width=imagesx($e_p);
        $height=imagesy($e_p);
        imagecopyresampled($thumb, $e_p, 350, 950, 0, 0, 379, 379, $width, $height);

        $mobile=substr_replace($userinfo['mobile'],'****',3,4);
        $black = imagecolorallocate($thumb, 0,0,0);
        //输出一个灰色的字符串作为阴影
        imagettftext($thumb, 30, 0, 360, 1420, $black, 'static/font/msyh.ttf', '推荐人：'.$mobile);
        // Output
        imagepng($thumb,'upload/qrcode/'.'tg_'.$img_name.'.png');
        @unlink ('upload/qrcode/'.$img_name.'.png');
        View::assign('img_name',$img_name);
        View::assign('url',$url);
        return view();
    }
    //用户收藏列表
    public function collect_list()
    {
        return view();
    }
    //加载收藏列表
    public function ajax_collect(){
        $user_id=$this->user_id;
        $p=input('page');
        $count=Db::name('goods_collect')
            ->alias('gc')
            ->join('goods g','gc.goods_id=g.goods_id','left')
            ->where('gc.user_id='.$user_id)
            ->count();
        $pages=ceil($count/15);
        $lists=Db::name('goods_collect')
            ->alias('gc')
            ->join('goods g','gc.goods_id=g.goods_id','left')
            ->where('gc.user_id='.$user_id)
            ->field('gc.collect_id,gc.goods_id,g.goods_img,g.goods_price,g.goods_name')
            ->page($p,15)
            ->select();
        $data=[];
        if ($lists){
            foreach ($lists as $key => $val){
                $img=$val['goods_img'] ? $val['goods_img'] : '/public/images/not_adv.jpg';
                $a='<div class="public-list-item"><div class="danxuan-fa">';
                $a.='<div class="danxuan-item"><span class="iconfont icondanxuan danxuan" data-id="'.$val['collect_id'].'"></span></div></div>';
                $a.='<a href="'.url('Goods/goods_info',['id'=>$val['goods_id']]).'" class="public-list-item-a"><div class="img"><img src="'.$img.'"></div>';
                $a.='<div class="good"><p>'.$val['goods_name'].'</p><p class="red">￥'.$val['goods_price'].'</p>';
                $a.='</div></a></div>';
                $data[]=$a;
            }
        }
        return json(['pages'=>$pages,'data'=>$data]);
    }
    //删除收藏
    public function collect_del(){
        $ids=input('ids/a');
        if (empty($ids)){
            return json(['code'=>0,'msg'=>'请勾选要删除的列表']);
        }
        $res=Db::name('goods_collect')->whereIn('collect_id',implode(',',$ids))->delete();
        if ($res){
            return json(['code'=>1,'msg'=>'删除成功','url'=>url('User/collect_list')->build()]);
        }else{
            return json(['code'=>0,'msg'=>'删除失败']);
        }
    }
    //浏览记录
    public function visit_log()
    {
        return view();
    }
    //ajax加载浏览记录
    function ajax_visit(){
        $p=input('page');
        $count=Db::name('goods_visit')
            ->alias('gv')
            ->join('goods g','g.goods_id=gv.goods_id','left')
            ->where('gv.user_id='.$this->user_id)
            ->count();
        $data=[];
        $pages=ceil($count/15);
        $list=[];
        $curyear = date('Y');
        $lists=Db::name('goods_visit')
            ->alias('gv')
            ->join('goods g','g.goods_id=gv.goods_id','left')
            ->where('gv.user_id='.$this->user_id)
            ->field('gv.visittime,gv.goods_id,gv.visit_id,g.goods_img,g.goods_price,g.goods_name')
            ->page($p,15)
            ->order('gv.visittime desc')
            ->select();
        if ($lists){
            foreach ($lists as $key => $val){
                if (date('Y',$val['visittime'])==$curyear){
                    $date=date('m月d日',$val['visittime']);
                }else{
                    $date=date('Y年m月d日',$val['visittime']);
                }
                $list[$date][]=$val;
            }
            foreach ($list as $k => $v){
                $a='<h4 class="title"><span class="iconfont icondanxuan title-danxuan"></span>'.$k.'</h4>';
                $a.='<div class="public-list-list">';
                foreach ($v as $k1 => $v1){
                    $img=$v1['goods_img'] ? $v1['goods_img'] : '/public/images/not_adv.jpg';
                    $a.='<div class="public-list-item"><div class="danxuan-fa"><div class="danxuan-item"><span class="iconfont icondanxuan danxuan" data-id="'.$v1['visit_id'].'"></span>';
                    $a.='</div></div>';
                    $a.='<a href="'.url('Goods/goods_info',['id'=>$v1['goods_id']]).'" class="public-list-item-a"><div class="img"><img src="'.$img.'">';
                    $a.='</div><div class="good"><p>'.$v1['goods_name'].'</p><p class="red">￥'.$v1['goods_price'].'</p></div></a></div>';
                }
                $a.='</div>';
                $data[]=$a;
            }
        }
        return json(['pages'=>$pages,'data'=>$data]);
    }
    //删除收藏
    public function visit_del(){
        $ids=input('ids/a');
        if (empty($ids)){
            return json(['code'=>0,'msg'=>'请勾选要删除的列表']);
        }
        $res=Db::name('goods_visit')->whereIn('visit_id',implode(',',$ids))->delete();
        if ($res){
            return json(['code'=>1,'msg'=>'删除成功','url'=>url('User/visit_log')->build()]);
        }else{
            return json(['code'=>0,'msg'=>'删除失败']);
        }
    }
    //用户地址列表
    public function address_list()
    {
        $address_lists =  Db::name('user_address')->order('is_default desc')->where('user_id', $this->user_id)->select();
        View::assign('lists', $address_lists);
        return view();
    }
    //添加编辑地址
    public function add_edit_address(){
        if ($this->user_id==0){
            $this->error('请先登录',url('User/login'));
        }
        $address_id = input('address_id/d',0);
        $add=[];
        $citys=$districts=[];
        if ($address_id){
            $add = Db::name('user_address')->where('address_id',$address_id)->find();
            if ($add['user_id']!=$this->user_id){
                $this->error('地址不存在');
            }
            $citys_where[]=['parent_id','=',$add['pid']];
            $citys=Db::name('region')->where($citys_where)->select()->toArray();
            $districts_where[]=['parent_id','=',$add['cid']];
            $districts=Db::name('region')->where($districts_where)->select()->toArray();
        }
        //查出来一级的
        $level1_where[]=['level','=',1];
        $level1_where[]=['parent_id','=',0];
        $parents=Db::name('region')->where($level1_where)->select()->toArray();
        View::assign('add', $add);
        View::assign('parents', $parents);
        View::assign('citys', $citys);
        View::assign('districts', $districts);
        return view();
    }
    //保存地址
    public function address_save(){
        $address_id = input('address_id/d',0);
        $data = input('post.');
        if (empty($data['consignee'])){
            return json(['code'=>0,'msg'=>'请填写收货人']);
        }
        if (empty($data['mobile']) ||!check_mobile($data['mobile'])){
            return json(['code'=>0,'msg'=>'手机号码格式有误']);
        }
        if (empty($data['pid'])){
            return json(['code'=>0,'msg'=>'请选择省份']);
        }
        if (empty($data['cid'])){
            return json(['code'=>0,'msg'=>'请选择城市']);
        }
        if (empty($data['did'])){
            return json(['code'=>0,'msg'=>'请选择地区']);
        }
        if (empty($data['address'])){
            return json(['code'=>0,'msg'=>'请填写详细地址']);
        }
        $data['province']=Db::name('region')->where('id',$data['pid'])->value('name');
        $data['city']=Db::name('region')->where('id',$data['cid'])->value('name');
        $data['district']=Db::name('region')->where('id',$data['did'])->value('name');
        if ($address_id){
            $res=Db::name('user_address')->where('address_id',$address_id)->update($data);
        }else{
            $count=Db::name('user_address')->where('user_id='.$this->user_id)->count();
            if ($count>=10){
                return json(['code'=>0,'msg'=>'最多可添加10条地址']);
            }
            unset($data['address_id']);
            $data['user_id']=$this->user_id;
            if ($count==0){
                $data['is_default']=1;
            }
            $res=Db::name('user_address')->insert($data);
        }
        if ($res!==false){
            return json(['code'=>1,'msg'=>'操作成功','url'=>url('User/address_list')->build()]);
        }else{
            return json(['code'=>0,'msg'=>'操作失败']);
        }
    }
    //设置默认收货地址
    public function set_default()
    {
        $id = input('address_id/d');
        Db::name('user_address')->where(array('user_id' => $this->user_id))->save(array('is_default' => 0));
        $row = Db::name('user_address')->where(array('user_id' => $this->user_id, 'address_id' => $id))->save(array('is_default' => 1));
        if ($row!=false){
            return json(['code'=>1,'msg'=>'操作成功','url'=>url('User/address_list')->build()]);
        }else{
            return json(['code'=>0,'msg'=>'操作失败']);
        }
    }
    //地址删除
    public function del_address()
    {
        $id = input('address_id/d');

        $address = Db::name('user_address')->where("address_id", $id)->find();
        $row = Db::name('user_address')->where(array('user_id' => $this->user_id, 'address_id' => $id))->delete();
        // 如果删除的是默认收货地址 则要把第一个地址设置为默认收货地址
        if ($address['is_default'] == 1) {
            $address2 = Db::name('user_address')->where("user_id", $this->user_id)->find();
            $address2 && Db::name('user_address')->where("address_id", $address2['address_id'])->save(array('is_default' => 1));
        }
        if ($row!=false){
            return json(['code'=>1,'msg'=>'操作成功','url'=>url('address_list')]);
        }else{
            return json(['code'=>0,'msg'=>'操作失败']);
        }
    }
    //上传图片
    public function upload_pic(){
        // 获取表单上传文件 例如上传了001.jpg
        $file = request()->file('file');
        $path = input('path','upload');
        // 移动到框架应用根目录/uploads/ 目录下
        if($file){
            $info=Filesystem::putFile($path,$file);
            if($info){
                $img=UPLOAD_PATH.$info;
                $this->saveImage($img);
                return json(['code'=>1,'img'=>$img]);
            }else{
                // 上传失败获取错误信息
                return json(['code'=>0,'msg'=>"文件上传失败"]);
            }
        }
    }
    //上传头像
    public function head_pic_upload(){
        $user=get_user_info($this->user_id);
        $head_pic=input('head_pic');
        if (empty($head_pic)){
            return json(['code'=>0,'msg'=>'请选择图片']);
        }
        $res=Db::name('users')->where('user_id',$this->user_id)->update(['head_pic'=>$head_pic]);
        if ($res){
            $file='';
            if ($user['head_pic']){
                $file='.'.$user['head_pic'];
            }
            if (stripos($file,'default.png')){
                $file='';
            }
            if (file_exists($file)){
                unlink($file);
            }
            return json(['code'=>1,'msg'=>'操作成功','url'=>url('User/user_info')->build()]);
        }else{
            return json(['code'=>0,'msg'=>'操作失败']);
        }
    }
    //图片裁剪处理
    private function saveImage($path){
        $image= Image::open('.'.$path);
        $image->thumb(600,600,true)->save('.'.$path);
    }
}
