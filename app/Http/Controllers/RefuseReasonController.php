<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RefuseReason;
use App\Http\Response;

class RefuseReasonController extends Controller
{
    public function create(Request $request) {
        $request->validate([
            'content' => 'required|string',
        ]);
        $reason = new RefuseReason();
        $reason->content = $request->input('content');
        $reason->save();
        return $reason;
    }

    public function getAll(Request $request) {
        return RefuseReason::all();
    }

    public function delete(Request $request) {
        $request->validate([
            'reasonId' => 'required|integer|exists:refuse_reasons,id',
        ]);
        RefuseReason::destroy($request->input('reasonId'));
        return Response::success();
    }
}
