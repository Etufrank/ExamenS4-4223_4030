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
    protected $updatedField     = '';

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
    public function getGainsByType($typeId, $debut, $fin, $estInterOperateur = null)
{
    $builder = $this->select('SUM(frais_appliques) as total_frais')
                    ->where('type_operation_id', $typeId)
                    ->where('date_transaction >=', $debut)
                    ->where('date_transaction <=', $fin)
                    ->where('statut', 'effectuee');
    
    if ($estInterOperateur !== null) {
        $builder->where('est_inter_operateur', $estInterOperateur);
    }
    
    return $builder->first()['total_frais'] ?? 0;
}

public function getMontantsParOperateur($debut, $fin)
{
    return $this->select('prefixes_operateur.prefixe, SUM(transactions.montant) as total_montant, prefixes_operateur.commission_pourcentage')
                ->join('clients', 'clients.id = transactions.client_id')
                ->join('prefixes_operateur', 'substr(clients.numero_telephone, 1, 3) = prefixes_operateur.prefixe', 'left')
                ->where('transactions.date_transaction >=', $debut)
                ->where('transactions.date_transaction <=', $fin)
                ->where('transactions.statut', 'effectuee')
                ->where('transactions.sens', 'debit')
                ->where('transactions.est_inter_operateur', 1)
                ->groupBy('prefixes_operateur.prefixe')
                ->findAll();
}

}

