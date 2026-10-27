<?php

namespace App\Controllers\Api;
use App\Controllers\BaseController;

class SendSubscribeMessage extends BaseController
{
    /** 小程序订阅提醒将在每天特定时间由系统定时任务自动执行 */

    /** 上班打卡提醒 07:58、08:58各执行一次 */
    public function amClockNotice()
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=wxdbf7badeabcfe714&secret=8738eb97e27dc5b95a90f4563e0086f7';
        $res = global_curl($url);
        if ($res !== false) {
            $res = json_decode($res, true);
            $accessToken = $res['access_token'] ?? '';
            if ($accessToken) { // 获取到accessToken
                $url = 'https://api.weixin.qq.com/cgi-bin/message/subscribe/send?access_token=' . $accessToken;
                $users = $this->db->table('user')->select('id, openid, name')->where(['isDelete' => 0, 'isDisable' => 0, 'roleId' => 0, 'name !=' => '审查员', 'department !=' => '后勤部', 'openid !=' => '', 'allowSubscribeMessage' => 1])->get()->getResultArray();
                if (is_countable($users) && count($users) > 0) {
                    foreach ($users as $index => $user) {
                        $hasClockIn = $this->db->table('clockIn')->where(['clockDate' => date('Y-m-d'), 'uId' => $user['id'], 'isDelete' => 0])->groupStart()->where('amClock !=', '00:00:00')->orGroupStart()->where('amClock', '00:00:00')->groupStart()->where('FIND_IN_SET("请假", marks) >', 0, false)->orWhere('FIND_IN_SET("调休", marks) >', 0, false)->orWhere('FIND_IN_SET("补休", marks) >', 0, false)->groupEnd()->groupEnd()->groupEnd()->countAllResults();
                        if ($hasClockIn > 0) { // 已经打卡或已申请请假/调休/补休，不发送消息
                            continue;
                        } elseif (date('N') > 5) { // 如果是周末
                            $lostWeekTodayHasClockIn = $this->db->table('clockIn')->where(['clockDate' => date('Y-m-d', strtotime('-7 day')), 'uId' => $user['id'], 'isDelete' => 0])->groupStart()->where('amClock !=', '00:00:00')->orGroupStart()->where('amClock', '00:00:00')->groupStart()->where('FIND_IN_SET("请假", marks) >', 0, false)->orWhere('FIND_IN_SET("调休", marks) >', 0, false)->orWhere('FIND_IN_SET("补休", marks) >', 0, false)->groupEnd()->groupEnd()->groupEnd()->countAllResults();
                            if ($lostWeekTodayHasClockIn > 0) { // 上周同一天已经打卡或申请请假/调休/补休，说明今天大概率是要上班的，发送消息
                                if ($this->db->table('clockIn')->where(['clockDate' => date('Y-m-d', strtotime('-1 day')), 'uId' => $user['id'], 'pmClock <' => '18:00:00', 'isDelete' => 0])->where('FIND_IN_SET("值班", marks) >', 0, false)->countAllResults() > 0 && date('H:i:s') < '08:58:00') { // 如果昨天有标记值班且下班时间早于18:00，今天07:58执行这次不提醒，08:58执行这次再提醒
                                    continue; // 不发送消息
                                } else { // 其他情况正常发送消息
                                    if (date('H:i:s') < '08:58:00') {
                                        $res = global_curl($url, [
                                            'post' => 1,
                                            'postfields' => json_encode([
                                                'touser' => $user['openid'],
                                                'template_id' => 'aGdeHUz0_0_1LMDru2_V6pD0DN2Y5SX_oJv1AzcWubU',
                                                'page' => 'pages/index/index',
                                                'data' => [
                                                    'phrase1' => ['value' => '上班打卡'],
                                                    'character_string2' => ['value' => '< 08:00']
                                                ]
                                            ], JSON_UNESCAPED_UNICODE),
                                        ]);
                                    } else {
                                        $res = global_curl($url, [
                                            'post' => 1,
                                            'postfields' => json_encode([
                                                'touser' => $user['openid'],
                                                'template_id' => 'aGdeHUz0_0_1LMDru2_V6pD0DN2Y5SX_oJv1AzcWubU',
                                                'page' => 'pages/index/index',
                                                'data' => [
                                                    'phrase1' => ['value' => '上班打卡'],
                                                    'character_string2' => ['value' => '< 09:00']
                                                ]
                                            ], JSON_UNESCAPED_UNICODE),
                                        ]);
                                    }
                                    if ($res !== false) {
                                        $res = json_decode($res, true);
                                        if ($res['errcode'] == 0) {
                                            log_message('error', '发送上班打卡提醒成功！openid: ' . ', name: ' . $user['name']);
                                        } else {
                                            log_message('error', '发送上班打卡提醒失败！openid: ' . ', name: ' . $user['name'] . ', errcode: ' . $res['errcode'] . ', errmsg: ' . $res['errmsg']);
                                        }
                                    }
                                }
                            } else {
                                continue; // 不发送消息
                            }
                        } else {
                            // 周一到周五，正常工作日，直接发送消息
                            if ($this->db->table('clockIn')->where(['clockDate' => date('Y-m-d', strtotime('-1 day')), 'uId' => $user['id'], 'pmClock <' => '18:00:00', 'isDelete' => 0])->where('FIND_IN_SET("值班", marks) >', 0, false)->countAllResults() > 0 && date('H:i:s') < '08:58:00') { // 如果昨天有标记值班且下班时间早于18:00，今天07:58执行这次不提醒，08:58执行这次再提醒
                                continue; // 不发送消息
                            } else { // 其他情况正常发送消息
                                if (date('H:i:s') < '08:58:00') {
                                    $res = global_curl($url, [
                                        'post' => 1,
                                        'postfields' => json_encode([
                                            'touser' => $user['openid'],
                                            'template_id' => 'aGdeHUz0_0_1LMDru2_V6pD0DN2Y5SX_oJv1AzcWubU',
                                            'page' => 'pages/index/index',
                                            'data' => [
                                                'phrase1' => ['value' => '上班打卡'],
                                                'character_string2' => ['value' => '< 08:00']
                                            ]
                                        ], JSON_UNESCAPED_UNICODE),
                                    ]);
                                } else {
                                    $res = global_curl($url, [
                                        'post' => 1,
                                        'postfields' => json_encode([
                                            'touser' => $user['openid'],
                                            'template_id' => 'aGdeHUz0_0_1LMDru2_V6pD0DN2Y5SX_oJv1AzcWubU',
                                            'page' => 'pages/index/index',
                                            'data' => [
                                                'phrase1' => ['value' => '上班打卡'],
                                                'character_string2' => ['value' => '< 09:00']
                                            ]
                                        ], JSON_UNESCAPED_UNICODE),
                                    ]);
                                }
                                if ($res !== false) {
                                    $res = json_decode($res, true);
                                    if ($res['errcode'] == 0) {
                                        log_message('error', '发送上班打卡提醒成功！openid: ' . ', name: ' . $user['name']);
                                    } else {
                                        log_message('error', '发送上班打卡提醒失败！openid: ' . ', name: ' . $user['name'] . ', errcode: ' . $res['errcode'] . ', errmsg: ' . $res['errmsg']);
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    /** 下班打卡提醒 16:40、18:00各执行一次 */
    public function pmClockNotice()
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=wxdbf7badeabcfe714&secret=8738eb97e27dc5b95a90f4563e0086f7';
        $res = global_curl($url);
        if ($res !== false) {
            $res = json_decode($res, true);
            $accessToken = $res['access_token'] ?? '';
            if ($accessToken) {
                $url = 'https://api.weixin.qq.com/cgi-bin/message/subscribe/send?access_token=' . $accessToken;
                $users = $this->db->table('user')->select('id, openid, name')->where(['isDelete' => 0, 'isDisable' => 0, 'roleId' => 0, 'name !=' => '审查员', 'department !=' => '后勤部', 'openid !=' => '', 'allowSubscribeMessage' => 1])->get()->getResultArray();
                if (is_countable($users) && count($users) > 0) {
                    foreach ($users as $index => $user) {
                        $hasClockIn = $this->db->table('clockIn')->where(['clockDate' => date('Y-m-d'), 'uId' => $user['id'], 'isDelete' => 0])->groupStart()->where('pmClock !=', '00:00:00')->orGroupStart()->where('pmClock', '00:00:00')->groupStart()->where('FIND_IN_SET("请假", marks) >', 0, false)->orWhere('FIND_IN_SET("调休", marks) >', 0, false)->orWhere('FIND_IN_SET("补休", marks) >', 0, false)->groupEnd()->groupEnd()->groupEnd()->countAllResults();
                        if ($hasClockIn > 0) { // 已打卡或已申请请假/调休/补休，不发送消息
                            continue;
                        } elseif (date('N') > 5) { // 如果是周末
                            $lostWeekTodayHasClockIn = $this->db->table('clockIn')->where(['clockDate' => date('Y-m-d', strtotime('-7 day')), 'uId' => $user['id'], 'isDelete' => 0])->groupStart()->where('amClock !=', '00:00:00')->orGroupStart()->where('amClock', '00:00:00')->groupStart()->where('FIND_IN_SET("请假", marks) >', 0, false)->orWhere('FIND_IN_SET("调休", marks) >', 0, false)->orWhere('FIND_IN_SET("补休", marks) >', 0, false)->groupEnd()->groupEnd()->groupEnd()->countAllResults();
                            if ($lostWeekTodayHasClockIn > 0) { // 上周同一天已打卡或已申请请假/调休/补休，说明大概率今天是要上班的，发送消息
                                if ($this->db->table('clockIn')->where(['clockDate' => date('Y-m-d', strtotime('-1 day')), 'uId' => $user['id'], 'pmClock <' => '18:00:00', 'isDelete' => 0])->where('FIND_IN_SET("值班", marks) >', 0, false)->countAllResults() == 0 && date('H:i:s') < '18:00:00') { // 如果昨天没有（标记值班且下班时间早于18:00），今天16:40执行这次不提醒，18:00执行这次再次提醒
                                    continue; // 不发送消息
                                } else { // 其他情况正常发送消息
                                    if (date('H:i:s') < '18:00:00') {
                                        $res = global_curl($url, [
                                            'post' => 1,
                                            'postfields' => json_encode([
                                                'touser' => $user['openid'],
                                                'template_id' => 'aGdeHUz0_0_1LMDru2_V6pD0DN2Y5SX_oJv1AzcWubU',
                                                'page' => 'pages/index/index',
                                                'data' => [
                                                    'phrase1' => ['value' => '下班打卡'],
                                                    'character_string2' => ['value' => '> 16:40']
                                                ]
                                            ], JSON_UNESCAPED_UNICODE),
                                        ]);
                                    } else {
                                        $res = global_curl($url, [
                                            'post' => 1,
                                            'postfields' => json_encode([
                                                'touser' => $user['openid'],
                                                'template_id' => 'aGdeHUz0_0_1LMDru2_V6pD0DN2Y5SX_oJv1AzcWubU',
                                                'page' => 'pages/index/index',
                                                'data' => [
                                                    'phrase1' => ['value' => '下班打卡'],
                                                    'character_string2' => ['value' => '> 18:00']
                                                ]
                                            ], JSON_UNESCAPED_UNICODE),
                                        ]);
                                    }
                                    if ($res !== false) {
                                        $res = json_decode($res, true);
                                        if ($res['errcode'] == 0) {
                                            log_message('error', '发送下班打卡提醒成功！openid: ' . ', name: ' . $user['name']);
                                        } else {
                                            log_message('error', '发送下班打卡提醒失败！openid: ' . ', name: ' . $user['name'] . ', errcode: ' . $res['errcode'] . ', errmsg: ' . $res['errmsg']);
                                        }
                                    }
                                }
                            } else {
                                continue; // 不发送消息
                            }
                        } else {
                            // 周一到周五，正常工作日，直接发送消息
                            if ($this->db->table('clockIn')->where(['clockDate' => date('Y-m-d', strtotime('-1 day')), 'uId' => $user['id'],'pmClock <' => '18:00:00', 'isDelete' => 0])->where('FIND_IN_SET("值班", marks) >', 0, false)->countAllResults() == 0 && date('H:i:s') < '18:00:00') { // 如果昨天没有（标记值班且下班时间早于18:00），今天16:40执行这次不提醒，18:00执行这次再次提醒
                                continue; // 不发送消息
                            } else { // 其他情况正常发送消息
                                if (date('H:i:s') < '18:00:00') {
                                    $res = global_curl($url, [
                                        'post' => 1,
                                        'postfields' => json_encode([
                                            'touser' => $user['openid'],
                                            'template_id' => 'aGdeHUz0_0_1LMDru2_V6pD0DN2Y5SX_oJv1AzcWubU',
                                            'page' => 'pages/index/index',
                                            'data' => [
                                                'phrase1' => ['value' => '下班打卡'],
                                                'character_string2' => ['value' => '> 16:40']
                                            ]
                                        ], JSON_UNESCAPED_UNICODE),
                                    ]);
                                } else {
                                    $res = global_curl($url, [
                                        'post' => 1,
                                        'postfields' => json_encode([
                                            'touser' => $user['openid'],
                                            'template_id' => 'aGdeHUz0_0_1LMDru2_V6pD0DN2Y5SX_oJv1AzcWubU',
                                            'page' => 'pages/index/index',
                                            'data' => [
                                                'phrase1' => ['value' => '下班打卡'],
                                                'character_string2' => ['value' => '> 18:00']
                                            ]
                                        ], JSON_UNESCAPED_UNICODE),
                                    ]);
                                }
                                if ($res !== false) {
                                    $res = json_decode($res, true);
                                    if ($res['errcode'] == 0) {
                                        log_message('error', '发送下班打卡提醒成功！openid: ' . ', name: ' . $user['name']);
                                    } else {
                                        log_message('error', '发送下班打卡提醒失败！openid: ' . ', name: ' . $user['name'] . ', errcode: ' . $res['errcode'] . ', errmsg: ' . $res['errmsg']);
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    /** 其他重要事项提醒 */
    public function messageNotice()
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=wxdbf7badeabcfe714&secret=8738eb97e27dc5b95a90f4563e0086f7';
        $res = global_curl($url);
        if ($res !== false) {
            $res = json_decode($res, true);
            $accessToken = $res['access_token'] ?? '';
            if ($accessToken) {
                $url = 'https://api.weixin.qq.com/cgi-bin/message/subscribe/send?access_token=' . $accessToken;
                $users = $this->db->table('user')->select('id, openid, roleId, name, lever')->where(['isDelete' => 0,'isDisable' => 0, 'department !=' => '后勤部', 'openid !=' => '', 'allowSubscribeMessage' => 1])->get()->getResultArray();
                if (is_countable($users) && count($users) > 0) {
                    foreach ($users as $index => $user) {
                        // 责任损失通知
                        $serviceTargetNotice = $this->db->table('service')->where(['disStatus <' => 3, 'targetAmount >' => 0, 'isDisable' => 0, 'isDelete' => 0])->where('FIND_IN_SET(' . $this->db->escape($user['name']) . ', targetName) >', 0, false)->countAllResults();
                        if ($user['roleId'] == 0 && $serviceTargetNotice > 0) {
                            $res = global_curl($url, [
                                'post' => 1,
                                'postfields' => json_encode([
                                    'touser' => $user['openid'],
                                    'template_id' => 'LrXY4p45CyChXKLeX0AqSEqnhitCG-htzmmZukm8hCs',
                                    'page' => 'pages/index/index',
                                    'data' => [
                                        'thing1' => ['value' => '伍陆管理系统'],
                                        'thing2' => ['value' => '您有' . $serviceTargetNotice .'条未确认的责任损失'],
                                        'time3' => ['value' => date('Y-m-d H:i:s')],
                                        'thing4' => ['value' => '重要事项提醒'],
                                        'thing5' => ['value' => '超过7天未亲自确认将自动确认属实']
                                    ]
                                ], JSON_UNESCAPED_UNICODE),
                            ]);
                            if ($res !== false) {
                                $res = json_decode($res, true);
                                if ($res['errcode'] == 0) {
                                    log_message('error', '发送责任损失提醒成功！openid: ' . ', name: ' . $user['name']);
                                } else {
                                    log_message('error', '发送责任损失提醒失败！openid: ' . ', name: ' . $user['name'] . ', errcode: ' . $res['errcode'] . ', errmsg: ' . $res['errmsg']);
                                }
                            }
                        }
                        // 待审批事项通知
                        $rights = explode(',', $user['lever']);
                        if ($user['roleId'] == 1 || in_array(3, $rights)) { // 管理员或有管理员专属权限才进行通知
                            $clockinNum = $this->db->table('clockin')->groupStart()->where('FIND_IN_SET("请假", marks) >', 0, false)->orWhere('FIND_IN_SET("调休", marks) >', 0, false)->orWhere('FIND_IN_SET("补休", marks) >', 0, false)->orWhere('FIND_IN_SET("外勤", marks) >', 0, false)->groupEnd()->WHERE(['isCheck' => 0, 'isDelete' => 0])->countAllResults();
                            $fillWorkHourNum = $this->db->table('work_hour')->where(['type' => 0, 'isCheck' => 0, 'isDelete' => 0])->countAllResults();
                            $overWorkHourNum = $this->db->table('work_hour')->where(['type' => 1, 'isCheck' => 0, 'isDelete' => 0])->countAllResults();
                            $workHourNum = $this->db->table('work_hour')->where(['type' => 2, 'isCheck' => 0,'isDelete' => 0])->countAllResults();
                            $discountReqNum = $this->db->table('discount_req')->where(['checkId' => 0, 'description' => '', 'isDelete' => 0])->countAllResults();
                            $totalNum = $clockinNum + $fillWorkHourNum + $overWorkHourNum + $workHourNum + $discountReqNum;
                            if ($totalNum > 0) {
                                $res = global_curl($url, [
                                    'post' => 1,
                                    'postfields' => json_encode([
                                        'touser' => $user['openid'],
                                        'template_id' => 'LrXY4p45CyChXKLeX0AqSEqnhitCG-htzmmZukm8hCs',
                                        'page' => 'pages/index/index',
                                        'data' => [
                                            'thing1' => ['value' => '伍陆管理系统'],
                                            'thing2' => ['value' => '您有' . $totalNum .'条待审批信息'],
                                            'time3' => ['value' => date('Y-m-d H:i:s')],
                                            'thing4' => ['value' => '审批中心提醒'],
                                            'thing5' => ['value' => '点击前往小程序进行审批']
                                        ]
                                    ], JSON_UNESCAPED_UNICODE),
                                ]);
                                if ($res !== false) {
                                    $res = json_decode($res, true);
                                    if ($res['errcode'] == 0) {
                                        log_message('error', '发送审批中心提醒成功！openid: ' . ', name: ' . $user['name']);
                                    } else {
                                        log_message('error', '发送审批中心提醒失败！openid: ' . ', name: ' . $user['name'] . ', errcode: ' . $res['errcode'] . ', errmsg: ' . $res['errmsg']);
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}
