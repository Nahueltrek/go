<?php
use App\Http\Controllers\Admin\ArticleController as AdminArticleController;
use App\Http\Controllers\Admin\BusinessController as AdminBusinessController;
use App\Http\Controllers\Admin\ClaimController as AdminClaimController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\ReviewController as AdminReviewController;
use App\Http\Controllers\Admin\CollaborationRequestController as AdminCollaborationRequestController;
use App\Http\Controllers\Admin\OrganizationController as AdminOrganizationController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Owner\BusinessEditController;
use App\Http\Controllers\Owner\ClaimController as OwnerClaimController;
use App\Http\Controllers\Owner\DashboardController as OwnerDashboardController;
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

Route::middleware(['auth', 'role:admin,super_admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', AdminDashboardController::class)->name('dashboard');
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
});

Route::middleware(['auth', 'role:admin,super_admin,editor'])->prefix('admin/articles')->name('admin.articles.')->group(function () {
    Route::get('/', [AdminArticleController::class, 'index'])->name('index');
    Route::get('/nuevo', [AdminArticleController::class, 'create'])->name('create');
    Route::post('/', [AdminArticleController::class, 'store'])->name('store');
    Route::get('/{article}/editar', [AdminArticleController::class, 'edit'])->name('edit');
    Route::put('/{article}', [AdminArticleController::class, 'update'])->name('update');
});