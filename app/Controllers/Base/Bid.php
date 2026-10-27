<?php

namespace App\Controllers\Base;
use App\Controllers\BaseController;

class Bid extends BaseController
{
    // 招标信息 列表
    public function list()
    {
        if (!$this->myservice->verifyRight('BIDINFO_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/Bid/list 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $page = max(intval($this->request->getGetPost('page')), 1);
        $rows = max(intval($this->request->getGetPost('rows')), 100);
        $sidx = $this->request->getGetPost('sidx');
        $sord = strtoupper((string)$this->request->getGetPost('sord')) === 'ASC' ? 'ASC' : 'DESC';
        $siteType = intval($this->request->getGetPost('siteType'));
        $status = intval($this->request->getGetPost('status'));
        $match = intval($this->request->getGetPost('match'));
        $beginDate = $this->request->getGetPost('beginDate');
        $endDate = $this->request->getGetPost('endDate');
        $skey = $this->request->getGetPost('skey');
        $now = date('Y-m-d H:i:s');
        $beginDate = $beginDate > $now ? $now : $beginDate;
        $endDate = $endDate > $now ? $now : $endDate;
        // 既无管理员权限、也无高级用户权限，就依最近投标排名先后，依次延迟一分钟显示，最大延迟5分钟。
        if (!in_array('SYS_ADMIN', $this->myerp['rights']) && !in_array('SYS_ADVANCE', $this->myerp['rights'])) {
            $bidRank = $this->db->table('user_rank')->select('rank')->where(['uName' => $this->myerp['uName'], 'name' => 'bidRank'])->get()->getRowArray()['rank'] ?? 0;
            if (intval($bidRank) > 0) {
                $beginDate = date('Y-m-d H:i:s', strtotime($beginDate . ' - ' . min((intval($bidRank) - 1) * 1, 5) . ' minute'));
                $endDate = date('Y-m-d H:i:s', strtotime($endDate . ' - ' . min((intval($bidRank) - 1) * 1, 5) . ' minute'));
            } else {
                $beginDate = date('Y-m-d H:i:s', strtotime($beginDate . ' - 5 minute'));
                $endDate = date('Y-m-d H:i:s', strtotime($endDate . ' - 5 minute'));
            }
        }
        $builder = $this->db->table('bid_info')->where('inDate >=', $beginDate)->where('inDate <=', $endDate)->where('isDelete', 0);
        if ($siteType > -1) {
            $siteIdsArr = array_column($this->db->table('bid_site')->select('id')->where(['siteType' => $siteType, 'isDelete' => 0])->orderBy('id', 'DESC')->get()->getResultArray(), 'id');
            if (is_array($siteIdsArr) && count($siteIdsArr) > 0) {
                $builder->whereIn('bidSiteId', $siteIdsArr);
            }
        }
        if ($status > -1) {
            if ($status == 0) {
                $builder->groupStart()->where('FIND_IN_SET(' . $this->db->escape($this->myerp['uName']) . ', joinName) >', 0, false)->where('FIND_IN_SET(' . $this->db->escape($this->myerp['uName']) . ', joinHis) >', 0, false)->groupEnd();
            } elseif ($status == 1) {
                $builder->where('quoteId >', 0);
            }
        }
        // 处理搜索关键词【与其他搜索不同，此处将所有关键词打散为单个字符后进行统一匹配】
        if ($skey !== '' && $skey !== null) {
            // 记录热搜词
            $hotWord = preg_replace('/[^\x{4e00}-\x{9fa5}\dA-Za-z]/u', '', $skey);
            if (strlen($hotWord) <= 20) {
                if ($this->db->table('bid_hotword')->where('name', $hotWord)->countAllResults() > 0) {
                    $this->db->table('bid_hotword')->where('name', $hotWord)->set('searchCount', 'searchCount + 1', false)->update();
                } else {
                    $this->db->table('bid_hotword')->insert(['name' => $hotWord, 'searchCount' => 1]);
                }
            }
            $keywords = array_values(array_filter(explode(',', $skey), 'strlen'));
            if ($match == 1) { // 精确查询
                foreach ($keywords as $kw) {
                    $builder->groupStart()
                        ->like('bidSiteName', $this->db->escapeLikeString($kw))
                        ->orLike('bidTitle', $this->db->escapeLikeString($kw))
                        ->orLike('bidUrl', $this->db->escapeLikeString($kw))
                        ->orLike('joinName', $this->db->escapeLikeString($kw))
                        ->orLike('joinHis', $this->db->escapeLikeString($kw))
                        ->orLike('leaveReasonHis', $this->db->escapeLikeString($kw))
                        ->orLike('quoteNo', $this->db->escapeLikeString($kw))
                    ->groupEnd();
                }
            } else { // 模糊查询
                $regexpWord = mb_str_split($hotWord);
                foreach ($regexpWord as $kw) {
                    $builder->where('CONCAT(bidSiteName,bidTitle,bidUrl,joinName,joinHis,leaveReasonHis,quoteNo) REGEXP "' . $kw . '"', null, false);
                }
            }
        }
        $builder->orderBy('joinStatus', 'DESC');
        // 允许排序的字段白名单，杜绝 order by 注入
        $allowedSort = ['id','bidSiteName','bidTitle','beginDate','inDate','joinHis','leaveReasonHis','quoteId'];
        $orderField  = $sidx ? (in_array($sidx, $allowedSort, true) ? $sidx : 'id') : 'id';
        $list = (clone $builder)->orderBy($orderField, $sord)->limit($rows)->offset($rows * ($page - 1))->get()->getResultArray();
        $total = $builder->countAllResults();
        $v = [];
        $bidSites = $this->db->table('bid_site')->select('id, winRate, winInfo')->where('isDelete', 0)->get()->getResultArray();
        $bidSitesArr = array_column($bidSites, null, 'id'); // 以id为键名组成新数组
        foreach ($list as $arr => $row) {
            $v[$arr]['id'] = intval($row['id']);
            $v[$arr]['bidSiteId'] = intval($row['bidSiteId']);
            $v[$arr]['bidSiteName'] = $row['bidSiteName'];
            $v[$arr]['winRate'] = $bidSitesArr[$row['bidSiteId']]['winRate'] ?? 0;
            $v[$arr]['winInfo'] = $bidSitesArr[$row['bidSiteId']]['winInfo'] ?? '';
            $v[$arr]['bidTitle'] = $row['bidTitle'];
            $v[$arr]['bidUrl'] = $row['bidUrl'];
            $v[$arr]['beginDate'] = $row['beginDate'];
            $v[$arr]['inDate'] = $row['inDate'];
            $v[$arr]['joinStatus'] = $row['joinStatus'];
            $v[$arr]['joinName'] = $row['joinName'];
            $v[$arr]['joinHis'] = $row['joinHis'];
            $v[$arr]['leaveReasonHis'] = $row['leaveReasonHis'];
            $v[$arr]['quoteId'] = $row['quoteId'];
            $v[$arr]['quoteNo'] = $row['quoteNo'];
        }
        $json['status'] = 200;
        $json['msg'] = 'success';
        $json['data']['page'] = $page;
        $json['data']['records'] = $total;
        $json['data']['total'] = ceil($json['data']['records'] / $rows);
        $json['data']['rows'] = $v;
        $json['data']['todayTotal'] = $this->db->table('bid_info')->where('inDate >=', date('Y-m-d 00:00:00'))->countAllResults();
        return $this->response->setJSON($json);
    }
    // 招标信息 获取热搜关键词
    public function getHotWord()
    {
        if (!$this->myservice->verifyRight('BIDINFO_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/Bid/getHotWord 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $hotWord = $this->request->getGetPost('hotWord');
        $builder = $this->db->table('bid_hotword')->select('name, searchCount')->where('isDelete', 0);
        if ($hotWord) {
            // 去掉无意义的容易引起错误的字符，再打散成数组
            $regexpWord = mb_str_split(preg_replace('/[^\x{4e00}-\x{9fa5}\dA-Za-z]/u', '', $hotWord));
            foreach ($regexpWord as $kw) {
                $builder->where('name REGEXP "' . $kw . '"', null, false);
            }
        }
        $list = $builder->orderBy('searchCount', 'DESC')->limit(20)->get()->getResultArray();
        if (count($list) > 0) {
            return $this->response->setJSON(['status' => 200, 'msg' => 'success', 'data' => $list]);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
    // 招标信息 获取信息
    public function query()
    {
        if (!$this->myservice->verifyRight('BIDINFO_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/Bid/query 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $data = $this->db->table('bid_info')->where(['id' => $id, 'isDelete' => 0])->get()->getRowArray();
        if ($data) {
            $info['id'] = $id;
            $info['bidSiteId'] = $data['bidSiteId'];
            $info['bidSiteName'] = $data['bidSiteName'];
            $info['bidTitle'] = $data['bidTitle'];
            $info['bidUrl'] = $data['bidUrl'];
            $info['beginDate'] = $data['beginDate'];
            $info['inDate'] = $data['inDate'];
            $info['joinStatus'] = $data['joinStatus'];
            $info['joinName'] = $data['joinName'];
            $info['joinHis'] = $data['joinHis'];
            $info['quoteId'] = $data['quoteId'];
            $info['quoteNo'] = $data['quoteNo'];

            $json['status'] = 200;
            $json['msg'] = 'success';
            $json['data'] = $info;
            return $this->response->setJSON($json);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
    // 招标信息 新增
    public function add()
    {
        if (!$this->myservice->verifyRight('BIDINFO_UPDATE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/Bid/add 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $data = $this->request->getPost();
        if (is_array($data) && count($data) > 0) {
            $data = $this->validform($data);
            // 如果返回的是 Response 对象，直接返回它
            if ($data instanceof \CodeIgniter\HTTP\Response) {
                return $data;
            }
            if ($this->db->table('bid_info')->where(['bidUrl' => $data['bidUrl'], 'isDelete' => 0])->countAllResults() > 0) {
                return $this->response->setJSON(['status' => -1, 'msg' => '招标记录重复！']);
            }
            $info = array_intersect_key($data, array_flip(['bidSiteName', 'bidTitle', 'bidUrl', 'beginDate', 'inDate']));
            $this->db->transStart();
            $this->db->table('bid_info')->insert($info);
            $data['id'] = $this->db->insertID();
            $this->myservice->log('新增了招标信息，标题：' . $data['bidTitle']);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '新增成功！', 'data' => $data]);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '提交的是空数据！']);
    }
    // 招标信息 编辑
    public function update()
    {
        if (!$this->myservice->verifyRight('BIDINFO_UPDATE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/Bid/update 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $data = $this->request->getPost();
        if (is_array($data) && count($data) > 0) {
            $data = $this->validform($data);
            // 如果返回的是 Response 对象，直接返回它
            if ($data instanceof \CodeIgniter\HTTP\Response) {
                return $data;
            }
            $info = array_intersect_key($data, array_flip(['bidSiteName', 'bidTitle', 'bidUrl']));
            $this->db->transStart();
            $this->db->table('bid_info')->where('id', $data['id'])->update($info);
            $this->myservice->log('编辑了招标信息，标题：' . $data['bidTitle']);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '编辑成功！', 'data' => $data]);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '提交的是空数据！']);
    }
    // 招标信息 删除
    public function delete()
    {
        if (!$this->myservice->verifyRight('BIDINFO_DELETE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/Bid/delete 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $data = $this->db->table('bid_info')->where(['id' => $id, 'isDelete' => 0])->get()->getRowArray();
        if ($data) {
            // 真删除
            $this->db->transStart();
            $this->db->table('bid_info')->where('id', $id)->delete();
            $this->myservice->log('删除了招标信息，标题：' . $data['bidTitle']);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '删除成功！', 'data' => $data]);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
    // 招标信息 设置参与状态
    public function setJoinStatus()
    {
        if (!$this->myservice->verifyRight('BIDINFO_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/Bid/setJoinStatus 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $leaveReason = $this->request->getGetPost('leaveReason');
        if ($id > 0 && strlen($this->myerp['uName']) > 0) {
            $bidinfo = $this->db->table('bid_info')->where('id', $id)->get()->getRowArray();
            $joinName_arr = strlen($bidinfo['joinName']) > 0 ? explode(',', $bidinfo['joinName']) : [];
            $this->db->transStart();
            if (count($joinName_arr) == 0) { // 无人参与则直接参与
                $this->db->table('bid_info')->where('id', $id)->update(['joinStatus' => 1, 'joinName' => $this->myerp['uName']]);
                $this->myservice->log('决定参与投标，招标信息ID：' . $id . '，标题：' . $bidinfo['bidTitle']);
            } else { // 有人参与
                if (in_array($this->myerp['uName'], $joinName_arr)) { // 自己有参与
                    if (count($joinName_arr) == 1) { // 仅自己参与 则完全取消参与
                        $this->db->table('bid_info')->where('id', $id)->update([
                            'joinStatus' => 0,
                            'joinName' => '',
                            'leaveReasonHis' => $bidinfo['leaveReasonHis'] ? ($bidinfo['leaveReasonHis'] . ($leaveReason ? ($leaveReason . ' ' . date('Y-m-d H:i:s') . ' ' . $this->myerp['uName'] . "\r\n") : '')) : ($leaveReason ? ($leaveReason . ' ' . date('Y-m-d H:i:s') . ' ' . $this->myerp['uName'] . "\r\n") : '')
                        ]);
                        $this->myservice->log('取消参与投标，招标信息ID：' . $id . '，标题：' . $bidinfo['bidTitle']);
                    } else { // 自己和别人均有参与 则只取消自己参与
                        $joinName_arr = array_diff($joinName_arr, [0 => $this->myerp['uName']]);
                        $joinName = implode(',', $joinName_arr);
                        $this->db->table('bid_info')->where('id', $id)->update([
                            'joinStatus' => 1,
                            'joinName' => $joinName,
                            'leaveReasonHis' => $bidinfo['leaveReasonHis'] ? ($bidinfo['leaveReasonHis'] . ($leaveReason ? ($leaveReason . ' ' . date('Y-m-d H:i:s') . ' ' . $this->myerp['uName'] . "\r\n") : '')) : ($leaveReason ? ($leaveReason . ' ' . date('Y-m-d H:i:s') . ' ' . $this->myerp['uName'] . "\r\n") : '')
                        ]);
                        $this->myservice->log('取消参与投标，招标信息ID：' . $id . '，标题：' . $bidinfo['bidTitle']);
                    }
                } else { // 自己无参与、则直接参与
                    array_push($joinName_arr, $this->myerp['uName']);
                    $joinName = implode(',', $joinName_arr);
                    $this->db->table('bid_info')->where('id', $id)->update(['joinStatus' => 1, 'joinName' => $joinName]);
                    $this->myservice->log('决定参与投标，招标信息ID：' . $id . '，标题：' . $bidinfo['bidTitle']);
                }
            }
            //更新参与历史
            $joinHis_arr = strlen($bidinfo['joinHis']) > 0 ? explode(',', $bidinfo['joinHis']) : [];
            if (count($joinHis_arr) == 0) {
                $this->db->table('bid_info')->where('id', $id)->update(['joinHis' => $this->myerp['uName']]);
            } else {
                if (!in_array($this->myerp['uName'], $joinHis_arr)) {
                    array_push($joinHis_arr, $this->myerp['uName']);
                    $joinHis = implode(',', $joinHis_arr);
                    $this->db->table('bid_info')->where('id', $id)->update(['joinHis' => $joinHis]);
                }
            }
            $data = $this->db->table('bid_info')->select('id,joinHis,joinName,joinStatus,leaveReasonHis')->where('id', $id)->get()->getRowArray();
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '操作成功！', 'data' => $data]);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
    // 招标信息 删除1年前信息
    public function deleteBidInfo1year()
    {
        if (!$this->myservice->verifyRight('BIDINFO_DELETE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/Bid/deleteBidInfo1year 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        // 真删除
        $this->db->transStart();
        $this->db->table('bid_info')->where('beginDate <', date('Y-m-d H:i:s', strtotime('-1 year')))->orWhere('inDate <', date('Y-m-d H:i:s', strtotime('-1 year')))->delete();
        $this->myservice->log('删除了1年前的招标信息');
        $this->db->transComplete();
        if ($this->db->transStatus() === false) {
            return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
        }
        return $this->response->setJSON(['status' => 200, 'msg' => '删除成功！']);
    }
    // 招标信息 记录查看日志
    public function viewLog()
    {
        if (!$this->myservice->verifyRight('BIDINFO_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/Bid/viewLog 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $bidTitle = $this->request->getGetPost('bidTitle');
        if ($id > 0 && $bidTitle) {
<<<<<<< HEAD
            $this->myservice->log('查看了招标信息，ID：' . $id . '，标题：' . $bidTitle);
            return $this->response->setJSON(['status' => 200, 'msg' => 'success']);
=======
            $result = $this->myservice->log('查看了招标信息，ID：' . $id . '，标题：' . $bidTitle);
            if ($result) {
                return $this->response->setJSON(['status' => 200, 'msg' => 'success']);
            }
            return $this->response->setJSON(['status' => -1, 'msg' => 'fail']);
>>>>>>> 151b415 (更新语言最新版本+最新底层框架后全部差异化代码适配重写)
        } else {
            return $this->response->setJSON(['status' => -1, 'msg' => 'fail']);
        }
    }
    // 招标信息 数据验证
    private function validform(array $data)
    {
        if (strlen($data['bidSiteName']) < 1) {
            return $this->response->setJSON(['status' => -1, 'msg' => '招标来源不能为空！']);
        }
        $data['bidTitle'] = str_replace(["\r", "\n", "\r\n", '{', '}', '"', "'"], '”', $data['bidTitle']);
        if (strlen($data['bidTitle']) < 1) {
            return $this->response->setJSON(['status' => -1, 'msg' => '招标标题不能为空！']);
        }
        if (strlen($data['bidUrl']) < 1) {
            return $this->response->setJSON(['status' => -1, 'msg' => '招标链接不能为空！']);
        }
        if ($data['id'] > 0) {
            if ($this->db->table('bid_info')->where(['id !=' => $data['id'], 'bidUrl' => $data['bidUrl']])->countAllResults() > 0) {
                return $this->response->setJSON(['status' => -1, 'msg' => '招标链接重复！']);
            }
        } else {
            if ($this->db->table('bid_info')->where('bidUrl', $data['bidUrl'])->countAllResults() > 0) {
                return $this->response->setJSON(['status' => -1, 'msg' => '招标链接重复！']);
            }
        }
        $data['beginDate'] = $data['inDate'] = date('Y-m-d H:i:s');
        return $data;
    }

    // 招标关键词 获取所有
    public function getKeywords()
    {
        if (!$this->myservice->verifyRight('BIDKEYWORD_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/Bid/getKeywords 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $list = $this->db->table('bid_keyword')->where('isDelete', 0)->orderBy('id', 'DESC')->get()->getResultArray();
        $v = [];
        foreach ($list as $arr => $row) {
            $v[$arr]['id'] = intval($row['id']);
            $v[$arr]['keywordType'] = $row['keywordType'];
            $v[$arr]['name'] = $row['name'];
        }
        $json['status'] = 200;
        $json['msg'] = 'success';
        $json['data'] = $v;
        return $this->response->setJSON($json);
    }
    // 招标关键词 获取某个
    public function getKeyword()
    {
        if (!$this->myservice->verifyRight('BIDKEYWORD_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/Bid/getKeyword 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $data = $this->db->table('bid_keyword')->where(['id' => $id, 'isDelete' => 0])->get()->getRowArray();
        if ($data) {
            $info['id'] = $id;
            $info['name'] = $data['name'];
            $info['keywordType'] = intval($data['keywordType']);
            $json['status'] = 200;
            $json['msg'] = 'success';
            $json['data'] = $info;
            return $this->response->setJSON($json);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
    // 招标关键词 新增
    public function addKeyword()
    {
        if (!$this->myservice->verifyRight('BIDKEYWORD_ADD')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/Bid/addKeyword 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $data = $this->request->getPost();
        if ($data) {
            $data['name'] = preg_replace('!\s+!', ' ', $data['name']); // 将连续多个空格替换为单空格
            if (strlen($data['name']) < 1) {
                return $this->response->setJSON(['status' => -1, 'msg' => '关键词不能为空！']);
            }
            if ($this->db->table('bid_keyword')->where(['name' => $data['name'], 'isDelete' => 0])->countAllResults() > 0) {
                return $this->response->setJSON(['status' => -1, 'msg' => '关键词重复！']);
            }
            $info = array_intersect_key($data, array_flip(['name', 'keywordType']));
            $this->db->transStart();
            $this->db->table('bid_keyword')->insert($info);
            $data['id'] = $this->db->insertID();
            $this->myservice->log('新增了招标关键词：' . $data['name']);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '新增成功！', 'data' => $data]);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '提交的是空数据！']);
    }
    // 招标关键词 编辑
    public function updateKeyword()
    {
        if (!$this->myservice->verifyRight('BIDKEYWORD_UPDATE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/Bid/updateKeyword 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $data = $this->request->getPost();
        if ($data) {
            $data['name'] = preg_replace('!\s+!', ' ', $data['name']); // 将连续多个空格替换为单空格
            if (strlen($data['name']) < 1) {
                return $this->response->setJSON(['status' => -1, 'msg' => '关键词不能为空！']);
            }
            if ($this->db->table('bid_keyword')->where(['name' => $data['name'], 'id !=' => $data['id'], 'isDelete' => 0])->countAllResults() > 0) {
                return $this->response->setJSON(['status' => -1, 'msg' => '关键词重复！']);
            }
            $info = array_intersect_key($data, array_flip(['name', 'keywordType']));
            $this->db->transStart();
            $this->db->table('bid_keyword')->where('id', $data['id'])->update($info);
            $this->myservice->log('编辑了招标关键词：' . $data['name']);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '编辑成功！', 'data' => $data]);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '提交的是空数据！']);
    }
    // 招标关键词 删除
    public function deleteKeyword()
    {
        if (!$this->myservice->verifyRight('BIDKEYWORD_DELETE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/Bid/deleteKeyword 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $data = $this->db->table('bid_keyword')->where('id', $id)->get()->getRowArray();
        if ($data) {
            // 真删除
            $this->db->transStart();
            $this->db->table('bid_keyword')->where('id', $id)->delete();
            $this->myservice->log('删除了招标关键词：' . $data['name']);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '删除成功！']);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }


    // 招标网站 获取全部
    public function getSites()
    {
        if (!$this->myservice->verifyRight('BIDSITE_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/Bid/getSites 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $page = max(intval($this->request->getGetPost('page')), 1);
        $rows = max(intval($this->request->getGetPost('rows')), 100);
        $sidx = $this->request->getGetPost('sidx');
        $sord = strtoupper((string)$this->request->getGetPost('sord')) === 'ASC' ? 'ASC' : 'DESC';
        $province = $this->request->getGetPost('province');
        $siteType = intval($this->request->getGetPost('siteType'));
        $skey = $this->request->getGetPost('skey');
        $builder = $this->db->table('bid_site')->where('isDelete', 0);
        if (strlen($province) > 0) {
            $builder->where('province', $province);
        }
        if ($siteType > -1) {
            $builder->where('siteType', $siteType);
        }
        if ($skey !== '' && $skey !== null) {
            $keywords = array_values(array_filter(explode(',', $skey), 'strlen'));
            foreach ($keywords as $kw) {
                $builder->groupStart()
                    ->like('name', $this->db->escapeLikeString($kw))
                    ->orLike('note', $this->db->escapeLikeString($kw))
                ->groupEnd();
            }
        }
        // 允许排序的字段白名单，杜绝 order by 注入
        $allowedSort = ['id','name','province','siteType','hits','winRate','status'];
        $orderField  = $sidx ? (in_array($sidx, $allowedSort, true) ? $sidx : 'id') : 'id';
        $list = (clone $builder)->orderBy($orderField, $sord)->orderBy('hits', 'DESC')->limit($rows)->offset($rows * ($page - 1))->get()->getResultArray();
        $total = $builder->countAllResults();
        $v = [];
        foreach ($list as $arr => $row) {
            $v[$arr]['id'] = intval($row['id']);
            $v[$arr]['province'] = $row['province'];
            $v[$arr]['name'] = $row['name'];
            $v[$arr]['url'] = $row['url'];
            $v[$arr]['siteType'] = intval($row['siteType']);
            $v[$arr]['note'] = $row['note'];
            $v[$arr]['hits'] = intval($row['hits']);
            $v[$arr]['winRate'] = intval($row['winRate']);
            $v[$arr]['winInfo'] = $row['winInfo'];
            $v[$arr]['status'] = intval($row['status']);
        }
        $json['status'] = 200;
        $json['msg'] = 'success';
        $json['data']['total'] = $total;
        $json['data']['rows'] = $v;
        $json['data']['neverTotal'] = $this->db->table('bid_site')->where(['isDelete' => 0, 'winRate' => -1])->countAllResults();
        return $this->response->setJSON($json);
    }
    // 招标网站 获取某个
    public function getSite()
    {
        if (!$this->myservice->verifyRight('BIDSITE_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/Bid/getSite 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $data = $this->db->table('bid_site')->where(['id' => $id, 'isDelete' => 0])->get()->getRowArray();
        if ($data) {
            $info['id'] = $id;
            $info['province'] = $data['province'];
            $info['name'] = $data['name'];
            $info['url'] = $data['url'];
            $info['siteType'] = intval($data['siteType']);
            $info['note'] = $data['note'];
            $info['status'] = intval($data['status']);
            $json['status'] = 200;
            $json['msg'] = 'success';
            $json['data'] = $info;
            return $this->response->setJSON($json);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
    // 招标网站 新增
    public function addSite()
    {
        if (!$this->myservice->verifyRight('BIDSITE_ADD')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/Bid/addSite 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $data = $this->request->getPost();
        if (is_array($data) && count($data) > 0) {
            if ($this->db->table('bid_site')->where('name', $data['name'])->countAllResults() > 0) {
                return $this->response->setJSON(['status' => -1, 'msg' => '招标网站名称重复！']);
            }
            $info = array_intersect_key($data, array_flip(['province', 'name', 'url', 'siteType', 'note', 'status']));
            $this->db->transStart();
            $this->db->table('bid_site')->insert($info);
            $data['id'] = $this->db->insertID();
            $this->myservice->log('新增招标网站：' . $data['name']);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '新增成功！', 'data' => $data]);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '提交的是空数据！']);
    }
    // 招标网站 编辑
    public function updateSite()
    {
        if (!$this->myservice->verifyRight('BIDSITE_UPDATE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/Bid/updateSite 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $data = $this->request->getPost();
        if (is_array($data) && count($data) > 0) {
            if ($this->db->table('bid_site')->where(['id !=' => $data['id'], 'name' => $data['name']])->countAllResults() > 0) {
                return $this->response->setJSON(['status' => -1, 'msg' => '招标网站名称重复！']);
            }
            $info = array_intersect_key($data, array_flip(['province', 'name', 'url', 'siteType', 'note', 'status']));
            $this->db->transStart();
            $this->db->table('bid_site')->where('id', $data['id'])->update($info);
            $this->myservice->log('编辑招标网站：' . $data['name']);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '编辑成功！', 'data' => $data]);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '提交的是空数据！']);
    }
    // 招标网站 增加网站点击数
    public function addSiteHits()
    {
        $id = intval($this->request->getGetPost('id'));
        if ($id > 0) {
            $result = $this->db->table('bid_site')->where('id', $id)->set('hits', 'hits + 1', false)->update();
<<<<<<< HEAD
=======
            log_message('error', (string)$result);
>>>>>>> 151b415 (更新语言最新版本+最新底层框架后全部差异化代码适配重写)
            if (!$result) {
                return $this->response->setJSON(['status' => -1, 'msg' => 'fail']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => 'success']);
        } else {
            return $this->response->setJSON(['status' => -1, 'msg' => 'fail']);
        }
    }


    // 投标项目 列表
    public function bidProgramList()
    {
        if (!$this->myservice->verifyRight('BIDPROGRAM_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/Bid/bidProgramList 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $page = max(intval($this->request->getGetPost('page')), 1);
        $rows = max(intval($this->request->getGetPost('rows')), 100);
        $sidx = $this->request->getGetPost('sidx');
        $sord = strtoupper((string)$this->request->getGetPost('sord')) === 'ASC' ? 'ASC' : 'DESC';
        $status1 = $this->request->getGetPost('status1');
        $status2 = intval($this->request->getGetPost('status2'));
        $status3 = intval($this->request->getGetPost('status3'));
        $skey = $this->request->getGetPost('skey');
        $beginDate = $this->request->getGetPost('beginDate');
        $endDate = $this->request->getGetPost('endDate');
        $unDone = intval($this->request->getGetPost('unDone'));
        $builder = $this->db->table('bid_program')->where('isDelete', 0);
        if ($status1) {
            $builder->where('FIND_IN_SET(' . $this->db->escape($status1) . ', quotedName) >', 0, false);
        }
        if ($status2 > -1) {
            $builder->where('sId',  $status2);
        }
        if ($status3 > -1) {
            $builder->where('status',  $status3);
        }
        if ($beginDate) {
            $builder->where('createTime >=',  $beginDate . ' 00:00:00');
        }
        if ($endDate) {
            $builder->where('createTime <=',  $endDate . ' 23:59:59');
        }
        if ($unDone > 0) {
            $builder->where('status',  0);
        }
        if ($skey !== '' && $skey !== null) {
            $keywords = array_values(array_filter(explode(',', $skey), 'strlen'));
            foreach ($keywords as $kw) {
                $builder->groupStart()
                    ->like('srcBqNo', $this->db->escapeLikeString($kw))
                    ->orLike('contactName', $this->db->escapeLikeString($kw))
                    ->orLike('contactInfo', $this->db->escapeLikeString($kw))
                    ->orLike('sName', $this->db->escapeLikeString($kw))
                    ->orLike('bidSiteName', $this->db->escapeLikeString($kw))
                    ->orLike('bidTitle', $this->db->escapeLikeString($kw))
                    ->orLike('bidUrl', $this->db->escapeLikeString($kw))
                    ->orLike('quotedName', $this->db->escapeLikeString($kw))
                    ->orLike('description', $this->db->escapeLikeString($kw))
                    ->orLike('lostReason', $this->db->escapeLikeString($kw))
                    ->orLike('uName', $this->db->escapeLikeString($kw))
                ->groupEnd();
            }
        }
        // 允许排序的字段白名单，杜绝 order by 注入
        $allowedSort = ['id','bidSiteName','contactName','bidTitle','bidUrl','budget','amount','quotedName','sName','srcBqNo','description','uName','createTime','bidDate','status'];
        $orderField  = $sidx ? (in_array($sidx, $allowedSort, true) ? $sidx : 'id') : 'id';
        $list = (clone $builder)->orderBy($orderField, $sord)->limit($rows)->offset($rows * ($page - 1))->get()->getResultArray();
        $total = $builder->countAllResults();
        $v = [];
        foreach ($list as $arr => $row) {
            $v[$arr]['id'] = intval($row['id']);
            $v[$arr]['srcBqId'] = intval($row['srcBqId']);
            $v[$arr]['srcBqNo'] = $row['srcBqNo'];
            $v[$arr]['contactId'] = intval($row['contactId']);
            $v[$arr]['contactName'] = $row['contactName'];
            $v[$arr]['sId'] = intval($row['sId']);
            $v[$arr]['sName'] = $row['sName'];
            $v[$arr]['amount'] = (float)$row['amount'];
            $v[$arr]['budget'] = (float)$row['budget'];
            $v[$arr]['bidSiteId'] = intval($row['bidSiteId']);
            $v[$arr]['bidSiteName'] = $row['bidSiteName'];
            $v[$arr]['bidInfoId'] = intval($row['bidInfoId']);
            $v[$arr]['bidTitle'] = $row['bidTitle'];
            $v[$arr]['bidUrl'] = $row['bidUrl'];
            $v[$arr]['contactInfo'] = $row['contactInfo'];
            $v[$arr]['quotedName'] = $row['quotedName'];
            $v[$arr]['bidDate'] = $row['bidDate'];
            $v[$arr]['description'] = $row['description'];
            $v[$arr]['lostReason'] = $row['lostReason'];
            $v[$arr]['uId'] = intval($row['uId']);
            $v[$arr]['uName'] = $row['uName'];
            $v[$arr]['createTime'] = $row['createTime'];
            $v[$arr]['status'] = $row['status'];
            $v[$arr]['files1'] = $row['files1'] == '' ? [] : json_decode($row['files1'], true);
            $v[$arr]['files2'] = $row['files2'] == '' ? [] : json_decode($row['files2'], true);
            $v[$arr]['files3'] = $row['files3'] == '' ? [] : json_decode($row['files3'], true);
        }
        $json['status'] = 200;
        $json['msg'] = 'success';
        $json['data']['page'] = $page;
        $json['data']['records'] = $total;
        $json['data']['total'] = ceil($json['data']['records'] / $rows);
        $json['data']['rows'] = $v;
        return $this->response->setJSON($json);
    }
    // 投标项目 获取
    public function queryBidProgram()
    {
        if (!$this->myservice->verifyRight('BIDPROGRAM_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/Bid/queryBidProgram 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $data = $this->db->table('bid_program')->where(['id' => $id, 'isDelete' => 0])->get()->getRowArray();
        if ($data) {
            $info['id'] = $id;
            $info['srcBqId'] = intval($data['srcBqId']);
            $info['srcBqNo'] = $data['srcBqNo'];
            $info['contactId'] = intval($data['contactId']);
            $info['contactName'] = $data['contactName'];
            $info['sId'] = intval($data['sId']);
            $info['sName'] = $data['sName'];
            $info['amount'] = (float)$data['amount'];
            $info['budget'] = (float)$data['budget'];
            $info['bidSiteId'] = intval($data['bidSiteId']);
            $info['bidSiteName'] = $data['bidSiteName'];
            $info['bidInfoId'] = intval($data['bidInfoId']);
            $info['bidTitle'] = $data['bidTitle'];
            $info['bidUrl'] = $data['bidUrl'];
            $info['contactInfo'] = $data['contactInfo'];
            $info['quotedName'] = $data['quotedName'];
            $info['bidDate'] = $data['bidDate'] == '0000-00-00' ? '' : $data['bidDate'];
            $info['files1'] = $data['files1'] == '' ? [] : json_decode($data['files1'], true);
            $info['files2'] = $data['files2'] == '' ? [] : json_decode($data['files2'], true);
            $info['files3'] = $data['files3'] == '' ? [] : json_decode($data['files3'], true);
            $info['description'] = $data['description'];
            $info['lostReason'] = $data['lostReason'];
            $info['uId'] = intval($data['uId']);
            $info['uName'] = $data['uName'];
            $info['createTime'] = $data['createTime'];
            $info['status'] = intval($data['status']);

            $json['status'] = 200;
            $json['msg'] = 'success';
            $json['data'] = $info;
            return $this->response->setJSON($json);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
    // 投标项目 新增
    public function addBidProgram()
    {
        if (!$this->myservice->verifyRight('BIDPROGRAM_ADD')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/Bid/addBidProgram 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $data = $this->request->getPost();
        if (is_array($data) && count($data) > 0) {
            $data = $this->bidProgramValidform($data);
            // 如果返回的是 Response 对象，直接返回它
            if ($data instanceof \CodeIgniter\HTTP\Response) {
                return $data;
            }
            $info = array_intersect_key($data, array_flip(['srcBqId', 'srcBqNo', 'quotedName', 'amount', 'budget', 'bidDate', 'status', 'bidSiteId', 'bidSiteName', 'contactId', 'contactName', 'sId', 'sName', 'bidTitle', 'bidUrl', 'contactInfo', 'files1', 'files2', 'files3', 'description', 'lostReason', 'uId', 'uName', 'createTime']));
            $this->db->transStart();
            $this->db->table('bid_program')->insert($info);
            $data['id'] = $this->db->insertID();
            $this->db->table('bill_bidquote')->where('id', $data['srcBqId'])->update(['programStatus' => $data['status'], 'programId' => $data['id']]);
            $this->myservice->log('新增了招标项目ID：' . $data['id'] . '(标题：' . $data['bidTitle'] . ')');
            $this->myservice->log('将投标项目ID：' . $data['id'] . '(标题：' . $data['bidTitle'] . ')的开标结果标记为 ' . ($data['status'] == 0 ? '未开标' : ($data['status'] == 1 ? '未中标' : '已中标')));
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            $data['files1'] = $data['files1'] == '' ? [] : json_decode($data['files1'], true);
            $data['files2'] = $data['files2'] == '' ? [] : json_decode($data['files2'], true);
            $data['files3'] = $data['files3'] == '' ? [] : json_decode($data['files3'], true);
            return $this->response->setJSON(['status' => 200, 'msg' => '新增成功！', 'data' => $data]);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '提交的是空数据！']);
    }
    // 投标项目 编辑
    public function updateBidProgram()
    {
        if (!$this->myservice->verifyRight('BIDPROGRAM_UPDATE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/Bid/updateBidProgram 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $data = $this->request->getPost();
        if (is_array($data) && count($data) > 0) {
            $data = $this->bidProgramValidform($data);
            // 如果返回的是 Response 对象，直接返回它
            if ($data instanceof \CodeIgniter\HTTP\Response) {
                return $data;
            }
            $oldData = $this->db->table('bid_program')->where('id', $data['id'])->get()->getRowArray();
            $info = array_intersect_key($data, array_flip(['srcBqId', 'srcBqNo', 'quotedName', 'amount', 'budget', 'bidDate', 'status', 'bidSiteId', 'bidSiteName', 'contactId', 'contactName', 'sId', 'sName', 'bidTitle', 'bidUrl', 'contactInfo', 'files1', 'files2', 'files3', 'description', 'lostReason']));
            $this->db->transStart();
            $this->db->table('bid_program')->where('id', $data['id'])->update($info);
            if ($data['srcBqId'] > 0) {
                $this->db->table('bill_bidquote')->where('id', $data['srcBqId'])->update(['programStatus' => $data['status'], 'programId' => $data['id']]);
            }
            $this->myservice->log('编辑了招标项目ID：' . $data['id'] . '(标题：' . $data['bidTitle'] . ')' . ($data['status'] != $oldData['status'] ? ('，并将开标结果标记为 ' . ($data['status'] == 0 ? '未开标' : ($data['status'] == 1 ? '未中标' : '已中标'))) : ''));
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            $data['files1'] = $data['files1'] == '' ? [] : json_decode($data['files1'], true);
            $data['files2'] = $data['files2'] == '' ? [] : json_decode($data['files2'], true);
            $data['files3'] = $data['files3'] == '' ? [] : json_decode($data['files3'], true);
            return $this->response->setJSON(['status' => 200, 'msg' => '编辑成功！', 'data' => $data]);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '提交的是空数据！']);
    }
    // 投标项目 删除
    public function deleteBidProgram()
    {
        if (!$this->myservice->verifyRight('BIDPROGRAM_DELETE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/Bid/deleteBidProgram 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $data = $this->db->table('bid_program')->where('id', $id)->get()->getRowArray();
        if ($data) {
            if ($data['uId'] != $this->myerp['uId']) {
                return $this->response->setJSON(['status' => -1, 'msg' => '不能删除别人录入的投标项目！']);
            }
            $this->db->transStart();
            $this->db->table('bid_program')->where('id', $id)->update(['isDelete' => 1]);
            $this->db->table('bill_bidquote')->where('id', $data['srcBqId'])->update(['programStatus' => -1, 'programId' => 0]);
            $this->myservice->log('删除了投标项目ID：' . $id . '(标题：' . $data['bidTitle'] . ')');
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '删除成功！']);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
    // 投标项目 结果状态标记
    public function bidProgramStatus()
    {
        if (!$this->myservice->verifyRight('BIDPROGRAM_UPDATE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/Bid/bidProgramStatus 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $status = intval($this->request->getGetPost('status'));
        $lostReason = $this->request->getGetPost('lostReason');
        if ($id > 0) {
            $this->db->transStart();
            $this->db->table('bid_program')->where('id', $id)->update(['status' => $status, 'lostReason' => $lostReason]);
            $data = $this->db->table('bid_program')->where('id', $id)->get()->getRowArray();
            $this->db->table('bill_bidquote')->where('id', $data['srcBqId'])->update(['programStatus' => $data['status'], 'programId' => $data['id']]);
            // 实时更新招标来源网站、招标单位的历史中标率
            $res = $this->db->table('bid_program')->select('SUM(amount) AS amount, SUM(1) AS offerCount')->where(['isDelete' => 0, 'bidSiteName' =>  $data['bidSiteName']])->get()->getRowArray();
            if ($res && isset($res['offerCount']) && $res['offerCount'] > 0) {
                $offerCount = intval($res['offerCount']);
                $res2 = $this->db->table('bid_program')->select('SUM(amount) AS winAmount, SUM(1) AS winCount')->where(['isDelete' => 0, 'bidSiteName' => $data['bidSiteName'], 'status' => 2])->get()->getRowArray();
                $winCount = ($res2 && isset($res2['winCount'])) ? intval($res2['winCount']) : 0;
                $winRate = round($winCount / $offerCount * 100, 2);
                $winInfo = '投 ' . $offerCount . ' 中 ' . $winCount;
                $this->db->table('bid_site')->where('name', $data['bidSiteName'])->update(['winRate' => $winRate, 'winInfo' => $winInfo]);
            } else {
                $this->db->table('bid_site')->where('name', $data['bidSiteName'])->update(['winRate' => -1, 'winInfo' => '']);
            }
            $res3 = $this->db->table('bid_program')->select('SUM(amount) AS amount, SUM(1) AS offerCount')->where(['isDelete' => 0, 'contactName' => $data['contactName']])->get()->getRowArray();
            if ($res3 && isset($res3['offerCount']) && $res3['offerCount'] > 0) {
                $offerCount = intval($res3['offerCount']);
                $res4 = $this->db->table('bid_program')->select('SUM(amount) AS winAmount, SUM(1) AS winCount')->WHERE(['isDelete' => 0, 'contactName' => $data['contactName'], 'status' => 2])->get()->getRowArray();
                $winCount = ($res4 && isset($res4['winCount'])) ? intval($res4['winCount']) : 0;
                $winRate = round($winCount / $offerCount * 100, 2);
                $winInfo = '投 ' . $offerCount . ' 中 ' . $winCount;
                $this->db->table('contact')->where(['name' => $data['contactName'], 'type' => 0])->update(['winRate' => $winRate, 'winInfo' => $winInfo]);
            } else {
                $this->db->table('contact')->where(['name' => $data['contactName'], 'type' => 0])->update(['winRate' => -1, 'winInfo' => '']);
            }
            // end
            $this->myservice->log('将投标项目ID：' . $id . '（招标标题：' . $data['bidTitle'] . '）的开标结果标记为 ' . ($status == 0 ? '未开标' : ($status == 1 ? '未中标' : '已中标')));
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '操作成功！', 'data' => $data]);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '提交的是空数据！']);
    }
    // 投标项目 上传文件1
    public function uploadBidProgramFile1()
    {
        if (!$this->myservice->verifyRight('BIDPROGRAM_UPDATE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/Bid/uploadBidProgramFile1 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        set_time_limit(0);
        session_write_close();
        if (empty($_FILES)){
            return $this->response->setJSON(['status' => -1, 'msg' => '没有上传文件！']);
        };
        $uppath = 'data/upfile/bidProgram/files1/';
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
        $this->myservice->log('上传了投标项目合同文件：' . $_FILES['file']['name']);
        $json['status'] = 200;
        $json['msg'] = '资料上传成功！';
        $json['file'] = ['name' => $_FILES['file']['name'], 'url' => $target_file, 'upName' => $this->myerp['uName'], 'upTime' => date('Y-m-d')];
        return $this->response->setJSON($json);
    }
    // 投标项目 上传文件2
    public function uploadBidProgramFile2()
    {
        if (!$this->myservice->verifyRight('BIDPROGRAM_UPDATE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/Bid/uploadBidProgramFile2 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        set_time_limit(0);
        session_write_close();
        if (empty($_FILES)){
            return $this->response->setJSON(['status' => -1, 'msg' => '没有上传文件！']);
        };
        $uppath = 'data/upfile/bidProgram/files2/';
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
        $this->myservice->log('上传了投标项目签收凭证：' . $_FILES['file']['name']);
        $json['status'] = 200;
        $json['msg'] = '资料上传成功！';
        $json['file'] = ['name' => $_FILES['file']['name'], 'url' => $target_file, 'upName' => $this->myerp['uName'], 'upTime' => date('Y-m-d')];
        return $this->response->setJSON($json);
    }
    // 投标项目 上传文件3
    public function uploadBidProgramFile3()
    {
        if (!$this->myservice->verifyRight('BIDPROGRAM_UPDATE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/Bid/uploadBidProgramFile3 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        set_time_limit(0);
        session_write_close();
        if (empty($_FILES)){
            return $this->response->setJSON(['status' => -1, 'msg' => '没有上传文件！']);
        };
        $uppath = 'data/upfile/bidProgram/files3/';
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
        $this->myservice->log('上传了投标项目其他文件：' . $_FILES['file']['name']);
        $json['status'] = 200;
        $json['msg'] = '资料上传成功！';
        $json['file'] = ['name' => $_FILES['file']['name'], 'url' => $target_file, 'upName' => $this->myerp['uName'], 'upTime' => date('Y-m-d')];
        return $this->response->setJSON($json);
    }
    // 投标项目 数据验证
    private function bidProgramValidform(array $data)
    {
        isset($data['id']) ? intval($data['id']) : -1;
        if (strlen($data['quotedName']) < 1) {
            return $this->response->setJSON(['status' => -1, 'msg' => '报价人不能为空！']);
        }
        $data['amount'] = (float)$data['amount'];
        if ($data['amount'] <= 0 ) {
            return $this->response->setJSON(['status' => -1, 'msg' => '报价金额必须大于零！']);
        }
        if (strlen($data['bidDate']) < 1) {
            return $this->response->setJSON(['status' => -1, 'msg' => '开标日期不能为空！']);
        }
        if (strlen($data['bidSiteName']) < 1) {
            return $this->response->setJSON(['status' => -1, 'msg' => '招标来源不能为空！']);
        }
        if (strlen($data['contactName']) < 1) {
            return $this->response->setJSON(['status' => -1, 'msg' => '招标单位不能为空！']);
        }
        if (strlen($data['sName']) < 1) {
            return $this->response->setJSON(['status' => -1, 'msg' => '投标单位不能为空！']);
        }
        $data['bidTitle'] = str_replace(["\r", "\n", "\r\n", '{', '}', '"', "'"], '”', $data['bidTitle']);
        if (strlen($data['bidTitle']) < 1) {
            return $this->response->setJSON(['status' => -1, 'msg' => '招标标题不能为空！']);
        }
        if (strlen($data['bidUrl']) < 1) {
            return $this->response->setJSON(['status' => -1, 'msg' => '招标链接不能为空！']);
        }
        if (strlen($data['contactInfo']) < 1) {
            return $this->response->setJSON(['status' => -1, 'msg' => '联系方式不能为空！']);
        }
        if ($data['status'] == 1 && $data['lostReason'] == '') {
            return $this->response->setJSON(['status' => -1, 'msg' => '开标结果为【未中标】时，必须录入【未中标原因】']);
        }
        if ($this->db->table('bid_program')->where(['contactName' => $data['contactName'], 'bidTitle' => $data['bidTitle'], 'bidUrl' => $data['bidUrl'], 'id !=' => $data['id'], 'isDelete' => 0])->countAllResults() > 0) {
            return $this->response->setJSON(['status' => -1, 'msg' => '已存在相同单位+相同标题+相同链接的投标项目！<br>如果确定是相同链接下的不同投标项目（例如分包），请适当在标题后面追加文字加以区分（例如：原标题+分包2）。']);
        }
        $data['status'] = intval($data['status']);
        $data['files1'] = strlen($data['files1']) < 10 ? '' : $data['files1'];
        $data['files2'] = strlen($data['files2']) < 10 ? '' : $data['files2'];
        $data['files3'] = strlen($data['files3']) < 10 ? '' : $data['files3'];
        $data['uId'] = $this->myerp['uId'];
        $data['uName'] = $this->myerp['uName'];
        $data['createTime'] = date("Y-m-d H:i:s");
        return $data;
    }


    // 代办跑腿 列表
    public function bidServiceList()
    {
        if (!$this->myservice->verifyRight('BIDSERVICE_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/Bid/bidServiceList 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $page = max(intval($this->request->getGetPost('page')), 1);
        $rows = max(intval($this->request->getGetPost('rows')), 100);
        $sidx = $this->request->getGetPost('sidx');
        $sord = strtoupper((string)$this->request->getGetPost('sord')) === 'ASC' ? 'ASC' : 'DESC';
        $province = $this->request->getGetPost('province');
        $city = $this->request->getGetPost('city');
        $area = $this->request->getGetPost('area');
        $skey = $this->request->getGetPost('skey');
        $builder = $this->db->table('bid_service')->where('isDelete', 0);
        if ($province) {
            $builder->where('province', $province);
        }
        if ($city) {
            $builder->where('city', $city);
        }
        if ($area) {
            $builder->where('area', $area);
        }
        if ($skey !== '' && $skey !== null) {
            $keywords = array_values(array_filter(explode(',', $skey), 'strlen'));
            foreach ($keywords as $kw) {
                $builder->groupStart()
                    ->like('mobile', $this->db->escapeLikeString($kw))
                    ->orLike('phone', $this->db->escapeLikeString($kw))
                    ->orLike('qq', $this->db->escapeLikeString($kw))
                    ->orLike('wechat', $this->db->escapeLikeString($kw))
                    ->orLike('address', $this->db->escapeLikeString($kw))
                    ->orLike('description', $this->db->escapeLikeString($kw))
                ->groupEnd();
            }
        }
        $allowedSort = ['id','name'];
        $orderField  = $sidx ? (in_array($sidx, $allowedSort, true) ? $sidx : 'id') : 'id';
        $list = (clone $builder)->orderBy($orderField, $sord)->limit($rows)->offset($rows * ($page - 1))->get()->getResultArray();
        $total = $builder->countAllResults();
        $v = [];
        foreach ($list as $arr => $row) {
            $v[$arr]['id'] = intval($row['id']);
            $v[$arr]['name'] = $row['name'];
            $v[$arr]['mobile'] = $row['mobile'];
            $v[$arr]['phone'] = $row['phone'];
            $v[$arr]['qq'] = $row['qq'];
            $v[$arr]['wechat'] = $row['wechat'];
            $v[$arr]['beginDate'] = $row['beginDate'];
            $v[$arr]['province'] = $row['province'];
            $v[$arr]['city'] = $row['city'];
            $v[$arr]['area'] = $row['area'];
            $v[$arr]['address'] = $row['address'];
            $v[$arr]['description'] = $row['description'];
        }
        $json['status'] = 200;
        $json['msg'] = 'success';
        $json['data']['page'] = $page;
        $json['data']['records'] = $total;
        $json['data']['total'] = ceil($json['data']['records'] / $rows);
        $json['data']['rows'] = $v;
        return $this->response->setJSON($json);
    }
    // 代办跑腿 获取
    public function bidServiceQuery()
    {
        if (!$this->myservice->verifyRight('BIDSERVICE_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/Bid/bidServiceQuery 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $data = $this->db->table('bid_service')->where(['id' => $id, 'isDelete' => 0])->get()->getRowArray();
        if ($data) {
            $info['id'] = $id;
            $info['name'] = $data['name'];
            $info['mobile'] = $data['mobile'];
            $info['phone'] = $data['phone'];
            $info['qq'] = $data['qq'];
            $info['wechat'] = $data['wechat'];
            $info['beginDate'] = $data['beginDate'];
            $info['province'] = $data['province'];
            $info['city'] = $data['city'];
            $info['area'] = $data['area'];
            $info['address'] = $data['address'];
            $info['description'] = $data['description'];
            $info['files'] = $data['files'] == '' ? [] : json_decode($data['files'], true);
            $json['status'] = 200;
            $json['msg'] = 'success';
            $json['data'] = $info;
            return $this->response->setJSON($json);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
    // 代办跑腿 新增
    public function bidServiceAdd()
    {
        if (!$this->myservice->verifyRight('BIDSERVICE_ADD')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/Bid/bidServiceAdd 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $data = $this->request->getPost();
        if (is_array($data) && count($data) > 0) {
            $data = $this->bidServiceValidform($data);
            // 如果返回的是 Response 对象，直接返回它
            if ($data instanceof \CodeIgniter\HTTP\Response) {
                return $data;
            }
            $info = array_intersect_key($data, array_flip(['name', 'mobile', 'phone', 'qq', 'wechat', 'beginDate', 'province', 'city', 'area', 'address', 'description', 'files']));
            $this->db->transStart();
            $this->db->table('bid_service')->insert($info);
            $data['id'] = $this->db->insertID();
            $this->myservice->log('新增了代办跑腿，ID：' . $data['id'] . '，名称：' . $data['name']);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '新增成功！', 'data' => $data]);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '提交的是空数据！']);
    }
    // 代办跑腿 上传资料
    public function bidServiceUploadFile()
    {
        if (!$this->myservice->verifyRight('BIDSERVICE_UPDATE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/Bid/bidServiceUploadFile 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        if (empty($_FILES)){
            return $this->response->setJSON(['status' => -1, 'msg' => '没有上传文件！']);
        };
        $uppath = 'data/upfile/bidService/';
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
        $this->myservice->log('上传了代办跑腿资料：' . $_FILES['file']['name']);
        $json['status'] = 200;
        $json['msg'] = '资料上传成功！';
        $json['file'] = ['name' => $_FILES['file']['name'], 'url' => $target_file, 'upName' => $this->myerp['uName'], 'upTime' => date('Y-m-d')];
        return $this->response->setJSON($json);
    }
    // 代办跑腿 编辑
    public function bidServiceUpdate()
    {
        if (!$this->myservice->verifyRight('BIDSERVICE_UPDATE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/Bid/bidServiceUpdate 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $data = $this->request->getPost();
        if (is_array($data) && count($data) > 0) {
            $data = $this->bidServiceValidform($data);
            // 如果返回的是 Response 对象，直接返回它
            if ($data instanceof \CodeIgniter\HTTP\Response) {
                return $data;
            }
            $info = array_intersect_key($data, array_flip(['name', 'mobile', 'phone', 'qq', 'wechat', 'beginDate', 'province', 'city', 'area', 'address', 'description', 'files']));
            $this->db->transStart();
            $this->db->table('bid_service')->where('id', $data['id'])->update($info);
            $this->myservice->log('编辑了代办跑腿，ID：' . $data['id'] . '，名称：' . $data['name']);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '编辑成功！', 'data' => $data]);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '提交的是空数据！']);
    }
    // 代办跑腿 删除
    public function bidServiceDelete()
    {
        if (!$this->myservice->verifyRight('BIDSERVICE_DELETE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/Bid/bidServiceDelete 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $data = $this->db->table('bid_service')->where('id', $id)->get()->getRowArray();
        if ($data) {
            $this->db->transStart();
            $this->db->table('bid_service')->where('id', $id)->update(['isDelete' => 1]);
            $this->myservice->log('删除了代办跑腿：ID：' . $data['id'] . '，' . $data['name'] . ' ' . $data['mobile'] . ' ' . $data['phone'] . ' ' . $data['qq'] . ' ' . $data['wechat'] . ' ' . $data['province'] . ' ' . $data['city'] . ' ' . $data['area'] . ' ' . $data['address']);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '删除成功！']);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
    // 代办跑腿 数据验证
    private function bidServiceValidform(array $data)
    {
        isset($data['id']) ? intval($data['id']) : -1;
        if (strlen($data['name']) < 1) {
            return $this->response->setJSON(['status' => -1, 'msg' => '联系人不能为空！']);
        }
        if (strlen($data['mobile']) < 1) {
            return $this->response->setJSON(['status' => -1, 'msg' => '手机不能为空！']);
        }
        if (strlen($data['province']) < 1) {
            return $this->response->setJSON(['status' => -1, 'msg' => '所在省不能为空！']);
        }
        if ($data['mobile'] && $this->db->table('bid_service')->where(['mobile' => $data['mobile'], 'isDelete' => 0, 'id !=' => $data['id']])->countAllResults() > 0) {
            return $this->response->setJSON(['status' => -1, 'msg' => '手机号重复！']);
        }
        if ($data['phone'] && $this->db->table('bid_service')->where(['phone' => $data['phone'], 'isDelete' => 0, 'id !=' => $data['id']])->countAllResults() > 0) {
            return $this->response->setJSON(['status' => -1, 'msg' => '座机号重复！']);
        }
        if ($data['qq'] && $this->db->table('bid_service')->where(['qq' => $data['qq'], 'isDelete' => 0, 'id !=' => $data['id']])->countAllResults() > 0) {
            return $this->response->setJSON(['status' => -1, 'msg' => 'QQ号重复！']);
        }
        if ($data['wechat'] && $this->db->table('bid_service')->where(['wechat' => $data['wechat'], 'isDelete' => 0, 'id !=' => $data['id']])->countAllResults() > 0) {
            return $this->response->setJSON(['status' => -1, 'msg' => '微信号重复！']);
        }
        $data['beginDate'] = $data['beginDate'] ?? date('Y-m-d');
        $data['files'] = strlen($data['files']) < 10 ? '' : $data['files'];
        return $data;
    }
}
