<?php

namespace App\Controllers\Api;
use App\Controllers\BaseController;

class SpiderBid04 extends BaseController
{
    /* 招标企业类 按每5分钟执行一次的标准设计 */
    public function index()
    {
        $this->db->table('option')->where('optionName', 'bid_spider_0_4_status')->update(['optionValue' => 1]);
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
        // 250 国铁采购平台
        $url = 'https://cg.95306.cn/proxy/portal/mainPage/information/queryListForMainPage';
        $res = global_curl($url);
        if ($res !== false) {
            $obj = json_decode($res, true);
            $arr = $obj['data']['bidNoticeList']['0102'] ?? [];
            if (is_array($arr) && count($arr) > 0) {
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['notTitle'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['notTitle'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 250;
                            $data['bidSiteName'] = '国铁采购平台';
                            $data['bidTitle'] = $v['notTitle'];
                            $data['bidUrl'] = 'https://cg.95306.cn/baseinfor/notice/informationShow?id='.$v['id'];
                            $data['beginDate'] = $v['checkTime'];
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 250 -> 国铁采购平台');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 250 -> 国铁采购平台');
        }
        // 292 中煤易购
        $url = 'https://ego.chinacoal.com/dyc/busicommon/noauth/homePageQryArticleList';
        $res = global_curl($url,[
            'post' => 1,
            'postfields' => '{"pageNo":1,"pageSize":10,"itemType":1,"objType":1,"source":2}',
            'httpheader' => [
                'Accept: application/json, text/plain, */*',
                'Accept-Encoding: gzip, deflate, br, zstd',
                'Accept-Language: zh-CN,zh;q=0.9,en;q=0.8,en-GB;q=0.7,en-US;q=0.6',
                'Cache-Control: no-cache',
                'Connection: keep-alive',
                'Content-Length: 62',
                'Content-Type: application/json;charset=UTF-8',
                'Host: ego.chinacoal.com',
                'Origin: https://ego.chinacoal.com',
                'Pragma: no-cache',
                'Referer: https://ego.chinacoal.com/',
                'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0'
            ]
        ]);
        if ($res !== false) {
            $obj = json_decode($res, true);
            $arr = $obj['data']['rows'] ?? [];
            if (is_array($arr) && count($arr) > 0) {
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['articleName'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['articleName'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 292;
                            $data['bidSiteName'] = '中煤易购';
                            $data['bidTitle'] = $v['articleName'];
                            $data['bidUrl'] = 'https://ego.chinacoal.com/#/announcementInquiryDetail?id='.$v['id'];
                            $data['beginDate'] = $v['createTime'];
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 292 -> 中煤易购');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 292 -> 中煤易购');
        }
        // 298 三一重工
        for ($i = 0; $i < 5; $i++) {
            usleep(500000);
            $url = 'https://igsp-api.sany.com.cn/src/purchaser/v1/project-annos/site/projectAnno?orderColumn=0&page='.$i.'&projectType=&size=10';
            $res = global_curl($url);
            if ($res !== false) {
                $obj = json_decode($res, true);
                $arr = $obj['content'] ?? [];
                if (is_array($arr) && count($arr) > 0) {
                    foreach ($arr as $k => $v) {
                        $data = [];
                        if (!preg_match('#(' . $in_keywords . ')#', $v['title'])) {
                            continue;
                        } else {
                            if (preg_match('#(' . $out_keywords . ')#', $v['title'])) {
                                continue;
                            } else {
                                $data['bidSiteId'] = 298;
                                $data['bidSiteName'] = '三一重工';
                                $data['bidTitle'] = $v['title'];
                                $data['bidUrl'] = 'https://igsp.sany.com.cn/public/noticeDetialPage?title='.$v['title'].'&id='.$v['id'].'&titleIcon=sourcNoticeDetial';
                                $data['beginDate'] = $v['publishTime'];
                                $data['inDate'] = date('Y-m-d H:i:s');
                                $final_arr[] = $data;
                            }
                        }
                    }
                } else {
                    log_message('error', '招标采集：未匹配到数据 298 -> 三一重工 -> 第'.$i.'页');
                }
            } else {
                log_message('error', '招标采集：未获取到网页 298 -> 三一重工 -> 第'.$i.'页');
            }
        }
        // 535 中国核工业集团有限公司电子采购平台 【特殊通过浏览器刷新插件+自定义js获取数据后发送到本系统api并存数据库，每次采集从数据库拿】
        $res = $this->db->table('bid_temp')->select('res')->where('site', '中国核工业集团有限公司电子采购平台')->get()->getRowArray()['res'] ?? '';
        if($res) {
            $preg = '#<li><spanclass=".*">.*</span><span.*>(.*)</span>.*<a.*href="(.*)">(.*)</a></li>#isU';
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
                            $data['bidSiteId'] = 535;
                            $data['bidSiteName'] = '中国核工业集团有限公司电子采购平台';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = 'https://www.cnncecp.com'.$v['url'];
                            $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 535 -> 中国核工业集团有限公司电子采购平台');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 535 -> 中国核工业集团有限公司电子采购平台');
        }
        // 542 黔云招采（能源板块）
        $url = 'https://gzny.e-qyzc.com/api/saas-portal/noauth/trans/trade/pageEs';
        $res = global_curl($url, [
            'post' => 1,
            'postfields' => '{"pageNum":1,"pageSize":99,"tradePattern":"1138499232192491521","noticeType":1,"releaseEndTime":"","releaseStartTime":"","purchaseProjectType":"1138486289808052234","purchaseMode":null,"purchasePatternId":"","businessName":""}'
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
                            $data['bidSiteId'] = 542;
                            $data['bidSiteName'] = '黔云招采（能源板块）';
                            $data['bidTitle'] = $v['purchaseProjectName'];
                            $data['bidUrl'] = 'https://gzny.e-qyzc.com/#/trade-info-detail?id='.$v['id'].'&noticeType='.$v['noticeType'].'&publishStatus='.$v['publishStatus'].'&systemStatus';
                            $data['beginDate'] = date('Y-m-d H:i:s', intval($v['releaseTime']) / 1000);
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 542 -> 黔云招采（能源板块）');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 542 -> 黔云招采（能源板块）');
        }
        // 617 酒钢公共交易平台
        $url = 'https://eps.jiugangbid.com/inteligentsearch_wz/rest/esinteligentsearch/getFullTextDataNew';
        $postfieldsArr = [
            '招标采购' => '{"token":"","pn":0,"rn":10,"sdt":"","edt":"","wd":"","inc_wd":"","exc_wd":"","fields":"title;searchtitle","cnum":"002","sort":"{\"istop\":\"0\",\"ordernum\":\"0\",\"webdate\":\"0\"}","ssort":"title","cl":200,"terminal":"","condition":[{"fieldName":"categorynum","equal":"001001002","notEqual":null,"equalList":null,"notEqualList":null,"isLike":true,"likeType":2},{"fieldName":"xiangmuhy","equal":"M","notEqual":null,"equalList":null,"notEqualList":null,"isLike":false,"likeType":0}],"time":[{"fieldName":"zhaobiaoenddate","startTime":"2026-07-03 10:29:35"}],"highlights":"title;searchtitle","statistics":null,"unionCondition":null,"accuracy":"","noParticiple":"1","searchRange":[],"isBusiness":"1"}',
            '非招标采购' => '{"token":"","pn":0,"rn":10,"sdt":"","edt":"","wd":"","inc_wd":"","exc_wd":"","fields":"title;searchtitle","cnum":"002","sort":"{\"istop\":\"0\",\"ordernum\":\"0\",\"webdate\":\"0\"}","ssort":"title","cl":200,"terminal":"","condition":[{"fieldName":"categorynum","equal":"001002002","notEqual":null,"equalList":null,"notEqualList":null,"isLike":true,"likeType":2},{"fieldName":"xiangmuhy","equal":"M","notEqual":null,"equalList":null,"notEqualList":null,"isLike":false,"likeType":0}],"time":[{"fieldName":"zhaobiaoenddate","startTime":"2026-07-03 10:48:15"}],"highlights":"title;searchtitle","statistics":null,"unionCondition":null,"accuracy":"","noParticiple":"1","searchRange":[],"isBusiness":"1"}',
            '竞卖' => '{"token":"","pn":0,"rn":10,"sdt":"","edt":"","wd":"","inc_wd":"","exc_wd":"","fields":"title;searchtitle","cnum":"002","sort":"{\"istop\":\"0\",\"ordernum\":\"0\",\"webdate\":\"0\"}","ssort":"title","cl":200,"terminal":"","condition":[{"fieldName":"categorynum","equal":"001003001","notEqual":null,"equalList":null,"notEqualList":null,"isLike":true,"likeType":2},{"fieldName":"xiangmuhy","equal":"M","notEqual":null,"equalList":null,"notEqualList":null,"isLike":false,"likeType":0}],"time":[{"fieldName":"zhaobiaoenddate","startTime":"2026-07-03 10:50:03"}],"highlights":"title;searchtitle","statistics":null,"unionCondition":null,"accuracy":"","noParticiple":"1","searchRange":[],"isBusiness":"1"}'
        ];
        foreach ($postfieldsArr as $index => $postfields) {
            $res = global_curl($url, [
                'post' => 1,
                'postfields' => $postfields
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
                                $data['bidSiteId'] = 617;
                                $data['bidSiteName'] = '酒钢公共交易平台';
                                $data['bidTitle'] = $v['title'];
                                $data['bidUrl'] = 'https://eps.jiugangbid.com'.$v['linkurl'];
                                $data['beginDate'] = $v['webdate'];
                                $data['inDate'] = date('Y-m-d H:i:s');
                                $final_arr[] = $data;
                            }
                        }
                    }
                } else {
                    log_message('error', '招标采集：未匹配到数据 617 -> 酒钢公共交易平台 -> '.$index);
                }
            } else {
                log_message('error', '招标采集：未获取到网页 617 -> 酒钢公共交易平台 -> '.$index);
            }
        }
        // 624 北京京能电子商务平台
        for ($i = 1; $i <= 20; $i++) { // 非招信息
            $url = 'https://www.powerbeijing-eshop.com/jncms/search/search_result.html?categoryIds=10,11,16,19&tabName=%E9%87%87%E8%B4%AD%E5%85%AC%E5%91%8A%E5%88%97%E8%A1%A8&goSearch=&page='.$i;
            $res = global_curl($url, [
                'httpheader' => [
                    'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7',
                    'Accept-Encoding: gzip, deflate, br, zstd',
                    'Accept-Language: zh-CN,zh;q=0.9,en;q=0.8,en-GB;q=0.7,en-US;q=0.6',
                    'Cache-Control: no-cache',
                    'Connection: keep-alive',
                    'Host: www.powerbeijing-eshop.com',
                    'Pragma: no-cache',
                    'Referer: https://www.powerbeijing-eshop.com/jncms/search/search_result.html?categoryIds=10,11,12,13&tabName=%E9%87%87%E8%B4%AD%E5%85%AC%E5%91%8A%E5%88%97%E8%A1%A8&goSearch=&page=6',
                    'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0'
                ]
            ]);
            if ($res !== false) {
                $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
                $preg = '#<li><ahref="(.*)"title="(.*)">.*<divclass="newsDate"><div>(.*)</div></div></a></li>#isU';
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
                                $data['bidSiteId'] = 624;
                                $data['bidSiteName'] = '北京京能电子商务平台';
                                $data['bidTitle'] = $v['title'];
                                $data['bidUrl'] = 'https:' . $v['url'];
                                $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                                $data['inDate'] = date('Y-m-d H:i:s');
                                $final_arr[] = $data;
                            }
                        }
                    }
                } else {
                    log_message('error', '招标采集：未匹配到数据 624 -> 北京京能电子商务平台 -> 非招信息 -> 第' . $i . '页');
                }
            } else {
                log_message('error', '招标采集：未获取到网页 624 -> 北京京能电子商务平台 -> 非招信息 -> 第' . $i . '页');
            }
        }
        for ($i = 1; $i <= 3; $i++) { // 招标信息
            $url = 'https://www.powerbeijing-eshop.com/jncms/search/bulletin.html?dates=300&categoryId=2&tabName=%E6%8B%9B%E6%A0%87%E5%85%AC%E5%91%8A&page='.$i;
            $res = global_curl($url, [
                'httpheader' => [
                    'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7',
                    'Accept-Encoding: gzip, deflate, br, zstd',
                    'Accept-Language: zh-CN,zh;q=0.9,en;q=0.8,en-GB;q=0.7,en-US;q=0.6',
                    'Cache-Control: no-cache',
                    'Connection: keep-alive',
                    'Host: www.powerbeijing-eshop.com',
                    'Pragma: no-cache',
                    'Referer: https://www.powerbeijing-eshop.com/jncms/search/search_result.html?categoryIds=10,11,12,13&tabName=%E9%87%87%E8%B4%AD%E5%85%AC%E5%91%8A%E5%88%97%E8%A1%A8&goSearch=&page=6',
                    'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0'
                ]
            ]);
            if ($res !== false) {
                $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
                $preg = '#<li><ahref="(.*)"title="(.*)">.*<divclass="newsDate"><div>(.*)</div></div></a></li>#isU';
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
                                $data['bidSiteId'] = 624;
                                $data['bidSiteName'] = '北京京能电子商务平台';
                                $data['bidTitle'] = $v['title'];
                                $data['bidUrl'] = 'https:' . $v['url'];
                                $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                                $data['inDate'] = date('Y-m-d H:i:s');
                                $final_arr[] = $data;
                            }
                        }
                    }
                } else {
                    log_message('error', '招标采集：未匹配到数据 624 -> 北京京能电子商务平台 -> 招标信息 -> 第' . $i . '页');
                }
            } else {
                log_message('error', '招标采集：未获取到网页 624 -> 北京京能电子商务平台 -> 招标信息 -> 第' . $i . '页');
            }
        }
        // 684 安徽建工集团电子化招采平台
        $url = 'https://cg.aceg.com.cn/inteligentsearch/rest/esinteligentsearch/getFullTextDataNew';
        $res = global_curl($url, [
            'post' => 1,
            'postfields' => '{"token":"","pn":0,"rn":99,"sdt":"","edt":"","wd":"%20","inc_wd":"","exc_wd":"","fields":"title","cnum":"001","sort":"{\"webdate\":\"0\"}","ssort":"title","cl":200,"terminal":"","condition":[{"fieldName":"categorynum","equal":"002001","notEqual":null,"equalList":null,"notEqualList":null,"isLike":true,"likeType":2}],"time":[{"fieldName":"webdate","startTime":"1970-01-01 00:00:00","endTime":"2999-12-31 23:59:59"}],"highlights":"citycode","statistics":null,"unionCondition":null,"accuracy":"","noParticiple":"0","searchRange":null,"isBusiness":"1"}',
            'httpheader' => [
                'Accept: application/json, text/javascript, */*; q=0.01',
                'Accept-Encoding: gzip, deflate, br, zstd',
                'Accept-Language: zh-CN,zh;q=0.9,en;q=0.8,en-GB;q=0.7,en-US;q=0.6',
                'Cache-Control: no-cache',
                'Connection: keep-alive',
                'Content-Length: 549',
                'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
                'Cookie: Secure',
                'Host: cg.aceg.com.cn',
                'Origin: https://cg.aceg.com.cn',
                'Pragma: no-cache',
                'Referer: https://cg.aceg.com.cn/xjcg/002001/bidding.html',
                'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36 Edg/131.0.0.0'
            ]
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
                            $data['bidSiteId'] = 684;
                            $data['bidSiteName'] = '安徽建工集团电子化招采平台';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = 'https://cg.aceg.com.cn'.$v['linkurl'];
                            $data['beginDate'] = $v['webdate'];
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 684 -> 安徽建工集团电子化招采平台');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 684 -> 安徽建工集团电子化招采平台');
        }
        // 693 徐工全球数字化供应链系统
        $url = 'http://xdsc.xcmg.com:8985/api/srmServer/v1/srmBaseNoticeRestful/listByPage?pageIndex=1&pageRows=50';
        $postfieldss = [
            '竞争性采购公告' => '{"pageIndex":1,"pageRows":50,"noticeType":"TENDER_ANN","status":"PUBLISHED"}',
            '询比价公告' => '{"pageIndex":1,"pageRows":50,"noticeType":"INQUIRY_ANN","status":"PUBLISHED"}'
        ];
        foreach ($postfieldss as $index => $postfields) {
            $res = global_curl($url, [
                'post' => 1,
                'postfields' => $postfields
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
                                $data['bidSiteId'] = 693;
                                $data['bidSiteName'] = '徐工全球数字化供应链系统';
                                $data['bidTitle'] = $v['noticeTitle'];
                                $data['bidUrl'] = 'http://xdsc.xcmg.com:8985/#/particulars?noticeId='.$v['noticeId'];
                                $data['beginDate'] = $v['creationDate'];
                                $data['inDate'] = date('Y-m-d H:i:s');
                                $final_arr[] = $data;
                            }
                        }
                    }
                } else {
                    log_message('error', '招标采集：未匹配到数据 693 -> 徐工全球数字化供应链系统 -> '.$index);
                }
            } else {
                log_message('error', '招标采集：未获取到网页 693 -> 徐工全球数字化供应链系统 -> '.$index);
            }
        }
        // 911 华润守正采购交易平台 【特殊通过浏览器刷新插件+自定义js获取数据后发送到本系统api并存数据库，每次采集从数据库拿】
        $res = $this->db->table('bid_temp')->select('res')->where('site', '华润守正采购交易平台招标采购')->get()->getRowArray()['res'] ?? '';
        if($res) {
            if (strpos($res, '</totalCount><data>') !== false) {
                $res_temp = [];
                preg_match('#</totalCount><data>(.*)</data><totalPage>#isU', $res, $res_temp);
                $preg = '#<data>.*<title>(.*)</title>.*<url>(.*)</url>.*<publishDate>(.*)</publishDate>.*</data>#isU';
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
                                $data['bidSiteId'] = 911;
                                $data['bidSiteName'] = '华润守正采购交易平台';
                                $data['bidTitle'] = $v['title'];
                                $data['bidUrl'] = strpos($v['url'], 'http') === 0 ? $v['url'] : ('https://www.szecp.com.cn'.str_replace('..', '', $v['url']));
                                $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                                $data['inDate'] = date('Y-m-d H:i:s');
                                $final_arr[] = $data;
                            }
                        }
                    }
                } else {
                    log_message('error', '招标采集：未匹配到数据 911 -> 华润守正采购交易平台 -> 招标采购');
                }
            } else {
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
                                $data['bidSiteId'] = 911;
                                $data['bidSiteName'] = '华润守正采购交易平台';
                                $data['bidTitle'] = $v['title'];
                                $data['bidUrl'] = strpos($v['url'], 'http') === 0 ? $v['url'] : ('https://www.szecp.com.cn' . str_replace('..', '', $v['url']));
                                $data['beginDate'] = $v['publishDate'];
                                $data['inDate'] = date('Y-m-d H:i:s');
                                $final_arr[] = $data;
                            }
                        }
                    }
                } else {
                    log_message('error', '招标采集：未匹配到数据 911 -> 华润守正采购交易平台 -> 招标采购');
                }
            }
        } else {
            log_message('error', '招标采集：未获取到网页 911 -> 华润守正采购交易平台 -> 招标采购');
        }
        $res = $this->db->table('bid_temp')->select('res')->where('site', '华润守正采购交易平台非招标采购')->get()->getRowArray()['res'] ?? '';
        if($res) {
            if (strpos($res, '</totalCount><data>') !== false) {
                $res_temp = [];
                preg_match('#</totalCount><data>(.*)<totalPage>#isU', $res, $res_temp);
                $preg = '#<data>.*<title>(.*)</title>.*<url>(.*)</url>.*<publishDate>(.*)</publishDate>.*</data>#isU';
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
                                $data['bidSiteId'] = 911;
                                $data['bidSiteName'] = '华润守正采购交易平台';
                                $data['bidTitle'] = $v['title'];
                                $data['bidUrl'] = strpos($v['url'], 'http') === 0 ? $v['url'] : ('https://www.szecp.com.cn'.str_replace('..', '', $v['url']));
                                $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                                $data['inDate'] = date('Y-m-d H:i:s');
                                $final_arr[] = $data;
                            }
                        }
                    }
                } else {
                    log_message('error', '招标采集：未匹配到数据 911 -> 华润守正采购交易平台 -> 非招标采购');
                }
            } else {
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
                                $data['bidSiteId'] = 911;
                                $data['bidSiteName'] = '华润守正采购交易平台';
                                $data['bidTitle'] = $v['title'];
                                $data['bidUrl'] = strpos($v['url'], 'http') === 0 ? $v['url'] : ('https://www.szecp.com.cn' . str_replace('..', '', $v['url']));
                                $data['beginDate'] = $v['publishDate'];
                                $data['inDate'] = date('Y-m-d H:i:s');
                                $final_arr[] = $data;
                            }
                        }
                    }
                } else {
                    log_message('error', '招标采集：未匹配到数据 911 -> 华润守正采购交易平台 -> 非招标采购');
                }
            }
        } else {
            log_message('error', '招标采集：未获取到网页 911 -> 华润守正采购交易平台 -> 非招标采购');
        }
        // 1145 中通服供应链股份有限公司 【特殊通过浏览器刷新插件+自定义js获取数据后发送到本系统api并存数据库，每次采集从数据库拿】
        $res = $this->db->table('bid_temp')->select('res')->where('site', '中通服供应链股份有限公司')->get()->getRowArray()['res'] ?? '';
        if($res) {
            $preg = '#<li.*<a.*title="(.*)".*href="(.*)".*</a>.*发布时间：(.*)</span></p></li>#isU';
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
                            $data['bidSiteId'] = 1145;
                            $data['bidSiteName'] = '中通服供应链股份有限公司';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = strpos($v['url'], 'http') === 0 ? $v['url'] : ('https://zb.chinaccsscm.cn'.$v['url']);
                            $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 1145 -> 中通服供应链股份有限公司');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 1145 -> 中通服供应链股份有限公司');
        }
        // 1282 吉利电子招标平台
        $url = 'https://glzb.geely.com/gpmp/notice/listnotice?pagesize=20&pagenumber=1&publishstatus=2&iflongpro=0&_='.intval(microtime(true) * 1000);
        $res = global_curl($url);
        if ($res !== false) {
            $obj = json_decode($res, true);
            $arr = $obj['data']['items'] ?? [];
            if (is_array($arr) && count($arr) > 0) {
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['pjtnoticename'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['pjtnoticename'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 1282;
                            $data['bidSiteName'] = '吉利电子招标平台';
                            $data['bidTitle'] = $v['pjtnoticename'];
                            $data['bidUrl'] = 'https://glzb.geely.com/#/login/bidList?projectid='.$v['projectid'];
                            $data['beginDate'] = date('Y-m-d H:i:s', intval($v['publishtime'] / 1000));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 1282 -> 吉利电子招标平台');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 1282 -> 吉利电子招标平台');
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
            $this->db->table('option')->where('optionName', 'bid_spider_0_4_status')->update(['optionValue' => 0]);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                log_message('error', '招标采集：招标信息入库失败（招标企业类）事务已回滚！');
            }
        }
    }
}
