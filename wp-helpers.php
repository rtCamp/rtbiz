<?php
/**
 * Created by PhpStorm.
 * User: faishal
 * Date: 17/02/14
 * Time: 3:40 PM
 */
if ( ! class_exists( 'RT_WP_Autoload' ) ){
	/**
	 * Generic autoloader for classes named in WordPress coding style.
	 */
	class RT_WP_Autoload
	{

		/**
		 * @var string Current directory absolute path
		 */
		public $dir;

		/**
		 * @param string $dir optional, default value is current
		 */
		function __construct( $dir = '' )
		{
			if ( ! empty( $dir ) ){
				$this->dir = trailingslashit( $dir );
			} else {
				//__DIR__ is actually equivalent to realpath(dirname(__FILE__)) and Added in PHP 5.3.0
				//Bellow line will also work for php  5.2.4 (WordPress required php  5.2.4 or greater )
				$this->dir = trailingslashit( ( @__DIR__ == '__DIR__' ) && define( '__DIR__', realpath( dirname( __FILE__ ) ) ) );
			}

			spl_autoload_register( array( $this, 'spl_autoload_register' ) );
		}

		/**
		 * @param string $class_name
		 */
		function spl_autoload_register( $class_name )
		{

			$class_path = $this->dir . strtolower( str_replace( '_', '-', $class_name ) ) . '/class-' . strtolower( str_replace( '_', '-', $class_name ) ) . '.php';

			if ( file_exists( $class_path ) ){
				include $class_path;
			}
		}
	}
}

/**
 * Initiate Loader class
 */

$obj_rt_wp_loader = new RT_WP_Autoload();