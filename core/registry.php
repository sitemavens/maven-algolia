<?php

namespace MavenAlgolia\Core;


class Registry{
	
	private static $instance;
	private $pluginUrl;
	private $pluginDir;
	private $pluginVersion;
	private $pluginName;
	private $pluginDirectoryName = "";
	private $pluginKey = false;
	private $pluginShortName = false;
	private $options = array( );
	
	/**
	 *
	 * @var WordpressRegistry 
	 */
	private $settingKey = 'maven-algolia';
	
	
	/**
	 * 
	 * @return \MavenAlgolia\Core\Registry
	 */
	static function instance() {
		if ( ! isset( self::$instance ) ) {
			
			$defaultOptions = array(
				'apiKey' => '', 
				'appId' => '',
				'apiKeySearch' => '',
				'defaultIndex' => '',
				'appValid' => '0',
				'appSearchValid' => '0',
				'indexTaxonomies' => '0',
				'showPostCategoriesInPopup' => '0',
				'showThumbInPopup' => '0',
				'popupThumbnailArgs' => array('w' => 20, 'h' => 40),
				'showExcerptInPopup' => '0',
				'excerptMaxChars' => '0',
			);
			
			self::$instance = new self( );
			self::$instance->setOptions($defaultOptions);
		}
		

		return self::$instance;
	}
	
	/**
	 * Return the values
	 * @return \Maven\Settings\Option[]
	 */
	public function getOptions() {
		return $this->options;
	}

	public function getKeys() {
		return array_keys( $this->options );
	}

	/**
	 * This method must be used JUST to initialize the default settings
	 * @param Option[] $values
	 * @return Option[] 
	 */
	protected function setOptions( $options ) {

		foreach ( $options as $optionKey => $option ) {
			$this->options[ $optionKey ] = $option;
		}
	}
	
	
	/**
	 * Return a setting
	 * @param string $key
	 * @return \Maven\Settings\Option 
	 */
	public function get( $key ) {

		if ( isset( $this->options[ $key ] ) ) {
			return $this->options[ $key ];
		}

		return null;
	}
	
	
	/**
	 * Set a setting
	 * @param string $key
	 * @param string $value 
	 */
	public function updateOption( $key, $value ) {

		if ( isset( $this->options[ $key ] ) ) {
			$this->options[ $key ] = $value;
			$this->saveOptions( $this->options );
		}
	}
	
	/**
	 * Set a setting
	 * @param string $key
	 * @param string $value 
	 */
	public function set( $key, $value ) {

		if ( isset( $this->options[ $key ] ) ) {
			$this->options[ $key ] = $value;
		}
	}

	public function getPluginUrl () {
		return $this->pluginUrl;
	}

	public function getPluginDir () {
		return $this->pluginDir;
	}

	public function getPluginVersion () {
		return $this->pluginVersion;
	}

	public function getPluginName () {
		return $this->pluginName;
	}

	public function getPluginDirectoryName () {
		return $this->pluginDirectoryName;
	}

	public function getPluginKey () {
		return $this->pluginKey;
	}

	public function getPluginShortName () {
		return $this->pluginShortName;
	}

	public static function setInstance ( $instance ) {
		self::$instance = $instance;
	}

	public function setPluginUrl ( $pluginUrl ) {
		$this->pluginUrl = $pluginUrl;
	}

	public function setPluginDir ( $pluginDir ) {
		$this->pluginDir = $pluginDir;
	}

	public function setPluginVersion ( $pluginVersion ) {
		$this->pluginVersion = $pluginVersion;
	}

	public function setPluginName ( $pluginName ) {
		$this->pluginName = $pluginName;
	}

	public function setPluginDirectoryName ( $pluginDirectoryName ) {
		$this->pluginDirectoryName = $pluginDirectoryName;
	}

	public function setPluginKey ( $pluginKey ) {
		$this->pluginKey = $pluginKey;
	}

	public function setPluginShortName ( $pluginShortName ) {
		$this->pluginShortName = $pluginShortName;
	}
	
	
	public function reset(){
		
		delete_option( $this->getSettingKey() );
		
	}
	
	/**
	 * 
	 * @param \Maven\Settings\Option[] $options
	 */
	public function saveOptions( $options ){
		
		if( get_option( $this->getSettingKey() ) !== FALSE ){
			//Save the options in the WP table
			update_option( $this->getSettingKey(), $options );
		}else{
			//Save the options in the WP table
			add_option( $this->getSettingKey(), $options, '', 'no' );
		}
		
		$this->setOptions( $options );
	}
	
	
	private function getSettingKey(){
		
		return $this->settingKey;
	}
	
	public function getAbsPath(){
		return ABSPATH;
		
	}
	
	
	public function getWpIncludesPath( $full = false ){
		
		if ( $full ) {
			return ABSPATH . WPINC . "/";
		}

		return WPINC;
	}

	
	public function init() {
		
		// Get the options from the db
		$existingsOptions = get_option( $this->getSettingKey() );
		
		// If options exists we need to merge them with the default ones
		$options = wp_parse_args( $existingsOptions, $this->getOptions() );
		
		$this->setOptions( $options );
		
		
	}
	
	/**
	 * Return a setting
	 * @param string $key
	 * @return null 
	 */
	public function getValue( $key ) {

		if ( isset( $this->options[ $key ] ) ) {
			return $this->options[ $key ];
		}

		return null;
	}
	
	
	public function getApiKey(){
		return $this->getValue('apiKey');
	}
	
	public function getAppId(){
		return $this->getValue('appId');
	}
	
	public function getApiKeySearch(){
		return $this->getValue('apiKeySearch');
	}
	
	public function getDefaultIndex(){
		return $this->getValue('defaultIndex');
	}
	
	public function isValidApp(){
		return (bool)$this->getValue('appValid');
	}
	
	public function isValidAppSearch(){
		return (bool)$this->getValue('appSearchValid');
	}
	
	public function isEnabled(){
		return (bool) ( $this->isValidAppSearch() && $this->getDefaultIndex() );
	}
	
	public function indexTaxonomies( ) {
		return (bool)$this->getValue('indexTaxonomies');
	}
	
	public function showPostCategoriesInPopup( ) {
		return (bool)$this->getValue('showPostCategoriesInPopup');
	}
	
	public function showExcerptInPopup( ) {
		return (bool)$this->getValue('showExcerptInPopup');
	}
	
	public function getExcerptMaxChars( ) {
		return $this->getValue('excerptMaxChars');
	}
	
	public function showThumbInPopup( ) {
		return (bool)$this->getValue('showThumbInPopup');
	}
	
	public function getPopupThumbnailArgs( ) {
		return $this->getValue('popupThumbnailArgs');
	}
	
} 