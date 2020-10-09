<?php
/**
 * @version    $version 5.0.0 Peter Bui  $
 * @copyright    Copyright (C) 2020 PB Web Development. All rights reserved.
 * @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * Updated    6th October 2020
 *
 * Twitter: @astroboysoup
 * Blog: https://pbwebdev.com
 * Email: mail@pbwebdev.com
 *
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 *
 */

// no direct access
defined('_JEXEC') or die('Restricted access');


class plgSystemGoogleAnalyticsPro extends JPlugin {

	var $document = null;
	private $verify = null;
	private $trackingID = null;
	private $trackingID2 = null;
	private $containerID = null;

	private $userIDTracking = null;
	private $pageviewTrackingD = null;
	private $siteCurrency = null;
	private $userPerformanceTiming = null;
	private $enhancedLinkAttribution = null;
	private $ipAnonymize = null;

	private $buffer = null;
	private $output = null;
	protected $app;

	function plgGoogleAnalyticsPro( $subject, $params)
	{
		parent::__construct($subject, $params);
	}

	//Generate all the output
	function onBeforeCompileHead()
	{
		$this->document = JFactory::getDocument();
		if ($this->app->isClient('administrator') || $this->document->getType() != 'html') {
			return;
		}

		if($this->params->get('verify')){
			$this->output .= $this->webmasterVerify();
		}

		if($this->params->get('trackingID')) {
			$this->output .= $this->googleAnalyticsTag();
		}

		if($this->params->get('containerID')) {
			$this->output .= $this->googleTagManager();
		}

		$this->document->addCustomTag($this->output);
	}

	function onAfterRender()
	{
		if ($this->app->isClient('administrator') || $this->document->getType() != 'html') {
			return;
		}

		if($this->params->get('containerID')) {
			$buffer = JFactory::getApplication()->getBody();
			$noJSOutput = '
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id='.$this->params->get('containerID').'" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>';
			$buffer= str_ireplace('</body>',$noJSOutput.'</body>',$buffer);
			JFactory::getApplication()->setBody($buffer);
		}
		return;
	}

	private function webmasterVerify() {
		$this->verify = $this->params->get('verify');
		$this->buffer = '<meta name="google-site-verification" content="'.$this->verify.'" />
		';
		return $this->buffer;
	}




	private function userIDTracking() {
		$this->verify = $this->params->get('userIDTracking');
		$userID = '1';
		$this->buffer = "gtag('set', {'user_id': '".$userID."'});
		";
		return $this->buffer;
	}


	private function pageviewTrackingD() {
		$this->verify = $this->params->get('pageviewTrackingD');
		$this->buffer = '<meta name="google-site-verification" content="'.$this->verify.'" />
		';
		return $this->buffer;
	}

	private function siteCurrency() {
		$this->verify = $this->params->get('siteCurrency');
		$this->buffer = '<meta name="google-site-verification" content="'.$this->verify.'" />
		';
		return $this->buffer;
	}

	private function userPerformanceTiming() {
		$this->verify = $this->params->get('userPerformanceTiming');
		$this->buffer = '<meta name="google-site-verification" content="'.$this->verify.'" />
		';
		return $this->buffer;
	}

	private function enhancedLinkAttribution() {
		$this->verify = $this->params->get('enhancedLinkAttribution');
		$this->buffer = '<meta name="google-site-verification" content="'.$this->verify.'" />
		';
		return $this->buffer;
	}




	private function ipAnonymize() {
		$this->verify = $this->params->get('ipAnonymize');
		$this->buffer = '<meta name="google-site-verification" content="'.$this->verify.'" />
		';
		return $this->buffer;
	}








	private function googleAnalyticsTag() {
		$this->trackingID = $this->params->get('trackingID');
		$this->buffer = '
	<script async src="https://www.googletagmanager.com/gtag/js?id='.$this->trackingID.'"></script>
	<script>
	  window.dataLayer = window.dataLayer || [];
	  function gtag(){dataLayer.push(arguments);}
	  gtag(\'js\', new Date());
	  gtag(\'config\', \''.$this->trackingID.'\');
	</script>
		';
		return $this->buffer;
	}

	private function googleTagManager() {
		$this->containerID = $this->params->get('containerID');
		$this->buffer = '
	<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({\'gtm.start\':
	new Date().getTime(),event:\'gtm.js\'});var f=d.getElementsByTagName(s)[0],
	j=d.createElement(s),dl=l!=\'dataLayer\'?\'&l=\'+l:\'\';j.async=true;j.src=
	\'https://www.googletagmanager.com/gtm.js?id=\'+i+dl;f.parentNode.insertBefore(j,f);
	})(window,document,\'script\',\'dataLayer\',\''.$this->containerID.'\');</script>
	';
		return $this->buffer;
	}
}
?>