<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
//以下を追加でNews Modelが扱える
use App\News;

use App\History;

use Carbon\Carbon;

class NewsController extends Controller
{
    //
    public function add()
    {
        return view('admin.news.create');
    }
    
    public function create(Request $request)
    {
        //validationを行う
        $this->validate($request, News::$rules);
        
        $news = new News;
        $form = $request->all();

        //フォームから画像が送信されたら、保存、$news->image_pathに画像のパスを保存
        if (isset($form['image'])) {
            $path = $request->file('image')->store('public/image');
            $news->image_path = basename($path);
        } else {
            $news->image_path = null;
        }

        //フォームから送信された_tokenを削除
        unset($form['_token']);
        //フォームから送信されたimageを削除
        unset($form['image']);
        
        //データベースに保存

        $news->fill($form);
        $news->save();

        return redirect('admin/news/create');
    }
    
    public function index(Request $request)
    {
        $cond_title = $request->cond_title;
        if ($cond_title != '') {
            //検索されたら検索結果を取得する
            $posts = News::where('title', $cond_title)->get();
        } else {
            //それ以外は全てニュースを取得する
            $posts = News::all();
        }
        return view('admin.news.index', ['posts' => $posts, 'cond_title' => $cond_title]);
    }
    
    //編集用のアクションを追加(edit,update)
    public function edit(Request $request)
    {
        //News Modelからデータを取得
        $news = News::find($request->id);
        if (empty($news)) {
            abort(404);
        }
        return view('admin.news.edit', ['news_form' => $news]);
    }
    
    public function update(Request $request)
    {
        $this->validate($request, News::$rules);
        $news = News::find($request->id);
        $news_form = $request->all();
        if ($request->remove == 'true') {
            $news_form['image_path'] = null;
        } elseif ($request->file('image')) {
            $path = $request->file('image')->store('public/image');
            $news_form['image_path'] = basename($path);
        } else {
            $news_form['image_path'] = $news->image_path;
        }
        
        unset($news_form['_token']);
        unset($news_form['image']);
        unset($news_form['remove']);
        
        //該当データを上書き保存
        $news->fill($news_form)->save();
        /*上記コードは
        $news->fill($news_form);
        $news->save();
        を短縮して書いたもの*/
        
        $history = new History;
        $history->news_id = $news->id;
        $history->edited_at = Carbon::now();
        $history->save();

        
        return redirect('admin/news');
    }
    public function delete(Request $request)
    {
        //該当するNews Modelを取得
        $news = News::find($request->id);
        
        //削除
        $news->delete();
        return redirect('admin/news/');
    }
}
