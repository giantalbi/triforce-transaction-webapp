<?php
namespace OgreWeb\Models;
class Membre{
    public $MembreID;
    public $Nom;
    public $Prenom;
    public $Email;
    public $MotDePasse = null;
    public $DateInscription;
    public $EstCA = false;
    public $XP = 0;
    public $Level = 0;
    public $Photo = null;
}
?>
