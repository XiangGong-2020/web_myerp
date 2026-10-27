<?php

namespace App\Controllers\Api;
use App\Controllers\BaseController;

class SpiderBid12 extends BaseController
{
    /* 招标异常类 按每30分钟执行一次的标准设计 */
    public function index()
    {
        $this->db->table('option')->where('optionName', 'bid_spider_1_2_status')->update(['optionValue' => 1]);
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

        // 126 云采链 【经常会封IP，只能改为每隔30分钟一次性采500条】
        $url = 'https://middleware.choicelink.cn/notice/api/public/siteserverContent1/getIndex?filter=siteId+%3D%3D+%224795%22+%26%26+channelId+in+%225138,5140,5139,5141%22&order=addDate+desc&pageNum=1&pageSize=500';
        $res = global_curl($url, [
            'httpheader' => [
                'Accept: application/json, text/plain, */*',
                'Accept-Encoding: gzip, deflate, br, zstd',
                'Accept-Language: zh-CN,zh;q=0.9,en;q=0.8,en-GB;q=0.7,en-US;q=0.6',
                'Cache-Control: no-cache',
                'Connection: keep-alive',
                'Host: middleware.choicelink.cn',
                'Origin: https://www.choicelink.cn',
                'Pragma: no-cache',
                'Referer: https://www.choicelink.cn/',
                'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36 Edg/137.0.0.0'
            ]
        ]);
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
                            $data['bidSiteId'] = 126;
                            $data['bidSiteName'] = '云采链';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = 'https://www.choicelink.cn/single-pages/notice-content.html?siteId=' . $v['siteId'] . '&contentId=' . $v['id'] . '&channelId=' . $v['channelId'];
                            $data['beginDate'] = str_replace('T', ' ', $v['addDate']);
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 126 -> 云采链');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 126 -> 云采链');
        }
        // 961 西藏政府采购网 【网站打开速度很慢，只能改为每30分钟一次性采200条】
        $url = 'http://www.ccgp-xizang.gov.cn/freecms/rest/v1/notice/selectInfoMoreChannel.do?COLLCC=2316543238&&siteId=18de62f0-2fb0-4187-a6c1-cd8fcbfb4585&channel=b541ffff-03ee-4160-be64-b11ccf79660d&currPage=1&pageSize=200&noticeType=&cityOrArea=&noticeName=&operationStartTime=&operationEndTime=&selectTimeName=noticeTime';
        $res = global_curl($url, [
            'timeout' => 120
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
                            $data['bidSiteId'] = 961;
                            $data['bidSiteName'] = '西藏政府采购网';
                            $data['bidTitle'] = $v['title'];
                            $data['bidUrl'] = strpos($v['pageurl'], 'http') === 0 ? $v['pageurl'] : ('http://www.ccgp-xizang.gov.cn'.$v['pageurl']);
                            $data['beginDate'] = $v['addtimeStr'];
                            $data['inDate'] = date('Y-m-d H:i:s');
                            $final_arr[] = $data;
                        }
                    }
                }
            } else {
                log_message('error', '招标采集：未匹配到数据 961 -> 西藏政府采购网');
            }
        } else {
            log_message('error', '招标采集：未获取到网页 961 -> 西藏政府采购网');
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
            $this->db->table('option')->where('optionName', 'bid_spider_1_2_status')->update(['optionValue' => 0]);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                log_message('error', '招标采集：招标信息入库失败（招标异常类）事务已回滚！');
            }
        }
    }
}
