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
        // $this->logActivity($model, 'update');

        static $updateCallCount = 0;
        $updateCallCount++;

        // Ghi lại log chi tiết
        \Log::info("Observer 'updated' triggered for model: " . get_class($model) .
            " | ID: " . $model->getKey() .
            " | Call Count in this request: " . $updateCallCount);

        // In ra những thay đổi
        \Log::info("Changes detected: " . json_encode($model->getChanges()));

        // Chỉ ghi log vào DB ở lần gọi ĐẦU TIÊN
        if ($updateCallCount === 1) {
            $this->logActivity($model, 'update');
        } else {
            \Log::warning("DUPLICATE 'updated' event detected. Log to DB skipped.");
        }
    }

    /**
     * Handle the Model "deleted" event.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    public function deleted(Model $model)
    {
        // Sử dụng sự kiện "deleting" tốt hơn để lấy được dữ liệu trước khi xóa
        // nhưng "deleted" cũng hoạt động.
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

        LichSuThayDoi::create([
            'bang_thaydoi' => $model->getTable(),
            'id_banghi' => $model->getKey(),
            'nguoi_thuchien_id' => Auth::user()->id,
            'hanhdong' => $action,
            'noidung_cu' => $oldData,
            'noidung_moi' => $newData,
            'ghi_chu' => $this->generateLogMessage($model, $action),
        ]);
    }

    /**
     * Tạo một thông điệp log mô tả hành động.
     */
    protected function generateLogMessage(Model $model, string $action): string
    {
        $userName = Auth::user()->hoten;
        $modelName = class_basename($model);

        // Sử dụng thuộc tính $logIdentifier nếu có, nếu không thì dùng khóa chính
        $logIdentifierKey = property_exists($model, 'logIdentifier') ? $model->logIdentifier : $model->getKeyName();
        $recordIdentifier = $model->{$logIdentifierKey};

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