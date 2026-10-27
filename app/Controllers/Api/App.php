<?php

namespace App\Controllers\Api;
use App\Controllers\BaseController;

header('Access-Control-Allow-Origin:*'); //指定允许所有域名访问，即允许跨域

class APP extends BaseController
{
    // 绑定微信小程序openid
    public function bindOpenid()
    {
        $uId = (int)$this->request->getGetPost('uId');
        $code = $this->request->getGetPost('code');
        if ($uId > 0 && $code) {
            $url = 'https://api.weixin.qq.com/sns/jscode2session?appid=wxdbf7badeabcfe714&secret=8738eb97e27dc5b95a90f4563e0086f7&js_code=' . $code . '&grant_type=authorization_code';
            $res = global_curl($url);
            if ($res !== false) {
                $res = json_decode($res, true);
                $openid = $res['openid'] ?? '';
                if ($openid) {
                    $affected = $this->db->table('user')->where('id', $uId)->update(['openid' => $openid]);
                    if ($affected === 0) {
                        return $this->response->setJSON(['status' => -1, 'msg' => '绑定失败']);
                    }
                    return $this->response->setJSON(['status' => 200, 'msg' => '绑定成功']);
                } else {
                    return $this->response->setJSON(['status' => -1, 'msg' => '获取openid失败']);
                }
            } else {
                return $this->response->setJSON(['status' => -1, 'msg' => '请求微信接口失败']);
            }
        } else {
            return $this->response->setJSON(['status' => -1, 'msg' => '参数错误']);
        }
    }
    // 接收微信小程序订阅消息成功后的回调接口
    public function wxRevice()
    {
        $signature = $this->request->getGetPost('signature');
        $timestamp = $this->request->getGetPost('timestamp');
        $nonce = $this->request->getGetPost('nonce');
        $echostr = $this->request->getGetPost('echostr');
        $openid = $this->request->getGetPost('openid');
        $encrypt_type = $this->request->getGetPost('encrypt_type');
        $msg_signature = $this->request->getGetPost('msg_signature');
        $token = 'wx56dz';
        if ($signature && $timestamp && $nonce) {
            $tmpArr = [$token, $timestamp, $nonce];
            sort($tmpArr, SORT_STRING);
            $tmpStr = implode($tmpArr);
            $tmpStr = sha1($tmpStr);
            if (hash_equals($tmpStr, $signature)) {
                // 验证成功，处理订阅消息接收逻辑
                $affected = $this->db->table('user')->where(['openid' => $openid])->update(['allowSubscribeMessage' => 1]);
                if ($affected === 0) {
                    log_message('error', '更新用户订阅消息状态失败，openid: ' . $openid);
                    return $this->response->setJSON(['status' => -1, 'msg' => 'openid不存在']);
                }
                return $this->response->setBody($echostr)->setContentType('text/plain');
            } else {
                log_message('error', '更新用户订阅消息状态失败，openid: ' . $openid);
                return $this->response->setJSON(['status' => -1, 'msg' => '请求错误']);
            }
        } else {
                log_message('error', '更新用户订阅消息状态失败，openid: ' . $openid);
                return $this->response->setJSON(['status' => -1, 'msg' => '请求错误']);
        }
    }
    // 用户登录
    public function login()
    {
        $data = $this->request->getPost();
        if (is_array($data) && count($data) > 0) {
            if (!$data['isNewApp'] ) {
                return $this->response->setJSON(['status' => -1, 'msg' => '旧小程序已停用']);
            }
            if (strlen($data['userName']) < 1) {
                return $this->response->setJSON(['status' => -1, 'msg' => '账号不能为空']);
            }
            if (strlen($data['userPwd']) < 1) {
                return $this->response->setJSON(['status' => -1, 'msg' => '密码不能为空']);
            }
            $user = $this->db->table('user')->groupStart()->where('userName', $data['userName'])->orWhere('mobile', $data['userName'])->groupEnd()->where('isDelete', 0)->get()->getRowArray();
            if ($user) {
                if ($user['isDisable'] == 1) {
                    return $this->response->setJSON(['status' => -1, 'msg' => '账号被停用']);
                }
                if ($user['userPwd'] == $data['userPwd'] || $user['userPwd'] == md5($data['userPwd'])) {
                    $this->db->table('log')->insert(['log' => '登陆成功，用户名：' . $user['userName'] . '，方式：小程序，IP：' . $this->request->getIPAddress(), 'name' => $user['name'], 'createTime' => date('Y-m-d H:i:s')]);
                    return $this->response->setJSON(['status' => 200, 'msg' => '登录成功', 'data' => $user]);
                } else {
                    return $this->response->setJSON(['status' => -1, 'msg' => '密码错误']);
                }
            } else {
                return $this->response->setJSON(['status' => -1, 'msg' => '账号错误']);
            }
        } else {
            return $this->response->setJSON(['status' => -1, 'msg' => '账号信息缺失']);
        }
    }
    // 首页数据
    public function getMainData()
    {
        $uId = (int)$this->request->getGetPost('uId');
        $user = $this->db->table('user')->where('id', $uId)->get()->getRowArray();
        $purchaseReqNotice = $this->db->table('pur_req')->where('purchaseId', 0)->where('description', '')->where('isDelete', 0)->countAllResults();
        $proKittingReqNotice = $this->db->table('pro_kitting_req')->where('produceId', 0)->where('description', '')->where('isDelete', 0)->countAllResults();
        $proKittingTaskNotice = $this->db->table('pro_kitting_task')->where('takeStatus', 0)->where('checkId >', 0)->where('isDelete', 0)->countAllResults();
        $proKittingTaskUpNotice = $this->db->table('pro_kitting_task_up')->where('doneStatus <', 2)->where('isDelete', 0)->countAllResults();
        $proProcessingReqNotice = $this->db->table('pro_Processing_req')->where('produceId', 0)->where('description', '')->where('isDelete', 0)->countAllResults();
        $proProcessingTaskNotice = $this->db->table('pro_Processing_task')->where('takeStatus', 0)->where('checkId >', 0)->where('isDelete', 0)->countAllResults();
        $proProcessingTaskUpNotice = $this->db->table('pro_processing_task_up')->where('doneStatus', 1)->where('isDelete', 0)->countAllResults();
        $serviceTargetNotice = $this->db->table('service')->where('disStatus <', 3)->where('FIND_IN_SET(' . $this->db->escape($user['name']) . ', targetName) >', 0, false)->where('targetAmount >', 0)->where('isDisable', 0)->where('isDelete', 0)->countAllResults();
        $planNotice = $this->db->table('plan')->where('isDisable', 0)->where('uId', $user['id'])->where('isDelete', 0)->countAllResults();
        $dutyNotice = $this->db->table('duty')->groupStart()->where('beginName', $user['name'])->orWhere('FIND_IN_SET(' . $this->db->escape($user['name']) . ', targetName) >', 0, false)->orWhere('FIND_IN_SET(' . $this->db->escape($user['department']) . ', targetName) >', 0, false)->groupEnd()->where('isDisable', 0)->where('isDelete', 0)->countAllResults();
        $deliverDullNotice = $this->db->table('deliver_bag')->where('scanName', '')->where('scanTime', '0000-00-00 00:00:00')->where('printTime !=', '0000-00-00 00:00:00')->where('printTime <=', date('Y-m-d', strtotime('-1 day')) . ' 11:59:59')->where('isDelete', 0)->countAllResults();
        $json['status'] = 200;
        $json['msg'] = 'success';
        $json['data']['purchaseReqNotice'] = $purchaseReqNotice;
        $json['data']['proKittingReqNotice'] = $proKittingReqNotice;
        $json['data']['proKittingTaskNotice'] = $proKittingTaskNotice;
        $json['data']['proKittingTaskUpNotice'] = $proKittingTaskUpNotice;
        $json['data']['proProcessingReqNotice'] = $proProcessingReqNotice;
        $json['data']['proProcessingTaskNotice'] = $proProcessingTaskNotice;
        $json['data']['proProcessingTaskUpNotice'] = $proProcessingTaskUpNotice;
        $json['data']['serviceTargetNotice'] = $serviceTargetNotice;
        $json['data']['planNotice'] = $planNotice;
        $json['data']['dutyNotice'] = $dutyNotice;
        $json['data']['deliverDullNotice'] = $deliverDullNotice;
        return $this->response->setJSON($json);
    }
    // 审批中心 数据
    public function getCheckData()
    {
        $uId = (int)$this->request->getGetPost('uId');
        $user = $this->db->table('user')->where('id', $uId)->get()->getRowArray();
        $v = [
            'clockin' => [],
            'clockinNum' => 0,
            'fillWorkHour' => [],
            'fillWorkHourNum' => 0,
            'overWorkHour' => [],
            'overWorkHourNum' => 0,
            'workHour' => [],
            'workHourNum' => 0,
            'discountReq' => [],
            'discountReqNum' => 0,
            'quoteReq' => [],
            'quoteReqNum' => 0,
            'totalNum' => 0
        ];
        if ($user) {
            $rights = explode(',', $user['lever']);
            if (in_array(3, $rights)) { // 有管理员专属权限可以查看到审批信息
                $v['clockin'] = $this->db->table('clockin')->groupStart()->where('FIND_IN_SET("请假", marks) >', 0, false)->orWhere('FIND_IN_SET("调休", marks) >', 0, false)->orWhere('FIND_IN_SET("补休", marks) >', 0, false)->orWhere('FIND_IN_SET("外勤", marks) >', 0, false)->groupEnd()->where('isCheck', 0)->where('isDelete', 0)->select(['id','clockDate','uName','marks','description','latitude','longitude',"'' AS address"])->orderBy('id', 'DESC')->get()->getResultArray();
                $v['clockinNum'] = count($v['clockin']);
                $v['fillWorkHour'] = $this->db->table('work_hour')->where('type', 0)->where('isCheck', 0)->where('isDelete', 0)->orderBy('id', 'DESC')->get()->getResultArray();
                $v['fillWorkHourNum'] = count($v['fillWorkHour']);
                $v['overWorkHour'] = $this->db->table('work_hour')->where('type', 1)->where('isCheck', 0)->where('isDelete', 0)->orderBy('id', 'DESC')->get()->getResultArray();
                $v['overWorkHourNum'] = count($v['overWorkHour']);
                $v['workHour'] = $this->db->table('work_hour')->where('type', 2)->where('isCheck', 0)->where('isDelete', 0)->orderBy('id', 'DESC')->get()->getResultArray();
                $v['workHourNum'] = count($v['workHour']);
                $v['discountReq'] = $this->db->table('discount_req')->where('checkId', 0)->where('description', '')->where('isDelete', 0)->orderBy('id', 'DESC')->get()->getResultArray();
                $v['discountReqNum'] = count($v['discountReq']);
                $v['quoteReq'] = $this->db->table('quote_req')->where('checkId', 0)->where('description', '')->where('isDelete', 0)->orderBy('id', 'DESC')->get()->getResultArray();
                $v['quoteReqNum'] = count($v['quoteReq']);
                $v['totalNum'] = $v['clockinNum'] + $v['fillWorkHourNum'] + $v['overWorkHourNum'] + $v['workHourNum'] + $v['discountReqNum'] + $v['quoteReqNum'];
            }
        }
        $json['status'] = 200;
        $json['msg'] = 'success';
        $json['data'] = $v;
        return $this->response->setJSON($json);
    }
    // 审批中心 审批
    public function checkDataCheck()
    {
        $type = (int)$this->request->getGetPost('type');
        $id = (int)$this->request->getGetPost('id');
        $value = (int)$this->request->getGetPost('value');
        $description = $this->request->getGetPost('description');
        $checkId = (int)$this->request->getGetPost('checkId');
        $checkName = $this->request->getGetPost('checkName');
        $user = $this->db->table('user')->where('id', $checkId)->get()->getRowArray();
        if ($user && $user['roleId'] == 1) { // 但是只有管理员角色才能审批
            if ($type > 0 && $id > 0 && $value > 0) {
                switch ($type) {
                    case 1:
                        $data = $this->db->table('clockin')->where('id', $id)->get()->getRowArray();
                        $this->db->transStart();
                        $this->db->table('clockin')->where('id', $id)->update(['isCheck' => $value, 'checkName' => $checkName, 'checkTime' => date('Y-m-d H:i:s')]);
                        $this->db->table('log')->insert(['log' => ($value == 1 ? '批准了' : '拒绝了') . ' ' . $data['uName'] . ' ' . $data['clockDate'] . ' 的 ' . $data['marks'] . '申请', 'name' => $checkName, 'createTime' => date('Y-m-d H:i:s')]);
                        $this->db->transComplete();
                        if ($this->db->transStatus() === false) {
                            return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚']);
                        }
                        return $this->response->setJSON(['status' => 200, 'msg' => '操作成功']);
                    case 2:
                        $data = $this->db->table('work_hour')->where('id', $id)->get()->getRowArray();
                        $this->db->transStart();
                        $this->db->table('work_hour')->where('id', $id)->update(['isCheck' => $value, 'checkName' => $checkName, 'checkTime' => date('Y-m-d H:i:s')]);
                        $this->db->table('log')->insert(['log' => ($value == 1 ? '批准了' : '拒绝了') . ' ' . $data['uName'] . ' ' . $data['workDate'] . ' 的 补班申报', 'name' => $checkName, 'createTime' => date('Y-m-d H:i:s')]);
                        $this->db->transComplete();
                        if ($this->db->transStatus() === false) {
                            return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚']);
                        }
                        return $this->response->setJSON(['status' => 200, 'msg' => '操作成功']);
                    case 3:
                        $data = $this->db->table('work_hour')->where('id', $id)->get()->getRowArray();
                        $this->db->transStart();
                        $this->db->table('work_hour')->where('id', $id)->update(['isCheck' => $value, 'checkName' => $checkName, 'checkTime' => date('Y-m-d H:i:s')]);
                        $this->db->table('log')->insert(['log' => ($value == 1 ? '批准了' : '拒绝了') . ' ' . $data['uName'] . ' ' . $data['workDate'] . ' 的 加班申报', 'name' => $checkName, 'createTime' => date('Y-m-d H:i:s')]);
                        $this->db->transComplete();
                        if ($this->db->transStatus() === false) {
                            return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚']);
                        }
                        return $this->response->setJSON(['status' => 200, 'msg' => '操作成功']);
                    case 4:
                        $data = $this->db->table('work_hour')->where('id', $id)->get()->getRowArray();
                        $this->db->transStart();
                        $this->db->table('work_hour')->where('id', $id)->update(['isCheck' => $value, 'checkName' => $checkName, 'checkTime' => date('Y-m-d H:i:s')]);
                        $this->db->table('log')->insert(['log' => ($value == 1 ? '批准了' : '拒绝了') . ' ' . $data['uName'] . ' ' . $data['workDate'] . ' 的 计时申报', 'name' => $checkName, 'createTime' => date('Y-m-d H:i:s')]);
                        $this->db->transComplete();
                        if ($this->db->transStatus() === false) {
                            return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚']);
                        }
                        return $this->response->setJSON(['status' => 200, 'msg' => '操作成功']);
                    case 5:
                        $data = $this->db->table('discount_req')->where('id', $id)->get()->getRowArray();
                        $this->db->transStart();
                        $this->db->table('discount_req')->where('id', $id)->update(['checkId' => $checkId, 'checkName' => $checkName, 'checkTime' => date('Y-m-d H:i:s'), 'description' => $description]);
                        $this->db->table('log')->insert(['log' => ($description == '' ? '批准了' : '拒绝了') . ' ' . $data['uName'] . ' ' . $data['createTime'] . ' 提交的 优惠申请，ID：' . $id . '，标题：' . $data['title'], 'name' => $checkName, 'createTime' => date('Y-m-d H:i:s')]);
                        $this->db->transComplete();
                        if ($this->db->transStatus() === false) {
                            return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚']);
                        }
                        return $this->response->setJSON(['status' => 200, 'msg' => '操作成功']);
                    case 6:
                        $data = $this->db->table('quote_req')->where('id', $id)->get()->getRowArray();
                        $this->db->transStart();
                        $this->db->table('quote_req')->where('id', $id)->update(['checkId' => $checkId, 'checkName' => $checkName, 'checkTime' => date('Y-m-d H:i:s'), 'description' => $description]);
                        $this->db->table('log')->insert(['log' => ($description == '' ? '批准了' : '拒绝了') . ' ' . $data['uName'] . ' ' . $data['createTime'] . ' 提交的 报价审批，ID：' . $id . '，标题：' . $data['title'], 'name' => $checkName, 'createTime' => date('Y-m-d H:i:s')]);
                        $this->db->transComplete();
                        if ($this->db->transStatus() === false) {
                            return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚']);
                        }
                        return $this->response->setJSON(['status' => 200, 'msg' => '操作成功']);
                    default:
                        return $this->response->setJSON(['status' => -1, 'msg' => '信息类别无效']);
                }
            }
            return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在']);
        } else {
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足']);
        }
    }
    // 打卡地址查验
    public function addressSearch()
    {
<<<<<<< HEAD
        $latitude = (float)$this->request->getGetPost('latitude');
        $longitude = (float)$this->request->getGetPost('longitude');
=======
        $latitude = $this->request->getGetPost('latitude');
        $longitude = $this->request->getGetPost('longitude');
>>>>>>> 151b415 (更新语言最新版本+最新底层框架后全部差异化代码适配重写)
        $address = address_search($latitude, $longitude);
        if ($address) {
            return $this->response->setJSON(['status' => 200, 'msg' => '查询成功', 'data' => $address]);
        } else {
            return $this->response->setJSON(['status' => -1, 'msg' => '查询失败']);
        }
    }
    // 用户数据
    public function mainData()
    {
        $uId = (int)$this->request->getGetPost('uId');
        $data['myInfo'] = $this->db->table('user')->where('id', $uId)->get()->getRowArray();
        $data['myInfo']['medal'] = $this->db->table('user_medal')->where(['date' => date('Ym', strtotime('-1 month')), 'uName' => $data['myInfo']['name'], 'isDelete' => 0])->get()->getResultArray();
        $data['myInfo']['energy'] = $data['myInfo']['lastMonthCore'] = $this->db->table('bill_achievement_info')->where(['uId' => $uId, 'isDelete' => 0])->select('sumSalary')->get()->getRowArray()['sumSalary'] ?? 0;
        if ($data['myInfo']['superiorsId'] > 0) {
            $superiorsInfo = $this->db->table('user')->where(['id' => $data['myInfo']['superiorsId'], 'isDelete' => 0])->get()->getRowArray();
            if ($superiorsInfo) {
                $data['myInfo']['superiorsInfo'] = $superiorsInfo;
                $clockinInfo = $this->db->table('clockin')->where(['uId' => $superiorsInfo['id'], 'clockDate ' => date('Y-m-d'), 'isDelete' => 0])->get()->getRowArray();
                if ($clockinInfo) {
                    if (($clockinInfo['amClock'] != '00:00:00' && $clockinInfo['pmClock'] == '00:00:00') || ($clockinInfo['amClock2'] !== '00:00:00' && $clockinInfo['pmClock2'] == '00:00:00')) {
                        $data['myInfo']['superiorsInfo']['onDuty'] = '在岗';
                    } elseif (($clockinInfo['amClock'] != '00:00:00' && $clockinInfo['pmClock'] != '00:00:00' && $clockinInfo['amClock2'] == '00:00:00') || ($clockinInfo['amClock2'] != '00:00:00' && $clockinInfo['pmClock2'] != '00:00:00')) {
                        $data['myInfo']['superiorsInfo']['onDuty'] = '离岗';
                    } else {
                        $data['myInfo']['superiorsInfo']['onDuty'] = trim(str_replace(['补卡,', ',补卡', '补卡'], '', $clockinInfo['marks']), ',');
                    }
                } else {
                    if ($superiorsInfo['id'] == 1 || $superiorsInfo['id'] == 11 || $superiorsInfo['id'] == 7 || $superiorsInfo['id'] == 8 || $superiorsInfo['id'] == 103 || $superiorsInfo['id'] == 172) {
                        $data['myInfo']['superiorsInfo']['onDuty'] = '';
                    } else {
                        $data['myInfo']['superiorsInfo']['onDuty'] = '休息';
                    }
                }
            }
        }
        $membersInfo = $this->db->table('user')->where(['superiorsId' => $data['myInfo']['id'], 'isDisable' => 0, 'isDelete' => 0])->get()->getResultArray();
        if (count($membersInfo) > 0) {
            $data['myInfo']['membersInfo'] = $membersInfo;
            foreach ($data['myInfo']['membersInfo'] as $k => $v) {
                $clockinInfo = $this->db->table('clockin')->where(['uId' => $v['id'], 'clockDate ' => date('Y-m-d'), 'isDelete' => 0])->get()->getRowArray();
                if ($clockinInfo) {
                    if (($clockinInfo['amClock'] != '00:00:00' && $clockinInfo['pmClock'] == '00:00:00') || ($clockinInfo['amClock2'] !== '00:00:00' && $clockinInfo['pmClock2'] == '00:00:00')) {
                        $data['myInfo']['membersInfo'][$k]['onDuty'] = '在岗';
                    } elseif (($clockinInfo['amClock'] != '00:00:00' && $clockinInfo['pmClock'] != '00:00:00' && $clockinInfo['amClock2'] == '00:00:00') || ($clockinInfo['amClock2'] != '00:00:00' && $clockinInfo['pmClock2'] != '00:00:00')) {
                        $data['myInfo']['membersInfo'][$k]['onDuty'] = '离岗';
                    } else {
                        $data['myInfo']['membersInfo'][$k]['onDuty'] = trim(str_replace(['补卡,', ',补卡', '补卡'], '', $clockinInfo['marks']), ',');
                    }
                } else {
                    if ($data['myInfo']['membersInfo'][$k]['id'] == 1 || $data['myInfo']['membersInfo'][$k]['id'] == 11 || $data['myInfo']['membersInfo'][$k]['id'] == 7 || $data['myInfo']['membersInfo'][$k]['id'] == 8 || $data['myInfo']['membersInfo'][$k]['id'] == 103 || $data['myInfo']['membersInfo'][$k]['id'] == 172) {
                        $data['myInfo']['membersInfo'][$k]['onDuty'] = '';
                    } else {
                        $data['myInfo']['membersInfo'][$k]['onDuty'] = '休息';
                    }
                }
            }
        }
        return $this->response->setJSON(['status' => 200, 'msg' => 'success', 'data' => $data]);
    }
    // 社保参保与薪资发放必备前置信息补录
    public function userInfoFill()
    {
        $data = $this->request->getPost();
        if (is_array($data) && count($data) > 0) {
            $affected = $this->db->table('user')->where(['id' => $data['id']])->update(array_intersect_key($data, array_flip(['hukou', 'bankNo', 'zfbNo'])));
            if ($affected == 0) {
                return $this->response->setJSON(['status' => -1, 'msg' => '补录失败']);
            } else {
                $this->db->table('log')->insert(['log' => '补录了员工信息中的社保参保与薪资发放必备前置信息', 'name' => $data['name'], 'createTime' => date('Y-m-d H:i:s')]);
                return $this->response->setJSON(['status' => 200, 'msg' => '补录成功']);
            }
        } else {
            return $this->response->setJSON(['status' => -1, 'msg' => '提交数据为空']);
        }
    }
    // 我的绩效
    public function get_user_achievement()
    {
        $uId = (int)$this->request->getGetPost('uId');
        $name = $this->request->getGetPost('name');
        $type = (int)$this->request->getGetPost('type');
        $beginDate = $type == 1 ? date('Y-m-01', strtotime(date('Y-m-01') . ' -1 month')) : date('Y-m-01');
        $endDate = $type == 1 ? date('Y-m-t', strtotime(date('Y-m-01') . ' -1 month')) : date('Y-m-d');
        if ($endDate > date('Y-m-d')) { //结束日期不允许超今天
            $endDate = date('Y-m-d');
        }
        if ($endDate >= date('Y-m-01')) { // 如果是员工自查，且查本月，则只允许每5天展示前5天前的数据。
            $endDate = date('Y-m-d', strtotime($endDate . ' -5 day'));
            if ($endDate <= date('Y-m-05')) { // 1-5号查询不展示数据
                $endDate = date('Y-m-d', strtotime(date('Y-m-01') . ' -1 day'));
            } elseif ($endDate > date('Y-m-05') && $endDate <= date('Y-m-10')) { // 6-10号每次查询都只展示5号之前数据
                $endDate = date('Y-m-05');
            } elseif ($endDate > date('Y-m-10') && $endDate <= date('Y-m-15')) { // 11-15号每次查询都只给10号之前数据
                $endDate = date('Y-m-10');
            } elseif ($endDate > date('Y-m-15') && $endDate <= date('Y-m-20')) { // 16-20号每次查询都只给15号之前数据
                $endDate = date('Y-m-15');
            } elseif ($endDate > date('Y-m-20') && $endDate <= date('Y-m-25')) { // 21-25号每次查询都只给15号之前数据
                $endDate = date('Y-m-20');
            } elseif ($endDate > date('Y-m-25')) { // 25号后每次查询都只给25号之前数据
                $endDate = date('Y-m-20');
            }
        }
        if ($uId > 0 && $name && $beginDate && $endDate) {

            // 取出业绩系数用于后面运算
            $optionValue = $this->db->table('option')->where(['optionName' => 'exp_par'])->select('optionValue')->get()->getRowArray()['optionValue'] ?? '';
            $expPar = $optionValue ? (array)json_decode($optionValue, true) : [];
            if (empty($expPar)) {
                return $this->response->setJSON(['status' => -1, 'msg' => '业绩系数获取失败！']);
            }

            // 出勤信息
            $attendance = $this->db->table('bill_attendance_info')->where(['beginDate >=' => $beginDate, 'endDate <=' => $endDate, 'uName' => $name, 'isDelete' => 0])->get()->getResultArray();

            // 采购业绩
            $performancePur['orderinfo'] = $this->db->table('bill_order_info')->where(['billDate >=' => $beginDate, 'billDate <=' => $endDate, 'billType' => 'PUR', 'uId' => $uId, 'checkId >' => 0, 'isDelete' => 0])->countAllResults();

            $performancePur['contractinfo'] = $this->db->table('bill_contract_info')->where(['paidTime >=' => $beginDate . ' 00:00:00', 'paidTime <=' => $endDate . ' 23:59:59', 'billType' => 'PUR', 'isReq !=' => 1, 'uId' => $uId, 'price >' => 0, 'paidAmount >' => 0, 'isDelete' => 0])->countAllResults();

            $performancePur['supplier'] = $this->db->table('contact')->where(['createTime >=' => $beginDate . ' 00:00:00', 'createTime <=' => $endDate . ' 23:59:59', 'uName' => $name, 'type' => 1, 'isDisable' => 0, 'isDelete' => 0])->countAllResults();

            $performancePur['expendinfo'] = 0;

            $performancePur['deAmount'] = $this->db->table('bill_contract_info')->where(['paidTime >=' => $beginDate . ' 00:00:00', 'paidTime <=' => $endDate . ' 23:59:59', 'isReq' => 2, 'uId' => $uId, 'billType' => 'PUR', 'limitPrice >' => 0, 'price >' => 0, 'paidAmount >' => 0, 'isDelete' => 0])->where('paidAmount >= amount')->select('SUM(CASE WHEN (limitPrice * 1.25 * qty) <= paidAmount THEN 0 ELSE ((limitPrice * 1.25 * qty) - paidAmount) END) AS deAmount', false)->get()->getRowArray()['deAmount'] ?? 0;

            $performancePur['core'] = intval((float)$performancePur['orderinfo'] * (float)$expPar['pur']['par1'] + (float)$performancePur['contractinfo'] * (float)$expPar['pur']['par2'] + (float)$performancePur['supplier'] * (float)$expPar['pur']['par3'] + (float)$performancePur['deAmount'] * (float)$expPar['pur']['par4']);

            // 销售业绩
            $performanceSale['bidpro'] = $this->db->table('bid_program')->select('SUM(CASE WHEN LOCATE(",", quotedName) > 0 THEN (amount / ((CHAR_LENGTH(quotedName) - CHAR_LENGTH(REPLACE(quotedName,",",""))) + 1)) ELSE amount END) AS totalAmount', false)->where(['createTime >=' => $beginDate . ' 00:00:00', 'createTime <=' => $endDate . ' 23:59:59', 'isDelete' => 0])->where('FIND_IN_SET(' . $this->db->escape($name).', quotedName) >', 0, false)->get()->getRowArray()['totalAmount'] ?? 0;

            $performanceSale['order'] = $this->db->table('bill_order')->where(['createTime >= ' => $beginDate . ' 00:00:00', 'createTime <= ' => $endDate . ' 23:59:59', 'billType' => 'SALE', 'uId' => $uId, 'checkId >' => 0, 'moneyStatus1' => 2, 'isCancel' => 0, 'isDelete' => 0])->selectSum('amount', 'totalAmount')->get()->getRowArray()['totalAmount'] ?? 0;

            $performanceSale['contract'] = $this->db->table('bill_contract')->select('SUM(CASE WHEN LOCATE(",", saler) > 0 THEN (amount / ((CHAR_LENGTH(saler) - CHAR_LENGTH(REPLACE(saler,",",""))) + 1) ) ELSE amount END) AS totalAmount', false)->where(['deliverDoneTime >= ' => $beginDate . ' 00:00:00', 'deliverDoneTime <= ' => $endDate . ' 23:59:59', 'billType' => 'SALE', 'uId' => $uId, 'checkId >' => 0, 'goodsStatus1 >' => 0, 'redoSoType' => 0, 'isDelete' => 0])->where('FIND_IN_SET(' . $this->db->escape($name) . ', saler) >', 0, false)->get()->getRowArray()['totalAmount'] ?? 0;

            $performanceSale['onlineshop'] = $this->db->table('bill_onlineshopMp_info')->selectSum('amount', 'totalAmount')->where(['beginDate >= ' => $beginDate . ' 00:00:00', 'endDate <= ' => $endDate . ' 23:59:59', 'uName' => $name, 'isDelete' => 0])->get()->getRowArray()['totalAmount'] ?? 0;

            $performanceSale['core'] = intval((float)$performanceSale['order'] * (float)$expPar['sale']['par1'] + (float)$performanceSale['contract'] * (float)$expPar['sale']['par2'] + (float)$performanceSale['onlineshop'] * (float)$expPar['sale']['par3']);

            // 配货业绩
            $pro_kitting_task_up_all = $this->db->table('pro_kitting_task_up')->selectSum('upQty', 'proQtyAll')->where(['createTime >= ' => $beginDate . ' 00:00:00', 'createTime <= ' => $endDate . ' 23:59:59', 'uName' => $name, 'isDelete' => 0])->get()->getRowArray();
            $pro_kitting_task_up_uncheck = $this->db->table('pro_kitting_task_up')->selectSum('upQty', 'proQtyUncheck')->where(['createTime >= ' => $beginDate . ' 00:00:00', 'createTime <= ' => $endDate . ' 23:59:59', 'doneStatus <' => 2, 'uName' => $name, 'isDelete' => 0])->get()->getRowArray();
            $pro_kitting_task_up_checked = $this->db->table('pro_kitting_task_up')->selectSum('upQty', 'proQty')->selectSum('amount', 'proAmount')->where(['createTime >= ' => $beginDate . ' 00:00:00', 'createTime <= ' => $endDate . ' 23:59:59', 'doneStatus' => 2, 'uName' => $name, 'isDelete' => 0])->get()->getRowArray();
            $performancePro['proQtyAll'] = intval($pro_kitting_task_up_all['proQtyAll'] ? (float)$pro_kitting_task_up_all['proQtyAll'] : 0);
            $performancePro['proQtyUncheck'] = intval($pro_kitting_task_up_uncheck['proQtyUncheck'] ? (float)$pro_kitting_task_up_uncheck['proQtyUncheck'] : 0);
            $performancePro['proQty'] = intval($pro_kitting_task_up_checked['proQty'] ? (float)$pro_kitting_task_up_checked['proQty'] : 0);
            $performancePro['core'] = intval($pro_kitting_task_up_checked['proAmount'] ? (float)$pro_kitting_task_up_checked['proAmount'] : 0);

            // 打包业绩
            $performancePack = $this->db->table('deliver_bag')->select('COUNT(CASE WHEN weight < 20 THEN no ELSE null END) AS totalCount1, SUM(CASE WHEN weight < 20 THEN weight ELSE 0 END) AS totalWeight1, COUNT(CASE company WHEN "申通快递" THEN no ELSE null END) AS totalZYCount, SUM(CASE company WHEN "申通快递" THEN amount ELSE 0 END) AS totalZYAmount, COUNT(CASE WHEN weight >= 20 THEN no ELSE null END) AS totalCount2, SUM(CASE WHEN weight >= 20 THEN weight ELSE 0 END) AS totalWeight2', false)->where(['scanTime >=' => $beginDate . ' 00:00:00', 'scanTime <=' => $endDate . ' 23:59:59', 'scanName' => $name, 'isDelete' => 0])->get()->getRowArray() ?? ['totalCount1' => 0, 'totalWeight1' => 0, 'totalZYCount' => 0, 'totalZYAmount' => 0, 'totalCount2' => 0, 'totalWeight2' => 0];

            $performancePack['core'] = intval((float)$performancePack['totalCount1'] * (float)$expPar['pack']['par1'] + (float)$performancePack['totalWeight1'] * (float)$expPar['pack']['par2'] + ((float)$expPar['pack']['par3'] * (float)$performancePack['totalZYCount'] - (float)$performancePack['totalZYAmount']) * (float)$expPar['pack']['par4'] + (float)$performancePack['totalCount2'] * (float)$expPar['pack']['par5'] + (float)$performancePack['totalWeight2'] * (float)$expPar['pack']['par6']);

            // 产品开发
            $workTask = $this->db->table('goods_task')->where(['beginDate >=' => $beginDate, 'beginDate <=' => $endDate, 'isDelete' => 0])->groupStart()->where('leader', $name)->orWhere('FIND_IN_SET(' . $this->db->escape($name) . ', other) >', 0, false)->groupEnd()->countAllResults();

            // 反馈责任
            $serviceTarget = $this->db->table('service')->select('content, targetName, targetAmount, beginDate')->where(['beginDate >=' => $beginDate, 'beginDate <=' => $endDate, 'targetAmount >' => 0, 'disStatus' => 3, 'isDelete' => 0])->where('FIND_IN_SET(' . $this->db->escape($name) . ', targetName) >', 0, false)->orderBy('id', 'ASC')->get()->getResultArray();
            foreach ($serviceTarget as $arr => $row) {
                $serviceTarget[$arr]['content'] =  strip_tags(str_replace('<i class="close iconfont icon-shanchu"></i></div>', '&nbsp;&nbsp;&#10;', str_replace('<div class="info-time">', '）', str_replace('<div class="info-username">', '（', $row['content']))));
            }

            // 工作目标
            $workPlan = $this->db->table('plan')->select('date,type,target,summary,isDisable')->where(['date >=' => $beginDate, 'date <=' => $endDate, 'uId' => $uId, 'isDelete' => 0])->orderBy('id', 'ASC')->get()->getResultArray();

            // 组装返回数据
            $json['status'] = 200;
            $json['msg'] = 'success';
            $json['attendance'] = $attendance;
            $json['performancePur'] = $performancePur;
            $json['performanceSale'] = $performanceSale;
            $json['performancePro'] = $performancePro;
            $json['performancePack'] = $performancePack;
            $json['workTask'] = $workTask;
            $json['serviceTarget'] = $serviceTarget;
            $json['workPlan'] = $workPlan;
            $json['planTypeArr'] = ['日', '周', '月', '年'];
            return $this->response->setJSON($json);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '提交的是空数据']);
    }
    // 我的考评
    public function get_user_evaluate()
    {
        $uId = (int)$this->request->getGetPost('uId');
        $name = $this->request->getGetPost('name');
        $type = (int)$this->request->getGetPost('type');
        $beginDate = $type == 1 ? date('Y-m-01', strtotime(date('Y-m-01') . ' -1 month')) : date('Y-m-01', strtotime(date('Y-m-01') . ' -2 month'));
        $endDate = $type == 1 ? date('Y-m-t', strtotime(date('Y-m-01') . ' -1 month')) : date('Y-m-t', strtotime(date('Y-m-01') . ' -2 month'));
        if ($uId > 0 && $name && $beginDate && $endDate) {
            //考评项目
            $evaluationIds = $this->db->table('user')->select('evaluationIds')->where(['id' => $uId])->get()->getRowArray()['evaluationIds'] ?? '';
            if ($evaluationIds) {
                $evaluations = array_column($this->db->table('evaluation')->select('name')->whereIn('id', explode(',', $evaluationIds))->where('isDelete', 0)->orderBy('id', 'ASC')->get()->getResultArray(), 'name');
            } else {
                $evaluations = [];
            }
            //考评得分
            $evaluateScore = $this->db->table('bill_evaluate_info')->select('score')->where(['beginDate >=' => $beginDate, 'endDate <=' => $endDate, 'uId' => $uId, 'isDelete' => 0])->get()->getRowArray()['score'] ?? 0;

            //组装返回数据
            $json['status'] = 200;
            $json['msg'] = 'success';
            $json['evaluations'] = $evaluations;
            $json['evaluateScore'] = $evaluateScore;
            return $this->response->setJSON($json);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '没有此用户数据']);
    }
    // 我的日志
    public function get_user_log()
    {
        $current = max((int)$this->request->getGetPost('current'), 1);
        $pageSize = max((int)$this->request->getGetPost('pageSize'), 20);
        $uName = $this->request->getGetPost('uName');
        $builder = $this->db->table('log');
        $builder->where('isDelete', 0);
        if ($uName) {
            $builder->where('name', $uName);
        }
        // 分页数据：先克隆 builder 再复用条件
        $list  = (clone $builder)->orderBy('id', 'DESC')->limit($pageSize)->offset($pageSize * ($current - 1))->get()->getResultArray();
        $total = $builder->countAllResults();
        $json['status'] = 200;
        $json['msg'] = 'success';
        $json['data']['current'] = $current;
        $json['data']['total'] = $total;
        $json['data']['pages'] = ceil($total / $pageSize);
        $json['data']['list'] = $list;
        return $this->response->setJSON($json);
    }
    // 我的权限
    public function get_user_rights()
    {
        $uId = (int)$this->request->getGetPost('uId');
        if ($uId) {
            $lever = $this->db->table('user')->where(['id' => $uId])->get()->getRowArray()['lever'] ?? '';
            if ($lever) {
                $rightsArr = $this->db->table('right')->select('id, name, parentId, path, level')->whereIn('id', explode(',', $lever))->where(['isDisable' => 0, 'isDelete' => 0])->orderBy('id', 'ASC')->get()->getResultArray();
            } else {
                $rightsArr = [];
            }
            return $this->response->setJSON(['status' => 200, 'msg' => 'success', 'data' => $rightsArr]);
        } else {
            return $this->response->setJSON(['status' => -1, 'msg' => '没有对应的数据']);
        }
    }
    // 修改资料
    public function changeInfo()
    {
        $data = $this->request->getPost();
        if (is_array($data) && count($data) > 0) {
            $user = $this->db->table('user')->where(['id' => $data['uId']])->get()->getRowArray();
            $this->db->transStart();
            $this->db->table('user')->where(['id' => $data['uId']])->update(['wechat' => $data['wechat'], 'mobile' => $data['mobile'], 'birthdaySolar' => $data['birthdaySolar'], 'birthdayLunar' => $data['birthdayLunar'], 'animal' => $data['animal'], 'astro' => $data['astro']]);
            $this->db->table('log')->insert(['log' => '修改了自身资料，方式：小程序', 'name' => $user['name'], 'createTime' => date('Y-m-d H:i:s')]);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '资料修改失败']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '资料修改成功']);
        } else {
            return $this->response->setJSON(['status' => -1, 'msg' => '提交数据不完整']);
        }
    }
    // 上传头像
    public function uploadAvatar()
    {
        set_time_limit(0);
        session_write_close();
        if (empty($_FILES)){
            return $this->response->setJSON(['status' => -1, 'msg' => '没有上传文件！']);
        };
        $uppath = 'data/upfile/avatar/';
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
        return $this->response->setJSON(['status' => 200, 'msg' => '提交成功', 'data' => $target_file]);
    }
    // 修改头像
    public function changeAvatar()
    {
        $data = $this->request->getPost();
        if (is_array($data) && count($data) > 0) {
            $user = $this->db->table('user')->where(['id' => $data['uId']])->get()->getRowArray();
            $this->db->transStart();
            $this->db->table('user')->where(['id' => $data['uId']])->update(['avatar' => $data['avatar']]);
            $this->db->table('log')->insert(['log' => '修改了头像，方式：小程序', 'name' => $user['name'], 'createTime' => date('Y-m-d H:i:s')]);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '头像修改失败']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '头像修改成功']);
        } else {
            return $this->response->setJSON(['status' => -1, 'msg' => '提交数据不完整']);
        }
    }
    // 修改密码
    public function changePwd()
    {
        $data = $this->request->getPost();
        if (is_array($data) && count($data) > 0) {
            $data['new'] = md5($data['new']);
            $user = $this->db->table('user')->where(['id' => $data['uId']])->get()->getRowArray();
            $this->db->transStart();
            $this->db->table('user')->where(['id' => $data['uId']])->update(['userPwd' => $data['new']]);
            $this->db->table('log')->insert(['log' => '修改了登陆密码，方式：小程序', 'name' => $user['name'], 'createTime' => date('Y-m-d H:i:s')]);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '密码修改失败']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '密码修改成功']);
        } else {
            return $this->response->setJSON(['status' => -1, 'msg' => '提交数据不完整']);
        }
    }


    // 应聘登记
    public function applicantReg()
    {
        $data = $this->request->getPost();
        if (is_array($data) && count($data) > 0) {
            if ($data['mobile'] && (isset($data['wechat']) == false || $data['wechat'] == '')) {
                $data['wechat'] = $data['mobile'];
            }
            $parentId = $this->db->table('category')->where(['name' => $data['position']])->get()->getRowArray()['parentId'] ?? 0;
            if ($parentId > 0) {
                $data['department'] = $this->db->table('category')->where(['id' => $parentId])->get()->getRowArray()['name'] ?? $data['department'];
            }
            $data['joinDate'] = date('Y-m-d');
            if($this->db->table('applicant')->select('id')->groupStart()->where('sfzNo', $data['sfzNo'])->orWhere('wechat', $data['wechat'])->orWhere('mobile ', $data['mobile'])->groupEnd()->where('isDelete', 0)->countAllResults() > 0) {
                return $this->response->setJSON(['status' => -1, 'msg' => '已登记过']);
            }
            $this->db->table('applicant')->insert($data);
            $resid = $this->db->insertID();
            if ($resid) {
                return $this->response->setJSON(['status' => 200, 'msg' => '登记成功', 'data' => ['id' => $resid]]);
            }
            return $this->response->setJSON(['status' => -1, 'msg' => '登记失败']);
        } else {
            return $this->response->setJSON(['status' => -1, 'msg' => '提交数据不完整']);
        }
    }


    // 打卡处理
    public function clockIn()
    {
        $clockDate = $this->request->getGetPost('clockDate');
        $clockDate = strlen($clockDate) > 0 ? $clockDate : date('Y-m-d');
        if ($clockDate !== date('Y-m-d')) {
            return $this->response->setJSON(['status' => -1, 'msg' => '只允许当天打卡']);
        }
        $hour = (int)date('H');
        if ($hour > 23 || $hour < 7) {
            return $this->response->setJSON(['status' => -1, 'msg' => '非打卡时间段']);
        }
        $type = (int)$this->request->getGetPost('type');
        $latitude = (float)$this->request->getGetPost('latitude');
        $longitude = (float)$this->request->getGetPost('longitude');
        $uId = (int)$this->request->getGetPost('uId');
        $clock = date('H:i:s');
        $uName = $this->db->table('user')->where(['id' => $uId])->get()->getRowArray()['name'];
        $clockInData = $this->db->table('clockin')->where(['clockDate' => $clockDate, 'uId' => $uId])->get()->getRowArray();
        $data['latitude'] = $latitude;
        $data['longitude'] = $longitude;
        if (is_countable($clockInData) && count($clockInData) > 0) {
            if ($type == 1) { // 上班打卡
                if ($clockInData['amClock'] !== '00:00:00') {
                    return $this->response->setJSON(['status' => -1, 'msg' => '已打过卡']);
                }
                $data['amClock'] = $clock;
                //赋默认值迟到，避免打卡人员不选迟到原因。
                if ($clock >= '08:01:00') {
                    $data['marks'] = $clockInData['marks'] == '' ? '迟到' : (trim(str_replace(',,', ',', str_replace('迟到', '', $clockInData['marks'])), ',') == '' ? '迟到' : ($clockInData['marks'] . ',迟到'));
                }
                $data['isDelete'] = 0;
                $this->db->transStart();
                $this->db->table('clockin')->where(['clockDate' => $clockDate, 'uId' => $uId])->update($data);
                $this->db->table('log')->insert(['log' => '进行了 ' . $clockDate . ' 的上班打卡', 'name' => $uName, 'createTime' => date('Y-m-d H:i:s')]);
                $this->db->transComplete();
                if ($this->db->transStatus() === false) {
                    return $this->response->setJSON(['status' => -2, 'msg' => '打卡失败']);
                }
                return $this->response->setJSON(['status' => 200, 'msg' => '打卡成功', 'data' => $clock]);
            } elseif ($type == 2) { // 下班打卡
                $data['pmClock'] = $clock;
                //赋默认值早退，避免打卡人员不选早退原因。
                if ($clock < '18:00:00') {
                    $data['marks'] = $clockInData['marks'] == '' ? '早退' : (trim(str_replace(',,', ',', str_replace('早退', '', $clockInData['marks'])), ',') == '' ? '早退' : ($clockInData['marks'] . ',早退'));
                } else {
                    $data['marks'] = trim(str_replace(',,', ',', str_replace('早退', '', $clockInData['marks'])), ',');
                }
                $data['isDelete'] = 0;
                $this->db->transStart();
                $this->db->table('clockin')->where(['clockDate' => $clockDate, 'uId' => $uId])->update($data);
                $this->db->table('log')->insert(['log' => '进行了 ' . $clockDate . ' 的下班打卡', 'name' => $uName, 'createTime' => date('Y-m-d H:i:s')]);
                $this->db->transComplete();
                if ($this->db->transStatus() === false) {
                    return $this->response->setJSON(['status' => -2, 'msg' => '打卡失败']);
                }
                return $this->response->setJSON(['status' => 200, 'msg' => '打卡成功', 'data' => $clock]);
            } elseif ($type == 3) { // 二次上班打卡
                if ($clockInData['amClock2'] !== '00:00:00') {
                    return $this->response->setJSON(['status' => -1, 'msg' => '已打过卡']);
                }
                if (strtotime($clock) < strtotime($clockInData['pmClock'] . ' +5 minutes')) {
                    return $this->response->setJSON(['status' => -1, 'msg' => '至少间隔5分钟']);
                }
                $data['amClock2'] = $clock;
                $data['isDelete'] = 0;
                $this->db->transStart();
                $this->db->table('clockin')->where(['clockDate' => $clockDate, 'uId' => $uId])->update($data);
                $this->db->table('log')->insert(['log' => '进行了 ' . $clockDate . ' 的二次上班打卡', 'name' => $uName, 'createTime' => date('Y-m-d H:i:s')]);
                $this->db->transComplete();
                if ($this->db->transStatus() === false) {
                    return $this->response->setJSON(['status' => -2, 'msg' => '打卡失败']);
                }
                return $this->response->setJSON(['status' => 200, 'msg' => '打卡成功', 'data' => $clock]);
            } elseif ($type == 4) { // 二次下班打卡
                $data['pmClock2'] = $clock;
                $data['isDelete'] = 0;
                $this->db->transStart();
                $this->db->table('clockin')->where(['clockDate' => $clockDate, 'uId' => $uId])->update($data);
                $this->db->table('log')->insert(['log' => '进行了 ' . $clockDate . ' 的二次下班打卡', 'name' => $uName, 'createTime' => date('Y-m-d H:i:s')]);
                $this->db->transComplete();
                if ($this->db->transStatus() === false) {
                    return $this->response->setJSON(['status' => -2, 'msg' => '打卡失败']);
                }
                return $this->response->setJSON(['status' => 200, 'msg' => '打卡成功', 'data' => $clock]);
            } elseif ($type == 5) { // 三次上班打卡
                if ($clockInData['amClock3'] !== '00:00:00') {
                    return $this->response->setJSON(['status' => -1, 'msg' => '已打过卡']);
                }
                if (strtotime($clock) < strtotime($clockInData['pmClock3'] . ' +5 minutes')) {
                    return $this->response->setJSON(['status' => -1, 'msg' => '至少间隔5分钟']);
                }
                $data['amClock3'] = $clock;
                $data['isDelete'] = 0;
                $this->db->transStart();
                $this->db->table('clockin')->where(['clockDate' => $clockDate, 'uId' => $uId])->update($data);
                $this->db->transComplete();
                if ($this->db->transStatus() === false) {
                    return $this->response->setJSON(['status' => -2, 'msg' => '打卡失败']);
                }
                return $this->response->setJSON(['status' => 200, 'msg' => '打卡成功', 'data' => $clock]);
            } elseif ($type == 6) { // 三次下班打卡
                $data['pmClock3'] = $clock;
                $data['isDelete'] = 0;
                $this->db->transStart();
                $this->db->table('clockin')->where(['clockDate' => $clockDate, 'uId' => $uId])->update($data);
                $this->db->table('log')->insert(['log' => '进行了 ' . $clockDate . ' 的三次下班打卡', 'name' => $uName, 'createTime' => date('Y-m-d H:i:s')]);
                $this->db->transComplete();
                if ($this->db->transStatus() === false) {
                    return $this->response->setJSON(['status' => -2, 'msg' => '打卡失败']);
                }
                return $this->response->setJSON(['status' => 200, 'msg' => '打卡成功', 'data' => $clock]);
            } else {
                return $this->response->setJSON(['status' => -1, 'msg' => '打卡类型错误']);
            }
        } else {
            $data['uId'] = $uId;
            $data['uName'] = $uName;
            $data['clockDate'] = $clockDate;
            $data['amClock'] = $data['pmClock'] = '00:00:00';
            $data['marks'] = '';
            if ($type == 1) { //上班打卡
                $data['amClock'] = $clock;
                //赋值默认值迟到，避免打卡人员不选迟到原因。
                $clock >= '08:01:00' && $data['marks'] = '迟到';
            } elseif ($type == 2) { //下班打卡
                $data['pmClock'] = $clock;
                //赋值默认值早退，避免打卡人员不选早退原因。
                $clock < '18:00:00' && $data['marks'] = '早退';
            }
            $data['createTime'] = date('Y-m-d H:i:s');
            $data['isDelete'] = 0;
            $this->db->transStart();
            $this->db->table('clockin')->insert($data);
            $this->db->table('log')->insert(['log' => '进行了 ' . $clockDate . ' 的' . ($type == 1 ? '上班' : '下班') . '打卡', 'name' => $uName, 'createTime' => date('Y-m-d H:i:s')]);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -2, 'msg' => '打卡失败']);
            } else {
                return $this->response->setJSON(['status' => 200, 'msg' => '打卡成功', 'data' => $clock]);
            }
        }
    }
    // 打卡状态标注处理
    public function clockInMark()
    {
        $clockInType = (int)$this->request->getGetPost('clockInType');
        $mark = $this->request->getGetPost('mark');
        $uId = (int)$this->request->getGetPost('uId');
        $clockDate = date('Y-m-d');
        $oldMarks = $this->db->table('clockin')->where(['clockDate' => $clockDate, 'uId' => $uId])->get()->getRowArray()['marks'] ?? '';
        //去掉上次打卡时默认赋值的迟到早退原因，追加更新为用户自己选择的原因
        if ($clockInType == 1) {
            $oldMarks = trim(str_replace(',,', ',', str_replace('迟到', '', $oldMarks)), ',');
        } else {
            $oldMarks = trim(str_replace(',,', ',', str_replace('早退', '', $oldMarks)), ',');
        }
        $oldMarksArr = $oldMarks == '' ? [] : explode(',', $oldMarks);
        $nowMarkArr = explode(',', $mark);
        $newMarks = implode(',', array_unique(array_merge($oldMarksArr, $nowMarkArr)));
        $affected = $this->db->table('clockin')->where(['clockDate' => $clockDate, 'uId' => $uId])->update(['marks' => $newMarks, 'isDelete' => 0]);
        if ($affected == 0) {
            return $this->response->setJSON(['status' => -1, 'msg' => '打卡失败']);
        } else {
            return $this->response->setJSON(['status' => 200, 'msg' => '打卡成功', 'data' => ['marks' => $newMarks]]);
        }
    }
    // 断网缓存的打卡信息提交
    public function upClockCacheData()
    {
        $clockCacheData = $this->request->getGetPost('clockCacheData');
        $clockCacheData = (array)json_decode($clockCacheData, true);
        $uId = (int)$this->request->getGetPost('uId');
        $uName = $this->db->table('user')->where(['id' => $uId])->get()->getRowArray()['name'] ?? '';
        if (count($clockCacheData) > 0 && $uName) {
            $this->db->transStart();
            foreach ($clockCacheData as $arr => $row) {
                $oldData = $this->db->table('clockin')->where(['clockDate' => $row['clockDate'], 'uId' => $uId])->get()->getRowArray();
                if ($oldData) {
                    $amClock = $oldData['amClock'] !== '00:00:00' ? $oldData['amClock'] : ($row['amClock'] ?? '00:00:00');
                    $pmClock = $row['pmClock'] ?? $oldData['pmClock'];
                    $amClock2 = $oldData['amClock2'] !== '00:00:00' ? $oldData['amClock2'] : ($row['amClock2'] ?? '00:00:00');
                    $pmClock2 = $row['pmClock2'] ?? $oldData['pmClock2'];
                    $amClock3 = $oldData['amClock3'] !== '00:00:00' ? $oldData['amClock3'] : ($row['amClock3'] ?? '00:00:00');
                    $pmClock3 = $row['pmClock3'] ?? $oldData['pmClock3'];
                    $oldMarksArr = $oldData['marks'] ? explode(',', $oldData['marks']) : [];
                    $nowMarksArr = isset($row['marks']) ? explode(',', $row['marks']) : [];
                    $newMarks = implode(',', array_unique(array_merge($oldMarksArr, $nowMarksArr))); // 先合并数组、再数组去重、再数组转字符串
                    $description = isset($row['description']) ? ($row['description'] . ' ' . $oldData['description']) : $oldData['description'];
                    $latitude = $row['latitude'] ?? 0;
                    $longitude = $row['longitude'] ?? 0;
                    $status = 1;
                } else {
                    $amClock = $row['amClock'] ?? '00:00:00';
                    $pmClock = $row['pmClock'] ?? '00:00:00';
                    $amClock2 = $row['amClock2'] ?? '00:00:00';
                    $pmClock2 = $row['pmClock2'] ?? '00:00:00';
                    $amClock3 = $row['amClock3'] ?? '00:00:00';
                    $pmClock3 = $row['pmClock3'] ?? '00:00:00';
                    $nowMarksArr = isset($row['marks']) ? explode(',', $row['marks']) : [];
                    $newMarks = implode(',', $nowMarksArr);
                    $description = isset($row['description']) ? $row['description'] : '';
                    $latitude = $row['latitude'] ?? 0;
                    $longitude = $row['longitude'] ?? 0;
                    $status = 1;
                }
                // 有则更新，无则插入
                if ($this->db->table('clockin')->where(['clockDate' => $row['clockDate'], 'uId' => $uId])->countAllResults() > 0) {
                    $this->db->table('clockin')->where(['clockDate' => $row['clockDate'], 'uId' => $uId])->update([
                        'amClock' => $amClock,
                        'pmClock' => $pmClock,
                        'amClock2' => $amClock2,
                        'pmClock2' => $pmClock2,
                        'amClock3' => $amClock3,
                        'pmClock3' => $pmClock3,
                        'marks' => $newMarks,
                        'status' => $status,
                        'description' => $description,
                        'latitude' => $latitude,
                        'longitude' => $longitude
                    ]);
                } else {
                    $this->db->table('clockin')->insert([
                        'uId' => $uId,
                        'uName' => $uName,
                        'clockDate' => $row['clockDate'],
                        'amClock' => $amClock,
                        'pmClock' => $pmClock,
                        'amClock2' => $amClock2,
                        'pmClock2' => $pmClock2,
                        'amClock3' => $amClock3,
                        'pmClock3' => $pmClock3,
                        'marks' => $newMarks,
                        'status' => $status,
                        'description' => $description,
                        'latitude' => $latitude,
                        'longitude' => $longitude,
                        'createTime' => date('Y-m-d H:i:s')
                    ]);
                }
            }
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '打卡同步成功']);
        } else {
            return $this->response->setJSON(['status' => -1, 'msg' => '提交的数据为空']);
        }
    }
    // 获取当天打卡信息
    public function getClockInInfo()
    {
        $clockDate = $this->request->getGetPost('clockDate');
        $clockDate = strlen($clockDate) > 0 ? $clockDate : date('Y-m-d');
        $uId = (int)$this->request->getGetPost('uId');
        $clockInData = $this->db->table('clockin')->where(['clockDate' => $clockDate, 'uId' => $uId, 'isDelete' => 0])->get()->getRowArray();
        if (is_countable($clockInData) && count($clockInData) > 0) {
            $marksArr = explode(',', $clockInData['marks']);
            if (in_array('补班', $marksArr)) {
                $workHours = $this->db->table('work_hour')->where(['workDate' => $clockInData['clockDate'], 'uName' => $clockInData['uName'], 'type' => 0, 'isDelete' => 0])->get()->getRowArray()['workHours'] ?? 0;
                $index = array_search('补班', $marksArr);
                if ($index !== false) {
                    unset($marksArr[$index]);
                }
                array_push($marksArr, '补班' . $workHours . '小时');
            }
            if (in_array('加班', $marksArr)) {
                $workHours = $this->db->table('work_hour')->where(['workDate' => $clockInData['clockDate'], 'uName' => $clockInData['uName'], 'type' => 1, 'isDelete' => 0])->get()->getRowArray()['workHours'] ?? 0;
                $index = array_search('加班', $marksArr);
                if ($index !== false) {
                    unset($marksArr[$index]);
                }
                array_push($marksArr, '加班' . $workHours . '小时');
            }
            if (in_array('计时', $marksArr)) {
                $workHours = $this->db->table('work_hour')->where(['workDate' => $clockInData['clockDate'], 'uName' => $clockInData['uName'], 'type' => 2, 'isDelete' => 0])->get()->getRowArray()['workHours'] ?? 0;
                $index = array_search('计时', $marksArr);
                if ($index !== false) {
                    unset($marksArr[$index]);
                }
                array_push($marksArr, '计时' . $workHours . '小时');
            }
            $clockInData['marks'] = implode(',', $marksArr);
            $clockInData['isToday'] = $clockDate == date('Y-m-d') ? 1 : 0;
            return $this->response->setJSON(['status' => 200, 'msg' => '获取信息成功', 'data' => $clockInData]);
        } else {
            return $this->response->setJSON(['status' => -1, 'msg' => '当天无打卡信息']);
        }
    }
    // 获取所有标注信息
    public function getMarks()
    {
        $uId = (int)$this->request->getGetPost('uId');
        $marksData = $this->db->table('clockin')->select('clockDate,amClock,pmClock,amClock2,pmClock2,amClock3,pmClock3,marks')->where(['clockDate >=' => date('Y-m-01', strtotime(date('Y-m-01') . " -1 month")), 'uId' => $uId, 'isDelete' => 0])->orderBy('id', 'DESC')->limit(100)->get()->getResultArray();
        if (is_countable($marksData) && count($marksData) > 0) {
            $v = [];
            foreach ($marksData as $arr => $row) {
                $v[$arr]['date'] = $row['clockDate'];
                if ($row['marks'] == '') {
                    if ($row['amClock'] == '00:00:00' || $row['pmClock'] == '00:00:00') {
                        $v[$arr]['info'] = '遗漏';
                    } else {
                        $v[$arr]['info'] = '正常';
                    }
                } else {
                    if ((($row['amClock'] != '00:00:00' || $row['pmClock'] != '00:00:00') && ($row['amClock'] == '00:00:00' || $row['pmClock'] == '00:00:00')) || (($row['amClock2'] != '00:00:00' || $row['pmClock2'] != '00:00:00') && ($row['amClock2'] == '00:00:00' || $row['pmClock2'] == '00:00:00')) || (($row['amClock3'] != '00:00:00' || $row['pmClock3'] != '00:00:00') && ($row['amClock3'] == '00:00:00' || $row['pmClock3'] == '00:00:00'))) {
                        $v[$arr]['info'] = $row['marks'] . ',遗漏';
                    } else {
                        $v[$arr]['info'] = $row['marks'];
                    }
                }
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '获取标注成功', 'data' => $v]);
        } else {
            return $this->response->setJSON(['status' => -1, 'msg' => '获取标注失败']);
        }
    }
    // 获取当月应出勤天数、已出勤天数、迟到次数、下月应出勤天数
    public function getCurMonthAttDays()
    {
        $uId = (int)$this->request->getGetPost('uId');
        $beginDate = date('Y-m-01');
        $endDate = date('Y-m-t');
        $user = $this->db->table('user')->where('id', $uId)->get()->getRowArray();
        $totalWorkinSeconds = $workDays = $lateTimes = 0;
        $list = $this->db->table('clockin')->where(['clockDate >=' => $beginDate, 'clockDate <=' => $endDate, 'uId' => $uId, 'isDelete' => 0])->get()->getResultArray();
        foreach ($list as $arr => $row) {
            // 计算上班的秒数
            $workinSeconds = 0;
            // 有完整首次上下班打卡
            if ($row['amClock'] != '00:00:00' && $row['pmClock'] != '00:00:00') {
                //早于8点和晚于18点的时间不算工作时间
                $row['amClock'] < '08:00:00' && $row['amClock'] = '08:00:00';
                $row['pmClock'] > '18:00:00' && $row['pmClock'] = '18:00:00';
                $workinSeconds += (strtotime(date('Y-m-d') . ' ' . $row['pmClock']) - strtotime(date('Y-m-d') . ' ' . $row['amClock']));
            }
            // 有完整二次上下班打卡
            if ($row['amClock2'] != '00:00:00' && $row['pmClock2'] != '00:00:00') {
                //早于8点和晚于18点的时间不算工作时间
                $row['amClock2'] < '08:00:00' && $row['amClock2'] = '08:00:00';
                $row['pmClock2'] > '18:00:00' && $row['pmClock2'] = '18:00:00';
                $workinSeconds += (strtotime(date('Y-m-d') . ' ' . $row['pmClock2']) - strtotime(date('Y-m-d') . ' ' . $row['amClock2']));
            }
            // 有完整三次上下班打卡
            if ($row['amClock3'] != '00:00:00' && $row['pmClock3'] != '00:00:00') {
                //早于8点和晚于18点的时间不算工作时间
                $row['amClock3'] < '08:00:00' && $row['amClock3'] = '08:00:00';
                $row['pmClock3'] > '18:00:00' && $row['pmClock3'] = '18:00:00';
                $workinSeconds += (strtotime(date('Y-m-d') . ' ' . $row['pmClock3']) - strtotime(date('Y-m-d') . ' ' . $row['amClock3']));
            }
            // 首次上班打卡早于12点且下班打卡晚于12点，上班小时数要减去午休的时间
            if ($row['amClock'] != '00:00:00' && $row['amClock'] <= '12:00:00' && $row['pmClock'] > '12:00:00') {
                $midrestTime = min(strtotime(date('Y-m-d') . ' ' . $row['pmClock']) - strtotime(date('Y-m-d') . ' 12:00:00'), 3600);
                $workinSeconds -= $midrestTime;
            }
            // 二次上班打卡早于12点且二次下班打卡晚于12点，上班小时数要减去午休的时间
            if ($row['amClock2'] != '00:00:00' && $row['amClock2'] <= '12:00:00' && $row['pmClock2'] > '12:00:00') {
                $midrestTime = min(strtotime(date('Y-m-d') . ' ' . $row['pmClock2']) - strtotime(date('Y-m-d') . ' 12:00:00'), 3600);
                $workinSeconds -= $midrestTime;
            }
            // 三次上班打卡早于12点且三次下班打卡晚于12点，上班小时数要减去午休的时间
            if ($row['amClock3'] != '00:00:00' && $row['amClock3'] <= '12:00:00' && $row['pmClock3'] > '12:00:00') {
                $midrestTime = min(strtotime(date('Y-m-d') . ' ' . $row['pmClock3']) - strtotime(date('Y-m-d') . ' 12:00:00'), 3600);
                $workinSeconds -= $midrestTime;
            }
            // 首次上班打卡在12点~13点之间且有下班打卡，上班小时数要减去午休的时间
            if ($row['amClock'] > '12:00:00' && $row['amClock'] < '13:00:00' && $row['pmClock'] != '00:00:00') {
                if ($row['pmClock'] > '12:00:00' && $row['pmClock'] <= '13:00:00') {
                    $midrestTime = strtotime(date('Y-m-d') . ' ' . $row['pmClock']) - strtotime(date('Y-m-d') . ' ' . $row['amClock']);
                } elseif ($row['pmClock'] > '13:00:00') {
                    $midrestTime = strtotime(date('Y-m-d') . ' 13:00:00') - strtotime(date('Y-m-d') . ' ' . $row['amClock']);
                } else {
                    $midrestTime = 0;
                }
                $workinSeconds -= $midrestTime;
            }
            // 二次上班打卡在12点~13点之间且有下班打卡，上班小时数要减去午休的时间
            if ($row['amClock2'] > '12:00:00' && $row['amClock2'] < '13:00:00' && $row['pmClock2'] != '00:00:00') {
                if ($row['pmClock2'] > '12:00:00' && $row['pmClock2'] <= '13:00:00') {
                    $midrestTime = strtotime(date('Y-m-d') . ' ' . $row['pmClock2']) - strtotime(date('Y-m-d') . ' ' . $row['amClock2']);
                } elseif ($row['pmClock2'] > '13:00:00') {
                    $midrestTime = strtotime(date('Y-m-d') . ' 13:00:00') - strtotime(date('Y-m-d') . ' ' . $row['amClock2']);
                } else {
                    $midrestTime = 0;
                }
                $workinSeconds -= $midrestTime;
            }
            // 三次上班打卡在12点~13点之间且有下班打卡，上班小时数要减去午休的时间
            if ($row['amClock3'] > '12:00:00' && $row['amClock3'] < '13:00:00' && $row['pmClock3'] != '00:00:00') {
                if ($row['pmClock3'] > '12:00:00' && $row['pmClock3'] <= '13:00:00') {
                    $midrestTime = strtotime(date('Y-m-d') . ' ' . $row['pmClock3']) - strtotime(date('Y-m-d') . ' ' . $row['amClock3']);
                } elseif ($row['pmClock3'] > '13:00:00') {
                    $midrestTime = strtotime(date('Y-m-d') . ' 13:00:00') - strtotime(date('Y-m-d') . ' ' . $row['amClock3']);
                } else {
                    $midrestTime = 0;
                }
                $workinSeconds -= $midrestTime;
            }
            // 如果是值班导致的上班时间缺失，要补上扣掉的上班时长
            if (strpos($row['marks'], '值班') !== false) {
                if ($row['pmClock'] >= '16:40:00' && $row['pmClock'] <= '18:00:00') {
                    $workinSeconds += (strtotime(date('Y-m-d') . ' 18:00:00') - strtotime(date('Y-m-d') . ' ' . $row['pmClock']));
                }
                if ($row['amClock'] >= '08:00:00' && $row['amClock'] <= '09:00:00') {
                    $workinSeconds += (strtotime(date('Y-m-d') . ' ' . $row['amClock']) - strtotime(date('Y-m-d') . ' 08:00:00'));
                }
                if ($row['amClock'] > '09:00:00') {
                    $workinSeconds += 3600;
                }
                if ($row['pmClock2'] >= '16:40:00' && $row['pmClock2'] <= '18:00:00') {
                    $workinSeconds += (strtotime(date('Y-m-d') . ' 18:00:00') - strtotime(date('Y-m-d') . ' ' . $row['pmClock2']));
                }
                if ($row['amClock2'] >= '08:00:00' && $row['amClock2'] <= '09:00:00') {
                    $workinSeconds += (strtotime(date('Y-m-d') . ' ' . $row['amClock2']) - strtotime(date('Y-m-d') . ' ' . $row['pmClock']));
                }
                if ($row['pmClock3'] >= '16:40:00' && $row['pmClock3'] <= '18:00:00') {
                    $workinSeconds += (strtotime(date('Y-m-d') . ' 18:00:00') - strtotime(date('Y-m-d') . ' ' . $row['pmClock3']));
                }
                if ($row['amClock3'] >= '08:00:00' && $row['amClock3'] <= '09:00:00') {
                    $workinSeconds += (strtotime(date('Y-m-d') . ' ' . $row['amClock3']) - strtotime(date('Y-m-d') . ' ' . $row['pmClock2']));
                }
            }
            // 处理当天结果数据
            $totalWorkinSeconds += $workinSeconds;
            strstr($row['marks'], '迟到') && $lateTimes += 1;
        }
        // 获取补班、加班的小时数
        $fillHours = (float)$this->db->table('work_hour')->selectSum('workHours', 'fillHours')->where(['workDate >=' => $beginDate, 'workDate <=' => $endDate, 'uName' => $user['name'], 'type' => 0, 'isCheck' => 1, 'isDelete' => 0])->get()->getRowArray()['fillHours'] ?? 0;
        $overtimeHours = (float)$this->db->table('work_hour')->selectSum('workHours', 'overtimeHours')->where(['workDate >=' => $beginDate, 'workDate <=' => $endDate, 'uName' => $user['name'], 'type' => 1, 'isCheck' => 1, 'isDelete' => 0])->get()->getRowArray()['overtimeHours'] ?? 0;
        // 加上补班、加班的小时数，再计算出勤天数
        $workDays = floor(($totalWorkinSeconds / 3600 + $fillHours + $overtimeHours) / 9 * 10) / 10;
        // 如果工作天数小数位是9，但缺失时间小于600秒，则向上取整为整数天。
        if (strpos(strval($workDays), '.') !== false && explode('.', strval($workDays))[1] == '9' && (ceil($workDays) * 9 * 3600 - $totalWorkinSeconds) < 600) {
            $workDays = ceil($workDays);
        }
        // 获取当月应出勤天数
        $mustDayNum = $this->db->table('attendance_plan')->select('m' . intval(date('m')))->where(['year' => intval(date('Y')), 'isDelete' => 0])->get()->getRowArray()['m' . intval(date('m'))] ?? '?';
        // 获取下月应出勤天数
        $mustDayNum_nextMonth = $this->db->table('attendance_plan')->select('m' . intval(date('m', strtotime(date('Y-m-01') . ' +1 month'))))->where(['year' => intval(date('Y', strtotime(date('Y-m-01') . ' +1 month'))), 'isDelete' => 0])->get()->getRowArray()['m' . intval(date('m', strtotime(date('Y-m-01') . ' +1 month')))] ?? '?';
        return $this->response->setJSON(['status' => 200, 'msg' => 'success', 'data' => ['showMustDayNum' => $user['joinDate'] >= $beginDate ? 0 : 1, 'mustDayNum' => $mustDayNum, 'workDays' => $workDays, 'mustDayNum_nextMonth' => $mustDayNum_nextMonth, 'lateTimes' => $lateTimes]]);
    }
    // 获取每月补卡次数限制、本月剩余补卡次数
    public function getFillClockinTimes()
    {
        $uId = (int)$this->request->getGetPost('uId');
        $fillClockinTimes = intval(((array)json_decode($this->db->table('option')->select('optionValue')->where('optionName', 'system')->get()->getRowArray()['optionValue'] ?? '', true))['fillClockinTimes'] ?? 5);
        $hasUseTimes = $this->db->table('clockin')->where(['clockDate >=' => date('Y-m-01'), 'clockDate <=' => date('Y-m-t'), 'uId' => $uId, 'isDelete' => 0])->where('FIND_IN_SET("补卡", marks) >', 0, false)->countAllResults();
        $fillClockinTimesRest = intval($fillClockinTimes) - intval($hasUseTimes);
        return $this->response->setJSON(['status' => 200, 'msg' => 'success', 'data' => ['fillClockinTimes' => $fillClockinTimes, 'fillClockinTimesRest' => max($fillClockinTimesRest, 0)]]);
    }
    // （请假、调休、补休、外勤、值班、补班、加班、计时）申请提交
    public function clockOutSubmit()
    {
        $clockOutTit = $this->request->getGetPost('clockOutTit');
        $clockOutDays = $this->request->getGetPost('clockOutDays');
        $workHours = (float)$this->request->getGetPost('workHours');
        $description = $this->request->getGetPost('description');
        $uId = (int)$this->request->getGetPost('uId');
        $uName = $this->db->table('user')->select('name')->where('id', $uId)->get()->getRowArray()['name'] ?? '';
        $clockOutDays = explode(',', $clockOutDays);
        if (count($clockOutDays) > 0 && $uName) {
            $nowMarkArr = explode(',', $clockOutTit);
            $work_hour_arr = [];
            $this->db->transStart();
            foreach ($clockOutDays as $arr => $row) {
                if ($clockOutTit == '补班' || $clockOutTit == '加班' || $clockOutTit == '计时') {
                    if ($row != date('Y-m-d')) {
                        return $this->response->setJSON(['status' => -1, 'msg' => '只能当天申报']);
                    } elseif (
                        $clockOutTit == '补班' && $this->db->table('clockin')->where(['clockDate' => $row, 'uName' => $uName, 'amClock !=' => '00:00:00', 'pmClock <=' => '18:30:30', 'isDelete' => 0])->countAllResults() > 0
                    ) {
                        return $this->response->setJSON(['status' => -1, 'msg' => '已打卡无需申报']);
                    } elseif (
                        $clockOutTit == '补班' && date('H:i:s') < '18:30:00'
                    ) {
                        return $this->response->setJSON(['status' => -1, 'msg' => '白天补班只打卡']);
                    } else {
                        $work_hour_arr[] = [
                            'type' => $clockOutTit == '补班' ? 0 : ($clockOutTit == '加班' ? 1 : 2),
                            'uName' => $uName,
                            'workDate' => $row,
                            'workHours' => $workHours,
                            'description' => $description,
                            'createTime' => date('Y-m-d H:i:s')
                        ];
                    }
                }
                if (($clockOutTit == '请假' || $clockOutTit == '调休' || $clockOutTit == '补休' || $clockOutTit == '外勤') && $row < date('Y-m-d')) {
                    return $this->response->setJSON(['status' => -1, 'msg' => '不能申请今天前']); //不允许申请今天以前（因为原则上要求员工提前申请），其他标注不受此限制。
                } elseif ($clockOutTit == '值班' && $row > date('Y-m-d')) {
                    return $this->response->setJSON(['status' => -1, 'msg' => '不能申请今天后']);
                }
                //标注字段追加、去除、去重等处理
                $oldMarks = $this->db->table('clockin')->select('marks')->where(['clockDate' => $row, 'uId' => $uId])->get()->getRowArray()['marks'] ?? '';
                if ($clockOutTit == '值班') { //如果申请值班状态，则去除迟到、早退标注
                    $oldMarks = str_replace(['迟到,', ',迟到', '迟到'], '', $oldMarks);
                    $oldMarks = str_replace(['早退,', ',早退', '早退'], '', $oldMarks);
                }
                $oldMarksArr = $oldMarks == '' ? [] : explode(',', trim(str_replace(',,', ',', $oldMarks), ','));
                $newMarks = trim(preg_replace('/,,/', ',', implode(',', array_unique(array_merge($oldMarksArr, $nowMarkArr)))));
                // 有则更新，无则插入
                if ($this->db->table('clockin')->where(['uId' => $uId, 'clockDate' => $row])->countAllResults() > 0) {
                    $this->db->table('clockin')->where(['uId' => $uId, 'clockDate' => $row])->update([
                        'marks' => $newMarks,
                        'description' => $description,
                        'createTime' => date('Y-m-d H:i:s'),
                        'isDelete' => 0
                    ]);
                } else {
                    $this->db->table('clockin')->insert([
                        'uId' => $uId,
                        'uName' => $uName,
                        'clockDate' => $row,
                        'marks' => $newMarks,
                        'description' => $description,
                        'createTime' => date('Y-m-d H:i:s'),
                        'isDelete' => 0
                    ]);
                }
            }
            // 如果有合规的加班、计时申报则录入加班计时表
            if (count($work_hour_arr) > 0) {
                $this->db->table('work_hour')->insertBatch($work_hour_arr);
            }
            $this->db->table('log')->insert(['log' => '提交了 ' . implode(',', $clockOutDays) . ' 的 ' . $clockOutTit . ' 申报', 'name' => $uName, 'createTime' => date('Y-m-d H:i:s')]);
            $this->db->transComplete();
            if ($this->db->transStatus() === FALSE) {
                return $this->response->setJSON(['status' => -1, 'msg' => '提交失败']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '提交成功']);
        } else {
            return $this->response->setJSON(['status' => -1, 'msg' => '日期不能为空']);
        }
    }
    // （请假、调休、补休、外勤、值班、补班、加班、计时）申请撤回
    public function clockOutBack()
    {
        $clockOutTit = $this->request->getGetPost('clockOutTit');
        $clockOutDays = $this->request->getGetPost('clockOutDays');
        $uId = (int)$this->request->getGetPost('uId');
        $uName = $this->db->table('user')->select('name')->where('id', $uId)->get()->getRowArray()['name'] ?? '';
        $clockOutDays = explode(',', $clockOutDays);
        if (count($clockOutDays) > 0 && $uName) {
            $this->db->transStart();
            foreach ($clockOutDays as $arr => $row) {
                if ($clockOutTit == '外勤') {
                    return $this->response->setJSON(['status' => -1, 'msg' => '外勤不允许撤回']);
                }
                if ($clockOutTit == '补班' || $clockOutTit == '加班' || $clockOutTit == '计时') {
                    $this->db->table('work_hour')->where(['type' => $clockOutTit == '补班' ? 0 : ($clockOutTit == '加班' ? 1 : 2), 'uName' => $uName, 'workDate' => $row])->update(['isDelete' => 1]);
                }
                //标注字段清除、去重等处理
                $clockinData = $this->db->table('clockin')->where(['clockDate' => $row, 'uId' => $uId])->get()->getRowArray();
                if (is_countable($clockinData) && count($clockinData) > 0) {
                    if (strpos($clockinData['marks'], $clockOutTit) !== false) {
                        $newMarks = str_replace([$clockOutTit . ',', ',' . $clockOutTit, $clockOutTit], '', $clockinData['marks']);
                        if ($newMarks == '' && $clockinData['amClock'] == '00:00:00' && $clockinData['pmClock'] == '00:00:00') {
                            //如果撤回后已无标注且无打卡则删除当天记录
                            $this->db->table('clockin')->where(['clockDate' => $row, 'uId' => $uId])->update(['isDelete' => 1]);
                        } else {
                            $this->db->table('clockin')->where(['clockDate' => $row, 'uId' => $uId])->update(['marks' => $newMarks, 'description' => $clockinData['description'] . '[已撤回' . $clockOutTit . ']']);
                        }
                        $this->db->table('log')->insert(['log' => '撤回了 ' . implode(',', $clockOutDays) . ' 的 ' . $clockOutTit . ' 申请', 'name' => $uName, 'createTime' => date('Y-m-d H:i:s')]);
                    }
                }
            }
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '撤回失败']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '撤回成功']);
        } else {
            return $this->response->setJSON(['status' => -1, 'msg' => '日期不能为空']);
        }
    }
    // （请假、调休、补休、外勤、值班、补班、加班、计时）获取最新2条
    public function getCheckInfos()
    {
        $uId = (int)$this->request->getGetPost('uId');
        $checkInfoData = $this->db->table('clockin')->select('clockDate,marks,isCheck')->groupStart()->where('FIND_IN_SET("请假", marks) >', 0, false)->orWhere('FIND_IN_SET("调休", marks) >', 0, false)->orWhere('FIND_IN_SET("补休", marks) >', 0, false)->orWhere('FIND_IN_SET("外勤", marks) >', 0, false)->groupEnd()->where(['uId' => $uId, 'isDelete' => 0])->orderBy('clockDate', 'DESC')->limit(2)->get()->getResultArray();
        if (is_countable($checkInfoData) && count($checkInfoData) > 0) {
            $v = [];
            foreach ($checkInfoData as $arr => $row) {
                $v[$arr]['clockDate'] = $row['clockDate'];
                $v[$arr]['marks'] = $row['marks'];
                $v[$arr]['isCheck'] = (int)$row['isCheck'];
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '获取信息成功', 'data' => $v]);
        } else {
            return $this->response->setJSON(['status' => -1, 'msg' => '无记录']);
        }
    }


    // 快递包货 扫码上传（扫码座）
    public function upDeliverNo()
    {
        // 扫码座HTTP/HTTPS上传方法是POST
        // 扫码座既不按规范设Content-Type(它上传扫码结果是用text/html，发送心跳包是application/json)
        // 也不用标准的&分隔(它用的&&)，所以框架的所有便利方法都指望不上，必须自己处理
        // CI3里直接下面一行代码就能获取到数据，因为它的input类里有个raw_input_stream方法，可以读取框架缓存的流数据
        // parse_str($this->input->raw_input_stream, $data);
        // 但CI4里没有这个方法了，Content-Type是text/html导致CI4不解析请求体，&&分隔符导致即便解析也不认，
        // 而getBody()返回"php://input"字符串则说明CI4的构造函数根本没执行读流那个分支，
        // 三件事叠在一起，框架的所有方法全部失效，只剩php://input直读这一条路。
        $raw = file_get_contents('php://input');
        if ($raw === '') {
            log_message('error', '扫码设备请求体为空');
            exit('code=0005'); // 运单号缺失
        }
        // 处理扫码设备上传的非标准格式
        $raw = str_replace('&&', '&', $raw);
        // 解析成数组
        parse_str($raw, $data);
        $time = date('Y-m-d H:i:s');
        if (is_array($data) && isset($data['vgdecoderesult']) && isset($data['devicenumber'])) {
            $ncod = $data['vgdecoderesult'];
            $nid = $data['devicenumber'];
            // 根据配好的设备号与扫码人对应关系确定扫码人
            $usermap = ['1001' => '解维燕', '1002' => '向劲', '1003' => '吴青', '1004' => '程莉', '1005' => '罗育秀', '1006' => '向云凤', '1007' => '郑红丽'];
            $user = $usermap[$nid] ?? '未知';
            // 判断格式
            if (!preg_match('/[0-9a-zA-z]+$/', $ncod) || strlen($ncod) < 11 || strlen($ncod) > 15) {
                log_message('error', '发包记录：运单号格式错误，' . json_encode($data));
                return $this->response->setContentType('text/plain')->setBody('code=0001'); // 001代表运单号格式错误
            }
            // 判断拦截（为了处理速度，限制只查30天内数据）
            if ($this->db->table('deliver_bag_off')->where(['createTime >' => date('Y-m-d H:i:s', strtotime('-30 day')), 'no' => $ncod, 'isDelete' => 0])->countAllResults() > 0) {
                $this->db->table('deliver_bag_off')
                    ->where('no', $ncod)
                    ->set('scanTime', $time)
                    ->set('scanName', $user)
                    ->set('offHits', 'offHits + 1', false)   // 第三个参数传false，不转义，原样输出
                    ->update();
                log_message('error', '发包记录：此单拦截不发货，' . json_encode($data));
                return $this->response->setContentType('text/plain')->setBody('code=0002'); // 002代表此单拦截不发货
            }
            // 判断有无（为了处理速度，限制只查30天内数据）
            if ($this->db->table('deliver_bag')->where(['printTime >' => date('Y-m-d H:i:s', strtotime('-30 day')), 'no' => $ncod, 'isDelete' => 0])->countAllResults() == 0) {
                log_message('error', '发包记录：系统中无此单号，' . json_encode($data));
                return $this->response->setContentType('text/plain')->setBody('code=0003'); // 003代表系统中无此单号
            }
            $this->db->transStart();
            // 更新对应单号的包货包货时间
            $this->db->table('deliver_bag')->where('no', $ncod)->update(['packName' => $user, 'packTime' => $time]);
            $this->db->table('log')->insert(['log' => '从扫码座扫描了 1 个包裹（发包记录），单号：' . $ncod, 'name' => $user, 'createTime' => date('Y-m-d H:i:s')]);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                log_message('error', '发包记录：sql事务失败！' . json_encode($data));
                return $this->response->setContentType('text/plain')->setBody('code=0004'); // sql事务提交失败！事务已回滚！
            }
            return $this->response->setContentType('text/plain')->setBody('code=0000'); // 提交成功
        } elseif (is_array($data) && isset($data['www_vguang_cn']) && isset($data['devicenumber'])) {
            // beat心跳，无需处理
        } else {
            log_message('error', '发包记录：运单号缺失，' . json_encode($data));
            return $this->response->setContentType('text/plain')->setBody('code=0005'); // 005代表运单号缺失
        }
    }
    // 快递发货 获取扫码APP所需的正常用户列表，按appSort自定义排序字段和id字段排序
    public function getUserList()
    {
        $v = [];
        // 先仓储部
        $users = $this->db->table('user')->select('id, name, userName, userPwd')->where(['department' => '仓储部', 'isDisable' => 0, 'isDelete' => 0])->orderBy('id', 'ASC')->get()->getResultArray();
        if (count($users) > 0) {
            foreach ($users as $arr => $row) {
                array_push($v, $row);
            }
        }
        // 再其他有可能要扫码发货的部门
        $users = $this->db->table('user')->select('id, name, userName, userPwd')->where(['department' => '销售部', 'isDisable' => 0, 'isDelete' => 0])->orderBy('id', 'ASC')->get()->getResultArray();
        if (count($users) > 0) {
            foreach ($users as $arr => $row) {
                array_push($v, $row);
            }
        }
        // 再其他有可能要扫码发货的部门
        $users = $this->db->table('user')->select('id, name, userName, userPwd')->where(['department' => '运营部', 'isDisable' => 0, 'isDelete' => 0])->orderBy('id', 'ASC')->get()->getResultArray();
        if (count($users) > 0) {
            foreach ($users as $arr => $row) {
                array_push($v, $row);
            }
        }
        // 再其他有可能要扫码发货的部门
        $users = $this->db->table('user')->select('id, name, userName, userPwd')->where(['department' => '财务部', 'isDisable' => 0, 'isDelete' => 0])->orderBy('id', 'ASC')->get()->getResultArray();
        if (count($users) > 0) {
            foreach ($users as $arr => $row) {
                array_push($v, $row);
            }
        }
        // 再其他有可能要扫码发货的部门
        $users = $this->db->table('user')->select('id, name, userName, userPwd')->where(['department' => '行政部', 'isDisable' => 0, 'isDelete' => 0])->orderBy('id', 'ASC')->get()->getResultArray();
        if (count($users) > 0) {
            foreach ($users as $arr => $row) {
                array_push($v, $row);
            }
        }
        $json['status'] = 200;
        $json['msg'] = 'success';
        $json['data'] = isset($v) ? array_values($v) : [];
        return $this->response->setJSON($json);
    }
    // 快递发货 扫码上传（扫码台）
    public function upDeliverInfo()
    {
        $ncod = $this->request->getGetPost('ncod');
        $nwei = max((float)$this->request->getGetPost('nwei'), 0.01);
        $name = $this->request->getGetPost('name');
        $time = date('Y-m-d H:i:s');
        $cover = (int)$this->request->getGetPost('cover');
        $ncom = '';
        $amount = 0;
        $resdata = ['ncod' => $ncod, 'nwei' => $nwei, 'name' => $name, 'time' => $time, 'cover' => $cover];
        if ($ncod) {
            // 判断格式
            if (!preg_match('/[0-9a-zA-z]+$/', $ncod) || strlen($ncod) < 11 || strlen($ncod) > 15) {
                return $this->response->setJSON(['status' => -1, 'msg' => '运单号格式错误', 'data' => $resdata]);
            }
            // 判断扫码人是否一致（为了处理速度，限制只查30天内数据）
            $packName = $this->db->table('deliver_bag')->select('packName')->where(['printTime >' => date('Y-m-d H:i:s', strtotime('-30 day')), 'no' => $ncod, 'isDelete' => 0])->get()->getRowArray()['packName'] ?? '';
            if ($packName && $packName != $name) {
                return $this->response->setJSON(['status' => -1, 'msg' => '与打包扫码人不符', 'data' => $resdata]);
            }
            // 判断拦截（为了处理速度，限制只查30天内数据）
            if ($this->db->table('deliver_bag_off')->where(['createTime >' => date('Y-m-d H:i:s', strtotime('-30 day')), 'no' => $ncod, 'isDelete' => 0])->countAllResults() > 0) {
                $this->db->table('deliver_bag_off')->where('no', $ncod)->set('scanName', $name)->set('scanTime', $time)->set('offHits', 'offHits + 1', false)->update();
                return $this->response->setJSON(['status' => 100, 'msg' => '此单拦截不发货', 'data' => $resdata]);
            }
            // 判断重复（为了处理速度，限制只查30天内数据）
            if ($this->db->table('deliver_bag')->where(['printTime >' => date('Y-m-d H:i:s', strtotime('-30 day')), 'no' => $ncod, 'scanName !=' => '', 'scanTime !=' => '0000-00-00 00:00:00', 'isDelete' => 0])->countAllResults() > 0 && $cover == 0) {
                return $this->response->setJSON(['status' => 101, 'msg' => '此单扫码重复', 'data' => $resdata]);
            }
            // 判断所属快递公司成功后自动计算运费
            $deliverRull = $this->db->table('deliver_company')->select('id, name AS com, len, str')->where(['len !=' => '', 'str !=' => '', 'isDelete' => 0])->orderBy('id', 'ASC')->get()->getResultArray();
            for ($i = 0; $i < count($deliverRull); $i++) {
                $len = explode('|', $deliverRull[$i]['len']);
                $str = explode('|', $deliverRull[$i]['str']);
                // 先匹配运单号长度
                if (in_array(strlen($ncod), $len)) {
                    // 再匹配运单号抬头
                    for ($j = 0; $j < count($str); $j++) {
                        if (substr($ncod, 0, strlen($str[$j])) == $str[$j]) {
                            // 计算运费
                            $ncom = $deliverRull[$i]['com'];
                            $data = $this->db->table('deliver_bag')->select('address')->where(['no' => $ncod, 'isDelete' => 0])->get()->getRowArray();
                            if ($data && $data['address'] && in_array($ncom, ['申通快递', '圆通速递', '邮政快递', '京东快递'])) {
                                $amount = deliver_amount_calc($ncom, $data['address'], $nwei);
                            }
                        }
                    }
                }
            };
            $resdata = ['ncod' => $ncod, 'ncom' => $ncom, 'nwei' => $nwei, 'amount' => $amount, 'name' => $name, 'time' => $time];
            $this->db->transStart();
            // 有则更新，无则插入
            if ($this->db->table('deliver_bag')->where(['no' => $ncod])->countAllResults() > 0) {
                $this->db->table('deliver_bag')->where('no', $ncod)->update([
                    'company' => $ncom,
                    'weight' => $nwei,
                    'amount' => $amount,
                    'scanName' => $name,
                    'scanTime' => $time,
                    'scanWay' => 2
                ]);
            } else {
                $this->db->table('deliver_bag')->insert([
                    'no' => $ncod,
                    'company' => $ncom,
                    'weight' => $nwei,
                    'amount' => $amount,
                    'scanName' => $name,
                    'scanTime' => $time,
                    'scanWay' => 2
                ]);
            }
            $this->db->table('log')->insert(['log' => '从扫码台扫描了 1 个包裹，单号：' . $ncod, 'name' => $name, 'createTime' => date('Y-m-d H:i:s')]);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！', 'data' => $resdata]);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '提交成功！', 'data' => $resdata]);
        } else {
            return $this->response->setJSON(['status' => -1, 'msg' => '提交的数据不完整！', 'data' => $resdata]);
        }
    }
    // 快递发货 扫码单个上传（APP + 小程序)
    public function singleUpDeliverNo()
    {
        $ncod = $this->request->getGetPost('ncod');
        $nwei = bcdiv(max((int)$this->request->getGetPost('nwei'), 10), '1000', 2);
        $name = $this->request->getGetPost('name');
        $time = date('Y-m-d H:i:s');
        $ncom = '';
        $amount = 0;
        $resdata = ['ncod' => $ncod, 'nwei' => $nwei, 'name' => $name, 'time' => $time];
        if ($ncod) {
            // 判断格式
            if (!preg_match('/[0-9a-zA-z]+$/', $ncod) || strlen($ncod) < 11 || strlen($ncod) > 15) {
                return $this->response->setJSON(['status' => -1, 'msg' => '运单号格式错误', 'data' => $resdata]);
            }
            // 判断扫码人是否一致（为了处理速度，限制只查30天内数据）
            $packName = $this->db->table('deliver_bag')->where(['printTime >' => date('Y-m-d H:i:s', strtotime('-30 day')), 'no' => $ncod, 'isDelete' => 0])->get()->getRowArray()['packName'] ?? '';
            if ($packName && $packName != $name) {
                return $this->response->setJSON(['status' => -1, 'msg' => '与打包扫码人不符', 'data' => $resdata]);
            }
            $this->db->transStart();
            // 判断拦截（为了处理速度，限制只查30天内数据）
            if ($this->db->table('deliver_bag_off')->where(['createTime >' => date('Y-m-d H:i:s', strtotime('-30 day')), 'no' => $ncod, 'isDelete' => 0])->countAllResults() > 0) {
                $this->db->table('deliver_bag_off')->where('no', $ncod)->set('scanName', $name)->set('scanTime', $time)->set('offHits', 'offHits + 1', false)->update();
                return $this->response->setJSON(['status' => 100, 'msg' => '此单拦截不发货', 'data' => $resdata]);
            }
            // 判断重复（为了处理速度，限制只查30天内数据）
            if ($this->db->table('deliver_bag')->where(['printTime >' => date('Y-m-d H:i:s', strtotime('-30 day')), 'no' => $ncod, 'isDelete' => 0])->countAllResults() > 0) {
                $this->db->table('deliver_bag')->where('no', $ncod)->update(['scanName' => $name, 'scanTime' => $time]);
                return $this->response->setJSON(['status' => 101, 'msg' => '此单扫码重复', 'data' => $resdata]);
            }
            // 判断所属快递公司成功后自动计算运费
            $deliverRull = (array) $this->db->table('deliver_company')->select('id, name AS com, len, str')->where(['len !=' => '', 'str !=' => '', 'isDelete' => 0])->orderBy('id', 'ASC')->get()->getResultArray();
            for ($i = 0; $i < count($deliverRull); $i++) {
                $len = explode('|', $deliverRull[$i]['len']);
                $str = explode('|', $deliverRull[$i]['str']);
                // 先匹配运单号长度
                if (in_array(strlen($ncod), $len)) {
                    // 再匹配运单号抬头
                    for ($j = 0; $j < count($str); $j++) {
                        if (substr($ncod, 0, strlen($str[$j])) == $str[$j]) {
                            // 计算运费
                            $ncom = $deliverRull[$i]['com'];
                            $data = $this->db->table('deliver_bag')->select('address')->where(['no' => $ncod, 'isDelete' => 0])->get()->getRowArray();
                            if ($data && $data['address'] && in_array($ncom, ['申通快递', '圆通速递', '邮政快递', '京东快递'])) {
                                $amount = deliver_amount_calc($ncom, $data['address'], $nwei);
                            }
                        }
                    }
                }
            };
            $resdata = ['ncod' => $ncod, 'ncom' => $ncom, 'nwei' => $nwei, 'amount' => $amount, 'name' => $name, 'time' => $time];
            // 有则更新，无则插入
            if ($this->db->table('deliver_bag')->where(['no' => $ncod])->countAllResults() > 0) {
                $this->db->table('deliver_bag')->where('no', $ncod)->update([
                    'company' => $ncom,
                    'weight' => $nwei,
                    'amount' => $amount,
                    'scanName' => $name,
                    'scanTime' => $time,
                    'scanWay' => 1
                ]);
            } else {
                $this->db->table('deliver_bag')->insert([
                    'no' => $ncod,
                    'company' => $ncom,
                    'weight' => $nwei,
                    'amount' => $amount,
                    'scanName' => $name,
                    'scanTime' => $time,
                    'scanWay' => 1
                ]);
            }
            $this->db->table('log')->insert(['log' => '从手持设备扫描了 1 个包裹，单号：' . $ncod, 'name' => $name, 'createTime' => date('Y-m-d H:i:s')]);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！', 'data' => $resdata]);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '提交成功！', 'data' => $resdata]);
        } else {
            return $this->response->setJSON(['status' => -1, 'msg' => '提交的数据不完整！', 'data' => $resdata]);
        }
    }
    // 快递发货 扫码批量上传(APP + 小程序)
    public function batchUpDeliverNo()
    {
        $uname = $this->request->getGetPost('uName');
        $upwd = $this->request->getGetPost('upwd');
        $nlist = $this->request->getGetPost('nlist');
        if (strlen($upwd) != 32) {
            $upwd = md5($upwd);
        }
        if ($uname && $upwd && $nlist) {
            // 账号校验
            $user = $this->db->table('user')->groupStart()->where('userName', $uname)->orWhere('mobile', $uname)->groupEnd()->where(['userPwd'=> $upwd, 'isDisable' => 0, 'isDelete' => 0])->get()->getRowArray();
            if (!$user) {
                return $this->response->setJSON(['status' => -1, 'msg' => '账号或密码错误，或账号已被停用！']);
            }
            // 运单处理
            $deliverRull = (array) $this->db->table('deliver_company')->select('id, name AS com, len, str')->where(['len !=' => '', 'str != ' => '', 'isDelete' => 0])->orderBy('id', 'ASC')->get()->getResultArray();
            $nlist = (array)json_decode($nlist, true);
            $nlist_count = count($nlist);
            if ($nlist_count > 0) {
                $this->db->transStart();
                foreach ($nlist as $arr => $row) {
                    if (preg_match('/[0-9a-zA-z]+$/', $row['ncod']) && strlen($row['ncod']) >= 13 && strlen($row['ncod']) <= 15) { //运单号格式合法才继续
                        $row['nwei'] = bcdiv(max(intval($row['nwei']), 10), '1000', 2);
                        $ncom = '';
                        $amount = 0;
                        // 判断所属快递公司成功后自动计算运费
                        for ($i = 0; $i < count($deliverRull); $i++) {
                            $len = explode('|', $deliverRull[$i]['len']);
                            $str = explode('|', $deliverRull[$i]['str']);
                            // 先匹配运单号长度
                            if (in_array(strlen($row['ncod']), $len)) {
                                // 再匹配运单号抬头
                                for ($j = 0; $j < count($str); $j++) {
                                    if (substr($row['ncod'], 0, strlen($str[$j])) == $str[$j]) {
                                        $ncom = $deliverRull[$i]['com'];
                                        // 计算运费
                                        $data = $this->db->table('deliver_bag')->select('address')->where(['no' => $row['ncod'], 'isDelete' => 0])->get()->getRowArray();
                                        if ($data && $data['address'] && in_array($ncom, ['申通快递', '圆通速递', '邮政快递', '京东快递'])) {
                                            $amount = deliver_amount_calc($ncom, $data['address'], $row['nwei']);
                                        }
                                    }
                                }
                            }
                        };
                        // 有则更新，无则插入
                        if ($this->db->table('deliver_bag')->where(['no' => $row['ncod']])->countAllResults() > 0) {
                            $this->db->table('deliver_bag')->where(['no' => $row['ncod']])->update([
                                'company' => $ncom,
                                'weight' => $row['nwei'],
                                'amount' => $amount,
                                'scanName' => $row['name'],
                                'scanTime' => date('Y-m-d H:i:s'),
                                'scanWay' => 1
                            ]);
                        } else {
                            $this->db->table('deliver_bag')->insert([
                                'no' => $row['ncod'],
                                'company' => $ncom,
                                'weight' => $row['nwei'],
                                'amount' => $amount,
                                'scanName' => $row['name'],
                                'scanTime' => date('Y-m-d H:i:s'),
                                'scanWay' => 1
                            ]);
                        }
                    }
                }
                $this->db->table('log')->insert(['log' => '从手持设备批量提交了 ' . $nlist_count . ' 个包裹扫描记录！', 'name' => '系统', 'createTime' => date('Y-m-d H:i:s')]);
                $this->db->transComplete();
                if ($this->db->transStatus() === false) {
                    return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
                }
                return $this->response->setJSON(['status' => 200, 'msg' => '批量提交成功！共提交了 ' . $nlist_count . ' 个包裹扫描记录！']);
            } else {
                return $this->response->setJSON(['status' => -1, 'msg' => '提交的运单列表是空的！']);
            }
        } else {
            return $this->response->setJSON(['status' => -1, 'msg' => '提交的数据不完整！']);
        }
    }


    // 采购申请 列表
    public function purReqList()
    {
        $current = max((int)$this->request->getGetPost('current'), 1);
        $pageSize = max((int)$this->request->getGetPost('pageSize'), 10);
        $skey = $this->request->getGetPost('skey');
        $type = $this->request->getGetPost('type');
        $uId = (int)$this->request->getGetPost('uId');

        $builder = $this->db->table('pur_req');
        $builder->where('isDelete', 0);
        if ($skey !== '' && $skey !== null) {
            $keywords = array_values(array_filter(explode(',', $skey), 'strlen'));
            foreach ($keywords as $kw) {
                $builder->groupStart()
                    ->like('title', $this->db->escapeLikeString($kw))
                    ->orLike('content', $this->db->escapeLikeString($kw))
                    ->orLike('uName', $this->db->escapeLikeString($kw))
                    ->orLike('description', $this->db->escapeLikeString($kw))
                ->groupEnd();
            }
        }
        if ($type === 'self') {
            $builder->where('uId', $uId);
        }
        $list = (clone $builder)->orderBy('id', 'DESC')->limit($pageSize)->offset($pageSize * ($current - 1))->get()->getResultArray();
        $total = $builder->countAllResults();
        $v = [];
        foreach ($list as $row) {
            $v[] = [
                'id' => (int)$row['id'],
                'level' => (int)$row['level'],
                'title' => $row['title'],
                'content' => $row['content'],
                'imgs' => $row['imgs'] === '' ? [] : explode(',', $row['imgs']),
                'uId' => (int)$row['uId'],
                'uName' => $row['uName'],
                'createTime' => ($row['createTime'] === '0000-00-00 00:00:00' || $row['createTime'] === null) ? '' : $row['createTime'],
                'checkId' => (int)$row['checkId'],
                'purchaseId' => (int)$row['purchaseId'],
                'description' => $row['description'],
            ];
        }
        $json['status'] = 200;
        $json['msg'] = 'success';
        $json['data']['current'] = $current;
        $json['data']['total'] = $total;
        $json['data']['pages'] = ceil($total / $pageSize);
        $json['data']['list'] = $v;
        return $this->response->setJSON($json);
    }
    // 采购申请 提交
    public function purReqSubmit()
    {
        $data = $this->request->getPost();
        if (is_array($data) && count($data) > 0) {
            $data['imgs'] = strlen($data['imgs']) < 10 ? '' : $data['imgs'];
            $data['createTime'] = date('Y-m-d H:i:s');
            $this->db->transStart();
            $this->db->table('pur_req')->insert($data);
            $resid = $this->db->insertID();
            $this->db->table('log')->insert(['log' => '提交了采购申请，ID: ' . $resid . '，标题：' . $data['title'], 'name' => $data['uName'], 'createTime' => date('Y-m-d H:i:s')]);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '提交成功', 'data' => ['id' => $resid, 'createTime' => $data['createTime']]]);
        } else {
            return $this->response->setJSON(['status' => -1, 'msg' => '提交数据不完整']);
        }
    }
    // 采购申请 撤销
    public function purReqDel()
    {
        $id = (int)$this->request->getGetPost('id');
        if ($id > 0) {
            $this->db->transStart();
            $data = $this->db->table('pur_req')->where(['id' => $id, 'isDelete' => 0])->get()->getRowArray();
            $this->db->table('pur_req')->where(['id' => $id])->update(['isDelete' => 1]);
            $this->db->table('log')->insert(['log' => '撤销了采购申请，ID: ' . $id . '，标题：' . $data['title'], 'name' => $data['uName'], 'createTime' => date('Y-m-d H:i:s')]);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '撤销成功']);
        } else {
            return $this->response->setJSON(['status' => -1, 'msg' => '提交数据不完整']);
        }
    }
    // 采购申请 上传参考图片
    public function purReqUploadImg()
    {
        set_time_limit(0);
        session_write_close();
        if (empty($_FILES)){
            return $this->response->setJSON(['status' => -1, 'msg' => '没有上传文件！']);
        };
        $uppath = 'data/upfile/purreq/img/';
        $temp_file = $_FILES['file']['tmp_name'];
        $target_file = date("YmdHis") . rand(1000, 9000) . '.' . strrev(explode('.', strrev($_FILES['file']['name']))[0]);
        if (!is_dir($uppath)) {
            @umask(0);
            $ret = @mkdir($uppath, 0777);
            if ($ret === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '创建文件夹']);
            }
        }
        move_uploaded_file($temp_file, $uppath . $target_file);
        return $this->response->setJSON(['status' => 200, 'msg' => '提交成功', 'data' => $target_file]);
    }


    // 配货申请 列表
    public function proKittingReqList()
    {
        $current = max((int)$this->request->getGetPost('current'), 1);
        $pageSize = max((int)$this->request->getGetPost('pageSize'), 10);
        $skey = $this->request->getGetPost('skey');
        $type = $this->request->getGetPost('type');
        $uId = (int)$this->request->getGetPost('uId');
        $builder = $this->db->table('pro_kitting_req');
        $builder->where('isDelete', 0);
        if ($skey !== '') {
            $keywords = array_values(array_filter(explode(',', $skey), 'strlen'));
            foreach ($keywords as $kw) {
                $builder->groupStart()
                    ->like('goodsText', $this->db->escapeLikeString($kw))
                    ->orLike('content', $this->db->escapeLikeString($kw))
                    ->orLike('uName', $this->db->escapeLikeString($kw))
                    ->orLike('description', $this->db->escapeLikeString($kw))
                ->groupEnd();
            }
        }
        if ($type == 'self') {
            $builder->where('uId', $uId);
        }
        $user = $this->db->table('user')->where('id', $uId)->get()->getRowArray();
        $list = (clone $builder)->orderBy('id', 'DESC')->limit($pageSize)->offset($pageSize * ($current - 1))->get()->getResultArray();
        $total = $builder->countAllResults();
        $v = [];
        foreach ($list as $arr => $row) {
            $v[$arr]['id'] = intval($row['id']);
            $v[$arr]['lastDate'] = ($user['position'] == '配货专员' && $row['uId'] != $uId) ? '' : $row['lastDate']; // 配货专员不允许看到其他人申请的交付限期，避免作弊
            $v[$arr]['goodsId'] = intval($row['goodsId']);
            $v[$arr]['goodsText'] = $row['goodsText'];
            $v[$arr]['qty'] = intval($row['qty']);
            $v[$arr]['content'] = $row['content'];
            $v[$arr]['uId'] = intval($row['uId']);
            $v[$arr]['uName'] = $row['uName'];
            $v[$arr]['createTime'] = $row['createTime'] == '0000-00-00 00:00:00' ? '' : $row['createTime'];
            $v[$arr]['checkId'] = intval($row['checkId']);
            $v[$arr]['produceId'] = intval($row['produceId']);
            $v[$arr]['description'] = $row['description'];
            $v[$arr]['taskId'] = intval($row['taskId']);
            if ($v[$arr]['taskId'] > 0) {
                $v[$arr]['taskGet'] = $this->db->table('pro_kitting_task_get')->where(['iid' => $v[$arr]['taskId'], 'isDelete' => 0])->get()->getResultArray();
            } else {
                $v[$arr]['taskGet'] = [];
            }
        }
        $json['status'] = 200;
        $json['msg'] = 'success';
        $json['data'] = [
            'current' => $current,
            'total' => $total,
            'pages' => (int)ceil($total / $pageSize),
            'list' => $v
        ];
        return $this->response->setJSON($json);
    }
    // 配货申请 提交
    public function proKittingReqSubmit()
    {
        $data = $this->request->getPost();
        if (is_array($data) && count($data) > 0) {
            $data['createTime'] = date('Y-m-d H:i:s');
            if (strtotime($data['lastDate']) - strtotime($data['createTime']) < 18000) {
                return $this->response->setJSON(['status' => -1, 'msg' => '交付限期至少留足5小时以上']);
            }
            $goods = $this->db->table('goods')->where(['id' => $data['goodsId']])->get()->getRowArray();
            if (!$goods || $goods['isDelete'] == 1) {
                return $this->response->setJSON(['status' => -1, 'msg' => '产品不存在']);
            }
            if ($goods['isDisable'] == 1) {
                return $this->response->setJSON(['status' => -1, 'msg' => '将停产不再生产']);
            }
            if ($goods['isDisable'] == 2) {
                return $this->response->setJSON(['status' => -1, 'msg' => '已停产不再生产']);
            }
            if ($goods['isDisable'] == 9) {
                return $this->response->setJSON(['status' => -1, 'msg' => '已禁用不再生产']);
            }
            $kitCids_arr = array_column($this->db->table('category')->where('FIND_IN_SET("245", path) >', 0, false)->where('isDelete', 0)->select('id')->get()->getResultArray(), 'id');
            if (!in_array($goods['categoryId'], $kitCids_arr)) {
                return $this->response->setJSON(['status' => -1, 'msg' => '请申请基础套件']);
            }
            $this->db->transStart();
            $this->db->table('pro_kitting_req')->insert($data);
            $resid = $this->db->insertID();
            $this->db->table('log')->insert(['log' => '提交了配货申请，ID: ' . $resid . '，产品：' . $data['goodsText'] . '，数量：' . $data['qty'], 'name' => $data['uName'], 'createTime' => date('Y-m-d H:i:s')]);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '提交成功', 'data' => ['id' => $resid, 'createTime' => $data['createTime']]]);
        } else {
            return $this->response->setJSON(['status' => -1, 'msg' => '提交数据不完整']);
        }
    }
    // 配货申请 撤销
    public function proKittingReqDel()
    {
        $id = (int)$this->request->getGetPost('id');
        if ($id > 0) {
            $this->db->transStart();
            $data = $this->db->table('pro_kitting_req')->where(['id' => $id, 'isDelete' => 0])->get()->getRowArray();
            $this->db->table('pro_kitting_req')->where(['id' => $id])->update(['isDelete' => 1]);
            $this->db->table('log')->insert(['log' => '撤销了配货申请，ID: ' . $id . '，产品：' . $data['goodsText'] . '，数量：' . $data['qty'], 'name' => $data['uName'], 'createTime' => date('Y-m-d H:i:s')]);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '撤销成功']);
        } else {
            return $this->response->setJSON(['status' => -1, 'msg' => '提交数据不完整']);
        }
    }


    // 配货任务 获取可领取任务数量
    public function proKittingTaskCanTakeNum()
    {
        $takeTotal = $this->db->table('pro_kitting_task')->where(['takeStatus' => 0, 'checkId >' => 0, 'isDelete' => 0])->countAllResults();
        return $this->response->setJSON(['status' => 200, 'msg' => 'success', 'data' => ['takeTotal' => $takeTotal]]);
    }
    // 配货任务 合并领取 选择本组组员
    public function proKittingTaskTakeGroup()
    {
        $uName = $this->request->getGetPost('uName');
        if ($uName) {
<<<<<<< HEAD
            $group = $this->db->table('pro_group')->where('FIND_IN_SET(' . $this->db->escape($uName) . ', members) > 0', null, false)->where(['type' => '配货组', 'isDelete' => 0])->get()->getRowArray();
=======
            $group = $this->db->table('pro_group')->where("FIND_IN_SET(" . $this->db->escape($uName) . ", members) >", 0, false)->where(['type' => '配货组', 'isDelete' => 0])->get()->getRowArray();
>>>>>>> 151b415 (更新语言最新版本+最新底层框架后全部差异化代码适配重写)
            if ($group) {
                $takeGroup = $this->db->table('user')->select('id,name')->whereIn('name', explode(',', $group['members']))->where(['isDisable' => 0, 'isDelete' => 0])->get()->getResultArray();
                if ($takeGroup) {
                    foreach ($takeGroup as $arr => $user) {
                        $takeGroup[$arr]['groupName'] = $group['name'];
                        $clockinInfo = $this->db->table('clockin')->where(['uId' => $user['id'], 'clockDate' => date('Y-m-d'), 'isDelete' => 0])->get()->getRowArray();
                        if ($clockinInfo) {
                            if ($clockinInfo['amClock'] != '00:00:00') {
                                $takeGroup[$arr]['onDuty'] = 1;
                            } else {
                                $takeGroup[$arr]['onDuty'] = 0;
                            }
                        } else {
                            $takeGroup[$arr]['onDuty'] = 0;
                        }
                    }
                    return $this->response->setJSON(['status' => 200, 'msg' => 'success', 'data' => ['takeGroup' => $takeGroup]]);
                } else {
                    return $this->response->setJSON(['status' => -1, 'msg' => '组员不存在']);
                }
            } else {
                return $this->response->setJSON(['status' => -1, 'msg' => '不是配货组成员']);
            }
        } else {
            return $this->response->setJSON(['status' => -1, 'msg' => '重新登录再尝试']);
        }
    }
    // 配货任务 合并领取 提交
    public function proKittingTaskTakeTotalSubmit()
    {
        $takeMemberIds = $this->request->getGetPost('takeMemberIds');
        $uId = (int)$this->request->getGetPost('uId');
        $uName = $this->request->getGetPost('uName');
        if ($takeMemberIds) {
            // 取一个交付限期最紧急的可领取任务
            $taskData = $this->db->table('pro_kitting_task')->where(['takeStatus' => 0, 'checkId >' => 0, 'isDelete' => 0])->orderBy('lastDate', 'ASC')->get()->getRowArray();
            if ($taskData) {
                $takeMemberIds = explode(',', $takeMemberIds);
                $memberNum = count($takeMemberIds);
                // 计算任务产量除以组员人数的商和余数，来平均分配产量，余数(剩余产量)加给随机组员
                $perMemberQty = intdiv(intval($taskData['qty']), $memberNum); // 取商为平均领取产量
                $remainQty = intval($taskData['qty']) % $memberNum; // 取余为剩余产量
                $createTime = date('Y-m-d H:i:s');
                $data = [];
                $takeName = [];
                // 取合理随机数
                $randKey = rand(0, $memberNum - 1);
                // 如果任务产量小于组员人数，不足以均分，则随机一名组员领完产量
                if (intval($taskData['qty']) < $memberNum) {
                    $user = $this->db->table('user')->select('id, name')->where(['id' => $takeMemberIds[$randKey]])->get()->getRowArray();
                    $eachGetData = [
                        'iid' => $taskData['id'],
                        'lastDate' => $taskData['lastDate'],
                        'goodsId' => $taskData['goodsId'],
                        'goodsText' => $taskData['goodsText'],
                        'description' => $taskData['description'],
                        'taskQty' => $taskData['qty'],
                        'getQty' => $taskData['qty'],
                        'uId' => $user['id'],
                        'uName' => $user['name'],
                        'createTime' => $createTime
                    ];
                    array_push($data, $eachGetData);
                    array_push($takeName, $user['name']);
                } else {
                    // 如果任务产量大于等于组员人数，组员平均领取
                    foreach ($takeMemberIds as $arr => $row) {
                        $user = $this->db->table('user')->select('id, name')->where(['id' => $row])->get()->getRowArray();
                        $eachGetData = [
                            'iid' => $taskData['id'],
                            'lastDate' => $taskData['lastDate'],
                            'goodsId' => $taskData['goodsId'],
                            'goodsText' => $taskData['goodsText'],
                            'description' => $taskData['description'],
                            'taskQty' => $taskData['qty'],
                            'getQty' => $arr == $randKey ? ($perMemberQty + $remainQty) : $perMemberQty, // 剩余产量加给随机一名组员
                            'uId' => $user['id'],
                            'uName' => $user['name'],
                            'createTime' => $createTime
                        ];
                        array_push($data, $eachGetData);
                        array_push($takeName, $user['name']);
                    }
                }
                $this->db->transStart();
                $this->db->table('pro_kitting_task_get')->insertBatch($data);
                $taskStatus = $taskData['taskStatus'] == 0 ? 1 : $taskData['taskStatus'];
                $this->db->table('pro_kitting_task')->where(['id' => $taskData['id']])->update(['takeStatus' => 2, 'takeName' => implode(',', $takeName), 'taskStatus' => $taskStatus]);
                $this->db->table('log')->insert(['log' => '代本组【' . implode(',', $takeName) . '】合并领取了配货任务，任务编号：' . $taskData['id'] . '，任务产品：' . $taskData['goodsText'] . '，任务产量：' . $taskData['qty'], 'name' => $uName, 'createTime' => date('Y-m-d H:i:s')]);
                $this->db->transComplete();
                if ($this->db->transStatus() === false) {
                    return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚']);
                } else {
                    return $this->response->setJSON(['status' => 200, 'msg' => '领取成功']);
                }
            } else {
                return $this->response->setJSON(['status' => -1, 'msg' => '无任务可领']);
            }
        } else {
            return $this->response->setJSON(['status' => -1, 'msg' => '未选择组员']);
        }
    }
    // 我的配货任务 列表
    public function myProKittingTaskList()
    {
        $current = max((int)$this->request->getGetPost('current'), 1);
        $pageSize = max((int)$this->request->getGetPost('pageSize'), 10);
        $uId = (int)$this->request->getGetPost('uId');
        $uName = $this->request->getGetPost('uName');
        $myTaskType = (int)$this->request->getGetPost('myTaskType');
        $myTaskSortType = (int)$this->request->getGetPost('myTaskSortType');
        $builder = $this->db->table('pro_kitting_task_get');
        $builder->where('isDelete', 0);
        switch ($myTaskType) {
            case 0:
<<<<<<< HEAD
                $builder->where('doneStatus <', 2)->where('upQty < getQty', null, false)->where('isGroupTrans', 0)->where('uId', $uId);
=======
                $builder->where('doneStatus <', 2)->where('upQty <', 'getQty', false)->where('isGroupTrans', 0)->where('uId', $uId);
>>>>>>> 151b415 (更新语言最新版本+最新底层框架后全部差异化代码适配重写)
                break;
            case 1:
                $builder->where('doneStatus', 1)->where('isGroupTrans', 0)->where('uId', $uId);
                break;
            case 2:
                $builder->where('doneStatus <', 2)->groupStart()->groupStart()->where('uId', $uId)->where('transInfo !=', '')->groupEnd()->orLike('transInfo', $this->db->escapeLikeString($uName))->groupEnd();
                break;
            default:
                $builder->where('doneStatus', -1);
                break;
        }
        if ($myTaskSortType == 0) {
            $builder->orderBy('createTime', 'DESC');
        } else {
            $builder->orderBy('lastDate', 'ASC');
        }
        $list = (clone $builder)->limit($pageSize)->offset($pageSize * ($current - 1))->get()->getResultArray();
        $total = $builder->countAllResults();
        $v = [];
        foreach ($list as $arr => $row) {
            $v[$arr]['id'] = intval($row['id']);
            $v[$arr]['iid'] = intval($row['iid']);
            $v[$arr]['lastDate'] = $row['lastDate'];
            $v[$arr]['goodsId'] = intval($row['goodsId']);
            $v[$arr]['goodsText'] = $row['goodsText'];
            $v[$arr]['description'] = $row['description'];
            $v[$arr]['taskQty'] = intval($row['taskQty']);
            $v[$arr]['getQty'] = intval($row['getQty']);
            $v[$arr]['upQty'] = intval($row['upQty']);
            $v[$arr]['doneStatus'] = intval($row['doneStatus']);
            $v[$arr]['uId'] = intval($row['uId']);
            $v[$arr]['uName'] = $row['uName'];
            $v[$arr]['createTime'] = $row['createTime'] == '0000-00-00 00:00:00' ? '' : $row['createTime'];
            $v[$arr]['backInfo'] = $row['backInfo'];
            $v[$arr]['upList'] = $this->db->table('pro_kitting_task_up')->select('id, upQty, doneStatus, createTime')->where(['iiid' => $row['id'], 'isDelete' => 0])->orderBy('id', 'ASC')->get()->getResultArray();
            $v[$arr]['transInfo'] = $row['transInfo'] ? (array)json_decode($row['transInfo'], true) : [];
            $v[$arr]['transHis'] = $row['transHis'];
            if (count($v[$arr]['transInfo']) > 0) {
                $hasTransQty = 0;
                foreach ($v[$arr]['transInfo'] as $k1 => $v1) {
                    $hasTransQty += intval($v1['qty']);
                }
                $v[$arr]['hasTransQty'] = $hasTransQty;
            } else {
                $v[$arr]['hasTransQty'] = 0;
            }
        }
        $json['status'] = 200;
        $json['msg'] = 'success';
        $json['data']['current'] = $current;
        $json['data']['total'] = $total;
        $json['data']['pages'] = ceil($total / $pageSize);
        $json['data']['list'] = $v;
<<<<<<< HEAD
        $json['data']['myTaskTotal'] = $this->db->table('pro_kitting_task_get')->where(['uId' => $uId, 'doneStatus <' => 2, 'isGroupTrans' => 0, 'isDelete' => 0])->where('upQty < getQty', null, false)->countAllResults();
        $json['data']['myTaskUnCheckTotal'] = $this->db->table('pro_kitting_task_get')->where(['uId' =>$uId, 'doneStatus' => 1, 'isGroupTrans' => 0, 'isDelete' => 0])->countAllResults();
        $json['data']['myTaskTransTotal'] = $this->db->table('pro_kitting_task_get')->groupStart()->groupStart()->where('uId', $uId)->where('transInfo !=', '')->groupEnd()->orLike('transInfo', $this->db->escapeLikeString($uName))->groupEnd()->where(['doneStatus <' => 2, 'isDelete' => 0])->countAllResults();
        $json['data']['myTaskTimeoutTotal'] = $this->db->table('pro_kitting_task_get')->where(['uId' => $uId, 'lastDate <' => date('Y-m-d H:i:s'), 'isGroupTrans' => 0, 'isDelete' => 0])->where('upQty < getQty', null, false)->countAllResults();
=======
        $json['data']['myTaskTotal'] = $this->db->table('pro_kitting_task_get')->where(['uId' => $uId, 'doneStatus <' => 2, 'isGroupTrans' => 0, 'isDelete' => 0])->where('upQty <', 'getQty', false)->countAllResults();
        $json['data']['myTaskUnCheckTotal'] = $this->db->table('pro_kitting_task_get')->where(['uId' =>$uId, 'doneStatus' => 1, 'isGroupTrans' => 0, 'isDelete' => 0])->countAllResults();
        $json['data']['myTaskTransTotal'] = $this->db->table('pro_kitting_task_get')->groupStart()->groupStart()->where('uId', $uId)->where('transInfo !=', '')->groupEnd()->orLike('transInfo', $this->db->escapeLikeString($uName))->groupEnd()->where(['doneStatus <' => 2, 'isDelete' => 0])->countAllResults();
        $json['data']['myTaskTimeoutTotal'] = $this->db->table('pro_kitting_task_get')->where(['uId' => $uId, 'lastDate <' => date('Y-m-d H:i:s'), 'isGroupTrans' => 0, 'isDelete' => 0])->where('upQty <', 'getQty', false)->countAllResults();
>>>>>>> 151b415 (更新语言最新版本+最新底层框架后全部差异化代码适配重写)
        return $this->response->setJSON($json);
    }
    // 我的配货任务 获取同任务领取
    public function proKittingTaskGetSame()
    {
        $id = (int)$this->request->getGetPost('id');
        if ($id > 0) {
            $getinfo = $this->db->table('pro_kitting_task_get')->where(['id' => $id])->get()->getRowArray();
            if ($getinfo) {
<<<<<<< HEAD
                $myTaskUpSameGet = $this->db->table('pro_kitting_task_get')->where('iid', $getinfo['iid'])->where('upQty < getQty', null, false)->where('isDelete', 0)->get()->getResultArray();
=======
                $myTaskUpSameGet = $this->db->table('pro_kitting_task_get')->where('iid', $getinfo['iid'])->where('upQty <', 'getQty', false)->where('isDelete', 0)->get()->getResultArray();
>>>>>>> 151b415 (更新语言最新版本+最新底层框架后全部差异化代码适配重写)
                foreach ($myTaskUpSameGet as $k => $v) {
                    $myTaskUpSameGet[$k]['getQty'] = intval($v['getQty']);
                    $myTaskUpSameGet[$k]['upQty'] = intval($v['upQty']);
                    $myTaskUpSameGet[$k]['curUpQty'] = intval($v['getQty']) - intval($v['upQty']);
                    $myTaskUpSameGet[$k]['proGroup'] = $this->db->table('pro_group')->select('name')->where('FIND_IN_SET(' . $this->db->escape($v['uName']) . ', members) >', 0, false)->where('isDelete', 0)->get()->getRowArray()['name'] ?? '';
                }
                return $this->response->setJSON(['status' => 200, 'msg' => 'success', 'data' => ['myTaskUpSameGet' => $myTaskUpSameGet]]);
            }
        }
        return $this->response->setJSON(['status' => -1, 'msg' => 'fail']);
    }
    // 我的配货任务 交付 上传图片
    public function myProKittingTaskUploadImg()
    {
        set_time_limit(0);
        session_write_close();
        if (empty($_FILES)){
            return $this->response->setJSON(['status' => -1, 'msg' => '没有上传文件！']);
        };
        $uppath = 'data/upfile/proKittingTask/';
        $temp_file = $_FILES['file']['tmp_name'];
        $target_file = date("YmdHis") . rand(1000, 9000) . '.' . strrev(explode('.', strrev($_FILES['file']['name']))[0]);
        if (!is_dir($uppath)) {
            @umask(0);
            $ret = @mkdir($uppath, 0777);
            if ($ret === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '创建文件夹失败']);
            }
        }
        move_uploaded_file($temp_file, $uppath . $target_file);
        return $this->response->setJSON(['status' => 200, 'msg' => '上传成功', 'data' => $target_file]);
    }
    // 我的配货任务 交付
    public function myProKittingTaskUp()
    {
        $myTaskUpData = $this->request->getGetPost('myTaskUpData');
        if (strlen($myTaskUpData) > 0) {
            $myTaskUpData = (array)json_decode($myTaskUpData, true);
            $myTaskUpDescription = $this->request->getGetPost('myTaskUpDescription');
            $myTaskUpImgs = $this->request->getGetPost('myTaskUpImgs');
            foreach ($myTaskUpData as $k => $v) {
                $taskGetData = $this->db->table('pro_kitting_task_get')->where(['id' => $v['id'], 'isDelete' => 0])->get()->getRowArray();
                if ((intval($taskGetData['upQty']) + intval($v['curUpQty'])) > intval($taskGetData['getQty'])) {
                    return $this->response->setJSON(['status' => -1, 'msg' => $v['uName'] . '交付超量']);
                }
                if (intval($v['curUpQty']) > 0 && intval($this->db->table('pro_kitting_task_up')->selectSum('upQty', 'totalUpQty')->where(['iiid' => $v['id'], 'upQty >' => 0, 'isDelete' => 0])->get()->getRowArray()['totalUpQty']) >= intval($taskGetData['getQty'])) {
                    return $this->response->setJSON(['status' => -1, 'msg' => $v['uName'] . '交付超量']);
                }
            }
            // 没有超量交付，才操作数据
            $this->db->transStart();
            $proName = [];
            foreach ($myTaskUpData as $k => $v) {
                if (intval($v['curUpQty']) > 0) {
                    $taskGetData = $this->db->table('pro_kitting_task_get')->where(['id' => $v['id'], 'isDelete' => 0])->get()->getRowArray();
                    $info = array_intersect_key($taskGetData, array_flip(['iid', 'lastDate', 'goodsId', 'goodsText', 'taskQty', 'getQty', 'uId', 'uName']));
                    $info['iiid'] = intval($v['id']);
                    $info['upQty'] = intval($v['curUpQty']);
                    $info['upDescription'] = $myTaskUpDescription;
                    $info['upImgs'] = $myTaskUpImgs;
                    $info['doneStatus'] = 1;
                    $info['createTime'] = date('Y-m-d H:i:s');
                    $this->db->table('pro_kitting_task_up')->insert($info);
                    $this->db->table('pro_kitting_task_get')->where(['id' => $info['iiid']])->set('doneStatus', 1)->set('upQty','upQty + ' . $info['upQty'], false)->update();
                    $this->db->table('log')->insert(['log' => '交付了配货任务，任务编号：' . $info['iid'] . '，任务产品：' . $info['goodsText'] . '，交付产量：' . $v['curUpQty'], 'name' => $info['uName'], 'createTime' => date('Y-m-d H:i:s')]);
                    array_push($proName, $info['uName']);
                }
            }
            if (count($proName) > 0 && isset($info['goodsId']) && $info['goodsId'] > 0) {
                $this->db->table('goods')->where(['id' => $info['goodsId']])->update(['proName' => implode(',', $proName)]);
            }
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '交付成功']);
        } else {
            return $this->response->setJSON(['status' => -1, 'msg' => '提交的是空数据']);
        }
    }
    // 我的配货任务 撤回交付
    public function myProKittingTaskUpBack()
    {
        $id = (int)$this->request->getGetPost('id');
        $taskUpData = $this->db->table('pro_kitting_task_up')->where(['id' => $id, 'doneStatus' => 1, 'isDelete' => 0])->get()->getRowArray();
        if ($taskUpData) {
            $this->db->transStart();
            $this->db->table('pro_kitting_task_up')->where(['id' => $id])->update(['isDelete' => 1]);
            $upQtyTotal = $this->db->table('pro_kitting_task_up')->selectSum('upQty', 'upQtyTotal')->where(['iiid' => $taskUpData['iiid'], 'isDelete' => 0])->get()->getRowArray()['upQtyTotal'] ?? 0;
            $unCheckUpNum = $this->db->table('pro_kitting_task_up')->where(['iiid' => $taskUpData['iiid'], 'doneStatus' => 1, 'isDelete' => 0])->countAllResults();
            $this->db->table('pro_kitting_task_get')->where(['id' => $taskUpData['iiid']])->update(['upQty' => $upQtyTotal, 'doneStatus' => $unCheckUpNum == 0 ? 0 : 1]);
            $this->db->table('log')->insert(['log' => '撤回了尚未点货入库的配货交付，任务编号：' . $taskUpData['iid'] . '，任务产品：' . $taskUpData['goodsText'] . '，交付产量：' . $taskUpData['upQty'], 'name' => $taskUpData['uName'], 'createTime' => date('Y-m-d H:i:s')]);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '撤回成功']);
        } else {
            return $this->response->setJSON(['status' => -1, 'msg' => '已入库不可撤回']);
        }
    }
    // 我的配货任务 遇阻反馈
    public function myProKittingTaskFeedback()
    {
        $id = (int)$this->request->getGetPost('id');
        $backInfo = $this->request->getGetPost('backInfo');
        $backName = $this->request->getGetPost('backName');
        $taskGetData = $this->db->table('pro_kitting_task_get')->where(['id' => $id, 'isDelete' => 0])->get()->getRowArray();
        if ($taskGetData && $backInfo && $backName) {
            $this->db->transStart();
            $this->db->table('pro_kitting_task_get')->where(['id' => $id])->update(['backInfo' => $backInfo]);
            $this->db->table('pro_kitting_task')->where(['id' => $taskGetData['iid']])->update(['backInfo' => $backInfo]);
            $this->db->table('log')->insert(['log' => '提交了配货任务遇阻反馈，任务编号：' . $taskGetData['iid'] . '，任务产品：' . $taskGetData['goodsText'] . '，遇阻信息：' . $backInfo, 'name' => $backName, 'createTime' => date('Y-m-d H:i:s')]);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '反馈成功']);
        } else {
            return $this->response->setJSON(['status' => -1, 'msg' => '提交的信息错误']);
        }
    }
    // 我的配货任务 转让任务 获取可转让对像用户信息
    public function myProKittingTaskTransTargetData()
    {
        $type = (int)$this->request->getGetPost('type');
        $taskId = (int)$this->request->getGetPost('taskId');
        $uName = $this->request->getGetPost('uName');
        $myTaskGetData = $this->db->table('pro_kitting_task_get')->where(['id' => $taskId, 'isDelete' => 0])->get()->getRowArray();
        if ($myTaskGetData) {
            $myTaskTransRemainQty = 0;
            $myTaskTransTargetData = [];
            $allGroup = $this->db->table('pro_group')->where(['type' => '配货组', 'isDelete' => 0])->get()->getResultArray();
            $myGroup = $this->db->table('pro_group')->where(['type' => '配货组', 'isDelete' => 0])->where('FIND_IN_SET(' . $this->db->escape($uName) . ', members) >', 0, false)->get()->getRowArray();
            if ($type == 1) { // 整组转让
                // 获取全组人领取的任务数据，计算全组剩余可转让产量
                $groupTaskGetData = $this->db->table('pro_kitting_task_get')->where(['iid' => $myTaskGetData['iid'], 'isDelete' => 0])->whereIn('uName', explode(',', $myGroup['members']))->get()->getResultArray();
                if (count($groupTaskGetData) > 0) {
                    foreach ($groupTaskGetData as $k => $v) {
                        $myTaskTransRemainQty += (intval($v['getQty']) - intval($v['upQty']));
                        if ($v['transInfo']) {
                            $transInfo = (array)json_decode($v['transInfo'], true);
                            if (count($transInfo) > 0) {
                                $hasTransQty = 0;
                                foreach ($transInfo as $k1 => $v1) {
                                    $hasTransQty += intval($v1['qty']);
                                }
                                $myTaskTransRemainQty -= $hasTransQty;
                            }
                        }
                    }
                    foreach ($allGroup as $k2 => $v2) {
                        if (strpos($v2['members'], $uName) === false) {
                            array_push($myTaskTransTargetData, $v2['name'] . ' ' . $v2['members']);
                        }
                    }
                } else {
                    return $this->response->setJSON(['status' => -1, 'msg' => '本组已无此任务']);
                }
            } else { // 单人转让
                $myTaskTransRemainQty += (intval($myTaskGetData['getQty']) - intval($myTaskGetData['upQty']));
                if ($myTaskGetData['transInfo']) {
                    $transInfo = (array)json_decode($myTaskGetData['transInfo'], true);
                    if (count($transInfo) > 0) {
                        $hasTransQty = 0;
                        foreach ($transInfo as $k => $v) {
                            $hasTransQty += intval($v['qty']);
                        }
                        $myTaskTransRemainQty -= $hasTransQty;
                    }
                }
                $groupArr = explode(',', implode(',', array_column($allGroup, 'members')));
                foreach ($groupArr as $k1 => $v1) {
                    if ($v1 != $uName) {
                        array_push($myTaskTransTargetData, $v1);
                    }
                }
            }
            if ($myTaskTransRemainQty >= 0 && count($myTaskTransTargetData) > 0) {
                return $this->response->setJSON(['status' => 200, 'msg' => '获取数据成功', 'data' => ['myTaskTransRemainQty' => $myTaskTransRemainQty, 'myTaskTransTargetData' => $myTaskTransTargetData]]);
            } else {
                return $this->response->setJSON(['status' => -1, 'msg' => '获取数据失败']);
            }
        } else {
            return $this->response->setJSON(['status' => -1, 'msg' => '任务已不存在']);
        }
    }
    // 我的配货任务 转让任务
    public function myProKittingTaskTrans()
    {
        $type = (int)$this->request->getGetPost('type');
        $id = (int)$this->request->getGetPost('id');
        $qty = (int)$this->request->getGetPost('qty');
        $target = $this->request->getGetPost('target');
        if ($type > 0 && $id > 0 && $qty > 0 && $target) {
            $myTaskGetData = $this->db->table('pro_kitting_task_get')->where(['id' => $id, 'isDelete' => 0])->get()->getRowArray();
            if ($myTaskGetData) {
                if ($type == 1) { // 整组转让
                    if (strpos($target, ',') === false) {
                        return $this->response->setJSON(['status' => -1, 'msg' => '目标需是整组人']);
                    }
                    $myTaskTransRemainQty = 0;
                    $myGroup = $this->db->table('pro_group')->where(['type' => '配货组', 'isDelete' => 0])->where('FIND_IN_SET(' . $this->db->escape($myTaskGetData['uName']) . ', members) >', 0, false)->get()->getRowArray();
                    // 获取全组人领取的任务数据，计算全组剩余可转让产量
                    $groupTaskGetData = $this->db->table('pro_kitting_task_get')->where(['iid' => $myTaskGetData['iid'], 'isDelete' => 0])->whereIn('uName', explode(',', $myGroup['members']))->get()->getResultArray();
                    if (count($groupTaskGetData) > 0) {
                        foreach ($groupTaskGetData as $k => $v) {
                            if ($v['transInfo']) {
                                return $this->response->setJSON(['status' => -1, 'msg' => $v['uName'] . '已有转让']);
                            }
                            if ($v['upQty'] > 0) {
                                return $this->response->setJSON(['status' => -1, 'msg' => $v['uName'] . '已有交付']);
                            }
                            $myTaskTransRemainQty += intval($v['getQty']);
                        }
                    } else {
                        return $this->response->setJSON(['status' => -1, 'msg' => '本组已无此任务']);
                    }
                    if ($myTaskTransRemainQty >= $qty) {
                        $targetMembers = explode(' ', $target)[1];
                        $transInfo[$targetMembers] = [
                            'qty' => $qty,
                            'time' => date('Y-m-d H:i:s')
                        ];
                        $this->db->transStart();
                        $this->db->table('pro_kitting_task_get')->where(['id' => $id])->update(['transInfo' => json_encode($transInfo, 320), 'isGroupTrans' => 1]);
                        foreach ($groupTaskGetData as $k1 => $v1) {
                            if ($v1['id'] != $id) {
                                // 假删除其他组员的领取的任务，后面如果对像拒绝转让，要恢复
                                $this->db->table('pro_kitting_task_get')->where(['id' => $v1['id']])->update(['isDelete' => 1, 'isGroupTrans' => 1]);
                            }
                        }
                        $this->db->table('log')->insert(['log' => '整组转让了配货任务，任务编号：' . $myTaskGetData['iid'] . '，任务产品：' . $myTaskGetData['goodsText'] . '，转让对像：' . $targetMembers . '，转让产量：' . $qty, 'name' => $myTaskGetData['uName'], 'createTime' => date('Y-m-d H:i:s')]);
                        $this->db->transComplete();
                        if ($this->db->transStatus() === false) {
                            return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚']);
                        }
                        return $this->response->setJSON(['status' => 200, 'msg' => '转让成功']);
                    } else {
                        return $this->response->setJSON(['status' => -1, 'msg' => '剩余产量不足']);
                    }
                } else { // 单人转让
                    if (strpos($target, ',') !== false) {
                        return $this->response->setJSON(['status' => -1, 'msg' => '目标需是单人']);
                    }
                    if ($myTaskGetData['transInfo']) {
                        $transInfo = (array)json_decode($myTaskGetData['transInfo'], true);
                        $hasTransQty = 0;
                        foreach ($transInfo as $k => $v) {
                            $hasTransQty += intval($v['qty']);
                        }
                        if ((intval($myTaskGetData['getQty']) - intval($myTaskGetData['upQty']) - $hasTransQty) < $qty) {
                            return $this->response->setJSON(['status' => -1, 'msg' => '剩余产量不足']);
                        } elseif (isset($transInfo[$target])) {
                            return $this->response->setJSON(['status' => -1, 'msg' => '任务已转让过Ta']);
                        } else {
                            $transInfo[$target] = [
                                'qty' => $qty,
                                'time' => date('Y-m-d H:i:s')
                            ];
                        }
                    } else {
                        $transInfo[$target] = [
                            'qty' => $qty,
                            'time' => date('Y-m-d H:i:s')
                        ];
                    }
                    $this->db->transStart();
                    $this->db->table('pro_kitting_task_get')->where(['id' => $id])->update(['transInfo' => json_encode($transInfo, 320)]);
                    $this->db->table('log')->insert(['log' => '单人转让了配货任务，任务编号：' . $myTaskGetData['iid'] . '，任务产品：' . $myTaskGetData['goodsText'] . '，转让对像：' . $target . '，转让产量：' . $qty, 'name' => $myTaskGetData['uName'], 'createTime' => date('Y-m-d H:i:s')]);
                    $this->db->transComplete();
                    if ($this->db->transStatus() === false) {
                        return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚']);
                    }
                    return $this->response->setJSON(['status' => 200, 'msg' => '转让成功']);
                }
            } else {
                return $this->response->setJSON(['status' => -1, 'msg' => '任务已不存在']);
            }
        } else {
            return $this->response->setJSON(['status' => -1, 'msg' => '提交的数据不全']);
        }
    }
    // 我的配货任务 转让任务 撤回
    public function myProKittingTaskTransBack()
    {
        $id = (int)$this->request->getGetPost('id');
        $target = $this->request->getGetPost('target');
        if ($id > 0 && $target) {
            $myTaskGetData = $this->db->table('pro_kitting_task_get')->where(['id' => $id, 'isDelete' => 0])->get()->getRowArray();
            if ($myTaskGetData) {
                $transInfo = $myTaskGetData['transInfo'] ? (array)json_decode($myTaskGetData['transInfo'], true) : [];
                if (is_array($transInfo) && count($transInfo) > 0 && isset($transInfo[$target])) {
                    $targetInfo = $transInfo[$target];
                    unset($transInfo[$target]);
                    if (count($transInfo) > 0) {
                        $newTransInfo = json_encode($transInfo, 320);
                    } else {
                        $newTransInfo = '';
                    }
                    $this->db->transStart();
                    $this->db->table('pro_kitting_task_get')->where(['id' => $id])->update(['transInfo' => $newTransInfo]);
                    if (strpos($target, ',') !== false) {
                        // 整组转让的，撤回时要恢复转出方的任务状态
                        $myGroupMembers = $this->db->table('pro_group')->select('members')->where(['type' => '配货组', 'isDelete' => 0])->where('FIND_IN_SET(' . $this->db->escape($myTaskGetData['uName']) . ', members) >', 0, false)->get()->getRowArray()['members'] ?? '';
                        if ($myGroupMembers) {
                            $this->db->table('pro_kitting_task_get')->where('iid', $myTaskGetData['iid'])->whereIn('uName', explode(',', $myGroupMembers))->update(['isDelete' => 0, 'isGroupTrans' => 0]);
                        }
                    }
                    $this->db->table('log')->insert(['log' => '撤回了配货任务' . (strpos($target, ',') !== false ? '整组' : '单人') . '转让，任务编号：' . $myTaskGetData['iid'] . '，任务产品：' . $myTaskGetData['goodsText'] . '，转让对像：' . $target . '，转让产量：' . $targetInfo['qty'], 'name' => $myTaskGetData['uName'], 'createTime' => date('Y-m-d H:i:s')]);
                    $this->db->transComplete();
                    if ($this->db->transStatus() === false) {
                        return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚']);
                    }
                    return $this->response->setJSON(['status' => 200, 'msg' => '撤回成功']);
                } else {
                    return $this->response->setJSON(['status' => -1, 'msg' => '已被接受或拒绝']);
                }
            } else {
                return $this->response->setJSON(['status' => -1, 'msg' => '任务不存在']);
            }
        } else {
            return $this->response->setJSON(['status' => -1, 'msg' => '提交的数据不全']);
        }
    }
    // 我的配货任务 转让任务 接受
    public function myProKittingTaskTransAgree()
    {
        $id = (int)$this->request->getGetPost('id');
        $target = $this->request->getGetPost('target');
        $uName = $this->request->getGetPost('uName');
        if ($id > 0 && $target) {
            $taskGetData = $this->db->table('pro_kitting_task_get')->where(['id' => $id, 'isDelete' => 0])->get()->getRowArray();
            if ($taskGetData) {
                $transInfo = $taskGetData['transInfo'] ? (array)json_decode($taskGetData['transInfo'], true) : [];
                if (is_array($transInfo) && count($transInfo) > 0 && isset($transInfo[$target])) {
                    $targetInfo = $transInfo[$target];
                    if (strpos($target, ',') !== false) {
                        // 整组转让
                        $targetMembers = explode(',', $target);
                        $targetMemberNum = count($targetMembers);
                        $perMemberQty = intdiv(intval($targetInfo['qty']), $targetMemberNum); // 取商为平均领取产量
                        $remainQty = intval($targetInfo['qty']) % $targetMemberNum; // 取余为剩余产量
                        $createTime = date('Y-m-d H:i:s');
                        $data = [];
                        // 取随机数
                        $randKey = rand(0, $targetMemberNum - 1);
                        $this->db->transStart();
                        // 如果任务产量小于组员人数，不足以均分，则随机一名组员领完产量
                        if (intval($targetInfo['qty']) < $targetMemberNum) {
                            $user = $this->db->table('user')->select('id,name')->where('name', $targetMembers[$randKey])->get()->getRowArray();
                            $eachGetData = [
                                'iid' => $taskGetData['iid'],
                                'lastDate' => $taskGetData['lastDate'],
                                'goodsId' => $taskGetData['goodsId'],
                                'goodsText' => $taskGetData['goodsText'],
                                'description' => $taskGetData['description'],
                                'taskQty' => $taskGetData['taskQty'],
                                'getQty' => $targetInfo['qty'],
                                'uId' => $user['id'],
                                'uName' => $user['name'],
                                'createTime' => $createTime,
                                'transHis' => $targetInfo['time'] . ' 由' . $taskGetData['uName'] . '整组转让产量' . $targetInfo['qty'] . "\r\n"
                            ];
                            array_push($data, $eachGetData);
                        } else {
                            // 如果任务产量大于等于组员人数，组员平均领取
                            foreach ($targetMembers as $arr => $row) {
                                $user = $this->db->table('user')->select('id,name')->where('name', $row)->get()->getRowArray();
                                $eachGetData = [
                                    'iid' => $taskGetData['iid'],
                                    'lastDate' => $taskGetData['lastDate'],
                                    'goodsId' => $taskGetData['goodsId'],
                                    'goodsText' => $taskGetData['goodsText'],
                                    'description' => $taskGetData['description'],
                                    'taskQty' => $taskGetData['taskQty'],
                                    'getQty' => $arr == $randKey ? ($perMemberQty + $remainQty) : $perMemberQty, // 剩余产量加给随机一名组员
                                    'uId' => $user['id'],
                                    'uName' => $user['name'],
                                    'createTime' => $createTime,
                                    'transHis' => $targetInfo['time'] . ' 由' . $taskGetData['uName'] . '整组转让产量' . $targetInfo['qty'] . ' 分得产量' . ($arr == $randKey ? ($perMemberQty + $remainQty) : $perMemberQty) . "\r\n"
                                ];
                                array_push($data, $eachGetData);
                            }
                        }
                        $this->db->table('pro_kitting_task_get')->insertBatch($data);
                        $fromGroupMembers = $this->db->table('pro_group')->select('members')->where(['type' => '配货组', 'isDelete' => 0])->where('FIND_IN_SET(' . $this->db->escape($taskGetData['uName']) . ', members) >', 0, false)->get()->getRowArray()['members'] ?? '';
                        if ($fromGroupMembers) {
                            $this->db->table('pro_kitting_task_get')->where('iid', $taskGetData['iid'])->whereIn('uName', explode(',', $fromGroupMembers))->update(['isDelete' => 1]);
                        }
                        $this->db->table('log')->insert(['log' => '接受' . $taskGetData['uName'] . '整组转让的配货任务，任务编号：' . $taskGetData['iid'] . '，任务产品：' . $taskGetData['goodsText'] . '，转让产量：' . $targetInfo['qty'], 'name' => $uName ?? $target, 'createTime' => date('Y-m-d H:i:s')]);
                        $this->db->transComplete();
                        if ($this->db->transStatus() === false) {
                            return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚']);
                        }
                        return $this->response->setJSON(['status' => 200, 'msg' => '接受成功']);
                    } else {
                        // 单人转让
                        if (($taskGetData['getQty'] - $taskGetData['upQty']) < $targetInfo['qty']) {
                            return $this->response->setJSON(['status' => -1, 'msg' => '剩余产量不足']);
                        }
                        unset($transInfo[$target]);
                        if (count($transInfo) > 0) {
                            $newTransInfo = json_encode($transInfo, 320);
                        } else {
                            $newTransInfo = '';
                        }
                        $createTime = date('Y-m-d H:i:s');
                        $newTransHis = $taskGetData['transHis'] . $createTime . ' ' . $target . '接受' . $taskGetData['uName'] . '单人转让的' . $targetInfo['qty'] . '产量' . "\r\n";
                        $user = $this->db->table('user')->where(['name' => $target])->get()->getRowArray();
                        $data = [
                            'iid' => $taskGetData['iid'],
                            'lastDate' => $taskGetData['lastDate'],
                            'goodsId' => $taskGetData['goodsId'],
                            'goodsText' => $taskGetData['goodsText'],
                            'description' => $taskGetData['description'],
                            'taskQty' => $taskGetData['taskQty'],
                            'getQty' => $targetInfo['qty'],
                            'uId' => $user['id'],
                            'uName' => $user['name'],
                            'createTime' => $createTime,
                            'transHis' => $targetInfo['time'] . ' 由' . $taskGetData['uName'] . '单人转让产量' . $targetInfo['qty'] . "\r\n"
                        ];
                        $this->db->transStart();
                        $this->db->table('pro_kitting_task_get')->insert($data);
                        if ($taskGetData['getQty'] - $targetInfo['qty'] <= 0) {
                            $this->db->table('pro_kitting_task_get')->where('id', $id)->update(['isDelete' => 1]);
                        } else {
                            $this->db->table('pro_kitting_task_get')->where('id', $id)->update(['getQty' => $taskGetData['getQty'] - $targetInfo['qty'], 'transInfo' => $newTransInfo, 'transHis' => $newTransHis]);
                        }
                        $this->db->table('log')->insert(['log' => '接受' . $taskGetData['uName'] . '单人转让的配货任务，任务编号：' . $taskGetData['iid'] . '，任务产品：' . $taskGetData['goodsText'] . '，转让产量：' . $targetInfo['qty'], 'name' => $uName ?? $target, 'createTime' => date('Y-m-d H:i:s')]);
                        $this->db->transComplete();
                        if ($this->db->transStatus() === false) {
                            return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚']);
                        }
                        return $this->response->setJSON(['status' => 200, 'msg' => '接受成功']);
                    }
                } else {
                    return $this->response->setJSON(['status' => -1, 'msg' => '转让已被撤回']);
                }
            } else {
                return $this->response->setJSON(['status' => -1, 'msg' => '任务不存在']);
            }
        } else {
            return $this->response->setJSON(['status' => -1, 'msg' => '提交的数据不全']);
        }
    }
    // 我的配货任务 转让任务 拒绝
    public function myProKittingTaskTransDisagree()
    {
        $id = (int)$this->request->getGetPost('id');
        $target = $this->request->getGetPost('target');
        $uName = $this->request->getGetPost('uName');
        if ($id > 0 && $target) {
            $taskGetData = $this->db->table('pro_kitting_task_get')->where(['id' => $id, 'isDelete' => 0])->get()->getRowArray();
            if ($taskGetData) {
                $transInfo = $taskGetData['transInfo'] ? (array)json_decode($taskGetData['transInfo'], true) : [];
                if (is_array($transInfo) && count($transInfo) > 0 && isset($transInfo[$target])) {
                    $targetInfo = $transInfo[$target];
                    unset($transInfo[$target]);
                    if (count($transInfo) > 0) {
                        $newTransInfo = json_encode($transInfo, 320);
                    } else {
                        $newTransInfo = '';
                    }
                    $createTime = date('Y-m-d H:i:s');
                    $newTransHis = $taskGetData['transHis'] . $createTime . ' ' . ($uName ?? $target) . '拒绝' . $taskGetData['uName'] . (strpos($target, ',') !== false ? '整组' : '单人') . '转让的' . $targetInfo['qty'] . '产量' . "\r\n";
                    $this->db->transStart();
                    $this->db->table('pro_kitting_task_get')->where('id', $id)->update(['transInfo' => $newTransInfo, 'transHis' => $newTransHis]);
                    if (strpos($target, ',') !== false) {
                        // 整组转让的，拒绝时要恢复转出方的任务状态
                        $groupMembers = $this->db->table('pro_group')->select('members')->where(['type' => '配货组', 'isDelete' => 0])->where('FIND_IN_SET(' . $this->db->escape($taskGetData['uName']) . ', members) >', 0, false)->get()->getRowArray()['members'] ?? '';
                        if ($groupMembers) {
                            $this->db->table('pro_kitting_task_get')->where('iid', $taskGetData['iid'])->whereIn('uName', explode(',', $groupMembers))->update(['isDelete' => 0, 'isGroupTrans' => 0]);
                        }
                    }
                    $this->db->table('log')->insert(['log' => '拒绝' . $taskGetData['uName'] . (strpos($target, ',') !== false ? '整组' : '单人') . '转让的配货任务，任务编号：' . $taskGetData['iid'] . '，任务产品：' . $taskGetData['goodsText'] . '，转让产量：' . $targetInfo['qty'], 'name' => $uName ?? $target, 'createTime' => date('Y-m-d H:i:s')]);
                    $this->db->transComplete();
                    if ($this->db->transStatus() === false) {
                        return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚']);
                    }
                    return $this->response->setJSON(['status' => 200, 'msg' => '拒绝成功']);
                } else {
                    return $this->response->setJSON(['status' => -1, 'msg' => '转让已被撤回']);
                }
            } else {
                return $this->response->setJSON(['status' => -1, 'msg' => '任务不存在']);
            }
        } else {
            return $this->response->setJSON(['status' => -1, 'msg' => '提交的数据不全']);
        }
    }


    // 配货交付 列表
    public function proKittingTaskUpList()
    {
        $current = max((int)$this->request->getGetPost('current'), 1);
        $pageSize = max((int)$this->request->getGetPost('pageSize'), 10);
        $doneStatus = (int)$this->request->getGetPost('doneStatus') ?? 0;
        $skey = $this->request->getGetPost('skey');
        $builder = $this->db->table('pro_kitting_task_up');
        $builder->where('isDelete', 0);
        if ($skey !== '') {
            $keywords = array_values(array_filter(explode(',', $skey), 'strlen'));
            foreach ($keywords as $kw) {
                $builder->groupStart()
                    ->like('goodsText', $this->db->escapeLikeString($kw))
                    ->orLike('upDescription', $this->db->escapeLikeString($kw))
                    ->orLike('uName', $this->db->escapeLikeString($kw))
                ->groupEnd();
            }
        }
        if ($doneStatus > 0) {
            $builder->where('doneStatus', $doneStatus);
        }
        $list = (clone $builder)->orderBy('id', 'DESC')->limit($pageSize)->offset($pageSize * ($current - 1))->get()->getResultArray();
        $total = $builder->countAllResults();
        $v = [];
        foreach ($list as $arr => $row) {
            $v[$arr]['id'] = intval($row['id']);
            $v[$arr]['iid'] = intval($row['iid']);
            $v[$arr]['goodsId'] = intval($row['goodsId']);
            $v[$arr]['goodsText'] = $row['goodsText'];
            $v[$arr]['upQty'] = intval($row['upQty']);
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
        $json['data']['current'] = $current;
        $json['data']['total'] = $total;
        $json['data']['pages'] = ceil($total / $pageSize);
        $json['data']['list'] = $v;
        return $this->response->setJSON($json);
    }
    // 配货交付 获取同任务交付
    public function proKittingTaskUpSame()
    {
        $id = (int)$this->request->getGetPost('id');
        if ($id > 0) {
            $upinfo = $this->db->table('pro_kitting_task_up')->where('id', $id)->get()->getRowArray();
            if ($upinfo) {
                $sameUp = $this->db->table('pro_kitting_task_up')->where(['iid' => $upinfo['iid'], 'doneStatus' => 1, 'isDelete' => 0])->get()->getResultArray();
                foreach ($sameUp as $k => $v) {
                    $sameUp[$k]['getQty'] = intval($v['getQty']);
                    $sameUp[$k]['upQty'] = intval($v['upQty']);
                    $sameUp[$k]['proGroup'] = $this->db->table('pro_group')->select('name')->where('FIND_IN_SET(' . $this->db->escape($v['uName']) . ', members) >', 0, false)->where('isDelete', 0)->get()->getRowArray()['name'] ?? '';
                }
                return $this->response->setJSON(['status' => 200, 'msg' => 'sucess', 'data' => ['sameUp' => $sameUp]]);
            }
        }
        return $this->response->setJSON(['status' => -1, 'msg' => 'fail']);
    }
    // 配货交付 点货入库与取消入库
    public function proKittingTaskUpCheck()
    {
        $curid = (int)$this->request->getGetPost('curid');
        $selectedids = $this->request->getGetPost('selectedids');
        $selectedids = strlen($selectedids) > 0 ? explode(',', $selectedids) : [];
        $status = (int)$this->request->getGetPost('status');
        $qty = (float)$this->request->getGetPost('qty');
        $weight = (float)$this->request->getGetPost('weight');
        $checkId = (int)$this->request->getGetPost('checkId');
        $checkName = $this->request->getGetPost('checkName');
        $checkTime = $checkId > 0 ? date('Y-m-d H:i:s') : '0000-00-00 00:00:00';
        $user = $this->db->table('user')->where('id', $checkId)->get()->getRowArray();
        if ($user) {
            $rights = explode(',', $user['lever']);
            if (in_array(707, $rights) || $user['roleId'] == 1) { // 有点货入库权限或管理员角色
                if ($curid > 0) {
                    if (count($selectedids) == 0) {
                        $selectedids = [$curid];
                    }
                    $this->db->transStart();
                    $upData = $getData = $data = [];
                    foreach ($selectedids as $k => $id) {
                        $upData = $this->db->table('pro_kitting_task_up')->where('id', $id)->get()->getRowArray();
                        $getData = $this->db->table('pro_kitting_task_get')->where('id', $upData['iiid'])->get()->getRowArray();
                        $data = $this->db->table('pro_kitting_task')->where('id', $upData['iid'])->get()->getRowArray();
                        // 更新交付状态、业绩提成
                        if ($status == 2) {
                            $price = $this->db->table('goods')->select('producePrice3')->where('id', $upData['goodsId'])->get()->getRowArray()['producePrice3'] ?? 0;
                            $amount = round((float)$price * (float)$upData['upQty'], 2);
                            $this->db->table('pro_kitting_task_up')->where('id', $id)->update(['doneStatus' => $status, 'checkId ' => $checkId, 'checkName' => $checkName, 'checkTime' => $checkTime, 'weight' => round($weight / $qty * (float)$upData['upQty'], 5), 'price' => $price, 'amount' => $amount]);
                        } else {
                            $this->db->table('pro_kitting_task_up')->where('id', $id)->update(['doneStatus' => $status, 'checkId ' => 0, 'checkName' => '', 'checkTime' => '0000-00-00 00:00:00', 'weight' => 0, 'price' => 0, 'amount' => 0]);
                        }
                        // 更新已领任务状态
                        $uncheckCount = $this->db->table('pro_kitting_task_up')->where(['iiid' => $upData['iiid'], 'doneStatus' => 1, 'isDelete' => 0])->countAllResults();
                        if ($uncheckCount > 0) {
                            $this->db->table('pro_kitting_task_get')->where('id', $upData['iiid'])->update( ['doneStatus' => 1]);
                        } else {
                            if ($getData['upQty'] >= $getData['getQty']) {
                                $this->db->table('pro_kitting_task_get')->where('id', $upData['iiid'])->update(['doneStatus' => 2]);
                            } else {
                                $this->db->table('pro_kitting_task_get')->where('id', $upData['iiid'])->update(['doneStatus' => 0]);
                            }
                        }
                        // 处理库存 与 库存流水
                        $this->db->table('goods')->where('id', $upData['goodsId'])->set('currentQty', 'currentQty' . ($status == 2 ? ' + ' : ' - ') . $upData['upQty'], false)->set('billDate' , date('Y-m-d'))->update();
                        $goods = $this->db->table('goods')->where('id', $upData['goodsId'])->get()->getRowArray();
                        $invHisData['goodsId'] = $upData['goodsId'];
                        $invHisData['billId'] = $upData['iid'];
                        $invHisData['billNo'] = '配货任务编号：' . $upData['iid'];
                        $invHisData['billType'] = 'PK';
                        $invHisData['transType'] = 101;
                        $invHisData['changeType'] = '配货任务交付' . ($status == 2 ? '点货入库' : '取消入库');
                        $invHisData['changeTime'] = date('Y-m-d H:i:s');
                        $invHisData['changeCurrentQty'] = ($status == 2 ? '+' : '-') . abs((float)$upData['upQty']);
                        $invHisData['currentQty'] = (float)$goods['currentQty'];
                        $this->db->table('inv_history')->insert($invHisData);
                        if ($goods['hasSon'] > 0 && strlen($goods['sonGoods']) > 0) {
                            $sonGoods = (array)json_decode($goods['sonGoods'], true);
                            foreach ($sonGoods as $arr4 => $row4) {
                                $this->db->table('goods')->where('id', $row4['gid'])->set('currentQty', 'currentQty' . ($status == 2 ? ' - ' : ' + ') . ($row4['qty1'] * $row4['qty2'] * $upData['upQty']), false)->set('billDate', date('Y-m-d'))->update();
                                $newSonGoods = $this->db->table('goods')->select('currentQty')->where('id', $row4['gid'])->get()->getRowArray();
                                $invHisData['goodsId'] = $row4['gid'];
                                $invHisData['billId'] = $upData['iid'];
                                $invHisData['billNo'] = '配货任务编号：' . $upData['iid'];
                                $invHisData['billType'] = 'PK';
                                $invHisData['transType'] = 102;
                                $invHisData['changeType'] = '配货任务交付' . ($status == 2 ? '点货入库' : '取消入库');
                                $invHisData['changeTime'] = date('Y-m-d H:i:s');
                                $invHisData['changeCurrentQty'] = ($status == 2 ? '-' : '+') . abs((float)$row4['qty1'] * (float)$row4['qty2'] * (float)$upData['upQty']);
                                $invHisData['currentQty'] = (float)$newSonGoods['currentQty'];
                                $this->db->table('inv_history')->insert($invHisData);
                            }
                        }
                        $this->db->table('log')->insert(['log' => ($status == 2 ? '点货入库了 ' : '取消入库了 ') . $upData['uName'] . ' 的配货任务交付。任务编号：' . $upData['iid'] . '，任务产品：' . $upData['goodsText'] . '，交付产量：' . $upData['upQty'], 'name' => $checkName, 'createTime' => date('Y-m-d H:i:s')]);
                    }
                    // 更新主任务状态
                    $sumDoneQty = $this->db->table('pro_kitting_task_up')->selectSum('upQty', 'sumDoneQty')->where(['iid' => $data['id'], 'doneStatus' => 2, 'isDelete' => 0])->get()->getRowArray()['sumDoneQty'] ?? 0;
                    if ($sumDoneQty > 0 && $sumDoneQty < $data['qty']) {
                        $taskStatus = 2;
                    } elseif ($sumDoneQty >= $data['qty']) {
                        $taskStatus = 3;
                    } else {
                        $taskStatus = 1;
                    }
                    $this->db->table('pro_kitting_task')->where('id', $data['id'])->update(['taskStatus ' => $taskStatus]);
                    // 更新产品重量
                    if ($weight > 0) {
                        $this->db->table('goods')->where('id', $upData['goodsId'])->update(['weight ' => round($weight / $qty, 5)]);
                    }
                    $this->db->transComplete();
                    if ($this->db->transStatus() === false) {
                        return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚']);
                    }
                    return $this->response->setJSON(['status' => 200, '操作成功' => '事务已回滚', 'data' => ['doneStatus' => $status]]);
                } else {
                    return $this->response->setJSON(['status' => -1, 'msg' => '提交的是空数据']);
                }
            } else {
                return $this->response->setJSON(['status' => -1, 'msg' => '无此权限']);
            }
        } else {
            return $this->response->setJSON(['status' => -1, 'msg' => '无此用户']);
        }
    }


    // 加工申请 列表
    public function proProcessingReqList()
    {
        $current = max((int)$this->request->getGetPost('current'), 1);
        $pageSize = max((int)$this->request->getGetPost('pageSize'), 10);
        $skey = $this->request->getGetPost('skey');
        $type = $this->request->getGetPost('type');
        $uId = (int)$this->request->getGetPost('uId');
        $builder = $this->db->table('pro_processing_req');
        $builder->where('isDelete', 0);
        if ($skey !== '') {
            $keywords = array_values(array_filter(explode(',', $skey), 'strlen'));
            foreach ($keywords as $kw) {
                $builder->groupStart()
                    ->like('projectName', $this->db->escapeLikeString($kw))
                    ->orLike('content', $this->db->escapeLikeString($kw))
                    ->orLike('uName', $this->db->escapeLikeString($kw))
                    ->orLike('description', $this->db->escapeLikeString($kw))
                ->groupEnd();
            }
        }
        if ($type == 'self') {
            $builder->where('uId', $uId);
        }
        $list = (clone $builder)->orderBy('id', 'DESC')->limit($pageSize)->offset($pageSize * ($current - 1))->get()->getResultArray();
        $total = $builder->countAllResults();
        $v = [];
        $user = $this->db->table('user')->where('id', $uId)->get()->getRowArray();
        foreach ($list as $arr => $row) {
            $v[$arr]['id'] = intval($row['id']);
            $v[$arr]['lastDate'] = ($user['position'] == '加工专员' && $row['uId'] != $uId) ? '' : $row['lastDate']; // 隐藏交付限期，避免作弊
            $v[$arr]['projectId'] = intval($row['projectId']);
            $v[$arr]['projectType'] = intval($row['projectType']);
            $v[$arr]['projectName'] = $row['projectName'];
            $v[$arr]['needTest'] = intval($row['needTest']);
            $v[$arr]['qty'] = intval($row['qty']);
            $v[$arr]['content'] = $row['content'];
            $v[$arr]['uId'] = intval($row['uId']);
            $v[$arr]['uName'] = $row['uName'];
            $v[$arr]['createTime'] = $row['createTime'] == '0000-00-00 00:00:00' ? '' : $row['createTime'];
            $v[$arr]['checkId'] = intval($row['checkId']);
            $v[$arr]['produceId'] = intval($row['produceId']);
            $v[$arr]['description'] = $row['description'];
            $v[$arr]['taskId'] = intval($row['taskId']);
            if ($v[$arr]['taskId'] > 0) {
                $v[$arr]['taskGet'] = $this->db->table('pro_processing_task_get')->where(['iid' => $v[$arr]['taskId'], 'isDelete' => 0])->get()->getResultArray();
            } else {
                $v[$arr]['taskGet'] = [];
            }
        }
        $json['status'] = 200;
        $json['msg'] = 'success';
        $json['data']['current'] = $current;
        $json['data']['total'] = $total;
        $json['data']['pages'] = ceil($total / $pageSize);
        $json['data']['list'] = $v;
        return $this->response->setJSON($json);
    }
    // 加工申请 提交
    public function proProcessingReqSubmit()
    {
        $data = $this->request->getPost();
        if (is_array($data) && count($data) > 0) {
            $data['createTime'] = date('Y-m-d H:i:s');
            if (strtotime($data['lastDate']) - strtotime($data['createTime']) < 18000) {
                return $this->response->setJSON(['status' => -1, 'msg' => '交付限期至少留足5小时以上']);
            }
            $project = $this->db->table('pro_processing_project')->where('id', $data['projectId'])->get()->getRowArray();
            if (!$project || $project['isDelete'] == 1) {
                return $this->response->setJSON(['status' => -1, 'msg' => '加工项目不存在']);
            }
            if ($project['isDisable'] == 1){
                return $this->response->setJSON(['status' => -1, 'msg' => '将停产不再生产']);
            }
            if ($project['isDisable'] == 2) {
                return $this->response->setJSON(['status' => -1, 'msg' => '已停产不再生产']);
            }
            if ($project['isDisable'] == 9) {
                return $this->response->setJSON(['status' => -1, 'msg' => '已禁用不再生产']);
            }
            $this->db->transStart();
            $data['projectType'] = $project['type'];
            $data['needTest'] = $project['needTest'];
            $this->db->table('pro_processing_req')->insert($data);
            $resid = $this->db->insertID();
            $this->db->table('log')->insert(['log' => '提交了加工申请，ID: ' . $resid . '，项目名称：' . $data['projectName'] . '，数量：' . $data['qty'], 'name' => $data['uName'], 'createTime' => date('Y-m-d H:i:s')]);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '提交成功', 'data' => ['id' => $resid, 'createTime' => $data['createTime']]]);
        } else {
            return $this->response->setJSON(['status' => -1, 'msg' => '提交数据不完整']);
        }
    }
    // 加工申请 撤销
    public function proProcessingReqDel()
    {
        $id = (int)$this->request->getGetPost('id');
        if ($id > 0) {
            $data = $this->db->table('pro_processing_req')->where(['id' => $id, 'isDelete' => 0])->get()->getRowArray();
            if ($data) {
                $this->db->transStart();
                $this->db->table('pro_processing_req')->where('id', $id)->update(['isDelete' => 1]);
                $this->db->table('log')->insert(['log' => '撤销了加工申请，ID: ' . $id . '，项目名称：' . $data['projectName'] . '，数量：' . $data['qty'], 'name' => $data['uName'], 'createTime' => date('Y-m-d H:i:s')]);
                $this->db->transComplete();
                if ($this->db->transStatus() === false) {
                    return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚']);
                }
                return $this->response->setJSON(['status' => 200, 'msg' => '撤销成功']);
            } else {
                return $this->response->setJSON(['status' => -1, 'msg' => '申请已撤销过或已不存在']);
            }
        } else {
            return $this->response->setJSON(['status' => -1, 'msg' => '提交数据不完整']);
        }
    }


    // 加工任务 获取可领取任务数量
    public function proProcessingTaskCanTakeNum()
    {
        $takeTotal = $this->db->table('pro_processing_task')->where(['takeStatus' => 0, 'checkId >' => 0, 'isDelete' => 0])->countAllResults();
        $json['status'] = 200;
        $json['msg'] = 'success';
        $json['data']['takeTotal'] = $takeTotal;
        return $this->response->setJSON($json);
    }
    // 加工任务 合并领取 选择加工专员
    public function proProcessingTaskTakeGroup()
    {
        $uName = $this->request->getGetPost('uName');
        if ($uName) {
            $groups = $this->db->table('pro_group')->where(['type' => '加工组', 'isDelete' => 0])->get()->getResultArray();
            if ($groups) {
                $isMember = false;
                foreach ($groups as $k => $v) {
                    if (in_array($uName, explode(',', $v['members']))) {
                        $isMember = true;
                    }
                }
                if ($isMember == false) {
                    return $this->response->setJSON(['status' => -1, 'msg' => '不是加工组成员']);
                }
                $takeGroup = $this->db->table('user')->select('id,name')->where(['position' => '加工专员', 'isDisable' => 0, 'isDelete' => 0])->get()->getResultArray();
                if ($takeGroup) {
                    foreach ($takeGroup as $arr => $user) {
                        $takeGroup[$arr]['groupName'] = '';
                        foreach ($groups as $group) {
                            if (in_array($user['name'], explode(',', $group['members']))) {
                                $takeGroup[$arr]['groupName'] = $group['name'];
                                break;
                            }
                        }
                        if ($takeGroup[$arr]['groupName'] == '') {
                            return $this->response->setJSON(['status' => -1, 'msg' => '加工组和加工专员不匹配']);
                        }
                        $clockinInfo = $this->db->table('clockin')->where(['uId' => $user['id'], 'clockDate ' => date('Y-m-d'), 'isDelete' => 0])->get()->getRowArray();
                        if ($clockinInfo) {
                            if ($clockinInfo['amClock'] != '00:00:00') {
                                $takeGroup[$arr]['onDuty'] = 1;
                            } else {
                                $takeGroup[$arr]['onDuty'] = 0;
                            }
                        } else {
                            $takeGroup[$arr]['onDuty'] = 0;
                        }
                    }
                    // （二维数组）依次按字段排序
                    if (count($takeGroup) > 0) {
                        $sort1 = array_column($takeGroup, 'groupName');
                        $sort2 = array_column($takeGroup, 'id');
                        array_multisort($sort1, SORT_ASC, $sort2, SORT_ASC, $takeGroup);
                    }
                    $json['status'] = 200;
                    $json['msg'] = 'success';
                    $json['data']['takeGroup'] = $takeGroup;
                    return $this->response->setJSON($json);
                } else {
                    return $this->response->setJSON(['status' => -1, 'msg' => '没找到加工专员']);
                }
            } else {
                return $this->response->setJSON(['status' => -1, 'msg' => '不是加工组成员']);
            }
        } else {
            return $this->response->setJSON(['status' => -1, 'msg' => '重新登录再尝试']);
        }
    }
    // 加工任务 合并领取 提交
    public function proProcessingTaskTakeTotalSubmit()
    {
        $producerIds = $this->request->getGetPost('producerIds');
        $testerIds = $this->request->getGetPost('testerIds');
        $uId = (int)$this->request->getGetPost('uId');
        $uName = $this->request->getGetPost('uName');
        if (!$uId) {
            return $this->response->setJSON(['status' => -1, 'msg' => '账号错误，请重新登录！']);
        }
        if ($producerIds && $testerIds) {
            // 取一个交付限期最紧急的可领取任务
            $taskData = $this->db->table('pro_processing_task')->where(['takeStatus' => 0, 'checkId >' => 0, 'isDelete' => 0])->orderBy('lastDate', 'ASC')->get()->getRowArray();
            if ($taskData) {
                $createTime = date('Y-m-d H:i:s');
                $produceData = [];
                $testData = [];
                $takeName = [];
                // 先处理加工工序领取
                $producerIds = explode(',', $producerIds);
                $producerNum = count($producerIds);
                // 计算任务产量除以组员人数的商和余数，来平均分配产量，余数(剩余产量)加给随机组员
                $perProduceQty = intdiv(intval($taskData['qty']), $producerNum); // 取商为平均领取产量
                $remainProduceQty = intval($taskData['qty']) % $producerNum; // 取余为剩余产量
                // 取合理随机数
                $randKey = rand(0, $producerNum - 1);
                // 如果任务产量小于组员人数，不足以均分，则随机一名组员领完产量
                if (intval($taskData['qty']) < $producerNum) {
                    $user = $this->db->table('user')->select('id,name')->where('id', $producerIds[$randKey])->get()->getRowArray();
                    $eachGetData = [
                        'iid' => $taskData['id'],
                        'lastDate' => $taskData['lastDate'],
                        'projectId' => $taskData['projectId'],
                        'projectType' => $taskData['projectType'],
                        'projectName' => $taskData['projectName'],
                        'needTest' => $taskData['needTest'],
                        'description' => $taskData['description'],
                        'taskQty' => $taskData['qty'],
                        'getQty' => $taskData['qty'],
                        'isTest' => 0,
                        'uId' => $user['id'],
                        'uName' => $user['name'],
                        'createTime' => $createTime
                    ];
                    array_push($produceData, $eachGetData);
                    array_push($takeName, $user['name']);
                } else {
                    // 如果任务产量大于等于组员人数，组员平均领取
                    foreach ($producerIds as $arr => $row) {
                        $user = $this->db->table('user')->select('id,name')->where('id', $row)->get()->getRowArray();
                        $eachGetData = [
                            'iid' => $taskData['id'],
                            'lastDate' => $taskData['lastDate'],
                            'projectId' => $taskData['projectId'],
                            'projectType' => $taskData['projectType'],
                            'projectName' => $taskData['projectName'],
                            'needTest' => $taskData['needTest'],
                            'description' => $taskData['description'],
                            'taskQty' => $taskData['qty'],
                            'getQty' => $arr == $randKey ? ($perProduceQty + $remainProduceQty) : $perProduceQty, // 剩余产量加给随机一名组员
                            'isTest' => 0,
                            'uId' => $user['id'],
                            'uName' => $user['name'],
                            'createTime' => $createTime
                        ];
                        array_push($produceData, $eachGetData);
                        array_push($takeName, $user['name']);
                    }
                }
                // 再处理测试工序领取
                if ($taskData['needTest'] == 1 && $testerIds) {
                    $testerIds = explode(',', $testerIds);
                    $testerNum = count($testerIds);
                    // 计算任务产量除以组员人数的商和余数，来平均分配产量，余数(剩余产量)加给随机组员
                    $perTestQty = intdiv(intval($taskData['qty']), $testerNum); // 取商为平均领取产量
                    $remainTestQty = intval($taskData['qty']) % $testerNum; // 取余为剩余产量
                    // 取合理随机数
                    $randKey = rand(0, $testerNum - 1);
                    // 如果任务产量小于组员人数，不足以均分，则随机一名组员领完产量
                    if (intval($taskData['qty']) < $testerNum) {
                        $user = $this->db->table('user')->select('id,name')->where('id', $testerIds[$randKey])->get()->getRowArray();
                        $eachGetData = [
                            'iid' => $taskData['id'],
                            'lastDate' => $taskData['lastDate'],
                            'projectId' => $taskData['projectId'],
                            'projectType' => $taskData['projectType'],
                            'projectName' => $taskData['projectName'],
                            'needTest' => $taskData['needTest'],
                            'description' => $taskData['description'],
                            'taskQty' => $taskData['qty'],
                            'getQty' => $taskData['qty'],
                            'isTest' => 1,
                            'uId' => $user['id'],
                            'uName' => $user['name'],
                            'createTime' => $createTime
                        ];
                        array_push($testData, $eachGetData);
                        array_push($takeName, $user['name']);
                    } else {
                        // 如果任务产量大于等于组员人数，组员平均领取
                        foreach ($testerIds as $arr => $row) {
                            $user = $this->db->table('user')->select('id,name')->where('id', $row)->get()->getRowArray();
                            $eachGetData = [
                                'iid' => $taskData['id'],
                                'lastDate' => $taskData['lastDate'],
                                'projectId' => $taskData['projectId'],
                                'projectType' => $taskData['projectType'],
                                'projectName' => $taskData['projectName'],
                                'needTest' => $taskData['needTest'],
                                'description' => $taskData['description'],
                                'taskQty' => $taskData['qty'],
                                'getQty' => $arr == $randKey ? ($perTestQty + $remainTestQty) : $perTestQty, // 剩余产量加给随机一名组员
                                'isTest' => 1,
                                'uId' => $user['id'],
                                'uName' => $user['name'],
                                'createTime' => $createTime
                            ];
                            array_push($testData, $eachGetData);
                            array_push($takeName, $user['name']);
                        }
                    }
                }
                $this->db->transStart();
                $this->db->table('pro_processing_task_get')->insertBatch($produceData);
                if ($testData) {
                    $this->db->table('pro_processing_task_get')->insertBatch($testData);
                }
                $taskStatus = $taskData['taskStatus'] == 0 ? 1 : $taskData['taskStatus'];
                $this->db->table('pro_processing_task')->where('id', $taskData['id'])->update(['takeStatus' => 2, 'takeName' => implode(',', $takeName), 'taskStatus' => $taskStatus]);
                $this->db->table('log')->insert(['log' => '代【' . implode(',', $takeName) . '】合并领取了加工任务，任务编号：' . $taskData['id'] . '，加工项目：' . $taskData['projectName'] . '，任务产量：' . $taskData['qty'], 'name' => $uName, 'createTime' => date('Y-m-d H:i:s')]);
                $selfTaskGet = $this->db->table('pro_processing_task_get')->where(['iid' => $taskData['id'], 'uId' => $uId, 'createTime' => $createTime])->get()->getRowArray();
                $this->db->transComplete();
                if ($this->db->transStatus() === false) {
                    return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚']);
                }
                return $this->response->setJSON(['status' => 200, 'msg' => '领取成功', 'data' => $selfTaskGet]);

            } else {
                return $this->response->setJSON(['status' => -1, 'msg' => '无任务可领']);
            }
        } else {
            return $this->response->setJSON(['status' => -1, 'msg' => '未选择加工人员']);
        }
    }
    // 我的加工任务 列表
    public function myProProcessingTaskList()
    {
        $current = max((int)$this->request->getGetPost('current'), 1);
        $pageSize = max((int)$this->request->getGetPost('pageSize'), 10);
        $uId = (int)$this->request->getGetPost('uId');
        $uName = $this->request->getGetPost('uName');
        $myTaskType = (int)$this->request->getGetPost('myTaskType');
        $myTaskSortType = (int)$this->request->getGetPost('myTaskSortType');
        $builder = $this->db->table('pro_processing_task_get');
        $builder->where('isDelete', 0);
        switch ($myTaskType) {
            case 0:
                $builder->where(['doneStatus <' => 2,  'isGroupTrans' => 0, 'uId' => $uId]);
                $builder->where('upQty < getQty');
                break;
            case 1:
                $builder->where(['doneStatus' => 1,  'isGroupTrans' => 0, 'uId' => $uId]);
                break;
            case 2:
                $builder->where('doneStatus <', 2)->groupStart()->groupStart()->where('uId', $uId)->where('transInfo !=', '')->groupEnd()->orLike('transInfo', $this->db->escapeLikeString($uName))->groupEnd();
                break;
            default:
                $builder->where('doneStatus', -1);
                break;
        }
        if ($myTaskSortType == 0) {
            $builder->orderBy('createTime', 'DESC');
        } else {
            $builder->orderBy('lastDate', 'ASC');
        }
        $list = (clone $builder)->limit($pageSize)->offset($pageSize * ($current - 1))->get()->getResultArray();
        $total = $builder->countAllResults();
        $v = [];
        foreach ($list as $arr => $row) {
            $v[$arr]['id'] = intval($row['id']);
            $v[$arr]['iid'] = intval($row['iid']);
            $v[$arr]['lastDate'] = $row['lastDate'];
            $v[$arr]['projectId'] = intval($row['projectId']);
            $v[$arr]['projectType'] = intval($row['projectType']);
            $v[$arr]['projectName'] = $row['projectName'];
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
            $v[$arr]['upList'] = $this->db->table('pro_processing_task_up')->select('id, upQty, doneStatus, createTime')->where(['iiid' => $row['id'], 'isDelete' => 0])->orderBy('id', 'ASC')->get()->getResultArray();
            $v[$arr]['transInfo'] = $row['transInfo'] ? (array)json_decode($row['transInfo'], true) : [];
            $v[$arr]['transHis'] = $row['transHis'];
            if (count($v[$arr]['transInfo']) > 0) {
                $hasTransQty = 0;
                foreach ($v[$arr]['transInfo'] as $k1 => $v1) {
                    $hasTransQty += intval($v1['qty']);
                }
                $v[$arr]['hasTransQty'] = $hasTransQty;
            } else {
                $v[$arr]['hasTransQty'] = 0;
            }
        }
        $json['status'] = 200;
        $json['msg'] = 'success';
        $json['data']['current'] = $current;
        $json['data']['total'] = $total;
        $json['data']['pages'] = ceil($total / $pageSize);
        $json['data']['list'] = $v;
        $json['data']['myTaskTotal'] = $this->db->table('pro_processing_task_get')->where(['uId' => $uId, 'doneStatus <' => 2, 'isGroupTrans' => 0, 'isDelete' => 0])->where('upQty < getQty')->countAllResults();
        $json['data']['myTaskUnCheckTotal'] = $this->db->table('pro_processing_task_get')->where(['uId' => $uId, 'doneStatus' => 1, 'isGroupTrans' => 0, 'isDelete' => 0])->countAllResults();
        $json['data']['myTaskTransTotal'] = $this->db->table('pro_processing_task_get')->groupStart()->groupStart()->where('uId', $uId)->where('transInfo !=', '')->groupEnd()->orLike('transInfo ', $this->db->escapeLikeString($uName))->groupEnd()->where(['doneStatus <' => 2, 'isDelete' => 0])->countAllResults();
        $json['data']['myTaskTimeoutTotal'] = $this->db->table('pro_processing_task_get')->where(['uId' => $uId, 'lastDate <' => date('Y-m-d H:i:s'), 'isGroupTrans' => 0, 'isDelete' => 0])->where('upQty < getQty')->countAllResults();
        return $this->response->setJSON($json);
    }
    // 我的加工任务 获取同任务领取
    public function proProcessingTaskGetSame()
    {
        $id = (int)$this->request->getGetPost('id');
        if ($id > 0) {
            $getinfo = $this->db->table('pro_processing_task_get')->where('id', $id)->get()->getRowArray();
            if ($getinfo) {
                $myTaskUpSameGet = $this->db->table('pro_processing_task_get')->where(['iid' => $getinfo['iid'], 'isTest' => $getinfo['isTest'], 'isDelete' => 0])->where('upQty < getQty')->get()->getResultArray();
                foreach ($myTaskUpSameGet as $k => $v) {
                    $myTaskUpSameGet[$k]['getQty'] = intval($v['getQty']);
                    $myTaskUpSameGet[$k]['upQty'] = intval($v['upQty']);
                    $myTaskUpSameGet[$k]['curUpQty'] = intval($v['getQty']) - intval($v['upQty']);
                    $myTaskUpSameGet[$k]['proGroup'] = $this->db->table('pro_group')->select('name')->where('FIND_IN_SET(' . $this->db->escape($v['uName']) . ', members) >', 0, false)->where('isDelete', 0)->get()->getRowArray()['name'] ?? '';
                }
                return $this->response->setJSON(['status' => 200, 'msg' => 'success', 'data' => ['myTaskUpSameGet' => $myTaskUpSameGet]]);
            }
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '获取失败']);
    }
    // 我的加工任务 交付 上传图片
    public function myProProcessingTaskUploadImg()
    {
        set_time_limit(0);
        session_write_close();
        if (empty($_FILES)){
            return $this->response->setJSON(['status' => -1, 'msg' => '没有上传文件！']);
        };
        $uppath = 'data/upfile/proProcessingTask/';
        $temp_file = $_FILES['file']['tmp_name'];
        $target_file = date("YmdHis") . rand(1000, 9000) . '.' . strrev(explode('.', strrev($_FILES['file']['name']))[0]);
        if (!is_dir($uppath)) {
            @umask(0);
            $ret = @mkdir($uppath, 0777);
            if ($ret === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '文件夹创建失败']);
            }
        }
        move_uploaded_file($temp_file, $uppath . $target_file);
        return $this->response->setJSON(['status' => 200, 'msg' => 'success', 'data' => $target_file]);
    }
    // 我的加工任务 交付
    public function myProProcessingTaskUp()
    {
        $myTaskUpData = $this->request->getGetPost('myTaskUpData');
        if (strlen($myTaskUpData) > 0) {
            $myTaskUpData = (array)json_decode($myTaskUpData, true);
            $myTaskUpDescription = $this->request->getGetPost('myTaskUpDescription');
            $myTaskUpImgs = $this->request->getGetPost('myTaskUpImgs');
            foreach ($myTaskUpData as $k => $v) {
                $taskGetData = $this->db->table('pro_processing_task_get')->where(['id' => $v['id'], 'isDelete' => 0])->get()->getRowArray();
                if ((intval($taskGetData['upQty']) + intval($v['curUpQty'])) > intval($taskGetData['getQty'])) {
                    return $this->response->setJSON(['status' => -1, 'msg' => $v['uName'] . '交付超量']);
                }
                if (intval($v['curUpQty']) > 0 && intval($this->db->table('pro_processing_task_up')->selectSum('upQty', 'upQtyTotal')->where(['iiid' => $v['id'], 'upQty >' => 0, 'isDelete' => 0])->get()->getRowArray()['upQtyTotal'] ?? 0) >= intval($taskGetData['getQty'])) {
                    return $this->response->setJSON(['status' => -1, 'msg' => $v['uName'] . '交付超量']);
                }
            }
            $proName = [];
            // 没有超量交付，才操作数据
            $this->db->transStart();
            $taskGetData = [];
            foreach ($myTaskUpData as $k => $v) {
                if (intval($v['curUpQty']) > 0) {
                    $taskGetData = $this->db->table('pro_processing_task_get')->where(['id' => $v['id'], 'isDelete' => 0])->get()->getRowArray();
                    $info = array_intersect_key($taskGetData, array_flip(['iid', 'lastDate', 'projectId', 'projectType', 'projectName', 'needTest', 'taskQty', 'getQty', 'isTest', 'uId', 'uName']));
                    $info['iiid'] = intval($v['id']);
                    $info['upQty'] = intval($v['curUpQty']);
                    $info['upDescription'] = $myTaskUpDescription;
                    $info['upImgs'] = $myTaskUpImgs;
                    $info['doneStatus'] = 1;
                    $info['createTime'] = date('Y-m-d H:i:s');
                    $this->db->table('pro_processing_task_up')->insert($info);
                    $this->db->table('pro_processing_task_get')->where('id', $info['iiid'])->set('doneStatus', 1)->set('upQty', 'upQty + ' . $info['upQty'], false)->update();
                    $this->db->table('log')->insert(['log' => '交付了加工任务，任务编号：' . $info['iid'] . '，加工项目：' . $info['projectName'] . ($taskGetData['isTest'] == 1 ? '，工序：测试工序' : '，工序：加工工序') . '，交付产量：' . $v['curUpQty'], 'name' => $info['uName'], 'createTime' => date('Y-m-d H:i:s')]);
                    if ($taskGetData['isTest'] ==  0) {
                        array_push($proName, $info['uName']);
                    }
                }
            }
            if (count($proName) > 0) {
                $this->db->table('pro_processing_project')->where('id', $taskGetData['projectId'])->update(['proName' => implode(',', $proName)]);
            }
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '交付成功']);
        } else {
            return $this->response->setJSON(['status' => -1, 'msg' => '提交的是空数据']);
        }
    }
    // 我的加工任务 撤回交付
    public function myProProcessingTaskUpBack()
    {
        $id = (int)$this->request->getGetPost('id');
        $taskUpData = $this->db->table('pro_processing_task_up')->where(['id' => $id, 'doneStatus' => 1, 'isDelete' => 0])->get()->getRowArray();
        if ($taskUpData) {
            $this->db->transStart();
            $this->db->table('pro_processing_task_up')->where('id', $id)->update(['isDelete' => 1]);
            $upQtyTotal = (int)$this->db->table('pro_processing_task_up')->selectSum('upQty', 'upQtyTotal')->where(['iiid' => $taskUpData['iiid'], 'isTest' => $taskUpData['isTest'], 'isDelete' => 0])->get()->getRowArray()['upQtyTotal'] ?? 0;
            $unCheckUpNum = $this->db->table('pro_processing_task_up')->where(['iiid' => $taskUpData['iiid'], 'isTest' => $taskUpData['isTest'], 'doneStatus' => 1, 'isDelete' => 0])->countAllResults();
            $this->db->table('pro_processing_task_get')->where('id', $taskUpData['iiid'])->update(['upQty' => $upQtyTotal, 'doneStatus' => $unCheckUpNum == 0 ? 0 : 1]);
            $this->db->table('log')->insert(['log' => '撤回了尚未入库的加工交付，任务编号：' . $taskUpData['iid'] . '，加工项目：' . $taskUpData['projectName'] . ($taskUpData['isTest'] == 1 ? '，工序：测试工序' : '，工序：加工工序') . '，交付产量：' . $taskUpData['upQty'], 'name' => $taskUpData['uName'], 'createTime' => date('Y-m-d H:i:s')]);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '撤回成功']);
        } else {
            return $this->response->setJSON(['status' => -1, 'msg' => '已入库不可撤回']);
        }
    }
    // 我的加工任务 遇阻反馈
    public function myProProcessingTaskFeedback()
    {
        $id = (int)$this->request->getGetPost('id');
        $backInfo = $this->request->getGetPost('backInfo');
        $backName = $this->request->getGetPost('backName');
        $taskGetData = $this->db->table('pro_processing_task_get')->where(['id' => $id, 'isDelete' => 0])->get()->getRowArray();
        if ($taskGetData && $backInfo && $backName) {
            $this->db->transStart();
            $this->db->table('pro_processing_task_get')->where('id', $id)->update(['backInfo' => $backInfo]);
            $this->db->table('pro_processing_task')->where('id', $taskGetData['iid'])->update(['backInfo' => $backInfo]);
            $this->db->table('log')->insert(['log' => '提交了加工任务遇阻反馈，任务编号：' . $taskGetData['iid'] . '，加工项目：' . $taskGetData['projectName'] . '，遇阻信息：' . $backInfo, 'name' => $backName, 'createTime' => date('Y-m-d H:i:s')]);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '反馈成功']);
        } else {
            return $this->response->setJSON(['status' => -1, 'msg' => '提交的信息错误']);
        }
    }
    // 我的加工任务 转让任务 获取可转让对像用户信息
    public function myProProcessingTaskTransTargetData()
    {
        $taskId = (int)$this->request->getGetPost('taskId');
        $uName = $this->request->getGetPost('uName');
        $myTaskGetData = $this->db->table('pro_processing_task_get')->where(['id' => $taskId, 'isDelete' => 0])->get()->getRowArray();
        if ($myTaskGetData) {
            $myTaskTransRemainQty = 0;
            $myTaskTransTargetData = [];
            $others = $this->db->table('user')->select('name')->where(['position' => '加工专员', 'name !=' => $uName, 'isDisable' => 0, 'isDelete' => 0])->get()->getResultArray();
            if (count($others) > 0) {
                $myTaskTransTargetData = array_column($others, 'name');
            }
            // 目前只做单人转让
            $myTaskTransRemainQty += (intval($myTaskGetData['getQty']) - intval($myTaskGetData['upQty']));
            if ($myTaskGetData['transInfo']) {
                $transInfo = (array)json_decode($myTaskGetData['transInfo'], true);
                if (count($transInfo) > 0) {
                    $hasTransQty = 0;
                    foreach ($transInfo as $k => $v) {
                        $hasTransQty += intval($v['qty']);
                    }
                    $myTaskTransRemainQty -= $hasTransQty;
                }
            }
            if ($myTaskTransRemainQty >= 0 && count($myTaskTransTargetData) > 0) {
                return $this->response->setJSON(['status' => 200, 'msg' => 'success', 'data' => ['myTaskTransRemainQty' => $myTaskTransRemainQty, 'myTaskTransTargetData' => $myTaskTransTargetData]]);
            } else {
                return $this->response->setJSON(['status' => -1, 'msg' => '获取数据失败']);
            }
        } else {
            return $this->response->setJSON(['status' => -1, 'msg' => '任务已不存在']);
        }
    }
    // 我的加工任务 转让任务
    public function myProProcessingTaskTrans()
    {
        $id = (int)$this->request->getGetPost('id');
        $qty = (int)$this->request->getGetPost('qty');
        $target = $this->request->getGetPost('target');
        if ($id > 0 && $qty > 0 && $target) {
            $myTaskGetData = $this->db->table('pro_processing_task_get')->where(['id' => $id, 'isDelete' => 0])->get()->getRowArray();
            if ($myTaskGetData) {
                // 目前只做单人转让
                if (strpos($target, ',') !== false) {
                    return $this->response->setJSON(['status' => -1, 'msg' => '目标需是单人']);
                }
                if ($myTaskGetData['transInfo']) {
                    $transInfo = (array)json_decode($myTaskGetData['transInfo'], true);
                    $hasTransQty = 0;
                    foreach ($transInfo as $k => $v) {
                        $hasTransQty += intval($v['qty']);
                    }
                    if ((intval($myTaskGetData['getQty']) - intval($myTaskGetData['upQty']) - $hasTransQty) < $qty) {
                        return $this->response->setJSON(['status' => -1, 'msg' => '剩余产量不足']);
                    } elseif (isset($transInfo[$target])) {
                        return $this->response->setJSON(['status' => -1, 'msg' => '任务已转让过Ta']);
                    } else {
                        $transInfo[$target] = [
                            'qty' => $qty,
                            'time' => date('Y-m-d H:i:s')
                        ];
                    }
                } else {
                    $transInfo[$target] = [
                        'qty' => $qty,
                        'time' => date('Y-m-d H:i:s')
                    ];
                }
                $this->db->transStart();
                $this->db->table('pro_processing_task_get')->where('id', $id)->update(['transInfo' => json_encode($transInfo, 320)]);
                $this->db->table('log')->insert(['log' => '单人转让了加工任务，任务编号：' . $myTaskGetData['iid'] . '，加工项目：' . $myTaskGetData['projectName'] . '，转让对像：' . $target . '，转让产量：' . $qty, 'name' => $myTaskGetData['uName'], 'createTime' => date('Y-m-d H:i:s')]);
                $this->db->transComplete();
                if ($this->db->transStatus() === false) {
                    return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚']);
                }
                return $this->response->setJSON(['status' => 200, 'msg' => '转让成功']);
            } else {
                return $this->response->setJSON(['status' => -1, 'msg' => '任务已不存在']);
            }
        } else {
            return $this->response->setJSON(['status' => -1, 'msg' => '提交的数据不全']);
        }
    }
    // 我的加工任务 转让任务 撤回
    public function myProProcessingTaskTransBack()
    {
        $id = (int)$this->request->getGetPost('id');
        $target = $this->request->getGetPost('target');
        if ($id > 0 && $target) {
            $myTaskGetData = $this->db->table('pro_processing_task_get')->where(['id' => $id, 'isDelete' => 0])->get()->getRowArray();
            if ($myTaskGetData) {
                $transInfo = $myTaskGetData['transInfo'] ? (array)json_decode($myTaskGetData['transInfo'], true) : [];
                if (is_array($transInfo) && count($transInfo) > 0 && isset($transInfo[$target])) {
                    $targetInfo = $transInfo[$target];
                    unset($transInfo[$target]);
                    if (count($transInfo) > 0) {
                        $newTransInfo = json_encode($transInfo, 320);
                    } else {
                        $newTransInfo = '';
                    }
                    $this->db->transStart();
                    $this->db->table('pro_processing_task_get')->where('id', $id)->update(['transInfo' => $newTransInfo]);
                    $this->db->table('log')->insert(['log' => '撤回了加工任务转让，任务编号：' . $myTaskGetData['iid'] . '，加工项目：' . $myTaskGetData['projectName'] . '，转让对像：' . $target . '，转让产量：' . $targetInfo['qty'], 'name' => $myTaskGetData['uName'], 'createTime' => date('Y-m-d H:i:s')]);
                    $this->db->transComplete();
                    if ($this->db->transStatus() === false) {
                        return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚']);
                    }
                    return $this->response->setJSON(['status' => 200, 'msg' => '撤回成功']);
                } else {
                    return $this->response->setJSON(['status' => -1, 'msg' => '已被接受或拒绝']);
                }
            } else {
                return $this->response->setJSON(['status' => -1, 'msg' => '任务不存在']);
            }
        } else {
            return $this->response->setJSON(['status' => -1, 'msg' => '提交的数据不全']);
        }
    }
    // 我的加工任务 转让任务 接受
    public function myProProcessingTaskTransAgree()
    {
        $id = (int)$this->request->getGetPost('id');
        $target = $this->request->getGetPost('target');
        $uName = $this->request->getGetPost('uName');
        if ($id > 0 && $target) {
            $taskGetData = $this->db->table('pro_processing_task_get')->where(['id' => $id, 'isDelete' => 0])->get()->getRowArray();
            if ($taskGetData) {
                $transInfo = $taskGetData['transInfo'] ? (array)json_decode($taskGetData['transInfo'], true) : [];
                if (is_array($transInfo) && count($transInfo) > 0 && isset($transInfo[$target])) {
                    $targetInfo = $transInfo[$target];
                    // 单人转让
                    if (($taskGetData['getQty'] - $taskGetData['upQty']) < $targetInfo['qty']) {
                        return $this->response->setJSON(['status' => -1, 'msg' => '剩余产量不足']);
                    }
                    unset($transInfo[$target]);
                    if (count($transInfo) > 0) {
                        $newTransInfo = json_encode($transInfo, 320);
                    } else {
                        $newTransInfo = '';
                    }
                    $createTime = date('Y-m-d H:i:s');
                    $newTransHis = $taskGetData['transHis'] . $createTime . ' ' . $target . '接受' . $taskGetData['uName'] . '单人转让的' . $targetInfo['qty'] . '产量' . "\r\n";
                    $user = $this->db->table('user')->where(['name' => $target])->get()->getRowArray();
                    $data = [
                        'iid' => $taskGetData['iid'],
                        'lastDate' => $taskGetData['lastDate'],
                        'projectId' => $taskGetData['projectId'],
                        'projectType' => $taskGetData['projectType'],
                        'projectName' => $taskGetData['projectName'],
                        'needTest' => $taskGetData['needTest'],
                        'description' => $taskGetData['description'],
                        'taskQty' => $taskGetData['taskQty'],
                        'getQty' => $targetInfo['qty'],
                        'isTest' => $taskGetData['isTest'],
                        'uId' => $user['id'],
                        'uName' => $user['name'],
                        'createTime' => $createTime,
                        'transHis' => $targetInfo['time'] . ' 由' . $taskGetData['uName'] . '单人转让产量' . $targetInfo['qty'] . "\r\n"
                    ];
                    $this->db->transStart();
                    $this->db->table('pro_processing_task_get')->insert($data);
                    if ($taskGetData['getQty'] - $targetInfo['qty'] <= 0) {
                        $this->db->table('pro_processing_task_get')->where('id', $id)->update(['isDelete' => 1]);
                    } else {
                        $this->db->table('pro_processing_task_get')->where('id', $id)->update(['getQty' => $taskGetData['getQty'] - $targetInfo['qty'], 'transInfo' => $newTransInfo, 'transHis' => $newTransHis]);
                    }
                    $this->db->table('log')->insert(['log' => '接受' . $taskGetData['uName'] . '单人转让的加工任务，任务编号：' . $taskGetData['iid'] . '，加工项目：' . $taskGetData['projectName'] . '，转让产量：' . $targetInfo['qty'], 'name' => $uName ?? $target, 'createTime' => date('Y-m-d H:i:s')]);
                    $this->db->transComplete();
                    if ($this->db->transStatus() === false) {
                        return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚']);
                    }
                    return $this->response->setJSON(['status' => 200, 'msg' => '接收成功']);
                } else {
                    return $this->response->setJSON(['status' => -1, 'msg' => '转让已被撤回']);
                }
            } else {
                return $this->response->setJSON(['status' => -1, 'msg' => '任务不存在']);
            }
        } else {
            return $this->response->setJSON(['status' => -1, 'msg' => '提交的数据不全']);
        }
    }
    // 我的加工任务 转让任务 拒绝
    public function myProProcessingTaskTransDisagree()
    {
        $id = (int)$this->request->getGetPost('id');
        $target = $this->request->getGetPost('target');
        $uName = $this->request->getGetPost('uName');
        if ($id > 0 && $target) {
            $taskGetData = $this->db->table('pro_processing_task_get')->where(['id' => $id, 'isDelete' => 0])->get()->getRowArray();
            if ($taskGetData) {
                $transInfo = $taskGetData['transInfo'] ? (array)json_decode($taskGetData['transInfo'], true) : [];
                if (is_array($transInfo) && count($transInfo) > 0 && isset($transInfo[$target])) {
                    $targetInfo = $transInfo[$target];
                    unset($transInfo[$target]);
                    if (count($transInfo) > 0) {
                        $newTransInfo = json_encode($transInfo, 320);
                    } else {
                        $newTransInfo = '';
                    }
                    $createTime = date('Y-m-d H:i:s');
                    $newTransHis = $taskGetData['transHis'] . $createTime . ' ' . ($uName ?? $target) . '拒绝' . $taskGetData['uName'] . '单人转让的' . $targetInfo['qty'] . '产量' . "\r\n";
                    $this->db->transStart();
                    $this->db->table('pro_processing_task_get')->where('id', $id)->update(['transInfo' => $newTransInfo, 'transHis' => $newTransHis]);
                    $this->db->table('log')->insert(['log' => '拒绝' . $taskGetData['uName'] . '单人转让的加工任务，任务编号：' . $taskGetData['iid'] . '，加工项目：' . $taskGetData['projectName'] . '，转让产量：' . $targetInfo['qty'], 'name' => $uName ?? $target, 'createTime' => date('Y-m-d H:i:s')]);
                    $this->db->transComplete();
                    if ($this->db->transStatus() === false) {
                        return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚']);
                    }
                    return $this->response->setJSON(['status' => 200, 'msg' => '拒绝成功']);
                } else {
                    return $this->response->setJSON(['status' => -1, 'msg' => '转让已被撤回']);
                }
            } else {
                return $this->response->setJSON(['status' => -1, 'msg' => '任务不存在']);
            }
        } else {
            return $this->response->setJSON(['status' => -1, 'msg' => '提交的数据不全']);
        }
    }


    // 加工交付 列表
    public function proProcessingTaskUpList()
    {
        $current = max((int)$this->request->getGetPost('current'), 1);
        $pageSize = max((int)$this->request->getGetPost('pageSize'), 10);
        $doneStatus = (int)($this->request->getGetPost('doneStatus') ?? 0);
        $skey = $this->request->getGetPost('skey');
        $builder = $this->db->table('pro_processing_task_up');
        $builder->where('isDelete', 0);
        if ($doneStatus > 0) {
            $builder->where('doneStatus', $doneStatus);
        }
        if ($skey !== '') {
            $keywords = array_values(array_filter(explode(',', $skey), 'strlen'));
            foreach ($keywords as $kw) {
                $builder->groupStart()
                    ->like('projectName', $this->db->escapeLikeString($kw))
                    ->orLike('upDescription', $this->db->escapeLikeString($kw))
                    ->orLike('uName', $this->db->escapeLikeString($kw))
                ->groupEnd();
            }
        }
        $list = (clone $builder)->orderBy('id', 'DESC')->limit($pageSize)->offset($pageSize * ($current - 1))->get()->getResultArray();
        $total = $builder->countAllResults();
        $v = [];
        foreach ($list as $arr => $row) {
            $v[$arr]['id'] = intval($row['id']);
            $v[$arr]['iid'] = intval($row['iid']);
            $v[$arr]['projectId'] = intval($row['projectId']);
            $v[$arr]['projectType'] = intval($row['projectType']);
            $v[$arr]['projectName'] = $row['projectName'];
            $v[$arr]['isTest'] = intval($row['isTest']);
            $v[$arr]['upQty'] = intval($row['upQty']);
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
        $json['data']['current'] = $current;
        $json['data']['total'] = $total;
        $json['data']['pages'] = ceil($total / $pageSize);
        $json['data']['list'] = $v;
        return $this->response->setJSON($json);
    }
    // 加工交付 获取同任务交付
    public function proProcessingTaskUpSame()
    {
        $id = (int)$this->request->getGetPost('id');
        if ($id > 0) {
            $upinfo = $this->db->table('pro_processing_task_up')->where(['id' => $id])->get()->getRowArray();
            if ($upinfo) {
                $sameUp = $this->db->table('pro_processing_task_up')->where(['iid' => $upinfo['iid'], 'isTest' => $upinfo['isTest'], 'doneStatus' => 1, 'isDelete' => 0])->get()->getResultArray();
                foreach ($sameUp as $k => $v) {
                    $sameUp[$k]['getQty'] = intval($v['getQty']);
                    $sameUp[$k]['upQty'] = intval($v['upQty']);
                    $sameUp[$k]['proGroup'] = $this->db->table('pro_group')->select('name')->where('FIND_IN_SET(' . $this->db->escape($v['uName']) . ', members) >', 0, false)->where('isDelete', 0)->get()->getRowArray()['name'] ?? '';
                }
                return $this->response->setJSON(['status' => 200, 'msg' => 'sucess', 'data' => ['sameUp' => $sameUp]]);
            }
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '获取失败']);
    }
    // 加工交付 点货入库与取消入库
    public function proProcessingTaskUpCheck()
    {
        $curid = (int)$this->request->getGetPost('curid');
        $selectedids = $this->request->getGetPost('selectedids');
        $selectedids = strlen($selectedids) > 0 ? explode(',', $selectedids) : [];
        $status = (int)$this->request->getGetPost('status');
        $qty = (float)$this->request->getGetPost('qty');
        $weight = (float)$this->request->getGetPost('weight');
        $checkId = (int)$this->request->getGetPost('checkId');
        $checkName = $this->request->getGetPost('checkName');
        $checkTime = $checkId > 0 ? date('Y-m-d H:i:s') : '0000-00-00 00:00:00';
        $user = $this->db->table('user')->where('id', $checkId)->get()->getRowArray();
        if ($user) {
            // 取出业绩系数用于后面运算
            $optionValue = $this->db->table('option')->select('optionValue')->where('optionName', 'exp_par')->get()->getRowArray()['optionValue'] ?? '';
            $expPar = $optionValue ? (array)json_decode($optionValue, true) : [];
            if (empty($expPar)) {
                return $this->response->setJSON(['status' => -1, 'msg' => '业绩系数获取失败！']);
            }
            $rights = explode(',', $user['lever']);
            if (in_array(707, $rights) || $user['roleId'] == 1) { // 有点货入库权限或管理员角色
                if ($curid > 0) {
                    if (count($selectedids) == 0) {
                        $selectedids = [$curid];
                    }
                    $this->db->transStart();
                    $upData = $getData = $data = $project = [];
                    foreach ($selectedids as $k => $id) {
                        $upData = $this->db->table('pro_processing_task_up')->where('id', $id)->get()->getRowArray();
                        $getData = $this->db->table('pro_processing_task_get')->where('id', $upData['iiid'])->get()->getRowArray();
                        $data = $this->db->table('pro_processing_task')->where('id', $upData['iid'])->get()->getRowArray();
                        $project = $this->db->table('pro_processing_project')->where('id', $upData['projectId'])->get()->getRowArray();
                        // 更新交付状态、业绩提成
                        if ($status == 2) {
                            if (in_array($project['type'], [0, 1])) {
                                if ($upData['isTest'] == 0) {
                                    $price = round((float)$project['producePrice'] * (float)$expPar['proProcessing']['par3'] / 100, 5);
                                    $amount = round($price * (float)$upData['upQty'], 2);
                                } else {
                                    $price = round((float)$project['producePrice'] * (float)$expPar['proProcessing']['par4'] / 100, 5);
                                    $amount = round($price * (float)$upData['upQty'], 2);
                                }
                            } else {
                                $price = (float)$project['producePrice'];
                                $amount = round($price * (float)$upData['upQty'], 2);
                            }
                            $this->db->table('pro_processing_task_up')->where('id', $id)->update(['price' => $price, 'amount' => $amount, 'weight' => round($weight / $qty * (float)$upData['upQty'], 5), 'doneStatus' => $status, 'checkId ' => $checkId, 'checkName' => $checkName, 'checkTime' => $checkTime]);
                        } else {
                            $this->db->table('pro_processing_task_up')->where('id', $id)->update(['price' => 0, 'amount' => 0, 'weight' => 0, 'doneStatus' => $status, 'checkId ' => 0, 'checkName' => '', 'checkTime' => '0000-00-00 00:00:00']);
                        }
                        // 更新已领任务状态
                        $uncheckCount = $this->db->table('pro_processing_task_up')->where(['iiid' => $upData['iiid'], 'doneStatus' => 1, 'isDelete' => 0])->countAllResults();
                        if ($uncheckCount > 0) {
                            $this->db->table('pro_processing_task_get')->where('id', $upData['iiid'])->update(['doneStatus' => 1]);
                        } else {
                            if ($getData['upQty'] >= $getData['getQty']) {
                                $this->db->table('pro_processing_task_get')->where('id', $upData['iiid'])->update(['doneStatus' => 2]);
                            } else {
                                $this->db->table('pro_processing_task_get')->where('id', $upData['iiid'])->update(['doneStatus' => 0]);
                            }
                        }
                        // 处理库存 与 库存流水
                        $this->db->table('goods')->where('id', $project['goodsId'])->set('currentQty', 'currentQty' . ($status == 2 ? ' + ' : ' - ') . $upData['upQty'], false)->set('billDate', date('Y-m-d'))->update();
                        $goods = $this->db->table('goods')->where('id', $project['goodsId'])->get()->getRowArray();
                        $invHisData['goodsId'] = $project['goodsId'];
                        $invHisData['billId'] = $upData['iid'];
                        $invHisData['billNo'] = '加工任务编号：' . $upData['iid'];
                        $invHisData['billType'] = 'PP';
                        $invHisData['transType'] = 101;
                        $invHisData['changeType'] = '加工任务交付' . ($status == 2 ? '点货入库' : '取消入库');
                        $invHisData['changeTime'] = date('Y-m-d H:i:s');
                        $invHisData['changeCurrentQty'] = ($status == 2 ? '+' : '-') . abs((float)$upData['upQty']);
                        $invHisData['currentQty'] = (float)$goods['currentQty'];
                        $this->db->table('inv_history')->insert($invHisData);
                        if ($goods['hasSon'] > 0 && strlen($goods['sonGoods']) > 0) {
                            $sonGoods = (array)json_decode($goods['sonGoods'], true);
                            foreach ($sonGoods as $arr4 => $row4) {
                                $this->db->table('goods')->where('id', $row4['gid'])->set('currentQty', 'currentQty' . ($status == 2 ? ' - ' : ' + ') . ($row4['qty1'] * $row4['qty2'] * $upData['upQty']), false)->set('billDate', date('Y-m-d'))->update();
                                $newSonGoods = $this->db->table('goods')->select('currentQty')->where('id', $row4['gid'])->get()->getRowArray();
                                $invHisData['goodsId'] = $row4['gid'];
                                $invHisData['billId'] = $upData['iid'];
                                $invHisData['billNo'] = '加工任务编号：' . $upData['iid'];
                                $invHisData['billType'] = 'PP';
                                $invHisData['transType'] = 102;
                                $invHisData['changeType'] = '加工任务交付' . ($status == 2 ? '点货入库' : '取消入库');
                                $invHisData['changeTime'] = date('Y-m-d H:i:s');
                                $invHisData['changeCurrentQty'] = ($status == 2 ? '-' : '+') . abs((float)$row4['qty1'] * (float)$row4['qty2'] * (float)$upData['upQty']);
                                $invHisData['currentQty'] = (float)$newSonGoods['currentQty'];
                                $this->db->table('inv_history')->insert($invHisData);
                            }
                        }
                        $this->db->table('log')->insert(['log' => ($status == 2 ? '点货入库了 ' : '取消入库了 ') . $upData['uName'] . ' 的加工交付。任务编号：' . $upData['iid'] . '，加工项目：' . $upData['projectName'] . (in_array($project['type'], [0, 1]) ? ($upData['isTest'] == 1 ? '，交付工序：测试工序' : '，交付工序：加工工序') : '') . '，交付产量：' . $upData['upQty'], 'name' => $checkName, 'createTime' => date('Y-m-d H:i:s')]);
                    }
                    // 更新主任务状态
                    $sumDoneProduceQty = $this->db->table('pro_processing_task_up')->selectSum('upQty', 'sumDoneProduceQty')->where(['iid' => $upData['iid'], 'isTest' => 0, 'doneStatus' => 2, 'isDelete' => 0])->get()->getRowArray()['sumDoneProduceQty'] ?? 0;
                    $sumDoneTestQty = $this->db->table('pro_processing_task_up')->selectSum('upQty', 'sumDoneTestQty')->where(['iid' => $upData['iid'], 'isTest' => 1, 'doneStatus' => 2, 'isDelete' => 0])->get()->getRowArray()['sumDoneTestQty'] ?? 0;
                    if (($sumDoneProduceQty > 0 && $sumDoneProduceQty < $data['qty']) || ($sumDoneTestQty > 0 && $sumDoneTestQty < $data['qty'])) {
                        $taskStatus = 2;
                    } elseif ($sumDoneProduceQty >= $data['qty'] && $sumDoneTestQty >= $data['qty']) {
                        $taskStatus = 3;
                    } else {
                        $taskStatus = 1;
                    }
                    $this->db->table('pro_processing_task')->where('id', $data['id'])->update(['taskStatus ' => $taskStatus]);
                    // 更新产品重量
                    if ($weight > 0) {
                        $this->db->table('goods')->where('id', $project['goodsId'])->update(['weight' => round($weight / $qty, 5)]);
                    }
                    $this->db->transComplete();
                    if ($this->db->transStatus() === false) {
                        return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚']);
                    }
                    return $this->response->setJSON(['status' => 200, 'msg' => '操作成功', 'data' => ['doneStatus' => $status]]);
                } else {
                    return $this->response->setJSON(['status' => -1, 'msg' => '提交的是空数据']);
                }
            } else {
                return $this->response->setJSON(['status' => -1, 'msg' => '无此权限']);
            }
        } else {
            return $this->response->setJSON(['status' => -1, 'msg' => '无此用户']);
        }
    }


    // 责任损失 列表
    public function getServiceTarget()
    {
        $current = max((int)$this->request->getGetPost('current'), 1);
        $pageSize = max((int)$this->request->getGetPost('pageSize'), 10);
        $skey = $this->request->getGetPost('skey');
        $uId = (int)$this->request->getGetPost('uId');
        $uName = $this->request->getGetPost('uName');
        $builder = $this->db->table('service');
        $builder->where('isDelete', 0);
        if ($uId > 0 && $uName) {
            $builder->where(['disStatus <' => 3, 'targetAmount >' =>  0, 'isDisable' => 0, 'isDelete' => 0]);
            $builder->where('FIND_IN_SET(' . $this->db->escape($uName) . ', targetName) >', 0, false);
            if ($skey !== '') {
                $keywords = array_values(array_filter(explode(',', $skey), 'strlen'));
                foreach ($keywords as $kw) {
                    $builder->like('content', $this->db->escapeLikeString($kw));
                }
            }
            $list = (clone $builder)->orderBy('id', 'DESC')->limit($pageSize)->offset($pageSize * ($current - 1))->get()->getResultArray();
            $total = $builder->countAllResults();
            $v = [];
            foreach ($list as $arr => $row) {
                $v[$arr]['id'] = intval($row['id']);
                $v[$arr]['categoryId'] = intval($row['categoryId']);
                $v[$arr]['categoryName'] = $this->db->table('category')->select('name')->where('id', $v[$arr]['categoryId'])->get()->getRowArray()['name'] ?? '';
                $v[$arr]['orderId'] = intval($row['orderId']);
                $v[$arr]['orderNo'] = $row['orderNo'];
                $v[$arr]['deliverId'] = intval($row['deliverId']);
                $v[$arr]['deliverNo'] = $row['deliverNo'];
                $v[$arr]['ww'] = $row['ww'];
                $v[$arr]['tel'] = $row['tel'];
                $v[$arr]['qq'] = $row['qq'];
                $v[$arr]['customerinfo'] = (('' != $row['ww'] && NULL != $row['ww']) ? '旺旺：' . $row['ww'] : '') . (('' != $row['qq'] && NULL != $row['qq']) ? ' QQ：' . $row['qq'] : '') . (('' != $row['tel'] && NULL != $row['tel']) ? ' 电话：' . $row['tel'] : '');
                $content = explode('======', strip_tags(str_replace('</i></div>', '======', str_replace('<div class="info-username">', '------', str_replace('</div><div class="info-time">', '', str_replace('</div><i class="close iconfont icon-shanchu">', '', $row['content']))))));
                $v[$arr]['content'] = '';
                if (count($content) > 0) {
                    foreach ($content as $i => $text) {
                        if (strpos($text, '------') !== false) {
                            $text = explode('------', $text);
                            $text[0] && ($v[$arr]['content'] .= $text[0] . "\r\n");
                        }
                    }
                }
                $v[$arr]['targetName'] = $row['targetName'];
                $v[$arr]['targetAmount'] = (float)abs($row['targetAmount']);
                $v[$arr]['beginDate'] = $row['beginDate'];
                $v[$arr]['isDisable'] = intval($row['isDisable']);
                $v[$arr]['disStatus'] = intval($row['disStatus']);
                $v[$arr]['disReason'] = $row['disReason'];
            }
        } else {
            $total = 0;
            $v = [];
        }
        $json['status'] = 200;
        $json['msg'] = 'success';
        $json['data']['current'] = $current;
        $json['data']['total'] = $total;
        $json['data']['pages'] = ceil($total / $pageSize);
        $json['data']['list'] = $v;
        return $this->response->setJSON($json);
    }
    // 责任损失 确认或异议
    public function checkServiceTarget()
    {
        $disStatus = (int)$this->request->getGetPost('disStatus');
        $id = (int)$this->request->getGetPost('id');
        $disReason = $this->request->getGetPost('disReason');
        $uId = (int)$this->request->getGetPost('uId');
        $uName = $this->request->getGetPost('uName');
        if ($id > 0) {
            $data = $this->db->table('service')->where('id', $id)->get()->getRowArray();
            if ($data) {
                $this->db->transStart();
                if ($disStatus == 3) {
                    $this->db->table('service')->where('id', $id)->update(['disStatus' => $disStatus]);
                    $this->db->table('log')->insert(['log' => $uName . ' 确认了责任损失，损失金额：' . $data['targetAmount'] . '，客户之声ID：' . $id, 'name' => $uName, 'createTime' => date('Y-m-d H:i:s')]);
                } elseif ($disStatus == 1) {
                    if (strlen($disReason) < 1) {
                        return $this->response->setJSON(['status' => -1, 'msg' => '异议原因不能为空']);
                    }
                    $this->db->table('service')->where('id', $id)->update(['disStatus' => $disStatus, 'disReason' => $data['disReason'] . date('Y-m-d H:i:s') . ' ' . $uName . '提交异议：' . $disReason . "\r\n"]);
                    $this->db->table('log')->insert(['log' => $uName . ' 提出了责任损失异议，损失金额：' . $data['targetAmount'] . '，客户之声ID：' . $id . '，异议内容：' . $disReason, 'name' => $uName, 'createTime' => date('Y-m-d H:i:s')]);
                }
                $this->db->transComplete();
                if ($this->db->transStatus() === false) {
                    return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚']);
                }
                return $this->response->setJSON(['status' => 200, 'msg' => '操作成功']);
            } else {
                return $this->response->setJSON(['status' => -1, 'msg' => '责任损失不存在']);
            }
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '提交的是空数据']);
    }


    // 工作目标 列表
    public function getPlanList()
    {
        $current = max((int)$this->request->getGetPost('current'), 1);
        $pageSize = max((int)$this->request->getGetPost('pageSize'), 10);
        $isDisable = (int)$this->request->getGetPost('isDisable');
        $uId = (int)$this->request->getGetPost('uId');
        $builder = $this->db->table('plan');
        $builder->where(['uId' => $uId, 'isDelete' => 0]);
        if ($isDisable > -1) {
            $builder->where('isDisable', $isDisable);
        }
        $list = (clone $builder)->orderBy('id', 'DESC')->limit($pageSize)->offset($pageSize * ($current - 1))->get()->getResultArray();
        $total = $builder->countAllResults();
        $v = [];
        foreach ($list as $arr => $row) {
            $v[$arr]['id'] = intval($row['id']);
            $v[$arr]['type'] = intval($row['type']);
            $v[$arr]['date'] = $row['date'];
            $v[$arr]['target'] = $row['target'];
            $v[$arr]['summary'] = $row['summary'];
            $v[$arr]['uId'] = intval($row['uId']);
            $v[$arr]['uName'] = $row['uName'];
            $v[$arr]['createTime'] = $row['createTime'];
            $v[$arr]['summaryTime'] = $row['summaryTime'];
            $v[$arr]['isDisable'] = intval($row['isDisable']);
        }
        $json['status'] = 200;
        $json['msg'] = 'success';
        $json['data']['current'] = $current;
        $json['data']['total'] = $total;
        $json['data']['pages'] = ceil($total / $pageSize);
        $json['data']['list'] = $v;
        return $this->response->setJSON($json);
    }
    // 工作目标 状态标记
    public function planDisable()
    {
        $id = (int)$this->request->getGetPost('id');
        $isDisable = (int)$this->request->getGetPost('isDisable');
        $endName = $this->request->getGetPost('endName');
        if ($id > 0) {
            $data = $this->db->table('plan')->where('id', $id)->get()->getRowArray();
            if ($data) {
                $this->db->transStart();
                $this->db->table('plan')->where('id', $id)->update(['isDisable' => $isDisable]);
                $planTypeArr = ['日', '周', '月', '年'];
                $this->db->table('log')->insert(['log' => '将 ' . $data['date'] . ' 的' . $planTypeArr[$data['type']] . '工作目标（ID：' . $data['id'] . '）的完成状态标记为' . ($isDisable > 0 ? '已完成' : '未完成'), 'name' => $endName, 'createTime' => date('Y-m-d H:i:s')]);
                $this->db->transComplete();
                if ($this->db->transStatus() === false) {
                    return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚']);
                }
                return $this->response->setJSON(['status' => 200, 'msg' => '操作成功']);
            }
            return $this->response->setJSON(['status' => -1, 'msg' => '没有相关信息']);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '提交的是空数据']);
    }
    // 工作目标 新增、编辑
    public function planSubmit()
    {
        $data = $this->request->getPost();
        if (is_array($data) && count($data) > 0) {
            if (strlen($data['target']) < 1) {
                return $this->response->setJSON(['status' => -1, 'msg' => '目标规划不能为空']);
            }
            $data['date'] = date('Y-m-d');
            $data['createTime'] = date('Y-m-d H:i:s');
            if ($data['id'] < 1) {
                if ($data['type'] == 0 && $this->db->table('plan')->where(['date' => $data['date'], 'type' => 0, 'uId' => $data['uId'], 'isDelete' => 0])->countAllResults() > 2) {
                    return $this->response->setJSON(['status' => -1, 'msg' => '日目标每天最多3条']);
                }
                if ($data['type'] == 1 && $this->db->table('plan')->where(['date >=' => date('Y-m-01'), 'date <=' => date('Y-m-d'), 'type' => 1, 'uId' => $data['uId'], 'isDelete' => 0])->countAllResults() > 5) {
                    return $this->response->setJSON(['status' => -1, 'msg' => '周目标最多每月6条']);
                }
                if ($data['type'] == 2 && $this->db->table('plan')->where(['date >=' => date('Y-m-01'), 'date <=' => date('Y-m-d'), 'type' => 2, 'uId' => $data['uId'], 'isDelete' => 0])->countAllResults() > 0) {
                    return $this->response->setJSON(['status' => -1, 'msg' => '月目标最多每月1条']);
                }
                if ($data['type'] == 3 && $this->db->table('plan')->where(['date >=' => date('Y-01-01'), 'date <=' => date('Y-12-31'), 'type' => 3, 'uId' => $data['uId'], 'isDelete' => 0])->countAllResults() > 0) {
                    return $this->response->setJSON(['status' => -1, 'msg' => '年目标最多每年1条']);
                }
                $info = array_intersect_key($data, array_flip(['type', 'date', 'target', 'summary', 'uId', 'uName', 'createTime']));
                $this->db->transStart();
                $this->db->table('plan')->insert($info);
                $data['id'] =  $this->db->insertID();
                $data['isDisable'] = 0;
                $planTypeArr = ['日', '周', '月', '年'];
                $this->db->table('log')->insert(['log' => '新增了 ' . $data['date'] . ' 的' . $planTypeArr[$data['type']] . '工作目标（ID：' . $data['id'] . '）', 'name' => $data['uName'], 'createTime' => date('Y-m-d H:i:s')]);
                $this->db->transComplete();
                if ($this->db->transStatus() === false) {
                    return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚']);
                }
                return $this->response->setJSON(['status' => 200, 'msg' => '新增成功', 'data' => $data]);
            } else {
                $oldData = $this->db->table('plan')->where(['id' => $data['id']])->get()->getRowArray();
                $nowTime = time();
                if ($data['type'] != $oldData['type'] && ($nowTime - strtotime($oldData['createTime'])) > 3600) {
                    return $this->response->setJSON(['status' => -1, 'msg' => '无法再修改类别']);
                }
                if ($data['type'] == 0) {
                    if ($data['target'] != $oldData['target'] && ($nowTime - strtotime($oldData['createTime'])) > 7200) {
                        return $this->response->setJSON(['status' => -1, 'msg' => '日目标在发布2小时后禁止编辑目标规划']);
                    } elseif ($data['summary'] != $oldData['summary'] && $oldData['summaryTime'] != '0000-00-00 00:00:00' && ($nowTime - strtotime($oldData['summaryTime'])) > 7200) {
                        return $this->response->setJSON(['status' => -1, 'msg' => '日目标在总结提交2小时后禁止再编辑工作总结']);
                    }
                } elseif ($data['type'] == 1) {
                    if ($data['target'] != $oldData['target'] && ($nowTime - strtotime($oldData['createTime'])) > 172800) {
                        return $this->response->setJSON(['status' => -1, 'msg' => '周目标在发布2天后禁止编辑目标规划']);
                    } elseif ($data['summary'] != $oldData['summary'] && $oldData['summaryTime'] != '0000-00-00 00:00:00' && ($nowTime - strtotime($oldData['summaryTime'])) > 172800) {
                        return $this->response->setJSON(['status' => -1, 'msg' => '周目标在总结提交2天后禁止再编辑工作总结']);
                    }
                } elseif ($data['type'] == 2) {
                    if ($data['target'] != $oldData['target'] && ($nowTime - strtotime($oldData['createTime'])) > 1728000) {
                        return $this->response->setJSON(['status' => -1, 'msg' => '月目标在发布20天后禁止编辑目标规划']);
                    } elseif ($data['summary'] != $oldData['summary'] && $oldData['summaryTime'] != '0000-00-00 00:00:00' && ($nowTime - strtotime($oldData['summaryTime'])) > 1728000) {
                        return $this->response->setJSON(['status' => -1, 'msg' => '月目标在总结提交20天后禁止再编辑工作总结']);
                    }
                } elseif ($data['type'] == 3) {
                    if ($data['target'] != $oldData['target'] && ($nowTime - strtotime($oldData['createTime'])) > 5184000) {
                        return $this->response->setJSON(['status' => -1, 'msg' => '年目标在发布60天后禁止编辑目标规划']);
                    } elseif ($data['summary'] != $oldData['summary'] && $oldData['summaryTime'] != '0000-00-00 00:00:00' && ($nowTime - strtotime($oldData['summaryTime'])) > 5184000) {
                        return $this->response->setJSON(['status' => -1, 'msg' => '年目标在总结提交60天后禁止再编辑工作总结']);
                    }
                }
                $info = array_intersect_key($data, array_flip(['target', 'summary']));
                //首次提交总结内容，记录首次提交时间
                if ($data['summary'] && $oldData['summaryTime'] == '0000-00-00 00:00:00') {
                    $info['summaryTime'] = date('Y-m-d H:i:s');
                }
                $this->db->transStart();
                $this->db->table('plan')->where('id', $data['id'])->update($info);
                $planTypeArr = ['日', '周', '月', '年'];
                $this->db->table('log')->insert(['log' => '编辑了 ' . $oldData['date'] . ' 的' . $planTypeArr[$data['type']] . '工作目标（ID：' . $data['id'] . '）', 'name' => $data['uName'], 'createTime' => date('Y-m-d H:i:s')]);
                $this->db->transComplete();
                if ($this->db->transStatus() === false) {
                    return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚']);
                }
                return $this->response->setJSON(['status' => 200, 'msg' => '编辑成功', 'data' => $data]);
            }
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '提交数据不完整']);
    }


    // 工作转交 转交对象数组 获取
    public function getTargetData()
    {
        $departments = array_column($this->db->table('category')->select('name')->where(['typeNumber' => 'departmenttype', 'isDelete' => 0])->get()->getResultArray(), 'name');
        $users = array_column($this->db->table('user')->select('name')->where(['isDisable' => 0, 'isDelete' => 0])->get()->getResultArray(), 'name');
        $list = array_merge($departments, $users);
        $json['status'] = 200;
        $json['msg'] = 'success';
        $json['data'] = $list;
        return $this->response->setJSON($json);
    }
    // 工作转交 列表
    public function getDutyList()
    {
        $current = max((int)$this->request->getGetPost('current'), 1);
        $pageSize = max((int)$this->request->getGetPost('pageSize'), 10);
        $isDisable = (int)$this->request->getGetPost('isDisable');
        $uId = (int)$this->request->getGetPost('uId') ?? 11;
        $user = $this->db->table('user')->where('id', $uId)->get()->getRowArray();
        $builder = $this->db->table('duty');
        $builder->groupStart()
            ->where('beginName', $user['name'])
            ->orWhere('FIND_IN_SET(' . $this->db->escape($user['name']) . ', targetName) >', 0, false)
            ->orWhere('FIND_IN_SET(' . $this->db->escape($user['department']) . ', targetName) >', 0, false)
        ->groupEnd()
        ->where('isDelete', 0);
        if ($isDisable > -1) {
            $builder->where('isDisable', $isDisable);
        }
        $list = (clone $builder)->orderBy('id', 'DESC')->limit($pageSize)->offset($pageSize * ($current - 1))->get()->getResultArray();
        $total = $builder->countAllResults();
        $v = [];
        foreach ($list as $arr => $row) {
            $v[$arr]['id'] = intval($row['id']);
            $v[$arr]['targetName'] = $row['targetName'];
            $v[$arr]['level'] = intval($row['level']);
            $v[$arr]['lastDate'] = $row['lastDate'];
            $v[$arr]['content'] = $row['content'];
            $v[$arr]['backinfo'] = $row['backinfo'];
            $v[$arr]['beginDate'] = $row['beginDate'];
            $v[$arr]['beginName'] = $row['beginName'];
            $v[$arr]['endDate'] = $row['endDate'];
            $v[$arr]['endName'] = $row['endName'];
            $v[$arr]['isDisable'] = intval($row['isDisable']);
        }
        $json['status'] = 200;
        $json['msg'] = 'success';
        $json['data']['current'] = $current;
        $json['data']['total'] = $total;
        $json['data']['pages'] = ceil($total / $pageSize);
        $json['data']['list'] = $v;
        return $this->response->setJSON($json);
    }
    // 工作转交 状态标记
    public function dutyDisable()
    {
        $id = (int)$this->request->getGetPost('id');
        $isDisable = (int)$this->request->getGetPost('isDisable');
        $endName = $this->request->getGetPost('endName');
        $endDate = date('Y-m-d H:i:s');
        if ($id > 0) {
            $this->db->transStart();
            $this->db->table('duty')->where('id', $id)->update(['isDisable' => $isDisable, 'endName' => $endName, 'endDate' => $endDate]);
            $this->db->table('log')->insert(['log' => '将工作转交ID：' . $id . ' 的处理状态标记为' . ($isDisable > 0 ? '已处理' : '未处理'), 'name' => $endName, 'createTime' => date('Y-m-d H:i:s')]);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '操作成功']);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '提交数据不完整']);
    }
    // 工作转交 新增
    public function dutySubmit()
    {
        $data = $this->request->getPost();
        if (is_array($data) && count($data) > 0) {
            $data['beginDate'] = date('Y-m-d H:i:s');
            $data = array_intersect_key($data, array_flip(['targetName', 'level', 'lastDate', 'content', 'beginName', 'beginDate']));
            $this->db->transStart();
            $this->db->table('duty')->insert($data);
            $resid = $this->db->insertID();
            $this->db->table('log')->insert(['log' => '提交了工作转交，ID: ' . $resid, 'name' => $data['beginName'], 'createTime' => date('Y-m-d H:i:s')]);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '新增成功']);
        } else {
            return $this->response->setJSON(['status' => -1, 'msg' => '提交数据不完整']);
        }
    }


    // 发包滞留 列表
    public function getDeliverDullList()
    {
        $current = max((int)$this->request->getGetPost('current'), 1);
        $pageSize = max((int)$this->request->getGetPost('pageSize'), 10);
        $builder = $this->db->table('deliver_bag');
        $builder->where(['scanName' => '', 'scanTime' => '0000-00-00 00:00:00', 'printTime !=' =>  '0000-00-00 00:00:00', 'printTime <=' => date('Y-m-d', strtotime('-1 day')) . ' 11:59:59', 'isDelete' => 0]);
        $list = (clone $builder)->orderBy('id', 'DESC')->limit($pageSize)->offset($pageSize * ($current - 1))->get()->getResultArray();
        $total = $builder->countAllResults();
        $v = [];
        foreach ($list as $arr => $row) {
            $v[$arr]['id'] = intval($row['id']);
            $v[$arr]['no'] = $row['no'];
            $v[$arr]['company'] = $row['company'];
            $v[$arr]['address'] = $row['address'];
            $v[$arr]['printTime'] = '0000-00-00 00:00:00' == $row['printTime'] ? '' : $row['printTime'];
            $v[$arr]['lastUpdator'] = $row['lastUpdator'];
            $v[$arr]['lastFourMobileNum'] = $row['lastFourMobileNum'];
            $v[$arr]['chType'] = $row['chType'];
            $v[$arr]['sellerNick'] = $row['sellerNick'];
            $v[$arr]['orderNo'] = $row['orderNo'];
        }
        $json['status'] = 200;
        $json['msg'] = 'success';
        $json['data']['current'] = $current;
        $json['data']['total'] = $total;
        $json['data']['pages'] = ceil($total / $pageSize);
        $json['data']['list'] = $v;
        return $this->response->setJSON($json);
    }


    // 包裹接收 列表
    public function getDeliverRecList()
    {
        $current = max((int)$this->request->getGetPost('current'), 1);
        $pageSize = max((int)$this->request->getGetPost('pageSize'), 10);
        $type = $this->request->getGetPost('type');
        $uId = (int)$this->request->getGetPost('uId');
        $builder = $this->db->table('deliver_rec');
        $builder->where('isDelete', 0);
        if ($type == 'self') {
            $builder->where('uId', $uId);
        }
        $list = (clone $builder)->orderBy('id', 'DESC')->limit($pageSize)->offset($pageSize * ($current - 1))->get()->getResultArray();
        $total = $builder->countAllResults();
        $v = [];
        foreach ($list as $arr => $row) {
            $v[$arr]['id'] = intval($row['id']);
            $v[$arr]['no'] = $row['no'];
            $v[$arr]['company1'] = $row['company1'];
            $v[$arr]['company2'] = $row['company2'];
            $v[$arr]['boxNum'] = intval($row['boxNum']);
            $v[$arr]['boxWeight'] = (float)$row['boxWeight'];
            $v[$arr]['destroy'] = $row['destroy'];
            $v[$arr]['description'] = $row['description'];
            $v[$arr]['uId'] = intval($row['uId']);
            $v[$arr]['uName'] = $row['uName'];
            $v[$arr]['createTime'] = $row['createTime'] == '0000-00-00 00:00:00' ? '' : $row['createTime'];
        }
        $json['status'] = 200;
        $json['msg'] = 'success';
        $json['data']['current'] = $current;
        $json['data']['total'] = $total;
        $json['data']['pages'] = ceil($total / $pageSize);
        $json['data']['list'] = $v;
        return $this->response->setJSON($json);
    }
    // 包裹接收 提交
    public function deliverRecSubmit()
    {
        $data = $this->request->getPost();
        if (is_array($data) && count($data) > 0) {
            if ($this->db->table('deliver_rec')->where(['no' => $data['no'], 'isDelete' => 0])->countAllResults() > 0) {
                return $this->response->setJSON(['status' => -1, 'msg' => '该单号已接收过']);
            }
            $data['createTime'] = date('Y-m-d H:i:s');
            $this->db->transStart();
            $this->db->table('deliver_rec')->insert($data);
            $resid = $this->db->insertID();
            $this->db->table('log')->insert(['log' => '提交了收包记录，ID: ' . $resid . '，单号：' . $data['no'], 'name' => $data['uName'], 'createTime' => date('Y-m-d H:i:s')]);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '操作成功']);
        } else {
            return $this->response->setJSON(['status' => -1, 'msg' => '提交数据不完整']);
        }
    }
    // 包裹接收 扫码上传（APP）
    public function upDeliverRecNo()
    {
        $ncod = $this->request->getGetPost('ncod');
        if ($ncod) {
            // 处理京东运单的特殊情况
            if (strpos($ncod, 'JD') === 0) {
                $ncod = explode('-', $ncod)[0];
            }
            // 判断格式
            if (!preg_match('/[0-9a-zA-z]+$/', $ncod) || strlen($ncod) < 11 || strlen($ncod) > 15) {
                return $this->response->setJSON(['status' => 100, 'msg' => '单号错误']);
            } else {
                $ncom = '';
                $time = date('Y-m-d H:i:s');
                // 判断所属快递公司
                $deliverRull = (array) $this->db->table('deliver_company')->select('id, name AS com, len, str')->where(['len !=' => '', 'str !=' => '', 'isDelete' => 0])->get()->getResultArray();
                for ($i = 0; $i < count($deliverRull); $i++) {
                    $len = explode('|', $deliverRull[$i]['len']);
                    $str = explode('|', $deliverRull[$i]['str']);
                    // 先匹配运单号长度
                    if (in_array(strlen($ncod), $len)) {
                        // 再匹配运单号抬头
                        for ($j = 0; $j < count($str); $j++) {
                            if (substr($ncod, 0, strlen($str[$j])) == $str[$j]) {
                                $ncom = $deliverRull[$i]['com'];
                            }
                        }
                    }
                }
                if ($this->db->table('deliver_rec')->where(['no' => $ncod, 'isDelete' => 0])->countAllResults() == 0) {
                    $this->db->transStart();
                    $this->db->table('deliver_rec')->insert(['no' => $ncod, 'company1' => $ncom, 'createTime' => $time]);
                    $this->db->table('log')->insert(['log' => '从扫码枪扫描签收了 1 个包裹（收包记录），单号：' . $ncod, 'name' => '系统', 'createTime' => date('Y-m-d H:i:s')]);
                    $this->db->transComplete();
                    if ($this->db->transStatus() === false) {
                        return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚']);
                    }
                    return $this->response->setJSON(['status' => 200, 'msg' => '提交成功', 'data' => [['no' => $ncod, 'company1' => $ncom, 'createTime' => $time]]]);
                } else {
                    $this->db->transStart();
                    $this->db->table('deliver_rec')->where(['no' => $ncod, 'company1' => $ncom, 'isDelete' => 0])->update(['createTime' => $time]);
                    $this->db->table('log')->insert(['log' => '从扫码枪扫描签收了 1 个包裹（收包记录），单号：' . $ncod, 'name' => '系统', 'createTime' => date('Y-m-d H:i:s')]);
                    $this->db->transComplete();
                    if ($this->db->transStatus() === false) {
                        return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚']);
                    }
                    return $this->response->setJSON(['status' => 200, 'msg' => '提交成功', 'data' => [['no' => $ncod, 'company1' => $ncom, 'createTime' => $time]]]);
                }
            }
        } else {
            return $this->response->setJSON(['status' => 100, 'msg' => '单号错误']);
        }
    }


    // 快递估算
    public function deliverPredict()
    {
        $province = $this->request->getGetPost('province');
        $city = $this->request->getGetPost('city');
        $area = $this->request->getGetPost('area');
        $address = mb_substr($this->request->getGetPost('address'), 0, 2);
        $weight = $this->request->getGetPost('weight');
        $uName = $this->request->getGetPost('uName');

        //计算运费
        $amountYZ = deliver_amount_calc('邮政快递', $address, $weight);
        $amountST = deliver_amount_calc('申通快递', $address, $weight);
        $amountSF = deliver_amount_calc('顺丰速运', $address, $weight);
        $amountBS = deliver_amount_calc('百世快运', $address, $weight);
        $amountYT = deliver_amount_calc('圆通速递', $address, $weight);
        $amountJD = deliver_amount_calc('京东快递', $address, $weight);
        $amountJDZH = deliver_amount_calc('京东重货', $address, $weight);
        $builder = $this->db->table('areas')->select('deliverTimeSF, deliverTimeYZ, deliverTimeST, deliverTimeYT, deliverTimeJD, deliverTimeBS, deliverTimeJDZH');
        //获取时效
        if ($area > 0) {
            $builder->where('id', $area);
        } else {
            if ($city > 0) {
                $builder->where('id', $city);
            } else {
                if ($province > 0) {
                    $builder->where('id', $province);
                }
            }
        }
        $deliverTimeArr = $builder->get()->getRowArray();
        //判断现在是否超过下午6点
        $isLate = intval(date('H')) > 18 ? true : false;
        //计算送达签收时间
        $recDateYZ = date('Y年m月d日H点i分', strtotime(date('Y-m-d 18:00:00') . ($isLate ? ' + 1 day' : '')) + 86400 * (float)$deliverTimeArr['deliverTimeYZ']);
        $recDateST = date('Y年m月d日H点i分', strtotime(date('Y-m-d 18:00:00') . ($isLate ? ' + 1 day' : '')) + 86400 * (float)$deliverTimeArr['deliverTimeST']);
        $recDateSF = date('Y年m月d日H点i分', strtotime(date('Y-m-d 18:00:00') . ($isLate ? ' + 1 day' : '')) + 86400 * (float)$deliverTimeArr['deliverTimeSF']);
        $recDateBS = date('Y年m月d日H点i分', strtotime(date('Y-m-d 18:00:00') . ($isLate ? ' + 1 day' : '')) + 86400 * (float)$deliverTimeArr['deliverTimeBS']);
        $recDateYT = date('Y年m月d日H点i分', strtotime(date('Y-m-d 18:00:00') . ($isLate ? ' + 1 day' : '')) + 86400 * (float)$deliverTimeArr['deliverTimeYT']);
        $recDateJD = date('Y年m月d日H点i分', strtotime(date('Y-m-d 18:00:00') . ($isLate ? ' + 1 day' : '')) + 86400 * (float)$deliverTimeArr['deliverTimeJD']);
        $recDateJDZH = date('Y年m月d日H点i分', strtotime(date('Y-m-d 18:00:00') . ($isLate ? ' + 1 day' : '')) + 86400 * (float)$deliverTimeArr['deliverTimeJDZH']);

        //组装并返回数据
        $data = [
            '邮政快递' => ['amount' => $amountYZ, 'deliverTime' => $deliverTimeArr['deliverTimeYZ'], 'isLate' => $isLate, 'recDate' => $recDateYZ],
            '申通快递' => ['amount' => $amountST, 'deliverTime' => $deliverTimeArr['deliverTimeST'], 'isLate' => $isLate, 'recDate' => $recDateST],
            '顺丰速运' => ['amount' => $amountSF, 'deliverTime' => $deliverTimeArr['deliverTimeSF'], 'isLate' => $isLate, 'recDate' => $recDateSF],
            '百世快运' => ['amount' => $amountBS, 'deliverTime' => $deliverTimeArr['deliverTimeBS'], 'isLate' => $isLate, 'recDate' => $recDateBS],
            '圆通速递' => ['amount' => $amountYT, 'deliverTime' => $deliverTimeArr['deliverTimeYT'], 'isLate' => $isLate, 'recDate' => $recDateYT],
            '京东快递' => ['amount' => $amountJD, 'deliverTime' => $deliverTimeArr['deliverTimeJD'], 'isLate' => $isLate, 'recDate' => $recDateJD],
            '京东重货' => ['amount' => $amountJDZH, 'deliverTime' => $deliverTimeArr['deliverTimeJDZH'], 'isLate' => $isLate, 'recDate' => $recDateJDZH]
        ];
        $this->db->table('log')->insert(['log' => '使用快递估算工具估算了 ' . $weight . 'KG 包裹发往 ' . $address . ' 所需的费用与时效', 'name' => $uName, 'createTime' => date('Y-m-d H:i:s')]);
        return $this->response->setJSON(['status' => 200, 'msg' => 'success', 'data' => $data]);
    }


    // 采购收货 获取单据
    public function getPurBillList()
    {
        $current = max((int)$this->request->getGetPost('current'), 1);
        $pageSize = max((int)$this->request->getGetPost('pageSize'), 1);
        $skey = $this->request->getGetPost('skey');
        $type = (int)$this->request->getGetPost('type');
        if ($type == 1) {
            // 采购订单
            $builder = $this->db->table('bill_order')->select('id,billNo,contactName,uName,totalQty,description,createTime,amount,srcInvId1,goods,contactId,uId');
            $builder->where(['transType' => 101101, 'goodsStatus1 <' => 2, 'checkId >' => 0, 'isDraft' => 0, 'isDelete' => 0]);
            if ($skey !== '') {
                $keywords = array_values(array_filter(explode(',', $skey), 'strlen'));
                foreach ($keywords as $kw) {
                    $builder->groupStart()
                        ->like('billNo', $this->db->escapeLikeString($kw))
                        ->orLike('contactName', $this->db->escapeLikeString($kw))
                        ->orWhere('uName', $kw)
                        ->orLike('goods', $this->db->escapeLikeString($kw))
                        ->orLike('description', $this->db->escapeLikeString($kw))
                    ->groupEnd();
                }
            }
            $list = (clone $builder)->orderBy('id', 'DESC')->limit($pageSize)->offset($pageSize * ($current - 1))->get()->getResultArray();
            $total = $builder->countAllResults();
        } else {
            // 特殊采购
            $builder = $this->db->table('bill_contract')->select('id,billNo,contactName,uName,totalQty,description,createTime,isReq,reqInfo');
            $builder->where(['billType' => 'PUR', 'isReq !=' => 1, 'goodsStatus1 <' => 1, 'checkId >' => 0,'isDraft' => 0, 'isDelete' => 0]);
            if ($skey !== '') {
                $keywords = array_values(array_filter(explode(',', $skey), 'strlen'));
                foreach ($keywords as $kw) {
                    $builder->groupStart()
                        ->like('billNo', $this->db->escapeLikeString($kw))
                        ->orLike('contactName', $this->db->escapeLikeString($kw))
                        ->orWhere('uName', $kw)
                        ->orLike('goods', $this->db->escapeLikeString($kw))
                        ->orLike('description', $this->db->escapeLikeString($kw))
                        ->orLike('reqInfo', $this->db->escapeLikeString($kw))
                    ->groupEnd();
                }
            }
            $list = (clone $builder)->orderBy('id', 'DESC')->limit($pageSize)->offset($pageSize * ($current - 1))->get()->getResultArray();
            $total = $builder->countAllResults();
        }
        $v = [];
        foreach ($list as $arr => $row) {
            $v[$arr]['id'] = intval($row['id']);
            $v[$arr]['billNo'] = $row['billNo'];
            $v[$arr]['contactName'] = $row['contactName'];
            $v[$arr]['uName'] = $row['uName'];
            $v[$arr]['totalQty'] = intval($row['totalQty']);
            $v[$arr]['description'] = $row['description'];
            $v[$arr]['createTime'] = $row['createTime'];
            $v[$arr]['isReq'] = $row['isReq'] ?? -1;
            $v[$arr]['reqInfo'] = $row['reqInfo'] ?? '';
            if ($type == 1) { // 采购订单
                if ($row['srcInvId1'] == 0) {
                    $v[$arr]['goods'] = [];
                } else {
                    $v[$arr]['goods'] = (array)json_decode($row['goods'], true);
                    $inventoryInfoIds = $this->db->table('bill_inventory_info')->select('id,qty')->where(['transType' => 102101, 'srcOrderId' => $v[$arr]['id'], 'isDelete' => 0])->orderBy('id', 'ASC')->get()->getResultArray();
                    if (count($v[$arr]['goods']) > 0 && count($inventoryInfoIds) > 0 && count($v[$arr]['goods']) == count($inventoryInfoIds)) {
                        foreach ($v[$arr]['goods'] as $arr2 => $row2) {
                            $v[$arr]['goods'][$arr2]['id'] = intval($inventoryInfoIds[$arr2]['id']);
                            $v[$arr]['goods'][$arr2]['hasRecQty'] = intval($inventoryInfoIds[$arr2]['qty']);
                        }
                    } else {
                        $v[$arr]['goods'] = [];
                    }
                }
            } else {
                // 特殊采购
                $v[$arr]['goods'] = $this->db->table('bill_contract_info')->select('id,no,name,spec,sku,brand,unit,qty,hasRecQty')->where(['iid' => $row['id'], 'isDelete' => 0])->orderBy('id', 'ASC')->get()->getResultArray();
            }
        }
        $json['status'] = 200;
        $json['msg'] = 'success';
        $json['data']['current'] = $current;
        $json['data']['total'] = $total;
        $json['data']['pages'] = ceil($total / $pageSize);
        $json['data']['rows'] = $v;
        return $this->response->setJSON($json);
    }
    // 采购收货 一键创建采购收货单
    public function crePurBillSrcInv()
    {
        $billNo = $this->request->getGetPost('billNo');
        $uId = (int)$this->request->getGetPost('uId');
        $uName = $this->request->getGetPost('uName');
        if (strlen($billNo) > 0) {
            $row = $this->db->table('bill_order')->select('id,billNo,contactName,uName,totalQty,description,createTime,amount,srcInvId1,goods,contactId,uId')->where(['billNo' => $billNo, 'isDelete' => 0])->get()->getRowArray();
            if ($row && isset($row['srcInvId1']) && $row['srcInvId1'] == 0) {
                $inv_data = $row;
                $inv_data['billDate'] = date('Y-m-d');
                $inv_data['billNo'] = str_no('CG');
                $inv_data['billType'] = 'PUR';
                $inv_data['transType'] = 102101;
                $inv_data['totalOrderQty'] = $row['totalQty'];
                $inv_data['totalQty'] = 0;
                $inv_data['goodsStatus'] = 0;
                $inv_data['uId'] = $inv_data['checkId'] = $uId > 0 ? $uId : $row['uId'];
                $inv_data['uName'] = $inv_data['checkName'] = $uName ? $uName : $row['uName'];
                $inv_data['srcOrderId'] = $row['id'];
                $inv_data['srcOrderNo'] = $row['billNo'];
                $inv_data['files'] = '';
                $inv_data['description'] = '';
                $inv_data['createTime'] = $inv_data['modifyTime'] = date('Y-m-d H:i:s');
                $info = array_intersect_key($inv_data, array_flip(['billNo', 'billDate', 'billType', 'transType', 'totalOrderQty', 'totalQty', 'amount', 'goodsStatus', 'uId', 'uName', 'checkId', 'checkName', 'contactId', 'contactName', 'srcOrderId', 'srcOrderNo', 'goods', 'description', 'createTime', 'modifyTime']));
                $this->db->transStart();
                $this->db->table('bill_inventory')->insert($info);
                $inventoryId = $this->db->insertID();
                $this->db->table('bill_order')->where('id', $row['id'])->update(['srcInvId1' => $inventoryId, 'srcInvNo1' => $inv_data['billNo']]);
                $order_info = $this->db->table('bill_order_info')->select('goodsId,storageId,orderQty,price,amount,description')->where(['transType' => 101101, 'iid' => $row['id'], 'isDelete' => 0])->orderBy('id', 'ASC')->get()->getResultArray();
                $v1 = [];
                foreach ($order_info as $arr1 => $row1) {
                    $v1[$arr1]['iid'] = $inventoryId;
                    $v1[$arr1]['billNo'] = $inv_data['billNo'];
                    $v1[$arr1]['billDate'] = $inv_data['billDate'];
                    $v1[$arr1]['billType'] = 'PUR';
                    $v1[$arr1]['transType'] = 102101;
                    $v1[$arr1]['goodsId'] = intval($row1['goodsId']);
                    $v1[$arr1]['storageId'] = intval($row1['storageId']);
                    $v1[$arr1]['orderQty'] = intval($row1['orderQty']);
                    $v1[$arr1]['qty'] = 0;
                    $v1[$arr1]['price'] = abs((float)$row1['price']);
                    $v1[$arr1]['amount'] = abs((float)$row1['amount']);
                    $v1[$arr1]['srcOrderId'] = intval($row['id']);
                    $v1[$arr1]['description'] = $row1['description'];
                }
                $this->db->table('bill_inventory_info')->insertBatch($v1);
                $this->db->table('log')->insert(['log' => '在小程序上一键创建了采购订单 ' . $row['billNo'] . ' 的采购收货单，单号: ' . $inv_data['billNo'], 'name' => $inv_data['uName'], 'createTime' => date('Y-m-d H:i:s')]);
                $goods = (array)json_decode($row['goods'], true);
                $inventoryInfo = $this->db->table('bill_inventory_info')->select('id,qty')->where(['srcOrderId' => $row['id'], 'isDelete' => 0])->orderBy('id', 'ASC')->get()->getResultArray();
                foreach ($goods as $arr2 => $row2) {
                    $goods[$arr2]['id'] = intval($inventoryInfo[$arr2]['id']);
                    $goods[$arr2]['hasRecQty'] = intval($inventoryInfo[$arr2]['qty']);
                }
                $this->db->transComplete();
                if ($this->db->transStatus() === false) {
                    return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚']);
                }
                return $this->response->setJSON(['status' => 200, 'msg' => '操作成功', 'data' => $goods]);
            } else {
                return $this->response->setJSON(['status' => -1, 'msg' => '订单不存在或已有收货单']);
            }
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '传输数据不能为空']);
    }
    // 采购收货 更新单据
    public function upPurBillList()
    {
        $type = (int)$this->request->getGetPost('type');
        $infoId = (int)$this->request->getGetPost('infoId');
        $nowQty = (int)$this->request->getGetPost('nowQty');
        $uName = $this->request->getGetPost('uName');
        if ($type == 1) { // 采购订单
            $this->db->transStart();
            $inv_info = $this->db->table('bill_inventory_info')->where('id', $infoId)->get()->getRowArray();
            $inv = $this->db->table('bill_inventory')->where('id', $inv_info['iid'])->get()->getRowArray();
            // 处理收发退货人
            $pusaName = explode(',', $inv['pusaName']);
            array_push($pusaName, $uName);
            array_unique($pusaName);
            $pusaName = trim(implode(',', $pusaName), ',');
            // 更新单据收货状态
            $this->db->table('bill_inventory_info')->where('id', $infoId)->set('qty', 'qty + ' . $nowQty, false)->update();
            if ($this->db->table('bill_inventory_info')->where('iid', $inv_info['iid'])->where('qty < orderQty')->countAllResults() > 0) {
                //如仍有已收数量小于订单数量的子项，判定为部分收货，更新已收货总数和收货状态
                $this->db->table('bill_inventory')->where('id', $inv_info['iid'])->set('pusaName', $pusaName)->set('goodsStatus', 1)->set('totalQty', 'totalQty + ' . $nowQty, false)->update();
                $this->db->table('bill_order')->where('id', $inv_info['srcOrderId'])->update(['goodsStatus1' => 1]);
            } else {
                //否则判定为已全部收货，更新已收货总数和收货状态
                $this->db->table('bill_inventory')->where('id', $inv_info['iid'])->set('pusaName', $pusaName)->set('goodsStatus', 2)->set('totalQty', 'totalQty + ' . $nowQty, false)->update();
                $this->db->table('bill_order')->where('id', $inv_info['srcOrderId'])->update(['goodsStatus1' => 2]);
            }
            // 插入收货记录
            $inv_log['iid'] = $inv_info['iid'];
            $inv_log['srcOrderId'] = $inv_info['srcOrderId'];
            $inv_log['uName'] = $uName;
            $inv_log['goodsId'] = $inv_info['goodsId'];
            $inv_log['storageId'] = $inv_info['storageId'];
            $inv_log['qty'] = $nowQty;
            $inv_log['createTime'] = date('Y-m-d H:i:s');
            $this->db->table('bill_inventory_log')->insert($inv_log);
            // 更新产品库存
            $this->db->table('goods')->where('id', $inv_info['goodsId'])->set('currentQty', 'currentQty + ' . $nowQty, false)->update();
            // 插入库存流水
            $newQty = $this->db->table('goods')->select('currentQty')->where('id', $inv_info['goodsId'])->get()->getRowArray();
            $invHisData['goodsId'] = $inv_info['goodsId'];
            $invHisData['billId'] = $inv_info['iid'];
            $invHisData['billNo'] = $inv_info['billNo'];
            $invHisData['billType'] = 'PU';
            $invHisData['transType'] = 102101;
            $invHisData['changeType'] = '小程序收货';
            $invHisData['changeTime'] = date('Y-m-d H:i:s');
            $invHisData['changeCurrentQty'] = (float)$nowQty;
            $invHisData['currentQty'] = (float)$newQty['currentQty'];
            $invHisInfo = array_intersect_key($invHisData, array_flip(['goodsId', 'billId', 'billNo', 'billType', 'transType', 'changeType', 'changeTime', 'changeCurrentQty', 'currentQty']));
            $this->db->table('inv_history')->insert($invHisInfo);
            $this->db->table('log')->insert(['log' => '在小程序上进行了采购订单收货，单号：' . $inv['srcOrderNo'] . '，产品ID: ' . $inv_info['goodsId'] . '，收货数量：' . $nowQty, 'name' => $uName, 'createTime' => date('Y-m-d H:i:s')]);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '收货成功']);
        } else { // 特殊采购
            $this->db->transStart();
            $contract_info = $this->db->table('bill_contract_info')->where('id', $infoId)->get()->getRowArray();
            $this->db->table('bill_contract_info')->where('id', $infoId)->set('hasRecQty', 'hasRecQty + ' . $nowQty, false)->update();
            $this->db->table('log')->insert(['log' => '在小程序上进行了特殊采购收货，单号：' . $contract_info['billNo'] . '，产品ID: ' . $contract_info['goodsId'] . '，收货数量：' . $nowQty, 'name' => $uName, 'createTime' => date('Y-m-d H:i:s')]);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '收货成功']);
        }
    }


    // 单位重量 提交
    public function weightSubmit()
    {
        $id = (int)$this->request->getGetPost('id');
        $weight = (float)$this->request->getGetPost('weight');
        $uId = (int)$this->request->getGetPost('uId');
        $uName = $this->request->getGetPost('uName');
        if ($id > 0 && $weight > 0 && $uId > 0 && $uName) {
            $goods = $this->db->table('goods')->where('id', $id)->get()->getRowArray();
            if ($goods) {
                $this->db->transStart();
                $this->db->table('goods')->where('id', $id)->update(['weight' => $weight, 'modName' => $uName, 'modifyTime' => date('Y-m-d H:i:s')]);
                $this->db->table('goods_modify_his')->insert(['goodsId' => $id, 'change' => '从小程序将单位重量由（' . $goods['weight'] . '）修改为（' . $weight . '）', 'changeTime' => date('Y-m-d H:i:s'), 'uId' => $uId, 'uName' => $uName]);
                $this->db->table('log')->insert(['log' => '从小程序编辑了产品的单位重量，产品ID: ' . $id . '，产品编码：' . $goods['no'] . '，新的单位重量：' . $weight, 'name' => $uName, 'createTime' => date('Y-m-d H:i:s')]);
                $this->db->transComplete();
                if ($this->db->transStatus() === false) {
                    return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚']);
                }
                return $this->response->setJSON(['status' => 200, 'msg' => '提交成功']);
            } else {
                return $this->response->setJSON(['status' => -1, 'msg' => '产品不存在']);
            }
        } else {
            return $this->response->setJSON(['status' => -1, 'msg' => '提交数据不完整']);
        }
    }
    // 盘点库存 提交
    public function submitGoodsCheck()
    {
        $id = (int)$this->request->getGetPost('id');
        $no = $this->request->getGetPost('no');
        $checkQty = (float)$this->request->getGetPost('checkQty');
        $checkName = $this->request->getGetPost('checkName');
        $changeQty = (float)$this->request->getGetPost('changeQty');
        $locationId = (int)$this->request->getGetPost('locationId');
        $locationName = $this->request->getGetPost('locationName');
        if ($id > -1) {
            $user = $this->db->table('user')->select('lever')->where(['name' => $checkName, 'isDisable' => 0, 'isDelete' => 0])->get()->getRowArray();
            if ($user) {
                $rights = explode(',', $user['lever']);
                if (in_array(87, $rights)) { // 有权限
                    $data = ['currentQty' => $checkQty, 'checkQty' => $checkQty, 'changeQty' => $changeQty, 'checkName' => $checkName, 'checkTime' => date('Y-m-d H:i:s')];
                    if ($locationId > 0) {
                        $data['locationId'] = $locationId;
                        $data['locationName'] = $locationName;
                    }
                    $this->db->transStart();
                    $this->db->table('goods')->where('id', $id)->update($data);
                    $this->db->table('log')->insert(['log' => '在小程序端提交了库存盘点，产品ID: ' . $id . '，产品编码：' . $no . '， 盘点库存：' . $checkQty . ($locationId > 0 ? '，并顺便重选指定了库位：' . $locationName : ''), 'name' => $checkName, 'createTime' => date('Y-m-d H:i:s')]);
                    $this->db->transComplete();
                    if ($this->db->transStatus() === false) {
                        return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚']);
                    }
                    return $this->response->setJSON(['status' => 200, 'msg' => '提交成功', 'data' => ['checkTime' => $data['checkTime']]]);
                } else {
                    return $this->response->setJSON(['status' => -1, 'msg' => '无库存盘点权限']);
                }
            } else {
                return $this->response->setJSON(['status' => -1, 'msg' => '无此账号']);
            }
        } else {
            return $this->response->setJSON(['status' => -1, 'msg' => '提交数据不完整']);
        }
    }
    // 库位信息 获取
    public function getLocationInfo()
    {
        $type = $this->request->getGetPost('type');
        $builder = $this->db->table('location');
        $builder->where('isDelete', 0);
        if ($type) {
            if ($type == 'parent') {
                $builder->where('parentId', 0);
            } elseif ($type == 'child') {
                $builder->where('parentId >', 0);
            }
        }
        $list = $builder->orderBy('id', 'ASC')->get()->getResultArray();
        $v = [];
        foreach ($list as $arr => $row) {
            $v[$arr]['id'] = intval($row['id']);
            $v[$arr]['storageId'] = intval($row['storageId']);
            $v[$arr]['parentId'] = intval($row['parentId']);
            $v[$arr]['name'] = $row['name'];
        }
        $json['status'] = 200;
        $json['msg'] = 'success';
        $json['data'] = $v;
        return $this->response->setJSON($json);
    }
    // 库位指定 提交
    public function submitGoodsLocation()
    {
        $id = (int)$this->request->getGetPost('id');
        $no = $this->request->getGetPost('no');
        $checkQty = $this->request->getGetPost('checkQty');
        $checkName = $this->request->getGetPost('checkName');
        $changeQty = (float)$this->request->getGetPost('changeQty');
        $locationId = (int)$this->request->getGetPost('locationId');
        $locationName = $this->request->getGetPost('locationName');
        if ($id > -1) {
            $user = $this->db->table('user')->select('lever')->where(['name' => $checkName, 'isDisable' => 0, 'isDelete' => 0])->get()->getRowArray();
            if ($user) {
                $rights = explode(',', $user['lever']);
                if (in_array(87, $rights)) { // 有权限
                    $data = ['locationId' => $locationId, 'locationName' => $locationName];
                    if (is_numeric($checkQty)) {
                        $data['currentQty'] = $checkQty;
                        $data['checkQty'] = $checkQty;
                        $data['changeQty'] = $changeQty;
                        $data['checkName'] = $checkName;
                        $data['checkTime'] = date('Y-m-d H:i:s');
                    }
                    $this->db->transStart();
                    $this->db->table('goods')->where('id', $id)->update($data);
                    $this->db->table('log')->insert(['log' => '提交了库位指定，产品ID: ' . $id . '，编码：' . $no . '，指定库位：' . $locationName . (is_numeric($checkQty) ? '，并顺便提交了盘点库存：' . $checkQty : ''), 'name' => $checkName, 'createTime' => date('Y-m-d H:i:s')]);
                    $this->db->transComplete();
                    if ($this->db->transStatus() === false) {
                        return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚']);
                    }
                    return $this->response->setJSON(['status' => 200, 'msg' => '提交成功', 'data' => ['checkTime' => $data['checkTime'] ?? '']]);
                } else {
                    return $this->response->setJSON(['status' => -1, 'msg' => '无库位指定权限']);
                }
            } else {
                return $this->response->setJSON(['status' => -1, 'msg' => '无此账号']);
            }
        } else {
            return $this->response->setJSON(['status' => -1, 'msg' => '提交数据不完整']);
        }
    }


    // 产品动态
    public function goodsRecentNotice()
    {
        $current = max((int)$this->request->getGetPost('current'), 1);
        $pageSize = max((int)$this->request->getGetPost('pageSize'), 10);
        $type = (int)$this->request->getGetPost('type');
        if ($type == 1) { //最近采购到货
            $builder = $this->db->table('bill_inventory_log');
            $builder->where(['transType' => 102101, 'isDelete' => 0]);
            $list = (clone $builder)->orderBy('id', 'DESC')->limit($pageSize)->offset($pageSize * ($current - 1))->get()->getResultArray();
            $total = $builder->countAllResults();
            $v = [];
            foreach ($list as $arr => $row) {
                $goods = $this->db->table('goods')->where('id', $row['goodsId'])->get()->getRowArray();
                if ($goods) {
                    $v[$arr]['id'] = intval($row['id']);
                    $v[$arr]['qty'] = $row['qty'] . ' ' . $goods['unitName'];
                    $v[$arr]['title'] = $goods['no'] . ' ' . $goods['name'] . ' ' . $goods['spec'] . ' ' . $goods['sku'] . ' ' . $goods['brandName'];
                    $v[$arr]['uName'] = $row['uName'];
                    $v[$arr]['date'] = $row['createTime'] . ' 收货';
                } else {
                    continue;
                }
            }
            $json['status'] = 200;
            $json['msg'] = 'success';
            $json['data']['current'] = $current;
            $json['data']['total'] = $total;
            $json['data']['pages'] = ceil($total / $pageSize);
            $json['data']['list'] = $v;
            return $this->response->setJSON($json);
        } elseif ($type == 2) { //最近配货交付
            $builder = $this->db->table('pro_kitting_task_up');
            $builder->where('isDelete', 0);
            $list = (clone $builder)->orderBy('id', 'DESC')->limit($pageSize)->offset($pageSize * ($current - 1))->get()->getResultArray();
            $total = $builder->countAllResults();
            $v = [];
            foreach ($list as $arr => $row) {
                $goods = $this->db->table('goods')->where('id', $row['goodsId'])->get()->getRowArray();
                if ($goods) {
                    $v[$arr]['id'] = intval($row['id']);
                    $v[$arr]['qty'] = $row['upQty'] . ' ' . $goods['unitName'];
                    $v[$arr]['title'] = $goods['no'] . ' ' . $goods['name'] . ' ' . $goods['spec'] . ' ' . $goods['sku'] . ' ' . $goods['brandName'];
                    $v[$arr]['uName'] = $row['uName'];
                    $v[$arr]['date'] = $row['createTime'] . ' 交付';
                } else {
                    continue;
                }
            }
            $json['status'] = 200;
            $json['msg'] = 'success';
            $json['data']['current'] = $current;
            $json['data']['total'] = $total;
            $json['data']['pages'] = ceil($total / $pageSize);
            $json['data']['list'] = $v;
            return $this->response->setJSON($json);
        } elseif ($type == 3) { //最近缺货申报
            $sql = "SELECT id, 0 AS qty, CONCAT(title, ' ', content) AS title, uName, createTime FROM pur_req WHERE isDelete = 0 UNION ALL SELECT id, qty, CONCAT(goodsText, ' ', content) AS title, uName, createTime FROM pro_kitting_req WHERE isDelete = 0 ORDER BY createTime DESC LIMIT  :pageSize: OFFSET :offset:";
            $list = $this->db->query($sql, [
                'pageSize' => (int)$pageSize,
                'offset'   => (int)($pageSize * ($current - 1))
            ])->getResultArray();
            $v = [];
            foreach ($list as $arr => $row) {
                $v[$arr]['id'] = intval($row['id']);
                $v[$arr]['qty'] = $row['qty'] > 0 ? ($row['qty'] . ' 套') : '';
                $v[$arr]['title'] = $row['title'];
                $v[$arr]['uName'] = $row['uName'];
                $v[$arr]['date'] = $row['createTime'] . ' 申报';
            }
            $json['status'] = 200;
            $json['msg'] = 'success';
            $json['data']['current'] = $current;
            $json['data']['total'] = $this->db->table('pur_req')->where('isDelete', 0)->countAllResults() + $this->db->table('pro_kitting_req')->where('isDelete', 0)->countAllResults();
            $json['data']['pages'] = ceil($json['data']['total'] / $pageSize);
            $json['data']['list'] = $v;
            return $this->response->setJSON($json);
        } elseif ($type == 4) { //最近新品上架
            $builder = $this->db->table('goods');
            $builder->where('isDelete', 0);
            $list = (clone $builder)->orderBy('id', 'DESC')->limit($pageSize)->offset($pageSize * ($current - 1))->get()->getResultArray();
            $total = $builder->countAllResults();
            $v = [];
            foreach ($list as $arr => $row) {
                $v[$arr]['id'] = intval($row['id']);
                $v[$arr]['qty'] = '';
                $v[$arr]['title'] = $row['no'] . ' ' . $row['name'] . ' ' . $row['spec'] . ' ' . $row['sku'] . ' ' . $row['brandName'];
                $v[$arr]['uName'] = $row['uName'];
                $v[$arr]['date'] = $row['createTime'] . ' 上架';
            }
            $json['status'] = 200;
            $json['msg'] = 'success';
            $json['data']['current'] = $current;
            $json['data']['total'] = $total;
            $json['data']['pages'] = ceil($total / $pageSize);
            $json['data']['list'] = $v;
            return $this->response->setJSON($json);
        } else {
            return $this->response->setJSON(['status' => -1, 'msg' => '请求类型错误']);
        }
    }


    // 学习视频 列表
    public function getVideoList()
    {
        $current = max((int)$this->request->getGetPost('current'), 1);
        $pageSize = max((int)$this->request->getGetPost('pageSize'), 10);
        $skey = $this->request->getGetPost('skey');
        $uId = (int)$this->request->getGetPost('uId');
        $builder = $this->db->table('news');
        $builder->where(['type' => 2, 'isDelete' => 0]);
        if ($skey !== '') {
            $keywords = array_filter(explode(',', $skey), 'strlen');
            foreach ($keywords as $kw) {
                $builder->like('title', $this->db->escapeLikeString($kw));
            }
        }
        $user = $this->db->table('user')->where('id', $uId)->get()->getRowArray();
        if ($user && $user['roleId'] == 0) {
            if ($user['allowVideoTypeIds']) {
                $builder->whereIn('videotype', explode(',', $user['allowVideoTypeIds']));
            } else {
                $builder->where('videotype', 0);
            }
        }
        $list = (clone $builder)->orderBy('id', 'DESC')->limit($pageSize)->offset($pageSize * ($current - 1))->get()->getResultArray();
        $total = $builder->countAllResults();
        $v = [];
        foreach ($list as $arr => $row) {
            $v[$arr]['id'] = intval($row['id']);
            $v[$arr]['type'] = $row['type'];
            $v[$arr]['title'] = $row['title'];
            $videoSrc = [];
            preg_match('#<video.*src="(.*)".*>#isU', $row['content'], $videoSrc);
            $v[$arr]['src'] = (is_countable($videoSrc) && count($videoSrc) > 0) ? 'https://erp.56dz.com' . $videoSrc[1] : '';
            $v[$arr]['beginName'] = $row['beginName'];
            $v[$arr]['beginDate'] = $row['beginDate'];
        }
        $json['status'] = 200;
        $json['msg'] = 'success';
        $json['data']['current'] = $current;
        $json['data']['total'] = $total;
        $json['data']['pages'] = ceil($total / $pageSize);
        $json['data']['list'] = $v;
        return $this->response->setJSON($json);
    }
    // 学习视频 更新观看信息
    public function updateWatchinfo()
    {
        $id = (int)$this->request->getGetPost('id');
        $uId = (int)$this->request->getGetPost('uId');
        $uName = $this->request->getGetPost('uName');
        if ($id > 0) {
            $watchinfo = $this->db->table('news')->select('watchinfo')->where(['isDelete' => 0, 'id' => $id])->get()->getRowArray()['watchinfo'] ?? '';
            $watchinfo = $watchinfo ? (array)json_decode($watchinfo, true) : [];
            $isExit = false;
            foreach ($watchinfo as $arr => $row) {
                if ($uId == $row['uId']) {
                    $watchinfo[$arr]['time'] = intval($watchinfo[$arr]['time']) + 10;
                    $watchinfo[$arr]['leave'] = date('Y-m-d H:i:s');
                    $isExit = true;
                }
            }
            if (!$isExit) {
                array_push($watchinfo, ["uId" => $uId, "name" => $uName, "time" => "10", "leave" => date("Y-m-d H:i:s")]);
            }
            $this->db->transStart();
            $this->db->table('news')->where('id', $id)->update(['watchinfo' => json_encode($watchinfo, 320)]);
            $this->db->table('user')->where('id', $uId)->set('studyPower', 'studyPower + 10', false)->update();
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '提交成功', 'data' => $watchinfo]);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '提交的是空数据']);
    }


    // 全员速览
    public function userMap()
    {
        $list = $this->db->table('user')->whereNotIn('name', ['系统','审查员'])->where(['isDisable' => 0, 'isDelete' => 0])->orderBy('joinDate', 'ASC')->get()->getResultArray();
        $today = date('Y-m-d');
        $v = [];
        foreach ($list as $arr => $row) {
            $v[$arr]['id'] = intval($row['id']);
            $v[$arr]['roleId'] = intval($row['roleId']);
            $v[$arr]['avatar'] = '/data/upfile/avatar/' . ($row['avatar'] ?: 'avatar.jpg');
            $v[$arr]['name'] = $row['name'];
            $v[$arr]['sex'] = $row['sex'];
            $v[$arr]['company'] = $row['company'];
            $v[$arr]['department'] = $row['department'];
            $v[$arr]['position'] = $row['position'];
            $v[$arr]['superiorsId'] = intval($row['superiorsId']);
            $v[$arr]['superiorsName'] = $row['superiorsName'];
            $v[$arr]['mobile'] = $row['mobile'];
            $v[$arr]['wechat'] = $row['wechat'];
            $v[$arr]['joinDate'] = $row['joinDate'];
            $v[$arr]['duty'] = $row['duty'];
            $v[$arr]['animal'] = $row['animal'];
            $v[$arr]['astro'] = $row['astro'];
            $v[$arr]['nation'] = $row['nation'];
            $v[$arr]['level'] = $row['level'];
            $clockinInfo = $this->db->table('clockin')->where(['uId' => $row['id'], 'clockDate ' => $today, 'isDelete' => 0])->get()->getRowArray();
            if ($clockinInfo) {
                if (($clockinInfo['amClock'] != '00:00:00' && $clockinInfo['pmClock'] == '00:00:00') || ($clockinInfo['amClock2'] !== '00:00:00' && $clockinInfo['pmClock2'] == '00:00:00') || ($clockinInfo['amClock3'] !== '00:00:00' && $clockinInfo['pmClock3'] == '00:00:00')) {
                    $v[$arr]['onDuty'] = '在岗';
                } elseif (($clockinInfo['pmClock'] != '00:00:00' && $clockinInfo['amClock2'] == '00:00:00') || ($clockinInfo['pmClock2'] != '00:00:00' && $clockinInfo['amClock3'] == '00:00:00') || $clockinInfo['pmClock3'] != '00:00:00') {
                    $v[$arr]['onDuty'] = '离岗';
                } else {
                    if ($clockinInfo['isCheck'] == 1) {
                        $v[$arr]['onDuty'] = '批准' . trim(str_replace(['补卡,', ',补卡', '补卡'], '', $clockinInfo['marks']), ',');
                    } else {
                        $v[$arr]['onDuty'] = '申请' . trim(str_replace(['补卡,', ',补卡', '补卡'], '', $clockinInfo['marks']), ',');
                    }
                }
            } else {
                if ($row['id'] == 7 || $row['id'] == 8 || $row['id'] == 103 || $row['id'] == 172 || $row['id'] == 195) {
                    $v[$arr]['onDuty'] = '';
                } else {
                    $v[$arr]['onDuty'] = '休息';
                }
            }
            if ($row['id'] == 7 || $row['id'] == 8) {
                $v[$arr]['groupLevel'] = 1;
            } elseif ($row['superiorsId'] == 7 || $row['superiorsId'] == 8) {
                $v[$arr]['groupLevel'] = 2;
            } else {
                $v[$arr]['groupLevel'] = 3;
            }
        }
        $json['status'] = 200;
        $json['msg'] = 'success';
        $json['data'] = $v;
        return $this->response->setJSON($json);
    }

    // 重复使用的接口
    // 产品信息 获取
    public function getGoodsInfo()
    {
        $current = max((int)$this->request->getGetPost('current'), 1);
        $pageSize = max((int)$this->request->getGetPost('pageSize'), 1);
        $goodsId = (int)$this->request->getPost('goodsId');
        $no = $this->request->getGetPost('no');
        $skey = $this->request->getGetPost('skey');
        $type = (int)$this->request->getGetPost('type');
        $onlyKit = (int)$this->request->getGetPost('onlyKit');
        $builder = $this->db->table('goods')->select('id,barCode,no,name,spec,sku,brandName,unitName,weight,storageName,locationName,categoryName,currentQty,checkName,checkTime,hasSon,sonGoods,imgs,files,description,isProofread,isDisable,devName,serName,uName,modName,createTime,modifyTime');
        $builder->where('isDelete', 0);
        if ($goodsId > 0) {
            $builder->where('id', $goodsId);
        }
        if ($onlyKit == 1) { // 仅查需配货套件产品
            $kitCids_arr = array_column($this->db->table('category')->select('id')->where('FIND_IN_SET("245", path) >', 0, false)->where('isDelete', 0)->get()->getResultArray(), 'id');
            $builder->whereIn('categoryId', $kitCids_arr);
        }
        if ($type == 1) { // 精确查询
            if ($no) {
                $builder->where('REPLACE(REPLACE(no, "+", ""), "-", "")', $this->db->escape(str_replace(['+', '-'], '', $no)), false);
            } elseif ($skey !== '') {
                $keywords = array_values(array_filter(explode(',', $skey), 'strlen'));
                foreach ($keywords as $kw) {
                    $builder->groupStart()
                        ->where('barCode', $kw)
                        ->orWhere('REPLACE(REPLACE(no, "+", ""), "-", "")', $this->db->escape(str_replace(['+', '-'], '', $kw)), false)
                        ->orWhere('name', $kw)
                        ->orWhere('spec', $kw)
                        ->orWhere('sku', $kw)
                        ->orWhere('brandName', $kw)
                        ->orWhere('devName', $kw)
                        ->orWhere('serName', $kw)
                        ->orWhere('uName', $kw)
                        ->orWhere('modName', $kw)
                        ->orWhere('JSON_SEARCH(sonGoods, "one", ' . $this->db->escape($kw) . ') IS NOT NULL', null, false)
                    ->groupEnd();
                }
            }
        } else { // 模糊查询
            if ($no) {
                $builder->like('REPLACE(REPLACE(no, "+", ""), "-", "")', $this->db->escapeLikeString(str_replace(['+', '-'], '', $no)), 'both', null, false);
            } elseif ($skey !== '') {
                $keywords = array_values(array_filter(explode(',', $skey), 'strlen'));
                foreach ($keywords as $kw) {
                    $builder->groupStart()
                        ->like('barCode', $this->db->escapeLikeString($kw))
                        ->orLike('REPLACE(REPLACE(no, "+", ""), "-", "")', $this->db->escapeLikeString(str_replace(['+', '-'], '', $kw)), 'both', null, false)
                        ->orLike('name', $this->db->escapeLikeString($kw))
                        ->orLike('spec', $this->db->escapeLikeString($kw))
                        ->orLike('sku', $this->db->escapeLikeString($kw))
                        ->orLike('brandName', $this->db->escapeLikeString($kw))
                        ->orLike('devName', $this->db->escapeLikeString($kw))
                        ->orLike('serName', $this->db->escapeLikeString($kw))
                        ->orLike('uName', $this->db->escapeLikeString($kw))
                        ->orLike('modName', $this->db->escapeLikeString($kw))
                        ->orLike('files', $this->db->escapeLikeString($kw))
                        ->orLike('description', $this->db->escapeLikeString($kw))
                        ->orLike('sonGoods', $this->db->escapeLikeString($kw))
                    ->groupEnd();
                }
            }
        }
        $list = (clone $builder)->orderBy('id', 'DESC')->limit($pageSize)->offset($pageSize * ($current - 1))->get()->getResultArray();
        $total = $builder->countAllResults();
        $v = [];
        foreach ($list as $arr => $row) {
            $v[$arr]['id'] = intval($row['id']);
            $v[$arr]['no'] = $row['no'];
            $v[$arr]['barCode'] = $row['barCode'];
            $v[$arr]['name'] = $row['name'];
            $v[$arr]['spec'] = $row['spec'];
            $v[$arr]['sku'] = $row['sku'];
            $v[$arr]['brandName'] = $row['brandName'];
            $v[$arr]['unitName'] = $row['unitName'];
            $v[$arr]['weight'] = (float)$row['weight'];
            $v[$arr]['storageName'] = $row['storageName'];
            $v[$arr]['locationName'] = $row['locationName'];
            $v[$arr]['categoryName'] = $row['categoryName'];
            $v[$arr]['currentQty'] = (float)$row['currentQty'];
            $v[$arr]['checkName'] = $row['checkName'];
            $v[$arr]['checkTime'] = '0000-00-00 00:00:00' == $row['checkTime'] ? '' : $row['checkTime'];
            $v[$arr]['hasSon'] = intval($row['hasSon']);
            $v[$arr]['description'] = $row['description'];
            $v[$arr]['isProofread'] = intval($row['isProofread']);
            $v[$arr]['isDisable'] = intval($row['isDisable']);
            $v[$arr]['devName'] = $row['devName'];
            $v[$arr]['serName'] = $row['serName'];
            $v[$arr]['uName'] = $row['uName'];
            $v[$arr]['modName'] = $row['modName'];
            $v[$arr]['createTime'] = $row['createTime'];
            $v[$arr]['modifyTime'] = $row['modifyTime'];
            if (strlen($row['imgs']) > 10) {
                $v[$arr]['imgs'] = (array)json_decode($row['imgs'], true);
            } else {
                $v[$arr]['imgs'] = [];
            }
            $v2 = [];
            if ($v[$arr]['hasSon'] > 0 && strlen($row['sonGoods']) > 0) {
                $sonlist = (array)json_decode($row['sonGoods'], true);
                foreach ($sonlist as $arr2 => $row2) {
                    $curSonGoods = $this->db->table('goods')->where('id', $row2['gid'])->get()->getRowArray();
                    $v2[$arr2]['gid'] = $row2['gid'];
                    $v2[$arr2]['no'] = $curSonGoods['no'];
                    $v2[$arr2]['name'] = $curSonGoods['name'];
                    $v2[$arr2]['spec'] = $curSonGoods['spec'];
                    $v2[$arr2]['sku'] = $curSonGoods['sku'];
                    $v2[$arr2]['brand'] = $curSonGoods['brandName'];
                    $v2[$arr2]['location'] = $curSonGoods['locationName'];
                    $v2[$arr2]['qty1'] = $row2['qty1'];
                    $v2[$arr2]['unit1'] = $curSonGoods['unitName'];
                    $v2[$arr2]['qty2'] = $row2['qty2'];
                    $v2[$arr2]['unit2'] = $row2['unit2'];
                    $v2[$arr2]['currentQty'] = (float)$curSonGoods['currentQty'];
                    $v2[$arr2]['description'] = $curSonGoods['description'];
                }
            }
            $v[$arr]['sonGoods'] = $v2;
        }
        $json['status'] = 200;
        $json['msg'] = 'success';
        $json['data']['current'] = $current;
        $json['data']['total'] = $total;
        $json['data']['pages'] = ceil($total / $pageSize);
        $json['data']['rows'] = $v;
        return $this->response->setJSON($json);
    }
    // 加工项目信息 获取
    public function getProProcessingProjectInfo()
    {
        $current = max((int)$this->request->getGetPost('current'), 1);
        $pageSize = max((int)$this->request->getGetPost('pageSize'), 1);
        $projectId = (int)$this->request->getGetPost('projectId');
        $searchName = $this->request->getGetPost('searchName');
        $searchType = (int)($this->request->getGetPost('searchType') ?? -1);
        $builder = $this->db->table('pro_processing_project');
        $builder->where('isDelete', 0);
        if ($projectId > 0) {
            $builder->where('id', $projectId);
        }
        if ($searchType >= 0) {
            $builder->where('type', $searchType);
        }
        if ($searchName !== '') {
            $keywords = array_values(array_filter(explode(',', $searchName), 'strlen'));
            foreach ($keywords as $kw) {
                $builder->groupStart()
                    ->like('REPLACE(REPLACE(name, "+", ""), "-", "")', $this->db->escapeLikeString(str_replace(['+', '-'], '', $kw)), 'both', null, false)
                    ->orLike('uName', $this->db->escapeLikeString($kw))
                    ->orLike('modName', $this->db->escapeLikeString($kw))
                    ->orLike('searchName', $this->db->escapeLikeString($kw))
                    ->orLike('description', $this->db->escapeLikeString($kw))
                ->groupEnd();
            }
        }
        $list = (clone $builder)->orderBy('id', 'DESC')->limit($pageSize)->offset($pageSize * ($current - 1))->get()->getResultArray();
        $total = $builder->countAllResults();
        $v = [];
        foreach ($list as $arr => $row) {
            $v[$arr]['id'] = intval($row['id']);
            $v[$arr]['type'] = intval($row['type']);
            $v[$arr]['name'] = $row['name'];
            $v[$arr]['goodsId'] = intval($row['goodsId']);
            $v[$arr]['tPointNum'] = intval($row['tPointNum']);
            $v[$arr]['zPointNum'] = intval($row['zPointNum']);
            $v[$arr]['donePerHourNum'] = intval($row['donePerHourNum']);
            $v[$arr]['needTest'] = intval($row['needTest']);
            $v[$arr]['producePrice'] = $row['producePrice'];
            $v[$arr]['uName'] = $row['uName'];
            $v[$arr]['createTime'] = $row['createTime'];
            $v[$arr]['modName'] = $row['modName'];
            $v[$arr]['modifyTime'] = $row['modifyTime'];
            $v[$arr]['description'] = $row['description'];
            $v[$arr]['isDisable'] = intval($row['isDisable']);
            if ($row['goodsId'] > 0) {
                $goods = $this->db->table('goods')->where(['id' => $row['goodsId'], 'isDelete' => 0])->get()->getRowArray();
                if (is_array($goods) && count($goods) > 0) {
                    $goods['id'] = intval($goods['id']);
                    $goods['no'] = $goods['no'];
                    $goods['barCode'] = $goods['barCode'];
                    $goods['name'] = $goods['name'];
                    $goods['spec'] = $goods['spec'];
                    $goods['sku'] = $goods['sku'];
                    $goods['brandName'] = $goods['brandName'];
                    $goods['unitName'] = $goods['unitName'];
                    $goods['weight'] = (float)$goods['weight'];
                    $goods['storageName'] = $goods['storageName'];
                    $goods['locationName'] = $goods['locationName'];
                    $goods['categoryName'] = $goods['categoryName'];
                    $goods['currentQty'] = (float)$goods['currentQty'];
                    $goods['checkName'] = $goods['checkName'];
                    $goods['checkTime'] = '0000-00-00 00:00:00' == $goods['checkTime'] ? '' : $goods['checkTime'];
                    $goods['hasSon'] = intval($goods['hasSon']);
                    $goods['description'] = $goods['description'];
                    $goods['isProofread'] = intval($goods['isProofread']);
                    $goods['isDisable'] = intval($goods['isDisable']);
                    $goods['devName'] = $goods['devName'];
                    $goods['serName'] = $goods['serName'];
                    $goods['uName'] = $goods['uName'];
                    $goods['modName'] = $goods['modName'];
                    $goods['createTime'] = $goods['createTime'];
                    $goods['modifyTime'] = $goods['modifyTime'];
                    if (strlen($goods['imgs']) > 10) {
                        $goods['imgs'] = (array)json_decode($goods['imgs'], true);
                    } else {
                        $goods['imgs'] = [];
                    }
                    if ($goods['hasSon'] > 0 && strlen($goods['sonGoods']) > 0) {
                        $list = (array)json_decode($goods['sonGoods'], true);
                        foreach ($list as $arr2 => $row2) {
                            $curSonGoods = $this->db->table('goods')->where('id', $row2['gid'])->get()->getRowArray();
                            $w[$arr2]['gid'] = $row2['gid'];
                            $w[$arr2]['no'] = $curSonGoods['no'];
                            $w[$arr2]['name'] = $curSonGoods['name'];
                            $w[$arr2]['spec'] = $curSonGoods['spec'];
                            $w[$arr2]['sku'] = $curSonGoods['sku'];
                            $w[$arr2]['brand'] = $curSonGoods['brandName'];
                            $w[$arr2]['location'] = $curSonGoods['locationName'];
                            $w[$arr2]['qty1'] = $row2['qty1'];
                            $w[$arr2]['unit1'] = $curSonGoods['unitName'];
                            $w[$arr2]['qty2'] = $row2['qty2'];
                            $w[$arr2]['unit2'] = $row2['unit2'];
                            $w[$arr2]['currentQty'] = (float)$curSonGoods['currentQty'];
                            $w[$arr2]['description'] = $curSonGoods['description'];
                        }
                    }
                    $goods['sonGoods'] = $w ?? [];
                    $v[$arr]['goodsInfo'] = $goods;
                } else {
                    $v[$arr]['goodsInfo'] = '';
                }
            } else {
                $v[$arr]['goodsInfo'] = '';
            }
        }
        $json['status'] = 200;
        $json['msg'] = 'success';
        $json['data']['current'] = $current;
        $json['data']['total'] = $total;
        $json['data']['pages'] = ceil($total / $pageSize);
        $json['data']['rows'] = $v;
        return $this->response->setJSON($json);
    }
}
