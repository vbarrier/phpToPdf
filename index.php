<?php

require_once 'lib/dompdf/autoload.inc.php';
require_once 'lib/twig/autoload.inc.php';

use Dompdf\Dompdf;

///////////////
/// Data simulation Assiduite
///////////////

function getDataAssiduite(): array
{
    $data = [
        'documentTitle' => "Attestation d'assiduité de formation",
        'ofUserName' => 'Vincent BARRIER',
        'ofName' => 'Kagilum',
        'trainee' => ['firstName' => 'Jane', 'lastName' => 'Doe'],
        'trainingName' => 'Professionnel Scrum Certifié',
        'trainingStartDate' => '15/02/2021',
        'trainingEndDate' => '16/02/2021',
        'trainingDuration' => 14
    ];
    $data['traineeName'] = $data['trainee']['firstName'] . ' ' . $data['trainee']['lastName'];
    $data['signatureDate'] = $data['trainingEndDate'];
    return $data;
}

///////////////
/// Data simulation Emargement
///////////////

function getDayNameFromTimestamp(int $timestamp): string
{
    $dayName = array('Dim', 'Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam');
    return $dayName[date('w', $timestamp)];
}

function getDetailedPeriod(array $period): array
{
    if (count($period) > 0) {
        return [
            'date' => getDayNameFromTimestamp($period['startDate']) . ' ' . date('d/m/y', $period['startDate']),
            'startHour' => date('H:i', $period['startDate']),
            'endHour' => date('H:i', $period['endDate']),
            'duration' => ($period['endDate'] - $period['startDate']) / 3600
        ];
    } else {
        return [];
    }
}

function validatePeriods(array $periods)
{
    $previousPeriod = null;
    $validatePeriod = function($period) use (&$previousPeriod) {
        $startTimestamp = $period['startDate'];
        if ($previousPeriod != null && $startTimestamp < $previousPeriod['endDate']) {
            throw new Exception('Error, a training period must start after the end of the previous one (start: ' . $startTimestamp . ', end: ' . $previousPeriod['endDate']);
        }
        $endTimestamp = $period['endDate'];
        if ($endTimestamp <= $startTimestamp) {
            throw new Exception('Error, a training period must end after it starts (start: ' . $startTimestamp . ', end: ' . $endTimestamp);
        }
        $startDate = date('d/m/Y', $startTimestamp);
        $endDate = date('d/m/Y', $endTimestamp);
        if ($startDate != $endDate) {
            throw new Exception('Error, a training period must start and end the same day (start: ' . $startDate . ', end: ' . $endDate);
        }
        $previousPeriod = $period;
    };
    array_map($validatePeriod, $periods);
}

function getDataEmargement(): array
{
    $nbPeriodPerPage = 8; // Be careful, this is not dynamic !
    $nbTraineePerPage = 7; // Be careful, this is not dynamic !
    $rawTrainingPeriods = [
        ['startDate' => strtotime('2021-02-15 09:00:00'), 'endDate' => strtotime('2021-02-15 12:30:00')],
        ['startDate' => strtotime('2021-02-15 13:30:00'), 'endDate' => strtotime('2021-02-15 17:00:00')],
        ['startDate' => strtotime('2021-02-16 09:00:00'), 'endDate' => strtotime('2021-02-16 13:30:00')],
        ['startDate' => strtotime('2021-02-16 13:30:00'), 'endDate' => strtotime('2021-02-16 16:00:00')],
    ];
    validatePeriods($rawTrainingPeriods);
    $trainees = [
        ['firstName' => 'Nicolas', 'lastName' => 'Noullet'],
        ['firstName' => 'Vincent', 'lastName' => 'Barrier'],
        ['firstName' => 'Gladys', 'lastName' => 'Lutiku'],
        ['firstName' => 'Kesley', 'lastName' => 'George'],
    ];
    $trainingPeriods = array_map('getDetailedPeriod', $rawTrainingPeriods);
    $trainingDuration = array_sum(array_column($trainingPeriods, 'duration'));
    $getDayFromTimestamp = function (int $timestamp) {
        return date('d/m/Y', $timestamp);
    };
    $timestamps = array_column($rawTrainingPeriods, 'startDate');
    sort($timestamps, SORT_NUMERIC);
    $uniqueDays = array_unique(array_map($getDayFromTimestamp, $timestamps));
    return [
        'documentTitle' => "Feuille d'émargement",
        'trainingName' => 'Formation professionnel Scrum Certifié : Scrum Master / Product Owner',
        'trainingStartDate' => reset($uniqueDays),
        'trainingEndDate' => end($uniqueDays),
        'trainingDays' => count($uniqueDays),
        'trainingDuration' => $trainingDuration,
        'trainingLocation' => 'A distance',
        'trainingPeriods' => $trainingPeriods,
        'trainees' => $trainees,
        'trainer' => ['firstName' => 'Albert', 'lastName' => 'Einstein'],
        'nbPeriodPerPage' => $nbPeriodPerPage,
        'nbTraineePerPage' => $nbTraineePerPage
    ];
}

///////////////
/// Functions
///////////////

function getDompdf(): Dompdf
{
    $options = new \Dompdf\Options();
    $options->set('chroot', 'assets');
    return new Dompdf($options);
}

function getTwig(): \Twig\Environment
{
    $loader = new \Twig\Loader\FilesystemLoader('templates');
    $twig = new \Twig\Environment($loader, ['cache' => false]);
    return $twig;
}

function getHtml(\Twig\Environment $twig, array $templates, array $data): string
{
    return $twig->render('index.twig', array_merge($data, $templates));
}

function generatePdf(Dompdf $dompdf, string $html, $orientation = 'portrait')
{
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', $orientation);
    $dompdf->render();
    $dompdf->stream();
}

///////////////
/// Main
///////////////


function generatePdfAssiduite($twig, $dompdf)
{
    $data = getDataAssiduite();
    $templates = [
        'headerTemplate' => 'header.twig',
        'footerTemplate' => 'footer.twig',
        'bodyTemplate' => 'doc-assiduite.twig',
        'documentType' => 'assiduite'
    ];
    $html = getHtml($twig, $templates, $data);
//    echo $html;
    generatePdf($dompdf, $html);
}

function generatePdfEmargement($twig, $dompdf)
{
    $data = getDataEmargement();
    $templates = [
        'headerTemplate' => 'header.twig',
        'footerTemplate' => 'footer.twig',
        'bodyTemplate' => 'doc-emargement.twig',
        'documentType' => 'emargement'
    ];
    $html = getHtml($twig, $templates, $data);
//    echo $html;
    generatePdf($dompdf, $html, 'landscape');
}

$twig = getTwig();
$dompdf = getDompdf();
//generatePdfAssiduite($twig, $dompdf);
generatePdfEmargement($twig, $dompdf);

?>
