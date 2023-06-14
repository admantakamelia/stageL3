    <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
            "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
  <title>Informations sur l'état d'un bureau</title>
  <meta name="Description" content="Gestion des ressources IBISC">
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
</head>
<body style="font-family:calibri,verdana">
<?php


/*-------------------------------------------------------------------------------------------------
//
*/
// Constantes associées à la gestion de la DB

define('SERVER','127.0.0.1');
define('LOGIN', 'root');
define('PASS','');
define('DB','gestionbureaux'); // nom de la BD
define('PORT', 3306); // port associé à mysql

// Code des erreurs
define('ERR_OK',0);
define('ERR_CANNOT_OPEN_DB',1);
define('ERR_MYSQLI_QUERY', 2);
define('ERR_FETCHASSOC', 3);
define('ERR_DESKID_NOT_FOUND_IN_DB', 4);
define('ERR_DESKNAME_NOT_FOUND_IN_DB', 5);
define('ERR_BOOKINGID_NOT_FOUND_IN_DB', 6);
define('ERR_ROOMID_NOT_FOUND_IN_DB', 7);
define('ERR_ROOMNAME_NOT_FOUND_IN_DB', 8);

$error=ERR_OK;  // Gestion des erreurs

//---------------------------------------------------
// Affichage des erreurs
function printError()
{
   global $error;
   if ($error == ERR_OK)
   {
      print_r('AUCUNE ERREUR<BR>');
      return true;
   }
   switch ($error) {
	  case ERR_CANNOT_OPEN_DB:
		 print_r('Cannot open database');
		 break;
      case ERR_MYSQLI_QUERY:
         print_r('Mysqli query ran wrong ...');
         break;
      case ERR_FETCHASSOC:
         print_r('Fetch_assoc ran wrong ...');
         break;
      case ERR_DESKID_NOT_FOUND_IN_DB:
         print_r('deskId not found in database');
         break;
     case ERR_DESKNAME_NOT_FOUND_IN_DB:
         print_r('desk name not found in database');
         break;
     case ERR_BOOKINGID_NOT_FOUND_IN_DB:
         print_r('bookingId not found in database');
         break;
     case ERR_ROOMID_NOT_FOUND_IN_DB:
         print_r('roomId not found in database');
         break;
     case ERR_ROOMNAME_NOT_FOUND_IN_DB:
         print_r('room name not found in database');
         break;
	 default:
		 print_r('ERREUR NON PREVUE: '.$error.'<OK>');
		break;
   }
   print_r('<BR>');
   return true;
}

//-------------------------------------------------------------

function open_gestionbureaux_db( &$mysqli)
{
    global $error;
    $mysqli = new mysqli(SERVER, LOGIN, PASS, DB, PORT);

   if ($mysqli->connect_errno)
   {
      $error = ERR_CANNOT_OPEN_DB;
      return false;
   }
   $error = ERR_OK;
   return true;
}
//-------------------------------------------------------
//
class bureau {
    public $id; // identifiant du bureau
    public $nom; // nom du bureau
    public $estReservable; // peut-on réserver le bureau?
    public $estPartageable; // le bureau est-il partageable entre plusieurs personnes?
    
    //---------------------------------------------------
    // Affichage des informations concernant le bureau courant
    public function printDeskInfo()
    {
        print_r('Bureau '.$this->nom.'('.$this->id.'): ');
        if ($this->estReservable == 1)
           print_r('[RESERVABLE] ');
        else
           print_r('[NON RESERVABLE] ');
        if ($this->estPartageable == 1)
           print_r('[PARTAGEABLE] ');
        else
           print_r('[NON PARTAGEABLE] ');
        print_r('<BR>');
    }


    //-------------------------------------------------------------
    // Récupère les infos d'un bureau repéré par son identifiant deskId
    // Pré: on suppose que l'identifiant deskId existe dans la BD
    public function getDeskInfoFromDeskId_db($deskId)
    {
      global $error;
      $this->id = $deskId;
      if ( open_gestionbureaux_db( $mysqli) == false)
      {
         $error = ERR_CANNOT_OPEN_DB;
         return false;
      }
 
      $query = 'SELECT nom, estReservable, estPartageable FROM bureau WHERE id="'.$deskId.'"';

      if ( ($res = $mysqli->query($query)) == false)
      {
          $error = ERR_MYSQLIQUERY;
	      return false;
      }
      elseif (($row = $res->fetch_assoc( )) == NULL)
      {
          $error = ERR_DESKID_NOT_FOUND_IN_DB;
          return false;
      }
      elseif (sizeof($row) == 0)
      {
          $error = ERR_DESKID_NOT_FOUND_IN_DB;
          return false;
      }
      else
      {
        $this->nom = $row['nom'];
        $this->estPartageable = $row['estPartageable'];
        $this->estReservable = $row['estReservable'];
      }
      $error = ERR_OK;
      return true;
    }

    //-------------------------------------------------------------
    // Récupère les infos d'un bureau identifié par son nom, si le nom du bureau existe dans la base
    // Pré: on suppose que le nom de bureau deskName existe dans la BD
    public function getDeskinfoFromDeskName_db($deskName)
    {
      global $error;
      $this->nom = $deskName;
      if ( open_gestionbureaux_db( $mysqli) == false)
      {
         $error = ERR_CANNOT_OPEN_DB;
         return false;
      }
 
      $query = 'SELECT id, estReservable, estPartageable FROM bureau WHERE nom="'.$deskName.'"';

      if ( ($res = $mysqli->query($query)) == false)
      {
          $error = ERR_MYSQLIQUERY;
	      return false;
      }
      elseif (($row = $res->fetch_assoc( )) == NULL)
      {
          $error = ERR_DESKNAME_NOT_FOUND_IN_DB;
          return false;
      }
      elseif (sizeof($row) == 0)
      {
          $error = ERR_DESKNAME_NOT_FOUND_IN_DB;
          return false;
      }
      else {
        $this->id = $row['id'];
        $this->estPartageable = $row['estPartageable'];
        $this->estReservable = $row['estReservable'];
      }
      $error = ERR_OK;
      return true;
    }
}

/*-------------------------------------------------------------------------------------------------
//
*/

class reservationbureau {
   public $id;
   public $idBureau;
   public $idDemandeur;
   public $idPersonne;
   public $dateReservation;
   public $debutReservation;
   public $finReservation;
   public $reservationconfirmee;
   public $dateConfirmation;
   public $reservationannulee;
   public $dateAnnulation;
   public $idPersonneannule;
   public $reservationterminee;

    //---------------------------------------------------
    // Affichage des informations concernant le bureau courant
    public function printBookingInfo()
    {
        print_r('   idBureau: '.$this->idBureau.'<BR>');
        print_r('   idDemandeur: '.$this->idDemandeur.'<BR>');
        print_r('   idPersonne: '.$this->idPersonne.'<BR>');
        print_r('   dateReservation: '.$this->dateReservation.'<BR>');
        print_r('   debutReservation: '.$this->debutReservation.'<BR>');
        print_r('   finReservation: '.$this->finReservation.'<BR>');
        print_r('   reservationconfirmee: '.$this->reservationconfirmee.'<BR>');
        print_r('   dateConfirmation: '.$this->dateConfirmation.'<BR>');
        print_r('   reservationannulee: '.$this->reservationannulee.'<BR>');
        print_r('   dateAnnulation: '.$this->dateAnnulation.'<BR>');
        print_r('   idPersonneannule: '.$this->idPersonneannule.'<BR>');
        print_r('   reservationterminee: '.$this->reservationterminee.'<BR>');
        print_r('<BR>');
    }

   // Récupère les informations d'une réservation pointée par son identifiant reservationId
   public function getInfoReservationFromId_db( $reservationId)
   {
      global $error;
      $this->id = $reservationId;
      if ( open_gestionbureaux_db( $mysqli) == false)
      {
         $error = ERR_CANNOT_OPEN_DB;
         return false;
      }
 
      $query = 'SELECT * FROM reservationbureau WHERE id="'.$reservationId.'"';

      if ( ($res = $mysqli->query($query)) == false)
      {
          $error = ERR_MYSQLIQUERY;
	      return false;
      }
      elseif (($row = $res->fetch_assoc( )) == NULL)
      {
         $error = ERR_BOOKINGID_NOT_FOUND_IN_DB;
         return false;
      }
      elseif (sizeof($row) == 0)
      {
          $error = ERR_BOOKINGID_NOT_FOUND_IN_DB;
          return false;
      }
      else
      {
        $this->id = $row['id'];
        $this->idDemandeur = $row['iddemandeur'];
        $this->idPersonne = $row['idpersonne'];
        $this->dateReservation = $row['datereservation'];
        $this->debutReservation = $row['debutreservation'];
        $this->finReservation = $row['finreservation'];
        $this->reservationconfirmee = $row['reservationconfirmee'];
        $this->dateConfirmation = $row['dateconfirmation'];
        $this->reservationannulee = $row['reservationannulee'];
        $this->dateAnnulation = $row['dateannulation'];
        $this->idPersonneAnnule = $row['idpersonneannule'];
        $this->reservationterminee = $row['reservationterminee'];
      }
      $error = ERR_OK;
      return true;
   }

}

/*-------------------------------------------------------------------------------------------------
//
*/
class personne
{
    public $id;
    public $nom;
    public $prenom;
    public $idemploi;

    public function getNomPrenomFromId_db($idPersonne)
    {
       global $error;
       if ( open_gestionbureaux_db( $mysqli) == false)
       {
         $error = ERR_CANNOT_OPEN_DB;
         return false;
       }

       $query = 'SELECT nom, prenom from personne where id="'.$idPersonne.'"';

      if ( ($res = $mysqli->query($query)) == false)
      {
          $error = ERR_MYSQLIQUERY;
	      return false;
      }
      elseif (($row = $res->fetch_assoc( )) == NULL)
      {
         $error = ERR_IDPERSONNE_NOT_FOUND_IN_DB;
         return false;
      }
      else
      {
        $this->id = $idPersonne;
        $this->nom = $row['nom'];
        $this->prenom = $row['prenom'];
      }
      return true;
    }

    public function getEmploiFromId_db($idPersonne)
    {
      if ( open_gestionbureaux_db( $mysqli) == false)
      {
         print_r('Cannot open database');
         return false;
      }
      $query = 'SELECT idemploi from lienpersonneemploi where idpersonne="'.$idPersonne.'"';
      if ( ($res = $mysqli->query($query)) == false)
	     return false;
      if (($row = $res->fetch_assoc( )) == NULL)
         return false;
      $this->idemploi = $row['idemploi'];
    }
}

/*-------------------------------------------------------------------------------------------------
//
*/
class salle
{
    public $id;
    public $nom;
    public $estReservable;

    //---------------------------------------------------
    // Affichage des informations concernant la salle courante
    public function printRoomInfo()
    {
        print_r('Salle '.$this->nom.'('.$this->id.'): ');
        if ($this->estReservable == 1)
           print_r('[RESERVABLE] ');
        else
           print_r('[NON RESERVABLE] ');
        print_r('<BR>');
    }
 

    //-------------------------------------------------------------
    // Récupère les infos d'une salle repérée par son identifiant roomId
    // Pré: on suppose que l'identifiant roomId existe dans la BD
    public function getRoomInfoFromRoomId_db($roomId)
    {
      global $error;
      $this->id = $roomId;
      if ( open_gestionbureaux_db( $mysqli) == false)
      {
         $error = ERR_CANNOT_OPEN_DB;
         return false;
      }
 
      $query = 'SELECT nom, estReservable FROM salle WHERE id="'.$roomId.'"';

      if ( ($res = $mysqli->query($query)) == false)
      {
          $error = ERR_MYSQLIQUERY;
	      return false;
      }
      elseif (($row = $res->fetch_assoc( )) == NULL)
      {
          $error = ERR_ROOMID_NOT_FOUND_IN_DB;
          return false;
      }
      elseif (sizeof($row) == 0)
      {
          $error = ERR_ROOMID_NOT_FOUND_IN_DB;
          return false;
      }
      else
      {
        $this->nom = $row['nom'];
        $this->estReservable = $row['estReservable'];
      }
      $error = ERR_OK;
      return true;
    }

    //-------------------------------------------------------------
    // Récupère les infos d'une salle identifiée par son nom, si le nom de la salle existe dans la base
    // Pré: on suppose que le nom de la salle roomName existe dans la BD
    public function getRoominfoFromRoomName_db($roomName)
    {
      global $error;
      $this->nom = $roomName;
      if ( open_gestionbureaux_db( $mysqli) == false)
      {
         $error = ERR_CANNOT_OPEN_DB;
         return false;
      }
 
      $query = 'SELECT id, estReservable FROM salle WHERE nom="'.$roomName.'"';

      if ( ($res = $mysqli->query($query)) == false)
      {
          $error = ERR_MYSQLIQUERY;
	      return false;
      }
      elseif (($row = $res->fetch_assoc( )) == NULL)
      {
          $error = ERR_ROOMNAME_NOT_FOUND_IN_DB;
          return false;
      }
      elseif (sizeof($row) == 0)
      {
          $error = ERR_ROOMNAME_NOT_FOUND_IN_DB;
          return false;
      }
      else {
        $this->id = $row['id'];
        $this->estReservable = $row['estReservable'];
      }
      $error = ERR_OK;
      return true;
    }

}

//------------------------------------------------------------------
//
//---------------------------------------------------------------------------------------------------------
// Donne l'idReservation de la réservation en cours à une date dateNow pour un bureau d'identifiant idDesk
// Pour un jour donné dateNow et un bureau donné d'identifiant deskId, remplit les informations sur la réservation associée
// Post: reservationExiste vaut 1 ssi il existe une réservation d'identifiant idReservation dans la BD à la date dateNow sur le bureau d'identifiant deskId.
function getIdReservationFromDateDeskId_db($dateNow, $deskId, &$reservationExiste, &$idReservation)
{
   global $error;
   if ( open_gestionbureaux_db( $mysqli) == false)
   {
      print_r('Cannot open database');
      $error = ERR_CANNOT_OPEN_DB;
      return false;
   }
   $query = 'SELECT r.id FROM reservationbureau r ,bureau b WHERE b.id = r.idbureau and b.id="'.$deskId.'" AND "'.$dateNow.'" BETWEEN r.debutreservation and r.finreservation';
   if ( ($res = $mysqli->query($query)) == false)
   {
       $error = ERR_MYSQLI_QUERY;
	   return false;
   }
   elseif (($row = $res->fetch_assoc( )) == NULL)
   {
       $reservationExiste = 0;
   }
   elseif (sizeof($row) == 0)
      $reservationExiste = 0;
   else
     {
      $reservationExiste = 1;
      $idReservation = $row['id'];
     }
   $error = ERR_OK;
   return true;
}

 // Retourne la liste des réservations émises par une personne donnée identifiée par idDemandeur
 // Post: nbBookingByIddemandeur est le nombre de réservations émises par idDemandeur; tblBookingByIddemandeur est la liste des identifiants des réservations émises par Iddemandeur
 function getAllBookingByIddemandeur($idDemandeur, &$nbBookingByIddemandeur, &$tblBookingByIddemandeur)
 {
     global $error;
    if ( open_gestionbureaux_db( $mysqli) == false)
    {
      $error = ERR_CANNOT_OPEN_DB;
      return false;
    }
    $query = 'SELECT id from reservationbureau WHERE iddemandeur="'.$idDemandeur.'"';
   if ( ($res = $mysqli->query($query)) == false)
   {
       $error = ERR_MYSQLI_QUERY;
	   return false;
   }
    $i = 0;
    while (($row = $res->fetch_assoc( )) != NULL)
    {
        $tblBookingByIddemandeur[$i] = $row['id'];
        $i++;
    }
    $nbBookingByIddemandeur = $i;
    $error = ERR_OK;
    return true;
}

//Retounre la liste des nbDesk identifiants de bureaux tblDeskId dela salle roomName 
function getTblDeskIdFromRoomName_db($roomName, &$nbDesk, &$tblDeskId)
{
   global $error;
   if ( open_gestionbureaux_db( $mysqli) == false)
   {
      $error = ERR_CANNOT_OPEN_DB;
      return false;
   }
   $query = 'SELECT b.id FROM `bureau` b, `salle`s, `lienbureausalle` lbs WHERE (s.nom="'.$roomName.'") AND (lbs.idbureau = b.id) and (lbs.idsalle = s.id)';
   if ( ($res = $mysqli->query($query)) == false)
   {
       $error = ERR_MYSQLIQUERY;
	   return false;
   }
   $i = 0;
   while (($row = $res->fetch_assoc( )) != NULL)
   {
       $tblDeskId[ $i] = $row['id'];
       $i++;
   }
   $nbDesk = $i;
   return true;
}

// Pour un jour donné, récupère l'ensemble des identifiants des bureaux libres du parc de bureaux gérés
function getAllFreeDesksFromDate_db($dateNow, &$nbFreeDesks, &$tblFreeDesks)
{
   global $error;
   if ( open_gestionbureaux_db( $mysqli) == false)
   {
      $error = ERR_CANNOT_OPEN_DB;
      return false;
   }
   $query = 'SELECT id FROM bureau WHERE estReservable=1'; // Liste tous les bureaux réservables

   if ( ($res = $mysqli->query($query)) == false)
   {
       $error = ERR_MYSQLIQUERY;
	   return false;
   }
   $i = 0;
   while (($row = $res->fetch_assoc( )) != NULL)
   {
       $deskId = $row['id'];
       if ($this->getIdReservationFromDateDeskId_db($dateNow, $deskId, $isDeskFree) == false)
       {
         print_r('*** ERROR!!! getIdReservationFromDateDeskId_db() returns false<BR>');
       }
          if ($isDeskFree == 1)
          {
              $tblFreeDesks[$i] = $deskId;
              $i++;
          }
          
      }
      $nbFreeDesks = $i;
    $error = ERR_OK;
    return true;
}

//------------------------------------------------------------------
// Tests

// Info d'un bureau connaissant son Id
function testInfoBureauParId($idBureau)
{
    print_r('Test information bureau par Id:<BR>');
    $desk = new bureau();
    print_r('   idBureau: '.$idBureau.'<BR>');
    if ($desk->getDeskInfoFromDeskId_db($idBureau) == false)
       printError();
    else
       $desk->printDeskInfo();
    print_r('OK<BR><BR>');
    return true;
}

// Etat d'un bureau connaissant son nom
function testInfoBureauParNom($nomBureau)
{
    print_r('Test information bureau par nom:<BR>');
    $desk = new bureau();
    print_r('   nomBureau: '.$nomBureau.'<BR>');
    if ($desk->getDeskInfoFromDeskName_db($nomBureau) == false)
       printError();
    else
	    $desk->printDeskInfo();

    print_r('OK<BR><BR>');
    return true;
}

// Informations reservationbureau par bookingid
function testInfoReservationbureauParBookingId($bookingid)
{
    print_r('Test information réservation bureau par reservationId:<BR>');
    $reservation = new reservationbureau();
    print_r('   Id réservation: '.$bookingid.'<BR>');
    if ($reservation->getInfoReservationFromId_db($bookingid) == false)
       printError();
    else
       $reservation->printBookingInfo();

    print_r('OK<BR><BR>');
    return true;
}

// Donne les caractéristiques de la réservation sur un bureau d'identifint deskId à une date dateNow
function testInfoReservationParDateDeskId($dateNow, $deskId)
{
    print_r('Test information réservation bureau et date donnés:<BR>');
    print_r('   date: '.$dateNow.', idBureau: '.$deskId.'<BR>');
    if (getIdReservationFromDateDeskId_db($dateNow, $deskId, $reservationExiste, $idReservation) == false)
       printError();
    else {
       if ($reservationExiste == 0)
          print_r('AUCUNE RESERVATION<BR>');
       else
       {
          print_r('Réservation '.$idReservation.'<BR>' );
          $reservation = new reservationbureau();
          if ($reservation->getInfoReservationFromId_db($idReservation) == false)
             printError();
          else
             $reservation->printBookingInfo();
       }
    }
    print_r('OK<BR><BR>');
    return true;   
}

// Donne la liste identifiants des réservations effectuées par un demandeur
function testBookingListByIddemandeur($iddemandeur)
{
    print_r('Test liste des demandes effectuées par '.$iddemandeur.'<BR>');
    if (getAllBookingByIddemandeur($iddemandeur, $nbBookingByIddemandeur, $tblBookingByIddemandeur) == false)
       printError();
    else {
	   print_r('    Nombre de demandes: '.$nbBookingByIddemandeur.'<BR>');
       if ($nbBookingByIddemandeur > 0)
          for ($i=0;$i<$nbBookingByIddemandeur;$i++)
             print_r('     '.$tblBookingByIddemandeur[$i].'<BR>');
   }
   print_r('OK<BR><BR>');
   return true;
}

// Infos d'une salle connaissant son Id
function testInfoSalleParId($idSalle)
{
    print_r('Test information salle par Id:<BR>');
    $room = new salle();
    print_r('   idSalle: '.$idSalle.'<BR>');
    if ($room->getRoomInfoFromRoomId_db($idSalle) == false)
       printError();
    else
       $room->printRoomInfo();
    print_r('OK<BR><BR>');
    return true;
}

// Etat d'un bureau connaissant son nom
function testInfoSalleParNom($nomSalle)
{
    print_r('Test information salle par nom:<BR>');
    $room = new salle();
    print_r('   nomSalle: '.$nomSalle.'<BR>');
    if ($room->getRoomInfoFromRoomName_db($nomSalle) == false)
        printError();
    else
	    $room->printRoomInfo();

    print_r('OK<BR><BR>');
    return true;
}

// Donne les nom, prénom et emploi d'une personne d'identifiant id Personne
function testNomPrenomEmploiFromIdpersonne($idPersonne)
{
    print_r('Test nom, prénom et emploi à partir de idPersonne<BR>');
    $personne = new personne();
    if ($personne->getNomPrenomFromId_db($idPersonne) == false)
       printError();
    else
    {
	   
    }


}

function testAllDesksByRoomName($nomSalle)
{
    print_r('Test ensemble des bureaux dans la salle de nom nomSalle:<BR>');
    $room = new salle();
    print_r('   nomSalle: '.$nomSalle.'<BR>');
    if (getTblDeskIdFromRoomName_db($nomSalle, $nbDesk, $tblDeskId) == false)
       printError();
    else
    {
        print_r('      '.$nbDesk.' bureaux<BR>');
        for ($i = 0; $i<$nbDesk;$i++)
           print_r('        '.$tblDeskId[$i].'<BR>');
    }

    print_r('OK<BR><BR>');
    return true;
}

//------------------------------------------------------------------
// In: date, deskname, deskid, roomname, roomid, bookingid, iddemandeur
//
   $date = $_POST['date'];
   $deskId = $_POST['deskid'];
   $deskName = $_POST['deskname'];
   $bookingId = $_POST['bookingid'];
   $roomId = $_POST['roomid'];
   $roomName = $_POST['roomname'];
   $idDemandeur = $_POST['iddemandeur'];

   echo '<pre>';
   testInfoBureauParId($deskId);
   testInfoBureauParNom($deskName);
   testInfoSalleParId($roomId);
   testInfoSalleParNom($roomName);
   testAllDesksByRoomName($roomName);
   testInfoReservationbureauParBookingId($bookingId);
   testInfoReservationParDateDeskId($date, $deskId);
   testBookingListByIddemandeur($idDemandeur);
   echo '</pre>';

/*   $room = new salle();
   $room->getInfoFromName_db($roomName);
   $per = new personne();

   if ($desk->etat == 'réservable')
   {
      $reservation = new reservationbureau();
      $reservation->getIdFromDateDesk_db($date,$desk->id, $isDeskFree);
      if ($isDeskFree == 1)
         print_r('   LIBRE');
      elseif (($isDeskFree == 0) & ($reservation->reservationConfirmee == 1))
         print_r('   OCCUPE');
      else
         print_r('   RESERVATION EN ATTENTE DE CONFIRMATION');

   }
  
   print_r('<BR>');
   print_r('Salle '.$roomName.': ');
   if ($room->estReservable == 1)
   {
      print_r(' réservable<BR>');
      $room->getTblDeskIdFromName_db($roomName, $nbDesk, $tblDeskId);
      if ($nbDesk == 0)
      {
         print_r('*** ERREUR!!! incohérent, salle sans bureau.');
         return;
      }
      else
      {
	     print_r($nbDesk.' bureaux<BR>');
         for ($i=0;$i<$nbDesk;$i++)
         {
           $desk->getInfoFromId_db($tblDeskId[$i]);
           print_r('    Bureau: '.$desk->nom.' ('.$desk->etat.')');
           if ($desk->etat == 'réservable')
           {
              $reservation = new reservationbureau();
              $reservation->getIdFromDateDesk_db($date,$tblDeskId[$i], $isDeskFree);
              if ($isDeskFree == 1)
                 print_r('   LIBRE');
              elseif (($isDeskFree == 0) & ($reservation->reservationConfirmee == 1))
              {   
                  print_r('   OCCUPE (');
                  $per->getNomPrenomFromId_db($reservation->idPersonne);
                  $per->getEmploiFromId_db($reservation->idPersonne);
                  print_r($per->nom.', '.$per->prenom.':'.$per->idemploi.')');
              }
              else
              {
                 print_r('   RESERVATION EN ATTENTE DE CONFIRMATION (');
                 $per->getNomPrenomFromId_db($reservation->idPersonne);
                 print_r($per->nom.', '.$per->prenom.')');
              }
            }
            print_r('<BR>');

         }
      }
    }
    else
    {
        print_r(' non réservable<BR>');
    }

    print_r('<BR>');
    $reservation = new reservationbureau();
    if ($reservation->getAllFreeDesksFromDate_db($date, $nbFreeDesks, $tblFreeDesks) == false)
    {
        print_r('!!! BEURK<BR>');
        return;
    }
    print_r('Nombre de bureaux libres le '.$date.': '.$nbFreeDesks.'<BR>');
    for ($i=0;$i<$nbFreeDesks;$i++)
    {
        print_r('   '.$tblFreeDesks[$i].'<BR>');
    }

    if ($reservation->getAllBookingByIdpersonne($idDemandeur, $nbBookingByIddemandeur, $tblBookingByIddemandeur) == false)
    {
        print_r('!!! BEURK<BR>');
        return;
    }
    print_r('<BR>Nombre de réservations effectuées par '.$idDemandeur.': '.$nbBookingByIddemandeur.'<BR>');
    for ($i=0;$i<$nbBookingByIddemandeur;$i++)
       print_r('    '.$tblBookingByIddemandeur[$i].'<BR>');

       */
?>
</body>
</html>