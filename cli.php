<?php
// 1. 定义项目根目录
define('ROOTPATH', __DIR__ . DIRECTORY_SEPARATOR);

// 2. 官方 CLI 专用引导脚本（自动处理所有路径、常量、环境、框架初始化）
require ROOTPATH . 'vendor/codeigniter4/framework/system/util_bootstrap.php';

// ===== 命令行参数解析 =====
$dir        = $argv[1] ?? '';
$controller = $argv[2] ?? '';
$method     = $argv[3] ?? 'index';

if (empty($dir) || empty($controller)) {
    exit("参数错误！格式：php cli.php 目录 控制器 [方法]\n示例：php cli.php api SpiderBid01 index\n");
}

$class = "App\\Controllers\\" . trim($dir, '\\/') . '\\' . $controller;

if (!class_exists($class)) {
    exit("错误：控制器类 {$class} 不存在，请检查目录、控制器名大小写\n");
}

// 实例化控制器（仅执行构造函数，属性还未初始化）
$instance = new $class();

// ========== 关键修复：手动执行 CI4 标准控制器初始化 ==========
// 和 Web 环境执行逻辑完全一致，所有模型、数据库、Session 都会完成赋值
// 执行完这一步，所有强类型属性都有值，PHP 8.3 不再报错
$instance->initController(
    service('request'),
    service('response'),
    service('logger')
);
// =============================================================

if (!method_exists($instance, $method)) {
    exit("错误：方法 {$controller}::{$method} 不存在\n");
}

// 调用业务方法（此时所有属性均已初始化）
call_user_func_array([$instance, $method], array_slice($argv, 4));
