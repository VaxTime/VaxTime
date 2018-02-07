<?php
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => VAX_TEMPLATES,
));

$txFunction = new Twig_SimpleFunction('tx', function($id, $params = []) {
  return Translation::instance()->show($id, $params);
});

$linkOpenFunction = new Twig_SimpleFunction('linkOpen', function($link, $target = "_self") {
  return '<a target="' . $target . '" href="' . $link . '">';
});

$linkCloseFunction = new Twig_SimpleFunction('linkClose', function() {
  return '</a>';
});

$app['twig']->addFunction($txFunction);
$app['twig']->addFunction($linkOpenFunction);
$app['twig']->addFunction($linkCloseFunction);