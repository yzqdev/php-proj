<?php

//使用restfulapi实现activity的curd
class ActivityController
{
    //获取列表
    public function index()
    {
        return Resp::outs(Activity::all());
    }

    //获取某一个活动
    public function get($id)
    {
        return Resp::outs(Activity::where('id', $id)->first());
    }

    //创建一个活动
    public function create(Request $request)
    {
        $data = $request->all();
        return Resp::outs(Activity::create($data));
    }

    //更新一个活动
    public function update(Request $request, $id)
    {
        $data = $request->all();
        return Resp::outs(Activity::where('id', $id)->update($data));
    }

    //删除一个活动
    public function delete($id)
    {
        return Resp::outs(Activity::where('id', $id)->delete());
    }
}