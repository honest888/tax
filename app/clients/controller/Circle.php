<?php
/**
*@作者:MissZhang
*@邮箱:<787727147@qq.com>
*@创建时间:2021/8/9 上午9:44
*@说明:圈子控制器
*/
namespace app\mobile\controller;


use app\common\model\AccountLog;
use app\common\model\Ad;
use app\common\model\CircleComment;
use app\common\model\CircleZan;
use app\common\model\Users;
use think\facade\Db;
use think\facade\View;

class Circle extends Base
{
    //首页
    public function index()
    {
        $ad_list=Ad::where('open=1 and ad_type=4')->order('sort asc,ad_id desc')->column('ad_link,image');
        $type=input('type');
        View::assign('type',$type);
        View::assign('ad_list',$ad_list);
        return view();
    }
    //ajax获取列表
    public function ajax_get_circle(){
        $type=input('type');
        $where[]=['open','=',1];
        $user_id=input('user_id');
        if ($user_id){
            $where[]=['user_id','=',$user_id];
        }else{
            if ($type==2){
                $ids=get_node_ids($this->user_id);
                $ids=array_merge($ids,[$this->user_id]);
                $where[]=['user_id','in',$ids];
                $where[]=['sj_open','=',1];
            }else{
                $where[]=['qz_open','=',1];
            }
        }
        $circle=new \app\common\model\Circle();
        $p=input('page');
        $count=$circle->where($where)->count();
        $pages=ceil($count/20);
        $data=[];
        $lists=$circle
            ->where($where)
            ->order('sort asc,id desc')
            ->page($p,20)
            ->field('id,user_id,images,content,click,comment,favour,add_time')
            ->select();
        if ($lists){
            foreach ($lists as $key => $val){
                $url=url('Circle/circle_detail',['id'=>$val['id']]);
                $url1=url('Circle/circle_detail',['id'=>$val['id']])->domain(SITE_URL)->build();
                $user_url=url('Circle/user_circle',['user_id'=>$val['user_id']]);
                $a='<div class="circle-list-item list'.$val['id'].'">
                      <div class="top">
                        <a href="'.$user_url.'" class="left">
                          <img src="'.$val['user']['head_pic'].'">
                        </a>
                        <div class="right">
                          <div class="right-item">
                            <h4>'.$val['user']['realname'].'</h4>
                            <p>'.date('Y/m/d H:i',$val['time']).'</p>
                          </div>';
                        if ($val['user_id']==$this->user_id){
                            $a.='<div class="right-item iconfont icondel" data-id="'.$val['id'].'"></div>';
                        }
                        $a.='</div></div>';
                        $a.='<div class="info">';
                        $a.='<div class="text">
                          <a href="'.$url.'">#'.$val['user']['realname'].'的秀#</a>
                          '.$val['content_text'].'
                          <a href="'.$url.'">全文</a>
                        </div>';
                        $a.='<div class="images">';
                        if ($val['images']){
                            foreach ($val['images'] as $image){
                                $a.='<img onload="$(this).zoomify()" src="'.$image.'">';
                            }
                        }
                        $a.='</div>
                        <div class="bot">
                          <a href="'.$url.'#comment-list" class="bot-item">
                            <span class="iconfont iconpinglun"></span>
                            <b>'.$val['comment'].'</b>
                          </a>
                          <div class="bot-item">
                            <span class="iconfont iconshoucang1"></span>
                            <b>'.$val['favour'].'</b>
                          </div>
                        </div>
                      </div>
                    </div>';
                $data[]=$a;
            }
        }
        return json(['pages'=>$pages,'data'=>$data]);
    }
    //发布界面
    public function issue(){
        if (IS_POST){
            $data=input('post.');
            if (empty($data['content'])){
                return json(['code'=>0,'msg'=>"请输入内容"]);
            }
            if (mb_strlen($data['content'])>500){
                return json(['code'=>0,'msg'=>"内容不能超过500字"]);
            }
            if ($data['qz_open']==0 && $data['sj_open']==0){
                return json(['code'=>0,'msg'=>"至少一种圈子可见"]);
            }
            $check=$this->request->checkToken('__token__');
            if (false===$check){
                return json(['code'=>0,'msg'=>"非法提交"]);
            }
            $data['user_id']=$this->user_id;
            $res=\app\common\model\Circle::create($data);
            $log=true;
            $circle_given_jifen=getSysConfig('rate.circle_given_jifen');
            $arr=explode('|',$circle_given_jifen);
            $jifen=$arr[0];
            if ($jifen>0){
                $jifen_arr=config('app.jifen_arr');
                //统计今日发布圈子赠送了多少积分
                $sum_where[]=['user_id','=',$this->user_id];
                $sum_where[]=['type','=',2];
                $sum_where[]=['desc','like',"{$jifen_arr[5]}"];
                $sum=AccountLog::where($sum_where)->whereDay('add_time')->sum('money');
                //有最大限制
                if ($arr[1]){
                    if ($sum+$jifen<=$arr[1]){
                        $log=accountLog($this->user_id,$jifen,$jifen_arr[5],2);
                    }
                }else{//没有最大限制?
                    $log=accountLog($this->user_id,$jifen,$jifen_arr[5],2);
                }
            }
            if ($res && $log){
                $url=url('Circle/index')->build();
                return json(['code'=>1,'msg'=>'发布成功','url'=>$url]);
            }else{
                return json(['code'=>0,'msg'=>'发布失败']);
            }
        }
        return view();
    }
    //圈子详情界面
    public function circle_detail(){
        $id=input('id');
        if (empty($id)){
            $this->error('提交参数有误');
        }
        $circle=\app\common\model\Circle::find($id);
        if (empty($circle)) {
            $this->error('圈子不存在');
        }
        //查询是否已点赞
        $circle_zan=new CircleZan();
        $find_where['user_id']=$this->user_id;
        $find_where['cid']=$id;
        $find=$circle_zan->where($find_where)->find();
        //增加浏览次数
        Db::name('circle')->where('id',$id)->inc('click')->update();
        View::assign('circle', $circle);
        View::assign('find', $find);
        return view();
    }
    //圈子点赞
    public function circle_zan(){
        $id=input('id');
        if (empty($id)) {
            return json(['code'=>0,'msg'=>'提交参数有误']);
        }
        $circle=\app\common\model\Circle::find($id);
        if (empty($circle)) {
            return json(['code'=>0,'msg'=>'圈子不存在']);
        }
        $circle_zan=new CircleZan();
        $find_where['user_id']=$this->user_id;
        $find_where['cid']=$id;
        $find=CircleZan::withTrashed()->where($find_where)->find();
        $log1=true;
        if ($find){
            $zan_id=$find->id;
            //已经处于软删除
            if ($find['delete_time']){
                $res=$find->restore();
                $log=Db::name('circle')->where('id',$id)->inc('favour')->update();
            }else{
                $res=$find->delete();
                $log=Db::name('circle')->where('id',$id)->dec('favour')->update();
            }
        }else{
            //圈子秀被点赞赠送积分
            $quilt_zan_given_jifen=getSysConfig('rate.quilt_zan_given_jifen');
            $arr=explode('|',$quilt_zan_given_jifen);
            $jifen_arr=config('app.jifen_arr');
            $jifen=$arr[0];
            $account_log=new AccountLog();
            if ($jifen>0){
                //统计今日被点赞赠送了多少积分
                $sum_where[]=['user_id','=',$circle['user_id']];
                $sum_where[]=['type','=',2];
                $sum_where[]=['desc','like',"{$jifen_arr[8]}"];
                $sum=$account_log->where($sum_where)->whereDay('add_time')->sum('money');
                //有最大限制
                if ($arr[1]){
                    if ($sum+$jifen<=$arr[1]){
                        $log1=accountLog($circle['user_id'],$jifen,$jifen_arr[8],2);
                    }
                }else{//没有最大限制?
                    $log1=accountLog($circle['user_id'],$jifen,$jifen_arr[8],2);
                }
            }
            $insert['cid']=$id;
            $insert['user_id']=$this->user_id;
            $res=$circle_zan->save($insert);
            $zan_id=$circle_zan->id;
            $log=Db::name('circle')->where('id',$id)->inc('favour')->update();
        }
        if ($res && $log && $log1){
            return json(['code'=>1,'msg'=>'点赞成功','id'=>$zan_id,'head'=>$this->user['head_pic']]);
        }else{
            return json(['code'=>0,'msg'=>'点赞失败']);
        }
    }
    //删除圈子
    public function circle_del(){
        $id=input('id');
        if (empty($id)) {
            return json(['code'=>0,'msg'=>'提交参数有误']);
        }
        $circle=\app\common\model\Circle::where(['user_id'=>$this->user_id,'id'=>$id])->find();
        if (empty($circle)) {
            return json(['code'=>0,'msg'=>'圈子不存在']);
        }
        $res=$circle->delete();
        if ($res){
            //删除点赞
            Db::name('circle_zan')->where('cid',$id)->delete();
            //删除评论表
            Db::name('circle_comment')->where('cid',$id)->delete();
            return json(['code'=>1,'msg'=>'删除成功']);
        }else{
            return json(['code'=>0,'msg'=>'删除失败']);
        }
    }
    //评论圈子
    public function circle_comment(){
        $id=input('id');
        $content=input('content');
        if (empty($id)) {
            return json(['code'=>0,'msg'=>'提交参数有误']);
        }
        $circle=Db::name('circle')->where('id',$id)->find();
        if (empty($circle)){
            return json(['code'=>0,'msg'=>'圈子不存在']);
        }
        if (empty($content)) {
            return json(['code'=>0,'msg'=>'请输入评论内容']);
        }
        $circle_comment=new CircleComment();
        $insert['cid']=$id;
        $insert['content']=$content;
        $insert['user_id']=$this->user_id;
        $res=$circle_comment->save($insert);
        $log=$log1=true;
        //评论圈子赠送积分
        $comment_given_jifen=getSysConfig('rate.comment_given_jifen');
        $arr=explode('|',$comment_given_jifen);
        $jifen_arr=config('app.jifen_arr');
        $jifen=$arr[0];
        $account_log=new AccountLog();
        if ($jifen>0) {
            //统计今日发布评论赠送了多少积分
            $sum_where[]=['user_id','=',$this->user_id];
            $sum_where[]=['type','=',2];
            $sum_where[]=['desc','like',"{$jifen_arr[6]}"];
            $sum=$account_log->where($sum_where)->whereDay('add_time')->sum('money');
            //有最大限制
            if ($arr[1]){
                if ($sum+$jifen<=$arr[1]){
                    $log=accountLog($this->user_id,$jifen,$jifen_arr[6],2);
                }
            }else{//没有最大限制?
                $log=accountLog($this->user_id,$jifen,$jifen_arr[6],2);
            }
        }
        //圈子被评论赠送积分
        $quilt_comment_given_jifen=getSysConfig('rate.quilt_comment_given_jifen');
        $brr=explode('|',$quilt_comment_given_jifen);
        $quilt_jifen=$brr[0];
        if ($quilt_jifen>0) {
            //统计今日被评论赠送了多少积分
            $quilt_sum_where[]=['user_id','=',$circle['user_id']];
            $quilt_sum_where[]=['type','=',2];
            $quilt_sum_where[]=['desc','like',"{$jifen_arr[9]}"];
            $quilt_sum=$account_log->where($quilt_sum_where)->whereDay('add_time')->sum('money');
            //有最大限制
            if ($brr[1]){
                if ($quilt_sum+$quilt_jifen<=$brr[1]){
                    $log1=accountLog($circle['user_id'],$quilt_jifen,$jifen_arr[9],2);
                }
            }else{//没有最大限制?
                $log1=accountLog($circle['user_id'],$quilt_jifen,$jifen_arr[9],2);
            }
        }
        if ($res && $log && $log1){
            //增加评论数
            Db::name('circle')->where('id',$id)->inc('comment')->update();
            return json(['code'=>1,'msg'=>'评论成功']);
        }else{
            return json(['code'=>0,'msg'=>'评论失败']);
        }
    }
    //ajax获取评论
    public function ajax_get_circle_comment(){
        $id=input('id');
        $where[]=['cid','=',$id];
        $circle=new CircleComment();
        $p=input('page');
        $count=$circle->where($where)->count();
        $pages=ceil($count/20);
        $data=[];
        $lists=$circle
            ->where($where)
            ->order('id desc')
            ->page($p,20)
            ->field('id,user_id,content,add_time')
            ->select();
        if ($lists){
            foreach ($lists as $key => $val){
                $user_url=url('Circle/user_circle',['user_id'=>$val['user_id']]);
                $a='<div class="comment-item">
                      <a class="left" href="'.$user_url.'">
                        <img src="'.$val['user']['head_pic'].'">
                      </a>
                      <div class="center">
                        <h5>
                          <span>'.$val['user']['realname'].'</span>
                          <span class="time">'.$val['day_text'].'</span>
                        </h5>
                        <p>'.$val['content'].'</p>
                      </div>
                    </div>';
                $data[]=$a;
            }
        }
        return json(['pages'=>$pages,'data'=>$data]);
    }
    //用户圈子界面
    public function user_circle(){
        $user_id=input('user_id');
        if (empty($user_id)){
            $this->error('提交参数有误');
        }
        $user=Users::find($user_id);
        if (empty($user)){
            $this->error('用户不存在');
        }
        View::assign('user_info', $user);
        return view();
    }
}
