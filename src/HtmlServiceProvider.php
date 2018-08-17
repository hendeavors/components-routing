<?php

namespace Endeavors\Components\Routing;

use Illuminate\Html\HtmlServiceProvider as OriginalHtmlServiceProvider;
use Illuminate\Html\HtmlBuilder;
use Illuminate\Html\FormBuilder;

class HtmlServiceProvider extends OriginalHtmlServiceProvider
{
	/**
	 * Register the HTML builder instance.
	 *
	 * @return void
	 */
	protected function registerHtmlBuilder()
	{
		$this->app->bindShared('html', function($app)
		{
			return new HtmlBuilder($app['url']->original());
		});
	}

	/**
	 * Register the form builder instance.
	 *
	 * @return void
	 */
	protected function registerFormBuilder()
	{
		$this->app->bindShared('form', function($app)
		{
			$form = new FormBuilder($app['html'], $app['url']->original(), $app['session.store']->getToken());

			return $form->setSessionStore($app['session.store']);
		});
	}
}
