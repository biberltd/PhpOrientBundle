<?php

namespace BiberLtd\Bundle\PhpOrientBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

class OrientBackupCommand extends ContainerAwareCommand
{
    // Backup folder name
    private $backupFolderName = '../backups/orientdb';

    // Backup script path
    private $backupScriptPath;

    protected function configure()
    {
        $this
            ->setName('orientdb:backup')
            ->setDescription('OrientDB backup command.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Backing up...');

        // Filesystem
        $filesystem = new Fileystem();

        // Parameters
        $orientParams = $this->getContainer()->getParameter('orientdb');
        $availableDatabases = $orientParams['database'];

        // Backup script
        $this->backupScriptPath = $orientParams['backup']['script_path'];

        // OrientDB Connections
        $orientService = $this->getContainer()->get('biberltd.phporient');
        $orientService->connect();

        // Get database list
        $orientDatabases = $orientService->dbList();

        // Backup date
        $backupDate = new \Datemicrotime(true);
        $backupDate = $backupDate->format('d-m-Y');

        // Prepare output name
        $rootDir = $this->getContainer()->get('kernel')->getRootDir();
        $backupOutputName = $backupDate . '.zip';

        // Backup output directory
        $backupOutputDir = $rootDir . '/' . $this->backupFolderName;

        foreach ($availableDatabases as $databaseName => $params) {

            if (isset($orientDatabases['databases'][$databaseName])) {
                $databasePath = $orientDatabases['databases'][$databaseName];
                $databaseName = strtolower($databaseName);

                if (! $filesystem->exists($backupOutputDir . '/' . $databaseName)) {
                    $filesystem->mkdir($backupOutputDir . '/' . $databaseName);
                }
            }

            $outputName = $backupOutputDir . '/' . $databaseName . '/' . $backupOutputName;
            $backupStatus = $this->runBackupShell($databasePath, $params, $outputName);

            if ($backupStatus) {
                $output->writeln("\t #{$databaseName} - SUCCESS!");
            } else {
                $output->writeln("\t #{$databaseName} - FAILED!");
            }
        }
    }

    /**
     * Run backup shell
     *
     * @param $databasePath
     * @param $dbParams
     * @param $outputName
     * @return bool
     */
    protected function runBackupShell($databasePath, $dbParams, $outputName)
    {
        $process = new Process("{$this->backupScriptPath} {$databasePath} {$dbParams['username']} {$dbParams['password']} {$outputName}");
        $process->run();

        return $process->isSuccessful() ? true : false;
    }
}