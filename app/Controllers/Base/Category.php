<?php

namespace App\Controllers\Base;
use App\Controllers\BaseController;

class Category extends BaseController
{
    //列表
    public function list()
    {
        $page = max(intval($this->request->getGetPost('page')), 1);
        $rows = max(intval($this->request->getGetPost('rows')), 10000);
        $onlyposition = intval($this->request->getGetPost('onlyposition'));
        $typeNumber = $this->request->getGetPost('typeNumber');
        $parentId = intval($this->request->getGetPost('parentId'));
        $sidx = $this->request->getGetPost('sidx');
        $sord = strtoupper((string)$this->request->getGetPost('sord')) === 'ASC' ? 'ASC' : 'DESC';
        $builder = $this->db->table('category')->where('isDelete', 0);
        if (strlen($typeNumber) > 0) {
            if (strpos($typeNumber, ',') !== false) {
                $builder->whereIn('typeNumber', explode(',', $typeNumber));
            } else {
                $builder->where('typeNumber', $typeNumber);
                if ($onlyposition == 1) {
                    $builder->groupStart()
                        ->where('onlyposition', '')
                        ->orWhere('FIND_IN_SET(' . $this->db->escape($this->myerp['user']['position']) . ', onlyposition) >', 0, false)
                    ->groupEnd();
                }
            }
        }
        if ($parentId > 0 ) {
            $builder->where('parentId', $parentId);
        }
        $allowedSort = ['id','name','typeNumber','level','ordnum','onlyposition','parentId','path'];
        if ($sidx && in_array($sidx, $allowedSort, true)) {
            $builder->orderBy($sidx, $sord);
        }
        $list = (clone $builder)->orderBy('typeNumber', 'ASC')->orderBy('path', 'ASC')->orderBy('id', 'ASC')->limit($rows)->offset($rows * ($page - 1))->get()->getResultArray();
        $total = $builder->countAllResults();
        $v = [];
        foreach ($list as $arr => $row) {
            $v[$arr]['id'] = intval($row['id']);
            $v[$arr]['typeNumber'] = $row['typeNumber'];
            $v[$arr]['name'] = $row['name'];
            $v[$arr]['level'] = intval($row['level']);
            $v[$arr]['ordnum'] = intval($row['ordnum']);
            $v[$arr]['onlyposition'] = $row['onlyposition'];
            $v[$arr]['parentId'] = intval($row['parentId']);
            $v[$arr]['parentName'] = $this->db->table('category')->select('name')->where('id', $row['parentId'])->get()->getRowArray()['name'] ?? '';
            $v[$arr]['path'] = $row['path'];
        }
        $json['status'] = 200;
        $json['msg'] = 'success';
        $json['data']['page'] = $page;
        $json['data']['records'] = $total;
        $json['data']['total'] = ceil($json['data']['records'] / $rows);
        $json['data']['rows'] = $v;
        return $this->response->setJSON($json);
    }
    //查询
    public function query()
    {
        $id = intval($this->request->getGetPost('id'));
        $data = $this->db->table('category')->where(['id' => $id, 'isDelete' => 0])->get()->getRowArray();
        if ($data) {
            $json['status'] = 200;
            $json['msg'] = 'success';
            $json['data']['id'] = intval($data['id']);
            $json['data']['typeNumber'] = $data['typeNumber'];
            $json['data']['name'] = $data['name'];
            $json['data']['level'] = intval($data['level']);
            $json['data']['ordnum'] = intval($data['ordnum']);
            $json['data']['parentId'] = intval($data['parentId']);
            $json['data']['parentName'] = $this->db->table('category')->select('name')->where('id', $data['parentId'])->get()->getRowArray()['name'] ?? '';
            $json['data']['path'] = $data['path'];
            $json['data']['onlyposition'] = $data['onlyposition'];
            return $this->response->setJSON($json);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
    //新增
    public function add()
    {
        $data = $this->request->getPost();
        if (is_array($data) && count($data) > 0) {
            $data = $this->validform($data);
            // 如果返回的是 Response 对象，直接返回它
            if ($data instanceof \CodeIgniter\HTTP\Response) {
                return $data;
            }
            $success = '';
            switch ($data['typeNumber']) {
                case 'customertype':
                    $success = '新增了客户类别：';
                    break;
                case 'suppliertype':
                    $success = '新增了商家类别：';
                    break;
                case 'goodstype':
                    $success = '新增了产品类别：';
                    break;
                case 'brandtype':
                    $success = '新增了产品品牌：';
                    break;
                case 'unittype':
                    $success = '新增了计量单位：';
                    break;
                case 'incometype':
                    $success = '新增了收入类别：';
                    break;
                case 'expendtype':
                    $success = '新增了支出类别：';
                    break;
                case 'settlementtype':
                    $success = '新增了结算方式：';
                    break;
                case 'servicetype':
                    $success = '新增了反馈类别：';
                    break;
                case 'departmenttype':
                    $success = '新增了职能部门：';
                    break;
                case 'positiontype':
                    $success = '新增了部门职位：';
                    break;
                case 'videotype':
                    $success = '新增了视频类别：';
                    break;
                default:
                    return $this->response->setJSON(['status' => -1, 'msg' => '没有此类别！']);
            }
            $this->db->transStart();
            if (in_array($data['typeNumber'], ['goodstype', 'positiontype'])) { // 多级类别
                if ($data['parentId'] == 0) { // 是顶级
                    $data['level'] = 1;
                    $this->db->table('category')->insert(array_intersect_key($data, array_flip(['name', 'typeNumber', 'level', 'parentId', 'onlyposition'])));
                    $newid = $this->db->insertID();
                    $this->db->table('category')->where('id', $newid)->update(['path' => $newid]);
                    $data['id'] = $newid;
                    $data['path'] = $newid;
                } else { // 是子级
                    $parentData = $this->db->table('category')->where(['id' => $data['parentId']])->get()->getRowArray();
                    if (!$parentData) {
                        $this->db->transRollback();   // 手动回滚
                        return $this->response->setJSON(['status' => -1, 'msg' => '未查询到上级分类！事务已回滚！']);
                    }
                    $data['level'] = (int)$parentData['level'] + 1;
                    $this->db->table('category')->insert(array_intersect_key($data, array_flip(['name', 'typeNumber', 'level', 'parentId', 'onlyposition'])));
                    $newid = $this->db->insertID();
                    $data['id'] = $newid;
                    $data['path'] = $parentData['path'] . ',' . $newid;
                    $this->db->table('category')->where('id', $newid)->update(['path' => $parentData['path'] . ',' . $newid]);
                }
            } else { // 单级分类
                $this->db->table('category')->insert(array_intersect_key($data, array_flip(['name', 'typeNumber'])));
                $data['id'] = $this->db->insertID();
            }
            $this->myservice->log($success . $data['name']);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '新增成功！', 'data' => $data]);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '提交的是空数据！']);
    }
    //编辑
    public function update()
    {
        $data = $this->request->getPost();
        if (is_array($data) && count($data) > 0) {
            $data = $this->validform($data);
            // 如果返回的是 Response 对象，直接返回它
            if ($data instanceof \CodeIgniter\HTTP\Response) {
                return $data;
            }
            if ($data['parentId'] == $data['id']) {
                return $this->response->setJSON(['status' => -1, 'msg' => '当前分类和上级分类不能相同！']);
            }
            $oldData = $this->db->table('category')->where('id', $data['id'])->get()->getRowArray();
            if (!$oldData) {
                return $this->response->setJSON(['status' => -1, 'msg' => '原数据获取失败！']);
            }
            $success = '';
            switch ($data['typeNumber']) {
                case 'customertype':
                    $success = '编辑了客户类别：';
                    break;
                case 'suppliertype':
                    $success = '编辑了商家类别：';
                    break;
                case 'goodstype':
                    $success = '编辑了产品类别：';
                    break;
                case 'brandtype':
                    $success = '编辑了产品品牌：';
                    break;
                case 'unittype':
                    $success = '编辑了计量单位：';
                    break;
                case 'incometype':
                    $success = '编辑了收入类别：';
                    break;
                case 'expendtype':
                    $success = '编辑了支出类别：';
                    break;
                case 'settlementtype':
                    $success = '编辑了结算方式：';
                    break;
                case 'servicetype':
                    $success = '编辑了反馈类别：';
                    break;
                case 'departmenttype':
                    $success = '编辑了职能部门：';
                    break;
                case 'positiontype':
                    $success = '编辑了部门职位：';
                    break;
                case 'videotype':
                    $success = '编辑了视频类别：';
                    break;
                default:
                    return $this->response->setJSON(['status' => -1, 'msg' => '没有此类别！']);
            }
            $this->db->transStart();
            if (in_array($data['typeNumber'], ['goodstype', 'positiontype'])) { // 多级类别
                // 获取原子级分类数据
                $sonList = $this->db->table('category')->where('id !=', $data['id'])->where('FIND_IN_SET(' . $this->db->escape($data['id']) . ', path) >', 0, false)->get()->getResultArray();
                if ($data['parentId'] == 0) { // 转顶级
                    // 更新自身
                    $this->db->table('category')->where('id', $data['id'])->update(['name' => $data['name'], 'level' => 1, 'parentId' => 0, 'path' => $data['id'], 'onlyposition' => $data['onlyposition']]);
                    // 顶级转顶级无需额外处理，只处理子级转顶级的情况
                    if ($oldData['parentId'] > 0 && count($sonList) > 0) { // 更新子级
                        $info = [];
                        foreach ($sonList as $k => $v) {
                            $parts = explode(',', $v['path']);
                            $pos = array_search((string)$data['id'], $parts, true);  // true = 严格比较
                            if ($pos === false) {
                                $this->db->transRollback(); // 手动回滚
                                return $this->response->setJSON(['status' => -1, 'msg' => '未匹配到目录树，事务已回滚！']);
                            } else {
                                $path = implode(',', array_slice($parts, $pos));
                            }
                            $level = substr_count($path, ',') + 1;
                            $info[] = [
                                'id' => $v['id'],
                                'path' => $path,
                                'level' => $level
                            ];
                        }
                        $this->db->table('category')->updateBatch($info, 'id');
                    }
                } else { // 转子级
                    $parentData = $this->db->table('category')->where('id', $data['parentId'])->get()->getRowArray(); // 获取父级分类数据
                    // 更新自身
                    $this->db->table('category')->where('id', $data['id'])->update(['name' => $data['name'], 'level' => (int)$parentData['level'] + 1, 'parentId' => $data['parentId'], 'path' => $parentData['path'] . ',' . $data['id'], 'onlyposition' => $data['onlyposition']]);
                    if (count($sonList) > 0) { // 更新子级
                        $info = [];
                        foreach ($sonList as $k => $v) {
                            $parts = explode(',', $v['path']);
                            $pos = array_search((string)$data['id'], $parts, true);  // true = 严格比较
                            if ($pos === false) {
                                $this->db->transRollback(); // 手动回滚
                                return $this->response->setJSON(['status' => -1, 'msg' => '未匹配到目录树，事务已回滚！']);
                            } else {
                                $path = $parentData['path'] . ',' . implode(',', array_slice($parts, $pos));
                            }
                            $level = substr_count($path, ',') + 1;
                            $info[] = [
                                'id' => $v['id'],
                                'path' => $path,
                                'level' => $level
                            ];
                        }
                        $this->db->table('category')->updateBatch($info, 'id');
                    }
                }
            } else { // 单级类别
                $this->db->table('category')->where('id', $data['id'])->update(['name' => $data['name']]);
            }
            // 更新其他引用的地方
            if ($data['name'] != $oldData['name']) {
                switch ($data['typeNumber']) {
                    case 'customertype':
                        $this->myservice->log($success . '将 ' . $oldData['name'] . ' 改为了 ' . $data['name']);
                        break;
                    case 'suppliertype':
                        $this->myservice->log($success . '将 ' . $oldData['name'] . ' 改为了 ' . $data['name']);
                        break;
                    case 'goodstype':
                        $this->db->table('goods')->where('categoryId', $data['id'])->update(['categoryName' => $data['name']]);
                        $oldParentName = $this->db->table('category')->select('name')->where('id', $oldData['parentId'])->get()->getRowArray()['name'] ?? '全部';
                        $newParentName = $this->db->table('category')->select('name')->where('id', $data['parentId'])->get()->getRowArray()['name'] ?? '全部';
                        $this->myservice->log('编辑了产品类别，ID：' . $data['id'] . '，旧名称：' . $oldData['name'] . '，新名称：'. $data['name'] . '，旧上级分类：' . $oldParentName . '，新上级分类：'. $newParentName);
                        break;
                    case 'brandtype':
                        $this->db->table('goods')->where('brandId', $data['id'])->update(['brandName' => $data['name']]);
                        $this->myservice->log($success . '将 ' . $oldData['name'] . ' 改为了 ' . $data['name']);
                        break;
                    case 'unittype':
                        $this->db->table('goods')->where('unitId', $data['id'])->update(['unitName' => $data['name']]);
                        $this->myservice->log($success . '将 ' . $oldData['name'] . ' 改为了 ' . $data['name']);
                        break;
                    case 'incometype':
                        $this->myservice->log($success . '将 ' . $oldData['name'] . ' 改为了 ' . $data['name']);
                        break;
                    case 'expendtype':
                        $this->myservice->log($success . '将 ' . $oldData['name'] . ' 改为了 ' . $data['name']);
                        break;
                    case 'settlementtype':
                        $this->myservice->log($success . '将 ' . $oldData['name'] . ' 改为了 ' . $data['name']);
                        break;
                    case 'servicetype':
                        $this->myservice->log($success . '将 ' . $oldData['name'] . ' 改为了 ' . $data['name']);
                        break;
                    case 'departmenttype':
                        $this->db->table('user')->where('department', $oldData['name'])->update(['department' => $data['name']]);
                        $this->db->table('user_medal')->where('department', $oldData['name'])->update(['department' => $data['name']]);
                        $this->db->table('user_rank')->where('department', $oldData['name'])->update(['department' => $data['name']]);
                        $this->db->table('applicant')->where('department', $oldData['name'])->update(['department' => $data['name']]);
                        $this->db->table('bill_achievement_info')->where('department', $oldData['name'])->update(['department' => $data['name']]);
                        $this->db->table('bill_attendance_info')->where('department', $oldData['name'])->update(['department' => $data['name']]);
                        $this->db->table('bill_evaluate_info')->where('department', $oldData['name'])->update(['department' => $data['name']]);
                        $this->myservice->log($success . '将 ' . $oldData['name'] . ' 改为了 ' . $data['name']);
                        break;
                    case 'positiontype':
                        $this->db->table('user')->where('position', $oldData['name'])->update(['position' => $data['name']]);
                        $this->db->table('user_medal')->where('position', $oldData['name'])->update(['position' => $data['name']]);
                        $this->db->table('user_rank')->where('position', $oldData['name'])->update(['position' => $data['name']]);
                        $this->db->table('applicant')->where('position', $oldData['name'])->update(['position' => $data['name']]);
                        $this->db->table('bill_achievement_info')->where('position', $oldData['name'])->update(['position' => $data['name']]);
                        $this->db->table('bill_attendance_info')->where('position', $oldData['name'])->update(['position' => $data['name']]);
                        $this->db->table('bill_evaluate_info')->where('position', $oldData['name'])->update(['position' => $data['name']]);
                        $oldParentName = $this->db->table('category')->select('name')->where('id', $oldData['parentId'])->get()->getRowArray()['name'] ?? '无';
                        $newParentName = $this->db->table('category')->select('name')->where('id', $data['parentId'])->get()->getRowArray()['name'] ?? '无';
                        $this->myservice->log('编辑了部门职位，ID：' . $data['id'] . '，旧名称：' . $oldData['name'] . '，新名称：'. $data['name'] . '，旧上级分类：' . $oldParentName . '，新上级分类：'. $newParentName);
                        break;
                    case 'videotype':
                        $this->myservice->log($success . '将 ' . $oldData['name'] . ' 改为了 ' . $data['name']);
                        break;
                    default:
                        break;
                }
            } else {
                $this->myservice->log($success . $data['name']);
            }
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON(['status' => -1, 'msg' => '事务已回滚！']);
            }
            return $this->response->setJSON(['status' => 200, 'msg' => '编辑成功！', 'data' => $data]);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '提交的是空数据！']);
    }
    //删除
    public function delete()
    {
        $id = intval($this->request->getGetPost('id'));
        $typeNumber = $this->request->getGetPost('typeNumber');
        $data = $this->db->table('category')->where(['id' => $id, 'isDelete' => 0])->get()->getRowArray();
        if ($data) {
            $success = '';
            switch ($typeNumber) {
                case 'customertype':
                    if ($this->db->table('contact')->where(['categoryId' => $id, 'isDelete' => 0])->countAllResults() > 0) {
                        return $this->response->setJSON(['status' => -1, 'msg' => '客户类别已被使用，无法删除！']);
                    }
                    $success = '删除了客户类别';
                    break;
                case 'suppliertype':
                    if ($this->db->table('contact')->where(['categoryId' => $id, 'isDelete' => 0])->countAllResults() > 0) {
                        return $this->response->setJSON(['status' => -1, 'msg' => '商家类别已被使用，无法删除！']);
                    }
                    $success = '删除了商家类别';
                    break;
                case 'goodstype':
                    if ($this->db->table('goods')->where(['categoryId' => $id, 'isDelete' => 0])->countAllResults() > 0) {
                        return $this->response->setJSON(['status' => -1, 'msg' => '产品类别已被使用，无法删除！']);
                    }
                    $success = '删除了产品类别';
                    break;
                case 'brandtype':
                    if ($this->db->table('goods')->where(['brandId' => $id, 'isDelete' => 0])->countAllResults() > 0) {
                        return $this->response->setJSON(['status' => -1, 'msg' => '产品品牌已被使用，无法删除！']);
                    }
                    $success = '删除了产品品牌';
                    break;
                case 'unittype':
                    if ($this->db->table('goods')->where(['unitId' => $id, 'isDelete' => 0])->countAllResults() > 0) {
                        return $this->response->setJSON(['status' => -1, 'msg' => '计量单位已被使用，无法删除！']);
                    }
                    $success = '删除了计量单位';
                    break;
                case 'incometype':
                    if ($this->db->table('bill_money_info')->where(['typeId' => $id, 'isDelete' => 0])->countAllResults() > 0) {
                        return $this->response->setJSON(['status' => -1, 'msg' => '收入类别已被使用，无法删除！']);
                    }
                    $success = '删除了收入类别';
                    break;
                case 'expendtype':
                    if ($this->db->table('bill_money_info')->where(['typeId' => $id, 'isDelete' => 0])->countAllResults() > 0) {
                        return $this->response->setJSON(['status' => -1, 'msg' => '支出类别已被使用，无法删除！']);
                    }
                    $success = '删除了支出类别';
                    break;
                case 'settlementtype':
                    if ($this->db->table('bill_money_info')->where(['wayId' => $id, 'isDelete' => 0])->countAllResults() > 0) {
                        return $this->response->setJSON(['status' => -1, 'msg' => '结算方式已被使用，无法删除！']);
                    }
                    $success = '删除了结算方式';
                    break;
                case 'servicetype':
                    if ($this->db->table('service')->where(['categoryId' => $id, 'isDelete' => 0])->countAllResults() > 0) {
                        return $this->response->setJSON(['status' => -1, 'msg' => '反馈类别已被使用，无法删除！']);
                    }
                    $success = '删除了反馈类别';
                    break;
                case 'departmenttype':
                    if ($this->db->table('applicant')->where(['department' => $data['name'], 'isDelete' => 0])->countAllResults() > 0) {
                        return $this->response->setJSON(['status' => -1, 'msg' => '职能部门已被使用，无法删除！']);
                    }
                    if ($this->db->table('user')->where(['department' => $data['name'], 'isDelete' => 0])->countAllResults() > 0) {
                        return $this->response->setJSON(['status' => -1, 'msg' => '职能部门已被使用，无法删除！']);
                    }
                    $success = '删除了职能部门';
                    break;
                case 'positiontype':
                    if ($this->db->table('applicant')->where(['position' => $data['name'], 'isDelete' => 0])->countAllResults() > 0) {
                        return $this->response->setJSON(['status' => -1, 'msg' => '部门职位已被使用，无法删除！']);
                    }
                    if ($this->db->table('user')->where(['position' => $data['name'], 'isDelete' => 0])->countAllResults() > 0) {
                        return $this->response->setJSON(['status' => -1, 'msg' => '部门职位已被使用，无法删除！']);
                    }
                    $success = '删除了部门职位';
                    break;
                case 'videotype':
                    if ($this->db->table('news')->where(['videoType' => $id, 'isDelete' => 0])->countAllResults() > 0) {
                        return $this->response->setJSON(['status' => -1, 'msg' => '视频类别已被使用，无法删除！']);
                    }
                    $success = '删除了视频类别';
                    break;
                default:
                    return $this->response->setJSON(['status' => -1, 'msg' => '参数错误！']);
            }
            if ($this->db->table('category')->where('isDelete', 0)->where('FIND_IN_SET(' . $id . ', path) >', 0, false)->countAllResults() > 1) {
                return $this->response->setJSON(['status' => -1, 'msg' => '无法删除，请先删除子分类！']);
            }
            $this->db->transStart();
            $this->db->table('category')->where('id', $id)->update(['isDelete' => 1]);
            $this->myservice->log($success . '，ID：' . $id . '，名称：' . $data['name']);
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
        $data['id'] = isset($data['id']) ? intval($data['id']) : 0;
        if (strlen($data['name']) < 1) {
            return $this->response->setJSON(['status' => -1, 'msg' => '名称不能为空！']);
        }
        if (strlen($data['typeNumber']) < 1) {
            return $this->response->setJSON(['status' => -1, 'msg' => '类型不能为空！']);
        }
        if ($this->db->table('category')->where(['name' => $data['name'], 'typeNumber' => $data['typeNumber'], 'id !=' => $data['id'], 'isDelete' => 0])->countAllResults() > 0) {
            return $this->response->setJSON(['status' => -1, 'msg' => '名称重复！']);
        }
        $data['parentId'] = isset($data['parentId']) ? intval($data['parentId']) : 0;
        $data['onlyposition'] = $data['onlyposition'] ?? '';
        return $data;
    }
}
