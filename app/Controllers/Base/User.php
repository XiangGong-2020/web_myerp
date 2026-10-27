<?php

namespace App\Controllers\Base;
use App\Controllers\BaseController;

class User extends BaseController
{
    /** 用户相关 */
    // 列表
    public function list()
    {
        if (!$this->myservice->verifyRight('SYS_DEFAULT')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/User/list 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $page = max(intval($this->request->getGetPost('page')), 1);
        $rows = max(intval($this->request->getGetPost('rows')), 10000);
        $sidx = $this->request->getGetPost('sidx');
        $sord = strtoupper((string)$this->request->getGetPost('sord')) === 'ASC' ? 'ASC' : 'DESC';
        $company = $this->request->getGetPost('company');
        $department = $this->request->getGetPost('department');
        $position = $this->request->getGetPost('position');
        $isDisable = intval($this->request->getGetPost('isDisable') ?? -1);
        $normal = $this->request->getGetPost('normal');
        $skey = $this->request->getGetPost('skey');

        $builder = $this->db->table('user');
        $builder->where('isDelete', 0);
        if ($company) {
            $builder->where('company', $company);
        }
        if ($department !== '' && $department !== null) {
            $keywords = array_values(array_filter(explode(',', $department), 'strlen'));
            $builder->whereIn('department', $keywords);
        }
        if ($position !== '' && $position !== null) {
            $keywords = array_values(array_filter(explode(',', $position), 'strlen'));
            $builder->whereIn('position', $keywords);
        }
        if ($isDisable >= 0) {
            $builder->where('isDisable', $isDisable);
        }
        if ($normal != '') {
            $builder->whereNotIn('name', ["系统","审查员"]);
        }
        if ($skey !== '' && $skey !== null) {
            $keywords = array_values(array_filter(explode(',', $skey), 'strlen'));
            foreach ($keywords as $kw) {
                $builder->groupStart()
                    ->like('name', $this->db->escapeLikeString($kw))
                    ->orLike('sfzNo', $this->db->escapeLikeString($kw))
                    ->orLike('bankNo', $this->db->escapeLikeString($kw))
                    ->orLike('zfbNo', $this->db->escapeLikeString($kw))
                    ->orLike('wechat', $this->db->escapeLikeString($kw))
                    ->orLike('mobile', $this->db->escapeLikeString($kw))
                    ->orLike('userName', $this->db->escapeLikeString($kw))
                    ->orLike('duty', $this->db->escapeLikeString($kw))
                    ->orLike('description', $this->db->escapeLikeString($kw))
                ->groupEnd();
            }
        }
        $allowedSort = ['id','name','userName','avatar','level','roleId','evaluationIds','sex','company','department','position','superiorsId','superiorsName','mobile','joinDate','leaveDate','duty','isDisable','isBuySocialSecurity','buySocialSecurityDate','stopSocialSecurityDate'];
        $orderField  = $sidx ? (in_array($sidx, $allowedSort, true) ? $sidx : 'id') : 'id';
        $list = (clone $builder)->orderBy($orderField, $sord)->limit($rows)->offset($rows * ($page - 1))->get()->getResultArray();
        $total = $builder->countAllResults();
        $v = [];
        foreach ($list as $arr => $row) {
            $v[$arr]['id'] = intval($row['id']);
            $v[$arr]['name'] = $row['name'];
            $v[$arr]['userName'] = $row['userName'];
            $v[$arr]['avatar'] = $row['avatar'];
            $v[$arr]['level'] = $row['level'];
            $v[$arr]['roleId'] = intval($row['roleId']);
            $v[$arr]['evaluationIds'] = $row['evaluationIds'];
            $v[$arr]['sex'] = $row['sex'];
            $v[$arr]['company'] = $row['company'];
            $v[$arr]['department'] = $row['department'];
            $v[$arr]['position'] = $row['position'];
            $v[$arr]['superiorsId'] = intval($row['superiorsId']);
            $v[$arr]['superiorsName'] = $row['superiorsName'];
            $v[$arr]['mobile'] = $row['mobile'];
            $v[$arr]['joinDate'] = $row['joinDate'];
            $v[$arr]['leaveDate'] = $row['leaveDate'];
            $v[$arr]['duty'] = $row['duty'];
            $v[$arr]['isDisable'] = intval($row['isDisable']);
            $v[$arr]['isBuySocialSecurity'] = intval($row['isBuySocialSecurity']);
            $v[$arr]['buySocialSecurityDate'] = $row['buySocialSecurityDate'];
            $v[$arr]['stopSocialSecurityDate'] = $row['stopSocialSecurityDate'];
        }
        $json['status'] = 200;
        $json['msg'] = 'success';
        $json['data']['page'] = $page;
        $json['data']['records'] = $total;
        $json['data']['total'] = ceil($json['data']['records'] / $rows);
        $json['data']['rows'] = $v;
        return $this->response->setJSON($json);
    }
    // 查询
    public function query()
    {
        if (!$this->myservice->verifyRight('SYS_DEFAULT')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/User/query 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $data = $this->db->table('user')->where(['id' => $id, 'isDelete' => 0])->get()->getRowArray();
        if ($data) {
            // 只返回必要的信息，不要暴露用户敏感信息
            unset($data['userPwd']);
            $json['status'] = 200;
            $json['msg'] = 'success';
            $json['data'] = $data;
            return $this->response->setJSON($json);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
    // 新增
    public function add()
    {
        if (!$this->myservice->verifyRight('USER_STAFF_ADD')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/User/add 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $data = $this->request->getPost();
        if (is_array($data) && count($data) > 0) {
            $data = $this->validform($data);
            // 如果返回的是 Response 对象，直接返回它
            if ($data instanceof \CodeIgniter\HTTP\Response) {
                return $data;
            }
            $data['userName'] = $data['mobile'];
            $data['userPwd'] = md5(substr($data['sfzNo'], -6));
            $info = array_intersect_key($data, array_flip(['userName', 'userPwd', 'name', 'sex', 'joinDate', 'company', 'department', 'position', 'superiorsId', 'superiorsName', 'education', 'school', 'major', 'nation', 'hukou', 'sfzNo', 'bankNo', 'zfbNo', 'mobile', 'wechat', 'animal', 'astro', 'birthdaySolar', 'birthdayLunar', 'homeAddress', 'fixedSalary', 'bonusPerson', 'isDisable', 'leaveDate', 'isBuySocialSecurity', 'buySocialSecurityDate', 'stopSocialSecurityDate', 'duty', 'description']));
            $this->db->transStart();
            $this->db->table('user')->insert($info);
            $data['id'] = $this->db->insertID();
            $this->myservice->log('新增了员工档案，姓名:' . $data['name']);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '新增成功！', 'data' => $data]);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '提交的是空数据！']);
    }
    // 编辑
    public function update()
    {
        if (!$this->myservice->verifyRight('USER_STAFF_UPDATE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/User/update 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $data = $this->request->getPost();
        if (is_array($data) && count($data) > 0) {
            $data = $this->validform($data);
            // 如果返回的是 Response 对象，直接返回它
            if ($data instanceof \CodeIgniter\HTTP\Response) {
                return $data;
            }
            $info = array_intersect_key($data, array_flip(['name', 'sex', 'joinDate', 'company', 'department', 'position', 'superiorsId', 'superiorsName', 'education', 'school', 'major', 'nation', 'hukou', 'sfzNo', 'bankNo', 'zfbNo', 'mobile', 'wechat', 'animal', 'astro', 'birthdaySolar', 'birthdayLunar', 'homeAddress', 'fixedSalary', 'bonusPerson', 'isDisable', 'leaveDate', 'isBuySocialSecurity', 'buySocialSecurityDate', 'stopSocialSecurityDate', 'duty', 'description']));
            $this->db->transStart();
            $this->db->table('user')->where('id', $data['id'])->update($info);
            if ($data['isDisable'] == 1) {
                // 如果是离职，则置空该员工负责的维护产品的维护人字段
                $this->db->table('goods')->where('serName', $data['name'])->update(['serName' => '']);
            }
            $this->myservice->log('编辑了员工档案，姓名:' . $data['name']);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '编辑成功！', 'data' => $data]);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '提交的是空数据！']);
    }
    // 数据验证
    private function validform(array $data)
    {
        if (strlen($data['name']) < 1) {
            return $this->response->setJSON(['status' => -1, 'msg' => '姓名不能为空！']);
        }
        if (strlen($data['sex']) < 1) {
            return $this->response->setJSON(['status' => -1, 'msg' => '性别不能为空！']);
        }
        if (strlen($data['joinDate']) < 1) {
            return $this->response->setJSON(['status' => -1, 'msg' => '入职日期不能为空！']);
        }
        if (strlen($data['company']) < 1) {
            return $this->response->setJSON(['status' => -1, 'msg' => '所属主体不能为空！']);
        }
        if (strlen($data['department']) < 1) {
            return $this->response->setJSON(['status' => -1, 'msg' => '所属部门不能为空！']);
        }
        if (strlen($data['position']) < 1) {
            return $this->response->setJSON(['status' => -1, 'msg' => '所在职位不能为空！']);
        }
        if (strlen($data['sfzNo']) < 1) {
            return $this->response->setJSON(['status' => -1, 'msg' => '身份证号不能为空！']);
        }
        if (strlen($data['mobile']) < 1) {
            return $this->response->setJSON(['status' => -1, 'msg' => '常用手机不能为空！']);
        }
        if ($data['isBuySocialSecurity'] < 0) {
            return $this->response->setJSON(['status' => -1, 'msg' => '社保状态不能为空！']);
        }
        if ($data['isBuySocialSecurity'] == 1 && strlen($data['buySocialSecurityDate']) < 1) {
            return $this->response->setJSON(['status' => -1, 'msg' => '设置已参保请选择准确参保日期！']);
        }
        if ($data['isBuySocialSecurity'] == 2 && strlen($data['stopSocialSecurityDate']) < 1) {
            return $this->response->setJSON(['status' => -1, 'msg' => '设置已停保请选择准确停保日期！']);
        }
        if (strlen($data['duty']) < 1) {
            return $this->response->setJSON(['status' => -1, 'msg' => '工作职责不能为空！']);
        }
        $data['id'] = isset($data['id']) ? intval($data['id']) : -1;
        if ($data['id'] > 0) {
            if ($this->db->table('user')->where(['id !=' => $data['id'], 'sfzNo' => $data['sfzNo']])->countAllResults() > 0) {
                return $this->response->setJSON(['status' => -1, 'msg' => '身份证号已经存在，请仔细检查是否重复！']);
            }
            if ($this->db->table('user')->where(['id !=' => $data['id'], 'mobile' => $data['mobile']])->countAllResults() > 0) {
                return $this->response->setJSON(['status' => -1, 'msg' => '常用手机已经存在，请仔细检查是否重复！']);
            }
        } else {
            if ($this->db->table('user')->where(['sfzNo' => $data['sfzNo']])->countAllResults() > 0) {
                return $this->response->setJSON(['status' => -1, 'msg' => '身份证号已经存在，请仔细检查是否重复！']);
            }
            if ($this->db->table('user')->where(['mobile' => $data['mobile']])->countAllResults() > 0) {
                return $this->response->setJSON(['status' => -1, 'msg' => '常用手机已经存在，请仔细检查是否重复！']);
            }
        }
        if ($data['isDisable'] < 0) {
            return $this->response->setJSON(['status' => -1, 'msg' => '在职状态不能为空！']);
        }
        if ($data['isDisable'] == 1) {
            if (strlen($data['leaveDate']) < 1 || $data['leaveDate'] == '0000-00-00') {
                return $this->response->setJSON(['status' => -1, 'msg' => '设置离职请选择准确离职日期！']);
            }
            $data['lever'] = '';
        } elseif ($data['isDisable'] == 0 && $data['leaveDate']) {
            $data['leaveDate'] = '0000-00-00';
        }
        return $data;
    }

    // 全员速览
    public function userMap()
    {
        if (!$this->myservice->verifyRight('SYS_DEFAULT')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/User/userMap 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $list = $this->db->table('user')
            ->whereNotIn('name', ['系统', '审查员'])
            ->where(['isDisable' => 0, 'isDelete' => 0])
            ->orderBy('joinDate', 'ASC')
            ->get()
            ->getResultArray();
        $today = date('Y-m-d');
        $v = [];
        foreach ($list as $arr => $row) {
            $v[$row['name']]['id'] = intval($row['id']);
            $v[$row['name']]['roleId'] = intval($row['roleId']);
            $v[$row['name']]['avatar'] = '/data/upfile/avatar/' . ($row['avatar'] ?: 'avatar.jpg') ;
            $v[$row['name']]['name'] = $row['name'];
            $v[$row['name']]['sex'] = $row['sex'];
            $v[$row['name']]['company'] = $row['company'];
            $v[$row['name']]['department'] = $row['department'];
            $v[$row['name']]['position'] = $row['position'];
            $v[$row['name']]['superiorsId'] = intval($row['superiorsId']);
            $v[$row['name']]['superiorsName'] = $row['superiorsName'];
            $v[$row['name']]['mobile'] = $row['mobile'];
            $v[$row['name']]['wechat'] = $row['wechat'];
            $v[$row['name']]['joinDate'] = $row['joinDate'];
            $v[$row['name']]['duty'] = $row['duty'];
            $v[$row['name']]['animal'] = $row['animal'];
            $v[$row['name']]['astro'] = $row['astro'];
            $v[$row['name']]['nation'] = $row['nation'];
            $v[$row['name']]['level'] = $row['level'];
            $clockinInfo = $this->db->table('clockin')->where(['uId' => $row['id'], 'clockDate ' => $today, 'isDelete' => 0])->get()->getRowArray();
            if ($clockinInfo) {
                if (($clockinInfo['amClock'] != '00:00:00' && $clockinInfo['pmClock'] == '00:00:00') || ($clockinInfo['amClock2'] !== '00:00:00' && $clockinInfo['pmClock2'] == '00:00:00') || ($clockinInfo['amClock3'] !== '00:00:00' && $clockinInfo['pmClock3'] == '00:00:00')) {
                    $v[$row['name']]['onDuty'] = '在岗';
                } elseif (($clockinInfo['pmClock'] != '00:00:00' && $clockinInfo['amClock2'] == '00:00:00') || ($clockinInfo['pmClock2'] != '00:00:00' && $clockinInfo['amClock3'] == '00:00:00') || $clockinInfo['pmClock3'] != '00:00:00') {
                    $v[$row['name']]['onDuty'] = '离岗';
                } else {
                    if ($clockinInfo['isCheck'] == 1) {
                        $v[$row['name']]['onDuty'] = '批准' . trim(str_replace(['补卡,', ',补卡', '补卡'], '', $clockinInfo['marks']), ',');
                    } else {
                        $v[$row['name']]['onDuty'] = '申请' . trim(str_replace(['补卡,', ',补卡', '补卡'], '', $clockinInfo['marks']), ',');
                    }
                }
            } else {
                if ($row['id'] == 7 || $row['id'] == 8 || $row['id'] == 103 || $row['id'] == 172 || $row['id'] == 195) {
                    $v[$row['name']]['onDuty'] = '';
                } else {
                    $v[$row['name']]['onDuty'] = '休息';
                }
            }
            if ($row['id'] == 7 || $row['id'] == 8) {
                $v[$row['name']]['groupLevel'] = 1;
            } elseif ($row['superiorsId'] == 7 || $row['superiorsId'] == 8) {
                $v[$row['name']]['groupLevel'] = 2;
            } else {
                $v[$row['name']]['groupLevel'] = 3;
            }
        }
        $json['status'] = 200;
        $json['msg'] = 'success';
        $json['data'] = $v;
        return $this->response->setJSON($json);
    }

    // 获取所有用户
    public function queryAll()
    {
        if (!$this->myservice->verifyRight('SYS_DEFAULT')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/User/queryAll 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $page = max(intval($this->request->getGetPost('page')), 1);
        $rows = max(intval($this->request->getGetPost('rows')), 10000);
        $department = $this->request->getGetPost('department');
        $position = $this->request->getGetPost('position');
        $roleId = intval($this->request->getGetPost('roleId') ?? -1);
        $isDisable = intval($this->request->getGetPost('isDisable') ?? -1);
        $normal = $this->request->getGetPost('normal');
        $member = $this->request->getGetPost('member');

        $builder = $this->db->table('user');
        $builder->where('isDelete', 0);
        if ($department !== '' && $department !== null) {
            $keywords = array_values(array_filter(explode(',', $department), 'strlen'));
            $builder->whereIn('department', $keywords);
        }
        if ($position !== '' && $position !== null) {
            $keywords = array_values(array_filter(explode(',', $position), 'strlen'));
            $builder->whereIn('position', $keywords);
        }
        if ($roleId >= 0) {
            $builder->where('roleId', $roleId);
        }
        if ($isDisable >= 0) {
            $builder->where('isDisable', $isDisable);
        } elseif ($isDisable == -1) {
            $builder->groupStart()
                ->where('leaveDate', '0000-00-00')
                ->orWhere('leaveDate >', date('Y-m-d', strtotime('-60 day')))
            ->groupEnd();
        }
        if ($normal == 'yes') {
            $builder->whereNotIn('name', ['系统', '审查员']);
        }
        if ($member == 'yes' && !in_array('SYS_ADMIN', $this->myerp['rights'])) {
            $builder->where('superiorsId', $this->myerp['uId']);
        }
        $list = (clone $builder)->orderBy('isDisable ASC, roleId DESC, id ASC')->limit($rows)->offset($rows * ($page - 1))->get()->getResultArray();
        $total = $builder->countAllResults();
        // 只返回必要的信息，不要暴露用户敏感信息
        $v = [];
        foreach ($list as $arr => $row) {
            $v[$arr]['id'] = intval($row['id']);
            $v[$arr]['name'] = $row['name'];
            $v[$arr]['isDisable'] = intval($row['isDisable']);
            $v[$arr]['evaluationIds'] = $row['evaluationIds'];
            $v[$arr]['groupId'] = 0;
            $v[$arr]['groupName'] = '';
            if (
                in_array('生产部', array_values(array_filter(explode(',', $department), 'strlen'))) && (
                    in_array('加工专员', array_values(array_filter(explode(',', $position), 'strlen')))
                    || in_array('配货专员', array_values(array_filter(explode(',', $position), 'strlen')))
                )
            ) {
                $groups = $this->db->table('pro_group')->select('id,name,type,members')->where('isDelete', 0)->get()->getResultArray();
                foreach ($groups as $k1 => $v1) {
                    if (strpos($v1['members'], $row['name']) !== false) {
                        $v[$arr]['groupId'] = intval($v1['id']);
                        $v[$arr]['groupName'] = $v1['name'];
                    }
                }
            }
        }
        if (
            in_array('生产部', array_values(array_filter(explode(',', $department), 'strlen'))) && (
                in_array('加工专员', array_values(array_filter(explode(',', $position), 'strlen')))
                || in_array('配货专员', array_values(array_filter(explode(',', $position), 'strlen')))
            )
        ) {
            // （二维数组）依次排序
            if (count($v) > 0) {
                $sort1 = array_column($v, 'groupId');
                $sort2 = array_column($v, 'isDisable');
                $sort3 = array_column($v, 'id');
                array_multisort($sort1, SORT_ASC, $sort2, SORT_ASC, $sort3, SORT_ASC, $v);
            }
        }
        $json['status'] = 200;
        $json['msg'] = 'success';
        $json['data']['page'] = $page;
        $json['data']['records'] = $total;
        $json['data']['total'] = ceil($json['data']['records'] / $rows);
        $json['data']['rows'] = $v;
        return $this->response->setJSON($json);
    }
    // 通过用户ID获取用户
    public function queryUserById()
    {
        $id = intval($this->request->getGetPost('id'));
        if ($id != $this->myerp['uId'] && !$this->myservice->verifyRight('USER_UPDATE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/User/queryUserById 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $data = $this->db->table('user')->where('id', $id)->get()->getRowArray();
        if ($data) {
            // 只返回必要的信息，不要暴露用户敏感信息
            unset($data['userPwd']);
            return $this->response->setJSON(['status' => 200, 'msg' => 'success', 'data' => $data]);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
    // 通过用户名获取用户
    public function queryUserByUserName()
    {
        $userName = $this->request->getGetPost('userName');
        if ($userName != $this->myerp['user']['userName'] && !$this->myservice->verifyRight('USER_UPDATE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/User/queryUserByUserName 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $data = $this->db->table('user')->where('userName', $userName)->get()->getRowArray();
        if ($data) {
            // 只返回必要的信息，不要暴露用户敏感信息
            unset($data['userPwd']);
            return $this->response->setJSON(['status' => 200, 'msg' => 'success', 'data' => $data]);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
    // 获取用户所有权限
    public function queryalluserright()
    {
        if (!$this->myservice->verifyRight('USER_UPDATE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/User/queryalluserright 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $userName = $this->request->getGetPost('userName');
        $name = $this->request->getGetPost('name');
        $data = $this->db->table('user')->where('userName', $userName)->orWhere('name', $name)->get()->getRowArray();
        if ($data) {
            $lever = explode(',', $data['lever']);
            $list = $this->db->table('right')->where(['isDelete' => 0, 'isDisable' => 0])->orderBy('ordnum ASC, id ASC')->get()->getResultArray();
            $right = array_column($list, 'name', 'id');
            $v = [];
            foreach ($list as $arr => $row) {
                $v[$arr]['fobjectid'] = $row['parentId'] > 0 ? $row['parentId'] : $row['id'];
                $v[$arr]['fobject'] = $row['parentId'] > 0 ? @$right[$row['parentId']] : $row['name'];
                $v[$arr]['faction'] = $row['level'] > 1 ? $row['name'] : '查询';
                $v[$arr]['fright'] = in_array($row['id'], $lever) ? 1 : 0;
                $v[$arr]['frightid'] = intval($row['id']);
            }
            $json['status'] = 200;
            $json['msg'] = 'success';
            $json['data']['rows'] = $v;
            $json['data']['records'] = count($list);
            return $this->response->setJSON($json);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
    // 分配系统权限给用户
    public function addrights2Outuser()
    {
        if (!$this->myservice->verifyRight('USER_UPDATE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/User/addrights2Outuser 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $userName = $this->request->getGetPost('userName');
        $rightid = $this->request->getGetPost('rightid');
        $allowVideoTypeIds = $this->request->getGetPost('allowVideoTypeIds');
        $data = $this->db->table('user')->where(['userName' => $userName])->get()->getRowArray();
        if ($data) {
            $this->db->transStart();
            $this->db->table('user')->where(['userName' => $userName])->update(['lever' => $rightid, 'allowVideoTypeIds' => $allowVideoTypeIds]);
            $this->myservice->log('更新了' . $data['name'] .'的员工账号' . $userName.'的权限');
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '操作成功！']);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
    // 重置用户密码
    public function resetPwd()
    {
        if (!$this->myservice->verifyRight('USER_RESETPWD')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/User/resetPwd 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $userName = $this->request->getGetPost('userName');
        $data = $this->db->table('user')->where(['userName' => $userName])->get()->getRowArray();
        if ($data) {
            $data['newpass'] = str_random(6, 'ABCDEFJHIJKMNOPQRSTUVWSYZ'); // 为了安全起见，重置密码设为随机6位大写字母
            $this->db->transStart();
            $this->db->table('user')->where(['userName' => $userName])->update(['userPwd' => md5($data['newpass'])]);
            $this->myservice->log('重置了员工账号' . $userName . '的密码');
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '操作成功！', 'data' => $data]);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }


    // 社保参保与薪资发放必需前置信息 补录
    public function fill()
    {
        if (!$this->myservice->verifyRight('SYS_DEFAULT')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/User/fill 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $data = $this->request->getPost();
        if (is_array($data) && count($data) > 0) {
            $info = array_intersect_key($data, array_flip(['hukou','bankNo', 'zfbNo']));
            $this->db->transStart();
            $this->db->table('user')->where(['id' => $this->myerp['uId']])->update($info);
            $this->myservice->log('补录了社保参保与薪资发放必需前置信息');
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '补录成功！', 'data' => $data]);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '提交的是空数据！']);
    }


    // 我的权限
    public function myRights()
    {
        if (!$this->myservice->verifyRight('SYS_DEFAULT')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/User/myRights 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $user = $this->db->table('user')->select('roleId, lever')->where(['id' => $this->myerp['uId']])->get()->getRowArray();
            if ($user) {
            if ($user['roleId'] == 1) {
                $rightsArr = $this->db->table('right')->select('id, name, parentId, path, level')->where(['isDisable' => 0, 'isDelete' => 0])->orderBy('id', 'ASC')->get()->getResultArray();
            } else {
                $rightsArr = $this->db->table('right')->select('id, name, parentId, path, level')->whereIn('id', explode(',', $user['lever']))->where(['isDisable' => 0, 'isDelete' => 0])->orderBy('id', 'ASC')->get()->getResultArray();
            }
            return $this->response->setJSON(['status' => 200, 'msg' => 'success', 'data' => $rightsArr]);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '用户不存在！']);
    }
    // 我的考评
    public function myEvaluation()
    {
        if (!$this->myservice->verifyRight('SYS_DEFAULT')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/User/myEvaluation 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $beginDate = $this->request->getGetPost('beginDate');
        $endDate = $this->request->getGetPost('endDate');
        //考评项目
        $evaluationIds = $this->db->table('user')->select('evaluationIds')->where(['id' => $this->myerp['uId']])->get()->getRowArray()['evaluationIds'] ?? '';
        if ($evaluationIds) {
            $evaluations = array_column($this->db->table('evaluation')->select('name')->whereIn('id', explode(',', $evaluationIds))->where(['isDelete' => 0])->orderBy('id', 'ASC')->get()->getResultArray(), 'name');
        } else {
            $evaluations = [];
        }
        //考评得分
        $evaluateScore = $this->db->table('bill_evaluate_info')->select('score')->where(['beginDate >=' => $beginDate, 'endDate <=' => $endDate, 'uId' => $this->myerp['uId'], 'isDelete' => 0])->get()->getRowArray()['score'] ?? 0;
        return $this->response->setJSON(['status' => 200, 'msg' => 'success', 'data' => ['evaluations' => $evaluations, 'evaluateScore' => $evaluateScore]]);
    }
    // 编辑资料
    public function setInfo()
    {
        if (!$this->myservice->verifyRight('SYS_DEFAULT')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/User/setInfo 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $data = $this->request->getPost();
        if (is_array($data) && count($data) > 0) {
            $this->db->transStart();
            $this->db->table('user')->where(['id' => $this->myerp['uId']])->update($data);
            $this->myservice->log('修改了自身资料，方式：电脑端');
            $data = $this->db->table('user')->where(['id' => $this->myerp['uId']])->get()->getRowArray();
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '编辑成功！', 'data' => $data]);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '提交的是空数据！']);
    }
    // 修改头像
    public function setAvatar()
    {
        if (!$this->myservice->verifyRight('SYS_DEFAULT')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/User/setAvatar 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        set_time_limit(0);
        session_write_close();
        if (empty($_FILES)){
            return $this->response->setJSON(['status' => -1, 'msg' => '没有上传文件！']);
        };
        $uppath = 'data/upfile/avatar/';
        $temp_file = $_FILES['file']['tmp_name'];
        $target_file = date("YmdHis") . rand(1000, 9000) . '.' . strrev(explode('.', strrev($_FILES['file']['name']))[0]);
        if (!is_dir($uppath)) {
            @umask(0);
            $ret = @mkdir($uppath, 0777);
            if ($ret === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '没有权限创建用于保存的文件夹！']);
            }
        }
        move_uploaded_file($temp_file, $uppath . $target_file);
        $this->db->transStart();
        $this->db->table('user')->where(['id' => $this->myerp['uId']])->update(['avatar' => $target_file]);
        $this->myservice->log('修改了头像，方式：电脑端');
        $this->db->transComplete();
        if ($this->db->transStatus() === false) {
            return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
        }
        return $this->response->setJSON(['status' => 200, 'msg' => '编辑成功！', 'data' => $target_file]);
    }
    // 修改密码
    public function setPassword()
    {
        if (!$this->myservice->verifyRight('SYS_DEFAULT')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/User/setPassword 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $data = $this->request->getPost();
        if (is_array($data) && count($data) > 0) {
            $info['userPwd'] = md5($data['userPwd']);
            $this->db->transStart();
            $this->db->table('user')->where(['id' => $this->myerp['uId']])->update($info);
            $this->myservice->log('修改了登陆密码，方式：电脑端');
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '修改成功！']);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '提交的是空数据！']);
    }


    // 成长等级 列表
    public function userLevelList()
    {
        if (!$this->myservice->verifyRight('USER_LEVEL_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/User/userLevelList 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $page = max(intval($this->request->getGetPost('page')), 1);
        $rows = max(intval($this->request->getGetPost('rows')), 100);
        $sidx = $this->request->getGetPost('sidx');
        $sord = strtoupper((string)$this->request->getGetPost('sord')) === 'ASC' ? 'ASC' : 'DESC';
        $order = $sidx ? $sidx . ' ' . $sord : 'minExp asc';
        $builder = $this->db->table('user_level');
        $builder->where('isDelete', 0);
        $allowedSort = ['id','name','minExp','maxExp','hourlyWage','description'];
        $orderField  = $sidx ? (in_array($sidx, $allowedSort, true) ? $sidx : 'id') : 'id';
        $list = (clone $builder)->orderBy($orderField, $sord)->limit($rows)->offset($rows * ($page - 1))->get()->getResultArray();
        $total = $builder->countAllResults();
        $v = [];
        foreach ($list as $arr => $row) {
            $v[$arr]['id'] = intval($row['id']);
            $v[$arr]['name'] = $row['name'];
            $v[$arr]['minExp'] = intval($row['minExp']);
            $v[$arr]['maxExp'] = intval($row['maxExp']);
            $v[$arr]['hourlyWage'] = $row['hourlyWage'];
            $v[$arr]['description'] = $row['description'];
        }
        $json['status'] = 200;
        $json['msg'] = 'success';
        $json['data']['page'] = $page;
        $json['data']['records'] = $total;
        $json['data']['total'] = ceil($json['data']['records'] / $rows);
        $json['data']['rows'] = $v;
        return $this->response->setJSON($json);
    }
    // 成长等级 查询
    public function userLevelQuery()
    {
        if (!$this->myservice->verifyRight('USER_LEVEL_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/User/userLevelQuery 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $data = $this->db->table('user_level')->where(['id' => $id, 'isDelete' => 0])->get()->getRowArray();
        if ($data) {
            $json['status'] = 200;
            $json['msg'] = 'success';
            $json['data'] = $data;
            return $this->response->setJSON($json);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
    // 成长等级 新增
    public function userLevelAdd()
    {
        if (!$this->myservice->verifyRight('USER_LEVEL_ADVANCE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/User/userLevelAdd 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $data = $this->request->getPost();
        if (is_array($data) && count($data) > 0) {
            $data = $this->userLevelValidform($data);
            // 如果返回的是 Response 对象，直接返回它
            if ($data instanceof \CodeIgniter\HTTP\Response) {
                return $data;
            }
            $info = array_intersect_key($data, array_flip(['name', 'minExp', 'maxExp', 'hourlyWage', 'description']));
            $this->db->transStart();
            $this->db->table('user_level')->insert($info);
            $data['id'] = $this->db->insertID();
            $this->myservice->log('新增了成长等级，等级名称:' . $data['name']);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '新增成功！', 'data' => $data]);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '提交的是空数据！']);
    }
    // 成长等级 编辑
    public function userLevelUpdate()
    {
        if (!$this->myservice->verifyRight('USER_LEVEL_ADVANCE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/User/userLevelUpdate 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $data = $this->request->getPost();
        if (is_array($data) && count($data) > 0) {
            $data = $this->userLevelValidform($data);
            // 如果返回的是 Response 对象，直接返回它
            if ($data instanceof \CodeIgniter\HTTP\Response) {
                return $data;
            }
            $info = array_intersect_key($data, array_flip(['name', 'minExp', 'maxExp', 'hourlyWage', 'description']));
            $this->db->transStart();
            $this->db->table('user_level')->where('id', $data['id'])->update($info);
            $this->myservice->log('编辑了成长等级，等级名称:' . $data['name']);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '编辑成功！', 'data' => $data]);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '提交的是空数据！']);
    }
    // 成长等级 数据验证
    private function userLevelValidform(array $data)
    {
        if (strlen($data['name']) < 1) {
            return $this->response->setJSON(['status' => -1, 'msg' => '等级名称不能为空！']);
        }
        if (intval($data['minExp']) < 0) {
            return $this->response->setJSON(['status' => -1, 'msg' => '最小经验不能为负！']);
        }
        if (intval($data['maxExp']) < 0) {
            return $this->response->setJSON(['status' => -1, 'msg' => '最大经验不能为负！']);
        }
        if (intval($data['minExp']) >= intval($data['maxExp'])) {
            return $this->response->setJSON(['status' => -1, 'msg' => '最小经验不能大于等于最大经验！']);
        }
        if (intval($data['hourlyWage']) < 0) {
            return $this->response->setJSON(['status' => -1, 'msg' => '时薪不能为负！']);
        }
        $data['id'] = isset($data['id']) ? intval($data['id']) : -1;
        if ($data['id'] > 0) {
            if ($this->db->table('user_level')->where(['id !=' => $data['id'], 'name' => $data['name']])->countAllResults() > 0) {
                return $this->response->setJSON(['status' => -1, 'msg' => '等级名称已经存在，请仔细检查是否重复！']);
            }
        } else {
            if ($this->db->table('user_level')->where(['name' => $data['name']])->countAllResults() > 0) {
                return $this->response->setJSON(['status' => -1, 'msg' => '等级名称已经存在，请仔细检查是否重复！']);
            }
        }
        return $data;
    }


    // 荣誉奖章 列表
    public function userMedalList()
    {
        if (!$this->myservice->verifyRight('SYS_DEFAULT')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/User/userMedalList 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $department = $this->request->getGetPost('department');
        $position = $this->request->getGetPost('position');
        $uName = $this->request->getGetPost('uName');
        $beginDate = $this->request->getGetPost('beginDate');
        $endDate = $this->request->getGetPost('endDate');
        $builder = $this->db->table('user_medal');
        $builder->where('isDelete', 0);
        if ($department) {
            $builder->where('department', $department);
        }
        if ($position) {
            $builder->where('position', $position);
        }
        if ($uName) {
            $builder->where('uName', $uName);
        }
        if ($beginDate) {
            $builder->where('date >=', substr(str_replace('-', '', $beginDate), 0, 6));
        }
        if ($endDate) {
            $builder->where('date <=', substr(str_replace('-', '', $endDate), 0, 6));
        }
        $list = $builder->orderBy('department DESC, position ASC, id ASC')->get()->getResultArray();
        $v = [];
        foreach ($list as $arr => $row) {
            if (isset($v[$row['uName']])) {
                $v[$row['uName']][] = $row;
            } else {
                $v[$row['uName']] = [];
                $v[$row['uName']][] = $row;
            }
        }
        $json['status'] = 200;
        $json['msg'] = 'success';
        $json['data'] = isset($v) ? $v : [];
        return $this->response->setJSON($json);
    }
}
