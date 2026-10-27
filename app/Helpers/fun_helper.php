<?php
if (!function_exists('token')) {
    /**
     * Token生成与验证（与原CI3逻辑完全一致）
     * @param string $str 传任意非空值触发验证，不传则生成token
     * @return bool|string
     */
    function token($str = '')
    {
        // CI4 服务方式获取请求/响应对象，替代 get_instance()
        $request  = service('request');
        $response = service('response');

        if (!$str) {
            // 生成token，写入cookie
            $tokenValue = md5(time() . uniqid());
            $response->setCookie('token', $tokenValue, 36000);
            return $tokenValue;
        } else {
            // 验证：对比POST提交的token和cookie中的token
            $postToken   = $request->getPost('token');
            $cookieToken = $request->getCookie('token');

            if (!empty($postToken) && !empty($cookieToken) && $postToken === $cookieToken) {
                // 验证通过，清除cookie（设置过期时间为过去，确保浏览器立即删除）
                $response->setCookie('token', '', time() - 3600);
                return true;
            }
            return false;
        }
    }
}
// 自动生成单号
if (!function_exists('str_no')) {
    function str_no($str = '')
    {
        return $str . date("YmdHis") . substr(strval(intval(microtime(true) * 10000)), -4, 4);
    }
}
// 随机字符串生成
if (!function_exists('str_random')) {
    function str_random(int $len, string $chars = '')
    {
        if ($chars == '') {
            $chars = '123456789abcdefghijklmnopqrstuvwxyzABCDEFJHIJKMNOPQRSTUVWSYZ';
        }
        $str = '';
        $max = strlen($chars) - 1;
        for ($i = 0; $i < $len; $i++) {
            $str .= $chars[mt_rand(0, $max)];
        }
        return $str;
    }
}
// 字符串检查
if (!function_exists('str_check')) {
    function str_check(string $t0, string $t1)
    {
        if (strlen($t0) < 1) return false;
        switch ($t1) {
            case 'en':
                $t2 = '/^[a-zA-Z]+$/';
                break;
            case 'cn':
                $t2 = '/[\u4e00-\u9fa5]+/u';
                break;
            case 'int':
                $t2 = '/^[0-9]*$/';
                break;
            case 'price':
                $t2 = '/^\d+(\.\d+)?$/';
                break;
            case 'userName':
                $t2 = '/^[a-zA-Z0-9_]{5,20}$/';
                break;
            case 'password':
                $t2 = '/^[a-zA-Z0-9_]{6,16}$/';
                break;
            case 'email':
                $t2 = '/^[\w\-\.]+@[a-zA-Z0-9]+\.(([a-zA-Z0-9]{2,4})|([a-zA-Z0-9]{2,4}\.[a-zA-Z]{2,4}))$/';
                break;
            case 'tel':
                $t2 = '/^((\(\+?\d{2,3}\))|(\+?\d{2,3}\-))?(\(0?\d{2,3}\)|0?\d{2,3}-)?[1-9]\d{4,7}(\-\d{1,4})?$/';
                break;
            case 'mobile':
                $t2 = '/^(\+?\d{2,3})?0?1(3\d|5\d|8\d)\d{8}$/';
                break;
            case 'idcard':
                $t2 = '/(^([\d]{15}|[\d]{18}|[\d]{17}x)$)/';
                break;
            case 'qq':
                $t2 = '/^[1-9]\d{4,15}$/';
                break;
            case 'url':
                $t2 = '/^(http|https|ftp):\/\/[a-zA-Z0-9]+\.[a-zA-Z0-9]+[\/=\?%\-&_~`@[\]\':+!]*([^<>\'\'])*$/';
                break;
            case 'ip':
                $t2 = '/^((25[0-5]|2[0-4]\d|(1\d|[1-9])?\d)\.){3}(25[0-5]|2[0-4]\d|(1\d|[1-9])?\d)$/';
                break;
            case 'file':
                $t2 = '/^[a-zA-Z0-9]{1,50}$/';
                break;
            case 'zipcode':
                $t2 = '/^\d{6}$/';
                break;
            case 'date':
                $t2 = '/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/';
                break;
            case 'time':
                $t2 = '/^[0-9]{2}:[0-9]{2}:[0-9]{2}$/';
                break;
            case 'datetime':
                $t2 = '/^[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}$/';
                break;
            case 'utf8':
                $t2 = '%^(?:
                            [\x09\x0A\x0D\x20-\x7E] # ASCII
                            | [\xC2-\xDF][\x80-\xBF] # non-overlong 2-byte
                            | \xE0[\xA0-\xBF][\x80-\xBF] # excluding overlongs
                            | [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2} # straight 3-byte
                            | \xED[\x80-\x9F][\x80-\xBF] # excluding surrogates
                            | \xF0[\x90-\xBF][\x80-\xBF]{2} # planes 1-3
                            | [\xF1-\xF3][\x80-\xBF]{3} # planes 4-15
                            | \xF4[\x80-\x8F][\x80-\xBF]{2} # plane 16
                            )*$%xs';
                break;
            default:
                $t2 = '';
                break;
        }
        $pour = @preg_match($t2, $t0);
        if ($pour) {
            return $t0;
        } else {
            return false;
        }
    }
}
// 封装curl
if (!function_exists('global_curl')) {
    function global_curl(string $url, array $opts = [])
    {
        if (function_exists('curl_init')) {
            // 初始化curl会话
            $ch = curl_init();
            // 如未明确指明用HTTP2，则默认使用HTTP1.1，避免出现老网站不支持HTTP2的情况
            if (isset($opts['http2'])) {
                curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_VERSION_HTTP2);
            } else {
                curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
            }
            curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
            // 目标地址
            curl_setopt($ch, CURLOPT_URL, trim($url));
            // 压缩格式，为空表示支持解压所有压缩格式
            curl_setopt($ch, CURLOPT_ENCODING, '');
            // 设置携带useragent模仿浏览器
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Edg/106.0.1370.42');
            // 设置超时时间
            if (isset($opts['timeout'])) {
                // 连接服务器超时时间，单位：秒（0：无限制等待直到连接成功）
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $opts['timeout']);
                // 接收返回内容超时时间，单位：秒（0：无限制直到接收数据完成）
                curl_setopt($ch, CURLOPT_TIMEOUT, $opts['timeout']);
            } else {
                // 连接服务器超时时间，单位：秒（0：无限制等待直到连接成功）
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
                // 接收返回内容超时时间，单位：秒（0：无限制直到接收数据完成）
                curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            }
            if (isset($opts['ssl'])) {
                // 验证对等（0：信任任何证书）
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                // 检查证书中是否设置域名（0：不验证, 1：旧版验证； 2：新版验证）
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                // 设置SSL协议版本号
                curl_setopt($ch, CURLOPT_SSLVERSION, $opts['ssl']);
            } else {
                // 验证对等（0：信任任何证书）
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                // 检查证书中是否设置域名（0：不验证, 1：旧版验证； 2：新版验证）
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            }
            if (isset($opts['port'])) {
                // 端口
                curl_setopt($ch, CURLOPT_PORT, $opts['port']);
            }
            if (isset($opts['header'])) {
                // 是否返回header信息（0：不返回；1：返回）
                curl_setopt($ch, CURLOPT_HEADER, 1);
            } else {
                curl_setopt($ch, CURLOPT_HEADER, 0);
            }
            if (isset($opts['httpheader'])) {
                // HTTP头信息（数组形式）
                curl_setopt($ch, CURLOPT_HTTPHEADER, $opts['httpheader']);
            } else {
                curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-type:application/json; charset='utf-8'", "Accept:application/json", "Cache-Control:no-cache", "Pragma:no-cache"]);
            }
            // 请求方式
            if (isset($opts['post'])) {
                // POST请求方式
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
                if (isset($opts['postfields'])) {
                    // POST数据，$post可以是json，也可以是url拼接
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $opts['postfields']);
                }
            } else {
                // GET请求方式
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
            }
            if (isset($opts['returntransfer'])) {
                //0：成功只返回TRUE并自动输出返回的内容，失败返回FALSE； 1：成功只将结果以字符串返回，不自动输出任何内容，失败返回FALSE。
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, $opts['returntransfer']);
            } else {
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            }
            // 发起curl请求
            $res = curl_exec($ch);
            $errno = curl_errno($ch);
            $error = curl_error($ch);
            $info = curl_getinfo($ch);
            if ($errno) {
                var_dump($errno, $error, $info);
                /* curl_errno函数返回的数字说明：
                    [1] => 'CURLE_UNSUPPORTED_PROTOCOL', //传给libcurl的网址使用了不支持的协议。
                    [2] => 'CURLE_FAILED_INIT', //非常早期的初始化代码失败，可能是内部错误或问题。
                    [3] => 'CURLE_URL_MALFORMAT', //网址格式不正确。
                    [4] => 'CURLE_URL_MALFORMAT_USER', //libcurl编译时没有内置此功能。
                    [5] => 'CURLE_COULDNT_RESOLVE_PROXY', //无法解析代理服务器。
                    [6] => 'CURLE_COULDNT_RESOLVE_HOST', //无法解析主机。
                    [7] => 'CURLE_COULDNT_CONNECT', //无法通过connect()连接至主机或代理服务器。
                    [8] => 'CURLE_FTP_WEIRD_SERVER_REPLY', //连接到FTP后libcurl收到了不正常或不正确的回复。
                    [9] => 'CURLE_REMOTE_ACCESS_DENIED', //无法访问网址中指定的资源，对于FTP，如果尝试更改为远程目录，就会发生这种情况。
                    [11] => 'CURLE_FTP_WEIRD_PASS_REPLY', //在将FTP密码发送到服务器后，libcurl需要收到正确的回复，此错误代码表示返回的是意外的代码。
                    [13] => 'CURLE_FTP_WEIRD_PASV_REPLY', //libcurl无法从服务器端收到有用的结果，作为对PASV或EPSV命令的响应，服务器有问题。
                    [14]=>'CURLE_FTP_WEIRD_227_FORMAT', //FTP 服务器返回227行作为对PASV命令的响应，如果libcurl无法解析此行，就会返回此代码。
                    [15] => 'CURLE_FTP_CANT_GET_HOST', //在查找用于新连接的主机时出现内部错误。
                    [17] => 'CURLE_FTP_COULDNT_SET_TYPE', //在尝试将传输模式设置为二进制或ascii时发生错误。
                    [18] => 'CURLE_PARTIAL_FILE', //文件传输尺寸小于或大于预期，当服务器先报告了一个预期的传输尺寸，然后所传送的数据与先前指定尺寸不相符时，就会发生此错误。
                    [19] => 'CURLE_FTP_COULDNT_RETR_FILE', //RETR命令收到了不正常的回复，或完成的传输尺寸为零字节。
                    [21] => 'CURLE_QUOTE_ERROR', //在向远程服务器发送自定义QUOTE命令时，其中一个命令返回的错误代码为400或更大的数字(对于FTP)，或以其他方式表明命令无法成功完成。
                    [22] => 'CURLE_HTTP_RETURNED_ERROR', //如果CURLOPT_FAILONERROR设置为TRUE，且HTTP服务器返回>=400的错误代码，就会返回此代码(此错误代码以前又称为CURLE_HTTP_NOT_FOUND)。
                    [23] => 'CURLE_WRITE_ERROR', //在向本地文件写入所收到的数据时发生错误，或由写入回调(write callback)向libcurl返回了一个错误。
                    [25] => 'CURLE_UPLOAD_FAILED', //无法开始上传，对于FTP服务器通常会拒绝执行STOR命令，错误缓冲区通常会提供服务器对此问题的说明(此错误代码以前又称为CURLE_FTP_COULDNT_STOR_FILE)
                    [26] => 'CURLE_READ_ERROR', //读取本地文件时遇到问题，或由读取回调(read callback)返回了一个错误。
                    [27] => 'CURLE_OUT_OF_MEMORY', //内存分配请求失败。此错误比较严重，若发生此错误，则表明出现了非常严重的问题。
                    [28] => 'CURLE_OPERATION_TIMEDOUT', //操作超时。已达到指定的超时时间。
                    [30] => 'CURLE_FTP_PORT_FAILED', //FTP PORT命令返回错误。在没有为 libcurl 指定适当的地址使用时，最有可能发生此问题。 请参阅 CURLOPT_FTPPORT。
                    [31] => 'CURLE_FTP_COULDNT_USE_REST', //FTP REST命令返回错误。如果服务器正常，则应当不会发生这种情况。
                    [33] => 'CURLE_RANGE_ERROR', //服务器不支持或不接受范围请求。
                    [34] => 'CURLE_HTTP_POST_ERROR', //此问题比较少见，主要由内部混乱引发。
                    [35] => 'CURLE_SSL_CONNECT_ERROR', //同时使用 SSL/TLS 时可能会发生此错误。您可以访问错误缓冲区查看相应信息。可能是证书（文件格式、路径、许可）、密码及其他因素导致了此问题。
                    [36] => 'CURLE_BAD_DOWNLOAD_RESUME', //尝试恢复超过文件大小限制的 FTP 连接。
                    [37] => 'CURLE_FILE_COULDNT_READ_FILE', //无法打开 FILE:// 路径下的文件。很可能是文件路径无法识别现有文件。建议您检查文件的访问权限。
                    [38] => 'CURLE_LDAP_CANNOT_BIND', //LDAP 无法绑定。LDAP 绑定操作失败。
                    [39] => 'CURLE_LDAP_SEARCH_FAILED', //LDAP 搜索无法进行。
                    [41] => 'CURLE_FUNCTION_NOT_FOUND', //找不到函数。找不到必要的 zlib 函数。
                    [42] => 'CURLE_ABORTED_BY_CALLBACK', //由回调中止。回调向 libcurl 返回了 “abort”。
                    [43] => 'CURLE_BAD_FUNCTION_ARGUMENT', //内部错误。使用了不正确的参数调用函数。
                    [45] => 'CURLE_INTERFACE_FAILED', //界面错误。请通过 CURLOPT_INTERFACE 设置要使用哪个界面来处理外部连接的来源 IP 地址。（此错误代码以前又称为 CURLE_HTTP_PORT_FAILED）
                    [47] => 'CURLE_TOO_MANY_REDIRECTS', //重定向过多。进行重定向时，libcurl 达到了网页点击上限。请使用 CURLOPT_MAXREDIRS 设置上限。
                    [48] => 'CURLE_UNKNOWN_TELNET_OPTION', //无法识别以 CURLOPT_TELNETOPTIONS 设置的选项。请参阅相关文档。
                    [49] => 'CURLE_TELNET_OPTION_SYNTAX', //telnet 选项字符串的格式不正确。
                    [51] => 'CURLE_PEER_FAILED_VERIFICATION', //远程服务器的 SSL 证书或 SSH md5 指纹不正确。
                    [52] => 'CURLE_GOT_NOTHING', //服务器未返回任何数据，在相应情况下，未返回任何数据就属于出现错误。
                    [53] => 'CURLE_SSL_ENGINE_NOTFOUND', //找不到指定的加密引擎。
                    [54] => 'CURLE_SSL_ENGINE_SETFAILED', //无法将选定的 SSL 加密引擎设为默认选项。
                    [55] => 'CURLE_SEND_ERROR', //无法发送网络数据。
                    [56] => 'CURLE_RECV_ERROR', //接收网络数据失败。
                    [58] => 'CURLE_SSL_CERTPROBLEM', //本地客户端证书有问题。
                    [59] => 'CURLE_SSL_CIPHER', //无法使用指定的密钥。
                    [60] => 'CURLE_SSL_CACERT', //无法使用已知的 CA 证书验证对等证书。
                    [61] => 'CURLE_BAD_CONTENT_ENCODING', //无法识别传输编码。
                    [62] => 'CURLE_LDAP_INVALID_URL', //LDAP 网址无效。
                    [63] => 'CURLE_FILESIZE_EXCEEDED', //超过了文件大小上限。
                    [64] => 'CURLE_USE_SSL_FAILED', //请求的 FTP SSL 级别失败。
                    [65] => 'CURLE_SEND_FAIL_REWIND', //进行发送操作时，curl 必须回转数据以便重新传输，但回转操作未能成功。
                    [66] => 'CURLE_SSL_ENGINE_INITFAILED', //SSL 引擎初始化失败。
                    [67] => 'CURLE_LOGIN_DENIED', //远程服务器拒绝 curl 登录（7.13.1 新增功能）。
                    [68] => 'CURLE_TFTP_NOTFOUND', //在 TFTP 服务器上找不到文件。
                    [69] => 'CURLE_TFTP_PERM', //在 TFTP 服务器上遇到权限问题。
                    [70] => 'CURLE_REMOTE_DISK_FULL', //服务器磁盘空间不足。
                    [71] => 'CURLE_TFTP_ILLEGAL', //TFTP 操作非法。
                    [72] => 'CURLE_TFTP_UNKNOWNID', //TFTP 传输 ID 未知。
                    [73] => 'CURLE_REMOTE_FILE_EXISTS', //文件已存在，无法覆盖。
                    [74] => 'CURLE_TFTP_NOSUCHUSER', //运行正常的 TFTP 服务器不会返回此错误。
                    [75] => 'CURLE_CONV_FAILED', //字符转换失败。
                    [76] => 'CURLE_CONV_REQD', //调用方必须注册转换回调。
                    [77] => 'CURLE_SSL_CACERT_BADFILE', //读取 SSL CA 证书时遇到问题（可能是路径错误或访问权限问题）。
                    [78] => 'CURLE_REMOTE_FILE_NOT_FOUND', //网址中引用的资源不存在。
                    [79] => 'CURLE_SSH', //SSH 会话中发生无法识别的错误。
                    [80] => 'CURLE_SSL_SHUTDOWN_FAILED', //无法终止 SSL 连接。
                    [81] => 'CURLE_AGAIN', //套接字尚未准备好 发送/接收 等待，直到准备就绪，然后重试。该返回码仅从 curl_easy_recv 和 curl_easy_send 返回。
                    [82] => 'CURLE_SSL_CRL_BADFILE', //无法加载 CRL 文件。
                    [83] => 'CURLE_SSL_ISSUER_ERROR', //发卡行检查失败。
                    [84] => 'CURLE_FTP_PRET_FAILED', //FTP服务器不理解PRET命令或不支持给定参数。使用CURLOPT_CUSTOMREQUEST时要小心，自定义LIST命令也将在PASV之前使用PRET CMD发送。
                    [85] => 'CURLE_RTSP_CSEQ_ERROR', //RTSP CSeq 数字不匹配。
                    [86] => 'CURLE_RTSP_SESSION_ERROR', //RTSP 会话标识符不匹配。
                    [87] => 'CURLE_FTP_BAD_FILE_LIST', //无法解析 FTP 文件列表（在 FTP 通配符下载期间）。
                    [88] => 'CURLE_CHUNK_FAILED', //块回调报告错误。
                    [89] => 'CURLE_NO_CONNECTION_AVAILABLE', //（仅供内部使用，不会由 libcurl 返回）没有可用的连接，会话将排队。
                    [90] => 'CURLE_SSL_PINNEDPUBKEYNOTMATCH', //无法匹配使用 CURLOPT_PINNEDPUBLICKEY 指定的固定键。
                    [91] => 'CURLE_SSL_INVALIDCERTSTATUS', //当用 CURLOPT_SSL_VERIFYSTATUS 询问时，状态返回失败。
                    [92] => 'CURLE_HTTP2_STREAM', //HTTP/2 中的流错误。
                    [93] => 'CURLE_RECURSIVE_API_CALL', //一个API函数在回调中被调用。
                */
                // 如果出错，返回false
                return false;
            } else {
                // 返回字符串数据
                return $res;
            }
            // 关闭curl会话
            curl_close($ch);
        } else {
            log_message('error', '服务器尚未开启curl扩展');
            echo '服务器尚未开启curl扩展';
        }
    }
}
// 精简采购链接，兼容淘宝、天猫、1688、京东、拼多多网页链接
if (!function_exists('shop_url_parse')) {
    function shop_url_parse(string $shopUrl)
    {
        if (isset($shopUrl) && strlen($shopUrl > 0)) {
            $shopUrl = str_replace(['<', '>'], ['&lt', '&gt'], $shopUrl); // 修正前端jqGrid getRowData获取的链接htmlDecode转义问题
            $pUrl = parse_url($shopUrl);
            if (isset($pUrl['host'])) {
                if (isset($pUrl['query'])) {
                    parse_str($pUrl['query'], $params);
                    $shopUrl = ($pUrl['scheme'] ?? 'http') . '://' . $pUrl['host'] . ($pUrl['path'] ? ($pUrl['path'] . '?') : '/') . (isset($params['id']) ? ('id=' . $params['id'] . (isset($params['skuId']) ? ('&skuId=' . $params['skuId']) : (isset($params['sku_properties']) ? ('&sku_properties=' . $params['sku_properties']) : ''))) : (isset($params['goods_id']) ? ('goods_id=' . $params['goods_id']) : ''));
                } else {
                    $shopUrl = ($pUrl['scheme'] ?? 'http') . '://' . $pUrl['host'] . ($pUrl['path'] ?? '');
                }
            } else {
                $shopUrl = '';
            }
        } else {
            $shopUrl = '';
        }
        return $shopUrl;
    }
}
// 计算快递运费
if (!function_exists('deliver_amount_calc')) {
    function deliver_amount_calc(string $company, string $address, float $weight = 0)
    {
        $amount = 0;
        if ($company && $address && $weight > 0) {
            switch ($company) {
                case '顺丰速运':
                    if ('重庆' == $address) {
                        if (0 <= $weight && $weight < 20) {
                            $amount = round(11 + max(($weight - 1), 0) * 2);
                        } else {
                            $amount = round(11 + max(($weight - 1), 0) * 3);
                        }
                    } elseif ('四川' == $address) {
                        $amount = round(14 + max(($weight - 1), 0) * 2);
                    } elseif (in_array($address, ['湖北', '陕西', '云南', '山西', '河南', '安徽', '江西', '湖南', '广西', '甘肃', '宁夏'])) {
                        if (0 <= $weight && $weight < 30) {
                            $amount = round(18 + max(($weight - 1), 0) * 5);
                        } else {
                            $amount = round(max($weight, 30) * 5);
                        }
                    } elseif (in_array($address, ['北京', '广东', '上海', '天津', '辽宁', '江苏', '河北', '内蒙', '山东', '浙江', '福建', '海南', '青海'])) {
                        if (0 <= $weight && $weight < 30) {
                            $amount = round(18 + max(($weight - 1), 0) * 6);
                        } else {
                            $amount = round(max($weight, 30) * 6);
                        }
                    } elseif ('贵州' == $address) {
                        $amount = round(18 + max(($weight - 1), 0) * 8);
                    } elseif (in_array($address, ['吉林', '黑龙', '新疆'])) {
                        if (0 <= $weight && $weight < 30) {
                            $amount = round(18 + max(($weight - 1), 0) * 9);
                        } else {
                            $amount = round(max($weight, 30) * 9);
                        }
                    } elseif ('西藏' == $address) {
                        $amount = round(22 + max(($weight - 1), 0) * 14);
                    } elseif (in_array($address, ['香港', '澳门', '台湾'])) {
                        $amount = max($weight, 1) * 30;
                    } else {
                        $amount = max($weight, 1) * 50;
                    }
                    break;
                case '邮政快递':
                    if (in_array($address, ['重庆', '四川', '贵州', '陕西', '云南', '湖北', '广西', '湖南', '河南', '山西', '甘肃', '宁夏', '江西', '海南', '河北', '广东', '安徽', '山东', '北京', '江苏', '浙江', '天津', '福建', '上海', '内蒙'])) { // 一、二、三区
                        if (0 <= $weight && $weight < 3) {
                            $amount = 2.8;
                        } elseif (3 <= $weight && $weight < 5) {
                            $amount = 4.5;
                        } else {
                            $amount = 4.5 + (ceil($weight - 1) * 1.5);
                        }
                    } elseif (in_array($address, ['辽宁', '吉林', '黑龙'])) { // 四区
                        if (0 <= $weight && $weight <= 3) {
                            $amount = 5.6 + (ceil(max(($weight - 1), 0)) * 1.6);
                        } elseif (3 < $weight && $weight <= 5) {
                            $amount = 7 + (ceil($weight - 1) * 2);
                        } else {
                            $amount = 11.2 + (ceil($weight - 1) * 3.2);
                        }
                    } elseif (in_array($address, ['青海'])) { // 五区一
                        if (0 <= $weight && $weight <= 3) {
                            $amount = 12.8 + (ceil(max(($weight - 1), 0)) * 9.6);
                        } elseif (3 < $weight && $weight <= 5) {
                            $amount = 13.6 + (ceil($weight - 1) * 10.2);
                        } else {
                            $amount = 16 + (ceil($weight - 1) * 12);
                        }
                    } elseif (in_array($address, ['新疆', '西藏'])) { // 五区二
                        if (0 <= $weight && $weight <= 5) {
                            $amount = 13.6 + (ceil(max(($weight - 1), 0)) * 10.2);
                        } else {
                            $amount = 16 + (ceil($weight - 1) * 12);
                        }
                    } elseif (in_array($address, ['香港', '澳门', '台湾'])) { // 港澳台
                        $amount = max($weight, 1) * 20;
                    } else { //海外
                        $amount = max($weight, 1) * 40;
                    }
                    break;
                case '申通快递':
                    if (in_array($address, ['重庆', '湖南', '江西', '江苏', '浙江', '安徽', '广东', '湖北', '河南', '山东', '陕西', '天津', '河北', '广西', '山西', '四川', '福建', '贵州', '云南'])) {
                        if (0 <= $weight && $weight <= 1) {
                            $amount = 2.7;
                        } elseif (1 < $weight && $weight <= 3) {
                            $amount = 2.9;
                        } elseif (3 < $weight && $weight <= 4) {
                            $amount = 4;
                        } elseif (4 < $weight && $weight <= 5) {
                            $amount = 5;
                        } else {
                            $amount = 2.6 + ($weight * 1.7);
                        }
                    } elseif ('上海' == $address) {
                        if (0 <= $weight && $weight <= 1) {
                            $amount = 3.7;
                        } elseif (1 < $weight && $weight <= 2) {
                            $amount = 3.9;
                        } elseif (2 < $weight && $weight <= 3) {
                            $amount = 4.1;
                        } elseif (3 < $weight && $weight <= 4) {
                            $amount = 6;
                        } elseif (4 < $weight && $weight <= 5) {
                            $amount = 6.5;
                        } else {
                            $amount = $weight * 2.5;
                        }
                    } elseif ('北京' == $address) {
                        if (0 <= $weight && $weight <= 1) {
                            $amount = 4;
                        } elseif (1 < $weight && $weight <= 2) {
                            $amount = 4.2;
                        } elseif (2 < $weight && $weight <= 3) {
                            $amount = 4.4;
                        } elseif (3 < $weight && $weight <= 4) {
                            $amount = 6;
                        } elseif (4 < $weight && $weight <= 5) {
                            $amount = 6.5;
                        } else {
                            $amount = $weight * 2.5;
                        }
                    } elseif (in_array($address, ['辽宁', '黑龙', '吉林', '海南', '甘肃', '宁夏', '青海'])) {
                        if (0 <= $weight && $weight <= 1) {
                            $amount = 3.5;
                        } elseif (1 < $weight && $weight <= 2) {
                            $amount = 4;
                        } elseif (2 < $weight && $weight <= 3) {
                            $amount = 6;
                        } elseif (3 < $weight && $weight <= 5) {
                            $amount = $weight * 2.5;
                        } else {
                            $amount = $weight * 2.5;
                        }
                    } elseif ('内蒙' == $address) {
                        $amount = 8 + max(($weight - 1), 0) * 6;
                    } elseif (in_array($address, ['新疆', '西藏'])) {
                        $amount = 12 + max(($weight - 1), 0) * 12;
                    } elseif (in_array($address, ['香港', '澳门', '台湾'])) {
                        $amount = max($weight, 1) * 20;
                    } else { //海外
                        $amount = max($weight, 1) * 40;
                    }
                    break;
                case '百世快运':
                    if ('重庆' == $address) {
                        if (0 <= $weight && $weight < 30) {
                            $amount = 30;
                        } elseif (30 <= $weight && $weight < 100) {
                            $amount = ceil($weight) * 0.7;
                        } elseif (100 <= $weight && $weight < 500) {
                            $amount = ceil($weight) * 0.6;
                        } elseif (500 <= $weight && $weight < 1000) {
                            $amount = ceil($weight) * 0.5;
                        } else {
                            $amount = ceil($weight) * 0.5;
                        }
                    } elseif ('四川' == $address) {
                        if (0 <= $weight && $weight < 30) {
                            $amount = 30;
                        } elseif (30 <= $weight && $weight < 100) {
                            $amount = ceil($weight) * 1;
                        } elseif (100 <= $weight && $weight < 500) {
                            $amount = ceil($weight) * 0.8;
                        } elseif (500 <= $weight && $weight < 1000) {
                            $amount = ceil($weight) * 0.7;
                        } else {
                            $amount = ceil($weight) * 0.6;
                        }
                    } elseif ('贵州' == $address) {
                        if (0 <= $weight && $weight < 30) {
                            $amount = 40;
                        } elseif (30 <= $weight && $weight < 100) {
                            $amount = ceil($weight) * 1.3;
                        } elseif (100 <= $weight && $weight < 500) {
                            $amount = ceil($weight) * 1.2;
                        } elseif (500 <= $weight && $weight < 1000) {
                            $amount = ceil($weight) * 1;
                        } else {
                            $amount = ceil($weight) * 0.85;
                        }
                    } elseif ('安徽' == $address) {
                        if (0 <= $weight && $weight < 30) {
                            $amount = 40;
                        } elseif (30 <= $weight && $weight < 100) {
                            $amount = ceil($weight) * 1.4;
                        } elseif (100 <= $weight && $weight < 500) {
                            $amount = ceil($weight) * 1.4;
                        } elseif (500 <= $weight && $weight < 1000) {
                            $amount = ceil($weight) * 1;
                        } else {
                            $amount = ceil($weight) * 0.9;
                        }
                    } elseif ('北京' == $address) {
                        if (0 <= $weight && $weight < 30) {
                            $amount = 40;
                        } elseif (30 <= $weight && $weight < 100) {
                            $amount = ceil($weight) * 1.5;
                        } elseif (100 <= $weight && $weight < 500) {
                            $amount = ceil($weight) * 1.5;
                        } elseif (500 <= $weight && $weight < 1000) {
                            $amount = ceil($weight) * 0.95;
                        } else {
                            $amount = ceil($weight) * 0.9;
                        }
                    } elseif ('甘肃' == $address) {
                        if (0 <= $weight && $weight < 30) {
                            $amount = 40;
                        } elseif (30 <= $weight && $weight < 100) {
                            $amount = ceil($weight) * 1.7;
                        } elseif (100 <= $weight && $weight < 500) {
                            $amount = ceil($weight) * 1.6;
                        } elseif (500 <= $weight && $weight < 1000) {
                            $amount = ceil($weight) * 1.2;
                        } else {
                            $amount = ceil($weight) * 1.1;
                        }
                    } elseif ('福建' == $address) {
                        if (0 <= $weight && $weight < 30) {
                            $amount = 40;
                        } elseif (30 <= $weight && $weight < 100) {
                            $amount = ceil($weight) * 1.5;
                        } elseif (100 <= $weight && $weight < 500) {
                            $amount = ceil($weight) * 1.5;
                        } elseif (500 <= $weight && $weight < 1000) {
                            $amount = ceil($weight) * 1.2;
                        } else {
                            $amount = ceil($weight) * 1.1;
                        }
                    } elseif ('广东' == $address) {
                        if (0 <= $weight && $weight < 30) {
                            $amount = 40;
                        } elseif (30 <= $weight && $weight < 100) {
                            $amount = ceil($weight) * 1.8;
                        } elseif (100 <= $weight && $weight < 500) {
                            $amount = ceil($weight) * 1.5;
                        } elseif (500 <= $weight && $weight < 1000) {
                            $amount = ceil($weight) * 0.95;
                        } else {
                            $amount = ceil($weight) * 0.9;
                        }
                    } elseif ('广西' == $address) {
                        if (0 <= $weight && $weight < 30) {
                            $amount = 40;
                        } elseif (30 <= $weight && $weight < 100) {
                            $amount = ceil($weight) * 1.7;
                        } elseif (100 <= $weight && $weight < 500) {
                            $amount = ceil($weight) * 1.6;
                        } elseif (500 <= $weight && $weight < 1000) {
                            $amount = ceil($weight) * 1.4;
                        } else {
                            $amount = ceil($weight) * 1.1;
                        }
                    } elseif ('海南' == $address) {
                        if (0 <= $weight && $weight < 30) {
                            $amount = 40;
                        } elseif (30 <= $weight && $weight < 100) {
                            $amount = ceil($weight) * 1.7;
                        } elseif (100 <= $weight && $weight < 500) {
                            $amount = ceil($weight) * 1.6;
                        } elseif (500 <= $weight && $weight < 1000) {
                            $amount = ceil($weight) * 1.5;
                        } else {
                            $amount = ceil($weight) * 1.3;
                        }
                    } elseif ('河北' == $address) {
                        if (0 <= $weight && $weight < 30) {
                            $amount = 40;
                        } elseif (30 <= $weight && $weight < 100) {
                            $amount = ceil($weight) * 1.5;
                        } elseif (100 <= $weight && $weight < 500) {
                            $amount = ceil($weight) * 1.5;
                        } elseif (500 <= $weight && $weight < 1000) {
                            $amount = ceil($weight) * 1.2;
                        } else {
                            $amount = ceil($weight) * 1;
                        }
                    } elseif ('河南' == $address) {
                        if (0 <= $weight && $weight < 30) {
                            $amount = 40;
                        } elseif (30 <= $weight && $weight < 100) {
                            $amount = ceil($weight) * 1.5;
                        } elseif (100 <= $weight && $weight < 500) {
                            $amount = ceil($weight) * 1.5;
                        } elseif (500 <= $weight && $weight < 1000) {
                            $amount = ceil($weight) * 1.1;
                        } else {
                            $amount = ceil($weight) * 0.95;
                        }
                    } elseif ('黑龙' == $address) {
                        if (0 <= $weight && $weight < 30) {
                            $amount = 80;
                        } elseif (30 <= $weight && $weight < 100) {
                            $amount = ceil($weight) * 1.8;
                        } elseif (100 <= $weight && $weight < 500) {
                            $amount = ceil($weight) * 1.8;
                        } elseif (500 <= $weight && $weight < 1000) {
                            $amount = ceil($weight) * 1.5;
                        } else {
                            $amount = ceil($weight) * 1.5;
                        }
                    } elseif ('辽宁' == $address) {
                        if (0 <= $weight && $weight < 30) {
                            $amount = 60;
                        } elseif (30 <= $weight && $weight < 100) {
                            $amount = ceil($weight) * 1.8;
                        } elseif (100 <= $weight && $weight < 500) {
                            $amount = ceil($weight) * 1.7;
                        } elseif (500 <= $weight && $weight < 1000) {
                            $amount = ceil($weight) * 1.5;
                        } else {
                            $amount = ceil($weight) * 1.3;
                        }
                    } elseif ('吉林' == $address) {
                        if (0 <= $weight && $weight < 30) {
                            $amount = 60;
                        } elseif (30 <= $weight && $weight < 100) {
                            $amount = ceil($weight) * 1.8;
                        } elseif (100 <= $weight && $weight < 500) {
                            $amount = ceil($weight) * 1.8;
                        } elseif (500 <= $weight && $weight < 1000) {
                            $amount = ceil($weight) * 1.6;
                        } else {
                            $amount = ceil($weight) * 1.5;
                        }
                    } elseif (in_array($address, ['湖北', '湖南'])) {
                        if (0 <= $weight && $weight < 30) {
                            $amount = 40;
                        } elseif (30 <= $weight && $weight < 100) {
                            $amount = ceil($weight) * 1.5;
                        } elseif (100 <= $weight && $weight < 500) {
                            $amount = ceil($weight) * 1.4;
                        } elseif (500 <= $weight && $weight < 1000) {
                            $amount = ceil($weight) * 1.2;
                        } else {
                            $amount = ceil($weight) * 0.9;
                        }
                    } elseif (in_array($address, ['江苏', '江西'])) {
                        if (0 <= $weight && $weight < 30) {
                            $amount = 40;
                        } elseif (30 <= $weight && $weight < 100) {
                            $amount = ceil($weight) * 1.5;
                        } elseif (100 <= $weight && $weight < 500) {
                            $amount = ceil($weight) * 1.4;
                        } elseif (500 <= $weight && $weight < 1000) {
                            $amount = ceil($weight) * 1.2;
                        } else {
                            $amount = ceil($weight) * 1;
                        }
                    } elseif ('内蒙' == $address) {
                        if (0 <= $weight && $weight < 30) {
                            $amount = 80;
                        } elseif (30 <= $weight && $weight < 100) {
                            $amount = ceil($weight) * 1.8;
                        } elseif (100 <= $weight && $weight < 500) {
                            $amount = ceil($weight) * 1.8;
                        } elseif (500 <= $weight && $weight < 1000) {
                            $amount = ceil($weight) * 1.6;
                        } else {
                            $amount = ceil($weight) * 1.4;
                        }
                    } elseif ('宁夏' == $address) {
                        if (0 <= $weight && $weight < 30) {
                            $amount = 80;
                        } elseif (30 <= $weight && $weight < 100) {
                            $amount = ceil($weight) * 1.8;
                        } elseif (100 <= $weight && $weight < 500) {
                            $amount = ceil($weight) * 1.7;
                        } elseif (500 <= $weight && $weight < 1000) {
                            $amount = ceil($weight) * 1.4;
                        } else {
                            $amount = ceil($weight) * 1.3;
                        }
                    } elseif ('青海' == $address) {
                        if (0 <= $weight && $weight < 30) {
                            $amount = 40;
                        } elseif (30 <= $weight && $weight < 100) {
                            $amount = ceil($weight) * 2;
                        } elseif (100 <= $weight && $weight < 500) {
                            $amount = ceil($weight) * 1.8;
                        } elseif (500 <= $weight && $weight < 1000) {
                            $amount = ceil($weight) * 1.5;
                        } else {
                            $amount = ceil($weight) * 1.3;
                        }
                    } elseif ('山东' == $address) {
                        if (0 <= $weight && $weight < 30) {
                            $amount = 40;
                        } elseif (30 <= $weight && $weight < 100) {
                            $amount = ceil($weight) * 1.6;
                        } elseif (100 <= $weight && $weight < 500) {
                            $amount = ceil($weight) * 1.5;
                        } elseif (500 <= $weight && $weight < 1000) {
                            $amount = ceil($weight) * 1.3;
                        } else {
                            $amount = ceil($weight) * 1;
                        }
                    } elseif ('山西' == $address) {
                        if (0 <= $weight && $weight < 30) {
                            $amount = 40;
                        } elseif (30 <= $weight && $weight < 100) {
                            $amount = ceil($weight) * 1.6;
                        } elseif (100 <= $weight && $weight < 500) {
                            $amount = ceil($weight) * 1.5;
                        } elseif (500 <= $weight && $weight < 1000) {
                            $amount = ceil($weight) * 1.1;
                        } else {
                            $amount = ceil($weight) * 1;
                        }
                    } elseif ('陕西' == $address) {
                        if (0 <= $weight && $weight < 30) {
                            $amount = 40;
                        } elseif (30 <= $weight && $weight < 100) {
                            $amount = ceil($weight) * 1.4;
                        } elseif (100 <= $weight && $weight < 500) {
                            $amount = ceil($weight) * 1.2;
                        } elseif (500 <= $weight && $weight < 1000) {
                            $amount = ceil($weight) * 1.1;
                        } else {
                            $amount = ceil($weight) * 0.9;
                        }
                    } elseif ('上海' == $address) {
                        if (0 <= $weight && $weight < 30) {
                            $amount = 40;
                        } elseif (30 <= $weight && $weight < 100) {
                            $amount = ceil($weight) * 1.5;
                        } elseif (100 <= $weight && $weight < 500) {
                            $amount = ceil($weight) * 1.4;
                        } elseif (500 <= $weight && $weight < 1000) {
                            $amount = ceil($weight) * 1.1;
                        } else {
                            $amount = ceil($weight) * 0.9;
                        }
                    } elseif ('天津' == $address) {
                        if (0 <= $weight && $weight < 30) {
                            $amount = 40;
                        } elseif (30 <= $weight && $weight < 100) {
                            $amount = ceil($weight) * 1.5;
                        } elseif (100 <= $weight && $weight < 500) {
                            $amount = ceil($weight) * 1.4;
                        } elseif (500 <= $weight && $weight < 1000) {
                            $amount = ceil($weight) * 1;
                        } else {
                            $amount = ceil($weight) * 0.95;
                        }
                    } elseif ('西藏' == $address) {
                        if (0 <= $weight && $weight < 30) {
                            $amount = 80;
                        } elseif (30 <= $weight && $weight < 100) {
                            $amount = ceil($weight) * 4;
                        } elseif (100 <= $weight && $weight < 500) {
                            $amount = ceil($weight) * 3.5;
                        } elseif (500 <= $weight && $weight < 1000) {
                            $amount = ceil($weight) * 3;
                        } else {
                            $amount = ceil($weight) * 2.6;
                        }
                    } elseif ('新疆' == $address) {
                        if (0 <= $weight && $weight < 30) {
                            $amount = 80;
                        } elseif (30 <= $weight && $weight < 100) {
                            $amount = ceil($weight) * 3.8;
                        } elseif (100 <= $weight && $weight < 500) {
                            $amount = ceil($weight) * 3.3;
                        } elseif (500 <= $weight && $weight < 1000) {
                            $amount = ceil($weight) * 2.2;
                        } else {
                            $amount = ceil($weight) * 1.8;
                        }
                    } elseif ('云南' == $address) {
                        if (0 <= $weight && $weight < 30) {
                            $amount = 40;
                        } elseif (30 <= $weight && $weight < 100) {
                            $amount = ceil($weight) * 1.8;
                        } elseif (100 <= $weight && $weight < 500) {
                            $amount = ceil($weight) * 1.5;
                        } elseif (500 <= $weight && $weight < 1000) {
                            $amount = ceil($weight) * 1.3;
                        } else {
                            $amount = ceil($weight) * 1.2;
                        }
                    } elseif ('浙江' == $address) {
                        if (0 <= $weight && $weight < 30) {
                            $amount = 40;
                        } elseif (30 <= $weight && $weight < 100) {
                            $amount = ceil($weight) * 1.5;
                        } elseif (100 <= $weight && $weight < 500) {
                            $amount = ceil($weight) * 1.4;
                        } elseif (500 <= $weight && $weight < 1000) {
                            $amount = ceil($weight) * 1;
                        } else {
                            $amount = ceil($weight) * 0.9;
                        }
                    } elseif (in_array($address, ['香港', '澳门', '台湾'])) {
                        if (0 <= $weight && $weight < 30) {
                            $amount = 80;
                        } elseif (30 <= $weight && $weight < 100) {
                            $amount = ceil($weight) * 4.8;
                        } elseif (100 <= $weight && $weight < 500) {
                            $amount = ceil($weight) * 4;
                        } elseif (500 <= $weight && $weight < 1000) {
                            $amount = ceil($weight) * 3;
                        } else {
                            $amount = ceil($weight) * 2.2;
                        }
                    } else { //海外
                        $amount = 50 + ceil($weight) * 10;
                    }
                    break;
                case '圆通速递':
                    if ('重庆' == $address) {
                        if (0 <= $weight && $weight < 3) {
                            $amount = 3;
                        } elseif (3 <= $weight && $weight < 5) {
                            $amount = 3.7;
                        } elseif (5 <= $weight && $weight < 18) {
                            $amount = 3.7 + ((($weight - intval($weight)) > 0.5 ? intval($weight) : (intval($weight) - 0.5)) * 0.6);
                        } else {
                            $amount = 3.7 + ((($weight - intval($weight)) > 0.5 ? intval($weight) : (intval($weight) - 0.5)) * 1);
                        }
                    } elseif ('四川' == $address) {
                        if (0 <= $weight && $weight < 3) {
                            $amount = 3;
                        } elseif (3 <= $weight && $weight < 5) {
                            $amount = 3.7;
                        } elseif (5 <= $weight && $weight < 18) {
                            $amount = 3.7 + ((($weight - intval($weight)) > 0.5 ? intval($weight) : (intval($weight) - 0.5)) * 1);
                        } else {
                            $amount = 3.7 + ((($weight - intval($weight)) > 0.5 ? intval($weight) : (intval($weight) - 0.5)) * 1.5);
                        }
                    } elseif (in_array($address, ['北京', '天津', '上海', '广东', '江苏', '浙江', '安徽', '江西', '湖北', '湖南', '河北', '河南', '广西', '贵州', '福建', '山东', '云南', '陕西'])) {
                        if (0 <= $weight && $weight < 3) {
                            $amount = 3;
                        } elseif (3 <= $weight && $weight < 5) {
                            $amount = 3.7;
                        } elseif (5 <= $weight && $weight < 18) {
                            $amount = 3.7 + ((($weight - intval($weight)) > 0.5 ? intval($weight) : (intval($weight) - 0.5)) * 1.7);
                        } else {
                            $amount = 3.7 + ((($weight - intval($weight)) > 0.5 ? intval($weight) : (intval($weight) - 0.5)) * 2.5);
                        }
                    } elseif (in_array($address, ['甘肃', '青海', '宁夏', '山西', '海南', '吉林', '辽宁', '黑龙'])) {
                        if (0 <= $weight && $weight < 3) {
                            $amount = 3;
                        } elseif (3 <= $weight && $weight < 5) {
                            $amount = 3.7;
                        } elseif (5 <= $weight && $weight < 18) {
                            $amount = 4.2 + ((($weight - intval($weight)) > 0.5 ? intval($weight) : (intval($weight) - 0.5)) * 5);
                        } else {
                            $amount = 4.2 + ((($weight - intval($weight)) > 0.5 ? intval($weight) : (intval($weight) - 0.5)) * 6);
                        }
                    } elseif ('内蒙' == $address) {
                        $amount = max((($weight - intval($weight)) > 0.5 ? (intval($weight) + 1) : (intval($weight) + 0.5)), 1) * 8;
                    } elseif (in_array($address, ['新疆', '西藏'])) {
                        $amount = max((($weight - intval($weight)) > 0.5 ? (intval($weight) + 1) : (intval($weight) + 0.5)), 1) * 15;
                    } elseif (in_array($address, ['香港', '澳门', '台湾'])) { // 港澳台
                        $amount = max($weight, 1) * 20;
                    } else { //海外
                        $amount = max($weight, 1) * 40;
                    }
                    break;
                case '京东快递':
                    if ('重庆' == $address) {
                        if (0 <= $weight && $weight <= 1) {
                            $amount = 11;
                        } else {
                            $amount = round(11 + max(($weight - 1), 0) * 2, 2);
                        }
                    } elseif ('四川' == $address) {
                        if (0 <= $weight && $weight <= 1) {
                            $amount = 12;
                        } else {
                            $amount = round(12 + max(($weight - 1), 0) * 2, 2);
                        }
                    } elseif (in_array($address, ['贵州', '湖北', '陕西'])) {
                        if (0 <= $weight && $weight <= 1) {
                            $amount = 15;
                        } else {
                            $amount = round(15 + max(($weight - 1), 0) * 5, 2);
                        }
                    } elseif (in_array($address, ['云南', '天津', '北京', '河南', '河北', '江西', '福建', '广东', '广西', '海南', '湖南', '江苏', '浙江', '上海', '安徽', '山东', '山西', '甘肃', '青海', '宁夏'])) {
                        if (0 <= $weight && $weight <= 1) {
                            $amount = 16;
                        } else {
                            $amount = round(16 + max(($weight - 1), 0) * 6, 2);
                        }
                    } elseif (in_array($address, ['吉林', '辽宁', '内蒙'])) {
                        if (0 <= $weight && $weight <= 1) {
                            $amount = 17;
                        } else {
                            $amount = round(17 + max(($weight - 1), 0) * 6, 2);
                        }
                    } elseif ($address == '新疆') {
                        if (0 <= $weight && $weight <= 1) {
                            $amount = 17;
                        } else {
                            $amount = round(17 + max(($weight - 1), 0) * 8, 2);
                        }
                    } elseif ($address == '黑龙') {
                        if (0 <= $weight && $weight <= 1) {
                            $amount = 18;
                        } else {
                            $amount = round(18 + max(($weight - 1), 0) * 8, 2);
                        }
                    } elseif ($address == '西藏') {
                        if (0 <= $weight && $weight <= 1) {
                            $amount = 20;
                        } else {
                            $amount = round(20 + max(($weight - 1), 0) * 10, 2);
                        }
                    } elseif (in_array($address, ['香港', '澳门', '台湾'])) { // 港澳台
                        $amount = max($weight, 1) * 30;
                    } else { //海外
                        $amount = 50 + ceil($weight) * 10;
                    }
                    $amount = round($amount * 0.8, 2); // 打8折
                    break;
                case '京东重货':
                    if ($address == '重庆') {
                        if (0 <= $weight && $weight <= 20) {
                            $amount = 34;
                        } elseif (20 < $weight && $weight <= 60) {
                            $amount = round(34 + max(($weight - 20), 0) * 1.7, 2);
                        } else {
                            $amount = round(34 + 40 * 1.7 + max(($weight - 60), 0) * 1.1, 2);
                        }
                    } elseif ($address == '四川') {
                        if (0 <= $weight && $weight <= 20) {
                            $amount = 54;
                        } elseif (20 < $weight && $weight <= 60) {
                            $amount = round(54 + max(($weight - 20), 0) * 2, 2);
                        } else {
                            $amount = round(54 + 40 * 2 + max(($weight - 60), 0) * 1.3, 2);
                        }
                    } elseif ($address == '云南') {
                        if (0 <= $weight && $weight <= 20) {
                            $amount = 70;
                        } elseif (20 < $weight && $weight <= 60) {
                            $amount = round(70 + max(($weight - 20), 0) * 3, 2);
                        } else {
                            $amount = round(70 + 40 * 3 + max(($weight - 60), 0) * 2.5, 2);
                        }
                    } elseif ($address == '贵州') {
                        if (0 <= $weight && $weight <= 20) {
                            $amount = 55;
                        } elseif (20 < $weight && $weight <= 60) {
                            $amount = round(55 + max(($weight - 20), 0) * 2.8, 2);
                        } else {
                            $amount = round(55 + 40 * 2.8 + max(($weight - 60), 0) * 2.1, 2);
                        }
                    } elseif ($address == '天津') {
                        if (0 <= $weight && $weight <= 20) {
                            $amount = 86;
                        } elseif (20 < $weight && $weight <= 60) {
                            $amount = round(86 + max(($weight - 20), 0) * 3.3, 2);
                        } else {
                            $amount = round(86 + 40 * 3.3 + max(($weight - 60), 0) * 2.5, 2);
                        }
                    } elseif ($address == '北京') {
                        if (0 <= $weight && $weight <= 20) {
                            $amount = 86;
                        } elseif (20 < $weight && $weight <= 60) {
                            $amount = round(86 + max(($weight - 20), 0) * 3.7, 2);
                        } else {
                            $amount = round(86 + 40 * 3.7 + max(($weight - 60), 0) * 3.3, 2);
                        }
                    } elseif ($address == '河南') {
                        if (0 <= $weight && $weight <= 20) {
                            $amount = 65;
                        } elseif (20 < $weight && $weight <= 60) {
                            $amount = round(65 + max(($weight - 20), 0) * 3.1, 2);
                        } else {
                            $amount = round(65 + 40 * 3.1 + max(($weight - 60), 0) * 2.5, 2);
                        }
                    } elseif ($address == '河北') {
                        if (0 <= $weight && $weight <= 20) {
                            $amount = 82;
                        } elseif (20 < $weight && $weight <= 60) {
                            $amount = round(82 + max(($weight - 20), 0) * 3.3, 2);
                        } else {
                            $amount = round(82 + 40 * 3.3 + max(($weight - 60), 0) * 2.5, 2);
                        }
                    } elseif ($address == '江西') {
                        if (0 <= $weight && $weight <= 20) {
                            $amount = 64;
                        } elseif (20 < $weight && $weight <= 60) {
                            $amount = round(64 + max(($weight - 20), 0) * 2.7, 2);
                        } else {
                            $amount = round(64 + 40 * 2.7 + max(($weight - 60), 0) * 2.4, 2);
                        }
                    } elseif ($address == '福建') {
                        if (0 <= $weight && $weight <= 20) {
                            $amount = 96;
                        } elseif (20 < $weight && $weight <= 60) {
                            $amount = round(96 + max(($weight - 20), 0) * 3.9, 2);
                        } else {
                            $amount = round(96 + 40 * 3.9 + max(($weight - 60), 0) * 2.4, 2);
                        }
                    } elseif ($address == '广东') {
                        if (0 <= $weight && $weight <= 20) {
                            $amount = 81;
                        } elseif (20 < $weight && $weight <= 60) {
                            $amount = round(81 + max(($weight - 20), 0) * 3.4, 2);
                        } else {
                            $amount = round(81 + 40 * 3.4 + max(($weight - 60), 0) * 2.5, 2);
                        }
                    } elseif ($address == '广西') {
                        if (0 <= $weight && $weight <= 20) {
                            $amount = 72;
                        } elseif (20 < $weight && $weight <= 60) {
                            $amount = round(72 + max(($weight - 20), 0) * 3.3, 2);
                        } else {
                            $amount = round(72 + 40 * 3.3 + max(($weight - 60), 0) * 3, 2);
                        }
                    } elseif ($address == '海南') {
                        if (0 <= $weight && $weight <= 20) {
                            $amount = 79;
                        } elseif (20 < $weight && $weight <= 60) {
                            $amount = round(79 + max(($weight - 20), 0) * 3.7, 2);
                        } else {
                            $amount = round(79 + 40 * 3.7 + max(($weight - 60), 0) * 2.8, 2);
                        }
                    } elseif ($address == '湖北') {
                        if (0 <= $weight && $weight <= 20) {
                            $amount = 82;
                        } elseif (20 < $weight && $weight <= 60) {
                            $amount = round(82 + max(($weight - 20), 0) * 3.3, 2);
                        } else {
                            $amount = round(82 + 40 * 3.3 + max(($weight - 60), 0) * 2, 2);
                        }
                    } elseif ($address == '湖南') {
                        if (0 <= $weight && $weight <= 20) {
                            $amount = 65;
                        } elseif (20 < $weight && $weight <= 60) {
                            $amount = round(65 + max(($weight - 20), 0) * 2.9, 2);
                        } else {
                            $amount = round(65 + 40 * 2.9 + max(($weight - 60), 0) * 2.1, 2);
                        }
                    } elseif ($address == '江苏') {
                        if (0 <= $weight && $weight <= 20) {
                            $amount = 84;
                        } elseif (20 < $weight && $weight <= 60) {
                            $amount = round(84 + max(($weight - 20), 0) * 3.4, 2);
                        } else {
                            $amount = round(84 + 40 * 3.4 + max(($weight - 60), 0) * 2.2, 2);
                        }
                    } elseif ($address == '浙江') {
                        if (0 <= $weight && $weight <= 20) {
                            $amount = 86;
                        } elseif (20 < $weight && $weight <= 60) {
                            $amount = round(86 + max(($weight - 20), 0) * 4.1, 2);
                        } else {
                            $amount = round(86 + 40 * 4.1 + max(($weight - 60), 0) * 3, 2);
                        }
                    } elseif ($address == '上海') {
                        if (0 <= $weight && $weight <= 20) {
                            $amount = 86;
                        } elseif (20 < $weight && $weight <= 60) {
                            $amount = round(86 + max(($weight - 20), 0) * 3.6, 2);
                        } else {
                            $amount = round(86 + 40 * 3.6 + max(($weight - 60), 0) * 2.3, 2);
                        }
                    } elseif ($address == '安徽') {
                        if (0 <= $weight && $weight <= 20) {
                            $amount = 65;
                        } elseif (20 < $weight && $weight <= 60) {
                            $amount = round(65 + max(($weight - 20), 0) * 3.3, 2);
                        } else {
                            $amount = round(65 + 40 * 3.3 + max(($weight - 60), 0) * 2.5, 2);
                        }
                    } elseif ($address == '山东') {
                        if (0 <= $weight && $weight <= 20) {
                            $amount = 100;
                        } elseif (20 < $weight && $weight <= 60) {
                            $amount = round(100 + max(($weight - 20), 0) * 3.8, 2);
                        } else {
                            $amount = round(100 + 40 * 3.8 + max(($weight - 60), 0) * 2.5, 2);
                        }
                    } elseif ($address == '山西') {
                        if (0 <= $weight && $weight <= 20) {
                            $amount = 66;
                        } elseif (20 < $weight && $weight <= 60) {
                            $amount = round(66 + max(($weight - 20), 0) * 3.5, 2);
                        } else {
                            $amount = round(66 + 40 * 3.5 + max(($weight - 60), 0) * 2.7, 2);
                        }
                    } elseif ($address == '陕西') {
                        if (0 <= $weight && $weight <= 20) {
                            $amount = 82;
                        } elseif (20 < $weight && $weight <= 60) {
                            $amount = round(82 + max(($weight - 20), 0) * 3.2, 2);
                        } else {
                            $amount = round(82 + 40 * 3.2 + max(($weight - 60), 0) * 2.1, 2);
                        }
                    } elseif ($address == '吉利') {
                        if (0 <= $weight && $weight <= 20) {
                            $amount = 147;
                        } elseif (20 < $weight && $weight <= 60) {
                            $amount = round(147 + max(($weight - 20), 0) * 4.5, 2);
                        } else {
                            $amount = round(147 + 40 * 4.5 + max(($weight - 60), 0) * 2.7, 2);
                        }
                    } elseif ($address == '辽宁') {
                        if (0 <= $weight && $weight <= 20) {
                            $amount = 99;
                        } elseif (20 < $weight && $weight <= 60) {
                            $amount = round(99 + max(($weight - 20), 0) * 4.1, 2);
                        } else {
                            $amount = round(99 + 40 * 4.1 + max(($weight - 60), 0) * 2.7, 2);
                        }
                    } elseif ($address == '黑龙') {
                        if (0 <= $weight && $weight <= 20) {
                            $amount = 149;
                        } elseif (20 < $weight && $weight <= 60) {
                            $amount = round(149 + max(($weight - 20), 0) * 4.3, 2);
                        } else {
                            $amount = round(149 + 40 * 4.3 + max(($weight - 60), 0) * 3, 2);
                        }
                    } elseif ($address == '甘肃') {
                        if (0 <= $weight && $weight <= 20) {
                            $amount = 85;
                        } elseif (20 < $weight && $weight <= 60) {
                            $amount = round(85 + max(($weight - 20), 0) * 3.6, 2);
                        } else {
                            $amount = round(85 + 40 * 3.6 + max(($weight - 60), 0) * 2.4, 2);
                        }
                    } elseif ($address == '青海') {
                        if (0 <= $weight && $weight <= 20) {
                            $amount = 85;
                        } elseif (20 < $weight && $weight <= 60) {
                            $amount = round(85 + max(($weight - 20), 0) * 3.8, 2);
                        } else {
                            $amount = round(85 + 40 * 3.8 + max(($weight - 60), 0) * 2.6, 2);
                        }
                    } elseif ($address == '宁夏') {
                        if (0 <= $weight && $weight <= 20) {
                            $amount = 86;
                        } elseif (20 < $weight && $weight <= 60) {
                            $amount = round(86 + max(($weight - 20), 0) * 3.8, 2);
                        } else {
                            $amount = round(86 + 40 * 3.8 + max(($weight - 60), 0) * 3.1, 2);
                        }
                    } elseif ($address == '内蒙') {
                        if (0 <= $weight && $weight <= 20) {
                            $amount = 118;
                        } elseif (20 < $weight && $weight <= 60) {
                            $amount = round(118 + max(($weight - 20), 0) * 3.9, 2);
                        } else {
                            $amount = round(118 + 40 * 3.9 + max(($weight - 60), 0) * 2.6, 2);
                        }
                    } elseif ($address == '西藏') {
                        if (0 <= $weight && $weight <= 20) {
                            $amount = 108;
                        } elseif (20 < $weight && $weight <= 60) {
                            $amount = round(108 + max(($weight - 20), 0) * 4.8, 2);
                        } else {
                            $amount = round(108 + 40 * 4.8 + max(($weight - 60), 0) * 4.7, 2);
                        }
                    } elseif ($address == '新疆') {
                        if (0 <= $weight && $weight <= 20) {
                            $amount = 159;
                        } elseif (20 < $weight && $weight <= 60) {
                            $amount = round(159 + max(($weight - 20), 0) * 5.8, 2);
                        } else {
                            $amount = round(159 + 40 * 5.8 + max(($weight - 60), 0) * 4, 2);
                        }
                    } elseif (in_array($address, ['香港', '澳门', '台湾'])) {
                        $amount = 200 + max($weight, 1) * 10;
                    } else { //海外
                        $amount = 350 + max($weight, 1) * 10;
                    }
                    $amount = round($amount * 0.8, 2); // 打8折
                    break;
                default:
                    $amount = 0;
            }
        }
        return round($amount, 2);
    }
}
// 以经纬度查询具体地址
if (!function_exists('address_search')) {
    function address_search(float $latitude, float $longitude)
    {
        $data = [];
        if (isset($latitude) && isset($longitude)) {
            $url = 'https://map.jiqrxx.com/getJson/search1.php';
            $res = global_curl($url, [
                'post' => 1,
                'postfields' => 'address=' . $latitude . '%2C' . $longitude . '&type=LatLng',
                'httpheader' => [
                    'accept: */*',
                    'accept-language: zh-CN,zh;q=0.9,en;q=0.8,en-GB;q=0.7,en-US;q=0.6',
                    'cache-control: no-cache',
                    'content-length: 40',
                    'content-type: application/x-www-form-urlencoded',
                    'cookie: Hm_lvt_60904f9492f40a9c0b5451452c8a037f=1733377250; Hm_lpvt_60904f9492f40a9c0b5451452c8a037f=1733377250; HMACCOUNT=CCDF87EE385FC546',
                    'origin: https://map.jiqrxx.com',
                    'pragma: no-cache',
                    'referer: https://map.jiqrxx.com/jingweidu/',
                    'user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36 Edg/131.0.0.0'
                ]
            ]);
            if (false !== $res) {
                $res = json_decode($res, true);
                $data = $res['result'];
            }
        }
        return $data;
    }
}
