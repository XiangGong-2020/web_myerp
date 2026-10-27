<?php

namespace App\Controllers\Base;
use App\Controllers\BaseController;

class Option extends BaseController
{

    // 系统参数 编辑
    public function updateSystem()
    {
        if (!$this->myservice->verifyRight('SYS_PAR_UPDATE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/Option/updateSystem 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $data = $this->request->getPost();
        if (is_array($data) && count($data) > 0) {
            $this->db->transStart();
            if ($this->db->table('option')->where('optionName', 'system')->countAllResults() > 0) {
                $this->db->table('option')->where('optionName', 'system')->update(['optionValue', json_encode($data, 320)]);
            } else {
                $option['type'] = 1;
                $option['optionName'] = 'system';
                $option['optioncCname'] = '系统参数';
                $option['optionValue'] = json_encode($data, 320);
                $this->db->table('option')->insert($option);
            }
            $this->myservice->log('编辑了系统参数');
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '更新成功！']);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '提交的是空数据！']);
    }

    // 业绩系数 编辑
    public function updateExpPar()
    {
        if (!$this->myservice->verifyRight('EXP_PAR_UPDATE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/Option/updateExpPar 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $data = $this->request->getPost();
        if (is_array($data) && count($data) > 0) {
            $this->db->transStart();
            if ($this->db->table('option')->where('optionName', 'exp_par')->countAllResults() > 0) {
                $this->db->table('option')->where('optionName', 'exp_par')->update(['optionValue', json_encode($data, 320)]);
            } else {
                $option['type'] = 2;
                $option['optionName'] = 'exp_par';
                $option['optioncCname'] = '业绩系数';
                $option['optionValue'] = json_encode($data, 320);
                $this->db->table('option')->insert($option);
            }
            $this->myservice->log('编辑了业绩系数');
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '更新成功！']);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '提交的是空数据！']);
    }

    // 系统任务 列表
    public function systemTaskList()
    {
        if (!$this->myservice->verifyRight('SYS_TASK_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/Option/systemTaskList 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $list = $this->db->table('option')->where('type', 4)->orderBy('id', 'ASC')->get()->getResultArray();
        $v = [];
        foreach ($list as $arr => $row) {
            $v[$arr]['id'] = intval($row['id']);
            $v[$arr]['optionName'] = $row['optionName'];
            $v[$arr]['optionCname'] = $row['optionCname'];
            $v[$arr]['optionValue'] = $row['optionValue'];
        }
        $json['status'] = 200;
        $json['msg'] = 'success';
        $json['data']['rows'] = $v;
        return $this->response->setJSON($json);
    }
    // 系统任务 更新
    public function systemTaskUpdate()
    {
        if (!$this->myservice->verifyRight('SYS_TASK_UPDATE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/Option/systemTaskUpdate 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $optionvalue = intval($this->request->getGetPost('optionvalue'));
        $data = $this->db->table('option')->where('id', $id)->get()->getRowArray();
        if ($data) {
            $this->db->transStart();
            $this->db->table('option')->where('id', $id)->update(['optionValue' => $optionvalue]);
            $this->myservice->log('更新了系统任务');
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '更新成功！']);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
}
