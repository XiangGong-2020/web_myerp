<?php
namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class IsLoggedInFilter implements FilterInterface
{
    protected mixed $myerp;
    /**
     * 控制器方法执行前触发
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        // 没获取到session，则强制跳转到登录页面
        $this->myerp = session()->get('myerp');
        if (!$this->myerp) {
            return redirect()->to('login');
        }
    }

    /**
     * 控制器方法执行后触发，不需要就留空
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
