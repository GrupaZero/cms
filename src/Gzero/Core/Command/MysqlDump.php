<?php namespace Gzero\Core\Command;

use Illuminate\Console\Command;

class MysqlDump extends Command {

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'db:dump';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a mysqldump file at backups directory';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire()
    {
        $host     = config('database.connections.mysql.host');
        $database = config('database.connections.mysql.database');
        $username = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');

        $backupPath = base_path() . '/backups/' . date('Y-m-d_H_i_s') . '.sql.gz';

        $this->info('Trying to backup database: ' . $database);

        $mysqldump = "/usr/bin/mysqldump --single-transaction --events --routines";

        $command = $mysqldump . ' -h ' . $host . ' -u ' . $username . ' -p' . $password . ' ' . $database .
            ' | gzip > ' . $backupPath;

        system($command);

        $this->info('Writing to: ' . $backupPath);
        $this->info('Backup has been completed!');

    }

}