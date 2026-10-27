<?php

namespace App\Controllers\Api;
use App\Controllers\BaseController;

class SpiderBid06 extends BaseController
{
    /* 招标公众号类 按每1小时执行一次的标准设计 */
    public function index()
    {
        $this->db->table('option')->where('optionName', 'bid_spider_0_6_status')->update(['optionValue' => 1]);
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
        // 公众号集合
        $gzh_arr = [
            ['id' => 91, 'name' => '山东技师学院公众号', 'fakeid' => 'MzIzMDQ0OTQ3OA=='],
            ['id' => 337, 'name' => '厦门市翔安职业技术学校公众号', 'fakeid' => 'MzIzMDQ0OTQ3OA=='],
            ['id' => 424, 'name' => '罗定第一中学公众号', 'fakeid' => 'Mzk0NTAxODc4MQ=='],
            ['id' => 457, 'name' => '重庆市大足中学公众号', 'fakeid' => 'MzA4MDQ4Mzk4NQ=='],
            ['id' => 508, 'name' => '云浮市中等专业学校公众号', 'fakeid' => 'Mzg4Nzk3NTU1MA=='],
            ['id' => 562, 'name' => '拉萨市第二中等职业技术学校公众号', 'fakeid' => 'MzA4NDQyNzQ2Nw=='],
            ['id' => 734, 'name' => '镇海区技工学校公众号', 'fakeid' => 'MzkxMzMzMzAyNA=='],
            ['id' => 735, 'name' => '播州职教公众号', 'fakeid' => 'MzIzMjcxNjY3Nw=='],
            ['id' => 736, 'name' => '福建省武平职业中专学校公众号', 'fakeid' => 'Mzg4OTA2ODkzNw=='],
            ['id' => 737, 'name' => '官渡区职业高级中学公众号', 'fakeid' => 'Mzk0OTMwODg3OQ=='],
            ['id' => 738, 'name' => '海盐职业教育中心公众号', 'fakeid' => 'MzIwMjc1MTE3MA=='],
            ['id' => 739, 'name' => '建始县中等职业技术学校公众号', 'fakeid' => 'MzA5MzU3NDI1NQ=='],
            ['id' => 740, 'name' => '连江职业中专学校公众号', 'fakeid' => 'MzU4MDUyNzE5MA=='],
            ['id' => 741, 'name' => '茂名市第一职业技术学校公众号', 'fakeid' => 'MzU0NzcyNDU0NA=='],
            ['id' => 742, 'name' => '平度市职业教育中心学校公众号', 'fakeid' => 'Mzg4MzAzOTQ5Mg=='],
            ['id' => 743, 'name' => '松桃职校微校园公众号', 'fakeid' => 'MzU0NzA0MDU3NQ=='],
            ['id' => 744, 'name' => '西藏拉萨市第一中等职业技术学校公众号', 'fakeid' => 'Mzg2NTQ3ODA5NQ=='],
            ['id' => 745, 'name' => '织金县中等职业学校公众号', 'fakeid' => 'MzU3NDgzOTE5OQ=='],
            ['id' => 751, 'name' => '漳州技师学院公众号', 'fakeid' => 'MzIyNTY2NDQxNw=='],
            ['id' => 787, 'name' => '郴州市第一职业中等专业学校公众号', 'fakeid' => 'MzkyMzM0MTA0MA=='],
            ['id' => 846, 'name' => '西藏技师学院公众号', 'fakeid' => 'MzAxMTE3NjAxOA=='],
            ['id' => 847, 'name' => '十堰市茅箭区致远学校公众号', 'fakeid' => 'Mzk0MjU5ODAwOQ=='],
            ['id' => 885, 'name' => '广州市荔湾区华侨小学汇龙学校公众号', 'fakeid' => 'MzI1MDM0OTEwOA=='],
            ['id' => 901, 'name' => '西藏阿里地区中等职业技术学校公众号', 'fakeid' => 'MzUyMDg2ODI1NQ=='],
            ['id' => 916, 'name' => '肥西经济开发区中心学校公众号', 'fakeid' => 'MzkyODE4MjQ4OA=='],
            ['id' => 930, 'name' => '东台市沿海第二实验幼儿园公众号', 'fakeid' => 'MzkwOTcyMDU1NQ=='],
            ['id' => 937, 'name' => '恩施市清江民族学校公众号', 'fakeid' => 'MzkyMjY2Mjg0Mw=='],
            ['id' => 938, 'name' => '海勃湾区第十五幼儿园公众号', 'fakeid' => 'MzkxNjU5MTgyNQ=='],
            ['id' => 939, 'name' => '广州市增城区朱村街第二幼儿园公众号', 'fakeid' => 'Mzk0OTI5OTA2MQ=='],
            ['id' => 940, 'name' => '准格尔旗薛家湾第八幼儿园公众号', 'fakeid' => 'MzAwMTc5MjMwNg=='],
            ['id' => 941, 'name' => '东胜区铁西实验幼儿园公众号', 'fakeid' => 'MzA5OTQ1MzcxMw=='],
            ['id' => 944, 'name' => '广安市第二幼儿园公众号', 'fakeid' => 'MzUzMDk2NzMxNQ=='],
            ['id' => 949, 'name' => '广州市花都区花东镇七星小学公众号', 'fakeid' => 'MzI1MTE4MDE0NQ=='],
            ['id' => 953, 'name' => '日喀则市特殊教育学校公众号', 'fakeid' => 'Mzg5MTU4ODU4NQ=='],
            ['id' => 963, 'name' => '镇雄教体公众号', 'fakeid' => 'Mzg2OTg4MDQ1Nw=='],
            ['id' => 971, 'name' => '东台市东台镇城东中学公众号', 'fakeid' => 'MzIzNTk3MjAwMw=='],
            ['id' => 974, 'name' => '韶关市第五中学公众号', 'fakeid' => 'MzYzNTI2MDg1OQ=='],
            ['id' => 977, 'name' => '乌海市海勃湾区第七幼儿园公众号', 'fakeid' => 'MzI2MTQ2ODk3MQ=='],
            ['id' => 982, 'name' => '建湖中专公众号', 'fakeid' => 'MzA4ODg2NDY4MA=='],
            ['id' => 983, 'name' => '来宾职业教育中心学校官微公众号', 'fakeid' => 'MzkzMTE5NTA4OQ=='],
            ['id' => 995, 'name' => '重庆市九龙坡区实验外国语学校公众号', 'fakeid' => 'Mzg4NDc3NTk0Ng=='],
            ['id' => 1002, 'name' => '广安市前锋区芦溪河幼儿园公众号', 'fakeid' => 'MzU5ODc4NjA2Mg=='],
            ['id' => 1003, 'name' => '如皋中专公众号', 'fakeid' => 'MzA5NzkwNTU5NQ=='],
            ['id' => 1007, 'name' => '盘州市职业技术学校公众号', 'fakeid' => 'MzA3MjE2NzQ2NQ=='],
            ['id' => 1019, 'name' => '嵊州市中等职业技术学校公众号', 'fakeid' => 'MzAxOTM3OTAzMw=='],
            ['id' => 1026, 'name' => '大渔幼儿园公众号', 'fakeid' => 'MzkxMTM5MzQ3NA=='],
            ['id' => 1034, 'name' => '四川省成都市航天中学校公众号', 'fakeid' => 'MzIzNjIyNTgyMg=='],
            ['id' => 1037, 'name' => '兴义市中等职业学校公众号', 'fakeid' => 'MzAxMzM5NTc4Mg=='],
            ['id' => 1045, 'name' => '吴忠市红寺堡区职业技术学校公众号', 'fakeid' => 'Mzg5MzAzNjY5MA=='],
            ['id' => 1046, 'name' => '阳泉高级技工学校公众号', 'fakeid' => 'MzAxMjM0NjU0Mw=='],
            ['id' => 1047, 'name' => '平潭职业中专公众号', 'fakeid' => 'MzAwNTM5MzM4Mw=='],
            ['id' => 1048, 'name' => '江苏省溧水中等专业学校公众号', 'fakeid' => 'MzU0ODUxMjI2MQ=='],
            ['id' => 1051, 'name' => '云吟职中公众号', 'fakeid' => 'MzIyMDMyNTM1MA=='],
            ['id' => 1055, 'name' => '广东省高州农业学校公众号', 'fakeid' => 'Mzg5NTg0ODMxNQ=='],
            ['id' => 1066, 'name' => '余杭研训公众号', 'fakeid' => 'MzU1NDMxOTUzNw=='],
            ['id' => 1075, 'name' => '来宾市第四中学公众号', 'fakeid' => 'Mzg5NjI2NzI1OQ=='],
            ['id' => 1085, 'name' => '昌宁职教公众号', 'fakeid' => 'MzUzODk5ODI2MQ=='],
            ['id' => 1107, 'name' => '惠州仲恺高新区第一幼儿园公众号', 'fakeid' => 'MzkyMzI4NzAxOA=='],
            ['id' => 1108, 'name' => '嵩明县县直机关幼儿园公众号', 'fakeid' => 'MzA4Njc0NjI0Mw=='],
            ['id' => 1109, 'name' => '华南师范大学附属平湖学校公众号', 'fakeid' => 'Mzg3ODY1OTY1OA=='],
            ['id' => 1130, 'name' => '峨眉二中公众号', 'fakeid' => 'MjM5ODQwNjQxOQ=='],
            ['id' => 1131, 'name' => '盐池县第二幼儿园公众号', 'fakeid' => 'MzIxMTU3MjE3MA=='],
            ['id' => 1143, 'name' => '云浮技师学院公众号', 'fakeid' => 'MzI2MDExOTUxMQ=='],
            ['id' => 1151, 'name' => '习水县中等职业学校公众号', 'fakeid' => 'MzIzMzY0Mzk0Mw=='],
            ['id' => 1166, 'name' => '凤冈县中等职业学校公众号', 'fakeid' => 'Mzg3MzAwODI2MQ=='],
            ['id' => 1168, 'name' => '昆明市第十幼儿园公众号', 'fakeid' => 'Mzg3NDcyMjIxNA=='],
            ['id' => 1172, 'name' => '日喀则市拉孜高级中学公众号', 'fakeid' => 'Mzk0MTMzNTMyMw=='],
            ['id' => 1177, 'name' => '昆明市盘龙区茨坝幼儿园公众号', 'fakeid' => 'MzkxNjQyMjU0NA=='],
            ['id' => 1181, 'name' => '兰州市第六十四中学公众号', 'fakeid' => 'MzIzMTc5NTE5NA=='],
            ['id' => 1188, 'name' => '湖南省浏阳高级技工学校公众号', 'fakeid' => 'MzI1NjE1MTM4MQ=='],
            ['id' => 1192, 'name' => '天津市滨海新区塘沽未来学校公众号', 'fakeid' => 'MzA5NDA0MDEyOQ=='],
            ['id' => 1194, 'name' => '凯里实验高级中学公众号', 'fakeid' => 'MzU1ODA0MTkzNw=='],
            ['id' => 1208, 'name' => '安徽蚌埠慕远学校公众号', 'fakeid' => 'Mzg4NDg1NjY2NQ=='],
            ['id' => 1209, 'name' => '拉萨那曲第一高级中学公众号', 'fakeid' => 'MzAwMzg2NDIyMQ=='],
            ['id' => 1218, 'name' => '锦屏县中等职业学校公众号', 'fakeid' => 'MzU4MzQ5NDMxOQ=='],
            ['id' => 1223, 'name' => '黄河科技学院供应管理中心公众号', 'fakeid' => 'MzE5MTM4NzIwMg=='],
            ['id' => 1228, 'name' => '象州县职业技术学校公众号', 'fakeid' => 'Mzg2NTE5NzcwOQ=='],
            ['id' => 1232, 'name' => '西双版纳职业技术学院公众号', 'fakeid' => 'MzIzNDQzMjM5NQ=='],
            ['id' => 1234, 'name' => '海南州职业技术学校公众号', 'fakeid' => 'Mzg5Nzg1NTA5OQ=='],
            ['id' => 1251, 'name' => '厦门市集美职业技术学校公众号', 'fakeid' => 'MzI0MDcyODEzNw=='],
            ['id' => 1252, 'name' => '四川天府新区综合高级中学公众号', 'fakeid' => 'MzkwNjU2Nzc2Ng=='],
            ['id' => 1255, 'name' => '东台市东台镇范公学校公众号', 'fakeid' => 'MzIxODkzMzE2Mg=='],
            ['id' => 1261, 'name' => '罗甸职校公众号', 'fakeid' => 'MzkyMDE4NTA1MA=='],
            ['id' => 1263, 'name' => '鄂伦春自治旗民族职业高中公众号', 'fakeid' => 'MzkxNDI5NjQyNA=='],
            ['id' => 1277, 'name' => '筠州教育公众号', 'fakeid' => 'MzUzMzAyMjQxMA=='],
            ['id' => 1281, 'name' => '昆明市中华小学公众号', 'fakeid' => 'Mzk0NjUyMDUxMg=='],
            ['id' => 1283, 'name' => '文昌市华侨农场小学公众号', 'fakeid' => 'Mzk0Nzc4NzI2NA=='],
            ['id' => 1285, 'name' => '东胜区第二幼儿园公众号', 'fakeid' => 'MzA3MTUxODEwMg=='],
            ['id' => 1289, 'name' => '广西师范大学附属中学公众号', 'fakeid' => 'MzA4MDU3MDIxOQ=='],
            ['id' => 1291, 'name' => '贵德县河西寄宿制学校公众号', 'fakeid' => 'Mzg3ODc0OTQ4NA=='],
            ['id' => 1292, 'name' => '东胜区伊克昭幼儿园公众号', 'fakeid' => 'MzA3MjQ3NTQwNg=='],
            ['id' => 1305, 'name' => '日喀则职业技术学院公众号', 'fakeid' => 'MzYyNDAyNzM1MA=='],
            ['id' => 1306, 'name' => '东乌珠穆沁旗第三幼儿园公众号', 'fakeid' => 'Mzg4ODg0NDEzMQ=='],
            ['id' => 1307, 'name' => '六盘水市第二十二中学公众号', 'fakeid' => 'Mzg3NjYxNDc1NA=='],
            ['id' => 1311, 'name' => '岑巩县第一中学公众号', 'fakeid' => 'Mzg4ODc1NTE2OQ==']
        ];
        // 发起微信公众号文章列表采集所需的token与cookie
        $gzh_token = '423667719';
        $gzh_cookie = 'cookie: appmsglist_action_3905589934=card; appmsglist_action_3877815277=card; annual_review_dialog=1; RK=KVzLgLQhdh; ptcz=2b46aa01617005e6b169c16127eb27981c53ece46225efc6c3b1112deaf5b37b; ua_id=WtEGQddB2PibK5aFAAAAAFnvLmazSATNOKgZgz_BlXg=; wxuin=78637786299382; mm_lang=zh_CN; rewardsn=; wxtokenkey=777; uuid=030ed021166996013cd7b4f9895204a8; rand_info=CAESINvod2QKlratFcYijezeh0CpyVd4HRiSpq+F6OlH4f83; slave_bizuin=3905589934; data_bizuin=3905589934; bizuin=3905589934; data_ticket=92oYt4ZHrMmLd5klqck/aaaYU4b6j/ZmGZymLbOCSvTJc51WTdS1beqbDvgI5goG; slave_sid=al9IOEZpMVJ2OUhUNUpjbmt0QzBwUW0xcWdVNENhQjRJZDVJT0V2b1NjNjl5d2xGWlhyVjBScnVjbXc4RUt2ZlN1bUwzSkhYcm1zNmpCVW42anc4UEtIbVowWWVHX0lxQjBxSWtiaEpndzU0NHVlSEFSQzM4UG5WOHdRR09OaENiemFBNWxQdTN5TEhWQTMw; slave_user=gh_8dfeb57a0f68; xid=70b14ff3f824fd893f3b76e471dc704d; _clck=3905589934|1|g8b|0; _clsk=1n4wmr|1785806904461|197|1|mp.weixin.qq.com/weheat-agent/payload/record';
        foreach ($gzh_arr as $index => $gzh) {
            $url = 'https://mp.weixin.qq.com/cgi-bin/appmsgpublish?sub=list&search_field=null&begin=0&count=20&query=&fakeid='.urlencode($gzh['fakeid']).'&type=101_1&free_publish_type=1&sub_action=list_ex&fingerprint=&token='.$gzh_token.'&lang=zh_CN&f=json&ajax=1';
            $res = global_curl($url, [
                'httpheader' => [
                    'accept: */*',
                    'accept-encoding: gzip, deflate, br, zstd',
                    'accept-language: zh-CN,zh;q=0.9',
                    'cache-control: no-cache',
                    $gzh_cookie,
                    'pragma: no-cache',
                    'referer: https://mp.weixin.qq.com/cgi-bin/appmsg?t=media/appmsg_edit_v2&action=edit&isNew=1&type=77&createType=8&token='.$gzh_token.'&lang=zh_CN&timestamp='.intval(microtime(true) * 1000),
                    'sec-ch-ua: "Not_A Brand";v="99", "Microsoft Edge";v="109", "Chromium";v="109"',
                    'sec-ch-ua-mobile: ?0',
                    'sec-ch-ua-platform: "Windows"',
                    'sec-fetch-dest: empty',
                    'sec-fetch-mode: cors',
                    'sec-fetch-site: same-origin',
                    'user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36',
                    'x-requested-with: XMLHttpRequest'
                ]
            ]);
            // 如果发现返回的是风控禁止访问状态码，则直接跳出整个循环
            if (strpos($res, '"ret":200013') !== false) {
                log_message('error', $gzh['name'] . ':' . $res);
                break;
            }
            if ($res !== false) {
                $res = json_decode($res, true);
                if (is_array($res) && isset($res['publish_page'])) {
                    $obj = json_decode($res['publish_page'], true);
                    $arr = $obj['publish_list'] ?? [];
                    if (is_array($arr) && count($arr) > 0) {
                        foreach ($arr as $k => $v) {
                            $x = json_decode($v['publish_info'], true);
                            $data = [];
                            if ($x && isset($x['appmsgex'])) {
                                foreach ($x['appmsgex'] as $k1 => $v1) {
                                    if (!preg_match('#(' . $in_keywords . ')#', $v1['title'])) {
                                        continue;
                                    } else {
                                        if (preg_match('#(' . $out_keywords . ')#', $v1['title'])) {
                                            continue;
                                        } else {
                                            $data['bidSiteId'] = $gzh['id'];
                                            $data['bidSiteName'] = $gzh['name'];
                                            $data['bidTitle'] = $v1['title'];
                                            $data['bidUrl'] = $v1['link'];
                                            $data['beginDate'] = date('Y-m-d H:i:s', $v1['create_time']);
                                            $data['inDate'] = date('Y-m-d H:i:s');
                                            $final_arr[] = $data;
                                        }
                                    }
                                }
                            }
                        }
                    } else {
                        log_message('error', '招标采集：未匹配到数据 '.$gzh['id'] . ' -> ' . $gzh['name']);
                    }
                } else {
                    log_message('error', '招标采集：未匹配到数据 '.$gzh['id'] . ' -> ' . $gzh['name']);
                }
            } else {
                log_message('error', '招标采集：未获取到网页 '. $gzh['id'] . ' -> ' . $gzh['name']);
            }
            $sleep_sec = random_int(5, 20);
            sleep($sleep_sec);
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
            $this->db->table('option')->where('optionName', 'bid_spider_0_6_status')->update(['optionValue' => 0]);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                log_message('error', '招标采集：招标信息入库失败（招标公众号类）事务已回滚！');
            }
        }
    }
}
