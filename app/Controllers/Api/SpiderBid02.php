<?php

namespace App\Controllers\Api;
use App\Controllers\BaseController;

class SpiderBid02 extends BaseController
{
    /* 招标代理类 按每5分钟执行一次的标准设计 */
    public function index()
    {
        $this->db->table('option')->where('optionName', 'bid_spider_0_2_status')->update(['optionValue' => 1]);
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
        // 60 广东业信采购招标有限公司
        $url = 'http://www.yjcg.cc/page/206.html';
        $res = global_curl($url);
        if ($res !== false) {
            if ($res) {
                $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
                $preg = '#<divclass="pvlist">.*<a.*href="(.*)".*title="(.*)".*>.*</a>.*<divclass="pvtime">(.*)</div></div>#isU';
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
                                $data['bidSiteId'] = 60;
                                $data['bidSiteName'] = '广东业信采购招标有限公司';
                                $data['bidTitle'] = $v['title'];
                                $data['bidUrl'] = 'http://www.yjcg.cc' . $v['url'];
                                $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                                $data['inDate'] = date('Y-m-d H:i:s');
                                $final_arr[] = $data;
                            }
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 60 -> 广东业信采购招标有限公司');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 60 -> 广东业信采购招标有限公司');
        }
        // 103 临沂市沂蒙国际招标有限公司
        $url = 'http://www.ymzb.com/news/?list_40.html';
        $res = global_curl($url);
        if ($res !== false) {
            $res = mb_convert_encoding($res, "UTF-8", "GBK"); //转换编码
            $preg = "#<li><div class='addi'>&nbsp;(.*)</div><a.*href='(.*)'.*>(.*)</a></li>#isU";
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
                            $data['bidSiteId'] = 103;
                            $data['bidSiteName'] = '临沂市沂蒙国际招标有限公司';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = $v['url'];
                            $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 103 -> 临沂市沂蒙国际招标有限公司');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 103 -> 临沂市沂蒙国际招标有限公司');
        }
        // 120 南方招标采购网
        $urls = [
            '招标公告' => 'http://www.nfzbcg.cn/Project/Index?Sub=%E6%8B%9B%E6%A0%87%E5%85%AC%E5%91%8A',
            '竞价公告' => 'http://www.nfzbcg.cn/Auctioning/Index?Sub=%E7%AB%9E%E4%BB%B7%E5%85%AC%E5%91%8A'
        ];
        foreach ($urls as $index => $url) {
            $res = global_curl($url);
            if ($res !== false) {
                $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
                $preg = '#<li><ahref="(.*)".*>(.*)</a><span>(.*)</span></li>#isU';
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
                                $data['bidSiteId'] = 120;
                                $data['bidSiteName'] = '南方招标采购网';
                                $data['bidTitle'] = $v['title'];
                                $data['bidUrl'] = 'http://www.nfzbcg.cn' . $v['url'];
                                $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                                $data['inDate'] = date('Y-m-d H:i:s');
                                $final_arr[] = $data;
                            }
                        }
                    }
                } else {
                    log_message('error', '招标采集：未匹配到数据 120 -> 南方招标采购网 -> ' . $index);
                }
            } else {
                log_message('error', '招标采集：未获取到网页 120 -> 南方招标采购网 -> ' . $index);
            }
        }
        // 176 广东宇天招标代理有限公司
        $url = 'http://yutianzb.net/List.aspx?T=277F792AB6203455&S=7E3602A4CC92FD20&iPage=1';
        $res = global_curl($url);
        if ($res !== false) {
            $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
            $preg = '#<li><b><atarget.*href="(.*)".*title="(.*)">.*</a></b><div.*>(.*)</div></li>#isU';
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
                            $data['bidSiteId'] = 176;
                            $data['bidSiteName'] = '广东宇天招标代理有限公司';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = 'http://yutianzb.net/' . $v['url'];
                            $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 176-> 广东宇天招标代理有限公司');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 176 -> 广东宇天招标代理有限公司');
        }
        // 183 广东德润招标代理有限公司
        $url = 'http://www.gddrzb.com/list.php?cid=17';
        $res = global_curl($url);
        if ($res !== false) {
            $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
            $res_temp = [];
            preg_match('#<divclass="rightcon_h2">采购公告</div><divclass="rightcon_cc"><divclass="list"><ul>(.*)</ul>#isU', $res, $res_temp);
            $preg = '#<li><ahref="(.*)"><emstyle="color:;font-weight:;">(.*)</em><span>(.*)</span></a></li>#isU';
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
                            $data['bidSiteId'] = 183;
                            $data['bidSiteName'] = '广东德润招标代理有限公司';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = strpos($v['url'], 'http') === 0 ? $v['url'] : ('http://www.gddrzb.com/' . $v['url']);
                            $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 183 -> 广东德润招标代理有限公司');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 183 -> 广东德润招标代理有限公司');
        }
        // 185 广东群升招标代理有限公司
        $url = 'http://www.gdqunsheng.com/zhaobiaogonggao.html';
        $res = global_curl($url);
        if ($res !== false) {
            $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
            $preg = '#<divclass="show-item">.*<a.*href="(.*)".*title="(.*)">.*</a>.*</div>.*<span>(.*)</span></div>.*</div></div>#isU';
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
                            $data['bidSiteId'] = 185;
                            $data['bidSiteName'] = '广东群升招标代理有限公司';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = $v['url'];
                            $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 185-> 广东群升招标代理有限公司');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 185 -> 广东群升招标代理有限公司');
        }
        // 230 广西工程咨询集团有限公司
        $url = 'https://www.gxgczxjt.com/public-notices';
        $res = global_curl($url);
        if ($res !== false) {
            $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
            $preg = '#<aclass="articlegroupbg-whiterounded-xlp-5.*href="(.*)".*<h3.*>(.*)</h3>.*</div><div.*<span.*<span.*</span>(.*)</span>.*</a>#isU';
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
                            $data['bidSiteId'] = 230;
                            $data['bidSiteName'] = '广西工程咨询集团有限公司';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = strpos($v['url'], 'http') === 0 ? $v['url'] : ('https://www.gxgczxjt.com'.$v['url']);
                            $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 230 -> 广西工程咨询集团有限公司');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 230 -> 广西工程咨询集团有限公司');
        }
        // 240 国盛招标项目管理咨询（广州）有限公司
        $url = 'https://www.guoshengzbzx.com/page125';
        $res = global_curl($url);
        if ($res !== false) {
            $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
            $preg = '#<listyle="margin-bottom:3px;"><pclass="linktitle"><atarget="_blank".*articleid=".*"href="(.*)"title="(.*)">.*</a></p><pclass="time"><spanclass="wp-new-ar-pro-time">(.*)</span></p><pclass="wpart-border-line"></p></li>#isU';
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
                            $data['bidSiteId'] = 240;
                            $data['bidSiteName'] = '国盛招标项目管理咨询（广州）有限公司';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = $v['url'];
                            $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 240 -> 国盛招标项目管理咨询（广州）有限公司');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 240 -> 国盛招标项目管理咨询（广州）有限公司');
        }
        // 242 广东建宇项目管理有限公司
        $url = 'https://www.gdjianyu.com/api/notices?cat=gczb&subcat=%E6%8B%9B%E6%A0%87%E5%85%AC%E5%91%8A&q=&page=8&pageSize=20';
        $res = global_curl($url);
        if ($res !== false) {
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
                            $data['bidSiteId'] = 242;
                            $data['bidSiteName'] = '广东建宇项目管理有限公司';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = 'https://www.gdjianyu.com/'.$v['no'];
                            $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['date']));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 242 -> 广东建宇项目管理有限公司');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 242 -> 广东建宇项目管理有限公司');
        }
        // 255 广东志正招标有限公司
        $urls = [
            '招标项目' => 'https://www.zztender.com/announce/list.json?page=1&pageSize=100&systemType=1%2C3&announceType=1%2C6',
            '采购项目' => 'https://www.zztender.com/announce/list.json?page=1&pageSize=100&systemType=2&announceType=1'
        ];
        foreach ($urls as $index => $url) {
            $res = global_curl($url);
            if ($res !== false) {
                $obj = json_decode($res, true);
                $arr = $obj['result'] ?? [];
                if (is_array($arr) && count($arr) > 0) {
                    foreach ($arr as $k => $v) {
                        $data = [];
                        if (!preg_match('#(' . $in_keywords . ')#', $v['announceName'])) {
                            continue;
                        } else {
                            if (preg_match('#(' . $out_keywords . ')#', $v['announceName'])) {
                                continue;
                            } else {
                                $data['bidSiteId'] = 255;
                                $data['bidSiteName'] = '广东志正招标有限公司';
                                $data['bidTitle'] = $v['announceName'];
                                $data['bidUrl'] = 'https://www.zztender.com/announce/info?announceId='.$v['id'];
                                $data['beginDate'] = $v['publishDateYMD'];
                                $data['inDate'] = date('Y-m-d H:i:s');
                                $final_arr[] = $data;
                            }
                        }
                    }
                } else {
                    log_message('error', '招标采集：未匹配到数据 255 -> 广东志正招标有限公司 -> ' . $index);
                }
            } else {
                log_message('error', '招标采集：未获取到网页 255 -> 广东志正招标有限公司 -> ' . $index);
            }
        }
        // 260 广东有德招标采购有限公司
        $url = 'http://www.youde.net/yd_zbcg/portal/getArticleByType'; // 招标采购公告
        $res = global_curl($url, [
            'post' => 1,
            'postfields' => 'articleType=1&page=1'
        ]);
        if ($res !== false) {
            $obj = json_decode($res, true);
            $arr = $obj['obj']['rows'] ?? [];
            if (is_array($arr) && count($arr) > 0) {
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['title'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['title'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 260;
                            $data['bidSiteName'] = '广东有德招标采购有限公司';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = 'http://www.youde.net/yd_zbcg/portal/toDetail?articleId='.$v['articleId'];
                            $data['beginDate'] = $v['publishedTime'];
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 260 -> 广东有德招标采购有限公司 ->招标采购公告');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 260 -> 广东有德招标采购有限公司 -> 招标采购公告');
        }
        $url = 'http://47.107.227.20:8080/AgentApi/EProject/PageNotice?page=1&rows=10&NoticeType=0&PublicId=803'; // 竞价公告
        $res = global_curl($url);
        if ($res !== false) {
            $obj = json_decode($res, true);
            $arr = $obj['rows'] ?? [];
            if (is_array($arr) && count($arr) > 0) {
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['Title'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['Title'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 260;
                            $data['bidSiteName'] = '广东有德招标采购有限公司';
                            $data['bidTitle'] = $v['Title'];
                            $data['bidUrl'] = 'http://yd.youde.net/single-pages/notice-content-new.html?ProjectId='.$v['ProjectId'].'&NoticeType=0&page=1&rows=10&index=2&channelIndex=';
                            $data['beginDate'] = $v['NoticeDate'];
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 260 -> 广东有德招标采购有限公司 -> 竞价公告');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 260 -> 广东有德招标采购有限公司 -> 竞价公告');
        }
        // 280 粤采通（深圳）招标有限公司
        $url = 'http://www.yctszzb.com/yctszzb/vip_doc/24200420_4776661_0_1.html';
        $res = global_curl($url);
        if ($res !== false) {
            $res = mb_convert_encoding($res, "UTF-8", "GBK"); //转换编码
            $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
            if (strpos($res, 'document.cookie="js_clearance="') !== false) {
                $cookie_280 = [];
                preg_match('#vart="(.*)"#isU', $res, $cookie_280);
                $res = global_curl($url, [
                    'httpheader' => [
                        'Cookie: js_clearance=' . $cookie_280[1]
                    ]
                ]);
                $res = mb_convert_encoding($res, "UTF-8", "GBK"); //转换编码
                $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
            }
            if (strpos($res, 'document.cookie="js_clearance="') !== false) {
                $cookie_280 = [];
                preg_match('#vart="(.*)"#isU', $res, $cookie_280);
                $res = global_curl($url, [
                    'httpheader' => [
                        'Cookie: js_clearance=' . $cookie_280[1]
                    ]
                ]);
                $res = mb_convert_encoding($res, "UTF-8", "GBK"); //转换编码
                $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
            }
            if (strpos($res, 'document.cookie="js_clearance="') !== false) {
                $cookie_280 = [];
                preg_match('#vart="(.*)"#isU', $res, $cookie_280);
                $res = global_curl($url, [
                    'httpheader' => [
                        'Cookie: js_clearance=' . $cookie_280[1]
                    ]
                ]);
                $res = mb_convert_encoding($res, "UTF-8", "GBK"); //转换编码
                $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
            }
            $preg = '#<li><divclass="inner">.*<a.*href="(.*)".*title="(.*)".*>.*</a></span><span.*>(.*)</span></div></li>#isU';
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
                            $data['bidSiteId'] = 280;
                            $data['bidSiteName'] = '粤采通（深圳）招标有限公司';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = 'http://www.yctszzb.com'.$v['url'];
                            $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 280 -> 粤采通（深圳）招标有限公司');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 280 -> 粤采通（深圳）招标有限公司');
        }
        // 285 佛山市粤辉工程造价咨询事务所有限公司
        $url = 'https://www.fsyh.cn/download/download.php?class3=57';
        $res = global_curl($url);
        if ($res !== false) {
            $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
            $res_temp = [];
            preg_match('#<ulclass="list-grouplist-group-divideredlist-group-fullmet-pager-ajax">(.*)</ul>#isU', $res, $res_temp);
            $preg = '#<liclass="list-group-item"><divclass="media">.*<divclass="media-body">.*<h4class=".*"><a.*href="..(.*)"title="(.*)".*><span.*>.*</span></a></h4><small.*><span></span><spanclass="m-l-10">(.*)</span></small></div></div></li>#isU';
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
                            $data['bidSiteId'] = 285;
                            $data['bidSiteName'] = '佛山市粤辉工程造价咨询事务所有限公司';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = 'https://www.fsyh.cn'.$v['url'];
                            $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 285 -> 佛山市粤辉工程造价咨询事务所有限公司');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 285 -> 佛山市粤辉工程造价咨询事务所有限公司');
        }
        // 286 四川国际招标有限责任公司
        $url = 'http://www.sichuanbidding.com/enrollment.aspx?nid=27&typeid=67';
        $res = global_curl($url);
        if ($res !== false) {
            $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
            $preg = '#<divclass="data_colcol-lg-4col-sm-6">.*<span>(.*)</span>.*<span>(.*)</span>.*<span>-</span>.*<span>(.*)</span>.*<a.*href="(.*)".*><span>(.*)</span></a>#isU';
            $obj = [];
            $arr = [];
            preg_match_all($preg, $res, $obj);
            $count = (is_countable($obj) && count($obj) > 0) ? count($obj[0]) : 0;
            if ($count > 0) {
                for ($j = 0; $j < $count; $j++) {
                    $arr[$j]['url'] = $obj[4][$j];
                    $arr[$j]['title'] = $obj[5][$j];
                    $arr[$j]['beginDate'] = $obj[2][$j].'-'.$obj[3][$j].'-'.$obj[1][$j];
                }
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['title'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['title'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 286;
                            $data['bidSiteName'] = '四川国际招标有限责任公司';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = 'http://www.sichuanbidding.com' . $v['url'];
                            $data['beginDate'] = $v['beginDate'];
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 286 -> 四川国际招标有限责任公司');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 286 -> 四川国际招标有限责任公司');
        }
        // 293 福建国诚招标有限公司
        $urls = [
            '政府采购项目信息公告（集中采购目录内及采购限额标准以上）' => 'http://www.fjgczb.com/articleList.shtml?type=7',
            '自行组织采购项目信息公告（集中采购目录外及采购限额标准以下）' => 'http://www.fjgczb.com/articleList.shtml?type=13',
            '国有企业采购' => 'http://www.fjgczb.com/articleList.shtml?type=26',
            '网上竞价' => 'http://www.fjgczb.com/articleList.shtml?type=10',
            '漳州分公司' => 'http://www.fjgczb.com/articleList.shtml?type=24',
            '厦门分公司' => 'http://www.fjgczb.com/articleList.shtml?type=35'
        ];
        foreach ($urls as $index => $url) {
            $res = global_curl($url);
            if ($res !== false) {
                $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
                $res_temp = [];
                preg_match('#<ulclass="right_ul">(.*)</ul>#isU', $res, $res_temp);
                $preg = '#<li><atitle="(.*)"href="(.*)">.*</a><span>(.*)</span></li>#isU';
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
                                $data['bidSiteId'] = 293;
                                $data['bidSiteName'] = '福建国诚招标有限公司';
                                $data['bidTitle'] = $v['title'];
                                $data['bidUrl'] = $v['url'];
                                $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                                $data['inDate'] = date('Y-m-d H:i:s');
                                $final_arr[] = $data;
                            }
                        }
                    }
                } else {
                    log_message('error', '招标采集：未匹配到数据 293 -> 福建国诚招标有限公司 -> ' . $index);
                }
            } else {
                log_message('error', '招标采集：未获取到网页 293 -> 福建国诚招标有限公司 -> ' . $index);
            }
        }
        // 299 江西省机电设备招标有限公司
        for ($i = 1; $i <= 5; $i++) {
            $url = 'http://118.31.74.217:9000/website/notice/page?noticeType=2&keyword=&page='.$i.'&size=20';
            $res = global_curl($url);
            if ($res !== false) {
            $obj = json_decode($res, true);
            $arr = $obj['data']['records'] ?? [];
            if (is_array($arr) && count($arr) > 0) {
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['noticeTitle'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['noticeTitle'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 299;
                            $data['bidSiteName'] = '江西省机电设备招标有限公司';
                            $data['bidTitle'] = $v['noticeTitle'];
                            $data['bidUrl'] = 'http://www.jxbidding.com/notice/bid/detail/'.$v['bid'];
                            $data['beginDate'] = $v['noticeReleaseTime'];
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
                } else {
                    log_message('error', '招标采集：未匹配到数据 299 -> 江西省机电设备招标有限公司 第 ' . $i . ' 页');
                }
            } else {
                log_message('error', '招标采集：未获取到网页 299 -> 江西省机电设备招标有限公司 第 ' . $i . ' 页');
            }
        }
        // 305 河源市采阳招标代理有限公司
        $url = 'http://www.hycyzb.com/zbxx/';
        $res = global_curl($url);
        if ($res !== false) {
            $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
            $res_temp = [];
            preg_match('#<ulclass="post_list">(.*)</ul>#isU', $res, $res_temp);
            $preg = '#<li><a.*href="(.*)".*title="(.*)".*>.*<span>(.*)</span></a></li>#isU';
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
                            $data['bidSiteId'] = 305;
                            $data['bidSiteName'] = '河源市采阳招标代理有限公司';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = 'http://www.hycyzb.com/zbxx/' . $v['url'];
                            $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 305 -> 河源市采阳招标代理有限公司');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 305 -> 河源市采阳招标代理有限公司');
        }
        // 309 河南省教育招标服务有限公司
        $url = 'https://www.hnzbedu.cn/bidding-announcement/';
        $res = global_curl($url);
        if ($res !== false) {
            $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
            $preg = '#<li><bclass="news-type">.*<a.*href="(.*)".*title="(.*)".*</a><spanclass="news-date">(.*)</span></li>#isU';
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
                            $data['bidSiteId'] = 309;
                            $data['bidSiteName'] = '河南省教育招标服务有限公司';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = 'https://www.hnzbedu.cn'.$v['url'];
                            $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 309 -> 河南省教育招标服务有限公司');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 309 -> 河南省教育招标服务有限公司');
        }
        // 356 广西科联招标中心有限公司
        $url = 'https://gnkl.zcjb.com.cn/cms/api/dynamicData/queryContentPage';
        $res = global_curl($url, [
            'post' => 1,
            'postfields' => '{"pageNo":1,"pageSize":99,"dto":{"siteId":"758","categoryId":"15277","publishDate":"","publishEndDate":""}}'
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
                            $data['bidSiteId'] = 356;
                            $data['bidSiteName'] = '广西科联招标中心有限公司';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = 'https://gnkl.zcjb.com.cn/cms/gxkl/webfile/detail/index.html?contentId='.$v['id'];
                            $data['beginDate'] = $v['publishDate'];
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 356 -> 广西科联招标中心有限公司');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 356 -> 广西科联招标中心有限公司');
        }
        // 361 广西中启招标咨询有限公司
        $url = 'http://www.gxzqzb.com/col.jsp?id=103';
        $res = global_curl($url, [
            'encoding' => 'UTF-8',
            'httpheader' => [
                'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7',
                'Accept-Language: zh-CN,zh;q=0.9,en;q=0.8,en-GB;q=0.7,en-US;q=0.6',
                'Cache-Control: no-cache',
                'Connection: keep-alive',
                'Host: www.gxzqzb.com',
                'Pragma: no-cache',
                'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/118.0.0.0 Safari/537.36 Edg/118.0.2088.46'
            ]
        ]);
        if ($res !== false) {
            $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
            $preg = "#<divtopClassName='top1'topSwitch='on'newsId='.*'newsName='(.*)'.*><div.*><div.*>(.*)</div><div.*>(.*)</div><a.*href=\"(.*)\".*></a></div>#isU";
            $obj = [];
            $arr = [];
            preg_match_all($preg, $res, $obj);
            $count = (is_countable($obj) && count($obj) > 0) ? count($obj[0]) : 0;
            if ($count > 0) {
                for ($j = 0; $j < $count; $j++) {
                    $arr[$j]['url'] = $obj[4][$j];
                    $arr[$j]['title'] = $obj[1][$j];
                    $arr[$j]['beginDate'] = str_replace('/', '-' , $obj[3][$j]) . '-' . $obj[2][$j];
                }
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['title'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['title'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 361;
                            $data['bidSiteName'] = '广西中启招标咨询有限公司';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = $v['url'];
                            $data['beginDate'] = $v['beginDate'];
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 361 -> 广西中启招标咨询有限公司');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 361 -> 广西中启招标咨询有限公司');
        }
        // 371 广东省机电设备招标中心有限公司
        $url = 'https://www.gdebidding.com/zbxxgg/index.jhtml';
        $res = global_curl($url);
        if ($res !== false) {
            $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
            $preg = '#<divclass="border-bottomborder-dashedpadding-toppadding-bottomheight"><a.*href="(.*)".*title="(.*)".*>.*</a><span.*>(.*)</span></div>#isU';
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
                            $data['bidSiteId'] = 371;
                            $data['bidSiteName'] = '广东省机电设备招标中心有限公司';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = 'https://www.gdebidding.com' . $v['url'];
                            $data['beginDate'] = $v['beginDate'];
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 371 -> 广东省机电设备招标中心有限公司');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 371 -> 广东省机电设备招标中心有限公司');
        }
        // 402 甘肃中远天成项目管理咨询有限公司
        $url = 'http://www.gszytc.com/news_2862426.html';
        $res = global_curl($url);
        if ($res !== false) {
            $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
            $preg = '#<li><divclass="m-title"><a.*href="(.*)".*>(.*)</a><span>发布时间:(.*)</span>.*</li>#isU';
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
                            $data['bidSiteId'] = 402;
                            $data['bidSiteName'] = '甘肃中远天成项目管理咨询有限公司';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = 'http://www.gszytc.com' . $v['url'];
                            $data['beginDate'] = $v['beginDate'];
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 402 -> 甘肃中远天成项目管理咨询有限公司');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 402 -> 甘肃中远天成项目管理咨询有限公司');
        }
        // 403 广东永正招标代理有限公司
        $url = 'http://www.gdyzzb.com/Article/ArticleList.aspx?menuId=18';
        $res = global_curl($url);
        if ($res !== false) {
            $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
            $preg = '#<li><aclass="aArItem".*href=\'(.*)\'.*title="(.*)"><span.*>.*</span><spanclass="spDateellipsis">(.*)</span></a></li>#isU';
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
                            $data['bidSiteId'] = 403;
                            $data['bidSiteName'] = '广东永正招标代理有限公司';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = 'http://www.gdyzzb.com/Article/' . $v['url'];
                            $data['beginDate'] = $v['beginDate'];
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 403 -> 广东永正招标代理有限公司');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 403 -> 广东永正招标代理有限公司');
        }
        // 404 福建省宏瑞招标代理有限公司
        $urls = [
            '招标公告' => 'http://www.fjhongrui.com/index.php/3/',
            '网上竞价' => 'http://www.fjhongrui.com/index.php/4/'
        ];
        foreach ($urls as $index => $url) {
            $res = global_curl($url);
            if ($res !== false) {
                $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
                $preg = '#<article>.*<a.*href="(.*)">(.*)</a></h2><div.*>(.*)--.*</div></article>#isU';
                $obj = [];
                $arr = [];
                preg_match_all($preg, $res, $obj);
                $count = (is_countable($obj) && count($obj) > 0) ? count($obj[0]) : 0;
                if ($count > 0) {
                    for ($j = 0; $j < $count; $j++) {
                        $arr[$j]['url'] = $obj[1][$j];
                        $arr[$j]['title'] = $obj[2][$j];
                        $arr[$j]['beginDate'] = str_replace('/', '-', $obj[3][$j]);
                    }
                    foreach ($arr as $k => $v) {
                        $data = [];
                        if (!preg_match('#(' . $in_keywords . ')#', $v['title'])) {
                            continue;
                        } else {
                            if (preg_match('#(' . $out_keywords . ')#', $v['title'])) {
                                continue;
                            } else {
                                $data['bidSiteId'] = 404;
                                $data['bidSiteName'] = '福建省宏瑞招标代理有限公司';
                                $data['bidTitle'] = $v['title'];
                                $data['bidUrl'] = 'http://www.fjhongrui.com' . $v['url'];
                                $data['beginDate'] = $v['beginDate'];
                                $data['inDate'] = date('Y-m-d H:i:s');
                                $final_arr[] = $data;
                            }
                        }
                    }
                } else {
                    log_message('error', '招标采集：未匹配到数据 404 -> 福建省宏瑞招标代理有限公司 -> ' . $index);
                }
            } else {
                log_message('error', '招标采集：未获取到网页 404 -> 福建省宏瑞招标代理有限公司 -> ' . $index);
            }

        }
        // 409 湖北省成套招标股份有限公司
        $url = 'https://www.hubeibidding.com/list/31.html';
        $res = global_curl($url);
        if ($res !== false) {
            $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
            $res_temp = [];
            preg_match('#<divclass="dynamic_content_center".*>(.*)</div>#isU', $res, $res_temp);
            $preg = '#<a.*href="(.*)".*><span><p>(.*)月</p><p>(.*)</p></span><h3>(.*)</h3>.*</a>#isU';
            $obj = [];
            $arr = [];
            is_countable($res_temp) && count($res_temp) > 0 && preg_match_all($preg, $res_temp[1], $obj);
            $count = (is_countable($obj) && count($obj) > 0) ? count($obj[0]) : 0;
            if ($count > 0) {
                for ($j = 0; $j < $count; $j++) {
                    $arr[$j]['url'] = $obj[1][$j];
                    $arr[$j]['title'] = $obj[4][$j];
                    $arr[$j]['beginDate'] = ($obj[2][$j] . '-' . $obj[3][$j]) > date('m-d') ? (date('Y', strtotime('-1 year')) . '-' . $obj[2][$j] . '-' . $obj[3][$j]) : ((date('Y') . '-' . $obj[2][$j] . '-' . $obj[3][$j]));
                }
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['title'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['title'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 409;
                            $data['bidSiteName'] = '湖北省成套招标股份有限公司';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = 'https://www.hubeibidding.com' . $v['url'];
                            $data['beginDate'] = $v['beginDate'];
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 409 -> 湖北省成套招标股份有限公司');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 409 -> 湖北省成套招标股份有限公司');
        }
        // 453 广东中鑫招标有限公司
        $url = 'http://www.zxbidding.com/zbgg';
        $res = global_curl($url);
        if ($res !== false) {
            $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
            $res_temp = [];
            preg_match('#<divclass="xwlb"><ul>(.*)</ul>#isU', $res, $res_temp);
            $preg = '#<li><ahref="(.*)".*<p.*>(.*)</p><divclass="date-box"><pclass="date">发布时间：(.*)</p><p.*</p></div></a></li>#isU';
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
                            $data['bidSiteId'] = 453;
                            $data['bidSiteName'] = '广东中鑫招标有限公司';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = 'http://www.zxbidding.com' . $v['url'];
                            $data['beginDate'] = $v['beginDate'];
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 453 -> 广东中鑫招标有限公司');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 453 -> 广东中鑫招标有限公司');
        }
        // 466 广西国力招标有限公司
        $url = 'https://www.gxguoli.cn/channels/59.html';
        $res = global_curl($url);
        if ($res !== false) {
            $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
            $preg = '#<li><aclass="title"href="(.*)">(.*)</a><spanclass="date">(.*)</span></li>#isU';
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
                            $data['bidSiteId'] = 466;
                            $data['bidSiteName'] = '广西国力招标有限公司';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = 'https://www.gxguoli.cn' . $v['url'];
                            $data['beginDate'] = $v['beginDate'];
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 466 -> 广西国力招标有限公司');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 466 -> 广西国力招标有限公司');
        }
        // 476 江西省鼎跃招标咨询有限公司
        $url = 'http://www.jxdyzb.com/channels/95.html';
        $res = global_curl($url);
        if ($res !== false) {
            $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
            $preg = '#<divclass="rqi-right-flex">.*<divclass="rqi-data-top">(.*)</div><div>(.*)</div>.*<a.*</a>.*<ahref="(.*)">(.*)</a>#isU';
            $obj = [];
            $arr = [];
            preg_match_all($preg, $res, $obj);
            $count = (is_countable($obj) && count($obj) > 0) ? count($obj[0]) : 0;
            if ($count > 0) {
                for ($j = 0; $j < $count; $j++) {
                    $arr[$j]['url'] = $obj[3][$j];
                    $arr[$j]['title'] = $obj[4][$j];
                    $arr[$j]['beginDate'] = $obj[2][$j] . '-' . $obj[1][$j];
                }
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['title'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['title'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 476;
                            $data['bidSiteName'] = '江西省鼎跃招标咨询有限公司';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = 'http://www.jxdyzb.com' . $v['url'];
                            $data['beginDate'] = $v['beginDate'];
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 476 -> 江西省鼎跃招标咨询有限公司');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 476 -> 江西省鼎跃招标咨询有限公司');
        }
        // 480 广东国星工程咨询有限公司
        $url = 'http://www.gdguoxing.cn/html/jxtl/zbgg/zhaobiaogonggao/index.html';
        $res = global_curl($url);
        if ($res !== false) {
            $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
            $preg = '#<divclass="widget-body">.*<ahref="(.*)"title="(.*)".*</a></h4><p.*>(.*)<span></span></p>.*</div>#isU';
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
                            $data['bidSiteId'] = 480;
                            $data['bidSiteName'] = '广东国星工程咨询有限公司';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = $v['url'];
                            $data['beginDate'] = $v['beginDate'];
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 480 -> 广东国星工程咨询有限公司');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 480 -> 广东国星工程咨询有限公司');
        }
        // 482 福建盛鑫招标代理有限公司
        $url = 'http://new.fjsxzb.com/api/Notice/Get?PageIndex=1&PageSize=10&OrderByFileds=p.PublishTime+desc';
        $res = global_curl($url);
        if ($res !== false) {
            $obj = json_decode($res, true);
            $arr = $obj['response']['data'] ?? [];
            if (is_array($arr) && count($arr) > 0) {
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['Title'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['Title'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 482;
                            $data['bidSiteName'] = '福建盛鑫招标代理有限公司';
                            $data['bidTitle'] = $v['Title'];
                            $data['bidUrl'] = 'http://www.fjsxzb.com/#/purchasedetail?type='.$v['Type'].'&id='.$v['Id'];
                            $data['beginDate'] = $v['PublishTime'];
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 482 -> 福建盛鑫招标代理有限公司 -> 招标信息');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 482 -> 福建盛鑫招标代理有限公司 -> 招标信息');
        }
        $url = 'http://new.fjsxzb.com/api/Tender/Get?PageIndex=1&PageSize=10&OrderByFileds=Id+desc&Multiple=';
        $res = global_curl($url);
        if ($res !== false) {
            $obj = json_decode($res, true);
            $arr = $obj['response']['data'] ?? [];
            if (is_array($arr) && count($arr) > 0) {
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['Name'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['Name'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 482;
                            $data['bidSiteName'] = '福建盛鑫招标代理有限公司';
                            $data['bidTitle'] = $v['Name'];
                            $data['bidUrl'] = 'http://www.fjsxzb.com/#/Tenderdetail?id='.$v['Id'];
                            $data['beginDate'] = $v['CreateTime'];
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 482 -> 福建盛鑫招标代理有限公司 -> 网上竞价');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 482 -> 福建盛鑫招标代理有限公司 -> 网上竞价');
        }
        // 500 常州润邦招标代理有限公司
        $url = 'http://runbase.cn/ProcurementNotice/';
        $res = global_curl($url);
        if ($res !== false) {
            $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
            $preg = '#<liclass="lh-3border-bottom-dashed">.*<a.*href="(.*)".*<span.*>(.*)</span>.*<spanclass="float-right">(.*)</span></a></li>#isU';
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
                            $data['bidSiteId'] = 500;
                            $data['bidSiteName'] = '常州润邦招标代理有限公司';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = 'http://runbase.cn' . $v['url'];
                            $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 500 -> 常州润邦招标代理有限公司');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 500 -> 常州润邦招标代理有限公司');
        }
        // 533 中山远信工程咨询招标有限公司
        $url = 'https://www.xhtczhs.com/news/1/';
        $res = global_curl($url);
        if ($res !== false) {
            $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
            $preg = '#<divclass="cbox-10p_loopitem">.*<a.*href="(.*)".*>(.*)</a>.*><Pclass="e_time.*>(.*)</P></div></div></div>#isU';
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
                            $data['bidSiteId'] = 533;
                            $data['bidSiteName'] = '中山远信工程咨询招标有限公司';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = 'https://www.xhtczhs.com' . $v['url'];
                            $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 533 -> 中山远信工程咨询招标有限公司');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 533 -> 中山远信工程咨询招标有限公司');
        }
        // 544 海南省教学仪器设备招标中心有限公司
        $url = 'http://www.hainjy.com/pur.jspx';
        $res = global_curl($url);
        if ($res !== false) {
            $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
            $preg = '#<li><span><b>.*</b>.*</span><em><a.*href="(.*)".*title="(.*)".*>.*</a></em><i>(.*)</i></li>#isU';
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
                            $data['bidSiteId'] = 544;
                            $data['bidSiteName'] = '海南省教学仪器设备招标中心有限公司';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = $v['url'];
                            $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 544 -> 海南省教学仪器设备招标中心有限公司');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 544 -> 海南省教学仪器设备招标中心有限公司');
        }
        // 554 广东国科招标有限公司 【特殊通过浏览器刷新插件+自定义js获取数据后发送到本系统api并存数据库，每次采集从数据库拿】
        $res = $this->db->table('bid_temp')->select('res')->where('site', '广东国科招标有限公司')->get()->getRowArray()['res'] ?? '';
        if($res) {
            $preg = '#<liclass=.*<a.*href="(.*)".*<b>(.*)</b><span>(.*)</span></a></li>#isU';
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
                            $data['bidSiteId'] = 554;
                            $data['bidSiteName'] = '广东国科招标有限公司';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = 'http://www.gzgkbidding.com' . $v['url'];
                            $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 554 -> 广东国科招标有限公司');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 554 -> 广东国科招标有限公司');
        }
        // 560 佛山市博众招标代理有限公司
        $url = 'https://www.bzzbdl.com/Designer/Common/GetData';
        $res = global_curl($url, [
            'post' => 1,
            'postfields' => 'dataType=news&key=&pageIndex=0&pageSize=20&selectCategory=526232&selectId=&dateFormater=yyyy-MM-dd&orderByField=createtime&orderByType=desc&templateId=0&postData=&es=false&setTop=true'
        ]);
        if ($res !== false) {
            $obj = json_decode($res, true);
            $arr = $obj['Data'] ?? [];
            if (is_array($arr) && count($arr) > 0) {
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['Name'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['Name'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 560;
                            $data['bidSiteName'] = '佛山市博众招标代理有限公司';
                            $data['bidTitle'] = $v['Name'];
                            $data['bidUrl'] = 'https://www.bzzbdl.com'.$v['LinkUrl'];
                            $data['beginDate'] = $v['QTime'];
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 560 -> 佛山市博众招标代理有限公司');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 560 -> 佛山市博众招标代理有限公司');
        }
        // 571 广东业胜招标代理有限公司
        $url = 'http://www.jyyxcg.com/dnews.php?nclassid=3';
        $res = global_curl($url);
        if ($res !== false) {
            $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
            $res_temp = [];
            preg_match('#<tablewidth="100%"border="0"align="center"cellpadding="0"cellspacing="0"style="margin:0auto;">(.*)</table>#isU', $res, $res_temp);
            $preg = '#<tr.*<ahref="(.*)">(.*)</a>.*\[(.*)\]</td></tr>#isU';
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
                            $data['bidSiteId'] = 571;
                            $data['bidSiteName'] = '广东业胜招标代理有限公司';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = 'http://www.jyyxcg.com/' . $v['url'];
                            $data['beginDate'] = $v['beginDate'];
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 571 -> 广东业胜招标代理有限公司');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 571 -> 广东业胜招标代理有限公司');
        }
        // 577 马鞍山市非进场项目阳光交易服务平台
        $url = 'http://www.masygjy.com/XZEpointWebBuilder/rest/secaction/getSecInfoListYzmygjy';
        $res = global_curl($url, [
            'post' => 1,
            'postfields' => 'siteGuid=3a507ad4-f2ac-473c-845d-e69c560629c1&categoryNum=001002&content=&pageIndex=0&pageSize=18&YZM=&ImgGuid=&startdate=&enddate=',
            'httpheader' => [
                'Accept: application/json, text/javascript, */*; q=0.01',
                'Accept-Language: zh-CN,zh;q=0.9,en;q=0.8,en-GB;q=0.7,en-US;q=0.6',
                'Cache-Control: no-cache',
                'Connection: keep-alive',
                'Content-Length: 131',
                'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
                'Host: www.masygjy.com',
                'Origin: http://www.masygjy.com',
                'Pragma: no-cache',
                'Referer: http://www.masygjy.com/jyxx/001002/trade_info.html',
                'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/127.0.0.0 Safari/537.36 Edg/127.0.0.0'
            ]
        ]);
        if ($res !== false) {
            $obj = json_decode($res, true);
            $arr = $obj['custom']['infodata'] ?? [];
            if (is_array($arr) && count($arr) > 0) {
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['title'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['title'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 577;
                            $data['bidSiteName'] = '马鞍山市非进场项目阳光交易服务平台';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = 'http://www.masygjy.com'.$v['infourl'];
                            $data['beginDate'] = $v['infodate'];
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 577 -> 马鞍山市非进场项目阳光交易服务平台');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 577-> 马鞍山市非进场项目阳光交易服务平台');
        }
        // 638 盛和集团
        $url = 'http://www.cnshzb.com/list-zhaobiaogonggao.html';
        $res = global_curl($url, [
            'header' => 1
        ]);
        if ($res !== false) {
            $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
            if (strpos($res, 'Set-Cookie') !== false) {
                $cookie_638 = [];
                preg_match_all('#Set-Cookie:(.*);#isU', $res, $cookie_638);
                $res = global_curl($url, [
                    'header' => 1,
                    'httpheader' => [
                        'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7',
                        'Accept-Encoding: gzip, deflate',
                        'Accept-Language: zh-CN,zh;q=0.9,en;q=0.8,en-GB;q=0.7,en-US;q=0.6',
                        'Cache-Control: no-cache',
                        'Connection: keep-alive',
                        'Cookie: ' . $cookie_638[1][0],
                        'Host: www.cnshzb.com',
                        'Pragma: no-cache',
                        'Referer: http://www.cnshzb.com/list-zhaobiaogonggao.html',
                        'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36 Edg/148.0.0.0'
                    ]
                ]);
                if ($res !== false) {
                    $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
                    if (strpos($res, 'Set-Cookie') !== false) {
                        $cookie_638 = [];
                        preg_match('#Set-Cookie:(.*);#isU', $res, $cookie_638);
                        $res = global_curl($url, [
                            'header' => 1,
                            'httpheader' => [
                                'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7',
                                'Accept-Encoding: gzip, deflate',
                                'Accept-Language: zh-CN,zh;q=0.9,en;q=0.8,en-GB;q=0.7,en-US;q=0.6',
                                'Cache-Control: no-cache',
                                'Connection: keep-alive',
                                'Cookie: ' . $cookie_638[1],
                                'Host: www.cnshzb.com',
                                'Pragma: no-cache',
                                'Referer: http://www.cnshzb.com/list-zhaobiaogonggao.html',
                                'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36 Edg/148.0.0.0'
                            ]
                        ]);
                    }
                }
            }
            $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
            $res_temp = [];
            preg_match('#<divclass="hr-itemspb90">(.*)</div>#isU', $res, $res_temp);
            $preg = '#<ahref="(.*)"title="(.*)".*<spanclass="date.*<i.*</i>(.*)</span></a>#isU';
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
                            $data['bidSiteId'] = 638;
                            $data['bidSiteName'] = '盛和集团';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = $v['url'];
                            $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 638 -> 盛和集团');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 638 -> 盛和集团');
        }
        // 642 广州顺为招标采购有限公司
        $url = 'http://www.gzswbc.com/index.html'; // 首页
        $res = global_curl($url);
        if ($res !== false) {
            $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
            $preg = '#<li><astyle="width:65%;"href="(.*)"title="(.*)".*</a><spanclass="bidDate">(.*)</span>.*</li>#isU';
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
                    if ($k < 20) {
                        $data = [];
                        if (!preg_match('#(' . $in_keywords . ')#', $v['title'])) {
                            continue;
                        } else {
                            if (preg_match('#(' . $out_keywords . ')#', $v['title'])) {
                                continue;
                            } else {
                                $data['bidSiteId'] = 642;
                                $data['bidSiteName'] = '广州顺为招标采购有限公司';
                                $data['bidTitle'] = $v['title'];
                                $data['bidUrl'] = 'http://www.gzswbc.com' . $v['url'];
                                $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                                $data['inDate'] = date('Y-m-d H:i:s');
                                $final_arr[] = $data;
                            }
                        }
                    } else {
                        break;
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 642 -> 广州顺为招标采购有限公司 -> 首页');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 642 -> 广州顺为招标采购有限公司 -> 首页');
        }
        $url = 'http://www.gzswbc.com/dzhzfcgpt/index.html'; // 电子化采购平台
        $res = global_curl($url);
        if ($res !== false) {
            $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
            $preg = '#<li><astyle="width:.*href="(.*)"title="(.*)".*</a>.*<spanclass="bidDate".*>(.*)</span></li>#isU';
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
                    if ($k < 10) {
                        $data = [];
                        if (!preg_match('#(' . $in_keywords . ')#', $v['title'])) {
                            continue;
                        } else {
                            if (preg_match('#(' . $out_keywords . ')#', $v['title'])) {
                                continue;
                            } else {
                                $data['bidSiteId'] = 642;
                                $data['bidSiteName'] = '广州顺为招标采购有限公司';
                                $data['bidTitle'] = $v['title'];
                                $data['bidUrl'] = 'http://www.gzswbc.com' . $v['url'];
                                $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                                $data['inDate'] = date('Y-m-d H:i:s');
                                $final_arr[] = $data;
                            }
                        }
                    } else {
                        break;
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 642 -> 广州顺为招标采购有限公司 -> 电子化采购平台');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 642 -> 广州顺为招标采购有限公司 -> 电子化采购平台');
        }
        // 644 广东启正招标代理有限公司
        $url = 'http://www.gdqizheng.com/News.aspx?SortID=65&CategoryID=34&ParentID=64&LanguageID=1';
        $res = global_curl($url);
        if ($res !== false) {
            $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
            $preg = '#<liclass="anim_emtb_to_t_scale"><a.*href="(.*)".*<h5class="title"><img.*>(.*)</h5>.*发布时间:(.*)</div></div></a></li>#isU';
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
                            $data['bidSiteId'] = 644;
                            $data['bidSiteName'] = '广东启正招标代理有限公司';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = 'http://www.gdqizheng.com' . $v['url'];
                            $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 644 -> 广东启正招标代理有限公司');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 644 -> 广东启正招标代理有限公司');
        }
        // 652 青岛鑫正源工程项目管理有限公司
        $url = 'http://www.qdxhaxzy.com/zhaobiaogonggao.html';
        $res = global_curl($url);
        if ($res !== false) {
            $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
            $res_temp = [];
            preg_match('#<ulclass="news-list">(.*)</ul>#isU', $res, $res_temp);
            $preg = '#<li><ahref="(.*)".*<strong>(.*)</strong><span>日期：(.*)</span>.*</li>#isU';
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
                            $data['bidSiteId'] = 652;
                            $data['bidSiteName'] = '青岛鑫正源工程项目管理有限公司';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = strpos($v['url'], 'http') === 0 ? $v['url'] : ('http://www.qdxhaxzy.com/' . $v['url']);
                            $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 652 -> 青岛鑫正源工程项目管理有限公司');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 652 -> 青岛鑫正源工程项目管理有限公司');
        }
        // 658 厦门市华沧采购招标有限公司
        $url = 'https://www.xm-hc.com/';
        $res = global_curl($url);
        if ($res !== false) {
            $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
            $res_temp = [];
            preg_match('#<tdwidth="19%"align="center"class="txtbanner">采购公告</td>.*</table>.*</table></td></tr><tr><tdalign="center">(.*)<ahref="/list/4"id="ctl00_ContentPlaceHolder1_zytz_aMore"class="txtbanner">重要通知</a>#isU', $res, $res_temp);
            $preg = '#<table.*<a.*href=\'(.*)\'>(.*)</a></td><td.*>(.*)</td>.*</table>#isU';
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
                            $data['bidSiteId'] = 658;
                            $data['bidSiteName'] = '厦门市华沧采购招标有限公司';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = 'https://www.xm-hc.com' . $v['url'];
                            $data['beginDate'] = $v['beginDate'];
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 658 -> 厦门市华沧采购招标有限公司');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 658 -> 厦门市华沧采购招标有限公司');
        }
        // 667 广州宜立工程管理有限公司
        $url = 'https://www.gzylzbdl.com/announce/list.json?page=1&pageSize=13&keyword=&systemType=1%2C3&announceType=1%2C6';
        $res = global_curl($url);
        if ($res !== false) {
            $obj = json_decode($res, true);
            $arr = $obj['result'] ?? [];
            if (is_array($arr) && count($arr) > 0) {
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['announceName'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['announceName'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 667;
                            $data['bidSiteName'] = '广州宜立工程管理有限公司';
                            $data['bidTitle'] = $v['announceName'];
                            $data['bidUrl'] = 'https://www.gzylzbdl.com/announce/info?announceId='.$v['id'];
                            $data['beginDate'] = $v['publishDateYMD'];
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 667 -> 广州宜立工程管理有限公司');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 667 -> 广州宜立工程管理有限公司');
        }
        // 679 广东明盈招标代理有限公司
        $url = 'http://www.myzbdl.com/articlist.asp?menuid=2&ii=2';
        $res = global_curl($url);
        if ($res !== false) {
            $res = mb_convert_encoding($res, "UTF-8", "GBK"); //转换编码
            $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
            $preg = "#<divclass='articlist'>.*<ahref='(.*)'.*title='(.*)'>.*</a>#isU";
            $obj = [];
            $arr = [];
            preg_match_all($preg, $res, $obj);
            $count = (is_countable($obj) && count($obj) > 0) ? count($obj[0]) : 0;
            if ($count > 0) {
                for ($j = 0; $j < $count; $j++) {
                    $arr[$j]['url'] = $obj[1][$j];
                    $arr[$j]['title'] = $obj[2][$j];
                    $arr[$j]['beginDate'] = date('Y-m-d H:i:s');
                }
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['title'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['title'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 679;
                            $data['bidSiteName'] = '广东明盈招标代理有限公司';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = 'http://www.myzbdl.com/' . $v['url'];
                            $data['beginDate'] = $v['beginDate'];
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 679 -> 广东明盈招标代理有限公司');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 679 -> 广东明盈招标代理有限公司');
        }
        // 688 福建君信招标有限公司
        $url = 'http://www.fjjxzb.cn/notice/getNoticeList.do';
        $res = global_curl($url, [
            'post' => 1,
            'postfields' => 'searchStr=&noticeType=0&pageSize=10&pageNum=1',
            'httpheader' => [
                'Accept: */*',
                'Accept-Encoding: gzip, deflate',
                'Accept-Language: zh-CN,zh;q=0.9,en;q=0.8,en-GB;q=0.7,en-US;q=0.6',
                'Cache-Control: no-cache',
                'Connection: keep-alive',
                'Content-Length: 45',
                'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
                'Host: www.fjjxzb.cn',
                'Origin: http://www.fjjxzb.cn',
                'Pragma: no-cache',
                'Referer: http://www.fjjxzb.cn/newIndex/toWsjj.do',
                'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36 Edg/131.0.0.0'
            ]
        ]);
        if ($res !== false) {
            $obj = json_decode($res, true);
            $arr = $obj['data'] ?? [];
            if (is_array($arr) && count($arr) > 0) {
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['noticeTitle'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['noticeTitle'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 688;
                            $data['bidSiteName'] = '福建君信招标有限公司';
                            $data['bidTitle'] = $v['noticeTitle'];
                            $data['bidUrl'] = 'http://www.fjjxzb.cn/newIndex/toWsjjContent.do?contentId='.$v['noticeId'].'&type='.$v['noticeType'];
                            $data['beginDate'] = $v['createTime'];
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 688 -> 福建君信招标有限公司');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 688 -> 福建君信招标有限公司');
        }
        // 691 贵州拓迈电子招投标服务有限公司
        $url = 'https://www.gztmzb.com/index.php/list/8';
        $res = global_curl($url);
        if ($res !== false) {
            $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
            $preg = '#<divclass="text"><a.*href="(.*)".*>(.*)<span>(.*)</span></a></div>#isU';
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
                            $data['bidSiteId'] = 691;
                            $data['bidSiteName'] = '贵州拓迈电子招投标服务有限公司';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = 'https://www.gztmzb.com/'.$v['url'];
                            $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 691 -> 贵州拓迈电子招投标服务有限公司');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 691 -> 贵州拓迈电子招投标服务有限公司');
        }
        // 700 江苏海外集团国际工程咨询有限公司
        $urls = [
            '招标公示' => 'https://www.joccon.cn/hwzb/cmszbgg/index.html',
            '其他公示' => 'https://www.joccon.cn/hwzb/cmsbggg/index.html'
        ];
        foreach ($urls as $index => $url) {
            $res = global_curl($url);
            if ($res !== false) {
                $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
                $preg = '#<liclass="PaddingLR15"><ahref="(.*)"title="(.*)">.*<spanclass="RightGray">(.*)</span>.*</li>#isU';
                $obj = [];
                $arr = [];
                preg_match_all($preg, $res, $obj);
                $count = (is_countable($obj) && count($obj) > 0) ? count($obj[0]) : 0;
                if ($count > 0) {
                    for ($j = 0; $j < $count; $j++) {
                        $arr[$j]['url'] = $obj[1][$j];
                        $arr[$j]['title'] = $obj[2][$j];
                        $arr[$j]['beginDate'] = str_replace('/', '-', $obj[3][$j]);
                    }
                    foreach ($arr as $k => $v) {
                        $data = [];
                        if (!preg_match('#(' . $in_keywords . ')#', $v['title'])) {
                            continue;
                        } else {
                            if (preg_match('#(' . $out_keywords . ')#', $v['title'])) {
                                continue;
                            } else {
                                $data['bidSiteId'] = 700;
                                $data['bidSiteName'] = '江苏海外集团国际工程咨询有限公司';
                                $data['bidTitle'] = $v['title'];
                                $data['bidUrl'] = 'https://www.joccon.cn'.$v['url'];
                                $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                                $data['inDate'] = date('Y-m-d H:i:s');
                                $final_arr[] = $data;
                            }
                        }
                    }
                } else {
                    log_message('error', '招标采集：未匹配到数据 700 -> 江苏海外集团国际工程咨询有限公司 -> ' . $index);
                }
            } else {
                log_message('error', '招标采集：未获取到网页 700 -> 江苏海外集团国际工程咨询有限公司 -> ' . $index);
            }
        }
        // 715 建通投资集团
        $url = 'http://www.gxjt.net/web/index.php?r=site/tenderinglist&cid=35';
        $res = global_curl($url);
        if ($res !== false) {
            $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
            $res_temp = [];
            preg_match('#招标公告</div><ul>(.*)</ul>#isU', $res, $res_temp);
            $preg = '#<li><a.*href="(.*)"title="(.*)".*\[(.*)\]</div></div></a></li>#isU';
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
                            $data['bidSiteId'] = 715;
                            $data['bidSiteName'] = '建通投资集团';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = 'http://www.gxjt.net'.$v['url'];
                            $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 715 -> 建通投资集团');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 715 -> 建通投资集团');
        }
        // 716 福建天平招标代理有限公司
        $urls = [
            '网上竞价招标公告' => 'http://www.fjtpzb.com/?m=home&c=Lists&a=index&tid=54',
            '其他采购招标公告' => 'http://www.fjtpzb.com/?m=home&c=Lists&a=index&tid=50',
            '集中采购目录外招标公告' => 'http://www.fjtpzb.com/?m=home&c=Lists&a=index&tid=45',
            '集中采购目录内招标公告' => 'http://www.fjtpzb.com/?m=home&c=Lists&a=index&tid=35'
        ];
        foreach ($urls as $index => $url) {
            $res = global_curl($url);
            if ($res !== false) {
                $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
                $preg = '#<li><spanclass="float_right">(.*)</span><ahref="(.*)"title="(.*)".*</li>#isU';
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
                                $data['bidSiteId'] = 716;
                                $data['bidSiteName'] = '福建天平招标代理有限公司';
                                $data['bidTitle'] = $v['title'];
                                $data['bidUrl'] = 'http://www.fjtpzb.com'.$v['url'];
                                $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                                $data['inDate'] = date('Y-m-d H:i:s');
                                $final_arr[] = $data;
                            }
                        }
                    }
                } else {
                    log_message('error', '招标采集：未匹配到数据 716 -> 福建天平招标代理有限公司 -> ' . $index);
                }
            } else {
                log_message('error', '招标采集：未获取到网页 716 -> 福建天平招标代理有限公司 -> ' . $index);
            }
        }
        // 717 广东国咨招标有限公司
        $url = 'https://guozitender.com/article/list_c0001';
        $res = global_curl($url);
        if ($res !== false) {
            $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
            $preg = '#<li><iclass="fl">•</i><h3><ahref="(.*)".*title="(.*)".*</a></h3><spanclass="fr">(.*)</span></li>#isU';
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
                            $data['bidSiteId'] = 717;
                            $data['bidSiteName'] = '广东国咨招标有限公司';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = 'https://guozitender.com'.$v['url'];
                            $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 717 -> 广东国咨招标有限公司');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 717 -> 广东国咨招标有限公司');
        }
        // 726 福建榕卫招标有限公司
        $urls = [
            '政府采购-招标公告' => 'http://www.fjrwzb.com/api/cmsplatform_SRW9QJJX/Biz_Q0AWQ8VG?columnId=184&page=1&row=15&keyWord=%E8%BE%93%E5%85%A5%E6%90%9C%E7%B4%A2%E5%85%B3%E9%94%AE%E8%AF%8D',
            '电子采购-网上竞价公告' => 'http://www.fjrwzb.com/api/cmsplatform_SRW9QJJX/Biz_Q0AWQ8VG?columnId=201&page=1&row=15&keyWord=%E8%BE%93%E5%85%A5%E6%90%9C%E7%B4%A2%E5%85%B3%E9%94%AE%E8%AF%8D'
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
                                $data['bidSiteId'] = 726;
                                $data['bidSiteName'] = '福建榕卫招标有限公司';
                                $data['bidTitle'] = $v['title'];
                                $data['bidUrl'] = 'http://www.fjrwzb.com/h/cmsplatform_DP7SUQGS?contentId=' . $v['contentId'] . '.html';
                                $data['beginDate'] = $v['publishTime'];
                                $data['inDate'] = date('Y-m-d H:i:s');
                                $final_arr[] = $data;
                            }
                        }
                    }
                } else {
                    log_message('error', '招标采集：未匹配到数据 726 -> 福建榕卫招标有限公司 -> ' . $index);
                }
            } else {
                log_message('error', '招标采集：未获取到网页 726 -> 福建榕卫招标有限公司 -> ' . $index);
            }
        }
        // 733 广东粤诚招标管理咨询有限公司
        $url = 'http://www.gdyczb.com/zbxxgg';
        $res = global_curl($url);
        if ($res !== false) {
            $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
            $preg = '#<divclass="article"><divclass="info">.*<a.*href="(.*)".*title="(.*)".*</a>.*<spanclass="datetime">(.*)</span></div>#isU';
            $obj = [];
            $arr = [];
            preg_match_all($preg, $res, $obj);
            $count = (is_countable($obj) && count($obj) > 0) ? count($obj[0]) : 0;
            if ($count > 0) {
                for ($j = 0; $j < $count; $j++) {
                    $arr[$j]['url'] = $obj[1][$j];
                    $arr[$j]['title'] = $obj[2][$j];
                    $arr[$j]['beginDate'] = str_replace('/', '-', $obj[3][$j]);
                }
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['title'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['title'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 733;
                            $data['bidSiteName'] = '广东粤诚招标管理咨询有限公司';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = 'http://www.gdyczb.com'.$v['url'];
                            $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 733 -> 广东粤诚招标管理咨询有限公司');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 733 -> 广东粤诚招标管理咨询有限公司');
        }
        // 749 佛山市浩采招标采购有限公司
        $url = 'http://www.fshaocai.com/nr.jsp';
        $res = global_curl($url);
        if ($res !== false) {
            $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
            $preg = '#<divtopClassName=\'top1\'.*<aclass=\'no-mixNewsStyleTitleTip\'href=\'(.*)\'.*<spanclass=\'mixNewsStyleDate\'>(.*)</span>.*title="(.*)".*</a></div>#isU';
            $obj = [];
            $arr = [];
            preg_match_all($preg, $res, $obj);
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
                            $data['bidSiteId'] = 749;
                            $data['bidSiteName'] = '佛山市浩采招标采购有限公司';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = strpos($v['url'], 'http') === 0 ? $v['url'] : ('http://www.fshaocai.com' . $v['url']);
                            $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 749 -> 佛山市浩采招标采购有限公司');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 749 -> 佛山市浩采招标采购有限公司');
        }
        // 750 广东丰联招标代理有限公司
        $url = 'http://www.gdflzb.com/index.php?m=content&c=index&a=lists&catid=5';
        $res = global_curl($url);
        if ($res !== false) {
            $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
            $res = mb_convert_encoding($res, "UTF-8", "GBK"); //转换编码
            $preg = '#<li><span>(.*)</span><ahref="(.*)">(.*)</a></a></li>#isU';
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
                            $data['bidSiteId'] = 750;
                            $data['bidSiteName'] = '广东丰联招标代理有限公司';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = $v['url'];
                            $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 750 -> 广东丰联招标代理有限公司');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 750 -> 广东丰联招标代理有限公司');
        }
        // 769 云南中诏项目管理有限公司
        $url = 'http://www.ynzzgl.com/col.jsp?id=106';
        $res = global_curl($url, [
            'httpheader' => [
                'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7',
                'Accept-Encoding: gzip, deflate',
                'Accept-Language: zh-CN,zh;q=0.9,en;q=0.8,en-GB;q=0.7,en-US;q=0.6',
                'Cache-Control: no-cache',
                'Connection: keep-alive',
                'Host: www.ynzzgl.com',
                'Pragma: no-cache',
                'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36 Edg/135.0.0.0'
            ]
        ]);
        if ($res !== false) {
            $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
            $preg = "#<tabletopClassName='productListTopIcon.*<a.*href='(.*)'.*title='(.*)'.*<\/div>#isU";
            $obj = [];
            $arr = [];
            preg_match_all($preg, $res, $obj);
            $count = (is_countable($obj) && count($obj) > 0) ? count($obj[0]) : 0;
            if ($count > 0) {
                for ($j = 0; $j < $count; $j++) {
                    $arr[$j]['url'] = $obj[1][$j];
                    $arr[$j]['title'] = $obj[2][$j];
                    $arr[$j]['beginDate'] = date('Y-m-d H:i:s');
                }
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['title'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['title'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 769;
                            $data['bidSiteName'] = '云南中诏项目管理有限公司';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = $v['url'];
                            $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 769 -> 云南中诏项目管理有限公司');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 769 -> 云南中诏项目管理有限公司');
        }
        // 785 华夏城投项目管理有限公司
        $url = 'https://www.sxhx2001.com/zbxx/zbgg/';
        $res = global_curl($url);
        if ($res !== false) {
            $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
            $preg = '#<dlclass="newsList".*<a.*href="(.*)".*title="(.*)".*</a><span>\[(.*)\]</span></dt>.*</dl>#isU';
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
                            $data['bidSiteId'] = 785;
                            $data['bidSiteName'] = '华夏城投项目管理有限公司';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = $v['url'];
                            $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 785 -> 华夏城投项目管理有限公司');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 785 -> 华夏城投项目管理有限公司');
        }
        // 786 浙江中创招投标有限公司
        $url = 'https://www.zjzcbidding.com/api/zbProjectNotice/projectNoticePage?type=tenders&current=1&size=10&projectSource=%E5%85%AC%E5%8F%B8%E9%A1%B9%E7%9B%AE';
        $res = global_curl($url);
        if($res !== false) {
            $obj = json_decode($res, true);
            $arr = $obj['data']['records'] ?? [];
            if (is_array($arr) && count($arr) > 0) {
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['name'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['name'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 786;
                            $data['bidSiteName'] = '浙江中创招投标有限公司';
                            $data['bidTitle'] = $v['name'];
                            $data['bidUrl'] = 'https://www.zjzcbidding.com/#/announcementDetails/'.$v['projectId'].'?type=%E6%8B%9B%E6%A0%87%E5%85%AC%E5%91%8A&projectNo='.$v['projectNo'];
                            $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['createTime']));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 786 -> 浙江中创招投标有限公司');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 786 -> 浙江中创招投标有限公司');
        }
        // 788 正采（深圳）招标代理有限公司
        $url = 'http://www.zcszzb.cn/news/event-information';
        $res = global_curl($url);
        if($res !== false) {
            $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
            $preg = '#<dl><dt><ahref="(.*)">(.*)</a>.*【(.*)】</dt>.*</dl>#isU';
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
                            $data['bidSiteId'] = 788;
                            $data['bidSiteName'] = '正采（深圳）招标代理有限公司';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = $v['url'];
                            $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 788 -> 正采（深圳）招标代理有限公司');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 788 -> 正采（深圳）招标代理有限公司');
        }
        // 807 江西银隆管理咨询有限公司 【特殊通过浏览器刷新插件+自定义js获取数据后发送到本系统api并存数据库，每次采集从数据库拿】
        $res = $this->db->table('bid_temp')->select('res')->where('site', '江西银隆管理咨询有限公司')->get()->getRowArray()['res'] ?? '';
        if ($res) {
            $preg = '#<li.*<span.*>(.*)</span>.*<a.*href="(.*)".*>(.*)</a></div></li>#isU';
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
                            $data['bidSiteId'] = 807;
                            $data['bidSiteName'] = '江西银隆管理咨询有限公司';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = 'https://www.jxylzb.com'.$v['url'];
                            $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 807 -> 江西银隆管理咨询有限公司');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 807 -> 江西银隆管理咨询有限公司');
        }
        // 818 丰信采购招标
        $url = 'https://www.xmfxcgzb.com/hyweb/hyzTradeInfo/getBulPage.do';
        $res = global_curl($url, [
            'post' => 1,
            'postfields' => '{pageIndex:"1",pageSize:"20",projectClass:"D01",noticeTitle:"",regionCode:"",tenderType:"ZY",transType :"",pubTime :"",state :"",noticeType :"1",projentity :"",tDeptid : "",tradeCode:"XMFXPT"}'
        ]);
        if($res !== false) {
            $obj = json_decode($res, true);
            $arr = $obj['data']['datalist'] ?? [];
            if (is_array($arr) && count($arr) > 0) {
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['noticeTitle'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['noticeTitle'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 818;
                            $data['bidSiteName'] = '丰信采购招标';
                            $data['bidTitle'] = $v['noticeTitle'];
                            $data['bidUrl'] = 'https://www.xmfxcgzb.com/hyweb/hyebid/governBid.do?GUID='.$v['tenderProjId'];
                            $data['beginDate'] = $v['sendTimeWx'];
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 818 -> 丰信采购招标');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 818 -> 丰信采购招标');
        }
        // 821 广西科文招标有限公司
        $url = 'http://www.kwbid.com.cn/newslist.jsp?id=4&pageno=1&pagenum=0';
        $res = global_curl($url);
        if($res !== false) {
            $res = mb_convert_encoding($res, "UTF-8", "GBK"); //转换编码
            $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
            $res_temp = [];
            preg_match('#<divclass="ny_wzlb"><ul>(.*)</ul>#isU', $res, $res_temp);
            $preg = '#<li><a.*href="(.*)".*>(.*)</a>(.*)</li>#isU';
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
                            $data['bidSiteId'] = 821;
                            $data['bidSiteName'] = '广西科文招标有限公司';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = strpos($v['url'], 'http') === 0 ? $v['url'] : ('http://www.kwbid.com.cn/' . $v['url']);
                            $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 821 -> 广西科文招标有限公司');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 821 -> 广西科文招标有限公司');
        }
        // 841 深圳市开正信息工程咨询有限公司
        $url = 'http://www.szkzzb.com/index.php?m=content&c=index&a=lists&catid=14';
        $res = global_curl($url);
        if($res !== false) {
            $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
            $res_temp = [];
            preg_match('#<ulclass="NewsList"id="NewsList">(.*)</ul>#isU', $res, $res_temp);
            $preg = '#<li.*<a.*href="(.*)".*title="(.*)".*</a></h3><pclass="t3">时间：(.*)</p>.*</li>#isU';
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
                            $data['bidSiteId'] = 841;
                            $data['bidSiteName'] = '深圳市开正信息工程咨询有限公司';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = 'http://www.szkzzb.com' . $v['url'];
                            $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 841 -> 深圳市开正信息工程咨询有限公司');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 841 -> 深圳市开正信息工程咨询有限公司');
        }
        // 849 安徽皖岳信合项目管理有限公司
        $url = 'https://web-api.ahwyxh.com/front/info/projectInfo/selectWyBidderProjectInfoByParam?infoTypeId=7&startTime=&endTime=&pageNum=1&pageSize=20&keyWord=&areaId=&biddingMethod=&projectSource=';
        $res = global_curl($url);
        if($res !== false) {
            $obj = json_decode($res, true);
            $arr = $obj['rows'] ?? [];
            if (is_array($arr) && count($arr) > 0) {
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['infoTitle'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['infoTitle'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 849;
                            $data['bidSiteName'] = '安徽皖岳信合项目管理有限公司';
                            $data['bidTitle'] = $v['infoTitle'];
                            $data['bidUrl'] = 'http://ahwyxh.com/xinhe/details/' . $v['infoId'] . '.html';
                            $data['beginDate'] = $v['createTime'];
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 849 -> 安徽皖岳信合项目管理有限公司');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 849 -> 安徽皖岳信合项目管理有限公司');
        }
        // 853 河南招标采购服务有限公司
        $url = 'http://122.114.92.171:8081/api/getContentPage/28/1/10';
        $res = global_curl($url, [
            'post' => 1,
            'httpheader' => [
                'Accept: application/json, text/javascript, */*; q=0.01',
                'Accept-Encoding: gzip, deflate',
                'Accept-Language: zh-CN,zh;q=0.9,en;q=0.8,en-GB;q=0.7,en-US;q=0.6',
                'Connection: keep-alive',
                'Content-Length: 0',
                'Host: 122.114.92.171:8081',
                'Origin: http://www.hnzbcg.com.cn',
                'Referer: http://www.hnzbcg.com.cn/',
                'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0'
            ]
        ]);
        if($res !== false) {
            $obj = json_decode($res, true);
            $arr = $obj['data']['data'] ?? [];
            if (is_array($arr) && count($arr) > 0) {
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['name'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['name'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 853;
                            $data['bidSiteName'] = '河南招标采购服务有限公司';
                            $data['bidTitle'] = $v['name'];
                            $data['bidUrl'] = 'http://www.hnzbcg.com.cn/html/'.$v['detailUrl'];
                            $data['beginDate'] = $v['releaseTime'];
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 853 -> 河南招标采购服务有限公司');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 853 -> 河南招标采购服务有限公司');
        }
        // 854 常州青果招投标有限公司
        $url = 'http://www.czqgztb.com/gonggao';
        $res = global_curl($url);
        if($res !== false) {
            $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
            $preg = '#<divclass="gonggao-con-list.*<a.*href="(.*)".*>(.*)</a>.*<divclass="g-timeright">(.*)</div></div>#isU';
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
                            $data['bidSiteId'] = 854;
                            $data['bidSiteName'] = '常州青果招投标有限公司';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = strpos($v['url'], 'http') === 0 ? $v['url'] : ('http://www.czqgztb.com'.$v['url']);
                            $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 854 -> 常州青果招投标有限公司');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 854 -> 常州青果招投标有限公司');
        }
        // 855 远东招标采购交易网
        $url = 'http://www.szyd11.com/';
        $res = global_curl($url);
        if($res !== false) {
            $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
            $res_temp = [];
            preg_match('#<span>项目编号</span><span>项目名称</span><span>开标时间</span></div>(.*)</div>#isU', $res, $res_temp);
            $preg = '#<ul.*data-send-date="(.*)".*<a.*href="(.*)".*>(.*)</a>.*</ul>#isU';
            $obj = [];
            $arr = [];
            is_countable($res_temp) && count($res_temp) > 0 && preg_match_all($preg, $res_temp[1], $obj);
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
                            $data['bidSiteId'] = 855;
                            $data['bidSiteName'] = '远东招标采购交易网';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = strpos($v['url'], 'http') === 0 ? $v['url'] : ('http://www.szyd11.com'.$v['url']);
                            $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 855 -> 远东招标采购交易网');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 855 -> 远东招标采购交易网');
        }
        // 871 中恰招标代理集团有限公司
        $url = 'http://www.zqia100.com/a/gsxw/';
        $res = global_curl($url);
        if($res !== false) {
            $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
            $preg = '#<listyle.*<a.*href="(.*)".*title="(.*)".*<spanstyle="float:right;">(.*)</span><spanclass="clear"></span></li>#isU';
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
                            $data['bidSiteId'] = 871;
                            $data['bidSiteName'] = '中恰招标代理集团有限公司';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = 'http://www.zqia100.com' . $v['url'];
                            $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 871 -> 中恰招标代理集团有限公司');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 871 -> 中恰招标代理集团有限公司');
        }
        // 886 广东智汇城市招标采购有限公司
        $url = 'http://www.gdzhcity.com/q_new1/';
        $res = global_curl($url, [
            'header' => 1
        ]);
        if($res !== false) {
            $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
            if (strpos($res, 'Location') !== false) {
                $res_temp = [];
                preg_match('#Location:(.*)Content-Length#isU', $res, $res_temp);
                $url = 'http://www.gdzhcity.com' . $res_temp[1];
                $res = global_curl($url);
                $url = 'http://www.gdzhcity.com/q_new1/';
                $res = global_curl($url);
                $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
            }
            $res_temp = [];
            preg_match('#<divclass="newslist_list"><ul>(.*)</ul>#isU', $res, $res_temp);
            $preg = '#<li><ahref="(.*)">(.*)</a><span>(.*)</span></li>#isU';
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
                            $data['bidSiteId'] = 886;
                            $data['bidSiteName'] = '广东智汇城市招标采购有限公司';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = strpos($v['url'], 'http') === 0 ? $v['url'] : ('http://www.gdzhcity.com/q_new1/'.$v['url']);
                            $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 886 -> 广东智汇城市招标采购有限公司');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 886 -> 广东智汇城市招标采购有限公司');
        }
        // 889 广西城建咨询设计有限公司
        $url = 'http://www.gxchengjian.com/website/site_page/CoNewTable.action?type=ZBGG&page=1&rows=10';
        $res = global_curl($url, [
            'post' => 1
        ]);
        if($res !== false) {
            $obj = json_decode($res, true);
            $arr = $obj['rows'] ?? [];
            if (is_array($arr) && count($arr) > 0) {
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['TITLE'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['TITLE'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 889;
                            $data['bidSiteName'] = '广西城建咨询设计有限公司';
                            $data['bidTitle'] = $v['TITLE'];
                            // url参数content加密开始
                            $content = '';
                            $keyStr = "KLMNOPQRSTABCDEFGHIJUVWXYZabcdopqrstuvwxefghijklmnyz0123456789+/";
                            $v['NEW_ID'] = str_replace("\r\n", "\n", $v['NEW_ID']);
                            $utftext = "";
                            $len = strlen($v['NEW_ID']);
                            for ($n = 0; $n < $len; $n++) {
                                $c = ord($v['NEW_ID'][$n]);
                                if ($c < 128) {
                                    $utftext .= chr($c);
                                } else if(($c > 127) && ($c < 2048)) {
                                    $utftext .= chr(($c >> 6) | 192);
                                    $utftext .= chr(($c & 63) | 128);
                                } else {
                                    $utftext .= chr(($c >> 12) | 224);
                                    $utftext .= chr((($c >> 6) & 63) | 128);
                                    $utftext .= chr(($c & 63) | 128);
                                }
                            }
                            $i = 0;
                            $len = strlen($utftext);
                            while ($i < $len) {
                                $chr1 = isset($utftext[$i]) ? ord($utftext[$i++]) : 0;
                                $chr2 = isset($utftext[$i]) ? ord($utftext[$i++]) : 0;
                                $chr3 = isset($utftext[$i]) ? ord($utftext[$i++]) : 0;
                                $enc1 = $chr1 >> 2;
                                $enc2 = (($chr1 & 3) << 4) | ($chr2 >> 4);
                                $enc3 = (($chr2 & 15) << 2) | ($chr3 >> 6);
                                $enc4 = $chr3 & 63;
                                if ($chr2 === 0) {
                                    $enc3 = $enc4 = 64;
                                } else if ($chr3 === 0) {
                                    $enc4 = 64;
                                }
                                $content .= $keyStr[$enc1] . $keyStr[$enc2] . $keyStr[$enc3] . ($keyStr[$enc4] ?? '');
                            }
                            // url参数加密结束
                            $data['bidUrl'] = 'http://www.gxchengjian.com/website/web/site/detail.do?content='.$content;
                            $data['beginDate'] = str_replace('/', '-', $v['CREATED_DATE']);
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 889 -> 广西城建咨询设计有限公司');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 889 -> 广西城建咨询设计有限公司');
        }
        // 893 中元建设科技有限责任公司
        $url = 'http://www.hbzyzx.com/invite/6';
        $res = global_curl($url);
        if($res !== false) {
            $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
            $res_temp = [];
            preg_match('#<divclass="tab2"><ul>(.*)</ul>#isU', $res, $res_temp);
            $preg = '#<li><ahref="(.*)".*>(.*)</a><span>(.*)</span></li>#isU';
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
                            $data['bidSiteId'] = 893;
                            $data['bidSiteName'] = '中元建设科技有限责任公司';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = strpos($v['url'], 'http') === 0 ? $v['url'] : ('http://www.hbzyzx.com'.$v['url']);
                            $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 893 -> 中元建设科技有限责任公司');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 893 -> 中元建设科技有限责任公司');
        }
        // 898 常州常投招标有限公司
        $url = 'http://www.jsczctzb.com/NewsList.asp?SortID=15';
        $res = global_curl($url, [
            'httpheader' => [
                'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7',
                'Accept-Encoding: gzip, deflate',
                'Accept-Language: zh-CN,zh;q=0.9,en;q=0.8,en-GB;q=0.7,en-US;q=0.6',
                'Cache-Control: no-cache',
                'Connection: keep-alive',
                'Host: www.jsczctzb.com',
                'Pragma: no-cache',
                'Referer: http://www.jsczctzb.com/NewsList.asp?SortID=15',
                'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0'
            ]
        ]);
        if($res !== false) {
            $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
            $preg = "#<listyle='width:100\%;height:40px;line-height:40px;background-color:\#fff;border:0;margin-bottom:0;padding:0;border-bottom:1px\#eeesolid;'>.*<ahref='(.*)'title='(.*)'.*</a></dt><dt.*</dt><dt.*>(.*)</dt></li>#isU";
            $obj = [];
            $arr = [];
            preg_match_all($preg, $res, $obj);
            $count = (is_countable($obj) && count($obj) > 0) ? count($obj[0]) : 0;
            if ($count > 0) {
                for ($j = 0; $j < $count; $j++) {
                    $arr[$j]['url'] = $obj[1][$j];
                    $arr[$j]['title'] = $obj[2][$j];
                    $arr[$j]['beginDate'] = str_replace('/', '-', $obj[3][$j]);
                }
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['title'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['title'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 898;
                            $data['bidSiteName'] = '常州常投招标有限公司';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = strpos($v['url'], 'http') === 0 ? $v['url'] : ('http://www.jsczctzb.com/' . $v['url']);
                            $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 898 -> 常州常投招标有限公司');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 898 -> 常州常投招标有限公司');
        }
        // 906 江西国政招标咨询有限公司 【特殊通过浏览器刷新插件+自定义js获取数据后发送到本系统api并存数据库，每次采集从数据库拿】
        $res = $this->db->table('bid_temp')->select('res')->where('site', '江西国政招标咨询有限公司')->get()->getRowArray()['res'] ?? '';
        if($res) {
            $res_temp = [];
            $preg = '#<li.*<a.*href="(.*)".*title="(.*)".*</a><span>(.*)</span></li>#isU';
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
                            $data['bidSiteId'] = 906;
                            $data['bidSiteName'] = '江西国政招标咨询有限公司';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = strpos($v['url'], 'http') === 0 ? $v['url'] : ('http://jxgzzb.com.cn'.$v['url']);
                            $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 906 -> 江西国政招标咨询有限公司');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 906 -> 江西国政招标咨询有限公司');
        }
        // 913 福建杰俊招标代理有限公司
        $urls = [
            '网上竞价' => 'http://www.fjjjzb.com/bidding/',
            '招标公告' => 'http://www.fjjjzb.com/notice/zhaobiao/'
        ];
        foreach ($urls as $index => $url) {
            $res = global_curl($url);
            if($res !== false) {
                $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
                $preg = '#<liclass="mb10"><ahref="(.*)"><spanclass="fr">(.*)</span><i.*</i>(.*)</a></li>#isU';
                $obj = [];
                $arr = [];
                preg_match_all($preg, $res, $obj);
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
                                $data['bidSiteId'] = 913;
                                $data['bidSiteName'] = '福建杰俊招标代理有限公司';
                                $data['bidTitle'] = $v['title'];
                                $data['bidUrl'] = strpos($v['url'], 'http') === 0 ? $v['url'] : ('http://www.fjjjzb.com'.$v['url']);
                                $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                                $data['inDate'] = date('Y-m-d H:i:s');
                                $final_arr[] = $data;
                            }
                        }
                    }
                } else {
                    log_message('error', '招标采集：未匹配到数据 913 -> 福建杰俊招标代理有限公司 -> ' . $index);
                }
            } else {
                log_message('error', '招标采集：未获取到网页 913 -> 福建杰俊招标代理有限公司 -> ' . $index);
            }
        }
        // 920 广西中信恒泰工程顾问有限公司
        $url = 'https://www.gxzxht.com/channel/zhaobiaogongshi.html';
        $res = global_curl($url);
        if($res !== false) {
            $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
            $res_temp = [];
            preg_match('#<ulclass="ruwei">(.*)</ul>#isU', $res, $res_temp);
            $preg = '#<li.*<a.*href="(.*)".*title="(.*)".*<spanclass="time">(.*)<strong>(.*)</strong></span></a></li>#isU';
            $obj = [];
            $arr = [];
            is_countable($res_temp) && count($res_temp) > 0 && preg_match_all($preg, $res_temp[1], $obj);
            $count = (is_countable($obj) && count($obj) > 0) ? count($obj[0]) : 0;
            if ($count > 0) {
                for ($j = 0; $j < $count; $j++) {
                    $arr[$j]['url'] = $obj[1][$j];
                    $arr[$j]['title'] = $obj[2][$j];
                    $arr[$j]['beginDate'] = $obj[3][$j] . '-' . $obj[4][$j];
                }
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['title'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['title'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 920;
                            $data['bidSiteName'] = '广西中信恒泰工程顾问有限公司';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = strpos($v['url'], 'http') === 0 ? $v['url'] : ('https://www.gxzxht.com'.$v['url']);
                            $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 920 -> 广西中信恒泰工程顾问有限公司');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 920 -> 广西中信恒泰工程顾问有限公司');
        }
        // 950 苏世建设管理集团有限公司
        $url = 'https://www.sushigroup.cn/list-98.html';
        $res = global_curl($url);
        if($res !== false) {
            $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
            $preg = '#<li><span>(.*)</span><ahref="(.*)".*title="(.*)".*</li>#isU';
            $obj = [];
            $arr = [];
            preg_match_all($preg, $res, $obj);
            $count = (is_countable($obj) && count($obj) > 0) ? count($obj[0]) : 0;
            if ($count > 0) {
                for ($j = 0; $j < $count; $j++) {
                    $arr[$j]['url'] = $obj[2][$j];
                    $arr[$j]['title'] = $obj[3][$j];
                    $arr[$j]['beginDate'] = str_replace('.', '-', $obj[1][$j]);
                }
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['title'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['title'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 950;
                            $data['bidSiteName'] = '苏世建设管理集团有限公司';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = strpos($v['url'], 'http') === 0 ? $v['url'] : ('https://www.sushigroup.cn'.$v['url']);
                            $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 950 -> 苏世建设管理集团有限公司');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 950 -> 苏世建设管理集团有限公司');
        }
        // 956 广东远东招标代理有限公司
        $url = 'http://www.gdydzb.com/Bulletins?type=1';
        $res = global_curl($url);
        if($res !== false) {
            $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
            $preg = '#<divclass="artItem"><ul><li>.*</li><li>.*</li><li><ahref="(.*)".*title="(.*)".*</a></li><li>.*</li><li>(.*)</li></ul></div>#isU';
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
                            $data['bidSiteId'] = 956;
                            $data['bidSiteName'] = '广东远东招标代理有限公司';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = strpos($v['url'], 'http') === 0 ? $v['url'] : ('http://www.gdydzb.com'.$v['url']);
                            $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 956 -> 广东远东招标代理有限公司');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 956 -> 广东远东招标代理有限公司');
        }
        // 962 广东万欣招标代理有限公司
        $url = 'https://www.gdwxzb.com/list_50/';
        $res = global_curl($url);
        if($res !== false) {
            $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
            $preg = '#<trclass="cat-list-row.*<ahref="(.*)">(.*)</a></td><td.*>(.*)</td>.*</tr>#isU';
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
                            $data['bidSiteId'] = 962;
                            $data['bidSiteName'] = '广东万欣招标代理有限公司';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = strpos($v['url'], 'http') === 0 ? $v['url'] : ('https://www.gdwxzb.com'.$v['url']);
                            $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 962 -> 广东万欣招标代理有限公司');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 962 -> 广东万欣招标代理有限公司');
        }
        // 967 广西翰林工程项目管理有限责任公司
        $url = 'http://www.gxhanlin.com/dongtai/';
        $res = global_curl($url);
        if($res !== false) {
            $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
            $preg = '#<liclass="item"><a.*href="(.*)".*title="(.*)".*</li>#isU';
            $obj = [];
            $arr = [];
            preg_match_all($preg, $res, $obj);
            $count = (is_countable($obj) && count($obj) > 0) ? count($obj[0]) : 0;
            if ($count > 0) {
                for ($j = 0; $j < $count; $j++) {
                    $arr[$j]['url'] = $obj[1][$j];
                    $arr[$j]['title'] = $obj[2][$j];
                    $arr[$j]['beginDate'] = date('Y-m-d H:i:s');
                }
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['title'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['title'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 967;
                            $data['bidSiteName'] = '广西翰林工程项目管理有限责任公司';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = strpos($v['url'], 'http') === 0 ? $v['url'] : ('http://www.gxhanlin.com' . $v['url']);
                            $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 967 -> 广西翰林工程项目管理有限责任公司');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 967 -> 广西翰林工程项目管理有限责任公司');
        }
        // 968 广西金证招标代理有限公司
        $url = 'http://www.gxjzzb.com/zbxx/zbgg/';
        $res = global_curl($url, [
            'httpheader' => [
                'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7',
                'Accept-Encoding: gzip, deflate',
                'Accept-Language: zh-CN,zh;q=0.9,en;q=0.8,en-GB;q=0.7,en-US;q=0.6',
                'Cache-Control: no-cache',
                'Connection: keep-alive',
                'Host: www.gxjzzb.com',
                'Pragma: no-cache',
                'Referer: http://www.gxjzzb.com/zbxx/zbgg/',
                'Upgrade-Insecure-Requests: 1',
                'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0'
            ]
        ]);
        if($res !== false) {
            $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
            $preg = '#<li><divclass="page-adate"><h3>(.*)</h3><p>(.*)</p></div><divclass="page-atit"><ahref="(.*)"title="(.*)".*</a></div></li>#isU';
            $obj = [];
            $arr = [];
            preg_match_all($preg, $res, $obj);
            $count = (is_countable($obj) && count($obj) > 0) ? count($obj[0]) : 0;
            if ($count > 0) {
                for ($j = 0; $j < $count; $j++) {
                    $arr[$j]['url'] = $obj[3][$j];
                    $arr[$j]['title'] = $obj[4][$j];
                    $arr[$j]['beginDate'] = $obj[2][$j] . '-' . $obj[1][$j];
                }
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['title'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['title'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 968;
                            $data['bidSiteName'] = '广西金证招标代理有限公司';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = $v['url'];
                            $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 968 -> 广西金证招标代理有限公司');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 968 -> 广西金证招标代理有限公司');
        }
        // 970 云之龙咨询集团有限公司
        $url = 'https://www.yzljt.cn/gonggao/index/mtype/2.html';
        $res = global_curl($url);
        if($res !== false) {
            $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
            $preg = '#<tr><tdclass="table-list__num">.*<a.*href="(.*)".*>(.*)</a></td><td.*</td><td>(.*)</td></tr>#isU';
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
                            $data['bidSiteId'] = 970;
                            $data['bidSiteName'] = '云之龙咨询集团有限公司';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = 'https://www.yzljt.cn' . $v['url'];
                            $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 970 -> 云之龙咨询集团有限公司');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 970 -> 云之龙咨询集团有限公司');
        }
        // 979 江苏常信招标代理有限公司
        $url = 'http://www.jscxzb.com/lists/2.html';
        $res = global_curl($url);
        if($res !== false) {
            $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
            $preg = '#<tr><tdheight="42"align="center">.*<a.*href="(.*)".*title="(.*)".*</a></td><td.*</td><td.*>(.*)</td></tr>#isU';
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
                            $data['bidSiteId'] = 979;
                            $data['bidSiteName'] = '江苏常信招标代理有限公司';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = 'http://www.jscxzb.com' . $v['url'];
                            $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 979 -> 江苏常信招标代理有限公司');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 979 -> 江苏常信招标代理有限公司');
        }
        // 986 河南省天平招标代理有限公司
        $url = 'http://www.hntpzx.com/zbgg/';
        $res = global_curl($url);
        if($res !== false) {
            $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
            $res_temp = [];
            preg_match('#<divclass="zhaocai-a2"><ul>(.*)</ul>#isU', $res, $res_temp);
            $preg = '#<li.*<a.*href="(.*)".*<span.*>(.*)</span><i>(.*)</i></a></li>#isU';
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
                            $data['bidSiteId'] = 986;
                            $data['bidSiteName'] = '河南省天平招标代理有限公司';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = $v['url'];
                            $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 986 -> 河南省天平招标代理有限公司');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 986 -> 河南省天平招标代理有限公司');
        }
        // 999 辰星（深圳）咨询设计有限公司
        $url = 'https://www.cxszzx.com/tenders/list-10-1.html';
        $res = global_curl($url);
        if($res !== false) {
            $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
            $preg = '#<liclass="m_ac"><divclass="date"><b.*>(.*)</b><span>(.*)</span></div>.*<a.*href="(.*)".*<h3.*>(.*)</h3></a>.*</li>#isU';
            $obj = [];
            $arr = [];
            preg_match_all($preg, $res, $obj);
            $count = (is_countable($obj) && count($obj) > 0) ? count($obj[0]) : 0;
            if ($count > 0) {
                for ($j = 0; $j < $count; $j++) {
                    $arr[$j]['url'] = $obj[3][$j];
                    $arr[$j]['title'] = $obj[4][$j];
                    $arr[$j]['beginDate'] = $obj[2][$j] . '-' . $obj[1][$j];
                }
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['title'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['title'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 999;
                            $data['bidSiteName'] = '辰星（深圳）咨询设计有限公司';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = $v['url'];
                            $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 999 -> 辰星（深圳）咨询设计有限公司');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 999 -> 辰星（深圳）咨询设计有限公司');
        }
        // 1004 深圳龙达招标有限公司
        $url = 'https://www.szldzb.com/index.php?m=home&c=Lists&a=index&tid=20';
        $res = global_curl($url);
        if($res !== false) {
            $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
            $preg = '#<tr><tdclass="time"><span.*</span>(.*)</td>.*<a.*href="(.*)".*>(.*)</a>.*</tr>#isU';
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
                            $data['bidSiteId'] = 1004;
                            $data['bidSiteName'] = '深圳龙达招标有限公司';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = strpos($v['url'], 'http') === 0 ? $v['url'] : ('http://www.szldzb.com' . $v['url']);
                            $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 1004 -> 深圳龙达招标有限公司');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 1004 -> 深圳龙达招标有限公司');
        }
        // 1015 厦门市务实采购有限公司
        $url = 'http://www.xmws.com/search/index/notice?modelid=8&project_no=&croporgan_name=&starttime=&endtime=&catid=26&page=1';
        $res = global_curl($url);
        if($res !== false) {
            $obj = json_decode($res, true);
            $arr = $obj['list'] ?? [];
            if (is_array($arr) && count($arr) > 0) {
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['title'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['title'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 1015;
                            $data['bidSiteName'] = '厦门市务实采购有限公司';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = 'http://www.xmws.com'.$v['url'];
                            $data['beginDate'] =date('Y-m-d H:i:s', intval($v['updatetime']));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 1015 -> 厦门市务实采购有限公司');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 1015 -> 厦门市务实采购有限公司');
        }
        // 1025 北京明德致信咨询有限公司
        $url = 'https://website.zbbmcc.com/prod-api/open/notice/getBidFrondNotice?pageNum=1&pageSize=10&columnId=4';
        $res = global_curl($url, [
            'httpheader' => [
                'Accept: application/json, text/plain, */*',
                'Accept-Encoding: gzip, deflate, br, zstd',
                'Accept-Language: zh-CN,zh;q=0.9,en;q=0.8,en-GB;q=0.7,en-US;q=0.6',
                'Cache-Control: no-cache',
                'Connection: keep-alive',
                'Host: website.zbbmcc.com',
                'Origin: https://www.zbbmcc.com',
                'Pragma: no-cache',
                'Referer: https://www.zbbmcc.com/',
                'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0',
                'WebSiteAuthorization: ILwDsrUT985RRMrddwJLwRN07kdzmqpXh0zKdlkNSYXBwgW2pFsG53bh/uoWMBbBCAAcE36W2gLMej1Uq48MK7iWnPQfTKqheKrcK+gWtb3zBE//N5ZuFjbWJi+TguP+VuJXWwk8fBTH1U6rJb83Xgi0RiiNJPhHAXbz1Zsyco1qRcLf6uWVx8LsIg4Wy/i2yP76OKd+j/1e+tHsexYi1OFUqJCWmRnR+pEPDAINSmxgflPy0KbEQsQDTbb1ezNSEcSaTkZ+fV6G1AdZ3ezBMUK0teaZcr31g9i1Tuz5KHrMT/ivj1zLnpCODgPZ4Dw3kAkoHZc0VScK9HWV2R6uxw=='
            ]
        ]);
        if($res !== false) {
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
                            $detail_url = 'https://website.zbbmcc.com/prod-api/open/notice/getBidFrondNoticeDetail?noticeId=' . $v['id'];
                            $detail_res = global_curl($detail_url, [
                                'httpheader' => [
                                    'Accept: application/json, text/plain, */*',
                                    'Accept-Encoding: gzip, deflate, br, zstd',
                                    'Accept-Language: zh-CN,zh;q=0.9,en;q=0.8,en-GB;q=0.7,en-US;q=0.6',
                                    'Cache-Control: no-cache',
                                    'Connection: keep-alive',
                                    'Host: website.zbbmcc.com',
                                    'Origin: https://www.zbbmcc.com',
                                    'Pragma: no-cache',
                                    'Referer: https://www.zbbmcc.com/',
                                    'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0',
                                    'WebSiteAuthorization: GUNNPs8RHXJ5cfwMdjaxixpCjRopzAJpsR9MhOnxP3A7fb2zBwHNPTmihiBoMg1YWefizT+nx2jvdA+9LOwmNlXzo2vqa8stQt4oJ2xE0tUc8x/zYg0krcnaPjOpsm3dl2ncjgNaspVpmiJzIdVqYVVgsezrb5NFxnBLDoDxS8zU7du8FTetGy7dx7VpYVUy7ei/fnXgvdniKYBgcZxfXFfDkr+YaJv3EsT0P5uzPUH4jLFnIwHky6TvLLjNqh4jNt1MtcW1oypr39KPSsFAmfhj6EkQNnNlJxF+XcmtwI2a2ZHNgTACtazKzLdyj0Iod9iV31AdNuz1K6LL3HAx'
                                ]
                            ]);
                            if($detail_res !== false) {
                                $detail_obj = json_decode($detail_res, true);
                                if (isset($detail_obj['data']['noticeFileUrl'])) {
                                    $data['bidSiteId'] = 1025;
                                    $data['bidSiteName'] = '北京明德致信咨询有限公司';
                                    $data['bidTitle'] = $v['noticeTitle'];
                                    $data['bidUrl'] = $detail_obj['data']['noticeFileUrl'];
                                    $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['publishTime']));
                                    $data['inDate'] = date('Y-m-d H:i:s');
                                    $final_arr[] = $data;
                                }
                            }
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 1025 -> 北京明德致信咨询有限公司');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 1025 -> 北京明德致信咨询有限公司');
        }
        // 1029 深圳市鹏达招标有限公司
        $url = 'http://www.szpdzb.com/?m=home&c=Lists&a=index&tid=6';
        $res = global_curl($url);
        if($res !== false) {
            $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
            $preg = '#<divclass="rownews-list">.*<a.*href="(.*)".*<spanclass="p1">(.*)</span><spanclass="p2">(.*)</span></div><h4>(.*)</h4>.*</a></div></div>#isU';
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
                            $data['bidSiteId'] = 1029;
                            $data['bidSiteName'] = '深圳市鹏达招标有限公司';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = strpos($v['url'], 'http') === 0 ? $v['url'] : ('http://www.szpdzb.com'.$v['url']);
                            $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 1029 -> 深圳市鹏达招标有限公司');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 1029 -> 深圳市鹏达招标有限公司');
        }
        // 1035 广东科安达招标有限公司
        $url = 'https://www.gdkeanda.com/index.php?c=category&id=2';
        $res = global_curl($url);
        if($res !== false) {
            $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
            $res_temp = [];
            preg_match('#<ulclass="newslist">(.*)</ul>#isU', $res, $res_temp);
            $preg = '#<li.*<a.*href="(.*)".*title="(.*)".*<divclass="newsDate"><div>(.*)</div>.*</li>#isU';
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
                            $data['bidSiteId'] = 1035;
                            $data['bidSiteName'] = '广东科安达招标有限公司';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = strpos($v['url'], 'http') === 0 ? $v['url'] : ('https://www.gdkeanda.com'.$v['url']);
                            $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }

            } else {
                log_message('error', '招标采集：未匹配到数据 1035 -> 广东科安达招标有限公司');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 1035 -> 广东科安达招标有限公司');
        }
        // 1036 广东省电信规划设计院有限公司
        $url = 'https://www.gpdi.com/homepage/cggg2/index.jhtml';
        $res = global_curl($url);
        if($res !== false) {
            $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
            $res_temp = [];
            preg_match('#<ulclass="common-ulcommon-current-ul">(.*)</ul>#isU', $res, $res_temp);
            $preg = '#<li.*data-url="(.*)".*>(.*)</a></p><i.*>(.*)</i></a></li>#isU';
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
                            $data['bidSiteId'] = 1036;
                            $data['bidSiteName'] = '广东省电信规划设计院有限公司';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = $v['url'];
                            $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }

            } else {
                log_message('error', '招标采集：未匹配到数据 1036 -> 广东省电信规划设计院有限公司');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 1036 -> 广东省电信规划设计院有限公司');
        }
        // 1039 江西铭大招标代理咨询有限公司
        $url = 'http://www.jxmdzb.com/index/purchase.html';
        $res = global_curl($url);
        if($res !== false) {
            $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
            $res_temp = [];
            preg_match('#<ulclass="type-cont">(.*)</ul>#isU', $res, $res_temp);
            $preg = '#<li.*<a.*href="(.*)".*>(.*)</a><span>(.*)</span></li>#isU';
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
                            $data['bidSiteId'] = 1039;
                            $data['bidSiteName'] = '江西铭大招标代理咨询有限公司';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = strpos($v['url'], 'http') === 0 ? $v['url'] : ('http://www.jxmdzb.com/'.$v['url']);
                            $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }

            } else {
                log_message('error', '招标采集：未匹配到数据 1039 -> 江西铭大招标代理咨询有限公司');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 1039 -> 江西铭大招标代理咨询有限公司');
        }
        // 1061 深圳市东海国际招标有限公司 【特殊通过浏览器刷新插件+自定义js获取数据后发送到本系统api并存数据库，每次采集从数据库拿】
        $res = $this->db->table('bid_temp')->select('res')->where('site', '深圳市东海国际招标有限公司')->get()->getRowArray()['res'] ?? '';
        if($res) {
            $preg = '#<tr><td><ahref=.*<ahref="(.*)">(.*)</a></td><td><a.*>(.*)</a></td></tr>#isU';
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
                            $data['bidSiteId'] = 1061;
                            $data['bidSiteName'] = '深圳市东海国际招标有限公司';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = strpos($v['url'], 'http') === 0 ? $v['url'] : ('https://www.szdhit.com'.$v['url']);
                            $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 1061 -> 深圳市东海国际招标有限公司');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 1061 -> 深圳市东海国际招标有限公司');
        }
        // 1084 深圳高星项目管理有限公司
        $url = 'http://gxsz.com/index/index/ajaxgetlist.html';
        $res = global_curl($url, [
            'post' => 1,
            'postfields' => 'cid=36&item_no=&title=&page=1',
            'httpheader' => [
                'Accept: application/json, text/javascript, */*; q=0.01',
                'Accept-Encoding: gzip, deflate',
                'Accept-Language: zh-CN,zh;q=0.9,en;q=0.8,en-GB;q=0.7,en-US;q=0.6',
                'Cache-Control: no-cache',
                'Connection: keep-alive',
                'Content-Length: 29',
                'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
                'Host: gxsz.com',
                'Origin: http://gxsz.com',
                'Pragma: no-cache',
                'Referer: http://gxsz.com/',
                'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0',
                'X-Requested-With: XMLHttpRequest'
            ]
        ]);
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
                            $data['bidSiteId'] = 1084;
                            $data['bidSiteName'] = '深圳高星项目管理有限公司';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = $v['url'];
                            $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['tender_time']));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 1084 -> 深圳高星项目管理有限公司');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 1084 -> 深圳高星项目管理有限公司');
        }
        // 1118 广东省国晟招标有限公司
        $url = 'http://www.gdgszb.com/info/caigougonggao.html';
        $res = global_curl($url, [
            'httpheader' => [
                'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7',
                'Accept-Encoding: gzip, deflate',
                'Accept-Language: zh-CN,zh;q=0.9,en;q=0.8,en-GB;q=0.7,en-US;q=0.6',
                'Cache-Control: no-cache',
                'Connection: keep-alive',
                'Host: www.gdgszb.com',
                'Pragma: no-cache',
                'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0'
            ]
        ]);
        if ($res !== false) {
            $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
            $preg = '#<li><divclass="mediamedia-x">.*<a.*href="(.*)".*title="(.*)".*</a></h3></div><divclass=\'time\'>(.*)</div></div></div></li>#isU';
            $obj = [];
            $arr = [];
            preg_match_all($preg, $res, $obj);
            $count = (is_countable($obj) && count($obj) > 0) ? count($obj[0]) : 0;
            if ($count > 0) {
                for ($j = 0; $j < $count; $j++) {
                    $arr[$j]['url'] = $obj[1][$j];
                    $arr[$j]['title'] = $obj[2][$j];
                    $arr[$j]['beginDate'] = str_replace('/', '-', $obj[3][$j]);
                }
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['title'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['title'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 1118;
                            $data['bidSiteName'] = '广东省国晟招标有限公司';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = strpos($v['url'], 'http') === 0 ? $v['url'] : ('http://www.gdgszb.com'.$v['url']);
                            $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 1118 -> 广东省国晟招标有限公司');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 1118 -> 广东省国晟招标有限公司');
        }
        // 1125 国采招标（深圳）有限公司
        $urls = [
            '政府采购' => 'https://www.gczbsz.com/channels/157.html',
            '自行采购' => 'https://www.gczbsz.com/channels/158.html'
        ];
        foreach ($urls as $index => $url) {
            $res = global_curl($url);
            if ($res !== false) {
                $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
                $preg = '#<li><aclass="box-title"href="(.*)".*>(.*)</a><spanclass="box-date">(.*)</span></li>#isU';
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
                                $data['bidSiteId'] = 1125;
                                $data['bidSiteName'] = '国采招标（深圳）有限公司';
                                $data['bidTitle'] = $v['title'];
                                $data['bidUrl'] = strpos($v['url'], 'http') === 0 ? $v['url'] : ('https://www.gczbsz.com'.$v['url']);
                                $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                                $data['inDate'] = date('Y-m-d H:i:s');
                                $final_arr[] = $data;
                            }
                        }
                    }
                } else {
                    log_message('error', '招标采集：未匹配到数据 1125 -> 国采招标（深圳）有限公司 -> ' . $index);
                }
            } else {
                log_message('error', '招标采集：未获取到网页 1125 -> 国采招标（深圳）有限公司 -> ' . $index);
            }
        }
        // 1126 宁波中基国际招标有限公司
        $url = 'https://www.cbbidding.com/Index/cms/mid/22.html';
        $res = global_curl($url);
        if ($res !== false) {
            $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
            $res_temp = [];
            preg_match('#<p>公布日期</p></div><ulclass="list">(.*)</ul>#isU', $res, $res_temp);
            $preg = '#<li.*<a.*href=\'(.*)\'.*</i>(.*)</a>.*<divclass="addtime">(.*)</div>.*</li>#isU';
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
                            $data['bidSiteId'] = 1126;
                            $data['bidSiteName'] = '宁波中基国际招标有限公司';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = strpos($v['url'], 'http') === 0 ? $v['url'] : ('https://www.cbbidding.com'.$v['url']);
                            $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 1126 -> 宁波中基国际招标有限公司');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 1126 -> 宁波中基国际招标有限公司');
        }
        // 1129 广东华邦招标代理有限公司
        $url = 'https://www.gdhbdl.com/index.php?m=home&c=Lists&a=index&tid=17';
        $res = global_curl($url);
        if($res !== false) {
            $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
            $preg = '#<li><divclass="list_picfl">.*<a.*<a.*href="(.*)".*>(.*)</a></p>.*</i>发布时间：(.*)</span>.*</li>#isU';
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
                            $data['bidSiteId'] = 1129;
                            $data['bidSiteName'] = '广东华邦招标代理有限公司';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = strpos($v['url'], 'http') === 0 ? $v['url'] : ('https://www.gdhbdl.com'.$v['url']);
                            $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 1129 -> 广东华邦招标代理有限公司');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 1129 -> 广东华邦招标代理有限公司');
        }
        // 1132 广东信仕德建设项目管理有限公司
        $url = 'http://www.gd-xsd.cn/col.jsp?id=112';
        $res = global_curl($url, [
            'httpheader' => [
                'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7',
                'Accept-Encoding: gzip, deflate',
                'Accept-Language: zh-CN,zh;q=0.9,en;q=0.8,en-GB;q=0.7,en-US;q=0.6',
                'Cache-Control: no-cache',
                'Connection: keep-alive',
                'Host: www.gd-xsd.cn',
                'Pragma: no-cache',
                'Upgrade-Insecure-Requests: 1',
                'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0'
            ]
        ]);
        if($res !== false) {
            $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
            $preg = '#<divtopClassName=\'top1\'topSwitch=\'on\'newsId=.*<a.*href="(.*)".*title="(.*)".*</a>.*<a.*>(.*)</a></div>#isU';
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
                            $data['bidSiteId'] = 1132;
                            $data['bidSiteName'] = '广东信仕德建设项目管理有限公司';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = strpos($v['url'], 'http') === 0 ? $v['url'] : ('http://www.gd-xsd.cn'.$v['url']);
                            $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 1132 -> 广东信仕德建设项目管理有限公司');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 1132 -> 广东信仕德建设项目管理有限公司');
        }
        // 1138 京采（深圳）招标代理有限公司
        $url = 'http://120.24.180.32:8088/homePage/listpage/1?detail=1&pageSize=20&pageNum=0';
        $res = global_curl($url);
        if($res !== false) {
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
                            $data['bidSiteId'] = 1138;
                            $data['bidSiteName'] = '京采（深圳）招标代理有限公司';
                            $data['bidTitle'] = $v['noticeTitle'];
                            $data['bidUrl'] = 'http://www.szjingcai.com.cn/details.html?'.$v['noticeId'];
                            $data['beginDate'] = $v['createTime'];
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 1138 -> 京采（深圳）招标代理有限公司');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 1138 -> 京采（深圳）招标代理有限公司');
        }
        // 1139 江西明台项目咨询管理有限公司
        $url = 'http://www.jxmtzb.com/html/zhaobiaogonggao/';
        $res = global_curl($url);
        if($res !== false) {
            $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
            $preg = '#<aclass="zbdt_list".*href="(.*)".*<divclass="time"><span>(.*)</span>(.*)</div><divclass="content_r"><divclass="titlelin1">(.*)</div>.*</a>#isU';
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
                            $data['bidSiteId'] = 1139;
                            $data['bidSiteName'] = '江西明台项目咨询管理有限公司';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = strpos($v['url'], 'http') === 0 ? $v['url'] : ('http://www.jxmtzb.com'.$v['url']);
                            $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 1139 -> 江西明台项目咨询管理有限公司');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 1139 -> 江西明台项目咨询管理有限公司');
        }
        // 1184 中创恒梦工程咨询有限公司
        $url = 'http://www.hubeidream.com/article/type/30-1.html';
        $res = global_curl($url);
        if($res !== false) {
            $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
            $res_temp = [];
            preg_match('#divclass="xwzx"><ul>(.*)</ul>#isU', $res, $res_temp);
            $preg = '#<li.*<a.*href="(.*)".*<p.*>(.*)</p>.*</li>#isU';
            $obj = [];
            $arr = [];
            is_countable($res_temp) && count($res_temp) > 0 && preg_match_all($preg, $res_temp[1], $obj);
            $count = (is_countable($obj) && count($obj) > 0) ? count($obj[0]) : 0;
            if ($count > 0) {
                for ($j = 0; $j < $count; $j++) {
                    $arr[$j]['url'] = $obj[1][$j];
                    $arr[$j]['title'] = $obj[2][$j];
                    $arr[$j]['beginDate'] = date('Y-m-d H:i:s');
                }
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['title'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['title'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 1184;
                            $data['bidSiteName'] = '中创恒梦工程咨询有限公司';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = strpos($v['url'], 'http') === 0 ? $v['url'] : ('http://www.hubeidream.com'.$v['url']);
                            $data['beginDate'] = date('Y-m-d H:i:s');
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 1184 -> 中创恒梦工程咨询有限公司');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 1184 -> 中创恒梦工程咨询有限公司');
        }
        // 1203 中邦科技
        $url = 'http://www.cobo91.com/custom2817407.html';
        $res = global_curl($url);
        if($res !== false) {
            $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
            $preg = '#<li><divclass="m-title"><a.*href="(.*)".*>(.*)</a><span>(.*)</span></div></li>#isU';
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
                            $data['bidSiteId'] = 1203;
                            $data['bidSiteName'] = '中邦科技';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = strpos($v['url'], 'http') === 0 ? $v['url'] : ('http://www.cobo91.com' . $v['url']);
                            $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 1203 -> 中邦科技');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 1203 -> 中邦科技');
        }
        // 1212 江西合胜合招标咨询有限公司
        $url = 'http://www.jxhshzb.com/home/tender/tender/inftype/1.html';
        $res = global_curl($url);
        if($res !== false) {
            $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
            $preg = '#<divclass="new-list">.*<a.*href="(.*)".*>(.*)</a><span>(.*)</span>.*</div>#isU';
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
                            $data['bidSiteId'] = 1212;
                            $data['bidSiteName'] = '江西合胜合招标咨询有限公司';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = strpos($v['url'], 'http') === 0 ? $v['url'] : ('http://www.jxhshzb.com' . str_replace('..', '', $v['url']));
                            $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 1212 -> 江西合胜合招标咨询有限公司');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 1212 -> 江西合胜合招标咨询有限公司');
        }
        // 1229 深圳市振东招标代理有限公司 【比较特殊，要重复多次获取cookie】
        $url = 'https://www.szzdzb.cn/Product-index-id-8/';
        $res = global_curl($url, [
            'header' => 1
        ]);
        if($res !== false) {
            $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
            if (strpos($res, 'Set-Cookie:') !== false) {
                $cookie_1229 = [];
                preg_match_all('#Set-Cookie:(.*);#isU', $res, $cookie_1229);
                $cookie_str_1229 = '';
                foreach ($cookie_1229[1] as $k => $v) {
                    $cookie_str_1229 .= trim($v) . '; ';
                }
                $res = global_curl($url, [
                    'header' => 1,
                    'httpheader' => [
                        'accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7',
                        'accept-encoding: gzip, deflate, br, zstd',
                        'accept-language: zh-CN,zh;q=0.9,en;q=0.8,en-GB;q=0.7,en-US;q=0.6',
                        'cache-control: no-cache',
                        'cookie: ' . $cookie_str_1229 . 'lg=cn; PbootSystem=g8k6rskfsqgkbtsrfnuugs60jp',
                        'pragma: no-cache',
                        'referer: https://www.szzdzb.cn/Product-index-id-8/',
                        'user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36 Edg/148.0.0.0'
                    ]
                ]);
                if($res !== false) {
                    $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
                    if (strpos($res, 'Set-Cookie') !== false) {
                        preg_match_all('#Set-Cookie:(.*);#isU', $res, $cookie_1229);
                        foreach ($cookie_1229[1] as $k => $v) {
                            $cookie_str_1229 .= trim($v) . '; ';
                        }
                        $res = global_curl($url, [
                            'header' => 1,
                            'httpheader' => [
                                'accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7',
                                'accept-encoding: gzip, deflate, br, zstd',
                                'accept-language: zh-CN,zh;q=0.9,en;q=0.8,en-GB;q=0.7,en-US;q=0.6',
                                'cache-control: no-cache',
                                'cookie: ' . $cookie_str_1229 . 'lg=cn; PbootSystem=g8k6rskfsqgkbtsrfnuugs60jp',
                                'pragma: no-cache',
                                'referer: https://www.szzdzb.cn/Product-index-id-8/',
                                'user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36 Edg/148.0.0.0'
                            ]
                        ]);
                        if($res !== false) {
                            $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
                        }
                    }
                }
            }
            $preg = '#<trclass="">.*<a.*</a></td><td.*<a.*href="(.*)".*>(.*)</a></td><td>(.*)</td></tr>#isU';
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
                            $data['bidSiteId'] = 1229;
                            $data['bidSiteName'] = '深圳市振东招标代理有限公司';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = strpos($v['url'], 'http') === 0 ? $v['url'] : ('https://www.szzdzb.cn' . str_replace('..', '', $v['url']));
                            $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 1229 -> 深圳市振东招标代理有限公司');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 1229 -> 深圳市振东招标代理有限公司');
        }
        // 1230 广州广控招标项目管理咨询有限公司
        $url = 'https://www.gzguangkong.cn/bidding';
        $res = global_curl($url);
        if($res !== false) {
            $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
            $res_temp = [];
            preg_match('#<divclass="news_list">.*<ul>(.*)</ul>#isU', $res, $res_temp);
            $preg = '#<li.*<a.*href="(.*)".*>(.*)</a><span>(.*)</span></li>#isU';
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
                            $data['bidSiteId'] = 1230;
                            $data['bidSiteName'] = '广州广控招标项目管理咨询有限公司';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = strpos($v['url'], 'http') === 0 ? $v['url'] : ('https://www.gzguangkong.cn' . str_replace('..', '', $v['url']));
                            $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 1230 -> 广州广控招标项目管理咨询有限公司');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 1230 -> 广州广控招标项目管理咨询有限公司');
        }
        // 1235 常州市城投建设工程招标有限公司
        $url = 'https://www.czctzb.com/zhaobiao28.html';
        $res = global_curl($url);
        if($res !== false) {
            $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
            $res_temp = [];
            preg_match('#<divclass="fr"style="width:1000px;margin-right:20px"><table.*>(.*)</table>#isU', $res, $res_temp);
            $preg = '#<tr><tdbgcolor.*<a.*href=(.*)target="_blank">(.*)</a></td><td.*</td><tdbgcolor.*>(.*)</td></tr>#isU';
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
                            $data['bidSiteId'] = 1235;
                            $data['bidSiteName'] = '常州市城投建设工程招标有限公司';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = strpos($v['url'], 'http') === 0 ? $v['url'] : ('https://www.czctzb.com/' . str_replace('..', '', $v['url']));
                            $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 1235 -> 常州市城投建设工程招标有限公司');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 1235 -> 常州市城投建设工程招标有限公司');
        }
        // 1241 粤采(广东)采购招标代理有限公司
        $url = 'http://www.yuecaizhaobiao.com/zhaobiao/';
        $res = global_curl($url);
        if($res !== false) {
            $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
            $preg = '#<li><span>.*</span><span>(.*)</span>.*<a.*href="(.*)".*>(.*)</a></li>#isU';
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
                            $data['bidSiteId'] = 1241;
                            $data['bidSiteName'] = '粤采(广东)采购招标代理有限公司';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = strpos($v['url'], 'http') === 0 ? $v['url'] : ('http://www.yuecaizhaobiao.com' . $v['url']);
                            $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 1241 -> 粤采(广东)采购招标代理有限公司');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 1241 -> 粤采(广东)采购招标代理有限公司');
        }
        // 1242 贵州中泰项目管理有限公司
        $url = 'https://www.gzztzx.cn/xmdt/17148962.html';
        $res = global_curl($url);
        if($res !== false) {
            $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
            $res_temp = [];
            preg_match('#<divclass="e_loop-1s_list"needjs="true"><divclass="p_list">(.*)<divclass="p_page">#isU', $res, $res_temp);
            $preg = '#<divclass="cbox-1p_loopitem">.*<a.*href="(.*)".*>(.*)</a>.*<Pclass="e_timeFormat-7s_body">(.*)</P><Pclass="e_timeFormat-5s_body">(.*)</P></div>#isU';
            $obj = [];
            $arr = [];
            is_countable($res_temp) && count($res_temp) > 0 && preg_match_all($preg, $res_temp[1], $obj);
            $count = (is_countable($obj) && count($obj) > 0) ? count($obj[0]) : 0;
            if ($count > 0) {
                for ($j = 0; $j < $count; $j++) {
                    $arr[$j]['url'] = $obj[1][$j];
                    $arr[$j]['title'] = $obj[2][$j];
                    $arr[$j]['beginDate'] = $obj[3][$j] . '-' . $obj[4][$j];
                }
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['title'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['title'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 1242;
                            $data['bidSiteName'] = '贵州中泰项目管理有限公司';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = strpos($v['url'], 'http') === 0 ? $v['url'] : ('https://www.gzztzx.cn' . $v['url']);
                            $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 1242 -> 贵州中泰项目管理有限公司');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 1242 -> 贵州中泰项目管理有限公司');
        }
        // 1275 广东嘉晟招标采购有限公司
        $url = 'http://www.gdjscbcg.com/news_3201131.html';
        $res = global_curl($url);
        if ($res !== false) {
            $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
            $preg = '#<li><divclass="m-title">.*<a.*href="(.*)".*>(.*)</a><span>发布时间:(.*)</span></div>.*</li>#isU';
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
                            $data['bidSiteId'] = 1275;
                            $data['bidSiteName'] = '广东嘉晟招标采购有限公司';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = strpos($v['url'], 'http') === 0 ? $v['url'] : ('http://www.gdjscbcg.com' . $v['url']);
                            $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 1275 -> 广东嘉晟招标采购有限公司');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 1275 -> 广东嘉晟招标采购有限公司');
        }
        // 1278 广州丰晟项目管理咨询有限公司 【特殊通过浏览器刷新插件+自定义js获取数据后发送到本系统api并存数据库，每次采集从数据库拿】
        $res = $this->db->table('bid_temp')->select('res')->where('site', '广州丰晟项目管理咨询有限公司')->get()->getRowArray()['res'] ?? '';
        if ($res && strpos($res, '<h3>') !== false) {
            $preg = '#<li.*<a.*href="(.*)".*title="(.*)".*<span>(.*)</span></a></li>#isU';
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
                            $data['bidSiteId'] = 1278;
                            $data['bidSiteName'] = '广州丰晟项目管理咨询有限公司';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = strpos($v['url'], 'http') === 0 ? $v['url'] : ('https://www.gzfsgl.cn/' . $v['url']);
                            $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 1278 -> 广州丰晟项目管理咨询有限公司');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 1278 -> 广州丰晟项目管理咨询有限公司');
        }
        // 1303 华采招标集团
        $url = 'http://www.bjhczb.com/list/index/id/12';
        $res = global_curl($url);
        if ($res !== false) {
            $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
            $res_temp = [];
            preg_match('#<ulclass="notice-ul">(.*)</ul>#isU', $res, $res_temp);
            $preg = '#<li.*<a.*href="(.*)".*<h2>(.*)<spanclass="time">(.*)</span></h2>.*</li>#isU';
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
                            $data['bidSiteId'] = 1303;
                            $data['bidSiteName'] = '华采招标集团';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = strpos($v['url'], 'http') === 0 ? $v['url'] : ('http://www.bjhczb.com' . $v['url']);
                            $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 1303 -> 华采招标集团');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 1303 -> 华采招标集团');
        }
        // 1315 深圳市全过程项目管理有限公司
        $url = 'http://www.szqgc.com/list/19.html';
        $res = global_curl($url);
        if ($res !== false) {
            $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
            $preg = '#<tr><td><a.*href="(.*)".*class="titleOverflow">(.*)</a></td><td><a.*>(.*)</a>.*</tr>#isU';
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
                            $data['bidSiteId'] = 1315;
                            $data['bidSiteName'] = '深圳市全过程项目管理有限公司';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = strpos($v['url'], 'http') === 0 ? $v['url'] : ('http://www.szqgc.com' . $v['url']);
                            $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 1315 -> 深圳市全过程项目管理有限公司');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 1315 -> 深圳市全过程项目管理有限公司');
        }
        // 1323 广东智信招标采购有限公司
        $url = 'https://www.gdzxzbcg.com/zfcg/zbgg2/zh-cn/index.html';
        $res = global_curl($url);
        if ($res !== false) {
            $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
            $preg = '#<liclass="news-item".*<a.*href="(.*)".*>(.*)</a></span><div.*>\[(.*)\]</div></li>#isU';
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
                            $data['bidSiteId'] = 1323;
                            $data['bidSiteName'] = '广东智信招标采购有限公司';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = strpos($v['url'], 'http') === 0 ? $v['url'] : ('https://www.gdzxzbcg.com' . $v['url']);
                            $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 1323 -> 广东智信招标采购有限公司');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 1323 -> 广东智信招标采购有限公司');
        }
        // 1336 苏州乐慧招投标咨询有限公司
        $url = 'http://www.szlhztb.cn/';
        $res = global_curl($url);
        if ($res !== false) {
            $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
            $res_temp = [];
            preg_match('#<divclass=\'newsListJ_newsList\'.*>(.*)<divclass=\'newsListJ_newsList#isU', $res, $res_temp);
            $preg = '#<divtopClassName=\'top1\'.*<a.*href="(.*)".*title="(.*)".*</a>.*<aclass=\'fk-newsListDate\'.*>(.*)</a></td></tr></table>#isU';
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
                            $data['bidSiteId'] = 1336;
                            $data['bidSiteName'] = '苏州乐慧招投标咨询有限公司';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = strpos($v['url'], 'http') === 0 ? $v['url'] : ('http://www.szlhztb.cn' . $v['url']);
                            $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 1336 -> 苏州乐慧招投标咨询有限公司');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 1336 -> 苏州乐慧招投标咨询有限公司');
        }
        // 1338 肇庆德力招标代理有限公司
        $url = 'http://www.zqdlzbdl.com/articlist.asp?menuid=2&ii=2';
        $res = global_curl($url);
        if ($res !== false) {
            $res = mb_convert_encoding($res, "UTF-8", "GBK"); //转换编码
            $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
            $preg = '#<divclass=\'articlist\'>.*<a.*href=\'(.*)\'.*title=\'(.*)\'.*</a></div>#isU';
            $obj = [];
            $arr = [];
            preg_match_all($preg, $res, $obj);
            $count = (is_countable($obj) && count($obj) > 0) ? count($obj[0]) : 0;
            if ($count > 0) {
                for ($j = 0; $j < $count; $j++) {
                    $arr[$j]['url'] = $obj[1][$j];
                    $arr[$j]['title'] = $obj[2][$j];
                    $arr[$j]['beginDate'] = date('Y-m-d H:i:s');
                }
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['title'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['title'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 1338;
                            $data['bidSiteName'] = '肇庆德力招标代理有限公司';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = strpos($v['url'], 'http') === 0 ? $v['url'] : ('http://www.zqdlzbdl.com/' . $v['url']);
                            $data['beginDate'] = $v['beginDate'];
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 1338 -> 肇庆德力招标代理有限公司');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 1338 -> 肇庆德力招标代理有限公司');
        }
        // 1343 建通控股集团
        $url = 'http://www.gxjt.net/web/index.php?r=site/tenderinglist&cid=35';
        $res = global_curl($url);
        if ($res !== false) {
            $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
            $res_temp = [];
            preg_match('#</span>招标公告</div><ul>(.*)</ul>#isU', $res, $res_temp);
            $preg = '#<li.*<a.*href="(.*)".*title="(.*)".*</div><divclass="right2">.*\[(.*)\]</div></div></a></li>#isU';
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
                            $data['bidSiteId'] = 1343;
                            $data['bidSiteName'] = '建通控股集团';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = strpos($v['url'], 'http') === 0 ? $v['url'] : ('http://www.gxjt.net' . $v['url']);
                            $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 1343 -> 建通控股集团');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 1343 -> 建通控股集团');
        }
        // 1363 广西众联工程项目管理有限公司
        $url = 'http://www.gxzhonglian.cn/NewsList.aspx?NewsTypeKeyID=3';
        $res = global_curl($url);
        if ($res !== false) {
            if (strpos($res, 'window.location.href=') !== false) {
                $url_temp = [];
                preg_match('#window.location.href=\'(.*)\';#isU', $res, $url_temp);
                if (is_countable($url_temp) && count($url_temp) > 0) {
                    $url = 'http://www.gxzhonglian.cn'. $url_temp[1];
                    $res = global_curl($url);
                }
            }
            $res = mb_convert_encoding($res, "UTF-8", "GBK"); //转换编码
            $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
            $preg = '#<tr><tdclass="lz_b"><a.*href="(.*)".*>(.*)</a></td><tdclass="lz_d".*>(.*)</td></tr>#isU';
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
                $matchData = [];
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['title'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['title'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 1363;
                            $data['bidSiteName'] = '广西众联工程项目管理有限公司';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = strpos($v['url'], 'http') === 0 ? $v['url'] : ('http://www.gxzhonglian.cn/' . $v['url']);
                            $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $matchData[] = $data;
                        }
                    }
                }
                if (count($matchData) > 0) {
                    foreach ($matchData as $k => $v) {
                        if ($this->db->table('bid_info')->where(['bidSiteId' => $v['bidSiteId'], 'bidTitle' => $v['bidTitle'], 'beginDate' => $v['beginDate']])->countAllResults() > 0) {
                            // 本站标题一样、发布日期一样的招标信息，只录一条
                            continue;
                        } else {
                            $final_arr[] = $v;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 1363 -> 广西众联工程项目管理有限公司');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 1363 -> 广西众联工程项目管理有限公司');
        }
        // 1369 福建省月德贵招标代理服务有限公司 【特殊通过浏览器刷新插件+自定义js获取数据后发送到本系统api并存数据库，每次采集从数据库拿】
        $res = $this->db->table('bid_temp')->select('res')->where('site', '福建省月德贵招标代理服务有限公司')->get()->getRowArray()['res'] ?? '';
        if ($res) {
            $preg = '#<li.*<pclass="time"><span>(.*)</span><i>(.*)</i></p>.*<a.*href="(.*)".*<h3>(.*)</h3></a>.*</li>#isU';
            $obj = [];
            $arr = [];
            preg_match_all($preg, $res, $obj);
            $count = (is_countable($obj) && count($obj) > 0) ? count($obj[0]) : 0;
            if ($count > 0) {
                for ($j = 0; $j < $count; $j++) {
                    $arr[$j]['url'] = $obj[3][$j];
                    $arr[$j]['title'] = $obj[4][$j];
                    $arr[$j]['beginDate'] = $obj[2][$j] . '-' . $obj[1][$j];
                }
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['title'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['title'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 1369;
                            $data['bidSiteName'] = '福建省月德贵招标代理服务有限公司';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = strpos($v['url'], 'http') === 0 ? $v['url'] : ('http://www.ydgzb.com/' . $v['url']);
                            $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 1369 -> 福建省月德贵招标代理服务有限公司');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 1369 -> 福建省月德贵招标代理服务有限公司');
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
            $this->db->table('option')->where('optionName', 'bid_spider_0_2_status')->update(['optionValue' => 0]);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                log_message('error', '招标采集：招标信息入库失败（招标代理类）事务已回滚！');
            }
        }
    }
}
