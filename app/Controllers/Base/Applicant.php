<?php

namespace App\Controllers\Base;
use App\Controllers\BaseController;

class Applicant extends BaseController
{
    // 列表
    public function list()
    {
        if (!$this->myservice->verifyRight('APPLICANT')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/Applicant/list 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $page = max((int)$this->request->getGetPost('page'), 1);
        $rows = max((int)$this->request->getGetPost('rows'), 100);
        $sidx = $this->request->getGetPost('sidx');
        $sord = strtoupper((string)$this->request->getGetPost('sord')) === 'ASC' ? 'ASC' : 'DESC';
        $skey = $this->request->getGetPost('skey');
        $builder = $this->db->table('applicant')->where('isDelete', 0);
        if ($skey !== '' && $skey !== null) {
            $keywords = array_values(array_filter(explode(',', $skey), 'strlen'));
            foreach ($keywords as $kw) {
                $builder->groupStart()
                    ->like('name', $this->db->escapeLikeString($kw))
                    ->orLike('sfzNo', $this->db->escapeLikeString($kw))
                    ->orLike('mobile', $this->db->escapeLikeString($kw))
                ->groupEnd();
            }
        }
        $allowedSort = ['id','joinDate','name','mobile','sex','birthday','identity','nativePlace','nation','political','healthy','marriage','education','graduation','school','major','salary'];
        $orderField  = $sidx ? (in_array($sidx, $allowedSort, true) ? $sidx : 'id') : 'id';
        $list = (clone $builder)->orderBy($orderField, $sord)->limit($rows)->offset($rows * ($page - 1))->get()->getResultArray();
        $total = $builder->countAllResults();
        $v = [];
        $sfz_arr = array_column($this->db->table('user')->select('sfzNo')->where('isDelete', 0)->get()->getResultArray(), 'sfzNo');
        foreach ($list as $arr => $row) {
            $v[$arr]['id'] = intval($row['id']);
            $v[$arr]['joinDate'] = $row['joinDate'];
            $v[$arr]['position'] = $row['position'];
            $v[$arr]['name'] = $row['name'];
            $v[$arr]['sex'] = $row['sex'];
            $v[$arr]['sfzNo'] = $row['sfzNo'];
            $v[$arr]['birthday'] = $row['birthday'];
            $v[$arr]['identity'] = $row['identity'];
            $v[$arr]['nativePlace'] = $row['nativePlace'];
            $v[$arr]['nation'] = $row['nation'];
            $v[$arr]['political'] = $row['political'];
            $v[$arr]['criminal'] = $row['criminal'];
            $v[$arr]['healthy'] = $row['healthy'];
            $v[$arr]['marriage'] = $row['marriage'];
            $v[$arr]['education'] = $row['education'];
            $v[$arr]['graduation'] = $row['graduation'];
            $v[$arr]['school'] = $row['school'];
            $v[$arr]['major'] = $row['major'];
            $v[$arr]['salary'] = intval($row['salary']);
            $v[$arr]['mobile'] = $row['mobile'];
            $v[$arr]['selfEvaluationOne'] = $row['selfEvaluationOne'];
            $v[$arr]['selfEvaluationTwo'] = $row['selfEvaluationTwo'];
            $v[$arr]['createdUser'] = in_array($row['sfzNo'], $sfz_arr) ? 1 : 0;
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
        if (!$this->myservice->verifyRight('APPLICANT')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/Applicant/query 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        if ($id > 0) {
            $data = $this->db->table('applicant')->where('id', $id)->get()->getRowArray();
            if ($data) {
                return $this->response->setJSON(['status' => 200, 'msg' => 'success', 'data' => $data]);
            }
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
    // 新增
    public function add()
    {
        if (!$this->myservice->verifyRight('APPLICANT')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/Applicant/add 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $data = $this->request->getPost();
        if (is_array($data) && count($data) > 0) {
            $data = $this->validform($data);
            // 如果返回的是 Response 对象，直接返回它
            if ($data instanceof \CodeIgniter\HTTP\Response) {
                return $data;
            }
            $data['isDelete'] = 0;
            $info = array_intersect_key($data, array_flip(['name','channel','department','position','joinDate','identity','sex','birthday','nativePlace','nation','political','criminal','healthy','marriage','education','entrance','graduation','school','major','sfzNo','nowAddress','homeAddress','wechat','mobile','salary','workOneTime','workOneCompany','workOnePosition','workOneEndReason','workOneWitness','workTwoTime','workTwoCompany','workTwoPosition','workTwoEndReason','workTwoWitness','familyOneRelationship','familyOneName','familyOneWorkPlace','familyOnePosition','familyOnePhone','familyTwoRelationship','familyTwoName','familyTwoWorkPlace','familyTwoPosition','familyTwoPhone','urgentName','urgentPhone','urgentRelationship','selfEvaluationOne','selfEvaluationTwo']));
            $this->db->transStart();
            $this->db->table('applicant')->insert($info);
            $data['id'] = $this->db->insertID();
            $this->myservice->log('新增了应聘档案，姓名:' . $data['name']);
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
        if (!$this->myservice->verifyRight('APPLICANT')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/Applicant/update 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $data = $this->request->getPost();
        if (is_array($data) && count($data) > 0) {
            $data = $this->validform($data);
            // 如果返回的是 Response 对象，直接返回它
            if ($data instanceof \CodeIgniter\HTTP\Response) {
                return $data;
            }
            $info = array_intersect_key($data, array_flip(['name','channel','department','position','joinDate','identity','sex','birthday','nativePlace','nation','political','criminal','healthy','marriage','education','entrance','graduation','school','major','sfzNo','nowAddress','homeAddress','wechat','mobile','salary','workOneTime','workOneCompany','workOnePosition','workOneEndReason','workOneWitness','workTwoTime','workTwoCompany','workTwoPosition','workTwoEndReason','workTwoWitness','familyOneRelationship','familyOneName','familyOneWorkPlace','familyOnePosition','familyOnePhone','familyTwoRelationship','familyTwoName','familyTwoWorkPlace','familyTwoPosition','familyTwoPhone','urgentName','urgentPhone','urgentRelationship','selfEvaluationOne','selfEvaluationTwo']));
            $this->db->transStart();
            $this->db->table('applicant')->where('id', $data['id'])->update($info);
            $this->myservice->log('编辑了应聘档案，姓名:' . $data['name']);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '编辑成功！', 'data' => $data]);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '提交的是空数据！']);
    }
    // 删除
    public function delete()
    {
        if (!$this->myservice->verifyRight('APPLICANT')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/Applicant/delete 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $data = $this->db->table('applicant')->where('id', $id)->get()->getRowArray();
        if ($data) {
            $this->db->transStart();
            $this->db->table('applicant')->where('id', $id)->update(['isDelete' => 1]);
            $this->myservice->log('删除了应聘档案，ID：'.$data['id'].'，姓名：'.$data['name']);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '删除成功！']);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
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
        if (strlen($data['sfzNo']) < 1) {
            return $this->response->setJSON(['status' => -1, 'msg' => '身份证号不能为空！']);
        }
        if (strlen($data['wechat']) < 1) {
            return $this->response->setJSON(['status' => -1, 'msg' => '常用微信不能为空！']);
        }
        if (strlen($data['mobile']) < 1) {
            return $this->response->setJSON(['status' => -1, 'msg' => '常用手机不能为空！']);
        }
        $data['id'] = isset($data['id']) ? intval($data['id']) : -1;
        if ($data['id'] > 0) {
            if ($this->db->table('applicant')->where(['id !=' => $data['id'], 'sfzNo' => $data['sfzNo'], 'isDelete' => 0])->countAllResults() > 0) {
                return $this->response->setJSON(['status' => -1, 'msg' => '身份证已经存在，请仔细检查是否重复！']);
            }
            if ($this->db->table('applicant')->where(['id !=' => $data['id'], 'wechat' => $data['wechat'], 'isDelete' => 0])->countAllResults() > 0) {
                return $this->response->setJSON(['status' => -1, 'msg' => '常用微信已经存在，请仔细检查是否重复！']);
            }
            if ($this->db->table('applicant')->where(['id !=' => $data['id'], 'mobile' => $data['mobile'], 'isDelete' => 0])->countAllResults() > 0){
                return $this->response->setJSON(['status' => -1, 'msg' => '常用手机已经存在，请仔细检查是否重复！']);
            }
        } else {
            if ($this->db->table('applicant')->where(['sfzNo' => $data['sfzNo'], 'isDelete' => 0])->countAllResults() > 0) {
                return $this->response->setJSON(['status' => -1, 'msg' => '身份证已经存在，请仔细检查是否重复！']);
            }
            if ($this->db->table('applicant')->where(['wechat' => $data['wechat'], 'isDelete' => 0])->countAllResults() > 0) {
                return $this->response->setJSON(['status' => -1, 'msg' => '常用微信已经存在，请仔细检查是否重复！']);
            }
            if ($this->db->table('applicant')->where(['mobile' => $data['mobile'], 'isDelete' => 0])->countAllResults() > 0){
                return $this->response->setJSON(['status' => -1, 'msg' => '常用手机已经存在，请仔细检查是否重复！']);
            }
        }
        return $data;
    }


    // 入职转化为正式员工
    public function regUser()
    {
        if (!$this->myservice->verifyRight('APPLICANT')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/Applicant/regUser 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = (int)$this->request->getGetPost('id');
        $animal = $this->request->getGetPost('animal');
        $astro = $this->request->getGetPost('astro');
        $birthdaySolar = $this->request->getGetPost('birthdaySolar');
        $birthdayLunar = $this->request->getGetPost('birthdayLunar');
        $data = $this->db->table('applicant')->where(['id' => $id, 'isDelete' => 0])->get()->getRowArray();
        if ($data) {
            if ($this->db->table('user')->where('userName', $data['mobile'])->orWhere('mobile', $data['mobile'])->countAllResults() > 0) {
                return $this->response->setJSON(['status' => -1, 'msg' => '该手机号已有正式员工使用！请核实手机号！']);
            }
            $data['userName'] = $data['mobile'];
            $data['userPwd'] = md5(substr($data['sfzNo'], -6));
            $data['animal'] = $animal;
            $data['astro'] = $astro;
            $data['birthdaySolar'] = $birthdaySolar;
            $data['birthdayLunar'] = $birthdayLunar;
            $info = array_intersect_key($data, array_flip(['userName', 'userPwd', 'name', 'sex', 'joinDate', 'department', 'position', 'education', 'school', 'major', 'sfzNo', 'mobile', 'wechat', 'animal', 'astro', 'birthdaySolar', 'birthdayLunar', 'homeAddress', 'nation']));
            $this->db->transStart();
            $this->db->table('user')->insert($info);
            $this->myservice->log('生成了正式员工，姓名：'.$data['name'].'，部门：'.$data['department'].'，职位：'.$data['position']);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '员工信息生成成功（员工账号名：员工手机号，初始密码：员工身份证后6位）。', 'data' => $data]);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
}
