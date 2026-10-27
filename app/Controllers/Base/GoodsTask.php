<?php

namespace App\Controllers\Base;
use App\Controllers\BaseController;

class GoodsTask extends BaseController
{
    // 列表
    public function list()
    {
        if (!$this->myservice->verifyRight('GOODSTASK_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Base/GoodsTask/list 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $page = max(intval($this->request->getGetPost('page')), 1);
        $rows = max(intval($this->request->getGetPost('rows')), 100);
        $sidx = $this->request->getGetPost('sidx');
        $sord = strtoupper((string)$this->request->getGetPost('sord')) === 'ASC' ? 'ASC' : 'DESC';
        $status1 = $this->request->getGetPost('status1');
        $status2 = $this->request->getGetPost('status2');
        $status3 = intval($this->request->getGetPost('status3'));
        $status4 = intval($this->request->getGetPost('status4'));
        $beginDate = $this->request->getGetPost('beginDate');
        $endDate = $this->request->getGetPost('endDate');
        $skey = $this->request->getGetPost('skey');
        $unDone = intval($this->request->getGetPost('unDone'));
        $builder = $this->db->table('goods_task')->where('isDelete', 0);
        if ($status1) {
            $builder->where('leader', $status1);
        }
        if ($status2) {
            $builder->where('FIND_IN_SET(' . $this->db->escape($status2) . ', other) >', 0, false);
        }
        if ($status3 == 0) {
            $builder->where('beginDate', '0000-00-00 00:00:00');
        } elseif ($status3 == 1) {
            $builder->where('beginDate >', '0000-00-00 00:00:00');
        }
        if ($status4 == 0) {
            $builder->where('endDate', '0000-00-00 00:00:00');
        } elseif ($status4 == 1) {
            $builder->where('endDate >', '0000-00-00 00:00:00');
        }
        if ($beginDate) {
            $builder->where('publishDate >=', $beginDate);
        }
        if ($endDate) {
            $builder->where('publishDate <=', $endDate);
        }
        if ($unDone > 0) {
            $builder->where('FIND_IN_SET("5", status) =', 0, false);
        }
        if ($skey !== '' && $skey !== null) {
            $keywords = array_values(array_filter(explode(',', $skey), 'strlen'));
            foreach ($keywords as $kw) {
                $builder->groupStart()
                    ->like('title', $this->db->escapeLikeString($kw))
                    ->orLike('content', $this->db->escapeLikeString($kw))
                    ->orLike('files', $this->db->escapeLikeString($kw))
                    ->orLike('publishName', $this->db->escapeLikeString($kw))
                    ->orLike('leader', $this->db->escapeLikeString($kw))
                    ->orLike('other', $this->db->escapeLikeString($kw))
                ->groupEnd();
            }
        }
        $allowedSort = ['id','srcDevReqId','title','lastDate','type','status','leader','other','goodsNo','beginDate','endDate','publishName','publishDate','checkId'];
        $orderField  = $sidx ? (in_array($sidx, $allowedSort, true) ? $sidx : 'id') : 'id';
        $list = (clone $builder)->orderBy($orderField, $sord)->limit($rows)->offset($rows * ($page - 1))->get()->getResultArray();
        $total = $builder->countAllResults();
        $v = [];
        foreach ($list as $arr => $row) {
            $v[$arr]['id'] = intval($row['id']);
            $v[$arr]['srcDevReqId'] = intval($row['srcDevReqId']);
            $v[$arr]['title'] = $row['title'];
            $v[$arr]['lastDate'] = $row['lastDate'];
            $v[$arr]['type'] = intval($row['type']);
            $v[$arr]['status'] = $row['status'];
            $v[$arr]['leader'] = $row['leader'];
            $v[$arr]['other'] = $row['other'];
            $v[$arr]['goodsNo'] = $row['goodsNo'];
            $v[$arr]['beginDate'] = $row['beginDate'];
            $v[$arr]['endDate'] = $row['endDate'];
            $v[$arr]['publishName'] = $row['publishName'];
            $v[$arr]['publishDate'] = $row['publishDate'];
            $v[$arr]['checkId'] = intval($row['checkId']);
        }
        $json['status'] = 200;
        $json['msg'] = 'success';
        $json['data']['page'] = $page;
        $json['data']['records'] = $total;
        $json['data']['total'] = ceil($json['data']['records'] / $rows);
        $json['data']['rows'] = $v;
        return $this->response->setJSON($json);
    }
    // 查询
    public function query()
    {
        if (!$this->myservice->verifyRight('GOODSTASK_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Base/GoodsTask/query 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $data = $this->db->table('goods_task')->where(['id' => $id, 'isDelete' => 0])->get()->getRowArray();
        if ($data) {
            $info['id'] = $id;
            $info['title'] = $data['title'];
            $info['content'] = $data['content'];
            $info['lastDate'] = $data['lastDate'];
            $info['type'] = intval($data['type']);
            $info['leader'] = $data['leader'];
            $info['other'] = $data['other'];
            $info['goodsNo'] = $data['goodsNo'];
            $info['execute'] = $data['execute'];
            $info['status'] = $data['status'];
            $info['statusInfo'] = (array)json_decode($data['statusInfo'], true);
            $info['imgs'] = $data['imgs'] == '' ? [] : json_decode($data['imgs'], true);
            $info['files'] = $data['files'] == '' ? [] : json_decode($data['files'], true);
            $info['beginDate'] = $data['beginDate'];
            $info['endDate'] = $data['endDate'];
            $info['publishName'] = $data['publishName'];
            $info['publishDate'] = $data['publishDate'];
            $info['srcDevReqId'] = intval($data['srcDevReqId']);
            if ($info['srcDevReqId'] > 0) {
                $info['reqInfo'] = $this->db->table('goods_dev_req')->where(['id' => $info['srcDevReqId'], 'isDelete' => 0])->get()->getRowArray();
            }
            $json['status'] = 200;
            $json['msg'] = 'success';
            $json['data'] = $info;
            return $this->response->setJSON($json);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
    // 新增
    public function add()
    {
        if (!$this->myservice->verifyRight('GOODSTASK_ADD')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Base/GoodsTask/add 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $data = $this->request->getGetPost();
        if (is_array($data) && count($data) > 0) {
            $data = $this->validform($data);
            // 如果返回的是 Response 对象，直接返回它
            if ($data instanceof \CodeIgniter\HTTP\Response) {
                return $data;
            }
            $data['publishName'] = $this->myerp['uName'];
            $data['publishDate'] = date("Y-m-d H:i:s");
            $data['checkId'] = $this->myerp['uId'];
            $info = array_intersect_key($data, array_flip(['srcDevReqId', 'title', 'content', 'lastDate', 'type', 'leader', 'other', 'goodsNo', 'execute', 'status', 'statusInfo', 'imgs', 'files', 'publishName', 'publishDate', 'checkId']));
            $this->db->transStart();
            $this->db->table('goods_task')->insert($info);
            $data['id'] = $this->db->insertID();
            $this->db->table('goods_dev_req')->where(['id' => $data['srcDevReqId']])->update(['srcTaskId' => $data['id']]);
            $this->myservice->log('新增了产品开发：' . $data['title']);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '新增成功！', 'data' => $data]);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '提交的是空数据！']);
    }
    // 编辑
    public function update()
    {
        if (!$this->myservice->verifyRight('GOODSTASK_UPDATE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Base/GoodsTask/update 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $data = $this->request->getGetPost();
        if (is_array($data) && count($data) > 0) {
            $data = $this->validform($data);
            // 如果返回的是 Response 对象，直接返回它
            if ($data instanceof \CodeIgniter\HTTP\Response) {
                return $data;
            }
            $info = array_intersect_key($data, array_flip(['title', 'content', 'lastDate', 'type', 'leader', 'other', 'goodsNo', 'status', 'statusInfo', 'imgs', 'files', 'beginDate', 'endDate']));
            $this->db->transStart();
            $this->db->table('goods_task')->where(['id' => $data['id']])->update($info);
            $data = $this->db->table('goods_task')->where(['id' => $data['id']])->get()->getRowArray();
            $this->myservice->log('编辑了产品开发：' . $data['title']);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '编辑成功！', 'data' => $data]);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '提交的是空数据！']);
    }
    // 删除
    public function delete()
    {
        if (!$this->myservice->verifyRight('GOODSTASK_DELETE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Base/GoodsTask/delete 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $data = $this->db->table('goods_task')->where(['id' => $id])->get()->getRowArray();
        if ($data) {
            if ($data['publishName'] != $this->myerp['uName']) {
                return $this->response->setJSON(['status' => -1, 'msg' => '不能删除别人发布的产品开发！']);
            }
            $this->db->transStart();
            $this->db->table('goods_task')->where(['id' => $id])->update(['isDelete' => 1]);
            $this->myservice->log('删除了产品开发，标题：' . $data['title']);
            $this->db->table('goods_dev_req')->where(['id' => $data['srcDevReqId']])->update(['srcTaskId' => 0]);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '删除成功！']);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
    // 审核或反审核
    public function check()
    {
        if (!$this->myservice->verifyRight('GOODSTASK_CHECK')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Base/GoodsTask/check 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $checkId = intval($this->request->getGetPost('checkId'));
        $title = $this->request->getGetPost('title');
        $data = $this->db->table('goods_task')->where(['id' => $id])->get()->getRowArray();
        if ($data) {
            $this->db->transStart();
            $this->db->table('goods_task')->where(['id' => $id])->update(['checkId ' => $checkId]);
            $this->myservice->log(($checkId > 0 ? '审核了' : '反审核了') . '产品开发，项目目标：' . $title);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '操作成功！']);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
    // 追加项目进展信息
    public function addExecuteon()
    {
        $id = intval($this->request->getGetPost('id'));
        $execute = $this->request->getGetPost('execute');
        $data = $this->db->table('goods_task')->where(['id' => $id])->get()->getRowArray();
        if ($data) {
            $new_execute = $data['execute'] . date('Y-m-d H:i:s') . ' ' . $execute . "\r\n";
            $this->db->transStart();
            $this->db->table('goods_task')->where(['id' => $id])->update(['execute' => $new_execute]);
            $this->myservice->log('追加了产品开发进展，项目ID：' . $data['id'] . '，项目进展：' . $execute);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '追加成功！', 'data' => $new_execute]);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
    // 上传图片
    public function uploadImg()
    {
        if (!$this->myservice->verifyRight('GOODSTASK_ADD')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Base/GoodsTask/uploadImg 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        set_time_limit(0);
        session_write_close();
        if (empty($_FILES)){
            return $this->response->setJSON(['status' => -1, 'msg' => '没有上传文件！']);
        };
        $uppath = 'data/upfile/goodsTask/';
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
        $this->myservice->log('上传了产品开发参考图片：' . $_FILES['file']['name']);
        return $this->response->setJSON(['status' => 200, 'msg' => '上传成功！', 'data' => $target_file]);
    }
    // 上传资料
    public function uploadfile()
    {
        if (!$this->myservice->verifyRight('GOODSTASK_ADD')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Base/GoodsTask/uploadfile 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        set_time_limit(0);
        session_write_close();
        if (empty($_FILES)){
            return $this->response->setJSON(['status' => -1, 'msg' => '没有上传文件！']);
        };
        $uppath = 'data/upfile/goodsTask/';
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
        $this->myservice->log('上传了产品开发参考资料：' . $_FILES['file']['name']);
        $json['status'] = 200;
        $json['msg'] = '资料上传成功';
        $json['file'] = ['name' => $_FILES['file']['name'], 'url' => $target_file, 'upName' => $this->myerp['uName'], 'upTime' => date('Y-m-d')];
        return $this->response->setJSON($json);
    }
    // 数据验证
    private function validform(array $data)
    {
        $data['id'] = isset($data['id']) ? intval($data['id']) : -1;
        if (strlen($data['title']) < 1) {
            return $this->response->setJSON(['status' => -1, 'msg' => '项目目标不能为空！']);
        }
        if (strlen($data['lastDate']) < 1) {
            return $this->response->setJSON(['status' => -1, 'msg' => '交付限期不能为空！']);
        }
        if (strlen($data['leader']) < 2) {
            return $this->response->setJSON(['status' => -1, 'msg' => '项目负责人不能为空！']);
        }
        $data['content'] = $data['content'] ?? '';
        $data['leader'] = $data['leader'] ?? '';
        $data['other'] = $data['other'] ?? '';
        if ($data['leader'] && $data['other'] && strpos($data['other'], $data['leader']) !== false) {
            return $this->response->setJSON(['status' => -1, 'msg' => '项目负责人与其他参与人不能重复！']);
        }
        $data['goodsNo'] = $data['goodsNo'] ?? '';
        $data['status'] = $data['status'] ?? '0';
        $data['statusInfo'] = json_encode($data['statusInfo'], 320);
        $data['imgs'] = strlen($data['imgs']) < 10 ? '' : $data['imgs'];
        $data['files'] = strlen($data['files']) < 10 ? '' : $data['files'];
        $statusInfo = (array)json_decode($data['statusInfo'], true);
        $data['beginDate'] = $statusInfo[0]['t'] ?? '0000-00-00 00:00:00';
        $data['endDate'] = $statusInfo[4]['t'] ?? '0000-00-00 00:00:00';
        return $data;
    }
}
