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

class Insolvency extends Base
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
		$map['plan']="insolvency";
        $res=DB::name('users_clients')->where($map)->find();
          
        if($res){
			$mm['user_client_id']=$res['id'];
			$profitloss=DB::name('user_insolvency')->where($mm)->find();
			
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
		$map['plan']="insolvency";
        $res=DB::name('users_clients')->where($map)->find();
          
        if($res){
			$mm['user_client_id']=$res['id'];
			$profitloss=DB::name('user_insolvency')->where($mm)->find();
			
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
		$map['plan']="insolvency";
		$link=$data['link'];
        $res=DB::name('users_clients')->where($map)->find();
        if($res){
			$mm['user_client_id']=$res['id'];
			$profitloss=DB::name('user_insolvency')->where($mm)->find();
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
             Db::name('user_insolvency')->where('id',$profitloss['id'])->update($new);
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
			$profitloss=DB::name('user_insolvency')->where($mm)->find();
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
             Db::name('user_insolvency')->where('id',$profitloss['id'])->update($new);
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
			$profitloss=DB::name('user_insolvency')->where($mm)->find();
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
             Db::name('user_insolvency')->where('id',$profitloss['id'])->update($new);
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
		   $map['plan']='insolvency';
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
	public function isubmit(){
      // $link="k0ArSMziYDVZ8IwixdW3fsAhumj5";
        
        //复制完后生成pdf,发送后删除 
		$link=$_POST['link'];
		$map['link']=$_POST['link'];
		$map['plan']='insolvency';
        $res=DB::name('users_clients')->where($map)->field('id,caseid')->find();
       
        if($res){
			$path='upload/insolvency/'.$link;
			//先挪动文件至temp目录下，先创建当前目录下的temp目录
			$targetDirectory ='/var/www/vhosts/taxprep.republictax.com/httpdocs/public/upload/insolvency/'.$link."/temp/";
			
			if (!is_dir($targetDirectory)) {
				   // 尝试创建目录
			   if (!mkdir($targetDirectory, 0755, true)) {
				   return false; 
			   }
			}
			$mm['user_client_id']=$res['id'];
			$detail=DB::name('user_insolvency')->where($mm)->find();
		
			if($detail){
				 $htmlContent = $this->gethtml($detail);

				if($htmlContent){
					// 设置文件名和路径
					$filename = 'insolvency.html';
					$filepath = '/var/www/vhosts/taxprep.republictax.com/httpdocs/public/upload/insolvency/'.$link.'/pdf/';
					 
					// 确保目录存在
					if (!file_exists($filepath)) {
						mkdir($filepath, 0755, true);
					}
					 
					// 将HTML内容写入文件
				    file_put_contents($filepath . $filename, $htmlContent);
					$htmlFilePath = '/var/www/vhosts/taxprep.republictax.com/httpdocs/public/upload/insolvency/'.$link.'/pdf/'.$filename;
					$pdfFilePath = '/var/www/vhosts/taxprep.republictax.com/httpdocs/public/upload/insolvency/'.$link.'/pdf/out.pdf';
					$command = "wkhtmltopdf --enable-local-file-access --lowquality $filepath$filename $pdfFilePath";//--orientation Landscape 
					
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
						 $fname="Tax_Organizer_Insolvency_Worksheet".$fname.".pdf";  
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
				$profitloss=DB::name('user_insolvency')->where($mm)->find();
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
		}
		.padding20{
			padding-left:20px;
		}
		.w200{
			width:200px!important;
		}
		.title{
		  margin-top:20px;
		  font-weight:bold;
		}
		.pleft{
			padding-left:20px;
		}
	</style>
	<body>
	    <div class="page">
			<table style="width:100%;">
			  <tr>
				 <td class="tipsbold noborder">Insolvency Worksheet </td>
				 <td class="tipsbold noborder"><i>Keep for Your Records</i></td>
			  </tr>
			</table>
			<table style="width:100%;">
			  <tr>
				 <td class="tipsbold">Date debt was canceled (mm/dd/yy)</td>
				 <td class="tipsbold"><input tabindex="3" type="text" id="a3" data-src="date_debt" class="tabletd" placeholder="mm/dd/yy" value="'.$detail['date_debt'].'"></td>
			  </tr>
			  <tr>
				 <td colspan="2" class="tipsbold">Part Ⅰ. Total liabilities immediately before the cancellation (don\'t include the same liability in more than one category)</td>
			  </tr>
			  <tr>
				 <td class="tipsbold" style="text-align: center;">Liabilities (debts)</td>
				 <td class="tipsbold" style="text-align: center;">Amount Owed <br>
					lmmediately Before the<br>
						Cancellation</td>
			  </tr>
			  <tr>
				 <td class="pleft">1. Credit card debt</td>
				 <td class="ma20">$<input tabindex="4" type="text" id="a4" data-src="credit_card_debt" class="tabletd w200 total_1" value="'.$detail['credit_card_debt'].'"></td>
			  </tr>
			  <tr>
				 <td class="pleft">2. Mortgage(s) on real property (including first and second mortgages and home equity loans) (mortgage(s) can<br> be on main home, any additional home, or property held for investment or used in a trade or business)</td>
				 <td class="ma20">$<input tabindex="5" type="text" id="a5" data-src="mortgage" class="tabletd w200 total_1" value="'.$detail['mortgage'].'"></td>
			  </tr>
			  <tr>
				 <td class="pleft">3. Car and other vehicle loans</td>
				 <td class="ma20">$<input tabindex="6" type="text" id="a6" data-src="car_other" class="tabletd w200 total_1" value="'.$detail['car_other'].'"></td>
			  </tr>
			  <tr>
				 <td class="pleft">4. Medical bills owed</td>
				 <td class="ma20">$<input tabindex="7" type="text" id="a7" data-src="medical_bills_owed" class="tabletd w200 total_1" value="'.$detail['medical_bills_owed'].'"></td>
			  </tr>
			  <tr>
				 <td class="pleft">5. Student loans</td>
				 <td class="ma20">$<input tabindex="8" type="text" id="a8" data-src="student_loans" class="tabletd w200 total_1" value="'.$detail['student_loans'].'"></td>
			  </tr>
			  <tr>
				 <td class="pleft">6. Accrued or past-due mortgage interest</td>
				 <td class="ma20">$<input tabindex="9" type="text" id="a9" data-src="accrued_mortgage_interest" class="tabletd w200 total_1" value="'.$detail['accrued_mortgage_interest'].'"></td>
			  </tr>
			  <tr>
				 <td class="pleft">7. Medical bills owed</td>
				 <td class="ma20">$<input tabindex="10" type="text" id="a10" data-src="medical_bills_owed" class="tabletd w200 total_1" value="'.$detail['medical_bills_owed'].'"></td>
			  </tr>
			  <tr>
				 <td class="pleft">8. Accrued or past-due utilities (water, gas, electric</td>
				 <td class="ma20">$<input tabindex="11" type="text" id="a11" data-src="accrued_utilities" class="tabletd w200 total_1" value="'.$detail['accrued_utilities'].'"></td>
			  </tr>
			  <tr>
				 <td class="pleft">9. Accrued or past-due child care costs</td>
				 <td class="ma20">$<input tabindex="12" type="text" id="a12" data-src="accrued_child_care_costs" class="tabletd w200 total_1" value="'.$detail['accrued_child_care_costs'].'"></td>
			  </tr>
			  <tr>
				 <td class="pleft">10. Federal or state income taxes remaining due (for prior tax years)</td>
				 <td class="ma20">$<input tabindex="13" type="text" id="a13" data-src="remaining_due" class="tabletd w200 total_1" value="'.$detail['remaining_due'].'"></td>
			  </tr>
			  <tr>
				 <td class="pleft">11. Judgments</td>
				 <td class="ma20">$<input tabindex="14" type="text" id="a14" data-src="judgments" class="tabletd w200 total_1" value="'.$detail['judgments'].'"></td>
			  </tr>
			  <tr>
				 <td class="pleft">12. Business debts (including those owed as a sole proprietor or partner)</td>
				 <td class="ma20">$<input tabindex="15" type="text" id="a15" data-src="business_debts" class="tabletd w200 total_1" value="'.$detail['business_debts'].'"></td>
			  </tr>
			  <tr>
				 <td class="pleft">13. Margin debt on stocks and other debt to purchase or secured by investment assets other than real property</td>
				 <td class="ma20">$<input tabindex="16" type="text" id="a16" data-src="margin_debt" class="tabletd w200 total_1" value="'.$detail['margin_debt'].'"></td>
			  </tr>
			  <tr>
				 <td class="pleft">14. Other liabilities (debts) not included above</td>
				 <td class="ma20">$<input tabindex="17" type="text" id="a17" data-src="other_liabilities" class="tabletd w200 total_1" value="'.$detail['other_liabilities'].'"></td>
			  </tr>
			  <tr>
				 <td class="pleft">15. <b>Total liabilities immediately before the cancellation. Add lines 1 through 14.</b></td>
				 <td class="ma20">$<input tabindex="18" type="text" id="a18" data-src="total_liabilities" class="tabletd w200" value="'.$detail['total_liabilities'].'" readonly></td>
			  </tr>
			  <tr>
				 <td class="pleft tipsbold" colspan="2">Part Ⅱ. Fair market value (FMV) of assets owned immediately before the cancellation (don\'t include the FMV of the same asset in <br>more than oncategory)</td>
			  </tr>
			  <tr>
				 <td class="tipsbold" style="text-align: center;">Assets</td>
				 <td class="tipsbold" style="text-align: center;">FMV Immediately Before<br>
					the Cancellation</td>
			  </tr>
			  <tr>
				 <td class="pleft">16. Cash and bank account balances</td>
				 <td class="ma20">$<input tabindex="19" type="text" id="a19" data-src="cash_bank_balances" class="tabletd w200 total_2" value="'.$detail['cash_bank_balances'].'"></td>
			  </tr>
			  <tr>
				 <td class="pleft">17. Real property, including the value of land (can be main home, any additional home, or property held for <br>investment or used in a trade or business)</td>
				 <td class="ma20">$<input tabindex="20" type="text" id="a20" data-src="real_property" class="tabletd w200 total_2" value="'.$detail['real_property'].'"></td>
			  </tr>
			  <tr>
				 <td class="pleft">18. Cars and other vehicles</td>
				 <td class="ma20">$<input tabindex="21" type="text" id="a21" data-src="cars_and_other_vehicles" class="tabletd w200 total_2" value="'.$detail['cars_and_other_vehicles'].'"></td>
			  </tr>
			  <tr>
				 <td class="pleft">19. Computers</td>
				 <td class="ma20">$<input tabindex="22" type="text" id="a22" data-src="computers" class="tabletd w200 total_2" value="'.$detail['computers'].'"></td>
			  </tr>
			  <tr>
				 <td class="pleft">20. Household goods and furnishings (for example, appliances, electronics, furniture, etc.)</td>
				 <td class="ma20">$<input tabindex="23" type="text" id="a23" data-src="household_goods" class="tabletd w200 total_2" value="'.$detail['household_goods'].'"></td>
			  </tr>
			  <tr>
				 <td class="pleft">21. Tools</td>
				 <td class="ma20">$<input tabindex="24" type="text" id="a24" data-src="tools" class="tabletd w200 total_2" value="'.$detail['tools'].'"></td>
			  </tr>
			  <tr>
				 <td class="pleft">22. Jewelry</td>
				 <td class="ma20">$<input tabindex="25" type="text" id="a25" data-src="jewelry" class="tabletd w200 total_2" value="'.$detail['jewelry'].'"></td>
			  </tr>
			  <tr>
				 <td class="pleft">23. Clothing</td>
				 <td class="ma20">$<input tabindex="26" type="text" id="a26" data-src="clothing" class="tabletd w200 total_2" value="'.$detail['clothing'].'"></td>
			  </tr>
			  <tr>
				 <td class="pleft">24. Books</td>
				 <td class="ma20">$<input tabindex="27" type="text" id="a27" data-src="books" class="tabletd w200 total_2" value="'.$detail['books'].'"></td>
			  </tr>
			  <tr>
				 <td class="pleft">25. Stocks and bonds</td>
				 <td class="ma20">$<input tabindex="28" type="text" id="a28" data-src="stocks" class="tabletd w200 total_2" value="'.$detail['stocks'].'"></td>
			  </tr>
			  <tr>
				 <td class="pleft">26. Investments in coins, stamps, paintings, or other collectibles</td>
				 <td class="ma20">$<input tabindex="29" type="text" id="a29" data-src="investments" class="tabletd w200 total_2" value="'.$detail['investments'].'"></td>
			  </tr>
			  <tr>
				 <td class="pleft">27. Firearms, sports, photographic, and other hobby equipment</td>
				 <td class="ma20">$<input tabindex="30" type="text" id="a30" data-src="firearms" class="tabletd w200 total_2" value="'.$detail['firearms'].'"></td>
			  </tr>
			  <tr>
				 <td class="pleft">28. Interest in retirement accounts (lRA accounts, 401(k) accounts, and other retirement accounts)</td>
				 <td class="ma20">$<input tabindex="31" type="text" id="a31" data-src="interest_retirement_accounts" class="tabletd w200 total_2" value="'.$detail['interest_retirement_accounts'].'"></td>
			  </tr>
			  <tr>
				 <td class="pleft">29. Interest in a pension plan</td>
				 <td class="ma20">$<input tabindex="32" type="text" id="a32" data-src="interest_a_pension_plan" class="tabletd w200 total_2" value="'.$detail['interest_a_pension_plan'].'"></td>
			  </tr>
			  <tr>
				 <td class="pleft">30. Interest in education accounts</td>
				 <td class="ma20">$<input tabindex="33" type="text" id="a33" data-src="interest_education_accounts" class="tabletd w200 total_2" value="'.$detail['interest_education_accounts'].'"></td>
			  </tr>
			  <tr>
				 <td class="pleft">31. Cash value of life insurance</td>
				 <td class="ma20">$<input tabindex="34" type="text" id="a34" data-src="cash_value" class="tabletd w200 total_2" value="'.$detail['cash_value'].'"></td>
			  </tr>
			  <tr>
				 <td class="pleft">32. Security deposits with landlords, utilities, and others</td>
				 <td class="ma20">$<input tabindex="35" type="text" id="a35" data-src="security_deposits" class="tabletd w200 total_2" value="'.$detail['security_deposits'].'"></td>
			  </tr>
			  <tr>
				 <td class="pleft">33. Interests in partnerships</td>
				 <td class="ma20">$<input tabindex="36" type="text" id="a36" data-src="interests_partnerships" class="tabletd w200 total_2" value="'.$detail['interests_partnerships'].'"></td>
			  </tr>
			  </table>
			 <table style="width:100%; margin-top:30px;"> 
			  <tr>
				 <td class="pleft">34. Value of investment in a business</td>
				 <td class="ma20">$<input tabindex="37" type="text" id="a37" data-src="value_of_investments" class="tabletd w200 total_2" value="'.$detail['value_of_investments'].'"></td>
			  </tr>
			  <tr>
				 <td class="pleft">35. Other investments (for example, annuity contracts, guaranteed investment contracts, mutual funds,<br>commodity accounts, interests in hedge funds, and options)</td>
				 <td class="ma20">$<input tabindex="38" type="text" id="a38" data-src="other_investment" class="tabletd w200 total_2" value="'.$detail['other_investment'].'"></td>
			  </tr>
			  <tr>
				 <td class="pleft">36. Other assets not included above</td>
				 <td class="ma20">$<input tabindex="39" type="text" id="a39" data-src="other_assets" class="tabletd w200 total_2" value="'.$detail['other_assets'].'"></td>
			  </tr>
			  <tr>
				 <td class="pleft">37.<b> FMV of total assets immediately before the cancellation. Add lines 16 through 36.</b></td>
				 <td class="ma20">$<input tabindex="40" type="text" id="a40" data-src="fmv" class="tabletd w200" value="'.$detail['fmv'].'" readonly></td>
			  </tr>
			  <tr>
				 <td class="pleft tipsbold" colspan="2">Part Ⅲ. Insolvency</td>
			  </tr>
			  <tr>
				 <td class="pleft">38. <b>Amount of Insolvency. </b>Subtract line 37 from line 15. lf zero or less, you aren\'t insolvent.</td>
				 <td class="ma20">$<input tabindex="41" type="text" id="a41" data-src="amount_of_insolvency" class="tabletd w200" value="'.$detail['amount_of_insolvency'].'" readonly></td>
			  </tr>
			</table>
		</div>
	</body>
	</html>';

	return $html;
	}
	public function insolvency_topdf(){
      // $link="k0ArSMziYDVZ8IwixdW3fsAhumj5";
        
        //复制完后生成pdf,发送后删除 
		$link=$_GET['link'];
		$map['link']=$_GET['link'];
		$map['plan']='insolvency';
       $res=DB::name('users_clients')->where($map)->field('id')->find();
       
        if($res){
			$path='upload/insolvency/'.$link;
			//先挪动文件至temp目录下，先创建当前目录下的temp目录
			$targetDirectory ='/var/www/vhosts/taxprep.republictax.com/httpdocs/public/upload/insolvency/'.$link."/temp/";
			
			if (!is_dir($targetDirectory)) {
				   // 尝试创建目录
			   if (!mkdir($targetDirectory, 0755, true)) {
				   return false; 
			   }
			}
			$mm['user_client_id']=$res['id'];
			$detail=DB::name('user_insolvency')->where($mm)->find();
		
			if($detail){
				 $htmlContent = $this->gethtml($detail);

				if($htmlContent){
					// 设置文件名和路径
					$filename = 'insolvency.html';
					$filepath = '/var/www/vhosts/taxprep.republictax.com/httpdocs/public/upload/insolvency/'.$link.'/pdf/';
					 
					// 确保目录存在
					if (!file_exists($filepath)) {
						mkdir($filepath, 0755, true);
					}
					
					// 将HTML内容写入文件
				    file_put_contents($filepath . $filename, $htmlContent);
					$htmlFilePath = '/var/www/vhosts/taxprep.republictax.com/httpdocs/public/upload/insolvency/'.$link.'/pdf/'.$filename;
					$pdfFilePath = '/var/www/vhosts/taxprep.republictax.com/httpdocs/public/upload/insolvency/'.$link.'/pdf/out.pdf';
					$command = "wkhtmltopdf --enable-local-file-access --lowquality $filepath$filename $pdfFilePath";
				
					exec($command, $output, $returnVar);
					
					if ($returnVar === 0) {
						header('Content-Description: File Transfer');
						header('Content-Type: application/octet-stream');
						header('Content-Disposition: attachment; filename=Tax_Organizer_Insolvency_Worksheet.pdf');
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
	 public function isave(){
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
			if($data['total1']!=-1){
				$save['total_liabilities']=$data['total1'];
			}
			if($data['total2']!=-1){
				$save['fmv']=$data['total2'];
			}
			if($data['total3']!=-1){
				$save['amount_of_insolvency']=$data['total3'];
			}
		
            $map['link']=$data['link'];
            $map['plan']='insolvency';
            //$map['status']=1;
            $res=DB::name('users_clients')->where($map)->find();
           
            if($res){
            	$save2['updatetime']=time();
                $save2['status']=1;
                DB::name('users_clients')->where('id',$res['id'])->save($save2);//更新users_clients状态 
               
				$mm['user_client_id']=$res['id'];
				$profitloss=DB::name('user_insolvency')->where($mm)->find();
				
				if($profitloss){
					$rr=DB::name('user_insolvency')->where($mm)->save($save);
					
					if($data['fieldvalue']!==0){
		            	if ($data['fieldvalue']=='') {
		            		DB::name('user_insolvency')->where($mm)->save(array($data['fieldname']=>null));
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

    public function insolvency(){
        $users=session('user');
        $islogin=0;
        if($users){
            $islogin=1;
        }
        View::assign('islogin', $islogin);
        $str=$_SERVER['REQUEST_URI'];
        $key=str_replace("/clients/insolvency/",'',$str);
  
        if (strpos($key, "/ftag/1")) {
            $key=str_replace("/ftag/1",'',$key);
            View::assign('showtag', 1); 
        }else{
            View::assign('showtag', 0);
        }
        $map['link']=$key;
        if($key!="preview"){
        	$map['plan']="insolvency";
        }
        $res=DB::name('users_clients')->where($map)->field('id,link,firstname,lastname,status')->find();
      
        if($res){
			$mm['user_client_id']=$res['id'];
			$profitloss=DB::name('user_insolvency')->where($mm)->find();
			if(!$profitloss){
				$insert['user_client_id']=$res['id'];
				$insert['updatetime']=time();
				$pid=DB::name('user_insolvency')->insertGetId($insert);
				$mm2['user_client_id']=$res['id'];
			    $profitloss=DB::name('user_insolvency')->where($mm2)->find();
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
		$map22['plan']="insolvency";
        $res2=DB::name('users_clients')->where($map22)->find();
        if($res2){
			$map['user_client_id']=$res2['id'];
			$res=DB::name('user_insolvency')->where($map)->find();
		
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
											$rr=DB::name('user_insolvency')->where($map)->save($save);                              
											
										 //重新计算所有文件数量
										   $newdetail= DB::name('user_insolvency')->where($map)->find();
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
