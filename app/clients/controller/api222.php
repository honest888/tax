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
use think\facade\Filesystem;
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
    protected function initialize()
    {
        parent::initialize();
    }
    public function ajax_remove(){
        if($_POST['pagename']&&$_POST['link']&&$_POST['files']){
            $pagename=$_POST['pagename'];
            $link=$_POST['link'];
            $map['link']=$_POST['link'];
            $res=DB::name('users_clients')->where($map)->find();
            if($res){
               /* $file = request()->file('files');
                try {
                   $result = $this->validate(//PDF, PNG, JPG, CSV, XLSX
                        ['file' => $file],//'jpg', 'png', 'jpg', 'pdf','csv','xlsx' //PDF, PNG, JPG, CSV, XLSX
                        ['file'=>'fileSize:50000000|fileExt:jpg,png,jpeg,gif,pdf,csv,xlsx'],
                        ['file.fileSize' => 'Oversize','file.fileExt'=>'only support jpg,png,jpeg,gif,pdf,csv,xlsx']
                    );
                }catch (ValidateException $e){
                    $error=$e->getError();
                    $values=array_values($error);
                    return json(['code'=>0,'msg'=>$values[0]]);
                }*/
             
                //删除数据库
                if($res['filename']){
                    //$filename= $_FILES["files"]["name"];//$pagename
                    $filename=$_POST['files'];
                    $fary=json_decode($res['filename'],true);
                    $fileary=$fary[$pagename];
                 
                    if($fileary){
                         foreach($fileary as $key=>$val){
                                if($val==$filename){
                                    unset($fary[$pagename][$key]);
                                }
                            }
                            if(empty($fary)){
                                $save['filename']='';
                            }else{
                                $save['filename']=json_encode($fary);
                            }
                                 $save['cpage']=$pagename;
                                 $save['last_field']=$pagename;
                                 $save['updatetime']=time();
                                 $save['status']=1;
                                 $rr=DB::name('users_clients')->where($map)->save($save);
                                 $customFormat = 'g:i A'; //显示样式  Jan 11, 2024, 10:0 AM  F为英文月份全称 jS为6th第几天
                                 $savetime="Saved at ".date($customFormat,$save['updatetime']); //将当前时间按照自定义格式转换成字符串
                                // return json(['code'=>1,'savetime'=>$savetime]);
                                 $path='/www/wwwroot/linshi.zhuoxindongying.cn/public/upload/clients/'.$link."/".$pagename."/".$filename;
                                if (unlink($path)) { //物理删除
                                   ///www/wwwroot/linshi.zhuoxindongying.cn/public/upload
                                   return json(['code'=>1,'savetime'=>$savetime]);
                                }else{
                                   return json(['code'=>0,'msg'=>1]);
                                }
                    }
                   
                }else{
                       return json(['code'=>0,'msg'=>3]);
                }
                
            }else{
                return json(['code'=>0,'msg'=>4]);
            }
        }
  
    }
    public function ajax_upload(){
      //print_r($_FILES);
      //  echo "uploading";
      //  $fileary = request()->file(); //接收的图片及视频
     //   print_r($fileary['files']);
     if($_POST['pagename']&&$_POST['link']){
        $pagename=$_POST['pagename'];
        $link=$_POST['link'];
        $map['link']=$_POST['link'];
        $res=DB::name('users_clients')->where($map)->find();
        if($res){
            if($_FILES){
                if ($_FILES["files"]["error"] > 0){
                        switch($_FILES['files']['error']) { 
                            case 1:    
                             $str='OverSize';
                            break;  
                            case 2:    
                            $str=' Size limited'; 
                            //$this->setError("The file is too large (form).");    
                            break;    
                            case 3:    
                            $str='sub uploaded'; 
                            //$this->setError("The file was only partially uploaded.");    
                            break;  
                            case 4:    
                            $str='No file uploaded'; 
                            break;
                            case 5:    
                            $str='tempfile is losing';
                            break; 
                            case 6:    
                            $str='Error when saving';    
                            break;    
                      }
                      if($_FILES['fileToUpload']['error']!=4){
                          return json(['code'=>0,'msg'=>$str]);
                          //$this->error("Error: " .$str);
                      }
                } else{
                    
                    $file = request()->file('files');
                    try {
                       $result = $this->validate(//PDF, PNG, JPG, CSV, XLSX
                            ['file' => $file],//'jpg', 'png', 'jpg', 'pdf','csv','xlsx' //PDF, PNG, JPG, CSV, XLSX
                            ['file'=>'fileSize:50000000|fileExt:jpg,png,jpeg,gif,pdf,csv,xlsx'],
                            ['file.fileSize' => 'Oversize','file.fileExt'=>'only support jpg,png,jpeg,gif,pdf,csv,xlsx']
                        );
                    }catch (ValidateException $e){
                        $error=$e->getError();
                        $values=array_values($error);
                        return json(['code'=>0,'msg'=>$values[0]]);
                    }
                  
                       // 移动到框架应用根目录/uploads/ 目录下
                        $filename= $_FILES["files"]["name"];//$pagename
                        $new_path = '/clients/'.$link."/".$pagename;
                        // 使用自定义的文件保存规则
                        $info = Filesystem::putFile($new_path,$file,$filename,[],1);
                        if($info){
                            $fileurl = UPLOAD_PATH.$info;
                           /*
                            $filename= $_FILES["files"]["name"];//$pagename
                            $str="/www/wwwroot/linshi.zhuoxindongying.cn/public/uploads/clients/".$pagename."/" .$filename;
                            move_uploaded_file($_FILES["files"]["tmp_name"], $str);
                            $newadd="/uploads/clients/".$pagename."/".$filename;*/
                            if($res['filename']){
                                $fary=json_decode($res['filename'],true);
                            }else{
                               $fary=[]; 
                            }
                                if($fary[$pagename]){
                                    array_push($fary[$pagename],$_FILES["files"]["name"]);
                                }else{
                                    $fary[$pagename][]=$_FILES["files"]["name"];
                                }
                                
                                $fileall=json_encode($fary);
                                $save['filename']=$fileall;
                                $save['cpage']=$pagename;
                                $save['last_field']=$pagename;
                                $save['updatetime']=time();
                                $save['status']=1;
                                $rr=DB::name('users_clients')->where($map)->save($save);
                               
                                $customFormat = 'g:i A'; //显示样式  Jan 11, 2024, 10:0 AM  F为英文月份全称 jS为6th第几天
                                $savetime="Saved at ".date($customFormat,$save['updatetime']); //将当前时间按照自定义格式转换成字符串
                                return json(['code'=>1,'savetime'=>$savetime]);
                        }else{
                            return json(['code'=>0]);
                        }
            			
                        
                 }
          }
        }else{
            return json(['code'=>0]);
        }
         
     }else{
         return json(['code'=>0]);
     }
        
    }
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
           View::assign('detail', $res); 
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
            'submission'=>array('len'=>0,'pname'=>'submission','next'=>'page1','parent'=>'0','step'=>0),
            'page1'=>array('len'=>0,'pname'=>'page1','next'=>'page2','parent'=>'submission','step'=>1),
            'page2'=>array('len'=>0,'pname'=>'page2','next'=>'page3','parent'=>'page1','step'=>2),
            'page3'=>array('len'=>2,'pname'=>'page3','next'=>'basic_info_did_address_change','parent'=>'page2','step'=>3),
            //Did you move to a new address in 2023
            'page4'=>array('len'=>0,'pname'=>'page4','next'=>'page5','parent'=>'page3','step'=>4),//no What's your address?
            'page3-1'=>array('len'=>0,'pname'=>'page3-1','next'=>'page3-2','parent'=>'page3','step'=>4),//new address 1
            'page3-2'=>array('len'=>0,'pname'=>'page3-2','next'=>'page5','parent'=>'page3-1','step'=>5),//new address 2
            'page5'=>array('len'=>4,'pname'=>'page5','next'=>'page6','parent'=>'basic_info_did_address_change','next'=>'basic_info_marital_status','step'=>6),//What's your marital status? array('single'=>'page6.html','married'=>'page6-1','divioced'=>'page6','separated'=>'page6','widowed'=>'page6')
            'page6'=>array('len'=>0,'pname'=>'page6','next'=>'dependents_count','parent'=>'page5','step'=>7),//How many children or others do you support?
            'page6-1'=>array('len'=>0,'pname'=>'page6-1','next'=>'page6-1-1','parent'=>'page5','step'=>7),//Tell us about your spouse
            'page6-1-1'=>array('len'=>0,'pname'=>'page6-1-1','next'=>'page6','parent'=>'page6-1','step'=>8),//Let's go over your spouse's driver's license or state ID
            'page11'=>array('len'=>0,'pname'=>'page11','next'=>'page12','parent'=>'dependents_count','step'=>10),// Good job
            'page7'=>array('len'=>1,'pname'=>'page7','next'=>'dependents_count','parent'=>'dependents_count','step'=>9),// first dependent
            'page8'=>array('len'=>2,'pname'=>'page8','next'=>'dependents_count','parent'=>'dependents_count','step'=>10),// second dependent
            'page9'=>array('len'=>3,'pname'=>'page9','next'=>'dependents_count','parent'=>'dependents_count','step'=>10),// third dependent
            'page10'=>array('len'=>4,'pname'=>'page10','next'=>'dependents_count','parent'=>'dependents_count','step'=>10),// fourth dependent
            
            //第二部分信息
            'page12'=>array('len'=>0,'pname'=>'page12','next'=>'page13','parent'=>'page11','step'=>11),//following sources of income
            'page13'=>array('len'=>0,'pname'=>'page13','next'=>'field_ary','parent'=>'field_ary_pre','step'=>12),//12页所有信息通过值判断显示
            
            'page15-1'=>array('len'=>1,'pname'=>'page15-1','next'=>'field_ary_next','parent'=>'page13','step'=>13,),//Upload all of your W-2 forms22
            'page15-2'=>array('len'=>0,'pname'=>'page15-2','next'=>'page15-2-1','parent'=>'page13','step'=>13),//
            'page15-3'=>array('len'=>0,'pname'=>'page15-3','next'=>'page15-3-1','parent'=>'page13','step'=>13),//Which of the following did you receive from your brokerage accounts?
            'page15-4'=>array('len'=>0,'pname'=>'page15-4','next'=>'page15-4-1','parent'=>'page13','step'=>13),//Upload your 1099-INT forms
            'page15-5'=>array('len'=>0,'pname'=>'page15-5','next'=>'page15-5-1','parent'=>'page13','step'=>13),//Upload your 1099-DIV forms
            'page15-6'=>array('len'=>0,'pname'=>'page15-6','next'=>'page15-6-0','parent'=>'page13','step'=>13),//Upload the 1099-S form you received for each property you sold
            'page15-7'=>array('len'=>0,'pname'=>'page15-7','next'=>'page15-7-1','parent'=>'page13','step'=>13),//
            'page15-8'=>array('len'=>0,'pname'=>'page15-8','next'=>'page15-8-1','parent'=>'page13','step'=>13),//
            'page15-9'=>array('len'=>0,'pname'=>'page15-9','next'=>'page15-9-1','parent'=>'page13','step'=>13),//
            'page15-10'=>array('len'=>0,'pname'=>'page15-10','next'=>'page15-10-1','parent'=>'page13','step'=>13),//
            'page15-11'=>array('len'=>0,'pname'=>'page15-11','next'=>'page15-11-1','parent'=>'page13','step'=>13),//
            'page15-12'=>array('len'=>0,'pname'=>'page15-12','next'=>'page15-12-1','parent'=>'page13','step'=>13),//
            'page15-13'=>array('len'=>0,'pname'=>'page15-13','next'=>'page15-13-1','parent'=>'page13','step'=>13),//How many different properties did you rent to others?
            'page15-14'=>array('len'=>0,'pname'=>'page15-14','next'=>'page15-14-1','parent'=>'page13','step'=>13),//What's the name of your farm?
            'page15-15'=>array('len'=>0,'pname'=>'page15-15','next'=>'page15-15-1','parent'=>'page13','step'=>13),//Upload your 1099-G forms
            'page15-16'=>array('len'=>0,'pname'=>'page15-16','next'=>'page15-16-1','parent'=>'page13','step'=>13),//Upload any K-1's you received
            'page15-17'=>array('len'=>0,'pname'=>'page15-17','next'=>'page15-17-0','parent'=>'page13','step'=>13),//Upload your 1099-SA forms
            'page15-18'=>array('len'=>0,'pname'=>'page15-18','next'=>'page15-18-1','parent'=>'page13','step'=>13),//If you received any W-2G's for your winnings upload them below
            'page15-19'=>array('len'=>0,'pname'=>'page15-19','next'=>'page15-19-1','parent'=>'page13','step'=>13),//Tell us about the alimony
            'page15-20'=>array('len'=>0,'pname'=>'page15-20','next'=>'page15-20-1','parent'=>'page13','step'=>13),//Tell us about the jury duty pay
            'page15-21'=>array('len'=>0,'pname'=>'page15-21','next'=>'page15-21-1','parent'=>'page13','step'=>13),//Upload your 1099-S form
            'page15-22'=>array('len'=>0,'pname'=>'page15-22','next'=>'page15-22-1','parent'=>'page13','step'=>13),//Which of the following forms have you received?
            'page15-23'=>array('len'=>0,'pname'=>'page15-23','next'=>'page15-23-0','parent'=>'page13','step'=>13),//
            'page15-24'=>array('len'=>0,'pname'=>'page15-24','next'=>'field_ary_next','parent'=>'page13','step'=>13),//Is there anything else you'd like to tell us about income you, your spouse, or dependents received in 2023?/
            
            'page15-2-1'=>array('len'=>0,'pname'=>'page15-2-1','next'=>'page15-2-2','parent'=>'page15-2','step'=>14),//Tell us the type of self-employment work you do
            'page15-2-2'=>array('len'=>0,'pname'=>'page15-2-2','next'=>'page15-2-3','parent'=>'page15-2-1','step'=>15),//Do you have a P&L statement or other financial statements for this business?
            'page15-2-3'=>array('len'=>0,'pname'=>'page15-2-3','next'=>'page15-2-3-1','parent'=>'page15-2-2','step'=>16),//Upload your P&L and any other financial statements you have for this business yes
            'page15-2-3-1'=>array('len'=>0,'pname'=>'page15-2-3-1','next'=>'page15-2-4','parent'=>'page15-2-3','step'=>17),//yes 第二步
            'page15-2-3-2'=>array('len'=>0,'pname'=>'page15-2-3-2','next'=>'page15-2-4','parent'=>'page15-2-3-1','step'=>18),//yes 第二步
            
            'page15-2-4'=>array('len'=>0,'pname'=>'page15-2-4','next'=>'page15-2-4','parent'=>'page15-2-2','step'=>18),//How much income did you earn for your work or business? 
            'page15-2-4-nec'=>array('len'=>0,'pname'=>'page15-2-4-nec','next'=>'','parent'=>'page15-2-4','step'=>19),//How much income did you earn for your work or business? 
            'page15-2-4-misc'=>array('len'=>0,'pname'=>'page15-2-4-misc','next'=>'','parent'=>'','step'=>20),//How much income did you earn for your work or business? 
            'page15-2-4-k'=>array('len'=>0,'pname'=>'page15-2-4-k','next'=>'page15-2-5','parent'=>'','step'=>21),//How much income did you earn for your work or business? 
            'page15-2-5'=>array('len'=>0,'pname'=>'page15-2-5','next'=>'','parent'=>'','step'=>22), //Did you issue any refunds or credits to customers?
            'page15-2-5-1'=>array('len'=>0,'pname'=>'page15-2-5-1','next'=>'page15-2-6','parent'=>'page15-2-5','step'=>23),//What was the total amount of refunds and credits you issued to customers?
            'page15-2-6'=>array('len'=>0,'pname'=>'page15-2-6','next'=>'page15-2-7','parent'=>'page15-2-5-1','step'=>24),//Did your business sell physical products?
            'page15-2-6-1'=>array('len'=>0,'pname'=>'page15-2-6-1','next'=>'page15-2-6-2','parent'=>'page15-2-6','step'=>25),//Tell us about your product-related costs
            'page15-2-6-2'=>array('len'=>0,'pname'=>'page15-2-6-2','next'=>'page15-2-7','parent'=>'page15-2-6-1','step'=>26),//What was the inventory at the beginning and end of the year?
            'page15-2-7'=>array('len'=>0,'pname'=>'page15-2-7','next'=>'page15-2-8','parent'=>'page15-2-6','step'=>27),//Did you have any salaried employees?
            
            'page15-2-7-1'=>array('len'=>0,'pname'=>'page15-2-7-1','next'=>'page15-2-7-2','parent'=>'page15-2-7','step'=>28),//upload form v3
            'page15-2-7-2'=>array('len'=>0,'pname'=>'page15-2-7-2','next'=>'page15-2-8','parent'=>'page15-2-7-1','step'=>29),//tax reports
            'page15-2-8'=>array('len'=>0,'pname'=>'page15-2-8','next'=>'page15-2-9','parent'=>'page15-2-7','step'=>30),//did u hire any independent
         
            'page15-2-8-1'=>array('len'=>0,'pname'=>'page15-2-8-1','next'=>'page15-2-8-2','parent'=>'page15-2-8','step'=>31),//How much did you pay the contractors?
            'page15-2-8-2'=>array('len'=>0,'pname'=>'page15-2-8-2','next'=>'page15-2-9','parent'=>'page15-2-8-1','step'=>32),//Did you issue 1099-NEC's to the contractors?
            'page15-2-8-3'=>array('len'=>0,'pname'=>'page15-2-8-3','next'=>'page15-2-9','parent'=>'page15-2-8-2','step'=>33),//Upload the 1099-NEC's you issued to contractors
            
            'page15-2-9'=>array('len'=>0,'pname'=>'page15-2-9','next'=>'page15-2-10','parent'=>'page15-2-8-2','step'=>34),//Did you have a home office?
            'page15-2-9-1'=>array('len'=>0,'pname'=>'page15-2-9-1','next'=>'page15-2-9-2','parent'=>'page15-2-9','step'=>35),//What is the size of your home
            'page15-2-9-2'=>array('len'=>0,'pname'=>'page15-2-9-2','next'=>'page15-2-10','parent'=>'page15-2-9-1','step'=>36),//Tell us about your home office expenses
            'page15-2-10'=>array('len'=>0,'pname'=>'page15-2-10','next'=>'','parent'=>'page15-2-9','step'=>37),//Did you use a car or truck for your work?
            'page15-2-10-1'=>array('len'=>0,'pname'=>'page15-2-10-1','next'=>'page15-2-10-2','parent'=>'page15-2-10','step'=>38),//Tell us about the vehicle you used
            'page15-2-10-2'=>array('len'=>0,'pname'=>'page15-2-10-2','next'=>'page15-2-10-3','parent'=>'page15-2-10-1','step'=>39),//How many miles did you drive with your vehicle during the calendar year?
            'page15-2-10-3'=>array('len'=>0,'pname'=>'page15-2-10-3','next'=>'page15-2-11','parent'=>'page15-2-10-2','step'=>40),//Tell us about the expenses associated with your vehicle
            'page15-2-11'=>array('len'=>0,'pname'=>'page15-2-11','next'=>'page15-2-12','parent'=>'page15-2-10','step'=>41),//If you had any of the following work-related expenses, please let us know
            'page15-2-12'=>array('len'=>0,'pname'=>'page15-2-12','next'=>'page15-2-13','parent'=>'page15-2-11','step'=>42),//Tell us about any other expenses you had related to the work you did
            'page15-2-13'=>array('len'=>0,'pname'=>'page15-2-13','next'=>'page15-2-13-1','parent'=>'page15-2-12','step'=>43),//Did you buy any depreciable assets related to your work or business, other than vehicles?
            'page15-2-13-1'=>array('len'=>0,'pname'=>'page15-2-13-1','next'=>'page15-2-14','parent'=>'page15-2-13','step'=>44),//Upload a spreadsheet listing the assets you purchased for your business
            'page15-2-14'=>array('len'=>0,'pname'=>'page15-2-14','next'=>'page15-2-14-1','parent'=>'page15-2-13','step'=>45),//Did you sell or dispose of any depreciable assets related to your work or business?
            'page15-2-14-1'=>array('len'=>0,'pname'=>'page15-2-14-1','next'=>'page15-2-15','parent'=>'page15-2-14','step'=>46),//Upload a spreadsheet listing the assets you sold or disposed of for your business
            
            'page15-2-15'=>array('len'=>0,'pname'=>'page15-2-15','next'=>'page15-2-15-1','parent'=>'page15-2-14-1','step'=>47),//Those are all the questions we have about the business for now. Did you or your spouse have a second business?
            
            'page15-2-15-1'=>array('len'=>0,'pname'=>'page15-2-15-1','next'=>'page15-2-15-2','parent'=>'page15-2-15','step'=>48),//Tell us about the second business
            'page15-2-15-2'=>array('len'=>0,'pname'=>'page15-2-15-2','next'=>'page15-2-15-3','parent'=>'page15-2-15-1','step'=>49),//If the business has a name tell us what it is
            'page15-2-15-3'=>array('len'=>0,'pname'=>'page15-2-15-3','next'=>'page15-2-15-3-1','parent'=>'page15-2-15-2','step'=>50),//Do you have a P&L statement or other financial statements for this business?
            'page15-2-15-3-1'=>array('len'=>0,'pname'=>'page15-2-15-3-1','next'=>'page15-2-15-3-1-4','parent'=>'page15-2-15-3','step'=>51),//Have you granted us access to your books?
            'page15-2-15-3-1-1'=>array('len'=>0,'pname'=>'page15-2-15-3-1-1','next'=>'page15-2-15-3-1-2','parent'=>'page15-2-15-3-1','step'=>52),//Are all the income and expenses you had for this business reflected on your financial statements?
            'page15-2-15-3-1-2'=>array('len'=>0,'pname'=>'page15-2-15-3-1-2','next'=>'','parent'=>'page15-2-15-3-1-1','step'=>53),//Which of the following forms did you receive for the income you earned?
            'page15-2-15-3-1-3'=>array('len'=>0,'pname'=>'page15-2-15-3-1-3','next'=>'page15-2-15-3-1-2','parent'=>'page15-2-15-3-1-1','step'=>53),//How much income did you earn for the business?
            'page15-2-15-3-1-4'=>array('len'=>0,'pname'=>'page15-2-15-3-1-4','next'=>'page15-2-15-3-1-1','parent'=>'page15-2-15-3-1','step'=>52),//Please grant us access to your books or upload your P&L and financial statements
            
            'page15-2-15-3-1-2-nec'=>array('len'=>0,'pname'=>'page15-2-15-3-1-2-nec','next'=>'','parent'=>'page15-2-15-3-1-2','step'=>53),//How much income did you earn for your work or business? 
            'page15-2-15-3-1-2-misc'=>array('len'=>0,'pname'=>'page15-2-15-3-1-2-misc','next'=>'','parent'=>'','step'=>54),//How much income did you earn for your work or business? 
            'page15-2-15-3-1-2-k'=>array('len'=>0,'pname'=>'page15-2-15-3-1-2-k','next'=>'page15-2-15-4','parent'=>'','step'=>55),//How much income did you earn for your work or business? 
            'page15-2-15-5'=>array('len'=>0,'pname'=>'page15-2-15-5','next'=>'','parent'=>'','step'=>57), //Did you issue any refunds or credits to customers?
            'page15-2-15-5-1'=>array('len'=>0,'pname'=>'page15-2-15-5-1','next'=>'page15-2-15-5-2','parent'=>'page15-2-15-5','step'=>58), //What was the total amount of refunds and credits you issued to customers?
            'page15-2-15-5-2'=>array('len'=>0,'pname'=>'page15-2-15-5-2','next'=>'page15-2-15-4','parent'=>'page15-2-15-5','step'=>59), //Did your business sell physical products?
            'page15-2-15-5-3'=>array('len'=>0,'pname'=>'page15-2-15-5-3','next'=>'page15-2-15-5-4','parent'=>'page15-2-15-5-2','step'=>60), //Tell us about your product-related costs
            'page15-2-15-5-4'=>array('len'=>0,'pname'=>'page15-2-15-5-4','next'=>'page15-2-15-4','parent'=>'page15-2-15-5-3','step'=>61), //What was the inventory at the beginning and end of the year?
            
            'page15-2-15-4'=>array('len'=>0,'pname'=>'page15-2-15-4','next'=>'','parent'=>'','step'=>62), //Did you have any salaried employees?
            
            //employee2
            'page15-2-15-4-1'=>array('len'=>0,'pname'=>'page15-2-15-4-1','next'=>'page15-2-15-4-2','parent'=>'page15-2-15-4','step'=>63),//upload form v3
            'page15-2-15-4-2'=>array('len'=>0,'pname'=>'page15-2-15-4-2','next'=>'page15-2-15-6','parent'=>'page15-2-15-4-1','step'=>64),//Upload your payroll tax reports
            'page15-2-15-6'=>array('len'=>0,'pname'=>'page15-2-15-6','next'=>'page15-2-15-7','parent'=>'page15-2-15-4-2','step'=>65),//Did you hire any independent contractors?
         
            'page15-2-15-7'=>array('len'=>0,'pname'=>'page15-2-15-7','next'=>'page15-2-15-8','parent'=>'page15-2-15-6','step'=>66),//How much did you pay the contractors?
            'page15-2-15-8'=>array('len'=>0,'pname'=>'page15-2-15-8','next'=>'page15-2-15-9','parent'=>'page15-2-15-7','step'=>67),//Did you issue 1099-NEC's to the contractors?
            'page15-2-15-9'=>array('len'=>0,'pname'=>'page15-2-15-9','next'=>'page15-2-15-10','parent'=>'page15-2-15-8','step'=>68),//Upload the 1099-NEC's you issued to contractors
            
            'page15-2-15-10'=>array('len'=>0,'pname'=>'page15-2-15-10','next'=>'page15-2-15-11','parent'=>'page15-2-15-9','step'=>69),//Did you have a home office?
            'page15-2-15-11'=>array('len'=>0,'pname'=>'page15-2-15-11','next'=>'page15-2-15-12','parent'=>'page15-2-15-10','step'=>70),//What is the size of your home
            'page15-2-15-12'=>array('len'=>0,'pname'=>'page15-2-15-12','next'=>'page15-2-15-13','parent'=>'page15-2-15-11','step'=>71),//Tell us about your home office expenses
            'page15-2-15-13'=>array('len'=>0,'pname'=>'page15-2-15-13','next'=>'page15-2-15-14','parent'=>'page15-2-15-12','step'=>72),//Did you use a car or truck for your work?
            'page15-2-15-14'=>array('len'=>0,'pname'=>'page15-2-15-14','next'=>'page15-2-15-15','parent'=>'page15-2-15-13','step'=>73),//Tell us about the vehicle you used
            'page15-2-15-15'=>array('len'=>0,'pname'=>'page15-2-15-15','next'=>'page15-2-15-16','parent'=>'page15-2-15-14','step'=>74),//How many miles did you drive with your vehicle during the calendar year?
            'page15-2-15-16'=>array('len'=>0,'pname'=>'page15-2-15-16','next'=>'page15-2-15-17','parent'=>'page15-2-15-15','step'=>75),//Tell us about the expenses associated with your vehicle
            'page15-2-15-17'=>array('len'=>0,'pname'=>'page15-2-15-17','next'=>'page15-2-15-18','parent'=>'page15-2-15-16','step'=>76),//If you had any of the following work-related expenses, please let us know
            'page15-2-15-18'=>array('len'=>0,'pname'=>'page15-2-15-18','next'=>'page15-2-15-19','parent'=>'page15-2-15-17','step'=>77),//Tell us about any other expenses you had related to the work you did
            'page15-2-15-19'=>array('len'=>0,'pname'=>'page15-2-15-19','next'=>'page15-2-15-20','parent'=>'page15-2-15-18','step'=>78),//Did you buy any depreciable assets related to your work or business, other than vehicles?
            'page15-2-15-20'=>array('len'=>0,'pname'=>'page15-2-15-20','next'=>'page15-2-15-21','parent'=>'page15-2-15-19','step'=>79),//Upload a spreadsheet listing the assets you purchased for the business
            'page15-2-15-21'=>array('len'=>0,'pname'=>'page15-2-15-21','next'=>'page15-2-15-22','parent'=>'page15-2-15-20','step'=>80),//Did you sell or dispose of any depreciable assets related to the business?
            'page15-2-15-22'=>array('len'=>0,'pname'=>'page15-2-15-22','next'=>'page15-2-15-23','parent'=>'page15-2-15-21','step'=>81),//Upload a spreadsheet listing the assets you sold or disposed of for the business
            
            'page15-2-15-23'=>array('len'=>0,'pname'=>'page15-2-15-23','next'=>'field_ary_next','parent'=>'page15-2-15-21','step'=>82),//Is there anything else you'd like to tell us about income you, your spouse, or dependents received in 2023?
            'page15-2-15-24'=>array('len'=>0,'pname'=>'page15-2-15-24','next'=>'page15-2-15-25','parent'=>'page15-2-15-23','step'=>83),//Use the space below to elaborate on any sources of income you received
            'page15-2-15-25'=>array('len'=>0,'pname'=>'page15-2-15-25','next'=>'field_ary_next','parent'=>'page15-2-15-24','step'=>84),//Attach supporting documents if relevant
            //Which of the following did you receive from your brokerage accounts?
            'page15-3-1'=>array('len'=>0,'pname'=>'page15-3-1','next'=>'','parent'=>'page15-3','step'=>14),//Upload the consolidated 1099's you received
            'page15-3-2'=>array('len'=>0,'pname'=>'page15-3-2','next'=>'','parent'=>'','step'=>15),//Upload the 1099-B's you received
            'page15-3-3'=>array('len'=>0,'pname'=>'page15-3-3','next'=>'page15-3-4','parent'=>'','step'=>15),//Upload any other brokerage statements you received
            'page15-3-4'=>array('len'=>0,'pname'=>'page15-3-4','next'=>'page15-3-5','parent'=>'page15-3','step'=>15),//Did you use any savings bonds for higher education?
            
            'page15-3-5'=>array('len'=>0,'pname'=>'page15-3-5','next'=>'','parent'=>'page15-3-4','step'=>16),//Is there anything else you'd like to tell us about income you, your spouse, or dependents received in 2023?
            'page15-3-6'=>array('len'=>0,'pname'=>'page15-3-6','next'=>'page15-3-7','parent'=>'page15-3-5','step'=>17),//Use the space below to elaborate on any sources of income you received
            'page15-3-7'=>array('len'=>0,'pname'=>'page15-3-7','next'=>'field_ary_next','parent'=>'page15-3-6','step'=>18),//Attach supporting documents if relevant
            
            //Now let's go over the interest income you earned 
            'page15-4-1'=>array('len'=>0,'pname'=>'page15-4-1','next'=>'field_ary_next','parent'=>'page15-4','step'=>19),//Is there anything else you'd like to tell us about income you, your spouse, or dependents received in 2023?
            'page15-4-2'=>array('len'=>0,'pname'=>'page15-4-2','next'=>'page15-4-3','parent'=>'page15-4-1','step'=>20),//Use the space below to elaborate on any sources of income you received
            'page15-4-3'=>array('len'=>0,'pname'=>'page15-4-3','next'=>'field_ary_next','parent'=>'page15-4-2','step'=>21),//Attach supporting documents if relevant
            
            //Now let's go over dividends you received
            'page15-5-1'=>array('len'=>0,'pname'=>'page15-5-1','next'=>'field_ary_next','parent'=>'page15-5','step'=>22),//Is there anything else you'd like to tell us about income you, your spouse, or dependents received in 2023?
            'page15-5-2'=>array('len'=>0,'pname'=>'page15-5-2','next'=>'page15-5-3','parent'=>'page15-5-1','step'=>23),//Use the space below to elaborate on any sources of income you received
            'page15-5-3'=>array('len'=>0,'pname'=>'page15-5-3','next'=>'field_ary_next','parent'=>'page15-5-2','step'=>24),//Attach supporting documents if relevant
            
            //Now we'll ask for the documents relating to the real estate you sold
            'page15-6-0'=>array('len'=>0,'pname'=>'page15-6-0','next'=>'page15-6-1','parent'=>'page15-6','step'=>23),//Upload the final closing statement for each sale
            'page15-6-1'=>array('len'=>0,'pname'=>'page15-6-1','next'=>'field_ary_next','parent'=>'page15-6-0','step'=>24),//Is there anything else you'd like to tell us about income you, your spouse, or dependents received in 2023?
            'page15-6-2'=>array('len'=>0,'pname'=>'page15-6-2','next'=>'page15-6-3','parent'=>'page15-6-1','step'=>25),//Use the space below to elaborate on any sources of income you received
            'page15-6-3'=>array('len'=>0,'pname'=>'page15-6-3','next'=>'field_ary_next','parent'=>'page15-6-2','step'=>26),//Attach supporting documents if relevant
            //Now let's go over the Incentive Stock Options you exercised
            'page15-7-1'=>array('len'=>0,'pname'=>'page15-7-1','next'=>'field_ary_next','parent'=>'page15-7','step'=>22),//Is there anything else you'd like to tell us about income you, your spouse, or dependents received in 2023?
            'page15-7-2'=>array('len'=>0,'pname'=>'page15-7-2','next'=>'page15-7-3','parent'=>'page15-7-1','step'=>23),//Use the space below to elaborate on any sources of income you received
            'page15-7-3'=>array('len'=>0,'pname'=>'page15-7-3','next'=>'field_ary_next','parent'=>'page15-7-2','step'=>24),//Attach supporting documents if relevant
            
            'page15-8-1'=>array('len'=>0,'pname'=>'page15-8-1','next'=>'page15-8-7','parent'=>'page15-8','step'=>25),//Tell us about your foreign accounts
            'page15-8-2'=>array('len'=>0,'pname'=>'page15-8-2','next'=>'','parent'=>'page15-8-1','step'=>26),//Tell us about your second foreign account
            'page15-8-3'=>array('len'=>0,'pname'=>'page15-8-3','next'=>'','parent'=>'page15-8-2','step'=>27),//Tell us about your third foreign account
            'page15-8-4'=>array('len'=>0,'pname'=>'page15-8-4','next'=>'','parent'=>'page15-8-3','step'=>28),//Tell us about your fourth foreign account
            'page15-8-5'=>array('len'=>0,'pname'=>'page15-8-5','next'=>'page15-8-6','parent'=>'page15-8-4','step'=>29),//Tell us about your fifth foreign account
            'page15-8-6'=>array('len'=>0,'pname'=>'page15-8-6','next'=>'page15-8-7','parent'=>'page15-8-5','step'=>30),//Tell us about your other foreign accounts by uploading a spreadsheet
            //后面通用页面
            'page15-8-7'=>array('len'=>0,'pname'=>'page15-8-7','next'=>'field_ary_next','parent'=>'page15-8-4','step'=>31),//Is there anything else you'd like to tell us about income you, your spouse, or dependents received in 2023?
            'page15-8-8'=>array('len'=>0,'pname'=>'page15-8-8','next'=>'page15-8-9','parent'=>'page15-8-7','step'=>32),//Use the space below to elaborate on any sources of income you received
            'page15-8-9'=>array('len'=>0,'pname'=>'page15-8-9','next'=>'field_ary_next','parent'=>'page15-8-8','step'=>33),//Attach supporting documents if relevant
            
            //Now we'll ask about your foreign accounts
            'page15-9-1'=>array('len'=>0,'pname'=>'page15-9-1','next'=>'field_ary_next','parent'=>'page15-9','step'=>34),//Is there anything else you'd like to tell us about income you, your spouse, or dependents received in 2023?
            'page15-9-2'=>array('len'=>0,'pname'=>'page15-9-2','next'=>'page15-9-3','parent'=>'page15-9-1','step'=>35),//Use the space below to elaborate on any sources of income you received
            'page15-9-3'=>array('len'=>0,'pname'=>'page15-9-3','next'=>'field_ary_next','parent'=>'page15-9-2','step'=>36),//Attach supporting documents if relevant
            
            //Now we'll ask about your foreign accounts
            'page15-10-1'=>array('len'=>0,'pname'=>'page15-10-1','next'=>'field_ary_next','parent'=>'page15-10','step'=>34),//Is there anything else you'd like to tell us about income you, your spouse, or dependents received in 2023?
            'page15-10-2'=>array('len'=>0,'pname'=>'page15-10-2','next'=>'page15-10-3','parent'=>'page15-10-1','step'=>35),//Use the space below to elaborate on any sources of income you received
            'page15-10-3'=>array('len'=>0,'pname'=>'page15-10-3','next'=>'field_ary_next','parent'=>'page15-10-2','step'=>36),//Attach supporting documents if relevant
            
            //Now let's go over your IRA, 401(k), and other pension plan withdrawals
            'page15-11-1'=>array('len'=>0,'pname'=>'page15-11-1','next'=>'page15-11-3','parent'=>'page15-11','step'=>34),//Is there anything else you'd like to tell us about income you, your spouse, or dependents received in 2023?
            'page15-11-2'=>array('len'=>0,'pname'=>'page15-11-2','next'=>'page15-11-3','parent'=>'page15-11-1','step'=>35),//Use the space below to elaborate on any sources of income you received
            'page15-11-3'=>array('len'=>0,'pname'=>'page15-11-3','next'=>'page15-11-4','parent'=>'page15-11-2','step'=>36),//Attach supporting documents if relevant
             //Now let's go over your IRA, 401(k), and other pension plan withdrawals
            'page15-11-4'=>array('len'=>0,'pname'=>'page15-11-4','next'=>'field_ary_next','parent'=>'page15-11-3','step'=>34),//Is there anything else you'd like to tell us about income you, your spouse, or dependents received in 2023?
            'page15-11-5'=>array('len'=>0,'pname'=>'page15-11-5','next'=>'page15-11-6','parent'=>'page15-11-4','step'=>35),//Use the space below to elaborate on any sources of income you received
            'page15-11-6'=>array('len'=>0,'pname'=>'page15-11-6','next'=>'field_ary_next','parent'=>'page15-11-5','step'=>36),//Attach supporting documents if relevant
            
            //Now let's go over your Social Security benefits
            'page15-12-1'=>array('len'=>0,'pname'=>'page15-12-1','next'=>'field_ary_next','parent'=>'page15-12','step'=>34),//Is there anything else you'd like to tell us about income you, your spouse, or dependents received in 2023?
            'page15-12-2'=>array('len'=>0,'pname'=>'page15-12-2','next'=>'page15-12-3','parent'=>'page15-12-1','step'=>35),//Use the space below to elaborate on any sources of income you received
            'page15-12-3'=>array('len'=>0,'pname'=>'page15-12-3','next'=>'field_ary_next','parent'=>'page15-12-2','step'=>36),//Attach supporting documents if relevant
            
            
            //Now let's go over unemployment benefits or government payments you received
            'page15-15-1'=>array('len'=>0,'pname'=>'page15-15-1','next'=>'field_ary_next','parent'=>'page15-15','step'=>34),//Is there anything else you'd like to tell us about income you, your spouse, or dependents received in 2023?
            'page15-15-2'=>array('len'=>0,'pname'=>'page15-15-2','next'=>'page15-15-3','parent'=>'page15-15-1','step'=>35),//Use the space below to elaborate on any sources of income you received
            'page15-15-3'=>array('len'=>0,'pname'=>'page15-15-3','next'=>'field_ary_next','parent'=>'page15-15-2','step'=>36),//Attach supporting documents if relevant
            
            //Now we'll ask about the K-1's you received
            'page15-16-1'=>array('len'=>0,'pname'=>'page15-16-1','next'=>'field_ary_next','parent'=>'page15-16','step'=>34),//Is there anything else you'd like to tell us about income you, your spouse, or dependents received in 2023?
            'page15-16-2'=>array('len'=>0,'pname'=>'page15-16-2','next'=>'page15-16-3','parent'=>'page15-16-1','step'=>35),//Use the space below to elaborate on any sources of income you received
            'page15-16-3'=>array('len'=>0,'pname'=>'page15-16-3','next'=>'field_ary_next','parent'=>'page15-16-2','step'=>36),//Attach supporting documents if relevant
            
            //Now let's go over withdrawals you made from your Health Savings Account or Medical Savings Account
            'page15-17-0'=>array('len'=>0,'pname'=>'page15-17-0','next'=>'page15-17-1','parent'=>'page15-17','step'=>34),//Were the distributions spent on medical expenses only?
            'page15-17-1'=>array('len'=>0,'pname'=>'page15-17-1','next'=>'field_ary_next','parent'=>'page15-17-0','step'=>34),//Is there anything else you'd like to tell us about income you, your spouse, or dependents received in 2023?
            'page15-17-2'=>array('len'=>0,'pname'=>'page15-17-2','next'=>'page15-17-3','parent'=>'page15-17-1','step'=>35),//Use the space below to elaborate on any sources of income you received
            'page15-17-3'=>array('len'=>0,'pname'=>'page15-17-3','next'=>'field_ary_next','parent'=>'page15-17-2','step'=>36),//Attach supporting documents if relevant
            
            //Now let's go over your gambling winnings
            'page15-18-1'=>array('len'=>0,'pname'=>'page15-18-1','next'=>'page15-18-3','parent'=>'page15-18','step'=>34),//Did you have any winnings not reported on a W-2G?
            'page15-18-2'=>array('len'=>0,'pname'=>'page15-18-2','next'=>'page15-18-3','parent'=>'page15-18-1','step'=>34),//What is the amount of winnings you had not reported on a W-2G?
            'page15-18-3'=>array('len'=>0,'pname'=>'page15-18-3','next'=>'page15-18-4','parent'=>'page15-18-2','step'=>34),//Did you have any gambling losses?
            'page15-18-4'=>array('len'=>0,'pname'=>'page15-18-4','next'=>'field_ary_next','parent'=>'page15-18-3','step'=>34),//Is there anything else you'd like to tell us about income you, your spouse, or dependents received in 2023?
            'page15-18-5'=>array('len'=>0,'pname'=>'page15-18-5','next'=>'page15-18-6','parent'=>'page15-18-4','step'=>35),//Use the space below to elaborate on any sources of income you received
            'page15-18-6'=>array('len'=>0,'pname'=>'page15-18-6','next'=>'field_ary_next','parent'=>'page15-18-5','step'=>36),//Attach supporting documents if relevant
            
            
             //Now let's go over the alimony or spousal support you received
            'page15-19-1'=>array('len'=>0,'pname'=>'page15-19-1','next'=>'field_ary_next','parent'=>'page15-19','step'=>34),//Is there anything else you'd like to tell us about income you, your spouse, or dependents received in 2023?
            'page15-19-2'=>array('len'=>0,'pname'=>'page15-19-2','next'=>'page15-19-3','parent'=>'page15-19-1','step'=>35),//Use the space below to elaborate on any sources of income you received
            'page15-19-3'=>array('len'=>0,'pname'=>'page15-19-3','next'=>'field_ary_next','parent'=>'page15-19-2','step'=>36),//Attach supporting documents if relevant
            
             //Now let's go over your jury duty earnings
            'page15-20-1'=>array('len'=>0,'pname'=>'page15-20-1','next'=>'field_ary_next','parent'=>'page15-20','step'=>34),//Is there anything else you'd like to tell us about income you, your spouse, or dependents received in 2023?
            'page15-20-2'=>array('len'=>0,'pname'=>'page15-20-2','next'=>'page15-20-3','parent'=>'page15-20-1','step'=>35),//Use the space below to elaborate on any sources of income you received
            'page15-20-3'=>array('len'=>0,'pname'=>'page15-20-3','next'=>'field_ary_next','parent'=>'page15-20-2','step'=>36),//Attach supporting documents if relevant
            
            //Now let's go over the sale of your main home
            'page15-21-1'=>array('len'=>0,'pname'=>'page15-21-1','next'=>'page15-21-2','parent'=>'page15-21','step'=>34),//Upload your final closing or settlement statement from the sale of your home
            'page15-21-2'=>array('len'=>0,'pname'=>'page15-21-2','next'=>'page15-21-4','parent'=>'page15-21-1','step'=>34),//Did you have any selling costs that are not on your closing statement?
            'page15-21-3'=>array('len'=>0,'pname'=>'page15-21-3','next'=>'page15-21-4','parent'=>'page15-21-2','step'=>34),//Tell us the amount of selling expenses not on your closing statement
            'page15-21-4'=>array('len'=>0,'pname'=>'page15-21-4','next'=>'page15-21-6','parent'=>'page15-21-3','step'=>34),//Did you make repairs or improvements to make your home more marketable before selling it?
            'page15-21-5'=>array('len'=>0,'pname'=>'page15-21-5','next'=>'page15-21-6','parent'=>'page15-21-4','step'=>34),//How much did you spend in home improvements and repairs?
            'page15-21-6'=>array('len'=>0,'pname'=>'page15-21-6','next'=>'page15-21-7','parent'=>'page15-21-4','step'=>34),//Tell us about the original purchase of your home
            'page15-21-7'=>array('len'=>0,'pname'=>'page15-21-7','next'=>'field_ary_next','parent'=>'page15-21-6','step'=>34),//Is there anything else you'd like to tell us about income you, your spouse, or dependents received in 2023?
            'page15-21-8'=>array('len'=>0,'pname'=>'page15-21-8','next'=>'page15-21-9','parent'=>'page15-21-7','step'=>35),//Use the space below to elaborate on any sources of income you received
            'page15-21-9'=>array('len'=>0,'pname'=>'page15-21-9','next'=>'field_ary_next','parent'=>'page15-21-8','step'=>36),//Attach supporting documents if relevant
            
            
            //Now let's go over your home foreclosure and debt cancellation
            'page15-22-0'=>array('len'=>0,'pname'=>'page15-22-0','next'=>'page15-22-1','parent'=>'page15-22','step'=>34),//Upload any 1099-A's you received
            'page15-22-0-1'=>array('len'=>0,'pname'=>'page15-22-0-1','next'=>'page15-22-1','parent'=>'page15-22','step'=>34),//Upload your 1099-C
            'page15-22-1'=>array('len'=>0,'pname'=>'page15-22-1','next'=>'field_ary_next','parent'=>'page15-22','step'=>34),//Is there anything else you'd like to tell us about income you, your spouse, or dependents received in 2023?
            'page15-22-2'=>array('len'=>0,'pname'=>'page15-22-2','next'=>'page15-22-3','parent'=>'page15-22-1','step'=>35),//Use the space below to elaborate on any sources of income you received
            'page15-22-3'=>array('len'=>0,'pname'=>'page15-22-3','next'=>'field_ary_next','parent'=>'page15-22-2','step'=>36),//Attach supporting documents if relevant
            //Now we'll go over your 529 Plan and Coverdell ESA withdrawals
            'page15-23-0'=>array('len'=>0,'pname'=>'page15-23-0','next'=>'page15-23-1','parent'=>'page15-23','step'=>34),//Upload any 1099-A's you received
            'page15-23-1'=>array('len'=>0,'pname'=>'page15-23-1','next'=>'field_ary_next','parent'=>'page15-23','step'=>34),//Is there anything else you'd like to tell us about income you, your spouse, or dependents received in 2023?
            'page15-23-2'=>array('len'=>0,'pname'=>'page15-23-2','next'=>'page15-23-3','parent'=>'page15-23-1','step'=>35),//Use the space below to elaborate on any sources of income you received
            'page15-23-3'=>array('len'=>0,'pname'=>'page15-23-3','next'=>'field_ary_next','parent'=>'page15-23-2','step'=>36),//Attach supporting documents if relevant
            
            
            'page15-24-1'=>array('len'=>0,'pname'=>'page15-24-1','next'=>'page15-24-2','parent'=>'page15-24','step'=>14),//Use the space below to elaborate on any sources of income you received
            'page15-24-2'=>array('len'=>0,'pname'=>'page15-24-2','next'=>'field_ary_next','parent'=>'page15-24-1','step'=>14),//Attach supporting documents if relevant
            
            'page16'=>array('len'=>0,'pname'=>'page16','next'=>'','parent'=>'','step'=>85),//way to go
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
            if(isset($page_ary[$data['page']])){
                $curpage=$page_ary[$data['page']];
            }else{
                $curpage=$page_ary['submission'];
            }
         
                $save['cpage']=$data['page'];
                $save['updatetime']=time();
                if($res){
                   $rr=DB::name('users_clients')->where($map)->save($save);
                }
            print_r($data);
       }else{
            if($res['cpage']){
                $curpage=$page_ary[$res['cpage']];
            }else{
                $curpage=$page_ary['submission'];
            } 
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
                        if($curpage['len']==0){
                            switch($res['dependents_count']){
                                case 0:$lastpage="page6";break;
                                case 1:$lastpage="page7";break;
                                case 2:$lastpage="page8";break;
                                case 3:$lastpage="page9";break;
                                case 4:$lastpage="page10";break;
                            }
                        }
                                         
                        if($curpage['len']==1){
                            $lastpage="page6";
                        }
                        if($curpage['len']==2){
                           $lastpage="page7";
                        }
                        if($curpage['len']==3){
                           $lastpage="page8";
                        }
                        if($curpage['len']==4){
                            $lastpage="page9";
                        }
                        break;
                     case 'field_ary_pre': //Which of the following sources of income applied in 2023?
                        $new_field_ary=$this->field_ary;
                    
                        foreach($new_field_ary as $key=>$val){
                            if($res[$key]==1){
                                if(isset($data['finishpage'])){
                                    if($data['finishpage']==$key){
                                        $lastpage=$val['endpage'];
                                        break;
                                    }
                                }
                                if(isset($data['curfield'])){
                                    if($data['curfield']==$key){
                                        $tt=$val;
                                        $lastpage=$lastpage1;
                                        break;
                                    }
                                }
                                $lastpage1=$val['endpage'];
                            }
                        }
                        if(!$lastpage){
                              $lastpage='page12';
                        }
                        echo "--sdfsfsdf//";
                        echo $lastpage; echo "//sdfsfsdf--";
                        break; 
                    default:
                       $lastpage=$curpage['parent'];
                }
                switch($curpage['next']){
                    case 'basic_info_did_address_change':
                         $cchage=$res['basic_info_did_address_change'];
                        if($cchage==1){ //搬迁
                            $nextpage="page3-1";
                        }else{
                            $nextpage="page4"; //默认不搬迁
                        }  
                        break;
                    case 'dependents_count':
                        if($curpage['len']==0){
                            if($res['dependents_count']==0){
                              $nextpage="page11";//直接 完成
                            }
                            if($res['dependents_count']>=1){
                                $nextpage="page7";
                            }
                        }
                         // echo $nextpage;die();
                        if($curpage['len']==1){
                            if($res['dependents_count']>1){
                                $nextpage="page8";
                            }else{
                                $nextpage="page11";
                            }
                        }
                        if($curpage['len']==2){
                            if($res['dependents_count']>2){
                                $nextpage="page9";
                            }else{
                                $nextpage="page11";
                            }
                        }
                        if($curpage['len']==3){
                            if($res['dependents_count']>3){
                                 $nextpage="page10";
                            }else{
                                $nextpage="page11";
                            }
                        }
                        if($curpage['len']==4){
                           $nextpage="page11";
                        }
                        break;
                    case 'basic_info_marital_status':
                        if($res['basic_info_marital_status']=='2'){
                            $nextpage="page6-1";
                        }else{
                            $nextpage="page6";
                        }
                     break;
                    case 'field_ary': //Which of the following sources of income applied in 2023?
                        $new_field_ary=array_reverse($this->field_ary);
                        foreach($new_field_ary as $key=>$val){
                            if($res[$key]==1){
                                if(isset($data['finishpage'])){
                                    if($data['finishpage']==$key){ break;}
                                }else{
                                    if($key==$data['curfield']){
                                        $nextpage=$val['next']; 
                                        $tt2=$val;
                                        View::assign('field_content', $val);
                                        break;
                                    }
                                }
                                $nextpage=$val['next']; 
                                $tt2=$val;
                                View::assign('field_content', $val);
                            }
                        }
                        if(!$nextpage){
                            $nextpage="page16";
                        }
                        break;
                    case 'field_ary_next': //Which of the following sources of income applied in 2023?
                        $new_field_ary=$this->field_ary;
                        foreach($new_field_ary as $key=>$val){
                         //  echo $key."--".$nn."<br>";
                            if($val['endpage']==$curpage['pname']||$val['endpage2']==$curpage['pname']){  $nn='';continue; }
                            if($res[$key]==1){
                                $nn='page13';
                              //  echo $key;
                                break;
                            }
                         //  echo $key."--".$nn."<br>";
                        }
                       // echo "aa--".$nn."<br>";
                        if(!$nn){
                            $nextpage="page15-10-1";
                            //$nextpage="page16";
                        }else{
                            $nextpage=$nn;
                        }
                        echo $nextpage;
                        echo "--end";
                        break;
                    default:
                        $nextpage=$curpage['next'];
                }
    
            $percent=$curpage['step'];
            $pagename=$curpage['pname'];
            if(isset($tt)&&$tt!=''){
               View::assign('field_content', $tt); 
            }
         
             echo $pagename;
            View::assign('pagename', $pagename);
                View::assign('percent', $percent);
                View::assign('lastpage', $lastpage);
                View::assign('nextpage', $nextpage);
            
        return view($pagename);
       
    }
    public function getpagenext(){
        $data=input('post.');
        $cname=$data['pagename'];
        $new_field_ary=$this->field_ary;
        foreach($new_field_ary as $key=>$val){
             //  echo $key."--".$nn."<br>";
          if($val['endpage']==$cname||$val['endpage2']==$cname){  $nn='';continue; }
              if($res[$key]==1){
                     $nn='page13';
                     break;
                 }
           }
          if(!$nn){
               $nextpage="page16";
           }else{
               $nextpage=$nn;
           }
        return json(['code'=>1,'pagenext'=>$nextpage]); 
    }
    public function pageshow(){
        
    }
    public function pagesave_form(){
        if (IS_POST){
            $data=input('post.');
            if(!$data['fieldname']){
                return json(['code'=>0]);  
            }
            
            $save['cpage']=$data['pagename'];
            $save['last_field']=$data['fieldname'];
            $save['updatetime']=time();
            $save['status']=1;
            $map['link']=$data['link'];
            //$map['status']=1;
            $res=DB::name('users_clients')->where($map)->find();
            if($res){
                //查看当前数据，
                if($data['action']=="select"){
                    if($data['fieldvalue']=='none'){
                        $temp_ary[]='none';
                    }else{
                       if($res[$data['fieldname']]!=''&&$res[$data['fieldname']]!='null'){
                           $temp_ary=json_decode($res[$data['fieldname']],true);
                           array_push($temp_ary,$data['fieldvalue']);
                           foreach($temp_ary as $key=>$val){
                             if($val=='none'){
                                 unset($temp_ary[$key]);
                             } 
                           }
                       }else{
                           $temp_ary[]=$data['fieldvalue'];
                       }
                    }
                }
                if($data['action']=="cancel"){
                   if($res[$data['fieldname']]!=''&&$res[$data['fieldname']]!='null'){
                       $temp_ary=json_decode($res[$data['fieldname']],true);
                       foreach($temp_ary as $key=>$val){
                         if($val==$data['fieldvalue']){
                             unset($temp_ary[$key]);
                         } 
                       }
                   }else{
                       $temp_ary='';
                   } 
                }
             //   print_r($temp_ary);
               if($temp_ary){
                   $ary=$temp_ary;
                       $none=0;
                       $nec=0;
                       $misc=0;
                       $k=0;
                   if($data['fieldname']=='self_employment_0_type_of_income'||$data['fieldname']=='self_employment_1_type_of_income'){
                      
                   foreach($ary as $key=>$val){
                          switch($val){
                            case 'none':
                               $none=1;
                            break;
                            case '1099nec':
                               $nec=1;
                            break;
                            case '1099misc':
                               $misc=1; 
                            break;
                            case '1099k':
                               $k=1;
                            break;
                         }
                     }
                   }
                 if($data['fieldname']=='stocks_mutual_funds_bonds_which_documents'){//page15-3 Which of the following did you receive from your brokerage accounts?
                   foreach($ary as $key=>$val){
                          switch($val){
                            case 'none':
                               $none=1;
                            break;
                            case 'consolidated_1099':
                               $nec=1;
                            break;
                            case '1099_b':
                               $misc=1; 
                            break;
                            case 'brokerage_statement':
                               $k=1;
                            break;
                         }
                     }
                     if($none){
                        $pagenext="page15-3-4";
                        }else{
                            if($nec){
                                $pagenext="page15-3-1";
                            }else{
                                if($misc){
                                    $pagenext="page15-3-2";
                                }else{
                                   if($k){
                                      $pagenext="page15-3-3"; 
                                   }else{
                                      $pagenext="page15-3-4";  
                                   }
                                }
                            }
                        }
                   }
                   if($data['fieldname']=='self_employment_0_type_of_income'){
                      if($none){
                        $pagenext="page15-2-5";
                        }else{
                            if($nec){
                                $pagenext="page15-2-4-nec";
                            }else{
                                if($misc){
                                    $pagenext="page15-2-4-misc";
                                }else{
                                   if($k){
                                      $pagenext="page15-2-4-k"; 
                                   }else{
                                      $pagenext="page15-2-5";  
                                   }
                                }
                            }
                        }
                   } 
                   if($data['fieldname']=='self_employment_1_type_of_income'){
                      if($none){
                        $pagenext="page15-2-15-4";
                        }else{
                            if($nec){
                                $pagenext="page15-2-15-3-1-2-nec";
                            }else{
                                if($misc){
                                    $pagenext="page15-2-15-3-1-2-misc";
                                }else{
                                   if($k){
                                      $pagenext="page15-2-15-3-1-2-k"; 
                                   }else{
                                      $pagenext="page15-2-15-4";  
                                   }
                                }
                            }
                        }
                   }
                   
                  if($data['fieldname']=='home_foreclosure_debt_cancellation_did_confirm'){//page15-22
                   foreach($ary as $key=>$val){
                          switch($val){
                            case 'none':
                               $none=1;
                            break;
                            case '1099_a':
                               $nec=1;
                            break;
                            case '1099_c':
                               $misc=1; 
                            break;
                         }
                     }
                     if($none){
                        $pagenext="page15-3";
                        }else{
                            if($nec){
                                $pagenext="";
                            }else{
                                if($misc){
                                    $pagenext="";
                                }else{
                                   $pagenext=""; 
                                }
                            }
                        }
                   } 
                   
                  if($data['fieldname']=='home_foreclosure_debt_cancellation_which_documents'){//page15-22-Which of the following forms have you received?
                   foreach($ary as $key=>$val){
                          switch($val){
                            case 'none':
                               $none=1;
                            break;
                            case '1099_a':
                               $nec=1;
                            break;
                            case '1099_c':
                               $misc=1; 
                            break;
                         }
                     }
                     if($none){
                        $pagenext="page15-22-1";
                        }else{
                            if($nec){
                                $pagenext="page15-22-0";
                            }else{
                                if($misc){
                                    $pagenext="page15-22-0-1";
                                }else{
                                    $pagenext="page15-22-1";
                                }
                            }
                        }
                   }  
                   $save[$data['fieldname']]=json_encode($temp_ary);
               }else{
                   $save[$data['fieldname']]='';
                   if($data['fieldname']=='self_employment_0_type_of_income'){
                        $pagenext="page15-2-5";
                   }
                  if($data['fieldname']=='self_employment_1_type_of_income'){
                        $pagenext="page15-2-15-4";
                   } 
                  if($data['fieldname']=='stocks_mutual_funds_bonds_which_documents'){
                        $pagenext="page15-3-4";
                   } 
                  if($data['fieldname']=='home_foreclosure_debt_cancellation_did_confirm'){ //page15-22
                        $pagenext="page15-22-1";
                   }  
               }
               $rr=DB::name('users_clients')->where($map)->save($save);
                //   echo DB::name('users_clients')->getLastSql();die();
               $customFormat = 'g:i A'; //显示样式  Jan 11, 2024, 10:0 AM  F为英文月份全称 jS为6th第几天
               $savetime="Saved at ".date($customFormat,$save['updatetime']); //将当前时间按照自定义格式转换成字符串
               return json(['code'=>1,'savetime'=>$savetime,'pagenext'=>$pagenext]); 
            }else{
               return json(['code'=>0]);  
            }
        }
    }
    public function pagesave(){
        if (IS_POST){
            $data=input('post.');
            if(!$data['fieldname']){
                return json(['code'=>0]);  
            }
            $save[$data['fieldname']]=$data['fieldvalue'];
            $save['cpage']=$data['pagename'];
            $save['last_field']=$data['fieldname'];
            $save['updatetime']=time();
            $save['status']=1;
            $map['link']=$data['link'];
            //$map['status']=1;
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
