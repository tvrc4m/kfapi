<?php
/**
 * 全局函数库
 * User: dawn
 * Date: 2018/3/2
 * Time: 下午5:58
 */

if (! function_exists('api_success')) {
    /**
     * 成功消息
     * @param array $data
     * @param array $header
     * @return \Illuminate\Http\JsonResponse
     */
    function api_success($data = [], array $header = []) {
        $resp = [
            'error_no' => 0,
            'error_message' => 'success',
        ];
        if (!empty($data)) {
            if ($data instanceof Illuminate\Contracts\Support\Arrayable) {
                $data = $data->toArray();
            }
            $resp['data'] = $data;
        }
        return response()->json($resp, 200, $header);
    }
}

if (! function_exists('api_error')) {
    /**
     * 失败消息
     * @param string $message
     * @param int $error_code
     * @param int $http_code
     * @param array $header
     * @return \Illuminate\Http\JsonResponse
     */
    function api_error(string $message = 'error', int $error_code = 1, int $http_code = 400, array $header = []) {
        $resp = [
            'error_no' => $error_code,
            'error_message' => $message,
        ];
        return response()->json($resp, $http_code, $header);
    }
}

if (! function_exists('print_sql')) {
    /**
     * 打印执行的sql语句
     * 需要写在sql操作之前
     */
    function print_sql() {
        \DB::listen(function ($query) {
            // $query->sql
            // $query->bindings
            // $query->time
            dd($query->sql);
        });
    }
}