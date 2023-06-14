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


function modifierReservation(&$mysqli, $new_date_reservation,$id_reservation,$date_debut,$date_fin,$confirmation) {
    if (open_reservation_db($mysqli) == false) {
        echo "Cannot open database";
        return false;
    }

    $query_check = 'UPDATE reservations
    SET date_reservation = "' . $new_date_reservation . '",date_debut = "'.$date_debut .'",date_fin = "'.$date_fin .'",confirmation = "'.$confirmation .'"
    WHERE id_reservation = "' . $id_reservation . '"';

    $result_check = $mysqli->query($query_check);

    if (!$result_check) {
        echo "Une erreur s'est produite lors de la modification de la réservation : " . $mysqli->error;
        return false;
    }

   /* if ($result_check->num_rows > 0) {
        echo "La réservation existe déjà.";
        return false;
    } else {
        $query_check = 'UPDATE reservations  (id_bureau,  id_pers_create, id_beneficiaire, date_reservation,id_salle)
                         VALUES ("' . $id_bureau . '", "' . $id_create . '", "' . $id_beneficiaire . '", "' . $date_reservation . '","'.$id_salle.'")';
*/
        if ($mysqli->query( $query_check) === TRUE) {
            echo "La réservation a été modifier avec succès.";
            return true;
        } else {
            echo "Une erreur s'est produite lors de l'insertion de la réservation : " . $mysqli->error;
            return false;
        }
    }
//}
//------------------------------------------------------------------------------

$id_reservation = $_POST['id_reservation'];
$new_date_reservation = $_POST['new_date_reservation'];
$date_debut = $_POST['date_debut'];
$date_fin= $_POST['date_fin'];
$confirmation= $_POST['confirmation'];
   
  print_r( 'id_reservation ='.$id_reservation.' ,new_date_reservation = '. $new_date_reservation.' <BR>');
 
  if ( modifierReservation($mysqli, $new_date_reservation,$id_reservation,$date_debut,$date_fin,$confirmation)) {
    echo "La réservation a été modifier avec succès.";
} else {
   echo "Une erreur s'est produite .";
}