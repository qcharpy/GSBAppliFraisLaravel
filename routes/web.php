<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('home');
});

// Afficher le formulaire d'authentification 
//Route::get('/getLogin', 'ConnexionController@getLogin');
Route::get('/getLogin', function () {
   return view ('formLogin');
});
// Authentifie le visiteur à partir du login et mdp saisis
Route::post('/login', 'ConnexionController@logIn');

// Déloguer le visiteur
Route::get('/Logout', 'ConnexionController@logOut');

//saisirFrais
Route::get('/saisirFraisForfait', 'FraisForfaitController@saisirFraisForfait');

//saisirFrais
Route::post('/saisirFraisForfait', 'FraisForfaitController@validerFraisForfait');

// Afficher la liste des fiches de Frais du visiteur connecté
Route::get('/getListeFrais', 'VoirFraisController@getFraisVisiteur');

// Afficher le détail de la fiche de frais pour le mois sélectionné
Route::get('/voirDetailFrais/{mois}', 'VoirFraisController@voirDetailFrais');

// Afficher la liste des frais hors forfait d'une fiche de Frais
Route::get('/getListeFraisHorsForfait/{mois}', 'FraisHorsForfaitController@getFraisHorsForfait');

// Afficher le formulaire d'un Frais Hors Forfait pour une modification
Route::get('/modifierFraisHorsForfait/{idFrais}', 'FraisHorsForfaitController@modifierFraisHorsForfait');

// Afficher le formulaire d'un Frais Hors Forfait pour un ajout
Route::get('/ajouterFraisHorsForfait/{mois}', 'FraisHorsForfaitController@saisirFraisHorsForfait');

// Enregistrer une modification ou un ajout d'un Frais Hors Forfait
Route::post('/validerFraisHorsForfait', 'FraisHorsForfaitController@validerFraisHorsForfait');

// Supprimer un Frais Hors Forfait
Route::get('/supprimerFraisHorsForfait/{idFrais}', 'FraisHorsForfaitController@supprimmerFraisHorsForfait');

// Afficher le formulaire pré-remplis de modification des informations personnels
Route::get('/modifInfos', 'userController@affFormModifInfos');

// Met à jour les données personnels
Route::post('/modifInfos', 'userController@verifInfos');

//Met à jour le mot de passe
Route::post('/modifMdp', 'userController@modifMdp');

// Affiche la liste utilisateurs (Visiteurs et Délégués)
Route::get('/gestionUtilisateurs', 'userController@getUser'); //

// Afficher le formulaire d'ajout d'un nouveau visiteur
Route::get('/ajoutVisiteur', 'userController@getRegion'); //

// Ajoute un nouveau visiteur
Route::post('/addVisitor', 'userController@addVisiteur'); 

// Retourner à une vue dont on passe le nom en paramètre
Route::get('getRetour/{retour}', function($retour){
    return redirect("/".$retour);
});

// Modifier les infos d'un visiteur
Route::get('modifLesInfos/{idVisiteur}', 'userController@getOneUser'); //

// Validation du formulaire de modification des infos
Route::post('/modifUser', 'userController@update');


// Gestion frais 
Route::get('/gestionFrais', function () {
    
    if (Session::get('aff_role') == 'Délégué' || Session::get('aff_role') == 'Responsable') {
        return view ('gestionFrais');
    }
    else {
        return view('accesInterdit');
    }
 });
