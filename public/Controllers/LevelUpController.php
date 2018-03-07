<?php
namespace OgreWeb\Controllers;
use OgreWeb\Lib\Controller;
use OgreWeb\Lib\HttpResponse;
class LevelUpController extends Controller{

    public function __construct(){
        parent::__construct();
    }

    public function index(){
        $add_level_error = array();
        if(isset($_SESSION["username"])){
            $membre = $this->_uow->MembreRepository->getConnectedMember();

            $niveaux = $this->_uow->NiveauRepository->get(array(
                "ORDER BY" => "XP ASC"
            ));

            $succes = $this->_uow->Membre_SuccesRepository->getAvailableSuccesFromMembreID($membre->MembreID);
            $quete = $this->_uow->QueteRepository->get();
            $quetesMembre = $this->_uow->Membre_QueteRepository->getByMembreID($membre->MembreID);
            $quetesMembreUnfinished = $this->_uow->Membre_QueteRepository->getUnfinishedQuestFromMemberID($membre->MembreID);
            $succesMembre = $this->_uow->Membre_SuccesRepository->getSuccesFromMemberID($membre->MembreID);

            return Controller::getView("Views/LevelUp/index.php", array(
                "membre_data" => array(
                    "membre" => $membre,
                    "niveau_index" => $this->_uow->NiveauRepository->getNiveauIndexByXP(intval($membre->XP)),
                    "quetes" => $quetesMembre,
                    "succes" => $succesMembre,
                    "isActive" => $this->_uow->MembreRepository->isMemberActive($membre->Email)
                ),
                "niveaux" => $niveaux,
                "succes" => $succes,
                "quetes" => $quete,
                "quetes_unfinished" => $quetesMembreUnfinished
            ));
        }
        parent::redirectToView("/Acceuil/Connection");
    }
}
?>
