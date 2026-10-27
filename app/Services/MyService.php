<?php

namespace App\Services;

use CodeIgniter\Database\ConnectionInterface;

class MyService
{
    protected ConnectionInterface $db;
    protected mixed $myerp = [];
    protected bool $initialized = false;

    // 只接收一个参数
    public function __construct(?ConnectionInterface $db = null, array $myerp = [])
    {
        $this->db = $db ?? \Config\Database::connect();
        $this->myerp = $myerp;
    }

    public function verifyRight(string $right = ''): bool
    {
        if ($right === '' || empty($this->myerp['uId'])) {
            return false;
        }
        $user = $this->db->table('user')
            ->where(['id' => $this->myerp['uId'], 'isDisable' => 0])
            ->get()
            ->getRowArray();

        if (! $user) {
            return false;
        }

        if ((int) $user['roleId'] === 1) {
            return true;
        }

        if (empty($user['lever'])) {
            return false;
        }

        $rights = $this->myerp['rights'] ?? [];
        if (! is_array($rights)) {
            return false;
        }

        if (strpos($right, '&&') !== false) {
            foreach (explode('&&', $right) as $r) {
                if (! in_array($r, $rights)) {
                    return false;
                }
            }
            return true;
        }

        if (strpos($right, '||') !== false) {
            foreach (explode('||', $right) as $r) {
                if (in_array($r, $rights)) {
                    return true;
                }
            }
            return false;
        }

        return in_array($right, $rights);
    }

    public function log(string $info, string $name = ''): void
    {
        $data = [
            'log'        => $info,
            'name'       => !$name ? ($this->myerp['uName'] ?? '系统') : $name,
            'createTime' => date('Y-m-d H:i:s'),
        ];
        $result = $this->db->table('log')->insert($data);
        if (!$result) {
            $error = $this->db->error();
            log_message('error', '日志写入失败: ' . json_encode($error, JSON_UNESCAPED_UNICODE).'，日志内容：' . json_encode($data, JSON_UNESCAPED_UNICODE));
        }
    }
}