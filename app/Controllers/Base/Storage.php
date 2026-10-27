<?php

namespace App\Controllers\Base;
use App\Controllers\BaseController;

class Storage extends BaseController
{
    // 仓库列表
    public function list()
    {
        if (!$this->myservice->verifyRight('STORAGE_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/Storage/list 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $page = max(intval($this->request->getGetPost('page')), 1);
        $rows = max(intval($this->request->getGetPost('rows')), 100);
        $sidx = $this->request->getGetPost('sidx');
        $sord = strtoupper((string)$this->request->getGetPost('sord')) === 'ASC' ? 'ASC' : 'DESC';
        $skey = $this->request->getGetPost('skey');
        $builder = $this->db->table('storage');
        $builder->where('isDelete', 0);
        if ($skey !== '' && $skey !== null) {
            $keywords = array_values(array_filter(explode(',', $skey), 'strlen'));
            foreach ($keywords as $kw) {
                $builder->groupStart()
                    ->like('name', $this->db->escapeLikeString($kw))
                    ->orLike('address', $this->db->escapeLikeString($kw))
                    ->orLike('phone', $this->db->escapeLikeString($kw))
                    ->orLike('empName', $this->db->escapeLikeString($kw))
                ->groupEnd();
            }
        }
        $allowedSort = ['id','name','address','phone','empName','type','allowNeg','isDisable'];
        $orderField  = $sidx ? (in_array($sidx, $allowedSort, true) ? $sidx : 'id') : 'id';
        $list = (clone $builder)->orderBy($orderField, $sord)->limit($rows)->offset($rows * ($page - 1))->get()->getResultArray();
        $total = $builder->countAllResults();
        $v = [];
        foreach ($list as $arr => $row) {
            $v[$arr]['id'] = intval($row['id']);
            $v[$arr]['name'] = $row['name'];
            $v[$arr]['address'] = $row['address'];
            $v[$arr]['phone'] = $row['phone'];
            $v[$arr]['empId'] = intval($row['empId']);
            $v[$arr]['empName'] = $row['empName'];
            $v[$arr]['type'] = intval($row['type']);
            $v[$arr]['allowNeg'] = intval($row['allowNeg']);
            $v[$arr]['isDisable'] = intval($row['isDisable']);
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
        if (!$this->myservice->verifyRight('STORAGE_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/Storage/query 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $data = $this->db->table('storage')->where(['id' => $id, 'isDelete' => 0])->get()->getRowArray();
        if ($data) {
            $json['status'] = 200;
            $json['msg'] = 'success';
            $json['data']['id'] = intval($data['id']);
            $json['data']['name'] = $data['name'];
            $json['data']['address'] = $data['address'];
            $json['data']['phone'] = $data['phone'];
            $json['data']['empId'] = intval($data['empId']);
            $json['data']['empName'] = $data['empName'];
            $json['data']['type'] = intval($data['type']);
            $json['data']['allowNeg'] = intval($data['allowNeg']);
            $json['data']['isDisable'] = intval($data['isDisable']);
            return $this->response->setJSON($json);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
    // 新增
    public function add()
    {
        if (!$this->myservice->verifyRight('STORAGE_ADD')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/Storage/add 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $data = $this->request->getPost('postData');
        if (strlen($data) > 0) {
            $data = $this->validform((array)json_decode($data, true));
            // 如果返回的是 Response 对象，直接返回它
            if ($data instanceof \CodeIgniter\HTTP\Response) {
                return $data;
            }
            $this->db->transStart();
            $this->db->table('storage')->insert(array_intersect_key($data, array_flip(['name', 'address', 'phone', 'empId', 'empName', 'type', 'allowNeg'])));
            $data['id'] = $this->db->insertID();
            $this->myservice->log('新增了仓库：' . $data['name']);
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
        if (!$this->myservice->verifyRight('STORAGE_UPDATE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/Storage/update 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $data = $this->request->getPost('postData');
        if (strlen($data) > 0) {
            $data = $this->validform((array)json_decode($data, true));
            $this->db->transStart();
            $this->db->table('storage')->where('id', $data['storageId'])->update(array_intersect_key($data, array_flip(['name', 'address', 'phone', 'empId', 'empName', 'type', 'allowNeg'])));
            $data['id'] = $data['storageId'];
            $this->db->table('goods')->where('storageId', $data['id'])->update(['storageName' => $data['name']]);
            $this->myservice->log('编辑了仓库：' . $data['name']);
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
        if (!$this->myservice->verifyRight('STORAGE_DELETE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/Storage/delete 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('storageId'));
        $data = $this->db->table('storage')->where('id', $id)->get()->getRowArray();
        if ($data) {
            if (
                $this->db->table('goods')->where(['storageId' => $id, 'isDelete' => 0])->countAllResults() > 0 ||
                $this->db->table('bill_order_info')->where(['storageId' => $id, 'isDelete' => 0])->countAllResults() > 0 ||
                $this->db->table('bill_inventory_info')->where(['storageId' => $id, 'isDelete' => 0])->countAllResults() > 0
            ) {
                return $this->response->setJSON(['status' => -1, 'msg' => '不能删除已发生业务的仓库！']);
            }
            $this->db->transStart();
            $this->db->table('storage')->where('id', $id)->update(['isDelete' => 1]);
            $this->myservice->log('删除了仓库，名称：' . $data['name']);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '删除成功！']);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
    // 启用禁用
    public function disable()
    {
        if (!$this->myservice->verifyRight('STORAGE_ENABLE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/Storage/disable 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $isDisable = intval($this->request->getGetPost('isDisable'));
        $data = $this->db->table('storage')->where(['id' => $id, 'isDelete' => 0])->get()->getRowArray();
        if ($data) {
            $this->db->transStart();
            $this->db->table('storage')->where('id', $id)->update(['isDisable' => $isDisable]);
            $this->myservice->log(($isDisable > 0 ? '启用了' : '禁用了') . '仓库：' . $data['name']);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '操作成功！']);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
    // 数据验证
    private function validform(array $data)
    {
        if (strlen($data['name']) < 1) {
            return $this->response->setJSON(['status' => -1, 'msg' => '仓库名称不能为空！']);
        }
        if (strlen($data['address']) < 1) {
            return $this->response->setJSON(['status' => -1, 'msg' => '仓库地址不能为空！']);
        }
        if (strlen($data['phone']) < 1) {
            return $this->response->setJSON(['status' => -1, 'msg' => '仓库电话不能为空！']);
        }
        $data['id'] = isset($data['id']) ? intval($data['id']) : -1;
        $data['storageId'] = isset($data['storageId']) ? intval($data['storageId']) : 0;
        $data['empId'] = isset($data['empId']) ? intval($data['empId']) : 0;
        $data['type'] = isset($data['type']) ? intval($data['type']) : 0;
        $data['allowNeg'] = isset($data['allowNeg']) ? intval($data['allowNeg']) : 0;
        if ($this->db->table('storage')->where(['isDelete' => 0, 'id !=' => $data['storageId'],'name' => $data['name']])->countAllResults() > 0) {
            return $this->response->setJSON(['status' => -1, 'msg' => '仓库名称重复！']);
        }
        return $data;
    }


    // 分区与库位列表
    public function locationList()
    {
        if (!$this->myservice->verifyRight('STORAGE_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/Storage/locationList 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $storageId = intval($this->request->getGetPost('storageId'));
        $type = $this->request->getGetPost('type');
        $builder = $this->db->table('location');
        $builder->where('isDelete', 0);
        if ($storageId > 0) {
            $builder->where('storageId', $storageId);
        }
        if ($type) {
            if ($type == 'parent') {
                $builder->where('parentId', 0);
            } elseif ($type == 'child') {
                $builder->where('parentId >', 0);
            }
        }
        $list = $builder->orderBy('id', 'ASC')->get()->getResultArray();
        $v = [];
        foreach ($list as $arr => $row) {
            $v[$arr]['id'] = intval($row['id']);
            $v[$arr]['storageId'] = intval($row['storageId']);
            $v[$arr]['parentId'] = intval($row['parentId']);
            $v[$arr]['name'] = $row['name'];
        }
        $json['status'] = 200;
        $json['msg'] = 'success';
        $json['data'] = $v;
        return $this->response->setJSON($json);
    }
    // 查询分区与库位
    public function locationQuery()
    {
        if (!$this->myservice->verifyRight('STORAGE_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/Storage/locationQuery 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $data = $this->db->table('location')->where(['id' => $id, 'isDelete' => 0])->get()->getRowArray();
        if ($data) {
            $json['status'] = 200;
            $json['msg'] = 'success';
            $json['data']['id'] = intval($data['id']);
            $json['data']['name'] = $data['name'];
            $json['data']['storageId'] = intval($data['storageId']);
            $json['data']['parentId'] = intval($data['parentId']);
            return $this->response->setJSON($json);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
    // 新增分区与库位
    public function locationAdd()
    {
        if (!$this->myservice->verifyRight('STORAGE_UPDATE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/Storage/locationAdd 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $data = $this->request->getPost();
        if (is_array($data) && count($data) > 0) {
            if (strlen($data['name']) < 1) {
                return $this->response->setJSON(['status' => -1, 'msg' => '名称不能为空！']);
            }
            if ($this->db->table('location')->where(['name' => $data['name'], 'storageId' => $data['storageId'], 'parentId' => $data['parentId'], 'isDelete' => 0])->countAllResults() > 0) {
                return $this->response->setJSON(['status' => -1, 'msg' => '名称重复！']);
            }
            $this->db->transStart();
            $this->db->table('location')->insert(array_intersect_key($data, array_flip(['storageId', 'parentId', 'name'])));
            $data['id'] = $this->db->insertID();
            $this->myservice->log('新增了'. ($data['parentId'] > 0 ? '库位' : '分区') . '：'. $data['name']);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '新增成功！', 'data' => $data]);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '提交的是空数据！']);
    }
    // 编辑分区与库位
    public function locationUpdate()
    {
        if (!$this->myservice->verifyRight('STORAGE_UPDATE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/Storage/locationUpdate 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $data = $this->request->getPost();
        if (is_array($data) && count($data) > 0) {
            if (strlen($data['name']) < 1) {
                return $this->response->setJSON(['status' => -1, 'msg' => '名称不能为空！']);
            }
            if ($this->db->table('location')->where(['id !=' => $data['id'], 'name' => $data['name'], 'storageId' => $data['storageId'], 'parentId' => $data['parentId'], 'isDelete' => 0])->countAllResults() > 0) {
                return $this->response->setJSON(['status' => -1, 'msg' => '名称重复！']);
            }
            $this->db->transStart();
            $this->db->table('location')->where(['id' => $data['id']])->update(['name' => $data['name']]);
            $this->db->table('goods')->where(['locationId' => $data['id']])->update(['locationName' => $data['name']]);
            $this->myservice->log('编辑了'. ($data['parentId'] > 0 ? '库位' : '分区') . '：'. $data['name']);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '编辑成功！', 'data' => $data]);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '提交的是空数据！']);
    }
    // 删除分区与库位
    public function locationDelete()
    {
        if (!$this->myservice->verifyRight('STORAGE_UPDATE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/Storage/locationDelete 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $data = $this->db->table('location')->where(['id' => $id])->get()->getRowArray();
        if ($data) {
            if ($data['parentId'] == 0) {
                if ($this->db->table('location')->where(['parentId' => $data['id'], 'isDelete' => 0])->countAllResults() > 0) {
                    return $this->response->setJSON(['status' => -1, 'msg' => '不能删除已建立库位的分区！']);
                }
            } else{
                if ($this->db->table('goods')->where(['locationId' => $data['id'], 'isDelete' => 0])->countAllResults() > 0) {
                    return $this->response->setJSON(['status' => -1, 'msg' => '不能删除已被产品使用的库位！']);
                }
            }
            $this->db->transStart();
            $this->db->table('location')->where(['id' => $id])->update(['isDelete' => 1]);
            $this->myservice->log('删除了'. ($data['parentId'] > 0 ? '库位' : '分区') . '：'. $data['name']);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '删除成功！']);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
}
