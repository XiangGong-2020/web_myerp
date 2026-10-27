<?php

namespace App\Controllers\Base;
use App\Controllers\BaseController;

class News extends BaseController
{
    // 列表
    public function list()
    {
        if (!$this->myservice->verifyRight('NEWS_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/News/list 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $page = max(intval($this->request->getGetPost('page')), 1);
        $rows = max(intval($this->request->getGetPost('rows')), 10);
        $sidx = $this->request->getGetPost('sidx');
        $sord = $this->request->getGetPost('sord');
        $type = intval($this->request->getGetPost('type'));
        $skey = $this->request->getGetPost('skey');
        $beginDate = $this->request->getGetPost('beginDate');
        $endDate = $this->request->getGetPost('endDate');
        $builder = $this->db->table('news');
        if ($type > 0) {
            $builder->where('type', $type);
        }
        if ($beginDate) {
            $builder->where('beginDate >=', $beginDate);
        }
        if ($endDate) {
            $builder->where('beginDate <=', $endDate);
        }
        if (!in_array('SYS_ADMIN', $this->myerp['rights'])) {
            if($type == 2) {
                if ($this->myerp['user']['allowVideoTypeIds']) {
                    $builder->whereIn('videotype', explode(',', $this->myerp['user']['allowVideoTypeIds']));
                } else {
                    $builder->where('videotype', 0);
                }
            }
            $builder->groupStart()
                ->where('department', '')
                ->orWhere('FIND_IN_SET(' . $this->db->escape($this->myerp['user']['department']) . ', department) >', 0, false)
            ->groupEnd();
        }
        if ($skey !== '' && $skey !== null) {
            $keywords = array_values(array_filter(explode(',', $skey), 'strlen'));
            foreach ($keywords as $kw) {
                $builder->groupStart()
                    ->like('title', $this->db->escapeLikeString($kw))
                    ->orLike('content', $this->db->escapeLikeString($kw))
                ->groupEnd();
            }
        }
        $builder->where('isDelete', 0);
        $allowedSort = ['id','type','title','content','beginName','beginDate'];
        $orderField  = $sidx ? (in_array($sidx, $allowedSort, true) ? $sidx : 'id') : 'id';
        $list = (clone $builder)->orderBy($orderField, $sord)->limit($rows)->offset($rows * ($page - 1))->get()->getResultArray();
        $total = $builder->countAllResults();
        $v = [];
        foreach ($list as $arr => $row) {
            $v[$arr]['id'] = intval($row['id']);
            $v[$arr]['type'] = $row['type'];
            $v[$arr]['title'] = $row['title'];
            $v[$arr]['content'] = $row['content'];
            $v[$arr]['beginName'] = $row['beginName'];
            $v[$arr]['beginDate'] = $row['beginDate'];
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
        if (!$this->myservice->verifyRight('NEWS_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/News/query 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $data = $this->db->table('news')->where(['id' => $id, 'isDelete' => 0])->get()->getRowArray();
        if ($data) {
            $info['id'] = $id;
            $info['type'] = intval($data['type']);
            $info['videotype'] = intval($data['videotype']);
            $info['department'] = $data['department'];
            $info['title'] = $data['title'];
            $info['content'] = $data['content'];
            $info['beginName'] = $data['beginName'];
            $info['beginDate'] = $data['beginDate'];
            if ($data['type'] == 2 && $data['videotype'] > 0 && !in_array('SYS_ADMIN', $this->myerp['rights'])) {
                if (!in_array($data['videotype'], explode(',', $this->myerp['user']['allowVideoTypeIds']))) {
                    return $this->response->setJSON(['status' => -1, 'msg' => '无权观看本视频！']);
                }
            }
            $json['status'] = 200;
            $json['msg'] = 'success';
            $json['data'] = $info;
            return $this->response->setJSON($json);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
    // 获取观看信息
    public function getWatchinfo()
    {
        if (!$this->myservice->verifyRight('NEWS_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/News/getWatchinfo 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $data = $this->db->table('news')->select('watchinfo')->where(['id' => $id, 'isDelete' => 0])->get()->getRowArray();
        if ($data) {
            $json['status'] = 200;
            $json['msg'] = 'success';
            $json['data'] = (array)json_decode($data['watchinfo'], true);
            return $this->response->setJSON($json);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！', 'data' => []]);
    }
    // 更新观看信息
    public function updateWatchinfo()
    {
        if (!$this->myservice->verifyRight('NEWS_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/News/updateWatchinfo 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $watchinfo = $this->request->getGetPost('watchinfo');
        if ($id > 0) {
            $this->db->transStart();
            $this->db->table('news')->where('id', $id)->update(['watchinfo' => json_encode($watchinfo, 320)]);
            $this->db->table('user')->set('studyPower', 'studyPower + 10', false)->where('id', $this->myerp['uId'])->update();
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '更新成功！']);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '提交的是空数据！']);
    }
    // 新增
    public function add()
    {
        if (!$this->myservice->verifyRight('NEWS_ADD')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/News/add 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $data = $this->request->getPost();
        if (is_array($data) && count($data) > 0) {
            $data = $this->validform($data);
            // 如果返回的是 Response 对象，直接返回它
            if ($data instanceof \CodeIgniter\HTTP\Response) {
                return $data;
            }
            $data['beginDate'] = date('Y-m-d H:i:s');
            $success = (1 == $data['type'] ? '发布业务知识' : (2 == $data['type'] ? '发布学习视频' : (3 == $data['type'] ? '发布通知公告' : '发布制度文件')));
            $info = array_intersect_key($data, array_flip(['type', 'videotype', 'department', 'title', 'content', 'beginDate', 'beginName']));
            $this->db->transStart();
            $this->db->table('news')->insert($info);
            $data['id'] = $this->db->insertID();
            $this->myservice->log($success . '，标题：' . $data['title']);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '新增成功！', 'data' => $data]);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '提交的是空数据！']);
    }
    //编辑
    public function update()
    {
        if (!$this->myservice->verifyRight('NEWS_UPDATE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/News/update 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $data = $this->request->getPost();
        if (is_array($data) && count($data) > 0) {
            $data = $this->validform($data);
            // 如果返回的是 Response 对象，直接返回它
            if ($data instanceof \CodeIgniter\HTTP\Response) {
                return $data;
            }
            $success = '编辑了' . (1 == $data['type'] ? '业务知识' : (2 == $data['type'] ? '学习视频' : (3 == $data['type'] ? '通知公告' : '制度文件')));
            $info = array_intersect_key($data, array_flip(['type', 'videotype', 'department', 'title', 'content']));
            $this->db->transStart();
            $this->db->table('news')->where('id', $data['id'])->update($info);
            $this->myservice->log($success . '，标题：' . $data['title']);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '更新成功！', 'data' => $data]);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '提交的是空数据！']);
    }
    //删除
    public function delete()
    {
        if (!$this->myservice->verifyRight('NEWS_DELETE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/News/delete 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getPost('id'));
        $data = $this->db->table('news')->where('id', $id)->get()->getRowArray();
        if ($data) {
            $success = '删除了' . (1 == $data['type'] ? '业务知识' : (2 == $data['type'] ? '学习视频' : (3 == $data['type'] ? '通知公告' : '制度文件')));
            $this->db->transStart();
            $this->db->table('news')->where('id', $id)->update(['isDelete' => 1]);
            $this->myservice->log($success . '，标题：' . $data['title']);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '删除成功！', 'data' => ['id' => $id]]);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
    //数据验证
    private function validform(array $data)
    {
        if (strlen($data['title']) < 1) {
            return $this->response->setJSON(['status' => -1, 'msg' => '标题不能为空！']);
        }
        if (strlen($data['content']) < 1) {
            return $this->response->setJSON(['status' => -1, 'msg' => '内容不能为空！']);
        }
        $data['content'] = str_replace(['http://erp.56dz.web', 'http://192.168.0.222', 'https://erp.56dz.com'], '', $data['content']);
        return $data;
    }
}
