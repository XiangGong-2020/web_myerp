<?php

namespace App\Controllers\Api;
use App\Controllers\BaseController;

class SpiderOrder extends BaseController
{
    /* 淘宝系灵通打单auth */
    const LT_TB_FRONT_AUTH = 'Frontend-Auth: eyJhbGciOiJIUzI1NiJ9.eyJleHAiOjEwNDI3NDY2NDU3LCJzdWIiOiIxMjM0NTY3OGh5cSIsImlkIjoxMTQyMzg5MywidXNlck5hbWUiOiIxMjM0NTY3OGh5cSIsIm5hbWUiOiIxMjM0NTY3OGh5cSIsIm9wZXJhdG9yTmFtZSI6Iui_kOiQpSIsIm9wZXJhdG9yIjoiMTIzNDU2NzhoeXE66L-Q6JClIiwicm9sZXMiOlt7ImF1dGhvcml0eSI6IlJPTEVfVVNFUiJ9XX0.wHuQju-Jl0EtwdsekHhUwC6L6MwsuL7_lmihocP6pKY';
    const LT_TB_WEB_AUTH = 'web-auth: eyJhbGciOiJIUzI1NiJ9.eyJleHAiOjEwMzMxOTgwNTYxLCJzdWIiOiIxMjM0NTY3OGh5cSIsImlkIjoxMTQyMzg5MywidXNlck5hbWUiOiIxMjM0NTY3OGh5cSIsIm5hbWUiOiIxMjM0NTY3OGh5cSIsIm9wZXJhdG9yTmFtZSI6Iui_kOiQpSIsIm9wZXJhdG9yIjoiMTIzNDU2NzhoeXE66L-Q6JClIiwicnVpZCI6MTE0MjM4OTMsInJvbGVzIjpbeyJhdXRob3JpdHkiOiJST0xFX1VTRVIifV19.4KMN2Ny6bRriQ4FjDaC2PjIA7JqgDOvPep--s2zDEvM';

    /* 拼多多系灵通打单auth */
    const LT_PDD_FRONT_AUTH = 'frontend-auth: eyJhbGciOiJIUzI1NiJ9.eyJleHAiOjEwNDMwNTY0NDY0LCJzdWIiOiI5NTY2NTkxNDMiLCJpZCI6MzEwMzcyMzYsInVzZXJOYW1lIjoiOTU2NjU5MTQzIiwibmFtZSI6IuS8jemZhueUteWtkOaXl-iIsOW6lyIsIm9wZXJhdG9yTmFtZSI6IuWQkeW3pSIsIm9wZXJhdG9yIjoi5ZCR5belIiwicm9sZXMiOlt7ImF1dGhvcml0eSI6IlJPTEVfVVNFUiJ9XX0.YN_TX4sJUw4WrPH_gGfEW9s2z3Q2UMJNjeqrJ84tvKc';
    const LT_PDD_WEB_AUTH = 'web-auth: eyJhbGciOiJIUzI1NiJ9.eyJleHAiOjEwMzMxOTgwNzMwLCJzdWIiOiI5NTY2NTkxNDMiLCJpZCI6MzEwMzcyMzYsInVzZXJOYW1lIjoiOTU2NjU5MTQzIiwibmFtZSI6IuS8jemZhueUteWtkOaXl-iIsOW6lyIsIm9wZXJhdG9yTmFtZSI6IuWQkeW3pSIsIm9wZXJhdG9yIjoi5ZCR5belIiwicnVpZCI6MzEwNzk2NjcsInJvbGVzIjpbeyJhdXRob3JpdHkiOiJST0xFX1VTRVIifV19.gf96BKd0SIQQsEJu3rbgKQ9caff424CYILX3K7JkSW4';

    /* 店小秘cookie */
    const DXM_COOKIE = 'Cookie: MYJ_MKTG_fapsc5t4tc=JTdCJTdE; dxm_i=MTI0NTgwMSFhVDB4TWpRMU9EQXghNzk3ODJmMGM5M2U2ZTY3OTBkNzY2ODg2ZDI0MWQxNTA; dxm_t=MTc3ODYzODEwMSFkRDB4TnpjNE5qTTRNVEF4IWU2YmNhZDlkZmMzZWFjZjdhZDAyYzhjNTQ4YjhkZmNl; dxm_c=WW5kZmlQQXkhWXoxWmJtUm1hVkJCZVEhODJjYjA1ODA4NGEwYTk4YWY4MTU4YWI0MTM0Yzk2OTM; dxm_w=MDE0NzgwMWRmNzdjMDIxNDdmZmZmODEzYzcwMjBiYTIhZHowd01UUTNPREF4WkdZM04yTXdNakUwTjJabVptWTRNVE5qTnpBeU1HSmhNZyFiZmFmYzZjNzM2NjM2OWYwNzA4MGFhMjJjZWFkMjcyNw; dxm_s=KvfHO6p7h1ex7Ml3BFbCyYCHVwx7Hs_xH9B_Hi1yiyc; HMACCOUNT=362FBFF0634E5EDF; Hm_lvt_f8001a3f3d9bf5923f780580eb550c0b=1786672662; MYJ_fapsc5t4tc=JTdCJTIyZGV2aWNlSWQlMjIlM0ElMjJmZmFkZTM3YS04YjMzLTRhZjktYmRkYy1lNzQyNTBmNmQ0NGQlMjIlMkMlMjJ1c2VySWQlMjIlM0ElMjIxMjQ1ODAxJTIyJTJDJTIycGFyZW50SWQlMjIlM0ElMjIxMjQzNzY0JTIyJTJDJTIyc2Vzc2lvbklkJTIyJTNBMTc4Nzk5MjMxMjg4MSUyQyUyMm9wdE91dCUyMiUzQWZhbHNlJTJDJTIybGFzdEV2ZW50SWQlMjIlM0E5JTdE; Hm_lpvt_f8001a3f3d9bf5923f780580eb550c0b=1787992322; JSESSIONID=554CDD74A424C75F9F2F9E70E67B0426; arp_scroll_position=100; tfstk=gRyS5HGltUY7XZsRJUSVCyv665GtFiWZJHiLjkpyvYHJJeEtuJrPZk-ddyzmaurdrywIklVPxYHr9DaaY4pzL7rLvPztyLoPr29Qvk4Itg0EJ2aUv7SVQOruqvDpJNWNQyj2HizWwp3dvqnoVioJdLC-2vDd7Zz4mGe-Kzo1h6g8DiiEYDLLJLHvMcgxvQHLJqhxXc8-pvUdcmnEv3p-v03vDD3xJvHLJtIjYqnKpvULDiinh17s0IgzVt6xP34cAGVSlppLhmQnWu9eB02mVC0LVq_Ol-aiNVE-lp783K2sRq2OusZ0M7Urmy61lYPabJcTeEIr25ZUCflvuIanFDZinRQwTjPQ2JhbiOLIqWEzdWEVfCmgMz2uOAtdeoMSPfnoGOpQWXn_jx2O7Bl4OueKGSb9_Ve0D5PQzZvKz5ZYlfy2udkz_lyn68L54upZ53ODOODBdmgNciOHtGvdDlVTR_vEwmm23isXy1c-mmMVciOnmbnm0OIfcUwl.';

    /* 获取最近3天已发货订单数据并处理 按每1分钟执行一次的标准设计 */
    public function salesorder_spider()
    {
        $this->db->table('option')->where('optionName', 'salesorder_spider_status')->update(['optionValue' => 1]);
        //取出套餐类产品类别信息，供后续库存操作使用
        $suitCids_arr = array_column($this->db->table('category')->select('id')->where('FIND_IN_SET("280", path) >', 0, false)->where('isDelete', 0)->get()->getResultArray(), 'id');
        // 淘宝系
        $url = 'https://ltserver.800best.com/bestsmart/tradeHist/v3/readySends';
        $res = global_curl($url, [
            'post' => 1,
            'postfields' => '{"consignFrom":"' . date('Y-m-d', strtotime("-3 days")) . 'T16:00:00.000Z","consignTo":"' . date('Y-m-d') . 'T15:59:59.999Z","pageNumber":1,"objectsPerPage":5000,"routers":["12345678hyq","和成聚科技公司","五六电子网店","的确良电子0148","tb521980958","伍陆电子旗舰","1688#12447910","DY#12589013","JD#12928741"],"userIds":[11423893,11592964,11592981,12309001,12447942,12896229,12447910,12589013,12928741],"sellerNicks":["12345678hyq","和成聚科技公司","五六电子网店","的确良电子0148","tb521980958","伍陆电子旗舰","1688#cqsbx","DY#102595011","JD#伍陆电子旗舰店"]}',
            'httpheader' => [
                'accept: application/json',
                'accept-language: zh-CN,zh;q=0.9',
                'content-type: application/json',
                self::LT_TB_WEB_AUTH
            ]
        ]);
        if ($res !== false) {
            $obj = json_decode($res, true);
            $arr = $obj['data']['hits'] ?? [];
            if (is_array($arr) && count($arr) > 0) {
                $this->db->transStart();
                foreach ($arr as $k => $v) {
                    // 我们目前没开京东店铺，当前灵通打的京东订单为帮代理代发货，不同步订单信息到管理平台，只同步物流单信息即可。
                    if ($v['orderSource'] == 'JD') {
                        continue;
                    }
                    $comeFrom = ($v['orderSource'] == '1688' ? '1688_' : ($v['orderSource'] == 'DY' ? 'DY_' : 'TB_')) . ($v['title'] ?? ($v['sellerNick'] ?? ($v['orderSource'] ?? '其他')));
                    // 如果已存在该订单信息，且已发货已收款，则退出本次循环，停止本单后续处理动作
                    if ($this->db->table('bill_order')->where(['billNo' => $v['tradeId'], 'srcMoneyId1 >' => 0, 'isDelete' => 0])->countAllResults() > 0) {
                        continue;
                    }
                    if ($this->db->table('billfrom')->where('name', $comeFrom)->countAllResults() == 0) {
                        $this->db->table('billfrom')->insert(['name' => $comeFrom, 'company' => '虚拟主体']);
                    }
                    $billfrom = $this->db->table('billfrom')->select('id, name, company')->where('name', $comeFrom)->get()->getRowArray();
                    $company = $this->db->table('company')->select('id, name')->where('name', $billfrom['company'])->get()->getRowArray() ?? ['id' => 16, 'name' => '虚拟主体'];
                    $companyAccount = $this->db->table('company_account')->select('id, name')->where('company', $company['name'])->get()->getRowArray() ?? ['id' => 1, 'name' => '无主账户(户头)'];
                    $order = $order_info = $inv = $inv_info = $inv_log = $money = $money_info = [];
                    $order['billFrom'] = $comeFrom;
                    $order['billDate'] = $inv['billDate'] = $money['billDate'] = $money_info['billDate'] = date('Y-m-d', intval($v['consignTime'] / 1000));
                    $order['deliveryDate'] = date_format(date_add(date_create($order['billDate']), date_interval_create_from_date_string('5 days')), 'Y-m-d');
                    $order['billNo'] = $order['srcMoneyNo1'] = $order['srcInvNo1'] = $inv['billNo'] = $inv['srcOrderNo'] = $money['billNo'] = $money['srcNo'] = $money_info['billNo'] = $v['tradeId'];
                    $order['billType'] = $inv['billType'] = 'SALE';
                    $order['transType'] = $money['srcTransType'] = 101201;
                    $inv['transType'] = 102201;
                    $money['billType'] = $money_info['billType'] = 'REC';
                    $money['transType'] = $money_info['transType'] = 103101;
                    $money['miniType'] = $money_info['miniType'] = 1;
                    $order['totalQty'] =  $order['totalDiscount'] = $order['totalAmount'] = 0;
                    $order['address'] = ($v['receiverState'] ?? '') . ($v['receiverCity'] ?? '') . ($v['receiverDistrict'] ?? '') . ($v['receiverTown'] ?? ($v['town'] ?? ''));
                    $order['expressCompanyName'] = $v['expressCompanyName'] ?? '';
                    $order['expressNo'] = $v['expressNo'] ?? '';
                    $order['goods'] = $inv['goods'] = [];
                    foreach ($v['orders'] as $k1 => $v1) {
                        $order['totalQty'] += intval($v1['num']);
                        $order['totalDiscount'] += ((float)$v1['discountFee'] - (float)$v1['adjustFee']);
                        $order['totalAmount'] += $v1['totalFee'] ? (float)$v1['totalFee'] : (float)$v1['payment'];
                        $order['goods'][$k1]['no'] = $v1['outerSkuId'] ?? ($v1['outerIid'] ?? '');
                        $order['goods'][$k1]['name'] = $v1['title'];
                        $order['goods'][$k1]['sku'] = $v1['skuPropertiesName'] ?? '';
                        $order['goods'][$k1]['qty'] = intval($v1['num']);
                        $order['goods'][$k1]['unit'] = '件';
                        $order['goods'][$k1]['price'] = abs((float)$v1['price']);
                        $order['goods'][$k1]['discount'] = ((float)$v1['discountFee'] - (float)$v1['adjustFee']);
                        $order['goods'][$k1]['amount'] = abs((float)$v1['payment']);
                        if ($order['goods'][$k1]['no'] == '' && strpos($v1['title'], '补差价') === false) {
                            $info = '【' . $comeFrom . '】【' . str_replace('"', '', $v1['title']) . ($v1['skuPropertiesName'] ? ' ' . str_replace('"', '', $v1['skuPropertiesName']) : '') . '】没有产品编码！';
                            if ($this->db->table('spider_err_goods')->where(['info' => $info, 'status' => 0])->countAllResults() == 0) {
                                $this->db->table('spider_err_goods')->insert(['createTime' => date('Y-m-d H:i:s'), 'info' => $info]);
                            }
                        }
                    }
                    $order['goods'] = $inv['goods'] = json_encode($order['goods'], 320);
                    $order['disRate'] = ((float)$v['discountFee'] / $order['totalAmount']) ?? 0;
                    $order['disAmount'] = (float)$v['discountFee'];
                    $order['deliverAmount'] = (float)$v['postFee'];
                    $order['amount'] = $inv['amount'] = $money['amount'] = $money['cleared'] = $money_info['amount'] = (float)$v['payment'];
                    $order['moneyStatus1'] = $order['goodsStatus1'] = $inv['goodsStatus'] = $money['moneyStatus'] = 2;
                    $inv['totalOrderQty'] = $inv['totalQty'] = $order['totalQty'];
                    $order['uId'] = $inv['uId'] = $money['uId'] = $money_info['uId'] = $order['checkId'] = $inv['checkId'] = $money['checkId'] = $money_info['checkId'] = 1;
                    $order['uName'] = $inv['uName'] = $money['uName'] = $order['checkName'] = $inv['checkName'] = $money['checkName'] = '系统';
                    $order['contactId'] = $inv['contactId'] = $money['contactId'] = $money_info['contactId'] = 1;
                    $order['contactName'] = $inv['contactName'] = $money['contactName'] = '默认客户';
                    $order['sId'] = $company['id'];
                    $order['sName'] = $company['name'];
                    $order['description'] = ($v['buyerMessage'] ? '客户备注：' . $v['buyerMessage'] . "\r\n" : '') . ($v['sellerMemo'] ? '销售备注：' . $v['sellerMemo'] . "\r\n" : '');
                    $order['createTime'] = $order['modifyTime'] = $inv['createTime'] = $inv['modifyTime'] = $money['createTime'] = $money['modifyTime'] = $money_info['createTime'] = $money_info['settlementTime'] = date('Y-m-d H:i:s', intval($v['consignTime'] / 1000));
                    $money_info['typeId'] = 8;
                    $money_info['accId'] = $companyAccount['id'];
                    $money_info['wayId'] = 12;
                    $inv['pusaName'] = '系统';
                    // 插入前多检测一次是否重复
                    if ($this->db->table('bill_order')->where(['billNo' => $order['billNo'], 'srcMoneyId1 >' => 0, 'isDelete' => 0])->countAllResults() > 0) {
                        continue;
                    }
                    $this->db->table('bill_order')->insert($order);
                    $iid_1 = $this->db->insertID();
                    $this->db->table('bill_inventory')->insert($inv);
                    $iid_2 = $this->db->insertID();
                    $money['srcId'] = $iid_1;
                    $this->db->table('bill_money')->insert($money);
                    $iid_3 = $this->db->insertID();
                    if ($iid_1 && $iid_2 && $iid_3) {
                        $this->db->table('bill_order')->where('billNo', $order['billNo'])->update(['srcMoneyId1' => $iid_3, 'srcInvId1' => $iid_2]);
                        $this->db->table('bill_inventory')->where('billNo', $order['billNo'])->update(['srcOrderId' => $iid_1]);
                        $money_info['iid'] = $iid_3;
                        $this->db->table('bill_money_info')->insert($money_info);
                        foreach ($v['orders'] as $k2 => $v2) {
                            $no = $v2['outerSkuId'] ?? ($v2['outerIid'] ?? '');
                            $goods = $this->db->table('goods')->where(['no' => $no, 'isDelete' => 0])->get()->getRowArray() ?? [];
                            $order_info[$k2]['billNo'] = $inv_info[$k2]['billNo'] = $order['billNo'];
                            $order_info[$k2]['billType'] = $inv_info[$k2]['billType'] = $inv_log[$k2]['billType'] = $order['billType'];
                            $order_info[$k2]['transType'] = $inv_log[$k2]['transType'] = $order['transType'];
                            $inv_info[$k2]['transType'] = $inv['transType'];
                            $order_info[$k2]['goodsId'] = $inv_info[$k2]['goodsId'] = $inv_log[$k2]['goodsId'] = $goods['id'] ?? 0;
                            if ('' != $no && 0 == $order_info[$k2]['goodsId'] && strpos($v2['title'], '补差价') === false) {
                                $info = '【' . $comeFrom . '】【' . $no . ' ' . str_replace('"', '', $v2['title']) . ($v2['skuPropertiesName'] ? ' ' . str_replace('"', '', $v2['skuPropertiesName']) : '') . '】在本系统中没有找到对应的产品！';
                                if ($this->db->table('spider_err_goods')->where(['info' => $info, 'status' => 0])->countAllResults() == 0) {
                                    $this->db->table('spider_err_goods')->insert(['createTime' => date('Y-m-d H:i:s'), 'info' => $info]);
                                }
                            }
                            $order_info[$k2]['storageId'] = $inv_info[$k2]['storageId'] = $inv_log[$k2]['storageId'] = 1;
                            $order_info[$k2]['orderQty'] = $inv_info[$k2]['orderQty'] = abs(intval($v2['num']));
                            $inv_info[$k2]['qty'] = $inv_log[$k2]['qty'] = abs(intval($v2['num']));
                            $order_info[$k2]['price'] = $inv_info[$k2]['price'] = (float)$v2['price'];
                            $order_info[$k2]['discountRate'] = (((float)$v2['discountFee'] - (float)$v2['adjustFee']) / ((float)$v2['price'] * intval($v2['num'])) * 100);
                            $order_info[$k2]['discount'] = ((float)$v2['discountFee'] - (float)$v2['adjustFee']);
                            $order_info[$k2]['amount'] = $inv_info[$k2]['amount'] = $v2['totalFee'] ? (float)$v2['totalFee'] : (float)$v2['payment'];
                            $order_info[$k2]['uId'] = $order_info[$k2]['checkId'] = 1;
                            $order_info[$k2]['contactId'] = $order['contactId'];
                            $order_info[$k2]['description'] = $inv_info[$k2]['description'] = (0 == $order_info[$k2]['goodsId'] ? (($no == '' ? '该产品未编码' : '系统没有此编码产品') . '，来源：【' . $comeFrom . '】【' . $v2['title'] . ($v2['skuPropertiesName'] ? ' ' . $v2['skuPropertiesName'] . '】' : '】')) : '');
                            $order_info[$k2]['billDate'] = $inv_info[$k2]['billDate'] = $order['billDate'];
                            $order_info[$k2]['iid'] = $inv_info[$k2]['srcOrderId'] = $inv_log[$k2]['srcOrderId'] = $iid_1;
                            $inv_info[$k2]['iid'] = $inv_log[$k2]['iid'] = $iid_2;
                            $inv_log[$k2]['uName'] = '系统';
                            $inv_log[$k2]['locationId'] = 0;
                            $inv_log[$k2]['deliverCompany'] = $v2['logisticsCompany'] ?? $order['expressCompanyName'];
                            $inv_log[$k2]['deliverNo'] = $v2['invoiceNo'] ?? $order['expressNo'];
                            $inv_log[$k2]['createTime'] = $order['createTime'];
                            // 处理销售价集合、价差异常值
                            // 分成两个价格体系【设置价默认不加运费系（淘宝、天猫、1688、抖音、官网）、设置价默认加运费系（拼多多）】
                            // 任何产品，在各自体系内相互比价，最高价和最低价相差0.3元判定为价格设置异常
                            // 任何产品，在体系间相互比价，默认加运费系最高价比默认不加运费系最低价高3.8元以上（设置的运费3~3.5元+合理的0.3元的差价）判定为价格设置异常
                            if ($goods) {
                                $shopPrices = $goods['shopPrices'] ? json_decode($goods['shopPrices'], true) : [];
                                $shopPrices[$comeFrom] = (float)$v2['price'];
                                ksort($shopPrices);
                                $shopPriceMinNoaddChannel = $shopPriceMaxNoaddChannel = $shopPriceMinAddChannel = $shopPriceMaxAddChannel = $shopPriceWarning = 0;
                                foreach ($shopPrices as $k3 => $v3) {
                                    if (strpos($k3, 'PDD_') === 0) { // 设置价默认加运费系
                                        if ($shopPriceMinAddChannel == 0 || $shopPriceMaxAddChannel == 0) {
                                            $shopPriceMinAddChannel = $shopPriceMaxAddChannel = (float)$v3;
                                        } else {
                                            if ((float)$v3 < $shopPriceMinAddChannel) {
                                                $shopPriceMinAddChannel = (float)$v3;
                                            } elseif ((float)$v3 > $shopPriceMaxAddChannel) {
                                                $shopPriceMaxAddChannel = (float)$v3;
                                            }
                                        }
                                    } else { // 设置价默认不加运费系
                                        if ($shopPriceMinNoaddChannel == 0 || $shopPriceMaxNoaddChannel == 0) {
                                            $shopPriceMinNoaddChannel = $shopPriceMaxNoaddChannel = (float)$v3;
                                        } else {
                                            if ((float)$v3 < $shopPriceMinNoaddChannel) {
                                                $shopPriceMinNoaddChannel = (float)$v3;
                                            } elseif ((float)$v3 > $shopPriceMaxNoaddChannel) {
                                                $shopPriceMaxNoaddChannel = (float)$v3;
                                            }
                                        }
                                    }
                                }
                                if ($shopPriceMinAddChannel > 0 && $shopPriceMaxAddChannel > 0) {
                                    if (($shopPriceMaxAddChannel - $shopPriceMinAddChannel) > 0.3) {
                                        $shopPriceWarning = 1;
                                        $goodsText = $no . ' ' . str_replace('"', '', $v2['title']) . ($v2['skuPropertiesName'] ? ' ' . str_replace('"', '', $v2['skuPropertiesName']) : '');
                                        $info = '在【拼多多平台（售价含运费）】比价，相差最大已达 '.round(($shopPriceMaxAddChannel - $shopPriceMinAddChannel), 2).' 元';
                                        if ($this->db->table('spider_err_price')->where([
                                            'goodsId' => $goods['id'],
                                            'shopPrices' => json_encode($shopPrices, 320),
                                            'info' => $info,
                                            'status' => 0
                                        ])->countAllResults() == 0) {
                                            $this->db->table('spider_err_price')->insert([
                                                'goodsId' => $goods['id'],
                                                'goodsText' => $goodsText,
                                                'shopPrices' => json_encode($shopPrices, 320),
                                                'info' => $info,
                                                'createTime' => date('Y-m-d H:i:s')
                                            ]);
                                        }
                                    }
                                }
                                if ($shopPriceMinNoaddChannel > 0 && $shopPriceMaxNoaddChannel > 0) {
                                    if (($shopPriceMaxNoaddChannel - $shopPriceMinNoaddChannel) > 0.3) {
                                        $shopPriceWarning = 1;
                                        $goodsText = $no . ' ' . str_replace('"', '', $v2['title']) . ($v2['skuPropertiesName'] ? ' ' . str_replace('"', '', $v2['skuPropertiesName']) : '');
                                        $info = '在【淘宝系、抖音、官网平台（售价不含运费）】比价，相差最大已达 '.round(($shopPriceMaxNoaddChannel - $shopPriceMinNoaddChannel), 2).' 元';
                                        if ($this->db->table('spider_err_price')->where([
                                            'goodsId' => $goods['id'],
                                            'shopPrices' => json_encode($shopPrices, 320),
                                            'info' => $info,
                                            'status' => 0
                                        ])->countAllResults() == 0) {
                                            $this->db->table('spider_err_price')->insert([
                                                'goodsId' => $goods['id'],
                                                'goodsText' => $goodsText,
                                                'shopPrices' => json_encode($shopPrices, 320),
                                                'info' => $info,
                                                'createTime' => date('Y-m-d H:i:s')
                                            ]);
                                        }
                                    }
                                }
                                if ($shopPriceMaxAddChannel > 0 && $shopPriceMinNoaddChannel > 0) {
                                    if (($shopPriceMaxAddChannel - $shopPriceMinNoaddChannel) > 3.8) {
                                        $shopPriceWarning = 1;
                                        $goodsText = $no . ' ' . str_replace('"', '', $v2['title']) . ($v2['skuPropertiesName'] ? ' ' . str_replace('"', '', $v2['skuPropertiesName']) : '');
                                        $info = '在【拼多多平台（售价含运费）】的最高价，比在【淘宝系、抖音、官网平台（售价不含运费）】的最低价，高出已达 '.round(($shopPriceMaxAddChannel - $shopPriceMinNoaddChannel), 2).' 元';
                                        if ($this->db->table('spider_err_price')->where([
                                            'goodsId' => $goods['id'],
                                            'shopPrices' => json_encode($shopPrices, 320),
                                            'info' => $info,
                                            'status' => 0
                                        ])->countAllResults() == 0) {
                                            $this->db->table('spider_err_price')->insert([
                                                'goodsId' => $goods['id'],
                                                'goodsText' => $goodsText,
                                                'shopPrices' => json_encode($shopPrices, 320),
                                                'info' => $info,
                                                'createTime' => date('Y-m-d H:i:s')
                                            ]);
                                        }
                                    }
                                }
                                if ($shopPriceMaxAddChannel > 0 && $shopPriceMinNoaddChannel > 0) {
                                    if (($shopPriceMaxAddChannel - $shopPriceMinNoaddChannel) < 0) {
                                        $shopPriceWarning = 1;
                                        $goodsText = $no . ' ' . str_replace('"', '', $v2['title']) . ($v2['skuPropertiesName'] ? ' ' . str_replace('"', '', $v2['skuPropertiesName']) : '');
                                        $info = '在【拼多多平台（售价含运费）】的最高价，比在【淘宝系、抖音、官网平台（售价不含运费）】的最低价还低，低出已达 '.round(($shopPriceMaxAddChannel - $shopPriceMinNoaddChannel), 2).' 元';
                                        if ($this->db->table('spider_err_price')->where([
                                            'goodsId' => $goods['id'],
                                            'shopPrices' => json_encode($shopPrices, 320),
                                            'info' => $info,
                                            'status' => 0
                                        ])->countAllResults() == 0) {
                                            $this->db->table('spider_err_price')->insert([
                                                'goodsId' => $goods['id'],
                                                'goodsText' => $goodsText,
                                                'shopPrices' => json_encode($shopPrices, 320),
                                                'info' => $info,
                                                'createTime' => date('Y-m-d H:i:s')
                                            ]);
                                        }
                                    }
                                }
                            } else {
                                $shopPrices = [];
                                $shopPrices[$comeFrom] = (float)$v2['price'];
                                $shopPriceWarning = 0;
                            }
                            // 更新产品信息、处理库存信息
                            if ($goods && in_array($goods['categoryId'], $suitCids_arr)) { // 销售套餐类
                                // 先更新对应产品网店图、销售价集合、价差异常值、本次销售价、本次变动日期
                                $this->db->table('goods')->where('id', $inv_info[$k2]['goodsId'])->update(['salePrice' => (float)$v2['price'], 'billDate' => $order['billDate'], 'shopImg' => $v2['picPath'] ?? ($goods['shopImg'] ?? ''), 'shopPrices' => json_encode($shopPrices, 320), 'shopPriceWarning' => $shopPriceWarning]);
                                // 再直接操作子产品库存
                                if ($goods['hasSon'] > 0 && strlen($goods['sonGoods']) > 0) {
                                    $sonGoods = (array)json_decode($goods['sonGoods'], true);
                                    foreach ($sonGoods as $arr4 => $row4) {
                                        $this->db->table('goods')->where('id', $row4['gid'])->set('currentQty', 'currentQty - ' . abs((float)$row4['qty1'] * (float)$row4['qty2'] * (float)$inv_info[$k2]['qty']), false)->set('billDate', $order['billDate'])->update();
                                        $newSonGoods = $this->db->table('goods')->select('currentQty')->where('id', $row4['gid'])->get()->getRowArray();
                                        $invHisData['goodsId'] = $row4['gid'];
                                        $invHisData['billId'] = $iid_2;
                                        $invHisData['billNo'] = $inv['billNo'];
                                        $invHisData['billType'] = 'SA';
                                        $invHisData['transType'] = 102201;
                                        $invHisData['changeType'] = '新增销售发货单(套餐)';
                                        $invHisData['changeTime'] = date('Y-m-d H:i:s');
                                        $invHisData['changeCurrentQty'] = -abs((float)$row4['qty1'] * (float)$row4['qty2'] * (float)$inv_info[$k2]['qty']);
                                        $invHisData['currentQty'] = (float)$newSonGoods['currentQty'];
                                        $invHisInfo = array_intersect_key($invHisData, array_flip(['goodsId', 'billId', 'billNo', 'billType', 'transType', 'changeType', 'changeTime', 'changeCurrentQty', 'currentQty']));
                                        $this->db->table('inv_history')->insert($invHisInfo);
                                    }
                                }
                            } else { // 非销售套餐类，则直接更新对应产品库存、网店图、销售价集合、价差异常值
                                if ($inv_info[$k2]['goodsId'] > 0) {
                                    $this->db->table('goods')->where('id', $inv_info[$k2]['goodsId'])->update(['currentQty' => (float)$goods['currentQty'] - $inv_info[$k2]['qty'], 'salePrice' => (float)$v2['price'], 'billDate' => $order['billDate'], 'shopImg' => $v2['picPath'] ?? $goods['shopImg'], 'shopPrices' => json_encode($shopPrices, 320), 'shopPriceWarning' => $shopPriceWarning]);
                                    $newQty = $this->db->table('goods')->select('currentQty')->where('id', $inv_info[$k2]['goodsId'])->get()->getRowArray();
                                    if (is_array($newQty) && count($newQty) > 0) {
                                        $invHisData['goodsId'] = $inv_info[$k2]['goodsId'];
                                        $invHisData['billId'] = $iid_2;
                                        $invHisData['billNo'] = $inv['billNo'];
                                        $invHisData['billType'] = 'SA';
                                        $invHisData['transType'] = 102201;
                                        $invHisData['changeType'] = '新增销售发货单';
                                        $invHisData['changeTime'] = date('Y-m-d H:i:s');
                                        $invHisData['changeCurrentQty'] = -abs($inv_info[$k2]['qty']);
                                        $invHisData['currentQty'] = (float)$newQty['currentQty'];
                                        $invHisInfo = array_intersect_key($invHisData, array_flip(['goodsId', 'billId', 'billNo', 'billType', 'transType', 'changeType', 'changeTime', 'changeCurrentQty', 'currentQty']));
                                        $this->db->table('inv_history')->insert($invHisInfo);
                                    }
                                }
                            }
                        }
                        $this->db->table('bill_order_info')->insertBatch($order_info);
                        $this->db->table('bill_inventory_info')->insertBatch($inv_info);
                        $this->db->table('bill_inventory_log')->insertBatch($inv_log);
                    }
                }
                $this->db->transComplete();
                if ($this->db->transStatus() === false) {
                    log_message('error', '灵通已发货订单同步：事务已回滚（淘宝系）！');
                }
            } else {
                log_message('error', '灵通已发货订单同步：未获取到订单列表（淘宝系）！');
            }
        } else {
            log_message('error', '灵通已发货订单同步：未获取到api数据（淘宝系）！');
        }
        // 拼多多
        $url = 'https://ltserver-pdd.800best.com/bestsmart/tradeHist/v3/readySends';
        $res = global_curl($url, [
            'post' => 1,
            'postfields' => '{"consignFrom":"' . date('Y-m-d', strtotime("-3 days")) . 'T16:00:00.000Z","consignTo":"' . date('Y-m-d') . 'T15:59:59.999Z","pageNumber":1,"objectsPerPage":2000,"routers":["PDD#31037236","PDD#31186155","PDD#31234312","PDD#31307563"],"userIds":[31037236,31186155,31234312,31307563],"sellerNicks":["PDD#伍陆电子旗舰店","PDD#晶恒电子","PDD#恒熠机电","PDD#伍陆电子"]}',
            'httpheader' => [
                'Accept: application/json',
                'Accept-Language: zh-CN,zh;q=0.9',
                'Content-Type: application/json',
                self::LT_PDD_WEB_AUTH
            ]
        ]);
        if ($res !== false) {
            $obj = json_decode($res, true);
            $arr = $obj['data']['hits'] ?? [];
            if (is_array($arr) && count($arr) > 0) {
                $this->db->transStart();
                foreach ($arr as $k => $v) {
                    $shopNameMap = [
                        '伍陆电子店' => '伍陆电子旗舰店',
                        '伍陆电子旗舰店' => '伍陆电子旗舰店',
                        '晶恒电子' => '伍陆电子灿跃专卖店',
                        '恒熠机电' => '恒熠机电',
                        '伍陆电子' => '卓航DIY科技'
                    ];
                    $comeFrom = $v['sellerNick'] ? ('PDD_' . ($shopNameMap[$v['sellerNick']] ?? $v['sellerNick'])) : 'PDD_其他';
                    // 如果已存在该订单信息，则退出本次循环，停止本单后续处理动作
                    if ($this->db->table('bill_order')->where(['billNo' => $v['tradeId'], 'srcMoneyId1 >' => 0, 'isDelete' => 0])->countAllResults() > 0) {
                        continue;
                    }
                    if ($this->db->table('billfrom')->where('name', $comeFrom)->countAllResults() == 0) {
                        $this->db->table('billfrom')->insert(['name' => $comeFrom, 'company' => '虚拟主体']);
                    }
                    $billfrom = $this->db->table('billfrom')->select('id, name, company')->where('name', $comeFrom)->get()->getRowArray();
                    $company = $this->db->table('company')->select('id, name')->where('name', $billfrom['company'])->get()->getRowArray() ?? ['id' => 16, 'name' => '虚拟主体'];
                    $companyAccount = $this->db->table('company_account')->select('id, name')->where('company', $company['name'])->get()->getRowArray() ?? ['id' => 1, 'name' => '无主账户(户头)'];
                    $order = $order_info = $inv = $inv_info = $inv_log = $money = $money_info = [];
                    $order['billFrom'] = $comeFrom;
                    $order['billDate'] = $inv['billDate'] = $money['billDate'] = $money_info['billDate'] = date('Y-m-d', intval($v['consignTime'] / 1000));
                    $order['deliveryDate'] = date_format(date_add(date_create($order['billDate']), date_interval_create_from_date_string('5 days')), 'Y-m-d');
                    $order['billNo'] = $order['srcMoneyNo1'] = $order['srcInvNo1'] = $inv['billNo'] = $inv['srcOrderNo'] = $money['billNo'] = $money['srcNo'] = $money_info['billNo'] = $v['tradeId'];
                    $order['billType'] = $inv['billType'] = 'SALE';
                    $order['transType'] = $money['srcTransType'] = 101201;
                    $inv['transType'] = 102201;
                    $money['billType'] = $money_info['billType'] = 'REC';
                    $money['transType'] = $money_info['transType'] = 103101;
                    $money['miniType'] = $money_info['miniType'] = 1;
                    $order['totalQty'] = $order['totalDiscount'] = $order['totalAmount'] = 0;
                    $order['address'] = ($v['receiverState'] ?? '') . ($v['receiverCity'] ?? '') . ($v['receiverDistrict'] ?? '') . ($v['receiverTown'] ?? ($v['town'] ?? ''));
                    $order['expressCompanyName'] = $v['expressCompanyName'] ?? '';
                    $order['expressNo'] = $v['expressNo'] ?? '';
                    $order['goods'] = $inv['goods'] = [];
                    foreach ($v['orders'] as $k1 => $v1) {
                        $order['totalQty'] += intval($v1['num']);
                        $order['totalDiscount'] += ((float)$v1['discountFee'] - (float)$v1['adjustFee']);
                        $order['totalAmount'] += intval($v1['num']) * (float)$v1['price'];
                        $order['goods'][$k1]['no'] = $v1['outerSkuId'] ?? ($v1['outerIid'] ?? '');
                        $order['goods'][$k1]['name'] = $v1['title'];
                        $order['goods'][$k1]['sku'] = $v1['skuPropertiesName'] ?? '';
                        $order['goods'][$k1]['qty'] = intval($v1['num']);
                        $order['goods'][$k1]['unit'] = '件';
                        $order['goods'][$k1]['price'] = abs((float)$v1['price']);
                        $order['goods'][$k1]['discount'] = ((float)$v1['discountFee'] - (float)$v1['adjustFee']);
                        $order['goods'][$k1]['amount'] = abs((float)($order['goods'][$k1]['qty'] * $order['goods'][$k1]['price']));
                        if ($order['goods'][$k1]['no'] == '' && strpos($v1['title'], '补差价') == false) {
                            $info = '【' . $comeFrom . '】【' . str_replace('"', '', $v1['title']) . ($v1['skuPropertiesName'] ? ' ' . str_replace('"', '', $v1['skuPropertiesName']) : '') . '】没有产品编码！';
                            if ($this->db->table('spider_err_goods')->where(['info' => $info, 'status' => 0])->countAllResults() == 0) {
                                $this->db->table('spider_err_goods')->insert(['createTime' => date('Y-m-d H:i:s'), 'info' => $info]);
                            }
                        }
                    }
                    $order['goods'] = $inv['goods'] = json_encode($order['goods'], 320);
                    $order['disRate'] = ((float)$v['discountFee'] / $order['totalAmount']);
                    $order['disAmount'] = (float)$v['discountFee'];
                    $order['deliverAmount'] = (float)$v['postFee'];
                    $order['amount'] = $inv['amount'] = $money['amount'] = $money['cleared'] = $money_info['amount'] = (float)$v['payment'];
                    $order['moneyStatus1'] = $order['goodsStatus1'] = $inv['goodsStatus'] = $money['moneyStatus'] = 2;
                    $inv['totalOrderQty'] = $inv['totalQty'] = $order['totalQty'];
                    $order['uId'] = $inv['uId'] = $money['uId'] = $money_info['uId'] = $order['checkId'] = $inv['checkId'] = $money['checkId'] = $money_info['checkId'] = 1;
                    $order['uName'] = $inv['uName'] = $money['uName'] = $order['checkName'] = $inv['checkName'] = $money['checkName'] = '系统';
                    $order['contactId'] = $inv['contactId'] = $money['contactId'] = $money_info['contactId'] = 1;
                    $order['contactName'] = $inv['contactName'] = $money['contactName'] = '默认客户';
                    $order['sId'] = $company['id'];
                    $order['sName'] = $company['name'];
                    $order['description'] = ($v['buyerMessage'] ? '客户备注：' . $v['buyerMessage'] . "\r\n" : '') . ($v['sellerMemo'] ? '销售备注：' . $v['sellerMemo'] . "\r\n" : '');
                    $order['createTime'] = $order['modifyTime'] = $inv['createTime'] = $inv['modifyTime'] = $money['createTime'] = $money['modifyTime'] = $money_info['createTime'] = $money_info['settlementTime'] = date('Y-m-d H:i:s', intval($v['consignTime'] / 1000));
                    $money_info['typeId'] = 8;
                    $money_info['accId'] = $companyAccount['id'];
                    $money_info['wayId'] = 12;
                    $inv['pusaName'] = '系统';
                    // 插入前多检测一次是否重复
                    if ($this->db->table('bill_order')->where(['billNo' => $order['billNo'], 'srcMoneyId1 >' => 0, 'isDelete' => 0])->countAllResults() > 0) {
                        continue;
                    }
                    $this->db->table('bill_order')->insert($order);
                    $iid_1 = $this->db->insertID();
                    $this->db->table('bill_inventory')->insert($inv);
                    $iid_2 = $this->db->insertID();
                    $money['srcId'] = $iid_1;
                    $this->db->table('bill_money')->insert($money);
                    $iid_3 = $this->db->insertID();
                    if ($iid_1 && $iid_2 && $iid_3) {
                        $this->db->table('bill_order')->where('billNo', $order['billNo'])->update(['srcMoneyId1' => $iid_3, 'srcInvId1' => $iid_2]);
                        $this->db->table('bill_inventory')->where('billNo', $order['billNo'])->update(['srcOrderId' => $iid_1]);
                        $money_info['iid'] = $iid_3;
                        $this->db->table('bill_money_info')->insert($money_info);
                        foreach ($v['orders'] as $k2 => $v2) {
                            $no = $v2['outerSkuId'] ?? ($v2['outerIid'] ?? '');
                            $goods = $this->db->table('goods')->where(['no' => $no, 'isDelete' => 0])->get()->getRowArray() ?? [];
                            $order_info[$k2]['billNo'] = $inv_info[$k2]['billNo'] = $order['billNo'];
                            $order_info[$k2]['billType'] = $inv_info[$k2]['billType'] = $inv_log[$k2]['billType'] = $order['billType'];
                            $order_info[$k2]['transType'] = $inv_log[$k2]['transType'] = $order['transType'];
                            $inv_info[$k2]['transType'] = $inv['transType'];
                            $order_info[$k2]['goodsId'] = $inv_info[$k2]['goodsId'] = $inv_log[$k2]['goodsId'] = $goods['id'] ?? 0;
                            if ('' != $no && 0 == $order_info[$k2]['goodsId'] && strpos($v2['title'], '补差价') === false) {
                                $info = '【' . $comeFrom . '】【' . $no . ' ' . str_replace('"', '', $v2['title']) . ($v2['skuPropertiesName'] ? ' ' . str_replace('"', '', $v2['skuPropertiesName']) : '') . '】在本系统中没有找到对应的产品！';
                                if ($this->db->table('spider_err_goods')->where(['info' => $info, 'status' => 0])->countAllResults() == 0) {
                                    $this->db->table('spider_err_goods')->insert(['createTime' => date('Y-m-d H:i:s'), 'info' => $info]);
                                }
                            }
                            $order_info[$k2]['storageId'] = $inv_info[$k2]['storageId'] = $inv_log[$k2]['storageId'] = 1;
                            $order_info[$k2]['orderQty'] = $inv_info[$k2]['orderQty'] = abs(intval($v2['num']));
                            $inv_info[$k2]['qty'] = $inv_log[$k2]['qty'] = abs(intval($v2['num']));
                            $order_info[$k2]['price'] = $inv_info[$k2]['price'] = (float)$v2['price'];
                            $order_info[$k2]['discountRate'] = (((float)$v2['discountFee'] - (float)$v2['adjustFee']) / ((float)$v2['price'] * intval($v2['num'])) * 100);
                            $order_info[$k2]['discount'] = ((float)$v2['discountFee'] - (float)$v2['adjustFee']);
                            $order_info[$k2]['amount'] = $inv_info[$k2]['amount'] = (float)($order_info[$k2]['orderQty'] * $order_info[$k2]['price']);
                            $order_info[$k2]['uId'] = $order_info[$k2]['checkId'] = 1;
                            $order_info[$k2]['contactId'] = $order['contactId'];
                            $order_info[$k2]['description'] = $inv_info[$k2]['description'] = (0 == $order_info[$k2]['goodsId'] ? (($no == '' ? '该产品未编码' : '系统没有此编码产品') . '，来源：【' . $comeFrom . '】【' . $v2['title'] . ($v2['skuPropertiesName'] ? ' ' . $v2['skuPropertiesName'] . '】' : '】')) : '');
                            $order_info[$k2]['billDate'] = $inv_info[$k2]['billDate'] = $order['billDate'];
                            $order_info[$k2]['iid'] = $inv_info[$k2]['srcOrderId'] = $inv_log[$k2]['srcOrderId'] = $iid_1;
                            $inv_info[$k2]['iid'] = $inv_log[$k2]['iid'] = $iid_2;
                            $inv_log[$k2]['uName'] = '系统';
                            $inv_log[$k2]['locationId'] = 0;
                            $inv_log[$k2]['deliverCompany'] = $v2['logisticsCompany'] ?? $order['expressCompanyName'];
                            $inv_log[$k2]['deliverNo'] = $v2['invoiceNo'] ?? $order['expressNo'];
                            $inv_log[$k2]['createTime'] = $order['createTime'];
                            // 处理销售价集合、价差异常值
                            // 分成两个价格体系【设置价默认不加运费系（淘宝、天猫、1688、抖音、官网）、设置价默认加运费系（拼多多）】
                            // 任何产品，在各自体系内相互比价，最高价和最低价相差0.3元判定为价格设置异常
                            // 任何产品，在体系间相互比价，默认加运费系最高价比默认不加运费系最低价高3.8元以上（设置的运费3~3.5元+合理的0.3元的差价）判定为价格设置异常
                            if ($goods) {
                                $shopPrices = $goods['shopPrices'] ? json_decode($goods['shopPrices'], true) : [];
                                $shopPrices[$comeFrom] = (float)$v2['price'];
                                ksort($shopPrices);
                                $shopPriceMinNoaddChannel = $shopPriceMaxNoaddChannel = $shopPriceMinAddChannel = $shopPriceMaxAddChannel = $shopPriceWarning = 0;
                                foreach ($shopPrices as $k3 => $v3) {
                                    if (strpos($k3, 'PDD_') === 0) { // 设置价默认加运费系
                                        if ($shopPriceMinAddChannel == 0 || $shopPriceMaxAddChannel == 0) {
                                            $shopPriceMinAddChannel = $shopPriceMaxAddChannel = (float)$v3;
                                        } else {
                                            if ((float)$v3 < $shopPriceMinAddChannel) {
                                                $shopPriceMinAddChannel = (float)$v3;
                                            } elseif ((float)$v3 > $shopPriceMaxAddChannel) {
                                                $shopPriceMaxAddChannel = (float)$v3;
                                            }
                                        }
                                    } else { // 设置价默认不加运费系
                                        if ($shopPriceMinNoaddChannel == 0 || $shopPriceMaxNoaddChannel == 0) {
                                            $shopPriceMinNoaddChannel = $shopPriceMaxNoaddChannel = (float)$v3;
                                        } else {
                                            if ((float)$v3 < $shopPriceMinNoaddChannel) {
                                                $shopPriceMinNoaddChannel = (float)$v3;
                                            } elseif ((float)$v3 > $shopPriceMaxNoaddChannel) {
                                                $shopPriceMaxNoaddChannel = (float)$v3;
                                            }
                                        }
                                    }
                                }
                                if ($shopPriceMinAddChannel > 0 && $shopPriceMaxAddChannel > 0) {
                                    if (($shopPriceMaxAddChannel - $shopPriceMinAddChannel) > 0.3) {
                                        $shopPriceWarning = 1;
                                        $goodsText = $no . ' ' . str_replace('"', '', $v2['title']) . ($v2['skuPropertiesName'] ? ' ' . str_replace('"', '', $v2['skuPropertiesName']) : '');
                                        $info = '在【拼多多平台（售价含运费）】比价，相差最大已达 '.round(($shopPriceMaxAddChannel - $shopPriceMinAddChannel), 2).' 元';
                                        if ($this->db->table('spider_err_price')->where([
                                            'goodsId' => $goods['id'],
                                            'shopPrices' => json_encode($shopPrices, 320),
                                            'info' => $info,
                                            'status' => 0
                                        ])->countAllResults() == 0) {
                                            $this->db->table('spider_err_price')->insert([
                                                'goodsId' => $goods['id'],
                                                'goodsText' => $goodsText,
                                                'shopPrices' => json_encode($shopPrices, 320),
                                                'info' => $info,
                                                'createTime' => date('Y-m-d H:i:s')
                                            ]);
                                        }
                                    }
                                }
                                if ($shopPriceMinNoaddChannel > 0 && $shopPriceMaxNoaddChannel > 0) {
                                    if (($shopPriceMaxNoaddChannel - $shopPriceMinNoaddChannel) > 0.3) {
                                        $shopPriceWarning = 1;
                                        $goodsText = $no . ' ' . str_replace('"', '', $v2['title']) . ($v2['skuPropertiesName'] ? ' ' . str_replace('"', '', $v2['skuPropertiesName']) : '');
                                        $info = '在【淘宝系、抖音、官网平台（售价不含运费）】比价，相差最大已达 '.round(($shopPriceMaxNoaddChannel - $shopPriceMinNoaddChannel), 2).' 元';
                                        if ($this->db->table('spider_err_price')->where([
                                            'goodsId' => $goods['id'],
                                            'shopPrices' => json_encode($shopPrices, 320),
                                            'info' => $info,
                                            'status' => 0
                                        ])->countAllResults() == 0) {
                                            $this->db->table('spider_err_price')->insert([
                                                'goodsId' => $goods['id'],
                                                'goodsText' => $goodsText,
                                                'shopPrices' => json_encode($shopPrices, 320),
                                                'info' => $info,
                                                'createTime' => date('Y-m-d H:i:s')
                                            ]);
                                        }
                                    }
                                }
                                if ($shopPriceMaxAddChannel > 0 && $shopPriceMinNoaddChannel > 0) {
                                    if (($shopPriceMaxAddChannel - $shopPriceMinNoaddChannel) > 3.8) {
                                        $shopPriceWarning = 1;
                                        $goodsText = $no . ' ' . str_replace('"', '', $v2['title']) . ($v2['skuPropertiesName'] ? ' ' . str_replace('"', '', $v2['skuPropertiesName']) : '');
                                        $info = '在【拼多多平台（售价含运费）】的最高价，比在【淘宝系、抖音、官网平台（售价不含运费）】的最低价，高出已达 '.round(($shopPriceMaxAddChannel - $shopPriceMinNoaddChannel), 2).' 元';
                                        if ($this->db->table('spider_err_price')->where([
                                            'goodsId' => $goods['id'],
                                            'shopPrices' => json_encode($shopPrices, 320),
                                            'info' => $info,
                                            'status' => 0
                                        ])->countAllResults() == 0) {
                                            $this->db->table('spider_err_price')->insert([
                                                'goodsId' => $goods['id'],
                                                'goodsText' => $goodsText,
                                                'shopPrices' => json_encode($shopPrices, 320),
                                                'info' => $info,
                                                'createTime' => date('Y-m-d H:i:s')
                                            ]);
                                        }
                                    }
                                }
                                if ($shopPriceMaxAddChannel > 0 && $shopPriceMinNoaddChannel > 0) {
                                    if (($shopPriceMaxAddChannel - $shopPriceMinNoaddChannel) < 0) {
                                        $shopPriceWarning = 1;
                                        $goodsText = $no . ' ' . str_replace('"', '', $v2['title']) . ($v2['skuPropertiesName'] ? ' ' . str_replace('"', '', $v2['skuPropertiesName']) : '');
                                        $info = '在【拼多多平台（售价含运费）】的最高价，比在【淘宝系、抖音、官网平台（售价不含运费）】的最低价还低，低出已达 '.round(($shopPriceMaxAddChannel - $shopPriceMinNoaddChannel), 2).' 元';
                                        if ($this->db->table('spider_err_price')->where([
                                            'goodsId' => $goods['id'],
                                            'shopPrices' => json_encode($shopPrices, 320),
                                            'info' => $info,
                                            'status' => 0
                                        ])->countAllResults() == 0) {
                                            $this->db->table('spider_err_price')->insert([
                                                'goodsId' => $goods['id'],
                                                'goodsText' => $goodsText,
                                                'shopPrices' => json_encode($shopPrices, 320),
                                                'info' => $info,
                                                'createTime' => date('Y-m-d H:i:s')
                                            ]);
                                        }
                                    }
                                }
                            } else {
                                $shopPrices = [];
                                $shopPrices[$comeFrom] = (float)$v2['price'];
                                $shopPriceWarning = 0;
                            }
                            // 更新产品信息、处理库存信息
                            if ($goods && in_array($goods['categoryId'], $suitCids_arr)) { // 销售套餐类
                                // 先更新对应产品网店图、销售价集合、价差异常值、本次销售价、本次变动日期
                                $this->db->table('goods')->where('id', $inv_info[$k2]['goodsId'])->update(['salePrice' => (float)$v2['price'], 'billDate' => $order['billDate'], 'shopImg' => $v2['picPath'] ?? ($goods['shopImg'] ?? ''), 'shopPrices' => json_encode($shopPrices, 320), 'shopPriceWarning' => $shopPriceWarning]);
                                if ($goods['hasSon'] > 0 && strlen($goods['sonGoods']) > 0) {
                                    $sonGoods = (array)json_decode($goods['sonGoods'], true);
                                    foreach ($sonGoods as $arr4 => $row4) {
                                        $this->db->table('goods')->where('id', $row4['gid'])->set('currentQty', 'currentQty - ' . abs((float)$row4['qty1'] * (float)$row4['qty2'] * (float)$inv_info[$k2]['qty']), false)->set('billDate', $order['billDate'])->update();
                                        $newSonGoods = $this->db->table('goods')->select('currentQty')->where('id', $row4['gid'])->get()->getRowArray();
                                        $invHisData['goodsId'] = $row4['gid'];
                                        $invHisData['billId'] = $iid_2;
                                        $invHisData['billNo'] = $inv['billNo'];
                                        $invHisData['billType'] = 'SA';
                                        $invHisData['transType'] = 102201;
                                        $invHisData['changeType'] = '新增销售发货单(套餐)';
                                        $invHisData['changeTime'] = date('Y-m-d H:i:s');
                                        $invHisData['changeCurrentQty'] = -abs((float)$row4['qty1'] * (float)$row4['qty2'] * (float)$inv_info[$k2]['qty']);
                                        $invHisData['currentQty'] = (float)$newSonGoods['currentQty'];
                                        $invHisInfo = array_intersect_key($invHisData, array_flip(['goodsId', 'billId', 'billNo', 'billType', 'transType', 'changeType', 'changeTime', 'changeCurrentQty', 'currentQty']));
                                        $this->db->table('inv_history')->insert($invHisInfo);
                                    }
                                }
                            } else {
                                if ($inv_info[$k2]['goodsId'] > 0) {
                                    $this->db->table('goods')->where('id', $inv_info[$k2]['goodsId'])->update(['currentQty' => (float)$goods['currentQty'] - $inv_info[$k2]['qty'], 'salePrice' => (float)$v2['price'], 'billDate' => $order['billDate'], 'shopImg' => $v2['picPath'] ?? $goods['shopImg'], 'shopPrices' => json_encode($shopPrices, 320), 'shopPriceWarning' => $shopPriceWarning]);
                                    $newQty = $this->db->table('goods')->select('currentQty')->where('id', $inv_info[$k2]['goodsId'])->get()->getRowArray();
                                    if (is_array($newQty) && count($newQty) > 0) {
                                        $invHisData['goodsId'] = $inv_info[$k2]['goodsId'];
                                        $invHisData['billId'] = $iid_2;
                                        $invHisData['billNo'] = $inv['billNo'];
                                        $invHisData['billType'] = 'SA';
                                        $invHisData['transType'] = 102201;
                                        $invHisData['changeType'] = '新增销售发货单';
                                        $invHisData['changeTime'] = date('Y-m-d H:i:s');
                                        $invHisData['changeCurrentQty'] = -abs($inv_info[$k2]['qty']);
                                        $invHisData['currentQty'] = (float)$newQty['currentQty'];
                                        $invHisInfo = array_intersect_key($invHisData, array_flip(['goodsId', 'billId', 'billNo', 'billType', 'transType', 'changeType', 'changeTime', 'changeCurrentQty', 'currentQty']));
                                        $this->db->table('inv_history')->insert($invHisInfo);
                                    }
                                }
                            }
                        }
                        $this->db->table('bill_order_info')->insertBatch($order_info);
                        $this->db->table('bill_inventory_info')->insertBatch($inv_info);
                        $this->db->table('bill_inventory_log')->insertBatch($inv_log);
                    }
                }
                $this->db->transComplete();
                if ($this->db->transStatus() === false) {
                    log_message('error', '灵通已发货订单同步：事务已回滚（拼多多）！');
                }
            } else {
                log_message('error', '灵通已发货订单同步：未获取到订单列表（拼多多）！');
            }
        } else {
            log_message('error', '灵通已发货订单同步：未获取到api数据（拼多多）！');
        }
        // 店小秘
        $url = 'https://www.dianxiaomi.com/api/package/list.json';
        $res = global_curl($url, [
            'post' => 1,
            'postfields' => 'pageNo=1&pageSize=100&shopId=-1&state=shipped&platform=&isSearch=1&searchType=orderId&authId=-1&startTime='.date('Y-m-d', strtotime("-3 days")).'&endTime='.date('Y-m-d').'&country=&orderField=shipped_time&isVoided=0&isRemoved=0&ruleId=-1&sysRule=&applyType=&applyStatus=&printJh=-1&printMd=-1&commitPlatform=success&productStatus=&jhComment=-1&storageId=0&isOversea=-1&isFree=-1&isBatch=-1&history=&custom=-1&timeOut=0&refundStatus=0&buyerAccount=&forbiddenStatus=-1&forbiddenReason=0&behindTrack=-1&orderId=&axios_cancelToken=true',
            'httpheader' => [
                'Accept: application/json, text/plain, */*',
                'Accept-Encoding: gzip, deflate, br',
                'Accept-Language: zh-CN,zh;q=0.9',
                'Bx-V: 2.5.11',
                'Cache-Control: no-cache',
                'Content-Length: 495',
                'Content-Type: application/x-www-form-urlencoded',
                self::DXM_COOKIE,
                'Origin: https://www.dianxiaomi.com',
                'Pragma: no-cache',
                'Referer: https://www.dianxiaomi.com/web/order/shipped/success?go=m10403',
                'User-Agent: Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/116.0.5845.97 Safari/537.36 Core/1.116.567.400 QQBrowser/19.7.6764.400'
            ]
        ]);
        if ($res !== false) {
            $obj = json_decode($res, true);
            $obj = $obj['data']['page']['list'] ?? [];
            $count = (is_countable($obj) && count($obj) > 0) ? count($obj[0]) : 0;
            if ($count > 0) {
                $this->db->transStart();
                foreach ($obj as $k => $v) {
                    // 如果已存在该订单信息，则退出本次循环，停止本单后续处理动作
                    if ($this->db->table('bill_order')->where(['billNo' => $v['orderId'], 'isDelete' => 0])->countAllResults() > 0) {
                        continue;
                    }
                    $comeFrom = str_replace('Lazada：', 'LAZADA_', str_replace('速卖通：', 'SMT_', $v['shopPlatform']));
                    if ($this->db->table('billfrom')->where('name', $comeFrom)->countAllResults() == 0) {
                        $this->db->table('billfrom')->insert(['name' => $comeFrom, 'company' => '虚拟主体']);
                    }
                    $billfrom = $this->db->table('billfrom')->select('id, name, company')->where('name', $comeFrom)->get()->getRowArray();
                    $company = $this->db->table('company')->select('id, name')->where('name', $billfrom['company'])->get()->getRowArray() ?? ['id' => 16, 'name' => '虚拟主体'];
                    $companyAccount = $this->db->table('company_account')->select('id, name')->where('company', $company['name'])->get()->getRowArray() ?? ['id' => 1, 'name' => '无主账户(户头)'];
                    $order = $order_info = $inv = $inv_info = $inv_log = $money = $money_info = [];
                    $order['billFrom'] = $comeFrom;
                    $order['billDate'] = $inv['billDate'] = $money['billDate'] = $money_info['billDate'] = date('Y-m-d', intval($v['orderPayTime'] / 1000));
                    $order['deliveryDate'] = date_format(date_add(date_create($order['billDate']), date_interval_create_from_date_string('5 days')), 'Y-m-d');
                    $order['billNo'] = $order['srcMoneyNo1'] = $order['srcInvNo1'] = $inv['billNo'] = $inv['srcOrderNo'] = $money['billNo'] = $money['srcNo'] = $money_info['billNo'] = $v['orderId'];
                    $order['billType'] = $inv['billType'] = 'SALE';
                    $order['transType'] = $money['srcTransType'] = 101201;
                    $inv['transType'] = 102201;
                    $money['billType'] = $money_info['billType'] = 'REC';
                    $money['transType'] = $money_info['transType'] = 103101;
                    $money['miniType'] = $money_info['miniType'] = 1;
                    $order['totalAmount'] = 0;
                    $order['totalDiscount'] = 0;
                    $order['totalQty'] = 0;
                    $order['address'] = '国外:' . $v['countryCN'];
                    $order['expressCompanyName'] = '国际物流';
                    $order['expressNo'] = $v['trackingNumber'];
                    $order['goods'] = $inv['goods'] = [];
                    $productList = $v['productList'] ?? [];
                    $count2 = (is_countable($productList) && count($productList) > 0) ? count($productList) : 0;
                    if ($count2 > 0) {
                        for ($i = 0; $i < $count2; $i++) {
                            $order['totalQty'] += $productList[$i]['quantity'];
                            $order['goods'][$i]['no'] = $productList[$i]['productDisplaySku'];
                            $goods = $this->db->table('goods')->where('no', $productList[$i]['productDisplaySku'])->get()->getRowArray();
                            $order['goods'][$i]['name'] = $goods['name'] ?? '';
                            $order['goods'][$i]['spec'] = $goods['spec'] ?? '';
                            $order['goods'][$i]['sku'] = $goods['sku'] ?? '';
                            $order['goods'][$i]['qty'] = $productList[$i]['quantity'];
                            $order['goods'][$i]['unit'] = $goods['unitName'] ?? '件';
                            //如果是非人民币价格，则使用产品预销售价（人民币价格）
                            $order['goods'][$i]['price'] = $productList[$i]['price'];
                            $order['goods'][$i]['discount'] = 0;
                            $order['goods'][$i]['amount'] = abs((float)($productList[$i]['price'] * $productList[$i]['quantity']));
                            $order['totalAmount'] += $order['goods'][$i]['amount'];
                            if (!$order['goods'][$i]['no']) {
                                $info = '【' . $comeFrom . '】【订单号 ' . $order['billNo'] . '】中有产品没有设置产品编码！';
                                if ($this->db->table('spider_err_goods')->where(['info' => $info, 'status' => 0])->countAllResults() == 0) {
                                    $this->db->table('spider_err_goods')->insert(['createTime' => date('Y-m-d H:i:s'), 'info' => $info]);
                                }
                            }
                            if (!$goods) {
                                $info = '【' . $comeFrom . '】【订单号 ' . $order['billNo'] . '】中的【' . $order['goods'][$i]['no'] . '】在本系统中没有找到对应的产品！';
                                if ($this->db->table('spider_err_goods')->where(['info' => $info, 'status' => 0])->countAllResults() == 0) {
                                    $this->db->table('spider_err_goods')->insert(['createTime' => date('Y-m-d H:i:s'), 'info' => $info]);
                                }
                            }
                        }
                    } else {
                        log_message('error', '店小秘已发货订单同步：未匹配到订单 ' . $order['billNo'] . ' 的产品数据！本订单无法处理！');
                        continue;
                    }
                    $order['goods'] = $inv['goods'] = json_encode($order['goods'], 320);
                    $order['disRate'] = ((float)$order['totalDiscount'] / $order['totalAmount']);
                    $order['disAmount'] = (float)$order['totalDiscount'];
                    $order['deliverAmount'] = $v['orderAmount'] - $order['totalAmount'];
                    $order['amount'] = $inv['amount'] = $money['amount'] = $money['cleared'] = $money_info['amount'] = $v['orderAmount'];
                    $order['moneyStatus1'] = $order['goodsStatus1'] = $inv['goodsStatus'] = $money['moneyStatus'] = 2;
                    $inv['totalOrderQty'] = $inv['totalQty'] = $order['totalQty'];
                    $order['uId'] = $inv['uId'] = $money['uId'] = $money_info['uId'] = $order['checkId'] = $inv['checkId'] = $money['checkId'] = $money_info['checkId'] = 1;
                    $order['uName'] = $inv['uName'] = $money['uName'] = $order['checkName'] = $inv['checkName'] = $money['checkName'] = '系统';
                    $order['contactId'] = $inv['contactId'] = $money['contactId'] = $money_info['contactId'] = 1;
                    $order['contactName'] = $inv['contactName'] = $money['contactName'] = '默认客户';
                    $order['sId'] = $company['id'];
                    $order['sName'] = $company['name'];
                    $order['createTime'] = $order['modifyTime'] = $inv['createTime'] = $inv['modifyTime'] = $money['createTime'] = $money['modifyTime'] = $money_info['createTime'] = $money_info['settlementTime'] = date('Y-m-d H:i:s', intval($v['orderPayTime'] / 1000));
                    $money_info['typeId'] = 8;
                    $money_info['accId'] = $companyAccount['id'];
                    $money_info['wayId'] = 12;
                    $inv['pusaName'] = '系统';
                    // 插入前多检测一次是否已存在本订单
                    if ($this->db->table('bill_order')->where(['billNo' => $order['billNo'], 'isDelete' => 0])->countAllResults() > 0) {
                        continue;
                    }
                    $this->db->table('bill_order')->insert($order);
                    $iid_1 = $this->db->insertID();
                    $this->db->table('bill_inventory')->insert($inv);
                    $iid_2 = $this->db->insertID();
                    $money['srcId'] = $iid_1;
                    $this->db->table('bill_money')->insert($money);
                    $iid_3 = $this->db->insertID();
                    if ($iid_1 && $iid_2 && $iid_3) {
                        $this->db->table('bill_order')->where('billNo', $order['billNo'])->update(['srcMoneyId1' => $iid_3, 'srcInvId1' => $iid_2]);
                        $this->db->table('bill_inventory')->where('billNo', $order['billNo'])->update(['srcOrderId' => $iid_1]);
                        $money_info['iid'] = $iid_3;
                        $this->db->table('bill_money_info')->insert($money_info);
                        $orderGoods = json_decode($order['goods'], true);
                        foreach ($orderGoods as $k2 => $v2) {
                            $no = $v2['no'] ?? '';
                            $goods = $this->db->table('goods')->where('no', $no)->get()->getRowArray();
                            $order_info[$k2]['billNo'] = $inv_info[$k2]['billNo'] = $order['billNo'];
                            $order_info[$k2]['billType'] = $inv_info[$k2]['billType'] = $inv_log[$k2]['billType'] = $order['billType'];
                            $order_info[$k2]['transType'] = $inv_log[$k2]['transType'] = $order['transType'];
                            $inv_info[$k2]['transType'] = $inv['transType'];
                            $order_info[$k2]['goodsId'] = $inv_info[$k2]['goodsId'] = $inv_log[$k2]['goodsId'] = $goods['id'] ?? 0;
                            $order_info[$k2]['storageId'] = $inv_info[$k2]['storageId'] = $inv_log[$k2]['storageId'] = 1;
                            $order_info[$k2]['orderQty'] = $inv_info[$k2]['orderQty'] = $inv_info[$k2]['qty'] = $inv_log[$k2]['qty'] = abs(intval($v2['qty']));
                            $order_info[$k2]['price'] = $inv_info[$k2]['price'] = (float)$v2['price'];
                            $order_info[$k2]['discountRate'] = 0;
                            $order_info[$k2]['discount'] = 0;
                            $order_info[$k2]['amount'] = $inv_info[$k2]['amount'] = (float)$v2['amount'];
                            $order_info[$k2]['uId'] = $order_info[$k2]['checkId'] = $order['uId'];
                            $order_info[$k2]['contactId'] = $order['contactId'];
                            $order_info[$k2]['description'] = $inv_info[$k2]['description'] = (0 == $order_info[$k2]['goodsId'] ? (($no == '' ? '该产品未编码' : '系统没有此编码产品') . '，来源：【' . $comeFrom . '】【' . $v2['no'] . '】') : '');
                            $order_info[$k2]['billDate'] = $inv_info[$k2]['billDate'] = $order['billDate'];
                            $order_info[$k2]['iid'] = $inv_info[$k2]['srcOrderId'] = $inv_log[$k2]['srcOrderId'] = $iid_1;
                            $inv_info[$k2]['iid'] = $inv_log[$k2]['iid'] = $iid_2;
                            $inv_log[$k2]['uName'] = '系统';
                            $inv_log[$k2]['locationId'] = 0;
                            $inv_log[$k2]['deliverCompany'] = $v['agentProviderName'];
                            $inv_log[$k2]['deliverNo'] = $order['expressNo'];
                            $inv_log[$k2]['createTime'] = $order['createTime'];
                            //如果是销售套餐类产品，则直接操作子产品库存
                            if ($goods && in_array($goods['categoryId'], $suitCids_arr)) {
                                if ($goods['hasSon'] > 0 && strlen($goods['sonGoods']) > 0) {
                                    $sonGoods = (array)json_decode($goods['sonGoods'], true);
                                    foreach ($sonGoods as $arr4 => $row4) {
                                        $this->db->table('goods')->where('id', $row4['gid'])->set('currentQty', 'currentQty - ' . abs((float)$row4['qty1'] * (float)$row4['qty2'] * (float)$inv_info[$k2]['qty']), false)->set('billDate', $order['billDate'])->update();
                                        $newSonGoods = $this->db->table('goods')->select('currentQty')->where('id', $row4['gid'])->get()->getRowArray();
                                        $invHisData['goodsId'] = $row4['gid'];
                                        $invHisData['billId'] = $iid_2;
                                        $invHisData['billNo'] = $inv['billNo'];
                                        $invHisData['billType'] = 'SA';
                                        $invHisData['transType'] = 102201;
                                        $invHisData['changeType'] = '新增销售发货单(套餐)';
                                        $invHisData['changeTime'] = date('Y-m-d H:i:s');
                                        $invHisData['changeCurrentQty'] = -abs((float)$row4['qty1'] * (float)$row4['qty2'] * (float)$inv_info[$k2]['qty']);
                                        $invHisData['currentQty'] = (float)$newSonGoods['currentQty'];
                                        $invHisInfo = array_intersect_key($invHisData, array_flip(['goodsId', 'billId', 'billNo', 'billType', 'transType', 'changeType', 'changeTime', 'changeCurrentQty', 'currentQty']));
                                        $this->db->table('inv_history')->insert($invHisInfo);
                                    }
                                }
                            } else {
                                if ($inv_info[$k2]['goodsId'] > 0) {
                                    $this->db->table('goods')->where('id', $inv_info[$k2]['goodsId'])->set('currentQty', 'currentQty - ' . $inv_info[$k2]['qty'], false)->set('billDate', $order['billDate'])->update();
                                    $newQty = $this->db->table('goods')->select('currentQty')->where('id', $inv_info[$k2]['goodsId'])->get()->getRowArray();
                                    if (is_array($newQty) && count($newQty) > 0) {
                                        $invHisData['goodsId'] = $inv_info[$k2]['goodsId'];
                                        $invHisData['billId'] = $iid_2;
                                        $invHisData['billNo'] = $inv['billNo'];
                                        $invHisData['billType'] = 'SA';
                                        $invHisData['transType'] = 102201;
                                        $invHisData['changeType'] = '新增销售发货单';
                                        $invHisData['changeTime'] = date('Y-m-d H:i:s');
                                        $invHisData['changeCurrentQty'] = -abs($inv_info[$k2]['qty']);
                                        $invHisData['currentQty'] = (float)$newQty['currentQty'];
                                        $invHisInfo = array_intersect_key($invHisData, array_flip(['goodsId', 'billId', 'billNo', 'billType', 'transType', 'changeType', 'changeTime', 'changeCurrentQty', 'currentQty']));
                                        $this->db->table('inv_history')->insert($invHisInfo);
                                    }
                                }
                            }
                        }
                        $this->db->table('bill_order_info')->insertBatch($order_info);
                        $this->db->table('bill_inventory_info')->insertBatch($inv_info);
                        $this->db->table('bill_inventory_log')->insertBatch($inv_log);
                    }
                }
                $this->db->transComplete();
                if ($this->db->transStatus() === false) {
                    log_message('error', '店小秘已发货订单同步：事务已回滚！');
                }
            } else {
                log_message('error', '店小秘已发货订单同步：未匹配到数据！');
            }
        } else {
            log_message('error', '店小秘已发货订单同步：未获取到网页！');
        }
        // 官网
        $url = 'https://www.56dz.com/erpapi/order/getdeliveredorder';
        $res = global_curl($url);
        if ($res !== false) {
            $obj = json_decode($res, true);
            $arr = $obj['data'] ?? [];
            if ($obj && $obj['errno'] == 0 && is_array($arr) && count($arr) > 0) {
                $orderIds = [];
                $this->db->transStart();
                foreach ($arr as $k => $v) {
                    $comeFrom = '官网';
                    //处理运单信息，将官网订单号更新到对应的发包记录里
                    $this->db->table('deliver_bag')->where('no', $v['express_no'])->update(['orderNo' => $v['order_no']]);
                    // 如果已存在该订单信息，则退出本次循环，停止本单后续处理动作
                    if ($this->db->table('bill_order')->where(['billNo' => $v['order_no'], 'srcMoneyId1 >' => 0, 'isDelete' => 0])->countAllResults() > 0) {
                        continue;
                    }
                    if ($this->db->table('billfrom')->where('name', $comeFrom)->countAllResults() == 0) {
                        $this->db->table('billfrom')->insert(['name' => $comeFrom, 'company' => '虚拟主体']);
                    }
                    $billfrom = $this->db->table('billfrom')->select('id, name, company')->where('name', $comeFrom)->get()->getRowArray();
                    $company = $this->db->table('company')->select('id, name')->where('name', $billfrom['company'])->get()->getRowArray() ?? ['id' => 16, 'name' => '虚拟主体'];
                    $companyAccount = $this->db->table('company_account')->select('id, name')->where('company', $company['name'])->get()->getRowArray() ?? ['id' => 1, 'name' => '无主账户(户头)'];
                    $customer = $order = $order_info = $inv = $inv_info = $inv_log = $money = $money_info = [];
                    $builder = $this->db->table('contact')->where('isDelete', 0);
                    if ($v['mobile']) {
                        $builder->where('JSON_SEARCH(linkMans, "one", ' . $this->db->escape($v['mobile']) . ', NULL, "$[*].linkMobile") IS NOT NULL', null, false);
                    } elseif ($v['phone']) {
                        $builder->where('JSON_SEARCH(linkMans, "one", ' . $this->db->escape($v['phone']) . ', NULL, "$[*].linkPhone") IS NOT NULL', null, false);
                    } else {
                        $builder->where('id', -1);
                    }
                    $contactInfo = (clone $builder)->select('id, name')->get()->getRowArray();
                    $contactId = $contactInfo ? $contactInfo['id'] : '';
                    $contactName = $contactInfo ? $contactInfo['name'] : '';
                    if (strpos($v['mobile'], '*') === false && $builder->countAllResults() == 0) {
                        $customer['type'] = 0;
                        $customer['comeFrom'] = $comeFrom;
                        $customer['name'] = $v['accept_name'];
                        $customer['categoryId'] = 46;
                        $customer['cLevel'] = 0;
                        $customer['beginDate'] = date('Y-m-d');
                        $customer['uName'] = '系统';
                        $customer['createTime'] = date('Y-m-d H:i:s');
                        $customer['linkMans'] = json_encode([
                            [
                                'linkId' => '1',
                                'linkName' =>  $v['accept_name'] ?? '',
                                'linkPlace' => '',
                                'linkWangWang' => '',
                                'linkWeixin' => '',
                                'linkIm' => '',
                                'linkMobile' => $v['mobile'] ?? '',
                                'linkPhone' => $v['phone'] ?? '',
                                'linkFirst' => 1,
                                'province' => $v['province'] ?? '',
                                'city' => $v['city'] ?? '',
                                'county' => $v['county'] ?? '',
                                'address' => $v['addr'] ?? ''
                            ]
                        ], 320);
                        $this->db->table('contact')->insert($customer);
                        $contactId = $this->db->insertID();
                        $contactName = $customer['name'];
                    }
                    $order['billFrom'] = $comeFrom;
                    $order['billDate'] = $inv['billDate'] = $money['billDate'] = $money_info['billDate'] = date('Y-m-d', intval($v['create_time'] / 1000));
                    $order['deliveryDate'] = date_format(date_add(date_create($order['billDate']), date_interval_create_from_date_string('5 days')), 'Y-m-d');
                    $order['billNo'] = $order['srcMoneyNo1'] = $order['srcInvNo1'] = $inv['billNo'] = $inv['srcOrderNo'] = $money['billNo'] = $money['srcNo'] = $money_info['billNo'] = $v['order_no'];
                    $order['billType'] = $inv['billType'] = 'SALE';
                    $order['transType'] = $money['srcTransType'] = 101201;
                    $inv['transType'] = 102201;
                    $money['billType'] = $money_info['billType'] = 'REC';
                    $money['transType'] = $money_info['transType'] = 103101;
                    $money['miniType'] = $money_info['miniType'] = 1;
                    $order['totalAmount'] = $v['order_amount'];
                    $order['totalDiscount'] = $v['adjust_amount'];
                    $order['totalQty'] = 0;
                    $order['address'] = ($v['province'] ?? '') . ($v['city'] ?? '') . ($v['county'] ?? '');
                    $order['expressCompanyName'] = $v['express_company_name'];
                    $order['expressNo'] = $v['express_no'];
                    $order['goods'] = $inv['goods'] = [];
                    foreach ($v['goods'] as $k1 => $v1) {
                        $order['totalQty'] += intval($v1['qty']);
                        $order['goods'][$k1]['no'] = $v1['goods_no'];
                        $order['goods'][$k1]['name'] = $v1['title'];
                        $order['goods'][$k1]['sku'] = $v1['type'];
                        $order['goods'][$k1]['qty'] = intval($v1['qty']);
                        $order['goods'][$k1]['unit'] = '件';
                        $order['goods'][$k1]['price'] = abs((float)$v1['price']);
                        $order['goods'][$k1]['discount'] = 0;
                        $order['goods'][$k1]['amount'] = abs((float)$v1['amount']);
                        if (!$v1['goods_no'] && strpos($v1['title'], '补差价') === false) {
                            $info = '【' . $comeFrom . '】【' . str_replace('"', '', $v1['title']) . ($v1['type'] ? ' ' . str_replace('"', '', $v1['type']) : '') . '】没有产品编码！';
                            if ($this->db->table('spider_err_goods')->where(['info' => $info, 'status' => 0])->countAllResults() == 0) {
                                $this->db->table('spider_err_goods')->insert(['createTime' => date('Y-m-d H:i:s'), 'info' => $info]);
                            }
                        }
                    }
                    $order['goods'] = $inv['goods'] = json_encode($order['goods'], 320);
                    $order['disRate'] = ((float)$order['totalDiscount'] / $order['totalAmount']);
                    $order['disAmount'] = (float)$order['totalDiscount'];
                    $order['deliverAmount'] = (float)$v['real_freight'];
                    $order['amount'] = $inv['amount'] = $money['amount'] = $money['cleared'] = $money_info['amount'] = (float)$v['order_amount'];
                    $order['moneyStatus1'] = $order['goodsStatus1'] = $inv['goodsStatus'] = $money['moneyStatus'] = 2;
                    $inv['totalOrderQty'] = $inv['totalQty'] = $order['totalQty'];
                    $userInfo = $this->db->table('user')->select('id, name')->where('userName', $v['admin'])->get()->getRowArray();
                    $order['uId'] = $inv['uId'] = $order['checkId'] = $inv['checkId'] = $userInfo ? $userInfo['id'] : 1;
                    $order['uName'] = $inv['uName'] = $order['checkName'] = $inv['checkName'] = $userInfo ? $userInfo['name'] : '系统';
                    $money['uId'] = $money['checkId'] = $money_info['uId'] = $money_info['checkId'] = 1;
                    $money['uName'] = $money['checkName'] = '系统';
                    $order['contactId'] = $inv['contactId'] = $money['contactId'] = $money_info['contactId'] = $contactId ?? 1;
                    $order['contactName'] = $inv['contactName'] = $money['contactName'] = $contactName ?? '默认客户';
                    $order['sId'] = $company['id'];
                    $order['sName'] = $company['name'];
                    $order['description'] = ($v['user_remark'] ? '客户备注：' . $v['user_remark'] . "\r\n" : '') . ($v['admin_remark'] ? '销售备注：' . $v['admin_remark'] . "\r\n" : '');
                    $order['createTime'] = $order['modifyTime'] = $inv['createTime'] = $inv['modifyTime'] = $money['createTime'] = $money['modifyTime'] = $money_info['createTime'] = $money_info['settlementTime'] = date('Y-m-d H:i:s', intval($v['create_time'] / 1000));
                    $money_info['typeId'] = 8;
                    $money_info['accId'] = $companyAccount['id'];
                    $money_info['wayId'] = $v['payment'] == 1 ? 74 : 12;
                    $inv['pusaName'] = '系统';
                    // 插入前多检测一次是否已存在本订单
                    if ($this->db->table('bill_order')->where(['billNo' => $v['order_no'], 'srcMoneyId1 >' => 0, 'isDelete' => 0])->countAllResults() > 0) {
                        continue;
                    }
                    $this->db->table('bill_order')->insert($order);
                    $iid_1 = $this->db->insertID();
                    $this->db->table('bill_inventory')->insert($inv);
                    $iid_2 = $this->db->insertID();
                    $money['srcId'] = $iid_1;
                    $this->db->table('bill_money')->insert($money);
                    $iid_3 = $this->db->insertID();
                    if ($iid_1 && $iid_2 && $iid_3) {
                        $this->db->table('bill_order')->where('billNo', $order['billNo'])->update(['srcMoneyId1' => $iid_3, 'srcInvId1' => $iid_2]);
                        $this->db->table('bill_inventory')->where('billNo', $order['billNo'])->update(['srcOrderId' => $iid_1]);
                        $money_info['iid'] = $iid_3;
                        $this->db->table('bill_money_info')->insert($money_info);
                        foreach ($v['goods'] as $k2 => $v2) {
                            $no = $v2['goods_no'] ?? '';
                            $goods = $this->db->table('goods')->where(['no' => $no, 'isDelete' => 0])->get()->getRowArray() ?? [];
                            $order_info[$k2]['billNo'] = $inv_info[$k2]['billNo'] = $order['billNo'];
                            $order_info[$k2]['billType'] = $inv_info[$k2]['billType'] = $inv_log[$k2]['billType'] = $order['billType'];
                            $order_info[$k2]['transType'] = $inv_log[$k2]['transType'] = $order['transType'];
                            $inv_info[$k2]['transType'] = $inv['transType'];
                            $order_info[$k2]['goodsId'] = $inv_info[$k2]['goodsId'] = $inv_log[$k2]['goodsId'] = $goods['id'] ?? 0;
                            if ('' != $no && 0 == $order_info[$k2]['goodsId'] && strpos($v2['title'], '补差价') === false) {
                                $info = '【' . $comeFrom . '】【' . $no . ' ' . str_replace('"', '', $v2['title']) . ($v2['type'] ? ' ' . str_replace('"', '', $v2['type']) : '') . '】在本系统中没有找到对应的产品！';
                                if ($this->db->table('spider_err_goods')->where(['info' => $info, 'status' => 0])->countAllResults() == 0) {
                                    $this->db->table('spider_err_goods')->insert(['createTime' => date('Y-m-d H:i:s'), 'info' => $info]);
                                }
                            }
                            $order_info[$k2]['storageId'] = $inv_info[$k2]['storageId'] = $inv_log[$k2]['storageId'] = 1;
                            $order_info[$k2]['orderQty'] = $inv_info[$k2]['orderQty'] = $inv_info[$k2]['qty'] = $inv_log[$k2]['qty'] = abs(intval($v2['qty']));
                            $order_info[$k2]['price'] = $inv_info[$k2]['price'] = (float)$v2['price'];
                            $order_info[$k2]['discountRate'] = 0;
                            $order_info[$k2]['discount'] = 0;
                            $order_info[$k2]['amount'] = $inv_info[$k2]['amount'] = (float)$v2['amount'];
                            $order_info[$k2]['uId'] = $order_info[$k2]['checkId'] = $order['uId'];
                            $order_info[$k2]['contactId'] = $order['contactId'];
                            $order_info[$k2]['description'] = $inv_info[$k2]['description'] = (0 == $order_info[$k2]['goodsId'] ? (($no == '' ? '该产品未编码' : '系统没有此编码产品') . '，来源：【' . $comeFrom . '】【' . $v2['title'] . ($v2['type'] ? ' ' . $v2['type'] . '】' : '】')) : '');
                            $order_info[$k2]['billDate'] = $inv_info[$k2]['billDate'] = $order['billDate'];
                            $order_info[$k2]['iid'] = $inv_info[$k2]['srcOrderId'] = $inv_log[$k2]['srcOrderId'] = $iid_1;
                            $inv_info[$k2]['iid'] = $inv_log[$k2]['iid'] = $iid_2;
                            $inv_log[$k2]['uName'] = $inv['uName'];
                            $inv_log[$k2]['locationId'] = 0;
                            $inv_log[$k2]['deliverCompany'] = $order['expressCompanyName'];
                            $inv_log[$k2]['deliverNo'] = $order['expressNo'];
                            $inv_log[$k2]['createTime'] = $order['createTime'];
                            // 处理销售价集合、价差异常值
                            // 分成两个价格体系【设置价默认不加运费系（淘宝、天猫、1688、抖音、官网）、设置价默认加运费系（拼多多）】
                            // 任何产品，在各自体系内相互比价，最高价和最低价相差0.3元判定为价格设置异常
                            // 任何产品，在体系间相互比价，默认加运费系最高价比默认不加运费系最低价高3.8元以上（设置的运费3~3.5元+合理的0.3元的差价）判定为价格设置异常
                            if ($goods) {
                                $shopPrices = $goods['shopPrices'] ? json_decode($goods['shopPrices'], true) : [];
                                $shopPrices[$comeFrom] = (float)$v2['price'];
                                ksort($shopPrices);
                                $shopPriceMinNoaddChannel = $shopPriceMaxNoaddChannel = $shopPriceMinAddChannel = $shopPriceMaxAddChannel = $shopPriceWarning = 0;
                                foreach ($shopPrices as $k3 => $v3) {
                                    if (strpos($k3, 'PDD_') === 0) { // 设置价默认加运费系
                                        if ($shopPriceMinAddChannel == 0 || $shopPriceMaxAddChannel == 0) {
                                            $shopPriceMinAddChannel = $shopPriceMaxAddChannel = (float)$v3;
                                        } else {
                                            if ((float)$v3 < $shopPriceMinAddChannel) {
                                                $shopPriceMinAddChannel = (float)$v3;
                                            } elseif ((float)$v3 > $shopPriceMaxAddChannel) {
                                                $shopPriceMaxAddChannel = (float)$v3;
                                            }
                                        }
                                    } else { // 设置价默认不加运费系
                                        if ($shopPriceMinNoaddChannel == 0 || $shopPriceMaxNoaddChannel == 0) {
                                            $shopPriceMinNoaddChannel = $shopPriceMaxNoaddChannel = (float)$v3;
                                        } else {
                                            if ((float)$v3 < $shopPriceMinNoaddChannel) {
                                                $shopPriceMinNoaddChannel = (float)$v3;
                                            } elseif ((float)$v3 > $shopPriceMaxNoaddChannel) {
                                                $shopPriceMaxNoaddChannel = (float)$v3;
                                            }
                                        }
                                    }
                                }
                                if ($shopPriceMinAddChannel > 0 && $shopPriceMaxAddChannel > 0) {
                                    if (($shopPriceMaxAddChannel - $shopPriceMinAddChannel) > 0.3) {
                                        $shopPriceWarning = 1;
                                        $goodsText = $no . ' ' . str_replace('"', '', $v2['title']) . ($v2['type'] ? ' ' . str_replace('"', '', $v2['type']) : '');
                                        $info = '在【拼多多平台（售价含运费）】比价，相差最大已达 '.round(($shopPriceMaxAddChannel - $shopPriceMinAddChannel), 2).' 元';
                                        if ($this->db->table('spider_err_price')->where([
                                            'goodsId' => $goods['id'],
                                            'shopPrices' => json_encode($shopPrices, 320),
                                            'info' => $info,
                                            'status' => 0
                                        ])->countAllResults() == 0) {
                                            $this->db->table('spider_err_price')->insert([
                                                'goodsId' => $goods['id'],
                                                'goodsText' => $goodsText,
                                                'shopPrices' => json_encode($shopPrices, 320),
                                                'info' => $info,
                                                'createTime' => date('Y-m-d H:i:s')
                                            ]);
                                        }
                                    }
                                }
                                if ($shopPriceMinNoaddChannel > 0 && $shopPriceMaxNoaddChannel > 0) {
                                    if (($shopPriceMaxNoaddChannel - $shopPriceMinNoaddChannel) > 0.3) {
                                        $shopPriceWarning = 1;
                                        $goodsText = $no . ' ' . str_replace('"', '', $v2['title']) . ($v2['type'] ? ' ' . str_replace('"', '', $v2['type']) : '');
                                        $info = '在【淘宝系、抖音、官网平台（售价不含运费）】比价，相差最大已达 '.round(($shopPriceMaxNoaddChannel - $shopPriceMinNoaddChannel), 2).' 元';
                                        if ($this->db->table('spider_err_price')->where([
                                            'goodsId' => $goods['id'],
                                            'shopPrices' => json_encode($shopPrices, 320),
                                            'info' => $info,
                                            'status' => 0
                                        ])->countAllResults() == 0) {
                                            $this->db->table('spider_err_price')->insert([
                                                'goodsId' => $goods['id'],
                                                'goodsText' => $goodsText,
                                                'shopPrices' => json_encode($shopPrices, 320),
                                                'info' => $info,
                                                'createTime' => date('Y-m-d H:i:s')
                                            ]);
                                        }
                                    }
                                }
                                if ($shopPriceMaxAddChannel > 0 && $shopPriceMinNoaddChannel > 0) {
                                    if (($shopPriceMaxAddChannel - $shopPriceMinNoaddChannel) > 3.8) {
                                        $shopPriceWarning = 1;
                                        $goodsText = $no . ' ' . str_replace('"', '', $v2['title']) . ($v2['type'] ? ' ' . str_replace('"', '', $v2['type']) : '');
                                        $info = '在【拼多多平台（售价含运费）】的最高价，比在【淘宝系、抖音、官网平台（售价不含运费）】的最低价，高出已达 '.round(($shopPriceMaxAddChannel - $shopPriceMinNoaddChannel), 2).' 元';
                                        if ($this->db->table('spider_err_price')->where([
                                            'goodsId' => $goods['id'],
                                            'shopPrices' => json_encode($shopPrices, 320),
                                            'info' => $info,
                                            'status' => 0
                                        ])->countAllResults() == 0) {
                                            $this->db->table('spider_err_price')->insert([
                                                'goodsId' => $goods['id'],
                                                'goodsText' => $goodsText,
                                                'shopPrices' => json_encode($shopPrices, 320),
                                                'info' => $info,
                                                'createTime' => date('Y-m-d H:i:s')
                                            ]);
                                        }
                                    }
                                }
                                if ($shopPriceMaxAddChannel > 0 && $shopPriceMinNoaddChannel > 0) {
                                    if (($shopPriceMaxAddChannel - $shopPriceMinNoaddChannel) < 0) {
                                        $shopPriceWarning = 1;
                                        $goodsText = $no . ' ' . str_replace('"', '', $v2['title']) . ($v2['type'] ? ' ' . str_replace('"', '', $v2['type']) : '');
                                        $info = '在【拼多多平台（售价含运费）】的最高价，比在【淘宝系、抖音、官网平台（售价不含运费）】的最低价还低，低出已达 '.round(($shopPriceMaxAddChannel - $shopPriceMinNoaddChannel), 2).' 元';
                                        if ($this->db->table('spider_err_price')->where([
                                            'goodsId' => $goods['id'],
                                            'shopPrices' => json_encode($shopPrices, 320),
                                            'info' => $info,
                                            'status' => 0
                                        ])->countAllResults() == 0) {
                                            $this->db->table('spider_err_price')->insert([
                                                'goodsId' => $goods['id'],
                                                'goodsText' => $goodsText,
                                                'shopPrices' => json_encode($shopPrices, 320),
                                                'info' => $info,
                                                'createTime' => date('Y-m-d H:i:s')
                                            ]);
                                        }
                                    }
                                }
                            } else {
                                $shopPrices = [];
                                $shopPrices[$comeFrom] = (float)$v2['price'];
                                $shopPriceWarning = 0;
                            }
                            // 更新产品信息、处理库存信息
                            if ($goods && in_array($goods['categoryId'], $suitCids_arr)) { // 销售套餐类
                                // 先更新对应产品网店图、销售价集合、价差异常值、本次销售价、本次变动日期
                                $this->db->table('goods')->where('id', $inv_info[$k2]['goodsId'])->update(['salePrice' => (float)$v2['price'], 'billDate' => $order['billDate'], 'shopPrices' => json_encode($shopPrices, 320), 'shopPriceWarning' => $shopPriceWarning]);
                                //如果是销售套餐类产品，则直接操作子产品库存
                                if ($goods['hasSon'] > 0 && strlen($goods['sonGoods']) > 0) {
                                    $sonGoods = (array)json_decode($goods['sonGoods'], true);
                                    foreach ($sonGoods as $arr4 => $row4) {
                                        $this->db->table('goods',)->where('id', $row4['gid'])->set('currentQty', 'currentQty - ' . abs((float)$row4['qty1'] * (float)$row4['qty2'] * (float)$inv_info[$k2]['qty']), false)->set('billDate', $order['billDate'])->update();
                                        $newSonGoods = $this->db->table('goods')->select('currentQty')->where('id', $row4['gid'])->get()->getRowArray();
                                        $invHisData['goodsId'] = $row4['gid'];
                                        $invHisData['billId'] = $iid_2;
                                        $invHisData['billNo'] = $inv['billNo'];
                                        $invHisData['billType'] = 'SA';
                                        $invHisData['transType'] = 102201;
                                        $invHisData['changeType'] = '新增销售发货单(套餐)';
                                        $invHisData['changeTime'] = date('Y-m-d H:i:s');
                                        $invHisData['changeCurrentQty'] = -abs((float)$row4['qty1'] * (float)$row4['qty2'] * (float)$inv_info[$k2]['qty']);
                                        $invHisData['currentQty'] = (float)$newSonGoods['currentQty'];
                                        $invHisInfo = array_intersect_key($invHisData, array_flip(['goodsId', 'billId', 'billNo', 'billType', 'transType', 'changeType', 'changeTime', 'changeCurrentQty', 'currentQty']));
                                        $this->db->table('inv_history')->insert($invHisInfo);
                                    }
                                }
                            } else { // 非销售套餐类，则直接更新对应产品库存、网店图、销售价集合、价差异常值
                                if ($inv_info[$k2]['goodsId'] > 0) {
                                    $this->db->table('goods')->where('id', $inv_info[$k2]['goodsId'])->update(['currentQty' => (float)$goods['currentQty'] - $inv_info[$k2]['qty'], 'salePrice' => (float)$v2['price'], 'billDate' => $order['billDate'], 'shopPrices' => json_encode($shopPrices, 320), 'shopPriceWarning' => $shopPriceWarning]);
                                    $newQty = $this->db->table('goods')->select('currentQty')->where('id', $inv_info[$k2]['goodsId'])->get()->getRowArray();
                                    if (is_array($newQty) && count($newQty) > 0) {
                                        $invHisData['goodsId'] = $inv_info[$k2]['goodsId'];
                                        $invHisData['billId'] = $iid_2;
                                        $invHisData['billNo'] = $inv['billNo'];
                                        $invHisData['billType'] = 'SA';
                                        $invHisData['transType'] = 102201;
                                        $invHisData['changeType'] = '新增销售发货单';
                                        $invHisData['changeTime'] = date('Y-m-d H:i:s');
                                        $invHisData['changeCurrentQty'] = -abs($inv_info[$k2]['qty']);
                                        $invHisData['currentQty'] = (float)$newQty['currentQty'];
                                        $invHisInfo = array_intersect_key($invHisData, array_flip(['goodsId', 'billId', 'billNo', 'billType', 'transType', 'changeType', 'changeTime', 'changeCurrentQty', 'currentQty']));
                                        $this->db->table('inv_history')->insert($invHisInfo);
                                    }
                                }
                            }
                        }
                        $this->db->table('bill_order_info')->insertBatch($order_info);
                        $this->db->table('bill_inventory_info')->insertBatch($inv_info);
                        $this->db->table('bill_inventory_log')->insertBatch($inv_log);
                    }
                    array_push($orderIds, $v['id']);
                }
                $this->db->transComplete();
                if ($this->db->transStatus() === false) {
                    log_message('error', '官网已发货订单同步：事务已回滚！');
                }
                global_curl('https://www.56dz.com/erpapi/order/updatedeliverstatus', [
                    'post' => !0,
                    'postfields' => '{"ids": "' . implode(',', $orderIds) . '"}'
                ]);
            } elseif ($obj && $obj['errno'] != 0) {
                log_message('error', '官网已发货订单同步：未获取到订单列表！');
            }
        } else {
            log_message('error', '官网已发货订单同步：未获取到api数据！');
        }
        $this->db->table('option')->where('optionName', 'salesorder_spider_status')->update(['optionValue' => 0]);
    }
    /* 获取最近60天已退款订单数据并处理 按每1分钟执行一次的标准设计 */
    public function salesorder_closed_spider()
    {
        $this->db->table('option')->where('optionName', 'salesorder_closed_spider_status')->update(['optionValue' => 1]);
        // 淘宝系 退款管理 已打印 + 退款完成（判定为肯定需要退款退货）
        $url = 'https://ltserver.800best.com/bestsmart/refund/v2/get';
        $res = global_curl($url, [
            'post' => 1,
            'postfields' => '{"token":"QJS7cAyad3AZ2759#wihjtF9mG7SAnJ21","dateRange":["' . date('Y-m-d', strtotime("-60 days")) . 'T16:00:00.000Z","' . date('Y-m-d') . 'T15:59:59.999Z"],"reason":"","orderStatus":"","printed":"true","status":"SUCCESS","createBegin":"' . date('Y-m-d', strtotime("-60 days")) . 'T16:00:00.000Z","createEnd":"' . date('Y-m-d') . 'T15:59:59.999Z","pageNumber":1,"objectsPerPage":2000}',
            'httpheader' => [
                'Accept: application/json',
                'Accept-Language: zh-CN,zh;q=0.9,en;q=0.8,en-GB;q=0.7,en-US;q=0.6',
                'Content-Type: application/json',
                'User-Agent: Mozilla/5.0 (Windows NT 6.3; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36 Edg/109.0.1518.140',
                self::LT_TB_WEB_AUTH
            ]
        ]);
        if ($res !== false) {
            $obj = json_decode($res, true);
            $arr = $obj['data']['list'] ?? [];
            if (is_array($arr) && count($arr) > 0) {
                $this->db->transStart();
                foreach ($arr as $k => $v) {
                    // 我们目前没开京东店铺，当前灵通打的京东订单为帮代理代发货，不同步订单信息到管理平台，只同步物流单信息即可。
                    if (substr($v['sellerNick'], 0, 3) == 'JD#') {
                        continue;
                    }
                    // 处理退款：没有对应退款编号的退款信息
                    if ($this->db->table('bill_money_info')->where(['billNo' => $v['tradeId'], 'transType' => 103102, 'refundId' => $v['refundId'], 'isDelete' => 0])->countAllResults() == 0) {
                        // 获取退款单信息
                        $money = $this->db->table('bill_money')->where('billNo = "' . $v['tradeId'] . '" AND transType = 103102 AND isDelete = 0')->get()->getRowArray();
                        if ($money) {
                            // 已有退款单
                            $order = $this->db->table('bill_order')->where('billNo', $money['srcNo'])->get()->getRowArray();
                            if ((float)$money['cleared'] + (float)$v['refundFee'] > ((float)$order['amount'] + 3)) {
                                continue; // 如果本次退款金额 + 已退金额 > 销售订单金额 + 3元运费，则跳过，避免出现重复退款
                            }
                            $money_info = [];
                            $money_info['iid'] = $money['id'];
                            $money_info['billNo'] = $money['billNo'];
                            $money_info['billDate'] = $money['billDate'];
                            $money_info['billType'] = $money['billType'];
                            $money_info['transType'] = $money['transType'];
                            $money_info['miniType'] = $money['miniType'];
                            $money_info['amount'] = $v['refundFee'];
                            $money_info['contactId'] = $money['contactId'];
                            $money_info['typeId'] = 284;
                            $money_info['accId'] = 1;
                            $money_info['wayId'] = 12;
                            $money_info['uId'] = $money['uId'];
                            $money_info['checkId'] = $money['checkId'];
                            $money_info['refundId'] = $v['refundId'];
                            $money_info['settlementTime'] = $money_info['createTime'] = date('Y-m-d H:i:s');
                            $money_info['description'] = '退款编号：' . $v['refundId'] . '，退款原因：' . $v['reason'];
                            if ($this->db->table('bill_money_info')->where(['billNo' => $money['billNo'],'transType' => 103102, 'refundId' => '', 'amount' => floatval($v['refundFee']), 'isDelete' => 0])->countAllResults() > 0) {
                                $this->db->table('bill_money_info')->where(['billNo' => $money['billNo'], 'transType' => 103102, 'refundId' => '', 'amount' => floatval($v['refundFee']), 'isDelete' => 0])->update($money_info);
                            } else {
                                $this->db->table('bill_money_info')->insert($money_info);
                            }
                            $totalAmount = $this->db->table('bill_money_info')->select('SUM(amount) AS totalAmount')->where(['iid' => $money['id'], 'isDelete' => 0])->get()->getRowArray()['totalAmount'] ?? 0;
                            $this->db->table('bill_money')->where('id', $money['id'])->update(['moneyStatus' => 2, 'amount' => $totalAmount, 'cleared' => $totalAmount]);
                        } else { // 没有退款单
                            // 获取订单信息
                            $order = $this->db->table('bill_order')->where(['billNo' => $v['tradeId'], 'transType' => 101201, 'isDelete' => 0])->get()->getRowArray();
                            if ($order) {
                                $money = [];
                                $money['billDate'] = date('Y-m-d');
                                $money['billNo'] = $v['tradeId'];
                                $money['billType'] = 'PAY';
                                $money['transType'] = 103102;
                                $money['miniType'] = 1;
                                $money['srcTransType'] = 101201;
                                $money['srcId'] = $order['id'];
                                $money['srcNo'] = $v['tradeId'];
                                $money['amount'] = $money['cleared'] = $v['refundFee'];
                                $money['moneyStatus'] = 2;
                                $money['uId'] = $money['checkId'] = $order['uId'];
                                $money['uName'] = $money['checkName'] = $order['uName'];
                                $money['contactId'] = $order['contactId'];
                                $money['contactName'] = $order['contactName'];
                                $money['files'] = $money['description'] = '';
                                $money['createTime'] = $money['modifyTime'] = date('Y-m-d H:i:s', $v['modified'] / 1000);
                                $this->db->table('bill_money')->insert($money);
                                $srcMoneyId2 = $this->db->insertID();
                                if ($srcMoneyId2) {
                                    $money_info['iid'] = $srcMoneyId2;
                                    $money_info['billNo'] = $money['billNo'];
                                    $money_info['billDate'] = $money['billDate'];
                                    $money_info['billType'] = $money['billType'];
                                    $money_info['transType'] = $money['transType'];
                                    $money_info['miniType'] = $money['miniType'];
                                    $money_info['amount'] = $v['refundFee'];
                                    $money_info['contactId'] = $money['contactId'];
                                    $money_info['typeId'] = 284;
                                    $money_info['accId'] = 1;
                                    $money_info['wayId'] = 12;
                                    $money_info['uId'] = $money['uId'];
                                    $money_info['checkId'] = $money['checkId'];
                                    $money_info['refundId'] = $v['refundId'];
                                    $money_info['settlementTime'] = $money_info['createTime'] = date('Y-m-d H:i:s');
                                    $money_info['description'] = '退款编号：' . $v['refundId'] . '，退款原因：' . $v['reason'];
                                    $this->db->table('bill_money_info')->insert($money_info);
                                    $this->db->table('bill_order')->where('billNo', $v['tradeId'])->update(['moneyStatus2' => 2, 'srcMoneyId2' => $srcMoneyId2, 'srcMoneyNo2' => $v['tradeId']]);
                                }
                            }
                        }
                    }
                    // 处理退货：没有对应退款编号的退货信息 且已经发过货
                    if ($this->db->table('bill_inventory_info')->where(['billNo' => $v['tradeId'], 'transType' => 102202, 'refundId' => $v['refundId'], 'isDelete' => 0])->countAllResults() == 0 && $this->db->table('bill_inventory')->where(['billNo' => $v['tradeId'], 'transType' => 102201, 'isDelete' => 0])->countAllResults() > 0) {
                        // 获取发货单信息
                        $inv_send = $this->db->table('bill_inventory')->where(['billNo' => $v['tradeId'], 'transType' => 102201, 'isDelete' => 0])->get()->getRowArray();
                        $inv_info_send = $this->db->table('bill_inventory_info')->where(['billNo' => $v['tradeId'], 'transType' => 102201, 'isDelete' => 0])->get()->getResultArray();
                        // 获取退货单
                        $inv_back = $this->db->table('bill_inventory')->where(['billNo' => $v['tradeId'], 'transType' => 102202, 'isDelete' => 0])->get()->getRowArray();
                        if ($inv_back) { // 已有退货单
                            $inv_info_back = $this->db->table('bill_inventory_info')->where(['billNo' => $v['tradeId'], 'transType' => 102202, 'refundId' => $v['refundId'], 'isDelete' => 0])->get()->getResultArray();
                            $needAdd = true;
                            foreach ($inv_info_back as $k1 => $v1) {
                                if ($v1['amount'] == $v['refundFee'] && $v1['refundId'] == '') {
                                    $this->db->table('bill_inventory_info')->where('refundId', $v['refundId'])->update(['id' => $v1['id']]);
                                    $needAdd = false;
                                    break;
                                }
                            }
                            if ($needAdd) {
                                $inv_info_back_data = [];
                                foreach ($inv_info_send as $k2 => $v2) {
                                    if ($v2['amount'] == $v['refundFee']) {
                                        $inv_info_back_data = $v2;
                                        unset($inv_info_back_data['id']);
                                        $inv_info_back_data['iid'] = $inv_back['id'];
                                        $inv_info_back_data['billDate'] = $inv_back['billDate'];
                                        $inv_info_back_data['transType'] = $inv_back['transType'];
                                        $inv_info_back_data['refundId'] = $v['refundId'];
                                        $inv_info_back_data['description'] = '退款编号：' . $v['refundId'] . '，退货原因：' . $v['reason'];
                                        $this->db->table('bill_inventory_info')->insert($inv_info_back_data);
                                        $totalInfo = $this->db->table('bill_inventory_info')->select('SUM(qty) AS qty, SUM(amount) AS amount')->where(['iid' => $inv_back['id'], 'isDelete' => 0])->get()->getRowArray() ?? [];
                                        $this->db->table('bill_inventory')->where('id', $inv_back['id'])->update([
                                            'totalOrderQty' => $totalInfo['qty'] ?? 0,
                                            'totalQty' => $totalInfo['qty'] ?? 0,
                                            'amount' => $totalInfo['amount'] ?? 0,
                                            'goodsStatus' => 2
                                        ]);
                                        $this->db->table('bill_order')->where('billNo', $v['tradeId'])->update(['goodsStatus2' => 2]);
                                        // 更新产品库存、最新活跃日期
                                        $this->db->table('goods')->where('id', $inv_info_back_data['goodsId'])->set('currentQty', 'currentQty + ' . $inv_info_back_data['qty'], false)->set('billDate', $inv_info_back_data['billDate'])->update();
                                        // 记录库存流水
                                        $inv_log = $this->db->table('bill_inventory_log')->where(['iid' => $v2['iid'], 'goodsId' => $v2['goodsId'], 'qty' => $v2['qty'], 'isDelete' => 0])->get()->getRowArray();
                                        if ($inv_log) {
                                            unset($inv_log['id']);
                                            $inv_log['iid'] = $inv_back['id'];
                                            $inv_log['transType'] = 102202;
                                            $inv_log['createTime'] = date('Y-m-d H:i:s');
                                            $this->db->table('bill_inventory_log')->insert($inv_log);
                                        }
                                    }
                                }
                            }
                        } else { // 无退货单
                            $inv_info_back_data = [];
                            foreach ($inv_info_send as $k2 => $v2) {
                                if ($v2['amount'] == $v['refundFee']) {
                                    $inv_info_back_data = $v2;
                                    break;
                                }
                            }
                            if (count($inv_info_back_data) > 0) {
                                $inv_back = $inv_send;
                                unset($inv_back['id']);
                                $inv_back['billDate'] = date('Y-m-d');
                                $inv_back['transType'] = 102202;
                                $inv_back['files'] = '';
                                $inv_back['description'] = '退货原因：' . $v['reason'];
                                $inv_back['createTime'] = $inv_back['modifyTime'] = date('Y-m-d H:i:s', $v['modified'] / 1000);
                                $this->db->table('bill_inventory')->insert($inv_back);
                                $srcInvId2 = $this->db->insertID();
                                if ($srcInvId2) {
                                    unset($inv_info_back_data['id']);
                                    $inv_info_back_data['iid'] = $srcInvId2;
                                    $inv_info_back_data['billDate'] = $inv_back['billDate'];
                                    $inv_info_back_data['transType'] = $inv_back['transType'];
                                    $inv_info_back_data['refundId'] = $v['refundId'];
                                    $inv_info_back_data['description'] = '退款编号：' . $v['refundId'] . '，退货原因：' . $v['reason'];
                                    $this->db->table('bill_inventory_info')->insert($inv_info_back_data);
                                    $this->db->table('bill_inventory')->where('id', $srcInvId2)->update([
                                        'totalOrderQty' => $inv_info_back_data['qty'],
                                        'totalQty' => $inv_info_back_data['qty'],
                                        'amount' => $inv_info_back_data['amount'],
                                        'goodsStatus' => 2
                                    ]);
                                    $this->db->table('bill_order')->where('billNo', $v['tradeId'])->update(['goodsStatus2' => 2, 'srcInvId2' => $srcInvId2, 'srcInvNo2' => $v['tradeId']]);
                                    //更新产品库存、最新活跃日期
                                    $this->db->table('goods')->where('id', $inv_info_back_data['goodsId'])->set('currentQty', 'currentQty + ' . $inv_info_back_data['qty'], false)->set('billDate', $inv_info_back_data['billDate'])->update();
                                    //记录库存流水
                                    $inv_log = $this->db->table('bill_inventory_log')->where(['iid' => $inv_send['id'], 'goodsId' => $inv_info_back_data['goodsId'], 'qty' => $inv_info_back_data['qty'], 'isDelete' => 0])->get()->getRowArray();
                                    if ($inv_log) {
                                        unset($inv_log['id']);
                                        $inv_log['iid'] = $srcInvId2;
                                        $inv_log['transType'] = 102202;
                                        $inv_log['createTime'] = date('Y-m-d H:i:s');
                                        $this->db->table('bill_inventory_log')->insert($inv_log);
                                    }
                                }
                            }
                        }
                    }
                }
                $this->db->transComplete();
                if ($this->db->transStatus() === false) {
                    log_message('error', '灵通已退款订单同步：事务已回滚（淘宝系）！');
                }
            } else {
                log_message('error', '灵通已退款订单同步：未获取到列表（淘宝系）！');
            }
        } else {
            log_message('error', '灵通已退款订单同步：未获取到api数据（淘宝系）！');
        }
        // 淘宝系 交易关闭的已发货订单 （判定为肯定需要退款退货）
        $url = 'https://ltserver.800best.com/bestsmart/tradeHist/v3/readySends';
        $res = global_curl($url, [
            'post' => 1,
            'postfields' => '{"consignFrom":"' . date('Y-m-d', strtotime("-60 days")) . 'T16:00:00.000Z","consignTo":"' . date('Y-m-d') . 'T15:59:59.999Z","pageNumber":1,"objectsPerPage":5000,"status":"TRADE_CLOSED","routers":["12345678hyq","和成聚科技公司","五六电子网店","的确良电子0148","tb521980958","伍陆电子旗舰","1688#12447910","DY#12589013","JD#12928741"],"userIds":[11423893,11592964,11592981,12309001,12447942,12896229,12447910,12589013,12928741],"sellerNicks":["12345678hyq","和成聚科技公司","五六电子网店","的确良电子0148","tb521980958","伍陆电子旗舰","1688#cqsbx","DY#102595011","JD#伍陆电子旗舰店"]}',
            'httpheader' => [
                'accept: application/json',
                'accept-language: zh-CN,zh;q=0.9',
                'content-type: application/json',
                self::LT_TB_WEB_AUTH
            ]
        ]);
        if ($res !== false) {
            $obj = json_decode($res, true);
            $arr = $obj['data']['hits'] ?? [];
            if (is_array($arr) && count($arr) > 0) {
                $this->db->transStart();
                foreach ($arr as $k => $v) {
                    // 我们目前没开京东店铺，当前灵通打的京东订单为帮代理代发货，不同步订单信息到管理平台，只同步物流单信息即可。
                    if ($v['orderSource'] == 'JD') {
                        continue;
                    }
                    // 获取订单信息
                    $order = $this->db->table('bill_order')->where(['billNo' => $v['tradeId'], 'transType' => 101201, 'isDelete' => 0])->get()->getRowArray();
                    if ($order) {
                        // 处理退款
                        if ($this->db->table('bill_money')->where(['billNo' => $v['tradeId'], 'transType' => 103102, 'isDelete' => 0])->countAllResults() == 0) {
                            $money = [];
                            $money['billDate'] = date('Y-m-d');
                            $money['billNo'] = $v['tradeId'];
                            $money['billType'] = 'PAY';
                            $money['transType'] = 103102;
                            $money['miniType'] = 1;
                            $money['srcTransType'] = 101201;
                            $money['srcId'] = $order['id'];
                            $money['srcNo'] = $v['tradeId'];
                            $money['amount'] = $money['cleared'] = $order['amount'];
                            $money['moneyStatus'] = 2;
                            $money['uId'] = $money['checkId'] = $order['uId'];
                            $money['uName'] = $money['checkName'] = $order['uName'];
                            $money['contactId'] = $order['contactId'];
                            $money['contactName'] = $order['contactName'];
                            $money['files'] = $money['description'] = '';
                            $money['createTime'] = $money['modifyTime'] = date('Y-m-d H:i:s');
                            $this->db->table('bill_money')->insert($money);
                            $srcMoneyId2 = $this->db->insertID();
                            if ($srcMoneyId2) {
                                $money_info['iid'] = $srcMoneyId2;
                                $money_info['billNo'] = $money['billNo'];
                                $money_info['billDate'] = $money['billDate'];
                                $money_info['billType'] = $money['billType'];
                                $money_info['transType'] = $money['transType'];
                                $money_info['miniType'] = $money['miniType'];
                                $money_info['amount'] = $money['amount'];
                                $money_info['contactId'] = $money['contactId'];
                                $money_info['typeId'] = 284;
                                $money_info['accId'] = 1;
                                $money_info['wayId'] = 12;
                                $money_info['uId'] = $money['uId'];
                                $money_info['checkId'] = $money['checkId'];
                                $money_info['refundId'] = '';
                                $money_info['settlementTime'] = $money_info['createTime'] = date('Y-m-d H:i:s');
                                $money_info['description'] = '';
                                $this->db->table('bill_money_info')->insert($money_info);
                                $this->db->table('bill_order')->where('billNo', $v['tradeId'])->update(['moneyStatus2' => 2, 'srcMoneyId2' => $srcMoneyId2, 'srcMoneyNo2' => $v['tradeId']]);
                            }
                        }
                        // 处理退货
                        if ($this->db->table('bill_inventory')->where(['billNo' => $v['tradeId'], 'transType' => 102201, 'isDelete' => 0])->countAllResults() > 0 && $this->db->table('bill_inventory')->where(['billNo' => $v['tradeId'], 'transType' => 102202, 'isDelete' => 0])->countAllResults() == 0) {
                            // 获取发货单信息
                            $inv_send = $this->db->table('bill_inventory')->where(['billNo' => $v['tradeId'], 'transType' => 102201,'isDelete' => 0])->get()->getRowArray();
                            $inv_info_send = $this->db->table('bill_inventory_info')->where(['billNo' => $v['tradeId'], 'transType' => 102201, 'isDelete' => 0])->get()->getResultArray();
                            if (count($inv_info_send) > 0) {
                                $inv_back = $inv_send;
                                unset($inv_back['id']);
                                $inv_back['billDate'] = date('Y-m-d');
                                $inv_back['transType'] = 102202;
                                $inv_back['files'] = '';
                                $inv_back['description'] = '';
                                $inv_back['createTime'] = $inv_back['modifyTime'] = date('Y-m-d H:i:s');
                                $this->db->table('bill_inventory')->insert($inv_back);
                                $srcInvId2 = $this->db->insertID();
                                if ($srcInvId2) {
                                    foreach ($inv_info_send as $k2 => $v2) {
                                        $inv_info_back_data = [];
                                        $inv_info_back_data['iid'] = $srcInvId2;
                                        $inv_info_back_data['billDate'] = $inv_back['billDate'];
                                        $inv_info_back_data['billType'] = $inv_back['billType'];
                                        $inv_info_back_data['transType'] = $inv_back['transType'];
                                        $inv_info_back_data['goodsId'] = $v2['goodsId'];
                                        $inv_info_back_data['storageId'] = $v2['storageId'];
                                        $inv_info_back_data['orderQty'] = $v2['orderQty'];
                                        $inv_info_back_data['qty'] = $v2['qty'];
                                        $inv_info_back_data['price'] = $v2['price'];
                                        $inv_info_back_data['amount'] = $v2['amount'];
                                        $inv_info_back_data['srcOrderId'] = $v2['srcOrderId'];
                                        $this->db->table('bill_inventory_info')->insert($inv_info_back_data);
                                        //更新产品库存、最新活跃日期
                                        $this->db->table('goods')->where('id', $inv_info_back_data['goodsId'])->set('currentQty', 'currentQty + ' . $inv_info_back_data['qty'], false)->set('billDate', $inv_info_back_data['billDate'])->update();
                                        //记录库存流水
                                        $inv_log = $this->db->table('bill_inventory_log')->where(['iid' => $inv_send['id'], 'goodsId' => $inv_info_back_data['goodsId'], 'qty' => $inv_info_back_data['qty'], 'isDelete' => 0])->get()->getRowArray();
                                        if ($inv_log) {
                                            unset($inv_log['id']);
                                            $inv_log['iid'] = $srcInvId2;
                                            $inv_log['transType'] = 102202;
                                            $inv_log['createTime'] = date('Y-m-d H:i:s');
                                            $this->db->table('bill_inventory_log')->insert($inv_log);
                                        }
                                    }
                                    $this->db->table('bill_order')->where('billNo', $v['tradeId'])->update(['goodsStatus2' => 2, 'srcInvId2' => $srcInvId2, 'srcInvNo2' => $v['tradeId'], 'isCancel' => 1]);
                                }
                            }
                        }
                    }
                }
                $this->db->transComplete();
                if ($this->db->transStatus() === false) {
                    log_message('error', '灵通已关闭订单同步：事务已回滚（淘宝系）！');
                }
            } else {
                log_message('error', '灵通已关闭订单同步：未获取到订单列表（淘宝系）！');
            }
        } else {
            log_message('error', '灵通已关闭订单同步：未获取到api数据（淘宝系）！');
        }
        // 拼多多 售后订单 已打印 + 已回收（判定为肯定需要退款，至于退货，会在面单同步方法中删除发货单并作废订单，这里就不再处理退货）
        $url = 'https://ltserver-pdd.800best.com/bestsmart/refund/getRefundOrder';
        $res = global_curl($url, [
            'post' => 1,
            'postfields' => '{"pageNumber":1,"objectsPerPage":2000,"billType":0,"printStatus":true,"modifiedStartTime":"' . date('Y-m-d', strtotime("-60 days")) . 'T16:00:00.000Z","modifiedEndTime":"' . date('Y-m-d') . 'T15:59:59.999Z","canceled":1,"expressNo":"","tid":"","receiverInfo":"","refundStatus":"","userIds":[31037236,31186155,31234312,31307563],"uids":[31037236,31186155,31234312,31307563]}',
            'httpheader' => [
                'Accept: application/json',
                'Accept-Language: zh-CN,zh;q=0.9,en;q=0.8,en-GB;q=0.7,en-US;q=0.6',
                'Content-Type: application/json',
                'User-Agent: Mozilla/5.0 (Windows NT 6.3; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36 Edg/109.0.1518.140',
                self::LT_PDD_WEB_AUTH
            ]
        ]);
        if ($res !== false) {
            $obj = json_decode($res, true);
            $arr = $obj['data']['list'] ?? [];
            if (is_array($arr) && count($arr) > 0) {
                $this->db->transStart();
                foreach ($arr as $k => $v) {
                    if ($v['billType'] > 2) { //只采纳仅退款、退货退款售后单进行处理
                        continue;
                    }
                    // 仅处理退款：没有对应退款编号的退款信息
                    if ($this->db->table('bill_money_info')->where(['billNo' => $v['tid'], 'transType' => 103102, 'refundId' => $v['refundId'], 'isDelete' => 0])->countAllResults() == 0) {
                        // 获取退款单信息
                        $money = $this->db->table('bill_money')->where(['billNo' => $v['tid'], 'transType' => 103102, 'isDelete' => 0])->get()->getRowArray();
                        if ($money) { // 已有退款单
                            $order = $this->db->table('bill_order')->where('billNo', $money['srcNo'])->get()->getRowArray();
                            if ((float)$money['cleared'] + (float)bcdiv($v['refundFee'], 100, 2) > ((float)$order['amount'] + 3)) {
                                continue; // 如果本次退款金额 + 已退金额 > 销售订单金额 + 3元运费，则跳过，避免出现重复退款
                            }
                            $money_info = [];
                            $money_info['iid'] = $money['id'];
                            $money_info['billNo'] = $money['billNo'];
                            $money_info['billDate'] = $money['billDate'];
                            $money_info['billType'] = $money['billType'];
                            $money_info['transType'] = $money['transType'];
                            $money_info['miniType'] = $money['miniType'];
                            $money_info['amount'] = bcdiv($v['refundFee'], 100, 2);
                            $money_info['contactId'] = $money['contactId'];
                            $money_info['typeId'] = 284;
                            $money_info['accId'] = 1;
                            $money_info['wayId'] = 12;
                            $money_info['uId'] = $money['uId'];
                            $money_info['checkId'] = $money['checkId'];
                            $money_info['refundId'] = $v['refundId'];
                            $money_info['settlementTime'] = $money_info['createTime'] = date('Y-m-d H:i:s');
                            $money_info['description'] = '退款编号：' . $v['refundId'];
                            if ($this->db->table('bill_money_info')->where(['billNo' => $money['billNo'], 'transType' => 103102, 'refundId' => '', 'amount' => floatval(bcdiv($v['refundFee'], 100, 2)), 'isDelete' => 0])->countAllResults() > 0) {
                                $this->db->table('bill_money_info')->where(['billNo' => $money['billNo'], 'transType' => 103102, 'refundId' => '', 'amount' => floatval(bcdiv($v['refundFee'], 100, 2)), 'isDelete' => 0])->update($money_info);
                            } else {
                                $this->db->table('bill_money_info')->insert($money_info);
                            }
                            $totalAmount = $this->db->table('bill_money_info')->select('SUM(amount) AS totalAmount')->where(['iid' => $money['id'], 'isDelete' => 0])->get()->getRowArray()['totalAmount'] ?? 0;
                            $this->db->table('bill_money')->where('id', $money['id'])->update(['moneyStatus' => 2, 'amount' => $totalAmount, 'cleared' => $totalAmount]);
                        } else { // 没有退款单
                            // 获取订单信息
                            $order = $this->db->table('bill_order')->where(['billNo' => $v['tid'], 'transType' => 101201, 'isDelete' => 0])->get()->getRowArray();
                            if ($order) {
                                $money = [];
                                $money['billDate'] = date('Y-m-d');
                                $money['billNo'] = $v['tid'];
                                $money['billType'] = 'PAY';
                                $money['transType'] = 103102;
                                $money['miniType'] = 1;
                                $money['srcTransType'] = 101201;
                                $money['srcId'] = $order['id'];
                                $money['srcNo'] = $v['tid'];
                                $money['amount'] = $money['cleared'] = bcdiv($v['refundFee'], 100, 2);
                                $money['moneyStatus'] = 2;
                                $money['uId'] = $money['checkId'] = $order['uId'];
                                $money['uName'] = $money['checkName'] = $order['uName'];
                                $money['contactId'] = $order['contactId'];
                                $money['contactName'] = $order['contactName'];
                                $money['files'] = $money['description'] = '';
                                $money['createTime'] = $money['modifyTime'] = date('Y-m-d H:i:s', $v['modified'] / 1000);
                                $this->db->table('bill_money')->insert($money);
                                $srcMoneyId2 = $this->db->insertID();
                                if ($srcMoneyId2) {
                                    $money_info['iid'] = $srcMoneyId2;
                                    $money_info['billNo'] = $money['billNo'];
                                    $money_info['billDate'] = $money['billDate'];
                                    $money_info['billType'] = $money['billType'];
                                    $money_info['transType'] = $money['transType'];
                                    $money_info['miniType'] = $money['miniType'];
                                    $money_info['amount'] = bcdiv($v['refundFee'], 100, 2);
                                    $money_info['contactId'] = $money['contactId'];
                                    $money_info['typeId'] = 284;
                                    $money_info['accId'] = 1;
                                    $money_info['wayId'] = 12;
                                    $money_info['uId'] = $money['uId'];
                                    $money_info['checkId'] = $money['checkId'];
                                    $money_info['refundId'] = $v['refundId'];
                                    $money_info['settlementTime'] = $money_info['createTime'] = date('Y-m-d H:i:s');
                                    $money_info['description'] = '退款编号：' . $v['refundId'];
                                    $this->db->table('bill_money_info')->insert($money_info);
                                    $this->db->table('bill_order')->where('billNo', $v['tid'])->update(['moneyStatus2' => 2, 'srcMoneyId2' => $srcMoneyId2, 'srcMoneyNo2' => $v['tid']]);
                                }
                            }
                        }
                    }
                }
                $this->db->transComplete();
                if ($this->db->transStatus() === false) {
                    log_message('error', '灵通已退款订单同步：事务已回滚（拼多多）！');
                }
            } else {
                log_message('error', '灵通已退款订单同步：未获取到列表（拼多多）！');
            }
        } else {
            log_message('error', '灵通已退款订单同步：未获取到api数据（拼多多）！');
        }
        // 店小秘
        $url = 'https://www.dianxiaomi.com/api/package/list.json';
        $res = global_curl($url, [
            'post' => 1,
            'postfields' => 'pageNo=1&pageSize=100&shopId=-1&state=refound&platform=&isSearch=1&searchType=orderId&authId=-1&startTime=&endTime=&country=&orderField=refunded_time&isVoided=0&isRemoved=0&ruleId=-1&sysRule=&applyType=&applyStatus=&printJh=-1&printMd=-1&commitPlatform=&productStatus=&jhComment=-1&storageId=0&isOversea=-1&isFree=-1&isBatch=-1&history=&custom=-1&timeOut=0&refundStatus=0&buyerAccount=&forbiddenStatus=-1&forbiddenReason=0&behindTrack=-1&orderId=&axios_cancelToken=true',
            'httpheader' => [
                'Accept: application/json, text/plain, */*',
                'Accept-Encoding: gzip, deflate, br',
                'Accept-Language: zh-CN,zh;q=0.9',
                'Bx-V: 2.5.11',
                'Cache-Control: no-cache',
                'Content-Length: 469',
                'Content-Type: application/x-www-form-urlencoded',
                self::DXM_COOKIE,
                'Origin: https://www.dianxiaomi.com',
                'Pragma: no-cache',
                'Referer: https://www.dianxiaomi.com/web/order/refound?go=m106',
                'User-Agent: Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/116.0.5845.97 Safari/537.36 Core/1.116.567.400 QQBrowser/19.7.6764.400'
            ]
        ]);
        if ($res !== false) {
            $obj = json_decode($res, true);
            $obj = $obj['data']['page']['list'] ?? [];
            $count = (is_countable($obj) && count($obj) > 0) ? count($obj[0]) : 0;
            if ($count > 0) {
                $this->db->transStart();
                foreach ($obj as $k => $v) {
                    // 无收款单或已有退款单，跳出本次循环
                    if (
                        $this->db->table('bill_money')->where(['billNo' => $v['orderId'], 'transType' => 103101, 'isDelete' => 0])->countAllResults() < 1 ||
                        $this->db->table('bill_money')->where(['billNo' => $v['orderId'], 'transType' => 103102, 'isDelete' => 0])->countAllResults() > 0
                    ) {
                        continue;
                    }
                    // 直接提取收款单信息，重新组装后，生成退款单信息，录入后更新订单字段
                    $money = $this->db->table('bill_money')->where(['billNo' => $v['orderId'], 'transType' => 103101, 'isDelete' => 0])->get()->getRowArray();
                    if ($money) {
                        $moneyId = $money['id'];
                        unset($money['id']);
                        $money['billDate'] = date('Y-m-d');
                        $money['billType'] = 'PAY';
                        $money['transType'] = 103102;
                        $money['miniType'] = 1;
                        $money['createTime'] = $money['modifyTime'] = date('Y-m-d H:i:s');
                        $this->db->table('bill_money')->insert($money);
                        $srcMoneyId2 = $this->db->insertID();
                        if ($srcMoneyId2) {
                            $money_info = $this->db->table('bill_money_info')->where(['iid' => $moneyId, 'isDelete' => 0])->get()->getResultArray();
                            if (! empty($money_info)) {
                                foreach ($money_info as $k1 => $v1) {
                                    unset($money_info[$k1]['id']);
                                    $money_info[$k1]['iid'] = $srcMoneyId2;
                                    $money_info[$k1]['billDate'] = date('Y-m-d');
                                    $money_info[$k1]['billType'] = 'PAY';
                                    $money_info[$k1]['transType'] = 103102;
                                    $money_info[$k1]['miniType'] = 1;
                                    $money_info[$k1]['settlementTime'] = $money_info[$k1]['createTime'] = date('Y-m-d H:i:s');
                                    $money_info[$k1]['typeId'] = 284;
                                    $money_info[$k1]['accId'] = 3;
                                    $money_info[$k1]['wayId'] = 12;
                                }
                            }
                            $this->db->table('bill_money_info')->insertBatch($money_info);
                            $this->db->table('bill_order')->where('billNo', $v['orderId'])->update(['moneyStatus2' => 2, 'srcMoneyId2' => $srcMoneyId2, 'srcMoneyNo2' => $v['orderId']]);
                        }
                    }
                }
                $this->db->transComplete();
                if ($this->db->transStatus() === false) {
                    log_message('error', '店小秘已退款订单同步：事务已回滚！');
                }
            } else {
                log_message('error', '店小秘已退款订单同步：未匹配到数据！');
            }
        } else {
            log_message('error', '店小秘已退款订单同步：未获取到网页！');
        }
        // 官网（暂未开发退款功能）
        $this->db->table('option')->where('optionName', 'salesorder_closed_spider_status')->update(['optionValue' => 0]);
    }
    /* 获取最近30天已打印运单号并处理 按每1分钟执行一次的标准设计 */
    public function deliverbag_spider($n = 1)
    {
        $this->db->table('option')->where('optionName', 'deliverbag_spider_status')->update(['optionValue' => 1]);
        //先从网店账号表提取员工对应关系
        $shopAccountList = $this->db->table('shop_account')->select('name, uName')->where('isDelete', 0)->get()->getResultArray();
        $shopAccountArr = [];
        if (is_countable($shopAccountList) && count($shopAccountList) > 0) {
            foreach ($shopAccountList as $k => $v) {
                $shopAccountArr[$v['name']] = $v['uName'];
            }
        }
        //淘宝系（包含自定义打单）
        $url = 'https://ltserver.800best.com/bestsmart/eno/v3/get';
        $res = global_curl($url, [
            'post' => 1,
            'postfields' => '{"token":"","expressNo":"","tradeId":"","keyWord":"","creator":"","enousedDate":["' . date('Y-m-d', strtotime("-30 days")) . 'T16:00:00.000Z","' . date('Y-m-d') . 'T15:59:59.999Z"],"cancel":"","senderKey":"","goodsInfo":"","packageCode":"","company":"","branchCode":"","chType":"","type":"","status":"","printCountType":"","begin":"' . date('Y-m-d', strtotime("-30 days")) . 'T16:00:00.000Z","end":"' . date('Y-m-d') . 'T15:59:59.999Z","datePickType":"shortTime","pageNumber":1,"objectsPerPage":10000}',
            'httpheader' => [
                'accept: application/json',
                'accept-language: zh-CN,zh;q=0.9',
                'content-type: application/json',
                self::LT_TB_WEB_AUTH
            ]
        ]);
        if ($res !== false) {
            $obj = json_decode($res, true);
            $arr = $obj['data']['list'] ?? [];
            if (is_array($arr) && count($arr) > 0) {
                $this->db->transStart();
                foreach ($arr as $k => $v) {
                    // 先处理已回收运单号对应的销售订单【容易误处理，暂停本段代码执行】
                    /* if ($v['canceled'] && strlen($v['orderIds']) > 0) { //已回收 且有对应订单号 则判定实际未发货
                        $orderIds = explode(',', $v['orderIds']);
                        foreach ($orderIds as $index => $billNo) {
                            $order = $this->db->table('bill_order')->where(['billNo' => $billNo, 'billType' => 'SALE', 'isDelete' => 0])->get()->getRowArray();
                            if ($order && $order['srcInvId1'] > 0 && $order['srcInvId2'] == 0 && $order['srcMoneyId1'] > 0 && $order['srcMoneyId2'] > 0 && $order['isCancel'] == 0) { //如果订单存在于ERP系统、且已发货、未退货、已收款、已退款、未作废，才将自动生成的发货单删除、恢复库存、并将状态设置为作废、提醒事项不再将作废订单计入在内
                                $inventory = $this->db->table('bill_inventory')->where(['id' => $order['srcInvId1'], 'billType' => 'SALE', 'isDelete' => 0])->get()->getRowArray();
                                if ($inventory) {
                                    $suitCids_arr = array_column($this->db->table('category')->select('id')->where('FIND_IN_SET("280", path) >', 0, false)->where('isDelete', 0)->get()->getResultArray(), 'id'); //取出套餐类id数组备用
                                    $inventory_info = $this->db->table('bill_inventory_info')->where(['iid' => $inventory['id'], 'billType' => 'SALE', 'isDelete' => 0])->get()->getResultArray();
                                    foreach ($inventory_info as $arr => $row) {
                                        $goods = $this->db->table('goods')->where('id', $row['goodsId'])->get()->getRowArray();
                                        //如果是销售套餐类产品，则直接操作子产品库存
                                        if($goods && in_array($goods['categoryId'], $suitCids_arr)) {
                                            if ($goods['hasSon'] > 0 && strlen($goods['sonGoods']) > 0) {
                                                $sonGoods = (array)json_decode($goods['sonGoods'], true);
                                                foreach ($sonGoods as $arr1 => $row1) {
                                                    $this->db->table('goods')->where('id', $row1['gid'])->set('currentQty', 'currentQty + ' . abs((float)$row1['qty1'] * (float)$row1['qty2'] * (float)$row['qty']), false)->update();
                                                    $newQty = $this->db->table('goods')->select('currentQty')->where('id', $row1['gid'])->get()->getRowArray();
                                                    if ($newQty) {
                                                        $invHisData['goodsId'] = $row1['gid'];
                                                        $invHisData['billId'] = $row['iid'];
                                                        $invHisData['billNo'] = $row['billNo'];
                                                        $invHisData['billType'] = 'SA';
                                                        $invHisData['transType'] = $row['transType'];
                                                        $invHisData['changeType'] = '删除销售发货单(套餐)';
                                                        $invHisData['changeTime'] = date('Y-m-d H:i:s');
                                                        $invHisData['changeCurrentQty'] = abs((float)$row1['qty1'] * (float)$row1['qty2'] * (float)$row['qty']);
                                                        $invHisData['currentQty'] = (float)$newQty['currentQty'];
                                                        $invHisInfo = elements(['goodsId', 'billId', 'billNo', 'billType', 'transType', 'changeType', 'changeTime', 'changeCurrentQty', 'currentQty'], $invHisData);
                                                        $this->db->table('inv_history')->insert($invHisInfo);
                                                    }
                                                }
                                            }
                                        } else {
                                            $this->db->table('goods')->where('id', $row['goodsId'])->set('currentQty', 'currentQty + ' . $row['qty'], false)->update();
                                            $newQty = $this->db->table('goods')->select('currentQty')->where('id', $row['goodsId'])->get()->getRowArray();
                                            if (is_array($newQty) && count($newQty) > 0) {
                                                $invHisData['goodsId'] = $row['goodsId'];
                                                $invHisData['billId'] = $row['iid'];
                                                $invHisData['billNo'] = $row['billNo'];
                                                $invHisData['billType'] = 'SA';
                                                $invHisData['transType'] = $row['transType'];
                                                $invHisData['changeType'] = '删除销售发货单';
                                                $invHisData['changeTime'] = date('Y-m-d H:i:s');
                                                $invHisData['changeCurrentQty'] = abs((float)$row['qty']);
                                                $invHisData['currentQty'] = (float)$newQty['currentQty'];
                                                $invHisInfo = elements(['goodsId', 'billId', 'billNo', 'billType', 'transType', 'changeType', 'changeTime', 'changeCurrentQty', 'currentQty'], $invHisData);
                                                $this->db->table('inv_history')->insert($invHisInfo);
                                            }
                                        }
                                    }
                                    $this->db->table('bill_inventory')->where('id', $inventory['id'])->update(['isDelete' => 1]);
                                    $this->db->table('bill_inventory_info')->where('iid', $inventory['id'])->update(['isDelete' => 1]);
                                    $this->db->table('bill_inventory_log')->where('iid', $inventory['id'])->update(['isDelete' => 1]);
                                    $this->db->table('bill_order')->where('id', $order['id'])->update(['srcInvId1' => 0, 'srcInvNo1' => '', 'goodsStatus1' => 0, 'isCancel' => 1]);
                                }
                            }
                        }
                    } */
                    //然后才处理发包记录本身信息
                    if (strlen($v['no'] ?? '') < 9) { //跳过无效单号
                        continue;
                    }
                    if ($this->db->table('deliver_bag')->where(['no' => $v['no'], 'amount >' => 0])->countAllResults() > 0) { // 跳过已出运费（已扫码）单号
                        continue;
                    }
                    $no = $v['no'];
                    $company = $v['company'] == 'STO' ? '申通快递' : ($v['company'] == 'YTO' ? '圆通速递' : ($v['company'] == 'SF' ? '顺丰速运' : (($v['company'] == 'POSTB' || $v['company'] == 'YZDSBK' || $v['company'] == 'EMS') ? '邮政快递' : ($v['company'] == 'JDPS' ? '京东快递' : '其他'))));
                    $address = mb_substr($v['receiverState'], 0, 2) ?? '未知';
                    $address = $address == '��' ? '北京' : ($address == '青�' ? '青海' : ($address == '陕�' ? '陕西' : $address));
                    $chType = $v['chType'];
                    $sellerNick = $v['sellerNick'];
                    $lastUpdator = isset($shopAccountArr[$v['lastUpdator']]) ? $shopAccountArr[$v['lastUpdator']] : $v['lastUpdator'];
                    $printTime = date('y-m-d H:i:s', $v['lastUpdateTime'] / 1000);
                    $lastFourMobileNum = $v['smobile'] ? substr($v['smobile'], -4, 4) : substr($v['sphone'], -4, 4);
                    $orderNo = $v['orderIds'];
                    $amount = 0;
                    $isDelete = intval($v['canceled']);
                    $weight = $this->db->table('deliver_bag')->select('weight')->where('no', $no)->get()->getRowArray()['weight'] ?? 0;
                    if ((float)$weight > 0) {
                        $amount = deliver_amount_calc($company, $address, $weight);
                    }
                    // 有则更新无则插入
                    if ($this->db->table('deliver_bag')->where('no', $v['no'])->countAllResults() > 0) {
                        $this->db->table('deliver_bag')->where('no', $no)->update([
                            'company' => $company,
                            'address' => $address,
                            'printTime' => $printTime,
                            'lastUpdator' => $lastUpdator,
                            'lastFourMobileNum' => $lastFourMobileNum,
                            'chType' => $chType,
                            'sellerNick' => $sellerNick,
                            'orderNo' => $orderNo,
                            'amount' => $amount,
                            'isDelete' => $isDelete
                        ]);
                    } else {
                        $this->db->table('deliver_bag')->insert([
                            'no' => $no,
                            'company' => $company,
                            'address' => $address,
                            'printTime' => $printTime,
                            'lastUpdator' => $lastUpdator,
                            'lastFourMobileNum' => $lastFourMobileNum,
                            'chType' => $chType,
                            'sellerNick' => $sellerNick,
                            'orderNo' => $orderNo,
                            'amount' => $amount,
                            'isDelete' => $isDelete
                        ]);
                    }
                }
                $this->db->transComplete();
                if ($this->db->transStatus() === false) {
                    log_message('error', '灵通运单号同步：事务已回滚（淘宝系）！');
                }
            } else {
                log_message('error', '灵通运单号同步：未获取到列表（淘宝系）！');
            }
        } else {
            log_message('error', '灵通运单号同步：未获取到api数据（淘宝系）！');
        }
        //拼多多（包含自定义打单）
        $url = 'https://ltserver-pdd.800best.com/bestsmart/eno/v3/get';
        $res = global_curl($url, [
            'post' => 1,
            'postfields' => '{"token":"","expressNo":"","tradeId":"","keyWord":"","creator":"","enousedDate":["' . date('Y-m-d', strtotime("-30 days")) . 'T16:00:00.000Z","' . date('Y-m-d') . 'T15:59:59.999Z"],"cancel":"","senderKey":"","goodsInfo":"","packageCode":"","company":"","branchCode":"","chType":"","type":"","status":"","printCountType":"","begin":"' . date('Y-m-d', strtotime("-30 days")) . 'T16:00:00.000Z","end":"' . date('Y-m-d') . 'T15:59:59.999Z","datePickType":"shortTime","pageNumber":1,"objectsPerPage":10000,"pdd_control_type":"pdd_std_dd_selforder_view","client_id":"79c42ae725e746ba86cbf36d2d846fd2","outer_platform":7}',
            'httpheader' => [
                'accept: application/json',
                'accept-language: zh-CN,zh;q=0.9',
                'content-type: application/json',
                self::LT_PDD_WEB_AUTH
            ]
        ]);
        if ($res !== false) {
            $obj = json_decode($res, true);
            $arr = $obj['data']['list'] ?? [];
            if (is_array($arr) && count($arr) > 0) {
                $this->db->transStart();
                foreach ($arr as $k => $v) {
                    //先处理已回收运单号对应的销售订单【容易误处理，暂停本段代码执行】
                    /* if ($v['canceled'] && strlen($v['orderIds']) > 0) { //已回收 且有对应订单号 则判定未实际未发货
                        $orderIds = explode(',', $v['orderIds']);
                        foreach ($orderIds as $index => $billNo) {
                            $order = $this->db->table('bill_order')->where(['billNo' => $billNo, 'billType' => 'SALE', 'isDelete' => 0])->get()->getRowArray();
                            if ($order && $order['srcInvId1'] > 0 && $order['srcInvId2'] == 0 && $order['srcMoneyId1'] > 0 && $order['srcMoneyId2'] > 0 && $order['isCancel'] == 0) { //如果订单存在于ERP系统、且已发货、未退货、已收款、已退款、未作废，才将自动生成的发货单删除、恢复库存、并将状态设置为作废、提醒事项不再将作废订单计入在内
                                $inventory = $this->db->table('bill_inventory')->where(['id' => $order['srcInvId1'], 'billType' => 'SALE', 'isDelete' => 0])->get()->getRowArray();
                                if ($inventory) {
                                    $suitCids_arr = array_column($this->db->table('category')->select('id')->where('FIND_IN_SET("280", path) >', 0, false)->where('isDelete', 0)->get()->getResultArray(), 'id'); //取出套餐类id数组备用
                                    $inventory_info = $this->db->table('bill_inventory_info')->where(['iid' -> $inventory['id'], 'billType' => 'SALE', 'isDelete' => 0])->get()->getResultArray();
                                    foreach ($inventory_info as $arr => $row) {
                                        $goods = $this->db->table('goods')->where('id', $row['goodsId'])->get()->getRowArray();
                                        //如果是销售套餐类产品，则直接操作子产品库存
                                        if($goods && in_array($goods['categoryId'], $suitCids_arr)) {
                                            if ($goods['hasSon'] > 0 && strlen($goods['sonGoods']) > 0) {
                                                $sonGoods = (array)json_decode($goods['sonGoods'], true);
                                                foreach ($sonGoods as $arr1 => $row1) {
                                                    $this->db->table('goods')->where('id', $row1['gid'])->set('currentQty', 'currentQty + ' . abs((float)$row1['qty1'] * (float)$row1['qty2'] * (float)$row['qty']), false)->update();
                                                    $newQty = $this->db->table('goods')->select('currentQty')->where('id', $row1['gid'])->get()->getRowArray();
                                                    if (is_array($newQty) && count($newQty) > 0) {
                                                        $invHisData['goodsId'] = $row1['gid'];
                                                        $invHisData['billId'] = $row['iid'];
                                                        $invHisData['billNo'] = $row['billNo'];
                                                        $invHisData['billType'] = 'SA';
                                                        $invHisData['transType'] = $row['transType'];
                                                        $invHisData['changeType'] = '删除销售发货单(套餐)';
                                                        $invHisData['changeTime'] = date('Y-m-d H:i:s');
                                                        $invHisData['changeCurrentQty'] = abs((float)$row1['qty1'] * (float)$row1['qty2'] * (float)$row['qty']);
                                                        $invHisData['currentQty'] = (float)$newQty['currentQty'];
                                                        $invHisInfo = elements(['goodsId', 'billId', 'billNo', 'billType', 'transType', 'changeType', 'changeTime', 'changeCurrentQty', 'currentQty'], $invHisData);
                                                        $this->db->table('inv_history')->insert($invHisInfo);
                                                    }
                                                }
                                            }
                                        } else {
                                            $this->db->table('goods')->where('id', $row['goodsId'])->set('currentQty', 'currentQty + ' . $row['qty'], false)->update();
                                            $newQty = $this->db->table('goods')->select('currentQty')->where('id', $row['goodsId'])->get()->getRowArray();
                                            if ($newQty) {
                                                $invHisData['goodsId'] = $row['goodsId'];
                                                $invHisData['billId'] = $row['iid'];
                                                $invHisData['billNo'] = $row['billNo'];
                                                $invHisData['billType'] = 'SA';
                                                $invHisData['transType'] = $row['transType'];
                                                $invHisData['changeType'] = '删除销售发货单';
                                                $invHisData['changeTime'] = date('Y-m-d H:i:s');
                                                $invHisData['changeCurrentQty'] = abs((float)$row['qty']);
                                                $invHisData['currentQty'] = (float)$newQty['currentQty'];
                                                $invHisInfo = elements(['goodsId', 'billId', 'billNo', 'billType', 'transType', 'changeType', 'changeTime', 'changeCurrentQty', 'currentQty'], $invHisData);
                                                $this->db->table('inv_history')->insert($invHisInfo);
                                            }
                                        }
                                    }
                                    $this->db->table('bill_inventory')->where('id', $inventory['id'])->update(['isDelete' => 1]);
                                    $this->db->table('bill_inventory_info')->where('iid', $inventory['id'])->update(['isDelete' => 1]);
                                    $this->db->table('bill_inventory_log')->where('iid', $inventory['id'])->update(['isDelete' => 1]);
                                    $this->db->table('bill_order')->where('id', $order['id'])->update(['srcInvId1' => 0, 'srcInvNo1' => '', 'goodsStatus1' => 0, 'isCancel' => 1]);
                                }
                            }
                        }
                    } */
                    //然后才处理发包记录本身信息
                    if (strlen($v['no'] ?? '') < 9) { //跳过无效单号
                        continue;
                    }
                    if ($this->db->table('deliver_bag')->where(['no' => $v['no'] , 'amount >' => 0])->countAllResults() > 0) { //跳过已出运费（已扫码）单号
                        continue;
                    }
                    $no = $v['no'];
                    $company = $v['company'] == 'STO' ? '申通快递' : ($v['company'] == 'YTO' ? '圆通速递' : ($v['company'] == 'SF' ? '顺丰速运' : (($v['company'] == 'POSTB' || $v['company'] == 'YZDSBK' || $v['company'] == 'EMS') ? '邮政快递' : ($v['company'] == 'JDPS' ? '京东快递' : '其他'))));
                    $address = mb_substr($v['receiverState'], 0, 2) ?? '未知';
                    $address = $address == '��' ? '北京' : ($address == '青�' ? '青海' : ($address == '陕�' ? '陕西' : $address));
                    $chType = $v['chType'];
                    $sellerNick = $v['sellerNick'];
                    $lastUpdator = isset($shopAccountArr[$v['lastUpdator']]) ? $shopAccountArr[$v['lastUpdator']] : $v['lastUpdator'];
                    $printTime = date('y-m-d H:i:s', $v['lastUpdateTime'] / 1000);
                    $lastFourMobileNum = $v['smobile'] ? substr($v['smobile'], -4, 4) : substr($v['sphone'], -4, 4);
                    $orderNo = $v['orderIds'];
                    $amount = 0;
                    $isDelete = intval($v['canceled']);
                    $weight = $this->db->table('deliver_bag')->select('weight')->where('no', $no)->get()->getRowArray()['weight'] ?? 0;
                    if ((float)$weight > 0) {
                        $amount = deliver_amount_calc($company, $address, $weight);
                    }
                    // 有则更新无则插入
                    if ($this->db->table('deliver_bag')->where('no', $v['no'])->countAllResults() > 0) {
                        $this->db->table('deliver_bag')->where('no', $no)->update([
                            'company' => $company,
                            'address' => $address,
                            'printTime' => $printTime,
                            'lastUpdator' => $lastUpdator,
                            'lastFourMobileNum' => $lastFourMobileNum,
                            'chType' => $chType,
                            'sellerNick' => $sellerNick,
                            'orderNo' => $orderNo,
                            'amount' => $amount,
                            'isDelete' => $isDelete
                        ]);
                    } else {
                        $this->db->table('deliver_bag')->insert([
                            'no' => $no,
                            'company' => $company,
                            'address' => $address,
                            'printTime' => $printTime,
                            'lastUpdator' => $lastUpdator,
                            'lastFourMobileNum' => $lastFourMobileNum,
                            'chType' => $chType,
                            'sellerNick' => $sellerNick,
                            'orderNo' => $orderNo,
                            'amount' => $amount,
                            'isDelete' => $isDelete
                        ]);
                    }
                }
                $this->db->transComplete();
                if ($this->db->transStatus() === false) {
                    log_message('error', '灵通运单号同步：事务已回滚（拼多多）！');
                }
            } else {
                log_message('error', '灵通运单号同步：未获取到列表（拼多多）！');
            }
        } else {
            log_message('error', '灵通运单号同步：未获取到api数据（拼多多）！');
        }
        $this->db->table('option')->where('optionName', 'deliverbag_spider_status')->update(['optionValue' => 0]);
        if ($n >= 1 && $n <= 5 && intval(date('s')) >= 8 && intval(date('s')) <= 55) {
            // 尽可能快的连续抓取最新数据入库，让直接打印快递面单到1楼后尽可能快的能扫码识别匹配。
            // 在下一分钟定时任务触发前，递归调用最多4次，从而弥补windows定时任务最小只支持1分钟执行一次的不足。
            // 递归调用时机控制在每分钟的第8秒到第55秒之间，避免和下一个分钟的定时任务触发时间冲突。
            // 递归深度不宜太深，否则内存占用过高，有可能造成内存溢出。
            $this->deliverbag_spider($n + 1);
        }
    }
    /* 未发货订单强制同步 */
    public function notSendGoodsOrder_spider()
    {
        $billNo = $this->request->getGetPost('billNo');
        $uName = $this->request->getGetPost('uName');
        if (strlen($billNo) >= 19 && strlen($uName) > 0) {
            if ($this->db->table('bill_order')->where(['billNo' => $billNo, 'isDelete' => 0])->countAllResults() > 0) {
                return $this->response->setJSON(['status' => -1, 'msg' => '系统中已存在该订单！']);
            } else {
                if (strlen($billNo) == 20 && strpos($billNo, 'X') !== false) { // 官网订单
                    $url = 'https://www.56dz.com/erpapi/order/getnotdeliveredorder';
                    $res = global_curl($url);
                    if ($res !== false) {
                        $obj = json_decode($res, true);
                        $arr = $obj['data'] ?? [];
                        if ($obj && $obj['errno'] == 0 && is_array($arr) && count($arr) > 0) {
                            $this->db->transStart();
                            foreach ($arr as $k => $v) {
                                if ($v['order_no'] == $billNo) {
                                    //处理运单信息，将官网订单号更新到对应的发包记录里
                                    $this->db->table('deliver_bag')->where('no', $v['express_no'])->update(['orderNo' => $v['order_no']]);
                                    // 如果已存在该订单信息，则退出本次循环，停止本单后续处理动作
                                    if ($this->db->table('bill_order')->where(['billNo' => $v['order_no'] , 'isDelete' => 0])->countAllResults() > 0) {
                                        continue;
                                    }
                                    $comeFrom = '官网';
                                    if ($this->db->table('billfrom')->where('name', $comeFrom)->countAllResults() == 0) {
                                        $this->db->table('billfrom')->insert(['name' => $comeFrom, 'company' => '虚拟主体']);
                                    }
                                    $billfrom = $this->db->table('billfrom')->select('id, name, company')->where('name', $comeFrom)->get()->getRowArray();
                                    $company = $this->db->table('company')->select('id, name')->where('name', $billfrom['company'])->get()->getRowArray() ?? ['id' => 16, 'name' => '虚拟主体'];
                                    $companyAccount = $this->db->table('company_account')->select('id, name')->where('company', $company['name'])->get()->getRowArray() ?? ['id' => 1, 'name' => '无主账户(户头)'];
                                    $customer = $order = $order_info = $money = $money_info = [];
                                    $builder = $this->db->table('contact')->where('isDelete', 0);
                                    if ($v['mobile']) {
                                        $builder->where('JSON_SEARCH(linkMans, "one", ' . $this->db->escape($v['mobile']) . ', NULL, "$[*].linkMobile") IS NOT NULL', null, false);
                                    } elseif ($v['phone']) {
                                        $builder->where('JSON_SEARCH(linkMans, "one", ' . $this->db->escape($v['phone']) . ', NULL, "$[*].linkPhone") IS NOT NULL', null, false);
                                    } else {
                                        $builder->where('id', -1);
                                    }
                                    $contactInfo = (clone $builder)->select('id, name')->get()->getRowArray();
                                    $contactId = $contactInfo ? $contactInfo['id'] : '';
                                    $contactName = $contactInfo ? $contactInfo['name'] : '';
                                    if (strpos($v['mobile'], '*') === false && $builder->countAllResults() == 0) {
                                        $customer['type'] = 0;
                                        $customer['comeFrom'] = $comeFrom;
                                        $customer['name'] = $v['accept_name'];
                                        $customer['categoryId'] = 46;
                                        $customer['cLevel'] = 0;
                                        $customer['beginDate'] = date('Y-m-d');
                                        $customer['uName'] = '系统';
                                        $customer['createTime'] = date('Y-m-d H:i:s');
                                        $customer['linkMans'] = json_encode([
                                            [
                                                'linkId' => '1',
                                                'linkName' =>  ($v['accept_name'] ?? ''),
                                                'linkPlace' => '',
                                                'linkWangWang' => '',
                                                'linkWeixin' => '',
                                                'linkIm' => '',
                                                'linkMobile' => ($v['mobile'] ?? ''),
                                                'linkPhone' => ($v['phone'] ?? ''),
                                                'linkFirst' => 1,
                                                'province' => ($v['province'] ?? ''),
                                                'city' => ($v['city'] ?? ''),
                                                'county' => ($v['county'] ?? ''),
                                                'address' => ($v['addr'] ?? '')
                                            ]
                                        ], 320);
                                        $this->db->table('contact')->insert($customer);
                                        $contactId = $this->db->insertID();
                                        $contactName = $customer['name'];
                                    }
                                    $order['billFrom'] = $comeFrom;
                                    $order['billDate'] = $money['billDate'] = $money_info['billDate'] = date('Y-m-d', intval($v['create_time'] / 1000));
                                    $order['deliveryDate'] = date_format(date_add(date_create($order['billDate']), date_interval_create_from_date_string('5 days')), 'Y-m-d');
                                    $order['billNo'] = $order['srcMoneyNo1'] = $order['srcInvNo1'] = $money['billNo'] = $money['srcNo'] = $money_info['billNo'] = $v['order_no'];
                                    $order['billType'] = 'SALE';
                                    $order['transType'] = $money['srcTransType'] = 101201;
                                    $money['billType'] = $money_info['billType'] = 'REC';
                                    $money['transType'] = $money_info['transType'] = 103101;
                                    $money['miniType'] = $money_info['miniType'] = 1;
                                    $order['totalAmount'] = $v['order_amount'];
                                    $order['totalDiscount'] = $v['adjust_amount'];
                                    $order['totalQty'] = 0;
                                    $order['goods'] = [];
                                    foreach ($v['goods'] as $k1 => $v1) {
                                        $order['totalQty'] += intval($v1['qty']);
                                        $order['goods'][$k1]['no'] = $v1['goods_no'];
                                        $order['goods'][$k1]['name'] = $v1['title'];
                                        $order['goods'][$k1]['sku'] = $v1['type'];
                                        $order['goods'][$k1]['qty'] = intval($v1['qty']);
                                        $order['goods'][$k1]['unit'] = '件';
                                        $order['goods'][$k1]['price'] = abs((float)$v1['price']);
                                        $order['goods'][$k1]['discount'] = 0;
                                        $order['goods'][$k1]['amount'] = abs((float)$v1['amount']);
                                        if (!$v1['goods_no'] && strpos($v1['title'], '补差价') === false) {
                                            $info = '【' . $comeFrom . '】【' . str_replace('"', '', $v1['title']) . ($v1['type'] ? ' ' . str_replace('"', '', $v1['type']) : '') . '】没有产品编码！';
                                            if ($this->db->table('spider_err_goods')->where(['info' => $info, 'status' => 0])->countAllResults() == 0) {
                                                $this->db->table('spider_err_goods')->insert(['createTime' => date('Y-m-d H:i:s'), 'info' => $info]);
                                            }
                                        }
                                    }
                                    $order['goods'] = json_encode($order['goods'], 320);
                                    $order['disRate'] = ((float)$order['totalDiscount'] / $order['totalAmount']);
                                    $order['disAmount'] = (float)$order['totalDiscount'];
                                    $order['deliverAmount'] = (float)$v['real_freight'];
                                    $order['amount'] = $money['amount'] = $money['cleared'] = $money_info['amount'] = (float)$v['order_amount'];
                                    $order['moneyStatus1'] = $money['moneyStatus'] = 2;
                                    $order['goodsStatus1'] = 0;
                                    $userInfo = $this->db->table('user')->select('id, name')->where('userName', $v['admin'])->get()->getRowArray();
                                    $order['uId'] = $order['checkId'] = $userInfo ? $userInfo['id'] : 1;
                                    $order['uName'] = $order['checkName'] = $userInfo ? $userInfo['name'] : '系统';
                                    $money['uId'] = $money['checkId'] = $money_info['uId'] = $money_info['checkId'] = 1;
                                    $money['uName'] = $money['checkName'] = '系统';
                                    $order['contactId'] = $money['contactId'] = $money_info['contactId'] = $contactId ?? 1;
                                    $order['contactName'] = $money['contactName'] = $contactName ?? '默认客户';
                                    $order['sId'] = $company['id'];
                                    $order['sName'] = $company['name'];
                                    $order['description'] = ($v['user_remark'] ? '客户备注：' . $v['user_remark'] . "\r\n" : '') . ($v['admin_remark'] ? '销售备注：' . $v['admin_remark'] . "\r\n" : '') . '物流单号：' . $v['express_company_name'] . $v['express_no'];
                                    $order['createTime'] = $order['modifyTime'] = $money['createTime'] = $money['modifyTime'] = $money_info['createTime'] = $money_info['settlementTime'] = date('Y-m-d H:i:s', intval($v['create_time'] / 1000));
                                    $money_info['typeId'] = 8;
                                    $money_info['accId'] = $companyAccount['id'];
                                    $money_info['wayId'] = $v['payment'] == 1 ? 74 : 12;
                                    // 插入前多检测一次是否已存在本订单
                                    if ($this->db->table('bill_order')->where(['billNo' => $v['order_no'], 'isDelete' => 0])->countAllResults() > 0) {
                                        continue;
                                    }
                                    $this->db->table('bill_order')->insert($order);
                                    $iid_1 = $this->db->insertID();
                                    $money['srcId'] = $iid_1;
                                    $this->db->table('bill_money')->insert($money);
                                    $iid_3 = $this->db->insertID();
                                    if ($iid_1 && $iid_3) {
                                        $this->db->table('bill_order')->where('billNo', $order['billNo'])->update(['srcMoneyId1' => $iid_3]);
                                        $money_info['iid'] = $iid_3;
                                        $this->db->table('bill_money_info')->insert($money_info);
                                        foreach ($v['goods'] as $k2 => $v2) {
                                            $no = $v2['goods_no'] ?? '';
                                            $goods = $this->db->table('goods')->where('no', $no)->get()->getRowArray();
                                            $order_info[$k2]['billNo'] = $order['billNo'];
                                            $order_info[$k2]['billType'] = $order['billType'];
                                            $order_info[$k2]['transType'] = $order['transType'];
                                            $order_info[$k2]['goodsId'] = $goods['id'] ?? 0;
                                            if ('' != $no && 0 == $order_info[$k2]['goodsId'] && strpos($v2['title'], '补差价') === false) {
                                                $info = '【' . $comeFrom . '】【' . $no . ' ' . str_replace('"', '', $v2['title']) . ($v2['type'] ? ' ' . str_replace('"', '', $v2['type']) : '') . '】在本系统中没有找到对应的产品！';
                                                if ($this->db->table('spider_err_goods')->where(['info' => $info, 'status' => 0])->countAllResults() == 0) {
                                                    $this->db->table('spider_err_goods')->insert(['createTime' => date('Y-m-d H:i:s'), 'info' => $info]);
                                                }
                                            }
                                            $order_info[$k2]['storageId'] = 1;
                                            $order_info[$k2]['orderQty'] = abs(intval($v2['qty']));
                                            $order_info[$k2]['price'] = (float)$v2['price'];
                                            $order_info[$k2]['discountRate'] = 0;
                                            $order_info[$k2]['discount'] = 0;
                                            $order_info[$k2]['amount'] = (float)$v2['amount'];
                                            $order_info[$k2]['uId'] = $order_info[$k2]['checkId'] = $order['uId'];
                                            $order_info[$k2]['contactId'] = $order['contactId'];
                                            $order_info[$k2]['description'] = (0 == $order_info[$k2]['goodsId'] ? '该产品未编码 或者 本ERP系统没有对应编码的产品，来源：【' . $comeFrom . '】【' . $v2['title'] . ($v2['type'] ? ' ' . $v2['type'] . '】' : '】') : '');
                                            $order_info[$k2]['billDate'] = $order['billDate'];
                                            $order_info[$k2]['iid'] = $iid_1;
                                        }
                                        $this->db->table('bill_order_info')->insertBatch($order_info);
                                    }
                                }
                            }
                            $this->db->table('log')->insert(['log' => '强制同步了未发货订单（订单号：' . $billNo . '）！', 'name' => $uName, 'createTime' => date('Y-m-d H:i:s')]);
                            $this->db->transComplete();
                            if ($this->db->transStatus() === false) {
                                return $this->response->setJSON(['status' => -1, 'msg' => '未发货订单强制同步失败（订单号：' . $billNo . '）！事务已回滚！']);
                            }
                            return $this->response->setJSON(['status' => 200, 'msg' => '未发货订单强制同步成功（订单号：' . $billNo . '）！']);
                        } else {
                            return $this->response->setJSON(['status' => -1, 'msg' => '未获取到未发货订单（订单号：' . $billNo . '）！']);
                        }
                    } else {
                        return $this->response->setJSON(['status' => -1, 'msg' => '未获取到未发货订单（订单号：' . $billNo . '）！']);
                    }
                } elseif (strlen($billNo) == 22 && strpos($billNo, '-') !== false) { // PDD订单
                    $url = 'https://ltserver-pdd.800best.com/bestsmart/nsTrade/incrementGet';
                    $res = global_curl($url, [
                        'post' => 1,
                        'postfields' => '{"token":"WyrR7wmZxfeASAXA#NZm38fGxTbTwbhNe","all":true,"lastQueryTime":null,"firstQueryTime":null,"lastUpdatetime":null,"payTimeOffset":0}',
                        'httpheader' => [
                            'accept: application/json',
                            'accept-encoding: gzip, deflate, br',
                            'accept-language: zh-CN,zh;q=0.9',
                            'cache-control: no-cache',
                            'content-length: 139',
                            'content-type: application/json',
                            self::LT_PDD_FRONT_AUTH,
                            'Frontend-Version: index-v4.11.0.html',
                            'User-Agent: Mozilla/5.0 (Windows NT 6.3; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36 Edg/109.0.1518.140',
                            self::LT_PDD_WEB_AUTH,
                            'x-pdd-pagecode: 8df9d7d8b0b1479f484e30beebb64e47f8cc8ed28ff5',
                            'x-pdd-pati: m4RcsOmoikGOroD7NuuSlGTKz6m8yPws',
                        ]
                    ]);
                    if ($res !== false) {
                        $obj = json_decode($res, true);
                        $arr = $obj['data'] ?? [];
                        if (is_array($arr) && count($arr) > 0) {
                            $this->db->transStart();
                            foreach ($arr['trades'] as $i => $v) {
                                if ($v['tradeId'] == $billNo) {
                                    $comeFrom = $v['sellerNick'] ? 'PDD_' . $v['sellerNick'] : 'PDD';
                                    if ($this->db->table('billfrom')->where('name', $comeFrom)->countAllResults() == 0) {
                                        $this->db->table('billfrom')->insert(['name' => $comeFrom, 'company' => '虚拟主体']);
                                    }
                                    $billfrom = $this->db->table('billfrom')->select('id, name, company')->where('name', $comeFrom)->get()->getRowArray();
                                    $company = $this->db->table('company')->select('id, name')->where('name', $billfrom['company'])->get()->getRowArray() ?? ['id' => 16, 'name' => '虚拟主体'];
                                    $companyAccount = $this->db->table('company_account')->select('id, name')->where('company', $company['name'])->get()->getRowArray() ?? ['id' => 1, 'name' => '无主账户(户头)'];
                                    $order = $order_info = $money = $money_info = [];
                                    $order['billFrom'] = $comeFrom;
                                    $order['billDate'] = $money['billDate'] = $money_info['billDate'] = date('Y-m-d', intval($v['payTime'] / 1000));
                                    $order['deliveryDate'] = date_format(date_add(date_create($order['billDate']), date_interval_create_from_date_string('5 days')), 'Y-m-d');
                                    $order['billNo'] = $order['srcMoneyNo1'] = $order['srcInvNo1'] = $money['billNo'] = $money['srcNo'] = $money_info['billNo'] = $v['tradeId'];
                                    $order['billType'] = 'SALE';
                                    $order['transType'] = $money['srcTransType'] = 101201;
                                    $money['billType'] = $money_info['billType'] = 'REC';
                                    $money['transType'] = $money_info['transType'] = 103101;
                                    $money['miniType'] = $money_info['miniType'] = 1;
                                    $order['totalQty'] = $order['totalDiscount'] = $order['totalAmount'] = 0;
                                    $order['goods'] = [];
                                    $deliverInfo = [];
                                    foreach ($v['orders'] as $k1 => $v1) {
                                        $order['totalQty'] += intval($v1['num']);
                                        $order['totalDiscount'] += ((float)$v1['discountFee'] - (float)$v1['adjustFee']);
                                        $order['totalAmount'] += intval($v1['num']) * (float)$v1['price'];
                                        $order['goods'][$k1]['no'] = ($v1['outerSkuId'] ?? ($v1['outerIid'] ?? ''));
                                        $order['goods'][$k1]['name'] = $v1['title'];
                                        $order['goods'][$k1]['sku'] = $v1['skuPropertiesName'] ?? '';
                                        $order['goods'][$k1]['qty'] = intval($v1['num']);
                                        $order['goods'][$k1]['unit'] = '件';
                                        $order['goods'][$k1]['price'] = abs((float)$v1['price']);
                                        $order['goods'][$k1]['discount'] = ((float)$v1['discountFee'] - (float)$v1['adjustFee']);
                                        $order['goods'][$k1]['amount'] = abs((float)($order['goods'][$k1]['qty'] * $order['goods'][$k1]['price']));
                                        if (!$v1['outerSkuId'] && !$v1['outerIid'] && strpos($v1['title'], '补差价') === false) {
                                            $info = '【' . $comeFrom . '】【' . str_replace('"', '', $v1['title']) . ($v1['skuPropertiesName'] ? ' ' . str_replace('"', '', $v1['skuPropertiesName']) : '') . '】没有产品编码！';
                                            if ($this->db->table('spider_err_goods')->where(['info' => $info, 'status' => 0])->countAllResults() == 0) {
                                                $this->db->table('spider_err_goods')->insert(['createTime' => date('Y-m-d H:i:s'), 'info' => $info]);
                                            }
                                        }
                                        if (strlen($v1['invoiceNo'] ?? '') > 0) {
                                            $deliverInfo[$k1] = $v1['logisticsCompany'] . $v1['invoiceNo'];
                                        }
                                    }
                                    $order['goods'] = json_encode($order['goods'], 320);
                                    $des = '';
                                    if (isset($deliverInfo)) {
                                        $deliverInfo = array_unique($deliverInfo);
                                        $des = implode('，', $deliverInfo);
                                    }
                                    $order['disRate'] = ((float)$v['discountFee'] / $order['totalAmount']);
                                    $order['disAmount'] = (float)$v['discountFee'];
                                    $order['deliverAmount'] = (float)$v['postFee'];
                                    $order['amount'] = $money['amount'] = $money['cleared'] = $money_info['amount'] = (float)$v['payment'];
                                    $order['moneyStatus1'] = $money['moneyStatus'] = 2;
                                    $order['goodsStatus1'] = 0;
                                    $order['uId'] = $money['uId'] = $money_info['uId'] = $order['checkId'] = $money['checkId'] = $money_info['checkId'] = 1;
                                    $order['uName'] = $money['uName'] = $order['checkName'] = $money['checkName'] = '系统';
                                    $order['contactId'] = $money['contactId'] = $money_info['contactId'] = 1;
                                    $order['contactName'] = $money['contactName'] = '默认客户';
                                    $order['sId'] = $company['id'];
                                    $order['sName'] = $company['name'];
                                    $order['description'] = ($v['buyerMessage'] ? '客户备注：' . $v['buyerMessage'] . "\r\n" : '') . ($v['sellerMemo'] ? '销售备注：' . $v['sellerMemo'] . "\r\n" : '') . ($des ? '物流单号：' . $des : '');
                                    $order['createTime'] = $order['modifyTime'] = $money['createTime'] = $money['modifyTime'] = $money_info['createTime'] = $money_info['settlementTime'] = date('Y-m-d H:i:s', intval($v['payTime'] / 1000));
                                    $money_info['typeId'] = 8;
                                    $money_info['accId'] = $companyAccount['id'];
                                    $money_info['wayId'] = 12;
                                    // 插入前多检测一次是否重复
                                    if ($this->db->table('bill_order')->where(['billNo' => $order['billNo'], 'isDelete' => 0])->countAllResults() > 0) {
                                        continue;
                                    }
                                    $this->db->table('bill_order')->insert($order);
                                    $iid_1 = $this->db->insertID();
                                    $money['srcId'] = $iid_1;
                                    $this->db->table('bill_money')->insert($money);
                                    $iid_3 = $this->db->insertID();
                                    if ($iid_1 && $iid_3) {
                                        $this->db->table('bill_order')->where('billNo', $order['billNo'])->update(['srcMoneyId1' => $iid_3]);
                                        $money_info['iid'] = $iid_3;
                                        $this->db->table('bill_money_info')->insert($money_info);
                                        foreach ($v['orders'] as $k2 => $v2) {
                                            $no = ($v2['outerSkuId'] ?? ($v2['outerIid'] ?? ''));
                                            $goods = $this->db->table('goods')->where(['no' => $no, 'isDelete' => 0])->get()->getRowArray();
                                            $order_info[$k2]['billNo'] = $order['billNo'];
                                            $order_info[$k2]['billType'] = $order['billType'];
                                            $order_info[$k2]['transType'] = $order['transType'];
                                            $order_info[$k2]['goodsId'] = $goods['id'] ?? 0;
                                            if ('' != $no && 0 == $order_info[$k2]['goodsId'] && strpos($v2['title'], '补差价') === false) {
                                                $info = '【' . $comeFrom . '】【' . $no . ' ' . str_replace('"', '', $v2['title']) . ($v2['skuPropertiesName'] ? ' ' . str_replace('"', '', $v2['skuPropertiesName']) : '') . '】在本系统中没有找到对应的产品！';
                                                if ($this->db->table('spider_err_goods')->where(['info' => $info, 'status' => 0])->countAllResults() == 0) {
                                                    $this->db->table('spider_err_goods')->insert(['createTime' => date('Y-m-d H:i:s'), 'info' => $info]);
                                                }
                                            }
                                            $order_info[$k2]['storageId'] = 1;
                                            $order_info[$k2]['orderQty'] = abs(intval($v2['num']));
                                            $order_info[$k2]['price'] = (float)$v2['price'];
                                            $order_info[$k2]['discountRate'] = (((float)$v2['discountFee'] - (float)$v2['adjustFee']) / ((float)$v2['price'] * intval($v2['num'])) * 100);
                                            $order_info[$k2]['discount'] = ((float)$v2['discountFee'] - (float)$v2['adjustFee']);
                                            $order_info[$k2]['amount'] = (float)($order_info[$k2]['orderQty'] * $order_info[$k2]['price']);
                                            $order_info[$k2]['uId'] = $order_info[$k2]['checkId'] = 1;
                                            $order_info[$k2]['contactId'] = $order['contactId'];
                                            $order_info[$k2]['description'] = (0 == $order_info[$k2]['goodsId'] ? '该产品未编码 或者本系统没有对应编码的产品，来源：【' . $comeFrom . '】【' . $v2['title'] . ($v2['skuPropertiesName'] ? ' ' . $v2['skuPropertiesName'] . '】' : '】') : '');
                                            $order_info[$k2]['billDate'] = $order['billDate'];
                                            $order_info[$k2]['iid'] = $iid_1;
                                        }
                                        $this->db->table('bill_order_info')->insertBatch($order_info);
                                    }
                                }
                            }
                            $this->db->table('log')->insert(['log' => '强制同步了未发货订单（订单号：' . $billNo . '）！', 'name' => $uName, 'createTime' => date('Y-m-d H:i:s')]);
                            $this->db->transComplete();
                            if ($this->db->transStatus() === false) {
                                return $this->response->setJSON(['status' => -1, 'msg' => '未发货订单强制同步失败（订单号：' . $billNo . '）！事务已回滚！']);
                            }
                            return $this->response->setJSON(['status' => 200, 'msg' => '未发货订单强制同步成功（订单号：' . $billNo . '）！']);
                        } else {
                            return $this->response->setJSON(['status' => -1, 'msg' => '未获取到未发货订单（订单号：' . $billNo . '）！']);
                        }
                    } else {
                            return $this->response->setJSON(['status' => -1, 'msg' => '未获取到未发货订单（订单号：' . $billNo . '）！']);
                    }
                } else { // 淘宝、天猫、1688、抖音、京东订单
                    $url = 'https://ltserver-prod1.800best.com/bestsmart/trade/v2/incrementGet';
                    $res = global_curl($url, [
                        'post' => 1,
                        'postfields' => '{"token":"NJ4WR4sfxK6wAJXp#2XhZk1WsrxZaTFmD","all":true,"lastQueryTime":null,"firstQueryTime":null,"lastUpdatetime":null,"payTimeOffset":0}',
                        'httpheader' => [
                            'Accept: application/json',
                            'Accept-Encoding: gzip, deflate, br',
                            'Accept-Language: zh-CN,zh;q=0.9,en;q=0.8,en-GB;q=0.7,en-US;q=0.6',
                            'Cache-Control: no-cache',
                            'Connection: keep-alive',
                            'Content-Length: 139',
                            'Content-Type: application/json',
                            self::LT_TB_FRONT_AUTH,
                            'Frontend-Version: index-v4.10.0.html',
                            'User-Agent: Mozilla/5.0 (Windows NT 6.3; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36 Edg/109.0.1518.140',
                            self::LT_TB_WEB_AUTH,
                        ]
                    ]);
                    if ($res !== false) {
                        $obj = json_decode($res, true);
                        $arr = $obj['data'] ?? [];
                        if (is_array($arr) && count($arr) > 0) {
                            $this->db->transStart();
                            foreach ($arr['trades'] as $i => $v) {
                                if ($v['tradeId'] == $billNo) {
                                    $comeFrom = ($v['orderSource'] == '1688' ? '1688_' : ($v['orderSource'] == 'DY' ? 'DY_' : 'TB_')) . ($v['title'] ?? ($v['sellerNick'] ?? ($v['orderSource'] ?? '其他')));
                                    if ($this->db->table('billfrom')->where('name', $comeFrom)->countAllResults() == 0) {
                                        $this->db->table('billfrom')->insert(['name' => $comeFrom, 'company' => '虚拟主体']);
                                    }
                                    $billfrom = $this->db->table('billfrom')->select('id, name, company')->where('name', $comeFrom)->get()->getRowArray();
                                    $company = $this->db->table('company')->select('id, name')->where('name', $billfrom['company'])->get()->getRowArray() ?? ['id' => 16, 'name' => '虚拟主体'];
                                    $companyAccount = $this->db->table('company_account')->select('id, name')->where('company', $company['name'])->get()->getRowArray() ?? ['id' => 1, 'name' => '无主账户(户头)'];
                                    $order = $order_info = $money = $money_info = [];
                                    $order['billFrom'] = $comeFrom;
                                    $order['billDate'] = $money['billDate'] = $money_info['billDate'] = date('Y-m-d', intval($v['payTime'] / 1000));
                                    $order['deliveryDate'] = date_format(date_add(date_create($order['billDate']), date_interval_create_from_date_string('5 days')), 'Y-m-d');
                                    $order['billNo'] = $order['srcMoneyNo1'] = $order['srcInvNo1'] = $money['billNo'] = $money['srcNo'] = $money_info['billNo'] = $v['tradeId'];
                                    $order['billType'] = 'SALE';
                                    $order['transType'] = $money['srcTransType'] = 101201;
                                    $money['billType'] = $money_info['billType'] = 'REC';
                                    $money['transType'] = $money_info['transType'] = 103101;
                                    $money['miniType'] = $money_info['miniType'] = 1;
                                    $order['totalQty'] = $order['totalDiscount'] = $order['totalAmount'] = 0;
                                    $order['goods'] = [];
                                    $deliverInfo = [];
                                    foreach ($v['orders'] as $k1 => $v1) {
                                        $order['totalQty'] += intval($v1['num']);
                                        $order['totalDiscount'] += ((float)$v1['discountFee'] - (float)$v1['adjustFee']);
                                        $order['totalAmount'] += intval($v1['num']) * (float)$v1['price'];
                                        $order['goods'][$k1]['no'] = ($v1['outerSkuId'] ?? ($v1['outerIid'] ?? ''));
                                        $order['goods'][$k1]['name'] = $v1['title'];
                                        $order['goods'][$k1]['sku'] = $v1['skuPropertiesName'] ?? '';
                                        $order['goods'][$k1]['qty'] = intval($v1['num']);
                                        $order['goods'][$k1]['unit'] = '件';
                                        $order['goods'][$k1]['price'] = abs((float)$v1['price']);
                                        $order['goods'][$k1]['discount'] = ((float)$v1['discountFee'] - (float)$v1['adjustFee']);
                                        $order['goods'][$k1]['amount'] = abs((float)($order['goods'][$k1]['qty'] * $order['goods'][$k1]['price']));
                                        if (!$v1['outerSkuId'] && !$v1['outerIid'] && strpos($v1['title'], '补差价') === false) {
                                            $info = '【' . $comeFrom . '】【' . str_replace('"', '', $v1['title']) . ($v1['skuPropertiesName'] ? ' ' . str_replace('"', '', $v1['skuPropertiesName']) : '') . '】没有产品编码！';
                                            if ($this->db->table('spider_err_goods')->where(['info' => $info, 'status' => 0])->countAllResults() == 0) {
                                                $this->db->table('spider_err_goods')->insert(['createTime' => date('Y-m-d H:i:s'), 'info' => $info]);
                                            }
                                        }
                                        if (strlen($v1['invoiceNo'] ?? '') > 0) {
                                            $deliverInfo[$k1] = $v1['logisticsCompany'] . $v1['invoiceNo'];
                                        }
                                    }
                                    $order['goods'] = json_encode($order['goods'], 320);
                                    $des = '';
                                    if (isset($deliverInfo)) {
                                        $deliverInfo = array_unique($deliverInfo);
                                        $des = implode('，', $deliverInfo);
                                    }
                                    $order['disRate'] = ((float)$v['discountFee'] / $order['totalAmount']);
                                    $order['disAmount'] = (float)$v['discountFee'];
                                    $order['deliverAmount'] = (float)$v['postFee'];
                                    $order['amount'] = $money['amount'] = $money['cleared'] = $money_info['amount'] = (float)$v['payment'];
                                    $order['moneyStatus1'] = $money['moneyStatus'] = 2;
                                    $order['goodsStatus1'] = 0;
                                    $order['uId'] = $money['uId'] = $money_info['uId'] = $order['checkId'] = $money['checkId'] = $money_info['checkId'] = 1;
                                    $order['uName'] = $money['uName'] = $order['checkName'] = $money['checkName'] = '系统';
                                    $order['contactId'] = $money['contactId'] = $money_info['contactId'] = 1;
                                    $order['contactName'] = $money['contactName'] = '默认客户';
                                    $order['sId'] = $company['id'];
                                    $order['sName'] = $company['name'];
                                    $order['description'] = ($v['buyerMessage'] ? '客户备注：' . $v['buyerMessage'] . "\r\n" : '') . ($v['sellerMemo'] ? '销售备注：' . $v['sellerMemo'] . "\r\n" : '') . ($des ? '物流单号：' . $des : '');
                                    $order['createTime'] = $order['modifyTime'] = $money['createTime'] = $money['modifyTime'] = $money_info['createTime'] = $money_info['settlementTime'] = date('Y-m-d H:i:s', intval($v['payTime'] / 1000));
                                    $money_info['typeId'] = 8;
                                    $money_info['accId'] = $companyAccount['id'];
                                    $money_info['wayId'] = 12;
                                    // 插入前多检测一次是否重复
                                    if ($this->db->table('bill_order')->where(['billNo' => $order['billNo'], 'isDelete' => 0])->countAllResults() > 0) {
                                        continue;
                                    }
                                    $this->db->table('bill_order')->insert($order);
                                    $iid_1 = $this->db->insertID();
                                    $money['srcId'] = $iid_1;
                                    $this->db->table('bill_money')->insert($money);
                                    $iid_3 = $this->db->insertID();
                                    if ($iid_1 && $iid_3) {
                                        $this->db->table('bill_order')->where('billNo', $order['billNo'])->update(['srcMoneyId1' => $iid_3]);
                                        $money_info['iid'] = $iid_3;
                                        $this->db->table('bill_money_info')->insert($money_info);
                                        foreach ($v['orders'] as $k2 => $v2) {
                                            $no = ($v2['outerSkuId'] ?? ($v2['outerIid'] ?? ''));
                                            $goods = $this->db->table('goods')->where(['no' => $no, 'isDelete' => 0])->get()->getRowArray();
                                            $order_info[$k2]['billNo'] = $order['billNo'];
                                            $order_info[$k2]['billType'] = $order['billType'];
                                            $order_info[$k2]['transType'] = $order['transType'];
                                            $order_info[$k2]['goodsId'] = $goods['id'] ?? 0;
                                            if ('' != $no && 0 == $order_info[$k2]['goodsId'] && strpos($v2['title'], '补差价') === false) {
                                                $info = '【' . $comeFrom . '】【' . $no . ' ' . str_replace('"', '', $v2['title']) . ($v2['skuPropertiesName'] ? ' ' . str_replace('"', '', $v2['skuPropertiesName']) : '') . '】在本系统中没有找到对应的产品！';
                                                if ($this->db->table('spider_err_goods')->where(['info' => $info, 'status' => 0])->countAllResults() == 0) {
                                                    $this->db->table('spider_err_goods')->insert(['createTime' => date('Y-m-d H:i:s'), 'info' => $info]);
                                                }
                                            }
                                            $order_info[$k2]['storageId'] = 1;
                                            $order_info[$k2]['orderQty'] = abs(intval($v2['num']));
                                            $order_info[$k2]['price'] = (float)$v2['price'];
                                            $order_info[$k2]['discountRate'] = (((float)$v2['discountFee'] - (float)$v2['adjustFee']) / ((float)$v2['price'] * intval($v2['num'])) * 100);
                                            $order_info[$k2]['discount'] = ((float)$v2['discountFee'] - (float)$v2['adjustFee']);
                                            $order_info[$k2]['amount'] = (float)($order_info[$k2]['orderQty'] * $order_info[$k2]['price']);
                                            $order_info[$k2]['uId'] = $order_info[$k2]['checkId'] = 1;
                                            $order_info[$k2]['contactId'] = $order['contactId'];
                                            $order_info[$k2]['description'] = (0 == $order_info[$k2]['goodsId'] ? '该产品未编码 或者本系统没有对应编码的产品，来源：【' . $comeFrom . '】【' . $v2['title'] . ($v2['skuPropertiesName'] ? ' ' . $v2['skuPropertiesName'] . '】' : '】') : '');
                                            $order_info[$k2]['billDate'] = $order['billDate'];
                                            $order_info[$k2]['iid'] = $iid_1;
                                        }
                                        $this->db->table('bill_order_info')->insertBatch($order_info);
                                    }
                                }
                            }
                            $this->db->table('log')->insert(['log' => '强制同步了未发货订单（订单号：' . $billNo . '）！', 'name' => $uName, 'createTime' => date('Y-m-d H:i:s')]);
                            $this->db->transComplete();
                            if ($this->db->transStatus() === false) {
                                return $this->response->setJSON(['status' => -1, 'msg' => '未发货订单强制同步失败（订单号：' . $billNo . '）！事务已回滚！']);
                            }
                            return $this->response->setJSON(['status' => 200, 'msg' => '未发货订单强制同步成功（订单号：' . $billNo . '）！']);
                        } else {
                            return $this->response->setJSON(['status' => -1, 'msg' => '未获取到未发货订单（订单号：' . $billNo . '）！']);
                        }
                    } else {
                        $url = 'https://ltserver.800best.com/bestsmart/ws/nsTrade/incrementGet';
                        $res = global_curl($url, [
                            'post' => 1,
                            'postfields' => '{"token":"NJ4WR4sfxK6wAJXp#2XhZk1WsrxZaTFmD","all":true,"lastQueryTime":null,"firstQueryTime":null,"lastUpdatetime":null,"payTimeOffset":0}',
                            'httpheader' => [
                                'Accept: application/json',
                                'Accept-Encoding: gzip, deflate, br',
                                'Accept-Language: zh-CN,zh;q=0.9,en;q=0.8,en-GB;q=0.7,en-US;q=0.6',
                                'Cache-Control: no-cache',
                                'Connection: keep-alive',
                                'Content-Length: 139',
                                'Content-Type: application/json',
                                self::LT_TB_FRONT_AUTH,
                                'Frontend-Version: index-v4.10.0.html',
                                'User-Agent: Mozilla/5.0 (Windows NT 6.3; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36 Edg/109.0.1518.140',
                                self::LT_TB_WEB_AUTH,
                            ]
                        ]);
                        if ($res !== false) {
                            $obj = json_decode($res, true);
                            $arr = $obj['data'] ?? [];
                            if (is_array($arr) && count($arr) > 0) {
                                $this->db->transStart();
                                foreach ($arr['trades'] as $i => $v) {
                                    if ($v['tradeId'] == $billNo) {
                                        $comeFrom = ($v['orderSource'] == '1688' ? '1688_' : ($v['orderSource'] == 'DY' ? 'DY_' : 'TB_')) . ($v['title'] ?? ($v['sellerNick'] ?? ($v['orderSource'] ?? '其他')));
                                        if ($this->db->table('billfrom')->where('name', $comeFrom)->countAllResults() == 0) {
                                            $this->db->table('billfrom')->insert(['name' => $comeFrom, 'company' => '虚拟主体']);
                                        }
                                        $billfrom = $this->db->table('billfrom')->select('id, name, company')->where('name', $comeFrom)->get()->getRowArray();
                                        $company = $this->db->table('company')->select('id, name')->where('name', $billfrom['company'])->get()->getRowArray() ?? ['id' => 16, 'name' => '虚拟主体'];
                                        $companyAccount = $this->db->table('company_account')->select('id, name')->where('company', $company['name'])->get()->getRowArray() ?? ['id' => 1, 'name' => '无主账户(户头)'];
                                        $order = $order_info = $money = $money_info = [];
                                        $order['billFrom'] = $comeFrom;
                                        $order['billDate'] = $money['billDate'] = $money_info['billDate'] = date('Y-m-d', intval($v['payTime'] / 1000));
                                        $order['deliveryDate'] = date_format(date_add(date_create($order['billDate']), date_interval_create_from_date_string('5 days')), 'Y-m-d');
                                        $order['billNo'] = $order['srcMoneyNo1'] = $order['srcInvNo1'] = $money['billNo'] = $money['srcNo'] = $money_info['billNo'] = $v['tradeId'];
                                        $order['billType'] = 'SALE';
                                        $order['transType'] = $money['srcTransType'] = 101201;
                                        $money['billType'] = $money_info['billType'] = 'REC';
                                        $money['transType'] = $money_info['transType'] = 103101;
                                        $money['miniType'] = $money_info['miniType'] = 1;
                                        $order['totalQty'] = $order['totalDiscount'] = $order['totalAmount'] = 0;
                                        $order['goods'] = [];
                                        $deliverInfo = [];
                                        foreach ($v['orders'] as $k1 => $v1) {
                                            $order['totalQty'] += intval($v1['num']);
                                            $order['totalDiscount'] += ((float)$v1['discountFee'] - (float)$v1['adjustFee']);
                                            $order['totalAmount'] += intval($v1['num']) * (float)$v1['price'];
                                            $order['goods'][$k1]['no'] = ($v1['outerSkuId'] ?? ($v1['outerIid'] ?? ''));
                                            $order['goods'][$k1]['name'] = $v1['title'];
                                            $order['goods'][$k1]['sku'] = $v1['skuPropertiesName'] ?? '';
                                            $order['goods'][$k1]['qty'] = intval($v1['num']);
                                            $order['goods'][$k1]['unit'] = '件';
                                            $order['goods'][$k1]['price'] = abs((float)$v1['price']);
                                            $order['goods'][$k1]['discount'] = ((float)$v1['discountFee'] - (float)$v1['adjustFee']);
                                            $order['goods'][$k1]['amount'] = abs((float)($order['goods'][$k1]['qty'] * $order['goods'][$k1]['price']));
                                            if (!$v1['outerSkuId'] && !$v1['outerIid'] && strpos($v1['title'], '补差价') === false) {
                                                $info = '【' . $comeFrom . '】【' . str_replace('"', '', $v1['title']) . ($v1['skuPropertiesName'] ? ' ' . str_replace('"', '', $v1['skuPropertiesName']) : '') . '】没有产品编码！';
                                                if ($this->db->table('spider_err_goods')->where(['info' => $info, 'status' => 0])->countAllResults() == 0) {
                                                    $this->db->table('spider_err_goods')->insert(['createTime' => date('Y-m-d H:i:s'), 'info' => $info]);
                                                }
                                            }
                                            if (strlen($v1['invoiceNo'] ?? '') > 0) {
                                                $deliverInfo[$k1] = $v1['logisticsCompany'] . $v1['invoiceNo'];
                                            }
                                        }
                                        $order['goods'] = json_encode($order['goods'], 320);
                                        $des = '';
                                        if (isset($deliverInfo)) {
                                            $deliverInfo = array_unique($deliverInfo);
                                            $des = implode('，', $deliverInfo);
                                        }
                                        $order['disRate'] = ((float)$v['discountFee'] / $order['totalAmount']);
                                        $order['disAmount'] = (float)$v['discountFee'];
                                        $order['deliverAmount'] = (float)$v['postFee'];
                                        $order['amount'] = $money['amount'] = $money['cleared'] = $money_info['amount'] = (float)$v['payment'];
                                        $order['moneyStatus1'] = $money['moneyStatus'] = 2;
                                        $order['goodsStatus1'] = 0;
                                        $order['uId'] = $money['uId'] = $money_info['uId'] = $order['checkId'] = $money['checkId'] = $money_info['checkId'] = 1;
                                        $order['uName'] = $money['uName'] = $order['checkName'] = $money['checkName'] = '系统';
                                        $order['contactId'] = $money['contactId'] = $money_info['contactId'] = 1;
                                        $order['contactName'] = $money['contactName'] = '默认客户';
                                        $order['sId'] = $company['id'];
                                        $order['sName'] = $company['name'];
                                        $order['description'] = ($v['buyerMessage'] ? '客户备注：' . $v['buyerMessage'] . "\r\n" : '') . ($v['sellerMemo'] ? '销售备注：' . $v['sellerMemo'] . "\r\n" : '') . ($des ? '物流单号：' . $des : '');
                                        $order['createTime'] = $order['modifyTime'] = $money['createTime'] = $money['modifyTime'] = $money_info['createTime'] = $money_info['settlementTime'] = date('Y-m-d H:i:s', intval($v['payTime'] / 1000));
                                        $money_info['typeId'] = 8;
                                        $money_info['accId'] = $companyAccount['id'];
                                        $money_info['wayId'] = 12;
                                        // 插入前多检测一次是否重复
                                        if ($this->db->table('bill_order')->where(['billNo' => $order['billNo'], 'isDelete' => 0])->countAllResults() > 0) {
                                            continue;
                                        }
                                        $this->db->table('bill_order')->insert($order);
                                        $iid_1 = $this->db->insertID();
                                        $money['srcId'] = $iid_1;
                                        $this->db->table('bill_money')->insert($money);
                                        $iid_3 = $this->db->insertID();
                                        if ($iid_1 && $iid_3) {
                                            $this->db->table('bill_order')->where('billNo', $order['billNo'])->update(['srcMoneyId1' => $iid_3]);
                                            $money_info['iid'] = $iid_3;
                                            $this->db->table('bill_money_info')->insert($money_info);
                                            foreach ($v['orders'] as $k2 => $v2) {
                                                $no = ($v2['outerSkuId'] ?? ($v2['outerIid'] ?? ''));
                                                $goods = $this->db->table('goods')->where(['no' => $no, 'isDelete' => 0])->get()->getRowArray();
                                                $order_info[$k2]['billNo'] = $order['billNo'];
                                                $order_info[$k2]['billType'] = $order['billType'];
                                                $order_info[$k2]['transType'] = $order['transType'];
                                                $order_info[$k2]['goodsId'] = $goods['id'] ?? 0;
                                                if ('' != $no && 0 == $order_info[$k2]['goodsId'] && strpos($v2['title'], '补差价') === false) {
                                                    $info = '【' . $comeFrom . '】【' . $no . ' ' . str_replace('"', '', $v2['title']) . ($v2['skuPropertiesName'] ? ' ' . str_replace('"', '', $v2['skuPropertiesName']) : '') . '】在本系统中没有找到对应的产品！';
                                                    if ($this->db->table('spider_err_goods')->where(['info' => $info, 'status' => 0])->countAllResults() == 0) {
                                                        $this->db->table('spider_err_goods')->insert(['createTime' => date('Y-m-d H:i:s'), 'info' => $info]);
                                                    }
                                                }
                                                $order_info[$k2]['storageId'] = 1;
                                                $order_info[$k2]['orderQty'] = abs(intval($v2['num']));
                                                $order_info[$k2]['price'] = (float)$v2['price'];
                                                $order_info[$k2]['discountRate'] = (((float)$v2['discountFee'] - (float)$v2['adjustFee']) / ((float)$v2['price'] * intval($v2['num'])) * 100);
                                                $order_info[$k2]['discount'] = ((float)$v2['discountFee'] - (float)$v2['adjustFee']);
                                                $order_info[$k2]['amount'] = (float)($order_info[$k2]['orderQty'] * $order_info[$k2]['price']);
                                                $order_info[$k2]['uId'] = $order_info[$k2]['checkId'] = 1;
                                                $order_info[$k2]['contactId'] = $order['contactId'];
                                                $order_info[$k2]['description'] = (0 == $order_info[$k2]['goodsId'] ? '该产品未编码 或者本系统没有对应编码的产品，来源：【' . $comeFrom . '】【' . $v2['title'] . ($v2['skuPropertiesName'] ? ' ' . $v2['skuPropertiesName'] . '】' : '】') : '');
                                                $order_info[$k2]['billDate'] = $order['billDate'];
                                                $order_info[$k2]['iid'] = $iid_1;
                                            }
                                            $this->db->table('bill_order_info')->insertBatch($order_info);
                                        }
                                    }
                                }
                                $this->db->table('log')->insert(['log' => '强制同步了未发货订单（订单号：' . $billNo . '）！', 'name' => $uName, 'createTime' => date('Y-m-d H:i:s')]);
                                $this->db->transComplete();
                                if ($this->db->transStatus() === false) {
                                    return $this->response->setJSON(['status' => -1, 'msg' => '未发货订单强制同步失败（订单号：' . $billNo . '）！事务已回滚！']);
                                }
                                return $this->response->setJSON(['status' => 200, 'msg' => '未发货订单强制同步成功（订单号：' . $billNo . '）！']);
                            } else {
                            return $this->response->setJSON(['status' => -1, 'msg' => '未获取到未发货订单（订单号：' . $billNo . '）！']);
                            }
                        } else {
                            return $this->response->setJSON(['status' => -1, 'msg' => '未获取到未发货订单（订单号：' . $billNo . '）！']);
                        }
                    }
                }
            }
        } else {
            return $this->response->setJSON(['status' => -1, 'msg' => '单号或用户名错误！']);
        }
    }
}
