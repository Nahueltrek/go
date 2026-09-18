<?php
use App\Http\Controllers\Admin\ArticleController as AdminArticleController;
use App\Http\Controllers\Admin\BlogPostController as AdminBlogPostController;
use App\Http\Controllers\Admin\BusinessController as AdminBusinessController;
use App\Http\Controllers\Admin\ClaimController as AdminClaimController;
use App\Http\Controllers\Admin\GoChileDashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\ReviewController as AdminReviewController;
use App\Http\Controllers\Admin\CollaborationRequestController as AdminCollaborationRequestController;
use App\Http\Controllers\Admin\OrganizationController as AdminOrganizationController;
use App\Http\Controllers\Admin\ExperienceController as AdminExperienceController;
use App\Http\Controllers\Admin\ModerationController as AdminModerationController;
use App\Http\Controllers\Admin\ProjectController as AdminProjectController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Owner\BusinessEditController;
use App\Http\Controllers\Owner\ClaimController as OwnerClaimController;
use App\Http\Controllers\Owner\DashboardController as OwnerDashboardController;
use App\Http\Controllers\Owner\ExperienceController as OwnerExperienceController;
use App\Http\Controllers\Owner\OrganizationController as OwnerOrganizationController;
use App\Http\Controllers\Owner\ProjectController as OwnerProjectController;
use App\Http\Controllers\Public\AttractionShowController;
use App\Http\Controllers\Public\BlogIndexController;
use App\Http\Controllers\Public\BlogShowController;
use App\Http\Controllers\Public\BusinessShowController;
use App\Http\Controllers\Public\RouteShowController;
use App\Http\Controllers\HomeController as GoChileHomeController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\MapController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\ExperienceController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\BlogPostController;
use App\Http\Controllers\CollaborationController;
use Illuminate\Support\Facades\Route;

// --- Home: reemplazado por la portada nueva de GO Chile 2.0 ---
Route::get('/', [GoChileHomeController::class, 'index'])->name('home');

// --- rm360 original, sin cambios ---
Route::get('/emprendimientos/{slug}', BusinessShowController::class)->name('businesses.show');
Route::get('/atractivos/{slug}', AttractionShowController::class)->name('attractions.show');
Route::get('/rutas/{slug}', RouteShowController::class)->name('routes.show');
Route::get('/blog', BlogIndexController::class)->name('blog.index');
Route::get('/blog/{slug}', BlogShowController::class)->name('blog.show');

// --- GO Chile 2.0: público, nuevo ---
Route::get('/explorar', [SearchController::class, 'index'])->name('explorar');
Route::get('/mapa', [MapController::class, 'index'])->name('mapa');
Route::get('/mapa/geojson', [MapController::class, 'geojson'])->name('mapa.geojson');
Route::get('/operadores/{slug}', [OrganizationController::class, 'show'])->name('operadores.show');
Route::get('/experiencias/{slug}', [ExperienceController::class, 'show'])->name('experiencias.show');
Route::get('/proyectos/{slug}', [ProjectController::class, 'show'])->name('proyectos.show');
Route::get('/agenda', [EventController::class, 'index'])->name('agenda.index');
Route::get('/agenda/{slug}', [EventController::class, 'show'])->name('agenda.show');
Route::get('/bitacora', [BlogPostController::class, 'index'])->name('bitacora.index');
Route::get('/bitacora/{slug}', [BlogPostController::class, 'show'])->name('bitacora.show');
Route::get('/colaboradores', [CollaborationController::class, 'create'])->name('colaboradores.create');
Route::post('/colaboradores', [CollaborationController::class, 'store'])->name('colaboradores.store');

Route::get('/login', [LoginController::class, 'create'])->name('login')->middleware('guest');
Route::post('/login', [LoginController::class, 'store'])->middleware('guest');
Route::post('/logout', [LoginController::class, 'destroy'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::post('/emprendimientos/{slug}/reclamar', [OwnerClaimController::class, 'store'])->name('businesses.claim');
    Route::get('/dashboard', OwnerDashboardController::class)->name('dashboard');
    Route::get('/dashboard/negocios/{slug}/editar', [BusinessEditController::class, 'edit'])->name('dashboard.businesses.edit');
    Route::put('/dashboard/negocios/{slug}', [BusinessEditController::class, 'update'])->name('dashboard.businesses.update');
});

// --- Sprint 2: panel del Owner de Organization ("Mi GO") ---
Route::middleware('auth')->prefix('mi-organizacion')->name('owner.')->group(function () {
    Route::get('/', [OwnerOrganizationController::class, 'show'])->name('organization.show');
    Route::get('/editar', [OwnerOrganizationController::class, 'edit'])->name('organization.edit');
    Route::put('/', [OwnerOrganizationController::class, 'update'])->name('organization.update');

    Route::get('/experiencias', [OwnerExperienceController::class, 'index'])->name('experiencias.index');
    Route::get('/experiencias/nueva', [OwnerExperienceController::class, 'create'])->name('experiencias.create');
    Route::post('/experiencias', [OwnerExperienceController::class, 'store'])->name('experiencias.store');
    Route::get('/experiencias/{experience}/editar', [OwnerExperienceController::class, 'edit'])->name('experiencias.edit');
    Route::put('/experiencias/{experience}', [OwnerExperienceController::class, 'update'])->name('experiencias.update');
    Route::post('/experiencias/{experience}/enviar-revision', [OwnerExperienceController::class, 'submitForReview'])->name('experiencias.submit');

    Route::get('/proyectos', [OwnerProjectController::class, 'index'])->name('proyectos.index');
    Route::get('/proyectos/nuevo', [OwnerProjectController::class, 'create'])->name('proyectos.create');
    Route::post('/proyectos', [OwnerProjectController::class, 'store'])->name('proyectos.store');
    Route::get('/proyectos/{project}/editar', [OwnerProjectController::class, 'edit'])->name('proyectos.edit');
    Route::put('/proyectos/{project}', [OwnerProjectController::class, 'update'])->name('proyectos.update');
    Route::post('/proyectos/{project}/enviar-revision', [OwnerProjectController::class, 'submitForReview'])->name('proyectos.submit');
});

Route::middleware(['auth', 'role:admin,super_admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/reviews', [AdminReviewController::class, 'index'])->name('reviews.index');
    Route::post('/reviews/{review}/approve', [AdminReviewController::class, 'approve'])->name('reviews.approve');
    Route::post('/reviews/{review}/reject', [AdminReviewController::class, 'reject'])->name('reviews.reject');
    Route::get('/claims', [AdminClaimController::class, 'index'])->name('claims.index');
    Route::post('/claims/{claim}/approve', [AdminClaimController::class, 'approve'])->name('claims.approve');
    Route::post('/claims/{claim}/reject', [AdminClaimController::class, 'reject'])->name('claims.reject');
    Route::get('/businesses', [AdminBusinessController::class, 'index'])->name('businesses.index');

    // --- GO Chile 2.0: admin, nuevo ---
    Route::get('/colaboradores', [AdminCollaborationRequestController::class, 'index'])->name('colaboradores.index');
    Route::post('/colaboradores/{collaborationRequest}/approve', [AdminCollaborationRequestController::class, 'approve'])->name('colaboradores.approve');
    Route::post('/colaboradores/{collaborationRequest}/reject', [AdminCollaborationRequestController::class, 'reject'])->name('colaboradores.reject');
    Route::get('/organizaciones', [AdminOrganizationController::class, 'index'])->name('organizaciones.index');
    Route::get('/organizaciones/{organization}/editar', [AdminOrganizationController::class, 'edit'])->name('organizaciones.edit');
    Route::put('/organizaciones/{organization}', [AdminOrganizationController::class, 'update'])->name('organizaciones.update');
    Route::post('/organizaciones/{organization}/approve', [AdminOrganizationController::class, 'approve'])->name('organizaciones.approve');
    Route::post('/organizaciones/{organization}/suspend', [AdminOrganizationController::class, 'suspend'])->name('organizaciones.suspend');

    Route::get('/experiencias', [AdminExperienceController::class, 'index'])->name('experiencias.index');
    Route::get('/experiencias/nueva', [AdminExperienceController::class, 'create'])->name('experiencias.create');
    Route::post('/experiencias', [AdminExperienceController::class, 'store'])->name('experiencias.store');
    Route::get('/experiencias/{experience}/editar', [AdminExperienceController::class, 'edit'])->name('experiencias.edit');
    Route::put('/experiencias/{experience}', [AdminExperienceController::class, 'update'])->name('experiencias.update');
    Route::delete('/experiencias/{experience}', [AdminExperienceController::class, 'destroy'])->name('experiencias.destroy');
    Route::post('/experiencias/{experience}/approve', [AdminExperienceController::class, 'approve'])->name('experiencias.approve');
    Route::post('/experiencias/{experience}/reject', [AdminExperienceController::class, 'reject'])->name('experiencias.reject');

    // --- Sprint 2: Project (no tenía CRUD admin todavía) ---
    Route::get('/proyectos', [AdminProjectController::class, 'index'])->name('proyectos.index');
    Route::get('/proyectos/nuevo', [AdminProjectController::class, 'create'])->name('proyectos.create');
    Route::post('/proyectos', [AdminProjectController::class, 'store'])->name('proyectos.store');
    Route::get('/proyectos/{project}/editar', [AdminProjectController::class, 'edit'])->name('proyectos.edit');
    Route::put('/proyectos/{project}', [AdminProjectController::class, 'update'])->name('proyectos.update');
    Route::delete('/proyectos/{project}', [AdminProjectController::class, 'destroy'])->name('proyectos.destroy');
    Route::post('/proyectos/{project}/approve', [AdminProjectController::class, 'approve'])->name('proyectos.approve');
    Route::post('/proyectos/{project}/reject', [AdminProjectController::class, 'reject'])->name('proyectos.reject');

    // --- Sprint 2: vista unificada de contenido pendiente ---
    Route::get('/moderacion', [AdminModerationController::class, 'index'])->name('moderacion.index');
});

Route::middleware(['auth', 'role:admin,super_admin,editor'])->prefix('admin/articles')->name('admin.articles.')->group(function () {
    Route::get('/', [AdminArticleController::class, 'index'])->name('index');
    Route::get('/nuevo', [AdminArticleController::class, 'create'])->name('create');
    Route::post('/', [AdminArticleController::class, 'store'])->name('store');
    Route::get('/{article}/editar', [AdminArticleController::class, 'edit'])->name('edit');
    Route::put('/{article}', [AdminArticleController::class, 'update'])->name('update');
});

// --- Sprint Bitácora GO 1.0: admin de BlogPost (Bitácora), no existía ---
Route::middleware(['auth', 'role:admin,super_admin,editor'])->prefix('admin/bitacora')->name('admin.blog-posts.')->group(function () {
    Route::get('/', [AdminBlogPostController::class, 'index'])->name('index');
    Route::get('/nueva', [AdminBlogPostController::class, 'create'])->name('create');
    Route::post('/', [AdminBlogPostController::class, 'store'])->name('store');
    Route::post('/subir-imagen', [AdminBlogPostController::class, 'uploadImage'])->name('upload-image');
    Route::get('/{blogPost}/editar', [AdminBlogPostController::class, 'edit'])->name('edit');
    Route::put('/{blogPost}', [AdminBlogPostController::class, 'update'])->name('update');
});
