<?php

namespace OgreWeb\DAL;
use OgreWeb\Lib\Repository;

class AbonnementRepository extends Repository{
    public function __construct($pContext){
        parent::__construct("Abonnement", $pContext);
    }

    public function getByEmail($email){
        $uow = new UnitOfWork();
        //Get the membre first
        $membre = $uow->MembreRepository->getByEmail($email);
        if(is_null($membre)){
            return false;
        }

        $exist = $this->get(array(
            "WHERE"=> "MembreID='".$membre->MembreID."'",
            "ORDER BY" => "DateDebut DESC"
        ));
        return $exist;
    }

    static function getDateFin($dateDebut, $nbSession){
        $monthDebut = date_create($dateDebut)->format("m");
        //Round the month to the exact session
        if($monthDebut >= 1 && $monthDebut < 6){
            //If winter session
            $monthEnd = ($nbSession == 2 ? 1 : 8);
            $interval = abs($monthDebut - $monthEnd);
            if($nbSession == 2)
                $interval = 12 - $interval;
        }
        else{
            //If summer session
            $monthEnd = ($nbSession == 2 ? 8 : 1);
            $interval = 12 - ($monthDebut - $monthEnd);
        }

        //One year cycle
        if($interval == 0)
            $endDate = date_add($endDate, date_interval_create_from_date_string("1 Years"));
        else
            $endDate = date_add(date_create($dateDebut), date_interval_create_from_date_string($interval." Months"));
        
        //Set the day as 1
        $endDateStr = $endDate->format("Y-m-d");
        $split = explode("-", $endDateStr);
        $split[count($split) - 1] = 1;
        $endDateStr = implode("-", $split);
        $endDate = date_create($endDateStr);
        return $endDate;
    }
}
