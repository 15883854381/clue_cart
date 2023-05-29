<?php
declare (strict_types=1);

namespace app\command;

use app\controller\Test;
use app\controller\Ulits;
use app\controller\User;
use app\controller\WeiXinUlits;
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
        $task->addFunc(function () {
            $hour = date('H');
            $fruits = [9, 10, 11, 12, 14, 16, 18, 19];
            if (!in_array($hour, $fruits)) {
                return;
            }
            $sendTemplate = new WeiXinUlits();
            $sendTemplate->sendTemplate();

        }, 'sendTemplate', 60 * 60, 1);
        $task->addFunc(function () {
            $hour = date('H');
            $fruits = [11];
            if (!in_array($hour, $fruits)) {
                return;
            }
            $sendTemplate = new WeiXinUlits();
            $sendTemplate->pushInfo();
        }, 'pushInfo', 60 * 60, 1);


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
