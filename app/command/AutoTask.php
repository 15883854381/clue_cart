<?php
declare (strict_types=1);

namespace app\command;

use app\controller\Test;
use app\controller\Ulits;
use app\controller\User;
use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;
use think\facade\Log;

class AutoTask extends Command
{
    protected function configure()

    {
        $this->setName('Test')
            ->addArgument('action', Argument::OPTIONAL, "action", '')
            ->addArgument('force', Argument::OPTIONAL, "force", '');

    }

    protected function execute(Input $input, Output $output)

    {

        $action = trim($input->getArgument('action'));
        $force = trim($input->getArgument('force'));
        $task = new \EasyTask\Task();
        $task->setRunTimePath('./runtime/');
        $task->addClass('app\controller\WeiXinUlits', 'sendTemplate', 'send_sms_task', 60 * 60, 1);
        if ($action == 'start') {
            $task->start();
        } elseif ($action == 'status') {
            $task->status();
        } elseif ($action == 'stop') {
            $force = ($force == 'force'); //是否强制停止
            $task->stop($force);
        } else {
            exit('Command is not exist');
        }

    }
}
