<?php
/**
*@作者:MissZhang
*@邮箱:<787727147@qq.com>
*@创建时间:2021/7/7 下午4:40
*@说明:首页控制器
*/
namespace app\clients\controller;

use think\facade\Db;
use think\facade\Filesystem;
use think\facade\View;

require_once root_path('vendor') . "PHPMailer/PHPMailer.php";
use PHPMailer\PHPMailer\PHPMailer;
require_once root_path('vendor') . "PHPMailer/SMTP.php";
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require_once root_path('vendor') . "Twilio/autoload.php";
use Twilio\Rest\Client;
use Twilio\Exceptions\RestException;
use DateTime;

class Eapi extends Base
{
    protected function initialize()
    {
        parent::initialize();
    }   
	/*preview*/
    public function preclient(){
		
        if (IS_POST) {
		   $data = json_decode(file_get_contents('php://input'), true);
		    if(!$data['plan']){
			  $response=array('code'=>0,'msg'=>'Please select return type');			
			  echo json_encode($response);exit();
			}else{
				$plan=$data['plan'];
			}
		   if(!$data['caseid']){	
		     if($plan!="1040"&&$plan!="profitloss"&&$plan!="cm"&&$plan!="deductions"&&$plan!="insolvency"&&$plan!="dependent"&&$plan!="all"&&$plan!="rentalincome"){
		      	 $response=array('code'=>0,'msg'=>'No case id');	
		      	  echo json_encode($response);exit();		
		     }
			}
		   $ww2['link']='preview';
		   if($plan!="1040"&&$plan!="profitloss"&&$plan!="cm"&&$plan!="deductions"&&$plan!="insolvency"&&$plan!="dependent"&&$plan!="all"&&$plan!="rentalincome"){
		      $ww2['plan']=$data['plan'];
		   }
		   $check2=DB::name('users_clients')->where($ww2)->find();
		 
		   if($check2){
             // if($plan!="1040"&&$plan!="profitloss"&&$plan!="cm"&&$plan!="deductions"&&$plan!="insolvency"&&$plan!="dependent"&&$plan!="all"&&$plan!="rentalincome"){
             // 	$link=$this->getLink($data['plan'],'preview',$data['caseid']);
             // }else{
             	$link=$this->getLink($data['plan'],'preview',$data['caseid']);
            			 
			 $response = array('code'=>1,'msg'=>'1PreviewLink for you','link'=>$link);
			 echo json_encode($response);exit();
		   }else{
			   if($plan!="1040"&&$plan!="profitloss"&&$plan!="cm"&&$plan!="deductions"&&$plan!="insolvency"&&$plan!="dependent"&&$plan!="all"&&$plan!="rentalincome"){
				    $link=$this->getLink($data['plan'],'preview',$data['caseid']);
					$response = array('code'=>1,'msg'=>'2PreviewLink for you','link'=>$link);
					echo json_encode($response);exit();
			   }else{
				 $response = array('code'=>0,'msg'=>'No preview for you');  
				 echo json_encode($response);exit();
			   }
		    	
		   }	
        }else{
			echo "Forbidden!!!!!!";
		}
    }
	public function gettype(){
		if (IS_POST) {
         
		   $typelist1='[
			  {
				"name": "Phase 1: Primary Only",
				"keyval": "el1s",
				"sort": 999,
				"type": 1
			  },
			  {
				"name": "Phase 1: Primary – CC EDIT",
				"keyval": "ael1fs",
				"sort": 999,
				"type": 1
			  },
			  {
				"name": "Phase 1: Primary – No Refund Clause",
				"keyval": "el1srn",
				"sort": 990,
				"type": 1
			  },
			  {
				"name": "Phase 1: PRIMARY IRS FORMS ONLY",
				"keyval": "el1ss",
				"sort": 980,
				"type": 1
			  },
			  {
				"name": "Phase 1: SPOUSE ONLY",
				"keyval": "el1m",
				"sort": 970,
				"type": 1
			  },
			  {
				"name": "Phase 1: Primary and Business",
				"keyval": "el1b",
				"sort": 960,
				"type": 1
			  },
			  {
				"name": "Phase 2: PRIMARY",
				"keyval": "el2",
				"sort": 950,
				"type": 1
			  },
			  {
				"name": "EXTRAS: CC AUTH",
				"keyval": "ccx",
				"sort": 930,
				"type": 1
			  },
			  {
				"name": "EXTRAS: CC AUTH (BLANK CCN)",
				"keyval": "ccx2",
				"sort": 0,
				"type": 1
			  },
			  {
				"name": "EXTRAS: One Time Payment Auth",
				"keyval": "otp",
				"sort": 0,
				"type": 1
			  },
			  {
				"name": "Extras: Tax Prep OPT OUT",
				"keyval": "xtp",
				"sort": 0,
				"type": 1
			  },
			  {
				"name": "EXTRAS: Tax Prep Waiver (Signature. Spouse initial)",
				"keyval": "wai",
				"sort": 0,
				"type": 1
			  },
			  {
				"name": "TAX PREP: Tax Prep Agreement W/CC Auth",
				"keyval": "tpc",
				"sort": 0,
				"type": 1
			  },
			  {
				"name": "TAX PREP: Form 8879",
				"keyval": "8879",
				"sort": 0,
				"type": 1
			  },
			  {
				"name": "TAX PREP: Form 8879 Spouse",
				"keyval": "8879sp",
				"sort": 0,
				"type": 1
			  }
			]';
		   $typelist1=json_decode($typelist1,true);
		   $map['status']=0;
		   $typelist=DB::name('contract')->where($map)->field('id,name,keyval,sort')->order('sort desc,id desc')->select();
	
		   $typelist=array_merge($typelist1,$typelist->toArray());
		   $response = array('code'=>1,'ctype'=>$typelist);		   
		   echo json_encode($response);exit();
		}
	}
	
	public function acapi(){
		/*header("Access-Control-Allow-Origin: https://ntcg.irslogics.com");
		header("Access-Control-Allow-Methods: POST");
		header("Access-Control-Allow-Headers: Content-Type");*/
		
        if (IS_GET) {
          // $data=input('post.');
		  //$data = json_decode(file_get_contents('php://input'), true);
		   $data = input('get.');

           $data['reg_time']=time();         
           $data['user_id']=0;//from crm 
		
		   if(!$data['plan']){				
			 $response=array('code'=>0,'msg'=>'Please select return type');			
			  echo json_encode($response);exit();
			}
		   if(!$data['taxyear']){				
				$response=array('code'=>0,'msg'=>'Please select year');			
			    echo json_encode($response);exit();
			}
		   /*
		   $pattern = '/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/';
			if (!preg_match($pattern,$data['email'])) {				 
				$response=array('code'=>0,'msg'=>'Invalid email');				
			    echo json_encode($response);exit();
			}*/
			if(!$data['caseid']){
			   $response=array('code'=>0,'msg'=>'No Caseid detected');				
			   echo json_encode($response);exit();
			}
		   
		 //  print_r($check2);exit();
		    $url='https://ntcg.irslogics.com/publicapi/cases/casefile?CaseID='.$data['caseid'].'&details=ServiceDetail/Campaign&apikey=f6bf9800223b44a5ac6948ee6556225d';
					//echo $url;
			$infoff=curlget($url);
			$ary2=json_decode($infoff,true);
			$ary=json_decode($ary2['data'],true);
			//print_r($ary);exit();
			if($ary['Email']){
			   $ww2['caseid']=$data['caseid'];
			   $ww2['taxyear']=$data['taxyear'];
			   $ww2['plan']=$data['plan'];
			  // $ww2['email']=$ary['Email'];
			   $check2=DB::name('users_clients')->where($ww2)->field('link,plan,taxyear,caseid,email,firstname')->find();
			}else{
				$response = array('code' => 0,'msg'=>'Email is lost from CRM api');					
				echo json_encode($response);exit();		
			}		

		   if($check2){
			  //查看是否同一个邮箱，不是的话进行更新
			  if($ary['Email']!=$check2['email']){
			  	$maa['id']=$check2['id'];
			  	$maadata['email']=$ary['Email'];
			  	DB::name('users_clients')->where($maa)->save($maadata);//更新邮箱
			  }
			   // send email directly
			//  $this->crmEmailSend($check2['id']);
			  // return json(['code'=>1,'msg'=>'Client exists,contract is sent directly']);  
			 $response=array('code'=>1,'msg'=>'Client exists,contract is sent directly');
			 $link=$this->getLink($ww2['plan'],$check2['link'],$check2['caseid']);
			 //$link="http://".$_SERVER['HTTP_HOST'].'/clients/folders/'.$check2['link'];
			 /*
			 $pre_url="https://ntcg.irslogics.com/publicapi/cases/activity";			 
			 $pre_postdata="{\r\n             \t\"CaseID\":\"".$data['caseid']."\",\r\n             \t\"ActivityType\":\"General\",\r\n             \t\"Subject\":\"TaxRepublic\",\r\n             \t\"Comment\":\" Email Sent with link:  <a target='_blank' href='".$link."'>".$link."</a> Tax documents to finalize"." \"\r\n}\r\n";
			 $pre_header=array("Content-Type: application/json","Authorization: f6bf9800223b44a5ac6948ee6556225d");
			 $this->curlRequest($pre_url,$pre_postdata,$pre_header);*/
             // return json(['code'=>1,'msg'=>'Contract was successfully sent']); 
			 // $response = array('code'=>1,'msg'=>'Contract was successfully sent','link'=>$link);
			  header("Location:".$link); exit();
		   }else{
			        
					//print_r($ary);exit();
					if($ary['Email']){
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
						   $data['email']=$ary['Email'];
						   $data['firstname']=$ary['FirstName'];
						   $data['lastname']=$ary['LastName'];
						   $res=Db::name('users_clients')->insertGetId($data);
						 
						  // echo Db::name('users_clients')->getLastSql();
						   if($res){
							  //如果是profit loss  将在profit loss表中创建
							  if($data['plan']=='profitloss'){
								  $profit['user_client_id']=$res;
								  $profit['updatetime']=time();
								  Db::name('user_profit_loss')->insertGetId($profit);
							  }
							  if($data['plan']=='cm'){
								 $insert['user_client_id']=$res;
								 $insert['updatetime']=time();
								 $pid=DB::name('user_financial')->insertGetId($insert);
							  }
							  if($data['plan']=='deductions'){
								 $insert['user_client_id']=$res;
								 $insert['updatetime']=time();
								 $pid=DB::name('user_deductions')->insertGetId($insert);
							  }
							  if($data['plan']=='insolvency'){
								 $insert['user_client_id']=$res;
								 $insert['updatetime']=time();
								 $pid=DB::name('user_insolvency')->insertGetId($insert);
							  }
							  if($data['plan']=='dependent'){
								 $insert['user_client_id']=$res;
								 $insert['updatetime']=time();
								 $pid=DB::name('user_dependent')->insertGetId($insert);
							  }
							  if($data['plan']=='all'){
								 $insert['user_client_id']=$res;
								 $insert['updatetime']=time();
								 $pid=DB::name('user_all')->insertGetId($insert);
							  }
							  if($data['plan']=='rentalincome'){
								 $insert['user_client_id']=$res;
								 $insert['updatetime']=time();
								 $pid=DB::name('user_rentalincome')->insertGetId($insert);
							  }
							  // successfully created,and then send email
							//  $this->crmEmailSend($res);
							$plan=$data['plan'];
							if($plan!="1040"&&$plan!="profitloss"&&$plan!="cm"&&$plan!="deductions"&&$plan!="insolvency"&&$plan!="dependent"&&$plan!="all"&&$plan!="rentalincome"){
								    
							}
							$link=$this->getLink($data['plan'],$data['link'],$data['caseid']);
							  /*
							  //create activity to CRM
							  $link="http://".$_SERVER['HTTP_HOST'].'/clients/folders/'.$data['link'];
							 $pre_url="https://ntcg.irslogics.com/publicapi/cases/activity";
							 
							 $pre_postdata="{\r\n             \t\"CaseID\":\"".$data['caseid']."\",\r\n             \t\"ActivityType\":\"General\",\r\n             \t\"Subject\":\"TaxRepublic\",\r\n             \t\"Comment\":\" Email Sent with link:  <a target='_blank' href='".$link."'>".$link."</a> Tax documents to finalize"." \"\r\n}\r\n";
							 $pre_header=array("Content-Type: application/json","Authorization: f6bf9800223b44a5ac6948ee6556225d");
							 $this->curlRequest($pre_url,$pre_postdata,$pre_header);*/
							 // return json(['code'=>1,'msg'=>'Contract was successfully sent']); 
							$response = array('code'=>1,'msg'=>'Contract was created successfully','link'=>$link);
							//  echo "7";exit();
							header("Location:".$link); exit();
							// echo json_encode($response); exit();
						   }else{
							  //return json(['code'=>0,'msg'=>'Fail to send']);  
							  $response = array('code' => 0,'msg'=>'Failed to send');
							  //  echo "8";exit();
							 echo json_encode($response);exit();
						   }  
					}else{
						$response = array('code' => 0,'msg'=>'Email is lost from CRM api');					
					    echo json_encode($response);exit();						
					}					  
		   }
		
           
        }else{
			echo "Forbidden!!!!!!";
		}
    }
   public function getPlanName($plan){
         $map['keyval']=$plan;
         $clientres=DB::name('contract')->where($map)->find();
         if($clientres){
         	return $clientres['name'];
         }else{
         	return '';
         }
   }
   public function getLink($plan,$link,$caseid=''){
	         if($plan=="1040"){
				 $rel="https://".$_SERVER['HTTP_HOST'].'/clients/folders/'.$link;
			 }else{
				if($plan=="profitloss"){
				 $rel="https://".$_SERVER['HTTP_HOST'].'/clients/files/'.$link;
			    } 
				if($plan=="cm"){
				 $rel="https://".$_SERVER['HTTP_HOST'].'/clients/financial/'.$link;
			    }
				if($plan=="deductions"){
				 $rel="https://".$_SERVER['HTTP_HOST'].'/clients/deductions/'.$link;
			    }
				if($plan=="insolvency"){
				 $rel="https://".$_SERVER['HTTP_HOST'].'/clients/insolvency/'.$link;
			    }
				if($plan=="dependent"){
				 $rel="https://".$_SERVER['HTTP_HOST'].'/clients/dependent/'.$link;
			    }
				if($plan=="all"){
				 $rel="https://".$_SERVER['HTTP_HOST'].'/clients/all/'.$link;
			    }
				if($plan=="rentalincome"){
				 $rel="https://".$_SERVER['HTTP_HOST'].'/clients/rentalincome/'.$link;
			    }
			    if($plan!="1040"&&$plan!="profitloss"&&$plan!="cm"&&$plan!="deductions"&&$plan!="insolvency"&&$plan!="dependent"&&$plan!="all"&&$plan!="rentalincome"){
			    $contractary=array("taxel1s","taxael1fs","taxel1srn","taxel1ss","taxel1m","taxel1b","taxel2","taxccx","taxccx2","taxotp","taxxtp","taxwai","taxtpc","tax8879","tax8879sp","htmlel1s","htmlel1fs","htmlel1srn","htmlel1ss","htmlel1m","htmlel1b","htmlel2","htmlccx","htmlccx2","htmlotp","htmlxtp","htmlwai","htmltpc","html8879","html8879sp");
			    	//如果是来自Adobe内容
			  	   if (in_array($plan, $contractary)) {
			  	   	       $map11['plan']=$plan;
						   $map11['link']=$link;
						   if($link!='preview'){
                              $map11['caseid']=$caseid;
						   }
						   $clientres=DB::name('users_clients')->where($map11)->find();
						   if(!$clientres){
							   $ins['plan']=$map11['plan'];
							   $ins['link']=$map11['link'];
							   if($link!='preview'){
							     $ins['caseid']=$caseid;
							   }else{
							     $ins['ispreview']=1;
							   }
							   $clientid=DB::name('users_clients')->insertGetId($ins);
						   }else{
							  $clientid=$clientres['id'];
						 }
			  	      $valary=$this->getFieldsValue($caseid,$plan,$clientid,$link);
			  	      if($link!='preview'){
			  	      	  $rel="https://".$_SERVER['HTTP_HOST'].'/clients/key/'.$link;
			  	      }else{
			  	          $rel="https://".$_SERVER['HTTP_HOST'].'/clients/preview?pl='.$plan;
			  	      }
			  	   }else{
			  	   	  if($link=='preview'){
						  //先找到users_clients表有没有对应字段
						   $map11['plan']=$plan;
						   $map11['link']='preview';
						   $clientres=DB::name('users_clients')->where($map11)->find();
						   if(!$clientres){
							   $ins['ispreview']=1;
							   $ins['plan']=$map11['plan'];
							   $ins['link']=$map11['link'];
							   $clientid=DB::name('users_clients')->insertGetId($ins);
						   }else{
							  $clientid=$clientres['id'];
						   }
						   $conres=DB::name('contract')->where('keyval',$plan)->find();
						   if($conres){
							  $fileary=array('CaseID','FirstName','LastName','StatusID','SaleDate','CellPhone','HomePhone','WorkPhone','Email','City','State','State','Zip','Address','AptNo','SSN','SpouseFirstName','SpouseMiddleName','SpouseLastName','SpouseSSN','Birthdate','Birthday','dob','Spousedob','SpouseEmail','SpouseHomePhone','SpouseWorkPhone','SpouseCellPhone','sdob','soFullName','SetOfficerName','GrossSale','MiddleName','taxamount','BusinessName','TaxAmount','GrossSales','ccType','ServiceDetail','TotalPayment','TotalPayments','TotalAmountPaid','TotalFee','Balance');

							  $infoary=array('CaseID','FirstName','LastName','StatusID','SaleDate','StatusName','CellPhone','HomePhone','WorkPhone','Email','City','State','State','Zip','Address','AptNo','SSN','EIN','MartialStatus','BusinessName','BusinessType','BusinessAddress','OweTaxestoFederal','UnfiledTaxestoFederal','TaxLiability','CreatedDate','MiddleName','BestTimeToCall');
								 //找到对应表，添加记录
								 if($conres['type']!=1){//确定是html文档类型						
									 //找到对应表，得到需要填写的字段									  
									 $cdata['user_client_id']=$clientid;
									 $fileinfo=getCrmApi($caseid,'file');
									 $caseinfo=getCrmApi($caseid,'info');
								 
									  $tableName=$conres['tablename'];
									  Db::table($tableName)->find();
										$fields=Db::getFields($tableName);
									   foreach($fields as $key=>$val){
										if($val['name']=='id'||$val['name']=='user_client_id') continue;
										 if (in_array($val['name'], $fileary)) {
											   $cdata[$val['name']]=$fileinfo[$val['name']];
										 }else{
											if (in_array($val['name'], $infoary)) {
											   $cdata[$val['name']]=$caseinfo[$val['name']];
											       /* $pdate=substr($val['ScheduledDate'],0,10);
													$dateObj = new DateTime($pdate);
													$pdate = $dateObj->format('m/d/Y');
													$timestamp=strtotime($pdate);*/
											}else{
											   $cdata[$val['name']]='';
											}
										 }
										}
									  $tableName=substr($tableName,3); 
									  //查看client是否存在，存在即更新，不存在则插入
									  $concheck=DB::name($tableName)->where('user_client_id',$clientid)->find();
									  if($concheck){
										DB::name($tableName)->where('user_client_id',$clientid)->save($cdata);
									  }else{
										DB::name($tableName)->insertGetId($cdata);
									  }
									}
							 }
						$rel="https://".$_SERVER['HTTP_HOST'].'/clients/preview?pl='.$plan;
					}else{
						 //先找到users_clients表有没有对应字段
						   $map11['plan']=$plan;
						   $map11['link']=$link;
						   $map11['caseid']=$caseid;
						   $clientres=DB::name('users_clients')->where($map11)->find();
						   if(!$clientres){
							   $ins['plan']=$map11['plan'];
							   $ins['link']=$map11['link'];
							   $ins['caseid']=$caseid;
							   $clientid=DB::name('users_clients')->insertGetId($ins);
						   }else{
							  $clientid=$clientres['id'];
						   }
						   $conres=DB::name('contract')->where('keyval',$plan)->find();
						   if($conres){
							  $fileary=array('CaseID','FirstName','LastName','StatusID','SaleDate','CellPhone','HomePhone','WorkPhone','Email','City','State','State','Zip','Address','AptNo','SSN','SpouseFirstName','SpouseMiddleName','SpouseLastName','SpouseSSN','Birthdate','Birthday','dob','Spousedob','SpouseEmail','SpouseHomePhone','SpouseWorkPhone','SpouseCellPhone','sdob','soFullName','SetOfficerName','GrossSale','MiddleName','taxamount','BusinessName','TaxAmount','GrossSales','ccType','ServiceDetail','TotalPayment','TotalPayments','TotalAmountPaid','TotalFee','Balance');

							  $infoary=array('CaseID','FirstName','LastName','StatusID','SaleDate','StatusName','CellPhone','HomePhone','WorkPhone','Email','City','State','State','Zip','Address','AptNo','SSN','EIN','MartialStatus','BusinessName','BusinessType','BusinessAddress','OweTaxestoFederal','UnfiledTaxestoFederal','TaxLiability','CreatedDate','MiddleName','BestTimeToCall');
								 //找到对应表，添加记录
								 if($conres['type']!=1){//确定是html文档类型						
									 //找到对应表，得到需要填写的字段									  
									 $cdata['user_client_id']=$clientid;
									 $fileinfo=getCrmApi($caseid,'file');
									 $caseinfo=getCrmApi($caseid,'info');
								 
									  $tableName=$conres['tablename'];
									  Db::table($tableName)->find();
										$fields=Db::getFields($tableName);
									   foreach($fields as $key=>$val){
										if($val['name']=='id'||$val['name']=='user_client_id') continue;
										 if (in_array($val['name'], $fileary)) {
											   $cdata[$val['name']]=$fileinfo[$val['name']];
										 }else{
											if (in_array($val['name'], $infoary)) {
											   $cdata[$val['name']]=$caseinfo[$val['name']];
											}else{
											   $cdata[$val['name']]='';
											}
										 }
										}
									  $tableName=substr($tableName,3); 
									  //查看client是否存在，存在即更新，不存在则插入
									  $concheck=DB::name($tableName)->where('user_client_id',$clientid)->find();
									  if($concheck){
										DB::name($tableName)->where('user_client_id',$clientid)->save($cdata);
									  }else{
										DB::name($tableName)->insertGetId($cdata);
									  }
									}
									$rel="https://".$_SERVER['HTTP_HOST'].'/clients/key/'.$link;
						 }else{
							$rel=''; 
						 }
						
					}
			  	   }
			      
			    }
			 }
			return $rel;
   }
	/*增加client  from the CRM API   ,create client, then send email*/
    public function addclient(){
		/*header("Access-Control-Allow-Origin: https://ntcg.irslogics.com");
		header("Access-Control-Allow-Methods: POST");
		header("Access-Control-Allow-Headers: Content-Type");*/
		
        if (IS_POST) {

          // $data=input('post.');
		   $data = json_decode(file_get_contents('php://input'), true);
	
           $data['reg_time']=time();         
           $data['user_id']=0;//from crm 
		
		   if(!$data['plan']){
				// return json(['code'=>0,'msg'=>'Please select return type']); 
				 $response=array('code'=>0,'msg'=>'Please select return type');
				// echo "1";exit();
			  echo json_encode($response);exit();
			}
		   if(!$data['taxyear']){
				// return json(['code'=>0,'msg'=>'Please select year']); 
				$response=array('code'=>0,'msg'=>'Please select year');
				//echo "2";exit();
			    echo json_encode($response);exit();
			}
		   
		   $pattern = '/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/';
			if (!preg_match($pattern,$data['email'])) {
				//return json(['code'=>0,'msg'=>'Invalid email']);  
				$response=array('code'=>0,'msg'=>'Invalid email');
				//echo "3";exit();
			    echo json_encode($response);exit();
			}
			if(!$data['caseid']){
				$response=array('code'=>0,'msg'=>'No Caseid detected');
				 //echo "4";exit();
			   echo json_encode($response);exit();
				// return json(['code'=>0,'msg'=>'No Caseid detected']); 
			}
		   $ww2['caseid']=$data['caseid'];
		   
		   $ww2['taxyear']=$data['taxyear'];
		   $ww2['plan']=$data['plan'];
		   $check2=DB::name('users_clients')->where($ww2)->find();
		   $ww2['email']=$data['email'];
		   if($check2){
		   	   if($data['email']!=$check2['email']){
				  	$maa['id']=$check2['id'];
				  	$maadata['email']=$data['email'];
				  	DB::name('users_clients')->where($maa)->save($maadata);//更新邮箱
				  }
			   // send email directly
				 $this->crmEmailSend($check2['id']);
				  // return json(['code'=>1,'msg'=>'Client exists,contract is sent directly']);  
				 $response=array('code'=>1,'msg'=>'Client exists,contract is sent directly');
				// $link="http://".$_SERVER['HTTP_HOST'].'/clients/folders/'.$check2['link'];
				 $link=$this->getLink($ww2['plan'],$check2['link'],$data['caseid']);
				 $pre_url="https://ntcg.irslogics.com/publicapi/cases/activity";
				 $dname=$this->getPlanName($ww2['plan']);
				 $pre_postdata="{\r\n             \t\"CaseID\":\"".$data['caseid']."\",\r\n             \t\"ActivityType\":\"General\",\r\n             \t\"Subject\":\"".$dname."_Document Sent\",\r\n             \t\"Comment\":\" Email Sent with link:  <a target='_blank' href='".$link."'>".$link."</a> Tax documents to finalize"." \"\r\n}\r\n";
				 $pre_header=array("Content-Type: application/json","Authorization: f6bf9800223b44a5ac6948ee6556225d");
				 $this->curlRequest($pre_url,$pre_postdata,$pre_header);
	             // return json(['code'=>1,'msg'=>'Contract was successfully sent']); 
				  $response = array('code'=>1,'msg'=>'Contract was successfully sent1','link'=>$link);
				 echo json_encode($response);exit();
		}
		  
			/*//看是否有相同 的email和caseid
		   $ww['caseid']=$data['caseid'];
		   $ww['taxyear']=$data['taxyear'];
		   $check=DB::name('users_clients')->where($ww)->find();
		   if($check){
			   if($check['email']!=$data['email']){
				  // return json(['code'=>0,'msg'=>'Caseid has been used']); 
				   $response = array('code'=>0,'msg'=>'Caseid has been used');
				   echo "6";exit();
			   }
		   }*/
		   
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
			  //如果是profit loss  将在profit loss表中创建
			  if($data['plan']=='profitloss'){
				  $profit['user_client_id']=$res;
				  $profit['updatetime']=time();
				  Db::name('user_profit_loss')->insertGetId($profit);
			  }
			  if($data['plan']=='cm'){  //financial
				 $insert['user_client_id']=$res;
				 $insert['updatetime']=time();
				 $pid=DB::name('user_financial')->insertGetId($insert);
			  }
			  if($data['plan']=='deductions'){
				 $insert['user_client_id']=$res;
				 $insert['updatetime']=time();
				 $pid=DB::name('user_deductions')->insertGetId($insert);
			  }
			  if($data['plan']=='insolvency'){
				 $insert['user_client_id']=$res;
				 $insert['updatetime']=time();
				 $pid=DB::name('user_insolvency')->insertGetId($insert);
			  }
			  if($data['plan']=='dependent'){
				 $insert['user_client_id']=$res;
				 $insert['updatetime']=time();
				 $pid=DB::name('user_dependent')->insertGetId($insert);
			  }
			  if($data['plan']=='all'){
				 $insert['user_client_id']=$res;
				 $insert['updatetime']=time();
				 $pid=DB::name('user_all')->insertGetId($insert);
			  }
			  if($data['plan']=='rentalincome'){
				 $insert['user_client_id']=$res;
				 $insert['updatetime']=time();
				 $pid=DB::name('user_rentalincome')->insertGetId($insert);
			  }
			  $tttag=0;
			  if($data['plan']!='1040'&&$data['plan']!='profitloss'&&$data['plan']!='cm'&&$data['plan']!='deductions'&&$data['plan']!='insolvency'&&$data['plan']!='dependent'&&$data['plan']!='all'&&$data['plan']!='rentalincome'){
			  	   $contractary=array("taxel1s","taxael1fs","taxel1srn","taxel1ss","taxel1m","taxel1b","taxel2","taxccx","taxccx2","taxotp","taxxtp","taxwai","taxtpc","tax8879","tax8879sp","htmlel1s","htmlel1fs","htmlel1srn","htmlel1ss","htmlel1m","htmlel1b","htmlel2","htmlccx","htmlccx2","htmlotp","htmlxtp","htmlwai","htmltpc","html8879","html8879sp");
			  	   if (in_array($data['plan'], $contractary)) {
			  	   	  $valary=$this->getFieldsValue($data['caseid'],$data['plan'],$res,$data['link']);
			  	   	  //$link=$data['link'];
			  	   }else{
				  	   	//到contract表中去查找
				  	    $conres=DB::name('contract')->where('keyval',$data['plan'])->find();
	                   if($conres){
						$fileary=array('CaseID','FirstName','LastName','StatusID','SaleDate','CellPhone','HomePhone','WorkPhone','Email','City','State','State','Zip','Address','AptNo','SSN','SpouseFirstName','SpouseMiddleName','SpouseLastName','SpouseSSN','Birthdate','Birthday','dob','Spousedob','SpouseEmail','SpouseHomePhone','SpouseWorkPhone','SpouseCellPhone','sdob','soFullName','SetOfficerName','GrossSale','MiddleName','taxamount','BusinessName','TaxAmount','GrossSales','ccType','ServiceDetail','TotalPayment','TotalPayments','TotalAmountPaid','TotalFee','Balance');

						$infoary=array('CaseID','FirstName','LastName','StatusID','SaleDate','StatusName','CellPhone','HomePhone','WorkPhone','Email','City','State','State','Zip','Address','AptNo','SSN','EIN','MartialStatus','BusinessName','BusinessType','BusinessAddress','OweTaxestoFederal','UnfiledTaxestoFederal','TaxLiability','CreatedDate','MiddleName','BestTimeToCall');
							 //找到对应表，添加记录
	                     if($conres['type']!=1){//确定是html文档类型						
						     //找到对应表，得到需要填写的字段
						       //$tableName=str_replace("tp_",'',$conres['tablename']);
						     $cdata['user_client_id']=$res;
						     $fileinfo=getCrmApi($data['caseid'],'file');
						     $caseinfo=getCrmApi($data['caseid'],'info');
						 
							  $tableName=$conres['tablename'];
							  Db::table($tableName)->find();
								$fields=Db::getFields($tableName);
							   foreach($fields as $key=>$val){
							   	if($val['name']=='id'||$val['name']=='user_client_id') continue;
							   	 if (in_array($val['name'], $fileary)) {
							   	 	   $cdata[$val['name']]=$fileinfo[$val['name']];
								 }else{
								 	if (in_array($val['name'], $infoary)) {
	                                   $cdata[$val['name']]=$caseinfo[$val['name']];
								 	}else{
	                                   $cdata[$val['name']]='';
								 	}
								 }
							    }
							  $tableName=substr($tableName,3); 
					          DB::name($tableName)->insertGetId($cdata);
							}
				     }
			  	   }
			  	   
			     $tttag=1;
			  }
			 // if($tttag==0){
			  	  // successfully created,and then send email
			     $this->crmEmailSend($res);
			  //}
			 
			  //create activity to CRM
			if($tttag==1){
				$link="https://".$_SERVER['HTTP_HOST'].'/clients/key/'.$data['link'];//$this->getLink($data['plan'],$data['link'],$data['caseid']); 
			}else{
				$link=$this->getLink($data['plan'],$data['link']); 
			}
			 
			//  $link="http://".$_SERVER['HTTP_HOST'].'/clients/folders/'.$data['link'];
			 $pre_url="https://ntcg.irslogics.com/publicapi/cases/activity";
			 $dname=$this->getPlanName($data['plan']);
			 $pre_postdata="{\r\n             \t\"CaseID\":\"".$data['caseid']."\",\r\n             \t\"ActivityType\":\"General\",\r\n             \t\"Subject\":\"".$dname."_Document Sent\",\r\n             \t\"Comment\":\" Email Sent with link:  <a target='_blank' href='".$link."'>".$link."</a> Tax documents to finalize"." \"\r\n}\r\n";
			 $pre_header=array("Content-Type: application/json","Authorization: f6bf9800223b44a5ac6948ee6556225d");
			 if($tttag==0){
			   $this->curlRequest($pre_url,$pre_postdata,$pre_header);
			 }
             // return json(['code'=>1,'msg'=>'Contract was successfully sent']); 
			  $response = array('code'=>1,'msg'=>'Contract was successfully sent','link'=>$link);
			//  echo "7";exit();
			 echo json_encode($response); exit();
           }else{
              //return json(['code'=>0,'msg'=>'Fail to send']);  
			  $response = array('code' => 0,'msg'=>'Failed to send');
			  //  echo "8";exit();
			 echo json_encode($response);exit();
           } 
        }else{
			echo "Forbidden!!!!!!";
		}
    }
   public function getFieldsValue($CASEIDD,$plan,$userclientid,$link){
     	$conres=DB::name('contract')->where('keyval',$plan)->find();
	    if($conres){
	         if($conres['type']!=1){
		   	   if($CASEIDD){
					$response=getCrmApi($CASEIDD,"file");
					$accinfo=getCrmApi($CASEIDD,"account");
					$caseinfo=getCrmApi($CASEIDD,"info");
					$paymentinfo=getCrmApi($CASEIDD,"schedule");
					$invoice=getCrmApi($CASEIDD,"invoice");
					$SERVICESINFO=getCrmApi($CASEIDD,"service");
					if (empty($response)) {
						  $rdvalue = [
							'FirstName' => '',
							'MiddleName' => '',
							'LastName' => '',
							'Address' => '',
							'AptNo' => '',
							'State' => '',
							'City' => '',
							'Zip' => '',
							'SSN' => '',
							'dob' => '',
							'Email' => '',
							'WorkPhone' => '',
							'HomePhone' => '',
							'CellPhone' => '',
							'SpouseFirstName' => '',
							'SpouseMiddleName' => '',
							'SpouseLastName' => '',
							'SpouseSSN' => '',
							'Spousedob' => '',
							'SpouseEmail' => '',
							'SpouseCellPhone' => '',
							'SpouseWorkPhone' => '',
							'SpouseHomePhone' => '',
						  ];
					     $response = $rdvalue;
					 }
					     $docinfo=[];
						  foreach($accinfo as $key=>$val){
							 if($val['PrimaryAccount']==1){
								 $docinfo=$val;				
								 break;
							 }
						  }
						  if (empty($docinfo)) {
							  $defaultValues = [
								'CCType' => '',
								'NameOnAccount' => '',
								'CCNo' => '',
								'CCExpDate' => '',
								'Address' => '',
								'City' => '',
								'Zip' => ''
							  ];
							  $docinfo = $defaultValues;
							}
						 $SCHEDULEPAYMENTINFO='';
						
						 if($paymentinfo){
							$curtime=time()-14*27*60*60;
							foreach($paymentinfo as $key=>$val){
								$pdate=substr($val['ScheduledDate'],0,10);
								$amount=$val['Amount'];
								//$date = '2024-08-20';
								$dateObj = new DateTime($pdate);
								$pdate = $dateObj->format('m/d/Y');
								$timestamp=strtotime($pdate);
								if($timestamp>$curtime){
									$SCHEDULEPAYMENTINFO.="\r\n".$pdate."------------------- Amount: $".$amount."";
									//$SCHEDULEPAYMENTINFO.="<br>".$pdate."------------------- Amount: $".$amount."";	
								}
							}
						}
						$CCTYPE = $docinfo['CCType'];
						switch($CCTYPE){  //Visa/MC/Amex)
						   case 1:
							  $CCTYPE="Visa";break;
						   case 2:
							  $CCTYPE="Master";break;
						   case 3:
							  $CCTYPE="Amex";break;
					   }
					   if ($CCTYPE ==""){
							$CCTYPE ="-";
						}
						$CCNAME = $docinfo['NameOnAccount'];				
						$CCNUMBER =$docinfo['CCNo'];
						if ($CCNUMBER ==""){
							$CCNUMBER ="-";
						}
						$CCEXP = $docinfo['CCExpDate'];
						if ($CCEXP ==""){
							$CCEXP ="-";
						}
						//startcccendccc
						$CCADDR = $docinfo['Address'];
						if ($CCADDR ==""){
							$CCADDR ="-";
						}

						$CCCITY = $docinfo['City'];
						if ($CCCITY ==""){
							$CCCITY ="-";
						}
						$CCSCODE ="***";
						if ($CCSCODE ==""){
							$CCSCODE ="-";
						}
						
						$CCSTATE = '';
						
						$CCZIP =$docinfo['Zip'];
						if ($CCZIP ==""){
							$CCZIP ="-";
						}else{
							$CCSTATE=getState($CCZIP);
						}
					if(!$caseinfo){
						$defaultValues1 = [
						'OweTaxestoFederal' => '',
						'UnfiledTaxestoFederal' => '',
						'SetOfficerID' => '',
						'EIN' => '',
						'BusinessAddress' => '',
						'TaxLiability' => '',
						'CasemanagerID' => ''
					  ];
					  $caseinfo = $defaultValues1;
					}
					$YEARSOWNED = $caseinfo['OweTaxestoFederal'];
					$YEARSNOTFILED = $caseinfo['UnfiledTaxestoFederal'];
					$INVOICEINFO ='';
				    //$invoice=getResponse($CASEIDD,"invoice");
				    $max='1970-01-01';
				    $INVOICEINFO='';
				    $count=0;
				    if($invoice){
						foreach($invoice as $key=>$val){
						$price=$val['UnitPrice']*$val['Quantity'];
						$indate=substr($val['Date'],0,10);
						$dateObj1 = new DateTime($indate);
						$pdate2 = $dateObj1->format('m/d/Y');
						$timestamp2=strtotime($pdate2);
						if($timestamp2>$curtime){
							$INVOICEINFO.="\r\n".$val['InvoiceTypeName']."--$".$price."";
						}
						$tempdate=substr($val['Date'],0,10);
						if($tempdate>=$max){
							 $max=$tempdate;
							 $maxinvoice=$price;
						}
						$count++;
					   }
						
					}else{
						$invoice='';
					}	
					if($INVOICEINFO==''&&$invoice){
						 $pric1e=$invoice[$count-1]['UnitPrice']*$invoice[$count-1]['Quantity'];
						 $INVOICEINFO.="\r\n".$invoice[$count-1]['InvoiceTypeName']."--$".$pric1e."";
					 }
					 if($SERVICESINFO){
						 $SERVICESINFO2='';
					    foreach($SERVICESINFO as $key=>$val){
							$detail='';
						   foreach($val['ServiceDetails'] as $key1=>$val1){
							   $detail.=" ".$val1." ";
						   }
							 $SERVICESINFO2.="\r\n".$val['Name'].": ".$detail;
						}
						$SERVICESINFO =$SERVICESINFO2;
						
					 }else{
					   $SERVICESINFO='';
					 }
					$SERVICENAME =$SERVICESINFO2;
					$PENDPAYMENTS = '';
					$SETTLEMENTSTAFF =$caseinfo['SetOfficerID']['Name'];
					$BUSINESSNAME = $caseinfo['BusinessName'];
					
					$EIN = $caseinfo['EIN'];
					$BUSADDR1 =$caseinfo['BusinessAddress'];
					$BUSADDR2 ='';
					$MAILINGADDR = $docinfo['Address'];
				
					$BACKTAXES = $caseinfo['TaxLiability'];
				    $BACKTAXES = '$'.number_format($BACKTAXES, 2);
					$GROSSSALE = $maxinvoice;
				    $GROSSSALE = '$'.number_format($GROSSSALE, 2);
					$SOEMAIL = '';
					$SOPHONE ='';
					$SOFAXPHONE =''; 
					$CMNAME = $caseinfo['CasemanagerID']['Name'];
					$CMEMAIL = '';
					$CMPHONE ='';
					$JOBNAME ='';
				    if($YEARSNOTFILED==''){
						$YEARSNOTFILED ='N/A';
					}

					if (!$SCHEDULEPAYMENTINFO) {
						$SCHEDULEPAYMENTINFO = $INVOICEINFO; 
					}
					$FIRSTNAME = $response['FirstName'];
					$MIDDLENAME =$response['MiddleName'];
					$LASTNAME = $response['LastName'];
					$FULLNAME= $FIRSTNAME." ".$MIDDLENAME." ".$LASTNAME; // NM
				    if ($CCNAME ==""){
						$CCNAME =$FULLNAME;
					}
					$ADDRESS1 =$response['Address'];
					$APARTMENT = $response['AptNo'];
					$ADDRESSMAIN1=$ADDRESS1." ".$APARTMENT;
					$STATE = $response['State'];
				    if ($CCSTATE ==""){
						$CCSTATE=$STATE;
					}
					$CITY = $response['City'];
					$ZIPCODE = $response['Zip'];
					$ADDRESSMAIN2=$CITY.", ".$STATE ." ".$ZIPCODE;
					$SSN = $response['SSN'];
					$DOB =$response['dob'];
				    if($DOB){
					   $DOB=substr($DOB, 0, 10);
					   $date11 = new DateTime($DOB);
					   $DOB = $date11->format('m-d-Y');
				    }
					$EMAIL = $response['Email'];
					$WORKPHONE = $response['WorkPhone'];
					if($response['WorkPhone']){
						$WORKPHONE=$response['WorkPhone'];
					}else{
						if($response['HomePhone']){
							$WORKPHONE=$response['HomePhone'];
						}else{
							if($response['CellPhone']){
								$WORKPHONE=$response['CellPhone'];
							}
						}
					}
					$HOMEPHONE =  $response['HomePhone'];
					$CELLPHONE =$response['CellPhone']; 
					$SPFIRSTNAME = $response['SpouseFirstName']; 
					$SPMNAME = $response['SpouseMiddleName'];
					$SPLASTNAME =$response['SpouseLastName']; 
					$SPFULLNAME= $SPFIRSTNAME." ".$SPMNAME." ".$SPLASTNAME;
					$SPSSN =$response['SpouseSSN'];
					$SPDOB = $response['Spousedob'];
				    if($SPDOB){
					   $SPDOB=substr($SPDOB, 0, 10);
						//echo $SPDOB."<br>";
						$date11 = new DateTime($SPDOB);
						$SPDOB = $date11->format('m-d-Y');
						//echo $SPDOB;
				    }
					$SPEMAIL = $response['SpouseEmail'];
					$SPCELLPHONE = $response['SpouseCellPhone']; 
					$SPWORKPHONE = $response['SpouseWorkPhone'];
					$SPHOMEPHONE =$response['SpouseHomePhone'];
					$HIDDENCASEID='***'.$CASEIDD.'***'; 
					$apiary=array('CCT'=>$CCTYPE,'CASEID'=>$CASEIDD,'CCN'=>$CCNAME,'CCNU'=>$CCNUMBER,'CCE'=>$CCEXP,'CCA'=>($CCADDR),'CCC'=>$CCCITY,'CCV'=>$CCSCODE,'CCST'=>$CCSTATE,'CCZ'=>$CCZIP,'IN'=>$INVOICEINFO,'SO'=>$SETTLEMENTSTAFF,'DEBT'=>$BACKTAXES,'SL'=>$GROSSSALE,'NM'=>$FULLNAME,'ADDR'=>($ADDRESSMAIN1),'ADDR2'=>($ADDRESSMAIN2),'SSN'=>$SSN,'WP'=>$WORKPHONE,'SP'=>$SPFULLNAME,'SPN'=>$SPSSN,'BN'=>$BUSINESSNAME,'BA1'=>$BUSADDR1,'BA2'=>$BUSADDR2,'EI'=>$EIN,'HID'=>$HIDDENCASEID,'MA'=>$MAILINGADDR,'DB'=>$DOB,'SDB'=>$SPDOB,'EM'=>$EMAIL,'YO'=>$YEARSOWNED,'YNF'=>$YEARSNOTFILED,'SN'=>($SERVICENAME),'SI'=>($SERVICESINFO),'SPM'=>($SCHEDULEPAYMENTINFO),'JB'=>($JOBNAME),'inputCCT'=>$CCTYPE,'inputCCN'=>$CCNAME,'inputCCNU'=>$CCNAME,'inputCCE'=>$CCEXP,'inputCCV'=>$CCSCODE,'inputCCA'=>$CCADDR,'inputCCC'=>$CCCITY,'inputCCST'=>$CCSTATE,'inputCCZ'=>$CCZIP,'inputSPM'=>($SCHEDULEPAYMENTINFO),'inputHID'=>$HIDDENCASEID);
				}else{
					$apiary=[];
				}
		
				 $cdata['user_client_id']=$userclientid;
				 $tableName=$conres['tablename'];
				 Db::table($tableName)->find();
				 $fields=Db::getFields($tableName);
				foreach($fields as $key=>$val){
						if($val['name']=='id'||$val['name']=='user_client_id') continue;
						if(isset($apiary[$val['name']])){
							$cdata[$val['name']]=$apiary[$val['name']];
						}
						// else{
						// 	$cdata[$val['name']]='';
						// }
				}
				$tableName=substr($tableName,3); 
				$concheck=DB::name($tableName)->where('user_client_id',$userclientid)->find();
				if($concheck){
					DB::name($tableName)->where('user_client_id',$userclientid)->save($cdata);
				 }else{
					DB::name($tableName)->insertGetId($cdata);
				 }
				 //echo DB::name($tableName)->getLastSql();
				//DB::name($tableName)->insertGetId($cdata);
				//echo DB::name($tableName)->getLastSql();
			}
		 }
	
   }
	public function crmEmailSend($id){		
		   $map['id']=$id;
		   $res=DB::name('users_clients')->where($map)->field('id,firstname,lastname,email,status,link,taxyear,caseid,mobile,plan')->find();
		   if($res){
			   //邮件发送
			    $mail = new PHPMailer(true);
			    $mail->isSMTP();
				$mail->Host = 'mail.smtp2go.com';
				$mail->SMTPAuth = true;
				$mail->Username = 'mark@republictax.com';
				$mail->Password = '5gioZVsUnQ4o';
				$mail->Port = 2525;
				$mail->setFrom('taxprep@republictax.com', 'Republic Tax Annual Prep');
				$mail->SMTPDebug = 0; 
				$mail->isHTML(true);                                    // Set email format to HTML
				$mail->AltBody = '';
				$pattern = '/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/';
				if (!preg_match($pattern, $res['email'])) {
					return json(['code'=>0,'msg'=>'Invalid email']);  
				}
			
				$new[]='';
                $link=$this->getLink($res['plan'],$res['link'],$res['caseid']);  
				//$link="http://".$_SERVER['HTTP_HOST'].'/clients/folders/'.$res['link'];
				$new['email']=$res['email'];
				//$new['email']='760492603@qq.com';
				$new['link']=$link;
				$new['firstname']=$res['firstname'];
				$new['lastname']=$res['lastname'];
				$new['taxyear']=$res['taxyear'];

				$mail->addAddress($res['email'], 'Republic Tax Annual Prep');				
				$mail->Subject ='Republic Tax has sent you a document to review ';
				$name=$this->getPlanName($res['plan']);
				$mail->Body    = $this->getEmailHtml($new,$name);
				//短信发送
				$this->SMSSend($res,$link);
				//发送短信结束 
				// Send the message, check for errors                     
				if ($mail->send()) {
					//更新发送时间
					$log['clientid']=$res['id'];
					$log['addtime']=time();
					DB::name('mail_log')->insert($log);			

					
					$pre_url="https://ntcg.irslogics.com/publicapi/cases/activity";
			 
					$pre_postdata="{\r\n             \t\"CaseID\":\"".$res['caseid']."\",\r\n             \t\"ActivityType\":\"General\",\r\n             \t\"Subject\":\"".$name."_Email&SMS Document Sent\",\r\n             \t\"Comment\":\" Email &SMS were sent with link:  <a target='_blank' href='".$link."'>".$link."</a> Tax documents to finalize"." \"\r\n}\r\n";
					$pre_header=array("Content-Type: application/json","Authorization: f6bf9800223b44a5ac6948ee6556225d");
					$this->curlRequest($pre_url,$pre_postdata,$pre_header);
					return true;
				}else{
					return false;
				}
		   }else{
			  return false;
		   }
	}
	function curlRequest($url,$postdata,$header){
        $curl = curl_init();
		curl_setopt_array($curl, array(
										  CURLOPT_URL =>$url,
										  CURLOPT_RETURNTRANSFER => true,
										  CURLOPT_ENCODING => "",
										  CURLOPT_MAXREDIRS => 10,
										  CURLOPT_TIMEOUT => 0,
										  CURLOPT_FOLLOWLOCATION => true,
										  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
										  CURLOPT_CUSTOMREQUEST => "POST",
										  CURLOPT_POSTFIELDS =>$postdata,
										  CURLOPT_HTTPHEADER =>$header ,
										));
		$response = curl_exec($curl);
		curl_close($curl);
    }
	public function sendbatch(){
		//查找状态为未完成的内容 
		if (IS_POST) {
           $data=input('post.');
		    $map[]=['user_id','=',$data['user_id']];
		    $map[]=['status','<>',2];
		    $res=DB::name('users_clients')->where($map)->field('id,firstname,lastname,email,status,link,taxyear,caseid,mobile,plan')->select();
			$counter=0;
			$total=0;
			foreach($res as $key=>$val){
				$mail = new PHPMailer(true);
				$mail->isSMTP();
				$mail->Host = 'mail.smtp2go.com';
				$mail->SMTPAuth = true;
				$mail->Username = 'mark@republictax.com';
				$mail->Password = '5gioZVsUnQ4o';
				$mail->Port = 2525;
				$mail->setFrom('taxprep@republictax.com', 'Republic Tax Annual Prep');
				$mail->SMTPDebug = 0; 
				$mail->isHTML(true);                                    // Set email format to HTML
				$mail->AltBody = '';
				$pattern = '/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/';
				if (!preg_match($pattern, $val['email'])) {
					//echo $val['email']."<br>";
					continue;
				}
			
				$new[]='';
				//$link="http://".$_SERVER['HTTP_HOST'].'/clients/folders/'.$val['link'];
				$link=$this->getLink($val['plan'],$val['link'],$val['caseid']);
				$new['email']=$val['email'];
				$new['link']=$link;
				$new['firstname']=$val['firstname'];
				$new['lastname']=$val['lastname'];
				$new['taxyear']=$val['taxyear'];
				$mail->addAddress($val['email'], 'Republic Tax Annual Prep');				
				$mail->Subject = 'Republic Tax has sent you a document to review';
				$name=$this->getPlanName($val['plan']);
				$mail->Body    = $this->getEmailHtml($new);
				$total++;
				//短信发送
		
				$this->SMSSend($val,$link);
				//发送短信结束 
				// Send the message, check for errors                     
				if ($mail->send()) {
					//更新发送时间
					$counter++;
					$log['clientid']=$val['id'];
					$log['addtime']=time();
					DB::name('mail_log')->insert($log);
					//return json(['code'=>0,'msg'=>$mail->ErrorInfo]);
				}else{
					//$counter2++;
					//file_put_contents('emailsent.txt',$mail->ErrorInfo.'\n',FILE_APPEND);
				}
			}
			return json(['code'=>1,'msg'=>$counter.'/'.$total.' emails have been sent','url'=>url('User/center')->build()]);
		}
	}
	public function singleEmailSend(){
		 if (IS_POST) {
           $data=input('post.');
		   $map['id']=$data['id'];
		   $res=DB::name('users_clients')->where($map)->field('id,firstname,lastname,email,status,link,taxyear,caseid,mobile,plan')->find();
		   if($res){
			   //邮件发送
			    $mail = new PHPMailer(true);
			    $mail->isSMTP();
				$mail->Host = 'mail.smtp2go.com';
				$mail->SMTPAuth = true;
				$mail->Username = 'mark@republictax.com';
				$mail->Password = '5gioZVsUnQ4o';
				$mail->Port = 2525;
				$mail->setFrom('taxprep@republictax.com', 'Republic Tax Annual Prep');
				$mail->SMTPDebug = 0; 
				$mail->isHTML(true);                                    // Set email format to HTML
				$mail->AltBody = '';
				$pattern = '/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/';
				if (!preg_match($pattern, $res['email'])) {
					return json(['code'=>0,'msg'=>'Invalid email']);  
				}
			
				$new[]='';

				//$link="http://".$_SERVER['HTTP_HOST'].'/clients/folders/'.$res['link'];
				$link=$this->getLink($res['plan'],$res['link'],$res['caseid']);
				$new['email']=$res['email'];
				$new['link']=$link;
				$new['firstname']=$res['firstname'];
				$new['lastname']=$res['lastname'];
				$new['taxyear']=$res['taxyear'];
				$mail->addAddress($res['email'], 'Republic Tax Annual Prep');				
				$mail->Subject = 'Republic Tax has sent you a document to review';
				$name=$this->getPlanName($res['plan']);
				$mail->Body    = $this->getEmailHtml($new,$name);
				//短信发送
				//echo $res['email'];
				$this->SMSSend($res,$link);
				//发送短信结束 
				// Send the message, check for errors   
				//echo $link;
				if ($mail->send()) {
					//更新发送时间
					$log['clientid']=$res['id'];
					$log['addtime']=time();
					DB::name('mail_log')->insert($log);
					return json(['code'=>1,'msg'=>'Email/SMS has been sent successfully','url'=>url('User/center')->build()]);
				}else{
					return json(['code'=>0,'msg'=>$mail->ErrorInfo]);
				}
		   }else{
			   return json(['code'=>0,'msg'=>'Invalid email']);
		   }
		 }
	}
	public function SMSSend($res,$link){
		
		//先查看是否有mobile，如果有直接发送，如果没有先从api中获取
				if($res['mobile']){
					$mobile=$res['mobile'];
				}else{
					$caseid=$res['caseid'];
					$url='https://ntcg.irslogics.com/publicapi/cases/casefile?CaseID='.$caseid.'&details=ServiceDetail/Campaign&apikey=f6bf9800223b44a5ac6948ee6556225d';
					//echo $url;
					$infoff=curlget($url);
					$ary2=json_decode($infoff,true);
					$ary=json_decode($ary2['data'],true);
					//print_r($ary);exit();
					if($ary['CellPhone']){
						$mobile=$ary['CellPhone'];
					}else{
						if($ary['HomePhone']){
							$mobile=$ary['HomePhone'];
						}else{
							if($ary['WorkPhone']){
								$mobile=$ary['WorkPhone'];
							}
						}
					}
					if($mobile){
						$mobile=str_replace("(","",$mobile);
						$mobile=str_replace(")","",$mobile);
						$mobile=str_replace("-","",$mobile);
						$mobile="+1".$mobile;
						//存储至数据库
						$msave['mobile']=$mobile;
						$map['id']=$res['id'];
					    DB::name('users_clients')->where($map)->save($msave);//更新数据库内手机号
					}
				}
				//$mobile='+16266776013';
		//	echo $mobile;exit();
				if($mobile){
					//echo $mobile;exit();
					$sid = 'ACb91b2ec9f22a24c28a1563de83c64dfc';
					$token = 'cb30974744a7615ef9dd692ebcc8e94d';
					$client = new Client($sid, $token);
					// Specify the phone numbers in [E.164 format](https://www.twilio.com/docs/glossary/what-e164) (e.g., +16175551212)
					// This parameter determines the destination phone number for your SMS message. Format this number with a '+' and a country code
					//$phoneNumber = "+16266776013";
					$phoneNumber = $mobile;
					//$phoneNumber='+13523774813';
				
					// This must be a Twilio phone number that you own, formatted with a '+' and country code
					$twilioPurchasedNumber = "49316";
                     try {
						// Send a text message
						$message = $client->messages->create(
							$phoneNumber,
							[
								'from' => $twilioPurchasedNumber,
								'body' => $this->getSMSConent($res['firstname'],$res['lastname'],$link)
							]
						);
						 $fail_log['name']=$res['firstname']." ".$res['lastname']." ".$res['email'];
                         $fail_log['addtime']=time();
                         $fail_log['response']='success';
                         $fail_log['tomobile']=$mobile;
				         DB::name('user_clientsmslog')->insert($fail_log);
						//echo $message->sid;exit();
						//return json(['code'=>1,'msg'=>"Message sent successfully with sid = " . $message->sid ."\n\n"]);
						//print("Message sent successfully with sid = " . $message->sid ."\n\n");
					 } catch (\Twilio\Exceptions\RestException $e) {
				         $content = $e->getMessage();
						 //print_r($content);
				            //写入异常日志
				         $fail_log['name']=$res['firstname']." ".$res['lastname']." ".$res['email'];
                         $fail_log['addtime']=time();
                         $fail_log['response']=$content;
                         $fail_log['tomobile']=$mobile;
				         DB::name('user_clientsmslog')->insert($fail_log);
						//return json(['code'=>0,'msg'=>"Couldn't send message to ".$phoneNumber]);
					}
				}
				
	}
	public function emailSend(){
		//查找状态为未完成的内容 
		
		    $map[]=['status','<>',2];
		    $map[]=['id','>',3517];
		 	$map[]=['plan','<>','all'];
		    $map[]=['sevenemail','=',0];//查找创建7天内未完成的
		    $res=DB::name('users_clients')->where($map)->field('id,firstname,lastname,email,status,link,taxyear,caseid,mobile,plan,reg_time,firstemail,threeemail,sevenemail')->select();
			
			//
			/*
            $mail->Subject = '2023 Tax Filing Season - Republic Tax';
			$mail->addAddress($email, 'Clients');
			    $new['email']=$email;
				$new['link']='ddd';
				$new['firstname']='a';
				$new['lastname']='a';
				$new['taxyear']='2024';
            $mail->Body    = $this->getEmailHtml($new);
			$rr=$mail->send();*/
				
			foreach($res as $key=>$val){
				//先查看超过7天且未发送7天邮件的
			   //查看超过3天的，发送一次
				//判断是否需要发 送邮件
				//创建时间距离当前时间的差
				$sendtag=0;
				$a=0;
				$b=0;
				$c=0;
				$span=time()-$val['reg_time'];
				$updata=[];
				if($span>=7*24*60*60){//超过7天直接写定7天标识 并发送
					   $a=1;
                      $updata['sevenemail']=1;
                      $sendtag=1;
				}elseif($span>=3*24*60*60){
					  if(!$val['threeemail']){
					  	$b=1;
					  	 $updata['threeemail']=1;
                         $sendtag=1;
					  }
				}elseif($span>=24*60*60){
					if(!$val['firstemail']){
						$c=1;
					  $updata['firstemail']=1;
                      $sendtag=1;
                    }
				}
				//echo $val['caseid']." ".$val['email']."创建时间：".date("Y-m-d H:i:s",$val['reg_time'])."发送时间：".date("Y-m-d H:i:s",time()).",来源".$a.$b.$c."<br>";
				//if($sendtag&&($val['email']=='mark@republictax.com'||$val['email']=='harvard@republictax.com'||$val['email']=='760492603@qq.com')){
				if($sendtag){					
					$mail = new PHPMailer(true);
					$mail->isSMTP();//重要，强制使用SMTP服务器
					$mail->Host = 'mail.smtp2go.com';
					$mail->SMTPAuth = true;
					$mail->Username = 'mark@republictax.com';
					$mail->Password = '5gioZVsUnQ4o';
					$mail->Port = 2525;
					$mail->setFrom('taxprep@republictax.com', 'Republic Tax Annual Prep');
					$mail->SMTPDebug = 0; 
					$mail->isHTML(true);                                    // Set email format to HTML
					$mail->AltBody = '';
					$pattern = '/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/';
					if (!preg_match($pattern, $val['email'])) {
						echo $val['email']."<br>";
						continue;
					}
				
					$new[]='';
					//$link="http://".$_SERVER['HTTP_HOST'].'/clients/folders/'.$val['link'];
					$link=$this->getLink($val['plan'],$val['link'],$val['caseid']);
					$new['email']=$val['email'];
					//$new['email']='Harvard@republictax.com';
					$new['link']=$link;
					$new['firstname']=$val['firstname'];
					$new['lastname']=$val['lastname'];
					$new['taxyear']=$val['taxyear'];
					$mail->addAddress('Harvard@republictax.com', 'Republic Tax Annual Prep');
					//$mail->addAddress('760492603@qq.com', 'Republic Tax Annual Prep');
					//$mail->addAddress($val['email'], 'Republic Tax Annual Prep');				
					$mail->Subject ='Republic Tax has sent you a document to review ';
					$name=$this->getPlanName($val['plan']);
					$mail->Body    = $this->getEmailHtml($new,$name);
					$val['mobile']='+16266776013';
					//print_r($val);exit();
					$this->SMSSend($val,$link);//测试屏蔽
					// Send the message, check for errors   
					echo $val['caseid']." ".$val['email']." 创建时间：".date("Y-m-d H:i:s",$val['reg_time'])."发送时间：".date("Y-m-d H:i:s",time()).",来源".$a.$b.$c."<br>";               
					if ($mail->send()) {
						//更新发送时间
						$counter++;
						$log['clientid']=$val['id'];
						$log['addtime']=time();
						//DB::name('mail_log')->insert($log); //测试屏蔽
						//更新发送标识
						$map11['id']=$val['id'];
						if($updata){
							DB::name('users_clients')->where($map11)->save($updata);//测试屏蔽
						}
						
						echo "Success--".$val['email']."<br>";
						//return json(['code'=>0,'msg'=>$mail->ErrorInfo]);
					}else{
						file_put_contents('emailsent.txt',$val['email'].$val['id'].$mail->ErrorInfo.'\n',FILE_APPEND);
					}
			   }
			}
			echo $counter;
	}
	public function sendEmail($email,$link,$firstname,$lastname,$taxyear,$plan,$caseid){
				      
			
	}
	public function getEmailHtml($ary,$name){



		$html='
			<!DOCTYPE html>
			<html crosspilot="">
			<head><meta charset="utf-8"/>
				<title></title>
			</head>
			<body data-autofill-highlight="false" data-gr-ext-installed="" data-new-gr-c-s-check-loaded="14.1155.0" data-new-gr-c-s-loaded="14.1155.0">
			<p>Hello Mr/Ms. '.$ary['fisrtname']." ".$ary['lastname'].',<br />
			<br />
			Republic Tax has requested that you complete the following document:'.$name.' .<br/>
			
			<p>Please follow the link below to complete this document:</p>
			<p><a href="'.$ary['link'].'">'.$ary['link'].'</a></p>
		
			<p>Warm regards,</p>

			<p>
			Republic Tax
			</p></body>
			</html>
			';
		return $html;
	}
	public function getSMSConent($firstname,$lastname,$url){
		//$str="Hi ".$firstname." ".$lastname.", please visit ".$url." to start your republic tax prep organizer.";
		$str="Hi ".$firstname." ".$lastname.", Republic Tax has sent you a document. Please review this link here: ".$url." ";
		return $str;
	}
	
}
