<?php 

$app->get('/', function() use ($app) {
    $languagePrefix = Language::browserLanguage($app['db']);

    return $app->redirect($app['url_generator']->generate("index_{$languagePrefix}"));
});

$app->get('/my-schedule', function () use ($app) {
    $languagePrefix = Language::browserLanguage($app['db']);

    return $app->redirect($app['url_generator']->generate("my-schedule-error_{$languagePrefix}"));
});

$app->get('/thank-you/{child_id}/{hash}', function ($child_id, $hash = "") use ($app) {
    $languagePrefix = Language::browserLanguage($app['db']);

    return $app->redirect($app['url_generator']->generate("thank-you_{$languagePrefix}", ['child_id' => $child_id, 'hash' => $hash]));
});

$app->get('/thank-you/{child_id}', function ($child_id) use ($app) {
    $languagePrefix = Language::browserLanguage($app['db']);

    return $app->redirect($app['url_generator']->generate("thank-you-error_{$languagePrefix}", ['child_id' => $child_id]));
});

$app->get('/unsubscribe/{child_id}/{hash}', function (Request $request, $child_id, $hash) use ($app) {
    $languagePrefix = Language::browserLanguage($app['db']);

    return $app->redirect($app['url_generator']->generate("unsubscribe_{$languagePrefix}", ['child_id' => $child_id, 'hash' => $hash]));
});

foreach ($languages as $language) {
    $shortCode = $language->shortCode;
    $app->mount('/' . $shortCode, new VaxTimeControllerProvider($shortCode));
}