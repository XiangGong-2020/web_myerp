<?php

namespace App\Controllers\Base;
use App\Controllers\BaseController;

class SalerDuty extends BaseController
{
    // 值班选择 列表
    public function list()
    {
        $dutyDate = $this->request->getGetPost('dutyDate');
        $list = $this->db->table('saler_duty')->where(['dutyDate' => $dutyDate, 'isDisable' => 0, 'isDelete' => 0])->get()->getResultArray();
        $data = [];
        $dutyChanel = $this->db->table('billFrom')->select('name')->where('name !=', "官网")->where('name !=', "手工录入")->where(['isDisable' => 0, 'isDelete' => 0])->orderBy('name', 'ASC')->get()->getResultArray();
        foreach ($dutyChanel as $arr1 => $row1) {
            $data[$row1['name']] = [];
        }
        $data['发货打单'] = [];
        $data['QQ客服'] = [];
        $data['官网客服'] = [];
        $data['晚班客服'] = [];
        foreach ($list as $arr2 => $row2) {
            $row2['id'] = intval($row2['id']);
            $row2['dutyDate'] = $row2['dutyDate'];
            $row2['dutyName'] = $row2['dutyName'];
            $row2['name'] = $row2['name'];
            $row2['createTime'] = $row2['createTime'];
            $row2['isDisable'] = intval($row2['isDisable']);
            $row2['disableTime'] = $row2['disableTime'];
            $row2['description'] = $row2['description'];
            if (isset($data[$row2['dutyName']])) {
                $data[$row2['dutyName']][] = $row2;
            } else {
                $data[$row2['dutyName']] = [$row2];
            }
        }

        $json['status'] = 200;
        $json['msg'] = 'success';
        $json['data'] = $data;
        $dutyNote = $this->db->table('saler_duty_note')->select('note')->where(['dutyDate' => $dutyDate, 'name' => $this->myerp['uName'], 'isDelete' => 0])->get()->getRowArray()['note'] ?? '';
        $json['dutyNote'] = $dutyNote ?? '';
        return $this->response->setJSON($json);
    }
    // 值班选择 选择
    public function choose()
    {
        if ($this->myerp['user']['department'] != '销售部' && !in_array($this->myerp['uName'], ['叶超', '杨颐'])) {
           return $this->response->setJSON(['status' => -1, 'msg' => '仅销售部或运营部相关人员可选择！']);
        };
        $dutyDate = $this->request->getGetPost('dutyDate');
        $dutyName = $this->request->getGetPost('dutyName');
        $name = $this->request->getGetPost('name');
        if ($dutyName && $dutyDate && $name) {
            if ($dutyDate != date('Y-m-d')) {
                return $this->response->setJSON(['status' => -1, 'msg' => '只能选择今天确定性的值班内容！']);
            } elseif (
                $this->db->table('saler_duty')->where(['dutyDate' => $dutyDate, 'dutyName' => $dutyName, 'name' => $name, 'isDisable' => 0, 'isDelete' => 0])->countAllResults() > 0
            ) {
                return $this->response->setJSON(['status' => 200, 'msg' => '已选择过了！']);
            } else {
                if (
                    $this->db->table('saler_duty')->where(['dutyDate' => $dutyDate, 'dutyName' => $dutyName, 'name' => $name, 'isDisable' => 1, 'isDelete' => 0])->countAllResults() > 0
                ) {
                    $res = $this->db->table('saler_duty')
                        ->where(['dutyDate' => $dutyDate, 'dutyName' => $dutyName, 'name' => $name, 'isDisable' => 1, 'isDelete' => 0])->update(['isDisable' => 0]);
                    if ($res) {
                        return $this->response->setJSON(['status' => 200, 'msg' => '选择成功！']);
                    }
                    return $this->response->setJSON(['status' => -1, 'msg' => '选择失败！']);
                } else {
                    $res = $this->db->table('saler_duty')
                        ->insert(['dutyDate' => $dutyDate, 'dutyName' => $dutyName, 'name' => $name, 'createTime' => date('Y-m-d H:i:s')]);
                    if ($res) {
                        return $this->response->setJSON(['status' => 200, 'msg' => '选择成功！']);
                    }
                    return $this->response->setJSON(['status' => -1, 'msg' => '选择失败！']);
                }
            }
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '提交的是空数据！']);
    }
    // 值班选择 放弃
    public function cancel()
    {
        $id = intval($this->request->getGetPost('id'));
        if ($id > 0) {
            $data = $this->db->table('saler_duty')->where(['id' => $id])->get()->getRowArray();
            if ($data['name'] != $this->myerp['uName']) {
                return $this->response->setJSON(['status' => -1, 'msg' => '仅本人可放弃！']);
            } elseif ($data['dutyDate'] != date('Y-m-d')) {
                return $this->response->setJSON(['status' => -1, 'msg' => '只能放弃今天选择的值班内容！']);
            } else {
                $res = $this->db->table('saler_duty')->where(['id' => $id])->update(['isDisable' => 1, 'disableTime' => date('Y-m-d H:i:s')]);
                if ($res) {
                    return $this->response->setJSON(['status' => 200, 'msg' => '放弃成功！']);
                }
                return $this->response->setJSON(['status' => -1, 'msg' => '放弃失败！']);
            }
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '提交的是空数据！']);
    }
    // 值班记录 保存
    public function saveNote()
    {
        if ($this->myerp['user']['department'] != '销售部' && !in_array($this->myerp['uName'], ['叶超', '杨颐'])) {
            return $this->response->setJSON(['status' => -1, 'msg' => '仅销售部或运营部相关人员可保存！']);
        };
        $dutyDate = $this->request->getGetPost('dutyDate');
        $name = $this->request->getGetPost('name');
        $note = $this->request->getGetPost('note');
        if ($dutyDate && $name && $note) {
            if ($dutyDate != date('Y-m-d')) {
                return $this->response->setJSON(['status' => -1, 'msg' => '只能保存今天的值班记录！']);
            } else {
                if ($this->db->table('saler_duty_note')->where(['dutyDate' => $dutyDate, 'name' => $name, 'isDelete' => 0])->countAllResults() > 0) {
                    $res = $this->db->table('saler_duty_note')
                        ->where(['dutyDate' => $dutyDate, 'name' => $name, 'isDelete' => 0])
                        ->update(['note' => $note, 'modifyTime' => date('Y-m-d H:i:s')]);
                    if ($res) {
                        return $this->response->setJSON(['status' => 200, 'msg' => '保存成功！']);
                    }
                    return $this->response->setJSON(['status' => -1, 'msg' => '保存失败！']);
                } else {
                    $res = $this->db->table('saler_duty_note')
                        ->insert(['dutyDate' => $dutyDate, 'name' => $name, 'note' => $note, 'createTime' => date('Y-m-d H:i:s')]);
                    if ($res) {
                        return $this->response->setJSON(['status' => 200, 'msg' => '保存成功！']);
                    }
                    return $this->response->setJSON(['status' => -1, 'msg' => '保存失败！']);
                }
            }
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '提交的是空数据！']);
    }
    // 值班记录 列表
    public function noteList()
    {
        $user = $this->request->getGetPost('user');
        if (($user == '' || $user != $this->myerp['uName']) && !$this->myservice->verifyRight('SYS_ADVANCE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/SalerDuty/noteList 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $page = max(intval($this->request->getGetPost('page')), 1);
        $rows = max(intval($this->request->getGetPost('rows')), 100);
        $sidx = $this->request->getGetPost('sidx');
        $sord = strtoupper((string)$this->request->getGetPost('sord')) === 'ASC' ? 'ASC' : 'DESC';
        $beginDate = $this->request->getGetPost('beginDate');
        $endDate = $this->request->getGetPost('endDate');
        $skey = $this->request->getGetPost('skey');
        $builder = $this->db->table('saler_duty_note');
        $builder->where('isDelete', 0);
        if ($user) {
            $builder->where('name', $user);
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
                    ->orLike('note', $this->db->escapeLikeString($kw))
                ->groupEnd();
            }
        }
        $allowedSort = ['id','name','note','createTime','modifyTime'];
        $orderField  = $sidx ? (in_array($sidx, $allowedSort, true) ? $sidx : 'id') : 'id';
        $list = (clone $builder)->orderBy($orderField, $sord)->limit($rows)->offset($rows * ($page - 1))->get()->getResultArray();
        $total = $builder->countAllResults();
        $v = [];
        foreach ($list as $arr => $row) {
            $v[$arr]['id'] = intval($row['id']);
            $v[$arr]['dutyDate'] = $row['dutyDate'];
            $v[$arr]['name'] = $row['name'];
            $v[$arr]['note'] = $row['note'];
            $v[$arr]['createTime'] = $row['createTime'];
            $v[$arr]['modifyTime'] = $row['modifyTime'];
        }
        $json['status'] = 200;
        $json['msg'] = 'success';
        $json['data']['page'] = $page;
        $json['data']['records'] = $total;
        $json['data']['total'] = ceil($json['data']['records'] / $rows);
        $json['data']['rows'] = $v;
        return $this->response->setJSON($json);
    }
}
