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

/*
require_once root_path('vendor').'/autoload.php';
use FacebookAds\Object\Campaign;
use FacebookAds\Object\Values\InsightsLevels;
use FacebookAds\Object\Values\InsightsFields;
use FacebookAds\Object\AdAccount;
use FacebookAds\Object\AdsInsights;
use FacebookAds\Api;
use FacebookAds\Logger\CurlLogger; */

class Api extends Base
{
    public function folders(){
        $str=$_SERVER['QUERY_STRING'];
        $key=str_replace("s=/clients/folders/",'',$str);
     
        $map['link']=$key;
        $res=DB::name('users_clients')->where($map)->find();

        if($res){
             $le=substr($res['firstname'],0,1);
             $le2=substr($res['lastname'],0,1);
             $name=$le.$le2;
           View::assign('shortname', $name); 
           View::assign('questions', $res); 
           $sublink="http://".$_SERVER['HTTP_HOST'].'/clients/submission/'.$res['link'];
    
           View::assign('link', $sublink); 
        }else{
          die('Something goes wrong');
        }  
       
       return view();
    }
    public function submission(){
        $str=$_SERVER['QUERY_STRING'];
        $key=str_replace("s=/clients/submission/",'',$str);
      
        $map['link']=$key;
        $res=DB::name('users_clients')->where($map)->find();
       // echo DB::name('users_clients')->getLastSql();die();
        
        $actionpage="http://".$_SERVER['HTTP_HOST'].'/clients/submission/'.$res['link'];
        $page_ary=array(
            '0'=>array('len'=>0,'pname'=>'submission','next'=>'1','parent'=>'0','step'=>0),
            '1'=>array('len'=>0,'pname'=>'page1','next'=>'2','parent'=>'submission','step'=>0),
            '2'=>array('len'=>0,'pname'=>'page2','next'=>'3','parent'=>'page1','step'=>0),
            '3'=>array('len'=>2,'pname'=>'page3','next'=>array('0'=>'page4','1'=>'page3-1'),'parent'=>'page2','step'=>0),
            '4'=>array('len'=>0,'pname'=>'page4','next'=>'page5','parent'=>3,'step'=>0),
            '5'=>array('len'=>0,'pname'=>'page3-1','next'=>'page3-2','parent'=>'page3','step'=>1),
            '6'=>array('len'=>0,'pname'=>'page3-2','next'=>'page5','parent'=>'page3-1','step'=>0),
            '7'=>array('len'=>4,'pname'=>'page5','next'=>'page6','parent'=>'choice-basic_info_did_address_change','next'=>array('single'=>'page6.html','married'=>'page6-1','divioced'=>'page6','separated'=>'page6','widowed'=>'page6')),//What's your marital status?
            '8'=>array('len'=>0,'pname'=>'page6','next'=>'choice-dependents_count','parent'=>'page5'),//How many children or others do you support?
            '9'=>array('len'=>0,'pname'=>'page6-1','next'=>'page6-1-1','parent'=>'page5'),//Tell us about your spouse
            '10'=>array('len'=>0,'pname'=>'page6-1-1','next'=>'page6','parent'=>'page6-1'),//Let's go over your spouse's driver's license or state ID
            '12'=>array('len'=>0,'pname'=>'page11','next'=>'page12','parent'=>'choice-dependents_count'),// Good job
            '13'=>array('len'=>0,'pname'=>'page7','next'=>'page8','parent'=>'page6'),// first dependent
            '14'=>array('len'=>0,'pname'=>'page8','next'=>'page8','parent'=>'page6'),// second dependent
            );
        $page_ary2=array(
            'submission'=>array('len'=>0,'next'=>'page1','parent'=>'0','step'=>0),
            'page1'=>array('len'=>0,'pname'=>'page1','next'=>'page2','parent'=>'submission','step'=>1),
            'page2'=>array('len'=>0,'pname'=>'page2','next'=>'page3','parent'=>'page1','step'=>2),
            'page3'=>array('len'=>2,'pname'=>'page3','next'=>'basic_info_did_address_change','parent'=>'page2','step'=>3),//Did you move to a new address in 2023?
            'page4'=>array('len'=>0,'pname'=>'page4','next'=>'page5','parent'=>'page3','step'=>4),//no What's your address?
            'page3-1'=>array('len'=>0,'pname'=>'page3-1','next'=>'page3-2','parent'=>'page3','step'=>4),//new address 1
            'page3-2'=>array('len'=>0,'pname'=>'page3-2','next'=>'page5','parent'=>'page3-1','step'=>5),//new address 2
            'page5'=>array('len'=>4,'pname'=>'page5','next'=>'page6','parent'=>'basic_info_did_address_change','next'=>array('single'=>'page6.html','married'=>'page6-1','divioced'=>'page6','separated'=>'page6','widowed'=>'page6'),'step'=>6),//What's your marital status?
            'page6'=>array('len'=>0,'pname'=>'page6','next'=>'dependents_count','parent'=>'page5','step'=>7),//How many children or others do you support?
            'page6-1'=>array('len'=>0,'pname'=>'page6-1','next'=>'page6-1-1','parent'=>'page5','step'=>7),//Tell us about your spouse
            'page6-1-1'=>array('len'=>0,'pname'=>'page6-1-1','next'=>'page6','parent'=>'page6-1','step'=>8),//Let's go over your spouse's driver's license or state ID
            'page11'=>array('len'=>0,'pname'=>'page11','next'=>'page12','parent'=>'dependents_count','step'=>10),// Good job
            'page7'=>array('len'=>1,'pname'=>'page7','next'=>'dependents_count','parent'=>'dependents_count','step'=>9),// first dependent
            'page8'=>array('len'=>2,'pname'=>'page8','next'=>'dependents_count','parent'=>'dependents_count','step'=>10),// second dependent
            'page9'=>array('len'=>3,'pname'=>'page9','next'=>'dependents_count','parent'=>'dependents_count','step'=>10),// second dependent
            'page10'=>array('len'=>4,'pname'=>'page10','next'=>'dependents_count','parent'=>'dependents_count','step'=>10),// second dependent
            );
        if($res){
           $mainlink="http://".$_SERVER['HTTP_HOST'].'/clients/folders/'.$res['link'];
           
           View::assign('actionpage', $actionpage);
           View::assign('detail', $res);
           
           View::assign('mainlink', $mainlink);
           View::assign('questions', $res); 
           //$sublink="http://".$_SERVER['HTTP_HOST'].'/clients/submission/'.$res['link'];
           View::assign('link', $sublink); 
        }else{
          die('Something goes wrong');
        }  
       if(IS_POST){
            $data=input('post.');
            if(isset($page_ary2[$data['page']])){
                $curpage=$page_ary2[$data['page']];
            }else{
                $curpage=$page_ary2['submission'];
            }
                switch($curpage['parent']){
                    case 'basic_info_did_address_change':
                        if($res['basic_info_did_address_change']==1){ //搬迁
                            $lastpage="page3-2";
                        }else{
                            $lastpage="page4"; //默认不搬迁
                        }
                        break;
                    case 'dependents_count':
                        break;
                    default:
                        $parent=$curpage['parent'];
                }
                switch($curpage['next']){
                    case 'basic_info_did_address_change':
                        if($res['basic_info_did_address_change']==1){ //搬迁
                            $nextpage="page3-2";
                        }else{
                            $nextpage="page4"; //默认不搬迁
                        }
                        break;
                    case 'dependents_count':
                        if($res['len']==0){
                            if($res['dependents_count']==0){
                              $nextpage="page11";//直接 完成
                            }
                            if($res['dependents_count']==1){
                                $nextpage="page7";
                            }
                            if($res['dependents_count']==2){
                                $nextpage="page8";
                            }
                            if($res['dependents_count']==3){
                                $nextpage="page9";
                            }
                            if($res['dependents_count']==4){
                                $nextpage="page10";
                            }
                        }
                        if($res['len']==1){
                            if($res['dependents_count']>=2){
                                $nextpage="page8";
                            }
                        }
                        if($res['len']==2){
                            if($res['dependents_count']>=3){
                                $nextpage="page9";
                            }
                        }
                        if($res['len']==3){
                            if($res['dependents_count']>=4){
                                $nextpage="page10";
                            }
                        }
                        if($res['len']==4){
                            if($res['dependents_count']>=4){
                                $nextpage="page11";
                            }
                        }
                    default:
                        $nepage=$curpage['next'];
                }
           
            $percent=$curpage['step'];
            if($data['page']){
                //$lastpage=$data['page']-1;
                //$nextpage=$data['page']+1;
                $percent=$data['page'];
                $pagename="page".$percent;
                View::assign('percent', $percent);
                View::assign('lastpage', $lastpage);
                View::assign('nextpage', $nextpage);
                $save['cpage']=$percent;
                $save['updatetime']=time();
                if($res){
                   $rr=DB::name('users_clients')->where($map)->save($save);
                }
                return view($pagename);
            }else{
                $percent=0;
                $lastpage=0;
                $nextpage=1;
                View::assign('percent', $percent);
                View::assign('lastpage', $lastpage);
                View::assign('nextpage', $nextpage);
                $save['cpage']=$percent;
                $save['updatetime']=time();
                if($res){
                   $rr=DB::name('users_clients')->where($map)->save($save);
                }
                return view();
            }
            
        }else{
           
            if($res['cpage']){
                $percent=$res['cpage'];
                $lastpage=$res['cpage']-1;
                $nextpage=$res['cpage']+1;
                View::assign('percent', $percent);
                View::assign('lastpage', $lastpage);
                View::assign('nextpage', $nextpage);
                $pagename="page".$percent;
                return view($pagename);
            }else{
                $percent=0;
                $lastpage=0;
                $nextpage=1;
                View::assign('percent', $percent);
                View::assign('lastpage', $lastpage);
                View::assign('nextpage', $nextpage);
                return view();
            } 
        }
       
    }
    public function pageshow(){
        
    }
    public function pagesave(){
        if (IS_POST){
            $data=input('post.');
            if(!$data['fieldname']){
                return json(['code'=>0]);  
            }
            $save[$data['fieldname']]=$data['fieldvalue'];
            $save['cpage']=$data['percent'];
            $save['last_field']=$data['fieldname'];
            $save['updatetime']=time();
            $map['link']=$data['link'];
            $res=DB::name('users_clients')->where($map)->find();
            if($res){
               $rr=DB::name('users_clients')->where($map)->save($save);
            //   echo DB::name('users_clients')->getLastSql();die();
                $customFormat = 'g:i A'; //显示样式  Jan 11, 2024, 10:0 AM  F为英文月份全称 jS为6th第几天
                $savetime="Saved at ".date($customFormat,$save['updatetime']); //将当前时间按照自定义格式转换成字符串
               return json(['code'=>1,'savetime'=>$savetime]); 
            }else{
               return json(['code'=>0]);  
            }
            //保存字段
            //保存相应的字段，修改最后编辑字段，修改最后保存时间  通过最后保存字段，获得在哪一页
        }
    }
    public function answers(){
      return view();
    }
    //首页
    public function fbreturn2(){
        echo "vvv";
        die();
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
}
