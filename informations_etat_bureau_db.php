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
   $mysqli = new mysqli('127.0.0.1', 'root', '', 'reservation', 3306);

   // Oh non ! Une connect_errno existe donc la tentative de connexion a échoué !
   if ($mysqli->connect_errno)
   {
      print_r('Cannot connect to database.<BR>');
      return false;
   }

   return true;
}

//-------------------------------------------------------------

function getDeskIdfromName( &$mysqli, $deskName, &$deskId)
{
  if ( open_reservation_db( $mysqli) == false)
  {
      print_r('Cannot open database');
      return false;
  }
 
   $query = 'SELECT id_bureau FROM bureau 
    WHERE nom="'.$deskName.'"';
  //print_r('query='.$query.'<BR>');

   if ( ($res = $mysqli->query($query)) == false)
	return false;
   $row = $res->fetch_assoc( );
   if ($row === null) {
      return false;
  }
   $deskId = $row['id_bureau'];
   //print_r('deskId = '.$deskId.'<BR>');
   if (strlen($deskId) == 0)
      return false;
 
   return true;
}
//-------------------------------------------------------------
function bureau_estreservable( &$mysqli, $deskName, &$reservable)
{
  if ( open_reservation_db( $mysqli) == false)
  {
      print_r('Cannot open database');
      return false;
  }
 
   $query = 'SELECT reservable
   from bureau 
   where nom="'.$deskName.'" ';
  //print_r('query='.$query.'<BR>');

   if ( ($res = $mysqli->query($query)) == false)
	return false;
   $row = $res->fetch_assoc( );
   if ($row === null) {
      return false;
  }
   
   $reservable = $row['reservable'];
  
   if (strlen($reservable) == 0)
      return false;
      
   return true;
}


//-------------------------------------------------------------



//--------------------------------------
//-------------------------------------------------------------


function  isItOnReservation( &$mysqli, $deskName,$date,&$deskId,&$estReserve)
{
  if ( open_reservation_db( $mysqli) == false)
  {
      print_r('Cannot open database');
      return false;
  }
  $query = 'SELECT r.id_bureau
  FROM reservations r
  JOIN bureau b ON b.id_bureau = r.id_bureau
    WHERE b.nom = "'.$deskName.'" 
          and "'.$date.'" BETWEEN r.date_debut AND r.date_fin';

if ( ($res = $mysqli->query($query)) == false)
return false;
$row = $res->fetch_assoc( );
if ($row === null) {
   return false;
}
$deskId = $row['id_bureau'];
//print_r('deskId = '.$deskId.'<BR>');
//$reservable = $row['reservable'];
  
if (strlen($deskId) == 0)
$estReserve = 0;
else
$estReserve = 1;
return true;
}
//-------------------------------------------------------------
function DeskIsItConfirm ( &$mysqli, $deskName, &$confirmation)
{
  if ( open_reservation_db( $mysqli) == false)
  {
      print_r('Cannot open database');
      return false;
  }
 
   $query = 'SELECT r.confirmation
   from reservations r
   join bureau b on b.id_bureau = r.id_bureau
   where b.nom="'.$deskName.'" ';
   
   if ( ($res = $mysqli->query($query)) == false)
	return false;
   $row = $res->fetch_assoc( );
   if ($row === null) {
      return false;
  }
   $confirmation =$row['confirmation'];
   
   if (strlen($confirmation) == 0){
      return false;}
      
   return true;
}
//-------------------------------------------------------------

function getDeskStatefromName(&$mysqli, $deskName, $date, &$deskState){
   if (getDeskIdfromName($mysqli, $deskName, $deskId) == false) {
       $deskState = "n'existe pas";
       return true;
   }
   if (bureau_estreservable($mysqli, $deskName, $reservable) == false) {
      print_r("une erreur s'est produite");
      return false;
   }
   if ($reservable == 0)
   {
      $deskState = "non réservable";
      return true;
   }
   if (isItOnReservation($mysqli, $deskName,$date, $deskId,$estReserve) ==false) {
      //print_r("une erreur s'est produite");
     // return false;
   //}
   if ($estReserve == 0)
   {
      $deskState = "non réservé";
      return true;
   }
}
   if (DeskIsItConfirm ($mysqli, $deskName, $confirmation) ==false) {
     print_r("une erreur s'est produite");
      return false;
   }
  if ($confirmation ==0 ) {
   $deskState = "non confirmé";
   return true;
}
 
   $deskState = "confirmé";

return true;
}
//------------------------------------------------
/*function bureaux_from_sameRoom( &$mysqli, $nomSalle, &$bureaux)
{
  if ( open_reservation_db( $mysqli) == false)
  {
      print_r('Cannot open database');
      return false;
  }
 
  $query = 'SELECT  b.nom 
  FROM bureau b
  JOIN salles s ON b.id_salle = s.id_salle
  WHERE s.nom = "'.$nomSalle.'"';
  //print_r('query='.$query.'<BR>');

   if ( ($res = $mysqli->query($query)) == false)
	return false;
   $row = $res->fetch_assoc( );
  
   if ($row === null) {
      return false;
  }
  
$result = mysqli_query($mysqli, $query); // Remplacez $connection par votre objet de connexion à la base de données

// Vérification des erreurs lors de l'exécution de la requête
if (!$result) {
    die("Erreur lors de l'exécution de la requête : " . mysqli_error($mysqli));
}

// Récupération des données dans un tableau associatif
$data = array();
while ($row = mysqli_fetch_assoc($result)) {
    //$nomSalle = $row['salle_nom'];
    $bureaux = $row['nom'];

    // Ajouter les données dans le tableau
    if (!isset($data[$nomSalle])) {
        $data[$nomSalle] = array();
    }
   $data[$nomSalle][] = $bureaux;
}

// Manipulation des données pour obtenir une représentation en chaîne de caractères
$resultat_final = '';
foreach ($data as $nomSalle => $bureaux) {
    $resultat_final .= "Salle : " . $nomSalle .  "\n";
    $resultat_final .= "=> Bureaux : " . implode(", ", $bureaux) . "\n\n <BR>";
}

 // $bureaux =$row['nom'];
  if (($bureaux) == 0){
   return false;}
   // Afficher le résultat final
echo $resultat_final;
return true;
}*/


//------------------------------------------------------------------
// In: year, title
//
  $date = $_POST['date'];
  $deskName = $_POST['deskname'];

   if ( strlen( $deskName) == 0)
   {
      print_r('*** Desk Name is empty !<BR>');
      exit;
   }

   if ( strlen( $date) == 0)
   {
      print_r('*** Date is empty !<BR>');
      exit;
   }
  print_r('deskName='.$deskName.', date='.$date.'<BR>');
 
  if (getDeskStatefromName($mysqli, $deskName, $date, $deskState)) {
   echo "État du bureau : " . $deskState;
} else {
   echo "Une erreur s'est produite lors de la récupération de l'état du bureau.";
}
//---------------------------------------------------------------------------------------------

//exécution:

/*$nomSalle = $_POST['sallename'];
   

if ( strlen( $nomSalle) == 0)
{
   print_r('*** salle Name is empty !<BR>');
   exit;
}


print_r('sallename='.$nomSalle.'<BR>');

if (bureaux_from_sameRoom($mysqli, $nomSalle, $bureaux)) {
// echo "les bureaux : " . $bureaux;
return true;
} else {
echo "Une erreur s'est produite lors de la récupération de l'état du bureau.";
}*/
 //-----------------------------------------------------

   

?>
</body>
</html>