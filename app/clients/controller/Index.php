<?php
/**
*@作者:MissZhang
*@邮箱:<787727147@qq.com>
*@创建时间:2021/7/7 下午4:40
*@说明:首页控制器
*/
namespace app\clients\controller;


use app\common\model\Ad;
use app\common\model\DlUser;
use app\common\model\GoodsCategory;
use think\facade\Db;
use think\facade\View;

class Index extends Base
{
    
     public function  fbpage(){  //fb返回后跳转
        $str=$_SERVER['PHP_SELF']; 
        $strnew=str_replace("/index.php?s=/qiantai/fbpage/",'',$str);
       
        $str=$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'];
        $strnew=str_replace("/index.php?s=/qiantai/fbpage/",'',$str);
        $key=substr($strnew,0,32);
        //$key=str_replace("/",'',$strnew);
      //数据库中找到相应的key对应的链接，再与此处get中的参数进行拼接，然后跳转
      
        $map['linkkey']=$key;
        $res=DB::name('user_cloak')->where($map)->find();
       // echo DB::name('user_cloak')->getLastSql();die();
       file_put_contents('fbpage.txt',$str.input('get.'),FILE_APPEND);  
        if($res){
            $link=$res['link'];
            $ary=input('get.');
          // print_r($ary);
            if($ary){
                foreach($ary as $key2=>$val2){
                   // echo $key2."=".$val."<br>";
                    if($key2=="pxfb"){
                        foreach($val2 as $key3=>$val3){
                            $link=$link."&pxfb[".$key3."]=".$val3;
                        }
                    }else{
                        $link=$link."&".$key2."=".$val2;
                    }
                }
              //  echo ($link);
              ob_start();
              ob_end_clean();
              header("Location:".$link);
              exit;
            }
        }else{
          file_put_contents('fbpage.txt',$str,FILE_APPEND);  
        }  
     }
    public function callback(){
     
            // Please make sure to REPLACE the value of VERIFY_TOKEN 'abc' with 
            // your own secret string. This is the value to pass to Facebook 
            //  when adding/modifying this subscription.
         $app_secret = '2d67e3f252462b0c502b281eaabc10a1';//密钥
        $app_id = '255043567534203';   
      
       $app_secret =$this->app_sercret;//'9df726aeecf8e77c8b8edd1ebc510f6d';
       $app_id = $this->app_id;//'275021805062420';
      // echo $app_id;echo $app_secret;die();
         $ary=input('post.');
         $ary2=input('get.code');
        
       //  print_r($_GET);
       if($_GET['code']){
           file_put_contents('fb.txt',date("Y-m-d H:i:s")."CODE".$_GET['code']."---".$_GET['state']."\/r\/n",FILE_APPEND);
         //数据库查询到相应的fb_id，更新用户表中的code
         $ar=json_decode($_GET['state'],true);
         $res=DB::name('users_adaccount')->where('fb_ad_account_id='.$ar['fb_id'])->find();
         if($res){
             //继续获得token
             $my_url="https://fb.zhuoxinqingdao.cn/qiantai/callback";//获取token返回网址
             $token_url= "https://graph.facebook.com/oauth/access_token?client_id=".$app_id."&redirect_uri=".urlencode($my_url)."&client_secret=" . $app_secret ."&code=".$_GET['code'];
             $rt = $this->curl_get($token_url);
             $jsonrt = json_decode($rt, true);
     // echo "第次token";
            if($jsonrt['error']['code']){
                echo "code已过期，请重新进行授权操作";die();
            }
          
             $access_token=$jsonrt['access_token'];
             $token_url2= "https://graph.facebook.com/oauth/access_token?client_id=".$app_id."&redirect_uri=".urlencode($my_url)."&client_secret=" . $app_secret ."&grant_type=fb_exchange_token&fb_exchange_token=".$access_token; 
             $rt2 = $this->curl_get($token_url2);
            //echo "<br><br><br>long token";
             $jsonrt2 = json_decode($rt2, true);
            //print_r($jsonrt2);
             $save['access_token']=$jsonrt2['access_token'];
             $save['code']=$_GET['code'];
             $save['nickname']=$ar['fb_owner'];
             $save['token_expire']=time()+$jsonrt2['expires_in'];
             DB::name('users_adaccount')->where('fb_ad_account_id='.$ar['fb_id'])->save($save);
             echo "恭喜！".$save['nickname'].", facebook授权成功！授权有效期至".date("Y-m-d H:i:s",$save['token_expire']); 
         }else{
            echo "请联系管理员，将您添加为我们的正式用户后，再执行操作";  
         }
       }else{
           echo "无效访问";
       }
         
        
    }
    public function curl_get($url)
    {
        $info = curl_init();
        curl_setopt($info, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($info, CURLOPT_HEADER, 0);
        curl_setopt($info, CURLOPT_NOBODY, 0);
        curl_setopt($info, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($info, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($info, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($info, CURLOPT_URL, $url);
        $output = curl_exec($info);
        curl_close($info);
        return $output;
    }
     public function fbreturn(){
       
        /*
      $rt = $this->curl_get('https://api.weixin.qq.com/sns/oauth2/access_token?appid=' . $appid . '&secret=' . $appsecret . '&code=' . $_POST['code'] . '&grant_type=authorization_code');
              
         $jsonrt = json_decode($rt, true);*/
         $ary=input('post.');
         $ary2=input('get.');
         file_put_contents('fb.txt',$ary."---".$ary2,FILE_APPEND);
        //  if (isset($jsonrt['errcode'])) {
        //   return json(['code'=>0,'msg'=>$jsonrt['errmsg']]);
        //  }
      
    }
    //首页
    public function index(){
        
        $ad=Ad::where('open=1')->order('sort asc,ad_id desc')->column('ad_link,image');
        $cate_list=GoodsCategory::where('is_show=1')->order('sort asc,id desc')->column('id,name');
        View::assign('ad',$ad);
        View::assign('cate_list',$cate_list);
        return view();
    }

    //根据经纬度获取地址
    public function get_location(){
        //纬度
        $latitude=input('latitude');
        //经度
        $longitude=input('longitude');
        if ($latitude && $longitude){
            //金鼎凤凰城 115.508265,35.237464
            $result=getAreaFromLat($latitude,$longitude);
        }
        $business="未知地区";
        if ($result['business']){
            $business=$result['business'];
        }
        return json(['code' => 1, 'msg' => '获取成功', 'business' =>$business]);
    }
    //附近
    public function nearby(){
        $result=getArea();
        $latitude=$longitude="";
        if ($result['latitude']){
            $latitude=$result['latitude'];
        }
        if ($result['longitude']){
            $longitude=$result['longitude'];
        }
        View::assign('latitude',$latitude);
        View::assign('longitude',$longitude);
        return view();
    }
    //获取附近代理
    public function get_nearby(){
        //纬度
        $latitude=input('latitude');
        //经度
        $longitude=input('longitude');
        $p=input('page');
        $dl_user=new DlUser();
        $count=$dl_user->where(1)->count();
        $pages=ceil($count/10);
        if ($latitude && $longitude){
            $field="ACOS(SIN(($latitude * 3.1415) / 180 ) *SIN((latitude * 3.1415) / 180 ) + COS(($latitude * 3.1415) / 180 ) * COS((latitude * 3.1415) / 180 ) *COS(($longitude * 3.1415) / 180 - (longitude * 3.1415) / 180 ) ) * 6380 as dis";
            $lists=$dl_user->where(1)
                ->page($p,10)
                ->field("id,latitude,longitude,user_id,$field")
                ->order('dis asc,id desc')
                ->select();
        }else{
            $lists=$dl_user->where(1)
                ->order('id desc')
                ->page($p,10)
                ->select();
        }
        $data=[];
        if ($lists){
            foreach ($lists as $key => $val){
                $mobile=substr_replace($val['user']['mobile'],'****',3,4);
                $a='<a href="javascript:;" class="nearby-item">
                      <div class="left">
                        <img src="'.$val['user']['head_pic'].'">
                      </div>
                      <div class="right">
                          <h5>'.$val['user']['realname'].'</h5>
                          <p>'.$mobile.'</p>
                      </div>
                    </a>';
                $data[]=$a;
            }
        }
        return json(['pages'=>$pages,'data'=>$data]);
    }
    //ajax请求商品
    public function ajax_index(){
        $p=input('page');
        $cate_id=input('cate_id');
        $where[]=['is_on_sale','=',1];
        if ($cate_id){
            $where[]=['cat_id_1','=',$cate_id];
        }
        $goods_model=new \app\common\model\Goods();
        $count=$goods_model->where($where)->count();
        $pages=ceil($count/10);
        $lists=$goods_model->where($where)
            ->field('goods_id,goods_img,goods_price,level_price,goods_name')
            ->order('sort asc,goods_id asc')
            ->page($p,10)
            ->select();
        $data=[];
        if ($lists){
            foreach ($lists as $key => $val){
                $img=$val['goods_img'] ?: '/static/images/not_adv.png';
                $goods_price=$val->getDengjiPrice($this->user['level']);
                $a='<div class="goods-item">';
                $a.='<a href="'.url('Goods/goods_info',['id'=>$val['goods_id']]).'">';
                $a.='<img class="goods-img" src="'.$img.'">';
                $a.='</a>';
                $a.='<div class="goods-info">
                    <div class="goods-name">'.$val['goods_name'].'</div>
                    <div class="goods-bot">
                      <div class="left">¥'.$goods_price.'</div>
                      <img class="add-cart" onclick="add_cart('.$val['goods_id'].')" src="/static/mobile/img/add-cart.png">
                    </div>
                  </div>';
                $a.='</div>';
                $data[]=$a;
            }
        }
        return json(['pages'=>$pages,'data'=>$data]);
    }
    //获取下级地区
    public function get_region(){
        $parent_id = input('parent_id'); // 商品分类 父id
        $list = Db::name('region')->where('parent_id', $parent_id)->column('id,name');
        if ($list) {
            return json(['code' => 1, 'msg' => '获取成功！', 'result' => $list]);
        }
        return json(['code' => 0, 'msg' => '获取失败！', 'result' =>[]]);
    }
}
