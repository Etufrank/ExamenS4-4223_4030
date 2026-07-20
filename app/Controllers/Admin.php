<?php

namespace App\Controllers;

use App\Models\PrefixeOperateur;
use App\Models\TypeOperation;
use App\Models\BaremeFrais;
use App\Models\Client;
use App\Models\Transaction;
use App\Models\Gain;

class Admin extends BaseController
{
    protected $prefixeModel;
    protected $typeModel;
    protected $baremeModel;
    protected $clientModel;
    protected $transactionModel;
    protected $gainModel;

    public function __construct()
    {
        $this->prefixeModel = new PrefixeOperateur();
        $this->typeModel = new TypeOperation();
        $this->baremeModel = new BaremeFrais();
        $this->clientModel = new Client();
        $this->transactionModel = new Transaction();
        $this->gainModel = new Gain();
    }

    public function prefixes()
    {
        $data['prefixes'] = $this->prefixeModel->findAll();
        $data['title'] = 'Gestion des préfixes';
        return view('admin/prefixes', $data);
    }

    public function ajouterPrefixe()
    {
        $rules = [
            'prefixe' => 'required|is_unique[prefixes_operateur.prefixe]|min_length[2]|max_length[10]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->with('error', $this->validator->getErrors());
        }

        $this->prefixeModel->save([
            'prefixe' => $this->request->getPost('prefixe'),
            'description' => $this->request->getPost('description'),
        ]);

        return redirect()->to('/admin/prefixes')->with('success', 'Préfixe ajouté avec succès');
    }

    public function supprimerPrefixe($id)
    {
        $this->prefixeModel->delete($id);
        return redirect()->to('/admin/prefixes')->with('success', 'Préfixe supprimé');
    }

    public function typesOperations()
    {
        $data['types'] = $this->typeModel->findAll();
        $data['title'] = 'Gestion des types d\'opérations';
        return view('admin/types_operations', $data);
    }

    public function ajouterType()
    {
        $rules = [
            'nom' => 'required|is_unique[types_operations.nom]',
            'code' => 'required|is_unique[types_operations.code]|min_length[2]|max_length[10]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->with('error', $this->validator->getErrors());
        }

        $this->typeModel->save([
            'nom' => $this->request->getPost('nom'),
            'code' => strtoupper($this->request->getPost('code')),
            'description' => $this->request->getPost('description'),
        ]);

        return redirect()->to('/admin/types-operations')->with('success', 'Type ajouté avec succès');
    }

    public function supprimerType($id)
    {
        $this->typeModel->delete($id);
        return redirect()->to('/admin/types-operations')->with('success', 'Type supprimé');
    }

    public function baremes()
    {
        $data['baremes'] = $this->baremeModel->getBaremesWithType();
        $data['types'] = $this->typeModel->findAll();
        $data['title'] = 'Gestion des barèmes de frais';
        return view('admin/baremes', $data);
    }

    public function ajouterBareme()
    {
        $rules = [
            'type_operation_id' => 'required',
            'montant_min' => 'required|numeric',
            'montant_max' => 'required|numeric',
            'frais_fixe' => 'permit_empty|numeric',
            'frais_pourcentage' => 'permit_empty|numeric',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->with('error', $this->validator->getErrors());
        }

        $this->baremeModel->save([
            'type_operation_id' => $this->request->getPost('type_operation_id'),
            'montant_min' => $this->request->getPost('montant_min'),
            'montant_max' => $this->request->getPost('montant_max'),
            'frais_fixe' => $this->request->getPost('frais_fixe') ?: 0,
            'frais_pourcentage' => $this->request->getPost('frais_pourcentage') ?: 0,
        ]);

        return redirect()->to('/admin/baremes')->with('success', 'Barème ajouté avec succès');
    }

    public function modifierBareme($id)
    {
        $bareme = $this->baremeModel->find($id);
        if (!$bareme) {
            return redirect()->to('/admin/baremes')->with('error', 'Barème introuvable');
        }

        $data['bareme'] = $bareme;
        $data['types'] = $this->typeModel->findAll();
        $data['title'] = 'Modifier un barème';
        return view('admin/bareme_edit', $data);
    }

    public function mettreAJourBareme($id)
    {
        $rules = [
            'montant_min' => 'required|numeric',
            'montant_max' => 'required|numeric',
            'frais_fixe' => 'permit_empty|numeric',
            'frais_pourcentage' => 'permit_empty|numeric',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->with('error', $this->validator->getErrors());
        }

        $this->baremeModel->update($id, [
            'montant_min' => $this->request->getPost('montant_min'),
            'montant_max' => $this->request->getPost('montant_max'),
            'frais_fixe' => $this->request->getPost('frais_fixe') ?: 0,
            'frais_pourcentage' => $this->request->getPost('frais_pourcentage') ?: 0,
        ]);

        return redirect()->to('/admin/baremes')->with('success', 'Barème mis à jour');
    }

    public function supprimerBareme($id)
    {
        $this->baremeModel->delete($id);
        return redirect()->to('/admin/baremes')->with('success', 'Barème supprimé');
    }

    public function gains()
    {
        $data['gains'] = $this->gainModel->getGainsWithType();
        $data['title'] = 'Situation des gains';
        return view('admin/gains', $data);
    }

    public function clients()
    {
        $data['clients'] = $this->clientModel->findAll();
        $data['title'] = 'Situation des comptes clients';
        return view('admin/clients', $data);
    }
}