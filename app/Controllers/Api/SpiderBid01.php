<?php

namespace App\Controllers\Api;
use App\Controllers\BaseController;

class SpiderBid01 extends BaseController
{
    /* 招标平台类 按每5分钟执行一次的标准设计 */
    public function index()
    {
        $this->db->table('option')->where('optionName', 'bid_spider_0_1_status')->update(['optionValue' => 1]);
        // 将关键词根据规则组装成正则表达式字符串
        $in_keywords_arr = array_column($this->db->table('bid_keyword')->select('name')->where(['keywordType' => 0, 'isDelete' => 0])->get()->getResultArray(), 'name');
        foreach ($in_keywords_arr as $arr => $row) {
            if (strpos($row, ' ') !== false) {
                $row_arr = explode(' ', $row);
                $row_preg = '';
                foreach ($row_arr as $k => $v) {
                    $row_preg .= '(?=.*' . $v . ')';
                }
                $in_keywords_arr[$arr] = $row_preg;
            }
        }
        $in_keywords = implode('|', $in_keywords_arr);
        $out_keywords_arr = array_column($this->db->table('bid_keyword')->select('name')->where(['keywordType' => 1, 'isDelete' => 0])->get()->getResultArray(), 'name');
        foreach ($out_keywords_arr as $arr => $row) {
            if (strpos($row, ' ') !== false) {
                $row_arr = explode(' ', $row);
                $row_preg = '';
                foreach ($row_arr as $k => $v) {
                    $row_preg .= '(?=.*' . $v . ')';
                }
                $out_keywords_arr[$arr] = $row_preg;
            }
        }
        $out_keywords = implode('|', $out_keywords_arr);
        // 定义最终匹配到的数据
        $final_arr = [];
        // 1 竞采星 200条
        $url = 'https://api.easyjcx.com/api/portalCli/bidNeed'; // 栏目 采购需求
        $res = global_curl($url, [
            'post' => 1,
            'postfields' => '{"keyword":"","current":1,"size":100,"condition":{"areaScope":"","procurementType":"","sourceType":"","collegeNameList":[]}}'
        ]);
        if ($res !== false) {
            $obj = json_decode($res, true);
            $arr = $obj['data']['records'] ?? [];
            if (is_array($arr) && count($arr) > 0) {
                foreach ($arr as $k => $v) {
                    $data = [];
                    if ($v['orderTitle']) {
                        if (!preg_match('#(' . $in_keywords . ')#', $v['orderTitle'])) {
                            continue;
                        } else {
                            if (preg_match('#(' . $out_keywords . ')#', $v['orderTitle'])) {
                                continue;
                            } else {
                                $data['bidSiteId'] = 1;
                                $data['bidSiteName'] = '竞采星';
                                $data['bidTitle'] = $v['orderTitle'];
                                $data['bidUrl'] = 'https://www.easyjcx.com/#/purchase/detail/' . $v['orderMainId'];
                                $data['beginDate'] = $v['startBidtime'];
                                $data['inDate'] = date('Y-m-d H:i:s');
                                $final_arr[] = $data;
                            }
                        }
                    } else {
                        continue;
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 1 -> 竞采星 -> 栏目 -> 采购需求');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 1 -> 竞采星 -> 栏目 -> 采购需求');
        }
        $url = 'https://api.easyjcx.com/api/portalCli/bidAnno'; // 栏目 招标信息
        $res = global_curl($url, [
            'post' => 1,
            'postfields' => '{"keyword":"","current":1,"size":100,"condition":{"areaScope":"","collegeNameList":[],"infoType":"","projectType":""}}'
        ]);
        if ($res !== false) {
            $obj = json_decode($res, true);
            $arr = $obj['data']['records'] ?? [];
            if (is_array($arr) && count($arr) > 0) {
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['projectName'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['projectName'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 1;
                            $data['bidSiteName'] = '竞采星';
                            $data['bidTitle'] = $v['projectName'];
                            $data['bidUrl'] = 'https://www.easyjcx.com/#/bidding/detail/' . $v['bidAnnid'];
                            $data['beginDate'] = $v['publishTime'];
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 1 -> 竞采星 -> 栏目 -> 招标信息');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 1 -> 竞采星 -> 栏目 -> 招标信息');
        }
        // 2 行采家 200条
        // 栏目 采购公告 正常
        $url = 'https://www.gec123.com/xcj-gateway/api/v1/notices/stable?__platDomain__=www.gec123.com&pi=1&ps=200&publishSite=1&sourceType=2,6&states=4&type=207,206';
        $res = global_curl($url);
        if ($res !== false) {
            $obj = json_decode($res, true);
            $arr = $obj['notices'] ?? [];
            if (is_array($arr) && count($arr) > 0) {
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['title'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['title'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 2;
                            $data['bidSiteName'] = '行采家';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = 'https://www.gec123.com/notices/detail/' . $v['id'];
                            $data['beginDate'] = date('Y-m-d H:i:s', intval($v['issueTime']) / 1000);
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 2 -> 行采家 -> 采购公告 -> 正常');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 2 -> 行采家 -> 采购公告 -> 正常');
        }
        // 电子竞采
        $url = 'https://www.gec123.com/xcj-gateway/api/v2/xcjejc/v1/public/text/packages?__platDomain__=www.gec123.com&pageNum=1&pageSize=200';
        $res = global_curl($url);
        if ($res !== false) {
            $obj = json_decode($res, true);
            $arr = $obj ?? [];
            if (is_array($arr) && count($arr) > 0) {
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['noticeName'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['noticeName'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 2;
                            $data['bidSiteName'] = '行采家';
                            $data['bidTitle'] = $v['noticeName'];
                            $data['bidUrl'] = 'https://www.gec123.com/xe/notice/view-notice?noticeId=' . $v['noticeId'];
                            $data['beginDate'] = date('Y-m-d H:i:s', intval($v['publishTime']) / 1000);
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 2 -> 行采家 -> 电子竞采');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 2 -> 行采家 -> 电子竞采');
        }
        // 23 政采云 【特殊通过浏览器刷新插件+自定义js获取数据后发送到本系统api并存数据库，每次采集从数据库拿】
        $res = $this->db->table('bid_temp')->select('res')->where('site', '政采云在线询价浙江')->get()->getRowArray()['res'] ?? '';
        if ($res) {
            $obj = json_decode($res, true);
            $arr = $obj['result']['list'] ?? [];
            if (is_array($arr) && count($arr) > 0) {
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['title'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['title'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 23;
                            $data['bidSiteName'] = '政采云';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = 'https://zxxj.zcygov.cn/bidding/detail?requisitionId='.$v['requisitionId'].'&type='.$v['type'];
                            $data['beginDate'] = date('Y-m-d H:i:s', intval($v['pubTimestamp']) / 1000);
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 23 -> 政采云 -> 政采云在线询价浙江');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 23 -> 政采云 -> 政采云在线询价浙江');
        }
        $res = $this->db->table('bid_temp')->select('res')->where('site', '政采云反向竞价浙江')->get()->getRowArray()['res'] ?? '';
        if ($res) {
            $obj = json_decode($res, true);
            $arr = $obj['result']['list'] ?? [];
            if (is_array($arr) && count($arr) > 0) {
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['title'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['title'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 23;
                            $data['bidSiteName'] = '政采云';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = 'https://fxjj.zcygov.cn/bidding/detail?requisitionId='.$v['requisitionId'].'&type='.$v['type'];
                            $data['beginDate'] = date('Y-m-d H:i:s', intval($v['pubTimestamp']) / 1000);
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 23 -> 政采云 -> 政采云反向竞价浙江');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 23 -> 政采云 -> 政采云反向竞价浙江');
        }
        $res = $this->db->table('bid_temp')->select('res')->where('site', '政采云在线询价青海')->get()->getRowArray()['res'] ?? '';
        if ($res) {
            $obj = json_decode($res, true);
            $arr = $obj['result']['list'] ?? [];
            if (is_array($arr) && count($arr) > 0) {
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['title'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['title'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 23;
                            $data['bidSiteName'] = '政采云';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = 'https://zxxj.zcygov.cn/bidding/detail?requisitionId='.$v['requisitionId'].'&type='.$v['type'];
                            $data['beginDate'] = date('Y-m-d H:i:s', intval($v['pubTimestamp']) / 1000);
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 23 -> 政采云 -> 政采云在线询价青海');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 23 -> 政采云 -> 政采云在线询价青海');
        }
        $res = $this->db->table('bid_temp')->select('res')->where('site', '政采云反向竞价青海')->get()->getRowArray()['res'] ?? '';
        if ($res) {
            $obj = json_decode($res, true);
            $arr = $obj['result']['list'] ?? [];
            if (is_array($arr) && count($arr) > 0) {
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['title'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['title'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 23;
                            $data['bidSiteName'] = '政采云';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = 'https://fxjj.zcygov.cn/bidding/detail?requisitionId='.$v['requisitionId'].'&type='.$v['type'];
                            $data['beginDate'] = date('Y-m-d H:i:s', intval($v['pubTimestamp']) / 1000);
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 23 -> 政采云 -> 政采云反向竞价青海');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 23 -> 政采云 -> 政采云反向竞价青海');
        }
        $res = $this->db->table('bid_temp')->select('res')->where('site', '政采云在线询价云南')->get()->getRowArray()['res'] ?? '';
        if ($res) {
            $obj = json_decode($res, true);
            $arr = $obj['result']['list'] ?? [];
            if (is_array($arr) && count($arr) > 0) {
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['title'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['title'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 23;
                            $data['bidSiteName'] = '政采云';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = 'https://zxxj.zcygov.cn/bidding/detail?requisitionId='.$v['requisitionId'].'&type='.$v['type'];
                            $data['beginDate'] = date('Y-m-d H:i:s', intval($v['pubTimestamp']) / 1000);
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 23 -> 政采云 -> 政采云在线询价云南');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 23 -> 政采云 -> 政采云在线询价云南');
        }
        $res = $this->db->table('bid_temp')->select('res')->where('site', '政采云反向竞价云南')->get()->getRowArray()['res'] ?? '';
        if ($res) {
            $obj = json_decode($res, true);
            $arr = $obj['result']['list'] ?? [];
            if (is_array($arr) && count($arr) > 0) {
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['title'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['title'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 23;
                            $data['bidSiteName'] = '政采云';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = 'https://fxjj.zcygov.cn/bidding/detail?requisitionId='.$v['requisitionId'].'&type='.$v['type'];
                            $data['beginDate'] = date('Y-m-d H:i:s', intval($v['pubTimestamp']) / 1000);
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 23 -> 政采云 -> 政采云反向竞价云南');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 23 -> 政采云 -> 政采云反向竞价云南');
        }
        $res = $this->db->table('bid_temp')->select('res')->where('site', '政采云在线询价广西')->get()->getRowArray()['res'] ?? '';
        if ($res) {
            $obj = json_decode($res, true);
            $arr = $obj['result']['list'] ?? [];
            if (is_array($arr) && count($arr) > 0) {
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['title'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['title'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 23;
                            $data['bidSiteName'] = '政采云';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = 'https://zxxj.zcygov.cn/bidding/detail?requisitionId='.$v['requisitionId'].'&type='.$v['type'];
                            $data['beginDate'] = date('Y-m-d H:i:s', intval($v['pubTimestamp']) / 1000);
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 23 -> 政采云 -> 政采云在线询价广西');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 23 -> 政采云 -> 政采云在线询价广西');
        }
        $res = $this->db->table('bid_temp')->select('res')->where('site', '政采云反向竞价广西')->get()->getRowArray()['res'] ?? '';
        if ($res) {
            $obj = json_decode($res, true);
            $arr = $obj['result']['list'] ?? [];
            if (is_array($arr) && count($arr) > 0) {
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['title'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['title'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 23;
                            $data['bidSiteName'] = '政采云';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = 'https://fxjj.zcygov.cn/bidding/detail?requisitionId='.$v['requisitionId'].'&type='.$v['type'];
                            $data['beginDate'] = date('Y-m-d H:i:s', intval($v['pubTimestamp']) / 1000);
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 23 -> 政采云 -> 政采云反向竞价广西');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 23 -> 政采云 -> 政采云反向竞价广西');
        }
        $res = $this->db->table('bid_temp')->select('res')->where('site', '政采云在线询价吉林')->get()->getRowArray()['res'] ?? '';
        if ($res) {
            $obj = json_decode($res, true);
            $arr = $obj['result']['list'] ?? [];
            if (is_array($arr) && count($arr) > 0) {
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['title'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['title'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 23;
                            $data['bidSiteName'] = '政采云';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = 'https://zxxj.zcygov.cn/bidding/detail?requisitionId='.$v['requisitionId'].'&type='.$v['type'];
                            $data['beginDate'] = date('Y-m-d H:i:s', intval($v['pubTimestamp']) / 1000);
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 23 -> 政采云 -> 政采云在线询价吉林');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 23 -> 政采云 -> 政采云在线询价吉林');
        }
        $res = $this->db->table('bid_temp')->select('res')->where('site', '政采云反向竞价吉林')->get()->getRowArray()['res'] ?? '';
        if ($res) {
            $obj = json_decode($res, true);
            $arr = $obj['result']['list'] ?? [];
            if (is_array($arr) && count($arr) > 0) {
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['title'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['title'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 23;
                            $data['bidSiteName'] = '政采云';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = 'https://fxjj.zcygov.cn/bidding/detail?requisitionId='.$v['requisitionId'].'&type='.$v['type'];
                            $data['beginDate'] = date('Y-m-d H:i:s', intval($v['pubTimestamp']) / 1000);
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 23 -> 政采云 -> 政采云反向竞价吉林');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 23 -> 政采云 -> 政采云反向竞价吉林');
        }
        $res = $this->db->table('bid_temp')->select('res')->where('site', '政采云在线询价新疆')->get()->getRowArray()['res'] ?? '';
        if ($res) {
            $obj = json_decode($res, true);
            $arr = $obj['result']['list'] ?? [];
            if (is_array($arr) && count($arr) > 0) {
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['title'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['title'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 23;
                            $data['bidSiteName'] = '政采云';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = 'https://zxxj.zcygov.cn/bidding/detail?requisitionId='.$v['requisitionId'].'&type='.$v['type'];
                            $data['beginDate'] = date('Y-m-d H:i:s', intval($v['pubTimestamp']) / 1000);
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 23 -> 政采云 -> 政采云在线询价新疆');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 23 -> 政采云 -> 政采云在线询价新疆');
        }
        $res = $this->db->table('bid_temp')->select('res')->where('site', '政采云反向竞价新疆')->get()->getRowArray()['res'] ?? '';
        if ($res) {
            $obj = json_decode($res, true);
            $arr = $obj['result']['list'] ?? [];
            if (is_array($arr) && count($arr) > 0) {
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['title'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['title'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 23;
                            $data['bidSiteName'] = '政采云';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = 'https://fxjj.zcygov.cn/bidding/detail?requisitionId='.$v['requisitionId'].'&type='.$v['type'];
                            $data['beginDate'] = date('Y-m-d H:i:s', intval($v['pubTimestamp']) / 1000);
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 23 -> 政采云 -> 政采云反向竞价新疆');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 23 -> 政采云 -> 政采云反向竞价新疆');
        }
        $res = $this->db->table('bid_temp')->select('res')->where('site', '政采云在线询价新疆兵团')->get()->getRowArray()['res'] ?? '';
        if ($res) {
            $obj = json_decode($res, true);
            $arr = $obj['result']['list'] ?? [];
            if (is_array($arr) && count($arr) > 0) {
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['title'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['title'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 23;
                            $data['bidSiteName'] = '政采云';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = 'https://zxxj.zcygov.cn/bidding/detail?requisitionId='.$v['requisitionId'].'&type='.$v['type'];
                            $data['beginDate'] = date('Y-m-d H:i:s', intval($v['pubTimestamp']) / 1000);
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 23 -> 政采云 -> 政采云在线询价新疆兵团');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 23 -> 政采云 -> 政采云在线询价新疆兵团');
        }
        $res = $this->db->table('bid_temp')->select('res')->where('site', '政采云反向竞价新疆兵团')->get()->getRowArray()['res'] ?? '';
        if ($res) {
            $obj = json_decode($res, true);
            $arr = $obj['result']['list'] ?? [];
            if (is_array($arr) && count($arr) > 0) {
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['title'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['title'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 23;
                            $data['bidSiteName'] = '政采云';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = 'https://fxjj.zcygov.cn/bidding/detail?requisitionId='.$v['requisitionId'].'&type='.$v['type'];
                            $data['beginDate'] = date('Y-m-d H:i:s', intval($v['pubTimestamp']) / 1000);
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 23 -> 政采云 -> 政采云反向竞价新疆兵团');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 23 -> 政采云 -> 政采云反向竞价新疆兵团');
        }
        $res = $this->db->table('bid_temp')->select('res')->where('site', '政采云框架协议青海果洛藏族自治州本级')->get()->getRowArray()['res'] ?? '';
        if ($res) {
            $obj = json_decode($res, true);
            $arr = $obj['result']['data'] ?? [];
            if (is_array($arr) && count($arr) > 0) {
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['title'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['title'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 23;
                            $data['bidSiteName'] = '政采云';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = 'https://kjxy.zcygov.cn/luban/announcement/detail?encryptId='.urlencode($v['encryptId']).'&district='.$v['district'];
                            $data['beginDate'] = date('Y-m-d H:i:s', intval($v['releasedAt']) / 1000);
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 23 -> 政采云 -> 政采云框架协议青海果洛藏族自治州本级');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 23 -> 政采云 -> 政采云框架协议青海果洛藏族自治州本级');
        }
        // 下面分站还没改版，还能直接拿到数据
        $url = 'https://zxxj.guizhou.zcygov.cn/api/sparta/announcement/listNewest'; // 贵州 询价
        $res = global_curl($url, [
            'post' => 1,
            'postfields' => '{"backCategoryName":"","categoryType":"GOODS","pageNo":1,"pageSize":100,"tradeStyles":["BIDDING"],"stateList":[3,4,5,6,7,10,12,50],"otherSearch":"","instanceCodes":["GZZXXJ"],"sortField":"ANNOUNCEMENT_PUBLISH_TIME","sortMethod":"DESC","districtCodeList":["520000","520100","520102","520103","520111","520112","520113","520115","520121","520122","520123","520181","520183","520193","520198","520199","520200","520201","520203","520204","520224","520281","520299","520300","520302","520303","520304","520309","520322","520323","520324","520325","520326","520327","520328","520329","520330","520381","520382","520399","520400","520402","520403","520422","520423","520424","520425","520426","520495","520499","520500","520502","520521","520523","520524","520525","520526","520527","520581","520591","520592","520599","520600","520602","520603","520621","520622","520623","520624","520625","520626","520627","520628","520691","520692","520699","520800","520899","520900","520902","520999","522300","522301","522302","522323","522324","522325","522326","522327","522328","522329","522399","522600","522601","522622","522623","522624","522625","522626","522627","522628","522629","522630","522631","522632","522633","522634","522635","522636","522699","522700","522701","522702","522722","522723","522725","522726","522727","522728","522729","522730","522731","522732","522797","522799","529900"],"administrativeDistrictCodeList":[]}'
        ]);
        if ($res !== false) {
            $obj = json_decode($res, true);
            $arr = $obj['result']['list'] ?? [];
            if (is_array($arr) && count($arr) > 0) {
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['title'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['title'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 23;
                            $data['bidSiteName'] = '政采云';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = 'https://zxxj.guizhou.zcygov.cn/bidding/detail?requisitionId='.$v['requisitionId'].'&type='.$v['type'];
                            $data['beginDate'] = date('Y-m-d H:i:s', intval($v['pubTimestamp']) / 1000);
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 23 -> 政采云 -> 贵州 询价');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 23 -> 政采云 -> 贵州 询价');
        }
        $url = 'https://fxjj.guizhou.zcygov.cn/api/sparta/announcement/listNewest'; // 贵州 反拍
        $res = global_curl($url, [
            'post' => 1,
            'postfields' => '{"backCategoryName":"","categoryType":"GOODS","pageNo":1,"pageSize":100,"tradeStyles":["REVERSE"],"stateList":[3,4,5,6,7,10,12,50],"otherSearch":"","instanceCodes":["GZFXJJ"],"sortField":"ANNOUNCEMENT_PUBLISH_TIME","sortMethod":"DESC","districtCodeList":["520000","520100","520102","520103","520111","520112","520113","520115","520121","520122","520123","520181","520183","520193","520198","520199","520200","520201","520203","520204","520224","520281","520299","520300","520302","520303","520304","520309","520322","520323","520324","520325","520326","520327","520328","520329","520330","520381","520382","520399","520400","520402","520403","520422","520423","520424","520425","520426","520495","520499","520500","520502","520521","520523","520524","520525","520526","520527","520581","520591","520592","520599","520600","520602","520603","520621","520622","520623","520624","520625","520626","520627","520628","520691","520692","520699","520800","520899","520900","520902","520999","522300","522301","522302","522323","522324","522325","522326","522327","522328","522329","522399","522600","522601","522622","522623","522624","522625","522626","522627","522628","522629","522630","522631","522632","522633","522634","522635","522636","522699","522700","522701","522702","522722","522723","522725","522726","522727","522728","522729","522730","522731","522732","522797","522799","529900"],"administrativeDistrictCodeList":[]}'
        ]);
        if ($res !== false) {
            $obj = json_decode($res, true);
            $arr = $obj['result']['list'] ?? [];
            if (is_array($arr) && count($arr) > 0) {
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['title'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['title'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 23;
                            $data['bidSiteName'] = '政采云';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = 'https://fxjj.guizhou.zcygov.cn/bidding/detail?requisitionId='.$v['requisitionId'].'&type='.$v['type'];
                            $data['beginDate'] = date('Y-m-d H:i:s', intval($v['pubTimestamp']) / 1000);
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 23 -> 政采云 -> 贵州 反拍');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 23 -> 政采云 -> 贵州 反拍');
        }
        $url = 'https://ddcg.guizhou.zcygov.cn/api/sparta/announcement/listNewest'; // 贵州 定点
        $res = global_curl($url, [
            'post' => 1,
            'postfields' => '{"backCategoryName":"","categoryType":null,"pageNo":1,"pageSize":99,"tradeStyles":["BIDDING","REVERSE","SECOND_BIDDING"],"stateList":[3,4,5,6,7,10,12,50],"otherSearch":"","instanceCodes":["GZDDCG"],"sortField":"ANNOUNCEMENT_PUBLISH_TIME","sortMethod":"DESC","districtCodeList":[],"administrativeDistrictCodeList":[]}'
        ]);
        if ($res !== false) {
            $obj = json_decode($res, true);
            $arr = $obj['result']['list'] ?? [];
            if (is_array($arr) && count($arr) > 0) {
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['title'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['title'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 23;
                            $data['bidSiteName'] = '政采云';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = 'https://ddcg.guizhou.zcygov.cn/bidding/detail?requisitionId='.$v['requisitionId'].'&type='.$v['type'];
                            $data['beginDate'] = date('Y-m-d H:i:s', intval($v['pubTimestamp']) / 1000);
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 23 -> 政采云 -> 贵州 定点');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 23 -> 政采云 -> 贵州 定点');
        }
        $url = 'https://mall.anhui.zcygov.cn/api/sparta/announcement/listNewest'; // 安徽 询价
        $res = global_curl($url, [
            'post' => 1,
            'postfields' => '{"backCategoryName":"","categoryType":"GOODS","pageNo":1,"pageSize":100,"tradeStyles":["BIDDING"],"stateList":[3,4,5,6,7,10,12,50],"otherSearch":"","instanceCodes":["AHDDCGG","AHXYGHG","AHWC","AHSSCPG"],"sortField":"ANNOUNCEMENT_PUBLISH_TIME","sortMethod":"DESC","districtCodeList":["340000","340100","340102","340103","340104","340106","340107","340109","340111","340117","340121","340122","340123","340124","340181","340199","340200","340202","340203","340207","340208","340221","340222","340223","340225","340271","340272","340299","340300","340302","340303","340304","340311","340315","340316","340317","340321","340322","340323","340399","340400","340402","340403","340404","340405","340406","340421","340422","340471","340472","340490","340499","340500","340503","340504","340506","340509","340510","340511","340521","340522","340523","340524","340599","340600","340602","340603","340604","340621","340671","340672","340673","340699","340700","340705","340706","340711","340715","340722","340799","340800","340802","340803","340811","340816","340822","340825","340826","340827","340828","340871","340881","340882","340899","341000","341002","341003","341004","341021","341022","341023","341024","341071","341099","341100","341102","341103","341122","341124","341125","341126","341171","341172","341181","341182","341199","341200","341202","341203","341204","341221","341222","341225","341226","341271","341272","341282","341299","341300","341302","341321","341322","341323","341324","341371","341372","341373","341399","341500","341502","341503","341504","341522","341523","341524","341525","341571","341572","341599","341600","341602","341608","341611","341621","341622","341623","341699","341700","341702","341707","341708","341721","341722","341723","341771","341772","341799","341800","341802","341812","341821","341822","341823","341824","341825","341871","341872","341873","341881","341899","349900"],"administrativeDistrictCodeList":[]}'
        ]);
        if ($res !== false) {
            $obj = json_decode($res, true);
            $arr = $obj['result']['list'] ?? [];
            if (is_array($arr) && count($arr) > 0) {
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['title'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['title'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 23;
                            $data['bidSiteName'] = '政采云';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = 'https://mall.anhui.zcygov.cn/bidding/detail?requisitionId='.$v['requisitionId'].'&type='.$v['type'];
                            $data['beginDate'] = date('Y-m-d H:i:s', intval($v['pubTimestamp']) / 1000);
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 23 -> 政采云 -> 安徽 询价');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 23 -> 政采云 -> 安徽 询价');
        }
        $url = 'https://mall.anhui.zcygov.cn/api/sparta/announcement/listNewest'; // 安徽 反拍
        $res = global_curl($url, [
            'post' => 1,
            'postfields' => '{"backCategoryName":"","tradeModel":"REVERSE","categoryType":"GOODS","pageNo":1,"pageSize":100,"stateList":[3,4,5,6,7,10,12,50],"otherSearch":"","instanceCodes":["AHWC","AHSSCPG","AHDDCGG","AHXYGHG"],"sortField":"ANNOUNCEMENT_PUBLISH_TIME","sortMethod":"DESC","districtCodeList":["340000","340100","340102","340103","340104","340106","340107","340109","340111","340117","340121","340122","340123","340124","340181","340199","340200","340202","340203","340207","340208","340221","340222","340223","340225","340271","340272","340299","340300","340302","340303","340304","340311","340315","340316","340317","340321","340322","340323","340399","340400","340402","340403","340404","340405","340406","340421","340422","340471","340472","340490","340499","340500","340503","340504","340506","340509","340510","340511","340521","340522","340523","340524","340599","340600","340602","340603","340604","340621","340671","340672","340673","340699","340700","340705","340706","340711","340715","340722","340799","340800","340802","340803","340811","340816","340822","340825","340826","340827","340828","340871","340881","340882","340899","341000","341002","341003","341004","341021","341022","341023","341024","341071","341099","341100","341102","341103","341122","341124","341125","341126","341171","341172","341181","341182","341199","341200","341202","341203","341204","341221","341222","341225","341226","341271","341272","341282","341299","341300","341302","341321","341322","341323","341324","341371","341372","341373","341399","341500","341502","341503","341504","341522","341523","341524","341525","341571","341572","341599","341600","341602","341608","341611","341621","341622","341623","341699","341700","341702","341707","341708","341721","341722","341723","341771","341772","341799","341800","341802","341812","341821","341822","341823","341824","341825","341871","341872","341873","341881","341899","349900"],"administrativeDistrictCodeList":[]}'
        ]);
        if ($res !== false) {
            $obj = json_decode($res, true);
            $arr = $obj['result']['list'] ?? [];
            if (is_array($arr) && count($arr) > 0) {
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['title'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['title'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 23;
                            $data['bidSiteName'] = '政采云';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = 'https://mall.anhui.zcygov.cn/bidding/detail?requisitionId='.$v['requisitionId'].'&type='.$v['type'];
                            $data['beginDate'] = date('Y-m-d H:i:s', intval($v['pubTimestamp']) / 1000);
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 23 -> 政采云 -> 安徽 反拍');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 23 -> 政采云 -> 安徽 反拍');
        }
        // 35 广咨电子招投标交易平台 50条
        $url = 'https://www.gzebid.cn/api/bid/share/api/platform/noticeList';
        $res = global_curl($url, [
            'post' => 1,
            'postfields' => '{"params":{"type":"2","tenderMode":null},"pageSize":100,"pageNo":1}'
        ]);
        if ($res !== false) {
            $obj = json_decode($res, true);
            $arr = $obj['data']['datas'] ?? [];
            if (is_array($arr) && count($arr) > 0) {
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['title'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['title'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 35;
                            $data['bidSiteName'] = '广咨电子招投标交易平台';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = 'https://www.gzebid.cn/#/noticeDetail?id='.$v['noticeId'].'&tenderMode='.$v['tenderMode'].'&noticeType='.$v['type'];
                            $data['beginDate'] = $v['publishDate'];
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 35 -> 广咨电子招投标交易平台 -> 栏目 -> 招标公告');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 35 -> 广咨电子招投标交易平台 -> 栏目 -> 招标公告');
        }
        // 49 中国政府采购网 【特殊通过浏览器刷新插件+自定义js获取数据后发送到本系统api并存数据库，每次采集从数据库拿】
        $cookie = $this->db->table('bid_temp')->select('cookie')->where('site', '中国政府采购网')->get()->getRowArray()['cookie'] ?? '';
        if($cookie) {
            $url = 'http://www.ccgp.gov.cn/cggg/zygg/gkzb/'; // 栏目 中央公告 公开招标
            $res = global_curl($url, [
                'httpheader' => [
                    'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9',
                    'Accept-Encoding: gzip, deflate, br',
                    'Accept-Language: zh-CN,zh;q=0.9',
                    'Cache-Control: no-cache',
                    'Connection: keep-alive',
                    'Cookie: ' . $cookie,
                    'Host: www.ccgp.gov.cn',
                    'Pragma: no-cache',
                    'sec-ch-ua: "Not_A Brand";v="99", "Google Chrome";v="109", "Chromium";v="109"',
                    'sec-ch-ua-mobile: ?0',
                    'sec-ch-ua-platform: "Windows"',
                    'Sec-Fetch-Dest: document',
                    'Sec-Fetch-Mode: navigate',
                    'Sec-Fetch-Site: none',
                    'Sec-Fetch-User: ?1',
                    'Upgrade-Insecure-Requests: 1',
                    'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36'
                ]
            ]);
            if ($res !== false) {
                $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
                $preg = '#<li><ahref=".(.*)"target="_blank"title="(.*)">.*</a>.*发布时间：<em>(.*)</em>.*</li>#isU';
                $obj = [];
                $arr = [];
                preg_match_all($preg, $res, $obj);
                $count = (is_countable($obj) && count($obj) > 0) ? count($obj[0]) : 0;
                if ($count > 0) {
                    for ($j = 0; $j < $count; $j++) {
                        $arr[$j]['url'] = $obj[1][$j];
                        $arr[$j]['title'] = $obj[2][$j];
                        $arr[$j]['beginDate'] = $obj[3][$j];
                    }
                    foreach ($arr as $k => $v) {
                        $data = [];
                        if (!preg_match('#(' . $in_keywords . ')#', $v['title'])) {
                            continue;
                        } else {
                            if (preg_match('#(' . $out_keywords . ')#', $v['title'])) {
                                continue;
                            } else {
                                $data['bidSiteId'] = 49;
                                $data['bidSiteName'] = '中国政府采购网';
                                $data['bidTitle'] = $v['title'];
                                $data['bidUrl'] = 'http://www.ccgp.gov.cn/cggg/zygg/gkzb' . $v['url'];
                                $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                                $data['inDate'] = date('Y-m-d H:i:s');
                                $final_arr[] = $data;
                            }
                        }
                    }
                } else {
                    log_message('error', '招标采集：未匹配到数据 49 -> 中国政府采购网 -> 栏目 -> 中央公告 -> 公开招标');
                }
            } else {
                log_message('error', '招标采集：未获取到网页 49 -> 中国政府采购网 -> 栏目 -> 中央公告 -> 公开招标');
            }
            $url = 'http://www.ccgp.gov.cn/cggg/zygg/xjgg/'; // 栏目 中央公告 询价公告
            $res = global_curl($url, [
                'httpheader' => [
                    'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9',
                    'Accept-Encoding: gzip, deflate, br',
                    'Accept-Language: zh-CN,zh;q=0.9',
                    'Cache-Control: no-cache',
                    'Connection: keep-alive',
                    'Cookie: ' . $cookie,
                    'Host: www.ccgp.gov.cn',
                    'Pragma: no-cache',
                    'sec-ch-ua: "Not_A Brand";v="99", "Google Chrome";v="109", "Chromium";v="109"',
                    'sec-ch-ua-mobile: ?0',
                    'sec-ch-ua-platform: "Windows"',
                    'Sec-Fetch-Dest: document',
                    'Sec-Fetch-Mode: navigate',
                    'Sec-Fetch-Site: none',
                    'Sec-Fetch-User: ?1',
                    'Upgrade-Insecure-Requests: 1',
                    'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36'
                ]
            ]);
            if ($res !== false) {
                $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
                $preg = '#<li><ahref=".(.*)"target="_blank"title="(.*)">.*</a>.*发布时间：<em>(.*)</em>.*</li>#isU';
                $obj = [];
                $arr = [];
                preg_match_all($preg, $res, $obj);
                $count = (is_countable($obj) && count($obj) > 0) ? count($obj[0]) : 0;
                if ($count > 0) {
                    for ($j = 0; $j < $count; $j++) {
                        $arr[$j]['url'] = $obj[1][$j];
                        $arr[$j]['title'] = $obj[2][$j];
                        $arr[$j]['beginDate'] = $obj[3][$j];
                    }
                    foreach ($arr as $k => $v) {
                        $data = [];
                        if (!preg_match('#(' . $in_keywords . ')#', $v['title'])) {
                            continue;
                        } else {
                            if (preg_match('#(' . $out_keywords . ')#', $v['title'])) {
                                continue;
                            } else {
                                $data['bidSiteId'] = 49;
                                $data['bidSiteName'] = '中国政府采购网';
                                $data['bidTitle'] = $v['title'];
                                $data['bidUrl'] = 'http://www.ccgp.gov.cn/cggg/zygg/xjgg' . $v['url'];
                                $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                                $data['inDate'] = date('Y-m-d H:i:s');
                                $final_arr[] = $data;
                            }
                        }
                    }
                } else {
                    log_message('error', '招标采集：未匹配到数据 49 -> 中国政府采购网 -> 栏目 -> 中央公告 -> 询价公告');
                }
            } else {
                log_message('error', '招标采集：未获取到网页 49 -> 中国政府采购网 -> 栏目 -> 中央公告 -> 询价公告');
            }
            $url = 'http://www.ccgp.gov.cn/cggg/zygg/jzxtpgg/'; // 栏目 中央公告 竞争性谈判
            $res = global_curl($url, [
                'httpheader' => [
                    'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9',
                    'Accept-Encoding: gzip, deflate, br',
                    'Accept-Language: zh-CN,zh;q=0.9',
                    'Cache-Control: no-cache',
                    'Connection: keep-alive',
                    'Cookie: ' . $cookie,
                    'Host: www.ccgp.gov.cn',
                    'Pragma: no-cache',
                    'sec-ch-ua: "Not_A Brand";v="99", "Google Chrome";v="109", "Chromium";v="109"',
                    'sec-ch-ua-mobile: ?0',
                    'sec-ch-ua-platform: "Windows"',
                    'Sec-Fetch-Dest: document',
                    'Sec-Fetch-Mode: navigate',
                    'Sec-Fetch-Site: none',
                    'Sec-Fetch-User: ?1',
                    'Upgrade-Insecure-Requests: 1',
                    'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36'
                ]
            ]);
            if ($res !== false) {
                $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
                $preg = '#<li><ahref=".(.*)"target="_blank"title="(.*)">.*</a>.*发布时间：<em>(.*)</em>.*</li>#isU';
                $obj = [];
                $arr = [];
                preg_match_all($preg, $res, $obj);
                $count = (is_countable($obj) && count($obj) > 0) ? count($obj[0]) : 0;
                if ($count > 0) {
                    for ($j = 0; $j < $count; $j++) {
                        $arr[$j]['url'] = $obj[1][$j];
                        $arr[$j]['title'] = $obj[2][$j];
                        $arr[$j]['beginDate'] = $obj[3][$j];
                    }
                    foreach ($arr as $k => $v) {
                        $data = [];
                        if (!preg_match('#(' . $in_keywords . ')#', $v['title'])) {
                            continue;
                        } else {
                            if (preg_match('#(' . $out_keywords . ')#', $v['title'])) {
                                continue;
                            } else {
                                $data['bidSiteId'] = 49;
                                $data['bidSiteName'] = '中国政府采购网';
                                $data['bidTitle'] = $v['title'];
                                $data['bidUrl'] = 'http://www.ccgp.gov.cn/cggg/zygg/jzxtpgg' . $v['url'];
                                $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                                $data['inDate'] = date('Y-m-d H:i:s');
                                $final_arr[] = $data;
                            }
                        }
                    }
                } else {
                    log_message('error', '招标采集：未匹配到数据 49 -> 中国政府采购网 -> 栏目 -> 中央公告 -> 竞争性谈判');
                }
            } else {
                log_message('error', '招标采集：未获取到网页 49 -> 中国政府采购网 -> 栏目 -> 中央公告 -> 竞争性谈判');
            }
            $url = 'http://www.ccgp.gov.cn/cggg/zygg/jzxcs/'; // 栏目 中央公告 竞争性磋商
            $res = global_curl($url, [
                'httpheader' => [
                    'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9',
                    'Accept-Encoding: gzip, deflate, br',
                    'Accept-Language: zh-CN,zh;q=0.9',
                    'Cache-Control: no-cache',
                    'Connection: keep-alive',
                    'Cookie: ' . $cookie,
                    'Host: www.ccgp.gov.cn',
                    'Pragma: no-cache',
                    'sec-ch-ua: "Not_A Brand";v="99", "Google Chrome";v="109", "Chromium";v="109"',
                    'sec-ch-ua-mobile: ?0',
                    'sec-ch-ua-platform: "Windows"',
                    'Sec-Fetch-Dest: document',
                    'Sec-Fetch-Mode: navigate',
                    'Sec-Fetch-Site: none',
                    'Sec-Fetch-User: ?1',
                    'Upgrade-Insecure-Requests: 1',
                    'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36'
                ]
            ]);
            if ($res !== false) {
                $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
                $preg = '#<li><ahref=".(.*)"target="_blank"title="(.*)">.*</a>.*发布时间：<em>(.*)</em>.*</li>#isU';
                $obj = [];
                $arr = [];
                preg_match_all($preg, $res, $obj);
                $count = (is_countable($obj) && count($obj) > 0) ? count($obj[0]) : 0;
                if ($count > 0) {
                    for ($j = 0; $j < $count; $j++) {
                        $arr[$j]['url'] = $obj[1][$j];
                        $arr[$j]['title'] = $obj[2][$j];
                        $arr[$j]['beginDate'] = $obj[3][$j];
                    }
                    foreach ($arr as $k => $v) {
                        $data = [];
                        if (!preg_match('#(' . $in_keywords . ')#', $v['title'])) {
                            continue;
                        } else {
                            if (preg_match('#(' . $out_keywords . ')#', $v['title'])) {
                                continue;
                            } else {
                                $data['bidSiteId'] = 49;
                                $data['bidSiteName'] = '中国政府采购网';
                                $data['bidTitle'] = $v['title'];
                                $data['bidUrl'] = 'http://www.ccgp.gov.cn/cggg/zygg/jzxtpgg' . $v['url'];
                                $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                                $data['inDate'] = date('Y-m-d H:i:s');
                                $final_arr[] = $data;
                            }
                        }
                    }
                } else {
                    log_message('error', '招标采集：未匹配到数据 49 -> 中国政府采购网 -> 栏目 -> 中央公告 -> 竞争性磋商');
                }
            } else {
                log_message('error', '招标采集：未获取到网页 49 -> 中国政府采购网 -> 栏目 -> 中央公告 -> 竞争性磋商');
            }
            $url = 'http://www.ccgp.gov.cn/cggg/dfgg/gkzb/'; // 栏目 地方公告 公开招标
            $res = global_curl($url, [
                'httpheader' => [
                    'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9',
                    'Accept-Encoding: gzip, deflate, br',
                    'Accept-Language: zh-CN,zh;q=0.9',
                    'Cache-Control: no-cache',
                    'Connection: keep-alive',
                    'Cookie: ' . $cookie,
                    'Host: www.ccgp.gov.cn',
                    'Pragma: no-cache',
                    'sec-ch-ua: "Not_A Brand";v="99", "Google Chrome";v="109", "Chromium";v="109"',
                    'sec-ch-ua-mobile: ?0',
                    'sec-ch-ua-platform: "Windows"',
                    'Sec-Fetch-Dest: document',
                    'Sec-Fetch-Mode: navigate',
                    'Sec-Fetch-Site: none',
                    'Sec-Fetch-User: ?1',
                    'Upgrade-Insecure-Requests: 1',
                    'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36'
                ]
            ]);
            if ($res !== false) {
                $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
                $preg = '#<li><ahref=".(.*)"target="_blank"title="(.*)">.*</a>.*发布时间：<em>(.*)</em>.*</li>#isU';
                $obj = [];
                $arr = [];
                preg_match_all($preg, $res, $obj);
                $count = (is_countable($obj) && count($obj) > 0) ? count($obj[0]) : 0;
                if ($count > 0) {
                    for ($j = 0; $j < $count; $j++) {
                        $arr[$j]['url'] = $obj[1][$j];
                        $arr[$j]['title'] = $obj[2][$j];
                        $arr[$j]['beginDate'] = $obj[3][$j];
                    }
                    foreach ($arr as $k => $v) {
                        $data = [];
                        if (!preg_match('#(' . $in_keywords . ')#', $v['title'])) {
                            continue;
                        } else {
                            if (preg_match('#(' . $out_keywords . ')#', $v['title'])) {
                                continue;
                            } else {
                                $data['bidSiteId'] = 49;
                                $data['bidSiteName'] = '中国政府采购网';
                                $data['bidTitle'] = $v['title'];
                                $data['bidUrl'] = 'http://www.ccgp.gov.cn/cggg/dfgg/gkzb' . $v['url'];
                                $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                                $data['inDate'] = date('Y-m-d H:i:s');
                                $final_arr[] = $data;
                            }
                        }
                    }
                } else {
                    log_message('error', '招标采集：未匹配到数据 49 -> 中国政府采购网 -> 栏目 -> 地方公告 -> 公开招标');
                }
            } else {
                log_message('error', '招标采集：未获取到网页 49 -> 中国政府采购网 -> 栏目 -> 地方公告 -> 公开招标');
            }
            $url = 'http://www.ccgp.gov.cn/cggg/dfgg/xjgg/'; // 栏目 地方公告 询价公告
            $res = global_curl($url, [
                'httpheader' => [
                    'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9',
                    'Accept-Encoding: gzip, deflate, br',
                    'Accept-Language: zh-CN,zh;q=0.9',
                    'Cache-Control: no-cache',
                    'Connection: keep-alive',
                    'Cookie: ' . $cookie,
                    'Host: www.ccgp.gov.cn',
                    'Pragma: no-cache',
                    'sec-ch-ua: "Not_A Brand";v="99", "Google Chrome";v="109", "Chromium";v="109"',
                    'sec-ch-ua-mobile: ?0',
                    'sec-ch-ua-platform: "Windows"',
                    'Sec-Fetch-Dest: document',
                    'Sec-Fetch-Mode: navigate',
                    'Sec-Fetch-Site: none',
                    'Sec-Fetch-User: ?1',
                    'Upgrade-Insecure-Requests: 1',
                    'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36'
                ]
            ]);
            if ($res !== false) {
                $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
                $preg = '#<li><ahref=".(.*)"target="_blank"title="(.*)">.*</a>.*发布时间：<em>(.*)</em>.*</li>#isU';
                $obj = [];
                $arr = [];
                preg_match_all($preg, $res, $obj);
                $count = (is_countable($obj) && count($obj) > 0) ? count($obj[0]) : 0;
                if ($count > 0) {
                    for ($j = 0; $j < $count; $j++) {
                        $arr[$j]['url'] = $obj[1][$j];
                        $arr[$j]['title'] = $obj[2][$j];
                        $arr[$j]['beginDate'] = $obj[3][$j];
                    }
                    foreach ($arr as $k => $v) {
                        $data = [];
                        if (!preg_match('#(' . $in_keywords . ')#', $v['title'])) {
                            continue;
                        } else {
                            if (preg_match('#(' . $out_keywords . ')#', $v['title'])) {
                                continue;
                            } else {
                                $data['bidSiteId'] = 49;
                                $data['bidSiteName'] = '中国政府采购网';
                                $data['bidTitle'] = $v['title'];
                                $data['bidUrl'] = 'http://www.ccgp.gov.cn/cggg/dfgg/xjgg' . $v['url'];
                                $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                                $data['inDate'] = date('Y-m-d H:i:s');
                                $final_arr[] = $data;
                            }
                        }
                    }
                } else {
                    log_message('error', '招标采集：未匹配到数据 49 -> 中国政府采购网 -> 栏目 -> 地方公告 -> 询价公告');
                }
            } else {
                log_message('error', '招标采集：未获取到网页 49 -> 中国政府采购网 -> 栏目 -> 地方公告 -> 询价公告');
            }
            $url = 'http://www.ccgp.gov.cn/cggg/dfgg/jzxtpgg/'; // 栏目 地方公告 竞争性谈判
            $res = global_curl($url, [
                'httpheader' => [
                    'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9',
                    'Accept-Encoding: gzip, deflate, br',
                    'Accept-Language: zh-CN,zh;q=0.9',
                    'Cache-Control: no-cache',
                    'Connection: keep-alive',
                    'Cookie: ' . $cookie,
                    'Host: www.ccgp.gov.cn',
                    'Pragma: no-cache',
                    'sec-ch-ua: "Not_A Brand";v="99", "Google Chrome";v="109", "Chromium";v="109"',
                    'sec-ch-ua-mobile: ?0',
                    'sec-ch-ua-platform: "Windows"',
                    'Sec-Fetch-Dest: document',
                    'Sec-Fetch-Mode: navigate',
                    'Sec-Fetch-Site: none',
                    'Sec-Fetch-User: ?1',
                    'Upgrade-Insecure-Requests: 1',
                    'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36'
                ]
            ]);
            if ($res !== false) {
                $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
                $preg = '#<li><ahref=".(.*)"target="_blank"title="(.*)">.*</a>.*发布时间：<em>(.*)</em>.*</li>#isU';
                $obj = [];
                $arr = [];
                preg_match_all($preg, $res, $obj);
                $count = (is_countable($obj) && count($obj) > 0) ? count($obj[0]) : 0;
                if ($count > 0) {
                    for ($j = 0; $j < $count; $j++) {
                        $arr[$j]['url'] = $obj[1][$j];
                        $arr[$j]['title'] = $obj[2][$j];
                        $arr[$j]['beginDate'] = $obj[3][$j];
                    }
                    foreach ($arr as $k => $v) {
                        $data = [];
                        if (!preg_match('#(' . $in_keywords . ')#', $v['title'])) {
                            continue;
                        } else {
                            if (preg_match('#(' . $out_keywords . ')#', $v['title'])) {
                                continue;
                            } else {
                                $data['bidSiteId'] = 49;
                                $data['bidSiteName'] = '中国政府采购网';
                                $data['bidTitle'] = $v['title'];
                                $data['bidUrl'] = 'http://www.ccgp.gov.cn/cggg/dfgg/jzxtpgg' . $v['url'];
                                $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                                $data['inDate'] = date('Y-m-d H:i:s');
                                $final_arr[] = $data;
                            }
                        }
                    }
                } else {
                    log_message('error', '招标采集：未匹配到数据 49 -> 中国政府采购网 -> 栏目 -> 地方公告 -> 竞争性谈判');
                }
            } else {
                log_message('error', '招标采集：未获取到网页 49 -> 中国政府采购网 -> 栏目 -> 地方公告 -> 竞争性谈判');
            }
            $url = 'http://www.ccgp.gov.cn/cggg/dfgg/jzxcs/'; // 栏目 地方公告 竞争性磋商
            $res = global_curl($url, [
                'httpheader' => [
                    'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9',
                    'Accept-Encoding: gzip, deflate, br',
                    'Accept-Language: zh-CN,zh;q=0.9',
                    'Cache-Control: no-cache',
                    'Connection: keep-alive',
                    'Cookie: ' . $cookie,
                    'Host: www.ccgp.gov.cn',
                    'Pragma: no-cache',
                    'sec-ch-ua: "Not_A Brand";v="99", "Google Chrome";v="109", "Chromium";v="109"',
                    'sec-ch-ua-mobile: ?0',
                    'sec-ch-ua-platform: "Windows"',
                    'Sec-Fetch-Dest: document',
                    'Sec-Fetch-Mode: navigate',
                    'Sec-Fetch-Site: none',
                    'Sec-Fetch-User: ?1',
                    'Upgrade-Insecure-Requests: 1',
                    'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36'
                ]
            ]);
            if ($res !== false) {
                $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
                $preg = '#<li><ahref=".(.*)"target="_blank"title="(.*)">.*</a>.*发布时间：<em>(.*)</em>.*</li>#isU';
                $obj = [];
                $arr = [];
                preg_match_all($preg, $res, $obj);
                $count = (is_countable($obj) && count($obj) > 0) ? count($obj[0]) : 0;
                if ($count > 0) {
                    for ($j = 0; $j < $count; $j++) {
                        $arr[$j]['url'] = $obj[1][$j];
                        $arr[$j]['title'] = $obj[2][$j];
                        $arr[$j]['beginDate'] = $obj[3][$j];
                    }
                    foreach ($arr as $k => $v) {
                        $data = [];
                        if (!preg_match('#(' . $in_keywords . ')#', $v['title'])) {
                            continue;
                        } else {
                            if (preg_match('#(' . $out_keywords . ')#', $v['title'])) {
                                continue;
                            } else {
                                $data['bidSiteId'] = 49;
                                $data['bidSiteName'] = '中国政府采购网';
                                $data['bidTitle'] = $v['title'];
                                $data['bidUrl'] = 'http://www.ccgp.gov.cn/cggg/dfgg/jzxtpgg' . $v['url'];
                                $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                                $data['inDate'] = date('Y-m-d H:i:s');
                                $final_arr[] = $data;
                            }
                        }
                    }
                } else {
                    log_message('error', '招标采集：未匹配到数据 49 -> 中国政府采购网 -> 栏目 -> 地方公告 -> 竞争性磋商');
                }
            } else {
                log_message('error', '招标采集：未获取到网页 49 -> 中国政府采购网 -> 栏目 -> 地方公告 -> 竞争性磋商');
            }
        } else {
            log_message('error', '招标采集：未从数据库中获取到保存的cookie 49 -> 中国政府采购网');
        }
        // 115 广东教采云零散采购竞价平台
        for ($i = 1; $i < 5; $i++) {
            $url = 'https://www.gdedulscg.cn/home/bill/billlist.html?order=1&orderBy=1&userType=100&bidCate=1&smallCate=0&page=' . $i;
            $res = global_curl($url);
            if ($res !== false) {
                $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
                $preg = '#<divclass="list_data.*">.*<div.*title="(.*)".*onclick="see_info\((.*)\);">.*</div>.*<div.*class="list_title_time_datafl".*">(.*)</div>#isU';
                $obj = [];
                $arr = [];
                preg_match_all($preg, $res, $obj);
                $count = (is_countable($obj) && count($obj) > 0) ? count($obj[0]) : 0;
                if ($count > 0) {
                    for ($j = 0; $j < $count; $j++) {
                        $arr[$j]['url'] = $obj[2][$j];
                        $arr[$j]['title'] = $obj[1][$j];
                        $arr[$j]['beginDate'] = $obj[3][$j];
                    }
                    foreach ($arr as $k => $v) {
                        $data = [];
                        if (!preg_match('#(' . $in_keywords . ')#', $v['title'])) {
                            continue;
                        } else {
                            if (preg_match('#(' . $out_keywords . ')#', $v['title'])) {
                                continue;
                            } else {
                                $data['bidSiteId'] = 115;
                                $data['bidSiteName'] = '广东教采云零散采购竞价平台';
                                $data['bidTitle'] = $v['title'];
                                $data['bidUrl'] = 'https://www.gdedulscg.cn/home/bill/billdetail/billGuid/' . $v['url'] . '.html';
                                $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                                $data['inDate'] = date('Y-m-d H:i:s');
                                $final_arr[] = $data;
                            }
                        }
                    }
                } else {
                    log_message('error', '招标采集：未匹配到数据 115 -> 广东教采云零散采购竞价平台 -> 第'.$i.'页');
                }
            } else {
                log_message('error', '招标采集：未获取到网页 115 -> 广东教采云零散采购竞价平台 -> 第'.$i.'页');
            }
        }
        // 204 瞬速网上竞价平台
        $url = 'https://www.shuncai360.com/jjxq/5/iccgxq.chtml';
        $res = global_curl($url);
        if ($res !== false) {
            $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
            $res_temp = [];
            preg_match('#<table.*>(.*)</table>#isU', $res, $res_temp);
            $preg = '#<tr>.*<a.*title="(.*)".*href="(.*)".*rdate="(.*)".*</tr>#isU';
            $obj = [];
            $arr = [];
            is_countable($res_temp) && count($res_temp) > 0 && preg_match_all($preg, $res_temp[1], $obj);
            $count = (is_countable($obj) && count($obj) > 0) ? count($obj[0]) : 0;
            if ($count > 0) {
                for ($j = 0; $j < $count; $j++) {
                    $arr[$j]['url'] = $obj[2][$j];
                    $arr[$j]['title'] = $obj[1][$j];
                    $arr[$j]['beginDate'] = $obj[3][$j];
                }
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['title'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['title'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 204;
                            $data['bidSiteName'] = '瞬速网上竞价平台';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = 'https://www.shuncai360.com' . $v['url'];
                            $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 204 -> 瞬速网上竞价平台');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 204 -> 瞬速网上竞价平台');
        }
        // 205 招标采购服务平台
        $url = 'http://www.zbcgfw.com/gglist.json?random=1725098153326&ggname=&type=zbgg&page=1&pageSize=99';
        $res = global_curl($url);
        if ($res !== false) {
            $obj = json_decode($res, true);
            $arr = $obj['rows'] ?? [];
            if (is_array($arr) && count($arr) > 0) {
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['ggname'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['ggname'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 205;
                            $data['bidSiteName'] = '招标采购服务平台';
                            $data['bidTitle'] = $v['ggname'];
                            $data['bidUrl'] = 'http://www.zbcgfw.com/hbiddingWeb/pages/jyinfo/detail.html?ggcode='.$v['ggcode'].'&type='.$v['ggtype'];
                            $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['ggfbtime']));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 205 -> 招标采购服务平台');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 205 -> 招标采购服务平台');
        }
        // 256 招拍挂资源市场化配置交易平台
        $url = 'https://www.xmzpg.com/freecms/rest/v1/notice/selectInfoMoreChannel.do?siteId=a9b90a0d-faa9-462d-8c64-ed117cfebfdd&channel=98a12ce9-d577-45c1-8016-1c9420e09cb9&title=&purchaser=&purchaseManner=&openTenderCode=&operationStartTime=&operationEndTime=&agency=&noticeType=001011,001012,001013,001014,001015,001016,001019,001091&purchaseNature=&selectTimeName=noticeTime&currPage=1&pageSize=10';
        $res = global_curl($url, [
            'httpheader' => [
                'Accept: */*',
                'Accept-Language: zh-CN,zh;q=0.9,en;q=0.8,en-GB;q=0.7,en-US;q=0.6',
                'Cache-Control: no-cache',
                'Connection: keep-alive',
                'Content-Type: application/json;charset=utf-8',
                'Host: www.xmzpg.com',
                'Pragma: no-cache',
                'Referer: https://www.xmzpg.com/freecms/site/xmcms/gggs/index.html',
                'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/104.0.5112.81 Safari/537.36 Edg/104.0.1293.54'
            ]
        ]);
        if ($res !== false) {
            $obj = json_decode($res, true);
            $arr = $obj['data'] ?? [];
            if (is_array($arr) && count($arr) > 0) {
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['title'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['title'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 256;
                            $data['bidSiteName'] = '招拍挂资源市场化配置交易平台';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = 'https://www.xmzpg.com/freecms'.$v['htmlpath'].'?noticeType='.$v['noticeType'].'&noticeId='.$v['id'];
                            $data['beginDate'] = $v['noticeTime'];
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 256 -> 招拍挂资源市场化配置交易平台');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 256 -> 招拍挂资源市场化配置交易平台');
        }
        // 263 恒德易电子交易平台
        $url = 'https://eps.gdebidding.com/gg/queryjjggList?pageName=tdzz&&ggType=0&&page=1&&limit=20&&ggStartTime=&&ggEndTime='; // 网上竞价
        $res = global_curl($url);
        if ($res !== false) {
            $obj = json_decode($res, true);
            $arr = $obj['data'] ?? [];
            if (is_array($arr) && count($arr) > 0) {
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['ggName'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['ggName'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 263;
                            $data['bidSiteName'] = '恒德易电子交易平台';
                            $data['bidTitle'] = $v['ggName'];
                            $data['bidUrl'] = 'https://eps.gdebidding.com/gg/cmsGGDetailjj?guid='.$v['ggGuid'].'&&pageName=tdzz';
                            $data['beginDate'] = date('Y-m-d H:i:s', intval($v['publishTime'] / 1000));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 263 -> 恒德易电子交易平台 -> 网上竞价');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 263 -> 恒德易电子交易平台 -> 网上竞价');
        }
        $url = 'https://eps.gdebidding.com/gg/queryJyggList?pageName=xbcg&&ggType=0&&page=1&&limit=20&&ggStartTime=&&ggEndTime='; // 简易采购
        $res = global_curl($url);
        if ($res !== false) {
            $obj = json_decode($res, true);
            $arr = $obj['data'] ?? [];
            if (is_array($arr) && count($arr) > 0) {
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['ggName'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['ggName'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 263;
                            $data['bidSiteName'] = '恒德易电子交易平台';
                            $data['bidTitle'] = $v['ggName'];
                            $data['bidUrl'] = 'https://eps.gdebidding.com/gg/cmsGGDetailcg?guid='.$v['ggGuid'].'&&pageName=xbcg';
                            $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['publishTime'] / 1000));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 263 -> 恒德易电子交易平台 -> 简易采购');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 263 -> 恒德易电子交易平台 -> 简易采购');
        }
        // 265 新点电子交易平台
        $url = 'https://www.etrading.cn/inteligentsearch_wz/rest/esinteligentsearch/getFullTextDataNew';
        $equals = [
            '货物' => '002002001',
            '服务' => '002003001'
        ];
        foreach ($equals as $index => $equal) {
            $res = global_curl($url, [
                'post' =>  1,
                'postfields' => '{"token":"","pn":0,"rn":100,"sdt":"","edt":"","wd":"","inc_wd":"","exc_wd":"","fields":"title;infod","cnum":"002","sort":"{infodate:0}","ssort":"title","cl":500,"terminal":"","condition":[{"equal":"'.$equal.'","fieldName":"categorynum","isLike":"true","likeType":"2"},{"equal":"","fieldName":"infod","isLike":"true","likeType":"2"}],"time":[{"fieldName":"webdate","startTime":"'.date('Y-m-d', strtotime('-7 days')).' 00:00:00","endTime":"'.date('Y-m-d').' 23:59:59"}],"highlights":"title;content","statistics":null,"unionCondition":null,"accuracy":"","noParticiple":"","searchRange":null,"isBusiness":"1"}'
            ]);
            if ($res !== false) {
                $obj = json_decode($res, true);
                $obj = json_decode($obj['content'], true);
                $arr = $obj['result']['records'] ?? [];
                if (is_array($arr) && count($arr) > 0) {
                    foreach ($arr as $k => $v) {
                        $data = [];
                        if (!preg_match('#(' . $in_keywords . ')#', $v['title'])) {
                            continue;
                        } else {
                            if (preg_match('#(' . $out_keywords . ')#', $v['title'])) {
                                continue;
                            } else {
                                $data['bidSiteId'] = 265;
                                $data['bidSiteName'] = '新点电子交易平台';
                                $data['bidTitle'] = $v['title'];
                                $data['bidUrl'] = 'https://www.etrading.cn/noticeDetails_jyxx.html?infoid='.$v['infoid'].'&categorynum='.$v['categorynum'];
                                $data['beginDate'] = $v['webdate'];
                                $data['inDate'] = date('Y-m-d H:i:s');
                                $final_arr[] = $data;
                            }
                        }
                    }
                } else {
                    log_message('error', '招标采集：未匹配到数据 265 -> 新点电子交易平台 -> ' . $index);
                }
            } else {
                log_message('error', '招标采集：未获取到网页 265 -> 新点电子交易平台 ' . $index);
            }
        }
        // 268 航天电子采购平台 【特殊通过浏览器刷新插件+自定义js获取数据后发送到本系统api并存数据库，每次采集从数据库拿】
        $res = $this->db->table('bid_temp')->select('res')->where('site', '航天电子采购平台')->get()->getRowArray()['res'] ?? '';
        if ($res) {
            $preg = '#<divclass="menu_content_layer6_listhandsearchContentSc.*onclick="to_url\(\'(.*)\'.*<h2>(.*)</h2>.*发布日期：(.*)</h3>.*</div>#isU';
            $obj = [];
            $arr = [];
            preg_match_all($preg, $res, $obj);
            $count = (is_countable($obj) && count($obj) > 0) ? count($obj[0]) : 0;
            if ($count > 0) {
                for ($j = 0; $j < $count; $j++) {
                    $arr[$j]['url'] = $obj[1][$j];
                    $arr[$j]['title'] = $obj[2][$j];
                    $arr[$j]['beginDate'] = $obj[3][$j];
                }
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['title'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['title'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 268;
                            $data['bidSiteName'] = '航天电子采购平台';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = 'https://bd.ispacechina.com' . $v['url'];
                            $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 268 -> 航天电子采购平台');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 268 -> 航天电子采购平台');
        }
        // 324 优胜招标
        $urls = [
            '政府采购招标公告' => 'https://fjyszb.com/api/fetchPostList?slug[]=tender-notice_government-procurement&pageNumber=1',
            '网上竞价招标公告' => 'https://fjyszb.com/api/fetchPostList?slug[]=tender-notice_bidding&pageNumber=1'
        ];
        foreach ($urls as $index => $url) {
            $res = global_curl($url);
            if ($res !== false) {
                $obj = json_decode($res, true);
                $arr = $obj['data']['list'] ?? [];
                if (is_array($arr) && count($arr) > 0) {
                    foreach ($arr as $k => $v) {
                        $data = [];
                        if (!preg_match('#(' . $in_keywords . ')#', $v['title'])) {
                            continue;
                        } else {
                            if (preg_match('#(' . $out_keywords . ')#', $v['title'])) {
                                continue;
                            } else {
                                $data['bidSiteId'] = 324;
                                $data['bidSiteName'] = '优胜招标';
                                $data['bidTitle'] = $v['title'];
                                $data['bidUrl'] = 'https://fjyszb.com/post/'.$v['_id'];
                                $data['beginDate'] = $v['time'];
                                $data['inDate'] = date('Y-m-d H:i:s');
                                $final_arr[] = $data;
                            }
                        }
                    }
                } else {
                    log_message('error', '招标采集：未匹配到数据 324 -> 优胜招标 -> ' . $index);
                }
            } else {
                log_message('error', '招标采集：未获取到网页 324 -> 优胜招标 -> ' . $index);
            }
        }
        // 333 惠招标
        $url = 'http://www.hbidding.com/web/index/information/getjyinfo.json?random=1734487946809&type=zbgg&citycode=&zbdl=&leixing=%E6%94%BF%E5%BA%9C%E9%87%87%E8%B4%AD&keyword=&maxrow=99';
        $res = global_curl($url);
        if ($res !== false) {
            $obj = json_decode($res, true);
            $arr = $obj['tdata'] ?? [];
            if (is_array($arr) && count($arr) > 0) {
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['GGNAME'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['GGNAME'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 333;
                            $data['bidSiteName'] = '惠招标';
                            $data['bidTitle'] = $v['GGNAME'];
                            $data['bidUrl'] = 'http://www.hbidding.com/hbiddingWeb/pages/jyinfo/detail.html?active=0&&infoid='.$v['GGCODE'].'&&categoryid=zbgg';
                            $data['beginDate'] = $v['GGFBTIME'];
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 333 -> 惠招标');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 333 -> 惠招标');
        }
        // 336 河南省招标投标协会
        $url = 'https://www.hnzbcg.cn/zhaobiao/list1.html';
        $res = global_curl($url);
        if ($res !== false) {
            $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
            $preg = '#<li><divclass="right">(.*)</div>.*<ahref="(.*)".*>(.*)</a></div></li>#isU';
            $obj = [];
            $arr = [];
            preg_match_all($preg, $res, $obj);
            $count = (is_countable($obj) && count($obj) > 0) ? count($obj[0]) : 0;
            if ($count > 0) {
                for ($j = 0; $j < $count; $j++) {
                    $arr[$j]['url'] = $obj[2][$j];
                    $arr[$j]['title'] = $obj[3][$j];
                    $arr[$j]['beginDate'] = $obj[1][$j];
                }
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['title'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['title'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 336;
                            $data['bidSiteName'] = '河南省招标投标协会';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = 'https://www.hnzbcg.cn'.$v['url'];
                            $data['beginDate'] = $v['beginDate'];
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 336 -> 河南省招标投标协会');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 336 -> 河南省招标投标协会');
        }
        // 340 数字化招采平台（合并）
        $url = 'https://zbpt.yit.edu.cn/yicai-base/th_project/bidding.html?page=';
        for ($i = 1; $i <= 5; $i++) {
            $res = global_curl($url . $i);
            if ($res !== false) {
                $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
                $res_temp = [];
                preg_match('#<ulclass="table-body-listclearfix"id="zhaobiao">(.*)</ul>#isU', $res, $res_temp);
                $preg = '#<liclass=".*"><a.*href="(.*)".*title="(.*)">.*</span></a><span.*>(.*)</span>.*</li>#isU';
                $obj = [];
                $arr = [];
                is_countable($res_temp) && count($res_temp) > 0 && preg_match_all($preg, $res_temp[1], $obj);
                $count = (is_countable($obj) && count($obj) > 0) ? count($obj[0]) : 0;
                if ($count > 0) {
                    for ($j = 0; $j < $count; $j++) {
                        $arr[$j]['url'] = $obj[1][$j];
                        $arr[$j]['title'] = $obj[2][$j];
                        $arr[$j]['beginDate'] = $obj[3][$j];
                    }
                    foreach ($arr as $k => $v) {
                        $data = [];
                        if (!preg_match('#(' . $in_keywords . ')#', $v['title'])) {
                            continue;
                        } else {
                            if (preg_match('#(' . $out_keywords . ')#', $v['title'])) {
                                continue;
                            } else {
                                $data['bidSiteId'] = 340;
                                $data['bidSiteName'] = '数字化招采平台（合并）';
                                $data['bidTitle'] = $v['title'];
                                $data['bidUrl'] = str_replace('http://', 'https://', $v['url']);
                                $data['beginDate'] = $v['beginDate'];
                                $data['inDate'] = date('Y-m-d H:i:s');
                                $final_arr[] = $data;
                            }
                        }
                    }
                } else {
                    // 换个其他学校的域名
                    $url = 'https://zbpt.cqcst.edu.cn/yicai-base/th_project/bidding.html?page=';
                    $res = global_curl($url . $i);
                    if ($res !== false) {
                        $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
                        $res_temp = [];
                        preg_match('#<ulclass="table-body-listclearfix"id="zhaobiao">(.*)</ul>#isU', $res, $res_temp);
                        $preg = '#<liclass=".*"><a.*href="(.*)".*title="(.*)">.*</span></a><span.*>(.*)</span>.*</li>#isU';
                        $obj = [];
                        $arr = [];
                        is_countable($res_temp) && count($res_temp) > 0 && preg_match_all($preg, $res_temp[1], $obj);
                        $count = (is_countable($obj) && count($obj) > 0) ? count($obj[0]) : 0;
                        if ($count > 0) {
                            for ($j = 0; $j < $count; $j++) {
                                $arr[$j]['url'] = $obj[1][$j];
                                $arr[$j]['title'] = $obj[2][$j];
                                $arr[$j]['beginDate'] = $obj[3][$j];
                            }
                            foreach ($arr as $k => $v) {
                                $data = [];
                                if (!preg_match('#(' . $in_keywords . ')#', $v['title'])) {
                                    continue;
                                } else {
                                    if (preg_match('#(' . $out_keywords . ')#', $v['title'])) {
                                        continue;
                                    } else {
                                        $data['bidSiteId'] = 340;
                                        $data['bidSiteName'] = '数字化招采平台（合并）';
                                        $data['bidTitle'] = $v['title'];
                                        $data['bidUrl'] = str_replace('http://', 'https://', $v['url']);
                                        $data['beginDate'] = $v['beginDate'];
                                        $data['inDate'] = date('Y-m-d H:i:s');
                                        $final_arr[] = $data;
                                    }
                                }
                            }
                        } else {
                            log_message('error', '招标采集：未匹配到数据 340 -> 数字化招采平台（合并）');
                        }
                    } else {
                        log_message('error', '招标采集：未获取到网页 340 -> 数字化招采平台（合并）');
                    }
                }
            } else {
                // 换个其他学校的域名
                $url = 'https://zbpt.cqcst.edu.cn/yicai-base/th_project/bidding.html?page=';
                $res = global_curl($url . $i);
                if ($res !== false) {
                    $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
                    $res_temp = [];
                    preg_match('#<ulclass="table-body-listclearfix"id="zhaobiao">(.*)</ul>#isU', $res, $res_temp);
                    $preg = '#<liclass=".*"><a.*href="(.*)".*title="(.*)">.*</span></a><span.*>(.*)</span>.*</li>#isU';
                    $obj = [];
                    $arr = [];
                    is_countable($res_temp) && count($res_temp) > 0 && preg_match_all($preg, $res_temp[1], $obj);
                    $count = (is_countable($obj) && count($obj) > 0) ? count($obj[0]) : 0;
                    if ($count > 0) {
                        for ($j = 0; $j < $count; $j++) {
                            $arr[$j]['url'] = $obj[1][$j];
                            $arr[$j]['title'] = $obj[2][$j];
                            $arr[$j]['beginDate'] = $obj[3][$j];
                        }
                        foreach ($arr as $k => $v) {
                            $data = [];
                            if (!preg_match('#(' . $in_keywords . ')#', $v['title'])) {
                                continue;
                            } else {
                                if (preg_match('#(' . $out_keywords . ')#', $v['title'])) {
                                    continue;
                                } else {
                                    $data['bidSiteId'] = 340;
                                    $data['bidSiteName'] = '数字化招采平台（合并）';
                                    $data['bidTitle'] = $v['title'];
                                    $data['bidUrl'] = str_replace('http://', 'https://', $v['url']);
                                    $data['beginDate'] = $v['beginDate'];
                                    $data['inDate'] = date('Y-m-d H:i:s');
                                    $final_arr[] = $data;
                                }
                            }
                        }
                    } else {
                        log_message('error', '招标采集：未匹配到数据 340 -> 数字化招采平台（合并）');
                    }
                } else {
                    log_message('error', '招标采集：未获取到网页 340 -> 数字化招采平台（合并）');
                }
            }
        }
        // 344 优采通电子招投标交易平台 【特殊通过浏览器刷新插件+自定义js获取数据后发送到本系统api并存数据库，每次采集从数据库拿】
        $res = $this->db->table('bid_temp')->select('res')->where('site', '优采通电子招投标交易平台招标采购')->get()->getRowArray()['res'] ?? '';
        if($res) {
            $obj = json_decode($res, true);
            $arr = $obj ?? [];
            if (is_array($arr) && count($arr) > 0) {
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['projectName'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['projectName'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 344;
                            $data['bidSiteName'] = '优采通电子招投标交易平台';
                            $data['bidTitle'] = $v['projectName'];
                            $data['bidUrl'] = 'https://youcai.fjyszb.com/project?id='.$v['id'].'&type=01';
                            $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['releaseTime']));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 344 -> 优采通电子招投标交易平台 -> 招标采购');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 344 -> 优采通电子招投标交易平台 -> 招标采购');
        }
        $res = $this->db->table('bid_temp')->select('res')->where('site', '优采通电子招投标交易平台网上竞价')->get()->getRowArray()['res'] ?? '';
        if($res) {
            $obj = json_decode($res, true);
            $arr = $obj ?? [];
            if (is_array($arr) && count($arr) > 0) {
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['projectName'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['projectName'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 344;
                            $data['bidSiteName'] = '优采通电子招投标交易平台';
                            $data['bidTitle'] = $v['projectName'];
                            $data['bidUrl'] = 'https://youcai.fjyszb.com/project-bidprice?id='.$v['id'].'&type=01';
                            $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['releaseTime']));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 344 -> 优采通电子招投标交易平台 -> 网上竞价');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 344 -> 优采通电子招投标交易平台 -> 网上竞价');
        }
        // 347 招标网 【特殊通过浏览器刷新插件+自定义js获取数据后发送到本系统api并存数据库，每次采集从数据库拿】
        $res = $this->db->table('bid_temp')->select('res')->where('site', '招标网')->get()->getRowArray()['res'] ?? '';
        if ($res) {
            $preg = '#<divclass="bid_newsbid_news_tb">.*<span>(.*)</span></p>.*<a.*href="(.*)".*title="(.*)".*>.*</a></div><p.*>.*</p><p.*>.*</p></div></div>#isU';
            $obj = [];
            $arr = [];
            preg_match_all($preg, $res, $obj);
            $count = (is_countable($obj) && count($obj) > 0) ? count($obj[0]) : 0;
            if ($count > 0) {
                for ($j = 0; $j < $count; $j++) {
                    $arr[$j]['url'] = $obj[2][$j];
                    $arr[$j]['title'] = $obj[3][$j];
                    $arr[$j]['beginDate'] = $obj[1][$j];
                }
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['title'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['title'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 347;
                            $data['bidSiteName'] = '招标网';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = $v['url'];
                            $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 347 -> 招标网');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 347 -> 招标网');
        }
        // 348 宝华智慧招标共享平台
        $url = 'https://www.baohuabidding.cn/EWB-FRONT-5.4.2-sp1/rest/GgSearchActionNew/getJyInfoMationList';
        $res = global_curl($url, [
            'post' => 1,
            'postfields' => 'siteGuid=7eb5f7f1-9041-43ad-8e13-8fcb82ea831a&categoryNum=018001002002&kw=&zbjg=&pageIndex=0&pageSize=50&YZM=&ImgGuid=',
            'httpheader' => [
                'Accept: application/json, text/javascript, */*; q=0.01',
                'Accept-Language: zh-CN,zh;q=0.9,en;q=0.8,en-GB;q=0.7,en-US;q=0.6',
                'Cache-Control: no-cache',
                'Connection: keep-alive',
                'Content-Length: 118',
                'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
                'Host: www.baohuabidding.cn',
                'Origin: https://www.baohuabidding.cn',
                'Pragma: no-cache',
                'Referer: https://www.baohuabidding.cn/shbhgjzb/zbzx/bidlist.html',
                'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/117.0.0.0 Safari/537.36 Edg/117.0.2045.41'
            ]
        ]);
        if ($res !== false) {
            $obj = json_decode($res, true);
            $arr = $obj['custom'] ?? [];
            if (is_array($arr) && count($arr) > 0) {
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['title'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['title'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 348;
                            $data['bidSiteName'] = '宝华智慧招标共享平台';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = 'https://www.baohuabidding.cn/shbhgjzb'.$v['infourl'];
                            $data['beginDate'] = $v['infodate'];
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 348 -> 宝华智慧招标共享平台');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 348 -> 宝华智慧招标共享平台');
        }
        // 359 三门县小额招投标交易平台
        $url = 'https://www.smgtxztb.com/api/front/bidcontent/contentNoticeList';
        $res = global_curl($url, [
            'post' => 1,
            'postfields' => '{"page":1,"rows":30,"tradeType":"4","codes":"9005004001","projectName":"","dateType":""}'
        ]);
        if ($res !== false) {
            $obj = json_decode($res, true);
            $arr = $obj['data']['rows'] ?? [];
            if (is_array($arr) && count($arr) > 0) {
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['title'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['title'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 359;
                            $data['bidSiteName'] = '三门县小额招投标交易平台';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = 'https://www.smgtxztb.com/tradeInfo/detail?id='.$v['id'].'&code=401&navTag=401';
                            $data['beginDate'] = $v['publishTime'];
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 359 -> 三门县小额招投标交易平台');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 359 -> 三门县小额招投标交易平台');
        }
        // 369 全国招标采购公共服务平台
        $url = 'http://www.hnzbcgxxw.com/list/4.html';
        $res = global_curl($url);
        if ($res !== false) {
            $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
            $preg = '#<liclass="info_list_li"><p><a.*href="(.*)".*>(.*)</a><span>(.*)</span></p></li>#isU';
            $obj = [];
            $arr = [];
            preg_match_all($preg, $res, $obj);
            $count = (is_countable($obj) && count($obj) > 0) ? count($obj[0]) : 0;
            if ($count > 0) {
                for ($j = 0; $j < $count; $j++) {
                    $arr[$j]['url'] = $obj[1][$j];
                    $arr[$j]['title'] = $obj[2][$j];
                    $arr[$j]['beginDate'] = $obj[3][$j];
                }
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['title'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['title'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 369;
                            $data['bidSiteName'] = '全国招标采购公共服务平台';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = 'http://www.hnzbcgxxw.com'.$v['url'];
                            $data['beginDate'] = $v['beginDate'];
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 369 -> 全国招标采购公共服务平台');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 369 -> 全国招标采购公共服务平台');
        }
        // 386 常州政采云
        $url = 'http://www.czzczb.com/html/ns/zfcgxx_cggg/index.html';
        $res = global_curl($url);
        if ($res !== false) {
            $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
            $preg = '#<tr><tdwidth="86%"valign="middle"><ahref="(.*)">(.*)</a></td><td.*>(.*)</td></tr>#isU';
            $obj = [];
            $arr = [];
            preg_match_all($preg, $res, $obj);
            $count = (is_countable($obj) && count($obj) > 0) ? count($obj[0]) : 0;
            if ($count > 0) {
                for ($j = 0; $j < $count; $j++) {
                    $arr[$j]['url'] = $obj[1][$j];
                    $arr[$j]['title'] = $obj[2][$j];
                    $arr[$j]['beginDate'] = $obj[3][$j];
                }
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['title'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['title'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 386;
                            $data['bidSiteName'] = '常州政采云';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = $v['url'];
                            $data['beginDate'] = $v['beginDate'];
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 386 -> 常州政采云');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 386 -> 常州政采云');
        }
        // 421 山西省招标投标协会
        $url = 'https://www.sxtba.com/prod-api/web/purchaseNotice/list?releaseType=B&pageNum=1&pageSize=50';
        $res = global_curl($url);
        if ($res !== false) {
            $obj = json_decode($res, true);
            $arr = $obj['rows'] ?? [];
            if (is_array($arr) && count($arr) > 0) {
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['noticeTitle'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['noticeTitle'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 421;
                            $data['bidSiteName'] = '山西省招标投标协会';
                            $data['bidTitle'] = $v['noticeTitle'];
                            $data['bidUrl'] = 'https://www.sxtba.com/home/zcInfoChildDetail?id='.$v['id'].'&noticeListRoute='.$v['route'].'&projectTypes=purchasing';
                            $data['beginDate'] = $v['releaseTime'];
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 421 -> 山西省招标投标协会');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 421 -> 山西省招标投标协会');
        }
        // 429 东北新闻网
        $url = 'https://liaoning.nen.com.cn/network/liaoningnews/lnnewszhuantibd/zfcgls/index.shtml';
        $res = global_curl($url);
        if ($res !== false) {
            $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
            $preg = '#<liclass="z-flex-jcsb">.*<a.*href="(.*)".*>(.*)</a></div><div>(.*)</div></li>#isU';
            $obj = [];
            $arr = [];
            preg_match_all($preg, $res, $obj);
            $count = (is_countable($obj) && count($obj) > 0) ? count($obj[0]) : 0;
            if ($count > 0) {
                for ($j = 0; $j < $count; $j++) {
                    $arr[$j]['url'] = $obj[1][$j];
                    $arr[$j]['title'] = $obj[2][$j];
                    $arr[$j]['beginDate'] = $obj[3][$j];
                }
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['title'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['title'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 429;
                            $data['bidSiteName'] = '东北新闻网';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = $v['url'];
                            $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 429 -> 东北新闻网');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 429 -> 东北新闻网');
        }
        // 479 徽易采电子交易平台
        $url = 'https://huiyicai.cn/inteligentsearch/rest/esinteligentsearch/getFullTextDataNew';
        $res = global_curl($url, [
            'post' => 1,
            'postfields' => '{"esdsid":"1","token":"","pn":0,"rn":50,"sdt":"","edt":"","wd":" ","inc_wd":"","exc_wd":"","fields":"title","cnum":"001","sort":"{\"webdate\":\"0\"}","ssort":"title","cl":1000,"cutIngore":"title;linkurl","terminal":"","condition":[{"fieldName":"categorynum","equal":"001002002","notEqual":null,"equalList":null,"notEqualList":null,"isLike":true,"likeType":2}],"time":[],"highlights":"title","statistics":null,"unionCondition":[],"accuracy":"","noParticiple":"1","searchRange":[],"isBusiness":"1"}',
            'httpheader' => [
                'Accept: application/json, text/javascript, */*; q=0.01',
                'Accept-Encoding: gzip, deflate, br, zstd',
                'Accept-Language: zh-CN,zh;q=0.9,en;q=0.8,en-GB;q=0.7,en-US;q=0.6',
                'Cache-Control: no-cache',
                'Connection: keep-alive',
                'Content-Length: 496',
                'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
                'Cookie: _font_size_ratio_=1.0; userGuid=-703107112; oauthClientId=admin; oauthPath=http://10.110.11.20:8080/EpointWebBuilder; oauthLoginUrl=http://127.0.0.1:1112/membercenter/login.html?redirect_uri=; oauthLogoutUrl=; noOauthRefreshToken=undefined; noOauthAccessToken=undefined',
                'Host: huiyicai.cn',
                'Origin: https://huiyicai.cn',
                'Pragma: no-cache',
                'Referer: https://huiyicai.cn/qybfront/jyxx/001002/001002002/trade_info.html',
                'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36 Edg/148.0.0.0'
            ]
        ]);
        if ($res !== false) {
            $obj = json_decode($res, true);
            $obj = json_decode($obj['content'], true);
            $arr = $obj['result']['records'] ?? [];
            if (is_array($arr) && count($arr) > 0) {
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['title'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['title'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 479;
                            $data['bidSiteName'] = '徽易采电子交易平台';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = 'https://huiyicai.cn/qybfront'.$v['linkurl'];
                            $data['beginDate'] = $v['infodate'];
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 479 -> 徽易采电子交易平台');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 479 -> 徽易采电子交易平台');
        }
        // 483 南方采购交易平台
        $url = 'http://120.78.91.121:9600/web/web/getPageListForWeb?currentPage=1&offset=0&limit=20&cosid=1';
        $res = global_curl($url, [
            'httpheader' => [
                'Accept: application/json, text/plain, */*',
                'Accept-Encoding: gzip, deflate',
                'Accept-Language: zh-CN,zh;q=0.9,en;q=0.8,en-GB;q=0.7,en-US;q=0.6',
                'Authorization: null',
                'Cache-Control: no-cache',
                'Connection: keep-alive',
                'Host: 120.78.91.121:9600',
                'Origin: http://j.eebidding.com',
                'Pragma: no-cache',
                'Referer: http://j.eebidding.com/',
                'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0'
            ]
        ]);
        if ($res !== false) {
            $obj = json_decode($res, true);
            $arr = $obj['data']['list'] ?? [];
            if (is_array($arr) && count($arr) > 0) {
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['pjnoticetitle'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['pjnoticetitle'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 483;
                            $data['bidSiteName'] = '南方采购交易平台';
                            $data['bidTitle'] = $v['pjnoticetitle'];
                            $data['bidUrl'] = 'http://j.eebidding.com/#/pjnoticeInfo?pjnoticeid='.$v['pjnoticeid'].'&projectid='.$v['projectid'];
                            $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['pjnoticetime']));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 483 -> 南方采购交易平台');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 483 -> 南方采购交易平台');
        }
        // 484 E招冀成电子招标交易平台
        $url = 'https://www.hebeibidding.com/EpointWebBuilder/rest/GgSearchAction/getInfoMationList';
        $res = global_curl($url, [
            'post' => 1,
            'postfields' => 'siteGuid=7eb5f7f1-9041-43ad-8e13-8fcb82ea831a&categoryNum=007002001&xiaqucode=&pageIndex=0&pageSize=30&wd=&YZM=&ImgGuid=',
            'httpheader' => [
                'Accept: application/json, text/javascript, */*; q=0.01',
                'Accept-Language: zh-CN,zh;q=0.9,en;q=0.8,en-GB;q=0.7,en-US;q=0.6',
                'Cache-Control: no-cache',
                'Connection: keep-alive',
                'Content-Length: 119',
                'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
                'Host: www.hebeibidding.com',
                'Origin: https://www.hebeibidding.com',
                'Pragma: no-cache',
                'Referer: https://www.hebeibidding.com/',
                'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/123.0.0.0 Safari/537.36 Edg/123.0.0.0'
            ]
        ]);
        if ($res !== false) {
            $obj = json_decode($res, true);
            $arr = $obj['custom'] ?? [];
            if (is_array($arr) && count($arr) > 0) {
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['title'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['title'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 484;
                            $data['bidSiteName'] = 'E招冀成电子招标交易平台';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = 'https://www.hebeibidding.com'.$v['infourl'];
                            $data['beginDate'] = $v['infodate'];
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 484 -> E招冀成电子招标交易平台');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 484 -> E招冀成电子招标交易平台');
        }
        // 489 惠泽招电子招投标交易平台
        $url = 'https://www.huizezhao.cn/web/index/information/getjyinfopg.json?ts='.intval(microtime(true) * 1000).'&searchtype=ggname&searchcontent=&type=zbgg&leixing=%E5%85%A8%E9%83%A8&citycode=&featurecode=nxhzz&pageindex=1&sortname=&sortorder=desc&pagesize=50';
        $res = global_curl($url);
        if ($res !== false) {
            $obj = json_decode($res, true);
            $arr = $obj['rows'] ?? [];
            if (is_array($arr) && count($arr) > 0) {
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['ggname'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['ggname'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 489;
                            $data['bidSiteName'] = '惠泽招电子招投标交易平台';
                            $data['bidTitle'] = $v['ggname'];
                            $data['bidUrl'] = 'https://www.huizezhao.cn/business/detail/?ggcode='.$v['ggcode'].'&categoryid='.$v['ggtype'].'&featurecode=nxhzz';
                            $data['beginDate'] = $v['ggfbtime'];
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 489 -> 惠泽招电子招投标交易平台');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 489 -> 惠泽招电子招投标交易平台');
        }
        // 497 云采招阳
        $url = 'https://www.ebidcn.com/bulletin/search/1/ac4a71b5-be38-4b75-b03a-a93100b8967b/0/0/0/0/0/0/0/50/1.html';
        $res = global_curl($url);
        if ($res !== false) {
            $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
            $preg = '#<divclass="v3-notice-item"><div.*><a.*href="(.*)".*><span>.*</span>(.*)</a></div><ul>.*开始时间：(.*)</li>#isU';
            $obj = [];
            $arr = [];
            preg_match_all($preg, $res, $obj);
            $count = (is_countable($obj) && count($obj) > 0) ? count($obj[0]) : 0;
            if ($count > 0) {
                for ($j = 0; $j < $count; $j++) {
                    $arr[$j]['url'] = $obj[1][$j];
                    $arr[$j]['title'] = $obj[2][$j];
                    $arr[$j]['beginDate'] = $obj[3][$j];
                }
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['title'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['title'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 497;
                            $data['bidSiteName'] = '云采招阳';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = 'https://www.ebidcn.com' . $v['url'];
                            $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 497 -> 云采招阳');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 497 -> 云采招阳');
        }
        // 512 陕西省政府采购服务协会
        $url = 'https://www.sxszfcgfwxh.cn/api/etsweb/web/vtradeannouncement/page?noticeName=&projectClassifyCode=&noticeNature=1&bulletinType=&page=1&limit=100&timeType=0&cityCode=&dataType=1&_t=1736388756822';
        $res = global_curl($url);
        if ($res !== false) {
            $obj = json_decode($res, true);
            $arr = $obj['data']['list'] ?? [];
            if (is_array($arr) && count($arr) > 0) {
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['noticeName'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['noticeName'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 512;
                            $data['bidSiteName'] = '陕西省政府采购服务协会';
                            $data['bidTitle'] = $v['noticeName'];
                            $data['bidUrl'] = 'http://sxszfcgfwxh.cn/#/tender-vTradeAnnouncement-anno?tenderProjectId='.$v['tenderProjectId'].'&rootId=undefined';
                            $data['beginDate'] = $v['noticeTime'];
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 512 -> 陕西省政府采购服务协会');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 512 -> 陕西省政府采购服务协会');
        }
        // 545 八戒公采
        $url = 'https://bridgezhyc.zbj.com/api/platform/queryPurchase';
        $res = global_curl($url, [
            'post' => 1,
            'postfields' => '{"data":{"purchasetypeLevel1Id":null,"purchasetypeLevel2Id":null,"purchasetypeLevel3Id":null,"purchasetypeLevel1IdName":"","purchasetypeLevel2IdName":"","purchasetypeLevel3IdName":"","town":"","expirytTimeSort":0,"priceSort":0,"releaseTimeSort":0,"highPrice":null,"lowPrice":null,"page":1,"biddingType":0,"purchaseState":5,"baseAppId":"99","appId":"99","frontCat1Id":null,"frontCat2Id":null,"frontCat3Id":null,"frontCat4Id":null,"frontCat5Id":null,"frontCat6Id":null,"pageSize":99,"cityCode":"","province":"","city":"","otherTown":"","defaultRegion":"","townType":1,"title":""}}',
            'httpheader' => [
                'Accept: application/json, text/plain, */*',
                'Accept-Language: zh-CN,zh;q=0.9,en;q=0.8,en-GB;q=0.7,en-US;q=0.6',
                'Cache-Control: no-cache',
                'Content-Length: 578',
                'Content-Type: application/json',
                'Origin: https://cg.zbj.com',
                'Pragma: no-cache',
                'Referer: https://cg.zbj.com/buyerHall',
                'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36 Edg/126.0.0.0'
            ]
        ]);
        if ($res !== false) {
            $obj = json_decode($res, true);
            $arr = $obj['data']['data'] ?? [];
            if (is_array($arr) && count($arr) > 0) {
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['purchaseDemandName'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['purchaseDemandName'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 545;
                            $data['bidSiteName'] = '八戒公采';
                            $data['bidTitle'] = $v['purchaseDemandName'];
                            $data['bidUrl'] = ($v['platform'] == 1 ? 'https://cg.zbj.com/newdetailsDemand?id=' : 'https://chinazhyc.zbj.com/detailsDemand?id=') . $v['originOrderNo'];
                            $data['beginDate'] = date('Y-m-d H:i:s', $v['publishDate'] / 1000);
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 545 -> 八戒公采');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 545 -> 八戒公采');
        }
        // 548 福易采
        $url = 'http://www.fycbid.cn:8000/fyc/fyc3/uip/mh/project/query';
        $res = global_curl($url, [
            'post' => 1,
            'postfields' => '{"site":"00","bulletinType":"ZBGG","bidType":"20","dateType":null,"isProvince":"","page":{"current":1,"size":99},"address":"","isEffective":1,"title":""}'
        ]);
        if ($res !== false) {
            $obj = json_decode($res, true);
            $arr = $obj['data']['records'] ?? [];
            if (is_array($arr) && count($arr) > 0) {
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['title'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['title'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 548;
                            $data['bidSiteName'] = '福易采';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = 'https://www.fycbid.cn/#/notice/detail-upgrade?bidId='.$v['bidId'].'&bulletinId='.$v['bulletinId'];
                            $data['beginDate'] = $v['pushTime'];
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 548 -> 福易采');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 548 -> 福易采');
        }
        // 579 陕西招标投标公共服务平台
        for ($i = 1; $i <= 20; $i++) {
            $url = 'http://bulletin.sntba.com/xxfbcmses/search/bulletin.html?searchDate=1999-09-13&dates=300&word=&categoryId=88&industryName=&area=&status=&publishMedia=&sourceInfo=&showStatus=1&page=' . $i;
            $res = global_curl($url);
            if ($res !== false) {
                $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
                $preg = '#<tr><tdstyle="text-align:left;"name="imgShow"id="(.*)"><ahref="javascript:urlOpen\(\'(.*)\'\)"title="(.*)">.*</tr>#isU';
                $obj = [];
                $arr = [];
                preg_match_all($preg, $res, $obj);
                $count = (is_countable($obj) && count($obj) > 0) ? count($obj[0]) : 0;
                if ($count > 0) {
                    for ($j = 0; $j < $count; $j++) {
                        $arr[$j]['url'] = $obj[2][$j];
                        $arr[$j]['title'] = $obj[3][$j];
                        $arr[$j]['beginDate'] = $obj[1][$j];
                    }
                    foreach ($arr as $k => $v) {
                        $data = [];
                        if (!preg_match('#(' . $in_keywords . ')#', $v['title'])) {
                            continue;
                        } else {
                            if (preg_match('#(' . $out_keywords . ')#', $v['title'])) {
                                continue;
                            } else {
                                $data['bidSiteId'] = 579;
                                $data['bidSiteName'] = '陕西招标投标公共服务平台';
                                $data['bidTitle'] = $v['title'];
                                $data['bidUrl'] = $v['url'];
                                $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                                $data['inDate'] = date('Y-m-d H:i:s');
                                $final_arr[] = $data;
                            }
                        }
                    }
                } else {
                    log_message('error', '招标采集：未匹配到数据 579 -> 陕西招标投标公共服务平台 -> 循环' . $i);
                }
            } else {
                log_message('error', '招标采集：未获取到网页 579 -> 陕西招标投标公共服务平台 -> 循环' . $i);
            }
        }
        // 581 中教控股
        $url = 'https://srm.educationgroup.cn/yicai-base/th_project/bidding.html';
        $res = global_curl($url);
        if ($res !== false) {
            $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
            $res_temp = [];
            preg_match('#<ulclass="table-body-listclearfix"id="zhaobiao">(.*)</ul>#isU', $res, $res_temp);
            $preg = '#<li.*><a.*href="(.*)".*title="(.*)">.*</a><span.*>(.*)</span>.*</li>#isU';
            $obj = [];
            $arr = [];
            is_countable($res_temp) && count($res_temp) > 0 && preg_match_all($preg, $res_temp[1], $obj);
            $count = (is_countable($obj) && count($obj) > 0) ? count($obj[0]) : 0;
            if ($count > 0) {
                for ($j = 0; $j < $count; $j++) {
                    $arr[$j]['url'] = $obj[1][$j];
                    $arr[$j]['title'] = $obj[2][$j];
                    $arr[$j]['beginDate'] = $obj[3][$j];
                }
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['title'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['title'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 581;
                            $data['bidSiteName'] = '中教控股';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = $v['url'];
                            $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 581 -> 中教控股');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 581 -> 中教控股');
        }
        // 591 采联集团
        $url = 'https://www.choicelink.cn:8444/cailian/abpArticles/filter/getPaged2?filter=a.ext2+%3D%3D+%2274166%22+%26%26+a.isDispaly+%3D%3D+%22true%22&order=a.creationTime+desc&pageSize=99&pageNum=1';
        $res = global_curl($url);
        if ($res !== false) {
            $obj = json_decode($res, true);
            $arr = $obj['result']['items'] ?? [];
            if (is_array($arr) && count($arr) > 0) {
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['title'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['title'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 591;
                            $data['bidSiteName'] = '采联集团';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = 'https://www.chinapsp.cn/notice_content.html?itemid=' . $v['id'];
                            $data['beginDate'] = $v['creationTime'];
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 591 -> 采联集团');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 591 -> 采联集团');
        }
        // 614 招采进宝
        $url = 'https://china.zcjb.com.cn/cms/api/dynamicData/queryContentPage'; // 企业采购
        $res = global_curl($url, [
            'post' => 1,
            'postfields' => '{"pageNo":1,"pageSize":50,"dto":{"master":"1","siteId":"737","categoryId":"7372120","publishDate":"","publishEndDate":"","city":"","province":"","purchaseMode":""}}'
        ]);
        if ($res !== false) {
            $obj = json_decode($res, true);
            $arr = $obj['res']['rows'] ?? [];
            if (is_array($arr) && count($arr) > 0) {
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['title'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['title'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 614;
                            $data['bidSiteName'] = '招采进宝';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = 'https://china.zcjb.com.cn/cms/china/webfile/detail/index.html?contentId='.$v['id'];
                            $data['beginDate'] = $v['publishDate'];
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 614 -> 招采进宝 -> 企业采购');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 614 -> 招采进宝 -> 企业采购');
        }
        $url = 'https://china.zcjb.com.cn/cms/api/dynamicData/queryContentPage'; // 政府采购
        $res = global_curl($url, [
            'post' => 1,
            'postfields' => '{"pageNo":1,"pageSize":50,"dto":{"master":"1","siteId":"737","categoryId":"7372220","publishDate":"","publishEndDate":"","city":"","province":"","purchaseMode":""}}'
        ]);
        if ($res !== false) {
            $obj = json_decode($res, true);
            $arr = $obj['res']['rows'] ?? [];
            if (is_array($arr) && count($arr) > 0) {
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['title'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['title'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 614;
                            $data['bidSiteName'] = '招采进宝';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = 'https://china.zcjb.com.cn/cms/china/webfile/detail/index.html?contentId='.$v['id'];
                            $data['beginDate'] = $v['publishDate'];
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 614 -> 招采进宝 -> 政府采购');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 614 -> 招采进宝 -> 政府采购');
        }
        // 629 江苏政府采购网上商城 【特殊通过浏览器刷新插件+自定义js获取数据后发送到本系统api并存数据库，每次采集从数据库拿】
        $res = $this->db->table('bid_temp')->select('res')->where('site', '江苏政府采购网上商城')->get()->getRowArray()['res'] ?? '';
        if ($res) {
            $obj = json_decode($res, true);
            $arr = $obj['result']['data'] ?? [];
            if (is_array($arr) && count($arr) > 0) {
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['title'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['title'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 629;
                            $data['bidSiteName'] = '江苏政府采购网上商城';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = 'https://jsscgg.zcygov.cn/luban/announcement/detail?encryptId=' . urlencode($v['encryptId']) . '&district=' . $v['district'];
                            $data['beginDate'] = date('Y-m-d H:i:s', $v['releasedAt'] / 1000);
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 629 -> 江苏政府采购网上商城');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 629 -> 江苏政府采购网上商城');
        }
        // 649 山东三木招标网
        $url = 'http://www.chinasanmu.com.cn/list1.jsp';
        $res = global_curl($url);
        if ($res !== false) {
            $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
            preg_match('#<ulclass="listRightContent">(.*)</ul>#isU', $res, $res_temp);
            $preg = '#<li><ahref="(.*)">(.*)</a><span>(.*)</span>.*</li>#isU';
            $obj = [];
            $arr = [];
            is_countable($res_temp) && count($res_temp) > 0 && preg_match_all($preg, $res_temp[1], $obj);
            $count = (is_countable($obj) && count($obj) > 0) ? count($obj[0]) : 0;
            if ($count > 0) {
                for ($j = 0; $j < $count; $j++) {
                    $arr[$j]['url'] = $obj[1][$j];
                    $arr[$j]['title'] = $obj[2][$j];
                    $arr[$j]['beginDate'] = $obj[3][$j];
                }
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['title'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['title'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 649;
                            $data['bidSiteName'] = '山东三木招标网';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = 'http://www.chinasanmu.com.cn/' . $v['url'];
                            $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 649 -> 山东三木招标网');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 649 -> 山东三木招标网');
        }
        // 656 四川招采交易信息网
        $url = 'http://www.zhaocaijiaoyi.com/zbnews/list?bid=1&pid=12&tid=31';
        $res = global_curl($url);
        if ($res !== false) {
            $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
            $res_temp = [];
            preg_match('#<divclass="news-list">(.*)</div>#isU', $res, $res_temp);
            $preg = '#<a.*href="(.*)"><span.*>(.*)</span><span.*</span><span.*</span><span.*>(.*)</span></a>#isU';
            $obj = [];
            $arr = [];
            is_countable($res_temp) && count($res_temp) > 0 && preg_match_all($preg, $res_temp[1], $obj);
            $count = (is_countable($obj) && count($obj) > 0) ? count($obj[0]) : 0;
            if ($count > 0) {
                for ($j = 0; $j < $count; $j++) {
                    $arr[$j]['url'] = $obj[1][$j];
                    $arr[$j]['title'] = $obj[3][$j];
                    $arr[$j]['beginDate'] = $obj[2][$j];
                }
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['title'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['title'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 656;
                            $data['bidSiteName'] = '四川招采交易信息网';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = strpos($v['url'], 'http') === 0 ? $v['url'] : ('http://www.zhaocaijiaoyi.com' . $v['url']);
                            $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 656 -> 四川招采交易信息网');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 656 -> 四川招采交易信息网');
        }
        // 657 安招采
        $url = 'https://www.anzhaocai.com/NgsAPI/ngs-bid-manage/tenderProjectInfo/selectWebPageList';
        $res = global_curl($url, [
            'post' => 1,
            'postfields' => '{"projectType":"1003008002","noticeType":"1","current":1,"size":100,"noticeStartTime":"","noticeEndTime":"","title":"","projectProName":"","projectCityName":""}'
        ]);
        if ($res !== false) {
            $obj = json_decode($res, true);
            $arr = $obj['data']['records'] ?? [];
            if (is_array($arr) && count($arr) > 0) {
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['title'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['title'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 657;
                            $data['bidSiteName'] = '安招采';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = 'https://www.anzhaocai.com/#/noticeDetail/'.$v['id'].'/'.$v['projectType'].'/'.$v['noticeType'].'/'.$v['noticeScope'].'?currentMenu=3';
                            $data['beginDate'] = $v['updateDate'];
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 657 -> 安招采');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 657 -> 安招采');
        }
        // 661 数智云采
        $url = 'https://cjyc.hbbidding.com.cn/inteligentsearch/rest/esinteligentsearch/getFullTextDataNew';
        $res = global_curl($url, [
            'post' => 1,
            'postfields' => '{"token":"","pn":0,"rn":100,"sdt":"","edt":"","wd":"","inc_wd":"","exc_wd":"","fields":"","cnum":"001","sort":"{\"webdate\":\"0\",\"id\":\"0\"}","ssort":"","cl":200,"terminal":"","condition":[{"fieldName":"categorynum","equal":"004003002","notEqual":null,"equalList":null,"notEqualList":null,"isLike":true,"likeType":2}],"time":null,"highlights":"","statistics":null,"unionCondition":null,"accuracy":"","noParticiple":"1","searchRange":null,"noWd":true}'
        ]);
        if ($res !== false) {
            $obj = json_decode($res, true);
            $arr = $obj['result']['records'] ?? [];
            if (is_array($arr) && count($arr) > 0) {
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['title'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['title'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 661;
                            $data['bidSiteName'] = '数智云采';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = 'https://cjyc.hbbidding.com.cn/hubeiyth'.$v['linkurl'];
                            $data['beginDate'] = $v['webdate'];
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 661 -> 数智云采');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 661 -> 数智云采');
        }
        // 666 青海项目信息网
        $urls = [
            '招标公告' => 'http://www.qhei.net.cn/html/zbcg/list_1698.html',
            '采购公告' => 'http://www.qhei.net.cn/html/zbcg/list_1694.html'
        ];
        foreach ($urls as $index => $url) {
            $res = global_curl($url, [
                'httpheader' => [
                    'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7',
                    'Accept-Encoding: gzip, deflate',
                    'Accept-Language: zh-CN,zh;q=0.9,en;q=0.8,en-GB;q=0.7,en-US;q=0.6',
                    'Cache-Control: no-cache',
                    'Connection: keep-alive',
                    'Host: www.qhei.net.cn',
                    'Pragma: no-cache',
                    'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36 Edg/131.0.0.0'
                ]
            ]);
            if ($res !== false) {
                $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
                $res_temp = [];
                preg_match('#</h3><ul>(.*)</ul>#isU', $res, $res_temp);
                $preg = '#<li><ahref="(.*)".*title="(.*)">.*</a>\[(.*)\]</li>#isU';
                $obj = [];
                $arr = [];
                is_countable($res_temp) && count($res_temp) > 0 && preg_match_all($preg, $res_temp[1], $obj);
                $count = (is_countable($obj) && count($obj) > 0) ? count($obj[0]) : 0;
                if ($count > 0) {
                    for ($j = 0; $j < $count; $j++) {
                        $arr[$j]['url'] = $obj[1][$j];
                        $arr[$j]['title'] = $obj[2][$j];
                        $arr[$j]['beginDate'] = str_replace('/', '-', strip_tags($obj[3][$j]));
                    }
                    foreach ($arr as $k => $v) {
                        $data = [];
                        if (!preg_match('#(' . $in_keywords . ')#', $v['title'])) {
                            continue;
                        } else {
                            if (preg_match('#(' . $out_keywords . ')#', $v['title'])) {
                                continue;
                            } else {
                                $data['bidSiteId'] = 666;
                                $data['bidSiteName'] = '青海项目信息网';
                                $data['bidTitle'] = $v['title'];
                                $data['bidUrl'] = $v['url'];
                                $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                                $data['inDate'] = date('Y-m-d H:i:s');
                                $final_arr[] = $data;
                            }
                        }
                    }
                } else {
                    log_message('error', '招标采集：未匹配到数据 666 -> 青海项目信息网 -> ' . $index);
                }
            } else {
                log_message('error', '招标采集：未获取到网页 666 -> 青海项目信息网 -> ' . $index);
            }
        }
        // 669 江苏省招标投标公共服务平台
        $url = 'https://api.jszbtb.com/DataSyncApi/HomePageTenderQualifyBulletin?PageSize=200&CurrentPage=1';
        $res = global_curl($url);
        if ($res !== false) {
            $obj = json_decode($res, true);
            $arr = $obj['data'] ?? [];
            if (is_array($arr) && count($arr) > 0) {
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['bulletinname'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['bulletinname'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 669;
                            $data['bidSiteName'] = '江苏省招标投标公共服务平台';
                            $data['bidTitle'] = $v['bulletinname'];
                            $data['bidUrl'] = 'https://www.jszbtb.com/#/bulletindetail/TenderBulletin/' . $v['bulletinid'] . '?release=%7B"release"%3A"' . date('Y-m-d', strtotime($v['createtime'])) . '"%7D';
                            $data['beginDate'] = $v['createtime'];
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 669 -> 江苏省招标投标公共服务平台');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 669 -> 江苏省招标投标公共服务平台');
        }
        // 670 安天智采【特殊，要以当前时间生成验证字符串】
        $time = intval(microtime(true) * 1000);
        $ver = base64_encode(base64_encode($time - 3600 * 1000 * 5.5));
        $verP = urlencode(base64_encode(base64_encode($time - 3600 * 1000 * 4)));
        $url = 'https://www.xinecai.com/api/search4EsPage?bigCode=&bigCodes=&title=&announceType=2&areaCode=&biddingType=&state=&projectType=&timeType=&memberUserName=&loginUserId=&loginOrgCode=&verP='.$verP.'&prjContentType=&projectSum=&pageNo=1&pageSize=20&timestamp='.$time.'&areaCodes=';
        $res = global_curl($url, [
            'header' => 1,
            'httpheader' => [
                'Accept: text/plain, */*; q=0.01',
                'Accept-Encoding: gzip, deflate, br, zstd',
                'Accept-Language: zh-CN,zh;q=0.9,en;q=0.8,en-GB;q=0.7,en-US;q=0.6',
                'Cache-Control: no-cache',
                'Connection: keep-alive',
                'Host: www.xinecai.com',
                'Pragma: no-cache',
                'Referer: https://www.xinecai.com/bidding?announceType=2',
                'Sec-Fetch-Dest: empty',
                'Sec-Fetch-Mode: cors',
                'Sec-Fetch-Site: same-origin',
                'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36 Edg/131.0.0.0',
                'acceptver: '.$ver
            ]
        ]);
        if ($res !== false) {
            $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
            $res_temp = [];
            $preg = '#<liclass="js-li-item">.*<ahref="(.*)".*<spanclass="pointer"></span>(.*)</a><span>(.*)</span></h5>.*</li>#isU';
            $obj = [];
            $arr = [];
            preg_match_all($preg, $res, $obj);
            $count = (is_countable($obj) && count($obj) > 0) ? count($obj[0]) : 0;
            if ($count > 0) {
                for ($j = 0; $j < $count; $j++) {
                    $arr[$j]['url'] = $obj[1][$j];
                    $arr[$j]['title'] = $obj[2][$j];
                    $arr[$j]['beginDate'] = $obj[3][$j];
                }
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['title'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['title'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 670;
                            $data['bidSiteName'] = '安天智采';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = 'https://www.xinecai.com/' . $v['url'];
                            $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 670 -> 安天智采');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 670 -> 安天智采');
        }
        // 677 乐采云 【特殊通过浏览器刷新插件+自定义js获取数据后发送到本系统api并存数据库，每次采集从数据库拿】
        $res = $this->db->table('bid_temp')->select('res')->where('site', '乐采云项目采购浙江')->get()->getRowArray()['res'] ?? '';
        if ($res) {
            $obj = json_decode($res, true);
            $arr = $obj['result']['data'] ?? [];
            if (is_array($arr) && count($arr) > 0) {
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['title'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['title'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 677;
                            $data['bidSiteName'] = '乐采云';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = 'https://sourcing-lcy.lecaiyun.com/luban/announcement/detail?encryptId='.urlencode($v['encryptId']);
                            $data['beginDate'] = date('Y-m-d H:i:s', $v['releasedAt'] / 1000);
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 677 -> 乐采云 -> 项目采购浙江');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 677 -> 乐采云 -> 项目采购浙江');
        }
        $res = $this->db->table('bid_temp')->select('res')->where('site', '湖南乐采云商机大厅')->get()->getRowArray()['res'] ?? '';
        if ($res) {
            $obj = json_decode($res, true);
            $arr = $obj['result']['list'] ?? [];
            if (is_array($arr) && count($arr) > 0) {
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['title'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['title'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 677;
                            $data['bidSiteName'] = '乐采云';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = 'https://hnlcy.zcygov.cn/bidding/detail?requisitionId='.$v['requisitionId'].'&type='.$v['type'];
                            $data['beginDate'] = date('Y-m-d H:i:s', $v['pubTimestamp'] / 1000);
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 677 -> 乐采云 -> 湖南乐采云商机大厅');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 677 -> 乐采云 -> 湖南乐采云商机大厅');
        }
        $url = 'https://gxzxxb.lecaiyun.com/api/sparta/announcement/pagingForNewHall'; // 乐采云 广西本级 在线询比
        $res = global_curl($url, [
            'post' => 1,
            'postfields' => '{"backCategoryName":"","categoryType":null,"pageNo":1,"pageSize":60,"hallType":"BUSINESS","tradeStyles":["BIDDING","REVERSE"],"stateList":[],"otherSearch":"","instanceCodes":["GXZXXB","GXZXXBLCJJ"],"sortField":"QUOTE_DEADLINE","sortMethod":"DESC","districtCodeList":[],"administrativeDistrictCodeList":[],"hallAnnouncementType":"ALL"}'
        ]);
        if ($res !== false) {
            $obj = json_decode($res, true);
            $arr = $obj['result']['list'] ?? [];
            if (is_array($arr) && count($arr) > 0) {
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['title'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['title'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 677;
                            $data['bidSiteName'] = '乐采云';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = 'https://gxzxxb.lecaiyun.com/luban/bidding/detail?requisitionId='.$v['requisitionId'].'&type='.$v['type'];
                            $data['beginDate'] = date('Y-m-d H:i:s', $v['pubTimestamp'] / 1000);
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 677 -> 乐采云 -> 广西本级 -> 在线询比');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 677 -> 乐采云 -> 广西本级 -> 在线询比');
        }
        $url = 'https://gxzc.lecaiyun.com/api/sparta/announcement/pagingForNewHall'; // 乐采云 广西本级 商机大厅
        $res = global_curl($url, [
            'post' => 1,
            'postfields' => '{"backCategoryName":"","categoryType":null,"pageNo":1,"pageSize":60,"pageNum":1,"hallType":"BUSINESS","tradeStyles":["BIDDING","SOURCING_BIDDING"],"stateList":[],"otherSearch":"","instanceCodes":["GXLCY"],"sortField":"QUOTE_DEADLINE","sortMethod":"DESC","districtCodeList":[],"administrativeDistrictCodeList":[],"hallAnnouncementType":"ALL"}'
        ]);
        if ($res !== false) {
            $obj = json_decode($res, true);
            $arr = $obj['result']['list'] ?? [];
            if (is_array($arr) && count($arr) > 0) {
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['title'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['title'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 677;
                            $data['bidSiteName'] = '乐采云';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = 'https://gxzc.lecaiyun.com/bidding/detail?requisitionId='.$v['requisitionId'].'&type='.$v['type'];
                            $data['beginDate'] = date('Y-m-d H:i:s', $v['pubTimestamp'] / 1000);
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 677 -> 乐采云 -> 广西本级 -> 商机大厅');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 677 -> 乐采云 -> 广西本级 -> 商机大厅');
        }
        // 681 黔云招采
        $url = 'https://gzny.e-qyzc.com/api/saas-portal/noauth/trans/trade/pageEs';
        $res = global_curl($url, [
            'post' => 1,
            'postfields' => '{"pageNum":1,"pageSize":99,"tradePattern":"1138499232192491521","noticeType":null,"releaseEndTime":'.intval(microtime(true) * 1000).',"releaseStartTime":'.intval(microtime(true) * 1000 - 604800000).',"purchaseProjectType":"","purchaseMode":null,"purchasePatternId":"","businessName":""}'
        ]);
        if ($res !== false) {
            $obj = json_decode($res, true);
            $arr = $obj['data']['list'] ?? [];
            if (is_array($arr) && count($arr) > 0) {
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['purchaseProjectName'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['purchaseProjectName'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 681;
                            $data['bidSiteName'] = '黔云招采';
                            $data['bidTitle'] = $v['purchaseProjectName'];
                            $data['bidUrl'] = 'https://gzny.e-qyzc.com/#/trade-info-detail?id='.$v['id'].'&noticeType='.$v['noticeType'].'&publishStatus='.$v['publishStatus'].'&systemStatus';
                            $data['beginDate'] = date('Y-m-d H:i:s', intval($v['releaseTime'] / 1000));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 681 -> 黔云招采');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 681 -> 黔云招采');
        }
        // 690 易土365采购管理平台
        $url = 'https://www.etu365.com/api/v1/ztb/listzbggscreen_new?gglx=0&pageSize=50&page=1';
        $res = global_curl($url);
        if ($res !== false) {
            $obj = json_decode($res, true);
            $arr = $obj['data']['data'] ?? [];
            if (is_array($arr) && count($arr) > 0) {
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['bdmc'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['bdmc'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 690;
                            $data['bidSiteName'] = '易土365采购管理平台';
                            $data['bidTitle'] = $v['bdmc'];
                            $data['bidUrl'] = 'https://webapp.etu365.com/ProjectMgr/ZBGG?ggid='.$v['id'];
                            $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['ggrq_begin']));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 690 -> 易土365采购管理平台');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 690 -> 易土365采购管理平台');
        }
        // 714 青岛阳光采购平台
        $url = 'https://www.qdygcg.com/api/saas-portal/noauth/trans/trade/pageEs';
        $res = global_curl($url, [
            'post' => 1,
            'postfields' => '{"pageNum":1,"pageSize":100,"tradePattern":"","noticeType":1,"businessName":"","releaseEndTime":"","releaseStartTime":"","purchaseProjectType":"905486189149884416","purchaseMode":null}',
            'httpheader' => [
                'Accept: application/json, text/plain, */*',
                'Accept-Encoding: gzip, deflate, br, zstd',
                'Accept-Language: zh-CN,zh;q=0.9,en;q=0.8,en-GB;q=0.7,en-US;q=0.6',
                'Cache-Control: no-cache',
                'Connection: keep-alive',
                'Content-Length: 184',
                'Content-Type: application/json;charset=UTF-8',
                'Cookie: Secure; Secure',
                'Host: www.qdygcg.com',
                'Origin: https://www.qdygcg.com',
                'Pragma: no-cache',
                'Referer: https://www.qdygcg.com/web-portal/',
                'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0'
            ]
        ]);
        if ($res !== false) {
            $obj = json_decode($res, true);
            $arr = $obj['data']['list'] ?? [];
            if (is_array($arr) && count($arr) > 0) {
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['businessName'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['businessName'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 714;
                            $data['bidSiteName'] = '青岛阳光采购平台';
                            $data['bidTitle'] = $v['businessName'];
                            $data['bidUrl'] = 'https://www.qdygcg.com/#/trade-info-detail?id='.$v['id'].'&publishStatus='.$v['publishStatus'].'&noticeType='.$v['noticeType'].'&systemStatus';
                            $data['beginDate'] = date('Y-m-d H:i:s', intval($v['releaseTime'] / 1000));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 714 -> 青岛阳光采购平台');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 714 -> 青岛阳光采购平台');
        }
        // 719 鞍钢智慧招投标平台
        $url = 'https://bid.ansteel.cn/inteligentsearch/rest/esinteligentsearch/getFullTextDataNew';
        $res = global_curl($url, [
            'post' => 1,
            'postfields' => '{"token":"","pn":0,"rn":50,"sdt":"","edt":"","wd":"%20","inc_wd":"","exc_wd":"","fields":"title","cnum":"001","sort":"{\"webdate\":\"0\"}","ssort":"title","cl":2000,"terminal":"","condition":[{"fieldName":"categorynum","equal":"001001","notEqual":null,"equalList":null,"notEqualList":null,"isLike":true,"likeType":2},{"fieldName":"projectnumber","notEqual":null,"equalList":null,"notEqualList":null,"isLike":true,"likeType":0},{"fieldName":"zblx","notEqual":null,"equalList":null,"notEqualList":null,"isLike":true,"likeType":0},{"fieldName":"zbfw","equal":"0","notEqual":null,"equalList":null,"notEqualList":null,"isLike":true,"likeType":0},{"fieldName":"cglb","equal":"M","notEqual":null,"equalList":null,"notEqualList":null,"isLike":true,"likeType":0}],"time":[{"fieldName":"webdate","startTime":"1998-01-01 00:00:00","endTime":"2099-12-31 23:59:59"}],"highlights":"","statistics":null,"unionCondition":null,"accuracy":"","noParticiple":"1","searchRange":null,"isBusiness":"1"}'
        ]);
        if ($res !== false) {
            $obj = json_decode($res, true);
            $arr = $obj['result']['records'] ?? [];
            if (is_array($arr) && count($arr) > 0) {
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['title'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['title'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 719;
                            $data['bidSiteName'] = '鞍钢智慧招投标平台';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = 'https://bid.ansteel.cn'.$v['linkurl'];
                            $data['beginDate'] = $v['webdate'];
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 719 -> 鞍钢智慧招投标平台');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 719 -> 鞍钢智慧招投标平台');
        }
        // 729 工采通电子招投标交易平台 【特殊通过浏览器刷新插件+自定义js获取数据后发送到本系统api并存数据库，每次采集从数据库拿】
        $res = $this->db->table('bid_temp')->select('res')->where('site', '工采通电子招投标交易平台')->get()->getRowArray()['res'] ?? '';
        if($res) {
            $arr = json_decode($res, true);
            if (is_array($arr) && count($arr) > 0) {
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['projectName'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['projectName'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 729;
                            $data['bidSiteName'] = '工采通电子招投标交易平台';
                            $data['bidTitle'] = $v['projectName'];
                            $data['bidUrl'] = 'https://easy-prt.com/project?id='.$v['id'].'&type=01';
                            $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['releaseTime']));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 729 -> 工采通电子招投标交易平台');
            }
        } else {
            log_message('error', '招标采集：未从数据库中获取到保存的res 729 -> 工采通电子招投标交易平台');
        }
        // 752 中国西部招投标网
        $url = 'https://www.xbbidcn.com/prod-api/project/xbbid-publish/open/notices?pageNum=1&pageSize=20&type=1';
        $res = global_curl($url);
        if ($res !== false) {
            $obj = json_decode($res, true);
            $arr = $obj['rows'] ?? [];
            if (is_array($arr) && count($arr) > 0) {
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['title'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['title'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 752;
                            $data['bidSiteName'] = '中国西部招投标网';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = 'https://www.xbbidcn.com/portal/notices/'.$v['xbbidRecordId'];
                            $data['beginDate'] = $v['publishTime'];
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 752 -> 中国西部招投标网');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 752 -> 中国西部招投标网');
        }
        // 756 铜仁市电子卖场
        $url = 'https://admin.trzfcg.com:82/biz/competeproject/getListCount?competeResult=0&userAreaState=520600&_=1744526966419';
        $res = global_curl($url);
        if ($res !== false) {
            $obj = json_decode($res, true);
            $arr = $obj['data'] ?? [];
            if (is_array($arr) && count($arr) > 0) {
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['cpName'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['cpName'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 756;
                            $data['bidSiteName'] = '铜仁市电子卖场';
                            $data['bidTitle'] = $v['cpName'];
                            $data['bidUrl'] = 'https://tongren.trzfcg.com/bidding/spBiddingForm?cpId='.$v['id'];
                            $data['beginDate'] = $v['createTime'];
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 756 -> 铜仁市电子卖场');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 756 -> 铜仁市电子卖场');
        }
        // 800 煤婆网
        $url = 'https://www.mp12345.com/get/local/bidding/info.json?pageNumber=1&pageSize=100&bidMethodType=&mustBiddingCode=&bizTypeCode=&method=1&title=';
        $res = global_curl($url);
        if($res !== false) {
            $obj = json_decode($res, true);
            $arr = $obj['data']['data'] ?? [];
            if (is_array($arr) && count($arr) > 0) {
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['title'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['title'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 800;
                            $data['bidSiteName'] = '煤婆网';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = 'https://bidtest.mp12345.com/bidweb/#/notice/view/'.$v['notice_id'].'/2';
                            $data['beginDate'] = $v['public_time'];
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 800 -> 煤婆网');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 800 -> 煤婆网');
        }
        // 809 E商城
        $url = 'https://gego.zhongcy.com/proxy/platform/platform/notice/queryMallNoticePageList?noticeBigType=&pageSize=50&pageNum=1&noticeTypes=1&effectType=&noticeStatus=10&startTime=&endTime=&noticeTitle=&mathRandom=' . intval(microtime(true) * 1000);
        $res = global_curl($url, [
            'httpheader' => [
                'Accept: application/json;charset=UTF-8',
                'Accept-Encoding: gzip, deflate, br, zstd',
                'Accept-Language: zh-CN,zh;q=0.9,en;q=0.8,en-GB;q=0.7,en-US;q=0.6',
                'Cache-Control: no-cache',
                'Connection: keep-alive',
                'Host: gego.zhongcy.com',
                'Pragma: no-cache',
                'Referer: https://gego.zhongcy.com/mall-view/information/noticeList/',
                'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36 Edg/135.0.0.0'
            ]
        ]);
        if($res !== false) {
            $obj = json_decode($res, true);
            $arr = $obj['data']['result'] ?? [];
            if (is_array($arr) && count($arr) > 0) {
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['noticeTitle'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['noticeTitle'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 809;
                            $data['bidSiteName'] = 'E商城';
                            $data['bidTitle'] = $v['noticeTitle'];
                            $data['bidUrl'] = 'https://gego.zhongcy.com/mall-view/information/detail?noticeId='.$v['id'];
                            $data['beginDate'] = $v['publishTimeStr'];
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 809 -> E商城');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 809 -> E商城');
        }
        // 830 远瞩采购云
        $url = 'https://www.sh-yuanzhu.com/yuanzhu/announceyz/queryMore?id=&type=purchace&pageNo=1&pageSize=50&name=&orgName=';
        $res = global_curl($url);
        if($res !== false) {
            $obj = json_decode($res, true);
            $arr = $obj['data']['list'] ?? [];
            if (is_array($arr) && count($arr) > 0) {
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['titleName'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['titleName'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 830;
                            $data['bidSiteName'] = '远瞩采购云';
                            $data['bidTitle'] = $v['titleName'];
                            $data['bidUrl'] = 'https://www.sh-yuanzhu.com/yuanzhu/views/announce/StatuteD.html?uuid='.$v['uuid'];
                            $data['beginDate'] = $v['inputDate'];
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 830 -> 远瞩采购云');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 830 -> 远瞩采购云');
        }
        // 850 云智采
        $url = 'https://www.yunscai.com/door/api/search4Page4es?projectType=&bigCode=&bigCodes=&noticeType=1&prjLocate=&prjIndustryCode=&state=&buyPrjType=3&timeType=&memberUserName=&areaCode=&pageNo=1&pageSize=30&timestamp=1750990772064&areaCodes=';
        $res = global_curl($url);
        if($res !== false) {
            $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
            $preg = '#<liclass="js-li-item".*<a.*href="(.*)".*<div>(.*)</div></a><span>(.*)</span></h5>.*</li>#isU';
            $obj = [];
            $arr = [];
            preg_match_all($preg, $res, $obj);
            $count = (is_countable($obj) && count($obj) > 0) ? count($obj[0]) : 0;
            if ($count > 0) {
                for ($j = 0; $j < $count; $j++) {
                    $arr[$j]['url'] = $obj[1][$j];
                    $arr[$j]['title'] = $obj[2][$j];
                    $arr[$j]['beginDate'] = $obj[3][$j];
                }
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['title'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['title'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 850;
                            $data['bidSiteName'] = '云智采';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = 'https://www.yunscai.com'.$v['url'];
                            $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 850 -> 云智采');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 850 -> 云智采');
        }
        // 857 海安商城
        $url = 'http://58.221.196.23/EpointMallService/rest/hljingjia/frontgetrequirement';
        $res = global_curl($url, [
            'post' => 1,
            'postfields' => '{"page":"1","size":99,"keyword":"","referer":"http://58.221.196.23/HLMall/frontbidprojectcgxq.html?page=1"}',
            'httpheader' => [
                'Accept: application/json, text/javascript, */*; q=0.01',
                'Accept-Encoding: gzip, deflate',
                'Accept-Language: zh-CN,zh;q=0.9,en;q=0.8,en-GB;q=0.7,en-US;q=0.6',
                'Cache-Control: no-cache',
                'Connection: keep-alive',
                'Content-Length: 107',
                'Content-Type: application/json;charset=UTF-8',
                'Cookie: sitevalue=ZhuZhan',
                'Host: 58.221.196.23',
                'Origin: http://58.221.196.23',
                'Pragma: no-cache',
                'Referer: http://58.221.196.23/HLMall/frontbidprojectcgxq.html?page=1',
                'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0'
            ]
        ]);
        if($res !== false) {
            $obj = json_decode($res, true);
            $arr = $obj['userarea']['guideinfo'] ?? [];
            if (is_array($arr) && count($arr) > 0) {
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['jingjianame'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['jingjianame'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 857;
                            $data['bidSiteName'] = '海安商城';
                            $data['bidTitle'] = $v['jingjianame'];
                            $data['bidUrl'] = 'http://58.221.196.23/HLMall/noticedetail.html?type=hwjj&gonggaoguid='.$v['gonggaoguid'];
                            $data['beginDate'] = date('Y-m-d H:i:s', strtotime(substr($v['jingjiano'], 2, 14)));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 857 -> 海安商城');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 857 -> 海安商城');
        }
        // 902 广西阳光采购服务平台
        for ($i = 1; $i <= 5; $i++) {
            $url = 'https://www.gxygcg.com/bbw_prod/bbw_notice/list?purchase_model=0&project_type=%E8%B4%A7%E7%89%A9&notice_type=1&org_type=0&subject_name=0&region_code=0&page='.$i.'&page_size=10&search_type=3&search_key';
            $res = global_curl($url);
            if($res !== false) {
                $obj = json_decode($res, true);
                $arr = $obj['data']['data'] ?? [];
                if (is_array($arr) && count($arr) > 0) {
                    foreach ($arr as $k => $v) {
                        $data = [];
                        if (!preg_match('#(' . $in_keywords . ')#', $v['noticeTitle'])) {
                            continue;
                        } else {
                            if (preg_match('#(' . $out_keywords . ')#', $v['noticeTitle'])) {
                                continue;
                            } else {
                                $data['bidSiteId'] = 902;
                                $data['bidSiteName'] = '广西阳光采购服务平台';
                                $data['bidTitle'] = $v['noticeTitle'];
                                $data['bidUrl'] = 'https://www.gxygcg.com/purchase/detail?purchase_projects_ids='.$v['purchaseProjectsIds'].'&notice_type='.$v['noticeType'].'&notice_id='.$v['noticeId'];
                                $data['beginDate'] = date('Y-m-d H:i:s', intval($v['noticeTime'] / 1000));
                                $data['inDate'] = date('Y-m-d H:i:s');
                                $final_arr[] = $data;
                            }
                        }
                    }
                } else {
                    log_message('error', '招标采集：未匹配到数据 902 -> 广西阳光采购服务平台');
                }
            } else {
                log_message('error', '招标采集：未获取到网页 902 -> 广西阳光采购服务平台');
            }
        }
        // 903 轻采通
<<<<<<< HEAD
        $url = 'https://t.qc-sd.com/index/index/bidding/category/1.html'; // 上面3条
        $res = global_curl($url);
        if ($res !== false) {
            $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
            $preg = '#<divclass="biddingCaiLi"><divclass="biddingCaiLiTop">(.*)</div>.*<div>发布时间：<span>(.*)</span>.*<a.*href="(.*)".*</div>#isU';
            $obj = [];
            $arr = [];
            preg_match_all($preg, $res, $obj);
            $count = (is_countable($obj) && count($obj) > 0) ? count($obj[0]) : 0;
            if ($count > 0) {
                for ($j = 0; $j < $count; $j++) {
                    $arr[$j]['url'] = $obj[3][$j];
                    $arr[$j]['title'] = $obj[1][$j];
                    $arr[$j]['beginDate'] = $obj[2][$j];
                }
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['title'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['title'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 903;
                            $data['bidSiteName'] = '轻采通';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = strpos($v['url'], 'http') === 0 ? $v['url'] : ('https://t.qc-sd.com' . str_replace('..', '', $v['url']));
                            $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 903 -> 轻采通 -> 上面3条');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 903 -> 轻采通 -> 上面3条');
        }
        $url = 'https://t.qc-sd.com/index/index/bidding/category/1.html?page=1&limit=50'; // 下面10条
=======
        $url = 'https://t.qc-sd.com/index/index/bidding/category/1.html?page=1&limit=50';
>>>>>>> 151b415 (更新语言最新版本+最新底层框架后全部差异化代码适配重写)
        $res = global_curl($url, [
            'httpheader' => [
                'accept: application/json, text/javascript, */*; q=0.01',
                'accept-encoding: gzip, deflate, br, zstd',
                'accept-language: zh-CN,zh;q=0.9,en;q=0.8,en-GB;q=0.7,en-US;q=0.6',
                'cache-control: no-cache',
                'pragma: no-cache',
                'priority: u=1, i',
                'referer: https://t.qc-sd.com/index/index/bidding/category/1.html',
                'sec-ch-ua: "Microsoft Edge";v="137", "Chromium";v="137", "Not/A)Brand";v="24"',
                'sec-ch-ua-mobile: ?0',
                'sec-ch-ua-platform: "Windows"',
                'sec-fetch-dest: empty',
                'sec-fetch-mode: cors',
                'sec-fetch-site: same-origin',
                'user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0',
                'x-requested-with: XMLHttpRequest'
            ]
        ]);
        if($res !== false) {
            $obj = json_decode($res, true);
            $arr = $obj['rows'] ?? [];
            if (is_array($arr) && count($arr) > 0) {
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['title'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['title'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 903;
                            $data['bidSiteName'] = '轻采通';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = 'https://t.qc-sd.com/index/index/bidding_detail/ids/' . $v['id'] . '.html';
                            $data['beginDate'] = $v['createtime_text'];
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
<<<<<<< HEAD
                log_message('error', '招标采集：未匹配到数据 903 -> 轻采通 -> 下面10条');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 903 -> 轻采通 -> 下面10条');
=======
                log_message('error', '招标采集：未匹配到数据 903 -> 轻采通');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 903 -> 轻采通');
>>>>>>> 151b415 (更新语言最新版本+最新底层框架后全部差异化代码适配重写)
        }
        // 910 阳光易招公共资源交易平台
        $url = 'https://www.sunbidding.com/zcggg/index.jhtml';
        $res = global_curl($url);
        if($res !== false) {
            $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
            $res_temp = [];
            preg_match('#<divclass="infolist-main"style="margin-left:0;"><ul>(.*)</ul>#isU', $res, $res_temp);
            $preg = '#<li.*<a.*href="(.*)".*title="(.*)".*</span><em>(.*)</em></a></li>#isU';
            $obj = [];
            $arr = [];
            is_countable($res_temp) && count($res_temp) > 0 && preg_match_all($preg, $res_temp[1], $obj);
            $count = (is_countable($obj) && count($obj) > 0) ? count($obj[0]) : 0;
            if ($count > 0) {
                for ($j = 0; $j < $count; $j++) {
                    $arr[$j]['url'] = $obj[1][$j];
                    $arr[$j]['title'] = $obj[2][$j];
                    $arr[$j]['beginDate'] = $obj[3][$j];
                }
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['title'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['title'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 910;
                            $data['bidSiteName'] = '阳光易招公共资源交易平台';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = strpos($v['url'], 'http') === 0 ? $v['url'] : ('https://www.sunbidding.com'.$v['url']);
                            $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 910 -> 阳光易招公共资源交易平台');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 910 -> 阳光易招公共资源交易平台');
        }
        // 934 筑享云
        $url = 'https://www.pcteam.com/api/mall/home/demand/list?_t=1755995528&pageIndex=1&pageSize=36&statusStr=1';
        $res = global_curl($url);
        if($res !== false) {
            $obj = json_decode($res, true);
            $arr = $obj['data'] ?? [];
            if (is_array($arr) && count($arr) > 0) {
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['title'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['title'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 934;
                            $data['bidSiteName'] = '筑享云';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = 'https://www.pcteam.com/demandHall/'.$v['id'];
                            $data['beginDate'] = date('Y-m-d H:i:s');
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 934 -> 筑享云');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 934 -> 筑享云');
        }
        // 948 魏武国控招标采购交易平台
        $url = 'https://zbcg.wwgkjt.com/EWB-FRONT/rest/frontAppNotNeedLoginActionCustom/getJyxxList';
        $res = global_curl($url, [
            'post' => 1,
            'postfields' => 'siteGuid=7eb5f7f1-9041-43ad-8e13-8fcb82ea831a&categoryNum=002002001&sendDay=&startTime=&endTime=&xiaquCode=wwgk&zbType=&title=&pageIndex=0&pageSize=50&verificationCode=&verificationGuid=&ImgGuid=&YZM=',
            'httpheader' => [
                'Accept: application/json, text/javascript, */*; q=0.01',
                'Accept-Encoding: gzip, deflate, br, zstd',
                'Accept-Language: zh-CN,zh;q=0.9,en;q=0.8,en-GB;q=0.7,en-US;q=0.6',
                'Cache-Control: no-cache',
                'Connection: keep-alive',
                'Content-Length: 200',
                'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
                'Host: zbcg.wwgkjt.com',
                'Origin: https://zbcg.wwgkjt.com',
                'Pragma: no-cache',
                'Referer: https://zbcg.wwgkjt.com/jyxx/002002/jyxx_list.html?xiaqucode=wwgk',
                'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0'
            ]
        ]);
        if($res !== false) {
            $obj = json_decode($res, true);
            $arr = $obj['custom']['dataList'] ?? [];
            if (is_array($arr) && count($arr) > 0) {
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['title2'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['title2'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 948;
                            $data['bidSiteName'] = '魏武国控招标采购交易平台';
                            $data['bidTitle'] = $v['title2'];
                            $data['bidUrl'] = 'https://zbcg.wwgkjt.com' . $v['infoUrl'];
                            $data['beginDate'] = $v['infodate'];
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 948 -> 魏武国控招标采购交易平台');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 948 -> 魏武国控招标采购交易平台');
        }
        // 972 吉利人才发展集团电子采购管理平台 【特殊通过浏览器刷新插件+自定义js获取数据后发送到本系统api并存数据库，每次采集从数据库拿】
        $res = $this->db->table('bid_temp')->select('res')->where('site', '吉利人才发展集团电子采购管理平台')->get()->getRowArray()['res'] ?? '';
        if($res) {
            $preg = '#<li><a.*title="(.*)".*href="/HomeSite/Site/NewsContentView\?newsId=(.*)".*</a><spanclass="date">(.*)</span>.*</li>#isU';
            $obj = [];
            $arr = [];
            preg_match_all($preg, $res, $obj);
            $count = (is_countable($obj) && count($obj) > 0) ? count($obj[0]) : 0;
            if ($count > 0) {
                for ($j = 0; $j < $count; $j++) {
                    $arr[$j]['url'] = $obj[2][$j];
                    $arr[$j]['title'] = $obj[1][$j];
                    $arr[$j]['beginDate'] = $obj[3][$j];
                }
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['title'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['title'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 972;
                            $data['bidSiteName'] = '吉利人才发展集团电子采购管理平台';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = 'http://eps.geelytalent.com/' . $v['url']; // 直接使用真实链接访问时提示“无权访问采购平台，请登录帐号重试”，只能保存虚拟链接，点击可进入列表页
                            $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 972 -> 吉利人才发展集团电子采购管理平台');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 972 -> 吉利人才发展集团电子采购管理平台');
        }
        // 985 量云采
        $url = 'https://n.zlsungin.cn/api/LycPublicNotice/GetList?pageSize=30&offset=1&query=siteId%3D%2725%27+AND+noticeType+%3D%270%27';
        $res = global_curl($url);
        if($res !== false) {
            $obj = json_decode($res, true);
            $arr = $obj['items'] ?? [];
            if (is_array($arr) && count($arr) > 0) {
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['title'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['title'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 985;
                            $data['bidSiteName'] = '量云采';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = 'https://www.zlsungin.cn/single-pages/noticeContent.html?id='.$v['id'];
                            $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['addDate']));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 985 -> 量云采');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 985 -> 量云采');
        }
        // 1011 一毂清风电子招投标平台
        $url = 'https://ebs.hbncp.com.cn/ebsapi/project/release/announcement/public/listavailable';
        $res = global_curl($url, [
            'post' => 1,
            'postfields' => '{"pageIndex":1,"pageSize":50,"query":{"keyword_LIKE":"","proBulletinNature_EQ":""}}'
        ]);
        if($res !== false) {
            $obj = json_decode($res, true);
            $arr = $obj['data']['rows'] ?? [];
            if (is_array($arr) && count($arr) > 0) {
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['bulletinTitle'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['bulletinTitle'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 1011;
                            $data['bidSiteName'] = '一毂清风电子招投标平台';
                            $data['bidTitle'] = $v['bulletinTitle'];
                            $data['bidUrl'] = 'https://www.hbncp.com.cn/#/projectDetail/'.$v['id'];
                            $data['beginDate'] = date('Y-m-d H:i:s', intval($v['showTime'] / 1000));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 1011 -> 一毂清风电子招投标平台');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 1011 -> 一毂清风电子招投标平台');
        }
        // 1021 如e采购
        $url = 'https://cg.rgepay.com/';
        $res = global_curl($url, [
            'httpheader' => [
                'accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7',
                'accept-encoding: gzip, deflate, br, zstd',
                'accept-language: zh-CN,zh;q=0.9,en;q=0.8,en-GB;q=0.7,en-US;q=0.6',
                'cache-control: no-cache',
                'pragma: no-cache',
                'referer: https://cg.rgepay.com/cgr_articles.html?utf8=%E2%9C%93&q%5Btitle_cont%5D=&q%5Bcode_cont%5D=&q%5Ba_type_eq%5D=0&q%5Bgen_plan_jhlb_eq%5D=&q%5Bgen_plan_cgfs_eq%5D=&commit=%E6%9F%A5%E8%AF%A2',
                'user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0'
            ]
        ]);
        if($res !== false) {
            $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
            $res_temp = [];
            preg_match('#<divclass="gg_content"><divclass="notice_area"><ul.*>(.*)</ul>#isU', $res, $res_temp);
            $preg = '#<li.*<a.*href="(.*)".*title="(.*)".*<spanclass="mnl_datefr">(.*)</span></a></li>#isU';
            $obj = [];
            $arr = [];
            is_countable($res_temp) && count($res_temp) > 0 && preg_match_all($preg, $res_temp[1], $obj);
            $count = (is_countable($obj) && count($obj) > 0) ? count($obj[0]) : 0;
            if ($count > 0) {
                for ($j = 0; $j < $count; $j++) {
                    $arr[$j]['url'] = $obj[1][$j];
                    $arr[$j]['title'] = $obj[2][$j];
                    $arr[$j]['beginDate'] = $obj[3][$j];
                }
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['title'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['title'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 1021;
                            $data['bidSiteName'] = '如e采购';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = strpos($v['url'], 'http') === 0 ? $v['url'] : ('https://cg.rgepay.com'.$v['url']);
                            $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 1021 -> 如e采购');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 1021 -> 如e采购');
        }
        // 1042 南翼集团
        $url = 'http://www.qzsnyjt.com/h-col-135.html';
        $res = global_curl($url, [
            'httpheader' => [
                'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7',
                'Accept-Encoding: gzip, deflate',
                'Accept-Language: zh-CN,zh;q=0.9,en;q=0.8,en-GB;q=0.7,en-US;q=0.6',
                'Cache-Control: no-cache',
                'Connection: keep-alive',
                'Host: www.qzsnyjt.com',
                'Pragma: no-cache',
                'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0'
            ]
        ]);
        if($res !== false) {
            $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
            $preg = '#<divid="m_news_content.*<pclass="big_time">(.*)</p><pclass="small_time">(.*)</p>.*<divid="m_news_info.*<a.*href="(.*)".*title="(.*)".*</a>#isU';
            $obj = [];
            $arr = [];
            preg_match_all($preg, $res, $obj);
            $count = (is_countable($obj) && count($obj) > 0) ? count($obj[0]) : 0;
            if ($count > 0) {
                for ($j = 0; $j < $count; $j++) {
                    $arr[$j]['url'] = $obj[3][$j];
                    $arr[$j]['title'] = $obj[4][$j];
                    $arr[$j]['beginDate'] = $obj[1][$j] . '-' . $obj[2][$j];
                }
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['title'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['title'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 1042;
                            $data['bidSiteName'] = '南翼集团';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = strpos($v['url'], 'http') === 0 ? $v['url'] : ('http://www.qzsnyjt.com'.$v['url']);
                            $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 1042 -> 南翼集团');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 1042 -> 南翼集团');
        }
        // 1076 广西招标投标公共服务平台
        for ($i = 1; $i <= 5; $i++) {
            $url = 'http://zbtb.gxi.gov.cn:9000/xxfbcms/category/bulletinList.html?searchDate=2000-11-20&dates=300&word=&categoryId=88&industryName=&area=&status=&publishMedia=&sourceInfo=&showStatus=1&page=' . $i;
            $res = global_curl($url);
            if ($res !== false) {
                $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
                $preg = '#<tr><tdstyle="text-align:left;"name="imgShow"id="(.*)"><ahref="javascript:urlOpen\(\'(.*)\'\)"title="(.*)">.*</tr>#isU';
                $obj = [];
                $arr = [];
                preg_match_all($preg, $res, $obj);
                $count = (is_countable($obj) && count($obj) > 0) ? count($obj[0]) : 0;
                if ($count > 0) {
                    for ($j = 0; $j < $count; $j++) {
                        $arr[$j]['url'] = $obj[2][$j];
                        $arr[$j]['title'] = $obj[3][$j];
                        $arr[$j]['beginDate'] = $obj[1][$j];
                    }
                    foreach ($arr as $k => $v) {
                        $data = [];
                        if (!preg_match('#(' . $in_keywords . ')#', $v['title'])) {
                            continue;
                        } else {
                            if (preg_match('#(' . $out_keywords . ')#', $v['title'])) {
                                continue;
                            } else {
                                $data['bidSiteId'] = 1076;
                                $data['bidSiteName'] = '广西招标投标公共服务平台';
                                $data['bidTitle'] = $v['title'];
                                $data['bidUrl'] = $v['url'];
                                $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                                $data['inDate'] = date('Y-m-d H:i:s');
                                $final_arr[] = $data;
                            }
                        }
                    }
                } else {
                    log_message('error', '招标采集：未匹配到数据 1076 -> 广西招标投标公共服务平台 -> 循环' . $i);
                }
            } else {
                log_message('error', '招标采集：未获取到网页 1076 -> 广西招标投标公共服务平台 -> 循环' . $i);
            }
        }
        // 1089 海西招标投标公共服务平台
        $urls = [
            '招标公告' => 'http://admin.haixizb.com/index.php/getList?id=19&type=all&page=1&num=25&async=false',
            '网上竞价公告' => 'http://admin.haixizb.com/index.php/getList?id=22&type=all&page=1&num=25&async=false'
        ];
        foreach ($urls as $index => $url) {
            $res = global_curl($url);
            if ($res !== false) {
            $obj = json_decode($res, true);
            $arr = $obj['data']['list'] ?? [];
            if (is_array($arr) && count($arr) > 0) {
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['title'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['title'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 1089;
                            $data['bidSiteName'] = '海西招标投标公共服务平台';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = 'http://www.haixizb.com/new.html?id='.$v['id'];
                            $data['beginDate'] = $v['release_time'];
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
                } else {
                    log_message('error', '招标采集：未匹配到数据 1089 -> 海西招标投标公共服务平台 -> ' . $index);
                }
            } else {
                log_message('error', '招标采集：未获取到网页 1089 -> 海西招标投标公共服务平台 -> ' . $index);
            }
        }
        // 1092 云易通 【特殊通过浏览器刷新插件+自定义js获取数据后发送到本系统api并存数据库，每次采集从数据库拿】
        $res = $this->db->table('bid_temp')->select('res')->where('site', '云易通')->get()->getRowArray()['res'] ?? '';
        if($res) {
            $preg = '#<tr.*data-row-key="(.*)".*<divtitle="(.*)".*<tdclass="ant-table-row-cell-break-word">(.*)</td></tr>#isU';
            $obj = [];
            $arr = [];
            preg_match_all($preg, $res, $obj);
            $count = (is_countable($obj) && count($obj) > 0) ? count($obj[0]) : 0;
            if ($count > 0) {
                for ($j = 0; $j < $count; $j++) {
                    $arr[$j]['url'] = $obj[1][$j];
                    $arr[$j]['title'] = $obj[2][$j];
                    $arr[$j]['beginDate'] = $obj[3][$j];
                }
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['title'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['title'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 1092;
                            $data['bidSiteName'] = '云易通';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = 'https://yunmeng.xianeyun.com/trading_announcement/procurement/detail?id='.$v['url'];
                            $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 1092 -> 云易通');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 1092 -> 云易通');
        }
        // 1096 中采惠招
        $url = 'http://www.zchzb.com/hcgweb/index/information/getjyinfopg.json?random='.intval(microtime(true) * 1000).'&provincecode=&citycode=&pageindex=1&pagesize=50&type=zbgg&hycode=&leixing=&zbdl=&searchtype=ggname&searchcontent=&starttime=&endtime=&featurecode=zchz';
        $res = global_curl($url);
        if($res !== false) {
            $obj = json_decode($res, true);
            $arr = $obj['rows'] ?? [];
            if (is_array($arr) && count($arr) > 0) {
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['ggname'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['ggname'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 1096;
                            $data['bidSiteName'] = '中采惠招';
                            $data['bidTitle'] = $v['ggname'];
                            $data['bidUrl'] = 'http://www.zchzb.com/biddingWeb/pages/jyinfo/detail'.(strpos($v['ggcode'], 'SD') === 0 ? '-sd' : '').'.html?active=0&infoid='.$v['ggcode'].'&categoryid='.$v['ggtype'];
                            $data['beginDate'] = $v['ggfbtime'];
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 1096 -> 中采惠招');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 1096 -> 中采惠招');
        }
        // 1114 快速采购平台（小额采购）
        $url = 'http://jj.wisdombidding.com/oddPurchase';
        $res = global_curl($url);
        if($res !== false) {
            $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
            $preg = '#<trenquiryMainId=".*"><td><a.*title="(.*)".*href="(.*)".*</a>.*<div>发布：(.*)</div>.*</tr>#isU';
            $obj = [];
            $arr = [];
            preg_match_all($preg, $res, $obj);
            $count = (is_countable($obj) && count($obj) > 0) ? count($obj[0]) : 0;
            if ($count > 0) {
                for ($j = 0; $j < $count; $j++) {
                    $arr[$j]['url'] = $obj[2][$j];
                    $arr[$j]['title'] = $obj[1][$j];
                    $arr[$j]['beginDate'] = $obj[3][$j];
                }
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['title'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['title'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 1114;
                            $data['bidSiteName'] = '快速采购平台（小额采购）';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = strpos($v['url'], 'http') === 0 ? $v['url'] : ('http://jj.wisdombidding.com'.$v['url']);
                            $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 1114 -> 快速采购平台（小额采购）');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 1114 -> 快速采购平台（小额采购）');
        }
        // 1119 南方招标与采购交易平台
        $url = 'https://www.eebidding.com/f/list-10.html';
        $res = global_curl($url);
        if ($res !== false) {
            $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
            $preg = '#<!--<li>.*<li><a.*href="(.*)".*<span.*class="title">(.*)</span></a>.*<span.*class="time">(.*)</span>.*</li>#isU';
            $obj = [];
            $arr = [];
            preg_match_all($preg, $res, $obj);
            $count = (is_countable($obj) && count($obj) > 0) ? count($obj[0]) : 0;
            if ($count > 0) {
                for ($j = 0; $j < $count; $j++) {
                    $arr[$j]['url'] = $obj[1][$j];
                    $arr[$j]['title'] = $obj[2][$j];
                    $arr[$j]['beginDate'] = $obj[3][$j];
                }
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['title'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['title'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 1119;
                            $data['bidSiteName'] = '南方招标与采购交易平台';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = strpos($v['url'], 'http') === 0 ? $v['url'] : ('https://www.eebidding.com'.$v['url']);
                            $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 1119 -> 南方招标与采购交易平台');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 1119 -> 南方招标与采购交易平台');
        }
        // 1128 中国通用招标网
        for ($i = 1; $i <= 5; $i++) {
            $url = 'https://www.china-tender.com.cn/zbgg/index_'.$i.'.jhtml';
            $res = global_curl($url, [
                'httpheader' => [
                    'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7',
                    'Accept-Encoding: gzip, deflate, br, zstd',
                    'Accept-Language: zh-CN,zh;q=0.9,en;q=0.8,en-GB;q=0.7,en-US;q=0.6',
                    'Cache-Control: no-cache',
                    'Connection: keep-alive',
                    'Host: www.china-tender.com.cn',
                    'Pragma: no-cache',
                    'Referer: https://www.china-tender.com.cn/zbgg/index.jhtml',
                    'Sec-Fetch-Dest: document',
                    'Sec-Fetch-Mode: navigate',
                    'Sec-Fetch-Site: same-origin',
                    'Sec-Fetch-User: ?1',
                    'Upgrade-Insecure-Requests: 1',
                    'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0',
                    'sec-ch-ua: "Microsoft Edge";v="141", "Not?A_Brand";v="8", "Chromium";v="141"',
                    'sec-ch-ua-mobile: ?0',
                    'sec-ch-ua-platform: "Windows"'
                ]
            ]);
            if($res !== false) {
                $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
                $res_temp = [];
                preg_match('#<divclass="List2Top5"><ul>(.*)</ul>#isU', $res, $res_temp);
                $preg = '#<li.*<a.*href="(.*)".*>(.*)</a>.*<spanclass="GrayRight">发布时间：(.*)</span>.*</li>#isU';
                $obj = [];
                $arr = [];
                is_countable($res_temp) && count($res_temp) && preg_match_all($preg, $res_temp[1], $obj);
                $count = (is_countable($obj) && count($obj) > 0) ? count($obj[0]) : 0;
                if ($count > 0) {
                    for ($j = 0; $j < $count; $j++) {
                        $arr[$j]['url'] = $obj[1][$j];
                        $arr[$j]['title'] = $obj[2][$j];
                        $arr[$j]['beginDate'] = $obj[3][$j];
                    }
                    foreach ($arr as $k => $v) {
                        $data = [];
                        if (!preg_match('#(' . $in_keywords . ')#', $v['title'])) {
                            continue;
                        } else {
                            if (preg_match('#(' . $out_keywords . ')#', $v['title'])) {
                                continue;
                            } else {
                                $data['bidSiteId'] = 1128;
                                $data['bidSiteName'] = '中国通用招标网';
                                $data['bidTitle'] = $v['title'];
                                $data['bidUrl'] = strpos($v['url'], 'http') === 0 ? $v['url'] : ('https://www.china-tender.com.cn'.$v['url']);
                                $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                                $data['inDate'] = date('Y-m-d H:i:s');
                                $final_arr[] = $data;
                            }
                        }
                    }
                } else {
                    log_message('error', '招标采集：未匹配到数据 1128 -> 中国通用招标网 -> 第' . $i . '页');
                }
            } else {
                log_message('error', '招标采集：未获取到网页 1128 -> 中国通用招标网 -> 第' . $i . '页');
            }
        }
        // 1147 汇采通平台
        $url = 'https://www.aihuicai.com.cn/api/hc-announcement/api/biz/web/announcement/page';
        $res = global_curl($url, [
            'post' => 1,
            'postfields' => '{"startPublishDate":"","endPublishDate":"","keywords":"","sort":1,"pageNo":1,"pageSize":100}'
        ]);
        if($res !== false) {
            $obj = json_decode($res, true);
            $arr = $obj['data']['list'] ?? [];
            if (is_array($arr) && count($arr) > 0) {
                foreach ($arr as $k => $v) {

                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['title'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['title'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 1147;
                            $data['bidSiteName'] = '汇采通平台';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = $v['url'] ?? ('https://www.aihuicai.com.cn/home/#/detail/' . $v['id']);
                            $data['beginDate'] = $v['publishTime'];
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 1147 -> 汇采通平台');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 1147 -> 汇采通平台');
        }
        // 1150 阳光慧采电子交易平台
        $url = 'https://www.ezb.net.cn/';
        $res = global_curl($url);
        if($res !== false) {
            $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
            $res_temp = [];
            preg_match_all('#<divclass="animatedfadeInRightTabbedPanelsContent"><divclass="List2"><ul>(.*)</ul></div></div>#isU', $res, $res_temp);
            $preg = '#<li><spanclass="RightGray">(.*)</span><a.*title="(.*)".*href="(.*)".*</a></li>#isU';
            $obj = [];
            $arr = [];
            is_countable($res_temp) && count($res_temp) > 0 && preg_match_all($preg, $res_temp[1][0], $obj);
            $count = (is_countable($obj) && count($obj) > 0) ? count($obj[0]) : 0;
            if ($count > 0) {
                for ($j = 0; $j < $count; $j++) {
                    $arr[$j]['url'] = $obj[3][$j];
                    $arr[$j]['title'] = $obj[2][$j];
                    $arr[$j]['beginDate'] = $obj[1][$j];
                }
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['title'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['title'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 1150;
                            $data['bidSiteName'] = '阳光慧采电子交易平台';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = $v['url'];
                            $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 1150 -> 阳光慧采电子交易平台 -> 招标公告');
            }
            $preg = '#<li><spanclass="RightGray">(.*)</span><a.*href="(.*)".*title="(.*)".*</a></li>#isU';
            $obj = [];
            $arr = [];
            is_countable($res_temp) && count($res_temp) > 0 && preg_match_all($preg, $res_temp[1][3], $obj);
            $count = (is_countable($obj) && count($obj) > 0) ? count($obj[0]) : 0;
            if ($count > 0) {
                for ($j = 0; $j < $count; $j++) {
                    $arr[$j]['url'] = $obj[3][$j];
                    $arr[$j]['title'] = $obj[2][$j];
                    $arr[$j]['beginDate'] = $obj[1][$j];
                }
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['title'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['title'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 1150;
                            $data['bidSiteName'] = '阳光慧采电子交易平台';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = $v['url'];
                            $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 1150 -> 阳光慧采电子交易平台 -> 竞价公告');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 1150 -> 阳光慧采电子交易平台');
        }
        // 1155 亿企优采
        $url = 'https://s.vins.com.cn/business/login/biddingList';
        $res = global_curl($url, [
            'post' => 1,
            'postfields' => '------WebKitFormBoundaryeqjfZmOrDNs6yVOe'."\r\n".'Content-Disposition: form-data; name="page"'."\r\n"."\r\n".'{"numPerPage":99,"pageNum":1,"condition":"LIKE","keyword":"f01","searchValue":"","orderDirection":"","orderField":"","filterParams":{}}'."\r\n".'------WebKitFormBoundaryeqjfZmOrDNs6yVOe--',
            'httpheader' => [
                'Accept: application/json, text/plain, */*',
                'Accept-Encoding: gzip, deflate, br, zstd',
                'Accept-Language: zh-CN,zh;q=0.9,en;q=0.8,en-GB;q=0.7,en-US;q=0.6',
                'Cache-Control: no-cache',
                'Connection: keep-alive',
                'Content-Length: 268',
                'Content-Type: multipart/form-data; boundary=----WebKitFormBoundaryeqjfZmOrDNs6yVOe',
                'Host: s.vins.com.cn',
                'Origin: https://s.vins.com.cn',
                'Pragma: no-cache',
                'Referer: https://s.vins.com.cn/business/center/bidingList',
                'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0'
            ]
        ]);
        if($res !== false) {
            $obj = json_decode($res, true);
            $arr = $obj['data']['records'] ?? [];
            if (is_array($arr) && count($arr) > 0) {
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['f02'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['f02'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 1155;
                            $data['bidSiteName'] = '亿企优采';
                            $data['bidTitle'] = $v['f02'];
                            $data['bidUrl'] = 'https://s.vins.com.cn/business/bidingDetail?fid='.$v['f14'].'&school='.$v['f04'].'&f31='.$v['f31'];
                            $data['beginDate'] = $v['f07'];
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 1155 -> 亿企优采');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 1155 -> 亿企优采');
        }
        // 1162 湖北省政府采购协会
        $url = 'https://hubeigpa.com/list_30.html';
        $res = global_curl($url);
        if($res !== false) {
            $res = trim($res, '"');
            $res = str_replace(["\\n", "\\t", "\\", " "], '', $res);
            $preg = '#<dlclass="disflexflexwrap"><dt><a.*href="(.*)".*><span.*>(.*)</span><em.*>(.*)</em></a>.*<a.*><spanclass="title.*>(.*)</span>.*</dl>#isU';
            $obj = [];
            $arr = [];
            preg_match_all($preg, $res, $obj);
            $count = (is_countable($obj) && count($obj) > 0) ? count($obj[0]) : 0;
            if ($count > 0) {
                for ($j = 0; $j < $count; $j++) {
                    $arr[$j]['url'] = $obj[1][$j];
                    $arr[$j]['title'] = $obj[4][$j];
                    $arr[$j]['beginDate'] = $obj[3][$j] . '-' . $obj[2][$j];
                }
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['title'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['title'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 1162;
                            $data['bidSiteName'] = '湖北省政府采购协会';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = $v['url'];
                            $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 1162 -> 湖北省政府采购协会');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 1162 -> 湖北省政府采购协会');
        }
        // 1214 四川招投标网
        for ($i = 1; $i <= 3; $i++) {
            $url = 'https://www.scbid.com/zbxx/17_'. $i . '.html';
            $res = global_curl($url);
            if($res !== false) {
                $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
                $preg = '#<divclass="row">.*<a.*href="(.*)".*>(.*)</a>.*<divclass="stime">(.*)</div></div>#isU';
                $obj = [];
                $arr = [];
                preg_match_all($preg, $res, $obj);
                $count = (is_countable($obj) && count($obj) > 0) ? count($obj[0]) : 0;
                if ($count > 0) {
                    for ($j = 0; $j < $count; $j++) {
                        $arr[$j]['url'] = $obj[1][$j];
                        $arr[$j]['title'] = $obj[2][$j];
                        $arr[$j]['beginDate'] = $obj[3][$j] > date('m-d') ? ((date('Y') - 1) . '-' . $obj[3][$j]) : (date('Y') . '-' . $obj[3][$j]);
                    }
                    foreach ($arr as $k => $v) {
                        $data = [];
                        if (!preg_match('#(' . $in_keywords . ')#', $v['title'])) {
                            continue;
                        } else {
                            if (preg_match('#(' . $out_keywords . ')#', $v['title'])) {
                                continue;
                            } else {
                                $data['bidSiteId'] = 1214;
                                $data['bidSiteName'] = '四川招投标网';
                                $data['bidTitle'] = $v['title'];
                                $data['bidUrl'] = $v['url'];
                                $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                                $data['inDate'] = date('Y-m-d H:i:s');
                                $final_arr[] = $data;
                            }
                        }
                    }
                } else {
                    log_message('error', '招标采集：未匹配到数据 1214 -> 四川招投标网 -> 第' . $i . '页');
                }
            } else {
                log_message('error', '招标采集：未获取到网页 1214 -> 四川招投标网 -> 第' . $i . '页');
            }
        }
        // 1219 厦门招投标网
        $url = 'https://www.xmztb.com/freecms/rest/v1/notice/selectInfoMoreChannel.do?siteId=a40f4aeb-3b75-4326-bbc9-6ebada8b71e9&channel=a4efeb63-7f73-4926-bb6e-a9928b2d2002&title=&purchaser=&purchaseManner=&openTenderCode=&operationStartTime=&operationEndTime=&agency=&noticeType=00101&purchaseNature=1&selectTimeName=noticeTime&projectSourceCode=&currPage=1&pageSize=15';
        $res = global_curl($url, [
            'httpheader' => [
                'Accept: */*',
                'Accept-Encoding: gzip, deflate, br, zstd',
                'Accept-Language: zh-CN,zh;q=0.9,en;q=0.8,en-GB;q=0.7,en-US;q=0.6',
                'Cache-Control: no-cache',
                'Connection: keep-alive',
                'Content-Type: application/json;charset=utf-8',
                'Host: www.xmztb.com',
                'Pragma: no-cache',
                'Referer: https://www.xmztb.com/freecms/site/wxpt/xmzb/index.html',
                'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36 Edg/148.0.0.0'
            ]
        ]);
        if($res !== false) {
            $obj = json_decode($res, true);
            $arr = $obj['data'] ?? [];
            if (is_array($arr) && count($arr) > 0) {
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['title'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['title'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 1219;
                            $data['bidSiteName'] = '厦门招投标网';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = 'https://www.xmztb.com'.$v['pageurl'];
                            $data['beginDate'] = $v['noticeTime'];
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 1219 -> 厦门招投标网');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 1219 -> 厦门招投标网');
        }
        // 1250 浙江企业采购信息服务网 【特殊通过浏览器刷新插件+自定义js获取数据后发送到本系统api并存数据库，每次采集从数据库拿】
        $res = $this->db->table('bid_temp')->select('res')->where('site', '浙江企业采购信息服务网')->get()->getRowArray()['res'] ?? '';
        if ($res) {
            $obj = json_decode($res, true);
            $arr = $obj['result']['data']['data'] ?? [];
            if (is_array($arr) && count($arr) > 0) {
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['title'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['title'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 1250;
                            $data['bidSiteName'] = '浙江企业采购信息服务网';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = 'https://b.zhengcaiyun.cn/luban/detail?parentId=550016&articleId='.$v['articleId'];
                            $data['beginDate'] = date('Y-m-d H:i:s', $v['publishDate'] / 1000);
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 1250 -> 浙江企业采购信息服务网');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 1250 -> 浙江企业采购信息服务网');
        }
        // 1254 中工阳光招采平台
        $url = 'https://ygzcpt.cn/sunshineApi/public/notice/page?columnCode=PURCHASE_ANNOUNCEMENT&noticeName=&acceptancePlatform=1&pageSize=50&pageNum=1';
        $res = global_curl($url);
        if ($res !== false) {
            $obj = json_decode($res, true);
            $arr = $obj['rows'] ?? [];
            if (is_array($arr) && count($arr) > 0) {
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['noticeName'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['noticeName'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 1254;
                            $data['bidSiteName'] = '中工阳光招采平台';
                            $data['bidTitle'] = $v['noticeName'];
                            $data['bidUrl'] = 'https://www.ygzcpt.cn/cms/tenderingDetail.html?id='.$v['id'];
                            $data['beginDate'] = $v['publishTime'];
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 1254 -> 中工阳光招采平台');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 1254 -> 中工阳光招采平台');
        }
        // 1298 国e平台 【特殊通过浏览器刷新插件+自定义js获取数据后发送到本系统api并存数据库，每次采集从数据库拿】
        $res = $this->db->table('bid_temp')->select('res')->where('site', '国e平台')->get()->getRowArray()['res'] ?? '';
        if ($res) {
            $obj = json_decode($res, true);
            $arr = $obj['results'] ?? [];
            if (is_array($arr) && count($arr) > 0) {
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['title'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['title'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 1298;
                            $data['bidSiteName'] = '国e平台';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = 'https://new.ebidding.com/e-portal/business/detail.html?id='.$v['pkId'];
                            $data['beginDate'] = date('Y-m-d H:i:s', strtotime(substr($v['showDate'], 0, 10)));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 1298 -> 国e平台');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 1298 -> 国e平台');
        }
        // 1317 团渝政企采购平台
        $url = 'https://www.tuanyu.net/api/app/vipShop/notice/getNoticePage';
        $res = global_curl($url, [
            'post' => 1,
            'postfields' => '{"page":1,"pageSize":50,"auditStatus":1,"key":"","start":"","end":"","registerStartTim":"","registerEndTime":"","projectCategory":"","activeType":"不限","procurementForm":"","currentSort":"comprehensive","totalCount":0,"change":"","result":""}',
            'httpheader' => [
                'Accept: application/json, text/plain, */*',
                'Accept-Encoding: gzip, deflate, br, zstd',
                'Accept-Language: zh-CN,zh;q=0.9,en;q=0.8,en-GB;q=0.7,en-US;q=0.6',
                'Cache-Control: no-cache',
                'Connection: keep-alive',
                'Content-Length: 245',
                'Content-Type: application/json;charset=UTF-8',
                'Host: www.tuanyu.net',
                'Origin: https://www.tuanyu.net',
                'Pragma: no-cache',
                'Referer: https://www.tuanyu.net/',
                'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36 Edg/150.0.0.0'
            ]
        ]);
        if ($res !== false) {
            $obj = json_decode($res, true);
            $arr = $obj['item']['list'] ?? [];
            if (is_array($arr) && count($arr) > 0) {
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['noticeName'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['noticeName'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 1317;
                            $data['bidSiteName'] = '团渝政企采购平台';
                            $data['bidTitle'] = $v['noticeName'];
                            $data['bidUrl'] = 'https://www.tuanyu.net/#/tender/noticeDetail?id='.$v['id'];
                            $data['beginDate'] = $v['createTime'];
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 1317 -> 团渝政企采购平台');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 1317 -> 团渝政企采购平台');
        }
        // 将最终数据入库
        if (!empty($final_arr)) {
            $this->db->transStart();
            foreach ($final_arr as $k => $v) {
                $v['bidTitle'] = str_replace(["\r","\n","\r\n",'{','}','"',"'"], '”', $v['bidTitle']);
                if (
                    $v['beginDate'] < date('Y-m-d H:i:s', strtotime('-10 day'))
                    || $this->db->table('bid_info')->where('bidUrl', $v['bidUrl'])->countAllResults() > 0
                ) {
                    continue;
                }
                $this->db->table('bid_info')->insertBatch($v);
            }
            $this->db->table('option')->where('optionName', 'bid_spider_0_1_status')->update(['optionValue' => 0]);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                log_message('error', '招标采集：招标信息入库失败（招标平台类）事务已回滚！');
            }
        }
    }
}
