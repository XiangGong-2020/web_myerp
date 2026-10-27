<?php

namespace App\Controllers\Base;
use App\Controllers\BaseController;

class DeliverBagRec extends BaseController
{
    //列表
    public function list()
    {
        if (!$this->myservice->verifyRight('DELIVERBAG_QUERY')) {
            log_message('error', $this->myerp['uName'] . ' 访问 /Base/DeliverBagRec/list 权限不足！');
            return $this->response->setJSON(['status' => -1, 'msg' => '权限不足！']);
        }
        $page = max(intval($this->request->getGetPost('page')), 1);
        $rows = max(intval($this->request->getGetPost('rows')), 100);
        $sidx = $this->request->getGetPost('sidx');
        $sord = strtoupper((string)$this->request->getGetPost('sord')) === 'ASC' ? 'ASC' : 'DESC';
        $skey = $this->request->getGetPost('skey');
        $company1 = $this->request->getGetPost('company1');
        $beginDate = $this->request->getGetPost('beginDate');
        $endDate = $this->request->getGetPost('endDate');
        $builder = $this->db->table('deliver_rec');
        $builder->where('isDelete', 0);
        if ($company1) {
            $builder->where('company1', $company1);
        }
        if ($beginDate) {
            $builder->where('createTime >=', $beginDate);
        }
        if ($endDate) {
            $builder->where('createTime <=', $endDate);
        }
        if ($skey !== '' && $skey !== null) {
            $keywords = array_values(array_filter(explode(',', $skey), 'strlen'));
            foreach ($keywords as $kw) {
                $builder->groupStart()
                    ->like('no', $this->db->escapeLikeString($kw))
                    ->orLike('company1', $this->db->escapeLikeString($kw))
                    ->orLike('company2', $this->db->escapeLikeString($kw))
                    ->orLike('destroy', $this->db->escapeLikeString($kw))
                    ->orLike('description', $this->db->escapeLikeString($kw))
                    ->orLike('uName', $this->db->escapeLikeString($kw))
                ->groupEnd();
            }
        }
        $allowedSort = ['id','no','company1','company2','boxNum','boxWeight','destroy','description','uId','uName','createTime'];
        $orderField  = $sidx ? (in_array($sidx, $allowedSort, true) ? $sidx : 'id') : 'id';
        $list = (clone $builder)->orderBy($orderField, $sord)->limit($rows)->offset($rows * ($page - 1))->get()->getResultArray();
        $total = $builder->countAllResults();
        $v = [];
        foreach ($list as $arr => $row) {
            $v[$arr]['id'] = intval($row['id']);
            $v[$arr]['no'] = $row['no'];
            $v[$arr]['company1'] = $row['company1'];
            $v[$arr]['company2'] = $row['company2'];
            $v[$arr]['boxNum'] = (float)$row['boxNum'];
            $v[$arr]['boxWeight'] = (float)$row['boxWeight'];
            $v[$arr]['destroy'] = $row['destroy'];
            $v[$arr]['description'] = $row['description'];
            $v[$arr]['uId'] = intval($row['uId']);
            $v[$arr]['uName'] = $row['uName'];
            $v[$arr]['createTime'] = $row['createTime'] == '0000-00-00 00:00:00' ? '' : $row['createTime'];
        }
        $json['status'] = 200;
        $json['msg'] = 'success';
        $json['data']['page'] = $page;
        $json['data']['records'] = $total;
        $json['data']['total'] = ceil($json['data']['records'] / $rows);
        $json['data']['rows'] = $v;
        return $this->response->setJSON($json);
    }
}
