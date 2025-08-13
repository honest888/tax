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

class Rentalincome extends Base
{
    protected function initialize()
    {
        parent::initialize();
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
		$map['plan']="rentalincome";
        $res=DB::name('users_clients')->where($map)->find();
          
        if($res){
			$mm['user_client_id']=$res['id'];
			$profitloss=DB::name('user_rentalincome')->where($mm)->find();
			
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
		$map['plan']="rentalincome";
        $res=DB::name('users_clients')->where($map)->find();
          
        if($res){
			$mm['user_client_id']=$res['id'];
			$profitloss=DB::name('user_rentalincome')->where($mm)->find();
			
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
		$map['plan']="rentalincome";
		$link=$data['link'];
        $res=DB::name('users_clients')->where($map)->find();
        if($res){
			$mm['user_client_id']=$res['id'];
			$profitloss=DB::name('user_rentalincome')->where($mm)->find();
			$sign_name= uniqid() . '.png';
			$targetDirectory='/var/www/vhosts/taxprep.republictax.com/httpdocs/public/upload/rentalincome/'.$link."/signaturet/";
            $filename_sign = '/var/www/vhosts/taxprep.republictax.com/httpdocs/public/upload/rentalincome/'.$link."/signaturet/".$sign_name;//签名的路径
			if (!is_dir($targetDirectory)) {
			   if (!mkdir($targetDirectory, 0755, true)) {
				   return false; 
			   }
			}
			
            $sign_path='/upload/rentalincome/'.$link.'/signaturet/' . $sign_name;//存储路径
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
             Db::name('user_rentalincome')->where('id',$profitloss['id'])->update($new);
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
			$profitloss=DB::name('user_rentalincome')->where($mm)->find();
			$sign_name= uniqid() . '.png';
			$targetDirectory='/var/www/vhosts/taxprep.republictax.com/httpdocs/public/upload/rentalincome/'.$link."/signaturet/";
            $filename_sign = '/var/www/vhosts/taxprep.republictax.com/httpdocs/public/upload/rentalincome/'.$link."/signaturet/".$sign_name;//签名的路径
			if (!is_dir($targetDirectory)) {
			   if (!mkdir($targetDirectory, 0755, true)) {
				   return false; 
			   }
			}
			
            $sign_path='/upload/rentalincome/'.$link.'/signaturet/' . $sign_name;//存储路径
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
             Db::name('user_rentalincome')->where('id',$profitloss['id'])->update($new);
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
		   $map['plan']='rentalincome';
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
		$map['plan']='rentalincome';
        $res=DB::name('users_clients')->where($map)->field('id,caseid')->find();
       
        if($res){
			$path='upload/rentalincome/'.$link;
			//先挪动文件至temp目录下，先创建当前目录下的temp目录
			$targetDirectory ='/var/www/vhosts/taxprep.republictax.com/httpdocs/public/upload/rentalincome/'.$link."/temp/";
			
			if (!is_dir($targetDirectory)) {
				   // 尝试创建目录
			   if (!mkdir($targetDirectory, 0755, true)) {
				   return false; 
			   }
			}
			$mm['user_client_id']=$res['id'];
			$detail=DB::name('user_rentalincome')->where($mm)->find();
		
			if($detail){
				 $htmlContent = $this->gethtml($detail);

				if($htmlContent){
					// 设置文件名和路径
					$filename = 'rentalincome.html';
					$filepath = '/var/www/vhosts/taxprep.republictax.com/httpdocs/public/upload/rentalincome/'.$link.'/pdf/';
					 
					// 确保目录存在
					if (!file_exists($filepath)) {
						mkdir($filepath, 0755, true);
					}
					 
					// 将HTML内容写入文件
				    file_put_contents($filepath . $filename, $htmlContent);
					$htmlFilePath = '/var/www/vhosts/taxprep.republictax.com/httpdocs/public/upload/rentalincome/'.$link.'/pdf/'.$filename;
					$pdfFilePath = '/var/www/vhosts/taxprep.republictax.com/httpdocs/public/upload/rentalincome/'.$link.'/pdf/out.pdf';
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
				         $fname="Tax_Organizer_Schedule_E_Blank_Rental_Income".$fname.".pdf";  
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
				$profitloss=DB::name('user_rentalincome')->where($mm)->find();
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
			</head>
			<style>
			 body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
			font-size:14px;
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
			padding:0px;
        }
        input[type="text"] {
		    padding-left:10px;
            width: 100%;
            box-sizing: border-box;
			line-height:25px;
			height:25px;
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
	 
		input:focus {
			outline: none;
			border: 1px solid #000;
		}
		.tabletd{
		    border: none;
			background:#f1f4ff;
			font-size:12px;
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
		.tdcenter{
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
		  background:#f3f3f3;
		}	
		
		.pcshow{
		  display:flex;
		}
	
		.action-button{
		    min-width: 50px;
		    max-width:90px;
		}
		
		.page{
			 width:1000px;
			 margin:0 auto;
			 padding:20px 20px; 
			 background:#fff;
		}
		
		input:focus {
			background-color: #8bda5b;
		}
		
		.w100{
		  width:100px!important;
		}
		.w120{
			width:120px!important;
		}
		
		.m20{
		   margin-top:20px;
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
		
		.w220{
			width:220px!important;
		}
		.w200{
			width:200px!important;
		}
		.title{
		  font-weight:bold;
		  text-align:center;
		  font-size:18px;
		}
		.pleft{
			padding-left:10px;
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
    width:70px!important;
  }
  .w50{
    width:50px!important;
  }
  .lineheight{
    line-height:18px;
  }

  .inputborder{
    border:1px solid #000;
  }
	</style>
	<body>
	    <div class="page">
			<table style="width:100%;">
			    <tr>
				 <td class="w50" style="border:3px solid #000;height: 50px;"> </td>
				 <td class="title" style="border:3px solid #000;">1040</td>
				 <td class="title" style="border:3px solid #000;">US</td>
				 <td class="title" style="border:3px solid #000;">Rental & Royalty Income (Schedule E)</td>
				 <td style="border:3px solid #000;">&nbsp;Year <input tabindex="3" type="text" id="a3" data-src="tax_year" class="tabletd w70" value="'.$detail['tax_year'].'" style="border:1px solid #000;"></td>
				 <td  class="title" style="border:3px solid #000;">18</td>
			    </tr> 
			</table>
		   <div style="border:3px solid #000;padding:20px;border-top:none;">
		    <div style="font-weight:bold;font-size:18px;margin-top:20px;">GENERAL INFORMATION</div>
			<table style="width:100%;">
			    <tr>
				 <td class="noborder">Description of property </td>
				 <td class="noborder"><input tabindex="4" type="text" id="a4" data-src="description_of_property" class="tabletd " value="'.$detail['description_of_property'].'" style="border:1px solid #000;"></td>
				 <td class="noborder" rowspan="7" style="width:190px;">
				  <div style="border:1px solid #000;width:100%;height:100%;width:188px;">
				    <div style="margin-left:40px;padding-top:30px;"><b>Type of Property</b></div>
					<div style="margin-left:10px;padding-right:20px;">
					1 = Single Family Residence<br>
					2 = Multi-Family Residence<br>
					3 = Vacation /Short-Term Rental<br>
					4 = Commercial<br>
					5 = Land<br>
					6 = Royalties<br>
					7 = Self-Rental
					</div>
				  </div>
				 </td>
			    </tr> 
				<tr>
				 <td class="noborder">Street address </td>
				 <td class="noborder"><input tabindex="5" type="text" id="a5" data-src="street_address" class="tabletd inputborder" value="'.$detail['street_address'].'"></td>				
			    </tr> 
				<tr>
				 <td class="noborder">City</td>
				 <td class="noborder"><input tabindex="6" type="text" id="a6" data-src="city" class="tabletd inputborder" value="'.$detail['city'].'" ></td>
			    </tr> 
				<tr>
				 <td class="noborder">State</td>
				 <td class="noborder"><input tabindex="7" type="text" id="a7" data-src="state" class="tabletd inputborder" value="'.$detail['state'].'"></td>				
			    </tr> 
				<tr>
				 <td class="noborder">ZIP code</td>
				 <td class="noborder"><input tabindex="8" type="text" id="a8" data-src="zip_code" class="tabletd inputborder" value="'.$detail['zip_code'].'"></td>				 
			    </tr> 
				<tr>
				 <td class="noborder">Type of property (see table) </td>
				 <td class="noborder"><input tabindex="9" type="text" id="a9" data-src="type_of_property" class="tabletd inputborder" value="'.$detail['type_of_property'].'"></td>				 
			    </tr> 
				<tr>
				 <td class="noborder">Other type of property </td>
				 <td class="noborder"><input tabindex="10" type="text" id="a10" data-src="other_type_of_property" class="tabletd inputborder" value="'.$detail['other_type_of_property'].'"></td>				
			    </tr>
				<tr>
				 <td class="noborder">Number of days rented </td>
				 <td class="noborder" style="text-align:right;"><div style="display:inline-block;border:1px solid #000;border-right:0px;width:30px;text-align:center;margin-right:-3px;line-height:22px;">34</div>
				 <input tabindex="11" type="text" id="a11" data-src="number_of_day_rent" class="tabletd w130" value="'.$detail['number_of_day_rent'].'" style="border:1px solid #000;"></td>	
				 <td class="noborder"><input tabindex="12" type="text" id="a12" data-src="number_of_day_rent_2" class="tabletd " value="'.$detail['number_of_day_rent_2'].'" style="border:1px solid #000;border-left:none;"> </td>	
			    </tr>
			</table>
			<div style="margin-top:20px;"></div>
			<table style="width:100%;">
			  <tr>
				 <td class="noborder" style="font-size:10px;">Percentage of ownership<br>if not 100% (.xxxx). </td>
				 <td class="noborder"><input tabindex="13" type="text" id="a13" data-src="percent_of_ownership" class="tabletd inputborder" value="'.$detail['percent_of_ownership'].'"></td>	
				 <td class="noborder padding20">1=did not actively participate </td>
				 <td class="noborder"><input tabindex="14" type="text" id="a14" data-src="did_not_actively_participate" class="tabletd inputborder" value="'.$detail['did_not_actively_participate'].'"></td>				 
			  </tr> 
			  <tr>
				 <td class="noborder" style="font-size:10px;">Percentage of tenant occupancy <br>if not 100% (.xxxx)</td>
				 <td class="noborder"><input tabindex="15" type="text" id="a15" data-src="percent_of_tenant_occupancy" class="tabletd inputborder" value="'.$detail['percent_of_tenant_occupancy'].'"></td>	
				 <td class="noborder padding20">1=real estate professional </td>
				 <td class="noborder"><input tabindex="16" type="text" id="a16" data-src="real_estate_professional" class="tabletd inputborder" value="'.$detail['real_estate_professional'].'"></td>				 
			  </tr>
			  <tr>
				 <td class="noborder">1=spouse, 2=joint</td>
				 <td class="noborder"><input tabindex="17" type="text" id="a17" data-src="spouse_joint" class="tabletd inputborder" value="'.$detail['spouse_joint'].'"></td>	
				 <td class="noborder padding20">1=rental other than real estate </td>
				 <td class="noborder"><input tabindex="18" type="text" id="a18" data-src="rental_other_than_real" class="tabletd inputborder" value="'.$detail['rental_other_than_real'].'"></td>				 
			  </tr>
			  <tr>
				 <td class="noborder">1=qualified joint venture</td>
				 <td class="noborder"><input tabindex="19" type="text" id="a19" data-src="qualified_joint_venture" class="tabletd inputborder" value="'.$detail['qualified_joint_venture'].'"></td>	
				 <td class="noborder padding20">1=investment </td>
				 <td class="noborder"><input tabindex="20" type="text" id="a20" data-src="investment" class="tabletd inputborder" value="'.$detail['investment'].'"></td>				 
			  </tr>
			  <tr>
				 <td class="noborder" style="font-size:10px;">1=nonpassive activity, <br>2=passive royalty</td>
				 <td class="noborder"><input tabindex="21" type="text" id="a21" data-src="nonpassive_activity" class="tabletd inputborder" value="'.$detail['nonpassive_activity'].'"></td>	
				 <td class="noborder padding20" style="font-size:10px;">1=single member limited <br>liability company. </td>
				 <td class="noborder"><input tabindex="22" type="text" id="a22" data-src="liability" class="tabletd inputborder" value="'.$detail['liability'].'"></td>				 
			  </tr>
			  <tr>
				 <td class="noborder" colspan="3">If required to file Form(s) 1099, did you or will you file all required Form(s) 1099: 1=yes, 2=no</td>
				 <td class="noborder"><input tabindex="23" type="text" id="a23" data-src="fill_forms" class="tabletd inputborder" value="'.$detail['fill_forms'].'"></td>				 
			  </tr>
			</table>
			<div style="font-weight:bold;font-size:18px;margin-top:20px;">INCOME</div>
			<table>
			   <tr>
				 <td class="noborder" style="width:50%;">Rents or royalties received </td>
				 <td class="noborder"><input tabindex="24" type="text" id="a24" data-src="rent_received" class="tabletd inputborder" value="'.$detail['rent_received'].'"></td>
				 <td class="noborder"><input tabindex="25" type="text" id="a25" data-src="rent_received_2" class="tabletd inputborder" value="'.$detail['rent_received_2'].'"></td>				 
			   </tr>
			</table>
			<div style="font-weight:bold;font-size:18px;margin-top:20px;">DIRECT EXPENSES</div>
			<div >NOTE : <span style="background-color:yellow;">Direct expenses are related only to the rental activity. These include rental agency fees, advertising, and office supplies.</span></div>
			<table style="margin-top:10px;">
			   <tr>
				 <td class="noborder" style="width:50%;">Advertising</td>
				 <td class="noborder"><input tabindex="26" type="text" id="a26" data-src="advertising" class="tabletd inputborder" value="'.$detail['advertising'].'"></td>
				 <td class="noborder"><input tabindex="27" type="text" id="a27" data-src="advertising_2" class="tabletd inputborder" value="'.$detail['advertising_2'].'"></td>				 
			   </tr>
			   <tr>
				 <td class="noborder" style="width:50%;">Association dues</td>
				 <td class="noborder"><input tabindex="28" type="text" id="a28" data-src="associated_dues" class="tabletd inputborder" value="'.$detail['associated_dues'].'"></td>
				 <td class="noborder"><input tabindex="29" type="text" id="a29" data-src="associated_dues_2" class="tabletd inputborder" value="'.$detail['associated_dues_2'].'"></td>				 
			   </tr>
			   <tr>
				 <td class="noborder" style="width:50%;">Auto and travel (not entered elsewhere)</td>
				 <td class="noborder"><input tabindex="30" type="text" id="a30" data-src="auto_and_travel" class="tabletd inputborder" value="'.$detail['auto_and_travel'].'"></td>
				 <td class="noborder"><input tabindex="31" type="text" id="a31" data-src="auto_and_travel_2" class="tabletd inputborder" value="'.$detail['auto_and_travel_2'].'"></td>				 
			   </tr>
			   <tr>
				 <td class="noborder" style="width:50%;">Cleaning and maintenance </td>
				 <td class="noborder"><input tabindex="32" type="text" id="a32" data-src="clean_and_maintenance" class="tabletd inputborder" value="'.$detail['clean_and_maintenance'].'"></td>
				 <td class="noborder"><input tabindex="33" type="text" id="a33" data-src="clean_and_maintenance_2" class="tabletd inputborder" value="'.$detail['clean_and_maintenance_2'].'"></td>				 
			   </tr>
			   <tr>
				 <td class="noborder" style="width:50%;">Commissions</td>
				 <td class="noborder"><input tabindex="34" type="text" id="a34" data-src="commission" class="tabletd inputborder" value="'.$detail['commission'].'"></td>
				 <td class="noborder"><input tabindex="35" type="text" id="a35" data-src="commission_2" class="tabletd inputborder" value="'.$detail['commission_2'].'"></td>				 
			   </tr>
			   <tr>
				 <td class="noborder" style="width:50%;">Gardening</td>
				 <td class="noborder"><input tabindex="36" type="text" id="a36" data-src="gradening" class="tabletd inputborder" value="'.$detail['gradening'].'"></td>
				 <td class="noborder"><input tabindex="37" type="text" id="a37" data-src="gradening_2" class="tabletd inputborder" value="'.$detail['gradening_2'].'"></td>				 
			   </tr>
			   <tr>
				 <td class="noborder" style="width:50%;">Insurance</td>
				 <td class="noborder"><input tabindex="38" type="text" id="a38" data-src="insurance" class="tabletd inputborder" value="'.$detail['insurance'].'"></td>
				 <td class="noborder"><input tabindex="39" type="text" id="a39" data-src="insurance_2" class="tabletd inputborder" value="'.$detail['insurance_2'].'"></td>				 
			   </tr>
			   <tr>
				 <td class="noborder" style="width:50%;">Legal and professional fees</td>
				 <td class="noborder"><input tabindex="40" type="text" id="a40" data-src="legal_and_professional_fees" class="tabletd inputborder" value="'.$detail['legal_and_professional_fees'].'"></td>
				 <td class="noborder"><input tabindex="41" type="text" id="a41" data-src="legal_and_professional_fees_2" class="tabletd inputborder" value="'.$detail['legal_and_professional_fees_2'].'"></td>				 
			   </tr>
			   <tr>
				 <td class="noborder" style="width:50%;">Licenses and permits</td>
				 <td class="noborder"><input tabindex="42" type="text" id="a42" data-src="license_and_permits" class="tabletd inputborder" value="'.$detail['license_and_permits'].'"></td>
				 <td class="noborder"><input tabindex="43" type="text" id="a43" data-src="license_and_permits_2" class="tabletd inputborder" value="'.$detail['license_and_permits_2'].'"></td>				 
			   </tr>
			   <tr>
				 <td class="noborder" style="width:50%;">Management fees</td>
				 <td class="noborder"><input tabindex="44" type="text" id="a44" data-src="management_fees" class="tabletd inputborder" value="'.$detail['management_fees'].'"></td>
				 <td class="noborder"><input tabindex="45" type="text" id="a45" data-src="management_fees_2" class="tabletd inputborder" value="'.$detail['management_fees_2'].'"></td>				 
			   </tr>
			   <tr>
				 <td class="noborder" style="width:50%;">Miscellaneous</td>
				 <td class="noborder"><input tabindex="46" type="text" id="a46" data-src="miscellaneous" class="tabletd inputborder" value="'.$detail['miscellaneous'].'"></td>
				 <td class="noborder"><input tabindex="47" type="text" id="a47" data-src="miscellaneous_2" class="tabletd inputborder" value="'.$detail['miscellaneous_2'].'"></td>				 
			   </tr>
			   <tr>
				 <td class="noborder" style="width:50%;">Mortgage interest (paid to banks, etc.)</td>
				 <td class="noborder"><input tabindex="48" type="text" id="a48" data-src="mortgage_interest" class="tabletd inputborder" value="'.$detail['mortgage_interest'].'"></td>
				 <td class="noborder"><input tabindex="49" type="text" id="a49" data-src="mortgage_interest_2" class="tabletd inputborder" value="'.$detail['mortgage_interest_2'].'"></td>				 
			   </tr>
			   <tr>
				 <td class="noborder" style="width:50%;">Qualified mortgage insurance premiums</td>
				 <td class="noborder"><input tabindex="50" type="text" id="a50" data-src="qualified_mortgage" class="tabletd inputborder" value="'.$detail['qualified_mortgage'].'"></td>
				 <td class="noborder"><input tabindex="51" type="text" id="a51" data-src="qualified_mortgage_2" class="tabletd inputborder" value="'.$detail['qualified_mortgage_2'].'"></td>				 
			   </tr>
			   <tr>
				 <td class="noborder" style="width:50%;">Excess mortgage interest</td>
				 <td class="noborder"><input tabindex="52" type="text" id="a52" data-src="excess_mortgage" class="tabletd inputborder" value="'.$detail['excess_mortgage'].'"></td>
				 <td class="noborder"><input tabindex="53" type="text" id="a53" data-src="excess_mortgage_2" class="tabletd inputborder" value="'.$detail['excess_mortgage_2'].'"></td>				 
			   </tr>
			   <tr>
				 <td class="noborder" style="width:50%;">Other interest (not entered elsewhere)</td>
				 <td class="noborder"><input tabindex="54" type="text" id="a54" data-src="other_interest" class="tabletd inputborder" value="'.$detail['other_interest'].'"></td>
				 <td class="noborder"><input tabindex="55" type="text" id="a55" data-src="other_interest_2" class="tabletd inputborder" value="'.$detail['other_interest_2'].'"></td>				 
			   </tr>
			   <tr>
				 <td class="noborder" style="width:50%;">Painting and decorating</td>
				 <td class="noborder"><input tabindex="56" type="text" id="a56" data-src="paintint_and_decorating" class="tabletd inputborder" value="'.$detail['paintint_and_decorating'].'"></td>
				 <td class="noborder"><input tabindex="57" type="text" id="a57" data-src="paintint_and_decorating_2" class="tabletd inputborder" value="'.$detail['paintint_and_decorating_2'].'"></td>				 
			   </tr>
			   <tr>
				 <td class="noborder" style="width:50%;">Pest control</td>
				 <td class="noborder"><input tabindex="58" type="text" id="a58" data-src="pest_control" class="tabletd inputborder" value="'.$detail['pest_control'].'"></td>
				 <td class="noborder"><input tabindex="59" type="text" id="a59" data-src="pest_control_2" class="tabletd inputborder" value="'.$detail['pest_control_2'].'"></td>				 
			   </tr>
			   <tr>
				 <td class="noborder" style="width:50%;">Plumbing and electrical </td>
				 <td class="noborder"><input tabindex="60" type="text" id="a60" data-src="plumbing_and_electrical" class="tabletd inputborder" value="'.$detail['plumbing_and_electrical'].'"></td>
				 <td class="noborder"><input tabindex="61" type="text" id="a61" data-src="plumbing_and_electrical_2" class="tabletd inputborder" value="'.$detail['plumbing_and_electrical_2'].'"></td>				 
			   </tr>
			   <tr>
				 <td class="noborder" style="width:50%;">Repairs </td>
				 <td class="noborder"><input tabindex="62" type="text" id="a62" data-src="repairs" class="tabletd inputborder" value="'.$detail['repairs'].'"></td>
				 <td class="noborder"><input tabindex="63" type="text" id="a63" data-src="repairs_2" class="tabletd inputborder" value="'.$detail['repairs_2'].'"></td>				 
			   </tr>
			   <tr>
				 <td class="noborder" style="width:50%;">Supplies </td>
				 <td class="noborder"><input tabindex="64" type="text" id="a64" data-src="supplies" class="tabletd inputborder" value="'.$detail['supplies'].'"></td>
				 <td class="noborder"><input tabindex="65" type="text" id="a65" data-src="supplies_2" class="tabletd inputborder" value="'.$detail['supplies_2'].'"></td>				 
			   </tr>
			   <tr>
				 <td class="noborder" style="width:50%;">Taxes - real estate </td>
				 <td class="noborder"><input tabindex="66" type="text" id="a66" data-src="taxes_real_estate" class="tabletd inputborder" value="'.$detail['taxes_real_estate'].'"></td>
				 <td class="noborder"><input tabindex="67" type="text" id="a67" data-src="taxes_real_estate_2" class="tabletd inputborder" value="'.$detail['taxes_real_estate_2'].'"></td>				 
			   </tr>
			   <tr>
				 <td class="noborder" style="width:50%;">Taxes - other (not entered elsewhere)  </td>
				 <td class="noborder"><input tabindex="68" type="text" id="a68" data-src="taxes_others" class="tabletd inputborder" value="'.$detail['taxes_others'].'"></td>
				 <td class="noborder"><input tabindex="69" type="text" id="a69" data-src="taxes_others_2" class="tabletd inputborder" value="'.$detail['taxes_others_2'].'"></td>				 
			   </tr>
			   <tr>
				 <td class="noborder" style="width:50%;">Telephone </td>
				 <td class="noborder"><input tabindex="70" type="text" id="a70" data-src="telephone" class="tabletd inputborder" value="'.$detail['telephone'].'"></td>
				 <td class="noborder"><input tabindex="71" type="text" id="a71" data-src="telephone_2" class="tabletd inputborder" value="'.$detail['telephone_2'].'"></td>				 
			   </tr>
			   <tr>
				 <td class="noborder" style="width:50%;">Utilities </td>
				 <td class="noborder"><input tabindex="72" type="text" id="a72" data-src="utilities" class="tabletd inputborder" value="'.$detail['utilities'].'"></td>
				 <td class="noborder"><input tabindex="73" type="text" id="a73" data-src="utilities_2" class="tabletd inputborder" value="'.$detail['utilities_2'].'"></td>				 
			   </tr>
			   <tr>
				 <td class="noborder" style="width:50%;">Wages and salaries </td>
				 <td class="noborder"><input tabindex="74" type="text" id="a74" data-src="wages_and_salaries" class="tabletd inputborder" value="'.$detail['wages_and_salaries'].'"></td>
				 <td class="noborder"><input tabindex="75" type="text" id="a75" data-src="wages_and_salaries_2" class="tabletd inputborder" value="'.$detail['wages_and_salaries_2'].'"></td>				 
			   </tr>			  
			</table>
			<div>Other:</div>
			<table>
			   <tr>
				 <td class="noborder" style="width:50%;padding-left:30px;"><input tabindex="76" type="text" id="a76" data-src="other_1" class="tabletd borderBottom" value="'.$detail['other_1'].'" style="width:360px;"></td>
				 <td></td>
				 <td></td>				 
			   </tr>
			   <tr>
				 <td class="noborder" style="width:50%;padding-left:30px;"><input tabindex="77" type="text" id="a77" data-src="other_2" class="tabletd borderBottom" value="'.$detail['other_2'].'" style="width:360px;"></td>
				 <td></td>
				 <td></td>				 
			   </tr>
			   <tr>
				 <td class="noborder" style="width:50%;padding-left:30px;"><input tabindex="78" type="text" id="a78" data-src="other_3" class="tabletd borderBottom" value="'.$detail['other_3'].'" style="width:360px;"></td>
				 <td ></td>
				 <td></td>				 
			   </tr>
			   <tr>
				 <td class="noborder" style="width:50%;padding-left:30px;"><input tabindex="79" type="text" id="a79" data-src="other_4" class="tabletd borderBottom" value="'.$detail['other_4'].'" style="width:360px;"></td>
				 <td class=""></td>
				 <td class=""></td>				 
			   </tr>			   
			</table>
		   </div>
		   <table style="width:100%;">
			    <tr>
				 <td style="width:90%;border:3px solid #000;border-top:none;"></td>
				 <td class="title" style="border:3px solid #000;border-top:none;">18</td>
			    </tr> 
			</table>
			<table style="width:100%;margin-top:90px;">
			    <tr>
				 <td class="w50" style="border:3px solid #000;height: 50px;"> </td>
				 <td class="title" style="border:3px solid #000;">1040</td>
				 <td class="title" style="border:3px solid #000;">US</td>
				 <td class="title" style="border:3px solid #000;">Rental & Royalty Income (Sch. E) (cont.)</td>
				 <td style="border:3px solid #000;">&nbsp;Year <input tabindex="80" type="text" id="a80" data-src="tax_year" class="tabletd w70" value="'.$detail['tax_year'].'" style="border:1px solid #000;"></td>
				 <td  class="title" style="border:3px solid #000;">18</td>
			    </tr> 
			</table>
		   <div style="border:3px solid #000;padding:20px;border-top:none;">
				<div style="margin-top:10px;"><b>The indirect</b> expense column should only be used for vacation homes or less than 100% tenant occupied rentals.</div>
				<div style="font-weight:bold;font-size:18px;margin-top:5px;margin-bottom:20px;">GENERAL INFORMATION</div>
				<table>
				   <tr>
					 <td class="noborder" style="width:50%;">Foreign region</td>
					 <td class="noborder" colspan="3"><input tabindex="81" type="text" id="a81" data-src="foreign_region" class="tabletd inputborder" value="'.$detail['foreign_region'].'"></td>				 
				   </tr>
				   <tr>
					 <td class="noborder" style="width:50%;">Foreign postal code</td>
					 <td class="noborder" colspan="3"><input tabindex="82" type="text" id="a82" data-src="foreign_postal_code" class="tabletd inputborder" value="'.$detail['foreign_postal_code'].'"></td>				 
				   </tr>
				   <tr>
					 <td class="noborder" style="width:50%;">Foreign country</td>
					 <td class="noborder" colspan="3"><input tabindex="83" type="text" id="a83" data-src="foreign_country" class="tabletd inputborder" value="'.$detail['foreign_country'].'"></td>				 
				   </tr>
			   </table>
			   <div style="font-weight:bold;font-size:18px;margin-top:5px;">OIL AND GAS</div>
			   <table>
				   <tr>
					 <td class="noborder" style="width:50%;">Production type (preparer use only)</td>
					 <td class="noborder"><input tabindex="84" type="text" id="a84" data-src="production_type" class="tabletd inputborder" value="'.$detail['production_type'].'"></td>
					 <td class="noborder"><input tabindex="85" type="text" id="a85" data-src="production_type_2" class="tabletd inputborder" value="'.$detail['production_type_2'].'"></td>				 
				   </tr>
				   <tr>
					 <td class="noborder" style="width:50%;">Cost depletion</td>
					 <td class="noborder"><input tabindex="86" type="text" id="a86" data-src="cost_depletion_1" class="tabletd inputborder" value="'.$detail['cost_depletion_1'].'"></td>
					 <td class="noborder"><input tabindex="87" type="text" id="a87" data-src="cost_depletion_2" class="tabletd inputborder" value="'.$detail['cost_depletion_2'].'"></td>				 
				   </tr>
				   <tr>
					 <td class="noborder" style="width:50%;">Percentage depletion rate or amount</td>
					 <td class="noborder"><input tabindex="88" type="text" id="a88" data-src="percentage_depletion_1" class="tabletd inputborder" value="'.$detail['percentage_depletion_1'].'"></td>
					 <td class="noborder"><input tabindex="89" type="text" id="a89" data-src="percentage_depletion_2" class="tabletd inputborder" value="'.$detail['percentage_depletion_2'].'"></td>				 
				   </tr>
				   <tr>
					 <td class="noborder" style="width:50%;">State cost depletion, if different (-1 if none)</td>
					 <td class="noborder"><input tabindex="90" type="text" id="a90" data-src="state_cost_depletion_1" class="tabletd inputborder" value="'.$detail['state_cost_depletion_1'].'"></td>
					 <td class="noborder"><input tabindex="91" type="text" id="a91" data-src="state_cost_depletion_2" class="tabletd inputborder" value="'.$detail['state_cost_depletion_2'].'"></td>				 
				   </tr>
				   <tr>
					 <td class="noborder" style="width:50%;">State % depletion rate or amount, if different (-1 if none) .</td>
					 <td class="noborder"><input tabindex="92" type="text" id="a92" data-src="state_depletion_rate_or_amount_1" class="tabletd inputborder" value="'.$detail['state_depletion_rate_or_amount_1'].'"></td>
					 <td class="noborder"><input tabindex="93" type="text" id="a93" data-src="state_depletion_rate_or_amount_2" class="tabletd inputborder" value="'.$detail['state_depletion_rate_or_amount_2'].'"></td>				 
				   </tr>
			   </table>
			   <div style="font-weight:bold;font-size:18px;margin-top:5px;">PERSONAL USE OF DWELLING UNIT (INCLUDING VACATION HOME)</div>
			   <table>
				   <tr>
					 <td class="noborder" style="width:50%;">Number of days personal use</td>
					 <td class="noborder"><input tabindex="94" type="text" id="a94" data-src="number_of_days_personal_use_1" class="tabletd inputborder" value="'.$detail['number_of_days_personal_use_1'].'"></td>
					 <td class="noborder"><input tabindex="95" type="text" id="a95" data-src="number_of_days_personal_use_2" class="tabletd inputborder" value="'.$detail['number_of_days_personal_use_2'].'"></td>				 
				   </tr>
				   <tr>
					 <td class="noborder" style="width:50%;">Number of days owned (if optional method elected)</td>
					 <td class="noborder"><input tabindex="96" type="text" id="a96" data-src="number_of_days_owned_1" class="tabletd inputborder" value="'.$detail['number_of_days_owned_1'].'"></td>
					 <td class="noborder"><input tabindex="97" type="text" id="a97" data-src="number_of_days_owned_2" class="tabletd inputborder" value="'.$detail['number_of_days_owned_2'].'"></td>				 
				   </tr>
			   </table>
			   <div style="font-weight:bold;font-size:18px;margin-top:5px;">INDIRECT EXPENSES</div>
			   <div style="margin-top:5px;">NOTE :<span style="background-color:yellow;">Indirect expenses are related to operating or maintaining the dwelling unit.<br>
					These include repairs, insurance, and utilities.</span></div>
			   <table>
				   <tr>
					 <td class="noborder" style="width:50%;">Advertising</td>
					 <td class="noborder"><input tabindex="98" type="text" id="a98" data-src="advertising2_1" class="tabletd inputborder" value="'.$detail['advertising2_1'].'"></td>
					 <td class="noborder"><input tabindex="99" type="text" id="a99" data-src="advertising2_2" class="tabletd inputborder" value="'.$detail['advertising2_2'].'"></td>				 
				   </tr>
				   <tr>
					 <td class="noborder" style="width:50%;">Association dues</td>
					 <td class="noborder"><input tabindex="100" type="text" id="a100" data-src="associated_dues2_1" class="tabletd inputborder" value="'.$detail['associated_dues2_1'].'"></td>
					 <td class="noborder"><input tabindex="101" type="text" id="a101" data-src="associated_dues2_2" class="tabletd inputborder" value="'.$detail['associated_dues2_2'].'"></td>				 
				   </tr>
				   <tr>
					 <td class="noborder" style="width:50%;">Auto and travel (not entered elsewhere)</td>
					 <td class="noborder"><input tabindex="102" type="text" id="a102" data-src="auto_and_travel2_1" class="tabletd inputborder" value="'.$detail['auto_and_travel2_1'].'"></td>
					 <td class="noborder"><input tabindex="103" type="text" id="a103" data-src="auto_and_travel2_2" class="tabletd inputborder" value="'.$detail['auto_and_travel2_2'].'"></td>				 
				   </tr>
				   <tr>
					 <td class="noborder" style="width:50%;">Cleaning and maintenance</td>
					 <td class="noborder"><input tabindex="104" type="text" id="a104" data-src="clean_and_maintenance2_1" class="tabletd inputborder" value="'.$detail['clean_and_maintenance2_1'].'"></td>
					 <td class="noborder"><input tabindex="105" type="text" id="a105" data-src="clean_and_maintenance2_2" class="tabletd inputborder" value="'.$detail['clean_and_maintenance2_2'].'"></td>				 
				   </tr>
				   <tr>
					 <td class="noborder" style="width:50%;">Commissions</td>
					 <td class="noborder"><input tabindex="106" type="text" id="a106" data-src="commission2_1" class="tabletd inputborder" value="'.$detail['commission2_1'].'"></td>
					 <td class="noborder"><input tabindex="107" type="text" id="a107" data-src="commission2_2" class="tabletd inputborder" value="'.$detail['commission2_2'].'"></td>				 
				   </tr>
				   <tr>
					 <td class="noborder" style="width:50%;">Gardening</td>
					 <td class="noborder"><input tabindex="108" type="text" id="a108" data-src="gradening2_1" class="tabletd inputborder" value="'.$detail['gradening2_1'].'"></td>
					 <td class="noborder"><input tabindex="109" type="text" id="a109" data-src="gradening2_2" class="tabletd inputborder" value="'.$detail['gradening2_2'].'"></td>				 
				   </tr>
				   <tr>
					 <td class="noborder" style="width:50%;">Insurance</td>
					 <td class="noborder"><input tabindex="110" type="text" id="a110" data-src="insurance2_1" class="tabletd inputborder" value="'.$detail['insurance2_1'].'"></td>
					 <td class="noborder"><input tabindex="111" type="text" id="a111" data-src="insurance2_2" class="tabletd inputborder" value="'.$detail['insurance2_2'].'"></td>				 
				   </tr>
				   <tr>
					 <td class="noborder" style="width:50%;">Legal and professional fees</td>
					 <td class="noborder"><input tabindex="112" type="text" id="a112" data-src="legal_and_professional_fees2_1" class="tabletd inputborder" value="'.$detail['legal_and_professional_fees2_1'].'"></td>
					 <td class="noborder"><input tabindex="113" type="text" id="a113" data-src="legal_and_professional_fees2_2" class="tabletd inputborder" value="'.$detail['legal_and_professional_fees2_2'].'"></td>				 
				   </tr>
				   <tr>
					 <td class="noborder" style="width:50%;">Licenses and permits</td>
					 <td class="noborder"><input tabindex="114" type="text" id="a114" data-src="license_and_permits2_1" class="tabletd inputborder" value="'.$detail['license_and_permits2_1'].'"></td>
					 <td class="noborder"><input tabindex="115" type="text" id="a115" data-src="license_and_permits2_2" class="tabletd inputborder" value="'.$detail['license_and_permits2_2'].'"></td>				 
				   </tr>
				   <tr>
					 <td class="noborder" style="width:50%;">Management fees </td>
					 <td class="noborder"><input tabindex="116" type="text" id="a116" data-src="management_fees2_1" class="tabletd inputborder" value="'.$detail['management_fees2_1'].'"></td>
					 <td class="noborder"><input tabindex="117" type="text" id="a117" data-src="management_fees2_2" class="tabletd inputborder" value="'.$detail['management_fees2_2'].'"></td>				 
				   </tr>
				   <tr>
					 <td class="noborder" style="width:50%;">Miscellaneous</td>
					 <td class="noborder"><input tabindex="118" type="text" id="a118" data-src="miscellaneous2_1" class="tabletd inputborder" value="'.$detail['miscellaneous2_1'].'"></td>
					 <td class="noborder"><input tabindex="119" type="text" id="a119" data-src="miscellaneous2_2" class="tabletd inputborder" value="'.$detail['miscellaneous2_2'].'"></td>				 
				   </tr>
				   <tr>
					 <td class="noborder" style="width:50%;">Mortgage interest (paid to banks, etc.)</td>
					 <td class="noborder"><input tabindex="120" type="text" id="a120" data-src="mortgage_interest2_1" class="tabletd inputborder" value="'.$detail['mortgage_interest2_1'].'"></td>
					 <td class="noborder"><input tabindex="121" type="text" id="a121" data-src="mortgage_interest2_2" class="tabletd inputborder" value="'.$detail['mortgage_interest2_2'].'"></td>				 
				   </tr>
				   <tr>
					 <td class="noborder" style="width:50%;">Qualified mortgage insurance premiums</td>
					 <td class="noborder"><input tabindex="122" type="text" id="a122" data-src="qualified_mortgage2_1" class="tabletd inputborder" value="'.$detail['qualified_mortgage2_1'].'"></td>
					 <td class="noborder"><input tabindex="123" type="text" id="a123" data-src="qualified_mortgage2_2" class="tabletd inputborder" value="'.$detail['qualified_mortgage2_2'].'"></td>				 
				   </tr>
				   <tr>
					 <td class="noborder" style="width:50%;">Excess mortgage interest</td>
					 <td class="noborder"><input tabindex="124" type="text" id="a124" data-src="excess_mortgage2_1" class="tabletd inputborder" value="'.$detail['excess_mortgage2_1'].'"></td>
					 <td class="noborder"><input tabindex="125" type="text" id="a125" data-src="excess_mortgage2_2" class="tabletd inputborder" value="'.$detail['excess_mortgage2_2'].'"></td>				 
				   </tr>
				   <tr>
					 <td class="noborder" style="width:50%;">Other interest (not entered elsewhere)</td>
					 <td class="noborder"><input tabindex="126" type="text" id="a126" data-src="other_interest2_1" class="tabletd inputborder" value="'.$detail['other_interest2_1'].'"></td>
					 <td class="noborder"><input tabindex="127" type="text" id="a127" data-src="other_interest2_2" class="tabletd inputborder" value="'.$detail['other_interest2_2'].'"></td>				 
				   </tr>
				   <tr>
					 <td class="noborder" style="width:50%;">Painting and decorating</td>
					 <td class="noborder"><input tabindex="128" type="text" id="a128" data-src="paintint_and_decorating2_1" class="tabletd inputborder" value="'.$detail['paintint_and_decorating2_1'].'"></td>
					 <td class="noborder"><input tabindex="129" type="text" id="a129" data-src="paintint_and_decorating2_2" class="tabletd inputborder" value="'.$detail['paintint_and_decorating2_2'].'"></td>				 
				   </tr>
				   <tr>
					 <td class="noborder" style="width:50%;">Pest control</td>
					 <td class="noborder"><input tabindex="130" type="text" id="a130" data-src="pest_control2_1" class="tabletd inputborder" value="'.$detail['pest_control2_1'].'"></td>
					 <td class="noborder"><input tabindex="131" type="text" id="a131" data-src="pest_control2_2" class="tabletd inputborder" value="'.$detail['pest_control2_2'].'"></td>				 
				   </tr>
				   <tr>
					 <td class="noborder" style="width:50%;">Plumbing and electrical</td>
					 <td class="noborder"><input tabindex="132" type="text" id="a132" data-src="plumbing_and_electrical2_1" class="tabletd inputborder" value="'.$detail['plumbing_and_electrical2_1'].'"></td>
					 <td class="noborder"><input tabindex="133" type="text" id="a133" data-src="plumbing_and_electrical2_2" class="tabletd inputborder" value="'.$detail['plumbing_and_electrical2_2'].'"></td>				 
				   </tr>
				   <tr>
					 <td class="noborder" style="width:50%;">Repairs</td>
					 <td class="noborder"><input tabindex="134" type="text" id="a134" data-src="repairs2_1" class="tabletd inputborder" value="'.$detail['repairs2_1'].'"></td>
					 <td class="noborder"><input tabindex="135" type="text" id="a135" data-src="repairs2_2" class="tabletd inputborder" value="'.$detail['repairs2_2'].'"></td>				 
				   </tr>
				   <tr>
					 <td class="noborder" style="width:50%;">Supplies</td>
					 <td class="noborder"><input tabindex="136" type="text" id="a136" data-src="supplies2_1" class="tabletd inputborder" value="'.$detail['supplies2_1'].'"></td>
					 <td class="noborder"><input tabindex="137" type="text" id="a137" data-src="supplies2_2" class="tabletd inputborder" value="'.$detail['supplies2_2'].'"></td>				 
				   </tr>
				   <tr>
					 <td class="noborder" style="width:50%;">Taxes - real estate</td>
					 <td class="noborder"><input tabindex="138" type="text" id="a138" data-src="taxes_real_estate2_1" class="tabletd inputborder" value="'.$detail['taxes_real_estate2_1'].'"></td>
					 <td class="noborder"><input tabindex="139" type="text" id="a139" data-src="taxes_real_estate2_2" class="tabletd inputborder" value="'.$detail['taxes_real_estate2_2'].'"></td>				 
				   </tr>
				   <tr>
					 <td class="noborder" style="width:50%;">Taxes - other (not entered elsewhere) </td>
					 <td class="noborder"><input tabindex="140" type="text" id="a140" data-src="taxes_others2_1" class="tabletd inputborder" value="'.$detail['taxes_others2_1'].'"></td>
					 <td class="noborder"><input tabindex="141" type="text" id="a141" data-src="taxes_others2_2" class="tabletd inputborder" value="'.$detail['taxes_others2_2'].'"></td>				 
				   </tr>
				   <tr>
					 <td class="noborder" style="width:50%;">Telephone</td>
					 <td class="noborder"><input tabindex="142" type="text" id="a142" data-src="telephone2_1" class="tabletd inputborder" value="'.$detail['telephone2_1'].'"></td>
					 <td class="noborder"><input tabindex="143" type="text" id="a143" data-src="telephone2_2" class="tabletd inputborder" value="'.$detail['telephone2_2'].'"></td>				 
				   </tr>
				   <tr>
					 <td class="noborder" style="width:50%;">Utilities</td>
					 <td class="noborder"><input tabindex="144" type="text" id="a144" data-src="utilities2_1" class="tabletd inputborder" value="'.$detail['utilities2_1'].'"></td>
					 <td class="noborder"><input tabindex="145" type="text" id="a145" data-src="utilities2_2" class="tabletd inputborder" value="'.$detail['utilities2_2'].'"></td>				 
				   </tr>
				   <tr>
					 <td class="noborder" style="width:50%;">Wages and salaries</td>
					 <td class="noborder"><input tabindex="146" type="text" id="a146" data-src="wages_and_salaries2_1" class="tabletd inputborder" value="'.$detail['wages_and_salaries2_1'].'"></td>
					 <td class="noborder"><input tabindex="147" type="text" id="a147" data-src="wages_and_salaries2_2" class="tabletd inputborder" value="'.$detail['wages_and_salaries2_2'].'"></td>				 
				   </tr>
			   </table>
				<div>Other:</div>
				<table>
				   <tr>
					 <td class="noborder" style="width:50%;padding-left:30px;"><input tabindex="148" type="text" id="a148" data-src="other2_1" class="tabletd borderBottom" value="'.$detail['other2_1'].'" style="width:360px;"></td>
					 <td></td>
					 <td></td>				 
				   </tr>
				   <tr>
					 <td class="noborder" style="width:50%;padding-left:30px;"><input tabindex="149" type="text" id="a149" data-src="other2_2" class="tabletd borderBottom" value="'.$detail['other2_2'].'" style="width:360px;"></td>
					 <td></td>
					 <td></td>				 
				   </tr>
				   <tr>
					 <td class="noborder" style="width:50%;padding-left:30px;"><input tabindex="150" type="text" id="a150" data-src="other2_3" class="tabletd borderBottom" value="'.$detail['other2_3'].'" style="width:360px;"></td>
					 <td class=""></td>
					 <td class=""></td>				 
				   </tr>
				   <tr>
					 <td class="noborder" style="width:50%;padding-left:30px;"><input tabindex="151" type="text" id="a151" data-src="other2_4" class="tabletd borderBottom" value="'.$detail['other2_4'].'" style="width:360px;"></td>
					 <td class=""></td>
					 <td class=""></td>				 
				   </tr>			   
				</table>			   
		   </div>
		   <table style="width:100%;">
			    <tr>
				 <td style="width:90%;border:3px solid #000;border-top:none;"></td>
				 <td class="title" style="border:3px solid #000;border-top:none;">18</td>
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
		$map['plan']='rentalincome';
       $res=DB::name('users_clients')->where($map)->field('id')->find();
       
        if($res){
			$path='upload/rentalincome/'.$link;
			//先挪动文件至temp目录下，先创建当前目录下的temp目录
			$targetDirectory ='/var/www/vhosts/taxprep.republictax.com/httpdocs/public/upload/rentalincome/'.$link."/temp/";
			
			if (!is_dir($targetDirectory)) {
				   // 尝试创建目录
			   if (!mkdir($targetDirectory, 0755, true)) {
				   return false; 
			   }
			}
			$mm['user_client_id']=$res['id'];
			$detail=DB::name('user_rentalincome')->where($mm)->find();
		
			if($detail){
				 $htmlContent = $this->gethtml($detail);

				if($htmlContent){
					// 设置文件名和路径
					$filename = 'rentalincome.html';
					$filepath = '/var/www/vhosts/taxprep.republictax.com/httpdocs/public/upload/rentalincome/'.$link.'/pdf/';
					 
					// 确保目录存在
					if (!file_exists($filepath)) {
						mkdir($filepath, 0755, true);
					}
					
					// 将HTML内容写入文件
				    file_put_contents($filepath . $filename, $htmlContent);
					$htmlFilePath = '/var/www/vhosts/taxprep.republictax.com/httpdocs/public/upload/rentalincome/'.$link.'/pdf/'.$filename;
					$pdfFilePath = '/var/www/vhosts/taxprep.republictax.com/httpdocs/public/upload/rentalincome/'.$link.'/pdf/out.pdf';
					$command = "wkhtmltopdf --enable-local-file-access --lowquality $filepath$filename $pdfFilePath";
				
					exec($command, $output, $returnVar);
					
					if ($returnVar === 0) {
						header('Content-Description: File Transfer');
						header('Content-Type: application/octet-stream');
						header('Content-Disposition: attachment; filename=Tax_Organizer_Schedule_E_Blank_Rental_Income.pdf');
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
            $map['plan']='rentalincome';
            //$map['status']=1;
            $res=DB::name('users_clients')->where($map)->find();
           
            if($res){
            	$save2['updatetime']=time();
                $save2['status']=1;
                DB::name('users_clients')->where('id',$res['id'])->save($save2);//更新users_clients状态 
               
				$mm['user_client_id']=$res['id'];
				$profitloss=DB::name('user_rentalincome')->where($mm)->find();
				
				if($profitloss){
					$rr=DB::name('user_rentalincome')->where($mm)->save($save);
					
					if($data['fieldvalue']!==0){
		            	if ($data['fieldvalue']=='') {
		            		DB::name('user_rentalincome')->where($mm)->save(array($data['fieldname']=>null));
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
        $key=str_replace("/clients/rentalincome/",'',$str);
  
        if (strpos($key, "/ftag/1")) {
            $key=str_replace("/ftag/1",'',$key);
            View::assign('showtag', 1); 
        }else{
            View::assign('showtag', 0);
        }
        $map['link']=$key;
		if($key!="preview"){
        	$map['plan']="rentalincome";
        }
        $res=DB::name('users_clients')->where($map)->field('id,link,firstname,lastname,status')->find();
     
        if($res){
			$mm['user_client_id']=$res['id'];
			$profitloss=DB::name('user_rentalincome')->where($mm)->find();
			if(!$profitloss){
				$insert['user_client_id']=$res['id'];
				$insert['updatetime']=time();
				$pid=DB::name('user_rentalincome')->insertGetId($insert);
				$mm2['user_client_id']=$res['id'];
			    $profitloss=DB::name('user_rentalincome')->where($mm2)->find();
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
		$map22['plan']="rentalincome";
        $res2=DB::name('users_clients')->where($map22)->find();
        if($res2){
			$map['user_client_id']=$res2['id'];
			$res=DB::name('user_rentalincome')->where($map)->find();
		
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
								$new_path = '/rentalincome/'.$link."/sheets/".$pagename;
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
											$rr=DB::name('user_rentalincome')->where($map)->save($save);                              
											
										 //重新计算所有文件数量
										   $newdetail= DB::name('user_rentalincome')->where($map)->find();
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
