<?php

namespace App\Controllers\Base;
use App\Controllers\BaseController;

class Right extends BaseController
{

    // 系统权限列表
    public function list()
    {
        if (!$this->myservice->verifyRight('SYS_RIGHT_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/Right/list 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $page = max(intval($this->request->getGetPost('page')), 1);
        $rows = max(intval($this->request->getGetPost('rows')), 10000);
        $builder = $this->db->table('right');
        $builder->where('isDelete', 0);
        $list =(clone $builder)->orderBy('ordnum', 'ASC')->orderBy('id', 'ASC')->limit($rows)->offset($rows * ($page - 1))->get()->getResultArray();
        $total = $builder->countAllResults();
        $v = [];
        foreach ($list as $arr => $row) {
            $v[$arr]['group'] = (0 == intval($row['parentId']) ? $row['name'] : '');
            $v[$arr]['name'] = (0 == intval($row['parentId']) ? '查询' : $row['name']);
            $v[$arr]['module'] = $row['module'];
            $v[$arr]['id'] = intval($row['id']);
            $v[$arr]['parentId'] = intval($row['parentId']);
            $v[$arr]['path'] = $row['path'];
            $v[$arr]['level'] = intval($row['level']);
            $v[$arr]['ordnum'] = intval($row['ordnum']);
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
    // 系统权限更新
    public function update()
    {
        if (!$this->myservice->verifyRight('SYS_RIGHT_UPDATE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/Right/update 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $oid = intval($this->request->getGetPost('oid'));
        $name = $this->request->getGetPost('name');
        $module = $this->request->getGetPost('module');
        $id = intval($this->request->getGetPost('id'));
        $parentId = intval($this->request->getGetPost('parentId'));
        $path = $this->request->getGetPost('path');
        $level = intval($this->request->getGetPost('level'));
        $ordnum = intval($this->request->getGetPost('ordnum'));
        if ($oid > 0 && strlen($name) > 0 && strlen($module) > 0 && $id > 0 && $parentId >= 0 && strlen($path) > 0 && $level > 0 && $ordnum > 0) {
            $this->db->transStart();
            $this->db->table('right')->where(['id' => $oid])->update(['id' => $id, 'name' => $name, 'module' => $module, 'parentId' => $parentId, 'path' => $path, 'level' => $level, 'ordnum' => $ordnum]);
            $this->myservice->log('更新了系统权限，别名：' . $module);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '操作成功！']);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '提交的数据不完整！']);
    }
    // 系统权限启用禁用
    public function disable()
    {
        if (!$this->myservice->verifyRight('SYS_RIGHT_UPDATE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/Right/disable 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $isDisable = intval($this->request->getGetPost('isDisable'));
        $module = $this->request->getGetPost('module');
        if ($id > 0) {
            $this->db->transStart();
            $this->db->table('right')->where(['id' => $id])->update(['isDisable' => $isDisable]);
            $this->myservice->log(($isDisable == 1 ? '禁用了' : '启用了') . '系统权限，别名：' . $module);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '操作成功！']);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '提交的是空数据！']);
    }
}
