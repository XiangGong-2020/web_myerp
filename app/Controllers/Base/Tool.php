<?php

namespace App\Controllers\Base;
use App\Controllers\BaseController;

class Tool extends BaseController
{
    // 快递查询
    public function deliverSearch()
    {
        if (!$this->myservice->verifyRight('SYS_DEFAULT')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/Tool/deliverSearch 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $company = $this->request->getGetPost('company');
        $type = $this->request->getGetPost('type');
        $postid = $this->request->getGetPost('postid');
        $phone = $this->request->getGetPost('phone');
        $temp = $this->request->getGetPost('temp');
        $this->myservice->log('使用快递查询工具查询了' . $company . $postid . '的快递轨迹信息');
        if ($type && $postid) {
            $data = ['inData' => [], 'outData' => []];
            // 先查内部流转信息
            $data['inData'] = $this->db->table('deliver_bag')->select('lastUpdator, printTime, packName, packTime, scanName, scanTime')->where(['no' => $postid])->get()->getRowArray();
            // 再查外部轨迹信息
            if ($type == 'shentong' || $type == 'zhongtong' || $type == 'yunda') { // 快递100网页版查不到申通、中通、韵达了，改为用百度查
                $token = urlencode($this->db->table('bid_temp')->select('cookie')->where(['site' => '百度查快递token'])->get()->getRowArray()['cookie'] ?? '');
                $url = 'https://alayn.baidu.com/express/appdetail/pc_express?query_from_srcid=51150&tokenV2=' . $token . '&com=' . $type . '&nu=' . $postid;
                $res = global_curl($url, [
                    'httpheader' => [
                        'accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7',
                        'accept-encoding: gzip, deflate, br, zstd',
                        'accept-language: zh-CN,zh;q=0.9,en;q=0.8,en-GB;q=0.7,en-US;q=0.6',
                        'cache-control: no-cache',
                        'cookie: BIDUPSID=7E1542E99D7AF4645066ADAB2613C796; PSTM=1730351974; BAIDUID=F2AE02FCB779CE3295328C0AA32C00F5:FG=1; BDUSS=mlhVUNDU2ZhVUdJY3dmWG9WZkJ3aDF3Y1JWeUxQfkJocnRIV3FpanlnNVM1NEJuSUFBQUFBJCQAAAAAAAAAAAEAAADSYkxbsMvEvs34wuc4AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAFJaWWdSWllnW; BDUSS_BFESS=mlhVUNDU2ZhVUdJY3dmWG9WZkJ3aDF3Y1JWeUxQfkJocnRIV3FpanlnNVM1NEJuSUFBQUFBJCQAAAAAAAAAAAEAAADSYkxbsMvEvs34wuc4AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAFJaWWdSWllnW; MAWEBCUID=web_ltBbbrjysqGtfivQbzmVTEsNNRWCLLeHOvfWctzxygJhMiUdmL; MCITY=-%3A; H_WISE_SIDS_BFESS=60278_61027_62325_62341_62636_62720_62849_62863_62877_62882_62885_62919_62927_62921_62967; BDSFRCVID=WFDOJeC62C1e1wrs0-fihLnu6BXWvtQTH6aoifv58gsV61dbe_3MEG0PAf8g0KAMDjVJogKKKmOTH1IF_2uxOjjg8UtVJeC6EG0Ptf8g0x5; H_BDCLCKID_SF=tbFJ_ILXJC-3HtJYq4Rh-tFOMHLjJI62aKDs-hQgBhcqEIL4LTQqDJ-XKN0LKtc3BIot_qo9Q-QMVfbSj4QoMtKBjH7MbhKqtCna0xAbBl5nhMJqb67JDMPFXtOBLfTy523iab6vQpPaOqQ3DRoWXPIqbN7P-p5Z5mAqKl0MLPbtbb0x25_0-nDSHH8Dq6ke3H; BDSFRCVID_BFESS=WFDOJeC62C1e1wrs0-fihLnu6BXWvtQTH6aoifv58gsV61dbe_3MEG0PAf8g0KAMDjVJogKKKmOTH1IF_2uxOjjg8UtVJeC6EG0Ptf8g0x5; H_BDCLCKID_SF_BFESS=tbFJ_ILXJC-3HtJYq4Rh-tFOMHLjJI62aKDs-hQgBhcqEIL4LTQqDJ-XKN0LKtc3BIot_qo9Q-QMVfbSj4QoMtKBjH7MbhKqtCna0xAbBl5nhMJqb67JDMPFXtOBLfTy523iab6vQpPaOqQ3DRoWXPIqbN7P-p5Z5mAqKl0MLPbtbb0x25_0-nDSHH8Dq6ke3H; H_PS_PSSID=60278_61027_62325_62341_62636_62720_62849_62863_62877_62882_62885_62919_62927_62921_62967; H_WISE_SIDS=60278_61027_62325_62341_62636_62720_62849_62863_62877_62882_62885_62919_62927_62921_62967; BA_HECTOR=0101a08k208k24202kah850k019m871k0133o22; BAIDUID_BFESS=F2AE02FCB779CE3295328C0AA32C00F5:FG=1; delPer=0; ZFY=O7nAHXM17qkrQsY59fs7fhWGMp7ww1RmQk2tCMpLScE:C; BDRCVFR[dDFRJWj44Gm]=I67x6TjHwwYf0; ZD_ENTRY=baidu; ab_sr=1.0.1_OGE2ZTZhZjI1NDQ4M2JkZGRkNzFkNzlhNDYzYmQwNTdmNGRlOGJjMjdlZmQ2ODBhNGMxMDg1NDU5OTU0YjJkMjU4NzExMTI4OWFjM2I3MDFmNTc5MjYyN2Y2NTJiZTUwMjE3OGJmNjRmMGI3MzYzZDI3Y2RmMzJkYzgxZDMxNmEwMDE5Nzg0Yzk0NzExNmU5YzkwNTFlYzJjZjBjNjAwNjZiMjgxNWI1YWNkNWQ1MGI4ZGJiZjYzYzNlZTFiYTli; BDORZ=B490B5EBF6F3CD402E515D22BCDA1598; PSINO=6',
                        'pragma: no-cache',
                        'upgrade-insecure-requests: 1',
                        'user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36 Edg/135.0.0.0'
                    ]
                ]);
                if (false !== $res) {
                    $obj = json_decode($res, true);
                    $arr = $obj['data']['context'] ?? [];
                    if (is_array($arr) && count($arr) > 0) {
                        foreach ($arr as $k => $v) {
                            $data['outData'][] = ['time' => date('Y-m-d H:i:s', $v['time']), 'context' => $v['DESC']];
                        }
                    } else {
                        $data['outData'][] = ['time' => date('Y-m-d H:i:s'), 'context' => '无快递轨迹信息！'];
                    }
                } else {
                    $data['outData'][] = ['time' => date('Y-m-d H:i:s'), 'context' => '获取快递轨迹信息失败！'];
                }
            } else { // 其他还是用快递100查
                $url = 'https://www.kuaidi100.com/query?type=' . $type . '&postid=' . $postid . '&temp=' . $temp . '&phone=' . $phone;
                $res = global_curl($url, [
                    'httpheader' => [
                        'Accept: */*',
                        'Accept-Language: zh-CN,zh;q=0.9,en;q=0.8,en-GB;q=0.7,en-US;q=0.6',
                        'Cache-Control: no-cache',
                        'Connection: keep-alive',
                        'Cookie: csrftoken=0;',
                        'Host: www.kuaidi100.com',
                        'Pragma: no-cache',
                        'Referer: https://www.kuaidi100.com/all/jd.shtml',
                        'Sec-Fetch-Dest: empty',
                        'Sec-Fetch-Mode: cors',
                        'Sec-Fetch-Site: same-origin',
                        'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/118.0.0.0 Safari/537.36 Edg/118.0.2088.46',
                        'X-Requested-With: XMLHttpRequest',
                        'sec-ch-ua: "Chromium";v="118", "Microsoft Edge";v="118", "Not=A?Brand";v="99"',
                        'sec-ch-ua-mobile: ?0'
                    ]
                ]);
                if (false !== $res) {
                    $obj = json_decode($res, true);
                    $arr = $obj['data'] ?? [];
                    if (is_array($arr) && count($arr) > 0) {
                        $data['outData'] = $arr;
                    } else {
                        $data['outData'][] = ['time' => date('Y-m-d H:i:s'), 'context' => ($type == 'shunfeng' && $phone == '') ? '要查询顺丰速运，请输入发件人或收件人电话后4位！' : '无快递轨迹信息！'];
                    }
                } else {
                    $data['outData'][] = ['time' => date('Y-m-d H:i:s'), 'context' => '获取快递轨迹信息失败！'];
                }
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '查询完成！', 'data' => $data]);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '提交的是空数据！']);
    }

    // 快递估算
    public function deliverPredict()
    {
        if (!$this->myservice->verifyRight('SYS_DEFAULT')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/Tool/deliverPredict 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $province = (int)$this->request->getGetPost('province');
        $city = (int)$this->request->getGetPost('city');
        $area = (int)$this->request->getGetPost('area');
        $address = mb_substr($this->request->getGetPost('address'), 0, 2);
        $weight = (float)$this->request->getGetPost('weight');

        //计算运费
        $amountYZ = deliver_amount_calc('邮政快递', $address, $weight);
        $amountST = deliver_amount_calc('申通快递', $address, $weight);
        $amountSF = deliver_amount_calc('顺丰速运', $address, $weight);
        $amountBS = deliver_amount_calc('百世快运', $address, $weight);
        $amountYT = deliver_amount_calc('圆通速递', $address, $weight);
        $amountJD = deliver_amount_calc('京东快递', $address, $weight);
        $amountJDZH = deliver_amount_calc('京东重货', $address, $weight);

        //获取时效
        $builder = $this->db->table('areas');
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
        $deliverTimeArr = $builder->select('deliverTimeSF, deliverTimeYZ, deliverTimeST, deliverTimeYT, deliverTimeJD, deliverTimeBS, deliverTimeJDZH')->get()->getRowArray();
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
        $this->myservice->log('使用快递估算工具估算了 ' . $weight . 'KG 包裹发往 ' . $address . ' 所需的费用与时效');
        return $this->response->setJSON(['status' => 200, 'msg' => 'success', 'data' => $data]);
    }

    // 垃圾清理
    public function clearWaste()
    {
        if (!$this->myservice->verifyRight('SYS_ADMIN')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/Tool/clearWaste 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        set_time_limit(0); // 设置脚本运行时间无限制，直到脚本运行完毕
        session_write_close(); // 主动关闭session读写保护来解除锁定，避免其他访问无法读写session造成阻塞引起假死
        $type = $this->request->getGetPost('type');
        if ($type == 'clearUpload') {
            $timeStart = intval(microtime(true));
            $dir = 'data/upfile/avatar/'; // 用户头像
            $files = scandir($dir);
            foreach ($files as $file) {
                if (is_file($dir . $file)) {
                    if ($file != 'avatar.jpg' && $this->db->table('user')->where(['avatar' => $file])->countAllResults() == 0) {
                        unlink($dir . $file);
                    }
                }
            }
            $dir = 'data/upfile/bidQuote/'; // 预算报价 资料
            $files = scandir($dir);
            foreach ($files as $file) {
                if (is_file($dir . $file)) {
                    if ($this->db->table('bill_bidquote')->like('files', $file)->countAllResults() == 0) {
                        unlink($dir . $file);
                    }
                }
            }
            $dir = 'data/upfile/bidQuote/excel_temp/'; // 预算报价 表格上传缓存文件夹
            $files = scandir($dir);
            foreach ($files as $file) {
                if (is_file($dir . $file)) {
                    unlink($dir . $file);
                }
            }
            $dir = 'data/upfile/bidService/'; // 代办跑腿 资料
            $files = scandir($dir);
            foreach ($files as $file) {
                if (is_file($dir . $file)) {
                    if ($this->db->table('bid_service')->like('files', $file)->countAllResults() == 0) {
                        unlink($dir . $file);
                    }
                }
            }
            $dir = 'data/upfile/company/'; // 经营主体 营业执照
            $files = scandir($dir);
            foreach ($files as $file) {
                if (is_file($dir . $file)) {
                    if ($this->db->table('company')->where(['businessLicense' => $file])->countAllResults() == 0) {
                        unlink($dir . $file);
                    }
                }
            }
            $dir = 'data/upfile/contact/'; // 联系人 资料
            $files = scandir($dir);
            foreach ($files as $file) {
                if (is_file($dir . $file)) {
                    if ($this->db->table('contact')->like('files', $file)->countAllResults() == 0) {
                        unlink($dir . $file);
                    }
                }
            }
            $dir = 'data/upfile/contract/'; // 特销+特采 资料
            $files = scandir($dir);
            foreach ($files as $file) {
                if (is_file($dir . $file)) {
                    if ($this->db->table('bill_contract')->like('files', $file)->countAllResults() == 0) {
                        unlink($dir . $file);
                    }
                }
            }
            $dir = 'data/upfile/contract/excel_temp/'; // 特销+特采 表格上传缓存文件夹
            $files = scandir($dir);
            foreach ($files as $file) {
                if (is_file($dir . $file)) {
                    unlink($dir . $file);
                }
            }
            $dir = 'data/upfile/deliver/excel_temp/'; // 快递月结 表格上传缓存文件夹
            $files = scandir($dir);
            foreach ($files as $file) {
                if (is_file($dir . $file)) {
                    unlink($dir . $file);
                }
            }
            $dir = 'data/upfile/goods/file/'; // 产品 文件
            $files = scandir($dir);
            foreach ($files as $file) {
                if (is_file($dir . $file)) {
                    if ($this->db->table('goods')->like('files', $file)->countAllResults() == 0 && $this->db->table('goods_file')->where(['url' => $file])->countAllResults() == 0) {
                        unlink($dir . $file);
                    }
                }
            }
            $dir = 'data/upfile/goods/img/'; // 产品 图片
            $files = scandir($dir);
            foreach ($files as $file) {
                if (is_file($dir . $file)) {
                    if ($this->db->table('goods')->like('imgs', $file)->countAllResults() == 0) {
                        unlink($dir . $file);
                    }
                }
            }
            $dir = 'data/upfile/goodsTask/'; // 定制申请+产品开发 资料+图片
            $files = scandir($dir);
            foreach ($files as $file) {
                if (is_file($dir . $file)) {
                    if (
                        $this->db->table('goods_dev_req')->like('imgs', $file)->countAllResults() == 0 &&
                        $this->db->table('goods_dev_req')->like('files', $file)->countAllResults() == 0 &&
                        $this->db->table('goods_task')->like('imgs', $file)->countAllResults() == 0 &&
                        $this->db->table('goods_task')->like('files', $file)->countAllResults() == 0
                    ) {
                        unlink($dir . $file);
                    }
                }
            }
            $dir = 'data/upfile/invoice/excel_temp/'; // 发票 税务局全量发票表格上传缓存文件夹
            $files = scandir($dir);
            foreach ($files as $file) {
                if (is_file($dir . $file)) {
                    unlink($dir . $file);
                }
            }
            $dir = 'data/upfile/invPo/'; // 采购订单 资料
            $files = scandir($dir);
            foreach ($files as $file) {
                if (is_file($dir . $file)) {
                    if ($this->db->table('bill_order')->where(['billType' => 'PUR'])->like('files', $file)->countAllResults() == 0) {
                        unlink($dir . $file);
                    }
                }
            }
            $dir = 'data/upfile/invSo/'; // 销售订单 资料
            $files = scandir($dir);
            foreach ($files as $file) {
                if (is_file($dir . $file)) {
                    if ($this->db->table('bill_order')->where(['billType' => 'SALE'])->like('files', $file)->countAllResults() == 0) {
                        unlink($dir . $file);
                    }
                }
            }
            $dir = 'data/upfile/monIo/'; // 财务单 资料
            $files = scandir($dir);
            foreach ($files as $file) {
                if (is_file($dir . $file)) {
                    if ($this->db->table('bill_money')->like('files', $file)->countAllResults() == 0) {
                        unlink($dir . $file);
                    }
                }
            }
            $dir = 'data/upfile/payReq/'; // 付款+报销申请 图片
            $files = scandir($dir);
            foreach ($files as $file) {
                if (is_file($dir . $file)) {
                    if ($this->db->table('pay_req')->like('imgs', $file)->countAllResults() == 0) {
                        unlink($dir . $file);
                    }
                }
            }
            $dir = 'data/upfile/printTemplate/1/'; // 打印模板 装箱清单 表格上传缓存文件夹
            $files = scandir($dir);
            foreach ($files as $file) {
                if (is_file($dir . $file)) {
                    unlink($dir . $file);
                }
            }
            $dir = 'data/upfile/printTemplate/2/'; // 打印模板 发货标签 表格上传缓存文件夹
            $files = scandir($dir);
            foreach ($files as $file) {
                if (is_file($dir . $file)) {
                    unlink($dir . $file);
                }
            }
            $dir = 'data/upfile/proProcessingTask/'; // 加工交付 交付图片
            $files = scandir($dir);
            foreach ($files as $file) {
                if (is_file($dir . $file)) {
                    if ($this->db->table('pro_processing_task_up')->like('upImgs', $file)->countAllResults() == 0) {
                        unlink($dir . $file);
                    }
                }
            }
            $dir = 'data/upfile/proKittingTask/'; // 配货任务 交付图片
            $files = scandir($dir);
            foreach ($files as $file) {
                if (is_file($dir . $file)) {
                    if ($this->db->table('pro_kitting_task_up')->like('upImgs', $file)->countAllResults() == 0) {
                        unlink($dir . $file);
                    }
                }
            }
            $dir = 'data/upfile/purReq/file/'; // 采购申请 资料
            $files = scandir($dir);
            foreach ($files as $file) {
                if (is_file($dir . $file)) {
                    if ($this->db->table('pur_req')->like('files', $file)->countAllResults() == 0) {
                        unlink($dir . $file);
                    }
                }
            }
            $dir = 'data/upfile/purReq/img/'; // 采购申请 图片
            $files = scandir($dir);
            foreach ($files as $file) {
                if (is_file($dir . $file)) {
                    if ($this->db->table('pur_req')->like('imgs', $file)->countAllResults() == 0) {
                        unlink($dir . $file);
                    }
                }
            }
            $dir = 'data/upfile/ueditor/file/'; // ueditor 文件
            $files = scandir($dir);
            foreach ($files as $file) {
                if (is_dir($dir . $file)) {
                    $files2 = scandir($dir . $file . '/');
                    foreach ($files2 as $file2) {
                        if (is_file($dir . $file . '/' . $file2)) {
                            if ($this->db->table('news')->like('content', $file2)->countAllResults() == 0) {
                                unlink($dir . $file . '/' . $file2);
                            }
                        }
                    }
                }
            }
            $dir = 'data/upfile/ueditor/image/'; // ueditor 图片
            $files = scandir($dir);
            foreach ($files as $file) {
                if (is_dir($dir . $file)) {
                    $files2 = scandir($dir . $file . '/');
                    foreach ($files2 as $file2) {
                        if (is_file($dir . $file . '/' . $file2)) {
                            if ($this->db->table('service')->like('content', $file2)->countAllResults() == 0 && $this->db->table('news')->like('content', $file2)->countAllResults() == 0) {
                                unlink($dir . $file . '/' . $file2);
                            }
                        }
                    }
                }
            }
            $dir = 'data/upfile/ueditor/video/'; // ueditor 视频
            $files = scandir($dir);
            foreach ($files as $file) {
                if (is_dir($dir . $file)) {
                    $files2 = scandir($dir . $file . '/');
                    foreach ($files2 as $file2) {
                        if (is_file($dir . $file . '/' . $file2)) {
                            if ($this->db->table('news')->like('content', $file2)->countAllResults() == 0) {
                                unlink($dir . $file . '/' . $file2);
                            }
                        }
                    }
                }
            }
            $timeStop = intval(microtime(true));
            $timeCost = $timeStop - $timeStart;
            $this->myservice->log('使用垃圾清理（上传文件垃圾清理）功能一键清理了系统垃圾。耗时 ' . ($timeCost > 0 ? $timeCost : 1) . ' 秒。');
        }
        return $this->response->setJSON(['status' => 200, 'msg' => '清理完成！']);
    }
}
