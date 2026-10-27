<?php

namespace App\Controllers\Base;
use App\Controllers\BaseController;

class Service extends BaseController
{
    // 列表
    public function list()
    {
        if (!$this->myservice->verifyRight('SERVICE_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/Service/list 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $page = max(intval($this->request->getGetPost('page')), 1);
        $rows = max(intval($this->request->getGetPost('rows')), 100);
        $sidx = $this->request->getGetPost('sidx');
        $sord = strtoupper((string)$this->request->getGetPost('sord')) === 'ASC' ? 'ASC' : 'DESC';
        $categoryId = intval($this->request->getGetPost('categoryId'));
        $isDisable = intval($this->request->getGetPost('isDisable'));
        $targetName = $this->request->getGetPost('targetName');
        $beginName = $this->request->getGetPost('beginName');
        $skey = $this->request->getGetPost('skey');
        $beginDate = $this->request->getGetPost('beginDate');
        $endDate = $this->request->getGetPost('endDate');

        $builder = $this->db->table('service');
        $builder->where('isDelete', 0);
        if ($categoryId > 0) {
            $builder->where('categoryId', $categoryId);
        }
        if ($isDisable >= 0) {
            $builder->where('isDisable', $isDisable);
        }
        if ($beginName) {
            $builder->where('beginName', $beginName);
        }
        if ($beginDate) {
            $builder->where('beginDate >=', $beginDate);
        }
        if ($endDate) {
            $builder->where('beginDate <=', $endDate);
        }
        if ($targetName !== '' && $targetName !== null) {
            $keywords = array_values(array_filter(explode(',', $targetName), 'strlen'));
            foreach ($keywords as $kw) {
                $builder->where('FIND_IN_SET(' . $this->db->escape($kw) . ', targetName) >', 0, false);
            }
        }
        if ($skey !== '' && $skey !== null) {
            $keywords = array_values(array_filter(explode(',', $skey), 'strlen'));
            foreach ($keywords as $kw) {
                $builder->groupStart()
                    ->like('content', $this->db->escapeLikeString($kw))
                    ->orLike('beginName', $this->db->escapeLikeString($kw))
                    ->orWhere('endName', $kw)
                    ->orWhere('FIND_IN_SET(' . $this->db->escape($kw) . ', targetName) >', 0, false)
                    ->orLike('orderNo', $this->db->escapeLikeString($kw))
                    ->orLike('deliverNo', $this->db->escapeLikeString($kw))
                    ->orLike('qq', $this->db->escapeLikeString($kw))
                    ->orLike('ww', $this->db->escapeLikeString($kw))
                    ->orLike('tel', $this->db->escapeLikeString($kw))
                ->groupEnd();
            }
        }
        $allowedSort = ['id','name','company','description','isDisable'];
        $orderField  = $sidx ? (in_array($sidx, $allowedSort, true) ? $sidx : 'id') : 'id';
        $list = (clone $builder)->orderBy($orderField, $sord)->limit($rows)->offset($rows * ($page - 1))->get()->getResultArray();
        $total = $builder->countAllResults();
        $v = [];
        foreach ($list as $arr => $row) {
            $v[$arr]['id'] = intval($row['id']);
            $v[$arr]['categoryId'] = intval($row['categoryId']);
            $v[$arr]['orderId'] = intval($row['orderId']);
            $v[$arr]['orderNo'] = $row['orderNo'];
            $v[$arr]['deliverId'] = intval($row['deliverId']);
            $v[$arr]['deliverNo'] = $row['deliverNo'];
            $v[$arr]['ww'] = $row['ww'];
            $v[$arr]['tel'] = $row['tel'];
            $v[$arr]['qq'] = $row['qq'];
            $v[$arr]['customerinfo'] = (('' != $row['ww'] && NULL != $row['ww']) ? '旺旺：' . $row['ww'] : '') . (('' != $row['qq'] && NULL != $row['qq']) ? ' QQ：' . $row['qq'] : '') . (('' != $row['tel'] && NULL != $row['tel']) ? ' 电话：' . $row['tel'] : '');
            $v[$arr]['content'] = strip_tags(str_replace('</i></div>', "\r\n", str_replace('<div class="info-username">', '（', str_replace('</div><div class="info-time">', ' ', str_replace('</div><i class="close iconfont icon-shanchu">', '）', $row['content'])))));
            $v[$arr]['targetName'] = $row['targetName'];
            $v[$arr]['targetAmount'] = (float)abs($row['targetAmount']);
            $v[$arr]['beginName'] = $row['beginName'];
            $v[$arr]['beginDate'] = $row['beginDate'];
            $v[$arr]['endName'] = $row['endName'];
            $v[$arr]['endDate'] = $row['endDate'];
            $v[$arr]['isDisable'] = intval($row['isDisable']);
            $v[$arr]['disStatus'] = intval($row['disStatus']);
            $v[$arr]['disReason'] = $row['disReason'];
        }
        $json['status'] = 200;
        $json['msg'] = 'success';
        $json['data']['page'] = $page;
        $json['data']['records'] = $total;
        $json['data']['total'] = ceil($json['data']['records'] / $rows);
        $json['data']['rows'] = $v;
        return $this->response->setJSON($json);
    }
    //获取信息
    public function query()
    {
        if (!$this->myservice->verifyRight('SERVICE_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/Service/query 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $data = $this->db->table('service')->where(['id' => $id, 'isDelete' => 0])->get()->getRowArray();
        if ($data) {
            $info['id'] = $id;
            $info['categoryId'] = intval($data['categoryId']);
            $info['orderNo'] = $data['orderNo'];
            $info['deliverNo'] = $data['deliverNo'];
            $info['ww'] = $data['ww'];
            $info['tel'] = $data['tel'];
            $info['qq'] = $data['qq'];
            $info['content'] = $data['content'];
            $info['targetName'] = $data['targetName'];
            $info['targetAmount'] = (float)abs($data['targetAmount']);
            $info['beginName'] = $data['beginName'];
            $info['beginDate'] = $data['beginDate'];

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
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/Service/add 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $data = $this->request->getPost();
        if (is_array($data) && count($data) > 0) {
            $data = $this->validform($data);
            // 如果返回的是 Response 对象，直接返回它
            if ($data instanceof \CodeIgniter\HTTP\Response) {
                return $data;
            }
            $this->db->transStart();
            $data['beginName'] = $this->myerp['uName'];
            $data['beginDate'] = date('Y-m-d H:i:s');
            if (strlen($data['deliverNo']) > 0) {
                $description = strip_tags(str_replace('<i class="close iconfont icon-shanchu"></i>',' ',str_replace('<div class="info-time">', ' ', str_replace('<div class="info-username">',' ', $data['content']))));
                $targetAmount = 0;
                if(strstr($data['targetName'],'百世') || strstr($data['targetName'],'邮政') || strstr($data['targetName'],'顺丰') || strstr($data['targetName'],'圆通') || strstr($data['targetName'],'中通') || strstr($data['targetName'],'申通') || strstr($data['targetName'],'韵达') || strstr($data['targetName'],'德邦')) {
                    $targetAmount = $data['targetAmount'];
                }
                $this->db->table('deliver_bag')->where(['no' => $data['deliverNo']])->update(['description' => $description, 'targetAmount' => $targetAmount]);
            }
            $info = array_intersect_key($data, array_flip(['categoryId', 'orderId', 'orderNo', 'deliverId', 'deliverNo', 'qq', 'ww', 'tel', 'content', 'targetName', 'targetAmount', 'beginName', 'beginDate']));
            $this->db->table('service')->insert($info);
            $data['id'] = $this->db->insertID();
            $data['categoryId'] = intval($data['categoryId']);
            $data['isDisable'] = 0;
            $data['content'] = strip_tags(str_replace('</i></div>', "\r\n", str_replace('<div class="info-username">', '（', str_replace('</div><div class="info-time">', ' ', str_replace('</div><i class="close iconfont icon-shanchu">', '）', $data['content'])))));
            $this->myservice->log('新增了客户之声，编号：' . $data['id'] . ($data['orderNo'] ? ('；订单号：' . $data['orderNo']) : ''));
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
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/Service/update 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $data = $this->request->getPost();
        if (is_array($data) && count($data) > 0) {
            $oldData = $this->db->table('service')->where(['id' => $data['id']])->get()->getRowArray();
            if ($oldData) {
                if (!$this->myservice->verifyRight('SYS_ADMIN') && $oldData['beginName'] != $this->myerp['uName']) {
                    return $this->response->setJSON(['status' => -1, 'msg' => '不能编辑别人录入的客户之声！']);
                }
            }
            $data = $this->validform($data);
            // 如果返回的是 Response 对象，直接返回它
            if ($data instanceof \CodeIgniter\HTTP\Response) {
                return $data;
            }
            $this->db->transStart();
            if (strlen($data['deliverNo']) > 0) {
                $description = strip_tags(str_replace('<i class="close iconfont icon-shanchu"></i>',' ',str_replace('<div class="info-time">', ' ', str_replace('<div class="info-username">',' ', $data['content']))));
                $targetAmount = 0;
                if(strstr($data['targetName'],'百世') || strstr($data['targetName'],'邮政') || strstr($data['targetName'],'顺丰') || strstr($data['targetName'],'圆通') || strstr($data['targetName'],'中通') || strstr($data['targetName'],'申通') || strstr($data['targetName'],'韵达') || strstr($data['targetName'],'德邦')) {
                    $targetAmount = $data['targetAmount'];
                }
                $this->db->table('deliver_bag')->where(['no' => $data['deliverNo']])->update(['description' => $description, 'targetAmount' => $targetAmount]);
            }
            $info = array_intersect_key($data, array_flip(['categoryId', 'orderId', 'orderNo', 'deliverId', 'deliverNo', 'qq', 'ww', 'tel', 'content', 'targetName', 'targetAmount']));
            $this->db->table('service')->where(['id' => $data['id']])->update($info);
            $data = $this->db->table('service')->where(['id' => $data['id']])->get()->getRowArray();
            $data['categoryId'] = intval($data['categoryId']);
            $data['content'] = strip_tags(str_replace('</i></div>', "\r\n", str_replace('<div class="info-username">', '（', str_replace('</div><div class="info-time">', ' ', str_replace('</div><i class="close iconfont icon-shanchu">', '）', $data['content'])))));
            $this->myservice->log('编辑了客户之声， 编号：' . $data['id'] . ($data['orderNo'] ? ('；订单号：' . $data['orderNo']) : ''));
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
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/Service/delete 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $data = $this->db->table('service')->where(['id' => $id])->get()->getRowArray();
        if ($data) {
            if ($data['beginName'] != $this->myerp['uName']) {
                return $this->response->setJSON(['status' => -1, 'msg' => '不能删除别人录入的客户之声！']);
            }
            $this->db->transStart();
            $this->db->table('service')->where(['id' => $id])->update(['isDelete' => 1]);
            if ($data['deliverNo']) {
                $this->db->table('deliver_bag')->where(['no' => $data['deliverNo']])->update(['targetAmount' => 0, 'description' => ""]);
            }
            $this->myservice->log('删除了客户之声，编号：' . $id . ($data['orderNo'] ? ('；订单号：' . $data['orderNo']) : ''));
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '删除成功！']);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
    // 重新推送
    public function reSend()
    {
        if (!$this->myservice->verifyRight('SERVICE_UPDATE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/Service/reSend 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $data = $this->db->table('service')->where(['id' => $id])->get()->getRowArray();
        if ($data) {
            if ($data['beginName'] != $this->myerp['uName'] && !in_array('SYS_ADMIN', $this->myerp['rights'])) {
                return $this->response->setJSON(['status' => -1, 'msg' => '不能操作别人录入的客户之声！']);
            }
            $this->db->transStart();
            $this->db->table('service')->where(['id' => $id])->update(['disStatus' => 2, 'beginDate' => date('Y-m-d H:i:s')]);
            $this->myservice->log('重新推送了损失金额给责任人，客户之声编号：' . $id . '，损失金额：' . $data['targetAmount']);
            $data['disStatus'] = 2;
            $data['beginDate'] = date('Y-m-d H:i:s');
            $data['content'] = strip_tags(str_replace('</i></div>', "\r\n", str_replace('<div class="info-username">', '（', str_replace('</div><div class="info-time">', ' ', str_replace('</div><i class="close iconfont icon-shanchu">', '）', $data['content'])))));
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '推送成功！']);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
    // 解决状态
    public function disable()
    {
        if (!$this->myservice->verifyRight('SERVICE_UPDATE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/Service/disable 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $isDisable = intval($this->request->getGetPost('isDisable'));
        $endName = $this->myerp['uName'];
        $endDate = date('Y-m-d H:i:s');
        $data = $this->db->table('service')->where(['id' => $id])->get()->getRowArray();
        if ($data) {
            if ($data['beginName'] != $this->myerp['uName'] && strpos($data['targetName'], $this->myerp['uName']) === false && !in_array('SYS_ADVANCE', $this->myerp['rights'])) {
                return $this->response->setJSON(['status' => -1, 'msg' => '既不是录入人，也不是责任人，也不是高级用户，无权改变解决状态！']);
            }
            if ($data['disStatus'] > 0 && $data['disStatus'] < 3 && $data['targetAmount'] > 0) {
                return $this->response->setJSON(['status' => -1, 'msg' => '责任人未确认损失金额属实，无法改变解决状态！']);
            }
            $this->db->transStart();
            $this->db->table('service')->where(['id' => $id])->update(['isDisable' => $isDisable, 'endName' => $endName, 'endDate' => $endDate]);
            $this->myservice->log('将编号：' . $id . ' 的客户之声解决状态标记为' . ($isDisable > 0 ? '已解决' : '未解决'));
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
        $data['orderId'] = 0;
        $data['deliverId'] = 0;
        if (strlen($data['orderNo']) > 0) {
            if (strlen($data['orderNo']) < 10 || strlen($data['orderNo']) > 50 || preg_match('/^[a-zA-Z0-9\-]+$/', $data['orderNo']) !== 1) {
                return $this->response->setJSON(['status' => -1, 'msg' => '订单号格式异常，请准确输入！']);
            } else {
                if ($this->db->table('service')->where(['orderNo' => $data['orderNo'], 'id !=' => $data['id'], 'isDelete' => 0])->countAllResults() > 0) {
                    return $this->response->setJSON(['status' => -1, 'msg' => '该订单号已录入过客户之声！']);
                }
                $orderId = $this->db->table('bill_order')->where(['billNo' => $data['orderNo'], 'isDelete' => 0])->get()->getRowArray()['id'] ?? 0;
                if ($orderId > 0) {
                    $data['orderId'] = $orderId;
                } else {
                    return $this->response->setJSON(['status' => -1, 'msg' => '系统中无此订单号！']);
                }
            }
        }
        if (strlen($data['deliverNo']) > 0) {
            if (strlen($data['deliverNo']) < 11 || strlen($data['deliverNo']) > 15 || preg_match('/^[a-zA-Z0-9]+$/', $data['deliverNo']) !== 1) {
                return $this->response->setJSON(['status' => -1, 'msg' => '运单号格式异常，请准确输入！']);
            } else {
                $deliverId = $this->db->table('deliver_bag')->where(['no' => $data['deliverNo'], 'isDelete' => 0])->get()->getRowArray()['id'] ?? 0;
                if ($deliverId > 0) {
                    $data['deliverId'] = $deliverId;
                } else {
                    $deliverId = $this->db->table('deliver_rec')->where(['no' => $data['deliverNo'], 'isDelete' => 0])->get()->getRowArray()['id'] ?? 0;
                    if ($deliverId > 0) {
                        $data['deliverId'] = $deliverId;
                    } else {
                        return $this->response->setJSON(['status' => -1, 'msg' => '发包与收包记录均无此运单号！']);
                    }
                }
            }
        }
        return $data;
    }
}
