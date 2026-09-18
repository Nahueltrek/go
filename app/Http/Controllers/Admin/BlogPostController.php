<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use App\Models\Destination;
use App\Models\Organization;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;

class BlogPostController extends Controller
{
    public function index()
    {
        $posts = BlogPost::query()
            ->with(['author'])
            ->latest()
            ->paginate(20)
            ->through(fn (BlogPost $p) => [
                'id' => $p->id,
                'title' => $p->title,
                'slug' => $p->slug,
                'category' => $p->category,
                'status' => $p->status,
                'author' => $p->author?->name,
                'published_at' => $p->published_at?->format('d-m-Y'),
            ]);

        return Inertia::render('Admin/BlogPosts/Index', [
            'posts' => $posts,
            'categories' => BlogPost::CATEGORIES,
        ]);
    }

    public function create()
    {
        $this->authorize('create', BlogPost::class);

        return Inertia::render('Admin/BlogPosts/Edit', [
            'post' => null,
            'categories' => BlogPost::CATEGORIES,
            'organizations' => Organization::approved()->orderBy('name')->get(['id', 'name']),
            'destinations' => Destination::active()->orderBy('name')->get(['id', 'name']),
            'canPublish' => request()->user()->hasRole('admin') || request()->user()->hasRole('super_admin'),
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', BlogPost::class);

        $validated = $this->validated($request);

        $canPublish = $request->user()->hasRole('admin') || $request->user()->hasRole('super_admin');
        $status = ($validated['status'] !== 'draft' && $canPublish) ? $validated['status'] : 'draft';

        BlogPost::create([
            'author_id' => $request->user()->id,
            'title' => $validated['title'],
            'slug' => $validated['slug'] ?: Str::slug($validated['title']),
            'excerpt' => $validated['excerpt'],
            'content' => $validated['content'],
            'category' => $validated['category'],
            'cover_image_url' => $validated['cover_image_url'],
            'related_organization_id' => $validated['related_organization_id'],
            'related_destination_id' => $validated['related_destination_id'],
            'status' => $status,
            'published_at' => $status === 'published' ? now() : null,
        ]);

        return redirect()->route('admin.blog-posts.index')->with('status', 'Historia guardada.');
    }

    public function edit(BlogPost $blogPost)
    {
        $this->authorize('update', $blogPost);

        return Inertia::render('Admin/BlogPosts/Edit', [
            'post' => [
                'id' => $blogPost->id,
                'title' => $blogPost->title,
                'slug' => $blogPost->slug,
                'excerpt' => $blogPost->excerpt,
                'content' => $blogPost->content,
                'category' => $blogPost->category,
                'cover_image_url' => $blogPost->cover_image_url,
                'related_organization_id' => $blogPost->related_organization_id,
                'related_destination_id' => $blogPost->related_destination_id,
                'status' => $blogPost->status,
            ],
            'categories' => BlogPost::CATEGORIES,
            'organizations' => Organization::approved()->orderBy('name')->get(['id', 'name']),
            'destinations' => Destination::active()->orderBy('name')->get(['id', 'name']),
            'canPublish' => request()->user()->hasRole('admin') || request()->user()->hasRole('super_admin'),
        ]);
    }

    public function update(Request $request, BlogPost $blogPost)
    {
        $this->authorize('update', $blogPost);

        $validated = $this->validated($request, $blogPost->id);

        $canPublish = $request->user()->hasRole('admin') || $request->user()->hasRole('super_admin');
        $wantsPublish = $validated['status'] !== 'draft';

        if ($wantsPublish) {
            $this->authorize('publish', $blogPost);
        }

        $status = ($wantsPublish && $canPublish) ? $validated['status'] : 'draft';

        $blogPost->update([
            'title' => $validated['title'],
            'slug' => $validated['slug'] ?: Str::slug($validated['title']),
            'excerpt' => $validated['excerpt'],
            'content' => $validated['content'],
            'category' => $validated['category'],
            'cover_image_url' => $validated['cover_image_url'],
            'related_organization_id' => $validated['related_organization_id'],
            'related_destination_id' => $validated['related_destination_id'],
            'status' => $status,
            'published_at' => $status === 'published' ? ($blogPost->published_at ?? now()) : null,
        ]);

        return redirect()->route('admin.blog-posts.index')->with('status', 'Historia actualizada.');
    }

    /**
     * Sube una imagen de portada para un post de la Bitácora y devuelve su
     * URL pública. No crea/edita el BlogPost — el form la asigna a
     * cover_image_url y se guarda junto con el resto al hacer submit.
     * Se guarda en el disco 'blog_uploads' (public/uploads/blog-covers),
     * sin depender de storage:link.
     */
    public function uploadImage(Request $request): JsonResponse
    {
        $this->authorize('create', BlogPost::class);

        $request->validate([
            'image' => ['required', 'image', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
        ]);

        $file = $request->file('image');
        $filename = Str::uuid().'.'.$file->extension();
        $file->storeAs('', $filename, 'blog_uploads');

        return response()->json([
            'url' => Storage::disk('blog_uploads')->url($filename),
        ]);
    }

    protected function validated(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:blog_posts,slug' . ($ignoreId ? ",{$ignoreId},id" : ''),
            'excerpt' => 'nullable|string|max:500',
            'content' => 'required|string',
            'category' => 'required|in:' . implode(',', array_keys(BlogPost::CATEGORIES)),
            'cover_image_url' => 'nullable|string|max:2048',
            'related_organization_id' => 'nullable|exists:organizations,id',
            'related_destination_id' => 'nullable|exists:destinations,id',
            'status' => 'required|in:draft,published,unpublished',
        ]);
    }
}
