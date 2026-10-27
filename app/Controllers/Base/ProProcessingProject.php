<?php

namespace App\Controllers\Base;
use App\Controllers\BaseController;

class ProProcessingProject extends BaseController
{
    // 列表
    public function list()
    {
        if (!$this->myservice->verifyRight('PROPROJECT_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/ProProcessingProject/list 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $page = max(intval($this->request->getGetPost('page')), 1);
        $rows = max(intval($this->request->getGetPost('rows')), 100);
        $sidx = $this->request->getGetPost('sidx');
        $sord = strtoupper((string)$this->request->getGetPost('sord')) === 'ASC' ? 'ASC' : 'DESC';
        $type = intval($this->request->getGetPost('type'));
        $isDisable = intval($this->request->getGetPost('isDisable'));
        $searchType = intval($this->request->getGetPost('searchType'));
        $skey = $this->request->getGetPost('skey');
        $builder = $this->db->table('pro_processing_project');
        $builder->where('isDelete', 0);
        if ($type >= 0) {
            $builder->where('type', $type);
        }
        if ($isDisable >= 0) {
            $builder->where('isDisable', $isDisable);
        }
        if ($skey !== '' && $skey !== null) {
            $keywords = array_values(array_filter(explode(',', $skey), 'strlen'));
            if ($searchType == 0) { // 模糊查询
                foreach ($keywords as $kw) {
                    $builder->groupStart()
                        ->like('REPLACE(name, "-", "")', $this->db->escapeLikeString(str_replace('-', '', $kw)), 'both', null, false)
                        ->orLike('uName', $this->db->escapeLikeString($kw))
                        ->orLike('modName', $this->db->escapeLikeString($kw))
                        ->orLike('description', $this->db->escapeLikeString($kw))
                    ->groupEnd();
                }
            } else { // 精确查询
                foreach ($keywords as $kw) {
                    $builder->groupStart()
                        ->where('REPLACE(name, "-", "")', str_replace('-', '', $kw))
                        ->orWhere('uName', $kw)
                        ->orWhere('modName', $kw)
                    ->groupEnd();
                }
            }
        }
        $allowedSort = ['id','type','name','goodsId','tPointNum','zPointNum','donePerHourNum','needTest','producePrice','uName','createTime','modName','modifyTime','description','isDisable'];
        $orderField  = $sidx ? (in_array($sidx, $allowedSort, true) ? $sidx : 'id') : 'id';
        $list = (clone $builder)->orderBy($orderField, $sord)->limit($rows)->offset($rows * ($page - 1))->get()->getResultArray();
        $total = $builder->countAllResults();
        $v = [];
        foreach ($list as $arr => $row) {
            $v[$arr]['id'] = intval($row['id']);
            $v[$arr]['type'] = intval($row['type']);
            $v[$arr]['name'] = $row['name'];
            $v[$arr]['goodsId'] = intval($row['goodsId']);
            $v[$arr]['tPointNum'] = intval($row['tPointNum']);
            $v[$arr]['zPointNum'] = intval($row['zPointNum']);
            $v[$arr]['donePerHourNum'] = intval($row['donePerHourNum']);
            $v[$arr]['needTest'] = intval($row['needTest']);
            $v[$arr]['producePrice'] = $row['producePrice'];
            $v[$arr]['uName'] = $row['uName'];
            $v[$arr]['createTime'] = $row['createTime'];
            $v[$arr]['modName'] = $row['modName'];
            $v[$arr]['modifyTime'] = $row['modifyTime'];
            $v[$arr]['description'] = $row['description'];
            $v[$arr]['isDisable'] = intval($row['isDisable']);
        }
        $json['status'] = 200;
        $json['msg'] = 'success';
        $json['data']['page'] = $page;
        $json['data']['records'] = $total;
        $json['data']['total'] = ceil($json['data']['records'] / $rows);
        $json['data']['rows'] = $v;
        return $this->response->setJSON($json);
    }
    // 获取信息
    public function query()
    {
        if (!$this->myservice->verifyRight('PROPROJECT_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/ProProcessingProject/query 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $data = $this->db->table('pro_processing_project')->where(['id' => $id, 'isDelete' => 0])->get()->getRowArray();
        if ($data) {
            $info['id'] = $id;
            $info['type'] = intval($data['type']);
            $info['name'] = $data['name'];
            $info['goodsId'] = intval($data['goodsId']);
            $info['tPointNum'] = intval($data['tPointNum']);
            $info['zPointNum'] = intval($data['zPointNum']);
            $info['donePerHourNum'] = intval($data['donePerHourNum']);
            $info['donePerHourTestInfo'] = $data['donePerHourTestInfo'];
            $info['needTest'] = intval($data['needTest']);
            $info['producePrice'] = $data['producePrice'];
            $info['uName'] = $data['uName'];
            $info['createTime'] = $data['createTime'];
            $info['modName'] = $data['modName'];
            $info['modifyTime'] = $data['modifyTime'];
            $info['description'] = $data['description'];
            $info['isDisable'] = intval($data['isDisable']);
            $json['status'] = 200;
            $json['msg'] = 'success';
            $json['data'] = $info;
            return $this->response->setJSON($json);
        }
        return $this->response->setJSON(['status' => -1, 'msg' => '信息不存在！']);
    }
    // 新增
    public function add()
    {
        if (!$this->myservice->verifyRight('PROPROJECT_ADVANCE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/ProProcessingProject/add 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $data = $this->request->getPost();
        if (is_array($data) && count($data) > 0) {
            $data = $this->validform($data);
            // 如果返回的是 Response 对象，直接返回它
            if ($data instanceof \CodeIgniter\HTTP\Response) {
                return $data;
            }
            $info = array_intersect_key($data, array_flip(['type','name','goodsId','tPointNum','zPointNum','donePerHourNum','donePerHourTestInfo','needTest','producePrice','description','uName','createTime','modName','modifyTime']));
            $this->db->transStart();
            $this->db->table('pro_processing_project')->insert($info);
            $data['id'] = $this->db->insertID();
            $data['checkId'] = $data['produceId'] = 0;
            $this->myservice->log('新增了加工项目，ID：' . $data['id'] . '，名称：' . $data['name']);
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
        if (!$this->myservice->verifyRight('PROPROJECT_ADVANCE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/ProProcessingProject/update 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $data = $this->request->getPost();
        if (is_array($data) && count($data) > 0) {
            $data = $this->validform($data);
            // 如果返回的是 Response 对象，直接返回它
            if ($data instanceof \CodeIgniter\HTTP\Response) {
                return $data;
            }
            $oldData = $this->db->table('pro_processing_project')->where(['id' => $data['id']])->get()->getRowArray();
            $info = array_intersect_key($data, array_flip(['type','name','goodsId','tPointNum','zPointNum','donePerHourNum','donePerHourTestInfo','needTest','producePrice','description','modName','modifyTime','isDisable']));
            // 对比编辑内容并详尽的记录编辑历史
            $cArr = [];
            $dArr = [0 => '开发板整体生产类', 1 => '电路组件焊接类', 2 => '包装组装类', 9 => '临时加工类（无库存运算）'];
            $eArr = [0 => '无', 1 => '有'];
            $fArr = [0 => '正常', 1 => '将停产', 2 => '已停产', 9 => '已禁用'];
            $data['type'] != $oldData['type'] && array_push($cArr, '将项目类别由（' . $dArr[$oldData['type']] . '）改为了（' . $dArr[$data['type']] . '）');
            $data['name'] != $oldData['name'] && array_push($cArr, '将项目名称由（' . $oldData['name'] . '）改为了（' . $data['name'] . '）');
            $data['tPointNum'] != $oldData['tPointNum'] && array_push($cArr, '将贴片焊点数由（' . $oldData['tPointNum'] . '）改为了（' . $data['tPointNum'] . '）');
            $data['zPointNum'] != $oldData['zPointNum'] && array_push($cArr, '将直插焊点数由（' . $oldData['zPointNum'] . '）改为了（' . $data['zPointNum'] . '）');
            $data['donePerHourNum'] != $oldData['donePerHourNum'] && array_push($cArr, '将每小时可完成数由（' . $oldData['donePerHourNum'] . '）改为了（' . $data['donePerHourNum'] . '）');
            $data['donePerHourTestInfo'] != $oldData['donePerHourTestInfo'] && array_push($cArr, '将每小时可完成数实测记录（' . $oldData['donePerHourTestInfo'] . '）改为了（' . $data['donePerHourTestInfo'] . '）');
            $data['needTest'] != $oldData['needTest'] && array_push($cArr, '将测试工序由（' . $eArr[$oldData['needTest']] . '）改为了（' . $eArr[$data['needTest']] . '）');
            $data['description'] != $oldData['description'] && array_push($cArr, '将备注信息由（' . $oldData['description'] . '）改为了（' . $data['description'] . '）');
            $data['isDisable'] != $oldData['isDisable'] && array_push($cArr, '将产品状态由（' . $fArr[$oldData['isDisable']] . '）改为了（' . $fArr[$data['isDisable']] . '）');
            $this->db->transStart();
            $this->db->table('pro_processing_project')->where(['id' => $data['id']])->update($info);
            $this->db->table('pro_processing_req')->where(['projectId' => $data['id']])->update(['projectType' => $data['type'], 'projectName' => $data['name'], 'needTest' => $data['needTest']]);
            $this->db->table('pro_processing_task')->where(['projectId' => $data['id']])->update(['projectType' => $data['type'], 'projectName' => $data['name'], 'needTest' => $data['needTest']]);
            $this->db->table('pro_processing_task_get')->where(['projectId' => $data['id']])->update(['projectType' => $data['type'], 'projectName' => $data['name'], 'needTest' => $data['needTest']]);
            $this->db->table('pro_processing_task_up')->where(['projectId' => $data['id']])->update(['projectType' => $data['type'], 'projectName' => $data['name'], 'needTest' => $data['needTest']]);
            // 将编辑历史记录入库
            if (count($cArr) > 0) {
                $this->db->table('pro_processing_project_modify_his')->insert([
                    'proProjectId' => $data['id'],
                    'change' => implode("\r\n", $cArr),
                    'changeTime' => date('Y-m-d H:i:s'),
                    'uId' => $this->myerp['uId'],
                    'uName' => $this->myerp['uName']
                ]);
            }
            $data = $this->db->table('pro_processing_project')->where(['id' => $data['id']])->get()->getRowArray();
            $this->myservice->log('编辑了加工项目，ID：' . $data['id'] . '，名称' . $data['name']);
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
        if (!$this->myservice->verifyRight('PROPROJECT_ADVANCE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/ProProcessingProject/delete 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $id = intval($this->request->getGetPost('id'));
        $data = $this->db->table('pro_processing_project')->where(['id' => $id])->get()->getRowArray();
        if ($data) {
            if ($data['uName'] != $this->myerp['uName'] && !$this->myservice->verifyRight('SYS_ADMIN')) {
                return $this->response->setJSON(['status' => -1, 'msg' => '不能删除别人录入的加工项目！仅本人或管理员可删除！']);
            }
            $this->db->transStart();
            $this->db->table('pro_processing_project')->where(['id' => $id])->update(['isDelete' => 1]);
            $this->myservice->log('删除了加工项目，ID: ' . $id . '，名称：' . $data['name']);
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
        $data['id'] = isset($data['id']) ? intval($data['id']) : -1;
        $data['type'] = isset($data['type']) ? intval($data['type']) : -1;
        if ($data['type'] < 0) {
            return $this->response->setJSON(['status' => -1, 'msg' => '项目类型选择错误！']);
        }
        if (strlen($data['name']) < 1) {
            return $this->response->setJSON(['status' => -1, 'msg' => '项目名称不能为空！']);
        }
        if ($this->db->table('pro_processing_project')->where(['name' => $data['name'], 'isDelete' => 0, 'id !=' => $data['id']])->countAllResults() > 0) {
            return $this->response->setJSON(['status' => -1, 'msg' => '项目名称已经存在！']);
        }
        $data['goodsId'] = isset($data['goodsId']) ? intval($data['goodsId']) : -1;
        $data['tPointNum'] == isset($data['tPointNum']) ? intval($data['tPointNum']) : 0;
        $data['zPointNum'] == isset($data['zPointNum']) ? intval($data['zPointNum']) : 0;
        $data['donePerHourNum'] == isset($data['donePerHourNum']) ? intval($data['donePerHourNum']) : 0;
        $data['needTest'] == isset($data['needTest']) ? intval($data['needTest']) : 0;
        $proProcessingPar = ((array)json_decode($this->db->table('option')->select('optionValue')->where('optionName', 'exp_par')->get()->getRowArray()['optionValue'] ?? '', true))['proProcessing'] ?? [];
        if (empty($proProcessingPar)) {
            return $this->response->setJSON(['status' => -1, 'msg' => '业绩系数获取失败！']);
        }
        if (in_array($data['type'], [0,1,2])) {
            if ($data['goodsId'] <= 0) {
                return $this->response->setJSON(['status' => -1, 'msg' => '请选择准确的产品！']);
            }
            $goods = $this->db->table('goods')->where(['id' => $data['goodsId'], 'isDelete' => 0])->get()->getRowArray();
            if ($goods) {
                if ($goods['hasSon'] == 0 || strlen($goods['sonGoods']) == 0) {
                    return $this->response->setJSON(['status' => -1, 'msg' => '所选目标产品没有录入子产品/元件清单！']);
                } elseif ($goods['isDisable'] == 1) {
                    return $this->response->setJSON(['status' => -1, 'msg' => '所选目标产品将停产！']);
                } elseif ($goods['isDisable'] == 2) {
                    return $this->response->setJSON(['status' => -1, 'msg' => '所选目标产品已停产！']);
                } elseif ($goods['isDisable'] == 9) {
                    return $this->response->setJSON(['status' => -1, 'msg' => '所选目标产品已禁用！']);
                }
            } else {
                return $this->response->setJSON(['status' => -1, 'msg' => '所选目标产品不存在或已删除！']);
            }
            if (in_array($data['type'], [0,1])) {
                if ($data['tPointNum'] <= 0 && $data['zPointNum'] <= 0) {
                    return $this->response->setJSON(['status' => -1, 'msg' => '请录入有效的焊点数！']);
                }
                $data['donePerHourNum'] = 0;
                $data['producePrice'] = $data['tPointNum'] * (float)$proProcessingPar['par1'] + $data['zPointNum'] * (float)$proProcessingPar['par2'];
            } else {
                if ($data['donePerHourNum'] <= 0) {
                    return $this->response->setJSON(['status' => -1, 'msg' => '请录入有效的每小时可完成数！']);
                }
                $data['tPointNum'] = $data['zPointNum'] = 0;
                $data['producePrice'] = (float)$proProcessingPar['par5'] / $data['donePerHourNum'];
            }
        } else {
            if ($data['donePerHourNum'] <= 0) {
                return $this->response->setJSON(['status' => -1, 'msg' => '请录入有效的每小时可完成数！']);
            }
            $data['goodsId'] = $data['tPointNum'] = $data['zPointNum'] = 0;
            $data['producePrice'] = (float)$proProcessingPar['par5'] / $data['donePerHourNum'];
        }
        $data['donePerHourTestInfo'] = $data['donePerHourTestInfo'] ?? '';
        $data['description'] = $data['description'] ?? '';
        $data['uName'] = $this->myerp['uName'];
        $data['createTime'] = date('Y-m-d H:i:s');
        $data['modName'] = $this->myerp['uName'];
        $data['modifyTime'] = date('Y-m-d H:i:s');
        $data['isDisable'] = isset($data['isDisable']) ? intval($data['isDisable']) : 0;
        return $data;
    }

    // 获取加工项目编辑历史
    public function getModifyHis()
    {
        if (!$this->myservice->verifyRight('PROPROJECT_ADVANCE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/ProProcessingProject/getModifyHis 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $page = max(intval($this->request->getGetPost('page')), 1);
        $rows = max(intval($this->request->getGetPost('rows')), 100);
        $sidx = $this->request->getGetPost('sidx');
        $sord = strtoupper((string)$this->request->getGetPost('sord')) === 'ASC' ? 'ASC' : 'DESC';
        $id = intval($this->request->getGetPost('id'));
        $beginDate = $this->request->getGetPost('beginDate');
        $endDate = $this->request->getGetPost('endDate');
        $skey = $this->request->getGetPost('skey');
        $builder = $this->db->table('pro_processing_project_modify_his');
        $builder->where('proProjectId', $id)->where('isDelete', 0);
        if ($beginDate) {
            $builder->where('changeTime >=', $beginDate);
        }
        if ($endDate) {
            $builder->where('changeTime <=', $endDate);
        }
        if ($skey !== '' && $skey !== null) {
            $keywords = array_values(array_filter(explode(',', $skey), 'strlen'));
            foreach ($keywords as $kw) {
                $builder->groupStart()
                    ->like('change', $this->db->escapeLikeString($kw))
                    ->orWhere('uName', $kw)
                ->groupEnd();
            }
        }
        $allowedSort = ['id','proProjectId','change','changeTime','uName'];
        $orderField  = $sidx ? (in_array($sidx, $allowedSort, true) ? $sidx : 'id') : 'id';
        $list = (clone $builder)->orderBy($orderField, $sord)->limit($rows)->offset($rows * ($page - 1))->get()->getResultArray();
        $total = $builder->countAllResults();
        $v = [];
        foreach ($list as $arr => $row) {
            $v[] = [
                'id' => intval($row['id']),
                'proProjectId' => intval($row['proProjectId']),
                'change' => $row['change'],
                'changeTime' => $row['changeTime'],
                'uId' => intval($row['uId']),
                'uName' => $row['uName']
            ];
        }
        $json['status'] = 200;
        $json['msg'] = 'success';
        $json['data']['page'] = $page;
        $json['data']['records'] = $total;
        $json['data']['total'] = ceil($json['data']['records'] / $rows);
        $json['data']['rows'] = $v;
        return $this->response->setJSON($json);
    }

    // 计算全部加工项目的加工提成单价(初始的时候以及编辑业绩系数时点击保存并重算全部加工提成单价的时候会用到此方法)。
    public function upProcessingProjectProducePriceInfo()
    {
        if (!$this->myservice->verifyRight('EXP_PAR_UPDATE')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/ProProcessingProject/upProcessingProjectProducePriceInfo 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $list = $this->db->table('pro_processing_project')->where('isDelete', 0)->get()->getResultArray();
        $proProcessingPar = ((array)json_decode($this->db->table('option')->select('optionValue')->where('optionName', 'exp_par')->get()->getRowArray()['optionValue'] ?? '', true))['proProcessing'] ?? [];
        if (empty($proProcessingPar)) {
            return $this->response->setJSON(['status' => -1, 'msg' => '业绩系数获取失败！']);
        }
        foreach ($list as $k => $v) {
            $producePrice = 0;
            if($v['type'] < 9) {
                $producePrice = intval($v['tPointNum']) * (float)$proProcessingPar['par1'] + intval($v['zPointNum']) * (float)$proProcessingPar['par2'];
            } else {
                $producePrice = (float)$proProcessingPar['par5'] / intval($v['donePerHourNum']);
            }
            $this->db->table('pro_processing_project')->where('id', $v['id'])->update(['producePrice' => $producePrice]);
        }
        $this->myservice->log('编辑了业绩系数，并确定重算了全部加工项目的加工提成单价。');
        return $this->response->setJSON(['status' => 200, 'msg' => '计算完成！']);
    }
}
