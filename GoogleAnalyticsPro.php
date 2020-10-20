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

	private $user_id = 0;
	private $config = array();


	function plgGoogleAnalyticsPro($subject, $params)
	{
		parent::__construct($subject, $params);
	}

	function onAfterInitialise()
	{
		$this->document = JFactory::getDocument();
		$this->user_id = JFactory::getUser()->id;

		$this->config['verify'] = $this->params->get('verify');
		$this->config['trackingID'] = $this->params->get('trackingID');
		$this->config['trackingID2'] = $this->params->get('trackingID2');
		$this->config['containerID'] = $this->params->get('containerID');
		$this->config['userIDTracking'] = $this->params->get('userIDTracking');
		$this->config['forceSSL'] = $this->params->get('forceSSL');
		$this->config['pageviewTracking'] = $this->params->get('pageviewTracking');
		$this->config['siteCurrency'] = $this->params->get('siteCurrency');
		$this->config['enhancedLinkAttribution'] = $this->params->get('enhancedLinkAttribution');
		$this->config['userPerformanceTiming'] = $this->params->get('userPerformanceTiming');
		$this->config['ipAnonymize'] = $this->params->get('ipAnonymize');
	}

	//Generate all the output
	function onBeforeCompileHead()
	{
		if ($this->app->isClient('administrator') || $this->document->getType() != 'html') {
			return;
		}

		$output = '';

		if ($this->config['verify']){
			$output .= $this->webmasterVerify();
		}

		if ($this->config['trackingID'] || $this->config['trackingID2']) {
			$output .= $this->googleAnalyticsTag();
		}

		if ($this->config['containerID']) {
			$output .= $this->googleTagManager();
		}

		$this->document->addCustomTag($output);
	}

	function onAfterRender()
	{
		if ($this->app->isClient('administrator') || $this->document->getType() != 'html') {
			return;
		}

		if ($this->config['containerID']) {
			$buffer = JFactory::getApplication()->getBody();
			$script = '	<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=' . $this->config['containerID'] . '" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
';
			$buffer = str_ireplace('</body>', $script . '</body>', $buffer);

			JFactory::getApplication()->setBody($buffer);
		}
	}

	private function webmasterVerify()
	{
		return '<meta name="google-site-verification" content="' . $this->config['verify'] . '" />
';
	}

	private function userPerformanceTiming()
	{
		return '	if (window.performance) {
		var timeSincePageLoad = Math.round(performance.now());
		gtag(\'event\', \'timing_complete\', {
			\'name\': \'load\',
			\'value\': timeSincePageLoad,
			\'event_category\': \'JS Dependencies\'
		});
	}
';
	}

	private function googleAnalyticsTag()
	{
		$buffer = '
	<script async src="https://www.googletagmanager.com/gtag/js?id=' . $this->config['trackingID'] . '"></script>

	<script>
	window.dataLayer = window.dataLayer || [];
	function gtag(){dataLayer.push(arguments);}
	gtag(\'js\', new Date());
';

		$config = array();

		if ($this->config['userIDTracking'] && $this->user_id){
			$config['user_id'] = $this->user_id;
		}

		if ($this->config['pageviewTracking']){
			$config['send_page_view'] = false;
		}

		if ($this->config['siteCurrency']){
			$config['currency'] = $this->config['siteCurrency'];
		}

		if ($this->config['userPerformanceTiming']){
			$buffer .= $this->userPerformanceTiming();
		}

		if ($this->config['enhancedLinkAttribution']){
			$config['link_attribution'] = true;
		}

		if ($this->config['ipAnonymize']){
			$config['anonymize_ip'] = true;
		}

		if ($this->config['forceSSL']){
			$config['forceSSL'] = true;
		}

		$buffer .= '	gtag(\'config\', \'' . $this->config['trackingID'] . '\', ' . json_encode($config) . ');
';

		if ($this->config['trackingID2']){
			$buffer .= '	gtag(\'config\', \'' . $this->config['trackingID2'] . '\', ' . json_encode($config) . ');
';
		}

		$buffer .= '	</script>
';

		return $buffer;
	}

	private function googleTagManager() {
		return '
	<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({\'gtm.start\':
	new Date().getTime(),event:\'gtm.js\'});var f=d.getElementsByTagName(s)[0],
	j=d.createElement(s),dl=l!=\'dataLayer\'?\'&l=\'+l:\'\';j.async=true;j.src=
	\'https://www.googletagmanager.com/gtm.js?id=\'+i+dl;f.parentNode.insertBefore(j,f);
	})(window,document,\'script\',\'dataLayer\',\'' . $this->config['containerID'] . '\');</script>';
	}
}
?>