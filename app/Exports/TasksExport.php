<?php

namespace App\Exports;

use App\Models\Task;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TasksExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $projectId;

    public function __construct($projectId = null)
    {
        $this->projectId = $projectId;
    }

    public function collection()
    {
        $query = Task::with(['project', 'category', 'assignee']);

        if ($this->projectId) {
            $query->where('project_id', $this->projectId);
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Title',
            'Description',
            'Project',
            'Category',
            'Status',
            'Priority',
            'Due Date',
            'Assigned To',
            'Created At',
            'Updated At',
        ];
    }

    public function map($task): array
    {
        return [
            $task->id,
            $task->title,
            $task->description,
            $task->project->name,
            $task->category ? $task->category->name : 'None',
            ucfirst($task->status),
            ucfirst($task->priority),
            $task->due_date ? $task->due_date->format('Y-m-d') : 'No due date',
            $task->assignee ? $task->assignee->name : 'Unassigned',
            $task->created_at->format('Y-m-d H:i:s'),
            $task->updated_at->format('Y-m-d H:i:s'),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text
            1 => ['font' => ['bold' => true]],
        ];
    }
}
