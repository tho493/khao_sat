<?php

namespace App\Observers;

use App\Models\LichSuThayDoi;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class ActivityLogObserver
{
    /**
     * Handle the Model "created" event.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    public function created(Model $model)
    { 
        $this->logActivity($model, 'create');
    }

    /**
     * Handle the Model "updated" event.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    public function updated(Model $model)
    {
        $this->logActivity($model, 'update');
    }

    /**
     * Handle the Model "deleted" event.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    public function deleted(Model $model)
    {
        $this->logActivity($model, 'delete');
    }

    /**
     * Ghi lại hoạt động vào database.
     */
    protected function logActivity(Model $model, string $action)
    {
        if (!Auth::check()) {
            return;
        }

        $oldData = null;
        $newData = null;

        if ($action === 'create') {
            $newData = $model->getAttributes();
        } elseif ($action === 'update') {
            // Lấy ra những trường thực sự thay đổi
            $oldData = array_intersect_key($model->getOriginal(), $model->getChanges());
            $newData = $model->getChanges();
        } elseif ($action === 'delete') {
            $oldData = $model->getAttributes();
        }

        // Đảm bảo không ghi log nếu không có gì thay đổi (trường hợp update)
        if ($action === 'update' && empty($newData)) {
            return;
        }

        $modelName = class_basename($model);

        $logIdentifierKey = property_exists($model, 'logIdentifier') ? $model->logIdentifier : $model->getKeyName();
        $recordIdentifier = $model->{$logIdentifierKey};

        LichSuThayDoi::create([
            'bang_thaydoi' => $modelName,
            'id_banghi' => $recordIdentifier,
            'nguoi_thuchien_id' => Auth::user()->id,
            'hanhdong' => $action,
            'noidung_cu' => $oldData,
            'noidung_moi' => $newData,
            'ghi_chu' => $this->generateLogMessage($model, $action, $modelName, $logIdentifierKey, $recordIdentifier),
        ]);
    }

    /**
     * Tạo một thông điệp log mô tả hành động.
     */
    protected function generateLogMessage(Model $model, string $action, string $modelName, string $logIdentifierKey, string $recordIdentifier): string
    {
        $userName = Auth::user()->hoten;

        switch ($action) {
            case 'create':
                return "{$userName} đã tạo mới {$modelName} '{$recordIdentifier}'.";
            case 'update':
                return "{$userName} đã cập nhật {$modelName} '{$recordIdentifier}'.";
            case 'delete':
                return "{$userName} đã xóa {$modelName} '{$recordIdentifier}'.";
            default:
                return "Hành động '{$action}' được thực hiện trên {$modelName} '{$recordIdentifier}' bởi {$userName}.";
        }
    }
}