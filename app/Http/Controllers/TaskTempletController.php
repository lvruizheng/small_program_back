<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TaskTemplet;
use App\Http\Response;

class TaskTempletController extends Controller
{
    public function create(Request $request) {
        $request->validate([
            'title' => 'required|string',
        ]);
        if (TaskTemplet::all()->count() >= 14) {
            return [
                'errcode' => 141,
                'errMsg' => '模板数量已达上限',
            ];
        }
        $taskTemplet = TaskTemplet::create($request->only(['title', 'introduce', 'location', 'start', 'end']));
        return $taskTemplet;
    }

    public function update(Request $request) {
        $request->validate([
            'templetId' => 'required|integer|exists:task_templets,id',
            'title' => 'required|string',
        ]);
        $templet = TaskTemplet::find($request->input('templetId'));
        $templet->update($request->only(['title', 'introduce', 'location', 'start', 'end']));
        return $templet;
    }

    public function delete(Request $request) {
        $request->validate([
            'templetId' => 'required|integer|exists:task_templets,id',
        ]);
        TaskTemplet::destroy($request->input('templetId'));
        return Response::success();
    }

    public function getAll() {
        return TaskTemplet::all();
    }

    public function getOne(Request $request) {
        $request->validate([
            'templetId' => 'required|integer|exists:task_templets,id',
        ]);
        return TaskTemplet::find($request->input('templetId'));
    }
}
