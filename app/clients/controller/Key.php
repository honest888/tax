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
use DateTime;

class Key extends Base
{
    protected function initialize()
    {
        parent::initialize();
		$upload_pname_ary=array('page15-1'=>'w2','page15-12'=>'ssa_rrb_1099','page15-10'=>'form_2439','page15-11'=>'1099_r','page15-13-11-0'=>'1099_misc','page15-13-11-1'=>'1099_misc','page15-13-11-2'=>'1099_misc','page15-13-11-3'=>'1099_misc','page15-13-11-4'=>'1099_misc','page15-13-15-0'=>'1098','page15-13-15-1'=>'1098','page15-13-15-2'=>'1098','page15-13-15-3'=>'1098','page15-13-15-4'=>'1098','page15-13-17-0'=>'assets_purchased_business');
         $this->upload_pname_ary=$upload_pname_ary;
         View::assign('upload_pname_ary', $upload_pname_ary);
    }
    public function one(){
    	$CASEIDD=10002;
    	//$response=getCrmApi($CASEIDD,"file");
    	//print_r(json_decode($response['MiscXML'],true));
    	
    	//print_r($response);exit();
		//$accinfo=getCrmApi($CASEIDD,"account");
		//print_r($accinfo);exit();
		//$caseinfo=getCrmApi($CASEIDD,"info");
		//print_r($caseinfo['MiscXML']);
		//unset($caseinfo['MiscXML']);
		//print_r($caseinfo);
		//exit();
		//$paymentinfo=getCrmApi($CASEIDD,"schedule");
		//print_r($paymentinfo);exit();
		//$invoice=getCrmApi($CASEIDD,"invoice");
		//print_r($invoice);exit();
		//$SERVICESINFO=getCrmApi($CASEIDD,"service");
		//print_r($SERVICESINFO);exit();
		//$pay=getCrmApi($CASEIDD,"payment");
		//print_r($pay);exit();
		
        return view();
    }
	public function index(){
	    $str=$_SERVER['REQUEST_URI'];
        $key=str_replace("/clients/free/",'',$str);
        $key=str_replace("/clients/key/",'',$str);
        $map['link']=$key;
		//$map['plan']="dependent";
        $res=DB::name('users_clients')->where($map)->field('id,plan')->find();
		
		if($res){
			  //search from contract
			$sres=DB::name('contract')->where('keyval',$res['plan'])->find();
			if($sres){
				//get table 
				//echo $sres['tablename']."<br>";
				$tableName=substr($sres['tablename'],3); 
				$tableres=DB::name($tableName)->where('user_client_id',$res['id'])->find();
				if($tableres){
					//get all the field
					$template = file_get_contents("/var/www/vhosts/taxprep.republictax.com/httpdocs/public".$sres['filepath']);
					$fields=Db::getFields($sres['tablename']);
				    $signary=array();
				     $i=0;

					foreach($fields as $key=>$val){
						//echo $tableres[$val['name']];
						//echo $tableres[$val['name']]."<br>";
						//$find='{{' . $val['name'] . '}}';
						if($val['name']=='pin'){
							$pinary=str_split($tableres[$val['name']]);
							View::assign('pinary', $pinary);	
						}
						if($val['name']=="currentdate"){
							$curdate=date("m/d/Y");
							$template = str_replace('{{' . $val['name'] . '}}',$curdate, $template);	
							continue;
						}
						if (substr($val['name'], 0, 4) === 'sign') {//嵌入签名
							//echo $val['name']."<br>";
							if($tableres[$val['name']]){
                                $imgpath=$tableres[$val['name']];
							}else{
                                $imgpath='/static/images/sign2.png';
							}
							$signary[$val['name']]=$imgpath;//htmlel1s","htmlel1fs","htmlel1srn","htmlel1ss","htmlel1m","htmlel1b","htmlel2","htmlccx","htmlccx2","htmlotp","htmlxtp","htmlwai","htmltpc","html8879","html8879sp"
							if($res['plan']=='taxotp'||$res['plan']=='htmlel1s'||$res['plan']=='htmlel1fs'||$res['plan']=='htmlel1srn'||$res['plan']=='htmlel1ss'||$res['plan']=='htmlel1m'||$res['plan']=='htmlel1b'||$res['plan']=='htmlel2'||$res['plan']=='htmlccx'||$res['plan']=='htmlccx2'||$res['plan']=='htmlotp'||$res['plan']=='htmlxtp'||$res['plan']=='htmlwai'||$res['plan']=='html8879'||$res['plan']=='html8879sp'||$res['plan']=='htmltpc'){
								$style="width:200;";
							}else{
								$style="";
							}
                           $html='<div id="'.$val['name'].'" data-src='.$val['name'].' data-link="https://taxprep.republictax.com/clients/signfree?link='.$map['link'].'&f='.$val['name'].'"><img src="'.$imgpath.'" id="'.$val['name'].'img" style="'.$style.'" ></div>';
                           $template = str_replace('{{' . $val['name'] . '}}', $html, $template);
						}elseif(strpos($val['name'],'date') !== false||preg_match('/^\d{4}-\d{2}-\d{2}T/',$tableres[$val['name']])){//当包含date时 
						    if (preg_match('/^\d{4}-\d{2}-\d{2}T/',$tableres[$val['name']])) {  //1990-01-01T00:00:00
									$pdate=substr($tableres[$val['name']],0,10);  
									$dateObj = new DateTime($pdate);
									$pdate = $dateObj->format('m/d/Y');
									$template = str_replace('{{' . $val['name'] . '}}',$pdate, $template);										
							}else{
							   $template = str_replace('{{' . $val['name'] . '}}', $tableres[$val['name']], $template);		
							}
							
						}else{
							if($val['name']=="SPM"||$val['name']=="SN"||$val['name']=="IN"||$val['name']=="SI"){
								//$template = str_replace('{{' . $val['name'] . '}}', "<textarea cols='260' rows='27' style='border:none;'>".$tableres[$val['name']]."</textarea>", $template);	
								
								if($res['plan']=='taxotp'||$res['plan']=='htmlel1s'||$res['plan']=='htmlel1fs'||$res['plan']=='htmlel1srn'||$res['plan']=='htmlel1ss'||$res['plan']=='htmlel1m'||$res['plan']=='htmlel1b'||$res['plan']=='htmlel2'||$res['plan']=='htmlccx'||$res['plan']=='htmlccx2'||$res['plan']=='htmlotp'||$res['plan']=='htmlxtp'||$res['plan']=='htmlwai'||$res['plan']=='html8879'||$res['plan']=='html8879sp'||$res['plan']=='htmltpc'){
									//$str2=explode("\r\n",$tableres[$val['name']]);
									//$str2=str_replace("\r\n", "<br>", $tableres[$val['name']]);
									$str2=nl2br($tableres[$val['name']]);
									$template = str_replace('{{' . $val['name'] . '}}', $str2, $template);
								}else{
									$str2=explode("\r\n",$tableres[$val['name']]);
									
								  // $template = str_replace('{{' . $val['name'] . '}}', " <textarea cols='120' rows='17' style='border:none;font-size:26px;'>".$tableres[$val['name']]."</textarea>", $template);
								   $template = str_replace('{{' . $val['name'] . '}}',$tableres[$val['name']], $template);	
								}
							}else{
								if (substr($val['name'], 0, 5) === 'input') {
									$i++;
									if($res['plan']=='taxotp'||$res['plan']=='htmlel1s'||$res['plan']=='htmlel1fs'||$res['plan']=='htmlel1srn'||$res['plan']=='htmlel1ss'||$res['plan']=='htmlel1m'||$res['plan']=='htmlel1b'||$res['plan']=='htmlel2'||$res['plan']=='htmlccx'||$res['plan']=='htmlccx2'||$res['plan']=='htmlotp'||$res['plan']=='htmlxtp'||$res['plan']=='htmlwai'||$res['plan']=='html8879'||$res['plan']=='html8879sp'||$res['plan']=='htmltpc'){
										if($val['name']=="inputSPM"){
											$template = str_replace('{{' . $val['name'] . '}}', '<textarea id="a'.$i.'" name="'.$val['name'].'" data-src="'.$val['name'].'" style="border: none;background: #fff184;font-size: 14px;">'.$tableres[$val['name']].'</textarea>', $template);
										}else{
											$template = str_replace('{{' . $val['name'] . '}}', '<input type="text" id="a'.$i.'" name="'.$val['name'].'" value="'.$tableres[$val['name']].'"  data-src="'.$val['name'].'" style="border: none;background: #fff184;font-size: 14px;">', $template);
										}
										
									}else{
										$template = str_replace('{{' . $val['name'] . '}}', '<input type="text" id="a'.$i.'" name="'.$val['name'].'" value="'.$tableres[$val['name']].'"  data-src="'.$val['name'].'" style="height: 60px;width: 400px; border: none;background: #fff184;font-size: 22px;">', $template);

									}
									
								}else{
									if ($val['name']== 'curd_3'||$val['name']== 'curd_5') {//计算currendate+3
										if ($val['name']== 'curd_3'){
											$ttt=date("Y")+3;
										}
										if ($val['name']== 'curd_5'){
											$ttt=date("Y")+5;
										}
	                                       $template = str_replace('{{' . $val['name'] . '}}', $ttt, $template);	
									}else{
										$template = str_replace('{{' . $val['name'] . '}}', $tableres[$val['name']], $template);
									}
										
								
								}
									
							}
						   
						}
					}	
				   $tableres['link']=$map['link'];
				   $tableres['plan']=$res['plan'];
                   View::assign('template', $template);	
                   View::assign('detail', $tableres);	
                   View::assign('signary', $signary);	
                    View::assign('loopi', $i);
                  // print_r($signary);
				}else{
					die('No contract for this client');
				}
			}else{
				exit('No contract');
			}
		}else{
			exit('Invalid link');
		}	
		 return view();
	}
	public function preview(){
	   // $str=$_SERVER['REQUEST_URI'];
	   // echo $str;
        //$key=str_replace("/clients/free/",'',$str);
        $data=input('get.');
       // print_r($data);
        $map['plan']=$data['pl'];
        $map['link']='preview';
		//$map['plan']="dependent";
        $res=DB::name('users_clients')->where($map)->field('id,plan')->find();
  
		if($res){
			  //search from contract
			$sres=DB::name('contract')->where('keyval',$res['plan'])->find();
			if($sres){
				//get table 
				$tableName=substr($sres['tablename'],3); 
				$tableres=DB::name($tableName)->where('user_client_id',$res['id'])->find();
				if($tableres){
					//get all the field
					$template = file_get_contents("/var/www/vhosts/taxprep.republictax.com/httpdocs/public".$sres['filepath']);
					$fields=Db::getFields($sres['tablename']);

					foreach($fields as $key=>$val){
						//echo $tableres[$val['name']]."<br>";
						//$find='{{' . $val['name'] . '}}';
						if($val['name']=='pin'){
							$pinary=str_split($tableres[$val['name']]);
							View::assign('pinary', $pinary);	
						}
						if($val['name']=="currentdate"){
							$curdate=date("m/d/Y");
							$template = str_replace('{{' . $val['name'] . '}}',$curdate, $template);	
							continue;
						}
						if (substr($val['name'], 0, 4) === 'sign') {//嵌入签名
							$imgpath='/static/images/sign2aaa.png';
							$signary[$val['name']]=$imgpath;//htmlel1s","htmlel1fs","htmlel1srn","htmlel1ss","htmlel1m","htmlel1b","htmlel2","htmlccx","htmlccx2","htmlotp","htmlxtp","htmlwai","htmltpc","html8879","html8879sp"
							if($res['plan']=='taxotp'||$res['plan']=='htmlel1s'||$res['plan']=='htmlel1fs'||$res['plan']=='htmlel1srn'||$res['plan']=='htmlel1ss'||$res['plan']=='htmlel1m'||$res['plan']=='htmlel1b'||$res['plan']=='htmlel2'||$res['plan']=='htmlccx'||$res['plan']=='htmlccx2'||$res['plan']=='htmlotp'||$res['plan']=='htmlxtp'||$res['plan']=='htmlwai'||$res['plan']=='html8879'||$res['plan']=='html8879sp'||$res['plan']=='htmltpc'){
								$style="width:200;";
							}else{
								$style="";
							}
                           $html='<div id="'.$val['name'].'" data-src='.$val['name'].' data-link="https://taxprep.republictax.com/clients/signfree?link='.$map['link'].'&f='.$val['name'].'"><img src="'.$imgpath.'" id="'.$val['name'].'img" style="'.$style.'" ></div>';
                           $template = str_replace('{{' . $val['name'] . '}}', $html, $template);
						}elseif(strpos($val['name'],'date') !== false||preg_match('/^\d{4}-\d{2}-\d{2}T/',$tableres[$val['name']])){//当包含date时 
						    if (preg_match('/^\d{4}-\d{2}-\d{2}T/',$tableres[$val['name']])) {  //1990-01-01T00:00:00
									$pdate=substr($tableres[$val['name']],0,10);  
									$dateObj = new DateTime($pdate);
									$pdate = $dateObj->format('m/d/Y');
									$template = str_replace('{{' . $val['name'] . '}}',$pdate, $template);										
							}else{
							   $template = str_replace('{{' . $val['name'] . '}}', $tableres[$val['name']], $template);		
							}
							
						}else{
							if($val['name']=="SPM"||$val['name']=="SN"||$val['name']=="IN"||$val['name']=="SI"){
								//$template = str_replace('{{' . $val['name'] . '}}', "<textarea cols='260' rows='27' style='border:none;'>".$tableres[$val['name']]."</textarea>", $template);	
								
								if($res['plan']=='taxotp'||$res['plan']=='htmlel1s'||$res['plan']=='htmlel1fs'||$res['plan']=='htmlel1srn'||$res['plan']=='htmlel1ss'||$res['plan']=='htmlel1m'||$res['plan']=='htmlel1b'||$res['plan']=='htmlel2'||$res['plan']=='htmlccx'||$res['plan']=='htmlccx2'||$res['plan']=='htmlotp'||$res['plan']=='htmlxtp'||$res['plan']=='htmlwai'||$res['plan']=='html8879'||$res['plan']=='html8879sp'||$res['plan']=='htmltpc'){
									//$str2=explode("\r\n",$tableres[$val['name']]);
									$str2=str_replace("\r\n", "<br>", $tableres[$val['name']]);
									$template = str_replace('{{' . $val['name'] . '}}', $str2, $template);
								}else{
									$str2=explode("\r\n",$tableres[$val['name']]);
									
								  // $template = str_replace('{{' . $val['name'] . '}}', " <textarea cols='120' rows='17' style='border:none;font-size:26px;'>".$tableres[$val['name']]."</textarea>", $template);
								   $template = str_replace('{{' . $val['name'] . '}}',$tableres[$val['name']], $template);	
								}
							}else{
								if (substr($val['name'], 0, 5) === 'input') {
									$i++;
									if($res['plan']=='taxotp'||$res['plan']=='htmlel1s'||$res['plan']=='htmlel1fs'||$res['plan']=='htmlel1srn'||$res['plan']=='htmlel1ss'||$res['plan']=='htmlel1m'||$res['plan']=='htmlel1b'||$res['plan']=='htmlel2'||$res['plan']=='htmlccx'||$res['plan']=='htmlccx2'||$res['plan']=='htmlotp'||$res['plan']=='htmlxtp'||$res['plan']=='htmlwai'||$res['plan']=='html8879'||$res['plan']=='html8879sp'||$res['plan']=='htmltpc'){
										if($val['name']=="inputSPM"){
											$template = str_replace('{{' . $val['name'] . '}}', '<textarea id="a'.$i.'" name="'.$val['name'].'" data-src="'.$val['name'].'" style="border: none;background: #fff184;font-size: 14px;">'.$tableres[$val['name']].'</textarea>', $template);
										}else{
											$template = str_replace('{{' . $val['name'] . '}}', '<input type="text" id="a'.$i.'" name="'.$val['name'].'" value="'.$tableres[$val['name']].'"  data-src="'.$val['name'].'" style="border: none;background: #fff184;font-size: 14px;">', $template);
										}
									}else{
										$template = str_replace('{{' . $val['name'] . '}}', '<input type="text" id="a'.$i.'" name="'.$val['name'].'" value="'.$tableres[$val['name']].'"  data-src="'.$val['name'].'" style="height: 60px;width: 400px; border: none;background: #fff184;font-size: 22px;">', $template);

									}
									
								}else{
									if ($val['name']== 'curd_3'||$val['name']== 'curd_5') {//计算currendate+3
										if ($val['name']== 'curd_3'){
											$ttt=date("Y")+3;
										}
										if ($val['name']== 'curd_5'){
											$ttt=date("Y")+5;
										}
	                                       $template = str_replace('{{' . $val['name'] . '}}', $ttt, $template);	
									}else{
										$template = str_replace('{{' . $val['name'] . '}}', $tableres[$val['name']], $template);
									}
										
								
								}
									
							}
						   
						}
					}	
				   $tableres['link']=$map['link'];

                   View::assign('template', $template);	
                   View::assign('detail', $tableres);	
                   
				}else{
					die('No contract for this client');
				}
			}else{
				exit('No contract');
			}
		}else{
			exit('Invalid link');
		}	
		 return view('index');
	}
	public function signpage(){
		$users=session('user');
        $islogin=0;
        if($users){
            $islogin=1;
        }
    
       // View::assign('islogin', $islogin);
        $link = $_GET['link'];
        $field = $_GET['f'];
        $map['link']=$link;		
        $res=DB::name('users_clients')->where($map)->find();
          
        if($res){
			//$mm['user_client_id']=$res['id'];
			//$profitloss=DB::name('user_financial')->where($mm)->find();
		//	$rel="https://".$_SERVER['HTTP_HOST'].'/clients/free/'.$link;
			$sres=DB::name('contract')->where('keyval',$res['plan'])->find();
			if($sres){
				$tableName=substr($sres['tablename'],3); 
				$tableres=DB::name($tableName)->where('user_client_id',$res['id'])->find();
			}
			$tableres['link']=$res['link'];
			View::assign('detail', $tableres);
			View::assign('field', $field);
        }else{
          die('Something goes wrong');
        }  
       
       return view();
	}
	 public function save(){
        if (IS_POST){
            $data=input('post.');

            if(!$data['fieldname']){
                return json(['code'=>0]);  
            }
            $save[$data['fieldname']]=$data['fieldvalue'];
            if($data['fieldvalue']===0){
            	
            }else{
            	if ($data['fieldvalue']=='') {            		
            		$save[$data['fieldname']]=Db::raw('NULL');
            	}
            }

            $save[$data['fieldname']]=$data['fieldvalue'];
            //$save['curfield']=$data['fieldname'];
            //$save['updatetime']=time();
		
            $map['link']=$data['link'];
            $map['plan']=$data['plan'];
        
            $res=DB::name('users_clients')->where($map)->find();
           
            if($res){
            	$save2['updatetime']=time();
                $save2['status']=1;
                DB::name('users_clients')->where('id',$res['id'])->save($save2);//更新users_clients状态 
               
				$sres=DB::name('contract')->where('keyval',$data['plan'])->find();
				if($sres){
					$tableName=substr($sres['tablename'],3); 
					$tableres=DB::name($tableName)->where('user_client_id',$res['id'])->find();
					if($tableres){
						DB::name($tableName)->where('user_client_id',$res['id'])->save($save);
						 return json(['code'=>1,'msg'=>'']); 
					}else{
						return json(['code'=>0,'msg'=>'Invalid contract']);  
					}
				}else{
					return json(['code'=>0,'msg'=>'Error']);  
				}
			         
            }else{
               return json(['code'=>0]);  
            }
            //保存字段
            //保存相应的字段，修改最后编辑字段，修改最后保存时间  通过最后保存字段，获得在哪一页
        }
    }
	
    public function signaturet(){
      
       if (IS_POST) {
        //生成签名图片
        $signature = $_POST['signature'];
        $field = $_POST['f'];
        $data = input('post.');
        if(!$data['link']){
           return json(['code'=>0,'msg'=>'Error']);
        }
        $map['link']=$data['link'];
		//$map['plan']="cm";
		$link=$data['link'];
        $res=DB::name('users_clients')->where($map)->find();
        if($res){
			$sres=DB::name('contract')->where('keyval',$res['plan'])->find();
			if($sres){
				$tableName=substr($sres['tablename'],3); 
				$profitloss=DB::name($tableName)->where('user_client_id',$res['id'])->find();
				$sign_name= uniqid() . '.png';
				$targetDirectory='/var/www/vhosts/taxprep.republictax.com/httpdocs/public/upload/free/'.$link."/signaturet/";
	            $filename_sign = '/var/www/vhosts/taxprep.republictax.com/httpdocs/public/upload/free/'.$link."/signaturet/".$sign_name;//签名的路径
				if (!is_dir($targetDirectory)) {
				   if (!mkdir($targetDirectory, 0755, true)) {
					   return false; 
				   }
				}
				
	            $sign_path='/upload/free/'.$link.'/signaturet/' . $sign_name;//存储路径
				$temp_Path='/var/www/vhosts/taxprep.republictax.com/httpdocs/public/'.$profitloss[$field];
				file_put_contents($filename_sign, base64_decode(explode(',', $signature)[1]));//存签名照片
				if(isset($data['rotate'])&&$data['rotate']==1){              
					roate($filename_sign);
				}
				
				$old_sign_con='/var/www/vhosts/taxprep.republictax.com/httpdocs/public/'.$profitloss[$field];
	            if (is_file($old_sign_con)&&file_exists($old_sign_con)) {
	                   unlink($old_sign_con);
	              }
				 $new[$field]=$sign_path;
				 //将其他签名也同步更新
				 //得到所有签名字段
				 $fields=Db::getFields($sres['tablename']);
				 foreach($fields as $key=>$val){
				 	if (substr($val['name'], 0, 4) === 'sign') {
				 		$new[$val['name']]=$sign_path;
				 	}
				 }
	             Db::name($tableName)->where('id',$profitloss['id'])->update($new);
				 return json(['code'=>0,'img'=>$new[$field]]);
			}
			
		}else{
			return json(['code'=>0,'msg'=>'Error Link']);
		}
       }
    }
     public function signature(){      
       if (IS_POST) {
        //生成签名图片
        $signature = $_POST['signature'];
       
        $data = input('post.');
        if(!$data['link']){
           return json(['code'=>0,'msg'=>'Error']);
        }
        $map['link']=$data['link'];
		$link=$data['link'];
        $res=DB::name('users_clients')->where($map)->find();
        if($res){
			$mm['user_client_id']=$res['id'];
			$profitloss=DB::name('user_profit_loss')->where($mm)->find();
			$sign_name= uniqid() . '.png';
			$targetDirectory='/var/www/vhosts/taxprep.republictax.com/httpdocs/public/upload/profitloss/'.$link."/signature/";
            $filename_sign = '/var/www/vhosts/taxprep.republictax.com/httpdocs/public/upload/profitloss/'.$link."/signature/".$sign_name;//签名的路径
			if (!is_dir($targetDirectory)) {
			   if (!mkdir($targetDirectory, 0755, true)) {
				   return false; 
			   }
			}
			
            $sign_path='/upload/profitloss/'.$link.'/signature/' . $sign_name;//存储路径
			$temp_Path='/var/www/vhosts/taxprep.republictax.com/httpdocs/public/'.$profitloss['signimage'];
			file_put_contents($filename_sign, base64_decode(explode(',', $signature)[1]));//存签名照片
			if(isset($data['rotate'])&&$data['rotate']==1){              
				roate($filename_sign);
			}
			
			$old_sign_con='/var/www/vhosts/taxprep.republictax.com/httpdocs/public/'.$profitloss['signimage'];
            if (is_file($old_sign_con)&&file_exists($old_sign_con)) {
                   unlink($old_sign_con);
              }
			 $new['signimage']=$sign_path;
             Db::name('user_profit_loss')->where('id',$profitloss['id'])->update($new);
			 return json(['code'=>0,'img'=>$new['signimage']]);
		}else{
			return json(['code'=>0,'msg'=>'Error Link']);
		}
       }
    }
    public function signcheck(){
    	//return json(['code'=>1,'img'=>'']);
      //查看签名是否存在
       if (IS_POST) {        
        $data = input('post.');
        if(!$data['link']){
           return json(['code'=>0,'msg'=>'Error']);
        }
        $map['link']=$data['link'];
        $res=DB::name('users_clients')->where($map)->find();
          if($res){
			$sres=DB::name('contract')->where('keyval',$res['plan'])->find();
			if($sres){
				//get table 
				$tableName=substr($sres['tablename'],3); 
				$tableres=DB::name($tableName)->where('user_client_id',$res['id'])->find();
				if($tableres){
					    $tableName=str_replace("tp_",'',$sres['tablename']);
						$tableName=$sres['tablename'];
						try {
							Db::table($tableName)->find();
							$fields=Db::getFields($tableName);
							foreach($fields as $key=>$val){
								if (substr($val['name'], 0, 4) === 'sign') {//嵌入签名
									$old_sign_cont='/var/www/vhosts/taxprep.republictax.com/httpdocs/public/'.$tableres[$val['name']];
									if (!(is_file($old_sign_cont)&&file_exists($old_sign_cont))) {
						                   return json(['code'=>0,'msg'=>$val['name'].' signature is empty!']);
						            }
								}
							}
						} catch (DataNotFoundException $e) {
							 return json(['code'=>0,'msg'=>"{$tableName} doesn't exist"]);
						} catch (ModelNotFoundException $e) {
							 return json(['code'=>0,'msg'=>"{$tableName} doesn't exist"]);
						}
					//get all the field
			
			return json(['code'=>1,'img'=>'']);
		   }
		  }
		}else{
			return json(['code'=>0,'msg'=>'Error Link']);
		}
       }
    }
    public function submit(){
        //复制完后生成pdf,发送后删除 
		$link=$_POST['link'];
		
		$map['link']=$_POST['link'];
		//$map['plan']='cm';
        $res=DB::name('users_clients')->where($map)->field('id,caseid,plan')->find();
     
        if($res){
			$sres=DB::name('contract')->where('keyval',$res['plan'])->find();
			if($sres){
				//get table 
				$tableName=substr($sres['tablename'],3); 
				$tableres=DB::name($tableName)->where('user_client_id',$res['id'])->find();
				if($tableres){
					//get all the field
					$template = file_get_contents("/var/www/vhosts/taxprep.republictax.com/httpdocs/public".$sres['filepath']);
					$fields=Db::getFields($sres['tablename']);
				    $signary=array();
				    $template = str_replace('class="innerpage"','', $template);	
				    $template = str_replace('class="pagebreak"','style="margin-bottom:50px;"', $template);	
					foreach($fields as $key=>$val){
						if($val['name']=="currentdate"){
							$curdate=date("m/d/Y");
							$template = str_replace('{{' . $val['name'] . '}}',$curdate, $template);	
							continue;
						}
						if (substr($val['name'], 0, 4) === 'sign') {//嵌入签名
							if($tableres[$val['name']]){
                                $imgpath='/var/www/vhosts/taxprep.republictax.com/httpdocs/public'.$tableres[$val['name']];
							}else{
                                $imgpath='/var/www/vhosts/taxprep.republictax.com/httpdocs/public/static/images/sign2.png';
							}
							if($res['plan']=='taxotp'||$res['plan']=='htmlel1s'||$res['plan']=='htmlel1fs'||$res['plan']=='htmlel1srn'||$res['plan']=='htmlel1ss'||$res['plan']=='htmlel1m'||$res['plan']=='htmlel1b'||$res['plan']=='htmlel2'||$res['plan']=='htmlccx'||$res['plan']=='htmlccx2'||$res['plan']=='htmlotp'||$res['plan']=='htmlxtp'||$res['plan']=='htmlwai'||$res['plan']=='html8879'||$res['plan']=='html8879sp'||$res['plan']=='htmltpc'){
								$style="width:200;";
							}else{
								$style="";
							}
							$signary[$val['name']]=$imgpath;
                           $html='<img src="'.$imgpath.'" style="'.$style.'">';
                           $template = str_replace('{{' . $val['name'] . '}}', $html, $template);
						}elseif(strpos($val['name'],'date') !== false||preg_match('/^\d{4}-\d{2}-\d{2}T/',$tableres[$val['name']])){//当包含date时 
						    if (preg_match('/^\d{4}-\d{2}-\d{2}T/',$tableres[$val['name']])) {  //1990-01-01T00:00:00
									$pdate=substr($tableres[$val['name']],0,10);  
									$dateObj = new DateTime($pdate);
									$pdate = $dateObj->format('m/d/Y');
									$template = str_replace('{{' . $val['name'] . '}}',$pdate, $template);										
							}else{
							   $template = str_replace('{{' . $val['name'] . '}}', $tableres[$val['name']], $template);		
							}
							
						}else{
						   if($val['name']=="SPM"||$val['name']=="SN"||$val['name']=="IN"||$val['name']=="SI"||$val['name']=="inputSPM"){

						   	   //计算行数
						   
								//$template = str_replace('{{' . $val['name'] . '}}', $tableres[$val['name']], $template);
                                    $str2=nl2br($tableres[$val['name']]);
									//$str2=nl2br($tableres[$val['name']]);
									$template = str_replace('{{' . $val['name'] . '}}',$str2, $template);

								/*if($res['plan']=='taxotp'||$res['plan']=='htmlel1s'||$res['plan']=='htmlel1fs'||$res['plan']=='htmlel1srn'||$res['plan']=='htmlel1ss'||$res['plan']=='htmlel1m'||$res['plan']=='htmlel1b'||$res['plan']=='htmlel2'||$res['plan']=='htmlccx'||$res['plan']=='htmlccx2'||$res['plan']=='htmlotp'||$res['plan']=='htmlxtp'||$res['plan']=='htmlwai'||$res['plan']=='html8879'||$res['plan']=='html8879sp'||$res['plan']=='htmltpc'){
									//$template = str_replace('{{' . $val['name'] . '}}', $tableres[$val['name']], $template);
									$str2=str_replace("\r\n", "<p></p>", $tableres[$val['name']]);
									//$str2=nl2br($tableres[$val['name']]);
									$template = str_replace('{{' . $val['name'] . '}}',$str2, $template);

								}else{
									if($counter<=15){
                                     $template = str_replace('{{' . $val['name'] . '}}', " <textarea cols='120' rows='11' style='border:none;font-size:26px;'>".$tableres[$val['name']]."</textarea>", $template);
									}else{
									 $template = str_replace('{{' . $val['name'] . '}}', " <textarea cols='120' rows='6' style='border:none;font-size:26px;'>".$tableres[$val['name']]."</textarea>", $template);	
									}
								}	*/
								//nl2br(htmlspecialchars(urldecode($tableres[$val['name']])))
							}else{
								if ($val['name']== 'curd_3'||$val['name']== 'curd_5') {//计算currendate+3
										if ($val['name']== 'curd_3'){
											$ttt=date("Y")+3;
										}
										if ($val['name']== 'curd_5'){
											$ttt=date("Y")+5;
										}
	                                       $template = str_replace('{{' . $val['name'] . '}}', $ttt, $template);	
								}else{
									$template = str_replace('{{' . $val['name'] . '}}', $tableres[$val['name']], $template);	
								}
								
							}
						}
					}	
				   $htmlContent=$template;
				   
				   if($htmlContent){
				   	   $htmlContent = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $htmlContent);
				   	  // $htmlContent = preg_replace('/@font-face\s*\{[^}]*\}/', '', $htmlContent);//去除font face
                      //echo $cleanHtml;
						// 设置文件名和路径
						$filename = 'temp.html';
						$filepath = '/var/www/vhosts/taxprep.republictax.com/httpdocs/public/upload/free/'.$link.'/pdf/';
						
						// 确保目录存在
						if (!file_exists($filepath)) {
							mkdir($filepath, 0755, true);
						}
						// 将HTML内容写入文件
					    file_put_contents($filepath . $filename, $htmlContent);
					
						$htmlFilePath = '/var/www/vhosts/taxprep.republictax.com/httpdocs/public/upload/free/'.$link.'/pdf/'.$filename;
						$fname22=$sres['keyval'].".pdf"; 
						$pdfFilePath = '/var/www/vhosts/taxprep.republictax.com/httpdocs/public/upload/free/'.$link.'/pdf/'.$fname22;
						$command = "wkhtmltopdf --enable-local-file-access --lowquality --disable-javascript $filepath$filename $pdfFilePath";
					
						exec($command, $output, $returnVar);
			          
						if ($returnVar === 0) {
							$ttag=0;
							$judge = $this->isFileLargerThan($pdfFilePath, 3.4);
							if ($judge) {
								//如果文件过大，以链接形式发送
								$htmlContent = '<div><a href="https://taxprep.republictax.com/upload/free/'.$link.'/pdf/'.$fname22.'" target="_blank">Click here to view and download</a></div>';//去除font face
								$filename = 'temp.html';
								$filepath = '/var/www/vhosts/taxprep.republictax.com/httpdocs/public/upload/free/'.$link.'/pdf/';
								
								// 确保目录存在
								if (!file_exists($filepath)) {
									mkdir($filepath, 0755, true);
								}
								// 将HTML内容写入文件
							    file_put_contents($filepath . $filename, $htmlContent);
							
								$htmlFilePath = '/var/www/vhosts/taxprep.republictax.com/httpdocs/public/upload/free/'.$link.'/pdf/'.$filename;
								$pdfFilePath2 = '/var/www/vhosts/taxprep.republictax.com/httpdocs/public/upload/free/'.$link.'/pdf/out_download.pdf';
								$command = "wkhtmltopdf --enable-local-file-access --lowquality $filepath$filename $pdfFilePath2";
								exec($command, $output, $returnVar2);
								if ($returnVar2 === 0) {
									$ttag=1;
								}
							}
							
							//send caseid
							$CASEID=$res['caseid'];
						   //  $CASEID='1000111';
							$urrrrrl="https://ntcg.irslogics.com/publicapi/documents/casedocument?apikey=f6bf9800223b44a5ac6948ee6556225d&CaseID=".$CASEID."&Content-Type=application/pdf";
							$location=$pdfFilePath;
							if($ttag==1) $location=$pdfFilePath2;
							  $curl = curl_init();
							  curl_setopt_array($curl, array(
								CURLOPT_URL => $urrrrrl,
								CURLOPT_RETURNTRANSFER => true,
								CURLOPT_ENCODING => "",
								CURLOPT_MAXREDIRS => 10,
								CURLOPT_TIMEOUT => 0,
								CURLOPT_FOLLOWLOCATION => true,
								CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
								CURLOPT_CUSTOMREQUEST => "POST",
								//CURLOPT_POSTFIELDS => array(''=> new CURLFILE($location),'apikey' => 'f6bf9800223b44a5ac6948ee6556225d','CaseID' => $CASEID),
							  ));
						      curl_setopt($curl, CURLOPT_POST, 1);
							 $fname=date("YmdHis",time());
							 $fname=$sres['keyval'].$fname.".pdf"; 
						     $curlFile = curl_file_create($location,'application/pdf',$fname);//,'RepublicTest.pdf'
							  $post = array(
								'file' => $curlFile,
								'apikey' => 'f6bf9800223b44a5ac6948ee6556225d',
								'CaseID' => $CASEID
							  );
							  curl_setopt($curl, CURLOPT_POSTFIELDS, $post);
							  $response = curl_exec($curl);
							  curl_close($curl);
							//send caseid end
							//readfile($pdfFilePath);
							if($ttag==0) {//如果不超大小需要删除
								unlink($pdfFilePath);
							    unlink($filepath.$filename);
								rmdir($filepath);								
							}
							$save['updatetime']=time();
	                        $save['status']=2;
	                        DB::name('users_clients')->where($map)->save($save);
							return json_decode($response);
							
						} else {
							return json(['status'=>0,'message'=>'failed to generate pdf file']);  
						}
				}

				}else{
					die('No contract for this client');
				}
			}
			
		}
	}
	public function compressPdf($input, $output) {
								    $command = "gs -sDEVICE=pdfwrite -dCompatibilityLevel=1.4 ".
           "-dPDFSETTINGS=/printer ".
           "-dDownsampleColorImages=true -dColorImageResolution=150 ".
           "-dDownsampleGrayImages=true -dGrayImageResolution=150 ".
           "-dDownsampleMonoImages=true -dMonoImageResolution=150 ".
           "-dAutoFilterColorImages=false ".
           "-dColorImageFilter=/DCTEncode ".
           "-sOutputFile=$input $output";
								    exec($command);
								    return filesize($output);
								}
  public function isFileLargerThan($filePath, $sizeInMB) {
    if (!file_exists($filePath) || !is_file($filePath)) {
        return false;
    }
    
    $fileSize = filesize($filePath);
    $maxSize = $sizeInMB * 1024 * 1024;
    
    return $fileSize > $maxSize;
  }
}
