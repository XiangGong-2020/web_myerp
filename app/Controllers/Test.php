<?php
namespace App\Controllers;
use App\Controllers\BaseController;
use CodeIgniter\Database\RawSql;

set_time_limit(0); // 设置脚本运行时间无限制，直到脚本运行完毕
header('Access-Control-Allow-Origin:*'); //指定允许所有域名访问，即允许跨域
header('Access-Control-Allow-Methods:POST'); //响应类型
header('Access-Control-Allow-Headers:x-requested-with,content-type'); //响应头设置

class Test extends BaseController
{

    public function index()
    {
        if (!$this->myservice->verifyRight('SYS_ADMIN')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Test/index 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        set_time_limit(0); // 设置脚本运行时间无限制，直到脚本运行完毕
        session_write_close();

        /* $invoices = $this->db->table('invoice')->select('id, billNo')->where(['inoutType' => 0, 'billNo !=' => '', 'billFrom' => '', 'isDelete' => 0])->get()->getResultArray();
        foreach ($invoices as $k => $v) {
            $billNo = json_decode($v['billNo'], true);
            foreach ($billNo as $kk => $vv) {
                $billInfo = $this->db->table('bill_order')->select('billFrom')->where(['billNo' => $kk])->get()->getRowArray();
                if ($billInfo && $billInfo['billFrom']) {
                    $this->db->table('invoice')->where('id', $v['id'])->update(['billFrom' => $billInfo['billFrom']]);
                    break;
                }
            }
        } */

<<<<<<< HEAD

=======
            var_dump((array)json_decode('{"1591666069554072999":0,"1641965737253072999":0,"1591666069554072999":0}', true));
>>>>>>> 151b415 (更新语言最新版本+最新底层框架后全部差异化代码适配重写)










        // 天眼查通过识别号反查主体名
        /* $companyNo = '91130128MA08BN8K1E';
        $companyName = '';
        $cookie = $this->db->table('bid_temp')->where(['site' => '天眼查'])->select('cookie')->get()->getRow()->cookie;
        if ($cookie) {
            $token = $tycid = [];
            preg_match('#auth_token=(.*);#isU', $cookie, $token);
            preg_match('#TYCID=(.*);#isU', $cookie, $tycid);
            if (is_countable($token) && count($token) > 0 && is_countable($tycid) && count($tycid) > 0) {
                $url = 'https://capi.tianyancha.com/cloud-tempest/search/suggest/company/main?_=' . intval(microtime(true) * 1000);
                $res = global_curl($url, [
                    'post' => 1,
                    'postfields' => '{"keyword":"' . $companyNo . '"}',
                    'httpheader' => [
                        'Accept-Encoding: gzip, deflate, br, zstd',
                        'Accept-Language: zh-CN,zh;q=0.9,en;q=0.8,en-GB;q=0.7,en-US;q=0.6',
                        'Cache-Control: no-cache',
                        'Connection: keep-alive',
                        'Content-Length: 32',
                        'Content-Type: application/json',
                        'Host: capi.tianyancha.com',
                        'Origin: https://www.tianyancha.com',
                        'Pragma: no-cache',
                        'Referer: https://www.tianyancha.com/',
                        'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0',
                        'X-AUTH-TOKEN: ' . $token[1],
                        'X-TYCID: ' . $tycid[1],
                        'version: TYC-Web'
                    ]
                ]);
                if ($res !== false) {
                    $obj = json_decode($res, true);
                    $companyName = $obj['data']['companySuggestList'][0]['name'] ?? '';
                }
            }
        }
        var_dump($companyName); */

        // PDF解析测试
        /* $config = new \Smalot\PdfParser\Config();
        $parser = new \Smalot\PdfParser\Parser([], $config);
        $files = ['铁路电子客票1', '铁路电子客票5'];
        foreach ($files as $index => $file) {
            $pdf = $parser->parseFile('E:\发票识别测试\\' . $file . '.pdf');
            $pages = $pdf->getPages();
            $pageLen = count($pages);
            $text = $pages[$pageLen - 1]->getText();
            $arr = preg_split("/[\r\n]+/", str_replace(["\x06", "\0", "	", " "], '', $text));
            var_dump($arr);
            var_dump('------------------------------------------------------------------------');
        } */


        // 比较两个数组的键值，并返回交集或差集
        /* $a1 = json_decode('[{"url":"/data/upfile/goods/file/202107201647352434.rar","name":"28V数控可调稳压电源PCB(TJ-56-549).rar","upName":"陈元锋","upTime":"2021-07-20"},{"type":"1","url":"/data/upfile/goods/file/202107201648006780.rar","name":"亚克力外壳.rar","upName":"陈元锋","upTime":"2021-07-20"},{"type":"1","url":"/data/upfile/goods/file/202107201651138672.rar","name":"28V稳压电源程序(TJ-56-549).rar","upName":"陈元锋","upTime":"2021-07-20"},{"name":"11.jpg","url":"/data/upfile/goods/file/202404231500541766.jpg","upName":"向奇华","upTime":"2024-04-23"}]', true);

        $a2 = json_decode('[{"name":"11.jpg","url":"/data/upfile/goods/file/202404231500541766.jpg","upName":"向奇华","upTime":"2024-04-23"},{"name":"11111111111.jpg","url":"/data/upfile/goods/file/202404231500541788.jpg","upName":"向奇华","upTime":"2024-04-23"}]', true);

        var_dump(array_column($a1, 'url'), array_column($a2, 'url'));
        $b = array_intersect(array_column($a1, 'url'), array_column($a2, 'url')); // 交集
        $c = array_diff(array_column($a1, 'url'), array_column($a2, 'url')); // 差集
        var_dump($b);
        var_dump($c); */


        // 获取10位数字时间
        //var_dump(intval(microtime(true)));
        // 获取13位数字时间（带毫秒数）
        //var_dump(intval(microtime(true) * 1000));
    }
}
