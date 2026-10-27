<?php
namespace App\Controllers\Base;
use App\Controllers\BaseController;
class Clockin extends BaseController
{
    //列表
    public function list()
    {
        if (!$this->myservice->verifyRight('CLOCKIN_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/Clockin/list 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $page = max(intval($this->request->getGetPost('page')), 1);
        $rows = max(intval($this->request->getGetPost('rows')), 100);
        $uName = $this->request->getGetPost('uName');
        $skey = $this->request->getGetPost('skey');
        $beginDate = $this->request->getGetPost('beginDate');
        $endDate = $this->request->getGetPost('endDate');
        $isCheck = intval($this->request->getGetPost('isCheck'));
        $omit = intval($this->request->getGetPost('omit'));
        $builder = $this->db->table('clockin')->where('isDelete', 0);
        if ($omit > 0) {
            $builder->groupStart()
                ->groupStart()
                    ->where('amClock !=', '00:00:00')
                     ->where('pmClock', '00:00:00')
                ->groupEnd()
                ->orGroupStart()
                    ->where('amClock', '00:00:00')
                     ->where('pmClock !=', '00:00:00')
                ->groupEnd()
                ->orGroupStart()
                    ->where('amClock2 !=', '00:00:00')
                     ->where('pmClock2', '00:00:00')
                ->groupEnd()
                ->orGroupStart()
                    ->where('amClock2', '00:00:00')
                     ->where('pmClock2 !=', '00:00:00')
                ->groupEnd()
                ->orGroupStart()
                    ->where('amClock3 !=', '00:00:00')
                     ->where('pmClock3', '00:00:00')
                ->groupEnd()
                ->orGroupStart()
                    ->where('amClock3', '00:00:00')
                     ->where('pmClock3 !=', '00:00:00')
                ->groupEnd()
            ->groupEnd();
        }
        if ($uName) {
            $builder->where('uName', $uName);
        }
        if ($isCheck > -1) {
            $builder->groupStart()
                ->where('FIND_IN_SET("请假", marks) >', 0, false)
                ->orWhere('FIND_IN_SET("调休", marks) >', 0, false)
                ->orWhere('FIND_IN_SET("补休", marks) >', 0, false)
                ->orWhere('FIND_IN_SET("外勤", marks) >', 0, false)
            ->groupEnd()
            ->where('isCheck', $isCheck);
        }
        if ($beginDate) {
            $builder->where('clockDate >=', $beginDate);
        }
        if ($endDate) {
            $builder->where('clockDate <=', $endDate);
        }
        if ($skey !== '' && $skey !== null) {
            $keywords = array_values(array_filter(explode(',', $skey), 'strlen'));
            foreach ($keywords as $kw) {
                $builder->groupStart()
                    ->like('uName', $this->db->escapeLikeString($kw))
                    ->orLike('marks', $this->db->escapeLikeString($kw))
                    ->orLike('description', $this->db->escapeLikeString($kw))
                ->groupEnd();
            }
        }
        $list = (clone $builder)->orderBy('clockDate DESC, id DESC')->limit($rows)->offset($rows * ($page - 1))->get()->getResultArray();
        $v = [];
        $weekarray = ["日", "一", "二", "三", "四", "五", "六"];
        foreach ($list as $arr => $row) {
            $v[$arr]['id'] = intval($row['id']);
            $v[$arr]['uId'] = intval($row['uId']);
            $v[$arr]['uName'] = $row['uName'];
            $v[$arr]['clockDate'] = $row['clockDate'];
            $v[$arr]['weekday'] = "周" . $weekarray[date("w", strtotime($row['clockDate']))];
            $v[$arr]['amClock'] = $row['amClock'];
            $v[$arr]['pmClock'] = $row['pmClock'];
            $v[$arr]['amClock2'] = $row['amClock2'];
            $v[$arr]['pmClock2'] = $row['pmClock2'];
            $v[$arr]['amClock3'] = $row['amClock3'];
            $v[$arr]['pmClock3'] = $row['pmClock3'];
            $v[$arr]['marks'] = trim(preg_replace('/,,/', ',', $row['marks']), ',');
            $v[$arr]['description'] = $row['description'];
            $v[$arr]['latitude'] = $row['latitude'];
            $v[$arr]['longitude'] = $row['longitude'];
            $v[$arr]['createTime'] = $row['createTime'];
            $v[$arr]['isCheck'] = intval($row['isCheck']);
            $v[$arr]['checkName'] = $row['checkName'];
            $v[$arr]['checkTime'] = $row['checkTime'] == '0000-00-00 00:00:00' ? '' : $row['checkTime'];
            $v[$arr]['status'] = intval($row['status']);
        }
        $workDays = $fillHours = $overtimeHours = 0;
        if ($uName) {
            $totalWorkinSeconds = $workDays = 0;
            $user = $this->db->table('user')->where('name', $uName)->get()->getRowArray();
            $allList = (clone $builder)->get()->getResultArray();
            foreach ($allList as $arr => $row) {
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
                        $workinSeconds += (strtotime(date('Y-m-d') . ' 18:00:00') - strtotime(date('Y-m-d') . ' ' . $row['pmClock']) );
                    }
                    if ($row['amClock'] >= '08:00:00' && $row['amClock'] <= '09:00:00') {
                        $workinSeconds += (strtotime(date('Y-m-d') . ' ' . $row['amClock']) - strtotime(date('Y-m-d') . ' 08:00:00'));
                    }
                    if ($row['amClock'] > '09:00:00') {
                        $workinSeconds += 3600;
                    }
                    if ($row['pmClock2'] >= '16:40:00' && $row['pmClock2'] <= '18:00:00') {
                        $workinSeconds += (strtotime(date('Y-m-d') . ' 18:00:00') - strtotime(date('Y-m-d') . ' ' . $row['pmClock2']) );
                    }
                    if ($row['amClock2'] >= '08:00:00' && $row['amClock2'] <= '09:00:00') {
                        $workinSeconds += (strtotime(date('Y-m-d') . ' ' . $row['amClock2']) - strtotime(date('Y-m-d') . ' ' . $row['pmClock']));
                    }
                    if ($row['pmClock3'] >= '16:40:00' && $row['pmClock3'] <= '18:00:00') {
                        $workinSeconds += (strtotime(date('Y-m-d') . ' 18:00:00') - strtotime(date('Y-m-d') . ' ' . $row['pmClock3']) );
                    }
                    if ($row['amClock3'] >= '08:00:00' && $row['amClock3'] <= '09:00:00') {
                        $workinSeconds += (strtotime(date('Y-m-d') . ' ' . $row['amClock3']) - strtotime(date('Y-m-d') . ' ' . $row['pmClock2']));
                    }
                }
                // 如果是请年假导致的上班时间缺失，要补上扣掉的上班时长
                if (strpos($row['marks'], '请假') !== false && strpos($row['description'], '年假') !== false && in_array($user['position'], ['运营总监', '市场总监', '技术总监', '行政总监'])) {
                    $workinSeconds = 9 * 3600; // 请年假当天的上班时长直接算作9小时
                }
                // 处理当天结果数据
                $totalWorkinSeconds += $workinSeconds;
            }
            // 获取补班、加班、计时小时数
            $fillHours = $this->db->table('work_hour')->select('SUM(workHours) AS fillHours')->where(['workDate >=' => $beginDate, 'workDate <=' => $endDate, 'uName' => $uName, 'type' => 0, 'isCheck' => 1, 'isDelete' => 0])->get()->getRowArray()['fillHours'] ?? 0;
            $overtimeHours = $this->db->table('work_hour')->select('SUM(workHours) AS overtimeHours')->where(['workDate >=' => $beginDate, 'workDate <=' => $endDate, 'uName' => $uName, 'type' => 1, 'isCheck' => 1, 'isDelete' => 0])->get()->getRowArray()['overtimeHours'] ?? 0;
            // 再计算出勤天数
            $workDays = floor(($totalWorkinSeconds / 3600 + (float)$fillHours + (float)$overtimeHours) / 9 * 10) / 10;
            // 如果工作天数小数位是9，但缺失时间小于600秒，则向上取整为整数天。
            if (strpos(strval($workDays), '.') !== false && explode('.', strval($workDays))[1] == '9' && (ceil($workDays) * 9 * 3600 - $totalWorkinSeconds) < 600) {
                $workDays = ceil($workDays);
            }
        }
        $total = $builder->countAllResults();
        $json['status'] = 200;
        $json['msg'] = 'success';
        $json['data']['page'] = $page;
        $json['data']['records'] = $total;
        $json['data']['total'] = ceil($json['data']['records'] / $rows);
        $json['data']['rows'] = $v;
        $json['data']['countInfo'] = '出勤合计：' . ($uName ? ($uName . ' ' . $beginDate . ' 至 ' . $endDate . ' 出勤 ' .( $workDays ?? 0) . ' 天' . ($fillHours > 0 ? ' 含补班 ' . $fillHours . ' 小时' : '') . ($overtimeHours > 0 ? ' 含加班 ' . $overtimeHours . ' 小时' : '')) : '请选择具体员工具体时段进行出勤天数汇总计算');
        return $this->response->setJSON($json);
    }
    //获取信息
    public function query()
    {
        if (!$this->myservice->verifyRight('CLOCKIN_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/Clockin/query 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $data = $this->db->table('clockin')->where(['id' => $id, 'isDelete' => 0])->get()->getRowArray();
        if ($data) {
            $info['id'] = intval($data['id']);
            $info['uId'] = intval($data['uId']);
            $info['uName'] = $data['uName'];
            $info['clockDate'] = $data['clockDate'];
            $info['amClock'] = $data['amClock'];
            $info['pmClock'] = $data['pmClock'];
            $info['amClock2'] = $data['amClock2'];
            $info['pmClock2'] = $data['pmClock2'];
            $info['amClock3'] = $data['amClock3'];
            $info['pmClock3'] = $data['pmClock3'];
            $info['marks'] = trim(preg_replace('/,,/', ',', $data['marks']), ',');
            $info['description'] = $data['description'];

            $json['status'] = 200;
            $json['msg'] = 'success';
            $json['data'] = $info;
            return $this->response->setJSON($json);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
    //新增
    public function add()
    {
        if (!$this->myservice->verifyRight('CLOCKIN_ADD')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/Clockin/add 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $uId = intval($this->request->getGetPost('uId'));
        $uName = $this->request->getGetPost('uName');
        $clockDate = $this->request->getGetPost('clockDate');
        $amClock = $this->request->getGetPost('amClock');
        $pmClock = $this->request->getGetPost('pmClock');
        $amClock2 = $this->request->getGetPost('amClock2');
        $pmClock2 = $this->request->getGetPost('pmClock2');
        $amClock3 = $this->request->getGetPost('amClock3');
        $pmClock3 = $this->request->getGetPost('pmClock3');
        $marks = trim(preg_replace('/,,/', ',', $this->request->getGetPost('marks')), ',');
        $description = $this->request->getGetPost('description');
        $isCheck = 0;
        $createTime = date('Y-m-d H:i:s');
        $data = ['uId' => $uId, 'uName' => $uName, 'clockDate' => $clockDate, 'amClock' => $amClock, 'pmClock' => $pmClock, 'amClock2' => $amClock2, 'pmClock2' => $pmClock2, 'amClock3' => $amClock3, 'pmClock3' => $pmClock3, 'marks' => $marks, 'description' => $description, 'isCheck' => $isCheck, 'createTime' => $createTime];
        if ($uId && $uName && $clockDate) {
            if ($this->db->table('clockin')->where(['uId' => $uId, 'uName' => $uName, 'clockDate' => $clockDate, 'isDelete' => 0])->countAllResults() > 0) {
                return $this->response->setJSON(['status' => -1, 'msg' => '已存在该员工该天的考勤打卡记录！']);
            }
            $this->db->transStart();
            $this->db->table('clockin')->insert($data);
            $data['id'] = $this->db->insertID();
            $this->myservice->log('新增了 ' . $uName . ' ' . $clockDate . ' 的考勤信息');
            $weekarray = ["日", "一", "二", "三", "四", "五", "六"];
            $data['weekday'] = "周" . $weekarray[date("w", strtotime($data['clockDate']))];
            // 补卡限制处理
            $fillClockinTimes = intval(((array)json_decode($this->db->table('option')->select('optionValue')->where('optionName', 'system')->get()->getRowArray()['optionValue'] ?? ''))['fillClockinTimes'] ?? 5);
            if ($this->db->table('clockin')->where(['clockDate >=' => date('Y-m-01', strtotime($clockDate)), 'clockDate <=' => date('Y-m-t', strtotime($clockDate)), 'uId' => $uId])->where('FIND_IN_SET("补卡", marks) >', 0, false)->where('isDelete', 0)->countAllResults() > $fillClockinTimes) {
                $data['fillClockinInfo'] = '当月补卡次数已超限，当月已无全勤！';
            } else {
                $data['fillClockinInfo'] = '';
            }
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '新增成功！', 'data' => $data]);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '提交的是空数据！']);
    }
    //编辑
    public function update()
    {
        if (!$this->myservice->verifyRight('CLOCKIN_UPDATE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/Clockin/update 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $uId = intval($this->request->getGetPost('uId'));
        $uName = $this->request->getGetPost('uName');
        $clockDate = $this->request->getGetPost('clockDate');
        $amClock = $this->request->getGetPost('amClock');
        $pmClock = $this->request->getGetPost('pmClock');
        $amClock2 = $this->request->getGetPost('amClock2');
        $pmClock2 = $this->request->getGetPost('pmClock2');
        $amClock3 = $this->request->getGetPost('amClock3');
        $pmClock3 = $this->request->getGetPost('pmClock3');
        $marks = trim(preg_replace('/,,/', ',', $this->request->getGetPost('marks')), ',');
        $description = $this->request->getGetPost('description');
        if ($id > 0) {
            $data = ['amClock' => $amClock, 'pmClock' => $pmClock, 'amClock2' => $amClock2, 'pmClock2' => $pmClock2, 'amClock3' => $amClock3, 'pmClock3' => $pmClock3, 'marks' => $marks, 'description' => $description];
            $this->db->transStart();
            $this->db->table('clockin')->where('id', $id)->update($data);
            $this->myservice->log('编辑了 ' . $uName . ' ' . $clockDate . ' 的考勤信息');
            // 补卡限制处理
            $fillClockinTimes = intval(((array)json_decode($this->db->table('option')->select('optionValue')->where('optionName', 'system')->get()->getRowArray()['optionValue'] ?? ''))['fillClockinTimes'] ?? 5);
            if ($this->db->table('clockin')->where(['clockDate >=' => date('Y-m-01', strtotime($clockDate)), 'clockDate <=' => date('Y-m-t', strtotime($clockDate)), 'uId' => $uId])->where('FIND_IN_SET("补卡", marks) >', 0, false)->where('isDelete', 0)->countAllResults() > $fillClockinTimes) {
                $data['fillClockinInfo'] = '当月补卡次数已超限，当月已无全勤！';
            } else {
                $data['fillClockinInfo'] = '';
            }
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            $data['id'] = $id;
            return $this->response->setJSON(['status' => 200, 'msg' => '新增成功！', 'data' => $data]);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '提交的是空数据！']);
    }
    //删除
    public function delete()
    {
        if (!$this->myservice->verifyRight('CLOCKIN_DELETE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/Clockin/delete 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $data = $this->db->table('clockin')->where('id', $id)->get()->getRowArray();
        if ($data) {
            $this->db->transStart();
            $this->db->table('clockin')->where('id', $id)->update(['isDelete' => 1]);
            $this->myservice->log('删除了 ' . $data['uName'] . ' 的 ' . $data['clockDate'] . ' 的考勤打卡');
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '删除成功！']);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
    //请假调休补休审批
    public function check()
    {
        if (!$this->myservice->verifyRight('CLOCKIN_CHECK')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/Clockin/check 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $isCheck = intval($this->request->getGetPost('isCheck'));
        $uName = $this->request->getGetPost('uName');
        $clockDate = $this->request->getGetPost('clockDate');
        $marks = trim(preg_replace('/,,/', ',', $this->request->getGetPost('marks')), ',');
        if ($id > 0 && $uName && $clockDate && $marks) {
            $this->db->transStart();
            $this->db->table('clockin')->where('id', $id)->update(['isCheck' => $isCheck, 'checkName' => $this->myerp['uName'], 'checkTime' => date('Y-m-d H:i:s')]);
            $this->myservice->log(($isCheck == 0 ? '反批了' : ($isCheck == 1 ? '批准了' : '拒绝了')) . ' ' . $uName . ' ' . $clockDate . ' 的 ' . $marks . '申请');
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '操作成功！']);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
    // 打卡地址查验
    public function addressSearch()
    {
        if (!$this->myservice->verifyRight('CLOCKIN_CHECK')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/Clockin/addressSearch 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
<<<<<<< HEAD
        $latitude = (float)$this->request->getGetPost('latitude');
        $longitude = (float)$this->request->getGetPost('longitude');
=======
        $latitude = $this->request->getGetPost('latitude');
        $longitude = $this->request->getGetPost('longitude');
>>>>>>> 151b415 (更新语言最新版本+最新底层框架后全部差异化代码适配重写)
        $address = address_search($latitude, $longitude);
        if ($address) {
            return $this->response->setJSON(['status' => 200, 'msg' => '查询成功！', 'data' => $address]);
        } else {
            return $this->response->setJSON(['status' => -1, 'msg' => '查询失败！']);
        }
    }
}
