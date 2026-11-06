<?php
/** Isolated unit checks: no WordPress database, HTTP, email, or real uploads. */
namespace WPC_WPCF7_NTN { class WPCF7_Notion_Service { static function get_instance(){return new self;} function get_api_key(){return 'test-key';} } }
namespace {
define('ABSPATH', '/test/'); define('MINUTE_IN_SECONDS',60); define('HOUR_IN_SECONDS',3600); define('DAY_IN_SECONDS',86400);
$hooks=[];$options=['yea_award_registration_form_id'=>4138,'yea_award_delivery_page_id'=>2518];$transients=[];$http=[];$replies=[];
function add_action($name,$callback,...$rest){$GLOBALS['hooks'][$name][]=$callback;} function add_filter($name,$callback,...$rest){add_action($name,$callback);} function add_shortcode(...$args){}
function get_option($key,$default=false){return $GLOBALS['options'][$key]??$default;} function update_option($key,$value,...$args){$GLOBALS['options'][$key]=$value;return true;} function add_option($key,$value,...$args){if(isset($GLOBALS['options'][$key]))return false;$GLOBALS['options'][$key]=$value;return true;} function delete_option($key){unset($GLOBALS['options'][$key]);}
function get_transient($key){return $GLOBALS['transients'][$key]??false;} function set_transient($key,$value,$ttl){$GLOBALS['transients'][$key]=$value;} function wp_salt($type){return 'test-salt';}
function is_wp_error($value){return $value instanceof WP_Error;} class WP_Error {public $code,$message,$data;function __construct($code,$message,$data=[]){$this->code=$code;$this->message=$message;$this->data=$data;}}
class WP_REST_Response {public $data,$headers=[];function __construct($data){$this->data=$data;} function header($name,$value){$this->headers[$name]=$value;}}
function sanitize_text_field($value){return strip_tags($value);}
function wp_json_encode($value){return json_encode($value);} function wp_remote_request($url,$args){$GLOBALS['http'][]=[$url,$args];return array_shift($GLOBALS['replies']);} function wp_remote_retrieve_body($r){return $r['body'];} function wp_remote_retrieve_response_code($r){return $r['status'];}
function home_url($path='/'){return 'https://example.test'.$path;} function get_permalink($id){return 'https://example.test/delivery/';}
class Request {public $params,$headers;function __construct($params=[],$headers=[]){$this->params=$params;$this->headers=$headers;} function get_param($key){return $this->params[$key]??null;}function get_header($key){return $this->headers[$key]??'';}function get_file_params(){return $this->params['files']??[];}}
class FakeForm {function id(){return 4138;}function prop($name){return ['enable_database'=>true,'database_selected'=>'database-id','mapping'=>['registration-code'=>'code-property']];}}
function wpcf7_contact_form($id){return new FakeForm;} class WPCF7_Submission {static function get_instance(){return new self;}function get_contact_form(){return new FakeForm;}}
function check($value,$message){if(!$value)throw new \RuntimeException($message);}
function reply($data,$status=200){$GLOBALS['replies'][]=['body'=>json_encode($data),'status'=>$status];}
require __DIR__.'/../inc/award-delivery.php';
$codes=[];for($i=0;$i<1000;$i++){$code=yea_award_new_code();check(yea_award_normalize_code(strtolower(str_replace('-',' ',$code)))===$code,'Code normalization');$codes[$code]=true;}check(count($codes)===1000,'Code collision');check(yea_award_normalize_code('not-a-code')==='','Malformed code rejected');
$posted=$hooks['wpcf7_posted_data'][0](['project-title'=>'A project','registration-code'=>'forged']);check($posted['registration-code']!=='forged','Registration code must be server generated');
$response=$hooks['wpcf7_feedback_response'][0](['status'=>'aborted']);check(!isset($response['registration_code']),'No receipt without a saved Notion row');
$hooks['add-on-cf7-for-notion/notion-api/response'][0]('pages',(object)['id'=>'page-id'],(object)[]);
$response=$hooks['wpcf7_feedback_response'][0](['status'=>'mail_failed']);check($response['registration_code']===$posted['registration-code'],'Saved code survives email failure');check($response['email_confirmation']===false,'No false email success');
check(yea_award_unlock(new Request(['code'=>$code]))->data['status']===423,'Closed delivery cannot unlock');
check(yea_award_delivery_permission(new Request())->data['status']===423,'Closed delivery rejects upload even before token checks');
$options['yea_award_delivery_open']=true;
check(yea_award_delivery_permission(new Request())->data['status']===401,'No upload without a valid session');
reply(['results'=>[]]);check(is_wp_error(yea_award_find_registration($code)),'Unknown code rejected');
$page=['id'=>'page-id','properties'=>['Project'=>['type'=>'title','title'=>[['plain_text'=>'A project']]],'Email'=>['type'=>'email','email'=>'private@example.test']]];
reply(['results'=>[$page]]);$record=yea_award_find_registration($code);check(array_keys($record)===['page_id','project'],'Only project identity is exposed');check($record['project']==='A project','Project name returned');
reply(['results'=>[$page,$page]]);check(is_wp_error(yea_award_find_registration($code)),'Ambiguous code rejected');
reply(['results'=>[$page]]);$unlocked=yea_award_unlock(new Request(['code'=>$code]));check(strlen($unlocked->data['token'])===64,'Session token entropy');check($unlocked->headers['Cache-Control']==='no-store, private','Do not cache tokens');
$request=new Request([],['x-yea-delivery-token'=>$unlocked->data['token']]);check(yea_award_delivery_permission($request)===true,'Valid session permits delivery');
$key=yea_award_session_key($request);$transients[$key]['complete']=true;$done=yea_award_receive_files($request);check($done->data['received'],'Completed token returns its receipt without re-uploading');
check(is_wp_error(yea_award_validate_delivery_files([])),'No empty file uploads');
$options['yea_award_delivery_open']=false;check(is_wp_error(yea_award_delivery_permission($request)),'Closing delivery revokes previously unlocked access');
$session=[];$file=['name'=>'Project.pdf','mime'=>'application/pdf','path'=>tempnam(sys_get_temp_dir(),'yea-test-'),'hash'=>'fixture'];file_put_contents($file['path'],'%PDF-1.4 test');
reply(['id'=>'upload-id']);reply(['status'=>'pending']);reply(['id'=>'upload-id','status'=>'uploaded']);
$stage=yea_award_upload_to_notion($file,$session,'stage-key');check($stage['uploaded'],'Native file upload completed');$count=count($http);$again=yea_award_upload_to_notion($file,$session,'stage-key');check(count($http)===$count,'Retry reuses completed native upload');unlink($file['path']);
reply(['code'=>'unauthorized'],401);check(is_wp_error(yea_award_notion_request('pages/test')),'Remote errors cannot become success');
echo "PASS receipt generation, Notion confirmation, closed gates, code lookup, token authorization, native uploads and retry reuse\n";
}
