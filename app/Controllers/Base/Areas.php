<?php

namespace App\Controllers\Base;
use App\Controllers\BaseController;

class Areas extends BaseController
{
    // 远程同步快递时效（从快递网) 初始建表时用
    public function syncDeliverTime()
    {
        if (!$this->myservice->verifyRight('AREAS_ADVANCE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/Areas/syncDeliverTime 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        set_time_limit(0);
        session_write_close();
        //快递网api地址
        $url = 'http://www.kuaidi.com/orderindex-timeout.html';
        //获取省级
        $provinces = $this->db->table('areas')->where(['type' => 2, 'isDelete' => 0])->get()->getResultArray();
        $this->db->transStart();
        foreach ($provinces as $k1 => $v1) {
            //获取地级
            $citys = $this->db->table('areas')->where(['parentId' => $v1['id'], 'type' => 3, 'isDelete' => 0])->get()->getResultArray();
            foreach ($citys as $k2 => $v2) {
                //同步地级
                $res = global_curl($url, [
                    'post' => 1,
                    'postfields' => 'placefrom=%E9%87%8D%E5%BA%86-%E9%87%8D%E5%BA%86%E5%B8%82-%E4%B8%87%E5%B7%9E%E5%8C%BA&placeto=' . urlencode($v1['name'] . '-' . $v2['name']),
                    'httpheader' => [
                        'Accept: */*',
                        'Accept-Language: zh-CN,zh;q=0.9,en;q=0.8,en-GB;q=0.7,en-US;q=0.6',
                        'Cache-Control: no-cache',
                        'Connection: keep-alive',
                        'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
                        'Host: www.kuaidi.com',
                        'Origin: http://www.kuaidi.com',
                        'Pragma: no-cache',
                        'Referer: http://www.kuaidi.com/shixiaoindex.html',
                        'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/118.0.0.0 Safari/537.36 Edg/118.0.2088.46',
                        'X-Requested-With: XMLHttpRequest'
                    ]
                ]);
                if (false !== $res) {
                    $obj = json_decode($res, true);
                    $arr = $obj['data'] ?? [];
                    if (is_array($arr) && count($arr) > 0) {
                        $deliverTimeArr = [];
                        foreach ($arr as $a => $r) {
                            $r['exname'] == 'shunfeng' && $deliverTimeArr['deliverTimeSF'] = $r['timeavg'];
                            $r['exname'] == 'youzhengguonei' && $deliverTimeArr['deliverTimeYZ'] = $r['timeavg'];
                            $r['exname'] == 'shentong' && $deliverTimeArr['deliverTimeST'] = $r['timeavg'];
                            $r['exname'] == 'yuantong' && $deliverTimeArr['deliverTimeYT'] = $r['timeavg'];
                            $r['exname'] == 'huitongkuaidi' && $deliverTimeArr['deliverTimeBS'] = $r['timeavg'];
                        }
                        count($deliverTimeArr) > 0 && $this->db->table('areas')->where('id', $v2['id'])->update($deliverTimeArr);
                    } else {
                        log_message('error', '全国地区快递时效同步www.kuaidi.com：未匹配到数据 -> ' . $v1['name'] . '-' . $v2['name']);
                    }
                } else {
                    log_message('error', '全国地区快递时效同步www.kuaidi.com：未获取到api数据 -> ' . $v1['name'] . '-' . $v2['name']);
                }
                sleep(1);
            }
            sleep(1);
        }
        $this->myservice->log('远程同步了全国地区的地级快递时效（从www.kuaidi.com）');
        $this->db->transComplete();
        if ($this->db->transStatus() === false) {
            return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
        }
        return $this->response->setJSON(['status' => 200, 'msg' => 'success']);
    }

    // 一键填充省级、县级快递时效（使用同省地级最大时效、地级时效）
    public function fillTimeFromCity()
    {
        if (!$this->myservice->verifyRight('AREAS_ADVANCE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/Areas/fillTimeFromCity 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        //将同省下面所有地级的最大时效填充给省级
        $provinces = $this->db->table('areas')->where(['type' => 2, 'isDelete' => 0])->get()->getResultArray();
        $this->db->transStart();
        foreach ($provinces as $k1 => $v1) {
            $maxCityTimeArr = $this->db->table('areas')->select('max(deliverTimeSF) AS deliverTimeSF, max(deliverTimeYZ) AS deliverTimeYZ, max(deliverTimeJD) AS deliverTimeJD, max(deliverTimeST) AS deliverTimeST, max(deliverTimeYT) AS deliverTimeYT, max(deliverTimeBS) AS deliverTimeBS, max(deliverTimeJDZH) AS deliverTimeJDZH')->where('parentId', $v1['id'])->get()->getRowArray();
            $this->db->table('areas')->where('id', $v1['id'])->update($maxCityTimeArr);
        }
        //将地级时效填充给下面的县级
        $citys = $this->db->table('areas')->where(['type' => 3, 'isDelete' => 0])->get()->getResultArray();
        foreach ($citys as $k2 => $v2) {
            $this->db->table('areas')->where('parentId', $v2['id'])->update([
                'deliverTimeSF' => $v2['deliverTimeSF'],
                'deliverTimeYZ' => $v2['deliverTimeYZ'],
                'deliverTimeJD' => $v2['deliverTimeJD'],
                'deliverTimeST' => $v2['deliverTimeST'],
                'deliverTimeYT' => $v2['deliverTimeYT'],
                'deliverTimeBS' => $v2['deliverTimeBS'],
                'deliverTimeJDZH' => $v2['deliverTimeJDZH']
            ]);
        }
        $this->myservice->log('一键填充了全国地区的省级、县级快递时效');
        $this->db->transComplete();
        if ($this->db->transStatus() === false) {
            return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
        }
        return $this->response->setJSON(['status' => 200, 'msg' => 'success']);
    }

    // 全国地区列表
    public function list()
    {
        $page = max(intval($this->request->getGetPost('page')), 1);
        $rows = max(intval($this->request->getGetPost('rows')), 100);
        $sidx = $this->request->getGetPost('sidx');
        $sord = strtoupper((string)$this->request->getGetPost('sord')) === 'ASC' ? 'ASC' : 'DESC';
        $province = intval($this->request->getGetPost('province'));
        $city = intval($this->request->getGetPost('city'));
        $area = intval($this->request->getGetPost('area'));
        $skey = $this->request->getGetPost('skey');
        $builder = $this->db->table('areas')->where(['type >' => 1, 'isDelete' => 0]);
        if ($area > 0) {
           $builder->where('parentId',  $area);
        } else {
            if ($city > 0) {
                $builder->where('parentId', $city);
            } else {
                if ($province > 0) {
                    $builder->where('parentId', $province);
                }
            }
        }
        if ($skey !== '' && $skey !== null) {
            $keywords = array_values(array_filter(explode(',', $skey), 'strlen'));
            foreach ($keywords as $kw) {
                $builder->like('name', $this->db->escapeLikeString($kw));
            }
        }
        // 允许排序的字段白名单，杜绝 order by 注入
        $allowedSort = ['id','name','type','deliverTimeSF','deliverTimeYZ','deliverTimeJD','deliverTimeST','deliverTimeYT','deliverTimeBS','deliverTimeJDZH'];
        $orderField  = $sidx ? (in_array($sidx, $allowedSort, true) ? $sidx : 'id') : 'id';
        $list = (clone $builder)->orderBy($orderField, $sord)->limit($rows)->offset($rows * ($page - 1))->get()->getResultArray();
        $total = $builder->countAllResults();
        $v = [];
        $parents = array_column($this->db->table('areas')->select('id, name')->where('type <', 4)->get()->getResultArray(), 'name', 'id');
        foreach ($list as $arr => $row) {
            $v[$arr]['id'] = intval($row['id']);
            $v[$arr]['name'] = $row['name'];
            $v[$arr]['parentId'] = intval($row['parentId']);
            $v[$arr]['parentName'] = $parents[$row['parentId']];
            $v[$arr]['type'] = intval($row['type']);
            $v[$arr]['deliverTimeSF'] = (float)$row['deliverTimeSF'];
            $v[$arr]['deliverTimeYZ'] = (float)$row['deliverTimeYZ'];
            $v[$arr]['deliverTimeJD'] = (float)$row['deliverTimeJD'];
            $v[$arr]['deliverTimeST'] = (float)$row['deliverTimeST'];
            $v[$arr]['deliverTimeYT'] = (float)$row['deliverTimeYT'];
            $v[$arr]['deliverTimeBS'] = (float)$row['deliverTimeBS'];
            $v[$arr]['deliverTimeJDZH'] = (float)$row['deliverTimeJDZH'];
        }
        $json['status'] = 200;
        $json['msg'] = 'success';
        $json['data']['page'] = $page;
        $json['data']['records'] = $total;
        $json['data']['total'] = ceil($json['data']['records'] / $rows);
        $json['data']['rows'] = $v;
        return $this->response->setJSON($json);
    }

    // 全国地区更新
    public function update()
    {
        if (!$this->myservice->verifyRight('AREAS_ADVANCE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/Areas/update 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $name = $this->request->getGetPost('name');
        $deliverTimeSF = (float)$this->request->getGetPost('deliverTimeSF');
        $deliverTimeYZ = (float)$this->request->getGetPost('deliverTimeYZ');
        $deliverTimeJD = (float)$this->request->getGetPost('deliverTimeJD');
        $deliverTimeST = (float)$this->request->getGetPost('deliverTimeST');
        $deliverTimeYT = (float)$this->request->getGetPost('deliverTimeYT');
        $deliverTimeBS = (float)$this->request->getGetPost('deliverTimeBS');
        $deliverTimeJDZH = (float)$this->request->getGetPost('deliverTimeJDZH');
        if ($id > 0) {
            $this->db->transStart();
            $this->db->table('areas')->where('id', $id)->update(['deliverTimeSF' => $deliverTimeSF, 'deliverTimeYZ' => $deliverTimeYZ, 'deliverTimeST' => $deliverTimeST, 'deliverTimeYT' => $deliverTimeYT, 'deliverTimeJD' => $deliverTimeJD, 'deliverTimeBS' => $deliverTimeBS, 'deliverTimeJDZH' => $deliverTimeJDZH]);
            $this->myservice->log('更新了全国地区中的：' . $name . ' 的相关信息');
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '更新成功！']);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '提交的是空数据！']);
    }
}
