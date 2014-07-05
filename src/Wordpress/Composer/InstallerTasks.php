<?php namespace Wordpress\Composer;
/**
 * Wordpress Composer Installer
 *
 * @package Wordpress/Composer;
 * @subpackage Installer
 * @category Installer
 * @author Brian Greenacre <bgreenacre42@gmail.com>
 * @version $id$
 */

use Composer\Script\Event;

/**
 * Provides a number of tasks that can be done
 * after a composer event is fired.
 *
 * @package Wordpress/Composer
 * @subpackage Tasks
 * @category Tasks
 */
class InstallerTasks {

    /**
     * Array of default config values.
     *
     * @access public
     * @var array
     */
    public static $params = array(
        'wordpress_wp_config'    => array(
			'wp_content_dir'	 => 'wordpress/wp-content',
			'wp_core_dir'        => 'wordpress/core',
			'wp_title'			 => 'another blog on wordpress',
			'wp_admin'			 => 'admin',
			'wp_password'		 => 'admin',
			'wp_admin_email'	 => 'admin@admin.pl',
			'wp_is_public'		 => true,
			'vendor-dir'         => null,		
            'site_url'           => 'http://localhost',
            'db_host'            => 'localhost',
            'db_name'            => 'wordpress',
            'db_user'            => 'root',
            'db_pass'            => 'root',
            'db_charset'         => 'utf8',
            'db_collate'         => '',
            'db_prefix'          => 'wp_',
            'generate_auth_keys' => true,
            'wp_lang'            => '',
            'wp_debug'           => false,
            'disallow_file_edit' => false,
			'wp_uploads_dir'	 => null,
            'wp_post_revisions'  => false,
            'wp_cache' 			 => false,
            'autosave_interval'  => 360,
            'cache_exp_time' 	 => 0
        )
    );
	
	private static function array_extend($a, $b) {
		foreach($b as $k=>$v) {
			if( is_array($v) ) {
				if( !isset($a[$k]) ) {
					$a[$k] = $v;
				} else {
					$a[$k] = InstallerTasks::array_extend($a[$k], $v);
				}
			} else {
				$a[$k] = $v;
			}
		}
		return $a;
	}
	
	private static function getExtra(Event $event){
        // Get the params from the class and merge
        // any defined inside composer.json file.
        //$params = self::$params;
        $params = self::$params;
		$extra  = $event->getComposer()->getPackage()->getExtra();
		
        if (is_array($extra))
        {
			$params = InstallerTasks::array_extend($params, $extra);
        }
		return $params;
	}
    /**
     * Generate a wp-config.php and place it into
     * the wordpress core folder.
     *
     * @access public
     * @param  Event  $event Event object
     * @return void
     */
    public static function wpConfig(Event $event)
    {
		$params = self::getExtra($event);

        // Generate the auth salts or use default values.
        if (true === $params['wordpress_wp_config']['generate_auth_keys'])
        {
            $authKeys = file_get_contents('https://api.wordpress.org/secret-key/1.1/salt/');
        }
        else
        {
            $authKeys = "define('AUTH_KEY',   'put your unique phrase here');\n"
                . "define('SECURE_AUTH_KEY',  'put your unique phrase here');\n"
                . "define('LOGGED_IN_KEY',    'put your unique phrase here');\n"
                . "define('NONCE_KEY',        'put your unique phrase here');\n"
                . "define('AUTH_SALT',        'put your unique phrase here');\n"
                . "define('SECURE_AUTH_SALT', 'put your unique phrase here');\n"
                . "define('LOGGED_IN_SALT',   'put your unique phrase here');\n"
                . "define('NONCE_SALT',       'put your unique phrase here');\n";
        }


        $wpConfigParams = array(
            ':wp_content_dir'          => $params['wordpress_wp_config']['wp_content_dir'],
			':wp_core_dir'             => $params['wordpress_wp_config']['wp_core_dir'],
            ':site_url'                => $params['wordpress_wp_config']['site_url'],
            ':db_host'                 => $params['wordpress_wp_config']['db_host'],
            ':db_name'                 => $params['wordpress_wp_config']['db_name'],
            ':db_user'                 => $params['wordpress_wp_config']['db_user'],
            ':db_pass'                 => $params['wordpress_wp_config']['db_pass'],
            ':db_charset'              => $params['wordpress_wp_config']['db_charset'],
            ':db_collate'              => $params['wordpress_wp_config']['db_collate'],
            ':db_prefix'               => $params['wordpress_wp_config']['db_prefix'],
            ':wp_lang'                 => $params['wordpress_wp_config']['wp_lang'],
            ':wp_debug'                => (false !== $params['wordpress_wp_config']['wp_debug']) ? 'true' : 'false',
            ':disallow_file_edit'      => (false !== $params['wordpress_wp_config']['disallow_file_edit']) ? 'true' : 'false',
            ':auth_keys'               => $authKeys,
            ':vendor_dir'              => $event->getComposer()->getConfig()->get('vendor-dir'),
			':wp_uploads_dir'		   => $params['wordpress_wp_config']['wp_uploads_dir'],
            ':wp_post_revisions'	   => (false !== $params['wordpress_wp_config']['wp_post_revisions']) ? 'true' : 'false',
			':wp_cache'				   => (false !== $params['wordpress_wp_config']['wp_cache']) ? 'true' : 'false',
            ':autosave_interval'  	   => $params['wordpress_wp_config']['autosave_interval'],
            ':cache_exp_time'		   => $params['wordpress_wp_config']['cache_exp_time'],
            ':WP_DEFAULT_THEME'		   => $params['wordpress_wp_config']['WP_DEFAULT_THEME']
        );

        // Get the wp-config template file content.
        $wpConfig = file_get_contents(__DIR__ . '/../../../templates/wp-config.php-dist');
		$wpIndex = file_get_contents(__DIR__ . '/../../../templates/index.php-dist');
        // Replace tokens with values.
        $wpConfig = str_replace(
            array_keys($wpConfigParams),
            $wpConfigParams,
            $wpConfig
        );

        $wpIndex = str_replace(
            array_keys($wpConfigParams),
            $wpConfigParams,
            $wpIndex
        );		
        // Write the wp-config.php file.
        file_put_contents('./wp-config.php', $wpConfig);
		file_put_contents('./index.php', $wpIndex);
		
		echo 'wp-config created';
    }
    /**
     * install wp with default settings
     *
     * @access public
     * @param  Event  $event Event object
     * @return void
     */
    public static function installWP(Event $event){
		$params = self::getExtra($event);
		$url = $params['wordpress_wp_config']['site_url'] . '/' . $params['wordpress_wp_config']['wp_core_dir'] . '/wp-admin/install.php?step=2';
		$fields = array(
			"weblog_title" 		=> $params['wordpress_wp_config']['wp_title'],
			"user_name" 		=> $params['wordpress_wp_config']['wp_admin'],
			"admin_password" 	=> $params['wordpress_wp_config']['wp_password'],
			"admin_password2" 	=> $params['wordpress_wp_config']['wp_password'],
			"admin_email" 		=> $params['wordpress_wp_config']['wp_admin_email'],
			"blog_public" 		=> (true == $params['wordpress_wp_config']['wp_is_public']) ? 1 : 0
		);
		
		$fields_string = '';
		foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
		rtrim($fields_string, '&');
		
		echo '----------about to install wp-----------';
		//open connection
		$ch = curl_init();

		//set the url, number of POST vars, POST data
		curl_setopt($ch,CURLOPT_URL, $url);
		curl_setopt($ch,CURLOPT_POST, count($fields));
		curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);

		//execute post
		$result = curl_exec($ch);
		
		if($result){
		// TODO : check result and show proper information
			echo '----------wordpress succesifully installed-----------';
		}
		//close connection
		curl_close($ch);		
	}
	
	public static function buildTemplate(Event $event){
		$params = self::getExtra($event);
		if($params['wordpress_wp_config']['wp_theme_src']){
			$path = realpath(__DIR__ . '/../../../../../../') . '/' . $params['wordpress_wp_config']['wp_theme_src'];
		} else {
			throw new Exception('please set wp_theme_src');
			return;
		}
		
		
		chdir($path);
		exec('npm install');
		echo "\n" . 'npm install Done';
		exec('bower install');
		echo "\n" . 'bower install Done';
		exec('gulp build');
		echo "\n" . 'gulp build Done';
	}
}