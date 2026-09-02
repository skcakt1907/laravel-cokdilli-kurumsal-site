<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Rota bazlı rol kontrolü.
 *
 *   Route::…->middleware('rol:sahip')             -> yalnızca sahip
 *   Route::…->middleware('rol:sahip,yonetici')    -> sahip veya yönetici
 *
 * Yetkisiz erişimde 403; giriş yapılmamışsa zaten 'auth' devreye girer.
 */
class RolGerekli
{
    public function handle(Request $istek, Closure $sonraki, string ...$roller): Response
    {
        $kullanici = $istek->user();

        if (!$kullanici || !$kullanici->durum) {
            abort(403, 'Bu işlem için yetkiniz yok.');
        }

        if ($roller && !in_array($kullanici->rol, $roller, true)) {
            abort(403, 'Bu işlem için yetkiniz yok.');
        }

        return $sonraki($istek);
    }
}
