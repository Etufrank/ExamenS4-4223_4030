<?php

namespace App\Models;

use CodeIgniter\Model;

class GainModel extends Model
{
    protected $table            = 'gains';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $allowedFields    = ['type_operation_id', 'montant_total_frais', 'periode_debut', 'periode_fin'];
    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = null;

    public function getGainsWithType()
    {
        return $this->select('gains.*, types_operations.nom as type_nom, types_operations.code')
                    ->join('types_operations', 'types_operations.id = gains.type_operation_id')
                    ->orderBy('periode_debut', 'DESC')
                    ->findAll();
    }
}