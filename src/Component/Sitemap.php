<?php

/**
 * This file is part of inifnityloop-dev/sitemap (https://www.infinityloop.dev)
 *
 * Copyright (c) 2018 Václav Pelíšek (peldax@infinityloop.dev)
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license. For more information, see
 * <https://www.peldax.com>.
 */

declare(strict_types = 1);

namespace Nepttune\Component;

final class Sitemap extends \Nette\Application\UI\Control
{
    private static $defaultConfig = [
        'hreflang' => true,
    ];

    /** @var array */
    private $config;
    
    /** @var \Nette\DI\Container */
    private $context;

    /** @var \Nette\Caching\Cache */
    private $cache;

    public function __construct(array $config = [], \Nette\DI\Container $context, \Nette\Caching\IStorage $storage)
    {
        $this->config = \array_merge(self::$defaultConfig, $config);
        $this->context = $context;
        $this->cache = new \Nette\Caching\Cache($storage, 'Nepttune.Sitemap');
    }

    protected function beforeRender() : void
    {
        $this->template->hreflang = $this->config['hreflang'];
        $this->template->pages = $this->cache->call([$this, 'getPages']);
        $this->template->date = new \Nette\Utils\DateTime();
    }
    
    public function render() : void
    {
        $this->beforeRender();
        $this->template->setFile(__DIR__ . '/Sitemap.latte');
        $this->template->render();
    }

    public function getPages() : array
    {
        $pages = [];

        foreach ($this->context->findByType(\Nepttune\TI\ISitemap::class) as $name)
        {
            /** @var \Nepttune\TI\ISitemap $presenter */
            $presenter = $this->context->getService($name);
            $pages = \array_merge($pages, $presenter->getSitemap());
        }

        return $pages;
    }
}
