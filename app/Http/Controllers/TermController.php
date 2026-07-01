<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TermController extends Controller
{
    //
    public function index(int $year = 0) {
        if (!auth()->user()->can("role_any", "ec|cm|manager")) {
            abort(403, "この機能を利用する権限(ec|cm|manager)がありません。");
        }
        if ($year == 0) {
            $year = date("Y");
        }
        // year(昇順) -> post_id(降順) -> user.yomi(昇順) の優先順位でソートする
        $terms = \App\Models\Term::with("user", "post")->where("year", $year)->get();
        $terms = $terms->sort(function ($a, $b) {
            return [$a->year, -((int) $a->post_id), $a->user->yomi] <=> [$b->year, -((int) $b->post_id), $b->user->yomi];
        })->values();

        // get years from terms
        $years = \App\Models\Term::select("year")->distinct()->orderBy("year")->pluck("year");
        return view('term.index', ["terms" => $terms, "years" => $years, "selectedYear" => $year]);
    }
}
