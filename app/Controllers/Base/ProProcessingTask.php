<?php

namespace App\Controllers\Base;
use App\Controllers\BaseController;

class ProProcessingTask extends BaseController
{
    // 加工任务 列表
    public function list()
    {
        if (!$this->myservice->verifyRight('PROTASK_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/ProProcessingTask/list 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $page = max(intval($this->request->getGetPost('page')), 1);
        $rows = max(intval($this->request->getGetPost('rows')), 100);
        $sidx = $this->request->getGetPost('sidx');
        $sord = strtoupper((string)$this->request->getGetPost('sord')) === 'ASC' ? 'ASC' : 'DESC';
        $status1 = $this->request->getGetPost('status1');
        $status2 = $this->request->getGetPost('status2');
        $status3 = $this->request->getGetPost('status3');
        $status4 = intval($this->request->getGetPost('status4'));
        $status5 = intval($this->request->getGetPost('status5'));
        $status6 = intval($this->request->getGetPost('status6'));
        $skey = $this->request->getGetPost('skey');
        $beginDate = $this->request->getGetPost('beginDate');
        $endDate = $this->request->getGetPost('endDate');
        $unCheck = intval($this->request->getGetPost('unCheck'));
        $uId = intval($this->request->getGetPost('uId'));
        $unDone = intval($this->request->getGetPost('unDone'));
        $builder = $this->db->table('pro_processing_task');
        $builder->where('isDelete', 0);
        if ($status1) {
            $builder->where('reqName', $status1);
        }
        if ($status2) {
            $builder->where('uName', $status2);
        }
        if ($status3) {
<<<<<<< HEAD
            $builder->where('FIND_IN_SET(' . $this->db->escape($status3) . ', takeName) >', 0, false);
=======
            $builder->where("FIND_IN_SET(" . $this->db->escape($status3) . ", takeName) >", 0, false);
>>>>>>> 151b415 (更新语言最新版本+最新底层框架后全部差异化代码适配重写)
        }
        if ($status4 >= 0) {
            if ($status4 == 0) {
                $builder->where('checkId', 0);
            } else {
                $builder->where('checkId >', 0);
            }
        }
        if ($status5 >= 0) {
            $builder->where('takeStatus', $status5);
        }
        if ($status6 >= 0) {
            $builder->where('taskStatus', $status6);
        }
        if ($beginDate) {
            $builder->where('createTime >=', $beginDate);
        }
        if ($endDate) {
            $builder->where('createTime <=', $endDate);
        }
        if ($unCheck > 0) {
            $builder->where('checkId', 0);
        }
        if ($uId > 0) {
            $builder->where('uId', $uId);
        }
        if ($unDone > 0) {
            $builder->where('taskStatus <', 3);
        }
        if ($skey !== '' && $skey !== null) {
            $keywords = array_values(array_filter(explode(',', $skey), 'strlen'));
            foreach ($keywords as $kw) {
                $builder->groupStart()
                    ->like('projectName', $this->db->escapeLikeString($kw))
                    ->orLike('description', $this->db->escapeLikeString($kw))
                ->groupEnd();
            }
        }
        $allowedSort = ['id','reqId','reqName','reqTime','lastDate','projectId','projectType','projectName','qty','uName','checkId','checkName','description','createTime','takeStatus','taskStatus','backInfo'];
        $orderField  = $sidx ? (in_array($sidx, $allowedSort, true) ? $sidx : 'id') : 'id';
        $list = (clone $builder)->orderBy($orderField, $sord)->limit($rows)->offset($rows * ($page - 1))->get()->getResultArray();
        $totalQty = (clone $builder)->selectSum('qty', 'totalQty')->get()->getRowArray()['totalQty'] ?? 0;
        $total = $builder->countAllResults();
        $v = [];
        foreach ($list as $arr => $row) {
            $v[$arr]['id'] = intval($row['id']);
            $v[$arr]['reqId'] = intval($row['reqId']);
            $v[$arr]['reqName'] = $row['reqName'];
            $v[$arr]['reqTime'] = $row['reqTime'] == '0000-00-00 00:00:00' ? '' : $row['reqTime'];
            $v[$arr]['lastDate'] = $row['lastDate'];
            $v[$arr]['projectId'] = intval($row['projectId']);
            $v[$arr]['projectType'] = intval($row['projectType']);
            $v[$arr]['projectName'] = $row['projectName'];
            $v[$arr]['needTest'] = intval($row['needTest']);
            $v[$arr]['qty'] = intval($row['qty']);
            $v[$arr]['uId'] = intval($row['uId']);
            $v[$arr]['uName'] = $row['uName'];
            $v[$arr]['checkId'] = intval($row['checkId']);
            $v[$arr]['checkName'] = $row['checkName'];
            $v[$arr]['description'] = $row['description'];
            $v[$arr]['createTime'] = $row['createTime'] == '0000-00-00 00:00:00' ? '' : $row['createTime'];
            $v[$arr]['takeStatus'] = intval($row['takeStatus']);
            $v[$arr]['taskStatus'] = intval($row['taskStatus']);
            $v[$arr]['backInfo'] = $row['backInfo'];
        }
        $json['status'] = 200;
        $json['msg'] = 'success';
        $json['data']['page'] = $page;
        $json['data']['records'] = $total;
        $json['data']['total'] = ceil($json['data']['records'] / $rows);
        $json['data']['rows'] = $v;
        $json['data']['totalQty'] = $totalQty;
        return $this->response->setJSON($json);
    }
    // 加工任务 获取信息
    public function query()
    {
        if (!$this->myservice->verifyRight('PROTASK_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/ProProcessingTask/query 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $data = $this->db->table('pro_processing_task')->where(['id' => $id, 'isDelete' => 0])->get()->getRowArray();
        if ($data) {
            $info['id'] = $id;
            $info['lastDate'] = $data['lastDate'];
            $info['projectId'] = intval($data['projectId']);
            $info['projectType'] = intval($data['projectType']);
            $info['projectName'] = $data['projectName'];
            $info['needTest'] = intval($data['needTest']);
            $info['qty'] = intval($data['qty']);
            $info['uId'] = intval($data['uId']);
            $info['uName'] = $data['uName'];
            $info['checkId'] = intval($data['checkId']);
            $info['checkName'] = $data['checkName'];
            $info['reqId'] = intval($data['checkId']);
            $info['reqName'] = $data['reqName'];
            $info['reqTime'] = $data['reqTime'] == '0000-00-00 00:00:00' ? '' : $data['reqTime'];
            $info['description'] = $data['description'];
            $info['createTime'] = $data['createTime'] == '0000-00-00 00:00:00' ? '' : $data['createTime'];
            $info['takeStatus'] = intval($data['takeStatus']);
            $info['taskStatus'] = intval($data['taskStatus']);
            $list = $this->db->table('pro_processing_task_get')->where(['iid' => $id, 'isDelete' => 0])->get()->getResultArray();
            $v = [];
            foreach ($list as $arr => $row) {
                $v[$arr]['id'] = intval($row['id']);
                $v[$arr]['iid'] = intval($row['iid']);
                $v[$arr]['lastDate'] = $row['lastDate'];
                $v[$arr]['projectId'] = intval($row['projectId']);
                $v[$arr]['projectType'] = intval($row['projectType']);
                $v[$arr]['projectName'] = $row['projectName'];
                $v[$arr]['needTest'] = intval($row['needTest']);
                $v[$arr]['description'] = $row['description'];
                $v[$arr]['taskQty'] = intval($row['taskQty']);
                $v[$arr]['getQty'] = intval($row['getQty']);
                $v[$arr]['isTest'] = intval($row['isTest']);
                $v[$arr]['upQty'] = intval($row['upQty']);
                $v[$arr]['doneStatus'] = intval($row['doneStatus']);
                $v[$arr]['uId'] = intval($row['uId']);
                $v[$arr]['uName'] = $row['uName'];
                $v[$arr]['createTime'] = $row['createTime'] == '0000-00-00 00:00:00' ? '' : $row['createTime'];
                $v[$arr]['backInfo'] = $row['backInfo'];
                $v[$arr]['transInfo'] = $row['transInfo'] ? (array)json_decode($row['transInfo'], TRUE) : '';
                $v[$arr]['transHis'] = $row['transHis'];
                $v[$arr]['isGroupTrans'] = intval($row['isGroupTrans']);
            }
            $info['entries'] = $v;
            $json['status'] = 200;
            $json['msg'] = 'success';
            $json['data'] = $info;
            return $this->response->setJSON($json);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
    // 加工任务 新增
    public function add()
    {
        if (!$this->myservice->verifyRight('PROTASK_ADVANCE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/ProProcessingTask/add 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $data = $this->request->getPost();
        $data = $this->validform($data);
        // 如果返回的是 Response 对象，直接返回它
        if ($data instanceof \CodeIgniter\HTTP\Response) {
            return $data;
        }
        if (is_array($data) && count($data) > 0) {
            if (strtotime($data['lastDate']) - strtotime($data['createTime']) < 14400) {
                return $this->response->setJSON(['status' => -1, 'msg' => '交付限期起码距发布时间4小时以上，请合理指定交付限期，切勿随意录入！']);
            }
            $data['checkId'] = $data['uId'];
            $data['checkName'] = $data['uName'];
            $data['takeStatus'] = $data['taskStatus'] = 0;
            $info = array_intersect_key($data, array_flip(['reqId', 'reqName', 'reqTime', 'lastDate', 'projectId', 'projectType', 'projectName', 'needTest', 'qty', 'description', 'uId', 'uName', 'checkId', 'checkName', 'takeStatus', 'taskStatus', 'createTime']));
            $this->db->transStart();
            $this->db->table('pro_processing_task')->insert($info);
            $data['id'] = $this->db->insertID();
            if ($data['producers']) {
                $createTime = date('Y-m-d H:i:s');
                $getProduceData = $getTestData = [];
                // 先处理加工工序信息
                $producersArr = explode(',', $data['producers']);
                $producerNum = count($producersArr);
                // 计算任务产量除以组员人数的商和余数，来平均分配产量，余数(剩余产量)加给随机组员
                $perQty = intdiv(intval($data['qty']), $producerNum); // 取商为平均领取产量
                $remainQty = intval($data['qty']) % $producerNum; // 取余为剩余产量
                // 取合理随机数
                $randKey = rand(0, $producerNum - 1);
                foreach ($producersArr as $k => $v) {
                    $user = $this->db->table('user')->select('id,name')->where(['name' => $v])->get()->getRowArray();
                    $eachGetData = [
                        'iid' => $data['id'],
                        'lastDate' => $data['lastDate'],
                        'projectId' => $data['projectId'],
                        'projectType' => $data['projectType'],
                        'projectName' => $data['projectName'],
                        'needTest' => $data['needTest'],
                        'description' => $data['description'],
                        'taskQty' => $data['qty'],
                        'getQty' => $k == $randKey ? ($perQty + $remainQty) : $perQty, // 剩余产量加给随机一名组员
                        'isTest' => 0,
                        'uId' => $user['id'],
                        'uName' => $user['name'],
                        'createTime' => $createTime
                    ];
                    array_push($getProduceData, $eachGetData);
                }
                $this->db->table('pro_processing_task_get')->insertBatch($getProduceData);
                // 再处理测试工序信息
                if ($data['needTest'] == 1 && $data['testers']) {
                    $testersArr = explode(',', $data['testers']);
                    $testerNum = count($testersArr);
                    // 计算任务产量除以组员人数的商和余数，来平均分配产量，余数(剩余产量)加给随机组员
                    $perQty = intdiv(intval($data['qty']), $testerNum); // 取商为平均领取产量
                    $remainQty = intval($data['qty']) % $testerNum; // 取余为剩余产量
                    // 取合理随机数
                    $randKey = rand(0, $testerNum - 1);
                    foreach ($testersArr as $k => $v) {
                        $user = $this->db->table('user')->select('id,name')->where(['name' => $v])->get()->getRowArray();
                        $eachGetData = [
                            'iid' => $data['id'],
                            'lastDate' => $data['lastDate'],
                            'projectId' => $data['projectId'],
                            'projectType' => $data['projectType'],
                            'projectName' => $data['projectName'],
                            'needTest' => $data['needTest'],
                            'description' => $data['description'],
                            'taskQty' => $data['qty'],
                            'getQty' => $k == $randKey ? ($perQty + $remainQty) : $perQty, // 剩余产量加给随机一名组员
                            'isTest' => 1,
                            'uId' => $user['id'],
                            'uName' => $user['name'],
                            'createTime' => $createTime
                        ];
                        array_push($getTestData, $eachGetData);
                    }
                    $this->db->table('pro_processing_task_get')->insertBatch($getTestData);
                }
                $data['takeName'] = '';
                if ($data['producers']) {
                    $takeName = $data['producers'] .= (($data['needTest'] == 1 && $data['testers']) ? (','.$data['testers']) : '');
                    $data['takeName'] = implode(',', array_unique(explode(',', $takeName)));
                }
                $this->db->table('pro_processing_task')->where(['id' => $data['id']])->update(['takeStatus' => 2, 'takeName' => $data['takeName'], 'taskStatus' => 1]);
            }
            if ($data['reqId'] > 0) {
                $this->db->table('pro_processing_req')->where(['id' => $data['reqId']])->update(['produceId' => $data['uId'], 'produceName' => $data['uName'], 'produceTime' => date('Y-m-d H:i:s'), 'taskId' => $data['id']]);
            }
            $this->myservice->log('新增了加工任务，任务编号：' . $data['id'] . '，加工项目：' . $data['projectName'] . '，任务产量：' . $data['qty'] . ($data['producers'] ? ('，指定加工人：' . $data['producers']) : '') . ($data['testers'] ? ('，指定测试人：' . $data['testers']) : ''));
            $data = $this->db->table('pro_processing_task')->where(['id' => $data['id']])->get()->getRowArray();
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '新增成功！', 'data' => $data]);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '提交的是空数据！']);
    }
    // 加工任务 编辑
    public function update()
    {
        if (!$this->myservice->verifyRight('PROTASK_ADVANCE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/ProProcessingTask/update 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $data = $this->request->getPost();
        $data = $this->validform($data);
        // 如果返回的是 Response 对象，直接返回它
        if ($data instanceof \CodeIgniter\HTTP\Response) {
            return $data;
        }
        $oldData = $this->db->table('pro_processing_task')->where(['id' => $data['id']])->get()->getRowArray();
        if (strtotime($data['lastDate']) - strtotime($oldData['createTime']) < 14400) {
            return $this->response->setJSON(['status' => -1, 'msg' => '交付限期起码距发布时间4小时以上，请合理指定交付限期，切勿随意录入！']);
        }
        if (is_array($data) && count($data) > 0) {
            $info = array_intersect_key($data, array_flip(['reqName', 'reqTime', 'lastDate', 'projectId', 'projectType', 'projectName', 'needTest', 'qty', 'description']));
            if ($data['producers'] && $this->db->table('pro_processing_task_up')->where(['iid' => $data['id'], 'isDelete' => 0])->countAllResults() > 0) {
                return $this->response->setJSON(['status' => -1, 'msg' => '已有交付，无法重新指定加工人！']);
            }
            $this->db->transStart();
            $this->db->table('pro_processing_task')->where(['id' => $data['id']])->update($info);
            if ($data['producers']) {
                $this->db->table('pro_processing_task_get')->where(['iid' => $data['id']])->update(['isDelete' => 1]); // 先删除旧数据
                $createTime = date('Y-m-d H:i:s');
                $geProducetData = $getTestData = [];
                $producersArr = explode(',', $data['producers']);
                $producerNum = count($producersArr);
                // 计算任务产量除以组员人数的商和余数，来平均分配产量，余数(剩余产量)加给随机组员
                $perQty = intdiv(intval($data['qty']), $producerNum); // 取商为平均领取产量
                $remainQty = intval($data['qty']) % $producerNum; // 取余为剩余产量
                // 取合理随机数
                $randKey = rand(0, $producerNum - 1);
                foreach ($producersArr as $k => $v) {
                    $user = $this->db->table('user')->select('id,name')->where(['name' => $v])->get()->getRowArray();
                    $eachGetData = [
                        'iid' => $data['id'],
                        'lastDate' => $data['lastDate'],
                        'projectId' => $data['projectId'],
                        'projectType' => $data['projectType'],
                        'projectName' => $data['projectName'],
                        'needTest' => $data['needTest'],
                        'description' => $data['description'],
                        'taskQty' => $data['qty'],
                        'getQty' => $k == $randKey ? ($perQty + $remainQty) : $perQty, // 剩余产量加给随机一名组员
                        'isTest' => 0,
                        'uId' => $user['id'],
                        'uName' => $user['name'],
                        'createTime' => $createTime
                    ];
                    array_push($geProducetData, $eachGetData);
                }
                $this->db->table('pro_processing_task_get')->insertBatch($geProducetData);
                // 再处理测试人
                if ($data['needTest'] == 1 && $data['testers']) {
                    $testersArr = explode(',', $data['testers']);
                    $testerNum = count($testersArr);
                    // 计算任务产量除以组员人数的商和余数，来平均分配产量，余数(剩余产量)加给随机组员
                    $perQty = intdiv(intval($data['qty']), $testerNum); // 取商为平均领取产量
                    $remainQty = intval($data['qty']) % $testerNum; // 取余为剩余产量
                    // 取合理随机数
                    $randKey = rand(0, $testerNum - 1);
                    foreach ($testersArr as $k => $v) {
                        $user = $this->db->table('user')->select('id,name')->where(['name' => $v])->get()->getRowArray();
                        $eachGetData = [
                            'iid' => $data['id'],
                            'lastDate' => $data['lastDate'],
                            'projectId' => $data['projectId'],
                            'projectType' => $data['projectType'],
                            'projectName' => $data['projectName'],
                            'needTest' => $data['needTest'],
                            'description' => $data['description'],
                            'taskQty' => $data['qty'],
                            'getQty' => $k == $randKey ? ($perQty + $remainQty) : $perQty, // 剩余产量加给随机一名组员
                            'isTest' => 1,
                            'uId' => $user['id'],
                            'uName' => $user['name'],
                            'createTime' => $createTime
                        ];
                        array_push($getTestData, $eachGetData);
                    }
                    $this->db->table('pro_processing_task_get')->insertBatch($getTestData);
                }
                $data['takeName'] = '';
                if ($data['producers']) {
                    $takeName = $data['producers'] .= (($data['needTest'] == 1 && $data['testers']) ? (','.$data['testers']) : '');
                    $data['takeName'] = implode(',', array_unique(explode(',', $takeName)));
                }
                $this->db->table('pro_processing_task')->where(['id' => $data['id']])->update(['takeStatus' => 2, 'takeName' => $data['takeName'], 'taskStatus' => 1]);
            }
            $this->myservice->log('编辑了加工任务，任务编号：' . $data['id'] . '，加工项目：' . $data['projectName'] . '，任务产量：' . $data['qty'] . ($data['producers'] ? ('，指定加工人：' . $data['producers']) : '') . ($data['testers'] ? ('，指定测试人：' . $data['testers']) : ''));
            $data = $this->db->table('pro_processing_task')->where(['id' => $data['id']])->get()->getRowArray();
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '编辑成功！', 'data' => $data]);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '提交的是空数据！']);
    }
    // 加工任务 删除
    public function delete()
    {
        if (!$this->myservice->verifyRight('PROTASK_ADVANCE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/ProProcessingTask/delete 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $data = $this->db->table('pro_processing_task')->where(['id' => $id])->get()->getRowArray();
        if ($data) {
            if ($data['uId'] != $this->myerp['uId'] && !$this->myservice->verifyRight('SYS_ADMIN')) {
                return $this->response->setJSON(['status' => -1, 'msg' => '不能删除别人发布的加工任务，仅本人或管理员可删除！']);
            }
            $taskUp = $this->db->table('pro_processing_task_up')->where(['iid' => $id, 'isDelete' => 0])->countAllResults();
            if ($taskUp > 0) {
                return $this->response->setJSON(['status' => -1, 'msg' => '任务已有交付，不允许随意删除！']);
            }
            $this->db->transStart();
            $this->db->table('pro_processing_task')->where(['id' => $id])->update(['isDelete' => 1]);
            $this->db->table('pro_processing_task_get')->where(['iid' => $id])->update(['isDelete' => 1]);
            if ($data['reqId'] > 0) {
                $this->db->table('pro_processing_req')->where(['id' => $data['reqId']])->update(['produceId' => 0, 'produceName' => '', 'produceTime' => '', 'taskId' => 0]);
            }
            $this->myservice->log('删除加工任务，任务编号：' . $data['id'] . '，加工项目：' . $data['projectName'] . '，任务产量：' . $data['qty']);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '删除成功！']);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
    // 加工任务 发布与撤回
    public function check()
    {
        if (!$this->myservice->verifyRight('PROTASK_ADVANCE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/ProProcessingTask/check 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $checkId = intval($this->request->getGetPost('checkId'));
        $checkName = $this->request->getGetPost('checkName');
        $title = $this->request->getGetPost('title');
        if ($id > 0) {
            $this->db->transStart();
            $this->db->table('pro_processing_task')->where(['id' => $id])->update(['checkId ' => $checkId, 'checkName' => $checkName]);
            $this->myservice->log(($checkId > 0 ? '发布了' : '撤回了') . $title . ' 的加工任务。');
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '操作成功！']);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
    // 加工任务 数据验证
    private function validform(array $data)
    {
        $project = $this->db->table('pro_processing_project')->where(['id' => $data['projectId']])->get()->getRowArray();
        if (!$project || $project['isDelete'] == 1) {
            return $this->response->setJSON(['status' => -1, 'msg' => '加工项目不存在！']);
        }
        if ($project['isDisable'] == 1) {
            return $this->response->setJSON(['status' => -1, 'msg' => '将停产不再生产！']);
        }
        if ($project['isDisable'] == 2) {
            return $this->response->setJSON(['status' => -1, 'msg' => '已停产不再生产！']);
        }
        if ($project['isDisable'] == 9) {
            return $this->response->setJSON(['status' => -1, 'msg' => '已禁用不再生产！']);
        }
        $data['projectType'] = $project['type'];
        $data['needTest'] = $project['needTest'];
        $data['uId'] = $data['uId'] ?? $this->myerp['uId'];
        $data['uName'] = $data['uName'] ?? $this->myerp['uName'];
        $data['createTime'] = date('Y-m-d H:i:s');
        $data['description'] = $data['description'] ?? '';
        return $data;
    }

    // 加工任务 单任务领取列表
    public function get_list()
    {
        if (!$this->myservice->verifyRight('PROTASK_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/ProProcessingTask/get_list 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $sidx = $this->request->getGetPost('sidx');
        $sord = strtoupper((string)$this->request->getGetPost('sord')) === 'ASC' ? 'ASC' : 'DESC';
        $id = intval($this->request->getGetPost('id'));
        $beginDate = $this->request->getGetPost('beginDate');
        $endDate = $this->request->getGetPost('endDate');
        $builder = $this->db->table('pro_processing_task_get');
        $builder->where('iid', $id)->where('isDelete', 0);
        if ($beginDate) {
            $builder->where('createTime >=', $beginDate);
        }
        if ($endDate) {
            $builder->where('createTime <=', $endDate);
        }
        $allowedSort = ['id','iid','lastDate','projectId','projectType','projectName','needTest','description','taskQty','getQty','isTest','upQty','doneStatus','uName','createTime','backInfo','transInfo','transHis','isGroupTrans'];
        $orderField  = $sidx ? (in_array($sidx, $allowedSort, true) ? $sidx : 'id') : 'id';
        $list = (clone $builder)->orderBy($orderField, $sord)->get()->getResultArray();
        $total = $builder->countAllResults();
        $v = [];
        foreach ($list as $arr => $row) {
            $v[$arr]['id'] = intval($row['id']);
            $v[$arr]['iid'] = intval($row['iid']);
            $v[$arr]['lastDate'] = $row['lastDate'];
            $v[$arr]['projectId'] = intval($row['projectId']);
            $v[$arr]['projectType'] = intval($row['projectType']);
            $v[$arr]['projectName'] = $row['projectName'];
            $v[$arr]['needTest'] = intval($row['needTest']);
            $v[$arr]['description'] = $row['description'];
            $v[$arr]['taskQty'] = intval($row['taskQty']);
            $v[$arr]['getQty'] = intval($row['getQty']);
            $v[$arr]['isTest'] = intval($row['isTest']);
            $v[$arr]['upQty'] = intval($row['upQty']);
            $v[$arr]['doneStatus'] = intval($row['doneStatus']);
            $v[$arr]['uId'] = intval($row['uId']);
            $v[$arr]['uName'] = $row['uName'];
            $v[$arr]['createTime'] = $row['createTime'] == '0000-00-00 00:00:00' ? '' : $row['createTime'];
            $v[$arr]['backInfo'] = $row['backInfo'];
            $v[$arr]['transInfo'] = $row['transInfo'] ? (array)json_decode($row['transInfo'], TRUE) : '';
            $v[$arr]['transHis'] = $row['transHis'];
            $v[$arr]['isGroupTrans'] = intval($row['isGroupTrans']);
        }
        $json['status'] = 200;
        $json['msg'] = 'success';
        $json['data']['page'] = 1;
        $json['data']['records'] = $total;
        $json['data']['total'] = 1;
        $json['data']['rows'] = $v;
        return $this->response->setJSON($json);
    }

    // 加工任务 单任务交付列表
    public function up_list()
    {
        if (!$this->myservice->verifyRight('PROTASK_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/ProProcessingTask/up_list 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $page = max(intval($this->request->getGetPost('page')), 1);
        $rows = max(intval($this->request->getGetPost('rows')), 100);
        $sidx = $this->request->getGetPost('sidx');
        $sord = strtoupper((string)$this->request->getGetPost('sord')) === 'ASC' ? 'ASC' : 'DESC';
        $id = intval($this->request->getGetPost('id'));
        $beginDate = $this->request->getGetPost('beginDate');
        $endDate = $this->request->getGetPost('endDate');
        $builder = $this->db->table('pro_processing_task_up');
        $builder->where('iid', $id)->where('isDelete', 0);
        if ($beginDate) {
            $builder->where('createTime >=', $beginDate);
        }
        if ($endDate) {
            $builder->where('createTime <=', $endDate);
        }
        $allowedSort = ['id','iid','lastDate','projectId','projectType','projectName','needTest','description','taskQty','getQty','isTest','upQty','price','amount','upDescription','upImgs','doneStatus','uName','createTime'];
        $orderField  = $sidx ? (in_array($sidx, $allowedSort, true) ? $sidx : 'id') : 'id';
        $list = (clone $builder)->orderBy($orderField, $sord)->get()->getResultArray();
        $total = $builder->countAllResults();
        $v = [];
        foreach ($list as $arr => $row) {
            $v[$arr]['id'] = intval($row['id']);
            $v[$arr]['iid'] = intval($row['iid']);
            $v[$arr]['lastDate'] = $row['lastDate'];
            $v[$arr]['projectId'] = intval($row['projectId']);
            $v[$arr]['projectType'] = intval($row['projectType']);
            $v[$arr]['projectName'] = $row['projectName'];
            $v[$arr]['needTest'] = intval($row['needTest']);
            $v[$arr]['taskQty'] = intval($row['taskQty']);
            $v[$arr]['getQty'] = intval($row['getQty']);
            $v[$arr]['isTest'] = intval($row['isTest']);
            $v[$arr]['upQty'] = intval($row['upQty']);
            $v[$arr]['price'] = (float)$row['price'];
            $v[$arr]['amount'] = (float)$row['amount'];
            $v[$arr]['upDescription'] = $row['upDescription'];
            $v[$arr]['upImgs'] = $row['upImgs'] == '' ? [] : explode(',', $row['upImgs']);
            $v[$arr]['doneStatus'] = intval($row['doneStatus']);
            $v[$arr]['uId'] = intval($row['uId']);
            $v[$arr]['uName'] = $row['uName'];
            $v[$arr]['createTime'] = $row['createTime'] == '0000-00-00 00:00:00' ? '' : $row['createTime'];
        }
        $json['status'] = 200;
        $json['msg'] = 'success';
        $json['data']['page'] = $page;
        $json['data']['records'] = $total;
        $json['data']['total'] = ceil($json['data']['records'] / $rows);
        $json['data']['rows'] = $v;
        return $this->response->setJSON($json);
    }

    // 加工任务 所有加工领取列表
    public function get_list_all()
    {
        if (!$this->myservice->verifyRight('PROTASK_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/ProProcessingTask/get_list_all 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $page = max(intval($this->request->getGetPost('page')), 1);
        $rows = max(intval($this->request->getGetPost('rows')), 100);
        $sidx = $this->request->getGetPost('sidx');
        $sord = strtoupper((string)$this->request->getGetPost('sord')) === 'ASC' ? 'ASC' : 'DESC';
        $status1 = $this->request->getGetPost('status1');
        $status2 = intval($this->request->getGetPost('status2'));
        $skey = $this->request->getGetPost('skey');
        $beginDate = $this->request->getGetPost('beginDate');
        $endDate = $this->request->getGetPost('endDate');
        $builder = $this->db->table('pro_processing_task_get');
        $builder->where('isDelete', 0);
        if ($status1) {
            $builder->where('uName', $status1);
        }
        if (0 <= $status2) {
            $builder->where('doneStatus', $status2);
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
                    ->like('projectName', $this->db->escapeLikeString($kw))
                    ->orLike('description', $this->db->escapeLikeString($kw))
                    ->orWhere('uName', $kw)
                ->groupEnd();
            }
        }
        $allowedSort = ['id','iid','lastDate','projectId','projectType','projectName','needTest','taskQty','getQty','isTest','upQty','doneStatus','uId','uName','createTime','backInfo','transInfo','transHis','isGroupTrans'];
        $orderField  = $sidx ? (in_array($sidx, $allowedSort, true) ? $sidx : 'id') : 'id';
        $list = (clone $builder)->orderBy($orderField, $sord)->limit($rows)->offset($rows * ($page - 1))->get()->getResultArray();
        $totalQty = (clone $builder)->selectSum('getQty', 'totalGetQty')->selectSum('upQty', 'totalUpQty')->get()->getRowArray() ?? ['totalGetQty' => 0, 'totalUpQty' => 0];
        $total = $builder->countAllResults();
        $v = [];
        foreach ($list as $arr => $row) {
            $v[$arr]['id'] = intval($row['id']);
            $v[$arr]['iid'] = intval($row['iid']);
            $v[$arr]['lastDate'] = $row['lastDate'];
            $v[$arr]['projectId'] = intval($row['projectId']);
            $v[$arr]['projectType'] = intval($row['projectType']);
            $v[$arr]['projectName'] = $row['projectName'];
            $v[$arr]['needTest'] = intval($row['needTest']);
            $v[$arr]['taskQty'] = intval($row['taskQty']);
            $v[$arr]['getQty'] = intval($row['getQty']);
            $v[$arr]['isTest'] = intval($row['isTest']);
            $v[$arr]['upQty'] = intval($row['upQty']);
            $v[$arr]['doneStatus'] = intval($row['doneStatus']);
            $v[$arr]['uId'] = intval($row['uId']);
            $v[$arr]['uName'] = $row['uName'];
            $v[$arr]['createTime'] = $row['createTime'] == '0000-00-00 00:00:00' ? '' : $row['createTime'];
            $v[$arr]['backInfo'] = $row['backInfo'];
            $v[$arr]['transInfo'] = $row['transInfo'] ? (array)json_decode($row['transInfo'], TRUE) : '';
            $v[$arr]['transHis'] = $row['transHis'];
            $v[$arr]['isGroupTrans'] = intval($row['isGroupTrans']);
        }
        $json['status'] = 200;
        $json['msg'] = 'success';
        $json['data']['page'] = $page;
        $json['data']['records'] = $total;
        $json['data']['total'] = ceil($json['data']['records'] / $rows);
        $json['data']['rows'] = $v;
        $json['data']['totalQty'] = $totalQty;
        return $this->response->setJSON($json);
    }

    // 加工任务 所有加工交付列表
    public function up_list_all()
    {
        if (!$this->myservice->verifyRight('PROTASK_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/ProProcessingTask/up_list_all 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $page = max(intval($this->request->getGetPost('page')), 1);
        $rows = max(intval($this->request->getGetPost('rows')), 100);
        $sidx = $this->request->getGetPost('sidx');
        $sord = strtoupper((string)$this->request->getGetPost('sord')) === 'ASC' ? 'ASC' : 'DESC';
        $order = $sidx ? ($sidx . ' ' . $sord) : 'iid DESC,id DESC';
        $status1 = $this->request->getGetPost('status1');
        $status2 = intval($this->request->getGetPost('status2'));
        $skey = $this->request->getGetPost('skey');
        $beginDate = $this->request->getGetPost('beginDate');
        $endDate = $this->request->getGetPost('endDate');
        $builder = $this->db->table('pro_processing_task_up');
        $builder->where('isDelete', 0);
        if ($status1) {
            $builder->where('uName', $status1);
        }
        if (0 < $status2) {
            $builder->where('doneStatus', $status2);
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
                    ->like('projectName', $this->db->escapeLikeString($kw))
                    ->orLike('upDescription', $this->db->escapeLikeString($kw))
                    ->orWhere('uName', $kw)
                ->groupEnd();
            }
        }
        $allowedSort = ['id','iid','lastDate','projectId','projectType','projectName','needTest','taskQty','getQty','isTest','upQty','weight','price','amount','upDescription','upImgs','doneStatus','checkId','checkName','checkTime','uName','createTime'];
        $orderField  = $sidx ? (in_array($sidx, $allowedSort, true) ? $sidx : 'id') : 'id';
        $list = (clone $builder)->orderBy($orderField, $sord)->limit($rows)->offset($rows * ($page - 1))->get()->getResultArray();
        $totalQty = (clone $builder)->selectSum('upQty', 'totalQty')->get()->getRowArray()['totalQty'] ?? 0;
        $totalWeight = (clone $builder)->selectSum('weight', 'totalWeight')->get()->getRowArray()['totalWeight'] ?? 0;
        $total = $builder->countAllResults();
        $v = [];
        foreach ($list as $arr => $row) {
            $v[$arr]['id'] = intval($row['id']);
            $v[$arr]['iid'] = intval($row['iid']);
            $v[$arr]['lastDate'] = $row['lastDate'];
            $v[$arr]['projectId'] = intval($row['projectId']);
            $v[$arr]['projectType'] = intval($row['projectType']);
            $v[$arr]['projectName'] = $row['projectName'];
            $v[$arr]['needTest'] = intval($row['needTest']);
            $v[$arr]['taskQty'] = intval($row['taskQty']);
            $v[$arr]['getQty'] = intval($row['getQty']);
            $v[$arr]['isTest'] = intval($row['isTest']);
            $v[$arr]['upQty'] = intval($row['upQty']);
            $v[$arr]['weight'] = (float)$row['weight'];
            $v[$arr]['price'] = (float)$row['price'];
            $v[$arr]['amount'] = (float)$row['amount'];
            $v[$arr]['upDescription'] = $row['upDescription'];
            $v[$arr]['upImgs'] = $row['upImgs'] == '' ? [] : explode(',', $row['upImgs']);
            $v[$arr]['doneStatus'] = intval($row['doneStatus']);
            $v[$arr]['checkId'] = intval($row['checkId']);
            $v[$arr]['checkName'] = $row['checkName'];
            $v[$arr]['checkTime'] = $row['checkTime'] == '0000-00-00 00:00:00' ? '' : $row['checkTime'];
            $v[$arr]['uId'] = intval($row['uId']);
            $v[$arr]['uName'] = $row['uName'];
            $v[$arr]['createTime'] = $row['createTime'] == '0000-00-00 00:00:00' ? '' : $row['createTime'];
        }
        $json['status'] = 200;
        $json['msg'] = 'success';
        $json['data']['page'] = $page;
        $json['data']['records'] = $total;
        $json['data']['total'] = ceil($json['data']['records'] / $rows);
        $json['data']['rows'] = $v;
        $json['data']['totalQty'] = $totalQty;
        $json['data']['totalWeight'] = $totalWeight;
        return $this->response->setJSON($json);
    }
}
