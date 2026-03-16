<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TermController extends Controller
{
    //
    public function index() {
        if (!auth()->user()->can("role", "manager")) {
            abort(403, "この機能を利用する権限(manager)がありません。");
        }
        // year, post_id, user.yomiの順でソートする
        $terms = \App\Models\Term::with("user", "post")->orderBy("year")->orderBy("post_id")->get();
        $terms = $terms->sortBy(function ($term) {
            return $term->user->yomi;
        });
        return view('term.index', ["terms" => $terms]);
    }
}
