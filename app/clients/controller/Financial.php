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
use think\facade\Db;
use think\facade\Filesystem;
use think\facade\View;

require_once root_path('vendor') . "PHPMailer/PHPMailer.php";
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Financial extends Base
{
    protected function initialize()
    {
        parent::initialize();
		$upload_pname_ary=array('page15-1'=>'w2','page15-12'=>'ssa_rrb_1099','page15-10'=>'form_2439','page15-11'=>'1099_r','page15-13-11-0'=>'1099_misc','page15-13-11-1'=>'1099_misc','page15-13-11-2'=>'1099_misc','page15-13-11-3'=>'1099_misc','page15-13-11-4'=>'1099_misc','page15-13-15-0'=>'1098','page15-13-15-1'=>'1098','page15-13-15-2'=>'1098','page15-13-15-3'=>'1098','page15-13-15-4'=>'1098','page15-13-17-0'=>'assets_purchased_business');
         $this->upload_pname_ary=$upload_pname_ary;
         View::assign('upload_pname_ary', $upload_pname_ary);
    }
	public function file_remove(){
		if (IS_POST) { 
		   $data = input('post.');
		   $link=$data['link'];
		    if(!$link){
			   return json(['code'=>0,'msg'=>'Error']);
			}
			$map['link']=$link;
			$map['plan']="cm";
			$res=DB::name('users_clients')->where($map)->field('id')->find();
			if(!$res){
				return json(['code'=>0,'msg'=>'Error']);
			}
			$mm['user_client_id']=$res['id'];
			$financial_res=DB::name('user_financial')->where($mm)->find();
			if(!$financial_res){
				return json(['code'=>0,'msg'=>'Erro link']);
			}
			$fieldname=$data['field'];
			if(!$data['field']){
				return json(['code'=>0,'msg'=>'Incomplete parameters']);
			}
			if($financial_res[$fieldname]){
				//if exists ,removed it 
				$old_sign_cont='/var/www/vhosts/taxprep.republictax.com/httpdocs/public'.$financial_res[$fieldname];
				if (is_file($old_sign_cont)&&file_exists($old_sign_cont)) {
					unlink($old_sign_cont);
				}
				$save[$fieldname]='';
				$save['updatetime']=time();
				$rr=DB::name('user_financial')->where($mm)->save($save);  
				return json(['code'=>1,'msg'=>$filename]);   
			}else{
				return json(['code'=>0,'msg'=>"No file was removed"]);
			}
		}
	}
	public function file_upload(){
		if (IS_POST) { 
		   $data = input('post.');
		   $link=$data['link'];
		    if(!$link){
			   return json(['code'=>0,'msg'=>'Error']);
			}
			$map['link']=$link;
			$map['plan']="cm";
			$res=DB::name('users_clients')->where($map)->field('id')->find();
			if(!$res){
				return json(['code'=>0,'msg'=>'Error']);
			}
			$mm['user_client_id']=$res['id'];
			$financial_res=DB::name('user_financial')->where($mm)->find();
			if(!$financial_res){
				return json(['code'=>0,'msg'=>'Erro link']);
			}
			$fieldname=$data['field'];
			if(!$data['field']){
				return json(['code'=>0,'msg'=>'Incomplete parameters']);
			}
		    $file = request()->file('file');
			if ($file){
				              try {
							   $result = $this->validate(//PDF, PNG, JPG, CSV, XLSX
									['file' => $file],//'jpg', 'png', 'jpg', 'pdf','csv','xlsx' //PDF, PNG, JPG, CSV, XLSX
									['file'=>'fileSize:50000000|fileExt:csv,xlsx,pdf,xls'],
									['file.fileSize' => 'Oversize','file.fileExt'=>'only support csv,pdf,xlsx']
								);
							}catch (ValidateException $e){
								$error=$e->getError();
								$values=array_values($error);
								return json(['code'=>0,'msg'=>$values[0]]);
							}
				
							     $filename= date("mdHis").$_FILES["file"]["name"];//$pagename
									//$link="kLipRhIqZ0zieWnM399TVQWeG810";
									$new_path = '/financial/'.$link."/sheets";	
									$info = Filesystem::putFile($new_path,$file,$filename,[],1);								
									if($info){
										    $fileurl = UPLOAD_PATH.$info;	
											$save[$fieldname]=$fileurl;
											$save['updatetime']=time();
											$rr=DB::name('user_financial')->where($mm)->save($save);  
										    return json(['code'=>1,'msg'=>$filename]);   
									}else{
										return json(['code'=>0,'msg'=>'Failed to save file']);
									}
			}else{
					return json(['code'=>0,'msg'=>"No file"]);
			}
		}
	}
	public function signcheck(){
      //查看签名是否存在
       if (IS_POST) {        
        $data = input('post.');
        if(!$data['link']){
           return json(['code'=>0,'msg'=>'Error']);
        }
        $map['link']=$data['link'];
		$map['plan']="cm";
		$link=$data['link'];
        $res=DB::name('users_clients')->where($map)->find();
        if($res){
			$mm['user_client_id']=$res['id'];
			$profitloss=DB::name('user_financial')->where($mm)->find();
			//$old_sign_cons='/var/www/vhosts/taxprep.republictax.com/httpdocs/public/'.$profitloss['spouse_sign'];
			$old_sign_cont='/var/www/vhosts/taxprep.republictax.com/httpdocs/public/'.$profitloss['taxpayer_sign'];
            
			if (!(is_file($old_sign_cont)&&file_exists($old_sign_cont))) {
                   return json(['code'=>0,'msg'=>'Taxpayer signature is empty!']);
            }
			/*
			if (!(is_file($old_sign_cons)&&file_exists($old_sign_cons))) {
                   return json(['code'=>0,'msg'=>'Spouse signature is empty!']);
            }*/
			return json(['code'=>1,'img'=>'']);
		}else{
			return json(['code'=>0,'msg'=>'Error Link']);
		}
       }
    }
	 public function downloadfile(){
        $link=input('get.link');
        $filename=input('get.filename');
        $pagename=input('get.pagename');
        $path='upload/financial/'.$link."/sheets/".$pagename."/".$filename;
     
        //这里需要注意该目录是否存在，并且有创建的权限
        return download($path, $filename);
      
        //这里是下载zip文件
        header("Content-Type: application/zip");
        header("Content-Transfer-Encoding: Binary");
        header("Content-Length: " . filesize($zipname));
        header("Content-Disposition: attachment; filename=\"" . basename($zipname) . "\"");
        readfile($zipname);
        exit;
    }
	public function upload(){
		return view();
	}
    //taxpayer 手机端签名
	public function signpage(){
		$users=session('user');
        $islogin=0;
        if($users){
            $islogin=1;
        }
        View::assign('islogin', $islogin);
        $link = $_GET['link'];
        $map['link']=$link;
		$map['plan']="cm";
        $res=DB::name('users_clients')->where($map)->find();
          
        if($res){
			$mm['user_client_id']=$res['id'];
			$profitloss=DB::name('user_financial')->where($mm)->find();
			
			$le=substr($res['firstname'],0,1);
			$le2=substr($res['lastname'],0,1);
			$name=$le.$le2;
			View::assign('shortname', $name); 
			$profitloss['link']=$res['link'];
			View::assign('detail', $profitloss);
        }else{
          die('Something goes wrong');
        }  
       
       return view();
	}
	//spouse 手机端签名
	public function signpage2(){
		$users=session('user');
        $islogin=0;
        if($users){
            $islogin=1;
        }
        View::assign('islogin', $islogin);
        $link = $_GET['link'];
        $map['link']=$link;
		$map['plan']="cm";
        $res=DB::name('users_clients')->where($map)->find();
          
        if($res){
			$mm['user_client_id']=$res['id'];
			$profitloss=DB::name('user_financial')->where($mm)->find();
			
			$le=substr($res['firstname'],0,1);
			$le2=substr($res['lastname'],0,1);
			$name=$le.$le2;
			View::assign('shortname', $name); 
			$profitloss['link']=$res['link'];
			View::assign('detail', $profitloss);
        }else{
          die('Something goes wrong');
        }  
       
       return view();
	}
    public function signaturet(){
      
       if (IS_POST) {
        //生成签名图片
        $signature = $_POST['signature'];
       
        $data = input('post.');
        if(!$data['link']){
           return json(['code'=>0,'msg'=>'Error']);
        }
        $map['link']=$data['link'];
		$map['plan']="cm";
		$link=$data['link'];
        $res=DB::name('users_clients')->where($map)->find();
        if($res){
			$mm['user_client_id']=$res['id'];
			$profitloss=DB::name('user_financial')->where($mm)->find();
			$sign_name= uniqid() . '.png';
			$targetDirectory='/var/www/vhosts/taxprep.republictax.com/httpdocs/public/upload/financial/'.$link."/signaturet/";
            $filename_sign = '/var/www/vhosts/taxprep.republictax.com/httpdocs/public/upload/financial/'.$link."/signaturet/".$sign_name;//签名的路径
			if (!is_dir($targetDirectory)) {
			   if (!mkdir($targetDirectory, 0755, true)) {
				   return false; 
			   }
			}
			
            $sign_path='/upload/financial/'.$link.'/signaturet/' . $sign_name;//存储路径
			$temp_Path='/var/www/vhosts/taxprep.republictax.com/httpdocs/public/'.$profitloss['taxpayer_sign'];
			file_put_contents($filename_sign, base64_decode(explode(',', $signature)[1]));//存签名照片
			if(isset($data['rotate'])&&$data['rotate']==1){              
				roate($filename_sign);
			}
			
			$old_sign_con='/var/www/vhosts/taxprep.republictax.com/httpdocs/public/'.$profitloss['taxpayer_sign'];
            if (is_file($old_sign_con)&&file_exists($old_sign_con)) {
                   unlink($old_sign_con);
              }
			 $new['taxpayer_sign']=$sign_path;
             Db::name('user_financial')->where('id',$profitloss['id'])->update($new);
			 return json(['code'=>0,'img'=>$new['taxpayer_sign']]);
		}else{
			return json(['code'=>0,'msg'=>'Error Link']);
		}
       }
    }
    public function signatures(){
      
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
			$profitloss=DB::name('user_financial')->where($mm)->find();
			$sign_name= uniqid() . '.png';
			$targetDirectory='/var/www/vhosts/taxprep.republictax.com/httpdocs/public/upload/financial/'.$link."/signatures/";
            $filename_sign = '/var/www/vhosts/taxprep.republictax.com/httpdocs/public/upload/financial/'.$link."/signatures/".$sign_name;//签名的路径
			if (!is_dir($targetDirectory)) {
			   if (!mkdir($targetDirectory, 0755, true)) {
				   return false; 
			   }
			}
			
            $sign_path='/upload/financial/'.$link.'/signatures/' . $sign_name;//存储路径
			$temp_Path='/var/www/vhosts/taxprep.republictax.com/httpdocs/public/'.$profitloss['spouse_sign'];
			file_put_contents($filename_sign, base64_decode(explode(',', $signature)[1]));//存签名照片
			if(isset($data['rotate'])&&$data['rotate']==1){              
				roate($filename_sign);
			}
			
			$old_sign_con='/var/www/vhosts/taxprep.republictax.com/httpdocs/public/'.$profitloss['spouse_sign'];
            if (is_file($old_sign_con)&&file_exists($old_sign_con)) {
                   unlink($old_sign_con);
              }
			 $new['spouse_sign']=$sign_path;
             Db::name('user_financial')->where('id',$profitloss['id'])->update($new);
			 return json(['code'=>0,'img'=>$new['spouse_sign']]);
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
			$profitloss=DB::name('user_financial')->where($mm)->find();
			$sign_name= uniqid() . '.png';
			$targetDirectory='/var/www/vhosts/taxprep.republictax.com/httpdocs/public/upload/financial/'.$link."/signaturet/";
            $filename_sign = '/var/www/vhosts/taxprep.republictax.com/httpdocs/public/upload/financial/'.$link."/signaturet/".$sign_name;//签名的路径
			if (!is_dir($targetDirectory)) {
			   if (!mkdir($targetDirectory, 0755, true)) {
				   return false; 
			   }
			}
			
            $sign_path='/upload/financial/'.$link.'/signaturet/' . $sign_name;//存储路径
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
             Db::name('user_financial')->where('id',$profitloss['id'])->update($new);
			 return json(['code'=>0,'img'=>$new['signimage']]);
		}else{
			return json(['code'=>0,'msg'=>'Error Link']);
		}
       }
    }
	
	public function unlock(){
        if(IS_POST){
           $data=input('post.');
           $map['link']=$data['link'];
           $res0=DB::name('users_clients')->where($map)->find();
           if($res0){
               $new['status']=1;
               DB::name('users_clients')->where($map)->save($new);
              return json(['code'=>1,'data'=>'']); 
           }else{
              return json(['code'=>0,'msg'=>1]); 
           }
       }
    }
	public function submit(){
      // $link="k0ArSMziYDVZ8IwixdW3fsAhumj5";
        
        //复制完后生成pdf,发送后删除 
		$link=$_POST['link'];
		
		$map['link']=$_POST['link'];
		$map['plan']='cm';
        $res=DB::name('users_clients')->where($map)->field('id,caseid')->find();
     
        if($res){
			$path='upload/cm/'.$link;
			//先挪动文件至temp目录下，先创建当前目录下的temp目录
			$targetDirectory ='/var/www/vhosts/taxprep.republictax.com/httpdocs/public/upload/cm/'.$link."/temp/";
			
			if (!is_dir($targetDirectory)) {
				   // 尝试创建目录
			   if (!mkdir($targetDirectory, 0755, true)) {
				   return false; 
			   }
			}
			$mm['user_client_id']=$res['id'];
			$detail=DB::name('user_financial')->where($mm)->find();
		
			if($detail){
				 $htmlContent = $this->gethtml($detail);

				if($htmlContent){
					// 设置文件名和路径
					$filename = 'cm.html';
					$filepath = '/var/www/vhosts/taxprep.republictax.com/httpdocs/public/upload/cm/'.$link.'/pdf/';
					
					// 确保目录存在
					if (!file_exists($filepath)) {
						mkdir($filepath, 0755, true);
					}
					 
					// 将HTML内容写入文件
				    file_put_contents($filepath . $filename, $htmlContent);
				
					$htmlFilePath = '/var/www/vhosts/taxprep.republictax.com/httpdocs/public/upload/cm/'.$link.'/pdf/'.$filename;
					$pdfFilePath = '/var/www/vhosts/taxprep.republictax.com/httpdocs/public/upload/cm/'.$link.'/pdf/out.pdf';
					//$command = "wkhtmltopdf --enable-local-file-access --lowquality $filepath$filename $pdfFilePath";
					$command = "wkhtmltopdf --enable-local-file-access --lowquality $filepath$filename $pdfFilePath";
				
					exec($command, $output, $returnVar);
				
					if ($returnVar === 0) {
						//send caseid
						$CASEID=$res['caseid'];
					   //  $CASEID='1000111';
						$urrrrrl="https://ntcg.irslogics.com/publicapi/documents/casedocument?apikey=f6bf9800223b44a5ac6948ee6556225d&CaseID=".$CASEID."&Content-Type=application/pdf";
						$location=$pdfFilePath;
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
						 $fname="CM-financial".$fname.".pdf"; 
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
						unlink($pdfFilePath);
						unlink($filepath.$filename);
						rmdir($filepath);
						
						//更新数据表
						$save['updatetime']=time();
                        $save['status']=2;
                        DB::name('users_clients')->where($map)->save($save);
						return json_decode($response);
						
					} else {
						return json(['status'=>0]);  
					}
				}
			}
		}
	}
     public function getcurField(){
        if (IS_POST){
            $data=input('post.');
            if(!$data['link']){
                return json(['code'=>0]);  
            }
            $map['link']=$data['link'];
            $res=DB::name('users_clients')->where($map)->find();
            if($res){
				$mm['user_client_id']=$res['id'];
				$profitloss=DB::name('user_financial')->where($mm)->find();
				if($profitloss){
				   return json(['code'=>1,'curfield'=>$profitloss['curfield']]); 
				}else{
					return json(['code'=>0,'msg'=>'Error Message']);  
				}           
            }else{
               return json(['code'=>0]);  
            }
            //保存字段
            //保存相应的字段，修改最后编辑字段，修改最后保存时间  通过最后保存字段，获得在哪一页
        }
    }
	
	public function gethtml($detail){
		/*$columns = Db::getFields('tp_user_financial');
 
			// 遍历所有字段
			foreach ($columns as $column) {
			   if($column['name']!="user_client_id"&&$column['name']!="id"&&$column['name']!="business_name"&&$column['name']!="business_description"&&$column['name']!="year"&&$column['name']!="v1_year_make_modal"&&$column['name']!="v2_year_make_modal"&&$column['name']!="v1_date_placed_into_service"&&$column['name']!="v2_date_placed_into_service"&&$column['name']!="v1_is_vehicle_ownedfinanced"&&$column['name']!="v2_is_vehicle_ownedfinanced"&&$column['name']!="v1_is_vehicle_leased"&&$column['name']!="v2_is_vehicle_leased"&&$column['name']!="v1_was_vehicle_depreciated_a_prior_year"&&$column['name']!="v2_was_vehicle_depreciated_a_prior_year"&&$column['name']!="signimage"&&$column['name']!="updatetime"&&$column['name']!="curfield"&&$column['name']!="v1_miles_for_business_only"&&$column['name']!="v2_miles_for_business_only"&&$column['name']!="v1_miles_for_personal_only"&&$column['name']!="v2_miles_for_personal_only"&&$column['name']!="total_square_feet_of_home"&&$column['name']!="total_square_feet_of_office_only"){
			   	   if($detail[$column['name']]===0||$detail[$column['name']]>0){
			   	   	 $detail[$column['name']]="$".$detail[$column['name']];
			   	   }
			      
			   }
			}*/
		$html='<html lang="en">
		    <head><meta charSet="utf-8" />
				<meta name="viewport" content="width=device-width, initial-scale=1" />
			</head>
			<style>
			body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: auto; /* Allow columns to adjust width based on content */
        }
        th, td {
            border: 1px solid #000;
            padding: 2px 5px;
            text-align: left;
            white-space: nowrap; /* Prevent text from wrapping */
            overflow: hidden; /* Hide overflow content */
            text-overflow: ellipsis; /* Show ellipsis for overflow text */
			height:25px;
			font-size:12px;
        }
        input[type="text"] {
		    padding-left:20px;
            /*  width: 100%; */
            box-sizing: border-box;
			min-width: 100px;
			line-height:25px;
        }
        button {
            display: block;
            width: 100%;
            padding: 10px;
            margin-top: 20px;
            font-size: 16px;
        }
       .tips{
	     font-size:13px;
	   }
	   .tipslarger{
	     font-size:14px;
	   }
	   .tipsbold{
	     padding-left:10px;
	     font-weight:bold;	   
	   }
	   .tipRed{
	     color:#ff0000;
		 text-decoration:underline;
	   }
	   .tipItailc{
	     	font-style:italic;
	   }
	   .noborder{
	      border:none;
	   }
	   .action-button{
	     margin-bottom:20px;
	   }
	   .inputTd{
	     padding: 0 15px;
	   }
	
		.tabletd{
		    border: none;
			background:#f1f4ff;
			transition: background-color 0.3s ease;
		}
		
		.tableTdtext{
		    border: none;
			background:#f1f4ff;
			transition: background-color 0.3s ease;
		}
		.right{
		  text-align:right;
		  padding-right:10px;
		}
		.center{
		  text-align:center;
		}
		.borderLeft{
		  border-left:1px solid #000;
		}
		.borderRight{
		  border-right:1px solid #000;
		}
		.borderTop{
		  border-top:1px solid #000;
		}
		.borderBottom{
		  border-bottom:1px solid #000;
		}
		.greybgcolor{
		  background:#dadada;
		}
		.fieldcheckbox{
		    border:1px solid;
		    border-style: solid;
		  border-color: #ccc;
		  width:15px;
		    height:15px;
		}
		input[type="checkbox"] {
		  border-radius: 5px;
		  border:0.5px solid $ccc;
		}
		.dialog{
		  max-width:1080px;
		  height:560px;
		}
		.pcshow{
		  display:flex;
		}
		.page{
			padding:20px 50px;
			
		}
		.innerpage{
		  margin:0 auto;
		  background: #ffff;
		  box-shadow: rgba(0, 0, 0, 0.5) 0px -2px 10px;		  
		}
		.action-button{
		    min-width: 50px;
		    max-width:90px;
		}
		input:focus {
			background-color: #8bda5b;
		}
		.tincome{
		  color:#8bda5b;
		}
		.expenses{
		  color:red;
		}
		.w100{
		  width:100px!important;
		}
		.ma20{
		  margin-left:20px;
		}
		textarea{
		background: #f1f4ff;
		}
		.padding20{
			padding-left:20px;
		}
		.w120{
			width:120px!important;
		}
		.title{
		  margin-top:20px;
		  font-weight:bold;
		}
	</style>
	<body>
	      <div class="page">
			<table style="width:100%;">
			  <tr>
				 <td class="tipsbold noborder borderBottom" style="text-align:center;height:18px;font-size:16px">PERSONAL FINANCIAL QUESTIONNAIRE</td>
			  </tr>
			  <tr>
				 <td class="noborder"></td>
			  </tr>
			  <tr>
				 <td class="noborder">Note: Complete all blocks. Write “N/A” (Not Applicable) in those blocks that do not apply.</td>
			  </tr>
			  <tr>
				 <td class="noborder"></td>
			  </tr>
			  <tr>
				 <td class="tipsbold noborder" style="font-size:16px;">Section 1. Personal and Household Information</td>
			  </tr>
			</table>
			<table style="width:100%;">
			  <tr>
				 <td class="noborder" colspan="4"></td>
			  </tr>
			  <tr>
				 <td class="noborder">First Name</td>
				 <td class="noborder">MI</td>
				 <td class="noborder">Last Name</td>
				 <td class="noborder">Other Names or Aliases Ever Used</td>
			  </tr>
			  <tr>
				 <td>'.$detail['firstname'].'</td>
				 <td style="width:100px;">'.$detail['mi'].'</td>
				 <td><input tabindex="5" type="text" id="a6" data-src="lastname"  class="tabletd" value="'.$detail['lastname'].'"></td>
				 <td><input tabindex="6" type="text" id="a7" data-src="othername"  class="tabletd" value="'.$detail['othername'].'"></td>
			  </tr>
			  <tr>
				 <td class="noborder" colspan="4"></td>
			  </tr>
			  <tr>
				 <td colspan="2" class="noborder">Date of Birth</td>
				 <td class="noborder">Social Security Number</td>
				 <td class="noborder">Driver’s License Number and State</td>
			  </tr>
			  <tr>
				 <td colspan="2"><input tabindex="7" type="text" id="a8" data-src="date_of_birth"  class="tabletd" value="'.$detail['date_of_birth'].'"></td>
				 <td><input tabindex="8" type="text" id="a9" data-src="ssn"  class="tabletd" value="'.$detail['ssn'].'"></td>
				 <td><input tabindex="9" type="text" id="a10" data-src="driver_license_number"  class="tabletd" value="'.$detail['driver_license_number'].'"></td>
			  </tr>
			</table>
			<table style="width:100%;">
		      <tr>
				 <td colspan="5" class="noborder"></td>
			  </tr>
			  <tr>
				 <td colspan="5" class="noborder">Home Address (Street, City, State, Zip Code)</td>
			  </tr>';
			 $home_type=$detail['home_type']; 
			 $home_type_1='';
			 $home_type_2='';
			 $home_type_3='';
			 switch($home_type){
				 case 1:
				 $home_type_1="checked";
				 break;
				 case 2:
				 $home_type_2="checked";
				 break;
				 case 3:
				 $home_type_3="checked";
				 break;
			 }
             $contanct_type=$detail['contanct_type']; 
			 $contanct_type_1='';
			 $contanct_type_2='';
			 $contanct_type_3='';
			 $contanct_type_4='';
			 switch($contanct_type){
				 case 1:
				 $contanct_type_1="checked";
				 break;
				 case 2:
				 $contanct_type_2="checked";
				 break;
				 case 3:
				 $contanct_type_3="checked";
				 break;
				 case 4:
				 $contanct_type_4="checked";
				 break;
			 }
             $marital_status=$detail['marital_status']; 
			 $marital_status_1='';
			 $marital_status_2='';
			 $marital_status_3='';
			 switch($marital_status){
				 case 1:
				 $marital_status_1="checked";
				 break;
				 case 2:
				 $marital_status_2="checked";
				 break;
				 case 3:
				 $marital_status_3="checked";
				 break;
			 }			 
		$html.='<tr>
				 <td colspan="3"><textarea id="a42" tabindex="10" cols="70" rows="4" name="home_address" data-src="home_address" class="noborder"> '.$detail['home_address'].'</textarea></td>
				 <td colspan="2"><p>Do you: 
				       <input tabindex="11" type="checkbox" name="home_type" value="1" class="fieldcheckbox" data-src="home_type" '.$home_type_1.'>Own your home</p>
				        <p style="margin-left:50px;"><input tabindex="12" type="checkbox" name="home_type" value="2" class="fieldcheckbox" data-src="home_type" '.$home_type_2.'>Rent</p>
	                    <p style="margin-left:50px;"> <input tabindex="13" type="checkbox" name="home_type" value="3" class="fieldcheckbox" data-src="home_type" '.$home_type_3.'>Other (specify e.g., share rent, live with family)</p>
 	             </td>
			  </tr>
			  <tr>
				 <td>&nbsp;&nbsp;County of Residence<br><input tabindex="14" type="text" id="a13" data-src="country_of_residence"  class="tabletd" value="'.$detail['country_of_residence'].'"></td>
				 <td>&nbsp;&nbsp;Home Phone<br><input tabindex="15" type="text" id="a14" data-src="home_phone"  class="tabletd" value="'.$detail['home_phone'].'"></td>
				 <td>&nbsp;&nbsp;Cell Phone<br><input tabindex="16" type="text" id="a15" data-src="cell_phone"  class="tabletd" value="'.$detail['cell_phone'].'"></td>
				 <td>&nbsp;&nbsp;Fax<br><input tabindex="17" type="text" id="a16" data-src="fax"  class="tabletd" value="'.$detail['fax'].'"></td>
				 <td>&nbsp;&nbsp;Email<br><input tabindex="18" type="text" id="a17" data-src="email"  class="tabletd" value="'.$detail['email'].'"></td>
			  </tr>
			  <tr>
				 <td colspan="3">
				      <p>Mailing Address (if different from above or Post Office Box number)</p>
				      <textarea id="a43" tabindex="19" cols="70" rows="4" name="mailing_address" class="noborder" data-src="mailing_address"> '.$detail['mailing_address'].'</textarea>
				 </td>
				 <td colspan="2">
				         <p style="margin-left:10px;">I prefer to be contacted:</p>
				         <p style="margin-left:50px;">
						    <input tabindex="20" type="checkbox" name="contanct_type" value="1" class="fieldcheckbox" data-src="contanct_type" '.$contanct_type_1.'> Home
						    <input tabindex="21" type="checkbox" name="contanct_type" value="2" class="fieldcheckbox" data-src="contanct_type" '.$contanct_type_2.'> Cell
						 </p>
				         <p style="margin-left:50px;">
						    <input tabindex="22" type="checkbox" name="contanct_type" value="3" class="fieldcheckbox" data-src="contanct_type" '.$contanct_type_3.'>Rent
							<input tabindex="23" type="checkbox" name="contanct_type" value="4" class="fieldcheckbox" data-src="contanct_type" '.$contanct_type_4.'>Email
						</p>
 	             </td>
			  </tr>
			</table>
			<table style="width:100%;">
			  <tr>
				 <td class="noborder" colspan="3"></td>
			  </tr>
			  <tr> 
			    <td class="noborder"> Marital status (check one):</td>
				<td class="noborder"> 
				    <input tabindex="24" type="checkbox" name="marital_status" value="1" class="fieldcheckbox" data-src="marital_status" '.$marital_status_1.'>Married 
				    <input tabindex="25" type="checkbox" name="marital_status" value="2" class="fieldcheckbox" data-src="marital_status" '.$marital_status_2.'>Separated  
				    <input tabindex="26" type="checkbox" name="marital_status" value="3" class="fieldcheckbox" data-src="marital_status" '.$marital_status_3.'>Unmarried (single, divorced, widowed) 
			    </td>
			  </tr>
			  <tr> 
			    <td class="noborder"> </td>
				<td class="noborder ">  
				   <input tabindex="27" type="text" id="a18" data-src="date_of_marriage"  class="tabletd" value="'.$detail['date_of_marriage'].'" style="width:50%;border-bottom:1px solid #000;">Date of Marriage
			    </td>
			  </tr>
			</table>
            <table style="width:100%;">
			  <tr>
				 <td class="noborder" colspan="4"></td>
			  </tr>
			  <tr>
				 <td class="noborder">Spouse’s First Name</td>
				 <td class="noborder">MI</td>
				 <td class="noborder">Last Name</td>
				 <td class="noborder">Other Names or Aliases Ever Used</td>
			  </tr>
			  <tr>
				 <td><input tabindex="28" type="text" id="a19" data-src="spouse_first_name" class="tabletd" value="'.$detail['spouse_first_name'].'"></td>
				 <td style="width:100px"><input tabindex="29" type="text" id="a20" data-src="spouse_mi" class="tabletd" value="'.$detail['spouse_mi'].'"></td>
				 <td><input tabindex="30" type="text" id="a21" data-src="spouse_last_name" class="tabletd" value="'.$detail['spouse_last_name'].'"></td>
				 <td><input tabindex="31" type="text" id="a22" data-src="spouse_other_name" class="tabletd" value="'.$detail['spouse_other_name'].'"></td>
			  </tr>
			  <tr>
				 <td class="noborder" colspan="4"></td>
			  </tr>
			  <tr>
				 <td colspan="2" class="noborder">Spouse’s Date of Birth</td>
				 <td class="noborder">Spouse’s Social Security #</td>
				 <td class="noborder">Spouse’s Driver’s License # & State</td>
			  </tr>
			  <tr>
				 <td colspan="2">
				 <input tabindex="32" type="text" id="a23" data-src="spouse_date_of_birth"  class="tabletd" value="'.$detail['spouse_date_of_birth'].'"></td>
				 <td><input tabindex="33" type="text" id="a24" data-src="spouse_social_security"  class="tabletd" value="'.$detail['spouse_social_security'].'"></td>
				 <td><input tabindex="34" type="text" id="a25" data-src="spouse_driver_license"  class="tabletd" value="'.$detail['spouse_driver_license'].'"></td>
			  </tr>
			</table> 
			<table style="width:100%;">
			  <tr>
				 <td class="noborder" colspan="5"></td>
			  </tr>
			  <tr>
				 <td class="noborder">Dependent’s Name </td>
				 <td class="noborder">Age</td>
				 <td class="noborder">Relationship</td>
				 <td class="noborder">Claimed as dependent on<br>your Form 1040?</td>
				 <td class="noborder">Contributes to Household Income?</td>
			  </tr>';
			 $claimed_as_dependent_1=$detail['claimed_as_dependent_1']; 
			 $claimed_as_dependent_1_1='';
			 $claimed_as_dependent_1_2='';
			 switch($claimed_as_dependent_1){
				 case 1:
				 $claimed_as_dependent_1_1="checked";
				 break;
				 case 2:
				 $claimed_as_dependent_1_2="checked";
				 break;
			 }
			 $contribute_1=$detail['contribute_1']; 
			 $contribute_1_1='';
			 $contribute_1_2='';
			 switch($contribute_1){
				 case 1:
				 $contribute_1_1="checked";
				 break;
				 case 2:
				 $contribute_1_2="checked";
				 break;
			 }
			 $claimed_as_dependent_2=$detail['claimed_as_dependent_2']; 
			 $claimed_as_dependent_2_1='';
			 $claimed_as_dependent_2_2='';
			 switch($claimed_as_dependent_2){
				 case 1:
				 $claimed_as_dependent_2_1="checked";
				 break;
				 case 2:
				 $claimed_as_dependent_2_2="checked";
				 break;
			 }
			 $contribute_2=$detail['contribute_2']; 
			 $contribute_2_1='';
			 $contribute_2_2='';
			 switch($contribute_2){
				 case 1:
				 $contribute_2_1="checked";
				 break;
				 case 2:
				 $contribute_2_2="checked";
				 break;
			 }
			 $claimed_as_dependent_3=$detail['claimed_as_dependent_3']; 
			 $claimed_as_dependent_3_1='';
			 $claimed_as_dependent_3_2='';
			 switch($claimed_as_dependent_3){
				 case 1:
				 $claimed_as_dependent_3_1="checked";
				 break;
				 case 2:
				 $claimed_as_dependent_3_2="checked";
				 break;
			 }
			 $contribute_3=$detail['contribute_3']; 
			 $contribute_3_1='';
			 $contribute_3_2='';
			 switch($contribute_3){
				 case 1:
				 $contribute_3_1="checked";
				 break;
				 case 2:
				 $contribute_3_2="checked";
				 break;
			 }
			 $claimed_as_dependent_4=$detail['claimed_as_dependent_4']; 
			 $claimed_as_dependent_4_1='';
			 $claimed_as_dependent_4_2='';
			 switch($claimed_as_dependent_4){
				 case 1:
				 $claimed_as_dependent_4_1="checked";
				 break;
				 case 2:
				 $claimed_as_dependent_4_2="checked";
				 break;
			 }
			 $contribute_4=$detail['contribute_4']; 
			 $contribute_4_1='';
			 $contribute_4_2='';
			 switch($contribute_4){
				 case 1:
				 $contribute_4_1="checked";
				 break;
				 case 2:
				 $contribute_4_2="checked";
				 break;
			 }
			 $haver_interest=$detail['haver_interest']; 
			 $haver_interest_1='';
			 $haver_interest_2='';
			 switch($haver_interest){
				 case 1:
				 $haver_interest_1="checked";
				 break;
				 case 2:
				 $haver_interest_2="checked";
				 break;
			 }
			$html.='<tr>
				 <td><input tabindex="35" type="text" id="a26" data-src="dependent_name_1" class="tabletd" value="'.$detail['dependent_name_1'].'"></td>
				 <td style="width:100px"><input tabindex="36" type="text" id="a27" data-src="age_1" class="tabletd" value="'.$detail['age_1'].'"></td>
				 <td><input tabindex="37" type="text" id="a28" data-src="relationship_1" class="tabletd" value="'.$detail['relationship_1'].'"></td>
				 <td>
				    <input tabindex="38" type="checkbox" name="claimed_as_dependent_1" value="1" class="fieldcheckbox" data-src="claimed_as_dependent_1" '.$claimed_as_dependent_1_1.'>Yes 
				    <input tabindex="39" type="checkbox" name="claimed_as_dependent_1" value="2" class="fieldcheckbox" data-src="claimed_as_dependent_1" '.$claimed_as_dependent_1_2.'>No  
				 </td>
				 <td>
				    <input tabindex="40" type="checkbox" name="contribute_1" value="1" class="fieldcheckbox" data-src="contribute_1" '.$contribute_1_1.'>Yes 
				    <input tabindex="41" type="checkbox" name="contribute_1" value="2" class="fieldcheckbox" data-src="contribute_1" '.$contribute_1_2.'>No 
				 </td>
			  </tr>
			  <tr>
				 <td><input tabindex="42" type="text" id="a29" data-src="dependent_name_2" class="tabletd" value="'.$detail['dependent_name_2'].'"></td>
				 <td style="width:100px"><input tabindex="43" type="text" id="a30" data-src="age_2" class="tabletd" value="'.$detail['age_2'].'"></td>
				 <td><input tabindex="44" type="text" id="a31" data-src="relationship_2" class="tabletd" value="'.$detail['relationship_2'].'"></td>
				 <td>
				    <input tabindex="45" type="checkbox" name="claimed_as_dependent_2" value="1" class="fieldcheckbox" data-src="claimed_as_dependent_2" '.$claimed_as_dependent_2_1.'>Yes 
				    <input tabindex="46" type="checkbox" name="claimed_as_dependent_2" value="2" class="fieldcheckbox" data-src="claimed_as_dependent_2" '.$claimed_as_dependent_2_2.'>No  
				 </td>
				 <td>
				    <input tabindex="47" type="checkbox" name="contribute_2" value="1" class="fieldcheckbox" data-src="contribute_2" '.$contribute_2_1.'>Yes 
				    <input tabindex="48" type="checkbox" name="contribute_2" value="2" class="fieldcheckbox" data-src="contribute_2" '.$contribute_2_2.'>No 
				 </td>
			  </tr>
			  <tr>
				 <td><input tabindex="49" type="text" id="a32" data-src="dependent_name_3" class="tabletd" value="'.$detail['dependent_name_3'].'"></td>
				 <td style="width:100px"><input tabindex="50" type="text" id="a33" data-src="age_3" class="tabletd" value="'.$detail['age_3'].'"></td>
				 <td><input tabindex="51" type="text" id="a34" data-src="relationship_3" class="tabletd" value="'.$detail['relationship_3'].'"></td>
				 <td>
				    <input tabindex="52" type="checkbox" name="claimed_as_dependent_3" value="1" class="fieldcheckbox" data-src="claimed_as_dependent_3" '.$claimed_as_dependent_3_1.'>Yes 
				    <input tabindex="53" type="checkbox" name="claimed_as_dependent_3" value="2" class="fieldcheckbox" data-src="claimed_as_dependent_3" '.$claimed_as_dependent_3_2.'>No  
				 </td>
				 <td>
				    <input tabindex="54" type="checkbox" name="contribute_3" value="1" class="fieldcheckbox" data-src="contribute_3" '.$contribute_3_1.'>Yes 
				    <input tabindex="55" type="checkbox" name="contribute_3" value="2" class="fieldcheckbox" data-src="contribute_3" '.$contribute_3_2.'>No 
				 </td>
			  </tr>
			  <tr>
				 <td><input tabindex="56" type="text" id="a35" data-src="dependent_name_4" class="tabletd" value="'.$detail['dependent_name_4'].'"></td>
				 <td style="width:100px"><input tabindex="57" type="text" id="a36" data-src="age_4" class="tabletd" value="'.$detail['age_4'].'"></td>
				 <td><input tabindex="58" type="text" id="a37" data-src="relationship_4" class="tabletd" value="'.$detail['relationship_4'].'"></td>
				 <td>
				    <input tabindex="59" type="checkbox" name="claimed_as_dependent_4" value="1" class="fieldcheckbox" data-src="claimed_as_dependent_4" '.$claimed_as_dependent_4_1.'>Yes 
				    <input tabindex="60" type="checkbox" name="claimed_as_dependent_4" value="2" class="fieldcheckbox" data-src="claimed_as_dependent_4" '.$claimed_as_dependent_4_2.'>No  
				 </td>
				 <td>
				    <input tabindex="61" type="checkbox" name="contribute_4" value="1" class="fieldcheckbox" data-src="contribute_4" '.$contribute_4_1.'>Yes 
				    <input tabindex="62" type="checkbox" name="contribute_4" value="2" class="fieldcheckbox" data-src="contribute_4" '.$contribute_4_2.'>No 
				 </td>
			  </tr>
			</table> 
		
			<table style="width:100%;">
			  <tr>
				 <td class="noborder"></td>
			  </tr>
			  <tr>
				 <td class="tipsbold noborder" style="font-size:16px;">Section 2. Employment Information (if self-employed, complete Section 6 - 8)</td>
			  </tr>
			  <tr>
				 <td class="noborder"></td>
			  </tr>
			</table>
			<table style="width:100%;">
			   <tr>
				 <td class="noborder">Your Employer’s Name</td>
				 <td class="noborder">Your Occupation</td>
				 <td class="noborder">How long with this employer?</td>
			   </tr>
			</table>
			<table style="width:100%;">	
			   <tr>
				 <td><input tabindex="63" type="text" id="a38" data-src="employer_name" class="tabletd" value="'.$detail['employer_name'].'"></td>
				 <td><input tabindex="64" type="text" id="a39" data-src="occupation" class="tabletd" value="'.$detail['occupation'].'"></td>
				 <td>
				    <input tabindex="65" type="text" id="a40" data-src="employer_year" class="tabletd w100" value="'.$detail['employer_year'].'">(years)
					<input tabindex="66" type="text" id="a41" data-src="employer_month" class="tabletd w100" value="'.$detail['employer_month'].'">(months)
				 </td>
			   </tr>
			   <tr>
				 <td colspan="2">
				      <p>Employer’s Address (street, city, state, zip code)</p>
				      <textarea id="a44" tabindex="67" cols="70" rows="4" name="employer_address" data-src="employer_address" class="noborder">'.$detail['employer_address'].'</textarea>
				 </td>
				 <td>
				     <p>Do you have an interest in this business?</p>
					 <input tabindex="68" type="checkbox" name="haver_interest" value="1" class="fieldcheckbox" data-src="haver_interest" '.$haver_interest_1.'>Yes 
				     <input tabindex="69" type="checkbox" name="haver_interest" value="2" class="fieldcheckbox" data-src="haver_interest" '.$haver_interest_2.'>No 
				 </td>
			   </tr>';
		 $pay_period=$detail['pay_period']; 
         $pay_period_week='';
         $pay_period_biweek='';
         $pay_period_month='';
		 $pay_period_other='';
         switch($pay_period){
			 case 'weekly':
			 $pay_period_week="checked";
			 break;
			 case 'bi-weekly':
			 $pay_period_biweek="checked";
			 break;
			 case 'monthly':
			 $pay_period_month="checked";
			 break;
			 case 'other':
			 $pay_period_other="checked";
			 break;
		 }	
         $spouse_have_interest=$detail['spouse_have_interest'];
         $spouse_have_interest_1='';
         $spouse_have_interest_2='';	 
         switch($spouse_have_interest){	
		    case 1:
		    $spouse_have_interest_1="checked";
			break;
		    case 2:
		    $spouse_have_interest_2="checked";
			break;
		 }	
         $spouse_pay_period=$detail['spouse_pay_period']; 
         $spay_period_week='';
         $spay_period_biweek='';
         $spay_period_month='';
		 $spay_period_other='';
         switch($spouse_pay_period){
			 case 'weekly':
			 $spay_period_week="checked";
			 break;
			 case 'bi-weekly':
			 $spay_period_biweek="checked";
			 break;
			 case 'monthly':
			 $spay_period_month="checked";
			 break;
			 case 'other':
			 $spay_period_other="checked";
			 break;
		 }			 
         $html.='<tr>
				 <td>Work Telephone Number<br> <input tabindex="70" type="text" id="a45" data-src="work_telephone_number" class="tabletd" value="'.$detail['work_telephone_number'].'"></td>
				 <td>
				    Pay Period:<br>
					<input tabindex="71" type="checkbox" name="pay_period" value="weekly" class="fieldcheckbox" data-src="pay_period" '.$pay_period_week.'>Weekly 
				    <input tabindex="72" type="checkbox" name="pay_period" value="bi-weekly" class="fieldcheckbox" data-src="pay_period" '.$pay_period_biweek.'>Bi-weekly <br>
					<input tabindex="73" type="checkbox" name="pay_period" value="monthly" class="fieldcheckbox" data-src="pay_period" '.$pay_period_month.'>Monthly
					<input tabindex="74" type="checkbox" name="pay_period" value="other" class="fieldcheckbox" data-src="pay_period" '.$pay_period_other.'>Other
				 </td>
				 <td>Number of withholding allowances claimed on W-4:<br>
				    <input tabindex="75" type="text" id="a46" data-src="number_of_withholding" class="tabletd" value="'.$detail['number_of_withholding'].'">
				 </td>
			   </tr>
			</table>
			<table style="width:100%;">
			   <tr>
				 <td class="noborder">Spouse’s Employer’s Name</td>
				 <td class="noborder">Spouse’s Occupation</td>
				 <td class="noborder">How long with this employer?</td>
			   </tr>
			</table>
			<table style="width:100%;">	
			   <tr>
				 <td><input tabindex="76" type="text" id="a47" data-src="spouse_employer_name" class="tabletd" value="'.$detail['spouse_employer_name'].'"></td>
				 <td><input tabindex="77" type="text" id="a48" data-src="spouse_occupation" class="tabletd" value="'.$detail['spouse_occupation'].'"></td>
				 <td>
				    <input tabindex="78" type="text" id="a49" data-src="spouse_employer_year" class="tabletd w100" value="'.$detail['spouse_employer_year'].'">(years)
					<input tabindex="79" type="text" id="a50" data-src="spouse_employer_month" class="tabletd w100" value="'.$detail['spouse_employer_month'].'">(months)
				 </td>
			   </tr>
			   <tr>
				 <td colspan="2">
				      <p>&nbsp;&nbsp;Employer’s Address (street, city, state, zip code)<br>
				      <textarea id="a51" tabindex="80" cols="70" rows="4" name="spouse_employer_address" data-src="spouse_employer_address" class="noborder"> '.$detail['spouse_employer_address'].'</textarea>
				 </td>
				 <td>
				     <p class="ma20">Do you have an interest in this business?</p>
					 <input tabindex="81" type="checkbox" name="spouse_have_interest" value="1" class="fieldcheckbox" data-src="spouse_have_interest" '.$spouse_have_interest_1.'>Yes 
				     <input tabindex="82" type="checkbox" name="spouse_have_interest" value="2" class="fieldcheckbox" data-src="spouse_have_interest" '.$spouse_have_interest_2.'>No 
				 </td>
			   </tr>
			   <tr>
				 <td><p class="ma20">Work Telephone Number</p> <input tabindex="83" type="text" id="a52" data-src="spouse_work_telephone_number" class="tabletd" value="'.$detail['spouse_work_telephone_number'].'"></td>
				 <td>
				   <p class="ma20"> Pay Period:</p> 
					<input tabindex="84" type="checkbox" name="spouse_pay_period" value="weekly" class="fieldcheckbox" data-src="spouse_pay_period" '.$spay_period_week.'>Weekly 
				    <input tabindex="85" type="checkbox" name="spouse_pay_period" value="bi-weekly" class="fieldcheckbox" data-src="spouse_pay_period" '.$spay_period_biweek.'>Bi-weekly <br>
					<input tabindex="86" type="checkbox" name="spouse_pay_period" value="monthly" class="fieldcheckbox" data-src="spouse_pay_period" '.$spay_period_month.'>Monthly
					<input tabindex="87" type="checkbox" name="spouse_pay_period" value="other" class="fieldcheckbox" data-src="spouse_pay_period" '.$spay_period_other.'>Other
				 </td>
				 <td>&nbsp;&nbsp;Number of withholding allowances claimed on W-4:<br><input tabindex="88" type="text" id="a53" data-src="spouse_number_of_withholding" class="tabletd" value="'.$detail['spouse_number_of_withholding'].'">
				 </td>
				 
			   </tr>
			</table>   
			<div class="title" style="margin-top:30px;font-size:16px">Section 3. Personal Asset Information Cash and Investments</div>
			<table style="width:100%;">
			  <tr>
				 <td class="noborder"><p><b style="font-size:16px;"> <i>Personal Bank Accounts </i></b>– include all checking, online bank accounts, money market accounts, savings accounts, stored value cards (e.g., <br>payroll cards, government benefit cards, etc.) If you need additional space, please attach a separate sheet.<p></td>
			  </tr>
			  <tr>
				 <td class="noborder"></td>
			  </tr>
			  <tr>
				 <td class="tipsbold noborder">Total Cash on Hand (includes any money that you have that is not in a bank):</td>
			  </tr>
			  <tr>
				 <td class="noborder"><span style="margin-left:50px;">$</span><input tabindex="89" type="text" id="a54" data-src="total_cash" class="tabletd w100  borderBottom" value="'.$detail['total_cash'].'"></td>
			  </tr>
			</table>
			<div class="title" style="margin-top:80px;">Account #1</div>
			<div style="border:1px solid #000;padding:5px;">
			   <table style="width:100%;">
				  <tr>
					 <td class="noborder w100">Type of Account:</td>
					 <td class="noborder borderBottom"><input tabindex="90" type="text" id="a55" data-src="account_type_of_account_1" class="tabletd" value="'.$detail['account_type_of_account_1'].'"></td>
					 <td class="noborder w100">Bank Name:</td>
					 <td class="noborder borderBottom"><input tabindex="91" type="text" id="a56" data-src="account_bank_name_1" class="tabletd" value="'.$detail['account_bank_name_1'].'"></td>
				  </tr>
				  <tr>
					 <td class="noborder w100">Account #:</td>
					 <td class="noborder borderBottom"><input tabindex="92" type="text" id="a57" data-src="account_1" class="tabletd" value="'.$detail['account_1'].'"></td>
					 <td class="noborder"></td>
					 <td class="noborder"></td>
				  </tr>
				  <tr>
					 <td class="noborder w100">Current Balance: $</td>
					 <td class="noborder borderBottom"><input tabindex="93" type="text" id="a58" data-src="account_current_balance_1" class="tabletd" value="'.$detail['account_current_balance_1'].'"></td>
					 <td class="noborder w100">Bank Address:</td>
					 <td class="noborder borderBottom"><input tabindex="94" type="text" id="a59" data-src="account_bank_address_1" class="tabletd" value="'.$detail['account_bank_address_1'].'"></td>
				  </tr>
				</table>
			</div>
			<div class="title">Account #2</div>
			<div style="border:1px solid #000;padding:5px;">
			   <table style="width:100%;">
				  <tr>
					 <td class="noborder w100">Type of Account:</td>
					 <td class="noborder borderBottom"><input tabindex="95" type="text" id="a60" data-src="account_type_of_account_2" class="tabletd" value="'.$detail['account_type_of_account_2'].'"></td>
					 <td class="noborder w100">Bank Name:</td>
					 <td class="noborder borderBottom"><input tabindex="96" type="text" id="a61" data-src="account_bank_name_2" class="tabletd" value="'.$detail['account_bank_name_2'].'"></td>
				  </tr>
				  <tr>
					 <td class="noborder w100">Account #:</td>
					 <td class="noborder borderBottom"><input tabindex="97" type="text" id="a62" data-src="account_2" class="tabletd" value="'.$detail['account_2'].'"></td>
					 <td class="noborder"></td>
					 <td class="noborder"></td>
				  </tr>
				  <tr>
					 <td class="noborder w100">Current Balance: $</td>
					 <td class="noborder borderBottom"><input tabindex="98" type="text" id="a63" data-src="account_current_balance_2" class="tabletd" value="'.$detail['account_current_balance_2'].'"></td>
					 <td class="noborder w100">Bank Address:</td>
					 <td class="noborder borderBottom"><input tabindex="99" type="text" id="a64" data-src="account_bank_address_2" class="tabletd" value="'.$detail['account_bank_address_2'].'"></td>
				  </tr>
				</table>
			</div>
			<div class="title">Account #3</div>
			<div style="border:1px solid #000;padding:5px;">
			   <table style="width:100%;">
				  <tr>
					 <td class="noborder w100">Type of Account:</td>
					 <td class="noborder borderBottom"><input tabindex="100" type="text" id="a65" data-src="account_type_of_account_3" class="tabletd" value="'.$detail['account_type_of_account_3'].'"></td>
					 <td class="noborder w100">Bank Name:</td>
					 <td class="noborder borderBottom"><input tabindex="101" type="text" id="a66" data-src="account_bank_name_3" class="tabletd" value="'.$detail['account_bank_name_3'].'"></td>
				  </tr>
				  <tr>
					 <td class="noborder w100">Account #:</td>
					 <td class="noborder borderBottom"><input tabindex="102" type="text" id="a67" data-src="account_3" class="tabletd" value="'.$detail['account_3'].'"></td>
					 <td class="noborder"></td>
					 <td class="noborder"></td>
				  </tr>
				  <tr>
					 <td class="noborder w100">Current Balance: $</td>
					 <td class="noborder borderBottom"><input tabindex="103" type="text" id="a68" data-src="account_current_balance_3" class="tabletd" value="'.$detail['account_current_balance_3'].'"></td>
					 <td class="noborder w100">Bank Address:</td>
					 <td class="noborder borderBottom"><input tabindex="104" type="text" id="a69" data-src="account_bank_address_3" class="tabletd" value="'.$detail['account_bank_address_3'].'"></td>
				  </tr>
				</table>
			</div>
			<div class="title">Account #4</div>
			<div style="border:1px solid #000;padding:5px;">
			   <table style="width:100%;">
				  <tr>
					 <td class="noborder w100">Type of Account:</td>
					 <td class="noborder borderBottom"><input tabindex="105" type="text" id="a70" data-src="account_type_of_account_4" class="tabletd" value="'.$detail['account_type_of_account_4'].'"></td>
					 <td class="noborder w100">Bank Name:</td>
					 <td class="noborder borderBottom"><input tabindex="106" type="text" id="a71" data-src="account_bank_name_4" class="tabletd" value="'.$detail['account_bank_name_4'].'"></td>
				  </tr>
				  <tr>
					 <td class="noborder w100">Account #:</td>
					 <td class="noborder borderBottom"><input tabindex="107" type="text" id="a72" data-src="account_4" class="tabletd" value="'.$detail['account_4'].'"></td>
					 <td class="noborder"></td>
					 <td class="noborder"></td>
				  </tr>
				  <tr>
					 <td class="noborder w100">Current Balance: $</td>
					 <td class="noborder borderBottom"><input tabindex="108" type="text" id="a73" data-src="account_current_balance_4" class="tabletd" value="'.$detail['account_current_balance_4'].'"></td>
					 <td class="noborder w100">Bank Address:</td>
					 <td class="noborder borderBottom"><input tabindex="109" type="text" id="a74" data-src="account_bank_address_4" class="tabletd" value="'.$detail['account_bank_address_4'].'"></td>
				  </tr>
				</table>
			</div>
			<div style="margin-top:20px;"><b><i>Investment Accounts</i></b>– include stocks, bonds, mutual funds, stock options, certificates of deposit, and retirement assets such as IRAs, Keogh, <br> and 401(k) plans. Include all corporations, partnerships, limited liability companies or other business entities in which you are an officer, director, <br> owner, member,or otherwise have a financial interest. If you need additional space, please attach a separate sheet.</div>
			<div class="title">Investment #1</div>
			<div style="border:1px solid #000;padding:5px;">
			   <table style="width:100%;">
				  <tr>
					 <td class="noborder borderBottom" style="width:30%">Type of Investment or Financial Interest: </td>
					 <td class="noborder borderBottom" ><input tabindex="110" type="text" id="a75" data-src="investment_type_1" class="tabletd" value="'.$detail['investment_type_1'].'"></td>
				  </tr>
			   </table>  
			   <table style="width:100%;">  
				  <tr>
					 <td class="noborder borderBottom">Bank Name: </td>
					 <td class="noborder borderBottom"><input tabindex="111" type="text" id="a76" data-src="investment_bank_name_1" class="tabletd" value="'.$detail['investment_bank_name_1'].'" style="width:400px;"></td>
					 <td class="noborder borderBottom">Acct#</td>
					 <td class="noborder borderBottom"><input tabindex="112" type="text" id="a77" data-src="investment_acc_1" class="tabletd" value="'.$detail['investment_acc_1'].'"></td>
				  </tr>
			   </table>  
			   <table style="width:100%;">    
				  <tr>
				     <td class="noborder borderBottom" style="width:20%">Bank Address: </td>
					 <td class="noborder borderBottom" ><input tabindex="113" type="text" id="a78" data-src="investment_bank_address_1" class="tabletd" value="'.$detail['investment_bank_address_1'].'"></td>
				  </tr>
				</table> ';
		 $investment_is_collateral_1=$detail['investment_is_collateral_1'];
         $investment_is_collateral_1_1='';
         $investment_is_collateral_1_2='';	 
         switch($investment_is_collateral_1){	
		    case 1:
		    $investment_is_collateral_1_1="checked";
			break;
		    case 2:
		    $investment_is_collateral_1_2="checked";
			break;
		 }
         $investment_is_collateral_2=$detail['investment_is_collateral_2'];
         $investment_is_collateral_2_1='';
         $investment_is_collateral_2_2='';	 
         switch($investment_is_collateral_2){	
		    case 1:
		    $investment_is_collateral_2_1="checked";
			break;
		    case 2:
		    $investment_is_collateral_2_2="checked";
			break;
		 }	
         $investment_is_collateral_3=$detail['investment_is_collateral_3'];
         $investment_is_collateral_3_1='';
         $investment_is_collateral_3_2='';	 
         switch($investment_is_collateral_3){	
		    case 1:
		    $investment_is_collateral_3_1="checked";
			break;
		    case 2:
		    $investment_is_collateral_3_2="checked";
			break;
		 }		 
			$html.='<table style="width:100%;">  
				  <tr>
					 <td class="noborder borderBottom">Current  Value :	$ </td>
					 <td class="noborder borderBottom"><input tabindex="114" type="text" id="a79" data-src="investment_current_value_1" class="tabletd" value="'.$detail['investment_current_value_1'].'"></td>
					 <td class="noborder borderBottom" colspan="2">Is this asset collateral for an outstanding loan?
					   <input tabindex="115" type="checkbox" name="investment_is_collateral_1" value="1" class="fieldcheckbox" data-src="investment_is_collateral_1" '.$investment_is_collateral_1_1.' >Yes 
				       <input tabindex="116" type="checkbox" name="investment_is_collateral_1" value="2" class="fieldcheckbox" data-src="investment_is_collateral_1" '.$investment_is_collateral_1_2.'>No<br>
					 </td>
				  </tr>  
				  <tr>
					 <td class="noborder borderBottom">If YES – Loan Amount: $ </td>
					 <td class="noborder borderBottom"><input tabindex="117" type="text" id="a80" data-src="investment_if_loan_1" class="tabletd" value="'.$detail['investment_if_loan_1'].'"></td>
					 <td class="noborder borderBottom" style="width:50px;">Loan Balance: $</td>
					 <td class="noborder borderBottom"><input tabindex="118" type="text" id="a81" data-src="investment_load_balance_1" class="tabletd" value="'.$detail['investment_load_balance_1'].'"></td>
				  </tr>
				</table>  
			</div>
			<div  class="title">Investment #2</div>
			<div style="border:1px solid #000;padding:5px;">
			   <table style="width:100%;">
				  <tr>
					 <td class="noborder borderBottom" style="width:30%">Type of Investment or Financial Interest: </td>
					 <td class="noborder borderBottom" ><input tabindex="119" type="text" id="a82" data-src="investment_type_2" class="tabletd" value="'.$detail['investment_type_2'].'"></td>
				  </tr>
			   </table>  
			   <table style="width:100%;">  
				  <tr>
					 <td class="noborder borderBottom">Bank Name: </td>
					 <td class="noborder borderBottom"><input tabindex="120" type="text" id="a83" data-src="investment_bank_name_2" class="tabletd" value="'.$detail['investment_bank_name_2'].'" style="width:400px;"></td>
					 <td class="noborder borderBottom">Acct#</td>
					 <td class="noborder borderBottom"><input tabindex="121" type="text" id="a84" data-src="investment_acc_2" class="tabletd" value="'.$detail['investment_acc_2'].'"></td>
				  </tr>
			   </table>  
			   <table style="width:100%;">    
				  <tr>
				     <td class="noborder borderBottom" style="width:20%">Bank Address: </td>
					 <td class="noborder borderBottom" ><input tabindex="122" type="text" id="a85" data-src="investment_bank_address_2" class="tabletd" value="'.$detail['investment_bank_address_2'].'"></td>
				  </tr>
				</table> 
				<table style="width:100%;">  
				  <tr>
					 <td class="noborder borderBottom">Current  Value :	$ </td>
					 <td class="noborder borderBottom"><input tabindex="123" type="text" id="a86" data-src="investment_current_value_2" class="tabletd" value="'.$detail['investment_current_value_2'].'"></td>
					 <td class="noborder borderBottom" colspan="2">Is this asset collateral for an outstanding loan?
					   <input tabindex="124" type="checkbox" name="investment_is_collateral_2" value="1" class="fieldcheckbox" data-src="investment_is_collateral_2" '.$investment_is_collateral_2_1.'>Yes 
				       <input tabindex="125" type="checkbox" name="investment_is_collateral_2" value="2" class="fieldcheckbox" data-src="investment_is_collateral_2" '.$investment_is_collateral_2_2.'>No<br>
					 </td>
				  </tr>  
				  <tr>
					 <td class="noborder borderBottom">If YES – Loan Amount: $ </td>
					 <td class="noborder borderBottom"><input tabindex="126" type="text" id="a87" data-src="investment_if_loan_2" class="tabletd" value="'.$detail['investment_if_loan_2'].'"></td>
					 <td class="noborder borderBottom" style="width:50px;">Loan Balance: $</td>
					 <td class="noborder borderBottom"><input tabindex="127" type="text" id="a88" data-src="investment_load_balance_2" class="tabletd" value="'.$detail['investment_load_balance_2'].'"></td>
				  </tr>
				</table>  
			</div>
			<div  class="title">Investment #3</div>
			<div style="border:1px solid #000;padding:5px;">
			   <table style="width:100%;">
				  <tr>
					 <td class="noborder borderBottom" style="width:30%">Type of Investment or Financial Interest: </td>
					 <td class="noborder borderBottom" ><input tabindex="128" type="text" id="a89" data-src="investment_type_3" class="tabletd" value="'.$detail['investment_type_3'].'"></td>
				  </tr>
			   </table>  
			   <table style="width:100%;">  
				  <tr>
					 <td class="noborder borderBottom">Bank Name: </td>
					 <td class="noborder borderBottom"><input tabindex="129" type="text" id="a90" data-src="investment_bank_name_3" class="tabletd" value="'.$detail['investment_bank_name_3'].'" style="width:400px;"></td>
					 <td class="noborder borderBottom">Acct#</td>
					 <td class="noborder borderBottom"><input tabindex="130" type="text" id="a91" data-src="investment_acc_3" class="tabletd" value="'.$detail['investment_acc_3'].'"></td>
				  </tr>
			   </table>  
			   <table style="width:100%;">    
				  <tr>
				     <td class="noborder borderBottom" style="width:20%">Bank Address: </td>
					 <td class="noborder borderBottom" ><input tabindex="131" type="text" id="a92" data-src="investment_bank_address_3" class="tabletd" value="'.$detail['investment_bank_address_3'].'"></td>
				  </tr>
				</table> 
				<table style="width:100%;">  
				  <tr>
					 <td class="noborder borderBottom">Current  Value :	$ </td>
					 <td class="noborder borderBottom"><input tabindex="132" type="text" id="a93" data-src="investment_current_value_3" class="tabletd" value="'.$detail['investment_current_value_3'].'"></td>
					 <td class="noborder borderBottom" colspan="2">Is this asset collateral for an outstanding loan?
					   <input tabindex="133" type="checkbox" name="investment_is_collateral_3" value="1" class="fieldcheckbox" data-src="investment_is_collateral_3" '.$investment_is_collateral_3_1.'>Yes 
				       <input tabindex="134" type="checkbox" name="investment_is_collateral_3" value="2" class="fieldcheckbox" data-src="investment_is_collateral_3" '.$investment_is_collateral_3_2.'>No<br>
					 </td>
				  </tr>  
				  <tr>
					 <td class="noborder borderBottom">If YES – Loan Amount: $ </td>
					 <td class="noborder borderBottom"><input tabindex="135" type="text" id="a94" data-src="investment_if_loan_3" class="tabletd" value="'.$detail['investment_if_loan_3'].'"></td>
					 <td class="noborder borderBottom" style="width:50px;">Loan Balance: $</td>
					 <td class="noborder borderBottom"><input tabindex="136" type="text" id="a95" data-src="investment_load_balance_3" class="tabletd" value="'.$detail['investment_load_balance_3'].'"></td>
				  </tr>
				</table>  
			</div>
			<div style="margin-top:20px;">
			 <i><b style="font-size:16px;">Life Insurance Policies</b>– Only complete this section if you have a life insurance policy with a cash value (term life insurance does NOT have a cash value.) If you need additional space, please attach a separate sheet.
			ATTACHMENTS ARE REQUIRED. Please include a statement from the life insurance companies that include type and cash/loan value amounts. If you currently have a loan against the policy, include loan amount. </i>
			</div>
			<div  class="title">Policy #1</div>
			<div style="border:1px solid #000;padding:5px;">
			  <table style="width:100%;">  
				  <tr>
					 <td class="noborder borderBottom" style="width:20%">Insurance Company Name:</td>
					 <td class="noborder borderBottom"><input tabindex="137" type="text" id="a96" data-src="policy_insurance_company_name_1" class="tabletd" value="'.$detail['policy_insurance_company_name_1'].'"></td>
					 <td class="noborder borderBottom">Policy #:</td>
					 <td class="noborder borderBottom"><input tabindex="138" type="text" id="a97" data-src="policy_1" class="tabletd" value="'.$detail['policy_1'].'"> </td>
				  </tr>  
				  <tr>
					 <td class="noborder borderBottom">Policy Owner:</td>
					 <td class="noborder borderBottom"><input tabindex="139" type="text" id="a98" data-src="policy_owner_1" class="tabletd" value="'.$detail['policy_owner_1'].'"></td>
					 <td class="noborder borderBottom" colspan="2"></td>
				  </tr>
				  <tr>
					 <td class="noborder borderBottom">Address of Insurance Company: </td>
					 <td class="noborder borderBottom" colspan="3"><input tabindex="140" type="text" id="a99" data-src="policy_address_of_insurance_1" class="tabletd" value="'.$detail['policy_address_of_insurance_1'].'"></td>
				  </tr>  
				  <tr>
					 <td class="noborder borderBottom">Current Value: </td>
					 <td class="noborder borderBottom" colspan="3">$<input tabindex="141" type="text" id="a100" data-src="policy_current_value_1" class="tabletd" value="'.$detail['policy_current_value_1'].'" style="width:680px"></td>
				  </tr>
				</table>  
			</div>';
		 $policy_does_has_outstanding_loan_1=$detail['policy_does_has_outstanding_loan_1'];
         $policy_does_has_outstanding_loan_1_1='';
         $policy_does_has_outstanding_loan_1_2='';	 
         switch($policy_does_has_outstanding_loan_1){	
		    case 1:
		    $policy_does_has_outstanding_loan_1_1="checked";
			break;
		    case 2:
		    $policy_does_has_outstanding_loan_1_2="checked";
			break;
		 }
		 $policy_does_has_outstanding_loan_2=$detail['policy_does_has_outstanding_loan_2'];
         $policy_does_has_outstanding_loan_2_1='';
         $policy_does_has_outstanding_loan_2_2='';	 
         switch($policy_does_has_outstanding_loan_2){	
		    case 1:
		    $policy_does_has_outstanding_loan_2_1="checked";
			break;
		    case 2:
		    $policy_does_has_outstanding_loan_2_2="checked";
			break;
		 }
		$html.='<div style="border:1px solid #000;padding:5px;">
			  <table style="width:100%;">  
				  <tr>
					 <td class="noborder borderBottom" style="width:20%">Does this policy have an outstanding loan?</td>
					 <td class="noborder borderBottom">
					   <input tabindex="142" type="checkbox" name="policy_does_has_outstanding_loan_1" value="1" class="fieldcheckbox" data-src="policy_does_has_outstanding_loan_1" '.$policy_does_has_outstanding_loan_1_1.'>Yes 
				       <input tabindex="143" type="checkbox" name="policy_does_has_outstanding_loan_1" value="2" class="fieldcheckbox" data-src="policy_does_has_outstanding_loan_1" '.$policy_does_has_outstanding_loan_1_2.'>No</td>
					 <td class="noborder borderBottom" style="width:50px;">If yes, loan amount: $</td>
					 <td class="noborder borderBottom"><input tabindex="144" type="text" id="a101" data-src="policy_loan_amount_1" class="tabletd" value="'.$detail['policy_loan_amount_1'].'"> </td>
				  </tr> 
				</table>  
			</div>
			<div  class="title">Policy #2</div>
			<div style="border:1px solid #000;padding:5px;">
			  <table style="width:100%;">  
				  <tr>
					 <td class="noborder borderBottom" style="width:20%">Insurance Company Name:</td>
					 <td class="noborder borderBottom"><input tabindex="145" type="text" id="a102" data-src="policy_insurance_company_name_2" class="tabletd" value="'.$detail['policy_insurance_company_name_2'].'"></td>
					 <td class="noborder borderBottom">Policy #:</td>
					 <td class="noborder borderBottom"><input tabindex="146" type="text" id="a103" data-src="policy_2" class="tabletd" value="'.$detail['policy_2'].'"> </td>
				  </tr>  
				  <tr>
					 <td class="noborder borderBottom">Policy Owner:</td>
					 <td class="noborder borderBottom"><input tabindex="147" type="text" id="a104" data-src="policy_owner_2" class="tabletd" value="'.$detail['policy_owner_2'].'"></td>
					 <td class="noborder borderBottom" colspan="2"></td>
				  </tr>
				  <tr>
					 <td class="noborder borderBottom">Address of Insurance Company: </td>
					 <td class="noborder borderBottom" colspan="3"><input tabindex="148" type="text" id="a105" data-src="policy_address_of_insurance_2" class="tabletd" value="'.$detail['policy_address_of_insurance_2'].'"></td>
				  </tr>  
				  <tr>
					 <td class="noborder borderBottom">Current Value: </td>
					 <td class="noborder borderBottom" colspan="3">$<input tabindex="149" type="text" id="a106" data-src="policy_current_value_2" class="tabletd" value="'.$detail['policy_current_value_2'].'" style="width:680px"></td>
				  </tr>
				</table>  
			</div>
			<div style="border:1px solid #000;padding:5px;margin-top:60px;">
			  <table style="width:100%;">  
				  <tr>
					 <td class="noborder borderBottom" style="width:20%">Does this policy have an outstanding loan?</td>
					 <td class="noborder borderBottom">
					   <input tabindex="150" type="checkbox"  value="1" class="fieldcheckbox" data-src="policy_does_has_outstanding_loan_2" '.$policy_does_has_outstanding_loan_2_1.'>Yes 
				       <input tabindex="151" type="checkbox"  value="2" class="fieldcheckbox" data-src="policy_does_has_outstanding_loan_2" '.$policy_does_has_outstanding_loan_2_2.'>No</td>
					 <td class="noborder borderBottom" style="width:50px;">If yes, loan amount: $</td>
					 <td class="noborder borderBottom"><input tabindex="152" type="text" id="a107" data-src="policy_loan_amount_2" class="tabletd" value="'.$detail['policy_loan_amount_2'].'"> </td>
				  </tr> 
				</table>  
			</div>
			<div class="title">Policy #3</div>
			<div style="border:1px solid #000;padding:5px;">
			  <table style="width:100%;">  
				  <tr>
					 <td class="noborder borderBottom" style="width:20%">Insurance Company Name:</td>
					 <td class="noborder borderBottom"><input tabindex="153" type="text" id="a108" data-src="policy_insurance_company_name_3" class="tabletd" value="'.$detail['policy_insurance_company_name_3'].'"></td>
					 <td class="noborder borderBottom">Policy #:</td>
					 <td class="noborder borderBottom"><input tabindex="154" type="text" id="a109" data-src="policy_3" class="tabletd" value="'.$detail['policy_3'].'"> </td>
				  </tr>  
				  <tr>
					 <td class="noborder borderBottom">Policy Owner:</td>
					 <td class="noborder borderBottom"><input tabindex="155" type="text" id="a110" data-src="policy_owner_3" class="tabletd" value="'.$detail['policy_owner_3'].'"></td>
					 <td class="noborder borderBottom" colspan="2"></td>
				  </tr>
				  <tr>
					 <td class="noborder borderBottom">Address of Insurance Company: </td>
					 <td class="noborder borderBottom" colspan="3"><input tabindex="156" type="text" id="a111" data-src="policy_address_of_insurance_3" class="tabletd" value="'.$detail['policy_address_of_insurance_3'].'"></td>
				  </tr>  
				  <tr>
					 <td class="noborder borderBottom">Current Value: </td>
					 <td class="noborder borderBottom" colspan="3">$<input tabindex="157" type="text" id="a112" data-src="policy_current_value_3" class="tabletd" value="'.$detail['policy_current_value_3'].'" style="width:680px"></td>
				  </tr>
				</table>  
			</div>
			<div style="border:1px solid #000;padding:5px;margin-top:20px;">
			  <table style="width:100%;">  
				  <tr>
					 <td class="noborder borderBottom" style="width:20%">Does this policy have an outstanding loan?</td>
					 <td class="noborder borderBottom">
					   <input tabindex="158" type="checkbox" value="1" class="fieldcheckbox" data-src="policy_does_has_outstanding_loan_3" '.$policy_does_has_outstanding_loan_3_1.'>Yes 
				       <input tabindex="159" type="checkbox" value="2" class="fieldcheckbox" data-src="policy_does_has_outstanding_loan_3" '.$policy_does_has_outstanding_loan_3_2.'>No</td>
					 <td class="noborder borderBottom" style="width:50px;">If yes, loan amount: $</td>
					 <td class="noborder borderBottom"><input tabindex="160" type="text" id="a113" data-src="policy_loan_amount_3" class="tabletd" value="'.$detail['policy_loan_amount_3'].'"> </td>
				  </tr> 
				</table>  
			</div>
			
			<div style="margin-top:20px;">
			 <p> <i><b style="font-size:16px;">Real Property Owned or Being Purchased </b>–  List any information about any land contract, house, condo, co-op, time share, etc. that you own or are buying. If you need additional space, please attach a separate sheet.</i><p> </div>
			 <div style="margin-top:20px;">
			  <i>ATTACHMENTS ARE REQUIRED. Please include a current statement from your lender with a monthly payment amount and current loan balance for each piece of real estate owned.</i>
			</div>';
		 $property_1=$detail['property_1'];
         $property_1_1='';
         $property_1_2='';	 
         switch($property_1){	
		    case 1:
		    $property_1_1="checked";
			break;
		    case 2:
		    $property_1_2="checked";
			break;
		 }
		 $property_primary_residence_1=$detail['property_primary_residence_1'];
         $property_primary_residence_1_1='';
         $property_primary_residence_1_2='';	 
         switch($property_primary_residence_1){	
		    case 1:
		    $property_primary_residence_1_1="checked";
			break;
		    case 2:
		    $property_primary_residence_1_2="checked";
			break;
		 }
		 
		 $property_2=$detail['property_2'];
         $property_2_1='';
         $property_2_2='';	 
         switch($property_2){	
		    case 1:
		    $property_2_1="checked";
			break;
		    case 2:
		    $property_2_2="checked";
			break;
		 }
		 $property_primary_residence_2=$detail['property_primary_residence_2'];
         $property_primary_residence_2_1='';
         $property_primary_residence_2_2='';	 
         switch($property_primary_residence_2){	
		    case 1:
		    $property_primary_residence_2_1="checked";
			break;
		    case 2:
		    $property_primary_residence_2_2="checked";
			break;
		 }
			$html.='<div style="margin-top:20px;font-weight:bold;">Property #1 
			       <input tabindex="161" type="checkbox" name="property_1" value="1" class="fieldcheckbox" data-src="property_1" '.$property_1_1.'>OWNED 
				   <input tabindex="162" type="checkbox" name="property_1" value="2" class="fieldcheckbox" data-src="property_1" '.$property_1_2.'>PURCHASING
			</div>
			<div style="margin-top:20px;">
			    <table style="width:100%;"> 
				   <tr>
				      <td class=" borderBottom" rowspan="2" style="width:370px;">&nbsp;&nbsp;Property Address (street, city, state, zip code)<br>
					  <textarea id="a114" tabindex="163" cols="50" rows="4" name="policy_address_of_insurance_1" data-src="policy_address_of_insurance_1" class="noborder"> '.$detail['policy_address_of_insurance_1'].'</textarea></td>
					  <td class=" borderBottom">&nbsp;&nbsp;County <br><input tabindex="164" type="text" id="a115" data-src="property_county_1" class="tabletd" value="'.$detail['property_county_1'].'"></td>
					  <td class=" borderBottom">&nbsp;&nbsp;Primary residence? <br>
					   <input tabindex="165" type="checkbox" value="1" class="fieldcheckbox" data-src="property_primary_residence_1" '.$property_primary_residence_1_1.'>Yes 
				       <input tabindex="166" type="checkbox" value="2" class="fieldcheckbox" data-src="property_primary_residence_1" '.$property_primary_residence_1_2.'>No     
					  </td>
				   </tr>
				   <tr>
					  <td class=" borderBottom">&nbsp;&nbsp;Country <br><input tabindex="167" type="text" id="a116" data-src="property_country_1" class="tabletd" value="'.$detail['property_country_1'].'"></td>
					  <td class=" borderBottom">&nbsp;&nbsp;How title is held (joint tenancy, etc.)  <br><input tabindex="168" type="text" id="a115" data-src="property_how_title_is_held_1" class="tabletd" value="'.$detail['property_how_title_is_held_1'].'">
					  </td>
				   </tr>
                   <tr> 
				      <td>&nbsp;&nbsp;Name and Address of Lender/Contract Holder<br><input tabindex="41" type="text" id="a117" data-src="property_na_of_holder_1" class="tabletd" value="'.$detail['property_na_of_holder_1'].'"></td>
					  <td>&nbsp;&nbsp;Date Purchased <br><input tabindex="169" type="text" id="a118" data-src="property_date_puchased_1" class="tabletd" value="'.$detail['property_date_puchased_1'].'"></td>
					  <td>&nbsp;&nbsp;Current Market Value <br><input tabindex="170" type="text" id="a119" data-src="property_current_market_value_1" class="tabletd" value="'.$detail['property_current_market_value_1'].'">
					  </td>
				   </tr>					   
                </table>
               <table style="width:100%;margin-top:20px;"> 	
                   <tr>
				      <td class=" borderBottom">&nbsp;&nbsp;Description of Property<br>
					   <input tabindex="171" type="text" id="a120" data-src="property_description_1" class="tabletd" value="'.$detail['property_description_1'].'"></td>
					  <td class=" borderBottom">&nbsp;&nbsp;Monthly Mortgage or Rent <br><input tabindex="172" type="text" id="a121" data-src="property_mortgage_rent_1" class="tabletd" value="'.$detail['property_mortgage_rent_1'].'"></td>
					  <td class=" borderBottom">&nbsp;&nbsp;Date of Final Payment <br><input tabindex="173" type="text" id="a122" data-src="property_date_of_final_payment_1" class="tabletd" value="'.$detail['property_date_of_final_payment_1'].'"></td>
					  <td class=" borderBottom">&nbsp;&nbsp;Loan Balance <br><input tabindex="174" type="text" id="a123" data-src="property_load_balance_1" class="tabletd" value="'.$detail['property_load_balance_1'].'"></td>
				   </tr>
               </table>				   
			</div>
			
			<div class="title">Property #2 
			       <input tabindex="175" type="checkbox" name="property_2" value="1" class="fieldcheckbox" data-src="property_2" '.$property_2_1.'>OWNED 
				   <input tabindex="176" type="checkbox" name="property_2" value="2" class="fieldcheckbox" data-src="property_2" '.$property_2_2.'>PURCHASING
			</div>
			<div style="margin-top:20px;">
			    <table style="width:100%;"> 
				   <tr>
				      <td class=" borderBottom" rowspan="2" style="width:370px;">&nbsp;&nbsp;Property Address (street, city, state, zip code)<br>
					  <textarea id="a124" tabindex="177" cols="50" rows="4" name="policy_address_of_insurance_2" class="noborder" data-src="policy_address_of_insurance_2"> '.$detail['policy_address_of_insurance_2'].'</textarea></td>
					  <td class=" borderBottom">&nbsp;&nbsp;County <br><input tabindex="178" type="text" id="a125" data-src="property_county_2" class="tabletd" value="'.$detail['property_county_2'].'"></td>
					  <td class=" borderBottom">&nbsp;&nbsp;Primary residence? <br>
					  <input tabindex="179" type="checkbox" value="1" class="fieldcheckbox" data-src="property_primary_residence_2" '.$property_primary_residence_2_1.'>Yes 
				       <input tabindex="180" type="checkbox" value="2" class="fieldcheckbox" data-src="property_primary_residence_2" '.$property_primary_residence_2_2.'>No     
					  </td>
				   </tr>
				   <tr>
					  <td class=" borderBottom">&nbsp;&nbsp;Country <br><input tabindex="181" type="text" id="a126" data-src="property_country_2" class="tabletd" value="'.$detail['property_country_2'].'"></td>
					  <td class=" borderBottom">&nbsp;&nbsp;How title is held (joint tenancy, etc.)  <br><input tabindex="182" type="text" id="a127" data-src="property_how_title_is_held_2" class="tabletd" value="'.$detail['property_how_title_is_held_2'].'">
					  </td>
				   </tr>
                   <tr> 
				      <td>&nbsp;&nbsp;Name and Address of Lender/Contract Holder<br><input tabindex="183" type="text" id="a128" data-src="property_na_of_holder_2" class="tabletd" value="'.$detail['property_na_of_holder_2'].'"></td>
					  <td>&nbsp;&nbsp;Date Purchased <br><input tabindex="184" type="text" id="a129" data-src="property_date_puchased_2" class="tabletd" value="'.$detail['property_date_puchased_2'].'"></td>
					  <td>&nbsp;&nbsp;Current Market Value <br><input tabindex="185" type="text" id="a130" data-src="property_current_market_value_2" class="tabletd" value="'.$detail['property_current_market_value_2'].'">
					  </td>
				   </tr>					   
                </table>
               <table style="width:100%;margin-top:20px;"> 	
                   <tr>
				      <td class=" borderBottom">&nbsp;&nbsp;Description of Property<br>
					   <input tabindex="186" type="text" id="a131" data-src="property_description_1" class="tabletd" value="'.$detail['property_description_1'].'"></td>
					  <td class=" borderBottom">&nbsp;&nbsp;Monthly Mortgage or Rent <br><input tabindex="187" type="text" id="a132" data-src="property_mortgage_rent_2" class="tabletd" value="'.$detail['property_mortgage_rent_2'].'"></td>
					  <td class=" borderBottom">&nbsp;&nbsp;Date of Final Payment <br><input tabindex="188" type="text" id="a133" data-src="property_date_of_final_payment_2" class="tabletd" value="'.$detail['property_date_of_final_payment_2'].'"></td>
					  <td class=" borderBottom">&nbsp;&nbsp;Loan Balance <br><input tabindex="189" type="text" id="a134" data-src="property_load_balance_2" class="tabletd" value="'.$detail['property_load_balance_2'].'"></td>
				   </tr>
               </table>				   
			</div>
            <div style="margin-top:20px;"><i><b>Purchased and Leased Automobiles, Trucks, and other Licensed Assets– </b>–   Includes boats, RVs, motorcycles, all- terrain and off-road vehicles, trailers, etc. If you need additional space, please attach a separate sheet.</b></i></div>
			<div style="margin-top:20px;">
			<i>ATTACHMENTS ARE REQUIRED. Please include a current statement from your lender with a monthly payment amount and current loan balance for each vehicle owned or leased.</i>
			</div>';
		 $asset_1=$detail['asset_1'];
         $asset_1_1='';
         $asset_1_2='';	 
		 $asset_1_3='';	
         switch($asset_1){	
		    case 1:
		    $asset_1_1="checked";
			break;
		    case 2:
		    $asset_1_2="checked";
			break;
			case 3:
		    $asset_1_3="checked";
			break;
		 }
		 $asset_2=$detail['asset_2'];
         $asset_2_1='';
         $asset_2_2='';	 
		 $asset_2_3='';	
         switch($asset_2){	
		    case 1:
		    $asset_2_1="checked";
			break;
		    case 2:
		    $asset_2_2="checked";
			break;
			case 3:
		    $asset_2_3="checked";
			break;
		 }
		 $asset_3=$detail['asset_3'];
         $asset_3_1='';
         $asset_3_2='';	 
		 $asset_3_3='';	
         switch($asset_3){	
		    case 1:
		    $asset_3_1="checked";
			break;
		    case 2:
		    $asset_3_2="checked";
			break;
			case 3:
		    $asset_3_3="checked";
			break;
		 }
			$html.='<div class="title">Licensed Asset #1 
			       <input tabindex="190" type="checkbox" name="asset_1" value="1" class="fieldcheckbox" data-src="asset_1" '.$asset_1_1.'>OWNED 
				   <input tabindex="191" type="checkbox" name="asset_1" value="2" class="fieldcheckbox" data-src="asset_1" '.$asset_1_2.'>LEASED
				   <input tabindex="192" type="checkbox" name="asset_1" value="3" class="fieldcheckbox" data-src="asset_1" '.$asset_1_3.'>OTHER
			</div>
			<table style="width:100%;margin-top:20px;"> 	
			   <tr>
			    <td class="borderBottom">&nbsp;&nbsp;Vehicle Make<br><input tabindex="193" type="text" id="a135" data-src="asset_vehicle_make_1" class="tabletd" value="'.$detail['asset_vehicle_make_1'].'"></td>
			    <td class="borderBottom">&nbsp;&nbsp;Vehicle Model<br><input tabindex="194" type="text" id="a136" data-src="asset_vehicle_model_1" class="tabletd" value="'.$detail['asset_vehicle_model_1'].'"></td>
			    <td class="borderBottom">&nbsp;&nbsp;Year<br><input tabindex="195" type="text" id="a137" data-src="asset_year_1" class="tabletd" value="'.$detail['asset_year_1'].'"></td>
			    <td class="borderBottom">&nbsp;&nbsp;Mileage<br><input tabindex="196" type="text" id="a138" data-src="asset_mileage_1" class="tabletd" value="'.$detail['asset_mileage_1'].'"></td>
			    <td class="borderBottom">&nbsp;&nbsp;Date Purchased or Leased<br><input tabindex="197" type="text" id="a139" data-src="asset_date_purchased_leased_1" class="tabletd" value="'.$detail['asset_date_purchased_leased_1'].'"></td>
			   </tr>
			   <tr>
			    <td class="borderBottom">&nbsp;&nbsp;Lease Loan<br><input tabindex="198" type="text" id="a140" data-src="asset_lease_load_1" class="tabletd" value="'.$detail['asset_lease_load_1'].'"></td>
			    <td class="borderBottom">&nbsp;&nbsp;Name and Address of Lender<br><input tabindex="199" type="text" id="a141" data-src="asset_na_of_lender_1" class="tabletd" value="'.$detail['asset_na_of_lender_1'].'"></td>
			    <td class="borderBottom" colspan="2">&nbsp;&nbsp;Current Loan Balance<br><input tabindex="200" type="text" id="a142" data-src="asset_current_loan_balance_1" class="tabletd" value="'.$detail['asset_current_loan_balance_1'].'"></td>
			    <td class="borderBottom">&nbsp;&nbsp;Monthly Payment Amount<br><input tabindex="201" type="text" id="a143" data-src="asset_monthly_payment_amount_1" class="tabletd" value="'.$detail['asset_monthly_payment_amount_1'].'"></td>
			   </tr>
			</table>
			<div class="title">Licensed Asset #2 
			       <input tabindex="202" type="checkbox" name="asset_2" value="1" class="fieldcheckbox" data-src="asset_2" '.$asset_2_1.'>OWNED 
				   <input tabindex="203" type="checkbox" name="asset_2" value="2" class="fieldcheckbox" data-src="asset_2" '.$asset_2_2.'>LEASED
				   <input tabindex="204" type="checkbox" name="asset_2" value="3" class="fieldcheckbox" data-src="asset_2" '.$asset_2_3.'>OTHER
			</div>
			<table style="width:100%;margin-top:20px;"> 	
			   <tr>
			    <td class="borderBottom">&nbsp;&nbsp;Vehicle Make<br><input tabindex="205" type="text" id="a144" data-src="asset_vehicle_make_2" class="tabletd" value="'.$detail['asset_vehicle_make_2'].'"></td>
			    <td class="borderBottom">&nbsp;&nbsp;Vehicle Model<br><input tabindex="206" type="text" id="a145" data-src="asset_vehicle_model_2" class="tabletd" value="'.$detail['asset_vehicle_model_2'].'"></td>
			    <td class="borderBottom">&nbsp;&nbsp;Year<br><input tabindex="207" type="text" id="a146" data-src="asset_year_2" class="tabletd" value="'.$detail['asset_year_2'].'"></td>
			    <td class="borderBottom">&nbsp;&nbsp;Mileage<br><input tabindex="208" type="text" id="a147" data-src="asset_mileage_2" class="tabletd" value="'.$detail['asset_mileage_2'].'"></td>
			    <td class="borderBottom">&nbsp;&nbsp;Date Purchased or Leased<br><input tabindex="209" type="text" id="a148" data-src="asset_date_purchased_leased_2" class="tabletd" value="'.$detail['asset_date_purchased_leased_2'].'"></td>
			   </tr>
			   <tr>
			    <td class="borderBottom">&nbsp;&nbsp;Lease Loan<br><input tabindex="210" type="text" id="a149" data-src="asset_lease_load_2" class="tabletd" value="'.$detail['asset_lease_load_2'].'"></td>
			    <td class="borderBottom">&nbsp;&nbsp;Name and Address of Lender<br><input tabindex="211" type="text" id="a150" data-src="asset_na_of_lender_2" class="tabletd" value="'.$detail['asset_na_of_lender_2'].'"></td>
			    <td class="borderBottom" colspan="2">&nbsp;&nbsp;Current Loan Balance<br><input tabindex="212" type="text" id="a151" data-src="asset_current_loan_balance_2" class="tabletd" value="'.$detail['asset_current_loan_balance_2'].'"></td>
			    <td class="borderBottom">&nbsp;&nbsp;Monthly Payment Amount<br><input tabindex="213" type="text" id="a152" data-src="asset_monthly_payment_amount_2" class="tabletd" value="'.$detail['asset_monthly_payment_amount_2'].'"></td>
			   </tr>
			</table>
			<div  class="title">Licensed Asset #3 
			       <input tabindex="214" type="checkbox" name="asset_3" value="1" class="fieldcheckbox" data-src="asset_3"  '.$asset_3_1.'>OWNED 
				   <input tabindex="215" type="checkbox" name="asset_3" value="2" class="fieldcheckbox" data-src="asset_3"  '.$asset_3_2.'>LEASED
				   <input tabindex="216" type="checkbox" name="asset_3" value="3" class="fieldcheckbox" data-src="asset_3"  '.$asset_3_3.'>OTHER
			</div>
			<table style="width:100%;margin-top:20px;"> 	
			   <tr>
			    <td class="borderBottom">&nbsp;&nbsp;Vehicle Make<br><input tabindex="217" type="text" id="a153" data-src="asset_vehicle_make_3" class="tabletd" value="'.$detail['asset_vehicle_make_3'].'"></td>
			    <td class="borderBottom">&nbsp;&nbsp;Vehicle Model<br><input tabindex="218" type="text" id="a154" data-src="asset_vehicle_model_3" class="tabletd" value="'.$detail['asset_vehicle_model_3'].'"></td>
			    <td class="borderBottom">&nbsp;&nbsp;Year<br><input tabindex="219" type="text" id="a155" data-src="asset_yasset_year_3ear_1" class="tabletd" value="'.$detail['asset_year_3'].'"></td>
			    <td class="borderBottom">&nbsp;&nbsp;Mileage<br><input tabindex="220" type="text" id="a156" data-src="asset_mileage_3" class="tabletd" value="'.$detail['asset_mileage_3'].'"></td>
			    <td class="borderBottom">&nbsp;&nbsp;Date Purchased or Leased<br><input tabindex="221" type="text" id="a157" data-src="asset_date_purchased_leased_3" class="tabletd" value="'.$detail['asset_date_purchased_leased_3'].'"></td>
			   </tr>
			   <tr>
			    <td class="borderBottom">&nbsp;&nbsp;Lease Loan<br><input tabindex="222" type="text" id="a158" data-src="asset_lease_load_3" class="tabletd" value="'.$detail['asset_lease_load_3'].'"></td>
			    <td class="borderBottom">&nbsp;&nbsp;Name and Address of Lender<br><input tabindex="223" type="text" id="a159" data-src="asset_na_of_lender_3" class="tabletd" value="'.$detail['asset_na_of_lender_3'].'"></td>
			    <td class="borderBottom" colspan="2">&nbsp;&nbsp;Current Loan Balance<br><input tabindex="224" type="text" id="a160" data-src="asset_current_loan_balance_3" class="tabletd" value="'.$detail['asset_current_loan_balance_3'].'"></td>
			    <td class="borderBottom">&nbsp;&nbsp;Monthly Payment Amount<br><input tabindex="225" type="text" id="a161" data-src="asset_monthly_payment_amount_3" class="tabletd" value="'.$detail['asset_monthly_payment_amount_3'].'"></td>
			   </tr>
			</table>
			<div style="margin-top:20px;">
			 <i><b style="font-size:16px;">Other Valuable Items  </b>–  artwork, furniture/personal effects, antiques, licenses, domains, collections, jewelry, items of value in safe deposit boxes, interest in a company or business that is not publicly traded, etc. If you need additional space, please attach a separate sheet.</i>
			</div>
			<div class="title">Asset #1 </div>
			<table style="width:100%;margin-top:20px;"> 	
			   <tr>
			    <td class="borderBottom">&nbsp;&nbsp;Description of Asset:<br><input tabindex="226" type="text" id="a162" data-src="other_asset_description_1" class="tabletd" value="'.$detail['other_asset_description_1'].'"></td>
			    <td class="borderBottom" colspan="2">&nbsp;&nbsp;Purchase/Lease Date<br><input tabindex="227" type="text" id="a163" data-src="other_asset_purchased_date_1" class="tabletd" value="'.$detail['other_asset_purchased_date_1'].'"></td>
			    <td class="borderBottom">&nbsp;&nbsp;Current Market Value<br><input tabindex="228" type="text" id="a164" data-src="other_asset_current_market_value_1" class="tabletd" value="'.$detail['other_asset_current_market_value_1'].'"></td>
			   </tr>
			   <tr>
			    <td class="borderBottom">&nbsp;&nbsp;Name and Address of Lender<br><input tabindex="229" type="text" id="a165" data-src="other_asset_na_of_lender_1" class="tabletd" value="'.$detail['other_asset_na_of_lender_1'].'"></td>
			    <td class="borderBottom">&nbsp;&nbsp;Monthly Loan Amount<br><input tabindex="230" type="text" id="a166" data-src="other_asset_monthly_loan_amount_1" class="tabletd" value="'.$detail['other_asset_monthly_loan_amount_1'].'"></td>
			    <td class="borderBottom">&nbsp;&nbsp;Loan Balance<br><input tabindex="231" type="text" id="a167" data-src="other_asset_load_balance_1" class="tabletd" value="'.$detail['other_asset_load_balance_1'].'"></td>
			    <td class="borderBottom">&nbsp;&nbsp;Date of final payment<br><input tabindex="232" type="text" id="a168" data-src="other_asset_date_of_final_payment_1" class="tabletd" value="'.$detail['other_asset_date_of_final_payment_1'].'"></td>
			   </tr>
			   <tr>
			   <tr>
			    <td class="borderBottom">Location of Property:</td>
			    <td class="borderBottom" colspan="3">   <input tabindex="233" type="text" id="a169" data-src="other_location_of_property_1" class="tabletd" value="'.$detail['other_location_of_property_1'].'">
			    </td>
			   </tr>
			 </table>
			<div class="title" style="margin-top:70px;">Asset #2 </div>
			<table style="width:100%;margin-top:20px;"> 	
			   <tr>
			    <td class="borderBottom">&nbsp;&nbsp;Description of Asset:<br><input tabindex="234" type="text" id="a170" data-src="other_asset_description_2" class="tabletd" value="'.$detail['other_asset_description_2'].'"></td>
			    <td class="borderBottom" colspan="2">&nbsp;&nbsp;Purchase/Lease Date<br><input tabindex="235" type="text" id="a171" data-src="other_asset_purchased_date_2" class="tabletd" value="'.$detail['other_asset_purchased_date_2'].'"></td>
			    <td class="borderBottom">&nbsp;&nbsp;Current Market Value<br><input tabindex="236" type="text" id="a172" data-src="other_asset_current_market_value_2" class="tabletd" value="'.$detail['other_asset_current_market_value_2'].'"></td>
			   </tr>
			   <tr>
			    <td class="borderBottom">&nbsp;&nbsp;Name and Address of Lender<br><input tabindex="237" type="text" id="a173" data-src="other_asset_na_of_lender_2" class="tabletd" value="'.$detail['other_asset_na_of_lender_2'].'"></td>
			    <td class="borderBottom">&nbsp;&nbsp;Monthly Loan Amount<br><input tabindex="238" type="text" id="a174" data-src="other_asset_monthly_loan_amount_2" class="tabletd" value="'.$detail['other_asset_monthly_loan_amount_2'].'"></td>
			    <td class="borderBottom">&nbsp;&nbsp;Loan Balance<br><input tabindex="239" type="text" id="a175" data-src="other_asset_load_balance_2" class="tabletd" value="'.$detail['other_asset_load_balance_2'].'"></td>
			    <td class="borderBottom">&nbsp;&nbsp;Date of final payment<br><input tabindex="240" type="text" id="a176" data-src="other_asset_date_of_final_payment_2" class="tabletd" value="'.$detail['other_asset_date_of_final_payment_2'].'"></td>
			   </tr>
			   <tr>
			   <tr>
			    <td class="borderBottom">Location of Property:</td>
			    <td class="borderBottom" colspan="3"><input tabindex="241" type="text" id="a177" data-src="other_location_of_property_2" class="tabletd" value="'.$detail['other_location_of_property_2'].'">
			    </td>
			   </tr>
			 </table> 
			 <div class="title">Asset #3 </div>
			<table style="width:100%;margin-top:20px;"> 	
			   <tr>
			    <td class="borderBottom">&nbsp;&nbsp;Description of Asset:<br><input tabindex="242" type="text" id="a178" data-src="other_asset_description_3" class="tabletd" value="'.$detail['other_asset_description_3'].'"></td>
			    <td class="borderBottom" colspan="2">&nbsp;&nbsp;Purchase/Lease Date<br><input tabindex="243" type="text" id="a179" data-src="other_asset_purchased_date_3" class="tabletd" value="'.$detail['other_asset_purchased_date_3'].'"></td>
			    <td class="borderBottom">&nbsp;&nbsp;Current Market Value<br><input tabindex="244" type="text" id="a180" data-src="other_asset_current_market_value_3" class="tabletd" value="'.$detail['other_asset_current_market_value_3'].'"></td>
			   </tr>
			   <tr>
			    <td class="borderBottom">&nbsp;&nbsp;Name and Address of Lender<br><input tabindex="245" type="text" id="a181" data-src="other_asset_na_of_lender_3" class="tabletd" value="'.$detail['other_asset_na_of_lender_3'].'"></td>
			    <td class="borderBottom">&nbsp;&nbsp;Monthly Loan Amount<br><input tabindex="246" type="text" id="a182" data-src="other_asset_monthly_loan_amount_3" class="tabletd" value="'.$detail['other_asset_monthly_loan_amount_3'].'"></td>
			    <td class="borderBottom">&nbsp;&nbsp;Loan Balance<br><input tabindex="247" type="text" id="a183" data-src="other_asset_load_balance_3" class="tabletd" value="'.$detail['other_asset_load_balance_3'].'"></td>
			    <td class="borderBottom">&nbsp;&nbsp;Date of final payment<br><input tabindex="248" type="text" id="a184" data-src="other_asset_date_of_final_payment_3" class="tabletd" value="'.$detail['other_asset_date_of_final_payment_3'].'"></td>
			   </tr>
			   <tr>
			   <tr>
			    <td class="borderBottom">Location of Property:</td>
			    <td class="borderBottom" colspan="3">   <input tabindex="249" type="text" id="a185" data-src="other_location_of_property_3" class="tabletd" value="'.$detail['other_location_of_property_3'].'"></td>
			   </tr>
			 </table>  
              
             <div style="margin-top:20px;">
			 <p> <i><b style="font-size:16px;">Credit Cards </b>–  list all bank-issued credit cards. If you need additional space, please attach a separate sheet.</i><p> 
			 </div> 
			 <div class="title">Available Credit #1</div>
			 <table style="width:100%;margin-top:20px;"> 
			    <tr>
				    <td class="borderBottom">Name of Credit Institution<br><input tabindex="250" type="text" id="a185" data-src="card_institution_1" class="tabletd" value="'.$detail['card_institution_1'].'"></td>
				    <td class="borderBottom">Account Number<br><input tabindex="251" type="text" id="a186" data-src="card_number_1" class="tabletd" value="'.$detail['card_number_1'].'"></td>
				    <td class="borderBottom">Credit Limit<br><input tabindex="252" type="text" id="a187" data-src="card_limit_1" class="tabletd" value="'.$detail['card_limit_1'].'"></td>
				    <td class="borderBottom">Amount Owed<br><input tabindex="253" type="text" id="a188" data-src="card_amount_owned_1" class="tabletd" value="'.$detail['card_amount_owned_1'].'"></td>
				    <td class="borderBottom">Available Credit<br><input tabindex="254" type="text" id="a189" data-src="card_available_credit_1" class="tabletd" value="'.$detail['card_available_credit_1'].'"></td>
			   </tr>
			 </table>
			 <div class="title">Available Credit #2</div>
			 <table style="width:100%;margin-top:20px;"> 
			    <tr>
				    <td class="borderBottom">Name of Credit Institution<br><input tabindex="255" type="text" id="a190" data-src="card_institution_2" class="tabletd" value="'.$detail['card_institution_2'].'"></td>
				    <td class="borderBottom">Account Number<br><input tabindex="256" type="text" id="a191" data-src="card_number_2" class="tabletd" value="'.$detail['card_number_2'].'"></td>
				    <td class="borderBottom">Credit Limit<br><input tabindex="257" type="text" id="a192" data-src="card_limit_2" class="tabletd" value="'.$detail['card_limit_2'].'"></td>
				    <td class="borderBottom">Amount Owed<br><input tabindex="258" type="text" id="a193" data-src="card_amount_owned_2" class="tabletd" value="'.$detail['card_amount_owned_2'].'"></td>
				    <td class="borderBottom">Available Credit<br><input tabindex="259" type="text" id="a194" data-src="card_available_credit_2" class="tabletd" value="'.$detail['card_available_credit_2'].'"></td>
			   </tr>
			 </table>
			 <div class="title">Available Credit #3</div>
			 <table style="width:100%;margin-top:20px;"> 
			    <tr>
				    <td class="borderBottom">Name of Credit Institution<br><input tabindex="260" type="text" id="a195" data-src="card_institution_3" class="tabletd" value="'.$detail['card_institution_3'].'"></td>
				    <td class="borderBottom">Account Number<br><input tabindex="261" type="text" id="a196" data-src="card_number_3" class="tabletd" value="'.$detail['card_number_3'].'"></td>
				    <td class="borderBottom">Credit Limit<br><input tabindex="262" type="text" id="a197" data-src="card_limit_3" class="tabletd" value="'.$detail['card_limit_3'].'"></td>
				    <td class="borderBottom">Amount Owed<br><input tabindex="263" type="text" id="a198" data-src="card_amount_owned_3" class="tabletd" value="'.$detail['card_amount_owned_3'].'"></td>
				    <td class="borderBottom">Available Credit<br><input tabindex="264" type="text" id="a199" data-src="card_available_credit_3" class="tabletd" value="'.$detail['card_available_credit_3'].'"></td>
			   </tr>
			 </table>
			 <div style="margin-top:20px;font-weight:bold;">TOTAL AVAILABLE CREDIT: $<input tabindex="265" type="text" id="a200" data-src="total_available_credit" class="tabletd w120 borderBottom" value="'.$detail['total_available_credit'].'"></div>

			<div style="margin-top:20px;">
			 <p> <b style="font-size:16px;">Section 4. Monthly Household Income and Expense Information</b><p> 
			 <p><i><b>Monthly Household Income</b> – please include information for yourself, your spouse, and anyone else who contributes to your household’s income.</i><p> 
			</div>
			<div class="title">INCOME</div>
			<table style="width:100%;"> 
			    <tr>
				    <td class="borderBottom tipsbold" colspan="2">PRIMARY TAXPAYER</td>
			    </tr>
			    <tr>
				    <td class="borderBottom padding20">Wages/Salaries1</td>
				    <td class="borderBottom" style="width:120px;">$<input tabindex="266" type="text" id="a201" data-src="income_primary_wages" class="tabletd w120" value="'.$detail['income_primary_wages'].'"></td>
			    </tr>
			    <tr>
				    <td class="borderBottom padding20">Social Security</td>
				    <td class="borderBottom">$<input tabindex="267" type="text" id="a202" data-src="income_primary_social_security" class="tabletd w120" value="'.$detail['income_primary_social_security'].'"></td>
			    </tr>
			    <tr>
				    <td class="borderBottom padding20">Pension(s)</td>
				    <td class="borderBottom">$<input tabindex="268" type="text" id="a203" data-src="income_primary_pension" class="tabletd w120" value="'.$detail['income_primary_pension'].'"></td>
			    </tr>
			    <tr>
				    <td class="borderBottom padding20">Other Income (e.g. unemployment, disability)</td>
				    <td class="borderBottom">$<input tabindex="269" type="text" id="a204" data-src="income_other_income" class="tabletd w120" value="'.$detail['income_other_income'].'"></td>
			    </tr>
			    <tr>
				    <td class="borderBottom tipsbold" colspan="2">SPOUSE/OTHER INCOME</td>
			    </tr>
			    <tr>
				    <td class="borderBottom padding20">SPOUSE/OTHER INCOME</td>
				    <td class="borderBottom">$<input tabindex="270" type="text" id="a201" data-src="income_spouse_wages" class="tabletd w120" value="'.$detail['income_spouse_wages'].'"></td>
			    </tr>
			    <tr>
				    <td class="borderBottom padding20">Social Security</td>
				    <td class="borderBottom">$<input tabindex="271" type="text" id="a202" data-src="income_spouse_social_security" class="tabletd w120" value="'.$detail['income_spouse_social_security'].'"></td>
			    </tr>
			    <tr>
				    <td class="borderBottom padding20">Pension(s)</td>
				    <td class="borderBottom">$<input tabindex="272" type="text" id="a203" data-src="income_spouse_pension" class="tabletd w120" value="'.$detail['income_spouse_pension'].'"></td>
			    </tr>
			    <tr>
				    <td class="borderBottom padding20">Other Income (e.g. unemployment, disability)</td>
				    <td class="borderBottom">$<input tabindex="273" type="text" id="a204" data-src="income_other_income_spouse" class="tabletd w120" value="'.$detail['income_other_income_spouse'].'"></td>
			    </tr>
			    <tr>
				    <td class="borderBottom ">Interest and Dividends</td>
				    <td class="borderBottom">$<input tabindex="274" type="text" id="a205" data-src="income_interest_dividend" class="tabletd w120" value="'.$detail['income_interest_dividend'].'"></td>
			    </tr>
			    <tr>
				    <td class="borderBottom ">Draws or Distributions (e.g. income from partnerships, sub-S Corporations, etc.)2</td>
				    <td class="borderBottom">$<input tabindex="275" type="text" id="a206" data-src="income_draws_distribution" class="tabletd w120" value="'.$detail['income_draws_distribution'].'"></td>
			    </tr>
			    <tr>
				    <td class="borderBottom ">Net Rental Income3</td>
				    <td class="borderBottom">$<input tabindex="276" type="text" id="a207" data-src="income_net_rental" class="tabletd w120" value="'.$detail['income_net_rental'].'"></td>
			    </tr>
			    <tr>
				    <td class="borderBottom ">Net Business Income4</td>
				    <td class="borderBottom">$<input tabindex="277" type="text" id="a208" data-src="income_net_business" class="tabletd w120" value="'.$detail['income_net_business'].'"></td>
			    </tr>
			    <tr>
				    <td class="borderBottom">Child Support Received</td>
				    <td class="borderBottom">$<input tabindex="278" type="text" id="a209" data-src="income_child_support_received" class="tabletd " value="'.$detail['income_child_support_received'].'"></td>
			    </tr>
			    <tr>
				    <td class="borderBottom">Alimony Received</td>
				    <td class="borderBottom">$<input tabindex="279" type="text" id="a210" data-src="income_alimony_received" class="tabletd" value="'.$detail['income_alimony_received'].'"></td>
			    </tr>
			</table>';
		 $income_additional=$detail['income_additional'];
         $income_additional_1='';
         $income_additional_2='';
         switch($income_additional){	
		    case 1:
		    $income_additional_1="checked";
			break;
		    case 2:
		    $income_additional_1="checked";
			break;
		 }	
		$html.='<div style="margin-top:20px;"><b><i>Are there additional sources of income used to support the household, e.g. non-liable spouse, or anyone else who may contribute to the household income, etc.?</i></b>
			        <input tabindex="280" type="checkbox" name="income_additional" value="1" class="fieldcheckbox" data-src="income_additional" '.$income_additional_1.'>Yes 
				    <input tabindex="281" type="checkbox" name="income_additional" value="2" class="fieldcheckbox" data-src="income_additional" '.$income_additional_2.'>No  </div>
             <div style="margin-top:20px;"><i>If answering “yes” above, please provide the amount of additional income with a brief explanation: </i>
			       Amount:$<input tabindex="282" type="text" id="a211" data-src="income_amount" class="tabletd borderBottom w120" value="'.$detail['income_amount'].'">,
			       <br>Brief explanation:<input tabindex="283" type="text" id="a212" data-src="income_breif_explanation" class="tabletd borderBottom" value="'.$detail['income_breif_explanation'].'" style="width:780px;"></div>
			<div style="margin-top:20px;"><i><b>Monthly Household Expenses </b>- enter your average monthly expenses.</i></div>
			<div class="title" style="margin-bottom:20px;">EXPENSES</div>
			<table style="width:100%;"> 
			    <tr>
				    <td class="borderBottom tipsbold" colspan="2">FOOD, CLOTHING, MISC</td>
			    </tr>
			    <tr>
				    <td class="borderBottom padding20">Food</td>
				    <td class="borderBottom" style="width:120px;">$<input tabindex="284" type="text" id="a213" data-src="expense_food" class="tabletd w120" value="'.$detail['expense_food'].'"></td>
			    </tr>
			    <tr>
				    <td class="borderBottom padding20">Housekeeping Supplies</td>
				    <td class="borderBottom" style="width:120px;">$<input tabindex="285" type="text" id="a214" data-src="expense_housekeeping" class="tabletd w120" value="'.$detail['expense_housekeeping'].'"></td>
			    </tr>
			    <tr>
				    <td class="borderBottom padding20">Apparel & Services</td>
				    <td class="borderBottom" style="width:120px;">$<input tabindex="286" type="text" id="a215" data-src="expense_apparel" class="tabletd w120" value="'.$detail['expense_apparel'].'"></td>
			    </tr>
			    <tr>
				    <td class="borderBottom padding20">Personal Care</td>
				    <td class="borderBottom" style="width:120px;">$<input tabindex="287" type="text" id="a216" data-src="expense_personal_care" class="tabletd w120" value="'.$detail['expense_personal_care'].'"></td>
			    </tr>
			    <tr>
				    <td class="borderBottom padding20">Miscellaneous</td>
				    <td class="borderBottom" style="width:120px;">$<input tabindex="288" type="text" id="a217" data-src="expense_miscellaneous" class="tabletd w120" value="'.$detail['expense_miscellaneous'].'"></td>
			    </tr>
			    <tr>
				    <td class="borderBottom tipsbold" colspan="2">HOUSING & UTILITIES</td>
			    </tr>
			    <tr>
				    <td class="borderBottom padding20">1st Lien Mortgage</td>
				    <td class="borderBottom" style="width:120px;">$<input tabindex="289" type="text" id="a219" data-src="expense_lien_mortage_1" class="tabletd w120" value="'.$detail['expense_lien_mortage_1'].'"></td>
			    </tr>
			    <tr>
				    <td class="borderBottom padding20">2nd Lien Mortgage</td>
				    <td class="borderBottom" style="width:120px;">$<input tabindex="290" type="text" id="a220" data-src="expense_lien_mortage_2" class="tabletd w120" value="'.$detail['expense_lien_mortage_2'].'"></td>
			    </tr>
			    <tr>
				    <td class="borderBottom padding20">Rent Payment</td>
				    <td class="borderBottom" style="width:120px;">$<input tabindex="291" type="text" id="a221" data-src="expense_rent_payment" class="tabletd w120" value="'.$detail['expense_rent_payment'].'"></td>
			    </tr>
			    <tr>
				    <td class="borderBottom padding20">Homeowner Insurance</td>
				    <td class="borderBottom" style="width:120px;">$<input tabindex="292" type="text" id="a222" data-src="expense_homeowner" class="tabletd w120" value="'.$detail['expense_homeowner'].'"></td>
			    </tr>
			    <tr>
				    <td class="borderBottom padding20">Property Tax</td>
				    <td class="borderBottom" style="width:120px;">$<input tabindex="293" type="text" id="a223" data-src="expense_property_tax" class="tabletd w120" value="'.$detail['expense_property_tax'].'"></td>
			    </tr>
			    <tr>
				    <td class="borderBottom padding20">Gas</td>
				    <td class="borderBottom" style="width:120px;">$<input tabindex="294" type="text" id="a224" data-src="expense_gas" class="tabletd w120" value="'.$detail['expense_gas'].'"></td>
			    </tr>
			    <tr>
				    <td class="borderBottom padding20">Electricity</td>
				    <td class="borderBottom" style="width:120px;">$<input tabindex="295" type="text" id="a225" data-src="expense_electricity" class="tabletd w120" value="'.$detail['expense_electricity'].'"></td>
			    </tr>
			    <tr>
				    <td class="borderBottom padding20">Water</td>
				    <td class="borderBottom" style="width:120px;">$<input tabindex="296" type="text" id="a226" data-src="expense_water" class="tabletd w120" value="'.$detail['expense_water'].'"></td>
			    </tr>
			    <tr>
				    <td class="borderBottom padding20">Sewer</td>
				    <td class="borderBottom" style="width:120px;">$<input tabindex="297" type="text" id="a227" data-src="expense_sewer" class="tabletd w120" value="'.$detail['expense_sewer'].'"></td>
			    </tr>
			    <tr>
				    <td class="borderBottom padding20">Cable</td>
				    <td class="borderBottom" style="width:120px;">$<input tabindex="298" type="text" id="a228" data-src="expense_cable" class="tabletd w120" value="'.$detail['expense_cable'].'"></td>
			    </tr>
			    <tr>
				    <td class="borderBottom padding20">Trash</td>
				    <td class="borderBottom" style="width:120px;">$<input tabindex="299" type="text" id="a229" data-src="expense_trash" class="tabletd w120" value="'.$detail['expense_trash'].'"></td>
			    </tr>
			    <tr>
				    <td class="borderBottom padding20">Phone</td>
				    <td class="borderBottom" style="width:120px;">$<input tabindex="300" type="text" id="a230" data-src="expense_phone" class="tabletd w120" value="'.$detail['expense_phone'].'"></td>
			    </tr>
			    <tr>
				    <td class="borderBottom tipsbold" colspan="2">AUTO/TRANSPORTATION EXPENSES</td>
			    </tr>
			    <tr>
				    <td class="borderBottom padding20">Public Transportation</td>
				    <td class="borderBottom" style="width:120px;">$<input tabindex="301" type="text" id="a231" data-src="expense_public_transportation" class="tabletd w120" value="'.$detail['expense_public_transportation'].'"></td>
			    </tr>
			    <tr>
				    <td class="borderBottom padding20">Auto/Lease payment - vehicle  #1</td>
				    <td class="borderBottom" style="width:120px;">$<input tabindex="302" type="text" id="a232" data-src="expense_vehicle_1" class="tabletd w120" value="'.$detail['expense_vehicle_1'].'"></td>
			    </tr>
			    <tr>
				    <td class="borderBottom padding20">Auto/Lease payment – vehicle  #2</td>
				    <td class="borderBottom" style="width:120px;">$<input tabindex="303" type="text" id="a233" data-src="expense_vehicle_2" class="tabletd w120" value="'.$detail['expense_vehicle_2'].'"></td>
			    </tr>
			    <tr>
				    <td class="borderBottom padding20">Auto Expense (maintenance/repairs)</td>
				    <td class="borderBottom" style="width:120px;">$<input tabindex="304" type="text" id="a234" data-src="expense_auto_expense" class="tabletd w120" value="'.$detail['expense_auto_expense'].'"></td>
			    </tr>
			    <tr>
				    <td class="borderBottom padding20">Auto Insurance</td>
				    <td class="borderBottom" style="width:120px;">$<input tabindex="305" type="text" id="a235" data-src="expense_auto_insurance" class="tabletd w120" value="'.$detail['expense_auto_insurance'].'"></td>
			    </tr>
			    <tr>
				    <td class="borderBottom tipsbold" colspan="2">HEALTH CARE</td>
			    </tr>
			    <tr>
				    <td class="borderBottom padding20">Health Insurance</td>
				    <td class="borderBottom" style="width:120px;">$<input tabindex="306" type="text" id="a236" data-src="expense_health_insurance" class="tabletd w120" value="'.$detail['expense_health_insurance'].'"></td>
			    </tr>
			    <tr>
				    <td class="borderBottom padding20">Prescriptions</td>
				    <td class="borderBottom" style="width:120px;">$<input tabindex="307" type="text" id="a237" data-src="expense_prescription" class="tabletd w120" value="'.$detail['expense_prescription'].'"></td>
			    </tr>
			    <tr>
				    <td class="borderBottom padding20">Co-Pays</td>
				    <td class="borderBottom" style="width:120px;">$<input tabindex="308" type="text" id="a238" data-src="expense_copy" class="tabletd w120" value="'.$detail['expense_copy'].'"></td>
			    </tr>
			    <tr>
				    <td class="borderBottom tipsbold" colspan="2">TAXES</td>
			    </tr>
			    <tr>
				    <td class="borderBottom padding20">Total of all Federal, State, Local Withholdings</td>
				    <td class="borderBottom" style="width:120px;">$<input tabindex="309" type="text" id="a239" data-src="expense_total_tax" class="tabletd w120" value="'.$detail['expense_total_tax'].'"></td>
			    </tr>
				<tr>
				    <td class="borderBottom tipsbold" colspan="2">OTHER EXPENSES</td>
			    </tr>
			    <tr>
				    <td class="borderBottom padding20">Court Ordered Payments</td>
				    <td class="borderBottom" style="width:120px;">$<input tabindex="310" type="text" id="a240" data-src="expense_court_ordered" class="tabletd w120" value="'.$detail['expense_court_ordered'].'"></td>
			    </tr>
				<tr>
				    <td class="borderBottom padding20">Child/Dependent Care</td>
				    <td class="borderBottom" style="width:120px;">$<input tabindex="311" type="text" id="a241" data-src="expense_child_care" class="tabletd w120" value="'.$detail['expense_child_care'].'"></td>
			    </tr>
				<tr>
				    <td class="borderBottom padding20">Whole Life Insurance Policy</td>
				    <td class="borderBottom" style="width:120px;">$<input tabindex="312" type="text" id="a242" data-src="expense_whole_life_insurance" class="tabletd w120" value="'.$detail['expense_whole_life_insurance'].'"></td>
			    </tr>
				<tr>
				    <td class="borderBottom padding20">Term Life Insurance Policy</td>
				    <td class="borderBottom" style="width:120px;">$<input tabindex="313" type="text" id="a243" data-src="expense_term_life_insurance" class="tabletd w120" value="'.$detail['expense_term_life_insurance'].'"></td>
			    </tr>
			</table>
			<div style="margin-top:20px;">
			  Are there any special circumstances that need to be considered in your housing, health care, child care, or other expenses? For example, does your child have a mental or physical condition requiring extra care? Does your home require a wheelchair entrance? Please consider whether your case fits within one of the following categories:
			</div>
			<div class="ma20">
			    A.	Long-term illness, medical condition, or disability renders you incapable of earning a living.<br>
				B.	Liquidation of assets will not allow you to meet basic living expenses.<br>
				C.	You are not able to borrow against the equity in your assets, and the sale of the assets would cause a severe financial hardship.<br>
			</div>
			<div>
			    If so, please describe the facts of your situation in the space below, or attach an additional sheet.
			</div>
			<div>
			  <textarea cols="140" rows="36" style="border:1px solid #000;" id="a244" data-src="special_circumstances" name="special_circumstances">'.$detail['special_circumstances'].'</textarea>
			</div>
			<div style="margin-top:20px;"><b>Section 5. Other Financial Information</b></div>';
		 $are_party_lawsuit=$detail['are_party_lawsuit'];
         $are_party_lawsuit_1='';
         $are_party_lawsuit_2='';
         switch($are_party_lawsuit){	
		    case 1:
		    $are_party_lawsuit_1="checked";
			break;
		    case 2:
		    $are_party_lawsuit_2="checked";
			break;
		 }	
		 $beneficiary_of_trust=$detail['beneficiary_of_trust'];
         $beneficiary_of_trust_1='';
         $beneficiary_of_trust_2='';
         switch($beneficiary_of_trust){	
		    case 1:
		    $beneficiary_of_trust_1="checked";
			break;
		    case 2:
		    $beneficiary_of_trust_1="checked";
			break;
		 }	 
		$html.='<table style="width:100%;"> 
			   <tr>
			      <td class="noborder borderTop borderLeft borderRight"></td>
				  <td colspan="4">
				    If yes, answer the following:
				  </td>
			   <tr>
			   <tr>
			      <td class="noborder borderLeft borderRight">
				   &nbsp;&nbsp;Are  you a party to a lawsuit?<br>
				    &nbsp;<input tabindex="314" type="checkbox" name="are_party_lawsuit" value="1" class="fieldcheckbox" data-src="are_party_lawsuit" '.$are_party_lawsuit_1.'>Yes 
				    &nbsp;<input tabindex="315" type="checkbox" name="are_party_lawsuit" value="2" class="fieldcheckbox" data-src="are_party_lawsuit" '.$are_party_lawsuit_2.'>No 
				  </td>
				  <td class="w120">&nbsp;Plaintiff<br>
				     <input tabindex="316" type="text" id="a244" data-src="plaintiff" class="tabletd " value="'.$detail['plaintiff'].'"><br>
					 &nbsp;Defendant<br>
					 <input tabindex="317" type="text" id="a245" data-src="defendant" class="tabletd " value="'.$detail['defendant'].'">
				  </td>
				  <td class="w120">&nbsp;Location of filing<br>
				     <input tabindex="318" type="text" id="a246" data-src="location_of_filing" class="tabletd " value="'.$detail['location_of_filing'].'">
				  </td>
				  <td class="w120">&nbsp;Represented by<br>
				     <input tabindex="319" type="text" id="a247" data-src="represented_by" class="tabletd " value="'.$detail['represented_by'].'">
				  </td>
				  <td class="w120">&nbsp;Docket/Case No.<br>
				     <input tabindex="320" type="text" id="a248" data-src="docket" class="tabletd " value="'.$detail['docket'].'">
				  </td>
			   <tr>
			   <tr>
			      <td class="noborder borderLeft borderRight borderBottom"></td>
				  <td>&nbsp;Amount of Suit <br>
				    $<input tabindex="321" type="text" id="a249" data-src="amount_of_suit" class="tabletd w120" value="'.$detail['amount_of_suit'].'">
				  </td>
				  <td colspan="2">&nbsp;Possible Completion Date<br>
				     <input tabindex="322" type="text" id="a250" data-src="possible_completion_date" class="tabletd" value="'.$detail['possible_completion_date'].'" style="width:240px;">
				  </td>
				  <td>&nbsp;Subject of Suit<br>
				     <input tabindex="323" type="text" id="a251" data-src="subject_of_suit" class="tabletd w120" value="'.$detail['subject_of_suit'].'">
				  </td>
			   <tr>
			   
			   <tr>
			      <td class="noborder borderLeft ">
				   &nbsp;&nbsp;Are you the beneficiary of a trust, estate, <br>or life insurance policy?<br>
				    &nbsp;<input tabindex="324" type="checkbox" name="beneficiary_of_trust" value="1" class="fieldcheckbox" data-src="beneficiary_of_trust" '.$beneficiary_of_trust_1.'>Yes 
				    &nbsp;<input tabindex="325" type="checkbox" name="beneficiary_of_trust" value="2" class="fieldcheckbox" data-src="beneficiary_of_trust" '.$beneficiary_of_trust_2.'>No 
				  </td>
				  <td class="w120" colspan="2">&nbsp;Place where recorded:<br>
				     <input tabindex="326" type="text" id="a252" data-src="place_recorded" class="tabletd " value="'.$detail['place_recorded'].'">
				  </td>
				  <td class="w120" colspan="2">&nbsp;EIN:<br>
				     <input tabindex="327" type="text" id="a253" style="width:240px;" data-src="beneficiary_ein" class="tabletd" value="'.$detail['beneficiary_ein'].'">
				  </td>
			   <tr>
			   <tr>
			      <td class="noborder borderLeft  borderBottom"></td>
				  <td colspan="2">&nbsp;Name of trust/policy <br>
				    <input tabindex="328" type="text" id="a254" data-src="name_of_trust" class="tabletd" style="width:240px;" value="'.$detail['name_of_trust'].'">
				  </td>
				  <td>&nbsp;Anticipated amount<br>
				     $<input tabindex="329" type="text" id="a255" data-src="anticipated_amount" class="tabletd w120" value="'.$detail['anticipated_amount'].'" style="width:240px;">
				  </td>
				  <td>&nbsp;Date to be received<br>
				     <input tabindex="330" type="text" id="a256" data-src="date_tobe_received" class="tabletd w120" value="'.$detail['date_tobe_received'].'">
				  </td>
			   <tr>';
			 $trustee_fiduciary_contributor=$detail['trustee_fiduciary_contributor'];
			 $trustee_fiduciary_contributor_1='';
			 $trustee_fiduciary_contributor_2='';
			 switch($trustee_fiduciary_contributor){	
				case 1:
				$trustee_fiduciary_contributor_1="checked";
				break;
				case 2:
				$trustee_fiduciary_contributor_2="checked";
				break;
			 }
			 $have_safe_deposit=$detail['have_safe_deposit'];
			 $have_safe_deposit_1='';
			 $have_safe_deposit_2='';
			 switch($have_safe_deposit){	
				case 1:
				$have_safe_deposit_1="checked";
				break;
				case 2:
				$have_safe_deposit_2="checked";
				break;
			 }
			
			 $is_in_bankruptcy=$detail['is_in_bankruptcy'];
			 $is_in_bankruptcy_1='';
			 $is_in_bankruptcy_2='';
			 switch($is_in_bankruptcy){	
				case 1:
				$is_in_bankruptcy_1="checked";
				break;
				case 2:
				$is_in_bankruptcy_2="checked";
				break;
			 }
			 $filed_bankruptcy=$detail['filed_bankruptcy'];
			 $filed_bankruptcy_1='';
			 $filed_bankruptcy_2='';
			 switch($filed_bankruptcy){	
				case 1:
				$filed_bankruptcy_1="checked";
				break;
				case 2:
				$filed_bankruptcy_2="checked";
				break;
			 }
			 $transferred_asset=$detail['transferred_asset'];
			 $transferred_asset_1='';
			 $transferred_asset_2='';
			 switch($transferred_asset){	
				case 1:
				$transferred_asset_1="checked";
				break;
				case 2:
				$transferred_asset_2="checked";
				break;
			 }
			 $lived_outside=$detail['lived_outside'];
			 $lived_outside_1='';
			 $lived_outside_2='';
			 switch($lived_outside){	
				case 1:
				$lived_outside_1="checked";
				break;
				case 2:
				$lived_outside_2="checked";
				break;
			 }
			   $html.='<tr>
			      <td class="noborder borderLeft borderBottom">
				   &nbsp;&nbsp;Are you a trustee, fiduciary, or contributor of a trust?<br>
				    &nbsp;<input tabindex="331" type="checkbox" name="trustee_fiduciary_contributor" value="1" class="fieldcheckbox" data-src="trustee_fiduciary_contributor" '.$trustee_fiduciary_contributor_1.'>Yes 
				    &nbsp;<input tabindex="332" type="checkbox" name="trustee_fiduciary_contributor" value="2" class="fieldcheckbox" data-src="trustee_fiduciary_contributor" '.$trustee_fiduciary_contributor_2.'>No 
				  </td>
				  <td class="w120" colspan="2">&nbsp;Name of the trust<br>
				    <input tabindex="333" type="text" id="a257" data-src="trustee_name_of_trust" style="width:240px;" class="tabletd" value="'.$detail['trustee_name_of_trust'].'">
				  </td>
				  <td class="w120" colspan="2">&nbsp;EIN:<br>
				     <input tabindex="334" type="text" id="a258" style="width:240px;" data-src="trustee_ein" class="tabletd" value="'.$detail['trustee_ein'].'">
				  </td>
			   <tr>
               <tr>
			      <td class="noborder borderLeft ">
				   &nbsp;&nbsp;Do you have a safe deposit box (business or personal)?<br>
				    &nbsp;<input tabindex="335" type="checkbox" name="have_safe_deposit" value="1" class="fieldcheckbox" data-src="have_safe_deposit" '.$have_safe_deposit_1.'>Yes 
				    &nbsp;<input tabindex="336" type="checkbox" name="have_safe_deposit" value="2" class="fieldcheckbox" data-src="have_safe_deposit" '.$have_safe_deposit_2.'>No 
				  </td>
				  <td class="w120" colspan="4">&nbsp;Location (name, address, and box numbers)<br>
				    <input tabindex="337" type="text" id="a259" data-src="deposit_location" style="width:560px;" class="tabletd" value="'.$detail['deposit_location'].'">
				  </td>
			   <tr>
			   <tr>
			      <td class="noborder borderLeft borderBottom"></td>
				  <td class="w120" colspan="2">&nbsp;Contents<br>
				    <input tabindex="338" type="text" id="a260" data-src="deposit_content" style="width:240px;" class="tabletd" value="'.$detail['deposit_content'].'">
				  </td>
				  <td class="w120" colspan="2">&nbsp;Value:<br>
				     $<input tabindex="339" type="text" id="a261" style="width:240px;" data-src="deposit_value" class="tabletd" value="'.$detail['deposit_value'].'">
				  </td>
			   <tr>
			   <tr>
			      <td class="noborder borderLeft borderBottom">
				   &nbsp;&nbsp;Are you currently in bankruptcy?<br>
				    &nbsp;<input tabindex="340" type="checkbox" name="is_in_bankruptcy" value="1" class="fieldcheckbox" data-src="is_in_bankruptcy" '.$is_in_bankruptcy_1.'>Yes 
				    &nbsp;<input tabindex="341" type="checkbox" name="is_in_bankruptcy" value="2" class="fieldcheckbox" data-src="is_in_bankruptcy" '.$is_in_bankruptcy_2.'>No 
				  </td>
				  <td class="w120" colspan="4">&nbsp;Have you filed bankruptcy in the past 10 years?<br>
				    &nbsp;<input tabindex="342" type="checkbox" name="filed_bankruptcy" value="1" class="fieldcheckbox" data-src="filed_bankruptcy" '.$filed_bankruptcy_1.'>Yes 
				    &nbsp;<input tabindex="343" type="checkbox" name="filed_bankruptcy" value="2" class="fieldcheckbox" data-src="filed_bankruptcy" '.$filed_bankruptcy_2.'>No 
				  </td>
			   <tr>
			   <tr>
			      <td class="noborder borderLeft borderBottom">
				     Bankruptcy discharge/dismissal date: <br>
					 <input tabindex="344" type="text" id="a262" style="width:240px;" data-src="bankruptcy_discharge_date" class="tabletd" value="'.$detail['bankruptcy_discharge_date'].'">
				  </td>
				  <td class="w120" colspan="2">&nbsp;Petition No.:<br>
				    <input tabindex="345" type="text" id="a263" data-src="petition_no" style="width:240px;" class="tabletd" value="'.$detail['petition_no'].'">
				  </td>
				  <td class="w120" colspan="2">&nbsp;Location filed:<br>
				     $<input tabindex="346" type="text" id="a264" style="width:240px;" data-src="location_field" class="tabletd" value="'.$detail['location_field'].'">
				  </td>
			   <tr>
			</table>
			<table style="width:100%;margin-top:10px;"> 
			  <tr>
			      <td class="noborder borderLeft borderTop borderBottom" rowspan="2">&nbsp;In the past 10 years, have you transferred any assets for less than <br>&nbsp;their full value?<br>
					&nbsp;<input tabindex="347" type="checkbox" name="transferred_asset" value="1" class="fieldcheckbox" data-src="transferred_asset" '.$transferred_asset_1.'>Yes 
				    &nbsp;<input tabindex="348" type="checkbox" name="transferred_asset" value="2" class="fieldcheckbox" data-src="transferred_asset" '.$transferred_asset_2.'>No 
				  </td>
				  <td colspan="3">&nbsp;List Assets:<br>
				    <input tabindex="349" type="text" id="a265" data-src="list_assets" style="width:560px;" class="tabletd" value="'.$detail['list_assets'].'">
				  </td>
			  </tr>
			  <tr>
				  <td>&nbsp;Value at transfer:<br>
				    <input tabindex="350" type="text" id="a266" data-src="value_at_transfer" class="tabletd" value="'.$detail['value_at_transfer'].'">
				  </td>
				  <td>&nbsp;Date of transfer:<br>
				    <input tabindex="351" type="text" id="a267" data-src="date_of_transfer" class="tabletd" value="'.$detail['date_of_transfer'].'">
				  </td>
				  <td>&nbsp;Location transferred:<br>
				    <input tabindex="352" type="text" id="a268" data-src="location_transferred"  class="tabletd" value="'.$detail['location_transferred'].'">
				  </td>
			  </tr>
			  <tr>
			     <td colspan="4">
				    &nbsp;Have you lived outside the U.S. for 6 months or longer in the past 10years?
				    &nbsp;<input tabindex="353" type="checkbox" name="lived_outside" value="1" class="fieldcheckbox" data-src="lived_outside" '.$lived_outside_1.'>Yes 
				    &nbsp;<input tabindex="354" type="checkbox" name="lived_outside" value="2" class="fieldcheckbox" data-src="lived_outside" '.$lived_outside_2.'>No 
				  <br>
				    &nbsp;If yes, dates: From：
                     <input tabindex="355" type="text" id="a269" data-src="lived_outside_from" class="tabletd borderBottom" style="width:240px;" value="'.$detail['lived_outside_from'].'"> 
					To：
					<input tabindex="356" type="text" id="a270" data-src="lived_outside_to" class="tabletd borderBottom" style="width:240px;" value="'.$detail['lived_outside_to'].'"> 
				 </td>
			  </tr>';
			 $funds_being_held=$detail['funds_being_held'];
			 $funds_being_held_1='';
			 $funds_being_held_2='';
			 switch($funds_being_held){	
				case 1:
				$funds_being_held_1="checked";
				break;
				case 2:
				$funds_being_held_2="checked";
				break;
			 }
			 $operate_a_business=$detail['operate_a_business'];
			 $operate_a_business_1='';
			 $operate_a_business_2='';
			 switch($operate_a_business){	
				case 1:
				$operate_a_business_1="checked";
				break;
				case 2:
				$operate_a_business_2="checked";
				break;
			 }
			 $is_a_sole_proprietorship=$detail['is_a_sole_proprietorship'];
			 $is_a_sole_proprietorship_1='';
			 $is_a_sole_proprietorship_2='';
			 switch($is_a_sole_proprietorship){	
				case 1:
				$is_a_sole_proprietorship_1="checked";
				break;
				case 2:
				$is_a_sole_proprietorship_2="checked";
				break;
			 } 
			 
			$html.='<tr>
			     <td colspan="4">
				    &nbsp;Do you have any funds being held in trust by a third party?
				    &nbsp;<input tabindex="357" type="checkbox" name="funds_being_held" value="1" class="fieldcheckbox" data-src="funds_being_held" '.$funds_being_held_1.'>Yes 
				    &nbsp;<input tabindex="358" type="checkbox" name="funds_being_held" value="2" class="fieldcheckbox" data-src="funds_being_held" '.$funds_being_held_2.'>No 
				  <br>
				    &nbsp;If yes, amount: $
                     <input tabindex="359" type="text" id="a271" data-src="funds_amount" class="tabletd borderBottom" style="width:240px;" value="'.$detail['funds_amount'].'"> 
					&nbsp;Location:
					<input tabindex="360" type="text" id="a272" data-src="funds_location" class="tabletd borderBottom" style="width:240px;" value="'.$detail['funds_location'].'"> 
				 </td>
			  </tr>
			</table>
			<div style="margin-top:20px;"><b>Section 6. Self-Employed Information</b></div>
			<div style="margin-top:10px;font-size:12px;"><b>Are you or your spouse self-employed (receive a Form 1099) or do you operate a business?</b>
			      &nbsp;<input tabindex="361" type="checkbox" name="operate_a_business" value="1" class="fieldcheckbox" data-src="operate_a_business" '.$operate_a_business_1.'>Yes 
				  &nbsp;<input tabindex="362" type="checkbox" name="operate_a_business" value="2" class="fieldcheckbox" data-src="operate_a_business" '.$operate_a_business_2.'>No </div>
			<div style="margin-top:10px;font-size:12px;"><b>Is the business a sole-proprietorship?</b>
			      &nbsp;<input tabindex="363" type="checkbox" name="is_a_sole_proprietorship" value="1" class="fieldcheckbox" data-src="is_a_sole_proprietorship" '.$is_a_sole_proprietorship_1.'>Yes (if yes, continue)
				  &nbsp;<input tabindex="364" type="checkbox" name="is_a_sole_proprietorship" value="2" class="fieldcheckbox" data-src="is_a_sole_proprietorship" '.$is_a_sole_proprietorship_2.'>No (if no, skip to section 7)</div>
			
			<table style="width:100%;margin-top:10px;"> 
			   <tr>
			     <td colspan="2">Name of Business<br>
				    <input tabindex="365" type="text" id="a273" data-src="name_of_business" class="tabletd" value="'.$detail['name_of_business'].'"> 
				 </td>
				 <td colspan="2" rowspan="2">Address of Business (street, city, state, zip code)<br>
				   <textarea id="a42" id="a274" tabindex="366" cols="70" rows="4" name="address_of_business" class="noborder"> '.$detail['address_of_business'].'</textarea>
				 </td>
			   </tr>
			   <tr>
			     <td colspan="2">Employer Identification Number<br>
				    <input tabindex="367" type="text" id="a275" data-src="employer_id" class="tabletd " value="'.$detail['employer_id'].'"> 
				 </td>
			   </tr>
			   <tr>
			     <td>Business Telephone Number<br>
				    <input tabindex="368" type="text" id="a276" data-src="business_phone" class="tabletd " value="'.$detail['business_phone'].'"> 
				 </td>
			     <td colspan="2">Business Web Site<br>
				    <input tabindex="369" type="text" id="a278" data-src="business_web" class="tabletd " value="'.$detail['business_web'].'"> 
				 </td>
				 <td>Trade Name or dba<br>
				    <input tabindex="370" type="text" id="a279" data-src="trade_name_dba" class="tabletd " value="'.$detail['trade_name_dba'].'"> 
				 </td>
			   </tr>
			   <tr>
			     <td>Description of Business<br>
				    <input tabindex="371" type="text" id="a280" data-src="description_of_business" class="tabletd " value="'.$detail['description_of_business'].'"> 
				 </td>
			     <td>Total Number of Employees<br>
				    <input tabindex="372" type="text" id="a281" data-src="total_employees" class="tabletd " value="'.$detail['total_employees'].'"> 
				 </td>
				 <td>Frequency of Tax Deposits<br>
				    <input tabindex="373" type="text" id="a282" data-src="frequency_of_tax_deposits" class="tabletd " value="'.$detail['frequency_of_tax_deposits'].'"> 
				 </td>
				 <td>Average Gross Monthly Payroll<br>
				    $<input tabindex="374" type="text" id="a283" data-src="average_gross_monthly_payroll" class="tabletd  w120" value="'.$detail['average_gross_monthly_payroll'].'"> 
				 </td>
			   </tr>';
			 $is_federal_contractor=$detail['is_federal_contractor'];
			 $is_federal_contractor_1='';
			 $is_federal_contractor_2='';
			 switch($is_federal_contractor){
				case 1:
				$is_federal_contractor_1="checked";
				break;
				case 2:
				$is_federal_contractor_2="checked";
				break;
			 }
             $does_in_internet_sales=$detail['does_in_internet_sales'];
			 $does_in_internet_sales_1='';
			 $does_in_internet_sales_2='';
			 switch($does_in_internet_sales){	
				case 1:
				$does_in_internet_sales_1="checked";
				break;
				case 2:
				$does_in_internet_sales_2="checked";
				break;
			 } 	
             $any_other_business=$detail['any_other_business'];
			 $any_other_business_1='';
			 $any_other_business_2='';
			 switch($any_other_business){	
				case 1:
				$any_other_business_1="checked";
				break;
				case 2:
				$any_other_business_2="checked";
				break;
			 }
          
			$html.='<tr>
			     <td colspan="2">Is the business a Federal contractor?<br>
				     &nbsp;<input tabindex="375" type="checkbox" name="is_federal_contractor" value="1" class="fieldcheckbox" data-src="is_federal_contractor" '.$is_federal_contractor_1.'>Yes 
				     &nbsp;<input tabindex="376" type="checkbox" name="is_federal_contractor" value="2" class="fieldcheckbox" data-src="is_federal_contractor" '.$is_federal_contractor_2.'>No 
				 </td>
				 <td colspan="2">Does the business engage in Internet sales?<br>
				     &nbsp;<input tabindex="377" type="checkbox" name="does_in_internet_sales" value="1" class="fieldcheckbox" data-src="does_in_internet_sales" '.$does_in_internet_sales_1.'>Yes 
				     &nbsp;<input tabindex="378" type="checkbox" name="does_in_internet_sales" value="2" class="fieldcheckbox" data-src="does_in_internet_sales" '.$does_in_internet_sales_2.'>No 
				     <br>
                    If yes, complete ‘Payment Processor’ information below
				 </td>
			   </tr>
			   <tr>
			     <td colspan="2">Do you or your spouse have any other business interests? <br>Include any interest in an LLC, LLP, corporation, partnership, etc.<br>
				     &nbsp;<input tabindex="379" type="checkbox" name="any_other_business" value="1" class="fieldcheckbox" data-src="any_other_business" '.$any_other_business_1.'>Yes 
				     &nbsp;<input tabindex="380" type="checkbox" name="any_other_business" value="2" class="fieldcheckbox" data-src="any_other_business" '.$any_other_business_2.'>No 
				    <br> If yes, What Title: $<input tabindex="381" type="text" id="a284" data-src="business_title" class="tabletd borderBottom w120" value="'.$detail['business_title'].'"> 
				 </td>
				 <td colspan="2">Business Address (street, city, state, zip code)<br>
				     <textarea id="a285" tabindex="382" cols="75" rows="4" name="business_address" class="noborder">'.$detail['business_address'].'</textarea>
				 </td>
			   </tr>
			</table>
			<div style="margin-top:20px;"><b>Section 7. Business Asset Information (for self-employed)</b></div>
			<div style="margin-top:10px;font-size:12px;"><i><b>Business Bank Accounts –</b>
			      include all checking, online bank accounts, money market accounts, savings accounts, stored value cards (e.g. payroll cards, government benefit cards, etc.) If you need additional space, please attach a separate sheet.</i></div>
			<div style="margin-top:10px;font-size:12px;"><b>Total Business Cash on Hand (includes any money that your business has that is not in a bank):</b><br>
			$<input tabindex="383" type="text" id="a286" data-src="business_asset_total_cash" class="tabletd borderBottom w120" value="'.$detail['business_asset_total_cash'].'"> 
			</div>   
			<div style="height:50px;line-height: 50px;">Account #1</div>
			<div style="border:1px solid #000;padding:5px;">
			  <table style="width:100%;">  
			     <tr>
					 <td class="noborder w100">Type of Account:</td>
					 <td class="noborder borderBottom"><input tabindex="384" type="text" id="a287" data-src="business_asset_type_of_account_1" class="tabletd" value="'.$detail['business_asset_type_of_account_1'].'"></td>
					 <td class="noborder w100">Bank Name:</td>
					 <td class="noborder borderBottom"><input tabindex="385" type="text" id="a288" data-src="business_asset_bank_name_1" class="tabletd" value="'.$detail['business_asset_bank_name_1'].'"></td>
					 <td class="noborder w100">Account #:</td>
					 <td class="noborder borderBottom"><input tabindex="386" type="text" id="a289" data-src="business_asset_account_1" class="tabletd" value="'.$detail['business_asset_account_1'].'"></td>
				  </tr>
				  <tr>
					 <td class="noborder w100">Bank Address:</td>
					 <td class="noborder borderBottom" colspan="3"><input tabindex="387" type="text" id="a290" data-src="business_asset_bank_address_1" class="tabletd" value="'.$detail['business_asset_bank_address_1'].'"></td></td>
					 <td class="noborder w100">Current Balance: </td>
					 <td class="noborder borderBottom">$<input tabindex="388" type="text" id="a291" data-src="business_asset_balance_1" class="tabletd w100" value="'.$detail['business_asset_balance_1'].'"></td>
				  </tr>
			  </table>
			</div>
			<div class="title">Account #2</div>
			<div style="border:1px solid #000;padding:5px;">
			  <table style="width:100%;">  
			     <tr>
					 <td class="noborder w100">Type of Account:</td>
					 <td class="noborder borderBottom"><input tabindex="389" type="text" id="a292" data-src="business_asset_type_of_account_2" class="tabletd" value="'.$detail['business_asset_type_of_account_2'].'"></td>
					 <td class="noborder w100">Bank Name:</td>
					 <td class="noborder borderBottom"><input tabindex="390" type="text" id="a293" data-src="business_asset_bank_name_2" class="tabletd" value="'.$detail['business_asset_bank_name_2'].'"></td>
					 <td class="noborder w100">Account #:</td>
					 <td class="noborder borderBottom"><input tabindex="391" type="text" id="a294" data-src="business_asset_account_2" class="tabletd" value="'.$detail['business_asset_account_2'].'"></td>
				  </tr>
				  <tr>
					 <td class="noborder w100">Bank Address:</td>
					 <td class="noborder borderBottom" colspan="3"><input tabindex="392" type="text" id="a295" data-src="business_asset_bank_address_2" class="tabletd" value="'.$detail['business_asset_bank_address_2'].'"></td></td>
					 <td class="noborder w100">Current Balance: </td>
					 <td class="noborder borderBottom">$<input tabindex="393" type="text" id="a296" data-src="business_asset_balance_2" class="tabletd w100" value="'.$detail['business_asset_balance_2'].'"></td>
				  </tr>
			  </table>
			</div>
			<div style="margin-top:10px;font-size:12px;"><i><b>Accounts/Notes Receivable –</b>
			      include e-payment accounts receivable and factoring companies, and any bartering or online auction accounts. List all contracts separately, including contracts awarded, but not yet started. Include Federal Government Contracts. If you need additional space, please attach a separate sheet.</i></div>
			<div style="height:50px;line-height: 50px;">Account #1</div>	  
			<table style="width:100%;">  
			     <tr>
					 <td colspan="2">Accounts/Notes Receivable<br>
					    <input tabindex="394" type="text" id="a297" data-src="e_account_notes_1" class="tabletd" value="'.$detail['e_account_notes_1'].'"></td>
					 <td colspan="2">Address (street, city, state, zip code)<br>
					    <input tabindex="395" type="text" id="a298" data-src="e_account_address_1" class="tabletd" value="'.$detail['e_account_address_1'].'"></td>
				 </tr>
				 <tr>
					 <td>Status (age, factored, other)<br>
					    <input tabindex="396" type="text" id="a299" data-src="e_account_status_1" class="tabletd" value="'.$detail['e_account_status_1'].'"></td>
					 <td>Date Due<br><input tabindex="397" type="text" id="a293" data-src="e_account_date_due_1" class="tabletd" value="'.$detail['e_account_date_due_1'].'"></td>
					 <td class="w120">Invoice Number or Federal Government Contract Number<br><input tabindex="398" type="text" id="a300" data-src="e_account_inovice_no_1" class="tabletd" value="'.$detail['e_account_inovice_no_1'].'"></td>
					 <td class="noborder borderBottom">Amount Due<br><input tabindex="399" type="text" id="a301" data-src="e_account_amount_due_1" class="tabletd" value="'.$detail['e_account_amount_due_1'].'"></td>
				 </tr>
			</table>	
            <div class="title" style="margin-top:80px;">Account #2</div>	  
			<table style="width:100%;">  
			     <tr>
					 <td colspan="2">Accounts/Notes Receivable<br>
					    <input tabindex="400" type="text" id="a297" data-src="e_account_notes_2" class="tabletd" value="'.$detail['e_account_notes_2'].'"></td>
					 <td colspan="2">Address (street, city, state, zip code)<br>
					    <input tabindex="401" type="text" id="a298" data-src="e_account_address_2" class="tabletd" value="'.$detail['e_account_address_2'].'"></td>
				 </tr>
				 <tr>
					 <td>Status (age, factored, other)<br>
					    <input tabindex="402" type="text" id="a299" data-src="e_account_status_2" class="tabletd" value="'.$detail['e_account_status_2'].'"></td>
					 <td>Date Due<br><input tabindex="403" type="text" id="a293" data-src="e_account_date_due_2" class="tabletd" value="'.$detail['e_account_date_due_2'].'"></td>
					 <td class="w120">Invoice Number or Federal Government Contract Number<br><input tabindex="404" type="text" id="a300" data-src="e_account_inovice_no_2" class="tabletd" value="'.$detail['e_account_inovice_no_2'].'"></td>
					 <td class="noborder borderBottom">Amount Due<br><input tabindex="405" type="text" id="a301" data-src="e_account_amount_due_2" class="tabletd" value="'.$detail['e_account_amount_due_2'].'"></td>
				 </tr>
			</table>
            <div style="margin-top:10px;font-size:12px;"><i><b>Other Assets</b>
			       list all tools, books, machinery, equipment, inventory, or other assets used in your business. Also include a list of all intangible assets such as licenses, patents, domain names, copyrights, trademarks, etc. If you need additional space, please attach a separate sheet.</i></div>	
            <div class="title">Asset #1</div>	  
			<table style="width:100%;">  
			     <tr>
					 <td colspan="2">Description of Asset<br>
					    <input tabindex="406" type="text" id="a302" data-src="b_other_asset_description_1" class="tabletd" value="'.$detail['b_other_asset_description_1'].'"></td>
					 <td>Purchase/Lease Date<br>
					    <input tabindex="407" type="text" id="a303" data-src="b_other_asset_purchase_date_1" class="tabletd" value="'.$detail['b_other_asset_purchase_date_1'].'"></td>	
					 <td>Current Value<br>
					    $<input tabindex="408" type="text" id="304" data-src="b_other_asset_current_value_1" class="tabletd w120" value="'.$detail['b_other_asset_current_value_1'].'"></td>
					<td>Loan Balance<br>
					    $<input tabindex="409" type="text" id="305" data-src="b_other_asset_loan_balance_1" class="tabletd w120" value="'.$detail['b_other_asset_loan_balance_1'].'"></td>
				 </tr> 
				 <tr>
					 <td>Amount of Monthly Payment<br>
					    $<input tabindex="410" type="text" id="a306" data-src="b_other_asset_amount_of_payment_1" class="tabletd w100" value="'.$detail['b_other_asset_amount_of_payment_1'].'"></td>
					 <td>Date of Final Payment<br>$<input tabindex="411" type="text" id="a307" data-src="b_other_date_of_final_payment_1" class="tabletd w100" value="'.$detail['b_other_date_of_final_payment_1'].'"></td>
					 <td colspan="3">Location of Asset (street, city, state, zip code)<br>
					 <textarea id="a308" tabindex="412" cols="70" rows="4" name="b_other_asset_location_1" class="noborder" data-src="b_other_asset_location_1"> '.$detail['b_other_asset_location_1'].'</textarea></td>
				 </tr>
				 <tr>
					 <td colspan="5">Lender/Leaser/Landlord Name and Address (street, city, state, zip code)<br>
					 <textarea id="a309" tabindex="413" cols="70" rows="4" name="b_other_asset_name_address_1" class="noborder" data-src="b_other_asset_name_address_1"> '.$detail['b_other_asset_name_address_1'].'</textarea>
					 </td>
				 </tr>	 
			</table>		
            <div class="title">Asset #2</div>	  
			<table style="width:100%;">  
			     <tr>
					 <td colspan="2">Description of Asset<br>
					    <input tabindex="414" type="text" id="a310" data-src="b_other_asset_description_2" class="tabletd" value="'.$detail['b_other_asset_description_2'].'"></td>
					 <td>Purchase/Lease Date<br>
					    <input tabindex="415" type="text" id="a311" data-src="b_other_asset_purchase_date_2" class="tabletd" value="'.$detail['b_other_asset_purchase_date_2'].'"></td>	
					 <td>Current Value<br>
					    $<input tabindex="416" type="text" id="312" data-src="b_other_asset_current_value_2" class="tabletd w120" value="'.$detail['b_other_asset_current_value_2'].'"></td>
					<td>Loan Balance<br>
					    $<input tabindex="417" type="text" id="313" data-src="b_other_asset_loan_balance_2" class="tabletd w120" value="'.$detail['b_other_asset_loan_balance_2'].'"></td>
				 </tr> 
				 <tr>
					 <td>Amount of Monthly Payment<br>
					    $<input tabindex="418" type="text" id="a314" data-src="b_other_asset_amount_of_payment_2" class="tabletd w100" value="'.$detail['b_other_asset_amount_of_payment_2'].'"></td>
					 <td>Date of Final Payment<br>$<input tabindex="419" type="text" id="a315" data-src="b_other_date_of_final_payment_2" class="tabletd w100" value="'.$detail['b_other_date_of_final_payment_2'].'"></td>
					 <td colspan="3">Location of Asset (street, city, state, zip code)<br>
					 <textarea id="a316" tabindex="420" cols="70" rows="4" name="b_other_asset_location_2" class="noborder" data-src="b_other_asset_location_2"> '.$detail['b_other_asset_location_2'].'</textarea></td>
				 </tr>
				 <tr>
					 <td colspan="5">Lender/Leaser/Landlord Name and Address (street, city, state, zip code)<br>
					 <textarea id="a317" tabindex="421" cols="70" rows="4" name="b_other_asset_name_address_2" class="noborder" data-src="b_other_asset_name_address_2"> '.$detail['b_other_asset_name_address_2'].'</textarea>
					 </td>
				 </tr>	 
			</table>	
            <div  class="title">Asset #3</div>	  
			<table style="width:100%;">  
			     <tr>
					 <td colspan="2">Description of Asset<br>
					    <input tabindex="422" type="text" id="a318" data-src="b_other_asset_description_3" class="tabletd" value="'.$detail['b_other_asset_description_3'].'"></td>
					 <td>Purchase/Lease Date<br>
					    <input tabindex="423" type="text" id="a319" data-src="b_other_asset_purchase_date_3" class="tabletd" value="'.$detail['b_other_asset_purchase_date_3'].'"></td>	
					 <td>Current Value<br>
					    $<input tabindex="424" type="text" id="320" data-src="b_other_asset_current_value_3" class="tabletd w120" value="'.$detail['b_other_asset_current_value_3'].'"></td>
					<td>Loan Balance<br>
					    $<input tabindex="425" type="text" id="321" data-src="b_other_asset_loan_balance_3" class="tabletd w120" value="'.$detail['b_other_asset_loan_balance_3'].'"></td>
				 </tr> 
				 <tr>
					 <td>Amount of Monthly Payment<br>
					    $<input tabindex="426" type="text" id="a322" data-src="b_other_asset_amount_of_payment_3" class="tabletd w100" value="'.$detail['b_other_asset_amount_of_payment_3'].'"></td>
					 <td>Date of Final Payment<br>$<input tabindex="427" type="text" id="a323" data-src="b_other_date_of_final_payment_3" class="tabletd w100" value="'.$detail['b_other_date_of_final_payment_3'].'"></td>
					 <td colspan="3">Location of Asset (street, city, state, zip code)<br>
					 <textarea id="a324" tabindex="428" cols="70" rows="4" name="b_other_asset_location_3" class="noborder" data-src="b_other_asset_location_3"> '.$detail['b_other_asset_location_3'].'</textarea></td>
				 </tr>
				 <tr>
					 <td colspan="5">Lender/Leaser/Landlord Name and Address (street, city, state, zip code)<br>
					 <textarea id="a325" tabindex="429" cols="70" rows="4" name="b_other_asset_name_address_3" class="noborder" data-src="b_other_asset_name_address_3"> '.$detail['b_other_asset_name_address_3'].'</textarea>
					 </td>
				 </tr>	 
			</table>	
            <div style="margin-top:20px;"><b>Section 8. Business Income and Expense Information (for self-employed)</b></div>
			<div style="margin-top:10px;font-size:12px;"><i>If self-employed, please attach a copy of Profit & Loss Statement from accountant or accounting software (e.g. Quickbooks), if available. Otherwise, complete the following.</i></div>
            <div style="margin-top:10px;font-size:12px;">tatement of Income for the period
			   <input tabindex="430" type="text" id="326" data-src="period_from" class="tabletd w120" value="'.$detail['period_from'].'">
			   (mm/dd/yyyy) to
			   <input tabindex="431" type="text" id="327" data-src="period_to" class="tabletd w120" value="'.$detail['period_to'].'">(mm/dd/yyyy)
			</div>
			<div style="margin-top:10px;font-size:12px;"><b><i>Business Income – </i>you may average 6-12 months of income/receipts to determine your Gross monthly income/receipts.</b></div>
			<table style="width:100%;">
			   <tr>
				 <td>Gross Receipts</td>
				 <td>Gross Rental</td>
				 <td>Interest Income</td>
				 <td>Dividends</td>
				 <td>Other Income</td>
			   </tr>
			   <tr>
				 <td><input tabindex="432" type="text" id="328" data-src="gross_receipts" class="tabletd w120" value="'.$detail['gross_receipts'].'"></td>
				 <td><input tabindex="433" type="text" id="329" data-src="gross_rental" class="tabletd w120" value="'.$detail['gross_rental'].'"></td>
				 <td><input tabindex="434" type="text" id="330" data-src="interest_income" class="tabletd w120" value="'.$detail['interest_income'].'"></td>
				 <td><input tabindex="435" type="text" id="331" data-src="dividends" class="tabletd w120" value="'.$detail['dividends'].'"></td>
				 <td><input tabindex="436" type="text" id="332" data-src="other_income" class="tabletd w120" value="'.$detail['other_income'].'"></td>
			   </tr>
			</table>
			<div style="margin-top:10px;font-size:12px;"><b><i>Business Expenses – </i>you may average 6-12 months of expenses to determine your average expenses. If you need additional space, please attach a separate sheet.</b></div>
			<table style="width:100%;">
			   <tr>
				 <td colspan="2">Materials purchased (e.g., items directly related to the production of a product or service)</td>
				 <td class="w100">$<input tabindex="437" type="text" id="333" data-src="be_material_purchased" class="tabletd w100" value="'.$detail['be_material_purchased'].'"></td>
			   </tr>
			   <tr>
				 <td colspan="2">Inventory purchased (e.g. goods bought for resale)</td>
				 <td class="w100">$<input tabindex="438" type="text" id="334" data-src="be_inventory_purchased" class="tabletd w100" value="'.$detail['be_inventory_purchased'].'"></td>
			   </tr>
			   <tr>
				 <td colspan="2">Gross wages and salaries</td>
				 <td class="w100">$<input tabindex="439" type="text" id="335" data-src="be_gross_wages_salaries" class="tabletd w100" value="'.$detail['be_gross_wages_salaries'].'"></td>
			   </tr>
			   <tr>
				 <td colspan="2">Rent</td>
				 <td class="w100">$<input tabindex="440" type="text" id="336" data-src="be_rent" class="tabletd w100" value="'.$detail['be_rent'].'"></td>
			   </tr>
			   <tr>
				 <td colspan="2">Supplies (items used to conduct business and used up within one year, e.g. books, office supplies, equipment, etc.)</td>
				 <td class="w100">$<input tabindex="441" type="text" id="337" data-src="be_supplies" class="tabletd w100" value="'.$detail['be_supplies'].'"></td>
			   </tr>
			   <tr>
				 <td colspan="2">Utilities/telephone</td>
				 <td class="w100">$<input tabindex="442" type="text" id="338" data-src="be_utilities_telephone" class="tabletd w100" value="'.$detail['be_utilities_telephone'].'"></td>
			   </tr>
			   <tr>
				 <td colspan="2">Vehicle costs (gas, oil, repairs, maintenance)</td>
				 <td class="w100">$<input tabindex="443" type="text" id="339" data-src="be_vehicle_costs" class="tabletd w100" value="'.$detail['be_vehicle_costs'].'"></td>
			   </tr>
			   <tr>
				 <td colspan="2">Business insurance</td>
				 <td class="w100">$<input tabindex="444" type="text" id="340" data-src="be_business_insurance" class="tabletd w100" value="'.$detail['be_business_insurance'].'"></td>
			   </tr>
			   <tr>
				 <td colspan="2">Current business taxes (e.g. real estate, excise, franchise, occupational, personal property, sales)</td>
				 <td class="w100">$<input tabindex="445" type="text" id="341" data-src="be_current_business_taxes" class="tabletd w100" value="'.$detail['be_current_business_taxes'].'"></td>
			   </tr>
			   <tr>
				 <td colspan="2">Other secured debts (not credit cards)</td>
				 <td class="w100">$<input tabindex="446" type="text" id="342" data-src="be_other_secured_debts" class="tabletd w100" value="'.$detail['be_other_secured_debts'].'"></td>
			   </tr>
			   <tr>
			     <td rowspan="27">Other</td>
				 <td>Accounting</td>
				 <td class="w100">$<input tabindex="447" type="text" id="343" data-src="other_accounting" class="tabletd w100" value="'.$detail['other_accounting'].'"></td>
			   </tr>
			   <tr>
				 <td>Advertising</td>
				 <td class="w100">$<input tabindex="448" type="text" id="344" data-src="other_advertising" class="tabletd w100" value="'.$detail['other_advertising'].'"></td>
			   </tr>
			   <tr>
				 <td>Bad debts</td>
				 <td class="w100">$<input tabindex="449" type="text" id="345" data-src="other_bad_debts" class="tabletd w100" value="'.$detail['other_bad_debts'].'"></td>
			   </tr>
			   <tr>
				 <td>Bank Charges</td>
				 <td class="w100">$<input tabindex="450" type="text" id="346" data-src="other_bank_charges" class="tabletd w100" value="'.$detail['other_bank_charges'].'"></td>
			   </tr>
			   <tr>
				 <td>Commissions</td>
				 <td class="w100">$<input tabindex="451" type="text" id="347" data-src="other_commissions" class="tabletd w100" value="'.$detail['other_commissions'].'"></td>
			   </tr>
			   <tr>
				 <td>Contract Labor</td>
				 <td class="w100">$<input tabindex="452" type="text" id="348" data-src="other_contract_labor" class="tabletd w100" value="'.$detail['other_contract_labor'].'"></td>
			   </tr>
			   <tr>
				 <td>Delivery & Freight</td>
				 <td class="w100">$<input tabindex="453" type="text" id="348" data-src="other_delivery_freight" class="tabletd w100" value="'.$detail['other_delivery_freight'].'"></td>
			   </tr>
			   <tr>
				 <td>Draws/Distributions</td>
				 <td class="w100">$<input tabindex="454" type="text" id="348" data-src="other_draws" class="tabletd w100" value="'.$detail['other_draws'].'"></td>
			   </tr>
			   <tr>
				 <td>Dues & Subscriptions</td>
				 <td class="w100">$<input tabindex="455" type="text" id="348" data-src="other_dues" class="tabletd w100" value="'.$detail['other_dues'].'"></td>
			   </tr>
			   <tr>
				 <td>Entertainment</td>
				 <td class="w100">$<input tabindex="456" type="text" id="348" data-src="other_entertainment" class="tabletd w100" value="'.$detail['other_entertainment'].'"></td>
			   </tr>
			   <tr>
				 <td>Interest</td>
				 <td class="w100">$<input tabindex="457" type="text" id="348" data-src="other_interest" class="tabletd w100" value="'.$detail['other_interest'].'"></td>
			   </tr>
			   <tr>
				 <td>Janitorial</td>
				 <td class="w100">$<input tabindex="458" type="text" id="348" data-src="other_janitorial" class="tabletd w100" value="'.$detail['other_janitorial'].'"></td>
			   </tr>
			   <tr>
				 <td>Laundry & Cleaning</td>
				 <td class="w100">$<input tabindex="459" type="text" id="348" data-src="other_laundry" class="tabletd w100" value="'.$detail['other_laundry'].'"></td>
			   </tr>
			   <tr>
				 <td>Legal & Professional</td>
				 <td class="w100">$<input tabindex="460" type="text" id="348" data-src="other_legal" class="tabletd w100" value="'.$detail['other_legal'].'"></td>
			   </tr>
			   <tr>
				 <td>Licenses & Permits</td>
				 <td class="w100">$<input tabindex="461" type="text" id="348" data-src="other_licenses" class="tabletd w100" value="'.$detail['other_licenses'].'"></td>
			   </tr>
			   <tr>
				 <td>Meals</td>
				 <td class="w100">$<input tabindex="462" type="text" id="348" data-src="other_meals" class="tabletd w100" value="'.$detail['other_meals'].'"></td>
			   </tr>
			   <tr>
				 <td>Miscellaneous</td>
				 <td class="w100">$<input tabindex="463" type="text" id="348" data-src="other_miscellaneous" class="tabletd w100" value="'.$detail['other_miscellaneous'].'"></td>
			   </tr>
			   <tr>
				 <td>Office Expense</td>
				 <td class="w100">$<input tabindex="464" type="text" id="348" data-src="other_office_expense" class="tabletd w100" value="'.$detail['other_office_expense'].'"></td>
			   </tr>
			   <tr>
				 <td>Parking & Tolls</td>
				 <td class="w100">$<input tabindex="465" type="text" id="348" data-src="other_parking_tolls" class="tabletd w100" value="'.$detail['other_parking_tolls'].'"></td>
			   </tr>
			   <tr>
				 <td>Postage</td>
				 <td class="w100">$<input tabindex="466" type="text" id="348" data-src="other_postage" class="tabletd w100" value="'.$detail['other_postage'].'"></td>
			   </tr>
			   <tr>
				 <td>Printing</td>
				 <td class="w100">$<input tabindex="467" type="text" id="348" data-src="other_printing" class="tabletd w100" value="'.$detail['other_printing'].'"></td>
			   </tr>
			   <tr>
				 <td>Promotion</td>
				 <td class="w100">$<input tabindex="468" type="text" id="348" data-src="other_promotion" class="tabletd w100" value="'.$detail['other_promotion'].'"></td>
			   </tr>
			   <tr>
				 <td>Repairs</td>
				 <td class="w100">$<input tabindex="469" type="text" id="348" data-src="other_repairs" class="tabletd w100" value="'.$detail['other_repairs'].'"></td>
			   </tr>
			   <tr>
				 <td>Security</td>
				 <td class="w100">$<input tabindex="470" type="text" id="348" data-src="other_security" class="tabletd w100" value="'.$detail['other_security'].'"></td>
			   </tr>
			   <tr>
				 <td>Taxes – Payroll</td>
				 <td class="w100">$<input tabindex="471" type="text" id="348" data-src="other_taxes" class="tabletd w100" value="'.$detail['other_taxes'].'"></td>
			   </tr>
			   <tr>
				 <td>Travel – airfare, hotel, etc.</td>
				 <td class="w100">$<input tabindex="472" type="text" id="348" data-src="other_travel" class="tabletd w100" value="'.$detail['other_travel'].'"></td>
			   </tr>
			   <tr>
				 <td>Uniforms</td>
				 <td class="w100">$<input tabindex="473" type="text" id="348" data-src="other_uniforms" class="tabletd w100" value="'.$detail['other_uniforms'].'"></td>
			   </tr>
			   <tr>
				 <td colspan="2">Total Expenses</td>
				 <td class="w100">$<input tabindex="474" type="text" id="348" data-src="total_expenses" class="tabletd w100" value="'.$detail['total_expenses'].'"></td>
			   </tr>
			</table>
			<div style="margin-top:20px;"><b>Section 9. Certification</b></div>
			<div style="margin-top:10px;">Under penalty of perjury, I declare that I have examined the information provided in this statement and all other documents included with this questionnaire, and that, to the best of my knowledge and belief, they are true, correct, and complete.</div>
			<table>
			   <tr>
			      <td id="sign">Taxpayer’s Signature:';
				    $old_sign_con='/var/www/vhosts/taxprep.republictax.com/httpdocs/public/'.$detail['taxpayer_sign'];
					if (is_file($old_sign_con)&&file_exists($old_sign_con)) {
					 //  $html.='<img src="'.$detail['taxpayer_sign'].'" id="signimg" style="width:220px;">';
					   $html.='<img src="/var/www/vhosts/taxprep.republictax.com/httpdocs/public/'.$detail['taxpayer_sign'].'" style="width:220px;height:50px;">';
				     }else{
					   $html.='<img src="/var/www/vhosts/taxprep.republictax.com/httpdocs/public/static/images/sign.png" id="signimg" style="width:220px;">';
				     }
					  $html.='</td>
				  <td>Date:<input tabindex="475" type="text" id="348" data-src="taxpayer_sign_date" class="tabletd w100" value="'.$detail['taxpayer_sign_date'].'"></td>
			   </tr>
			   <tr>
			      <td id="sign2">Spouse’s Signature:';
				    $old_sign_con2='/var/www/vhosts/taxprep.republictax.com/httpdocs/public/'.$detail['spouse_sign'];
					 if (is_file($old_sign_con2)&&file_exists($old_sign_con2)) {
					  // $html.='<img src="'.$detail['spouse_sign'].'" id="signimg2" style="width:220px;">';
					   $html.='<img src="/var/www/vhosts/taxprep.republictax.com/httpdocs/public/'.$detail['spouse_sign'].'" style="width:220px;height:50px;">';
				     }else{
					   $html.='<img src="/var/www/vhosts/taxprep.republictax.com/httpdocs/public/static/images/sign.png" id="signimg" style="width:220px;">';
					   }
				     $html.='</td>
				  <td>Date:<input tabindex="476" type="text" id="348" data-src="spouse_sign_date" class="tabletd w100" value="'.$detail['spouse_sign_date'].'"></td>
			   </tr>
			</table>
		</div>
	</body>
	</html>';

	return $html;
	}
	public function cm_topdf(){
      // $link="k0ArSMziYDVZ8IwixdW3fsAhumj5";
        
        //复制完后生成pdf,发送后删除 
		$link=$_GET['link'];
		$map['link']=$_GET['link'];
	   $map['plan']='cm';
       $res=DB::name('users_clients')->where($map)->field('id')->find();
       
        if($res){
			$path='upload/cm/'.$link;
			//先挪动文件至temp目录下，先创建当前目录下的temp目录
			$targetDirectory ='/var/www/vhosts/taxprep.republictax.com/httpdocs/public/upload/cm/'.$link."/temp/";
			
			if (!is_dir($targetDirectory)) {
				   // 尝试创建目录
			   if (!mkdir($targetDirectory, 0755, true)) {
				   return false; 
			   }
			}
			$mm['user_client_id']=$res['id'];
			$detail=DB::name('user_financial')->where($mm)->find();
		
			if($detail){
				 $htmlContent = $this->gethtml($detail);

				if($htmlContent){
					// 设置文件名和路径
					$filename = 'cm.html';
					$filepath = '/var/www/vhosts/taxprep.republictax.com/httpdocs/public/upload/cm/'.$link.'/pdf/';
					 
					// 确保目录存在
					if (!file_exists($filepath)) {
						mkdir($filepath, 0755, true);
					}
					
					// 将HTML内容写入文件
				    file_put_contents($filepath . $filename, $htmlContent);
					$htmlFilePath = '/var/www/vhosts/taxprep.republictax.com/httpdocs/public/upload/cm/'.$link.'/pdf/'.$filename;
					$pdfFilePath = '/var/www/vhosts/taxprep.republictax.com/httpdocs/public/upload/cm/'.$link.'/pdf/out.pdf';
					$command = "wkhtmltopdf --enable-local-file-access --lowquality $filepath$filename $pdfFilePath";
			
					exec($command, $output, $returnVar);
				
					if ($returnVar === 0) {						
						header('Content-Description: File Transfer');
						header('Content-Type: application/octet-stream');
						header('Content-Disposition: attachment; filename=CM-financial.pdf');
						header('Content-Transfer-Encoding: binary');
						header('Expires: 0');
						header('Cache-Control: must-revalidate');
						header('Pragma: public');
						header('Content-Length: ' . filesize($pdfFilePath));
					
						ob_clean();
						flush();
						readfile($pdfFilePath);
						unlink($pdfFilePath);
						unlink($filepath.$filename);
						rmdir($filepath);
						exit;
					} else {
						return false;
					}
				}
			}
		}
        
    }
	 public function cmsave(){
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
            $save['curfield']=$data['fieldname'];
            $save['updatetime']=time();
			if($data['total']!=-1){
				$save['total_expenses']=$data['total'];
			}
            $map['link']=$data['link'];
            //$map['status']=1;
            $res=DB::name('users_clients')->where($map)->find();
            if($res){
            	$save2['updatetime']=time();
                $save2['status']=1;
                DB::name('users_clients')->where('id',$res['id'])->save($save2);//更新users_clients状态 

				$mm['user_client_id']=$res['id'];
				$profitloss=DB::name('user_financial')->where($mm)->find();
				if($profitloss){
					$rr=DB::name('user_financial')->where($mm)->save($save);
					if($data['fieldvalue']!==0){
		            	if ($data['fieldvalue']=='') {
		            		DB::name('user_financial')->where($mm)->save(array($data['fieldname']=>null));
		            	}
		            }
					$customFormat = 'g:i A'; //显示样式  Jan 11, 2024, 10:0 AM  F为英文月份全称 jS为6th第几天
					$savetime="Saved at ".date($customFormat,$save['updatetime']); //将当前时间按照自定义格式转换成字符串
				   return json(['code'=>1,'savetime'=>$savetime]); 
				}else{
					return json(['code'=>0,'msg'=>'Error Message']);  
				}           
            }else{
               return json(['code'=>0]);  
            }
            //保存字段
            //保存相应的字段，修改最后编辑字段，修改最后保存时间  通过最后保存字段，获得在哪一页
        }
    }

    public function financial(){
        $users=session('user');
        $islogin=0;
        if($users){
            $islogin=1;
        }
        View::assign('islogin', $islogin);
        $str=$_SERVER['REQUEST_URI'];
        $key=str_replace("/clients/financial/",'',$str);
  
        if (strpos($key, "/ftag/1")) {
            $key=str_replace("/ftag/1",'',$key);
            View::assign('showtag', 1); 
        }else{
            View::assign('showtag', 0);
        }
        $map['link']=$key;
        if($key!="preview"){
        	$map['plan']="cm";
        }
     
        $res=DB::name('users_clients')->where($map)->field('id,link,firstname,lastname,status')->find();
          
        if($res){
			$mm['user_client_id']=$res['id'];
			$profitloss=DB::name('user_financial')->where($mm)->find();
			if(!$profitloss){
				$insert['user_client_id']=$res['id'];
				$insert['updatetime']=time();
				$pid=DB::name('user_financial')->insertGetId($insert);
				$mm2['user_client_id']=$res['id'];
			    $profitloss=DB::name('user_financial')->where($mm2)->find();
			}
			$le=substr($res['firstname'],0,1);
			$le2=substr($res['lastname'],0,1);
			$name=$le.$le2;
			View::assign('shortname', $name); 
			$profitloss['link']=$res['link'];
			$profitloss['status']=$res['status'];
			View::assign('detail', $profitloss);
        }else{
          die('Something goes wrong');
        }  
       
       return view();
    }
	public function financial_upload(){
        $users=session('user');
        $islogin=0;
        if($users){
            $islogin=1;
        }
        View::assign('islogin', $islogin);
        $str=$_SERVER['REQUEST_URI'];
        $key=str_replace("/clients/financial_upload/",'',$str);
  
        if (strpos($key, "/ftag/1")) {
            $key=str_replace("/ftag/1",'',$key);
            View::assign('showtag', 1); 
        }else{
            View::assign('showtag', 0);
        }
        $map['link']=$key;
		$map['plan']="cm";
        $res=DB::name('users_clients')->where($map)->find();
          
        if($res){
			$mm['user_client_id']=$res['id'];
			$profitloss=DB::name('user_financial')->where($mm)->find();
			if(!$profitloss){
				$insert['user_client_id']=$res['id'];
				$insert['updatetime']=time();
				$pid=DB::name('user_financial')->insertGetId($insert);
				$mm2['user_client_id']=$res['id'];
			    $profitloss=DB::name('user_financial')->where($mm2)->find();
			}
			$le=substr($res['firstname'],0,1);
			$le2=substr($res['lastname'],0,1);
			$name=$le.$le2;
			View::assign('shortname', $name); 
			$profitloss['link']=$res['link'];
			$profitloss['status']=$res['status'];
			View::assign('detail', $profitloss);
			
			$upload_pname_ary=array('Personal_Bank_Accounts'=>'w2','page15-12'=>'ssa_rrb_1099','page15-10'=>'form_2439','page15-11'=>'1099_r','page15-13-11-0'=>'1099_misc','page15-13-11-1'=>'1099_misc','page15-13-11-2'=>'1099_misc','page15-13-11-3'=>'1099_misc','page15-13-11-4'=>'1099_misc','page15-13-15-0'=>'1098','page15-13-15-1'=>'1098','page15-13-15-2'=>'1098','page15-13-15-3'=>'1098','page15-13-15-4'=>'1098','page15-13-17-0'=>'assets_purchased_business','page15-13-17-1'=>'assets_purchased_business','page15-13-17-2'=>'assets_purchased_business','page15-13-17-3'=>'assets_purchased_business','page15-13-17-4'=>'assets_purchased_business','page15-13-19-0'=>'assets_sold_property','page15-13-19-1'=>'assets_sold_property','page15-13-19-2'=>'assets_sold_property','page15-13-19-3'=>'assets_sold_property','page15-13-20'=>'additional_rental_properties','page15-13-21'=>'profit_and_loss_stmt_rental','page15-13-22'=>'1099_misc','page15-13-23'=>'1098','page15-13-24'=>'assets_purchased_rental','page15-13-25'=>'Assets-Sold-Additional-Properties','page15-13-7-0'=>'Statements','page15-13-7-1'=>'Statements','page15-13-7-4'=>'Statements','page15-14-13-g'=>'1099_g','page15-14-13-k'=>'1099_k','page15-14-13-misc'=>'1099_misc','page15-14-13-nec'=>'1099_nec','page15-14-13-patr'=>'1099_patr','page15-14-31'=>'vehicle_expenses','page15-14-33'=>'assets_purchased_farm','page15-14-35'=>'assets_sold_farm','page15-14-6'=>'P&L and any financial statements','page15-14-8'=>'Spreadsheets','page15-15'=>'1099_g','page15-16'=>'k_1','page15-17-3'=>'all_other_documents','page15-17'=>'1099_sa','page15-18'=>'w2_g','page15-2-13-1'=>'assets_purchased_business','page15-2-14-1'=>'assets_sold_business','page15-2-15-20'=>'assets_purchased_business','page15-2-15-22'=>'assets_purchased_business','page15-2-15-25'=>'all_other_documents','page15-2-15-3-1-2-k'=>'1099_k','page15-2-15-3-1-2-misc'=>'1099_misc','page15-2-15-3-1-2-nec'=>'1099_nec','page15-2-15-3-1-4'=>'assets_purchased_business','page15-2-15-4-1'=>'w_3','page15-2-15-4-2'=>'payroll_tax_reports','page15-2-15-9'=>'1099_nec','page15-2-3'=>'profit_and_loss_stmt_business','page15-2-4-k'=>'1099_k','page15-2-4-misc'=>'1099_misc','page15-2-4-nec'=>'1099_nec','page15-2-7-1'=>'w_3','page15-2-7-2'=>'payroll_tax_reports','page15-2-8-3'=>'1099_nec','page15-21-1'=>'closing_statement','page15-21'=>'1099_s','page15-22-0-1'=>'1099_c','page15-22-0'=>'1099_a','page15-23'=>'1099_q','page15-24-2'=>'all_other_documents','page15-3-1'=>'consolidated_1099','page15-3-2'=>'1099_b','page15-3-3'=>'brokerage_statement','page15-3-7'=>'all_other_documents','page15-4'=>'1099_int','page15-5-3'=>'all_other_documents','page15-5'=>'1099_div','page15-6-0'=>'closing_statement','page15-6-3'=>'all_other_documents','page15-6'=>'1099_s','page15-7-3'=>'all_other_documents','page15-7'=>'form_3921','page15-8-6'=>'additional_foreign_accounts','page15-9'=>'1099_oid','page18-1'=>'form_1098','page18-10'=>'1098_e','page18-10'=>'1098_e','page18-14-4'=>'car_registration_renewal_notice','page18-18'=>'vehicle_seller_report','page18-2-1'=>'estimated_tax_payments','page18-20-1'=>'form_5498_sa','page18-22'=>'1095_a','page18-6-1'=>'charitable_receipts_cash','page18-7-3'=>'charitable_receipts_non_cash','page18-7-4'=>'charitable_receipts_non_cash','page18-9'=>'1098_t','page19-11'=>'irs_notice','page19-12'=>'all_other_documents','page19-14'=>'all_other_documents','page19-6'=>'estimated_tax_payments','page19-9'=>'old_tax_returns');
          
			View::assign('upload_pname_ary', $upload_pname_ary);
        }else{
          die('Something goes wrong');
        }  
       
       return view();
    }
	
	public function getAllFile(){
       if(IS_POST){
           $data=input('post.');
           $map['link']=$data['link'];
           $res0=DB::name('users_clients')->where($map)->find();
           if($res0){
              return json(['code'=>1,'data'=>$res0['filename']]); 
           }else{
              return json(['code'=>0,'msg'=>1]); 
           }
       }
    }
	public function ajax_getFilename(){
    if(IS_POST){
               $data=input('post.');
               $map['id']=$data['fid'];
         $newpagename="uncategorized";
         $pageshow="uncategorized";
               $res=DB::name('users_checklist')->where($map)->find(); 
       
               if($res){
                   //查找listkey 对应的pagename 
                   foreach($this->upload_pname_ary as $key=>$val){
                       if($val==$res['listkey']){
                           $newpagename=$key;
                           $pageshow=$val;
                           break;
                       }
                   }
         }  
          return json(['code'=>1,'filename'=>$newpagename,'pageshow'=>$pageshow]); 
    }     
  }
  function addFileToZip($path,$zip,$fileary){
     $handler=opendir($path); //打开当前文件夹由$path指定。
         while(($filename=readdir($handler))!==false){
             if($filename != "." && $filename != ".."){//文件夹文件名字为'.'和‘..'，不要对他们进行操作
                  if(is_dir($path."/".$filename)){// 如果读取的某个对象是文件夹，则递归
                    $this->addFileToZip($path."/".$filename, $zip,$fileary);
                  }else{ //将文件加入zip对象
                      $zip->addFile($path."/".$filename);//$path."/".$filename
                      $attachmentItem=$path."/".$filename;//去除层级目录
                      $zip->renameName($attachmentItem, basename($attachmentItem));
                  }
             }
         }
      @closedir($path);
    }
    function addFiletoTemp($link,$path,$fileary,$dir,$topath){
     $handler=opendir($path); //打开当前文件夹由$path指定。
         while(($filename=readdir($handler))!==false){
             if($filename != "." && $filename != ".."){//文件夹文件名字为'.'和‘..'，不要对他们进行操作
                  if(is_dir($path."/".$filename)){// 如果读取的某个对象是文件夹，则递归
                    $this->addFiletoTemp($link,$path."/".$filename, $fileary,$dir,$topath);
                  }else{ //将文件加入zip对象
                      foreach($fileary as $key=>$val){
                          $temppath='';$pname='';
                          $ary=explode("|",$val);//选中文件下载
                          $temppath=$dir."/".$ary[0];
                          $pname=$ary[1];
                         
                          if($path==$temppath&&$pname==$filename){
                          $pagename=$ary[0];
                              //集中放到一个目录 
                          $sourceFile='/var/www/vhosts/taxprep.republictax.com/httpdocs/public/upload/clients/'.$link."/".$pagename."/".$filename;
                          $folderPath ='/var/www/vhosts/taxprep.republictax.com/httpdocs/public/upload/clients/'.$link."/temp/";
                          $targetDirectory = $folderPath;
                       
                          $newpagename=$pagename;
                          foreach($this->upload_pname_ary as $key=>$val){
                               if($key==$pagename){
                                   $newpagename=$val;
                                   break;
                               }
                           }
                            $counter=$this->filecounter;
                            $this->filecounter++;
                            $newname=$counter."_".$newpagename."_".basename($sourceFile);
                            $targetPath = $targetDirectory.$newname ;//进行重命名，前面加上目录名
                            copy($sourceFile, $targetPath);//复制文件
                          }
                      }
                     
                  }
             }
         }
      @closedir($path);
    }
   function addAllFiletoTemp($link,$path,$targetDirectory){
     $handler=opendir($path); //打开当前文件夹由$path指定。
         while(($filename=readdir($handler))!==false){
             if($filename != "." && $filename != ".."){//文件夹文件名字为'.'和‘..'，不要对他们进行操作
                  if(is_dir($path."/".$filename)){// 如果读取的某个对象是文件夹，则递归
                    $this->addAllFiletoTemp($link,$path."/".$filename,$targetDirectory);
                  }else{ //将文件加入zip对象
                         $oldpath=dirname($path."/".$filename);
                         $pagename=str_replace("upload/clients/".$link."/",'',$oldpath);
                         if($pagename!="temp"){
                             $sourceFile=$path."/".$filename;
                              $newpagename=$pagename;
                              foreach($this->upload_pname_ary as $key=>$val){
                                   if($key==$pagename){
                                       $newpagename=$val;
                                       break;
                                   }
                               }
                              $counter=$this->filecounter;
                              $this->filecounter++;
                              $newname=$counter."_".$newpagename."_".basename($filename);
                              $targetPath = "upload/clients/".$link."/temp/".$newname ;//进行重命名，前面加上目录名
                             // echo $targetPath."--";
                             copy($sourceFile, $targetPath);//复制文件
                         }
                  }
             }
         }
      @closedir($path);
    }  
  function addAllFileForHtml($link,$path,$pichtml){
     $handler=opendir($path); //打开当前文件夹由$path指定。
         while(($filename=readdir($handler))!==false){
             if($filename != "." && $filename != ".."){//文件夹文件名字为'.'和‘..'，不要对他们进行操作
                  if(is_dir($path."/".$filename)){// 如果读取的某个对象是文件夹，则递归
                    $this->addAllFileForHtml($link,$path."/".$filename,$pichtml);
                  }else{ //将文件加入zip对象
					  
                         $oldpath=dirname($path."/".$filename);
                        
                         $pagename=str_replace("upload/clients/".$link."/",'',$oldpath);
					
                         $sfile=$path."/".$filename;
                         $sfile1="/var/www/vhosts/taxprep.republictax.com/httpdocs/public/".$path."/".$filename;
						
						 $file_extension = pathinfo($sfile1, PATHINFO_EXTENSION);			
                          if ($file_extension == "jpg" || $file_extension == "jpeg"||$file_extension == "png") {
							//  echo $sfile1."<br>";
                               $this->phtml.='<div class="separate-page"><img src="'.rawurldecode($sfile1).'" style="width:100%;height:auto;" ></div>';
                         }
                  }
             }
         }
         
      @closedir($path);
     // return $pichtml;
    }  
  function delete_directory($dir) {
    if (!$handle = @opendir($dir)) {
        return false;
    }
    while (false !== ($item = readdir($handle))) {
        if ($item == '.' || $item == '..') continue;
 
        $path = $dir . '/' . $item;
        if (is_dir($path)) {
            delete_directory($path);
        } else {
            unlink($path);
        }
    }
    closedir($handle);
    rmdir($dir);
    return true;
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
	public function ajax_remove(){
        if($_POST['pagename']&&$_POST['link']&&$_POST['files']){
           
            $pagename=$_POST['pagename'];
            $link=$_POST['link'];
            $map['link']=$_POST['link'];
			$map['plan']="cm";
            $res2=DB::name('users_clients')->where($map)->find();
            if($res2){
				$map22['user_client_id']=$res2['id'];
				$res=DB::name('user_financial')->where($map22)->find();
                //删除数据库
                if($res['filename']&&$res){
                    //$filename= $_FILES["files"]["name"];//$pagename
                    $filename=$_POST['files'];
                    $fary=json_decode($res['filename'],true);
                    $fileary=$fary[$pagename];
                 
                    if($fileary){
                        $fary[$pagename]=[];
                         foreach($fileary as $key=>$val){
                                if($val==$filename){
                                    continue;
                                  //  unset($fary[$pagename][$key]);
                                }
                                array_push($fary[$pagename],$val);
                            }
                            if(empty($fary)){
                                $save['filename']='';
                            }else{
                                $save['filename']=json_encode($fary);
                            }
                              
                                 $save['updatetime']=time();
                                 //$save['status']=1;
                                 $rr=DB::name('user_financial')->where($map22)->save($save);
                                 $customFormat = 'g:i A'; //显示样式  Jan 11, 2024, 10:0 AM  F为英文月份全称 jS为6th第几天
                                 $savetime="Saved at ".date($customFormat,$save['updatetime']); //将当前时间按照自定义格式转换成字符串
                                // return json(['code'=>1,'savetime'=>$savetime]);
                                 $path='/var/www/vhosts/taxprep.republictax.com/httpdocs/public/upload/financial/'.$link."/sheets/".$pagename."/".$filename;
								if(file_exists($path)){
									if (unlink($path)) { //物理删除                              
									   //重新计算所有文件数量
									  $newdetail= DB::name('users_clients')->where($map)->find();
									  $filecount=0;
									  if($newdetail['filename']){
										   $filearytop=json_decode($newdetail['filename'],true);
										   foreach($filearytop as $key=>$val){
											 $filecount=$filecount+count($val);
										   }
									  }
									  //重新计算数量结束
									   return json(['code'=>1,'savetime'=>$savetime,'data'=>$result,'per'=>0,'filecounter'=>$filecount]);
									}else{
									   return json(['code'=>0,'msg'=>1]);
									}
								}else{
									return json(['code'=>0,'msg'=>'The file does not exist, deletion failed']);
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
     if($_POST['pagename']&&$_POST['link']){
        $pagename=$_POST['pagename'];
        $link=trim($_POST['link']);
		
        $map22['link']=$_POST['link'];
		$map22['plan']="cm";
        $res2=DB::name('users_clients')->where($map22)->find();
        if($res2){
			$map['user_client_id']=$res2['id'];
			$res=DB::name('user_financial')->where($map)->find();
		
            if($_FILES&&$res){
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
									['file'=>'fileSize:50000000|fileExt:csv,xlsx'],
									['file.fileSize' => 'Oversize','file.fileExt'=>'only support csv,pdf,xlsx']
								);
							}catch (ValidateException $e){
								$error=$e->getError();
								$values=array_values($error);
								return json(['code'=>0,'msg'=>$values[0]]);
							}
						  
							   // 移动到框架应用根目录/uploads/ 目录下
								$filename= $_FILES["files"]["name"];//$pagename
								//$link="kLipRhIqZ0zieWnM399TVQWeG810";
								$new_path = '/financial/'.$link."/sheets/".$pagename;
								/*
								if (!is_dir($new_path)) {
									if (!mkdir($new_path, 0755, true)) {
										return json(['code'=>0,'msg'=>'无法创建目标目录']); 
									}
								}*/
								// 使用自定义的文件保存规则
								$info = Filesystem::putFile($new_path,$file,$filename,[],1);
							
								if($info){
									$fileurl = UPLOAD_PATH.$info;
								
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
											//$save['last_field']=$pagename;
											$save['updatetime']=time();
											//$save['status']=1;
											$rr=DB::name('user_financial')->where($map)->save($save);                              
											
										 //重新计算所有文件数量
										   $newdetail= DB::name('user_financial')->where($map)->find();
										   $filecount=0;
										  if($newdetail['filename']){
											   $filearytop=json_decode($newdetail['filename'],true);
											   foreach($filearytop as $key=>$val){
												 $filecount=$filecount+count($val);
											   }
										  }
									  return json(['code'=>1,'filecounter'=>$filecount,'per'=>$re]);   
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
}
