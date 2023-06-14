<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
            "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
  <title>Informations sur l'état d'un bureau</title>
  <meta name="Descriptions" content="Gestion des ressources IBISC">
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
</head>
<body style="font-family:calibri,verdana">
<?php
include 'informations_etat_bureau_db.php';

function bureaux_from_sameRoom($mysqli, $nomSalle, $Date, &$bureaux)
{
  if (open_reservation_db($mysqli) == false)
  {
      echo 'Cannot open database';
      return false;
  }
 
  $query = 'SELECT s.nom AS salle_nom, GROUP_CONCAT(b.nom) AS bureaux
            FROM bureau b
            JOIN salles s ON b.id_salle = s.id_salle
            WHERE s.nom = "'.$nomSalle.'"';

  $stmt = $mysqli->prepare($query);
  if (!$stmt) {
    echo 'Error in query preparation';
    return false;
  }

  /*$stmt->bind_param('s', $nomSalle ,$date,$bureaux);
  $stmt->execute();
  $result = $stmt->get_result();*/

  /*if (!$result) {
    echo "Erreur lors de l'exécution de la requête : " . $mysqli->error;
    return false;
  }*/if ( ($res = $mysqli->query($query)) == false)
	return false;
   $row = $res->fetch_assoc( );
   if ($row === null) {
      return false;
  }

  $data = array();

  

  //while ($row = $result->fetch_assoc()) {
   $nomSalle = $row['salle_nom'];
   $bureaux = explode(',', $row['bureaux']);

   if (!isset($data[$nomSalle])) {
       $data[$nomSalle] = array();
   }

   foreach ($bureaux as $bureau) {
       $data[$nomSalle][] = array('bureau_nom' => $bureau, 'etat' => ''); // Ajoutez chaque bureau dans le tableau $data avec un état initial vide
   }
//}

$resultat_final = '';

foreach ($data as $nomSalle => $bureaux) {
   $resultat_final .= "Salle : " . $nomSalle . "<BR>\n";

   foreach ($bureaux as &$bureauItem) {
       $bureau = $bureauItem['bureau_nom'];
       $etat = getDeskStatefromName($mysqli, $bureau, $Date, $deskState);
       //print_r ("etat = " .$$deskState);
       $bureauItem['etat'] = $deskState;
       $resultat_final .= "- Bureau : " . $bureau . ",<BR> État : " .$deskState . "<BR>\n";
   }


  if (empty($data)) {
    echo 'No data found for the provided room name';
    return false;
  }

  echo $resultat_final;
  return true;
}
}


  //--------------
$nomSalle = $_POST['sallename'];
$date=$_POST['date'];
$Date=$_POST['Date'];
if (strlen($nomSalle) == 0) {
  echo '*** salle Name is empty !<BR>';
  exit;
}

echo 'sallename=' . $nomSalle . '<BR>';

//$date = ''; // you should provide the value for $date

if (!bureaux_from_sameRoom($mysqli, $nomSalle, $Date, $bureaux)) {
  echo "Une erreur s'est produite lors de la récupération de l'état du bureau.";
}

?>
</body>
</html>