<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GitCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'git:push {comment} {--c|compare} {target=master} {--s|nostatus} {--i|issue=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Execute git commands : add --all >> commit >> push >> (option) show compare branch';

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
     * @return mixed
     */
    public function handle()
    {
      if (!$this->option('nostatus')) {
        // git status
        $this->comment("Run: git status");
        $this->info(shell_exec("git status"));
        $confirmed = $this->confirm('Do you commit to all files?');
        if (! $confirmed) {
            $this->comment('Please remove untraced file and re-run this command.');
            return;
        }
      }

      // git add
      $this->comment("Run: git add --all");
      $this->info(shell_exec("git add --all"));

      // git commit
      $comment = trim($this->argument('comment'));
      if ($this->option('issue')) {
        $comment = "#".$this->option('issue')." ".$comment;
      }
      $this->comment("Run: git commit -m {your input comment}");
      $this->info(shell_exec('git commit -m "'.$comment.'"'));

      // git push
      $this->comment("Run: git push -u origin HEAD");
      $this->info(shell_exec('git push -u origin HEAD'));

      if (!$this->option('compare')) {
        $this->comment("Finished!!");
        return;
      }
      // Show github compare page.
      $current = shell_exec('git rev-parse --abbrev-ref HEAD');
      $base = $this->argument('target');
      $this->info(shell_exec('hub compare '.$base.'..'.$current));
      $this->comment("Finished!!");
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['comment', InputArgument::REQUIRED, 'Your git comment'],
            ['target', InputArgument::NOT_REQUIRED, 'Git Base Branch Name (merge target)'],
        ];
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
          ['nostatus', '-nostatus', InputOption::VALUE_OPTIONAL, 'Run without git status command'],
          ['issue', '-issue', InputOption::VALUE_OPTIONAL, 'GitHub issue no'],
          ['compare', '-compare', InputOption::VALUE_OPTIONAL, 'If you execute github compare, add this option'],
        ];
    }
}
