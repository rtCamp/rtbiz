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
				$this->dir = $dir ;
			} else {
				//__DIR__ is actually equivalent to realpath(dirname(__FILE__)) and Added in PHP 5.3.0
				//Bellow line will also work for php  5.2.4 (WordPress required php  5.2.4 or greater )
				$this->dir = realpath( dirname( dirname( __FILE__ ) ) );
			}
			spl_autoload_register( array( $this, 'spl_autoload_register' ) );
		}

		/**
		 * @param string $class_name
		 */
		function spl_autoload_register( $class_name )
		{

			$class_path = trailingslashit( $this->dir ) .  $this->get_class_directory_name( $class_name ) . '/' . $this->get_class_file_name( $class_name );
			if ( file_exists( $class_path ) ){
				include_once $class_path;
			}
			$class_path = trailingslashit( $this->dir ) . $this->get_class_file_name( $class_name );

			if ( file_exists( $class_path ) ){
				include_once $class_path;
			}
		}

		/**
		 * @param $class_name string class name
		 *
		 * @return string return directory name as per the coding convention
		 */
		function get_class_directory_name( $class_name ){
			return strtolower( str_replace( '_', '-', $class_name ) );
		}

		/**
		 * @param $class_name string class name
		 *
		 * @return string return directory name as per the coding convention
		 */
		function get_class_file_name( $class_name ){
			return 'class-' . strtolower( str_replace( '_', '-', $class_name ) ) . '.php' ;
		}

	}
}
