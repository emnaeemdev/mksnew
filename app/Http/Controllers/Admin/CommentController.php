<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Comment::with('post');

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search by name, email, or content
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        $comments = $query->latest()->paginate(15);
        
        return view('admin.comments.index', compact('comments'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $posts = Post::select('id', 'title')->get();
        return view('admin.comments.create', compact('posts'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'content' => 'required|string',
            'post_id' => 'required|exists:posts,id',
            'status' => 'required|in:pending,approved,rejected'
        ]);

        Comment::create([
            'name' => $request->name,
            'email' => $request->email,
            'content' => $request->content,
            'post_id' => $request->post_id,
            'status' => $request->status,
            'ip_address' => $request->ip()
        ]);

        return redirect()->route('admin.comments.index')
            ->with('success', 'تم إنشاء التعليق بنجاح');
    }

    /**
     * Display the specified resource.
     */
    public function show(Comment $comment)
    {
        $comment->load('post');
        return view('admin.comments.show', compact('comment'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Comment $comment)
    {
        $posts = Post::select('id', 'title')->get();
        return view('admin.comments.edit', compact('comment', 'posts'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Comment $comment)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'content' => 'required|string',
            'post_id' => 'required|exists:posts,id',
            'status' => 'required|in:pending,approved,rejected'
        ]);

        $comment->update([
            'name' => $request->name,
            'email' => $request->email,
            'content' => $request->content,
            'post_id' => $request->post_id,
            'status' => $request->status
        ]);

        return redirect()->route('admin.comments.index')
            ->with('success', 'تم تحديث التعليق بنجاح');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Comment $comment)
    {
        $comment->delete();

        return redirect()->route('admin.comments.index')
            ->with('success', 'تم حذف التعليق بنجاح');
    }

    /**
     * Approve a comment.
     */
    public function approve(Comment $comment)
    {
        $comment->update(['status' => 'approved']);
        
        return redirect()->back()
            ->with('success', 'تم الموافقة على التعليق');
    }

    /**
     * Reject a comment.
     */
    public function reject(Comment $comment)
    {
        $comment->update(['status' => 'rejected']);
        
        return redirect()->back()
            ->with('success', 'تم رفض التعليق');
    }
}