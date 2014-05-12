<?php

namespace MavenAlgolia\Core;

// Exit if accessed directly 
if ( !defined( 'ABSPATH' ) )
	exit;

class Loader {

	private static $classTypes = array();
	private static $classNames = array();

	/**
	 * 
	 * @param string $plugin_dir
	 * @param string/array $files
	 * @param array $data
	 * @param boolean $requireOnce
	 * @param boolean $return
	 */
	public static function load ( $plugin_dir, $files, $data = false, $requireOnce = true, $return = false ) {

		if ( is_array( $files ) ) {
			foreach ( $files as $file ) {
				self::loadFile( $plugin_dir . $file, $data, $requireOnce, $return );
			}
		} else if ( $files )
			return self::loadFile( $plugin_dir . $files, $data, $requireOnce, $return );
	}

	/**
	 *
	 * @param string $lib
	 * @param \Maven\Settings\Registry $registry 
	 */
	public static function loadLibrary ( $lib, $registry ) {

		$lib = self::addExtension( $lib );

		self::loadFile( $registry->getPluginDir() . $lib );
	}

	private static function addExtension ( $file ) {

		$extension = "";

		if ( !pathinfo( $file, PATHINFO_EXTENSION ) )
			$extension = ".php";

		return $file . $extension;
	}

	private static function loadFile ( $file, $data = false, $requireOnce = true, $return = false ) {


		$file = self::addExtension( $file );

		if ( $data )
			extract( $data );

		if ( file_exists( $file ) ) {
			if ( $requireOnce ) {
				if ( $return ) {
					ob_start();
					require_once $file;
					return ob_get_clean();
				} else {

					require_once $file;
				}
			} else {
				if ( $return ) {

					ob_start();
					require $file;
					return ob_get_clean();
				} else
					require $file;
			}
		} else
			die( $file );
		//TODO: Throw an exception if the file doesn't exists
	}

	/**
	 * 
	 * @param string $classType
	 * @param string $dir 
	 */
	public static function registerType ( $classType, $dir ) {

		if ( !isset( self::$classTypes[ $classType ] ) )
			self::$classTypes[ $classType ] = new ClassType( $classType, $dir );
	}

	/**
	 * 
	 * @param string $className
	 * @return ClassType 
	 */
	public static function getClassType ( $className ) {


		if ( isset( self::$classNames[ $className ] ) )
			return self::$classNames[ $className ];

		foreach ( self::$classTypes as $key => $value ) {

			$pos = strpos( $className, $key . '\\' );

			if ( $pos !== false ) {

				return $value;
			}
		}
	}

	/**
	 * 
	 * @param string $className
	 * @return boolean 
	 */
	public static function isClassTypeRegister ( $className ) {

		if ( isset( self::$classNames[ $className ] ) )
			return true;

		foreach ( self::$classTypes as $key => &$classType ) {

			//We search for the class type. We need to ensure that is something like Maven\ or MavenStats\
			$pos = strpos( $className, $key . '\\' );

			if ( $pos !== false ) {

				//We use this array to save the classes and improve perfornance
				self::$classNames[ $className ] = &$classType;
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if a class was loaded or not
	 * @param string $className
	 * @return boolean 
	 */
	public static function classLoaded ( $className ) {
		return class_exists( $className );
	}

	/**
	 * Get file content
	 * @param string $file
	 * @return string
	 * @throws \Maven\Exceptions\FileNotFoundException
	 */
	public static function getFileContent ( $file ) {

		if ( !$file || !file_exists( $file ) ) {
			throw new \Maven\Exceptions\FileNotFoundException( 'File not found: ' . $file );
		}

		ob_start();
		require($file);
		$templateContent = ob_get_clean();

		return $templateContent;
	}

	/**
	 * Convert a CamcelCase string into a word separated string, using the splitter
	 * @param string $camel
	 * @param string $splitter
	 * @return string 
	 */
	/* public static function unCamelize( $camel, $splitter = "-" ) {
	  $camel = preg_replace( '/(?!^)[[:upper:]][[:lower:]]/', '$0', preg_replace( '/(?!^)[[:upper:]]+/', $splitter . '$0', $camel ) );
	  return strtolower( $camel );
	  } */
}

/**
 * It's just a class to save a few info about a type
 */
class ClassType {

	private $classType;
	private $dir;

	/**
	 * 
	 * @param string $classType
	 * @param string $dir 
	 */
	function __construct ( $classType, $dir ) {
		$this->classType = $classType;
		$this->dir = $dir;
	}

	public function getDir () {
		return $this->dir;
	}

	public function setDir ( $value ) {
		$this->dir = $value;
	}

	public function getClassType () {
		return $this->classType;
	}

	public function setClassType ( $value ) {
		$this->classType = $value;
	}

}

spl_autoload_register( function($className) {

	$originalClassName = $className;

	if ( !Loader::isClassTypeRegister( $className ) )
		return;

	if ( $className[ 0 ] == "\\" ) {
		$className = substr( $className, 1, strlen( $className ) );
	}

	$classType = Loader::getClassType( $className );

	//Get the class name
	$classOnlyName = substr( $className, strrpos( $className, "\\" ) + 1 );

	//Remove the class name from the full path
	$className = substr( $className, 0, strrpos( $className, "\\" ) + 1 );


	$className = Utils::unCamelize( $className );


//		$className = explode('\\', $className);
//		foreach( $className as $part )
//			$part = Utils::unCamelize( $part );
//		
//		$className = implode('\\', $className);
//		if ( strpos( $className, 'IntelligenceReport' )){
//				var_dump(Utils::unCamelize( $className ));die();
//		}
//		
	//$className		= str_replace( $classOnlyName, "", $className );
	//Convert Camel Case with -
	$classOnlyName = Utils::unCamelize( $classOnlyName );
	$uncamelizeClassType = Utils::unCamelize( $classType->getClassType() );

//		 var_dump($classType->getClassType());
//		  var_dump($className);
//		   var_dump(strtolower( strtr( substr( $className, strlen( $classType->getClassType() ) ), '\\', '/' ) ));
	// var_dump($classType->getDir());
	$classPath = strtolower( strtr( substr( $className, strlen( $uncamelizeClassType ) ), '\\', '/' ) ) . $classOnlyName . ".php";

	$filePath = $classType->getDir() . $classPath;

	global $loader;
	
	if ( $loader)
		return;
	
	if ( file_exists( $filePath ) ) {
			require_once( $filePath );
	} else {
		$data = "File not found <br />";
		$data .= "Class Name: $originalClassName <br />";
		$data .= "Class Name modified: $className <br />";
		$data .= "Class Path: $classPath <br />";
		$data .= "Class Only Name: $classOnlyName <br />";
		$data .= "Class Type: " . $uncamelizeClassType . "<br/>";
		$data .= "File Path: " . $filePath . "<br/>";
		echo "<pre>";
		debug_print_backtrace();
		echo "</pre>";
		die( $data );
	}
} );
