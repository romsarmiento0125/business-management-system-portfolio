<?php

namespace App\Models;

use CodeIgniter\Model;

class AuditLogModel extends Model
{
    protected $table         = 'audit_logs';
    protected $primaryKey    = 'id';
    protected $allowedFields = [
        'user_id',
        'method',
        'endpoint',
        'payload',
        'description',
        'response_code',
        'ip_address',
        'created_at',
    ];
    protected $useTimestamps = false;
}
