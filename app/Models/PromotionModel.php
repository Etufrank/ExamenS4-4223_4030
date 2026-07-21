<?php
namespace App\Models;
use CodeIgniter\Model;
class PromotionModel extends Model
{
    protected $table = 'promotions';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = ['type_operation_id', 'operateur_prefixe', 'reduction_pourcentage', 'date_debut', 'date_fin'];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    public function getActivePromotion($typeId, $prefixe = '032')
    {
        $now = date('Y-m-d H:i:s');
        return $this->where('type_operation_id', $typeId)
                    ->where('operateur_prefixe', $prefixe)
                    ->where('date_debut <=', $now)
                    ->where('date_fin >=', $now)
                    ->orderBy('reduction_pourcentage', 'DESC')
                    ->first();
    }
    public function getPromotionsWithType()
    {
        return $this->select('promotions.*, types_operations.nom as type_nom')
                    ->join('types_operations', 'types_operations.id = promotions.type_operation_id')
                    ->orderBy('date_debut', 'DESC')
                    ->findAll();
    }
}