<?php

namespace App\Controllers\Api;
use App\Controllers\BaseController;

class DailyTask extends BaseController
{
    public function index()
    {
        /** 上月相关数据计算 */

        $beginDate = date('Y-m-01', strtotime(date('Y-m-01') . ' -1 month'));
        $endDate = date('Y-m-t', strtotime(date('Y-m-01') . ' -1 month'));

        // 计算上月各经营主体含税销售额并记录到数据库备用
        $companys = $this->db->table('company')->where(['type' => 1, 'isDisable' => 0, 'isDelete' => 0])->orderBy('ordnum ASC, id ASC')->get()->getResultArray();
        foreach ($companys as $k => $v) {
            // 绑定网店销售金额（电商平台对接了税务局，只要挂了公司资质，销售额都是要报税的）
            $lastMonth_onlineOrderAmount = (float)$this->db->table('bill_order')->select('SUM(amount) AS totalAmount')->where(['billType' => 'SALE', 'billDate >=' => $beginDate, 'billDate <=' => $endDate, 'sId' => $v['id'], 'isCancel' => 0, 'isDraft' => 0,'isDelete' => 0])->notLike('billNo', 'XD', 'after')->notLike('billNo','XXX')->get()->getRowArray()['totalAmount'] ?? 0;
            // 非绑定网店业务开出发票金额（主要是特销单、手工订单、官网订单，其他没挂公司资质的网店订单开出的票）
            $lastMonth_notOnlineOrderInvoiceOutAmount = (float)$this->db->table('invoice')->select('SUM(amount) AS totalAmount')->where(['date >=' => $beginDate, 'date <=' => $endDate, 'inoutType' => 0, 'us' => $v['id'], 'isDelete' => 0])->groupStart()->like('billNo', 'XD')->orLike('billNo', 'XH')->orLike('billNo', 'XXX')->groupEnd()->notLike('status1', '已红冲', 'after')->get()->getRowArray()['totalAmount'] ?? 0;
            $lastMonth_saleAmount = intval($lastMonth_onlineOrderAmount + $lastMonth_notOnlineOrderInvoiceOutAmount);
            if ($this->db->table('sales_amount_monthly')->where(['cId' => $v['id'], 'year' => date('Y', strtotime($beginDate)), 'month' => date('n', strtotime($beginDate))])->countAllResults() > 0) {
                $this->db->table('sales_amount_monthly')->where(['cId' => $v['id'], 'year' => date('Y', strtotime($beginDate)), 'month' => date('n', strtotime($beginDate))])->update(['saleAmount' => $lastMonth_saleAmount]);
            } else {
                $this->db->table('sales_amount_monthly')->insert([
                    'cId' => $v['id'],
                    'cName' => $v['name'],
                    'year' => date('Y', strtotime($beginDate)),
                    'month' => date('n', strtotime($beginDate)),
                    'saleAmount' => $lastMonth_saleAmount
                ]);
            }
        }

        // 计算并更新上月出勤排名（允许并列同一排名）、全勤荣誉奖章
        $maxid = $this->db->table('bill_attendance_info')->select('MAX(iid) as maxid')->where('isDelete', 0)->get()->getRowArray()['maxid'] ?? 0;
        $list = $this->db->table('bill_attendance_info')->select('billNo, uName, department, position, (workHours + fillHours) AS workHours, workDays, (lateTimes + leaveearlyTimes) AS llTimes, clockoffDays, overtimeHours, dutyTimes, isFull')->where(['iid' => $maxid, 'isDelete' => 0])->get()->getResultArray();
        if (count($list) > 0) {
            $sort1 = array_column($list, 'workDays');
            $sort2 = array_column($list, 'workHours');
            $sort3 = array_column($list, 'isFull');
            $sort4 = array_column($list, 'dutyTimes');
            $sort5 = array_column($list, 'overtimeHours');
            $sort6 = array_column($list, 'llTimes');
            $sort7 = array_column($list, 'clockoffDays');
            array_multisort($sort1, SORT_DESC, $sort2, SORT_DESC, $sort3, SORT_DESC, $sort4, SORT_DESC, $sort5, SORT_DESC, $sort6, SORT_ASC, $sort7, SORT_ASC, $list);
            $this->db->table('user_rank')->where(['date' => intval(str_replace('CQ', '', $list[0]['billNo'])), 'name' => 'attRank'])->delete();
            $this->db->table('user_medal')->where(['date' => intval(str_replace('CQ', '', $list[0]['billNo'])) , 'name' => '全勤荣誉'])->delete();
            $rowData = [];
            $rank = 1;
            $dataRank = $dataMedal = [];
            foreach ($list as $k => $v) {
                if ($k == 0) {
                    $rowData = $v;
                    $rank = 1;
                } else {
                    if (!($v['workHours'] == $rowData['workHours'] && $v['workDays'] == $rowData['workDays'] && $v['llTimes'] == $rowData['llTimes'] && $v['clockoffDays'] == $rowData['clockoffDays'] && $v['overtimeHours'] == $rowData['overtimeHours'] && $v['dutyTimes'] == $rowData['dutyTimes'] && $v['isFull'] == $rowData['isFull'])) {
                        $rowData = $v;
                        $rank++;
                    }
                }
                array_push($dataRank, [
                    'date' => intval(str_replace('CQ', '', $v['billNo'])),
                    'name' => 'attRank',
                    'rank' => $rank,
                    'uName' => $v['uName'],
                    'department' => $v['department'],
                    'position' => $v['position']
                ]);
                if ($v['isFull'] == 1) {
                    array_push($dataMedal, [
                        'date' => str_replace('CQ', '', $v['billNo']),
                        'name' => '全勤荣誉',
                        'img' => 'icon-quanqin.png',
                        'uName' => $v['uName'],
                        'department' => $v['department'],
                        'position' => $v['position']
                    ]);
                }
            }
            count($dataRank) > 0 && $this->db->table('user_rank')->insertBatch($dataRank);
            count($dataMedal) > 0 && $this->db->table('user_medal')->insertBatch($dataMedal);
        }

        // 计算并更新上月投标排名、投标冠亚季军奖章、中标飞跃奖章
        $quotedNamelist = $this->db->table('bid_program')->select('quotedName')->where(['createTime >=' => $beginDate . ' 00:00:00', 'createTime <=' => $endDate . ' 23:59:59', 'isDelete' => 0, 'quotedName !=' => ''])->groupBy('quotedName')->get()->getResultArray();
        if (count($quotedNamelist) > 0) {
            $quotedNameStr = '';
            foreach ($quotedNamelist as $k2 => $v2) {
                $quotedNameStr .= ',' . $v2['quotedName'];
            }
            $quotedNameStr = substr($quotedNameStr, 1);
            $quotedNameArr = explode(',', $quotedNameStr);
            $quotedNameArr = array_unique($quotedNameArr);
            $v = [];
            foreach ($quotedNameArr as $k3 => $v3) {
                if ($v3) {
                    $res = $this->db->table('bid_program')->select('SUM(CASE WHEN LOCATE(",", quotedName) > 0 THEN (amount / ((CHAR_LENGTH(quotedName) - CHAR_LENGTH(REPLACE(quotedName, ",", ""))) + 1)) ELSE amount END) AS amount, COUNT(*) AS offerCount', false)->where('createTime >=', $beginDate . ' 00:00:00')->where('createTime <=', $endDate . ' 23:59:59')->like('quotedName', $this->db->escapeLikeString($v3))->where('isDelete', 0)->get()->getRowArray();
                    if ($res && isset($res['amount']) && (int)$res['amount'] > 0) {
                        $v[$k3]['userinfo'] = $this->db->table('user')->where('name', $v3)->get()->getRowArray();
                        $v[$k3]['quotedName'] = $v3;
                        $v[$k3]['offerCount'] = (int)$res['offerCount'];
                        $v[$k3]['amount'] = (float)$res['amount'];
                        // 中标信息
                        $res2 = $this->db->table('bid_program')->select('SUM(CASE WHEN LOCATE(",", quotedName) > 0 THEN (amount / ((CHAR_LENGTH(quotedName) - CHAR_LENGTH(REPLACE(quotedName,",",""))) + 1)) ELSE amount END) AS winAmount, COUNT(*) AS winCount', false)->where('createTime >=', $beginDate . ' 00:00:00')->where('createTime <=', $endDate . ' 23:59:59')->like('quotedName', $this->db->escapeLikeString($v3))->where(['status' => 2, 'isDelete' => 0])->get()->getRowArray();
                        $v[$k3]['winCount'] = ($res2 && isset($res2['winCount'])) ? (int)$res2['winCount'] : 0;
                        $v[$k3]['winAmount'] = ($res2 && isset($res2['winAmount'])) ? (float)$res2['winAmount'] : 0;
                        $v[$k3]['winRate'] = round($v[$k3]['winCount'] / $v[$k3]['offerCount'] * 100, 2);
                        // 计算中标飞跃奖章(上月、上上月，去年同期两个月)
                        $v[$k3]['isLeap'] = 0;
                        $res3 = $this->db->table('bid_program')->select('SUM(CASE WHEN LOCATE(",", quotedName) > 0 THEN (amount / ((CHAR_LENGTH(quotedName) - CHAR_LENGTH(REPLACE(quotedName,",",""))) + 1)) ELSE amount END) AS winAmount', false)->where('createTime >=', date('Y-m-01', strtotime($beginDate . ' -1 month')) . ' 00:00:00')->where('createTime <=', $endDate . ' 23:59:59')->like('quotedName', $this->db->escapeLikeString($v3))->where(['status' => 2, 'isDelete' => 0])->get()->getRowArray();
                        $res4 = $this->db->table('bid_program')->select('SUM(CASE WHEN LOCATE(",", quotedName) > 0 THEN (amount / ((CHAR_LENGTH(quotedName) - CHAR_LENGTH(REPLACE(quotedName,",",""))) + 1)) ELSE amount END) AS winAmount', false)->where('createTime >=', date('Y-m-01', strtotime($beginDate . ' -13 month')) . ' 00:00:00')->where('createTime <=', date('Y-m-t', strtotime($endDate . ' -12 month')) . ' 23:59:59')->like('quotedName', $this->db->escapeLikeString($v3))->where(['status' => 2, 'isDelete' => 0])->get()->getRowArray();
                        if ($res3 && isset($res3['winAmount']) && $res4 && isset($res4['winAmount'])) {
                            if ((float)$res3['winAmount'] > 0 && (float)$res4['winAmount'] > 0 && ((float)$res3['winAmount'] / (float)$res4['winAmount']) >= 1.39) {
                                $v[$k3]['isLeap'] = 1;
                            }
                        }
                    }
                }
            }
            if (count($v) > 0) {
                $sort1 = array_column($v, 'winAmount');
                $sort2 = array_column($v, 'amount');
                $sort3 = array_column($v, 'winCount');
                $sort4 = array_column($v, 'offerCount');
                array_multisort($sort1, SORT_DESC, $sort2, SORT_DESC, $sort3, SORT_DESC, $sort4, SORT_DESC, $v);
                $this->db->table('user_rank')->where(['date' => substr(str_replace('-', '', $beginDate), 0, 6), 'name' => 'bidRank'])->delete();
                $this->db->table('user_medal')->where('date', substr(str_replace('-', '', $beginDate), 0, 6))->whereNotIn('name', ["投标冠军","投标亚军","投标季军","中标飞跃"])->delete();
                $dataRank = $dataMedal = [];
                foreach ($v as $k4 => $v4) {
                    array_push($dataRank, [
                        'date' => substr(str_replace('-', '', $beginDate), 0, 6),
                        'name' => 'bidRank',
                        'rank' => $k4 + 1,
                        'uName' => $v4['quotedName'],
                        'department' => '销售部',
                        'position' => $v4['userinfo']['position']
                    ]);
                    if ($k4 == 0) {
                        array_push($dataMedal, [
                            'date' => substr(str_replace('-', '', $beginDate), 0, 6),
                            'name' => '投标冠军',
                            'img' => 'icon-guanjun-1.png',
                            'uName' => $v4['quotedName'],
                            'department' => '销售部',
                            'position' => $v4['userinfo']['position']
                        ]);
                    } elseif ($k4 == 1) {
                        array_push($dataMedal, [
                            'date' => substr(str_replace('-', '', $beginDate), 0, 6),
                            'name' => '投标亚军',
                            'img' => 'icon-yajun-1.png',
                            'uName' => $v4['quotedName'],
                            'department' => '销售部',
                            'position' => $v4['userinfo']['position']
                        ]);
                    } elseif ($k4 == 2) {
                        array_push($dataMedal, [
                            'date' => substr(str_replace('-', '', $beginDate), 0, 6),
                            'name' => '投标季军',
                            'img' => 'icon-jijun-1.png',
                            'uName' => $v4['quotedName'],
                            'department' => '销售部',
                            'position' => $v4['userinfo']['position']
                        ]);
                    }
                    if ($v4['isLeap'] == 1) {
                        array_push($dataMedal, [
                            'date' => substr(str_replace('-', '', $beginDate), 0, 6),
                            'name' => '中标飞跃',
                            'img' => 'icon-feiyue.png',
                            'uName' => $v4['quotedName'],
                            'department' => '销售部',
                            'position' => $v4['userinfo']['position']
                        ]);
                    }
                }
                count($dataRank) > 0 && $this->db->table('user_rank')->insertBatch($dataRank);
                count($dataMedal) > 0 && $this->db->table('user_medal')->insertBatch($dataMedal);
            }
        }

        // 计算并更新上月销售排名、销售冠亚季军奖章
        $nameStr = '';
<<<<<<< HEAD
        $orderNameArr = $this->db->table('bill_order')->select('uName')->where(['uId >' => 1, 'createTime >=' => $beginDate . ' 00:00:00', 'createTime <=' => $endDate . ' 23:59:59', 'billType' => 'SALE', 'checkId >' => 0, 'moneyStatus1' => 2, 'isCancel' => 0, 'uName !=' => '', 'isDelete' => 0])->where('uName !=', '系统')->groupBy('uName')->get()->getResultArray();
=======
        $orderNameArr = $this->db->table('bill_order')->select('uName')->where(['uId >' => 1, 'createTime >=' => $beginDate . ' 00:00:00', 'createTime <=' => $endDate . ' 23:59:59', 'billType' => 'SALE', 'checkId >' => 0, 'moneyStatus1' => 2, 'isCancel' => 0, 'uName !=' => '', 'uName !=' => '系统', 'isDelete' => 0])->groupBy('uName')->get()->getResultArray();
>>>>>>> 151b415 (更新语言最新版本+最新底层框架后全部差异化代码适配重写)
        if (count($orderNameArr) > 0) {
            foreach ($orderNameArr as $k5 => $v5) {
                $nameStr .= ',' . $v5['uName'];
            }
        }
<<<<<<< HEAD
        $contractNameArr = $this->db->table('bill_contract')->select('saler')->where(['deliverDoneTime >=' => $beginDate . ' 00:00:00','deliverDoneTime <=' => $endDate . ' 23:59:59', 'billType' => 'SALE', 'checkId >' =>  0, 'goodsStatus1 >' => 0, 'redoSoType' => 0, 'saler !=' => '', 'isDelete' => 0])->where('saler !=', '系统')->groupBy('saler')->get()->getResultArray();
=======
        $contractNameArr = $this->db->table('bill_contract')->select('saler')->where(['deliverDoneTime >=' => $beginDate . ' 00:00:00','deliverDoneTime <=' => $endDate . ' 23:59:59', 'billType' => 'SALE', 'checkId >' =>  0, 'goodsStatus1 >' => 0, 'redoSoType' => 0, 'saler !=' => '', 'saler !=' => '系统', 'isDelete' => 0])->groupBy('saler')->get()->getResultArray();
>>>>>>> 151b415 (更新语言最新版本+最新底层框架后全部差异化代码适配重写)
        if (count($contractNameArr) > 0) {
            foreach ($contractNameArr as $k6 => $v6) {
                $nameStr .= ',' . $v6['saler'];
            }
        }
        $nameStr = substr($nameStr, 1);
        $nameArr = explode(',', $nameStr);
        $nameArr = array_unique($nameArr);
        if (count($nameArr) > 0) {
            $v = [];
            foreach ($nameArr as $k7 => $v7) {
                if ($v7) {
                    $v[$k7]['userinfo'] = $this->db->table('user')->where('name', $v7)->get()->getRowArray();
                    $orderAmount = $this->db->table('bill_order')->select('SUM(amount) AS totalAmount')->where(['createTime >=' => $beginDate . ' 00:00:00', 'createTime <=' => $endDate . ' 23:59:59', 'billType' => 'SALE', 'uName' => $v7, 'checkId >' => 0, 'moneyStatus1' => 2, 'isCancel' => 0, 'isDelete' => 0])->get()->getRowArray()['totalAmount'] ?? 0;
                    $sql = 'SELECT SUM(CASE WHEN (CHAR_LENGTH(saler) - CHAR_LENGTH(REPLACE(saler,",",""))) > 0 THEN (amount / ((CHAR_LENGTH(saler) - CHAR_LENGTH(REPLACE(saler,",",""))) + 1) ) ELSE amount END) AS amount FROM bill_contract WHERE deliverDoneTime >= :begin: AND deliverDoneTime <= :end: AND billType = "SALE" AND FIND_IN_SET(:user:, saler) > 0 AND checkId > 0 AND goodsStatus1 > 0 AND redoSoType = 0 AND isDelete = 0';
                    $res = $this->db->query($sql, [
                        'begin'  => $beginDate . ' 00:00:00',
                        'end'    => $endDate . ' 23:59:59',
                        'user' => $v7,
                    ])->getRowArray();
                    $contractAmount = ($res && isset($res['amount'])) ? round((float)$res['amount'], 2) : 0;
                    $onlineshopAmount = $this->db->table('bill_onlineshopMp_info')->select('SUM(amount) AS totalAmount')->where(['beginDate >=' => $beginDate, 'endDate <=' => $endDate, 'uName' => $v7, 'isDelete' => 0])->get()->getRowArray()['totalAmount'] ?? 0;
                    $v[$k7]['uName'] = $v7;
                    $v[$k7]['dateRange'] = $beginDate . ' ~ ' . $endDate;
                    $v[$k7]['orderAmount'] = round((float)$orderAmount, 2);
                    $v[$k7]['contractAmount'] = round((float)$contractAmount, 2);
                    $v[$k7]['onlineshopAmount'] = round((float)$onlineshopAmount, 2);
                    $v[$k7]['totalAmount'] = $v[$k7]['orderAmount'] + $v[$k7]['contractAmount'] + $v[$k7]['onlineshopAmount'];
                }
            }
            if (count($v) > 0) {
                $sort1 = array_column($v, 'totalAmount');
                $sort2 = array_column($v, 'onlineshopAmount');
                $sort3 = array_column($v, 'contractAmount');
                $sort4 = array_column($v, 'orderAmount');
                array_multisort($sort1, SORT_DESC, $sort2, SORT_DESC, $sort3, SORT_DESC, $sort4, SORT_DESC, $v);
                $this->db->table('user_rank')->where(['date' => substr(str_replace('-', '', $beginDate), 0, 6), 'name' => 'saleRank'])->delete();
                $this->db->table('user_medal')->where('date', substr(str_replace('-', '', $beginDate), 0, 6))->whereIn('name', ["销售冠军","销售亚军","销售季军"])->delete();
                $dataRank = $dataMedal = [];
                foreach ($v as $k8 => $v8) {
                    array_push($dataRank, [
                        'date' => substr(str_replace('-', '', $beginDate), 0, 6),
                        'name' => 'saleRank',
                        'rank' => $k8 + 1,
                        'uName' => $v8['uName'],
                        'department' => '销售部',
                        'position' => $v8['userinfo']['position']
                    ]);
                    if ($k8 == 0) {
                        array_push($dataMedal, [
                            'date' => substr(str_replace('-', '', $beginDate), 0, 6),
                            'name' => '销售冠军',
                            'img' => 'icon-guanjun.png',
                            'uName' => $v8['uName'],
                            'department' => '销售部',
                            'position' => $v8['userinfo']['position']
                        ]);
                    } elseif ($k8 == 1) {
                        array_push($dataMedal, [
                            'date' => substr(str_replace('-', '', $beginDate), 0, 6),
                            'name' => '销售亚军',
                            'img' => 'icon-yajun.png',
                            'uName' => $v8['uName'],
                            'department' => '销售部',
                            'position' => $v8['userinfo']['position']
                        ]);
                    } elseif ($k8 == 2) {
                        array_push($dataMedal, [
                            'date' => substr(str_replace('-', '', $beginDate), 0, 6),
                            'name' => '销售季军',
                            'img' => 'icon-jijun.png',
                            'uName' => $v8['uName'],
                            'department' => '销售部',
                            'position' => $v8['userinfo']['position']
                        ]);
                    }
                }
                count($dataRank) > 0 && $this->db->table('user_rank')->insertBatch($dataRank);
                count($dataMedal) > 0 && $this->db->table('user_medal')->insertBatch($dataMedal);
            }
        }

        // 计算并更新上月客服排名、金银铜牌客服奖章
        $users = $this->db->table('user')->select('id,name')->where(['department' => '销售部', 'isDisable' => 0, 'isDelete' => 0])->whereIn('position', ['销售客服','销售经理','市场总监'])->get()->getResultArray();
        if (count($users) > 0) {
            foreach ($users as $k16 => $v16) {
                $score = 0;
                $score += $this->db->table('service')->where(['beginDate >=' => $beginDate . ' 00:00:00', 'beginDate <=' => $endDate . ' 23:59:59', 'beginName' => $v16['name'], 'isDelete' => 0])->countAllResults();
                $score += $this->db->table('service')->where(['endDate >=' => $beginDate . ' 00:00:00', 'endDate <=' => $endDate . ' 23:59:59', 'endName' => $v16['name'], 'isDelete' => 0])->countAllResults() * 3;
                $score += $this->db->table('potential_customer')->where(['createTime >=' => $beginDate . ' 00:00:00', 'createTime <=' => $endDate . ' 23:59:59', 'uName' => $v16['name'], 'isDelete' => 0])->countAllResults() * 2;
                $score += $this->db->table('review_hold')->where(['createTime >=' => $beginDate . ' 00:00:00', 'createTime <=' => $endDate . ' 23:59:59', 'uName' =>  $v16['name'], 'isDisable' => 1, 'isDelete' => 0])->countAllResults() * 3;
                $users[$k16]['score'] = $score;
            }
            $sort1 = array_column($users, 'score');
            $sort2 = array_column($users, 'id');
            array_multisort($sort1, SORT_DESC, $sort2, SORT_ASC, $users);
            $this->db->table('user_rank')->where(['date' => substr(str_replace('-', '', $beginDate), 0, 6), 'name' => 'serviceRank'])->delete();
            $this->db->table('user_medal')->where('date', substr(str_replace('-', '', $beginDate), 0, 6))->whereIn('name', ['金牌客服','银牌客服','铜牌客服'])->delete();
            $dataRank = $dataMedal = [];
            foreach ($users as $k17 => $v17) {
                array_push($dataRank, [
                    'date' => substr(str_replace('-', '', $beginDate), 0, 6),
                    'name' => 'serviceRank',
                    'rank' => $k17 + 1,
                    'uName' => $v17['name'],
                    'department' => '销售部',
                    'position' => '销售客服'
                ]);
                if ($k17 == 0) {
                    array_push($dataMedal, [
                        'date' => substr(str_replace('-', '', $beginDate), 0, 6),
                        'name' => '金牌客服',
                        'img' => 'icon-jinpai.png',
                        'uName' => $v17['name'],
                        'department' => '销售部',
                        'position' => '销售客服'
                    ]);
                } elseif ($k17 == 1) {
                    array_push($dataMedal, [
                        'date' => substr(str_replace('-', '', $beginDate), 0, 6),
                        'name' => '银牌客服',
                        'img' => 'icon-yinpai.png',
                        'uName' => $v17['name'],
                        'department' => '销售部',
                        'position' => '销售客服'
                    ]);
                } elseif ($k17 == 2) {
                    array_push($dataMedal, [
                        'date' => substr(str_replace('-', '', $beginDate), 0, 6),
                        'name' => '铜牌客服',
                        'img' => 'icon-tongpai.png',
                        'uName' => $v17['name'],
                        'department' => '销售部',
                        'position' => '销售客服'
                    ]);
                }
            }
            count($dataRank) > 0 && $this->db->table('user_rank')->insertBatch($dataRank);
            count($dataMedal) > 0 && $this->db->table('user_medal')->insertBatch($dataMedal);
        }

        // 计算并更新上月采购排名、采购冠亚季军奖章
        $orderuIdArr = $this->db->table('bill_order_info')->select('uId')->where(['billDate >=' => $beginDate, 'billDate <=' => $endDate, 'billType' => 'PUR', 'checkId >' => 0, 'uId >' => 1, 'isDelete' => 0])->groupBy('uId')->get()->getResultArray();
        $contractuIdArr = $this->db->table('bill_contract_info')->select('uId')->where(['billDate >=' => $beginDate, 'billDate <=' => $endDate, 'billType' => 'PUR', 'uId >' => 1, 'isDelete' => 0])->whereIn('isReq',[0,2])->groupBy('uId')->get()->getResultArray();
        $uIdArr = array_unique(array_column(array_merge($orderuIdArr, $contractuIdArr), 'uId'));
        $optionValue = $this->db->table('option')->select('optionValue')->where('optionName', 'exp_par')->get()->getRowArray()['optionValue'] ?? '';
        $expPar = $optionValue ? (array)json_decode($optionValue, true) : [];
        if (empty($expPar)) {
            log_message('error', '业绩系数获取失败！');
        } else {
            if (count($uIdArr) > 0) {
                $v = [];
                foreach ($uIdArr as $k9 => $v9) {
                    if ($v9) {
                        $v[$k9]['userinfo'] = $this->db->table('user')->where('id', $v9)->get()->getRowArray();
                        $uName = $v[$k9]['userinfo']['name'];
                        $orderNum = $this->db->table('bill_order_info')->where(['billDate >' => $beginDate, 'billDate <=' => $endDate, 'billType' => 'PUR', 'uId' => $v9, 'isDelete' => 0])->countAllResults(); //采购订单货物种类数
                        $contractNum = $this->db->table('bill_contract_info')->where(['billDate >=' => $beginDate, 'billDate <=' => $endDate, 'billType' => 'PUR', 'uId' => $v9, 'isDelete' => 0])->whereIn('isReq', [0,2])->countAllResults(); //特殊采购货物种类数
                        $supplierNum = $this->db->table('contact')->where(['createTime >=' => $beginDate . ' 00:00:00', 'createTime <=' => $endDate . ' 23:59:59', 'uName' => $uName, 'type' => 1, 'isDisable' => 0, 'isDelete' => 0])->countAllResults(); //新增商家数
                        $invoiceNum = $this->db->table('invoice')->where(['createTime >=' => $beginDate . ' 00:00:00', 'createTime <=' => $endDate . ' 23:59:59', 'uName' => $uName, 'isDelete' => 0])->countAllResults(); //发票录入数
                        $deAmount = $this->db->table('bill_contract_info')->select('SUM(CASE WHEN (limitPrice * 1.25 * qty) <= paidAmount THEN 0 ELSE ((limitPrice * 1.25 * qty) - paidAmount) END) AS deAmount', false)->where(['billDate >=' => $beginDate, 'billDate <=' => $endDate, 'isReq' => 2, 'uId' => $v9, 'billType' => 'PUR', 'limitPrice >' => 0, 'Price >' => 0, 'paidAmount >' => 0, 'isDelete' => 0])->get()->getRowArray()['deAmount'] ?? 0; //特殊采购毛利
                        $core = intval((float)$orderNum * (float)$expPar['pur']['par1'] + (float)$contractNum * (float)$expPar['pur']['par2'] + (float)$supplierNum * (float)$expPar['pur']['par3'] + (float)$deAmount * (float)$expPar['pur']['par4']); //采购业绩
                        $v[$k9]['uName'] = $uName;
                        $v[$k9]['dateRange'] = $beginDate . ' ~ ' . $endDate;
                        $v[$k9]['orderNum'] = $orderNum;
                        $v[$k9]['contractNum'] = $contractNum;
                        $v[$k9]['supplierNum'] = $supplierNum;
                        $v[$k9]['invoiceNum'] = $invoiceNum;
                        $v[$k9]['core'] = $core;
                    }
                }
            }
            if (count($v) > 0) {
                $sort1 = array_column($v, 'core');
                $sort2 = array_column($v, 'orderNum');
                $sort3 = array_column($v, 'contractNum');
                $sort4 = array_column($v, 'supplierNum');
                $sort5 = array_column($v, 'invoiceNum');
                array_multisort($sort1, SORT_DESC, $sort2, SORT_DESC, $sort3, SORT_DESC, $sort4, SORT_DESC, $sort5, SORT_DESC, $v);
                $this->db->table('user_rank')->where(['date' => substr(str_replace('-', '', $beginDate), 0, 6), 'name' => 'purRank'])->delete();
                $this->db->table('user_medal')->where('date', substr(str_replace('-', '', $beginDate), 0, 6))->whereIn('name', ['采购冠军','采购亚军','采购季军'])->delete();
                $dataRank = $dataMedal = [];
                foreach ($v as $k10 => $v10) {
                    array_push($dataRank, [
                        'date' => substr(str_replace('-', '', $beginDate), 0, 6),
                        'name' => 'purRank',
                        'rank' => $k10 + 1,
                        'uName' => $v10['uName'],
                        'department' => '采购部',
                        'position' => $v10['userinfo']['position']
                    ]);
                    if ($k10 == 0) {
                        array_push($dataMedal, [
                            'date' => substr(str_replace('-', '', $beginDate), 0, 6),
                            'name' => '采购冠军',
                            'img' => 'icon-guanjun.png',
                            'uName' => $v10['uName'],
                            'department' => '采购部',
                            'position' => $v10['userinfo']['position']
                        ]);
                    } elseif ($k10 == 1) {
                        array_push($dataMedal, [
                            'date' => substr(str_replace('-', '', $beginDate), 0, 6),
                            'name' => '采购亚军',
                            'img' => 'icon-yajun.png',
                            'uName' => $v10['uName'],
                            'department' => '采购部',
                            'position' => $v10['userinfo']['position']
                        ]);
                    } elseif ($k10 == 2) {
                        array_push($dataMedal, [
                            'date' => substr(str_replace('-', '', $beginDate), 0, 6),
                            'name' => '采购季军',
                            'img' => 'icon-jijun.png',
                            'uName' => $v10['uName'],
                            'department' => '采购部',
                            'position' => $v10['userinfo']['position']
                        ]);
                    }
                }
                count($dataRank) > 0 && $this->db->table('user_rank')->insertBatch($dataRank);
                count($dataMedal) > 0 && $this->db->table('user_medal')->insertBatch($dataMedal);
            }
        }

        // 计算并更新上月回票排名、回票冠亚季军奖章
        $list = $this->db->table('invoice')->select('uName')->where(['date >=' => $beginDate, 'date <=' => $endDate, 'inoutType' => 1, 'uName !=' => '系统', 'isDelete' => 0])->groupBy('uName')->get()->getResultArray();
        if (count($list) > 0) {
            foreach ($list as $k11 => $v11) {
                $list[$k11]['amount'] = $this->db->table('invoice')->select('SUM(amount) AS totalAmount')->where(['date >=' => $beginDate,'date <=' => $endDate, 'inoutType' => 1, 'uName' => $v11['uName'], 'isDelete' => 0])->get()->getRowArray()['totalAmount'] ?? 0;
                $list[$k11]['userinfo'] = $this->db->table('user')->where('name', $v11['uName'])->get()->getRowArray();
            }
            $sort1 = array_column($list, 'amount');
            array_multisort($sort1, SORT_DESC, $list);
            $this->db->table('user_rank')->where(['date' => substr(str_replace('-', '', $beginDate), 0, 6), 'name' => 'invoiceRank'])->delete();
            $this->db->table('user_medal')->where('date', substr(str_replace('-', '', $beginDate), 0, 6))->whereIn('name', ['回票冠军','回票亚军','回票季军'])->delete();
            $dataRank = $dataMedal = [];
            foreach ($list as $k12 => $v12) {
                array_push($dataRank, [
                    'date' => substr(str_replace('-', '', $beginDate), 0, 6),
                    'name' => 'invoiceRank',
                    'rank' => $k12 + 1,
                    'uName' => $v12['uName'],
                    'department' => '采购部',
                    'position' => $v12['userinfo']['position']
                ]);
                if ($k12 == 0) {
                    array_push($dataMedal, [
                        'date' => substr(str_replace('-', '', $beginDate), 0, 6),
                        'name' => '回票冠军',
                        'img' => 'icon-guanjun-1.png',
                        'uName' => $v12['uName'],
                        'department' => '采购部',
                        'position' => $v12['userinfo']['position']
                    ]);
                } elseif ($k12 == 1) {
                    array_push($dataMedal, [
                        'date' => substr(str_replace('-', '', $beginDate), 0, 6),
                        'name' => '回票亚军',
                        'img' => 'icon-yajun-1.png',
                        'uName' => $v12['uName'],
                        'department' => '采购部',
                        'position' => $v12['userinfo']['position']
                    ]);
                } elseif ($k12 == 2) {
                    array_push($dataMedal, [
                        'date' => substr(str_replace('-', '', $beginDate), 0, 6),
                        'name' => '回票季军',
                        'img' => 'icon-jijun-1.png',
                        'uName' => $v12['uName'],
                        'department' => '采购部',
                        'position' => $v12['userinfo']['position']
                    ]);
                }
            }
            count($dataRank) > 0 && $this->db->table('user_rank')->insertBatch($dataRank);
            count($dataMedal) > 0 && $this->db->table('user_medal')->insertBatch($dataMedal);
        }

        // 计算并更新上月生产标兵奖章（允许并列同一排名）
        $maxid = $this->db->table('bill_evaluate_info')->select('MAX(iid) AS maxid')->where('isDelete', 0)->get()->getRowArray()['maxid'] ?? 0;
        $maxScoreInfo = $this->db->table('bill_evaluate_info')->select('iid, billNo, MAX(score) AS maxScore')->where(['iid' => $maxid, 'department' => '生产部', 'isDelete' => 0])->get()->getRowArray();
        if ($maxScoreInfo && $maxScoreInfo['maxScore'] > 0) {
            $list = $this->db->table('bill_evaluate_info')->select('billNo, uId, uName, department, score')->where(['iid' => $maxScoreInfo['iid'], 'department' => '生产部', 'score' => $maxScoreInfo['maxScore'], 'isDelete' => 0])->get()->getResultArray();
            if (count($list) > 0) {
                foreach ($list as $i => $u) {
                    $list[$i]['userinfo'] = $this->db->table('user')->where('id', $u['uId'])->get()->getRowArray();
                    $list[$i]['energy'] = $this->db->table('bill_achievement_info')->select('sumSalary')->where(['uName' => $u['uName'], 'isDelete' => 0])->get()->getRowArray()['sumSalary'] ?? 0;
                }
                $sort1 = array_column($list, 'score');
                $sort2 = array_column($list, 'energy');
                array_multisort($sort1, SORT_DESC, $sort2, SORT_DESC, $list);
                $this->db->table('user_medal')->where(['date' => str_replace('KP', '', $list[0]['billNo']), 'name' => '生产标兵'])->delete();
                $rowData = [];
                $rank = 1;
                $dataMedal = [];
                foreach ($list as $k14 => $v14) {
                    if ($k14 == 0) {
                        $rowData = $v14;
                        $rank = 1;
                    } else {
                        if (!($v14['score'] == $rowData['score'] && $v14['energy'] == $rowData['energy'])) {
                            $rowData = $v14;
                            $rank++;
                        }
                    }
                    if ($rank == 1) {
                        array_push($dataMedal, [
                            'date' => str_replace('KP', '', $v14['billNo']),
                            'name' => '生产标兵',
                            'img' => 'icon-qi.png',
                            'uName' => $v14['uName'],
                            'department' => $v14['department'],
                            'position' => $v14['userinfo']['position']
                        ]);
                    } else {
                        break;
                    }
                }
                count($dataMedal) > 0 && $this->db->table('user_medal')->insertBatch($dataMedal);
            }
        }

        // 计算并更新上月仓储能手奖章（允许并列同一排名）
        $maxid = $this->db->table('bill_evaluate_info')->select('MAX(iid) AS maxid')->where('isDelete', 0)->get()->getRowArray()['maxid'] ?? 0;
        $maxScoreInfo = $this->db->table('bill_evaluate_info')->select('iid, billNo, MAX(score) AS maxScore')->where(['iid' => $maxid, 'department' => '仓储部', 'isDelete' => 0])->get()->getRowArray();
        if ($maxScoreInfo && $maxScoreInfo['maxScore'] > 0) {
            $list = $this->db->table('bill_evaluate_info')->select('billNo, uId, uName, department, score')->where(['iid' => $maxScoreInfo['iid'], 'department' => '仓储部', 'score' => $maxScoreInfo['maxScore'], 'isDelete' => 0])->get()->getResultArray();
            if (count($list) > 0) {
                foreach ($list as $i => $u) {
                    $list[$i]['userinfo'] = $this->db->table('user')->where('id', $u['uId'])->get()->getRowArray();
                    $list[$i]['energy'] = $this->db->table('bill_achievement_info')->select('sumSalary')->where(['uName' => $u['uName'], 'isDelete' => 0])->get()->getRowArray()['sumSalary'] ?? 0;
                }
                $sort1 = array_column($list, 'score');
                $sort2 = array_column($list, 'energy');
                array_multisort($sort1, SORT_DESC, $sort2, SORT_DESC, $list);
                $this->db->table('user_medal')->where(['date' => str_replace('KP', '', $list[0]['billNo']), 'name' => '仓储能手'])->delete();
                $rowData = [];
                $rank = 1;
                $dataMedal = [];
                foreach ($list as $k15 => $v15) {
                    if ($k15 == 0) {
                        $rowData = $v15;
                        $rank = 1;
                    } else {
                        if (!($v15['score'] == $rowData['score'] && $v15['energy'] == $rowData['energy'])) {
                            $rowData = $v15;
                            $rank++;
                        }
                    }
                    if ($rank == 1) {
                        array_push($dataMedal, [
                            'date' => str_replace('KP', '', $v15['billNo']),
                            'name' => '仓储能手',
                            'img' => 'icon-pan.png',
                            'uName' => $v15['uName'],
                            'department' => $v15['department'],
                            'position' => $v15['userinfo']['position']
                        ]);
                    } else {
                        break;
                    }
                }
                count($dataMedal) > 0 && $this->db->table('user_medal')->insertBatch($dataMedal);
            }
        }



        /** 其他数据计算 */

        // 计算并更新历史中标率
        $siteList = array_column($this->db->table('bid_program')->select('bidSiteName')->where(['isDelete' => 0, 'bidSiteName !=' => ''])->orderBy('id asc')->groupBy('bidSiteName')->get()->getResultArray(), 'bidSiteName');
        foreach ($siteList as $arr => $row) {
            if ($row) {
                $bidProgramInfo = $this->db->table('bid_program')->select('SUM(amount) AS amount, SUM(1) AS offerCount')->where(['isDelete' => 0, 'bidSiteName' => $row])->get()->getRowArray();
                if ($bidProgramInfo && isset($bidProgramInfo['offerCount']) && $bidProgramInfo['offerCount'] > 0) {
                    $offerCount = intval($bidProgramInfo['offerCount']);
                    $bidProgramWinInfo = $this->db->table('bid_program')->select('SUM(amount) AS winAmount, SUM(1) AS winCount')->where(['isDelete' => 0, 'bidSiteName' => $row, 'status' => 2])->get()->getRowArray();
                    $winCount = ($bidProgramWinInfo && isset($bidProgramWinInfo['winCount'])) ? intval($bidProgramWinInfo['winCount']) : 0;
                    $winRate = round($winCount / $offerCount * 100, 2);
                    $winInfo = '投 ' . $offerCount . ' 中 ' . $winCount;
                    $this->db->table('bid_site')->where('name', $row)->update(['winRate' => $winRate, 'winInfo' => $winInfo]);
                } else {
                    $this->db->table('bid_site')->where('name', $row)->update(['winRate' => -1, 'winInfo' => '']);
                }
            }
        }
        $contactList = array_column($this->db->table('bid_program')->select('contactName')->where(['isDelete' => 0, 'contactName !=' => ''])->orderBy('id', 'ASC')->groupBy('contactName')->get()->getResultArray(), 'contactName');
        foreach ($contactList as $arr => $row) {
            if ($row) {
                $bidProgramInfo = $this->db->table('bid_program')->select('SUM(amount) AS amount, SUM(1) AS offerCount')->where(['isDelete' => 0, 'contactName' => $row])->get()->getRowArray();
                if ($bidProgramInfo && isset($bidProgramInfo['offerCount']) && $bidProgramInfo['offerCount'] > 0) {
                    $offerCount = intval($bidProgramInfo['offerCount']);
                    $bidProgramWinInfo = $this->db->table('bid_program')->select('SUM(amount) AS winAmount, SUM(1) AS winCount')->where(['isDelete' => 0, 'contactName' => $row, 'status' => 2])->get()->getRowArray();
                    $winCount = ($bidProgramWinInfo && isset($bidProgramWinInfo['winCount'])) ? intval($bidProgramWinInfo['winCount']) : 0;
                    $winRate = round($winCount / $offerCount * 100, 2);
                    $winInfo = '投 ' . $offerCount . ' 中 ' . $winCount;
                    $this->db->table('contact')->where(['name' => $row, 'type' => 0])->update(['winRate' => $winRate, 'winInfo' => $winInfo]);
                } else {
                    $this->db->table('contact')->where(['name' => $row, 'type' => 0])->update(['winRate' => -1, 'winInfo' => '']);
                }
            }
        }

        // 更新员工经验exp2、成长等级
        $lastDate = $this->db->table('bill_attendance')->select('MAX(endDate) AS lastDate')->where('isDelete', 0)->get()->getRowArray()['lastDate'] ?? '';
        if ($lastDate) {
            $startDate = date('Y-m-d', strtotime($lastDate . ' +1 day'));
            $stopDate = date('Y-m-d', strtotime('-1 day'));
            $uNames = $this->db->table('clockin')->select('uName')->where(['clockDate >=' => $startDate, 'clockDate <=' => $stopDate, 'isDelete' => 0])->groupBy('uName')->get()->getResultArray();
            if (is_countable($uNames) && count($uNames) > 0) {
                foreach ($uNames as $index => $uName) {
                    $user = $this->db->table('user')->where('name', $uName['uName'])->get()->getRowArray();
                    $workHours = $workDays = $fillHours = $overtimeHours = $dutyTimes = 0;
                    $list = $this->db->table('clockin')->where(['clockDate >=' => $startDate, 'clockDate <=' => $stopDate, 'isDelete' => 0, 'uName' => $user['name']])->get()->getResultArray();
                    foreach ($list as $arr => $row) {
                        //计算上班、未上班的小时数与换算成的天数
                        $workinHours = $workoutHours = $workinDays = $workoutDays = 0;
                        //有完整首次上下班打卡
                        if ($row['amClock'] != '00:00:00' && $row['pmClock'] != '00:00:00') {
                            //早于8点和晚于18点的时间不算工作时间
                            $row['amClock'] < '08:00:00' && $row['amClock'] = '08:00:00';
                            $row['pmClock'] > '18:00:00' && $row['pmClock'] = '18:00:00';
                            $workinHours += min((strtotime(date('Y-m-d') . ' ' . $row['pmClock']) - strtotime(date('Y-m-d') . ' ' . $row['amClock'])) / 3600, 10);
                        }
                        //有完整二次上下班打卡
                        if ($row['amClock2'] != '00:00:00' && $row['pmClock2'] != '00:00:00') {
                            //早于8点和晚于18点的时间不算工作时间
                            $row['amClock2'] < '08:00:00' && $row['amClock2'] = '08:00:00';
                            $row['pmClock2'] > '18:00:00' && $row['pmClock2'] = '18:00:00';
                            $workinHours += min((strtotime(date('Y-m-d') . ' ' . $row['pmClock2']) - strtotime(date('Y-m-d') . ' ' . $row['amClock2'])) / 3600, 10);
                        }
                        //有完整三次上下班打卡
                        if ($row['amClock3'] != '00:00:00' && $row['pmClock3'] != '00:00:00') {
                            //早于8点和晚于18点的时间不算工作时间
                            $row['amClock3'] < '08:00:00' && $row['amClock3'] = '08:00:00';
                            $row['pmClock3'] > '18:00:00' && $row['pmClock3'] = '18:00:00';
                            $workinHours += min((strtotime(date('Y-m-d') . ' ' . $row['pmClock3']) - strtotime(date('Y-m-d') . ' ' . $row['amClock3'])) / 3600, 10);
                        }
                        //上班打卡早于12点且下班打卡晚于12点，上班小时数要减去午休的时间
                        if ($row['amClock'] != '00:00:00' && $row['amClock'] <= '12:00:00' && $row['pmClock'] > '12:00:00') {
                            $midrestTime = min((strtotime(date('Y-m-d') . ' ' . $row['pmClock']) - strtotime(date('Y-m-d') . ' 12:00:00')) / 3600, 1);
                            $workinHours = $workinHours - $midrestTime;
                        }
                        //二次上班打卡早于12点且二次下班打卡晚于12点，上班小时数要减去午休的时间
                        if ($row['amClock2'] != '00:00:00' && $row['amClock2'] <= '12:00:00' && $row['pmClock2'] > '12:00:00') {
                            $midrestTime = min((strtotime(date('Y-m-d') . ' ' . $row['pmClock2']) - strtotime(date('Y-m-d') . ' 12:00:00')) / 3600, 1);
                            $workinHours = $workinHours - $midrestTime;
                        }
                        //三次上班打卡早于12点且三次下班打卡晚于12点，上班小时数要减去午休的时间
                        if ($row['amClock3'] != '00:00:00' && $row['amClock3'] <= '12:00:00' && $row['pmClock3'] > '12:00:00') {
                            $midrestTime = min((strtotime(date('Y-m-d') . ' ' . $row['pmClock3']) - strtotime(date('Y-m-d') . ' 12:00:00')) / 3600, 1);
                            $workinHours = $workinHours - $midrestTime;
                        }
                        //上班打卡在12点~13点之间且有下班打卡，上班小时数要减去午休的时间
                        if ($row['amClock'] > '12:00:00' && $row['amClock'] < '13:00:00' && $row['pmClock'] != '00:00:00') {
                            if ($row['pmClock'] > '12:00:00' && $row['pmClock'] <= '13:00:00') {
                                $midrestTime = min(max((strtotime(date('Y-m-d') . ' ' . $row['pmClock']) - strtotime(date('Y-m-d') . ' ' . $row['amClock'])) / 3600, 0), 1);
                            } elseif ($row['pmClock'] > '13:00:00') {
                                $midrestTime = min((strtotime(date('Y-m-d') . ' 13:00:00') - strtotime(date('Y-m-d') . ' ' . $row['amClock'])) / 3600, 1);
                            } else {
                                $midrestTime = 0;
                            }
                            $workinHours = $workinHours - $midrestTime;
                        }
                        $workinHours = round($workinHours, 1);
                        $workoutHours = max(round(9 - $workinHours, 1), 0);
                        if (strstr($row['marks'], '值班')) {
                            if ($row['pmClock'] >= '16:40:00' && $row['pmClock'] < '17:50:00') {
                                //当天16:40点到18点打了下班卡且选择了值班标记，则属于正常值班，值班天数直接判定 +1
                                $dutyTimes += 1;
                            } elseif ($row['amClock'] == '00:00:00' && $row['pmClock'] == '00:00:00') {
                                //当天没有打卡，但是有值班标记，则属于休息人员值班，值班天数直接判定 +1
                                $dutyTimes += 1;
                            } elseif ($row['amClock'] >= '08:00:00' && $row['amClock'] <= '09:01:00') {
                                //当天8点到9点打了上班卡且选择了值班标记，且前一天没有标记值班，则判定为值班天数 +1
                                $lastday = date('Y-m-d', strtotime($row['clockDate'] . ' -1 day'));
                                $this->db->table('clockin')->where(['clockDate' => $lastday, 'uId' => $row['uId'], 'isDelete' => 0])->where('FIND_IN_SET("值班", marks) >', 0, false)->countAllResults() < 1 && $dutyTimes += 1;
                            }
                        }
                        $workHours += $workinHours;
                        //如果是值班导致的上班时间缺失，要补上扣掉的上班时长
                        if (strstr($row['marks'], '值班') && (($row['pmClock'] >= '16:40:00' && $row['pmClock'] <= '18:00:00') || ($row['amClock'] >= '08:00:00' && $row['amClock'] < '09:01:00'))) {
                            $workHours += $workoutHours;
                        }
                    }
                    $fillHours = $this->db->table('work_hour')->select('SUM(workHours) AS fillHours')->where(['workDate >=' => $beginDate, 'workDate <=' => $endDate, 'uName' => $user['name'], 'type' => 0, 'isCheck' => 1, 'isDelete' => 0])->get()->getRowArray()['fillHours'] ?? 0;
                    $overtimeHours = $this->db->table('work_hour')->select('SUM(workHours) AS overtimeHours')->where(['workDate >='=> $beginDate, 'workDate <=' => $endDate, 'uName' => $user['name'], 'type' => 1,'isCheck' => 1, 'isDelete' => 0])->get()->getRowArray()['overtimeHours'] ?? 0;
                    $workDays = floor(($workHours + (float)$fillHours) / 9 * 10) / 10;
                    // 最终计算
                    $expinfo = $this->db->table('bill_attendance_info')->select('SUM(workDays) AS a, SUM(dutyTimes) AS b, SUM(fillHours) AS c, SUM(overtimeHours) AS d')->where('uName', $user['name'])->get()->getRowArray();
                    $exp2 = ((float)$expinfo['a'] + (float)$workDays) + ((float)$expinfo['b'] + (int)$dutyTimes) * 0.39 + ((float)$expinfo['c'] + (float)$expinfo['d'] + (float)$fillHours + (float)$overtimeHours) * 0.11;
                    $exp2 > 0 && $this->db->table('user')->where('name', $user['name'])->update(['exp2' => $exp2]);
                    $exp = $this->db->table('user')->select('(exp1 + exp2) AS exp')->where('name', $user['name'])->get()->getRowArray()['exp'] ?? 0;
                    $level = $this->db->table('user_level')->select('id, name')->where(['minExp <=' => intval($exp), 'maxExp >=' => intval($exp), 'isDelete' => 0])->get()->getRowArray() ?? ['id' => 1, 'name' => '萌新'];
                    $this->db->table('user')->where('name', $user['name'])->update(['levelId' => $level['id'], 'level' => $level['name']]);
                }
            }
        }

        // 自动解除特采申请的编辑中、报价中状态，避免头天非正常退出页面导致第二天要手动解除状态的麻烦
        $this->db->table('bill_contract')->where(['billType' => 'PUR', 'isReq' => 1])->update(['quoteName' => '', 'onModify' => 0]);

        // 自动确认属实超过7天仍未手动确认的责任损失（首次记录或重新推送的）
        $this->db->table('service')->where(['targetName !=' => '', 'targetAmount >' => 0, 'beginDate <' => date('Y-m-d H:i:s', strtotime('-7 day')), 'isDisable' => 0, 'isDelete' => 0])->where('CHAR_LENGTH(targetName) !=', 4, false)->whereIn('disStatus', [0,2])->update(['disStatus' => 3]);

        // 排查所有错误产品、套件包含套件产品，做好记录，便于后续直接提取数据，不用每次都慢查询
        $kitCids_arr = array_column($this->db->table('category')->select('id')->where('FIND_IN_SET("245", path) >', 0, false)->where('isDelete', 0)->get()->getResultArray(), 'id');
        $suitCids_arr = array_column($this->db->table('category')->select('id')->where('FIND_IN_SET("280", path) >', 0, false)->where('isDelete', 0)->get()->getResultArray(), 'id');
        $kitGoods_arr = $this->db->table('goods')->select('id,categoryId,hasSon,sonGoods')->whereIn('categoryId', $kitCids_arr)->where('isDelete', 0)->get()->getResultArray();
        $suitGoods_arr = $this->db->table('goods')->select('id,categoryId,hasSon,sonGoods')->whereIn('categoryId', $suitCids_arr)->where('isDelete', 0)->get()->getResultArray();
        $kitGoodsIds_arr = array_column($kitGoods_arr, 'id');
        $suitGoodsIds_arr = array_column($suitGoods_arr, 'id');
        $errGoodsIds_arr = [];
        $kitHasKitGoodsIds_arr = [];
        //检查异常套件
        foreach ($kitGoods_arr as $k1 => $v1) {
            if ($v1['hasSon'] == 0 || strlen($v1['sonGoods']) == 0) {
                //未录入子产品
                $errGoodsIds_arr[] = $v1['id'];
            } else {
                $sonGoods1 = (array)json_decode($v1['sonGoods'], true);
                if (count($sonGoods1) > 0) {
                    foreach ($sonGoods1 as $i1 => $e1) {
                        if (empty($e1['gid'])) {
                            //子产品ID为空
                            $errGoodsIds_arr[] = $v1['id'];
                            break;
                        } elseif ($this->db->table('goods')->where(['id' => $e1['gid'], 'isDelete' => 0])->countAllResults() < 1) {
                            //子产品不存在
                            $errGoodsIds_arr[] = $v1['id'];
                            break;
                        } elseif ($e1['gid'] == $v1['id']) {
                            //套娃了 子产品与父产品相同
                            $errGoodsIds_arr[] = $v1['id'];
                            break;
                        } elseif (in_array($v1['categoryId'], $kitCids_arr) && in_array($e1['gid'], $kitGoodsIds_arr)) { //套件包含套件
                            $kitHasKitGoodsIds_arr[] = $v1['id'];
                            $sonsonGoods = $this->db->table('goods')->select('sonGoods')->where('id', $e1['gid'])->get()->getRowArray()['sonGoods'] ?? '';
                            if (strlen($sonsonGoods) > 0) {
                                $sonsonlist = (array)json_decode($sonsonGoods, true);
                                foreach ($sonsonlist as $sonsonkey => $sonsonrow) {
                                    if (in_array($sonsonrow['gid'], $kitGoodsIds_arr)) {
                                        //子套件又包含套件(三层套娃)
                                        $errGoodsIds_arr[] = $v1['id'];
                                    }
                                }
                            }
                        } elseif (in_array($v1['categoryId'], $kitCids_arr) && in_array($e1['gid'], $suitGoodsIds_arr)) {
                            //套娃了 需配货套件包含了销售搭配套餐
                            $errGoodsIds_arr[] = $v1['id'];
                            break;
                        } elseif (
                            !is_numeric($e1['qty1']) ||
                            !is_numeric($e1['qty2']) ||
                            (float)$e1['qty1'] <= 0 ||
                            (float)$e1['qty2'] <= 0 ||
                            (count(explode('.', $e1['qty1'])) > 1 && strlen(explode('.', $e1['qty1'])[1]) > 5) ||
                            count(explode('.', $e1['qty2'])) > 1
                        ) {
                            //子产品每份数量需是大于 0 的整数或小数（小数点后最多5位）！份数需是大于 0 的整数！
                            $errGoodsIds_arr[] = $v1['id'];
                            break;
                        }
                    }
                } else {
                    //子产品解析出错或个数为0
                    $errGoodsIds_arr[] = $v1['id'];
                }
            }
        }
        //检查异常套餐
        foreach ($suitGoods_arr as $k2 => $v2) {
            if ($v2['hasSon'] == 0 || strlen($v2['sonGoods']) == 0) {
                //未录入子产品
                $errGoodsIds_arr[] = $v2['id'];
            } else {
                $sonGoods2 = (array)json_decode($v2['sonGoods'], true);
                if (count($sonGoods2) > 0) {
                    foreach ($sonGoods2 as $i2 => $e2) {
                        if (empty($e2['gid'])) {
                            //子产品ID为空
                            $errGoodsIds_arr[] = $v2['id'];
                            break;
                        } elseif ($this->db->table('goods')->where(['id' => $e2['gid'], 'isDelete' => 0])->countAllResults() < 1) {
                            //子产品不存在
                            $errGoodsIds_arr[] = $v2['id'];
                            break;
                        } elseif ($e2['gid'] == $v2['id']) {
                            //套娃了 子产品与父产品相同
                            $errGoodsIds_arr[] = $v2['id'];
                            break;
                        } elseif (in_array($v2['categoryId'], $suitCids_arr) && in_array($e2['gid'], $suitGoodsIds_arr)) {
                            //套娃了 销售搭配套餐包含了其他销售搭配套餐
                            $errGoodsIds_arr[] = $v2['id'];
                            break;
                        } elseif (
                            !is_numeric($e2['qty1']) ||
                            !is_numeric($e2['qty2']) ||
                            (float)$e2['qty1'] <= 0 ||
                            (float)$e2['qty2'] <= 0 ||
                            (count(explode('.', $e2['qty1'])) > 1 && strlen(explode('.', $e2['qty1'])[1]) > 5) ||
                            count(explode('.', $e2['qty2'])) > 1
                        ) {
                            //子产品每份数量需是大于 0 的整数或小数（小数点后最多5位）！份数需是大于 0 的整数！
                            $errGoodsIds_arr[] = $v2['id'];
                            break;
                        }
                    }
                } else {
                    //子产品解析出错或个数为0
                    $errGoodsIds_arr[] = $v2['id'];
                }
            }
        }
        if (count($errGoodsIds_arr) > 0) {
            $this->db->table('goods')->whereIn('id', $errGoodsIds_arr)->update(['isError' => 1]);
        }
        if (count($kitHasKitGoodsIds_arr) > 0) {
            $this->db->table('goods')->whereIn('id', $kitHasKitGoodsIds_arr)->update(['isKitHasKit' => 1]);
        }

        /** 其他任务 */

        // 对于InnoDB的表，可以通过以下命令来回收碎片，释放空间，这个是随机读IO操作，会比较耗时，也会阻塞表上正常的DML运行，同时需要占用额外更多的磁盘空间，
        // 对于RDS来说，可能会导致磁盘空间瞬间爆满，实例瞬间被锁定，应用无法做DML操作，所以禁止在系统运行时去执行。
        /* $sql = 'SHOW TABLES';
        $tables = $this->db->query($sql, 2);
        foreach ($tables as $index => $table) {
            $sql = 'alter table ' . $table['Tables_in_myerp'] . ' engine innodb';
            $this->db->query($sql);
        } */
    }
}
