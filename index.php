<?php

require_once 'lib/dompdf/autoload.inc.php';
require_once 'lib/twig/autoload.inc.php';

use Dompdf\Dompdf;


///////////////
// Functions
///////////////

function getDompdf(): Dompdf {
    $options = new \Dompdf\Options();
    $options->set('chroot', 'assets');
    return new Dompdf($options);
}

function getTwig(): \Twig\Environment {
    $loader = new \Twig\Loader\FilesystemLoader('templates');
    $twig = new \Twig\Environment($loader, ['cache' => false]);
    return $twig;
}

function getData(): array
{
    $data = [
        'documentTitle' => "Attestation d'assiduité de formation",
        'ofUserName' => 'Vincent BARRIER',
        'ofName' => 'Kagilum',
        'traineeFirstName' => 'Jane',
        'traineeLastName' => 'BIRKIN',
        'trainingName' => 'Professionnel Scrum Certifié',
        'trainingStartDate' => '15/02/2021',
        'trainingEndDate' => '16/02/2021',
        'trainingDuration' => 14
    ];
    $data['traineeName'] = $data['traineeFirstName'] . ' ' . $data['traineeLastName'];
    $data['signatureDate'] = $data['trainingEndDate'];
    return $data;
}

function getHtml(\Twig\Environment $twig, array $templates, array $data): string
{
    return $twig->render('index.html', array_merge($data, $templates));
}

function generatePdf(Dompdf $dompdf, string $html)
{
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4');
    $dompdf->render();
    $dompdf->stream();
}


///////////////
// Main
///////////////

$twig = getTwig();
$dompdf = getDompdf();

$data = getData();

$templates = [
    'headerTemplate' => 'header.html',
    'footerTemplate' => 'footer.html',
    'bodyTemplate' => 'assiduite.html'
];

$html = getHtml($twig, $templates, $data);

generatePdf($dompdf, $html);

?>
