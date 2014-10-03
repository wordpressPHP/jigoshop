<?php

namespace Jigoshop\Core;

use Jigoshop\Frontend\Page;
use Symfony\Component\DependencyInjection\Container;
use WPAL\Wordpress;

/**
 * Factory that decides what current page is and provides proper page object.
 *
 * @package Jigoshop\Core
 */
class PageResolver
{
	/** @var \WPAL\Wordpress */
	private $wp;
	/** @var \Jigoshop\Core\Pages */
	private $pages;

	public function __construct(Wordpress $wp, Pages $pages)
	{
		$this->wp = $wp;
		$this->pages = $pages;
	}

	public function resolve(Container $container)
	{
		if (defined('DOING_AJAX') && DOING_AJAX) {
			// Instantiate page to install Ajax actions
			$this->getPage($container);
		} else {
			$that = $this;
			$this->wp->addAction('template_redirect', function () use ($container, $that){
				$page = $that->getPage($container);
				$container->set('jigoshop.page.current', $page);
				$container->get('jigoshop.template')->setPage($page);
			});
		}
	}

	public function getPage(Container $container)
	{
		if (!$this->pages->isJigoshop() && !$this->pages->isAjax()) {
			return null;
		}

		if ($this->pages->isCart()) {
			return $container->get('jigoshop.page.cart');
		}

		if ($this->pages->isProductList()) {
			return $container->get('jigoshop.page.product_list');
		}

		if ($this->pages->isProduct()) {
			return $container->get('jigoshop.page.product');
		}
	}
}