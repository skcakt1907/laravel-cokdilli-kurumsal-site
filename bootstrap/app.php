<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'rol' => \App\Http\Middleware\RolGerekli::class,
            'dil' => \App\Http\Middleware\DilSec::class,
        ]);

        // Site tarafında dil, çerez/URL üzerinden her istekte belirlenir.
        $middleware->appendToGroup('web', \App\Http\Middleware\DilSec::class);

        // Giriş gerektiren sayfalarda yönlendirme hedefi panel girişidir.
        $middleware->redirectGuestsTo(fn () => route('panel.giris'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        // Sunucunun post_max_size sınırını aşan yükleme.
        // PHP isteği daha Laravel'e ulaşmadan boşaltır; varsayılan davranış
        // ham 413 sayfasıdır ve kullanıcı neden olduğunu anlamaz.
        $exceptions->render(function (PostTooLargeException $e, Request $istek) {
            $sinir = \App\Support\VideoYukle::sinirMb();

            return back()
                ->withInput($istek->except(['video_dosya', 'gorsel', 'logo', 'favicon']))
                ->with('hata', "Dosya çok büyük. Sunucu en fazla {$sinir} MB kabul ediyor. "
                             . 'Daha küçük bir dosya seçin ya da videoyu YouTube/Vimeo\'ya '
                             . 'yükleyip bağlantısını yapıştırın.');
        });
    })->create();
