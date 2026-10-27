<?php

namespace App\Controllers\Base;
use App\Controllers\BaseController;

class ManagePlan extends BaseController
{
    // 列表
    public function list()
    {
        if (!$this->myservice->verifyRight('MANAGE_PLAN_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/ManagePlan/list 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $page = max(intval($this->request->getGetPost('page')), 1);
        $rows = max(intval($this->request->getGetPost('rows')), 100);
        $sidx = $this->request->getGetPost('sidx');
        $sord = strtoupper((string)$this->request->getGetPost('sord')) === 'ASC' ? 'ASC' : 'DESC';
        $uId = intval($this->request->getGetPost('uId'));
        $type = intval($this->request->getGetPost('type'));
        $status = intval($this->request->getGetPost('status'));
        $skey = $this->request->getGetPost('skey');
        $beginDate = $this->request->getGetPost('beginDate');
        $endDate = $this->request->getGetPost('endDate');
        $builder = $this->db->table('manage_plan');
        $builder->where('isDelete', 0);
        if ($uId > 0) {
            $builder->where('uId', $uId);
        }
        if ($type >= 0) {
            $builder->where('type', $type);
        }
        if ($status >= 0) {
            $builder->where('isDisable', $status);
        }
        if ($beginDate) {
            $builder->where('date >=', $beginDate);
        }
        if ($endDate) {
            $builder->where('date <=', $endDate);
        }
        if ($skey !== '' && $skey !== null) {
            $keywords = array_values(array_filter(explode(',', $skey), 'strlen'));
            foreach ($keywords as $kw) {
                $builder->groupStart()
                    ->like('target', $this->db->escapeLikeString($kw))
                    ->orLike('summary', $this->db->escapeLikeString($kw))
                    ->orWhere('uName', $kw)
                ->groupEnd();
            }
        }
        $allowedSort = ['id','type','date','target','summary','uName','createTime','summaryTime','isDisable'];
        $orderField  = $sidx ? (in_array($sidx, $allowedSort, true) ? $sidx : 'id') : 'id';
        $list = (clone $builder)->orderBy($orderField, $sord)->limit($rows)->offset($rows * ($page - 1))->get()->getResultArray();
        $total = $builder->countAllResults();
        $v = [];
        foreach ($list as $arr => $row) {
            $v[$arr]['id'] = intval($row['id']);
            $v[$arr]['type'] = intval($row['type']);
            $v[$arr]['date'] = $row['date'];
            $v[$arr]['target'] = $row['target'];
            $v[$arr]['summary'] = $row['summary'];
            $v[$arr]['uId'] = intval($row['uId']);
            $v[$arr]['uName'] = $row['uName'];
            $v[$arr]['createTime'] = $row['createTime'];
            $v[$arr]['summaryTime'] = $row['summaryTime'];
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
        if (!$this->myservice->verifyRight('MANAGE_PLAN_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/ManagePlan/query 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $data = $this->db->table('manage_plan')->where(['id' => $id, 'isDelete' => 0])->get()->getRowArray();
        if ($data) {
            $info['id'] = $id;
            $info['type'] = intval($data['type']);
            $info['date'] = $data['date'];
            $info['target'] = $data['target'];
            $info['summary'] = $data['summary'];
            $info['uId'] = intval($data['uId']);
            $info['createTime'] = $data['createTime'];
            $info['summaryTime'] = $data['summaryTime'];
            $info['targetEditable'] = 1;
            $info['summaryEditable'] = 1;
            $nowTime = time();
            if (
                ($data['type'] == 0 && ($nowTime - strtotime($data['createTime'])) > 7200) ||
                ($data['type'] == 1 && ($nowTime - strtotime($data['createTime'])) > 172800) ||
                ($data['type'] == 2 && ($nowTime - strtotime($data['createTime'])) > 1728000) ||
                ($data['type'] == 3 && ($nowTime - strtotime($data['createTime'])) > 5184000)
            ) {
                $info['targetEditable'] = 0;
            }
            if (
                ($data['type'] == 0 && $data['summaryTime'] != '0000-00-00 00:00:00' && ($nowTime - strtotime($data['summaryTime'])) > 7200) ||
                ($data['type'] == 1 && $data['summaryTime'] != '0000-00-00 00:00:00' && ($nowTime - strtotime($data['summaryTime'])) > 172800) ||
                ($data['type'] == 2 && $data['summaryTime'] != '0000-00-00 00:00:00' && ($nowTime - strtotime($data['summaryTime'])) > 1728000) ||
                ($data['type'] == 3 && $data['summaryTime'] != '0000-00-00 00:00:00' && ($nowTime - strtotime($data['summaryTime'])) > 5184000)
            ) {
                $info['summaryEditable'] = 0;
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
        if (!$this->myservice->verifyRight('MANAGE_PLAN_ADVANCE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/ManagePlan/add 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $data = $this->request->getPost();
        if (is_array($data) && count($data) > 0) {
            $data = $this->validform($data);
            // 如果返回的是 Response 对象，直接返回它
            if ($data instanceof \CodeIgniter\HTTP\Response) {
                return $data;
            }
            $data['date'] = date('Y-m-d');
            if ($data['type'] == 0 && $this->db->table('manage_plan')->where(['date' => $data['date'], 'type' => 0, 'uId' => $this->myerp['uId'], 'isDelete' => 0])->countAllResults() > 2) {
                return $this->response->setJSON(['status' => -1, 'msg' => '日计划每天最多3条！']);
            }
            if ($data['type'] == 1 && $this->db->table('manage_plan')->where(['date >=' => date('Y-m-01'), 'date <=' => date('Y-m-d'), 'type' => 1, 'uId' => $this->myerp['uId'], 'isDelete' => 0])->countAllResults() > 5) {
                return $this->response->setJSON(['status' => -1, 'msg' => '周计划最多每月6条！']);
            }
            if ($data['type'] == 2 && $this->db->table('manage_plan')->where(['date >=' => date('Y-m-01'), 'date <=' => date('Y-m-d'), 'type' => 2, 'uId' => $this->myerp['uId'], 'isDelete' => 0])->countAllResults() > 0) {
                return $this->response->setJSON(['status' => -1, 'msg' => '月计划最多每月1条！']);
            }
            if ($data['type'] == 3 && $this->db->table('manage_plan')->where(['date >=' => date('Y-01-01'), 'date <=' => date('Y-12-31'), 'type' => 3, 'uId' => $this->myerp['uId'], 'isDelete' => 0])->countAllResults() > 0) {
                return $this->response->setJSON(['status' => -1, 'msg' => '年计划最多每年1条！']);
            }
            $data['uId'] = $this->myerp['uId'];
            $data['uName'] = $this->myerp['uName'];
            $data['createTime'] = date('Y-m-d H:i:s');
            $info = array_intersect_key($data, array_flip(['type', 'date', 'target', 'summary', 'uId', 'uName', 'createTime']));
            $this->db->transStart();
            $this->db->table('manage_plan')->insert($info);
            $data['id'] = $this->db->insertID();
            $data['isDisable'] = 0;
            $planTypeArr = ['日','周','月','年'];
            $this->myservice->log('新增了 '.$data['date'].' 的'.$planTypeArr[$data['type']].'管理计划（ID：'.$data['id'].'）');
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
        if (!$this->myservice->verifyRight('MANAGE_PLAN_ADVANCE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/ManagePlan/update 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $data = $this->request->getPost();
        if (is_array($data) && count($data) > 0) {
            $data = $this->validform($data);
            // 如果返回的是 Response 对象，直接返回它
            if ($data instanceof \CodeIgniter\HTTP\Response) {
                return $data;
            }
            $oldData = $this->db->table('manage_plan')->where(['id' => $data['id']])->get()->getRowArray();
            $nowTime = time();
            if ($data['type'] == 0) {
                if ($data['target'] != $oldData['target'] && ($nowTime - strtotime($oldData['createTime'])) > 7200) {
                    return $this->response->setJSON(['status' => -1, 'msg' => '日计划在发布2小时后禁止编辑计划目标！']);
                } elseif ($data['summary'] != $oldData['summary'] && $oldData['summaryTime'] != '0000-00-00 00:00:00' && ($nowTime - strtotime($oldData['summaryTime'])) > 7200) {
                    return $this->response->setJSON(['status' => -1, 'msg' => '日计划在总结提交2小时后禁止再编辑总结！']);
                }
            } elseif ($data['type'] == 1) {
                if ($data['target'] != $oldData['target'] && ($nowTime - strtotime($oldData['createTime'])) > 172800) {
                    return $this->response->setJSON(['status' => -1, 'msg' => '周计划在发布2天后禁止编辑计划目标！']);
                } elseif ($data['summary'] != $oldData['summary'] && $oldData['summaryTime'] != '0000-00-00 00:00:00' && ($nowTime - strtotime($oldData['summaryTime'])) > 172800) {
                    return $this->response->setJSON(['status' => -1, 'msg' => '周计划在总结提交2天后禁止再编辑总结！']);
                }
            } elseif ($data['type'] == 2) {
                if ($data['target'] != $oldData['target'] && ($nowTime - strtotime($oldData['createTime'])) > 1728000) {
                    return $this->response->setJSON(['status' => -1, 'msg' => '月计划在发布20天后禁止编辑计划目标！']);
                } elseif ($data['summary'] != $oldData['summary'] && $oldData['summaryTime'] != '0000-00-00 00:00:00' && ($nowTime - strtotime($oldData['summaryTime'])) > 1728000) {
                    return $this->response->setJSON(['status' => -1, 'msg' => '月计划在总结提交20天后禁止再编辑总结！']);
                }
            } elseif ($data['type'] == 3) {
                if ($data['target'] != $oldData['target'] && ($nowTime - strtotime($oldData['createTime'])) > 5184000) {
                    return $this->response->setJSON(['status' => -1, 'msg' => '年计划在发布60天后禁止编辑计划目标！']);
                } elseif ($data['summary'] != $oldData['summary'] && $oldData['summaryTime'] != '0000-00-00 00:00:00' && ($nowTime - strtotime($oldData['summaryTime'])) > 5184000) {
                    return $this->response->setJSON(['status' => -1, 'msg' => '年计划在总结提交60天后禁止再编辑总结！']);
                }
            }
            $info = array_intersect_key($data, array_flip(['target', 'summary']));
            //首次提交总结内容，记录首次提交时间
            if ($data['summary'] && $oldData['summaryTime'] == '0000-00-00 00:00:00') {
                $info['summaryTime'] = date('Y-m-d H:i:s');
            }
            $this->db->transStart();
            $this->db->table('manage_plan')->update($info, ['id' => $data['id']]);
            $planTypeArr = ['日','周','月','年'];
            $this->myservice->log('编辑了 '. $oldData['date'].' 的'.$planTypeArr[$data['type']].'管理计划（ID：'.$data['id'].'）');
            $data = $this->db->table('manage_plan')->where('id', $data['id'])->get()->getRowArray();
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
        if (!$this->myservice->verifyRight('MANAGE_PLAN_ADVANCE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/ManagePlan/delete 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $data = $this->db->table('manage_plan')->where('id', $id)->get()->getRowArray();
        if ($data) {
            $nowTime = time();
            if (($nowTime - strtotime($data['createTime'])) > 3600) {
                return $this->response->setJSON(['status' => -1, 'msg' => '计划在发布1小时后禁止删除！']);
            }
            if ($data['uId'] != $this->myerp['uId']) {
                return $this->response->setJSON(['status' => -1, 'msg' => '不能删除别人的管理计划！']);
            }
            $this->db->transStart();
            $this->db->table('manage_plan')->where('id', $id)->update(['isDelete' => 1]);
            $planTypeArr = ['日','周','月','年'];
            $this->myservice->log('删除了 '. $data['date'].' 的'.$planTypeArr[$data['type']].'管理计划（ID：'.$data['id'].'）');
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '删除成功！']);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
    // 状态
    public function disable()
    {
        if (!$this->myservice->verifyRight('MANAGE_PLAN_ADVANCE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/ManagePlan/disable 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $isDisable = intval($this->request->getGetPost('isDisable'));
        $data = $this->db->table('manage_plan')->where('id', $id)->get()->getRowArray();
        if ($data) {
            $this->db->transStart();
            $this->db->table('manage_plan')->where('id', $id)->update(['isDisable' => $isDisable]);
            $planTypeArr = ['日','周','月','年'];
            $this->myservice->log('将 '.$data['date'].' 的'.$planTypeArr[$data['type']].'管理计划（ID：'.$data['id'].'）的完成状态标记为' . ($isDisable > 0 ? '已完成' : '未完成'));
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
        $data['type'] = intval($data['type']);
        if (strlen($data['target']) < 1) {
            return $this->response->setJSON(['status' => -1, 'msg' => '计划目标不能为空！']);
        }
        return $data;
    }
}
