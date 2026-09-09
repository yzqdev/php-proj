<?php
declare(strict_types=1);

use App\Middleware\JwtAuthMiddleware;
use Slim\Factory\AppFactory;

/**
 * Route table (all under /api/v1).
 */
$app = AppFactory::create();
$app->get('/swagger', [\App\Controllers\SwaggerController::class, 'index']);
$app->get('/swagger/json', [\App\Controllers\SwaggerController::class, 'json']);
$app->get('/favicon.ico', function (\Psr\Http\Message\RequestInterface $request, \Psr\Http\Message\ResponseInterface $response) {
    return $response->withStatus(204);
});
$app->group('/api/v1', function (\Slim\Routing\RouteCollectorProxy $group) {
    // ----- Auth (public) ---------------------------------------------
    $group->post('/auth/register', [App\Controllers\AuthController::class, 'register']);
    $group->post('/auth/login', [App\Controllers\AuthController::class, 'login']);
    $group->post('/auth/refresh', [App\Controllers\AuthController::class, 'refresh']);

    // ----- Auth (protected) ------------------------------------------
    $group->get('/auth/me', [App\Controllers\AuthController::class, 'me'])
          ->add(JwtAuthMiddleware::class);

    // ----- Articles ----------------------------------------------------
    $group->get('/articles', App\Controllers\ArticleController::class);
    $group->get('/articles/{id}', App\Controllers\ArticleController::class);
    $group->post('/articles', App\Controllers\ArticleController::class)
          ->add(JwtAuthMiddleware::class);
    $group->put('/articles/{id}', App\Controllers\ArticleController::class)
          ->add(JwtAuthMiddleware::class);
    $group->delete('/articles/{id}', App\Controllers\ArticleController::class)
          ->add(JwtAuthMiddleware::class);

    // ----- Comments ------------------------------------------------------
    $group->get('/articles/{id}/comments', App\Controllers\CommentController::class);
    $group->post('/articles/{id}/comments', App\Controllers\CommentController::class)
          ->add(JwtAuthMiddleware::class);
    $group->delete('/comments/{id}', App\Controllers\CommentController::class)
          ->add(JwtAuthMiddleware::class);
});
