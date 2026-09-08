<?php
declare(strict_types=1);

use App\Middleware\JwtAuthMiddleware;

/**
 * Route table (all under /api/v1).
 *
 * JWT middleware is applied per-route. Registration order doesn't matter
 * here — routes with ->add() run JwtAuth inside the global middleware stack.
 */
$app->group('/api/v1', function (\Slim\Routing\RouteCollectorProxy $group) {
    // ----- Auth (public) ---------------------------------------------
    $group->post('/auth/register', App\Controllers\Auth\RegisterController::class);
    $group->post('/auth/login', App\Controllers\Auth\LoginController::class);
    $group->post('/auth/refresh', App\Controllers\Auth\RefreshController::class);

    // ----- Auth (protected) ------------------------------------------
    $group->get('/auth/me', App\Controllers\Auth\MeController::class)
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
