To do list 
Frank : 
1-script.sql 

Migration 
1-Table users
php spark make:migration create_users_table

2-Table clients
php spark make:migration create_clients_table

3-Table prefixes_operateur
php spark make:migration create_prefixes_operateur_table


Tahiry : 
Suite Migration 
4- Création de :Table types_operations
create_types_operations_tables

5- création de Table baremes_frais

6- Création Table transactions
php spark make:migration create_transactions_table

7-Creation de Table gains 
migration create_gains_table

Frank 
Models 
 Création de : 
_model User
_model Client
_model PrefixeOperateur
_model TypeOperation

Tahiry :
Suite Models 
Création de : 
model BaremeFrais
model Transaction
model Gain


Frank : 
Routes 

Tahiry : 
Ajout du filtre Auth 
filter Auth 

Frank : 
Template css telechargé 
Main.php
 App/views client/login 
Dashboard
dépôt 

Problème PC de Tahiry : 
Travaillé sur le Pc de Frank 
_retrait 
_transfert 
_historique 

Frank 
Admin :
Barème 
Préfixe 
Type_operation
 
Tahiry reutilise son pc 

Tahiry 
Suite Admin 
Bareme_edit 
Gains
Clients 

Frank 
Debug

---------------------V2-------------------------------

Tahiry : 
Amélioration css 

Frank: 
Amélioration du login 

Frank 
Modification table 

Tahiry 
Update du controller 
AdminController 
ClientController

Frank 
Migrations

 add_operator_fields_to_prefixes 
 = ajoute est_autre_operateur et commission_pourcentage dans prefixes_operateur
 add_frais_inclus_to_transactions → ajoute frais_inclus et est_inter_operateur dans transactions