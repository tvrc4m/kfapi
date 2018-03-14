<?php

namespace App\Http\Middleware;

use Closure;

/**
 * 跨域支持中间件
 * @package App\Http\Middleware
 */
class CorsMiddleware
{
    private $headers;
    private $allow_origin;
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $this->headers = [
            'Access-Control-Allow-Methods' => 'GET, POST, PUT, DELETE',
            'Access-Control-Allow-Headers' => $request->header('Access-Control-Request-Headers'),
            'Access-Control-Allow-Credentials' => 'true',//允许客户端发送cookie
            'Access-Control-Max-Age' => 1728000 //该字段可选，用来指定本次预检请求的有效期，在此期间，不用发出另一条预检请求。
        ];

        // 设置允许的来源
        $this->allow_origin = [
            "*",
            // 'http://localhost',
        ];

        // 判断来源
        $origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
        if (!in_array("*", $this->allow_origin)) {
            //如果origin不在允许列表内，直接返回403
            if (!in_array($origin, $this->allow_origin) && !empty($origin)) {
                return response('Forbidden', 403);
            }
        }

        //如果是复杂请求，先返回一个200，并allow该origin
        if ($request->isMethod('options')) {
            return $this->setCorsHeaders(response('ok', 200), $origin);
        }

        $response = $next($request);

        // 简单请求 正常设置header
        $this->setCorsHeaders($response, $origin);

        return $response;
    }

    /**
     * 设置响应头
     * @param $response
     * @param $origin
     * @return mixed
     */
    private function setCorsHeaders($response, $origin)
    {
        foreach ($this->headers as $key => $value) {
            $response->header($key, $value);
        }
        if (in_array("*", $this->allow_origin)) {
            $response->header('Access-Control-Allow-Origin', "*");
        } else {
            if (in_array($origin, $this->allow_origin)) {
                $response->header('Access-Control-Allow-Origin', $origin);
            } else {
                $response->header('Access-Control-Allow-Origin', '');
            }
        }
        return $response;
    }
}
