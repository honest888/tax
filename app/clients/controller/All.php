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

class All extends Base
{
    protected function initialize()
    {
        parent::initialize();
    }
	public function signcheck(){
      //查看签名是否存在
       if (IS_POST) {        
        $data = input('post.');
        if(!$data['link']){
           return json(['code'=>0,'msg'=>'Error']);
        }
        $map['link']=$data['link'];
		$map['plan']="all";
		$link=$data['link'];
        $res=DB::name('users_clients')->where($map)->find();
        if($res){
			$mm['user_client_id']=$res['id'];
			$profitloss=DB::name('user_all')->where($mm)->field('id,signimage')->find();
			//echo $profitloss['signimage'];
			
			$old_sign_cons='/var/www/vhosts/taxprep.republictax.com/httpdocs/public'.trim($profitloss['signimage']);
			//echo $old_sign_cons;
			if($profitloss['signimage']){
			//if (is_file($old_sign_cont)&&file_exists($old_sign_cont)) {
                 return json(['code'=>1]);
            }else{
				 return json(['code'=>0,'msg'=>'Please sign your name!']);
			}
			
		}else{
			return json(['code'=>0,'msg'=>'Error Link']);
		}
       }
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
		$map['plan']="all";
        $res=DB::name('users_clients')->where($map)->find();
          
        if($res){
			$mm['user_client_id']=$res['id'];
			$profitloss=DB::name('user_all')->where($mm)->find();
			
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
		$map['plan']="all";
        $res=DB::name('users_clients')->where($map)->find();
          
        if($res){
			$mm['user_client_id']=$res['id'];
			$profitloss=DB::name('user_all')->where($mm)->find();
			
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
		$map['plan']="all";
		$link=$data['link'];
        $res=DB::name('users_clients')->where($map)->find();
        if($res){
			$mm['user_client_id']=$res['id'];
			$profitloss=DB::name('user_all')->where($mm)->find();
			$sign_name= uniqid() . '.png';
			$targetDirectory='/var/www/vhosts/taxprep.republictax.com/httpdocs/public/upload/all/'.$link."/signaturet/";
            $filename_sign = '/var/www/vhosts/taxprep.republictax.com/httpdocs/public/upload/all/'.$link."/signaturet/".$sign_name;//签名的路径
			if (!is_dir($targetDirectory)) {
			   if (!mkdir($targetDirectory, 0755, true)) {
				   return false; 
			   }
			}
			
            $sign_path='/upload/all/'.$link.'/signaturet/' . $sign_name;//存储路径
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
             Db::name('user_all')->where('id',$profitloss['id'])->update($new);
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
			$profitloss=DB::name('user_all')->where($mm)->find();
			$sign_name= uniqid() . '.png';
			$targetDirectory='/var/www/vhosts/taxprep.republictax.com/httpdocs/public/upload/all/'.$link."/signaturet/";
            $filename_sign = '/var/www/vhosts/taxprep.republictax.com/httpdocs/public/upload/all/'.$link."/signaturet/".$sign_name;//签名的路径
			if (!is_dir($targetDirectory)) {
			   if (!mkdir($targetDirectory, 0755, true)) {
				   return false; 
			   }
			}
			
            $sign_path='/upload/all/'.$link.'/signaturet/' . $sign_name;//存储路径
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
             Db::name('user_all')->where('id',$profitloss['id'])->update($new);
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
		   $map['plan']='all';
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
		$map['plan']='all';
        $res=DB::name('users_clients')->where($map)->field('id,caseid')->find();
       
        if($res){
			$path='upload/all/'.$link;
			//先挪动文件至temp目录下，先创建当前目录下的temp目录
			$targetDirectory ='/var/www/vhosts/taxprep.republictax.com/httpdocs/public/upload/all/'.$link."/temp/";
			
			if (!is_dir($targetDirectory)) {
				   // 尝试创建目录
			   if (!mkdir($targetDirectory, 0755, true)) {
				   return false; 
			   }
			}
			$mm['user_client_id']=$res['id'];
			$detail=DB::name('user_all')->where($mm)->find();
		
			if($detail){
				 $htmlContent = $this->gethtml($detail);

				if($htmlContent){
					// 设置文件名和路径
					$filename = 'all.html';
					$filepath = '/var/www/vhosts/taxprep.republictax.com/httpdocs/public/upload/all/'.$link.'/pdf/';
					 
					// 确保目录存在
					if (!file_exists($filepath)) {
						mkdir($filepath, 0755, true);
					}
					 
					// 将HTML内容写入文件
				    file_put_contents($filepath . $filename, $htmlContent);
					$htmlFilePath = '/var/www/vhosts/taxprep.republictax.com/httpdocs/public/upload/all/'.$link.'/pdf/'.$filename;
					$pdfFilePath = '/var/www/vhosts/taxprep.republictax.com/httpdocs/public/upload/all/'.$link.'/pdf/out.pdf';
					$command = "wkhtmltopdf --enable-local-file-access --orientation Landscape --lowquality $filepath$filename $pdfFilePath";
					
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
						  $fname=date("Ymd_H_i_s",time());
						  $fname="Tax_Organizer_ALL_".$fname.".pdf";
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
				$profitloss=DB::name('user_all')->where($mm)->find();
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
          
            text-align: left;
            white-space: nowrap; /* Prevent text from wrapping */
            overflow: hidden; /* Hide overflow content */
            text-overflow: ellipsis; /* Show ellipsis for overflow text */
			height:25px;
			font-size:12px;
        }
        input[type="text"] {
		    padding-left:20px;
       
            box-sizing: border-box;
			min-width: 100px;
			line-height:23px;
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
		.allTleft{
    width:150px;padding-right:5px;
  }
  .w170{
    width:170px!important;
  }
  .w150{
    width:150px!important;
  }
  .w130{
    width:130px!important;
  }
  .w70{
    width:30px!important;
  }
  .lineheight{
    line-height:18px;
  }
  .focus::focus {
	border-bottom:1px solid #000;
  }
	</style>
	<body>
	    <div class="page">
			<table style="width:100%;">
			    <tr>
				 <td class="noborder w100" style="font-size:15px; font-weight:bold;">Tax Year</td>
				 <td class="noborder borderBottom w100"><input tabindex="3" type="text" id="a3" data-src="tax_year" class="tabletd" value="'.$detail['tax_year'].'"></td>
				 <td class="noborder" style="padding-left:180px;font-size:16px; font-weight:bold;">Client Tax Organizer</td>
			    </tr> 
			</table>
			<div style="text-align:center;height:20px;"></div>
			<table style="width:100%;">
			    <tr>
				 <td style="font-size:16px;font-weight:bold;" class="noborder borderLeft borderTop w200 pleft">Personal Information</td>
				 <td class="noborder borderTop" style="text-align:center;width:260px">Taxpayer</td>
				 <td class="noborder borderTop borderRight" style="text-align:center;">Spouse</td>
			   </tr>
           </table>
		   <table style="width:100%;">		
			   <tr>
				 <td class="pleft allTleft greybgcolor" >First name & Middle Initial</td>
				 <td><input tabindex="4" type="text" id="a4" data-src="taxpayer_firstname" class="tabletd" value="'.$detail['taxpayer_firstname'].'"></td>
				 <td><input tabindex="15" type="text" id="a5" data-src="spouse_firstname" class="tabletd" value="'.$detail['spouse_firstname'].'"></td>
			   </tr>
			   <tr>
				 <td class="pleft greybgcolor" >Last name</td>
				 <td><input tabindex="5" type="text" id="a6" data-src="taxpayer_lastname" class="tabletd" value="'.$detail['taxpayer_lastname'].'"></td>
				 <td><input tabindex="16" type="text" id="a7" data-src="spouse_lastname" class="tabletd" value="'.$detail['spouse_lastname'].'"></td>
			   </tr>
			   <tr>
				 <td class="pleft greybgcolor">Social Security number</td>
				 <td><input tabindex="6" type="text" id="a8" data-src="taxpayer_ssn" class="tabletd" value="'.$detail['taxpayer_ssn'].'"></td>
				 <td><input tabindex="17" type="text" id="a9" data-src="spouse_ssn" class="tabletd" value="'.$detail['spouse_ssn'].'"></td>
			   </tr>
			   <tr>
				 <td class="pleft greybgcolor">Date of birth</td>
				 <td><input tabindex="7" type="text" id="a10" data-src="taxpayer_birth" class="tabletd" value="'.$detail['taxpayer_birth'].'"></td>
				 <td><input tabindex="18" type="text" id="a11" data-src="spouse_birth" class="tabletd" value="'.$detail['spouse_birth'].'"></td>
			   </tr>
			   <tr>
				 <td class="pleft greybgcolor">Occupation</td>
				 <td><input tabindex="8" type="text" id="a12" data-src="taxpayer_occupation" class="tabletd" value="'.$detail['taxpayer_occupation'].'"></td>
				 <td><input tabindex="19" type="text" id="a13" data-src="spouse_occupation" class="tabletd" value="'.$detail['spouse_occupation'].'"></td>
			   </tr>
			   <tr>
				 <td class="pleft greybgcolor">Drivers License Number</td>
				 <td><input tabindex="9" type="text" id="a14" data-src="taxpayer_driverno" class="tabletd w170" value="'.$detail['taxpayer_driverno'].'"> 
				      <div style="display:inline-block;width:40px;line-height:28px;padding:0 5px;" class="borderLeft borderRight greybgcolor">State</div>
					  <input tabindex="10" type="text" id="a15" data-src="taxpayer_driver_state" class="tabletd w100" value="'.$detail['taxpayer_driver_state'].'">  
				 </td>
				 <td>
				      <input tabindex="20" type="text" id="a16" data-src="spouse_driverno" class="tabletd w170" value="'.$detail['spouse_driverno'].'"> 
				      <div style="display:inline-block;width:40px;line-height:28px;padding:0 5px;" class="borderLeft borderRight greybgcolor">State</div>
					  <input tabindex="21" type="text" id="a17" data-src="spouse_driver_state" class="tabletd w100" value="'.$detail['spouse_driver_state'].'">  
				 </td>
			   </tr>
			   <tr>
				 <td class="pleft greybgcolor">Drivers License Exp Date</td>
				 <td><input tabindex="11" type="text" id="a18" data-src="taxpayer_license_expdate" class="tabletd w120" value="'.$detail['taxpayer_license_expdate'].'"> 
				      <div style="display:inline-block;width:60px;line-height:28px;padding:0 5px;" class="borderLeft borderRight greybgcolor">Iss. Date</div>
					  <input tabindex="12" type="text" id="a19" data-src="taxpayer_license_issdate" class="tabletd w100" value="'.$detail['taxpayer_license_issdate'].'">  
				 </td>
				 <td>
				      <div style="display:inline-block;width:50px;line-height:28px;padding:0 5px;" class="borderRight greybgcolor">Exp</div>
				      <input tabindex="22" type="text" id="a20" data-src="spouse_license_expdate" class="tabletd w120" value="'.$detail['spouse_license_expdate'].'"> 
				      <div style="display:inline-block;width:60px;line-height:28px;padding:0 5px;" class="borderLeft borderRight greybgcolor">Iss. Date</div>
					  <input tabindex="23" type="text" id="a21" data-src="spouse_license_issdate" class="tabletd w100" value="'.$detail['spouse_license_issdate'].'">  
				 </td>
			   </tr>
			   <tr>
				 <td class="pleft greybgcolor">Home phone</td>
				 <td><input tabindex="13" type="text" id="a22" data-src="taxpayer_homephone" class="tabletd w120" value="'.$detail['taxpayer_homephone'].'"> 
				      <div style="display:inline-block;width:60px;line-height:28px;padding:0 5px;" class="borderLeft borderRight greybgcolor">Cell</div>
					  <input tabindex="14" type="text" id="a23" data-src="taxpayer_cell" class="tabletd w100" value="'.$detail['taxpayer_cell'].'">  
				 </td>
				 <td>
				      <div style="display:inline-block;width:50px;line-height:28px;padding:0 5px;" class="borderRight greybgcolor">Home</div>
				      <input tabindex="24" type="text" id="a24" data-src="spouse_homephone" class="tabletd w120" value="'.$detail['spouse_homephone'].'"> 
				      <div style="display:inline-block;width:60px;line-height:28px;padding:0 5px;" class="borderLeft borderRight greybgcolor">Cell</div>
					  <input tabindex="25" type="text" id="a25" data-src="spouse_cell" class="tabletd w100" value="'.$detail['spouse_cell'].'">  
				 </td>
			   </tr>
			   <tr>
				 <td class="pleft greybgcolor">Address</td>
				 <td colspan="2"><input tabindex="26" type="text" id="a26" data-src="taxpayer_address" class="tabletd" value="'.$detail['taxpayer_address'].'" style="width:494px;"> 
				      <div style="display:inline-block;width:60px;line-height:28px;padding:0 5px;" class="borderLeft borderRight greybgcolor">Apt/Suite</div>
					  <input tabindex="27" type="text" id="a27" data-src="apt" class="tabletd w100" value="'.$detail['apt'].'">  
				 </td>
			   </tr>
			   <tr>
				 <td class="pleft greybgcolor">City</td>
				 <td colspan="2"><input tabindex="28" type="text" id="a28" data-src="taxpayer_address" class="tabletd" value="'.$detail['taxpayer_address'].'" style="width:330px;"> 
				      <div style="display:inline-block;width:60px;line-height:28px;padding:0 5px;" class="borderLeft borderRight greybgcolor">State</div>
					  <input tabindex="29" type="text" id="a29" data-src="state" class="tabletd w70" value="'.$detail['state'].'">  
				      <div style="display:inline-block;width:60px;line-height:28px;padding:0 5px;" class="borderLeft borderRight greybgcolor">Zip</div>
					  <input tabindex="30" type="text" id="a30" data-src="zip" class="tabletd w100" value="'.$detail['zip'].'">  
				 </td>
			   </tr>
			</table>';
			switch($detail['taxpayer_legally_blind']){
				case 1:
				  $taxpayer_legally_blind1="checked";
				  break;
				case 2:
				  $taxpayer_legally_blind2="checked";
				  break;  
			}
			switch($detail['spouse_legally_blind']){
				case 1:
				  $spouse_legally_blind1="checked";
				  break;
				case 2:
				  $spouse_legally_blind2="checked";
				  break;  
			}
			switch($detail['taxpayer_disabled']){
				case 1:
				  $taxpayer_disabled1="checked";
				  break;
				case 2:
				  $taxpayer_disabled2="checked";
				  break;  
			}
			switch($detail['spouse_disabled']){
				case 1:
				  $spouse_disabled1="checked";
				  break;
				case 2:
				  $spouse_disabled2="checked";
				  break;  
			}
			switch($detail['filing_status']){
				case 1:
				  $filing_status1="checked";
				  break;
				case 2:
				  $filing_status2="checked";
				  break; 
				case 3:
				  $filing_status3="checked";
				  break; 
				case 4:
				  $filing_status4="checked";
				  break;
				case 5:
				  $filing_status5="checked";
				  break;		
			}
			$html.='<table style="width:100%;">		
			   <tr>
				 <td class="pleft allTleft noborder">Taxpayer Legally Blind</td>
				 <td class="noborder">
				   <input tabindex="31" type="checkbox" name="taxpayer_legally_blind" value="1" class="fieldcheckbox" data-src="taxpayer_legally_blind" '.$taxpayer_legally_blind1.'>Yes 
				   <input tabindex="31" type="checkbox" name="taxpayer_legally_blind" value="2" class="fieldcheckbox " data-src="taxpayer_legally_blind" '.$taxpayer_legally_blind2.'>No</td>
				 <td class="pleft allTleft noborder">Spouse Legally Blind</td>
				 <td class="noborder">
				   <input tabindex="32" type="checkbox" name="spouse_legally_blind" value="1" class="fieldcheckbox" data-src="spouse_legally_blind" '.$spouse_legally_blind1.'>Yes 
				   <input tabindex="32" type="checkbox" name="spouse_legally_blind" value="2" class="fieldcheckbox" data-src="spouse_legally_blind"  '.$spouse_legally_blind2.'>No</td>
			   </tr>
			   <tr>
				 <td class="pleft allTleft noborder">Taxpayer Disabled</td>
				 <td class="noborder">
				   <input tabindex="33" type="checkbox" name="taxpayer_disabled" value="1" class="fieldcheckbox" data-src="taxpayer_disabled" '.$taxpayer_disabled1.'>Yes 
				   <input tabindex="33" type="checkbox" name="taxpayer_disabled" value="2" class="fieldcheckbox " data-src="taxpayer_disabled" '.$taxpayer_disabled2.'>No</td>
				 <td class="pleft allTleft noborder">Spouse Disabled</td>
				 <td class="noborder">
				   <input tabindex="34" type="checkbox" name="spouse_disabled" value="1" class="fieldcheckbox" data-src="spouse_disabled" '.$spouse_disabled1.'>Yes 
				   <input tabindex="34" type="checkbox" name="spouse_disabled" value="2" class="fieldcheckbox" data-src="spouse_disabled" '.$spouse_disabled2.'>No</td>
			   </tr>
			</table>  
			<table style="width:100%;">		
			   <tr>
				 <td class="pleft allTleft noborder"><b>Filing status:</b><br>(Circle One)</td>
				 <td class="noborder">
				   <input tabindex="35" type="checkbox" name="filing_status" value="1" class="fieldcheckbox" data-src="filing_status" '.$filing_status1.'>Single 
				   <input tabindex="35" type="checkbox" name="filing_status" value="2" class="fieldcheckbox" data-src="filing_status" '.$filing_status2.'>Head of Household
				   <input tabindex="35" type="checkbox" name="filing_status" value="2" class="fieldcheckbox" data-src="filing_status" '.$filing_status3.'>Married filing joint
				   <input tabindex="35" type="checkbox" name="filing_status" value="2" class="fieldcheckbox" data-src="filing_status" '.$filing_status4.'>Married filing separate
				   <input tabindex="35" type="checkbox" name="filing_status" value="2" class="fieldcheckbox" data-src="filing_status" '.$filing_status5.'>Widower
				 </td>
				 <td class="pleft allTleft noborder w130">Year of Spouse death?</td>
				 <td class="noborder borderBottom w120">
				   <input tabindex="36" type="text" id="a31" data-src="year_of_spouse_death" class="tabletd w120" value="'.$detail['year_of_spouse_death'].'">  </td>
			   </tr>
			</table>	
			<table style="width:100%;margin-top:15px;">	
               <tr>
				 <td class="pleft" style="font-size:15px;font-weight:bold;" colspan="8">Dependents (Children & Others)</td>
			   </tr>			
			   <tr>
				 <td class="pleft greybgcolor tdcenter">Name</td>
				 <td class="pleft greybgcolor tdcenter">Relationship</td>
				 <td class="pleft greybgcolor tdcenter">Date <br>of <br>Birth</td>
				 <td class="pleft greybgcolor tdcenter">Social<br> Security <br>Number</td>
				 <td class="pleft greybgcolor tdcenter">Months <br>Lived With<br> You</td>
				 <td class="pleft greybgcolor tdcenter">Disabled</td>
				 <td class="pleft greybgcolor tdcenter">Full Time <br>Student</td>
				 <td class="pleft greybgcolor tdcenter">Dependent\'s <br>Gross<br> Income</td>
			   </tr>
			   <tr>
				 <td><input tabindex="37" type="text" id="a32" data-src="dependent_name_1" class="tabletd" value="'.$detail['dependent_name_1'].'" style="width:200px;"></td>
				 <td><input tabindex="38" type="text" id="a33" data-src="dependent_relationship_1" class="tabletd w70" value="'.$detail['dependent_relationship_1'].'"></td>
				 <td><input tabindex="39" type="text" id="a34" data-src="dependent_date_of_birth_1" class="tabletd w70" value="'.$detail['dependent_date_of_birth_1'].'"></td>
				 <td><input tabindex="40" type="text" id="a35" data-src="dependent_ssn_1" class="tabletd w70" value="'.$detail['dependent_ssn_1'].'"></td>
				 <td><input tabindex="41" type="text" id="a36" data-src="dependent_month_live_with_1" class="tabletd w70" value="'.$detail['dependent_month_live_with_1'].'"></td>
				 <td><input tabindex="42" type="text" id="a37" data-src="dependent_disabled_1" class="tabletd w70" value="'.$detail['dependent_disabled_1'].'"></td>
				 <td><input tabindex="43" type="text" id="a38" data-src="dependent_fulltime_student_1" class="tabletd w70" value="'.$detail['dependent_fulltime_student_1'].'"></td>
				 <td><input tabindex="44" type="text" id="a39" data-src="dependent_gross_income_1" class="tabletd w70" value="'.$detail['dependent_gross_income_1'].'"></td>
			   </tr>
			   <tr>
				 <td><input tabindex="45" type="text" id="a40" data-src="dependent_name_2" class="tabletd " value="'.$detail['dependent_name_2'].'" style="width:200px;"></td>
				 <td><input tabindex="46" type="text" id="a41" data-src="dependent_relationship_2" class="tabletd w70" value="'.$detail['dependent_relationship_2'].'"></td>
				 <td><input tabindex="47" type="text" id="a42" data-src="dependent_date_of_birth_2" class="tabletd w70" value="'.$detail['dependent_date_of_birth_2'].'"></td>
				 <td><input tabindex="48" type="text" id="a43" data-src="dependent_ssn_2" class="tabletd w70" value="'.$detail['dependent_ssn_2'].'"></td>
				 <td><input tabindex="49" type="text" id="a44" data-src="dependent_month_live_with_2" class="tabletd w70" value="'.$detail['dependent_month_live_with_2'].'"></td>
				 <td><input tabindex="50" type="text" id="a45" data-src="dependent_disabled_2" class="tabletd w70" value="'.$detail['dependent_disabled_2'].'"></td>
				 <td><input tabindex="51" type="text" id="a46" data-src="dependent_fulltime_student_2" class="tabletd w70" value="'.$detail['dependent_fulltime_student_2'].'"></td>
				 <td><input tabindex="52" type="text" id="a47" data-src="dependent_gross_income_2" class="tabletd w70" value="'.$detail['dependent_gross_income_2'].'"></td>
			   </tr>
			   <tr>
				 <td><input tabindex="53" type="text" id="a48" data-src="dependent_name_3" class="tabletd" value="'.$detail['dependent_name_3'].'" style="width:200px;"></td>
				 <td><input tabindex="54" type="text" id="a49" data-src="dependent_relationship_3" class="tabletd w70" value="'.$detail['dependent_relationship_3'].'"></td>
				 <td><input tabindex="55" type="text" id="a50" data-src="dependent_date_of_birth_3" class="tabletd w70" value="'.$detail['dependent_date_of_birth_3'].'"></td>
				 <td><input tabindex="56" type="text" id="a51" data-src="dependent_ssn_3" class="tabletd w70" value="'.$detail['dependent_ssn_3'].'"></td>
				 <td><input tabindex="57" type="text" id="a52" data-src="dependent_month_live_with_3" class="tabletd w70" value="'.$detail['dependent_month_live_with_3'].'"></td>
				 <td><input tabindex="58" type="text" id="a53" data-src="dependent_disabled_3" class="tabletd w70" value="'.$detail['dependent_disabled_3'].'"></td>
				 <td><input tabindex="59" type="text" id="a54" data-src="dependent_fulltime_student_3" class="tabletd w70" value="'.$detail['dependent_fulltime_student_3'].'"></td>
				 <td><input tabindex="60" type="text" id="a55" data-src="dependent_gross_income_3" class="tabletd w70" value="'.$detail['dependent_gross_income_3'].'"></td>
			   </tr>
			   <tr>
				 <td><input tabindex="61" type="text" id="a56" data-src="dependent_name_4" class="tabletd" value="'.$detail['dependent_name_4'].'" style="width:200px;"></td>
				 <td><input tabindex="62" type="text" id="a57" data-src="dependent_relationship_4" class="tabletd w70" value="'.$detail['dependent_relationship_4'].'"></td>
				 <td><input tabindex="63" type="text" id="a58" data-src="dependent_date_of_birth_4" class="tabletd w70" value="'.$detail['dependent_date_of_birth_4'].'"></td>
				 <td><input tabindex="64" type="text" id="a59" data-src="dependent_ssn_4" class="tabletd w70" value="'.$detail['dependent_ssn_4'].'"></td>
				 <td><input tabindex="65" type="text" id="a60" data-src="dependent_month_live_with_4" class="tabletd w70" value="'.$detail['dependent_month_live_with_4'].'"></td>
				 <td><input tabindex="66" type="text" id="a61" data-src="dependent_disabled_4" class="tabletd w70" value="'.$detail['dependent_disabled_4'].'"></td>
				 <td><input tabindex="67" type="text" id="a62" data-src="dependent_fulltime_student_4" class="tabletd w70" value="'.$detail['dependent_fulltime_student_4'].'"></td>
				 <td><input tabindex="68" type="text" id="a63" data-src="dependent_gross_income_4" class="tabletd w70" value="'.$detail['dependent_gross_income_4'].'"></td>
			   </tr>
			   <tr>
				 <td><input tabindex="69" type="text" id="a64" data-src="dependent_name_5" class="tabletd" value="'.$detail['dependent_name_5'].'" style="width:200px;"></td>
				 <td><input tabindex="70" type="text" id="a65" data-src="dependent_relationship_5" class="tabletd w70" value="'.$detail['dependent_relationship_5'].'"></td>
				 <td><input tabindex="71" type="text" id="a66" data-src="dependent_date_of_birth_5" class="tabletd w70" value="'.$detail['dependent_date_of_birth_5'].'"></td>
				 <td><input tabindex="72" type="text" id="a67" data-src="dependent_ssn_5" class="tabletd w70" value="'.$detail['dependent_ssn_5'].'"></td>
				 <td><input tabindex="73" type="text" id="a68" data-src="dependent_month_live_with_5" class="tabletd w70" value="'.$detail['dependent_month_live_with_5'].'"></td>
				 <td><input tabindex="74" type="text" id="a69" data-src="dependent_disabled_5" class="tabletd w70" value="'.$detail['dependent_disabled_5'].'"></td>
				 <td><input tabindex="75" type="text" id="a70" data-src="dependent_fulltime_student_5" class="tabletd w70" value="'.$detail['dependent_fulltime_student_5'].'"></td>
				 <td><input tabindex="76" type="text" id="a71" data-src="dependent_gross_income_5" class="tabletd w70" value="'.$detail['dependent_gross_income_5'].'"></td>
			   </tr>			   
			</table>
			<div class="tipsbold" style="margin-top:15px;">Please answer the following questions to determine maximum deductions:</div>';
			switch($detail['marital_status_change']){
				case 1:
				  $marital_status_change1="checked";
				  break;
				case 2:
				  $marital_status_change2="checked";
				  break;  
			}
			switch($detail['receive_a_distribution']){
				case 1:
				  $receive_a_distribution1="checked";
				  break;
				case 2:
				  $receive_a_distribution2="checked";
				  break;  
			}
			switch($detail['address_change']){
				case 1:
				  $address_change1="checked";
				  break;
				case 2:
				  $address_change2="checked";
				  break;  
			}
			switch($detail['give_a_gift']){
				case 1:
				  $give_a_gift1="checked";
				  break;
				case 2:
				  $give_a_gift2="checked";
				  break;  
			}
			switch($detail['changes_dependent']){
				case 1:
				  $changes_dependent1="checked";
				  break;
				case 2:
				  $changes_dependent2="checked";
				  break;  
			}
			switch($detail['go_through_bankruptcy']){
				case 1:
				  $go_through_bankruptcy1="checked";
				  break;
				case 2:
				  $go_through_bankruptcy2="checked";
				  break;  
			}
			$html.='<table style="width:100%;">		
			   <tr>
				 <td class="pleft allTleft noborder">1. Did your marital status change during the year?</td>
				 <td class="noborder">
				   <input tabindex="77" type="checkbox" name="marital_status_change" value="1" class="fieldcheckbox" data-src="marital_status_change" '.$marital_status_change1.'>Yes 
				   <input tabindex="77" type="checkbox" name="marital_status_change" value="2" class="fieldcheckbox " data-src="marital_status_change" '.$marital_status_change2.'>No</td>
				 <td class="pleft allTleft noborder lineheight">13. Did you receive a distribution from a retirement <br> plan (401(k), IRA, 1099-R etc)?</td>
				 <td class="noborder">
				   <input tabindex="94" type="checkbox" name="receive_a_distribution" value="1" class="fieldcheckbox" data-src="receive_a_distribution" '.$receive_a_distribution1.'>Yes 
				   <input tabindex="94" type="checkbox" name="receive_a_distribution" value="2" class="fieldcheckbox" data-src="receive_a_distribution" '.$receive_a_distribution2.'>No</td>
			   </tr>
			   <tr>
				 <td class="pleft allTleft noborder">2. Did your address change during the year?</td>
				 <td class="noborder">
				   <input tabindex="78" type="checkbox" name="address_change" value="1" class="fieldcheckbox" data-src="address_change" '.$address_change1.'>Yes 
				   <input tabindex="78" type="checkbox" name="address_change" value="2" class="fieldcheckbox " data-src="address_change" '.$address_change2.'>No</td>
				 <td class="pleft allTleft noborder lineheight">14. Did you give a gift of more than <br>$14,000 to one or more people?</td>
				 <td class="noborder">
				   <input tabindex="95" type="checkbox" name="give_a_gift" value="1" class="fieldcheckbox" data-src="give_a_gift" '.$give_a_gift1.'>Yes 
				   <input tabindex="95" type="checkbox" name="give_a_gift" value="2" class="fieldcheckbox" data-src="give_a_gift" '.$give_a_gift2.'>No</td>
			   </tr>
			   <tr>
				 <td class="pleft allTleft noborder">3. Were there any changes in dependents?</td>
				 <td class="noborder">
				   <input tabindex="79" type="checkbox" name="changes_dependent" value="1" class="fieldcheckbox" data-src="changes_dependent" '.$changes_dependent1.'>Yes 
				   <input tabindex="79" type="checkbox" name="changes_dependent" value="2" class="fieldcheckbox " data-src="changes_dependent"  '.$changes_dependent2.'>No</td>
				 <td class="pleft allTleft noborder lineheight">15. Did you go through bankruptcy, foreclosure, <br>or repossession proceedings?</td>
				 <td class="noborder">
				   <input tabindex="96" type="checkbox" name="go_through_bankruptcy" value="1" class="fieldcheckbox" data-src="go_through_bankruptcy" '.$go_through_bankruptcy1.'>Yes 
				   <input tabindex="96" type="checkbox" name="go_through_bankruptcy" value="2" class="fieldcheckbox" data-src="go_through_bankruptcy" '.$go_through_bankruptcy2.'>No</td>
			   </tr>';
			   switch($detail['receive_unreported_tipincome']){
				case 1:
				  $receive_unreported_tipincome1="checked";
				  break;
				case 2:
				  $receive_unreported_tipincome2="checked";
				  break;  
			   }
			   switch($detail['incur_a_loss']){
				case 1:
				  $incur_a_loss1="checked";
				  break;
				case 2:
				  $incur_a_loss2="checked";
				  break;  
			   }
			   switch($detail['receive_any_unemployment']){
				case 1:
				  $receive_any_unemployment1="checked";
				  break;
				case 2:
				  $receive_any_unemployment2="checked";
				  break;  
			   }
			   switch($detail['notified_by_either']){
				case 1:
				  $notified_by_either1="checked";
				  break;
				case 2:
				  $notified_by_either2="checked";
				  break;  
			   }
			   switch($detail['sell_any_stock']){
				case 1:
				  $sell_any_stock1="checked";
				  break;
				case 2:
				  $sell_any_stock2="checked";
				  break;  
			   }
			   switch($detail['pay_any_alimony']){
				case 1:
				  $pay_any_alimony1="checked";
				  break;
				case 2:
				  $pay_any_alimony2="checked";
				  break;  
			   }
			   switch($detail['equlity_loan']){
				case 1:
				  $equlity_loan1="checked";
				  break;
				case 2:
				  $equlity_loan2="checked";
				  break;  
			   }
			   switch($detail['make_any_energy_efficient']){
				case 1:
				  $make_any_energy_efficient1="checked";
				  break;
				case 2:
				  $make_any_energy_efficient2="checked";
				  break;  
			   }
			   $html.='
			   <tr>
				 <td class="pleft allTleft noborder">4. Did you receive unreported tip income of <br>$20 or more in any month?</td>
				 <td class="noborder">
				   <input tabindex="80" type="checkbox" name="receive_unreported_tipincome" value="1" class="fieldcheckbox" data-src="receive_unreported_tipincome" '.$receive_unreported_tipincome1.'>Yes 
				   <input tabindex="80" type="checkbox" name="receive_unreported_tipincome" value="2" class="fieldcheckbox " data-src="receive_unreported_tipincome" '.$receive_unreported_tipincome2.'>No</td>
				 <td class="pleft allTleft noborder lineheight">16. Did you incur a loss because of damaged or stolen property?</td>
				 <td class="noborder">
				   <input tabindex="97" type="checkbox" name="incur_a_loss" value="1" class="fieldcheckbox" data-src="incur_a_loss" '.$incur_a_loss1.'>Yes 
				   <input tabindex="97" type="checkbox" name="incur_a_loss" value="2" class="fieldcheckbox" data-src="incur_a_loss" '.$incur_a_loss2.'>No</td>
			   </tr>
			   <tr>
				 <td class="pleft allTleft noborder lineheight">5. Did you receive any unemployment or <br>disability income?</td>
				 <td class="noborder">
				   <input tabindex="81" type="checkbox" name="receive_any_unemployment" value="1" class="fieldcheckbox" data-src="receive_any_unemployment" '.$receive_any_unemployment1.'>Yes 
				   <input tabindex="81" type="checkbox" name="receive_any_unemployment" value="2" class="fieldcheckbox " data-src="receive_any_unemployment" '.$receive_any_unemployment2.'>No</td>
				 <td class="pleft allTleft noborder lineheight">17. Were you notified or audited by either the IRS or State taxing agency?</td>
				 <td class="noborder">
				   <input tabindex="98" type="checkbox" name="notified_by_either" value="1" class="fieldcheckbox" data-src="notified_by_either" '.$notified_by_either1.'>Yes 
				   <input tabindex="98" type="checkbox" name="notified_by_either" value="2" class="fieldcheckbox" data-src="notified_by_either"  '.$notified_by_either2.'>No</td>
			   </tr>
			   <tr>
				 <td class="pleft allTleft noborder lineheight">6. Did you sell any stocks, bonds, crypto <br>or other investment property?</td>
				 <td class="noborder">
				   <input tabindex="82" type="checkbox" name="sell_any_stock" value="1" class="fieldcheckbox" data-src="sell_any_stock" '.$sell_any_stock1.'>Yes 
				   <input tabindex="82" type="checkbox" name="sell_any_stock" value="2" class="fieldcheckbox" data-src="sell_any_stock" '.$sell_any_stock2.'>No</td>
				 <td class="pleft allTleft noborder lineheight">18. Did id you pay any alimony? <br>Enter recipient\'s
				    SSN<input tabindex="100" type="text" id="a72" data-src="recipient_ssn" class="tabletd w70" value="'.$detail['recipient_ssn'].'">
					Paid $<input tabindex="101" type="text" id="a73" data-src="recipients_paied" class="tabletd w70" value="'.$detail['recipients_paied'].'">
				   </td>
				 <td class="noborder">
				   <input tabindex="99" type="checkbox" name="pay_any_alimony" value="1" class="fieldcheckbox" data-src="pay_any_alimony" '.$pay_any_alimony1.'>Yes 
				   <input tabindex="99" type="checkbox" name="pay_any_alimony" value="2" class="fieldcheckbox" data-src="pay_any_alimony" '.$pay_any_alimony2.'>No</td>
			   </tr>
			   <tr>
				 <td class="pleft allTleft noborder lineheight">7. Did you purchase, sell, or refinance your principal<br> home or second home, or take out <br>a home equity loan?</td>
				 <td class="noborder">
				   <input tabindex="83" type="checkbox" name="equlity_loan" value="1" class="fieldcheckbox" data-src="equlity_loan" '.$equlity_loan1.'>Yes 
				   <input tabindex="83" type="checkbox" name="equlity_loan" value="2" class="fieldcheckbox " data-src="equlity_loan" '.$equlity_loan2.'>No</td>
				 <td class="pleft allTleft noborder lineheight">19. Did you make energy efficient improvements to your home? <br>If yes, please attach details.</td>
				 <td class="noborder">
				   <input tabindex="102" type="checkbox" name="make_any_energy_efficient" value="1" class="fieldcheckbox" data-src="make_any_energy_efficient" '.$make_any_energy_efficient1.'>Yes 
				   <input tabindex="102" type="checkbox" name="make_any_energy_efficient" value="2" class="fieldcheckbox" data-src="make_any_energy_efficient"  '.$make_any_energy_efficient2.'>No</td>
			   </tr>';
			   switch($detail['convert_part_traditional']){
				case 1:
				  $convert_part_traditional1="checked";
				  break;
				case 2:
				  $convert_part_traditional2="checked";
				  break;  
			   }
			   switch($detail['were_a_citizen']){
				case 1:
				  $were_a_citizen1="checked";
				  break;
				case 2:
				  $were_a_citizen2="checked";
				  break;  
			   }
			   switch($detail['claimed_as_dependent']){
				case 1:
				  $claimed_as_dependent1="checked";
				  break;
				case 2:
				  $claimed_as_dependent2="checked";
				  break;  
			   }
			   switch($detail['have_children_under19']){
				case 1:
				  $have_children_under191="checked";
				  break;
				case 2:
				  $have_children_under192="checked";
				  break;  
			   }
			   switch($detail['cancel_any_debt']){
				case 1:
				  $cancel_any_debt1="checked";
				  break;
				case 2:
				  $cancel_any_debt2="checked";
				  break;  
			   }
			   switch($detail['buy_any_internet_merchandise']){
				case 1:
				  $buy_any_internet_merchandise1="checked";
				  break;
				case 2:
				  $buy_any_internet_merchandise2="checked";
				  break;  
			   }
			   switch($detail['buy_any_internet_merchandise']){
				case 1:
				  $buy_any_internet_merchandise1="checked";
				  break;
				case 2:
				  $buy_any_internet_merchandise2="checked";
				  break;  
			   }
			   $html.='
			   <tr>
				 <td class="pleft allTleft noborder lineheight">8. Did you convert part or all of your traditional/<br>SEP/SIMPLE IRA to a ROTH IRA?</td>
				 <td class="noborder">
				   <input tabindex="84" type="checkbox" name="convert_part_traditional" value="1" class="fieldcheckbox" data-src="convert_part_traditional" '.$convert_part_traditional1.'>Yes 
				   <input tabindex="84" type="checkbox" name="convert_part_traditional" value="2" class="fieldcheckbox " data-src="convert_part_traditional" '.$convert_part_traditional2.'>No</td>
				 <td class="pleft allTleft noborder lineheight">20. Were you a citizen of, have income from, or live <br>in a foreign country?</td>
				 <td class="noborder">
				   <input tabindex="103" type="checkbox" name="were_a_citizen" value="1" class="fieldcheckbox" data-src="were_a_citizen" '.$were_a_citizen1.'>Yes 
				   <input tabindex="103" type="checkbox" name="were_a_citizen" value="2" class="fieldcheckbox" data-src="were_a_citizen"  '.$were_a_citizen2.'>No</td>
			   </tr>
			   <tr>
				 <td class="pleft allTleft noborder lineheight">9. Could you be claimed as a dependent on <br>another person\'s tax return?</td>
				 <td class="noborder">
				   <input tabindex="85" type="checkbox" name="claimed_as_dependent" value="1" class="fieldcheckbox" data-src="claimed_as_dependent" '.$claimed_as_dependent1.'>Yes 
				   <input tabindex="85" type="checkbox" name="claimed_as_dependent" value="2" class="fieldcheckbox " data-src="claimed_as_dependent"  '.$claimed_as_dependent2.'>No</td>
				 <td class="pleft allTleft noborder lineheight">21. Do you have children who are under age 19 or <br>a full time student under age 24 with investment <br>income greater than $2,100?</td>
				 <td class="noborder">
				   <input tabindex="104" type="checkbox" name="have_children_under19" value="1" class="fieldcheckbox" data-src="have_children_under19" '.$have_children_under191.'>Yes 
				   <input tabindex="104" type="checkbox" name="have_children_under19" value="2" class="fieldcheckbox" data-src="have_children_under19" '.$have_children_under192.'>No</td>
			   </tr>
			   <tr>
				 <td class="pleft allTleft noborder lineheight">10. Did a lender cancel any of your debt?<br>(Attach forms 1099 A or C)</td>
				 <td class="noborder">
				   <input tabindex="86" type="checkbox" name="cancel_any_debt" value="1" class="fieldcheckbox" data-src="cancel_any_debt" '.$cancel_any_debt1.'>Yes 
				   <input tabindex="86" type="checkbox" name="cancel_any_debt" value="2" class="fieldcheckbox " data-src="cancel_any_debt" '.$cancel_any_debt2.'>No</td>
				 <td class="pleft allTleft noborder lineheight">22. Did you buy any internet merchandise for which <br>you did not pay sales/use tax?</td>
				 <td class="noborder">
				   <input tabindex="105" type="checkbox" value="1" class="fieldcheckbox" data-src="buy_any_internet_merchandise" '.$buy_any_internet_merchandise1.'>Yes 
				   <input tabindex="105" type="checkbox" value="2" class="fieldcheckbox" data-src="buy_any_internet_merchandise" '.$buy_any_internet_merchandise2.'>No</td>
			   </tr>';
			   switch($detail['purchase_a_hybrid']){
				case 1:
				  $purchase_a_hybrid1="checked";
				  break;
				case 2:
				  $purchase_a_hybrid2="checked";
				  break;  
			   }
			   switch($detail['have_aca']){
				case 1:
				  $have_aca1="checked";
				  break;
				case 2:
				  $have_aca2="checked";
				  break;  
			   }
			   switch($detail['health_insurance']){
				case 1:
				  $health_insurance1="checked";
				  break;
				case 2:
				  $health_insurance2="checked";
				  break;  
			   }

			   $html.='
			   <tr>
				 <td class="pleft allTleft noborder lineheight">11. Did you purchase a hybrid or electric vehicle <br>this year? Please provide year, make,model,<br>and date purchased.<br>
				    Year <input tabindex="88" type="text" id="a74" data-src="hybrid_year" class="tabletd w70 borderBottom" value="'.$detail['hybrid_year'].'">
					Make <input tabindex="89" type="text" id="a75" data-src="hybrid_make" class="tabletd w70 borderBottom" value="'.$detail['hybrid_make'].'"><br>
					Model <input tabindex="90" type="text" id="a76" data-src="hybrid_model" class="tabletd w70 borderBottom" value="'.$detail['hybrid_model'].'">
					Date <input tabindex="91" type="text" id="a77" data-src="hybrid_date_purchased" class="tabletd w70 borderBottom" value="'.$detail['hybrid_date_purchased'].'">
				 </td>
				 <td class="noborder">
				   <input tabindex="87" type="checkbox" name="purchase_a_hybrid" value="1" class="fieldcheckbox" data-src="purchase_a_hybrid" '.$purchase_a_hybrid1.'>Yes 
				   <input tabindex="87" type="checkbox" name="purchase_a_hybrid" value="2" class="fieldcheckbox " data-src="purchase_a_hybrid" '.$purchase_a_hybrid2.'>No</td>
				 <td class="pleft allTleft noborder lineheight">23. Health Insurance Did you have ACA compliant <br>health insurance during the year? (Attach Form 1095-A or 1095-B)</td>
				 <td class="noborder">
				   <input tabindex="106" type="checkbox" name="have_aca" value="1" class="fieldcheckbox" data-src="have_aca" '.$have_aca1.'>Yes 
				   <input tabindex="106" type="checkbox" name="have_aca" value="2" class="fieldcheckbox" data-src="have_aca" '.$have_aca2.'>No</td>
			   </tr>
			   <tr>
				 <td class="pleft allTleft noborder">12. Did you and your dependents have health <br>insurance coverage for the full year?</td>
				 <td class="noborder" colspan="3">
				   <input tabindex="92" type="checkbox" name="health_insurance" value="1" class="fieldcheckbox" data-src="health_insurance" '.$health_insurance1.'>Yes 
				   <input tabindex="93" type="checkbox" name="health_insurance" value="2" class="fieldcheckbox " data-src="health_insurance" '.$health_insurance2.'>No</td>
			   </tr>
			</table>  
			<table>
			   <tr>
			      <td class="noborder"id="sign">Signature:';
				  $old_sign_con='/var/www/vhosts/taxprep.republictax.com/httpdocs/public/'.$detail['signimage'];
					if (is_file($old_sign_con)&&file_exists($old_sign_con)) {
				     // if($detail['signimage']){
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
	public function topdf(){
      // $link="k0ArSMziYDVZ8IwixdW3fsAhumj5";
        
        //复制完后生成pdf,发送后删除 
		$link=$_GET['link'];
		$map['link']=$_GET['link'];
		$map['plan']='all';
       $res=DB::name('users_clients')->where($map)->field('id')->find();
       
        if($res){
			$path='upload/all/'.$link;
			//先挪动文件至temp目录下，先创建当前目录下的temp目录
			$targetDirectory ='/var/www/vhosts/taxprep.republictax.com/httpdocs/public/upload/all/'.$link."/temp/";
			
			if (!is_dir($targetDirectory)) {
				   // 尝试创建目录
			   if (!mkdir($targetDirectory, 0755, true)) {
				   return false; 
			   }
			}
			$mm['user_client_id']=$res['id'];
			$detail=DB::name('user_all')->where($mm)->find();
		
			if($detail){
				 $htmlContent = $this->gethtml($detail);

				if($htmlContent){
					// 设置文件名和路径
					$filename = 'all.html';
					$filepath = '/var/www/vhosts/taxprep.republictax.com/httpdocs/public/upload/all/'.$link.'/pdf/';
					 
					// 确保目录存在
					if (!file_exists($filepath)) {
						mkdir($filepath, 0755, true);
					}
					
					// 将HTML内容写入文件
				    file_put_contents($filepath . $filename, $htmlContent);
					$htmlFilePath = '/var/www/vhosts/taxprep.republictax.com/httpdocs/public/upload/all/'.$link.'/pdf/'.$filename;
					$pdfFilePath = '/var/www/vhosts/taxprep.republictax.com/httpdocs/public/upload/all/'.$link.'/pdf/out.pdf';
					$command = "wkhtmltopdf --enable-local-file-access --lowquality $filepath$filename $pdfFilePath";
				
					exec($command, $output, $returnVar);
					
					if ($returnVar === 0) {
						header('Content-Description: File Transfer');
						header('Content-Type: application/octet-stream');
						header('Content-Disposition: attachment; filename=Tax_Organizer_ALL.pdf');
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
            $map['plan']='all';
            //$map['status']=1;
            $res=DB::name('users_clients')->where($map)->find();
           
            if($res){
            	$save2['updatetime']=time();
                $save2['status']=1;
                DB::name('users_clients')->where('id',$res['id'])->save($save2);//更新users_clients状态 
               
				$mm['user_client_id']=$res['id'];
				$profitloss=DB::name('user_all')->where($mm)->find();
				
				if($profitloss){
					$rr=DB::name('user_all')->where($mm)->save($save);
					
					if($data['fieldvalue']!==0){
		            	if ($data['fieldvalue']=='') {
		            		DB::name('user_all')->where($mm)->save(array($data['fieldname']=>null));
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

    public function index(){
        $users=session('user');
        $islogin=0;
        if($users){
            $islogin=1;
        }
        View::assign('islogin', $islogin);
        $str=$_SERVER['REQUEST_URI'];
        $key=str_replace("/clients/all/",'',$str);
  
        if (strpos($key, "/ftag/1")) {
            $key=str_replace("/ftag/1",'',$key);
            View::assign('showtag', 1); 
        }else{
            View::assign('showtag', 0);
        }
        $map['link']=$key;
		if($key!="preview"){
        	$map['plan']="all";
        }
        $res=DB::name('users_clients')->where($map)->field('id,link,firstname,lastname,status')->find();
     
        if($res){
			$mm['user_client_id']=$res['id'];
			$profitloss=DB::name('user_all')->where($mm)->find();
			if(!$profitloss){
				$insert['user_client_id']=$res['id'];
				$insert['updatetime']=time();
				$pid=DB::name('user_all')->insertGetId($insert);
				$mm2['user_client_id']=$res['id'];
			    $profitloss=DB::name('user_all')->where($mm2)->find();
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
		$map22['plan']="all";
        $res2=DB::name('users_clients')->where($map22)->find();
        if($res2){
			$map['user_client_id']=$res2['id'];
			$res=DB::name('user_all')->where($map)->find();
		
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
								$new_path = '/all/'.$link."/sheets/".$pagename;
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
											$rr=DB::name('user_all')->where($map)->save($save);                              
											
										 //重新计算所有文件数量
										   $newdetail= DB::name('user_all')->where($map)->find();
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
