<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;
use CodeIgniter\Session\Session;
use CodeIgniter\Database\BaseConnection;
use Config\Database;
use App\Services\MyService;

/**
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 *
 * Extend this class in any new controllers:
 * ```
 *     class Home extends BaseController
 * ```
 *
 * For security, be sure to declare any new methods as protected or private.
 */
abstract class BaseController extends Controller
{
    /**
     * Be sure to declare properties for any property fetch you initialized.
     * The creation of dynamic property is deprecated in PHP 8.2.
     */

    // 强类型声明（PHP 8.3 必须保证 initController 中一定赋值）
    protected BaseConnection $db;
    protected ?Session $session = null;
    protected array $myerp = [];
    protected MyService $myservice;

    /**
     * @return void
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Load here all helpers you want to be available in your controllers that extend BaseController.
        // Caution: Do not put the this below the parent::initController() call below.
        $this->helpers = ['fun_helper'];

        // Caution: Do not edit this line.
        parent::initController($request, $response, $logger);

        // 连接数据库，和CI3 $this->db等价
        $this->db = Database::connect();

        // Preload any models, libraries, etc, here.
        // 所有模型、配置初始化都在这里执行

        // 仅 Web 环境，cli 和 api 请求不需要session
        $path = $request->getUri()->getPath();
        $isApiRequest = str_starts_with($path, '/api/') || str_starts_with($path, '/Api/');
        if (!is_cli() && !$isApiRequest) {
            $this->session = service('session');
            $this->myerp = $this->session->get('myerp') ?? [];
            // 把已初始化好的值直接传给 Service
            $this->myservice = new MyService($this->db, $this->myerp);
        }

    }
}
