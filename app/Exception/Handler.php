<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Auth\Access\AuthorizationException;
use Throwable;

class Handler extends ExceptionHandler
{
    public function register(): void
    {
        // Model tidak ditemukan → 404
        $this->renderable(function (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Data tidak ditemukan.',
            ], 404);
        });

        // Route tidak ditemukan → 404
        $this->renderable(function (NotFoundHttpException $e) {
            return response()->json([
                'message' => 'Endpoint tidak ditemukan.',
            ], 404);
        });

        // Belum login → 401
        $this->renderable(function (AuthenticationException $e) {
            return response()->json([
                'message' => 'Silakan login terlebih dahulu.',
            ], 401);
        });

        // Policy menolak → 403
        $this->renderable(function (AuthorizationException $e) {
            return response()->json([
                'message' => 'Anda tidak memiliki akses ke resource ini.',
            ], 403);
        });
    }
}