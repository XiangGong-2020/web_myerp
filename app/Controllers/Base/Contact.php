<?php

namespace App\Controllers\Base;
use App\Controllers\BaseController;

class Contact extends BaseController
{
    //列表
    public function list()
    {
        $type = max(intval($this->request->getGetPost('type')), -1);
        switch ($type) {
            case 0:
                if (!$this->myservice->verifyRight('CUSTOMER_QUERY')) {
                    log_message('error', $this->myerp['uName'] . ' 访问 /Base/Contact/list 客户 权限不足！');
                    return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
                };
                break;
            case 1:
                if (!$this->myservice->verifyRight('SUPPLIER_QUERY')) {
                    log_message('error', $this->myerp['uName'] . ' 访问 /Base/Contact/list 商家 权限不足！');
                    return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
                };
                break;
            default:
                if (!$this->myservice->verifyRight('CUSTOMER_QUERY') && !$this->myservice->verifyRight('SUPPLIER_QUERY')) {
                    log_message('error', $this->myerp['uName'] . ' 访问 /Base/Contact/list 客户或商家 权限不足！');
                    return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
                };
                break;
        }
        $page = max(intval($this->request->getGetPost('page')), 1);
        $rows = max(intval($this->request->getGetPost('rows')), 100);
        $sidx = $this->request->getGetPost('sidx');
        $sord = strtoupper((string)$this->request->getGetPost('sord')) === 'ASC' ? 'ASC' : 'DESC';
        $skey = $this->request->getGetPost('skey');
        $categoryid = intval($this->request->getGetPost('categoryId'));
        $disableType = intval($this->request->getGetPost('disableType'));
        $blackList = intval($this->request->getGetPost('blackList'));
        $builder = $this->db->table('contact')->where('isDelete', 0);
        if ($categoryid > 0) {
            $builder->where('categoryId', $categoryid);
        }
        if ($disableType >= 0) {
            $builder->where('isDisable', $disableType);
        }
        if ($blackList > 0) {
            $builder->where('blackList', 1);
        }
        if ($type > -1) {
            $builder->where('type', $type);
        }
        if ($skey !== '' && $skey !== null) {
            $keywords = array_values(array_filter(explode(',', $skey), 'strlen'));
            foreach ($keywords as $kw) {
                $builder->groupStart()
                    ->like('name', $this->db->escapeLikeString($kw))
                    ->orLike('linkMans', $this->db->escapeLikeString($kw))
                    ->orLike('description', $this->db->escapeLikeString($kw))
                    ->orLike('product', $this->db->escapeLikeString($kw))
                    ->orLike('website', $this->db->escapeLikeString($kw))
                ->groupEnd();
            }
        }
        // 允许排序的字段白名单，杜绝 order by 注入
        $allowedSort = ['id','type','name','cLevel','product','website','amount','comeFrom','description','isDisable','linkMen','winRate','winInfo'];
        $orderField  = $sidx ? (in_array($sidx, $allowedSort, true) ? $sidx : 'id') : 'id';
        $list = (clone $builder)->orderBy($orderField, $sord)->limit($rows)->offset($rows * ($page - 1))->get()->getResultArray();
        $total = $builder->countAllResults();
        $v = [];
        foreach ($list as $arr => $row) {
            $v[$arr]['id'] = intval($row['id']);
            $v[$arr]['type'] = intval($row['type']);
            $v[$arr]['name'] = $row['name'];
            $v[$arr]['cLevel'] = intval($row['cLevel']);
            $v[$arr]['product'] = $row['product'] ?? '';
            $v[$arr]['website'] = $row['website'] ?? '';
            $v[$arr]['amount'] = (float)$row['amount'];
            $v[$arr]['comeFrom'] = $row['comeFrom'] ?? '';
            $v[$arr]['description'] = $row['description'];
            $v[$arr]['isDisable'] = intval($row['isDisable']);
            $v[$arr]['links'] = '';
            $v[$arr]['linkMen'] = $row['linkMans'];
            $v[$arr]['winRate'] = intval($row['winRate']);
            $v[$arr]['winInfo'] = $row['winInfo'];
            if (strlen($row['linkMans']) > 0) {
                $list = (array)json_decode($row['linkMans'], true);
                foreach ($list as $arr1 => $row1) {
                    if ($row1['linkFirst'] == 1) {
                        $v[$arr]['contacter'] = $row1['linkName'] ?? '';
                        $v[$arr]['place'] = $row1['linkPlace'] ?? '';
                        $v[$arr]['wangwang'] = $row1['linkWangWang'] ?? '';
                        $v[$arr]['weixin'] = $row1['linkWeixin'] ?? '';
                        $v[$arr]['im'] = $row1['linkIm'] ?? '';
                        $v[$arr]['mobile'] = $row1['linkMobile'] ?? '';
                        $v[$arr]['phone'] = $row1['linkPhone'] ?? '';
                        $v[$arr]['province'] = $row1['province'] ?? '';
                        $v[$arr]['city'] = $row1['city'] ?? '';
                        $v[$arr]['county'] = $row1['county'] ?? '';
                        $v[$arr]['deliveryAddress'] = $row1['address'] ?? '';
                        $v[$arr]['firstLink']['first'] = $row1['linkFirst'] ?? '';
                    }
                }
            }
        }
        $json['status'] = 200;
        $json['msg'] = 'success';
        $json['data']['page'] = $page;
        $json['data']['records'] = $total;
        $json['data']['total'] = ceil($json['data']['records'] / $rows);
        $json['data']['rows'] = $v;
        return $this->response->setJSON($json);
    }
    //导出列表
    public function export()
    {
        set_time_limit(0); // 设置脚本运行时间无限制，直到脚本运行完毕
        session_write_close(); // 主动关闭session读写保护来解除锁定，避免其他访问无法读写session造成阻塞引起假死
        $type = max(intval($this->request->getGetPost('type')), -1);
        switch ($type) {
            case 0:
                if (!$this->myservice->verifyRight('CUSTOMER_EXPORT')) {
                    log_message('error', $this->myerp['uName'] . ' 访问 /Base/Contact/export 客户 权限不足！');
                    return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
                }
                break;
            case 1:
                if (!$this->myservice->verifyRight('SUPPLIER_EXPORT')) {
                    log_message('error', $this->myerp['uName'] . ' 访问 /Base/Contact/export 商家 权限不足！');
                    return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
                }
                break;
            default:
                if (!$this->myservice->verifyRight('CUSTOMER_EXPORT') && !$this->myservice->verifyRight('SUPPLIER_EXPORT')) {
                    log_message('error', $this->myerp['uName'] . ' 访问 /Base/Contact/export 客户或商家 权限不足！');
                    return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
                }
                break;
        }
        $skey = $this->request->getGetPost('skey');
        $categoryid = intval($this->request->getGetPost('categoryId'));
        $disableType = intval($this->request->getGetPost('disableType'));
        $blackList = intval($this->request->getGetPost('blackList'));
        $ids = $this->request->getGetPost('ids');
        $ids = array_filter(array_map('intval', explode(',', (string) $ids)));
        $builder = $this->db->table('contact')->where('isDelete', 0);
        if ($ids) {
            $builder->whereIn('id', $ids);
        } else {
            $builder->where('isDelete', 0);
            if ($categoryid > 0) {
                $builder->where('categoryId', $categoryid);
            }
            if ($disableType >= 0) {
                $builder->where('isDisable', $disableType);
            }
            if ($blackList > 0) {
                $builder->where('blackList', 1);
            }
            if ($type > -1) {
                $builder->where('type', $type);
            }

        }
        if ($skey !== '' && $skey !== null) {
            $keywords = array_values(array_filter(explode(',', $skey), 'strlen'));
            foreach ($keywords as $kw) {
                $builder->groupStart()
                    ->like('name', $this->db->escapeLikeString($kw))
                    ->orLike('comeFrom', $this->db->escapeLikeString($kw))
                    ->orLike('linkMans', $this->db->escapeLikeString($kw))
                    ->orLike('description', $this->db->escapeLikeString($kw))
                    ->orLike('product', $this->db->escapeLikeString($kw))
                    ->orLike('website', $this->db->escapeLikeString($kw))
                ->groupEnd();
            }
        }
        $list = (clone $builder)->orderBy('id', 'DESC')->get()->getResultArray();
        $v = [];
        // 调用spreadsheet处理表格
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $activeWorksheet = $spreadsheet->getActiveSheet();
        // A-Z列 设置字符串自动换行(会撑开单元格高度,并破坏设置的默认行高)，设置水平居左+垂直居中
        $activeWorksheet->getStyle("A:Z")->getAlignment()->setWrapText(TRUE);
        $activeWorksheet->getStyle('A:Z')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
        $activeWorksheet->getStyle('A:Z')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
        if (0 == $type) {
            $name = '客户列表';
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
            $activeWorksheet->getStyle('A1:N' . (COUNT($list) + 2))->applyFromArray($styleArray, false);
            // 设置加粗
            $activeWorksheet->getStyle('A1')->getFont()->setBold(true);
            $activeWorksheet->getStyle('A2:N2')->getFont()->setBold(true);
            // 设置背景色
            $activeWorksheet->getStyle('A2:N2')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
            $activeWorksheet->getStyle('A2:N2')->getFill()->getStartColor()->setRGB('F5F5F5');
            // 合并单元格
            $activeWorksheet->mergeCells('A1:N1');
            // 设置行高
            $activeWorksheet->getRowDimension('1')->setRowHeight(40);
            // 设置水平居中
            $activeWorksheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            // 设置每列列宽
            $activeWorksheet->getColumnDimension('A')->setWidth(10);
            $activeWorksheet->getColumnDimension('B')->setWidth(20);
            $activeWorksheet->getColumnDimension('C')->setWidth(10);
            $activeWorksheet->getColumnDimension('D')->setWidth(10);
            $activeWorksheet->getColumnDimension('E')->setWidth(12);
            $activeWorksheet->getColumnDimension('F')->setWidth(15);
            $activeWorksheet->getColumnDimension('G')->setWidth(20);
            $activeWorksheet->getColumnDimension('H')->setWidth(10);
            $activeWorksheet->getColumnDimension('I')->setWidth(15);
            $activeWorksheet->getColumnDimension('J')->setWidth(15);
            $activeWorksheet->getColumnDimension('K')->setWidth(15);
            $activeWorksheet->getColumnDimension('L')->setWidth(15);
            $activeWorksheet->getColumnDimension('M')->setWidth(15);
            $activeWorksheet->getColumnDimension('N')->setWidth(20);
            //设置当前的sheet
            $activeWorksheet->setCellValue('A1', $name . '（导出时间 ' . date('Y-m-d H:i:s') . '）');
            $activeWorksheet->setCellValue('A2', 'ID');
            $activeWorksheet->setCellValue('B2', '名称');
            $activeWorksheet->setCellValue('C2', '类别');
            $activeWorksheet->setCellValue('D2', '等级');
            $activeWorksheet->setCellValue('E2', '建档日期');
            $activeWorksheet->setCellValue('F2', '预收款余额');
            $activeWorksheet->setCellValue('G2', '备注');
            $activeWorksheet->setCellValue('H2', '联系人');
            $activeWorksheet->setCellValue('I2', '旺旺');
            $activeWorksheet->setCellValue('J2', '微信');
            $activeWorksheet->setCellValue('K2', 'QQ');
            $activeWorksheet->setCellValue('L2', '手机');
            $activeWorksheet->setCellValue('M2', '座机');
            $activeWorksheet->setCellValue('N2', '地址');
            //设置某些列为文本格式
            $activeWorksheet->getStyle('A1:N' . (count($list) + 2))->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT);
            $i = 3;
            foreach ($list as $k => $v) {
                $activeWorksheet->setCellValue('A' . $i, $v['id']);
                $activeWorksheet->setCellValue('B' . $i, $v['name']);
                $activeWorksheet->setCellValue('C' . $i, $this->db->table('category')->select('name')->where('id', $v['categoryId'])->get()->getRowArray()['name'] ?? '');
                $activeWorksheet->setCellValue('D' . $i, $v['cLevel'] == 0 ? '零售' : ($v['cLevel'] == 1 ? '批发' : ($v['cLevel'] == 2 ? '大宗' : ($v['cLevel'] == 3 ? '分销' : ($v['cLevel'] == 4 ? '战略' : '')))));
                $activeWorksheet->setCellValue('E' . $i, $v['beginDate']);
                $activeWorksheet->setCellValue('F' . $i, $v['amount']);
                $activeWorksheet->setCellValue('G' . $i, $v['description']);
                if (strlen($v['linkMans']) > 10) {
                    $linkMans = (array)json_decode($v['linkMans'], true);
                    foreach ($linkMans as $k1 => $v1) {
                        if ($v1['linkFirst'] == 1) {
                            $activeWorksheet->setCellValue('H' . $i, $v1['linkName'] ?? '');
                            $activeWorksheet->setCellValueExplicit('I' . $i, $v1['linkWangWang'] ?? '', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                            $activeWorksheet->setCellValueExplicit('J' . $i, $v1['linkWeixin'] ?? '', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                            $activeWorksheet->setCellValueExplicit('K' . $i, $v1['linkIm'] ?? '', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                            $activeWorksheet->setCellValueExplicit('L' . $i, $v1['linkMobile'] ?? '', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                            $activeWorksheet->setCellValueExplicit('M' . $i, $v1['linkPhone'] ?? '', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                            $activeWorksheet->setCellValue('N' . $i, ($v1['province'] ?? '') . ($v1['city'] ?? '') . ($v1['county'] ?? '') . ($v1['address'] ?? ''));
                        }
                    }
                } else {
                    $activeWorksheet->setCellValue('H' . $i, '');
                    $activeWorksheet->setCellValue('I' . $i, '');
                    $activeWorksheet->setCellValue('J' . $i, '');
                    $activeWorksheet->setCellValue('K' . $i, '');
                    $activeWorksheet->setCellValue('L' . $i, '');
                    $activeWorksheet->setCellValue('M' . $i, '');
                    $activeWorksheet->setCellValue('N' . $i, '');
                }
                $i++;
            }
        } else {
            $name = '商家列表';
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
            $activeWorksheet->getStyle('A1:P' . (COUNT($list) + 2))->applyFromArray($styleArray, false);

            // 设置加粗
            $activeWorksheet->getStyle('A1')->getFont()->setBold(true);
            $activeWorksheet->getStyle('A2:P2')->getFont()->setBold(true);

            // 设置背景色
            $activeWorksheet->getStyle('A2:P2')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
            $activeWorksheet->getStyle('A2:P2')->getFill()->getStartColor()->setRGB('F5F5F5');

            // 合并单元格
            $activeWorksheet->mergeCells('A1:P1');

            // 设置行高
            $activeWorksheet->getRowDimension('1')->setRowHeight(40);

            // 设置水平居中
            $activeWorksheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

            // 设置每列列宽
            $activeWorksheet->getColumnDimension('A')->setWidth(10);
            $activeWorksheet->getColumnDimension('B')->setWidth(20);
            $activeWorksheet->getColumnDimension('C')->setWidth(10);
            $activeWorksheet->getColumnDimension('D')->setWidth(10);
            $activeWorksheet->getColumnDimension('E')->setWidth(12);
            $activeWorksheet->getColumnDimension('F')->setWidth(20);
            $activeWorksheet->getColumnDimension('G')->setWidth(12);
            $activeWorksheet->getColumnDimension('H')->setWidth(15);
            $activeWorksheet->getColumnDimension('I')->setWidth(20);
            $activeWorksheet->getColumnDimension('J')->setWidth(10);
            $activeWorksheet->getColumnDimension('K')->setWidth(15);
            $activeWorksheet->getColumnDimension('L')->setWidth(15);
            $activeWorksheet->getColumnDimension('M')->setWidth(15);
            $activeWorksheet->getColumnDimension('N')->setWidth(15);
            $activeWorksheet->getColumnDimension('O')->setWidth(15);
            $activeWorksheet->getColumnDimension('P')->setWidth(20);

            //设置当前的sheet
            $activeWorksheet->setCellValue('A1', $name . '（导出时间 ' . date('Y-m-d H:i:s') . '）');
            $activeWorksheet->setCellValue('A2', 'ID');
            $activeWorksheet->setCellValue('B2', '名称');
            $activeWorksheet->setCellValue('C2', '类别');
            $activeWorksheet->setCellValue('D2', '评价');
            $activeWorksheet->setCellValue('E2', '主营');
            $activeWorksheet->setCellValue('F2', '网站');
            $activeWorksheet->setCellValue('G2', '建档日期');
            $activeWorksheet->setCellValue('H2', '预付款余额');
            $activeWorksheet->setCellValue('I2', '备注');
            $activeWorksheet->setCellValue('J2', '联系人');
            $activeWorksheet->setCellValue('K2', '旺旺');
            $activeWorksheet->setCellValue('L2', '微信');
            $activeWorksheet->setCellValue('M2', 'QQ');
            $activeWorksheet->setCellValue('N2', '手机');
            $activeWorksheet->setCellValue('O2', '座机');
            $activeWorksheet->setCellValue('P2', '地址');

            //设置某些列为文本格式
            $activeWorksheet->getStyle('A1:P' . (count($list) + 2))->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT);

            $i = 3;
            foreach ($list as $k => $v) {
                $activeWorksheet->setCellValue('A' . $i, $v['id']);
                $activeWorksheet->setCellValue('B' . $i, $v['name']);
                $activeWorksheet->setCellValue('C' . $i, $this->db->table('category')->select('name')->where('id', $v['categoryId'])->get()->getRowArray()['name'] ?? '');
                $activeWorksheet->setCellValue('D' . $i, $v['cLevel'] == 0 ? '优秀' : ($v['cLevel'] == 1 ? '较好' : ($v['cLevel'] == 2 ? '一般' : ($v['cLevel'] == 3 ? '较差' : ($v['cLevel'] == 4 ? '恶劣' : '')))));
                $activeWorksheet->setCellValue('E' . $i, $v['product']);
                $activeWorksheet->setCellValue('F' . $i, $v['website']);
                $activeWorksheet->setCellValue('G' . $i, $v['beginDate']);
                $activeWorksheet->setCellValue('H' . $i, $v['amount']);
                $activeWorksheet->setCellValue('I' . $i, $v['description']);
                if (strlen($v['linkMans']) > 10) {
                    $linkMans = (array)json_decode($v['linkMans'], true);
                    foreach ($linkMans as $k1 => $v1) {
                        if ($v1['linkFirst'] == 1) {
                            $activeWorksheet->setCellValue('J' . $i, $v1['linkName'] ?? '');
                            $activeWorksheet->setCellValueExplicit('K' . $i, $v1['linkWangWang'] ?? '', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                            $activeWorksheet->setCellValueExplicit('L' . $i, $v1['linkWeixin'] ?? '', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                            $activeWorksheet->setCellValueExplicit('M' . $i, $v1['linkIm'] ?? '', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                            $activeWorksheet->setCellValueExplicit('N' . $i, $v1['linkMobile'] ?? '', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                            $activeWorksheet->setCellValueExplicit('O' . $i, $v1['linkPhone'] ?? '', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                            $activeWorksheet->setCellValue('P' . $i, ($v1['province'] ?? '') . ($v1['city'] ?? '') . ($v1['county'] ?? '') . ($v1['address'] ?? ''));
                        }
                    }
                } else {
                    $activeWorksheet->setCellValue('J' . $i, '');
                    $activeWorksheet->setCellValue('K' . $i, '');
                    $activeWorksheet->setCellValue('L' . $i, '');
                    $activeWorksheet->setCellValue('M' . $i, '');
                    $activeWorksheet->setCellValue('N' . $i, '');
                    $activeWorksheet->setCellValue('O' . $i, '');
                    $activeWorksheet->setCellValue('P' . $i, '');
                }
                $i++;
            }
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
    //查询
    public function query()
    {
        $id = intval($this->request->getGetPost('id'));
        $type = max(intval($this->request->getGetPost('type')), -1);
        switch ($type) {
            case 0:
                if (!$this->myservice->verifyRight('CUSTOMER_QUERY')) {
                    log_message('error', $this->myerp['uName'] . ' 访问 /Base/Contact/query 客户 权限不足！');
                    return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
                }
                break;
            case 1:
                if (!$this->myservice->verifyRight('SUPPLIER_QUERY')) {
                    log_message('error', $this->myerp['uName'] . ' 访问 /Base/Contact/query 商家 权限不足！');
                    return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
                }
                break;
            default:
                if (!$this->myservice->verifyRight('CUSTOMER_QUERY') && !$this->myservice->verifyRight('SUPPLIER_QUERY')) {
                    log_message('error', $this->myerp['uName'] . ' 访问 /Base/Contact/query 客户或商家 权限不足！');
                    return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
                }
                break;
        }
        $builder = $this->db->table('contact')->where(['id' => $id, 'isDelete' => 0]);
        if ($type >= 0) {
            $builder->where(['type' => $type]);
        }
        $data = $builder->get()->getRowArray();
        if ($data) {
            $info['id'] = $id;
            $info['categoryId'] = intval($data['categoryId']);
            $info['name'] = $data['name'];
            $info['cLevel'] = intval($data['cLevel']);
            $info['product'] = $data['product'];
            $info['website'] = $data['website'];
            $info['beginDate'] = $data['beginDate'];
            $info['amount'] = (float) $data['amount'];
            $info['taxRate'] = (float) $data['taxRate'];
            $info['comeFrom'] = $data['comeFrom'];
            $info['description'] = $data['description'];
            $info['files'] = $data['files'] == '' ? [] : json_decode($data['files'], true);
            $v = [];
            if (strlen($data['linkMans']) > 0) {
                $list = (array)json_decode($data['linkMans'], true);
                foreach ($list as $arr => $row) {
                    $v[$arr]['id'] = $row['linkId'];
                    $v[$arr]['contacter'] = isset($row['linkName']) ? $row['linkName'] : '';
                    $v[$arr]['place'] = isset($row['linkPlace']) ? $row['linkPlace'] : '';
                    $v[$arr]['wangwang'] = isset($row['linkWangWang']) ? $row['linkWangWang'] : '';
                    $v[$arr]['weixin'] = isset($row['linkWeixin']) ? $row['linkWeixin'] : '';
                    $v[$arr]['im'] = isset($row['linkIm']) ? $row['linkIm'] : '';
                    $v[$arr]['mobile'] = isset($row['linkMobile']) ? $row['linkMobile'] : '';
                    $v[$arr]['phone'] = isset($row['linkPhone']) ? $row['linkPhone'] : '';
                    $v[$arr]['province'] = isset($row['province']) ? $row['province'] : '';
                    $v[$arr]['city'] = isset($row['city']) ? $row['city'] : '';
                    $v[$arr]['county'] = isset($row['county']) ? $row['county'] : '';
                    $v[$arr]['address'] = isset($row['address']) ? $row['address'] : '';
                    $v[$arr]['first'] = $row['linkFirst'] == 1 ? true : '';
                }
            }
            $info['links'] = $v;

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
        $data = $this->request->getPost();
        if (is_array($data) && count($data) > 0) {
            $data = $this->validform($data);
            // 如果返回的是 Response 对象，直接返回它
            if ($data instanceof \CodeIgniter\HTTP\Response) {
                return $data;
            }
            switch ($data['type']) {
                case 0:
                    if (!$this->myservice->verifyRight('CUSTOMER_ADD')) {
                        log_message('error', $this->myerp['uName'] . ' 访问 /Base/Contact/add 客户 权限不足！');
                        return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
                    }
                    $typeName = '客户';
                    break;
                case 1:
                    if (!$this->myservice->verifyRight('SUPPLIER_ADD')) {
                        log_message('error', $this->myerp['uName'] . ' 访问 /Base/Contact/add 商家 权限不足！');
                        return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
                    }
                    $typeName = '商家';
                    break;
                default:
                    return $this->response->setJSON(['status' => -1, 'msg' => '没有此类别！']);
            }
            $info = array_intersect_key($data, array_flip(['type', 'comeFrom', 'name', 'product', 'website', 'taxRate', 'categoryId', 'cLevel', 'beginDate', 'amount', 'description', 'linkMans', 'files', 'uName', 'createTime']));
            $this->db->transStart();
            $this->db->table('contact')->insert($info);
            $data['id'] = $this->db->insertID();
            $data['categoryId'] = intval($data['categoryId']);
            $data['linkMans'] = (array)json_decode($data['linkMans'], true);
            $data['isDisable'] = 0;
            $this->myservice->log('新增了' . $typeName . '，ID：' . $data['id'] . '，名称：' . $data['name']);
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
        $data = $this->request->getPost();
        if (is_array($data) && count($data) > 0) {
            $data = $this->validform($data);
            // 如果返回的是 Response 对象，直接返回它
            if ($data instanceof \CodeIgniter\HTTP\Response) {
                return $data;
            }
            switch ($data['type']) {
                case 0:
                    if (!$this->myservice->verifyRight('CUSTOMER_UPDATE')) {
                        log_message('error', $this->myerp['uName'] . ' 访问 /Base/Contact/update 客户 权限不足！');
                        return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
                    }
                    $typeName = '客户';
                    break;
                case 1:
                    if (!$this->myservice->verifyRight('SUPPLIER_UPDATE')) {
                        log_message('error', $this->myerp['uName'] . ' 访问 /Base/Contact/update 商家 权限不足！');
                        return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
                    }
                    $typeName = '商家';
                    break;
                default:
                    return $this->response->setJSON(['status' => -1, 'msg' => '没有此类别！']);
            }
            if ($data['id'] < 2) {
                return $this->response->setJSON(['status' => -1, 'msg' => '禁止编辑' . ($data['id'] == 0 ? '特殊采购虚拟商家！' : '默认客户！')]);
            } else {
                $info = array_intersect_key($data, array_flip(['name', 'product', 'website', 'taxRate', 'categoryId', 'cLevel', 'beginDate', 'amount', 'description', 'linkMans', 'files']));
                $this->db->transStart();
                $this->db->table('contact')->where('id', $data['id'])->update($info);
                $this->myservice->log('编辑了' . $typeName . ',ID：' . $data['id'] . ',名称：' . $data['name']);
                $data['categoryId'] = intval($data['categoryId']);
                $data['linkMans'] = (array)json_decode($data['linkMans'], true);
                $this->db->transComplete();
                if ($this->db->transStatus() === false) {
                    return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
                }
                return $this->response->setJSON(['status' => 200, 'msg' => '编辑成功！', 'data' => $data]);
            }
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '提交的是空数据！']);
    }
    //删除
    public function delete()
    {
        $id = intval($this->request->getGetPost('id'));
        $type = max(intval($this->request->getGetPost('type')), -1);
        switch ($type) {
            case 0:
                if (!$this->myservice->verifyRight('CUSTOMER_DELETE')) {
                    log_message('error', $this->myerp['uName'] . ' 访问 /Base/Contact/delete 客户 权限不足！');
                    return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
                }
                $typeName = '客户';
                break;
            case 1:
                if (!$this->myservice->verifyRight('SUPPLIER_DELETE')) {
                    log_message('error', $this->myerp['uName'] . ' 访问 /Base/Contact/delete 商家 权限不足！');
                    return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
                }
                $typeName = '商家';
                break;
            default:
                return $this->response->setJSON(['status' => -1, 'msg' => '没有此类别！']);
        }
        $data = $this->db->table('contact')->where('id', $id)->get()->getRowArray();
        if ($data) {
            if ($this->db->table('bill_order')->where(['isDelete' => 0, 'contactId' => $id])->countAllResults() > 0) {
                return $this->response->setJSON(['status' => -1, 'msg' => $typeName . '已产生业务，无法删除！']);
            }
            if ($this->db->table('bill_contract')->where(['isDelete' => 0, 'contactId' => $id])->countAllResults() > 0) {
                return $this->response->setJSON(['status' => -1, 'msg' => $typeName . '已产生业务，无法删除！']);
            }
            $this->db->transStart();
            $this->db->table('contact')->where('id', $id)->update(['isDelete' => 1]);
            $this->myservice->log('删除了' . $typeName . '，ID：' . $id . '，名称：' . $data['name']);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '删除成功！']);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
    //更新黑名单状态
    public function upBlackList()
    {
        if (!$this->myservice->verifyRight('CUSTOMER_UPDATE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/Contact/upBlackList 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = $this->request->getGetPost('id');
        $ids = array_filter(array_map('intval', explode(',', (string) $id)));
        $blackList = intval($this->request->getGetPost('blackList'));
        if (count($ids) > 0) {
            $this->db->transStart();
            $this->db->table('contact')->whereIN('id', $ids)->update(['blackList' => $blackList]);
            $this->myservice->log('将部分客户' . ($blackList == 0 ? '移出' : '加入') . '了黑名单，客户ID：' . $id);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '操作成功！']);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '提交的是空数据！']);
    }
    //更新禁用状态
    public function disable()
    {
        $id = intval($this->request->getGetPost('id'));
        $isDisable = intval($this->request->getGetPost('isDisable'));
        $type = $this->request->getGetPost('type');
        if ($id > 0) {
            if ('customer' == $type) {
                if (!$this->myservice->verifyRight('CUSTOMER_UPDATE')) {
                    log_message('error', $this->myerp['uName'] . ' 访问 /Base/Contact/disable 客户 权限不足！');
                    return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
                }
                $typeName = '客户';
            } else {
                if (!$this->myservice->verifyRight('SUPPLIER_UPDATE')) {
                    log_message('error', $this->myerp['uName'] . ' 访问 /Base/Contact/disable 商家 权限不足！');
                    return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
                }
                $typeName = '商家';
            }
            $this->db->transStart();
            $this->db->table('contact')->where('id', $id)->update(['isDisable' => $isDisable]);
            $this->myservice->log(($isDisable > 0 ? '禁用了' : '启用了') . 'ID为：' . $id . ' 的' . $typeName);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '操作成功！']);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '提交的是空数据！']);
    }
    //检测是否重复
    public function checkRepeat()
    {
        // 严格检测，除了客户名称允许重复，商家名称、商家和客户的旺旺、微信、QQ、手机号、座机号任意一项都不允许重复
        // 不严格的话，将录入很多重复的信息，导致数据混乱
        $type = max(intval($this->request->getGetPost('type')), -1);
        $id = intval($this->request->getGetPost('id'));
        $name = $this->request->getGetPost('name');
        $linkWangWang = $this->request->getGetPost('linkWangWang');
        $linkWeixin = $this->request->getGetPost('linkWeixin');
        $linkIm = $this->request->getGetPost('linkIm');
        $linkMobile = $this->request->getGetPost('linkMobile');
        $linkPhone = $this->request->getGetPost('linkPhone');
        $builder = $this->db->table('contact')->where(['type' => $type, 'isDelete' => 0]);
        if ($id > -1) {
            $builder->where('id !=', $id);
        }
        $builder->groupStart();
        if ($type == 1) {
            $builder->where('name', $name);
        } else {
            $builder->where('name', '');
        }
        if ($linkWangWang) {
            $builder->orWhere('JSON_SEARCH(linkMans, "one", ' . $this->db->escape($linkWangWang) . ', NULL, "$[*].linkWangWang") IS NOT NULL', null, false);
        }
        if ($linkWeixin) {
            $builder->orWhere('JSON_SEARCH(linkMans, "one", ' . $this->db->escape($linkWeixin) . ', NULL, "$[*].linkWeixin") IS NOT NULL', null, false);
        }
        if ($linkIm) {
            $builder->orWhere('JSON_SEARCH(linkMans, "one", ' . $this->db->escape($linkIm) . ', NULL, "$[*].linkIm") IS NOT NULL', null, false);
        }
        if ($linkMobile) {
            $builder->orWhere('JSON_SEARCH(linkMans, "one", ' . $this->db->escape($linkMobile) . ', NULL, "$[*].linkMobile") IS NOT NULL', null, false);
        }
        if ($linkPhone) {
            $builder->orWhere('JSON_SEARCH(linkMans, "one", ' . $this->db->escape($linkPhone) . ', NULL, "$[*].linkPhone") IS NOT NULL', null, false);
        }
        $builder->groupEnd();
        if ($builder->countAllResults() > 0) {
            return $this->response->setJSON(['status' => -1, 'msg' => '已经存在！']);
        }
        return $this->response->setJSON(['status' => 200, 'msg' => 'success']);
    }
    //上传资料
    public function uploadfile()
    {
        if (!$this->myservice->verifyRight('CUSTOMER_UPDATE') && !$this->myservice->verifyRight('SUPPLIER_UPDATE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/Contact/uploadfile 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        if (empty($_FILES)){
            return $this->response->setJSON(['status' => -1, 'msg' => '没有上传文件！']);
        };
        $uppath = 'data/upfile/contact/';
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
        $this->myservice->log('上传了客户或商家资料：' . $_FILES['file']['name']);
        $json['status'] = 200;
        $json['msg'] = '资料上传成功';
        $json['file'] = ['name' => $_FILES['file']['name'], 'url' => $target_file, 'upName' => $this->myerp['uName'], 'upTime' => date('Y-m-d')];
        return $this->response->setJSON($json);
    }
    //数据验证
    private function validform(array $data)
    {
        isset($data['id']) ? intval($data['id']) : -1;
        if ($data['categoryId'] < 1) {
            return $this->response->setJSON(['status' => -1, 'msg' => '类别不能为空！']);
        }
        if (strlen($data['name']) < 1) {
            return $this->response->setJSON(['status' => -1, 'msg' => '名称不能为空！']);
        }
        $data['product'] = $data['product'] ?? '';
        $data['website'] = $data['website'] ?? '';
        $data['categoryId'] = intval($data['categoryId']);
        $data['cLevel'] = intval($data['cLevel']);
        $data['taxRate'] = isset($data['taxRate']) ? (float) $data['taxRate'] : 17;
        $data['amount'] = (float) $data['amount'];
        $data['linkMans'] = strlen($data['linkMans']) >= 10 ? $data['linkMans'] : '';
        $data['beginDate'] = $data['beginDate'] ?? date('Y-m-d');
        $data['type'] = intval($this->request->getGetPost('type')) == 0 ? 0 : 1;
        $data['contact'] = $data['name'];
        $data['files'] = strlen($data['files']) < 10 ? '' : $data['files'];
        $data['uName'] = $this->myerp['uName'];
        $data['createTime'] = date('Y-m-d H:i:s');
        return $data;
    }
}
