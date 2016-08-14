<?php namespace Gzero\Core\Commands;

use Illuminate\Console\Command;

class MysqlRestore extends Command {

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'db:restore';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:restore {dbName}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Restore a mysqldump file from backups directory';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire()
    {
        $dbName   = $this->argument('dbName');
        $host     = config('database.connections.mysql.host');
        $database = config('database.connections.mysql.database');
        $username = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');

        $backupPath = base_path() . '/backups/' . $dbName . '.sql.gz';

        if (!file_exists($backupPath)) {
            $this->error("Failed to read backup file at $backupPath");
            return;
        }

        $this->info('Trying to restore database: ' . $database);

        $mysql = "/bin/gunzip -c $backupPath | /usr/bin/mysql ";

        $command = $mysql . ' -h ' . $host . ' -u ' . $username . ' -p' . $password . ' ' . $database;

        system($command);

        $this->info('Database has been restored!');

    }

}
