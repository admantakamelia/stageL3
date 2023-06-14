<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
            "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
  <title>Informations sur l'état d'un bureau</title>
  <meta name="Description" content="Gestion des ressources IBISC">
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
</head>
<body style="font-family:calibri,verdana">
<?php
  
//-------------------------------------------------------------

function open_reservation_db( &$mysqli)
{
   $mysqli = new mysqli('127.0.0.1', 'root', '', 'reservation', 3308);

   // Oh non ! Une connect_errno existe donc la tentative de connexion a échoué !
   if ($mysqli->connect_errno)
   {
      print_r('Cannot connect to database.<BR>');
      return false;
   }

   return true;
}

//-------------------------------------------------------------


function createReservation(&$mysqli, $id_bureau, $id_create, $id_beneficiaire, $id_salle, $date_reservation) {
    if (open_reservation_db($mysqli) == false) {
        echo "Cannot open database";
        return false;
    }

    $query_check = 'SELECT * FROM reservations r
    inner join salles s on s.id_salle = r.id_salle 
    inner join bureau b on b.id_bureau=r.id_bureau 
    join utilisateur u on u.id_utilisateur =r.id_pers_create
    join personne p on p.id_personne = r.id_beneficiaire
                    WHERE b.id_salle = s.id_salle
                    and  r.id_bureau = "' . $id_bureau . '" 
                    AND r.id_salle = "' . $id_salle . '" 
                    AND r.id_pers_create = "' . $id_create . '" 
                    AND r.id_beneficiaire = "' . $id_beneficiaire . '"
                    and r.date_reservation= "' .$date_reservation .'"';

    $result_check = $mysqli->query($query_check);

    if (!$result_check) {
        echo "Une erreur s'est produite lors de la vérification de la réservation : " . $mysqli->error;
        return false;
    }

    if ($result_check->num_rows > 0) {
        echo "La réservation existe déjà.";
        return false;
    } else {
        $query_check = 'INSERT INTO reservations (id_bureau,  id_pers_create, id_beneficiaire, date_reservation,id_salle)
                         VALUES ("' . $id_bureau . '", "' . $id_create . '", "' . $id_beneficiaire . '", "' . $date_reservation . '","'.$id_salle.'")';

        if ($mysqli->query( $query_check) === TRUE) {
            echo "La réservation a été insérée avec succès.";
            return true;
        } else {
            echo "Une erreur s'est produite lors de l'insertion de la réservation : " . $mysqli->error;
            return false;
        }
    }
}
//------------------------------------------------------------------------------
$id_bureau = $_POST['id_b'];
  $date_reservation = $_POST['date_res'];
  $id_create = $_POST['id_create'];
  $id_beneficiaire = $_POST['id_benificiaire'];
  $id_salle = $_POST['id_salle'];


   

   
  print_r( 'date reservation ='.$date_reservation.' , la personne qui la crée ='.$id_create.' ,la personne benificiaire = '. $id_beneficiaire.' , id_bureau= '.$id_bureau.'<BR>');
 
  if (createReservation($mysqli,$id_bureau, $id_create, $id_beneficiaire,$id_salle,  $date_reservation)) {
    echo "La réservation a été insérée avec succès.";
} else {
   echo "Une erreur s'est produite .";
}