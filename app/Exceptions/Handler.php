<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Laravel\Lumen\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\HttpException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        AuthorizationException::class,
        HttpException::class,
        ModelNotFoundException::class,
        ValidationException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception  $e
     * @return void
     */
    public function report(Exception $e)
    {
        parent::report($e);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $e
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $e)
    {
        return $this->myRender($request, $e);
    }

    /**
     * 自定义错误渲染
     * @param $request
     * @param Exception $e
     * @return Response|\Illuminate\Http\JsonResponse|null|\Symfony\Component\HttpFoundation\Response
     */
    private function myRender($request, Exception $e)
    {
        // 表单验证异常
        if ($e instanceof ValidationException && $e->getResponse()) {
            // 获取第一条错误信息
            $error_message = current($e->errors())[0];
            return api_error($error_message);
        }
        // 非debug模式 所有异常统一格式
        if (!env('APP_DEBUG')) {
            return api_error($e->getMessage());
        }

        return parent::render($request, $e);
    }
}
