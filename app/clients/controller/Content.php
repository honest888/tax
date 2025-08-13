<?php
/**
 *@作者:MissZhang
 *@邮箱:<787727147@qq.com>
 *@创建时间:2021/7/30 上午10:18
 *@说明:内容管理控制器
 */

namespace app\mobile\controller;


use app\common\model\Video;
use app\common\model\VideoCategory;
use think\facade\Db;
use think\facade\View;

class Content extends Base
{
    //公告列表
    public function essay_list(){
        return view();
    }
    //ajax获取公告
    public function ajax_essay_list(){
        $p=input('page');
        $count=Db::name('essay')->where('open=1')->count();
        $pages=ceil($count/$this->page_size);
        $lists=Db::name('essay')
            ->where('open=1')
            ->order('sort asc,id desc')
            ->page($p,$this->page_size)
            ->field('id,title,description,image')
            ->select();
        $data=[];
        if ($lists){
            foreach ($lists as $key => $val){
                $img=$val['image'] ? $val['image'] : '/public/images/not_adv.jpg';
                $a='<a href="'.url('Content/essay_detail',['artid'=>$val['id']]).'" class="notice-list-item">';
                $a.='<div class="left">
                    <img src="'.$img.'">
                </div>';
                $a.='<div class="right">
                    <h6>'.$val['title'].'</h6>
                    <p>'.$val['description'].'</p>
                </div>';
                $a.='</a>';
                $data[]=$a;
            }
        }
        return json(['pages'=>$pages,'data'=>$data]);
    }
    //公告详情
    public function essay_detail(){
        $artid=input('artid');
        $art=Db::name('essay')->where('id',$artid)->find();
        if (empty($art) || $art['open']==0){
            $this->error('公告不存在');
        }
        View::assign('art',$art);
        return view();
    }
    //协议详情
    public function protocol_detail(){
        $artid=input('artid');
        $art=Db::name('protocol')->where('id',$artid)->find();
        if (empty($art)){
            $this->error('协议不存在');
        }
        View::assign('art',$art);
        return view();
    }
    //ajax获取文章
    public function ajax_article_list(){
        $p=input('page');
        $count=Db::name('article')->where('open=1')->count();
        $pages=ceil($count/$this->page_size);
        $lists=Db::name('article')
            ->where('open=1')
            ->order('sort asc,id desc')
            ->page($p,$this->page_size)
            ->field('id,title,description,image')
            ->select();
        $data=[];
        if ($lists){
            foreach ($lists as $key => $val){
                $img=$val['image'] ? $val['image'] : '/public/images/not_adv.jpg';
                $a='<a href="'.url('Content/article_detail',['artid'=>$val['id']]).'" class="notice-list-item">';
                $a.='<div class="left">
                    <img src="'.$img.'">
                </div>';
                $a.='<div class="right">
                    <h6>'.$val['title'].'</h6>
                    <p>'.$val['description'].'</p>
                </div>';
                $a.='</a>';
                $data[]=$a;
            }
        }
        return json(['pages'=>$pages,'data'=>$data]);
    }
    //文章详情
    public function article_detail(){
        $artid=input('artid');
        $art=Db::name('article')->where('id',$artid)->find();
        if (empty($art) || $art['open']==0){
            $this->error('文章不存在');
        }
        View::assign('art',$art);
        return view();
    }
    //视频列表
    public function video_list(){
        $cate_list=VideoCategory::where('is_show=1')->order('sort asc,id desc')->column('id,name');
        View::assign('cate_list',$cate_list);
        return view();
    }
    //ajax获取视频列表
    public function ajax_video_list(){
        $p=input('page');
        $video=new Video();
        $cate_id=input('cate_id');
        $where[]=['is_show','=',1];
        if ($cate_id){
            $where[]=['vid','=',$cate_id];
        }
        $count=$video->where($where)->count();
        $pages=ceil($count/$this->page_size);
        $lists=$video->where($where)
            ->order('sort asc,id desc')
            ->page($p,$this->page_size)
            ->field('id,title,duration,video_url,image')
            ->select();
        $data=[];
        if ($lists){
            foreach ($lists as $key => $val){
                $img=$val['image'] ? $val['image'] : '/public/images/not_adv.jpg';
                $a='<div class="video-item">
                      <div class="video-title">'.$val['title'].'</div>
                      <img src="/static/mobile/img/play.png" data-id="'.$val['id'].'" class="play">
                      <video class="video" id="video'.$val['id'].'" data-id="'.$val['id'].'" src="'.$val['video_url'].'" poster="'.$img.'">您的浏览器不支持 video 标签。</video>
                      <div class="video-duration">'.$val['duration'].'</div>
                    </div>';
                $data[]=$a;
            }
        }
        return json(['pages'=>$pages,'data'=>$data]);
    }
}