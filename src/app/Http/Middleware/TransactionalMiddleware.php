<?php

namespace App\Http\Middleware;

use App\AOP\Transactional;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class TransactionalMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        $controller = $request->route()->getController();
        $method = $request->route()->getActionMethod();

        $reflectionMethod = new ReflectionMethod($controller, $method);
        $attributes = $reflectionMethod->getAttributes(Transactional::class);

        if (!empty($attributes)) {
            try {
                return DB::transaction(function () use ($request, $controller, $reflectionMethod) {
                    // リフレクションを使用してメソッドを呼び出す
                    return $reflectionMethod->invoke($controller, ...$request->route()->parameters());
                });
            } catch (Throwable $e) {
                // エラーログを記録するなどの処理を追加できます
                return response()->json(['error' => 'トランザクション中にエラーが発生しました。'], 500);
            }
        }
        return $response;    
    }
}