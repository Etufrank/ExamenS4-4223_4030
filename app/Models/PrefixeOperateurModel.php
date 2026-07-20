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
    protected $allowedFields    = ['prefixe', 'description', 'est_autre_operateur', 'commission_pourcentage'];
    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';

    public function getAutresOperateurs()
    {
        return $this->where('est_autre_operateur', 1)->findAll();
    }

    public function getOperateurs()
    {
        return $this->where('est_autre_operateur', 0)->findAll();
    }

    public function getCommissionByPrefixe($prefixe)
    {
        $result = $this->where('prefixe', $prefixe)->first();
        return $result ? $result['commission_pourcentage'] : 0;
    }
}