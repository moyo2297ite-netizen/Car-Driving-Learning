<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AvailabilitySlotController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\ContentCategoryController;
use App\Http\Controllers\Api\ContentController;
use App\Http\Controllers\Api\ContentProgressController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\QuizController;
use App\Http\Controllers\Api\SettingController;
use App\Http\Controllers\Api\SkillController;
use App\Http\Controllers\Api\StaffController;
use App\Http\Controllers\Api\StudentSkillController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markRead']);

    // إدارة المدربين والمشرفين — أدمن فقط
    Route::middleware('role:admin')->group(function () {
        Route::get('/staff', [StaffController::class, 'index']);
        Route::post('/staff', [StaffController::class, 'store']);
        Route::delete('/staff/{user}', [StaffController::class, 'destroy']);
    });

    // أوقات توفر المدربين
    Route::middleware('role:instructor')->group(function () {
        Route::post('/availability-slots', [AvailabilitySlotController::class, 'store']);
        Route::get('/availability-slots/mine', [AvailabilitySlotController::class, 'mine']);
    });

    Route::middleware('role:supervisor,admin')->group(function () {
        Route::get('/availability-slots/pending', [AvailabilitySlotController::class, 'pending']);
        Route::post('/availability-slots/{slot}/approve', [AvailabilitySlotController::class, 'approve']);
        Route::post('/availability-slots/{slot}/reject', [AvailabilitySlotController::class, 'reject']);
    });

    Route::get('/availability-slots/available', [AvailabilitySlotController::class, 'available']);

    // الحجوزات
    Route::middleware('role:student')->group(function () {
        Route::post('/bookings', [BookingController::class, 'store']);
        Route::get('/bookings/mine', [BookingController::class, 'mine']);
    });

    Route::middleware('role:supervisor,admin')->group(function () {
        Route::get('/bookings/pending', [BookingController::class, 'pending']);
        Route::post('/bookings/{booking}/assign', [BookingController::class, 'assign']);
    });

    Route::middleware('role:instructor')->group(function () {
        Route::get('/bookings/today', [BookingController::class, 'today']);
        Route::get('/instructor/students', [BookingController::class, 'students']);
    });

    Route::middleware('role:instructor,supervisor,admin')->group(function () {
        Route::post('/bookings/{booking}/complete', [BookingController::class, 'complete']);
    });

    Route::middleware('role:student,supervisor,admin')->group(function () {
        Route::post('/bookings/{booking}/cancel', [BookingController::class, 'cancel']);
    });

    // الدفع
    Route::middleware('role:student,supervisor,admin')->group(function () {
        Route::post('/bookings/{booking}/payments', [PaymentController::class, 'store']);
        Route::get('/payments/sham-cash-link', [PaymentController::class, 'shamCashLink']);
    });

    Route::middleware('role:supervisor,admin')->group(function () {
        Route::post('/payments/{payment}/confirm', [PaymentController::class, 'confirm']);
        Route::get('/payments/pending', [PaymentController::class, 'pending']);
    });

    // المحتوى التعليمي
    Route::get('/content-categories', [ContentCategoryController::class, 'index']);
    Route::get('/content-categories/{contentCategory}', [ContentCategoryController::class, 'show']);
    Route::get('/contents/{content}', [ContentController::class, 'show']);

    Route::middleware('role:supervisor,admin')->group(function () {
        Route::post('/content-categories', [ContentCategoryController::class, 'store']);
        Route::put('/content-categories/{contentCategory}', [ContentCategoryController::class, 'update']);
        Route::delete('/content-categories/{contentCategory}', [ContentCategoryController::class, 'destroy']);

        Route::post('/contents', [ContentController::class, 'store']);
        Route::put('/contents/{content}', [ContentController::class, 'update']);
        Route::delete('/contents/{content}', [ContentController::class, 'destroy']);

        Route::post('/contents/{content}/quiz', [QuizController::class, 'store']);
    });

    Route::middleware('role:student')->group(function () {
        Route::post('/contents/{content}/view', [ContentController::class, 'markViewed']);
        Route::post('/quizzes/{quiz}/submit', [QuizController::class, 'submit']);
        Route::get('/me/content-progress', [ContentProgressController::class, 'mine']);
        Route::get('/me/skills', [StudentSkillController::class, 'mine']);
    });

    Route::middleware('role:supervisor,instructor,admin')->group(function () {
        Route::get('/students/{student}/content-progress', [ContentProgressController::class, 'forStudent']);
        Route::get('/students/{student}/skills', [StudentSkillController::class, 'forStudent']);
        Route::get('/students/{student}/certificate-eligibility', [StudentSkillController::class, 'certificateEligibility']);
    });

    // المهارات
    Route::get('/skills', [SkillController::class, 'index']);

    Route::middleware('role:admin')->group(function () {
        Route::post('/skills', [SkillController::class, 'store']);
        Route::put('/skills/{skill}', [SkillController::class, 'update']);
        Route::delete('/skills/{skill}', [SkillController::class, 'destroy']);
    });

    Route::middleware('role:instructor')->group(function () {
        Route::put('/students/{student}/skills/{skill}', [StudentSkillController::class, 'update']);
    });

    // الرسائل
    Route::middleware('role:student,supervisor,admin')->group(function () {
        Route::post('/messages', [MessageController::class, 'store']);
        Route::get('/messages/with/{user}', [MessageController::class, 'conversation']);
        Route::post('/messages/{message}/read', [MessageController::class, 'markRead']);
    });

    Route::middleware('role:supervisor,admin')->group(function () {
        Route::get('/messages/threads', [MessageController::class, 'threads']);
    });

    // الإعدادات
    Route::middleware('role:admin,supervisor')->group(function () {
        Route::get('/settings', [SettingController::class, 'index']);
    });

    Route::middleware('role:admin')->group(function () {
        Route::put('/settings', [SettingController::class, 'update']);
    });

    // اللوحات
    Route::get('/dashboard/admin', [DashboardController::class, 'admin'])->middleware('role:admin');
    Route::get('/dashboard/supervisor', [DashboardController::class, 'supervisor'])->middleware('role:supervisor,admin');
    Route::get('/dashboard/instructor', [DashboardController::class, 'instructor'])->middleware('role:instructor');
});
