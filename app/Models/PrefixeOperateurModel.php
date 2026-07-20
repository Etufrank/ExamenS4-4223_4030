<?php

namespace App\Models;

use CodeIgniter\Model;

class PrefixeOperateurModel extends Model
{
    protected $table            = 'prefixes_operateur';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $allowedFields    = ['prefixe', 'description'];
    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = '';
}