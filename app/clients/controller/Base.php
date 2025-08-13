<?php
/**
 *@作者:MissZhang
 *@邮箱:<787727147@qq.com>
 *@创建时间:2021/7/7 下午3:46
 *@说明:前台基类控制器
 */

namespace app\clients\controller;
use app\BaseController;
use app\common\model\ThirdUser;
use app\common\model\Users;
use think\facade\Db;
use think\facade\View;

class Base extends BaseController
{
    public $user_id = 0;
    public $user = array();
    public $page_size = 0;
    public $wx_user;
    protected function initialize()
    {
        //die();
        parent::initialize();
		date_default_timezone_set("America/Los_Angeles");
       set_time_limit(0);
        /*if (session('?user')) {
            $user = session('user');
            $user = Db::name('users')->where("user_id", $user['user_id'])->find();
            $user_model=Users::find($user['user_id']);
            session('user', $user);  //覆盖session 中的 user
            $this->user = $user_model;
            $this->user_id = $user['user_id'];
            View::assign('user', $user_model); //存储用户信息
            if ($this->user['is_lock']==1){
                session('user',null);
                $this->redirect(url('User/login'));
            }
        }else{
            $nologin =['login','do_login','reg','forget_pwd','protocol_detail','folders'];
            $nologin_controller =['Yunpian','api'];
            if (!$this->user_id && !in_array(ACTION_NAME, $nologin) && !in_array(CONTROLLER_NAME,$nologin_controller)) {
                $this->redirect(url('User/login'));
            }
        }*/
        //如果是微信浏览器 则必须要先登录
        /*
        if(strstr($_SERVER['HTTP_USER_AGENT'],'MicroMessenger')){
            $nologin_controller =['Yunpian'];
            if (empty($this->user_id) && !in_array(ACTION_NAME,['login','do_login','reg','forget_pwd']) && !in_array(CONTROLLER_NAME,$nologin_controller)){
                $this->redirect(url('User/login'));
            }else if ($this->user && empty(session('openid'))){
                $this->wx_user=Db::name('wx_user')->where(1)->find();
                if ($this->wx_user && $this->wx_user['wait_access']==1){
                    //获取openid
                    $this->get_openid();
                }
            }
        }else{
            $nologin =['login','do_login','reg','forget_pwd','protocol_detail'];
            $nologin_controller =['Yunpian'];
            if (!$this->user_id && !in_array(ACTION_NAME, $nologin) && !in_array(CONTROLLER_NAME,$nologin_controller)) {
                $this->redirect(url('User/login'));
            }
        }*/
        $field_ary = array();  //endpage 当next 为field_ary_next有作用
         $field_ary=array(
            'wages_and_salaries_did_confirm'=>array('h1'=>'Let\'s start with earnings you received from your job','sub'=>'','img'=>'wages-and-salaries.svg','next'=>'page15-1','endpage'=>'page15-1'),
            'self_employment_did_confirm'=>array('h1'=>'Let\'s go over your self-employment earnings','sub'=>"This doesn't include business information that'll be reported in a separate tax return.",'img'=>'self-employment.svg','next'=>'page15-2','endpage'=>'page15-2-15-22','endpage2'=>'page15-2-15-21','endpage3'=>'page15-2-15'),
            'stocks_mutual_funds_bonds_did_confirm'=>array('h1'=>"Let's go over the stocks, bonds, and cryptocurrency you sold",'sub'=>"If you sold investments held in a retirement account like an IRA or 401K, we'll ask you about that separately.",'img'=>'stocks-mutual-funds-bonds.svg','next'=>'page15-3','endpage'=>'page15-3-4'),
            'interest_did_confirm'=>array('h1'=>"Now let's go over the interest income you earned",'sub'=>'This includes interest you earned on savings bonds you cashed in.','img'=>'interest.svg','next'=>'page15-4','endpage'=>'page15-4'),
            'dividends_did_confirm'=>array('h1'=>"Now let's go over dividends you received",'sub'=>"",'img'=>'dividends.svg','next'=>'page15-5','endpage'=>'page15-5'),
            'sold_real_estate_did_confirm'=>array('h1'=>"Now we'll ask for the documents relating to the real estate you sold",'sub'=>"",'img'=>'sold-real-estate.svg','next'=>'page15-6','endpage'=>'page15-6-0'),
            'incentive_stock_options_did_confirm'=>array('h1'=>"Now let's go over the Incentive Stock Options you exercised",'sub'=>"",'img'=>'incentive-stock-options.svg','next'=>'page15-7','endpage'=>'page15-7'),
            'foreign_accounts_did_confirm'=>array('h1'=>"Now we'll ask about your foreign accounts",'sub'=>"This can include a bank account, brokerage account, or any other financial account. Do not include mutual funds holding foreign assets, as those are not included in foreign reporting requirements.",'img'=>'foreign-accounts.svg','next'=>'page15-8','endpage'=>'page15-8','endpage1'=>'page15-8-1','endpage2'=>'page15-8-2','endpage3'=>'page15-8-3','endpage4'=>'page15-8-4','endpage5'=>'page15-8-5'),
            '1099_oid_did_confirm'=>array('h1'=>"Now we'll ask for the 1099-OID you received",'sub'=>"",'img'=>'1099-oid.svg','next'=>'page15-9','endpage'=>'page15-9'),
            'undistributed_capital_gains_did_confirm'=>array('h1'=>"Now let's go over your undistributed capital gains",'sub'=>"",'img'=>'undistributed-capital-gains.svg','next'=>'page15-10','endpage'=>'page15-10'),
            'ira_401k_pension_plan_withdrawals_did_confirm'=>array('h1'=>"Now let's go over your IRA, 401(k), and other pension plan withdrawals",'sub'=>"This includes any annuities, life insurance proceeds or disability payments you might have received.",'img'=>'ira-401k-pension-plan-withdrawals.svg','next'=>'page15-11','endpage'=>'page15-11-3'),
            'social_security_did_confirm'=>array('h1'=>"Now let's go over your Social Security benefits",'sub'=>"This includes payments for social security retirement, social security disability, survivors benefits, and railroad retirement.",'img'=>'social-security.svg','next'=>'page15-12','endpage'=>'page15-12'),
            'rental_properties_did_confirm'=>array('h1'=>"Now we'll ask you about income you earned from rentals & royalties",'sub'=>"",'img'=>'rental-properties.svg','next'=>'page15-13','endpage'=>'page15-13','endpage1'=>'page15-13-19-0','endpage2'=>'page15-13-19-1','endpage3'=>'page15-13-19-2','endpage4'=>'page15-13-19-3','endpage5'=>'page15-13-22'),//两个步骤多的
            'farm_income_did_confirm'=>array('h1'=>"Now let's go over the income you earned from your farm",'sub'=>"",'img'=>'farm-income.svg','next'=>'page15-14','endpage'=>'page15-14-35','endpage2'=>'page15-14-34'),//两个步骤多的
            'unemployment_did_confirm'=>array('h1'=>"Now let's go over unemployment benefits or government payments you received",'sub'=>"",'img'=>'unemployment.svg','next'=>'page15-15','endpage'=>'page15-15'),
            'schedule_k1_did_confirm'=>array('h1'=>"Now we'll ask about the K-1's you received",'sub'=>"",'img'=>'schedule-k1.svg','next'=>'page15-16','endpage'=>'page15-16'),
            'withdrew_from_hsa_msa_did_confirm'=>array('h1'=>"Now let's go over withdrawals you made from your Health Savings Account or Medical Savings Account",'sub'=>"",'img'=>'withdrew-from-hsa-msa.svg','next'=>'page15-17','endpage'=>'page15-17-0'),
            'gambling_winnings_did_confirm'=>array('h1'=>"Now let's go over your gambling winnings",'sub'=>"",'img'=>'gambling-winnings.svg','next'=>'page15-18','endpage'=>'page15-18-3'),
            'alimony_received_did_confirm'=>array('h1'=>"Now let's go over the alimony or spousal support you received",'sub'=>"",'img'=>'received-alimony.svg','next'=>'page15-19','endpage'=>'page15-19'),
            'jury_duty_did_confirm'=>array('h1'=>"Now let's go over your jury duty earnings",'sub'=>"",'img'=>'jury-duty.svg','next'=>'page15-20','endpage'=>'page15-20'),
            'sold_main_home_did_confirm'=>array('h1'=>"Now let's go over the sale of your main home",'sub'=>"This does not include the sale of any second homes or investment properties.",'img'=>'sold-main-home.svg','next'=>'page15-21','endpage'=>'page15-21-5','endpage2'=>'page15-21-6'),
            'home_foreclosure_debt_cancellation_did_confirm'=>array('h1'=>"Now let's go over your home foreclosure and debt cancellation",'sub'=>"",'img'=>'moving-truck.svg','next'=>'page15-22','endpage'=>'page15-22-0-1'),
            'withdrew_from_529_did_confirm'=>array('h1'=>"Now we'll go over your 529 Plan and Coverdell ESA withdrawals",'sub'=>"",'img'=>'student-loan-interest.svg','next'=>'page15-23','endpage'=>'page15-23-0'),
            'miscellaneous_income_did_confirm'=>array('h1'=>"You mentioned you received income from other sources. Let's go over that.",'sub'=>"",'img'=>'miscellaneous-income.svg','next'=>'page15-10-1'),
            );
         $this->field_ary = $field_ary;   
         View::assign('field_ary', $field_ary);
         
         $field_ary_3 = array();  //endpage 当next 为field_ary_next有作用
         $field_ary_3=array(
            'mortgage_interest_did_confirm'=>array('h1'=>'Now let\'s go over your home loans','sub'=>'This includes interest and points on your home mortgage, mortgage insurance (PMI or MIP), and interest on refinanced or home equity loans. It does not include rental or business properties.','img'=>'home-loan-interest.svg','next'=>'page18-1','endpage'=>'page18-1'),
            'property_taxes_did_confirm'=>array('h1'=>"Now let's go over your property taxes",'sub'=>"This doesn't include taxes you paid on properties you rented out.",'img'=>'property-taxes.svg','next'=>'page18-2','endpage'=>'page18-2-1'),
            'child_care_credit_did_confirm'=>array('h1'=>"Now we'll ask about child care expenses you incurred",'sub'=>"This tax credit is only relevant for children under the age of 13 or for those who are disabled.",'img'=>'child-care-credit.svg','next'=>'page18-3','endpage'=>'page18-3','endpage1'=>'page18-3-0','endpage2'=>'page18-3-1','endpage3'=>'page18-3-2','endpage4'=>'page18-3-3'),
            'adoption_credit_did_confirm'=>array('h1'=>"Now let's go over your Adoption Credit",'sub'=>'','img'=>'adoption-credit.svg','next'=>'page18-4','endpage'=>'page18-4-3'),
            'alimony_paid_did_confirm'=>array('h1'=>"Now let's go over the alimony you paid",'sub'=>"We'll look into whether you qualify for the alimony paid deduction. Child support payments don't count towards this deduction.",'img'=>'paid-alimony.svg','next'=>'page18-5','endpage'=>'page18-5'),
            'charitable_donations_did_confirm'=>array('h1'=>"Now let's go over your cash and check donations",'sub'=>"This will only count if you donated to non-profit, government, or religious organizations.",'img'=>'charitable-donations.svg','next'=>'page18-6','endpage'=>'page18-6-1'),
            'non_cash_donations_did_confirm'=>array('h1'=>"Now let's go over your non-cash donations",'sub'=>"",'img'=>'non-cash-donations.svg','next'=>'page18-7','endpage'=>'page18-7','endpage2'=>'page18-7-4'),
            'retirement_contributions_did_confirm'=>array('h1'=>"Now let's go over your retirement contributions",'sub'=>"You can make IRA contributions for the 2023 tax year up until April 15, 2024.",'img'=>'traditional-roth-ira-contributions.svg','next'=>'page18-8','endpage'=>'page18-8','endpage1'=>'page18-8-1-tira','endpage2'=>'page18-8-1-rira','endpage3'=>'page18-8-1-sep','endpage4'=>'page18-8-1-k'),
            'college_tuition_did_confirm'=>array('h1'=>"Now let's go over your college tuition and expenses",'sub'=>"We'll determine if you qualify for the American Opportunity Credit, Lifetime Learning Credit, or Tuition and Fees Deduction.",'img'=>'college-tuition.svg','next'=>'page18-9','endpage'=>'page18-9-2'),
            'student_loan_interest_did_confirm'=>array('h1'=>"Now let's go over your student loan payments",'sub'=>"We'll only be able to get you a deduction for this if your income is under $85,000 (or $170,000 if married filing jointly).",'img'=>'student-loan-interest.svg','next'=>'page18-10','endpage'=>'page18-10'),
            'contributed_to_529_did_confirm'=>array('h1'=>"Now we'll ask about your 529 Plan and Coverdell ESA contributions",'sub'=>"",'img'=>'dividends.svg','next'=>'page18-11','endpage'=>'page18-11'),
            'educator_expenses_did_confirm'=>array('h1'=>"Now we'll ask about your educator expenses",'sub'=>"If you taught K-12 and spent money on books or supplies for your students, we might be able to get you a deduction.",'img'=>'educator-expenses.svg','next'=>'page18-12','endpage'=>'page18-12'),
            'major_purchases_did_confirm'=>array('h1'=>"Now we'll ask about the major purchases you made",'sub'=>"If you made large purchases throughout the year (vehicles, boats, aircraft, mobile homes, etc.) we might be able to use those purchases to reduce your tax bill. This will only be relevant if the local sales taxes of those items exceed your local income tax bill, and if we itemize your deductions.",'img'=>'major-purchases.svg','next'=>'page18-13','endpage'=>'page18-13','endpage1'=>'page18-13-0','endpage2'=>'page18-13-1','endpage3'=>'page18-13-2','endpage4'=>'page18-13-3'),
            'car_registration_fees_did_confirm'=>array('h1'=>"Now let's go over your car registration fees",'sub'=>"",'img'=>'car-registration-fees.svg','next'=>'page18-14','endpage'=>'page18-14','endpage1'=>'page18-14-0','endpage2'=>'page18-14-1','endpage3'=>'page18-14-2','endpage4'=>'page18-14-3','endpage5'=>'page18-14-4'),
            'personal_property_taxes_did_confirm'=>array('h1'=>"Now we'll ask about personal property taxes you paid",'sub'=>"You can deduct taxes you paid on the value of personal property, including a boat, motorcycle, RV, or airplane, but not your home or any real estate you own.",'img'=>'social-security.svg','next'=>'page18-15','endpage'=>'page18-15'),
            'casualties_and_thefts_did_confirm'=>array('h1'=>"Now let's go over your casualties and thefts",'sub'=>"",'img'=>'casualties-and-thefts.svg','next'=>'page18-16','endpage'=>'page18-16'),
            'home_energy_credits_did_confirm'=>array('h1'=>"Now let's go over energy-efficient improvements you made to your home",'sub'=>"Note that you cannot take this credit for equipment used to heat a swimming pool or hot tub, or for energy star appliances, low-flow shower heads, or low-flush toilets.",'img'=>'home-energy-credits.svg','next'=>'page18-17','endpage'=>'page18-17-1'),
            'energy_efficient_vehicles_did_confirm'=>array('h1'=>"Now let's go over your energy efficient vehicle",'sub'=>"We'll make sure you get the maximum credit possible for your vehicle.",'img'=>'energy-efficient-vehicles.svg','next'=>'page18-18','endpage'=>'page18-18'),
            'energy_efficient_charging_station_did_confirm'=>array('h1'=>"Now we'll ask about the energy efficient charging station you purchased",'sub'=>"",'img'=>'energy-efficient-charging-station.svg','next'=>'page18-19','endpage'=>'page18-19'),
            'contributions_to_hsa_msa_did_confirm'=>array('h1'=>"Now let's go over your contributions to HSA/MSA",'sub'=>"You can make HSA & MSA contributions for the 2023 tax year up until April 15, 2024.",'img'=>'contributions-to-hsa-msa.svg','next'=>'page18-20','endpage'=>'page18-20-1'),
            'medical_expenses_did_confirm'=>array('h1'=>"Now let's go over your medical and dental expenses",'sub'=>"TThis section is only relevant if you had significant medical and dental expenses (over 7.5% of your adjusted gross income)",'img'=>'medical-expenses.svg','next'=>'page18-21','endpage'=>'page18-21'),
            'affordable_care_act_did_confirm'=>array('h1'=>"Now we'll ask about your health plan so we can check if you qualify for a tax credit",'sub'=>"This will only be relevant if you enrolled in a plan through the Health Insurance Marketplace (Affordable Care Act).",'img'=>'affordable-care-act.svg','next'=>'page18-22','endpage'=>'page18-22'),
            );
         $this->field_ary_3 = $field_ary_3;   
         View::assign('field_ary_3', $field_ary_3);
        //country 
        $country_ary=array('AX'=>'Åland Islands','AL'=>'Albania','DZ'=>'Algeria','AS'=>'American Samoa','AD'=>'Andorra','AO'=>'Angola','AI'=>'Anguilla','AQ'=>'Antarctica','AG'=>'Antigua and Barbuda','AR'=>'Argentina','AM'=>'Armenia','AW'=>'Aruba','AU'=>'Australia','AT'=>'Austria','AZ'=>'Azerbaijan','BS'=>'Bahamas','BH'=>'Bahrain','BD'=>'Bangladesh','BB'=>'Barbados','BY'=>'Belarus','BE'=>'Belgium','BZ'=>'Belize','BJ'=>'Benin','BM'=>'Bermuda','BT'=>'Bhutan','BO'=>'Bolivia, Plurinational State of','BQ'=>'Bonaire, Sint Eustatius and Saba','BA'=>'Bosnia and Herzegovina','BW'=>'Botswana','BV'=>'Bouvet Island','BR'=>'Brazil','IO'=>'British Indian Ocean Territory','BN'=>'Brunei Darussalam','BG'=>'Bulgaria','BF'=>'Burkina Faso','BI'=>'Burundi','KH'=>'Cambodia','CM'=>'Cameroon','CA'=>'Canada','CV'=>'Cape Verde','KY'=>'Cayman Islands','CF'=>'Central African Republic','TD'=>'Chad','CL'=>'Chile','CN'=>'China','CX'=>'Christmas Island','CC'=>'Cocos (Keeling) Islands','CO'=>'Colombia','KM'=>'Comoros','CG'=>'Congo','CD'=>'Congo, the Democratic Republic of the','CK'=>'Cook Islands','CR'=>'Costa Rica','CI'=>"Côte d'Ivoire",'HR'=>'Croatia','CU'=>'Cuba','CW'=>'Curaçao','CY'=>'Cyprus','CZ'=>'Czech Republic','DK'=>'Denmark','DJ'=>'Djibouti','DM'=>'Dominica','DO'=>'Dominican Republic','EC'=>'Ecuador','EG'=>'Egypt','SV'=>'El Salvador','GQ'=>'Equatorial Guinea','ER'=>'Eritrea','EE'=>'Estonia','ET'=>'Ethiopia','FK'=>'Falkland Islands (Malvinas)','FO'=>'Faroe Islands','FJ'=>'Fiji','FI'=>'Finland','FR'=>'France','GF'=>'French Guiana','PF'=>'French Polynesia','TF'=>'French Southern Territories','GA'=>'Gabon','GM'=>'Gambia','GE'=>'Georgia','DE'=>'Germany','GH'=>'Ghana','GI'=>'Gibraltar','GR'=>'Greece','GL'=>'Greenland','GD'=>'Grenada','GP'=>'Guadeloupe','GU'=>'Guam','GT'=>'Guatemala','GG'=>'Guernsey','GN'=>'Guinea','GW'=>'Guinea-Bissau','GY'=>'Guyana','HT'=>'Haiti','HM'=>'Heard Island and McDonald Islands','VA'=>'Holy See (Vatican City State)','HN'=>'Honduras','HK'=>'Hong Kong','HU'=>'Hungary','IS'=>'Iceland','IN'=>'India','ID'=>'Indonesia','IR'=>'Iran, Islamic Republic of','IQ'=>'Iraq','IE'=>'Ireland','IM'=>'Isle of Man','IL'=>'Israel','IT'=>'Italy','JM'=>'Jamaica','JP'=>'Japan','JE'=>'Jersey','JO'=>'Jordan','KZ'=>'Kazakhstan','KE'=>'Kenya','KI'=>'Kiribati','KP'=>"Korea, Democratic People's Republic of",'KR'=>'Korea, Republic of','KW'=>'Kuwait','KG'=>'Kyrgyzstan','LA'=>"Lao People's Democratic Republic",'LV'=>'Latvia','LB'=>'Lebanon','LS'=>'Lesotho','LR'=>'Liberia','LY'=>'Libya','LI'=>'Liechtenstein','LT'=>'Lithuania','LU'=>'Luxembourg','MO'=>'Macao','MK'=>'Macedonia, the Former Yugoslav Republic of','MG'=>'Madagascar','MW'=>'Malawi','MY'=>'Malaysia','MV'=>'Maldives','ML'=>'Mali','MT'=>'Malta','MH'=>'Marshall Islands','MQ'=>'Martinique','MR'=>'Mauritania','MU'=>'Mauritius','YT'=>'Mayotte','MX'=>'Mexico','FM'=>'Micronesia, Federated States of','MD'=>'Moldova, Republic of','MC'=>'Monaco','MN'=>'Mongolia','ME'=>'Montenegro','MS'=>'Montserrat','MA'=>'Morocco','MZ'=>'Mozambique','MM'=>'Myanmar','NA'=>'Namibia','NR'=>'Nauru','NP'=>'Nepal','NL'=>'Netherlands','NC'=>'New Caledonia','NZ'=>'New Zealand','NI'=>'Nicaragua','NE'=>'Niger','NG'=>'Nigeria','NU'=>'Niue','NF'=>'Norfolk Island','MP'=>'Northern Mariana Islands','NO'=>'Norway','OM'=>'Oman','PK'=>'Pakistan','PW'=>'Palau','PS'=>'Palestine, State of','PA'=>'Panama','PG'=>'Papua New Guinea','PY'=>'Paraguay','PE'=>'Peru','PH'=>'Philippines','PN'=>'Pitcairn','PL'=>'Poland','PT'=>'Portugal','PR'=>'Puerto Rico','QA'=>'Qatar','RE'=>'Réunion','RO'=>'Romania','RU'=>'Russian Federation','RW'=>'Rwanda','BL'=>'Saint Barthélemy','SH'=>'Saint Helena, Ascension and Tristan da Cunha','KN'=>'Saint Kitts and Nevis','LC'=>'Saint Lucia','MF'=>'Saint Martin (French part)','PM'=>'Saint Pierre and Miquelon','VC'=>'Saint Vincent and the Grenadines','WS'=>'Samoa','SM'=>'San Marino','ST'=>'Sao Tome and Principe','SA'=>'Saudi Arabia','SN'=>'Senegal','RS'=>'Serbia','SC'=>'Seychelles','SL'=>'Sierra Leone','SG'=>'Singapore','SX'=>'Sint Maarten (Dutch part)','SK'=>'Slovakia','SI'=>'Slovenia','SB'=>'Solomon Islands','SO'=>'Somalia','ZA'=>'South Africa','GS'=>'South Georgia and the South Sandwich Islands','SS'=>'South Sudan','ES'=>'Spain','LK'=>'Sri Lanka','SD'=>'Sudan','SR'=>'Suriname','SJ'=>'Svalbard and Jan Mayen','SZ'=>'Swaziland','SE'=>'Sweden','CH'=>'Switzerland','SY'=>'Syrian Arab Republic','TW'=>'Taiwan, Province of China','TJ'=>'Tajikistan','TZ'=>'Tanzania, United Republic of','TH'=>'Thailand','TL'=>'Timor-Leste','TG'=>'Togo','TK'=>'Tokelau','TO'=>'Tonga','TT'=>'Trinidad and Tobago','TN'=>'Tunisia','TR'=>'Turkey','TM'=>'Turkmenistan','TC'=>'Turks and Caicos Islands','TV'=>'Tuvalu','UG'=>'Uganda','UA'=>'Ukraine','AE'=>'United Arab Emirates','GB'=>'United Kingdom','US'=>'United States','UM'=>'United States Minor Outlying Islands','UY'=>'Uruguay','UZ'=>'Uzbekistan','VU'=>'Vanuatu','VE'=>'Venezuela, Bolivarian Republic of','VN'=>'Viet Nam','VG'=>'Virgin Islands, British','VI'=>'Virgin Islands, U.S.','WF'=>'Wallis and Futuna','EH'=>'Western Sahara','YE'=>'Yemen','ZM'=>'Zambia','ZW'=>'Zimbabwe');
	     $this->country_ary = $country_ary;   
         View::assign('country_ary', $country_ary);
         
         //checklist
         $checkary=array('1095_a'=>'1095-A','1098_e'=>'1098-E','1098_t'=>'1098-T','1099_a'=>'1099-A','1099_b'=>'1099-B','1099_c'=>'1099-C','1099_div'=>'1099-DIV','1099_g'=>'1099-G','1099_h'=>'1099-H','1099_int'=>'1099-INT','1099_k'=>'1099-K','1099_ltc'=>'1099-LTC','1099_misc'=>'1099-MISC','1099_nec'=>'1099-NEC','1099_nec_issued'=>'1099-NEC Issued','1099_oid'=>'1099-OID','1099_patr'=>'1099-PATR','1099_q'=>'1099-Q','1099_r'=>'1099-R','1099_s'=>'1099-S','1099_sa'=>'1099-SA','additional_dependents'=>'Additional Dependents','additional_foreign_accounts'=>'Additional Foreign Accounts','additional_rental_properties'=>'Additional Rental Properties','assets_purchased_business'=>'Assets Purchased (Business)','assets_purchased_farm'=>'Assets Purchased (Farm)','assets_sold_business'=>'Assets Sold (Business)','assets_sold_farm'=>'Assets Sold (Farm)','assets_sold_rental'=>'Assets Sold (Rental)','brokerage_statement'=>'Brokerage Statement','car_registration_renewal_notice'=>'Car Registration','charitable_receipts_cash'=>'Charitable Receipts (Cash/Check)','charitable_receipts_non_cash'=>'Charitable Receipts (Non-Cash)','closing_statement'=>'Closing Statement','consolidated_1099'=>'Consolidated 1099','drivers_license'=>'Driver\'s License','estimated_tax_payments'=>'Estimated Tax Payments','form_1098'=>'Form 1098','form_2439'=>'Form 2439','form_3921'=>'Form 3921','form_3922'=>'Form 3922','form_5498'=>'Form 5498','form_5498_sa'=>'Form 5498-SA','w_3'=>'Form W-3','irs_notice'=>'IRS Notice','k_1'=>'K-1','major_purchases'=>'Major Purchases','old_tax_returns'=>'Old Tax Return','all_other_documents'=>'Other Documents','profit_and_loss_stmt_business'=>'P&amp;L Statement (Business)','profit_and_loss_stmt_farm'=>'P&amp;L Statement (Farm)','profit_and_loss_stmt_rental'=>'P&amp;L Statement (Rental)','payroll_tax_reports'=>'Payroll Tax Report','property_taxes'=>'Property Tax Statement','assets_purchased_rental'=>'Repairs &amp; Improvements (Rental)','ssa_rrb_1099'=>'SSA-1099','sales_by_state_report'=>'Sales by State Report','states_filing_sales_tax_returns'=>'States Filing Sales Tax Returns','organizer'=>'Tax Organizer','uncategorized'=>'Uncategorized','vehicle_expenses'=>'Vehicle Expenses','vehicle_seller_report'=>'Vehicle Seller Report','w2'=>'W-2','w2_g'=>'W-2G');
         View::assign('checkary', $checkary);
         //$upload_pname_ary=array('1095_a'=>'1095-A','1098_e'=>'1098-E','1098_t'=>'1098-T','1099_a'=>'1099-A','1099_b'=>'1099-B','1099_c'=>'1099-C','1099_div'=>'1099-DIV','1099_g'=>'1099-G','1099_h'=>'1099-H','1099_int'=>'1099-INT','1099_k'=>'1099-K','1099_ltc'=>'1099-LTC','1099_misc'=>'page15-13-11-0','1099_nec'=>'1099-NEC','1099_nec_issued'=>'1099-NEC Issued','1099_oid'=>'1099-OID','1099_patr'=>'1099-PATR','1099_q'=>'1099-Q','1099_r'=>'page15-11','1099_s'=>'1099-S','1099_sa'=>'1099-SA','additional_dependents'=>'Additional Dependents','additional_foreign_accounts'=>'Additional Foreign Accounts','additional_rental_properties'=>'Additional Rental Properties','assets_purchased_business'=>'Assets Purchased (Business)','assets_purchased_farm'=>'Assets Purchased (Farm)','assets_sold_business'=>'Assets Sold (Business)','assets_sold_farm'=>'Assets Sold (Farm)','assets_sold_rental'=>'Assets Sold (Rental)','brokerage_statement'=>'Brokerage Statement','car_registration_renewal_notice'=>'Car Registration','charitable_receipts_cash'=>'Charitable Receipts (Cash/Check)','charitable_receipts_non_cash'=>'Charitable Receipts (Non-Cash)','closing_statement'=>'Closing Statement','consolidated_1099'=>'Consolidated 1099','drivers_license'=>'Driver\'s License','estimated_tax_payments'=>'Estimated Tax Payments','form_1098'=>'Form 1098','form_2439'=>'page15-10','form_3921'=>'Form 3921','form_3922'=>'Form 3922','form_5498'=>'Form 5498','form_5498_sa'=>'Form 5498-SA','w_3'=>'Form W-3','irs_notice'=>'IRS Notice','k_1'=>'K-1','major_purchases'=>'Major Purchases','old_tax_returns'=>'Old Tax Return','all_other_documents'=>'Other Documents','profit_and_loss_stmt_business'=>'P&amp;L Statement (Business)','profit_and_loss_stmt_farm'=>'P&amp;L Statement (Farm)','profit_and_loss_stmt_rental'=>'P&amp;L Statement (Rental)','payroll_tax_reports'=>'Payroll Tax Report','property_taxes'=>'Property Tax Statement','assets_purchased_rental'=>'Repairs &amp; Improvements (Rental)','ssa_rrb_1099'=>'page15-12','sales_by_state_report'=>'Sales by State Report','states_filing_sales_tax_returns'=>'States Filing Sales Tax Returns','organizer'=>'Tax Organizer','uncategorized'=>'Uncategorized','vehicle_expenses'=>'Vehicle Expenses','vehicle_seller_report'=>'Vehicle Seller Report','w2'=>'page15-1','w2_g'=>'W-2G');
         $upload_pname_ary=array('page15-1'=>'w2','page15-12'=>'ssa_rrb_1099','page15-10'=>'form_2439','page15-11'=>'1099_r','page15-13-11-0'=>'1099_misc','page15-13-11-1'=>'1099_misc','page15-13-11-2'=>'1099_misc','page15-13-11-3'=>'1099_misc','page15-13-11-4'=>'1099_misc','page15-13-15-0'=>'1098','page15-13-15-1'=>'1098','page15-13-15-2'=>'1098','page15-13-15-3'=>'1098','page15-13-15-4'=>'1098','page15-13-17-0'=>'assets_purchased_business','page15-13-17-1'=>'assets_purchased_business','page15-13-17-2'=>'assets_purchased_business','page15-13-17-3'=>'assets_purchased_business','page15-13-17-4'=>'assets_purchased_business','page15-13-19-0'=>'assets_sold_property','page15-13-19-1'=>'assets_sold_property','page15-13-19-2'=>'assets_sold_property','page15-13-19-3'=>'assets_sold_property','page15-13-20'=>'additional_rental_properties','page15-13-21'=>'profit_and_loss_stmt_rental','page15-13-22'=>'1099_misc','page15-13-23'=>'1098','page15-13-24'=>'assets_purchased_rental','page15-13-25'=>'Assets-Sold-Additional-Properties','page15-13-7-0'=>'Statements','page15-13-7-1'=>'Statements','page15-13-7-4'=>'Statements','page15-14-13-g'=>'1099_g','page15-14-13-k'=>'1099_k','page15-14-13-misc'=>'1099_misc','page15-14-13-nec'=>'1099_nec','page15-14-13-patr'=>'1099_patr','page15-14-31'=>'vehicle_expenses','page15-14-33'=>'assets_purchased_farm','page15-14-35'=>'assets_sold_farm','page15-14-6'=>'P&L and any financial statements','page15-14-8'=>'Spreadsheets','page15-15'=>'1099_g','page15-16'=>'k_1','page15-17-3'=>'all_other_documents','page15-17'=>'1099_sa','page15-18'=>'w2_g','page15-2-13-1'=>'assets_purchased_business','page15-2-14-1'=>'assets_sold_business','page15-2-15-20'=>'assets_purchased_business','page15-2-15-22'=>'assets_purchased_business','page15-2-15-25'=>'all_other_documents','page15-2-15-3-1-2-k'=>'1099_k','page15-2-15-3-1-2-misc'=>'1099_misc','page15-2-15-3-1-2-nec'=>'1099_nec','page15-2-15-3-1-4'=>'assets_purchased_business','page15-2-15-4-1'=>'w_3','page15-2-15-4-2'=>'payroll_tax_reports','page15-2-15-9'=>'1099_nec','page15-2-3'=>'profit_and_loss_stmt_business','page15-2-4-k'=>'1099_k','page15-2-4-misc'=>'1099_misc','page15-2-4-nec'=>'1099_nec','page15-2-7-1'=>'w_3','page15-2-7-2'=>'payroll_tax_reports','page15-2-8-3'=>'1099_nec','page15-21-1'=>'closing_statement','page15-21'=>'1099_s','page15-22-0-1'=>'1099_c','page15-22-0'=>'1099_a','page15-23'=>'1099_q','page15-24-2'=>'all_other_documents','page15-3-1'=>'consolidated_1099','page15-3-2'=>'1099_b','page15-3-3'=>'brokerage_statement','page15-3-7'=>'all_other_documents','page15-4'=>'1099_int','page15-5-3'=>'all_other_documents','page15-5'=>'1099_div','page15-6-0'=>'closing_statement','page15-6-3'=>'all_other_documents','page15-6'=>'1099_s','page15-7-3'=>'all_other_documents','page15-7'=>'form_3921','page15-8-6'=>'additional_foreign_accounts','page15-9'=>'1099_oid','page18-1'=>'form_1098','page18-10'=>'1098_e','page18-10'=>'1098_e','page18-14-4'=>'car_registration_renewal_notice','page18-18'=>'vehicle_seller_report','page18-2-1'=>'estimated_tax_payments','page18-20-1'=>'form_5498_sa','page18-22'=>'1095_a','page18-6-1'=>'charitable_receipts_cash','page18-7-3'=>'charitable_receipts_non_cash','page18-7-4'=>'charitable_receipts_non_cash','page18-9'=>'1098_t','page19-11'=>'irs_notice','page19-12'=>'all_other_documents','page19-14'=>'all_other_documents','page19-6'=>'estimated_tax_payments','page19-9'=>'old_tax_returns');
         $this->upload_pname_ary=$upload_pname_ary;
         View::assign('upload_pname_ary', $upload_pname_ary);
         $this->filecounter=0;
         View::assign('filecounter', $filecounter);
         //assets you sold or disposed of for this property
        $this->public_assign();
    }
    //公共赋值操作
    public function public_assign()
    {
        $tpshop_config = array();
        $tp_config = Db::name('config')->select();
        if($tp_config){
            foreach($tp_config as $k => $v) {
                if($v['name'] == 'hot_keywords'){
                    $value = explode('|', $v['value']);
                }else{
                    $value=$v['value'];
                }
                $tpshop_config[$v['inc_type'].'_'.$v['name']] = $value;
            }
        }
       
        $this->app_id=$tpshop_config['basic_appid'];
        $this->app_sercret=$tpshop_config['basic_appsecret'];
        $this->page_size = config('app.pagesize');
        View::assign('tpshop_config', $tpshop_config);
    }
    // 网页授权登录获取 openid
    public function get_openid(){
        $code=input('code');
        if (empty($code)){
            //跳转至授权页
            $baseUrl = urlencode($this->get_url());
            $url = $this->__CreateOauthUrlForCode($baseUrl); // 获取 code地址
            $this->redirect($url);
        }else{
            //上面获取到code后这里跳转回来
            $data = $this->getOpenidFromMp($code);//获取网页授权access_token和用户openid
            $data2 = $this->GetUserInfo($data['access_token'],$data['openid']);//获取微信用户信息
            $openid=$data2['openid'];
            $unionid=$data2['unionid'];
            $third_user=new ThirdUser();
            //是否已登录过
            if ($unionid){
                $where['unionid']=$unionid;
                $where['type']=2;
                $find=$third_user->where($where)->find();
            }else{
                $where['openid']=$openid;
                $where['type']=2;
                $find=$third_user->where($where)->find();
            }
            if ($find){
                $update['uid']         =        $this->user_id;
                $update['avatar']      =        $data2['headimgurl'];
                $update['province']    =        $data2['province'];
                $update['city']        =        $data2['city'];
                $update['nick_name']   =        $data2['nickname'];
                $update['gender']      =        $data2['sex']==1 ? "男" : "女";
                $update['openid']      =        $openid;
                $update['unionid']     =        $unionid;
                $find->save($update);
            }else{
                //插入到第三方用户信息表
                $map['uid']            =       $this->user_id;
                $map['type']           =       2;
                $map['avatar']         =       $data2['headimgurl'];
                $map['province']       =       $data2['province'];
                $map['city']           =       $data2['city'];
                $map['nick_name']      =       $data2['nickname'];
                $map['gender']         =       $data2['sex']==1 ? "男" : "女";
                $map['openid']         =       $openid;
                $map['unionid']        =       $unionid;
                $third_user->save($map);
            }
            session('openid',$openid);
            Db::name('users')->where('user_id',$this->user_id)->update(['openid'=>$openid]);
            return true;
        }
    }
    //通过access_token openid 获取UserInfo
    public function GetUserInfo($access_token,$openid)
    {
        // 获取用户 信息
        $url = $this->__CreateOauthUrlForUserinfo($access_token,$openid);
        $ch = curl_init();//初始化curl
        curl_setopt($ch, CURLOPT_TIMEOUT, 300);//设置超时
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,FALSE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        $res = curl_exec($ch);//运行curl，结果以jason形式返回
        $data = json_decode($res,true);
        curl_close($ch);
        return $data;
    }
    //构造获取拉取用户信息(需scope为 snsapi_userinfo)的url地址
    private function __CreateOauthUrlForUserinfo($access_token,$openid)
    {
        $urlObj["access_token"] = $access_token;
        $urlObj["openid"] = $openid;
        $urlObj["lang"] = 'zh_CN';
        $bizString = $this->ToUrlParams($urlObj);
        return "https://api.weixin.qq.com/sns/userinfo?".$bizString;
    }
    //构造获取code的url连接
    private function __CreateOauthUrlForCode($redirectUrl)
    {
        $urlObj["appid"] = $this->wx_user['appid'];
        $urlObj["redirect_uri"] = "$redirectUrl";
        $urlObj["response_type"] = "code";
        $urlObj["scope"] = "snsapi_userinfo";
        $urlObj["state"] = "STATE"."#wechat_redirect";
        $bizString = $this->ToUrlParams($urlObj);
        return "https://open.weixin.qq.com/connect/oauth2/authorize?".$bizString;
    }
    //获取当前的url 地址
    private function get_url() {
        $sys_protocal = isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443' ? 'https://' : 'http://';
        $php_self = $_SERVER['PHP_SELF'] ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME'];
        $path_info = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '';
        $relate_url = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : $php_self.(isset($_SERVER['QUERY_STRING']) ? '?'.$_SERVER['QUERY_STRING'] : $path_info);
        return $sys_protocal.(isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '').$relate_url;
    }
    //返回已经拼接好的字符串
    private function ToUrlParams($urlObj)
    {
        $buff = "";
        foreach ($urlObj as $k => $v)
        {
            if($k != "sign"){
                $buff .= $k . "=" . $v . "&";
            }
        }
        $buff = trim($buff, "&");
        return $buff;
    }
    //通过code获取openid和access_token
    public function GetOpenidFromMp($code)
    {
        //通过code获取网页授权access_token 和 openid 。网页授权access_token是一次性的，而基础支持的access_token的是有时间限制的：7200s。
        //1、微信网页授权是通过OAuth2.0机制实现的，在用户授权给公众号后，公众号可以获取到一个网页授权特有的接口调用凭证（网页授权access_token），通过网页授权access_token可以进行授权后接口调用，如获取用户基本信息；
        //2、其他微信接口，需要通过基础支持中的“获取access_token”接口来获取到的普通access_token调用。
        $url = $this->__CreateOauthUrlForOpenid($code);
        $ch = curl_init();//初始化curl
        curl_setopt($ch, CURLOPT_TIMEOUT, 300);//设置超时
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,FALSE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        $res = curl_exec($ch);//运行curl，结果以jason形式返回
        $data = json_decode($res,true);
        curl_close($ch);
        return $data;
    }
    //构造获取open和access_toke的url地址
    private function __CreateOauthUrlForOpenid($code)
    {
        $urlObj["appid"] = $this->wx_user['appid'];
        $urlObj["secret"] = $this->wx_user['appsecret'];
        $urlObj["code"] = $code;
        $urlObj["grant_type"] = "authorization_code";
        $bizString = $this->ToUrlParams($urlObj);
        return "https://api.weixin.qq.com/sns/oauth2/access_token?".$bizString;
    }
    protected function ajaxReturn($data){
        exit(json_encode($data));
    }
}