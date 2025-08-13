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

class Deductions extends Base
{
    protected function initialize()
    {
        parent::initialize();
		$upload_pname_ary=array('page15-1'=>'w2','page15-12'=>'ssa_rrb_1099','page15-10'=>'form_2439','page15-11'=>'1099_r','page15-13-11-0'=>'1099_misc','page15-13-11-1'=>'1099_misc','page15-13-11-2'=>'1099_misc','page15-13-11-3'=>'1099_misc','page15-13-11-4'=>'1099_misc','page15-13-15-0'=>'1098','page15-13-15-1'=>'1098','page15-13-15-2'=>'1098','page15-13-15-3'=>'1098','page15-13-15-4'=>'1098','page15-13-17-0'=>'assets_purchased_business');
         $this->upload_pname_ary=$upload_pname_ary;
         View::assign('upload_pname_ary', $upload_pname_ary);
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
		$map['plan']="deductions";
        $res=DB::name('users_clients')->where($map)->find();
          
        if($res){
			$mm['user_client_id']=$res['id'];
			$profitloss=DB::name('user_deductions')->where($mm)->find();
			
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
		$map['plan']="deductions";
        $res=DB::name('users_clients')->where($map)->find();
          
        if($res){
			$mm['user_client_id']=$res['id'];
			$profitloss=DB::name('user_deductions')->where($mm)->find();
			
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
		$map['plan']="deductions";
		$link=$data['link'];
        $res=DB::name('users_clients')->where($map)->find();
        if($res){
			$mm['user_client_id']=$res['id'];
			$profitloss=DB::name('user_deductions')->where($mm)->find();
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
             Db::name('user_deductions')->where('id',$profitloss['id'])->update($new);
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
			$profitloss=DB::name('user_deductions')->where($mm)->find();
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
             Db::name('user_deductions')->where('id',$profitloss['id'])->update($new);
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
			$profitloss=DB::name('user_deductions')->where($mm)->find();
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
             Db::name('user_deductions')->where('id',$profitloss['id'])->update($new);
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
		   $map['plan']='deductions';
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
	public function desubmit(){
      // $link="k0ArSMziYDVZ8IwixdW3fsAhumj5";
        
        //复制完后生成pdf,发送后删除 
		$link=$_POST['link'];
		$map['link']=$_POST['link'];
		$map['plan']='deductions';
        $res=DB::name('users_clients')->where($map)->field('id,caseid')->find();
       
        if($res){
			$path='upload/deductions/'.$link;
			//先挪动文件至temp目录下，先创建当前目录下的temp目录
			$targetDirectory ='/var/www/vhosts/taxprep.republictax.com/httpdocs/public/upload/deductions/'.$link."/temp/";
			
			if (!is_dir($targetDirectory)) {
				   // 尝试创建目录
			   if (!mkdir($targetDirectory, 0755, true)) {
				   return false; 
			   }
			}
			$mm['user_client_id']=$res['id'];
			$detail=DB::name('user_deductions')->where($mm)->find();
		
			if($detail){
				 $htmlContent = $this->gethtml($detail);

				if($htmlContent){
					// 设置文件名和路径
					$filename = 'deductions.html';
					$filepath = '/var/www/vhosts/taxprep.republictax.com/httpdocs/public/upload/deductions/'.$link.'/pdf/';
					 
					// 确保目录存在
					if (!file_exists($filepath)) {
						mkdir($filepath, 0755, true);
					}
					 
					// 将HTML内容写入文件
				   
					//$command = "wkhtmltopdf --enable-local-file-access --orientation Landscape --lowquality $filepath$filename $pdfFilePath";	用了此命令导致文件被分隔				
					file_put_contents($filepath . $filename, $htmlContent);
					$htmlFilePath = '/var/www/vhosts/taxprep.republictax.com/httpdocs/public/upload/deductions/'.$link.'/pdf/'.$filename;
					$pdfFilePath = '/var/www/vhosts/taxprep.republictax.com/httpdocs/public/upload/deductions/'.$link.'/pdf/out.pdf';
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
						  $fname="Tax_Organizer_Self_Employed".$fname.".pdf";
						 
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
				$profitloss=DB::name('user_deductions')->where($mm)->find();
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
			font-size:12px;
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
		.w120{
			width:120px!important;
		}
		.w220{
			width:220px!important;
		}
		.m20{
		   margin-top:20px;
		}
		.title{
		  margin-top:20px;
		  font-weight:bold;
		}
		.ma20{
		  margin-left:20px;
		}
		textarea{
		background: #f1f4ff;
		font-size:12px;
		}
		.padding20{
			padding-left:20px;
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
				 <td class="tipsbold noborder borderBottom" style="text-align:center;height:18px;font-size:16px">Sched. A -Itemized Deductions Organizer </td>
			  </tr>
			</table>
			<div class="m20"></div>
			<table style="width:65%;margin-left:200px;">
			  <tr>
				 <td class="tipsbold noborder">Name: </td>
				 <td class="tipsbold noborder borderBottom"><input tabindex="3" type="text" id="a3" data-src="name"  class="tabletd" value="'.$detail['name'].'"></td>
				 <td class="tipsbold noborder">Tax Tear: </td>
				 <td class="tipsbold noborder borderBottom w120"><input tabindex="4" type="text" id="a4" data-src="tax_year"  class="tabletd w120" value="'.$detail['tax_year'].'"></td>
			  </tr>
			</table>
			<div class="title">Medical Expenses</div>
			<table style="width:100%;">
			  <tr>
				 <td class="noborder w100"></td>
				 <td class="noborder">PrescriptionMedicines and Drugs</td>
				 <td class="noborder" style="text-align:right;">$</td>
				 <td class="noborder borderBottom"><input tabindex="5" type="text" id="a5" data-src="medical_prescription_medicines_and_drugs"  class="tabletd w120" value="'.$detail['medical_prescription_medicines_and_drugs'].'"></td>
			  </tr>
			  <tr>
				 <td class="noborder w100"></td>
				 <td class="noborder">Insurance Premiums YOU paid</td>
				 <td class="noborder" style="text-align:right;">$</td>
				 <td class="noborder borderBottom"><input tabindex="6" type="text" id="a6" data-src="medical_insurance_premiums"  class="tabletd w120" value="'.$detail['medical_insurance_premiums'].'"></td>
			  </tr>
			  <tr>
				 <td class="noborder w100"></td>
				 <td class="noborder">Doctors,Dentist Visits</td>
				 <td class="noborder" style="text-align:right;">$</td>
				 <td class="noborder borderBottom"><input tabindex="7" type="text" id="a7" data-src="medical_doctors_dentist_visits"  class="tabletd w120" value="'.$detail['medical_doctors_dentist_visits'].'"></td>
			  </tr>
			  <tr>
				 <td class="noborder w100"></td>
				 <td class="noborder">Lab Fees</td>
				 <td class="noborder" style="text-align:right;">$</td>
				 <td class="noborder borderBottom"><input tabindex="8" type="text" id="a8" data-src="medical_lab_fees" class="tabletd w120" value="'.$detail['medical_lab_fees'].'"></td>
			  </tr>
			  <tr>
				 <td class="noborder w100"></td>
				 <td class="noborder">LongTerm Care Insurance Premiums </td>
				 <td class="noborder" style="text-align:right;">$</td>
				 <td class="noborder borderBottom"><input tabindex="9" type="text" id="a9" data-src="medical_long_term_care_insurance_premiums" class="tabletd w120" value="'.$detail['medical_long_term_care_insurance_premiums'].'"></td>
			  </tr>
			  <tr>
				 <td class="noborder w100"></td>
				 <td class="noborder">Hospitals and Nursing homes </td>
				 <td class="noborder" style="text-align:right;">$</td>
				 <td class="noborder borderBottom"><input tabindex="10" type="text" id="a10" data-src="medical_hospitals_nursing_homes" class="tabletd w120" value="'.$detail['medical_hospitals_nursing_homes'].'"></td>
			  </tr>
			  <tr>
				 <td class="noborder w100"></td>
				 <td class="noborder">Other Medical & DentalExpenses </td>
				 <td class="noborder" style="text-align:right;">$</td>
				 <td class="noborder borderBottom"><input tabindex="11" type="text" id="a11" data-src="medical_other_medical_dental" class="tabletd w120" value="'.$detail['medical_other_medical_dental'].'"></td>
			  </tr>
			  <tr>
				 <td class="noborder w100"></td>
				 <td class="noborder">Medical Miles Driven</td>
				 <td class="noborder borderBottom"><input tabindex="12" type="text" id="a12" data-src="medical_miles_driven" class="tabletd w100" value="'.$detail['medical_miles_driven'].'">Miles</td>
				 <td class="noborder"></td>
			  </tr>
			</table>
			<div class="title">Taxes Paid </div>
			<table style="width:100%;">
			  <tr>
				 <td class="noborder w100"></td>
				 <td class="noborder">State Taxes paid for prior years in this year </td>
				 <td class="noborder">$</td>
				 <td class="noborder borderBottom"><input tabindex="14" type="text" id="a14" data-src="taxes_state"  class="tabletd w120" value="'.$detail['taxes_state'].'"></td>
			  </tr>
			  <tr>
				 <td class="noborder w100"></td>
				 <td class="noborder">REALESTATE TAXES-Primary Home </td>
				 <td class="noborder">$</td>
				 <td class="noborder borderBottom"><input tabindex="15" type="text" id="a15" data-src="taxes_real_eastate"  class="tabletd w120" value="'.$detail['taxes_real_eastate'].'"></td>
			  </tr>
			  <tr>
				 <td class="noborder w100"></td>
				 <td class="noborder">Other Real Estate Taxes </td>
				 <td class="noborder">$</td>
				 <td class="noborder borderBottom"><input tabindex="16" type="text" id="a16" data-src="taxes_other_real_estates"  class="tabletd w120" value="'.$detail['taxes_other_real_estates'].'"></td>
			  </tr>
			  <tr>
				 <td class="noborder w100"></td>
				 <td class="noborder">Pers. Prop. Taxes (OMV/ automobile Fees) </td>
				 <td class="noborder">$</td>
				 <td class="noborder borderBottom"><input tabindex="17" type="text" id="a17" data-src="taxes_pers_prop"  class="tabletd w120" value="'.$detail['taxes_pers_prop'].'"></td>
			  </tr>
			</table>
			<div class="title">Interest Paid</div>
			<table style="width:100%;">
			  <tr>
				 <td class="noborder w100"></td>
				 <td class="noborder">Home Mortgage Interest (Form 1098)</td>
				 <td class="noborder">$</td>
				 <td class="noborder borderBottom"><input tabindex="18" type="text" id="a18" data-src="home_mortgage_interest"  class="tabletd w120" value="'.$detail['home_mortgage_interest'].'"></td>
			  </tr>
			  <tr>
				 <td class="noborder w100"></td>
				 <td class="noborder">Investment Interest (Interest on margin accts)</td>
				 <td class="noborder">$</td>
				 <td class="noborder borderBottom"><input tabindex="19" type="text" id="a19" data-src="invest_interest"  class="tabletd w120" value="'.$detail['invest_interest'].'"></td>
			  </tr>
			  <tr>
				 <td class="noborder w100"></td>
				 <td class="noborder">Interest and/or Points not on Form 1098</td>
				 <td class="noborder">$</td>
				 <td class="noborder borderBottom"><input tabindex="20" type="text" id="a20" data-src="interest_points"  class="tabletd w120" value="'.$detail['interest_points'].'"></td>
			  </tr>
			</table>
			<table style="width:100%;">  
			  <tr>
				 <td class="noborder w100"></td>
				 <td class="noborder">**If interest not reported on Form 1098, please <br>
include Payee\'s name, address, SSN/EIN and <br>
amount paid on separate sheet.</td>
				 <td class="noborder" colspan="2" style="text-align:right;"><textarea tabindex="23" cols="60" rows="8" style="border:1px solid #000;margin-top:10px;" >'.$detail['interest'].'</textarea></td>
			  </tr>
			</table>  
			<div class="title">Cash Contributions</div>
			<table style="width:100%;">
			  <tr>
				 <td class="noborder w100"></td>
				 <td class="noborder">Donations to charitable orgs via cash, check,CC, etc.</td>
				 <td class="noborder">$</td>
				 <td class="noborder borderBottom"><input tabindex="21" type="text" id="a21" data-src="cash_contributions_donations"  class="tabletd w120" value="'.$detail['cash_contributions_donations'].'"></td>
			  </tr>
			</table>  
			<div class="title">Non-Cash Donations</div>
			<table style="width:100%;">
			  <tr>
				 <td class="noborder w100"></td>
				 <td class="noborder">Clothing, Furniture, etc. Anything non - cash</td>
				 <td class="noborder">$</td>
				 <td class="noborder borderBottom"><input tabindex="22" type="text" id="a22" data-src="no_cash_donation"  class="tabletd w120" value="'.$detail['no_cash_donation'].'"></td>
			  </tr>
			</table> 
			<table style="width:100%;">    
			  <tr>
				 <td class="noborder w100"></td>
				 <td class="noborder">*** If over $500, you MUST include description <br>
of items donated, Name and address of <br>
organization donated to, Thrift Store Value <br>
of items donated, and date donated. <br>
Each Date/donation will need to be separated.</td>
				 <td class="noborder" colspan="2" style="text-align:right;"><textarea tabindex="23" cols="60" rows="8" style="border:1px solid #000;margin-top:10px;" >'.$detail['description'].'</textarea></td>
			  </tr>
			</table> 
			<div class="title">Miscellaneous Deductions </div>
			<table style="width:100%;">
			  <tr>
				 <td class="noborder w100"></td>
				 <td class="noborder">Union and Professional Dues</td>
				 <td class="noborder">$</td>
				 <td class="noborder borderBottom"><input tabindex="23" type="text" id="a23" data-src="deductions_union_professional_dues"  class="tabletd w120" value="'.$detail['deductions_union_professional_dues'].'"></td>
			  </tr>
			  <tr>
				 <td class="noborder w100"></td>
				 <td class="noborder">Unreimbursed expenses if you are an EMPLOYEE</td>
				 <td class="noborder">$</td>
				 <td class="noborder borderBottom"><input tabindex="24" type="text" id="a24" data-src="deductions_unreimbursed_expenses"  class="tabletd w120" value="'.$detail['deductions_unreimbursed_expenses'].'"></td>
			  </tr>	
              <tr>
				 <td class="noborder w100"></td>
				 <td class="noborder">Tax Preparation Fee</td>
				 <td class="noborder">$</td>
				 <td class="noborder borderBottom"><input tabindex="25" type="text" id="a25" data-src="deductions_tax_preparation_fee"  class="tabletd w120" value="'.$detail['deductions_tax_preparation_fee'].'"></td>
			  </tr>		
             <tr>
				 <td class="noborder w100"></td>
				 <td class="noborder"><b>*** *If you have additional expenses as an EMPLOYEE.<br>
please ask for Employee expense organizer.</b></td>
				 <td class="noborder" colspan="2"></td>
			  </tr>			  
            </table> 	
		</div>
	</body>
	</html>';

	return $html;
	}
	public function deductions_topdf(){
      // $link="k0ArSMziYDVZ8IwixdW3fsAhumj5";
        
        //复制完后生成pdf,发送后删除 
		$link=$_GET['link'];
		$map['link']=$_GET['link'];
		$map['plan']='deductions';
       $res=DB::name('users_clients')->where($map)->field('id')->find();
       
        if($res){
			$path='upload/deductions/'.$link;
			//先挪动文件至temp目录下，先创建当前目录下的temp目录
			$targetDirectory ='/var/www/vhosts/taxprep.republictax.com/httpdocs/public/upload/deductions/'.$link."/temp/";
			
			if (!is_dir($targetDirectory)) {
				   // 尝试创建目录
			   if (!mkdir($targetDirectory, 0755, true)) {
				   return false; 
			   }
			}
			$mm['user_client_id']=$res['id'];
			$detail=DB::name('user_deductions')->where($mm)->find();
		
			if($detail){
				 $htmlContent = $this->gethtml($detail);

				if($htmlContent){
					// 设置文件名和路径
					$filename = 'deductions.html';
					$filepath = '/var/www/vhosts/taxprep.republictax.com/httpdocs/public/upload/deductions/'.$link.'/pdf/';
					 
					// 确保目录存在
					if (!file_exists($filepath)) {
						mkdir($filepath, 0755, true);
					}
					
					// 将HTML内容写入文件
				    file_put_contents($filepath . $filename, $htmlContent);
					$htmlFilePath = '/var/www/vhosts/taxprep.republictax.com/httpdocs/public/upload/deductions/'.$link.'/pdf/'.$filename;
					$pdfFilePath = '/var/www/vhosts/taxprep.republictax.com/httpdocs/public/upload/deductions/'.$link.'/pdf/out.pdf';
					$command = "wkhtmltopdf --enable-local-file-access --lowquality $filepath$filename $pdfFilePath";
				
					exec($command, $output, $returnVar);
					
					if ($returnVar === 0) {
						header('Content-Description: File Transfer');
						header('Content-Type: application/octet-stream');
						header('Content-Disposition: attachment; filename=Tax_Organizer-Schedule_A_Blank.pdf');
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
	 public function dsave(){
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
				$profitloss=DB::name('user_deductions')->where($mm)->find();
				if($profitloss){
					$rr=DB::name('user_deductions')->where($mm)->save($save);
					if($data['fieldvalue']!==0){
		            	if ($data['fieldvalue']=='') {
		            		DB::name('user_deductions')->where($mm)->save(array($data['fieldname']=>null));
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

    public function deductions(){
        $users=session('user');
        $islogin=0;
        if($users){
            $islogin=1;
        }
        View::assign('islogin', $islogin);
        $str=$_SERVER['REQUEST_URI'];
        $key=str_replace("/clients/deductions/",'',$str);
  
        if (strpos($key, "/ftag/1")) {
            $key=str_replace("/ftag/1",'',$key);
            View::assign('showtag', 1); 
        }else{
            View::assign('showtag', 0);
        }
        $map['link']=$key;
		if($key!="preview"){
        	$map['plan']="deductions";
        }
        $res=DB::name('users_clients')->where($map)->field('id,link,firstname,lastname,status')->find();
          
        if($res){
			$mm['user_client_id']=$res['id'];
			$profitloss=DB::name('user_deductions')->where($mm)->find();
			if(!$profitloss){
				$insert['user_client_id']=$res['id'];
				$insert['updatetime']=time();
				$pid=DB::name('user_deductions')->insertGetId($insert);
				$mm2['user_client_id']=$res['id'];
			    $profitloss=DB::name('user_deductions')->where($mm2)->find();
			}
			$le=substr($res['firstname'],0,1);
			$le2=substr($res['lastname'],0,1);
			$name=$le.$le2;
			View::assign('shortname', $name); 
			$profitloss['link']=$res['link'];
			$profitloss['status']=$res['status'];
			View::assign('detail', $profitloss);
        }else{
          die('Invalid Link');
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
	 public function ajax_upload(){
     if($_POST['pagename']&&$_POST['link']){
        $pagename=$_POST['pagename'];
        $link=$_POST['link'];
        $map22['link']=$_POST['link'];
		$map22['plan']="deductions";
        $res2=DB::name('users_clients')->where($map22)->find();
        if($res2){
			$map['user_client_id']=$res2['id'];
			$res=DB::name('user_deductions')->where($map)->find();
		
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
                    
                    $file2 = request()->file('files');
					 foreach($files as $file) {
						  try {
							   $result = $this->validate(//PDF, PNG, JPG, CSV, XLSX
									['file' => $file],//'jpg', 'png', 'jpg', 'pdf','csv','xlsx' //PDF, PNG, JPG, CSV, XLSX
									['file'=>'fileSize:50000000|fileExt:csv,xlsx'],
									['file.fileSize' => 'Oversize','file.fileExt'=>'only support csv,xlsx']
								);
							}catch (ValidateException $e){
								$error=$e->getError();
								$values=array_values($error);
								return json(['code'=>0,'msg'=>$values[0]]);
							}
						  
							   // 移动到框架应用根目录/uploads/ 目录下
								$filename= $_FILES["files"]["name"];//$pagename
								$new_path = '/financial/'.$link."/sheets/".$pagename;
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
											$rr=DB::name('user_deductions')->where($map)->save($save);                              
											
										 //重新计算所有文件数量
										   $newdetail= DB::name('user_deductions')->where($map)->find();
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
          }
        }else{
            return json(['code'=>0]);
        }
     }else{
         return json(['code'=>0]);
     }
        
    }
}
