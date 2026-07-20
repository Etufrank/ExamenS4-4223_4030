<?php

namespace App\Models;

use CodeIgniter\Model;

class BaremeFraisModel extends Model
{
    protected $table            = 'baremes_frais';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $allowedFields    = ['type_operation_id', 'montant_min', 'montant_max', 'frais_fixe', 'frais_pourcentage'];
    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';

    public function getBaremeByTypeAndMontant($typeId, $montant)
    {
        $montant = (float) $montant;
        return $this->where('type_operation_id', $typeId)
                    ->where('montant_min <=', $montant)
                    ->where('montant_max >=', $montant)
                    ->first();
    }

    public function getBaremesWithType()
    {
        return $this->select('baremes_frais.*, types_operations.nom as type_nom, types_operations.code')
                    ->join('types_operations', 'types_operations.id = baremes_frais.type_operation_id')
                    ->orderBy('type_operation_id')
                    ->orderBy('montant_min')
                    ->findAll();
    }

    public function getBaremeWithType($id)
    {
        return $this->select('baremes_frais.*, types_operations.nom as type_nom, types_operations.code')
                    ->join('types_operations', 'types_operations.id = baremes_frais.type_operation_id')
                    ->where('baremes_frais.id', $id)
                    ->first();
    }
}