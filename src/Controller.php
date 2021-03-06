<?php


namespace Eagle\Mvc;

use Phalcon\Dispatcher;
use Phalcon\Http\ResponseInterface;


class Controller extends \Phalcon\Mvc\Controller {

	/** @var array Snippet-identifiers to redraw */

	protected $toRedraw = [];

	/** @var array Snippet contents redrawed */

	protected $redrawedSnippets = [];

	/**
	 * Event for processing request after route execution
	 *
	 * @param Dispatcher $dispatcher
	 *
	 * @throws UnknownProcess
	 */

	public function afterExecuteRoute() {

		if(!is_null($this->request->getQuery('process'))) {

			$process = $this->request->getQuery('process');

			if(!method_exists($this, $process . 'Process'))
				throw new UnknownProcess('Cannot find process with identifier `' . $process .'`.');
			else {

				$this->{$process . 'Process'}();

				if($this->request->isAjax())
					$this->sendSnippetsResponse();
			}
		}

		if(!is_null($this->request->get('form_uid')) && $this->request->isAjax())
			$this->sendSnippetsResponse();

	}

	private function sendSnippetsResponse() {

		// Redraw snippets if request is Ajax

		$view = $this->view;

		$view->start();

		$view->render($this->dispatcher->getControllerName(), $this->dispatcher->getActionName());

		$view->finish();

		$this->view->disable();

		foreach($this->toRedraw as $snippetId)
			$this->redrawedSnippets[$snippetId] = $this->getSnippetContent($snippetId, $view->getContent());

		$this->response->setJsonContent([
			'eagleProcess' => [
				'snippets' => $this->redrawedSnippets
			],
			'data' => [
				'post' => $_POST,
				'get' => $_GET,
				'request' => $_REQUEST
			]
		]);

		$this->response->send();

	}

	/**
	 * Get snippet content for redrawing
	 *
	 * @param $snippetId
	 * @param $actionContent
	 *
	 * @return bool
	 */

	protected function getSnippetContent($snippetId, $actionContent) {

		$content = false;

		$doc = new \DOMDocument();
		@$doc->loadHTML('<?xml encoding="utf-8" ?>' . $actionContent);

		$finder = new \DOMXPath($doc);

		foreach($finder->evaluate('//div[@id="snippet-' . $snippetId . '"]/node()') as $childNode)
			$content .= $doc->saveHtml($childNode);

		return $content;
	}

	/**
	 * Redraw snippet
	 *
	 * @param $snippetName
	 */

	public function redrawSnippet($snippetName) {

		$this->toRedraw[] = $snippetName;
	}

	/**
	 * Redirect by HTTP to another action or URL
	 *
	 * @param mixed $location
	 * @param bool $externalRedirect
	 * @param int $statusCode
	 * @return ResponseInterface
	 */

	public function redirect($location = null, $externalRedirect = false, $statusCode = 302) {

		$this->response->redirect($location, $externalRedirect, $statusCode);
	}

}