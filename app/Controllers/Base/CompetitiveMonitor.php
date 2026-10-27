<?php

namespace App\Controllers\Base;
use App\Controllers\BaseController;

class CompetitiveMonitor extends BaseController
{
    //竞品监控 自有列表
    public function list()
    {
        if (!$this->myservice->verifyRight('COMPETITIVE_MONITOR_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/CompetitiveMonitor/list 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $page = max(intval($this->request->getGetPost('page')), 1);
        $rows = max(intval($this->request->getGetPost('rows')), 10);
        $sidx = $this->request->getGetPost('sidx');
        $sord = strtoupper((string)$this->request->getGetPost('sord')) === 'ASC' ? 'ASC' : 'DESC';
        $status1 = intval($this->request->getGetPost('status1'));
        $status2 = $this->request->getGetPost('status2');
        $status3 = $this->request->getGetPost('status3');
        $skey = $this->request->getGetPost('skey');
        $builder = $this->db->table('competitive_monitor');
        $builder->where('type', 0)->where('isDelete', 0);
        if ($status1 >= 0) {
            if ($status1 == 2) { // 链接无效
                $ids_1 = array_column($this->db->table('competitive_monitor')->select('id')->where(['status' => 2, 'type' => 0, 'isDelete' => 0])->get()->getResultArray(), 'id');
                $ids_2 = array_column($this->db->table('competitive_monitor')->select('iid')->where(['status' => 2, 'type' => 1, 'isDelete' => 0])->get()->getResultArray(), 'iid');
                $ids = array_unique(array_merge($ids_1, $ids_2));
                if (count($ids) > 0) {
                    $builder->whereIn('id', $ids);
                } else {
                    $builder->where('id', -1);
                }
            } else {
                $builder->where('status', $status1);
            }
        }
        if ($status2) {
            $builder->where('shopName', $status2);
        }
        if ($status3) {
            $builder->where('uName', $status3);
        }
        if ($skey !== '' && $skey !== null) {
            $keywords = array_values(array_filter(explode(',', $skey), 'strlen'));
            foreach ($keywords as $kw) {
                $builder->like('goodsText', $this->db->escapeLikeString($kw));
            }
        }
        $allowedSort = ['id','goodsId','goodsText','type','shopType','url','price1','price2','discountsInfo','competitiveInfo','status','uId','uName','createTime','modifyTime','lastSpiderTime'];
        $orderField  = $sidx ? (in_array($sidx, $allowedSort, true) ? $sidx : 'id') : 'id';
        $list = (clone $builder)->orderBy($orderField, $sord)->limit($rows)->offset($rows * ($page - 1))->get()->getResultArray();
        $total = $builder->countAllResults();
        $v = [];
        foreach ($list as $arr => $row) {
            $v[$arr]['id'] = intval($row['id']);
            $v[$arr]['goodsId'] = intval($row['goodsId']);
            $v[$arr]['goodsText'] = $row['goodsText'];
            $v[$arr]['type'] = intval($row['type']);
            $v[$arr]['shopType'] = $row['shopType'];
            $v[$arr]['shopName'] = $row['shopName'];
            $v[$arr]['url'] = $row['url'];
            $v[$arr]['price1'] = (float)$row['price1'];
            $v[$arr]['price2'] = (float)$row['price2'];
            $v[$arr]['discountsInfo'] = $row['discountsInfo'];
            $v[$arr]['competitiveInfo'] = $this->db->table('competitive_monitor')->where(['iid' => intval($row['id']), 'type' => 1, 'isDelete' => 0])->get()->getResultArray();
            $v[$arr]['status'] = intval($row['status']);
            $v[$arr]['uId'] = intval($row['uId']);
            $v[$arr]['uName'] = $row['uName'];
            $v[$arr]['createTime'] = $row['createTime'] == '0000-00-00 00:00:00' ? '' : $row['createTime'];
            $v[$arr]['modifyTime'] = $row['modifyTime'] == '0000-00-00 00:00:00' ? '' : $row['modifyTime'];
            $v[$arr]['lastSpiderTime'] = $row['lastSpiderTime'] == '0000-00-00 00:00:00' ? '' : $row['lastSpiderTime'];
        }
        $json['status'] = 200;
        $json['msg'] = 'success';
        $json['data']['page'] = $page;
        $json['data']['records'] = $total;
        $json['data']['total'] = ceil($json['data']['records'] / $rows);
        $json['data']['rows'] = $v;
        return $this->response->setJSON($json);
    }
    //竞品监控 竞品列表
    public function get_list()
    {
        if (!$this->myservice->verifyRight('COMPETITIVE_MONITOR_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/CompetitiveMonitor/get_list 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $page = max(intval($this->request->getGetPost('page')), 1);
        $rows = max(intval($this->request->getGetPost('rows')), 100);
        $sidx = $this->request->getGetPost('sidx');
        $sord = strtoupper((string)$this->request->getGetPost('sord')) === 'ASC' ? 'ASC' : 'DESC';
        $id = intval($this->request->getGetPost('id'));
        $builder = $this->db->table('competitive_monitor')->where(['iid' => $id, 'type' => 1, 'isDelete' => 0]);
        $allowedSort = ['id','iid','goodsId','title','type','shopType','shopName','url','price1','price2','discountsInfo','status','uId','uName','createTime','modifyTime','lastSpiderTime'];
        $orderField  = $sidx ? (in_array($sidx, $allowedSort, true) ? $sidx : 'id') : 'id';
        $list = $builder->orderBy($orderField . ' ' . $sord)->limit($rows)->offset($rows * ($page - 1))->get()->getResultArray();
        $total = $builder->countAllResults();
        $v = [];
        foreach ($list as $arr => $row) {
            $v[$arr]['id'] = intval($row['id']);
            $v[$arr]['iid'] = intval($row['iid']);
            $v[$arr]['goodsId'] = intval($row['goodsId']);
            $v[$arr]['title'] = $row['title'];
            $v[$arr]['type'] = intval($row['type']);
            $v[$arr]['shopType'] = $row['shopType'];
            $v[$arr]['shopName'] = $row['shopName'];
            $v[$arr]['url'] = $row['url'];
            $v[$arr]['price1'] = (float)$row['price1'];
            $v[$arr]['price2'] = (float)$row['price2'];
            $v[$arr]['discountsInfo'] = $row['discountsInfo'];
            $v[$arr]['status'] = intval($row['status']);
            $v[$arr]['uId'] = intval($row['uId']);
            $v[$arr]['uName'] = $row['uName'];
            $v[$arr]['createTime'] = $row['createTime'] == '0000-00-00 00:00:00' ? '' : $row['createTime'];
            $v[$arr]['modifyTime'] = $row['modifyTime'] == '0000-00-00 00:00:00' ? '' : $row['modifyTime'];
            $v[$arr]['lastSpiderTime'] = $row['lastSpiderTime'] == '0000-00-00 00:00:00' ? '' : $row['lastSpiderTime'];
        }
        $json['status'] = 200;
        $json['msg'] = 'success';
        $json['data']['page'] = $page;
        $json['data']['records'] = $total;
        $json['data']['total'] = ceil($json['data']['records'] / $rows);
        $json['data']['rows'] = $v;
        return $this->response->setJSON($json);
    }
    //竞品监控 获取信息
    public function query()
    {
        if (!$this->myservice->verifyRight('COMPETITIVE_MONITOR_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/CompetitiveMonitor/query 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $data = $this->db->table('competitive_monitor')->where(['id' => $id, 'type' => 0, 'isDelete' => 0])->get()->getRowArray();
        if ($data) {
            $info['id'] = $id;
            $info['goodsId'] = intval($data['goodsId']);
            $info['goodsText'] = $data['goodsText'];
            $info['type'] = intval($data['type']);
            $info['shopType'] = $data['shopType'];
            $info['shopName'] = $data['shopName'];
            $info['url'] = $data['url'];
            $info['price1'] = (float)$data['price1'];
            $info['price2'] = (float)$data['price2'];
            $info['discountsInfo'] = $data['discountsInfo'];
            $info['competitiveInfo'] = $this->db->table('competitive_monitor')->where(['iid' => intval($info['id']), 'type' => 1, 'isDelete' => 0])->get()->getResultArray();
            $info['status'] = intval($data['status']);
            $info['uId'] = intval($data['uId']);
            $info['uName'] = $data['uName'];
            $info['createTime'] = $data['createTime'] == '0000-00-00 00:00:00' ? '' : $data['createTime'];
            $info['modifyTime'] = $data['modifyTime'] == '0000-00-00 00:00:00' ? '' : $data['modifyTime'];
            $info['lastSpiderTime'] = $data['lastSpiderTime'] == '0000-00-00 00:00:00' ? '' : $data['lastSpiderTime'];

            $json['status'] = 200;
            $json['msg'] = 'success';
            $json['data'] = $info;
            return $this->response->setJSON($json);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
    //竞品监控 新增
    public function add()
    {
        if (!$this->myservice->verifyRight('COMPETITIVE_MONITOR_ADVANCE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/CompetitiveMonitor/add 权限不足！');
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
            //处理自家产品
            $this->db->table('competitive_monitor')->insert([
                'goodsId' => $data['goodsId'],
                'goodsText' => $data['goodsText'],
                'type' => 0,
                'url' => $data['url'],
                'shopType' => $data['shopType'],
                'uId' => $data['uId'],
                'uName' => $data['uName'],
                'createTime' => $data['createTime'],
                'modifyTime' => $data['modifyTime']
            ]);
            $selfId = $this->db->insertID();
            //发送到WS服务端
            if ($data['monitorType'] == 0) {
                $this->wsclient($selfId);
            }
            //处理竞品
            if (isset($data['competitiveInfo']) && is_countable($data['competitiveInfo']) && count($data['competitiveInfo']) > 0) {
                foreach ($data['competitiveInfo'] as $k => $v) {
                    $this->db->table('competitive_monitor')->insert([
                        'iid' => $selfId,
                        'goodsId' => $data['goodsId'],
                        'type' => 1,
                        'url' => $v['url'],
                        'shopType' => $v['shopType'],
                        'uId' => $data['uId'],
                        'uName' => $data['uName'],
                        'createTime' => $data['createTime'],
                        'modifyTime' => $data['modifyTime']
                    ]);
                    $otherId = $this->db->insertID();
                    //发送到WS服务端
                    if ($data['monitorType'] == 0) {
                        $this->wsclient($otherId);
                    }
                }
            }
            $this->myservice->log('新增了竞品监控，监控ID：' . $selfId . '，监控产品：' . $data['goodsText']);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '新增成功！', 'data' => $data]);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '提交的是空数据！']);
    }
    //竞品监控 编辑
    public function update()
    {
        if (!$this->myservice->verifyRight('COMPETITIVE_MONITOR_ADVANCE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/CompetitiveMonitor/update 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $data = $this->request->getPost();
        if (is_array($data) && count($data) > 0) {
            $data = $this->validform($data);
            // 如果返回的是 Response 对象，直接返回它
            if ($data instanceof \CodeIgniter\HTTP\Response) {
                return $data;
            }
            $oldData = $this->db->table('competitive_monitor')->where(['id' => $data['id']])->get()->getRowArray();
            $this->db->transStart();
            // 处理自家产品
            $this->db->table('competitive_monitor')->where('id', $data['id'])->update([
                'goodsId' => $data['goodsId'],
                'goodsText' => $data['goodsText'],
                'url' => $data['url'],
                'shopType' => $data['shopType'],
                'modifyTime' => $data['modifyTime']
            ]);
            // 发送到WS服务端
            if ($data['monitorType'] == 0) {
                $this->wsclient($data['id']);
            }
            // 处理竞品
            if (isset($data['competitiveInfo']) && is_countable($data['competitiveInfo']) && count($data['competitiveInfo']) > 0) {
                // 处理被删除了的竞品条目
                $oldIdsArr = array_column($this->db->table('competitive_monitor')->select('id')->where('iid', $data['id'])->get()->getResultArray(), 'id');
                $newIdsArr = array_column($data['competitiveInfo'], 'id');
                $notExitsIdsArr = array_diff($oldIdsArr, $newIdsArr);
                if (count($notExitsIdsArr) > 0) {
                    $this->db->table('competitive_monitor')->whereIn('id', $notExitsIdsArr)->update(['isDelete' => 1]);
                }
                // 处理新的竞品条目
                foreach ($data['competitiveInfo'] as $k => $v) {
                    if ($v['id'] > 0) {
                        $id = $v['id'];
                        $this->db->table('competitive_monitor')->where('id', $id)->update([
                            'goodsId' => $data['goodsId'],
                            'url' => $v['url'],
                            'shopType' => $v['shopType'],
                            'modifyTime' => $data['modifyTime']
                        ]);
                    } else {
                        $this->db->table('competitive_monitor')->insert([
                            'iid' => $oldData['id'],
                            'goodsId' => $data['goodsId'],
                            'type' => 1,
                            'url' => $v['url'],
                            'shopType' => $v['shopType'],
                            'uId' => $data['uId'],
                            'uName' => $data['uName'],
                            'createTime' => $data['createTime'],
                            'modifyTime' => $data['modifyTime']
                        ]);
                        $id = $this->db->insertID();
                    }
                    // 发送到WS服务端
                    if ($data['monitorType'] == 0) {
                        $this->wsclient($id);
                    }
                }
            }
            $this->myservice->log('编辑了竞品监控，监控ID：' . $data['id'] . '，监控产品：' . $data['goodsText']);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '编辑成功！', 'data' => $data]);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '提交的是空数据！']);
    }
    // 竞品监控 删除
    public function delete()
    {
        if (!$this->myservice->verifyRight('COMPETITIVE_MONITOR_ADVANCE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/CompetitiveMonitor/delete 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $data = $this->db->table('competitive_monitor')->where('id', $id)->get()->getRowArray();
        if ($data) {
            $this->db->transStart();
            $this->db->table('competitive_monitor')->where('id', $data['id'])->update(['isDelete' => 1]);
            $this->db->table('competitive_monitor')->where('iid', $data['id'])->update(['isDelete' => 1]);
            $this->myservice->log('删除了竞品监控，监控ID：' . $data['id'] . '，监控产品：' . $data['goodsText']);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '删除成功！']);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
    //竞品监控 数据验证
    private function validform(array $data) {
        $data['id'] = isset($data['id']) ? intval($data['id']) : 0;
        $data['goodsId'] = isset($data['goodsId']) ? intval($data['goodsId']) : 0;
        if ($data['goodsId'] < 1) {
            return $this->response->setJSON(['status' => -1, 'msg' => '自家产品不能为空！']);
        }
        if (strlen($data['goodsText']) < 1) {
            return $this->response->setJSON(['status' => -1, 'msg' => '自家产品不能为空！']);
        }
        if (strlen($data['url']) < 1) {
            return $this->response->setJSON(['status' => -1, 'msg' => '自家链接不能为空！']);
        }
        //规范重组URL
        $pUrl = parse_url($data['url']);
        if (isset($pUrl['host']) && ($pUrl['host'] == "item.taobao.com" || $pUrl['host'] == "detail.tmall.com") && isset($pUrl['path']) && $pUrl['path'] == "/item.htm" && isset($pUrl['query'])) {
            parse_str($pUrl['query'], $params);
            $data['url'] = 'https://'.$pUrl['host'].'/item.htm?'.(isset($params['id']) ? ('id='.$params['id']) : '') .(isset($params['skuId']) ? ('&skuId='.$params['skuId']) : '');
            if ($this->db->table('competitive_monitor')->where('url', $data['url'])->where('isDelete', 0)->where('id !=', $data['id'])->countAllResults() > 0) {
                return $this->response->setJSON(['status' => -1, 'msg' => '自家链接已经被监控中！']);
            }
        } else {
            return $this->response->setJSON(['status' => -1, 'msg' => '自家链接格式错误！需是淘宝商品详情页具体套餐链接！']);
        }
        $data['shopType'] = strpos($data['url'], 'taobao.com') !== false ? '淘宝' : (strpos($data['url'], 'tmall.com') !== false ? '天猫' : '未知');
        if (isset($data['competitiveInfo']) && is_countable($data['competitiveInfo']) && count($data['competitiveInfo']) > 0) {
            foreach ($data['competitiveInfo'] as $k => $v) {
                //规范重组URL
                $pUrl = parse_url($v['url']);
                if (isset($pUrl['host']) && ($pUrl['host'] == "item.taobao.com" || $pUrl['host'] == "detail.tmall.com") && isset($pUrl['path']) && $pUrl['path'] == "/item.htm" && isset($pUrl['query'])) {
                    parse_str($pUrl['query'], $params);
                    $data['competitiveInfo'][$k]['url'] = 'https://'.$pUrl['host'].'/item.htm?'.(isset($params['id']) ? ('id='.$params['id']) : '') .(isset($params['skuId']) ? ('&skuId='.$params['skuId']) : '');
                    if ($this->db->table('competitive_monitor')->where('url', $data['competitiveInfo'][$k]['url'])->where('isDelete', 0)->where('id !=', $data['competitiveInfo'][$k]['id'])->countAllResults() > 0) {
                        return $this->response->setJSON(['status' => -1, 'msg' => '第'.($k + 1).'条竞品链接已经被监控中！']);
                    }
                } else {
                    return $this->response->setJSON(['status' => -1, 'msg' => '竞品链接格式错误！需是淘宝商品详情页具体套餐链接！']);
                }
                $data['competitiveInfo'][$k]['shopType'] = strpos($data['competitiveInfo'][$k]['url'], 'taobao.com') !== false ? '淘宝' : (strpos($data['competitiveInfo'][$k]['url'], 'tmall.com') !== false ? '天猫' : '未知');
            }
        }
        $data['uId'] = $data['uId'] ?? $this->myerp['uId'];
        $data['uName'] = $data['uName'] ?? $this->myerp['uName'];
        $data['createTime'] = $data['modifyTime'] = date('Y-m-d H:i:s');
        return $data;
    }

    // 获取所有自家店铺
    public function queryAllSelfShopName()
    {
        if (!$this->myservice->verifyRight('COMPETITIVE_MONITOR_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/CompetitiveMonitor/queryAllSelfShopName 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $list = $this->db->table('competitive_monitor')->select('shopName')->where(['type' => 0, 'isDelete' => 0])->groupBy('shopName')->get()->getResultArray();
        $v = [];
        foreach ($list as $arr => $row) {
            $v[$arr]['id'] = intval($arr);
            $v[$arr]['name'] = $row['shopName'];
        }
        $json['status'] = 200;
        $json['msg'] = 'success';
        $json['data']['rows'] = $v;
        return $this->response->setJSON($json);
}

    //websoket客户端 往websoket服务器发送监控插队请求
    public function wsclient($data = '') {
        if (!$this->myservice->verifyRight('COMPETITIVE_MONITOR_ADVANCE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/CompetitiveMonitor/wsclient 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        //使用错误控制运算符 @ 来抑制错误消息的显示，但不建议这样做，因为它会隐藏重要的错误信息。
        $fp = @stream_socket_client('tcp://127.0.0.1:8081', $errno, $errmsg, 1);
        if ($fp) {
            // 推送数据，注意端口是Text协议，需要在数据末尾加上换行符
            fwrite($fp, 'taobaoReq=>'.$data."\r\n");
            fclose($fp);
        } else {
            log_message('error', "stream_socket_clien ERROR: $errno - $errmsg");
        }
    }
}
