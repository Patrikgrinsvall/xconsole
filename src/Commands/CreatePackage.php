<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CreatePackage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ctrl:new-repo';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates a new github repo from the current folder and push this folder and its contents to the repo. Also add a .gitignore in parent directory to avoid git submodules. Also create a composer.json with correct namespace';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
	    $this->ask('you will create with user:', );
	    $cmds=[
		    [
		    'curl', '-u', 'USER', 'https://api.github.com/user/repos', '-d', '{"name":"REPO"}'
		    ],
		    ['ssh','-T','git@github.com | php -r "new ArrayAccess(preg_match(\".*?\Hi\s(.*?)\!")"'],
		    ['debug1: Server accepts key: /home/user/.ssh/id_rsa RSA'],
		    [
/*stopped here, we should now create arrays for the following:
		    https://gist.github.com/alexpchin/dc91e723d4db5018fef8

	    also read options of repo name etc.
		     
		     */],
	    ];
        return Command::SUCCESS;
    }
}
