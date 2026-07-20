<?php

namespace App\Models;

use CodeIgniter\Model;

class TransactionModel extends Model
{
    protected $table            = 'transactions';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $allowedFields    = ['reference', 'type_operation_id', 'client_id', 'montant', 'frais_appliques', 'montant_total', 'sens', 'statut', 'description'];
    protected $useTimestamps    = true;
    protected $createdField     = 'date_transaction';
    protected $updatedField     = null;

    public function generateReference()
    {
        return 'TXN-' . date('Ymd') . '-' . strtoupper(uniqid());
    }

    public function getTransactionsByClient($clientId, $limit = null)
    {
        $builder = $this->select('transactions.*, types_operations.nom as type_nom, types_operations.code')
                        ->join('types_operations', 'types_operations.id = transactions.type_operation_id')
                        ->where('client_id', $clientId)
                        ->orderBy('date_transaction', 'DESC');
        if ($limit) {
            $builder->limit($limit);
        }
        return $builder->findAll();
    }

    public function getGainsByPeriod($typeId, $debut, $fin)
    {
        return $this->select('SUM(frais_appliques) as total_frais')
                    ->where('type_operation_id', $typeId)
                    ->where('date_transaction >=', $debut)
                    ->where('date_transaction <=', $fin)
                    ->where('statut', 'effectuee')
                    ->first();
    }
}