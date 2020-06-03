<?php
namespace App\metier;

use Illuminate\Support\Facades\DB;

/** 
 * Classe d'accès aux données. 
 */
class GsbFrais{   		
/**
 * Retourne les informations d'un visiteur 
 
 * @param $login 
 * @param $mdp
 * @return l'id, le nom et le prénom sous la forme d'un objet 
*/
    public function getInfosVisiteur($login, $mdp){
            $req = "select visiteur.id as id, visiteur.nom as nom, visiteur.prenom as prenom, vaffectation.aff_role as aff_role, vaffectation.reg_nom as region, vaffectation.sec_nom as sec_nom, vaffectation.aff_sec as secteur, vaffectation.aff_reg as region_code
            from visiteur inner join vaffectation on vaffectation.idVisiteur = visiteur.id
            where visiteur.login=:login and visiteur.mdp=:mdp";
            $ligne = DB::select($req, ['login'=>$login, 'mdp'=>sha1($mdp)]);
            return $ligne;
    }

/**
 * Retourne les informations personnelles d'un visiteur
 
 * @param $id 
 * @return la ville et le cp sous la forme d'un objet 
*/
    public function getInfosPerso($id){
		$req = "select id, nom, prenom, adresse, cp, ville, tel, email, aff_reg, aff_role from visiteur inner join 
		vaffectation on visiteur.id = vaffectation.idVisiteur where visiteur.id= :id";
        $ligne = DB::select($req, ['id'=>$id]);
        return $ligne[0];
    }

    /**
     * Insère un nouveau visiteur dans la pase de donnée.
     *
     * @param $id
     * @param $name
     * @param $firstName
     * @param $login
     * @param $mdp
     * @param $address
     * @param $cp
     * @param $town
     * @param $hireDate
     * @param $tel
     * @param $mail
     */
    public function addUser($id, $name, $firstName, $login, $mdp, $address, $cp, $town, $hireDate, $tel, $mail, $region, $role) {
        $request = "insert into visiteur (id, nom, prenom, login, mdp, adresse, cp, ville, dateEmbauche, tel, email)
            VALUES (:id, :nom, :prenom, :login, :mdp, :addr, :cp, :ville, :dateEmb, :tel, :mail)";
        $request1 = "insert into travailler (idVisiteur, tra_date, tra_reg, tra_role)
            VALUES (:idVisi, :dateDbt, :region, :role)";
        DB::insert($request, ['id'=>$id, 'nom'=>$name, 'prenom'=>$firstName, 'login'=>$login, 'mdp'=>sha1($mdp), 'addr'=>$address, 'cp'=>$cp, 'ville'=>$town, 'dateEmb'=>$hireDate, 'tel'=>$tel, 'mail'=>$mail]);
        DB::insert($request1, ['idVisi'=>$id, 'dateDbt'=>$hireDate, 'region'=>$region, 'role'=>$role]);
    }

    /**
     * Récupère la liste des régions
     *
     * @return un objet avec tous les champs des lignes de region
     */
    public function getRegion($sec) {
        $request = "SELECT id, reg_nom FROM region WHERE sec_code = :sec";
        $result = DB::select($request, ['sec'=>$sec]);
        return $result;
    }

	/**
	 * Vérifie si le visiteur existe déjà.
	 * 
     * @param $id
     * @param $login
     * @return int, nombre de ligne retourner par la requete
     */
    public function existVisitor($id, $login) {
        $request = "SELECT COUNT(*) AS existUser FROM visiteur WHERE id = :id OR login = :login";
        $result = DB::select($request, ['id'=>$id, 'login'=>$login]);
        return $result[0]->existUser;
    }

/**
 * Retourne sous forme d'un tableau d'objets toutes les lignes de frais hors forfait
 * concernées par les deux arguments
 
 * La boucle foreach ne peut être utilisée ici car on procède
 * à une modification de la structure itérée - transformation du champ date-
 
 * @param $idVisiteur 
 * @param $mois sous la forme aaaamm
 * @return un objet avec tous les champs des lignes de frais hors forfait 
*/
	public function getLesFraisHorsForfait($idVisiteur,$mois){
	    $req = "select * from lignefraishorsforfait where lignefraishorsforfait.idvisiteur =:idVisiteur 
		and lignefraishorsforfait.mois = :mois ";	
            $lesLignes = DB::select($req, ['idVisiteur'=>$idVisiteur, 'mois'=>$mois]);
//            for ($i=0; $i<$nbLignes; $i++){
//                    $date = $lesLignes[$i]['date'];
//                    $lesLignes[$i]['date'] =  dateAnglaisVersFrancais($date);
//            }
            return $lesLignes; 
	}
/**
 * Retourne sous forme d'un tableau associatif toutes les lignes de frais au forfait
 * concernées par les deux arguments
 
 * @param $idVisiteur 
 * @param $mois sous la forme aaaamm
 * @return un objet contenant les frais forfait du mois
*/
	public function getLesFraisForfait($idVisiteur, $mois){
		$req = "select fraisforfait.id as idfrais, fraisforfait.libelle as libelle, lignefraisforfait.mois as mois,
		lignefraisforfait.quantite as quantite from lignefraisforfait inner join fraisforfait 
		on fraisforfait.id = lignefraisforfait.idfraisforfait
		where lignefraisforfait.idvisiteur = :idVisiteur and lignefraisforfait.mois = :mois
		order by lignefraisforfait.idfraisforfait";	
//                echo $req;
                $lesLignes = DB::select($req, ['idVisiteur'=>$idVisiteur, 'mois'=>$mois]);
		return $lesLignes; 
	}
/**
 * Retourne tous les id de la table FraisForfait
 * @return un objet avec les données de la table frais forfait
*/
	public function getLesIdFrais(){
		$req = "select fraisforfait.id as idfrais from fraisforfait order by fraisforfait.id";
		$lesLignes = DB::select($req);
		return $lesLignes;
	}
/**
 * Met à jour la table ligneFraisForfait
 
 * Met à jour la table ligneFraisForfait pour un visiteur et
 * un mois donné en enregistrant les nouveaux montants
 
 * @param $idVisiteur 
 * @param $mois sous la forme aaaamm
 * @param $lesFrais tableau associatif de clé idFrais et de valeur la quantité pour ce frais
*/
	public function majFraisForfait($idVisiteur, $mois, $lesFrais){
		$lesCles = array_keys($lesFrais);
    //            print_r($lesFrais);
		foreach($lesCles as $unIdFrais){
			$qte = $lesFrais[$unIdFrais];
			$req = "update lignefraisforfait set lignefraisforfait.quantite = :qte
			where lignefraisforfait.idvisiteur = :idVisiteur and lignefraisforfait.mois = :mois
			and lignefraisforfait.idfraisforfait = :unIdFrais";
                        DB::update($req, ['qte'=>$qte, 'idVisiteur'=>$idVisiteur, 'mois'=>$mois, 'unIdFrais'=>$unIdFrais]);
		}
		
	}
/**
 * met à jour le nombre de justificatifs de la table ficheFrais
 * pour le mois et le visiteur concerné
 
 * @param $idVisiteur 
 * @param $mois sous la forme aaaamm
*/
	public function majNbJustificatifs($idVisiteur, $mois, $nbJustificatifs){
		$req = "update fichefrais set nbjustificatifs = :nbJustificatifs 
		where fichefrais.idvisiteur = :idVisiteur and fichefrais.mois = :mois";
		DB::update($req, ['nbJustificatifs'=>$nbJustificatifs, 'idVisiteur'=>$idVisiteur, 'mois'=>$mois]);	
	}
/**
 * Teste si un visiteur possède une fiche de frais pour le mois passé en argument
 
 * @param $idVisiteur 
 * @param $mois sous la forme aaaamm
 * @return vrai ou faux 
*/	
	public function estPremierFraisMois($idVisiteur,$mois)
	{
		$ok = false;
		$req = "select count(*) as nblignesfrais from fichefrais 
		where fichefrais.mois = :mois and fichefrais.idvisiteur = :idVisiteur";
		$laLigne = DB::select($req, ['idVisiteur'=>$idVisiteur, 'mois'=>$mois]);
                $nb = $laLigne[0]->nblignesfrais;
		if($nb == 0){
			$ok = true;
		}
		return $ok;
	}
/**
 * Retourne le dernier mois en cours d'un visiteur
 
 * @param $idVisiteur 
 * @return le mois sous la forme aaaamm
*/	
	public function dernierMoisSaisi($idVisiteur){
		$req = "select max(mois) as dernierMois from fichefrais where fichefrais.idvisiteur = :idVisiteur";
		$laLigne = DB::select($req, ['idVisiteur'=>$idVisiteur]);
                $dernierMois = $laLigne[0]->dernierMois;
		return $dernierMois;
	}
	
/**
 * Crée une nouvelle fiche de frais et les lignes de frais au forfait pour un visiteur et un mois donnés
 
 * récupère le dernier mois en cours de traitement, met à 'CL' son champs idEtat, crée une nouvelle fiche de frais
 * avec un idEtat à 'CR' et crée les lignes de frais forfait de quantités nulles 
 * @param $idVisiteur 
 * @param $mois sous la forme aaaamm
*/
	public function creeNouvellesLignesFrais($idVisiteur,$mois){
		$dernierMois = $this->dernierMoisSaisi($idVisiteur);
		$laDerniereFiche = $this->getLesInfosFicheFrais($idVisiteur,$dernierMois);
		if($laDerniereFiche->idEtat=='CR'){
				$this->majEtatFicheFrais($idVisiteur, $dernierMois,'CL');
				
		}
		$req = "insert into fichefrais(idvisiteur,mois,nbJustificatifs,montantValide,dateModif,idEtat) 
		values(:idVisiteur,:mois,0,0,now(),'CR')";
		DB::insert($req, ['idVisiteur'=>$idVisiteur, 'mois'=>$mois]);
		$lesIdFrais = $this->getLesIdFrais();
		foreach($lesIdFrais as $uneLigneIdFrais){
			$unIdFrais = $uneLigneIdFrais->idfrais;
			$req = "insert into lignefraisforfait(idvisiteur,mois,idFraisForfait,quantite) 
			values(:idVisiteur,:mois,:unIdFrais,0)";
			DB::insert($req, ['idVisiteur'=>$idVisiteur, 'mois'=>$mois, 'unIdFrais'=>$unIdFrais]);
		 }
	}
/**
 * Crée un nouveau frais hors forfait pour un visiteur un mois donné
 * à partir des informations fournies en paramètre
 
 * @param $idVisiteur 
 * @param $mois sous la forme aaaamm
 * @param $libelle : le libelle du frais
 * @param $date : la date du frais au format français jj//mm/aaaa
 * @param $montant : le montant
*/
	public function creeNouveauFraisHorsForfait($idVisiteur,$mois,$libelle,$date,$montant){
//		$dateFr = dateFrancaisVersAnglais($date);
		$req = "insert into lignefraishorsforfait(idVisiteur, mois, libelle, date, montant) 
		values(:idVisiteur,:mois,:libelle,:date,:montant)";
		DB::insert($req, ['idVisiteur'=>$idVisiteur, 'mois'=>$mois, 'libelle'=>$libelle,'date'=>$date,'montant'=>$montant]);
	}

/**
 * Récupère le frais hors forfait dont l'id est passé en argument
 * @param $idFrais 
 * @return un objet avec les données du frais hors forfait
*/
	public function getUnFraisHorsForfait($idFrais){
		$req = "select * from lignefraishorsforfait where lignefraishorsforfait.id = :idFrais ";
		$fraisHF = DB::select($req, ['idFrais'=>$idFrais]);
//                print_r($unfraisHF);
                $unFraisHF = $fraisHF[0];
                return $unFraisHF;
	}
/**
 * Modifie frais hors forfait à partir de son id
 * à partir des informations fournies en paramètre
 * @param $id 
 * @param $libelle : le libelle du frais
 * @param $date : la date du frais au format français jj//mm/aaaa
 * @param $montant : le montant
*/
	public function modifierFraisHorsForfait($id, $libelle,$date,$montant){
//		$dateFr = dateFrancaisVersAnglais($date);
		$req = "update lignefraishorsforfait set libelle = :libelle, date = :date, montant = :montant
		where id = :id";
		DB::update($req, ['libelle'=>$libelle,'date'=>$date,'montant'=>$montant, 'id'=>$id]);
	}
        
/**
 * Supprime le frais hors forfait dont l'id est passé en argument
 * @param $idFrais 
*/
	public function supprimerFraisHorsForfait($idFrais){
		$req = "delete from lignefraishorsforfait where lignefraishorsforfait.id = :idFrais ";
		DB::delete($req, ['idFrais'=>$idFrais]);
	}
/**
 * Retourne les fiches de frais d'un visiteur à partir d'un certain mois
 * @param $idVisiteur 
 * @param $mois mois début
 * @return un objet avec les fiches de frais de la dernière année
*/
	public function getLesFrais($idVisiteur, $mois){
		$req = "select * from  fichefrais where idvisiteur = :idVisiteur
                and  mois >= :mois   
		order by fichefrais.mois desc ";
                $lesLignes = DB::select($req, ['idVisiteur'=>$idVisiteur, 'mois'=>$mois]);
                return $lesLignes;
	}
/**
 * Retourne les informations d'une fiche de frais d'un visiteur pour un mois donné
 * @param $idVisiteur 
 * @param $mois sous la forme aaaamm
 * @return un objet avec des champs de jointure entre une fiche de frais et la ligne d'état 
*/	
	public function getLesInfosFicheFrais($idVisiteur,$mois){
		$req = "select fichefrais.idEtat as idEtat, fichefrais.dateModif as dateModif, fichefrais.nbJustificatifs as nbJustificatifs, 
			fichefrais.montantValide as montantValide, etat.libelle as libEtat from  fichefrais inner join etat on fichefrais.idEtat = etat.id 
			where fichefrais.idvisiteur = :idVisiteur and fichefrais.mois = :mois";
		$lesLignes = DB::select($req, ['idVisiteur'=>$idVisiteur,'mois'=>$mois]);			
		return $lesLignes[0];
	}
/** 
 * Modifie l'état et la date de modification d'une fiche de frais
 * Modifie le champ idEtat et met la date de modif à aujourd'hui
 * @param $idVisiteur 
 * @param $mois sous la forme aaaamm
 */
 
	public function majEtatFicheFrais($idVisiteur,$mois,$etat){
		$req = "update fichefrais set idEtat = :etat, dateModif = now() 
		where fichefrais.idvisiteur = :idVisiteur and fichefrais.mois = :mois";
		DB::update($req, ['etat'=>$etat, 'idVisiteur'=>$idVisiteur, 'mois'=>$mois]);
	}


	/**
	 * Met à jour les informations d'un utilisateur
	 *
	 * @param $idVisiteur id de l'utilisateur
	 * @param $adresse nouvelle adresse de l'utilisateur
	 * @param $cp nouveau code postal de l'utilisateur
	 * @param $ville nouvelle ville de l'utilisateur
	 * @param $tel nouveau téléphone de l'utilisateur
	 * @param $email nouvelle email de l'utilistateur
	 **/
	public function majInfosUtilisateur($idVisiteur, $adresse, $cp, $ville, $tel, $email)
	{
		$req = "UPDATE visiteur SET adresse = :adresse, cp = :cp, ville = :ville, tel = :tel, email = :email WHERE id = :idVisiteur;";
		DB::update($req, ['adresse'=>$adresse, 'cp'=>$cp, 'ville'=>$ville, 'tel'=>$tel, 'email'=>$email, 'idVisiteur'=>$idVisiteur]);
	}


	/**
	 * Calcul de la fiche frais du mois
	 *
	 * @param $idVisiteur id de l'utilisateur
	 * @param $mois mois de la fiche de frais
	 *
	 * 
	 **/
	public function calculFicheFrais($idVisiteur, $mois) {
		$req= "UPDATE fichefrais as laFiche SET montantValide = (SELECT SUM(quantite * fraisforfait.montant) FROM lignefraisforfait INNER JOIN fraisforfait 
		ON lignefraisforfait.idFraisForfait = fraisforfait.id WHERE idVisiteur = laFiche.idVisiteur AND mois = laFiche.mois) + (SELECT SUM(montant) 
		FROM lignefraishorsforfait WHERE idVisiteur = laFiche.idVisiteur AND mois = laFiche.mois) WHERE idVisiteur = :idVisiteur AND idEtat = 'CL' AND montantValide = 0";
		DB::update($req, ['idVisiteur'=>$idVisiteur]);
	}

	/**
	 * Met à jour le mot de passe d'un visiteur spécifié
	 *
	 *
	 * @param $idVisiteur ID du visiteur pour lequel le mot de passe doit être modifié
	 * @param $newMdp nouveau mot de passe du visiteur
	 **/
	public function miseAJourMdp($idVisiteur, $newMdp)
	{
		$req = "UPDATE visiteur SET mdp = SHA1(:newMdp) WHERE id = :idVisiteur;";
		DB::update($req, ['idVisiteur'=>$idVisiteur, 'newMdp'=>$newMdp]);
	}

	/**
	 * Récupère les visiteurs d'un secteur
	 *
	 *
	 * @param $nomsecteur Nom du secteur du responsable qui veut gérer les visiteurs et délégués de son secteur
	 * @return une liste de visiteur
	 **/
	public function getSecteurVisiteur($nomSecteur){
		$req = "SELECT id, nom, prenom, vaffectation.aff_role, vaffectation.reg_nom, adresse, cp, ville, tel, email FROM visiteur INNER JOIN vaffectation
		ON visiteur.id = vaffectation.idVisiteur WHERE vaffectation.sec_nom = :secteur AND vaffectation.aff_role = 'Visiteur' OR vaffectation.aff_role = 'Délégué'";
        $result = DB::select($req, ['secteur'=>$nomSecteur]);
        return $result;
	}

	/**
	 * Met à jour le role et/ou la région d'un utilisateur
	 * 
     * @param $id
     * @param $date
     * @param $region
     * @param string $role
     */
	public function updateVisiteur($id, $date, $region, $role = 'Visiteur') {
        $request = "insert into travailler (idVisiteur, tra_date, tra_reg, tra_role)
            VALUES (:idVisi, :dateDbt, :region, :role)";
        DB::insert($request, ['idVisi'=>$id, 'dateDbt'=>$date, 'region'=>$region, 'role'=>$role]);
    }
}
?>
