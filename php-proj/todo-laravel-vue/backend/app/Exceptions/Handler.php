<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

/**
 * @OA\Schema(
 *    schema="ValidationErrorResponseJson",
 *        @OA\Property(
 *            property="message",
 *            description="Error message",
 *            type="string",
 *            nullable="false",
 *            example="The status field is required. (and 1 more error)"
 *        ),
 *        @OA\Property(
 *            property="errors",
 *            description="Error object",
 *            type="object",
 *            nullable="true",
 *            @OA\Property(
 *               property="status",
 *               type="array",
 *               nullable=false,
 *               example="['The status field is required.']",
 *               @OA\Items(type="string")
 *           ),
 *            @OA\Property(
 *               property="priority",
 *               type="array",
 *               nullable=false,
 *               example="['The priority field is required.']",
 *               @OA\Items(type="string")
 *           ),
 *        ),
 *    )
 * )
 *
 **/
class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }
}
