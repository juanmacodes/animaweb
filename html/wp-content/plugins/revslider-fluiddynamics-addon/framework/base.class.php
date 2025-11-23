<?php
/* 
 * @author    ThemePunch <info@themepunch.com>
 * @link      http://www.themepunch.com/
 * @copyright 2024 ThemePunch
*/

if(!defined('ABSPATH')) exit();

class RsAddOnTheFluidDynamicsBase {
	
	const MINIMUM_VERSION = '6.7.0';
	
	protected function systemsCheck() {
		
		if(!class_exists('RevSliderFront')) {
		
			return 'add_notice_plugin';
		
		}
		else if(!version_compare(RevSliderGlobals::SLIDER_REVISION, RsAddOnTheFluidDynamicsBase::MINIMUM_VERSION, '>=')) {
		
			return 'add_notice_version';
		
		}
		else if(get_option('revslider-valid', 'false') == 'false') {
		
			 return 'add_notice_activation';
		
		}
		
		return false;
		
	}
	
	protected function loadClasses() {
		
		$isAdmin = is_admin();
		
		if($isAdmin) {
			
			//handle update process, this uses the typical ThemePunch server process
			require_once(static::$_PluginPath . 'admin/includes/update.class.php');
			$update_admin = new RevAddOnTheFluidDynamicsUpdate(static::$_Version);

			add_filter('pre_set_site_transient_update_plugins', array($update_admin, 'set_update_transient'));
			add_filter('plugins_api', array($update_admin, 'set_updates_api_results'), 10, 3);
			
			// admin CSS/JS
			add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
			
		}

		//v7, load always as things are needed in overview page
		require_once(static::$_PluginPath . 'public/includes/front.class.php');
		new SrFluidDynamicsFront(static::$_PluginTitle);

		global $SR_GLOBALS;
    	if(!isset($SR_GLOBALS['front_version']) || $SR_GLOBALS['front_version'] !== 7){
			/* 
			frontend scripts always enqueued for admin previews
			*/
			require_once(static::$_PluginPath . 'sr6/includes/slider.class.php');
			require_once(static::$_PluginPath . 'sr6/includes/slide.class.php');
			
			new RsTheFluidDynamicsSliderFront(static::$_Version, static::$_PluginUrl, static::$_PluginTitle, $isAdmin);
			new RsTheFluidDynamicsSlideFront(static::$_PluginTitle);
		}
		
	}
	
	/**
	 * Load the textdomain
	 **/
	public function _loadPluginTextDomain(){
		
		load_plugin_textdomain('rs_' . static::$_PluginTitle, false, static::$_PluginPath . 'languages/');
		
	}

	// load admin scripts
	public function enqueue_admin_scripts($hook) {

		if($hook === 'toplevel_page_revslider') {

			if(!isset($_GET['page']) || !isset($_GET['view'])) return;
			
			$page = $_GET['page'];
			if($page !== 'revslider') return;
			
			$_handle = 'rs-' . static::$_PluginTitle;
			$_base   = static::$_PluginUrl . 'admin/assets/';
			
			// load fronted Script for some global function
			$_jsPathMin = file_exists(RS_FLUIDDYNAMICS_PLUGIN_PATH . 'sr6/assets/js/revolution.addon.' . static::$_PluginTitle . '.js') ? '' : '.min';	
			wp_enqueue_script($_handle.'-js', static::$_PluginUrl . 'sr6/assets/js/revolution.addon.' . static::$_PluginTitle . $_jsPathMin . '.js', array('jquery', 'revbuilder-admin'), static::$_Version, true);
			
			$_jsPathMin = file_exists(static::$_PluginPath . 'admin/assets/js/revslider-' . static::$_PluginTitle . '-addon-admin.dev.js') ? '.dev' : '';
			wp_enqueue_style($_handle.'-css', $_base . 'css/revslider-' . static::$_PluginTitle . '-addon-admin.css', array(), static::$_Version);
			wp_enqueue_script($_handle.'-addon-admin-js', $_base . 'js/revslider-' . static::$_PluginTitle . '-addon-admin' . $_jsPathMin . '.js', array('jquery', 'revbuilder-admin', 'revbuilder-threejs'), static::$_Version, true);
			wp_localize_script($_handle.'-addon-admin-js', 'revslider_fluiddynamics_addon', self::get_var() );
			
			wp_enqueue_script('revbuilder-threejs', RS_PLUGIN_URL . 'sr6/assets/js/libs/three.min.js', array('jquery', 'revbuilder-admin',$_handle.'-js'), RS_REVISION);
		}		
	}

	
	/**
	 * New function for ajax activation to include AddOn help definitions
	 *
	 * @since    2.0.0
	 */
	public static function get_data($var='',$slug='revslider-fluiddynamics-addon') {
		
		if($slug === 'revslider-fluiddynamics-addon'){
			
			$obj = self::get_var();
			$obj['help'] = self::get_definitions();
			return $obj;
			
		}
		
		return $var;
	
	}
	
	/**
	 * Called via php filter.  Merges AddOn definitions with core revslider definitions
	 *
	 * @since    2.0.0
	 */
	public static function get_help($definitions) {
		
		if(empty($definitions) || !isset($definitions['editor_settings'])) return $definitions;
		
		if(isset($definitions['editor_settings']['layer_settings']) && isset($definitions['editor_settings']['layer_settings']['addons'])) {
			$help = self::get_definitions();
			$definitions['editor_settings']['layer_settings']['addons']['fluiddynamics_addon'] = $help['layer'];
		}
		
		return $definitions;
	
	}
	
	/**
	 * Returns the global JS variable
	 *
	 * @since    2.0.0
	 */
	public static function get_var() {
			
		$_textdomain = 'revslider-fluiddynamics-addon';
		return array(
		
			'bricks' => array(		
				// GENERAL
				'fluiddynamics' => __('Fluid Dynamics', $_textdomain),
				'fluiddynamicsdatt' => __('Fluid Dynamics Setup', $_textdomain),
				'restartAni' => __('Restart Animation', $_textdomain),
				'pelib' => __('Fluid Dynamics FX Library',$_textdomain),
				'parpres' => __('Default Presets',$_textdomain),
				'custompres' => __('Custom Presets',$_textdomain),
				'select' => __('Select', $_textdomain),
				
				//Menus
				'main' => __('Main', $_textdomain),
				'simu' => __('Simulation', $_textdomain),
				'color' => __('Color', $_textdomain),

				//Main
				'followMouseSetup' => __('Mouse Setup', $_textdomain),
				'followMouse' => __('Follow Mouse', $_textdomain),
				'followAMGen' => __('Follow Mouse', $_textdomain),
				'paramSetup' => __('Parameter Setup', $_textdomain),
				'curlVal' => __('Curl', $_textdomain),
				'mousePower' => __('Mouse Power', $_textdomain),
				'splatRadius' => __('Radius', $_textdomain),
				'lifetimeSetup' => __('Lifetime Setup', $_textdomain),
				'densityDissipation' => __('Density', $_textdomain),
				'velocityDissipation' => __('Velocity', $_textdomain),
				'pressureDissipation' => __('Pressure', $_textdomain),
				'densityDissipationValueHelper' => __('Density controlls the speed the Color fades away (Base value: 69)', $_textdomain),
				'velocityDissipationValueHelper' => __('Velocity controlls how long the Path stays visible (Base value: 96)', $_textdomain),
				'pressureDissipationValueHelper' => __('Pressure controlls how long the pressure fields of prior movement stay in the Scene (Base value: 75)', $_textdomain),
				'dprSetup' => __('Resolution Setup', $_textdomain),
				'qualityFluid' => __('Quality', $_textdomain),
				'dprFluid' => __('DPR', $_textdomain),
				'dprSetupMobile' => __('Mobile', $_textdomain),
				'dprOnMobile' => __('Mobile Setup', $_textdomain),

				//SIMU
				'direction' => __('Direction', $_textdomain),
				'simulationSetup' => __('Simulation Setup', $_textdomain),
				'simuDelay' => __('Delay', $_textdomain),
				'simuLength' => __('Duration', $_textdomain),
				'simuWait' => __('Interval', $_textdomain),
				'simuPower' => __('Power', $_textdomain),
				'simuRadius' => __('Radius', $_textdomain),
				'vecLength' => __('Speed', $_textdomain), 
				'mistHelperDuration' => __('Controls the Speed and Randomness of the Emitters', $_textdomain),
				'mistHelperPower' => __('Controls the Power of the Emitters', $_textdomain),
				'mistHelperSpeed' => __('Controls the Speed of spreading', $_textdomain),

				//COLOR
				'colorSetup' => __('Main Color Setup', $_textdomain),
				'colorChangeSetup' => __('Color Change Setup', $_textdomain),
				'colorEffectSetup' => __('Effects', $_textdomain),
				'maxOpacitySetup' => __('Opacity Setup', $_textdomain),
				'colorChangeLenth' => __('Duration', $_textdomain),
				'allColorCount' => __('Colors used', $_textdomain),
				'fd_color1' => __('Color 1', $_textdomain),
				'fd_color2' => __('Color 2', $_textdomain),
				'fd_color3' => __('Color 3', $_textdomain),
				'fd_color4' => __('Color 4', $_textdomain),
				'fd_color5' => __('Color 5', $_textdomain),
				'fd_color6' => __('Color 6', $_textdomain),
				'maxOpacityValue' => __('Max Opacity', $_textdomain),
				'colorFull' => __('Colorfull', $_textdomain),
				'maxOpacityValueHelper' => __('Limits the Opacity of all Colors shown to selected Value', $_textdomain),
				'colorFade' => __('Color Fade', $_textdomain),
				'alphaClearHelper' => __('Option currently only works on Windows (After changings state, save & reload)', $_textdomain),
				'colorFullHelper' => __('When turned on, max Opacity should be reduced', $_textdomain),
				'glow' => __('Glow', $_textdomain),
			)
		);
	
	}
	
	/**
	 * Returns the addon help definitions
	 *
	 * @since    2.0.0
	 */
	private static function get_definitions() {
		
		return array(
			
			'layer' => array(
	
				/**
				 * _____________________________________
				 * ________________MAIN_________________
				 * _____________________________________
				 */

				// ____MOUSE SETUP_____

				'followMouse' => array(
					
					'buttonTitle' => __('Follow Mouse', 'revslider-fluiddynamics-addon'), 
					'title' => __('Follow Mouse', 'revslider-fluiddynamics-addon'),
					'helpPath' => 'addOns.revslider-fluiddynamics-addon.followMouse', 
					'keywords' => array('addon', 'addons', 'fluiddynamics', 'fluiddynamics addon', 'Follow Mouse'), 
					'description' => __("Toggles the ability to controll the Fluid by moving the Mouse", 'revslider-fluiddynamics-addon'), 
					'helpStyle' => 'normal', 
					'article' => 'http://docs.themepunch.com/slider-revolution/fluiddynamics-addon/', 
					'video' => false,
					'section' => 'Layer Settings -> TheFluidDynamics',
					'highlight' => array(
						
						'dependencies' => array('layerselected::shape{{fluiddynamics}}'), 
						'menu' => "#module_layers_trigger, #gst_layer_revslider-fluiddynamics-addon", 
						'scrollTo' => '#form_layerinner_revslider-fluiddynamics-addon', 
						'focus' => "*[data-r='addOns.revslider-fluiddynamics-addon.followMouse']"
						
					)
					
				),

				'mousePower' => array(
					
					'buttonTitle' => __('Mouse Power', 'revslider-fluiddynamics-addon'), 
					'title' => __('Mouse Power', 'revslider-fluiddynamics-addon'),
					'helpPath' => 'addOns.revslider-fluiddynamics-addon.mousePower', 
					'keywords' => array('addon', 'addons', 'fluiddynamics', 'fluiddynamics addon', 'Mouse Power'), 
					'description' => __("Controlls the Force of the mouse interacting with the Fluid", 'revslider-fluiddynamics-addon'), 
					'helpStyle' => 'normal', 
					'article' => 'http://docs.themepunch.com/slider-revolution/fluiddynamics-addon/', 
					'video' => false,
					'section' => 'Layer Settings -> TheFluidDynamics',
					'highlight' => array(
						
						'dependencies' => array('layerselected::shape{{fluiddynamics}}'), 
						'menu' => "#module_layers_trigger, #gst_layer_revslider-fluiddynamics-addon", 
						'scrollTo' => '#form_layerinner_revslider-fluiddynamics-addon', 
						'focus' => "*[data-r='addOns.revslider-fluiddynamics-addon.mousePower']"
						
					)
					
				),

				// ____PARAMETER SETUP_____

				'curlVal' => array(
					
					'buttonTitle' => __('Curl', 'revslider-fluiddynamics-addon'), 
					'title' => __('Curl', 'revslider-fluiddynamics-addon'),
					'helpPath' => 'addOns.revslider-fluiddynamics-addon.curlVal', 
					'keywords' => array('addon', 'addons', 'fluiddynamics', 'fluiddynamics addon', 'curlVal'), 
					'description' => __("Amount of Curl added to the Fluid Effect", 'revslider-fluiddynamics-addon'), 
					'helpStyle' => 'normal', 
					'article' => 'http://docs.themepunch.com/slider-revolution/fluiddynamics-addon/', 
					'video' => false,
					'section' => 'Layer Settings -> TheFluidDynamics',
					'highlight' => array(
						
						'dependencies' => array('layerselected::shape{{fluiddynamics}}'), 
						'menu' => "#module_layers_trigger, #gst_layer_revslider-fluiddynamics-addon", 
						'scrollTo' => '#form_layerinner_revslider-fluiddynamics-addon', 
						'focus' => "*[data-r='addOns.revslider-fluiddynamics-addon.curlVal']"
						
					)
					
				),

				'splatRadius' => array(
					
					'buttonTitle' => __('Radius', 'revslider-fluiddynamics-addon'), 
					'title' => __('Radius', 'revslider-fluiddynamics-addon'),
					'helpPath' => 'addOns.revslider-fluiddynamics-addon.splatRadius', 
					'keywords' => array('addon', 'addons', 'fluiddynamics', 'fluiddynamics addon', 'Radius'), 
					'description' => __("Sets the max value for the Fluids Radius (Radius is also affected by the mouse movement Speed)", 'revslider-fluiddynamics-addon'), 
					'helpStyle' => 'normal', 
					'article' => 'http://docs.themepunch.com/slider-revolution/fluiddynamics-addon/', 
					'video' => false,
					'section' => 'Layer Settings -> TheFluidDynamics',
					'highlight' => array(
						
						'dependencies' => array('layerselected::shape{{fluiddynamics}}'), 
						'menu' => "#module_layers_trigger, #gst_layer_revslider-fluiddynamics-addon", 
						'scrollTo' => '#form_layerinner_revslider-fluiddynamics-addon', 
						'focus' => "*[data-r='addOns.revslider-fluiddynamics-addon.splatRadius']"
						
					)
					
				),

				// ____LIFETIME SETUP_____

				'densityDissipation' => array(
					
					'buttonTitle' => __('Density', 'revslider-fluiddynamics-addon'), 
					'title' => __('Density', 'revslider-fluiddynamics-addon'),
					'helpPath' => 'addOns.revslider-fluiddynamics-addon.densityDissipation', 
					'keywords' => array('addon', 'addons', 'fluiddynamics', 'fluiddynamics addon', 'Density'), 
					'description' => __("Controlls the Speed the Color Fades away at (higher value = color fades slower)", 'revslider-fluiddynamics-addon'), 
					'helpStyle' => 'normal', 
					'article' => 'http://docs.themepunch.com/slider-revolution/fluiddynamics-addon/', 
					'video' => false,
					'section' => 'Layer Settings -> TheFluidDynamics',
					'highlight' => array(
						
						'dependencies' => array('layerselected::shape{{fluiddynamics}}'), 
						'menu' => "#module_layers_trigger, #gst_layer_revslider-fluiddynamics-addon", 
						'scrollTo' => '#form_layerinner_revslider-fluiddynamics-addon', 
						'focus' => "*[data-r='addOns.revslider-fluiddynamics-addon.densityDissipation']"
						
					)
					
				),

				'velocityDissipation' => array(
					
					'buttonTitle' => __('Velocity', 'revslider-fluiddynamics-addon'), 
					'title' => __('Velocity', 'revslider-fluiddynamics-addon'),
					'helpPath' => 'addOns.revslider-fluiddynamics-addon.velocityDissipation', 
					'keywords' => array('addon', 'addons', 'fluiddynamics', 'fluiddynamics addon', 'Velocity'), 
					'description' => __("Controlls how long the Path stays visible (lower value = path visible for longer)", 'revslider-fluiddynamics-addon'), 
					'helpStyle' => 'normal', 
					'article' => 'http://docs.themepunch.com/slider-revolution/fluiddynamics-addon/', 
					'video' => false,
					'section' => 'Layer Settings -> TheFluidDynamics',
					'highlight' => array(
						
						'dependencies' => array('layerselected::shape{{fluiddynamics}}'), 
						'menu' => "#module_layers_trigger, #gst_layer_revslider-fluiddynamics-addon", 
						'scrollTo' => '#form_layerinner_revslider-fluiddynamics-addon', 
						'focus' => "*[data-r='addOns.revslider-fluiddynamics-addon.velocityDissipation']"
						
					)
					
				),

				'pressureDissipation' => array(
					
					'buttonTitle' => __('Pressure', 'revslider-fluiddynamics-addon'), 
					'title' => __('Pressure', 'revslider-fluiddynamics-addon'),
					'helpPath' => 'addOns.revslider-fluiddynamics-addon.pressureDissipation', 
					'keywords' => array('addon', 'addons', 'fluiddynamics', 'fluiddynamics addon', 'Pressure'), 
					'description' => __("Contolls how long prior movements in the Fluid affect new Movements (higher value = higher effect from prior movements", 'revslider-fluiddynamics-addon'), 
					'helpStyle' => 'normal', 
					'article' => 'http://docs.themepunch.com/slider-revolution/fluiddynamics-addon/', 
					'video' => false,
					'section' => 'Layer Settings -> TheFluidDynamics',
					'highlight' => array(
						
						'dependencies' => array('layerselected::shape{{fluiddynamics}}'), 
						'menu' => "#module_layers_trigger, #gst_layer_revslider-fluiddynamics-addon", 
						'scrollTo' => '#form_layerinner_revslider-fluiddynamics-addon', 
						'focus' => "*[data-r='addOns.revslider-fluiddynamics-addon.pressureDissipation']"
						
					)
					
				),

				// ____RESOLUTION SETUP_____

				'qualityFluid' => array(
					
					'buttonTitle' => __('Quality', 'revslider-fluiddynamics-addon'), 
					'title' => __('Quality', 'revslider-fluiddynamics-addon'),
					'helpPath' => 'addOns.revslider-fluiddynamics-addon.qualityFluid', 
					'keywords' => array('addon', 'addons', 'fluiddynamics', 'fluiddynamics addon', 'Quality'), 
					'description' => __("Adjusts the quality of the simulation on all devices", 'revslider-fluiddynamics-addon'), 
					'helpStyle' => 'normal', 
					'article' => 'http://docs.themepunch.com/slider-revolution/fluiddynamics-addon/', 
					'video' => false,
					'section' => 'Layer Settings -> TheFluidDynamics',
					'highlight' => array(
						
						'dependencies' => array('layerselected::shape{{fluiddynamics}}'), 
						'menu' => "#module_layers_trigger, #gst_layer_revslider-fluiddynamics-addon", 
						'scrollTo' => '#form_layerinner_revslider-fluiddynamics-addon', 
						'focus' => "*[data-r='addOns.revslider-fluiddynamics-addon.qualityFluid']"
						
					)
					
				),

				'dprFluid' => array(
					
					'buttonTitle' => __('DPR', 'revslider-fluiddynamics-addon'), 
					'title' => __('DPR', 'revslider-fluiddynamics-addon'),
					'helpPath' => 'addOns.revslider-fluiddynamics-addon.dprFluid', 
					'keywords' => array('addon', 'addons', 'fluiddynamics', 'fluiddynamics addon', 'DPR'), 
					'description' => __("Sets the max. device pixel ratio on all devices", 'revslider-fluiddynamics-addon'), 
					'helpStyle' => 'normal', 
					'article' => 'http://docs.themepunch.com/slider-revolution/fluiddynamics-addon/', 
					'video' => false,
					'section' => 'Layer Settings -> TheFluidDynamics',
					'highlight' => array(
						
						'dependencies' => array('layerselected::shape{{fluiddynamics}}'), 
						'menu' => "#module_layers_trigger, #gst_layer_revslider-fluiddynamics-addon", 
						'scrollTo' => '#form_layerinner_revslider-fluiddynamics-addon', 
						'focus' => "*[data-r='addOns.revslider-fluiddynamics-addon.dprFluid']"
						
					)
					
				),

				'dprOnMobile' => array(
					
					'buttonTitle' => __('Mobile Setup', 'revslider-fluiddynamics-addon'), 
					'title' => __('Mobile Setup', 'revslider-fluiddynamics-addon'),
					'helpPath' => 'addOns.revslider-fluiddynamics-addon.dprOnMobile', 
					'keywords' => array('addon', 'addons', 'fluiddynamics', 'fluiddynamics addon', 'Mobile Setup'), 
					'description' => __("If turned on, mobile devices can have seperate device pixel ratio settings to maximise performance", 'revslider-fluiddynamics-addon'), 
					'helpStyle' => 'normal', 
					'article' => 'http://docs.themepunch.com/slider-revolution/fluiddynamics-addon/', 
					'video' => false,
					'section' => 'Layer Settings -> TheFluidDynamics',
					'highlight' => array(
						
						'dependencies' => array('layerselected::shape{{fluiddynamics}}'), 
						'menu' => "#module_layers_trigger, #gst_layer_revslider-fluiddynamics-addon", 
						'scrollTo' => '#form_layerinner_revslider-fluiddynamics-addon', 
						'focus' => "*[data-r='addOns.revslider-fluiddynamics-addon.dprOnMobile']"
						
					)
					
				),

				// ____MOBILE_____

				'qualityFluidMobile' => array(
					
					'buttonTitle' => __('Quality', 'revslider-fluiddynamics-addon'), 
					'title' => __('Quality', 'revslider-fluiddynamics-addon'),
					'helpPath' => 'addOns.revslider-fluiddynamics-addon.qualityFluidMobile', 
					'keywords' => array('addon', 'addons', 'fluiddynamics', 'fluiddynamics addon', 'Quality'), 
					'description' => __("Adjusts the quality of the simulation on mobile devices", 'revslider-fluiddynamics-addon'), 
					'helpStyle' => 'normal', 
					'article' => 'http://docs.themepunch.com/slider-revolution/fluiddynamics-addon/', 
					'video' => false,
					'section' => 'Layer Settings -> TheFluidDynamics',
					'highlight' => array(
						
						'dependencies' => array('layerselected::shape{{fluiddynamics}}'), 
						'menu' => "#module_layers_trigger, #gst_layer_revslider-fluiddynamics-addon", 
						'scrollTo' => '#form_layerinner_revslider-fluiddynamics-addon', 
						'focus' => "*[data-r='addOns.revslider-fluiddynamics-addon.qualityFluidMobile']"
						
					)
					
				),

				'dprFluidMobile' => array(
					
					'buttonTitle' => __('DPR', 'revslider-fluiddynamics-addon'), 
					'title' => __('DPR', 'revslider-fluiddynamics-addon'),
					'helpPath' => 'addOns.revslider-fluiddynamics-addon.dprFluidMobile', 
					'keywords' => array('addon', 'addons', 'fluiddynamics', 'fluiddynamics addon', 'DPR'), 
					'description' => __("Sets the max. device pixel ratio on mobile devices", 'revslider-fluiddynamics-addon'), 
					'helpStyle' => 'normal', 
					'article' => 'http://docs.themepunch.com/slider-revolution/fluiddynamics-addon/', 
					'video' => false,
					'section' => 'Layer Settings -> TheFluidDynamics',
					'highlight' => array(
						
						'dependencies' => array('layerselected::shape{{fluiddynamics}}'), 
						'menu' => "#module_layers_trigger, #gst_layer_revslider-fluiddynamics-addon", 
						'scrollTo' => '#form_layerinner_revslider-fluiddynamics-addon', 
						'focus' => "*[data-r='addOns.revslider-fluiddynamics-addon.dprFluidMobile']"
						
					)
					
				),

				/**
				 * _____________________________________
				 * __________SIMULATION_________________
				 * _____________________________________
				 */

				'automateSel' => array(
					
					'buttonTitle' => __('Select', 'revslider-fluiddynamics-addon'), 
					'title' => __('Select', 'revslider-fluiddynamics-addon'),
					'helpPath' => 'addOns.revslider-fluiddynamics-addon.automateSel', 
					'keywords' => array('addon', 'addons', 'fluiddynamics', 'fluiddynamics addon', 'Select'), 
					'description' => __("Select Simulation", 'revslider-fluiddynamics-addon'), 
					'helpStyle' => 'normal', 
					'article' => 'http://docs.themepunch.com/slider-revolution/fluiddynamics-addon/', 
					'video' => false,
					'section' => 'Layer Settings -> TheFluidDynamics',
					'highlight' => array(
						
						'dependencies' => array('layerselected::shape{{fluiddynamics}}'), 
						'menu' => "#module_layers_trigger, #gst_layer_revslider-fluiddynamics-addon", 
						'scrollTo' => '#form_layerinner_revslider-fluiddynamics-addon', 
						'focus' => "*[data-r='addOns.revslider-fluiddynamics-addon.automateSel']"
						
					)
					
				),

				'directionChooser' => array(
					
					'buttonTitle' => __('Direction', 'revslider-fluiddynamics-addon'), 
					'title' => __('Direction', 'revslider-fluiddynamics-addon'),
					'helpPath' => 'addOns.revslider-fluiddynamics-addon.directionChooser', 
					'keywords' => array('addon', 'addons', 'fluiddynamics', 'fluiddynamics addon', 'Direction'), 
					'description' => __("Select Direction for Simulation", 'revslider-fluiddynamics-addon'), 
					'helpStyle' => 'normal', 
					'article' => 'http://docs.themepunch.com/slider-revolution/fluiddynamics-addon/', 
					'video' => false,
					'section' => 'Layer Settings -> TheFluidDynamics',
					'highlight' => array(
						
						'dependencies' => array('layerselected::shape{{fluiddynamics}}'), 
						'menu' => "#module_layers_trigger, #gst_layer_revslider-fluiddynamics-addon", 
						'scrollTo' => '#form_layerinner_revslider-fluiddynamics-addon', 
						'focus' => "*[data-r='addOns.revslider-fluiddynamics-addon.directionChooser']"
						
					)
					
				),

				'colDirectionChooser' => array(
					
					'buttonTitle' => __('Direction', 'revslider-fluiddynamics-addon'), 
					'title' => __('Direction', 'revslider-fluiddynamics-addon'),
					'helpPath' => 'addOns.revslider-fluiddynamics-addon.colDirectionChooser', 
					'keywords' => array('addon', 'addons', 'fluiddynamics', 'fluiddynamics addon', 'Direction'), 
					'description' => __("Select Direction for Simulation", 'revslider-fluiddynamics-addon'), 
					'helpStyle' => 'normal', 
					'article' => 'http://docs.themepunch.com/slider-revolution/fluiddynamics-addon/', 
					'video' => false,
					'section' => 'Layer Settings -> TheFluidDynamics',
					'highlight' => array(
						
						'dependencies' => array('layerselected::shape{{fluiddynamics}}'), 
						'menu' => "#module_layers_trigger, #gst_layer_revslider-fluiddynamics-addon", 
						'scrollTo' => '#form_layerinner_revslider-fluiddynamics-addon', 
						'focus' => "*[data-r='addOns.revslider-fluiddynamics-addon.colDirectionChooser']"
						
					)
					
				),

				// _______SIMULATION SETUP____________

				'simuLength' => array(
					
					'buttonTitle' => __('Duration', 'revslider-fluiddynamics-addon'), 
					'title' => __('Duration', 'revslider-fluiddynamics-addon'),
					'helpPath' => 'addOns.revslider-fluiddynamics-addon.simuLength', 
					'keywords' => array('addon', 'addons', 'fluiddynamics', 'fluiddynamics addon', 'Duration'), 
					'description' => __("Sets time period for the visible Part of the Simulation", 'revslider-fluiddynamics-addon'), 
					'helpStyle' => 'normal', 
					'article' => 'http://docs.themepunch.com/slider-revolution/fluiddynamics-addon/', 
					'video' => false,
					'section' => 'Layer Settings -> TheFluidDynamics',
					'highlight' => array(
						
						'dependencies' => array('layerselected::shape{{fluiddynamics}}'), 
						'menu' => "#module_layers_trigger, #gst_layer_revslider-fluiddynamics-addon", 
						'scrollTo' => '#form_layerinner_revslider-fluiddynamics-addon', 
						'focus' => "*[data-r='addOns.revslider-fluiddynamics-addon.simuLength']"
						
					)
					
				),

				'simuWait' => array(
					
					'buttonTitle' => __('Wait', 'revslider-fluiddynamics-addon'), 
					'title' => __('Wait', 'revslider-fluiddynamics-addon'),
					'helpPath' => 'addOns.revslider-fluiddynamics-addon.simuWait', 
					'keywords' => array('addon', 'addons', 'fluiddynamics', 'fluiddynamics addon', 'Wait'), 
					'description' => __("Sets time period between Simulations", 'revslider-fluiddynamics-addon'), 
					'helpStyle' => 'normal', 
					'article' => 'http://docs.themepunch.com/slider-revolution/fluiddynamics-addon/', 
					'video' => false,
					'section' => 'Layer Settings -> TheFluidDynamics',
					'highlight' => array(
						
						'dependencies' => array('layerselected::shape{{fluiddynamics}}'), 
						'menu' => "#module_layers_trigger, #gst_layer_revslider-fluiddynamics-addon", 
						'scrollTo' => '#form_layerinner_revslider-fluiddynamics-addon', 
						'focus' => "*[data-r='addOns.revslider-fluiddynamics-addon.simuWait']"
						
					)
					
				),

				'simuPower' => array(
					
					'buttonTitle' => __('Power', 'revslider-fluiddynamics-addon'), 
					'title' => __('Power', 'revslider-fluiddynamics-addon'),
					'helpPath' => 'addOns.revslider-fluiddynamics-addon.simuPower', 
					'keywords' => array('addon', 'addons', 'fluiddynamics', 'fluiddynamics addon', 'Power'), 
					'description' => __("Sets Power and Force of Simulation", 'revslider-fluiddynamics-addon'), 
					'helpStyle' => 'normal', 
					'article' => 'http://docs.themepunch.com/slider-revolution/fluiddynamics-addon/', 
					'video' => false,
					'section' => 'Layer Settings -> TheFluidDynamics',
					'highlight' => array(
						
						'dependencies' => array('layerselected::shape{{fluiddynamics}}'), 
						'menu' => "#module_layers_trigger, #gst_layer_revslider-fluiddynamics-addon", 
						'scrollTo' => '#form_layerinner_revslider-fluiddynamics-addon', 
						'focus' => "*[data-r='addOns.revslider-fluiddynamics-addon.simuPower']"
						
					)
					
				),

				'simuRadius' => array(
					
					'buttonTitle' => __('Radius', 'revslider-fluiddynamics-addon'), 
					'title' => __('Radius', 'revslider-fluiddynamics-addon'),
					'helpPath' => 'addOns.revslider-fluiddynamics-addon.simuRadius', 
					'keywords' => array('addon', 'addons', 'fluiddynamics', 'fluiddynamics addon', 'Radius'), 
					'description' => __("Sets the Radius for circular Simulations", 'revslider-fluiddynamics-addon'), 
					'helpStyle' => 'normal', 
					'article' => 'http://docs.themepunch.com/slider-revolution/fluiddynamics-addon/', 
					'video' => false,
					'section' => 'Layer Settings -> TheFluidDynamics',
					'highlight' => array(
						
						'dependencies' => array('layerselected::shape{{fluiddynamics}}'), 
						'menu' => "#module_layers_trigger, #gst_layer_revslider-fluiddynamics-addon", 
						'scrollTo' => '#form_layerinner_revslider-fluiddynamics-addon', 
						'focus' => "*[data-r='addOns.revslider-fluiddynamics-addon.simuRadius']"
						
					)
					
				),

				/**
				 * _____________________________________
				 * _______________COLOR_________________
				 * _____________________________________
				 */

				// _______MAIN COLOR SETUP___________

				'colorChangeType' => array(
					
					'buttonTitle' => __('Select', 'revslider-fluiddynamics-addon'), 
					'title' => __('Select', 'revslider-fluiddynamics-addon'),
					'helpPath' => 'addOns.revslider-fluiddynamics-addon.colorChangeType', 
					'keywords' => array('addon', 'addons', 'fluiddynamics', 'fluiddynamics addon', 'Select'), 
					'description' => __("Choose between timed and lifetime color fade options", 'revslider-fluiddynamics-addon'), 
					'helpStyle' => 'normal', 
					'article' => 'http://docs.themepunch.com/slider-revolution/fluiddynamics-addon/', 
					'video' => false,
					'section' => 'Layer Settings -> TheFluidDynamics',
					'highlight' => array(
						
						'dependencies' => array('layerselected::shape{{fluiddynamics}}'), 
						'menu' => "#module_layers_trigger, #gst_layer_revslider-fluiddynamics-addon", 
						'scrollTo' => '#form_layerinner_revslider-fluiddynamics-addon', 
						'focus' => "*[data-r='addOns.revslider-fluiddynamics-addon.colorChangeType']"
						
					)
					
				),

				'colorChangeLenth' => array(
					
					'buttonTitle' => __('Duration', 'revslider-fluiddynamics-addon'), 
					'title' => __('Duration', 'revslider-fluiddynamics-addon'),
					'helpPath' => 'addOns.revslider-fluiddynamics-addon.colorChangeLenth', 
					'keywords' => array('addon', 'addons', 'fluiddynamics', 'fluiddynamics addon', 'Duration'), 
					'description' => __("Length of time it takes to change colors", 'revslider-fluiddynamics-addon'), 
					'helpStyle' => 'normal', 
					'article' => 'http://docs.themepunch.com/slider-revolution/fluiddynamics-addon/', 
					'video' => false,
					'section' => 'Layer Settings -> TheFluidDynamics',
					'highlight' => array(
						
						'dependencies' => array('layerselected::shape{{fluiddynamics}}'), 
						'menu' => "#module_layers_trigger, #gst_layer_revslider-fluiddynamics-addon", 
						'scrollTo' => '#form_layerinner_revslider-fluiddynamics-addon', 
						'focus' => "*[data-r='addOns.revslider-fluiddynamics-addon.colorChangeLenth']"
						
					)
					
				),

				'colorFull' => array(
					
					'buttonTitle' => __('Colorfull', 'revslider-fluiddynamics-addon'), 
					'title' => __('Colorfull', 'revslider-fluiddynamics-addon'),
					'helpPath' => 'addOns.revslider-fluiddynamics-addon.colorFull', 
					'keywords' => array('addon', 'addons', 'fluiddynamics', 'fluiddynamics addon', 'Colorfull'), 
					'description' => __("Randomly generate colors for the fluid", 'revslider-fluiddynamics-addon'), 
					'helpStyle' => 'normal', 
					'article' => 'http://docs.themepunch.com/slider-revolution/fluiddynamics-addon/', 
					'video' => false,
					'section' => 'Layer Settings -> TheFluidDynamics',
					'highlight' => array(
						
						'dependencies' => array('layerselected::shape{{fluiddynamics}}'), 
						'menu' => "#module_layers_trigger, #gst_layer_revslider-fluiddynamics-addon", 
						'scrollTo' => '#form_layerinner_revslider-fluiddynamics-addon', 
						'focus' => "*[data-r='addOns.revslider-fluiddynamics-addon.colorFull']"
						
					)
					
				),

				'allColorCount' => array(
					
					'buttonTitle' => __('Colors Used', 'revslider-fluiddynamics-addon'), 
					'title' => __('Colors Used', 'revslider-fluiddynamics-addon'),
					'helpPath' => 'addOns.revslider-fluiddynamics-addon.allColorCount', 
					'keywords' => array('addon', 'addons', 'fluiddynamics', 'fluiddynamics addon', 'Colors Used'), 
					'description' => __("The Fluid Effect will cycle through all colors set by the User", 'revslider-fluiddynamics-addon'), 
					'helpStyle' => 'normal', 
					'article' => 'http://docs.themepunch.com/slider-revolution/fluiddynamics-addon/', 
					'video' => false,
					'section' => 'Layer Settings -> TheFluidDynamics',
					'highlight' => array(
						
						'dependencies' => array('layerselected::shape{{fluiddynamics}}'), 
						'menu' => "#module_layers_trigger, #gst_layer_revslider-fluiddynamics-addon", 
						'scrollTo' => '#form_layerinner_revslider-fluiddynamics-addon', 
						'focus' => "*[data-r='addOns.revslider-fluiddynamics-addon.allColorCount']"
						
					)
					
				),

				'maxOpacityValue' => array(
					
					'buttonTitle' => __('Max Opacity', 'revslider-fluiddynamics-addon'), 
					'title' => __('Max Opacity', 'revslider-fluiddynamics-addon'),
					'helpPath' => 'addOns.revslider-fluiddynamics-addon.maxOpacityValue', 
					'keywords' => array('addon', 'addons', 'fluiddynamics', 'fluiddynamics addon', 'Max Opacity'), 
					'description' => __("Limits the Opacity of all Colors in the Simulation to a set Value", 'revslider-fluiddynamics-addon'), 
					'helpStyle' => 'normal', 
					'article' => 'http://docs.themepunch.com/slider-revolution/fluiddynamics-addon/', 
					'video' => false,
					'section' => 'Layer Settings -> TheFluidDynamics',
					'highlight' => array(
						
						'dependencies' => array('layerselected::shape{{fluiddynamics}}'), 
						'menu' => "#module_layers_trigger, #gst_layer_revslider-fluiddynamics-addon", 
						'scrollTo' => '#form_layerinner_revslider-fluiddynamics-addon', 
						'focus' => "*[data-r='addOns.revslider-fluiddynamics-addon.maxOpacityValue']"
						
					)
					
				),
			)
			
		);
		
	}

}
	
?>