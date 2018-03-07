<?php
namespace OgreWeb\Controllers;
use OgreWeb\Lib\Controller;
use OgreWeb\Lib\HttpResponse;
class MembreController extends Controller{

    public function __construct(){
        //Call the parent Controller
        parent::__construct();
    }

    public function index(){
        //Load the member's profile if the session is set
        if(isset($_SESSION["username"])){
            $member = $this->_uow->MembreRepository->getConnectedMember();

            $abonnement_connected = $this->_uow->AbonnementRepository->Get(array(
                "WHERE" => "MembreID=".$member->MembreID." AND ".
                "\"".date("Y-m-d")." \" BETWEEN DateDebut AND DateFin"
            ));
            return Controller::getView("Views/Member/index.php", array(
                //Get the connected member
                "membre_data" => array(
                    "membre" => $member,
                    "abonnement" => (count($abonnement_connected) > 0 ? $abonnement_connected[0] : null),
                    "isActive" => $this->_uow->MembreRepository->isMemberActive($member->Email)
                ),
                //If the connected member is CA, get the administration's data
                "CA_data" => ($member->EstCA ? array(
                    "membres" => $this->_uow->MembreRepository->Get(array(
                        "ORDER BY" => "Nom"
                    )),
                    "abonnements" => $this->_uow->AbonnementRepository->Get(),
                    "quetes" => $this->_uow->QueteRepository->get(),
                    "quetes_membres" => $this->_uow->Membre_QueteRepository->get(),
                    "succes" => $this->_uow->SuccesRepository->get(),
                    "succes_membre" => $this->_uow->Membre_SuccesRepository->get()
                    ) : NULL)
                ));
            }
            //If there is no user connected
            parent::redirectToView("/Acceuil/Connection");
        }

        public function confirmationEmail($param = null){
            $hash = $param;
            if(is_null($hash))
            parent::redirectToView("/Acceuil/Index");
            $hash = $this->_uow->sanitize($hash);
            //Check if the given hash exists
            $exist = $this->_uow->ConfirmationEmailRepository->get(array(
                "WHERE" => "Hash=\"".$hash."\""
            ));
            if(count($exist) == 1){
                unset($_SESSION["username"]);

                //Check if the link is still available
                $confirm = $exist[0];
                $date = date_create($confirm->Date);
                $today = date_create(date("Y-m-d"));
                $diff = date_diff($date, $today, true);
                if($diff->d >= 1){
                    $this->_uow->ConfirmationEmailRepository->remove($confirm);
                    parent::redirectToView("/Acceuil/Index");
                }

                $id = $confirm->MembreID;
                $member = $this->_uow->MembreRepository->getByID($id);
                $member->Email = $confirm->Email;
                $this->_uow->MembreRepository->update($member, $id);
                $this->_uow->ConfirmationEmailRepository->remove($confirm);
            }
            parent::redirectToView("/Acceuil/Connection");
        }

        public function confirmationMotDePasse($param = null){
            $hash = $param;
            if(is_null($hash))
            parent::redirectToView("/Acceuil/Index");
            unset($_SESSION["username"]);
            $hash = $this->_uow->sanitize($hash);
            //Check if the given hash exists
            $exist = $this->_uow->ConfirmationMdpRepository->get(array(
                "WHERE" => "Hash=\"".$hash."\""
            ));
            if(count($exist) > 0){
                $confirm = $exist[0];
                //Check if the link is still available
                $date = date_create($confirm->Date);
                $today = date_create(date("Y-m-d"));
                $diff = date_diff($date, $today, true);
                if($diff->d >= 1){
                    $this->_uow->ConfirmationMdpRepository->remove($confirm);
                    parent::redirectToView("/Acceuil/Index");
                }
                return Controller::getView("Views/Member/confirmationMotDePasse.php", array(
                    "Hash"=>$hash
                ));
            }
            parent::redirectToView("/Acceuil/Index");
        }

        public function classement(){
            $membres = $this->_uow->MembreRepository->getClassementLevelUp();
            $membres_data = array();
            foreach($membres as $membre){
                $membre_data = array(
                    "membre" => $membre,
                    "niveau_index" => $this->_uow->NiveauRepository->getNiveauIndexByXP($membre->XP)
                );
                array_push($membres_data, $membre_data);
            }


            $niveaux = $this->_uow->NiveauRepository->get();
            return Controller::getView("Views/Member/classement.php", array(
                "membres_data" => $membres_data,
                "niveaux" => $niveaux
            ));
        }
    }
    ?>
