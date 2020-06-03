<?php


namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\metier\GsbFrais;

class userController extends Controller {

    /**
     * Ajoute un nouveau visiteur ou délégué
     * 
     * @param Request $request
     * @return type Vue confirmInscript, avec tableau associatif de login et mdp
     */
    public function addVisiteur(Request $request) {
        $this->validate($request, [
            'address' => ['bail', 'required', "regex:/[0-9]{1,3}\s[a-z\séèàêâùïüëA-Z-']{1,29}/"],
            'ville' => ['bail', 'required', "regex:/^[a-zéèàêâùïüëA-Z][a-zéèàêâùïüëA-Z-'\s]{1,30}$/"],
            'cp' => ['bail', 'required', 'digits:5'],
            'tel' => ['bail', 'required', 'digits_between:3,15'],
            'mail' => ['bail', 'required', 'email']
        ]);
        $bdd = new GsbFrais();
        $name = $request->input('name');
        $firstName = $request->input('firstName');
        $login = strtolower(substr($firstName, 0,1).$name);
        $mdp = $this->generatePassword();
        $address = $request->input('address');
        $cp = $request->input('cp');
        $ville = $request->input('ville');
        $dateEmb = date('Y-m-d');
        $tel = $request->input('tel');
        $mail = $request->input('mail');
        $region = $request->input('region');
        $role = $request->input('role');

        $id = $this->generagetId(strtolower(substr($name, 0,1)));

        if ($bdd->existVisitor($id, $login) != 0){
            $error = 'Le visiteur existe déjà !';
            return redirect('/ajoutVisiteur')->with(compact('error'));
        } else {
            $bdd->addUser($id, $name, $firstName, $login, $mdp, $address, $cp, $ville, $dateEmb, $tel, $mail, $region, $role);
            return view('confirmInscript', compact('login', 'mdp'));
        }
    }

    /**
     * Initialise le formulaire des infos personnelles
     *
     * @return type Vue formModifInfos, avec tableau associatif des informations
     */
    public function affFormModifInfos() {
        $erreur = "";
        $idVisiteur = Session::get('id');
        $gsbFrais = new GsbFrais();
        $info = $gsbFrais->getInfosPerso($idVisiteur);
        // Affiche le formulaire en lui fournissant les données à afficher
        // la fonction compact équivaut à array('lesFrais' => $lesFrais, ...)
        return view('formModifInfos', compact('info', 'erreur'));
    }

    /**
     * Vérifie les infos et met à jour l'utilisateur dans le base de données
     *
     * @param Request $request
     * @return type Vue confirmModifIngos
     */
    public function verifInfos(Request $request) {
        $this->validate($request, [
            'adresse' => ['bail', 'required', "regex:/[0-9]{1,3}\s[a-z\séèàêâùïüëA-Z-']{1,29}/"],
            'ville' => ['bail', 'required', "regex:/^[a-zéèàêâùïüëA-Z][a-zéèàêâùïüëA-Z-'\s]{1,30}$/"],
            'cp' => ['bail', 'required', 'digits:5'],
            'tel' => ['bail', 'required', 'digits_between:3,15'],
            'email' => ['bail', 'required', 'email']
        ]);
        //Récupérer les données pour mettre à jour
        $adresse = $request->input('adresse');
        $cp = $request->input('cp');
        $ville = $request->input('ville');
        $idVisiteur = Session::get('id');
        $tel = $request->input('tel');
        $email = $request->input('email');

        //Mise à jour des informations de l'utilisateur en base de données
        $gsbFrais = new GsbFrais();
        $gsbFrais->majInfosUtilisateur($idVisiteur, $adresse, $cp, $ville, $tel, $email);

        // confirmer la mise à jour
        return view('confirmModifInfos');
    }

    /**
     * Récupère la liste des Régions
     *
     * @return type Vue formUser, avec tableau associatif de Région
     */
    public function getRegion() {
        //Si l'utilisateur n'est pas un responsable alors le rediriger sur la page d'accès réfusé
        if (Session::get('aff_role') <> 'Responsable') {
            return view('accesInterdit');
        }
        $bdd = new GsbFrais();
        $secteur = Session::get('sec_code');
        $lesRegion = $bdd->getRegion($secteur);

        return view('formUser', compact('lesRegion'));
    }

    /**
     * Fonction qui récupère la liste des visiteurs d'un secteur
     *
     * @return type Vue listeUser, avec tableau associatif de visiteurs
     */
    public function getUser() {
        //Si l'utilisateur n'est pas un responsable alors le rediriger sur la page d'accès réfusé
        if (Session::get('aff_role') <> 'Responsable') 
        {
            return view('accesInterdit');
        }
        $frais = new GsbFrais();
        $secteur = Session::get('sec_nom');
        $visiteurs = $frais->getSecteurVisiteur($secteur);
        return view('listeUser', compact('visiteurs'));
    }

    /**
     * Fonction affiche un visiteur
     *
     * @param idVisiteur 
     * @return type 
     */
    public function getOneUser($idVisiteur){
        //Si l'utilisateur n'est pas un responsable alors le rediriger sur la page d'accès réfusé
        if (Session::get('aff_role') <> 'Responsable') 
        {
            return view('accesInterdit');
        }
        $erreur = "";
        //$idVisiteur;
        $gsbFrais = new GsbFrais();
        //Récupère les infos de l'utilisateur spécifié
        $info = $gsbFrais->getInfosPerso($idVisiteur);
        // Affiche le formulaire en lui fournissant les données à afficher
        // la fonction compact équivaut à array('lesFrais' => $lesFrais, ...)
        //Récupère les régions d'affectation du secteur du responsable connecté
        $secteur = Session::get('sec_code');
        $lesRegion = $gsbFrais->getRegion($secteur);
        return view('formModifUser', compact('info', 'lesRegion', 'erreur'));
    }

    /**
     * Modifie le mot de passe de l'utilisteur après avoir vérifié sa conformité

     * @param Request $request Données saisi dans le formulaire de modification du mot de passe
     * @return View 
     **/
    public function modifMdp(Request $request) {
        //Vérification du mot de passe actuel de l'utilisateur
        $login = Session::get('login');
        $pwd = $request->input('mdpActuel'); 
        $gsbFrais = new GsbFrais();
        $res = $gsbFrais->getInfosVisiteur($login,$pwd);
        if(empty($res)) { //Si le mot de passe actuel est faux alors afficher une erreur dans le formulaire de modification du mot de passe
            $erreur = "mot de passe incorrect !";
            return back()->with('erreur', $erreur);
        }
        $this->validate($request, [
            'newPassword' => ['bail', 'required', 'confirmed', 'min:6', "regex:/(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])([a-zA-Z0-9$@%*+\-_!]{6,})/"],
            'newPassword_confirmation' => ['bail', 'required'],
        ]);

        //Modification du mot de passe
        $newPwd = $request->input('newPassword');
        $idVisiteur = Session::get('id');
        $gsbFrais->miseAJourMdp($idVisiteur, $newPwd);

        //Retour de la vue de confirmation
        return view('confirmModifMdp');

    }

    /**
     * Validation des modifications du formulaire
     * 
     * @param Request $request
     * @return view confirmModifInfos ou formModifInfos avec message d'erreur
     */
    public function update(Request $request) {
        $gsbFrais = new GsbFrais();
        $promotion = $request->input('role1');
        $region = $request->input('region');
        $value = $request->input('saveReg');
        $id = $request->input('saveId');
        $date = date('Y-m-d');

        if ($region != $value || $promotion == 'oui'){
            if ($promotion == 'oui') {
                $gsbFrais->updateVisiteur($id, $date, $region, 'Délégué');
            } else {$gsbFrais->updateVisiteur($id, $date, $region);}
            return view('confirmModifInfos');
        } else {
            $error = 'Vous n\'avez modifié aucune information !';
            return redirect('/modifLesInfos/'.$id)->with(compact('error'));
        }
    }

    /**
     * Génère un identifiant aléatoire composer entre 2 et 4 caractères.
     *
     * @param $firstChar
     * @return string
     */
    private function generagetId($firstChar){
        $id = $firstChar;
        $long = rand(1, 3);
        $chaine = '123456789';
        for ($i = 0; $i < $long; $i++){
            $id .= $chaine[rand(0, strlen($chaine) - 1)];
        }
        return $id;
    }

    /**
     * Génère un mot de passe aléatoire de 5 caractères.
     *
     * @return string
     */
    private function generatePassword() {
        $password = '';
        $chaine = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        do 
        {
            $password = "";
            for ($i = 0; $i < 6; $i++){
                $password .= $chaine[rand(0, strlen($chaine) - 1)];
            }
        } 
        while (preg_match('/(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])([a-zA-Z0-9$@%*+\-_!]{6,})/', $password) == false);
        return $password;
    }
}