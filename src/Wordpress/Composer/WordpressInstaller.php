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

use Composer\Package\PackageInterface;
use Composer\Installer\LibraryInstaller;

/**
 * WordpressInstaller is a installer class for composer. It handles
 * the installing of worpress packages.
 *
 * @package Wordpress/Composer
 * @subpackage Installer
 * @category Installer
 */
class WordpressInstaller extends LibraryInstaller {

    /**
     * Supported package types.
     * 
     * @var array
     */
    protected $_types = array(
        'wordpress-plugin',
		'wordpress-muplugin',
        'wordpress-theme',
        'wordpress-core',
    );

    /**
     * Find and return the installation path used by
     * composer installer for a package.
     *
     * @access public
     * @param  PackageInterface $package Package object
     * @return string                    Installation path.
     */
    public function getInstallPath(PackageInterface $package)
    {
        // Default paths
        $wpCorePath    = 'wordpress/core';
        $wpContentPath = 'wordpress/wp-content';

        // Find what the path should be for the given package
        // based on it's name.
        $path          = $package->getPrettyName();
        $pos           = strpos($path, '/');

        if ($pos !== FALSE)
        {
            $path = substr($path, $pos);
        }

        // Using anything that's been configured but the composer.json file.
        if ($this->composer->getPackage())
        {
            $extra = $this->composer->getPackage()->getExtra();
            if ( ! empty($extra["wordpress_wp_config"]['wp_core_dir']))
            {
                $wpCorePath = rtrim($extra["wordpress_wp_config"]['wp_core_dir'], '/');
            }

            if ( ! empty($extra["wordpress_wp_config"]['wp_content_dir']))
            {
                $wpContentPath = rtrim($extra["wordpress_wp_config"]['wp_content_dir'], '/');
            }
        }

        switch($package->getType())
        {
            case 'wordpress-core':
                $installPath = $wpCorePath;
		
                break;
            case 'wordpress-plugin':
                $installPath = $wpContentPath . '/plugins/' . trim($path, '/');
		
                break;
            case 'wordpress-muplugin':
                $installPath = $wpContentPath . $extra['wordpress_wp_config']['wp_mupl_dir'] . trim($path, '/');

		break;
            case 'wordpress-theme':
                $installPath = $wpContentPath . '/themes/' . trim($path, '/');   
		
                break;
        }

        return $installPath;
    }

    /**
     * Check if a package is supported by this class.
     *
     * @access public
     * @param  string $type Package type.
     * @return bool         True if it is supported otherwise false.
     */
    public function supports($type)
    {
        return in_array($type, $this->_types);
    }

}
