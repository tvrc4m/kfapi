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
     * @param array $header
     * @return \Illuminate\Http\JsonResponse
     */
    function api_error(string $message = 'error', int $error_code = 1, array $header = []) {
        $resp = [
            'error_no' => $error_code,
            'error_message' => $message,
        ];
        return response()->json($resp, 200, $header);
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
            var_dump($query->sql);
        });
    }
}

if (! function_exists('similar_array')) {
    /**
     * 获得两个数组的相似度
     * @param $arr1
     * @param $arr2
     * @return int
     */
    function similar_array($arr1, $arr2) {
        sort($arr1);
        sort($arr2);
        $str1 = implode("-", $arr1);
        $str2 = implode("-", $arr2);
        similar_text($str1, $str2, $percent);
        return intval($percent);
    }
}

if (! function_exists('arraySort')) {
    /**
     * 二维数组根据某个字段排序
     * @param array $array 要排序的数组
     * @param string $keys   要排序的键字段
     * @param integer $sort  排序类型  SORT_ASC     SORT_DESC
     * @return array 排序后的数组
     */
    function arraySort($array, $keys, $sort = SORT_DESC) {
        $keysValue = [];
        foreach ($array as $k => $v) {
            $keysValue[$k] = $v[$keys];
        }
        array_multisort($keysValue, $sort, $array);
        return $array;
    }
}
