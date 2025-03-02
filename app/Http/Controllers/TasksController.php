<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Task;    // 追加

class TasksController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
    
        $data = [];
        if (\Auth::check()) { // 認証済みの場合
            // 認証済みユーザーを取得
            $user = \Auth::user();
            // ユーザーの投稿の一覧を作成日時の降順で取得
            // （後のChapterで他ユーザーの投稿も取得するように変更しますが、現時点ではこのユーザーの投稿のみ取得します）
            $tasks = $user->tasks()->orderBy('created_at', 'desc')->paginate(10);
            $data = [
                'user' => $user,
                'tasks' => $tasks,
            ];
        }
        
        // dashboardビューでそれらを表示
        return view('dashboard', $data);
    }


    /**
     * Show the form for creating a new resource.
     */
     // getでtasks/createにアクセスされた場合の「新規登録画面表示処理」
    public function create()
    {
        $task = new Task;

        // メッセージ作成ビューを表示
        return view('tasks.create', [
            'task' => $task,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    // postでtasks/にアクセスされた場合の「新規登録処理」
    public function store(Request $request)
    {
        
        //バリデーション
        $request->validate([
            'status' => 'required|max:10',
            'content' => 'required|max:255',
        ]);
        
        // 認証済みユーザー（閲覧者）の投稿として作成（リクエストされた値をもとに作成）
        $request->user()->tasks()->create([
            'status' => $request->input('status'),
            'content' => $request->input('content'), 
        ]);

        // 前のURLへリダイレクトさせる
        return redirect('/');
        
        
        
    }

    /**
     * Display the specified resource.
     */
    // getでtasks/idにアクセスされた場合の「取得表示処理」
    public function show($id)
    {
        // idの値でメッセージを検索して取得
        $task = Task::findOrFail($id);
        
        if (\Auth::id() !== $task->user_id) {
            
        // トップページへリダイレクトさせる
        return redirect('/');
        }

        // メッセージ詳細ビューでそれを表示
        return view('tasks.show', [
            'task' => $task,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    // getでmessages/id/editにアクセスされた場合の「更新画面表示処理」
    public function edit($id)
    {
        // idの値でメッセージを検索して取得
        $task = Task::findOrFail($id);
        
        if (\Auth::id() !== $task->user_id) {
            
        // トップページへリダイレクトさせる
        return redirect('/');
        }

        // メッセージ編集ビューでそれを表示
        return view('tasks.edit', [
            'task' => $task,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    // putまたはpatchでmessages/idにアクセスされた場合の「更新処理」
    public function update(Request $request, $id)
    {
        // バリデーション
        $request->validate([
            'status' => 'required|max:10',
            'content' => 'required|max:255',
        ]);
        
        // idの値でメッセージを検索して取得
        $task = Task::findOrFail($id);
        // メッセージを更新
        $task->status = $request->status;
        $task->content = $request->content;
        $task->save();
        return redirect('/');
        
        if (\Auth::id() !== $task->user_id) {
        // トップページへリダイレクトさせる
        return redirect('/');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    // deleteでmessages/idにアクセスされた場合の「削除処理」
    public function destroy($id)
    {
        $task = Task::findOrFail($id);
        // idの値でメッセージを検索して取得
        if (\Auth::id() === $task->user_id) {
            $task->delete();

        // トップページへリダイレクトさせる
        return redirect('/');
        }
    }
}
