<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      http://www.themepunch.com/
 * @copyright 2024 ThemePunch
 */

if( !defined( 'ABSPATH') ) exit();

class RsTheFluidDynamicsSlideFront extends RevSliderFunctions {
	
	private $title;
	
	public function __construct($title) {
		
		$this->title = $title;
		add_action('revslider_add_layer_attributes', array($this, 'write_layer_attributes'), 10, 3);
		add_filter('revslider_putCreativeLayer', array($this, 'check_fluiddynamics'), 10, 3);
	
	}
	
	// HANDLE ALL TRUE/FALSE
	private function isFalse($val) {
	
		$true = array(true, 'on', 1, '1', 'true');
		return !in_array($val, $true);
	
	}
	
	private function isEnabled($slider){
		
		$settings = $slider->get_params();
		$enabled = $this->get_val($settings, array('addOns', 'revslider-' . $this->title . '-addon', 'enable'), false);
		
		return ($this->isFalse($enabled)) ? false : true;
	}
	
	// removes fluiddynamics layers that may exist if the AddOn is not officially enabled
	public function check_fluiddynamics($layers, $output, $static_slide) {
		
		$slider = $this->get_val($output, 'slider', false);
		if(empty($slider)) return;
		// addon enabled
		if ($this->isEnabled($slider)) return $layers;
		$ar = array();
		foreach($layers as $layer) {
			$isTheFluidDynamics = false;
			if(array_key_exists('subtype', $layer)) {
				$fluiddynamics = $this->get_val($layer, 'subtype', false);
				$isTheFluidDynamics = $fluiddynamics === 'fluiddynamics';
			}
			if(!$isTheFluidDynamics) $ar[] = $layer;
		}
		return $ar;
	}

	private function aO($val,$d,$s) {
		return $val==$d ? '' : $s.':'.$val.';';
	}

	private function convertColors($a) {
		if(!empty($a) && is_array($a)){
			foreach($a as $c => $v){
				$a[$c] = RSColorpicker::get($v);				
			}
		}				
		
		return $a;
	}
	
		
	public function write_layer_attributes($layer, $slide, $slider) {
		
		// addon enabled
		$enabled = $this->isEnabled($slider);
		if(empty($enabled)) return;		
		$subtype = $this->get_val($layer, 'subtype', '');
		if(!$subtype || $subtype !== 'fluiddynamics') return;
				
		$addOn = $this->get_val($layer, ['addOns', 'revslider-' . $this->title . '-addon'], false);
		if(!$addOn) return;
		
		//MAIN
		$followMouse = $this->get_val($addOn, 'followMouse', "mGenerator");
		$followAMGen = $this->get_val($addOn, 'followAMGen', "mGenerator");
		$curlVal = $this->get_val($addOn, 'curlVal', 15);
		$mousePower = $this->get_val($addOn, 'mousePower', 20);
		$splatRadius = $this->get_val($addOn, 'splatRadius', 20);
		$densityDissipation = $this->get_val($addOn, 'densityDissipation', 69);
		$velocityDissipation = $this->get_val($addOn, 'velocityDissipation', 96);
		$pressureDissipation = $this->get_val($addOn, 'pressureDissipation', 75);
		$qualityFluid = $this->get_val($addOn, 'qualityFluid', 100);
		$dprFluid = $this->get_val($addOn, 'dprFluid', "auto");
		$dprOnMobile = $this->get_val($addOn, 'dprOnMobile', false);
		$qualityFluidMobile = $this->get_val($addOn, 'qualityFluidMobile', 100);
		$dprFluidMobile = $this->get_val($addOn, 'dprFluidMobile', 1);

		//SIMU
		$automateSel = $this->get_val($addOn, 'automateSel', "off");
		$directionChooser = $this->get_val($addOn, 'directionChooser', "lr");
		$colDirectionChooser = $this->get_val($addOn, 'colDirectionChooser', "lr");
		$simuDelay = $this->get_val($addOn, 'simuDelay', 0);
		$simuLength = $this->get_val($addOn, 'simuLength', 36);
		$simuWait = $this->get_val($addOn, 'simuWait', 40);
		$simuPower = $this->get_val($addOn, 'simuPower', 50);
		$simuRadius = $this->get_val($addOn, 'simuRadius', 50);
		$vecLength = $this->get_val($addOn, 'vecLength', 50);

		//COLOR
		$allColorCount = $this->get_val($addOn, 'allColorCount', 2);
		$fd_color1 = $this->get_val($addOn, 'fd_color1', "#ff2525");
		$fd_color2 = $this->get_val($addOn, 'fd_color2', "#2d46fd");
		$fd_color3 = $this->get_val($addOn, 'fd_color3', "#2dfd67");
		$fd_color4 = $this->get_val($addOn, 'fd_color4', "#ffffff");
		$fd_color5 = $this->get_val($addOn, 'fd_color5', "#ffffff");
		$fd_color6 = $this->get_val($addOn, 'fd_color6', "#ffffff");
		$maxOpacityValue = $this->get_val($addOn, 'maxOpacityValue', 80);
		$colorFade = $this->get_val($addOn, 'colorFade', false);
		$colorChangeType = $this->get_val($addOn, 'colorChangeType', "timed");
		$colorChangeLenth = $this->get_val($addOn, 'colorChangeLenth', 1.5);
		$colorFull = $this->get_val($addOn, 'colorFull', false);
		$glow = $this->get_val($addOn, 'glow', false);
	
		//MAIN
		$datas = '';
		if($followMouse != "mGenerator") $datas .= 'mf:'.$followMouse.';';
		if($followAMGen != "mGenerator") $datas .= 'mfa:'.$followAMGen.';';
		if($curlVal != 15) $datas .= 'mc:'.$curlVal.';';
		if($mousePower != 20) $datas .= 'mp:'.$mousePower.';';
		if($splatRadius != 20) $datas .= 'ms:'.$splatRadius.';';
		if($densityDissipation != 69) $datas .= 'md:'.$densityDissipation.';';
		if($velocityDissipation != 96) $datas .= 'mv:'.$velocityDissipation.';';
		if($pressureDissipation != 75) $datas .= 'mr:'.$pressureDissipation.';';
		if($qualityFluid != 100) $datas .= 'mq:'.$qualityFluid.';';
		if($dprFluid != "auto") $datas .= 'mdpr:'.$dprFluid.';';
		if($dprOnMobile != false) $datas .= 'mom:'.$dprOnMobile.';';
		if($qualityFluidMobile != 100) $datas .= 'mqm:'.$qualityFluidMobile.';';
		if($dprFluidMobile != 1) $datas .= 'mdpm:'.$dprFluidMobile.';';

		//SIMU
		if($automateSel != "off") $datas .= 'sa:'.$automateSel.';';
		if($directionChooser != "lr") $datas .= 'sd:'.$directionChooser.';';
		if($colDirectionChooser != "lr") $datas .= 'sc:'.$colDirectionChooser.';';
		if($simuDelay != 0) $datas .= 'sy:'.$simuDelay.';';
		if($simuLength != 36) $datas .= 'sl:'.$simuLength.';';
		if($simuWait != 40) $datas .= 'sw:'.$simuWait.';';
		if($simuPower != 50) $datas .= 'sp:'.$simuPower.';';
		if($simuRadius != 50) $datas .= 'sr:'.$simuRadius.';';
		if($vecLength != 50) $datas .= 'sv:'.$vecLength.';';

		//COLOR
		if($allColorCount != 2) $datas .= 'cc:'.$allColorCount.';';
		if($fd_color1 != "#ff2525") $datas .= 'c1:'.$fd_color1.';';
		if($fd_color2 != "#2d46fd") $datas .= 'c2:'.$fd_color2.';';
		if($fd_color3 != "#2dfd67") $datas .= 'c3:'.$fd_color3.';';
		if($fd_color4 != "#ffffff") $datas .= 'c4:'.$fd_color4.';';
		if($fd_color5 != "#ffffff") $datas .= 'c5:'.$fd_color5.';';
		if($fd_color6 != "#ffffff") $datas .= 'c6:'.$fd_color6.';';
		if($maxOpacityValue != 80) $datas .= 'cm:'.$maxOpacityValue.';';
		if($colorFade != false) $datas .= 'cf:'.$colorFade.';';
		if($colorChangeType != "timed") $datas .= 'ct:'.$colorChangeType.';';
		if($colorChangeLenth != 1.5) $datas .= 'cl:'.$colorChangeLenth.';';
		if($colorFull != false) $datas .= 'cu:'.$colorFull.';';
		if($glow != false) $datas .= 'cg:'.$glow.';';

		echo RS_T8 . " data-wpsdata='" .$datas."'\n";
	}
	
}
?>