<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'project_id' => ['required', 'exists:projects,id'],
            'title' => ['required', 'string', 'max:255'],
            'status' => ['nullable', 'in:pending,in_progress,completed'],
            'due_date' => ['nullable', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'project_id.required' => 'Proyek harus dipilih.',
            'project_id.exists' => 'Proyek tidak ditemukan.',
            'title.required' => 'Judul tugas wajib diisi.',
            'title.max' => 'Judul tugas tidak boleh lebih dari 255 karakter.',
            'status.in' => 'Status yang dipilih tidak valid (pending, in_progress, completed).',
            'due_date.date' => 'Format tanggal batas waktu tidak valid.',
        ];
    }
}
