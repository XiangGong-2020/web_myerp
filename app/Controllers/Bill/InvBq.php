<?php
namespace App\Controllers\Bill;
use App\Controllers\BaseController;
class InvBq extends BaseController
{
    // 列表
    public function list()
    {
        if (!$this->myservice->verifyRight('BIDQUOTE_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/InvBq/list 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $page = max(intval($this->request->getGetPost('page')), 1);
        $rows = max(intval($this->request->getGetPost('rows')), 100);
        $sidx = $this->request->getGetPost('sidx');
        $sord = strtoupper((string)$this->request->getGetPost('sord')) === 'ASC' ? 'ASC' : 'DESC';
        $uId = intval($this->request->getGetPost('uId'));
        $skey = $this->request->getGetPost('skey');
        $beginDate = $this->request->getGetPost('beginDate');
        $endDate = $this->request->getGetPost('endDate');
        $builder = $this->db->table('bill_bidquote');
        if ($uId > 0) {
            $builder->where('uId', $uId);
        }
        if ($beginDate) {
            $builder->where('billDate >=', $beginDate);
        }
        if ($endDate) {
            $builder->where('billDate <=', $endDate);
        }
        if ($skey !== '' && $skey !== null) {
            $keywords = array_values(array_filter(explode(',', $skey), 'strlen'));
            foreach ($keywords as $kw) {
                $builder->groupStart()
                    ->like('billNo', $this->db->escapeLikeString($kw))
                    ->orLike('contactName', $this->db->escapeLikeString($kw))
                    ->orLike('sName', $this->db->escapeLikeString($kw))
                    ->orLike('goods', $this->db->escapeLikeString($kw))
                    ->orLike('description', $this->db->escapeLikeString($kw))
                ->groupEnd();
            }
        }
        $builder->where('isDelete', 0);
        $allowedSort = ['id','bidSiteName','bidTitle','billNo','uName','offerEndTime','description','quoteNum','quoteName'];
        $orderField  = $sidx ? (in_array($sidx, $allowedSort, true) ? $sidx : 'id') : 'id';
        $list = (clone $builder)->orderBy($orderField, $sord)->limit($rows)->offset($rows * ($page - 1))->get()->getResultArray();
        $total = $builder->countAllResults();
        $v = [];
        foreach ($list as $arr => $row) {
            $v[$arr]['id'] = intval($row['id']);
            $v[$arr]['billNo'] = $row['billNo'];
            $v[$arr]['billDate'] = $row['billDate'];
            $v[$arr]['offerEndTime'] = $row['offerEndTime'];
            $v[$arr]['contactId'] = intval($row['contactId']);
            $v[$arr]['contactName'] = $row['contactName'];
            $v[$arr]['sId'] = intval($row['sId']);
            $v[$arr]['sName'] = $row['sName'];
            $v[$arr]['amount'] = (float)$row['amount'];
            $v[$arr]['bidSiteId'] = intval($row['bidSiteId']);
            $v[$arr]['bidSiteName'] = $row['bidSiteName'];
            $v[$arr]['bidInfoId'] = intval($row['bidInfoId']);
            $v[$arr]['bidTitle'] = $row['bidTitle'];
            $v[$arr]['bidUrl'] = $row['bidUrl'];
            $v[$arr]['contactInfo'] = $row['contactInfo'];
            $v[$arr]['goods'] = (array)json_decode($row['goods'], true);
            $v[$arr]['description'] = $row['description'];
            $v[$arr]['quoteName'] = $row['quoteName'];
            $v[$arr]['quoteNum'] = intval($row['quoteNum']);
            $v[$arr]['quotedName'] = $row['quotedName'];
            $v[$arr]['onModify'] = intval($row['onModify']);
            $v[$arr]['modifyTimes'] = intval($row['modifyTimes']);
            $v[$arr]['uId'] = $row['uId'];
            $v[$arr]['uName'] = $row['uName'];
            $v[$arr]['createTime'] = $row['createTime'];
            $v[$arr]['isDraft'] = intval($row['isDraft']);
            $v[$arr]['bidStatus'] = intval($row['bidStatus']);
            $v[$arr]['bidDate'] = $row['bidDate'] == '0000-00-00' ? '' : $row['bidDate'];
            $v[$arr]['programStatus'] = intval($row['programStatus']);
            $v[$arr]['contractStatus'] = intval($row['contractStatus']);
            $v[$arr]['programId'] = intval($row['programId']);
            $v[$arr]['contractId'] = intval($row['contractId']);
        }
        $json['status'] = 200;
        $json['msg'] = 'success';
        $json['data']['page'] = $page;
        $json['data']['records'] = $total;
        $json['data']['total'] = ceil($json['data']['records'] / $rows);
        $json['data']['rows'] = $v;
        return $this->response->setJSON($json);
    }
    // 详单信息列表
    public function getEntries()
    {
        if (!$this->myservice->verifyRight('BIDQUOTE_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/InvBq/getEntries 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $list = $this->db->table('bill_bidquote_info')->where(['iid' => $id, 'isDelete' => 0])->get()->getResultArray();
        $v = [];
        foreach ($list as $arr => $row) {
            $v[] = [
                'zid' => $row['zid'],
                'no' => $row['no'],
                'name' => $row['name'],
                'spec' => $row['spec'],
                'sku' => $row['sku'],
                'brand' => $row['brand'],
                'unit' => $row['unit'],
                'qty' => (float)$row['qty'],
                'price' => (float)$row['price'],
                'amount' => (float)$row['amount'],
                'buyUrl' => $row['buyUrl'],
                'description1' => $row['description1'],
                'description2' => $row['description2'],
            ];
        }
        $json['status'] = 200;
        $json['msg'] = 'success';
        $json['data'] = $v;
        return $this->response->setJSON($json);
    }
    // 报价信息列表
    public function quoteInfoList()
    {
        if (!$this->myservice->verifyRight('BIDQUOTE_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/InvBq/quoteInfoList 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $sidx = $this->request->getGetPost('sidx');
        $sord = strtoupper((string)$this->request->getGetPost('sord')) === 'ASC' ? 'ASC' : 'DESC';
        $iid = intval($this->request->getGetPost('iid'));
        $zid = $this->request->getGetPost('zid');
        $builder = $this->db->table('bill_bidquote_info_quote');
        $builder->where(['iid' => $iid, 'zid' => $zid, 'price >' => 0, 'isDelete' => 0]);
        $allowedSort = ['id','iid','zid','goodsId'];
        $orderField  = $sidx ? (in_array($sidx, $allowedSort, true) ? $sidx : 'id') : 'id';
        $list = (clone $builder)->orderBy($orderField, $sord)->get()->getResultArray();
        $total = $builder->countAllResults();
        $v = [];
        foreach ($list as $arr => $row) {
            $v[$arr]['id'] = intval($row['id']);
            $v[$arr]['iid'] = intval($row['iid']);
            $v[$arr]['zid'] = $row['zid'];
            $v[$arr]['goodsId'] = intval($row['goodsId']);
            $v[$arr]['no'] = $row['no'];
            $v[$arr]['name'] = $row['name'];
            $v[$arr]['spec'] = $row['spec'];
            $v[$arr]['sku'] = $row['sku'];
            $v[$arr]['brand'] = $row['brand'];
            $v[$arr]['unit'] = $row['unit'];
            $v[$arr]['qty'] = (float)$row['qty'];
            $v[$arr]['price'] = $row['price'] ?? 0;
            $v[$arr]['amount'] = $row['amount'] ?? 0;
            $v[$arr]['myUrl'] = $row['myUrl'];
            $v[$arr]['description1'] = $row['description1_quote'] ?? $row['description1'];
            $v[$arr]['description2'] = $row['description2_quote'] ?? $row['description2'];
            $v[$arr]['uId'] = intval($row['uId']);
            $v[$arr]['uName'] = $row['uName'];
            $v[$arr]['firstTime'] = $row['firstTime'];
            $v[$arr]['lastTime'] = $row['lastTime'];
            $v[$arr]['status'] = intval($row['status']);
        }
        $json['status'] = 200;
        $json['msg'] = 'success';
        $json['data']['records'] = $total;
        $json['data']['total'] = 1;
        $json['data']['rows'] = $v;
        return $this->response->setJSON($json);
    }
    // 查询
    public function query()
    {
        if (!$this->myservice->verifyRight('BIDQUOTE_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/InvBq/query 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $data = $this->db->table('bill_bidquote')->where(['id' => $id, 'isDelete' => 0])->get()->getRowArray();
        if ($data) {
            $info['id'] = $id;
            $info['billNo'] = $data['billNo'];
            $info['billDate'] = $data['billDate'];
            $info['offerEndTime'] = $data['offerEndTime'];
            $info['contactId'] = intval($data['contactId']);
            $info['contactName'] = $data['contactName'];
            $info['sId'] = intval($data['sId']);
            $info['sName'] = $data['sName'];
            $info['amount'] = (float)$data['amount'];
            $info['bidInfoId'] = intval($data['bidInfoId']);
            $info['bidSiteId'] = intval($data['bidSiteId']);
            $info['bidSiteName'] = $data['bidSiteName'];
            $info['bidTitle'] = $data['bidTitle'];
            $info['bidUrl'] = $data['bidUrl'];
            $info['contactInfo'] = $data['contactInfo'];
            $info['goods'] = $data['goods'] == '' ? [] : json_decode($data['goods'], true);
            $info['description'] = $data['description'];
            $info['files'] = $data['files'] == '' ? [] : json_decode($data['files'], true);
            $info['uId'] = intval($data['uId']);
            $info['uName'] = $data['uName'];
            $info['createTime'] = $data['createTime'];
            $info['modifyTime'] = $data['modifyTime'];
            $info['quoteName'] = $data['quoteName'];
            $info['quoteNum'] = $data['quoteNum'];
            $info['quotedName'] = $data['quotedName'];
            $info['onModify'] = $data['onModify'];
            $info['modifyTimes'] = $data['modifyTimes'];
            $info['isDraft'] = intval($data['isDraft']);
            $list = $this->db->table('bill_bidquote_info')->where(['iid' => $id, 'isDelete' => 0])->orderBy('CAST(zid AS UNSIGNED) asc, id asc')->get()->getResultArray();
            $v = [];
            foreach ($list as $arr => $row) {
                $v[] = [
                    'id' => intval($row['id']),
                    'iid' => intval($row['iid']),
                    'zid' => $row['zid'],
                    'no' => $row['no'],
                    'name' => $row['name'],
                    'spec' => $row['spec'],
                    'sku' => $row['sku'],
                    'brand' => $row['brand'],
                    'unit' => $row['unit'],
                    'qty' => (float)$row['qty'],
                    'price' => (float)$row['price'],
                    'amount' => (float)$row['amount'],
                    'buyUrl' => $row['buyUrl'],
                    'description1' => $row['description1'],
                    'description2' => $row['description2'],
                    'uId' => intval($row['uId']),
                    'uName' => $row['uName']
                ];
            }
            $info['entries'] = $v;
            $json['status'] = 200;
            $json['msg'] = 'success';
            $json['data'] = $info;
            $json['totalsize'] = count($list);
            return $this->response->setJSON($json);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
    // 新增
    public function add()
    {
        if (!$this->myservice->verifyRight('BIDQUOTE_ADD')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/InvBq/add 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $data = $this->request->getPost('postData');
        if (strlen($data) > 0) {
            $data = $this->validform((array)json_decode($data, TRUE));
            // 如果返回的是 Response 对象，直接返回它
            if ($data instanceof \CodeIgniter\HTTP\Response) {
                return $data;
            }
            //自动审核，后面如果开始严格审核要删除下面2项
            $info = array_intersect_key($data, array_flip([
                'bidInfoId',
                'bidSiteId',
                'bidSiteName',
                'bidTitle',
                'bidUrl',
                'contactInfo',
                'quotedName',
                'billNo',
                'billDate',
                'offerEndTime',
                'contactId',
                'contactName',
                'sId',
                'sName',
                'amount',
                'goods',
                'description',
                'files',
                'uId',
                'uName',
                'createTime',
                'modifyTime',
                'isDraft'
            ]));
            $this->db->transStart();
            $this->db->table('bill_bidquote')->insert($info);
            $iid = $this->db->insertID();
            $this->bidquote_info(intval($iid), $data, []);
            $this->db->table('bid_info')->where('id', $data['bidInfoId'])->update(['quoteId' => $iid, 'quoteNo' => $data['billNo']]);
            $this->myservice->log('新增了预算报价单' . ($data['isDraft'] == 1 ? '草稿' : '') . '，单号：' . $data['billNo']);
            $data['id'] = $iid;
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
        if (!$this->myservice->verifyRight('BIDQUOTE_UPDATE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/InvBq/update 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $data = $this->request->getPost('postData');
        if (strlen($data) > 0) {
            $data = $this->validform((array)json_decode($data, true));
            // 如果返回的是 Response 对象，直接返回它
            if ($data instanceof \CodeIgniter\HTTP\Response) {
                return $data;
            }
            $oldData = $this->db->table('bill_bidquote')->getWhere(['id' => $data['id']])->getRowArray();
            $this->db->transStart();
            //先处理info信息，方便提前判断异常中断后续操作
            if ($data['goods'] != $oldData['goods'] || $data['isNewImportExcel']) {
                $this->bidquote_info(intval($data['id']), $data, $oldData);
            } else {
                $this->bidquote_info_update(intval($data['id']), $data, $oldData);
            }
            //无异常再处理后续
            $info = array_intersect_key($data, array_flip([
                'contactInfo',
                'quotedName',
                'offerEndTime',
                'contactId',
                'contactName',
                'sId',
                'sName',
                'amount',
                'goods',
                'description',
                'files',
                'modifyTime',
                'isDraft'
            ]));
            $this->db->table('bill_bidquote')->where(['id' => $data['id']])->update($info);
            $this->db->table('bill_bidquote')->set('onModify', 0)->set('modifyTimes', 'modifyTimes + 1', false)->where(['id' => $data['id']])->update();
            $this->db->table('bid_info')->where(['id' => $data['bidInfoId']])->update(['quoteId' => $data['id'], 'quoteNo' => $data['billNo']]);
            $this->myservice->log('编辑了预算报价单' . ($data['isDraft'] == 1 ? '草稿' : '') . '，' . (($oldData['isDraft'] == 1 && $data['isDraft'] == 0) ? '并将草稿转为了正式，' : '') . '单号：' . $data['billNo']);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '编辑成功！']);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '提交的是空数据！']);
    }
    // 删除
    public function delete()
    {
        if (!$this->myservice->verifyRight('BIDQUOTE_DELETE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/InvBq/delete 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $data = $this->db->table('bill_bidquote')->where(['id' => $id, 'isDelete' => 0])->get()->getRowArray();
        if ($data) {
            if (!$this->myservice->verifyRight('SYS_ADMIN') && $data['uId'] != $this->myerp['uId']) {
                return $this->response->setJSON(['status' => -1, 'msg' => '不能删除别人的预算报价单！仅本人或管理员可删除！']);
            }
            if ($data['bidStatus'] == 1) {
                return $this->response->setJSON(['status' => -1, 'msg' => '已投标，不可随意删除！']);
            }
            if ($data['programStatus'] > -1) {
                return $this->response->setJSON(['status' => -1, 'msg' => '已转投标项目，不可随意删除！']);
            }
            if ($data['contractStatus'] > 0) {
                return $this->response->setJSON(['status' => -1, 'msg' => '已转特殊销售单，不可随意删除！']);
            }
            $this->db->transStart();
            $this->db->table('bill_bidquote')->where(['id' => $id])->update(['isDelete' => 1]);
            $this->db->table('bill_bidquote_info')->where(['iid' => $id])->update(['isDelete' => 1]);
            $this->db->table('bid_info')->where(['id' => $data['bidInfoId']])->update(['quoteId' => 0, 'quoteNo' => '']);
            $this->myservice->log('删除了预算报价单！单号：' . $data['billNo']);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '删除成功！', 'data' => ['id' => $id]]);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
    // 更新备注
    public function updatedes()
    {
        if (!$this->myservice->verifyRight('BIDQUOTE_UPDATE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/InvBq/updatedes 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $newDes = $this->request->getGetPost('newDes');
        $data = $this->db->table('bill_bidquote')->where(['id' => $id])->get()->getRowArray();
        if ($data) {
            $v['description'] = $newDes;
            $v['modifyTime'] = date("Y-m-d H:i:s");
            $this->db->transStart();
            $this->db->table('bill_bidquote')->where(['id' => $id])->update($v);
            $this->myservice->log('单独更新了预算报价单的备注，单号：' . $data['billNo']);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '更新成功！', 'data' => ['id' => $id]]);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
    // info数据 产品有变化 全部删除 重新插入
    private function bidquote_info(int $iid, array $data, array $oldData)
    {
        $v = [];
        foreach ($data['entries'] as $arr => $row) {
            $v[] = [
                'iid' => intval($iid),
                'zid' => $row['zid'],
                'billDate' => $data['billDate'],
                'billNo' => $data['billNo'],
                'contactId' => $data['contactId'],
                'storageId' => 1,
                'goodsId' => $row['no'] ? ($this->db->table('goods')->select('id')->where(['no' => $row['no']])->get()->getRowArray()['id'] ?? 0) : 0,
                'no' => $row['no'],
                'name' => $row['name'],
                'spec' => $row['spec'],
                'sku' => $row['sku'],
                'brand' => $row['brand'],
                'unit' => $row['unit'],
                'qty' => abs((float)$row['qty']),
                'price' => abs((float)$row['price']),
                'amount' => abs((float)$row['amount']),
                'buyUrl' => $row['buyUrl'] ?? '',
                'description1' => $row['description1'],
                'description2' => $row['description2'],
                'uId' => $row['uId'] ?? $data['uId'],
                'uName' => $row['uName'] ?? $data['uName'],
                'isDraft' => $data['isDraft'],
            ];
        }
        if (!empty($v)) {
            if ($data['id'] > 0) {
                // 真删除
                $this->db->table('bill_bidquote_info')->where(['iid' => $iid])->delete();
            }
            $this->db->table('bill_bidquote_info')->insertBatch($v);
        }
    }
    // info数据 产品无变化 全部更新
    private function bidquote_info_update(int $iid, array $data, array $oldData)
    {
        $v = [];
        foreach ($data['entries'] as $arr => $row) {
            $v[] = [
                'id' => intval($row['id']),
                'iid' => intval($iid),
                'zid' => $row['zid'],
                'billDate' => $data['billDate'],
                'billNo' => $data['billNo'],
                'contactId' => intval($data['contactId']),
                'storageId' => 1,
                'goodsId' => $row['no'] ? ($this->db->table('goods')->select('id')->where(['no' => $row['no']])->get()->getRowArray()['id'] ?? 0) : 0,
                'no' => $row['no'],
                'name' => $row['name'],
                'spec' => $row['spec'],
                'sku' => $row['sku'],
                'brand' => $row['brand'],
                'unit' => $row['unit'],
                'qty' => abs((float)$row['qty']),
                'price' => abs((float)$row['price']),
                'amount' => abs((float)$row['amount']),
                'buyUrl' => $row['buyUrl'] ?? '',
                'description1' => $row['description1'],
                'description2' => $row['description2'],
                'uId' => $row['uId'] ?? $data['uId'],
                'uName' => $row['uName'] ?? $data['uName'],
                'isDraft' => $data['isDraft'],
            ];
        }
        if (!empty($v)) {
<<<<<<< HEAD
            $this->db->table('bill_bidquote_info')->updateBatch($v, ['id', 'iid']);
=======
            $this->db->table('bill_bidquote_info')->updateBatch($v, 'id');
>>>>>>> 151b415 (更新语言最新版本+最新底层框架后全部差异化代码适配重写)
        }
    }
    // 数据验证
    private function validform(array $data)
    {
        $data['id'] = isset($data['id']) ? intval($data['id']) : -1;
        if ($data['billNo'] == '')  {
            return $this->response->setJSON(['status' => -1, 'msg' => '单据编号不能为空！']);
        }
        if ($data['billDate'] == '') {
            return $this->response->setJSON(['status' => -1, 'msg' => '单据日期不能为空！']);
        }
        if ($data['contactName'] == '') {
            return $this->response->setJSON(['status' => -1, 'msg' => '招标单位不能为空！']);
        }
        if ($data['sName'] == '') {
            return $this->response->setJSON(['status' => -1, 'msg' => '投标单位不能为空！']);
        }
        if ($data['offerEndTime'] == '') {
            return $this->response->setJSON(['status' => -1, 'msg' => '报价截止时间不能为空！']);
        }
        if ($data['contactInfo'] == '') {
            return $this->response->setJSON(['status' => -1, 'msg' => '联系方式不能为空！']);
        }
        if ($data['quotedName'] == '') {
            return $this->response->setJSON(['status' => -1, 'msg' => '有效报价人不能为空！']);
        }
        $data['amount'] = abs((float)$data['amount']);
        $data['uId'] = $data['uId'] ?? $this->myerp['uId'];
        $data['uName'] = $data['uName'] ?? $this->myerp['uName'];
        $data['createTime'] = date("Y-m-d H:i:s");
        $data['modifyTime'] = date("Y-m-d H:i:s");
        $data['entries'] = $data['entries'] ?? [];
        $data['description'] = isset($data['description']) ? str_replace(['{', '}', '"', "'"], '”', $data['description']) : '';
        $data['files'] = strlen($data['files'] ?? '') < 10 ? '' : $data['files'];
        $data['isDraft'] = isset($data['isDraft']) ? intval($data['isDraft']) : 0;
        if ($data['id'] > 0) {
            if ($this->db->table('bill_bidquote')->where(['id' => $data['id'], 'isDelete' => 0])->countAllResults() < 1) {
                return $this->response->setJSON(['status' => -1, 'msg' => '单据不存在、或者已删除！']);
            }
        } else {
            if ($this->db->table('bill_bidquote')->where(['bidInfoId' => $data['bidInfoId']])->countAllResults() > 0) {
                return $this->response->setJSON(['status' => -1, 'msg' => '此招标信息已创建过预算报价单！']);
            }
            if ($this->db->table('bill_bidquote')->where(['billNo' => $data['billNo']])->countAllResults() > 0) {
                return $this->response->setJSON(['status' => -1, 'msg' => '单据编号重复！']);
            }
        }
        if (count($data['entries']) < 1) {
            return $this->response->setJSON(['status' => -1, 'msg' => '提交的是空数据！']);
        }
        foreach ($data['entries'] as $arr => $row) {
            //处理容易出问题的特殊字符
            $row['zid'] = str_replace(["\r", "\n", "\r\n", '{', '}', '"', "'"], '”', trim($row['zid']));
            $row['name'] = str_replace(["\r", "\n", "\r\n", '{', '}', '"', "'"], '”', trim($row['name']));
            $row['spec'] = str_replace(["\r", "\n", "\r\n", '{', '}', '"', "'"], '”', trim($row['spec']));
            $row['sku'] = str_replace(["\r", "\n", "\r\n", '{', '}', '"', "'"], '”', trim($row['sku']));
            $row['brand'] = str_replace(["\r", "\n", "\r\n", '{', '}', '"', "'"], '”', trim($row['brand']));
            $row['unit'] = str_replace(["\r", "\n", "\r\n", '{', '}', '"', "'"], '”', trim($row['unit']));
            $row['buyUrl'] = $row['buyUrl'] ? shop_url_parse(trim($row['buyUrl'])) : '';
            $row['description1'] = str_replace(["\r", "\n", "\r\n", '{', '}', '"', "'"], '”', $row['description1']);
            $row['description2'] = str_replace(["\r", "\n", "\r\n", '{', '}', '"', "'"], '”', $row['description2']);
            // 错误检查
            if (strlen($row['name']) < 1) {
                return $this->response->setJSON(['status' => -1, 'msg' => '请选择产品！']);
            }
            if ((float)$row['qty'] < 0) {
                return $this->response->setJSON(['status' => -1, 'msg' => '数量要为正数！']);
            }
            if ((float)$row['price'] < 0) {
                return $this->response->setJSON(['status' => -1, 'msg' => '价格要为正数！']);
            }

            $data['goods'][$arr]['zid'] = $row['zid'];
            $data['goods'][$arr]['name'] = $row['name'];
            $data['goods'][$arr]['spec'] = $row['spec'];
            $data['goods'][$arr]['sku'] = $row['sku'];
            $data['goods'][$arr]['brand'] = $row['brand'];
            $data['goods'][$arr]['unit'] = $row['unit'];
            $data['goods'][$arr]['qty'] = abs((float)$row['qty']);
            $data['goods'][$arr]['price'] = abs((float)$row['price']);
            $data['goods'][$arr]['amount'] = abs((float)$row['amount']);
        }
        $data['goods'] = json_encode($data['goods'], 320);
        return $data;
    }


    // 上传资料
    public function uploadfile()
    {
        if (!$this->myservice->verifyRight('BIDQUOTE_ADD')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/InvBq/uploadfile 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        set_time_limit(0);
        session_write_close();
        if (empty($_FILES)){
            return $this->response->setJSON(['status' => -1, 'msg' => '没有上传文件！']);
        };
        $uppath = 'data/upfile/bidQuote/';
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
        $this->myservice->log('上传了预算报价资料：' . $_FILES['file']['name']);
        $json['status'] = 200;
        $json['msg'] = '资料上传成功';
        $json['file'] = ['name' => $_FILES['file']['name'], 'url' => $target_file, 'upName' => $this->myerp['uName'], 'upTime' => date('Y-m-d')];
        return $this->response->setJSON($json);
    }
    // 上传表格并提取数据
    public function uploadExcel()
    {
        if (!$this->myservice->verifyRight('BIDQUOTE_ADD')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/InvBq/uploadExcel 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        set_time_limit(0);
        session_write_close();
        if (empty($_FILES)){
            return $this->response->setJSON(['status' => -1, 'msg' => '没有上传文件！']);
        };
        $uppath = 'data/upfile/bidQuote/excel_temp/';
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
        $this->myservice->log('导入了预算报价表格：' . $_FILES['file']['name']);

        // 调用spreadsheet处理表格
        $inputFileName = $uppath . $target_file; // 要读取的xls文件路径
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($inputFileName);
        $sheet = $spreadsheet->getActiveSheet();
        $sheetData = $sheet->toArray(null, true, true, true);
        // 组装数据插入表
        $v = [];
        for ($i = 3; $i <= count($sheetData); $i++) {
            $v[$i - 3]['iid'] = 0;
            $v[$i - 3]['zid'] = $sheetData[$i]['A'] ? str_replace(["\r", "\n", "\r\n", '{', '}', '"', "'"], '”', $sheetData[$i]['A']) : '';
            $v[$i - 3]['no'] = $sheetData[$i]['B'] ? str_replace(["\r", "\n", "\r\n", '{', '}', '"', "'"], '', $sheetData[$i]['B']) : '';
            $v[$i - 3]['name'] = $sheetData[$i]['C'] ? str_replace(["\r", "\n", "\r\n", '{', '}', '"', "'"], '”', $sheetData[$i]['C']) : '';
            $v[$i - 3]['spec'] = $sheetData[$i]['D'] ? str_replace(["\r", "\n", "\r\n", '{', '}', '"', "'"], '”', $sheetData[$i]['D']) : '';
            $v[$i - 3]['sku'] = $sheetData[$i]['E'] ? str_replace(["\r", "\n", "\r\n", '{', '}', '"', "'"], '”', $sheetData[$i]['E']) : '';
            $v[$i - 3]['brand'] = $sheetData[$i]['F'] ? str_replace(["\r", "\n", "\r\n", '{', '}', '"', "'"], '”', $sheetData[$i]['F']) : '';
            $v[$i - 3]['unit'] = $sheetData[$i]['G'] ? str_replace(["\r", "\n", "\r\n", '{', '}', '"', "'"], '”', $sheetData[$i]['G']) : '';
            $v[$i - 3]['qty'] = $sheetData[$i]['H'] ?? 0;
            $v[$i - 3]['price'] = $sheetData[$i]['I'] ?? 0;
            $thistotal = $sheetData[$i]['J'] ?? 0;
            $v[$i - 3]['amount'] = (0 < (float)$thistotal ? (float)$thistotal : ((float)$v[$i - 3]['qty'] * (float)$v[$i - 3]['price']));
            // 判断是否有单元格超链接、公式超链接、或字符串url，最终提取出有效的参考链接
            $v[$i - 3]['buyUrl'] = '';
            $cell = $sheet->getCell('K' . $i); // 通过坐标直接获取单元格对象
            $url = '';
            if ($cell->hasHyperlink()) { // 判断【原生右键插入的超链接】
                $url = $cell->getHyperlink()->getUrl();
            } else { // 判断【=HYPERLINK公式生成的超链接】
                $rawValue = $cell->getValue();
                if (is_string($rawValue) && stripos($rawValue, '=HYPERLINK') === 0) {
                    if (preg_match('/^=HYPERLINK\(\s*["\']([^"\']+)["\']/i', $rawValue, $matches)) {
                        $url = $matches[1];
                    }
                }
            }
            if ($url) {
                $v[$i - 3]['buyUrl'] = shop_url_parse($url);
            } else {
                if ($sheetData[$i]['K']) {
                    preg_match('/https?:\/\/\S+/', $sheetData[$i]['K'], $matchUrl);
                    if (!empty($matchUrl[0])) {
                        $v[$i - 3]['buyUrl'] = shop_url_parse($matchUrl[0]);
                    }
                }
            }
            $v[$i - 3]['description1'] = $sheetData[$i]['L'] ? str_replace(["\r", "\n", "\r\n", '{', '}', '"', "'"], '”', $sheetData[$i]['L']) : '';
            $v[$i - 3]['description2'] = $sheetData[$i]['M'] ? str_replace(["\r", "\n", "\r\n", '{', '}', '"', "'"], '”', $sheetData[$i]['M']) : '';
        }
        // 返回组装好的数据
        $info['entries'] = array_values($v);
        $json['status'] = 200;
        $json['msg'] = 'success';
        $json['data'] = $info;
        $json['totalsize'] = count($info['entries']);
        return $this->response->setJSON($json);
    }
    // 导出最终报价清单
    public function exportInfoGrid()
    {
        if (!$this->myservice->verifyRight('BIDQUOTE_ADD')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/InvBq/exportInfoGrid 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = $this->request->getGetPost('id');
        $billNo = $this->request->getGetPost('billNo');
        $list = $this->db->table('bill_bidquote_info')->where('iid', $id)->get()->getResultArray();
        $name = '预算报价 最终报价清单 ' . $billNo;

        // 调用spreadsheet处理表格
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $activeWorksheet = $spreadsheet->getActiveSheet();

        // A-Z列 设置字符串自动换行(会撑开单元格高度,并破坏设置的默认行高)，设置水平居左+垂直居中
        $activeWorksheet->getStyle("A:Z")->getAlignment()->setWrapText(TRUE);
        $activeWorksheet->getStyle('A:Z')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
        $activeWorksheet->getStyle('A:Z')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

        // 设置边框
        $activeWorksheet->setShowGridlines(false);
        $styleArray = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => '00000000'],
                ]
            ],
        ];
        $activeWorksheet->getStyle('A1:M' . (COUNT($list) + 2))->applyFromArray($styleArray, false);

        // 合并单元格
        $activeWorksheet->mergeCells('A1:M1');

        // 设置水平居中
        $activeWorksheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // 设置行高
        $activeWorksheet->getRowDimension('1')->setRowHeight(40);

        // 设置加粗
        $activeWorksheet->getStyle('A1')->getFont()->setBold(true);
        $activeWorksheet->getStyle('A2:M2')->getFont()->setBold(true);

        // 设置背景色
        $activeWorksheet->getStyle('A2:M2')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
        $activeWorksheet->getStyle('A2:M2')->getFill()->getStartColor()->setRGB('F5F5F5');

        // 设置每列列宽
        $activeWorksheet->getColumnDimension('A')->setWidth(10);
        $activeWorksheet->getColumnDimension('B')->setWidth(20);
        $activeWorksheet->getColumnDimension('C')->setWidth(20);
        $activeWorksheet->getColumnDimension('D')->setWidth(20);
        $activeWorksheet->getColumnDimension('E')->setWidth(20);
        $activeWorksheet->getColumnDimension('F')->setWidth(10);
        $activeWorksheet->getColumnDimension('G')->setWidth(10);
        $activeWorksheet->getColumnDimension('H')->setWidth(10);
        $activeWorksheet->getColumnDimension('I')->setWidth(15);
        $activeWorksheet->getColumnDimension('J')->setWidth(15);
        $activeWorksheet->getColumnDimension('K')->setWidth(20);
        $activeWorksheet->getColumnDimension('L')->setWidth(20);
        $activeWorksheet->getColumnDimension('M')->setWidth(20);

        //设置当前的sheet
        $activeWorksheet->setCellValue('A1', $name . '（导出时间 ' . date('Y-m-d H:i:s') . '）');
        $activeWorksheet->setCellValue('A2', '自编序号');
        $activeWorksheet->setCellValue('B2', '产品编码');
        $activeWorksheet->setCellValue('C2', '产品名称');
        $activeWorksheet->setCellValue('D2', '规格型号');
        $activeWorksheet->setCellValue('E2', '辅助属性');
        $activeWorksheet->setCellValue('F2', '产品品牌');
        $activeWorksheet->setCellValue('G2', '单位');
        $activeWorksheet->setCellValue('H2', '数量');
        $activeWorksheet->setCellValue('I2', '最终报价单价');
        $activeWorksheet->setCellValue('J2', '最终报价总价');
        $activeWorksheet->setCellValue('K2', '最终参考链接');
        $activeWorksheet->setCellValue('L2', '最终备注1');
        $activeWorksheet->setCellValue('M2', '最终备注2');

        $i = 3;
        foreach ($list as $k => $v) {
            $activeWorksheet->setCellValueExplicit('A' . $i, $v['zid'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $activeWorksheet->setCellValue('B' . $i, $v['no']);
            $activeWorksheet->setCellValue('C' . $i, $v['name']);
            $activeWorksheet->setCellValue('D' . $i, $v['spec']);
            $activeWorksheet->setCellValue('E' . $i, $v['sku']);
            $activeWorksheet->setCellValue('F' . $i, $v['brand']);
            $activeWorksheet->setCellValue('G' . $i, $v['unit']);
            $activeWorksheet->setCellValue('H' . $i, $v['qty']);
            $activeWorksheet->setCellValue('I' . $i, $v['price']);
            $activeWorksheet->setCellValue('J' . $i, $v['amount']);
            $activeWorksheet->setCellValue('K' . $i, $v['buyUrl']);
            $activeWorksheet->setCellValue('L' . $i, $v['description1']);
            $activeWorksheet->setCellValue('M' . $i, $v['description2']);
            $i++;
        }

        $outputFileName = $name . ".xlsx";
        $this->myservice->log('导出了 ' . $name);
        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
        ob_start();
        $writer->save('php://output');
        $content = ob_get_clean();
        return $this->response
            ->setHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->setHeader('Content-Disposition', "attachment; filename=\"{$outputFileName}\"")
            ->setHeader('Cache-Control', 'max-age=0')
            ->setBody($content);
    }


    // 编辑前检查
    public function modifyCheck()
    {
        $id = intval($this->request->getGetPost('id'));
        $data = $this->db->table('bill_bidquote')->where(['id' => $id, 'isDelete' => 0])->get()->getRowArray();
        if ($data) {
            if ($data['uId'] != $this->myerp['uId'] && !$this->myservice->verifyRight('SYS_ADMIN')) {
                log_message('error', $this->myerp['uName'] . ' 访问 Bill/InvBq/modifyCheck 权限不足！');
                return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
            }
            if ($data['bidStatus'] == 1) {
                return $this->response->setJSON(['status' => -1, 'msg' => '已投标！不可再编辑！']);
            } elseif ($data['programStatus'] > -1) {
                return $this->response->setJSON(['status' => -1, 'msg' => '已转投标项目！不可再编辑！']);
            } elseif ($data['contractStatus'] == 1) {
                return $this->response->setJSON(['status' => -1, 'msg' => '已转特殊销售单！不可再编辑！']);
            } else {
                $res =$this->db->table('bill_bidquote')->where('id', $id)->update(['onModify' => 1]);
                if (!$res) {
                    return $this->response->setJSON(['status' => -1, 'msg' => '更新失败！']);
                }
                return $this->response->setJSON(['status' => 200, 'msg' => 'success', 'data' => $data]);
            }
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
    // 解除编辑中或正在报价状态
    public function confirmModify()
    {
        $id = intval($this->request->getGetPost('id'));
        $data = $this->db->table('bill_bidquote')->where(['id' => $id, 'isDelete' => 0])->get()->getRowArray();
        if ($data) {
            if ($data && $data['uId'] != $this->myerp['uId'] && !$this->myservice->verifyRight('SYS_ADMIN')) {
                log_message('error', $this->myerp['uName'] . ' 访问 Bill/InvBq/confirmModify 权限不足！');
                return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
            }
            $this->db->transStart();
            if ($this->myservice->verifyRight('SYS_ADMIN')) { //管理员可同时解除正在报价状态
                $this->db->table('bill_bidquote')->where(['id' => $id])->update(['onModify' => 0, 'quoteName' => '']);
            } else {
                $this->db->table('bill_bidquote')->where(['id' => $id])->update(['onModify' => 0]);
            }
            $this->myservice->log('解除了预算报价单的编辑中状态，单号：' . $data['billNo']);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '操作成功！']);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
    // 更新正在报价状态（主要是将自己的名字加入或移出正在报价人）
    public function upQuoteName()
    {
        if (!$this->myservice->verifyRight('BIDQUOTE_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/InvBq/upQuoteName 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $type = intval($this->request->getGetPost('type'));
        $uName = $this->myerp['uName'];
        $data = $this->db->table('bill_bidquote')->where(['id' => $id, 'isDelete' => 0])->get()->getRowArray();
        if ($data) {
            if ($data['bidStatus'] == 1) {
                return $this->response->setJSON(['status' => -1, 'msg' => '已投标，无需再报价！']);
            }
            if ($data['programStatus'] > -1) {
                return $this->response->setJSON(['status' => -1, 'msg' => '已转化投标项目，无需再报价！']);
            }
            if ($data['contractStatus'] == 1) {
                return $this->response->setJSON(['status' => -1, 'msg' => '已转化特殊销售单，无需再报价！']);
            }
            $quoteName = '';
            if ($type == 1) { //加入
                if ($data['onModify'] == 1) {
                    return $this->response->setJSON(['status' => -1, 'msg' => '单据正在被编辑中！']);
                }
                $quoteName = $data['quoteName'] == '' ? [] : explode(',', $data['quoteName']);
                $quoteName = implode(',', array_unique(array_merge($quoteName, [$uName])));
            } elseif ($type == 2) { // 移出
                $quoteName = str_replace([$uName . ',', ',' . $uName, $uName], '', $data['quoteName']);
            }
            $res = $this->db->table('bill_bidquote')->where(['id' => $id])->update(['quoteName' => $quoteName]);
            if (!$res) {
                return $this->response->setJSON(['status' => -1, 'msg' => '更新失败！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '更新成功！']);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
    // 更新投标状态
    public function upBidStatus()
    {
        $id = intval($this->request->getGetPost('id'));
        $bidStatus = intval($this->request->getGetPost('bidStatus'));
        $data = $this->db->table('bill_bidquote')->where(['id' => $id])->get()->getRowArray();
        if ($data) {
            if ($data && $data['uId'] != $this->myerp['uId'] && !$this->myservice->verifyRight('SYS_ADMIN')) {
                log_message('error', $this->myerp['uName'] . ' 访问 Bill/InvBq/upBidStatus 权限不足！');
                return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
            }
            if ($bidStatus == 0) {
                if ($data['programStatus'] > -1) {
                    return $this->response->setJSON(['status' => -1, 'msg' => '已转投标项目，不可更改投标状态！']);
                }
                if ($data['contractStatus'] > 0) {
                    return $this->response->setJSON(['status' => -1, 'msg' => '已转特殊销售单，不可更改投标状态！']);
                }
            } else {
                if ($this->db->table('bill_bidquote_info')->where(['iid' => $id, 'amount' => 0, 'isDelete' => 0])->countAllResults() > 0) {
                    return $this->response->setJSON(['status' => -1, 'msg' => '仍有产品未出最终报价，不可更改为已投标状态！']);
                }
                if ($data['bidDate'] == '0000-00-00') {
                    $info['bidDate'] = date('Y-m-d');
                    $data['bidDate'] = $info['bidDate'];
                }
            }
            $info['bidStatus'] = $bidStatus;

            $res = $this->db->table('bill_bidquote')->where(['id' => $id])->update($info);
            if (!$res) {
                return $this->response->setJSON(['status' => -1, 'msg' => '更新失败！']);
            }
            $data['bidStatus'] = $bidStatus;
            $data['id'] = intval($data['id']);
            $data['bidSiteId'] = intval($data['bidSiteId']);
            $data['goods'] = (array)json_decode($data['goods'], true);
            $data['quoteNum'] = intval($data['quoteNum']);
            $data['onModify'] = intval($data['onModify']);
            $data['modifyTimes'] = intval($data['modifyTimes']);
            $data['isDraft'] = intval($data['isDraft']);
            $data['programStatus'] = intval($data['programStatus']);
            $data['contractStatus'] = intval($data['contractStatus']);
            return $this->response->setJSON(['status' => 200, 'msg' => '更新成功！', 'data' => $data]);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }


    // 获取我的报价信息
    public function queryQuote()
    {
        if (!$this->myservice->verifyRight('BIDQUOTE_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/InvBq/queryQuote 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $data = $this->db->table('bill_bidquote')->where(['id' => $id, 'isDelete' => 0])->get()->getRowArray();
        if ($data) {
            $data['files'] = $data['files'] == '' ? [] : json_decode($data['files'], true);
            /* $sql = 'SELECT
            a.id, a.iid, a.zid, a.goodsId, a.no, a.name, a.spec, a.sku, a.brand, a.unit, a.qty, a.price, a.amount, a.buyUrl, a.description1, a.description2,
            b.price price_quote, b.amount amount_quote, b.myUrl myUrl_quote, b.description1 description1_quote, b.description2 description2_quote, b.uId uId_quote, b.uName uName_quote
            FROM bill_bidquote_info AS a
            LEFT JOIN (
                SELECT iid, zid, price, amount, myUrl, description1, description2, uId, uName FROM bill_bidquote_info_quote WHERE uId = ' . $this->myerp['uId'] . ' AND isDelete = 0
            ) AS b ON a.iid = b.iid AND a.zid = b.zid
            WHERE a.iid = ' . $id . ' ORDER BY CAST(a.zid AS UNSIGNED) asc, a.id asc'; */
            $list = $this->db->table('bill_bidquote_info AS a')
                ->select('a.id, a.iid, a.zid, a.goodsId, a.no, a.name, a.spec, a.sku, a.brand, a.unit, a.qty, a.price, a.amount, a.buyUrl, a.description1, a.description2, b.price price_quote, b.amount amount_quote, b.myUrl myUrl_quote, b.description1 description1_quote, b.description2 description2_quote, b.uId uId_quote, b.uName uName_quote')
                ->join('bill_bidquote_info_quote AS b',  'a.iid = b.iid AND a.zid = b.zid AND b.uId = ' . $this->myerp['uId'] . ' AND b.isDelete = 0', 'left')
                ->where('a.iid', $id)
                ->orderBy('CAST(a.zid AS UNSIGNED) ASC, a.id ASC')
                ->get()
                ->getResultArray();
            $v = [];
            foreach ($list as $arr => $row) {
                $v[] = [
                    'id' => intval($row['id']),
                    'iid' => intval($row['iid']),
                    'zid' => $row['zid'],
                    'goodsId' => intval($row['goodsId']),
                    'no' => $row['no'],
                    'name' => $row['name'],
                    'spec' => $row['spec'],
                    'sku' => $row['sku'],
                    'brand' => $row['brand'],
                    'unit' => $row['unit'],
                    'qty' => (float)$row['qty'],
                    'price' => $row['price_quote'] ?? 0,
                    'amount' => $row['amount_quote'] ?? 0,
                    'buyUrl' => $row['buyUrl'],
                    'myUrl' => $row['myUrl_quote'],
                    'description1' => $row['description1_quote'],
                    'description2' => $row['description2_quote']
                ];
            }
            $zids = array_column($this->db->table('bill_bidquote_info')->select('zid')->where(['iid' => $id, 'isDelete' => 0])->get()->getResultArray(), 'zid');
            $quoteZids = array_column($this->db->table('bill_bidquote_info_quote')->select('zid')->where(['iid' => $id, 'isDelete' => 0])->groupBy('zid')->get()->getResultArray(), 'zid');
            $nonQuoteZids = array_values(array_diff($zids, $quoteZids));

            $info['data'] = $data;
            $info['entries'] = $v;
            $info['zids'] = $zids;
            $info['quoteZids'] = $quoteZids;
            $info['nonQuoteZids'] = $nonQuoteZids;

            $json['status'] = 200;
            $json['msg'] = 'success';
            $json['data'] = $info;
            return $this->response->setJSON($json);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
    // 提交我的报价信息
    public function saveQuote()
    {
        if (!$this->myservice->verifyRight('BIDQUOTE_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/InvBq/saveQuote 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $data = $this->request->getPost('postData');
        if (strlen($data) > 0) {
            $data = (array)json_decode($data, true);
            $billData = $this->db->table('bill_bidquote')->where(['id' => $data['iid'], 'isDelete' => 0])->get()->getRowArray();
            if ($billData) {
                if ($billData['onModify'] == 1) {
                    return $this->response->setJSON(['status' => -1, 'msg' => '单据正在被编辑！强行暂停报价！']);
                }
            } else {
                return $this->response->setJSON(['status' => -1, 'msg' => '单据已被删除！强行停止报价！']);
            }
            // 处理容易出问题的特殊字符
            $data['zid'] = str_replace(["\r", "\n", "\r\n", '{', '}', '"', "'"], '”', $data['zid']);
            $data['name'] = str_replace(["\r", "\n", "\r\n", '{', '}', '"', "'"], '”', $data['name']);
            $data['spec'] = str_replace(["\r", "\n", "\r\n", '{', '}', '"', "'"], '”', $data['spec']);
            $data['sku'] = str_replace(["\r", "\n", "\r\n", '{', '}', '"', "'"], '”', $data['sku']);
            $data['brand'] = str_replace(["\r", "\n", "\r\n", '{', '}', '"', "'"], '”', $data['brand']);
            $data['unit'] = str_replace(["\r", "\n", "\r\n", '{', '}', '"', "'"], '”', $data['unit']);
            $data['myUrl'] = shop_url_parse($data['myUrl']);
            $data['description1'] = str_replace(["\r", "\n", "\r\n", '{', '}', '"', "'"], '”', $data['description1']);
            $data['description2'] = str_replace(["\r", "\n", "\r\n", '{', '}', '"', "'"], '”', $data['description2']);
            $data['price'] = round($data['price'], 5);
            $data['amount'] = round($data['amount'], 2);
            $this->db->transStart();
            // 有则更新 无则插入
            if ($this->db->table('bill_bidquote_info_quote')->where(['iid' => $data['iid'], 'zid' => $data['zid'], 'uId' => $data['uId']])->countAllResults() > 0) {
                $this->db->table('bill_bidquote_info_quote')->where(['iid' => $data['iid'], 'zid' => $data['zid'], 'uId' => $data['uId']])->update($data);
            } else {
                $data['billNo'] = $billData['billNo'];
                $data['firstTime'] = $data['lastTime'] = date('Y-m-d H:i:s');
                $this->db->table('bill_bidquote_info_quote')->insert($data);
            }
            $quoteNum = $this->db->table('bill_bidquote_info_quote')->select('uId')->where(['iid' => $data['iid'], 'isDelete' => 0])->groupBy('uId')->countAllResults();
            $this->db->table('bill_bidquote')->where('id', $data['iid'])->update(['quoteNum' => $quoteNum]);
            $this->myservice->log('对预算报价单号为 ' . $billData['billNo'] . ' 中自编序号为 ' . $data['zid'] . ' 的产品进行了报价，所报单价：' . $data['price'] . '，所报总价：' . $data['amount']);
            $zids = array_column($this->db->table('bill_bidquote_info')->select('zid')->where(['iid' => $data['iid'], 'isDelete' => 0])->get()->getResultArray(), 'zid');
            $quoteZids = array_column($this->db->table('bill_bidquote_info_quote')->select('zid')->where(['iid' => $data['iid'], 'isDelete' => 0])->groupBy('zid')->get()->getResultArray(), 'zid');
            $nonQuoteZids = array_values(array_diff($zids, $quoteZids));
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '操作成功！', 'data' => ['myUrl' => $data['myUrl'], 'zids' => $zids, 'quoteZids' => $quoteZids, 'nonQuoteZids' => $nonQuoteZids]]);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '提交的是空数据！']);
    }
    // 更新报价信息被选状态
    public function updateQuoteInfoStatus()
    {
        if (!$this->myservice->verifyRight('BIDQUOTE_UPDATE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/InvBq/updateQuoteInfoStatus 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $status = intval($this->request->getGetPost('status'));
        $data = $this->db->table('bill_bidquote_info_quote')->where(['id' => $id])->get()->getRowArray();
        if ($data) {
            $this->db->transStart();
            if ($status == 1) {
                $this->db->table('bill_bidquote_info_quote')->where(['iid' => $data['iid'], 'zid' => $data['zid'], 'id !=' => $id])->update(['status' => 0]);
                $this->db->table('bill_bidquote_info_quote')->where(['id' => $id])->update(['status' => 1]);
            } else {
                $this->db->table('bill_bidquote_info_quote')->where(['id' => $id])->update(['status' => 0]);
            }
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 1, 'msg' => '操作成功！']);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
    // 重置报价信息被选状态
    public function resetQuoteInfoStatus()
    {
        if (!$this->myservice->verifyRight('BIDQUOTE_UPDATE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/InvBq/resetQuoteInfoStatus 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        if ($id > 0) {
            $res = $this->db->table('bill_bidquote_info_quote')->where(['iid' => $id])->update(['status' => 0]);
            if (!$res) {
                return $this->response->setJSON(['status' => -1, 'msg' => '操作失败！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '操作成功！']);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
}
