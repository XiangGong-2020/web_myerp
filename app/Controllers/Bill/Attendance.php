<?php

namespace App\Controllers\Bill;
use App\Controllers\BaseController;

class Attendance extends BaseController
{
    // 列表
    public function list()
    {
        if (!$this->myservice->verifyRight('ATTENDANCE_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/Attendance/list 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $page = max(intval($this->request->getGetPost('page')), 1);
        $rows = max(intval($this->request->getGetPost('rows')), 100);
        $sidx = $this->request->getGetPost('sidx');
        $sord = strtoupper((string)$this->request->getGetPost('sord')) === 'ASC' ? 'ASC' : 'DESC';
        $builder = $this->db->table('bill_attendance');
        $builder->where('isDelete', 0);
        $allowedSort = ['id','billNo','beginDate','endDate','uName','createTime','modifyTime','isDraft'];
        $orderField  = $sidx ? (in_array($sidx, $allowedSort, true) ? $sidx : 'id') : 'id';
        $list = (clone $builder)->orderBy($orderField, $sord)->limit($rows)->offset($rows * ($page - 1))->get()->getResultArray();
        $total = $builder->countAllResults();
        $v = [];
        foreach ($list as $arr => $row) {
            $v[] = [
                'id' => intval($row['id']),
                'billNo' => $row['billNo'],
                'beginDate' => $row['beginDate'],
                'endDate' => $row['endDate'],
                'description' => $row['description'],
                'uId' => intval($row['uId']),
                'uName' => $row['uName'],
                'createTime' => $row['createTime'],
                'modifyTime' => $row['modifyTime'],
                'isDraft' => intval($row['isDraft'])
            ];
        }
        $json['status'] = 200;
        $json['msg'] = 'success';
        $json['data']['page'] = $page;
        $json['data']['records'] = $total;
        $json['data']['total'] = ceil($json['data']['records'] / $rows);
        $json['data']['rows'] = $v;
        return $this->response->setJSON($json);
    }
    // 查询
    public function query()
    {
        if (!$this->myservice->verifyRight('ATTENDANCE_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/Attendance/query 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $data = $this->db->table('bill_attendance')->where(['id' => $id, 'isDelete' => 0])->get()->getRowArray();
        if ($data) {
            $info['id'] = intval($data['id']);
            $info['billNo'] = $data['billNo'];
            $info['beginDate'] = $data['beginDate'];
            $info['endDate'] = $data['endDate'];
            $info['mustDayNum'] = (float)$data['mustDayNum'];
            $info['description'] = $data['description'];
            $info['uId'] = intval($data['uId']);
            $info['uName'] = $data['uName'];
            $info['createTime'] = $data['createTime'];
            $info['modifyTime'] = $data['modifyTime'];
            $info['isDraft'] = intval($data['isDraft']);
            $info['fillClockinTimes'] = intval(((array)json_decode(
                $this->db->table('option')
                    ->select('optionValue')
                    ->where(['optionName' => 'system'])
                    ->get()
                    ->getRowArray()['optionValue']
                ))['fillClockinTimes'] ?? 5);
            $list = $this->db->table('bill_attendance_info')
                ->where(['isDelete' => 0, 'iid' => $id])
                ->orderBy('department', 'DESC')
                ->orderBy('position', 'ASC')
                ->orderBy('uId', 'ASC')
                ->get()
                ->getResultArray();
            $totalDutyDays = 0;
            $v = [];
            foreach ($list as $arr => $row) {
                $v[$arr]['id'] = intval($row['id']);
                $v[$arr]['uId'] = intval($row['uId']);
                $v[$arr]['uName'] = $row['uName'];
                $v[$arr]['department'] = $row['department'];
                $v[$arr]['position'] = $row['position'];
                $v[$arr]['workHours'] = (float)$row['workHours'];
                $v[$arr]['fillHours'] = (float)$row['fillHours'];
                $v[$arr]['overtimeHours'] = (float)$row['overtimeHours'];
                $v[$arr]['recordHours'] = (float)$row['recordHours'];
                $v[$arr]['dutyTimes'] = intval($row['dutyTimes']);
                $totalDutyDays += $v[$arr]['dutyTimes'];
                $v[$arr]['fillClockinTimes'] = intval($row['fillClockinTimes']);
                $v[$arr]['lateTimes'] = intval($row['lateTimes']);
                $v[$arr]['leaveearlyTimes'] = intval($row['leaveearlyTimes']);
                $v[$arr]['clockoffDays'] = (float)$row['clockoffDays'];
                $v[$arr]['clockadjRestDays'] = (float)$row['clockadjRestDays'];
                $v[$arr]['fillRestDays'] = (float)$row['fillRestDays'];
                $v[$arr]['travelDays'] = (float)$row['travelDays'];
                $v[$arr]['clockadjWorkDays'] = (float)$row['clockadjWorkDays'];
                $v[$arr]['fillDays'] = (float)$row['fillDays'];
                $v[$arr]['workDays'] = (float)$row['workDays'];
                $v[$arr]['mustDayNum'] = (float)$row['mustDayNum'];
                $v[$arr]['isFull'] = intval($row['isFull']);
                $v[$arr]['deSocialSecurityDayNum'] = (float)$row['deSocialSecurityDayNum'];
                $v[$arr]['description'] = $row['description'];
                $v[$arr]['modifyInfo'] = $row['modifyInfo'];
            }
            $info['entries'] = $v;
            if ($totalDutyDays != intval(date('t', strtotime($info['beginDate'])))) {
                $info['Warning'] = '值班总次数与当月总天数不符，请核对值班信息。';
            }
            $json['status'] = 200;
            $json['msg'] = 'success';
            $json['data'] = $info;
            return $this->response->setJSON($json);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
    // 编辑
    public function update()
    {
        if (!$this->myservice->verifyRight('ATTENDANCE_ADVANCE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/Attendance/update 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $data = $this->request->getGetPost('postData');
        if (strlen($data) > 0) {
            $data = $this->validform((array)json_decode($data, true));
            // 如果返回的是 Response 对象，直接返回它
            if ($data instanceof \CodeIgniter\HTTP\Response) {
                return $data;
            }
            $oldData = $this->db->table('bill_attendance')->where('id', $data['id'])->where('isDelete', 0)->get()->getRowArray();
            $info = array_intersect_key($data, array_flip(['description', 'modifyTime', 'isDraft']));
            $this->db->transStart();
            $this->db->table('bill_attendance')->where('id', $data['id'])->update($info);
<<<<<<< HEAD
            $this->attendance_info(intval($data['id']), $data);
=======
            $this->attendance_info($data);
>>>>>>> 151b415 (更新语言最新版本+最新底层框架后全部差异化代码适配重写)
            $this->myservice->log('编辑了出勤月表单' . ($data['isDraft'] == 1 ? '草稿' : '') . (($oldData['isDraft'] == 1 && $data['isDraft'] == 0) ? '，并将草稿转为了正式' : '') . '，单号：' . $oldData['billNo']);
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
        if (!$this->myservice->verifyRight('ATTENDANCE_ADVANCE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/Attendance/delete 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $data = $this->db->table('bill_attendance')->where('id', $id)->where('isDelete', 0)->get()->getRowArray();
        if ($data) {
            if (!$this->myservice->verifyRight('SYS_ADMIN') && $data['uId'] != $this->myerp['uId']) {
                return $this->response->setJSON(['status' => -1, 'msg' => '不能删除别人生成的出勤月表单！仅本人或管理员可删除！']);
            }
            $this->db->transStart();
            $this->db->table('bill_attendance')->where('id', $id)->update(['isDelete' => 1]);
            $this->db->table('bill_attendance_info')->where('iid', $id)->update(['isDelete' => 1]);
            $this->myservice->log('删除了出勤月表单' . ($data['isDraft'] == 1 ? '草稿' : '') . '，单号：' . $data['billNo']);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '删除成功！']);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
    // 更新备注
    public function updatedes()
    {
        if (!$this->myservice->verifyRight('ATTENDANCE_ADVANCE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/Attendance/updatedes 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $newDes = $this->request->getGetPost('newDes');
        $data = $this->db->table('bill_attendance')->where('id', $id)->where('isDelete', 0)->get()->getRowArray();
        if ($data) {
            $v['description'] = $newDes;
            $v['modifyTime'] = date("Y-m-d H:i:s");
            $this->db->transStart();
            $this->db->table('bill_attendance')->where('id', $id)->update($v);
            $this->myservice->log('单独更新了出勤月表单备注，单号' . $data['billNo']);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '更新成功！', 'data' => ['id' => $id]]);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
    // info数据
    private function attendance_info(int $iid, array $data)
    {
        foreach ($data['entries'] as $arr => $row) {
            $oldInfo = $this->db->table('bill_attendance_info')->where('id', $row['id'])->get()->getRowArray();
            $v[$arr]['id'] = intval($row['id']);
            $v[$arr]['iid'] = intval($iid);
            $v[$arr]['mustDayNum'] = (float)$row['mustDayNum'];
            $v[$arr]['isFull'] = intval($row['isFull']);
            $v[$arr]['deSocialSecurityDayNum'] = (float)$row['deSocialSecurityDayNum'];
            $v[$arr]['description'] = $row['description'];
            if (
                (float)($oldInfo['mustDayNum'] ?? 0) != $v[$arr]['mustDayNum']
                || (int)($oldInfo['isFull'] ?? 0) != $v[$arr]['isFull']
                || (float)($oldInfo['deSocialSecurityDayNum'] ?? 0) != $v[$arr]['deSocialSecurityDayNum']
                || ($oldInfo['description'] ?? '') != $v[$arr]['description']
            ) {
                $v[$arr]['modifyInfo'] = $this->myerp['uName'] . ' 于 ' . date('Y-m-d H:i:s') . ' 修改';
            } else {
                $v[$arr]['modifyInfo'] = $oldInfo['modifyInfo'] ?? '';
            }
        }
        if (isset($v)) {
<<<<<<< HEAD
            $this->db->table('bill_attendance_info')->updateBatch($v, ['id', 'iid']);
=======
            $this->db->table('bill_attendance_info')->updateBatch($v, 'id');
>>>>>>> 151b415 (更新语言最新版本+最新底层框架后全部差异化代码适配重写)
        }
    }
    // 验证
    private function validform(array $data)
    {
        $data['modifyTime'] = date('Y-m-d H:i:s');
        $data['isDraft'] = isset($data['isDraft']) ? intval($data['isDraft']) : 0;
        return $data;
    }

    // 一键生成上月出勤月表
    public function createLastMonthAttendance()
    {
        if (!$this->myservice->verifyRight('ATTENDANCE_ADVANCE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/Attendance/createLastMonthAttendance 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $endDateWeek = intval($this->request->getGetPost('endDateWeek'));
        $beginDate = date('Y-m-01', strtotime(date('Y-m-01') . ' -1 month'));
        $endDate = date('Y-m-t', strtotime(date('Y-m-01') . ' -1 month'));
        $systemMustDayNum = $this->db->table('attendance_plan')->where(['year' => intval(date('Y', strtotime(date('Y-m-01') . ' -1 month'))), 'isDelete' => 0])->get()->getRowArray()['m' . intval(date('m', strtotime(date('Y-m-01') . ' -1 month')))] ?? 26; // 兜底26天
        $systemFillClockinTimes = intval(((array)json_decode($this->db->table('option')->where(['optionName' => 'system'])->get()->getRowArray()['optionValue'] ?? '', true))['fillClockinTimes'] ?? 5);
        if ($this->db->table('bill_attendance')->where(['beginDate' => $beginDate, 'isDelete' => 0])->countAllResults() > 0) {
            return $this->response->setJSON(['status' => -1, 'msg' => '已生成过上月出勤月表！']);
        } elseif (
            $this->db->table('clockin')
                ->where('clockDate >=', $beginDate)
                ->where('clockDate <=', $endDate)
                ->groupStart()
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
                ->groupEnd()
                ->where('isDelete', 0)
                ->countAllResults() > 0
        ) {
            return $this->response->setJSON(['status' => -1, 'msg' => '请先核实补录上月考勤打卡中的漏打卡信息']);
        } elseif (
            $this->db->table('work_hour')
                ->where('workDate >=', $beginDate)
                ->where('workDate <=', $endDate)
                ->where('workHours >', 0)
                ->where('isCheck', 0)
                ->where('isDelete', 0)
                ->countAllResults() > 0
        ) {
            return $this->response->setJSON(['status' => -1, 'msg' => '请先审批上月补班申报、加班申报、计时申报中的未审批项']);
        } elseif (!$systemMustDayNum) {
            return $this->response->setJSON(['status' => -1, 'msg' => '请先规划上月应出勤天数！']);
        } else {
            $uNames = $this->db->table('clockin')
                ->select('uName')
                ->where('clockDate >=', $beginDate)
                ->where('clockDate <=', $endDate)
                ->where('isDelete', 0)
                ->groupBy('uName')
                ->get()
                ->getResultArray();
            if (!empty($uNames)) {
                $this->db->transStart();
                // 先生成主表
                $data['billNo'] = 'CQ' . date('Ym', strtotime(date('Y-m-01') . ' -1 month'));
                $data['beginDate'] = $beginDate;
                $data['endDate'] = $endDate;
                $data['mustDayNum'] = (float)$systemMustDayNum;
                $data['description'] = '';
                $data['uId'] = $this->myerp['uId'];
                $data['uName'] = $this->myerp['uName'];
                $data['createTime'] = $data['modifyTime'] = date('Y-m-d H:i:s');
                $data['isDraft'] = 1;
                $this->db->table('bill_attendance')->insert($data);
                $iid = $this->db->insertID();
                $totalDutyDays = 0;
                // 再生成明细表
                $v =[];
                foreach ($uNames as $index => $uName) {
                    $user = $this->db->table('user')->where('name', $uName['uName'])->get()->getRowArray();
                    $workDays = $workHours = $fillHours = $overtimeHours = $recordHours = $dutyTimes = $fillClockinTimes = $lateTimes = $leaveearlyTimes = $clockoffDays = $clockadjRestDays = $fillRestDays = $travelDays = $clockadjWorkDays = $fillDays = $isFull = $deSocialSecurityDayNum = 0;
                    $description = '';
                    $totalWorkinSeconds = 0;
                    $list = $this->db->table('clockin')
                        ->where('clockDate >=', $beginDate)
                        ->where('clockDate <=', $endDate)
                        ->where('isDelete', 0)
                        ->where('uName', $user['name'])
                        ->get()
                        ->getResultArray();
                    foreach ($list as $arr => $row) {
                        // 计算上班的秒数
                        $workinSeconds = $workinHours = $workoutHours = $workinDays = $workoutDays = 0;
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
                        // 如果是请年假导致的上班时间缺失，要补上扣掉的上班时长
                        if (strpos($row['marks'], '请假') !== false && strpos($row['description'], '年假') !== false && in_array($user['position'], ['运营总监', '市场总监', '技术总监', '行政总监'])) {
                            $workinSeconds = 9 * 3600; // 请年假当天的上班时长直接算作9小时
                            strpos($description, '有请年假') === false && $description .= '有请年假';
                        }
                        // 处理当天结果数据，转换秒数为小时、天数的时候，保留5位小数，避免后续计算中出现过多的误差
                        $totalWorkinSeconds += $workinSeconds;
                        $workinHours = round($workinSeconds / 3600, 5);
                        $workoutHours = max(round(9 - $workinHours, 5), 0);
                        $workinDays = round($workinHours / 9, 5);
                        $workoutDays = round($workoutHours / 9, 5);
                        strstr($row['marks'], '补卡') && $fillClockinTimes += 1;
                        strstr($row['marks'], '迟到') && $lateTimes += 1;
                        strstr($row['marks'], '早退') && $leaveearlyTimes += 1;
                        strstr($row['marks'], '请假') && $clockoffDays += $workoutDays;
                        strstr($row['marks'], '调休') && $clockadjRestDays += $workoutDays;
                        strstr($row['marks'], '补休') && $fillRestDays += $workoutDays;
                        strstr($row['marks'], '外勤') && $travelDays += $workinDays;
                        strstr($row['marks'], '调班') && $clockadjWorkDays += $workoutDays;
                        strstr($row['marks'], '补班') && $fillDays += $workinDays;
                        if (strstr($row['marks'], '值班')) {
                            if ($row['pmClock'] >= '16:40:00' && $row['pmClock'] < '17:50:00') {
                                // 当天16:40点到18点打了下班卡且选择了值班标记，则属于正常值班，值班天数直接判定 +1
                                $dutyTimes += 1;
                                $totalDutyDays += 1;
                            } elseif ($row['amClock'] == '00:00:00' && $row['pmClock'] == '00:00:00') {
                                // 当天没有打卡，但是有值班标记，则属于休息人员值班，值班天数直接判定 +1
                                $dutyTimes += 1;
                                $totalDutyDays += 1;
                            }
                            // 特殊情况 值班缺失
                            if ($row['amClock'] >= '08:05:00' && $row['amClock'] <= '09:05:00') {
                                // 当天8点5分到9点5分打了上班卡且选择了值班标记，且前一天没有有效的值班，则判定为前一天值班了，值班天数 +1
                                $lastday = date('Y-m-d', strtotime($row['clockDate'] . ' -1 day'));
                                if (
                                    $this->db->table('clockin')
                                        ->where('clockDate', $lastday)
<<<<<<< HEAD
                                        ->where('FIND_IN_SET("值班", marks) >', 0, false)
=======
                                        ->where("FIND_IN_SET('值班', marks) >", 0, false)
>>>>>>> 151b415 (更新语言最新版本+最新底层框架后全部差异化代码适配重写)
                                        ->groupStart()
                                            ->groupStart()
                                                ->where('pmClock >=', '16:40:00')
                                                ->where('pmClock <', '17:50:00')
                                            ->groupEnd()
                                            ->orGroupStart()
                                                ->where('amClock', '00:00:00')
                                                ->where('pmClock', '00:00:00')
                                            ->groupEnd()
                                        ->groupEnd()
                                        ->where('isDelete', 0)
                                        ->countAllResults() == 0
                                ) {
                                    $dutyTimes += 1;
                                    $totalDutyDays += 1;
                                }
                            }
                        }
                    }
                    // 获取补班、加班、计时小时数
                    $fillHours = floatval($this->db->table('work_hour')
                        ->selectSum('workHours')
                        ->where('workDate >=', $beginDate)
                        ->where('workDate <=', $endDate)
                        ->where('uName', $user['name'])
                        ->where('type', 0)
                        ->where('isCheck', 1)
                        ->where('isDelete', 0)
                        ->get()
                        ->getRowArray()['workHours'] ?? 0);
                    $overtimeHours = floatval($this->db->table('work_hour')
                        ->selectSum('workHours')
                        ->where('workDate >=', $beginDate)
                        ->where('workDate <=', $endDate)
                        ->where('uName', $user['name'])
                        ->where('type', 1)
                        ->where('isCheck', 1)
                        ->where('isDelete', 0)
                        ->get()
                        ->getRowArray()['workHours'] ?? 0);
                    $recordHours = floatval($this->db->table('work_hour')
                        ->selectSum('workHours')
                        ->where('workDate >=', $beginDate)
                        ->where('workDate <=', $endDate)
                        ->where('uName', $user['name'])
                        ->where('type', 2)
                        ->where('isCheck', 1)
                        ->where('isDelete', 0)
                        ->get()
                        ->getRowArray()['workHours'] ?? 0);
                    // 获取应出勤天数
                    $mustDayNum = $systemMustDayNum;
                    // 再计算出勤小时数、天数
                    $workHours = round($totalWorkinSeconds / 3600, 5);
                    $workDays = floor(($workHours + $fillHours) / 9 * 10) / 10;
                    // 如果出勤天数小于应出勤天数，且有加班，自动将加班时间算为补班，补足应出勤天数之后剩余的加班时间才算作加班
                    if (($mustDayNum * 9 - ($workHours + $fillHours)) > 0 && $overtimeHours > 0) {
                        $needFillHours = round(($mustDayNum * 9 - ($workHours + $fillHours)), 1);
                        if ($overtimeHours >= $needFillHours) {
                            $description .= $needFillHours > 0 ? (($description == '' ? '' : ',') . $needFillHours . '小时加班已自动转为补班') : '';
                            $overtimeHours = $overtimeHours - $needFillHours;
                            $fillHours = $fillHours + $needFillHours;
                            $workDays = $mustDayNum;
                        } else {
                            $description .= round($overtimeHours, 1) > 0 ? (($description == '' ? '' : ',') . round($overtimeHours, 1) . '小时加班已自动转为补班') : '';
                            $fillHours = $fillHours + $overtimeHours;
                            $workDays = floor(($workHours + $fillHours) / 9 * 10) / 10;
                            $overtimeHours = 0;
                        }
                    }
                    // 开始计算全勤
                    if ($mustDayNum > 0 && $endDateWeek > 0) {
                        if ($fillClockinTimes <= $systemFillClockinTimes) { // 补卡次数未超限
                            if ($workDays >= $mustDayNum) { // 出勤天数 >= 应出勤天数，算全勤
                                $isFull = 1;
                            } else {
                                if ($mustDayNum - $workDays < 1.2) { // 应出勤天数 - 出勤天数 < 1.2天
                                    if (($mustDayNum * 9 * 60 * 60) - ($totalWorkinSeconds + ($fillHours * 60 * 60)) <= 600) { // 月缺勤时间小于10分钟，算全勤
                                        $isFull = 1;
                                    } elseif ($endDateWeek == 6) {
                                        // 如果月末刚好是星期六，查询下月1号星期天是否正常上满一天班
                                        if (
                                            $this->db->table('clockin')
                                                ->where('clockDate', date('Y-m-01', strtotime($beginDate . ' +1 month')))
                                                ->where('uName', $user['name'])
                                                ->where('amClock >', '00:00:00')
                                                ->groupStart()
                                                    ->groupStart()
                                                        ->where('amClock <=', '08:10:00')
                                                        ->where('pmClock >= ', '18:00:00')
                                                    ->groupEnd()
                                                    ->orGroupStart()
                                                        ->where('FIND_IN_SET("值班", marks) >', 0, false)
                                                        ->where('amClock <=', '09:10:00')
                                                        ->where('pmClock >= ', '16:40:00')
                                                    ->groupEnd()
                                                ->groupEnd()
                                                ->where('isDelete', 0)
                                                ->countAllResults() > 0
                                        ) {
                                            if (($mustDayNum * 9 * 60 * 60) - ($totalWorkinSeconds + ($fillHours * 60 * 60) + (9 * 60 * 60)) <= 600) { // 月缺勤时间小于10分钟，算全勤
                                                $isFull = 1;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                    // 如果是全勤，但工作天数小于应出勤天数，且工作天数小数位是9，则向上取整为整数天。
                    if ($isFull == 1 && $workDays < $mustDayNum && strpos(strval($workDays), '.') !== false && explode('.', strval($workDays))[1] == '9') {
                        $workDays = ceil($workDays);
                    }
                    // 开始计算缺勤扣社保天数，已买社保且缺勤大于0.5天且不是全勤才计算扣社保天数。
                    if (
                        (
                            ($user['isBuySocialSecurity'] == 1 && $user['buySocialSecurityDate'] <= $endDate) ||
                            ($user['isBuySocialSecurity'] == 2 && $user['stopSocialSecurityDate'] > $endDate)
                        ) && ($mustDayNum - $workDays > 0.5) && $isFull == 0
                    ) {
                        if ($endDateWeek == 6) {
                            // 如果月末刚好是星期六，查询下月1号星期天是否正常上班
                            if (
                                $this->db->table('clockin')
                                    ->where('clockDate', date('Y-m-01', strtotime($beginDate . ' +1 month')))
                                    ->where('uName', $user['name'])
                                    ->where('amClock >', '00:00:00')
                                    ->groupStart()
                                        ->groupStart()
                                            ->where('amClock <=', '08:10:00')
                                            ->where('pmClock >=', '18:00:00')
                                        ->groupEnd()
                                        ->orGroupStart()
                                            ->where('FIND_IN_SET("值班",marks) >', 0, false)
                                            ->where('amClock <=', '09:10:00')
                                            ->where('pmClock >=', '16:40:00')
                                        ->groupEnd()
                                    ->groupEnd()
                                    ->where('isDelete', 0)
                                    ->countAllResults() > 0
                            ) {
                                $deSocialSecurityDayNum = max(0, $mustDayNum - $workDays - 1);
                            }
                        } else {
                            $deSocialSecurityDayNum = max(0.5, $mustDayNum - $workDays);
                        }
                    }
                    // 最后再四舍五入保留1位小数，免得前面的运算损失精度
                    $workHours = round($workHours, 1);
                    $clockoffDays = round($clockoffDays, 1);
                    $clockadjRestDays = round($clockadjRestDays, 1);
                    $fillRestDays = round($fillRestDays, 1);
                    $travelDays = round($travelDays, 1);
                    $clockadjWorkDays = round($clockadjWorkDays, 1);
                    $fillDays = round($fillDays, 1);
                    // 组装info数据
                    $v[$index]['iid'] = $iid;
                    $v[$index]['billNo'] = $data['billNo'];
                    $v[$index]['beginDate'] = $data['beginDate'];
                    $v[$index]['endDate'] = $data['endDate'];
                    $v[$index]['uId'] = intval($user['id']);
                    $v[$index]['uName'] = $user['name'];
                    $v[$index]['department'] = $user['department'];
                    $v[$index]['position'] = $user['position'];
                    $v[$index]['workHours'] = $workHours;
                    $v[$index]['fillHours'] = $fillHours;
                    $v[$index]['overtimeHours'] = $overtimeHours;
                    $v[$index]['recordHours'] = $recordHours;
                    $v[$index]['dutyTimes'] = $dutyTimes;
                    $v[$index]['fillClockinTimes'] = $fillClockinTimes;
                    $v[$index]['lateTimes'] = $lateTimes;
                    $v[$index]['leaveearlyTimes'] = $leaveearlyTimes;
                    $v[$index]['clockoffDays'] = $clockoffDays;
                    $v[$index]['clockadjRestDays'] = $clockadjRestDays;
                    $v[$index]['fillRestDays'] = $fillRestDays;
                    $v[$index]['travelDays'] = $travelDays;
                    $v[$index]['clockadjWorkDays'] = $clockadjWorkDays;
                    $v[$index]['fillDays'] = $fillDays;
                    $v[$index]['workDays'] = $workDays;
                    $v[$index]['mustDayNum'] = $mustDayNum;
                    $v[$index]['isFull'] = $isFull;
                    $v[$index]['deSocialSecurityDayNum'] = $deSocialSecurityDayNum;
                    $v[$index]['description'] = $description;
                }
                $sort0 = array_column($v, 'uId');
                array_multisort($sort0, SORT_ASC, $v);
                $this->db->table('bill_attendance_info')->insertBatch($v);
                $this->myservice->log('一键生成了出勤月表单，单号：' . $data['billNo']);
                $this->db->transComplete();
                if ($this->db->transStatus() === false) {
                    return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
                }
                return $this->response->setJSON(['status' => 200, 'msg' => '生成成功！', 'data' => ['Warning' => intval(date('t', strtotime($beginDate))) != $totalDutyDays ? '但值班总次数与当月总天数不符，请核对值班信息。' : '']]);
            }
            return $this->response->setJSON(['status' => -1, 'msg' => '缺少员工数据！']);
        }
    }
    // 一键重算上月出勤月表
    public function redoLastMonthAttendance()
    {
        if (!$this->myservice->verifyRight('ATTENDANCE_ADVANCE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/Attendance/redoLastMonthAttendance 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $endDateWeek = intval($this->request->getGetPost('endDateWeek'));
        $beginDate = date('Y-m-01', strtotime(date('Y-m-01') . ' -1 month'));
        $endDate = date('Y-m-t', strtotime(date('Y-m-01') . ' -1 month'));
        $systemMustDayNum = floatval($this->db->table('attendance_plan')->select('m' . intval(date('m', strtotime(date('Y-m-01') . ' -1 month'))))->where(['year' => intval(date('Y', strtotime(date('Y-m-01') . ' -1 month'))), 'isDelete' => 0])->get()->getRowArray()['m' . intval(date('m', strtotime(date('Y-m-01') . ' -1 month')))] ?? 26); // 兜底26天
        $systemFillClockinTimes = intval(((array)json_decode($this->db->table('option')->select('optionValue')->where(['optionName' => 'system'])->get()->getRowArray()['optionValue'] ?? '', true))['fillClockinTimes'] ?? 5);
        $data = $this->db->table('bill_attendance')->select('id, billNo')->where('beginDate', $beginDate)->where('isDelete', 0)->get()->getRowArray();
        if ($data) {
            if (
                $this->db->table('clockin')
                    ->where('clockDate >=', $beginDate)
                    ->where('clockDate <=', $endDate)
                    ->where('isDelete', 0)
                        ->groupStart()
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
                        ->groupEnd()
                        ->countAllResults() > 0
            ) {
                return $this->response->setJSON(['status' => -1, 'msg' => '请先核实补录上月考勤打卡中的漏打卡信息']);
            } elseif (
                $this->db->table('work_hour')
                    ->where('workDate >=', $beginDate)
                    ->where('workDate <=', $endDate)
                    ->where('workHours >', 0)
                    ->where('isCheck', 0)
                    ->where('isDelete', 0)
                    ->countAllResults() > 0
            ) {
                return $this->response->setJSON(['status' => -1, 'msg' => '请先审批上月补班申报、加班申报、计时申报中的未审批项']);
            } elseif (!$systemMustDayNum) {
                return $this->response->setJSON(['status' => -1, 'msg' => '请先规划上月应出勤天数！']);
            } else {
                $totalDutyDays = 0;
                $uNames = $this->db->table('clockin')
                    ->select('uName')
                    ->where('clockDate >=', $beginDate)
                    ->where('clockDate <=', $endDate)
                    ->where('isDelete', 0)
                    ->groupBy('uName')
                    ->get()
                    ->getResultArray();
                if (!empty($uNames)) {
                    $this->db->transStart();
                    foreach ($uNames as $index => $uName) {
                        $user = $this->db->table('user')->where('name', $uName['uName'])->get()->getRowArray();
                        $workDays = $workHours = $fillHours = $overtimeHours = $recordHours = $dutyTimes = $fillClockinTimes = $lateTimes = $leaveearlyTimes = $clockoffDays = $clockadjRestDays = $fillRestDays = $travelDays = $clockadjWorkDays = $fillDays = $workDays = $isFull = $deSocialSecurityDayNum = 0;
                        $description = '';
                        $totalWorkinSeconds = 0;
                        $list = $this->db->table('clockin')
                            ->where('clockDate >=', $beginDate)
                            ->where('clockDate <=', $endDate)
                            ->where('isDelete', 0)
                            ->where('uName', $user['name'])
                            ->get()
                            ->getResultArray();
                        foreach ($list as $arr => $row) {
                            // 计算上班的秒数
                            $workinSeconds = $workinHours = $workoutHours = $workinDays = $workoutDays = 0;
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
                                strpos($description, '有请年假') === false && $description .= '有请年假';
                            }
                            // 处理当天结果数据
                            $totalWorkinSeconds += $workinSeconds;
                            $workinHours = round($workinSeconds / 3600, 5);
                            $workoutHours = max(round(9 - $workinHours, 5), 0);
                            $workinDays = round($workinHours / 9, 5);
                            $workoutDays = round($workoutHours / 9, 5);
                            strstr($row['marks'], '补卡') && $fillClockinTimes += 1;
                            strstr($row['marks'], '迟到') && $lateTimes += 1;
                            strstr($row['marks'], '早退') && $leaveearlyTimes += 1;
                            strstr($row['marks'], '请假') && $clockoffDays += $workoutDays;
                            strstr($row['marks'], '调休') && $clockadjRestDays += $workoutDays;
                            strstr($row['marks'], '补休') && $fillRestDays += $workoutDays;
                            strstr($row['marks'], '外勤') && $travelDays += $workinDays;
                            strstr($row['marks'], '调班') && $clockadjWorkDays += $workoutDays;
                            strstr($row['marks'], '补班') && $fillDays += $workinDays;
                            if (strstr($row['marks'], '值班')) {
                                if ($row['pmClock'] >= '16:40:00' && $row['pmClock'] < '17:50:00') {
                                    // 当天16:40点到18点打了下班卡且选择了值班标记，则属于正常值班，值班天数直接判定 +1
                                    $dutyTimes += 1;
                                    $totalDutyDays += 1;
                                } elseif ($row['amClock'] == '00:00:00' && $row['pmClock'] == '00:00:00') {
                                    // 当天没有打卡，但是有值班标记，则属于休息人员值班，值班天数直接判定 +1
                                    $dutyTimes += 1;
                                    $totalDutyDays += 1;
                                }
                                // 特殊情况 值班缺失
                                if ($row['amClock'] >= '08:05:00' && $row['amClock'] <= '09:05:00') {
                                    // 当天8点5分到9点5分打了上班卡且选择了值班标记，且前一天没有有效的值班，则判定为前一天值班了，值班天数 +1
                                    $lastday = date('Y-m-d', strtotime($row['clockDate'] . ' -1 day'));
                                    if (
                                        $this->db->table('clockin')
                                            ->where('clockDate =', $lastday)
                                            ->where('FIND_IN_SET("值班", marks) >', 0, false)
                                            ->groupStart()
                                                ->groupStart()
                                                    ->where('pmClock >=', '16:40:00')
                                                    ->where('pmClock <', '17:50:00')
                                                ->groupEnd()
                                                ->orGroupStart()
                                                    ->where('amClock', '00:00:00')
                                                    ->where('pmClock', '00:00:00')
                                                ->groupEnd()
                                            ->groupEnd()
                                            ->where('isDelete', 0)
                                            ->countAllResults() == 0
                                    ) {
                                        $dutyTimes += 1;
                                        $totalDutyDays += 1;
                                    }
                                }
                            }
                        }
                        // 获取补班、加班、计时小时数
                        $fillHours = floatval($this->db->table('work_hour')
                            ->selectSum('workHours')
                            ->where('workDate >=', $beginDate)
                            ->where('workDate <=', $endDate)
                            ->where('uName', $user['name'])
                            ->where('type', 0)
                            ->where('isCheck', 1)
                            ->where('isDelete', 0)
                            ->get()
                            ->getRowArray()['workHours'] ?? 0);
                        $overtimeHours = floatval($this->db->table('work_hour')
                            ->selectSum('workHours')
                            ->where('workDate >=', $beginDate)
                            ->where('workDate <=', $endDate)
                            ->where('uName', $user['name'])
                            ->where('type', 1)
                            ->where('isCheck', 1)
                            ->where('isDelete', 0)
                            ->get()
                            ->getRowArray()['workHours'] ?? 0);
                        $recordHours = floatval($this->db->table('work_hour')
                            ->selectSum('workHours')
                            ->where('workDate >=', $beginDate)
                            ->where('workDate <=', $endDate)
                            ->where('uName', $user['name'])
                            ->where('type', 2)
                            ->where('isCheck', 1)
                            ->where('isDelete', 0)
                            ->get()
                            ->getRowArray()['workHours'] ?? 0);
                        // 获取应出勤天数
                        $userMustDayNum = floatval($this->db->table('bill_attendance_info')
                            ->select('mustDayNum')
                            ->where('uId', $user['id'])
                            ->where('beginDate', $beginDate)
                            ->where('isDelete', 0)
                            ->get()
                            ->getRowArray()['mustDayNum'] ?? 0);
                        if ($userMustDayNum) {
                            $mustDayNum = $userMustDayNum;
                        } else {
                            $mustDayNum = $systemMustDayNum;
                        }
                        // 再计算出勤小时数、天数
                        $workHours = round($totalWorkinSeconds / 3600, 5);
                        $workDays = floor(($workHours + $fillHours) / 9 * 10) / 10;
                        // 如果出勤天数小于应出勤天数，且有加班，自动将加班时间算为补班，补足应出勤天数之后剩余的加班时间才算作加班
                        if (($mustDayNum * 9 - ($workHours + $fillHours)) > 0 && $overtimeHours > 0) {
                            $needFillHours = round(($mustDayNum * 9 - ($workHours + $fillHours)), 1);
                            if ($overtimeHours >= $needFillHours) {
                                $description = $needFillHours > 0 ? ($description == '' ? '' : ',') . $needFillHours . '小时加班已自动转为补班' : '';
                                $overtimeHours = $overtimeHours - $needFillHours;
                                $fillHours = $fillHours + $needFillHours;
                                $workDays = $mustDayNum;
                            } else {
                                $description = round($overtimeHours, 1) > 0 ? ($description == '' ? '' : ',') . round($overtimeHours, 1) . '小时加班已自动转为补班' : '';
                                $fillHours = $fillHours + $overtimeHours;
                                $workDays = floor(($workHours + $fillHours) / 9 * 10) / 10;
                                $overtimeHours = 0;
                            }
                        }
                        // 开始计算全勤
                        if ($mustDayNum > 0 && $endDateWeek > 0) {
                            if ($fillClockinTimes <= $systemFillClockinTimes) { // 补卡次数未超限
                                if ($workDays >= $mustDayNum) { // 出勤天数 >= 应出勤天数，算全勤
                                    $isFull = 1;
                                } else {
                                    if ($mustDayNum - $workDays < 1.2) { // 应出勤天数 - 出勤天数 < 1.2天
                                        if (($mustDayNum * 9 * 60 * 60) - ($totalWorkinSeconds + ($fillHours * 60 * 60)) <= 600) { // 月缺勤总时间小于10分钟，算全勤
                                            $isFull = 1;
                                        } elseif ($endDateWeek == 6) { // 如果月末刚好是星期六，查询下月1号星期天是否正常上满一天班
                                            if (
                                                $this->db->table('clockin')
                                                    ->where('clockDate =', date('Y-m-01', strtotime($beginDate . ' +1 month')))
                                                    ->where('uName =', $user['name'])
                                                    ->where('amClock >', '00:00:00')
                                                    ->groupStart()
                                                        ->groupStart()
                                                            ->where('amClock <=', '08:10:00')
                                                            ->where('pmClock >=', '18:00:00')
                                                        ->groupEnd()
                                                        ->orGroupStart()
                                                            ->where('FIND_IN_SET("值班", marks) >', 0, false)
                                                            ->where('amClock <=', '09:10:00')
                                                            ->where('pmClock >=', '16:40:00')
                                                        ->groupEnd()
                                                    ->groupEnd()
                                                    ->where('isDelete =', 0)
                                                    ->countAllResults() > 0
                                            ) {
                                                if (($mustDayNum * 9 * 60 * 60) - ($totalWorkinSeconds + ($fillHours * 60 * 60) + (9 * 60 * 60)) <= 600) { // 月缺勤总时间小于10分钟，算全勤
                                                    $isFull = 1;
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                        // 如果是全勤，但工作天数小于应出勤天数，且工作天数小数位是9，则向上取整为整数天。
                        if ($isFull == 1 && $workDays < $mustDayNum && strpos(strval($workDays), '.') !== false && explode('.', strval($workDays))[1] == '9') {
                            $workDays = ceil($workDays);
                        }
                        // 开始计算缺勤扣社保天数，已买社保且缺勤大于0.5天且不是全勤才计算扣社保天数。
                        if (
                            (
                                ($user['isBuySocialSecurity'] == 1 && $user['buySocialSecurityDate'] <= $endDate) ||
                                ($user['isBuySocialSecurity'] == 2 && $user['stopSocialSecurityDate'] > $endDate)
                            ) && ($mustDayNum - $workDays > 0.5) && $isFull == 0
                        ) {
                            if ($endDateWeek == 6) {
                                // 如果月末刚好是星期六，查询下月1号星期天是否正常上班
                                if (
                                    $this->db->table('clockin')
                                        ->where('clockDate =', date('Y-m-01', strtotime($beginDate . ' +1 month')))
                                        ->where('uName =', $user['name'])
                                        ->where('amClock >', '00:00:00')
                                        ->groupStart()
                                            ->groupStart()
                                                ->where('amClock <=', '08:10:00')
                                                ->where('pmClock >=', '18:00:00')
                                            ->groupEnd()
                                            ->orGroupStart()
                                                ->where('FIND_IN_SET("值班", marks) >', 0, false)
                                                ->where('amClock <=', '09:10:00')
                                                ->where('pmClock >=', '16:40:00')
                                            ->groupEnd()
                                        ->groupEnd()
                                        ->where('isDelete =', 0)
                                        ->countAllResults() > 0
                                ) {
                                    $deSocialSecurityDayNum = max(0, $mustDayNum - $workDays - 1);
                                }
                            } else {
                                $deSocialSecurityDayNum = max(0.5, $mustDayNum - $workDays);
                            }
                        }
                        // 最后再四舍五入保留1位小数，免得前面的运算损失精度
                        $workHours = round($workHours, 1);
                        $clockoffDays = round($clockoffDays, 1);
                        $clockadjRestDays = round($clockadjRestDays, 1);
                        $fillRestDays = round($fillRestDays, 1);
                        $travelDays = round($travelDays, 1);
                        $clockadjWorkDays = round($clockadjWorkDays, 1);
                        $fillDays = round($fillDays, 1);
                        // 有则更新 无则插入
                        if ($this->db->table('bill_attendance_info')->where(['uId' => $user['id'], 'uName' => $user['name'], 'beginDate' => $beginDate])->countAllResults() > 0) {
                            $this->db->table('bill_attendance_info')
                                ->where(['uId' => $user['id'], 'uName' => $user['name'], 'beginDate' => $beginDate])
                                ->update([
                                'workHours' => $workHours,
                                'fillHours' => $fillHours,
                                'overtimeHours' => $overtimeHours,
                                'recordHours' => $recordHours,
                                'dutyTimes' => $dutyTimes,
                                'fillClockinTimes' => $fillClockinTimes,
                                'lateTimes' => $lateTimes,
                                'leaveearlyTimes' => $leaveearlyTimes,
                                'clockoffDays' => $clockoffDays,
                                'clockadjRestDays' => $clockadjRestDays,
                                'fillRestDays' => $fillRestDays,
                                'travelDays' => $travelDays,
                                'clockadjWorkDays' => $clockadjWorkDays,
                                'fillDays' => $fillDays,
                                'workDays' => $workDays,
                                'mustDayNum' => $mustDayNum,
                                'isFull' => $isFull,
                                'deSocialSecurityDayNum' => $deSocialSecurityDayNum,
                                'description' => $description
                            ]);
                        } else {
                            $this->db->table('bill_attendance_info')->insert([
                                'iid' => $data['id'],
                                'billNo' => $data['billNo'],
                                'beginDate' => $beginDate,
                                'endDate' => $endDate,
                                'uId' => $user['id'],
                                'uName' => $user['name'],
                                'department' > $user['department'],
                                'position' => $user['position'],
                                'workHours' => $workHours,
                                'fillHours' => $fillHours,
                                'overtimeHours' => $overtimeHours,
                                'recordHours' => $recordHours,
                                'dutyTimes' => $dutyTimes,
                                'fillClockinTimes' => $fillClockinTimes,
                                'lateTimes' => $lateTimes,
                                'leaveearlyTimes' => $leaveearlyTimes,
                                'clockoffDays' => $clockoffDays,
                                'clockadjRestDays' => $clockadjRestDays,
                                'fillRestDays' => $fillRestDays,
                                'travelDays' => $travelDays,
                                'clockadjWorkDays' => $clockadjWorkDays,
                                'fillDays' => $fillDays,
                                'workDays' => $workDays,
                                'mustDayNum' => $mustDayNum,
                                'isFull' => $isFull,
                                'deSocialSecurityDayNum' => $deSocialSecurityDayNum,
                                'description' => $description
                            ]);
                        }
                    }
                    $this->myservice->log('一键重算了出勤月表单，单号：' . $data['billNo']);
                    $this->db->transComplete();
                    if ($this->db->transStatus() === false) {
                        return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
                    }
                    return $this->response->setJSON(['status' => 200, 'msg' => '重算成功！', 'data' => ['Warning' => intval(date('t', strtotime($beginDate))) != $totalDutyDays ? '但值班总次数与当月总天数不符，请核对值班信息。' : '']]);
                }
                return $this->response->setJSON(['status' => -1, 'msg' => '缺少员工数据！']);
            }
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '无上月出勤月表单']);
    }
}
