<?php

namespace App\Controllers\Api;
use App\Controllers\BaseController;

class SpiderBid03 extends BaseController
{
    /* 招标政府类 按每5分钟执行一次的标准设计 */
    public function index()
    {
        $this->db->table('option')->where('optionName', 'bid_spider_0_3_status')->update(['optionValue' => 1]);
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
        // 3 重庆市政府采购云平台 300条 【特殊通过浏览器刷新插件+自定义js获取数据后发送到本系统api并存数据库，每次采集从数据库拿】
        $res = $this->db->table('bid_temp')->select('res')->where('site', '重庆市政府采购云平台')->get()->getRowArray()['res'] ?? '';
        if($res) {
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
                            $data['bidSiteId'] = 3;
                            $data['bidSiteName'] = '重庆市政府采购云平台';
                            $data['bidTitle'] = $v['noticeName'];
                            $data['bidUrl'] = $v['noticeAddress'];
                            $data['beginDate'] = date('Y-m-d H:i:s', intval($v['publishTime'] / 1000));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 3 -> 重庆市政府采购云平台');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 3 -> 重庆市政府采购云平台');
        }
        // 4 南安市人民政府
        $url = 'http://110.88.153.235:50080/hyweb/otherTrade/getOtherTradeNoticePage.do';
        $res = global_curl($url, [
            'post' => 1,
            'postfields' => '{pageIndex:"1",pageSize:"10",noticeTitle:"",regionCode:"350500",tenderType:"Z",transType :"",pubTime :"",state :"",noticeType :"1"}',
            'httpheader' => [
                'Accept: application/json, text/javascript, */*; q=0.01',
                'Accept-Encoding: gzip, deflate',
                'Accept-Language: zh-CN,zh;q=0.9,en;q=0.8,en-GB;q=0.7,en-US;q=0.6',
                'Cache-Control: no-cache',
                'Connection: keep-alive',
                'Content-Length: 131',
                'Content-Type: application/json;charset=UTF-8',
                'Host: 110.88.153.235:50080',
                'Origin: http://110.88.153.235:50080',
                'Pragma: no-cache',
                'Referer: http://110.88.153.235:50080/hyweb/naebid/nazbcg.do',
                'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36 Edg/131.0.0.0'
            ]
        ]);
        if ($res !== false) {
            $obj = json_decode($res, true);
            $arr = $obj['data']['datalist'] ?? [];
            if (is_array($arr) && count($arr) > 0) {
                foreach ($arr as $k => $v) {
                    if (isset($v['title'])) {
                        $data = [];
                        if (!preg_match('#(' . $in_keywords . ')#', $v['title'])) {
                            continue;
                        } else {
                            if (preg_match('#(' . $out_keywords . ')#', $v['title'])) {
                                continue;
                            } else {
                                $data['bidSiteId'] = 4;
                                $data['bidSiteName'] = '南安市人民政府';
                                $data['bidTitle'] = $v['title'];
                                $data['bidUrl'] = 'http://110.88.153.235:50080/hyweb/naebid/otherBidNew.do?srcNoticeId='.$v['noticeId'].'&noticeType=1';
                                $data['beginDate'] = $v['publishTime'];
                                $data['inDate'] = date('Y-m-d H:i:s');
                                $final_arr[] = $data;
                            }
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 4 -> 南安市人民政府');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 4 -> 南安市人民政府');
        }
        // 11 重庆市公共资源交易网 200条
        $url = 'https://www.cqggzy.com/inteligentsearch/rest/esinteligentsearch/getFullTextDataNew';
        $postfieldsArr = [
            '政府采购' => '{"token":"","pn":0,"rn":100,"sdt":"","edt":"","wd":"","inc_wd":"","exc_wd":"","fields":"","cnum":"001","sort":"{\"istop\":\"0\",\"ordernum\":\"0\",\"webdate\":\"0\",\"newid\":\"0\"}","ssort":"","cl":10000,"terminal":"","highlights":"","statistics":null,"unionCondition":[],"accuracy":"","noParticiple":"1","searchRange":null,"noWd":true,"condition":[{"fieldName":"categorynum","equal":"014005001","notEqual":null,"equalList":null,"notEqualList":["014001018","004002005","014001015","014005014","014008011","014008"],"isLike":true,"likeType":2,"noWd":true}],"time":[{"fieldName":"webdate","startTime":"'.date('Y-m-d', strtotime('-10 days')).' 00:00:00","endTime":"'.date('Y-m-d').' 23:59:59"}]}',
            '阳光采购' =>  '{"token":"","pn":0,"rn":100,"sdt":"","edt":"","wd":"","inc_wd":"","exc_wd":"","fields":"","cnum":"001","sort":"{\"istop\":\"0\",\"ordernum\":\"0\",\"webdate\":\"0\",\"newid\":\"0\"}","ssort":"","cl":10000,"terminal":"","highlights":"","statistics":null,"unionCondition":[],"accuracy":"","noParticiple":"1","searchRange":null,"noWd":true,"condition":[{"fieldName":"categorynum","equal":"014012001","notEqual":null,"equalList":null,"notEqualList":["014001018","004002005","014001015","014005014","014008011","014008"],"isLike":true,"likeType":2,"noWd":true}],"time":[{"fieldName":"webdate","startTime":"'.date('Y-m-d', strtotime('-10 days')).' 00:00:00","endTime":"'.date('Y-m-d').' 23:59:59"}]}'
        ];
        foreach ($postfieldsArr as $index => $postfields) {
            $res = global_curl($url, [
                'post' => 1,
                'postfields' => $postfields
            ]);
            if ($res !== false) {
                $obj = json_decode($res, true);
                if (is_array($obj) && isset($obj['content'])) {
                    $obj = json_decode($obj['content'], true);
                    $arr = $obj['result']['records'] ?? [];
                } else {
                    $arr = [];
                }
                if (is_array($arr) && count($arr) > 0) {
                    foreach ($arr as $k => $v) {
                        $data = [];
                        if (!preg_match('#(' . $in_keywords . ')#', $v['title'])) {
                            continue;
                        } else {
                            if (preg_match('#(' . $out_keywords . ')#', $v['title'])) {
                                continue;
                            } else {
                                $data['bidSiteId'] = 11;
                                $data['bidSiteName'] = '重庆市公共资源交易网';
                                $data['bidTitle'] = $v['title'];
                                $data['bidUrl'] = 'https://www.cqggzy.com/trade/'.($index == '政府采购' ? '014005' : '014012').'/'.$v['infoid'].'?categoryNum=' . $v['categorynum'];
                                $data['beginDate'] = $v['pubinwebdate'];
                                $data['inDate'] = date('Y-m-d H:i:s');
                                $final_arr[] = $data;
                            }
                        }
                    }
                } else {
                    log_message('error', '招标采集：未匹配到数据 11 -> 重庆市公共资源交易网 -> ' . $index);
                }
            } else {
                log_message('error', '招标采集：未获取到网页 11 -> 重庆市公共资源交易网 -> ' . $index);
            }
        }
        // 13 四川省政府采购网 50条 【特殊 需模拟加密生成验证签名sign、nsssjss后传到它的后台进行鉴权】
        $time = intval(microtime(true) * 1000);
        $url = 'https://www.ccgp-sichuan.gov.cn/gpcms/rest/web/v2/info/selectInfoMoreChannel?siteId=94c965cc-c55d-4f92-8469-d5875c68bd04&channel=c5bff13f-21ca-4dac-b158-cb40accd3035&currPage=1&pageSize=50&noticeType=00101&regionCode=&cityOrArea=&_t='.$time;
        $input = '/gpcms/rest/web/v2/info/selectInfoMoreChannel$$'.(int)(microtime(true)*1000);
        $publickey = "MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCS2TZDs5+orLYCL5SsJ54+bPCVs1ZQQwP2RoPkFQF2jcT0HnNNT8ZoQgJTrGwNi5QNTBDoHC4oJesAVYe6DoxXS9Nls8WbGE8ZNgOC5tVv1WVjyBw7k2x72C/qjPoyo/kO7TYl6Qnu4jqW/ImLoup/nsJppUznF0YgbyU/dFFNBQIDAQAB";
        $publickey = "-----BEGIN PUBLIC KEY-----" . "\n" . wordwrap($publickey, 64, "\n", true) . "\n" . "-----END PUBLIC KEY-----";
        openssl_public_encrypt($input, $output, $publickey);
        $nsssjss = base64_encode($output);
        $sign = md5(sha1($time.'_/gpcms/rest/web/v2/info/selectInfoMoreChannel_bosssoft_platform_095285'));
        $res = global_curl($url, [
            'httpheader' => [
                'Accept: */*',
                'Accept-Encoding: gzip, deflate, br, zstd',
                'Accept-Language: zh-CN,zh;q=0.9,en;q=0.8,en-GB;q=0.7,en-US;q=0.6',
                'Cache-Control: no-cache',
                'Connection: keep-alive',
                'Host: www.ccgp-sichuan.gov.cn',
                'Pragma: no-cache',
                'Referer: https://www.ccgp-sichuan.gov.cn/',
                'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36 Edg/148.0.0.0',
                'url: /gpcms/rest/web/v2/info/selectInfoMoreChannel',
                'time: ' . $time,
                'sign: ' . $sign,
                'nsssjss: ' . $nsssjss
            ]
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
                            $data['bidSiteId'] = 13;
                            $data['bidSiteName'] = '四川省政府采购网';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = 'https://www.ccgp-sichuan.gov.cn/maincms-web/article?type=notice&id='.$v['id'].'&planId='.$v['planId'];
                            $data['beginDate'] = $v['noticeTime'];
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 13 -> 四川省政府采购网');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 13 -> 四川省政府采购网');
        }
        // 14 湖北省政府采购网 50条 改为采两个分类的列表页
        $urls = [
            '省级' => 'https://www.ccgp-hubei.gov.cn/notice/cggg/pzbgg/index_1.html',
            '市县' => 'https://www.ccgp-hubei.gov.cn/notice/cggg/czbgg/index_1.html'
        ];
        foreach($urls as $index => $url) {
            $res = global_curl($url);
            if ($res !== false) {
                $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
                $res_temp = [];
                preg_match('#<ulclass="news-list-content.*>(.*)</ul>.*</div>#isU', $res, $res_temp);
                $preg = '#<li><a.*href="(.*)".*>\[.*\](.*)</a><span>(.*)</span></li>#isU';
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
                                $data['bidSiteId'] = 14;
                                $data['bidSiteName'] = '湖北省政府采购网';
                                $data['bidTitle'] = $v['title'];
                                $data['bidUrl'] = 'https://www.ccgp-hubei.gov.cn'.$v['url'];
                                $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                                $data['inDate'] = date('Y-m-d H:i:s');
                                $final_arr[] = $data;
                            }
                        }
                    }
                } else {
                    log_message('error', '招标采集：未匹配到数据 14 -> 湖北省政府采购网 -> ' . $index);
                }
            } else {
                log_message('error', '招标采集：未获取到网页 14 -> 湖北省政府采购网 -> ' . $index);
            }
        }
        // 15 湖南省政府采购网 70条
        $url = 'http://www.ccgp-hunan.gov.cn/portal/category'; // 栏目 省级
        $postfieldsArr = [
            '省级' => '{"pageNo":1,"pageSize":50,"categoryCode":"160-945988","_t":1779674051000,"districtCode":["430000"],"isProvince":true}',
            '市级' => '{"pageNo":1,"pageSize":50,"categoryCode":"160-945988","_t":1779674331000,"districtCode":["430000"],"isProvince":false,"isMunicipal":true}',
            '县级' => '{"pageNo":1,"pageSize":50,"categoryCode":"160-945988","_t":1779674579000,"districtCode":["430000"],"isProvince":false,"isMunicipal":false}'
        ];
        foreach ($postfieldsArr as $index => $postfields) {
            $res = global_curl($url, [
                'post' => 1,
                'postfields' => $postfields
            ]);
            if ($res !== false) {
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
                                $data['bidSiteId'] = 15;
                                $data['bidSiteName'] = '湖南省政府采购网';
                                $data['bidTitle'] = $v['title'];
                                $data['bidUrl'] = 'http://www.ccgp-hunan.gov.cn/luban/front/detail?parentId=622707&articleId=' . $v['articleId'];
                                $data['beginDate'] = date('Y-m-d H:i:s', intval($v['publishDate'] / 1000));
                                $data['inDate'] = date('Y-m-d H:i:s');
                                $final_arr[] = $data;
                            }
                        }
                    }
                } else {
                    log_message('error', '招标采集：未匹配到数据 15 -> 湖南省政府采购网 -> ' . $index);
                }
            } else {
                log_message('error', '招标采集：未获取到网页 15 -> 湖南省政府采购网 -> ' . $index);
            }
        }
        // 16 陕西省政府采购网 45条
        $urls = [
            '省级' => 'https://www.ccgp-shaanxi.gov.cn/freecms/rest/v1/notice/selectInfoForIndex.do?&siteId=a7a15d60-de5b-42f2-b35a-7e3efc34e54f&channel=1eb454a2-7ff7-4a3b-b12c-12acc2685bd1&currPage=1&pageSize=11&regionCode=610001&noticeType=001011,001012,001013,001014,001016,001019&cityOrArea=&selectTimeName=noticeTime',
            '市级' => 'https://www.ccgp-shaanxi.gov.cn/freecms/rest/v1/notice/selectInfoForIndex.do?&siteId=a7a15d60-de5b-42f2-b35a-7e3efc34e54f&channel=1eb454a2-7ff7-4a3b-b12c-12acc2685bd1&currPage=1&pageSize=11&regionCode=!610001&noticeType=001011,001012,001013,001014,001016,001019&cityOrArea=&selectTimeName=noticeTime',
            '县级' => 'https://www.ccgp-shaanxi.gov.cn/freecms/rest/v1/notice/selectInfoForIndex.do?&siteId=a7a15d60-de5b-42f2-b35a-7e3efc34e54f&channel=1eb454a2-7ff7-4a3b-b12c-12acc2685bd1&currPage=1&pageSize=11&regionCode=!610001&noticeType=001011,001012,001013,001014,001016,001019&cityOrArea=3&selectTimeName=noticeTime'
        ];
        foreach ($urls as $index => $url) {
            $res = global_curl($url, [
                'header' => 1
            ]);
            if ($res !== false) {
                $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
                if (strpos($res, 'Set-Cookie') !== false) {
                    $cookie_16 = [];
                    preg_match('#Set-Cookie\:(.*);#isU', $res, $cookie_16);
                    $res = global_curl($url, [
                        'httpheader' => [
                            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9',
                            'Accept-Language: zh-CN,zh;q=0.9,en;q=0.8,en-GB;q=0.7,en-US;q=0.6',
                            'Cache-Control: max-age=0',
                            'Connection: keep-alive',
                            'Cookie: ' . $cookie_16[1],
                            'Host: www.ccgp-shaanxi.gov.cn',
                            'Upgrade-Insecure-Requests: 1',
                            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/99.0.4844.74 Safari/537.36 Edg/99.0.1150.46'
                        ]
                    ]);
                } elseif (strpos($res, 'Location:') !== false) {
                    $cookie_16 = [];
                    preg_match('#Location\:(.*)Cache-control#isU', $res, $cookie_16);
                    $url = $cookie_16[1];
                    $res = global_curl($url, [
                        'httpheader' => [
                            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9',
                            'Accept-Language: zh-CN,zh;q=0.9,en;q=0.8,en-GB;q=0.7,en-US;q=0.6',
                            'Cache-Control: max-age=0',
                            'Connection: keep-alive',
                            'Cookie: ',
                            'Host: www.ccgp-shaanxi.gov.cn',
                            'Upgrade-Insecure-Requests: 1',
                            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/99.0.4844.74 Safari/537.36 Edg/99.0.1150.46'
                        ]
                    ]);
                } else {
                    $res = global_curl($url, [
                        'httpheader' => [
                            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9',
                            'Accept-Language: zh-CN,zh;q=0.9,en;q=0.8,en-GB;q=0.7,en-US;q=0.6',
                            'Cache-Control: max-age=0',
                            'Connection: keep-alive',
                            'Cookie: ',
                            'Host: www.ccgp-shaanxi.gov.cn',
                            'Upgrade-Insecure-Requests: 1',
                            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/99.0.4844.74 Safari/537.36 Edg/99.0.1150.46'
                        ]
                    ]);
                }
                $obj = json_decode($res, true);
                $arr = (isset($obj['code']) && $obj['code'] == 200) ? $obj['data'] : null;
                if (is_array($arr)) {
                    foreach ($arr as $k => $v) {
                        $data = [];
                        if (!preg_match('#(' . $in_keywords . ')#', $v['title'])) {
                            continue;
                        } else {
                            if (preg_match('#(' . $out_keywords . ')#', $v['title'])) {
                                continue;
                            } else {
                                $data['bidSiteId'] = 16;
                                $data['bidSiteName'] = '陕西省政府采购网';
                                $data['bidTitle'] = $v['title'];
                                $data['bidUrl'] = 'https://www.ccgp-shaanxi.gov.cn' . $v['pageurl'] . '?noticeType=' . $v['noticeType'] . '&noticeId=' . $v['id'];
                                $data['beginDate'] = $v['noticeTime'];
                                $data['inDate'] = date('Y-m-d H:i:s');
                                $final_arr[] = $data;
                            }
                        }
                    }
                } else {
                    log_message('error', '招标采集：未匹配到数据 16 -> 陕西省政府采购网 -> ' . $index);
                }
            } else {
                log_message('error', '招标采集：未获取到网页 16 -> 陕西省政府采购网 -> ' . $index);
            }
        }
        // 24 安徽省政府采购网 50条
        $url = 'https://www.ccgp-anhui.gov.cn/front/search/category';
        $res = global_curl($url, [
            'post' => 1,
            'postfields' => '{"leaf":"0","categoryCode":"ZcyAnnouncement2","pageSize":"50","pageNo":1}',
            'httpheader' => [
                'Accept: */*',
                'Accept-Language: zh-CN,zh;q=0.9,en;q=0.8,en-GB;q=0.7,en-US;q=0.6',
                'Cache-Control: no-cache',
                'Connection: keep-alive',
                'Content-Length: 73',
                'Content-Type: application/json',
                'Host: www.ccgp-anhui.gov.cn',
                'Origin: http://www.ccgp-anhui.gov.cn',
                'Pragma: no-cache',
                'Referer: http://www.ccgp-anhui.gov.cn/ZcyAnnouncement/ZcyAnnouncement2/index.html',
                'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/96.0.4664.55 Safari/537.36 Edg/96.0.1054.34',
                'X-Requested-With: XMLHttpRequest'
            ]
        ]);
        if ($res !== false) {
            $obj = json_decode($res, true);
            $arr = $obj['hits']['hits'] ?? [];
            if (is_array($arr) && count($arr) > 0) {
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['_source']['title'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['_source']['title'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 24;
                            $data['bidSiteName'] = '安徽省政府采购网';
                            $data['bidTitle'] = $v['_source']['title'];
                            $data['bidUrl'] = 'https://www.ccgp-anhui.gov.cn/luban/detail?articleId=' . $v['_source']['articleId'];
                            $data['beginDate'] = date('Y-m-d H:i:s', intval($v['_source']['publishDate'] / 1000));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 24 -> 安徽省政府采购网');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 24 -> 安徽省政府采购网');
        }
        // 25 贵州省政府采购网
        $url = 'http://www.ccgp-guizhou.gov.cn/portal/category';
        // 招标公告
        $res = global_curl($url, [
            'post' => 1,
<<<<<<< HEAD
            'postfields' => '{"pageNo":1,"pageSize":15,"categoryCode":"ZcyAnnouncement3001","_t":' . intval(microtime(true) * 1000) . '}'
=======
            'postfields' => '{"pageNo":1,"pageSize":100,"categoryCode":"ZcyAnnouncement3001","_t":' . intval(microtime(true) * 1000) . '}'
>>>>>>> 151b415 (更新语言最新版本+最新底层框架后全部差异化代码适配重写)
        ]);
        if ($res !== false) {
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
                            $data['bidSiteId'] = 25;
                            $data['bidSiteName'] = '贵州省政府采购网';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = 'http://www.ccgp-guizhou.gov.cn/site/detail?articleId=' . $v['articleId'];
                            $data['beginDate'] = date('Y-m-d H:i:s', intval($v['publishDate'] / 1000));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 25 -> 贵州省政府采购网 -> 招标公告');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 25 -> 贵州省政府采购网 -> 招标公告');
        }
        // 采购文件需求公示
        $res = global_curl($url, [
            'post' => 1,
<<<<<<< HEAD
            'postfields' => '{"pageNo":1,"pageSize":15,"categoryCode":"ZcyAnnouncement3014","_t":' . intval(microtime(true) * 1000) . '}'
=======
            'postfields' => '{"pageNo":1,"pageSize":100,"categoryCode":"ZcyAnnouncement3014","_t":' . intval(microtime(true) * 1000) . '}'
>>>>>>> 151b415 (更新语言最新版本+最新底层框架后全部差异化代码适配重写)
        ]);
        if ($res !== false) {
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
                            $data['bidSiteId'] = 25;
                            $data['bidSiteName'] = '贵州省政府采购网';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = 'http://www.ccgp-guizhou.gov.cn/site/detail?articleId=' . $v['articleId'];
                            $data['beginDate'] = date('Y-m-d H:i:s', intval($v['publishDate'] / 1000));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 25 -> 贵州省政府采购网 -> 采购文件需求公示');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 25 -> 贵州省政府采购网 -> 采购文件需求公示');
        }
        // 26 江苏省政府采购网 50条
        $urls = [
            '省级' => 'http://www.ccgp-jiangsu.gov.cn/jscms/js_lmapi/api/getArticeData/lists/cggg_2?_=' . time(),
            '市级' => 'http://www.ccgp-jiangsu.gov.cn/jscms/js_lmapi/api/getArticeData/lists/cggg_3?_=' . time(),
            '区县' => 'http://www.ccgp-jiangsu.gov.cn/jscms/js_lmapi/api/getArticeData/lists/cggg_4?_=' . time()
        ];
        foreach ($urls as $index => $url) {
            $res = global_curl($url);
            if ($res !== false) {
                $obj = json_decode($res, true);
                $arr = $obj['result'] ?? [];
                if (is_array($arr) && count($arr) > 0) {
                    foreach ($arr as $k => $v) {
                        $data = [];
                        if (!preg_match('#(' . $in_keywords . ')#', $v['title'])) {
                            continue;
                        } else {
                            if (preg_match('#(' . $out_keywords . ')#', $v['title'])) {
                                continue;
                            } else {
                                $data['bidSiteId'] = 26;
                                $data['bidSiteName'] = '江苏省政府采购网';
                                $data['bidTitle'] = $v['title'];
                                $data['bidUrl'] = 'http://www.ccgp-jiangsu.gov.cn/jiangsu/js_cggg/details.html?gglb=' . $v['ggCode'] . '&ggid=' . $v['id'];
                                $data['beginDate'] = $v['publishDate'] ?? date('Y-m-d H:i:s');
                                $data['inDate'] = date('Y-m-d H:i:s');
                                $final_arr[] = $data;
                            }
                        }
                    }
                } else {
                    log_message('error', '招标采集：未匹配到数据 26 -> 江苏省政府采购网 -> ' . $index);
                }
            } else {
                log_message('error', '招标采集：未获取到网页 26 -> 江苏省政府采购网 -> ' . $index);
            }
        }
        // 29 山东省政府采购网 40条 【特殊每次链接都不一样导致重复入库，增加了针对性的重复判断】
        $url = 'https://www.ccgp-shandong.gov.cn:8087/api/website/site/getListByCode';
        for ($i = 1; $i < 5; $i++) {
            $res = global_curl($url, [
                'post' => 1,
                'postfields' => '{"colCode":"0301,0303","area":"","currentPage":'.$i.',"pageSize":12,"title":"","date":"","unitName":"","homePage":1}'
            ]);
            if ($res !== false) {
                $obj = json_decode($res, true);
                $arr = $obj['data']['data']['records'] ?? [];
                if (is_array($arr) && count($arr) > 0) {
                    $matchData = [];
                    foreach ($arr as $k => $v) {
                        $data = [];
                        if (!preg_match('#(' . $in_keywords . ')#', $v['title'])) {
                            continue;
                        } else {
                            if (preg_match('#(' . $out_keywords . ')#', $v['title'])) {
                                continue;
                            } else {
                                $data['bidSiteId'] = 29;
                                $data['bidSiteName'] = '山东省政府采购网';
                                $data['bidTitle'] = $v['title'];
                                $data['bidUrl'] = 'https://www.ccgp-shandong.gov.cn/detail?id='.$v['id'].'&colCode='.$v['colCode'].'&urlType=site&oldData=0';
                                $data['beginDate'] = $v['date'];
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
                    log_message('error', '招标采集：未匹配到数据 29 -> 山东省政府采购网 -> 第' . $i . '页');
                }
            } else {
                log_message('error', '招标采集：未获取到网页 29 -> 山东省政府采购网 -> 第' . $i . '页');
            }
        }
        // 30 河南省政府采购网 112条
        for ($i = 1; $i < 2; $i++) { // 栏目 省级 采购
            $url = 'https://zfcg.henan.gov.cn/henan/list2?channelCode=0101&pageNo=' . $i . '&pageSize=16&bz=1&gglx=0';
            $res = global_curl($url);
            if ($res !== false) {
                $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
                $preg = '#<li><ahref="/henan/content(.*)">(.*)</a><p><spanclass="Right10">.*<spanclass="Blue">.*</span></span><spanclass="Right10">.*<spanclass="Blue">.*</span></span><spanclass="GrayRight">(.*)</span></p></li>#isU';
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
                                $data['bidSiteId'] = 30;
                                $data['bidSiteName'] = '河南省政府采购网';
                                $data['bidTitle'] = $v['title'];
                                $data['bidUrl'] = 'https://zfcg.henan.gov.cn/henan/content' . $v['url'];
                                $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                                $data['inDate'] = date('Y-m-d H:i:s');
                                $final_arr[] = $data;
                            }
                        }
                    }
                } else {
                    log_message('error', '招标采集：未匹配到数据 30 -> 河南省政府采购网 -> 栏目 -> 省级 -> 采购 -> 循环' . $i . '');
                }
            } else {
                log_message('error', '招标采集：未获取到网页 30 -> 河南省政府采购网 -> 栏目 -> 省级 -> 采购 -> 循环' . $i . '');
            }
        }
        for ($i = 1; $i < 2; $i++) { // 栏目 省级 非政府采购
            $url = 'https://zfcg.henan.gov.cn/henan/list2?channelCode=9101&pageNo=' . $i . '&pageSize=16&bz=1&gglx=0';
            $res = global_curl($url);
            if ($res !== false) {
                $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
                $preg = '#<li><ahref="/henan/content(.*)">(.*)</a><p><spanclass="Right10">.*<spanclass="Blue">.*</span></span><spanclass="Right10">.*<spanclass="Blue">.*</span></span><spanclass="GrayRight">(.*)</span></p></li>#isU';
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
                                $data['bidSiteId'] = 30;
                                $data['bidSiteName'] = '河南省政府采购网';
                                $data['bidTitle'] = $v['title'];
                                $data['bidUrl'] = 'https://zfcg.henan.gov.cn/henan/content' . $v['url'];
                                $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                                $data['inDate'] = date('Y-m-d H:i:s');
                                $final_arr[] = $data;
                            }
                        }
                    }
                } else {
                    log_message('error', '招标采集：未匹配到数据 30 -> 河南省政府采购网 -> 栏目 -> 省级 -> 非政府采购 -> 循环' . $i . '');
                }
            } else {
                log_message('error', '招标采集：未获取到网页 30 -> 河南省政府采购网 -> 栏目 -> 省级 -> 非政府采购 -> 循环' . $i . '');
            }
        }
        for ($i = 1; $i < 2; $i++) { // 栏目 省级 其他
            $url = 'https://zfcg.henan.gov.cn/henan/list2?channelCode=1304&pageNo=' . $i . '&pageSize=16&bz=1&gglx=0';
            $res = global_curl($url);
            if ($res !== false) {
                $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
                $preg = '#<li><ahref="/henan/content(.*)">(.*)</a><p><spanclass="Right10">.*<spanclass="Blue">.*</span></span><spanclass="Right10">.*<spanclass="Blue">.*</span></span><spanclass="GrayRight">(.*)</span></p></li>#isU';
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
                                $data['bidSiteId'] = 30;
                                $data['bidSiteName'] = '河南省政府采购网';
                                $data['bidTitle'] = $v['title'];
                                $data['bidUrl'] = 'https://zfcg.henan.gov.cn/henan/content' . $v['url'];
                                $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                                $data['inDate'] = date('Y-m-d H:i:s');
                                $final_arr[] = $data;
                            }
                        }
                    }
                } else {
                    log_message('error', '招标采集：未匹配到数据 30 -> 河南省政府采购网 -> 栏目 -> 省级 -> 其他 -> 循环' . $i . '');
                }
            } else {
                log_message('error', '招标采集：未获取到网页 30 -> 河南省政府采购网 -> 栏目 -> 省级 -> 其他 -> 循环' . $i . '');
            }
        }
        for ($i = 1; $i < 3; $i++) { // 栏目 市县 采购
            $url = 'https://zfcg.henan.gov.cn/henan/list2?channelCode=0101&pageNo=' . $i . '&pageSize=16&bz=2&gglx=0';
            $res = global_curl($url);
            if ($res !== false) {
                $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
                $preg = '#<li><ahref="/henan/content(.*)">(.*)</a><p><spanclass="Right10">.*<spanclass="Blue">.*</span></span><spanclass="Right10">.*<spanclass="Blue">.*</span></span><spanclass="GrayRight">(.*)</span></p></li>#isU';
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
                                $data['bidSiteId'] = 30;
                                $data['bidSiteName'] = '河南省政府采购网';
                                $data['bidTitle'] = $v['title'];
                                $data['bidUrl'] = 'https://zfcg.henan.gov.cn/henan/content' . $v['url'];
                                $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                                $data['inDate'] = date('Y-m-d H:i:s');
                                $final_arr[] = $data;
                            }
                        }
                    }
                } else {
                    log_message('error', '招标采集：未匹配到数据 30 -> 河南省政府采购网 -> 栏目 -> 市县 -> 采购 -> 循环' . $i . '');
                }
            } else {
                log_message('error', '招标采集：未获取到网页 30 -> 河南省政府采购网 -> 栏目 -> 市县 -> 采购 -> 循环' . $i . '');
            }
        }
        for ($i = 1; $i < 2; $i++) { // 栏目 市县 非政府采购
            $url = 'https://zfcg.henan.gov.cn/henan/list2?channelCode=9101&pageNo=' . $i . '&pageSize=16&bz=2&gglx=0';
            $res = global_curl($url);
            if ($res !== false) {
                $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
                $preg = '#<li><ahref="/henan/content(.*)">(.*)</a><p><spanclass="Right10">.*<spanclass="Blue">.*</span></span><spanclass="Right10">.*<spanclass="Blue">.*</span></span><spanclass="GrayRight">(.*)</span></p></li>#isU';
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
                                $data['bidSiteId'] = 30;
                                $data['bidSiteName'] = '河南省政府采购网';
                                $data['bidTitle'] = $v['title'];
                                $data['bidUrl'] = 'https://zfcg.henan.gov.cn/henan/content' . $v['url'];
                                $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                                $data['inDate'] = date('Y-m-d H:i:s');
                                $final_arr[] = $data;
                            }
                        }
                    }
                } else {
                    log_message('error', '招标采集：未匹配到数据 30 -> 河南省政府采购网 -> 栏目 -> 市县 -> 非政府采购 -> 循环' . $i . '');
                }
            } else {
                log_message('error', '招标采集：未获取到网页 30 -> 河南省政府采购网 -> 栏目 -> 市县 -> 非政府采购 -> 循环' . $i . '');
            }
        }
        for ($i = 1; $i < 2; $i++) { // 栏目 市县 其他
            $url = 'https://zfcg.henan.gov.cn/henan/list2?channelCode=1304&pageNo=' . $i . '&pageSize=16&bz=2&gglx=0';
            $res = global_curl($url);
            if ($res !== false) {
                $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
                $preg = '#<li><ahref="/henan/content(.*)">(.*)</a><p><spanclass="Right10">.*<spanclass="Blue">.*</span></span><spanclass="Right10">.*<spanclass="Blue">.*</span></span><spanclass="GrayRight">(.*)</span></p></li>#isU';
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
                                $data['bidSiteId'] = 30;
                                $data['bidSiteName'] = '河南省政府采购网';
                                $data['bidTitle'] = $v['title'];
                                $data['bidUrl'] = 'https://zfcg.henan.gov.cn/henan/content' . $v['url'];
                                $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                                $data['inDate'] = date('Y-m-d H:i:s');
                                $final_arr[] = $data;
                            }
                        }
                    }
                } else {
                    log_message('error', '招标采集：未匹配到数据 30 -> 河南省政府采购网 -> 栏目 -> 市县 -> 其他 -> 循环' . $i . '');
                }
            } else {
                log_message('error', '招标采集：未获取到网页 30 -> 河南省政府采购网 -> 栏目 -> 市县 -> 其他 -> 循环' . $i . '');
            }
        }
        // 31 广东省政府采购网 20 【特殊 需模拟加密生成nsssjss后传到它的后台进行鉴权】
        $urls = [
            '项目采购-采购公告' => 'https://gdgpo.czt.gd.gov.cn/gpcms/rest/web/v2/info/selectInfoMoreChannel?siteId=cd64e06a-21a7-4620-aebc-0576bab7e07a&channel=fca71be5-fc0c-45db-96af-f513e9abda9d&currPage=1&pageSize=30&noticeType=00101&regionCode=&cityOrArea=&subChannel=false&purchaseManner=&_t=' . intval(microtime(true) * 1000),
            '电子卖场-采购公告' => 'https://gdgpo.czt.gd.gov.cn/gpcms/rest/web/v2/info/selectInfoMoreChannel?siteId=cd64e06a-21a7-4620-aebc-0576bab7e07a&channel=3b49b9ba-48b6-4220-9e8b-eb89f41e9d66&currPage=1&pageSize=30&noticeType=201022,201023,201111,00107D&regionCode=&cityOrArea=&purchaseManner=9&_t=' . intval(microtime(true) * 1000)
        ];
        foreach ($urls as $index => $url) {
            $input = '/freecms/rest/v1/notice/selectInfoForIndex.do$$'.(int)(microtime(true)*1000);
            $publickey = "MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCS2TZDs5+orLYCL5SsJ54+bPCVs1ZQQwP2RoPkFQF2jcT0HnNNT8ZoQgJTrGwNi5QNTBDoHC4oJesAVYe6DoxXS9Nls8WbGE8ZNgOC5tVv1WVjyBw7k2x72C/qjPoyo/kO7TYl6Qnu4jqW/ImLoup/nsJppUznF0YgbyU/dFFNBQIDAQAB";
            $publickey = "-----BEGIN PUBLIC KEY-----" . "\n" . wordwrap($publickey, 64, "\n", true) . "\n" . "-----END PUBLIC KEY-----";
            openssl_public_encrypt($input, $output, $publickey);
            $nsssjss = base64_encode($output);
            $res = global_curl($url, [
                'httpheader' => [
                    'Accept: */*',
                    'Accept-Language: zh-CN,zh;q=0.9,en;q=0.8,en-GB;q=0.7,en-US;q=0.6',
                    'Cache-Control: no-cache',
                    'Connection: keep-alive',
                    'Content-Type: application/json;charset=utf-8',
                    'Host: gdgpo.czt.gd.gov.cn',
                    'Pragma: no-cache',
                    'Referer: https://gdgpo.czt.gd.gov.cn/cms-gd/site/guangdong/index.html',
                    'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/117.0.0.0 Safari/537.36',
                    'nsssjss: '.$nsssjss
                ]
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
                                $data['bidSiteId'] = 31;
                                $data['bidSiteName'] = '广东省政府采购网';
                                $data['bidTitle'] = $v['title'];
                                $data['bidUrl'] = 'https://gdgpo.czt.gd.gov.cn/maincms-web/noticeGd?type=notice&id='.$v['id'].'&channel='.$v['channel'].'&noticeType='.$v['noticeType'].'&openTenderCode='.$v['openTenderCode'].'&channelName=' . $v['channelName'];
                                $data['beginDate'] = $v['noticeTime'];
                                $data['inDate'] = date('Y-m-d H:i:s');
                                $final_arr[] = $data;
                            }
                        }
                    }
                } else {
                    log_message('error', '招标采集：未匹配到数据 31 -> 广东省政府采购网 -> ' . $index);
                }
            } else {
                log_message('error', '招标采集：未获取到网页 31 -> 广东省政府采购网 -> ' . $index);
            }
        }
        // 32 广西省政府采购网 100条
        $url = 'http://www.ccgp-guangxi.gov.cn/front/search/category';
        $res = global_curl($url, [
            'post' => 1,
            'postfields' => '{"categoryCode":"ZcyAnnouncement1","pageSize":100,"pageNo":1}'
        ]);
        if ($res !== false) {
            $obj = json_decode($res, true);
            $arr = $obj['hits']['hits'] ?? [];
            if (is_array($arr) && count($arr) > 0) {
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['_source']['title'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['_source']['title'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 32;
                            $data['bidSiteName'] = '广西省政府采购网';
                            $data['bidTitle'] = $v['_source']['title'];
                            $data['bidUrl'] = 'http://www.ccgp-guangxi.gov.cn/luban/detail?articleId=' . $v['_id'];
                            $data['beginDate'] = date('Y-m-d H:i:s', $v['_source']['publishDate'] / 1000);
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 32 -> 广西省政府采购网');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 32 -> 广西省政府采购网');
        }
        // 44 河南省电子招标投标公共服务平台
        $url = 'https://hnztbkhd.fgw.henan.gov.cn/xxfbcms/search/bulletin.html?searchDate='.date('Y-m-d', strtotime('-3 day')).'&dates=3&categoryId=88&industryName=&area=&status=01&publishMedia=&sourceInfo=&showStatus=&word=&signDate=';
        $res = global_curl($url);
        if ($res !== false) {
            $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
            $preg = '#<tr><tdstyle="text-align:left;"name="imgShow"id="(.*)"><ahref="javascript:urlOpen\(\'(.*)\'\)"title="(.*)">.*</a>.*</tr>#isU';
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
                            $data['bidSiteId'] = 44;
                            $data['bidSiteName'] = '河南省电子招标投标公共服务平台';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = $v['url'];
                            $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 44 -> 河南省电子招标投标公共服务平台');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 44 -> 河南省电子招标投标公共服务平台');
        }
        // 72 宿迁市政府采购网 100条
        $url = 'http://zfcg.sqcz.suqian.gov.cn/pss/jsp/relevantCgggList.jsp'; // 栏目 采购预告
        $res = global_curl($url, [
            'post' => 1,
            'postfields' => 'gglb=cgyg&cityId=sz&pageSize=20&pageNo=1',
            'httpheader' => [
                'Accept: application/json, text/javascript, */*; q=0.01',
                'Accept-Language: zh-CN,zh;q=0.9',
                'Connection: keep-alive',
                'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
                'Host: zfcg.sqcz.suqian.gov.cn',
                'Origin: http://zfcg.sqcz.suqian.gov.cn',
                'User-Agent: Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.25 Safari/537.36 Core/1.70.3756.400 QQBrowser/10.5.4039.400'
            ]
        ]);
        if ($res !== false) {
            $res = trim(trim($res, "\xEF\xBB\xBF"));
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
                            $data['bidSiteId'] = 72;
                            $data['bidSiteName'] = '宿迁市政府采购网';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = 'http://zfcg.sqcz.suqian.gov.cn/' . $v['url'];
                            $data['beginDate'] = $v['createDate'];
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 72 -> 宿迁市政府采购网 -> 栏目 -> 采购预告');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 72 -> 宿迁市政府采购网 -> 栏目 -> 采购预告');
        }
        $url = 'http://zfcg.sqcz.suqian.gov.cn/pss/jsp/relevantCgggList.jsp'; // 栏目 公开招标
        $res = global_curl($url, [
            'post' => 1,
            'postfields' => 'gglb=gkzb&pageSize=20&pageNo=1',
            'httpheader' => [
                'Accept: application/json, text/javascript, */*; q=0.01',
                'Accept-Language: zh-CN,zh;q=0.9',
                'Connection: keep-alive',
                'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
                'Host: zfcg.sqcz.suqian.gov.cn',
                'Origin: http://zfcg.sqcz.suqian.gov.cn',
                'User-Agent: Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.25 Safari/537.36 Core/1.70.3756.400 QQBrowser/10.5.4039.400'
            ]
        ]);
        if ($res !== false) {
            $res = trim(trim($res, "\xEF\xBB\xBF"));
            $obj = json_decode($res, true);
            $arr = $obj['data']['list'] ?? [];
            if (is_array($arr) && count($arr) > 0) {
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['bidTitle'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['bidTitle'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 72;
                            $data['bidSiteName'] = '宿迁市政府采购网';
                            $data['bidTitle'] = $v['bidTitle'];
                            $data['bidUrl'] = 'http://zfcg.sqcz.suqian.gov.cn/zfcgCggg/ggDetail.html?gglb=gkzb&ggid=' . $v['id'];
                            $data['beginDate'] = $v['createDate'];
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 72 -> 宿迁市政府采购网 -> 栏目 -> 公开招标');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 72 -> 宿迁市政府采购网 -> 栏目 -> 公开招标');
        }
        $url = 'http://zfcg.sqcz.suqian.gov.cn/pss/jsp/relevantCgggList.jsp'; // 栏目 竞争性谈判
        $res = global_curl($url, [
            'post' => 1,
            'postfields' => 'gglb=jztp&pageSize=20&pageNo=1',
            'httpheader' => [
                'Accept: application/json, text/javascript, */*; q=0.01',
                'Accept-Language: zh-CN,zh;q=0.9',
                'Connection: keep-alive',
                'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
                'Host: zfcg.sqcz.suqian.gov.cn',
                'Origin: http://zfcg.sqcz.suqian.gov.cn',
                'User-Agent: Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.25 Safari/537.36 Core/1.70.3756.400 QQBrowser/10.5.4039.400'
            ]
        ]);
        if ($res !== false) {
            $res = trim(trim($res, "\xEF\xBB\xBF"));
            $obj = json_decode($res, true);
            $arr = $obj['data']['list'] ?? [];
            if (is_array($arr) && count($arr) > 0) {
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['bidTitle'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['bidTitle'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 72;
                            $data['bidSiteName'] = '宿迁市政府采购网';
                            $data['bidTitle'] = $v['bidTitle'];
                            $data['bidUrl'] = 'http://zfcg.sqcz.suqian.gov.cn/zfcgCggg/ggDetail.html?gglb=jztp&ggid=' . $v['id'];
                            $data['beginDate'] = $v['createDate'];
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 72 -> 宿迁市政府采购网 -> 栏目 -> 竞争性谈判');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 72 -> 宿迁市政府采购网 -> 栏目 -> 竞争性谈判');
        }
        $url = 'http://zfcg.sqcz.suqian.gov.cn/pss/jsp/relevantCgggList.jsp'; // 栏目 竞争性磋商
        $res = global_curl($url, [
            'post' => 1,
            'postfields' => 'gglb=jzcs&pageSize=20&pageNo=1',
            'httpheader' => [
                'Accept: application/json, text/javascript, */*; q=0.01',
                'Accept-Language: zh-CN,zh;q=0.9',
                'Connection: keep-alive',
                'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
                'Host: zfcg.sqcz.suqian.gov.cn',
                'Origin: http://zfcg.sqcz.suqian.gov.cn',
                'User-Agent: Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.25 Safari/537.36 Core/1.70.3756.400 QQBrowser/10.5.4039.400'
            ]
        ]);
        if ($res !== false) {
            $res = trim(trim($res, "\xEF\xBB\xBF"));
            $obj = json_decode($res, true);
            $arr = $obj['data']['list'] ?? [];
            if (is_array($arr) && count($arr) > 0) {
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['bidTitle'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['bidTitle'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 72;
                            $data['bidSiteName'] = '宿迁市政府采购网';
                            $data['bidTitle'] = $v['bidTitle'];
                            $data['bidUrl'] = 'http://zfcg.sqcz.suqian.gov.cn/zfcgCggg/ggDetail.html?gglb=jzcs&ggid=' . $v['id'];
                            $data['beginDate'] = $v['createDate'];
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 72 -> 宿迁市政府采购网 -> 栏目 -> 竞争性磋商');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 72 -> 宿迁市政府采购网 -> 栏目 -> 竞争性磋商');
        }
        $url = 'http://zfcg.sqcz.suqian.gov.cn/pss/jsp/relevantCgggList.jsp'; // 栏目 询价公告
        $res = global_curl($url, [
            'post' => 1,
            'postfields' => 'gglb=xjgg&pageSize=20&pageNo=1',
            'httpheader' => [
                'Accept: application/json, text/javascript, */*; q=0.01',
                'Accept-Language: zh-CN,zh;q=0.9',
                'Connection: keep-alive',
                'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
                'Host: zfcg.sqcz.suqian.gov.cn',
                'Origin: http://zfcg.sqcz.suqian.gov.cn',
                'User-Agent: Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.25 Safari/537.36 Core/1.70.3756.400 QQBrowser/10.5.4039.400'
            ]
        ]);
        if ($res !== false) {
            $res = trim(trim($res, "\xEF\xBB\xBF"));
            $obj = json_decode($res, true);
            $arr = $obj['data']['list'] ?? [];
            if (is_array($arr) && count($arr) > 0) {
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['bidTitle'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['bidTitle'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 72;
                            $data['bidSiteName'] = '宿迁市政府采购网';
                            $data['bidTitle'] = $v['bidTitle'];
                            $data['bidUrl'] = 'http://zfcg.sqcz.suqian.gov.cn/zfcgCggg/ggDetail.html?gglb=xjgg&ggid=' . $v['id'];
                            $data['beginDate'] = $v['createDate'];
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 72 -> 宿迁市政府采购网 -> 栏目 -> 询价公告');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 72 -> 宿迁市政府采购网 -> 栏目 -> 询价公告');
        }
        // 77 苏州市政府采购 60条
        $url = 'https://czju.suzhou.gov.cn/zfcg/content/cpContents.action';
        $res = global_curl($url, [
            'post' => 1,
            'postfields' => 'type=A&title=&choose=&projectType=&zbCode=&appcode=&isjz=false&page=1&rows=30',
            'httpheader' => [
                'Accept: application/json, text/javascript, */*; q=0.01',
                'Accept-Language: zh-CN,zh;q=0.9,en;q=0.8,en-GB;q=0.7,en-US;q=0.6',
                'Cache-Control: no-cache',
                'Connection: keep-alive',
                'Content-Length: 77',
                'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
                'Host: czju.suzhou.gov.cn',
                'Origin: https://czju.suzhou.gov.cn',
                'Pragma: no-cache',
                'Referer: https://czju.suzhou.gov.cn/zfcg/html/content.shtml?type=A&title=&choose=&projectType=&zbCode=&appcode=',
                'sec-ch-ua: "Chromium";v="104", " Not A;Brand";v="99", "Microsoft Edge";v="104"',
                'sec-ch-ua-mobile: ?0',
                'sec-ch-ua-platform: "Windows"',
                'Sec-Fetch-Dest: empty',
                'Sec-Fetch-Mode: cors',
                'Sec-Fetch-Site: same-origin',
                'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/104.0.5112.81 Safari/537.36 Edg/104.0.1293.54',
                'X-Requested-With: XMLHttpRequest'
            ]
        ]);
        if ($res !== false) {
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
                            $data['bidSiteId'] = 77;
                            $data['bidSiteName'] = '苏州市政府采购';
                            $data['bidTitle'] = $v['TITLE'];
                            $data['bidUrl'] = 'https://czju.suzhou.gov.cn/zfcg/html/project/' . $v['ID'] . '.shtml';
                            $data['beginDate'] = $v['RELEASETIME'];
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 77 -> 苏州市政府采购 -> 循环 ' . $i . '');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 77 -> 苏州市政府采购 -> 循环 ' . $i . '');
        }
        // 84 青岛市政府采购网 30条 【特殊每次链接都不一样导致重复入库，增加了针对性的重复判断】
        $url = 'http://zfcg.qingdao.gov.cn:58060/api/siteservice/free/qd/site-info/page'; // 栏目 采购公告 市级
        $res = global_curl($url, [
            'post' => 1,
            'postfields' => '{"subject":null,"page":1,"area":"","limit":15,"colCode":"0303","colCodes":null,"sort":"-pdate","areaType":"city","pdate":null,"pdates":["",""],"unitName":null,"projectCode":null,"projectName":null,"agentName":null,"pdateType":null,"kindOf":null,"projectType":null}',
            'httpheader' => [
                'Accept: application/json, text/plain, */*',
                'Accept-Encoding: gzip, deflate',
                'Accept-Language: zh-CN,zh;q=0.9,en;q=0.8,en-GB;q=0.7,en-US;q=0.6',
                'Cache-Control: no-cache',
                'Connection: keep-alive',
                'Content-Length: 264',
                'Content-Type: application/json',
                'Host: zfcg.qingdao.gov.cn:58060',
                'Origin: http://www.ccgp-qingdao.gov.cn',
                'Pragma: no-cache',
                'Referer: http://www.ccgp-qingdao.gov.cn/',
                'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36 Edg/151.0.0.0'
            ]
        ]);
        if ($res !== false) {
            $obj = json_decode($res, true);
            $arr = $obj['data']['data']['records'] ?? [];
            if (is_array($arr) && count($arr) > 0) {
                $matchData = [];
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['subject'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['subject'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 84;
                            $data['bidSiteName'] = '青岛市政府采购网';
                            $data['bidTitle'] = $v['subject'];
                            $data['bidUrl'] = 'http://www.ccgp-qingdao.gov.cn/qdsite/#/read?id='.$v['id'];
                            $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['pdate']));
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
                log_message('error', '招标采集：未匹配到数据 84 -> 青岛市政府采购网 -> 栏目 -> 采购公告 市级');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 84 -> 青岛市政府采购网 -> 栏目 -> 采购公告 市级');
        }
        $url = 'http://zfcg.qingdao.gov.cn:58060/api/siteservice/free/qd/site-info/page'; // 栏目 采购公告 县级
        $res = global_curl($url, [
            'post' => 1,
            'postfields' => '{"subject":null,"page":1,"area":"","limit":15,"colCode":"0303","colCodes":null,"sort":"-pdate","areaType":"county","pdate":null,"pdates":["",""],"unitName":null,"projectCode":null,"projectName":null,"agentName":null,"pdateType":null,"kindOf":null,"projectType":null}',
            'httpheader' => [
                'Accept: application/json, text/plain, */*',
                'Accept-Encoding: gzip, deflate',
                'Accept-Language: zh-CN,zh;q=0.9,en;q=0.8,en-GB;q=0.7,en-US;q=0.6',
                'Cache-Control: no-cache',
                'Connection: keep-alive',
                'Content-Length: 266',
                'Content-Type: application/json',
                'Host: zfcg.qingdao.gov.cn:58060',
                'Origin: http://www.ccgp-qingdao.gov.cn',
                'Pragma: no-cache',
                'Referer: http://www.ccgp-qingdao.gov.cn/',
                'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36 Edg/151.0.0.0'
            ]
        ]);
        if ($res !== false) {
            $obj = json_decode($res, true);
            $arr = $obj['data']['data']['records'] ?? [];
            if (is_array($arr) && count($arr) > 0) {
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['subject'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['subject'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 84;
                            $data['bidSiteName'] = '青岛市政府采购网';
                            $data['bidTitle'] = $v['subject'];
                            $data['bidUrl'] = 'http://www.ccgp-qingdao.gov.cn/qdsite/#/read?id='.$v['id'];
                            $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['pdate']));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 84 -> 青岛市政府采购网 -> 栏目 -> 采购公告 县级');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 84 -> 青岛市政府采购网 -> 栏目 -> 采购公告 县级');
        }
        // 131 安庆市公共资源交易中心
        $url = 'https://aqggzy.anqing.gov.cn/inteligentsearch/rest/esinteligentsearch/getFullTextDataNew';
        $res = global_curl($url, [
            'post' => 1,
            'postfields' => '{"token":"","pn":0,"rn":50,"sdt":"","edt":"","wd":"%20","inc_wd":"","exc_wd":"","fields":"title","cnum":"002","sort":"{\"webdate\":\"0\"}","ssort":"title","cl":200,"terminal":"","condition":[{"fieldName":"categorynum","equal":"003002002","notEqual":null,"equalList":null,"notEqualList":null,"isLike":true,"likeType":2}],"time":[{"fieldName":"webdate","startTime":"' . date('Y-m-d', strtotime('-10 days')) . ' 00:00:00","endTime":"2999-12-31 23:59:59"}],"highlights":"citycode","statistics":null,"unionCondition":null,"accuracy":"","noParticiple":"0","searchRange":null,"isBusiness":"1"}',
            'httpheader' => [
                'Accept: application/json, text/javascript, */*; q=0.01',
                'Accept-Language: zh-CN,zh;q=0.9,en;q=0.8,en-GB;q=0.7,en-US;q=0.6',
                'Cache-Control: no-cache',
                'Connection: keep-alive',
                'Content-Length: 552',
                'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
                'Host: aqggzy.anqing.gov.cn',
                'Origin: http://aqggzy.anqing.gov.cn',
                'Pragma: no-cache',
                'Referer: http://aqggzy.anqing.gov.cn/jyxx/003002/construction.html',
                'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/92.0.4515.159 Safari/537.36 Edg/92.0.902.84',
                'X-Requested-With: XMLHttpRequest'
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
                            $data['bidSiteId'] = 131;
                            $data['bidSiteName'] = '安庆市公共资源交易中心';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = 'https://aqggzy.anqing.gov.cn/tiaozhuan.html?infoid=' . $v['linkurl'] . '&categorynum=' . $v['categorynum'];
                            $data['beginDate'] = $v['webdate'];
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 131 -> 安庆市公共资源交易中心');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 131 -> 安庆市公共资源交易中心');
        }
        // 146 金沙县人民政府
        $url = 'https://www.gzjinsha.gov.cn/ztzl/jsxczyjsjsgjfgk_5901439/sgjfgk/';
        $res = global_curl($url);
        if ($res !== false) {
            $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
            $res_temp = [];
            preg_match('#<h1><span>采购公告</span></h1><ul>(.*)</ul>#isU', $res, $res_temp);
            $preg = '#<li><a.*title="(.*)"href="(.*)">.*</a><span>(.*)</span></li>#isU';
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
                            $data['bidSiteId'] = 146;
                            $data['bidSiteName'] = '金沙县人民政府';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = $v['url'];
                            $data['beginDate'] = $v['beginDate'];
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 146 -> 金沙县人民政府');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 146 -> 金沙县人民政府');
        }
        // 213 军队采购网
        for ($i = 1; $i <= 10; $i++) {
            $url = 'https://www.plap.mil.cn/freecms/rest/v1/notice/selectInfoMoreChannel.do?&siteId=404bb030-5be9-4070-85bd-c94b1473e8de&channel=c5bff13f-21ca-4dac-b158-cb40accd3035&currPage='.$i.'&pageSize=20&noticeType=&regionCode=&purchaseManner=&title=&openTenderCode=&operationStartTime=&operationEndTime=&selectTimeName=&cityOrArea=&purchaseNature=1&punishType=';
            $res = global_curl($url, [
                'httpheader' => [
                    'Accept: */*',
                    'Accept-Language: zh-CN,zh;q=0.9,en;q=0.8,en-GB;q=0.7,en-US;q=0.6',
                    'Cache-Control: no-cache',
                    'Connection: keep-alive',
                    'Content-Type: application/json;charset=utf-8',
                    'Host: www.plap.mil.cn',
                    'Pragma: no-cache',
                    'Referer: https://www.plap.mil.cn',
                    'Sec-Fetch-Dest: empty',
                    'Sec-Fetch-Mode: cors',
                    'Sec-Fetch-Site: same-origin',
                    'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/103.0.5060.53 Safari/537.36 Edg/103.0.1264.37'
                ]
            ]);
            if ($res !== false) {
                if (strpos($res, 'HTTP状态 404 - 未找到') !== false) {
                    $res = global_curl($url, [
                        'httpheader' => [
                            'Accept: */*',
                            'Accept-Language: zh-CN,zh;q=0.9,en;q=0.8,en-GB;q=0.7,en-US;q=0.6',
                            'Cache-Control: no-cache',
                            'Connection: keep-alive',
                            'Content-Type: application/json;charset=utf-8',
                            'Host: www.plap.mil.cn',
                            'Pragma: no-cache',
                            'Referer: https://www.plap.mil.cn',
                            'Sec-Fetch-Dest: empty',
                            'Sec-Fetch-Mode: cors',
                            'Sec-Fetch-Site: same-origin',
                            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/103.0.5060.53 Safari/537.36 Edg/103.0.1264.37'
                        ]
                    ]);
                }
                if (strpos($res, 'HTTP状态 404 - 未找到') !== false) {
                    $res = global_curl($url, [
                        'httpheader' => [
                            'Accept: */*',
                            'Accept-Language: zh-CN,zh;q=0.9,en;q=0.8,en-GB;q=0.7,en-US;q=0.6',
                            'Cache-Control: no-cache',
                            'Connection: keep-alive',
                            'Content-Type: application/json;charset=utf-8',
                            'Host: www.plap.mil.cn',
                            'Pragma: no-cache',
                            'Referer: https://www.plap.mil.cn',
                            'Sec-Fetch-Dest: empty',
                            'Sec-Fetch-Mode: cors',
                            'Sec-Fetch-Site: same-origin',
                            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/103.0.5060.53 Safari/537.36 Edg/103.0.1264.37'
                        ]
                    ]);
                }
                if (strpos($res, 'HTTP状态 404 - 未找到') !== false) {
                    $res = global_curl($url, [
                        'httpheader' => [
                            'Accept: */*',
                            'Accept-Language: zh-CN,zh;q=0.9,en;q=0.8,en-GB;q=0.7,en-US;q=0.6',
                            'Cache-Control: no-cache',
                            'Connection: keep-alive',
                            'Content-Type: application/json;charset=utf-8',
                            'Host: www.plap.mil.cn',
                            'Pragma: no-cache',
                            'Referer: https://www.plap.mil.cn',
                            'Sec-Fetch-Dest: empty',
                            'Sec-Fetch-Mode: cors',
                            'Sec-Fetch-Site: same-origin',
                            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/103.0.5060.53 Safari/537.36 Edg/103.0.1264.37'
                        ]
                    ]);
                }
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
                                $data['bidSiteId'] = 213;
                                $data['bidSiteName'] = '军队采购网';
                                $data['bidTitle'] = $v['title'];
                                $data['bidUrl'] = 'https://www.plap.mil.cn' . $v['pageurl'];
                                $data['beginDate'] = $v['noticeTime'];
                                $data['inDate'] = date('Y-m-d H:i:s');
                                $final_arr[] = $data;
                            }
                        }
                    }
                } else {
                    log_message('error', '招标采集：未匹配到数据 213 -> 军队采购网 第 ' .$i.' 页');
                }
            } else {
                log_message('error', '招标采集：未获取到网页 213 -> 军队采购网 第 ' .$i.' 页');
            }
        }
        // 226 甘肃经济信息网
        $urls = [
            '公开招标' => 'https://www.gsei.com.cn/html/1336/',
            '竞争性磋商' => 'https://www.gsei.com.cn/html/1662/',
            '其他公告' => 'https://www.gsei.com.cn/html/1663/'
        ];
        foreach ($urls as $index => $url) {
            $res = global_curl($url);
            if ($res !== false) {
                $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
                $preg = '#<li><spanclass=".*">(.*)</span><a.*href="(.*)".*title="(.*)">.*</a></li>#isU';
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
                                $data['bidSiteId'] = 226;
                                $data['bidSiteName'] = '甘肃经济信息网';
                                $data['bidTitle'] = $v['title'];
                                $data['bidUrl'] = 'https://www.gsei.com.cn' . $v['url'];
                                $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                                $data['inDate'] = date('Y-m-d H:i:s');
                                $final_arr[] = $data;
                            }
                        }
                    }
                } else {
                    log_message('error', '招标采集：未匹配到数据 226 -> 甘肃经济信息网 -> ' . $index);
                }
            } else {
                log_message('error', '招标采集：未获取到网页 226 -> 甘肃经济信息网 -> ' . $index);
            }
        }
        // 253 山东省公共资源交易中心
        $url = 'https://ggzyjyzx.shandong.gov.cn/col/col209868/index.html';
        $res = global_curl($url);
        if ($res !== false) {
            $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
            $preg = '#<record>.*<liclass="clearfix"><ahref="(.*)".*title="(.*)">.*</a><span>(.*)</span></li>.*</record>#isU';
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
                            $data['bidSiteId'] = 253;
                            $data['bidSiteName'] = '山东省公共资源交易中心';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = $v['url'];
                            $data['beginDate'] = date('Y-m-d H:i:s', strtotime(str_replace('/', '-', $v['beginDate'])));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 253 -> 山东省公共资源交易中心');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 253 -> 山东省公共资源交易中心');
        }
        // 254 河北省政府采购网上商城
        $url = 'http://www.hebzfcgwssc.com/Mall/HeBei/jingjiaInfo.aspx';
        $res = global_curl($url);
        if ($res !== false) {
            $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
            $preg = "#<liclass='mr-item'>.*<a.*href='(.*)'.*>(.*)<span.*>.*</span></a></div></li>#isU";
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
                            $data['bidSiteId'] = 254;
                            $data['bidSiteName'] = '河北省政府采购网上商城';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = 'http://www.hebzfcgwssc.com/Mall/HeBei/'.$v['url'];
                            $data['beginDate'] = $v['beginDate'];
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 254 -> 河北省政府采购网上商城');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 254 -> 河北省政府采购网上商城');
        }
        // 272 广安市公共资源交易网
        $urls = [
            '政府采购' => 'https://gasggzy.cn/jyxx/002002/trans_info.html',
            '其他交易公告' => 'https://gasggzy.cn/jyxx/002006/trans_info.html'
        ];
        foreach ($urls as $index => $url) {
            $res = global_curl($url);
            if ($res !== false) {
                $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
                $res_temp = [];
                preg_match('#<tbodyid="showList">(.*)</tbody>#isU', $res, $res_temp);
                $preg = '#<tr><td><ahref="(.*)".*"title="(.*)">.*</a></td><td><span.*>(.*)</span></td></tr>#isU';
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
                                $data['bidSiteId'] = 272;
                                $data['bidSiteName'] = '广安市公共资源交易网';
                                $data['bidTitle'] = $v['title'];
                                $data['bidUrl'] = 'https://gasggzy.cn'.$v['url'];
                                $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                                $data['inDate'] = date('Y-m-d H:i:s');
                                $final_arr[] = $data;
                            }
                        }
                    }
                } else {
                    log_message('error', '招标采集：未匹配到数据 272 -> 广安市公共资源交易网 -> ' . $index);
                }
            } else {
                log_message('error', '招标采集：未获取到网页 272 -> 广安市公共资源交易网 -> ' . $index);
            }
        }
        // 273 河北省政府采购网
        $urls = [
            '省本级' => 'https://www.ccgp-hebei.gov.cn/province/cggg/zbgg/',
            '石家庄市' => 'https://www.ccgp-hebei.gov.cn/sjz/sjz/cggg/zbggAAAS/',
            '辛集市' => 'https://www.ccgp-hebei.gov.cn/xjs/xj/cggg/zbggAAAS/',
            '唐山市' => 'https://www.ccgp-hebei.gov.cn/ts/ts/cggg/zbggAAAS/',
            '秦皇岛市' => 'https://www.ccgp-hebei.gov.cn/qhd/qhd/cggg/zbggAAAS/',
            '邯郸市' => 'https://www.ccgp-hebei.gov.cn/hd/hd/cggg/zbggAAAS/',
            '邢台市' => 'https://www.ccgp-hebei.gov.cn/xt/xt/cggg/zbggAAAS/',
            '保定市' => 'https://www.ccgp-hebei.gov.cn/bd/bd/cggg/zbggAAAS/',
            '定州市' => 'https://www.ccgp-hebei.gov.cn/dz/dz/cggg/zbggAAAS/',
            '张家口市' => 'https://www.ccgp-hebei.gov.cn/zjk/zjk/cggg/zbggAAAS/',
            '承德市' => 'https://www.ccgp-hebei.gov.cn/cd/cd/cggg/zbggAAAS/',
            '沧州市' => 'https://www.ccgp-hebei.gov.cn/cz/cz/cggg/zbggAAAS/',
            '廊坊市' => 'https://www.ccgp-hebei.gov.cn/lf/lf/cggg/zbggAAAS/',
            '衡水市' => 'https://www.ccgp-hebei.gov.cn/hs/hs/cggg/zbggAAAS/',
            '雄安新区' => 'https://www.ccgp-hebei.gov.cn/xazq/xaxq_xaxq/cggg/zbggAAAS/'
        ];
        foreach ($urls as $index => $url) {
            sleep(1);
            $res = global_curl($url, [
                'httpheader' => [
                    'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7',
                    'Accept-Encoding: gzip, deflate, br, zstd',
                    'Accept-Language: zh-CN,zh;q=0.9,en;q=0.8,en-GB;q=0.7,en-US;q=0.6',
                    'Cache-Control: no-cache',
                    'Connection: keep-alive',
                    'Host: www.ccgp-hebei.gov.cn',
                    'Pragma: no-cache',
                    'Referer: http://www.ccgp-hebei.gov.cn',
                    'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0'
                ]
            ]);
            if ($res !== false) {
                $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
                $res_temp = [];
                preg_match('#<!--列表start--><div.*><table.*>(.*)</table>#isU', $res, $res_temp);
                if (is_countable($res_temp) && count($res_temp) > 0) {
                    $preg = '#<tr><td.*>.*</td><td.*><ahref="(.*)".*title="(.*)".*>.*</a></td></tr><tr.*>.*发布时间：<span.*>(.*)</span>.*</td></tr>#isU';
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
                                    $data['bidSiteId'] = 273;
                                    $data['bidSiteName'] = '河北省政府采购网';
                                    $data['bidTitle'] = $v['title'];
                                    $data['bidUrl'] = $url . str_replace('./', '', $v['url']);
                                    $data['beginDate'] = $v['beginDate'];
                                    $data['inDate'] = date('Y-m-d H:i:s');
                                    $final_arr[] = $data;
                                }
                            }
                        }
                    } else {
                        log_message('error', '招标采集：未匹配到数据 273 -> 河北省政府采购网 -> ' . $index);
                    }
                } else {
                    preg_match('#<!--静态内容列表--><divclass="contentclearfix"id="staticContent"><divclass="result-list">(.*)<!--静态内容分页-->#isU', $res, $res_temp);
                    $preg = '#<divclass="list-item">.*<a.*href="(.*)".*title="(.*)".*<spanclass="list-item-meta"data-label="发布时间：">(.*)</span>#isU';
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
                                    $data['bidSiteId'] = 273;
                                    $data['bidSiteName'] = '河北省政府采购网';
                                    $data['bidTitle'] = $v['title'];
                                    $data['bidUrl'] = '';
                                    if (strpos($v['url'], '../../../') !== false) {
                                        $urlArr = explode('/', rtrim($url, '/'));
                                        $urlArrLength = count($urlArr);
                                        $jumpNum = substr_count($v['url'], '../');
                                        $remainUrlArrLength = $urlArrLength - $jumpNum;
                                        for ($m = 0; $m < $remainUrlArrLength; $m++) {
                                            if ($m == 1) {
                                                $data['bidUrl'] .= '//';
                                            } else {
                                                $data['bidUrl'] .= $urlArr[$m] . '/';
                                            }
                                        }
                                        $data['bidUrl'] .= str_replace('../', '', $v['url']);
                                    } else {
                                        $data['bidUrl'] = $url . str_replace('./', '', $v['url']);
                                    }
                                    $data['beginDate'] = $v['beginDate'];
                                    $data['inDate'] = date('Y-m-d H:i:s');
                                    $final_arr[] = $data;
                                }
                            }
                        }
                    } else {
                        log_message('error', '招标采集：未匹配到数据 273 -> 河北省政府采购网 -> ' . $index);
                    }
                }
            } else {
                log_message('error', '招标采集：未获取到网页 273 -> 河北省政府采购网 -> ' . $index);
            }
        }
        // 279 盐城市政府采购网 【特殊通过浏览器刷新插件+自定义js获取数据后发送到本系统api并存数据库，每次采集从数据库拿】
        $res = $this->db->table('bid_temp')->select('res')->where('site', '盐城市政府采购网市级采购')->get()->getRowArray()['res'] ?? '';
        if($res) {
            $preg = '#<li.*<a.*title="(.*)".*href="(.*)".*</a><span.*>(.*)</span></li>#isU';
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
                            $data['bidSiteId'] = 279;
                            $data['bidSiteName'] = '盐城市政府采购网';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = $v['url'];
                            $data['beginDate'] = $v['beginDate'];
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 279 -> 盐城市政府采购网市级采购');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 279 -> 盐城市政府采购网市级采购');
        }
        $res = $this->db->table('bid_temp')->select('res')->where('site', '盐城市政府采购网县级采购')->get()->getRowArray()['res'] ?? '';
        if($res) {
            $preg = '#<li.*<a.*title="(.*)".*href="(.*)".*</a><span.*>(.*)</span></li>#isU';
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
                            $data['bidSiteId'] = 279;
                            $data['bidSiteName'] = '盐城市政府采购网';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = $v['url'];
                            $data['beginDate'] = $v['beginDate'];
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 279 -> 盐城市政府采购网县级采购');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 279 -> 盐城市政府采购网县级采购');
        }
        // 297 瓮安县人民政府
        $url = 'http://www.wengan.gov.cn/xw/tzgg/';
        $res = global_curl($url);
        if ($res !== false) {
            $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
            $res_temp = [];
            preg_match('#<ulclass="NewsList">(.*)</ul>#isU', $res, $res_temp);
            $preg = '#<li>.*<a.*href="(.*)"title="(.*)">.*</a><span>(.*)</span></li>#isU';
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
                            $data['bidSiteId'] = 297;
                            $data['bidSiteName'] = '瓮安县人民政府';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = $v['url'];
                            $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 297 -> 瓮安县人民政府');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 297 -> 瓮安县人民政府');
        }
        // 308 马鞍山市教育局
        $url = 'https://sjy.mas.gov.cn/xxfb/ztbgg/index.html';
        $res = global_curl($url);
        if ($res !== false) {
            $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
            $res_temp = [];
            preg_match('#<ulclass="doc_listlist.*>(.*)</ul>#isU', $res, $res_temp);
            $preg = '#<li.*><a.*href="(.*)".*title="(.*)".*><span.*>.*</span></a><span.*>(.*)</span></li>#isU';
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
                            $data['bidSiteId'] = 308;
                            $data['bidSiteName'] = '马鞍山市教育局';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = $v['url'];
                            $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 308 -> 马鞍山市教育局');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 308 -> 马鞍山市教育局');
        }
        // 315 宿迁市教育局
        $url = 'http://jyj.suqian.gov.cn/sjyj/tzgg/list.shtml';
        $res = global_curl($url);
        if ($res !== false) {
            $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
            $preg = '#<liclass="texto"><a.*href="(.*)".*title=\'(.*)\'.*>.*</a><span>\[(.*)\]</span></li>#isU';
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
                            $data['bidSiteId'] = 315;
                            $data['bidSiteName'] = '宿迁市教育局';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = 'http://jyj.suqian.gov.cn'.$v['url'];
                            $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 315 -> 宿迁市教育局');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 315 -> 宿迁市教育局');
        }
        // 320 江西省政府采购电子卖场 【特殊通过浏览器刷新插件+自定义js获取数据后发送到本系统api并存数据库，每次采集从数据库拿】
        $res = $this->db->table('bid_temp')->select('res')->where('site', '江西省政府采购电子卖场')->get()->getRowArray()['res'] ?? '';
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
                            $data['bidSiteId'] = 320;
                            $data['bidSiteName'] = '江西省政府采购电子卖场';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = 'https://www.jxemall.com/luban/bidding/detail?requisitionId='.$v['requisitionId'].'&type='.$v['type'];
                            $data['beginDate'] = date('Y-m-d H:i:s', intval($v['pubTimestamp']) / 1000);
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 320 -> 江西省政府采购电子卖场');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 320 -> 江西省政府采购电子卖场');
        }
        // 322 金坛教育服务
        $url = 'http://www.jtjyfw.net/html/NewList-jtjyfw_108.html';
        $res = global_curl($url, [
            'httpheader' => [
                'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7',
                'Accept-Encoding: gzip, deflate',
                'Accept-Language: zh-CN,zh;q=0.9,en;q=0.8,en-GB;q=0.7,en-US;q=0.6',
                'Cache-Control: no-cache',
                'Connection: keep-alive',
                'Host: www.jtjyfw.net',
                'Pragma: no-cache',
                'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0'
            ]
        ]);
        if ($res !== false) {
            $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
            $preg = '#<divclass="row"onclick="window.open\(\'(.*)\'\)"><h6>(.*)</h6>.*<span>(.*)</span>.*</div>#isU';
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
                            $data['bidSiteId'] = 322;
                            $data['bidSiteName'] = '金坛教育服务';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = 'http://www.jtjyfw.net'.$v['url'];
                            $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 322 -> 金坛教育服务');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 322 -> 金坛教育服务');
        }
        // 323 安徽省公共资源交易集团
        $url = 'https://www.ahggzyjy.com/inteligentsearch/rest/esinteligentsearch/getFullTextDataNew';
        $postfieldsArr = [
            '项目管理有限公司 政府采购 交易公告' => '{"token":"","pn":0,"rn":50,"sdt":"","edt":"","wd":"","inc_wd":"","exc_wd":"","fields":"title","cnum":"hfggzy","sort":"{\"webdate\":\"0\"}","ssort":"title","cl":200,"terminal":"","condition":[{"fieldName":"categorynum","equal":"020001003","notEqual":null,"equalList":null,"notEqualList":null,"isLike":true,"likeType":2},{"fieldName":"projecttype","equal":2,"notEqual":null,"equalList":null,"notEqualList":null,"isLike":true,"likeType":2}],"time":null,"highlights":"","statistics":null,"unionCondition":null,"accuracy":"","noParticiple":"0","searchRange":null,"isBusiness":"1"}',
            '项目管理有限公司 其他交易 交易公告' => '{"token":"","pn":0,"rn":50,"sdt":"","edt":"","wd":"","inc_wd":"","exc_wd":"","fields":"title","cnum":"hfggzy","sort":"{\"webdate\":\"0\"}","ssort":"title","cl":200,"terminal":"","condition":[{"fieldName":"categorynum","equal":"020001003","notEqual":null,"equalList":null,"notEqualList":null,"isLike":true,"likeType":2},{"fieldName":"projecttype","equal":5,"notEqual":null,"equalList":null,"notEqualList":null,"isLike":true,"likeType":2}],"time":null,"highlights":"","statistics":null,"unionCondition":null,"accuracy":"","noParticiple":"0","searchRange":null,"isBusiness":"1"}',
            '项目管理有限公司 政府采购（非进场） 交易公告' => '{"token":"","pn":0,"rn":50,"sdt":"","edt":"","wd":"","inc_wd":"","exc_wd":"","fields":"title","cnum":"hfggzy","sort":"{\"webdate\":\"0\"}","ssort":"title","cl":200,"terminal":"","condition":[{"fieldName":"categorynum","equal":"020001003","notEqual":null,"equalList":null,"notEqualList":null,"isLike":true,"likeType":2},{"fieldName":"projecttype","equal":6,"notEqual":null,"equalList":null,"notEqualList":null,"isLike":true,"likeType":2}],"time":null,"highlights":"","statistics":null,"unionCondition":null,"accuracy":"","noParticiple":"0","searchRange":null,"isBusiness":"1"}',
            '项目管理有限公司 企业采购 交易公告' => '{"token":"","pn":0,"rn":50,"sdt":"","edt":"","wd":"","inc_wd":"","exc_wd":"","fields":"title","cnum":"hfggzy","sort":"{\"webdate\":\"0\"}","ssort":"title","cl":200,"terminal":"","condition":[{"fieldName":"categorynum","equal":"020001003","notEqual":null,"equalList":null,"notEqualList":null,"isLike":true,"likeType":2},{"fieldName":"projecttype","equal":7,"notEqual":null,"equalList":null,"notEqualList":null,"isLike":true,"likeType":2}],"time":null,"highlights":"","statistics":null,"unionCondition":null,"accuracy":"","noParticiple":"0","searchRange":null,"isBusiness":"1"}',
            '安徽省级政府集中采购 政府采购 交易公告' => '{"token":"","pn":0,"rn":50,"sdt":"","edt":"","wd":"","inc_wd":"","exc_wd":"","fields":"title","cnum":"hfggzy","sort":"{\"webdate\":\"0\"}","ssort":"title","cl":200,"terminal":"","condition":[{"fieldName":"categorynum","equal":"020002003","notEqual":null,"equalList":null,"notEqualList":null,"isLike":true,"likeType":2},{"fieldName":"projecttype","equal":2,"notEqual":null,"equalList":null,"notEqualList":null,"isLike":true,"likeType":2}],"time":null,"highlights":"","statistics":null,"unionCondition":null,"accuracy":"","noParticiple":"0","searchRange":null,"isBusiness":"1"}',
            '合肥市级政府集中采购 政府采购 交易公告' => '{"token":"","pn":0,"rn":50,"sdt":"","edt":"","wd":"","inc_wd":"","exc_wd":"","fields":"title","cnum":"hfggzy","sort":"{\"webdate\":\"0\"}","ssort":"title","cl":200,"terminal":"","condition":[{"fieldName":"categorynum","equal":"020003003","notEqual":null,"equalList":null,"notEqualList":null,"isLike":true,"likeType":2},{"fieldName":"projecttype","equal":2,"notEqual":null,"equalList":null,"notEqualList":null,"isLike":true,"likeType":2}],"time":null,"highlights":"","statistics":null,"unionCondition":null,"accuracy":"","noParticiple":"0","searchRange":null,"isBusiness":"1"}',
            '合肥县区交易公司 交易公告' => '{"token":"","pn":0,"rn":50,"sdt":"","edt":"","wd":"","inc_wd":"","exc_wd":"","fields":"title","cnum":"hfggzy","sort":"{\"webdate\":\"0\"}","ssort":"title","cl":200,"terminal":"","condition":[{"fieldName":"categorynum","equal":"020007003","notEqual":null,"equalList":null,"notEqualList":null,"isLike":true,"likeType":2}],"time":null,"highlights":"","statistics":null,"unionCondition":null,"accuracy":"","noParticiple":"0","searchRange":null,"isBusiness":"1"}',
            '其他 其他交易 交易公告' => '{"token":"","pn":0,"rn":50,"sdt":"","edt":"","wd":"","inc_wd":"","exc_wd":"","fields":"title","cnum":"hfggzy","sort":"{\"webdate\":\"0\"}","ssort":"title","cl":200,"terminal":"","condition":[{"fieldName":"categorynum","equal":"020008002","notEqual":null,"equalList":null,"notEqualList":null,"isLike":true,"likeType":2},{"fieldName":"projecttype","equal":5,"notEqual":null,"equalList":null,"notEqualList":null,"isLike":true,"likeType":2}],"time":null,"highlights":"","statistics":null,"unionCondition":null,"accuracy":"","noParticiple":"0","searchRange":null,"isBusiness":"1"}'
        ];
        foreach ($postfieldsArr as $index => $fields) {
            $res = global_curl($url, [
                'post' => 1,
                'postfields' => $fields
            ]);
            if ($res !== false) {
                $obj = json_decode($res, true);
                $obj = json_decode($obj['content'], true);
                $arr = $obj['result']['records'] ?? [];
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
                                $data['bidSiteId'] = 323;
                                $data['bidSiteName'] = '安徽省公共资源交易集团';
                                $data['bidTitle'] = $v['title'];
                                $data['bidUrl'] = 'https://www.ahggzyjy.com'.$v['linkurl'];
                                $data['beginDate'] = $v['webdate'];
                                $data['inDate'] = date('Y-m-d H:i:s');
                                $final_arr[] = $data;
                            }
                        }
                    }
                } else {
                    log_message('error', '招标采集：未匹配到数据 323 -> 安徽省公共资源交易集团 -> ' . $index);
                }
            } else {
                log_message('error', '招标采集：未获取到网页 323 -> 安徽省公共资源交易集团 -> ' . $index);
            }
        }
        // 328 安徽省招标投标信息网
        $url = 'https://www.ahtba.org.cn/site/trade/affiche/pageList';
        $postfieldsArr = [
            '当年 -> 依法招标（采购）项目 -> 政府采购 -> 采购公告' => '{"publishTimeType":"year","tradeType":"01","tradeClassify":"D","afficheType":"D01","regionCode":"","afficheSourceType":"","afficheTitle":"","pageNum":1,"pageSize":200}',
            '当年 -> 依法招标（采购）项目 -> 其他 -> 交易公告' => '{"publishTimeType":"year","tradeType":"01","tradeClassify":"Z","afficheType":"Z01","regionCode":"","afficheSourceType":"","afficheTitle":"","pageNum":1,"pageSize":200}',
            '当年 -> 自愿招标（采购）项目 -> 货物类 -> 采购公告' => '{"publishTimeType":"year","tradeType":"02","tradeClassify":"Y1","afficheType":"Y11","regionCode":"","afficheSourceType":"","afficheTitle":"","pageNum":1,"pageSize":200}'
        ];
        foreach ($postfieldsArr as $index => $postfields) {
            $res = global_curl($url, [
                'post' => 1,
                'postfields' => $postfields
            ]);
            if ($res !== false) {
                $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
                $preg = '#<li>.*<ahref="(.*)".*>(.*)</a>.*</div><divclass="frnums">(.*)</div>.*</li>#isU';
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
                                $data['bidSiteId'] = 328;
                                $data['bidSiteName'] = '安徽省招标投标信息网';
                                $data['bidTitle'] = $v['title'];
                                $data['bidUrl'] = 'https://www.ahtba.org.cn'.$v['url'];
                                $data['beginDate'] = $v['beginDate'];
                                $data['inDate'] = date('Y-m-d H:i:s');
                                $final_arr[] = $data;
                            }
                        }
                    }
                } else {
                    log_message('error', '招标采集：未匹配到数据 328 -> 安徽省招标投标信息网 -> ' . $index);
                }
            } else {
                log_message('error', '招标采集：未获取到网页 328 -> 安徽省招标投标信息网 -> ' . $index);
            }
        }
        // 332 柯桥区人民政府
        $url = 'https://www.kq.gov.cn/api-gateway/jpaas-publish-server/front/page/build/unit?parseType=bulidstatic&webId=2944&tplSetId=N91xjFcPI1PTOEOpzLn5x&pageType=column&tagId=%E5%BD%93%E5%89%8D%E6%A0%8F%E7%9B%AE_list&editType=null&pageId=1499279';
        $res = global_curl($url);
        if ($res !== false) {
            $res = json_decode($res, true);
            $res = $res['data']['html'] ?? '';
            $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
            $preg = '#<li><ahref="(.*)"title="(.*)".*</a><span>(.*)</span></li>#isU';
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
                            $data['bidSiteId'] = 332;
                            $data['bidSiteName'] = '柯桥区人民政府';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = 'https://www.kq.gov.cn'.$v['url'];
                            $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 332 -> 柯桥区人民政府');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 332 -> 柯桥区人民政府');
        }
        // 352 东台市人民政府 【特殊通过浏览器刷新插件+自定义js获取数据后发送到本系统api并存数据库，每次采集从数据库拿】
        $res = $this->db->table('bid_temp')->select('res')->where('site', '东台市人民政府')->get()->getRowArray()['res'] ?? '';
        if($res) {
            $preg = '#<li><ahref="(.*)">(.*)</a><span>(.*)</span></li>#isU';
            $obj = [];
            $arr = [];
            preg_match_all($preg, $res, $obj);
            $count = (is_countable($obj) && count($obj) > 0) ? count($obj[0]) : 0;
            if ($count > 0) {
                for ($j = 0; $j < $count; $j++) {
                    $arr[$j]['url'] = $obj[1][$j];
                    $arr[$j]['title'] = $obj[2][$j];
                    $arr[$j]['beginDate'] = ($obj[3][$j] > date('m-d') ? date('Y', strtotime('-1 year')) : date('Y')) . '-' . $obj[3][$j];
                }
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['title'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['title'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 352;
                            $data['bidSiteName'] = '东台市人民政府';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = 'https://www.dongtai.gov.cn'.$v['url'];
                            $data['beginDate'] = $v['beginDate'];
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 352 -> 东台市人民政府');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 352 -> 东台市人民政府');
        }
        // 360 绍兴市教育局
        $url = 'https://jyj.sx.gov.cn/api-gateway/jpaas-publish-server/front/page/build/unit?parseType=bulidstatic&webId=3011&tplSetId=0OI1wdsajDxJvE4zxf0Fk&pageType=column&tagId=%E5%BD%93%E5%89%8D%E6%A0%8F%E7%9B%AE%E4%BF%A1%E6%81%AF%E5%88%97%E8%A1%A8&editType=null&pageId=1229352954';
        $res = global_curl($url);
        if ($res !== false) {
            $obj = json_decode($res, true);
            $res = $obj['data']['html'] ?? '';
            $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
            $preg = '#<li><ahref="(.*)"title="(.*)".*<spanclass="bt-data-time">\[(.*)\]</span></a></li>#isU';
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
                            $data['bidSiteId'] = 360;
                            $data['bidSiteName'] = '绍兴市教育局';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = strpos($v['url'], 'http') === 0 ? $v['url'] : ('https://jyj.sx.gov.cn'.$v['url']);
                            $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 360 -> 绍兴市教育局');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 360 -> 绍兴市教育局');
        }
        // 388 甘肃省公共资源交易中心
        $url = 'https://pzxx.ggzyjy.gansu.gov.cn/f/newprovince/annogoods/annogoodItem'; //政府采购
        $res = global_curl($url, [
            'post' => 1,
            'postfields' => 'projecttype=D',
            'httpheader' => [
                'accept: */*',
                'accept-encoding: gzip, deflate, br, zstd',
                'accept-language: zh-CN,zh;q=0.9,en;q=0.8,en-GB;q=0.7,en-US;q=0.6',
                'cache-control: no-cache',
                'content-length: 13',
                'content-type: application/x-www-form-urlencoded; charset=UTF-8',
                'origin: https://pzxx.ggzyjy.gansu.gov.cn',
                'pragma: no-cache',
                'priority: u=1, i',
                'referer: https://pzxx.ggzyjy.gansu.gov.cn/',
                'user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36 Edg/131.0.0.0'
            ]
        ]);
        if ($res !== false) {
            $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
            $preg = '#<a.*onclick="loadTender\(\'(.*)\'.*title="(.*)".*</a>.*<i.*>(.*)</i></dd>#isU';
            $obj = [];
            $arr = [];
            preg_match_all($preg, $res, $obj);
            $count = (is_countable($obj) && count($obj) > 0) ? count($obj[0]) : 0;
            if ($count > 0) {
                for ($j = 0; $j < $count; $j++) {
                    $arr[$j]['url'] = '/f/newprovince/tenderproject/' . base64_encode($obj[1][$j]) . '/tenderprojectIndex?area=';
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
                            $data['bidSiteId'] = 388;
                            $data['bidSiteName'] = '甘肃省公共资源交易中心';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = 'https://pzxx.ggzyjy.gansu.gov.cn' . $v['url'];
                            $data['beginDate'] = $v['beginDate'];
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
                log_message('error', '招标采集：未匹配到数据 388 -> 甘肃省公共资源交易中心 -> 政府采购');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 388 -> 甘肃省公共资源交易中心 -> 政府采购');
        }
        $url = 'https://sjfz.ggzyjy.gansu.gov.cn:19002/api/renren-api/sunshinePruchase/getSunshinePruchaseInfo?queryPlatformCode=&purchaseBodyName=&projectCategory=2&pageNo=1&pageSize=50&latestStatusType=1&projectName=&queryListType=1&_t=' . intval(microtime(true) * 1000); // 阳光采购 限额以下政府采购 采购公告
        $res = global_curl($url);
        if ($res !== false) {
            $obj = json_decode($res, true);
            $arr = $obj['data']['list'] ?? [];
            if (is_array($arr) && count($arr) > 0) {
                $matchData = [];
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['projectName'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['projectName'])) {
                            continue;
                        } else {
                            $bidUrlRes = global_curl('https://sjfz.ggzyjy.gansu.gov.cn:19002/api/renren-api/sunshinePruchase/getUrl', [
                                'post' => 1,
                                'postfields' => $v['projectId']
                            ]);
                            if ($bidUrlRes !== false) {
                                $bidUrlObj = json_decode($bidUrlRes, true);
                                $bidUrl = $bidUrlObj['data'];
                                if ($bidUrl) {
                                    $data['bidSiteId'] = 388;
                                    $data['bidSiteName'] = '甘肃省公共资源交易中心';
                                    $data['bidTitle'] = $v['projectName'];
                                    $data['bidUrl'] = $bidUrl;
                                    $data['beginDate'] = $v['latestPublishTime'];
                                    $data['inDate'] = date('Y-m-d H:i:s');
                                    $matchData[] = $data;
                                }
                            }
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
                log_message('error', '招标采集：未匹配到数据 388 -> 甘肃省公共资源交易中心 -> 阳光采购 限额以下政府采购 采购公告');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 388 -> 甘肃省公共资源交易中心 -> 阳光采购 限额以下政府采购 采购公告');
        }
        $url = 'https://ygjy.ggzyjy.gansu.gov.cn:3045/f/getAnnoList'; // 阳光交易 限额以下工程建设
        $res = global_curl($url, [
            'post' => 1,
            'postfields' => 'annoType=1&annoTitle=&platformCode=&pageNo=1&pageSize=15&selectType=1',
            'httpheader' => [
                'accept: text/html, */*; q=0.01',
                'accept-language: zh-CN,zh;q=0.9,en;q=0.8,en-GB;q=0.7,en-US;q=0.6',
                'cache-control: no-cache',
                'content-length: 66',
                'content-type: application/x-www-form-urlencoded; charset=UTF-8',
                'origin: https://ygjy.ggzyjy.gansu.gov.cn:3045',
                'pragma: no-cache',
                'referer: https://ygjy.ggzyjy.gansu.gov.cn:3045/f',
                'user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36 Edg/131.0.0.0'
            ]
        ]);
        if ($res !== false) {
            $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
            $preg = '#<aclass="yRegionItemclear"href="(.*)".*title="(.*)".*<spantitle="(.*)">.*</span></a>#isU';
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
                            $data['bidSiteId'] = 388;
                            $data['bidSiteName'] = '甘肃省公共资源交易中心';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = 'https://ygjy.ggzyjy.gansu.gov.cn:3045' . $v['url'];
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
                log_message('error', '招标采集：未匹配到数据 388 -> 甘肃省公共资源交易中心 -> 阳光交易 限额以下工程建设');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 388 -> 甘肃省公共资源交易中心 -> 阳光交易 限额以下工程建设');
        }
        $url = 'https://ygjy.ggzyjy.gansu.gov.cn:3045/f/getAnnoList'; // 阳光交易 限额以下政府采购
        $res = global_curl($url, [
            'post' => 1,
            'postfields' => 'annoType=3&annoTitle=&platformCode=&pageNo=1&pageSize=15&selectType=1',
            'httpheader' => [
                'accept: text/html, */*; q=0.01',
                'accept-language: zh-CN,zh;q=0.9,en;q=0.8,en-GB;q=0.7,en-US;q=0.6',
                'cache-control: no-cache',
                'content-length: 66',
                'content-type: application/x-www-form-urlencoded; charset=UTF-8',
                'origin: https://ygjy.ggzyjy.gansu.gov.cn:3045',
                'pragma: no-cache',
                'referer: https://ygjy.ggzyjy.gansu.gov.cn:3045/f',
                'user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36 Edg/131.0.0.0'
            ]
        ]);
        if ($res !== false) {
            $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
            $preg = '#<aclass="yRegionItemclear"href="(.*)".*title="(.*)".*<spantitle="(.*)">.*</span></a>#isU';
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
                            $data['bidSiteId'] = 388;
                            $data['bidSiteName'] = '甘肃省公共资源交易中心';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = 'https://ygjy.ggzyjy.gansu.gov.cn:3045' . $v['url'];
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
                log_message('error', '招标采集：未匹配到数据 388 -> 甘肃省公共资源交易中心 -> 阳光交易 限额以下政府采购');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 388 -> 甘肃省公共资源交易中心 -> 阳光交易 限额以下政府采购');
        }
        // 395 芜湖市公共资源交易中心
        $url = 'https://whsggzy.wuhu.gov.cn/EWB-FRONT1/rest/lightfrontaction/getPageInfoListNewWhJyxxCustom'; // 政府采购 采购公告
        $res = global_curl($url, [
            'post' => 1,
            'postfields' => 'searchText=&categoryNum=005002&xmlx=&hylb=%E5%85%A8%E9%83%A8&searchjydd=%E5%85%A8%E9%83%A8&searchxmxq=%E5%85%A8%E9%83%A8&searchjyfs=%E5%85%A8%E9%83%A8&searchinfotype=005002001&searchdate=%E5%85%A8%E9%83%A8&siteGuid=7eb5f7f1-9041-43ad-8e13-8fcb82ea831a&pageindex=0&pagesize=10&projectzilei=%E5%85%A8%E9%83%A8',
            'httpheader' => [
                'Accept: application/json, text/javascript, */*; q=0.01',
                'Accept-Encoding: gzip, deflate, br, zstd',
                'Accept-Language: zh-CN,zh;q=0.9,en;q=0.8,en-GB;q=0.7,en-US;q=0.6',
                'Cache-Control: no-cache',
                'Connection: keep-alive',
                'Content-Length: 307',
                'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
                'Host: whsggzy.wuhu.gov.cn',
                'Origin: https://whsggzy.wuhu.gov.cn',
                'Pragma: no-cache',
                'Referer: https://whsggzy.wuhu.gov.cn/whggzyjy/005/005002/transaction_information.html',
                'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36 Edg/131.0.0.0'
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
                            $data['bidSiteId'] = 395;
                            $data['bidSiteName'] = '芜湖市公共资源交易中心';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = 'https://whsggzy.wuhu.gov.cn/whggzyjy'.$v['infourl'];
                            $data['beginDate'] = $v['infodate'];
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 395 -> 芜湖市公共资源交易中心 政府采购 采购公告');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 395 -> 芜湖市公共资源交易中心 政府采购 采购公告');
        }
        $url = 'https://whsggzy.wuhu.gov.cn/EWB-FRONT1/rest/lightfrontaction/getPageInfoListNewWhJyxxCustom'; // 其他采购 采购公告
        $res = global_curl($url, [
            'post' => 1,
            'postfields' => 'searchText=&categoryNum=005020&xmlx=&hylb=%E5%85%A8%E9%83%A8&searchjydd=%E5%85%A8%E9%83%A8&searchxmxq=%E5%85%A8%E9%83%A8&searchjyfs=%E5%85%A8%E9%83%A8&searchinfotype=005020001&searchdate=%E5%85%A8%E9%83%A8&siteGuid=7eb5f7f1-9041-43ad-8e13-8fcb82ea831a&pageindex=0&pagesize=10&projectzilei=%E5%85%A8%E9%83%A8',
            'httpheader' => [
                'Accept: application/json, text/javascript, */*; q=0.01',
                'Accept-Encoding: gzip, deflate, br, zstd',
                'Accept-Language: zh-CN,zh;q=0.9,en;q=0.8,en-GB;q=0.7,en-US;q=0.6',
                'Cache-Control: no-cache',
                'Connection: keep-alive',
                'Content-Length: 307',
                'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
                'Host: whsggzy.wuhu.gov.cn',
                'Origin: https://whsggzy.wuhu.gov.cn',
                'Pragma: no-cache',
                'Referer: https://whsggzy.wuhu.gov.cn/whggzyjy/005/005020/transaction_information.html',
                'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36 Edg/131.0.0.0'
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
                            $data['bidSiteId'] = 395;
                            $data['bidSiteName'] = '芜湖市公共资源交易中心';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = 'https://whsggzy.wuhu.gov.cn/whggzyjy'.$v['infourl'];
                            $data['beginDate'] = $v['infodate'];
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 395 -> 芜湖市公共资源交易中心 其他采购 采购公告');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 395 -> 芜湖市公共资源交易中心 其他采购 采购公告');
        }
        // 399 北京市中介服务网上交易平台 【特殊通过浏览器刷新插件+自定义js获取数据后发送到本系统api并存数据库，每次采集从数据库拿】
        $res = $this->db->table('bid_temp')->select('res')->where('site', '北京市中介服务网上交易平台')->get()->getRowArray()['res'] ?? '';
        if ($res) {
            $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
            $preg = '#<tr><tdclass="pc_cell">.*<a.*href="(.*)".*title="(.*)".*</a></td><td>(.*)</td></tr>#isU';
            $obj = [];
            $arr = [];
            preg_match_all($preg, $res, $obj);
            $count = (is_countable($obj) && count($obj) > 0) ? count($obj[0]) : 0;
            if ($count > 0) {
                for ($j = 0; $j < $count; $j++) {
                    $arr[$j]['url'] = $obj[1][$j];
                    $arr[$j]['title'] = $obj[2][$j];
                    $arr[$j]['beginDate'] = str_replace(['年','月','日'], ['-','-',''], $obj[3][$j]);
                }
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['title'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['title'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 399;
                            $data['bidSiteName'] = '北京市中介服务网上交易平台';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = 'http://zjfw.beijing.gov.cn' . $v['url'];
                            $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 399 -> 北京市中介服务网上交易平台');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 399 -> 北京市中介服务网上交易平台');
        }
        // 426 中招联合招标采购平台
        $url = 'https://www.365trade.com.cn/zbgg/index.jhtml?typeId=102';
        $res = global_curl($url);
        if ($res !== false) {
            $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
            $res_temp = [];
            preg_match('#<ulclass="searchList">(.*)</ul>#isU', $res, $res_temp);
            $preg = '#<li><ahref="(.*)".*title="(.*)".*发布日期：(.*)</i>.*</li>#isU';
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
                            $data['bidSiteId'] = 426;
                            $data['bidSiteName'] = '中招联合招标采购平台';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = 'https://www.365trade.com.cn'.$v['url'];
                            $data['beginDate'] = $v['beginDate'];
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 426 -> 中招联合招标采购平台');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 426 -> 中招联合招标采购平台');
        }
        // 435 海安市限额以下公共资源交易平台
        $url = 'http://xeyxjy.haian.gov.cn/007/subpage.html';
        $res = global_curl($url);
        if ($res !== false) {
            $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
            $res_temp = [];
            preg_match('#<ulid="infoList">(.*)</ul>#isU', $res, $res_temp);
            $preg = '#<liclass="news-item"><a.*href="(.*)".*title="(.*)".*>.*</a><spanclass="dater">(.*)</span></li>#isU';
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
                            $data['bidSiteId'] = 435;
                            $data['bidSiteName'] = '海安市限额以下公共资源交易平台';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = 'http://xeyxjy.haian.gov.cn' . $v['url'];
                            $data['beginDate'] = $v['beginDate'];
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 435 -> 海安市限额以下公共资源交易平台');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 435 -> 海安市限额以下公共资源交易平台');
        }
        // 440 山东省采购与招标网
        $urls = [
            '招标公告' => 'https://www.sdbidding.org.cn/bulletins/11',
            '采购公告' => 'https://www.sdbidding.org.cn/bulletins/13'
        ];
        foreach ($urls as $index => $url) {
            $res = global_curl($url, [
                'httpheader' => [
                    'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7',
                    'Accept-Language: zh-CN,zh;q=0.9,en;q=0.8,en-GB;q=0.7,en-US;q=0.6',
                    'Cache-Control: no-cache',
                    'Connection: keep-alive',
                    'Host: www.sdbidding.org.cn',
                    'Pragma: no-cache',
                    'Sec-Fetch-Dest: document',
                    'Sec-Fetch-Mode: navigate',
                    'Sec-Fetch-Site: none',
                    'Sec-Fetch-User: ?1',
                    'Upgrade-Insecure-Requests: 1',
                    'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/123.0.0.0 Safari/537.36 Edg/123.0.0.0'
                ]
            ]);
            if ($res !== false) {
                $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
                $preg = '#<tr><tdclass="num">.*</td><tdclass="tit"><a.*href="(.*)".*title="(.*)">.*</a></td>.*<tdclass="time">(.*)</td></tr>#isU';
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
                                $data['bidSiteId'] = 440;
                                $data['bidSiteName'] = '山东省采购与招标网';
                                $data['bidTitle'] = $v['title'];
                                $data['bidUrl'] = 'https://www.sdbidding.org.cn' . $v['url'];
                                $data['beginDate'] = $v['beginDate'];
                                $data['inDate'] = date('Y-m-d H:i:s');
                                $final_arr[] = $data;
                            }
                        }
                    }
                } else {
                    log_message('error', '招标采集：未匹配到数据 440 -> 山东省采购与招标网 -> ' . $index);
                }
            } else {
                log_message('error', '招标采集：未获取到网页 440 -> 山东省采购与招标网 -> ' . $index);
            }
        }
        // 451 合肥市公共资源交易中心
        $url = 'https://ggzy.hefei.gov.cn/jyxx/002002/002002001/moreinfo_jyxxgg2.html';
        $res = global_curl($url);
        if ($res !== false) {
            $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
            $preg = '#<liclass="ewb-right-listclearfix"><a.*href="(.*)".*title="(.*)".*>.*</a><spanclass="ewb-right-dater">(.*)</span></li>#isU';
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
                            $data['bidSiteId'] = 451;
                            $data['bidSiteName'] = '合肥市公共资源交易中心';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = 'https://ggzy.hefei.gov.cn'.$v['url'];
                            $data['beginDate'] = $v['beginDate'];
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 451 -> 合肥市公共资源交易中心');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 451 -> 合肥市公共资源交易中心');
        }
        // 463 六安市公共资源交易中心 【特殊通过浏览器刷新插件+自定义js获取数据后发送到本系统api并存数据库，每次采集从数据库拿】
        $res = $this->db->table('bid_temp')->select('res')->where('site', '六安市公共资源交易中心-政府采购-货物类-采购公告')->get()->getRowArray()['res'] ?? '';
        if($res) {
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
                            $data['bidSiteId'] = 463;
                            $data['bidSiteName'] = '六安市公共资源交易中心';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = 'http://ggzy.luan.gov.cn'.$v['linkurl'];
                            $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['webdate']));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 463 -> 六安市公共资源交易中心 政府采购 货物类 采购公告');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 463 -> 六安市公共资源交易中心 政府采购 货物类 采购公告');
        }
        $res = $this->db->table('bid_temp')->select('res')->where('site', '北六安市公共资源交易中心-其他行业-交易公告')->get()->getRowArray()['res'] ?? '';
        if($res) {
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
                            $data['bidSiteId'] = 463;
                            $data['bidSiteName'] = '六安市公共资源交易中心';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = 'http://ggzy.luan.gov.cn'.$v['linkurl'];
                            $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['webdate']));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 463 -> 六安市公共资源交易中心 其他行业 交易公告');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 463 -> 六安市公共资源交易中心 其他行业 交易公告');
        }
        $res = $this->db->table('bid_temp')->select('res')->where('site', '六安市公共资源交易中心-非进场交易项目')->get()->getRowArray()['res'] ?? '';
        if($res) {
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
                            $data['bidSiteId'] = 463;
                            $data['bidSiteName'] = '六安市公共资源交易中心';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = 'http://ggzy.luan.gov.cn'.$v['linkurl'];
                            $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['webdate']));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 463 -> 六安市公共资源交易中心 非进场交易项目');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 463 -> 六安市公共资源交易中心 非进场交易项目');
        }
        // 468 四川省公共资源交易信息网
        $url = 'https://ggzyjy.sc.gov.cn/inteligentsearch/rest/esinteligentsearch/getFullTextDataNew';
        $res = global_curl($url, [
            'post' => 1,
            'postfields' => '{"token":"","pn":0,"rn":100,"sdt":"","edt":"","wd":"","inc_wd":"","exc_wd":"","fields":"","cnum":"","sort":"{\"webdate\":\"0\"}","ssort":"","cl":10000,"terminal":"","condition":[{"fieldName":"categorynum","equal":"002002001","notEqual":null,"equalList":null,"notEqualList":null,"isLike":true,"likeType":2}],"time":[{"fieldName":"webdate","startTime":"","endTime":""}],"highlights":"","statistics":null,"unionCondition":null,"accuracy":"","noParticiple":"1","searchRange":null,"noWd":true}'
        ]); // 政府采购 采购公告
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
                            $data['bidSiteId'] = 468;
                            $data['bidSiteName'] = '四川省公共资源交易信息网';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = 'https://ggzyjy.sc.gov.cn' . $v['linkurl'];
                            $data['beginDate'] = $v['webdate'];
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 468 -> 四川省公共资源交易信息网 政府采购 采购公告');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 468 -> 四川省公共资源交易信息网 政府采购 采购公告');
        }
        $res = global_curl($url, [
            'post' => 1,
            'postfields' => '{"token":"","pn":0,"rn":100,"sdt":"","edt":"","wd":"","inc_wd":"","exc_wd":"","fields":"","cnum":"","sort":"{\"webdate\":\"0\"}","ssort":"","cl":10000,"terminal":"","condition":[{"fieldName":"categorynum","equal":"002007001","notEqual":null,"equalList":null,"notEqualList":null,"isLike":true,"likeType":2}],"time":[{"fieldName":"webdate","startTime":"","endTime":""}],"highlights":"","statistics":null,"unionCondition":null,"accuracy":"","noParticiple":"1","searchRange":null,"noWd":true}'
        ]); // 其他类别 公告信息
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
                            $data['bidSiteId'] = 468;
                            $data['bidSiteName'] = '四川省公共资源交易信息网';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = 'https://ggzyjy.sc.gov.cn' . $v['linkurl'];
                            $data['beginDate'] = $v['webdate'];
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 468 -> 四川省公共资源交易信息网 其他类别 公告信息');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 468 -> 四川省公共资源交易信息网 其他类别 公告信息');
        }
        // 491 江西省公共资源交易平台
        $url = 'http://www.jxsggzy.cn/XZinterface/rest/esinteligentsearch/getFullTextDataNew';
        $res = global_curl($url, [
            'post' => 1,
            'postfields' => '{"token":"","pn":0,"rn":100,"sdt":"","edt":"","wd":"","inc_wd":"","exc_wd":"","fields":"","cnum":"","sort":"{\"webdate\":\"0\",\"id\":\"0\"}","ssort":"","cl":10000,"terminal":"","condition":[{"fieldName":"categorynum","equal":"002006001","notEqual":null,"equalList":null,"notEqualList":null,"isLike":true,"likeType":2}],"time":[{"fieldName":"webdate","startTime":"'.date('Y-m-d', strtotime('-10 day')).' 00:00:00","endTime":"'.date('Y-m-d').' 23:59:59"}],"highlights":"","statistics":null,"unionCondition":[],"accuracy":"","noParticiple":"1","searchRange":null,"noWd":true}'
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
                            $data['bidSiteId'] = 491;
                            $data['bidSiteName'] = '江西省公共资源交易平台';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = 'https://www.jxsggzy.cn'.$v['linkurl'];
                            $data['beginDate'] = $v['webdate'];
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 491 -> 江西省公共资源交易平台');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 491 -> 江西省公共资源交易平台');
        }
        // 493 深圳市公共资源交易中心
        $url = 'https://www.szggzy.com/cms/api/v1/trade/content/page';
        $res = global_curl($url, [
            'post' => 1,
            'postfields' => '{"modelId":1378,"channelId":2850,"fields":[{"fieldName":"jygg_gglxmc_rank1","fieldValue":"采购公告"}],"jsgcProjectType":"","parentBusinessType":"政府采购框架采购","title":null,"releaseTimeBegin":null,"releaseTimeEnd":null,"page":0,"size":99,"siteId":1}',
            'httpheader' => [
                'Accept: application/json, text/javascript, */*; q=0.01',
                'Accept-Language: zh-CN,zh;q=0.9,en;q=0.8,en-GB;q=0.7,en-US;q=0.6',
                'Cache-Control: no-cache',
                'Connection: keep-alive',
                'Content-Length: 264',
                'Content-Type: application/json',
                'Host: www.szggzy.com',
                'Origin: https://www.szggzy.com',
                'Pragma: no-cache',
                'Referer: https://www.szggzy.com/jygg/list.html?id=zfcg',
                'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/123.0.0.0 Safari/537.36 Edg/123.0.0.0'
            ]
        ]);
        if ($res !== false) {
            $obj = json_decode($res, true);
            $arr = $obj['data']['content'] ?? [];
            if (is_array($arr) && count($arr) > 0) {
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['title'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['title'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 493;
                            $data['bidSiteName'] = '深圳市公共资源交易中心';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = $v['url'];
                            $data['beginDate'] = $v['publishTime'];
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 493 -> 深圳市公共资源交易中心');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 493 -> 深圳市公共资源交易中心');
        }
        // 501 威海市公共资源交易网
        // 先获取AccessToken
        $tokenUrl = 'https://ggzyjy.weihai.cn/EpointWebBuilder/rest/getOauthInfoAction/getNoUserAccessToken';
        $tokenRes = global_curl($tokenUrl, [
            'post' => 1,
            'httpheader' => [
                'Accept: application/json, text/javascript, */*; q=0.01',
                'Accept-Encoding: gzip, deflate, br, zstd',
                'Accept-Language: zh-CN,zh;q=0.9,en;q=0.8,en-GB;q=0.7,en-US;q=0.6',
                'Cache-Control: no-cache',
                'Connection: keep-alive',
                'Content-Length: 0',
                'Host: ggzyjy.weihai.cn',
                'Origin: https://ggzyjy.weihai.cn',
                'Pragma: no-cache',
                'Referer: https://ggzyjy.weihai.cn/weihai/jyxx/003002/transInfo.html',
                'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0'
            ]
        ]);
        if ($tokenRes !== false) {
            $tokenRes = json_decode($tokenRes, true);
            if (isset($tokenRes['custom']['access_token'])) {
                $url = 'https://ggzyjy.weihai.cn/EpointWebBuilder/rest/frontAppCustomAction/getPageInfoListNew';
                $res = global_curl($url, [
                    'post' => 1,
                    'postfields' => 'params=%7B%22siteGuid%22%3A%227eb5f7f1-9041-43ad-8e13-8fcb82ea831a%22%2C%22categoryNum%22%3A%22003002001%22%2C%22kw%22%3A%22%22%2C%22startDate%22%3A%22%22%2C%22endDate%22%3A%22%22%2C%22pageIndex%22%3A0%2C%22pageSize%22%3A30%2C%22area%22%3A%22%22%7D',
                    'httpheader' => [
                        'Accept: application/json, text/javascript, */*; q=0.01',
                        'Accept-Encoding: gzip, deflate, br, zstd',
                        'Accept-Language: zh-CN,zh;q=0.9,en;q=0.8,en-GB;q=0.7,en-US;q=0.6',
                        'Authorization: Bearer ' . $tokenRes['custom']['access_token'],
                        'Cache-Control: no-cache',
                        'Connection: keep-alive',
                        'Content-Length: 248',
                        'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
                        'Host: ggzyjy.weihai.cn',
                        'Origin: https://ggzyjy.weihai.cn',
                        'Pragma: no-cache',
                        'Referer: https://ggzyjy.weihai.cn/jyxx/003002/003002001/transInfo.html',
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
                                    $data['bidSiteId'] = 501;
                                    $data['bidSiteName'] = '威海市公共资源交易网';
                                    $data['bidTitle'] = $v['title'];
                                    $data['bidUrl'] = 'https://ggzyjy.weihai.cn/weihai' . $v['infourl'];
                                    $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['infodate']));
                                    $data['inDate'] = date('Y-m-d H:i:s');
                                    $final_arr[] = $data;
                                }
                            }
                        }
                    } else {
                        log_message('error', '招标采集：未匹配到数据 501 -> 威海市公共资源交易网');
                    }
                } else {
                    log_message('error', '招标采集：未获取到网页 501 -> 威海市公共资源交易网');
                }
            } else {
                log_message('error', '招标采集：未获取到AccessToken 501 -> 威海市公共资源交易网');
            }
        } else {
            log_message('error', '招标采集：未获取到AccessToken 501 -> 威海市公共资源交易网');
        }
        // 534 河北省数据和政务服务局
        $url = 'https://szj.hebei.gov.cn/inteligentsearchnew/rest/esinteligentsearch/getFullTextDataNew';
        $res = global_curl($url, [
            'post' => 1,
            'postfields' => '{"token":"","pn":0,"rn":200,"sdt":"","edt":"","wd":" ","inc_wd":"","exc_wd":"","fields":"title","cnum":"001","sort":"{\"webdate\":0}","ssort":"title","cl":200,"terminal":"","condition":[{"fieldName":"categorynum","equal":"003001001","notEqual":null,"equalList":null,"notEqualList":null,"isLike":true,"likeType":2}],"time":null,"highlights":"title","statistics":null,"unionCondition":null,"accuracy":"","noParticiple":"0","searchRange":null,"isBusiness":"1"}'
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
                            $data['bidSiteId'] = 534;
                            $data['bidSiteName'] = '河北省数据和政务服务局';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = 'https://szj.hebei.gov.cn/hbggfwpt'.$v['linkurl'];
                            $data['beginDate'] = $v['webdate'];
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 534 -> 河北省数据和政务服务局');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 534 -> 河北省数据和政务服务局');
        }
        // 552 泰州市公共资源交易中心
        $url = 'http://ggzy.taizhou.gov.cn/inteligentsearch/rest/esinteligentsearch/getFullTextDataNew';
        $res = global_curl($url, [
            'post' => 1,
            'postfields' => '{"esdsid":"1","token":"","pn":0,"rn":100,"sdt":"","edt":"","wd":" ","inc_wd":"","exc_wd":"","fields":"title","cnum":"001","sort":"{\"webdate\":\"0\"}","ssort":"title","cl":1000,"cutIngore":"title;linkurl","terminal":"","condition":[{"fieldName":"categorynum","equal":"001004002","notEqual":null,"equalList":null,"notEqualList":null,"isLike":true,"likeType":2}],"time":[],"highlights":"title","statistics":null,"unionCondition":null,"accuracy":"","noParticiple":"1","searchRange":[],"isBusiness":"1"}'
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
                            $data['bidSiteId'] = 552;
                            $data['bidSiteName'] = '泰州市公共资源交易中心';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = 'http://ggzy.taizhou.gov.cn'.$v['linkurl'];
                            $data['beginDate'] = $v['webdate'];
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 552 -> 泰州市公共资源交易中心');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 552 -> 泰州市公共资源交易中心');
        }
        // 582 河北省招标投标公共服务平台
        for($i = 1; $i <= 5; $i++) {
            // 依法必须招标项目
            $url = 'https://szj.hebei.gov.cn/zbtbfwpt/homepage/getdata.json?city=13&infotype=zbgg&hytype=A&rows=10&page='.$i.'&sidx=bulletinissuetime&sord=desc';
            $res = global_curl($url);
            if ($res !== false) {
                $obj = json_decode($res, true);
                $arr = $obj['rows'] ?? [];
                if (is_array($arr) && count($arr) > 0) {
                    foreach ($arr as $k => $v) {
                        $data = [];
                        if (!preg_match('#(' . $in_keywords . ')#', $v['name'])) {
                            continue;
                        } else {
                            if (preg_match('#(' . $out_keywords . ')#', $v['name'])) {
                                continue;
                            } else {
                                $data['bidSiteId'] = 582;
                                $data['bidSiteName'] = '河北省招标投标公共服务平台';
                                $data['bidTitle'] = $v['name'];
                                $data['bidUrl'] = 'https://szj.hebei.gov.cn/zbtbfwpt'.$v['url'].'&infoid='.$v['guid'].'&bdcodes='.$v['bdcodes'].'&laiyuan='. urlencode($v['laiyuan']);
                                $data['beginDate'] = $v['fbrq'];
                                $data['inDate'] = date('Y-m-d H:i:s');
                                $final_arr[] = $data;
                            }
                        }
                    }
                } else {
                    log_message('error', '招标采集：未匹配到数据 582 -> 河北省招标投标公共服务平台 -> 依法必须招标项目 -> 循环' . $i);
                }
            } else {
                log_message('error', '招标采集：未获取到网页 582 -> 河北省招标投标公共服务平台 -> 依法必须招标项目 -> 循环' . $i);
            }
        }
        for($i = 1; $i <= 5; $i++) {
            // 非必须招标项目
            $url = 'https://szj.hebei.gov.cn/zbtbfwpt/homepage/getdata.json?city=13&infotype=zbgg&hytype=B&rows=10&page='.$i.'&sidx=bulletinissuetime&sord=desc';
            $res = global_curl($url);
            if ($res !== false) {
                $obj = json_decode($res, true);
                $arr = $obj['rows'] ?? [];
                if (is_array($arr) && count($arr) > 0) {
                    foreach ($arr as $k => $v) {
                        $data = [];
                        if (!preg_match('#(' . $in_keywords . ')#', $v['name'])) {
                            continue;
                        } else {
                            if (preg_match('#(' . $out_keywords . ')#', $v['name'])) {
                                continue;
                            } else {
                                $data['bidSiteId'] = 582;
                                $data['bidSiteName'] = '河北省招标投标公共服务平台';
                                $data['bidTitle'] = $v['name'];
                                $data['bidUrl'] = 'https://szj.hebei.gov.cn/zbtbfwpt'.$v['url'].'&infoid='.$v['guid'].'&bdcodes='.$v['bdcodes'].'&laiyuan='. urlencode($v['laiyuan']);
                                $data['beginDate'] = $v['fbrq'];
                                $data['inDate'] = date('Y-m-d H:i:s');
                                $final_arr[] = $data;
                            }
                        }
                    }
                } else {
                    log_message('error', '招标采集：未匹配到数据 582 -> 河北省招标投标公共服务平台 -> 非必须招标项目 -> 循环' . $i);
                }
            } else {
                log_message('error', '招标采集：未获取到网页 582 -> 河北省招标投标公共服务平台 -> 非必须招标项目 -> 循环' . $i);
            }
        }
        // 603 重庆市公共资源交易网_南川区
        $url = 'https://www.cqggzy.com/inteligentsearch/rest/esinteligentsearch/getFullTextDataNew';
        $res = global_curl($url, [
            'post' => 1,
            'postfields' => '{"token":"","pn":0,"rn":99,"sdt":"","edt":"","wd":"","inc_wd":"","exc_wd":"","fields":"","cnum":"005","sort":"{\"istop\":0,\"ordernum\":0,\"webdate\":0,\"pubinwebdate\":0}","ssort":"","cl":10000,"terminal":"","condition":[{"fieldName":"categorynum","equal":"014013001","notEqual":null,"equalList":null,"notEqualList":["014001018","014002014","014005015"],"isLike":true,"likeType":2}],"time":null,"highlights":"","statistics":null,"unionCondition":null,"accuracy":"","noParticiple":"1","searchRange":null,"noWd":true}'
        ]);
        if ($res !== false) {
            $obj = json_decode($res, true);
            if (is_array($obj) && isset($obj['content'])) {
                $obj = json_decode($obj['content'], true);
                $arr = $obj['result']['records'] ?? [];
            } else {
                $arr = [];
            }
            if (is_array($arr) && count($arr) > 0) {
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['title'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['title'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 603;
                            $data['bidSiteName'] = '重庆市公共资源交易网_南川区';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = 'https://www.cqggzy.com/nanchuanweb/xxhz/'.substr($v['categorynum'], 0, 6).'/'.$v['categorynum'].'/'.date('Ymd', strtotime($v['pubinwebdate'])).'/'.$v['infoid'].'.html';
                            $data['beginDate'] = $v['pubinwebdate'];
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 603 -> 重庆市公共资源交易网_南川区');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 603 -> 重庆市公共资源交易网_南川区');
        }
        // 622 涟水县人民政府
        $url = 'http://www.lianshui.gov.cn/articleCommonController/lists.do';
        $res = global_curl($url, [
            'post' => 1,
            'postfields' => 'topic=&page=1&pagesize=100&title=&docNo=&orgname=&summary=&key=&pubtime1=&pubtime2=&rdeptid=000000006512793301651723f7fe04a0&deptid=',
            'httpheader' => [
                'Accept: application/json, text/javascript, */*; q=0.01',
                'Accept-Encoding: gzip, deflate',
                'Accept-Language: zh-CN,zh;q=0.9,en;q=0.8,en-GB;q=0.7,en-US;q=0.6',
                'Cache-Control: no-cache',
                'Connection: keep-alive',
                'Content-Length: 132',
                'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
                'Host: www.lianshui.gov.cn',
                'Origin: http://www.lianshui.gov.cn',
                'Pragma: no-cache',
                'Referer: http://www.lianshui.gov.cn/cmsweb/zwgk/ls/index.html',
                'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/128.0.0.0 Safari/537.36 Edg/128.0.0.0',
                'X-Requested-With: XMLHttpRequest'
            ]
        ]);
        if ($res !== false) {
            $obj = json_decode($res, true);
            $arr = $obj['value']['list'] ?? [];
            if (is_array($arr) && count($arr) > 0) {
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['title'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['title'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 622;
                            $data['bidSiteName'] = '涟水县人民政府';
                            $data['bidTitle'] = preg_replace("/([\r|\n|\t| ]+)/", '', $v['title']);
                            $data['bidUrl'] = $v['domain'].$v['path'];
                            $data['beginDate'] = $v['releaseTime'];
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 622 -> 涟水县人民政府');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 622-> 涟水县人民政府');
        }
        // 643 阜阳市公共资源交易中心
        $url = 'https://jyzx.fy.gov.cn/EpointWebBuilder/rest/GgSearchAction/getInfoMationList';
        $res = global_curl($url, [
            'post' => 1,
            'postfields' => 'siteGuid=7eb5f7f1-9041-43ad-8e13-8fcb82ea831a&categoryNum=006001002&xiaQuCode=&hylb=&jyfs=&startdate='.date('Y-m-d', strtotime('-7 day')).'&enddate='.date('Y-m-d').'+23%3A59%3A59&pageIndex=0&pageSize=10&YZM=&ImgGuid=&key=&ismulticol=',
            'httpheader' => [
                'Accept: application/json, text/javascript, */*; q=0.01',
                'Accept-Encoding: gzip, deflate, br, zstd',
                'Accept-Language: zh-CN,zh;q=0.9,en;q=0.8,en-GB;q=0.7,en-US;q=0.6',
                'Cache-Control: no-cache',
                'Connection: keep-alive',
                'Content-Length: 196',
                'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
                'Host: jyzx.fy.gov.cn',
                'Origin: https://jyzx.fy.gov.cn',
                'Pragma: no-cache',
                'Referer: https://jyzx.fy.gov.cn/fuyang/jyxx/006001/transinfo.html',
                'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36 Edg/148.0.0.0'
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
                            $data['bidSiteId'] = 643;
                            $data['bidSiteName'] = '阜阳市公共资源交易中心';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = 'https://jyzx.fy.gov.cn/fuyang'.$v['infourl'];
                            $data['beginDate'] = $v['infodate'];
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 643 -> 阜阳市公共资源交易中心');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 643 -> 阜阳市公共资源交易中心');
        }
        // 659 宝应县政府
        $url = 'http://www.baoying.gov.cn:8081/zwpd/listzfcg.asp';
        $res = global_curl($url);
        if ($res !== false) {
            $res = mb_convert_encoding($res, "UTF-8", "GBK"); //转换编码
            $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
            $preg = '#<tr><tdwidth="23".*<a.*href="(.*)".*title="(.*)".*</a></td><td.*bt_time>(.*)</td>.*</tr>#isU';
            $obj = [];
            $arr = [];
            preg_match_all($preg, $res, $obj);
            $count = (is_countable($obj) && count($obj) > 0) ? count($obj[0]) : 0;
            if ($count > 0) {
                for ($j = 0; $j < $count; $j++) {
                    $arr[$j]['url'] = $obj[1][$j];
                    $arr[$j]['title'] = $obj[2][$j];
                    $arr[$j]['beginDate'] = str_replace('.', '-', $obj[3][$j]);
                }
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['title'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['title'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 659;
                            $data['bidSiteName'] = '宝应县政府';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = 'http://www.baoying.gov.cn:8081/zwpd/' . $v['url'];
                            $data['beginDate'] = $v['beginDate'];
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 659 -> 宝应县政府');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 659 -> 宝应县政府');
        }
        // 664 宁夏回族自治区公共资源交易网
        $url = 'https://ggzyjy.fzggw.nx.gov.cn/interface_wz/rest/esinteligentsearch/getFullTextDataNew';
        $res = global_curl($url, [
            'post' => 1,
            'postfields' => '{"token":"","pn":0,"rn":100,"sdt":"","edt":"","wd":"","inc_wd":"","exc_wd":"","fields":"","cnum":"","sort":"{\"webdate\":\"0\",\"id\":\"0\"}","ssort":"","cl":10000,"terminal":"","condition":[{"fieldName":"categorynum","equal":"001001002001","notEqual":null,"equalList":null,"notEqualList":null,"isLike":true,"likeType":2}],"time":null,"highlights":"","statistics":null,"unionCondition":[],"accuracy":"","noParticiple":"1","searchRange":null,"noWd":true}',
            'httpheader' => [
                'Accept: application/json, text/javascript, */*; q=0.01',
                'Accept-Encoding: gzip, deflate, br, zstd',
                'Accept-Language: zh-CN,zh;q=0.9,en;q=0.8,en-GB;q=0.7,en-US;q=0.6',
                'Cache-Control: no-cache',
                'Connection: keep-alive',
                'Content-Length: 453',
                'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
                'Host: ggzyjy.fzggw.nx.gov.cn',
                'Origin: https://ggzyjy.fzggw.nx.gov.cn',
                'Pragma: no-cache',
                'Referer: https://ggzyjy.fzggw.nx.gov.cn/dzjy/001001/001001002/trade_infomation.html',
                'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36 Edg/135.0.0.0'
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
                            $data['bidSiteId'] = 664;
                            $data['bidSiteName'] = '宁夏回族自治区公共资源交易网';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = 'https://www.nxggzyjy.org'.$v['linkurl'];
                            $data['beginDate'] = $v['infodate'];
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 664 -> 宁夏回族自治区公共资源交易网');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 664 -> 宁夏回族自治区公共资源交易网');
        }
        // 696 宜都市人民政府
        $url = 'https://xxgkapi.yichang.gov.cn/show/lists?jsoncallback=&areaid=8&webid=&cid=44&page=1&pagenums=20&orderby=1&_='.intval(microtime(true) * 1000);
        $res = global_curl($url);
        if ($res !== false) {
            $obj = json_decode($res, true);
            $arr = $obj['lists'] ?? [];
            if (is_array($arr) && count($arr) > 0) {
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['title'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['title'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 696;
                            $data['bidSiteName'] = '宜都市人民政府';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = 'http://www.yidu.gov.cn/zfxxgk/show.html?aid=8&id='.$v['n_id'].'&depid=';
                            $data['beginDate'] = $v['createtime'];
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 696 -> 钟山职业技术学院资产管理处');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 696 -> 宜都市人民政府');
        }
        // 702 郧阳区人民政府
        $url = 'http://yunyang.shiyan.gov.cn/xxgkxi/fdzdgk/qtzdgknr/gsgg/';
        $res = global_curl($url);
        if ($res !== false) {
            $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
            $preg = '#<liclass="list-group-itemborder-0"><a.*href=".(.*)".*title="(.*)".*</a><span.*>(.*)</span>.*</li>#isU';
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
                            $data['bidSiteId'] = 702;
                            $data['bidSiteName'] = '郧阳区人民政府';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = 'http://yunyang.shiyan.gov.cn/xxgkxi/fdzdgk/qtzdgknr/gsgg'.$v['url'];
                            $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 702 -> 郧阳区人民政府');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 702 -> 郧阳区人民政府');
        }
        // 732 重庆市政府采购网 【特殊通过浏览器刷新插件+自定义js获取数据后发送到本系统api并存数据库，每次采集从数据库拿】
        $res = $this->db->table('bid_temp')->select('res')->where('site', '重庆市政府采购网')->get()->getRowArray()['res'] ?? '';
        if($res) {
            $arr = json_decode($res, true);
            if (is_array($arr) && count($arr) > 0) {
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['title'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['title'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 732;
                            $data['bidSiteName'] = '重庆市政府采购网';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = 'https://www.ccgp-chongqing.gov.cn/info-notice/procument-notice-detail/'.$v['id'];
                            $data['beginDate'] = $v['issueTime'];
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 732 -> 重庆市政府采购网');
            }
        } else {
            log_message('error', '招标采集：未从数据库中获取到保存的res 732 -> 重庆市政府采购网');
        }
        // 754 河北省体育局
        $url = 'https://sport.hebei.gov.cn/tongzhigonggao/';
        $res = global_curl($url);
        if ($res !== false) {
            $res = mb_convert_encoding($res, "UTF-8", "GBK"); //转换编码
            $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
            $res_temp = [];
            preg_match('#<divclass="gk-content-list".*>(.*)</div>#isU', $res, $res_temp);
            $preg = '#<li><a.*href="(.*)".*title="(.*)".*>.*</a><spanclass="date">(.*)</span></li>#isU';
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
                            $data['bidSiteId'] = 754;
                            $data['bidSiteName'] = '河北省体育局';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = 'https://sport.hebei.gov.cn'.$v['url'];
                            $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 754 -> 河北省体育局');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 754 -> 河北省体育局');
        }
        // 781 苏州市公共资源交易平台
        $url = 'https://www.szzyjy.com.cn/EpointWebBuilder/JyxxSearchAction.action?cmd=getList1&categorynum=003004002&diqu=&xmmc=&zstype=&zblx=&starttime='.date('Y-m-d', strtotime('-7 day')).'&endtime='.date('Y-m-d').'&siteguid=7eb5f7f1-9041-43ad-8e13-8fcb82ea831a&pageIndex=0&pageSize=99';
        $res = global_curl($url);
        if ($res !== false) {
            $obj = json_decode($res, true);
            $obj2 = $obj['custom'] ?? '';
            $obj3 = json_decode($obj2, true);
            $arr = $obj3['Table'] ?? [];
            if (is_array($arr) && count($arr) > 0) {
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['title'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['title'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 781;
                            $data['bidSiteName'] = '苏州市公共资源交易平台';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = 'https://www.szzyjy.com.cn/jump.html?infoguid='.$v['infoid'].'&categorynum='.$v['categorynum'];
                            $data['beginDate'] = $v['postdate'];
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 781 -> 苏州市公共资源交易平台');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 781 -> 苏州市公共资源交易平台');
        }
        // 803 甘肃政府采购网
        $url = 'https://www.ccgp-gansu.gov.cn/gpcms/rest/web/v2/info/selectInfoMoreChannel?siteId=793f8fcd-77c4-4c76-9ce6-9de8deddee8d&channel=58d8630f-7191-41a5-921a-db6a0b74f367&currPage=1&pageSize=16&noticeType=00101&regionCode=620001&cityOrArea=&_t='.intval(microtime(true) * 1000);
        $res = global_curl($url);
        if($res !== false) {
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
                            $data['bidSiteId'] = 803;
                            $data['bidSiteName'] = '甘肃政府采购网';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = 'https://www.ccgp-gansu.gov.cn/maincms-web/articleGs?type=notice&id='.$v['id'].'&planId=&channel='.$v['channel'];
                            $data['beginDate'] = $v['noticeTime'];
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 803 -> 甘肃政府采购网');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 803 -> 甘肃政府采购网');
        }
        // 848 玉溪市人民政府
        $url = 'https://www.yuxi.gov.cn/yxs/ztb/';
        $res = global_curl($url);
        if($res !== false) {
            $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
            $preg = '#<atarget="_blank"title="(.*)"href="(.*)".*<span>(.*)</span></a>#isU';
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
                            $data['bidSiteId'] = 848;
                            $data['bidSiteName'] = '玉溪市人民政府';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = 'https://www.yuxi.gov.cn' . $v['url'];
                            $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 848 -> 玉溪市人民政府');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 848 -> 玉溪市人民政府');
        }
        // 875 江门市新会区人力资源和社会保障局
        $url = 'http://www.xinhui.gov.cn/jmxhqrsj/gkmlpt/api/all/1398?page=1&sid=750098';
        $res = global_curl($url);
        if($res !== false) {
            $obj = json_decode($res, true);
            $arr = $obj['articles'] ?? [];
            if (is_array($arr) && count($arr) > 0) {
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['title'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['title'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 875;
                            $data['bidSiteName'] = '江门市新会区人力资源和社会保障局';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = $v['url'];
                            $data['beginDate'] = $v['created_at'];
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 875 -> 江门市新会区人力资源和社会保障局');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 875 -> 江门市新会区人力资源和社会保障局');
        }
        // 899 兵团政府采购网 【特殊通过浏览器刷新插件+自定义js获取数据后发送到本系统api并存数据库，每次采集从数据库拿】
        $ress = [
            '采购项目公告' => $this->db->table('bid_temp')->select('res')->where('site', '兵团政府采购网采购项目公告')->get()->getRowArray()['res'] ?? '',
            '其他非政府采购公告' => $this->db->table('bid_temp')->select('res')->where('site', '兵团政府采购网其他非政府采购公告')->get()->getRowArray()['res'] ?? '',
            '其他非政府采购项目招标公告' => $this->db->table('bid_temp')->select('res')->where('site', '兵团政府采购网其他非政府采购项目招标公告')->get()->getRowArray()['res'] ?? ''
        ];
        foreach ($ress as $index => $res) {
            if($res !== false) {
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
                                $data['bidSiteId'] = 899;
                                $data['bidSiteName'] = '兵团政府采购网';
                                $data['bidTitle'] = $v['title'];
                                $data['bidUrl'] = 'http://ccgp-bingtuan.gov.cn/site/detail?parentId=189169&articleId='.str_replace(' ', '+', $v['articleId']);
                                $data['beginDate'] =date('Y-m-d H:i:s', intval($v['publishDate'] / 1000));
                                $data['inDate'] = date('Y-m-d H:i:s');
                                $final_arr[] = $data;
                            }
                        }
                    }
                } else {
                    log_message('error', '招标采集：未匹配到数据 899 -> 兵团政府采购网 -> ' . $index);
                }
            } else {
                log_message('error', '招标采集：未获取到网页 899 -> 兵团政府采购网 -> ' . $index);
            }
        }
        // 964 盐城市人民政府 【特殊通过浏览器刷新插件+自定义js获取数据后发送到本系统api并存数据库，每次采集从数据库拿】
        $res = $this->db->table('bid_temp')->select('res')->where('site', '盐城市人民政府')->get()->getRowArray()['res'] ?? '';
        if($res) {
            $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
            $preg = '#<trclass="tr_main_value_.*>.*<ahref="(.*)".*title="(.*)".*</a></td><td.*>(.*)</td></tr>#isU';
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
                            $data['bidSiteId'] = 964;
                            $data['bidSiteName'] = '盐城市人民政府';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = $v['url'];
                            $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 964 -> 盐城市人民政府');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 964 -> 盐城市人民政府');
        }
        // 969 重庆市九龙坡区教育委员会
        $url = 'https://www.gec123.com/xcj-gateway/api/v1/notices/stable?__platDomain__=jlpqjw.gec123.com&excludeOrgIds=130117358764164079&ifLoseSightPlatDomain=true&pi=1&ps=20&sourceType=1,2,3,4,5,6&stockOrgId=130117349800935913&subordinateUnits=1&type=100,200,201,202,203,204,205,206,207,309,400,401,402,3091,4001,888,889,890';
        $res = global_curl($url);
        if($res !== false) {
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
                            $data['bidSiteId'] = 969;
                            $data['bidSiteName'] = '重庆市九龙坡区教育委员会';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = 'https://jlpqjw.gec123.com/notices/detail/'.$v['id'].'?title='.$v['title'].'sourceType='.$v['sourceType'];
                            $data['beginDate'] = date('Y-m-d H:i:s', intval($v['issueTime'] / 1000));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 969 -> 重庆市九龙坡区教育委员会');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 969 -> 重庆市九龙坡区教育委员会');
        }
        // 990 新疆公共资源交易网
        $url = 'https://ggzy.xinjiang.gov.cn/inteligentsearchnew/rest/esinteligentsearch/getFullTextDataNew';
        $res = global_curl($url, [
            'post' => 1,
            'postfields' => '{"token":"","pn":0,"rn":50,"sdt":"","edt":"","wd":"","inc_wd":"","exc_wd":"","fields":"title,projectnum,projectname","cnum":"001","sort":"{\"webdate\":\"0\"}","ssort":"title","cl":200,"terminal":"","condition":[{"fieldName":"categorynum","isLike":true,"likeType":2,"equal":"001004003"}],"time":null,"highlights":"title","statistics":null,"unionCondition":[],"accuracy":"100","noParticiple":"0","searchRange":null,"isBusiness":1}'
        ]);
        if($res !== false) {
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
                            $data['bidSiteId'] = 990;
                            $data['bidSiteName'] = '新疆公共资源交易网';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = 'https://ggzy.xinjiang.gov.cn/xinjiangggzy'.$v['linkurl'];
                            $data['beginDate'] = $v['webdate'];
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 990 -> 新疆公共资源交易网');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 990 -> 新疆公共资源交易网');
        }
        // 992 饶平县人民政府
        $url = 'https://www.raoping.gov.cn/zwgk/zfqzqd/gsgg/';
        $res = global_curl($url);
        if($res !== false) {
            $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
            $res_temp = [];
            preg_match('#<ulclass="ul_news">(.*)</ul>#isU', $res, $res_temp);
            $preg = '#<li.*<a.*href="(.*)">.*<span>(.*)</span></p><b>(.*)</b></a></li>#isU';
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
                            $data['bidSiteId'] = 992;
                            $data['bidSiteName'] = '饶平县人民政府';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = $v['url'];
                            $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 992 -> 饶平县人民政府');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 992 -> 饶平县人民政府');
        }
        // 1006 北京市政府采购网
        $urls = [
            '市级招标公告' => 'http://www.ccgp-beijing.gov.cn/xxgg/sjxxgg/zbgg/A002004001001index_1.htm',
            '区级招标公告' => 'http://www.ccgp-beijing.gov.cn/xxgg/qjxxgg/qjzbgg/A002004002001index_1.htm'
        ];
        foreach ($urls as $index => $url) {
            $res = global_curl($url);
            if($res !== false) {
                $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
                $preg = '#<li><ahref="(.*)".*>(.*)</a><span.*>(.*)</span></li>#isU';
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
                                $data['bidSiteId'] = 1006;
                                $data['bidSiteName'] = '北京市政府采购网';
                                $data['bidTitle'] = $v['title'];
                                $data['bidUrl'] = 'http:' . $v['url'];
                                $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                                $data['inDate'] = date('Y-m-d H:i:s');
                                $final_arr[] = $data;
                            }
                        }
                    }
                } else {
                    log_message('error', '招标采集：未匹配到数据 1006 -> 北京市政府采购网 -> ' . $index);
                }
            } else {
                log_message('error', '招标采集：未获取到网页 1006 -> 北京市政府采购网 -> ' . $index);
            }
        }
        // 1014 兰州市公共资源交易中心
        $url = 'https://lzggzyjy.lanzhou.gov.cn/inteligentsearch/rest/esinteligentsearch/getFullTextDataNew';
        $res = global_curl($url, [
            'post' => 1,
            'postfields' => '{"token":"","pn":0,"rn":30,"sdt":"","edt":"","wd":"","inc_wd":"","exc_wd":"","fields":"title;categorynum;zhuanzai;webdate","cnum":"001","sort":"{\"webdate\":\"0\"}","ssort":"title","cl":10000,"terminal":"","condition":[],"time":[{"fieldName":"webdate","startTime":"","endTime":""}],"highlights":"title","statistics":null,"unionCondition":[{"fieldName":"categorynum","isLike":true,"likeType":2,"equal":"002002001"},{"fieldName":"categorynum","isLike":true,"likeType":2,"equal":"014002001"}],"accuracy":"","noParticiple":"1","searchRange":null,"isBusiness":"1"}'
        ]);
        if($res !== false) {
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
                            $data['bidSiteId'] = 1014;
                            $data['bidSiteName'] = '兰州市公共资源交易中心';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = 'https://lzggzyjy.lanzhou.gov.cn'.$v['linkurl'];
                            $data['beginDate'] = $v['infodate'];
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 1014 -> 兰州市公共资源交易中心');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 1014 -> 兰州市公共资源交易中心');
        }
        // 1020 马鞍山市公共资源交易中心
        $url = 'https://zbcg.mas.gov.cn/inteligentsearch_es/rest/esinteligentsearch/getFullTextDataNew';
        $res = global_curl($url, [
            'post' => 1,
            'postfields' => '{"token":"","pn":0,"rn":15,"sdt":"","edt":"","wd":"","inc_wd":"","exc_wd":"","fields":"title;categorynum;projectindustry;projectnature","cnum":"002","sort":"{\"webdatenew\":\"0\"}","ssort":"title","cl":300,"terminal":"","condition":[],"time":[],"highlights":"","statistics":null,"unionCondition":[{"fieldName":"categorynum","isLike":true,"likeType":2,"equal":"002002001004"},{"fieldName":"categorynum","isLike":true,"likeType":2,"equal":"002002002004"},{"fieldName":"categorynum","isLike":true,"likeType":2,"equal":"002002003004"},{"fieldName":"categorynum","isLike":true,"likeType":2,"equal":"002002004004"}],"accuracy":"","noParticiple":"1","searchRange":null,"isBusiness":"1"}'
        ]);
        if($res !== false) {
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
                            $data['bidSiteId'] = 1020;
                            $data['bidSiteName'] = '马鞍山市公共资源交易中心';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = 'https://zbcg.mas.gov.cn/masggzynew'.$v['linkurl'];
                            $data['beginDate'] = $v['webdate'];
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 1020 -> 马鞍山市公共资源交易中心');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 1020 -> 马鞍山市公共资源交易中心');
        }
        // 1073 阜宁县公共资源电子化服务平台
        $url = 'http://222.188.117.130:17172/jyxx/001005/about.html';
        $res = global_curl($url);
        if($res !== false) {
            $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
            $preg = '#<tr><tdclass="xuhao">.*<ahref="(.*)"title="(.*)".*</a></div></td><td>(.*)</td></tr>#isU';
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
                            $data['bidSiteId'] = 1073;
                            $data['bidSiteName'] = '阜宁县公共资源电子化服务平台';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = strpos($v['url'], 'http') === 0 ? $v['url'] : ('http://222.188.117.130:17172'.$v['url']);
                            $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 1073 -> 阜宁县公共资源电子化服务平台');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 1073 -> 阜宁县公共资源电子化服务平台');
        }
        // 1074 无锡市教育局
        $url = 'https://jy.wuxi.gov.cn/gkzbxx/index.shtml';
        $res = global_curl($url);
        if($res !== false) {
            $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
            $res_temp = [];
            preg_match('#<ulclass="list03list03_sp">(.*)</ul>#isU', $res, $res_temp);
            $preg = '#<li><ahref="(.*)".*title="(.*)".*</a><span.*>(.*)</span></li>#isU';
            $obj = [];
            $arr = [];
            is_countable($res_temp) && count($res_temp) > 0 && preg_match_all($preg, $res_temp[1], $obj);
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
                            $data['bidSiteId'] = 1074;
                            $data['bidSiteName'] = '无锡市教育局';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = strpos($v['url'], 'http') === 0 ? $v['url'] : ('https://jy.wuxi.gov.cn'.$v['url']);
                            $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 1074 -> 无锡市教育局');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 1074 -> 无锡市教育局');
        }
        // 1077 南通市海门区公共资源交易中心采购限额以下公共资源智慧交易及一体化监管平台
        $url = 'http://xe.hmdzzw.cn/jygk/001006/001006001/aboultjyxx.html';
        $res = global_curl($url);
        if ($res !== false) {
            $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
            $res_temp = [];
            preg_match('#<tbodyid="infoContent">(.*)</tbody>#isU', $res, $res_temp);
            $preg = '#<tr.*<ahref="(.*)"title="(.*)".*</a></td><td.*</td><tdclass="ewb-trade-td">(.*)</td></tr>#isU';
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
                            $data['bidSiteId'] = 1077;
                            $data['bidSiteName'] = '南通市海门区公共资源交易中心采购限额以下公共资源智慧交易及一体化监管平台';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = 'http://xe.hmdzzw.cn' . $v['url'];
                            $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 1077 -> 南通市海门区公共资源交易中心采购限额以下公共资源智慧交易及一体化监管平台');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 1077 -> 南通市海门区公共资源交易中心采购限额以下公共资源智慧交易及一体化监管平台');
        }
        // 1115 吉林省政府采购网 【特殊通过浏览器刷新插件+自定义js获取数据后发送到本系统api并存数据库，每次采集从数据库拿】
        $res = $this->db->table('bid_temp')->select('res')->where('site', '吉林省政府采购网')->get()->getRowArray()['res'] ?? '';
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
                            $data['bidSiteId'] = 1115;
                            $data['bidSiteName'] = '吉林省政府采购网';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = 'http://www.ccgp-jilin.gov.cn/site/detail?parentId=550068&articleId='.str_replace(' ', '+', $v['articleId']);
                            $data['beginDate'] = date('Y-m-d H:i:s', intval($v['publishDate'] / 1000));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 1115 -> 吉林省政府采购网');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 1115 -> 吉林省政府采购网');
        }
        // 1198 长沙市政府采购网
        $url = 'http://changs.ccgp-hunan.gov.cn/gp/gpcategory/getNotice';
        $res = global_curl($url, [
            'post' => 1,
            'postfields' => 'page=1&limit=100&sidx=&order=&categoryId=225&articleType=0&projid=&name=&cgType=0&buyerNm=&buyerOrgNm=&supplyNm=&basicType=1&basicDatetime='.date('Y-m-d', strtotime('-3 day')).'&basicDatetimes='.date('Y-m-d').'&type=0&basicArea=0&categoryType=0',
            'httpheader' => [
                'Accept: application/json, text/javascript, */*; q=0.01',
                'Accept-Encoding: gzip, deflate',
                'Accept-Language: zh-CN,zh;q=0.9,en;q=0.8,en-GB;q=0.7,en-US;q=0.6',
                'Cache-Control: no-cache',
                'Connection: keep-alive',
                'Content-Length: 209',
                'Content-Type: application/x-www-form-urlencoded',
                'Host: changs.ccgp-hunan.gov.cn',
                'Origin: http://changs.ccgp-hunan.gov.cn',
                'Pragma: no-cache',
                'Referer: http://changs.ccgp-hunan.gov.cn/gp/noticeSerach.html?basicArea=changsha&articleType=3',
                'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0'
            ]
        ]);
        if($res !== false) {
            $obj = json_decode($res, true);
            $arr = $obj['list']['list'] ?? [];
            if (is_array($arr) && count($arr) > 0) {
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['basicTitle'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['basicTitle'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 1198;
                            $data['bidSiteName'] = '长沙市政府采购网';
                            $data['bidTitle'] = $v['basicTitle'];
                            $data['bidUrl'] = 'http://changs.ccgp-hunan.gov.cn/gp/showNotice.html?basicId='.$v['basicId'].'&articleType='.$v['articleType'].'&basicArea='.$v['area'];
                            $data['beginDate'] = $v['basicDatetime'];
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 1198 -> 长沙市政府采购网');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 1198 -> 长沙市政府采购网');
        }
        // 1215 黑龙江省政府采购电子卖场
        $url = 'https://hljcg.hlj.gov.cn/proxy/trade-service/mallCgjh/infomation/queryMallNoticePageList?pageNum=1&pageSize=200&noticeStatus=10&noticeTypes=1&effectType=11&noticeTitle=&startTime=&endTime=';
        $res = global_curl($url);
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
                            $data['bidSiteId'] = 1215;
                            $data['bidSiteName'] = '黑龙江省政府采购电子卖场';
                            $data['bidTitle'] = $v['noticeTitle'];
                            $data['bidUrl'] = 'https://hljcg.hlj.gov.cn/mall-view/information/detail?noticeId='.$v['id'];
                            $data['beginDate'] = $v['publishTimeStr'];
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 1215 -> 黑龙江省政府采购电子卖场');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 1215 -> 黑龙江省政府采购电子卖场');
        }
        // 1217 西藏公共资源交易平台
        for ($i = 1; $i <= 5; $i++) {
            $url = 'https://ggzy.xizang.gov.cn/search/queryContents_'.$i.'.jhtml';
            $res = global_curl($url, [
                'post' => 1,
                'postfields' => 'title=&channelId=3547&areaNo=&inDates=&timeBegin=&timeEnd=',
                'httpheader' => [
                    'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7',
                    'Accept-Encoding: gzip, deflate, br, zstd',
                    'Accept-Language: zh-CN,zh;q=0.9,en;q=0.8,en-GB;q=0.7,en-US;q=0.6',
                    'Cache-Control: no-cache',
                    'Connection: keep-alive',
                    'Content-Length: 58',
                    'Content-Type: application/x-www-form-urlencoded',
                    'Cookie: _site_id_cookie=22; arialoadData=true; ariawapChangeViewPort=true; JIDENTITY=0da153aa-3021-4fe2-9c1d-15a7929475c3; ariauseGraymode=false',
                    'Host: ggzy.xizang.gov.cn',
                    'Origin: https://ggzy.xizang.gov.cn',
                    'Pragma: no-cache',
                    'Referer: https://ggzy.xizang.gov.cn/jyxxzc.jhtml',
                    'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36 Edg/148.0.0.0'
                ]
            ]);
            if($res !== false) {
                $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
                $preg = '#<li><ponclick="window.open\(\'(.*)\'\)"><span.*</span><span>(.*)</span></p><p>(.*)</p></li>#isU';
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
                                $data['bidSiteId'] = 1217;
                                $data['bidSiteName'] = '西藏公共资源交易平台';
                                $data['bidTitle'] = $v['title'];
                                $data['bidUrl'] = strpos($v['url'], 'http') === 0 ? $v['url'] : ('https://ggzy.xizang.gov.cn'.str_replace('..', '', $v['url']));
                                $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                                $data['inDate'] = date('Y-m-d H:i:s');
                                $final_arr[] = $data;
                            }
                        }
                    }
                } else {
                    log_message('error', '招标采集：未匹配到数据 1217 -> 西藏公共资源交易平台 -> 第'.$i.'页');
                }
            } else {
                log_message('error', '招标采集：未获取到网页 1217 -> 西藏公共资源交易平台 -> 第'.$i.'页');
            }
        }
        // 1260 上海市公共资源交易中心
        $url = 'https://www.shggzy.com/jyxxwzcg';
        $res = global_curl($url);
        if ($res !== false) {
            $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
            $preg = '#<lionclick="window.open\(\'(.*)\'\)"><span.*<span.*>(.*)</span><span.*</span><span>(.*)</span></li>#isU';
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
                            $data['bidSiteId'] = 1260;
                            $data['bidSiteName'] = '上海市公共资源交易中心';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = strpos($v['url'], 'http') === 0 ? $v['url'] : ('https://www.shggzy.com' . $v['url']);
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
                log_message('error', '招标采集：未匹配到数据 1260 -> 上海市公共资源交易中心');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 1260 -> 上海市公共资源交易中心');
        }
        // 1274 辽宁政府采购网
        $url = 'http://www.ccgp-liaoning.gov.cn/gateway/esservice/homePage/getHomePunInfoList';
        $res = global_curl($url, [
            'post' => 1,
            'postfields' => '{"title":"","planNo":"","district":"[]","infoTypeCode":"[\"1001\",\"100101\",\"100102\",\"100103\",\"100104\",\"100105\",\"100106\",\"100107\",\"1401\"]","current":1,"rowCount":50}'
        ]);
        if ($res !== false) {
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
                            $data['bidSiteId'] = 1274;
                            $data['bidSiteName'] = '辽宁政府采购网';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = 'http://www.ccgp-liaoning.gov.cn/portalDetails?infoId='.$v['id'].'&infoTypeCode='.$v['infoTypeCode'];
                            $data['beginDate'] = $v['insDatetime'];
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 1274 -> 辽宁政府采购网');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 1274 -> 辽宁政府采购网');
        }
        // 1280 南通市教育局
        $url = 'https://jyj.nantong.gov.cn/ntsjyj/zfcg/zfcg.html';
        $res = global_curl($url);
        if ($res !== false) {
            $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
            $preg = '#<ulclass="list-ul"><li.*<a.*href="(.*)".*>(.*)</a><span>(.*)</span></li></ul>#isU';
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
                            $data['bidSiteId'] = 1280;
                            $data['bidSiteName'] = '南通市教育局';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = strpos($v['url'], 'http') === 0 ? $v['url'] : ('https://jyj.nantong.gov.cn' . $v['url']);
                            $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 1280 -> 南通市教育局');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 1280 -> 南通市教育局');
        }
        // 1289 句容教育信息网
        $url = 'http://www.jre.net.cn/allStation/jrjy/www_jre_net_cn/subject.jsp?webSubjectId=www_jre_net_cn_5&currentPage=1';
        $res = global_curl($url);
        if ($res !== false) {
            $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
            $preg = '#<tr><tdclass="titleClass_title">.*<a.*href=\'(.*)\'.*title=\'(.*)\'.*</a>.*<a.*>(.*)</a></td></tr>#isU';
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
                            $data['bidSiteId'] = 1289;
                            $data['bidSiteName'] = '句容教育信息网';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = strpos($v['url'], 'http') === 0 ? $v['url'] : ('http://www.jre.net.cn/allStation/jrjy/www_jre_net_cn/' . $v['url']);
                            $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 1289 -> 句容教育信息网');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 1289 -> 句容教育信息网');
        }
        // 1290 中山市东区街道电子采购交易平台
        $url = 'https://cg.zsdqzw.cn/dqcg/web/api/door/get-project-list';
        $res = global_curl($url, [
            'post' => 1,
            'postfields' => 'page=1&limit=10&type=1'
        ]);
        if ($res !== false) {
            $obj = json_decode($res, true);
            $arr = $obj['data'] ?? [];
            if (is_array($arr) && count($arr) > 0) {
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['name'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['name'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 1290;
                            $data['bidSiteName'] = '中山市东区街道电子采购交易平台';
                            $data['bidTitle'] = $v['name'];
                            $data['bidUrl'] = 'https://cg.zsdqzw.cn/dqcg/web/dist/#/projectDetail?id='.$v['id'].'&is_show=1&type=1';
                            $data['beginDate'] = date('Y-m-d H:i:s', $v['add_time']);
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 1290 -> 中山市东区街道电子采购交易平台');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 1290 -> 中山市东区街道电子采购交易平台');
        }
        // 1314 罗湖区教育系统自行采购公告网
        $url = 'https://zhaobiao.luohuedu.net/m/new/3515-0-0-1';
        $res = global_curl($url, [
            'header' => 1
        ]);
        if ($res !== false) {
            $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
            if (strpos($res, 'Set-Cookie:') !== false) {
                $cookie_1314 = [];
                preg_match_all('#Set-Cookie:(.*?);#isU', $res, $cookie_1314);
                if (is_countable($cookie_1314) && count($cookie_1314) > 0) {
                    $cookie_1314 = implode(';', $cookie_1314[1]);
                    $res = global_curl($url, [
                        'header' => 1,
                        'httpheader' => [
                            'Cookie: ' . $cookie_1314
                        ]
                    ]);
                    $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
                }
            }
            $preg = '#<tr><tdstyle="height:40px;"><a.*href=\'(.*)\'.*>(.*)</a>\((.*)\)</td></tr>#isU';
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
                            $data['bidSiteId'] = 1314;
                            $data['bidSiteName'] = '罗湖区教育系统自行采购公告网';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = strpos($v['url'], 'http') === 0 ? $v['url'] : ('https://zhaobiao.luohuedu.net' . $v['url']);
                            $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 1314 -> 罗湖区教育系统自行采购公告网');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 1314 -> 罗湖区教育系统自行采购公告网');
        }
        // 1321 广东政务采购与招标平台
        $url = 'http://www.internationalzb.com/index.php?m=content&c=index&a=lists&catid=7';
        $res = global_curl($url);
        if ($res !== false) {
            $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
            $preg = '#<tr><tdvalign="top"><divclass="new_title"><a.*href="(.*)".*>(.*)</a></div><divclass="date">发布时间：(.*)</div></td></tr>#isU';
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
                            $data['bidSiteId'] = 1321;
                            $data['bidSiteName'] = '广东政务采购与招标平台';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = $v['url'];
                            $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 1321 -> 广东政务采购与招标平台');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 1321 -> 广东政务采购与招标平台');
        }
        // 1327 溧阳教育公共服务平台
        $url = 'http://www.lyjyfw.net/html/NewList-474.html';
        $res = global_curl($url, [
            'httpheader' => [
                'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7',
                'Accept-Encoding: gzip, deflate',
                'Accept-Language: zh-CN,zh;q=0.9,en;q=0.8,en-GB;q=0.7,en-US;q=0.6',
                'Cache-Control: no-cache',
                'Connection: keep-alive',
                'Host: www.lyjyfw.net',
                'Pragma: no-cache',
                'Upgrade-Insecure-Requests: 1',
                'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36 Edg/150.0.0.0'
            ]
        ]);
        if ($res !== false) {
            $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
            $res_temp = [];
            preg_match('#<ulid="YH_PageList">(.*)</ul>#isU', $res, $res_temp);
            $preg = '#<li.*<a.*href="(.*)".*title="(.*)".*</a><h4>\[(.*)\]</h4></li>#isU';
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
                            $data['bidSiteId'] = 1327;
                            $data['bidSiteName'] = '溧阳教育公共服务平台';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = strpos($v['url'], 'http') === 0 ? $v['url'] : ('http://www.lyjyfw.net' . $v['url']);
                            $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 1327 -> 溧阳教育公共服务平台');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 1327 -> 溧阳教育公共服务平台');
        }
        // 1333 山西省政府采购网
        $url = 'http://www.ccgp-shanxi.gov.cn/portal/category';
        $res = global_curl($url, [
            'post' => 1,
            'postfields' => '{"pageNo":1,"pageSize":100,"categoryCode":"ZcyAnnouncement1","_t":'.intval(microtime(true) * 1000).',"publishDateBegin":"'.date('Y-m-d', strtotime('-7 days')).'","publishDateEnd":"'.date('Y-m-d').'"}'
        ]);
        if ($res !== false) {
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
                            $data['bidSiteId'] = 1333;
                            $data['bidSiteName'] = '山西省政府采购网';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = 'http://www.ccgp-shanxi.gov.cn/site/detail?parentId=138010&articleId='.$v['articleId'];
                            $data['beginDate'] = date('Y-m-d H:i:s', intval($v['publishDate']) / 1000);
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 1333 -> 山西省政府采购网');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 1333 -> 山西省政府采购网');
        }
        // 1342 晋江市教育系统采购管理服务平台
        $url = 'https://jjsjyj.gcycloud.cn/freecms/rest/v1/notice/selectInfoMoreChannel.do?&siteId=e9148e3273316374308c2f8a1a061c63&channel=c2600800b52d848f0deefda10d1a13d6&currPage=1&pageSize=16&noticeType=00101&regionCode=350582&purchaseManner=&title=&purchaseNature=1&selectTimeName=noticeTime&orgGuid=&selectType=';
        $res = global_curl($url, [
            'httpheader' => [
                'Accept: */*',
                'Accept-Encoding: gzip, deflate, br, zstd',
                'Accept-Language: zh-CN,zh;q=0.9,en;q=0.8,en-GB;q=0.7,en-US;q=0.6',
                'Cache-Control: no-cache',
                'Connection: keep-alive',
                'Content-Type: application/json;charset=utf-8',
                'Host: jjsjyj.gcycloud.cn',
                'Pragma: no-cache',
                'Referer: https://jjsjyj.gcycloud.cn/freecms/site/jjsjyj/cggg/index.html',
                'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36 Edg/150.0.0.0'
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
                            $data['bidSiteId'] = 1342;
                            $data['bidSiteName'] = '晋江市教育系统采购管理服务平台';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = 'https://jjsjyj.gcycloud.cn/freecms/site/jjsjyj/cggg/index.html?infoid='.$v['htmlIndexnum'].'&pageUrl='.$v['pageurl'].'&noticeType='.$v['noticeType'];
                            $data['beginDate'] = $v['noticeTime'];
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 1342 -> 晋江市教育系统采购管理服务平台');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 1342 -> 晋江市教育系统采购管理服务平台');
        }
        // 1356 湘西公共资源交易网
        $url = 'http://www.xxzggzyjy.cn/jyxx/005002/005002001/aboutjyxxsearch.html';
        $res = global_curl($url);
        if ($res !== false) {
            $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
            $res_temp = [];
            preg_match('#<divid="static"><ulclass="wb-data-item">(.*)</ul>#isU', $res, $res_temp);
            $preg = '#<li.*<a.*href="(.*)".*title="(.*)".*</a></div><spanclass="wb-data-date">(.*)</span></li>#isU';
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
                            $data['bidSiteId'] = 1356;
                            $data['bidSiteName'] = '湘西公共资源交易网';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = strpos($v['url'], 'http') === 0 ? $v['url'] : ('http://www.xxzggzyjy.cn' . str_replace('../', '', $v['url']));
                            $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 1356 -> 湘西公共资源交易网');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 1356 -> 湘西公共资源交易网');
        }
        if ($res !== false) {
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
                            $data['bidSiteId'] = 1362;
                            $data['bidSiteName'] = '黑龙江政府采购电子卖场';
                            $data['bidTitle'] = $v['noticeTitle'];
                            $data['bidUrl'] = 'https://hljcg.hlj.gov.cn/mall-view/information/detail?noticeId='.$v['id'];
                            $data['beginDate'] = $v['publishTimeStr'];
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 1362 -> 黑龙江政府采购电子卖场');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 1362 -> 黑龙江政府采购电子卖场');
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
            $this->db->table('option')->where('optionName', 'bid_spider_0_3_status')->update(['optionValue' => 0]);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                log_message('error', '招标采集：招标信息入库失败（招标政府类）事务已回滚！');
            }
        }
    }
}
