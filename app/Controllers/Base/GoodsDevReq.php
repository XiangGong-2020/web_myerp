<?php

namespace App\Controllers\Base;
use App\Controllers\BaseController;

class GoodsDevReq extends BaseController
{
    // 列表
    public function list()
    {
        if (!$this->myservice->verifyRight('GOODSDEVREQ_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Base/GoodsDevReq/list 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $page = max(intval($this->request->getGetPost('page')), 1);
        $rows = max(intval($this->request->getGetPost('rows')), 100);
        $sidx = $this->request->getGetPost('sidx');
        $sord = strtoupper((string)$this->request->getGetPost('sord')) === 'ASC' ? 'ASC' : 'DESC';
        $uId = intval($this->request->getGetPost('uId'));
        $searchType = intval($this->request->getGetPost('searchType'));
        $skey = $this->request->getGetPost('skey');
        $beginDate = $this->request->getGetPost('beginDate');
        $endDate = $this->request->getGetPost('endDate');
        $unDone = intval($this->request->getGetPost('unDone'));
        $builder = $this->db->table('goods_dev_req');
        $builder->where('isDelete', 0);
        if ($uId > 0) {
            $builder->where('uId', $uId);
        }
        if ($searchType == 1) {
            $builder->where('id >', 423);
            $builder->where('srcTaskId', 0);
            $builder->where('description', '');
        } elseif ($searchType == 2) {
            $builder->where('description !=', '');
        }
        if ($beginDate) {
            $builder->where('createTime >=', $beginDate);
        }
        if ($endDate) {
            $builder->where('createTime <=', $endDate);
        }
        if ($unDone > 0) {
            $builder->where('id >', 423);
            $builder->groupStart()
                ->where('checkId', 0)
                ->orWhere('srcTaskId', 0)
            ->groupEnd()
            ->where('description', '');
        }
        if ($skey !== '' && $skey !== null) {
            $keywords = array_values(array_filter(explode(',', $skey), 'strlen'));
            foreach ($keywords as $kw) {
                $builder->groupStart()
                    ->like('title', $this->db->escapeLikeString($kw))
                    ->orLike('content', $this->db->escapeLikeString($kw))
                    ->orLike('uName', $this->db->escapeLikeString($kw))
                    ->orLike('checkName', $this->db->escapeLikeString($kw))
                    ->orLike('backInfo', $this->db->escapeLikeString($kw))
                    ->orLike('description', $this->db->escapeLikeString($kw))
                ->groupEnd();
            }
        }
        $allowedSort = ['id','lastDate','title','content','imgs','files','uId','uName','createTime','checkId','checkName','checkTime','srcTaskId','backInfo','description'];
        $orderField  = $sidx ? (in_array($sidx, $allowedSort, true) ? $sidx : 'id') : 'id';
        $list = (clone $builder)->orderBy($orderField, $sord)->limit($rows)->offset($rows * ($page - 1))->get()->getResultArray();
        $total = $builder->countAllResults();
        $v = [];
        foreach ($list as $arr => $row) {
            $v[$arr]['id'] = intval($row['id']);
            $v[$arr]['lastDate'] = $row['lastDate'];
            $v[$arr]['title'] = $row['title'];
            $v[$arr]['content'] = $row['content'];
            $v[$arr]['imgs'] = $row['imgs'] == '' ? [] : explode(',', $row['imgs']);
            $v[$arr]['files'] = $row['files'] == '' ? [] : json_decode($row['files'], true);
            $v[$arr]['uId'] = intval($row['uId']);
            $v[$arr]['uName'] = $row['uName'];
            $v[$arr]['createTime'] = $row['createTime'] == '0000-00-00 00:00:00' ? '' : $row['createTime'];
            $v[$arr]['checkId'] = intval($row['checkId']);
            $v[$arr]['checkName'] = $row['checkName'];
            $v[$arr]['checkTime'] = $row['checkTime'] == '0000-00-00 00:00:00' ? '' : $row['checkTime'];
            $v[$arr]['srcTaskId'] = intval($row['srcTaskId']);
            $v[$arr]['backInfo'] = $row['backInfo'];
            $v[$arr]['description'] = $row['description'];
        }
        $json['status'] = 200;
        $json['msg'] = 'success';
        $json['data']['page'] = $page;
        $json['data']['records'] = $total;
        $json['data']['total'] = ceil($json['data']['records'] / $rows);
        $json['data']['rows'] = $v;
        return $this->response->setJSON($json);
    }
    // 获取信息
    public function query()
    {
        if (!$this->myservice->verifyRight('GOODSDEVREQ_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Base/GoodsDevReq/query 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $data = $this->db->table('goods_dev_req')->where(['id' => $id, 'isDelete' => 0])->get()->getRowArray();
        if ($data) {
            $info['id'] = $id;
            $info['lastDate'] = $data['lastDate'];
            $info['title'] = $data['title'];
            $info['content'] = $data['content'];
            $info['imgs'] = $data['imgs'] == '' ? [] : explode(',', $data['imgs']);
            $info['files'] = $data['files'] == '' ? [] : json_decode($data['files'], true);
            $info['uId'] = intval($data['uId']);
            $info['uName'] = $data['uName'];
            $info['backInfo'] = $data['backInfo'];
            $info['description'] = $data['description'];
            $info['createTime'] = $data['createTime'];
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
        if (!$this->myservice->verifyRight('GOODSDEVREQ_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Base/GoodsDevReq/add 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $data = $this->request->getGetPost();
        if (is_array($data) && count($data) > 0) {
            $data = $this->validform($data);
            // 如果返回的是 Response 对象，直接返回它
            if ($data instanceof \CodeIgniter\HTTP\Response) {
                return $data;
            }
            if (strtotime($data['lastDate']) - strtotime($data['createTime']) < 18000) {
                return $this->response->setJSON(['status' => -1, 'msg' => '交付限期起码距申请时间5小时以上，请合理指定交付限期，切勿随意录入！']);
            }
            $info = array_intersect_key($data, array_flip(['lastDate', 'title', 'content', 'imgs', 'files', 'uId', 'uName', 'createTime']));
            $this->db->transStart();
            $this->db->table('goods_dev_req')->insert($info);
            $data['id'] = $this->db->insertID();
            $data['imgs'] = $data['imgs'] == '' ? [] : explode(',', $data['imgs']);
            $data['files'] = $data['files'] == '' ? [] : json_decode($data['files'], true);
            $this->myservice->log('新增定制申请，ID：' . $data['id'] . '，标题：' . $data['title']);
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
        if (!$this->myservice->verifyRight('GOODSDEVREQ_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Base/GoodsDevReq/update 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $data = $this->request->getGetPost();
        if (is_array($data) && count($data) > 0) {
            $data = $this->validform($data);
            // 如果返回的是 Response 对象，直接返回它
            if ($data instanceof \CodeIgniter\HTTP\Response) {
                return $data;
            }
            $oldData = $this->db->table('goods_dev_req')->where('id', $data['id'])->get()->getRowArray();
            if ($oldData['checkId'] > 0 && !in_array('GOODSDEVREQ_ADVANCE', $this->myerp['rights'])) {
                return $this->response->setJSON(['status' => -1, 'msg' => '已审批的申请无法编辑！']);
            }
            if ($oldData['srcTaskId'] > 0 && !in_array('GOODSDEVREQ_ADVANCE', $this->myerp['rights'])) {
                return $this->response->setJSON(['status' => -1, 'msg' => '已转化的申请无法编辑！']);
            }
            if (strtotime($data['lastDate']) - strtotime($oldData['createTime']) < 18000) {
                return $this->response->setJSON(['status' => -1, 'msg' => '交付限期起码距申请时间5小时以上，请合理指定交付限期，切勿随意录入！']);
            }
            $info = array_intersect_key($data, array_flip(['lastDate', 'title', 'content', 'imgs', 'files', 'backInfo', 'description']));
            $this->db->transStart();
            $this->db->table('goods_dev_req')->where('id', $data['id'])->update($info);
            if (!$oldData['description'] && $data['description']) {
                $this->db->table('goods_dev_req')->where('id', $data['id'])->update(['checkId' => $this->myerp['uId'], 'checkName' => $this->myerp['uName'], 'checkTime' => date('Y-m-d H:i:s')]);
            }
            $data = $this->db->table('goods_dev_req')->where('id', $data['id'])->get()->getRowArray();
            $data['imgs'] = $data['imgs'] == '' ? [] : explode(',', $data['imgs']);
            $data['files'] = $data['files'] == '' ? [] : json_decode($data['files'], true);
            $this->myservice->log('编辑了定制申请，ID：' . $data['id'] . '，标题：' . $data['title']);
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
        if (!$this->myservice->verifyRight('GOODSDEVREQ_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Base/GoodsDevReq/delete 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $data = $this->db->table('goods_dev_req')->where('id', $id)->get()->getRowArray();
        if ($data) {
            if ($data['uId'] != $this->myerp['uId']) {
                return $this->response->setJSON(['status' => -1, 'msg' => '不能删除别人的定制申请！']);
            }
            if ($data['checkId'] > 0) {
                return $this->response->setJSON(['status' => -1, 'msg' => '已审批的申请无法删除！']);
            }
            if ($data['srcTaskId'] > 0) {
                return $this->response->setJSON(['status' => -1, 'msg' => '已转化的申请无法删除！']);
            }
            $this->db->transStart();
            $this->db->table('goods_dev_req')->where('id', $id)->update(['isDelete' => 1]);
            $this->myservice->log('删除了定制申请，ID: ' . $id . '，标题：' . $data['title']);
            $this->db->transComplete();
            if ($this->db->transStatus() === FALSE) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '删除成功！']);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
    // 审批
    public function check()
    {
        if (!$this->myservice->verifyRight('GOODSDEVREQ_ADVANCE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Base/GoodsDevReq/check 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $checkId = intval($this->request->getGetPost('checkId'));
        $checkName = $this->request->getGetPost('checkName');
        $checkTime = $checkId > 0 ? date('Y-m-d H:i:s') : '0000-00-00 00:00:00';
        $title = $this->request->getGetPost('title');
        $data = $this->db->table('goods_dev_req')->where('id', $id)->get()->getRowArray();
        if ($data) {
            $this->db->transStart();
            $this->db->table('goods_dev_req')->where('id', $id)->update(['checkId ' => $checkId, 'checkName' => $checkName, 'checkTime' => $checkTime]);
            $data['imgs'] = $data['imgs'] == '' ? [] : explode(',', $data['imgs']);
            $data['files'] = $data['files'] == '' ? [] : json_decode($data['files'], true);
            $this->myservice->log(($checkId > 0 ? '审批了' : '反审批了') . '定制申请，ID：' . $id . '，标题：' . $title);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '操作成功！', 'data' => $data]);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
    // 上传图片
    public function uploadImg()
    {
        if (!$this->myservice->verifyRight('GOODSDEVREQ_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Base/GoodsDevReq/uploadImg 权限不足！');
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
        $this->myservice->log('上传了定制申请参考图片：' . $_FILES['file']['name']);
        return $this->response->setJSON(['status' => 200, 'msg' => '上传成功！', 'data' => $target_file]);
    }
    // 上传资料
    public function uploadFile()
    {
        if (!$this->myservice->verifyRight('GOODSDEVREQ_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Base/GoodsDevReq/uploadFile 权限不足！');
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
        $this->myservice->log('上传了定制申请参考资料：' . $_FILES['file']['name']);
        return $this->response->setJSON(['status' => 200, 'msg' => '上传成功！', 'data' => ['name' => $_FILES['file']['name'], 'url' => $target_file]]);
    }
    // 数据验证
    private function validform(array $data)
    {
        $data['id'] = isset($data['id']) ? intval($data['id']) : -1;
        if (strlen($data['title']) < 1) {
            return $this->response->setJSON(['status' => -1, 'msg' => '申请标题不能为空！']);
        }
        if (strlen($data['lastDate']) < 1) {
            return $this->response->setJSON(['status' => -1, 'msg' => '交付限期不能为空！']);
        }
        $data['uId'] = $data['uId'] ?? $this->myerp['uId'];
        $data['uName'] = $data['uName'] ?? $this->myerp['uName'];
        $data['createTime'] = date('Y-m-d H:i:s');
        $data['backInfo'] = $data['backInfo'] ?? '';
        $data['description'] = $data['description'] ?? '';
        $data['imgs'] = strlen($data['imgs']) < 10 ? '' : $data['imgs'];
        $data['files'] = strlen($data['files']) < 10 ? '' : $data['files'];
        return $data;
    }
}
