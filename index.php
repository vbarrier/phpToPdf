<?php

require_once 'lib/dompdf/autoload.inc.php';
require_once 'lib/twig/autoload.inc.php';

use Dompdf\Dompdf;

define('DATE_FORMAT_LONG', 'd/m/Y');
define('DATE_FORMAT_MEDIUM', 'd/m/y');
define('HOUR_FORMAT', 'H:i');

///////////////
/// Dummy data
///////////////

// TODO remove
function getDummyDataFinFormation(): array
{
    $dummyData = [
        'trainee' => getDummyTrainees()[0],
        'trainers' => getDummyTrainers(),
        'training' => [
            'remote' => false,
            'name' => "Professionnel Scrum Certifié agilité à l'échelle - SAFe PO/PM",
            'startDate' => date(DATE_FORMAT_LONG, strtotime('2021-02-15')),
            'endDate' => date(DATE_FORMAT_LONG, strtotime('2021-02-16')),
            'duration' => 14,
            'location' => 'Paris',
            'objectives' => [
                "Savoir identifier des User Stories fonctionnelles à partir des besoins utilisateurs",
                "Connaître les principes, méthodes et techniques Agiles",
                "Définir les critères d’acceptation d’une User Story",
                "Savoir constituer un Backlog de produit utilisable",
                "Planifier la production de valeur des incréments au cours des Sprints",
                "Acquérir les compétences pour gérer et motiver les équipes",
                "Aider à la collaboration entre acteurs du projet",
                "Faciliter le travail de l’équipe et du PO",
                "Savoir animer les différentes cérémonies Scrum",
            ]
        ]
    ];
    return $dummyData;
}

// TODO remove
function getDummyTraining(): array
{
    return [
        'days' => ['2021-06-28', '2021-06-29'],
//        'name' => "Professionnel Scrum Certifié agilité à l'échelle - Leading SAFe",
//        'name' => "Facilitation Graphique",
        'name' => "Formation Professionnel Scrum Certifié",
//        'name' => "Formation Professionnel Scrum Certifié SAFe Scrum Master",
//        'name' => "Scrum Master Avancé",
        'location' => 'À distance',
//        'location' => 'Paris',
//        'location' => 'Toulouse',
//        'location' => 'Bordeaux',
//        'location' => 'Nantes',
    ];
}

// TODO remove
// Example trainers: Min 1, Max 7
function getDummyTrainers(): array
{
    return [
//        ['firstName' => 'Benjamin', 'lastName' => 'CABANNE']
        ['firstName' => 'Marie', 'lastName' => 'FEDERICI']
//        ['firstName' => 'Gaël', 'lastName' => 'MOUSSAOUI']
//        ['firstName' => 'Valentine', 'lastName' => 'OGIER-GALLAND']
//        ['firstName' => 'Patrice', 'lastName' => 'FORNALIK']
//        ['firstName' => 'Vincent', 'lastName' => 'BARRIER']
//        ['firstName' => 'Cédric', 'lastName' => 'BODIN']
//        ['firstName' => 'Nicolas', 'lastName' => 'NOULLET']
    ];
}

// TODO remove
// Example trainees: Min 1, no max
function getDummyTrainees(): array
{
    return [
//        ['firstName' => 'John', 'lastName' => 'DOE', 'signatureFile' => 'signature1.png'],
        ['firstName' => 'John', 'lastName' => 'DOE'],
    ];
}

// TODO remove
function getDummyOf(): array
{
    return [
        'name' => 'Kagilum',
        'number' => '73310646031',
        'region' => 'Occitanie',
        'representative' => ['firstName' => 'Vincent', 'lastName' => 'Barrier', 'status' => 'Président'],
    ];
}

function getFooterText(): string
{
    return 'Kagilum SAS – Pôle formations Wensei – Capital social 9901€ – Agrément de formation 73310646031 – Siège social 8 impasse bonnet 31500 Toulouse – Tel 09.52.91.10.10 – Email formations@wensei.com – RCS Toulouse B 532222924 – TVA FR90532222924 – Code APE 6201Z';
}

///////////////
/// Data generation Fin Formation
///////////////

function getDataFinFormation(): array
{
    $dummyData = getDummyDataFinFormation(); // TODO plug in real data
    $data = [
        'trainee' => $dummyData['trainee'], // TODO plug in real data
        'trainers' => $dummyData['trainers'], // TODO plug in real data
        'training' => $dummyData['training'], // TODO plug in real data
        'headerTemplate' => 'header.twig',
        'footerTemplate' => 'footer.twig',
        'bodyTemplate' => 'doc-fin-formation.twig',
        'documentTitle' => "Attestation individuelle de fin de formation",
        'of' => getDummyOf(),

    ];
    $data['signatureDate'] = $data['training']['endDate']; // TODO change
    return [$data];
}

///////////////
/// Data generation Emargement
///////////////

function getDayNameFromTimestamp(int $timestamp): string
{
    $dayName = ['Dim', 'Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam'];
    return $dayName[date('w', $timestamp)];
}

function getDayFromTimestamp(int $timestamp)
{
    return date(DATE_FORMAT_LONG, $timestamp);
}

// Transform a raw period into details one ready to be displayed
function getDetailedPeriod(array $period): array
{
    if (count($period) > 0) {
        return [
            'date' => getDayNameFromTimestamp($period['startDate']) . ' ' . date(DATE_FORMAT_MEDIUM, $period['startDate']),
            'startHour' => date(HOUR_FORMAT, $period['startDate']),
            'endHour' => date(HOUR_FORMAT, $period['endDate']),
            'duration' => ($period['endDate'] - $period['startDate']) / 3600
        ];
    } else {
        return [];
    }
}

// Periods must be chronologically ordered and not overlap. One period must start and end the same day
function validatePeriods(array $periods)
{
    $previousPeriod = null;
    $validatePeriod = function ($period) use (&$previousPeriod) {
        $startTimestamp = $period['startDate'];
        if ($previousPeriod != null && $startTimestamp < $previousPeriod['endDate']) {
            throw new Exception('Error, a training period must start after the end of the previous one (start: ' . $startTimestamp . ', end: ' . $previousPeriod['endDate']);
        }
        $endTimestamp = $period['endDate'];
        if ($endTimestamp <= $startTimestamp) {
            throw new Exception('Error, a training period must end after it starts (start: ' . $startTimestamp . ', end: ' . $endTimestamp);
        }
        $startDate = date(DATE_FORMAT_LONG, $startTimestamp);
        $endDate = date(DATE_FORMAT_LONG, $endTimestamp);
        if ($startDate != $endDate) {
            throw new Exception('Error, a training period must start and end the same day (start: ' . $startDate . ', end: ' . $endDate);
        }
        $previousPeriod = $period;
    };
    array_map($validatePeriod, $periods);
}

// Create default periods from array of days in string ISO format (e.g. "2021-02-15")
function getRawTrainingPeriodsFromIsoDays(array $isoDays): array
{
    $rawTrainingPeriods = [];
    foreach ($isoDays as $isoDay) {
        array_push($rawTrainingPeriods, ['startDate' => strtotime($isoDay . ' 09:00:00'), 'endDate' => strtotime($isoDay . ' 12:30:00')]);
        array_push($rawTrainingPeriods, ['startDate' => strtotime($isoDay . ' 13:30:00'), 'endDate' => strtotime($isoDay . ' 17:00:00')]);
    }
    return $rawTrainingPeriods;
}

// Generate combinations from two arrays
function getCombinations(array $array1, array $array2): array
{
    $result = [];
    foreach ($array1 as $element1) {
        foreach ($array2 as $element2) {
            array_push($result, [$element1, $element2]);
        }
    }
    return $result;
}

// Constants to allow proper page fit, do not change them
define('MAX_PERIOD_PER_PAGE', 8);
define('MAX_TRAINEE_PER_PAGE', 7);

function getDataEmargement(): array
{
    // Dummy Data
    $trainers = getDummyTrainers();  // TODO plug in real data
    $trainees = getDummyTrainees();  // TODO plug in real data
    $training = getDummyTraining(); // TODO plug in real data
    // Processing
    $rawTrainingPeriods = getRawTrainingPeriodsFromIsoDays($training['days']);
    $nbTraineePerPage = MAX_TRAINEE_PER_PAGE - count($trainers) + 1;
    validatePeriods($rawTrainingPeriods);
    $trainingPeriods = array_map('getDetailedPeriod', $rawTrainingPeriods);
    $timestamps = array_column($rawTrainingPeriods, 'startDate');
    sort($timestamps, SORT_NUMERIC);
    $uniqueDays = array_unique(array_map('getDayFromTimestamp', $timestamps));
    $commonData = [
        'headerTemplate' => 'header.twig',
        'footerTemplate' => 'footer.twig',
        'bodyTemplate' => 'doc-emargement.twig',
        'documentTitle' => "Feuille d'émargement",
        'trainingName' => $training['name'],
        'trainingLocation' => $training['location'],
        'trainingStartDate' => reset($uniqueDays),
        'trainingEndDate' => end($uniqueDays),
        'trainingDays' => count($uniqueDays),
        'trainingDuration' => array_sum(array_column($trainingPeriods, 'duration')),
        'trainers' => $trainers,
        'nbPeriodPerPage' => MAX_PERIOD_PER_PAGE,
        'nbTraineePerPage' => $nbTraineePerPage,
    ];
    $traineesChunks = array_chunk($trainees, $nbTraineePerPage);
    $trainingPeriodChunks = array_chunk($trainingPeriods, MAX_PERIOD_PER_PAGE);
    $combinations = getCombinations($trainingPeriodChunks, $traineesChunks);
    $pages = [];
    foreach ($combinations as $combination) {
        array_push($pages, array_merge([
            'trainingPeriods' => $combination[0],
            'trainees' => $combination[1],
        ], $commonData));
    }
    return $pages;
}

///////////////
/// Main HTML & PDF functions
///////////////

function getNewDompdfInstance(): Dompdf
{
    $options = new \Dompdf\Options();
    $options->set('chroot', 'assets');
//    $options->set('enable_html5_parser', true); // No difference for the tested use cases
    return new Dompdf($options);
}

function getNewTwigInstance(): \Twig\Environment
{
    $loader = new \Twig\Loader\FilesystemLoader('templates');
    $twig = new \Twig\Environment($loader, ['cache' => false]);
    $twig->addFilter(new \Twig\TwigFilter('fullName', function ($person) {
        return $person != null ? $person['firstName'] . ' ' . $person['lastName'] : '';
    }));
    return $twig;
}

// Document type is used as a class on the HTML body : class="{{ documentType }}-document"
// Page management is not dynamic : you have to separate the data in pages beforehand
function getHtml(\Twig\Environment $twig, array $pagesData, string $documentType): string
{
    return $twig->render('index.twig', [
        'pagesData' => $pagesData,
        'documentType' => $documentType,
        'footerText' => getFooterText(),
    ]);
}

// Accepted orientations : landscape / portrait
function generatePdf(string $html, string $documentType, $orientation = 'portrait')
{
    $dompdf = getNewDompdfInstance(); // Must be a fresh new instance each time (see https://github.com/dompdf/dompdf/issues/1056)
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', $orientation);
    $dompdf->render();
    $output = $dompdf->output();
    file_put_contents('target/' . $documentType . '-' . time() . '.pdf', $output); // time() to be pseudo "unique"
}

///////////////
/// Main
///////////////

function generatePdfFinDeFormation($twig)
{
    $pagesData = getDataFinFormation();
    $documentType = 'fin-formation';
    $html = getHtml($twig, $pagesData, $documentType);
    generatePdf($html, $documentType);
//    echo $html;
}

function generatePdfEmargement($twig)
{
    $pagesData = getDataEmargement();
    $documentType = 'emargement';
    $html = getHtml($twig, $pagesData, $documentType);
    generatePdf($html, $documentType, 'landscape');
//    echo $html;
}

$twig = getNewTwigInstance();
generatePdfEmargement($twig);
//generatePdfFinDeFormation($twig);

echo '<h1>Success ! The result was written to the ./target directory</h1>';

?>
