<?php

require_once 'dompdf/autoload.inc.php';

use Dompdf\Dompdf;
use Dompdf\Options;

function utilGetUpperCase(string $string): string {
    return str_replace(
      array('é', 'è', 'ê', 'ë', 'à', 'â', 'î', 'ï', 'ô', 'ù', 'û'),
      array('É', 'È', 'Ê', 'Ë', 'À', 'Â', 'Î', 'Ï', 'Ô', 'Ù', 'Û'),
      strtoupper($string)
   );
}

function getCss()
{
    return '
       @page {
           margin: 100px;
       }
       @font-face { 
           font-family: poppins-regular; 
           font-weight: normal; 
           font-style: normal; src: url("dompdf/lib/fonts/poppins-regular.ttf") format("truetype"); 
       }       
       @font-face { 
           font-family: poppins-bold; 
           font-weight: normal; 
           font-style: normal; src: url("dompdf/lib/fonts/poppins-bold.ttf") format("truetype"); 
       }       
       @font-face { 
           font-family: poppins-italic; 
           font-weight: normal; 
           font-style: normal; src: url("dompdf/lib/fonts/poppins-italic.ttf") format("truetype"); 
       }
       body {
          font-family: poppins-regular;
          font-size: 14px;
          line-height: 14px;
       }
       b, strong {
          font-family: poppins-bold;
       }
       i, em {
          font-family: poppins-italic;
       }
       h1 {
          margin-top: 100px;
          text-align: center;
          margin-bottom: 150px;
       }
       h1, h2, h3, h4, h5, h6 {
          font-family: poppins-bold;
       }
       .signature {
          margin-top: 150px;
          float: right;
       }
       .dynamic {
          font-family: poppins-bold;
       }
       .logo {
            width: 150px;
       }
       .documentTitle {
            font-size: 12px;
            float:right;
       }
       .header {
           margin-top: -60px;
       }
       .footer {
           font-size: 12px;
           text-align: center;
           position: fixed; 
           bottom: -60px; 
           left: 0px; 
           right: 0px;
           height: 50px; 
       }
   ';
}

function getHeader($documentTitle)
{
    return '
<div class="header">   
    <div class="documentTitle">' . $documentTitle .'</div>
    <img class="logo" src="assets/images/logo-ws.png"/>
</div>
    ';
}

function getFooter()
{
    return '
<div class="footer">
    Kagilum SAS au capital social de 9000€ – Siège social : 8 impasse bonnet, 31500 Toulouse RCS Toulouse B 532 222 924 – N TVA FR 90 532222924
</div>
    ';
}

function getBody()
{
    $documentTitle = "Attestation d'assiduité de formation";
    $ofPersonName = 'Vincent BARRIER';
    $of = 'Kagilum';
    $traineeFirstName = 'Jane';
    $traineeLastName = 'BIRKIN';
    $traineeName = $traineeFirstName . ' ' . $traineeLastName;
    $trainingName = 'Professionnel Scrum Certifié';
    $trainingStartDate = '15/02/2021';
    $trainingEndDate = '16/02/2021';
    $todayDate = $today = date("j/m/Y");;
    $trainingDuration = 14;
    return '
' . getHeader($documentTitle) . '
<h1>' . utilGetUpperCase($documentTitle) . '</h1>

<p>
    Je soussigné <span class="dynamic">' . $ofPersonName . '</span>, représentant légal de l’organisme de formation  <span class="dynamic">' . $of . '</span>, atteste que  <span class="dynamic">' . $traineeName . '</span>, a bien suivi l’action de formation <span class="dynamic">' . $trainingName . ' : </span>
</p>
<ul>
    <li>qui s’est déroulée sur la période du <span class="dynamic">' . $trainingStartDate . '</span> au <span class="dynamic">' . $trainingEndDate . ',</span></li>
    <li>avec une durée de  <span class="dynamic">' . $trainingDuration . '</span> heures.</li>
</ul>

<div class="signature">
<p>
L’organisme de formation<br/>
Le <span class="dynamic">' . $todayDate . '</span><br/>
<span class="dynamic">' . $ofPersonName . '</span> 
</p>

<p>
Cachet Signature
</p>
</div>
' . getFooter() . '
    ';
}

function getHtml()
{
    return '
<!DOCTYPE html>
<html>
<head>
<style>
' . getCss() . '
</style>
</head>
<body>
' . getBody() . '
</body>
</html>';
}

$html = getHtml();

$options = new Options();
$options->set('chroot', 'assets');

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4');
$dompdf->render();

//echo $html;
$dompdf->stream();

?>
