<?php

namespace App\Controllers\Bill;
use App\Controllers\BaseController;

class Deliver extends BaseController
{
    // 列表
    public function list()
    {
        if (!$this->myservice->verifyRight('DELIVER_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/Deliver/list 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $page = max(intval($this->request->getGetPost('page')), 1);
        $rows = max(intval($this->request->getGetPost('rows')), 100);
        $sidx = $this->request->getGetPost('sidx');
        $sord = strtoupper((string)$this->request->getGetPost('sord')) === 'ASC' ? 'ASC' : 'DESC';
        $beginDate = $this->request->getGetPost('beginDate');
        $endDate = $this->request->getGetPost('endDate');
        $skey = $this->request->getGetPost('skey');
        $builder = $this->db->table('bill_deliver');
        $builder->where('isDelete', 0);
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
                    ->orLike('company', $this->db->escapeLikeString($kw))
                    ->orLike('srcMoneyNo1', $this->db->escapeLikeString($kw))
                    ->orLike('srcMoneyNo2', $this->db->escapeLikeString($kw))
                    ->orLike('description', $this->db->escapeLikeString($kw))
                ->groupEnd();
            }
        }
        $allowedSort = ['id','billDate','billNo','company','deliverDate','amount1','amount2','amount','description','uName','checkId','moneyStatus1','moneyStatus2','createTime','invoiceNum'];
        $orderField  = $sidx ? (in_array($sidx, $allowedSort, true) ? $sidx : 'id') : 'id';
        $list = (clone $builder)->orderBy($orderField, $sord)->limit($rows)->offset($rows * ($page - 1))->get()->getResultArray();
        $total = $builder->countAllResults();
        $v = [];
        foreach ($list as $arr => $row) {
            $v[$arr]['id'] = intval($row['id']);
            $v[$arr]['billDate'] = $row['billDate'];
            $v[$arr]['billNo'] = $row['billNo'];
            $v[$arr]['transType'] = 109101;
            $v[$arr]['company'] = $row['company'];
            $v[$arr]['deliverDate'] = $row['deliverDate'];
            $v[$arr]['amount1'] = (float)abs($row['amount1']);
            $v[$arr]['amount2'] = (float)abs($row['amount2']);
            $v[$arr]['amount'] = (float)abs($row['amount']);
            $v[$arr]['uName'] = $row['uName'];
            $v[$arr]['checkId'] = intval($row['checkId']);
            $v[$arr]['contactId'] = intval($row['contactId']);
            $v[$arr]['moneyStatus1'] = intval($row['moneyStatus1']);
            $v[$arr]['srcMoneyId1'] = intval($row['srcMoneyId1']);
            $v[$arr]['srcMoneyNo1'] = $row['srcMoneyNo1'];
            $v[$arr]['moneyStatus2'] = intval($row['moneyStatus2']);
            $v[$arr]['srcMoneyId2'] = intval($row['srcMoneyId2']);
            $v[$arr]['srcMoneyNo2'] = $row['srcMoneyNo2'];
            $v[$arr]['description'] = $row['description'];
            $v[$arr]['createTime'] = $row['createTime'];
            $v[$arr]['modifyTime'] = $row['modifyTime'];
            $v[$arr]['invoiceNum'] = intval($row['invoiceNum']);
            $v[$arr]['invoiceAmount'] = $row['invoiceAmount'];
            $v[$arr]['isIgnoreInvoice'] = intval($row['isIgnoreInvoice']);
            $v[$arr]['ignoreInvoiceReason'] = $row['ignoreInvoiceReason'];
            $v[$arr]['disagreeIgnoreReason'] = $row['disagreeIgnoreReason'];
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
    // 详情导出
    public function export()
    {
        if (!$this->myservice->verifyRight('DELIVER_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/Deliver/export 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $data = $this->db->table('bill_deliver')->where('id', $id)->get()->getRowArray();
        $list = $this->db->table('bill_deliver_info')->where(['iid' => $id, 'isDelete' => 0])->orderBy('id', 'ASC')->get()->getResultArray();
        $name = '快递月结单_' . $data['company'] . '_' . $data['deliverDate'];

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
        $activeWorksheet->getStyle('A1:K' . (COUNT($list) + 4))->applyFromArray($styleArray, false);

        // 设置加粗
        $activeWorksheet->getStyle('A1')->getFont()->setBold(true);
        $activeWorksheet->getStyle('A4:K4')->getFont()->setBold(true);

        // 设置背景色
        $activeWorksheet->getStyle('A4:K4')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
        $activeWorksheet->getStyle('A4:K4')->getFill()->getStartColor()->setRGB('F5F5F5');

        // 合并单元格
        $activeWorksheet->mergeCells('A1:K1');
        $activeWorksheet->mergeCells('B3:K3');

        // 设置行高
        $activeWorksheet->getRowDimension('1')->setRowHeight(40);

        // 设置水平居中
        $activeWorksheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // 设置每列列宽
        $activeWorksheet->getColumnDimension('A')->setWidth(12);
        $activeWorksheet->getColumnDimension('B')->setWidth(25);
        $activeWorksheet->getColumnDimension('C')->setWidth(12);
        $activeWorksheet->getColumnDimension('D')->setWidth(15);
        $activeWorksheet->getColumnDimension('E')->setWidth(15);
        $activeWorksheet->getColumnDimension('F')->setWidth(15);
        $activeWorksheet->getColumnDimension('G')->setWidth(15);
        $activeWorksheet->getColumnDimension('H')->setWidth(15);
        $activeWorksheet->getColumnDimension('I')->setWidth(15);
        $activeWorksheet->getColumnDimension('J')->setWidth(15);
        $activeWorksheet->getColumnDimension('K')->setWidth(20);

        //设置当前的sheet
        $activeWorksheet->setCellValue('A1', $name . '（导出时间 ' . date('Y-m-d H:i:s') . '）');
        $activeWorksheet->setCellValue('A2', '单据编号');
        $activeWorksheet->setCellValue('B2', $data['billNo']);
        $activeWorksheet->setCellValue('C2', '统计月份');
        $activeWorksheet->setCellValue('D2', $data['deliverDate']);
        $activeWorksheet->setCellValue('E2', '贵方对账总额');
        $activeWorksheet->setCellValue('F2', $data['amount1']);
        $activeWorksheet->setCellValue('G2', '我方校对总额');
        $activeWorksheet->setCellValue('H2', $data['amount2']);
        $activeWorksheet->setCellValue('I2', '最终需付总额');
        $activeWorksheet->setCellValue('J2', $data['amount']);
        $activeWorksheet->setCellValue('A3', '备注');
        $activeWorksheet->setCellValue('B3', $data['description']);
        $activeWorksheet->setCellValue('A4', '快递公司');
        $activeWorksheet->setCellValue('B4', '运单号');
        $activeWorksheet->setCellValue('C4', '入网日期');
        $activeWorksheet->setCellValue('D4', '对方目的地');
        $activeWorksheet->setCellValue('E4', '对方计重(KG)');
        $activeWorksheet->setCellValue('F4', '对方计费(元)');
        $activeWorksheet->setCellValue('G4', '我方有无此单');
        $activeWorksheet->setCellValue('H4', '我方目的地');
        $activeWorksheet->setCellValue('I4', '我方计重(KG)');
        $activeWorksheet->setCellValue('J4', '我方计费(元)');
        $activeWorksheet->setCellValue('K4', '打印时间');

        $i = 5;
        foreach ($list as $k => $v) {
            $activeWorksheet->setCellValue('A' . $i, $v['company']);
            $activeWorksheet->setCellValueExplicit('B' . $i, $v['no'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $activeWorksheet->setCellValue('C' . $i, $v['date']);
            $activeWorksheet->setCellValue('D' . $i, $v['address1']);
            $activeWorksheet->setCellValue('E' . $i, $v['weight1']);
            $activeWorksheet->setCellValue('F' . $i, $v['amount1']);
            $activeWorksheet->setCellValue('G' . $i, $v['noCheck'] ? '有' : '无');
            $activeWorksheet->setCellValue('H' . $i, $v['address2']);
            $activeWorksheet->setCellValue('I' . $i, $v['weight2']);
            $activeWorksheet->setCellValue('J' . $i, $v['amount2']);
            $activeWorksheet->setCellValue('K' . $i, $v['printTime'] == '0000-00-00 00:00:00' ? '' : $v['printTime']);
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
    // 可疑运单导出
    public function export_top()
    {
        if (!$this->myservice->verifyRight('DELIVER_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/Deliver/export_top 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $data = $this->db->table('bill_deliver')->where('id', $id)->get()->getRowArray();
        $list = $this->db->table('bill_deliver_info')
            ->where('iid', $id)
            ->groupStart()
                ->where('noCheck', 0)
                ->orWhere('amount1 > amount2', null, false)
            ->groupEnd()
            ->where('isDelete', 0)
            ->orderBy('id', 'ASC')
            ->get()
            ->getResultArray();
        $name = '快递月结单_' . $data['company'] . '_' . $data['deliverDate'] . '_可疑运单';

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
        $activeWorksheet->getStyle('A1:K' . (COUNT($list) + 4))->applyFromArray($styleArray, false);

        // 设置加粗
        $activeWorksheet->getStyle('A1')->getFont()->setBold(true);
        $activeWorksheet->getStyle('A4:K4')->getFont()->setBold(true);

        // 设置背景色
        $activeWorksheet->getStyle('A4:K4')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
        $activeWorksheet->getStyle('A4:K4')->getFill()->getStartColor()->setRGB('F5F5F5');

        // 合并单元格
        $activeWorksheet->mergeCells('A1:K1');
        $activeWorksheet->mergeCells('B3:K3');

        // 设置行高
        $activeWorksheet->getRowDimension('1')->setRowHeight(40);

        // 设置第水平居中
        $activeWorksheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // 设置每列列宽
        $activeWorksheet->getColumnDimension('A')->setWidth(12);
        $activeWorksheet->getColumnDimension('B')->setWidth(25);
        $activeWorksheet->getColumnDimension('C')->setWidth(15);
        $activeWorksheet->getColumnDimension('D')->setWidth(15);
        $activeWorksheet->getColumnDimension('E')->setWidth(15);
        $activeWorksheet->getColumnDimension('F')->setWidth(15);
        $activeWorksheet->getColumnDimension('G')->setWidth(15);
        $activeWorksheet->getColumnDimension('H')->setWidth(15);
        $activeWorksheet->getColumnDimension('I')->setWidth(15);
        $activeWorksheet->getColumnDimension('J')->setWidth(15);
        $activeWorksheet->getColumnDimension('K')->setWidth(20);

        //设置当前的sheet
        $activeWorksheet->setCellValue('A1', $name . '（导出时间 ' . date('Y-m-d H:i:s') . '）');
        $activeWorksheet->setCellValue('A2', '单据编号');
        $activeWorksheet->setCellValue('B2', $data['billNo']);
        $activeWorksheet->setCellValue('C2', '统计月份');
        $activeWorksheet->setCellValue('D2', $data['deliverDate']);
        $activeWorksheet->setCellValue('E2', '贵方对账总额');
        $activeWorksheet->setCellValue('F2', $data['amount1']);
        $activeWorksheet->setCellValue('G2', '我方校对总额');
        $activeWorksheet->setCellValue('H2', $data['amount2']);
        $activeWorksheet->setCellValue('I2', '最终需付总额');
        $activeWorksheet->setCellValue('J2', $data['amount']);
        $activeWorksheet->setCellValue('A3', '备注');
        $activeWorksheet->setCellValue('B3', $data['description']);
        $activeWorksheet->setCellValue('A4', '快递公司');
        $activeWorksheet->setCellValue('B4', '运单号');
        $activeWorksheet->setCellValue('C4', '入网日期');
        $activeWorksheet->setCellValue('D4', '对方目的地');
        $activeWorksheet->setCellValue('E4', '对方计重(KG)');
        $activeWorksheet->setCellValue('F4', '对方计费(元)');
        $activeWorksheet->setCellValue('G4', '我方有无此单');
        $activeWorksheet->setCellValue('H4', '我方目的地');
        $activeWorksheet->setCellValue('I4', '我方计重(KG)');
        $activeWorksheet->setCellValue('J4', '我方计费(元)');
        $activeWorksheet->setCellValue('K4', '打印时间');

        $i = 5;
        foreach ($list as $k => $v) {
            $activeWorksheet->setCellValue('A' . $i, $v['company']);
            $activeWorksheet->setCellValueExplicit('B' . $i, $v['no'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $activeWorksheet->setCellValue('C' . $i, $v['date']);
            $activeWorksheet->setCellValue('D' . $i, $v['address1']);
            $activeWorksheet->setCellValue('E' . $i, $v['weight1']);
            $activeWorksheet->setCellValue('F' . $i, $v['amount1']);
            $activeWorksheet->setCellValue('G' . $i, $v['noCheck'] ? '有' : '无');
            $activeWorksheet->setCellValue('H' . $i, $v['address2']);
            $activeWorksheet->setCellValue('I' . $i, $v['weight2']);
            $activeWorksheet->setCellValue('J' . $i, $v['amount2']);
            $activeWorksheet->setCellValue('K' . $i, $v['printTime'] == '0000-00-00 00:00:00' ? '' : $v['printTime']);
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
    // 查询
    public function query()
    {
        if (!$this->myservice->verifyRight('DELIVER_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/Deliver/query 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $type = $this->request->getGetPost('type');
        $no = $this->request->getGetPost('no');
        $weight = (float)$this->request->getGetPost('weight');
        $amount = (float)$this->request->getGetPost('amount');
        $data = $this->db->table('bill_deliver')->where('id', $id)->where('isDelete', 0)->get()->getRowArray();
        if ($data) {
            $info['id'] = intval($data['id']);
            $info['billDate'] = $data['billDate'];
            $info['billNo'] = $data['billNo'];
            $info['company'] = $data['company'];
            $info['deliverDate'] = $data['deliverDate'];
            $info['amount1'] = (float)$data['amount1'];
            $info['amount2'] = (float)$data['amount2'];
            $info['amount'] = (float)$data['amount'];
            $info['uId'] = intval($data['uId']);
            $info['uName'] = $data['uName'];
            $info['checkId'] = intval($data['checkId']);
            $info['contactId'] = intval($data['contactId']);
            $info['checkName'] = $data['checkName'] ?? '';
            $info['description'] = $data['description'];
            $info['createTime'] = $data['createTime'];
            $info['modifyTime'] = $data['modifyTime'];
            $info['moneyStatus1'] = intval($data['moneyStatus1']);
            $info['moneyStatus2'] = intval($data['moneyStatus2']);
            $info['srcMoneyId1'] = intval($data['srcMoneyId1']);
            $info['srcMoneyNo1'] = $data['srcMoneyNo1'];
            $info['srcMoneyId2'] = intval($data['srcMoneyId2']);
            $info['srcMoneyNo2'] = $data['srcMoneyNo2'];
            $info['isDraft'] = intval($data['isDraft']);

            $builder = $this->db->table('bill_deliver_info');
            $builder->where('iid', $id);
            if ('warning' == $type) {
                $builder->groupStart()
                    ->where('noCheck', 0)
                    ->orWhere('amount1 > amount2', null, false)
                ->groupEnd();
            } elseif ('normal' == $type) {
                $builder->where('noCheck', 1)->where('amount1 <= amount2', null, false);
            } else {
                $builder->where('noCheck', 1)->where('amount1 <= amount2', null, false)->where('amount1 >=', 25);
            }
            if ($no) {
                $builder->where('no', $no);
            }
            if (0 < $weight) {
                $builder->where('weight1 >', $weight);
            }
            if (0 < $amount) {
                $builder->where('amount1 >', $amount);
            }
            $builder->where('isDelete', 0);
            $list = $builder->get()->getResultArray();
            $v = [];
            foreach ($list as $arr => $row) {
                $v[$arr]['id'] = intval($row['id']);
                $v[$arr]['iid'] = intval($row['iid']);
                $v[$arr]['no'] = $row['no'];
                $v[$arr]['noCheck'] = intval($row['noCheck']);
                $v[$arr]['company'] = $row['company'];
                $v[$arr]['date'] = $row['date'];
                $v[$arr]['address1'] = $row['address1'];
                $v[$arr]['address2'] = $row['address2'];
                $v[$arr]['weight1'] = (float)$row['weight1'];
                $v[$arr]['weight2'] = (float)$row['weight2'];
                $v[$arr]['amount1'] = (float)$row['amount1'];
                $v[$arr]['amount2'] = (float)$row['amount2'];
                $v[$arr]['printTime'] = '0000-00-00 00:00:00' == $row['printTime'] ? '' : $row['printTime'];
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
    // 更新
    public function update()
    {
        if (!$this->myservice->verifyRight('DELIVER_UPDATE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/Deliver/update 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $data = $this->request->getGetPost('postData');
        if (strlen($data) > 0) {
            $data = $this->validform((array)json_decode($data, true));
            // 如果返回的是 Response 对象，直接返回它
            if ($data instanceof \CodeIgniter\HTTP\Response) {
                return $data;
            }
            $oldData = $this->db->table('bill_deliver')->where('id', $data['id'])->where('isDelete', 0)->get()->getRowArray();
            $info = array_intersect_key($data, array_flip(['amount', 'description', 'modifyTime', 'isDraft']));
            $this->db->transStart();
            $this->db->table('bill_deliver')->where('id', $data['id'])->update($info);
            $this->myservice->log('编辑了快递月结单' . ($data['isDraft'] == 1 ? '草稿' : '') . '，' . (($oldData['isDraft'] == 1 && $data['isDraft'] == 0) ? '并将草稿转为了正式，' : '') . '单号：' . $data['billNo']);
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
        if (!$this->myservice->verifyRight('DELIVER_DELETE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/Deliver/delete 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $data = $this->db->table('bill_deliver')->where('id', $id)->get()->getRowArray();
        if ($data) {
            if (!$this->myservice->verifyRight('SYS_ADMIN') && $data['uId'] != $this->myerp['uId']) {
                return $this->response->setJSON(['status' => -1, 'msg' => '不能删除别人录入的快递月结单！仅本人或管理员可删除！']);
            }
            if ($data['checkId'] > 0) {
                return $this->response->setJSON(['status' => -1, 'msg' => '已审核的单据不可随意删除！']);
            }
            if ($data['srcMoneyId1'] > 0) {
                return $this->response->setJSON(['status' => -1, 'msg' => '已发生过付款，请先删除付款单！']);
            }
            if ($data['srcMoneyId2'] > 0) {
                return $this->response->setJSON(['status' => -1, 'msg' => '已发生过退款，请先删除退款单！']);
            }
            $this->db->transStart();
            // 真删除
            $this->db->table('bill_deliver')->where('id', $id)->delete();
            $this->db->table('bill_deliver_info')->where('iid', $id)->delete();
            $this->myservice->log('删除了快递月结单' . ($data['isDraft'] == 1 ? '草稿' : '') . '，单号：' . $data['billNo']);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '删除成功！']);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
    // 审核
    public function check()
    {
        if (!$this->myservice->verifyRight('DELIVER_CHECK')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/Deliver/check 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $checkId = intval($this->request->getGetPost('checkId'));
        $checkName = $this->request->getGetPost('checkName');
        $billNo = $this->request->getGetPost('billNo');
        $transType = intval($this->request->getGetPost('transType'));
        if ($id > 0) {
            $this->db->transStart();
            $this->db->table('bill_deliver')->where('id', $id)->update(['checkId ' => $checkId, 'checkName' => $checkName]);
            $this->myservice->log(($checkId > 0 ? '审核了' : '反审核了') . '快递月结单，单号：' . $billNo);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '操作成功！']);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
    // 更新备注
    public function updatedes()
    {
        if (!$this->myservice->verifyRight('DELIVER_UPDATE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/Deliver/updatedes 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $newDes = $this->request->getGetPost('newDes');
        $data = $this->db->table('bill_deliver')->where('id', $id)->get()->getRowArray();
        if ($data) {
            $v['description'] = $newDes;
            $v['modifyTime'] = date("Y-m-d H:i:s");
            $this->db->transStart();
            $this->db->table('bill_deliver')->where('id', $id)->update($v);
            $this->myservice->log('单独更新了快递月结单的备注，单号：' . $data['billNo']);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
             return $this->response->setJSON(['status' => 200, 'msg' => '更新成功！', 'data' => ['id' => $id]]);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
    // 导入对方对账单表格
    public function uploadExcel_1()
    {
        if (!$this->myservice->verifyRight('DELIVER_ADD')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/Deliver/uploadExcel_1 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        set_time_limit(0); // 设置脚本运行时间无限制，直到脚本运行完毕
        session_write_close(); // 主动关闭session读写保护来解除锁定，避免其他访问无法读写session造成阻塞引起假死
        if (empty($_FILES)){
            return $this->response->setJSON(['status' => -1, 'msg' => '没有上传文件！']);
        };
        $uppath = 'data/upfile/deliver/excel_temp/';
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

        // 调用spreadsheet处理表格
        $inputFileName = $uppath . $target_file; // 要读取的xls文件路径
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($inputFileName);
        $sheetCount = $spreadsheet->getSheetCount();

        $deliver = $deliverNos = [];
        $iid = $amount1 = 0;

        $this->db->transBegin();

        for ($curSheet = 0; $curSheet <= $sheetCount - 1; $curSheet++) { //循环sheet工作表的总个数
            $deliver_info = [];
            $deliverbag = [];
            $sheet = $spreadsheet->getSheet($curSheet); //当前工作表
            $highestRow = $sheet->getHighestRow(); //取得总行数
            $highestColumn = $sheet->getHighestColumn(); //取得总列数(格式：'AA')
            $columns = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn); //将数据总列数(格式：'AA')转换成数字列数(格式：27)表示
            $sheetData = $sheet->toArray(null, true, true, true); // 当前活动表格调用toArray方法，得到表格的二维数组
            if ($columns < 5) continue; // 小于5列的工作表判定为无效列表，不参与计算
            if ($curSheet == 0) {
                $title = str_replace(' ', '', $sheetData[1]['A']);
                if (strpos($title, '快递公司名称') !== false) { //是模板表格
                    if ($sheetData[1]['B'] && strpos(mb_substr($sheetData[1]['B'], 0, 2, 'utf-8'), '顺丰') !== false) {
                        $deliver['company'] = '顺丰速运';
                        $deliver['contactId'] = 25771;
                        $deliver['deliverDate'] = trim($sheetData[1]['D'] ?? '');
                    } elseif ($sheetData[1]['B'] && strpos(mb_substr($sheetData[1]['B'], 0, 2, 'utf-8'), '邮政') !== false) {
                        $deliver['company'] = '邮政快递';
                        $deliver['contactId'] = 14873;
                        $deliver['deliverDate'] = trim($sheetData[1]['D'] ?? '');
                    } elseif ($sheetData[1]['B'] && strpos(mb_substr($sheetData[1]['B'], 0, 2, 'utf-8'), '京东') !== false) {
                        $deliver['company'] = '京东快递';
                        $deliver['contactId'] = 42073;
                        $deliver['deliverDate'] = trim($sheetData[1]['D'] ?? '');
                    } elseif ($sheetData[1]['B'] && strpos(mb_substr($sheetData[1]['B'], 0, 2, 'utf-8'), '申通') !== false) {
                        $deliver['company'] = '申通快递';
                        $deliver['contactId'] = 40763;
                        $deliver['deliverDate'] = trim($sheetData[1]['D'] ?? '');
                    } elseif ($sheetData[1]['B'] && strpos(mb_substr($sheetData[1]['B'], 0, 2, 'utf-8'), '圆通') !== false) {
                        $deliver['company'] = '圆通速递';
                        $deliver['contactId'] = 40105;
                        $deliver['deliverDate'] = trim($sheetData[1]['D'] ?? '');
                    } elseif ($sheetData[1]['B'] && strpos(mb_substr($sheetData[1]['B'], 0, 2, 'utf-8'), '百世') !== false) {
                        $deliver['company'] = '百世快运';
                        $deliver['contactId'] = 26144;
                        $deliver['deliverDate'] = trim($sheetData[1]['D'] ?? '');
                    } else {
                        $this->db->transRollback();
                        return $this->response->setJSON(['status' => -1, 'msg' => '未判断出导入的对账单属于哪家快递公司，请认真检查模板表格是否填写正确！']);
                    }
                } else {
                    $this->db->transRollback();
                    return $this->response->setJSON(['status' => -1, 'msg' => '表格格式错误！']);
                }
                if (strlen($deliver['deliverDate']) != 7 || strpos($deliver['deliverDate'], '-') != 4) {
                    $this->db->transRollback();
                    return $this->response->setJSON(['status' => -1, 'msg' => '目标月份格式错误！']);
                }
                $deliver['billDate'] = date("Y-m-d");
                $deliver['billNo'] = str_no('KD');
                $deliver['uId'] = $this->myerp['uId'];
                $deliver['uName'] = $this->myerp['uName'];
                $deliver['createTime'] = date("Y-m-d H:i:s");
                $deliver['isDraft'] = 1;
                if ($this->db->table('bill_deliver')->where(['company' => $deliver['company'], 'deliverDate' => $deliver['deliverDate'], 'isDelete' => 0])->countAllResults() == 0) {
                    $this->db->table('bill_deliver')->insert($deliver);
                    $iid = $this->db->insertID();
                }
            }
            if ($iid > 0) {
                $AAA = [];
                switch ($deliver['company']) {
                    case '顺丰速运':
                        $curIndex = 0;
                        for ($i = 4; $i <= $highestRow; $i++) {
                            $sheetData[$i]['A'] = preg_replace("/([\r|\n|\t| ]+)/", '', $sheetData[$i]['A']);
                            if ($sheetData[$i]['A'] == NULL || strlen($sheetData[$i]['A']) !== 15 || strpos($sheetData[$i]['A'], 'SF') !== 0) {
                                $AAA[] = $sheetData[$i]['A'];
                                continue;
                            }
                            $deliver_info[$curIndex]['iid'] = $iid;
                            $deliver_info[$curIndex]['no'] = $deliverbag[$curIndex]['no'] = $deliverNos[] = $sheetData[$i]['A'];
                            $deliver_info[$curIndex]['company'] = '顺丰速运';
                            $date = mb_substr($sheetData[$i]['B'], 0, 10, 'utf-8');
                            if (strlen($date) == 5) {
                                $date = mb_substr($deliver['deliverDate'], 0, 4) . '-' . $date;
                            }
                            $deliver_info[$curIndex]['date'] = $date;
                            $deliver_info[$curIndex]['address1'] = mb_substr($sheetData[$i]['D'], 0, 2, 'utf-8');
                            $deliver_info[$curIndex]['weight1'] = (float)$sheetData[$i]['C'];
                            $deliver_info[$curIndex]['amount1'] = $deliverbag[$curIndex]['amount'] = ((float)$sheetData[$i]['E'] + (float)$sheetData[$i]['F']);
                            $deliver_info[$curIndex]['description'] = $deliverbag[$curIndex]['description'] = $sheetData[$i]['G'] ?? '';
                            $amount1 += (float)$deliver_info[$curIndex]['amount1'];
                            $curIndex += 1;
                        }
                        if (count($AAA) > 0) {
                            log_message('error', '快递月结表格中以下单号不合法，未被导入：' . implode(',', $AAA));
                        }
                        break;
                    case '邮政快递':
                        $curIndex = 0;
                        for ($i = 4; $i <= $highestRow; $i++) {
                            $sheetData[$i]['A'] = preg_replace("/([\r|\n|\t| ]+)/", '', $sheetData[$i]['A']);
                            if ($sheetData[$i]['A'] == NULL || strlen($sheetData[$i]['A']) !== 13 || (strpos($sheetData[$i]['A'], '1') !== 0 && strpos($sheetData[$i]['A'], '8') !== 0 && strpos($sheetData[$i]['A'], '9') !== 0 && strpos($sheetData[$i]['A'], 'PH') !== 0)) {
                                $AAA[] = $sheetData[$i]['A'];
                                continue;
                            }
                            $deliver_info[$curIndex]['iid'] = $iid;
                            $deliver_info[$curIndex]['no'] = $deliverNos[] = $sheetData[$i]['A'];
                            $deliver_info[$curIndex]['company'] = '邮政快递';
                            $deliver_info[$curIndex]['date'] = mb_substr($sheetData[$i]['B'], 0, 10, 'utf-8');
                            $deliver_info[$curIndex]['address1'] = mb_substr($sheetData[$i]['D'], 0, 2, 'utf-8');
                            $deliver_info[$curIndex]['weight1'] = round((float)$sheetData[$i]['C'] / 1000, 3); // g 转 kg
                            $deliver_info[$curIndex]['amount1'] = ((float)$sheetData[$i]['E'] + (float)$sheetData[$i]['F']);
                            $deliver_info[$curIndex]['description'] = $sheetData[$i]['G'] ?? '';
                            $amount1 += (float)$deliver_info[$curIndex]['amount1'];
                            $curIndex += 1;
                        }
                        if (count($AAA) > 0) {
                            log_message('error', '快递月结表格中以下单号不合法，未被导入：' . implode(',', $AAA));
                        }
                        break;
                    case '京东快递':
                        $curIndex = 0;
                        for ($i = 4; $i <= $highestRow; $i++) {
                            $sheetData[$i]['A'] = preg_replace("/([\r|\n|\t| ]+)/", '', $sheetData[$i]['A']);
                            if ($sheetData[$i]['A'] == NULL || strlen($sheetData[$i]['A']) !== 15 || strpos($sheetData[$i]['A'], 'JD') !== 0) {
                                $AAA[] = $sheetData[$i]['A'];
                                continue;
                            }
                            $deliver_info[$curIndex]['iid'] = $iid;
                            $deliver_info[$curIndex]['no'] = $deliverNos[] = $sheetData[$i]['A'];
                            $deliver_info[$curIndex]['company'] = '京东快递';
                            $deliver_info[$curIndex]['date'] = mb_substr($sheetData[$i]['B'], 0, 10, 'utf-8');
                            $deliver_info[$curIndex]['address1'] = mb_substr($sheetData[$i]['D'], 0, 2, 'utf-8');
                            $deliver_info[$curIndex]['weight1'] = (float)$sheetData[$i]['C'];
                            $deliver_info[$curIndex]['amount1'] = ((float)$sheetData[$i]['E'] + (float)$sheetData[$i]['F']);
                            $deliver_info[$curIndex]['description'] = $sheetData[$i]['G'] ?? '';
                            $amount1 += (float)$deliver_info[$curIndex]['amount1'];
                            $curIndex += 1;
                        }
                        if (count($AAA) > 0) {
                            log_message('error', '快递月结表格中以下单号不合法，未被导入：' . implode(',', $AAA));
                        }
                        break;
                    case '申通快递':
                        $curIndex = 0;
                        for ($i = 4; $i <= $highestRow; $i++) {
                            $sheetData[$i]['A'] = preg_replace("/([\r|\n|\t| ]+)/", '', $sheetData[$i]['A']);
                            if (
                                $sheetData[$i]['A'] == NULL ||
                                (strlen($sheetData[$i]['A']) !== 13 && strlen($sheetData[$i]['A']) !== 15) ||
                                ((strlen($sheetData[$i]['A']) == 13 && strpos($sheetData[$i]['A'], '66') !== 0) || (strlen($sheetData[$i]['A']) == 15 && strpos($sheetData[$i]['A'], '77') !== 0))
                            ) {
                                $AAA[] = $sheetData[$i]['A'];
                                continue;
                            }
                            $deliver_info[$curIndex]['iid'] = $iid;
                            $deliver_info[$curIndex]['no'] = $deliverNos[] = $sheetData[$i]['A'];
                            $deliver_info[$curIndex]['company'] = '申通快递';
                            $deliver_info[$curIndex]['date'] = mb_substr($sheetData[$i]['B'], 0, 10, 'utf-8');
                            $deliver_info[$curIndex]['address1'] = mb_substr($sheetData[$i]['D'], 0, 2, 'utf-8');
                            $deliver_info[$curIndex]['weight1'] = (float)$sheetData[$i]['C'];
                            $deliver_info[$curIndex]['amount1'] = ((float)$sheetData[$i]['E'] + (float)$sheetData[$i]['F']);
                            $deliver_info[$curIndex]['description'] = $sheetData[$i]['G'] ?? '';
                            $amount1 += (float)$deliver_info[$curIndex]['amount1'];
                            $curIndex += 1;
                        }
                        if (count($AAA) > 0) {
                            log_message('error', '快递月结表格中以下单号不合法，未被导入：' . implode(',', $AAA));
                        }
                        break;
                    case '圆通速递':
                        $curIndex = 0;
                        for ($i = 4; $i <= $highestRow; $i++) {
                            $sheetData[$i]['A'] = preg_replace("/([\r|\n|\t| ]+)/", '', $sheetData[$i]['A']);
                            if ($sheetData[$i]['A'] == NULL || strlen($sheetData[$i]['A']) !== 15 || strpos($sheetData[$i]['A'], 'YT') !== 0) {
                                $AAA[] = $sheetData[$i]['A'];
                                continue;
                            }
                            $deliver_info[$curIndex]['iid'] = $iid;
                            $deliver_info[$curIndex]['no'] = $deliverNos[] = $sheetData[$i]['A'];
                            $deliver_info[$curIndex]['company'] = '圆通速递';
                            $deliver_info[$curIndex]['date'] = mb_substr($sheetData[$i]['B'], 0, 10, 'utf-8');
                            $deliver_info[$curIndex]['address1'] = mb_substr($sheetData[$i]['D'], 0, 2, 'utf-8');
                            $deliver_info[$curIndex]['weight1'] = (float)$sheetData[$i]['C'];
                            $deliver_info[$curIndex]['amount1'] = ((float)$sheetData[$i]['E'] + (float)$sheetData[$i]['F']);
                            $deliver_info[$curIndex]['description'] = $sheetData[$i]['G'] ?? '';
                            $amount1 += (float)$deliver_info[$curIndex]['amount1'];
                            $curIndex += 1;
                        }
                        if (count($AAA) > 0) {
                            log_message('error', '快递月结表格中以下单号不合法，未被导入：' . implode(',', $AAA));
                        }
                        break;
                    case '百世快运':
                        $curIndex = 0;
                        for ($i = 4; $i <= $highestRow; $i++) {
                            $sheetData[$i]['A'] = preg_replace("/([\r|\n|\t| ]+)/", '', $sheetData[$i]['A']);
                            if ($sheetData[$i]['A'] == NULL || strlen($sheetData[$i]['A']) < 11) {
                                $AAA[] = $sheetData[$i]['A'];
                                continue;
                            }
                            $deliver_info[$curIndex]['iid'] = $iid;
                            $deliver_info[$curIndex]['no'] = $deliverbag[$curIndex]['no'] = $deliverNos[] = $sheetData[$i]['A'];
                            $deliver_info[$curIndex]['company'] = '百世快运';
                            $deliver_info[$curIndex]['date'] = mb_substr($sheetData[$i]['B'], 0, 10, 'utf-8');
                            $deliver_info[$curIndex]['address1'] = mb_substr($sheetData[$i]['D'], 0, 2, 'utf-8');
                            $deliver_info[$curIndex]['weight1'] = (float)$sheetData[$i]['C'];
                            $deliver_info[$curIndex]['amount1'] = $deliverbag[$curIndex]['amount'] = ((float)$sheetData[$i]['E'] + (float)$sheetData[$i]['F']);
                            $deliver_info[$curIndex]['description'] = $deliverbag[$curIndex]['description'] = $sheetData[$i]['G'] ?? '';
                            $amount1 += (float)$deliver_info[$curIndex]['amount1'];
                            $curIndex += 1;
                        }
                        log_message('error', '快递月结表格中以下单号不合法，未被导入：' . implode(',', $AAA));
                        break;
                }
                if (count($deliver_info) > 0) {
                    $this->db->table('bill_deliver_info')->insertBatch($deliver_info);
                }
                //更新发包记录里的顺丰/百世快运的计费 (因这几个公司没有绝对准确的计费公式，所以直接以对方对账单计费为准)
                if (count($deliverbag) > 0) {
                    $this->db->table('deliver_bag')->updateBatch($deliverbag, 'no');
                }
            } else {
                $this->db->transRollback();
                return $this->response->setJSON(['status' => -1, 'msg' => '已存相同快递公司相同月份的快递月结单，无法再导入！']);
            }
        }
        if ($iid > 0) {
            $des = '';
            $rAmount = 0;
            if (count($deliverNos) > 0) {
                //从发包记录表提取数据，更新快递月结详情表中的各单的 “我方目的地”、“我方计重”、“我方计费”、“我方有无此单”
                $deliverbag_res = $this->db->table('deliver_bag')
                    ->select('no, address address2, weight weight2, amount amount2, printTime, 1 AS noCheck')
                    ->where('FIND_IN_SET(no, "' . implode(',', $deliverNos) . '") >', 0, false)
                    ->get()
                    ->getResultArray();
                if (!empty($deliverbag_res)) {
                    $this->db->table('bill_deliver_info')->updateBatch($deliverbag_res, 'no');
                }
                //从客户之声列表提取数据，更新快递月结主表的（备注、应扣除总额）
                $service_res = $this->db->table('service')
                    ->select('deliverNo, targetAmount, content')
                    ->where('FIND_IN_SET(deliverNo, "' . implode(',', $deliverNos) . '") >', 0, false)
                    ->where('targetName', $deliver['company'])
                    ->get()
                    ->getResultArray();
                if (!empty($service_res)) {
                    foreach ($service_res as $k => $v) {
                        $des .= ($k + 1) . '、' . $v['deliverNo'] . ' 扣除 ' . $v['targetAmount'] . '元，原因：' . strip_tags(str_replace('<i class="close iconfont icon-shanchu"></i></div>', '；', str_replace('<div class="info-time">', '）', str_replace('<div class="info-username">', '（', $v['content'])))) . "\r\n";
                        $rAmount += $v['targetAmount'];
                    }
                }
            }
            if (strlen($des) > 0) {
                $des .= '汇总：应扣总金额（' . $rAmount . '元）。' . "\r\n" . '说明：以上为系统自动从客户之声反馈信息里提取的相关数据，请视具体情况选择是否手动核对一遍。';
            }
            //如果我方计费高于对方或非大陆区域，则取对方的
            $this->db->table('bill_deliver_info')
                ->set('amount2', 'amount1', false)
                ->where('iid', $iid)
                ->groupStart()
                    ->where('amount2 > amount1', null, false)
                    ->orWhereIn('address2', ["香港","澳门","台湾"])
                ->groupEnd()
                ->update();
            //更新快递月结主表的（对方总额、我方总额、最终总额、备注信息）
            $amount2 = $this->db->table('bill_deliver_info')
                ->selectSum('amount2')
                ->where(['iid' => $iid, 'noCheck' => 1, 'isDelete' => 0])
                ->get()
                ->getRowArray()['amount2'] ?? 0;
            $this->db->table('bill_deliver')
                ->where('id', $iid)
                ->update(['amount1' => $amount1, 'amount2' => $amount2, 'amount' => ($amount2 - $rAmount), 'description' => $des]);
            $this->myservice->log('导入了 ' . $deliver['company'] . ' ' . $deliver['deliverDate'] . ' 的月度对账单，系统自动生成了快递月结单，单号：' . $deliver['billNo']);
            $this->db->transCommit();
            return $this->response->setJSON(['status' => 200, 'msg' => '自动生成快递月结单完成！']);
        }
        $this->db->transRollback();
        return $this->response->setJSON(['status' => -1, 'msg' => '没有数据用于生成快递月结单！']);
    }
    // 更新单个子条目
    public function upDeliverInfo()
    {
        if (!$this->myservice->verifyRight('DELIVER_UPDATE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/Deliver/upDeliverInfo 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $cellName = $this->request->getGetPost('cellName');
        $cellValue = $this->request->getGetPost('cellValue');
        $data = $this->db->table('bill_deliver_info')->where('id', $id)->get()->getRowArray();
        if ($data) {
            $this->db->transStart();
            if ($cellName == 'noCheck' && $cellValue == 1) {
                $this->db->table('bill_deliver_info')
                    ->set('noCheck', 1)
                    ->set('address2', 'address1', false)
                    ->set('weight2', 'weight1', false)
                    ->set('amount2', 'amount1', false)
                    ->where('id', $id)
                    ->update();
            } else {
                $this->db->table('bill_deliver_info')->set([$cellName => $cellValue])->where('id', $id)->update();
            }
            $this->myservice->log('编辑了快递月结单的单个运单信息，运单：' . $data['company'] . $data['no'] . '，编辑字段：' . $cellName . '，编辑后的值：' . $cellValue);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '更新成功！']);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
    // 编辑子条目后重新计算我方总额
    public function count_amount2()
    {
        if (!$this->myservice->verifyRight('DELIVER_UPDATE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 Bill/Deliver/count_amount2 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $data = $this->db->table('bill_deliver_info')->selectSum('amount2')->where(['iid' => $id, 'noCheck' => 1, 'isDelete' => 0])->get()->getRowArray();
        if ($data) {
            $this->db->transStart();
            $this->db->table('bill_deliver')->where('id', $id)->update(['amount2' => $data['amount2']]);
            $this->myservice->log('重新计算了快递月结单的我方总额，单号ID：' . $id . '，计算后的金额：' . $data['amount2']);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '我方总额计算成功！', 'data' => $data['amount2']]);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '提交的是空数据！']);
    }
    // 数据验证
    private function validform(array $data)
    {
        $data['id'] = isset($data['id']) ? intval($data['id']) : -1;
        $data['uId'] = $this->myerp['uId'];
        $data['uName'] = $this->myerp['uName'];
        $data['modifyTime'] = date('Y-m-d H:i:s');
        $data['isDraft'] = isset($data['isDraft']) ? intval($data['isDraft']) : 0;
        return $data;
    }
}
