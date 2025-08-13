<?php
/**
*@作者:MissZhang
*@邮箱:<787727147@qq.com>
*@创建时间:2021/7/7 下午4:41
*@说明:商品控制器
*/
namespace app\mobile\controller;


use app\common\model\GoodsCategory;
use think\facade\Db;
use think\facade\View;

class Goods extends Base
{
    //首页
    public function index()
    {
        return view();
    }
    //商品列表页
    public function goods_list(){
        $keyword=input('keyword');
        $cat_id=input('cat_id');
        $is_recommend=input('is_recommend');
        $order=input('order','all');
        $sort=input('sort','asc');
        View::assign('order',$order);
        View::assign('sort',$sort);
        View::assign('keyword',$keyword);
        View::assign('cat_id',$cat_id);
        View::assign('is_recommend',$is_recommend);
        return view();
    }
    //ajax加载商品列表
    public function ajax_goods(){
        $keyword=input('keyword');
        $cat_id=input('cat_id');
        $order=input('order','all');
        $sort=input('sort','asc');
        $is_recommend=input('is_recommend');
        $where="is_on_sale=1";
        $orders="last_update desc";
        if ($keyword){
            $where.=' and goods_name like '."'%".$keyword."%'";
        }
        if ($order!='all'){
            $orders="{$order} {$sort}";
        }
        if ($is_recommend){
            $where.='and is_recommend=1';
        }
        if ($cat_id){
            $level=Db::name('goods_category')->where('id',$cat_id)->value('level');
            if ($level){
                $where.=' and cat_id_'.$level.'='.$cat_id;
            }
        }
        $p=input('page');
        $count=Db::name('goods')
            ->where($where)
            ->count();
        $lists=Db::name('goods')
            ->where($where)
            ->order($orders)
            ->field('goods_id,goods_img,goods_price,goods_name')
            ->page($p,10)
            ->select();
        $pages=ceil($count/10);
        $data=[];
        if ($lists){
            foreach ($lists as $key => $val){
                $img=$val['goods_img'] ? $val['goods_img'] : '/public/images/not_adv.jpg';
                $a='<div class="public-list-item"><a href="'.url('Goods/goods_info',['id'=>$val['goods_id']]).'" class="public-list-item-a">';
                $a.='<div class="img"><img src="'.$img.'"></div>';
                $a.='<div class="good"><p class="word m4">'.$val['goods_name'].'</p><p class="price"><span>￥'.$val['goods_price'].'</span></p></div></a>';
                $a.='</div>';
                $data[]=$a;
            }
        }
        return json(['pages'=>$pages,'data'=>$data]);
    }
    //商品详情页
    public function goods_info()
    {
        $goods_id = input("id/d");
        $goodsModel = new \app\common\model\Goods();
        $goods = $goodsModel::find($goods_id);
        if (empty($goods) || $goods['is_on_sale'] == 0) {
            $this->error('此商品不存在或者已下架');
        }
        $spec_goods_price=Db::name('spec_goods_price')->where('goods_id',$goods_id)->order('store_count desc')->column('price,key_name,store_count,item_id','key');
        $keys=Db::name('spec_goods_price')->where('goods_id',$goods_id)->order('store_count desc')->column('key');
        $key=implode('_',$keys);
        $filter_spec = array();
        //数据库表前缀
        $prefix=Config('database.connections.mysql.prefix');
        if ($keys) {
            $spec_image_where[]=['goods_id','=',$goods_id];
            $spec_image_where[]=['src','<>',''];
            // 规格对应的 图片表， 例如颜色
            $specImage = Db::name('spec_image')->where($spec_image_where)->column("src",'spec_image_id');
            $keys = str_replace('_', ',', $key);
            $sql = "SELECT a.name,a.sort,b.* FROM {$prefix}spec AS a INNER JOIN {$prefix}spec_item AS b ON a.id = b.spec_id WHERE b.id IN($keys) ORDER BY b.id";
            $filter_spec2 =Db::query($sql);
            foreach ($filter_spec2 as $key => $val) {
                $filter_spec[$val['name']][] = array(
                    'item_id' => $val['id'],
                    'item' => $val['item'],
                    'src' => $specImage[$val['id']],
                );
            }
        }
        $user_id = 0;
        $edit=0;
        $text='';
        if (session('user')){
            $user_id=session('user')['user_id'];
        }
        if ($user_id){
            $coll=Db::name('goods_collect')->where('user_id='.$user_id.' and goods_id='.$goods_id)->find();
            if ($coll){
                $edit=1;//已收藏
            }
        }
        if ($user_id) {
//            $goods->add_visit_log($user_id, $goods);
            $collect = Db::name('goods_collect')->where(array("goods_id" => $goods_id, "user_id" => $user_id))->count(); //当前用户收藏
            View::assign('collect', $collect);
        }
        //把主图添加到相册中
         $goods_images[]=$goods['goods_img'];
        foreach ($goods->goods_images as $image){
            $goods_images[]=$image['image_url'];
        }
        View::assign('goods_images', $goods_images);
        View::assign('goods', $goods);
        View::assign('edit', $edit);
        View::assign('text', $text);
        View::assign('filter_spec', $filter_spec);
        View::assign('spec_goods_price', json_encode($spec_goods_price,true));
        return view();
    }
    //    收藏商品
    public function collect_goods(){
        $user_id=$this->user_id;
        if ($user_id==0){
            return json(['code'=>-100,'msg'=>'请先登录','url'=>url('User/login')->build()]);
        }
        $type=input('type');//1收藏2取消
        $goods_id=input('goods_id');
        $map['user_id']=$user_id;
        $map['goods_id']=$goods_id;
        $map['add_time']=time();
        if ($type==1){
            $msg='收藏成功';
            $res=Db::name('goods_collect')->insert($map);
        }else{
            $msg='取消成功';
            $res=Db::name('goods_collect')->where('goods_id='.$goods_id.' and user_id='.$user_id)->delete();
        }
        if ($res){
            return json(['code'=>1,'msg'=>$msg]);
        }else{
            return json(['code'=>0,'msg'=>'收藏失败']);
        }
    }
    /**
     * 分类列表显示
     */
    public function category_list()
    {
        $catList=GoodsCategory::where('parent_id=0 and is_show=1')->order('sort asc,id asc')->column('id,name');
        View::assign('catList', $catList);
        return view();
    }
    //ajax请求商品
    public function ajax_cate_goods(){
        $p=input('page');
        $cat_id=input('cat_id');
        if (empty($cat_id)){
            return json(['pages'=>1,'data'=>[]]);
        }
        $cat_where[]=['is_show','=',1];
        $cat_where[]=['parent_id','=',$cat_id];
        $count=Db::name('goods_category')->where($cat_where)->count();
        $pages=ceil($count/10);
        $lists=Db::name('goods_category')->where($cat_where)
            ->field('id,name')
            ->order('sort asc,id desc')
            ->page($p,10)
            ->select()
            ->toArray();
        foreach ($lists as $key1 => $value){
            $goods_where[]=['cat_id_2','=',$value['id']];
            $goods_where[]=['is_on_sale','=',1];
            $child=Db::name('goods')->where($goods_where)
                ->field('goods_id,goods_img,goods_price,goods_name')
                ->order('sort asc,goods_id desc')
                ->page($p,10)
                ->select()
                ->toArray();
            unset($goods_where);
            $lists[$key1]['child']=$child;
        }
        $data=[];
        if ($lists){
            foreach ($lists as $key => $val){
                $a='<div class="goods-cate-container">';
                $a.='<a href="'.url('Goods/goods_list',['cat_id'=>$val['id']]).'" class="goods-cate-tit">'.$val['name'].'</a>';
                $a.='<div class="goods-items">';
                foreach ($val['child'] as $child){
                    $img=$child['goods_img'] ? $child['goods_img'] : '/public/images/not_adv.jpg';
                    $a.='<a href="'.url('Goods/goods_info',['id'=>$child['goods_id']]).'" class="goods-item">';
                    $a.='<img class="goods-img" src="'.$img.'">';
                    $a.='<div class="goods-info">
                    <div class="goods-name">'.$child['goods_name'].'</div>
                    <div class="goods-bot">
                      <div class="left">¥'.$child['goods_price'].'</div>
                      <div class="right">立即购买</div>
                    </div>
                  </div>';
                    $a.='</a>';
                }
                $a.='</div>';
                $a.='</div>';
                $data[]=$a;
            }
        }
        return json(['pages'=>$pages,'data'=>$data]);
    }
    public function ajax_cate(){
        $cat_id=input('cat_id');
        $cat1List=Db::name('goods_category')
            ->where('parent_id='.$cat_id.' and is_show=1')
            ->order('sort asc,id asc')
            ->field('id,name')
            ->select()
            ->toArray();
        foreach ($cat1List as $key => $val){
            $cat1List[$key]['item']=Db::name('goods_category')
                ->where('parent_id='.$val['id'].' and is_show=1')
                ->order('sort asc,id asc')
                ->field('id,name,image')
                ->select();
        }
        $data=[];
        foreach ($cat1List as $k => $v){
            $a='<div class="cate-list">';
            $a.='<a class="title" href="'.url('Goods/goods_list',['cat_id'=>$v['id']]).'">'.$v['name'].'</a>';
            $a.='<div class="cate-item-list">';
            foreach ($v['item'] as $item){
                $img=$item['image'] ? $item['image'] : '/public/images/not_adv.jpg';
                $a.='<a href="'.url('Goods/goods_list',['cat_id'=>$item['id']]).'" class="cate-item"><dl><dt><img src="'.$img.'"></dt><dd>'.$item['name'].'</dd></dl></a>';
            }
            $a.='</div>';
            $a.='</div>';
            $data[]=$a;
        }
        exit(json_encode(['code'=>1,'data'=>$data]));
    }
}
