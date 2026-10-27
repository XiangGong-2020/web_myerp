<?php

namespace App\Controllers\Base;
use App\Controllers\BaseController;

class PotentialCustomer extends BaseController
{
    // 列表
    public function list()
    {
        if (!$this->myservice->verifyRight('SERVICE_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/PotentialCustomer/list 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $page = max(intval($this->request->getGetPost('page')), 1);
        $rows = max(intval($this->request->getGetPost('rows')), 100);
        $sidx = $this->request->getGetPost('sidx');
        $sord = strtoupper((string)$this->request->getGetPost('sord')) === 'ASC' ? 'ASC' : 'DESC';
        $skey = $this->request->getGetPost('skey');
        $categoryid = intval($this->request->getGetPost('categoryId'));
        $disableType = intval($this->request->getGetPost('disableType'));
        $builder = $this->db->table('potential_customer');
        $builder->where('isDelete', 0);
        if ($categoryid > 0) {
            $builder->where('categoryId', $categoryid);
        }
        if ($disableType >= 0) {
            $builder->where('isDisable', $disableType);
        }
        if ($skey !== '' && $skey !== null) {
            $keywords = array_values(array_filter(explode(',', $skey), 'strlen'));
            foreach ($keywords as $kw) {
                $builder->groupStart()
                    ->like('name', $this->db->escapeLikeString($kw))
                    ->orLike('linkMans', $this->db->escapeLikeString($kw))
                    ->orLike('description', $this->db->escapeLikeString($kw))
                ->groupEnd();
            }
        }
        // 允许排序的字段白名单，杜绝 order by 注入
        $allowedSort = ['id','name','uName','createTime','isDisable'];
        $orderField  = $sidx ? (in_array($sidx, $allowedSort, true) ? $sidx : 'id') : 'id';
        $list = (clone $builder)->orderBy($orderField, $sord)->limit($rows)->offset($rows * ($page - 1))->get()->getResultArray();
        $total = $builder->countAllResults();
        $v = [];
        foreach ($list as $arr => $row) {
            $v[$arr]['id'] = intval($row['id']);
            $v[$arr]['name'] = $row['name'];
            $v[$arr]['categoryId'] = intval($row['categoryId']);
            $v[$arr]['links'] = '';
            $v[$arr]['linkMen'] = $row['linkMans'];
            $v[$arr]['description'] = $row['description'];
            $v[$arr]['uName'] = $row['uName'];
            $v[$arr]['createTime'] = $row['createTime'];
            $v[$arr]['isDisable'] = intval($row['isDisable']);
            if (strlen($row['linkMans']) > 0) {
                $list = (array)json_decode($row['linkMans'], true);
                foreach ($list as $arr1 => $row1) {
                    if ($row1['linkFirst'] == 1) {
                        $v[$arr]['contacter'] = $row1['linkName'] ?? '';
                        $v[$arr]['place'] = $row1['linkPlace'] ?? '';
                        $v[$arr]['wangwang'] = $row1['linkWangWang'] ?? '';
                        $v[$arr]['weixin'] = $row1['linkWeixin'] ?? '';
                        $v[$arr]['im'] = $row1['linkIm'] ?? '';
                        $v[$arr]['mobile'] = $row1['linkMobile'] ?? '';
                        $v[$arr]['phone'] = $row1['linkPhone'] ?? '';
                        $v[$arr]['province'] = $row1['province'] ?? '';
                        $v[$arr]['city'] = $row1['city'] ?? '';
                        $v[$arr]['county'] = $row1['county'] ?? '';
                        $v[$arr]['deliveryAddress'] = $row1['address'] ?? '';
                        $v[$arr]['firstLink']['first'] = $row1['linkFirst'] ?? '';
                    }
                }
            }
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
        if (!$this->myservice->verifyRight('SERVICE_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/PotentialCustomer/query 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $data = $this->db->table('potential_customer')->where(['id' => $id, 'isDelete' => 0])->get()->getRowArray();
        if ($data) {
            $info['id'] = $id;
            $info['categoryId'] = intval($data['categoryId']);
            $info['name'] = $data['name'];
            $info['description'] = $data['description'];
            if (strlen($data['linkMans']) > 0) {
                $list = (array)json_decode($data['linkMans'], true);
                foreach ($list as $arr => $row) {
                    $v[$arr]['id'] = $row['linkId'];
                    $v[$arr]['contacter'] = $row['linkName'] ?? '';
                    $v[$arr]['place'] = $row['linkPlace'] ?? '';
                    $v[$arr]['wangwang'] = $row['linkWangWang'] ?? '';
                    $v[$arr]['weixin'] = $row['linkWeixin'] ?? '';
                    $v[$arr]['im'] = $row['linkIm'] ?? '';
                    $v[$arr]['mobile'] = $row['linkMobile'] ?? '';
                    $v[$arr]['phone'] = $row['linkPhone'] ?? '';
                    $v[$arr]['province'] = $row['province'] ?? '';
                    $v[$arr]['city'] = $row['city'] ?? '';
                    $v[$arr]['county'] = $row['county'] ?? '';
                    $v[$arr]['address'] = $row['address'] ?? '';
                    $v[$arr]['first'] = $row['linkFirst'] == 1 ? true : '';
                }
            }
            $info['links'] = isset($v) ? array_values($v) : [];
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
        if (!$this->myservice->verifyRight('SERVICE_ADD')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/PotentialCustomer/add 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $data = $this->request->getPost();
        if (is_array($data) && count($data) > 0) {
            $data = $this->validform($data);
            // 如果返回的是 Response 对象，直接返回它
            if ($data instanceof \CodeIgniter\HTTP\Response) {
                return $data;
            }
            $info = array_intersect_key($data, array_flip(['name', 'categoryId', 'description', 'linkMans', 'uId', 'uName', 'createTime']));
            $this->db->transStart();
            $this->db->table('potential_customer')->insert($info);
            $data['id'] = $this->db->insertID();
            $data['categoryId'] = intval($data['categoryId']);
            $data['linkMans'] = (array)json_decode($data['linkMans'], true);
            $data['isDisable'] = 0;
            $this->myservice->log('新增了潜在客户，ID：' . $data['id'] . '，名称：' . $data['name']);
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
        if (!$this->myservice->verifyRight('SERVICE_UPDATE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/PotentialCustomer/update 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $data = $this->request->getPost();
        if (is_array($data) && count($data) > 0) {
            $data = $this->validform($data);
            // 如果返回的是 Response 对象，直接返回它
            if ($data instanceof \CodeIgniter\HTTP\Response) {
                return $data;
            }
            $info = array_intersect_key($data, array_flip(['name', 'categoryId', 'description', 'linkMans']));
            $this->db->transStart();
            $this->db->table('potential_customer')->where(['id' => $data['id']])->update($info);
            $data['categoryId'] = intval($data['categoryId']);
            $data['linkMans'] = (array)json_decode($data['linkMans'], true);
            $this->myservice->log('编辑了潜在客户，ID：' . $data['id'] . ',名称：' . $data['name']);
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
        if (!$this->myservice->verifyRight('SERVICE_DELETE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/PotentialCustomer/delete 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $data = $this->db->table('potential_customer')->where(['id' => $id])->get()->getRowArray();
        if ($data) {
            if ($data['uId'] != $this->myerp['uId']) {
                return $this->response->setJSON(['status' => -1, 'msg' => '不能删除别人录入的潜客登记！']);
            }
            $this->db->transStart();
            $this->db->table('potential_customer')->where(['id' => $id])->update(['isDelete' => 1]);
            $this->myservice->log('删除了潜在客户，ID：' . $id . '，名称：' . $data['name']);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '删除成功！']);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
    // 跟进状态变更
    public function disable()
    {
        if (!$this->myservice->verifyRight('SERVICE_UPDATE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/PotentialCustomer/disable 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $isDisable = intval($this->request->getGetPost('isDisable'));
        $data = $this->db->table('potential_customer')->where(['id' => $id])->get()->getRowArray();
        if ($data) {
            $this->db->transStart();
            $this->db->table('potential_customer')->where(['id' => $id])->update(['isDisable' => $isDisable]);
            $this->myservice->log($isDisable == 0 ? ('重置了ID为：' . $id . '，名称为：' . $data['name'] . ' 的潜在客户的跟进状态') : ($isDisable == 1 ? ('放弃了ID为：' . $id . '，名称为：' . $data['name'] . ' 的潜在客户的跟进') : ('转化了ID为：' . $id . '，名称为：' . $data['name'] . ' 的潜在客户为正式客户')));
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '操作成功！']);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
    // 检测是否重复
    public function checkRepeat()
    {
        $id = intval($this->request->getGetPost('id'));
        $linkMobile = $this->request->getGetPost('linkMobile');
        $builder = $this->db->table('potential_customer');
        $builder->where('isDelete', 0);
        if ($linkMobile) {
            $builder->where('JSON_SEARCH(linkMans, "one", ' . $this->db->escape($linkMobile) . ', NULL, "$[*].linkMobile") IS NOT NULL', null, false);
            if ($id > -1) {
                $builder->where('id !=', $id);
            }
            if ($builder->countAllResults() > 0) {
                return $this->response->setJSON(['status' => -1, 'msg' => '已经存在']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => 'success']);
        }
        return $this->response->setJSON(['status' => 200, 'msg' => 'success']);
    }
    // 数据验证
    private function validform(array $data)
    {
        $data['id'] = isset($data['id']) ? intval($data['id']) : -1;
        if ($data['categoryId'] < 1) {
            return $this->response->setJSON(['status' => -1, 'msg' => '类别不能为空！']);
        }
        if (strlen($data['name']) < 1) {
            return $this->response->setJSON(['status' => -1, 'msg' => '名称不能为空！']);
        }
        $data['categoryId'] = intval($data['categoryId']);
        $data['linkMans'] = strlen($data['linkMans']) >= 10 ? $data['linkMans'] : '';
        $data['uId'] = $this->myerp['uId'];
        $data['uName'] = $this->myerp['uName'];
        $data['createTime'] = date('Y-m-d H:i:s');
        return $data;
    }
}
