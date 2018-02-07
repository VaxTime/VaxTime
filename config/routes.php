<?php 

$app->get('/', function() use ($app) {
  return $app->redirect($app['url_generator']->generate("index_en"));
});

$app->get('/my-schedule', function() use ($app) {
  return $app->redirect($app['url_generator']->generate("my-schedule-error_en"));
});

$app->get('/thank-you/{child_id}/{hash}', function($child_id, $hash = "") use ($app) {
  return $app->redirect($app['url_generator']->generate("thank-you_en", ['child_id' => $child_id, 'hash' => $hash]));
});

$app->get('/thank-you/{child_id}', function($child_id) {
  return $app->redirect($app['url_generator']->generate("thank-you-error_en", ['child_id' => $child_id]));
});

$app->get('/unsubscribe/{child_id}/{hash}', function(Request $request, $child_id, $hash) use ($app) {
  return $app->redirect($app['url_generator']->generate("unsubscribe_en", ['child_id' => $child_id, 'hash' => $hash]));
});

foreach ($languages as $language) {
  $shortCode = $language->shortCode;
  $app->mount('/' . $shortCode, new VaxTimeControllerProvider($shortCode));
}