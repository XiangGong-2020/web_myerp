<?php

namespace App\Controllers\Base;
use App\Controllers\BaseController;

class CameraView extends BaseController
{
    // 获取播放url与accessToken
    public function index()
    {
        if (!$this->myservice->verifyRight('CAMERA_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/CameraView/index 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $accessToken = $this->db->table('option')->select('optionValue')->where('optionName', 'ys7_accessToken')->get()->getRowArray()['optionValue'] ?? '';
        $deviceSerial = $this->request->getGetPost('deviceSerial');
        $channelNo = intval($this->request->getGetPost('channelNo'));
        $type = $this->request->getGetPost('type');
        $v = [];
        // 获取播放url
        if ($accessToken && $deviceSerial && $channelNo > 0) {
            // 第一次获取url
            $res = global_curl('https://open.ys7.com/api/lapp/v2/live/address/get', [
                'post' => 1,
                'postfields' => 'accessToken=' . $accessToken . '&deviceSerial=' . $deviceSerial . '&channelNo=' . $channelNo . '&type=' . ($type == 'rec' ? 2 : 1),
                'httpheader' => [
                    'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
                    'Host: open.ys7.com'
                ]
            ]);
            if (false !== $res) {
                $res = json_decode($res, true);
                if ($res['code'] == 200) {
                    $v = ['url' => $res['data']['url'], 'accessToken' => $accessToken];
                } elseif ($res['code'] == 401 || $res['code'] == 10002) { // 可能是accessToken失效，重新获取accessToken并存数据库
                    $accessTokenRes = global_curl('https://open.ys7.com/api/lapp/token/get', [
                        'post' => 1,
                        'postfields' => 'appKey=233dde3af9d44466a8dada73012d5461&appSecret=bffde29d7cdb454af4fcd14eabebeb7a',
                        'httpheader' => [
                            'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
                            'Host: open.ys7.com'
                        ]
                    ]);
                    if (false !== $accessTokenRes) {
                        $accessTokenRes = json_decode($accessTokenRes, true);
                        if ($accessTokenRes['code'] == 200) {
                            $accessToken = $accessTokenRes['data']['accessToken'];
                            $this->db->table('option')->where('optionName', 'ys7_accessToken')->update(['optionValue' => $accessToken]);
                            // 第二次获取url
                            $res2 = global_curl('https://open.ys7.com/api/lapp/v2/live/address/get', [
                                'post' => 1,
                                'postfields' => 'accessToken=' . $accessToken . '&deviceSerial=' . $deviceSerial . '&channelNo=' . $channelNo . '&type=' . ($type == 'rec' ? 2 : 1),
                                'httpheader' => [
                                    'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
                                    'Host: open.ys7.com'
                                ]
                            ]);
                            if (false !== $res2) {
                                $res2 = json_decode($res2, true);
                            }
                            if (is_array($res2) && isset($res2['code']) && $res2['code'] == 200 && isset($res2['data']['url'])) {
                                $v = ['url' => $res2['data']['url'], 'accessToken' => $accessToken];
                            } else {
                                return $this->response->setJSON(['status' => -1, 'msg' => '获取萤石云播放地址失败！']);
                            }
                        } else {
                            return $this->response->setJSON(['status' => -1, 'msg' => $accessTokenRes['msg']]);
                        }
                    } else {
                        return $this->response->setJSON(['status' => -1, 'msg' => '获取萤石云accessToken失败！']);
                    }
                } else {
                    return $this->response->setJSON(['status' => -1, 'msg' => '获取萤石云播放地址失败！']);
                }
            } else {
                return $this->response->setJSON(['status' => -1, 'msg' => '获取萤石云播放地址失败！']);
            }
        } else {
            return $this->response->setJSON(['status' => -1, 'msg' => '参数错误！']);
        }
        $json['status'] = 200;
        $json['msg'] = 'success';
        $json['data'] = $v;
        return $this->response->setJSON($json);
    }
}
