<?php

namespace App\Controllers\Api;
use App\Controllers\BaseController;

class SpiderBid11 extends BaseController
{
    /* 招标密集类 按每1分钟执行一次的标准设计 */
    public function index()
    {
        $this->db->table('option')->where('optionName', 'bid_spider_1_1_status')->update(['optionValue' => 1]);
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

        // 12 云南省政府采购网 10条
        $url = 'http://www.ccgp-yunnan.gov.cn/api/procurement/Procurement.gghtMoreList.svc?captchaCheckFlag=0&p=1';
        $res = global_curl($url, [
            'post' => 1,
            'postfields' => 'current=1&rowCount=10&searchPhrase=&query_bulletintitle=&query_startTime=&query_endTime=&query_type=1&query_code=&query_gglxdm=&query_purchaser=&query_projectid=',
            'httpheader' => [
                'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7',
                'Accept-Language: zh-CN,zh;q=0.9,en;q=0.8,en-GB;q=0.7,en-US;q=0.6',
                'Cache-Control: no-cache',
                'Connection: keep-alive',
                'Host: www.ccgp-yunnan.gov.cn',
                'Pragma: no-cache',
                'Upgrade-Insecure-Requests: 1',
                'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/114.0.0.0 Safari/537.36 Edg/114.0.1823.82'
            ]
        ]);
        if ($res !== false) {
            $obj = json_decode($res, true);
            $arr = $obj['data']['rows'] ?? [];
            if (is_array($arr) && count($arr) > 0) {
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['bulletintitle'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['bulletintitle'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 12;
                            $data['bidSiteName'] = '云南省政府采购网';
                            $data['bidTitle'] = $v['bulletintitle'];
                            $data['bidUrl'] = 'http://www.ccgp-yunnan.gov.cn/showBulletinInfo.html?bulletin_id=' . $v['bulletin_id'];
                            $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['finishday']));
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 12 -> 云南省政府采购网');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 12 -> 云南省政府采购网');
        }
        // 23 政采云在线询价湖南 【特殊通过浏览器刷新插件+自定义js获取数据后发送到本系统api并存数据库，每次采集从数据库拿】
        $res = $this->db->table('bid_temp')->select('res')->where('site', '政采云在线询价湖南')->get()->getRowArray()['res'] ?? '';
        if($res) {
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
                            $data['bidUrl'] = 'https://hunan.zcygov.cn/bidding/detail?requisitionId='.$v['requisitionId'].'&type='.$v['type'];
                            $data['beginDate'] = date('Y-m-d H:i:s', intval($v['pubTimestamp']) / 1000);
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 23 -> 政采云 -> 政采云在线询价湖南');
            }
        } else {
            log_message('error', '招标采集：未从数据库中获取到保存的res 23 -> 政采云 -> 政采云在线询价湖南');
        }
        // 27 浙江省政府采购网 300条
        $url = 'https://zfcg.czt.zj.gov.cn/portal/category';
        // 政府采购公告 采购项目公告
        $res = global_curl($url, [
            'post' => 1,
            'postfields' => '{"pageNo":1,"pageSize":99,"categoryCode":"110-978863","isGov":true,"excludeDistrictPrefix":["90","006011","H0"],"_t":'.intval(microtime(true) * 1000).'}'
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
                            $data['bidSiteId'] = 27;
                            $data['bidSiteName'] = '浙江省政府采购网';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = 'http://www.ccgp-zhejiang.gov.cn/site/detail?parentId=600007&articleId='.$v['articleId'];
                            $data['beginDate'] = date('Y-m-d H:i:s', intval($v['publishDate']) / 1000);
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 27 -> 浙江省政府采购网 -> 栏目 -> 政府采购公告 采购项目公告');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 27 -> 浙江省政府采购网 -> 栏目 -> 政府采购公告 采购项目公告');
        }
        // 政府采购公告 电子卖场公告
        $res = global_curl($url, [
            'post' => 1,
            'postfields' => '{"pageNo":1,"pageSize":99,"categoryCode":"110-341071","isGov":true,"excludeDistrictPrefix":["90","006011","H0"],"_t":'.intval(microtime(true) * 1000).'}'
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
                            $data['bidSiteId'] = 27;
                            $data['bidSiteName'] = '浙江省政府采购网';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = 'http://www.ccgp-zhejiang.gov.cn/site/detail?parentId=600007&articleId='.$v['articleId'];
                            $data['beginDate'] = date('Y-m-d H:i:s', intval($v['publishDate']) / 1000);
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 27 -> 浙江省政府采购网 -> 栏目 -> 政府采购公告 电子卖场公告');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 27 -> 浙江省政府采购网 -> 栏目 -> 政府采购公告 电子卖场公告');
        }
        // 非政府采购公告 其他非政府采购公告
        $res = global_curl($url, [
            'post' => 1,
            'postfields' => '{"pageNo":1,"pageSize":99,"categoryCode":"110-774650","includeGovDistrict":1,"_t":'.intval(microtime(true) * 1000).'}'
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
                            $data['bidSiteId'] = 27;
                            $data['bidSiteName'] = '浙江省政府采购网';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = 'http://www.ccgp-zhejiang.gov.cn/site/detail?parentId=600007&articleId='.$v['articleId'];
                            $data['beginDate'] = date('Y-m-d H:i:s', intval($v['publishDate']) / 1000);
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 27 -> 浙江省政府采购网 -> 栏目 -> 非政府采购公告 其他非政府采购公告');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 27 -> 浙江省政府采购网 -> 栏目 -> 非政府采购公告 其他非政府采购公告');
        }
        // 28 福建省政府采购网 【特殊 需模拟加密生成验证签名sign后传到它的后台进行鉴权】
        $urls = [
            '项目公告省级' => 'https://zfcg.czt.fujian.gov.cn/gpcms/rest/web/v2/info/selectInfoMoreChannel?siteId=d36a6e8b-4363-4b52-a00b-79ca47033923&channel=f582600e-065d-4f35-8966-48a33fa93863&currPage=1&pageSize=50&regionCode=350001&noticeType=00101&cityOrArea=&purchaseManner=&organizationForm=!4&_t='.intval(microtime(true) * 1000),
            '项目公告市级' => 'https://zfcg.czt.fujian.gov.cn/gpcms/rest/web/v2/info/selectInfoMoreChannel?siteId=d36a6e8b-4363-4b52-a00b-79ca47033923&channel=f582600e-065d-4f35-8966-48a33fa93863&currPage=1&pageSize=50&regionCode=!350001&noticeType=00101&cityOrArea=&purchaseManner=&organizationForm=!4&_t='.intval(microtime(true) * 1000),
            '项目公告县级' => 'https://zfcg.czt.fujian.gov.cn/gpcms/rest/web/v2/info/selectInfoMoreChannel?siteId=d36a6e8b-4363-4b52-a00b-79ca47033923&channel=f582600e-065d-4f35-8966-48a33fa93863&currPage=1&pageSize=50&regionCode=!350001&noticeType=00101&cityOrArea=3&purchaseManner=&organizationForm=!4&_t='.intval(microtime(true) * 1000)
        ];
        foreach ($urls as $index => $url) {
            $time = intval(microtime(true) * 1000);
            $sign = md5(sha1($time . '_/gpcms/rest/web/v2/info/selectInfoMoreChannel_bosssoft_platform_095285'));
            $res = global_curl($url, [
                'httpheader' => [
                    'Accept: */*',
                    'Accept-Encoding: gzip, deflate, br, zstd',
                    'Accept-Language: zh-CN,zh;q=0.9,en;q=0.8,en-GB;q=0.7,en-US;q=0.6',
                    'Cache-Control: no-cache',
                    'Connection: keep-alive',
                    'Cookie: regionCode=350001; regionRemark=1; regionFullName=%E7%A6%8F%E5%BB%BA%E7%9C%81%E6%9C%AC%E7%BA%A7; changeRegion=%E7%A6%8F%E5%BB%BA',
                    'Host: zfcg.czt.fujian.gov.cn',
                    'Pragma: no-cache',
                    'sign: ' . $sign,
                    'time: ' . $time,
                    'url: /gpcms/rest/web/v2/info/selectInfoMoreChannel',
                    'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36 Edg/131.0.0.0'
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
                                $data['bidSiteId'] = 28;
                                $data['bidSiteName'] = '福建省政府采购网';
                                $data['bidTitle'] = $v['title'];
                                $data['bidUrl'] = 'https://zfcg.czt.fujian.gov.cn/maincms-web/articleDetail?id='.$v['id'].'&titleType=xmgg&planId='.$v['planId'].'&type=notice&soure=ggxx';
                                $data['beginDate'] = $v['noticeTime'];
                                $data['inDate'] = date('Y-m-d H:i:s');
                                $final_arr[] = $data;
                            }
                        }
                    }
                } else {
                    log_message('error', '招标采集：未匹配到数据 28 -> 福建省政府采购网 -> '.$index);
                }
            } else {
                log_message('error', '招标采集：未获取到网页 28 -> 福建省政府采购网 -> '.$index);
            }
        }
        // 169 青海省政府采购网 80条
        $url = 'http://www.ccgp-qinghai.gov.cn/portal/searchHome';
        $pars = [
            '省级 公招' => '{"code":"ZcyAnnouncement3001","subCodes":["ZcyAnnouncement3001"],"isProvince":true,"districtCode":"639900","pageSize":16,"needTotal":true,"needValidCount":false,"needNewCnt":true}',
            '省级 邀请' => '{"code":"ZcyAnnouncement3009","subCodes":["ZcyAnnouncement3009"],"isProvince":true,"districtCode":"639900","pageSize":16,"needTotal":true,"needValidCount":false,"needNewCnt":true}',
            '省级 竞谈' => '{"code":"ZcyAnnouncement3002","subCodes":["ZcyAnnouncement3002"],"isProvince":true,"districtCode":"639900","pageSize":16,"needTotal":true,"needValidCount":false,"needNewCnt":true}',
            '省级 竞磋' => '{"code":"ZcyAnnouncement3011","subCodes":["ZcyAnnouncement3011"],"isProvince":true,"districtCode":"639900","pageSize":16,"needTotal":true,"needValidCount":false,"needNewCnt":true}',
            '省级 询价' => '{"code":"ZcyAnnouncement3003","subCodes":["ZcyAnnouncement3003"],"isProvince":true,"districtCode":"639900","pageSize":16,"needTotal":true,"needValidCount":false,"needNewCnt":true}',
            '市级 公招' => '{"code":"ZcyAnnouncement3001","subCodes":["ZcyAnnouncement3001"],"isProvince":false,"districtCode":"639900","pageSize":16,"needTotal":true,"needValidCount":false,"needNewCnt":true}',
            '市级 邀请' => '{"code":"ZcyAnnouncement3009","subCodes":["ZcyAnnouncement3009"],"isProvince":false,"districtCode":"639900","pageSize":16,"needTotal":true,"needValidCount":false,"needNewCnt":true}',
            '市级 竞谈' => '{"code":"ZcyAnnouncement3002","subCodes":["ZcyAnnouncement3002"],"isProvince":false,"districtCode":"639900","pageSize":16,"needTotal":true,"needValidCount":false,"needNewCnt":true}',
            '市级 竞磋' => '{"code":"ZcyAnnouncement3011","subCodes":["ZcyAnnouncement3011"],"isProvince":false,"districtCode":"639900","pageSize":16,"needTotal":true,"needValidCount":false,"needNewCnt":true}',
            '市级 询价' => '{"code":"ZcyAnnouncement3003","subCodes":["ZcyAnnouncement3003"],"isProvince":false,"districtCode":"639900","pageSize":16,"needTotal":true,"needValidCount":false,"needNewCnt":true}'
        ];
        foreach ($pars as $index => $par) {
            $res = global_curl($url, [
                'post' => 1,
                'postfields' => $par
            ]);
            if ($res !== false) {
                $obj = json_decode($res, true);
                $arr = $obj['result']['data']['children'] ?? [];
                if (is_array($arr) && count($arr) > 0) {
                    foreach ($arr as $k => $v) {
                        $data = [];
                        if (!preg_match('#(' . $in_keywords . ')#', $v['title'])) {
                            continue;
                        } else {
                            if (preg_match('#(' . $out_keywords . ')#', $v['title'])) {
                                continue;
                            } else {
                                $data['bidSiteId'] = 169;
                                $data['bidSiteName'] = '青海省政府采购网';
                                $data['bidTitle'] = $v['title'];
                                $data['bidUrl'] = 'http://www.ccgp-qinghai.gov.cn/site/detail?articleId=' . $v['articleId'];
                                $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['publishDateString']));
                                $data['inDate'] = date('Y-m-d H:i:s');
                                $final_arr[] = $data;
                            }
                        }
                    }
                } else {
                    log_message('error', '招标采集：未匹配到数据 169 -> 青海省政府采购网 -> ' . $index);
                }
            } else {
                log_message('error', '招标采集：未获取到网页 169 -> 青海省政府采购网 -> ' . $index);
            }
        }
        // 196 优质采
        $url = 'https://www.youzhicai.com/s/0_0_0_0_.html?key=';
        for ($i = 1; $i <= 2; $i++) {
            $res = global_curl($url, [
                'post' => 1,
                'postfields' => '__RequestVerificationToken=Zhs688mUR9tceP5wpSUXx5ub_xCOEl8kq2U9TWiH0xhNO_6O5JHqPAXC3OxpdvJRtnLClz4g0Jl_Lwmp6AmIGBl48IISvRJ4Lc_1b8NJqNcPNEHiYOfe0ojO5Y34C39gDuxLTg2&MsProvince=&MsCity=&MsStartDate=&MsEndDate=&AutoOr=0&BackOr=0&NoticeTitle=&searchAccuracy=precise&matchType=precise&TenderType=&MsBidderType=0&MsNoticeType=1&MsPublishType=3&MsSingUpType=0&MsSort=2&MsProvince=&PageIndex='.$i.'&PageSize=15&AgencyId=&SecondSearch=&SecondSearchType=%E6%90%9C%E6%8B%9B%E6%A0%87%E5%8D%95%E4%BD%8D&TotalSize=745829&SearchRange=0&year=&key1=&key2=&key3=',
                'httpheader' => [
                    'accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7',
                    'accept-encoding: gzip, deflate, br, zstd',
                    'accept-language: zh-CN,zh;q=0.9,en;q=0.8,en-GB;q=0.7,en-US;q=0.6',
                    'cache-control: no-cache',
                    'content-length: 538',
                    'content-type: application/x-www-form-urlencoded',
                    'origin: https://www.youzhicai.com',
                    'pragma: no-cache',
                    'referer: https://www.youzhicai.com/s/0_0_0_0_.html?key=',
                    'user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36 Edg/131.0.0.0'
                ]
            ]);
            if ($res !== false) {
                $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
                $preg = '#<liclass="project-liclearfix".*<a.*href="(.*)".*title="(.*)".*</a><span.*>(.*)</span>.*</li>#isU';
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
                                $data['bidSiteId'] = 196;
                                $data['bidSiteName'] = '优质采';
                                $data['bidTitle'] = $v['title'];
                                $data['bidUrl'] = 'https://www.youzhicai.com' . $v['url'];
                                $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                                $data['inDate'] = date('Y-m-d H:i:s');
                                $final_arr[] = $data;
                            }
                        }
                    }
                } else {
                    log_message('error', '招标采集：未匹配到数据 196 -> 优质采 第' . $i . '页');
                }
            } else {
                log_message('error', '招标采集：未获取到网页 196 -> 优质采 第' . $i . '页');
            }
        }
        // 241 诚E招电子采购交易平台
        for ($i = 1; $i <= 5; $i++) {
            $url = 'https://www.chengezhao.com/cms/categories/%E4%B8%9A%E5%8A%A1%E5%85%AC%E5%91%8A/%E9%A1%B9%E7%9B%AE%E5%85%AC%E5%91%8A/' . ($i > 1 ? 'page/' . $i . '/' : '');
            $res = global_curl($url);
            if ($res !== false) {
                $res = preg_replace("/([\r|\n|\t| ]+)/", '', $res);
                $preg = '#<divclass="cez-business-main__news-itemcez-business-main__news-item--active"><aclass="cez-business-main__news-item-date"href="(.*)"><span>(.*)</span><span>(.*)</span></a><divclass="cez-business-main__news-item-divider"></div><divclass="cez-business-main__news-item-content"><h3><ahref=".*">(.*)</a></h3>#isU';
                $obj = [];
                $arr = [];
                preg_match_all($preg, $res, $obj);
                $count = (is_countable($obj) && count($obj) > 0) ? count($obj[0]) : 0;
                if ($count > 0) {
                    for ($j = 0; $j < $count; $j++) {
                        $arr[$j]['url'] = $obj[1][$j];
                        $arr[$j]['title'] = $obj[4][$j];
                        $arr[$j]['beginDate'] = $obj[3][$j].'-'.$obj[2][$j];
                    }
                    foreach ($arr as $k => $v) {
                        $data = [];
                        if (!preg_match('#(' . $in_keywords . ')#', $v['title'])) {
                            continue;
                        } else {
                            if (preg_match('#(' . $out_keywords . ')#', $v['title'])) {
                                continue;
                            } else {
                                $data['bidSiteId'] = 241;
                                $data['bidSiteName'] = '诚E招电子采购交易平台';
                                $data['bidTitle'] = $v['title'];
                                $data['bidUrl'] = 'https://www.chengezhao.com'.$v['url'];
                                $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['beginDate']));
                                $data['inDate'] = date('Y-m-d H:i:s');
                                $final_arr[] = $data;
                            }
                        }
                    }
                } else {
                    log_message('error', '招标采集：未匹配到数据 241 -> 诚E招电子采购交易平台 -> 第 ' . $i . ' 页');
                }
            } else {
                log_message('error', '招标采集：未从数据库中获取到保存的res 241 -> 诚E招电子采购交易平台 -> 第 ' . $i . ' 页');
            }
            sleep(1);
        }
        // 460 内蒙古自治区政府采购网
        $url = 'https://www.ccgp-neimenggu.gov.cn/zfcgwslave/web/index.php?r=pro/index-cggg';
        $res = global_curl($url);
        if ($res !== false) {
            $obj = json_decode($res, true);
            $arr = $obj['zzq']['list'][2]['post'] ?? []; // 自治区 采购公告
            if (is_array($arr) && count($arr) > 0) {
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['TITLE'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['TITLE'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 460;
                            $data['bidSiteName'] = '内蒙古自治区政府采购网';
                            $data['bidTitle'] = $v['TITLE'];
                            $data['bidUrl'] = 'https://www.ccgp-neimenggu.gov.cn/category/cgggg?tb_id=1&p_id='.$v['wp_mark_id'].'&type=2&notice_guid='.$v['notice_guid'];
                            $data['beginDate'] = $v['SUBDATE'];
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 460 -> 内蒙古自治区政府采购网 -> 自治区 采购公告');
            }
            $arr = $obj['ms']['list'][2]['post'] ?? []; // 盟市 采购公告
            if (is_array($arr) && count($arr) > 0) {
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['TITLE'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['TITLE'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 460;
                            $data['bidSiteName'] = '内蒙古自治区政府采购网';
                            $data['bidTitle'] = $v['TITLE'];
                            $data['bidUrl'] = 'https://www.ccgp-neimenggu.gov.cn/category/cgggg?tb_id=1&p_id='.$v['wp_mark_id'].'&type=2&notice_guid='.$v['notice_guid'];
                            $data['beginDate'] = $v['SUBDATE'];
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 460 -> 内蒙古自治区政府采购网 -> 盟市 采购公告');
            }
            $arr = $obj['qx']['list'][2]['post'] ?? []; // 旗县 采购公告
            if (is_array($arr) && count($arr) > 0) {
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['TITLE'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['TITLE'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 460;
                            $data['bidSiteName'] = '内蒙古自治区政府采购网';
                            $data['bidTitle'] = $v['TITLE'];
                            $data['bidUrl'] = 'https://www.ccgp-neimenggu.gov.cn/category/cgggg?tb_id=1&p_id='.$v['wp_mark_id'].'&type=2&notice_guid='.$v['notice_guid'];
                            $data['beginDate'] = $v['SUBDATE'];
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 460 -> 内蒙古自治区政府采购网 -> 旗县 采购公告');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 460 -> 内蒙古自治区政府采购网');
        }
        // 709 贵州省招标投标公共服务平台
        $url = 'http://ztb.guizhou.gov.cn/api/trade/search?pubDate=all&pubType=all&region=5200&industry=all&prjType=Z&noticeType=all&noticeClassify=all&pageIndex=1&args=';
        $res = global_curl($url);
        if ($res !== false) {
            $obj = json_decode($res, true);
            $arr = $obj['data'] ?? [];
            if (is_array($arr) && count($arr) > 0) {
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['Title'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['Title'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 709;
                            $data['bidSiteName'] = '贵州省招标投标公共服务平台';
                            $data['bidTitle'] = $v['Title'];
                            $data['bidUrl'] = 'http://ztb.guizhou.gov.cn/trade/bulletin/?id='.$v['Id'];
                            $data['beginDate'] = $v['PubDate'];
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 709 -> 贵州省招标投标公共服务平台 -> 货物与服务');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 709 -> 贵州省招标投标公共服务平台 -> 货物与服务');
        }
        $url = 'http://ztb.guizhou.gov.cn/api/trade/search?pubDate=all&pubType=all&region=5200&industry=all&prjType=D01&noticeType=all&noticeClassify=all&pageIndex=1&args=';
        $res = global_curl($url);
        if ($res !== false) {
            $obj = json_decode($res, true);
            $arr = $obj['data'] ?? [];
            if (is_array($arr) && count($arr) > 0) {
                foreach ($arr as $k => $v) {
                    $data = [];
                    if (!preg_match('#(' . $in_keywords . ')#', $v['Title'])) {
                        continue;
                    } else {
                        if (preg_match('#(' . $out_keywords . ')#', $v['Title'])) {
                            continue;
                        } else {
                            $data['bidSiteId'] = 709;
                            $data['bidSiteName'] = '贵州省招标投标公共服务平台';
                            $data['bidTitle'] = $v['Title'];
                            $data['bidUrl'] = 'http://ztb.guizhou.gov.cn/trade/bulletin/?id='.$v['Id'];
                            $data['beginDate'] = $v['PubDate'];
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 709 -> 贵州省招标投标公共服务平台 -> 政府采购 -> 货物');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 709 -> 贵州省招标投标公共服务平台 -> 政府采购 -> 货物');
        }
        // 783 新疆政府采购网
        $url = 'http://www.ccgp-xinjiang.gov.cn/portal/searchHome';
        $pars = [
            '自治区 采购项目公告' => '{"code":"ZcyAnnouncement2","subCodes":["ZcyAnnouncement3001","ZcyAnnouncement3002","ZcyAnnouncement3003","ZcyAnnouncement3008","ZcyAnnouncement3011","ZcyAnnouncement2001"],"isProvince":true,"districtCode":"659900","pageSize":12,"needTotal":true,"needValidCount":false,"needNewCnt":true}',
            '地州 采购项目公告' => '{"code":"ZcyAnnouncement2","subCodes":["ZcyAnnouncement3001","ZcyAnnouncement3002","ZcyAnnouncement3003","ZcyAnnouncement3008","ZcyAnnouncement3011","ZcyAnnouncement2001"],"isProvince":false,"districtCode":"659900","pageSize":12,"needTotal":true,"needValidCount":false,"needNewCnt":true}'
        ];
        foreach ($pars as $index => $par) {
            $res = global_curl($url, [
                'post' => 1,
                'postfields' => $par
            ]);
            if($res !== false) {
                $obj = json_decode($res, true);
                $arr = $obj['result']['data']['children'] ?? [];
                if (is_array($arr) && count($arr) > 0) {
                    foreach ($arr as $k => $v) {
                        $data = [];
                        if (!preg_match('#(' . $in_keywords . ')#', $v['title'])) {
                            continue;
                        } else {
                            if (preg_match('#(' . $out_keywords . ')#', $v['title'])) {
                                continue;
                            } else {
                                $data['bidSiteId'] = 783;
                                $data['bidSiteName'] = '新疆政府采购网';
                                $data['bidTitle'] = $v['title'];
                                $data['bidUrl'] = 'http://www.ccgp-xinjiang.gov.cn/site/detail?parentId=3661&articleId='.$v['articleId'];
                                $data['beginDate'] = date('Y-m-d H:i:s', strtotime($v['publishDateString']));
                                $data['inDate'] = date('Y-m-d H:i:s');
                                $final_arr[] = $data;
                            }
                        }
                    }
                } else {
                    log_message('error', '招标采集：未匹配到数据 783 -> 新疆政府采购网 -> ' . $index);
                }
            } else {
                log_message('error', '招标采集：未获取到网页 783 -> 新疆政府采购网 -> ' . $index);
            }
        }
        // 1049 安徽省政府采购信息服务网 【特殊通过浏览器刷新插件+自定义js获取数据后发送到本系统api并存数据库，每次采集从数据库拿】
        $res = $this->db->table('bid_temp')->select('res')->where('site', '安徽省政府采购信息服务网')->get()->getRowArray()['res'] ?? '';
        if ($res) {
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
                            $data['bidSiteId'] = 1049;
                            $data['bidSiteName'] = '安徽省政府采购信息服务网';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = 'http://www.ahzfcg.com.cn/cms/cmsNewsDetail/'.$v['id'];
                            $data['beginDate'] = $v['pubTime'];
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 1049 -> 安徽省政府采购信息服务网');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 1049 -> 安徽省政府采购信息服务网');
        }
        // 1357 国资智采
        $url = 'https://www.gzzczb.com/prod-api/yw/zbgg/getGgListNoPage?pageSize=10&page=1&ggAnnouncement=zbgg';
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
                            $data['bidSiteId'] = 1357;
                            $data['bidSiteName'] = '国资智采';
                            $data['bidTitle'] = $v['ggName'];
                            $data['bidUrl'] = 'https://www.gzzczb.com/ggDetail?id='.$v['id'];
                            $data['beginDate'] = $v['ggStartTime'];
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 1357 -> 国资智采');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 1357 -> 国资智采');
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
            $this->db->table('option')->where('optionName', 'bid_spider_1_1_status')->update(['optionValue' => 0]);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                log_message('error', '招标采集：招标信息入库失败（招标密集类）事务已回滚！');
            }
        }
    }
}
