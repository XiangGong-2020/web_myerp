<?php
namespace App\Controllers;
class Login extends BaseController
{
    public function index()
    {
        $data = $this->request->getPost(NULL, NULL, NULL);
        if (is_array($data) && count($data) > 0) {
            if (!isset($data['type']) && !token(1)) {
                return $this->response->setJSON(['status' => -1, 'msg' => 'token验证未通过']);
            }
            if (strlen($data['userName']) < 1) {
                return $this->response->setJSON(['status' => -1, 'msg' => '账号不能为空']);
            }
            if (strlen($data['userPwd']) < 1) {
                return $this->response->setJSON(['status' => -1, 'msg' => '密码不能为空']);
            }
            $user = $this->db->table('user')->groupStart()->where('userName', $data['userName'])->orWhere('mobile', $data['userName'])->groupEnd()->where('isDelete', 0)->get()->getRowArray();
            if ($user) {
                if ($user['isDisable'] == 1) {
                    return $this->response->setJSON(['status' => -1, 'msg' => '员工已离职']);
                }
                if ($user['userPwd'] == md5($data['userPwd']) || $user['userPwd'] == $data['userPwd']) {
                    $myerp['uId'] = intval($user['id']);
                    $myerp['uName'] = $user['name'];
                    $myerp['isFirstLogin'] = true;
                    $myerp['lastLoginTime'] = date('Y-m-d H:i:s');
                    $myerp['noAlertCurLogin'] = [];
                    $this->session->set('myerp', $myerp);
                    $this->myservice->log('登陆成功，账号：' . $user['userName'] . '，方式：电脑端，IP：' . $this->request->getIPAddress());
                    return $this->response->setJSON(['status' => 200, 'msg' => '登录成功']);
                }
                return $this->response->setJSON(['status' => -1, 'msg' => '密码错误']);
            }
            return $this->response->setJSON(['status' => -1, 'msg' => '账号不存在或已删除']);
        } else {
            return view('login');
        }
    }
    public function out()
    {
        $this->session->destroy();
        return redirect()->to('/login');
    }
}
