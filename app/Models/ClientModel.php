<?php

namespace App\Models;

use CodeIgniter\Model;

class ClientModel extends Model
{
    protected $table            = 'clients';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $allowedFields    = ['user_id', 'numero_telephone', 'nom', 'prenom', 'solde', 'statut'];
    protected $useTimestamps    = true;
    protected $createdField     = 'date_creation';
    protected $updatedField     = null;

    public function findByNumero($numero)
    {
        return $this->where('numero_telephone', $numero)->first();
    }

    public function getClientWithUser($clientId)
    {
        return $this->select('clients.*, users.username, users.email, users.role')
                    ->join('users', 'users.id = clients.user_id')
                    ->where('clients.id', $clientId)
                    ->first();
    }
}