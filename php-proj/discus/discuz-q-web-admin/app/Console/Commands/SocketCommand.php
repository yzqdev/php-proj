<?php
namespace App\Console\Commands;

use Discuz\Console\AbstractCommand;
use Discuz\Foundation\Application;

class SocketCommand extends AbstractCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'socket';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * webSocket端口
     * @var int
     */
    protected $serverPort = 7104;

    /**
     * webSocket 对象
     * @var \Swoole\WebSocket\Server|null
     */
    protected $ws = null;

    /**
     * swoole 锁对象
     * @var \Swoole\Lock|null
     */
    protected $lock = null;

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
        //
        $this->start();
    }

    public function start()
    {
        try {
            //创建websocket服务器对象，监听0.0.0.0:9502端口
            $this->ws = new \Swoole\WebSocket\Server("0.0.0.0", $this->serverPort);
        }catch (Exception $exception)
        {
            $this->log("__construct","init swoole websocket error",$exception);
        }

        //监听WebSocket连接打开事件
        $this->ws->on('open', function ($ws, $request) {
            $this->onOpen($ws, $request);
        });
        $this->ws->on('request', function ($request, $response) {
            $this->onRequest($request, $response);
        });
        //监听WebSocket消息事件
        $this->ws->on('message', function ($ws, $frame) {
            $this->onMessage($ws, $frame);
        });
        //监听WebSocket连接关闭事件
        $this->ws->on('close', function ($ws, $fd) {
            $this->onClose($ws, $fd);
        });
        $this->ws->start();
    }

    /**
     * 监听打开连接
     * @param $ws
     * @param $request
     */
    protected function onOpen($ws, $request)
    {
        echo "client-{$request->fd} is client \n";
    }

    /**
     * 监听消息方法
     * @param Swoole\Websocket\Server $ws
     * @param Swoole\Websocket\Frame $frame
     */
    protected function onMessage($ws, $frame)
    {
        echo "Message: {$frame->data}\n";
        $ws->push($frame->fd, "server: {$frame->data}");
    }

    /**
     * 监听关闭连接
     * @param $ws
     * @param $fd
     */
    protected function onClose($ws, $fd)
    {
        echo "client-{$fd} is closed\n";
        $this->dropUser($fd);
    }

    /**
     * 监听请求
     * @param $request
     * @param $response
     */
    protected function onRequest($request, $response)
    {
        var_dump($request);
    }
}

