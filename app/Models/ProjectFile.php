<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectFile extends Model
{
    protected $fillable = [
        'project_id', 'file_name', 'file_path', 'file_type', 'file_size', 'category', 'uploaded_by',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function uploadedBy()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getFileSizeFormattedAttribute(): string
    {
        if (!$this->file_size) return '';
        $kb = $this->file_size / 1024;
        if ($kb < 1024) return round($kb, 1) . ' KB';
        return round($kb / 1024, 1) . ' MB';
    }
}
