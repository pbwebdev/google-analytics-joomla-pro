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

	private $document = null;
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

		$output = '';

		if ($this->params->get('verify')){
			$output .= $this->webmasterVerify();
		}

		if ($this->params->get('trackingID') || $this->params->get('trackingID2')) {
			$output .= $this->googleAnalyticsTag();
		}

		if ($this->params->get('containerID')) {
			$output .= $this->googleTagManager();
		}

		$this->document->addCustomTag($output);
	}

	function onAfterRender()
	{
		if ($this->app->isClient('administrator') || $this->document->getType() != 'html') {
			return;
		}

		if ($this->params->get('containerID')) {
			$buffer = JFactory::getApplication()->getBody();
			$noJSOutput = '<noscript><iframe src="https://www.googletagmanager.com/ns.html?id='.$this->params->get('containerID').'" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>';
			$buffer= str_ireplace('</body>',$noJSOutput.'</body>',$buffer);
			JFactory::getApplication()->setBody($buffer);
		}
	}

	private function webmasterVerify()
	{
		$verify = $this->params->get('verify');

		return '<meta name="google-site-verification" content="'.$verify.'" />';
	}

	private function userIDTracking()
	{
		$user = JFactory::getUser();
		
		return $user->id;
	}

	private function userPerformanceTiming()
	{
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

	private function isLoggedIn()
	{
		return JFactory::getUser()->id ? true : false;
	}

	private function googleAnalyticsTag()
	{
		$trackingID = $this->params->get('trackingID');
		$trackingID2 = $this->params->get('trackingID2');
		$userIDTracking = $this->params->get('userIDTracking');
		$forceSSL = $this->params->get('forceSSL');
		$pageviewTracking = $this->params->get('pageviewTracking');
		$siteCurrency = $this->params->get('siteCurrency');
		$enhancedLinkAttribution = $this->params->get('enhancedLinkAttribution');
		$userPerformanceTiming = $this->params->get('userPerformanceTiming');
		$ipAnonymize = $this->params->get('ipAnonymize');

		$buffer = '
		<script async src="https://www.googletagmanager.com/gtag/js?id='.$trackingID.'"></script>
		<script>
		window.dataLayer = window.dataLayer || [];
		function gtag(){dataLayer.push(arguments);}
		gtag(\'js\', new Date());
		';

		$config = array();

		if ($userIDTracking && $this->isLoggedIn()){
			$config['user_id'] = $this->userIDTracking();
		}

		if ($pageviewTracking){
			$config['send_page_view'] = false;
		}

		if ($siteCurrency){
			$config['currency'] = $siteCurrency;
		}

		if ($userPerformanceTiming){
			$buffer .= $this->userPerformanceTiming();
		}

		if ($enhancedLinkAttribution){
			$config['link_attribution'] = true;
		}

		if ($ipAnonymize){
			$config['anonymize_ip'] = true;
		}

		if ($forceSSL){
			$config['forceSSL'] = true;
		}

		$buffer .= '		gtag(\'config\', \''.$trackingID.'\', '.json_encode($config).');
';

		if ($trackingID2){
			$buffer .= '		gtag(\'config\', \''.$trackingID2.'\', '.json_encode($config).');
';
		}

		$buffer .= '		</script>';

		return $buffer;
	}

	private function googleTagManager() {
		$containerID = $this->params->get('containerID');

		return '
		<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({\'gtm.start\':
		new Date().getTime(),event:\'gtm.js\'});var f=d.getElementsByTagName(s)[0],
		j=d.createElement(s),dl=l!=\'dataLayer\'?\'&l=\'+l:\'\';j.async=true;j.src=
		\'https://www.googletagmanager.com/gtm.js?id=\'+i+dl;f.parentNode.insertBefore(j,f);
		})(window,document,\'script\',\'dataLayer\',\''.$containerID.'\');</script>';
	}
}
?>