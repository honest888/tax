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

class Profitloss extends Base
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
		$link=$data['link'];
        $res=DB::name('users_clients')->where($map)->find();
        if($res){
			$mm['user_client_id']=$res['id'];
			$profitloss=DB::name('user_profit_loss')->where($mm)->find();
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
		$map['plan']='profitloss';
        $res=DB::name('users_clients')->where($map)->field('id,caseid')->find();
       
        if($res){
			$path='upload/profitloss/'.$link;
			//先挪动文件至temp目录下，先创建当前目录下的temp目录
			$targetDirectory ='/var/www/vhosts/taxprep.republictax.com/httpdocs/public/upload/profitloss/'.$link."/temp/";
			
			if (!is_dir($targetDirectory)) {
				   // 尝试创建目录
			   if (!mkdir($targetDirectory, 0755, true)) {
				   return false; 
			   }
			}
			$mm['user_client_id']=$res['id'];
			$detail=DB::name('user_profit_loss')->where($mm)->find();
		
			if($detail){
				 $htmlContent = $this->gethtml($detail);

				if($htmlContent){
					// 设置文件名和路径
					$filename = 'profitloss.html';
					$filepath = '/var/www/vhosts/taxprep.republictax.com/httpdocs/public/upload/profitloss/'.$link.'/pdf/';
					 
					// 确保目录存在
					if (!file_exists($filepath)) {
						mkdir($filepath, 0755, true);
					}
					 
					// 将HTML内容写入文件
				    file_put_contents($filepath . $filename, $htmlContent);
					$htmlFilePath = '/var/www/vhosts/taxprep.republictax.com/httpdocs/public/upload/profitloss/'.$link.'/pdf/'.$filename;
					$pdfFilePath = '/var/www/vhosts/taxprep.republictax.com/httpdocs/public/upload/profitloss/'.$link.'/pdf/out.pdf';
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
						 $fname=date("YmdHis",time());
				         $fname="Profitloss_".$fname.".pdf";   
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
				$profitloss=DB::name('user_profit_loss')->where($mm)->find();
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
	//用户端签名
	public function signpage(){
		$users=session('user');
        $islogin=0;
        if($users){
            $islogin=1;
        }
        View::assign('islogin', $islogin);
        $link = $_GET['link'];
        $map['link']=$link;
		$map['plan']="profitloss";
        $res=DB::name('users_clients')->where($map)->find();
          
        if($res){
			$mm['user_client_id']=$res['id'];
			$profitloss=DB::name('user_profit_loss')->where($mm)->find();
			
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
	public function gethtml($detail){
		$columns = Db::getFields('tp_user_profit_loss');
 
			// 遍历所有字段
			foreach ($columns as $column) {
			   if($column['name']!="user_client_id"&&$column['name']!="id"&&$column['name']!="business_name"&&$column['name']!="business_description"&&$column['name']!="year"&&$column['name']!="v1_year_make_modal"&&$column['name']!="v2_year_make_modal"&&$column['name']!="v1_date_placed_into_service"&&$column['name']!="v2_date_placed_into_service"&&$column['name']!="v1_is_vehicle_ownedfinanced"&&$column['name']!="v2_is_vehicle_ownedfinanced"&&$column['name']!="v1_is_vehicle_leased"&&$column['name']!="v2_is_vehicle_leased"&&$column['name']!="v1_was_vehicle_depreciated_a_prior_year"&&$column['name']!="v2_was_vehicle_depreciated_a_prior_year"&&$column['name']!="signimage"&&$column['name']!="updatetime"&&$column['name']!="curfield"&&$column['name']!="v1_miles_for_business_only"&&$column['name']!="v2_miles_for_business_only"&&$column['name']!="v1_miles_for_personal_only"&&$column['name']!="v2_miles_for_personal_only"&&$column['name']!="total_square_feet_of_home"&&$column['name']!="total_square_feet_of_office_only"){
			   	   if($detail[$column['name']]===0||$detail[$column['name']]>0){
			   	   	 $detail[$column['name']]="$".$detail[$column['name']];
			   	   }
			      
			   }
			}
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
				margin:20px;
				border-collapse: collapse;
				table-layout: auto;
			}
			th, td {
				clear:both;
				font-size:12px;
				text-align: left;
				height:14px;
				line-height:14px;
			}
			td{
				max-height:20px;
			}
			input[type="text"] {
				padding-left:20px;
				width: 100%;
				box-sizing: border-box;
				min-width: 150px;
			}
			
		   .tips{
			 font-size:12px;
		   }
		   .tipslarger{
			 font-size:12px;
		   }
		   .tipsbold{
			 padding-left: 10px;	   
			 border:1px solid #000;
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
		   
		   input{
			   border:1px solid #000;
			   height:14px;
		   }
		
			input:focus {
				outline: none;
				border: none;
			}
			.tableTd{
				border: none;
				background:#f1f4ff;
				font-size:12px;
			font-weight:normal;
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
				border:1px solid #536cf1;
			     width:10px;
				height:10px;
				margin-left:10px;
			}
			input[type="checkbox"] {
			  border-radius: 5px;
			  border:0.5px solid $ccc;
			}
	</style>
	<body>
		<table style="width:95%;">
		  <tr>
			 <td class="tipsbold noborder">TOTAL BUSINESS INCOME</td>
			 <td class="noborder" style="height:16px;line-height:16px;"><input type="text" class="tabletd" style="border:1px solid #000;height:16px;line-height:16px;" value="'.$detail['total_business_income'].'"></td>
			 <td class="noborder" colspan="2" rowspan="3" style="text-align: center;"> <img src="/var/www/vhosts/taxprep.republictax.com/httpdocs/public/static/images/logo.png" style="width:120px;"></td>
			 <td style="text-align:right;" class="tipsbold noborder borderLeft borderTop greybgcolor">BUSINESS NAME:</td>
			 <td class="noborder borderTop borderBottom borderRight"><input type="text" name="business_name" class="tabletd" id="input1" value="'.$detail['business_name'].'"></td>
		  </tr>
		  <tr>
		     <td class="noborder"></td>
			 <td class="tipsbold tipslarger noborder right" style="padding-top:0px;">***REQUIRED***</td>
			 <td style="text-align:right;" class="tipsbold noborder borderLeft greybgcolor">Business Description:</td>
			 <td class="noborder borderBottom borderRight" ><input type="text" name="business_description" class="tabletd" value="'.$detail['business_description'].'"></td>
		  </tr>
		  <tr>
			 <td class="tipsbold noborder">PART I: BUSINESS EXPENSES</td>
			 <td class="noborder" style="border-right:none;"></td>
			 <td style="text-align:right;" class="tipsbold noborder borderLeft borderBottom greybgcolor">Year:</td>
			 <td class="noborder borderBottom borderRight"><input type="text" name="business_description" class="tabletd" value="'.$detail['year'].'"></td>
		  </tr>
          <tr>
			 <td class="noborder"></td>
			 <td class="noborder"></td>
			 <td class="noborder"></td>
			 <td class="noborder"></td>
			 <td class="noborder"></td>
			 <td class="noborder"></td>
		  </tr>
		  <tr>
			 <td class="tipsbold">Advertising</td>
			 <td class="noborder borderBottom"><input type="text" class="tabletd" value="'.$detail['advertising'].'"></td>
			 <td colspan="2" class="tipsbold noborder">Did you purchase materials used towards Job Completion?</td>
			 <td colspan="2" class="tipsbold tipRed tipItailc noborder" style="text-align:center;">PLEASE NOTE: THESE ARE</td>
		  </tr>
          <tr>
			 <td class="tipsbold">Commissions & Fees</td>
			 <td class="noborder borderBottom"><input type="text"  class="tabletd" value="'.$detail['commission_fee'].'"></td>
			 <td colspan="2" class="tips noborder">(such as Lumber, Piping, Nails)</td>
			 <td colspan="2" class="tipsbold tipRed tipItailc noborder" style="text-align:center;">BUSINESS EXPENSES ONLY</td>
		  </tr>
		  <tr>
			 <td class="tipsbold">Subcontractor/Contract Labor</td>
			 <td class="noborder borderBottom"><input type="text"  class="tabletd" value="'.$detail['subcontractor'].'"></td>
			 <td colspan="2" class="tips tipsbold noborder" style="text-decoration:underline;">This cannot be combined with Expenses at left</td>
			 <td colspan="2" class="tipsbold tipRed tipItailc noborder"  style="text-align:center;">NOT PERSONAL EXPENSES</td>
		  </tr>
		  <tr>
			 <td class="tipsbold">Employee Health Insurance</td>
			 <td class="noborder borderBottom"><input type="text"  class="tabletd" value="'.$detail['employee_health_inshurance'].'"></td>
			 <td class="noborder"  style="text-align:right;">Total of Goods Purchased:</td>
			 <td class="noborder borderBottom"><input type="text"  class="tabletd" value="'.$detail['total_of_goods_purchased'].'"></td>
			 <td colspan="2" class="noborder"></td>
		  </tr>
		  <tr>
			 <td class="tipsbold">Insurance (non-Health) (Auto, Liability)</td>
			 <td class="noborder borderBottom"><input type="text"  class="tabletd" value="'.$detail['insurance'].'"></td>
			 <td colspan="4" class="noborder" style="border-bottom:1px solid #fff;"></td>
		  </tr>
		  <tr>
			 <td class="tipsbold">Interest</td>
			 <td colspan="5" class="noborder" style="border-top:1px solid #fff;"></td>
		  </tr>
		  <tr>
			 <td class="tipsbold">Paid to Non Mortgage Banks</td>
			 <td class="noborder borderBottom"><input type="text"  class="tabletd" value="'.$detail['paid_to_non_mortgage_banks'].'"></td>
			 <td colspan="4" class="noborder">Did you have a Home Office? If so, Complete PART II</td>
		  </tr>
		  <tr>
			 <td class="tipsbold">Business Credit Card Interest</td>
			 <td class="noborder borderBottom"><input type="text"  class="tabletd" value="'.$detail['business_credit_card'].'"></td>
			 <td colspan="4" class="noborder"></td>
		  </tr>
		  <tr>
			 <td class="tipsbold">Legal & Professional Fees</td>
			 <td class="noborder borderBottom"><input type="text"  class="tabletd" value="'.$detail['legal_professional_fees'].'"></td>
			 <td colspan="4" class="noborder">PART II: Business Use of Home</td>
		  </tr>
		  <tr>
			 <td class="tipsbold">Office Expenses</td>
			 <td class="noborder borderBottom"><input type="text" class="tabletd" value="'.$detail['office_expenses'].'"></td>
			 <td colspan="2" class="noborder borderBottom" ></td>
			 <td colspan="2" class="noborder" style="border-bottom:1px solid #fff;"></td>
		  </tr>
		  <tr>
			 <td class="tipsbold">Rent or Lease</td>
			 <td class="noborder"></td>
			 <td class="noborder  borderLeft right greybgcolor borderTop" >Total Square feet of Home</td>
			 <td class="noborder borderBottom borderTop borderRight greybgcolor"><input type="text"  class="tabletd" style="background:#dadada;" value="'.$detail['total_square_feet_of_home'].'"></td>
			 <td colspan="2" class="noborder" style="border-top:1px solid #fff;">***REQUIRED***</td>
		  </tr>
		  <tr>
			 <td class="tipsbold">Machinery, Equipment</td>
			 <td class="noborder borderBottom"><input type="text" class="tabletd" value="'.$detail['machinery_equipment'].'"></td>
			 <td class="noborder borderLeft borderBottom right greybgcolor">Total Square feet of Office only</td>
			 <td class="noborder borderBottom borderRight greybgcolor"><input type="text"  class="tabletd" style="background:#dadada;" value="'.$detail['total_square_feet_of_office_only'].'"></td>
			 <td colspan="2" class="noborder" style="border:1px solid #fff;">***REQUIRED***</td>
		  </tr>
		  <tr>
			 <td class="tipsbold">Land, Other</td>
			 <td class="noborder borderBottom"><input type="text"  class="tabletd" value="'.$detail['land_other'].'"></td>
			 <td colspan="4" class="noborder"></td>
		  </tr>
		  <tr style="height:20px;">
			 <td class="tipsbold" style="height:20px;"><span>Office Rent</span></td>
			 <td class="noborder borderBottom" style="height:20px;"><input type="text" class="tabletd" value="'.$detail['office_rent'].'"></td>
			 <td class="noborder right" style="height:20px;"><span>Mortgage Interest</span></td>
			 <td class="noborder borderBottom" style="height:20px;"><input type="text" class="tabletd" value="'.$detail['mortgage_interest'].'"></td>
			 <td colspan="2" class="noborder" style="height:20px;"></td>
		  </tr>
		  <tr>
			 <td class="tipsbold">Repairs & Maintenance</td>
			 <td class="noborder borderBottom"><input type="text"  class="tabletd" value="'.$detail['repairs_maintenance'].'"></td>
			 <td class="noborder right">Real Estate Taxes</td>
			 <td class="noborder borderBottom"><input type="text"  class="tabletd" value="'.$detail['real_estate_taxes'].'"></td>
			 <td colspan="2" class="noborder"></td>
		  </tr>
		  <tr>
			 <td class="tipsbold">Taxes & Licenses</td>
			 <td class="noborder borderBottom"><input type="text" class="tabletd" value="'.$detail['taxes_licenses'].'"></td>
			 <td class="noborder right">Home Insurance</td>
			 <td class="noborder borderBottom"><input type="text" class="tabletd" value="'.$detail['home_insurance'].'"></td>
			 <td colspan="2" class="noborder"></td>
		  </tr>
		  <tr>
			 <td class="tipsbold">Payroll Taxes</td>
			 <td class="noborder borderBottom"><input type="text" class="tabletd" value="'.$detail['payroll_taxes'].'"></td>
			 <td class="noborder right">MISC</td>
			 <td class="noborder borderBottom"><input type="text" class="tabletd" value="'.$detail['misc2'].'"></td>
			<td colspan="2" rowspan="8" class="noborder" style="padding-top:0px;">
				 <div style="margin-left:260px;text-align:left;border:1px solid #000;line-height:14px;padding: 0 5px;background:#dadada;">
				 I agree that all information contained<br>
				 here is accurate and to the best<br>
				 of my knowledge.
				   <p style="background:#b4c6de;width: 220px;text-align: center;border-bottom:1px solid #000;margin:2px;" id="sign">';
				    $old_sign_con='/var/www/vhosts/taxprep.republictax.com/httpdocs/public/'.$detail['signimage'];
					if (is_file($old_sign_con)&&file_exists($old_sign_con)) {
					// if($detail['signimage']){
					  $html.='<img src="/var/www/vhosts/taxprep.republictax.com/httpdocs/public/'.$detail['signimage'].'" id="signimg" style="width:220px;height:50px;">';
					 }else{
					  $html.='<img src="/var/www/vhosts/taxprep.republictax.com/httpdocs/public/static/images/sign.png" id="signimg" style="width:220px;">';
					 }
		  $html.=' </p>
				 <span style="font-size:12px;font-weight:bold;">CLIENT SIGNATURE</span>
				 </div>
				 <p style="text-align:center;margin-left:100px;">***REQUIRED***</p>
			 </td>
		  </tr>
		  <tr>
			 <td class="tipsbold">Sales Tax</td>
			 <td class="noborder borderBottom"><input type="text" class="tabletd" value="'.$detail['sales_tax'].'"></td>
			 <td class="noborder right">Mortgage / Rent</td>
			 <td class="noborder borderBottom"><input type="text" class="tabletd" value="'.$detail['mortgage_rent'].'"></td>
			
		  </tr>
		  <tr>
			 <td class="tipsbold">Other Tax or License</td>
			 <td class="noborder borderBottom"><input type="text"  class="tabletd" value="'.$detail['other_tax_or_license'].'"></td>
			 <td class="noborder right">Repairs & Maintenance</td>
			 <td class="noborder borderBottom"><input type="text"  class="tabletd" value="'.$detail['repairs_maintenance'].'"></td>
		
		  </tr>
		  <tr>
			 <td class="tipsbold">Travel</td>
			 <td class="noborder"></td>
			 <td class="noborder right">Utilities</td>
			 <td class="noborder borderBottom"><input type="text" class="tabletd" value="'.$detail['utilities'].'"></td>
			
		  </tr>
		  <tr>
			 <td class="tipsbold">Airfare/Train</td>
			 <td class="noborder borderBottom"><input type="text" class="tabletd" value="'.$detail['airfare_train'].'"></td>
			 <td class="noborder right">Other</td>
			 <td class="noborder borderBottom"><input type="text" class="tabletd" value="'.$detail['other'].'"></td>
			
		  </tr>
		  <tr>
			 <td class="tipsbold">Meals</td>
			 <td class="noborder borderBottom"><input type="text"  class="tabletd" value="'.$detail['meals'].'"></td>
			 <td class="noborder right"></td>
			 <td class="noborder right"></td>
		  </tr>
		  <tr>
			 <td class="tipsbold">Parking/Tolls</td>
			 <td class="noborder borderBottom"><input type="text"  class="tabletd" value="'.$detail['parking_tolls'].'"></td>
			 <td colspan="2" class="noborder"></td>
		  </tr>
		  <tr>
			 <td class="tipsbold">Car Rental / Gas</td>
			 <td class="noborder borderBottom"><input type="text"  class="tabletd" value="'.$detail['car_rental_gas'].'"></td>
			 <td colspan="2" class="noborder"></td>
		  </tr>
		  <tr>
			 <td class="tipsbold">Taxi, Shuttles, Shared</td>
			 <td class="noborder borderBottom"><input type="text"  class="tabletd" value="'.$detail['tax_shuttles_shared'].'"></td>
			 <td colspan="2" class="noborder tipsbold">Did you use a Vehicle for your Business? If so, complete PART III</td>
			 <td colspan="2" class="noborder"></td>
		  </tr>
		  <tr>
			 <td class="tipsbold">Lodging</td>
			 <td class="noborder borderBottom"><input type="text" class="tabletd" value="'.$detail['lodging'].'"></td>
			 <td colspan="2" class="noborder"></td>
			 <td colspan="2" class="noborder"></td>
		  </tr>
		  <tr>
			 <td class="tipsbold">Tips</td>
			 <td class="noborder borderBottom"><input type="text"  class="tabletd"class="tabletd" value="'.$detail['tips'].'"></td>
			 <td colspan="2" class="noborder tipsbold">PART III: Vehicle(s)</td>
			 <td colspan="2" class="noborder"></td>
		  </tr>
		  <tr>
			 <td class="tipsbold">Dry Cleaning</td>
			 <td class="noborder borderBottom"><input type="text"  class="tabletd" value="'.$detail['dry_cleaning'].'"></td>
			 <td class="noborder"></td>
			 <td class="noborder tipslarger tipsbold">VEHICLE 1</td>
			 <td class="noborder tipslarger tipsbold">VEHICLE 2</td>
			 <td class="noborder"></td>
		  </tr>
		  <tr>
			 <td class="tipsbold"># of Days of town</td>
			 <td class="noborder borderBottom"><input type="text"  class="tabletd" value="'.$detail['of_days_of_town'].'"></td>
			 <td class="noborder borderLeft borderTop borderRight right tipsbold greybgcolor">Vehicle - Year, Make, Model</td>
		     <td class="noborder borderBottom borderRight borderTop"><input type="text"  class="tabletd" value="'.$detail['v1_year_make_modal'].'"></td>
		     <td class="noborder borderBottom borderTop borderRight"><input type="text"  class="tabletd" value="'.$detail['v2_year_make_modal'].'"></td>
		     <td class="noborder">***REQUIRED***</td>
		  </tr>
	      <tr>
	       <td class="tipsbold">Utilities/Internet (not Home Utilities)</td>
	       <td class="noborder borderBottom"><input type="text" class="tabletd" value="'.$detail['utilities_internet'].'"></td>
	       <td class="noborder borderLeft borderRight right tipsbold greybgcolor">Date placed into service</td>
	       <td class="noborder borderBottom borderRight"><input type="text" class="tabletd" value="'.$detail['v1_date_placed_into_service'].'"></td>
	       <td class="noborder borderBottom borderRight"><input type="text" class="tabletd" value="'.$detail['v2_date_placed_into_service'].'"></td>
	       <td class="noborder">***REQUIRED***</td>
	      </tr>
	      <tr>
	       <td class="tipsbold">Telephone (landline, Mobile, FAX)</td>
	       <td class="noborder borderBottom"><input type="text" class="tabletd" value="'.$detail['telephone'].'"></td>
	       <td class="noborder borderLeft borderRight right tipsbold greybgcolor">Miles for Business Only</td>
	       <td class="noborder borderBottom borderRight"><input type="text"  class="tabletd" value="'.$detail['v1_miles_for_business_only'].'"></td>
	       <td class="noborder borderBottom borderRight"><input type="text"  class="tabletd" value="'.$detail['v2_miles_for_business_only'].'"></td>
	       <td class="noborder">***REQUIRED***</td>
	      </tr>
	      <tr>
	       <td class="tipsbold">Salaries / Wages - Employees</td>
	       <td class="noborder borderBottom"><input type="text"  class="tabletd" value="'.$detail['salaries_wages_employees'].'"></td>
	       <td class="noborder borderLeft borderRight right tipsbold greybgcolor">Miles for Personal Only</td>
	       <td class="noborder borderBottom borderRight"><input type="text"  class="tabletd" value="'.$detail['v1_miles_for_personal_only'].'"></td>
	       <td class="noborder borderBottom borderRight"><input type="text"  class="tabletd" value="'.$detail['v2_miles_for_personal_only'].'"></td>
	       <td class="noborder">***REQUIRED***</td>
	      </tr>';
		
		  $v1_is_vehicle_leased1="";
		  $v1_is_vehicle_leased2="";
		  if($detail['v1_is_vehicle_leased']==1){
			  $v1_is_vehicle_leased1="checked";
		  }
		  if($detail['v1_is_vehicle_leased']==2){
			  $v1_is_vehicle_leased2="checked";
		  }
		  $v2_is_vehicle_leased1="";
		  $v2_is_vehicle_leased2="";
		  if($detail['v2_is_vehicle_leased']==1){
			  $v2_is_vehicle_leased1="checked";
		  }
		  if($detail['v2_is_vehicle_leased']==2){
			  $v2_is_vehicle_leased2="checked";
		  }
		  
		  $v1_is_vehicle_ownedfinanced1="";
		  $v1_is_vehicle_ownedfinanced2="";
		  if($detail['v1_is_vehicle_ownedfinanced']==1){
			  $v1_is_vehicle_ownedfinanced1="checked";
		  }
		  if($detail['v1_is_vehicle_ownedfinanced']==2){
			  $v1_is_vehicle_ownedfinanced2="checked";
		  }
		  $v2_is_vehicle_ownedfinanced1="";
		  $v2_is_vehicle_ownedfinanced2="";
		  if($detail['v2_is_vehicle_ownedfinanced']==1){
			  $v2_is_vehicle_ownedfinanced1="checked";
		  }
		  if($detail['v2_is_vehicle_ownedfinanced']==2){
			  $v2_is_vehicle_ownedfinanced2="checked";
		  }
		  
		  $v1_was_vehicle_depreciated_a_prior_year1="";
		  $v1_was_vehicle_depreciated_a_prior_year2="";
		  if($detail['v1_was_vehicle_depreciated_a_prior_year']==1){
			  $v1_was_vehicle_depreciated_a_prior_year1="checked";
		  }
		  if($detail['v1_was_vehicle_depreciated_a_prior_year']==2){
			  $v1_was_vehicle_depreciated_a_prior_year2="checked";
		  }
		  $v2_was_vehicle_depreciated_a_prior_year1="";
		  $v2_was_vehicle_depreciated_a_prior_year2="";
		  if($detail['v2_was_vehicle_depreciated_a_prior_year']==1){
			  $v2_was_vehicle_depreciated_a_prior_year1="checked";
		  }
		  if($detail['v2_was_vehicle_depreciated_a_prior_year']==2){
			  $v2_was_vehicle_depreciated_a_prior_year2="checked";
		  }
		  
	      $html.='<tr>
	       <td class="tipsbold">Bank Fees</td>
	       <td class="noborder borderBottom"><input type="text"  class="tabletd" value="'.$detail['bank_fees'].'"></td>
	       <td class="noborder borderLeft borderRight right tipsbold greybgcolor">Is Vehicle Leased?</td>
	       <td class="noborder borderBottom borderRight greybgcolor" style="font-size:12px;"><input type="radio" class="fieldcheckbox" '.$v1_is_vehicle_leased1.'>&nbsp;&nbsp;  Yes <input type="radio"  class="fieldcheckbox" '.$v1_is_vehicle_leased2.'> &nbsp;&nbsp;No</td>
	       <td class="noborder borderBottom  borderRight greybgcolor" style="font-size:12px;"><input type="radio"  class="fieldcheckbox" '.$v2_is_vehicle_leased1.' >&nbsp;&nbsp;  Yes <input type="radio"  class="fieldcheckbox" '.$v2_is_vehicle_leased2.'> &nbsp;&nbsp;No</td>
	       <td class="noborder">***REQUIRED***</td>
	      </tr>
	      <tr>
	       <td class="tipsbold">Delivery</td>
	       <td class="noborder borderBottom"><input type="text" class="tabletd" '.$detail['delivery'].'></td>
	       <td class="noborder borderLeft borderRight right tipsbold greybgcolor">Is Vehicle Owned/Financed?</td>
	       <td class="noborder borderBottom borderRight greybgcolor"><input type="radio"  class="fieldcheckbox" '.$v1_is_vehicle_ownedfinanced1.'> &nbsp;&nbsp;Yes <input type="radio"  class="fieldcheckbox"  '.$v1_is_vehicle_ownedfinanced2.'> &nbsp;&nbsp;No</td>
	       <td class="noborder borderBottom borderRight greybgcolor"><input type="radio"  class="fieldcheckbox" '.$v2_is_vehicle_ownedfinanced1.'> &nbsp;&nbsp;Yes <input type="radio"  class="fieldcheckbox" '.$v2_is_vehicle_ownedfinanced2.'> &nbsp;&nbsp;No</td>
	       <td class="noborder">***REQUIRED***</td>
	      </tr>
	      <tr>
	       <td class="tipsbold">Dues & Subscriptions</td>
	       <td class="noborder borderBottom"><input type="text" class="tabletd" value="'.$detail['dues_subscriptions'].'"></td>
	       <td class="noborder borderLeft borderRight borderBottom right tipsbold greybgcolor">Was vehicle Depreciated a prior Year?</td>
	       <td class="noborder borderBottom borderRight greybgcolor"><input type="radio"  class="fieldcheckbox" '.$v1_was_vehicle_depreciated_a_prior_year1.'> &nbsp;&nbsp;Yes <input type="radio" class="fieldcheckbox" '.$v1_was_vehicle_depreciated_a_prior_year2.'> &nbsp;&nbsp;No</td>
	       <td class="noborder borderBottom borderRight greybgcolor"><input type="radio"  class="fieldcheckbox" '.$v2_was_vehicle_depreciated_a_prior_year1.'> &nbsp;&nbsp;Yes <input type="radio" class="fieldcheckbox" '.$v2_was_vehicle_depreciated_a_prior_year2.'> &nbsp;&nbsp;No</td>
	       <td class="noborder">***REQUIRED***</td>
	      </tr>
	      <tr>
			 <td class="tipsbold">Entertainment / Meals w/ Clients</td>
			 <td class="noborder borderBottom"></td>
			 <td class="noborder"></td>
			 <td class="noborder borderRight"></td>
			 <td class="noborder"></td>
			 <td class="noborder"></td>
		  </tr>
		  <tr>
	       <td class="tipsbold" style="line-height:12px;height:12px;">Gifts</td>
	       <td class="noborder borderBottom" style="line-height:12px;height:12px;"><input type="text" class="tabletd" value="'.$detail['gifts'].'"></td>
	       <td class="noborder borderLeft borderRight right tipsbold" style="line-height:12px;height:12px;">Lease / Financing Payments</td>
	       <td class="borderBottom borderRight" style="line-height:12px;height:12px;"><input type="text" class="tabletd" value="'.$detail['v1_lease_financing_payments'].'"></td>
	       <td class="borderBottom" style="line-height:12px;height:12px;"><input type="text" class="tabletd" value="'.$detail['v2_lease_financing_payments'].'"></td>
	       <td class="noborder" style="line-height:12px;height:12px;"></td>
	      </tr>
	      <tr>
	       <td class="tipsbold">Janitorial</td>
	       <td class="noborder borderBottom"><input type="text" class="tabletd" value="'.$detail['janitorial'].'"></td>
	       <td class="noborder borderLeft borderRight right tipsbold">Gas, Lube, Oil</td>
	       <td class="borderBottom borderRight"><input type="text"  class="tabletd" value="'.$detail['v1_gas_lube_oi'].'"></td>
	       <td class="borderBottom"><input type="text"  class="tabletd" value="'.$detail['v2_gas_lube_oi'].'"></td>
	       <td class="noborder"></td>
	      </tr>
	      <tr>
	       <td class="tipsbold">Laundry & Cleaning</td>
	       <td class="noborder borderBottom"><input type="text" class="tabletd" value="'.$detail['laundry_cleaning'].'"></td>
	       <td class="noborder borderLeft borderRight right tipsbold">Repairs and Maintence</td>
	       <td class="borderBottom borderRight"><input type="text"  class="tabletd" value="'.$detail['v1_repairs_and_maintence'].'"></td>
	       <td class="borderBottom"><input type="text"  class="tabletd" value="'.$detail['v2_repairs_and_maintence'].'"></td>
	       <td class="noborder"></td>
	      </tr>
	      <tr>
	       <td class="tipsbold">MISC</td>
	       <td class="noborder borderBottom"><input type="text" class="tabletd" value="'.$detail['misc'].'"></td>
	       <td class="noborder borderLeft borderRight right tipsbold">Tires</td>
	       <td class="borderBottom borderRight"><input type="text"  class="tabletd" value="'.$detail['v1_tires'].'"></td>
	       <td class="borderBottom"><input type="text"  class="tabletd" value="'.$detail['v2_tires'].'"></td>
	       <td class="noborder"></td>
	      </tr>
	      <tr>
	       <td class="tipsbold">Postage</td>
	       <td class="noborder borderBottom"><input type="text"  class="tabletd" value="'.$detail['postage'].'"></td>
	       <td class="noborder borderLeft borderRight right tipsbold">Towing</td>
	       <td class="borderBottom borderRight"><input type="text"  class="tabletd" value="'.$detail['v1_towing'].'"></td>
	       <td class="borderBottom"><input type="text"  class="tabletd" value="'.$detail['v2_towing'].'"></td>
	       <td class="noborder"></td>
	      </tr>
	      <tr>
	       <td class="tipsbold">Printing</td>
	       <td class="noborder borderBottom"><input type="text"  class="tabletd" value="'.$detail['printing'].'"></td>
	       <td class="noborder borderLeft borderRight right tipsbold">Insurance</td>
	       <td class="borderBottom borderRight"><input type="text"  class="tabletd" value="'.$detail['v1_insurance'].'"></td>
	       <td class="borderBottom"><input type="text"  class="tabletd" value="'.$detail['v2_insurance'].'"></td>
	       <td class="noborder"></td>
	      </tr>
	      <tr>
	       <td class="tipsbold">Security</td>
	       <td class="noborder borderBottom"><input type="text"  class="tabletd" value="'.$detail['security'].'"></td>
	       <td class="noborder borderLeft borderRight right tipsbold">License & Registration</td>
	       <td class="borderBottom borderRight"><input type="text"  class="tabletd" value="'.$detail['v1_license_registration'].'"></td>
	       <td class="borderBottom"><input type="text"  class="tabletd" value="'.$detail['v2_license_registration'].'"></td>
	       <td class="noborder"></td>
	      </tr>
	      <tr>
	       <td class="tipsbold">Tools</td>
	       <td class="noborder borderBottom"><input type="text"  class="tabletd" value="'.$detail['tools'].'"></td>
	       <td class="noborder borderLeft borderRight right tipsbold">Permits</td>
	       <td class="borderBottom borderRight"><input type="text" class="tabletd" value="'.$detail['v1_permits'].'"></td>
	       <td class="borderBottom"><input type="text" class="tabletd" value="'.$detail['v2_permits'].'"></td>
	       <td class="noborder"></td>
	      </tr>
	      <tr>
	       <td class="tipsbold">Uniforms</td>
	       <td class="noborder borderBottom"><input type="text" class="tabletd" value="'.$detail['uniforms'].'"></td>
	       <td class="noborder borderLeft borderRight right tipsbold">Interest</td>
	       <td class="borderBottom borderRight"><input type="text"  class="tabletd" value="'.$detail['v1_interest'].'"></td>
	       <td class="borderBottom"><input type="text"  class="tabletd" value="'.$detail['v2_interest'].'"></td>
	       <td class="noborder"></td>
	      </tr>
	      <tr>
	       <td class="tipsbold">Supplies</td>
	       <td class="noborder borderBottom"><input type="text" class="tabletd" value="'.$detail['supplies'].'"></td>
	       <td class="noborder borderLeft borderRight right tipsbold">Auto Club</td>
	       <td class="borderBottom borderRight"><input type="text"  class="tabletd" value="'.$detail['v1_auto_club'].'"></td>
	       <td class="borderBottom"><input type="text"  class="tabletd" value="'.$detail['v2_auto_club'].'"></td>
	       <td class="noborder"></td>
	      </tr>
	      <tr>
	       <td class="tipsbold">Other - (Explain)</td>
	       <td class="noborder borderBottom"><input type="text"  class="tabletd" value="'.$detail['other_explain'].'"></td>
	       <td class="noborder borderLeft borderRight right tipsbold">Warranty</td>
	       <td class="borderBottom borderRight"><input type="text" class="tabletd" value="'.$detail['v1_warranty'].'"></td>
	       <td class="borderBottom"><input type="text" class="tabletd" value="'.$detail['v2_warranty'].'"></td>
	       <td class="noborder"></td>
	      </tr>
	      <tr>
	       <td colspan="2" class="noborder"></td>
	       <td class="noborder right tipsbold borderRight">Smog</td>
	       <td class="borderBottom borderRight"><input type="text"  class="tabletd" value="'.$detail['v1_smog'].'"></td>
	       <td class="borderBottom"><input type="text"  class="tabletd" value="'.$detail['v2_smog'].'"></td>
	       <td class="noborder"></td>
	      </tr>
	      <tr>
	       <td class="tipsbold noborder right">Total Expenses:</td>
	       <td class="borderBottom borderLeft borderTop"><input type="text" class="tabletd" value="'.$detail['total_expenses'].'"></td>
	       <td class="noborder borderLeft borderRight right tipsbold">Other</td>
	       <td  class="borderBottom borderRight"><input type="text"  class="tabletd" value="'.$detail['v1_other'].'"></td>
	       <td class="borderBottom"><input type="text"  class="tabletd" value="'.$detail['v2_other'].'"></td>
	       <td class="noborder"></td>
	      </tr>
		  <tr>
	       <td class="noborder"></td>
	       <td class="noborder right">***REQUIRED***</td>
	       <td class="noborder"></td>
	       <td class="noborder"></td>
	       <td class="noborder"></td>
	       <td class="noborder right">Profit and Loss Statement</td>
	      </tr>
		</table>
	</body>
	</html>';
	return $html;
	}
	public function topdf(){
      // $link="k0ArSMziYDVZ8IwixdW3fsAhumj5";
        
        //复制完后生成pdf,发送后删除 
		$link=$_GET['link'];
		$map['link']=$_GET['link'];
		$map['plan']='profitloss';
       $res=DB::name('users_clients')->where($map)->field('id')->find();
       
        if($res){
			$path='upload/profitloss/'.$link;
			//先挪动文件至temp目录下，先创建当前目录下的temp目录
			$targetDirectory ='/var/www/vhosts/taxprep.republictax.com/httpdocs/public/upload/profitloss/'.$link."/temp/";
			
			if (!is_dir($targetDirectory)) {
				   // 尝试创建目录
			   if (!mkdir($targetDirectory, 0755, true)) {
				   return false; 
			   }
			}
			$mm['user_client_id']=$res['id'];
			$detail=DB::name('user_profit_loss')->where($mm)->find();
		
			if($detail){
				 $htmlContent = $this->gethtml($detail);

				if($htmlContent){
					// 设置文件名和路径
					$filename = 'profitloss.html';
					$filepath = '/var/www/vhosts/taxprep.republictax.com/httpdocs/public/upload/profitloss/'.$link.'/pdf/';
					 
					// 确保目录存在
					if (!file_exists($filepath)) {
						mkdir($filepath, 0755, true);
					}
					 
					// 将HTML内容写入文件
				    file_put_contents($filepath . $filename, $htmlContent);
					$htmlFilePath = '/var/www/vhosts/taxprep.republictax.com/httpdocs/public/upload/profitloss/'.$link.'/pdf/'.$filename;
					$pdfFilePath = '/var/www/vhosts/taxprep.republictax.com/httpdocs/public/upload/profitloss/'.$link.'/pdf/out.pdf';
					$command = "wkhtmltopdf --enable-local-file-access --orientation Landscape --lowquality $filepath$filename $pdfFilePath";
					
					exec($command, $output, $returnVar);
					
					if ($returnVar === 0) {
						header('Content-Description: File Transfer');
						header('Content-Type: application/octet-stream');
						header('Content-Disposition: attachment; filename=ProfitLoss.pdf');
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
	 public function profitsave(){
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
				$profitloss=DB::name('user_profit_loss')->where($mm)->find();
				if($profitloss){
					$rr=DB::name('user_profit_loss')->where($mm)->save($save);
					if($data['fieldvalue']!==0){
		            	if ($data['fieldvalue']=='') {
		            		DB::name('user_profit_loss')->where($mm)->save(array($data['fieldname']=>null));
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
	public function iframe(){
		$users=session('user');
        $islogin=0;
        if($users){
            $islogin=1;
        }
        View::assign('islogin', $islogin);
        $str=$_SERVER['REQUEST_URI'];
        $key=str_replace("/clients/iframe/",'',$str);
  
        if (strpos($key, "/ftag/1")) {
            $key=str_replace("/ftag/1",'',$key);
            View::assign('showtag', 1); 
        }else{
            View::assign('showtag', 0);
        }
        $map['link']=$key;
		$map['plan']="profitloss";
        $res=DB::name('users_clients')->where($map)->find();
          
        if($res){
			$mm['user_client_id']=$res['id'];
			$profitloss=DB::name('user_profit_loss')->where($mm)->find();
			if(!$profitloss){
				$insert['user_client_id']=$res['id'];
				$insert['updatetime']=time();
				$pid=DB::name('user_profit_loss')->insertGetId($insert);
				$mm2['user_client_id']=$res['id'];
			    $profitloss=DB::name('user_profit_loss')->where($mm2)->find();
			}
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
    public function files(){
     
        $users=session('user');
        $islogin=0;
        if($users){
            $islogin=1;
        }
        View::assign('islogin', $islogin);
        $str=$_SERVER['REQUEST_URI'];
        $key=str_replace("/clients/files/",'',$str);
  
        if (strpos($key, "/ftag/1")) {
            $key=str_replace("/ftag/1",'',$key);
            View::assign('showtag', 1); 
        }else{
            View::assign('showtag', 0);
        }
        $map['link']=$key;
        if($key!="preview"){
        	$map['plan']="profitloss";
        }
        $res=DB::name('users_clients')->where($map)->field('id,link,firstname,lastname,status')->find();
          
        if($res){
			$mm['user_client_id']=$res['id'];
			$profitloss=DB::name('user_profit_loss')->where($mm)->find();
			if(!$profitloss){
				$insert['user_client_id']=$res['id'];
				$insert['updatetime']=time();
				$pid=DB::name('user_profit_loss')->insertGetId($insert);
				$mm2['user_client_id']=$res['id'];
			    $profitloss=DB::name('user_profit_loss')->where($mm2)->find();
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
       
       return view('files_new');
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
