<?php

declare(strict_types=1);

use SebastianBergmann\CodeCoverage\CodeCoverage;
use SebastianBergmann\CodeCoverage\Node\Directory;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableStyle;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Style\SymfonyStyle;

require __DIR__ . '/../vendor/autoload.php';

// construct symfony io object to format output
$io = new SymfonyStyle(new StringInput(''), new ConsoleOutput());

// parse input arguments
[,$metric, $path, $threshold] = $argv;
$threshold = min(100, max(0, (int) $threshold));

// load code coverage report
$coverageReportPath = __DIR__ . '/../var/coverage.php';
if (!is_readable($coverageReportPath)) {
    $io->error('Coverage report file "' . $coverageReportPath . '" is not readable or does not exist.');
    exit(1);
}
$coverage = require $coverageReportPath;

assertCodeCoverage($coverage, $path, $metric, $threshold);

function assertCodeCoverage(CodeCoverage $coverage, string $path, string $metric, int $threshold)
{
    global $io;

    $rootReport = $coverage->getReport();
    $pathReport = getReportForPath($rootReport, $path);

    if (!$pathReport) {
        $io->error('Coverage report for path "' . $path . '"" not found.');
        exit(1);
    }

    printCodeCoverageReport($path, $pathReport);

    if ('line' === $metric) {
        $reportedCoverage = $pathReport->getLineExecutedPercent();
    } elseif ('method' === $metric) {
        $reportedCoverage = $pathReport->getTestedMethodsPercent();
    } elseif ('class' === $metric) {
        $reportedCoverage = $pathReport->getTestedClassesPercent();
    } else {
        $io->error('Coverage metric "' . $metric . '"" is not supported yet.');
        exit(1);
    }

    if ($reportedCoverage < $threshold) {
        $io->error(sprintf(
            'Code Coverage for metric "%s" and path "%s" is below threshold of %.2F%%.',
            $metric,
            $path,
            $threshold
        ));

        exit(1);
    }

    $io->success(sprintf(
        'Code Coverage for metric "%s" and path "%s" is above threshold of %.2F%%.',
        $metric,
        $path,
        $threshold
    ));

    exit(0);
}

function printCodeCoverageReport(string $path, Directory $pathReport): void
{
    global $io;

    $rightAlignedTableStyle = new TableStyle();
    $rightAlignedTableStyle->setPadType(STR_PAD_LEFT);

    $table = new Table($io);
    $table->setColumnWidth(0, 20);
    $table->setColumnStyle(1, $rightAlignedTableStyle);
    $table->setColumnStyle(2, $rightAlignedTableStyle);

    $table->setHeaders(['Coverage Metric', 'Relative Coverage', 'Absolute Coverage']);
    $table->addRow([
        'Line Coverage',
        sprintf('%.2F%%', $pathReport->getLineExecutedPercent()),
        sprintf('%d/%d', $pathReport->getNumExecutedLines(), $pathReport->getNumExecutableLines()),
    ]);
    $table->addRow([
        'Method Coverage',
        sprintf('%.2F%%', $pathReport->getTestedMethodsPercent()),
        sprintf('%d/%d', $pathReport->getNumTestedMethods(), $pathReport->getNumMethods()),
    ]);
    $table->addRow([
        'Class Coverage',
        sprintf('%.2F%%', $pathReport->getTestedClassesPercent()),
        sprintf('%d/%d', $pathReport->getNumTestedClasses(), $pathReport->getNumClasses()),
    ]);

    $io->title('Code coverage report for path "' . $path . '"');
    $table->render();
    $io->newLine(2);
}

function getReportForPath(Directory $rootReport, string $path): ?Directory
{
    /** @var Directory $report */
    foreach ($rootReport as $report) {
        $reportPath = $report->getPath();
        if (false !== mb_stripos($reportPath, $path)) {
            return $report;
        }
    }

    return null;
}
