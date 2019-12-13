<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\HTML;

use App\News;

class NewsController extends Controller
{
    public function index(Request $request)
    {
        $posts = News::all()->sortByDesc('updated_at');
        
        if (count($posts) > 0) {
            $headline = $posts->shift();
        } else {
            $headline = null;
        }
        
        //news/index.blade.phpファイルを渡す
        //viewテンプレートにheadline,posts, という変数を渡す
        
        return view('news.index', ['headline' => $headline, 'posts' => $posts]);
    }
}