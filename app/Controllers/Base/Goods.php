<?php

namespace App\Controllers\Base;
use App\Controllers\BaseController;
use ZipStream\Test\FaultInjectionResource;

class Goods extends BaseController
{
    // 产品信息 列表
    public function list()
    {
        if (!$this->myservice->verifyRight('GOODS_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/Goods/list 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $page = max(intval($this->request->getGetPost('page')), 1);
        $rows = max(intval($this->request->getGetPost('rows')), 100);
        $sidx = $this->request->getGetPost('sidx');
        $sord = strtoupper((string)$this->request->getGetPost('sord')) === 'ASC' ? 'ASC' : 'DESC';
        $barCode = intval($this->request->getGetPost('barCode'));
        $noDevOrSerName = intval($this->request->getGetPost('noDevOrSerName'));
        $categoryId = intval($this->request->getGetPost('categoryId'));
        $storageId = intval($this->request->getGetPost('storageId'));
        $showForbidden = intval($this->request->getGetPost('showForbidden'));
        $showKitHasKit = intval($this->request->getGetPost('showKitHasKit'));
        $showErr = intval($this->request->getGetPost('showErr'));
        $searchType = intval($this->request->getGetPost('searchType'));
        $skey = $this->request->getGetPost('skey');
        $builder = $this->db->table('goods')->where('isDelete', 0);
        if ($barCode) {
            $builder->where('barCode', $barCode);
        }
        if ($noDevOrSerName > 0) {
            $builder->groupStart()
                ->where('devName', '')
                ->orWhere('serName', '')
            ->groupEnd();
        }
        if ($categoryId > 0) {
            $cid_arr = array_column($this->db->table('category')->select('id')->where('isDelete', 0)->where('FIND_IN_SET(' . $categoryId . ', path) >', 0, false)->get()->getResultArray(), 'id');
            if (is_array($cid_arr) && count($cid_arr) > 0) {
                $builder->whereIn('categoryId', $cid_arr);
            }
        }
        if ($storageId > 0) {
            $builder->where('storageId', $storageId);
        }
        if ($showForbidden == 0) {
            $builder->whereIn('isDisable', [0, 1]);
        } elseif ($showForbidden == 1) {
            $builder->where('isDisable <', 9);
        } elseif ($showForbidden == 2) {
            $builder->where('isDisable', 9);
        } elseif ($showForbidden == 3) {
            $builder->where('isDisable', 1);
        } elseif ($showForbidden == 4) {
            $builder->where('isDisable', 2);
        }
        if ($showErr > 0) {
            $builder->where('isError', 1);
        }
        if ($showKitHasKit > 0) {
            $builder->where('isKitHasKit', 1);
        }
        if ($skey !== '' && $skey !== null) {
            $keywords = array_values(array_filter(explode(',', $skey), 'strlen'));
            if ($searchType == 0) { // 模糊查询
                foreach ($keywords as $kw) {
                    $builder->groupStart()
                        ->like('barCode', $this->db->escapeLikeString($kw))
                        ->orLike('REPLACE(`no`, "-", "")', $this->db->escapeLikeString(str_replace('-', '', $kw)), 'both', null, false)
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
            } else { // 精确查询
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
        }
        $allowedSort = ['id','barCode','no','name','spec','sku','brandName','unitName','storageName','locationName','categoryName','weight','purPrice','salePrice','currentQty','checkQty','changeQty','checkName','checkTime','billDate','devName','serName','proName','uName','modName','files','description','description','hasSon','isDisable','isProofread'];
        $orderField  = $sidx ? (in_array($sidx, $allowedSort, true) ? $sidx : 'id') : 'id';
        $list = (clone $builder)->orderBy($orderField, $sord)->limit($rows)->offset($rows * ($page - 1))->get()->getResultArray();
        $total = $builder->countAllResults();
        $v = [];
        foreach ($list as $arr => $row) {
            $v[$arr]['id'] = intval($row['id']);
            $v[$arr]['barCode'] = $row['barCode'];
            $v[$arr]['no'] = $row['no'];
            $v[$arr]['name'] = $row['name'];
            $v[$arr]['spec'] = $row['spec'];
            $v[$arr]['sku'] = $row['sku'];
            $v[$arr]['brandId'] = intval($row['brandId']);
            $v[$arr]['brandName'] = $row['brandName'];
            $v[$arr]['unitId'] = intval($row['unitId']);
            $v[$arr]['unitName'] = $row['unitName'];
            $v[$arr]['storageId'] = intval($row['storageId']);
            $v[$arr]['storageName'] = $row['storageName'];
            $v[$arr]['locationId'] = intval($row['locationId']);
            $v[$arr]['locationName'] = $row['locationName'];
            $v[$arr]['categoryId'] = $row['categoryId'];
            $v[$arr]['categoryName'] = $row['categoryName'];
            $v[$arr]['weight'] = (float)$row['weight'];
            $v[$arr]['purPrice'] = (float)$row['purPrice'];
            $v[$arr]['salePrice'] = (float)$row['salePrice'];
            $v[$arr]['currentQty'] = (float)$row['currentQty'];
            $v[$arr]['checkQty'] = 0 == (float)$row['checkQty'] ? '' : (float)$row['checkQty'];
            $v[$arr]['changeQty'] = 0 == (float)$row['changeQty'] ? '' : (float)$row['changeQty'];
            $v[$arr]['checkName'] = $row['checkName'];
            $v[$arr]['checkTime'] = '0000-00-00 00:00:00' == $row['checkTime'] ? '' : $row['checkTime'];
            $v[$arr]['billDate'] = $row['billDate'];
            $v[$arr]['devName'] = $row['devName'];
            $v[$arr]['serName'] = $row['serName'];
            $v[$arr]['proName'] = $row['proName'];
            $v[$arr]['uName'] = $row['uName'];
            $v[$arr]['modName'] = $row['modName'];
            $imgs = [];
            if ($row['imgs']) {
                $imgs = json_decode($row['imgs'], true);
            }
            if ($row['shopImg']) {
                array_push($imgs, $row['shopImg']);
            }
            $v[$arr]['imgs'] = count($imgs) > 0 ? implode(',', $imgs) : '';
            $v[$arr]['description'] = $row['description'];
            $v[$arr]['hasSon'] = intval($row['hasSon']);
            $v[$arr]['sonGoods'] = $row['sonGoods'];
            $v[$arr]['produceClass1'] = intval($row['produceClass1']);
            $v[$arr]['produceClass2'] = intval($row['produceClass2']);
            $v[$arr]['produceQty1'] = intval($row['produceQty1']);
            $v[$arr]['produceQty2'] = intval($row['produceQty2']);
            $v[$arr]['producePrice1'] = (float)$row['producePrice1'];
            $v[$arr]['producePrice2'] = (float)$row['producePrice2'];
            $v[$arr]['producePrice3'] = (float)$row['producePrice3'];
            $v[$arr]['isDisable'] = intval($row['isDisable']);
            $v[$arr]['isProofread'] = intval($row['isProofread']);
        }
        $json['status'] = 200;
        $json['msg'] = 'success';
        $json['data']['page'] = $page;
        $json['data']['records'] = $total;
        $json['data']['total'] = ceil($json['data']['records'] / $rows);
        $json['data']['rows'] = $v;
        return $this->response->setJSON($json);
    }
    // 产品信息 列表 导出
    public function export()
    {
        if (!$this->myservice->verifyRight('GOODS_EXPORT')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/Goods/export 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        set_time_limit(0); // 设置脚本运行时间无限制，直到脚本运行完毕
        session_write_close(); // 主动关闭session读写保护来解除锁定，避免其他访问无法读写session造成阻塞引起假死
        $id = $this->request->getGetPost('id');
        $ids = array_filter(array_map('intval', explode(',', (string) $id)));
        $builder = $this->db->table('goods')->select('id,barCode,no,name,spec,sku,brandName,unitName,storageName,categoryName,locationName,purPrice,salePrice,lowQty,highQty,currentQty,weight,serName,description,isDisable,isProofread');
        if ($id) {
            $builder->whereIn('id', $ids);
        } else {
            $barCode = intval($this->request->getGetPost('barCode'));
            $monthNum = intval($this->request->getGetPost('monthNum'));
            $categoryId = intval($this->request->getGetPost('categoryId'));
            $storageId = intval($this->request->getGetPost('storageId'));
            $showForbidden = intval($this->request->getGetPost('showForbidden'));
            $showErr = intval($this->request->getGetPost('showErr'));
            $showKitHasKit = intval($this->request->getGetPost('showKitHasKit'));
            $skey = $this->request->getGetPost('skey');
            $searchType = intval($this->request->getGetPost('searchType'));
            $builder->where('isDelete', 0);
            if ($barCode) {
                $builder->where('barCode', $barCode);
            }
            if ($monthNum > 0) {
                $builder->where('billDate <', date('Y-m-d', strtotime('-' . $monthNum . ' months')));
                $builder->where('billDate >', '0000-00-00');
            }
            if ($categoryId > 0) {
<<<<<<< HEAD
                $cid_arr = array_column($this->db->table('category')->select('id')->where('isDelete', 0)->where('FIND_IN_SET(' . $categoryId . ', path) >', 0, false)->get()->getResultArray(), 'id');
=======
                $cid_arr = array_column($this->db->table('category')->select('id')->where('isDelete', 0)->where('FIND_IN_SET(' . $this->db->escape($categoryId) . ', path) >', 0, false)->get()->getResultArray(), 'id');
>>>>>>> 151b415 (更新语言最新版本+最新底层框架后全部差异化代码适配重写)
                if (is_array($cid_arr) && count($cid_arr) > 0) {
                    $builder->whereIn('categoryId', $cid_arr);
                }
            }
            if ($storageId > 0) {
                $builder->where('storageId', $storageId);
            }
            if ($showForbidden == 1) {
                $builder->where('isDisable <', 9);
            } elseif ($showForbidden == 2) {
                $builder->where('isDisable', 9);
            } elseif ($showForbidden == 3) {
                $builder->where('isDisable', 1);
            } elseif ($showForbidden == 4) {
                $builder->where('isDisable', 2);
            }
            if ($showErr > 0) {
                $builder->where('isError', 1);
            }
            if ($showKitHasKit > 0) {
                $builder->where('isKitHasKit', 1);
            }
            if ($skey !== '' && $skey !== null) {
                $keywords = array_values(array_filter(explode(',', $skey), 'strlen'));
                if ($searchType == 0) { // 模糊查询
                    foreach ($keywords as $kw) {
                        $builder->groupStart()
                            ->like('barCode', $this->db->escapeLikeString($kw))
                            ->orLike('REPLACE(`no`, "-", "")', $this->db->escapeLikeString(str_replace('-', '', $kw)), 'both', null, false)
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
                } else { // 精确查询
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
            }
        }
        $list = $builder->orderBy('id', 'DESC')->get()->getResultArray();
        $name = '产品列表_' . date('YmdHis');

        // 调用spreadsheet处理表格
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $worksheet1 = $spreadsheet->getActiveSheet();
        $worksheet1->setTitle('产品列表');

        // A-Z列 设置字符串自动换行(会撑开单元格高度,并破坏设置的默认行高)，设置水平居左+垂直居中
        $worksheet1->getStyle("A:Z")->getAlignment()->setWrapText(TRUE);
        $worksheet1->getStyle('A:Z')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
        $worksheet1->getStyle('A:Z')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

        // 设置边框
        $worksheet1->setShowGridlines(false);
        $styleArray = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => '00000000'],
                ]
            ],
        ];
        $worksheet1->getStyle('A1:U' . (COUNT($list) + 2))->applyFromArray($styleArray, false);

        // 设置加粗
        $worksheet1->getStyle('A1')->getFont()->setBold(true);
        $worksheet1->getStyle('A2:U2')->getFont()->setBold(true);

        // 设置背景色
        $worksheet1->getStyle('A2:U2')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
        $worksheet1->getStyle('A2:U2')->getFill()->getStartColor()->setRGB('F5F5F5');

        // 合并单元格
        $worksheet1->mergeCells('A1:U1');

        // 设置行高
        $worksheet1->getRowDimension('1')->setRowHeight(40);

        // 设置水平居中
        $worksheet1->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // 设置每列列宽
        $worksheet1->getColumnDimension('A')->setWidth(10);
        $worksheet1->getColumnDimension('B')->setWidth(20);
        $worksheet1->getColumnDimension('C')->setWidth(20);
        $worksheet1->getColumnDimension('D')->setWidth(20);
        $worksheet1->getColumnDimension('E')->setWidth(20);
        $worksheet1->getColumnDimension('F')->setWidth(20);
        $worksheet1->getColumnDimension('G')->setWidth(10);
        $worksheet1->getColumnDimension('H')->setWidth(20);
        $worksheet1->getColumnDimension('I')->setWidth(10);
        $worksheet1->getColumnDimension('J')->setWidth(10);
        $worksheet1->getColumnDimension('K')->setWidth(10);
        $worksheet1->getColumnDimension('L')->setWidth(10);
        $worksheet1->getColumnDimension('M')->setWidth(10);
        $worksheet1->getColumnDimension('N')->setWidth(10);
        $worksheet1->getColumnDimension('O')->setWidth(12);
        $worksheet1->getColumnDimension('P')->setWidth(12);
        $worksheet1->getColumnDimension('Q')->setWidth(12);
        $worksheet1->getColumnDimension('R')->setWidth(10);
        $worksheet1->getColumnDimension('S')->setWidth(10);
        $worksheet1->getColumnDimension('T')->setWidth(10);
        $worksheet1->getColumnDimension('U')->setWidth(20);

        //设置当前的sheet
        $worksheet1->setCellValue('A1', $name . '（导出时间 ' . date('Y-m-d H:i:s') . '）');
        $worksheet1->setCellValue('A2', 'ID');
        $worksheet1->setCellValue('B2', '产品条码');
        $worksheet1->setCellValue('C2', '产品编码');
        $worksheet1->setCellValue('D2', '产品名称');
        $worksheet1->setCellValue('E2', '规格型号');
        $worksheet1->setCellValue('F2', '辅助属性');
        $worksheet1->setCellValue('G2', '产品品牌');
        $worksheet1->setCellValue('H2', '产品类别');
        $worksheet1->setCellValue('I2', '最小单位');
        $worksheet1->setCellValue('J2', '首选仓库');
        $worksheet1->setCellValue('K2', '指定库位');
        $worksheet1->setCellValue('L2', '当前库存');
        $worksheet1->setCellValue('M2', '库存上限');
        $worksheet1->setCellValue('N2', '库存下限');
        $worksheet1->setCellValue('O2', '重量(kg)');
        $worksheet1->setCellValue('P2', '预采购价');
        $worksheet1->setCellValue('Q2', '预销售价');
        $worksheet1->setCellValue('R2', '产品状态');
        $worksheet1->setCellValue('S2', '校对状态');
        $worksheet1->setCellValue('T2', '维护人');
        $worksheet1->setCellValue('U2', '备注');

        $i = 3;
        foreach ($list as $k => $v) {
            $worksheet1->setCellValueExplicit('A' . $i, $v['id'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $worksheet1->setCellValueExplicit('B' . $i, $v['barCode'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $worksheet1->setCellValue('C' . $i, $v['no']);
            $worksheet1->setCellValue('D' . $i, $v['name']);
            $worksheet1->setCellValue('E' . $i, $v['spec']);
            $worksheet1->setCellValue('F' . $i, $v['sku']);
            $worksheet1->setCellValue('G' . $i, $v['brandName']);
            $worksheet1->setCellValue('H' . $i, $v['categoryName']);
            $worksheet1->setCellValue('I' . $i, $v['unitName']);
            $worksheet1->setCellValue('J' . $i, $v['storageName']);
            $worksheet1->setCellValue('K' . $i, $v['locationName']);
            $worksheet1->setCellValue('L' . $i, $v['currentQty']);
            $worksheet1->setCellValue('M' . $i, $v['lowQty']);
            $worksheet1->setCellValue('N' . $i, $v['highQty']);
            $worksheet1->setCellValue('O' . $i, $v['weight']);
            $worksheet1->setCellValue('P' . $i, $v['purPrice']);
            $worksheet1->setCellValue('Q' . $i, $v['salePrice']);
            $worksheet1->setCellValue('R' . $i, 0 == $v['isDisable'] ? '正常' : (1 == $v['isDisable'] ? '即将停产' : (2 == $v['isDisable'] ? '已停产' : (9 == $v['isDisable'] ? '已禁用' : ''))));
            $worksheet1->setCellValue('S' . $i, 0 == $v['isProofread'] ? '未校对' : '已校对');
            $worksheet1->setCellValue('T' . $i, $v['serName']);
            $worksheet1->setCellValue('U' . $i, $v['description']);
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
    // 获取产品信息
    public function query()
    {
        if (!$this->myservice->verifyRight('GOODS_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/Goods/query 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $data = $this->get_goods_info($id);
        if ($data) {
            return $this->response->setJSON(['status' => 200, 'msg' => '查询成功！', 'data' => $data]);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
    // 通过ID获取产品信息
    private function get_goods_info(mixed $id)
    {
        $data = $this->db->table('goods')->where(['id' => $id, 'isDelete' => 0])->get()->getRowArray();
        if ($data) {
            $data['id'] = $id;
            $data['barCode'] = $data['barCode'];
            $data['no'] = $data['no'];
            $data['name'] = $data['name'];
            $data['spec'] = $data['spec'];
            $data['sku'] = $data['sku'];
            $data['brandId'] = intval($data['brandId']);
            $data['brandName'] = $data['brandName'];
            $data['unitId'] = intval($data['unitId']);
            $data['unitName'] = $data['unitName'];
            $data['storageId'] = intval($data['storageId']);
            $data['storageName'] = $data['storageName'];
            $data['locationId'] = intval($data['locationId']);
            $data['locationName'] = $data['locationName'];
            $data['categoryId'] = intval($data['categoryId']);
            $data['categoryName'] = $data['categoryName'];
            $data['weight'] = (float) $data['weight'];
            $data['purPrice'] = (float) $data['purPrice'];
            $data['salePrice'] = (float) $data['salePrice'];
            $data['devName'] = $data['devName'];
            $data['serName'] = $data['serName'];
            $data['uName'] = $data['uName'];
            $data['modName'] = $data['modName'];
            $data['createTime'] = $data['createTime'];
            $data['modifyTime'] = $data['modifyTime'];
            $data['isDisable'] = intval($data['isDisable']);
            $data['isProofread'] = intval($data['isProofread']);
            $data['files'] = $data['files'] == '' ? [] : json_decode($data['files'], true);
            $data['imgs'] = $data['imgs'] == '' ? [] : json_decode($data['imgs'], true);
            $data['description'] = $data['description'];
            $data['hasSon'] = intval($data['hasSon']);
            if ($data['hasSon'] > 0 && strlen($data['sonGoods']) > 0) {
                $list = (array)json_decode($data['sonGoods'], true);
                foreach ($list as $arr => $row) {
                    $curSonGoods = $this->db->table('goods')->where(['id' => $row['gid']])->get()->getRowArray();
                    $v[$arr]['gid'] = $row['gid'];
                    $v[$arr]['no'] = $curSonGoods['no'];
                    $v[$arr]['name'] = $curSonGoods['name'];
                    $v[$arr]['spec'] = $curSonGoods['spec'];
                    $v[$arr]['sku'] = $curSonGoods['sku'];
                    $v[$arr]['brand'] = $curSonGoods['brandName'];
                    $v[$arr]['qty1'] = $row['qty1'];
                    $v[$arr]['unit1'] = $curSonGoods['unitName'];
                    $v[$arr]['qty2'] = $row['qty2'];
                    $v[$arr]['unit2'] = $row['unit2'];
                    $v[$arr]['produceClass1'] = intval($curSonGoods['produceClass1']);
                    $v[$arr]['produceClass2'] = intval($curSonGoods['produceClass2']);
                    $v[$arr]['produceQty1'] = intval($curSonGoods['produceQty1']);
                    $v[$arr]['produceQty2'] = intval($curSonGoods['produceQty2']);
                    $v[$arr]['producePrice1'] = (float)$curSonGoods['producePrice1'];
                    $v[$arr]['producePrice2'] = (float)$curSonGoods['producePrice2'];
                    $v[$arr]['producePrice3'] = (float)$curSonGoods['producePrice3'];
                }
            }
            $data['sonGoods'] = $v ?? [];
            $data['produceClass1'] = intval($data['produceClass1']);
            $data['produceClass2'] = intval($data['produceClass2']);
            $data['produceQty1'] = intval($data['produceQty1']);
            $data['produceQty2'] = intval($data['produceQty2']);
            $data['producePrice1'] = (float)$data['producePrice1'];
            $data['producePrice2'] = (float)$data['producePrice2'];
            $data['producePrice3'] = (float)$data['producePrice3'];
        }
        return $data;
    }
    // 新增
    public function add()
    {
        if (!$this->myservice->verifyRight('GOODS_ADD')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/Goods/add 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $data = $this->request->getPost('postData');
        if (strlen($data) > 0) {
            $data = $this->validform((array)json_decode($data, true));
            // 如果返回的是 Response 对象，直接返回它
            if ($data instanceof \CodeIgniter\HTTP\Response) {
                return $data;
            }
            $data['billDate'] = date('Y-m-d');
            $info = array_intersect_key($data, array_flip(['no', 'barCode', 'name', 'spec', 'sku', 'unitId', 'unitName', 'categoryId', 'categoryName', 'brandId', 'brandName', 'storageId', 'storageName', 'locationId', 'locationName', 'lowQty', 'highQty', 'weight', 'purPrice', 'salePrice', 'devName', 'serName', 'billDate', 'imgs', 'files', 'description', 'hasSon', 'sonGoods', 'produceClass1', 'produceClass2', 'produceQty1', 'produceQty2', 'producePrice1', 'producePrice2', 'producePrice3', 'isDisable', 'isProofread', 'createTime', 'uName', 'modifyTime', 'modName']));
            $this->db->transStart();
            $this->db->table('goods')->insert($info);
            $data['id'] = $this->db->insertID();
            $this->myservice->log('新增产品，ID：' . $data['id'] . '，编码：' . $data['no'] . '，全称：' . $data['name'] . ('' == $data['spec'] ? '' : ' ' . $data['spec']) . ('' == $data['sku'] ? '' : ' ' . $data['sku']) . ('' == $data['brandName'] ? '' : ' ' . $data['brandName']));
            $this->db->transComplete();
            if ($this->db->transStatus() === FALSE) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '新增成功！', 'data' => $data]);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '提交的是空数据！']);
    }
    // 编辑
    public function update()
    {
        if (!$this->myservice->verifyRight('GOODS_UPDATE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/Goods/update 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $data = $this->request->getPost('postData');
        if (strlen($data) > 0) {
            $data = $this->validform((array)json_decode($data, true));
            // 如果返回的是 Response 对象，直接返回它
            if ($data instanceof \CodeIgniter\HTTP\Response) {
                return $data;
            }
            $oldData = $this->db->table('goods')->where(['id' => $data['id']])->get()->getRowArray();
            $data['isError'] = 0;
            $data['modifyTime'] = date('Y-m-d H:i:s');
            $info = array_intersect_key($data, array_flip(['no', 'barCode', 'name', 'spec', 'sku', 'unitId', 'unitName', 'categoryId', 'categoryName', 'brandId', 'brandName', 'storageId', 'storageName', 'locationId', 'locationName', 'lowQty', 'highQty', 'weight', 'purPrice', 'salePrice', 'devName', 'serName', 'imgs', 'files', 'description', 'hasSon', 'sonGoods', 'produceClass1', 'produceClass2', 'produceQty1', 'produceQty2', 'producePrice1', 'producePrice2', 'producePrice3', 'isDisable', 'isError', 'isProofread', 'modifyTime', 'modName']));
            $this->db->transStart();
            $this->db->table('goods')->where('id', $data['id'])->update($info);
            // 对比编辑内容并详尽的记录编辑历史
            $cArr = [];
            $dArr = [0 => '正常', 1 => '将停产', 2 => '已停产', 9 => '已禁用'];
            $pArr = [0 => '未校对', 1 => '已校对'];
            $data['categoryId'] != $oldData['categoryId'] && array_push($cArr, '将产品类别由（' . $oldData['categoryName'] . '）改为了（' . $data['categoryName'] . '）');
            $data['no'] != $oldData['no'] && array_push($cArr, '将产品编码由（' . $oldData['no'] . '）改为了（' . $data['no'] . '）');
            $data['barCode'] != $oldData['barCode'] && array_push($cArr, '将产品条码由（' . $oldData['barCode'] . '）改为了（' . $data['barCode'] . '）');
            $data['name'] != $oldData['name'] && array_push($cArr, '将产品名称由（' . $oldData['name'] . '）改为了（' . $data['name'] . '）');
            $data['spec'] != $oldData['spec'] && array_push($cArr, '将产品型号由（' . $oldData['spec'] . '）改为了（' . $data['spec'] . '）');
            $data['sku'] != $oldData['sku'] && array_push($cArr, '将辅助属性由（' . $oldData['sku'] . '）改为了（' . $data['sku'] . '）');
            $data['brandId'] != $oldData['brandId'] && array_push($cArr, '将产品品牌由（' . $oldData['brandName'] . '）改为了（' . $data['brandName'] . '）');
            $data['unitId'] != $oldData['unitId'] && array_push($cArr, '将最小单位由（' . $oldData['unitName'] . '）改为了（' . $data['unitName'] . '）');
            $data['storageId'] != $oldData['storageId'] && array_push($cArr, '将首选仓库由（' . $oldData['storageName'] . '）改为了（' . $data['storageName'] . '）');
            $data['locationId'] != $oldData['locationId'] && array_push($cArr, '将指定库位由（' . $oldData['locationName'] . '）改为了（' . $data['locationName'] . '）');
            $data['lowQty'] != $oldData['lowQty'] && array_push($cArr, '将库存下限由（' . $oldData['lowQty'] . '）改为了（' . $data['lowQty'] . '）');
            $data['highQty'] != $oldData['highQty'] && array_push($cArr, '将库存上限由（' . $oldData['highQty'] . '）改为了（' . $data['highQty'] . '）');
            $data['weight'] != $oldData['weight'] && array_push($cArr, '将单位重量由（' . $oldData['weight'] . '）改为了（' . $data['weight'] . '）');
            $data['purPrice'] != $oldData['purPrice'] && array_push($cArr, '将预采购价由（' . $oldData['purPrice'] . '）改为了（' . $data['purPrice'] . '）');
            $data['salePrice'] != $oldData['salePrice'] && array_push($cArr, '将预销售价由（' . $oldData['salePrice'] . '）改为了（' . $data['salePrice'] . '）');
            $data['devName'] != $oldData['devName'] && array_push($cArr, '将开发人由（' . $oldData['devName'] . '）改为了（' . $data['devName'] . '）');
            $data['serName'] != $oldData['serName'] && array_push($cArr, '将维护人由（' . $oldData['serName'] . '）改为了（' . $data['serName'] . '）');
            $data['isDisable'] != $oldData['isDisable'] && array_push($cArr, '将产品状态由（' . $dArr[$oldData['isDisable']] . '）改为了（' . $dArr[$data['isDisable']] . '）');
            $data['isProofread'] != $oldData['isProofread'] && array_push($cArr, '将校对状态由（' . $pArr[$oldData['isProofread']] . '）改为了（' . $pArr[$data['isProofread']] . '）');
            $data['description'] != $oldData['description'] && array_push($cArr, '将备注信息由（' . $oldData['description'] . '）改为了（' . $data['description'] . '）');
            $data['producePrice3'] != $oldData['producePrice3'] && array_push($cArr, '将配货提成总价由（' . $oldData['producePrice3'] . '）改为了（' . $data['producePrice3'] . '）');
            if ($data['imgs'] != $oldData['imgs']) {
                $imgArr1 = $data['imgs'] == '' ? [] : json_decode($data['imgs'], true);
                $imgArr2 = $oldData['imgs'] == '' ? [] : json_decode($oldData['imgs'], true);
                $imgArr3 = array_diff($imgArr2, $imgArr1);
                $imgArr4 = array_diff($imgArr1, $imgArr2);
                $imgArr3 && array_push($cArr, '删除了 ' . count($imgArr3) . ' 张旧的产品图片');
                $imgArr4 && array_push($cArr, '新增了 ' . count($imgArr4) . ' 张新的产品图片');
            }
            if ($data['files'] != $oldData['files']) {
                $fileArr1 = $data['files'] == '' ? [] : array_column(json_decode($data['files'], true), 'url');
                $fileArr2 = $oldData['files'] == '' ? [] : array_column(json_decode($oldData['files'], true), 'url');
                $fileArr3 = array_diff($fileArr2, $fileArr1);
                $fileArr4 = array_diff($fileArr1, $fileArr2);
                $fileArr3 && array_push($cArr, '删除了 ' . count($fileArr3) . ' 个旧的次要文件');
                $fileArr4 && array_push($cArr, '新增了 ' . count($fileArr4) . ' 个新的次要文件');
            }
            if ($data['sonGoods'] != $oldData['sonGoods']) {
                $sonArr1 = $data['sonGoods'] == '' ? [] : json_decode($data['sonGoods'], true);
                $sonArr2 = $oldData['sonGoods'] == '' ? [] : json_decode($oldData['sonGoods'], true);
                if (count($sonArr2) > 0) { //原先有子产品
                    if (count($sonArr1) > 0) { //现在也有子产品【仔细严谨进行对比判断处理】
                        $sonArr3 = $sonArr4 = [];
                        foreach ($sonArr1 as $k => $v) {
                            array_push($sonArr3, $v['no'] . ' × ' . $v['qty1'] . $v['unit1'] . ' × ' . $v['qty2'] . $v['unit2']);
                        }
                        foreach ($sonArr2 as $k => $v) {
                            array_push($sonArr4, $v['no'] . ' × ' . $v['qty1'] . $v['unit1'] . ' × ' . $v['qty2'] . $v['unit2']);
                        }
                        $sonArr5 = array_diff($sonArr4, $sonArr3);
                        $sonArr6 = array_diff($sonArr3, $sonArr4);
                        if ($sonArr5) {
                            array_push($cArr, '删除了 ' . count($sonArr5) . ' 个子产品');
                            foreach ($sonArr5 as $k => $v) {
                                array_push($cArr, '删除了子产品 ' . $v);
                            }
                        }
                        if ($sonArr6) {
                            array_push($cArr, '新增了 ' . count($sonArr6) . ' 个子产品');
                            foreach ($sonArr6 as $k => $v) {
                                array_push($cArr, '新增了子产品 ' . $v);
                            }
                        }
                    } else { //现在无子产品
                        array_push($cArr, '删除了 ' . count($sonArr2) . ' 种子产品');
                        foreach ($sonArr2 as $k => $v) {
                            array_push($cArr, '删除了子产品 ' . $v['no'] . ' ' . $v['qty1'] . $v['unit1'] . ' × ' . $v['qty2'] . $v['unit2']);
                        }
                    }
                } else { //原先无子产品
                    if (count($sonArr1) > 0) { //现在有子产品
                        array_push($cArr, '添加了 ' . count($sonArr1) . ' 种子产品');
                        foreach ($sonArr1 as $k => $v) {
                            array_push($cArr, '添加了子产品 ' . $v['no'] . ' ' . $v['qty1'] . $v['unit1'] . ' × ' . $v['qty2'] . $v['unit2']);
                        }
                    }
                }
            }
            // 将最终编辑记录入库
            if (count($cArr) > 0) {
                $this->db->table('goods_modify_his')->insert([
                    'goodsId' => $data['id'],
                    'change' => implode("\r\n", $cArr),
                    'changeTime' => date('Y-m-d H:i:s'),
                    'uId' => $this->myerp['uId'],
                    'uName' => $this->myerp['uName']
                ]);
            }
            $this->myservice->log('编辑了产品，ID：' . $data['id'] . '，编码：' . $data['no'] . '，全称：' . $data['name'] . ('' == $data['spec'] ? '' : ' ' . $data['spec']) . ('' == $data['sku'] ? '' : ' ' . $data['sku']) . ('' == $data['brandName'] ? '' : ' ' . $data['brandName']));
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '编辑成功！', 'data' => $data['id']]);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '提交的是空数据！']);
    }
    // 删除
    public function delete()
    {
        if (!$this->myservice->verifyRight('GOODS_DELETE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/Goods/delete 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $data = $this->db->table('goods')->where('id', $id)->get()->getRowArray();
        if ($data) {
            if (
                $this->db->table('bill_order_info')->where(['goodsId' => $id, 'isDelete' => 0])->countAllResults() > 0 ||
                $this->db->table('bill_inventory_info')->where(['goodsId' => $id, 'isDelete' => 0])->countAllResults() > 0 ||
                $this->db->table('bill_contract_info')->where(['no' => $data['no'], 'isDelete' => 0])->countAllResults() > 0
            ) {
                return $this->response->setJSON(['status' => -1, 'msg' => '产品已发生过业务，不可删除！']);
            }
            $this->db->transStart();
            $this->db->table('goods')->where('id', $id)->update(['isDelete' => 1]);
            $this->myservice->log('删除了产品，ID：' . $data['id'] . '，编码：' . $data['no'] . '，全称：' . $data['name'] . ('' == $data['spec'] ? '' : ' ' . $data['spec']) . ('' == $data['sku'] ? '' : ' ' . $data['sku']) . ('' == $data['brandName'] ? '' : ' ' . $data['brandName']));
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '删除失败！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '删除成功！']);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
    // 获取产品编辑历史
    public function getModifyHis()
    {
        if (!$this->myservice->verifyRight('GOODS_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/Goods/getModifyHis 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $page = max(intval($this->request->getGetPost('page')), 1);
        $rows = max(intval($this->request->getGetPost('rows')), 100);
        $sidx = $this->request->getGetPost('sidx');
        $sord = strtoupper((string)$this->request->getGetPost('sord')) === 'ASC' ? 'ASC' : 'DESC';
        $id = intval($this->request->getGetPost('id'));
        $beginDate = $this->request->getGetPost('beginDate');
        $endDate = $this->request->getGetPost('endDate');
        $skey = $this->request->getGetPost('skey');
        $builder = $this->db->table('goods_modify_his')->where(['goodsId' => $id, 'isDelete' => 0]);
        if ($beginDate) {
            $builder->where('changeTime >=', $beginDate);
        }
        if ($endDate) {
            $builder->where('changeTime <=', $endDate);
        }
        if ($skey !== '' && $skey !== null) {
            $keywords = array_values(array_filter(explode(',', $skey), 'strlen'));
            foreach ($keywords as $kw) {
                $builder->groupStart()
                    ->like('change', $this->db->escapeLikeString($kw))
                    ->orLike('uName', $this->db->escapeLikeString($kw))
                ->groupEnd();
            }
        }
        $allowedSort = ['id','goodsId','change','changeTime','uId','uName'];
        $orderField  = $sidx ? (in_array($sidx, $allowedSort, true) ? $sidx : 'id') : 'id';
        $list = (clone $builder)->orderBy($orderField, $sord)->limit($rows)->offset($rows * ($page - 1))->get()->getResultArray();
        $total = $builder->countAllResults();
        $json['status'] = 200;
        $json['msg'] = 'success';
        $json['data']['page'] = $page;
        $json['data']['records'] = $total;
        $json['data']['total'] = ceil($json['data']['records'] / $rows);
        $json['data']['rows'] = $list;
        return $this->response->setJSON($json);
    }
    // 导出子产品清单
    public function exportSons()
    {
        if (!$this->myservice->verifyRight('GOODS_EXPORT')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/Goods/exportSons 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $data = $this->get_goods_info($id);
        if ($data) {
            $name = $data['no'] . '(' . $data['name'] . ')子产品(元件)清单_' . date('YmdHis');
            // 调用spreadsheet处理表格
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $worksheet1 = $spreadsheet->getActiveSheet();
            $worksheet1->setTitle('子产品（元件）总清单');
            // A-Z列 设置字符串自动换行(会撑开单元格高度,并破坏设置的默认行高)，设置水平居左+垂直居中
            $worksheet1->getStyle("A:Z")->getAlignment()->setWrapText(TRUE);
            $worksheet1->getStyle('A:Z')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
            $worksheet1->getStyle('A:Z')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
            // 设置边框
            $worksheet1->setShowGridlines(false);
            $styleArray = [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        'color' => ['argb' => '00000000'],
                    ]
                ],
            ];
            $worksheet1->getStyle('A1:J' . (count($data['sonGoods']) + 5))->applyFromArray($styleArray, false);
            // 设置加粗
            $worksheet1->getStyle('A1')->getFont()->setBold(true);
            $worksheet1->getStyle('A5:J5')->getFont()->setBold(true);
            // 设置背景色
            $worksheet1->getStyle('A1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
            $worksheet1->getStyle('A1')->getFill()->getStartColor()->setRGB('ffff00');
            $worksheet1->getStyle('A5:J5')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
            $worksheet1->getStyle('A5:J5')->getFill()->getStartColor()->setRGB('eeeeee');
            // 合并单元格
            $worksheet1->mergeCells('A1:J1');
            $worksheet1->mergeCells('A2:B2');
            $worksheet1->mergeCells('C2:E2');
            $worksheet1->mergeCells('G2:J2');
            $worksheet1->mergeCells('A3:B3');
            $worksheet1->mergeCells('C3:J3');
            $worksheet1->mergeCells('A4:J4');
            // 设置行高
            $worksheet1->getRowDimension('1')->setRowHeight(40);
            $worksheet1->getRowDimension('4')->setRowHeight(40);
            // 设置水平居中
            $worksheet1->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $worksheet1->getStyle('A4')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            // 设置每列列宽
            $worksheet1->getColumnDimension('A')->setWidth(10);
            $worksheet1->getColumnDimension('B')->setWidth(20);
            $worksheet1->getColumnDimension('C')->setWidth(20);
            $worksheet1->getColumnDimension('D')->setWidth(20);
            $worksheet1->getColumnDimension('E')->setWidth(20);
            $worksheet1->getColumnDimension('F')->setWidth(10);
            $worksheet1->getColumnDimension('G')->setWidth(10);
            $worksheet1->getColumnDimension('H')->setWidth(10);
            $worksheet1->getColumnDimension('I')->setWidth(6);
            $worksheet1->getColumnDimension('J')->setWidth(3);
            //设置当前的sheet
            $worksheet1->setCellValue('A1', $data['name']);
            $worksheet1->setCellValue('A2', '产品编码');
            $worksheet1->setCellValue('C2', $data['no']);
            $worksheet1->setCellValue('F2', '导出时间');
            $worksheet1->setCellValue('G2', date('Y-m-d H:i:s'));
            $worksheet1->setCellValue('A3', '备注');
            $worksheet1->setCellValue('C3', $data['description']);
            $worksheet1->setCellValue('A4', '子产品（元件）清单');
            $worksheet1->setCellValue('A5', '序号');
            $worksheet1->setCellValue('B5', '产品编码');
            $worksheet1->setCellValue('C5', '产品名称');
            $worksheet1->setCellValue('D5', '规格型号');
            $worksheet1->setCellValue('E5', '辅助属性');
            $worksheet1->setCellValue('F5', '产品品牌');
            $worksheet1->setCellValue('G5', '每份数量');
            $worksheet1->setCellValue('H5', '每份单位');
            $worksheet1->setCellValue('I5', '份数');
            $worksheet1->setCellValue('J5', '');
            $i = 6;
            foreach ($data['sonGoods'] as $k => $v) {
                $worksheet1->setCellValue('A' . $i, $i - 5);
                $worksheet1->setCellValue('B' . $i, $v['no']);
                $worksheet1->setCellValue('C' . $i, $v['name']);
                $worksheet1->setCellValue('D' . $i, $v['spec']);
                $worksheet1->setCellValue('E' . $i, $v['sku']);
                $worksheet1->setCellValue('F' . $i, $v['brand']);
                $worksheet1->setCellValue('G' . $i, $v['qty1']);
                $worksheet1->setCellValue('H' . $i, $v['unit1']);
                $worksheet1->setCellValue('I' . $i, $v['qty2']);
                $worksheet1->setCellValue('J' . $i, '份');
                $i++;
            }
            // 如果子产品又包含了子产品，导出子产品的附属子产品
            if (count($data['sonGoods']) > 0) {
                $kitCids_arr = array_column($this->db->table('category')->select('id')->where('FIND_IN_SET("245", path) >', 0, false)->where('isDelete', 0)->get()->getResultArray(), 'id');
                $kitGoodsIds_arr = array_column($this->db->table('goods')->select('id')->whereIn('categoryId', $kitCids_arr)->where('isDelete', 0)->get()->getResultArray(), 'id');
                foreach ($data['sonGoods'] as $key => $value) {
                    if (in_array($value['gid'], $kitGoodsIds_arr)) {
                        $son = $this->db->table('goods')->where('id', $value['gid'])->get()->getRowArray();
                        if (isset($son['sonGoods']) && strlen($son['sonGoods']) > 0) {
                            $sonGoods = json_decode($son['sonGoods'], true);
                            if (is_array($sonGoods) && count($sonGoods) > 0) {
                                //目前不采用多工作表模式，因为担心她们导出后打印的时候，粗心大意没有全表打印，导致打漏了清单的隐患。
                                //创建新工作表
                                //$worksheet2 = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($spreadsheet, '附属子产品（元件）清单 ' . $son['no']);
                                //$spreadsheet->addSheet($worksheet2);
                                // 设置边框
                                $worksheet1->getStyle('A' . $i . ':J' . ($i + count($sonGoods) + 4))->applyFromArray($styleArray, false);
                                // 设置加粗
                                $worksheet1->getStyle('A' . $i)->getFont()->setBold(true);
                                $worksheet1->getStyle('A' . ($i + 4) . ':J' . ($i + 4))->getFont()->setBold(true);
                                // 设置背景色
                                $worksheet1->getStyle('A' . $i)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
                                $worksheet1->getStyle('A' . $i)->getFill()->getStartColor()->setRGB('ffff00');
                                $worksheet1->getStyle('A' . ($i + 4) . ':J' . ($i + 4))->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
                                $worksheet1->getStyle('A' . ($i + 4) . ':J' . ($i + 4))->getFill()->getStartColor()->setRGB('eeeeee');
                                // 合并单元格
                                $worksheet1->mergeCells('A' . $i . ':J' . $i);
                                $i++;
                                $worksheet1->mergeCells('A' . $i . ':B' . $i);
                                $worksheet1->mergeCells('C' . $i . ':E' . $i);
                                $worksheet1->mergeCells('G' . $i . ':J' . $i);
                                $i++;
                                $worksheet1->mergeCells('A' . $i . ':B' . $i);
                                $worksheet1->mergeCells('C' . $i . ':J' . $i);
                                $i++;
                                $worksheet1->mergeCells('A' . $i . ':J' . $i);
                                // 设置行高
                                $worksheet1->getRowDimension('' . ($i - 3))->setRowHeight(40);
                                $worksheet1->getRowDimension('' . $i)->setRowHeight(40);
                                // 设置水平居中
                                $worksheet1->getStyle('A' . ($i - 3))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                                $worksheet1->getStyle('A' . $i)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                                //设置当前的sheet
                                $worksheet1->setCellValue('A' . ($i - 3), '附属子产品 ' . $son['no'] . ' ' . $son['name']);
                                $worksheet1->setCellValue('A' . ($i - 2), '产品编码');
                                $worksheet1->setCellValue('C' . ($i - 2), $son['no']);
                                $worksheet1->setCellValue('F' . ($i - 2), '导出时间');
                                $worksheet1->setCellValue('G' . ($i - 2), date('Y-m-d H:i:s'));
                                $worksheet1->setCellValue('A' . ($i - 1), '备注');
                                $worksheet1->setCellValue('C' . ($i - 1), $son['description']);
                                $worksheet1->setCellValue('A' . $i, '子产品（元件）清单');
                                $i++;
                                $worksheet1->setCellValue('A' . $i, '序号');
                                $worksheet1->setCellValue('B' . $i, '产品编码');
                                $worksheet1->setCellValue('C' . $i, '产品名称');
                                $worksheet1->setCellValue('D' . $i, '规格型号');
                                $worksheet1->setCellValue('E' . $i, '辅助属性');
                                $worksheet1->setCellValue('F' . $i, '产品品牌');
                                $worksheet1->setCellValue('G' . $i, '每份数量');
                                $worksheet1->setCellValue('H' . $i, '每份单位');
                                $worksheet1->setCellValue('I' . $i, '份数');
                                $worksheet1->setCellValue('J' . $i, '');
                                $i++;
                                $j = 1;
                                foreach ($sonGoods as $k => $v) {
                                    $worksheet1->setCellValue('A' . $i, $j);
                                    $j++;
                                    $worksheet1->setCellValue('B' . $i, $v['no']);
                                    $worksheet1->setCellValue('C' . $i, $v['name']);
                                    $worksheet1->setCellValue('D' . $i, $v['spec']);
                                    $worksheet1->setCellValue('E' . $i, $v['sku']);
                                    $worksheet1->setCellValue('F' . $i, $v['brand']);
                                    $worksheet1->setCellValue('G' . $i, $v['qty1']);
                                    $worksheet1->setCellValue('H' . $i, $v['unit1']);
                                    $worksheet1->setCellValue('I' . $i, $v['qty2']);
                                    $worksheet1->setCellValue('J' . $i, '份');
                                    $i++;
                                }
                            }
                        }
                    }
                }
            }
            // 合并底部签字单元格
            $worksheet1->getRowDimension('' . $i)->setRowHeight(50); // 设置行高
            $worksheet1->mergeCells('A' . $i . ':C' . $i);
            $worksheet1->mergeCells('D' . $i . ':F' . $i);
            $worksheet1->mergeCells('A' . ($i + 1) . ':C' . ($i + 1));
            $worksheet1->mergeCells('D' . ($i + 1) . ':F' . ($i + 1));
            $worksheet1->setCellValue('A' . $i, '清单导出责任人签字：');
            $worksheet1->setCellValue('D' . $i, '部门负责人审核签字：');
            $i++;
            $worksheet1->getRowDimension('' . $i)->setRowHeight(30); // 设置行高
            $worksheet1->mergeCells('A' . $i . ':C' . $i);
            $worksheet1->mergeCells('D' . $i . ':F' . $i);
            $worksheet1->mergeCells('A' . ($i + 1) . ':C' . ($i + 1));
            $worksheet1->mergeCells('D' . ($i + 1) . ':F' . ($i + 1));
            $worksheet1->setCellValue('A' . $i, '日期：');
            $worksheet1->setCellValue('D' . $i, '日期：');

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
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
    // 数据验证
    private function validform(array $data)
    {
        // 检测主要信息
        $data['id'] = isset($data['id']) ? intval($data['id']) : -1;
        $data['categoryId'] = isset($data['categoryId']) ? intval($data['categoryId']) : -1;
        if ($data['categoryId'] < 1) {
            return $this->response->setJSON(['status' => -1, 'msg' => '产品类别不能为空！']);
        }
        $data['categoryName'] = $this->db->table('category')->select('name')->where('id', $data['categoryId'])->get()->getRowArray()['name'] ?? '';
        if (!$data['categoryName']) {
            return $this->response->setJSON(['status' => -1, 'msg' => '产品类别不存在！']);
        }
        if (strlen($data['no']) < 1) {
            return $this->response->setJSON(['status' => -1, 'msg' => '产品编码不能为空！']);
        }
        if ($this->db->table('goods')->where(['no' => $data['no'], 'isDelete' => 0, 'id !=' => $data['id']])->countAllResults() > 0) {
            return $this->response->setJSON(['status' => -1, 'msg' => '产品编码已经存在！']);
        }
        if ($data['barCode'] && $this->db->table('goods')->where(['barCode' => $data['barCode'], 'isDelete' => 0, 'id !=' => $data['id']])->countAllResults() > 0) {
            return $this->response->setJSON(['status' => -1, 'msg' => '产品条码已经存在！']);
        }
        if (strlen($data['name']) < 1) {
            return $this->response->setJSON(['status' => -1, 'msg' => '产品名称不能为空！']);
        }
        if ($this->db->table('goods')->where(['name' => $data['name'], 'spec' => $data['spec'], 'sku' => $data['sku'], 'id !=' => $data['id'], 'isDelete' => 0])->countAllResults() > 0) {
            return $this->response->setJSON(['status' => -1, 'msg' => '产品名称 + 规格型号 + 辅助属性已经存在！']);
        }
        $data['unitId'] = intval($data['unitId']);
        if ($data['unitId'] < 1) {
            return $this->response->setJSON(['status' => -1, 'msg' => '最小单位不能为空！']);
        }
        $data['unitName'] = $this->db->table('category')->select('name')->where('id', $data['unitId'])->get()->getRowArray()['name'] ?? '';
        if (!$data['unitName']) {
            return $this->response->setJSON(['status' => -1, 'msg' => '计量单位不存在！']);
        }
        $data['storageId'] = intval($data['storageId']);
        if ($data['storageId'] < 1) {
            return $this->response->setJSON(['status' => -1, 'msg' => '首选仓库不能为空！']);
        }
        $data['storageName'] = $this->db->table('storage')->select('name')->where('id', $data['storageId'])->get()->getRowArray()['name'] ?? '';
        if (!$data['storageName']) {
            return $this->response->setJSON(['status' => -1, 'msg' => '首选仓库不存在！']);
        }
        // 检测子产品信息
        $kitCids_arr = array_column($this->db->table('category')->select('id')->where('FIND_IN_SET("245", path) >', 0, false)->where('isDelete', 0)->get()->getResultArray(), 'id');
        $suitCids_arr = array_column($this->db->table('category')->select('id')->where('FIND_IN_SET("280", path) >', 0, false)->where('isDelete', 0)->get()->getResultArray(), 'id');
        $kitGoods_arr = array_column($this->db->table('goods')->select('id')->whereIn('categoryId', $kitCids_arr)->where('isDelete', 0)->get()->getResultArray(), 'id');
        $suitGoods_arr = array_column($this->db->table('goods')->select('id')->whereIn('categoryId', $suitCids_arr)->where('isDelete', 0)->get()->getResultArray(), 'id');
        $data['hasSon'] = 0;
        if (strlen($data['sonGoods']) > 0) {
            $sonlist = (array)json_decode($data['sonGoods'], true);
            if (count($sonlist) > 0) {
                $sonTemp = [];
                foreach ($sonlist as $sonkey => $sonrow) {
                    if (empty($sonrow['gid'])) {
                        return $this->response->setJSON(['status' => -1, 'msg' => "子产品 {$sonrow['no']} 的ID不能为空！"]);
                    } elseif ($this->db->table('goods')->where(['id' => $sonrow['gid'], 'isDisable <' => 9, 'isDelete' => 0])->countAllResults() < 1) {
                        return $this->response->setJSON(['status' => -1, 'msg' => "子产品 {$sonrow['no']} 不存在或已禁用！请重新选择！"]);
                    } elseif ($sonrow['gid'] == $data['id']) {
                        return $this->response->setJSON(['status' => -1, 'msg' => "禁止套娃！子产品 {$sonrow['no']} 与父产品重复了！"]);
                    } elseif (in_array($data['categoryId'], $kitCids_arr) && in_array($sonrow['gid'], $kitGoods_arr)) { //套件包含套件
                        $sonson = $this->db->table('goods')->select('no, sonGoods, producePrice3')->where('id', $sonrow['gid'])->get()->getRowArray();
                        if (isset($sonson['sonGoods']) && strlen($sonson['sonGoods']) > 0) {
                            $sonsonlist = (array)json_decode($sonson['sonGoods'], true);
                            if (count($sonsonlist) > 0) {
                                foreach ($sonsonlist as $sonsonkey => $sonsonrow) {
                                    if (in_array($sonsonrow['gid'], $kitGoods_arr)) { //子套件又包含套件
                                        return $this->response->setJSON(['status' => -1, 'msg' => '禁止套娃！需配货套件禁止包含的子套件里又包含了子套件（三层套娃）！']);
                                    }
                                }
                            }
                        }
                    } elseif (in_array($data['categoryId'], $kitCids_arr) && in_array($sonrow['gid'], $suitGoods_arr)) { //套件包含套餐
                        return $this->response->setJSON(['status' => -1, 'msg' => '禁止套娃！需配货套件禁止包含销售搭配套餐！']);
                    } elseif (in_array($data['categoryId'], $suitCids_arr) && in_array($sonrow['gid'], $suitGoods_arr)) { //套餐包含套餐
                        return $this->response->setJSON(['status' => -1, 'msg' => '禁止套娃！销售搭配套餐禁止包含其他销售搭配套餐！']);
                    } elseif (
                        !is_numeric($sonrow['qty1']) ||
                        !is_numeric($sonrow['qty2']) ||
                        (float)$sonrow['qty1'] <= 0 ||
                        (float)$sonrow['qty2'] <= 0 ||
                        (count(explode('.', $sonrow['qty1'])) > 1 && strlen(explode('.', $sonrow['qty1'])[1]) > 5) ||
                        count(explode('.', $sonrow['qty2'])) > 1
                    ) {
                        return $this->response->setJSON(['status' => -1, 'msg' => "子产品每份数量需是大于 0 的整数或小数（小数点后最多5位）！份数需是大于 0 的整数！异常子产品：{$sonrow['no']}"]);
                    } elseif (isset($sonTemp[$sonrow['no']]) && $sonrow['qty1'] == $sonTemp[$sonrow['no']]['qty1'] && $sonrow['qty2'] == $sonTemp[$sonrow['no']]['qty2']) {
                        return $this->response->setJSON(['status' => -1, 'msg' => "子产品 {$sonrow['no']} 完全重复，存在每份数量与份数完全相同的行！请合并或纠正重复项！"]);
                    }
                    $sonTemp[$sonrow['no']]['qty1'] = $sonrow['qty1'];
                    $sonTemp[$sonrow['no']]['qty2'] = $sonrow['qty2'];
                }
                $data['hasSon'] = 1;
            } else {
                return $this->response->setJSON(['status' => -1, 'msg' => '子产品解析错误！']);
            }
        } else {
            if (in_array($data['categoryId'], $kitCids_arr) || in_array($data['categoryId'], $suitCids_arr)) {
                return $this->response->setJSON(['status' => -1, 'msg' => '销售搭配套餐、需配货套件 类产品必须严格准确录入子产品信息！']);
            }
        }
        // 处理剩余信息
        $data['devName'] = $data['devName'] ?? '';
        $data['serName'] = $data['serName'] ?? '';
        if (in_array($data['categoryId'], $kitCids_arr) && ($data['devName'] == '' || $data['serName'] == '')) {
            return $this->response->setJSON(['status' => -1, 'msg' => '自产产品（需配货套件类）必须录入开发人和维护人！']);
        }
        $data['brandId'] = intval($data['brandId']);
        $data['brandName'] = $this->db->table('category')->select('name')->where('id', $data['brandId'])->get()->getRowArray()['name'] ?? '';
        $data['locationId'] = intval($data['locationId']);
        $data['locationName'] = $this->db->table('location')->select('name')->where('id', $data['locationId'])->get()->getRowArray()['name'] ?? '';
        $data['lowQty'] = isset($data['lowQty']) ? intval($data['lowQty']) : 0;
        $data['highQty'] = isset($data['highQty']) ? intval($data['highQty']) : 0;
        $data['weight'] = isset($data['weight']) ? (float) $data['weight'] : 0;
        $data['purPrice'] = isset($data['purPrice']) ? (float) $data['purPrice'] : 0;
        $data['salePrice'] = isset($data['salePrice']) ? (float) $data['salePrice'] : 0;
        $data['isDisable'] = isset($data['isDisable']) ? intval($data['isDisable']) : 0;
        $data['isProofread'] = isset($data['isProofread']) ? intval($data['isProofread']) : 0;
        $data['imgs'] = strlen($data['imgs']) < 10 ? '' : $data['imgs'];
        $data['files'] = strlen($data['files']) < 10 ? '' : $data['files'];
        $data['description'] = $data['description'] ?? '';
        $data['modName'] = $this->myerp['uName'];
        $data['uName'] = $this->myerp['uName'];
        $data['createTime'] = date('Y-m-d H:i:s');
        $data['modifyTime'] = date('Y-m-d H:i:s');
        $data['produceClass1'] = intval($data['produceClass1']);
        $data['produceClass2'] = intval($data['produceClass2']);
        $data['produceQty1'] = intval($data['produceQty1']);
        $data['produceQty2'] = intval($data['produceQty2']);
        $data['producePrice1'] = (float)$data['producePrice1'];
        $data['producePrice2'] = (float)$data['producePrice2'];
        $data['producePrice3'] = (float)$data['producePrice3'];
        return $data;
    }

    // 上传产品图片
    public function uploadImg()
    {
        if (!$this->myservice->verifyRight('GOODS_UPDATE') && !$this->myservice->verifyRight('PO_ADD')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/Goods/uploadImg 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        set_time_limit(0);
        session_write_close();
        if (empty($_FILES)){
            return $this->response->setJSON(['status' => -1, 'msg' => '没有上传文件！']);
        };
        $uppath = 'data/upfile/goods/img/';
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
        $this->myservice->log('上传了产品图片：' . $_FILES['file']['name']);
        $json['status'] = 200;
        $json['msg'] = '图片上传成功！';
        $json['img'] = $target_file;
        return $this->response->setJSON($json);
    }
    // 保存图片信息
    public function saveImg()
    {
        if (!$this->myservice->verifyRight('GOODS_UPDATE') && !$this->myservice->verifyRight('PO_ADD')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/Goods/addImg 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $data = $this->request->getPost('postData');
        if (strlen($data) > 0) {
            $data = (array)json_decode($data, true);
            $id = isset($data['id']) ? $data['id'] : 0;
            if ($id < 1) {
                return $this->response->setJSON(['status' => -1, 'msg' => '目标产品不存在！']);
            }
            $this->db->transStart();
            if (is_array($data['imgs']) && count($data['imgs']) > 0) {
                $shopImg = '';
                foreach ($data['imgs'] as $k => $img) {
                    if (strpos($img, 'http') === 0) {
                        $shopImg = $img;
                        unset($data['imgs'][$k]);
                    }
                }
                if (count($data['imgs']) > 0) {
                    $imgs = json_encode($data['imgs']);
                } else {
                    $imgs = '';
                }
                $this->db->table('goods')->where('id', $id)->update(['imgs' => $imgs, 'shopImg' => $shopImg]);
            } else {
                $this->db->table('goods')->where('id', $id)->update(['imgs' => '', 'shopImg' => '']);
            }
            $goods = $this->db->table('goods')->select('id,no,imgs,shopImg')->where('id', $id)->get()->getRowArray();
            $imgs = [];
            if ($goods['imgs']) {
                $imgs = json_decode($goods['imgs'], true);
            }
            if ($goods['shopImg']) {
                array_push($imgs, $goods['shopImg']);
            }
            $goods['imgs'] = implode(',', $imgs);
            $this->myservice->log('更新了产品图片，产品ID：' . $goods['id'] . '，产品编码：' . $goods['no']);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '保存成功！', 'data' => $goods]);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '提交的是空数据！']);
    }
    // 通过ID获取图片信息
    public function getImgByInvId()
    {
        if (!$this->myservice->verifyRight('GOODS_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/Goods/getImgByInvId 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $goodsId = intval($this->request->getPost('goodsId'));
        $goods = $this->db->table('goods')->select('imgs, shopImg')->where('id', $goodsId)->get()->getRowArray();
        $imgs = [];
        if ($goods) {
            if ($goods['imgs']) {
                $imgs = json_decode($goods['imgs'], true);
            }
            if ($goods['shopImg']) {
                array_push($imgs, $goods['shopImg']);
            }
        }
        $json['status'] = 200;
        $json['msg'] = 'success';
        $json['imgs'] = $imgs;
        return $this->response->setJSON($json);
    }

    // 上传文件【产品管理页、产品文件管理页，上传共用此方法】
    public function uploadfile()
    {
        if (!$this->myservice->verifyRight('GOODS_UPDATE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/Goods/uploadfile 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        set_time_limit(0);
        session_write_close();
        if (empty($_FILES)){
            return $this->response->setJSON(['status' => -1, 'msg' => '没有上传文件！']);
        };
        $uppath = 'data/upfile/goods/file/';
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
        $this->myservice->log('上传了产品资料：' . $_FILES['file']['name']);
        $json['status'] = 200;
        $json['msg'] = '资料上传成功！';
        $json['file'] = ['name' => $_FILES['file']['name'], 'url' => $target_file, 'upName' => $this->myerp['uName'], 'upTime' => date('Y-m-d')];
        return $this->response->setJSON($json);
    }

    // 产品文件 列表
    public function fileList()
    {
        $type = $this->request->getGetPost('type');
        if (!$this->myservice->verifyRight(strtoupper(explode('_', $type)[0]) . '_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/Goods/fileList 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $page = max(intval($this->request->getGetPost('page')), 1);
        $rows = max(intval($this->request->getGetPost('rows')), 100);
        $sidx = $this->request->getGetPost('sidx');
        $sord = strtoupper((string)$this->request->getGetPost('sord')) === 'ASC' ? 'ASC' : 'DESC';
        $beginDate = $this->request->getGetPost('beginDate');
        $endDate = $this->request->getGetPost('endDate');
        $skey = $this->request->getGetPost('skey');
        $builder = $this->db->table('goods_file AS a');
        $builder->where('isDelete', 0);
        if ($type) {
            $builder->where('type', $type);
        }
        if ($beginDate) {
            $builder->where('createTime >=', $beginDate);
        }
        if ($endDate) {
            $builder->where('createTime <=', $endDate);
        }
        if ($skey !== '' && $skey !== null) {
            $keywords = array_values(array_filter(explode(',', $skey), 'strlen'));
            foreach ($keywords as $kw) {
                $builder->groupStart()
                    ->like('no', $this->db->escapeLikeString($kw))
                    ->orLike('name', $this->db->escapeLikeString($kw))
                    ->orLike('uName', $this->db->escapeLikeString($kw))
                ->groupEnd();
            }
        }
        $allowedSort = ['id','no','type','name','printSize','authorName','proofName','uName','createTime','modifyName','modifyTime','dTimes'];
        $orderField  = $sidx ? (in_array($sidx, $allowedSort, true) ? $sidx : 'id') : 'id';
        // 1. 子查询 getCompiledSelect() 只编译不执行
        $subQuery = $this->db->table('goods_file_req')
            ->select('iid, (
                    CASE
                    WHEN endTime >= "' . date('Y-m-d H:i:s') . '" AND checkId > 0 AND description = "" THEN 0
                    WHEN endTime = "0000-00-00 00:00:00" AND checkId = 0 AND description = "" THEN 2
                    WHEN endTime = "0000-00-00 00:00:00" AND checkId = 0 AND description != "" THEN 3
                    WHEN endTime < "' . date('Y-m-d H:i:s') . '" AND checkId > 0 AND description = "" THEN 4
                    ELSE 1
                    END
                ) AS download, description AS refuseReason', false)
            ->where('uId', $this->myerp['uId'])
            ->getCompiledSelect();
        $list = (clone $builder)->select('a.*, b.iid, b.download, b.refuseReason')
            ->join("({$subQuery}) AS b", 'a.id = b.iid', 'left')
            ->orderBy($orderField, $sord)
            ->limit($rows)
            ->offset($rows * ($page - 1))
            ->get()
            ->getResultArray();
        $total = $builder->countAllResults();
        $v = [];
        $hasDownloadRight = $this->myservice->verifyRight(strtoupper(explode('_', $type)[0]) . '_DOWNLOAD');
        foreach ($list as $arr => $row) {
            $v[$arr]['id'] = intval($row['id']);
            $v[$arr]['no'] = $row['no'];
            $v[$arr]['type'] = $row['type'];
            $v[$arr]['name'] = $row['name'];
            $v[$arr]['url'] = $row['url'];
            $v[$arr]['authorName'] = $row['authorName'];
            $v[$arr]['proofName'] = $row['proofName'];
            $v[$arr]['printSize'] = $row['printSize'];
            $v[$arr]['dTimes'] = intval($row['dTimes']);
            $v[$arr]['uId'] = intval($row['uId']);
            $v[$arr]['uName'] = $row['uName'];
            $v[$arr]['createTime'] = $row['createTime'];
            $v[$arr]['modifyId'] = intval($row['modifyId']);
            $v[$arr]['modifyName'] = $row['modifyName'];
            $v[$arr]['modifyTime'] = $row['modifyTime'];
            $v[$arr]['checkId'] = intval($row['checkId']);
            $v[$arr]['checkName'] = $row['checkName'];
            $v[$arr]['checkTime'] = $row['checkTime'];
            $v[$arr]['refuseReason'] = $row['refuseReason'];
            if ($hasDownloadRight) {
                $v[$arr]['download'] = 0;
            } else {
                $v[$arr]['download'] = (NULL == $row['download'] ? 1 : intval($row['download']));
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
    // 产品文件 获取信息
    public function fileQuery()
    {
        $id = intval($this->request->getGetPost('id'));
        $data = $this->db->table('goods_file')->where(['id' => $id, 'isDelete' => 0])->get()->getRowArray();
        if ($data) {
            if (!$this->myservice->verifyRight(strtoupper(explode('_', $data['type'])[0]) . '_QUERY')) {
                log_message('error', $this->myerp['uName'] . ' 访问 /Base/Goods/fileQuery 权限不足！');
                return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
            }
            $info['id'] = $id;
            $info['no'] = $data['no'];
            $info['type'] = $data['type'];
            $info['name'] = $data['name'];
            $info['url'] = $data['url'];
            $info['authorName'] = $data['authorName'];
            $info['proofName'] = $data['proofName'];
            $info['printSize'] = $data['printSize'];

            $json['status'] = 200;
            $json['msg'] = 'success';
            $json['data'] = $info;
            return $this->response->setJSON($json);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
    // 产品文件 新增
    public function fileAdd()
    {
        $data = $this->request->getPost();
        if (is_array($data) && count($data) > 0) {
            $data = $this->fileValidform($data);
            // 如果返回的是 Response 对象，直接返回它
            if ($data instanceof \CodeIgniter\HTTP\Response) {
                return $data;
            }
            if (!$this->myservice->verifyRight(strtoupper(explode('_', $data['type'])[0]) . '_ADD')) {
                log_message('error', $this->myerp['uName'] . ' 访问 /Base/Goods/fileAdd 权限不足！');
                return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
            }
            $info = array_intersect_key($data, array_flip(['no', 'type', 'name', 'url', 'authorName', 'proofName', 'printSize', 'uId', 'uName', 'createTime']));
            $this->db->transStart();
            $sql = $this->db->table('goods_file')->insert($info);
            $data['id'] = $this->db->insertID();
            $typeArr = [
                'goodsFileOne' => '产品文件_中文图纸',
                'goodsFileTwo' => '产品文件_中文教程',
                'goodsFileOne_EN' => '产品文件_外文图纸',
                'goodsFileTwo_EN' => '产品文件_外文教程',
                'goodsFileThree' => '产品文件_程序文件',
                'goodsFileFour' => '产品文件_PCB文件',
                'goodsFileFive' => '产品文件_激光文件',
                'goodsFileSix' => '产品文件_设计文件'
            ];
            $this->myservice->log('新增了' . $typeArr[$data['type']] . '，名称：' . $data['name']);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '新增成功！', 'data' => $data]);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '提交的是空数据！']);
    }
    // 产品文件 编辑
    public function fileUpdate()
    {
        $data = $this->request->getPost();
        if (is_array($data) && count($data) > 0) {
            $data = $this->fileValidform($data);
            // 如果返回的是 Response 对象，直接返回它
            if ($data instanceof \CodeIgniter\HTTP\Response) {
                return $data;
            }
            if (!$this->myservice->verifyRight(strtoupper(explode('_', $data['type'])[0]) . '_UPDATE')) {
                log_message('error', $this->myerp['uName'] . ' 访问 /Base/Goods/fileUpdate 权限不足！');
                return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
            }
            $info = array_intersect_key($data, array_flip(['no', 'name', 'url', 'authorName', 'proofName', 'printSize', 'modifyId', 'modifyName', 'modifyTime']));
            $this->db->transStart();
            $this->db->table('goods_file')->where(['id' => $data['id']])->update($info);
            $typeArr = [
                'goodsFileOne' => '产品文件_中文图纸',
                'goodsFileTwo' => '产品文件_中文教程',
                'goodsFileOne_EN' => '产品文件_外文图纸',
                'goodsFileTwo_EN' => '产品文件_外文教程',
                'goodsFileThree' => '产品文件_程序文件',
                'goodsFileFour' => '产品文件_PCB文件',
                'goodsFileFive' => '产品文件_激光文件',
                'goodsFileSix' => '产品文件_设计文件'
            ];
            $this->myservice->log('编辑了' . $typeArr[$data['type']] . '，名称：' . $data['name']);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '编辑成功！', 'data' => $data]);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '提交的是空数据！']);
    }
    // 产品文件 删除
    public function fileDelete()
    {
        $id = intval($this->request->getGetPost('id'));
        $data = $this->db->table('goods_file')->where('id', $id)->get()->getRowArray();
        if ($data) {
            if (!$this->myservice->verifyRight(strtoupper(explode('_', $data['type'])[0]) . '_DELETE')) {
                log_message('error', $this->myerp['uName'] . ' 访问 /Base/Goods/fileDelete 权限不足！');
                return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
            }
            $this->db->transStart();
            $this->db->table('goods_file')->where('id', $id)->update(['isDelete' => 1]);
            $typeArr = [
                'goodsFileOne' => '产品文件_中文图纸',
                'goodsFileTwo' => '产品文件_中文教程',
                'goodsFileOne_EN' => '产品文件_外文图纸',
                'goodsFileTwo_EN' => '产品文件_外文教程',
                'goodsFileThree' => '产品文件_程序文件',
                'goodsFileFour' => '产品文件_PCB文件',
                'goodsFileFive' => '产品文件_激光文件',
                'goodsFileSix' => '产品文件_设计文件'
            ];
            $this->myservice->log('删除了' . $typeArr[$data['type']] . '，名称：' . $data['name']);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '删除成功！']);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
    // 产品文件 审核
    public function fileCheck()
    {
        $id = intval($this->request->getGetPost('id'));
        $checkId = intval($this->request->getGetPost('checkId'));
        $checkName = $this->request->getGetPost('checkName');
        $checkTime = $checkId > 0 ? date('Y-m-d H:i:s') : '0000-00-00 00:00:00';
        $data = $this->db->table('goods_file')->where('id', $id)->get()->getRowArray();
        if ($data) {
            if (!$this->myservice->verifyRight(strtoupper(explode('_', $data['type'])[0]) . '_CHECK')) {
                log_message('error', $this->myerp['uName'] . ' 访问 /Base/Goods/fileCheck 权限不足！');
                return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
            }
            $this->db->transStart();
            $this->db->table('goods_file')->where('id', $id)->update(['checkId' => $checkId, 'checkName' => $checkName, 'checkTime' => $checkTime]);
            $this->myservice->log(($checkId == 0 ? '反审了' : '审核了') . '产品文件：' . $data['name']);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '操作成功！']);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
    // 产品文件 下载
    public function fileDownload()
    {
        $id = intval($this->request->getGetPost('id'));
        $step = intval($this->request->getGetPost('step'));
        $data = $this->db->table('goods_file')->where('id', $id)->get()->getRowArray();
        if ($data) {
            // 权限验证
            switch ($data['type']) {
                case 'goodsFileOne':
                    if (!$this->myservice->verifyRight('GOODSFILEONE_DOWNLOAD')) {
                        if (!$this->myservice->verifyRight('GOODSFILEONE_DOWNLOAD_REQ')) {
                            log_message('error', $this->myerp['uName'] . ' 访问 /Base/Goods/fileDownload goodsFileOne 权限不足！');
                            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
                        } else {
                            if ($this->db->table('goods_file_req')->where(['iid' => $id, 'uId' => $this->myerp['uId'], 'endTime > ' => date('Y-m-d H:i:s')])->countAllResults() < 1) {
                                return $this->response->setJSON(['status' => -1, 'msg' => '对不起！未申请下载或下载有效期已过！']);
                            }
                        }
                    }
                    break;
                case 'goodsFileTwo':
                    if (!$this->myservice->verifyRight('GOODSFILETWO_DOWNLOAD')) {
                        if (!$this->myservice->verifyRight('GOODSFILETWO_DOWNLOAD_REQ')) {
                            log_message('error', $this->myerp['uName'] . ' 访问 /Base/Goods/fileDownload goodsFileTwo 权限不足！');
                            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
                        } else {
                            if ($this->db->table('goods_file_req')->where(['iid' => $id, 'uId' => $this->myerp['uId'], 'endTime > ' => date('Y-m-d H:i:s')])->countAllResults() < 1) {
                                return $this->response->setJSON(['status' => -1, 'msg' => '对不起！未申请下载或下载有效期已过！']);
                            }
                        }
                    }
                    break;
                case 'goodsFileOne_EN':
                    if (!$this->myservice->verifyRight('GOODSFILEONE_DOWNLOAD')) {
                        if (!$this->myservice->verifyRight('GOODSFILEONE_DOWNLOAD_REQ')) {
                            log_message('error', $this->myerp['uName'] . ' 访问 /Base/Goods/fileDownload goodsFileOne_EN 权限不足！');
                            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
                        } else {
                            if ($this->db->table('goods_file_req')->where(['iid' => $id, 'uId' => $this->myerp['uId'], 'endTime > ' => date('Y-m-d H:i:s')])->countAllResults() < 1) {
                                return $this->response->setJSON(['status' => -1, 'msg' => '对不起！未申请下载或下载有效期已过！']);
                            }
                        }
                    }
                    break;
                case 'goodsFileTwo_EN':
                    if (!$this->myservice->verifyRight('GOODSFILETWO_DOWNLOAD')) {
                        if (!$this->myservice->verifyRight('GOODSFILETWO_DOWNLOAD_REQ')) {
                            log_message('error', $this->myerp['uName'] . ' 访问 /Base/Goods/fileDownload goodsFileTwo_EN 权限不足！');
                            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
                        } else {
                            if ($this->db->table('goods_file_req')->where(['iid' => $id, 'uId' => $this->myerp['uId'], 'endTime > ' => date('Y-m-d H:i:s')])->countAllResults() < 1) {
                                return $this->response->setJSON(['status' => -1, 'msg' => '对不起！未申请下载或下载有效期已过！']);
                            }
                        }
                    }
                    break;
                case 'goodsFileThree':
                    if (!$this->myservice->verifyRight('GOODSFILETHREE_DOWNLOAD')) {
                        if (!$this->myservice->verifyRight('GOODSFILETHREE_DOWNLOAD_REQ')) {
                            log_message('error', $this->myerp['uName'] . ' 访问 /Base/Goods/fileDownload goodsFileThree 权限不足！');
                            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
                        } else {
                            if ($this->db->table('goods_file_req')->where(['iid' => $id, 'uId' => $this->myerp['uId'], 'endTime > ' => date('Y-m-d H:i:s')])->countAllResults() < 1) {
                                return $this->response->setJSON(['status' => -1, 'msg' => '对不起！未申请下载或下载有效期已过！']);
                            }
                        }
                    }
                    break;
                case 'goodsFileFour':
                    if (!$this->myservice->verifyRight('GOODSFILEFOUR_DOWNLOAD')) {
                        if (!$this->myservice->verifyRight('GOODSFILEFOUR_DOWNLOAD_REQ')) {
                            log_message('error', $this->myerp['uName'] . ' 访问 /Base/Goods/fileDownload goodsFileFour 权限不足！');
                            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
                        } else {
                            if ($this->db->table('goods_file_req')->where(['iid' => $id, 'uId' => $this->myerp['uId'], 'endTime > ' => date('Y-m-d H:i:s')])->countAllResults() < 1) {
                                return $this->response->setJSON(['status' => -1, 'msg' => '对不起！未申请下载或下载有效期已过！']);
                            }
                        }
                    }
                    break;
                case 'goodsFileFive':
                    if (!$this->myservice->verifyRight('GOODSFILEFIVE_DOWNLOAD')) {
                        if (!$this->myservice->verifyRight('GOODSFILEFIVE_DOWNLOAD_REQ')) {
                            log_message('error', $this->myerp['uName'] . ' 访问 /Base/Goods/fileDownload goodsFileFive 权限不足！');
                            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
                        } else {
                            if ($this->db->table('goods_file_req')->where(['iid' => $id, 'uId' => $this->myerp['uId'], 'endTime > ' => date('Y-m-d H:i:s')])->countAllResults() < 1) {
                                return $this->response->setJSON(['status' => -1, 'msg' => '对不起！未申请下载或下载有效期已过！']);
                            }
                        }
                    }
                    break;
                case 'goodsFileSix':
                    if (!$this->myservice->verifyRight('GOODSFILESIX_DOWNLOAD')) {
                        if (!$this->myservice->verifyRight('GOODSFILESIX_DOWNLOAD_REQ')) {
                            log_message('error', $this->myerp['uName'] . ' 访问 /Base/Goods/fileDownload goodsFileSix 权限不足！');
                            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
                        } else {
                            if ($this->db->table('goods_file_req')->where(['iid' => $id, 'uId' => $this->myerp['uId'], 'endTime > ' => date('Y-m-d H:i:s')])->countAllResults() < 1) {
                                return $this->response->setJSON(['status' => -1, 'msg' => '对不起！未申请下载或下载有效期已过！']);
                            }
                        }
                    }
                    break;
                default:
                    break;
            }
            // 文件路径
            $filePath = FCPATH . 'data\\upfile\\goods\\file\\' . $data['url'];
            // 确保文件存在
            if (!file_exists($filePath)) {
                return $this->response->setJSON(['status' => -1, 'msg' => '文件不存在！']);
            }
        } else {
            return $this->response->setJSON(['status' => -1, 'msg' => '文件不存在！']);
        }
        if ($step !== 2) {
            return $this->response->setJSON(['status' => 200, 'msg' => '检测步骤通过！']);
        } else {
            // 文件路径
            $filePath = FCPATH . 'data\\upfile\\goods\\file\\' . $data['url'];
            // 获取文件大小
            $fileSize = filesize($filePath);
            // 设置HTTP头部
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . $data['name'] . '"');
            header('Content-Transfer-Encoding: binary');
            header('Content-Length: ' . $fileSize);
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Pragma: public');
            ini_set('zlib.output_compression', 'off');
            readfile($filePath);
            $typeArr = [
                'goodsFileOne' => '产品文件_中文图纸',
                'goodsFileTwo' => '产品文件_中文教程',
                'goodsFileOne_EN' => '产品文件_外文图纸',
                'goodsFileTwo_EN' => '产品文件_外文教程',
                'goodsFileThree' => '产品文件_程序文件',
                'goodsFileFour' => '产品文件_PCB文件',
                'goodsFileFive' => '产品文件_激光文件',
                'goodsFileSix' => '产品文件_设计文件'
            ];
            $this->db->table('goods_file')->set('dTimes', 'dTimes + 1', false)->where('id', $id)->update();
            $this->myservice->log('下载了' . $typeArr[$data['type']] . '，名称：' . $data['name']);
            exit;
        }
    }
    // 产品文件 申请下载
    public function fileReq()
    {
        $id = intval($this->request->getGetPost('id'));
        $data = $this->db->table('goods_file')->where(['id' => $id])->get()->getRowArray();
        if ($data) {
            // 判断权限
            if (
                ($data['type'] == 'goodsFileOne' && !$this->myservice->verifyRight('GOODSFILEONE_DOWNLOAD_REQ')) ||
                ($data['type'] == 'goodsFileTwo' && !$this->myservice->verifyRight('GOODSFILETWO_DOWNLOAD_REQ')) ||
                ($data['type'] == 'goodsFileOne_EN' && !$this->myservice->verifyRight('GOODSFILEONE_DOWNLOAD_REQ')) ||
                ($data['type'] == 'goodsFileTwo_EN' && !$this->myservice->verifyRight('GOODSFILETWO_DOWNLOAD_REQ')) ||
                ($data['type'] == 'goodsFileThree' && !$this->myservice->verifyRight('GOODSFILETHREE_DOWNLOAD_REQ')) ||
                ($data['type'] == 'goodsFileFour' && !$this->myservice->verifyRight('GOODSFILEFOUR_DOWNLOAD_REQ')) ||
                ($data['type'] == 'goodsFileFive' && !$this->myservice->verifyRight('GOODSFILEFIVE_DOWNLOAD_REQ')) ||
                ($data['type'] == 'goodsFileSix' && !$this->myservice->verifyRight('GOODSFILESIX_DOWNLOAD_REQ'))
            ) {
                log_message('error', $this->myerp['uName'] . ' 访问 /Base/Goods/fileReq ' . $data['type'] . ' 权限不足！');
                return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
            }
            $reqData = $this->db->table('goods_file_req')->where(['iid' => $id, 'uId' => $this->myerp['uId']])->get()->getRowArray();
            $this->db->transStart();
            if ($reqData) {
                $this->db->table('goods_file_req')->where(['iid' => $id, 'uId' => $this->myerp['uId']])->update([
                    'reqTime' => date('Y-m-d H:i:s'),
                    'endTime' => '0000-00-00 00:00:00',
                    'checkId' => 0,
                    'checkName' => '',
                    'checkTime' => '0000-00-00 00:00:00',
                    'description' => ''
                ]);
            } else {
                $this->db->table('goods_file_req')->insert([
                    'iid' => $id,
                    'no' => $data['no'],
                    'type' => $data['type'],
                    'name' => $data['name'],
                    'uId' => $this->myerp['uId'],
                    'uName' => $this->myerp['uName'],
                    'reqTime' => date('Y-m-d H:i:s'),
                    'endTime' => '0000-00-00 00:00:00',
                    'checkId' => 0,
                    'checkName' => '',
                    'checkTime' => '0000-00-00 00:00:00'
                ]);
            }
            $typeArr = [
                'goodsFileOne' => '产品文件_中文图纸',
                'goodsFileTwo' => '产品文件_中文教程',
                'goodsFileOne_EN' => '产品文件_外文图纸',
                'goodsFileTwo_EN' => '产品文件_外文教程',
                'goodsFileThree' => '产品文件_程序文件',
                'goodsFileFour' => '产品文件_PCB文件',
                'goodsFileFive' => '产品文件_激光文件',
                'goodsFileSix' => '产品文件_设计文件'
            ];
            $this->myservice->log('申请了' . $typeArr[$data['type']] . '下载，名称：' . $data['name']);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '申请成功！']);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
    // 产品文件 数据验证
    private function fileValidform(array $data)
    {
        $data['id'] = isset($data['id']) ? intval($data['id']) : -1;
        if ($this->db->table('goods')->where(['no' => $data['no'], 'isDelete' => 0])->countAllResults() < 1) {
            return $this->response->setJSON(['status' => -1, 'msg' => '对应产品编码不存在！']);
        }
        if (strlen($data['name']) < 1 || strlen($data['url']) < 1) {
            return $this->response->setJSON(['status' => -1, 'msg' => '上传文件不能为空！']);
        }
        if ($this->db->table('goods_file')->where(['no' => $data['no'], 'name' => $data['name'], 'isDelete' => 0, 'id !=' => $data['id']])->countAllResults() > 0) {
            return $this->response->setJSON(['status' => -1, 'msg' => '已上传过同名文件！']);
        }
        if (in_array($data['type'], ['goodsFileOne', 'goodsFileTwo', 'goodsFileOne_EN', 'goodsFileTwo_EN'])) {
            if (strlen($data['authorName']) < 1) {
                return $this->response->setJSON(['status' => -1, 'msg' => '编写人不能为空！']);
            }
            if (strlen($data['proofName']) < 1) {
                return $this->response->setJSON(['status' => -1, 'msg' => '校对人不能为空！']);
            }
        }
        $data['uId'] = $data['modifyId'] = $this->myerp['uId'];
        $data['uName'] = $data['modifyName'] = $this->myerp['uName'];
        $data['createTime'] = $data['modifyTime'] = date('Y-m-d H:i:s');
        return $data;
    }
    // 产品文件 申请下载 列表
    public function fileReqList()
    {
        if (!$this->myservice->verifyRight('GOODSFILEREQ_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/Goods/fileReqList 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $page = max(intval($this->request->getGetPost('page')), 1);
        $rows = max(intval($this->request->getGetPost('rows')), 100);
        $sidx = $this->request->getGetPost('sidx');
        $sord = strtoupper((string)$this->request->getGetPost('sord')) === 'ASC' ? 'ASC' : 'DESC';
        $status1 = $this->request->getGetPost('status1');
        $status2 = intval($this->request->getGetPost('status2'));
        $skey = $this->request->getGetPost('skey');
        $beginDate = $this->request->getGetPost('beginDate');
        $endDate = $this->request->getGetPost('endDate');
        $builder = $this->db->table('goods_file_req')->where('isDelete', 0);
        if ($status1) {
            $builder->where('uName', $status1);
        }
        if ($status2 == 1) {
            $builder->where('checkId', 0);
        } elseif ($status2 == 2) {
            $builder->where('description !=', '');
        }
        if ($beginDate) {
            $builder->where('reqTime >=', $beginDate);
        }
        if ($endDate) {
            $builder->where('reqTime <=', $endDate);
        }
        if ($skey !== '' && $skey !== null) {
            $keywords = array_values(array_filter(explode(',', $skey), 'strlen'));
            foreach ($keywords as $kw) {
                $builder->groupStart()
                    ->like('no', $this->db->escapeLikeString($kw))
                    ->orLike('name', $this->db->escapeLikeString($kw))
                    ->orLike('uName', $this->db->escapeLikeString($kw))
                ->groupEnd();
            }
        }
        $allowedSort = ['id','iid','no','type','name','uName','reqTime','checkName','checkTime','endTime','description'];
        $orderField  = $sidx ? (in_array($sidx, $allowedSort, true) ? $sidx : 'id') : 'id';
        $list = (clone $builder)->orderBy($orderField, $sord)->limit($rows)->offset($rows * ($page - 1))->get()->getResultArray();
        $total = $builder->countAllResults();
        $v = [];
        foreach ($list as $arr => $row) {
            $v[$arr]['id'] = intval($row['id']);
            $v[$arr]['iid'] = intval($row['iid']);
            $v[$arr]['no'] = $row['no'];
            $v[$arr]['type'] = $row['type'];
            $v[$arr]['name'] = $row['name'];
            $v[$arr]['uId'] = intval($row['uId']);
            $v[$arr]['uName'] = $row['uName'];
            $v[$arr]['reqTime'] = $row['reqTime'];
            $v[$arr]['checkId'] = intval($row['checkId']);
            $v[$arr]['checkName'] = $row['checkName'];
            $v[$arr]['checkTime'] = $row['checkTime'];
            $v[$arr]['endTime'] = $row['endTime'];
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
    // 产品文件 申请下载 获取信息
    public function fileReqQuery()
    {
        if (!$this->myservice->verifyRight('GOODSFILEREQ_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/Goods/fileReqQuery 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $data = $this->db->table('goods_file_req')->getWhere(['id' => $id, 'isDelete' => 0])->getRowArray();
        if ($data) {
            $info['id'] = $id;
            $info['no'] = $data['no'];
            $info['type'] = $data['type'];
            $info['name'] = $data['name'];
            $info['uName'] = $data['uName'];
            $info['reqTime'] = $data['reqTime'];
            $info['description'] = $data['description'];

            $json['status'] = 200;
            $json['msg'] = 'success';
            $json['data'] = $info;
            return $this->response->setJSON($json);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
    // 产品文件 申请下载 拒绝
    public function fileReqRefuse()
    {
        if (!$this->myservice->verifyRight('GOODSFILEREQ_UPDATE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/Goods/fileReqRefuse 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $data = $this->request->getPost();
        if (is_array($data) && count($data) > 0) {
            $info = array_intersect_key($data, array_flip(['description']));
            $this->db->transStart();
            $this->db->table('goods_file_req')->where('id', $data['id'])->update($info);
            $typeArr = [
                'goodsFileOne' => '产品文件_中文图纸',
                'goodsFileTwo' => '产品文件_中文教程',
                'goodsFileOne_EN' => '产品文件_外文图纸',
                'goodsFileTwo_EN' => '产品文件_外文教程',
                'goodsFileThree' => '产品文件_程序文件',
                'goodsFileFour' => '产品文件_PCB文件',
                'goodsFileFive' => '产品文件_激光文件',
                'goodsFileSix' => '产品文件_设计文件'
            ];
            $this->myservice->log('拒绝了' . $data['uName'] . '发起的' . $typeArr[$data['type']] . '，名称：' . $data['name']);
            $this->db->transComplete();
            if ($this->db->transStatus() === FALSE) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '拒绝成功！']);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '提交的是空数据！']);
    }
    // 产品文件 申请下载 审核与反审核
    public function fileReqCheck()
    {
        if (!$this->myservice->verifyRight('GOODSFILEREQ_UPDATE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/Goods/fileReqCheck 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $checkId = intval($this->request->getGetPost('checkId'));
        $checkName = $this->request->getGetPost('checkName');
        $checkTime = $checkId > 0 ? date('Y-m-d H:i:s') : '0000-00-00 00:00:00';
        $endTime = $checkId > 0 ? date('Y-m-d H:i:s', strtotime('+1 day')) : '0000-00-00 00:00:00';
        $data = $this->db->table('goods_file_req')->where(['id' => $id, 'isDelete' => 0])->get()->getRowArray();
        if ($data) {
            $data['checkId'] = $checkId;
            $data['checkName'] = $checkName;
            $data['checkTime'] = $checkTime;
            $data['endTime'] = $endTime;
            $this->db->transStart();
            $this->db->table('goods_file_req')->where('id', $id)->update($data);
            $this->myservice->log(($checkId > 0 ? '审核了 ' : '反审核了 ') . $data['uName'] . ' 的产品文件下载申请。文件ID：' . $data['iid'] . '，文件名称：' . $data['name']);
            $this->db->transComplete();
            if ($this->db->transStatus() === FALSE) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => $checkId > 0 ? '审核成功！' : '反审核成功！']);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
    // 产品文件 申请下载 删除
    public function fileReqDelete()
    {
        if (!$this->myservice->verifyRight('GOODSFILEREQ_DELETE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/Goods/fileReqDelete 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $data = $this->db->table('goods_file_req')->where('id', $id)->get()->getRowArray();
        if ($data) {
            if ($data['uId'] != $this->myerp['uId']) {
                return $this->response->setJSON(['status' => -1, 'msg' => '不能删除别人的下载申请！']);
            }
            $this->db->transStart();
            $this->db->table('goods_file_req')->where('id', $id)->update(['isDelete' => 1]);
            $typeArr = [
                'goodsFileOne' => '产品文件_中文图纸',
                'goodsFileTwo' => '产品文件_中文教程',
                'goodsFileOne_EN' => '产品文件_外文图纸',
                'goodsFileTwo_EN' => '产品文件_外文教程',
                'goodsFileThree' => '产品文件_程序文件',
                'goodsFileFour' => '产品文件_PCB文件',
                'goodsFileFive' => '产品文件_激光文件',
                'goodsFileSix' => '产品文件_设计文件'
            ];
            $this->myservice->log('删除了' . $typeArr[$data['type']] . '下载申请，名称：' . $data['name']);
            $this->db->transComplete();
            if ($this->db->transStatus() === FALSE) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '删除成功！']);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }

    // 库存查询 (库存盘点列表复用此方法)
    public function listInventoryQty()
    {
        if (!$this->myservice->verifyRight('INV_SEARCH')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/Goods/listInventoryQty 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $page = max(intval($this->request->getGetPost('page')), 1);
        $rows = max(intval($this->request->getGetPost('rows')), 100);
        $sidx = $this->request->getGetPost('sidx');
        $sord = strtoupper((string)$this->request->getGetPost('sord')) === 'ASC' ? 'ASC' : 'DESC';
        $storageId = intval($this->request->getGetPost('storageId'));
        $categoryId = intval($this->request->getGetPost('categoryId'));
        $skey = $this->request->getGetPost('skey');
        $builder = $this->db->table('goods')->where('isDelete', 0);
        if ($storageId > 0) {
            $builder->where('storageId', $storageId);
        }
        if ($categoryId > 0) {
<<<<<<< HEAD
            $cid = array_column($this->db->table('category')->select('id')->where('isDelete', 0)->where('FIND_IN_SET(' . $categoryId . ', path) >', 0, false)->get()->getResultArray(), 'id');
=======
            $cid = array_column($this->db->table('category')->select('id')->where('isDelete', 0)->where('FIND_IN_SET('.$this->db->escape($categoryId).', path) >', 0, false)->get()->getResultArray(), 'id');
>>>>>>> 151b415 (更新语言最新版本+最新底层框架后全部差异化代码适配重写)
            if (count($cid) > 0) {
                $builder->whereIn('categoryId', $cid);
            }
        }
        if ($skey !== '' && $skey !== null) {
            $keywords = array_values(array_filter(explode(',', $skey), 'strlen'));
            foreach ($keywords as $kw) {
                $builder->groupStart()
                    ->like('id', $this->db->escapeLikeString($kw))
                    ->orLike('barCode', $this->db->escapeLikeString($kw))
                    ->orLike('no', $this->db->escapeLikeString($kw))
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
        $allowedSort = ['id','barCode','no','name','spec','sku','brandName','categoryName','unitName','storageName','highQty','lowQty','purPrice','currentQty','changeQty','checkName','checkTime','isDisable'];
        $orderField  = $sidx ? (in_array($sidx, $allowedSort, true) ? $sidx : 'id') : 'id';
        $list = (clone $builder)->orderBy($orderField, $sord)->limit($rows)->offset($rows * ($page - 1))->get()->getResultArray();
        $currentQty_total = (clone $builder)->select('SUM(currentQty) AS currentQty_total')->get()->getRowArray()['currentQty_total'] ?? 0;
        $v = [];
        foreach ($list as $arr => $row) {
            $v[$arr]['id'] = intval($row['id']);
            $v[$arr]['barCode'] = $row['barCode'];
            $v[$arr]['no'] = $row['no'];
            $v[$arr]['name'] = $row['name'];
            $v[$arr]['spec'] = $row['spec'];
            $v[$arr]['sku'] = $row['sku'];
            $v[$arr]['brandName'] = $row['brandName'];
            $v[$arr]['categoryId'] = intval($row['categoryId']);
            $v[$arr]['categoryName'] = $row['categoryName'];
            $v[$arr]['unitName'] = $row['unitName'];
            $v[$arr]['storageId'] = intval($row['storageId']);
            $v[$arr]['storageName'] = $row['storageName'];
            $v[$arr]['highQty'] = intval($row['highQty']);
            $v[$arr]['lowQty'] = intval($row['lowQty']);
            $v[$arr]['purPrice'] = (float)$row['purPrice'];
            $v[$arr]['currentQty'] = (float)$row['currentQty'];
            $v[$arr]['checkQty'] = 0 == (float)$row['checkQty'] ? '' : (float)$row['checkQty'];
            $v[$arr]['changeQty'] = 0 == (float)$row['changeQty'] ? '' : (float)$row['changeQty'];
            $v[$arr]['checkName'] = $row['checkName'];
            $v[$arr]['checkTime'] = '0000-00-00 00:00:00' == $row['checkTime'] ? '' : $row['checkTime'];
            $v[$arr]['isDisable'] = intval($row['isDisable']);
        }
        $total = $builder->countAllResults();
        $json['status'] = 200;
        $json['msg'] = 'success';
        $json['data']['page'] = $page;
        $json['data']['records'] = $total;
        $json['data']['total'] = ceil($json['data']['records'] / $rows);
        $json['data']['rows'] = $v;
        $json['data']['currentQty_total'] = intval($currentQty_total);
        return $this->response->setJSON($json);
    }
    // 库存流水
    public function listInventoryHistory()
    {
        if (!$this->myservice->verifyRight('INV_HISTORY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/Goods/listInventoryHistory 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $page = max(intval($this->request->getGetPost('page')), 1);
        $rows = max(intval($this->request->getGetPost('rows')), 100);
        $sidx = $this->request->getGetPost('sidx');
        $sord = strtoupper((string)$this->request->getGetPost('sord')) === 'ASC' ? 'ASC' : 'DESC';
        $status1 = intval($this->request->getGetPost('status1'));
        $goodsId = intval($this->request->getGetPost('goodsId'));
        $beginDate = $this->request->getGetPost('beginDate');
        $endDate = $this->request->getGetPost('endDate');
        $type = intval($this->request->getGetPost('type'));
        $builder = $this->db->table('inv_history AS a');
        $builder->where('goodsId', $goodsId);
        if ($beginDate) {
            $builder->where('changeTime >=', $beginDate . ' 00:00:00');
        }
        if ($endDate) {
            $builder->where('changeTime <=', $endDate . ' 23:59:59');
        }
        if (0 <= $status1) {
            if (0 == $status1) {
                $builder->where('changeCurrentQty >', 0);
            } else {
                $builder->where('changeCurrentQty <', 0);
            }
        }
        if ($type == 1) {
            $builder->where('changeTime >=', date('Y-m-d', strtotime(date('Y-m-d') . " -12 month")) . ' 00:00:00');
            $builder->groupStart()
                ->whereIn('billType', ['SA', 'SC'])
                ->orGroupStart()
                    ->where(['billType' => 'PK', 'transType' => 102])
                ->groupEnd()
                ->orGroupStart()
                    ->where(['billType' => 'PP', 'transType' => 102])
                ->groupEnd()
            ->groupEnd();
        }
        $allowedSort = ['id','name','company','description','isDisable'];
        $orderField  = $sidx ? (in_array($sidx, $allowedSort, true) ? $sidx : 'id') : 'id';
        $subQuery = $this->db->table('goods')
        ->select('id, no, name, spec, sku, brandName, unitName, categoryName')
        ->getCompiledSelect();
        $list = (clone $builder)->select('a.*, b.no AS goodsNo, b.name AS goodsName, b.spec AS goodsSpec, b.sku AS goodsSku, b.brandName AS goodsBrandName, b.unitName AS goodsUnitName, b.categoryName AS goodsCategoryName')
        ->join("($subQuery) AS b", 'a.goodsId = b.id', 'left')
        ->orderBy($orderField, $sord)
        ->limit($rows)
        ->offset($rows * ($page - 1))
        ->get()
        ->getResultArray();
        $totalChangeCurrentQty = (clone $builder)->select('SUM(changeCurrentQty) AS totalChangeCurrentQty')->get()->getRowArray()['totalChangeCurrentQty'] ?? 0;
        $total = $builder->countAllResults();
        $json['status'] = 200;
        $json['msg'] = 'success';
        $json['data']['page'] = $page;
        $json['data']['records'] = $total;
        $json['data']['total'] = ceil($json['data']['records'] / $rows);
        $json['data']['rows'] = $list;
        $json['data']['totalChangeCurrentQty'] = $totalChangeCurrentQty;
        return $this->response->setJSON($json);
    }
    // 库存预警
    public function listInventoryQtyWarning()
    {
        if (!$this->myservice->verifyRight('INV_WARNING')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/Goods/listInventoryQtyWarning 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $page = max(intval($this->request->getGetPost('page')), 1);
        $rows = max(intval($this->request->getGetPost('rows')), 100);
        $sidx = $this->request->getGetPost('sidx');
        $sord = strtoupper((string)$this->request->getGetPost('sord')) === 'ASC' ? 'ASC' : 'DESC';
        $categoryId = intval($this->request->getGetPost('categoryId'));
        $storageId = intval($this->request->getGetPost('storageId'));
        $warnType = intval($this->request->getGetPost('warnType'));
        $skey = $this->request->getGetPost('skey');
        $builder = $this->db->table('goods');
        $builder->whereNotIn('categoryId', [263, 435])->where('isDisable <', 2)->where('isDelete', 0);
        if ($categoryId > 0) {
<<<<<<< HEAD
            $cid = array_column($this->db->table('category')->select('id')->where('isDelete', 0)->where('FIND_IN_SET(' . $categoryId . ', path) >', 0, false)->get()->getResultArray(), 'id');
=======
            $cid = array_column($this->db->table('category')->select('id')->where('isDelete', 0)->where('FIND_IN_SET(' . $this->db->escape($categoryId) . ', path) >', 0, false)->get()->getResultArray(), 'id');
>>>>>>> 151b415 (更新语言最新版本+最新底层框架后全部差异化代码适配重写)
            if (count($cid) > 0) {
                $builder->whereIn('categoryId', $cid);
            }
        }
        if ($storageId > 0) {
            $builder->where('storageId', $storageId);
        }
        if ($warnType > 0) {
            if ($warnType == 1) {
                $builder->where('currentQty < lowQty', null, false);
            } elseif ($warnType == 2) {
                $builder->where('currentQty > highQty', null, false);
            }
        } else {
            $builder->groupStart()
                ->where('currentQty > highQty', null, false)
                ->orWhere('currentQty < lowQty', null, false)
            ->groupEnd();
        }
        if ($skey !== '' && $skey !== null) {
            $keywords = array_values(array_filter(explode(',', $skey), 'strlen'));
            foreach ($keywords as $kw) {
                $builder->groupStart()
                    ->like('barCode', $this->db->escapeLikeString($kw))
                    ->orLike('no', $this->db->escapeLikeString($kw))
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
        $allowedSort = ['id','isDisable'];
        $orderField  = $sidx ? (in_array($sidx, $allowedSort, true) ? $sidx : 'id') : 'id';
        $list = (clone $builder)->orderBy($orderField, $sord)->limit($rows)->offset($rows * ($page - 1))->get()->getResultArray();
        $total = $builder->countAllResults();
        $v = [];
        foreach ($list as $arr => $row) {
            $v[$arr]['id'] = intval($row['id']);
            $v[$arr]['barCode'] = $row['barCode'];
            $v[$arr]['no'] = $row['no'];
            $v[$arr]['name'] = $row['name'];
            $v[$arr]['spec'] = $row['spec'];
            $v[$arr]['sku'] = $row['sku'];
            $v[$arr]['brandName'] = $row['brandName'];
            $v[$arr]['categoryId'] = intval($row['categoryId']);
            $v[$arr]['categoryName'] = $row['categoryName'];
            $v[$arr]['unitName'] = $row['unitName'];
            $v[$arr]['storageId'] = intval($row['storageId']);
            $v[$arr]['storageName'] = $row['storageName'];
            $v[$arr]['highQty'] = intval($row['highQty']);
            $v[$arr]['lowQty'] = intval($row['lowQty']);
            $v[$arr]['purPrice'] = (float)$row['purPrice'];
            $v[$arr]['currentQty'] = (float)$row['currentQty'];
            $v[$arr]['isDisable'] = intval($row['isDisable']);
            $totalQty = intval($v[$arr]['currentQty']);
            $v[$arr]['warning'] = $totalQty > $v[$arr]['highQty'] ? ($totalQty - $v[$arr]['highQty']) : ($totalQty < $v[$arr]['lowQty'] ? ($totalQty - $v[$arr]['lowQty']) : 0);
        }
        $json['status'] = 200;
        $json['msg'] = 'success';
        $json['data']['page'] = $page;
        $json['data']['records'] = $total;
        $json['data']['total'] = ceil($json['data']['records'] / $rows);
        $json['data']['rows'] = $v;
        return $this->response->setJSON($json);
    }
    // 库存预警 导出
    public function warningExport()
    {
        if (!$this->myservice->verifyRight('INV_WARNING')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/Goods/warningExport 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = $this->request->getGetPost('id');
        $ids = array_filter(array_map('intval', explode(',', (string) $id)));
        $builder = $this->db->table('goods');
        $builder->select('id,barCode,no,name,spec,sku,brandName,unitName,storageName,categoryName,locationName,purPrice,salePrice,lowQty,highQty,currentQty');
        if (!empty($ids)) {
            $builder->whereIn('id', $ids);
        } else {
            $storageId = intval($this->request->getGetPost('storageId'));
            $warnType = intval($this->request->getGetPost('warnType'));
            $categoryId = intval($this->request->getGetPost('categoryId'));
            $skey = $this->request->getGetPost('skey');
            $builder->where('isDelete', 0);
            if ($storageId > 0) {
                $builder->where('storageId', $storageId);
            }
            if ($warnType == 1) {
                $builder->where('currentQty < lowQty', null, false);
            } elseif ($warnType == 2) {
                $builder->where('currentQty > highQty', null, false);
            } else {
                $builder->where('(currentQty > highQty or currentQty < lowQty)', null, false);
            }
            if ($categoryId > 0) {
<<<<<<< HEAD
                $cid = array_column($this->db->table('category')->select('id')->where('isDelete', 0)->where('FIND_IN_SET(' . $categoryId . ', path) >', 0, false)->get()->getResultArray(), 'id');
=======
                $cid = array_column($this->db->table('category')->select('id')->where('isDelete', 0)->where('FIND_IN_SET(' . $this->db->escape($categoryId) . ', path) >', 0, false)->get()->getResultArray(), 'id');
>>>>>>> 151b415 (更新语言最新版本+最新底层框架后全部差异化代码适配重写)
                if (count($cid) > 0) {
                    $builder->whereIn('categoryId', $cid);
                }
            }
            if (!empty($skey)) {
                if ($skey !== '' && $skey !== null) {
                    $keywords = array_values(array_filter(explode(',', $skey), 'strlen'));
                    foreach ($keywords as $kw) {
                        $builder->groupStart()
                            ->like('barCode', $this->db->escapeLikeString($kw))
                            ->orLike('no', $this->db->escapeLikeString($kw))
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
        }
        $list = $builder->orderBy('id', 'DESC')->get()->getResultArray();
        $name = '库存预警产品列表_' . date('YmdHis');

        // 调用spreadsheet处理表格
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $worksheet1 = $spreadsheet->getActiveSheet();
        $worksheet1->setTitle('库存预警列表');

        // A-Z列 设置字符串自动换行(会撑开单元格高度,并破坏设置的默认行高)，设置水平居左+垂直居中
        $worksheet1->getStyle("A:Z")->getAlignment()->setWrapText(TRUE);
        $worksheet1->getStyle('A:Z')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
        $worksheet1->getStyle('A:Z')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

        // 设置边框
        $worksheet1->setShowGridlines(false);
        $styleArray = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => '00000000'],
                ]
            ],
        ];
        $worksheet1->getStyle('A1:O' . (COUNT($list) + 2))->applyFromArray($styleArray, false);

        // 设置加粗
        $worksheet1->getStyle('A1')->getFont()->setBold(true);
        $worksheet1->getStyle('A2:O2')->getFont()->setBold(true);

        // 设置背景色
        $worksheet1->getStyle('A2:O2')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
        $worksheet1->getStyle('A2:O2')->getFill()->getStartColor()->setRGB('F5F5F5');

        // 合并单元格
        $worksheet1->mergeCells('A1:O1');

        // 设置行高
        $worksheet1->getRowDimension('1')->setRowHeight(40);

        // 设置水平居中
        $worksheet1->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // 设置每列列宽
        $worksheet1->getColumnDimension('A')->setWidth(10);
        $worksheet1->getColumnDimension('B')->setWidth(20);
        $worksheet1->getColumnDimension('C')->setWidth(20);
        $worksheet1->getColumnDimension('D')->setWidth(20);
        $worksheet1->getColumnDimension('E')->setWidth(20);
        $worksheet1->getColumnDimension('F')->setWidth(20);
        $worksheet1->getColumnDimension('G')->setWidth(10);
        $worksheet1->getColumnDimension('H')->setWidth(20);
        $worksheet1->getColumnDimension('I')->setWidth(10);
        $worksheet1->getColumnDimension('J')->setWidth(10);
        $worksheet1->getColumnDimension('K')->setWidth(10);
        $worksheet1->getColumnDimension('L')->setWidth(10);
        $worksheet1->getColumnDimension('M')->setWidth(10);
        $worksheet1->getColumnDimension('N')->setWidth(10);
        $worksheet1->getColumnDimension('O')->setWidth(10);

        //设置当前的sheet
        $worksheet1->setCellValue('A1', $name . '（导出时间 ' . date('Y-m-d H:i:s') . '）');
        $worksheet1->setCellValue('A2', 'ID');
        $worksheet1->setCellValue('B2', '产品条码');
        $worksheet1->setCellValue('C2', '产品编码');
        $worksheet1->setCellValue('D2', '产品名称');
        $worksheet1->setCellValue('E2', '规格型号');
        $worksheet1->setCellValue('F2', '辅助属性');
        $worksheet1->setCellValue('G2', '产品品牌');
        $worksheet1->setCellValue('H2', '产品类别');
        $worksheet1->setCellValue('I2', '最小单位');
        $worksheet1->setCellValue('J2', '首选仓库');
        $worksheet1->setCellValue('K2', '指定库位');
        $worksheet1->setCellValue('L2', '当前库存');
        $worksheet1->setCellValue('M2', '库存上限');
        $worksheet1->setCellValue('N2', '库存下限');
        $worksheet1->setCellValue('O2', '超限数量');

        $i = 3;
        foreach ($list as $k => $v) {
            $worksheet1->setCellValueExplicit('A' . $i, $v['id'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $worksheet1->setCellValueExplicit('B' . $i, $v['barCode'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $worksheet1->setCellValue('C' . $i, $v['no']);
            $worksheet1->setCellValue('D' . $i, $v['name']);
            $worksheet1->setCellValue('E' . $i, $v['spec']);
            $worksheet1->setCellValue('F' . $i, $v['sku']);
            $worksheet1->setCellValue('G' . $i, $v['brandName']);
            $worksheet1->setCellValue('H' . $i, $v['categoryName']);
            $worksheet1->setCellValue('I' . $i, $v['unitName']);
            $worksheet1->setCellValue('J' . $i, $v['storageName']);
            $worksheet1->setCellValue('K' . $i, $v['locationName']);
            $worksheet1->setCellValue('L' . $i, $v['currentQty']);
            $worksheet1->setCellValue('M' . $i, $v['lowQty']);
            $worksheet1->setCellValue('N' . $i, $v['highQty']);
            $worksheet1->setCellValue('O' . $i, $v['currentQty'] > $v['highQty'] ? ($v['currentQty'] - $v['highQty']) : ($v['currentQty'] < $v['lowQty'] ? ($v['currentQty'] - $v['lowQty']) : 0));
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
    // 库存预警 快捷更新库存上下限字段
    public function upLHQty()
    {
        if (!$this->myservice->verifyRight('INV_WARNING')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/Goods/upLHQty 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $newQty = (float)$this->request->getGetPost('newQty');
        $type = $this->request->getGetPost('type');
        if ($id > 0) {
            $this->db->transStart();
            $this->db->table('goods')->where('id', $id)->update([$type => $newQty]);
            $data = $this->db->table('goods')->where('id', $id)->get()->getRowArray();
            $this->myservice->log('快捷编辑了产品的库存上下限，产品ID：' . $data['id'] . '，产品编码：' . $data['no']);
            $this->db->transComplete();
            if ($this->db->transStatus() === FALSE) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '更新成功！']);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '提交的是空数据！']);
    }
    // 库存盘点 快捷更新库存
    public function checkQty()
    {
        if (!$this->myservice->verifyRight('INV_CHECK')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/Goods/checkQty 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $storageId = intval($this->request->getGetPost('storageId'));
        $id = intval($this->request->getGetPost('id'));
        $checkQty = (float)$this->request->getGetPost('checkQty');
        $changeQty = (float)$this->request->getGetPost('changeQty');
        $checkName = $this->request->getGetPost('checkName');
        $checkTime = $this->request->getGetPost('checkTime');
        if ($id >= 0) {
            $storageId = (1 < $storageId ? $storageId : 1);
            $this->db->transStart();
            $this->db->table('goods')->where('id', $id)->update(['currentQty' => $checkQty, 'checkQty' => $checkQty, 'changeQty' => $changeQty, 'checkName' => $checkName, 'checkTime' => $checkTime]);
            $newQty = $this->db->table('goods')->select('currentQty')->where('id', $id)->get()->getRowArray();
            $invHisData['goodsId'] = $id;
            $invHisData['billId'] = 0;
            $invHisData['billNo'] = '库存盘点';
            $invHisData['billType'] = 'INVCHECK';
            $invHisData['transType'] = 0;
            $invHisData['changeType'] = '库存盘点';
            $invHisData['changeTime'] = date('Y-m-d H:i:s');
            $invHisData['changeCurrentQty'] = $changeQty;
            $invHisData['currentQty'] = (float)$newQty['currentQty'];
            $invHisInfo = array_intersect_key($invHisData, array_flip(['goodsId', 'billId', 'billNo', 'billType', 'transType', 'changeType', 'changeTime', 'changeCurrentQty', 'currentQty']));
            $this->db->table('inv_history')->insert($invHisInfo);
            $data = $this->db->table('goods')->where('id', $id)->get()->getRowArray();
            $this->myservice->log('在PC端提交了库存盘点，产品ID:' . $data['id'] . '，产品编码:' . $data['no'] . '，盘点库存:' . $checkQty);
            $this->db->transComplete();
            if ($this->db->transStatus() === FALSE) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '盘点成功！']);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '提交的是空数据！']);
    }
    // 库存呆滞
    public function listInventoryDull()
    {
        if (!$this->myservice->verifyRight('INV_DULL')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/Goods/listInventoryDull 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $page = max(intval($this->request->getGetPost('page')), 1);
        $rows = max(intval($this->request->getGetPost('rows')), 100);
        $sidx = $this->request->getGetPost('sidx');
        $sord = strtoupper((string)$this->request->getGetPost('sord')) === 'ASC' ? 'ASC' : 'DESC';
        $categoryId = intval($this->request->getGetPost('categoryId'));
        $monthNum = intval($this->request->getGetPost('monthNum'));
        $builder = $this->db->table('goods');
        $builder->where('currentQty !=', 0)->whereNotIn('categoryId', [263, 435])->where('isDisable <', 2)->where('isDelete', 0);
        if ($monthNum > 0) {
            $builder->where('billDate <', date('Y-m-d', strtotime('-' . $monthNum . ' months')))->where('billDate >', '0000-00-00');
        }
        if ($categoryId > 0) {
<<<<<<< HEAD
            $cid_arr = array_column($this->db->table('category')->select('id')->where('isDelete', 0)->where('FIND_IN_SET(' . $categoryId . ', path) >', 0, false)->get()->getResultArray(), 'id');
=======
            $cid_arr = array_column($this->db->table('category')->select('id')->where('isDelete', 0)->where('FIND_IN_SET(' . $this->db->escape($categoryId) . ', path) >', 0, false)->get()->getResultArray(), 'id');
>>>>>>> 151b415 (更新语言最新版本+最新底层框架后全部差异化代码适配重写)
            if (is_array($cid_arr) && count($cid_arr) > 0) {
                $builder->whereIn('categoryId', $cid_arr);
            }
        }
        $allowedSort = ['id','barCode','no','name','spec','sku','brandName','unitName','storageName','locationName','categoryName','currentQty','billDate','isDisable'];
        $orderField  = $sidx ? (in_array($sidx, $allowedSort, true) ? $sidx : 'id') : 'id';
        $list = (clone $builder)->orderBy($orderField, $sord)->limit($rows)->offset($rows * ($page - 1))->get()->getResultArray();
        $total = $builder->countAllResults();
        $v = [];
        foreach ($list as $arr => $row) {
            $v[$arr]['id'] = intval($row['id']);
            $v[$arr]['barCode'] = $row['barCode'];
            $v[$arr]['no'] = $row['no'];
            $v[$arr]['name'] = $row['name'];
            $v[$arr]['spec'] = $row['spec'];
            $v[$arr]['sku'] = $row['sku'];
            $v[$arr]['brandId'] = intval($row['brandId']);
            $v[$arr]['brandName'] = $row['brandName'];
            $v[$arr]['unitId'] = intval($row['unitId']);
            $v[$arr]['unitName'] = $row['unitName'];
            $v[$arr]['storageId'] = intval($row['storageId']);
            $v[$arr]['storageName'] = $row['storageName'];
            $v[$arr]['locationId'] = intval($row['locationId']);
            $v[$arr]['locationName'] = $row['locationName'];
            $v[$arr]['categoryId'] = $row['categoryId'];
            $v[$arr]['categoryName'] = $row['categoryName'];
            $v[$arr]['currentQty'] = (float)$row['currentQty'];
            $v[$arr]['billDate'] = $row['billDate'];
            $v[$arr]['isDisable'] = intval($row['isDisable']);
        }
        $json['status'] = 200;
        $json['msg'] = 'success';
        $json['data']['page'] = $page;
        $json['data']['records'] = $total;
        $json['data']['total'] = ceil($json['data']['records'] / $rows);
        $json['data']['rows'] = $v;
        return $this->response->setJSON($json);
    }

    // 产品选择 最近消耗量(销售出库量+作为生产原料消耗量)
    public function listQty()
    {
        if (!$this->myservice->verifyRight('GOODS_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/Goods/listQty 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $which = $this->request->getGetPost('which');
        $goodsId = intval($this->request->getPost('goodsId'));
        $qty = 0;
        if ($which == 'LastYear') { //近一年
            $endDate = date('Y-m-d', strtotime(date('Y-m-d') . " -12 month"));
            $qty = $this->db->table('inv_history')
            ->selectSum('changeCurrentQty', 'qty')
            ->where(['goodsId' => $goodsId, 'changeTime >=' => $endDate . ' 00:00:00'])
            ->groupStart()
                ->whereIn('billType', ['SA', 'SC'])
                ->orGroupStart()
                    ->where(['billType' => 'PK', 'transType' => 102])
                ->groupEnd()
                ->orGroupStart()
                    ->where(['billType' => 'PP', 'transType' => 102])
                ->groupEnd()
            ->groupEnd()
            ->get()->getRowArray()['qty'] ?? 0;
        }
        $json['status'] = 200;
        $json['msg'] = 'success';
        $json['qty'] = intval($qty) > 0 ? 0 : abs(intval($qty)); // 如果消耗量是正数，则说明并无消耗，置为0
        return $this->response->setJSON($json);
    }
    // 产品选择 最近采购|销售价格
    public function listPrice()
    {
        if (!$this->myservice->verifyRight('GOODS_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/Goods/listPrice 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $arr = ['PO' => 'PUR', 'SO' => 'SALE'];
        $type = $this->request->getGetPost('type');
        $goodsId = intval($this->request->getPost('goodsId'));
        $priceNew3 = $this->db->table('bill_order_info')->select('price')->where('goodsId', $goodsId)->where('billType', $arr[$type])->where('isDelete', 0)->orderBy('id', 'desc')->limit(3)->get()->getResultArray();
        if (!empty($priceNew3)) {
            $price1 = $priceNew3[0]['price'] ?? '无';
            $price2 = $priceNew3[1]['price'] ?? '无';
            $price3 = $priceNew3[2]['price'] ?? '无';
        } else {
            $price1 = $price2 = $price3 = '无';
        }
        $priceCalc3 = $this->db->table('bill_order_info')->selectMin('price', 'priceMin')->selectMax('price', 'priceMax')->selectAvg('price', 'priceAvg')->where('goodsId', $goodsId)->where('billType', 'PUR')->where('isDelete', 0)->get()->getRowArray();
        if ($priceCalc3) {
            $priceMin = $priceCalc3['priceMin'] ?? '无';
            $priceMax = $priceCalc3['priceMax'] ?? '无';
            $priceAvg = $priceCalc3['priceAvg'] ? round($priceCalc3['priceAvg'], 5) : '无';
        } else {
            $priceMin = $priceMax = $priceAvg = '无';
        }
        $listPrice = [
            'price1' => $price1,
            'price2' => $price2,
            'price3' => $price3,
            'priceMin' => $priceMin,
            'priceMax' => $priceMax,
            'priceAvg' => $priceAvg
        ];
        $json['status'] = 200;
        $json['msg'] = 'success';
        $json['listPrice'] = $listPrice;
        return $this->response->setJSON($json);
    }

    // 计算全部含子产品的产品的配货提成属性，更新到产品相应字段(初始的时候以及编辑业绩系数时点击保存并重算全部配货提成单价的时候会用到此方法)。
    public function upGoodsProduceInfo()
    {
        if (!$this->myservice->verifyRight('EXP_PAR_UPDATE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/Goods/upGoodsProduceInfo 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        // 只重新计算有子产品的
        $list = $this->db->table('goods')->where(['hasSon >' => 0, 'sonGoods !=' => ''])->get()->getResultArray();
        $proKittingPar = ((array)json_decode($this->db->table('option')->select('optionValue')->where('optionName', 'exp_par')->get()->getRowArray()['optionValue'] ?? '', true))['proKitting'] ?? [];
        if (empty($proKittingPar)) {
            return $this->response->setJSON(['status' => -1, 'msg' => '配货提成系数未配置或格式错误！']);
        }
        $this->db->transStart();
        foreach ($list as $k => $v) {
            $producePrice1 = ((($v['produceClass1'] + $v['produceClass2']) * $proKittingPar['par1']) + (($v['produceQty1'] + $v['produceQty2']) * $proKittingPar['par2'])) * $proKittingPar['par3'];
            $producePrice3 = $producePrice1 + $v['producePrice2'];
            $this->db->table('goods')->where('id', $v['id'])->update(['producePrice1' => $producePrice1, 'producePrice3' => $producePrice3]);
        }
        $this->myservice->log('编辑了业绩系数，并确定重算了全部含子产品的产品的配货提成属性。');
        $this->db->transComplete();
        if ($this->db->transStatus() === false) {
            return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
        }
        return $this->response->setJSON(['status' => 200, 'msg' => '更新成功！']);
    }
}
