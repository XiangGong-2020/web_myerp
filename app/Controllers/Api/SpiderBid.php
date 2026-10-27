<?php

namespace App\Controllers\Api;
use App\Controllers\BaseController;

set_time_limit(0); // 设置脚本运行时间无限制，直到脚本运行完毕
header('Access-Control-Allow-Origin:*'); // 指定允许所有域名访问，即允许跨域
header('Access-Control-Allow-Methods:POST'); // 响应类型
header('Access-Control-Allow-Headers:x-requested-with,content-type'); // 响应头设置
session_write_close();

class SpiderBid extends BaseController
{
    /* 浏览器常开采集：接收浏览器刷新插件自定义js脚本传输的cookie并进行保存备用 */
    public function saveCookie()
    {
        $site = $this->request->getGetPost('site');
        $cookie = $this->request->getGetPost('cookie');
        if ($site && $cookie) {
            if ($this->db->table('bid_temp')->where('site', $site)->countAllResults() > 0) {
                $this->db->table('bid_temp')->where('site', $site)->update(['cookie' => $cookie, 'modifyTime' => date('Y-m-d H:i:s')]);
            } else {
                $this->db->table('bid_temp')->insert(['site' => $site, 'cookie' => $cookie, 'modifyTime' => date('Y-m-d H:i:s')]);
            }
        }
        $json['status'] = 200;
        $json['msg'] = 'success';
        return $this->response->setJSON($json);
    }
    /* 浏览器常开采集：接收浏览器刷新插件自定义js脚本传输的res，根据情况选择是进行保存备用，还是直接匹配招标信息入库 */
    public function saveRes()
    {
        $site = $this->request->getGetPost('site');
        $res = $this->request->getGetPost('res');
        if ($site && $res) {
            $sites = [
                '50' => '云采通',
                '517' => '中国招标投标公共服务平台',
                '1200' => '精彩纵横'
            ];
            $key = array_search($site, $sites);
            if ($key !== false) { // 【特殊浏览器刷新插件直接传数据过来直接匹配入库】
                if ($res) {
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
                    if ($key == 50) { // 云采通
                        $obj = json_decode($res, true);
                        $arr = $obj ?? [];
                        if (is_array($arr) && count($arr) > 0) {
                            foreach ($arr as $k => $v) {
                                $data = [];
                                if (!preg_match('#(' . $in_keywords . ')#', $v['subject'])) {
                                    continue;
                                } else {
                                    if (preg_match('#(' . $out_keywords . ')#', $v['subject'])) {
                                        continue;
                                    } else {
                                        $data['bidSiteId'] = 50;
                                        $data['bidSiteName'] = '云采通';
                                        $data['bidTitle'] = $v['subject'];
                                        $data['bidUrl'] = 'https://www.yuncaitong.cn/publish/'.date('Y/m/d', intval($v['createTime'] / 1000)).'/'.$v['id'].'.shtml';
                                        $data['beginDate'] = date('Y-m-d H:i:s', intval($v['createTime'] / 1000));
                                        $data['inDate'] = date('Y-m-d H:i:s');
                                        $final_arr[] = $data;
                                    }
                                }
                            }
                        } else {
                            log_message('error', '招标采集：未匹配到数据 ' . $key . ' -> ' . $site);
                        }
                    } elseif ($key == 517) { // 中国招标投标公共服务平台
                        $preg = '#<tr><td.*id="(.*)".*<a.*href="javascript:urlOpen\(\'(.*)\'\)".*title="(.*)".*</tr>#isU';
                        $obj = [];
                        $arr = [];
                        preg_match_all($preg, $res, $obj);
                        $count = (is_countable($obj) && count($obj) > 0) ? count($obj[0]) : 0;
                        if ($count > 0) {
                            for ($j = 0; $j < $count; $j++) {
                                $arr[$j]['url'] = $obj[2][$j];
                                $arr[$j]['title'] = $obj[3][$j];
                                $arr[$j]['beginDate'] = date('Y-m-d H:i:s', strtotime($obj[1][$j]));
                            }
                            foreach ($arr as $k => $v) {
                                $data = [];
                                if (!preg_match('#(' . $in_keywords . ')#', $v['title'])) {
                                    continue;
                                } else {
                                    if (preg_match('#(' . $out_keywords . ')#', $v['title'])) {
                                        continue;
                                    } else {
                                        $data['bidSiteId'] = intval($key);
                                        $data['bidSiteName'] = $site;
                                        $data['bidTitle'] = $v['title'];
                                        $data['bidUrl'] = 'https://ctbpsp.com/#/bulletinDetail?uuid=' . $v['url'];
                                        $data['beginDate'] = $v['beginDate'];
                                        $data['inDate'] = date('Y-m-d H:i:s');
                                        $final_arr[] = $data;
                                    }
                                }
                            }
                        } else {
                            log_message('error', '招标采集：未匹配到数据 ' . $key . ' -> ' . $site);
                        }
                    } elseif ($key == 1200) { // 精彩纵横
                        $preg = '#<divclass="project-cardcard-model".*<a.*href="(.*)".*title="(.*)".*</a>.*>发布日期:.*<span.*>(.*)</span>.*</div>#isU';
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
                                        $data['bidSiteId'] = intval($key);
                                        $data['bidSiteName'] = $site;
                                        $data['bidTitle'] = $v['title'];
                                        $data['bidUrl'] = 'https://www.jczh.com' . $v['url'];
                                        $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                                        $data['inDate'] = date('Y-m-d H:i:s');
                                        $final_arr[] = $data;
                                    }
                                }
                            }
                        } else {
                            log_message('error', '招标采集：未匹配到数据 ' . $key . ' -> ' . $site);
                        }
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
                        $this->db->transComplete();
                        if ($this->db->transStatus() === false) {
                            log_message('error', '招标采集：招标信息入库失败（直传类）事务已回滚！');
                        }
                    }
                } else {
                    log_message('error', '招标采集：未从浏览器直接获取到res ' . $key . ' -> ' . $site);
                }
            } else {
                if ($this->db->table('bid_temp')->where('site', $site)->countAllResults() > 0) {
                    $this->db->table('bid_temp')->where('site', $site)->update(['res' => $res, 'modifyTime' => date('Y-m-d H:i:s')]);
                } else {
                    $this->db->table('bid_temp')->insert(['site' => $site, 'res' => $res, 'modifyTime' => date('Y-m-d H:i:s')]);
                }
            }
        }
        $json['status'] = 200;
        $json['msg'] = 'success';
        return $this->response->setJSON($json);
    }
    /* 招标废弃类 长期打不开 不再采集 每天检查一下看是否能再次打开 */
    public function trash()
    {
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



        // 555 公E采电子交易平台
        // 招采信息
        $url = 'https://www.enjoy5191.com/api/GetDataHandler.ashx?ieTime='.intval(microtime(true) * 1000).'&method=Web.GYC_GetProjectList&pageindex=1&pagesize=99&in_big_type=D%2CQ&NAME=&AREA_CODE=35&PUBLISHED_TIME_START='.date('Y-m-d', strtotime('-12 day')).'&PUBLISHED_TIME_END='.date('Y-m-d').'&STATUS=&TENDERER_NAME=&CODE=&UNIT_NAME=&TENDER_PROJECT_WAY=&SITES=2';
        $res = global_curl($url);
        if ($res !== false) {
            var_dump($res);
            $obj = json_decode($res, true);
            $arr = $obj['data'] ?? [];
            if (is_array($arr) && count($arr) > 0) {
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['NAME'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['NAME'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 555;
                            $data['bidSiteName'] = '公E采电子交易平台';
                            $data['bidTitle'] = $v['NAME'];
                            $data['bidUrl'] = $v['URL'];
                            $data['beginDate'] = $v['PUBLISHED_TIME'];
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 555 -> 公E采电子交易平台 -> 招采信息');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 555 -> 公E采电子交易平台 -> 招采信息');
        }
        // 网上竞价
        $url = 'https://www.enjoy5191.com/api/GetDataHandler.ashx?method=Web.YunJin_GetJiaoYiList&ieTime='.intval(microtime(true) * 1000).'&in_FILLING_ORGANIZATION=x30001,x30002,x90009&pagesize=99&pageindex=1&in_project_status=10%2C20%2C30%2C40%2C50%2C60%2C70&TYPE=%E8%B4%A7%E7%89%A9&AREA_CODE=&PUBLISHED_TIME_END='.date('Y-m-d').'&PUBLISHED_TIME_START='.date('Y-m-d', strtotime('-30 day')).'&BUDGET_END=&BUDGET_START=&SITES=2';
        $res = global_curl($url);
        if ($res !== false) {
            $obj = json_decode($res, true);
            $arr = $obj['data'] ?? [];
            if (is_array($arr) && count($arr) > 0) {
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['NAME'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['NAME'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 555;
                            $data['bidSiteName'] = '公E采电子交易平台';
                            $data['bidTitle'] = $v['NAME'];
                            $data['bidUrl'] = $v['URL'];
                            $data['beginDate'] = $v['PUBLISHED_TIME'];
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 555 -> 公E采电子交易平台 -> 网上竞价');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 555 -> 公E采电子交易平台 -> 网上竞价');
        }
        // 914 福建省国资采购平台
        $url = 'https://www.enjoy5191.com/api/GetDataHandler.ashx?pageindex=1&pagesize=100&method=Web.GZCG_GetJiaoYiList&in_BIG_TYPE=D,Q&NAME=&PUBLISHED_TIME_START=&PUBLISHED_TIME_END=&STATUS=1&AREA_CODE=&ieTime='.intval(microtime(true) * 1000).'&SITES=4';
        $res = global_curl($url);
        if($res !== false) {
            $obj = json_decode($res, true);
            $arr = $obj['data'] ?? [];
            if (is_array($arr) && count($arr) > 0) {
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['NAME'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['NAME'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 914;
                            $data['bidSiteName'] = '福建省国资采购平台';
                            $data['bidTitle'] = $v['NAME'];
                            $data['bidUrl'] = $v['URL'];
                            $data['beginDate'] = $v['PUBLISHED_TIME'];
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 914 -> 福建省国资采购平台');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 914 -> 福建省国资采购平台');
        }
        // 613 南京公共采购信息网
        $url = 'https://njgc.jfh.com/app/openTenderNotice/queryPage'; // 公开招标
        $res = global_curl($url, [
            'post' => 1,
            'postfields' => '{"rows":100,"type":"","page":1,"projectAttributionCode":"","purchaseItemsCode":"","releaseTimeStart":"","releaseTimeEnd":"","title":""}'
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
                            $data['bidSiteId'] = 613;
                            $data['bidSiteName'] = '南京公共采购信息网';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = 'https://njgc.jfh.com/purchase/detail?id='.$v['id'].'&type=1';
                            $data['beginDate'] = $v['releaseTime'];
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 613 -> 南京公共采购信息网 -> 公开招标');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 613 -> 南京公共采购信息网 -> 公开招标');
        }
        $url = 'https://njgc.jfh.com/app/negotiationNotice/queryPage'; // 竞争性谈判
        $res = global_curl($url, [
            'post' => 1,
            'postfields' => '{"rows":100,"type":"","page":1,"projectAttributionCode":"","purchaseItemsCode":"","releaseTimeStart":"","releaseTimeEnd":"","title":""}'
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
                            $data['bidSiteId'] = 613;
                            $data['bidSiteName'] = '南京公共采购信息网';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = 'https://njgc.jfh.com/purchase/detail?id='.$v['id'].'&type=3';
                            $data['beginDate'] = $v['releaseTime'];
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 613 -> 南京公共采购信息网 -> 竞争性谈判');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 613 -> 南京公共采购信息网 -> 竞争性谈判');
        }
        $url = 'https://njgc.jfh.com/app/consultationsNotice/queryPage'; // 竞争性磋商
        $res = global_curl($url, [
            'post' => 1,
            'postfields' => '{"rows":100,"type":"","page":1,"projectAttributionCode":"","purchaseItemsCode":"","releaseTimeStart":"","releaseTimeEnd":"","title":""}'
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
                            $data['bidSiteId'] = 613;
                            $data['bidSiteName'] = '南京公共采购信息网';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = 'https://njgc.jfh.com/purchase/detail?id='.$v['id'].'&type=4';
                            $data['beginDate'] = $v['releaseTime'];
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 613 -> 南京公共采购信息网 -> 竞争性磋商');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 613 -> 南京公共采购信息网 -> 竞争性磋商');
        }
        $url = 'https://njgc.jfh.com/app/inquiryNotice/queryPage'; // 询价公告
        $res = global_curl($url, [
            'post' => 1,
            'postfields' => '{"rows":100,"type":"","page":1,"projectAttributionCode":"","purchaseItemsCode":"","releaseTimeStart":"","releaseTimeEnd":"","title":""}'
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
                            $data['bidSiteId'] = 613;
                            $data['bidSiteName'] = '南京公共采购信息网';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = 'https://njgc.jfh.com/purchase/detail?id='.$v['id'].'&type=7';
                            $data['beginDate'] = $v['releaseTime'];
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 613 -> 南京公共采购信息网 -> 询价公告');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 613 -> 南京公共采购信息网 -> 询价公告');
        }
        // 650 阜阳技师学院
        $url = 'https://www.fyjsxy.com/fengcai/ztbgg/';
        $res = global_curl($url);
        if ($res !== false) {
            $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
            $preg = '#<liclass="news-title"><ahref="(.*)">(.*)<span>(.*)</span></a></li>#isU';
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
                            $data['bidSiteId'] = 650;
                            $data['bidSiteName'] = '阜阳技师学院';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = strpos($v['url'], 'http') === 0 ? $v['url'] : ('https://www.fyjsxy.com' . $v['url']);
                            $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 650 -> 阜阳技师学院');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 650 -> 阜阳技师学院');
        }
        // 758 湖北意达工程项目管理有限公司
        $url = 'http://www.hbydgc.cn/news/3/1.html';
        $res = global_curl($url);
        if ($res !== false) {
            $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
            $preg = '#<li><span>(.*)</span>.*<a.*href="(.*)".*title="(.*)".*</a></li>#isU';
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
                            $data['bidSiteId'] = 758;
                            $data['bidSiteName'] = '湖北意达工程项目管理有限公司';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = 'http://www.hbydgc.cn'.$v['url'];
                            $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 758 -> 湖北意达工程项目管理有限公司');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 758 -> 湖北意达工程项目管理有限公司');
        }
        // 784 赣榆区住房和城乡建设局
        $res = $this->db->table('bid_temp')->select('res')->where('site', '赣榆区住房和城乡建设局')->get()->getRowArray()['res'] ?? '';
        if ($res && strpos($res, 'projectName') !== false) {
            $obj = json_decode($res, true);
            $arr = $obj ?? [];
            if (is_array($arr) && count($arr) > 0) {
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['noticeTitle'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['noticeTitle'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 784;
                            $data['bidSiteName'] = '赣榆区住房和城乡建设局';
                            $data['bidTitle'] = $v['noticeTitle'];
                            $data['bidUrl'] = 'https://www.gyqzjjyth.com/#/tenderAnnouncementDetail?id='.$v['projectId'].'&sectionId='.$v['projectSectionId'];
                            $data['beginDate'] = $v['createTime'];
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 784 -> 赣榆区住房和城乡建设局');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 784 -> 赣榆区住房和城乡建设局');
        }
        // 833 四川省教育信息化与大数据中心
        $url = 'http://www.scedu.net/p/8/?StId=st_app_news_search_x637547015820606969_x__x__x_0_x_0';
        $res = global_curl($url);
        if($res !== false) {
            $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
            $preg = "#<divclass='div_item'><div.*<div.*>(.*)</div><a.*href='(.*)'.*>(.*)</a>#isU";
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
                            $data['bidSiteId'] = 833;
                            $data['bidSiteName'] = '四川省教育信息化与大数据中心';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = strpos($v['url'], 'http') === 0 ? $v['url'] : ('http://www.scedu.net'.$v['url']);
                            $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 833 -> 四川省教育信息化与大数据中心');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 833 -> 四川省教育信息化与大数据中心');
        }
        // 965 广西正海招标有限公司
        $url = 'http://www.gxzhzb.com/index.aspx';
        $res = global_curl($url);
        if($res !== false) {
            $res = mb_convert_encoding($res, "UTF-8", "GBK"); //转换编码
            $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
            $res_temp = [];
            preg_match('#<h1>招标公告</h1><a.*</a></div><ulclass="news_list">(.*)</ul>#isU', $res, $res_temp);
            $preg = '#<li><ahref=\'.*&(.*)\'.*title=\'(.*)\'.*</a><p>(.*)</p></li>#isU';
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
                            $data['bidSiteId'] = 965;
                            $data['bidSiteName'] = '广西正海招标有限公司';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = 'http://www.gxzhzb.com/index.aspx?' . $v['url']; // 网站各种防护，每次链接都不一样，且无法直接打开，所以只能链接首页，进去之后用户自己去找。
                            $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 965 -> 广西正海招标有限公司');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 965 -> 广西正海招标有限公司');
        }
        // 1135 昆明市体育学校
        $url = 'http://www.kmstx.cn/index/index/newslist/class/9.html';
        $res = global_curl($url);
        if($res !== false) {
            $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
            $preg = '#<liid="line_u.*<span>(.*)</span><a.*href="(.*)".*title="(.*)".*</a></li>#isU';
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
                            $data['bidSiteId'] = 1135;
                            $data['bidSiteName'] = '昆明市体育学校';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = strpos($v['url'], 'http') === 0 ? $v['url'] : ('http://www.kmstx.cn'.$v['url']);
                            $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 1135 -> 昆明市体育学校');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 1135 -> 昆明市体育学校');
        }
        // 1347 龙岩金衡招标代理服务有限公司 【特殊通过浏览器刷新插件+自定义js获取数据后发送到本系统api并存数据库，每次采集从数据库拿】
        $res = $this->db->table('bid_temp')->select('res')->where('site', '龙岩金衡招标代理服务有限公司')->get()->getRowArray()['res'] ?? '';
        if($res && strpos($res, '<table') !== false) {
            $preg = '#<trdata-index=.*<tddata-field="id"><div.*>(.*)</div></td><tddata-field="msgid"><div.*>(.*)</div></td><tddata-field="title".*><div.*>(.*)</div></td><tddata-field="adddate"><div.*>(.*)</div></td>.*</tr>#isU';
            $obj = [];
            $arr = [];
            preg_match_all($preg, $res, $obj);
            $count = (is_countable($obj) && count($obj) > 0) ? count($obj[0]) : 0;
            if ($count > 0) {
                for ($j = 0; $j < $count; $j++) {
                    $arr[$j]['url'] = $obj[1][$j] . '/' . $obj[2][$j] . '.html';
                    $arr[$j]['title'] = $obj[3][$j];
                    $arr[$j]['beginDate'] = $obj[4][$j];
                }
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['title'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['title'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 1347;
                            $data['bidSiteName'] = '龙岩金衡招标代理服务有限公司';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = 'http://lyjhzb.com/show/' . $v['url'];
                            $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 1347 -> 龙岩金衡招标代理服务有限公司');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 1347 -> 龙岩金衡招标代理服务有限公司');
        }


    }
    /* 测试方法 */
    public function test()
    {
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













        // 1371 云南艺术学院保卫处
        /* $url = 'http://www.szlhztb.cn/';
        $res = global_curl($url);
        if ($res !== false) {
            $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
            var_dump($res);
            $preg = '#<liid="line_u.*<a.*href="(.*)".*><span>(.*)</span><em>(.*)</em></a></li>#isU';
            $obj = [];
            $arr = [];
            preg_match_all($preg, $res, $obj);
            $count = (is_countable($obj) && count($obj) > 0) ? count($obj[0]) : 0;
            if ($count > 0) {
                var_dump($obj);
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
                            $data['bidSiteId'] = 1371;
                            $data['bidSiteName'] = '云南艺术学院保卫处';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = strpos($v['url'], 'http') === 0 ? $v['url'] : ('https://bwc.ynart.edu.cn/' . $v['url']);
                            $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 1371 -> 云南艺术学院保卫处');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 1371 -> 云南艺术学院保卫处');
        } */



        var_dump($final_arr);



        //$res = mb_convert_encoding($res, "UTF-8", "GBK"); //转换编码
        /*
            $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
            var_dump($res);
            $preg = '#<listyle="margin:30px0;font-size:15px;"><divclass="noleft"><spanclass="notop">(.*)</span><spanclass="nobottom">(.*)</span></div><ahref="(.*)".*title="(.*)".*</a>.*</li>#isU';
            $obj = [];
            $arr = [];
            preg_match_all($preg, $res, $obj);
            $count = (is_countable($obj) && count($obj) > 0) ? count($obj[0]) : 0;
            if ($count > 0) {
                var_dump($obj);
                for ($j = 0; $j < $count; $j++) {
                    $arr[$j]['url'] = $obj[3][$j];
                    $arr[$j]['title'] = $obj[4][$j];
                    $arr[$j]['beginDate'] = $obj[1][$j] . '-' . str_replace('/', '-', $obj[2][$j]);
                }
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['title'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['title'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 111;
                            $data['bidSiteName'] = '某某网站';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = strpos($v['url'], 'http') === 0 ? $v['url'] : ('http://www.lcsjsxy.cn' . str_replace('..', '', $v['url']));
                            $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
        */
        /*
            var_dump($res);
            $obj = json_decode($res, true);
            $arr = $obj['data'] ?? [];
            if (is_array($arr) && count($arr) > 0) {
                var_dump($arr);
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['title'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['title'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 111;
                            $data['bidSiteName'] = '某某网站';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = strpos($v['url'], 'http') === 0 ? $v['url'] : ('http://gljsxy.com/details/'.$v['seq']);
                            $data['beginDate'] = $v['publishDate'];
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
        */


    }
}
