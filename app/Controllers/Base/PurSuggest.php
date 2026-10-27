<?php

namespace App\Controllers\Base;
use App\Controllers\BaseController;

class PurSuggest extends BaseController
{
    // 采购建议 列表
    public function list()
    {
        if (!$this->myservice->verifyRight('PC_UPDATE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/PurSuggest/list 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $page = max(intval($this->request->getGetPost('page')), 1);
        $rows = max(intval($this->request->getGetPost('rows')), 100);
        $sidx = $this->request->getGetPost('sidx');
        $sord = strtoupper((string)$this->request->getGetPost('sord')) === 'ASC' ? 'ASC' : 'DESC';
        $uId = intval($this->request->getGetPost('uId'));
        $skey = $this->request->getGetPost('skey');
        $builder = $this->db->table('pur_suggest');
        $builder->where('isDelete', 0);
        if ($uId > 0) {
            $builder->where('purId', $uId);
        }
        if ($skey !== '' && $skey !== null) {
            $keywords = array_values(array_filter(explode(',', $skey), 'strlen'));
            foreach ($keywords as $kw) {
                $builder->groupStart()
                    ->like('billNo', $this->db->escapeLikeString($kw))
                    ->orLike('zid', $this->db->escapeLikeString($kw))
                    ->orLike('no', $this->db->escapeLikeString($kw))
                    ->orLike('name', $this->db->escapeLikeString($kw))
                    ->orLike('spec', $this->db->escapeLikeString($kw))
                    ->orLike('sku', $this->db->escapeLikeString($kw))
                    ->orLike('brand', $this->db->escapeLikeString($kw))
                    ->orLike('url', $this->db->escapeLikeString($kw))
                    ->orLike('content', $this->db->escapeLikeString($kw))
                    ->orLike('disReason', $this->db->escapeLikeString($kw))
                ->groupEnd();
            }
        }
        $allowedSort = ['id','createTime','modifyTime','status'];
        $orderField  = $sidx ? (in_array($sidx, $allowedSort, true) ? $sidx : 'id') : 'id';
        $list = (clone $builder)->orderBy($orderField, $sord)->limit($rows)->offset($rows * ($page - 1))->get()->getResultArray();
        $total = $builder->countAllResults();
        $v = [];
        foreach ($list as $arr => $row) {
            $v[$arr]['id'] = intval($row['id']);
            $v[$arr]['billId'] = intval($row['billId']);
            $v[$arr]['billNo'] = $row['billNo'];
            $v[$arr]['transType'] = intval($row['transType']);
            $v[$arr]['zid'] = $row['zid'];
            $v[$arr]['no'] = $row['no'];
            $v[$arr]['name'] = $row['name'];
            $v[$arr]['spec'] = $row['spec'];
            $v[$arr]['sku'] = $row['sku'];
            $v[$arr]['brand'] = $row['brand'];
            $v[$arr]['unit'] = $row['unit'];
            $v[$arr]['qty'] = (float)$row['qty'];
            $v[$arr]['purPrice'] = (float)$row['purPrice'];
            $v[$arr]['purAmount'] = (float)$row['purAmount'];
            $v[$arr]['purId'] = intval($row['purId']);
            $v[$arr]['purName'] = $row['purName'];
            $v[$arr]['price'] = (float)$row['price'];
            $v[$arr]['amount'] = (float)$row['amount'];
            $v[$arr]['url'] = $row['url'];
            $v[$arr]['content'] = $row['content'];
            $v[$arr]['uId'] = intval($row['uId']);
            $v[$arr]['uName'] = $row['uName'];
            $v[$arr]['createTime'] = $row['createTime'] == '0000-00-00 00:00:00' ? '' : $row['createTime'];
            $v[$arr]['modifyTime'] = $row['modifyTime'] == '0000-00-00 00:00:00' ? '' : $row['modifyTime'];
            $v[$arr]['status'] = intval($row['status']);
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
    // 采购建议 获取
    public function query()
    {
        if (!$this->myservice->verifyRight('PC_UPDATE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/PurSuggest/query 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $data = $this->db->table('pur_suggest')->where(['id' => $id, 'isDelete' => 0])->get()->getRowArray();
        if ($data) {
            $info['id'] = $id;
            $info['billId'] = $data['billId'];
            $info['billNo'] = $data['billNo'];
            $info['transType'] = $data['transType'];
            $info['zid'] = $data['zid'];
            $info['no'] = $data['no'];
            $info['name'] = $data['name'];
            $info['spec'] = $data['spec'];
            $info['sku'] = $data['sku'];
            $info['brand'] = $data['brand'];
            $info['unit'] = $data['unit'];
            $info['qty'] = $data['qty'];
            $info['purPrice'] = $data['purPrice'];
            $info['purAmount'] = $data['purAmount'];
            $info['purId'] = $data['purId'];
            $info['purName'] = $data['purName'];
            $info['price'] = $data['price'];
            $info['amount'] = $data['amount'];
            $info['url'] = $data['url'];
            $info['content'] = $data['content'];
            $json['status'] = 200;
            $json['msg'] = 'success';
            $json['data'] = $info;
            return $this->response->setJSON($json);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
    // 采购建议 新增
    public function add()
    {
        if (!$this->myservice->verifyRight('PUR_MANAGER')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/PurSuggest/add 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $data = $this->request->getPost();
        if (is_array($data) && count($data) > 0) {
            $data = $this->validform($data);
            // 如果返回的是 Response 对象，直接返回它
            if ($data instanceof \CodeIgniter\HTTP\Response) {
                return $data;
            }
            $info = array_intersect_key($data, array_flip(['billId', 'billNo', 'transType', 'zid', 'no', 'name', 'spec', 'sku', 'brand', 'unit', 'qty', 'purPrice', 'purAmount', 'purId', 'purName', 'price', 'amount', 'url', 'content', 'uId', 'uName', 'createTime']));
            $this->db->transStart();
            $this->db->table('pur_suggest')->insert($info);
            $data['id'] = $this->db->insertID();
            if ($data['transType'] == 101111) {
                $this->db->table('bill_contract_info')->where(['billNo' => $data['billNo'], 'zid' => $data['zid']])->update(['purSuggestId' => $data['id']]);
            } else {
                $this->db->table('bill_order_info')->where(['billNo' => $data['billNo'], 'no' => $data['no']])->update(['purSuggestId' => $data['id']]);
            }
            $this->myservice->log('新增了采购建议，所在单号：' . $data['billNo'] . '，' . ($data['zid'] ? ('产品自编序号：' . $data['zid']) : ('产品编码：' . $data['no'])) . '，原采购单价：' . $data['purPrice'] . '，建议采购单价：' . $data['price']);
            $this->db->transComplete();
            if ($this->db->transStatus() === FALSE) {
                return $this->response->setJSON(['status' => -1, 'msg' => '新增失败！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '新增成功！', 'data' => $data]);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '提交的是空数据！']);
    }
    // 采购建议 编辑
    public function update()
    {
        if (!$this->myservice->verifyRight('PUR_MANAGER')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/PurSuggest/update 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $data = $this->request->getPost();
        if (is_array($data) && count($data) > 0) {
            $data = $this->validform($data);
            // 如果返回的是 Response 对象，直接返回它
            if ($data instanceof \CodeIgniter\HTTP\Response) {
                return $data;
            }
            // 每次编辑都将抄收状态重置为未抄收，才会重新提醒
            $data['status'] = 0;
            $info = array_intersect_key($data, array_flip(['billId', 'billNo', 'transType', 'zid', 'no', 'name', 'spec', 'sku', 'brand', 'unit', 'qty', 'purPrice', 'purAmount', 'purId', 'purName', 'price', 'amount', 'url', 'content', 'uId', 'uName', 'modifyTime', 'status']));
            $this->db->transStart();
            $this->db->table('pur_suggest')->where(['id' => $data['id']])->update($info);
            $this->myservice->log('编辑了采购建议，所在单号：' . $data['billNo'] . '，' . ($data['zid'] ? ('产品自编序号：' . $data['zid']) : ('产品编码：' . $data['no'])) . '，原采购单价：' . $data['purPrice'] . '，建议采购单价：' . $data['price']);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '编辑成功！', 'data' => $data]);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '提交的是空数据！']);
    }
    // 采购建议 删除
    public function delete()
    {
        if (!$this->myservice->verifyRight('PUR_MANAGER')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/PurSuggest/delete 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $data = $this->db->table('pur_suggest')->where(['id' => $id])->get()->getRowArray();
        if ($data) {
            $this->db->transStart();
            $this->db->table('pur_suggest')->where(['id' => $id])->update(['isDelete' => 1]);
            if ($data['transType'] == 101111) {
                $this->db->table('bill_contract_info')->where(['billNo' => $data['billNo'], 'zid' => $data['zid']])->update(['purSuggestId' => 0]);
            } else {
                $this->db->table('bill_order_info')->where(['billNo' => $data['billNo'], 'no' => $data['no']])->update(['purSuggestId' => 0]);
            }
            $this->myservice->log('删除了采购建议，所在单号：' . $data['billNo'] . '，' . ($data['zid'] ? ('产品自编序号：' . $data['zid']) : ('产品编码：' . $data['no'])) . '，原采购单价：' . $data['purPrice'] . '，建议采购单价：' . $data['price']);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '删除成功！']);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
    // 采购建议 采购人操作
    public function status()
    {
        if (!$this->myservice->verifyRight('PC_UPDATE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/PurSuggest/status 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $status = intval($this->request->getGetPost('status'));
        $disReason = $this->request->getGetPost('disReason') ?? '';
        $data = $this->db->table('pur_suggest')->where(['id' => $id])->get()->getRowArray();
        if ($data) {
            $this->db->transStart();
            if ($status == 1) {
                $this->db->table('pur_suggest')->where('id', $id)->update(['status' => 1, 'disReason' => '']);
                $this->myservice->log('采纳了采购建议，所在单号：' . $data['billNo'] . '，' . ($data['zid'] ? ('产品自编序号：' . $data['zid']) : ('产品编码：' . $data['no'])) . '，原采购单价：' . $data['purPrice'] . '，建议采购单价：' . $data['price']);
            } else {
                $this->db->table('pur_suggest')->where('id', $id)->update(['status' => 2, 'disReason' => $disReason]);
                $this->myservice->log('拒绝了采购建议，所在单号：' . $data['billNo'] . '，' . ($data['zid'] ? ('产品自编序号：' . $data['zid']) : ('产品编码：' . $data['no'])) . '，原采购单价：' . $data['purPrice'] . '，建议采购单价：' . $data['price']);
            }
            $data['status'] = $status;
            $data['disReason'] = $disReason;
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '操作成功！', 'data' => $data]);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
    // 数据验证
    private function validform(array $data)
    {
        $data['id'] = isset($data['id']) ? intval($data['id']) : -1;
        $data['purAmount'] = round((float)$data['qty'] * (float)$data['purPrice'], 2);
        $data['amount'] = round((float)$data['qty'] * (float)$data['price'], 2);
        $data['url'] = $data['url'] ? shop_url_parse($data['url']) : '';
        $data['uId'] = $data['uId'] ?? $this->myerp['uId'];
        $data['uName'] = $data['uName'] ?? $this->myerp['uName'];
        $data['createTime'] = $data['modifyTime'] = date('Y-m-d H:i:s');
        return $data;
    }
}
