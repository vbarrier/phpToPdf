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
function getDummyDataAssiduite(): array
{
    $dummyData = [
        'trainee' => ['firstName' => 'Jane', 'lastName' => 'Doe'],
        'training' => [
            'name' => "Professionnel Scrum Certifié agilité à l'échelle - SAFe PO/PM",
            'startDate' => strtotime('2021-02-15'),
            'endDate' => strtotime('2021-02-16'),
            'duration' => 14
        ]
    ];
    $dummyData['signatureDate'] = $dummyData['trainingEndDate'];
    return $dummyData;
}

// TODO remove
function getDummyTraining(): array {
    return [
        'days' => ['2021-03-08', '2021-03-09'],
        'name' => "Professionnel Scrum Certifié agilité à l'échelle - SAFe PO/PM",
        'location' => 'À distance',
    ];
}

// TODO remove
// Example trainers: Min 1, Max 7
function getDummyTrainers(): array
{
    return [
        ['firstName' => 'Albert', 'lastName' => 'Einstein']
    ];
}

// TODO remove
// Example trainees: Min 1, no max
function getDummyTrainees(): array
{
    return [
        ['firstName' => 'Nicolas', 'lastName' => 'Noullet'],
        ['firstName' => 'Vincent', 'lastName' => 'Barrier'],
        ['firstName' => 'Gladys', 'lastName' => 'Lutiku'],
        ['firstName' => 'Kesley', 'lastName' => 'George'],
        ['firstName' => 'Nicolas', 'lastName' => 'Noullet'],
        ['firstName' => 'Vincent', 'lastName' => 'Barrier'],
        ['firstName' => 'Gladys', 'lastName' => 'Lutiku'],
        ['firstName' => 'Kesley', 'lastName' => 'George'],
    ];
}

///////////////
/// Data generation Assiduite
///////////////

function getDataAssiduite(): array
{
    $dummyDataAssiduite = getDummyDataAssiduite(); // TODO plug in real data
    $training = $dummyDataAssiduite['training']; // TODO plug in real data
    $data = [
        'trainee' => $dummyDataAssiduite['trainee'],
        'trainingName' => $training['name'],
        'trainingStartDate' => date(DATE_FORMAT_LONG, $training['startDate']),
        'trainingEndDate' => date(DATE_FORMAT_LONG, $training['endDate']),
        'trainingDuration' => $training['duration'],
        'headerTemplate' => 'header.twig',
        'footerTemplate' => 'footer.twig',
        'bodyTemplate' => 'doc-assiduite.twig',
        'documentTitle' => "Attestation d'assiduité de formation",
        'ofUserName' => 'Vincent BARRIER',
        'ofName' => 'Kagilum'
    ];
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
        'trainingLocation' =>  $training['location'],
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
    return $twig;
}

// Document type is used as a class on the HTML body : class="{{ documentType }}-document"
// Page management is not dynamic : you have to separate the data in pages beforehand
function getHtml(\Twig\Environment $twig, array $pagesData, string $documentType): string
{
    return $twig->render('index.twig', ['pagesData' => $pagesData, 'documentType' => $documentType]);
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

function generatePdfAssiduite($twig)
{
    $pagesData = getDataAssiduite();
    $documentType = 'assiduite';
    $html = getHtml($twig, $pagesData, $documentType);
    generatePdf($html, $documentType);
}

function generatePdfEmargement($twig)
{
    $pagesData = getDataEmargement();
    $documentType = 'emargement';
    $html = getHtml($twig, $pagesData, $documentType);
    generatePdf($html, $documentType, 'landscape');
}

$twig = getNewTwigInstance();
generatePdfEmargement($twig);
generatePdfAssiduite($twig);

echo '<h1>Success ! The result was written to the ./target directory</h1>';

?>
