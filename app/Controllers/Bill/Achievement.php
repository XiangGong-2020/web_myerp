<?php

namespace App\Controllers\Bill;
use App\Controllers\BaseController;

class Achievement extends BaseController
{
    // 列表
    public function list()
    {
        if (!$this->myservice->verifyRight('ACHIEVEMENT_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/Achievement/list 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $page = max(intval($this->request->getGetPost('page')), 1);
        $rows = max(intval($this->request->getGetPost('rows')), 100);
        $sidx = $this->request->getGetPost('sidx');
        $sord = strtoupper((string)$this->request->getGetPost('sord')) === 'ASC' ? 'ASC' : 'DESC';
        $builder = $this->db->table('bill_achievement');
        $builder->where('isDelete', 0);
        $allowedSort = ['id','billNo','beginDate','endDate','description','uName','createTime','isDraft'];
        $orderField  = $sidx ? (in_array($sidx, $allowedSort, true) ? $sidx : 'id') : 'id';
        $list = (clone $builder)->orderBy($orderField, $sord)->limit($rows)->offset($rows * ($page - 1))->get()->getResultArray();
        $total = $builder->countAllResults();
        $v = [];
        foreach ($list as $arr => $row) {
            $v[$arr]['id'] = intval($row['id']);
            $v[$arr]['billNo'] = $row['billNo'];
            $v[$arr]['beginDate'] = $row['beginDate'];
            $v[$arr]['endDate'] = $row['endDate'];
            $v[$arr]['description'] = $row['description'];
            $v[$arr]['uId'] = intval($row['uId']);
            $v[$arr]['uName'] = $row['uName'];
            $v[$arr]['createTime'] = $row['createTime'];
            $v[$arr]['modifyTime'] = $row['modifyTime'];
            $v[$arr]['isDraft'] = intval($row['isDraft']);
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
        if (!$this->myservice->verifyRight('ACHIEVEMENT_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/Achievement/query 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $data = $this->db->table('bill_achievement')->where(['id' => $id, 'isDelete' => 0])->get()->getRowArray();
        if ($data) {
            $info['id'] = intval($data['id']);
            $info['billNo'] = $data['billNo'];
            $info['beginDate'] = $data['beginDate'];
            $info['endDate'] = $data['endDate'];
            $info['description'] = $data['description'];
            $info['uId'] = intval($data['uId']);
            $info['uName'] = $data['uName'];
            $info['createTime'] = $data['createTime'];
            $info['modifyTime'] = $data['modifyTime'];
            $info['isDraft'] = intval($data['isDraft']);
            $list = $this->db->table('bill_achievement_info')->where(['iid' => $id, 'isDelete' => 0])->orderBy('department', 'DESC')->orderBy('position', 'ASC')->orderBy('uId', 'ASC')->get()->getResultArray();
            $v = [];
            foreach ($list as $arr => $row) {
                $v[$arr]['id'] = $row['id'];
                $v[$arr]['uId'] = intval($row['uId']);
                $v[$arr]['uName'] = $row['uName'];
                $v[$arr]['department'] = $row['department'];
                $v[$arr]['position'] = $row['position'];
                $v[$arr]['level'] = $row['level'];
                $v[$arr]['hourlyWage'] = (float)$row['hourlyWage'];
                $v[$arr]['dailyWage'] = round($v[$arr]['hourlyWage'] * 9, 2);
                $v[$arr]['workDays'] = (float)$row['workDays'];
                $v[$arr]['workHours'] = (float)$row['workHours'];
                $v[$arr]['workRate'] = (float)$row['workRate'];
                $v[$arr]['evaluateScore'] = intval($row['evaluateScore']);
                $v[$arr]['isFull'] = intval($row['isFull']);
                $v[$arr]['dutyTimes'] = intval($row['dutyTimes']);
                $v[$arr]['fillHours'] = (float)$row['fillHours'];
                $v[$arr]['overtimeHours'] = (float)$row['overtimeHours'];
                $v[$arr]['recordHours'] = (float)$row['recordHours'];
                $v[$arr]['attendanceSalary'] = intval($row['attendanceSalary']);
                $v[$arr]['evaluateSalary'] = intval($row['evaluateSalary']);
                $v[$arr]['achievementSalary'] = intval($row['achievementSalary']);
                $v[$arr]['dutySalary'] = intval($row['dutySalary']);
                $v[$arr]['overtimeSalary'] = intval($row['overtimeSalary']);
                $v[$arr]['recordSalary'] = intval($row['recordSalary']);
                $v[$arr]['bonusFullAtt'] = intval($row['bonusFullAtt']);
                $v[$arr]['bonusBid'] = intval($row['bonusBid']);
                $v[$arr]['bonusPerson'] = intval($row['bonusPerson']);
                $v[$arr]['deductionSocialSecurity'] = intval($row['deductionSocialSecurity']);
                $v[$arr]['deductionOther'] = intval($row['deductionOther']);
                $v[$arr]['sumSalary'] = intval($row['sumSalary']);
                $v[$arr]['bonusOffSeason'] = intval($row['bonusOffSeason']);
                $v[$arr]['finalSalary'] = (float)$row['finalSalary'];
                $v[$arr]['description'] = $row['description'];
            }
            $info['entries'] = $v;

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
        if (!$this->myservice->verifyRight('ACHIEVEMENT_ADVANCE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/Achievement/update 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $data = $this->request->getGetPost('postData');
        if (strlen($data) > 0) {
            $data = (array)json_decode($data, true);
            if ($data['id'] > 0) {
                $oldData = $this->db->table('bill_achievement')->where(['id' => $data['id'], 'isDelete' => 0])->get()->getRowArray();
                $data['modifyTime'] = date('Y-m-d H:i:s');
                $data['isDraft'] = isset($data['isDraft']) ? intval($data['isDraft']) : 0;
                $info = array_intersect_key($data, array_flip(['description', 'modifyTime', 'isDraft']));
                $this->db->transStart();
                $this->db->table('bill_achievement')->where(['id' => $data['id']])->update($info);
                $this->achievement_info(intval($data['id']), $data);
                $this->myservice->log('编辑了工资月表单' . ($data['isDraft'] == 1 ? '草稿' : '') . (($oldData['isDraft'] == 1 && $data['isDraft'] == 0) ? '，并将草稿转为了正式' : '') . '，单号：' . $oldData['billNo']);
                $this->db->transComplete();
                if ($this->db->transStatus() === false) {
                    return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
                }
                return $this->response->setJSON(['status' => 200, 'msg' => '编辑成功！', 'data' => $data]);
            }
            return $this->response->setJSON(['status' => -1, 'msg' => '提交的是空数据！']);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '提交的是空数据！']);
    }
    // 删除
    public function delete()
    {
        if (!$this->myservice->verifyRight('ACHIEVEMENT_ADVANCE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/Achievement/delete 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $data = $this->db->table('bill_achievement')->where(['id' => $id, 'isDelete' => 0])->get()->getRowArray();
        if ($data) {
            if (!$this->myservice->verifyRight('ACHIEVEMENT_ADMIN') && $data['uId'] != $this->myerp['uId']) {
                return $this->response->setJSON(['status' => -1, 'msg' => '不能删除别人生成的工资月表单！仅本人或管理员可删除！']);
            }
            $this->db->transStart();
            $this->db->table('bill_achievement')->where(['id' => $id])->update(['isDelete' => 1]);
            $this->db->table('bill_achievement_info')->where(['iid' => $id])->update(['isDelete' => 1]);
            $this->myservice->log('删除了工资月表单' . ($data['isDraft'] == 1 ? '草稿' : '') . '，单号：' . $data['billNo']);
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
        if (!$this->myservice->verifyRight('ACHIEVEMENT_ADVANCE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/Achievement/updatedes 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $newDes = $this->request->getGetPost('newDes');
        $data = $this->db->table('bill_achievement')->where(['id' => $id])->get()->getRowArray();
        if ($data) {
            $v['description'] = $newDes;
            $v['modifyTime'] = date("Y-m-d H:i:s");
            $this->db->transStart();
            $this->db->table('bill_achievement')->where(['id' => $id])->update($v);
            $this->myservice->log('单独更新了工资月表单备注，单号：' . $data['billNo']);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '更新成功！', 'data' => ['id' => $id]]);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
    // info数据
    private function achievement_info(int $iid, array $data)
    {
        foreach ($data['entries'] as $arr => $row) {
            $v[$arr]['id'] = intval($row['id']);
<<<<<<< HEAD
            $v[$arr]['iid'] = intval($iid);
=======
>>>>>>> 151b415 (更新语言最新版本+最新底层框架后全部差异化代码适配重写)
            $v[$arr]['bonusOffSeason'] = intval($row['bonusOffSeason']);
            $v[$arr]['finalSalary'] = intval($row['finalSalary']);
            $v[$arr]['description'] = $row['description'];
        }
        if (isset($v)) {
<<<<<<< HEAD
            $this->db->table('bill_achievement_info')->updateBatch($v, ['id', 'iid']);
=======
            $this->db->table('bill_achievement_info')->updateBatch($v, 'id');
>>>>>>> 151b415 (更新语言最新版本+最新底层框架后全部差异化代码适配重写)
        }
    }
    // 导出原单
    public function export()
    {
        if (!$this->myservice->verifyRight('ACHIEVEMENT_ADVANCE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/Achievement/export 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $data = $this->db->table('bill_achievement')->where(['id' => $id, 'isDelete' => 0])->get()->getRowArray();
        $list = $this->db->table('bill_achievement_info')->where(['iid' => $id, 'isDelete' => 0])->orderBy('department DESC, position ASC, uId ASC')->get()->getResultArray();
        $name = '工资月表单_' . $data['billNo'];

        // 调用spreadsheet处理表格
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $activeWorksheet = $spreadsheet->getActiveSheet();

        // A-Z列 设置字符串自动换行(会撑开单元格高度,并破坏设置的默认行高)，设置水平居左+垂直居中
        $activeWorksheet->getStyle("A:AC")->getAlignment()->setWrapText(TRUE);
        $activeWorksheet->getStyle('A:AC')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
        $activeWorksheet->getStyle('A:AC')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

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
        $activeWorksheet->getStyle('A1:AC' . (COUNT($list) + 4))->applyFromArray($styleArray, false);

        // 设置加粗
        $activeWorksheet->getStyle('A1')->getFont()->setBold(true);
        $activeWorksheet->getStyle('A4:AC4')->getFont()->setBold(true);

        // 设置背景色
        $activeWorksheet->getStyle('A4:AC4')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
        $activeWorksheet->getStyle('A4:AC4')->getFill()->getStartColor()->setRGB('F5F5F5');

        // 合并单元格
        $activeWorksheet->mergeCells('A1:AC1');
        $activeWorksheet->mergeCells('B3:AC3');

        // 设置行高
        $activeWorksheet->getRowDimension('1')->setRowHeight(40);
        $activeWorksheet->getRowDimension('3')->setRowHeight(80);

        // 设置水平居中
        $activeWorksheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // 设置每列列宽
        $activeWorksheet->getColumnDimension('A')->setWidth(8);
        $activeWorksheet->getColumnDimension('B')->setWidth(12);
        $activeWorksheet->getColumnDimension('C')->setWidth(8);
        $activeWorksheet->getColumnDimension('D')->setWidth(12);
        $activeWorksheet->getColumnDimension('E')->setWidth(6);
        $activeWorksheet->getColumnDimension('F')->setWidth(6);
        $activeWorksheet->getColumnDimension('G')->setWidth(10);
        $activeWorksheet->getColumnDimension('H')->setWidth(10);
        $activeWorksheet->getColumnDimension('I')->setWidth(10);
        $activeWorksheet->getColumnDimension('J')->setWidth(8);
        $activeWorksheet->getColumnDimension('K')->setWidth(6);
        $activeWorksheet->getColumnDimension('L')->setWidth(10);
        $activeWorksheet->getColumnDimension('M')->setWidth(10);
        $activeWorksheet->getColumnDimension('N')->setWidth(10);
        $activeWorksheet->getColumnDimension('O')->setWidth(10);
        $activeWorksheet->getColumnDimension('P')->setWidth(10);
        $activeWorksheet->getColumnDimension('Q')->setWidth(10);
        $activeWorksheet->getColumnDimension('R')->setWidth(10);
        $activeWorksheet->getColumnDimension('S')->setWidth(10);
        $activeWorksheet->getColumnDimension('T')->setWidth(10);
        $activeWorksheet->getColumnDimension('U')->setWidth(10);
        $activeWorksheet->getColumnDimension('V')->setWidth(10);
        $activeWorksheet->getColumnDimension('W')->setWidth(10);
        $activeWorksheet->getColumnDimension('X')->setWidth(10);
        $activeWorksheet->getColumnDimension('Y')->setWidth(10);
        $activeWorksheet->getColumnDimension('Z')->setWidth(10);
        $activeWorksheet->getColumnDimension('AA')->setWidth(10);
        $activeWorksheet->getColumnDimension('AB')->setWidth(10);
        $activeWorksheet->getColumnDimension('AC')->setWidth(10);

        //设置当前的sheet
        $activeWorksheet->setCellValue('A1', $name . '（导出时间 ' . date('Y-m-d H:i:s') . '）');
        $activeWorksheet->setCellValue('A2', '起始日');
        $activeWorksheet->setCellValue('B2', $data['beginDate']);
        $activeWorksheet->setCellValue('C2', '结束日');
        $activeWorksheet->setCellValue('D2', $data['endDate']);
        $activeWorksheet->setCellValue('A3', '备注');
        $activeWorksheet->setCellValue('B3', $data['description']);

        $activeWorksheet->setCellValue('A4', '工号');
        $activeWorksheet->setCellValue('B4', '姓名');
        $activeWorksheet->setCellValue('C4', '部门');
        $activeWorksheet->setCellValue('D4', '职位');
        $activeWorksheet->setCellValue('E4', '等级');
        $activeWorksheet->setCellValue('F4', '日薪');
        $activeWorksheet->setCellValue('G4', '出勤天数');
        $activeWorksheet->setCellValue('H4', '补班小时');
        $activeWorksheet->setCellValue('I4', '出勤率');
        $activeWorksheet->setCellValue('J4', '考评分');
        $activeWorksheet->setCellValue('K4', '全勤');
        $activeWorksheet->setCellValue('L4', '值班次数');
        $activeWorksheet->setCellValue('M4', '加班小时');
        $activeWorksheet->setCellValue('N4', '计时小时');
        $activeWorksheet->setCellValue('O4', '出勤工资');
        $activeWorksheet->setCellValue('P4', '考评工资');
        $activeWorksheet->setCellValue('Q4', '绩效工资');
        $activeWorksheet->setCellValue('R4', '值班工资');
        $activeWorksheet->setCellValue('S4', '加班工资');
        $activeWorksheet->setCellValue('T4', '计时工资');
        $activeWorksheet->setCellValue('U4', '全勤奖金');
        $activeWorksheet->setCellValue('V4', '投标奖金');
        $activeWorksheet->setCellValue('W4', '岗位津贴');
        $activeWorksheet->setCellValue('X4', '社保扣除');
        $activeWorksheet->setCellValue('Y4', '其他扣除');
        $activeWorksheet->setCellValue('Z4', '汇总工资');
        $activeWorksheet->setCellValue('AA4', '淡季津贴');
        $activeWorksheet->setCellValue('AB4', '实发工资');
        $activeWorksheet->setCellValue('AC4', '备注');

        $i = 5;
        foreach ($list as $k => $v) {
            $activeWorksheet->setCellValue('A' . $i, str_pad($v['uId'], 4, '0', STR_PAD_LEFT)); // 工号，不足4位，左边补零
            $activeWorksheet->setCellValue('B' . $i, $v['uName']);
            $activeWorksheet->setCellValue('C' . $i, $v['department']);
            $activeWorksheet->setCellValue('D' . $i, $v['position']);
            $activeWorksheet->setCellValue('E' . $i, $v['level']);
            $activeWorksheet->setCellValue('F' . $i, $v['hourlyWage'] ? round($v['hourlyWage'] * 9, 2) : '');
            $activeWorksheet->setCellValue('G' . $i, $v['workDays'] ?? '');
            $activeWorksheet->setCellValue('H' . $i, $v['fillHours'] ?? '');
            $activeWorksheet->setCellValue('I' . $i, ((float)$v['workRate'] * 100) . '%');
            $activeWorksheet->setCellValue('J' . $i, $v['evaluateScore'] ?? '');
            $activeWorksheet->setCellValue('K' . $i, $v['isFull'] == 1 ? '是' : '');
            $activeWorksheet->setCellValue('L' . $i, $v['dutyTimes'] ?? '');
            $activeWorksheet->setCellValue('M' . $i, $v['overtimeHours'] ?? '');
            $activeWorksheet->setCellValue('N' . $i, $v['recordHours'] ?? '');
            $activeWorksheet->setCellValue('O' . $i, $v['attendanceSalary'] ?? '');
            $activeWorksheet->setCellValue('P' . $i, $v['evaluateSalary'] ?? '');
            $activeWorksheet->setCellValue('Q' . $i, $v['achievementSalary'] ?? '');
            $activeWorksheet->setCellValue('R' . $i, $v['dutySalary'] ?? '');
            $activeWorksheet->setCellValue('S' . $i, $v['overtimeSalary'] ?? '');
            $activeWorksheet->setCellValue('T' . $i, $v['recordSalary'] ?? '');
            $activeWorksheet->setCellValue('U' . $i, $v['bonusFullAtt'] ?? '');
            $activeWorksheet->setCellValue('V' . $i, $v['bonusBid'] ?? '');
            $activeWorksheet->setCellValue('W' . $i, $v['bonusPerson'] ?? '');
            $activeWorksheet->setCellValue('X' . $i, $v['deductionSocialSecurity'] ?? '');
            $activeWorksheet->setCellValue('Y' . $i, $v['deductionOther'] ?? '');
            $activeWorksheet->setCellValue('Z' . $i, $v['sumSalary']);
            $activeWorksheet->setCellValue('AA' . $i, $v['bonusOffSeason'] ?? '');
            $activeWorksheet->setCellValue('AB' . $i, $v['finalSalary']);
            $activeWorksheet->setCellValue('AC' . $i, $v['description']);
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
    // 按经营主体导出工资月表
    public function exportCompanyAchievementExcel()
    {
        if (!$this->myservice->verifyRight('ACHIEVEMENT_ADVANCE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/Achievement/exportCompanyAchievementExcel 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $company = $this->request->getGetPost('company');
        $data = $this->db->table('bill_achievement')->where(['id' => $id, 'isDelete' => 0])->get()->getRowArray();
        $uIdArr = array_column($this->db->table('user')->select('id')->where(['company' => $company, 'isDelete' => 0])->get()->getResultArray(), 'id');
        if ($data && count($uIdArr) > 0) {
            $list = $this->db->table('bill_achievement_info')
                ->where('iid', $id)
                ->whereIn('uId', $uIdArr)
                ->where('isDelete', 0)
                ->orderBy('department DESC, position ASC, uId ASC')
                ->get()
                ->getResultArray();
            if (count($list) > 0) {
                foreach ($list as $k => $v) {
                    $list[$k]['sfzNo'] = $this->db->table('user')->select('sfzNo')->where(['id' => $v['uId']])->get()->getRowArray()['sfzNo'] ?? '';
                }
            }
        } else {
            $list = [];
        }
        $dateArr = explode('-', $data['beginDate']);
        $name = $company . $dateArr[0] . '年' . $dateArr[1] . '月工资表';

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
        $activeWorksheet->getStyle('A1:O' . (COUNT($list) + 2))->applyFromArray($styleArray, false);

        // 设置加粗
        $activeWorksheet->getStyle('A1')->getFont()->setBold(true);
        $activeWorksheet->getStyle('A2:O2')->getFont()->setBold(true);

        // 设置背景色
        $activeWorksheet->getStyle('A2:O2')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
        $activeWorksheet->getStyle('A2:O2')->getFill()->getStartColor()->setRGB('F5F5F5');

        // 合并单元格
        $activeWorksheet->mergeCells('A1:O1');

        // 设置行高
        $activeWorksheet->getRowDimension('1')->setRowHeight(40);

        // 设置水平居中
        $activeWorksheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // 设置每列列宽
        $activeWorksheet->getColumnDimension('A')->setWidth(8);
        $activeWorksheet->getColumnDimension('B')->setWidth(30);
        $activeWorksheet->getColumnDimension('C')->setWidth(10);
        $activeWorksheet->getColumnDimension('D')->setWidth(10);
        $activeWorksheet->getColumnDimension('E')->setWidth(10);
        $activeWorksheet->getColumnDimension('F')->setWidth(10);
        $activeWorksheet->getColumnDimension('G')->setWidth(10);
        $activeWorksheet->getColumnDimension('H')->setWidth(10);
        $activeWorksheet->getColumnDimension('I')->setWidth(10);
        $activeWorksheet->getColumnDimension('J')->setWidth(10);
        $activeWorksheet->getColumnDimension('K')->setWidth(10);
        $activeWorksheet->getColumnDimension('L')->setWidth(10);
        $activeWorksheet->getColumnDimension('M')->setWidth(10);
        $activeWorksheet->getColumnDimension('N')->setWidth(10);
        $activeWorksheet->getColumnDimension('O')->setWidth(10);

        //设置当前的sheet
        $activeWorksheet->setCellValue('A1', $name . '（导出时间 ' . date('Y-m-d H:i:s') . '）');

        $activeWorksheet->setCellValue('A2', '姓名');
        $activeWorksheet->setCellValue('B2', '身份证号码');
        $activeWorksheet->setCellValue('C2', '出勤工资');
        $activeWorksheet->setCellValue('D2', '考评工资');
        $activeWorksheet->setCellValue('E2', '绩效工资');
        $activeWorksheet->setCellValue('F2', '值班工资');
        $activeWorksheet->setCellValue('G2', '加班工资');
        $activeWorksheet->setCellValue('H2', '计时工资');
        $activeWorksheet->setCellValue('I2', '全勤奖');
        $activeWorksheet->setCellValue('J2', '其他奖金');
        $activeWorksheet->setCellValue('K2', '岗位津贴');
        $activeWorksheet->setCellValue('L2', '社保扣费');
        $activeWorksheet->setCellValue('M2', '其他扣除');
        $activeWorksheet->setCellValue('N2', '淡季津贴');
        $activeWorksheet->setCellValue('O2', '实发工资');

        $i = 3;
        foreach ($list as $k => $v) {
            $activeWorksheet->setCellValue('A' . $i, $v['uName']);
            $activeWorksheet->setCellValueExplicit('B' . $i, $v['sfzNo'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING); // 显式设置单元格格式为字符串类型
            $activeWorksheet->setCellValue('C' . $i, intval($v['attendanceSalary']));
            $activeWorksheet->setCellValue('D' . $i, intval($v['evaluateSalary']));
            $activeWorksheet->setCellValue('E' . $i, intval($v['achievementSalary']));
            $activeWorksheet->setCellValue('F' . $i, intval($v['dutySalary']));
            $activeWorksheet->setCellValue('G' . $i, intval($v['overtimeSalary']));
            $activeWorksheet->setCellValue('H' . $i, intval($v['recordSalary']));
            $activeWorksheet->setCellValue('I' . $i, intval($v['bonusFullAtt']));
            $activeWorksheet->setCellValue('J' . $i, intval($v['bonusBid']));
            $activeWorksheet->setCellValue('K' . $i, intval($v['bonusPerson']));
            $activeWorksheet->setCellValue('L' . $i, intval($v['deductionSocialSecurity']));
            $activeWorksheet->setCellValue('M' . $i, intval($v['deductionOther']));
            $activeWorksheet->setCellValue('N' . $i, intval($v['bonusOffSeason']));
            $activeWorksheet->setCellValue('O' . $i, intval($v['finalSalary']));
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


    // 一键生成工资月表
    public function createMonthAchievement()
    {
        if (!$this->myservice->verifyRight('ACHIEVEMENT_ADVANCE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/Achievement/createMonthAchievement 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $year = $this->request->getGetPost('year');
        $month = $this->request->getGetPost('month');
        if ($year != '' && $month != '') {
            $beginDate = date('Y-m-d', strtotime($year . '-' . $month . '-01'));
            $endDate = date('Y-m-t', strtotime($year . '-' . $month . '-01'));
            if ($this->db->table('bill_achievement')->where(['beginDate' => $beginDate, 'isDelete' => 0])->countAllResults() > 0) {
                return $this->response->setJSON(['status' => -1, 'msg' => '已生成过该月工资月表单']);
            } elseif ($this->db->table('bill_attendance')->where(['beginDate' => $beginDate, 'isDraft' => 0, 'isDelete' => 0])->countAllResults() < 1) {
                return $this->response->setJSON(['status' => -1, 'msg' => '缺少该月正式（非草稿）出勤月表单，无法生成！']);
            } elseif ($this->db->table('bill_evaluate')->where(['beginDate' => $beginDate, 'isDraft' => 0, 'isDelete' => 0])->countAllResults() < 1) {
                return $this->response->setJSON(['status' => -1, 'msg' => '缺少该月正式（非草稿）考评月表单，无法生成！']);
            } elseif ($this->db->table('bill_onlineshopMp')->where(['beginDate' => $beginDate, 'isDraft' => 0, 'isDelete' => 0])->countAllResults() < 1) {
                return $this->response->setJSON(['status' => -1, 'msg' => '缺少该月正式（非草稿）网店月绩单，无法生成！']);
            } elseif ($this->db->table('bill_contract')->where(['billDate >=' => $beginDate, 'billDate <=' => $endDate, 'billType' => 'PUR', 'moneyStatus1 >' => 0, 'isReq' => 1, 'isDraft' => 0, 'isDelete' => 0])->countAllResults() > 0) {
                return $this->response->setJSON(['status' => -1, 'msg' => '有该月已付款特殊采购单处于打回状态，无法生成！']);
            } elseif ($this->db->table('bill_contract')->where(['isReq !=' => 1, 'billDate >=' => $beginDate, 'billDate <=' => $endDate, 'billType' => 'PUR', 'checkId' => 0, 'isDraft' => 0, 'isDelete' => 0])->countAllResults() > 0) {
                return $this->response->setJSON(['status' => -1, 'msg' => '有该月特殊采购单未审核，无法生成！']);
            } elseif ($this->db->table('bill_contract')->where(['billDate >=' => $beginDate, 'billDate <=' => $endDate, 'billType' => 'SALE', 'checkId' => 0, 'isDraft' => 0, 'isDelete' => 0])->countAllResults() > 0) {
                return $this->response->setJSON(['status' => -1, 'msg' => '有该月特殊销售单未审核，无法生成！']);
            } elseif ($this->db->table('bill_order')->where(['billDate >=' => $beginDate, 'billDate <=' => $endDate, 'billType' => 'PUR', 'checkId' => 0, 'isCancel' => 0, 'isDraft' => 0, 'isDelete' => 0])->countAllResults() > 0) {
                return $this->response->setJSON(['status' => -1, 'msg' => '有该月采购订单未审核，无法生成！']);
            } elseif ($this->db->table('bill_order')->where(['billDate >=' => $beginDate, 'billDate <=' => $endDate, 'billType' => 'SALE', 'checkId' => 0, 'isCancel' => 0, 'isDraft' => 0, 'isDelete' => 0])->countAllResults() > 0) {
                return $this->response->setJSON(['status' => -1, 'msg' => '有该月销售订单未审核，无法生成！']);
            } elseif ($this->db->table('pro_kitting_task_up')->where(['createTime >=' => $beginDate . ' 00:00:00', 'createTime <=' => $endDate . ' 23:59:59', 'checkId' => 0, 'isDelete' => 0])->countAllResults() > 0) {
                return $this->response->setJSON(['status' => -1, 'msg' => '有该月配货交付未点货入库，无法生成！']);
            } else {
                $users = $this->db->table('user')
                    ->where('roleId', 0)
                    ->whereNotIn('name', ["系统","审查员"])
                    ->groupStart()
                        ->where('isDisable', 0)
                        ->orGroupStart()
                            ->where('isDisable', 1)
                            ->where('leaveDate >=', $beginDate)
                        ->groupEnd()
                    ->groupEnd()
                    ->where('isDelete', 0)
                    ->get()
                    ->getResultArray();
                $attendance = $this->db->table('bill_attendance')->where(['beginDate' => $beginDate, 'isDraft' => 0, 'isDelete' => 0])->get()->getRowArray();
                $attendance_info = $this->db->table('bill_attendance_info')->where(['iid' => $attendance['id'], 'isDelete' => 0])->get()->getResultArray();
                if ($attendance_info) {
                    $attendance_info = array_column($attendance_info, null, 'uName');
                }
                $evaluate_info = $this->db->table('bill_evaluate_info')->select('uName, score')->where(['beginDate' => $beginDate, 'isDelete' => 0])->groupBy('uName')->get()->getResultArray();
                if ($evaluate_info) {
                    $evaluate_info = array_column($evaluate_info, 'score', 'uName');
                }
                if (!empty($users)) {
                    $data['billNo'] = 'GZ' . date('Ym', strtotime($beginDate));
                    $data['beginDate'] = $beginDate;
                    $data['endDate'] = $endDate;
                    $data['description'] = '';
                    $data['uId'] = $this->myerp['uId'];
                    $data['uName'] = $this->myerp['uName'];
                    $data['createTime'] = $data['modifyTime'] = date('Y-m-d H:i:s');
                    $data['isDraft'] = 1;
                    $this->db->table('bill_achievement')->insert($data);
                    $iid = $this->db->insertID();
                    $v = [];
                    foreach ($users as $index => $user) {
                        // 采购业绩
                        $performancePur['orderinfo'] = $this->db->table('bill_order_info')
                            ->where('billDate >=', $beginDate)
                            ->where('billDate <=', $endDate)
                            ->where(['billType' => 'PUR', 'uId' => $user['id'], 'checkId >' => 0, 'isDelete' => 0])
                            ->countAllResults();

                        $performancePur['contractinfo'] = $this->db->table('bill_contract_info')
                            ->where('paidTime >=', $beginDate . ' 00:00:00')
                            ->where('paidTime <=', $endDate . ' 23:59:59')
                            ->where(['billType' => 'PUR', 'isReq !=' => 1, 'uId' => $user['id'], 'price >' => 0, 'paidAmount >' => 0, 'isDelete' => 0])
                            ->countAllResults();

                        $performancePur['supplier'] = $this->db->table('contact')
                            ->where('createTime >=', $beginDate . ' 00:00:00')
                            ->where('createTime <=', $endDate . ' 23:59:59')
                            ->where(['uName' => $user['name'], 'type' => 1, 'isDisable' => 0, 'isDelete' => 0])
                            ->countAllResults();

                        $performancePur['deAmount'] = floatval($this->db->table('bill_contract_info')
                            ->select('SUM(CASE WHEN (limitPrice * 1.25 * qty) <= paidAmount THEN 0 ELSE ((limitPrice * 1.25 * qty) - paidAmount) END) AS deAmount', false)
                            ->where('paidTime >=', $beginDate . ' 00:00:00')
                            ->where('paidTime <=', $endDate . ' 23:59:59')
                            ->where(['isReq' => 2, 'uId' => $user['id'], 'billType' => 'PUR', 'limitPrice >' => 0, 'price >' => 0])
                            ->where('paidAmount >', 0)
<<<<<<< HEAD
                            ->where('paidAmount >= amount', null, false)
=======
                            ->where('paidAmount >=', 'amount', false)
>>>>>>> 151b415 (更新语言最新版本+最新底层框架后全部差异化代码适配重写)
                            ->where('isDelete', 0)
                            ->get()
                            ->getRowArray()['deAmount'] ?? 0);
                        $performancePur['core'] = intval((float)$performancePur['orderinfo'] * (float)$this->myerp['expPar']['pur']['par1'] + (float)$performancePur['contractinfo'] * (float)$this->myerp['expPar']['pur']['par2'] + (float)$performancePur['supplier'] * (float)$this->myerp['expPar']['pur']['par3'] + (float)$performancePur['deAmount'] * (float)$this->myerp['expPar']['pur']['par4']);

                        // 销售业绩
                        $performanceSale['order'] = floatval($this->db->table('bill_order')
                            ->selectSum('amount')
                            ->where('createTime >=', $beginDate . ' 00:00:00')
                            ->where('createTime <=', $endDate . ' 23:59:59')
                            ->where(['billType' => 'SALE', 'uId' => $user['id'], 'checkId >' => 0, 'moneyStatus1' => 2, 'isCancel' => 0, 'isDelete' => 0])
                            ->get()
                            ->getRowArray()['amount'] ?? 0);

                        $performanceSale['contract'] = floatval($this->db->table('bill_contract')
                            ->select('SUM(CASE WHEN LOCATE(",", saler) > 0 THEN (amount / ((CHAR_LENGTH(saler) - CHAR_LENGTH(REPLACE(saler,",",""))) + 1) ) ELSE amount END) AS amount', false)
                            ->where('deliverDoneTime >=', $beginDate . ' 00:00:00')
                            ->where('deliverDoneTime <=', $endDate . ' 23:59:59')
                            ->where(['billType' => 'SALE', 'checkId >' => 0, 'goodsStatus1 >' => 0, 'redoSoType' => 0, 'isDelete' => 0])
<<<<<<< HEAD
                            ->where('FIND_IN_SET(' . $this->db->escape($user['name']) . ', saler) >', 0, false)
=======
                            ->where("FIND_IN_SET('" . $user['name'] . "', saler) >", 0, false)
>>>>>>> 151b415 (更新语言最新版本+最新底层框架后全部差异化代码适配重写)
                            ->get()
                            ->getRowArray()['amount'] ?? 0);

                        // 扣掉相应有退款单的退款金额
                        $orderRefundBills = $this->db->table('bill_order')
                            ->select('srcMoneyId2')
                            ->where('createTime >=', $beginDate . ' 00:00:00')
                            ->where('createTime <=', $endDate . ' 23:59:59')
                            ->where(['billType' => 'SALE', 'uId' => $user['id'], 'checkId >' => 0, 'moneyStatus1' => 2, 'srcMoneyId2 >' => 0, 'isCancel' => 0, 'isDelete' => 0])
                            ->get()
                            ->getResultArray();
                        if (!empty($orderRefundBills)) {
                            $orderRefundBillIds = array_column($orderRefundBills, 'srcMoneyId2');
                            $refundAmount = floatval($this->db->table('bill_money_info')
                                ->selectSum('amount')
                                ->whereIn('iid', $orderRefundBillIds)
                                ->where(['isDelete' => 0])
                                ->get()
                                ->getRowArray()['amount'] ?? 0);
                            $performanceSale['order'] -= $refundAmount;
                        }
                        $contractRefundBills = $this->db->table('bill_contract')
                            ->select('srcMoneyId2,saler')
                            ->where('deliverDoneTime >=', $beginDate . ' 00:00:00')
                            ->where('deliverDoneTime <=', $endDate . ' 23:59:59')
                            ->where(['billType' => 'SALE', 'checkId >' => 0, 'goodsStatus1 >' => 0, 'redoSoType' => 0, 'srcMoneyId2 >' => 0, 'isDelete' => 0])
<<<<<<< HEAD
                            ->where('FIND_IN_SET(' . $this->db->escape($user['name']) . ', saler) >', 0, false)
=======
                            ->where("FIND_IN_SET('" . $user['name'] . "', saler) >", 0, false)
>>>>>>> 151b415 (更新语言最新版本+最新底层框架后全部差异化代码适配重写)
                            ->get()
                            ->getResultArray();
                        if (!empty($contractRefundBills)) {
                            foreach ($contractRefundBills as $row) {
                                $refundAmount = floatval($this->db->table('bill_money_info')
                                    ->selectSum('amount')
                                    ->where(['iid' => $row['srcMoneyId2'], 'isDelete' => 0])
                                    ->get()
                                    ->getRowArray()['amount'] ?? 0);
                                if (strpos($row['saler'], ',') !== false) {
                                    $salerArray = explode(',', $row['saler']);
                                    $salerCount = count($salerArray);
                                    if ($salerCount > 0) {
                                        $performanceSale['contract'] -= $refundAmount / $salerCount;
                                    }
                                } else {
                                    $performanceSale['contract'] -= $refundAmount;
                                }
                            }
                        }

                        $performanceSale['onlineshop'] = floatval($this->db->table('bill_onlineshopMp_info')
                            ->selectSum('amount')
                            ->where(['beginDate >=' => $beginDate, 'endDate <=' => $endDate, 'uName' => $user['name'], 'isDelete' => 0])
                            ->get()
                            ->getRowArray()['amount'] ?? 0);

                        $performanceSale['core'] = intval((float)$performanceSale['order'] * (float)$this->myerp['expPar']['sale']['par1'] + (float)$performanceSale['contract'] * (float)$this->myerp['expPar']['sale']['par2'] + (float)$performanceSale['onlineshop'] * (float)$this->myerp['expPar']['sale']['par3']);

                        // 生产业绩（配货+加工）
                        $pro_kitting_task_up_amount = floatval($this->db->table('pro_kitting_task_up')
                            ->selectSum('amount')
                            ->where('createTime >=', $beginDate . ' 00:00:00')
                            ->where('createTime <=', $endDate . ' 23:59:59')
                            ->where('doneStatus', 2)
                            ->where('uName', $user['name'])
                            ->where('isDelete', 0)
                            ->get()
                            ->getRowArray()['amount'] ?? 0);
                        $pro_processing_task_up_amount = floatval($this->db->table('pro_processing_task_up')
                            ->selectSum('amount')
                            ->where('createTime >=', $beginDate . ' 00:00:00')
                            ->where('createTime <=', $endDate . ' 23:59:59')
                            ->where('doneStatus', 2)
                            ->where('uName', $user['name'])
                            ->where('isDelete', 0)
                            ->get()
                            ->getRowArray()['amount'] ?? 0);
                        $performancePro['core'] = intval((float)$pro_kitting_task_up_amount + (float)$pro_processing_task_up_amount);

                        // 打包业绩
                        if (in_array($user['department'], ['仓储部', '生产部', '销售部'])) {
                            $performancePack = $this->db->table('deliver_bag')
                                ->select('COUNT(CASE WHEN weight < 20 THEN no ELSE null END) AS totalCount1, SUM(CASE WHEN weight < 20 THEN weight ELSE 0 END) AS totalWeight1, COUNT(CASE company WHEN "申通快递" THEN no ELSE null END) AS totalZYCount, SUM(CASE company WHEN "申通快递" THEN amount ELSE 0 END) AS totalZYAmount, COUNT(CASE WHEN weight >= 20 THEN no ELSE null END) AS totalCount2, SUM(CASE WHEN weight >= 20 THEN weight ELSE 0 END) AS totalWeight2')
                                ->where('scanTime >=', $beginDate . ' 00:00:00')
                                ->where('scanTime <=', $endDate . ' 23:59:59')
                                ->where('scanName', $user['name'])
                                ->where('isDelete', 0)
                                ->get()
                                ->getRowArray() ?? [
                                    'totalCount1' => 0,
                                    'totalWeight1' => 0,
                                    'totalZYCount' => 0,
                                    'totalZYAmount' => 0,
                                    'totalCount2' => 0,
                                    'totalWeight2' => 0
                                ];
                            $performancePack['core'] = intval((float)$performancePack['totalCount1'] * (float)$this->myerp['expPar']['pack']['par1'] + (float)$performancePack['totalWeight1'] * (float)$this->myerp['expPar']['pack']['par2'] + ((float)$this->myerp['expPar']['pack']['par3'] * (float)$performancePack['totalZYCount'] - (float)$performancePack['totalZYAmount']) * (float)$this->myerp['expPar']['pack']['par4'] + (float)$performancePack['totalCount2'] * (float)$this->myerp['expPar']['pack']['par5'] + (float)$performancePack['totalWeight2'] * (float)$this->myerp['expPar']['pack']['par6']);
                        } else {
                            $performancePack = [
                                'totalCount1' => 0,
                                'totalWeight1' => 0,
                                'totalZYCount' => 0,
                                'totalZYAmount' => 0,
                                'totalCount2' => 0,
                                'totalWeight2' => 0,
                                'core' => 0
                            ];
                        }

                        // 投标奖金
                        $bonusBid = 0;

                        // 投标档位奖金
                        $bidOffer = $this->db->table('bid_program')
                            ->select('SUM(CASE WHEN LOCATE(",", quotedName) > 0 THEN (amount / ((CHAR_LENGTH(quotedName) - CHAR_LENGTH(REPLACE(quotedName,",",""))) + 1)) ELSE amount END) AS amount, COUNT(*) AS offerCount', false)
                            ->where('createTime >=', $beginDate . ' 00:00:00')
                            ->where('createTime <=', $endDate . ' 23:59:59')
                            ->where('FIND_IN_SET("' . $user['name'] . '", quotedName) >', 0, false)
                            ->where('isDelete', 0)
                            ->get()
                            ->getRowArray() ?? ['amount' => 0, 'offerCount' => 0];

                        $bidWin = $this->db->table('bid_program')
                            ->select('SUM(CASE WHEN LOCATE(",", quotedName) > 0 THEN (amount / ((CHAR_LENGTH(quotedName) - CHAR_LENGTH(REPLACE(quotedName,",",""))) + 1)) ELSE amount END) AS winAmount, COUNT(*) AS winCount', false)
                            ->where('createTime >=', $beginDate . ' 00:00:00')
                            ->where('createTime <=', $endDate . ' 23:59:59')
                            ->where('FIND_IN_SET("' . $user['name'] . '", quotedName) >', 0, false)
                            ->where('status', 2)
                            ->where('isDelete', 0)
                            ->get()
                            ->getRowArray() ?? ['winAmount' => 0, 'winCount' => 0];

                        if ((float)$bidOffer['amount'] > 1000000 && (float)$bidWin['winAmount'] > 120000) {
                            $bonusBid += (float)$this->myerp['system']['bonusBid2'];
                        } elseif ((float)$bidOffer['amount'] > 500000 && (float)$bidWin['winAmount'] > 50000) {
                            $bonusBid += (float)$this->myerp['system']['bonusBid1'];
                        }

                        // 中标飞跃奖金
                        // 前两月中标金额
                        $winAmount_lastTwoMonth = floatval($this->db->table('bid_program')
                            ->select('SUM(CASE WHEN LOCATE(",", quotedName) > 0 THEN (amount / ((CHAR_LENGTH(quotedName) - CHAR_LENGTH(REPLACE(quotedName,",",""))) + 1)) ELSE amount END) AS winAmount', false)
                            ->where('createTime >=', date('Y-m-01', strtotime($beginDate . ' -1 month')) . ' 00:00:00')
                            ->where('createTime <=', $endDate . ' 23:59:59')
                            ->where('FIND_IN_SET("' . $user['name'] . '", quotedName) >', 0, false)
                            ->where('isDelete', 0)
                            ->where('status', 2)
                            ->get()
                            ->getRowArray()['winAmount'] ?? 0);
                        // 去年同期两月中标金额
                        $winAmount_lastYearSameTwoMonth = floatval($this->db->table('bid_program')
                            ->select('SUM(CASE WHEN LOCATE(",", quotedName) > 0 THEN (amount / ((CHAR_LENGTH(quotedName) - CHAR_LENGTH(REPLACE(quotedName,",",""))) + 1)) ELSE amount END) AS winAmount', false)
                            ->where('createTime >=', date('Y-m-01', strtotime($beginDate . ' -13 month')) . ' 00:00:00')
                            ->where('createTime <=', date('Y-m-t', strtotime($endDate . ' -12 month')) . ' 23:59:59')
                            ->where('FIND_IN_SET("' . $user['name'] . '", quotedName) >', 0, false)
                            ->where('isDelete', 0)
                            ->where('status', 2)
                            ->get()
                            ->getRowArray()['winAmount'] ?? 0);
                        if ($winAmount_lastTwoMonth > 0 && $winAmount_lastYearSameTwoMonth > 0 && ($winAmount_lastTwoMonth / $winAmount_lastYearSameTwoMonth) >= 1.39) {
                            $bonusBid += (float)$this->myerp['system']['bonusBidLeap'];
                        }
                        // 开始准备最终数据
                        $v[$index]['iid'] = intval($iid);
                        $v[$index]['billNo'] = $data['billNo'];
                        $v[$index]['beginDate'] = $beginDate;
                        $v[$index]['endDate'] = $endDate;
                        $v[$index]['uId'] = intval($user['id']);
                        $v[$index]['uName'] = $user['name'];
                        $v[$index]['department'] = $user['department'];
                        $v[$index]['position'] = $user['position'];
                        $v[$index]['level'] = $user['level'];
                        $v[$index]['hourlyWage'] = (float)($this->myerp['userLevel'][$user['levelId']]['hourlyWage'] ?? 0);
                        $v[$index]['workHours'] = (float)($attendance_info[$user['name']]['workHours'] ?? 0);
                        $v[$index]['fillHours'] = (float)($attendance_info[$user['name']]['fillHours'] ?? 0);
                        $v[$index]['overtimeHours'] = (float)($attendance_info[$user['name']]['overtimeHours'] ?? 0);
                        $v[$index]['recordHours'] = (float)($attendance_info[$user['name']]['recordHours'] ?? 0);
                        $v[$index]['dutyTimes'] = intval($attendance_info[$user['name']]['dutyTimes'] ?? 0);
                        $v[$index]['workDays'] = (float)($attendance_info[$user['name']]['workDays'] ?? 0);
                        if (isset($attendance_info[$user['name']]['mustDayNum'])) {
                            $v[$index]['mustDayNum'] = (float)$attendance_info[$user['name']]['mustDayNum'];
                        } else {
                            $v[$index]['mustDayNum'] = floatval($this->db->table('attendance_plan')
                                ->select('m' . intval(date('m', strtotime($beginDate))))
                                ->where(['year' => intval(date('Y', strtotime($beginDate))), 'isDelete' => 0])
                                ->get()
                                ->getRowArray()['m' . intval(date('m', strtotime($beginDate)))] ?? 26); // 缺省兜底26天
                        }
                        $v[$index]['isFull'] = intval($attendance_info[$user['name']]['isFull'] ?? 0);
                        $v[$index]['evaluateScore'] = ($evaluate_info && isset($evaluate_info[$user['name']])) ? intval($evaluate_info[$user['name']]) : 0;
                        $v[$index]['achievementPur'] = (float)$performancePur['core'];
                        $v[$index]['achievementSale'] = (float)$performanceSale['core'];
                        $v[$index]['achievementPro'] = (float)$performancePro['core'];
                        $v[$index]['achievementPack'] = (float)$performancePack['core'];
                        // 出勤率 最大限制为2（即200%），比按要求多上的班将自动计算多出时间的考评工资。
                        $v[$index]['workRate'] = min(floor(floor(($v[$index]['workHours'] + $v[$index]['fillHours']) / 9 * 1000) / 1000 / $v[$index]['mustDayNum'] * 1000) / 1000, 2);
                        // 出勤工资 = 时薪 *（打卡 + 补班小时数）
                        $v[$index]['attendanceSalary'] = intval($v[$index]['hourlyWage'] * ($v[$index]['workHours'] + $v[$index]['fillHours']));
                        // 考评工资 = 考评工资基数 * 出勤率（可能大于100%）（然后采购部统一再*70%，生产部统一再*考评分/100）
                        $v[$index]['evaluateSalary'] = intval(intval($user['fixedSalary']) * $v[$index]['workRate']);
                        if ($v[$index]['department'] == '采购部' && $v[$index]['evaluateSalary'] > 0) {
                            $v[$index]['evaluateSalary'] = intval($v[$index]['evaluateSalary'] * 0.7);
                        }
                        if ($v[$index]['department'] == '生产部' && $v[$index]['evaluateSalary'] > 0) {
                            $v[$index]['evaluateSalary'] = intval($v[$index]['evaluateSalary'] * $v[$index]['evaluateScore'] / 100);
                        }
                        // 绩效工资 = 采购业绩指数 * 采购绩效工资转化系数 + 销售业绩指数 * 销售绩效工资转化系数 + 配货业绩指数 * 配货绩效工资转化系数 + 打包业绩指数 * 打包绩效工资转化系数（某些特殊情况下不该算打包业绩的人员无打包业绩）
                        $v[$index]['achievementSalary'] = intval(($v[$index]['achievementPur'] * (float)$this->myerp['expPar']['pur']['achievementSalaryRate']) + ($v[$index]['achievementSale'] * (float)$this->myerp['expPar']['sale']['achievementSalaryRate']) + ($v[$index]['achievementPro'] * (float)$this->myerp['expPar']['proKitting']['achievementSalaryRate']) + ($v[$index]['achievementPack'] * (float)$this->myerp['expPar']['pack']['achievementSalaryRate']));
                        // 销售值班工资 = 值班次数 * 销售每次值班工资
                        $v[$index]['dutySalary'] = $v[$index]['dutyTimes'] * (float)$this->myerp['system']['bonusDuty'];
                        // 加班工资 = 加班小时数 * 不同部门不同的加班时薪
                        $v[$index]['overtimeSalary'] = $v[$index]['overtimeHours'] * (in_array($user['department'], explode(',', $this->myerp['system']['priceWH1Department'])) ? (float)$this->myerp['system']['priceWH1'] : (float)$this->myerp['system']['priceWH2']);
                        // 计时工资 = 计时小时数 * 计时时薪
                        $v[$index]['recordSalary'] = $v[$index]['recordHours'] * (float)$this->myerp['system']['priceWR'];
                        // 全勤奖金
                        $v[$index]['bonusFullAtt'] = $v[$index]['isFull'] > 0 ? (float)$this->myerp['system']['bonusFullAttendance'] : 0;
                        // 投标奖金
                        $v[$index]['bonusBid'] = (float)$bonusBid;
                        // 岗位津贴
                        $v[$index]['bonusPerson'] = intval($user['bonusPerson']) * $v[$index]['workRate'];
                        // 如果已参保且参保日期在上月底之前，或已停保但停保日期在上月底之后，则扣社保。
                        if (
                            ($user['isBuySocialSecurity'] == 1 && $user['buySocialSecurityDate'] <= $endDate) ||
                            ($user['isBuySocialSecurity'] == 2 && $user['stopSocialSecurityDate'] > $endDate)
                        ) {
                            // 社保扣费基数
                            $v[$index]['deductionSocialSecurity'] = $this->myerp['system']['deductionSocialSecurity'] ?? 372;
                            // 缺勤扣社保
                            if ((float)$attendance_info[$user['name']]['deSocialSecurityDayNum'] > 0) {
                                $v[$index]['deductionSocialSecurity'] = intval($v[$index]['deductionSocialSecurity']) + intval((float)$attendance_info[$user['name']]['deSocialSecurityDayNum'] * 50);
                            }
                        } else {
                            $v[$index]['deductionSocialSecurity'] = 0;
                        }
                        // 其他扣除 提取反馈责任损失
                        $v[$index]['deductionOther'] = 0;
                        $serviceTarget = $this->db->table('service')
                            ->select('id,targetName,targetAmount')
                            ->where('beginDate >=', $beginDate . ' 00:00:00')
                            ->where('beginDate <=', $endDate . ' 23:59:59')
                            ->where('FIND_IN_SET("' . $user['name'] . '", targetName) >', 0, false)
                            ->where('targetAmount >', 0)
                            ->where('disStatus', 3)
                            ->where('isDelete', 0)
                            ->get()
                            ->getResultArray();
                        $v[$index]['description'] = $attendance_info[$user['name']]['description'] ?? '';
                        if (count($serviceTarget) > 0) {
                            foreach ($serviceTarget as $arr => $row) {
                                $deduction = (float)$row['targetAmount'] / count(explode(',', $row['targetName']));
                                $v[$index]['deductionOther'] += $deduction;
                                $v[$index]['description'] .= '责任扣除' . ($arr + 1) . ': 客户之声记录ID: ' . $row['id'] . ',扣除: ' . $deduction . '元。';
                            }
                        }
                        // 汇总工资 = 出勤工资 + 考评工资 + 绩效工资 + 值班工资 + 加班工资 + 计时工资 + 全勤奖金 + 投标奖金 + 岗位津贴 - 社保扣费 - 其他扣除
                        $v[$index]['sumSalary'] = $v[$index]['attendanceSalary'] + $v[$index]['evaluateSalary'] + $v[$index]['achievementSalary'] + $v[$index]['dutySalary'] + $v[$index]['overtimeSalary'] + $v[$index]['recordSalary'] + $v[$index]['bonusFullAtt'] + $v[$index]['bonusBid'] + $v[$index]['bonusPerson'] - $v[$index]['deductionSocialSecurity'] - $v[$index]['deductionOther'];
                    }
                    $this->db->transStart();
                    $this->db->table('bill_achievement_info')->insertBatch($v);
                    $this->myservice->log('一键生成了工资月表单，单号：' . $data['billNo']);
                    $this->db->transComplete();
                    if ($this->db->transStatus() === false) {
                        return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
                    }
                    return $this->response->setJSON(['status' => 200, 'msg' => '生成成功！']);
                }
                return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
            }
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '请选择年月！']);
    }
    // 一键重算工资月表
    public function redoMonthAchievement()
    {
        if (!$this->myservice->verifyRight('ACHIEVEMENT_ADVANCE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/Achievement/redoMonthAchievement 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $year = $this->request->getGetPost('year');
        $month = $this->request->getGetPost('month');
        if ($year != '' && $month != '') {
            $beginDate = $year . '-' . $month . '-01';
            $endDate = date('Y-m-t', strtotime($year . '-' . $month . '-01'));
            if ($this->db->table('bill_achievement')->where(['beginDate' => $beginDate, 'isDelete' => 0])->countAllResults() < 1) {
                return $this->response->setJSON(['status' => -1, 'msg' => '尚无该月工资月表单，无法重算！']);
            } elseif ($this->db->table('bill_attendance')->where(['beginDate' => $beginDate, 'isDraft' => 0, 'isDelete' => 0])->countAllResults() < 1) {
                return $this->response->setJSON(['status' => -1, 'msg' => '缺少该月正式（非草稿）出勤月表单，无法重算！']);
            } elseif ($this->db->table('bill_evaluate')->where(['beginDate' => $beginDate, 'isDraft' => 0, 'isDelete' => 0])->countAllResults() < 1) {
                return $this->response->setJSON(['status' => -1, 'msg' => '缺少该月正式（非草稿）考评月表单，无法重算！']);
            } elseif ($this->db->table('bill_onlineshopMp')->where(['beginDate' => $beginDate, 'isDraft' => 0, 'isDelete' => 0])->countAllResults() < 1) {
                return $this->response->setJSON(['status' => -1, 'msg' => '缺少该月正式（非草稿）网店月绩单，无法重算！']);
            } elseif ($this->db->table('bill_contract')->where(['billDate >=' => $beginDate, 'billDate <=' => $endDate, 'billType' => 'PUR', 'moneyStatus1 >' => 0, 'isReq' => 1, 'isDraft' => 0, 'isDelete' => 0])->countAllResults() > 0) {
                return $this->response->setJSON(['status' => -1, 'msg' => '有该月已付款特采单处于打回状态，无法重算！']);
            } elseif ($this->db->table('bill_contract')->where(['isReq !=' => 1, 'billDate >=' => $beginDate, 'billDate <=' => $endDate, 'billType' => 'PUR', 'checkId' => 0, 'isDraft' => 0, 'isDelete' => 0])->countAllResults() > 0) {
                return $this->response->setJSON(['status' => -1, 'msg' => '有该月特殊采购单未审核，无法重算！']);
            } elseif ($this->db->table('bill_contract')->where(['billDate >=' => $beginDate, 'billDate <=' => $endDate, 'billType' => 'SALE', 'checkId' => 0, 'isDraft' => 0, 'isDelete' => 0])->countAllResults() > 0) {
                return $this->response->setJSON(['status' => -1, 'msg' => '有该月特殊销售单未审核，无法重算！']);
            } elseif ($this->db->table('bill_order')->where(['billDate >=' => $beginDate, 'billDate <=' => $endDate, 'billType' => 'PUR', 'checkId' => 0, 'isCancel' => 0, 'isDraft' => 0, 'isDelete' => 0])->countAllResults() > 0) {
                return $this->response->setJSON(['status' => -1, 'msg' => '有该月采购订单未审核，无法重算！']);
            } elseif ($this->db->table('bill_order')->where(['billDate >=' => $beginDate, 'billDate <=' => $endDate, 'billType' => 'SALE', 'checkId' => 0, 'isCancel' => 0, 'isDraft' => 0, 'isDelete' => 0])->countAllResults() > 0) {
                return $this->response->setJSON(['status' => -1, 'msg' => '有该月销售订单未审核，无法重算！']);
            } elseif ($this->db->table('pro_kitting_task_up')->where(['createTime >=' => $beginDate . ' 00:00:00', 'createTime <=' => $endDate . ' 23:59:59', 'checkId' => 0, 'isDelete' => 0])->countAllResults() > 0) {
                return $this->response->setJSON(['status' => -1, 'msg' => '有该月配货交付未点货入库，无法重算！']);
            } else {
                $achievement = $this->db->table('bill_achievement')->where(['beginDate' => $beginDate, 'isDelete' => 0])->get()->getRowArray();
                $users = $this->db->table('user')
                    ->where('roleId', 0)
                    ->whereNotIn('name', ["系统","审查员"])
                    ->groupStart()
                        ->where('isDisable', 0)
                        ->orGroupStart()
                            ->where('isDisable', 1)
                            ->where('leaveDate >=', $beginDate)
                        ->groupEnd()
                    ->groupEnd()
                    ->where('isDelete', 0)
                    ->get()
                    ->getResultArray();
                $attendance = $this->db->table('bill_attendance')->where(['beginDate' => $beginDate, 'isDraft' => 0, 'isDelete' => 0])->get()->getRowArray();
                $attendance_info = $this->db->table('bill_attendance_info')->where(['iid' => $attendance['id'], 'isDelete' => 0])->get()->getResultArray();
                if ($attendance_info) {
                    $attendance_info = array_column($attendance_info, null, 'uName');
                }
                $evaluate_info = $this->db->table('bill_evaluate_info')->select('uName, score')->where(['beginDate' => $beginDate, 'isDelete' => 0])->groupBy('uName')->get()->getResultArray();
                if ($evaluate_info) {
                    $evaluate_info = array_column($evaluate_info, 'score', 'uName');
                }
                if ($achievement && !empty($users)) {
                    $this->db->transStart();
                    foreach ($users as $index => $user) {
                        // 采购业绩
                        $performancePur['orderinfo'] = $this->db->table('bill_order_info')
                            ->where('billDate >=', $beginDate)
                            ->where('billDate <=', $endDate)
                            ->where('billType', 'PUR')
                            ->where('uId', $user['id'])
                            ->where('checkId >', 0)
                            ->where('isDelete', 0)
                            ->countAllResults();

                        $performancePur['contractinfo'] = $this->db->table('bill_contract_info')
                            ->where('paidTime >=', $beginDate . ' 00:00:00')
                            ->where('paidTime <=', $endDate . ' 23:59:59')
                            ->where('billType', 'PUR')
                            ->where('isReq !=', 1)
                            ->where('uId', $user['id'])
                            ->where('price >', 0)
                            ->where('paidAmount >', 0)
                            ->where('isDelete', 0)
                            ->countAllResults();

                        $performancePur['supplier'] = $this->db->table('contact')
                            ->where('createTime >=', $beginDate . ' 00:00:00')
                            ->where('createTime <=', $endDate . ' 23:59:59')
                            ->where('uName', $user['name'])
                            ->where('type', 1)
                            ->where('isDisable', 0)
                            ->where('isDelete', 0)
                            ->countAllResults();

                        $performancePur['deAmount'] = floatval($this->db->table('bill_contract_info')
                            ->select('SUM(CASE WHEN (limitPrice * 1.25 * qty) <= paidAmount THEN 0 ELSE ((limitPrice * 1.25 * qty) - paidAmount) END) AS deAmount', false)
                            ->where('paidTime >=', $beginDate . ' 00:00:00')
                            ->where('paidTime <=', $endDate . ' 23:59:59')
                            ->where('isReq', 2)
                            ->where('uId', $user['id'])
                            ->where('billType', 'PUR')
                            ->where('limitPrice >', 0)
                            ->where('price >', 0)
                            ->where('paidAmount >', 0)
<<<<<<< HEAD
                            ->where('paidAmount >= amount', null, false)
=======
                            ->where('paidAmount >=', 'amount', false)
>>>>>>> 151b415 (更新语言最新版本+最新底层框架后全部差异化代码适配重写)
                            ->where('isDelete', 0)
                            ->get()
                            ->getRowArray()['deAmount'] ?? 0);

                        $performancePur['core'] = intval((float)$performancePur['orderinfo'] * (float)$this->myerp['expPar']['pur']['par1'] + (float)$performancePur['contractinfo'] * (float)$this->myerp['expPar']['pur']['par2'] + (float)$performancePur['supplier'] * (float)$this->myerp['expPar']['pur']['par3'] + (float)$performancePur['deAmount'] * (float)$this->myerp['expPar']['pur']['par4']);

                        // 销售业绩
                        $performanceSale['order'] = floatval($this->db->table('bill_order')
                            ->selectSum('amount')
                            ->where('createTime >=', $beginDate . ' 00:00:00')
                            ->where('createTime <=', $endDate . ' 23:59:59')
                            ->where('billType', 'SALE')
                            ->where('uId', $user['id'])
                            ->where('checkId >', 0)
                            ->where('moneyStatus1', 2)
                            ->where('isCancel', 0)
                            ->where('isDelete', 0)
                            ->get()->getRowArray()['amount'] ?? 0);
                        $performanceSale['contract'] =  floatval($this->db->table('bill_contract')
                            ->select('SUM(CASE WHEN LOCATE(",", saler) > 0 THEN (amount / ((CHAR_LENGTH(saler) - CHAR_LENGTH(REPLACE(saler,",",""))) + 1)) ELSE amount END) AS amount', false)
                            ->where('deliverDoneTime >=', $beginDate . ' 00:00:00')
                            ->where('deliverDoneTime <=', $endDate . ' 23:59:59')
                            ->where(['billType' => 'SALE', 'checkId >' => 0, 'goodsStatus1 >' => 0, 'redoSoType' => 0, 'isDelete' => 0])
                            ->where('FIND_IN_SET("' . $user['name'] . '", saler) >', 0, false)
                            ->get()
                            ->getRowArray()['amount'] ?? 0);
                        // 扣掉相应有退款单的退款金额
                        $orderRefundBills = $this->db->table('bill_order')
                            ->select('srcMoneyId2')
                            ->where('createTime >=', $beginDate . ' 00:00:00')
                            ->where('createTime <=', $endDate . ' 23:59:59')
                            ->where('billType', 'SALE')
                            ->where('uId', $user['id'])
                            ->where('checkId >', 0)
                            ->where('moneyStatus1', 2)
                            ->where('srcMoneyId2 >', 0)
                            ->where('isCancel', 0)
                            ->where('isDelete', 0)
                            ->get()
                            ->getResultArray();
                        if (!empty($orderRefundBills)) {
                            $orderRefundBillIds = array_column($orderRefundBills, 'srcMoneyId2');
                            $refundAmount = floatval($this->db->table('bill_money_info')
                                ->selectSum('amount')
                                ->whereIn('iid', $orderRefundBillIds)
                                ->where('isDelete', 0)
                                ->get()
                                ->getRowArray()['amount'] ?? 0);
                            $performanceSale['order'] -= $refundAmount;
                        }
                        $contractRefundBills = $this->db->table('bill_contract')
                            ->select('srcMoneyId2,saler')
                            ->where('deliverDoneTime >=', $beginDate . ' 00:00:00')
                            ->where('deliverDoneTime <=', $endDate . ' 23:59:59')
                            ->where('billType', 'SALE')
                            ->where('FIND_IN_SET("' . $user['name'] . '", saler) >', 0, false)
                            ->where('checkId >', 0)
                            ->where('goodsStatus1 >', 0)
                            ->where('redoSoType', 0)
                            ->where('srcMoneyId2 >', 0)
                            ->where('isDelete', 0)
                            ->get()
                            ->getResultArray();
                        if (!empty($contractRefundBills)) {
                            foreach ($contractRefundBills as $row) {
                                $refundAmount = floatval($this->db->table('bill_money_info')
                                    ->selectSum('amount')
                                    ->where('iid', $row['srcMoneyId2'])
                                    ->where('isDelete', 0)
                                    ->get()
                                    ->getRowArray()['amount'] ?? 0);
                                if (strpos($row['saler'], ',') !== false) {
                                    $salerArray = explode(',', $row['saler']);
                                    $salerCount = count($salerArray);
                                    if ($salerCount > 0) {
                                        $performanceSale['contract'] -= $refundAmount / $salerCount;
                                    }
                                } else {
                                    $performanceSale['contract'] -= $refundAmount;
                                }
                            }
                        }

                        $performanceSale['onlineshop'] = floatval($this->db->table('bill_onlineshopMp_info')
                            ->selectSum('amount')
                            ->where('beginDate >=', $beginDate)
                            ->where('endDate <=', $endDate)
                            ->where('uName', $user['name'])
                            ->where('isDelete', 0)
                            ->get()
                            ->getRowArray()['amount'] ?? 0);

                        $performanceSale['core'] = intval((float)$performanceSale['order'] * (float)$this->myerp['expPar']['sale']['par1'] + (float)$performanceSale['contract'] * (float)$this->myerp['expPar']['sale']['par2'] + (float)$performanceSale['onlineshop'] * (float)$this->myerp['expPar']['sale']['par3']);

                        // 生产业绩（配货+加工）
                        $pro_kitting_task_up_amount = floatval($this->db->table('pro_kitting_task_up')
                            ->selectSum('amount')
                            ->where('createTime >=', $beginDate . ' 00:00:00')
                            ->where('createTime <=', $endDate . ' 23:59:59')
                            ->where('doneStatus', 2)
                            ->where('uName', $user['name'])
                            ->where('isDelete', 0)
                            ->get()
                            ->getRowArray()['amount'] ?? 0);
                        $pro_processing_task_up_amount = floatval($this->db->table('pro_processing_task_up')
                            ->selectSum('amount')
                            ->where('createTime >=', $beginDate . ' 00:00:00')
                            ->where('createTime <=', $endDate . ' 23:59:59')
                            ->where('doneStatus', 2)
                            ->where('uName', $user['name'])
                            ->where('isDelete', 0)
                            ->get()
                            ->getRowArray()['amount'] ?? 0);
                        $performancePro['core'] = intval($pro_kitting_task_up_amount + $pro_processing_task_up_amount);

                        // 打包业绩
                        if (in_array($user['department'], ['仓储部', '生产部', '销售部'])) {
                            $performancePack = $this->db->table('deliver_bag')
                                ->select('COUNT(CASE WHEN weight < 20 THEN no ELSE null END) AS totalCount1, SUM(CASE WHEN weight < 20 THEN weight ELSE 0 END) AS totalWeight1, COUNT(CASE company WHEN "申通快递" THEN no ELSE null END) AS totalZYCount, SUM(CASE company WHEN "申通快递" THEN amount ELSE 0 END) AS totalZYAmount, COUNT(CASE WHEN weight >= 20 THEN no ELSE null END) AS totalCount2, SUM(CASE WHEN weight >= 20 THEN weight ELSE 0 END) AS totalWeight2', false)
                                ->where('scanTime >=', $beginDate . ' 00:00:00')
                                ->where('scanTime <=', $endDate . ' 23:59:59')
                                ->where('scanName', $user['name'])
                                ->where('isDelete', 0)
                                ->get()
                                ->getRowArray() ?? [
                                    'tatalCount1' => 0,
                                    'totalWeight1' => 0,
                                    'totalZYCount' => 0,
                                    'totalZYAmount' => 0,
                                    'tatalCount2' => 0,
                                    'totalWeight2' => 0
                                ];
                            $performancePack['core'] = intval((float)$performancePack['totalCount1'] * (float)$this->myerp['expPar']['pack']['par1'] + (float)$performancePack['totalWeight1'] * (float)$this->myerp['expPar']['pack']['par2'] + ((float)$this->myerp['expPar']['pack']['par3'] * (float)$performancePack['totalZYCount'] - (float)$performancePack['totalZYAmount']) * (float)$this->myerp['expPar']['pack']['par4'] + (float)$performancePack['totalCount2'] * (float)$this->myerp['expPar']['pack']['par5'] + (float)$performancePack['totalWeight2'] * (float)$this->myerp['expPar']['pack']['par6']);
                        } else {
                            $performancePack = [
                                'tatalCount1' => 0,
                                'totalWeight1' => 0,
                                'totalZYCount' => 0,
                                'totalZYAmount' => 0,
                                'tatalCount2' => 0,
                                'totalWeight2' => 0,
                                'core' => 0
                            ];
                        }
                        // 投标奖金
                        $bonusBid = 0;
                        // 投标档位奖金
                        $bidOffer = $this->db->table('bid_program')
                            ->select('SUM(CASE WHEN LOCATE(",", quotedName) > 0 THEN (amount / ((CHAR_LENGTH(quotedName) - CHAR_LENGTH(REPLACE(quotedName,",",""))) + 1)) ELSE amount END) AS amount, COUNT(*) AS offerCount', false)
                            ->where('createTime >=', $beginDate . ' 00:00:00')
                            ->where('createTime <=', $endDate . ' 23:59:59')
                            ->where('FIND_IN_SET("' . $user['name'] . '", quotedName) >', 0, false)
                            ->where('isDelete', 0)
                            ->get()
                            ->getRowArray() ?? ['amount' => 0, 'offerCount' => 0];

                        $bidWin = $this->db->table('bid_program')
                            ->select('SUM(CASE WHEN LOCATE(",", quotedName) > 0 THEN (amount / ((CHAR_LENGTH(quotedName) - CHAR_LENGTH(REPLACE(quotedName,",",""))) + 1)) ELSE amount END) AS winAmount, COUNT(*) AS winCount', false)
                            ->where('createTime >=', $beginDate . ' 00:00:00')
                            ->where('createTime <=', $endDate . ' 23:59:59')
                            ->where('FIND_IN_SET("' . $user['name'] . '", quotedName) >', 0, false)
                            ->where('status', 2)
                            ->where('isDelete', 0)
                            ->get()
                            ->getRowArray() ?? ['winAmount' => 0, 'winCount' => 0];
                        if ((float)$bidOffer['amount'] > 1000000 && (float)$bidWin['winAmount'] > 120000) {
                            $bonusBid += (float)$this->myerp['system']['bonusBid2'];
                        } elseif ((float)$bidOffer['amount'] > 500000 && (float)$bidWin['winAmount'] > 50000) {
                            $bonusBid += (float)$this->myerp['system']['bonusBid1'];
                        }
                        // 中标飞跃奖金
                        // 前两月中标金额
                        $winAmount_lastTwoMonth = floatval($this->db->table('bid_program')
                            ->select('SUM(CASE WHEN LOCATE(",", quotedName) > 0 THEN (amount / ((CHAR_LENGTH(quotedName) - CHAR_LENGTH(REPLACE(quotedName,",",""))) + 1)) ELSE amount END) AS winAmount', false)
                            ->where('createTime >=', date('Y-m-01', strtotime($beginDate . ' -1 month')) . ' 00:00:00')
                            ->where('createTime <=', $endDate . ' 23:59:59')
                            ->where('FIND_IN_SET("' . $user['name'] . '", quotedName) >', 0, false)
                            ->where('status', 2)
                            ->where('isDelete', 0)
                            ->get()
                            ->getRowArray()['winAmount'] ?? 0);
                        // 去年同期两月中标金额
                        $winAmount_lastYearSameTwoMonth = floatval($this->db->table('bid_program')
                            ->select('SUM(CASE WHEN LOCATE(",", quotedName) > 0 THEN (amount / ((CHAR_LENGTH(quotedName) - CHAR_LENGTH(REPLACE(quotedName,",",""))) + 1)) ELSE amount END) AS winAmount', false)
                            ->where('createTime >=', date('Y-m-01', strtotime($beginDate . ' -13 month')) . ' 00:00:00')
                            ->where('createTime <=', date('Y-m-t', strtotime($endDate . ' -12 month')) . ' 23:59:59')
                            ->where('FIND_IN_SET("' . $user['name'] . '", quotedName) >', 0, false)
                            ->where('status', 2)
                            ->where('isDelete', 0)
                            ->get()
                            ->getRowArray()['winAmount'] ?? 0);
                        if ($winAmount_lastTwoMonth > 0 && $winAmount_lastYearSameTwoMonth > 0 && ($winAmount_lastTwoMonth / $winAmount_lastYearSameTwoMonth) >= 1.39) {
                            $bonusBid += (float)$this->myerp['system']['bonusBidLeap'];
                        }
                        // 开始准备最终数据
                        $iid = $achievement['id'];
                        $workDays = (float)($attendance_info[$user['name']]['workDays'] ?? 0);
                        $workHours = (float)($attendance_info[$user['name']]['workHours'] ?? 0);
                        $fillHours = (float)($attendance_info[$user['name']]['fillHours'] ?? 0);
                        $overtimeHours = (float)($attendance_info[$user['name']]['overtimeHours'] ?? 0);
                        $recordHours = (float)($attendance_info[$user['name']]['recordHours'] ?? 0);
                        $hourlyWage = (float)($this->myerp['userLevel'][$user['levelId']]['hourlyWage'] ?? 0);
                        if (isset($attendance_info[$user['name']]['mustDayNum'])) {
                            $mustDayNum = (float)$attendance_info[$user['name']]['mustDayNum'];
                        } else {
                            $mustDayNum = floatval($this->db->table('attendance_plan')
                                ->select('m' . intval(date('m', strtotime($beginDate))))
                                ->where(['year' => intval(date('Y', strtotime($beginDate))), 'isDelete' => 0])
                                ->get()
                                ->getRowArray()['m' . intval(date('m', strtotime($beginDate)))] ?? 26); // 缺省兜底26天
                        }
                        $isFull = intval($attendance_info[$user['name']]['isFull'] ?? 0);
                        $dutyTimes = intval($attendance_info[$user['name']]['dutyTimes'] ?? 0);
                        $evaluateScore = ($evaluate_info && isset($evaluate_info[$user['name']])) ? intval($evaluate_info[$user['name']]) : 0;
                        $achievementPur = (float)$performancePur['core'];
                        $achievementSale = (float)$performanceSale['core'];
                        $achievementPro = (float)$performancePro['core'];
                        $achievementPack = (float)$performancePack['core'];
                        // 出勤率 最大限制为2（即200%），比按要求多上的班将自动计算多出时间的考评工资。
                        $workRate = min(floor(floor(($workHours + $fillHours) / 9 * 1000) / 1000 / $mustDayNum * 1000) / 1000, 2);
                        // 出勤工资 = 时薪 *（打卡 + 补班小时数）
                        $attendanceSalary = intval($hourlyWage * ($workHours + $fillHours));
                        // 考评工资 = 考评工资基数 * 出勤率（可能大于100%）*（目前暂不考虑考评分，然后采购部统一再*70%）
                        $evaluateSalary = intval((float)$user['fixedSalary'] * $workRate);
                        if ($user['department'] == '采购部' && $evaluateSalary > 0) {
                            $evaluateSalary = intval($evaluateSalary * 0.7);
                        }
                        if ($user['department'] == '生产部' && $evaluateSalary > 0) {
                            $evaluateSalary = intval($evaluateSalary * $evaluateScore / 100);
                        }
                        // 绩效工资 = 采购业绩指数 * 采购绩效工资转化系数 + 销售业绩指数 * 销售绩效工资转化系数 + 配货业绩指数 * 配货绩效工资转化系数 + 打包业绩指数 * 打包绩效工资转化系数（某些特殊情况下不该算打包业绩的人员无打包业绩）
                        $achievementSalary = intval(($achievementPur * (float)$this->myerp['expPar']['pur']['achievementSalaryRate']) + ($achievementSale * (float)$this->myerp['expPar']['sale']['achievementSalaryRate']) + ($achievementPro * (float)$this->myerp['expPar']['proKitting']['achievementSalaryRate']) + ($achievementPack * (float)$this->myerp['expPar']['pack']['achievementSalaryRate']));
                        // 销售值班工资 = 值班次数 * 销售每次值班工资
                        $dutySalary = $dutyTimes * (float)$this->myerp['system']['bonusDuty'];
                        // 加班工资 = 加班小时数 * 不同部门不同的加班时薪
                        $overtimeSalary = $overtimeHours * (in_array($user['department'], explode(',', $this->myerp['system']['priceWH1Department'])) ? (float)$this->myerp['system']['priceWH1'] : (float)$this->myerp['system']['priceWH2']);
                        // 计时工资 = 计时小时数 * 计时时薪
                        $recordSalary = $recordHours * (float)$this->myerp['system']['priceWR'];
                        // 全勤奖金
                        $bonusFullAtt = $isFull > 0 ? (float)$this->myerp['system']['bonusFullAttendance'] : 0;
                        // 投标奖金
                        $bonusBid = (float)$bonusBid;
                        // 岗位津贴
                        $bonusPerson = intval($user['bonusPerson']) * $workRate;
                        // 如果已参保且参保日期在上月底之前，或已停保但停保日期在上月底之后，则扣社保。
                        if (
                            ($user['isBuySocialSecurity'] == 1 && $user['buySocialSecurityDate'] <= $endDate) ||
                            ($user['isBuySocialSecurity'] == 2 && $user['stopSocialSecurityDate'] > $endDate)
                        ) {
                            // 社保扣费基数
                            $deductionSocialSecurity = $this->myerp['system']['deductionSocialSecurity'] ?? 372;
                            // 缺勤扣社保
                            if ((float)$attendance_info[$user['name']]['deSocialSecurityDayNum'] > 0) {
                                $deductionSocialSecurity = intval($deductionSocialSecurity) + intval((float)$attendance_info[$user['name']]['deSocialSecurityDayNum'] * 50);
                            }
                        } else {
                            $deductionSocialSecurity = 0;
                        }
                        // 其他扣除 提取反馈责任损失
                        $deductionOther = 0;
                        $serviceTarget = $this->db->table('service')
                            ->select('id,targetName,targetAmount')
                            ->where('beginDate >=', $beginDate . ' 00:00:00')
                            ->where('beginDate <=', $endDate . ' 23:59:59')
                            ->where('FIND_IN_SET("' . $user['name'] . '", targetName) >', 0 , false)
                            ->where('targetAmount >', 0)
                            ->where('disStatus', 3)
                            ->where('isDelete', 0)
                            ->get()
                            ->getResultArray();
                        $description = $attendance_info[$user['name']]['description'] ?? '';
                        if (is_array($serviceTarget) && count($serviceTarget) > 0) {
                            foreach ($serviceTarget as $arr => $row) {
                                $deduction = (float)$row['targetAmount'] / count(explode(',', $row['targetName']));
                                $deductionOther += $deduction;
                                $description .= '责任扣除' . ($arr + 1) . ': 客户之声记录ID: ' . $row['id'] . ',扣除: ' . $deduction . '元。';
                            }
                        }
                        // 汇总工资 = 出勤工资 + 考评工资 + 绩效工资 + 值班工资 + 加班工资 + 计时工资 + 全勤奖金 + 投标奖金 + 岗位津贴 - 社保扣费 - 其他扣除
                        $sumSalary = $attendanceSalary + $evaluateSalary + $achievementSalary + $dutySalary + $overtimeSalary + $recordSalary + $bonusFullAtt + $bonusBid + $bonusPerson - $deductionSocialSecurity - $deductionOther;

                        // 有则更新 无则插入
                        if ($this->db->table('bill_achievement_info')->where(['uId' => $user['id'], 'uName' => $user['name'], 'beginDate' => $beginDate])->countAllResults() > 0) {
                            $this->db->table('bill_achievement_info')
                                ->where(['uId' => $user['id'], 'uName' => $user['name'], 'beginDate' => $beginDate])
                                ->update([
                                    'department' => $user['department'],
                                    'position' => $user['position'],
                                    'level' => $user['level'],
                                    'hourlyWage' => $hourlyWage,
                                    'mustDayNum' => $mustDayNum,
                                    'workDays' => $workDays,
                                    'workHours' => $workHours,
                                    'workRate' => $workRate,
                                    'evaluateScore' => $evaluateScore,
                                    'isFull' => $isFull,
                                    'fillHours' => $fillHours,
                                    'overtimeHours' => $overtimeHours,
                                    'recordHours' => $recordHours,
                                    'dutyTimes' => $dutyTimes,
                                    'achievementPur' => $achievementPur,
                                    'achievementSale' => $achievementSale,
                                    'achievementPro' => $achievementPro,
                                    'achievementPack' => $achievementPack,
                                    'attendanceSalary' => $attendanceSalary,
                                    'evaluateSalary' => $evaluateSalary,
                                    'achievementSalary' => $achievementSalary,
                                    'dutySalary' => $dutySalary,
                                    'overtimeSalary' => $overtimeSalary,
                                    'recordSalary' => $recordSalary,
                                    'bonusFullAtt' => $bonusFullAtt,
                                    'bonusBid' => $bonusBid,
                                    'bonusPerson' => $bonusPerson,
                                    'deductionSocialSecurity' => $deductionSocialSecurity,
                                    'deductionOther' => $deductionOther,
                                    'sumSalary' => $sumSalary,
                                    'description' => $description
                                ]);
                        } else {
                            $this->db->table('bill_achievement_info')->insert([
                                'iid' => $iid,
                                'billNo' => $achievement['billNo'],
                                'beginDate' => $beginDate,
                                'endDate' => $endDate,
                                'uId' => $user['id'],
                                'uName' => $user['name'],
                                'department' => $user['department'],
                                'position' => $user['position'],
                                'level' => $user['level'],
                                'hourlyWage' => $hourlyWage,
                                'mustDayNum' => $mustDayNum,
                                'workDays' => $workDays,
                                'workHours' => $workHours,
                                'workRate' => $workRate,
                                'evaluateScore' => $evaluateScore,
                                'isFull' => $isFull,
                                'fillHours' => $fillHours,
                                'overtimeHours' => $overtimeHours,
                                'recordHours' => $recordHours,
                                'dutyTimes' => $dutyTimes,
                                'achievementPur' => $achievementPur,
                                'achievementSale' => $achievementSale,
                                'achievementPro' => $achievementPro,
                                'achievementPack' => $achievementPack,
                                'attendanceSalary' => $attendanceSalary,
                                'evaluateSalary' => $evaluateSalary,
                                'achievementSalary' => $achievementSalary,
                                'dutySalary' => $dutySalary,
                                'overtimeSalary' => $overtimeSalary,
                                'recordSalary' => $recordSalary,
                                'bonusFullAtt' => $bonusFullAtt,
                                'bonusBid' => $bonusBid,
                                'bonusPerson' => $bonusPerson,
                                'deductionSocialSecurity' => $deductionSocialSecurity,
                                'deductionOther' => $deductionOther,
                                'sumSalary' => $sumSalary,
                                'description' => $description
                            ]);
                        }
                    }
                    $this->myservice->log('一键重算了工资月表单，单号：' . $achievement['billNo']);
                    $this->db->transComplete();
                    if ($this->db->transStatus() === false) {
                        return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
                    }
                    return $this->response->setJSON(['status' => 200, 'msg' => '重算成功！']);
                }
                return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
            }
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '请选择年月！']);
    }
    // 绩效查询
    public function achievementSearch()
    {
        $uId = intval($this->request->getGetPost('uId'));
        $uName = $this->request->getGetPost('uName');
        $beginDate = $this->request->getGetPost('beginDate');
        $endDate = $this->request->getGetPost('endDate');
        $isSelf = intval($this->request->getGetPost('isSelf'));
        switch ($isSelf) {
            case 0:
                if (!$this->myservice->verifyRight('ACHIEVEMENT_SEARCH')) {
                    log_message('error', $this->myerp['uName'] . ' 访问 Bill/Achievement/achievementSearch 权限不足！');
                    return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
                }
                break;
            case 1:
                if ($uId != $this->myerp['uId'] || !$this->myservice->verifyRight('SYS_DEFAULT')) {
                    log_message('error', $this->myerp['uName'] . ' 访问 Bill/Achievement/achievementSearch 权限不足！');
                    return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
                }
                break;
            default:
                break;
        }
        if ($endDate > date('Y-m-d')) { // 结束日期不允许超今天
            $endDate = date('Y-m-d');
        }
        if ($isSelf > 0 && $endDate >= date('Y-m-01')) { // 如果是员工自查，且查本月，则只允许每5天展示前5天前的数据。
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
        if ($uId > 0 && $uName && $beginDate && $endDate) {
            $user = $this->db->table('user')->where(['id' => $uId])->get()->getRowArray();

            // 出勤信息
            $attendance = $this->db->table('bill_attendance_info')
                ->where([
                    'beginDate >=' => $beginDate,
                    'endDate <=' => $endDate,
                    'uName' => $uName,
                    'isDelete' => 0
                ])->get()
                ->getResultArray();

            // 考评得分
            $evaluateScore = intval($this->db->table('bill_evaluate_info')
                ->where([
                    'beginDate >=' => $beginDate,
                    'endDate <=' => $endDate,
                    'uName' => $uName,
                    'isDelete' => 0
                ])->get()
                ->getRowArray()['score'] ?? 0);

            //采购业绩
            $performancePur['orderinfo'] = $this->db->table('bill_order_info')
                ->where('billDate >=', $beginDate)
                ->where('billDate <=', $endDate)
                ->where('billType', 'PUR')
                ->where('uId', $uId)
                ->where('checkId >', 0)
                ->where('isDelete', 0)
                ->countAllResults();

            $performancePur['contractinfo'] = $this->db->table('bill_contract_info')
                ->where('paidTime >=', $beginDate . ' 00:00:00')
                ->where('paidTime <=', $endDate . ' 23:59:59')
                ->where('billType', 'PUR')
                ->where('isReq !=', 1)
                ->where('uId', $uId)
                ->where('price >', 0)
                ->where('paidAmount >', 0)
                ->where('isDelete', 0)
                ->countAllResults();

            $performancePur['supplier'] = $this->db->table('contact')
                ->where('createTime >=', $beginDate . ' 00:00:00')
                ->where('createTime <=', $endDate . ' 23:59:59')
                ->where('uName', $uName)
                ->where('type', 1)
                ->where('isDisable', 0)
                ->where('isDelete', 0)
                ->countAllResults();

            $performancePur['deAmount'] = floatval($this->db->table('bill_contract_info')
                ->select('SUM(CASE WHEN (limitPrice * 1.25 * qty) <= paidAmount THEN 0 ELSE ((limitPrice * 1.25 * qty) - paidAmount) END) AS deAmount', false)
                ->where('paidTime >=', $beginDate . ' 00:00:00')
                ->where('paidTime <=', $endDate . ' 23:59:59')
                ->where('isReq', 2)
                ->where('uId', $uId)
                ->where('billType', 'PUR')
                ->where('limitPrice >', 0)
                ->where('price >', 0)
                ->where('paidAmount >', 0)
<<<<<<< HEAD
                ->where('paidAmount >= amount', null, false)
=======
                ->where('paidAmount >=', 'amount', false)
>>>>>>> 151b415 (更新语言最新版本+最新底层框架后全部差异化代码适配重写)
                ->where('isDelete', 0)
                ->get()
                ->getRowArray()['deAmount'] ?? 0);

            $performancePur['core'] = intval((float)$performancePur['orderinfo'] * (float)$this->myerp['expPar']['pur']['par1'] + (float)$performancePur['contractinfo'] * (float)$this->myerp['expPar']['pur']['par2'] + (float)$performancePur['supplier'] * (float)$this->myerp['expPar']['pur']['par3'] + (float)$performancePur['deAmount'] * (float)$this->myerp['expPar']['pur']['par4']);

            // 销售业绩
            $performanceSale['bidpro'] = $this->db->table('bid_program')
                ->select('SUM(CASE WHEN LOCATE(",", quotedName) > 0 THEN (amount / ((CHAR_LENGTH(quotedName) - CHAR_LENGTH(REPLACE(quotedName,",",""))) + 1)) ELSE amount END) AS amount, COUNT(*) AS offerCount', false)
                ->where('createTime >=', $beginDate . ' 00:00:00')
                ->where('createTime <=', $endDate . ' 23:59:59')
                ->where('FIND_IN_SET("' . $uName . '", quotedName) >', 0, false)
                ->where('isDelete', 0)
                ->get()
                ->getRowArray() ?? ['amount' => 0, 'offerCount' => 0];

            $performanceSale['order'] = floatval($this->db->table('bill_order')
                ->select('amount')
                ->where('createTime >=', $beginDate . ' 00:00:00')
                ->where('createTime <=', $endDate . ' 23:59:59')
                ->where('billType', 'SALE')
                ->where('uId', $uId)
                ->where('checkId >', 0)
                ->where('moneyStatus1', 2)
                ->where('isCancel', 0)
                ->where('isDelete', 0)
                ->get()
                ->getRowArray()['amount'] ?? 0);

            $performanceSale['contract'] = floatval($this->db->table('bill_contract')
                ->select('SUM(CASE WHEN LOCATE(",", saler) > 0 THEN (amount / ((CHAR_LENGTH(saler) - CHAR_LENGTH(REPLACE(saler,",",""))) + 1) ) ELSE amount END) AS amount', false)
                ->where('deliverDoneTime >=', $beginDate . ' 00:00:00')
                ->where('deliverDoneTime <=', $endDate . ' 23:59:59')
                ->where('billType', 'SALE')
                ->where('FIND_IN_SET("' . $uName . '", saler) >', 0, false)
                ->where('checkId >', 0)
                ->where('goodsStatus1 >', 0)
                ->where('redoSoType', 0)
                ->where('isDelete', 0)
                ->get()
                ->getRowArray()['amount'] ?? 0);

            // 扣掉相应有退款单的退款金额
            $orderRefundBills = $this->db->table('bill_order')
                ->select('srcMoneyId2')
                ->where('createTime >=', $beginDate . ' 00:00:00')
                ->where('createTime <=', $endDate . ' 23:59:59')
                ->where('billType', 'SALE')
                ->where('uId', $uId)
                ->where('checkId >', 0)
                ->where('moneyStatus1', 2)
                ->where('srcMoneyId2 >', 0)
                ->where('isCancel', 0)
                ->where('isDelete', 0)
                ->get()
                ->getResultArray();
            if (!empty($orderRefundBills)) {
                $orderRefundBillIds = array_column($orderRefundBills, 'srcMoneyId2');
                $refundAmount = floatval($this->db->table('bill_money_info')
                    ->selectSum('amount')
                    ->whereIn('iid', $orderRefundBillIds)
                    ->where('isDelete', 0)
                    ->get()
                    ->getRowArray()['amount'] ?? 0);
                $performanceSale['order'] -= $refundAmount;
            }
            $contractRefundBills = $this->db->table('bill_contract')
                ->select('srcMoneyId2,saler')
                ->where('deliverDoneTime >=', $beginDate . ' 00:00:00')
                ->where('deliverDoneTime <=', $endDate . ' 23:59:59')
                ->where('billType', 'SALE')
                ->where('FIND_IN_SET("' . $uName . '", saler) >', 0, false)
                ->where('checkId >', 0)
                ->where('goodsStatus1 >', 0)
                ->where('redoSoType', 0)
                ->where('srcMoneyId2 >', 0)
                ->where('isDelete', 0)
                ->get()
                ->getResultArray();
            if (!empty($contractRefundBills)) {
                foreach ($contractRefundBills as $row) {
                    $refundAmount = floatval($this->db->table('bill_money_info')
                        ->selectSum('amount')
                        ->where('iid', $row['srcMoneyId2'])
                        ->where('isDelete', 0)
                        ->get()
                        ->getRowArray()['amount'] ?? 0);
                    if (strpos($row['saler'], ',') !== false) {
                        $salerArray = explode(',', $row['saler']);
                        $salerCount = count($salerArray);
                        if ($salerCount > 0) {
                            $performanceSale['contract'] -= $refundAmount / $salerCount;
                        }
                    } else {
                        $performanceSale['contract'] -= $refundAmount;
                    }
                }
            }

            $performanceSale['onlineshop'] = floatval($this->db->table('bill_onlineshopMp_info')
                ->selectSum('amount')
                ->where('beginDate >=', $beginDate)
                ->where('endDate <=', $endDate)
                ->where('uName', $uName)
                ->where('isDelete', 0)
                ->get()
                ->getRowArray()['amount'] ?? 0);

            $performanceSale['core'] = intval((float)$performanceSale['order'] * (float)$this->myerp['expPar']['sale']['par1'] + (float)$performanceSale['contract'] * (float)$this->myerp['expPar']['sale']['par2'] + (float)$performanceSale['onlineshop'] * (float)$this->myerp['expPar']['sale']['par3']);

            // 加工业绩（配货+加工）
            $pro_kitting_task_up_all = $this->db->table('pro_kitting_task_up')
                ->selectSum('upQty', 'proQtyAll')
                ->where('createTime >=', $beginDate . ' 00:00:00')
                ->where('createTime <=', $endDate . ' 23:59:59')
                ->where('uName', $uName)
                ->where('isDelete', 0)
                ->get()
                ->getRowArray() ?? ['proQtyAll' => 0];
            $pro_kitting_task_up_uncheck = $this->db->table('pro_kitting_task_up')
                ->selectSum('upQty', 'proQtyUncheck')
                ->where('createTime >=', $beginDate . ' 00:00:00')
                ->where('createTime <=', $endDate . ' 23:59:59')
                ->where('doneStatus <', 2)
                ->where('uName', $uName)
                ->where('isDelete', 0)
                ->get()
                ->getRowArray() ?? ['proQtyUncheck' => 0];
            $pro_kitting_task_up_checked = $this->db->table('pro_kitting_task_up')
                ->selectSum('upQty', 'proQty')
                ->selectSum('amount', 'proAmount')
                ->where('createTime >=', $beginDate . ' 00:00:00')
                ->where('createTime <=', $endDate . ' 23:59:59')
                ->where('doneStatus', 2)
                ->where('uName', $uName)
                ->where('isDelete', 0)
                ->get()
                ->getRowArray() ?? ['proQty' => 0, 'proAmount' => 0];
            $pro_processing_task_up_all = $this->db->table('pro_processing_task_up')
                ->selectSum('upQty', 'proQtyAll')
                ->where('createTime >=', $beginDate . ' 00:00:00')
                ->where('createTime <=', $endDate . ' 23:59:59')
                ->where('uName', $uName)
                ->where('isDelete', 0)
                ->get()
                ->getRowArray() ?? ['proQtyAll' => 0];
            $pro_processing_task_up_uncheck = $this->db->table('pro_processing_task_up')
                ->selectSum('upQty', 'proQtyUncheck')
                ->where('createTime >=', $beginDate . ' 00:00:00')
                ->where('createTime <=', $endDate . ' 23:59:59')
                ->where('doneStatus <', 2)
                ->where('uName', $uName)
                ->where('isDelete', 0)
                ->get()
                ->getRowArray() ?? ['proQtyUncheck' => 0];
            $pro_processing_task_up_checked = $this->db->table('pro_processing_task_up')
                ->selectSum('upQty', 'proQty')
                ->selectSum('amount', 'proAmount')
                ->where('createTime >=', $beginDate . ' 00:00:00')
                ->where('createTime <=', $endDate . ' 23:59:59')
                ->where('doneStatus', 2)
                ->where('uName', $uName)
                ->where('isDelete', 0)
                ->get()
                ->getRowArray() ?? ['proQty' => 0, 'proAmount' => 0];
            $performancePro['proKittingQtyAll'] = intval($pro_kitting_task_up_all['proQtyAll']);
            $performancePro['proKittingQtyUncheck'] = intval($pro_kitting_task_up_uncheck['proQtyUncheck']);
            $performancePro['proKittingQty'] = intval($pro_kitting_task_up_checked['proQty']);
            $performancePro['proProcessingQtyAll'] = intval($pro_processing_task_up_all['proQtyAll']);
            $performancePro['proProcessingQtyUncheck'] = intval($pro_processing_task_up_uncheck['proQtyUncheck']);
            $performancePro['proProcessingQty'] = intval($pro_processing_task_up_checked['proQty']);
            $performancePro['core'] = intval((float)$pro_kitting_task_up_checked['proAmount'] + (float)$pro_processing_task_up_checked['proAmount']);

            // 打包业绩
            if (in_array($user['department'], ['仓储部', '生产部', '销售部'])) {
                $performancePack = $this->db->table('deliver_bag')
                    ->select('COUNT(CASE WHEN weight < 20 THEN no ELSE null END) AS totalCount1, SUM(CASE WHEN weight < 20 THEN weight ELSE 0 END) AS totalWeight1, COUNT(CASE company WHEN "申通快递" THEN no ELSE null END) AS totalZYCount, SUM(CASE company WHEN "申通快递" THEN amount ELSE 0 END) AS totalZYAmount, COUNT(CASE WHEN weight >= 20 THEN no ELSE null END) AS totalCount2, SUM(CASE WHEN weight >= 20 THEN weight ELSE 0 END) AS totalWeight2', false)
                    ->where('scanTime >=', $beginDate . ' 00:00:00')
                    ->where('scanTime <=', $endDate . ' 23:59:59')
                    ->where('scanName', $uName)
                    ->where('isDelete', 0)
                    ->get()
                    ->getRowArray() ?? [
                        'totalCount1' => 0,
                        'totalWeight1' => 0,
                        'totalZYCount' => 0,
                        'totalZYAmount' => 0,
                        'totalCount2' => 0,
                        'totalWeight2' => 0
                    ];
                $performancePack['core'] = intval((float)$performancePack['totalCount1'] * (float)$this->myerp['expPar']['pack']['par1'] + (float)$performancePack['totalWeight1'] * (float)$this->myerp['expPar']['pack']['par2'] + ((float)$this->myerp['expPar']['pack']['par3'] * (float)$performancePack['totalZYCount'] - (float)$performancePack['totalZYAmount']) * (float)$this->myerp['expPar']['pack']['par4'] + (float)$performancePack['totalCount2'] * (float)$this->myerp['expPar']['pack']['par5'] + (float)$performancePack['totalWeight2'] * (float)$this->myerp['expPar']['pack']['par6']);
            } else {
                $performancePack = [
                    'totalCount1' => 0,
                    'totalWeight1' => 0,
                    'totalZYCount' => 0,
                    'totalZYAmount' => 0,
                    'totalCount2' => 0,
                    'totalWeight2' => 0,
                    'core' => 0
                ];
            }

            // 产品开发
            $workTask = $this->db->table('goods_task')
                ->where('beginDate >=', $beginDate)
                ->where('beginDate <=', $endDate)
                ->groupStart()
                    ->where('leader', $uName)
<<<<<<< HEAD
                    ->orWhere('FIND_IN_SET(' . $this->db->escape($uName) . ', other) >', 0, false)
=======
                    ->orWhere("FIND_IN_SET('". $uName ."', other) >", 0, false)
>>>>>>> 151b415 (更新语言最新版本+最新底层框架后全部差异化代码适配重写)
                ->groupEnd()
                ->where('isDelete', 0)
                ->countAllResults();

            // 反馈责任
            $serviceTarget = $this->db->table('service')
                ->where('beginDate >=', $beginDate . ' 00:00:00')
                ->where('beginDate <=', $endDate . ' 23:59:59')
<<<<<<< HEAD
                ->where('FIND_IN_SET(' . $this->db->escape($uName) . ', targetName) >', 0, false)
=======
                ->where("FIND_IN_SET('". $uName ."', targetName) >", 0, false)
>>>>>>> 151b415 (更新语言最新版本+最新底层框架后全部差异化代码适配重写)
                ->where('targetAmount >', 0)
                ->where('disStatus', 3)
                ->where('isDelete', 0)
                ->orderBy('id', 'ASC')
                ->get()
                ->getResultArray();
            if (!empty($serviceTarget)) {
                foreach ($serviceTarget as $arr => $row) {
                    $serviceTarget[$arr]['content'] =  strip_tags(str_replace('<i class="close iconfont icon-shanchu"></i></div>', '&nbsp;&nbsp;&#10;', str_replace('<div class="info-time">', '）', str_replace('<div class="info-username">', '（', $row['content']))));
                }
            }

            // 工作目标
            $workPlan = $this->db->table('plan')
                ->where('date >=', $beginDate)
                ->where('date <=', $endDate)
                ->where('uId', $uId)
                ->where('isDelete', 0)
                ->orderBy('id', 'ASC')
                ->get()
                ->getResultArray();

            // 组装返回数据
            $json['status'] = 200;
            $json['attendance'] = $attendance;
            $json['evaluateScore'] = $evaluateScore;
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
        return $this->response->setJSON(['status' => -1, 'msg' => '提交的是空数据！']);
    }
}
