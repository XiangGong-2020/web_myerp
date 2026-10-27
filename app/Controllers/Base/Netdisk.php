<?php

namespace App\Controllers\Base;
use App\Controllers\BaseController;

class Netdisk extends BaseController
{
    public function connector()
    {
        if (!$this->myservice->verifyRight('SYS_DEFAULT')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/Netdisk/elfinder_init 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        set_time_limit(0);
        session_write_close();

        // 如果必要目录缺失，则新建必要目录
        $path = set_realpath('data/网盘/[临时保存]');
        !is_dir($path) && mkdir($path);
        $path = set_realpath('data/网盘/[扫描保存]');
        !is_dir($path) && mkdir($path);
        $path = set_realpath('data/网盘/[长期保存]');
        !is_dir($path) && mkdir($path);
        $departments = $this->db->table('user')
            ->select('department')
            ->where('department !=', '')
            ->groupBy('department')
            ->orderBy('department', 'ASC')
            ->get()
            ->getResultArray();
        foreach ($departments as $k => $v) {
            $path = set_realpath('data/网盘/' . $v['department']);
            !is_dir($path) && mkdir($path);
        }

        // 组装配置信息
        $opts = [
            'maxArcFilesSize' => 0, // Max allowed archive files size (0 - no limit)
            'maxTargets' => 1000, // Max number of limits of selectable items (0 - no limit)
            'throwErrorOnExec' => false, // Throw Error on exec(), if set true, need try{} block for $connector->run();
            'debug' => false, // Send debug to client
            'commonTempPath' => 'data/网盘_temp',
            'roots' => [
                // Items volume
                [
                    'driver'        => 'LocalFileSystem',
                    'path'          => set_realpath('data/网盘/'),
                    'URL'           => base_url('data/网盘/'),
                    'trashHash'     => 't1_Lw',
                    'winHashFix'    => DIRECTORY_SEPARATOR !== '/',
                    'uploadDeny'    => [],
                    'uploadAllow'   => ['all'],
                    'uploadOrder'   => ['deny', 'allow'],
                    'uploadMaxSize' => 0, // 上传最大单个文件大小。数字或字符串，例如0/'10M'/'50K'/'1G'，0表示无限制。注意: elFinder 2.1+ 支持分片上传。
                    'uploadMaxConn' => 1, // 分片上传最大连接数，-1表示禁用分片上传。为了保障windows平台多文件上传不出错，这里设置为1，每次只上传1片。
                    'accessControl' => 'access',
                    'defaults' => ['read' => true, 'write' => true, 'locked' => false, 'hidden' => false],
                    'attributes' => [
                        [
                            'pattern' => '/^\/$/',
                            'write' => false,
                            'locked' => true
                        ],
                        [
                            'pattern' => '/.quarantine/',
                            'hidden' => true
                        ],
                        [
                            'pattern' => '/.tmb/',
                            'hidden' => true
                        ],
                        [
                            'pattern' => '/.trash/',
                            'hidden' => true
                        ],
                        [
                            'pattern' => '/^\/\[临时保存\]$/',
                            'locked' => true
                        ],
                        [
                            'pattern' => '/^\/\[扫描保存\]$/',
                            'locked' => true
                        ],
                        [
                            'pattern' => '/^\/\[长期保存\]$/',
                            'locked' => true
                        ],
                        [
                            'pattern' => '/^\/财务部$/',
                            'read' => (($this->myerp['user']['department'] == '财务部' || in_array('SYS_ADMIN', $this->myerp['rights'])) ? true : false),
                            'write' => (($this->myerp['user']['department'] == '财务部' || in_array('SYS_ADMIN', $this->myerp['rights'])) ? true : false),
                            'locked' => true,
                            'hidden' => (($this->myerp['user']['department'] == '财务部' || in_array('SYS_ADMIN', $this->myerp['rights'])) ? false : true)
                        ],
                        [
                            'pattern' => '/^\/采购部$/',
                            'read' => (($this->myerp['user']['department'] == '采购部' || in_array('SYS_ADMIN', $this->myerp['rights'])) ? true : false),
                            'write' => (($this->myerp['user']['department'] == '采购部' || in_array('SYS_ADMIN', $this->myerp['rights'])) ? true : false),
                            'locked' => true,
                            'hidden' => (($this->myerp['user']['department'] == '采购部' || in_array('SYS_ADMIN', $this->myerp['rights'])) ? false : true)
                        ],
                        [
                            'pattern' => '/^\/仓储部$/',
                            'read' => (($this->myerp['user']['department'] == '仓储部' || in_array('SYS_ADMIN', $this->myerp['rights'])) ? true : false),
                            'write' => (($this->myerp['user']['department'] == '仓储部' || in_array('SYS_ADMIN', $this->myerp['rights'])) ? true : false),
                            'locked' => true,
                            'hidden' => (($this->myerp['user']['department'] == '仓储部' || in_array('SYS_ADMIN', $this->myerp['rights'])) ? false : true)
                        ],
                        [
                            'pattern' => '/^\/后勤部$/',
                            'read' => (($this->myerp['user']['department'] == '后勤部' || in_array('SYS_ADMIN', $this->myerp['rights'])) ? true : false),
                            'write' => (($this->myerp['user']['department'] == '后勤部' || in_array('SYS_ADMIN', $this->myerp['rights'])) ? true : false),
                            'locked' => true,
                            'hidden' => (($this->myerp['user']['department'] == '后勤部' || in_array('SYS_ADMIN', $this->myerp['rights'])) ? false : true)
                        ],
                        [
                            'pattern' => '/^\/技术部$/',
                            'read' => (($this->myerp['user']['department'] == '技术部' || in_array('SYS_ADMIN', $this->myerp['rights'])) ? true : false),
                            'write' => (($this->myerp['user']['department'] == '技术部' || in_array('SYS_ADMIN', $this->myerp['rights'])) ? true : false),
                            'locked' => true,
                            'hidden' => (($this->myerp['user']['department'] == '技术部' || in_array('SYS_ADMIN', $this->myerp['rights'])) ? false : true)
                        ],
                        [
                            'pattern' => '/^\/生产部$/',
                            'read' => (($this->myerp['user']['department'] == '生产部' || in_array('SYS_ADMIN', $this->myerp['rights']) || in_array($this->myerp['user']['name'], ['叶超', '陈元锋', '向治安'])) ? true : false),
                            'write' => (($this->myerp['user']['department'] == '生产部' || in_array('SYS_ADMIN', $this->myerp['rights']) || in_array($this->myerp['user']['name'], ['叶超', '陈元锋', '向治安'])) ? true : false),
                            'locked' => true,
                            'hidden' => (($this->myerp['user']['department'] == '生产部' || in_array('SYS_ADMIN', $this->myerp['rights']) || in_array($this->myerp['user']['name'], ['叶超', '陈元锋', '向治安'])) ? false : true)
                        ],
                        [
                            'pattern' => '/^\/销售部$/',
                            'read' => (($this->myerp['user']['department'] == '销售部' || in_array('SYS_ADMIN', $this->myerp['rights'])) ? true : false),
                            'write' => (($this->myerp['user']['department'] == '销售部' || in_array('SYS_ADMIN', $this->myerp['rights'])) ? true : false),
                            'locked' => true,
                            'hidden' => (($this->myerp['user']['department'] == '销售部' || in_array('SYS_ADMIN', $this->myerp['rights'])) ? false : true)
                        ],
                        [
                            'pattern' => '/^\/行政部$/',
                            'read' => (($this->myerp['user']['department'] == '行政部' || in_array('SYS_ADMIN', $this->myerp['rights'])) ? true : false),
                            'write' => (($this->myerp['user']['department'] == '行政部' || in_array('SYS_ADMIN', $this->myerp['rights'])) ? true : false),
                            'locked' => true,
                            'hidden' => (($this->myerp['user']['department'] == '行政部' || in_array('SYS_ADMIN', $this->myerp['rights'])) ? false : true)
                        ],
                        [
                            'pattern' => '/^\/运营部$/',
                            'read' => (($this->myerp['user']['department'] == '运营部' || in_array('SYS_ADMIN', $this->myerp['rights'])) ? true : false),
                            'write' => (($this->myerp['user']['department'] == '运营部' || in_array('SYS_ADMIN', $this->myerp['rights'])) ? true : false),
                            'locked' => true,
                            'hidden' => (($this->myerp['user']['department'] == '运营部' || in_array('SYS_ADMIN', $this->myerp['rights'])) ? false : true)
                        ],
                        [
                            'pattern' => '/^\/信息部$/',
                            'read' => (($this->myerp['user']['department'] == '信息部' || in_array('SYS_ADMIN', $this->myerp['rights'])) ? true : false),
                            'write' => (($this->myerp['user']['department'] == '信息部' || in_array('SYS_ADMIN', $this->myerp['rights'])) ? true : false),
                            'locked' => true,
                            'hidden' => (($this->myerp['user']['department'] == '信息部' || in_array('SYS_ADMIN', $this->myerp['rights'])) ? false : true)
                        ]
                    ]
                ],
                // Trash volume
                [
                    'id'            => '1',
                    'driver'        => 'Trash',
                    'path'          => set_realpath('data/网盘/.trash/'),
                    'tmbURL'        => base_url('/data/网盘/.trash/.tmb/'),
                    'winHashFix'    => DIRECTORY_SEPARATOR !== '/',
                    'attributes' => [
                        [
                            'pattern' => '/.tmb/',
                            'hidden' => true
                        ],
                        [
                            'pattern' => '/^\/财务部$/',
                            'hidden' => (($this->myerp['user']['department'] == '财务部' || in_array('SYS_ADMIN', $this->myerp['rights'])) ? false : true)
                        ],
                        [
                            'pattern' => '/^\/采购部$/',
                            'hidden' => (($this->myerp['user']['department'] == '采购部' || in_array('SYS_ADMIN', $this->myerp['rights'])) ? false : true)
                        ],
                        [
                            'pattern' => '/^\/仓储部$/',
                            'hidden' => (($this->myerp['user']['department'] == '仓储部' || in_array('SYS_ADMIN', $this->myerp['rights'])) ? false : true)
                        ],
                        [
                            'pattern' => '/^\/后勤部$/',
                            'hidden' => (($this->myerp['user']['department'] == '后勤部' || in_array('SYS_ADMIN', $this->myerp['rights'])) ? false : true)
                        ],
                        [
                            'pattern' => '/^\/技术部$/',
                            'hidden' => (($this->myerp['user']['department'] == '技术部' || in_array('SYS_ADMIN', $this->myerp['rights'])) ? false : true)
                        ],
                        [
                            'pattern' => '/^\/生产部$/',
                            'hidden' => (($this->myerp['user']['department'] == '生产部' || in_array('SYS_ADMIN', $this->myerp['rights'])) ? false : true)
                        ],
                        [
                            'pattern' => '/^\/销售部$/',
                            'hidden' => (($this->myerp['user']['department'] == '销售部' || in_array('SYS_ADMIN', $this->myerp['rights'])) ? false : true)
                        ],
                        [
                            'pattern' => '/^\/行政部$/',
                            'hidden' => (($this->myerp['user']['department'] == '行政部' || in_array('SYS_ADMIN', $this->myerp['rights'])) ? false : true)
                        ],
                        [
                            'pattern' => '/^\/运营部$/',
                            'hidden' => (($this->myerp['user']['department'] == '运营部' || in_array('SYS_ADMIN', $this->myerp['rights'])) ? false : true)
                        ],
                        [
                            'pattern' => '/^\/信息部$/',
                            'hidden' => (($this->myerp['user']['department'] == '信息部' || in_array('SYS_ADMIN', $this->myerp['rights'])) ? false : true)
                        ]
                    ]
                ]
            ]
        ];

        // 运行连接器，捕获输出适配 CI4 响应规范
        ob_start();
        $connector = new \elFinderConnector(new \elFinder($opts));
        $connector->run();
        $output = ob_get_clean();

        return $this->response->setBody($output);
    }
}
