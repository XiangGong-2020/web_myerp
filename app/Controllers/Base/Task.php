<?php

namespace App\Controllers\Base;
use App\Controllers\BaseController;

class Task extends BaseController
{
    // 列表
    public function list()
    {
        if (!$this->myservice->verifyRight('TASK_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Base/Task/list 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $page = max(intval($this->request->getGetPost('page')), 1);
        $rows = max(intval($this->request->getGetPost('rows')), 100);
        $sidx = $this->request->getGetPost('sidx');
        $sord = strtoupper((string)$this->request->getGetPost('sord')) === 'ASC' ? 'ASC' : 'DESC';
        $status1 = intval($this->request->getGetPost('status1'));
        $status2 = $this->request->getGetPost('status2');
        $status3 = $this->request->getGetPost('status3');
        $status4 = $this->request->getGetPost('status4');
        $status5 = intval($this->request->getGetPost('status5'));
        $status6 = intval($this->request->getGetPost('status6'));
        $beginDate = $this->request->getGetPost('beginDate');
        $endDate = $this->request->getGetPost('endDate');
        $skey = $this->request->getGetPost('skey');
        $unDone = intval($this->request->getGetPost('unDone'));
        $builder = $this->db->table('task');
        $builder->where('isDelete', 0);
        if ($status1 > -1) {
            $builder->where('type', $status1);
        }
        if ($this->myservice->verifyRight('SYS_ADVANCE')) {
            if ($status2) {
                $builder->where('uName', $status2);
            }
            if ($status3) {
                $builder->where('leader', $status3);
            }
            if ($status4) {
                $builder->where('FIND_IN_SET(' . $this->db->escape($status4) . ', other) >', 0, false);
            }
        } else {
            $builder->groupStart()
                ->where('leader', $this->myerp['uName'])
                ->orWhere('FIND_IN_SET(' . $this->db->escape($this->myerp['uName']) . ', other) >', 0, false)
            ->groupEnd();
        }
        if ($status5 == 0) {
            $builder->where('doneTime', '0000-00-00 00:00:00');
        } elseif ($status5 == 1) {
            $builder->where('doneTime >', '0000-00-00 00:00:00');
        }
        if ($status6 == 0) {
            $builder->groupStart()
                ->groupStart()
                    ->where('doneTime', '0000-00-00 00:00:00')
                    ->where('limitTime >=', date('Y-m-d H:i:s'))
                ->groupEnd()
                ->orGroupStart()
                    ->where('doneTime >', '0000-00-00 00:00:00')
                    ->where('doneTime <= limitTime', null, false)
                ->groupEnd()
            ->groupEnd();
        } elseif ($status6 == 1) {
            $builder->groupStart()
                ->groupStart()
                    ->where('doneTime', '0000-00-00 00:00:00')
                    ->where('limitTime <', date('Y-m-d H:i:s'))
                ->groupEnd()
                ->orGroupStart()
                    ->where('doneTime >', '0000-00-00 00:00:00')
                    ->where('doneTime > limitTime', null, false)
                ->groupEnd()
            ->groupEnd();
        }
        if ($beginDate) {
            $builder->where('createTime >=', $beginDate . ' 00:00:00');
        }
        if ($endDate) {
            $builder->where('createTime <=', $endDate . ' 23:59:59');
        }
        if ($unDone > 0) {
            $builder->where('status', 0);
        }
        if ($skey !== '' && $skey !== null) {
            $keywords = array_values(array_filter(explode(',', $skey), 'strlen'));
            foreach ($keywords as $kw) {
                $builder->groupStart()
                    ->like('title', $this->db->escapeLikeString($kw))
                    ->orLike('content', $this->db->escapeLikeString($kw))
                    ->orLike('files', $this->db->escapeLikeString($kw))
                    ->orWhere('uName', $kw)
                    ->orWhere('leader', $kw)
                    ->orWhere('FIND_IN_SET(' . $this->db->escape($kw) . ', other) >', 0, false)
                ->groupEnd();
            }
        }
        $allowedSort = ['id','type','title','limitTime','leader','other','status','uName','createTime','doneTime'];
        $orderField  = $sidx ? (in_array($sidx, $allowedSort, true) ? $sidx : 'id') : 'id';
        $list = (clone $builder)->orderBy($orderField, $sord)->limit($rows)->offset($rows * ($page - 1))->get()->getResultArray();
        $total = $builder->countAllResults();
        $v = [];
        foreach ($list as $arr => $row) {
            $v[$arr]['id'] = intval($row['id']);
            $v[$arr]['type'] = intval($row['type']);
            $v[$arr]['title'] = $row['title'];
            $v[$arr]['limitTime'] = $row['limitTime'];
            $v[$arr]['leader'] = $row['leader'];
            $v[$arr]['other'] = $row['other'];
            $v[$arr]['status'] = intval($row['status']);
            $v[$arr]['uName'] = $row['uName'];
            $v[$arr]['createTime'] = $row['createTime'];
            $v[$arr]['doneTime'] = $row['doneTime'];
            if ($row['doneTime'] != '0000-00-00 00:00:00') {
                if ($row['doneTime'] > $row['limitTime']) {
                    $timelineStatus = '<span class="db h30 lh30 tc white bg-black b-r-3">超时</span>';
                } else {
                    $timelineStatus = '<span class="db h30 lh30 tc white bg-green b-r-3">正常</span>';
                }
            } else {
                if (date('Y-m-d H:i:s') > $row['limitTime']) {
                    $timelineStatus = '<span class="db h30 lh30 tc white bg-black b-r-3">超时</span>';
                } else {
                    $timelineStatus = '<span class="db h30 lh30 tc white bg-green b-r-3">正常</span>';
                }
            }
            $v[$arr]['timelineStatus'] = $timelineStatus;
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
        if (!$this->myservice->verifyRight('TASK_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Base/Task/query 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $data = $this->db->table('task')->where(['id' => $id, 'isDelete' => 0])->get()->getRowArray();
        if ($data) {
            $info['id'] = $id;
            $info['type'] = intval($data['type']);
            $info['title'] = $data['title'];
            $info['content'] = $data['content'];
            $info['limitTime'] = $data['limitTime'];
            $info['imgs'] = $data['imgs'] == '' ? [] : json_decode($data['imgs'], true);
            $info['files'] = $data['files'] == '' ? [] : json_decode($data['files'], true);
            $info['leader'] = $data['leader'];
            $info['other'] = $data['other'];
            $info['status'] = intval($data['status']);
            $info['execute'] = $data['execute'];
            $info['uName'] = $data['uName'];
            if (intval(microtime(true)) > (strtotime($data['createTime']) + 3600)) {
                $info['goneHour'] = 1;
            } else {
                $info['goneHour'] = 0;
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
        if (!$this->myservice->verifyRight('TASK_ADD')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Base/Task/add 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $data = $this->request->getGetPost();
        if (is_array($data) && count($data) > 0) {
            $data = $this->validform($data);
            // 如果返回的是 Response 对象，直接返回它
            if ($data instanceof \CodeIgniter\HTTP\Response) {
                return $data;
            }
            $info = array_intersect_key($data, array_flip(['title', 'limitTime', 'type', 'content', 'imgs', 'files', 'leader', 'other', 'status', 'execute', 'uName', 'createTime', 'doneTime']));
            $this->db->transStart();
            $this->db->table('task')->insert($info);
            $data['id'] = $this->db->insertID();
            if ($data['doneTime'] != '0000-00-00 00:00:00') {
                if ($data['doneTime'] > $data['limitTime']) {
                    $timelineStatus = '<span class="db h30 lh30 tc white bg-black b-r-3">超时</span>';
                } else {
                    $timelineStatus = '<span class="db h30 lh30 tc white bg-green b-r-3">正常</span>';
                }
            } else {
                if (date('Y-m-d H:i:s') > $data['limitTime']) {
                    $timelineStatus = '<span class="db h30 lh30 tc white bg-black b-r-3">超时</span>';
                } else {
                    $timelineStatus = '<span class="db h30 lh30 tc white bg-green b-r-3">正常</span>';
                }
            }
            $data['timelineStatus'] = $timelineStatus;
            $this->myservice->log('新增了任务清单：' . $data['title']);
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
        if (!$this->myservice->verifyRight('TASK_UPDATE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Base/Task/update 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $data = $this->request->getGetPost();
        if (is_array($data) && count($data) > 0) {
            $data = $this->validform($data);
            // 如果返回的是 Response 对象，直接返回它
            if ($data instanceof \CodeIgniter\HTTP\Response) {
                return $data;
            }
            $info = array_intersect_key($data, array_flip(['title', 'limitTime', 'content', 'imgs', 'files', 'leader', 'other', 'status', 'doneTime']));
            $this->db->transStart();
            $this->db->table('task')->where('id', $data['id'])->update($info);
            $data = $this->db->table('task')->where('id', $data['id'])->get()->getRowArray();
            if ($data['doneTime'] != '0000-00-00 00:00:00') {
                if ($data['doneTime'] > $data['limitTime']) {
                    $timelineStatus = '<span class="db h30 lh30 tc white bg-black b-r-3">超时</span>';
                } else {
                    $timelineStatus = '<span class="db h30 lh30 tc white bg-green b-r-3">正常</span>';
                }
            } else {
                if (date('Y-m-d H:i:s') > $data['limitTime']) {
                    $timelineStatus = '<span class="db h30 lh30 tc white bg-black b-r-3">超时</span>';
                } else {
                    $timelineStatus = '<span class="db h30 lh30 tc white bg-green b-r-3">正常</span>';
                }
            }
            $data['timelineStatus'] = $timelineStatus;
            $this->myservice->log('编辑了任务清单：' . $data['title']);
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
        if (!$this->myservice->verifyRight('TASK_DELETE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Base/Task/delete 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $data = $this->db->table('task')->where('id', $id)->get()->getRowArray();
        if ($data) {
            if ($data['uName'] != $this->myerp['uName']) {
                return $this->response->setJSON(['status' => -1, 'msg' => '不能删除别人发起的任务清单！']);
            }
            $this->db->transStart();
            $this->db->table('task')->where('id', $id)->update(['isDelete' => 1]);
            $this->myservice->log('删除了任务清单，ID：' . $id . '，标题：' . $data['title']);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '删除成功！']);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
    // 追加任务进展信息
    public function addExecuteon()
    {
        $id = intval($this->request->getGetPost('id'));
        $execute = $this->request->getGetPost('execute');
        $data = $this->db->table('task')->where('id', $id)->get()->getRowArray();
        if ($data && $execute) {
            $new_execute = $data['execute'] ? ($data['execute'] . "\r\n" . date('Y-m-d H:i:s') . ' ' . $this->myerp['uName'] . '：' . $execute) : (date('Y-m-d H:i:s') . ' ' . $this->myerp['uName'] . '：' . $execute);
            $this->db->transStart();
            $this->db->table('task')->where('id', $id)->update(['execute' => $new_execute]);
            $data['execute'] = $new_execute;
            $this->myservice->log('追加了任务进展，ID：' . $id . '，标题：' . $data['title'] . '，追加进展内容：' . $execute);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '提交成功', 'data' => $data]);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
    // 上传图片
    public function uploadImg()
    {
        if (!$this->myservice->verifyRight('TASK_ADD')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Base/Task/uploadImg 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        set_time_limit(0);
        session_write_close();
        if (empty($_FILES)){
            return $this->response->setJSON(['status' => -1, 'msg' => '没有上传文件！']);
        };
        $uppath = 'data/upfile/task/';
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
        if (!$this->myservice->verifyRight('TASK_ADD')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Base/Task/uploadfile 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        set_time_limit(0);
        session_write_close();
        if (empty($_FILES)){
            return $this->response->setJSON(['status' => -1, 'msg' => '没有上传文件！']);
        };
        $uppath = 'data/upfile/task/';
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
            return $this->response->setJSON(['status' => -1, 'msg' => '任务目标不能为空！']);
        }
        if (strlen($data['limitTime']) < 1) {
            return $this->response->setJSON(['status' => -1, 'msg' => '完成限期不能为空！']);
        }
        if (strlen($data['leader']) < 2) {
            return $this->response->setJSON(['status' => -1, 'msg' => '任务主导人不能为空！']);
        }
        $data['content'] = $data['content'] ?? '';
        $data['other'] = $data['other'] ?? '';
        if ($data['leader'] && $data['other'] && strpos($data['other'], $data['leader']) !== false) {
            return $this->response->setJSON(['status' => -1, 'msg' => '任务主导人与其他参与人不能重复！']);
        }
        $data['type'] = $data['type'] ?? 0;
        $data['status'] = $data['status'] ?? 0;
        $data['imgs'] = strlen($data['imgs']) < 10 ? '' : $data['imgs'];
        $data['files'] = strlen($data['files']) < 10 ? '' : $data['files'];
        $data['uName'] = $this->myerp['uName'];
        $data['createTime'] = date("Y-m-d H:i:s");
        if ($data['id'] > 0) {
            $oldData = $this->db->table('task')->where('id', $data['id'])->get()->getRowArray();
            if ($oldData['status'] == 0 && $data['status'] == 1) {
                $data['doneTime'] = date("Y-m-d H:i:s");
            } elseif ($oldData['status'] == 1 && $data['status'] == 0) {
                $data['doneTime'] = '0000-00-00 00:00:00';
            } elseif ($oldData['status'] == 1 && $data['status'] == 1 && $oldData['doneTime'] == '0000-00-00 00:00:00') {
                $data['doneTime'] = date("Y-m-d H:i:s");
            } elseif ($oldData['status'] == 0 && $data['status'] == 0 && $oldData['doneTime'] != '0000-00-00 00:00:00') {
                $data['doneTime'] = '0000-00-00 00:00:00';
            } else {
                $data['doneTime'] = $oldData['doneTime'];
            }
        } else {
            $data['doneTime'] = $data['status'] == 1 ? date("Y-m-d H:i:s") : '0000-00-00 00:00:00';
        }
        return $data;
    }
}
