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
	private $forceSLL = null;
	private $pageviewTracking = null;
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

		if($this->params->get('trackingID')||$this->params->get('trackingID2')) {
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
		$user = JFactory::getUser();
		
		return $user->id;
	}

	private function forceSSL() {
		return 'gtag(\'config\', {\'forceSSL\': true }} );
	';
	}

	private function pageviewTracking() {
		return 'gtag(\'config\', {\'send_page_view\': false} );
	';
	}

	private function siteCurrency() {
		return 'gtag(\'config\', {\'currency\': \''.$this->siteCurrency.'\'});
	';

	}

	private function userPerformanceTiming() {
			return 'if (window.performance) {
	var timeSincePageLoad = Math.round(performance.now());
		gtag(\'event\', \'timing_complete\', {
		    \'name\': \'load\',
		    \'value\': timeSincePageLoad,
		    \'event_category\': \'JS Dependencies\'
	    });
    }
    ';
	}

	private function enhancedLinkAttribution() {
		return 'gtag(\'config\', {\'link_attribution\': true} );
	';
	}

	private function ipAnonymize() {
		return 'gtag(\'config\', {\'anonymize_ip\': true} );
	';
	}

	private function isLoggedIn()
	{
		if(JFactory::getUser()->id){
			return true;
		};
		return false;
	}

	private function googleAnalyticsTag() {
	$this->trackingID = $this->params->get('trackingID');
	$this->trackingID2 = $this->params->get('trackingID2');
	$this->userIDTracking = $this->params->get('userIDTracking');
	$this->forceSSL = $this->params->get('forceSSL');
	$this->pageviewTracking = $this->params->get('pageviewTracking');
	$this->siteCurrency = $this->params->get('siteCurrency');
	$this->enhancedLinkAttribution = $this->params->get('enhancedLinkAttribution');
	$this->userPerformanceTiming = $this->params->get('userPerformanceTiming');
	$this->ipAnonymize = $this->params->get('ipAnonymize');

	$this->buffer = '
	<script async src="https://www.googletagmanager.com/gtag/js?id='.$this->trackingID.'"></script>
	<script>
	window.dataLayer = window.dataLayer || [];
	function gtag(){dataLayer.push(arguments);}
	gtag(\'js\', new Date());
	';

	$config = array();

	if($this->userIDTracking && $this->isLoggedIn()){
		$config['user_id'] = $this->userIDTracking();
	}

	if($this->pageviewTracking){
		$config['send_page_view'] = false;
	}

	if($this->siteCurrency){
		$config['currency'] = $this->siteCurrency;
	}

	if($this->userPerformanceTiming){
		$this->buffer .= $this->userPerformanceTiming();
	}

	if($this->enhancedLinkAttribution){
		$config['link_attribution'] = true;
	}

	if($this->ipAnonymize){
		$config['anonymize_ip'] = true;
	}

	if($this->forceSSL){
		$config['forceSSL'] = true;
	}

	$this->buffer .= 'gtag(\'config\', \''.$this->trackingID.'\', '.json_encode($config).');
';

	if($this->trackingID2){
		$this->buffer .= '	gtag(\'config\', \''.$this->trackingID2.'\', '.json_encode($config).');
';
	}

	$this->buffer .= '</script>';

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