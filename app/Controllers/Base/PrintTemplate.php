<?php

namespace App\Controllers\Base;
use App\Controllers\BaseController;

class PrintTemplate extends BaseController
{
    public function list()
    {
        if (!$this->myservice->verifyRight('PRINTTEMP_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/PrintTemplate/list 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $page = max(intval($this->request->getGetPost('page')), 1);
        $rows = max(intval($this->request->getGetPost('rows')), 100);
        $sidx = $this->request->getGetPost('sidx');
        $sord = strtoupper((string)$this->request->getGetPost('sord')) === 'ASC' ? 'ASC' : 'DESC';
        $type = $this->request->getGetPost('type');
        $beginDate = $this->request->getGetPost('beginDate');
        $endDate = $this->request->getGetPost('endDate');
        $skey = $this->request->getGetPost('skey');

        $builder = $this->db->table('print_log');
        $builder->where('isDelete', 0);
        if ($type > 0) {
            $builder->where('type', $type);
        }
        if ($beginDate) {
            $builder->where('createTime >=', $beginDate);
        }
        if ($endDate) {
            $builder->where('createTime <=', $endDate);
        }
        if ($skey !== '' && $skey !== null) {
            $keywords = array_values(array_filter(explode(',', $skey), 'strlen'));
            foreach ($keywords as $kw) {
                $builder->groupStart()
                    ->like('name', $this->db->escapeLikeString($kw))
                    ->orLike('billNo', $this->db->escapeLikeString($kw))
                    ->orLike('contactName', $this->db->escapeLikeString($kw))
                    ->orLike('goods', $this->db->escapeLikeString($kw))
                ->groupEnd();
            }
        }
        $allowedSort = ['id','type','name','billNo','contactName','createTime'];
        $orderField  = $sidx ? (in_array($sidx, $allowedSort, true) ? $sidx : 'id') : 'id';
        $list = (clone $builder)->orderBy($orderField, $sord)->limit($rows)->offset($rows * ($page - 1))->get()->getResultArray();
        $total = $builder->countAllResults();
        $v = [];
        foreach ($list as $arr => $row) {
            $v[$arr]['id'] = intval($row['id']);
            $v[$arr]['type'] = intval($row['type']);
            $v[$arr]['name'] = $row['name'];
            $v[$arr]['billNo'] = $row['billNo'];
            $v[$arr]['contactId'] = intval($row['contactId']);
            $v[$arr]['contactName'] = $row['contactName'];
            $v[$arr]['goods'] = $row['goods'];
            $v[$arr]['createTime'] = $row['createTime'];
        }
        $json['status'] = 200;
        $json['msg'] = 'success';
        $json['data']['page'] = $page;
        $json['data']['records'] = $total;
        $json['data']['total'] = ceil($json['data']['records'] / $rows);
        $json['data']['rows'] = $v;
        return $this->response->setJSON($json);
    }
    // 新增装箱清单打印log
    public function add_log1()
    {
        $data = $this->request->getPost();
        if ($data) {
            $data = $this->validform($data);
            // 如果返回的是 Response 对象，直接返回它
            if ($data instanceof \CodeIgniter\HTTP\Response) {
                return $data;
            }
            $info = array_intersect_key($data, array_flip(['type', 'name', 'billNo', 'contactId', 'contactName', 'goods', 'createTime']));
            $this->db->transStart();
            $this->db->table('print_log')->insert($info);
            $iid = $this->db->insertID();
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '记录成功！', 'data' => ['id' => intval($iid)]]);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '提交的是空数据！']);
    }
    // 新增发货标签打印log
    public function add_log2()
    {
        $data = $this->request->getPost();
        if ($data) {
            $data = $this->validform($data);
            // 如果返回的是 Response 对象，直接返回它
            if ($data instanceof \CodeIgniter\HTTP\Response) {
                return $data;
            }
            $info = array_intersect_key($data, array_flip(['type', 'name', 'goods', 'createTime']));
            $this->db->transStart();
            $this->db->table('print_log')->insert($info);
            $iid = $this->db->insertID();
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '记录成功！', 'data' => ['id' => intval($iid)]]);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '提交的是空数据！']);
    }
    // 上传表格并提取数据（装箱清单）
    public function uploadExcel_1()
    {
        if (!$this->myservice->verifyRight('PRINTTEMP_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/PrintTemplate/uploadExcel_1 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        set_time_limit(0);
        session_write_close();
        if (empty($_FILES)){
            return $this->response->setJSON(['status' => -1, 'msg' => '没有上传文件！']);
        };
        $uppath = 'data/upfile/printTemplate/1/';
        $temp_file = $_FILES['file']['tmp_name'];
        $target_file = date("YmdHis") . rand(1000, 9000) . '.' . strrev(explode('.', strrev($_FILES['file']['name']))[0]);
        if (!is_dir($uppath)) {
            @umask(0);
            $ret = @mkdir($uppath, 0777);
            if ($ret === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '没有权限创建用于保存的文件夹！']);
            }
        }
        move_uploaded_file($temp_file, $uppath . $target_file);
        $this->myservice->log('导入了装箱清单表格：' . $_FILES['file']['name']);

        // 调用spreadsheet处理表格
        $inputFileName = $uppath . $target_file; // 要读取的xls文件路径
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($inputFileName);
        $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
        //组装数据插入表
        for ($i = 3; $i <= count($sheetData); $i++) {
            //自定义键名来组装数组
            $v[$i - 3]['goods'] = $sheetData[$i]['A'] ?? '';
            $v[$i - 3]['brandName'] = $sheetData[$i]['B'] ?? '';
            $v[$i - 3]['unitName'] = $sheetData[$i]['C'] ?? '';
            $v[$i - 3]['qty'] = $sheetData[$i]['D'] ?? 0;
            $v[$i - 3]['description'] = $sheetData[$i]['E'] ?? '';
        }
        //返回组装好的数据
        $info['entries'] = isset($v) ? array_values($v) : [];
        $json['status'] = 200;
        $json['msg'] = 'success';
        $json['data'] = $info;
        $json['totalsize'] = count($info['entries']);
        return $this->response->setJSON($json);
    }
    // 上传表格并提取数据（发货标签）
    public function uploadExcel_2()
    {
        if (!$this->myservice->verifyRight('PRINTTEMP_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/PrintTemplate/uploadExcel_2 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        set_time_limit(0);
        session_write_close();
        if (empty($_FILES)){
            return $this->response->setJSON(['status' => -1, 'msg' => '没有上传文件！']);
        };
        $uppath = 'data/upfile/printTemplate/2/';
        $temp_file = $_FILES['file']['tmp_name'];
        $target_file = date("YmdHis") . rand(1000, 9000) . '.' . strrev(explode('.', strrev($_FILES['file']['name']))[0]);
        if (!is_dir($uppath)) {
            @umask(0);
            $ret = @mkdir($uppath, 0777);
            if ($ret === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '没有权限创建用于保存的文件夹！']);
            }
        }
        move_uploaded_file($temp_file, $uppath . $target_file);
        $this->myservice->log('导入了发货标签表格：' . $_FILES['file']['name']);

        // 调用spreadsheet处理表格
        $inputFileName = $uppath . $target_file; // 要读取的xls文件路径
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($inputFileName);
        $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
        //组装数据插入表
        for ($i = 3; $i <= count($sheetData); $i++) {
            //自定义键名来组装数组
            $v[$i - 3]['name'] = $sheetData[$i]['A'] ?? '';
            $v[$i - 3]['spec'] = $sheetData[$i]['B'] ?? '';
            $v[$i - 3]['sku'] = $sheetData[$i]['C'] ?? '';
            $v[$i - 3]['qty'] = $sheetData[$i]['D'] ?? 0;
        }
        //返回组装好的数据
        $info['entries'] = isset($v) ? array_values($v) : [];
        $json['status'] = 200;
        $json['msg'] = 'success';
        $json['data'] = $info;
        $json['totalsize'] = count($info['entries']);
        return $this->response->setJSON($json);
    }
    // 数据验证
    private function validform(array $data)
    {
        $data['name'] = $this->myerp['uName'];
        $data['createTime'] = date('Y-m-d H:i:s');
        //数据验证
        if (is_array($data['entries'])) {
            if (count($data['entries']) < 1) {
                return $this->response->setJSON(['status' => -1, 'msg' => '没有包含产品，不记录本次打印！']);
            }
        } else {
            return $this->response->setJSON(['status' => -1, 'msg' => '提交的产品信息是空数据，不记录本次打印！']);
        }
        //产品验证
        foreach ($data['entries'] as $arr => $row) {
            if ('' == $row['goods']) {
                return $this->response->setJSON(['status' => -1, 'msg' => '有产品信息为空的情况，不记录本次打印！']);
            }
            $data['goods'][$arr]['goods'] = $row['goods'];
            $data['goods'][$arr]['brand'] = $row['brand'] ?? '';
            $data['goods'][$arr]['unit'] = $row['unit'] ?? '';
            $data['goods'][$arr]['qty'] = $row['qty'] ?? 0;
            $data['goods'][$arr]['des'] = $row['des'] ?? '';
        }
        $data['goods'] = json_encode($data['goods'], 320);
        return $data;
    }
}
