<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            // Support multiple initial tasks when creating a project in single atomic transaction
            'tasks' => ['nullable', 'array'],
            'tasks.*.title' => ['required_with:tasks', 'string', 'max:255'],
            'tasks.*.status' => ['nullable', 'in:pending,in_progress,completed'],
            'tasks.*.due_date' => ['nullable', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama proyek wajib diisi.',
            'name.max' => 'Nama proyek tidak boleh lebih dari 255 karakter.',
            'description.max' => 'Deskripsi proyek tidak boleh lebih dari 2000 karakter.',
            'tasks.*.title.required_with' => 'Judul tugas pada proyek wajib diisi.',
            'tasks.*.status.in' => 'Status tugas tidak valid.',
        ];
    }
}
