<?php 
 /**
* Change Admin Domains
* 
*/
return ChangeAdminUrlPlugin::bootstrap();

class ChangeAdminUrlPlugin {
     static $instance;
     static public function bootstrap() {
          null === self::$instance && self::$instance = new self();
          return self::$instance;
     }
     /*
     private function setCookiePath() {
      defined('SITECOOKIEPATH') || define('SITECOOKIEPATH', preg_replace('|https?://[^/]+|i', '', get_option('siteurl') . '/' ) );
      defined('ADMIN_COOKIE_PATH') || define('ADMIN_COOKIE_PATH', SITECOOKIEPATH . $this->renameTo);
     }
     */
     public function __construct() {     
          //add_action( 'wp_enqueue_scripts', array($this, 'wp_enqueue_scripts'), 0 );
          $this->wp_enqueue_scripts();
          add_action('init', array($this, 'init_early'), 1) ;
          add_action('init', array($this, 'init_late'), 999) ;
          
     }
     public function init_early() {
          $page_viewed = basename($_SERVER['REQUEST_URI']);
          if (isCustomAdmin($_SERVER['HTTP_HOST'])) {

               if ($_SERVER['HTTP_HOST_MAIN'] == $_SERVER["HTTP_HOST_ORG"] &&
                         (is_user_logged_in() || is_admin() || $page_viewed == "wp-login.php")) 
               {
                    header("X-ADMIN-REDIR: yes");
                    wp_redirect( "https://".getAdminDomain($_SERVER['HTTP_HOST']).$_SERVER['REQUEST_URI'] );
                    die;
               }
          }

          if (class_exists('HMW_Classes_ObjController')) {
               $hmw_cookies = HMW_Classes_ObjController::$instances["HMW_Models_Cookies"];
               remove_action('redirect_post_location', array($hmw_cookies, 'setPostCookie'), PHP_INT_MAX);
               remove_action('set_auth_cookie', array($hmw_cookies, 'setAuthCookie'), PHP_INT_MAX);
               remove_action('clear_auth_cookie', array($hmw_cookies, 'setCleanCookie'), PHP_INT_MAX);
               remove_action('set_logged_in_cookie', array($hmw_cookies, 'setLoginCookie'), PHP_INT_MAX);
               
               add_action('login_init', array($this, 'hmw_login_init'), 0);
          }


          if ((is_user_logged_in() && is_admin() && $page_viewed == "customize.php") //|| is_customize_preview()
                    ) {
                         
               header("X-ADMIN-CHANGEDHOME: yes");
               add_filter( 'home_url', array($this, 'customizer_home_url'), 999, 4 );
          }
     }
     public function init_late() {

          if (isCustomAdmin($_SERVER['HTTP_HOST'])) {               
               add_filter("script_loader_tag", array($this, 'script_loader_tag'), 999, 3);
               add_filter("style_loader_tag", array($this, 'style_loader_tag'), 999, 4);      
               remove_action( 'admin_head', 'wp_admin_canonical_url' );
               add_action( 'template_redirect', array($this, 'template_redirect') );
          }
          

          add_filter( 'login_url', array($this, 'login_url'), 999, 3 );
          add_filter( 'rest_url', array($this, 'rest_url'), 999, 4 );
          add_filter( 'site_url', array($this, 'site_url'), 999, 4 );
          add_filter('admin_url', array($this, 'admin_url'), 999, 3);
          add_filter('network_admin_url', array($this, 'network_admin_url'), 999, 3);//Added by Mark Figueredo, <http://gruvii.com/> 
          
          header("X-ADMIN-INIT: yes");
          //wp_add_inline_script( 'admindomain', 'var _currentDomain="' . $_SERVER["HTTP_HOST_ORG"] . '"' );
     }

     public function wp_enqueue_scripts() {
          if ($_SERVER['HTTP_HOST_MAIN'] != $_SERVER["HTTP_HOST_ORG"]) {
               header("X-ADMIN-ADDEDJS: yes");
               wp_register_script( 'admindomain', '' );
               wp_enqueue_script( 'admindomain' );
               wp_add_inline_script( 'admindomain', 'window._currentDomain="' . $_SERVER["HTTP_HOST_MAIN"] . '";console.log(_currentDomain);' , "before");
          }
       }


     public function customizer_home_url( $url, $path, $orig_scheme, $blog_id ) {
          return $this->updateUrl($url, $blog_id);
     }

     public function hmw_login_init() {
          require_once('adminsubdomain/'.'HMW_Cookies.php');
          HMW_Classes_ObjController::$instances["HMW_Models_Cookies"] = new HMW_Models_Cookies_NONDOMAIN();
     }

     public function template_redirect() {
               
          if ( !is_user_logged_in() && !is_admin() && $_SERVER['HTTP_HOST_MAIN'] != $_SERVER["HTTP_HOST_ORG"]) 
          {
              nocache_headers();
              header("X-ADMIN-REDIRTEMPLATE: yes");
              wp_redirect(  get_admin_url( null, null, "https" ));
              die;
          }
      }


     public function script_loader_tag($tag, $handle, $src  ) {
          if ( is_admin() )  {
               return $this->updateUrls($tag);
          }

          return $tag;
     }
     public function style_loader_tag( $html, $handle, $href, $media ){
          if ( is_admin() ) {
               return $this->updateUrls($html);
          }

          return $html;
     }

     public function rest_url($url, $path, $blog_id, $scheme ) {
          $new = $this->updateUrl($url, $blog_id);
          return $new;
     }
     public function site_url($url, $path, $scheme, $blog_id ) {
          if (strpos($path, 'login') !== false ||
              strpos($path, 'wp-admin') !== false) {
               return $this->updateUrl($url, $blog_id);
          }
          return $url;
     }
     
     public function login_url($url, $redirect, $force_reauth) {
          return $this->updateUrl($url, null);
     }
     
     public function admin_url($url, $path, $blog_id) {
          return $this->updateUrl($url, $blog_id);
     }
         
     public function network_admin_url($url, $path) {
          $scheme = 'admin';
          $find = network_site_url('', $scheme);
          $replace = $this->replaceDomain($find);
          if ($find != $replace)
          (    0 === strpos($url, $find)) && $url = $replace.substr($url, strlen($find)) ;
          return $url;
     }

     private function updateUrls($source) {
          $scheme = 'admin';
          $find = get_site_url(null, '', $scheme);
          $replace = $this->replaceDomain($find);
          if ($find != $replace)
               return str_replace($find, $replace, $source); 
          return $source;
     }

     private function updateUrl($url, $blog_id) {
          $scheme = 'admin';
          $find = get_site_url($blog_id, '', $scheme);
          $replace = $this->replaceDomain($find);
          if ($find != $replace)
               (0 === strpos($url, $find)) && $url = $replace.substr($url, strlen($find));
          return $url;
     }
     
     private function replaceDomain($url) {
          $domain = parse_url($url, PHP_URL_HOST);
          $url = str_replace("//" . $domain,  "//" . getAdminDomain($domain), $url);
          return $url;
     }
}