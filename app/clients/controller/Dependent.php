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

class Dependent extends Base
{
    protected function initialize()
    {
        parent::initialize();
		$upload_pname_ary=array('page15-1'=>'w2','page15-12'=>'ssa_rrb_1099','page15-10'=>'form_2439','page15-11'=>'1099_r','page15-13-11-0'=>'1099_misc','page15-13-11-1'=>'1099_misc','page15-13-11-2'=>'1099_misc','page15-13-11-3'=>'1099_misc','page15-13-11-4'=>'1099_misc','page15-13-15-0'=>'1098','page15-13-15-1'=>'1098','page15-13-15-2'=>'1098','page15-13-15-3'=>'1098','page15-13-15-4'=>'1098','page15-13-17-0'=>'assets_purchased_business');
         $this->upload_pname_ary=$upload_pname_ary;
         View::assign('upload_pname_ary', $upload_pname_ary);
    }
	public function signcheck(){
      //查看签名是否存在
       if (IS_POST) {        
        $data = input('post.');
        if(!$data['link']){
           return json(['code'=>0,'msg'=>'Error']);
        }
        $map['link']=$data['link'];
		$link=$data['link'];
        $res=DB::name('users_clients')->where($map)->find();
        if($res){
			$mm['user_client_id']=$res['id'];
			$profitloss=DB::name('user_dependent')->where($mm)->find();
			$old_sign_con='/var/www/vhosts/taxprep.republictax.com/httpdocs/public/'.$profitloss['signimage'];
            if (is_file($old_sign_con)&&file_exists($old_sign_con)) {
                  return json(['code'=>1,'img'=>$profitloss['signimage']]);
             }else{
				 return json(['code'=>0,'img'=>'']);
			 }
		}else{
			return json(['code'=>0,'msg'=>'Error Link']);
		}
       }
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
		$map['plan']="dependent";
        $res=DB::name('users_clients')->where($map)->find();
          
        if($res){
			$mm['user_client_id']=$res['id'];
			$profitloss=DB::name('user_dependent')->where($mm)->find();
			
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
		$map['plan']="dependent";
        $res=DB::name('users_clients')->where($map)->find();
          
        if($res){
			$mm['user_client_id']=$res['id'];
			$profitloss=DB::name('user_dependent')->where($mm)->find();
			
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
		$map['plan']="dependent";
		$link=$data['link'];
        $res=DB::name('users_clients')->where($map)->find();
        if($res){
			$mm['user_client_id']=$res['id'];
			$profitloss=DB::name('user_dependent')->where($mm)->find();
			$sign_name= uniqid() . '.png';
			$targetDirectory='/var/www/vhosts/taxprep.republictax.com/httpdocs/public/upload/dependent/'.$link."/signaturet/";
            $filename_sign = '/var/www/vhosts/taxprep.republictax.com/httpdocs/public/upload/dependent/'.$link."/signaturet/".$sign_name;//签名的路径
			if (!is_dir($targetDirectory)) {
			   if (!mkdir($targetDirectory, 0755, true)) {
				   return false; 
			   }
			}
			
            $sign_path='/upload/dependent/'.$link.'/signaturet/' . $sign_name;//存储路径
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
             Db::name('user_dependent')->where('id',$profitloss['id'])->update($new);
			 return json(['code'=>0,'img'=>$new['signimage']]);
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
			$profitloss=DB::name('user_dependent')->where($mm)->find();
			$sign_name= uniqid() . '.png';
			$targetDirectory='/var/www/vhosts/taxprep.republictax.com/httpdocs/public/upload/dependent/'.$link."/signaturet/";
            $filename_sign = '/var/www/vhosts/taxprep.republictax.com/httpdocs/public/upload/dependent/'.$link."/signaturet/".$sign_name;//签名的路径
			if (!is_dir($targetDirectory)) {
			   if (!mkdir($targetDirectory, 0755, true)) {
				   return false; 
			   }
			}
			
            $sign_path='/upload/dependent/'.$link.'/signaturet/' . $sign_name;//存储路径
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
             Db::name('user_dependent')->where('id',$profitloss['id'])->update($new);
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
		   $map['plan']='dependent';
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
	public function depsubmit(){
      // $link="k0ArSMziYDVZ8IwixdW3fsAhumj5";
        
        //复制完后生成pdf,发送后删除 
		$link=$_POST['link'];
		$map['link']=$_POST['link'];
		$map['plan']='dependent';
        $res=DB::name('users_clients')->where($map)->field('id,caseid')->find();
       
        if($res){
			$path='upload/dependent/'.$link;
			//先挪动文件至temp目录下，先创建当前目录下的temp目录
			$targetDirectory ='/var/www/vhosts/taxprep.republictax.com/httpdocs/public/upload/dependent/'.$link."/temp/";
			
			if (!is_dir($targetDirectory)) {
				   // 尝试创建目录
			   if (!mkdir($targetDirectory, 0755, true)) {
				   return false; 
			   }
			}
			$mm['user_client_id']=$res['id'];
			$detail=DB::name('user_dependent')->where($mm)->find();
		
			if($detail){
				 $htmlContent = $this->gethtml($detail);

				if($htmlContent){
					// 设置文件名和路径
					$filename = 'dependent.html';
					$filepath = '/var/www/vhosts/taxprep.republictax.com/httpdocs/public/upload/dependent/'.$link.'/pdf/';
					 
					// 确保目录存在
					if (!file_exists($filepath)) {
						mkdir($filepath, 0755, true);
					}
					 
					// 将HTML内容写入文件
				    file_put_contents($filepath . $filename, $htmlContent);
					$htmlFilePath = '/var/www/vhosts/taxprep.republictax.com/httpdocs/public/upload/dependent/'.$link.'/pdf/'.$filename;
					$pdfFilePath = '/var/www/vhosts/taxprep.republictax.com/httpdocs/public/upload/dependent/'.$link.'/pdf/out.pdf';
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
						  $fname="Dependent_Questionnaire".$fname.".pdf";
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
				$profitloss=DB::name('user_dependent')->where($mm)->find();
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
			<div style="text-align:center;font-size:16px; font-weight:bold;">Dependent Questionnaire</div>
			<div style="text-align:center;">All Questions must be answered for each year you want to claim a dependent. Any missing and/or blank information will result in your dependent not being claimed on your tax return.</div>
			<div style="text-align:center;">***Please complete a separate questionnaire for each dependent***</div>
			<table style="width:100%;">
			  <tr>
				 <td class=" noborder w100">Your Name:</td>
				 <td class="noborder borderBottom"><input tabindex="3" type="text" id="a3" data-src="name" class="tabletd" value="'.$detail['name'].'"></td>
				 <td class="noborder w100">TAX YEAR</td>
				 <td class="noborder w100 borderBottom"><input tabindex="4" type="text" id="a4" data-src="tax_year" class="tabletd w100" value="'.$detail['tax_year'].'"></td>
			  </tr>
			  <tr>
				 <td class="noborder w100">Dependent\'s Name:</td>
				 <td class="noborder" colspan="3"><div class="borderBottom" style="display:inline-block"><input tabindex="5" type="text" id="a5" data-src="dependent_name" class="tabletd" value="'.$detail['dependent_name'].'" style="width:500px;"></div>(First, Middle, Last)</td>
			  </tr>
			</table>
			<table style="width:100%;">
			   <tr>
				 <td class="noborder w100">Dependent\'s DOB:</td>
				 <td class="noborder borderBottom"><input tabindex="6" type="text" id="a6" data-src="dependent_dob" class="tabletd" value="'.$detail['dependent_dob'].'"></td>
				 <td class="noborder w100">Dependent\'s SSN:</td>
				 <td class="noborder  borderBottom"><input tabindex="7" type="text" id="a7" data-src="dependent_ssn" class="tabletd " value="'.$detail['dependent_ssn'].'"></td>
			  </tr>
			</table>
			<table style="width:100%;">
			   <tr>
				 <td class="noborder" style="text-align:right;">Dependent\'s relationship to you: (ex. Son, Daughter, Grandchild, etc):</td>
				 <td class="noborder borderBottom w200"><input tabindex="8" type="text" id="a8" data-src="dependent_relationship" class="tabletd" value="'.$detail['dependent_relationship'].'"></td>
			  </tr>
			</table>';
			 $home_in_us=$detail['home_in_us']; 
			 $home_in_us1='';
			 $home_in_us2='';
			 switch($home_in_us){
				 case 1:
				 $home_in_us1="checked";
				 break;
				 case 2:
				 $home_in_us2="checked";
				 break;
			 }
			 $dependent_live_with=$detail['dependent_live_with']; 
			 $dependent_live_with1='';
			 $dependent_live_with2='';
			 switch($dependent_live_with){
				 case 1:
				 $dependent_live_with1="checked";
				 break;
				 case 2:
				 $dependent_live_with2="checked";
				 break;
			 }
			 $provide_more_support=$detail['provide_more_support']; 
			 $provide_more_support1='';
			 $provide_more_support2='';
			 switch($provide_more_support){
				 case 1:
				 $provide_more_support1="checked";
				 break;
				 case 2:
				 $provide_more_support2="checked";
				 break;
			 }
			 $claim_as_their_dependent=$detail['claim_as_their_dependent']; 
			 $claim_as_their_dependent1='';
			 $claim_as_their_dependent2='';
			 switch($claim_as_their_dependent){
				 case 1:
				 $claim_as_their_dependent1="checked";
				 break;
				 case 2:
				 $claim_as_their_dependent2="checked";
				 break;
			 }
			$html.='
			<div style="text-align:right;">(CIRCLE ONE)</div>
			<table style="width:100%;">
			   <tr>
				 <td class="noborder">1) Was your main home in the US for more than half of the above listed year?</td>
				 <td class="noborder">
				    <input tabindex="9" type="checkbox" name="home_in_us" value="1" class="fieldcheckbox " data-src="home_in_us" '.$home_in_us1.'>Yes 
					<input tabindex="9" type="checkbox" name="home_in_us" value="2" class="fieldcheckbox " data-src="home_in_us" '.$home_in_us2.'>No
				 </td>
			  </tr>
			  <tr>
				 <td class="noborder">2) Did the Dependent live with you for the entire year?</td>
				 <td class="noborder">
				    <input tabindex="10" type="checkbox" name="dependent_live_with" value="1" class="fieldcheckbox " data-src="dependent_live_with" '.$dependent_live_with1.'>Yes 
					<input tabindex="10" type="checkbox" name="dependent_live_with" value="2" class="fieldcheckbox " data-src="dependent_live_with" '.$dependent_live_with2.'>No
				 </td>
			  </tr>
			  <tr>
				 <td class="noborder" style="text-align:right;">2a) If "NO", how many DAYS did the child live with you in the above tax year?</td>
				 <td class="noborder borderBottom w100">
				    <input tabindex="11" type="text" id="a9" data-src="days_live_with" class="tabletd" value="'.$detail['days_live_with'].'">
				 </td>
			  </tr>
			  <tr>
				 <td class="noborder">3) Did you provide more than half of the support for the dependent in that year?</td>
				 <td class="noborder">
				    <input tabindex="12" type="checkbox" name="provide_more_support" value="1" class="fieldcheckbox" '.$provide_more_support1.'>Yes 
					<input tabindex="12" type="checkbox" name="provide_more_support" value="2" class="fieldcheckbox" '.$provide_more_support2.'>No
				 </td>
			  </tr>
			  <tr>
				 <td class="noborder">4) Could someone else claim this person as their dependent?</td>
				 <td class="noborder">
				    <input tabindex="13" type="checkbox" name="claim_as_their_dependent" value="1" class="fieldcheckbox" '.$claim_as_their_dependent1.'>Yes 
					<input tabindex="13" type="checkbox" name="claim_as_their_dependent" value="2" class="fieldcheckbox" '.$claim_as_their_dependent2.'>No
				 </td>
			  </tr>
			  <tr>
				 <td class="noborder">If you answered "YES" to #4, what is the relationship of this person to dep.?</td>
				 <td class="noborder borderBottom">
				    <input tabindex="14" type="text" id="a10" data-src="relationship_to_dep" class="tabletd " value="'.$detail['relationship_to_dep'].'">
				 </td>
			  </tr>';
			 $certain_person_didnot_claim=$detail['certain_person_didnot_claim']; 
			 $certain_person_didnot_claim1='';
			 $certain_person_didnot_claim2='';
			 switch($certain_person_didnot_claim){
				 case 1:
				 $certain_person_didnot_claim1="checked";
				 break;
				 case 2:
				 $certain_person_didnot_claim2="checked";
				 break;
			 } 
			 $provide_proof=$detail['provide_proof']; 
			 $provide_proof1='';
			 $provide_proof2='';
			 switch($provide_proof){
				 case 1:
				 $provide_proof1="checked";
				 break;
				 case 2:
				 $provide_proof2="checked";
				 break;
			 } 
			  $html.='<tr>
				 <td class="noborder">4a) Are you 100% certain that the other parent/person DID NOT claim this dep.?</td>
				 <td class="noborder">
			<input tabindex="14" type="checkbox" name="certain_person_didnot_claim" value="1" class="fieldcheckbox " '.$certain_person_didnot_claim1.'>Yes 
			<input tabindex="14" type="checkbox" name="certain_person_didnot_claim" value="2" class="fieldcheckbox " '.$certain_person_didnot_claim2.'>No
				 </td>
			  </tr>
			  <tr>
				 <td class="noborder">5) Can you provide proof and documentation that this is your dependent?</td>
				 <td class="noborder">
					<input tabindex="15" type="checkbox" name="provide_proof" value="1" class="fieldcheckbox" '.$provide_proof1.'>Yes 
					<input tabindex="15" type="checkbox" name="provide_proof" value="2" class="fieldcheckbox" '.$provide_proof2.'>No
				 </td>
			  </tr>
			  <tr>
				 <td class="noborder">5a) Please provide a copy of a report card, statement,etc w/ child\'s name and your address</td>
				 <td class="noborder borderBottom w100">
				    <input tabindex="16" type="text" id="a11" data-src="provide_copy_of_card" class="tabletd" value="'.$detail['provide_copy_of_card'].'">
				 </td>
			  </tr>';
			 $credits_disallowed=$detail['credits_disallowed']; 
			 $credits_disallowed1='';
			 $credits_disallowed2='';
			 switch($credits_disallowed){
				 case 1:
				 $credits_disallowed1="checked";
				 break;
				 case 2:
				 $credits_disallowed2="checked";
				 break;
			 } 
			 $complete_required=$detail['complete_required']; 
			 $complete_required1='';
			 $complete_required2='';
			 switch($complete_required){
				 case 1:
				 $complete_required1="checked";
				 break;
				 case 2:
				 $complete_required2="checked";
				 break;
			 } 
			 $was_dependent_married=$detail['was_dependent_married']; 
			 $was_dependent_married1='';
			 $was_dependent_married2='';
			 switch($was_dependent_married){
				 case 1:
				 $was_dependent_married1="checked";
				 break;
				 case 2:
				 $was_dependent_married2="checked";
				 break;
			 } 
			 $were_you_alien=$detail['were_you_alien']; 
			 $were_you_alien1='';
			 $were_you_alien2='';
			 switch($were_you_alien){
				 case 1:
				 $were_you_alien1="checked";
				 break;
				 case 2:
				 $were_you_alien2="checked";
				 break;
			 }
             $be_claimed_as_dependent=$detail['be_claimed_as_dependent']; 
			 $be_claimed_as_dependent1='';
			 $be_claimed_as_dependent2='';
			 switch($be_claimed_as_dependent){
				 case 1:
				 $be_claimed_as_dependent1="checked";
				 break;
				 case 2:
				 $be_claimed_as_dependent2="checked";
				 break;
			 } 	
             $was_dependent_a_student=$detail['was_dependent_a_student']; 
			 $was_dependent_a_student1='';
			 $was_dependent_a_student2='';
			 switch($was_dependent_a_student){
				 case 1:
				 $was_dependent_a_student1="checked";
				 break;
				 case 2:
				 $was_dependent_a_student2="checked";
				 break;
			 }			 
			  $html.='
			  <tr>
				 <td class="noborder">6) Were any credits (child tax credit, EIC) disallowed by IRS or State previously?</td>
				 <td class="noborder">
					<input tabindex="17" type="checkbox" name="credits_disallowed" value="1" class="fieldcheckbox " '.$credits_disallowed1.'>Yes 
					<input tabindex="17" type="checkbox" name="credits_disallowed" value="2" class="fieldcheckbox " '.$credits_disallowed2.'>No
				 </td>
			  </tr>
			  <tr>
				 <td class="noborder" style="text-align:right;">If "YES" to #6, did you complete required recertification forms?</td>
				 <td class="noborder">
					<input tabindex="18" type="checkbox" name="complete_required" value="1" class="fieldcheckbox" '.$complete_required1.'>Yes 
					<input tabindex="18" type="checkbox" name="complete_required" value="2" class="fieldcheckbox" '.$complete_required1.'>No
				 </td>
			  </tr>
			  <tr>
				 <td class="noborder">7) Was this Dependent Married at any time during the tax year?</td>
				 <td class="noborder">
					<input tabindex="19" type="checkbox" name="was_dependent_married" value="1" class="fieldcheckbox" '.$was_dependent_married1.'>Yes 
					<input tabindex="19" type="checkbox" name="was_dependent_married" value="2" class="fieldcheckbox" '.$was_dependent_married2.'>No
				 </td>
			  </tr>
			  <tr>
				 <td class="noborder" >8) Were you or your spouse a non‐resident alien for any part of the year?</td>
				 <td class="noborder">
					<input tabindex="20" type="checkbox" name="were_you_alien" value="1" class="fieldcheckbox" data-src="were_you_alien" '.$were_you_alien1.'>Yes 
					<input tabindex="20" type="checkbox" name="were_you_alien" value="2" class="fieldcheckbox" data-src="were_you_alien" '.$were_you_alien2.'>No
				 </td>
			  </tr>
			  <tr>
				 <td class="noborder" >9) Could you be claimed as the dependent of someone else?</td>
				 <td class="noborder">
					<input tabindex="21" type="checkbox" name="be_claimed_as_dependent" value="1" class="fieldcheckbox" '.$be_claimed_as_dependent1.'>Yes 
					<input tabindex="21" type="checkbox" name="be_claimed_as_dependent" value="2" class="fieldcheckbox" '.$be_claimed_as_dependent2.'>No
				 </td>
			  </tr>
			  <tr>
				 <td class="noborder">10) Was the dependent a student for the entire year listed above?</td>
				 <td class="noborder">
					<input tabindex="22" type="checkbox" name="was_dependent_a_student" value="1" class="fieldcheckbox" '.$was_dependent_a_student1.'>Yes 
					<input tabindex="22" type="checkbox" name="was_dependent_a_student" value="2" class="fieldcheckbox" '.$was_dependent_a_student2.'>No
				 </td>
			  </tr>
			  <tr>
				 <td class="noborder" colspan="2">11) If you are not the parent of this person, why is the parent not claiming them?</td>
			  </tr>
			  <tr>
				 <td class="noborder borderBottom" colspan="2"><input tabindex="23" style="width:820px;" type="text" class="tabletd" value="'.$detail['why_is_not_claim'].'"></td>
			  </tr>';
			 $is_over18=$detail['is_over18']; 
			 $is_over181='';
			 $is_over182='';
			 switch($is_over18){
				 case 1:
				 $is_over181="checked";
				 break;
				 case 2:
				 $is_over182="checked";
				 break;
			 }	
			 $can_provide_medical_documentation=$detail['can_provide_medical_documentation']; 
			 $can_provide_medical_documentation1='';
			 $can_provide_medical_documentation2='';
			 switch($is_over18){
				 case 1:
				 $can_provide_medical_documentation1="checked";
				 break;
				 case 2:
				 $can_provide_medical_documentation2="checked";
				 break;
			 }
			$was_a_full_student=$detail['was_a_full_student']; 
			 $was_a_full_student1='';
			 $was_a_full_student2='';
			 switch($was_a_full_student){
				 case 1:
				 $was_a_full_student1="checked";
				 break;
				 case 2:
				 $was_a_full_student2="checked";
				 break;
			 }	
			 $marital_status=$detail['marital_status']; 
			 $marital_status1='';
			 $marital_status2='';
			 switch($marital_status){
				 case 1:
				 $marital_status1="checked";
				 break;
				 case 2:
				 $marital_status2="checked";
				 break;
				 case 3:
				 $marital_status3="checked";
				 break;
				 case 4:
				 $marital_status4="checked";
				 break;
				 case 5:
				 $marital_status5="checked";
				 break;
			 }	
			 $live_with_spouse=$detail['live_with_spouse']; 
			 $live_with_spouse1='';
			 $live_with_spouse2='';
			 switch($live_with_spouse){
				 case 1:
				 $live_with_spouse1="checked";
				 break;
				 case 2:
				 $live_with_spouse2="checked";
				 break;
			 }	
			$have_any_income=$detail['have_any_income']; 
			 $have_any_income1='';
			 $have_any_income2='';
			 switch($have_any_income){
				 case 1:
				 $have_any_income1="checked";
				 break;
				 case 2:
				 $have_any_income2="checked";
				 break;
			 }	
			  $html.='
			  <tr>
				 <td class="noborder">12) Is the dependent over 18 and disabled</td>
				 <td class="noborder">
					<input tabindex="24" type="checkbox" name="is_over18" value="1" class="fieldcheckbox" '.$is_over181.'>Yes 
					<input tabindex="24" type="checkbox" name="is_over18" value="2" class="fieldcheckbox" '.$is_over182.'>No
				 </td>
			  </tr>
			  <tr>
				 <td class="noborder">12 a) Can you provide medical documentation for dependent if disabled?</td>
				 <td class="noborder">
					<input tabindex="25" type="checkbox" name="can_provide_medical_documentation" value="1" class="fieldcheckbox" '.$can_provide_medical_documentation1.'>Yes 
					<input tabindex="25" type="checkbox" name="can_provide_medical_documentation" value="2" class="fieldcheckbox" '.$can_provide_medical_documentation2.'>No
				 </td>
			  </tr>
			  <tr>
				 <td class="noborder">12 b) If Dependent was OVER AGE 18 in that year, were they a full time student?</td>
				 <td class="noborder">
					<input tabindex="26" type="checkbox" name="was_a_full_student" value="1" class="fieldcheckbox" '.$was_a_full_student1.'>Yes 
					<input tabindex="26" type="checkbox" name="was_a_full_student" value="2" class="fieldcheckbox" '.$was_a_full_student2.'>No
				 </td>
			  </tr>
			 </table>
			 <table style="width:100%;">
			  <tr>
				 <td class="noborder">13) Your Marital Status: (Circle One)</td>
				 <td class="noborder">
					<input tabindex="27" type="checkbox" name="marital_status" value="1" class="fieldcheckbox" '.$marital_status1.'>Unmarried 
					<input tabindex="27" type="checkbox" name="marital_status" value="2" class="fieldcheckbox" '.$marital_status2.'>Married
					<input tabindex="27" type="checkbox" name="marital_status" value="3" class="fieldcheckbox" '.$marital_status3.'>Divorced
					<input tabindex="27" type="checkbox" name="marital_status" value="4" class="fieldcheckbox" '.$marital_status4.'>Legally
					<input tabindex="27" type="checkbox" name="marital_status" value="5" class="fieldcheckbox" '.$marital_status5.'>Separated					
				 </td>
			  </tr>
			  <tr>
				 <td class="noborder">14) If Married, did you live with your spouse at least 1 day during the Year?</td>
				 <td class="noborder">
					<input tabindex="28" type="checkbox" name="live_with_spouse" value="1" class="fieldcheckbox" '.$live_with_spouse1.'>Yes 
					<input tabindex="28" type="checkbox" name="live_with_spouse" value="2" class="fieldcheckbox" '.$live_with_spouse2.'>No
				 </td>
			  </tr>
			  <tr>
				 <td class="noborder">15) Did your Dependent have any income in the Tax Year listed above?</td>
				 <td class="noborder">
					<input tabindex="28" type="checkbox" name="have_any_income" value="1" class="fieldcheckbox" '.$have_any_income1.'>Yes 
					<input tabindex="28" type="checkbox" name="have_any_income" value="2" class="fieldcheckbox" '.$have_any_income2.'>No
				 </td>
			  </tr>
			  <tr>
				 <td class="noborder" style="text-align:right;">If Yes to 15) How Much Exactly $</td>
				 <td class="noborder borderBottom w100"><input tabindex="29" type="text" id="a13" data-src="income_amount" class="tabletd" value="'.$detail['income_amount'].'"></td>
			  </tr>';
			 $pay_for_childcare=$detail['pay_for_childcare']; 
			 $pay_for_childcare1='';
			 $pay_for_childcare2='';
			 switch($pay_for_childcare){
				 case 1:
				 $pay_for_childcare1="checked";
				 break;
				 case 2:
				 $pay_for_childcare2="checked";
				 break;
			 }
			  $html.='
			  <tr>
				 <td class="noborder">16) Did you pay for childcare expenses? (Circle One)</td>
				 <td class="noborder">
					<input tabindex="30" type="checkbox" name="pay_for_childcare" value="1" class="fieldcheckbox" '.$pay_for_childcare1.'>Yes 
					<input tabindex="30" type="checkbox" name="pay_for_childcare" value="2" class="fieldcheckbox" '.$pay_for_childcare2.'>No
				 </td>
			  </tr>
			  <tr>
				 <td class="noborder" style="text-align:right;">If Yes to 16) How Much Exactly? $</td>
				 <td class="noborder borderBottom w100"><input tabindex="31" type="text" id="a14" data-src="chidcare_expense" class="tabletd" value="'.$detail['chidcare_expense'].'"></td>
			  </tr>
			</table>
		    <table style="width:100%;">
			   <tr>
				 <td class="noborder">16b) Name of Schools or Person:</td>
				 <td class="noborder borderBottom"><input tabindex="32" type="text" id="a15" data-src="name_of_school" class="tabletd" value="'.$detail['name_of_school'].'"  style="width:500px;"></td>
			  </tr>
			  <tr>
				 <td class="noborder">16c) EIN or SSN of school or person:</td>
				 <td class="noborder borderBottom"><input tabindex="33" type="text" id="a16" data-src="ein_of_school" class="tabletd" value="'.$detail['ein_of_school'].'"  style="width:500px;"></td>
			  </tr>
			</table>
			<div class=" tipsbold"><span class="borderBottom">Please read the following info: Tie‐Breaker Rules</span></div>
			<div>Under the tie‐breaker rules, the child is generally treated as a qualifying child of:</div>
                <div style="margin-left:30px;">-The parents if they file a joint return</div>
			    <div style="margin-left:30px;">-The parent, if only one person is the child\'s parent</div>	
				<div style="margin-left:30px;">-The parent with whom the child lived the longest durgin the tax year</div>
				<div style="margin-left:30px;">-The parent with the highest income if the child lived with each parent for equal time</div>
				<div style="margin-left:30px;">-The person with the highest income if no parent can claim the child</div>
				<div style="margin-left:30px;">−A person with higher AGI than any parent who can claim the child as a QC but does not</div>';
			 $have_you_read=$detail['have_you_read']; 
			 $have_you_read1='';
			 $have_you_read2='';
			 switch($have_you_read){
				 case 1:
				 $have_you_read1="checked";
				 break;
				 case 2:
				 $have_you_read2="checked";
				 break;
			 }
			 $answer_honestly=$detail['answer_honestly']; 
			 $answer_honestly1='';
			 $answer_honestly2='';
			 switch($answer_honestly){
				 case 1:
				 $answer_honestly1="checked";
				 break;
				 case 2:
				 $answer_honestly2="checked";
				 break;
			 }
			 $understand=$detail['understand']; 
			 $understand1='';
			 $understand2='';
			 switch($understand){
				 case 1:
				 $understand1="checked";
				 break;
				 case 2:
				 $understand2="checked";
				 break;
			 }
			$html.='
            <table style="width:100%;">
			  <tr>
				 <td class="noborder">Have you completely read and understood the tie‐breaker rules listed above? </td>
				 <td class="noborder">
					<input tabindex="34" type="checkbox" name="have_you_read" value="1" class="fieldcheckbox" '.$have_you_read1.'>Yes 
					<input tabindex="34" type="checkbox" name="have_you_read" value="2" class="fieldcheckbox" '.$have_you_read2.'>No
				 </td>
			  </tr>
			  <tr>
				 <td class="noborder">I have answered all questions  honestly and understand all above questions/info.</td>
				 <td class="noborder">
					<input tabindex="35" type="checkbox" name="answer_honestly" value="1" class="fieldcheckbox" '.$answer_honestly1.'>Yes 
					<input tabindex="35" type="checkbox" name="answer_honestly" value="2" class="fieldcheckbox" '.$answer_honestly2.'>No
				 </td>
			  </tr>
			  <tr>
				 <td class="noborder" colspan="2">If this form is left blank, ALL questions are not answered, or answers above disqualify the dependent,they WILL NOT be included on your tax return. </td>
			  </tr>
			  <tr>
				 <td class="noborder" style="text-align:right;">I UNDERSTAND:</td>
				 <td class="noborder">
					<input tabindex="36" type="checkbox" name="understand" value="1" class="fieldcheckbox" '.$understand1.'>Yes 
					<input tabindex="36" type="checkbox" name="understand" value="2" class="fieldcheckbox" '.$understand2.'>No
				 </td>
			  </tr>
			</table>
			<table>
			   <tr>
			      <td class="noborder"id="sign">Signature:';
				  $old_sign_con='/var/www/vhosts/taxprep.republictax.com/httpdocs/public/'.$detail['signimage'];
					if (is_file($old_sign_con)&&file_exists($old_sign_con)) {
				    //  if($detail['signimage']){
					   $html.='<img src="/var/www/vhosts/taxprep.republictax.com/httpdocs/public/'.$detail['signimage'].'" style="width:220px;height:50px;">';
				     }else{
					   $html.='<img src="/var/www/vhosts/taxprep.republictax.com/httpdocs/public/static/images/sign.png" id="signimg" style="width:220px;">';
				     }
					  $html.='</td>
				 
				  <td class="noborder w100">Date:<input tabindex="37" type="text" id="a17" data-src="signdate" class="tabletd w100 borderBottom" value="'.$detail['signdate'].'"></td>
			   </tr>
			</table>
		</div>
	</body>
	</html>';

	return $html;
	}
	public function dependent_topdf(){
      // $link="k0ArSMziYDVZ8IwixdW3fsAhumj5";
        
        //复制完后生成pdf,发送后删除 
		$link=$_GET['link'];
		$map['link']=$_GET['link'];
		$map['plan']='dependent';
       $res=DB::name('users_clients')->where($map)->field('id')->find();
       
        if($res){
			$path='upload/dependent/'.$link;
			//先挪动文件至temp目录下，先创建当前目录下的temp目录
			$targetDirectory ='/var/www/vhosts/taxprep.republictax.com/httpdocs/public/upload/dependent/'.$link."/temp/";
			
			if (!is_dir($targetDirectory)) {
				   // 尝试创建目录
			   if (!mkdir($targetDirectory, 0755, true)) {
				   return false; 
			   }
			}
			$mm['user_client_id']=$res['id'];
			$detail=DB::name('user_dependent')->where($mm)->find();
		
			if($detail){
				 $htmlContent = $this->gethtml($detail);

				if($htmlContent){
					// 设置文件名和路径
					$filename = 'dependent.html';
					$filepath = '/var/www/vhosts/taxprep.republictax.com/httpdocs/public/upload/dependent/'.$link.'/pdf/';
					 
					// 确保目录存在
					if (!file_exists($filepath)) {
						mkdir($filepath, 0755, true);
					}
					
					// 将HTML内容写入文件
				    file_put_contents($filepath . $filename, $htmlContent);
					$htmlFilePath = '/var/www/vhosts/taxprep.republictax.com/httpdocs/public/upload/dependent/'.$link.'/pdf/'.$filename;
					$pdfFilePath = '/var/www/vhosts/taxprep.republictax.com/httpdocs/public/upload/dependent/'.$link.'/pdf/out.pdf';
					$command = "wkhtmltopdf --enable-local-file-access --lowquality $filepath$filename $pdfFilePath";
				
					exec($command, $output, $returnVar);
					
					if ($returnVar === 0) {
						header('Content-Description: File Transfer');
						header('Content-Type: application/octet-stream');
						header('Content-Disposition: attachment; filename=Dependent_Questionnaire.pdf');
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
	 public function depsave(){
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
			/*
			if($data['total1']!=-1){
				$save['total_liabilities']=$data['total1'];
			}
			if($data['total2']!=-1){
				$save['fmv']=$data['total2'];
			}
			if($data['total3']!=-1){
				$save['amount_of_dependent']=$data['total3'];
			}*/
		
            $map['link']=$data['link'];
            $map['plan']='dependent';
            //$map['status']=1;
            $res=DB::name('users_clients')->where($map)->find();
           
            if($res){
            	$save2['updatetime']=time();
                $save2['status']=1;
                DB::name('users_clients')->where('id',$res['id'])->save($save2);//更新users_clients状态 
               
				$mm['user_client_id']=$res['id'];
				$profitloss=DB::name('user_dependent')->where($mm)->find();
				
				if($profitloss){
					$rr=DB::name('user_dependent')->where($mm)->save($save);
					
					if($data['fieldvalue']!==0){
		            	if ($data['fieldvalue']=='') {
		            		DB::name('user_dependent')->where($mm)->save(array($data['fieldname']=>null));
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

    public function dependent(){
        $users=session('user');
        $islogin=0;
        if($users){
            $islogin=1;
        }
        View::assign('islogin', $islogin);
        $str=$_SERVER['REQUEST_URI'];
        $key=str_replace("/clients/dependent/",'',$str);
  
        if (strpos($key, "/ftag/1")) {
            $key=str_replace("/ftag/1",'',$key);
            View::assign('showtag', 1); 
        }else{
            View::assign('showtag', 0);
        }
        $map['link']=$key;
        if($key!="preview"){
        	$map['plan']="dependent";
        }
        $res=DB::name('users_clients')->where($map)->field('id,link,firstname,lastname,status')->find();
     
        if($res){
			$mm['user_client_id']=$res['id'];
			$profitloss=DB::name('user_dependent')->where($mm)->find();
			if(!$profitloss){
				$insert['user_client_id']=$res['id'];
				$insert['updatetime']=time();
				$pid=DB::name('user_dependent')->insertGetId($insert);
				$mm2['user_client_id']=$res['id'];
			    $profitloss=DB::name('user_dependent')->where($mm2)->find();
			}
			
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
		$map22['plan']="dependent";
        $res2=DB::name('users_clients')->where($map22)->find();
        if($res2){
			$map['user_client_id']=$res2['id'];
			$res=DB::name('user_dependent')->where($map)->find();
		
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
								$new_path = '/dependent/'.$link."/sheets/".$pagename;
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
											$rr=DB::name('user_dependent')->where($map)->save($save);                              
											
										 //重新计算所有文件数量
										   $newdetail= DB::name('user_dependent')->where($map)->find();
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
