<?php
/** Integration-like checks for JSON persistence, DOM translation and protected form values. */
define('ABSPATH', '/test/');
$options=[];$query=['yea_lang'=>'de'];$hooks=[];$root=sys_get_temp_dir().'/yea-i18n-test-'.bin2hex(random_bytes(5));
function add_action($name,$cb,...$rest){$GLOBALS['hooks'][$name][]=$cb;}function add_filter(...$args){add_action(...$args);}function register_activation_hook(...$args){}function register_deactivation_hook(...$args){}
function wp_next_scheduled(...$args){return time()+100;}function wp_schedule_event(...$args){}function wp_schedule_single_event(...$args){}
function get_option($key,$default=false){return $GLOBALS['options'][$key]??$default;}function update_option($key,$value,...$rest){$GLOBALS['options'][$key]=$value;}function delete_option($key){unset($GLOBALS['options'][$key]);}
function wp_upload_dir(){return ['basedir'=>$GLOBALS['root']];}function wp_mkdir_p($path){return is_dir($path)||mkdir($path,0777,true);}function wp_json_encode($v,$flags=0){return json_encode($v,$flags);}
function get_query_var($key,$default=''){return $GLOBALS['query'][$key]??$default;}function home_url($path='/'){return 'https://yea.test'.$path;}function wp_parse_url($url){return parse_url($url);}function wp_unslash($v){return stripslashes($v);}
function check($condition,$message){if(!$condition)throw new RuntimeException($message);}
require __DIR__.'/../bundled-plugins/yea-i18n/yea-i18n.php';
wp_mkdir_p($root.'/yea-i18n');
$catalog=[YEA_JSON_Languages::key('Hello')=>'Hallo $1',YEA_JSON_Languages::key('School')=>'Schule',YEA_JSON_Languages::key('Find a project')=>'Projekt finden'];
file_put_contents($root.'/yea-i18n/de.json',json_encode($catalog));
check(yea_i18n_t('Hello')==='Hallo $1','Saved JSON translation');
check(yea_i18n_t('Unknown')==='Unknown','Unknown strings retain source');
check(yea_i18n_t('Welcome {{project}}',['project'=>'<private>'])==='Welcome <private>','Interpolation preserves user data as text');
check(YEA_JSON_Languages::url('https://yea.test/en/awards/?a=1#join','de')==='https://yea.test/de/awards/?a=1#join','Language switch retains page, query and anchor');
foreach(['https://other.test/about/','mailto:hello@yea.test','#section','https://yea.test/wp-json/yea/v1/award-delivery/status','https://yea.test/wp-content/a.pdf'] as $url){check(YEA_JSON_Languages::url($url,'de')===$url,'Protected URL '.$url);}
$rules=YEA_JSON_Languages::rules(['^news/([^/]+)/?$'=>'index.php?name=$matches[1]']);
check($rules['^(sq|en|de)/news/([^/]+)/?$']==='index.php?name=$matches[2]&yea_lang=$matches[1]','Rewrite capture groups shift correctly');
$html='<!doctype html><html><head><title>Hello</title><script>const label="Hello";</script></head><body><p>Hello</p><pre>Hello</pre><div translate="no">Hello</div><select name="school"><option>School</option><option value="original">School</option></select><input name="code" value="Hello" placeholder="Find a project"><textarea>Hello</textarea><a href="https://yea.test/awards/">Hello</a><span class="award-selected-files">Hello</span></body></html>';
$output=YEA_JSON_Languages::render($html);
$dom=new DOMDocument();@$dom->loadHTML($output);$xpath=new DOMXPath($dom);
check($xpath->query('//p')->item(0)->textContent==='Hallo $1','Literal dollar sign is not a regex replacement');
check($xpath->query('//option')->item(0)->getAttribute('value')==='School','Implicit option value remains unchanged');
check($xpath->query('//option')->item(1)->getAttribute('value')==='original','Explicit option value remains unchanged');
check($xpath->query('//option')->item(0)->textContent==='Schule','Visible option label translated');
check($xpath->query('//input')->item(0)->getAttribute('value')==='Hello','Registration code untouched');
check($xpath->query('//textarea')->item(0)->textContent==='Hello','Textarea untouched');
check($xpath->query('//pre')->item(0)->textContent==='Hello','Code block untouched');
check($xpath->query('//span')->item(0)->textContent==='Hello','Selected filename untouched');
check($xpath->query('//div')->item(0)->textContent==='Hello','Explicit translation exclusion');
check(str_contains($output,'const label="Hello";'),'Inline JavaScript untouched');
check(!str_contains($output,'<?xml'),'Temporary encoding declaration removed');
// Provider batches must validate before persistence; manual entries are not requested again.
function add_option($key,$value,...$rest){if(isset($GLOBALS['options'][$key]))return false;$GLOBALS['options'][$key]=$value;return true;}
function sanitize_key($key){return preg_replace('/[^a-z0-9_-]/','',strtolower($key));}
class WP_Error {public function __construct(public $code,public $message,public $data=[]){}public function get_error_code(){return $this->code;}}
function is_wp_error($value){return $value instanceof WP_Error;}
class FakeGooglePrompt {
    public function __construct(private $prompt){}
    public function using_provider($provider){check($provider==='google','Existing Google provider selected');return $this;}
    public function generate_text(){
        $batch=json_decode(substr($this->prompt,strpos($this->prompt,'Input: ')+7),true);
        $GLOBALS['requested']=$batch;
        return $GLOBALS['bad_batch']?'{}':json_encode(array_map(fn($v)=>'Translated '.$v,$batch));
    }
}
function wp_ai_client_prompt($prompt){return new FakeGooglePrompt($prompt);}
$options['yea_i18n_sources']=[YEA_JSON_Languages::key('Unique {{project}}')=>'Unique {{project}}'];
$GLOBALS['bad_batch']=true;
check(is_wp_error(YEA_JSON_Languages::tick()),'Partial/invalid provider output rejected');
check(!isset($options['yea_i18n_lock']),'Lock released after provider failure');
check(!is_file($root.'/yea-i18n/sq.json'),'Invalid batch never saved');
$GLOBALS['bad_batch']=false;
$result=YEA_JSON_Languages::tick();
check($result['translated']===1,'Validated provider batch saved');
check(YEA_JSON_Languages::catalog('sq')[YEA_JSON_Languages::key('Unique {{project}}')]==='Translated Unique {{project}}','Placeholders preserved in saved JSON');
check(is_file($root.'/yea-i18n/sq.json'),'Translations persist to JSON');
$options['yea_i18n_lock']=time();
check(YEA_JSON_Languages::tick()['busy']===true,'Concurrent requests do not call provider twice');
foreach(glob($root.'/yea-i18n/*') as $file){unlink($file);}rmdir($root.'/yea-i18n');rmdir($root);
echo "JSON language checks passed.\n";
